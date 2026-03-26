//! Character reset (preset) and referral handlers.
//!
//! Ported from `preset.php` (character reset confirmation) and
//! `referrals.php` (vallar history display).

use axum::{
    extract::{Query, State},
    http::StatusCode,
    response::{IntoResponse, Response},
};
use serde::Deserialize;

use crate::page::{Flash, PageMeta};
use crate::render::RenderContext;
use crate::state::AppState;

use super::build_anon_context;

// ---------------------------------------------------------------------------
// Character reset confirmation (preset.php port)
// ---------------------------------------------------------------------------

#[derive(Debug, Deserialize)]
pub struct PresetQuery {
    pub id: Option<i32>,
    pub code: Option<i32>,
}

/// GET /preset — confirm or cancel a character reset.
///
/// With `?id=X&code=Y` — execute the reset.
/// With `?id=X` (no code) — cancel the pending reset.
pub async fn confirm_preset(
    State(state): State<AppState>,
    Query(params): Query<PresetQuery>,
) -> Response {
    let player_id = match params.id {
        Some(id) if id > 0 => id,
        _ => return preset_message(&state, Flash::error("Zapomnij o tym.")),
    };

    match params.code {
        Some(code) if code > 0 => execute_reset(&state, player_id, code).await,
        Some(_) => preset_message(&state, Flash::error("Zapomnij o tym.")),
        None => cancel_reset(&state, player_id).await,
    }
}

async fn cancel_reset(state: &AppState, player_id: i32) -> Response {
    if let Err(e) =
        vallheru_data::queries::character_reset::cancel_reset_request(&state.pool, player_id).await
    {
        tracing::error!(error = %e, player_id, "preset: cancel reset DB error");
        return (StatusCode::INTERNAL_SERVER_ERROR, "Internal error").into_response();
    }

    preset_message(state, Flash::info("Próba resetu została anulowana."))
}

async fn execute_reset(state: &AppState, player_id: i32, code: i32) -> Response {
    let request = match vallheru_data::queries::character_reset::find_reset_request(
        &state.pool,
        player_id,
        code,
    )
    .await
    {
        Ok(Some(r)) => r,
        Ok(None) => return preset_message(state, Flash::error("Nie ma takiego zgłoszenia.")),
        Err(e) => {
            tracing::error!(error = %e, player_id, "preset: find reset request DB error");
            return (StatusCode::INTERNAL_SERVER_ERROR, "Internal error").into_response();
        }
    };

    let result = if request.reset_type == "A" {
        vallheru_data::queries::character_reset::execute_full_reset(&state.pool, player_id).await
    } else {
        vallheru_data::queries::character_reset::execute_partial_reset(&state.pool, player_id).await
    };

    match result {
        Ok(()) => {
            tracing::info!(
                player_id,
                reset_type = request.reset_type,
                "character reset executed"
            );
            preset_message(state, Flash::info("Postać została zresetowana."))
        }
        Err(e) => {
            tracing::error!(error = %e, player_id, "preset: execute reset DB error");
            (StatusCode::INTERNAL_SERVER_ERROR, "Internal error").into_response()
        }
    }
}

fn preset_message(state: &AppState, flash: Flash) -> Response {
    let meta = PageMeta::titled("Vallheru").with_flash(flash);
    let ctx = build_anon_context(state, &meta);
    state.templates.render("error.html", &ctx)
}

// ---------------------------------------------------------------------------
// Referrals / Vallar history (referrals.php port)
// ---------------------------------------------------------------------------

#[derive(Debug, Deserialize)]
pub struct ReferralsQuery {
    pub id: Option<i32>,
}

/// Extended template context for the referrals page.
#[derive(serde::Serialize)]
struct ReferralsContext {
    #[serde(flatten)]
    base: RenderContext,
    player_id: i32,
    username: String,
    vallars: i32,
    history: Vec<vallheru_data::queries::character_reset::VallarHistoryRow>,
}

/// GET /referrals — display a player's vallar history.
pub async fn referrals(
    State(state): State<AppState>,
    Query(params): Query<ReferralsQuery>,
) -> Response {
    let target_id = match params.id {
        Some(id) if id > 0 => id,
        _ => return preset_message(&state, Flash::error("Zapomnij o tym!")),
    };

    let info = match vallheru_data::queries::character_reset::get_player_vallar_info(
        &state.pool,
        target_id,
    )
    .await
    {
        Ok(Some(i)) => i,
        Ok(None) => return preset_message(&state, Flash::error("Nie ma takiego gracza!")),
        Err(e) => {
            tracing::error!(error = %e, target_id, "referrals: DB error");
            return (StatusCode::INTERNAL_SERVER_ERROR, "Internal error").into_response();
        }
    };

    let history = match vallheru_data::queries::character_reset::load_vallar_history(
        &state.pool,
        target_id,
        30,
    )
    .await
    {
        Ok(h) => h,
        Err(e) => {
            tracing::error!(error = %e, target_id, "referrals: load history DB error");
            return (StatusCode::INTERNAL_SERVER_ERROR, "Internal error").into_response();
        }
    };

    let meta = PageMeta::titled("Vallary");
    let base = build_anon_context(&state, &meta);
    let ctx = ReferralsContext {
        base,
        player_id: info.id,
        username: info.username,
        vallars: info.vallars,
        history,
    };
    state.templates.render_value("referrals.html", &ctx)
}
