//! Gathering-related handlers: mining, mines, lumberjack, smelter, farm.
//!
//! Ported from `kopalnia.php`, `mines.php`, `lumberjack.php`, `smelter.php`,
//! and `farm.php`.

use std::fmt::Write;

use axum::{Extension, Form, extract::State, response::Response};
use rand::Rng;

use crate::middleware::context::RequestContext;
use crate::page::{Flash, FlashKind, PageMeta};
use crate::state::AppState;
use vallheru_domain::crafting::gathering;

// =========================================================================
// View models
// =========================================================================

#[derive(serde::Serialize)]
pub struct MountainMineView {
    #[serde(flatten)]
    pub base: crate::render::RenderContext,
    pub energy: i32,
}

#[derive(serde::Deserialize)]
pub struct EnergyForm {
    pub amount: Option<i32>,
}

#[derive(serde::Serialize)]
pub struct MinesView {
    #[serde(flatten)]
    pub base: crate::render::RenderContext,
    pub deposits: MineDeposits,
    pub search_status: String,
}

#[derive(serde::Serialize, Default)]
pub struct MineDeposits {
    pub copper: i32,
    pub zinc: i32,
    pub tin: i32,
    pub iron: i32,
    pub coal: i32,
}

#[derive(serde::Serialize)]
pub struct MineDigView {
    #[serde(flatten)]
    pub base: crate::render::RenderContext,
    pub mine: String,
    pub has_deposits: bool,
    pub energy: i32,
}

#[derive(serde::Deserialize)]
pub struct MineDigForm {
    pub amount: Option<i32>,
    pub mine: Option<String>,
}

#[derive(serde::Serialize)]
pub struct LumberjackView {
    #[serde(flatten)]
    pub base: crate::render::RenderContext,
    pub license_level: i32,
    pub energy: i32,
    pub available_wood: Vec<WoodOption>,
}

#[derive(serde::Serialize)]
pub struct WoodOption {
    pub key: String,
    pub label: String,
}

#[derive(serde::Deserialize)]
pub struct LumberjackForm {
    pub amount: Option<i32>,
    pub wood_type: Option<String>,
}

#[derive(serde::Serialize)]
pub struct SmelterView {
    #[serde(flatten)]
    pub base: crate::render::RenderContext,
    pub smelter_level: i32,
    pub available_actions: Vec<SmeltAction>,
}

#[derive(serde::Serialize)]
pub struct SmeltAction {
    pub key: String,
    pub label: String,
}

#[derive(serde::Deserialize)]
pub struct SmeltForm {
    pub amount: Option<i32>,
    pub bar_type: Option<String>,
}

#[derive(serde::Serialize)]
pub struct FarmView {
    #[serde(flatten)]
    pub base: crate::render::RenderContext,
    pub has_plantation: bool,
    pub lands: i32,
    pub free_lands: i32,
    pub glasshouse: i32,
    pub irrigation: i32,
    pub creeper: i32,
    pub plots: Vec<FarmPlotInfo>,
}

#[derive(serde::Serialize)]
pub struct FarmPlotInfo {
    pub id: i32,
    pub name: String,
    pub amount: i32,
    pub age: i32,
    pub stage: String,
}

// =========================================================================
// Mountain mining (kopalnia.php)
// =========================================================================

/// GET /mining — show mountain mining page.
pub async fn mining_show(
    State(app): State<AppState>,
    Extension(ctx): Extension<RequestContext>,
) -> Response {
    let Some(ref user) = ctx.session_user else {
        return crate::page::redirect("/login");
    };

    #[allow(clippy::cast_possible_truncation)]
    let player_id = user.id as i32;

    let player_row = match load_player(&app, player_id).await {
        Ok(row) => row,
        Err(resp) => return resp,
    };

    if gathering::check_mountains(&player_row.location).is_err() {
        return error_page(&app, &ctx, "Nie znajdujesz się w górach.");
    }

    if player_row.hp <= 0 {
        return error_page(&app, &ctx, "Nie możesz kopać, ponieważ jesteś martwy!");
    }

    #[allow(clippy::cast_possible_truncation)]
    let energy = player_row.energy as i32;

    let meta = PageMeta::titled("Kopalnie górskie");
    let base = app.templates.build_context(&ctx, &meta);
    let view = MountainMineView { base, energy };
    app.templates.render_value("mining.html", &view)
}

