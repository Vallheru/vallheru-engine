//! Travel handler — stables, destinations, movement, and bandit encounters.
//!
//! Ported from `travel.php`. Players see travel options based on their
//! current location, pick a method (caravan/walk/magic), and move.
//! Non-magic travel has a random chance of bandit encounters, resolved
//! through fight, ransom, or escape.

use axum::{
    Extension, Form,
    extract::{Query, State},
    response::{IntoResponse, Response},
};
use rand::Rng;
use serde::Deserialize;

use crate::log_err;
use crate::middleware::context::RequestContext;
use crate::page::{Flash, FlashKind, PageMeta};
use crate::state::AppState;
use vallheru_domain::combat::encounter::{
    ElementRoll, RandomMonsterInputs, StatVariation, generate_random_monster,
};
use vallheru_domain::combat::formulas::ResistanceStrength;
use vallheru_domain::item::EquipmentType;
use vallheru_domain::location::Location;
use vallheru_domain::travel::{
    Destination, TravelAttempt, TravelError, TravelMethod, available_destinations,
    bandit_encounter_triggers, calculate_ransom, resolve_bandit_escape, travel_cost,
    validate_travel,
};

/// Query params common across travel views.
#[derive(Debug, Deserialize)]
pub struct TravelParams {
    /// Destination id (`gory`, `las`, `city2`, `powrot`).
    pub action: Option<String>,
    /// Travel method (`caravan`, `walk`, `magic`).
    pub step: Option<String>,
}

/// Form submitted from the encounter choice page.
#[derive(Debug, Deserialize)]
pub struct EncounterForm {
    /// One of: `fight`, `pay`, `escape`.
    pub action: String,
}

// ---------------------------------------------------------------------------
// View models
// ---------------------------------------------------------------------------

#[derive(serde::Serialize)]
pub struct TravelHubView {
    #[serde(flatten)]
    pub base: crate::render::RenderContext,
    pub location_name: &'static str,
    pub info_text: &'static str,
    pub destinations: Vec<DestinationLink>,
    /// True if player has maps >= 20 and is not immune (can enter portal).
    pub can_enter_portal: bool,
}

#[derive(serde::Serialize)]
pub struct DestinationLink {
    pub name: &'static str,
    pub param: &'static str,
}

#[derive(serde::Serialize)]
pub struct MethodSelectView {
    #[serde(flatten)]
    pub base: crate::render::RenderContext,
    pub destination_name: &'static str,
    pub dest_param: &'static str,
    pub caravan_cost: i32,
    pub walk_cost: i32,
    pub magic_cost: i32,
}

#[derive(serde::Serialize)]
pub struct TravelResultView {
    #[serde(flatten)]
    pub base: crate::render::RenderContext,
    pub message: String,
    pub next_url: String,
}

/// View for the bandit encounter choice page.
#[derive(serde::Serialize)]
pub struct EncounterView {
    #[serde(flatten)]
    pub base: crate::render::RenderContext,
    pub message: String,
}

// ---------------------------------------------------------------------------
// Handler
// ---------------------------------------------------------------------------

/// GET /travel — stables hub, destination selection, or travel execution.
pub async fn show(
    State(state): State<AppState>,
    Extension(ctx): Extension<RequestContext>,
    Query(params): Query<TravelParams>,
) -> Response {
    let Some(ref user) = ctx.session_user else {
        return crate::page::redirect("/login");
    };

    #[allow(clippy::cast_possible_truncation)]
    let player_id = user.id as i32;

    let player_row =
        match vallheru_data::queries::player::find_player_by_id(&state.pool, player_id).await {
            Ok(Some(row)) => row,
            Ok(None) => return crate::page::redirect("/login"),
            Err(e) => {
                tracing::error!(error = %e, "travel: failed to load player");
                return server_error();
            }
        };

    let Some(location) = Location::from_db(&player_row.location) else {
        return crate::page::redirect("/login");
    };

    // If the player is mid-encounter (location = Podróż), handle that state.
    if location == Location::Travelling {
        return handle_encounter_state(&state, &ctx, &player_row).await;
    }

    // Jail/dungeon check
    if location == Location::Dungeon {
        return error_page(&state, &ctx, "Nie możesz podróżować z tego miejsca.");
    }

    // Step 3: execute travel (destination + method given)
    if let (Some(action), Some(step)) = (&params.action, &params.step) {
        return execute_travel(&state, &ctx, &player_row, location, action, step).await;
    }

    // Step 2: show method selection (destination given, no method)
    if let Some(ref action) = params.action {
        return show_method_select(&state, &ctx, location, action);
    }

    // Step 1: show travel hub with available destinations
    show_hub(&state, &ctx, location, &player_row)
}

