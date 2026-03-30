//! Outpost (garrison/watchtower) handlers.
//!
//! Ported from `outposts.php` and `outpost.php`. Outposts are player-owned
//! military structures with troops, veterans, beasts, and combat.

use axum::extract::{Path, Query, State};
use axum::response::Response;
use axum::{Extension, Form};
use rand::Rng;

use crate::middleware::context::RequestContext;
use crate::page::{Flash, FlashKind, PageMeta};
use crate::state::AppState;
use vallheru_data::queries::{outpost as oq, player as pq};
use vallheru_domain::group::outpost as domain;

// =========================================================================
// View models
// =========================================================================

#[derive(serde::Serialize)]
pub struct OutpostMenuView {
    #[serde(flatten)]
    pub base: crate::render::RenderContext,
    pub has_outpost: bool,
    pub player_gold: i32,
}

#[derive(serde::Serialize)]
pub struct MyOutpostView {
    #[serde(flatten)]
    pub base: crate::render::RenderContext,
    pub out: OutpostInfo,
    pub monsters: Vec<MonsterInfo>,
    pub veterans: Vec<VeteranSummary>,
    pub cost: i32,
    pub can_add_bonus: bool,
    pub morale_label: String,
    pub fatigue_display: i32,
    pub max_troops: i32,
    pub max_equip: i32,
}

#[derive(serde::Serialize)]
pub struct OutpostInfo {
    pub id: i32,
    pub size: i32,
    pub warriors: i32,
    pub archers: i32,
    pub catapults: i32,
    pub barricades: i32,
    pub gold: i32,
    pub turns: i32,
    pub battack: i16,
    pub bdefense: i16,
    pub btax: i16,
    pub blost: i16,
    pub bcost: i16,
    pub fence: i32,
    pub barracks: i32,
    pub morale: f64,
}

#[derive(serde::Serialize)]
pub struct MonsterInfo {
    pub id: i32,
    pub name: String,
    pub power: i32,
    pub defense: i32,
}

#[derive(serde::Serialize)]
pub struct VeteranSummary {
    pub id: i32,
    pub name: String,
    pub attack: i32,
    pub defense: i32,
}

#[derive(serde::Serialize)]
pub struct TreasuryView {
    #[serde(flatten)]
    pub base: crate::render::RenderContext,
    pub outpost_gold: i32,
    pub player_gold: i32,
    pub message: String,
}

#[derive(serde::Deserialize)]
pub struct TreasuryForm {
    pub amount: Option<i32>,
}

#[derive(serde::Serialize)]
pub struct ShopView {
    #[serde(flatten)]
    pub base: crate::render::RenderContext,
    pub out: OutpostInfo,
    pub reserves: [i32; 4],
    pub max_troops: i32,
    pub max_equip: i32,
    pub max_level: i32,
    pub max_lair: i32,
    pub max_barrack: i32,
    pub minerals: MineralInfo,
    pub platinum: i64,
    pub message: String,
}

#[derive(serde::Serialize, Default)]
pub struct MineralInfo {
    pub pine: i32,
    pub crystal: i32,
    pub adamantium: i32,
    pub meteor: i32,
}

#[derive(serde::Deserialize)]
pub struct BuyArmyForm {
    pub army0: Option<i32>,
    pub army1: Option<i32>,
    pub army2: Option<i32>,
    pub army3: Option<i32>,
}

#[derive(serde::Deserialize)]
pub struct UpgradeForm {
    pub amount: Option<i32>,
}

#[derive(serde::Serialize)]
pub struct TaxView {
    #[serde(flatten)]
    pub base: crate::render::RenderContext,
    pub turns: i32,
    pub message: String,
}

#[derive(serde::Deserialize)]
pub struct TaxForm {
    pub amount: Option<i32>,
}

#[derive(serde::Serialize)]
pub struct OutpostListView {
    #[serde(flatten)]
    pub base: crate::render::RenderContext,
    pub outposts: Vec<OutpostListItem>,
    pub min_size: i32,
    pub max_size: i32,
    pub own_size: i32,
}

#[derive(serde::Serialize)]
pub struct OutpostListItem {
    pub id: i32,
    pub size: i32,
    pub owner_id: i32,
    pub owner_name: String,
}

#[derive(serde::Deserialize)]
pub struct ListSearchForm {
    pub slevel: Option<i32>,
    pub elevel: Option<i32>,
}

#[derive(serde::Serialize)]
pub struct BattleView {
    #[serde(flatten)]
    pub base: crate::render::RenderContext,
    pub messages: Vec<String>,
}

#[derive(serde::Deserialize)]
pub struct BattleForm {
    pub oid: Option<i32>,
    pub pid: Option<i32>,
    pub amount: Option<i32>,
}

#[derive(serde::Serialize)]
pub struct VeteranDetailView {
    #[serde(flatten)]
    pub base: crate::render::RenderContext,
    pub veteran: VeteranDetail,
    pub equip_weapons: Vec<EquipOption>,
    pub equip_armor: Vec<EquipOption>,
    pub equip_helm: Vec<EquipOption>,
    pub equip_legs: Vec<EquipOption>,
    pub equip_ring: Vec<EquipOption>,
    pub equip_arrows: Vec<EquipOption>,
    pub message: String,
}

#[derive(serde::Serialize)]
pub struct VeteranDetail {
    pub id: i32,
    pub name: String,
    pub weapon: String,
    pub wpower: i32,
    pub armor: String,
    pub apower: i32,
    pub helm: String,
    pub hpower: i32,
    pub legs: String,
    pub lpower: i32,
    pub ring1: String,
    pub rpower1: i32,
    pub ring2: String,
    pub rpower2: i32,
    pub arrows: String,
    pub opower: i32,
    pub attack: i32,
    pub defense: i32,
}

#[derive(serde::Serialize)]
pub struct EquipOption {
    pub id: i32,
    pub name: String,
    pub power: i32,
}

#[derive(serde::Deserialize)]
pub struct EquipVeteranForm {
    pub weapon: Option<i32>,
    pub armor: Option<i32>,
    pub helm: Option<i32>,
    pub legs: Option<i32>,
    pub ring1: Option<i32>,
    pub ring2: Option<i32>,
    pub arrows: Option<i32>,
}

#[derive(serde::Serialize)]
pub struct GuideView {
    #[serde(flatten)]
    pub base: crate::render::RenderContext,
}

// =========================================================================
// Garrison mission view models (outpost.php)
// =========================================================================

#[derive(serde::Serialize)]
pub struct GarrisonMenuView {
    #[serde(flatten)]
    pub base: crate::render::RenderContext,
    pub has_missions: bool,
    pub craft_mission: i32,
    pub missions: Vec<GarrisonMission>,
}

#[derive(serde::Serialize)]
pub struct GarrisonMission {
    pub index: usize,
    pub description: String,
}

#[derive(serde::Serialize)]
pub struct GarrisonResultView {
    #[serde(flatten)]
    pub base: crate::render::RenderContext,
    pub result: String,
    pub can_refresh: bool,
}

// =========================================================================
// Handlers — Outpost management
// =========================================================================

/// GET /outposts — main menu.
pub async fn outpost_menu(
    State(app): State<AppState>,
    Extension(ctx): Extension<RequestContext>,
) -> Response {
    let Some(ref user) = ctx.session_user else {
        return crate::page::redirect("/login");
    };

    #[allow(clippy::cast_possible_truncation)]
    let player_id = user.id as i32;

    let player = match load_player(&app, player_id).await {
        Ok(p) => p,
        Err(r) => return r,
    };

    if player.location != "Altara" && player.location != "Ardulith" {
        return error_page(&app, &ctx, "Nie znajdujesz się w mieście.");
    }

    let has_outpost = oq::find_by_owner(&app.pool, player_id)
        .await
        .ok()
        .flatten()
        .is_some();

    let meta = PageMeta::titled("Strażnica");
    let base = app.templates.build_context(&ctx, &meta);
    let view = OutpostMenuView {
        base,
        has_outpost,
        player_gold: player.credits,
    };
    app.templates.render_value("outpost_menu.html", &view)
}

