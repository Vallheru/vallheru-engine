//! Member list handler.
//!
//! Ported from `memberlist.php`. Paginated player listing with search
//! by name, accessible to all authenticated users.

use axum::{
    Extension,
    extract::{Query, State},
    response::{IntoResponse, Response},
};

use crate::middleware::context::RequestContext;
use crate::page::PageMeta;
use crate::state::AppState;

// ---------------------------------------------------------------------------
// View models
// ---------------------------------------------------------------------------

/// A member entry for the list.
#[derive(serde::Serialize)]
pub struct MemberEntry {
    pub id: i32,
    pub username: String,
    pub rank: String,
}

/// View model for the member list template.
#[derive(serde::Serialize)]
pub struct MemberListView {
    #[serde(flatten)]
    pub base: crate::render::RenderContext,
    pub members: Vec<MemberEntry>,
    pub page: i64,
    pub total_pages: i64,
    pub search: String,
}

/// Query parameters for the member list.
#[derive(serde::Deserialize)]
pub struct MemberListQuery {
    #[serde(default = "default_page")]
    pub page: i64,
    #[serde(default)]
    pub search: String,
}

fn default_page() -> i64 {
    1
}

// ---------------------------------------------------------------------------
// Handler
// ---------------------------------------------------------------------------

/// GET /memberlist — paginated player list with search.
pub async fn member_list(
    State(state): State<AppState>,
    Extension(ctx): Extension<RequestContext>,
    Query(params): Query<MemberListQuery>,
) -> Response {
    let pool = &state.pool;
    let page = params.page.max(1);

    // Convert search to ILIKE pattern if provided.
    let search_pattern = if params.search.is_empty() {
        None
    } else {
        // Replace * with % for SQL ILIKE wildcard support.
        let pattern = params.search.replace('*', "%");
        Some(pattern)
    };

    let count =
        match vallheru_data::queries::admin::count_members(pool, search_pattern.as_deref()).await {
            Ok(c) => c,
            Err(e) => {
                tracing::error!(error = %e, "failed to count members");
                return db_error();
            }
        };

    let total_pages = (count + 24) / 25; // ceil division, 25 per page

    let rows =
        match vallheru_data::queries::admin::list_members(pool, search_pattern.as_deref(), page)
            .await
        {
            Ok(r) => r,
            Err(e) => {
                tracing::error!(error = %e, "failed to load members");
                return db_error();
            }
        };

    let meta = PageMeta::titled("Lista mieszkańców").with_back_link("/city", "Wróć do miasta");
    let base = state.templates.build_context(&ctx, &meta);

    let view = MemberListView {
        base,
        members: rows
            .into_iter()
            .map(|r| MemberEntry {
                id: r.id,
                username: r.username,
                rank: r.rank,
            })
            .collect(),
        page,
        total_pages,
        search: params.search,
    };
    state.templates.render_value("memberlist.html", &view)
}

fn db_error() -> Response {
    (
        axum::http::StatusCode::INTERNAL_SERVER_ERROR,
        "Błąd bazy danych",
    )
        .into_response()
}