/// POST /travel/encounter — handle bandit encounter resolution.
pub async fn encounter_action(
    State(state): State<AppState>,
    Extension(ctx): Extension<RequestContext>,
    Form(form): Form<EncounterForm>,
) -> Response {
    let Some(ref user) = ctx.session_user else {
        return crate::page::redirect("/login");
    };

    #[allow(clippy::cast_possible_truncation)]
    let player_id = user.id as i32;

    let player_row =
        match vallheru_data::queries::player::find_player_by_id(&state.pool, player_id).await {
            Ok(Some(row)) => row,
            Ok(None) => return crate::page::redirect("/login"),
            Err(e) => {
                tracing::error!(error = %e, "travel encounter: failed to load player");
                return server_error();
            }
        };

    if player_row.location != "Podróż" || player_row.fight == 0 {
        return error_page(&state, &ctx, "Nie jesteś w trakcie podróży.");
    }

    match form.action.as_str() {
        "fight" => {
            // Redirect to the PvE battle system — the fight field is already
            // set to the temporary bandit monster ID.
            crate::page::redirect("/battle/pve")
        }
        "pay" => handle_pay_ransom(&state, &ctx, &player_row).await,
        "escape" => handle_escape(&state, &ctx, &player_row).await,
        _ => error_page(&state, &ctx, "Nieprawidłowa akcja."),
    }
}

// ---------------------------------------------------------------------------
// Sub-handlers
// ---------------------------------------------------------------------------

fn show_hub(
    state: &AppState,
    ctx: &RequestContext,
    location: Location,
    player_row: &vallheru_data::queries::player::PlayerRow,
) -> Response {
    let dests = available_destinations(location);
    let destinations: Vec<DestinationLink> = dests
        .iter()
        .map(|d| DestinationLink {
            name: d.display_name(),
            param: d.param(),
        })
        .collect();

    let (location_name, info_text) = hub_text(location);

    let can_enter_portal =
        location == Location::Altara && player_row.maps >= 20 && !player_row.immune;

    let meta = PageMeta::titled("Stajnie");
    let base = state.templates.build_context(ctx, &meta);

    let view = TravelHubView {
        base,
        location_name,
        info_text,
        destinations,
        can_enter_portal,
    };

    state.templates.render_value("travel.html", &view)
}

fn show_method_select(
    state: &AppState,
    ctx: &RequestContext,
    location: Location,
    action: &str,
) -> Response {
    let Some(dest) = Destination::from_param(action) else {
        return error_page(state, ctx, "Nieprawidłowy cel podróży.");
    };

    let cc = travel_cost(TravelMethod::Caravan, location, dest);
    let wc = travel_cost(TravelMethod::Walk, location, dest);
    let mc = travel_cost(TravelMethod::MagicPortal, location, dest);

    let meta = PageMeta::titled("Podróż");
    let base = state.templates.build_context(ctx, &meta);

    let view = MethodSelectView {
        base,
        destination_name: dest.display_name(),
        dest_param: dest.param(),
        caravan_cost: cc,
        walk_cost: wc,
        magic_cost: mc,
    };

    state.templates.render_value("travel_method.html", &view)
}

