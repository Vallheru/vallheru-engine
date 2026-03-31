//! Alchemy workshop handler.
//!
//! Ported from `alchemik.php`. Covers recipe purchase and potion brewing.

use axum::{Extension, Form, extract::Path, extract::State, response::Response};
use rand::Rng;

use crate::middleware::context::RequestContext;
use crate::page::{Flash, FlashKind, PageMeta};
use crate::state::AppState;

// =========================================================================
// View models
// =========================================================================

#[derive(serde::Serialize)]
pub struct AlchemyMainView {
    #[serde(flatten)]
    pub base: crate::render::RenderContext,
}

#[derive(serde::Serialize)]
pub struct AlchemyRecipesView {
    #[serde(flatten)]
    pub base: crate::render::RenderContext,
    pub recipes: Vec<RecipeEntry>,
    pub owned: Vec<RecipeEntry>,
}

#[derive(serde::Serialize)]
pub struct RecipeEntry {
    pub id: i32,
    pub name: String,
    pub cost: i32,
    pub level: i16,
    pub illani: i32,
    pub illanias: i32,
    pub nutari: i32,
    pub dynallca: i32,
    pub owned: bool,
}

#[derive(serde::Serialize)]
pub struct AlchemyLabView {
    #[serde(flatten)]
    pub base: crate::render::RenderContext,
    pub recipes: Vec<RecipeEntry>,
    pub energy: i32,
    pub herbs: HerbStock,
}

#[derive(serde::Serialize, Default)]
pub struct HerbStock {
    pub illani: i32,
    pub illanias: i32,
    pub nutari: i32,
    pub dynallca: i32,
}

#[derive(serde::Deserialize)]
pub struct BrewForm {
    pub recipe_id: Option<i32>,
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
    pub clas: String,
    pub race: String,
}

