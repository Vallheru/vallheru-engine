//! Registration handler.
//!
//! Ported from `register.php`. Displays the registration form and
//! processes new account submissions. Pending accounts are stored in
//! the `activations` table; actual account creation happens in the
//! activation flow (MP-05-03).

use axum::{
    Form,
    extract::State,
    http::StatusCode,
    response::{IntoResponse, Response},
};
use serde::Deserialize;

use crate::page::{Flash, PageMeta};
use crate::render::RenderContext;
use crate::state::AppState;

use super::build_anon_context;

/// Form data submitted by the registration form.
#[derive(Debug, Deserialize)]
pub struct RegisterForm {
    pub user: String,
    pub email: String,
    pub vemail: String,
    pub pass: String,
    pub gtype: String,
    #[serde(default)]
    pub r#ref: String,
}

/// Extended template context for the registration page.
#[derive(serde::Serialize)]
struct RegisterContext {
    #[serde(flatten)]
    base: RenderContext,
    player_count: i64,
    prefilled_ref: String,
    /// "form" to show the form, "success" to show the confirmation message.
    step: String,
}

/// GET /register — display the registration form.
pub async fn show_form(State(state): State<AppState>) -> Response {
    // Check if registration is open.
    match vallheru_data::queries::registration::is_registration_open(&state.pool).await {
        Ok((false, reason)) => {
            let msg = format!(
                "{}:<br />{}",
                state.catalog.get_or_key("head", "REASON"),
                reason,
            );
            return register_error(&state, &msg);
        }
        Err(e) => {
            tracing::error!(error = %e, "register: failed to check registration status");
            return (StatusCode::INTERNAL_SERVER_ERROR, "Internal error").into_response();
        }
        Ok((true, _)) => {}
    }

    // Count players for the description text.
    let player_count = vallheru_data::queries::registration::count_players(&state.pool)
        .await
        .unwrap_or_else(|e| {
            tracing::error!(error = %e, "count_players failed");
            0
        });

    let meta = PageMeta::titled(state.catalog.get_or_key("register", "TITLE"));
    let base = build_anon_context(&state, &meta);
    let ctx = RegisterContext {
        base,
        player_count,
        prefilled_ref: String::new(),
        step: "form".to_owned(),
    };
    state.templates.render_value("register.html", &ctx)
}

