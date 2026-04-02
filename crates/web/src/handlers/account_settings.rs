//! Account settings and profile management handlers.
//!
//! Ported from `account.php`. The PHP version is a single 1400-line file
//! with ~20 views routed via `?view=X`. The Rust version groups all
//! account-management views into a single GET handler that dispatches on
//! the `view` query param, plus focused POST handlers for mutations.

use axum::{
    Extension, Form,
    extract::{Path, Query, State},
    http::{StatusCode, header},
    response::{IntoResponse, Response},
};
use serde::Deserialize;

use vallheru_data::queries::account_settings as aq;

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
    pub edit: Option<i64>,
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

#[derive(Debug, Deserialize)]
pub struct FreezeForm {
    pub days: i16,
}

#[derive(Debug, Deserialize)]
pub struct StyleForm {
    pub style: String,
}

#[derive(Debug, Deserialize)]
pub struct RoleplayForm {
    pub roleplay: Option<String>,
    pub ooc: Option<String>,
    pub shortrp: Option<String>,
}

#[derive(Debug, Deserialize)]
pub struct AddBlockedForm {
    pub pid: i64,
}

#[derive(Debug, Deserialize)]
pub struct EditBlockedForm {
    pub block_mail: Option<String>,
    pub block_chat: Option<String>,
}

#[derive(Debug, Deserialize)]
pub struct LinkForm {
    pub label: String,
    pub url: String,
    pub sort_order: Option<i32>,
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
    // Freeze & immunity info (shown in menu and freeze/immu views).
    player_immune: bool,
    player_class: String,
    player_freeze: i16,
    // Roleplay fields (shown in roleplay edit view).
    player_roleplay: String,
    player_ooc: String,
    player_short_rpg: String,
    // Style picker.
    #[serde(default)]
    available_styles: Vec<String>,
    current_style: String,
    // Blocked users.
    #[serde(default)]
    blocked_users: Vec<BlockedUserView>,
    // Quick links.
    #[serde(default)]
    links: Vec<LinkView>,
    editing_link: Option<LinkView>,
}

#[derive(serde::Serialize, Default, Clone)]
struct BlockedUserView {
    id: i64,
    blocked_id: i64,
    name: String,
    block_mail: bool,
    block_chat: bool,
}

#[derive(serde::Serialize, Default, Clone)]
pub struct LinkView {
    id: i64,
    label: String,
    url: String,
    sort_order: i32,
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
        "freeze" => show_view_with_info(&state, &req_ctx, player_id, "freeze").await,
        "immu" => show_view_with_info(&state, &req_ctx, player_id, "immu").await,
        "style" => show_style(&state, &req_ctx, player_id).await,
        "roleplay" => show_view_with_info(&state, &req_ctx, player_id, "roleplay").await,
        "ignored" => show_ignored(&state, &req_ctx, player_id).await,
        "links" => show_links(&state, &req_ctx, player_id, params.edit).await,
        _ => show_menu(&state, &req_ctx, player_id).await,
    }
}

/// Show the account menu (default view).
async fn show_menu(state: &AppState, req_ctx: &RequestContext, player_id: i32) -> Response {
    let info = match aq::load_account_info(&state.pool, player_id).await {
        Ok(Some(i)) => i,
        Ok(None) => return (StatusCode::NOT_FOUND, "Player not found").into_response(),
        Err(e) => {
            tracing::error!(error = %e, player_id, "account: load info DB error");
            return (StatusCode::INTERNAL_SERVER_ERROR, "Internal error").into_response();
        }
    };

    let meta = PageMeta::titled("Opcje konta");
    let base = state.templates.build_context(req_ctx, &meta);
    let ctx = build_menu_ctx(base, "", &info);
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
    show_view_with_info(state, req_ctx, player_id, "profile").await
}

/// Show a view that needs player info (profile, freeze, immu, roleplay).
async fn show_view_with_info(
    state: &AppState,
    req_ctx: &RequestContext,
    player_id: i32,
    view: &str,
) -> Response {
    let info = match aq::load_account_info(&state.pool, player_id).await {
        Ok(Some(i)) => i,
        Ok(None) => return (StatusCode::NOT_FOUND, "Player not found").into_response(),
        Err(e) => {
            tracing::error!(error = %e, player_id, "account {view}: load info DB error");
            return (StatusCode::INTERNAL_SERVER_ERROR, "Internal error").into_response();
        }
    };

    let meta = PageMeta::titled("Opcje konta");
    let base = state.templates.build_context(req_ctx, &meta);
    let ctx = build_menu_ctx(base, view, &info);
    state.templates.render_value("account.html", &ctx)
}