/// POST /outposts/buy — purchase an outpost.
pub async fn buy_outpost(
    State(app): State<AppState>,
    Extension(ctx): Extension<RequestContext>,
) -> Response {
    let Some(ref user) = ctx.session_user else {
        return crate::page::redirect("/login");
    };

    #[allow(clippy::cast_possible_truncation)]
    let player_id = user.id as i32;

    let player = match load_player(&app, player_id).await {
        Ok(p) => p,
        Err(r) => return r,
    };

    if player.location != "Altara" && player.location != "Ardulith" {
        return error_page(&app, &ctx, "Nie znajdujesz się w mieście.");
    }

    if oq::find_by_owner(&app.pool, player_id)
        .await
        .ok()
        .flatten()
        .is_some()
    {
        return error_page(&app, &ctx, "Już posiadasz strażnicę!");
    }

    if player.credits < 500 {
        return error_page(
            &app,
            &ctx,
            "Nie masz wystarczająco dużo pieniędzy aby zakupić ziemię pod strażnicę.",
        );
    }

    let _ = sqlx::query("UPDATE players SET credits = credits - 500 WHERE id = $1")
        .bind(player_id)
        .execute(&app.pool)
        .await;
    let _ = oq::create_outpost(&app.pool, player_id).await;

    crate::page::redirect("/outposts")
}

/// GET /outposts/my — view my outpost details.
pub async fn my_outpost(
    State(app): State<AppState>,
    Extension(ctx): Extension<RequestContext>,
) -> Response {
    let Some(ref user) = ctx.session_user else {
        return crate::page::redirect("/login");
    };

    #[allow(clippy::cast_possible_truncation)]
    let player_id = user.id as i32;

    let out = match require_outpost(&app, &ctx, player_id).await {
        Ok(o) => o,
        Err(r) => return r,
    };

    let (monsters, veterans, player, leadership) =
        load_outpost_context(&app, &out, player_id).await;

    #[allow(clippy::cast_possible_truncation, clippy::cast_possible_wrap)]
    let monster_count = monsters.len() as i32;
    #[allow(clippy::cast_possible_truncation, clippy::cast_possible_wrap)]
    let veteran_count = veterans.len() as i32;
    let cost = domain::maintenance_cost(
        out.warriors,
        out.archers,
        out.catapults,
        monster_count,
        veteran_count,
        out.bcost,
    );

    let total_bonus = i32::from(out.battack + out.bdefense + out.btax + out.blost + out.bcost);
    let can_add_bonus = total_bonus < leadership;

    let max_troops = (out.size * 20) - out.warriors - out.archers;
    let max_equip = (out.size * 10) - out.catapults - out.barricades;

    let meta = PageMeta::titled("Moja Strażnica");
    let base = app.templates.build_context(&ctx, &meta);
    let _ = player;
    let view = MyOutpostView {
        base,
        out: outpost_info(&out),
        monsters: monsters
            .iter()
            .map(|m| MonsterInfo {
                id: m.id,
                name: m.name.clone(),
                power: m.power,
                defense: m.defense,
            })
            .collect(),
        veterans: veterans
            .iter()
            .map(|v| {
                let s = domain::veteran_stats(&domain::VeteranEquipment {
                    wpower: v.wpower,
                    weapon_name: v.weapon.as_deref().unwrap_or(""),
                    opower: v.opower,
                    apower: v.apower,
                    hpower: v.hpower,
                    lpower: v.lpower,
                    ring1: v.ring1.as_deref(),
                    rpower1: v.rpower1,
                    ring2: v.ring2.as_deref(),
                    rpower2: v.rpower2,
                });
                VeteranSummary {
                    id: v.id,
                    name: v.name.clone(),
                    attack: s.attack,
                    defense: s.defense,
                }
            })
            .collect(),
        cost,
        can_add_bonus,
        morale_label: domain::morale_label(out.morale).to_owned(),
        fatigue_display: 100 - out.fatigue,
        max_troops,
        max_equip,
    };
    app.templates.render_value("outpost_my.html", &view)
}

/// POST /outposts/bonus/:field — add a leadership bonus.
pub async fn add_bonus(
    State(app): State<AppState>,
    Extension(ctx): Extension<RequestContext>,
    Path(field): Path<String>,
) -> Response {
    let Some(ref user) = ctx.session_user else {
        return crate::page::redirect("/login");
    };

    #[allow(clippy::cast_possible_truncation)]
    let player_id = user.id as i32;

    let out = match require_outpost(&app, &ctx, player_id).await {
        Ok(o) => o,
        Err(r) => return r,
    };

    let valid_fields = ["battack", "bdefense", "btax", "blost", "bcost"];
    if !valid_fields.contains(&field.as_str()) {
        return error_page(&app, &ctx, "Nieprawidłowa premia.");
    }

    let total_bonus = i32::from(out.battack + out.bdefense + out.btax + out.blost + out.bcost);
    let leadership = load_leadership(&app, player_id).await;

    if total_bonus >= leadership {
        return error_page(&app, &ctx, "Nie możesz podnieść jakiejkolwiek premii.");
    }

    let current_val = match field.as_str() {
        "battack" => out.battack,
        "bdefense" => out.bdefense,
        "btax" => out.btax,
        "blost" => out.blost,
        "bcost" => out.bcost,
        _ => 0,
    };
    if current_val > 29 {
        return error_page(&app, &ctx, "Osiągnąłeś już maksymalny poziom tej premii.");
    }

    let _ = oq::increment_bonus(&app.pool, out.id, &field).await;
    crate::page::redirect("/outposts/my")
}

/// GET /outposts/treasury — treasury view.
pub async fn treasury_show(
    State(app): State<AppState>,
    Extension(ctx): Extension<RequestContext>,
) -> Response {
    let Some(ref user) = ctx.session_user else {
        return crate::page::redirect("/login");
    };

    #[allow(clippy::cast_possible_truncation)]
    let player_id = user.id as i32;

    let out = match require_outpost(&app, &ctx, player_id).await {
        Ok(o) => o,
        Err(r) => return r,
    };

    let player = match load_player(&app, player_id).await {
        Ok(p) => p,
        Err(r) => return r,
    };

    let meta = PageMeta::titled("Skarbiec Strażnicy");
    let base = app.templates.build_context(&ctx, &meta);
    let view = TreasuryView {
        base,
        outpost_gold: out.gold,
        player_gold: player.credits,
        message: String::new(),
    };
    app.templates.render_value("outpost_treasury.html", &view)
}

/// POST /outposts/treasury/deposit — deposit gold to outpost.
pub async fn treasury_deposit(
    State(app): State<AppState>,
    Extension(ctx): Extension<RequestContext>,
    Form(form): Form<TreasuryForm>,
) -> Response {
    let Some(ref user) = ctx.session_user else {
        return crate::page::redirect("/login");
    };

    #[allow(clippy::cast_possible_truncation)]
    let player_id = user.id as i32;

    let out = match require_outpost(&app, &ctx, player_id).await {
        Ok(o) => o,
        Err(r) => return r,
    };

    let amount = form.amount.unwrap_or(0);
    if amount <= 0 {
        return error_page(
            &app,
            &ctx,
            "Podaj ile sztuk złota chcesz dodać do strażnicy!",
        );
    }

    let player = match load_player(&app, player_id).await {
        Ok(p) => p,
        Err(r) => return r,
    };

    if amount > player.credits {
        return error_page(&app, &ctx, "Nie masz tyle sztuk złota.");
    }

    let _ = sqlx::query("UPDATE players SET credits = credits - $1 WHERE id = $2")
        .bind(amount)
        .bind(player_id)
        .execute(&app.pool)
        .await;
    let _ = oq::update_gold(&app.pool, out.id, amount).await;

    let meta = PageMeta::titled("Skarbiec Strażnicy").with_flash(Flash {
        kind: FlashKind::Success,
        message: format!("Dodałeś {amount} sztuk złota do strażnicy."),
    });
    let base = app.templates.build_context(&ctx, &meta);
    let view = TreasuryView {
        base,
        outpost_gold: out.gold + amount,
        player_gold: player.credits - amount,
        message: format!("Dodałeś {amount} sztuk złota do strażnicy."),
    };
    app.templates.render_value("outpost_treasury.html", &view)
}

