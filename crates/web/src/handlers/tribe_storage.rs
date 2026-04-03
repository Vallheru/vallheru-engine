//! Tribe shared storage handlers — armory, warehouse, herbs, minerals.
//!
//! Ported from `tribearmor.php`, `tribeware.php`, `tribeherbs.php`,
//! `tribeminerals.php`.

use axum::{Extension, Form, extract::State, response::Response};

use crate::middleware::context::RequestContext;
use crate::page::{Flash, FlashKind, PageMeta};
use crate::state::AppState;
use vallheru_data::queries::tribe as tq;
use vallheru_domain::group::tribe::TribeLevel;
use vallheru_domain::group::tribe_admin::{PermissionSet, TribePermission};
use vallheru_domain::group::tribe_storage::{
    GiveCheck, StorageArea, available_quantity, validate_access, validate_give,
    validate_give_permission, validate_reserve,
};

// =========================================================================
// View models
// =========================================================================

#[derive(serde::Serialize)]
pub struct ArmoryView {
    #[serde(flatten)]
    pub base: crate::render::RenderContext,
    pub items: Vec<ArmoryEntry>,
    pub page: i64,
    pub total_pages: i64,
    pub can_give: bool,
    pub members: Vec<MemberEntry>,
    pub type_filter: String,
    pub min_level: String,
    pub max_level: String,
}

#[derive(serde::Serialize)]
pub struct ArmoryEntry {
    pub id: i32,
    pub name: String,
    pub power: i32,
    pub wt: i32,
    pub maxwt: i32,
    pub zr: i32,
    pub szyb: i32,
    pub minlev: i32,
    pub item_type: String,
    pub magic: String,
    pub amount: i32,
    pub reserved: i32,
    pub available: i32,
    pub twohand: String,
}

#[derive(serde::Serialize)]
pub struct WarehouseView {
    #[serde(flatten)]
    pub base: crate::render::RenderContext,
    pub potions: Vec<PotionEntry>,
    pub can_give: bool,
    pub members: Vec<MemberEntry>,
}

#[derive(serde::Serialize)]
pub struct PotionEntry {
    pub id: i32,
    pub name: String,
    pub efect: String,
    pub power: i32,
    pub amount: i32,
    pub reserved: i32,
    pub available: i32,
    pub potion_type: String,
}

#[derive(serde::Serialize)]
pub struct HerbsView {
    #[serde(flatten)]
    pub base: crate::render::RenderContext,
    pub herbs: Vec<HerbEntry>,
    pub can_give: bool,
    pub members: Vec<MemberEntry>,
}

#[derive(serde::Serialize)]
pub struct HerbEntry {
    pub key: String,
    pub label: String,
    pub amount: i32,
    pub reserved: i32,
    pub available: i32,
}

#[derive(serde::Serialize)]
pub struct MineralsView {
    #[serde(flatten)]
    pub base: crate::render::RenderContext,
    pub minerals: Vec<MineralEntry>,
    pub gold: i32,
    pub mithril: i32,
    pub can_give: bool,
    pub members: Vec<MemberEntry>,
}

#[derive(serde::Serialize)]
pub struct MineralEntry {
    pub key: String,
    pub label: String,
    pub amount: i32,
    pub reserved: i32,
    pub available: i32,
}

#[derive(serde::Serialize, Clone)]
pub struct MemberEntry {
    pub id: i32,
    pub name: String,
}

// =========================================================================
// Form inputs
// =========================================================================

#[derive(serde::Deserialize)]
pub struct ArmoryFilterParams {
    #[serde(default)]
    pub type_filter: Option<String>,
    #[serde(default)]
    pub min_level: Option<i32>,
    #[serde(default)]
    pub max_level: Option<i32>,
    #[serde(default = "default_page")]
    pub page: i64,
}

fn default_page() -> i64 {
    1
}

#[derive(serde::Deserialize)]
pub struct DepositItemForm {
    pub item_id: i32,
    #[serde(default = "default_amount")]
    pub amount: i32,
}

#[derive(serde::Deserialize)]
pub struct GiveItemForm {
    pub item_id: i32,
    pub recipient_id: i32,
    #[serde(default = "default_amount")]
    pub amount: i32,
}

#[derive(serde::Deserialize)]
pub struct ReserveItemForm {
    pub item_id: i32,
    #[serde(default = "default_amount")]
    pub amount: i32,
}

#[derive(serde::Deserialize)]
pub struct HerbActionForm {
    pub herb_key: String,
    #[serde(default = "default_amount")]
    pub amount: i32,
    /// Only used for give
    #[serde(default)]
    pub recipient_id: Option<i32>,
}

#[derive(serde::Deserialize)]
pub struct MineralActionForm {
    pub mineral_key: String,
    #[serde(default = "default_amount")]
    pub amount: i32,
    /// Only used for give
    #[serde(default)]
    pub recipient_id: Option<i32>,
}

fn default_amount() -> i32 {
    1
}

// =========================================================================
// Helpers
// =========================================================================