/// POST /mining — execute mountain mining.
#[allow(clippy::too_many_lines)]
pub async fn mining_work(
    State(app): State<AppState>,
    Extension(ctx): Extension<RequestContext>,
    Form(form): Form<EnergyForm>,
) -> Response {
    let Some(ref user) = ctx.session_user else {
        return crate::page::redirect("/login");
    };

    #[allow(clippy::cast_possible_truncation)]
    let player_id = user.id as i32;

    let player_row = match load_player(&app, player_id).await {
        Ok(row) => row,
        Err(resp) => return resp,
    };

    if gathering::check_mountains(&player_row.location).is_err() {
        return error_page(&app, &ctx, "Nie znajdujesz się w górach.");
    }

    if player_row.hp <= 0 {
        return error_page(&app, &ctx, "Nie możesz kopać, ponieważ jesteś martwy!");
    }

    let amount = match form.amount {
        Some(a) if a > 0 => a,
        _ => return error_page(&app, &ctx, "Podaj ile energii chcesz przeznaczyć."),
    };

    #[allow(clippy::cast_possible_truncation)]
    let avail = player_row.energy as i32;
    if amount > avail {
        return error_page(&app, &ctx, "Nie masz tyle energii!");
    }

    // Load stats + skills for bonus calculation
    let stats = match vallheru_data::queries::player::load_stats(&app.pool, player_id).await {
        Ok(v) => v,
        Err(e) => {
            tracing::error!(error = %e, player_id, "load_stats failed");
            Vec::new()
        }
    };
    let skills = match vallheru_data::queries::player::load_skills(&app.pool, player_id).await {
        Ok(v) => v,
        Err(e) => {
            tracing::error!(error = %e, player_id, "load_skills failed");
            Vec::new()
        }
    };
    let mining_skill = find_skill(&skills, "mining");
    let strength = find_stat(&stats, "strength");
    let speed = find_stat(&stats, "speed");
    let is_craftsman = player_row.class == "Rzemieślnik";

    let bonus = gathering::mountain_mine_bonus(mining_skill, strength);

    // Simulate per-energy RNG loop (scoped so `rng` drops before async calls)
    let mut result = {
        let mut rng = rand::thread_rng();
        let mut result = gathering::MountainMineResult::default();

        for _ in 0..amount {
            let roll: i32 = rng.gen_range(1..=10);
            let sub_roll: i32 = if roll == 9 {
                rng.gen_range(50..=200)
            } else {
                rng.gen_range(1..=20)
            };

            match gathering::interpret_mountain_roll(roll, sub_roll, bonus) {
                gathering::MountainMineEvent::Nothing => {}
                gathering::MountainMineEvent::Crystals(n) => result.crystals += n,
                gathering::MountainMineEvent::Adamantium(n) => result.adamantium += n,
                gathering::MountainMineEvent::Mithril(n) => result.mithril += n,
                gathering::MountainMineEvent::Diamonds(n) => result.gold += n,
                gathering::MountainMineEvent::CaveIn => {
                    let speed_roll = rng.gen_range(1..=(speed * 10).max(1));
                    let diff_roll = rng.gen_range(1..=200);
                    if !gathering::survives_cave_in(speed_roll, diff_roll) {
                        result.player_died = true;
                        break;
                    }
                }
            }
        }
        result
    };

    // Apply results
    let xp = gathering::mountain_mine_xp(
        result.crystals,
        result.adamantium,
        result.mithril,
        result.gold,
        is_craftsman,
    );
    result.xp_strength = xp.0;
    result.xp_speed = xp.1;
    result.xp_mining = xp.2;

    // Ensure minerals row exists, then add minerals
    if let Err(e) = vallheru_data::queries::gathering::ensure_minerals(&app.pool, player_id).await {
        tracing::error!(error = %e, "ensure_minerals failed");
        return server_error();
    }

    let mut mineral_updates: Vec<(&str, i32)> = Vec::new();
    if result.crystals > 0 {
        mineral_updates.push(("crystal", result.crystals));
    }
    if result.adamantium > 0 {
        mineral_updates.push(("adamantium", result.adamantium));
    }

    if !mineral_updates.is_empty() {
        if let Err(e) =
            vallheru_data::queries::gathering::add_minerals(&app.pool, player_id, &mineral_updates)
                .await
        {
            tracing::error!(error = %e, "add_minerals failed");
            return server_error();
        }
    }

    // Deduct energy and add gold
    if let Err(e) =
        vallheru_data::queries::gathering::deduct_energy(&app.pool, player_id, f64::from(amount))
            .await
    {
        tracing::error!(error = %e, "deduct_energy failed");
        return server_error();
    }

    if result.gold > 0 {
        if let Err(e) = vallheru_data::queries::gathering::deduct_currency(
            &app.pool,
            player_id,
            -result.gold,
            0,
        )
        .await
        {
            tracing::error!(error = %e, "add gold failed");
            return server_error();
        }
    }

    // If the player died, persist HP = 0 (penalty applied at resurrection).
    if result.player_died {
        if let Err(e) = vallheru_data::queries::player::kill_player(&app.pool, player_id).await {
            tracing::error!(error = %e, "kill_player after cave-in failed");
            return server_error();
        }
    }

    let total_xp = result.xp_strength + result.xp_speed + result.xp_mining;

    // Apply stat/skill XP: strength, speed, mining.
    let xp_extra = apply_gathering_xp(
        &app,
        player_id,
        &player_row,
        &[("strength", result.xp_strength), ("speed", result.xp_speed)],
        &[("mining", result.xp_mining)],
    )
    .await;

    let msg = if result.player_died {
        format!(
            "Nastąpiło zawalenie się kopalni! Nie udało ci się uciec. Przed śmiercią zdobyłeś: {total_xp} PD.{xp_extra}"
        )
    } else {
        format!(
            "Zużyłeś {amount} energii. Zdobyłeś: {} kryształów, {} adamantium, {} mithrilu, {} złota. Łącznie {total_xp} PD.{xp_extra}",
            result.crystals, result.adamantium, result.mithril, result.gold
        )
    };

    let meta = PageMeta::titled("Kopalnie górskie").with_flash(Flash::success(msg));
    let base = app.templates.build_context(&ctx, &meta);

    #[allow(clippy::cast_possible_truncation)]
    let new_energy = (player_row.energy - f64::from(amount)) as i32;
    let view = MountainMineView {
        base,
        energy: new_energy.max(0),
    };
    app.templates.render_value("mining.html", &view)
}

// =========================================================================
// Mines (mines.php)
// =========================================================================