async fn execute_travel(
    state: &AppState,
    ctx: &RequestContext,
    player_row: &vallheru_data::queries::player::PlayerRow,
    location: Location,
    action: &str,
    step: &str,
) -> Response {
    let Some(dest) = Destination::from_param(action) else {
        return error_page(state, ctx, "Nieprawidłowy cel podróży.");
    };
    let Some(method) = TravelMethod::from_param(step) else {
        return error_page(state, ctx, "Nieprawidłowy sposób podróży.");
    };

    let result = validate_travel(&TravelAttempt {
        from: location,
        dest,
        method,
        hp: player_row.hp,
        fight_id: player_row.fight,
        is_immune: player_row.immune,
        credits: player_row.credits,
        energy: player_row.energy,
    });

    let cost = match result {
        Ok(cost) => cost,
        Err(e) => {
            let msg = travel_error_message(&e);
            return error_page(state, ctx, msg);
        }
    };

    // Bandit encounter roll (only for non-magic, non-Podróż origins).
    let roll = rand::thread_rng().gen_range(1..=100);
    if bandit_encounter_triggers(method, roll) {
        return trigger_encounter(state, ctx, player_row, dest, method, cost).await;
    }

    // No encounter — proceed with the move.
    complete_travel(state, ctx, player_row, dest, method, cost).await
}

// ---------------------------------------------------------------------------
// Encounter sub-handlers
// ---------------------------------------------------------------------------

/// Generate a bandit, persist encounter state, and show the choice page.
#[allow(clippy::too_many_lines)]
async fn trigger_encounter(
    state: &AppState,
    ctx: &RequestContext,
    player_row: &vallheru_data::queries::player::PlayerRow,
    dest: Destination,
    method: TravelMethod,
    cost: i32,
) -> Response {
    let player_id = player_row.id;

    // Load player stats and equipment for random monster generation.
    let player_stats = vallheru_data::queries::player::load_stats(&state.pool, player_id)
        .await
        .unwrap_or_default();
    let player_skills = vallheru_data::queries::player::load_skills(&state.pool, player_id)
        .await
        .unwrap_or_default();
    let equipped = vallheru_data::queries::item::find_equipped_items(&state.pool, player_id)
        .await
        .unwrap_or_default();

    let equipped_domain: Vec<_> = equipped.iter().map(|e| e.clone().into_domain()).collect();

    let weapon = equipped_domain
        .iter()
        .find(|e| e.equipment_type == EquipmentType::Weapon);
    let bow = equipped_domain
        .iter()
        .find(|e| e.equipment_type == EquipmentType::Bow);
    let arrows = equipped_domain
        .iter()
        .find(|e| e.equipment_type == EquipmentType::Arrows);

    let inputs = RandomMonsterInputs {
        name: "Bandyta".to_owned(),
        player_hp: player_row.hp,
        condition_mod: find_stat(&player_stats, "condition"),
        speed_mod: find_stat(&player_stats, "speed"),
        strength_mod: find_stat(&player_stats, "strength"),
        intelligence_mod: find_stat(&player_stats, "inteli"),
        agility_mod: find_stat(&player_stats, "agility"),
        wisdom_mod: find_stat(&player_stats, "wisdom"),
        dodge_skill: find_skill(&player_skills, "dodge"),
        attack_skill: find_skill(&player_skills, "attack"),
        shoot_skill: find_skill(&player_skills, "shoot"),
        magic_skill: find_skill(&player_skills, "magic"),
        has_weapon: weapon.is_some(),
        has_bow: bow.is_some(),
        weapon_power: weapon.map_or(0.0, |w| f64::from(w.power)),
        bow_power: bow.map_or(0.0, |b| f64::from(b.power)),
        arrow_power: arrows.map_or(0.0, |a| f64::from(a.power)),
    };

    let (variations, element_roll) = {
        let mut rng = rand::thread_rng();
        let variations: [StatVariation; 5] = std::array::from_fn(|_| StatVariation {
            pct_0_to_15: rng.gen_range(0..=15),
            positive: rng.gen_bool(0.5),
        });
        let element_roll = ElementRoll {
            chance_roll: rng.gen_range(1..=100),
            element_index: rng.gen_range(0..=3),
        };
        (variations, element_roll)
    };

    let bandit = generate_random_monster(&inputs, &variations, &element_roll);

    // Serialize element/resistance to DB strings.
    let dmgtype_db = bandit.dmgtype.to_spell_code();
    let resistance_db = format!(
        "{};{}",
        bandit.resistance.element.to_spell_code(),
        resistance_strength_str(bandit.resistance.strength),
    );

    // Insert temporary monster into catalog.
    let monster_id = match vallheru_data::queries::travel::insert_travel_monster(
        &state.pool,
        &bandit.name,
        bandit.level,
        bandit.hp,
        bandit.strength,
        bandit.agility,
        bandit.speed,
        bandit.endurance,
        dmgtype_db,
        &resistance_db,
    )
    .await
    {
        Ok(id) => id,
        Err(e) => {
            tracing::error!(error = %e, "travel: failed to insert bandit monster");
            return server_error();
        }
    };

    // Record encounter state.
    let method_str = match method {
        TravelMethod::Caravan => "caravan",
        TravelMethod::Walk => "walk",
        TravelMethod::MagicPortal => "magic",
    };

    if let Err(e) = vallheru_data::queries::travel::insert_travel_encounter(
        &state.pool,
        player_id,
        dest.param(),
        method_str,
        cost,
        monster_id,
    )
    .await
    {
        tracing::error!(error = %e, "travel: failed to insert encounter");
        return server_error();
    }

    // Place player in travelling state with fight set.
    if let Err(e) =
        vallheru_data::queries::travel::set_player_travelling(&state.pool, player_id, monster_id)
            .await
    {
        tracing::error!(error = %e, "travel: failed to set travelling");
        return server_error();
    }

    // Show encounter choice page.
    show_encounter_choices(state, ctx)
}

