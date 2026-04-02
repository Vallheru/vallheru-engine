//! Tribe hub handler — main tribe page, listing, viewing, creation,
//! joining and leaving.
//!
//! Ported from `tribes.php`.

use axum::{
    Extension, Form,
    extract::{Path, Query, State},
    response::Response,
};

use crate::middleware::context::RequestContext;
use crate::page::{Flash, FlashKind, PageMeta};
use crate::state::AppState;
use vallheru_data::queries::tribe as tq;
use vallheru_domain::group::tribe::{
    self, CreateTribeError, JoinRequestError, LeaveOutcome, LeaveTribeError, TribeLevel,
};

// =========================================================================
// View models
// =========================================================================

#[derive(serde::Serialize)]
pub struct TribeHubView {
    #[serde(flatten)]
    pub base: crate::render::RenderContext,
    pub has_tribe: bool,
    pub tribe: Option<TribeInfo>,
    pub members: Vec<MemberEntry>,
    pub is_owner: bool,
    pub can_admin: bool,
}

#[derive(serde::Serialize)]
pub struct TribeInfo {
    pub id: i32,
    pub name: String,
    pub owner_name: String,
    pub level: i16,
    pub level_name: String,
    pub public_msg: String,
    pub prefix: String,
    pub suffix: String,
    pub member_count: i64,
}

#[derive(serde::Serialize)]
pub struct MemberEntry {
    pub id: i32,
    pub name: String,
    pub level: i16,
    pub race: String,
    pub class: String,
    pub rank: String,
}

#[derive(serde::Serialize)]
pub struct TribeListView {
    #[serde(flatten)]
    pub base: crate::render::RenderContext,
    pub tribes: Vec<TribeListEntry>,
    pub page: i64,
    pub total_pages: i64,
}

#[derive(serde::Serialize)]
pub struct TribeListEntry {
    pub id: i32,
    pub name: String,
    pub owner_name: String,
    pub level: i16,
    pub member_count: i64,
}

#[derive(serde::Serialize)]
pub struct TribeViewPage {
    #[serde(flatten)]
    pub base: crate::render::RenderContext,
    pub tribe: TribeInfo,
    pub members: Vec<MemberEntry>,
    pub can_join: bool,
}

#[derive(serde::Serialize)]
pub struct TribeCreateView {
    #[serde(flatten)]
    pub base: crate::render::RenderContext,
    pub cost: i64,
}

// =========================================================================
// Form / query structs
// =========================================================================

#[derive(serde::Deserialize)]
pub struct TribeCreateForm {
    pub name: Option<String>,
    pub level: Option<i16>,
}

#[derive(serde::Deserialize)]
pub struct ListQuery {
    #[serde(default = "default_page")]
    pub page: i64,
}

fn default_page() -> i64 {
    1
}

// =========================================================================
// Private helpers
// =========================================================================

#[derive(Debug, Clone, sqlx::FromRow)]
struct PlayerRow {
    pub location: String,
    pub tribe: i32,
    pub credits: i64,
}

async fn load_player(app: &AppState, player_id: i32) -> Result<PlayerRow, Response> {
    sqlx::query_as::<_, PlayerRow>(
        "SELECT location, tribe_id AS tribe, credits FROM players WHERE id = $1",
    )
    .bind(player_id)
    .fetch_optional(&app.pool)
    .await
    .map_err(|_| server_error())?
    .ok_or_else(server_error)
}

fn error_page(state: &AppState, ctx: &RequestContext, message: &str) -> Response {
    let meta = PageMeta::titled("Błąd").with_flash(Flash {
        kind: FlashKind::Error,
        message: message.to_owned(),
    });
    let base = state.templates.build_context(ctx, &meta);
    state.templates.render("error.html", &base)
}

fn server_error() -> Response {
    use axum::response::IntoResponse;
    (
        axum::http::StatusCode::INTERNAL_SERVER_ERROR,
        "Internal error",
    )
        .into_response()
}

fn level_name(level: i16) -> &'static str {
    match level {
        1 => "Kryjówka",
        2 => "Kamienica",
        3 => "Dworek",
        4 => "Dwór",
        5 => "Zamek",
        _ => "Nieznany",
    }
}

