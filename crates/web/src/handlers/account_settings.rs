//! Account settings and profile management handlers.
//!
//! Ported from `account.php`. The PHP version is a single 1400-line file
//! with ~20 views routed via `?view=X`. The Rust version groups the core
//! account-management views (settings, password, name, profile) into a
//! single handler that dispatches on the `view` query param.

use axum::{
    Extension, Form,
    extract::{Query, State},
    http::StatusCode,
    response::{IntoResponse, Response},
};
use serde::Deserialize;

use crate::middleware::context::RequestContext;
use crate::page::{Flash, PageMeta};
use crate::render::RenderContext;
use crate::state::AppState;

// ---------------------------------------------------------------------------
// Query / form types
// ---------------------------------------------------------------------------

#[derive(Debug, Deserialize)]
pub struct AccountQuery {
    pub view: Option<String>,
}

#[derive(Debug, Deserialize)]
pub struct ChangeNameForm {
    pub name: String,
}

#[derive(Debug, Deserialize)]
pub struct ChangePasswordForm {
    /// Current (old) password.
    pub cp: String,
    /// New password.
    pub np: String,
}

#[derive(Debug, Deserialize)]
pub struct SettingsForm {
    pub battlelog: Option<String>,
    pub battle: Option<String>,
    pub graphbar: Option<String>,
    pub autodrink: Option<String>,
    pub drink: Option<String>,
    pub rinvites: Option<String>,
    pub oldchat: Option<String>,
    pub avatar: Option<String>,
}

#[derive(Debug, Deserialize)]
pub struct ProfileForm {
    pub profile: String,
}

// ---------------------------------------------------------------------------
// Template contexts
// ---------------------------------------------------------------------------

#[derive(serde::Serialize)]
struct AccountMenuContext {
    #[serde(flatten)]
    base: RenderContext,
    account_view: String,
    player_name: String,
    player_email: String,
    player_avatar: String,
    player_profile: String,
    player_messenger: String,
    player_vallars: i32,
}

#[derive(serde::Serialize)]
struct SettingsContext {
    #[serde(flatten)]
    base: RenderContext,
    account_view: String,
    battlelog: String,
    graphbar: String,
    autodrink: String,
    rinvites: String,
    oldchat: String,
    show_avatar: String,
}

// ---------------------------------------------------------------------------
// GET /account — show account menu or a specific view
// ---------------------------------------------------------------------------

/// GET /account — main account page dispatcher.
pub async fn show_account(
    State(state): State<AppState>,
    Extension(req_ctx): Extension<RequestContext>,
    Query(params): Query<AccountQuery>,
) -> Response {
    let Some(player_id) = session_player_id(&req_ctx) else {
        return (StatusCode::UNAUTHORIZED, "Authentication required").into_response();
    };

    let view = params.view.as_deref().unwrap_or("");

    match view {
        "options" => show_options(&state, &req_ctx, player_id).await,
        "pass" => show_simple_view(&state, &req_ctx, "pass"),
        "name" => show_simple_view(&state, &req_ctx, "name"),
        "profile" => show_profile(&state, &req_ctx, player_id).await,
        _ => show_menu(&state, &req_ctx, player_id).await,
    }
}

/// Show the account menu (default view).
async fn show_menu(state: &AppState, req_ctx: &RequestContext, player_id: i32) -> Response {
    let info =
        match vallheru_data::queries::account_settings::load_account_info(&state.pool, player_id)
            .await
        {
            Ok(Some(i)) => i,
            Ok(None) => return (StatusCode::NOT_FOUND, "Player not found").into_response(),
            Err(e) => {
                tracing::error!(error = %e, player_id, "account: load info DB error");
                return (StatusCode::INTERNAL_SERVER_ERROR, "Internal error").into_response();
            }
        };

    let meta = PageMeta::titled("Opcje konta");
    let base = state.templates.build_context(req_ctx, &meta);
    let ctx = AccountMenuContext {
        base,
        account_view: String::new(),
        player_name: info.username,
        player_email: info.email,
        player_avatar: info.avatar,
        player_profile: info.profile,
        player_messenger: info.messenger,
        player_vallars: info.vallars,
    };
    state.templates.render_value("account.html", &ctx)
}

/// Show the options/settings form.
async fn show_options(state: &AppState, req_ctx: &RequestContext, player_id: i32) -> Response {
    let settings = load_player_settings(state, player_id).await;
    let meta = PageMeta::titled("Opcje konta");
    let base = state.templates.build_context(req_ctx, &meta);
    let ctx = SettingsContext {
        base,
        account_view: "options".to_owned(),
        battlelog: settings.battlelog,
        graphbar: settings.graphbar,
        autodrink: settings.autodrink,
        rinvites: settings.rinvites,
        oldchat: settings.oldchat,
        show_avatar: String::new(),
    };
    state.templates.render_value("account.html", &ctx)
}