async fn load_player(app: &AppState, player_id: i32) -> Result<PlayerRow, Response> {
    sqlx::query_as::<_, PlayerRow>(
        "SELECT location, hp, energy, credits, clas, race FROM players WHERE id = $1",
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

/// GET /alchemy — main alchemy page.
pub async fn alchemy_show(
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

    let meta = PageMeta::titled("Pracownia Alchemiczna").with_back_link("/city", "Wróć do miasta");
    let base = app.templates.build_context(&ctx, &meta);
    let view = AlchemyMainView { base };
    app.templates.render_value("alchemy.html", &view)
}

/// GET /alchemy/recipes — show recipes for purchase.
pub async fn alchemy_recipes_show(
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

    let alchemy_skill = load_skill(&app, player_id, "alchemy").await;

    let catalog = vallheru_data::queries::crafting::alchemy_catalog(&app.pool)
        .await
        .unwrap_or_default();

    let owned = vallheru_data::queries::crafting::alchemy_player_recipes(&app.pool, player_id)
        .await
        .unwrap_or_default();

    let owned_names: Vec<&str> = owned.iter().map(|r| r.name.as_str()).collect();

    let recipes: Vec<RecipeEntry> = catalog
        .iter()
        .filter(|r| f64::from(r.level) <= alchemy_skill)
        .map(|r| RecipeEntry {
            id: r.id,
            name: r.name.clone(),
            cost: r.cost,
            level: r.level,
            illani: r.illani,
            illanias: r.illanias,
            nutari: r.nutari,
            dynallca: r.dynallca,
            owned: owned_names.contains(&r.name.as_str()),
        })
        .collect();

    let owned_entries: Vec<RecipeEntry> = owned
        .iter()
        .map(|r| RecipeEntry {
            id: r.id,
            name: r.name.clone(),
            cost: r.cost,
            level: r.level,
            illani: r.illani,
            illanias: r.illanias,
            nutari: r.nutari,
            dynallca: r.dynallca,
            owned: true,
        })
        .collect();

    let meta =
        PageMeta::titled("Alchemia - Przepisy").with_back_link("/alchemy", "Wróć do pracowni");
    let base = app.templates.build_context(&ctx, &meta);
    let view = AlchemyRecipesView {
        base,
        recipes,
        owned: owned_entries,
    };
    app.templates.render_value("alchemy_recipes.html", &view)
}

/// POST /alchemy/recipes/buy/:id — buy a recipe.
pub async fn alchemy_recipe_buy(
    State(app): State<AppState>,
    Extension(ctx): Extension<RequestContext>,
    Path(recipe_id): Path<i32>,
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

    let Ok(Some(recipe)) =
        vallheru_data::queries::crafting::alchemy_find_recipe(&app.pool, recipe_id, 0).await
    else {
        return error_page(&app, &ctx, "Przepis nie istnieje.");
    };

    let alchemy_skill = load_skill(&app, player_id, "alchemy").await;
    if alchemy_skill < f64::from(recipe.level) {
        return error_page(&app, &ctx, "Twoja umiejętność alchemii jest zbyt niska.");
    }

    if player_row.credits < i64::from(recipe.cost) {
        return error_page(&app, &ctx, "Nie masz wystarczająco złota.");
    }

    let already_owned = vallheru_data::queries::crafting::alchemy_player_has_recipe(
        &app.pool,
        player_id,
        &recipe.name,
    )
    .await
    .unwrap_or(false);
    if already_owned {
        return error_page(&app, &ctx, "Już posiadasz ten przepis.");
    }

    // Deduct gold
    if let Err(e) =
        vallheru_data::queries::gathering::deduct_currency(&app.pool, player_id, recipe.cost, 0)
            .await
    {
        tracing::error!(error = %e, "alchemy recipe buy: deduct gold");
        return server_error();
    }

    // Insert player recipe copy
    if let Err(e) =
        vallheru_data::queries::crafting::alchemy_buy_recipe(&app.pool, player_id, &recipe).await
    {
        tracing::error!(error = %e, "alchemy recipe buy: insert recipe");
        return server_error();
    }

    let msg = format!("Kupiłeś przepis: {}", recipe.name);
    let meta = PageMeta::titled("Alchemia - Przepisy")
        .with_back_link("/alchemy/recipes", "Wróć do przepisów")
        .with_flash(Flash::success(msg));
    let base = app.templates.build_context(&ctx, &meta);
    app.templates.render("success.html", &base)
}

/// GET /alchemy/lab — show brewing workshop.
pub async fn alchemy_lab_show(
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

    let owned = vallheru_data::queries::crafting::alchemy_player_recipes(&app.pool, player_id)
        .await
        .unwrap_or_default();

    let herbs = vallheru_data::queries::gathering::load_herbs(&app.pool, player_id)
        .await
        .ok()
        .flatten()
        .unwrap_or_default();

    let recipes: Vec<RecipeEntry> = owned
        .iter()
        .map(|r| RecipeEntry {
            id: r.id,
            name: r.name.clone(),
            cost: r.cost,
            level: r.level,
            illani: r.illani,
            illanias: r.illanias,
            nutari: r.nutari,
            dynallca: r.dynallca,
            owned: true,
        })
        .collect();

    #[allow(clippy::cast_possible_truncation)]
    let energy = player_row.energy as i32;

    let herb_stock = HerbStock {
        illani: herbs.illani,
        illanias: herbs.illanias,
        nutari: herbs.nutari,
        dynallca: herbs.dynallca,
    };

    let meta =
        PageMeta::titled("Alchemia - Pracownia").with_back_link("/alchemy", "Wróć do alchemii");
    let base = app.templates.build_context(&ctx, &meta);
    let view = AlchemyLabView {
        base,
        recipes,
        energy,
        herbs: herb_stock,
    };
    app.templates.render_value("alchemy_lab.html", &view)
}

/// POST /alchemy/brew — brew potions.
#[allow(clippy::too_many_lines)]
pub async fn alchemy_brew(
    State(app): State<AppState>,
    Extension(ctx): Extension<RequestContext>,
    Form(form): Form<BrewForm>,
) -> Response {
    struct BrewedPotion {
        name: String,
        power: i32,
        sell_cost: i64,
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

    let Some(recipe_id) = form.recipe_id else {
        return error_page(&app, &ctx, "Wybierz przepis.");
    };

    let amount = match form.amount {
        Some(a) if a > 0 => a,
        _ => return error_page(&app, &ctx, "Podaj ilość."),
    };

    let Ok(Some(recipe)) =
        vallheru_data::queries::crafting::alchemy_find_recipe(&app.pool, recipe_id, player_id)
            .await
    else {
        return error_page(&app, &ctx, "Nie posiadasz tego przepisu.");
    };

    // Energy cost: level * 0.2, min 1
    let level = i32::from(recipe.level);
    #[allow(clippy::cast_possible_truncation)]
    let energy_per = (f64::from(level) * 0.2).max(1.0) as i32;
    let total_energy = energy_per * amount;
    #[allow(clippy::cast_possible_truncation)]
    let avail_energy = player_row.energy as i32;

    if total_energy > avail_energy {
        return error_page(&app, &ctx, "Nie masz wystarczająco energii.");
    }

    // Check herb costs
    let herbs = vallheru_data::queries::gathering::load_herbs(&app.pool, player_id)
        .await
        .ok()
        .flatten()
        .unwrap_or_default();

    let illani_cost = recipe.illani * amount;
    let illanias_cost = recipe.illanias * amount;
    let nutari_cost = recipe.nutari * amount;
    let dynallca_cost = recipe.dynallca * amount;

    if herbs.illani < illani_cost
        || herbs.illanias < illanias_cost
        || herbs.nutari < nutari_cost
        || herbs.dynallca < dynallca_cost
    {
        return error_page(&app, &ctx, "Nie masz wystarczająco ziół.");
    }

    // Determine potion type from recipe
    let potion_type = if recipe.dynallca > 0 && recipe.illani == 0 && recipe.nutari == 0 {
        "P" // Poison (pure dynallca)
    } else if recipe.illani > 0 && recipe.dynallca == 0 && recipe.nutari == 0 {
        "M" // Mana potion (illani)
    } else if recipe.nutari > 0 && recipe.dynallca == 0 && recipe.illani == 0 {
        "H" // Health potion (nutari)
    } else if recipe.name.contains("antidotum") {
        "A" // Antidote
    } else if recipe.name.contains("trucizn") {
        "P" // Poison (mixed)
    } else {
        "H" // Default health
    };

    // Get relevant stat for brewing
    let stat_key = match potion_type {
        "M" | "P" => "wisdom",
        "A" => "speed",
        _ => "inteli",
    };
    let alchemy_skill = load_skill(&app, player_id, "alchemy").await;
    let relevant_stat = load_stat(&app, player_id, stat_key).await;
    let is_craftsman = player_row.clas == "Rzemieślnik";

    // Brew potions — collect results first (rng is !Send, must not cross .await)

    let (potions, total_xp) = {
        let mut rng = rand::thread_rng();
        let mut brewed = Vec::new();

        #[allow(clippy::cast_possible_truncation)]
        let chance = (alchemy_skill + relevant_stat) as i32;

        for _ in 0..amount {
            let roll: i32 = rng.gen_range(1..=100);
            if roll <= chance.min(95) {
                #[allow(clippy::cast_possible_truncation)]
                let power = (alchemy_skill / 2.0).min(f64::from(level)) as i32;
                let power = power.max(1);

                let (final_name, final_power) = if is_craftsman && rng.gen_range(1..=100) > 89 {
                    #[allow(clippy::cast_possible_truncation)]
                    let sup_power = (level + (alchemy_skill as i32)).min(level * 2);
                    (format!("{} (S)", recipe.name), sup_power)
                } else {
                    (recipe.name.clone(), power)
                };

                let sell_cost = i64::from(recipe.cost) / 20;
                brewed.push(BrewedPotion {
                    name: final_name,
                    power: final_power,
                    sell_cost,
                });
            }
        }

        #[allow(clippy::cast_possible_wrap, clippy::cast_possible_truncation)]
        let success_count = brewed.len() as i32;
        let mut xp = success_count * level * 2;
        if is_craftsman {
            xp *= 2;
        }
        (brewed, xp)
    };
    // rng is dropped here — safe to .await below

    #[allow(clippy::cast_possible_wrap, clippy::cast_possible_truncation)]
    let success_count = potions.len() as i32;
    for p in &potions {
        if let Err(e) = vallheru_data::queries::crafting::add_or_stack_potion(
            &app.pool,
            player_id,
            &p.name,
            potion_type,
            p.power,
            1,
            p.sell_cost,
        )
        .await
        {
            tracing::error!(error = %e, "alchemy_brew: add potion");
        }
    }

    // Deduct energy
    if let Err(e) = vallheru_data::queries::gathering::deduct_energy(
        &app.pool,
        player_id,
        f64::from(total_energy),
    )
    .await
    {
        tracing::error!(error = %e, "alchemy_brew: deduct energy");
        return server_error();
    }

    // Deduct herbs
    let mut herb_updates = Vec::new();
    if illani_cost > 0 {
        herb_updates.push(("illani", -illani_cost));
    }
    if illanias_cost > 0 {
        herb_updates.push(("illanias", -illanias_cost));
    }
    if nutari_cost > 0 {
        herb_updates.push(("nutari", -nutari_cost));
    }
    if dynallca_cost > 0 {
        herb_updates.push(("dynallca", -dynallca_cost));
    }

    if !herb_updates.is_empty() {
        if let Err(e) = deduct_herbs(&app.pool, player_id, &herb_updates).await {
            tracing::error!(error = %e, "alchemy_brew: deduct herbs");
            return server_error();
        }
    }

    // Apply XP
    let mut results_text = String::new();
    if total_xp > 0 {
        results_text = super::smithy::apply_craft_xp(
            &app,
            player_id,
            &player_row.race,
            &player_row.clas,
            total_xp,
            "alchemy",
        )
        .await;
    }

    let msg = format!("Uwarzyłeś {success_count}/{amount} mikstur. PD: {total_xp}.{results_text}");

    let meta = PageMeta::titled("Alchemia - Pracownia")
        .with_back_link("/alchemy/lab", "Wróć do pracowni")
        .with_flash(Flash::success(msg));
    let base = app.templates.build_context(&ctx, &meta);
    app.templates.render("success.html", &base)
}

/// Deduct herbs from a player's inventory.
async fn deduct_herbs(
    pool: &sqlx::PgPool,
    player_id: i32,
    updates: &[(&str, i32)],
) -> Result<(), sqlx::Error> {
    const ALLOWED: &[&str] = &["illani", "illanias", "nutari", "dynallca"];

    let mut set_parts = Vec::new();
    let mut values: Vec<i32> = Vec::new();
    let mut param_idx = 1;

    for &(col, amount) in updates {
        if !ALLOWED.contains(&col) {
            continue;
        }
        set_parts.push(format!("{col} = {col} + ${param_idx}"));
        values.push(amount);
        param_idx += 1;
    }

    if set_parts.is_empty() {
        return Ok(());
    }

    let sql = format!(
        "UPDATE herbs SET {} WHERE gracz = ${param_idx}",
        set_parts.join(", ")
    );

    let mut query = sqlx::query(&sql);
    for v in &values {
        query = query.bind(*v);
    }
    query = query.bind(player_id);
    query.execute(pool).await?;
    Ok(())
}