/// POST /outposts/treasury/withdraw — withdraw gold from outpost.
pub async fn treasury_withdraw(
    State(app): State<AppState>,
    Extension(ctx): Extension<RequestContext>,
    Form(form): Form<TreasuryForm>,
) -> Response {
    let Some(ref user) = ctx.session_user else {
        return crate::page::redirect("/login");
    };

    #[allow(clippy::cast_possible_truncation)]
    let player_id = user.id as i32;

    let out = match require_outpost(&app, &ctx, player_id).await {
        Ok(o) => o,
        Err(r) => return r,
    };

    let amount = form.amount.unwrap_or(0);
    if amount <= 0 {
        return error_page(&app, &ctx, "Podaj ile sztuk złota chcesz zamienić!");
    }

    if amount > out.gold {
        return error_page(&app, &ctx, "Nie masz tyle sztuk złota w strażnicy!");
    }

    let received = amount / 2;
    let _ = sqlx::query("UPDATE players SET credits = credits + $1 WHERE id = $2")
        .bind(received)
        .bind(player_id)
        .execute(&app.pool)
        .await;
    let _ = oq::update_gold(&app.pool, out.id, -amount).await;

    let player = match load_player(&app, player_id).await {
        Ok(p) => p,
        Err(r) => return r,
    };

    let meta = PageMeta::titled("Skarbiec Strażnicy").with_flash(Flash {
        kind: FlashKind::Success,
        message: format!(
            "Zamieniłeś {amount} sztuk złota ze strażnicy na {received} sztuk złota do ręki."
        ),
    });
    let base = app.templates.build_context(&ctx, &meta);
    let view = TreasuryView {
        base,
        outpost_gold: out.gold - amount,
        player_gold: player.credits,
        message: format!(
            "Zamieniłeś {amount} sztuk złota ze strażnicy na {received} sztuk złota do ręki."
        ),
    };
    app.templates.render_value("outpost_treasury.html", &view)
}

/// GET /outposts/taxes — tax collection page.
pub async fn taxes_show(
    State(app): State<AppState>,
    Extension(ctx): Extension<RequestContext>,
) -> Response {
    let Some(ref user) = ctx.session_user else {
        return crate::page::redirect("/login");
    };

    #[allow(clippy::cast_possible_truncation)]
    let player_id = user.id as i32;

    let out = match require_outpost(&app, &ctx, player_id).await {
        Ok(o) => o,
        Err(r) => return r,
    };

    let meta = PageMeta::titled("Ściągnij daniny z wiosek");
    let base = app.templates.build_context(&ctx, &meta);
    let view = TaxView {
        base,
        turns: out.turns,
        message: String::new(),
    };
    app.templates.render_value("outpost_taxes.html", &view)
}

/// POST /outposts/taxes — collect taxes.
pub async fn taxes_collect(
    State(app): State<AppState>,
    Extension(ctx): Extension<RequestContext>,
    Form(form): Form<TaxForm>,
) -> Response {
    let Some(ref user) = ctx.session_user else {
        return crate::page::redirect("/login");
    };

    #[allow(clippy::cast_possible_truncation)]
    let player_id = user.id as i32;

    let out = match require_outpost(&app, &ctx, player_id).await {
        Ok(o) => o,
        Err(r) => return r,
    };

    let army = out.warriors + out.archers;
    if army == 0 {
        return error_page(&app, &ctx, "Nie masz żołnierzy aby zbierali podatki!");
    }
    if out.turns < 1 {
        return error_page(
            &app,
            &ctx,
            "Nie masz tylu Punktów Ataku aby zbierać podatki.",
        );
    }

    let times = form.amount.unwrap_or(0);
    if times <= 0 {
        return error_page(&app, &ctx, "Podaj ile razy chcesz wysłać żołnierzy!");
    }
    if times > out.turns {
        return error_page(&app, &ctx, "Nie masz tyle Punktów Ataku!");
    }

    let rolls: Vec<i32> = {
        let mut rng = rand::thread_rng();
        (0..times).map(|_| rng.gen_range(1..=5)).collect()
    };
    let gold_gain = domain::tax_gold(army, times, out.btax, &rolls);
    let new_fatigue = domain::tax_fatigue(out.fatigue, times);
    let new_morale = domain::tax_morale(out.morale, times);

    let _ = oq::collect_taxes(&app.pool, out.id, gold_gain, times, new_fatigue, new_morale).await;

    let msg = format!(
        "Twoi żołnierze wyruszyli {times} razy na zbieranie danin z wiosek i zebrali w ten sposób {gold_gain} sztuk złota."
    );

    let meta = PageMeta::titled("Ściągnij daniny z wiosek").with_flash(Flash {
        kind: FlashKind::Success,
        message: msg.clone(),
    });
    let base = app.templates.build_context(&ctx, &meta);
    let view = TaxView {
        base,
        turns: out.turns - times,
        message: msg,
    };
    app.templates.render_value("outpost_taxes.html", &view)
}

/// GET /outposts/shop — army shop and upgrades.
#[allow(clippy::too_many_lines)]
pub async fn shop_show(
    State(app): State<AppState>,
    Extension(ctx): Extension<RequestContext>,
) -> Response {
    let Some(ref user) = ctx.session_user else {
        return crate::page::redirect("/login");
    };

    #[allow(clippy::cast_possible_truncation)]
    let player_id = user.id as i32;

    let out = match require_outpost(&app, &ctx, player_id).await {
        Ok(o) => o,
        Err(r) => return r,
    };

    let (minerals, platinum, reserves) = load_shop_context(&app, player_id).await;

    let max_troops = {
        let space = (out.size * 20) - out.warriors - out.archers;
        let affordable = out.gold / 25;
        space.min(affordable)
    };
    let max_equip = {
        let space = (out.size * 10) - out.catapults - out.barricades;
        let affordable = out.gold / 35;
        space.min(affordable)
    };

    #[allow(clippy::cast_possible_truncation)]
    let max_level = domain::max_size_upgrades(out.size, out.gold, platinum as i32, minerals.pine);
    let max_lair = domain::max_structure_upgrades(
        out.fence,
        out.size,
        out.barracks,
        out.gold,
        minerals.meteor,
        minerals.crystal,
    );
    let max_barrack = domain::max_structure_upgrades(
        out.barracks,
        out.size,
        out.fence,
        out.gold,
        minerals.meteor,
        minerals.adamantium,
    );

    let meta = PageMeta::titled("Rozbudowa i zaciąg armii");
    let base = app.templates.build_context(&ctx, &meta);
    let view = ShopView {
        base,
        out: outpost_info(&out),
        reserves,
        max_troops,
        max_equip,
        max_level,
        max_lair,
        max_barrack,
        minerals,
        platinum,
        message: String::new(),
    };
    app.templates.render_value("outpost_shop.html", &view)
}

/// POST /outposts/shop/army — buy troops.
pub async fn shop_buy_army(
    State(app): State<AppState>,
    Extension(ctx): Extension<RequestContext>,
    Form(form): Form<BuyArmyForm>,
) -> Response {
    let Some(ref user) = ctx.session_user else {
        return crate::page::redirect("/login");
    };

    #[allow(clippy::cast_possible_truncation)]
    let player_id = user.id as i32;

    let out = match require_outpost(&app, &ctx, player_id).await {
        Ok(o) => o,
        Err(r) => return r,
    };

    let reserves = oq::army_reserves(&app.pool).await.unwrap_or([0; 4]);

    let w = form.army0.unwrap_or(0).max(0);
    let a = form.army1.unwrap_or(0).max(0);
    let c = form.army2.unwrap_or(0).max(0);
    let b = form.army3.unwrap_or(0).max(0);

    // Validate against space
    let max_troops = (out.size * 20) - out.warriors - out.archers;
    if w + a > max_troops {
        return error_page(
            &app,
            &ctx,
            "Nie możesz kupić tak wielu żołnierzy. Zwiększ rozmiar strażnicy.",
        );
    }
    let max_equip = (out.size * 10) - out.catapults - out.barricades;
    if c + b > max_equip {
        return error_page(
            &app,
            &ctx,
            "Nie możesz kupić tak wielu machin lub fortyfikacji.",
        );
    }

    // Validate against reserves
    if w > reserves[0] || a > reserves[1] || c > reserves[2] || b > reserves[3] {
        return error_page(&app, &ctx, "Nie ma tylu jednostek do kupienia!");
    }

    let cost = w * 25 + a * 25 + c * 35 + b * 35;
    if cost > out.gold {
        return error_page(&app, &ctx, "Nie stać Cię na to.");
    }

    let _ = oq::buy_army(&app.pool, out.id, w, a, c, b, cost).await;
    let _ = oq::deduct_army_reserves(&app.pool, w, a, c, b).await;

    crate::page::redirect("/outposts/shop")
}

