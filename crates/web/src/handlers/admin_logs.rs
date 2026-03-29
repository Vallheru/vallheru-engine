//! Admin log viewer handler.
//!
//! Ported from `includes/admin/logs.php`. Staff can browse
//! `game_log_daily` entries, filtered by player ID, with pagination.

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

/// A single log entry for the template.
#[derive(serde::Serialize)]
pub struct LogEntry {
    pub owner_id: i32,
    pub message: String,
    pub date: String,
}

/// View model for the admin log page.
#[derive(serde::Serialize)]
pub struct AdminLogsView {
    #[serde(flatten)]
    pub base: crate::render::RenderContext,
    pub logs: Vec<LogEntry>,
    pub page: i64,
    pub total_pages: i64,
    pub player_filter: Option<i32>,
}

/// Query parameters for log viewing.
#[derive(serde::Deserialize)]
pub struct LogsQuery {
    #[serde(default = "default_page")]
    pub page: i64,
    pub lid: Option<i32>,
}

fn default_page() -> i64 {
    1
}

// ---------------------------------------------------------------------------
// Handler
// ---------------------------------------------------------------------------

/// GET /staff/logs — browse admin logs.
pub async fn admin_logs(
    State(state): State<AppState>,
    Extension(ctx): Extension<RequestContext>,
    Query(params): Query<LogsQuery>,
) -> Response {
    let pool = &state.pool;
    let page = params.page.max(1);

    let count = match vallheru_data::queries::admin::count_admin_logs(pool, params.lid).await {
        Ok(c) => c,
        Err(e) => {
            tracing::error!(error = %e, "failed to count logs");
            return db_error();
        }
    };

    let total_pages = (count + 49) / 50; // ceil division

    let rows = match vallheru_data::queries::admin::list_admin_logs(pool, params.lid, page).await {
        Ok(r) => r,
        Err(e) => {
            tracing::error!(error = %e, "failed to load logs");
            return db_error();
        }
    };

    let meta = PageMeta::titled("Logi graczy").with_back_link("/staff", "Panel");
    let base = state.templates.build_context(&ctx, &meta);

    let view = AdminLogsView {
        base,
        logs: rows
            .into_iter()
            .map(|r| LogEntry {
                owner_id: r.owner_id,
                message: r.message,
                date: r.created_at,
            })
            .collect(),
        page,
        total_pages,
        player_filter: params.lid,
    };
    state.templates.render_value("admin_logs.html", &view)
}

fn db_error() -> Response {
    (
        axum::http::StatusCode::INTERNAL_SERVER_ERROR,
        "Błąd bazy danych",
    )
        .into_response()
}
