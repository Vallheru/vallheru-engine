//! Court handlers — rules, cases, verdicts, and court staff listings.
//!
//! Ported from `court.php`. All players can view court documents and
//! staff listings. Judges, chancellors, and admins can create, edit,
//! and comment on documents.

use axum::{
    Extension, Form,
    extract::{Path, Query, State},
    response::{IntoResponse, Redirect, Response},
};

use crate::middleware::context::RequestContext;
use crate::page::PageMeta;
use crate::state::AppState;

// ---------------------------------------------------------------------------
// View models
// ---------------------------------------------------------------------------

/// Court main menu.
#[derive(serde::Serialize)]
pub struct CourtMenuView {
    #[serde(flatten)]
    pub base: crate::render::RenderContext,
    pub can_admin: bool,
}

/// Court staff listing.
#[derive(serde::Serialize)]
pub struct CourtStaffListView {
    #[serde(flatten)]
    pub base: crate::render::RenderContext,
    pub role_label: String,
    pub members: Vec<CourtMember>,
}

#[derive(serde::Serialize)]
pub struct CourtMember {
    pub id: i32,
    pub username: String,
}

/// List of court documents (rules/cases/verdicts).
#[derive(serde::Serialize)]
pub struct CourtDocListView {
    #[serde(flatten)]
    pub base: crate::render::RenderContext,
    pub kind: String,
    pub kind_label: String,
    pub docs: Vec<CourtDocEntry>,
}

#[derive(serde::Serialize)]
pub struct CourtDocEntry {
    pub id: i32,
    pub title: String,
}

/// Single court document detail.
#[derive(serde::Serialize)]
#[allow(clippy::struct_excessive_bools)]
pub struct CourtDocDetailView {
    #[serde(flatten)]
    pub base: crate::render::RenderContext,
    pub doc: CourtDocData,
    pub comments: Vec<CourtCommentData>,
    pub can_edit: bool,
    pub can_comment: bool,
    pub can_delete_comments: bool,
    pub is_case: bool,
}

#[derive(serde::Serialize)]
pub struct CourtDocData {
    pub id: i32,
    pub title: String,
    pub body: String,
    pub dated: String,
    pub kind: String,
}

#[derive(serde::Serialize)]
pub struct CourtCommentData {
    pub id: i32,
    pub author: String,
    pub body: String,
}

/// Court document edit form view.
#[derive(serde::Serialize)]
pub struct CourtDocEditView {
    #[serde(flatten)]
    pub base: crate::render::RenderContext,
    pub doc_id: i32,
    pub title: String,
    pub body: String,
}

/// Court document create form view.
#[derive(serde::Serialize)]
pub struct CourtDocCreateView {
    #[serde(flatten)]
    pub base: crate::render::RenderContext,
    pub kind: String,
    pub kind_label: String,
}

// ---------------------------------------------------------------------------
// Query/form types
// ---------------------------------------------------------------------------

#[derive(serde::Deserialize)]
pub struct CourtDocForm {
    pub ttitle: String,
    pub body: String,
    #[serde(default)]
    pub tid: Option<i32>,
}

#[derive(serde::Deserialize)]
pub struct CommentForm {
    pub body: String,
    pub tid: i32,
}

// ---------------------------------------------------------------------------
// Helpers
// ---------------------------------------------------------------------------

fn rank_label(kind: &str) -> &'static str {
    match kind {
        "judges" => "Sędzia",
        "aldermen" => "Ławnik",
        "lawyers" => "Prawnik",
        _ => "",
    }
}

fn kind_label(kind: &str) -> &'static str {
    match kind {
        "rule" => "Przepisy",
        "case" => "Sprawy",
        "verdict" => "Wyroki",
        _ => "",
    }
}

fn can_admin_court(rank: &str) -> bool {
    matches!(rank, "Admin" | "Sędzia" | "Kanclerz Sądu")
}

fn can_comment_court(rank: &str) -> bool {
    matches!(rank, "Admin" | "Sędzia" | "Prawnik")
}

fn player_rank(ctx: &RequestContext) -> String {
    ctx.session_user
        .as_ref()
        .map_or_else(String::new, |u| u.rank.clone())
}

// ---------------------------------------------------------------------------
// Handlers
// ---------------------------------------------------------------------------

/// GET /court — Court main menu.
pub async fn court_menu(
    State(state): State<AppState>,
    Extension(ctx): Extension<RequestContext>,
) -> Response {
    let rank = player_rank(&ctx);
    let meta = PageMeta::titled("Gmach sądu").with_back_link("/city", "Miasto");
    let view = CourtMenuView {
        base: state.templates.build_context(&ctx, &meta),
        can_admin: can_admin_court(&rank),
    };
    state.templates.render_value("court.html", &view)
}