/// POST /outposts/shop/upgrade — upgrade outpost size.
pub async fn shop_upgrade(
    State(app): State<AppState>,
    Extension(ctx): Extension<RequestContext>,
    Form(form): Form<UpgradeForm>,
) -> Response {
    let Some(ref user) = ctx.session_user else {
        return crate::page::redirect("/login");
    };

    #[allow(clippy::cast_possible_truncation)]
    let player_id = user.id as i32;

    let out = match require_outpost(&app, &ctx, player_id).await {
        Ok(o) => o,
        Err(r) => return r,
    };

    let levels = form.amount.unwrap_or(0);
    if levels <= 0 {
        return error_page(&app, &ctx, "Podaj ile poziomów chcesz rozbudować.");
    }

    let minerals = oq::get_minerals(&app.pool, player_id)
        .await
        .ok()
        .flatten()
        .unwrap_or(oq::MineralsRow {
            pine: 0,
            crystal: 0,
            adamantium: 0,
            meteor: 0,
        });
    let platinum = oq::get_platinum(&app.pool, player_id).await.unwrap_or(0);

    #[allow(clippy::cast_possible_truncation)]
    let max = domain::max_size_upgrades(out.size, out.gold, platinum as i32, minerals.pine);
    if levels > max {
        return error_page(
            &app,
            &ctx,
            "Nie stać Cię na powiększenie rozmiaru Strażnicy.",
        );
    }

    let (gold_cost, plat_cost, pine_cost) = domain::size_upgrade_cost(out.size, levels);

    let _ = oq::upgrade_size(&app.pool, out.id, levels, gold_cost).await;
    let _ = oq::deduct_platinum(&app.pool, player_id, plat_cost).await;
    let _ = oq::deduct_pine(&app.pool, player_id, pine_cost).await;

    crate::page::redirect("/outposts/shop")
}

/// POST /outposts/shop/lair — build beast lairs.
pub async fn shop_build_lair(
    State(app): State<AppState>,
    Extension(ctx): Extension<RequestContext>,
    Form(form): Form<UpgradeForm>,
) -> Response {
    let Some(ref user) = ctx.session_user else {
        return crate::page::redirect("/login");
    };

    #[allow(clippy::cast_possible_truncation)]
    let player_id = user.id as i32;

    let out = match require_outpost(&app, &ctx, player_id).await {
        Ok(o) => o,
        Err(r) => return r,
    };

    let amount = form.amount.unwrap_or(0);
    if amount <= 0 {
        return error_page(&app, &ctx, "Podaj liczbę legowisk.");
    }

    let minerals = oq::get_minerals(&app.pool, player_id)
        .await
        .ok()
        .flatten()
        .unwrap_or(oq::MineralsRow {
            pine: 0,
            crystal: 0,
            adamantium: 0,
            meteor: 0,
        });

    let max = domain::max_structure_upgrades(
        out.fence,
        out.size,
        out.barracks,
        out.gold,
        minerals.meteor,
        minerals.crystal,
    );
    if amount > max {
        return error_page(&app, &ctx, "Nie stać Cię na dokupienie Legowisk Bestii.");
    }

    let (gold_cost, meteor_cost, crystal_cost) = domain::structure_build_cost(out.fence, amount);

    let _ = oq::build_structure(&app.pool, out.id, "fence", amount, gold_cost).await;
    let _ = oq::deduct_minerals(&app.pool, player_id, meteor_cost, "crystal", crystal_cost).await;

    crate::page::redirect("/outposts/shop")
}

/// POST /outposts/shop/barracks — build veteran barracks.
pub async fn shop_build_barracks(
    State(app): State<AppState>,
    Extension(ctx): Extension<RequestContext>,
    Form(form): Form<UpgradeForm>,
) -> Response {
    let Some(ref user) = ctx.session_user else {
        return crate::page::redirect("/login");
    };

    #[allow(clippy::cast_possible_truncation)]
    let player_id = user.id as i32;

    let out = match require_outpost(&app, &ctx, player_id).await {
        Ok(o) => o,
        Err(r) => return r,
    };

    let amount = form.amount.unwrap_or(0);
    if amount <= 0 {
        return error_page(&app, &ctx, "Podaj liczbę kwater.");
    }

    let minerals = oq::get_minerals(&app.pool, player_id)
        .await
        .ok()
        .flatten()
        .unwrap_or(oq::MineralsRow {
            pine: 0,
            crystal: 0,
            adamantium: 0,
            meteor: 0,
        });

    let max = domain::max_structure_upgrades(
        out.barracks,
        out.size,
        out.fence,
        out.gold,
        minerals.meteor,
        minerals.adamantium,
    );
    if amount > max {
        return error_page(&app, &ctx, "Nie stać Cię na dokupienie Kwater Weteranów.");
    }

    let (gold_cost, meteor_cost, adam_cost) = domain::structure_build_cost(out.barracks, amount);

    let _ = oq::build_structure(&app.pool, out.id, "barracks", amount, gold_cost).await;
    let _ = oq::deduct_minerals(&app.pool, player_id, meteor_cost, "adamantium", adam_cost).await;

    crate::page::redirect("/outposts/shop")
}

/// GET /outposts/veterans/:id — veteran detail and equip page.
pub async fn veteran_detail(
    State(app): State<AppState>,
    Extension(ctx): Extension<RequestContext>,
    Path(veteran_id): Path<i32>,
) -> Response {
    let Some(ref user) = ctx.session_user else {
        return crate::page::redirect("/login");
    };

    #[allow(clippy::cast_possible_truncation)]
    let player_id = user.id as i32;

    let out = match require_outpost(&app, &ctx, player_id).await {
        Ok(o) => o,
        Err(r) => return r,
    };

    let Ok(Some(vet)) = oq::find_veteran(&app.pool, veteran_id, out.id).await else {
        return error_page(&app, &ctx, "Nie znaleziono weterana.");
    };

    render_veteran_detail(&app, &ctx, &vet, player_id, String::new()).await
}

/// POST /outposts/veterans/:id/equip — equip a veteran.
#[allow(clippy::too_many_lines)]
pub async fn veteran_equip(
    State(app): State<AppState>,
    Extension(ctx): Extension<RequestContext>,
    Path(veteran_id): Path<i32>,
    Form(form): Form<EquipVeteranForm>,
) -> Response {
    let Some(ref user) = ctx.session_user else {
        return crate::page::redirect("/login");
    };

    #[allow(clippy::cast_possible_truncation)]
    let player_id = user.id as i32;

    let out = match require_outpost(&app, &ctx, player_id).await {
        Ok(o) => o,
        Err(r) => return r,
    };

    let Ok(Some(_vet)) = oq::find_veteran(&app.pool, veteran_id, out.id).await else {
        return error_page(&app, &ctx, "Nie znaleziono weterana.");
    };

    let slots: &[(&str, &str, Option<i32>, bool)] = &[
        ("weapon", "wpower", form.weapon, false),
        ("armor", "apower", form.armor, false),
        ("helm", "hpower", form.helm, false),
        ("legs", "lpower", form.legs, false),
        ("ring1", "rpower1", form.ring1, false),
        ("ring2", "rpower2", form.ring2, false),
        ("arrows", "opower", form.arrows, true),
    ];

    let mut equipped = Vec::new();
    for &(slot, power_col, item_id_opt, is_arrows) in slots {
        let item_id = item_id_opt.unwrap_or(0);
        if item_id <= 0 {
            continue;
        }

        // Validate the item exists and belongs to player
        let items = oq::list_equip_for_veteran(&app.pool, player_id, slot_to_type(slot))
            .await
            .unwrap_or_default();

        let Some(item) = items.iter().find(|i| i.id == item_id) else {
            continue;
        };

        let item_power = item.power / 10;
        let item_name = item.name.clone();

        // Consume the item
        if is_arrows {
            if item.wt < 20 {
                continue;
            }
            let _ = oq::consume_arrows(&app.pool, item_id, player_id).await;
        } else {
            let _ = oq::consume_equipment(&app.pool, item_id, player_id).await;
        }

        let _ = oq::equip_veteran(
            &app.pool, veteran_id, slot, power_col, &item_name, item_power,
        )
        .await;
        equipped.push(item_name);
    }

    let msg = if equipped.is_empty() {
        String::new()
    } else {
        format!("Dodałeś weteranowi ekwipunek: {}", equipped.join(", "))
    };

    let Ok(Some(vet)) = oq::find_veteran(&app.pool, veteran_id, out.id).await else {
        return error_page(&app, &ctx, "Nie znaleziono weterana.");
    };

    render_veteran_detail(&app, &ctx, &vet, player_id, msg).await
}