#[derive(Debug, Clone, sqlx::FromRow)]
pub(crate) struct PlayerRow {
    pub tribe: i32,
}

pub(crate) async fn load_player(app: &AppState, player_id: i32) -> Result<PlayerRow, Response> {
    sqlx::query_as::<_, PlayerRow>("SELECT tribe_id AS tribe FROM players WHERE id = $1")
        .bind(player_id)
        .fetch_optional(&app.pool)
        .await
        .map_err(|_| server_error())?
        .ok_or_else(server_error)
}

pub(crate) fn storage_error_page(
    state: &AppState,
    ctx: &RequestContext,
    title: &str,
    message: &str,
) -> Response {
    let meta = PageMeta::titled(title).with_flash(Flash {
        kind: FlashKind::Error,
        message: message.to_owned(),
    });
    let base = state.templates.build_context(ctx, &meta);
    state.templates.render("error.html", &base)
}

fn error_page(state: &AppState, ctx: &RequestContext, message: &str) -> Response {
    storage_error_page(state, ctx, "Magazyn klanu", message)
}

pub(crate) fn server_error() -> Response {
    use axum::response::IntoResponse;
    (
        axum::http::StatusCode::INTERNAL_SERVER_ERROR,
        "Internal error",
    )
        .into_response()
}

