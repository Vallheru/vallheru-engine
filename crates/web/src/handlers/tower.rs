//! Tower handler — game clock (age, day, next reset time).
//!
//! Ported from `tower.php`. Displays the current game age, day, and
//! time until the next daily reset.

use axum::{Extension, extract::State, response::Response};

use crate::middleware::context::RequestContext;
use crate::page::PageMeta;
use crate::state::AppState;

// ---------------------------------------------------------------------------
// View model
// ---------------------------------------------------------------------------

#[derive(serde::Serialize)]
pub struct TowerView {
    #[serde(flatten)]
    pub base: crate::render::RenderContext,
    pub age: i32,
    pub day: i32,
    pub hours_to_reset: i64,
    pub minutes_to_reset: i64,
}

// ---------------------------------------------------------------------------
// GET /tower
// ---------------------------------------------------------------------------

/// Display the game clock — current age, day, and time to next reset.
pub async fn tower_show(
    State(state): State<AppState>,
    Extension(ctx): Extension<RequestContext>,
) -> Response {
    let (age, day) = vallheru_data::queries::locations::load_game_clock(&state.pool)
        .await
        .unwrap_or((1, 1));

    // Calculate time to next midnight UTC.
    let now_secs = std::time::SystemTime::now()
        .duration_since(std::time::UNIX_EPOCH)
        .unwrap_or_default()
        .as_secs();
    let secs_since_midnight = now_secs % 86400;
    let secs_to_midnight = 86400 - secs_since_midnight;
    #[allow(clippy::cast_possible_wrap)]
    let hours = (secs_to_midnight / 3600) as i64;
    #[allow(clippy::cast_possible_wrap)]
    let minutes = ((secs_to_midnight % 3600) / 60) as i64;

    let meta = PageMeta::titled("Wieża zegarowa");
    let base = state.templates.build_context(&ctx, &meta);

    let view = TowerView {
        base,
        age,
        day,
        hours_to_reset: hours,
        minutes_to_reset: minutes,
    };

    state.templates.render_value("tower.html", &view)
}
