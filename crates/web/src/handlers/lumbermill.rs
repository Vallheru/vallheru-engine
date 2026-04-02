//! Lumbermill (bow/arrow crafting) handler.
//!
//! Ported from `lumbermill.php`. Structurally very similar to smithy but
//! uses wood types (pine, hazel, yew, elm) and the `mill`/`mill_work` tables.

use axum::{Extension, Form, extract::Path, extract::State, response::Response};
use rand::Rng;

use crate::log_err;
use crate::middleware::context::RequestContext;
use crate::page::{Flash, FlashKind, PageMeta};
use crate::state::AppState;

// =========================================================================
// View models
// =========================================================================

#[derive(serde::Serialize)]
pub struct LumbermillMainView {
    #[serde(flatten)]
    pub base: crate::render::RenderContext,
}

#[derive(serde::Serialize)]
pub struct LumbermillPlansView {
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
pub struct LumbermillWorkshopView {
    #[serde(flatten)]
    pub base: crate::render::RenderContext,
    pub plans: Vec<PlanEntry>,
    pub works: Vec<WorkEntry>,
    pub energy: i32,
    pub wood: WoodStock,
}

#[derive(serde::Serialize, Default)]
pub struct WoodStock {
    pub pine: i32,
    pub hazel: i32,
    pub yew: i32,
    pub elm: i32,
}

#[derive(serde::Serialize)]
pub struct WorkEntry {
    pub id: i32,
    pub name: String,
    pub energy_spent: i32,
    pub energy_needed: i32,
}

#[derive(serde::Deserialize)]
pub struct CraftForm {
    pub plan_id: Option<i32>,
    pub energy: Option<i32>,
    pub wood: Option<String>,
}

#[derive(serde::Deserialize)]
pub struct ContinueForm {
    pub work_id: Option<i32>,
    pub energy: Option<i32>,
}

// =========================================================================
// Wood types
// =========================================================================

/// Wood type for bow crafting (mirrors Mineral for smithy).
#[derive(Debug, Clone, Copy)]
enum WoodType {
    Pine = 0,
    Hazel = 1,
    Yew = 2,
    Elm = 3,
}

impl WoodType {
    fn from_key(s: &str) -> Option<Self> {
        match s {
            "pine" => Some(Self::Pine),
            "hazel" => Some(Self::Hazel),
            "yew" => Some(Self::Yew),
            "elm" => Some(Self::Elm),
            _ => None,
        }
    }

    fn mineral_key(self) -> &'static str {
        match self {
            Self::Pine => "pine",
            Self::Hazel => "hazel",
            Self::Yew => "yew",
            Self::Elm => "elm",
        }
    }

    fn durability_bow(self) -> i32 {
        match self {
            Self::Pine => 40,
            Self::Hazel => 80,
            Self::Yew => 160,
            Self::Elm => 320,
        }
    }

    fn durability_arrow(self) -> i32 {
        match self {
            Self::Pine => 20,
            Self::Hazel => 40,
            Self::Yew => 80,
            Self::Elm => 160,
        }
    }

    fn max_bonus(self) -> i32 {
        match self {
            Self::Pine => 6,
            Self::Hazel => 10,
            Self::Yew => 14,
            Self::Elm => 17,
        }
    }