/// GET /mines — show mine overview.
pub async fn mines_show(
    State(app): State<AppState>,
    Extension(ctx): Extension<RequestContext>,
) -> Response {
    let Some(ref user) = ctx.session_user else {
        return crate::page::redirect("/login");
    };

    #[allow(clippy::cast_possible_truncation)]
    let player_id = user.id as i32;

    let player_row = match load_player(&app, player_id).await {
        Ok(row) => row,
        Err(resp) => return resp,
    };

    if gathering::check_altara(&player_row.location).is_err() {
        return error_page(&app, &ctx, "Nie znajdujesz się w mieście.");
    }

    let deposits = vallheru_data::queries::gathering::load_mines(&app.pool, player_id)
        .await
        .unwrap_or_else(|e| {
            tracing::error!(error = %e, player_id, "load_mines failed");
            None
        });

    let dep = deposits.map_or(MineDeposits::default(), |d| MineDeposits {
        copper: d.copper,
        zinc: d.zinc,
        tin: d.tin,
        iron: d.iron,
        coal: d.coal,
    });

    let search = vallheru_data::queries::gathering::load_mines_search(&app.pool, player_id)
        .await
        .unwrap_or_else(|e| {
            tracing::error!(error = %e, player_id, "load_mines_search failed");
            None
        });

    let search_status = search.map_or(String::new(), |s| {
        format!(
            "Twój geolog aktualnie poszukuje złóż {}. Zajmie mu to jeszcze {} dni.",
            s.mineral, s.days
        )
    });

    let meta = PageMeta::titled("Kopalnie");
    let base = app.templates.build_context(&ctx, &meta);
    let view = MinesView {
        base,
        deposits: dep,
        search_status,
    };
    app.templates.render_value("mines.html", &view)
}

/// POST /mines/dig — dig ore from deposits.
#[allow(clippy::too_many_lines)]
pub async fn mines_dig(
    State(app): State<AppState>,
    Extension(ctx): Extension<RequestContext>,
    Form(form): Form<MineDigForm>,
) -> Response {
    let Some(ref user) = ctx.session_user else {
        return crate::page::redirect("/login");
    };

    #[allow(clippy::cast_possible_truncation)]
    let player_id = user.id as i32;

    let player_row = match load_player(&app, player_id).await {
        Ok(row) => row,
        Err(resp) => return resp,
    };

    if gathering::check_altara(&player_row.location).is_err() {
        return error_page(&app, &ctx, "Nie znajdujesz się w mieście.");
    }

    if player_row.hp <= 0 {
        return error_page(&app, &ctx, "Nie możesz kopać ponieważ jesteś martwy!");
    }

    let Some(mine_key) = form.mine.as_deref() else {
        return error_page(&app, &ctx, "Wybierz kopalnię.");
    };

    let Some(ore_type) = gathering::OreType::parse(mine_key) else {
        return error_page(&app, &ctx, "Zapomnij o tym.");
    };

    let amount = match form.amount {
        Some(a) if a > 0 => a,
        _ => return error_page(&app, &ctx, "Podaj ile energii chcesz przeznaczyć."),
    };

    #[allow(clippy::cast_possible_truncation)]
    let avail_energy = player_row.energy as i32;
    if amount > avail_energy {
        return error_page(&app, &ctx, "Nie masz tyle energii!");
    }

    // Check deposit availability
    let deposits = vallheru_data::queries::gathering::load_mines(&app.pool, player_id)
        .await
        .unwrap_or_else(|e| {
            tracing::error!(error = %e, player_id, "load_mines failed");
            None
        });

    let deposit_amount = deposits.map_or(0, |d| match ore_type {
        gathering::OreType::Copper => d.copper,
        gathering::OreType::Zinc => d.zinc,
        gathering::OreType::Tin => d.tin,
        gathering::OreType::Iron => d.iron,
        gathering::OreType::Coal => d.coal,
    });

    if deposit_amount <= 0 {
        return error_page(&app, &ctx, "Nie ma złóż w tej kopalni!");
    }

    let stats = match vallheru_data::queries::player::load_stats(&app.pool, player_id).await {
        Ok(v) => v,
        Err(e) => {
            tracing::error!(error = %e, player_id, "load_stats failed");
            Vec::new()
        }
    };
    let skills = match vallheru_data::queries::player::load_skills(&app.pool, player_id).await {
        Ok(v) => v,
        Err(e) => {
            tracing::error!(error = %e, player_id, "load_skills failed");
            Vec::new()
        }
    };
    let mining_skill = find_skill(&skills, "mining");
    let strength = find_stat(&stats, "strength");
    let is_craftsman = player_row.class == "Rzemieślnik";

    let roll: i32 = rand::thread_rng().gen_range(1..=20);

    let dig_result = gathering::dig_ore(
        ore_type,
        amount,
        mining_skill,
        strength,
        roll,
        deposit_amount,
        is_craftsman,
    );

    // Store mined ore in minerals
    if let Err(e) = vallheru_data::queries::gathering::ensure_minerals(&app.pool, player_id).await {
        tracing::error!(error = %e, "ensure_minerals failed");
        return server_error();
    }

    let ore_col = ore_type.ore_column();
    if let Err(e) = vallheru_data::queries::gathering::add_minerals(
        &app.pool,
        player_id,
        &[(ore_col, dig_result.ore_gained)],
    )
    .await
    {
        tracing::error!(error = %e, "add_minerals failed");
        return server_error();
    }

    // Deduct deposit
    if let Err(e) = vallheru_data::queries::gathering::deduct_deposit(
        &app.pool,
        player_id,
        ore_type.as_str(),
        dig_result.ore_gained,
    )
    .await
    {
        tracing::error!(error = %e, "deduct_deposit failed");
        return server_error();
    }

    // Deduct energy
    if let Err(e) =
        vallheru_data::queries::gathering::deduct_energy(&app.pool, player_id, f64::from(amount))
            .await
    {
        tracing::error!(error = %e, "deduct_energy failed");
        return server_error();
    }

    let msg = format!(
        "Przeznaczyłeś na wydobycie {} energii. Zdobyłeś w zamian {} sztuk rudy oraz {} PD.",
        amount, dig_result.ore_gained, dig_result.xp
    );

    // Apply XP: strength (1/3), speed (1/3), mining (1/3).
    let third = dig_result.xp / 3;
    let xp_extra = apply_gathering_xp(
        &app,
        player_id,
        &player_row,
        &[("strength", third), ("speed", third)],
        &[("mining", third)],
    )
    .await;

    let msg = if xp_extra.is_empty() {
        msg
    } else {
        format!("{msg}{xp_extra}")
    };

    let meta = PageMeta::titled("Kopalnie").with_flash(Flash::success(msg));
    let base = app.templates.build_context(&ctx, &meta);

    // Reload deposits for the view
    let deposits = vallheru_data::queries::gathering::load_mines(&app.pool, player_id)
        .await
        .unwrap_or_else(|e| {
            tracing::error!(error = %e, player_id, "load_mines failed");
            None
        });
    let dep = deposits.map_or(MineDeposits::default(), |d| MineDeposits {
        copper: d.copper,
        zinc: d.zinc,
        tin: d.tin,
        iron: d.iron,
        coal: d.coal,
    });

    let view = MinesView {
        base,
        deposits: dep,
        search_status: String::new(),
    };
    app.templates.render_value("mines.html", &view)
}

