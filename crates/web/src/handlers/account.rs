//! Account activation and password-reset handlers.
//!
//! Ported from `aktywacja.php` (activation) and the `lostpasswd` step
//! of `index.php` (password recovery).

use axum::{
    Form,
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
// Activation
// ---------------------------------------------------------------------------

/// Query parameters for the activation link: `/activate?token=<int>`.
#[derive(Debug, Deserialize)]
pub struct ActivateQuery {
    pub token: Option<i32>,
}

/// GET /activate — confirm a pending account.
pub async fn activate(
    State(state): State<AppState>,
    Query(params): Query<ActivateQuery>,
) -> Response {
    let token = match params.token {
        Some(t) if t > 0 => t,
        _ => return activation_error(&state, state.catalog.get_or_key("common", "ERROR")),
    };

    let activation =
        match vallheru_data::queries::account::find_activation_by_token(&state.pool, token).await {
            Ok(Some(a)) => a,
            Ok(None) => {
                return activation_error(&state, state.catalog.get_or_key("common", "ERROR"));
            }
            Err(e) => {
                tracing::error!(error = %e, "activate: database error during lookup");
                return (StatusCode::INTERNAL_SERVER_ERROR, "Internal error").into_response();
            }
        };

    // Build default settings based on game type.
    let settings = vallheru_domain::account::default_settings(&activation.game_type);

    // Create the player and delete the activation record (transactional).
    if let Err(e) =
        vallheru_data::queries::account::activate_player(&state.pool, &activation, &settings).await
    {
        tracing::error!(error = %e, username = %activation.username, "activate: failed to create player");
        return (StatusCode::INTERNAL_SERVER_ERROR, "Internal error").into_response();
    }

    tracing::info!(username = %activation.username, "account activated");

    // Show success page.
    let meta = PageMeta::titled(state.catalog.get_or_key("activation", "TITLE")).with_flash(
        Flash::success(state.catalog.get_or_key("activation", "SUCCESS")),
    );
    let ctx = build_anon_context(&state, &meta);
    state.templates.render("activate.html", &ctx)
}

// ---------------------------------------------------------------------------
// Lost password
// ---------------------------------------------------------------------------

/// Query parameters for password-reset confirmation link.
#[derive(Debug, Deserialize)]
pub struct LostPasswordQuery {
    pub code: Option<String>,
    pub email: Option<String>,
}

/// Form data for requesting a password reset.
#[derive(Debug, Deserialize)]
pub struct LostPasswordForm {
    pub email: String,
}

/// Extended template context for the lost-password page.
#[derive(serde::Serialize)]
struct LostPasswordContext {
    #[serde(flatten)]
    base: RenderContext,
    /// "form" to show the email form, "sent" after submission, "changed" after confirmation.
    step: String,
    /// Message to display on the confirmation step.
    message: String,
}

/// GET /lost-password — show the reset form or confirm a reset link.
pub async fn show_lost_password(
    State(state): State<AppState>,
    Query(params): Query<LostPasswordQuery>,
) -> Response {
    // If code + email are present, this is a confirmation click.
    if let (Some(code), Some(email)) = (&params.code, &params.email) {
        return confirm_password_reset(&state, code, email).await;
    }

    // Otherwise, show the request form.
    let meta = PageMeta::titled(state.catalog.get_or_key("lost_password", "TITLE"));
    let base = build_anon_context(&state, &meta);
    let ctx = LostPasswordContext {
        base,
        step: "form".to_owned(),
        message: String::new(),
    };
    state.templates.render_value("lost_password.html", &ctx)
}

/// POST /lost-password — request a password reset.
pub async fn submit_lost_password(
    State(state): State<AppState>,
    Form(form): Form<LostPasswordForm>,
) -> Response {
    if form.email.is_empty() {
        return lost_password_error(
            &state,
            state.catalog.get_or_key("lost_password", "ERROR_MAIL"),
        );
    }

    // Look up the player by email.
    let player_id =
        match vallheru_data::queries::account::find_player_id_by_email(&state.pool, &form.email)
            .await
        {
            Ok(Some(id)) => id,
            Ok(None) => {
                return lost_password_error(
                    &state,
                    state.catalog.get_or_key("lost_password", "ERROR_NOEMAIL"),
                );
            }
            Err(e) => {
                tracing::error!(error = %e, "lost-password: database error");
                return (StatusCode::INTERNAL_SERVER_ERROR, "Internal error").into_response();
            }
        };

    // Generate a temporary password and secure token.
    let temp_password = vallheru_domain::account::generate_temporary_password();
    let token = vallheru_domain::account::generate_reset_token();
    let temp_hash = vallheru_domain::auth::hash_password(&temp_password);

    // Store the reset record.
    if let Err(e) = vallheru_data::queries::account::insert_password_reset(
        &state.pool,
        &token,
        &form.email,
        &temp_hash,
        player_id,
    )
    .await
    {
        tracing::error!(error = %e, "lost-password: failed to insert reset record");
        return (StatusCode::INTERNAL_SERVER_ERROR, "Internal error").into_response();
    }

    // TODO: Send email with the reset link and temporary password.
    // For now, log the token so testing is possible during migration.
    tracing::info!(
        player_id = player_id,
        email = %form.email,
        token = %token,
        temp_password = %temp_password,
        "password reset requested (email not yet wired)"
    );

    // Show success message.
    let meta = PageMeta::titled(state.catalog.get_or_key("lost_password", "TITLE"));
    let base = build_anon_context(&state, &meta);
    let ctx = LostPasswordContext {
        base,
        step: "sent".to_owned(),
        message: String::new(),
    };
    state.templates.render_value("lost_password.html", &ctx)
}

/// Confirm a password reset when the user clicks the link from the email.
async fn confirm_password_reset(state: &AppState, code: &str, email: &str) -> Response {
    if code.is_empty() || email.is_empty() {
        return lost_password_error(state, state.catalog.get_or_key("common", "ERROR"));
    }

    let reset = match vallheru_data::queries::account::find_password_reset(&state.pool, code, email)
        .await
    {
        Ok(Some(r)) => r,
        Ok(None) => {
            return lost_password_error(state, state.catalog.get_or_key("common", "ERROR"));
        }
        Err(e) => {
            tracing::error!(error = %e, "password-reset confirm: database error");
            return (StatusCode::INTERNAL_SERVER_ERROR, "Internal error").into_response();
        }
    };

    if let Err(e) =
        vallheru_data::queries::account::apply_password_reset(&state.pool, code, email, &reset)
            .await
    {
        tracing::error!(error = %e, "password-reset confirm: failed to apply");
        return (StatusCode::INTERNAL_SERVER_ERROR, "Internal error").into_response();
    }

    tracing::info!(player_id = reset.player_id, "password reset confirmed");

    let meta = PageMeta::titled(state.catalog.get_or_key("lost_password", "TITLE"));
    let base = build_anon_context(state, &meta);
    let ctx = LostPasswordContext {
        base,
        step: "changed".to_owned(),
        message: state
            .catalog
            .get_or_key("lost_password", "PASS_CHANGED")
            .to_owned(),
    };
    state.templates.render_value("lost_password.html", &ctx)
}

// ---------------------------------------------------------------------------
// Helpers
// ---------------------------------------------------------------------------

fn activation_error(state: &AppState, message: &str) -> Response {
    let meta = PageMeta::titled("Error").with_flash(Flash::error(message));
    let ctx = build_anon_context(state, &meta);
    state.templates.render("error.html", &ctx)
}

fn lost_password_error(state: &AppState, message: &str) -> Response {
    let meta = PageMeta::titled("Error").with_flash(Flash::error(message));
    let ctx = build_anon_context(state, &meta);
    state.templates.render("error.html", &ctx)
}