/// When the player visits /travel and is already at "Podróż".
async fn handle_encounter_state(
    state: &AppState,
    ctx: &RequestContext,
    player_row: &vallheru_data::queries::player::PlayerRow,
) -> Response {
    let player_id = player_row.id;

    // Load encounter record.
    let enc =
        match vallheru_data::queries::travel::load_travel_encounter(&state.pool, player_id).await {
            Ok(Some(e)) => e,
            Ok(None) => {
                // No encounter record but location is Podróż — clean up and send home.
                log_err!(
                    sqlx::query("UPDATE players SET miejsce = 'Altara', fight = 0 WHERE id = $1")
                        .bind(player_id)
                        .execute(&state.pool)
                        .await,
                    "query"
                );
                return crate::page::redirect("/city");
            }
            Err(e) => {
                tracing::error!(error = %e, "travel: failed to load encounter");
                return server_error();
            }
        };

    // If still in combat, show encounter choices (they came back without fighting).
    if player_row.fight > 0 {
        return show_encounter_choices(state, ctx);
    }

    // Fight resolved (fight == 0). Complete the travel or handle death.
    if player_row.hp <= 0 {
        // Player died in combat — cleanup and move to Altara.
        log_err!(
            vallheru_data::queries::travel::delete_travel_encounter(&state.pool, player_id).await,
            "delete travel encounter"
        );
        log_err!(
            sqlx::query("UPDATE players SET miejsce = 'Altara' WHERE id = $1")
                .bind(player_id)
                .execute(&state.pool)
                .await,
            "query"
        );
        return error_page(
            state,
            ctx,
            "Walka z bandytami zakończyła się dla Ciebie niepomyślnie. Budzisz się w Altarze.",
        );
    }

    // Player won — complete the original travel.
    let dest = Destination::from_param(&enc.destination);
    let method = TravelMethod::from_param(&enc.method);

    let (Some(dest), Some(method)) = (dest, method) else {
        // Corrupt encounter record — cleanup.
        log_err!(
            vallheru_data::queries::travel::delete_travel_encounter(&state.pool, player_id).await,
            "delete travel encounter"
        );
        log_err!(
            sqlx::query("UPDATE players SET miejsce = 'Altara', fight = 0 WHERE id = $1")
                .bind(player_id)
                .execute(&state.pool)
                .await,
            "query"
        );
        return crate::page::redirect("/city");
    };

    // Cleanup encounter before completing travel.
    log_err!(
        vallheru_data::queries::travel::delete_travel_encounter(&state.pool, player_id).await,
        "delete travel encounter"
    );

    complete_travel(state, ctx, player_row, dest, method, enc.travel_cost).await
}