    fn stock_value(self, minerals: &vallheru_data::queries::gathering::MineralsRow) -> i32 {
        match self {
            Self::Pine => minerals.pine,
            Self::Hazel => minerals.hazel,
            Self::Yew => minerals.yew,
            Self::Elm => minerals.elm,
        }
    }
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

/// Bow item type code: "B" for bows.
const BOW_TYPE: &str = "B";
/// Arrow item type code: "Q" for quiver/arrows.
const ARROW_TYPE: &str = "Q";

// =========================================================================
// Handlers
// =========================================================================

/// GET /lumbermill — main page.
pub async fn lumbermill_show(
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

    let meta = PageMeta::titled("Tartak").with_back_link("/city", "Wróć do miasta");
    let base = app.templates.build_context(&ctx, &meta);
    let view = LumbermillMainView { base };
    app.templates.render_value("lumbermill.html", &view)
}

/// GET /lumbermill/plans — show plans for purchase.
pub async fn lumbermill_plans_show(
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

    let carpentry_skill = load_skill(&app, player_id, "carpentry").await;

    let catalog = vallheru_data::queries::crafting::mill_catalog(&app.pool, None)
        .await
        .unwrap_or_default();

    let owned = vallheru_data::queries::crafting::mill_player_plans(&app.pool, player_id)
        .await
        .unwrap_or_default();

    let owned_names: Vec<&str> = owned.iter().map(|p| p.name.as_str()).collect();

    let plans: Vec<PlanEntry> = catalog
        .iter()
        .filter(|p| f64::from(p.level) <= carpentry_skill)
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

    let meta = PageMeta::titled("Tartak - Plany").with_back_link("/lumbermill", "Wróć do tartaku");
    let base = app.templates.build_context(&ctx, &meta);
    let view = LumbermillPlansView {
        base,
        plans,
        owned: owned_entries,
    };
    app.templates.render_value("lumbermill_plans.html", &view)
}

/// POST /lumbermill/plans/buy/:id — buy a plan.
pub async fn lumbermill_plan_buy(
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
        vallheru_data::queries::crafting::mill_find_plan(&app.pool, plan_id, 0).await
    else {
        return error_page(&app, &ctx, "Plan nie istnieje.");
    };

    let carpentry_skill = load_skill(&app, player_id, "carpentry").await;
    if carpentry_skill < f64::from(plan.level) {
        return error_page(&app, &ctx, "Twoja umiejętność ciesielstwa jest zbyt niska.");
    }

    if player_row.credits < i64::from(plan.cost) {
        return error_page(&app, &ctx, "Nie masz wystarczająco złota.");
    }

    let already =
        vallheru_data::queries::crafting::mill_player_has_plan(&app.pool, player_id, &plan.name)
            .await
            .unwrap_or(false);
    if already {
        return error_page(&app, &ctx, "Już posiadasz ten plan.");
    }

    // Deduct gold
    if let Err(e) =
        vallheru_data::queries::gathering::deduct_currency(&app.pool, player_id, plan.cost, 0).await
    {
        tracing::error!(error = %e, "mill plan buy: deduct gold");
        return server_error();
    }

    // Insert player plan copy
    if let Err(e) =
        vallheru_data::queries::crafting::mill_buy_plan(&app.pool, player_id, &plan).await
    {
        tracing::error!(error = %e, "mill plan buy: insert plan");
        return server_error();
    }

    let msg = format!("Kupiłeś plan: {}", plan.name);
    let meta = PageMeta::titled("Tartak - Plany")
        .with_back_link("/lumbermill/plans", "Wróć do planów")
        .with_flash(Flash::success(msg));
    let base = app.templates.build_context(&ctx, &meta);
    app.templates.render("success.html", &base)
}

/// GET /lumbermill/workshop — show crafting workshop.
pub async fn lumbermill_workshop_show(
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

    let owned = vallheru_data::queries::crafting::mill_player_plans(&app.pool, player_id)
        .await
        .unwrap_or_default();

    let plans: Vec<PlanEntry> = owned
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

    let active_works = vallheru_data::queries::crafting::mill_active_works(&app.pool, player_id)
        .await
        .unwrap_or_default();

    let works: Vec<WorkEntry> = active_works
        .iter()
        .map(|w| WorkEntry {
            id: w.id,
            name: w.name.clone(),
            energy_spent: i32::from(w.u_energy),
            energy_needed: i32::from(w.n_energy),
        })
        .collect();

    let minerals = vallheru_data::queries::gathering::load_minerals(&app.pool, player_id)
        .await
        .ok()
        .flatten()
        .unwrap_or_default();

    #[allow(clippy::cast_possible_truncation)]
    let energy = player_row.energy as i32;

    let wood = WoodStock {
        pine: minerals.pine,
        hazel: minerals.hazel,
        yew: minerals.yew,
        elm: minerals.elm,
    };

    let meta =
        PageMeta::titled("Tartak - Pracownia").with_back_link("/lumbermill", "Wróć do tartaku");
    let base = app.templates.build_context(&ctx, &meta);
    let view = LumbermillWorkshopView {
        base,
        plans,
        works,
        energy,
        wood,
    };
    app.templates
        .render_value("lumbermill_workshop.html", &view)
}

/// POST /lumbermill/craft — craft bows or arrows.
#[allow(clippy::too_many_lines)]
pub async fn lumbermill_craft(
    State(app): State<AppState>,
    Extension(ctx): Extension<RequestContext>,
    Form(form): Form<CraftForm>,
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

    let Some(plan_id) = form.plan_id else {
        return error_page(&app, &ctx, "Wybierz plan.");
    };

    let energy_spend = match form.energy {
        Some(e) if e > 0 => e,
        _ => return error_page(&app, &ctx, "Podaj energię."),
    };

    let wood_key = match form.wood.as_deref() {
        Some(k) if !k.is_empty() => k,
        _ => return error_page(&app, &ctx, "Wybierz rodzaj drewna."),
    };

    let Some(wood_type) = WoodType::from_key(wood_key) else {
        return error_page(&app, &ctx, "Nieznany rodzaj drewna.");
    };

    let Ok(Some(plan)) =
        vallheru_data::queries::crafting::mill_find_plan(&app.pool, plan_id, player_id).await
    else {
        return error_page(&app, &ctx, "Nie posiadasz tego planu.");
    };

    let plan_level = i32::from(plan.level);

    #[allow(clippy::cast_possible_truncation)]
    let avail_energy = player_row.energy as i32;
    if energy_spend > avail_energy {
        return error_page(&app, &ctx, "Nie masz wystarczająco energii.");
    }

    let minerals = vallheru_data::queries::gathering::load_minerals(&app.pool, player_id)
        .await
        .ok()
        .flatten()
        .unwrap_or_default();

    // Wood cost = energy spent (1 wood per 1 energy)
    let wood_cost = energy_spend;
    let available_wood = wood_type.stock_value(&minerals);
    if available_wood < wood_cost {
        return error_page(&app, &ctx, "Nie masz wystarczająco drewna.");
    }

    // Check energy_spend vs plan_level for batch size
    let batch = if plan_level > 0 {
        energy_spend / plan_level
    } else {
        0
    };

    let carpentry_skill = load_skill(&app, player_id, "carpentry").await;
    let strength = load_stat(&app, player_id, "strength").await;
    let is_craftsman = player_row.class == "Rzemieślnik";
    let is_bow = plan.item_type == BOW_TYPE;

    // Deduct energy
    if let Err(e) = vallheru_data::queries::gathering::deduct_energy(
        &app.pool,
        player_id,
        f64::from(energy_spend),
    )
    .await
    {
        tracing::error!(error = %e, "lumbermill_craft: deduct energy");
        return server_error();
    }

    // Deduct wood
    if let Err(e) = vallheru_data::queries::gathering::add_minerals(
        &app.pool,
        player_id,
        &[(wood_type.mineral_key(), -wood_cost)],
    )
    .await
    {
        tracing::error!(error = %e, "lumbermill_craft: deduct wood");
        return server_error();
    }

    let mut results_text;
    let mut total_xp: i32;

    if batch > 0 {
        // Craft items — collect results first (rng is !Send, must not cross .await)
        struct CraftedWood {
            name: String,
            power: i32,
            speed: i32,
            durability: i32,
            item_type_code: &'static str,
            sell_cost: i64,
        }

        let items = {
            #[allow(clippy::cast_possible_truncation)]
            let chance = ((carpentry_skill + strength) / f64::from(plan_level) * 50.0) as i32;
            let chance = chance.min(95);

            let mut rng = rand::thread_rng();
            let mut crafted = Vec::new();

            for _ in 0..batch {
                let roll = rng.gen_range(1..=100);
                if roll <= chance {
                    let max_bonus = wood_type.max_bonus();
                    let bonus = rng.gen_range(0..=max_bonus);

                    let (base_power, base_speed, durability) = if is_bow {
                        (plan_level + bonus, 0, wood_type.durability_bow())
                    } else {
                        (plan_level, 0, wood_type.durability_arrow())
                    };

                    let item_type_code = if is_bow { BOW_TYPE } else { ARROW_TYPE };
                    let sell_cost = i64::from(plan.cost) / 20;

                    let (item_name, final_power, final_durability) =
                        if is_craftsman && rng.gen_range(1..=100) > 89 {
                            let special_roll = rng.gen_range(1..=3);
                            let prefix = match special_roll {
                                1 => "Smoczy",
                                2 => "Elfi",
                                _ => "Krasnoludzki",
                            };
                            let sp = base_power * 2;
                            let sd = durability * 2;
                            (format!("{prefix} {}", plan.name), sp, sd)
                        } else {
                            (plan.name.clone(), base_power, durability)
                        };

                    crafted.push(CraftedWood {
                        name: item_name,
                        power: final_power,
                        speed: base_speed,
                        durability: final_durability,
                        item_type_code,
                        sell_cost,
                    });
                }
            }
            crafted
        };
        // rng is dropped here — safe to .await below

        #[allow(clippy::cast_possible_wrap, clippy::cast_possible_truncation)]
        let successes = items.len() as i32;
        for item in &items {
            if let Err(e) = vallheru_data::queries::crafting::insert_crafted_item(
                &app.pool,
                player_id,
                &item.name,
                item.power,
                item.item_type_code,
                item.sell_cost,
                i32::from(plan.level),
                0,
                item.durability,
                item.speed,
                item.durability,
                false,
                0,
            )
            .await
            {
                tracing::error!(error = %e, "lumbermill_craft: insert item");
            }
        }

        total_xp = plan_level * 10 * successes;
        if is_craftsman {
            total_xp *= 2;
        }
        results_text = format!("Wytworzono {successes}/{batch} przedmiotów.");
    } else {
        // Partial work
        #[allow(clippy::cast_possible_truncation)]
        let energy_i16 = energy_spend as i16;
        if let Err(e) = vallheru_data::queries::crafting::mill_create_work(
            &app.pool,
            player_id,
            &plan.name,
            plan.level,
            energy_i16,
            &plan.item_type,
            plan.elite,
        )
        .await
        {
            tracing::error!(error = %e, "lumbermill_craft: create work");
            return server_error();
        }
        total_xp = 2;
        results_text = "Rozpoczęto pracę.".to_string();
    }

    // Apply XP
    if total_xp > 0 {
        let xp_text = super::smithy::apply_craft_xp(
            &app,
            player_id,
            &player_row.race,
            &player_row.class,
            total_xp,
            "carpentry",
        )
        .await;
        results_text.push_str(&xp_text);
    }

    let meta = PageMeta::titled("Tartak - Pracownia")
        .with_back_link("/lumbermill/workshop", "Wróć do pracowni")
        .with_flash(Flash::success(results_text));
    let base = app.templates.build_context(&ctx, &meta);
    app.templates.render("success.html", &base)
}

/// POST /lumbermill/continue — continue work-in-progress.
#[allow(clippy::too_many_lines)]
pub async fn lumbermill_continue(
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

    let Some(energy_add) = form.energy.filter(|&e| e > 0) else {
        return error_page(&app, &ctx, "Podaj energię.");
    };

    #[allow(clippy::cast_possible_truncation)]
    let avail_energy = player_row.energy as i32;
    if energy_add > avail_energy {
        return error_page(&app, &ctx, "Nie masz wystarczająco energii.");
    }

    let Ok(Some(work)) =
        vallheru_data::queries::crafting::mill_find_work(&app.pool, work_id, player_id).await
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
        tracing::error!(error = %e, "lumbermill_continue: deduct energy");
        return server_error();
    }