/// POST /register — validate and create a pending activation.
pub async fn submit(State(state): State<AppState>, Form(form): Form<RegisterForm>) -> Response {
    // Check if registration is open.
    match vallheru_data::queries::registration::is_registration_open(&state.pool).await {
        Ok((false, reason)) => {
            let msg = format!(
                "{}:<br />{}",
                state.catalog.get_or_key("head", "REASON"),
                reason,
            );
            return register_error(&state, &msg);
        }
        Err(e) => {
            tracing::error!(error = %e, "register: failed to check registration status");
            return (StatusCode::INTERNAL_SERVER_ERROR, "Internal error").into_response();
        }
        Ok((true, _)) => {}
    }

    // Validate the form input.
    let validated = match vallheru_domain::registration::validate_registration(
        &form.user,
        &form.email,
        &form.vemail,
        &form.pass,
        &form.gtype,
        &form.r#ref,
    ) {
        Ok(v) => v,
        Err(e) => {
            let msg = registration_error_message(&state, &e);
            return register_error(&state, &msg);
        }
    };

    // Check username uniqueness.
    match vallheru_data::queries::registration::username_exists(&state.pool, &validated.username)
        .await
    {
        Ok(true) => {
            return register_error(&state, state.catalog.get_or_key("register", "NICK_TAKEN"));
        }
        Err(e) => {
            tracing::error!(error = %e, "register: username check failed");
            return (StatusCode::INTERNAL_SERVER_ERROR, "Internal error").into_response();
        }
        Ok(false) => {}
    }

    // Check email uniqueness.
    match vallheru_data::queries::registration::email_exists(&state.pool, &validated.email).await {
        Ok(true) => {
            return register_error(&state, state.catalog.get_or_key("register", "EMAIL_TAKEN"));
        }
        Err(e) => {
            tracing::error!(error = %e, "register: email check failed");
            return (StatusCode::INTERNAL_SERVER_ERROR, "Internal error").into_response();
        }
        Ok(false) => {}
    }

    // Hash the password with Argon2id.
    let pass_hash = vallheru_domain::auth::hash_password(&validated.password);

    // Generate activation token.
    let token = vallheru_domain::registration::generate_activation_token();

    // Insert the activation record.
    if let Err(e) = vallheru_data::queries::registration::insert_activation(
        &state.pool,
        &vallheru_data::queries::registration::NewActivation {
            username: &validated.username,
            email: &validated.email,
            pass_hash: &pass_hash,
            token,
            referrer: validated.referrer_id.unwrap_or(0),
            ip: "", // IP extraction will be wired in MP-05-06
            game_type: validated.game_type.as_code(),
        },
    )
    .await
    {
        tracing::error!(error = %e, "register: failed to insert activation");
        return (StatusCode::INTERNAL_SERVER_ERROR, "Internal error").into_response();
    }

    // Send activation email.
    send_activation_email(&state, &validated.username, &validated.email, token).await;

    // Show success page.
    let meta = PageMeta::titled(state.catalog.get_or_key("register", "TITLE")).with_flash(
        Flash::success(state.catalog.get_or_key("register", "SUCCESS")),
    );
    let base = build_anon_context(&state, &meta);
    let ctx = RegisterContext {
        base,
        player_count: 0,
        prefilled_ref: String::new(),
        step: "success".to_owned(),
    };
    state.templates.render_value("register.html", &ctx)
}

/// Send the activation email (best-effort — errors are logged, not propagated).
async fn send_activation_email(state: &AppState, username: &str, email: &str, token: i32) {
    let activation_url = format!(
        "{}/activate?token={token}&email={email}",
        state.templates.base_url(),
    );
    let subject = format!(
        "{} — Aktywacja konta",
        state.catalog.get_or_key("register", "TITLE")
    );
    let body = format!(
        "Witaj {username}!\n\n\
         Aby aktywować swoje konto, kliknij poniższy link:\n\
         {activation_url}\n\n\
         Jeśli nie rejestrowałeś się w grze, zignoruj tę wiadomość.",
    );
    if let Err(e) = state.email.send(email, &subject, &body).await {
        tracing::error!(error = %e, "register: failed to send activation email");
    }
}

/// Map domain validation errors to localized user-facing messages.
fn registration_error_message(
    state: &AppState,
    error: &vallheru_domain::registration::ValidationError,
) -> String {
    use vallheru_domain::registration::ValidationError;
    match error {
        ValidationError::EmptyFields => state
            .catalog
            .get_or_key("common", "EMPTY_FIELDS")
            .to_owned(),
        ValidationError::PasswordTooShort => state
            .catalog
            .get_or_key("register", "PASS_TOO_SHORT")
            .to_owned(),
        ValidationError::PasswordNoLetterOrDigit => state
            .catalog
            .get_or_key("register", "PASS_NO_NUMBER")
            .to_owned(),
        ValidationError::PasswordNoUppercase => state
            .catalog
            .get_or_key("register", "PASS_NO_BIG")
            .to_owned(),
        ValidationError::EmailMismatch => state
            .catalog
            .get_or_key("register", "EMAIL_MISMATCH")
            .to_owned(),
        ValidationError::InvalidEmail => state
            .catalog
            .get_or_key("register", "INVALID_EMAIL")
            .to_owned(),
        ValidationError::InvalidGameType => state.catalog.get_or_key("common", "ERROR").to_owned(),
    }
}

/// Build an error response for registration failures.
fn register_error(state: &AppState, message: &str) -> Response {
    let meta = PageMeta::titled("Error").with_flash(Flash::error(message));
    let ctx = build_anon_context(state, &meta);
    state.templates.render("error.html", &ctx)
}