// =========================================================================
// Lumberjack
// =========================================================================

/// GET /lumberjack — show lumberjack page.
pub async fn lumberjack_show(
    State(app): State<AppState>,
    Extension(ctx): Extension<RequestContext>,
) -> Response {
    let Some(ref user) = ctx.session_user else {
        return crate::page::redirect("/login");
    };

    #[allow(clippy::cast_possible_truncation)]
    let player_id = user.id as i32;

    let player_row = match load_player(&app, player_id).await {
        Ok(row) => row,
        Err(resp) => return resp,
    };

    if gathering::check_forest(&player_row.location).is_err() {
        return error_page(&app, &ctx, "Nie znajdujesz się w lesie.");
    }

    if player_row.hp <= 0 {
        return error_page(
            &app,
            &ctx,
            "Nie możesz rąbać drewna, ponieważ jesteś martwy!",
        );
    }

    let license_level =
        vallheru_data::queries::gathering::load_lumberjack_level(&app.pool, player_id)
            .await
            .unwrap_or_else(|e| {
                tracing::error!(error = %e, player_id, "load_lumberjack_level failed");
                0
            });

    if license_level < 1 {
        return error_page(
            &app,
            &ctx,
            "Nie posiadasz licencji drwalskiej. Musisz kupić ją w tartaku.",
        );
    }

    let available_wood = build_wood_options(license_level);

    #[allow(clippy::cast_possible_truncation)]
    let energy = player_row.energy as i32;

    let meta = PageMeta::titled("Drwal");
    let base = app.templates.build_context(&ctx, &meta);
    let view = LumberjackView {
        base,
        license_level,
        energy,
        available_wood,
    };
    app.templates.render_value("lumberjack.html", &view)
}