/// GET /outposts/list — list other outposts.
pub async fn list_outposts(
    State(app): State<AppState>,
    Extension(ctx): Extension<RequestContext>,
    Query(params): Query<ListSearchForm>,
) -> Response {
    let Some(ref user) = ctx.session_user else {
        return crate::page::redirect("/login");
    };

    #[allow(clippy::cast_possible_truncation)]
    let player_id = user.id as i32;

    let out = match require_outpost(&app, &ctx, player_id).await {
        Ok(o) => o,
        Err(r) => return r,
    };

    let min_default = (out.size + 1) / 2;
    let max_default = out.size * 2;
    let min_size = params.slevel.unwrap_or(min_default).max(1);
    let max_size = params.elevel.unwrap_or(max_default).max(min_size);

    let outposts = if params.slevel.is_some() || params.elevel.is_some() {
        oq::list_by_size_range(&app.pool, min_size, max_size, out.id)
            .await
            .unwrap_or_default()
            .into_iter()
            .map(|o| OutpostListItem {
                id: o.id,
                size: o.size,
                owner_id: o.owner,
                owner_name: o.owner_name,
            })
            .collect()
    } else {
        Vec::new()
    };

    let meta = PageMeta::titled("Lista Strażnic");
    let base = app.templates.build_context(&ctx, &meta);
    let view = OutpostListView {
        base,
        outposts,
        min_size,
        max_size,
        own_size: out.size,
    };
    app.templates.render_value("outpost_list.html", &view)
}

/// GET /outposts/battle — battle form.
pub async fn battle_show(
    State(app): State<AppState>,
    Extension(ctx): Extension<RequestContext>,
) -> Response {
    let Some(ref _user) = ctx.session_user else {
        return crate::page::redirect("/login");
    };

    let meta = PageMeta::titled("Atakuj Strażnicę");
    let base = app.templates.build_context(&ctx, &meta);
    let view = BattleView {
        base,
        messages: Vec::new(),
    };
    app.templates.render_value("outpost_battle.html", &view)
}