/// Show the style/theme picker view.
async fn show_style(state: &AppState, req_ctx: &RequestContext, player_id: i32) -> Response {
    let info = match aq::load_account_info(&state.pool, player_id).await {
        Ok(Some(i)) => i,
        Ok(None) => return (StatusCode::NOT_FOUND, "Player not found").into_response(),
        Err(e) => {
            tracing::error!(error = %e, player_id, "account style: load info DB error");
            return (StatusCode::INTERNAL_SERVER_ERROR, "Internal error").into_response();
        }
    };

    let settings = load_player_settings(state, player_id).await;
    let meta = PageMeta::titled("Opcje konta");
    let base = state.templates.build_context(req_ctx, &meta);
    let mut ctx = build_menu_ctx(base, "style", &info);
    ctx.available_styles = crate::assets::available_css_themes()
        .into_iter()
        .map(str::to_owned)
        .collect();
    ctx.current_style = settings.style;
    state.templates.render_value("account.html", &ctx)
}

/// Show the ignored/blocked users list.
async fn show_ignored(state: &AppState, req_ctx: &RequestContext, player_id: i32) -> Response {
    let info = match aq::load_account_info(&state.pool, player_id).await {
        Ok(Some(i)) => i,
        Ok(None) => return (StatusCode::NOT_FOUND, "Player not found").into_response(),
        Err(e) => {
            tracing::error!(error = %e, player_id, "account ignored: load info DB error");
            return (StatusCode::INTERNAL_SERVER_ERROR, "Internal error").into_response();
        }
    };

    let blocked = aq::list_blocked(&state.pool, i64::from(player_id))
        .await
        .unwrap_or_default();

    let meta = PageMeta::titled("Opcje konta");
    let base = state.templates.build_context(req_ctx, &meta);
    let mut ctx = build_menu_ctx(base, "ignored", &info);
    ctx.blocked_users = blocked
        .into_iter()
        .map(|b| BlockedUserView {
            id: b.id,
            blocked_id: b.blocked_id,
            name: b.blocked_name,
            block_mail: b.block_mail,
            block_chat: b.block_chat,
        })
        .collect();
    state.templates.render_value("account.html", &ctx)
}

/// Show the quick links management view.
async fn show_links(
    state: &AppState,
    req_ctx: &RequestContext,
    player_id: i32,
    editing: Option<i64>,
) -> Response {
    let info = match aq::load_account_info(&state.pool, player_id).await {
        Ok(Some(i)) => i,
        Ok(None) => return (StatusCode::NOT_FOUND, "Player not found").into_response(),
        Err(e) => {
            tracing::error!(error = %e, player_id, "account links: load info DB error");
            return (StatusCode::INTERNAL_SERVER_ERROR, "Internal error").into_response();
        }
    };

    let links = aq::list_links(&state.pool, player_id)
        .await
        .unwrap_or_default();
    let editing_link = editing.and_then(|eid| {
        links.iter().find(|l| l.id == eid).map(|l| LinkView {
            id: l.id,
            label: l.label.clone(),
            url: l.url.clone(),
            sort_order: l.sort_order,
        })
    });

    let meta = PageMeta::titled("Opcje konta");
    let base = state.templates.build_context(req_ctx, &meta);
    let mut ctx = build_menu_ctx(base, "links", &info);
    ctx.links = links
        .into_iter()
        .map(|l| LinkView {
            id: l.id,
            label: l.label,
            url: l.url,
            sort_order: l.sort_order,
        })
        .collect();
    ctx.editing_link = editing_link;
    state.templates.render_value("account.html", &ctx)
}