/// POST /lumberjack — execute lumberjack gathering.
#[allow(clippy::too_many_lines)]
pub async fn lumberjack_work(
    State(app): State<AppState>,
    Extension(ctx): Extension<RequestContext>,
    Form(form): Form<LumberjackForm>,
) -> Response {
    let Some(ref user) = ctx.session_user else {
        return crate::page::redirect("/login");
    };

    #[allow(clippy::cast_possible_truncation)]
    let player_id = user.id as i32;

    let player_row = match load_player(&app, player_id).await {
        Ok(row) => row,
        Err(resp) => return resp,
    };

    if gathering::check_forest(&player_row.location).is_err() {
        return error_page(&app, &ctx, "Nie znajdujesz się w lesie.");
    }

    if player_row.hp <= 0 {
        return error_page(
            &app,
            &ctx,
            "Nie możesz rąbać drewna, ponieważ jesteś martwy!",
        );
    }

    let license_level =
        vallheru_data::queries::gathering::load_lumberjack_level(&app.pool, player_id)
            .await
            .unwrap_or_else(|e| {
                tracing::error!(error = %e, player_id, "load_lumberjack_level failed");
                0
            });

    let Some(wood_key) = form.wood_type.as_deref() else {
        return error_page(&app, &ctx, "Wybierz rodzaj drewna.");
    };

    let wood_type = match wood_key {
        "pine" => gathering::WoodType::Pine,
        "hazel" => gathering::WoodType::Hazel,
        "yew" => gathering::WoodType::Yew,
        "elm" => gathering::WoodType::Elm,
        _ => return error_page(&app, &ctx, "Zapomnij o tym."),
    };

    if let Err(e) = gathering::check_lumber_license(license_level, wood_type) {
        return error_page(&app, &ctx, &e.to_string());
    }

    let amount = match form.amount {
        Some(a) if a > 0 => a,
        _ => return error_page(&app, &ctx, "Podaj ile energii chcesz przeznaczyć."),
    };

    #[allow(clippy::cast_possible_truncation)]
    let avail = player_row.energy as i32;
    if amount > avail {
        return error_page(&app, &ctx, "Nie masz tyle energii!");
    }

    let stats = match vallheru_data::queries::player::load_stats(&app.pool, player_id).await {
        Ok(v) => v,
        Err(e) => {
            tracing::error!(error = %e, player_id, "load_stats failed");
            Vec::new()
        }
    };
    let skills = match vallheru_data::queries::player::load_skills(&app.pool, player_id).await {
        Ok(v) => v,
        Err(e) => {
            tracing::error!(error = %e, player_id, "load_skills failed");
            Vec::new()
        }
    };
    let lumberjack_skill = find_skill(&skills, "lumberjack");
    let strength = find_stat(&stats, "strength");
    let is_craftsman = player_row.class == "Rzemieślnik";

    let bonus = gathering::lumber_bonus(lumberjack_skill, strength);

    let mut result = {
        let mut rng = rand::thread_rng();
        let mut result = gathering::LumberResult::default();

        for _ in 0..amount {
            let roll: i32 = rng.gen_range(1..=8);
            let sub_roll: i32 = if roll == 7 {
                rng.gen_range(1..=100)
            } else {
                rng.gen_range(1..=20)
            };

            match gathering::interpret_lumber_roll(roll, sub_roll, bonus) {
                gathering::LumberEvent::Nothing => {}
                gathering::LumberEvent::Wood(n) => result.wood += n,
                gathering::LumberEvent::Gold(n) => result.gold += n,
                gathering::LumberEvent::TreeFall => {
                    let survival_roll = rng.gen_range(1..=100);
                    if !gathering::survives_tree_fall(survival_roll) {
                        result.player_died = true;
                        break;
                    }
                }
            }
        }
        result
    };

    // XP split: lumberjack (half), strength (half)
    let base_xp = result.wood * 2;
    let xp = if is_craftsman { base_xp * 2 } else { base_xp };
    result.xp_lumberjack = xp / 2;
    result.xp_strength = xp / 2;

    // Store wood
    if result.wood > 0 {
        if let Err(e) =
            vallheru_data::queries::gathering::ensure_minerals(&app.pool, player_id).await
        {
            tracing::error!(error = %e, "ensure_minerals failed");
            return server_error();
        }
        if let Err(e) = vallheru_data::queries::gathering::add_minerals(
            &app.pool,
            player_id,
            &[(wood_type.as_str(), result.wood)],
        )
        .await
        {
            tracing::error!(error = %e, "add_minerals failed");
            return server_error();
        }
    }

    // Deduct energy
    if let Err(e) =
        vallheru_data::queries::gathering::deduct_energy(&app.pool, player_id, f64::from(amount))
            .await
    {
        tracing::error!(error = %e, "deduct_energy failed");
        return server_error();
    }

    // Add gold
    if result.gold > 0 {
        if let Err(e) = vallheru_data::queries::gathering::deduct_currency(
            &app.pool,
            player_id,
            -result.gold,
            0,
        )
        .await
        {
            tracing::error!(error = %e, "add gold failed");
            return server_error();
        }
    }

    // If the player died, persist HP = 0 (penalty applied at resurrection).
    if result.player_died {
        if let Err(e) = vallheru_data::queries::player::kill_player(&app.pool, player_id).await {
            tracing::error!(error = %e, "kill_player after tree fall failed");
            return server_error();
        }
    }

    let total_xp = result.xp_lumberjack + result.xp_strength;

    // Apply stat/skill XP: strength, lumberjack.
    let xp_extra = apply_gathering_xp(
        &app,
        player_id,
        &player_row,
        &[("strength", result.xp_strength)],
        &[("lumberjack", result.xp_lumberjack)],
    )
    .await;

    let msg = if result.player_died {
        format!("Spadło na ciebie drzewo! Nie przeżyłeś. Zdobyłeś {total_xp} PD.{xp_extra}")
    } else {
        format!(
            "Zużyłeś {amount} energii. Zdobyłeś: {} drewna, {} złota. Łącznie {total_xp} PD.{xp_extra}",
            result.wood, result.gold
        )
    };

    let meta = PageMeta::titled("Drwal").with_flash(Flash::success(msg));
    let base = app.templates.build_context(&ctx, &meta);

    #[allow(clippy::cast_possible_truncation)]
    let new_energy = (player_row.energy - f64::from(amount)) as i32;
    let view = LumberjackView {
        base,
        license_level,
        energy: new_energy.max(0),
        available_wood: build_wood_options(license_level),
    };
    app.templates.render_value("lumberjack.html", &view)
}

// =========================================================================
// Smelter
// =========================================================================

/// GET /smelter — show smelter page.
pub async fn smelter_show(
    State(app): State<AppState>,
    Extension(ctx): Extension<RequestContext>,
) -> Response {
    let Some(ref user) = ctx.session_user else {
        return crate::page::redirect("/login");
    };

    #[allow(clippy::cast_possible_truncation)]
    let player_id = user.id as i32;

    let player_row = match load_player(&app, player_id).await {
        Ok(row) => row,
        Err(resp) => return resp,
    };

    if gathering::check_altara(&player_row.location).is_err() {
        return error_page(&app, &ctx, "Nie znajdujesz się w mieście.");
    }

    let smelter_level = vallheru_data::queries::gathering::load_smelter_level(&app.pool, player_id)
        .await
        .unwrap_or_else(|e| {
            tracing::error!(error = %e, player_id, "load_smelter_level failed");
            0
        });

    let available_actions = build_smelt_actions(smelter_level);

    let meta = PageMeta::titled("Huta");
    let base = app.templates.build_context(&ctx, &meta);
    let view = SmelterView {
        base,
        smelter_level,
        available_actions,
    };
    app.templates.render_value("smelter.html", &view)
}