/// POST /outposts/battle — execute attack.
#[allow(clippy::too_many_lines)]
pub async fn battle_execute(
    State(app): State<AppState>,
    Extension(ctx): Extension<RequestContext>,
    Form(form): Form<BattleForm>,
) -> Response {
    let Some(ref user) = ctx.session_user else {
        return crate::page::redirect("/login");
    };

    #[allow(clippy::cast_possible_truncation)]
    let player_id = user.id as i32;

    let out = match require_outpost(&app, &ctx, player_id).await {
        Ok(o) => o,
        Err(r) => return r,
    };

    let player = match load_player(&app, player_id).await {
        Ok(p) => p,
        Err(r) => return r,
    };

    if player.hp <= 0 {
        return error_page(
            &app,
            &ctx,
            "Ponieważ jesteś martwy, nie możesz korzystać ze strażnicy.",
        );
    }

    if out.fatigue <= 25 {
        return error_page(&app, &ctx, "Twoja armia jest zbyt zmęczona by atakować!");
    }

    if out.warriors == 0 && out.archers == 0 {
        return error_page(&app, &ctx, "Nie masz wojsk aby atakować innego gracza!");
    }

    let amount = form.amount.unwrap_or(1).clamp(1, 3);
    if amount > out.turns {
        return error_page(&app, &ctx, "Nie masz wystarczającej ilości punktów ataku.");
    }

    // Find target
    let oid = form.oid.unwrap_or(0);
    let pid = form.pid.unwrap_or(0);
    if oid <= 0 && pid <= 0 {
        return error_page(&app, &ctx, "Nie wybrałeś celu ataku!");
    }

    let target_result = if oid > 0 {
        if oid == out.id {
            return error_page(&app, &ctx, "Nie możesz zaatakować własnej Strażnicy.");
        }
        oq::find_by_id(&app.pool, oid).await
    } else {
        if pid == player_id {
            return error_page(&app, &ctx, "Nie możesz atakować sam siebie.");
        }
        oq::find_by_owner_id(&app.pool, pid).await
    };

    let Ok(Some(enemy)) = target_result else {
        return error_page(&app, &ctx, "Nie ma takiej strażnicy.");
    };

    if i32::from(enemy.attacks) + amount > 3 {
        return error_page(
            &app,
            &ctx,
            "Jedna strażnica może być zaatakowana tylko 3 razy na reset!",
        );
    }

    // Get enemy owner name for messages
    let enemy_name = pq::find_player_by_id(&app.pool, enemy.owner)
        .await
        .ok()
        .flatten()
        .map_or_else(|| "Nieznany".to_owned(), |p| p.username);

    let mut messages = Vec::new();
    let mut current_attacker = out.clone();
    let mut current_defender = enemy.clone();

    // Pre-generate all random values (ThreadRng is !Send, cannot hold across .await).
    // Max 3 rounds, each needs: 2 combat bonuses, 3 att loss rolls, 4 def loss rolls,
    // 1 gold bonus roll, and up to ~40 kill-chance rolls for monsters/veterans.
    let round_randoms = {
        let mut rng = rand::thread_rng();
        let mut rounds = Vec::new();
        for _ in 0..amount {
            let combat_a = rng.gen_range(-5..=5);
            let combat_d = rng.gen_range(-5..=5);
            let att_win_rolls = [
                rng.gen_range(0..=8),
                rng.gen_range(0..=8),
                rng.gen_range(0..=8),
            ];
            let att_lose_rolls = [
                rng.gen_range(25..=35),
                rng.gen_range(25..=35),
                rng.gen_range(25..=35),
            ];
            let def_win_rolls = [
                rng.gen_range(0..=8),
                rng.gen_range(0..=8),
                rng.gen_range(0..=8),
                rng.gen_range(0..=8),
            ];
            let def_lose_rolls = [
                rng.gen_range(25..=35),
                rng.gen_range(25..=35),
                rng.gen_range(25..=35),
                rng.gen_range(25..=35),
            ];
            let gold_bonus = rng.gen_range(1..=100);
            let kill_chances: Vec<i32> = (0..40).map(|_| rng.gen_range(1..=100)).collect();
            rounds.push((
                combat_a,
                combat_d,
                att_win_rolls,
                att_lose_rolls,
                def_win_rolls,
                def_lose_rolls,
                gold_bonus,
                kill_chances,
            ));
        }
        rounds
    };

    for rr in &round_randoms {
        let (
            combat_a,
            combat_d,
            att_win_rolls,
            att_lose_rolls,
            def_win_rolls,
            def_lose_rolls,
            gold_bonus,
            ref kill_chances,
        ) = *rr;

        // Load current monsters/veterans for both sides
        let my_monsters = oq::list_monsters(&app.pool, current_attacker.id)
            .await
            .unwrap_or_default();
        let my_veterans = oq::list_veterans(&app.pool, current_attacker.id)
            .await
            .unwrap_or_default();
        let e_monsters = oq::list_monsters(&app.pool, current_defender.id)
            .await
            .unwrap_or_default();
        let e_veterans = oq::list_veterans(&app.pool, current_defender.id)
            .await
            .unwrap_or_default();

        let (my_matt, my_mdef) = sum_special_stats(&my_monsters, &my_veterans);
        let (e_matt, e_mdef) = sum_special_stats(&e_monsters, &e_veterans);

        let my_stats = domain::compute_combat_stats(&domain::OutpostCombatInput {
            warriors: current_attacker.warriors,
            archers: current_attacker.archers,
            catapults: current_attacker.catapults,
            barricades: current_attacker.barricades,
            battack: current_attacker.battack,
            bdefense: current_attacker.bdefense,
            morale: current_attacker.morale,
            monster_attack: my_matt,
            monster_defense: my_mdef,
            veteran_attack: 0,
            veteran_defense: 0,
            random_bonus: f64::from(combat_a) / 100.0,
        });

        let e_stats = domain::compute_combat_stats(&domain::OutpostCombatInput {
            warriors: current_defender.warriors,
            archers: current_defender.archers,
            catapults: current_defender.catapults,
            barricades: current_defender.barricades,
            battack: current_defender.battack,
            bdefense: current_defender.bdefense,
            morale: current_defender.morale,
            monster_attack: e_matt,
            monster_defense: e_mdef,
            veteran_attack: 0,
            veteran_defense: 0,
            random_bonus: f64::from(combat_d) / 100.0,
        });

        let attacker_wins = my_stats.attack > e_stats.defense;

        let att_rolls = if attacker_wins {
            att_win_rolls
        } else {
            att_lose_rolls
        };
        let def_rolls = if attacker_wins {
            def_lose_rolls
        } else {
            def_win_rolls
        };

        let att_losses = domain::attacker_losses(&domain::AttackerLossInput {
            warriors: current_attacker.warriors,
            archers: current_attacker.archers,
            catapults: current_attacker.catapults,
            attacker_stronger: attacker_wins && my_stats.attack > e_stats.defense,
            blost: current_attacker.blost,
            fatigue: current_attacker.fatigue,
            attacker_size: current_attacker.size,
            defender_size: current_defender.size,
            rolls: att_rolls,
        });

        let att_total_losses = (current_attacker.warriors - att_losses.warriors)
            + (current_attacker.archers - att_losses.archers)
            + (current_attacker.catapults - att_losses.catapults);

        let def_losses = domain::defender_losses(&domain::DefenderLossInput {
            warriors: current_defender.warriors,
            archers: current_defender.archers,
            catapults: current_defender.catapults,
            barricades: current_defender.barricades,
            blost: current_defender.blost,
            rolls: def_rolls,
            cap_to_attacker_losses: !attacker_wins,
            attacker_total_losses: att_total_losses,
        });

        // Apply losses
        let _ = oq::set_army(
            &app.pool,
            current_attacker.id,
            att_losses.warriors,
            att_losses.archers,
            att_losses.catapults,
            current_attacker.barricades,
        )
        .await;
        let _ = oq::set_fatigue(&app.pool, current_attacker.id, att_losses.new_fatigue).await;

        let _ = oq::set_army(
            &app.pool,
            current_defender.id,
            def_losses.warriors,
            def_losses.archers,
            def_losses.catapults,
            def_losses.barricades,
        )
        .await;

        // Gold transfer and experience
        let msg = if attacker_wins {
            let looted = current_defender.gold / 10;
            if looted > 0 {
                let _ = oq::update_gold(&app.pool, current_defender.id, -looted).await;
            }
            let gained = domain::attack_gold_gain(
                att_losses.warriors,
                att_losses.archers,
                def_losses.warriors,
                def_losses.archers,
                looted,
                gold_bonus,
            );
            let _ = oq::update_gold(&app.pool, current_attacker.id, gained).await;

            // Morale
            let _ = oq::adjust_morale(&app.pool, current_attacker.id, 7.5).await;
            let _ = oq::adjust_morale(&app.pool, current_defender.id, -10.0).await;

            format!(
                "Atakujesz strażnicę gracza {enemy_name} i wygrywasz! Zdobywasz {gained} sztuk złota."
            )
        } else {
            // Morale
            let _ = oq::adjust_morale(&app.pool, current_attacker.id, -10.0).await;
            let _ = oq::adjust_morale(&app.pool, current_defender.id, 7.5).await;

            format!("Atakujesz strażnicę gracza {enemy_name} lecz niestety przegrywasz!")
        };

        // 5% chance to lose monsters/veterans
        let mut kill_idx = 0_usize;
        for m in &my_monsters {
            if kill_chances.get(kill_idx).copied().unwrap_or(100) < 6 {
                let _ = oq::delete_monster(&app.pool, m.id).await;
            }
            kill_idx += 1;
        }
        for m in &e_monsters {
            if kill_chances.get(kill_idx).copied().unwrap_or(100) < 6 {
                let _ = oq::delete_monster(&app.pool, m.id).await;
            }
            kill_idx += 1;
        }
        for v in &my_veterans {
            if kill_chances.get(kill_idx).copied().unwrap_or(100) < 6 {
                let _ = oq::delete_veteran(&app.pool, v.id).await;
            }
            kill_idx += 1;
        }
        for v in &e_veterans {
            if kill_chances.get(kill_idx).copied().unwrap_or(100) < 6 {
                let _ = oq::delete_veteran(&app.pool, v.id).await;
            }
            kill_idx += 1;
        }

        // Record attack
        let _ = oq::increment_attacks(&app.pool, current_defender.id).await;
        let _ = oq::spend_turns(&app.pool, current_attacker.id, 1).await;

        messages.push(msg);

        // Reload for next round
        current_attacker = oq::find_by_id(&app.pool, current_attacker.id)
            .await
            .ok()
            .flatten()
            .unwrap_or(current_attacker);
        current_defender = oq::find_by_id(&app.pool, current_defender.id)
            .await
            .ok()
            .flatten()
            .unwrap_or(current_defender);

        if current_attacker.fatigue <= 25 {
            messages.push("Twoja armia jest zbyt zmęczona aby mogła atakować dalej!".to_owned());
            break;
        }
        if current_defender.attacks >= 3 {
            messages.push(
                "Nie możesz więcej atakować tej strażnicy! Musisz poczekać do kolejnego resetu."
                    .to_owned(),
            );
            break;
        }
    }

    let meta = PageMeta::titled("Wynik bitwy");
    let base = app.templates.build_context(&ctx, &meta);
    let view = BattleView { base, messages };
    app.templates.render_value("outpost_battle.html", &view)
}

/// GET /outposts/guide — static guide page.
pub async fn guide(
    State(app): State<AppState>,
    Extension(ctx): Extension<RequestContext>,
) -> Response {
    let meta = PageMeta::titled("Instrukcja Strażnicy");
    let base = app.templates.build_context(&ctx, &meta);
    let view = GuideView { base };
    app.templates.render_value("outpost_guide.html", &view)
}

// =========================================================================
// Garrison mission handlers (outpost.php)
// =========================================================================

/// GET /garrison — garrison guard post.
#[allow(clippy::too_many_lines)]
pub async fn garrison_show(
    State(app): State<AppState>,
    Extension(ctx): Extension<RequestContext>,
) -> Response {
    let Some(ref user) = ctx.session_user else {
        return crate::page::redirect("/login");
    };

    #[allow(clippy::cast_possible_truncation)]
    let player_id = user.id as i32;

    let player = match load_player(&app, player_id).await {
        Ok(p) => p,
        Err(r) => return r,
    };

    if player.location != "Altara" && player.location != "Ardulith" {
        return error_page(&app, &ctx, "Nie znajdujesz się w mieście.");
    }

    if !domain::can_access_garrison(&player.class) {
        return error_page(
            &app,
            &ctx,
            "Tylko parający się bronią bądź magią mają wstęp do tego budynku!",
        );
    }

    let meta = PageMeta::titled("Prefektura Gwardii");
    let base = app.templates.build_context(&ctx, &meta);
    let view = GarrisonMenuView {
        base,
        has_missions: false,
        craft_mission: i32::from(player.craft_mission),
        missions: Vec::new(),
    };
    app.templates.render_value("garrison.html", &view)
}