/// Show a simple view (password/name change forms — no extra data needed).
fn show_simple_view(state: &AppState, req_ctx: &RequestContext, view: &str) -> Response {
    let meta = PageMeta::titled("Opcje konta");
    let base = state.templates.build_context(req_ctx, &meta);
    let ctx = empty_menu_ctx(base, view);
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
    if !verify {
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
// POST /account/freeze — freeze (suspend) the account
// ---------------------------------------------------------------------------

pub async fn freeze_account(
    State(state): State<AppState>,
    Extension(req_ctx): Extension<RequestContext>,
    Form(form): Form<FreezeForm>,
) -> Response {
    let Some(player_id) = session_player_id(&req_ctx) else {
        return (StatusCode::UNAUTHORIZED, "Authentication required").into_response();
    };

    let days = form.days.clamp(1, 21);

    if let Err(e) = aq::freeze_account(&state.pool, player_id, days).await {
        tracing::error!(error = %e, player_id, "freeze_account: DB error");
        return (StatusCode::INTERNAL_SERVER_ERROR, "Internal error").into_response();
    }

    tracing::info!(player_id, days, "account frozen");

    // Session is destroyed in DB by freeze_account; expire the cookie and redirect.
    let cookie = crate::middleware::session::clear_session_cookie_header();
    (
        StatusCode::SEE_OTHER,
        [
            (header::LOCATION, "/"),
            (header::SET_COOKIE, cookie.as_str()),
        ],
    )
        .into_response()
}

// ---------------------------------------------------------------------------
// POST /account/immunity — toggle immunity flag
// ---------------------------------------------------------------------------

pub async fn set_immunity(
    State(state): State<AppState>,
    Extension(req_ctx): Extension<RequestContext>,
) -> Response {
    let Some(player_id) = session_player_id(&req_ctx) else {
        return (StatusCode::UNAUTHORIZED, "Authentication required").into_response();
    };

    // Load current state to check eligibility.
    let info = match aq::load_account_info(&state.pool, player_id).await {
        Ok(Some(i)) => i,
        Ok(None) => return (StatusCode::NOT_FOUND, "Player not found").into_response(),
        Err(e) => {
            tracing::error!(error = %e, player_id, "set_immunity: DB error");
            return (StatusCode::INTERNAL_SERVER_ERROR, "Internal error").into_response();
        }
    };

    if info.immune {
        return account_flash(
            &state,
            &req_ctx,
            Flash::error("Masz już przydzieloną immunicję."),
        );
    }

    if info.class.is_empty() {
        return account_flash(
            &state,
            &req_ctx,
            Flash::error("Musisz posiadać klasę, by uzyskać immunicję."),
        );
    }

    if let Err(e) = aq::set_immunity(&state.pool, player_id, true).await {
        tracing::error!(error = %e, player_id, "set_immunity: DB error");
        return (StatusCode::INTERNAL_SERVER_ERROR, "Internal error").into_response();
    }

    tracing::info!(player_id, "immunity granted");
    account_flash(
        &state,
        &req_ctx,
        Flash::success("Immunicja została przydzielona."),
    )
}

// ---------------------------------------------------------------------------
// POST /account/style — change CSS theme
// ---------------------------------------------------------------------------

pub async fn save_style(
    State(state): State<AppState>,
    Extension(req_ctx): Extension<RequestContext>,
    Form(form): Form<StyleForm>,
) -> Response {
    let Some(player_id) = session_player_id(&req_ctx) else {
        return (StatusCode::UNAUTHORIZED, "Authentication required").into_response();
    };

    let available = crate::assets::available_css_themes();
    let chosen = form.style.trim();
    if !available.contains(&chosen) {
        return account_flash(&state, &req_ctx, Flash::error("Nieprawidłowy styl."));
    }

    let mut settings = load_player_settings(&state, player_id).await;
    chosen.clone_into(&mut settings.style);

    if let Err(e) =
        vallheru_data::queries::player::save_settings(&state.pool, player_id, &settings).await
    {
        tracing::error!(error = %e, player_id, "save_style: DB error");
        return (StatusCode::INTERNAL_SERVER_ERROR, "Internal error").into_response();
    }

    account_flash(&state, &req_ctx, Flash::success("Styl został zmieniony."))
}

// ---------------------------------------------------------------------------
// POST /account/roleplay — save roleplay profile
// ---------------------------------------------------------------------------

pub async fn save_roleplay(
    State(state): State<AppState>,
    Extension(req_ctx): Extension<RequestContext>,
    Form(form): Form<RoleplayForm>,
) -> Response {
    let Some(player_id) = session_player_id(&req_ctx) else {
        return (StatusCode::UNAUTHORIZED, "Authentication required").into_response();
    };

    let roleplay = form.roleplay.as_deref().unwrap_or("").to_owned();
    let ooc = form.ooc.as_deref().unwrap_or("").to_owned();
    let short_rpg = vallheru_domain::text::strip_tags(form.shortrp.as_deref().unwrap_or(""));

    // If short_rpg is set but both main fields are empty, reject.
    if !short_rpg.is_empty() && roleplay.is_empty() && ooc.is_empty() {
        return account_flash(&state, &req_ctx, Flash::error("Wypełnij wszystkie pola!"));
    }

    if let Err(e) = aq::update_roleplay(&state.pool, player_id, &roleplay, &ooc, &short_rpg).await {
        tracing::error!(error = %e, player_id, "save_roleplay: DB error");
        return (StatusCode::INTERNAL_SERVER_ERROR, "Internal error").into_response();
    }

    account_flash(
        &state,
        &req_ctx,
        Flash::success("Profil fabularny został zaktualizowany."),
    )
}

// ---------------------------------------------------------------------------
// POST /account/blocked — add blocked user
// ---------------------------------------------------------------------------

pub async fn add_blocked(
    State(state): State<AppState>,
    Extension(req_ctx): Extension<RequestContext>,
    Form(form): Form<AddBlockedForm>,
) -> Response {
    let Some(player_id) = session_player_id(&req_ctx) else {
        return (StatusCode::UNAUTHORIZED, "Authentication required").into_response();
    };

    let owner_id = i64::from(player_id);

    // Check count limit (30).
    let current = aq::list_blocked(&state.pool, owner_id)
        .await
        .unwrap_or_default();
    if current.len() >= 30 {
        return account_flash(
            &state,
            &req_ctx,
            Flash::error("Twoja lista ignorowanych jest już pełna."),
        );
    }

    if form.pid == owner_id {
        return account_flash(
            &state,
            &req_ctx,
            Flash::error("Nie możesz ignorować siebie."),
        );
    }

    match aq::add_blocked(&state.pool, owner_id, form.pid).await {
        Ok(true) => account_flash(
            &state,
            &req_ctx,
            Flash::success("Dodano gracza do listy ignorowanych."),
        ),
        Ok(false) => account_flash(
            &state,
            &req_ctx,
            Flash::error("Ignorujesz już tego gracza."),
        ),
        Err(e) => {
            tracing::error!(error = %e, player_id, "add_blocked: DB error");
            (StatusCode::INTERNAL_SERVER_ERROR, "Internal error").into_response()
        }
    }
}

// ---------------------------------------------------------------------------
// POST /account/blocked/:id/edit — edit block flags
// ---------------------------------------------------------------------------

pub async fn edit_blocked(
    State(state): State<AppState>,
    Extension(req_ctx): Extension<RequestContext>,
    Path(block_id): Path<i64>,
    Form(form): Form<EditBlockedForm>,
) -> Response {
    let Some(player_id) = session_player_id(&req_ctx) else {
        return (StatusCode::UNAUTHORIZED, "Authentication required").into_response();
    };

    let block_mail = form.block_mail.is_some();
    let block_chat = form.block_chat.is_some();

    if let Err(e) = aq::update_block_flags(
        &state.pool,
        i64::from(player_id),
        block_id,
        block_mail,
        block_chat,
    )
    .await
    {
        tracing::error!(error = %e, player_id, block_id, "edit_blocked: DB error");
        return (StatusCode::INTERNAL_SERVER_ERROR, "Internal error").into_response();
    }

    account_flash(
        &state,
        &req_ctx,
        Flash::success("Zaktualizowano ustawienia ignorowanego."),
    )
}

// ---------------------------------------------------------------------------
// POST /account/blocked/:id/delete — remove blocked user
// ---------------------------------------------------------------------------

pub async fn remove_blocked(
    State(state): State<AppState>,
    Extension(req_ctx): Extension<RequestContext>,
    Path(block_id): Path<i64>,
) -> Response {
    let Some(player_id) = session_player_id(&req_ctx) else {
        return (StatusCode::UNAUTHORIZED, "Authentication required").into_response();
    };

    if let Err(e) = aq::remove_blocked(&state.pool, i64::from(player_id), block_id).await {
        tracing::error!(error = %e, player_id, block_id, "remove_blocked: DB error");
        return (StatusCode::INTERNAL_SERVER_ERROR, "Internal error").into_response();
    }

    account_flash(
        &state,
        &req_ctx,
        Flash::success("Usunięto gracza z listy ignorowanych."),
    )
}

// ---------------------------------------------------------------------------
// POST /account/links — add a new quick link
// ---------------------------------------------------------------------------

pub async fn add_link(
    State(state): State<AppState>,
    Extension(req_ctx): Extension<RequestContext>,
    Form(form): Form<LinkForm>,
) -> Response {
    let Some(player_id) = session_player_id(&req_ctx) else {
        return (StatusCode::UNAUTHORIZED, "Authentication required").into_response();
    };

    let label = vallheru_domain::text::strip_tags(form.label.trim());
    let url = form.url.trim().to_owned();
    if label.is_empty() || url.is_empty() {
        return account_flash(
            &state,
            &req_ctx,
            Flash::error("Proszę wypełnić etykietę i adres URL."),
        );
    }

    let sort_order = form.sort_order.unwrap_or(0);

    if let Err(e) = aq::add_link(&state.pool, player_id, &label, &url, sort_order).await {
        tracing::error!(error = %e, player_id, "add_link: DB error");
        return (StatusCode::INTERNAL_SERVER_ERROR, "Internal error").into_response();
    }

    account_flash(&state, &req_ctx, Flash::success("Dodano link."))
}

// ---------------------------------------------------------------------------
// POST /account/links/:id/edit — update a quick link
// ---------------------------------------------------------------------------

pub async fn edit_link(
    State(state): State<AppState>,
    Extension(req_ctx): Extension<RequestContext>,
    Path(link_id): Path<i64>,
    Form(form): Form<LinkForm>,
) -> Response {
    let Some(player_id) = session_player_id(&req_ctx) else {
        return (StatusCode::UNAUTHORIZED, "Authentication required").into_response();
    };

    let label = vallheru_domain::text::strip_tags(form.label.trim());
    let url = form.url.trim().to_owned();
    if label.is_empty() || url.is_empty() {
        return account_flash(
            &state,
            &req_ctx,
            Flash::error("Proszę wypełnić etykietę i adres URL."),
        );
    }

    let sort_order = form.sort_order.unwrap_or(0);

    if let Err(e) = aq::update_link(&state.pool, player_id, link_id, &label, &url, sort_order).await
    {
        tracing::error!(error = %e, player_id, link_id, "edit_link: DB error");
        return (StatusCode::INTERNAL_SERVER_ERROR, "Internal error").into_response();
    }

    account_flash(&state, &req_ctx, Flash::success("Zaktualizowano link."))
}

// ---------------------------------------------------------------------------
// POST /account/links/:id/delete — delete a quick link
// ---------------------------------------------------------------------------

pub async fn delete_link(
    State(state): State<AppState>,
    Extension(req_ctx): Extension<RequestContext>,
    Path(link_id): Path<i64>,
) -> Response {
    let Some(player_id) = session_player_id(&req_ctx) else {
        return (StatusCode::UNAUTHORIZED, "Authentication required").into_response();
    };

    if let Err(e) = aq::delete_link(&state.pool, player_id, link_id).await {
        tracing::error!(error = %e, player_id, link_id, "delete_link: DB error");
        return (StatusCode::INTERNAL_SERVER_ERROR, "Internal error").into_response();
    }

    account_flash(&state, &req_ctx, Flash::success("Usunięto link."))
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
    let ctx = empty_menu_ctx(base, "");
    state.templates.render_value("account.html", &ctx)
}

/// Build an `AccountMenuContext` populated from `AccountInfo`.
fn build_menu_ctx(base: RenderContext, view: &str, info: &aq::AccountInfo) -> AccountMenuContext {
    AccountMenuContext {
        base,
        account_view: view.to_owned(),
        player_name: info.username.clone(),
        player_email: info.email.clone(),
        player_avatar: info.avatar.clone(),
        player_profile: info.profile.clone(),
        player_messenger: info.messenger.clone(),
        player_vallars: info.vallars,
        player_immune: info.immune,
        player_class: info.class.clone(),
        player_freeze: info.freeze,
        player_roleplay: info.roleplay.clone(),
        player_ooc: info.ooc.clone(),
        player_short_rpg: info.short_rpg.clone(),
        available_styles: Vec::new(),
        current_style: String::new(),
        blocked_users: Vec::new(),
        links: Vec::new(),
        editing_link: None,
    }
}

/// Build an empty `AccountMenuContext` (for views that don't need player info).
fn empty_menu_ctx(base: RenderContext, view: &str) -> AccountMenuContext {
    AccountMenuContext {
        base,
        account_view: view.to_owned(),
        player_name: String::new(),
        player_email: String::new(),
        player_avatar: String::new(),
        player_profile: String::new(),
        player_messenger: String::new(),
        player_vallars: 0,
        player_immune: false,
        player_class: String::new(),
        player_freeze: 0,
        player_roleplay: String::new(),
        player_ooc: String::new(),
        player_short_rpg: String::new(),
        available_styles: Vec::new(),
        current_style: String::new(),
        blocked_users: Vec::new(),
        links: Vec::new(),
        editing_link: None,
    }
}