/// POST /smelter/smelt — smelt ore into bars.
#[allow(clippy::too_many_lines)]
pub async fn smelter_smelt(
    State(app): State<AppState>,
    Extension(ctx): Extension<RequestContext>,
    Form(form): Form<SmeltForm>,
) -> Response {
    let Some(ref user) = ctx.session_user else {
        return crate::page::redirect("/login");
    };

    #[allow(clippy::cast_possible_truncation)]
    let player_id = user.id as i32;

    let player_row = match load_player(&app, player_id).await {
        Ok(row) => row,
        Err(resp) => return resp,
    };

    if gathering::check_altara(&player_row.location).is_err() {
        return error_page(&app, &ctx, "Nie znajdujesz się w mieście.");
    }

    if player_row.hp <= 0 {
        return error_page(&app, &ctx, "Nie możesz wytapiać, ponieważ jesteś martwy!");
    }

    let smelter_level = vallheru_data::queries::gathering::load_smelter_level(&app.pool, player_id)
        .await
        .unwrap_or_else(|e| {
            tracing::error!(error = %e, player_id, "load_smelter_level failed");
            0
        });

    let Some(bar_key) = form.bar_type.as_deref() else {
        return error_page(&app, &ctx, "Wybierz surowiec do wytopienia.");
    };

    let Some(bar) = gathering::MetalBar::parse(bar_key) else {
        return error_page(&app, &ctx, "Zapomnij o tym.");
    };

    if let Err(e) = gathering::check_smelter_level(smelter_level, bar) {
        return error_page(&app, &ctx, &e.to_string());
    }

    let amount = match form.amount {
        Some(a) if a > 0 => a,
        _ => return error_page(&app, &ctx, "Podaj ile sztabek chcesz wytopić."),
    };

    let energy_cost = gathering::smelt_ore_energy(bar) * f64::from(amount);
    if player_row.energy < energy_cost {
        return error_page(&app, &ctx, "Nie masz tyle energii!");
    }

    // Check ore availability
    let minerals =
        match vallheru_data::queries::gathering::load_minerals(&app.pool, player_id).await {
            Ok(opt) => opt.unwrap_or_default(),
            Err(e) => {
                tracing::error!(error = %e, player_id, "load_minerals failed");
                vallheru_data::queries::gathering::MineralsRow::default()
            }
        };

    let recipe = gathering::smelt_recipe(bar);
    for &(col, per_bar) in &recipe {
        let available = get_mineral_field(&minerals, col);
        let needed = per_bar * amount;
        if available < needed {
            return error_page(
                &app,
                &ctx,
                "Nie masz minerałów potrzebnych do wytapiania tego surowca!",
            );
        }
    }

    // Load stats + skills
    let stats = match vallheru_data::queries::player::load_stats(&app.pool, player_id).await {
        Ok(v) => v,
        Err(e) => {
            tracing::error!(error = %e, player_id, "load_stats failed");
            Vec::new()
        }
    };
    let skills = match vallheru_data::queries::player::load_skills(&app.pool, player_id).await {
        Ok(v) => v,
        Err(e) => {
            tracing::error!(error = %e, player_id, "load_skills failed");
            Vec::new()
        }
    };
    let smelting_skill = find_skill(&skills, "smelting");
    let condition = find_stat(&stats, "condition");
    let is_craftsman = player_row.class == "Rzemieślnik";

    // Generate rolls (scoped so RNG drops before async calls)
    let diff = bar.difficulty() * 100;
    let skill_range = (smelting_skill + condition).max(1) * 100;
    let rolls: Vec<(i32, i32)> = {
        let mut rng = rand::thread_rng();
        (0..amount)
            .map(|_| (rng.gen_range(1..=skill_range), rng.gen_range(1..=diff)))
            .collect()
    };

    let smelt_result =
        gathering::smelt_ore(bar, amount, smelting_skill, condition, &rolls, is_craftsman);

    // Deduct ores, add bars
    let mut ore_deductions: Vec<(&str, i32)> = recipe
        .iter()
        .map(|&(col, per_bar)| (col, -(per_bar * amount)))
        .collect();
    ore_deductions.push((bar.column(), smelt_result.bars_produced));

    if let Err(e) =
        vallheru_data::queries::gathering::add_minerals(&app.pool, player_id, &ore_deductions).await
    {
        tracing::error!(error = %e, "smelt ore update failed");
        return server_error();
    }

    // Deduct energy
    if let Err(e) =
        vallheru_data::queries::gathering::deduct_energy(&app.pool, player_id, energy_cost).await
    {
        tracing::error!(error = %e, "deduct_energy failed");
        return server_error();
    }

    // Apply stat/skill XP: condition (half), smelting (half).
    let half_xp = smelt_result.xp / 2;
    let xp_extra = apply_gathering_xp(
        &app,
        player_id,
        &player_row,
        &[("condition", half_xp)],
        &[("smelting", half_xp)],
    )
    .await;

    let msg = format!(
        "Uzyskałeś {} sztabek {}. Zdobywasz {} PD.{xp_extra}",
        smelt_result.bars_produced, bar_key, smelt_result.xp
    );

    let meta = PageMeta::titled("Huta").with_flash(Flash::success(msg));
    let base = app.templates.build_context(&ctx, &meta);
    let view = SmelterView {
        base,
        smelter_level,
        available_actions: build_smelt_actions(smelter_level),
    };
    app.templates.render_value("smelter.html", &view)
}