/// Show the profile edit form.
async fn show_profile(state: &AppState, req_ctx: &RequestContext, player_id: i32) -> Response {
    let info =
        match vallheru_data::queries::account_settings::load_account_info(&state.pool, player_id)
            .await
        {
            Ok(Some(i)) => i,
            Ok(None) => return (StatusCode::NOT_FOUND, "Player not found").into_response(),
            Err(e) => {
                tracing::error!(error = %e, player_id, "account profile: load info DB error");
                return (StatusCode::INTERNAL_SERVER_ERROR, "Internal error").into_response();
            }
        };

    let meta = PageMeta::titled("Opcje konta");
    let base = state.templates.build_context(req_ctx, &meta);
    let ctx = AccountMenuContext {
        base,
        account_view: "profile".to_owned(),
        player_name: info.username,
        player_email: info.email,
        player_avatar: info.avatar,
        player_profile: info.profile,
        player_messenger: info.messenger,
        player_vallars: info.vallars,
    };
    state.templates.render_value("account.html", &ctx)
}

/// Show a simple view (password/name change forms — no extra data needed).
fn show_simple_view(state: &AppState, req_ctx: &RequestContext, view: &str) -> Response {
    let meta = PageMeta::titled("Opcje konta");
    let base = state.templates.build_context(req_ctx, &meta);
    let ctx = AccountMenuContext {
        base,
        account_view: view.to_owned(),
        player_name: String::new(),
        player_email: String::new(),
        player_avatar: String::new(),
        player_profile: String::new(),
        player_messenger: String::new(),
        player_vallars: 0,
    };
    state.templates.render_value("account.html", &ctx)
}

// ---------------------------------------------------------------------------
// POST /account/name — change display name
// ---------------------------------------------------------------------------

pub async fn change_name(
    State(state): State<AppState>,
    Extension(req_ctx): Extension<RequestContext>,
    Form(form): Form<ChangeNameForm>,
) -> Response {
    let Some(player_id) = session_player_id(&req_ctx) else {
        return (StatusCode::UNAUTHORIZED, "Authentication required").into_response();
    };

    let name = form.name.trim().to_owned();
    if name.is_empty() {
        return account_flash(&state, &req_ctx, Flash::error("Proszę wpisać imię."));
    }

    // Forbidden names.
    if name == "Admin" || name == "Staff" {
        return account_flash(
            &state,
            &req_ctx,
            Flash::error("Nie można użyć tego imienia."),
        );
    }

    // Strip HTML tags.
    let clean_name = vallheru_domain::text::strip_tags(&name);
    if clean_name.is_empty() {
        return account_flash(
            &state,
            &req_ctx,
            Flash::error("Nie można użyć tego imienia."),
        );
    }

    // Check uniqueness.
    match vallheru_data::queries::account_settings::is_username_taken(
        &state.pool,
        &clean_name,
        player_id,
    )
    .await
    {
        Ok(true) => {
            return account_flash(&state, &req_ctx, Flash::error("To imię jest już zajęte."));
        }
        Err(e) => {
            tracing::error!(error = %e, "change_name: DB error checking uniqueness");
            return (StatusCode::INTERNAL_SERVER_ERROR, "Internal error").into_response();
        }
        Ok(false) => {}
    }

    if let Err(e) = vallheru_data::queries::account_settings::update_username(
        &state.pool,
        player_id,
        &clean_name,
    )
    .await
    {
        tracing::error!(error = %e, player_id, "change_name: DB error");
        return (StatusCode::INTERNAL_SERVER_ERROR, "Internal error").into_response();
    }

    tracing::info!(player_id, new_name = %clean_name, "username changed");
    account_flash(
        &state,
        &req_ctx,
        Flash::success(format!("Zmieniłeś imię na {clean_name}.")),
    )
}

// ---------------------------------------------------------------------------
// POST /account/password — change password
// ---------------------------------------------------------------------------

pub async fn change_password(
    State(state): State<AppState>,
    Extension(req_ctx): Extension<RequestContext>,
    Form(form): Form<ChangePasswordForm>,
) -> Response {
    let Some(player_id) = session_player_id(&req_ctx) else {
        return (StatusCode::UNAUTHORIZED, "Authentication required").into_response();
    };

    if form.cp.is_empty() || form.np.is_empty() {
        return account_flash(
            &state,
            &req_ctx,
            Flash::error("Proszę wypełnić wszystkie pola."),
        );
    }

    // Verify current password.
    let stored_hash =
        match vallheru_data::queries::account_settings::get_password_hash(&state.pool, player_id)
            .await
        {
            Ok(Some(h)) => h,
            Ok(None) => return (StatusCode::NOT_FOUND, "Player not found").into_response(),
            Err(e) => {
                tracing::error!(error = %e, player_id, "change_password: DB error");
                return (StatusCode::INTERNAL_SERVER_ERROR, "Internal error").into_response();
            }
        };

    let verify = vallheru_domain::auth::verify_password(&form.cp, &stored_hash);
    if matches!(verify, vallheru_domain::auth::VerifyResult::Invalid) {
        return account_flash(
            &state,
            &req_ctx,
            Flash::error("Stare hasło jest nieprawidłowe."),
        );
    }

    // Hash and store the new password.
    let new_hash = vallheru_domain::auth::hash_password(&form.np);
    if let Err(e) =
        vallheru_data::queries::account_settings::update_password(&state.pool, player_id, &new_hash)
            .await
    {
        tracing::error!(error = %e, player_id, "change_password: DB error");
        return (StatusCode::INTERNAL_SERVER_ERROR, "Internal error").into_response();
    }

    tracing::info!(player_id, "password changed");
    account_flash(&state, &req_ctx, Flash::success("Hasło zostało zmienione."))
}