async fn owner_name(app: &AppState, owner_id: i32) -> String {
    let row: Option<(String,)> = sqlx::query_as("SELECT name FROM players WHERE id = $1")
        .bind(owner_id)
        .fetch_optional(&app.pool)
        .await
        .unwrap_or(None);
    row.map_or_else(|| "Nieznany".to_owned(), |r| r.0)
}

fn map_members(rows: Vec<tq::TribeMemberRow>) -> Vec<MemberEntry> {
    rows.into_iter()
        .map(|r| MemberEntry {
            id: r.id,
            name: r.name,
            level: r.level,
            race: r.race,
            class: r.class,
            rank: r.rank,
        })
        .collect()
}

// =========================================================================
// Handlers
// =========================================================================

/// GET /tribe — main tribe hub.
pub async fn tribe_hub(
    State(app): State<AppState>,
    Extension(ctx): Extension<RequestContext>,
) -> Response {
    let Some(ref user) = ctx.session_user else {
        return crate::page::redirect("/login");
    };

    #[allow(clippy::cast_possible_truncation)]
    let player_id = user.id as i32;

    let player = match load_player(&app, player_id).await {
        Ok(row) => row,
        Err(resp) => return resp,
    };

    let meta = PageMeta::titled("Klan").with_back_link("/city", "Wróć do miasta");

    if player.tribe == 0 {
        let base = app.templates.build_context(&ctx, &meta);
        let view = TribeHubView {
            base,
            has_tribe: false,
            tribe: None,
            members: Vec::new(),
            is_owner: false,
            can_admin: false,
        };
        return app.templates.render_value("tribe.html", &view);
    }

    let tribe_row = match tq::tribe_by_id(&app.pool, player.tribe).await {
        Ok(Some(t)) => t,
        Ok(None) => return error_page(&app, &ctx, "Klan nie istnieje."),
        Err(_) => return server_error(),
    };

    let members_rows = tq::tribe_members(&app.pool, player.tribe)
        .await
        .unwrap_or_default();
    #[allow(clippy::cast_possible_wrap)]
    let member_count = members_rows.len() as i64;
    let is_owner = tribe_row.owner == player_id;

    let owner_display = owner_name(&app, tribe_row.owner).await;

    let info = TribeInfo {
        id: tribe_row.id,
        name: tribe_row.name,
        owner_name: owner_display,
        level: tribe_row.level,
        level_name: level_name(tribe_row.level).to_owned(),
        public_msg: tribe_row.public_msg,
        prefix: tribe_row.prefix,
        suffix: tribe_row.suffix,
        member_count,
    };

    let base = app.templates.build_context(&ctx, &meta);
    let view = TribeHubView {
        base,
        has_tribe: true,
        tribe: Some(info),
        members: map_members(members_rows),
        is_owner,
        can_admin: is_owner,
    };
    app.templates.render_value("tribe.html", &view)
}

/// GET /tribe/list — paginated tribe listing.
pub async fn tribe_list(
    State(app): State<AppState>,
    Extension(ctx): Extension<RequestContext>,
    Query(params): Query<ListQuery>,
) -> Response {
    let Some(ref _user) = ctx.session_user else {
        return crate::page::redirect("/login");
    };

    let page = params.page.max(1);
    let per_page: i64 = 20;

    let Ok(count) = tq::tribe_list_count(&app.pool).await else {
        return server_error();
    };

    let total_pages = (count + per_page - 1) / per_page;

    let Ok(rows) = tq::tribe_list(&app.pool, page, per_page).await else {
        return server_error();
    };

    let tribes: Vec<TribeListEntry> = rows
        .into_iter()
        .map(|r| TribeListEntry {
            id: r.id,
            name: r.name,
            owner_name: r.owner_name,
            level: r.level,
            member_count: r.member_count,
        })
        .collect();

    let meta = PageMeta::titled("Lista klanów").with_back_link("/tribe", "Wróć do klanu");
    let base = app.templates.build_context(&ctx, &meta);
    let view = TribeListView {
        base,
        tribes,
        page,
        total_pages,
    };
    app.templates.render_value("tribe_list.html", &view)
}