    let new_energy = i32::from(work.u_energy) + energy_add;
    let plan_level = i32::from(work.n_energy);
    let carpentry_skill = load_skill(&app, player_id, "carpentry").await;
    let strength = load_stat(&app, player_id, "strength").await;
    let is_craftsman = player_row.class == "Rzemieślnik";

    let mut results_text;
    let mut total_xp;

    if new_energy >= plan_level {
        // Work complete
        #[allow(clippy::cast_possible_truncation)]
        let chance = ((carpentry_skill + strength) / f64::from(plan_level) * 50.0) as i32;
        let chance = chance.min(95);
        let roll = rand::thread_rng().gen_range(1..=100);

        if roll <= chance {
            let sell_cost = 100i64;
            if let Err(e) = vallheru_data::queries::crafting::insert_crafted_item(
                &app.pool, player_id, &work.name, plan_level, // power
                BOW_TYPE,   // item_type
                sell_cost,  // cost
                plan_level, // min_level
                0,          // agility
                100,        // durability
                0,          // speed
                100,        // max_durability
                false,      // two_handed
                0,          // repair_cost
            )
            .await
            {
                tracing::error!(error = %e, "lumbermill_continue: insert item");
            }
            total_xp = plan_level * 10;
            results_text = format!("Wytworzono: {}", work.name);
        } else {
            total_xp = 2;
            results_text = "Praca nie powiodła się.".to_string();
        }

        // Delete work
        log_err!(
            vallheru_data::queries::crafting::mill_delete_work(&app.pool, work_id).await,
            "mill delete work"
        );
    } else {
        // Still in progress
        #[allow(clippy::cast_possible_truncation)]
        let energy_add_i16 = energy_add as i16;
        if let Err(e) = vallheru_data::queries::crafting::mill_add_work_energy(
            &app.pool,
            work_id,
            energy_add_i16,
        )
        .await
        {
            tracing::error!(error = %e, "lumbermill_continue: add energy");
            return server_error();
        }
        total_xp = 2;
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
            "carpentry",
        )
        .await;
        results_text.push_str(&xp_text);
    }

    let meta = PageMeta::titled("Tartak - Pracownia")
        .with_back_link("/lumbermill/workshop", "Wróć do pracowni")
        .with_flash(Flash::success(results_text));
    let base = app.templates.build_context(&ctx, &meta);
    app.templates.render("success.html", &base)
}
