//! Tribe administration handlers — permissions, ranks, messages, kicks,
//! purchases, loans, and upgrades.
//!
//! Ported from `tribeadmin.php`.

use axum::{
    Extension, Form,
    extract::{Path, State},
    response::Response,
};

use crate::middleware::context::RequestContext;
use crate::page::{Flash, FlashKind, PageMeta};
use crate::state::AppState;
use vallheru_data::queries::tribe as tq;
use vallheru_domain::group::tribe::{
    ArmyPurchase, DefencePurchase, LoanCurrency, TribeLevel, validate_buy_army,
    validate_buy_defences, validate_buy_hospital_pass, validate_kick, validate_loan,
    validate_upgrade,
};
use vallheru_domain::group::tribe_admin::{
    self, ALL_PERMISSIONS, PermissionSet, TribePermission, available_admin_actions,
    can_access_admin, has_admin_permission, validate_assign_rank, validate_edit_messages,
    validate_manage_pending, validate_rank_labels, validate_set_permissions, validate_set_tags,
};

// =========================================================================
// View models
// =========================================================================

#[derive(serde::Serialize)]
pub struct TribeAdminView {
    #[serde(flatten)]
    pub base: crate::render::RenderContext,
    pub tribe_name: String,
    pub actions: Vec<String>,
}

#[derive(serde::Serialize)]
pub struct PermissionsView {
    #[serde(flatten)]
    pub base: crate::render::RenderContext,
    pub members: Vec<PermMemberEntry>,
    pub permission_labels: Vec<&'static str>,
}

#[derive(serde::Serialize)]
pub struct PermMemberEntry {
    pub id: i32,
    pub name: String,
    pub flags: Vec<bool>,
}

#[derive(serde::Serialize)]
pub struct RanksView {
    #[serde(flatten)]
    pub base: crate::render::RenderContext,
    pub labels: Vec<String>,
    pub members: Vec<RankMemberEntry>,
}

#[derive(serde::Serialize)]
pub struct RankMemberEntry {
    pub id: i32,
    pub name: String,
    pub rank: String,
}

#[derive(serde::Serialize)]
pub struct MessagesView {
    #[serde(flatten)]
    pub base: crate::render::RenderContext,
    pub public_msg: String,
    pub private_msg: String,
    pub prefix: String,
    pub suffix: String,
}

#[derive(serde::Serialize)]
pub struct PendingView {
    #[serde(flatten)]
    pub base: crate::render::RenderContext,
    pub requests: Vec<PendingEntry>,
}

#[derive(serde::Serialize)]
pub struct PendingEntry {
    pub id: i32,
    pub player_name: String,
}

#[derive(serde::Serialize)]
pub struct RequestsView {
    #[serde(flatten)]
    pub base: crate::render::RenderContext,
    pub reservations: Vec<ReservationEntry>,
}

#[derive(serde::Serialize)]
pub struct ReservationEntry {
    pub id: i32,
    pub player_name: String,
    pub item_name: String,
    pub amount: i32,
    pub r#type: String,
}

// =========================================================================
// Form structs
// =========================================================================

#[derive(serde::Deserialize)]
pub struct PermissionsForm {
    pub player_id: i32,
    #[serde(default)]
    pub messages: Option<String>,
    #[serde(default)]
    pub wait: Option<String>,
    #[serde(default)]
    pub kick: Option<String>,
    #[serde(default)]
    pub army: Option<String>,
    #[serde(default)]
    pub attack: Option<String>,
    #[serde(default)]
    pub loan: Option<String>,
    #[serde(default)]
    pub armory: Option<String>,
    #[serde(default)]
    pub warehouse: Option<String>,
    #[serde(default)]
    pub bank: Option<String>,
    #[serde(default)]
    pub herbs: Option<String>,
    #[serde(default)]
    pub forum: Option<String>,
    #[serde(default)]
    pub mail: Option<String>,
    #[serde(default)]
    pub ranks: Option<String>,
    #[serde(default)]
    pub info: Option<String>,
    #[serde(default)]
    pub astralvault: Option<String>,
}

#[derive(serde::Deserialize)]
pub struct RanksForm {
    #[serde(default)]
    pub rank1: String,
    #[serde(default)]
    pub rank2: String,
    #[serde(default)]
    pub rank3: String,
    #[serde(default)]
    pub rank4: String,
    #[serde(default)]
    pub rank5: String,
    #[serde(default)]
    pub rank6: String,
    #[serde(default)]
    pub rank7: String,
    #[serde(default)]
    pub rank8: String,
    #[serde(default)]
    pub rank9: String,
    #[serde(default)]
    pub rank10: String,
}

#[derive(serde::Deserialize)]
pub struct RankAssignForm {
    pub player_id: i32,
    #[serde(default)]
    pub rank_label: String,
}