// ---------------------------------------------------------------------------
// POST /account/settings — save game preferences
// ---------------------------------------------------------------------------

pub async fn save_settings(
    State(state): State<AppState>,
    Extension(req_ctx): Extension<RequestContext>,
    Form(form): Form<SettingsForm>,
) -> Response {
    let Some(player_id) = session_player_id(&req_ctx) else {
        return (StatusCode::UNAUTHORIZED, "Authentication required").into_response();
    };

    let mut settings = load_player_settings(&state, player_id).await;

    // Battle log preference.
    if form.battlelog.is_some() {
        let battle_val = form.battle.as_deref().unwrap_or("Y");
        if ["A", "D", "Y"].contains(&battle_val) {
            settings.battlelog = battle_val.to_owned();
        }
    } else {
        "N".clone_into(&mut settings.battlelog);
    }

    // Graphbar toggle.
    settings.graphbar = if form.graphbar.is_some() {
        "Y".to_owned()
    } else {
        "N".to_owned()
    };

    // Auto-drink potions.
    if form.autodrink.is_some() {
        let drink_val = form.drink.as_deref().unwrap_or("H");
        if ["H", "M", "A"].contains(&drink_val) {
            settings.autodrink = drink_val.to_owned();
        }
    } else {
        "N".clone_into(&mut settings.autodrink);
    }

    // Room invites (inverted — checkbox means BLOCK).
    settings.rinvites = if form.rinvites.is_some() {
        "N".to_owned()
    } else {
        "Y".to_owned()
    };

    // Old chat layout.
    settings.oldchat = if form.oldchat.is_some() {
        "Y".to_owned()
    } else {
        "N".to_owned()
    };

    if let Err(e) =
        vallheru_data::queries::player::save_settings(&state.pool, player_id, &settings).await
    {
        tracing::error!(error = %e, player_id, "save_settings: DB error");
        return (StatusCode::INTERNAL_SERVER_ERROR, "Internal error").into_response();
    }

    account_flash(
        &state,
        &req_ctx,
        Flash::success("Ustawienia zostały zapisane."),
    )
}

// ---------------------------------------------------------------------------
// POST /account/profile — save profile text
// ---------------------------------------------------------------------------

pub async fn save_profile(
    State(state): State<AppState>,
    Extension(req_ctx): Extension<RequestContext>,
    Form(form): Form<ProfileForm>,
) -> Response {
    let Some(player_id) = session_player_id(&req_ctx) else {
        return (StatusCode::UNAUTHORIZED, "Authentication required").into_response();
    };

    if form.profile.is_empty() {
        return account_flash(
            &state,
            &req_ctx,
            Flash::error("Proszę wypełnić pole profilu."),
        );
    }

    // Sanitize: strip HTML tags from user input.
    let clean_profile = vallheru_domain::text::strip_tags(&form.profile);

    if let Err(e) = vallheru_data::queries::account_settings::update_profile(
        &state.pool,
        player_id,
        &clean_profile,
    )
    .await
    {
        tracing::error!(error = %e, player_id, "save_profile: DB error");
        return (StatusCode::INTERNAL_SERVER_ERROR, "Internal error").into_response();
    }

    account_flash(
        &state,
        &req_ctx,
        Flash::success("Profil został zaktualizowany."),
    )
}

// ---------------------------------------------------------------------------
// Helpers
// ---------------------------------------------------------------------------

/// Extract the player ID from the session user.
///
/// The session stores `id` as `i64`; the database uses `i32`.
/// Player IDs are always within `i32` range in this game.
#[allow(clippy::cast_possible_truncation)]
fn session_player_id(req_ctx: &RequestContext) -> Option<i32> {
    req_ctx.session_user.as_ref().map(|u| u.id as i32)
}

/// Load the player's current settings from the DB.
async fn load_player_settings(
    state: &AppState,
    player_id: i32,
) -> vallheru_domain::player::settings::PlayerSettings {
    use vallheru_domain::player::settings::PlayerSettings;

    match vallheru_data::queries::player::find_player_by_id(&state.pool, player_id).await {
        Ok(Some(row)) => vallheru_data::queries::player::settings_from_row(&row),
        _ => PlayerSettings::default(),
    }
}

/// Render the account page with a flash message.
fn account_flash(state: &AppState, req_ctx: &RequestContext, flash: Flash) -> Response {
    let meta = PageMeta::titled("Opcje konta").with_flash(flash);
    let base = state.templates.build_context(req_ctx, &meta);
    let ctx = AccountMenuContext {
        base,
        account_view: String::new(),
        player_name: String::new(),
        player_email: String::new(),
        player_avatar: String::new(),
        player_profile: String::new(),
        player_messenger: String::new(),
        player_vallars: 0,
    };
    state.templates.render_value("account.html", &ctx)
}