/// POST /smelter/upgrade — upgrade smelter level.
pub async fn smelter_upgrade(
    State(app): State<AppState>,
    Extension(ctx): Extension<RequestContext>,
) -> Response {
    let Some(ref user) = ctx.session_user else {
        return crate::page::redirect("/login");
    };

    #[allow(clippy::cast_possible_truncation)]
    let player_id = user.id as i32;

    let player_row = match load_player(&app, player_id).await {
        Ok(row) => row,
        Err(resp) => return resp,
    };

    if gathering::check_altara(&player_row.location).is_err() {
        return error_page(&app, &ctx, "Nie znajdujesz się w mieście.");
    }

    let smelter_level = vallheru_data::queries::gathering::load_smelter_level(&app.pool, player_id)
        .await
        .unwrap_or_else(|e| {
            tracing::error!(error = %e, player_id, "load_smelter_level failed");
            0
        });

    let Some(cost) = gathering::smelter_upgrade_cost(smelter_level) else {
        return error_page(&app, &ctx, "Nie możesz więcej rozbudowywać huty!");
    };

    if player_row.credits < cost {
        return error_page(&app, &ctx, "Nie masz tyle sztuk złota!");
    }

    if let Err(e) = vallheru_data::queries::gathering::upgrade_smelter(&app.pool, player_id).await {
        tracing::error!(error = %e, "upgrade_smelter failed");
        return server_error();
    }

    if let Err(e) =
        vallheru_data::queries::gathering::deduct_currency(&app.pool, player_id, cost, 0).await
    {
        tracing::error!(error = %e, "deduct gold failed");
        return server_error();
    }

    let meta =
        PageMeta::titled("Huta").with_flash(Flash::success("Rozbudowałeś swoją hutę.".to_owned()));
    let base = app.templates.build_context(&ctx, &meta);
    let view = SmelterView {
        base,
        smelter_level: smelter_level + 1,
        available_actions: build_smelt_actions(smelter_level + 1),
    };
    app.templates.render_value("smelter.html", &view)
}

// =========================================================================
// Farm (overview only — full farm actions are complex)
// =========================================================================

/// GET /farm — show farm overview.
pub async fn farm_show(
    State(app): State<AppState>,
    Extension(ctx): Extension<RequestContext>,
) -> Response {
    let Some(ref user) = ctx.session_user else {
        return crate::page::redirect("/login");
    };

    #[allow(clippy::cast_possible_truncation)]
    let player_id = user.id as i32;

    let player_row = match load_player(&app, player_id).await {
        Ok(row) => row,
        Err(resp) => return resp,
    };

    if gathering::check_city(&player_row.location).is_err() {
        return error_page(&app, &ctx, "Nie znajdujesz się w mieście.");
    }

    let plantation =
        vallheru_data::queries::gathering::load_farm(&app.pool, player_id, &player_row.location)
            .await
            .unwrap_or_else(|e| {
                tracing::error!(error = %e, player_id, "load_farm failed");
                None
            });

    let (has_plantation, lands, glasshouse, irrigation, creeper, free_lands, plots) =
        if let Some(ref farm) = plantation {
            let all_plots = match vallheru_data::queries::gathering::load_farm_plots(
                &app.pool, farm.id,
            )
            .await
            {
                Ok(v) => v,
                Err(e) => {
                    tracing::error!(error = %e, farm_id = farm.id, "load_farm_plots failed");
                    Vec::new()
                }
            };

            let occupied: i32 = all_plots.iter().map(|p| p.amount).sum();
            let free = farm.lands - occupied;

            let plot_info: Vec<FarmPlotInfo> = all_plots
                .into_iter()
                .map(|p| {
                    let stage = gathering::GrowthStage::from_age(p.age);
                    FarmPlotInfo {
                        id: p.id,
                        name: p.name.unwrap_or_default(),
                        amount: p.amount,
                        age: p.age,
                        stage: format_growth_stage(stage),
                    }
                })
                .collect();

            (
                true,
                farm.lands,
                farm.glasshouse,
                farm.irrigation,
                farm.creeper,
                free,
                plot_info,
            )
        } else {
            (false, 0, 0, 0, 0, 0, Vec::new())
        };

    let meta = PageMeta::titled("Farma");
    let base = app.templates.build_context(&ctx, &meta);
    let view = FarmView {
        base,
        has_plantation,
        lands,
        free_lands,
        glasshouse,
        irrigation,
        creeper,
        plots,
    };
    app.templates.render_value("farm.html", &view)
}

// =========================================================================
// Helpers
// =========================================================================

async fn load_player(
    state: &AppState,
    player_id: i32,
) -> Result<vallheru_data::queries::player::PlayerRow, Response> {
    match vallheru_data::queries::player::find_player_by_id(&state.pool, player_id).await {
        Ok(Some(row)) => Ok(row),
        Ok(None) => Err(crate::page::redirect("/login")),
        Err(e) => {
            tracing::error!(error = %e, "load_player failed");
            Err(server_error())
        }
    }
}

fn find_stat(stats: &[vallheru_domain::player::stats::PlayerStat], key: &str) -> i32 {
    stats
        .iter()
        .find(|s| s.stat_key == key)
        .map_or(1, |s| s.trained.max(1))
}

fn find_skill(skills: &[vallheru_domain::player::skills::PlayerSkill], key: &str) -> i32 {
    skills
        .iter()
        .find(|s| s.skill_key == key)
        .map_or(1, |s| s.level.max(1))
}