/// Process the "pay ransom" encounter action.
async fn handle_pay_ransom(
    state: &AppState,
    ctx: &RequestContext,
    player_row: &vallheru_data::queries::player::PlayerRow,
) -> Response {
    let player_id = player_row.id;

    let Ok(Some(enc)) =
        vallheru_data::queries::travel::load_travel_encounter(&state.pool, player_id).await
    else {
        return error_page(state, ctx, "Nie jesteś w trakcie podróży.");
    };

    let method = TravelMethod::from_param(&enc.method).unwrap_or(TravelMethod::Walk);

    // Sum of all six stat trained values.
    let player_stats = vallheru_data::queries::player::load_stats(&state.pool, player_id)
        .await
        .unwrap_or_default();
    let stat_sum = [
        "strength",
        "agility",
        "speed",
        "wisdom",
        "inteli",
        "condition",
    ]
    .iter()
    .map(|k| find_stat(&player_stats, k))
    .sum::<i32>();

    let roll = rand::thread_rng().gen_range(1..=100);
    let result = calculate_ransom(method, enc.travel_cost, stat_sum, player_row.credits, roll);

    match result {
        vallheru_domain::travel::RansomResult::Pay(ransom) => {
            // Deduct ransom, clear fight, and complete travel.
            log_err!(
                sqlx::query("UPDATE players SET credits = credits - $1, fight = 0 WHERE id = $2")
                    .bind(ransom)
                    .bind(player_id)
                    .execute(&state.pool)
                    .await,
                "deduct ransom"
            );

            let dest = Destination::from_param(&enc.destination);
            let method_parsed = TravelMethod::from_param(&enc.method);
            log_err!(
                vallheru_data::queries::travel::delete_travel_encounter(&state.pool, player_id)
                    .await,
                "delete travel encounter"
            );

            let (Some(dest), Some(method_p)) = (dest, method_parsed) else {
                return crate::page::redirect("/city");
            };

            let message = format!(
                "Płacisz bandytom {ransom} sztuk złota i puszczają Ciebie wolno. \
                 Dalsza droga przebiega bez niespodzianek."
            );

            finish_travel_with_message(
                state,
                ctx,
                player_row,
                dest,
                method_p,
                enc.travel_cost,
                &message,
            )
            .await
        }
        vallheru_domain::travel::RansomResult::ForceFight => {
            // Can't pay — fall back to fight.
            let meta = PageMeta::titled("Podróż").with_flash(Flash {
                kind: FlashKind::Error,
                message: "Nie udało Ci się przekonać bandytów złotem. Rozpoczyna się walka!"
                    .to_owned(),
            });
            let base = state.templates.build_context(ctx, &meta);
            let view = EncounterView {
                base,
                message: "Nie udało Ci się przekonać bandytów złotem. Rozpoczyna się walka!"
                    .to_owned(),
            };
            state.templates.render_value("travel_encounter.html", &view)
        }
    }
}