#[derive(serde::Deserialize)]
pub struct MessagesForm {
    #[serde(default)]
    pub public_msg: String,
    #[serde(default)]
    pub private_msg: String,
}

#[derive(serde::Deserialize)]
pub struct TagsForm {
    #[serde(default)]
    pub prefix: String,
    #[serde(default)]
    pub suffix: String,
}

#[derive(serde::Deserialize)]
pub struct KickForm {
    pub player_id: i32,
}

#[derive(serde::Deserialize)]
pub struct DefencesForm {
    #[serde(default)]
    pub buy_traps: Option<u32>,
    #[serde(default)]
    pub buy_agents: Option<u32>,
}

#[derive(serde::Deserialize)]
pub struct ArmyForm {
    #[serde(default)]
    pub soldiers: Option<u32>,
    #[serde(default)]
    pub forts: Option<u32>,
}

#[derive(serde::Deserialize)]
pub struct LoanForm {
    pub player_id: i32,
    #[serde(default)]
    pub currency: String,
    #[serde(default)]
    pub amount: Option<i64>,
}

#[derive(serde::Deserialize)]
pub struct UpgradeForm {
    pub target_level: i16,
}

#[derive(serde::Deserialize)]
pub struct DeleteReservationsForm {
    #[serde(default)]
    pub ids: Vec<i32>,
}

// =========================================================================
// Private helpers
// =========================================================================

#[derive(Debug, Clone, sqlx::FromRow)]
struct PlayerRow {
    pub tribe: i32,
}

async fn load_player(app: &AppState, player_id: i32) -> Result<PlayerRow, Response> {
    sqlx::query_as::<_, PlayerRow>("SELECT tribe_id AS tribe FROM players WHERE id = $1")
        .bind(player_id)
        .fetch_optional(&app.pool)
        .await
        .map_err(|_| server_error())?
        .ok_or_else(server_error)
}