/// Apply stat and skill XP awards from gathering activities.
///
/// `stat_xp` is a list of `(stat_key, xp_amount)` pairs.
/// `skill_xp` is a list of `(skill_key, xp_amount)` pairs.
///
/// Returns extra flash text about any level-ups (empty if none).
async fn apply_gathering_xp(
    app: &AppState,
    player_id: i32,
    player_row: &vallheru_data::queries::player::PlayerRow,
    stat_xp: &[(&str, i32)],
    skill_xp: &[(&str, i32)],
) -> String {
    use vallheru_domain::player::progression;

    let Some(race) = vallheru_domain::player::Race::from_db(&player_row.race) else {
        return String::new();
    };
    let Some(class) = vallheru_domain::player::Class::from_db(&player_row.class) else {
        return String::new();
    };

    let mut extra = String::new();
    let mut hp_change = 0;

    // Apply stat XP.
    if !stat_xp.is_empty() {
        let mut stats = match vallheru_data::queries::player::load_stats(&app.pool, player_id).await
        {
            Ok(v) => v,
            Err(e) => {
                tracing::error!(error = %e, player_id, "apply_gathering_xp: load_stats failed");
                Vec::new()
            }
        };

        for &(key, xp) in stat_xp {
            if xp <= 0 {
                continue;
            }
            if let Some(stat) = stats.iter_mut().find(|s| s.stat_key == key) {
                let result = progression::apply_stat_xp(stat, xp, &race, &class);
                if result.levels_gained > 0 {
                    let _ = write!(
                        extra,
                        " Twój stat {} wzrósł o {} poziom(ów)!",
                        key, result.levels_gained
                    );
                }
                hp_change += result.hp_change;
            }
        }

        if let Err(e) =
            vallheru_data::queries::player::save_stats(&app.pool, player_id, &stats).await
        {
            tracing::error!(error = %e, "apply_gathering_xp: save_stats failed");
        }
    }

    // Apply skill XP.
    if !skill_xp.is_empty() {
        let mut skills = match vallheru_data::queries::player::load_skills(&app.pool, player_id)
            .await
        {
            Ok(v) => v,
            Err(e) => {
                tracing::error!(error = %e, player_id, "apply_gathering_xp: load_skills failed");
                Vec::new()
            }
        };

        for &(key, xp) in skill_xp {
            if xp <= 0 {
                continue;
            }
            if let Some(skill) = skills.iter_mut().find(|s| s.skill_key == key) {
                let result = progression::apply_skill_xp(skill, xp);
                if result.levels_gained > 0 {
                    let _ = write!(
                        extra,
                        " Twoja umiejętność {} wzrosła o {} poziom(ów)!",
                        key, result.levels_gained
                    );
                }
            }
        }

        if let Err(e) =
            vallheru_data::queries::player::save_skills(&app.pool, player_id, &skills).await
        {
            tracing::error!(error = %e, "apply_gathering_xp: save_skills failed");
        }
    }

    // Apply HP change from condition level-ups.
    if hp_change > 0 {
        if let Err(e) =
            vallheru_data::queries::locations::add_player_hp(&app.pool, player_id, hp_change).await
        {
            tracing::error!(error = %e, "apply_gathering_xp: add_player_hp failed");
        }
    }

    extra
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

fn build_wood_options(license_level: i32) -> Vec<WoodOption> {
    let mut opts = Vec::new();
    if license_level >= 1 {
        opts.push(WoodOption {
            key: "pine".to_owned(),
            label: "Sosna".to_owned(),
        });
    }
    if license_level >= 2 {
        opts.push(WoodOption {
            key: "hazel".to_owned(),
            label: "Leszczyna".to_owned(),
        });
    }
    if license_level >= 3 {
        opts.push(WoodOption {
            key: "yew".to_owned(),
            label: "Cis".to_owned(),
        });
    }
    if license_level >= 4 {
        opts.push(WoodOption {
            key: "elm".to_owned(),
            label: "Wiąz".to_owned(),
        });
    }
    opts
}

fn build_smelt_actions(smelter_level: i32) -> Vec<SmeltAction> {
    let all = [
        ("copper", "Wytapiaj miedź (2 rudy miedzi + 1 węgiel)"),
        (
            "bronze",
            "Wytapiaj brąz (1 ruda miedzi + 1 ruda cyny + 2 węgle)",
        ),
        (
            "brass",
            "Wytapiaj mosiądz (2 rudy miedzi + 1 ruda cynku + 2 węgle)",
        ),
        ("iron", "Wytapiaj żelazo (2 rudy żelaza + 3 węgle)"),
        ("steel", "Wytapiaj stal (3 rudy żelaza + 7 węgli)"),
    ];

    #[allow(clippy::cast_sign_loss)]
    let level = smelter_level as usize;
    all.iter()
        .take(level)
        .map(|&(key, label)| SmeltAction {
            key: key.to_owned(),
            label: label.to_owned(),
        })
        .collect()
}

fn get_mineral_field(
    minerals: &vallheru_data::queries::gathering::MineralsRow,
    column: &str,
) -> i32 {
    match column {
        "copperore" => minerals.copperore,
        "zincore" => minerals.zincore,
        "tinore" => minerals.tinore,
        "ironore" => minerals.ironore,
        "coal" => minerals.coal,
        "copper" => minerals.copper,
        "bronze" => minerals.bronze,
        "brass" => minerals.brass,
        "iron" => minerals.iron,
        "steel" => minerals.steel,
        "pine" => minerals.pine,
        "hazel" => minerals.hazel,
        "yew" => minerals.yew,
        "elm" => minerals.elm,
        "crystal" => minerals.crystal,
        "adamantium" => minerals.adamantium,
        "meteor" => minerals.meteor,
        _ => 0,
    }
}

fn format_growth_stage(stage: gathering::GrowthStage) -> String {
    match stage {
        gathering::GrowthStage::Seeded => "zasiana".to_owned(),
        gathering::GrowthStage::Seedling => "sadzonka".to_owned(),
        gathering::GrowthStage::YoungPlant => "młoda roślina".to_owned(),
        gathering::GrowthStage::Blooming => "rozkwita".to_owned(),
        gathering::GrowthStage::ReadyToHarvest => "gotowa do zebrania".to_owned(),
        gathering::GrowthStage::Wilting => "przekwita".to_owned(),
        gathering::GrowthStage::Withered => "zwiędła".to_owned(),
    }
}