/// GET /tribe/view/{id} — view a specific tribe's public info.
pub async fn tribe_view(
    State(app): State<AppState>,
    Extension(ctx): Extension<RequestContext>,
    Path(tribe_id): Path<i32>,
) -> Response {
    let Some(ref user) = ctx.session_user else {
        return crate::page::redirect("/login");
    };

    #[allow(clippy::cast_possible_truncation)]
    let player_id = user.id as i32;

    let player = match load_player(&app, player_id).await {
        Ok(row) => row,
        Err(resp) => return resp,
    };

    let tribe_row = match tq::tribe_by_id(&app.pool, tribe_id).await {
        Ok(Some(t)) => t,
        Ok(None) => return error_page(&app, &ctx, "Klan nie istnieje."),
        Err(_) => return server_error(),
    };

    let members_rows = tq::tribe_members(&app.pool, tribe_id)
        .await
        .unwrap_or_default();
    #[allow(clippy::cast_possible_wrap)]
    let member_count = members_rows.len() as i64;

    let owner_display = owner_name(&app, tribe_row.owner).await;

    let info = TribeInfo {
        id: tribe_row.id,
        name: tribe_row.name,
        owner_name: owner_display,
        level: tribe_row.level,
        level_name: level_name(tribe_row.level).to_owned(),
        public_msg: tribe_row.public_msg,
        prefix: tribe_row.prefix,
        suffix: tribe_row.suffix,
        member_count,
    };

    let can_join = player.tribe == 0;

    let meta = PageMeta::titled(&info.name).with_back_link("/tribe/list", "Wróć do listy");
    let base = app.templates.build_context(&ctx, &meta);
    let view = TribeViewPage {
        base,
        tribe: info,
        members: map_members(members_rows),
        can_join,
    };
    app.templates.render_value("tribe_view.html", &view)
}

/// GET /tribe/create — show creation form.
pub async fn tribe_create_show(
    State(app): State<AppState>,
    Extension(ctx): Extension<RequestContext>,
) -> Response {
    let Some(ref user) = ctx.session_user else {
        return crate::page::redirect("/login");
    };

    #[allow(clippy::cast_possible_truncation)]
    let player_id = user.id as i32;

    let player = match load_player(&app, player_id).await {
        Ok(row) => row,
        Err(resp) => return resp,
    };

    if player.tribe != 0 {
        return error_page(&app, &ctx, "Już należysz do klanu.");
    }

    if player.location != "Altara" && player.location != "Ardulith" {
        return error_page(
            &app,
            &ctx,
            "Musisz znajdować się w mieście z biurem klanów.",
        );
    }

    let cost = TribeLevel::Hideout.creation_cost();

    let meta = PageMeta::titled("Załóż klan").with_back_link("/tribe", "Wróć do klanu");
    let base = app.templates.build_context(&ctx, &meta);
    let view = TribeCreateView { base, cost };
    app.templates.render_value("tribe_create.html", &view)
}

/// POST /tribe/create — create a new tribe.
pub async fn tribe_create(
    State(app): State<AppState>,
    Extension(ctx): Extension<RequestContext>,
    Form(form): Form<TribeCreateForm>,
) -> Response {
    let Some(ref user) = ctx.session_user else {
        return crate::page::redirect("/login");
    };

    #[allow(clippy::cast_possible_truncation)]
    let player_id = user.id as i32;

    let player = match load_player(&app, player_id).await {
        Ok(row) => row,
        Err(resp) => return resp,
    };

    let name = form.name.as_deref().unwrap_or("").trim();
    let _level = form.level.unwrap_or(1);

    let cost =
        match tribe::validate_create_tribe(player.tribe, player.credits, name, &player.location) {
            Ok(cost) => cost,
            Err(CreateTribeError::AlreadyInTribe) => {
                return error_page(&app, &ctx, "Już należysz do klanu.");
            }
            Err(CreateTribeError::InsufficientGold) => {
                return error_page(&app, &ctx, "Nie masz wystarczającej ilości złota.");
            }
            Err(CreateTribeError::InvalidName) => {
                return error_page(&app, &ctx, "Nieprawidłowa nazwa klanu.");
            }
            Err(CreateTribeError::WrongLocation) => {
                return error_page(
                    &app,
                    &ctx,
                    "Musisz znajdować się w mieście z biurem klanów.",
                );
            }
        };

    match tq::create_tribe(&app.pool, name, player_id, 1, cost).await {
        Ok(_tribe_id) => crate::page::redirect_after_post("/tribe"),
        Err(_) => server_error(),
    }
}