fn error_page(state: &AppState, ctx: &RequestContext, message: &str) -> Response {
    let meta = PageMeta::titled("Administracja klanu").with_flash(Flash {
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

fn perms_from_row(row: &tq::TribePermRow) -> PermissionSet {
    PermissionSet::from_flags([
        row.messages != 0,
        row.wait != 0,
        row.kick != 0,
        row.army != 0,
        row.attack != 0,
        row.loan != 0,
        row.armory != 0,
        row.warehouse != 0,
        row.bank != 0,
        row.herbs != 0,
        row.forum != 0,
        row.mail != 0,
        row.ranks != 0,
        row.info != 0,
        row.astralvault != 0,
    ])
}

/// Load tribe + permissions, returning an error page if anything is missing.
async fn load_tribe_and_perms(
    app: &AppState,
    ctx: &RequestContext,
    player_id: i32,
    tribe_id: i32,
) -> Result<(tq::TribeRow, PermissionSet), Response> {
    let tribe = match tq::tribe_by_id(&app.pool, tribe_id).await {
        Ok(Some(t)) => t,
        Ok(None) => return Err(error_page(app, ctx, "Klan nie istnieje.")),
        Err(_) => return Err(server_error()),
    };

    let perms = match tq::tribe_perm_for_player(&app.pool, tribe_id, player_id).await {
        Ok(Some(row)) => perms_from_row(&row),
        Ok(None) => PermissionSet::default(),
        Err(_) => return Err(server_error()),
    };

    if !can_access_admin(player_id, tribe.owner, perms) {
        return Err(error_page(
            app,
            ctx,
            "Nie masz dostępu do panelu administracji.",
        ));
    }

    Ok((tribe, perms))
}

// =========================================================================
// Handlers
// =========================================================================

/// GET /tribe/admin — main admin panel.
pub async fn tribe_admin_show(
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

    let (tribe, _perms) = match load_tribe_and_perms(&app, &ctx, player_id, player.tribe).await {
        Ok(v) => v,
        Err(resp) => return resp,
    };

    let level = TribeLevel::from_db(tribe.level).unwrap_or(TribeLevel::Hideout);
    let is_owner = tribe.owner == player_id;
    let actions: Vec<String> = available_admin_actions(level, is_owner)
        .into_iter()
        .map(std::borrow::ToOwned::to_owned)
        .collect();

    let meta = PageMeta::titled("Administracja klanu").with_back_link("/tribe", "Wróć do klanu");
    let base = app.templates.build_context(&ctx, &meta);
    let view = TribeAdminView {
        base,
        tribe_name: tribe.name,
        actions,
    };
    app.templates.render_value("tribe_admin.html", &view)
}

/// GET /tribe/admin/permissions — permission editor.
pub async fn tribe_admin_permissions_show(
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

    let (tribe, _perms) = match load_tribe_and_perms(&app, &ctx, player_id, player.tribe).await {
        Ok(v) => v,
        Err(resp) => return resp,
    };

    if tribe.owner != player_id {
        return error_page(
            &app,
            &ctx,
            "Tylko właściciel klanu może zmieniać uprawnienia.",
        );
    }

    let members = tq::tribe_members(&app.pool, player.tribe)
        .await
        .unwrap_or_default();

    let mut member_entries = Vec::new();
    for m in &members {
        if m.id == player_id {
            continue; // Skip owner
        }
        let perm_row = match tq::tribe_perm_for_player(&app.pool, player.tribe, m.id).await {
            Ok(v) => v,
            Err(e) => {
                tracing::error!(error = %e, tribe_id = player.tribe, member_id = m.id, "Failed to load tribe permissions for member");
                None
            }
        };
        let flags = match perm_row {
            Some(ref row) => perms_from_row(row).to_flags().to_vec(),
            None => vec![false; 15],
        };
        member_entries.push(PermMemberEntry {
            id: m.id,
            name: m.name.clone(),
            flags,
        });
    }

    let permission_labels: Vec<&'static str> =
        ALL_PERMISSIONS.iter().map(|p| p.column_name()).collect();

    let meta =
        PageMeta::titled("Uprawnienia").with_back_link("/tribe/admin", "Wróć do administracji");
    let base = app.templates.build_context(&ctx, &meta);
    let view = PermissionsView {
        base,
        members: member_entries,
        permission_labels,
    };
    app.templates
        .render_value("tribe_admin_permissions.html", &view)
}

/// POST /tribe/admin/permissions — save permissions for a member.
pub async fn tribe_admin_permissions_save(
    State(app): State<AppState>,
    Extension(ctx): Extension<RequestContext>,
    Form(form): Form<PermissionsForm>,
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

    let (tribe, _perms) = match load_tribe_and_perms(&app, &ctx, player_id, player.tribe).await {
        Ok(v) => v,
        Err(resp) => return resp,
    };

    // Load target player's tribe to verify membership
    let Ok(target) = load_target_tribe(&app, form.player_id).await else {
        return server_error();
    };

    if let Err(e) = validate_set_permissions(player_id, tribe.owner, target, player.tribe) {
        return match e {
            tribe_admin::SetPermissionsError::NotOwner => error_page(
                &app,
                &ctx,
                "Tylko właściciel klanu może zmieniać uprawnienia.",
            ),
            tribe_admin::SetPermissionsError::NotInTribe => {
                error_page(&app, &ctx, "Ten gracz nie należy do Twojego klanu.")
            }
        };
    }

    let level = TribeLevel::from_db(tribe.level).unwrap_or(TribeLevel::Hideout);
    let mut pset = PermissionSet::from_flags([
        form.messages.is_some(),
        form.wait.is_some(),
        form.kick.is_some(),
        form.army.is_some(),
        form.attack.is_some(),
        form.loan.is_some(),
        form.armory.is_some(),
        form.warehouse.is_some(),
        form.bank.is_some(),
        form.herbs.is_some(),
        form.forum.is_some(),
        form.mail.is_some(),
        form.ranks.is_some(),
        form.info.is_some(),
        form.astralvault.is_some(),
    ]);
    pset.apply_level_mask(level);

    let bool_flags = pset.to_flags();
    let db_flags: [i16; 15] = std::array::from_fn(|i| i16::from(bool_flags[i]));

    match tq::upsert_permissions(&app.pool, player.tribe, form.player_id, db_flags).await {
        Ok(()) => crate::page::redirect_after_post("/tribe/admin/permissions"),
        Err(_) => server_error(),
    }
}

/// GET /tribe/admin/ranks — rank label editor + assignment.
pub async fn tribe_admin_ranks_show(
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

    let (tribe, perms) = match load_tribe_and_perms(&app, &ctx, player_id, player.tribe).await {
        Ok(v) => v,
        Err(resp) => return resp,
    };

    if !has_admin_permission(player_id, tribe.owner, perms, TribePermission::Ranks) {
        return error_page(&app, &ctx, "Nie masz uprawnień do zarządzania rangami.");
    }

    let rank_row = match tq::tribe_ranks(&app.pool, player.tribe).await {
        Ok(v) => v,
        Err(e) => {
            tracing::error!(error = %e, tribe_id = player.tribe, "Failed to load tribe ranks");
            None
        }
    };

    let labels = match rank_row {
        Some(ref r) => vec![
            r.rank1.clone(),
            r.rank2.clone(),
            r.rank3.clone(),
            r.rank4.clone(),
            r.rank5.clone(),
            r.rank6.clone(),
            r.rank7.clone(),
            r.rank8.clone(),
            r.rank9.clone(),
            r.rank10.clone(),
        ],
        None => vec![String::new(); 10],
    };

    let members = tq::tribe_members(&app.pool, player.tribe)
        .await
        .unwrap_or_default();

    let member_entries: Vec<RankMemberEntry> = members
        .into_iter()
        .map(|m| RankMemberEntry {
            id: m.id,
            name: m.name,
            rank: m.rank,
        })
        .collect();

    let meta = PageMeta::titled("Rangi").with_back_link("/tribe/admin", "Wróć do administracji");
    let base = app.templates.build_context(&ctx, &meta);
    let view = RanksView {
        base,
        labels,
        members: member_entries,
    };
    app.templates.render_value("tribe_admin_ranks.html", &view)
}

/// POST /tribe/admin/ranks — save rank labels.
pub async fn tribe_admin_ranks_save(
    State(app): State<AppState>,
    Extension(ctx): Extension<RequestContext>,
    Form(form): Form<RanksForm>,
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

    let (tribe, perms) = match load_tribe_and_perms(&app, &ctx, player_id, player.tribe).await {
        Ok(v) => v,
        Err(resp) => return resp,
    };

    let input_labels = [
        form.rank1,
        form.rank2,
        form.rank3,
        form.rank4,
        form.rank5,
        form.rank6,
        form.rank7,
        form.rank8,
        form.rank9,
        form.rank10,
    ];

    let labels = match validate_rank_labels(player_id, tribe.owner, perms, &input_labels) {
        Ok(l) => l,
        Err(tribe_admin::RankError::NoPermission) => {
            return error_page(&app, &ctx, "Nie masz uprawnień do zarządzania rangami.");
        }
        Err(tribe_admin::RankError::LabelTooLong) => {
            return error_page(&app, &ctx, "Nazwa rangi jest zbyt długa.");
        }
    };

    match tq::upsert_ranks(&app.pool, player.tribe, &labels).await {
        Ok(()) => crate::page::redirect_after_post("/tribe/admin/ranks"),
        Err(_) => server_error(),
    }
}

/// POST /tribe/admin/rank-assign — assign rank to a member.
pub async fn tribe_admin_rank_assign(
    State(app): State<AppState>,
    Extension(ctx): Extension<RequestContext>,
    Form(form): Form<RankAssignForm>,
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

    let (tribe, perms) = match load_tribe_and_perms(&app, &ctx, player_id, player.tribe).await {
        Ok(v) => v,
        Err(resp) => return resp,
    };

    let ranks_exist = match tq::tribe_ranks(&app.pool, player.tribe).await {
        Ok(v) => v.is_some(),
        Err(e) => {
            tracing::error!(error = %e, tribe_id = player.tribe, "Failed to check tribe ranks existence");
            false
        }
    };

    let Ok(target_tribe) = load_target_tribe(&app, form.player_id).await else {
        return server_error();
    };

    if let Err(e) = validate_assign_rank(
        player_id,
        tribe.owner,
        perms,
        ranks_exist,
        target_tribe,
        player.tribe,
    ) {
        return match e {
            tribe_admin::AssignRankError::NoPermission => {
                error_page(&app, &ctx, "Nie masz uprawnień do przydzielania rang.")
            }
            tribe_admin::AssignRankError::NoRanksDefined => {
                error_page(&app, &ctx, "Najpierw zdefiniuj rangi.")
            }
            tribe_admin::AssignRankError::NotInTribe => {
                error_page(&app, &ctx, "Ten gracz nie należy do Twojego klanu.")
            }
        };
    }

    match tq::assign_player_rank(&app.pool, form.player_id, &form.rank_label).await {
        Ok(()) => crate::page::redirect_after_post("/tribe/admin/ranks"),
        Err(_) => server_error(),
    }
}

/// GET /tribe/admin/messages — edit tribe messages and tags.
pub async fn tribe_admin_messages_show(
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

    let (tribe, perms) = match load_tribe_and_perms(&app, &ctx, player_id, player.tribe).await {
        Ok(v) => v,
        Err(resp) => return resp,
    };

    if !has_admin_permission(player_id, tribe.owner, perms, TribePermission::Messages) {
        return error_page(&app, &ctx, "Nie masz uprawnień do edycji wiadomości klanu.");
    }

    let meta = PageMeta::titled("Wiadomości klanu")
        .with_back_link("/tribe/admin", "Wróć do administracji");
    let base = app.templates.build_context(&ctx, &meta);
    let view = MessagesView {
        base,
        public_msg: tribe.public_msg,
        private_msg: tribe.private_msg,
        prefix: tribe.prefix,
        suffix: tribe.suffix,
    };
    app.templates
        .render_value("tribe_admin_messages.html", &view)
}

/// POST /tribe/admin/messages — save tribe messages.
pub async fn tribe_admin_messages_save(
    State(app): State<AppState>,
    Extension(ctx): Extension<RequestContext>,
    Form(form): Form<MessagesForm>,
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

    let (tribe, perms) = match load_tribe_and_perms(&app, &ctx, player_id, player.tribe).await {
        Ok(v) => v,
        Err(resp) => return resp,
    };

    if validate_edit_messages(player_id, tribe.owner, perms).is_err() {
        return error_page(&app, &ctx, "Nie masz uprawnień do edycji wiadomości klanu.");
    }

    match tq::update_tribe_messages(&app.pool, player.tribe, &form.public_msg, &form.private_msg)
        .await
    {
        Ok(()) => crate::page::redirect_after_post("/tribe/admin/messages"),
        Err(_) => server_error(),
    }
}

/// POST /tribe/admin/tags — save tribe prefix/suffix tags.
pub async fn tribe_admin_tags_save(
    State(app): State<AppState>,
    Extension(ctx): Extension<RequestContext>,
    Form(form): Form<TagsForm>,
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

    let (tribe, perms) = match load_tribe_and_perms(&app, &ctx, player_id, player.tribe).await {
        Ok(v) => v,
        Err(resp) => return resp,
    };

    let (prefix, suffix) =
        match validate_set_tags(player_id, tribe.owner, perms, &form.prefix, &form.suffix) {
            Ok(v) => v,
            Err(tribe_admin::SetTagsError::NoPermission) => {
                return error_page(&app, &ctx, "Nie masz uprawnień do edycji tagów klanu.");
            }
            Err(tribe_admin::SetTagsError::TagTooLong) => {
                return error_page(&app, &ctx, "Tag jest zbyt długi (max 5 znaków).");
            }
        };

    match tq::update_tribe_tags(&app.pool, player.tribe, &prefix, &suffix).await {
        Ok(()) => crate::page::redirect_after_post("/tribe/admin/messages"),
        Err(_) => server_error(),
    }
}

/// GET /tribe/admin/pending — show pending join requests.
pub async fn tribe_admin_pending_show(
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

    let (tribe, perms) = match load_tribe_and_perms(&app, &ctx, player_id, player.tribe).await {
        Ok(v) => v,
        Err(resp) => return resp,
    };

    if validate_manage_pending(player_id, tribe.owner, perms).is_err() {
        return error_page(
            &app,
            &ctx,
            "Nie masz uprawnień do zarządzania prośbami o dołączenie.",
        );
    }

    let requests = tq::pending_requests(&app.pool, player.tribe)
        .await
        .unwrap_or_default();

    let entries: Vec<PendingEntry> = requests
        .into_iter()
        .map(|r| PendingEntry {
            id: r.id,
            player_name: r.player_name,
        })
        .collect();

    let meta =
        PageMeta::titled("Oczekujący").with_back_link("/tribe/admin", "Wróć do administracji");
    let base = app.templates.build_context(&ctx, &meta);
    let view = PendingView {
        base,
        requests: entries,
    };
    app.templates
        .render_value("tribe_admin_pending.html", &view)
}

/// POST /tribe/admin/pending/accept/{id} — accept a join request.
pub async fn tribe_admin_pending_accept(
    State(app): State<AppState>,
    Extension(ctx): Extension<RequestContext>,
    Path(request_id): Path<i32>,
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

    let (tribe, perms) = match load_tribe_and_perms(&app, &ctx, player_id, player.tribe).await {
        Ok(v) => v,
        Err(resp) => return resp,
    };

    if validate_manage_pending(player_id, tribe.owner, perms).is_err() {
        return error_page(&app, &ctx, "Nie masz uprawnień do akceptowania graczy.");
    }

    // Find the request to get the player_id from it
    let requests = tq::pending_requests(&app.pool, player.tribe)
        .await
        .unwrap_or_default();

    let Some(req) = requests.iter().find(|r| r.id == request_id) else {
        return error_page(&app, &ctx, "Nie znaleziono prośby o dołączenie.");
    };

    match tq::accept_member(&app.pool, request_id, req.gracz, player.tribe).await {
        Ok(()) => crate::page::redirect_after_post("/tribe/admin/pending"),
        Err(_) => server_error(),
    }
}

/// POST /tribe/admin/pending/reject/{id} — reject a join request.
pub async fn tribe_admin_pending_reject(
    State(app): State<AppState>,
    Extension(ctx): Extension<RequestContext>,
    Path(request_id): Path<i32>,
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

    let (tribe, perms) = match load_tribe_and_perms(&app, &ctx, player_id, player.tribe).await {
        Ok(v) => v,
        Err(resp) => return resp,
    };

    if validate_manage_pending(player_id, tribe.owner, perms).is_err() {
        return error_page(&app, &ctx, "Nie masz uprawnień do odrzucania prośb.");
    }

    match tq::reject_request(&app.pool, request_id).await {
        Ok(()) => crate::page::redirect_after_post("/tribe/admin/pending"),
        Err(_) => server_error(),
    }
}

/// POST /tribe/admin/kick — kick a member from the tribe.
pub async fn tribe_admin_kick(
    State(app): State<AppState>,
    Extension(ctx): Extension<RequestContext>,
    Form(form): Form<KickForm>,
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

    let (tribe, perms) = match load_tribe_and_perms(&app, &ctx, player_id, player.tribe).await {
        Ok(v) => v,
        Err(resp) => return resp,
    };

    let has_kick_perm = has_admin_permission(player_id, tribe.owner, perms, TribePermission::Kick);

    let Ok(target_tribe) = load_target_tribe(&app, form.player_id).await else {
        return server_error();
    };

    if let Err(e) = validate_kick(
        player_id,
        tribe.owner,
        has_kick_perm,
        form.player_id,
        target_tribe,
        player.tribe,
    ) {
        return match e {
            vallheru_domain::group::tribe::KickMemberError::NotInTribe => {
                error_page(&app, &ctx, "Ten gracz nie należy do Twojego klanu.")
            }
            vallheru_domain::group::tribe::KickMemberError::CannotKickOwner => {
                error_page(&app, &ctx, "Nie można wyrzucić właściciela klanu.")
            }
            vallheru_domain::group::tribe::KickMemberError::NoPermission => {
                error_page(&app, &ctx, "Nie masz uprawnień do wyrzucania graczy.")
            }
        };
    }

    match tq::kick_member(&app.pool, form.player_id, player.tribe).await {
        Ok(()) => crate::page::redirect_after_post("/tribe/admin"),
        Err(_) => server_error(),
    }
}

/// POST /tribe/admin/defences — buy traps and agents.
pub async fn tribe_admin_defences(
    State(app): State<AppState>,
    Extension(ctx): Extension<RequestContext>,
    Form(form): Form<DefencesForm>,
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

    let (tribe, perms) = match load_tribe_and_perms(&app, &ctx, player_id, player.tribe).await {
        Ok(v) => v,
        Err(resp) => return resp,
    };

    let level = TribeLevel::from_db(tribe.level).unwrap_or(TribeLevel::Hideout);
    let has_army_perm = has_admin_permission(player_id, tribe.owner, perms, TribePermission::Army);

    #[allow(clippy::cast_sign_loss)]
    let purchase = DefencePurchase {
        buyer_id: player_id,
        owner_id: tribe.owner,
        buyer_has_army_perm: has_army_perm,
        level,
        current_traps: tribe.traps as u32,
        current_agents: tribe.agents as u32,
        buy_traps: form.buy_traps.unwrap_or(0),
        buy_agents: form.buy_agents.unwrap_or(0),
        tribe_gold: i64::from(tribe.credits),
    };

    let result = match validate_buy_defences(&purchase) {
        Ok(r) => r,
        Err(e) => {
            let msg = match e {
                vallheru_domain::group::tribe::BuyDefencesError::NoPermission => {
                    "Nie masz uprawnień do kupowania obrony."
                }
                vallheru_domain::group::tribe::BuyDefencesError::NothingToBuy => {
                    "Musisz podać ilość pułapek lub agentów do kupienia."
                }
                vallheru_domain::group::tribe::BuyDefencesError::TrapCapExceeded => {
                    "Przekroczono limit pułapek dla tego poziomu klanu."
                }
                vallheru_domain::group::tribe::BuyDefencesError::AgentCapExceeded => {
                    "Przekroczono limit agentów dla tego poziomu klanu."
                }
                vallheru_domain::group::tribe::BuyDefencesError::InsufficientGold => {
                    "Klan nie ma wystarczającej ilości złota."
                }
            };
            return error_page(&app, &ctx, msg);
        }
    };

    #[allow(clippy::cast_possible_truncation)]
    let new_traps = form.buy_traps.unwrap_or(0) as i16;
    #[allow(clippy::cast_possible_truncation)]
    let new_agents = form.buy_agents.unwrap_or(0) as i16;

    match tq::buy_defences(
        &app.pool,
        player.tribe,
        new_traps,
        new_agents,
        result.gold_cost,
    )
    .await
    {
        Ok(()) => crate::page::redirect_after_post("/tribe/admin"),
        Err(_) => server_error(),
    }
}

/// POST /tribe/admin/army — buy soldiers and fortifications.
pub async fn tribe_admin_army(
    State(app): State<AppState>,
    Extension(ctx): Extension<RequestContext>,
    Form(form): Form<ArmyForm>,
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

    let (tribe, perms) = match load_tribe_and_perms(&app, &ctx, player_id, player.tribe).await {
        Ok(v) => v,
        Err(resp) => return resp,
    };

    let level = TribeLevel::from_db(tribe.level).unwrap_or(TribeLevel::Hideout);
    let has_army_perm = has_admin_permission(player_id, tribe.owner, perms, TribePermission::Army);

    let purchase = ArmyPurchase {
        buyer_id: player_id,
        owner_id: tribe.owner,
        buyer_has_army_perm: has_army_perm,
        level,
        buy_soldiers: form.soldiers.unwrap_or(0),
        buy_fortifications: form.forts.unwrap_or(0),
        tribe_gold: i64::from(tribe.credits),
    };

    let result = match validate_buy_army(&purchase) {
        Ok(r) => r,
        Err(e) => {
            let msg = match e {
                vallheru_domain::group::tribe::BuyArmyError::NoPermission => {
                    "Nie masz uprawnień do kupowania wojska."
                }
                vallheru_domain::group::tribe::BuyArmyError::LevelTooLow => {
                    "Klan musi być na poziomie Zamku aby kupować wojsko."
                }
                vallheru_domain::group::tribe::BuyArmyError::NothingToBuy => {
                    "Musisz podać ilość żołnierzy lub fortyfikacji do kupienia."
                }
                vallheru_domain::group::tribe::BuyArmyError::InsufficientGold => {
                    "Klan nie ma wystarczającej ilości złota."
                }
            };
            return error_page(&app, &ctx, msg);
        }
    };

    #[allow(clippy::cast_possible_wrap)]
    let soldiers = result.soldiers_added as i32;
    #[allow(clippy::cast_possible_wrap)]
    let forts = result.fortifications_added as i32;

    match tq::buy_army(&app.pool, player.tribe, soldiers, forts, result.gold_cost).await {
        Ok(()) => crate::page::redirect_after_post("/tribe/admin"),
        Err(_) => server_error(),
    }
}

/// POST /tribe/admin/hospital-pass — buy hospital pass.
pub async fn tribe_admin_hospital_pass(
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

    let (tribe, _perms) = match load_tribe_and_perms(&app, &ctx, player_id, player.tribe).await {
        Ok(v) => v,
        Err(resp) => return resp,
    };

    if tribe.owner != player_id {
        return error_page(&app, &ctx, "Tylko właściciel klanu może kupić przepustkę.");
    }

    let tribe_has_pass = tribe.hospass == "Y";
    let tribe_mithril = i64::from(tribe.platinum);

    let cost = match validate_buy_hospital_pass(tribe_has_pass, tribe_mithril) {
        Ok(c) => c,
        Err(e) => {
            let msg = match e {
                vallheru_domain::group::tribe::BuyHospitalPassError::AlreadyOwned => {
                    "Klan już posiada przepustkę szpitalną."
                }
                vallheru_domain::group::tribe::BuyHospitalPassError::InsufficientMithril => {
                    "Klan nie ma wystarczającej ilości mithrilu."
                }
            };
            return error_page(&app, &ctx, msg);
        }
    };

    match tq::buy_hospital_pass(&app.pool, player.tribe, cost).await {
        Ok(()) => crate::page::redirect_after_post("/tribe/admin"),
        Err(_) => server_error(),
    }
}

/// POST /tribe/admin/loan — loan money to a member.
pub async fn tribe_admin_loan(
    State(app): State<AppState>,
    Extension(ctx): Extension<RequestContext>,
    Form(form): Form<LoanForm>,
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

    let (tribe, perms) = match load_tribe_and_perms(&app, &ctx, player_id, player.tribe).await {
        Ok(v) => v,
        Err(resp) => return resp,
    };

    let has_loan_perm = has_admin_permission(player_id, tribe.owner, perms, TribePermission::Loan);

    let Some(currency) = LoanCurrency::from_form_value(&form.currency) else {
        return error_page(&app, &ctx, "Nieprawidłowy typ waluty.");
    };

    let amount = form.amount.unwrap_or(0);

    let Ok(target_tribe) = load_target_tribe(&app, form.player_id).await else {
        return server_error();
    };

    let tribe_balance = match currency {
        LoanCurrency::Gold => i64::from(tribe.credits),
        LoanCurrency::Mithril => i64::from(tribe.platinum),
    };

    if let Err(e) = validate_loan(
        player_id,
        tribe.owner,
        has_loan_perm,
        target_tribe,
        player.tribe,
        amount,
        tribe_balance,
    ) {
        let msg = match e {
            vallheru_domain::group::tribe::LoanError::NoPermission => {
                "Nie masz uprawnień do pożyczania pieniędzy."
            }
            vallheru_domain::group::tribe::LoanError::NotInTribe => {
                "Ten gracz nie należy do Twojego klanu."
            }
            vallheru_domain::group::tribe::LoanError::InvalidAmount => {
                "Kwota musi być większa od zera."
            }
            vallheru_domain::group::tribe::LoanError::InsufficientFunds => {
                "Klan nie ma wystarczającej ilości środków."
            }
        };
        return error_page(&app, &ctx, msg);
    }

    let currency_str = match currency {
        LoanCurrency::Gold => "credits",
        LoanCurrency::Mithril => "platinum",
    };

    match tq::loan_to_member(
        &app.pool,
        player.tribe,
        form.player_id,
        currency_str,
        amount,
    )
    .await
    {
        Ok(()) => crate::page::redirect_after_post("/tribe/admin"),
        Err(_) => server_error(),
    }
}

/// POST /tribe/admin/upgrade — upgrade tribe level.
pub async fn tribe_admin_upgrade(
    State(app): State<AppState>,
    Extension(ctx): Extension<RequestContext>,
    Form(form): Form<UpgradeForm>,
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

    let (tribe, _perms) = match load_tribe_and_perms(&app, &ctx, player_id, player.tribe).await {
        Ok(v) => v,
        Err(resp) => return resp,
    };

    let current_level = TribeLevel::from_db(tribe.level).unwrap_or(TribeLevel::Hideout);
    let tribe_gold = i64::from(tribe.credits);

    let (target, cost) = match validate_upgrade(
        player_id,
        tribe.owner,
        current_level,
        form.target_level,
        tribe_gold,
    ) {
        Ok(v) => v,
        Err(e) => {
            let msg = match e {
                vallheru_domain::group::tribe::UpgradeTribeError::NotOwner => {
                    "Tylko właściciel klanu może ulepszać klan."
                }
                vallheru_domain::group::tribe::UpgradeTribeError::InvalidTarget => {
                    "Nieprawidłowy poziom docelowy."
                }
                vallheru_domain::group::tribe::UpgradeTribeError::ExceedsMaxLevel => {
                    "Klan jest już na maksymalnym poziomie."
                }
                vallheru_domain::group::tribe::UpgradeTribeError::InsufficientGold => {
                    "Klan nie ma wystarczającej ilości złota."
                }
            };
            return error_page(&app, &ctx, msg);
        }
    };

    match tq::upgrade_tribe(&app.pool, player.tribe, target.to_db(), cost).await {
        Ok(()) => crate::page::redirect_after_post("/tribe/admin"),
        Err(_) => server_error(),
    }
}

/// GET /tribe/admin/requests — show reservation requests.
pub async fn tribe_admin_requests_show(
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

    let (_tribe, _perms) = match load_tribe_and_perms(&app, &ctx, player_id, player.tribe).await {
        Ok(v) => v,
        Err(resp) => return resp,
    };

    let reservations = tq::reservations_for_tribe(&app.pool, player.tribe)
        .await
        .unwrap_or_default();

    let entries: Vec<ReservationEntry> = reservations
        .into_iter()
        .map(|r| ReservationEntry {
            id: r.id,
            player_name: r.player_name,
            item_name: r.item_name,
            amount: r.amount,
            r#type: r.r#type,
        })
        .collect();

    let meta =
        PageMeta::titled("Rezerwacje").with_back_link("/tribe/admin", "Wróć do administracji");
    let base = app.templates.build_context(&ctx, &meta);
    let view = RequestsView {
        base,
        reservations: entries,
    };
    app.templates
        .render_value("tribe_admin_requests.html", &view)
}

/// POST /tribe/admin/requests/delete — delete selected reservations.
pub async fn tribe_admin_requests_delete(
    State(app): State<AppState>,
    Extension(ctx): Extension<RequestContext>,
    Form(form): Form<DeleteReservationsForm>,
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

    let (_tribe, _perms) = match load_tribe_and_perms(&app, &ctx, player_id, player.tribe).await {
        Ok(v) => v,
        Err(resp) => return resp,
    };

    if form.ids.is_empty() {
        return error_page(&app, &ctx, "Nie zaznaczono żadnych rezerwacji.");
    }

    match tq::delete_reservations(&app.pool, &form.ids).await {
        Ok(()) => crate::page::redirect_after_post("/tribe/admin/requests"),
        Err(_) => server_error(),
    }
}

// =========================================================================
// Small helpers
// =========================================================================

/// Load a target player's tribe ID for validation.
async fn load_target_tribe(app: &AppState, target_id: i32) -> Result<i32, ()> {
    let row: Option<(i32,)> = sqlx::query_as("SELECT tribe_id FROM players WHERE id = $1")
        .bind(target_id)
        .fetch_optional(&app.pool)
        .await
        .map_err(|_| ())?;
    Ok(row.map_or(0, |r| r.0))
}
