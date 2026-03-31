//! Smithy (blacksmith) workshop handler.
//!
//! Ported from `kowal.php`. Covers plan purchase, normal crafting, and
//! work-in-progress continuation.

use axum::{Extension, Form, extract::Path, extract::State, response::Response};
use rand::Rng;

use crate::middleware::context::RequestContext;
use crate::page::{Flash, FlashKind, PageMeta};
use crate::state::AppState;
use vallheru_domain::crafting::smithing;

// =========================================================================
// View models
// =========================================================================

#[derive(serde::Serialize)]
pub struct SmithyMainView {
    #[serde(flatten)]
    pub base: crate::render::RenderContext,
}

#[derive(serde::Serialize)]
pub struct SmithyPlansView {
    #[serde(flatten)]
    pub base: crate::render::RenderContext,
    pub plans: Vec<PlanEntry>,
    pub owned: Vec<PlanEntry>,
}

#[derive(serde::Serialize)]
pub struct PlanEntry {
    pub id: i32,
    pub name: String,
    pub item_type: String,
    pub cost: i32,
    pub level: i16,
    pub owned: bool,
}

#[derive(serde::Serialize)]
pub struct SmithyWorkshopView {
    #[serde(flatten)]
    pub base: crate::render::RenderContext,
    pub plans: Vec<PlanEntry>,
    pub works: Vec<WorkEntry>,
    pub energy: i32,
    pub minerals: MineralStock,
}

#[derive(serde::Serialize, Default)]
pub struct MineralStock {
    pub copper: i32,
    pub bronze: i32,
    pub brass: i32,
    pub iron: i32,
    pub steel: i32,
}

#[derive(serde::Serialize)]
pub struct WorkEntry {
    pub id: i32,
    pub name: String,
    pub n_energy: i16,
    pub u_energy: i16,
    pub mineral: String,
    pub remaining: i16,
}

#[derive(serde::Deserialize)]
pub struct CraftForm {
    pub plan_id: Option<i32>,
    pub mineral: Option<String>,
    pub amount: Option<i32>,
}

#[derive(serde::Deserialize)]
pub struct ContinueForm {
    pub work_id: Option<i32>,
    pub amount: Option<i32>,
}

// =========================================================================
// Private helpers
// =========================================================================

#[derive(Debug, Clone, sqlx::FromRow)]
#[allow(dead_code)]
struct PlayerRow {
    pub location: String,
    pub hp: i32,
    pub energy: f64,
    pub credits: i64,
    pub platinum: i32,
    pub clas: String,
    pub race: String,
}

async fn load_player(app: &AppState, player_id: i32) -> Result<PlayerRow, Response> {
    sqlx::query_as::<_, PlayerRow>(
        "SELECT location, hp, energy, credits, platinum, clas, race FROM players WHERE id = $1",
    )
    .bind(player_id)
    .fetch_optional(&app.pool)
    .await
    .map_err(|_| server_error())?
    .ok_or_else(server_error)
}

async fn load_skill(app: &AppState, player_id: i32, key: &str) -> f64 {
    let row: Option<(f64,)> =
        sqlx::query_as("SELECT level FROM player_skills WHERE player_id = $1 AND skill_key = $2")
            .bind(player_id)
            .bind(key)
            .fetch_optional(&app.pool)
            .await
            .unwrap_or(None);
    row.map_or(0.0, |r| r.0)
}