/// POST /garrison/generate — generate missions.
#[allow(clippy::too_many_lines)]
pub async fn garrison_generate(
    State(app): State<AppState>,
    Extension(ctx): Extension<RequestContext>,
) -> Response {
    let Some(ref user) = ctx.session_user else {
        return crate::page::redirect("/login");
    };

    #[allow(clippy::cast_possible_truncation)]
    let player_id = user.id as i32;

    let player = match load_player(&app, player_id).await {
        Ok(p) => p,
        Err(r) => return r,
    };

    if !domain::can_access_garrison(&player.class) {
        return error_page(&app, &ctx, "Brak dostępu.");
    }

    if player.craft_mission <= 0 {
        return error_page(
            &app,
            &ctx,
            "Nie mamy dla ciebie jakiegokolwiek rozkazu. Wróć za jakiś czas.",
        );
    }

    if player.hp <= 0 {
        return error_page(
            &app,
            &ctx,
            "Nie możesz przyjąć zlecenia, ponieważ jesteś martwy.",
        );
    }

    let available = domain::mission_types_for_class(&player.class);

    let missions: Vec<GarrisonMission> = {
        let mut rng = rand::thread_rng();
        (0..3)
            .map(|i| {
                let idx = rng.gen_range(0..available.len());
                let mission_type = available[idx];
                let desc = garrison_mission_description(mission_type, &player.location);
                GarrisonMission {
                    index: i,
                    description: desc,
                }
            })
            .collect()
    };

    // Decrement craft_mission
    let _ = sqlx::query("UPDATE players SET craft_mission = craft_mission - 1 WHERE id = $1")
        .bind(player_id)
        .execute(&app.pool)
        .await;

    let meta = PageMeta::titled("Prefektura Gwardii");
    let base = app.templates.build_context(&ctx, &meta);
    let view = GarrisonMenuView {
        base,
        has_missions: true,
        craft_mission: i32::from(player.craft_mission) - 1,
        missions,
    };
    app.templates.render_value("garrison.html", &view)
}

/// POST /garrison/execute/:index — execute a garrison mission.
#[allow(clippy::too_many_lines)]
pub async fn garrison_execute(
    State(app): State<AppState>,
    Extension(ctx): Extension<RequestContext>,
    Path(index): Path<usize>,
) -> Response {
    let Some(ref user) = ctx.session_user else {
        return crate::page::redirect("/login");
    };

    #[allow(clippy::cast_possible_truncation)]
    let player_id = user.id as i32;

    let player = match load_player(&app, player_id).await {
        Ok(p) => p,
        Err(r) => return r,
    };

    if !domain::can_access_garrison(&player.class) {
        return error_page(&app, &ctx, "Brak dostępu.");
    }

    if index > 2 {
        return error_page(&app, &ctx, "Nieprawidłowe zlecenie.");
    }

    if player.energy < 5.0 {
        return error_page(&app, &ctx, "Nie masz tyle energii.");
    }

    // Deduct energy
    let _ = sqlx::query("UPDATE players SET energy = energy - 5 WHERE id = $1")
        .bind(player_id)
        .execute(&app.pool)
        .await;

    let (roll, damage_roll) = {
        let mut rng = rand::thread_rng();
        (rng.gen_range(1..=100), rng.gen_range(1..=25))
    };

    let stats = load_player_stats_for_garrison(&app, player_id).await;
    let (plevel, skill_name) = domain::garrison_player_level(&domain::GarrisonPlayerStats {
        condition: stats.condition,
        speed: stats.speed,
        agility: stats.agility,
        dodge: stats.dodge,
        hp: player.hp,
        strength: stats.strength,
        wisdom: stats.wisdom,
        intelligence: stats.intelligence,
        attack_skill: stats.attack,
        shoot_skill: stats.shoot,
        magic_skill: stats.magic,
        has_melee_weapon: stats.has_melee_weapon,
        has_bow: stats.has_bow,
    });

    let gold = plevel * 5;
    let gender_suffix = if player.gender.as_deref() == Some("M") {
        "eś"
    } else {
        "aś"
    };

    // Simple patrol outcome based on roll
    let result = if roll < 80 {
        // Success — gold and exp
        let _ = sqlx::query(
            "UPDATE players SET credits = credits + $1, mpoints = mpoints + 1 WHERE id = $2",
        )
        .bind(i64::from(gold))
        .bind(player_id)
        .execute(&app.pool)
        .await;
        let _ = grant_skill_exp(&app, player_id, skill_name, plevel).await;
        format!(
            "Zadanie zakończone. Otrzymał{gender_suffix} {gold} sztuk złota oraz {plevel} punktów doświadczenia."
        )
    } else if roll < 95 {
        // Good success — double reward
        let bonus_gold = gold + plevel * 5;
        let exp = plevel * 2;
        let _ = sqlx::query(
            "UPDATE players SET credits = credits + $1, mpoints = mpoints + 1 WHERE id = $2",
        )
        .bind(i64::from(bonus_gold))
        .bind(player_id)
        .execute(&app.pool)
        .await;
        let _ = grant_skill_exp(&app, player_id, skill_name, exp).await;
        format!(
            "Doskonale wykonane zadanie! Otrzymał{gender_suffix} {bonus_gold} sztuk złota oraz {exp} punktów doświadczenia."
        )
    } else {
        // Failure — damage
        #[allow(clippy::cast_possible_truncation)]
        let damage = ((f64::from(player.max_hp) / 100.0) * f64::from(damage_roll)).ceil() as i32;
        let new_hp = (player.hp - damage).max(0);
        let _ = sqlx::query("UPDATE players SET hp = $1 WHERE id = $2")
            .bind(new_hp)
            .bind(player_id)
            .execute(&app.pool)
            .await;
        format!(
            "To nie był twój szczęśliwy dzień. Odniosł{gender_suffix} {damage} obrażeń. Nie otrzymał{gender_suffix} zapłaty."
        )
    };

    let meta = PageMeta::titled("Prefektura Gwardii");
    let base = app.templates.build_context(&ctx, &meta);
    let view = GarrisonResultView {
        base,
        result,
        can_refresh: player.craft_mission > 1,
    };
    app.templates.render_value("garrison_result.html", &view)
}

// =========================================================================
// Helpers
// =========================================================================

async fn load_player(state: &AppState, player_id: i32) -> Result<pq::PlayerRow, Response> {
    match pq::find_player_by_id(&state.pool, player_id).await {
        Ok(Some(row)) => Ok(row),
        Ok(None) => Err(crate::page::redirect("/login")),
        Err(e) => {
            tracing::error!(error = %e, "load_player failed");
            Err(server_error())
        }
    }
}

async fn require_outpost(
    state: &AppState,
    ctx: &RequestContext,
    player_id: i32,
) -> Result<oq::OutpostRow, Response> {
    match oq::find_by_owner(&state.pool, player_id).await {
        Ok(Some(o)) => Ok(o),
        Ok(None) => Err(error_page(
            state,
            ctx,
            "Nie masz strażnicy. Kup najpierw ziemię pod nią.",
        )),
        Err(e) => {
            tracing::error!(error = %e, "require_outpost");
            Err(server_error())
        }
    }
}