/// GET /court/list/:role — List judges / aldermen / lawyers.
pub async fn court_staff_list(
    State(state): State<AppState>,
    Extension(ctx): Extension<RequestContext>,
    Path(role): Path<String>,
) -> Response {
    let allowed = ["judges", "aldermen", "lawyers"];
    if !allowed.contains(&role.as_str()) {
        return Redirect::to("/court").into_response();
    }

    let db_rank = rank_label(&role);
    let rows = vallheru_data::queries::moderation::list_players_by_rank(&state.pool, db_rank)
        .await
        .unwrap_or_default();

    let members: Vec<CourtMember> = rows
        .into_iter()
        .map(|r| CourtMember {
            id: r.id,
            username: r.username,
        })
        .collect();

    let label = match role.as_str() {
        "judges" => "Sędziowie",
        "aldermen" => "Ławnicy",
        "lawyers" => "Prawnicy",
        _ => "",
    };

    let meta = PageMeta::titled(label).with_back_link("/court", "Sąd");
    let view = CourtStaffListView {
        base: state.templates.build_context(&ctx, &meta),
        role_label: label.to_string(),
        members,
    };
    state.templates.render_value("court_staff.html", &view)
}

/// GET /court/docs/:kind — List court documents of a kind.
pub async fn court_doc_list(
    State(state): State<AppState>,
    Extension(ctx): Extension<RequestContext>,
    Path(kind): Path<String>,
) -> Response {
    let allowed = ["rule", "case", "verdict"];
    if !allowed.contains(&kind.as_str()) {
        return Redirect::to("/court").into_response();
    }

    let rows = vallheru_data::queries::moderation::list_court_docs(&state.pool, &kind)
        .await
        .unwrap_or_default();

    let docs: Vec<CourtDocEntry> = rows
        .into_iter()
        .map(|r| CourtDocEntry {
            id: r.id,
            title: r.title,
        })
        .collect();

    let label = kind_label(&kind);

    let meta = PageMeta::titled(label).with_back_link("/court", "Sąd");
    let view = CourtDocListView {
        base: state.templates.build_context(&ctx, &meta),
        kind: kind.clone(),
        kind_label: label.to_string(),
        docs,
    };
    state.templates.render_value("court_doc_list.html", &view)
}

/// GET /court/doc/:id — View single court document.
pub async fn court_doc_detail(
    State(state): State<AppState>,
    Extension(ctx): Extension<RequestContext>,
    Path(doc_id): Path<i32>,
) -> Response {
    let row = vallheru_data::queries::moderation::find_court_doc(&state.pool, doc_id)
        .await
        .ok()
        .flatten();

    let Some(row) = row else {
        return Redirect::to("/court").into_response();
    };

    let rank = player_rank(&ctx);
    let is_case = row.kind == "case";

    let comments = if is_case {
        vallheru_data::queries::moderation::list_court_comments(&state.pool, doc_id)
            .await
            .unwrap_or_default()
            .into_iter()
            .map(|c| CourtCommentData {
                id: c.id,
                author: c.author,
                body: c.body,
            })
            .collect()
    } else {
        Vec::new()
    };

    let meta = PageMeta::titled(&row.title)
        .with_back_link(format!("/court/docs/{}", row.kind), kind_label(&row.kind));
    let view = CourtDocDetailView {
        base: state.templates.build_context(&ctx, &meta),
        doc: CourtDocData {
            id: row.id,
            title: row.title,
            body: row.body.unwrap_or_default(),
            dated: row.dated.unwrap_or_default(),
            kind: row.kind.clone(),
        },
        can_edit: can_admin_court(&rank),
        can_comment: is_case && can_comment_court(&rank),
        can_delete_comments: can_admin_court(&rank),
        is_case,
        comments,
    };
    state.templates.render_value("court_doc_detail.html", &view)
}

/// GET /court/create/:kind — Form to create a new court document.
pub async fn court_doc_create_form(
    State(state): State<AppState>,
    Extension(ctx): Extension<RequestContext>,
    Path(kind): Path<String>,
) -> Response {
    let rank = player_rank(&ctx);
    if !can_admin_court(&rank) {
        return Redirect::to("/court").into_response();
    }

    let allowed = ["rule", "case", "verdict"];
    if !allowed.contains(&kind.as_str()) {
        return Redirect::to("/court").into_response();
    }

    let meta = PageMeta::titled("Nowy dokument").with_back_link("/court", "Sąd");
    let view = CourtDocCreateView {
        base: state.templates.build_context(&ctx, &meta),
        kind: kind.clone(),
        kind_label: kind_label(&kind).to_string(),
    };
    state.templates.render_value("court_doc_create.html", &view)
}

