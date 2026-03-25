//! Login and logout handlers.
//!
//! Ported from `index.php` (login) and `logout.php`. The domain-level
//! password verification lives in `vallheru_domain::auth`; this module
//! handles the HTTP layer: extracting form data, calling domain/data
//! functions, and producing responses.
//!
//! Session middleware (MP-05-06) will provide the actual session store.
//! Until then, the handlers are wired and route-reachable but delegate
//! session creation to a placeholder that will be replaced.

use axum::{
    Form,
    extract::State,
    http::StatusCode,
    response::{IntoResponse, Response},
};
use serde::Deserialize;

use crate::page::{self, Flash, PageMeta};
use crate::render::RenderContext;
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

    // Verify password (supports legacy MD5 and Argon2).
    let verify_result = vallheru_domain::auth::verify_password(&form.password, &player.pass_hash);

    match verify_result {
        vallheru_domain::auth::VerifyResult::Invalid => {
            return login_error(&state, state.catalog.get_or_key("head", "E_LOGIN"));
        }
        vallheru_domain::auth::VerifyResult::OkNeedsRehash(new_hash) => {
            // Transparently upgrade to Argon2.
            if let Err(e) = vallheru_data::queries::auth::update_password_hash(
                &state.pool,
                player.id,
                &new_hash,
            )
            .await
            {
                tracing::warn!(player_id = player.id, error = %e, "failed to rehash password");
                // Non-fatal: login still succeeds.
            }
        }
        vallheru_domain::auth::VerifyResult::Ok => {}
    }

    // Check ban status.
    // NOTE: we don't have the client IP readily available without an extractor;
    // for now pass empty string. Full IP extraction is wired in MP-05-06.
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

    tracing::info!(player_id = player.id, username = %player.username, "login successful");

    // TODO(MP-05-06): create session with player ID and redirect.
    // For now, redirect to the main game page.
    page::redirect_after_post("/city")
}

/// GET /logout — clear session and show goodbye page.
pub async fn logout() -> Response {
    // TODO(MP-05-06): destroy session, clear cookie.
    page::redirect("/")
}

/// Build an error response for login failures.
fn login_error(state: &AppState, message: &str) -> Response {
    let meta = PageMeta::titled("Error").with_flash(Flash::error(message));
    let ctx = build_anon_context(state, &meta);
    state.templates.render("error.html", &ctx)
}

/// Build a [`RenderContext`] for an unauthenticated visitor.
fn build_anon_context(state: &AppState, meta: &PageMeta) -> RenderContext {
    use crate::middleware::context::RequestContext;
    use uuid::Uuid;

    let req_ctx = RequestContext {
        request_id: Uuid::new_v4(),
        locale: state.context_defaults.locale.clone(),
        theme: String::new(),
        session_user: None,
    };
    state.templates.build_context(&req_ctx, meta)
}