async fn load_stat(app: &AppState, player_id: i32, key: &str) -> f64 {
    let row: Option<(f64,)> =
        sqlx::query_as("SELECT level FROM player_stats WHERE player_id = $1 AND stat_key = $2")
            .bind(player_id)
            .bind(key)
            .fetch_optional(&app.pool)
            .await
            .unwrap_or(None);
    row.map_or(0.0, |r| r.0)
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

// =========================================================================
// Handlers
// =========================================================================

/// GET /smithy — main smithy page with navigation.
pub async fn smithy_show(
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

    if player_row.location != "Miasto" && player_row.location != "Altara" {
        return error_page(&app, &ctx, "Musisz znajdować się w mieście.");
    }

    let meta = PageMeta::titled("Kowal").with_back_link("/city", "Wróć do miasta");
    let base = app.templates.build_context(&ctx, &meta);
    let view = SmithyMainView { base };
    app.templates.render_value("smithy.html", &view)
}

/// GET /smithy/plans — show plans available for purchase.
pub async fn smithy_plans_show(
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

    if player_row.location != "Miasto" && player_row.location != "Altara" {
        return error_page(&app, &ctx, "Musisz znajdować się w mieście.");
    }

    let smith_skill = load_skill(&app, player_id, "smith").await;

    let catalog = vallheru_data::queries::crafting::smith_catalog(&app.pool, None)
        .await
        .unwrap_or_default();

    let owned = vallheru_data::queries::crafting::smith_player_plans(&app.pool, player_id)
        .await
        .unwrap_or_default();

    let owned_names: Vec<&str> = owned.iter().map(|p| p.name.as_str()).collect();

    let plans: Vec<PlanEntry> = catalog
        .iter()
        .filter(|p| f64::from(p.level) <= smith_skill)
        .map(|p| PlanEntry {
            id: p.id,
            name: p.name.clone(),
            item_type: p.item_type.clone(),
            cost: p.cost,
            level: p.level,
            owned: owned_names.contains(&p.name.as_str()),
        })
        .collect();

    let owned_entries: Vec<PlanEntry> = owned
        .iter()
        .map(|p| PlanEntry {
            id: p.id,
            name: p.name.clone(),
            item_type: p.item_type.clone(),
            cost: p.cost,
            level: p.level,
            owned: true,
        })
        .collect();

    let meta = PageMeta::titled("Kowal - Plany").with_back_link("/smithy", "Wróć do kowala");
    let base = app.templates.build_context(&ctx, &meta);
    let view = SmithyPlansView {
        base,
        plans,
        owned: owned_entries,
    };
    app.templates.render_value("smithy_plans.html", &view)
}

/// POST /smithy/plans/buy/:id — buy a plan.
pub async fn smithy_plan_buy(
    State(app): State<AppState>,
    Extension(ctx): Extension<RequestContext>,
    Path(plan_id): Path<i32>,
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

    if player_row.location != "Miasto" && player_row.location != "Altara" {
        return error_page(&app, &ctx, "Musisz znajdować się w mieście.");
    }

    let Ok(Some(plan)) =
        vallheru_data::queries::crafting::smith_find_plan(&app.pool, plan_id, 0).await
    else {
        return error_page(&app, &ctx, "Plan nie istnieje.");
    };

    let smith_skill = load_skill(&app, player_id, "smith").await;
    if smith_skill < f64::from(plan.level) {
        return error_page(&app, &ctx, "Twoja umiejętność kowalstwa jest zbyt niska.");
    }

    if player_row.credits < i64::from(plan.cost) {
        return error_page(&app, &ctx, "Nie masz wystarczająco złota.");
    }

    let already_owned =
        vallheru_data::queries::crafting::smith_player_has_plan(&app.pool, player_id, &plan.name)
            .await
            .unwrap_or(false);
    if already_owned {
        return error_page(&app, &ctx, "Już posiadasz ten plan.");
    }

    // Deduct gold
    if let Err(e) =
        vallheru_data::queries::gathering::deduct_currency(&app.pool, player_id, plan.cost, 0).await
    {
        tracing::error!(error = %e, "smithy plan buy: deduct gold");
        return server_error();
    }

    // Insert player plan copy
    if let Err(e) =
        vallheru_data::queries::crafting::smith_buy_plan(&app.pool, player_id, &plan).await
    {
        tracing::error!(error = %e, "smithy plan buy: insert plan");
        return server_error();
    }

    let msg = format!("Kupiłeś plan: {}", plan.name);
    let meta = PageMeta::titled("Kowal - Plany")
        .with_back_link("/smithy/plans", "Wróć do planów")
        .with_flash(Flash::success(msg));
    let base = app.templates.build_context(&ctx, &meta);
    app.templates.render("success.html", &base)
}

/// GET /smithy/workshop — show crafting workshop.
pub async fn smithy_workshop_show(
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

    if player_row.location != "Miasto" && player_row.location != "Altara" {
        return error_page(&app, &ctx, "Musisz znajdować się w mieście.");
    }

    let owned = vallheru_data::queries::crafting::smith_player_plans(&app.pool, player_id)
        .await
        .unwrap_or_default();

    let works = vallheru_data::queries::crafting::smith_active_works(&app.pool, player_id)
        .await
        .unwrap_or_default();

    let minerals = vallheru_data::queries::gathering::load_minerals(&app.pool, player_id)
        .await
        .ok()
        .flatten()
        .unwrap_or_default();

    let plan_entries: Vec<PlanEntry> = owned
        .iter()
        .filter(|p| p.elite == 0)
        .map(|p| PlanEntry {
            id: p.id,
            name: p.name.clone(),
            item_type: p.item_type.clone(),
            cost: p.cost,
            level: p.level,
            owned: true,
        })
        .collect();

    let work_entries: Vec<WorkEntry> = works
        .iter()
        .map(|w| WorkEntry {
            id: w.id,
            name: w.name.clone(),
            n_energy: w.n_energy,
            u_energy: w.u_energy,
            mineral: w.mineral.clone(),
            remaining: w.n_energy - w.u_energy,
        })
        .collect();

    #[allow(clippy::cast_possible_truncation)]
    let energy = player_row.energy as i32;

    let stock = MineralStock {
        copper: minerals.copper,
        bronze: minerals.bronze,
        brass: minerals.brass,
        iron: minerals.iron,
        steel: minerals.steel,
    };

    let meta = PageMeta::titled("Kowal - Kuźnia").with_back_link("/smithy", "Wróć do kowala");
    let base = app.templates.build_context(&ctx, &meta);
    let view = SmithyWorkshopView {
        base,
        plans: plan_entries,
        works: work_entries,
        energy,
        minerals: stock,
    };
    app.templates.render_value("smithy_workshop.html", &view)
}

/// POST /smithy/craft — start crafting an item.
#[allow(clippy::too_many_lines)]
pub async fn smithy_craft(
    State(app): State<AppState>,
    Extension(ctx): Extension<RequestContext>,
    Form(form): Form<CraftForm>,
) -> Response {
    struct CraftedItem {
        name: String,
        power: i32,
        agi: i32,
        dur: i32,
        speed: i32,
        is_special: bool,
    }

    let Some(ref user) = ctx.session_user else {
        return crate::page::redirect("/login");
    };

    #[allow(clippy::cast_possible_truncation)]
    let player_id = user.id as i32;

    let player_row = match load_player(&app, player_id).await {
        Ok(row) => row,
        Err(resp) => return resp,
    };

    if player_row.location != "Miasto" && player_row.location != "Altara" {
        return error_page(&app, &ctx, "Musisz znajdować się w mieście.");
    }

    let Some(plan_id) = form.plan_id else {
        return error_page(&app, &ctx, "Wybierz plan.");
    };

    let mineral_key = match form.mineral.as_deref() {
        Some(k) if !k.is_empty() => k,
        _ => return error_page(&app, &ctx, "Wybierz minerał."),
    };

    let amount = match form.amount {
        Some(a) if a > 0 => a,
        _ => return error_page(&app, &ctx, "Podaj ilość."),
    };

    // Load plan
    let Ok(Some(plan)) =
        vallheru_data::queries::crafting::smith_find_plan(&app.pool, plan_id, player_id).await
    else {
        return error_page(&app, &ctx, "Nie posiadasz tego planu.");
    };

    let Some(item_type) = smithing::SmithItemType::from_db(&plan.item_type) else {
        return error_page(&app, &ctx, "Nieprawidłowy typ przedmiotu.");
    };

    let Some(mineral) = smithing::Mineral::from_key(mineral_key) else {
        return error_page(&app, &ctx, "Nieprawidłowy minerał.");
    };

    // Check energy
    let energy_per = i32::from(plan.level);
    let total_energy = energy_per * amount;
    #[allow(clippy::cast_possible_truncation)]
    let avail_energy = player_row.energy as i32;

    if total_energy > avail_energy {
        return error_page(&app, &ctx, "Nie masz wystarczająco energii.");
    }

    // Check minerals
    let minerals = vallheru_data::queries::gathering::load_minerals(&app.pool, player_id)
        .await
        .ok()
        .flatten()
        .unwrap_or_default();

    let mineral_stock = match mineral {
        smithing::Mineral::Copper => minerals.copper,
        smithing::Mineral::Bronze => minerals.bronze,
        smithing::Mineral::Brass => minerals.brass,
        smithing::Mineral::Iron => minerals.iron,
        smithing::Mineral::Steel => minerals.steel,
    };

    let mineral_per = plan.amount;
    let total_mineral = mineral_per * amount;

    if mineral_stock < total_mineral {
        return error_page(
            &app,
            &ctx,
            &format!(
                "Nie masz wystarczająco {mineral_key}. Potrzebujesz {total_mineral}, masz {mineral_stock}."
            ),
        );
    }

    // Calculate crafting results
    let smith_skill = load_skill(&app, player_id, "smith").await;
    let strength = load_stat(&app, player_id, "strength").await;
    let intelligence = load_stat(&app, player_id, "inteli").await;
    let agility_stat = load_stat(&app, player_id, "agility").await;

    let is_craftsman = player_row.clas == "Rzemieślnik";

    let chance = if item_type == smithing::SmithItemType::Tool {
        smithing::normal_tool_success_chance(smith_skill, i32::from(plan.level), mineral)
    } else {
        smithing::normal_success_chance(smith_skill, i32::from(plan.level), mineral)
    };

    let base_stats =
        smithing::normal_base_stats(item_type, i32::from(plan.level), mineral, plan.cost);

    // Craft items — collect results first (rng is !Send, must not cross .await)

    let (items, total_xp) = {
        let mut rng = rand::thread_rng();
        let mut crafted = Vec::new();
        let mut _special_count = 0i32;

        for _ in 0..amount {
            let roll: i32 = rng.gen_range(1..=100);
            if roll <= chance {
                let quality_roll2: i32 = rng.gen_range(1..=100);
                let quality_roll3: i32 = rng.gen_range(1..=101);
                let quality = smithing::special_quality_roll(
                    quality_roll2,
                    quality_roll3,
                    is_craftsman,
                    item_type,
                );

                let (item_name, power, agi, dur, speed, is_special) = if let Some(q) = quality {
                    _special_count += 1;
                    #[allow(clippy::cast_possible_truncation)]
                    let item_bonus_roll = rng.gen_range(1..=(smith_skill.ceil() as i32).max(1));
                    let inputs = smithing::SpecialBonusInputs {
                        item_bonus_roll,
                        strength_stat: strength,
                        intelligence_stat: intelligence,
                        agility_stat,
                    };
                    let bonus = smithing::compute_special_bonus(
                        q,
                        item_type,
                        &base_stats,
                        mineral,
                        &inputs,
                    );
                    let prefix = match q {
                        smithing::SpecialQuality::Dragon => "Smoczy",
                        smithing::SpecialQuality::Dwarven => "Krasnoludzki",
                        smithing::SpecialQuality::Elven => "Elfi",
                        smithing::SpecialQuality::DragonDwarven => "Smoczo-Krasnoludzki",
                        smithing::SpecialQuality::ElvenDwarven => "Elficko-Krasnoludzki",
                    };
                    let name = format!("{prefix} {}", plan.name);
                    (
                        name,
                        base_stats.power + bonus.power_bonus,
                        (base_stats.agility - bonus.agility_bonus).max(0),
                        base_stats.durability + bonus.durability_bonus,
                        0,
                        true,
                    )
                } else {
                    (
                        plan.name.clone(),
                        base_stats.power,
                        base_stats.agility,
                        base_stats.durability,
                        0,
                        false,
                    )
                };

                crafted.push(CraftedItem {
                    name: item_name,
                    power,
                    agi,
                    dur,
                    speed,
                    is_special,
                });
            }
        }

        #[allow(clippy::cast_possible_wrap, clippy::cast_possible_truncation)]
        let success_count = crafted.len() as i32;
        let mut xp = success_count * i32::from(plan.level) * 2;
        if is_craftsman {
            xp *= 2;
        }
        (crafted, xp)
    };
    // rng is dropped here — safe to .await below

    #[allow(clippy::cast_possible_wrap, clippy::cast_possible_truncation)]
    let success_count = items.len() as i32;
    let two_handed = plan.twohand == "Y";
    for item in &items {
        if let Err(e) = vallheru_data::queries::crafting::insert_crafted_item(
            &app.pool,
            player_id,
            &item.name,
            item.power,
            &plan.item_type,
            i64::from(base_stats.sell_cost),
            i32::from(plan.level),
            item.agi,
            item.dur,
            item.speed,
            item.dur,
            two_handed,
            base_stats.repair_cost,
        )
        .await
        {
            tracing::error!(error = %e, "smithy_craft: insert item");
        }
    }

    let mut results_text = format!("Wytworzono {success_count}/{amount} przedmiotów.");

    // Deduct energy and minerals
    if let Err(e) = vallheru_data::queries::gathering::deduct_energy(
        &app.pool,
        player_id,
        f64::from(total_energy),
    )
    .await
    {
        tracing::error!(error = %e, "smithy_craft: deduct energy");
        return server_error();
    }

    if let Err(e) = vallheru_data::queries::gathering::add_minerals(
        &app.pool,
        player_id,
        &[(mineral_key, -total_mineral)],
    )
    .await
    {
        tracing::error!(error = %e, "smithy_craft: deduct minerals");
        return server_error();
    }

    // Apply XP
    if total_xp > 0 {
        let xp_text = apply_craft_xp(
            &app,
            player_id,
            &player_row.race,
            &player_row.clas,
            total_xp,
            "smith",
        )
        .await;
        results_text.push_str(&xp_text);
    }

    let special_count = items.iter().filter(|i| i.is_special).count();
    let msg = format!(
        "Wykuto {success_count}/{amount} przedmiotów (w tym {special_count} specjalnych). PD: {total_xp}.{results_text}"
    );

    let meta = PageMeta::titled("Kowal - Kuźnia")
        .with_back_link("/smithy/workshop", "Wróć do kuźni")
        .with_flash(Flash::success(msg));
    let base = app.templates.build_context(&ctx, &meta);
    app.templates.render("success.html", &base)
}

/// POST /smithy/continue — continue work on a partial item.
pub async fn smithy_continue(
    State(app): State<AppState>,
    Extension(ctx): Extension<RequestContext>,
    Form(form): Form<ContinueForm>,
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

    if player_row.location != "Miasto" && player_row.location != "Altara" {
        return error_page(&app, &ctx, "Musisz znajdować się w mieście.");
    }

    let Some(work_id) = form.work_id else {
        return error_page(&app, &ctx, "Wybierz pracę.");
    };

    let amount = match form.amount {
        Some(a) if a > 0 => a,
        _ => return error_page(&app, &ctx, "Podaj ilość energii."),
    };

    let Ok(Some(work)) =
        vallheru_data::queries::crafting::smith_find_work(&app.pool, work_id, player_id).await
    else {
        return error_page(&app, &ctx, "Nie masz takiej pracy w toku.");
    };

    #[allow(clippy::cast_possible_truncation)]
    let avail = player_row.energy as i32;
    if amount > avail {
        return error_page(&app, &ctx, "Nie masz tyle energii!");
    }

    let remaining = work.n_energy - work.u_energy;
    let energy_to_add = amount.min(i32::from(remaining));

    // Deduct energy
    if let Err(e) = vallheru_data::queries::gathering::deduct_energy(
        &app.pool,
        player_id,
        f64::from(energy_to_add),
    )
    .await
    {
        tracing::error!(error = %e, "smithy_continue: deduct energy");
        return server_error();
    }

    // Add work energy
    #[allow(clippy::cast_possible_truncation)]
    let energy_i16 = energy_to_add as i16;
    if let Err(e) =
        vallheru_data::queries::crafting::smith_add_work_energy(&app.pool, work_id, energy_i16)
            .await
    {
        tracing::error!(error = %e, "smithy_continue: add work energy");
        return server_error();
    }

    let new_used = work.u_energy + energy_i16;
    let msg = if new_used >= work.n_energy {
        // Work complete — finalize
        if let Err(e) =
            vallheru_data::queries::crafting::smith_delete_work(&app.pool, work_id).await
        {
            tracing::error!(error = %e, "smithy_continue: delete work");
        }
        format!("Praca nad {} została ukończona!", work.name)
    } else {
        format!(
            "Kontynuujesz pracę nad {}. Postęp: {}/{}.",
            work.name, new_used, work.n_energy
        )
    };

    let meta = PageMeta::titled("Kowal - Kuźnia")
        .with_back_link("/smithy/workshop", "Wróć do kuźni")
        .with_flash(Flash::success(msg));
    let base = app.templates.build_context(&ctx, &meta);
    app.templates.render("success.html", &base)
}

// =========================================================================
// XP helper (shared with other crafting handlers)
// =========================================================================

pub(crate) async fn apply_craft_xp(
    app: &AppState,
    player_id: i32,
    race: &str,
    clas: &str,
    xp_amount: i32,
    skill_key: &str,
) -> String {
    use std::fmt::Write;
    use vallheru_domain::player::progression;

    let race = race.to_owned();
    let class = clas.to_owned();
    let mut extra = String::new();

    // Apply skill XP
    let mut skills = vallheru_data::queries::player::load_skills(&app.pool, player_id)
        .await
        .unwrap_or_default();

    let skill_xp = xp_amount / 2;
    let stat_xp = xp_amount - skill_xp;

    if let Some(skill) = skills.iter_mut().find(|s| s.skill_key == skill_key) {
        let result = progression::apply_skill_xp(skill, skill_xp);
        if result.levels_gained > 0 {
            let _ = write!(
                extra,
                " Umiejętność {} wzrosła o {} poziom(ów)!",
                skill_key, result.levels_gained
            );
        }
    }

    if let Err(e) = vallheru_data::queries::player::save_skills(&app.pool, player_id, &skills).await
    {
        tracing::error!(error = %e, "apply_craft_xp: save_skills");
    }

    // Apply stat XP to strength
    if stat_xp > 0 {
        let mut stats = vallheru_data::queries::player::load_stats(&app.pool, player_id)
            .await
            .unwrap_or_default();

        if let Some(stat) = stats.iter_mut().find(|s| s.stat_key == "strength") {
            let parsed_race = vallheru_domain::player::Race::from_db(&race)
                .unwrap_or(vallheru_domain::player::Race::Human);
            let parsed_class = vallheru_domain::player::Class::from_db(&class)
                .unwrap_or(vallheru_domain::player::Class::Warrior);
            let result = progression::apply_stat_xp(stat, stat_xp, &parsed_race, &parsed_class);
            if result.levels_gained > 0 {
                let _ = write!(
                    extra,
                    " Siła wzrosła o {} poziom(ów)!",
                    result.levels_gained
                );
            }
            if result.hp_change > 0 {
                let _ = vallheru_data::queries::locations::add_player_hp(
                    &app.pool,
                    player_id,
                    result.hp_change,
                )
                .await;
            }
        }

        if let Err(e) =
            vallheru_data::queries::player::save_stats(&app.pool, player_id, &stats).await
        {
            tracing::error!(error = %e, "apply_craft_xp: save_stats");
        }
    }

    extra
}
