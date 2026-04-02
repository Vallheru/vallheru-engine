//! Login and logout handlers.
//!
//! Ported from `index.php` (login) and `logout.php`. The domain-level
//! password verification lives in `vallheru_domain::auth`; this module
//! handles the HTTP layer: extracting form data, calling domain/data
//! functions, and producing responses.

use axum::{
    Form,
    extract::State,
    http::{StatusCode, header},
    response::{IntoResponse, Response},
};
use serde::Deserialize;

use crate::middleware::session;
use crate::page::{Flash, PageMeta};
use crate::state::AppState;

/// Form data submitted by the login form.
#[derive(Debug, Deserialize)]
pub struct LoginForm {
    pub email: String,
    pub password: String,
}

/// POST /login — authenticate and start a session.
pub async fn login(State(state): State<AppState>, Form(form): Form<LoginForm>) -> Response {
    // Validate non-empty fields.
    if form.email.is_empty() || form.password.is_empty() {
        return login_error(&state, state.catalog.get_or_key("head", "EMPTY_LOGIN"));
    }

    // Look up player by email.
    let player = match vallheru_data::queries::auth::find_by_email(&state.pool, &form.email).await {
        Ok(Some(p)) => p,
        Ok(None) => {
            return login_error(&state, state.catalog.get_or_key("head", "E_LOGIN"));
        }
        Err(e) => {
            tracing::error!(error = %e, "login: database error during player lookup");
            return (StatusCode::INTERNAL_SERVER_ERROR, "Internal error").into_response();
        }
    };

    // Verify password (Argon2 only).
    if !vallheru_domain::auth::verify_password(&form.password, &player.pass_hash) {
        return login_error(&state, state.catalog.get_or_key("head", "E_LOGIN"));
    }

    // Check ban status.
    let banned = vallheru_data::queries::auth::is_banned(
        &state.pool,
        "",
        player.id,
        &player.username,
        &player.email,
    )
    .await
    .unwrap_or(false);

    if banned {
        return login_error(&state, state.catalog.get_or_key("head", "BANNED"));
    }

    // Check frozen account.
    if player.freeze > 0 {
        let msg = format!(
            "{}{}{}",
            state.catalog.get_or_key("head", "ACCOUNT_BLOCKED"),
            player.freeze,
            state.catalog.get_or_key("head", "ACCOUNT_DAYS"),
        );
        return login_error(&state, &msg);
    }

    // Record login (increment counter, clear resting).
    if let Err(e) = vallheru_data::queries::auth::record_login(&state.pool, player.id).await {
        tracing::warn!(player_id = player.id, error = %e, "failed to record login");
    }

    // Clear stale fight state.
    if let Err(e) = vallheru_data::queries::auth::clear_stale_fight(&state.pool, player.id).await {
        tracing::warn!(player_id = player.id, error = %e, "failed to clear stale fight");
    }

    // Create session with session fixation defense (new ID on login).
    let new_session_id = session::generate_session_id();
    if let Err(e) = vallheru_data::queries::session::create_session(
        &state.pool,
        &new_session_id,
        Some(player.id),
    )
    .await
    {
        tracing::error!(player_id = player.id, error = %e, "failed to create session");
        return (StatusCode::INTERNAL_SERVER_ERROR, "Internal error").into_response();
    }

    tracing::info!(player_id = player.id, username = %player.username, "login successful");

    // Redirect to city with the session cookie set.
    let cookie = session::session_cookie_header(&new_session_id);
    (
        StatusCode::SEE_OTHER,
        [
            (header::LOCATION, "/city"),
            (header::SET_COOKIE, cookie.as_str()),
        ],
    )
        .into_response()
}

/// GET /logout — clear session and redirect to index.
pub async fn logout(State(state): State<AppState>, req: axum::extract::Request) -> Response {
    // Extract session ID from cookie and destroy it.
    if let Some(sid) = extract_session_cookie(&req) {
        if let Err(e) = vallheru_data::queries::session::delete_session(&state.pool, &sid).await {
            tracing::warn!(error = %e, "logout: failed to delete session");
        }
    }

    let cookie = session::clear_session_cookie_header();
    (
        StatusCode::SEE_OTHER,
        [
            (header::LOCATION, "/"),
            (header::SET_COOKIE, cookie.as_str()),
        ],
    )
        .into_response()
}

/// Extract session cookie from request (same logic as session middleware).
fn extract_session_cookie(req: &axum::extract::Request) -> Option<String> {
    let cookie_header = req.headers().get(header::COOKIE)?.to_str().ok()?;
    for pair in cookie_header.split(';') {
        let pair = pair.trim();
        if let Some(value) = pair.strip_prefix("sid=") {
            let value = value.trim();
            if !value.is_empty() {
                return Some(value.to_owned());
            }
        }
    }
    None
}

/// Build an error response for login failures.
fn login_error(state: &AppState, message: &str) -> Response {
    let meta = PageMeta::titled("Error").with_flash(Flash::error(message));
    let ctx = super::build_anon_context(state, &meta);
    state.templates.render("error.html", &ctx)
}
