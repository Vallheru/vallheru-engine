//! Bug report handlers for staff.
//!
//! Ported from `includes/admin/bugreport.php`. Staff can view
//! player-submitted bug reports, update their resolution status,
//! and add comments.

use axum::{
    Extension, Form,
    extract::{Path, State},
    response::{IntoResponse, Response},
};

use crate::middleware::context::RequestContext;
use crate::page::{Flash, FlashKind, PageMeta};
use crate::state::AppState;

// ---------------------------------------------------------------------------
// View models
// ---------------------------------------------------------------------------

/// A bug report list entry for the template.
#[derive(serde::Serialize)]
pub struct BugEntry {
    pub id: i32,
    pub sender: i32,
    pub title: String,
    pub location: String,
    pub status: String,
}

/// View model for the bug report list.
#[derive(serde::Serialize)]
pub struct BugListView {
    #[serde(flatten)]
    pub base: crate::render::RenderContext,
    pub bugs: Vec<BugEntry>,
}

/// View model for a single bug report detail.
#[derive(serde::Serialize)]
pub struct BugDetailView {
    #[serde(flatten)]
    pub base: crate::render::RenderContext,
    pub bug: BugDetail,
    pub comments: Vec<BugComment>,
}

/// Bug detail for the template.
#[derive(serde::Serialize)]
pub struct BugDetail {
    pub id: i32,
    pub title: String,
    pub location: String,
    pub body: String,
    pub status: String,
}

/// A comment on a bug report.
#[derive(serde::Serialize)]
pub struct BugComment {
    pub author: String,
    pub body: String,
    pub created: String,
}

// ---------------------------------------------------------------------------
// Handlers
// ---------------------------------------------------------------------------

/// GET /staff/bugreport — list all bug reports.
pub async fn bugreport_list(
    State(state): State<AppState>,
    Extension(ctx): Extension<RequestContext>,
) -> Response {
    let meta = PageMeta::titled("Zgłoszone błędy").with_back_link("/staff", "Panel");
    let base = state.templates.build_context(&ctx, &meta);

    let rows = match vallheru_data::queries::admin::list_bug_reports(&state.pool).await {
        Ok(r) => r,
        Err(e) => {
            tracing::error!(error = %e, "failed to load bug reports");
            return db_error();
        }
    };

    let bugs = rows.into_iter().map(|r| BugEntry {
        id: r.id,
        sender: r.sender,
        title: r.title,
        location: r.location,
        status: resolution_label(r.resolution),
    });

    let view = BugListView {
        base,
        bugs: bugs.collect(),
    };
    state.templates.render_value("bugreport_list.html", &view)
}

/// GET /staff/bugreport/:id — view a single bug report.
pub async fn bugreport_detail(
    State(state): State<AppState>,
    Extension(ctx): Extension<RequestContext>,
    Path(bug_id): Path<i32>,
) -> Response {
    let meta = PageMeta::titled("Szczegóły błędu").with_back_link("/staff/bugreport", "Lista");

    let row = match vallheru_data::queries::admin::find_bug_report(&state.pool, bug_id).await {
        Ok(Some(r)) => r,
        Ok(None) => return crate::page::redirect("/staff/bugreport"),
        Err(e) => {
            tracing::error!(error = %e, "failed to load bug report");
            return db_error();
        }
    };

    let comments = match vallheru_data::queries::admin::bug_comments(&state.pool, bug_id).await {
        Ok(c) => c,
        Err(e) => {
            tracing::error!(error = %e, "failed to load bug comments");
            return db_error();
        }
    };

    let base = state.templates.build_context(&ctx, &meta);

    let view = BugDetailView {
        base,
        bug: BugDetail {
            id: row.id,
            title: row.title,
            location: row.location,
            body: row.body,
            status: resolution_label(row.resolution),
        },
        comments: comments
            .into_iter()
            .map(|c| BugComment {
                author: c.author,
                body: c.body,
                created: c.created.unwrap_or_default(),
            })
            .collect(),
    };
    state.templates.render_value("bugreport_detail.html", &view)
}

/// Form for resolving a bug report.
#[derive(serde::Deserialize)]
pub struct BugResolveForm {
    pub action: String,
    #[serde(default)]
    pub comment: String,
}

/// POST /staff/bugreport/:id — resolve a bug report.
pub async fn bugreport_resolve(
    State(state): State<AppState>,
    Extension(ctx): Extension<RequestContext>,
    Path(bug_id): Path<i32>,
    Form(form): Form<BugResolveForm>,
) -> Response {
    let pool = &state.pool;
    let user_label = ctx
        .session_user
        .as_ref()
        .map_or("Staff".to_owned(), |u| format!("{} ID:{}", u.name, u.id));

    let (delete, resolution) = match form.action.as_str() {
        "fixed" => (true, 0_i16),
        "notbug" => (true, 1),
        "workforme" => (false, 2),
        "moreinfo" => (false, 3),
        "duplicate" => (true, 4),
        "invalid" => (true, 5),
        _ => return crate::page::redirect("/staff/bugreport"),
    };

    // Add comment if provided.
    if !form.comment.trim().is_empty() {
        if let Err(e) = vallheru_data::queries::admin::add_bug_comment(
            pool,
            bug_id,
            &user_label,
            form.comment.trim(),
        )
        .await
        {
            tracing::error!(error = %e, "failed to add bug comment");
        }
    }

    if delete {
        if let Err(e) = vallheru_data::queries::admin::delete_bug_report(pool, bug_id).await {
            tracing::error!(error = %e, "failed to delete bug report");
            return db_error();
        }
    } else if let Err(e) =
        vallheru_data::queries::admin::update_bug_resolution(pool, bug_id, resolution).await
    {
        tracing::error!(error = %e, "failed to update bug resolution");
        return db_error();
    }

    let meta = PageMeta::titled("Zgłoszone błędy")
        .with_back_link("/staff", "Panel")
        .with_flash(Flash {
            kind: FlashKind::Success,
            message: "Status błędu został zaktualizowany.".to_owned(),
        });
    let base = state.templates.build_context(&ctx, &meta);

    let rows = match vallheru_data::queries::admin::list_bug_reports(pool).await {
        Ok(r) => r,
        Err(e) => {
            tracing::error!(error = %e, "failed to reload bug reports");
            return db_error();
        }
    };

    let view = BugListView {
        base,
        bugs: rows
            .into_iter()
            .map(|r| BugEntry {
                id: r.id,
                sender: r.sender,
                title: r.title,
                location: r.location,
                status: resolution_label(r.resolution),
            })
            .collect(),
    };
    state.templates.render_value("bugreport_list.html", &view)
}

// ---------------------------------------------------------------------------
// Helpers
// ---------------------------------------------------------------------------

fn resolution_label(code: i16) -> String {
    match code {
        0 => "Oczekuje na sprawdzenie".to_owned(),
        2 | 3 => "Wymaga więcej informacji".to_owned(),
        _ => format!("Rozwiązane ({code})"),
    }
}

fn db_error() -> Response {
    (
        axum::http::StatusCode::INTERNAL_SERVER_ERROR,
        "Błąd bazy danych",
    )
        .into_response()
}