pub(crate) fn perms_from_row(row: &tq::TribePermRow) -> PermissionSet {
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

/// Load tribe, check storage area access, return tribe + perms.
pub(crate) async fn load_tribe_and_storage_access(
    app: &AppState,
    ctx: &RequestContext,
    player_id: i32,
    player_tribe: i32,
    area: StorageArea,
) -> Result<(tq::TribeRow, PermissionSet), Response> {
    let tribe = tq::tribe_by_id(&app.pool, player_tribe)
        .await
        .map_err(|_| server_error())?
        .ok_or_else(|| error_page(app, ctx, "Klan nie istnieje."))?;

    let level = TribeLevel::from_db(tribe.level).unwrap_or(TribeLevel::Hideout);

    if validate_access(Some(player_tribe), tribe.id, level, area).is_err() {
        return Err(error_page(
            app,
            ctx,
            "Twój klan nie ma jeszcze dostępu do tego magazynu.",
        ));
    }

    let perm_row = tq::tribe_perm_for_player(&app.pool, tribe.id, player_id)
        .await
        .map_err(|_| server_error())?;
    let perms = perm_row.map(|r| perms_from_row(&r)).unwrap_or_default();

    Ok((tribe, perms))
}

pub(crate) fn can_give(
    player_id: i32,
    owner_id: i32,
    perms: PermissionSet,
    perm: TribePermission,
) -> bool {
    validate_give_permission(player_id, owner_id, perms.has(perm)).is_ok()
}

pub(crate) async fn load_members(
    app: &AppState,
    tribe_id: i32,
) -> Result<Vec<MemberEntry>, Response> {
    let rows = tq::tribe_members(&app.pool, tribe_id)
        .await
        .map_err(|_| server_error())?;
    Ok(rows
        .into_iter()
        .map(|m| MemberEntry {
            id: m.id,
            name: m.name,
        })
        .collect())
}

/// Common guard: check session, load player, verify tribe membership.
pub(crate) async fn require_tribe_member(
    app: &AppState,
    ctx: &RequestContext,
) -> Result<(i32, PlayerRow), Response> {
    let Some(ref user) = ctx.session_user else {
        return Err(crate::page::redirect("/login"));
    };
    #[allow(clippy::cast_possible_truncation)]
    let player_id = user.id as i32;
    let player = load_player(app, player_id).await?;
    if player.tribe == 0 {
        return Err(error_page(app, ctx, "Nie należysz do żadnego klanu."));
    }
    Ok((player_id, player))
}

// =========================================================================
// Herb key validation
// =========================================================================

const HERB_KEYS: &[(&str, &str)] = &[
    ("illani", "Illani"),
    ("illanias", "Illanias"),
    ("nutari", "Nutari"),
    ("dynallca", "Dynallca"),
    ("ilani_seeds", "Nasiona Illani"),
    ("illanias_seeds", "Nasiona Illanias"),
    ("nutari_seeds", "Nasiona Nutari"),
    ("dynallca_seeds", "Nasiona Dynallca"),
];

fn is_valid_herb_key(key: &str) -> bool {
    HERB_KEYS.iter().any(|(k, _)| *k == key)
}

fn herb_amount_from_row(row: &tq::TribeHerbsRow, key: &str) -> (i32, i32) {
    match key {
        "illani" => (row.illani, row.rillani),
        "illanias" => (row.illanias, row.rillanias),
        "nutari" => (row.nutari, row.rnutari),
        "dynallca" => (row.dynallca, row.rdynallca),
        "ilani_seeds" => (row.ilani_seeds, row.rilani_seeds),
        "illanias_seeds" => (row.illanias_seeds, row.rillanias_seeds),
        "nutari_seeds" => (row.nutari_seeds, row.rnutari_seeds),
        "dynallca_seeds" => (row.dynallca_seeds, row.rdynallca_seeds),
        _ => (0, 0),
    }
}

// =========================================================================
// Mineral key validation
// =========================================================================

const MINERAL_KEYS: &[(&str, &str)] = &[
    ("copperore", "Ruda miedzi"),
    ("zincore", "Ruda cynku"),
    ("tinore", "Ruda cyny"),
    ("ironore", "Ruda żelaza"),
    ("copper", "Miedź"),
    ("bronze", "Brąz"),
    ("brass", "Mosiądz"),
    ("iron", "Żelazo"),
    ("steel", "Stal"),
    ("coal", "Węgiel"),
    ("adamantium", "Adamantium"),
    ("meteor", "Meteor"),
    ("crystal", "Kryształ"),
    ("pine", "Sosna"),
    ("hazel", "Leszczyna"),
    ("yew", "Cis"),
    ("elm", "Wiąz"),
];

fn is_valid_mineral_key(key: &str) -> bool {
    MINERAL_KEYS.iter().any(|(k, _)| *k == key)
}

fn mineral_amount_from_row(row: &tq::TribeMineralsRow, key: &str) -> (i32, i32) {
    match key {
        "copperore" => (row.copperore, row.rcopperore),
        "zincore" => (row.zincore, row.rzincore),
        "tinore" => (row.tinore, row.rtinore),
        "ironore" => (row.ironore, row.rironore),
        "copper" => (row.copper, row.rcopper),
        "bronze" => (row.bronze, row.rbronze),
        "brass" => (row.brass, row.rbrass),
        "iron" => (row.iron, row.riron),
        "steel" => (row.steel, row.rsteel),
        "coal" => (row.coal, row.rcoal),
        "adamantium" => (row.adamantium, row.radamantium),
        "meteor" => (row.meteor, row.rmeteor),
        "crystal" => (row.crystal, row.rcrystal),
        "pine" => (row.pine, row.rpine),
        "hazel" => (row.hazel, row.rhazel),
        "yew" => (row.yew, row.ryew),
        "elm" => (row.elm, row.relm),
        _ => (0, 0),
    }
}

// =========================================================================
// ARMORY handlers
// =========================================================================

/// GET /tribe/armory
#[allow(clippy::too_many_lines)]
pub async fn armory_show(
    State(app): State<AppState>,
    Extension(ctx): Extension<RequestContext>,
    axum::extract::Query(params): axum::extract::Query<ArmoryFilterParams>,
) -> Response {
    let (player_id, player) = match require_tribe_member(&app, &ctx).await {
        Ok(v) => v,
        Err(r) => return r,
    };

    let (tribe, perms) = match load_tribe_and_storage_access(
        &app,
        &ctx,
        player_id,
        player.tribe,
        StorageArea::Armory,
    )
    .await
    {
        Ok(v) => v,
        Err(r) => return r,
    };

    let type_filter = params.type_filter.as_deref().filter(|s| !s.is_empty());
    let page = params.page.max(1);
    let per_page: i64 = 30;

    let total = tq::armory_item_count(
        &app.pool,
        tribe.id,
        type_filter,
        params.min_level,
        params.max_level,
    )
    .await
    .unwrap_or_else(|e| {
        tracing::error!(error = %e, tribe_id = tribe.id, "Failed to count armory items");
        0
    });
    let total_pages = ((total + per_page - 1) / per_page).max(1);
    let page = page.min(total_pages);

    let rows = tq::armory_items(
        &app.pool,
        tribe.id,
        type_filter,
        params.min_level,
        params.max_level,
        page,
        per_page,
    )
    .await
    .unwrap_or_else(|e| {
        tracing::error!(error = %e, tribe_id = tribe.id, "Failed to list armory items");
        Vec::new()
    });

    let items: Vec<ArmoryEntry> = rows
        .into_iter()
        .map(|r| {
            let avail = available_quantity(i64::from(r.amount), i64::from(r.reserved));
            ArmoryEntry {
                id: r.id,
                name: r.name,
                power: r.power,
                wt: r.wt,
                maxwt: r.maxwt,
                zr: r.zr,
                szyb: r.szyb,
                minlev: r.minlev,
                item_type: r.r#type,
                magic: r.magic,
                amount: r.amount,
                reserved: r.reserved,
                #[allow(clippy::cast_possible_truncation)]
                available: avail as i32,
                twohand: r.twohand,
            }
        })
        .collect();

    let give_ok = can_give(
        player_id,
        tribe.owner,
        perms,
        StorageArea::Armory.give_permission(),
    );
    let members = if let Ok(v) = load_members(&app, tribe.id).await {
        v
    } else {
        tracing::error!(
            tribe_id = tribe.id,
            "Failed to load tribe members for armory"
        );
        Vec::new()
    };

    let meta = PageMeta::titled("Zbrojownia klanu").with_back_link("/tribe", "Wróć do klanu");
    let base = app.templates.build_context(&ctx, &meta);

    let view = ArmoryView {
        base,
        items,
        page,
        total_pages,
        can_give: give_ok,
        members,
        type_filter: params.type_filter.unwrap_or_default(),
        min_level: params.min_level.map(|v| v.to_string()).unwrap_or_default(),
        max_level: params.max_level.map(|v| v.to_string()).unwrap_or_default(),
    };
    app.templates.render_value("tribe_armory.html", &view)
}

/// POST /tribe/armory/deposit
pub async fn armory_deposit(
    State(app): State<AppState>,
    Extension(ctx): Extension<RequestContext>,
    Form(form): Form<DepositItemForm>,
) -> Response {
    let (player_id, player) = match require_tribe_member(&app, &ctx).await {
        Ok(v) => v,
        Err(r) => return r,
    };

    if load_tribe_and_storage_access(&app, &ctx, player_id, player.tribe, StorageArea::Armory)
        .await
        .is_err()
    {
        return error_page(&app, &ctx, "Nie masz dostępu do zbrojowni.");
    }

    if form.amount <= 0 {
        return error_page(&app, &ctx, "Ilość musi być większa od zera.");
    }

    if let Err(e) = tq::armory_deposit(&app.pool, player.tribe, form.item_id, form.amount).await {
        tracing::warn!("armory_deposit failed: {e}");
        return error_page(&app, &ctx, "Nie udało się złożyć przedmiotu.");
    }

    crate::page::redirect_after_post("/tribe/armory")
}

/// POST /tribe/armory/give
pub async fn armory_give(
    State(app): State<AppState>,
    Extension(ctx): Extension<RequestContext>,
    Form(form): Form<GiveItemForm>,
) -> Response {
    let (player_id, player) = match require_tribe_member(&app, &ctx).await {
        Ok(v) => v,
        Err(r) => return r,
    };

    let (tribe, perms) = match load_tribe_and_storage_access(
        &app,
        &ctx,
        player_id,
        player.tribe,
        StorageArea::Armory,
    )
    .await
    {
        Ok(v) => v,
        Err(r) => return r,
    };

    if !can_give(
        player_id,
        tribe.owner,
        perms,
        StorageArea::Armory.give_permission(),
    ) {
        return error_page(&app, &ctx, "Nie masz uprawnień do wydawania przedmiotów.");
    }

    let Ok(Some(item)) = tq::armory_item_by_id(&app.pool, form.item_id, tribe.id).await else {
        return error_page(&app, &ctx, "Przedmiot nie istnieje.");
    };

    // Check recipient tribe membership
    let recipient_tribe: Option<i32> = match sqlx::query_scalar(
        "SELECT tribe_id FROM players WHERE id = $1",
    )
    .bind(form.recipient_id)
    .fetch_optional(&app.pool)
    .await
    {
        Ok(v) => v,
        Err(e) => {
            tracing::error!(error = %e, recipient_id = form.recipient_id, "Failed to fetch recipient tribe for armory give");
            None
        }
    };

    let check = GiveCheck {
        total: i64::from(item.amount),
        reserved: i64::from(item.reserved),
        amount: i64::from(form.amount),
        is_reservation_fulfilment: false,
        recipient_tribe,
        tribe_id: tribe.id,
    };

    if validate_give(&check).is_err() {
        return error_page(
            &app,
            &ctx,
            "Nie można wydać przedmiotów — sprawdź ilość i odbiorcę.",
        );
    }

    if let Err(e) = tq::armory_give(&app.pool, form.item_id, form.recipient_id, form.amount).await {
        tracing::warn!("armory_give failed: {e}");
        return error_page(&app, &ctx, "Nie udało się wydać przedmiotu.");
    }

    crate::page::redirect_after_post("/tribe/armory")
}

/// POST /tribe/armory/reserve
pub async fn armory_reserve(
    State(app): State<AppState>,
    Extension(ctx): Extension<RequestContext>,
    Form(form): Form<ReserveItemForm>,
) -> Response {
    let (player_id, player) = match require_tribe_member(&app, &ctx).await {
        Ok(v) => v,
        Err(r) => return r,
    };

    let (tribe, _perms) = match load_tribe_and_storage_access(
        &app,
        &ctx,
        player_id,
        player.tribe,
        StorageArea::Armory,
    )
    .await
    {
        Ok(v) => v,
        Err(r) => return r,
    };

    let Ok(Some(item)) = tq::armory_item_by_id(&app.pool, form.item_id, tribe.id).await else {
        return error_page(&app, &ctx, "Przedmiot nie istnieje.");
    };

    if validate_reserve(
        StorageArea::Armory,
        i64::from(item.amount),
        i64::from(item.reserved),
        i64::from(form.amount),
    )
    .is_err()
    {
        return error_page(
            &app,
            &ctx,
            "Nie można zarezerwować — sprawdź dostępną ilość.",
        );
    }

    if let Err(e) =
        tq::armory_reserve(&app.pool, form.item_id, player_id, tribe.id, form.amount).await
    {
        tracing::warn!("armory_reserve failed: {e}");
        return error_page(&app, &ctx, "Nie udało się zarezerwować.");
    }

    crate::page::redirect_after_post("/tribe/armory")
}

// =========================================================================
// WAREHOUSE handlers
// =========================================================================

/// GET /tribe/warehouse
pub async fn warehouse_show(
    State(app): State<AppState>,
    Extension(ctx): Extension<RequestContext>,
) -> Response {
    let (player_id, player) = match require_tribe_member(&app, &ctx).await {
        Ok(v) => v,
        Err(r) => return r,
    };

    let (tribe, perms) = match load_tribe_and_storage_access(
        &app,
        &ctx,
        player_id,
        player.tribe,
        StorageArea::Warehouse,
    )
    .await
    {
        Ok(v) => v,
        Err(r) => return r,
    };

    let rows = match tq::warehouse_potions(&app.pool, tribe.id).await {
        Ok(v) => v,
        Err(e) => {
            tracing::error!(error = %e, tribe_id = tribe.id, "Failed to list warehouse potions");
            Vec::new()
        }
    };

    let potions: Vec<PotionEntry> = rows
        .into_iter()
        .map(|r| {
            let avail = available_quantity(i64::from(r.amount), i64::from(r.reserved));
            PotionEntry {
                id: r.id,
                name: r.name,
                efect: r.efect,
                power: r.power,
                amount: r.amount,
                reserved: r.reserved,
                #[allow(clippy::cast_possible_truncation)]
                available: avail as i32,
                potion_type: r.r#type,
            }
        })
        .collect();

    let give_ok = can_give(
        player_id,
        tribe.owner,
        perms,
        StorageArea::Warehouse.give_permission(),
    );
    let members = if let Ok(v) = load_members(&app, tribe.id).await {
        v
    } else {
        tracing::error!(
            tribe_id = tribe.id,
            "Failed to load tribe members for warehouse"
        );
        Vec::new()
    };

    let meta = PageMeta::titled("Magazyn mikstur").with_back_link("/tribe", "Wróć do klanu");
    let base = app.templates.build_context(&ctx, &meta);

    let view = WarehouseView {
        base,
        potions,
        can_give: give_ok,
        members,
    };
    app.templates.render_value("tribe_warehouse.html", &view)
}

/// POST /tribe/warehouse/deposit
pub async fn warehouse_deposit(
    State(app): State<AppState>,
    Extension(ctx): Extension<RequestContext>,
    Form(form): Form<DepositItemForm>,
) -> Response {
    let (player_id, player) = match require_tribe_member(&app, &ctx).await {
        Ok(v) => v,
        Err(r) => return r,
    };

    if load_tribe_and_storage_access(&app, &ctx, player_id, player.tribe, StorageArea::Warehouse)
        .await
        .is_err()
    {
        return error_page(&app, &ctx, "Nie masz dostępu do magazynu mikstur.");
    }

    if form.amount <= 0 {
        return error_page(&app, &ctx, "Ilość musi być większa od zera.");
    }

    if let Err(e) = tq::warehouse_deposit(&app.pool, player.tribe, form.item_id, form.amount).await
    {
        tracing::warn!("warehouse_deposit failed: {e}");
        return error_page(&app, &ctx, "Nie udało się złożyć mikstury.");
    }

    crate::page::redirect_after_post("/tribe/warehouse")
}

/// POST /tribe/warehouse/give
pub async fn warehouse_give(
    State(app): State<AppState>,
    Extension(ctx): Extension<RequestContext>,
    Form(form): Form<GiveItemForm>,
) -> Response {
    let (player_id, player) = match require_tribe_member(&app, &ctx).await {
        Ok(v) => v,
        Err(r) => return r,
    };

    let (tribe, perms) = match load_tribe_and_storage_access(
        &app,
        &ctx,
        player_id,
        player.tribe,
        StorageArea::Warehouse,
    )
    .await
    {
        Ok(v) => v,
        Err(r) => return r,
    };

    if !can_give(
        player_id,
        tribe.owner,
        perms,
        StorageArea::Warehouse.give_permission(),
    ) {
        return error_page(&app, &ctx, "Nie masz uprawnień do wydawania mikstur.");
    }

    let Ok(Some(potion)) = tq::warehouse_potion_by_id(&app.pool, form.item_id, tribe.id).await
    else {
        return error_page(&app, &ctx, "Mikstura nie istnieje.");
    };

    let recipient_tribe: Option<i32> = match sqlx::query_scalar(
        "SELECT tribe_id FROM players WHERE id = $1",
    )
    .bind(form.recipient_id)
    .fetch_optional(&app.pool)
    .await
    {
        Ok(v) => v,
        Err(e) => {
            tracing::error!(error = %e, recipient_id = form.recipient_id, "Failed to fetch recipient tribe for potion give");
            None
        }
    };

    let check = GiveCheck {
        total: i64::from(potion.amount),
        reserved: i64::from(potion.reserved),
        amount: i64::from(form.amount),
        is_reservation_fulfilment: false,
        recipient_tribe,
        tribe_id: tribe.id,
    };

    if validate_give(&check).is_err() {
        return error_page(
            &app,
            &ctx,
            "Nie można wydać mikstur — sprawdź ilość i odbiorcę.",
        );
    }

    if let Err(e) =
        tq::warehouse_give(&app.pool, form.item_id, form.recipient_id, form.amount).await
    {
        tracing::warn!("warehouse_give failed: {e}");
        return error_page(&app, &ctx, "Nie udało się wydać mikstury.");
    }

    crate::page::redirect_after_post("/tribe/warehouse")
}

/// POST /tribe/warehouse/reserve
pub async fn warehouse_reserve(
    State(app): State<AppState>,
    Extension(ctx): Extension<RequestContext>,
    Form(form): Form<ReserveItemForm>,
) -> Response {
    let (player_id, player) = match require_tribe_member(&app, &ctx).await {
        Ok(v) => v,
        Err(r) => return r,
    };

    let (tribe, _perms) = match load_tribe_and_storage_access(
        &app,
        &ctx,
        player_id,
        player.tribe,
        StorageArea::Warehouse,
    )
    .await
    {
        Ok(v) => v,
        Err(r) => return r,
    };

    let Ok(Some(potion)) = tq::warehouse_potion_by_id(&app.pool, form.item_id, tribe.id).await
    else {
        return error_page(&app, &ctx, "Mikstura nie istnieje.");
    };

    if validate_reserve(
        StorageArea::Warehouse,
        i64::from(potion.amount),
        i64::from(potion.reserved),
        i64::from(form.amount),
    )
    .is_err()
    {
        return error_page(
            &app,
            &ctx,
            "Nie można zarezerwować — sprawdź dostępną ilość.",
        );
    }

    if let Err(e) =
        tq::warehouse_reserve(&app.pool, form.item_id, player_id, tribe.id, form.amount).await
    {
        tracing::warn!("warehouse_reserve failed: {e}");
        return error_page(&app, &ctx, "Nie udało się zarezerwować.");
    }

    crate::page::redirect_after_post("/tribe/warehouse")
}

// =========================================================================
// HERBS handlers
// =========================================================================

/// GET /tribe/herbs
pub async fn herbs_show(
    State(app): State<AppState>,
    Extension(ctx): Extension<RequestContext>,
) -> Response {
    let (player_id, player) = match require_tribe_member(&app, &ctx).await {
        Ok(v) => v,
        Err(r) => return r,
    };

    let (tribe, perms) = match load_tribe_and_storage_access(
        &app,
        &ctx,
        player_id,
        player.tribe,
        StorageArea::Herbs,
    )
    .await
    {
        Ok(v) => v,
        Err(r) => return r,
    };

    let row = tq::tribe_herbs(&app.pool, tribe.id).await.ok().flatten();

    let herbs: Vec<HerbEntry> = HERB_KEYS
        .iter()
        .map(|(key, label)| {
            let (amount, reserved) = row
                .as_ref()
                .map_or((0, 0), |r| herb_amount_from_row(r, key));
            let avail = available_quantity(i64::from(amount), i64::from(reserved));
            HerbEntry {
                key: (*key).to_owned(),
                label: (*label).to_owned(),
                amount,
                reserved,
                #[allow(clippy::cast_possible_truncation)]
                available: avail as i32,
            }
        })
        .collect();

    let give_ok = can_give(
        player_id,
        tribe.owner,
        perms,
        StorageArea::Herbs.give_permission(),
    );
    let members = if let Ok(v) = load_members(&app, tribe.id).await {
        v
    } else {
        tracing::error!(
            tribe_id = tribe.id,
            "Failed to load tribe members for herbs"
        );
        Vec::new()
    };

    let meta = PageMeta::titled("Zielnik klanu").with_back_link("/tribe", "Wróć do klanu");
    let base = app.templates.build_context(&ctx, &meta);

    let view = HerbsView {
        base,
        herbs,
        can_give: give_ok,
        members,
    };
    app.templates.render_value("tribe_herbs.html", &view)
}

/// POST /tribe/herbs/deposit
pub async fn herbs_deposit(
    State(app): State<AppState>,
    Extension(ctx): Extension<RequestContext>,
    Form(form): Form<HerbActionForm>,
) -> Response {
    let (player_id, player) = match require_tribe_member(&app, &ctx).await {
        Ok(v) => v,
        Err(r) => return r,
    };

    if load_tribe_and_storage_access(&app, &ctx, player_id, player.tribe, StorageArea::Herbs)
        .await
        .is_err()
    {
        return error_page(&app, &ctx, "Nie masz dostępu do zielnika.");
    }

    if !is_valid_herb_key(&form.herb_key) {
        return error_page(&app, &ctx, "Nieprawidłowy typ ziół.");
    }

    if form.amount <= 0 {
        return error_page(&app, &ctx, "Ilość musi być większa od zera.");
    }

    if let Err(e) = tq::herb_deposit(
        &app.pool,
        player.tribe,
        player_id,
        &form.herb_key,
        form.amount,
    )
    .await
    {
        tracing::warn!("herb_deposit failed: {e}");
        return error_page(&app, &ctx, "Nie udało się złożyć ziół.");
    }

    crate::page::redirect_after_post("/tribe/herbs")
}

/// POST /tribe/herbs/give
pub async fn herbs_give(
    State(app): State<AppState>,
    Extension(ctx): Extension<RequestContext>,
    Form(form): Form<HerbActionForm>,
) -> Response {
    let (player_id, player) = match require_tribe_member(&app, &ctx).await {
        Ok(v) => v,
        Err(r) => return r,
    };

    let (tribe, perms) = match load_tribe_and_storage_access(
        &app,
        &ctx,
        player_id,
        player.tribe,
        StorageArea::Herbs,
    )
    .await
    {
        Ok(v) => v,
        Err(r) => return r,
    };

    if !can_give(
        player_id,
        tribe.owner,
        perms,
        StorageArea::Herbs.give_permission(),
    ) {
        return error_page(&app, &ctx, "Nie masz uprawnień do wydawania ziół.");
    }

    if !is_valid_herb_key(&form.herb_key) {
        return error_page(&app, &ctx, "Nieprawidłowy typ ziół.");
    }

    let Some(recipient_id) = form.recipient_id else {
        return error_page(&app, &ctx, "Nie wybrano odbiorcy.");
    };

    if form.amount <= 0 {
        return error_page(&app, &ctx, "Ilość musi być większa od zera.");
    }

    if let Err(e) = tq::herb_give(
        &app.pool,
        tribe.id,
        recipient_id,
        &form.herb_key,
        form.amount,
    )
    .await
    {
        tracing::warn!("herb_give failed: {e}");
        return error_page(&app, &ctx, "Nie udało się wydać ziół.");
    }

    crate::page::redirect_after_post("/tribe/herbs")
}

/// POST /tribe/herbs/reserve
pub async fn herbs_reserve(
    State(app): State<AppState>,
    Extension(ctx): Extension<RequestContext>,
    Form(form): Form<HerbActionForm>,
) -> Response {
    let (player_id, player) = match require_tribe_member(&app, &ctx).await {
        Ok(v) => v,
        Err(r) => return r,
    };

    let (tribe, _perms) = match load_tribe_and_storage_access(
        &app,
        &ctx,
        player_id,
        player.tribe,
        StorageArea::Herbs,
    )
    .await
    {
        Ok(v) => v,
        Err(r) => return r,
    };

    if !is_valid_herb_key(&form.herb_key) {
        return error_page(&app, &ctx, "Nieprawidłowy typ ziół.");
    }

    let row = tq::tribe_herbs(&app.pool, tribe.id).await.ok().flatten();
    let (amount, reserved) = row
        .as_ref()
        .map_or((0, 0), |r| herb_amount_from_row(r, &form.herb_key));

    if validate_reserve(
        StorageArea::Herbs,
        i64::from(amount),
        i64::from(reserved),
        i64::from(form.amount),
    )
    .is_err()
    {
        return error_page(
            &app,
            &ctx,
            "Nie można zarezerwować — sprawdź dostępną ilość.",
        );
    }

    if let Err(e) =
        tq::herb_reserve(&app.pool, tribe.id, player_id, &form.herb_key, form.amount).await
    {
        tracing::warn!("herb_reserve failed: {e}");
        return error_page(&app, &ctx, "Nie udało się zarezerwować.");
    }

    crate::page::redirect_after_post("/tribe/herbs")
}

// =========================================================================
// MINERALS handlers
// =========================================================================

/// GET /tribe/minerals
pub async fn minerals_show(
    State(app): State<AppState>,
    Extension(ctx): Extension<RequestContext>,
) -> Response {
    let (player_id, player) = match require_tribe_member(&app, &ctx).await {
        Ok(v) => v,
        Err(r) => return r,
    };

    let (tribe, perms) = match load_tribe_and_storage_access(
        &app,
        &ctx,
        player_id,
        player.tribe,
        StorageArea::Treasury,
    )
    .await
    {
        Ok(v) => v,
        Err(r) => return r,
    };

    let row = tq::tribe_minerals(&app.pool, tribe.id).await.ok().flatten();

    let minerals: Vec<MineralEntry> = MINERAL_KEYS
        .iter()
        .map(|(key, label)| {
            let (amount, reserved) = row
                .as_ref()
                .map_or((0, 0), |r| mineral_amount_from_row(r, key));
            let avail = available_quantity(i64::from(amount), i64::from(reserved));
            MineralEntry {
                key: (*key).to_owned(),
                label: (*label).to_owned(),
                amount,
                reserved,
                #[allow(clippy::cast_possible_truncation)]
                available: avail as i32,
            }
        })
        .collect();

    let give_ok = can_give(
        player_id,
        tribe.owner,
        perms,
        StorageArea::Treasury.give_permission(),
    );
    let members = if let Ok(v) = load_members(&app, tribe.id).await {
        v
    } else {
        tracing::error!(
            tribe_id = tribe.id,
            "Failed to load tribe members for treasury"
        );
        Vec::new()
    };

    let meta = PageMeta::titled("Skarbiec klanu").with_back_link("/tribe", "Wróć do klanu");
    let base = app.templates.build_context(&ctx, &meta);

    let view = MineralsView {
        base,
        minerals,
        gold: tribe.credits,
        mithril: tribe.platinum,
        can_give: give_ok,
        members,
    };
    app.templates.render_value("tribe_minerals.html", &view)
}

/// POST /tribe/minerals/deposit
pub async fn minerals_deposit(
    State(app): State<AppState>,
    Extension(ctx): Extension<RequestContext>,
    Form(form): Form<MineralActionForm>,
) -> Response {
    let (player_id, player) = match require_tribe_member(&app, &ctx).await {
        Ok(v) => v,
        Err(r) => return r,
    };

    if load_tribe_and_storage_access(&app, &ctx, player_id, player.tribe, StorageArea::Treasury)
        .await
        .is_err()
    {
        return error_page(&app, &ctx, "Nie masz dostępu do skarbca.");
    }

    if !is_valid_mineral_key(&form.mineral_key) {
        return error_page(&app, &ctx, "Nieprawidłowy typ surowca.");
    }

    if form.amount <= 0 {
        return error_page(&app, &ctx, "Ilość musi być większa od zera.");
    }

    if let Err(e) = tq::mineral_deposit(
        &app.pool,
        player.tribe,
        player_id,
        &form.mineral_key,
        form.amount,
    )
    .await
    {
        tracing::warn!("mineral_deposit failed: {e}");
        return error_page(&app, &ctx, "Nie udało się złożyć surowców.");
    }

    crate::page::redirect_after_post("/tribe/minerals")
}

/// POST /tribe/minerals/give
pub async fn minerals_give(
    State(app): State<AppState>,
    Extension(ctx): Extension<RequestContext>,
    Form(form): Form<MineralActionForm>,
) -> Response {
    let (player_id, player) = match require_tribe_member(&app, &ctx).await {
        Ok(v) => v,
        Err(r) => return r,
    };

    let (tribe, perms) = match load_tribe_and_storage_access(
        &app,
        &ctx,
        player_id,
        player.tribe,
        StorageArea::Treasury,
    )
    .await
    {
        Ok(v) => v,
        Err(r) => return r,
    };

    if !can_give(
        player_id,
        tribe.owner,
        perms,
        StorageArea::Treasury.give_permission(),
    ) {
        return error_page(&app, &ctx, "Nie masz uprawnień do wydawania surowców.");
    }

    if !is_valid_mineral_key(&form.mineral_key) {
        return error_page(&app, &ctx, "Nieprawidłowy typ surowca.");
    }

    let Some(recipient_id) = form.recipient_id else {
        return error_page(&app, &ctx, "Nie wybrano odbiorcy.");
    };

    if form.amount <= 0 {
        return error_page(&app, &ctx, "Ilość musi być większa od zera.");
    }

    if let Err(e) = tq::mineral_give(
        &app.pool,
        tribe.id,
        recipient_id,
        &form.mineral_key,
        form.amount,
    )
    .await
    {
        tracing::warn!("mineral_give failed: {e}");
        return error_page(&app, &ctx, "Nie udało się wydać surowców.");
    }

    crate::page::redirect_after_post("/tribe/minerals")
}

/// POST /tribe/minerals/reserve
pub async fn minerals_reserve(
    State(app): State<AppState>,
    Extension(ctx): Extension<RequestContext>,
    Form(form): Form<MineralActionForm>,
) -> Response {
    let (player_id, player) = match require_tribe_member(&app, &ctx).await {
        Ok(v) => v,
        Err(r) => return r,
    };

    let (tribe, _perms) = match load_tribe_and_storage_access(
        &app,
        &ctx,
        player_id,
        player.tribe,
        StorageArea::Treasury,
    )
    .await
    {
        Ok(v) => v,
        Err(r) => return r,
    };

    if !is_valid_mineral_key(&form.mineral_key) {
        return error_page(&app, &ctx, "Nieprawidłowy typ surowca.");
    }

    let row = tq::tribe_minerals(&app.pool, tribe.id).await.ok().flatten();
    let (amount, reserved) = row
        .as_ref()
        .map_or((0, 0), |r| mineral_amount_from_row(r, &form.mineral_key));

    if validate_reserve(
        StorageArea::Treasury,
        i64::from(amount),
        i64::from(reserved),
        i64::from(form.amount),
    )
    .is_err()
    {
        return error_page(
            &app,
            &ctx,
            "Nie można zarezerwować — sprawdź dostępną ilość.",
        );
    }

    if let Err(e) = tq::mineral_reserve(
        &app.pool,
        tribe.id,
        player_id,
        &form.mineral_key,
        form.amount,
    )
    .await
    {
        tracing::warn!("mineral_reserve failed: {e}");
        return error_page(&app, &ctx, "Nie udało się zarezerwować.");
    }

    crate::page::redirect_after_post("/tribe/minerals")
}
