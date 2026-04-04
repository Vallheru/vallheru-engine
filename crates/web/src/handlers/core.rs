//! Core (creature collection) handler.
//!
//! Ported from `core.php`. Covers license purchase, library, exploration,
//! training, arena fights, and healing.

use axum::{Extension, Form, extract::Path, extract::State, response::Response};
use rand::Rng;

use crate::log_err;
use crate::middleware::context::RequestContext;
use crate::page::{Flash, FlashKind, PageMeta};
use crate::state::AppState;
use vallheru_domain::crafting::breeding;

// =========================================================================
// View models
// =========================================================================

#[derive(serde::Serialize)]
pub struct CoreMainView {
    #[serde(flatten)]
    pub base: crate::render::RenderContext,
    pub has_license: bool,
    pub license_cost: i32,
}

#[derive(serde::Serialize)]
pub struct CoreLibraryView {
    #[serde(flatten)]
    pub base: crate::render::RenderContext,
    pub creatures: Vec<CreatureEntry>,
}

#[derive(serde::Serialize)]
pub struct CreatureEntry {
    pub id: i32,
    pub name: String,
    pub core_type: String,
    pub power: f64,
    pub defense: f64,
    pub status: String,
    pub active: String,
    pub gender: String,
    pub wins: i32,
    pub losses: i32,
}

#[derive(serde::Serialize)]
pub struct CoreExploreView {
    #[serde(flatten)]
    pub base: crate::render::RenderContext,
    pub regions: Vec<RegionEntry>,
    pub energy: i32,
    pub platinum: i32,
}

#[derive(serde::Serialize)]
pub struct RegionEntry {
    pub key: String,
    pub name: String,
    pub platinum_cost: i32,
}

#[derive(serde::Serialize)]
pub struct CoreArenaView {
    #[serde(flatten)]
    pub base: crate::render::RenderContext,
    pub active_creature: Option<CreatureEntry>,
    pub opponents: Vec<OpponentEntry>,
}

#[derive(serde::Serialize)]
pub struct OpponentEntry {
    pub id: i32,
    pub name: String,
    pub creature_type: String,
    pub power: f64,
    pub defense: f64,
    pub owner_name: String,
}

#[derive(serde::Deserialize)]
pub struct ExploreForm {
    pub region: Option<String>,
    pub attempts: Option<i32>,
}

#[derive(serde::Deserialize)]
pub struct TrainForm {
    pub core_id: Option<i32>,
    pub stat: Option<String>,
    pub reps: Option<i32>,
}

// =========================================================================
// Private helpers
// =========================================================================

#[derive(Debug, Clone, sqlx::FromRow)]
struct PlayerRow {
    pub location: String,
    pub energy: f64,
    pub credits: i32,
    pub platinum: i32,
}