fn outpost_info(o: &oq::OutpostRow) -> OutpostInfo {
    OutpostInfo {
        id: o.id,
        size: o.size,
        warriors: o.warriors,
        archers: o.archers,
        catapults: o.catapults,
        barricades: o.barricades,
        gold: o.gold,
        turns: o.turns,
        battack: o.battack,
        bdefense: o.bdefense,
        btax: o.btax,
        blost: o.blost,
        bcost: o.bcost,
        fence: o.fence,
        barracks: o.barracks,
        morale: o.morale,
    }
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

fn slot_to_type(slot: &str) -> &str {
    match slot {
        "armor" => "A",
        "helm" => "H",
        "legs" => "L",
        "ring1" | "ring2" => "I",
        "arrows" => "R",
        // "weapon" and anything else
        _ => "W",
    }
}

async fn load_outpost_context(
    state: &AppState,
    out: &oq::OutpostRow,
    player_id: i32,
) -> (
    Vec<oq::OutpostMonsterRow>,
    Vec<oq::OutpostVeteranRow>,
    pq::PlayerRow,
    i32,
) {
    let monsters = oq::list_monsters(&state.pool, out.id)
        .await
        .unwrap_or_default();
    let veterans = oq::list_veterans(&state.pool, out.id)
        .await
        .unwrap_or_default();
    let player = load_player(state, player_id).await.unwrap_or_else(|_| {
        panic!("player must exist");
    });
    let leadership = load_leadership(state, player_id).await;
    (monsters, veterans, player, leadership)
}

async fn load_leadership(state: &AppState, player_id: i32) -> i32 {
    let skills = vallheru_data::queries::player::load_skills(&state.pool, player_id)
        .await
        .unwrap_or_default();
    skills
        .iter()
        .find(|s| s.skill_key == "leadership")
        .map_or(0, |s| s.level)
}

async fn load_shop_context(state: &AppState, player_id: i32) -> (MineralInfo, i64, [i32; 4]) {
    let minerals = oq::get_minerals(&state.pool, player_id)
        .await
        .ok()
        .flatten()
        .map_or_else(MineralInfo::default, |m| MineralInfo {
            pine: m.pine,
            crystal: m.crystal,
            adamantium: m.adamantium,
            meteor: m.meteor,
        });
    let platinum = oq::get_platinum(&state.pool, player_id).await.unwrap_or(0);
    let reserves = oq::army_reserves(&state.pool).await.unwrap_or([0; 4]);
    (minerals, platinum, reserves)
}

fn sum_special_stats(
    monsters: &[oq::OutpostMonsterRow],
    veterans: &[oq::OutpostVeteranRow],
) -> (i32, i32) {
    let mut attack = 0;
    let mut defense = 0;
    for m in monsters {
        attack += m.power;
        defense += m.defense;
    }
    for v in veterans {
        let s = domain::veteran_stats(&domain::VeteranEquipment {
            wpower: v.wpower,
            weapon_name: v.weapon.as_deref().unwrap_or(""),
            opower: v.opower,
            apower: v.apower,
            hpower: v.hpower,
            lpower: v.lpower,
            ring1: v.ring1.as_deref(),
            rpower1: v.rpower1,
            ring2: v.ring2.as_deref(),
            rpower2: v.rpower2,
        });
        attack += s.attack;
        defense += s.defense;
    }
    (attack, defense)
}

async fn render_veteran_detail(
    state: &AppState,
    ctx: &RequestContext,
    vet: &oq::OutpostVeteranRow,
    player_id: i32,
    message: String,
) -> Response {
    let s = domain::veteran_stats(&domain::VeteranEquipment {
        wpower: vet.wpower,
        weapon_name: vet.weapon.as_deref().unwrap_or(""),
        opower: vet.opower,
        apower: vet.apower,
        hpower: vet.hpower,
        lpower: vet.lpower,
        ring1: vet.ring1.as_deref(),
        rpower1: vet.rpower1,
        ring2: vet.ring2.as_deref(),
        rpower2: vet.rpower2,
    });

    let weapons = load_equip_options(state, player_id, "W").await;
    let armor_opts = load_equip_options(state, player_id, "A").await;
    let helm_opts = load_equip_options(state, player_id, "H").await;
    let legs_opts = load_equip_options(state, player_id, "L").await;
    let ring_opts = load_equip_options(state, player_id, "I").await;
    let arrow_opts = load_equip_options(state, player_id, "R").await;

    let meta = PageMeta::titled("Weteran");
    let base = state.templates.build_context(ctx, &meta);
    let view = VeteranDetailView {
        base,
        veteran: VeteranDetail {
            id: vet.id,
            name: vet.name.clone(),
            weapon: vet.weapon.clone().unwrap_or_else(|| "brak".to_owned()),
            wpower: vet.wpower,
            armor: vet.armor.clone().unwrap_or_else(|| "brak".to_owned()),
            apower: vet.apower,
            helm: vet.helm.clone().unwrap_or_else(|| "brak".to_owned()),
            hpower: vet.hpower,
            legs: vet.legs.clone().unwrap_or_else(|| "brak".to_owned()),
            lpower: vet.lpower,
            ring1: vet.ring1.clone().unwrap_or_else(|| "brak".to_owned()),
            rpower1: vet.rpower1,
            ring2: vet.ring2.clone().unwrap_or_else(|| "brak".to_owned()),
            rpower2: vet.rpower2,
            arrows: vet.arrows.clone().unwrap_or_else(|| "brak".to_owned()),
            opower: vet.opower,
            attack: s.attack,
            defense: s.defense,
        },
        equip_weapons: weapons,
        equip_armor: armor_opts,
        equip_helm: helm_opts,
        equip_legs: legs_opts,
        equip_ring: ring_opts,
        equip_arrows: arrow_opts,
        message,
    };
    state.templates.render_value("outpost_veteran.html", &view)
}

async fn load_equip_options(state: &AppState, player_id: i32, eq_type: &str) -> Vec<EquipOption> {
    oq::list_equip_for_veteran(&state.pool, player_id, eq_type)
        .await
        .unwrap_or_default()
        .into_iter()
        .map(|e| EquipOption {
            id: e.id,
            name: e.name,
            power: e.power / 10,
        })
        .collect()
}

fn garrison_mission_description(mission_type: i32, location: &str) -> String {
    let other_city = if location == "Altara" {
        "Ardulith"
    } else {
        "Altara"
    };
    match mission_type {
        0 => format!("Potrzebujemy kogoś, kto dołączy do patrolu na drodze do {other_city}."),
        1 => "Ktoś o twoich umiejętnościach mógłby nam pomóc szkolić rekrutów.".to_owned(),
        2 => "Namierzyliśmy kryjówkę bandytów i organizujemy grupę by ich schwytać.".to_owned(),
        3 => "Potrzebujemy kogoś, kto dołączy do patrolu w Górach Kazad-nar.".to_owned(),
        4 => "Potrzebujemy kogoś, kto dołączy do patrolu w Lesie Avantiel.".to_owned(),
        5 => "Złodzieje w mieście się rozpanoszyli. Przyda się ktoś, kto wesprze nasze patrole."
            .to_owned(),
        6 => "Namierzyliśmy gniazdo potworów napadających na karawany. Organizujemy grupę."
            .to_owned(),
        7 => "Bandyci dają się we znaki. Dostaniesz paru żołnierzy i spróbujesz ich odnaleźć."
            .to_owned(),
        8 => {
            "Potwory rozzuchwaliły się. Dostaniesz paru ludzi i spróbujesz je odnaleźć.".to_owned()
        }
        9 => format!("Organizujemy oddział który dołączy do karawany do {other_city}."),
        10 => "Ktoś o twoich umiejętnościach mógłby nam pomóc szkolić adeptów magii.".to_owned(),
        _ => "Zadanie specjalne.".to_owned(),
    }
}

struct GarrisonStats {
    condition: i32,
    speed: i32,
    agility: i32,
    dodge: i32,
    strength: i32,
    wisdom: i32,
    intelligence: i32,
    attack: i32,
    shoot: i32,
    magic: i32,
    has_melee_weapon: bool,
    has_bow: bool,
}

async fn load_player_stats_for_garrison(state: &AppState, player_id: i32) -> GarrisonStats {
    let raw_stats = vallheru_data::queries::player::load_stats(&state.pool, player_id)
        .await
        .unwrap_or_default();
    let skills = vallheru_data::queries::player::load_skills(&state.pool, player_id)
        .await
        .unwrap_or_default();

    let find_stat = |key: &str| -> i32 {
        raw_stats
            .iter()
            .find(|s| s.stat_key == key)
            .map_or(1, |s| s.trained.max(1))
    };
    let find_skill = |key: &str| -> i32 {
        skills
            .iter()
            .find(|s| s.skill_key == key)
            .map_or(0, |s| s.level)
    };

    // Check equipped weapons
    let equipped = vallheru_data::queries::item::find_equipped_items(&state.pool, player_id)
        .await
        .unwrap_or_default();

    let has_melee_weapon = equipped
        .iter()
        .any(|e| e.equipment_type == "W" || e.equipment_type == "S");
    let has_bow = equipped.iter().any(|e| e.equipment_type == "B");

    GarrisonStats {
        condition: find_stat("condition"),
        speed: find_stat("speed"),
        agility: find_stat("agility"),
        dodge: find_skill("dodge"),
        strength: find_stat("strength"),
        wisdom: find_stat("wisdom"),
        intelligence: find_stat("inteli"),
        attack: find_skill("attack"),
        shoot: find_skill("shoot"),
        magic: find_skill("magic"),
        has_melee_weapon,
        has_bow,
    }
}

async fn grant_skill_exp(
    state: &AppState,
    player_id: i32,
    skill: &str,
    amount: i32,
) -> Result<(), sqlx::Error> {
    sqlx::query("UPDATE player_skills SET xp = xp + $1 WHERE player_id = $2 AND skill_key = $3")
        .bind(amount)
        .bind(player_id)
        .bind(skill)
        .execute(&state.pool)
        .await?;
    Ok(())
}