/// Process the "escape" encounter action.
async fn handle_escape(
    state: &AppState,
    ctx: &RequestContext,
    player_row: &vallheru_data::queries::player::PlayerRow,
) -> Response {
    let player_id = player_row.id;

    let Ok(Some(enc)) =
        vallheru_data::queries::travel::load_travel_encounter(&state.pool, player_id).await
    else {
        return error_page(state, ctx, "Nie jesteś w trakcie podróży.");
    };

    let player_stats = vallheru_data::queries::player::load_stats(&state.pool, player_id)
        .await
        .unwrap_or_default();
    let player_skills = vallheru_data::queries::player::load_skills(&state.pool, player_id)
        .await
        .unwrap_or_default();

    let player_speed = find_stat(&player_stats, "speed");
    let perception = find_skill(&player_skills, "perception");

    let (bandit_rolls, player_roll, bandit_extra) = {
        let mut rng = rand::thread_rng();
        let br: [i32; 4] = std::array::from_fn(|_| rng.gen_range(1..=75));
        let pr = rng.gen_range(1..=100);
        let be = rng.gen_range(1..=100);
        (br, pr, be)
    };

    let escape = resolve_bandit_escape(
        player_speed,
        perception,
        player_roll,
        &bandit_rolls,
        bandit_extra,
    );

    // Grant speed stat XP and perception skill XP regardless of outcome.
    let xp = escape.xp;
    #[allow(clippy::cast_possible_truncation)]
    let half_xp = (xp.max(1) / 2) as i32;
    log_err!(
        vallheru_data::queries::player::add_stat_xp(&state.pool, player_id, "speed", half_xp).await,
        "add speed stat xp"
    );
    log_err!(
        vallheru_data::queries::player::add_skill_xp(&state.pool, player_id, "perception", half_xp)
            .await,
        "add perception skill xp"
    );

    if escape.escaped {
        // Clear fight, complete travel.
        log_err!(
            sqlx::query("UPDATE players SET fight = 0 WHERE id = $1")
                .bind(player_id)
                .execute(&state.pool)
                .await,
            "query"
        );

        let dest = Destination::from_param(&enc.destination);
        let method = TravelMethod::from_param(&enc.method);
        log_err!(
            vallheru_data::queries::travel::delete_travel_encounter(&state.pool, player_id).await,
            "delete travel encounter"
        );

        let (Some(dest), Some(method)) = (dest, method) else {
            return crate::page::redirect("/city");
        };

        let message = format!(
            "Udało Ci się uciec przed bandytami. Zdobywasz {xp} doświadczenia. \
             Dalsza droga przebiega bez niespodzianek."
        );

        finish_travel_with_message(
            state,
            ctx,
            player_row,
            dest,
            method,
            enc.travel_cost,
            &message,
        )
        .await
    } else {
        // Failed escape — must fight.
        let meta = PageMeta::titled("Podróż").with_flash(Flash {
            kind: FlashKind::Error,
            message: "Nie udało Ci się uciec przed bandytami. Rozpoczyna się walka!".to_owned(),
        });
        let base = state.templates.build_context(ctx, &meta);
        let view = EncounterView {
            base,
            message: "Nie udało Ci się uciec przed bandytami. Rozpoczyna się walka!".to_owned(),
        };
        state.templates.render_value("travel_encounter.html", &view)
    }
}

// ---------------------------------------------------------------------------
// Shared travel completion
// ---------------------------------------------------------------------------

/// Complete the travel move: deduct cost, change location, show arrival.
async fn complete_travel(
    state: &AppState,
    ctx: &RequestContext,
    player_row: &vallheru_data::queries::player::PlayerRow,
    dest: Destination,
    method: TravelMethod,
    cost: i32,
) -> Response {
    let new_location = dest.target_location().to_db();
    let player_id = player_row.id;

    let db_result = match method {
        TravelMethod::Caravan | TravelMethod::MagicPortal => {
            vallheru_data::queries::travel::move_player_deduct_gold(
                &state.pool,
                player_id,
                new_location,
                cost,
            )
            .await
        }
        TravelMethod::Walk => {
            vallheru_data::queries::travel::move_player_deduct_energy(
                &state.pool,
                player_id,
                new_location,
                cost,
            )
            .await
        }
    };

    if let Err(e) = db_result {
        tracing::error!(error = %e, "travel: failed to update player");
        return server_error();
    }

    let next_url = destination_url(dest);

    let message = format!(
        "Podróż przebiegała spokojnie, po pewnym czasie widzisz przed sobą cel \
         swej podróży. Dotarłeś do {}.",
        dest.arrival_label()
    );

    let meta = PageMeta::titled("Podróż");
    let base = state.templates.build_context(ctx, &meta);

    let view = TravelResultView {
        base,
        message,
        next_url,
    };

    state.templates.render_value("travel_result.html", &view)
}