async fn load_player(app: &AppState, player_id: i32) -> Result<PlayerRow, Response> {
    sqlx::query_as::<_, PlayerRow>(
        "SELECT location, energy, credits, platinum FROM players WHERE id = $1",
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

/// Check if player has a core license (at least one creature or a flag).
async fn has_license(app: &AppState, player_id: i32) -> bool {
    let row: Option<(i64,)> = sqlx::query_as("SELECT COUNT(*) FROM core WHERE owner = $1")
        .bind(player_id)
        .fetch_optional(&app.pool)
        .await
        .unwrap_or_else(|e| {
            tracing::error!(error = %e, player_id, "has_license query failed");
            None
        });
    // If they have any creatures, they have a license.
    // Also check a flag column if it exists.
    row.is_some_and(|r| r.0 > 0)
}

// =========================================================================
// Handlers
// =========================================================================

/// GET /core — main creature page.
pub async fn core_show(
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

    let licensed = has_license(&app, player_id).await;

    let meta = PageMeta::titled("Stwory").with_back_link("/city", "Wróć do miasta");
    let base = app.templates.build_context(&ctx, &meta);
    let view = CoreMainView {
        base,
        has_license: licensed,
        license_cost: breeding::LICENSE_GOLD_COST,
    };
    app.templates.render_value("core.html", &view)
}

/// POST /core/license — buy a license.
pub async fn core_license_buy(
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

    if player_row.credits < breeding::LICENSE_GOLD_COST {
        return error_page(&app, &ctx, "Nie masz wystarczająco złota.");
    }

    // Deduct gold
    if let Err(e) = vallheru_data::queries::gathering::deduct_currency(
        &app.pool,
        player_id,
        breeding::LICENSE_GOLD_COST,
        0,
    )
    .await
    {
        tracing::error!(error = %e, "core license buy: deduct gold");
        return server_error();
    }

    // Create a starter creature (common plant type)
    let defs = match vallheru_data::queries::crafting::core_definitions_by_type(&app.pool, "Plant")
        .await
    {
        Ok(v) => v,
        Err(e) => {
            tracing::error!(error = %e, player_id, "core_definitions_by_type failed");
            Vec::new()
        }
    };

    if let Some(def) = defs.first() {
        let gender = if rand::thread_rng().gen_bool(0.5) {
            "M"
        } else {
            "F"
        };
        log_err!(
            vallheru_data::queries::crafting::core_create_creature(
                &app.pool,
                player_id,
                &def.name,
                &def.core_type,
                def.id,
                def.power,
                def.defense,
                &def.name,
                gender,
            )
            .await,
            "core create creature"
        );
    }

    let meta = PageMeta::titled("Stwory")
        .with_back_link("/core", "Wróć")
        .with_flash(Flash::success("Kupiłeś licencję łowcy stworów!".to_owned()));
    let base = app.templates.build_context(&ctx, &meta);
    app.templates.render("success.html", &base)
}

/// GET /core/library — show player creatures.
pub async fn core_library_show(
    State(app): State<AppState>,
    Extension(ctx): Extension<RequestContext>,
) -> Response {
    let Some(ref user) = ctx.session_user else {
        return crate::page::redirect("/login");
    };

    #[allow(clippy::cast_possible_truncation)]
    let player_id = user.id as i32;

    let creatures_rows =
        match vallheru_data::queries::crafting::core_player_creatures(&app.pool, player_id).await {
            Ok(v) => v,
            Err(e) => {
                tracing::error!(error = %e, player_id, "core_player_creatures failed");
                Vec::new()
            }
        };

    let creatures: Vec<CreatureEntry> = creatures_rows
        .iter()
        .map(|c| CreatureEntry {
            id: c.id,
            name: c.name.clone(),
            core_type: c.core_type.clone(),
            power: c.power,
            defense: c.defense,
            status: c.status.clone(),
            active: c.active.clone(),
            gender: c.gender.clone(),
            wins: c.wins,
            losses: c.losses,
        })
        .collect();

    let meta = PageMeta::titled("Stwory - Biblioteka").with_back_link("/core", "Wróć");
    let base = app.templates.build_context(&ctx, &meta);
    let view = CoreLibraryView { base, creatures };
    app.templates.render_value("core_library.html", &view)
}

/// GET /core/explore — show exploration regions.
pub async fn core_explore_show(
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

    let regions = vec![
        RegionEntry {
            key: "Plant".to_string(),
            name: "Las (rośliny)".to_string(),
            platinum_cost: 0,
        },
        RegionEntry {
            key: "Aqua".to_string(),
            name: "Ocean (wodne)".to_string(),
            platinum_cost: 50,
        },
        RegionEntry {
            key: "Material".to_string(),
            name: "Góry (materiałowe)".to_string(),
            platinum_cost: 100,
        },
        RegionEntry {
            key: "Element".to_string(),
            name: "Równiny (żywiołowe)".to_string(),
            platinum_cost: 150,
        },
        RegionEntry {
            key: "Alien".to_string(),
            name: "Pustynia (obce)".to_string(),
            platinum_cost: 200,
        },
        RegionEntry {
            key: "Ancient".to_string(),
            name: "Magiczne (starożytne)".to_string(),
            platinum_cost: 250,
        },
    ];

    #[allow(clippy::cast_possible_truncation)]
    let energy = player_row.energy as i32;

    let meta = PageMeta::titled("Stwory - Eksploracja").with_back_link("/core", "Wróć");
    let base = app.templates.build_context(&ctx, &meta);
    let view = CoreExploreView {
        base,
        regions,
        energy,
        platinum: player_row.platinum,
    };
    app.templates.render_value("core_explore.html", &view)
}

/// POST /core/explore — perform exploration.
#[allow(clippy::too_many_lines)]
pub async fn core_explore(
    State(app): State<AppState>,
    Extension(ctx): Extension<RequestContext>,
    Form(form): Form<ExploreForm>,
) -> Response {
    struct FoundCreature {
        name: String,
        core_type: String,
        ref_id: i32,
        power: f64,
        defense: f64,
        gender: &'static str,
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

    let region_key = match form.region.as_deref() {
        Some(r) if !r.is_empty() => r,
        _ => return error_page(&app, &ctx, "Wybierz region."),
    };

    let Some(core_type) = breeding::CoreType::from_db(region_key) else {
        return error_page(&app, &ctx, "Nieznany region.");
    };

    let attempts = match form.attempts {
        Some(a) if a > 0 => a,
        _ => return error_page(&app, &ctx, "Podaj liczbę prób."),
    };

    let energy_cost = breeding::EXPLORE_ENERGY_PER_ATTEMPT * f64::from(attempts);
    if player_row.energy < energy_cost {
        return error_page(&app, &ctx, "Nie masz wystarczająco energii.");
    }

    let platinum_cost = core_type.explore_platinum_cost() * attempts;
    if player_row.platinum < platinum_cost {
        return error_page(&app, &ctx, "Nie masz wystarczająco platyny.");
    }

    // Deduct energy
    if let Err(e) =
        vallheru_data::queries::gathering::deduct_energy(&app.pool, player_id, energy_cost).await
    {
        tracing::error!(error = %e, "core_explore: deduct energy");
        return server_error();
    }

    // Deduct platinum
    if platinum_cost > 0 {
        if let Err(e) = vallheru_data::queries::gathering::deduct_currency(
            &app.pool,
            player_id,
            0,
            platinum_cost,
        )
        .await
        {
            tracing::error!(error = %e, "core_explore: deduct platinum");
            return server_error();
        }
    }

    // Get definitions for this region
    let defs = match vallheru_data::queries::crafting::core_definitions_by_type(
        &app.pool,
        core_type.as_db(),
    )
    .await
    {
        Ok(v) => v,
        Err(e) => {
            tracing::error!(error = %e, player_id, "core_definitions_by_type failed");
            Vec::new()
        }
    };

    // Explore — collect found creatures first (rng is !Send, must not cross .await)

    let found = {
        let mut rng = rand::thread_rng();
        let mut found = Vec::new();

        for _ in 0..attempts {
            let rarity_roll = rng.gen_range(1..=3);
            let rarity = breeding::exploration_rarity(rarity_roll);
            let die_size = breeding::exploration_die_size(rarity);

            let roll_a = rng.gen_range(1..=die_size);
            let roll_b = rng.gen_range(1..=die_size);

            if breeding::exploration_find(roll_a, roll_b) {
                let target_rarity = match rarity {
                    breeding::ExplorationRarity::Common => 1,
                    breeding::ExplorationRarity::Uncommon => 2,
                    breeding::ExplorationRarity::Rare => 3,
                };

                let matching: Vec<_> = defs.iter().filter(|d| d.rarity == target_rarity).collect();
                if let Some(def) = matching.get(rng.gen_range(0..matching.len().max(1))) {
                    let gender = if rng.gen_bool(0.5) { "M" } else { "F" };
                    found.push(FoundCreature {
                        name: def.name.clone(),
                        core_type: def.core_type.clone(),
                        ref_id: def.id,
                        power: def.power,
                        defense: def.defense,
                        gender,
                    });
                }
            }
        }
        found
    };
    // rng is dropped here — safe to .await below

    let mut found_names: Vec<String> = Vec::new();
    for creature in &found {
        log_err!(
            vallheru_data::queries::crafting::core_create_creature(
                &app.pool,
                player_id,
                &creature.name,
                &creature.core_type,
                creature.ref_id,
                creature.power,
                creature.defense,
                &creature.name,
                creature.gender,
            )
            .await,
            "core create creature"
        );
        found_names.push(creature.name.clone());
    }

    let found_count = found.len();
    let msg = if found_count > 0 {
        format!(
            "Znaleziono {found_count} stworów: {}",
            found_names.join(", ")
        )
    } else {
        format!("Nie znaleziono żadnych stworów w {attempts} próbach.")
    };

    let meta = PageMeta::titled("Stwory - Eksploracja")
        .with_back_link("/core/explore", "Wróć")
        .with_flash(Flash::success(msg));
    let base = app.templates.build_context(&ctx, &meta);
    app.templates.render("success.html", &base)
}

/// POST /core/train — train a creature.
pub async fn core_train(
    State(app): State<AppState>,
    Extension(ctx): Extension<RequestContext>,
    Form(form): Form<TrainForm>,
) -> Response {
    let Some(ref user) = ctx.session_user else {
        return crate::page::redirect("/login");
    };

    #[allow(clippy::cast_possible_truncation)]
    let player_id = user.id as i32;

    let Some(core_id) = form.core_id else {
        return error_page(&app, &ctx, "Wybierz stwora.");
    };

    let stat = match form.stat.as_deref() {
        Some("power") => "power",
        Some("defense") => "defense",
        _ => return error_page(&app, &ctx, "Wybierz statystykę."),
    };

    let reps = match form.reps {
        Some(r) if r > 0 => r,
        _ => return error_page(&app, &ctx, "Podaj liczbę powtórzeń."),
    };

    let Ok(Some(creature)) =
        vallheru_data::queries::crafting::core_find_creature(&app.pool, core_id, player_id).await
    else {
        return error_page(&app, &ctx, "Nie masz takiego stwora.");
    };

    if creature.status != "Alive" {
        return error_page(&app, &ctx, "Stwór jest martwy.");
    }

    let gain = breeding::training_stat_gain(reps);

    let (power_add, defense_add) = match stat {
        "power" => (gain, 0.0),
        _ => (0.0, gain),
    };

    if let Err(e) = vallheru_data::queries::crafting::core_train(
        &app.pool,
        core_id,
        player_id,
        power_add,
        defense_add,
    )
    .await
    {
        tracing::error!(error = %e, "core_train: update");
        return server_error();
    }

    let msg = format!("Wytrenowano {}: +{:.2} {}", creature.name, gain, stat);

    let meta = PageMeta::titled("Stwory - Trening")
        .with_back_link("/core/library", "Wróć")
        .with_flash(Flash::success(msg));
    let base = app.templates.build_context(&ctx, &meta);
    app.templates.render("success.html", &base)
}

/// POST /core/activate/:id/:mode — activate/deactivate a creature.
pub async fn core_activate(
    State(app): State<AppState>,
    Extension(ctx): Extension<RequestContext>,
    Path((core_id, mode)): Path<(i32, String)>,
) -> Response {
    let Some(ref user) = ctx.session_user else {
        return crate::page::redirect("/login");
    };

    #[allow(clippy::cast_possible_truncation)]
    let player_id = user.id as i32;

    let Ok(Some(creature)) =
        vallheru_data::queries::crafting::core_find_creature(&app.pool, core_id, player_id).await
    else {
        return error_page(&app, &ctx, "Nie masz takiego stwora.");
    };

    if creature.status != "Alive" {
        return error_page(&app, &ctx, "Stwór jest martwy.");
    }

    let activate = match mode.as_str() {
        "activate" => true,
        "deactivate" => false,
        _ => return error_page(&app, &ctx, "Nieznany tryb."),
    };

    // Deactivate all first if activating
    if activate {
        log_err!(
            vallheru_data::queries::crafting::core_deactivate_all(&app.pool, player_id).await,
            "core deactivate all"
        );
    }

    if let Err(e) =
        vallheru_data::queries::crafting::core_set_active(&app.pool, core_id, player_id, activate)
            .await
    {
        tracing::error!(error = %e, "core_activate: set active");
        return server_error();
    }

    let msg = format!("Zmieniono status stwora: {}", creature.name);
    let meta = PageMeta::titled("Stwory")
        .with_back_link("/core/library", "Wróć")
        .with_flash(Flash::success(msg));
    let base = app.templates.build_context(&ctx, &meta);
    app.templates.render("success.html", &base)
}

/// GET /core/arena — show arena.
pub async fn core_arena_show(
    State(app): State<AppState>,
    Extension(ctx): Extension<RequestContext>,
) -> Response {
    let Some(ref user) = ctx.session_user else {
        return crate::page::redirect("/login");
    };

    #[allow(clippy::cast_possible_truncation)]
    let player_id = user.id as i32;

    // Find active training creature
    let creatures =
        match vallheru_data::queries::crafting::core_player_creatures(&app.pool, player_id).await {
            Ok(v) => v,
            Err(e) => {
                tracing::error!(error = %e, player_id, "core_player_creatures failed");
                Vec::new()
            }
        };

    let active = creatures
        .iter()
        .find(|c| c.active == "T" && c.status == "Alive");

    let active_creature = active.map(|c| CreatureEntry {
        id: c.id,
        name: c.name.clone(),
        core_type: c.core_type.clone(),
        power: c.power,
        defense: c.defense,
        status: c.status.clone(),
        active: c.active.clone(),
        gender: c.gender.clone(),
        wins: c.wins,
        losses: c.losses,
    });

    // Get possible opponents (same creature type)
    let opponents_rows = if let Some(ac) = active {
        match vallheru_data::queries::crafting::core_arena_opponents(
            &app.pool,
            &ac.core_type,
            player_id,
        )
        .await
        {
            Ok(v) => v,
            Err(e) => {
                tracing::error!(error = %e, player_id, "core_arena_opponents failed");
                Vec::new()
            }
        }
    } else {
        Vec::new()
    };

    let opponents: Vec<OpponentEntry> = opponents_rows
        .iter()
        .map(|o| OpponentEntry {
            id: o.id,
            name: o.name.clone(),
            creature_type: o.core_type.clone(),
            power: o.power,
            defense: o.defense,
            owner_name: String::new(),
        })
        .collect();

    let meta = PageMeta::titled("Stwory - Arena").with_back_link("/core", "Wróć");
    let base = app.templates.build_context(&ctx, &meta);
    let view = CoreArenaView {
        base,
        active_creature,
        opponents,
    };
    app.templates.render_value("core_arena.html", &view)
}

/// POST `/core/arena/fight/:opponent_id` — fight in arena.
#[allow(clippy::too_many_lines)]
pub async fn core_arena_fight(
    State(app): State<AppState>,
    Extension(ctx): Extension<RequestContext>,
    Path(opponent_id): Path<i32>,
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

    let energy_cost = breeding::ARENA_ENERGY_COST;
    if player_row.energy < energy_cost {
        return error_page(&app, &ctx, "Nie masz wystarczająco energii.");
    }

    // Find active training creature
    let creatures =
        match vallheru_data::queries::crafting::core_player_creatures(&app.pool, player_id).await {
            Ok(v) => v,
            Err(e) => {
                tracing::error!(error = %e, player_id, "core_player_creatures failed");
                Vec::new()
            }
        };

    let my_creature = match creatures
        .iter()
        .find(|c| c.active == "T" && c.status == "Alive")
    {
        Some(c) => c.clone(),
        None => return error_page(&app, &ctx, "Nie masz aktywnego stwora do walki."),
    };

    let Ok(Some(opponent)) =
        vallheru_data::queries::crafting::core_find_any(&app.pool, opponent_id).await
    else {
        return error_page(&app, &ctx, "Przeciwnik nie istnieje.");
    };

    // Deduct energy
    if let Err(e) =
        vallheru_data::queries::gathering::deduct_energy(&app.pool, player_id, energy_cost).await
    {
        tracing::error!(error = %e, "core_arena_fight: deduct energy");
        return server_error();
    }

    // Combat
    let my_attack = breeding::core_attack(my_creature.power, opponent.defense);
    let enemy_attack = breeding::core_attack(opponent.power, my_creature.defense);
    let outcome = breeding::arena_outcome(my_attack, enemy_attack);

    let msg = match outcome.cmp(&0) {
        std::cmp::Ordering::Greater => {
            // Win - gain gold and train points
            let gold_cap = breeding::arena_gold_cap(opponent.power, opponent.defense);
            let plat_cap = breeding::arena_platinum_cap(gold_cap);
            let (gold_reward, plat_reward) = {
                let mut rng = rand::thread_rng();
                (
                    rng.gen_range(1..=gold_cap.max(1)),
                    rng.gen_range(0..=plat_cap.max(1)),
                )
            };

            log_err!(
                vallheru_data::queries::crafting::core_arena_result(
                    &app.pool,
                    my_creature.id,
                    true,
                )
                .await,
                "core arena result"
            );

            // Add rewards
            log_err!(
                sqlx::query(
                "UPDATE players SET credits = credits + $1, platinum = platinum + $2 WHERE id = $3",
                )
                .bind(i64::from(gold_reward))
                .bind(plat_reward)
                .bind(player_id)
                .execute(&app.pool)
                    .await,
                "query"
            );

            format!(
                "{} wygrywa z {}! Nagroda: {} złota, {} platyny.",
                my_creature.name, opponent.name, gold_reward, plat_reward
            )
        }
        std::cmp::Ordering::Less => {
            // Lose
            log_err!(
                vallheru_data::queries::crafting::core_arena_result(
                    &app.pool,
                    my_creature.id,
                    false,
                )
                .await,
                "core arena result"
            );
            format!("{} przegrywa z {}.", my_creature.name, opponent.name)
        }
        std::cmp::Ordering::Equal => {
            // Draw
            "Remis!".to_string()
        }
    };

    let meta = PageMeta::titled("Stwory - Arena")
        .with_back_link("/core/arena", "Wróć")
        .with_flash(Flash::success(msg));
    let base = app.templates.build_context(&ctx, &meta);
    app.templates.render("success.html", &base)
}

/// POST /core/heal — heal all dead creatures.
pub async fn core_heal(
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

    let creatures =
        match vallheru_data::queries::crafting::core_player_creatures(&app.pool, player_id).await {
            Ok(v) => v,
            Err(e) => {
                tracing::error!(error = %e, player_id, "core_player_creatures failed");
                Vec::new()
            }
        };

    let dead_stats: Vec<(f64, f64)> = creatures
        .iter()
        .filter(|c| c.status == "Dead")
        .map(|c| (c.power, c.defense))
        .collect();

    if dead_stats.is_empty() {
        return error_page(&app, &ctx, "Nie masz martwych stworów.");
    }

    let gold_cost = breeding::heal_all_gold_cost(&dead_stats);
    let plat_cost = breeding::heal_all_platinum_cost(gold_cost);

    if player_row.credits < gold_cost {
        return error_page(&app, &ctx, "Nie masz wystarczająco złota.");
    }
    if player_row.platinum < plat_cost {
        return error_page(&app, &ctx, "Nie masz wystarczająco platyny.");
    }

    // Deduct costs
    if let Err(e) = vallheru_data::queries::gathering::deduct_currency(
        &app.pool, player_id, gold_cost, plat_cost,
    )
    .await
    {
        tracing::error!(error = %e, "core_heal: deduct currency");
        return server_error();
    }

    // Revive all dead creatures
    if let Err(e) =
        sqlx::query("UPDATE core SET status = 'Alive' WHERE owner = $1 AND status = 'Dead'")
            .bind(player_id)
            .execute(&app.pool)
            .await
    {
        tracing::error!(error = %e, "core_heal: revive");
        return server_error();
    }

    let msg = format!(
        "Wyleczono {} stworów za {} złota i {} platyny.",
        dead_stats.len(),
        gold_cost,
        plat_cost
    );

    let meta = PageMeta::titled("Stwory")
        .with_back_link("/core", "Wróć")
        .with_flash(Flash::success(msg));
    let base = app.templates.build_context(&ctx, &meta);
    app.templates.render("success.html", &base)
}

/// POST /core/release/:id — release a creature.
pub async fn core_release(
    State(app): State<AppState>,
    Extension(ctx): Extension<RequestContext>,
    Path(core_id): Path<i32>,
) -> Response {
    let Some(ref user) = ctx.session_user else {
        return crate::page::redirect("/login");
    };

    #[allow(clippy::cast_possible_truncation)]
    let player_id = user.id as i32;

    let Ok(Some(creature)) =
        vallheru_data::queries::crafting::core_find_creature(&app.pool, core_id, player_id).await
    else {
        return error_page(&app, &ctx, "Nie masz takiego stwora.");
    };

    if let Err(e) =
        vallheru_data::queries::crafting::core_release(&app.pool, core_id, player_id).await
    {
        tracing::error!(error = %e, "core_release");
        return server_error();
    }

    let msg = format!("Wypuściłeś stwora: {}", creature.name);
    let meta = PageMeta::titled("Stwory")
        .with_back_link("/core/library", "Wróć")
        .with_flash(Flash::success(msg));
    let base = app.templates.build_context(&ctx, &meta);
    app.templates.render("success.html", &base)
}
