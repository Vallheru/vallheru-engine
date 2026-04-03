//! Jeweller + jeweller shop handler.
//!
//! Ported from `jeweller.php` and `jewellershop.php`. Covers plan purchase,
//! simple ring crafting, stat-bonus ring crafting, and the NPC ring shop.

use axum::{Extension, Form, extract::Path, extract::State, response::Response};
use rand::Rng;

use crate::log_err;
use crate::middleware::context::RequestContext;
use crate::page::{Flash, FlashKind, PageMeta};
use crate::state::AppState;
use vallheru_domain::crafting::jeweller as jdomain;

// =========================================================================
// View models
// =========================================================================

#[derive(serde::Serialize)]
pub struct JewellerMainView {
    #[serde(flatten)]
    pub base: crate::render::RenderContext,
}

#[derive(serde::Serialize)]
pub struct JewellerPlansView {
    #[serde(flatten)]
    pub base: crate::render::RenderContext,
    pub plans: Vec<PlanEntry>,
    pub owned: Vec<PlanEntry>,
}

#[derive(serde::Serialize)]
pub struct PlanEntry {
    pub id: i32,
    pub name: String,
    pub cost: i32,
    pub level: i16,
    pub owned: bool,
}

#[derive(serde::Serialize)]
pub struct JewellerWorkshopView {
    #[serde(flatten)]
    pub base: crate::render::RenderContext,
    pub plans: Vec<PlanEntry>,
    pub works: Vec<WorkEntry>,
    pub energy: i32,
    pub adamantium: i32,
    pub crystal: i32,
    pub meteor: i32,
}

#[derive(serde::Serialize)]
pub struct WorkEntry {
    pub id: i32,
    pub name: String,
    pub energy_spent: i32,
    pub energy_needed: i32,
}

#[derive(serde::Serialize)]
pub struct JewellerShopView {
    #[serde(flatten)]
    pub base: crate::render::RenderContext,
    pub rings: Vec<ShopRingEntry>,
    pub gold: i64,
}

#[derive(serde::Serialize)]
pub struct ShopRingEntry {
    pub id: i32,
    pub name: String,
    pub amount: i32,
    pub cost: i32,
}

#[derive(serde::Deserialize)]
pub struct CraftForm {
    pub plan_id: Option<i32>,
    pub energy: Option<i32>,
    pub stat: Option<String>,
}

#[derive(serde::Deserialize)]
pub struct ContinueForm {
    pub work_id: Option<i32>,
    pub energy: Option<i32>,
}

// =========================================================================
// Private helpers
// =========================================================================

#[derive(Debug, Clone, sqlx::FromRow)]
struct PlayerRow {
    pub location: String,
    pub energy: f64,
    pub credits: i64,
    pub class: String,
    pub race: String,
}