/// Complete travel after encounter resolution with a custom message.
async fn finish_travel_with_message(
    state: &AppState,
    ctx: &RequestContext,
    player_row: &vallheru_data::queries::player::PlayerRow,
    dest: Destination,
    method: TravelMethod,
    cost: i32,
    message: &str,
) -> Response {
    let new_location = dest.target_location().to_db();
    let player_id = player_row.id;

    let db_result = match method {
        TravelMethod::Caravan | TravelMethod::MagicPortal => {
            vallheru_data::queries::travel::move_player_deduct_gold(
                &state.pool,
                player_id,
                new_location,
                cost,
            )
            .await
        }
        TravelMethod::Walk => {
            vallheru_data::queries::travel::move_player_deduct_energy(
                &state.pool,
                player_id,
                new_location,
                cost,
            )
            .await
        }
    };

    if let Err(e) = db_result {
        tracing::error!(error = %e, "travel: failed to update player");
        return server_error();
    }

    let next_url = destination_url(dest);

    let meta = PageMeta::titled("Podróż");
    let base = state.templates.build_context(ctx, &meta);

    let view = TravelResultView {
        base,
        message: message.to_owned(),
        next_url,
    };

    state.templates.render_value("travel_result.html", &view)
}

/// Show the encounter choice page (fight / pay / escape).
fn show_encounter_choices(state: &AppState, ctx: &RequestContext) -> Response {
    let meta = PageMeta::titled("Podróż");
    let base = state.templates.build_context(ctx, &meta);

    let view = EncounterView {
        base,
        message: "Podróżując do celu, nagle zobaczyłeś jak z pobocza drogi \
                  wyskakują na Was bandyci. Masz do wyboru:"
            .to_owned(),
    };

    state.templates.render_value("travel_encounter.html", &view)
}

// ---------------------------------------------------------------------------
// Helpers
// ---------------------------------------------------------------------------

fn hub_text(location: Location) -> (&'static str, &'static str) {
    match location {
        Location::Altara => (
            "Altara",
            "Witaj w Stajniach. Stąd możesz wyruszyć do innych miejsc świata Vallheru.",
        ),
        Location::Ardulith => (
            "Ardulith",
            "Witaj w Stajniach. Stąd możesz wyruszyć do innych miejsc świata Vallheru.",
        ),
        Location::Forest => (
            "Las Avantiel",
            "Witaj w Stajniach. Tędy możesz wrócić do stolicy Vallheru, Altary.",
        ),
        Location::Mountains => (
            "Góry Kazad-nar",
            "Witaj w Stajniach. Tędy możesz wrócić do stolicy Vallheru, Altary.",
        ),
        _ => ("Stajnie", "Nie możesz stąd podróżować."),
    }
}

fn travel_error_message(err: &TravelError) -> &'static str {
    match err {
        TravelError::MovementDenied(d) => match d {
            vallheru_domain::location::MovementDenied::Dead => {
                "Nie możesz podróżować, ponieważ jesteś martwy!"
            }
            vallheru_domain::location::MovementDenied::InCombat => {
                "Nie możesz podróżować podczas walki!"
            }
            vallheru_domain::location::MovementDenied::Immune => {
                "Nie możesz opuścić miasta podczas immunitetu."
            }
            vallheru_domain::location::MovementDenied::InDungeon => {
                "Nie możesz podróżować z lochów."
            }
            vallheru_domain::location::MovementDenied::OnAdventure => {
                "Musisz najpierw zakończyć przygodę."
            }
            vallheru_domain::location::MovementDenied::AlreadyThere => "Jesteś już w tym miejscu.",
            vallheru_domain::location::MovementDenied::InvalidRoute => {
                "Nie możesz dotrzeć do tego miejsca stąd."
            }
        },
        TravelError::InsufficientGold { .. } => "Nie masz tyle pieniędzy!",
        TravelError::InsufficientEnergy { .. } => "Nie masz energii aby podróżować!",
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

fn destination_url(dest: Destination) -> String {
    match dest {
        Destination::Mountains => "/mountains".to_owned(),
        Destination::Forest => "/forest".to_owned(),
        Destination::Ardulith | Destination::Altara => "/city".to_owned(),
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

fn resistance_strength_str(s: ResistanceStrength) -> &'static str {
    match s {
        ResistanceStrength::None => "none",
        ResistanceStrength::Weak => "weak",
        ResistanceStrength::Medium => "medium",
        ResistanceStrength::Strong => "strong",
    }
}

fn server_error() -> Response {
    (
        axum::http::StatusCode::INTERNAL_SERVER_ERROR,
        "Internal error",
    )
        .into_response()
}