/// POST /tribe/join/{id} — request to join a tribe.
pub async fn tribe_join(
    State(app): State<AppState>,
    Extension(ctx): Extension<RequestContext>,
    Path(tribe_id): Path<i32>,
) -> Response {
    let Some(ref user) = ctx.session_user else {
        return crate::page::redirect("/login");
    };

    #[allow(clippy::cast_possible_truncation)]
    let player_id = user.id as i32;

    let player = match load_player(&app, player_id).await {
        Ok(row) => row,
        Err(resp) => return resp,
    };

    // Verify the tribe exists
    let Ok(Some(_)) = tq::tribe_by_id(&app.pool, tribe_id).await else {
        return error_page(&app, &ctx, "Klan nie istnieje.");
    };

    let Ok(already_requested) = tq::join_request_exists(&app.pool, player_id, tribe_id).await
    else {
        return server_error();
    };

    match tribe::validate_join_request(player.tribe, already_requested, &player.location) {
        Ok(()) => {}
        Err(JoinRequestError::AlreadyInTribe) => {
            return error_page(&app, &ctx, "Już należysz do klanu.");
        }
        Err(JoinRequestError::AlreadyRequested) => {
            return error_page(
                &app,
                &ctx,
                "Już wysłałeś prośbę o dołączenie do tego klanu.",
            );
        }
        Err(JoinRequestError::WrongLocation) => {
            return error_page(
                &app,
                &ctx,
                "Musisz znajdować się w mieście z biurem klanów.",
            );
        }
    }

    match tq::create_join_request(&app.pool, player_id, tribe_id).await {
        Ok(()) => crate::page::redirect_after_post(&format!("/tribe/view/{tribe_id}")),
        Err(_) => server_error(),
    }
}

/// POST /tribe/leave — leave the current tribe.
pub async fn tribe_leave(
    State(app): State<AppState>,
    Extension(ctx): Extension<RequestContext>,
) -> Response {
    let Some(ref user) = ctx.session_user else {
        return crate::page::redirect("/login");
    };

    #[allow(clippy::cast_possible_truncation)]
    let player_id = user.id as i32;

    let player = match load_player(&app, player_id).await {
        Ok(row) => row,
        Err(resp) => return resp,
    };

    if player.tribe == 0 {
        return error_page(&app, &ctx, "Nie należysz do żadnego klanu.");
    }

    let tribe_row = match tq::tribe_by_id(&app.pool, player.tribe).await {
        Ok(Some(t)) => t,
        Ok(None) => return error_page(&app, &ctx, "Klan nie istnieje."),
        Err(_) => return server_error(),
    };

    let Ok(members) = tq::tribe_members(&app.pool, player.tribe).await else {
        return server_error();
    };

    let other_ids: Vec<i32> = members
        .iter()
        .filter(|m| m.id != player_id)
        .map(|m| m.id)
        .collect();

    let outcome = match tribe::leave_tribe(player_id, player.tribe, tribe_row.owner, other_ids) {
        Ok(o) => o,
        Err(LeaveTribeError::NotInTribe) => {
            return error_page(&app, &ctx, "Nie należysz do żadnego klanu.");
        }
    };

    match outcome {
        LeaveOutcome::MemberLeft => {
            if tq::leave_tribe(&app.pool, player_id).await.is_err() {
                return server_error();
            }
        }
        LeaveOutcome::Dissolved { member_ids } => {
            if tq::dissolve_tribe(&app.pool, player.tribe, &member_ids)
                .await
                .is_err()
            {
                return server_error();
            }
        }
    }

    crate::page::redirect_after_post("/tribe")
}