async fn load_player(app: &AppState, player_id: i32) -> Result<PlayerRow, Response> {
    sqlx::query_as::<_, PlayerRow>(
        "SELECT location, energy, credits, class, race FROM players WHERE id = $1",
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
            .unwrap_or_else(|e| {
                tracing::error!(error = %e, player_id, skill = key, "load_skill query failed");
                None
            });
    row.map_or(0.0, |r| r.0)
}

async fn load_stat(app: &AppState, player_id: i32, key: &str) -> f64 {
    let row: Option<(f64,)> =
        sqlx::query_as("SELECT level FROM player_stats WHERE player_id = $1 AND stat_key = $2")
            .bind(player_id)
            .bind(key)
            .fetch_optional(&app.pool)
            .await
            .unwrap_or_else(|e| {
                tracing::error!(error = %e, player_id, stat = key, "load_stat query failed");
                None
            });
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
// Handlers — jeweller main
// =========================================================================

/// GET /jeweller — main jeweller page.
pub async fn jeweller_show(
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

    let meta = PageMeta::titled("Jubiler").with_back_link("/city", "Wróć do miasta");
    let base = app.templates.build_context(&ctx, &meta);
    let view = JewellerMainView { base };
    app.templates.render_value("jeweller.html", &view)
}

/// GET /jeweller/plans — show plans for purchase.
pub async fn jeweller_plans_show(
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

    let catalog = match vallheru_data::queries::crafting::jeweller_catalog(&app.pool).await {
        Ok(v) => v,
        Err(e) => {
            tracing::error!(error = %e, "jeweller_catalog query failed");
            Vec::new()
        }
    };

    let owned = match vallheru_data::queries::crafting::jeweller_player_plans(&app.pool, player_id)
        .await
    {
        Ok(v) => v,
        Err(e) => {
            tracing::error!(error = %e, player_id, "jeweller_player_plans query failed");
            Vec::new()
        }
    };

    let owned_names: Vec<&str> = owned.iter().map(|p| p.name.as_str()).collect();
    let jewellery_skill = load_skill(&app, player_id, "jewellry").await;

    let plans: Vec<PlanEntry> = catalog
        .iter()
        .filter(|p| f64::from(p.level) <= jewellery_skill)
        .map(|p| PlanEntry {
            id: p.id,
            name: p.name.clone(),
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
            cost: p.cost,
            level: p.level,
            owned: true,
        })
        .collect();

    let meta = PageMeta::titled("Jubiler - Plany").with_back_link("/jeweller", "Wróć do jubilera");
    let base = app.templates.build_context(&ctx, &meta);
    let view = JewellerPlansView {
        base,
        plans,
        owned: owned_entries,
    };
    app.templates.render_value("jeweller_plans.html", &view)
}

/// POST /jeweller/plans/buy/:id — buy a plan.
pub async fn jeweller_plan_buy(
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
        vallheru_data::queries::crafting::jeweller_find_plan(&app.pool, plan_id, 0).await
    else {
        return error_page(&app, &ctx, "Plan nie istnieje.");
    };

    let jewellery_skill = load_skill(&app, player_id, "jewellry").await;

    #[allow(clippy::cast_possible_truncation)]
    let skill_int = jewellery_skill as i32;
    #[allow(clippy::cast_possible_truncation)]
    let gold_int = player_row.credits as i32;

    let already = vallheru_data::queries::crafting::jeweller_player_has_plan(
        &app.pool, player_id, &plan.name,
    )
    .await
    .unwrap_or_else(|e| {
        tracing::error!(error = %e, player_id, "jeweller_player_has_plan query failed");
        false
    });

    let is_craftsman = player_row.class == "Rzemieślnik";

    if let Err(e) = jdomain::can_buy_plan(
        plan_id,
        is_craftsman,
        already,
        plan.cost,
        i32::from(plan.level),
        gold_int,
        skill_int,
    ) {
        return error_page(&app, &ctx, &e.to_string());
    }

    // Deduct gold
    if let Err(e) =
        vallheru_data::queries::gathering::deduct_currency(&app.pool, player_id, plan.cost, 0).await
    {
        tracing::error!(error = %e, "jeweller plan buy: deduct gold");
        return server_error();
    }

    // Insert player plan copy
    if let Err(e) =
        vallheru_data::queries::crafting::jeweller_buy_plan(&app.pool, player_id, &plan).await
    {
        tracing::error!(error = %e, "jeweller plan buy: insert plan");
        return server_error();
    }

    let msg = format!("Kupiłeś plan: {}", plan.name);
    let meta = PageMeta::titled("Jubiler - Plany")
        .with_back_link("/jeweller/plans", "Wróć do planów")
        .with_flash(Flash::success(msg));
    let base = app.templates.build_context(&ctx, &meta);
    app.templates.render("success.html", &base)
}

/// GET /jeweller/workshop — show crafting workshop.
pub async fn jeweller_workshop_show(
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

    let owned = match vallheru_data::queries::crafting::jeweller_player_plans(&app.pool, player_id)
        .await
    {
        Ok(v) => v,
        Err(e) => {
            tracing::error!(error = %e, player_id, "jeweller_player_plans query failed");
            Vec::new()
        }
    };

    let plans: Vec<PlanEntry> = owned
        .iter()
        .map(|p| PlanEntry {
            id: p.id,
            name: p.name.clone(),
            cost: p.cost,
            level: p.level,
            owned: true,
        })
        .collect();

    let active_works =
        match vallheru_data::queries::crafting::jeweller_active_works(&app.pool, player_id).await {
            Ok(v) => v,
            Err(e) => {
                tracing::error!(player_id, error = ?e, "failed to load jeweller active works");
                Vec::new()
            }
        };

    #[allow(clippy::cast_possible_truncation)]
    let works: Vec<WorkEntry> = active_works
        .iter()
        .map(|w| WorkEntry {
            id: w.id,
            name: w.name.clone(),
            energy_spent: w.u_energy as i32,
            energy_needed: w.n_energy as i32,
        })
        .collect();

    let minerals =
        match vallheru_data::queries::gathering::load_minerals(&app.pool, player_id).await {
            Ok(Some(m)) => m,
            Ok(None) => vallheru_data::queries::gathering::MineralsRow::default(),
            Err(e) => {
                tracing::error!(player_id, error = ?e, "failed to load minerals for jeweller");
                vallheru_data::queries::gathering::MineralsRow::default()
            }
        };

    #[allow(clippy::cast_possible_truncation)]
    let energy = player_row.energy as i32;

    let meta =
        PageMeta::titled("Jubiler - Pracownia").with_back_link("/jeweller", "Wróć do jubilera");
    let base = app.templates.build_context(&ctx, &meta);
    let view = JewellerWorkshopView {
        base,
        plans,
        works,
        energy,
        adamantium: minerals.adamantium,
        crystal: minerals.crystal,
        meteor: minerals.meteor,
    };
    app.templates.render_value("jeweller_workshop.html", &view)
}

/// POST /jeweller/craft — craft rings.
#[allow(clippy::too_many_lines)]
pub async fn jeweller_craft(
    State(app): State<AppState>,
    Extension(ctx): Extension<RequestContext>,
    Form(form): Form<CraftForm>,
) -> Response {
    struct CraftedRing {
        name: String,
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

    let energy_spend = match form.energy {
        Some(e) if e > 0 => e,
        _ => return error_page(&app, &ctx, "Podaj energię."),
    };

    let Ok(Some(plan)) =
        vallheru_data::queries::crafting::jeweller_find_plan(&app.pool, plan_id, player_id).await
    else {
        return error_page(&app, &ctx, "Nie posiadasz tego planu.");
    };

    #[allow(clippy::cast_possible_truncation)]
    let avail_energy = player_row.energy as i32;
    if energy_spend > avail_energy {
        return error_page(&app, &ctx, "Nie masz wystarczająco energii.");
    }

    let plan_level = i32::from(plan.level);
    let jewellery_skill = load_skill(&app, player_id, "jewellry").await;
    let agility = load_stat(&app, player_id, "agility").await;
    let is_craftsman = player_row.class == "Rzemieślnik";

    // Check mineral costs
    let minerals = match vallheru_data::queries::gathering::load_minerals(&app.pool, player_id)
        .await
    {
        Ok(Some(m)) => m,
        Ok(None) => vallheru_data::queries::gathering::MineralsRow::default(),
        Err(e) => {
            tracing::error!(player_id, error = ?e, "failed to load minerals for jeweller craft");
            vallheru_data::queries::gathering::MineralsRow::default()
        }
    };

    let (adam_per, cryst_per, meteor_per) = jdomain::stat_ring_mineral_cost(plan_level);
    let batch = jdomain::stat_ring_batch_size(energy_spend, plan_level);

    let (total_adam, total_cryst, total_meteor) = if batch > 0 {
        (adam_per * batch, cryst_per * batch, meteor_per * batch)
    } else {
        (adam_per, cryst_per, meteor_per)
    };

    if minerals.adamantium < total_adam
        || minerals.crystal < total_cryst
        || minerals.meteor < total_meteor
    {
        return error_page(&app, &ctx, "Nie masz wystarczająco minerałów.");
    }

    // Deduct energy
    if let Err(e) = vallheru_data::queries::gathering::deduct_energy(
        &app.pool,
        player_id,
        f64::from(energy_spend),
    )
    .await
    {
        tracing::error!(error = %e, "jeweller_craft: deduct energy");
        return server_error();
    }

    // Deduct minerals
    if let Err(e) = vallheru_data::queries::gathering::add_minerals(
        &app.pool,
        player_id,
        &[
            ("adamantium", -total_adam),
            ("crystal", -total_cryst),
            ("meteor", -total_meteor),
        ],
    )
    .await
    {
        tracing::error!(error = %e, "jeweller_craft: deduct minerals");
        return server_error();
    }

    // Craft rings — collect results first (rng is !Send, must not cross .await)

    let (crafted_rings, total_xp, mut results_text, partial_work) = {
        let mut rng = rand::thread_rng();

        if batch > 0 {
            let chance = jdomain::stat_ring_chance(jewellery_skill, agility, plan_level);
            let can_choose = jdomain::can_choose_stat(jewellery_skill, plan_level);

            let chosen_stat = if can_choose {
                form.stat
                    .as_deref()
                    .and_then(parse_ring_stat)
                    .unwrap_or(jdomain::RingStat::Strength)
            } else {
                jdomain::RingStat::ALL[rng.gen_range(0..6)]
            };

            let max_bonus = plan_level * 2;
            let mut rings = Vec::new();

            for _ in 0..batch {
                let roll = rng.gen_range(1..=100);
                if roll <= chance {
                    let roll_skill = rng.gen_range(0.0..=jewellery_skill);
                    let bonus = jdomain::stat_ring_bonus(roll_skill, agility, max_bonus);
                    let ring_name = format!("Pierścień {} +{}", chosen_stat.polish_name(), bonus);
                    rings.push(CraftedRing { name: ring_name });
                }
            }

            #[allow(clippy::cast_possible_wrap, clippy::cast_possible_truncation)]
            let successes = rings.len() as i32;
            let mut xp = jdomain::stat_ring_xp(plan_level, successes);
            if is_craftsman {
                xp *= 2;
            }
            let text = format!("Wytworzono {successes}/{batch} pierścieni.");
            (rings, xp, text, false)
        } else {
            let xp = jdomain::STAT_RING_FAIL_XP;
            let text = "Rozpoczęto pracę nad pierścieniem.".to_string();
            (Vec::new(), xp, text, true)
        }
    };
    // rng is dropped here — safe to .await below

    // Insert crafted rings
    for ring in &crafted_rings {
        if let Err(e) = vallheru_data::queries::crafting::insert_crafted_item(
            &app.pool,
            player_id,
            &ring.name,
            0,                     // power
            "R",                   // item_type
            0,                     // cost
            i32::from(plan.level), // min_level
            0,
            0,
            0,
            0,     // agi, dur, speed, max_dur
            false, // two_handed
            0,     // repair_cost
        )
        .await
        {
            tracing::error!(error = %e, "jeweller_craft: insert ring");
        }
    }

    // Create partial work if needed
    if partial_work {
        if let Err(e) = vallheru_data::queries::crafting::jeweller_create_work(
            &app.pool,
            player_id,
            &plan.name,
            f64::from(plan.level),   // n_energy (total needed)
            f64::from(energy_spend), // u_energy (spent so far)
            "",                      // bonus
            "stat",                  // work_type
        )
        .await
        {
            tracing::error!(error = %e, "jeweller_craft: create work");
            return server_error();
        }
    }

    // Apply XP
    if total_xp > 0 {
        let xp_text = super::smithy::apply_craft_xp(
            &app,
            player_id,
            &player_row.race,
            &player_row.class,
            total_xp,
            "jewellry",
        )
        .await;
        results_text.push_str(&xp_text);
    }

    let meta = PageMeta::titled("Jubiler - Pracownia")
        .with_back_link("/jeweller/workshop", "Wróć do pracowni")
        .with_flash(Flash::success(results_text));
    let base = app.templates.build_context(&ctx, &meta);
    app.templates.render("success.html", &base)
}

/// POST /jeweller/continue — continue work-in-progress.
#[allow(clippy::too_many_lines)]
pub async fn jeweller_continue(
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

    let energy_add = match form.energy {
        Some(e) if e > 0 => e,
        _ => return error_page(&app, &ctx, "Podaj energię."),
    };

    #[allow(clippy::cast_possible_truncation)]
    let avail_energy = player_row.energy as i32;
    if energy_add > avail_energy {
        return error_page(&app, &ctx, "Nie masz wystarczająco energii.");
    }

    let Ok(Some(work)) =
        vallheru_data::queries::crafting::jeweller_find_work(&app.pool, work_id, player_id).await
    else {
        return error_page(&app, &ctx, "Nie masz takiej pracy.");
    };

    // Deduct energy
    if let Err(e) = vallheru_data::queries::gathering::deduct_energy(
        &app.pool,
        player_id,
        f64::from(energy_add),
    )
    .await
    {
        tracing::error!(error = %e, "jeweller_continue: deduct energy");
        return server_error();
    }

    #[allow(clippy::cast_possible_truncation)]
    let new_energy = work.u_energy as i32 + energy_add;
    #[allow(clippy::cast_possible_truncation)]
    let plan_level = work.n_energy as i32;
    let jewellery_skill = load_skill(&app, player_id, "jewellry").await;
    let agility = load_stat(&app, player_id, "agility").await;
    let is_craftsman = player_row.class == "Rzemieślnik";

    let mut results_text;
    let mut total_xp;

    if new_energy >= plan_level {
        // Work complete — attempt craft (rng must not cross .await)
        let (ring_name_opt, xp, text) = {
            let mut rng = rand::thread_rng();
            let chance = jdomain::stat_ring_chance(jewellery_skill, agility, plan_level);
            let roll = rng.gen_range(1..=100);

            if roll <= chance {
                let chosen_stat = jdomain::RingStat::ALL[rng.gen_range(0..6)];
                let max_bonus = plan_level * 2;
                let roll_skill = rng.gen_range(0.0..=jewellery_skill);
                let bonus = jdomain::stat_ring_bonus(roll_skill, agility, max_bonus);
                let ring_name = format!("Pierścień {} +{}", chosen_stat.polish_name(), bonus);
                let xp = jdomain::stat_ring_xp(plan_level, 1);
                let text = format!("Wytworzono: {ring_name}");
                (Some(ring_name), xp, text)
            } else {
                (
                    None,
                    jdomain::STAT_RING_FAIL_XP,
                    "Praca nie powiodła się.".to_string(),
                )
            }
        };
        // rng is dropped — safe to .await

        if let Some(ref ring_name) = ring_name_opt {
            if let Err(e) = vallheru_data::queries::crafting::insert_crafted_item(
                &app.pool, player_id, ring_name, 0,          // power
                "R",        // item_type
                0,          // cost
                plan_level, // min_level
                0, 0, 0, 0,     // agi, dur, speed, max_dur
                false, // two_handed
                0,     // repair_cost
            )
            .await
            {
                tracing::error!(error = %e, "jeweller_continue: insert ring");
            }
        }

        total_xp = xp;
        results_text = text;

        // Delete work
        log_err!(
            vallheru_data::queries::crafting::jeweller_delete_work(&app.pool, work_id).await,
            "jeweller delete work"
        );
    } else {
        // Still in progress
        if let Err(e) = vallheru_data::queries::crafting::jeweller_add_work_energy(
            &app.pool,
            work_id,
            f64::from(energy_add),
        )
        .await
        {
            tracing::error!(error = %e, "jeweller_continue: add energy");
            return server_error();
        }
        total_xp = jdomain::STAT_RING_FAIL_XP;
        results_text = format!("Kontynuujesz pracę. Energia: {new_energy}/{plan_level}.");
    }

    if is_craftsman {
        total_xp *= 2;
    }

    if total_xp > 0 {
        let xp_text = super::smithy::apply_craft_xp(
            &app,
            player_id,
            &player_row.race,
            &player_row.class,
            total_xp,
            "jewellry",
        )
        .await;
        results_text.push_str(&xp_text);
    }

    let meta = PageMeta::titled("Jubiler - Pracownia")
        .with_back_link("/jeweller/workshop", "Wróć do pracowni")
        .with_flash(Flash::success(results_text));
    let base = app.templates.build_context(&ctx, &meta);
    app.templates.render("success.html", &base)
}

// =========================================================================
// Handlers — NPC ring shop
// =========================================================================

/// GET /jeweller/shop — show NPC ring shop.
pub async fn jeweller_shop_show(
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

    let ring_rows = match vallheru_data::queries::crafting::ring_shop_list(&app.pool).await {
        Ok(v) => v,
        Err(e) => {
            tracing::error!(error = %e, "ring_shop_list query failed");
            Vec::new()
        }
    };

    let rings: Vec<ShopRingEntry> = ring_rows
        .iter()
        .map(|r| ShopRingEntry {
            id: r.id,
            name: r.name.clone(),
            amount: r.amount,
            cost: jdomain::SHOP_RING_COST,
        })
        .collect();

    let meta = PageMeta::titled("Sklep Jubilerski").with_back_link("/jeweller", "Wróć do jubilera");
    let base = app.templates.build_context(&ctx, &meta);
    let view = JewellerShopView {
        base,
        rings,
        gold: player_row.credits,
    };
    app.templates.render_value("jeweller_shop.html", &view)
}

/// POST /jeweller/shop/buy/:id — buy a ring from NPC shop.
pub async fn jeweller_shop_buy(
    State(app): State<AppState>,
    Extension(ctx): Extension<RequestContext>,
    Path(ring_id): Path<i32>,
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

    let cost = jdomain::SHOP_RING_COST;
    if player_row.credits < i64::from(cost) {
        return error_page(&app, &ctx, "Nie masz wystarczająco złota.");
    }

    let Ok(Some(ring)) = vallheru_data::queries::crafting::ring_shop_find(&app.pool, ring_id).await
    else {
        return error_page(&app, &ctx, "Ten pierścień nie jest dostępny.");
    };

    if ring.amount <= 0 {
        return error_page(&app, &ctx, "Ten pierścień nie jest dostępny.");
    }

    // Deduct gold
    if let Err(e) =
        vallheru_data::queries::gathering::deduct_currency(&app.pool, player_id, cost, 0).await
    {
        tracing::error!(error = %e, "jeweller shop buy: deduct gold");
        return server_error();
    }

    // Decrement shop stock
    if let Err(e) = vallheru_data::queries::crafting::ring_shop_decrement(&app.pool, ring_id).await
    {
        tracing::error!(error = %e, "jeweller shop buy: decrement stock");
        return server_error();
    }

    // Add ring to player equipment
    if let Err(e) = vallheru_data::queries::crafting::insert_crafted_item(
        &app.pool,
        player_id,
        &ring.name,
        0,                    // power
        "R",                  // item_type
        i64::from(cost) / 10, // cost (sell value)
        0,                    // min_level
        0,
        0,
        0,
        0,     // agi, dur, speed, max_dur
        false, // two_handed
        0,     // repair_cost
    )
    .await
    {
        tracing::error!(error = %e, "jeweller shop buy: insert ring");
        return server_error();
    }

    let msg = format!("Kupiłeś pierścień: {}", ring.name);
    let meta = PageMeta::titled("Sklep Jubilerski")
        .with_back_link("/jeweller/shop", "Wróć do sklepu")
        .with_flash(Flash::success(msg));
    let base = app.templates.build_context(&ctx, &meta);
    app.templates.render("success.html", &base)
}

// =========================================================================
// Helper
// =========================================================================

fn parse_ring_stat(s: &str) -> Option<jdomain::RingStat> {
    match s {
        "agility" => Some(jdomain::RingStat::Agility),
        "strength" => Some(jdomain::RingStat::Strength),
        "inteli" => Some(jdomain::RingStat::Intelligence),
        "wisdom" => Some(jdomain::RingStat::Wisdom),
        "speed" => Some(jdomain::RingStat::Speed),
        "condition" => Some(jdomain::RingStat::Condition),
        _ => None,
    }
}