/// POST /court/create/:kind — Create a new court document.
pub async fn court_doc_create(
    State(state): State<AppState>,
    Extension(ctx): Extension<RequestContext>,
    Path(kind): Path<String>,
    Form(form): Form<CourtDocForm>,
) -> Response {
    let rank = player_rank(&ctx);
    if !can_admin_court(&rank) {
        return Redirect::to("/court").into_response();
    }

    if form.ttitle.is_empty() || form.body.is_empty() {
        return crate::page::redirect(&format!("/court/create/{kind}"));
    }

    let _ = vallheru_data::queries::moderation::create_court_doc(
        &state.pool,
        &form.ttitle,
        &form.body,
        &kind,
    )
    .await;

    crate::page::redirect_after_post(&format!("/court/docs/{kind}"))
}

/// GET /court/edit/:id — Form to edit a court document.
pub async fn court_doc_edit_form(
    State(state): State<AppState>,
    Extension(ctx): Extension<RequestContext>,
    Path(doc_id): Path<i32>,
) -> Response {
    let rank = player_rank(&ctx);
    if !can_admin_court(&rank) {
        return Redirect::to("/court").into_response();
    }

    let row = vallheru_data::queries::moderation::find_court_doc(&state.pool, doc_id)
        .await
        .ok()
        .flatten();

    let Some(row) = row else {
        return Redirect::to("/court").into_response();
    };

    let meta = PageMeta::titled("Edytuj dokument")
        .with_back_link(format!("/court/doc/{doc_id}"), "Dokument");
    let view = CourtDocEditView {
        base: state.templates.build_context(&ctx, &meta),
        doc_id,
        title: row.title,
        body: row.body.unwrap_or_default(),
    };
    state.templates.render_value("court_doc_edit.html", &view)
}

/// POST /court/edit/:id — Save edited court document.
pub async fn court_doc_edit(
    State(state): State<AppState>,
    Extension(ctx): Extension<RequestContext>,
    Path(doc_id): Path<i32>,
    Form(form): Form<CourtDocForm>,
) -> Response {
    let rank = player_rank(&ctx);
    if !can_admin_court(&rank) {
        return Redirect::to("/court").into_response();
    }

    if form.ttitle.is_empty() || form.body.is_empty() {
        return crate::page::redirect(&format!("/court/edit/{doc_id}"));
    }

    let _ = vallheru_data::queries::moderation::update_court_doc(
        &state.pool,
        doc_id,
        &form.ttitle,
        &form.body,
    )
    .await;

    crate::page::redirect_after_post(&format!("/court/doc/{doc_id}"))
}

/// POST /court/comment — Add a comment to a court case.
pub async fn court_add_comment(
    State(state): State<AppState>,
    Extension(ctx): Extension<RequestContext>,
    Form(form): Form<CommentForm>,
) -> Response {
    let rank = player_rank(&ctx);
    if !can_comment_court(&rank) {
        return Redirect::to("/court").into_response();
    }

    if form.body.is_empty() {
        return crate::page::redirect(&format!("/court/doc/{}", form.tid));
    }

    let Some(ref user) = ctx.session_user else {
        return Redirect::to("/court").into_response();
    };
    let author = format!("{} ID: {}", user.name, user.id);

    let _ = vallheru_data::queries::moderation::add_court_comment(
        &state.pool,
        form.tid,
        &author,
        &form.body,
    )
    .await;

    crate::page::redirect_after_post(&format!("/court/doc/{}", form.tid))
}

/// POST /court/comment/delete/:id — Delete a court case comment.
#[derive(serde::Deserialize)]
pub struct DeleteCommentQuery {
    pub doc_id: i32,
}

pub async fn court_delete_comment(
    State(state): State<AppState>,
    Extension(ctx): Extension<RequestContext>,
    Path(comment_id): Path<i32>,
    Query(q): Query<DeleteCommentQuery>,
) -> Response {
    let rank = player_rank(&ctx);
    if !can_admin_court(&rank) {
        return Redirect::to("/court").into_response();
    }

    let _ = vallheru_data::queries::moderation::delete_court_comment(&state.pool, comment_id).await;

    crate::page::redirect_after_post(&format!("/court/doc/{}", q.doc_id))
}
