//! Combat handlers: exploration, `PvE` battles, `PvP` arena, hunter guild.
//!
//! Ported from `explore.php`, `battle.php`, and `hunters.php`.

use axum::{
    Extension, Form,
    extract::{Path, State},
    response::Response,
};
use rand::Rng;

use crate::log_err;
use crate::middleware::context::RequestContext;
use crate::page::{Flash, FlashKind, PageMeta};
use crate::state::AppState;
use vallheru_data::queries::{combat, player as player_q, settings as settings_q};
use vallheru_domain::combat::{
    battle::{
        self, AttackRolls, BattleOutcome, BattleState, EscapeRolls, MonsterAttackRoll,
        MonsterCombatState, MonsterTurnRolls, SpellRolls,
    },
    encounter::{
        self, FALLBACK_TIER, Monster, PlayerCombatProfile, difficulty_tier, encounter_region,
        encounter_reward, monster_matches_tier, player_power_level,
    },
    exploration::{self, ExploreRegion, MapContext, StepEvent, StepRolls},
    formulas::{self, AttackStance, DamageContext},
};
use vallheru_domain::item::{
    Element, EquipmentType, OwnedEquipment, Spell, SpellStatus, SpellType,
};
use vallheru_domain::player::Class;
use vallheru_domain::player::skills::PlayerSkill;
use vallheru_domain::player::stats::PlayerStat;

// =========================================================================
// View models
// =========================================================================

#[derive(serde::Serialize)]
struct ExploreView {
    #[serde(flatten)]
    base: crate::render::RenderContext,
    state: String,
    energy: f64,
    // Menu state
    has_fight: bool,
    fight_monster_name: String,
    region_description: String,
    // Results state
    steps_taken: f64,
    found_items: Vec<String>,
    bridge_event: String,
    encounter_name: String,
    // Escape state
    monster_name: String,
    xp_gain: i64,
    back_url: String,
}

#[derive(serde::Deserialize)]
pub struct ExploreForm {
    pub amount: Option<f64>,
}

#[derive(serde::Serialize)]
#[allow(clippy::struct_excessive_bools)]
struct BattleView {
    #[serde(flatten)]
    base: crate::render::RenderContext,
    outcome: String,
    round: i32,
    action_points: i32,
    player_hp: i32,
    player_max_hp: i32,
    player_mana: i32,
    monster_name: String,
    monster_hp: i32,
    monster_max_hp: i32,
    battle_log: Vec<String>,
    has_weapon: bool,
    has_bow: bool,
    has_spell: bool,
    has_def_spell: bool,
    has_potion: bool,
    xp_gain: i64,
    gold_gain: i64,
    loot_name: String,
    continue_url: String,
}

#[derive(serde::Deserialize)]
pub struct BattleActionForm {
    pub action: Option<String>,
}

#[derive(serde::Serialize)]
struct ArenaView {
    #[serde(flatten)]
    base: crate::render::RenderContext,
    opponents: Vec<combat::ArenaOpponent>,
}

#[derive(serde::Serialize)]
struct PvpResultView {
    #[serde(flatten)]
    base: crate::render::RenderContext,
    outcome: String,
    opponent_name: String,
    gold_gain: i64,
    battle_log: Vec<String>,
}

#[derive(serde::Serialize)]
struct HuntersView {
    #[serde(flatten)]
    base: crate::render::RenderContext,
    state: String,
    has_quest: bool,
    monsters_city1: Vec<combat::BestiaryEntry>,
    monsters_city2: Vec<combat::BestiaryEntry>,
    max_rows: usize,
    monster_name: String,
    monster_description: String,
    quest_description: String,
    quest_message: String,
}

// =========================================================================
// Exploration handlers
// =========================================================================

/// GET /explore — show exploration menu.
pub async fn explore_show(
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

    if player_row.location == "Altara" || player_row.location == "Ardulith" {
        return error_page(
            &app,
            &ctx,
            "Musisz opuścić miasto, żeby poszukiwać przygód.",
        );
    }

    let (has_fight, fight_monster_name) = if player_row.fight > 0 {
        let name = match combat::load_monster(&app.pool, player_row.fight).await {
            Ok(Some(m)) => m.name,
            _ => "Nieznany potwór".to_owned(),
        };
        (true, name)
    } else {
        (false, String::new())
    };

    let region_description = match player_row.location.as_str() {
        "Las" => {
            "Przed sobą widzisz ścianę lasu Avantiel. Wąska ścieżka prowadząca w głąb lasu niknie już po chwili za zakrętem. Każde zwiedzanie kosztuje 0,5 energii."
        }
        "Góry" => {
            "Stoisz u stóp potężnego pasma górskiego. Kamienista ścieżka prowadzi w głąb gór. Każde zwiedzanie kosztuje 0,5 energii."
        }
        _ => "Możesz zwiedzać okolicę.",
    };

    let meta = PageMeta::titled("Poszukiwania")
        .with_back_link(back_url_for_location(&player_row.location), "Wróć");
    let base = app.templates.build_context(&ctx, &meta);

    let view = ExploreView {
        base,
        state: if has_fight {
            "encounter".to_owned()
        } else {
            "menu".to_owned()
        },
        energy: player_row.energy,
        has_fight,
        fight_monster_name,
        region_description: region_description.to_owned(),
        steps_taken: 0.0,
        found_items: Vec::new(),
        bridge_event: String::new(),
        encounter_name: String::new(),
        monster_name: String::new(),
        xp_gain: 0,
        back_url: String::new(),
    };
    app.templates.render_value("explore.html", &view)
}

/// POST /explore — do exploration walk.
#[allow(clippy::too_many_lines)]
pub async fn explore_walk(
    State(app): State<AppState>,
    Extension(ctx): Extension<RequestContext>,
    Form(form): Form<ExploreForm>,
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

    if player_row.location == "Altara" || player_row.location == "Ardulith" {
        return error_page(
            &app,
            &ctx,
            "Musisz opuścić miasto, żeby poszukiwać przygód.",
        );
    }

    if player_row.hp <= 0 {
        return error_page(
            &app,
            &ctx,
            "Nie możesz poszukiwać przygód, ponieważ nie żyjesz!",
        );
    }

    if player_row.fight > 0 {
        return error_page(&app, &ctx, "Musisz najpierw pokonać potwora lub uciec!");
    }

    let amount = match form.amount {
        Some(a) if a >= 0.5 => a,
        _ => {
            return error_page(
                &app,
                &ctx,
                "Podaj ile energii chcesz przeznaczyć (min 0.5).",
            );
        }
    };

    if amount > player_row.energy {
        return error_page(&app, &ctx, "Nie masz tyle energii!");
    }

    let region = match player_row.location.as_str() {
        "Las" => ExploreRegion::Forest,
        _ => ExploreRegion::Mountains,
    };

    let maps_setting = match settings_q::get_setting(&app.pool, "maps").await {
        Ok(Some(r)) => r.value.and_then(|v| v.parse::<i32>().ok()).unwrap_or(0),
        Ok(None) => 0,
        Err(e) => {
            tracing::error!(error = ?e, "failed to load maps setting");
            0
        }
    };

    #[allow(clippy::cast_possible_truncation)]
    let step_count = (amount * 2.0).floor() as i32;
    let mut found_herbs = [0i32; 4];
    let mut found_gold: i64 = 0;
    let mut found_meteor = 0i32;
    let mut found_energy = 0i32;
    let mut found_maps = 0i32;
    let mut found_astral = 0i32;
    let mut encounter_monster_id: Option<i32> = None;
    let mut bridge_event = String::new();
    let mut actual_steps = step_count;
    let mut remaining_maps = maps_setting;

    let monsters =
        match combat::load_monsters_by_location(&app.pool, encounter_region(&player_row.location))
            .await
        {
            Ok(v) => v,
            Err(e) => {
                tracing::error!(error = ?e, "failed to load monsters for exploration");
                Vec::new()
            }
        };

    let monster_domains: Vec<Monster> = monsters
        .into_iter()
        .map(vallheru_data::queries::combat::MonsterRow::into_domain)
        .collect();

    let stats = match player_q::load_stats(&app.pool, player_id).await {
        Ok(v) => v,
        Err(e) => {
            tracing::error!(player_id, error = ?e, "failed to load stats for explore");
            Vec::new()
        }
    };
    let skills = match player_q::load_skills(&app.pool, player_id).await {
        Ok(v) => v,
        Err(e) => {
            tracing::error!(player_id, error = ?e, "failed to load skills for explore");
            Vec::new()
        }
    };
    let equipped =
        match vallheru_data::queries::item::find_equipped_items(&app.pool, player_id).await {
            Ok(v) => v,
            Err(e) => {
                tracing::error!(player_id, error = ?e, "failed to load equipped items for explore");
                Vec::new()
            }
        };

    let equipped_domain: Vec<OwnedEquipment> =
        equipped.iter().map(|e| e.clone().into_domain()).collect();
    let has_weapon = equipped_domain
        .iter()
        .any(|e| e.equipment_type == EquipmentType::Weapon);
    let has_second = equipped_domain
        .iter()
        .any(|e| e.equipment_type == EquipmentType::Shield);
    let has_bow = equipped_domain
        .iter()
        .any(|e| e.equipment_type == EquipmentType::Bow);

    let profile = PlayerCombatProfile {
        hp: player_row.hp,
        condition_mod: find_stat(&stats, "condition"),
        speed_mod: find_stat(&stats, "speed"),
        agility_mod: find_stat(&stats, "agility"),
        strength_mod: find_stat(&stats, "strength"),
        wisdom_mod: find_stat(&stats, "wisdom"),
        intelligence_mod: find_stat(&stats, "inteli"),
        dodge_skill: find_skill(&skills, "dodge"),
        attack_skill: find_skill(&skills, "attack"),
        shoot_skill: find_skill(&skills, "shoot"),
        magic_skill: find_skill(&skills, "magic"),
        has_weapon,
        has_second_weapon: has_second,
        has_bow,
    };
    let p_power = player_power_level(&profile);

    {
        let mut rng = rand::thread_rng();

        for step in 1..step_count {
            let rolls = StepRolls {
                event_roll: match region {
                    ExploreRegion::Forest => rng.gen_range(1..=19),
                    ExploreRegion::Mountains => rng.gen_range(1..=20),
                },
                amount_roll: rng.gen_range(1..=1000),
                map_chance_roll: rng.gen_range(1..=50),
                astral_chance_roll: rng.gen_range(1..=100),
            };

            let map_ctx = MapContext {
                maps_available: remaining_maps,
                player_maps: i32::from(player_row.maps) + found_maps,
                is_hero: player_row.rank == "Bohater",
            };

            let event = exploration::resolve_step(region, &rolls, &map_ctx, player_row.bridge);

            match event {
                StepEvent::Nothing => {}
                StepEvent::Gold(n) => found_gold += i64::from(n),
                StepEvent::Meteor(n) => found_meteor += n,
                StepEvent::Energy(n) => found_energy += n,
                StepEvent::Herbs(herbs) => {
                    for (i, v) in herbs.iter().enumerate() {
                        found_herbs[i] += v;
                    }
                }
                StepEvent::Map => {
                    found_maps += 1;
                    remaining_maps -= 1;
                }
                StepEvent::Astral => found_astral += 1,
                StepEvent::MonsterEncounter => {
                    let tier_roll = rng.gen_range(1..=100);
                    let tier = difficulty_tier(tier_roll);
                    let matching: Vec<&Monster> = monster_domains
                        .iter()
                        .filter(|m| monster_matches_tier(m, p_power, &tier))
                        .collect();

                    let candidates = if matching.is_empty() {
                        monster_domains
                            .iter()
                            .filter(|m| monster_matches_tier(m, p_power, &FALLBACK_TIER))
                            .collect::<Vec<_>>()
                    } else {
                        matching
                    };

                    if let Some(monster) = candidates.first() {
                        encounter_monster_id = Some(monster.id);
                        actual_steps = step;
                        break;
                    }
                }
                StepEvent::BridgeOfDeath => {
                    if !player_row.bridge {
                        "Wąska ścieżka prowadzi na most śmierci...".clone_into(&mut bridge_event);
                        actual_steps = step;
                        break;
                    }
                }
            }
        }
    }

    // Persist results
    let energy_cost = (f64::from(actual_steps) / 2.0) - f64::from(found_energy);
    let energy_cost = energy_cost.max(0.0);

    if found_gold > 0 {
        log_err!(
            combat::add_gold(&app.pool, player_id, found_gold).await,
            "add gold"
        );
    }
    log_err!(
        combat::deduct_energy(&app.pool, player_id, energy_cost).await,
        "deduct energy"
    );

    if found_herbs.iter().any(|h| *h > 0) {
        log_err!(
            combat::add_herbs(
                &app.pool,
                player_id,
                found_herbs[0],
                found_herbs[1],
                found_herbs[2],
                found_herbs[3],
            )
            .await,
            "add herbs"
        );
    }

    if found_meteor > 0 {
        log_err!(
            combat::add_meteors(&app.pool, player_id, found_meteor).await,
            "add meteors"
        );
    }

    if found_maps > 0 {
        #[allow(clippy::cast_possible_truncation)]
        let new_maps = player_row.maps + found_maps as i16;
        log_err!(
            combat::set_player_maps(&app.pool, player_id, new_maps).await,
            "set player maps"
        );
        log_err!(
            settings_q::upsert_setting(&app.pool, "maps", &remaining_maps.to_string()).await,
            "upsert setting"
        );
    }

    let encounter_name = if let Some(mid) = encounter_monster_id {
        log_err!(
            combat::set_player_fight(&app.pool, player_id, mid).await,
            "set player fight"
        );
        match combat::load_monster(&app.pool, mid).await {
            Ok(Some(m)) => m.name,
            Ok(None) => String::new(),
            Err(e) => {
                tracing::error!(monster_id = mid, error = ?e, "failed to load encounter monster name");
                String::new()
            }
        }
    } else {
        String::new()
    };

    let mut found_items_list = Vec::new();
    if found_gold > 0 {
        found_items_list.push(format!("{found_gold} sztuk złota"));
    }
    if found_meteor > 0 {
        found_items_list.push(format!("{found_meteor} meteorytów"));
    }
    if found_herbs[0] > 0 {
        found_items_list.push(format!("{} Illani", found_herbs[0]));
    }
    if found_herbs[1] > 0 {
        found_items_list.push(format!("{} Illanias", found_herbs[1]));
    }
    if found_herbs[2] > 0 {
        found_items_list.push(format!("{} Nutari", found_herbs[2]));
    }
    if found_herbs[3] > 0 {
        found_items_list.push(format!("{} Dynallca", found_herbs[3]));
    }
    if found_energy > 0 {
        found_items_list.push(format!("{found_energy} energii"));
    }
    if found_maps > 0 {
        found_items_list.push(format!("{found_maps} map"));
    }
    if found_astral > 0 {
        found_items_list.push(format!("{found_astral} astralnych"));
    }

    let meta = PageMeta::titled("Poszukiwania")
        .with_back_link(back_url_for_location(&player_row.location), "Wróć");
    let base = app.templates.build_context(&ctx, &meta);

    let remaining_energy = (player_row.energy - energy_cost).max(0.0);
    let view = ExploreView {
        base,
        state: "results".to_owned(),
        energy: remaining_energy,
        has_fight: false,
        fight_monster_name: String::new(),
        region_description: String::new(),
        steps_taken: f64::from(actual_steps) / 2.0,
        found_items: found_items_list,
        bridge_event,
        encounter_name,
        monster_name: String::new(),
        xp_gain: 0,
        back_url: String::new(),
    };
    app.templates.render_value("explore.html", &view)
}

/// GET /explore/escape — attempt to escape from a monster.
pub async fn explore_escape(
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

    if player_row.fight == 0 {
        return error_page(&app, &ctx, "Nie masz przed kim uciekać!");
    }

    let monster = match combat::load_monster(&app.pool, player_row.fight).await {
        Ok(Some(m)) => m.into_domain(),
        _ => return error_page(&app, &ctx, "Nie znaleziono potwora."),
    };

    let stats = match player_q::load_stats(&app.pool, player_id).await {
        Ok(v) => v,
        Err(e) => {
            tracing::error!(player_id, error = ?e, "failed to load stats for escape");
            Vec::new()
        }
    };
    let skills = match player_q::load_skills(&app.pool, player_id).await {
        Ok(v) => v,
        Err(e) => {
            tracing::error!(player_id, error = ?e, "failed to load skills for escape");
            Vec::new()
        }
    };

    let player_speed = find_stat(&stats, "speed");
    let perception = find_skill(&skills, "perception");

    let (player_roll, monster_roll) = {
        let mut rng = rand::thread_rng();
        (rng.gen_range(1..=100), rng.gen_range(1..=100))
    };

    #[allow(clippy::cast_possible_truncation)]
    let escaped = encounter::explore_escape_check(
        player_speed,
        perception,
        player_roll,
        monster.speed,
        monster_roll,
    ) > 0;

    let (state_str, xp_gain) = if escaped {
        let xp = encounter::escape_xp(&monster);
        log_err!(
            combat::clear_player_fight(&app.pool, player_id).await,
            "clear player fight"
        );
        ("escape_success".to_owned(), xp)
    } else {
        ("escape_fail".to_owned(), 0)
    };

    let back_url = back_url_for_location(&player_row.location);
    let meta = PageMeta::titled("Ucieczka").with_back_link(back_url.clone(), "Wróć");
    let base = app.templates.build_context(&ctx, &meta);

    let view = ExploreView {
        base,
        state: state_str,
        energy: player_row.energy,
        has_fight: !escaped,
        fight_monster_name: if escaped {
            String::new()
        } else {
            monster.name.clone()
        },
        region_description: String::new(),
        steps_taken: 0.0,
        found_items: Vec::new(),
        bridge_event: String::new(),
        encounter_name: String::new(),
        monster_name: monster.name,
        xp_gain,
        back_url,
    };
    app.templates.render_value("explore.html", &view)
}

// =========================================================================
// PvE battle handlers
// =========================================================================

/// GET /battle/pve — show current `PvE` battle state or redirect if no fight.
pub async fn pve_show(
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

    if player_row.fight == 0 {
        return error_page(&app, &ctx, "Nie jesteś w walce z żadnym potworem.");
    }

    if player_row.hp <= 0 {
        return error_page(&app, &ctx, "Nie możesz walczyć, ponieważ nie żyjesz!");
    }

    let monster = match combat::load_monster(&app.pool, player_row.fight).await {
        Ok(Some(m)) => m.into_domain(),
        _ => return error_page(&app, &ctx, "Nie znaleziono potwora."),
    };

    let equipped = match vallheru_data::queries::item::find_equipped_items(&app.pool, player_id)
        .await
    {
        Ok(v) => v,
        Err(e) => {
            tracing::error!(player_id, error = ?e, "failed to load equipped items for pve show");
            Vec::new()
        }
    };
    let equipped_domain: Vec<OwnedEquipment> =
        equipped.iter().map(|e| e.clone().into_domain()).collect();
    let has_weapon = equipped_domain.iter().any(|e| {
        matches!(
            e.equipment_type,
            EquipmentType::Weapon | EquipmentType::Shield
        )
    });
    let has_bow = equipped_domain
        .iter()
        .any(|e| e.equipment_type == EquipmentType::Bow);

    let spells =
        match vallheru_data::queries::item::find_spells_by_owner(&app.pool, player_id).await {
            Ok(v) => v,
            Err(e) => {
                tracing::error!(player_id, error = ?e, "failed to load spells for pve show");
                Vec::new()
            }
        };
    let has_spell = spells.iter().any(|s| s.status == "E" && s.typ == "B");
    let has_def_spell = spells.iter().any(|s| s.status == "E" && s.typ == "O");

    let potions =
        match vallheru_data::queries::item::find_potions_by_owner(&app.pool, player_id).await {
            Ok(v) => v,
            Err(e) => {
                tracing::error!(player_id, error = ?e, "failed to load potions for pve show");
                Vec::new()
            }
        };
    let has_potion = potions
        .iter()
        .any(|p| p.status == "K" && p.potion_type == "H");

    let stats = match player_q::load_stats(&app.pool, player_id).await {
        Ok(v) => v,
        Err(e) => {
            tracing::error!(player_id, error = ?e, "failed to load stats for pve show");
            Vec::new()
        }
    };
    let speed = find_stat(&stats, "speed");

    #[allow(clippy::cast_possible_truncation)]
    let ap = ((f64::from(speed) / monster.speed).ceil() as i32).clamp(1, 5);

    let continue_url = back_url_for_location(&player_row.location);
    let meta = PageMeta::titled("Walka");
    let base = app.templates.build_context(&ctx, &meta);

    let view = BattleView {
        base,
        outcome: "in_progress".to_owned(),
        round: 1,
        action_points: ap,
        player_hp: player_row.hp,
        player_max_hp: player_row.max_hp,
        player_mana: player_row.pm,
        monster_name: monster.name,
        monster_hp: monster.hp,
        monster_max_hp: monster.hp,
        battle_log: Vec::new(),
        has_weapon,
        has_bow,
        has_spell,
        has_def_spell,
        has_potion,
        xp_gain: 0,
        gold_gain: 0,
        loot_name: String::new(),
        continue_url,
    };
    app.templates.render_value("battle.html", &view)
}

/// POST /battle/pve — execute `PvE` auto-battle.
///
/// Runs the entire fight to completion using the player's primary attack mode,
/// then shows the result. Avoids server-side session state for partial battles.
#[allow(clippy::too_many_lines, clippy::similar_names)]
pub async fn pve_action(
    State(app): State<AppState>,
    Extension(ctx): Extension<RequestContext>,
    Form(form): Form<BattleActionForm>,
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

    if player_row.fight == 0 {
        return error_page(&app, &ctx, "Nie jesteś w walce z żadnym potworem.");
    }

    if player_row.hp <= 0 {
        return error_page(&app, &ctx, "Nie możesz walczyć, ponieważ nie żyjesz!");
    }

    let monster = match combat::load_monster(&app.pool, player_row.fight).await {
        Ok(Some(m)) => m.into_domain(),
        _ => return error_page(&app, &ctx, "Nie znaleziono potwora."),
    };

    let stats = match player_q::load_stats(&app.pool, player_id).await {
        Ok(v) => v,
        Err(e) => {
            tracing::error!(player_id, error = ?e, "failed to load stats for pve action");
            Vec::new()
        }
    };
    let skills = match player_q::load_skills(&app.pool, player_id).await {
        Ok(v) => v,
        Err(e) => {
            tracing::error!(player_id, error = ?e, "failed to load skills for pve action");
            Vec::new()
        }
    };
    let bonuses = match player_q::load_bonuses(&app.pool, player_id).await {
        Ok(v) => v,
        Err(e) => {
            tracing::error!(player_id, error = ?e, "failed to load bonuses for pve action");
            Vec::new()
        }
    };
    let equipped = match vallheru_data::queries::item::find_equipped_items(&app.pool, player_id)
        .await
    {
        Ok(v) => v,
        Err(e) => {
            tracing::error!(player_id, error = ?e, "failed to load equipped items for pve action");
            Vec::new()
        }
    };
    let equipped_domain: Vec<OwnedEquipment> =
        equipped.iter().map(|e| e.clone().into_domain()).collect();
    let spells =
        match vallheru_data::queries::item::find_spells_by_owner(&app.pool, player_id).await {
            Ok(v) => v,
            Err(e) => {
                tracing::error!(player_id, error = ?e, "failed to load spells for pve action");
                Vec::new()
            }
        };
    let potions =
        match vallheru_data::queries::item::find_potions_by_owner(&app.pool, player_id).await {
            Ok(v) => v,
            Err(e) => {
                tracing::error!(player_id, error = ?e, "failed to load potions for pve action");
                Vec::new()
            }
        };

    let weapon_ref = equipped_domain
        .iter()
        .find(|e| e.equipment_type == EquipmentType::Weapon);
    let second_weapon_ref = equipped_domain
        .iter()
        .find(|e| e.equipment_type == EquipmentType::Shield);
    let bow_ref = equipped_domain
        .iter()
        .find(|e| e.equipment_type == EquipmentType::Bow);
    let arrows_ref = equipped_domain
        .iter()
        .find(|e| e.equipment_type == EquipmentType::Arrows);
    let wand_ref = equipped_domain
        .iter()
        .find(|e| e.equipment_type == EquipmentType::Wand);
    let helmet_ref = equipped_domain
        .iter()
        .find(|e| e.equipment_type == EquipmentType::Helmet);
    let armor_ref = equipped_domain
        .iter()
        .find(|e| e.equipment_type == EquipmentType::Armor);
    let legs_ref = equipped_domain
        .iter()
        .find(|e| e.equipment_type == EquipmentType::Legs);
    let shield_ref = equipped_domain
        .iter()
        .find(|e| e.equipment_type == EquipmentType::Shield);

    // Build battle spell from equipped spells
    let battle_spell_row = spells.iter().find(|s| s.status == "E" && s.typ == "B");
    let battle_spell: Option<Spell> = battle_spell_row.map(spell_from_row);
    let def_spell_row = spells.iter().find(|s| s.status == "E" && s.typ == "O");
    let def_spell: Option<Spell> = def_spell_row.map(spell_from_row);

    let player_class = Class::from_db(&player_row.class).unwrap_or(Class::Warrior);
    let speed = find_stat(&stats, "speed");
    let condition = find_stat(&stats, "condition");
    let magic_skill = find_skill(&skills, "magic");

    // Create monster combat state
    #[allow(clippy::cast_possible_truncation)]
    let mon_state = MonsterCombatState {
        current_hp: monster.hp,
        max_hp: monster.hp,
        base_damage: f64::midpoint(monster.strength, monster.agility) as i32,
        speed: monster.speed as i32,
        level: monster.level,
        agility: monster.agility as i32,
        dmg_element: monster.dmgtype,
        name: monster.name.clone(),
    };

    #[allow(clippy::cast_possible_truncation)]
    let ap = ((f64::from(speed) / monster.speed).ceil() as i32).clamp(1, 5);
    let mut state = BattleState::new(player_row.hp, player_row.pm, ap, vec![mon_state]);

    let action_str = form.action.as_deref().unwrap_or("attack_normal");

    // Determine stance and action type
    let stance = match action_str {
        "attack_aggressive" => AttackStance::Aggressive,
        "attack_berserker" => AttackStance::Berserker,
        "attack_defensive" => AttackStance::Defensive,
        _ => AttackStance::Normal,
    };

    let wants_escape = action_str == "escape";
    let wants_rest = action_str == "rest";
    let wants_spell = action_str == "cast_spell" || action_str == "burst_spell";
    let weapon_weight = weapon_ref.map_or(0, |w| w.power / 10);

    let mut log: Vec<String> = Vec::new();

    // Build DamageContext for attacks
    let dmg_ctx = DamageContext {
        class: &player_class,
        stats: &stats,
        skills: &skills,
        bonuses: &bonuses,
        weapon: weapon_ref,
        second_weapon: second_weapon_ref,
        bow: bow_ref,
        arrows: arrows_ref,
        wand: wand_ref,
        helmet: helmet_ref,
        armor: armor_ref,
        legs: legs_ref,
        shield: shield_ref,
        attack_spell: battle_spell.as_ref(),
        pet_attack: 0,
        pet_defense: 0,
        monster_resistance: monster.resistance,
    };

    // Build MonsterTurnCtx
    #[allow(clippy::cast_possible_truncation)]
    let monster_agility = monster.agility as i32;
    let player_dodge_value = formulas::player_dodge(
        &player_class,
        find_stat(&stats, "agility"),
        monster_agility,
        find_skill(&skills, "dodge"),
    );
    let active_def_spell_ref = def_spell.as_ref();
    let monster_turn_ctx = battle::MonsterTurnCtx {
        player_dodge_value,
        stance,
        shield: shield_ref,
        armor_pieces: [helmet_ref, armor_ref, legs_ref, None],
        stats: &stats,
        skills: &skills,
        bonuses: &bonuses,
        monster_attacks_per_round: 1,
        active_def_spell: active_def_spell_ref,
    };

    // All RNG usage must be scoped to avoid holding !Send ThreadRng across awaits.
    {
        let mut rng = rand::thread_rng();

        // Run battle rounds until resolution
        while state.outcome == BattleOutcome::InProgress {
            // Player turn
            if wants_escape {
                #[allow(clippy::cast_possible_truncation)]
                let avg_speed = state.monsters.first().map_or(1, |m| m.speed);
                let escape_rolls = EscapeRolls {
                    player_roll: rng.gen_range(1..=100),
                    monster_roll: rng.gen_range(1..=100),
                };
                let result = battle::resolve_escape(
                    &mut state,
                    speed,
                    find_skill(&skills, "perception"),
                    &escape_rolls,
                    avg_speed,
                );
                if result.succeeded {
                    log.push("Udało Ci się uciec!".to_owned());
                } else {
                    log.push("Nie udało Ci się uciec!".to_owned());
                }
            } else if wants_rest {
                battle::resolve_rest(&mut state, condition);
                log.push("Odpoczywasz...".to_owned());
            } else if wants_spell && let Some(ref spell) = battle_spell {
                let spell_rolls = SpellRolls {
                    fizzle_roll: rng.gen_range(1..=100),
                    misfire_roll: rng.gen_range(1..=100),
                    skill_roll: rng.gen_range(1..=magic_skill.max(1)),
                    wand_roll: wand_ref.map_or(0, |w| rng.gen_range(1..=w.power.max(1))),
                    crit_roll_large: rng.gen_range(1..=1000),
                    crit_roll_small: rng.gen_range(1..=100),
                    dodge_roll: rng.gen_range(1..=100),
                    target_monster: 0,
                };
                let burst_power = if action_str == "burst_spell" {
                    magic_skill
                } else {
                    0
                };
                #[allow(clippy::cast_possible_truncation)]
                let endurance = monster.endurance as i32;
                let result = battle::resolve_spell(
                    &mut state,
                    spell,
                    magic_skill,
                    &spell_rolls,
                    burst_power,
                    endurance,
                );
                log.push(format!(
                    "Rzucasz zaklęcie! Zadajesz {} obrażeń.",
                    result.damage_dealt,
                ));
            } else if weapon_ref.is_some() {
                // Melee attack
                let attack_rolls = AttackRolls {
                    skill_roll: rng.gen_range(1..=find_skill(&skills, "attack").max(1)),
                    wand_roll: 0,
                    crit_roll_large: rng.gen_range(1..=1000),
                    crit_roll_small: rng.gen_range(1..=100),
                    dodge_roll: rng.gen_range(1..=100),
                    target_monster: 0,
                };
                let result = battle::resolve_attack(
                    &mut state,
                    &dmg_ctx,
                    stance,
                    &attack_rolls,
                    weapon_weight,
                );
                if result.was_dodged {
                    log.push(format!(
                        "Atakujesz {}! Potwór unika ciosu.",
                        state.monsters[0].name
                    ));
                } else {
                    log.push(format!(
                        "Atakujesz {}! Zadajesz {} obrażeń.{}",
                        state.monsters[0].name,
                        result.damage_dealt,
                        if result.was_critical {
                            " Trafienie krytyczne!"
                        } else {
                            ""
                        },
                    ));
                }
            } else {
                // No weapon, no spell — rest
                battle::resolve_rest(&mut state, condition);
                log.push("Nie masz czym walczyć... Odpoczywasz.".to_owned());
            }

            if state.outcome != BattleOutcome::InProgress {
                break;
            }

            // Monster turn
            let mon_attack_rolls: Vec<Vec<MonsterAttackRoll>> = state
                .monsters
                .iter()
                .map(|_| {
                    vec![MonsterAttackRoll {
                        damage_roll: rng.gen_range(0..=state.monsters[0].level.max(1)),
                        dodge_roll: rng.gen_range(1..=100),
                        block_roll: rng.gen_range(1..=100),
                        hit_location_roll: rng.gen_range(1..=100),
                    }]
                })
                .collect();

            let mon_rolls = MonsterTurnRolls {
                attacks: mon_attack_rolls,
            };

            let mon_result =
                battle::resolve_monster_turn(&mut state, &monster_turn_ctx, &mon_rolls);
            if mon_result.total_damage > 0 {
                log.push(format!(
                    "{} atakuje! Zadaje {} obrażeń.",
                    state.monsters[0].name, mon_result.total_damage,
                ));
            } else if mon_result.player_dodged_any {
                log.push(format!(
                    "{} atakuje, ale unikasz ciosu!",
                    state.monsters[0].name
                ));
            }

            // Advance round
            state.next_round();
        }
    } // drop rng (ThreadRng is !Send) before any awaits

    // Determine outcome and rewards
    let profile = PlayerCombatProfile {
        hp: player_row.hp,
        condition_mod: condition,
        speed_mod: speed,
        agility_mod: find_stat(&stats, "agility"),
        strength_mod: find_stat(&stats, "strength"),
        wisdom_mod: find_stat(&stats, "wisdom"),
        intelligence_mod: find_stat(&stats, "inteli"),
        dodge_skill: find_skill(&skills, "dodge"),
        attack_skill: find_skill(&skills, "attack"),
        shoot_skill: find_skill(&skills, "shoot"),
        magic_skill: find_skill(&skills, "magic"),
        has_weapon: weapon_ref.is_some(),
        has_second_weapon: second_weapon_ref.is_some(),
        has_bow: bow_ref.is_some(),
    };
    let reward = encounter_reward(&monster, player_power_level(&profile));

    let outcome_str;
    let mut xp_gain = 0i64;
    let mut gold_gain = 0i64;
    let mut loot_name = String::new();

    match state.outcome {
        BattleOutcome::Victory => {
            outcome_str = "victory";
            xp_gain = reward.xp;
            gold_gain = reward.gold;

            if !monster.loot.is_empty() {
                let loot_roll = rand::thread_rng().gen_range(1..=100);
                if let Some(entry) = encounter::pick_loot(&monster.loot, loot_roll) {
                    loot_name = entry.name.clone();
                }
            }

            let hp_delta = state.player_hp - player_row.hp;
            let mana_delta = state.player_mana - player_row.pm;
            log_err!(
                combat::apply_combat_results(&app.pool, player_id, hp_delta, gold_gain, mana_delta)
                    .await,
                "apply combat results"
            );
            log_err!(
                combat::clear_player_fight(&app.pool, player_id).await,
                "clear player fight"
            );
        }
        BattleOutcome::Defeat => {
            outcome_str = "defeat";
            log_err!(
                sqlx::query("UPDATE players SET hp = 0, fight = 0 WHERE id = $1")
                    .bind(player_id)
                    .execute(&app.pool)
                    .await,
                "query"
            );
        }
        BattleOutcome::Escaped => {
            outcome_str = "escaped";
            let hp_delta = state.player_hp - player_row.hp;
            let mana_delta = state.player_mana - player_row.pm;
            log_err!(
                combat::apply_combat_results(&app.pool, player_id, hp_delta, 0, mana_delta).await,
                "apply combat results"
            );
            log_err!(
                combat::clear_player_fight(&app.pool, player_id).await,
                "clear player fight"
            );
        }
        BattleOutcome::Draw | BattleOutcome::InProgress => {
            outcome_str = "draw";
            let hp_delta = state.player_hp - player_row.hp;
            let mana_delta = state.player_mana - player_row.pm;
            log_err!(
                combat::apply_combat_results(&app.pool, player_id, hp_delta, 0, mana_delta).await,
                "apply combat results"
            );
            log_err!(
                combat::clear_player_fight(&app.pool, player_id).await,
                "clear player fight"
            );
        }
    }

    log_err!(
        combat::deduct_energy(&app.pool, player_id, 1.0).await,
        "deduct energy"
    );

    let continue_url = back_url_for_location(&player_row.location);
    let meta = PageMeta::titled("Walka — Wynik");
    let base = app.templates.build_context(&ctx, &meta);

    let has_weapon = weapon_ref.is_some();
    let has_bow = bow_ref.is_some();
    let has_spell = spells.iter().any(|s| s.status == "E" && s.typ == "B");
    let has_def_spell = spells.iter().any(|s| s.status == "E" && s.typ == "O");
    let has_potion = potions
        .iter()
        .any(|p| p.status == "K" && p.potion_type == "H");

    let view = BattleView {
        base,
        outcome: outcome_str.to_owned(),
        round: state.round,
        action_points: state.action_points,
        player_hp: state.player_hp,
        player_max_hp: player_row.max_hp,
        player_mana: state.player_mana,
        monster_name: monster.name,
        monster_hp: state.monsters.first().map_or(0, |m| m.current_hp),
        monster_max_hp: monster.hp,
        battle_log: log,
        has_weapon,
        has_bow,
        has_spell,
        has_def_spell,
        has_potion,
        xp_gain,
        gold_gain,
        loot_name,
        continue_url,
    };
    app.templates.render_value("battle.html", &view)
}

// =========================================================================
// PvP Arena handlers
// =========================================================================

/// GET /arena — show `PvP` opponent list.
pub async fn arena_show(
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

    if player_row.location == "Lochy" {
        return error_page(
            &app,
            &ctx,
            "Nie możesz zwiedzać areny ponieważ znajdujesz się w lochach.",
        );
    }

    let opponents =
        match combat::load_arena_opponents(&app.pool, player_id, &player_row.location).await {
            Ok(v) => v,
            Err(e) => {
                tracing::error!(player_id, error = ?e, "failed to load arena opponents");
                Vec::new()
            }
        };

    let meta = PageMeta::titled("Arena Walk").with_back_link("/city", "Wróć do miasta");
    let base = app.templates.build_context(&ctx, &meta);

    let view = ArenaView { base, opponents };
    app.templates.render_value("arena.html", &view)
}

/// GET /arena/fight/:id — run a `PvP` battle with an opponent.
#[allow(clippy::too_many_lines)]
pub async fn arena_fight(
    State(app): State<AppState>,
    Extension(ctx): Extension<RequestContext>,
    Path(opponent_id): Path<i32>,
) -> Response {
    use vallheru_domain::combat::pvp::{
        self as pvp, PvpBattleRolls, PvpHalfRoundRolls, PvpOutcome, PvpStrikeRoll,
    };

    let Some(ref user) = ctx.session_user else {
        return crate::page::redirect("/login");
    };

    #[allow(clippy::cast_possible_truncation)]
    let player_id = user.id as i32;

    if player_id == opponent_id {
        return error_page(&app, &ctx, "Nie możesz walczyć sam ze sobą!");
    }

    let player_row = match load_player(&app, player_id).await {
        Ok(row) => row,
        Err(resp) => return resp,
    };

    if player_row.hp <= 0 {
        return error_page(&app, &ctx, "Nie możesz walczyć, ponieważ nie żyjesz!");
    }

    let opponent_row = match load_player(&app, opponent_id).await {
        Ok(row) => row,
        Err(resp) => return resp,
    };

    if opponent_row.hp <= 0 {
        return error_page(&app, &ctx, "Ten gracz nie żyje.");
    }

    if opponent_row.immune {
        return error_page(&app, &ctx, "Ten gracz jest chroniony.");
    }

    // Build PvpCombatants
    let mut attacker = build_pvp_combatant(&player_row, &app.pool).await;
    let mut defender = build_pvp_combatant(&opponent_row, &app.pool).await;

    // Generate rolls
    let max_half_rounds = 50usize;
    let strikes_per_turn = 3usize;
    let rolls = {
        let mut rng = rand::thread_rng();
        PvpBattleRolls {
            half_rounds: (0..max_half_rounds)
                .map(|_| PvpHalfRoundRolls {
                    strikes: (0..strikes_per_turn)
                        .map(|_| PvpStrikeRoll {
                            location_roll: rng.gen_range(1..=100),
                            attack_roll: rng.gen_range(1..=100),
                            dodge_roll: rng.gen_range(1..=100),
                            dodge_chance_roll: rng.gen_range(1..=100),
                            block_roll: rng.gen_range(1..=100),
                            crit_roll_1000: rng.gen_range(1..=1000),
                            crit_roll_100: rng.gen_range(1..=100),
                            misfire_roll: rng.gen_range(1..=100),
                            spell_resist_roll: rng.gen_range(1..=100),
                        })
                        .collect(),
                })
                .collect(),
        }
    };

    let result = pvp::run_pvp_battle(&mut attacker, &mut defender, &rolls);

    #[allow(clippy::cast_possible_wrap)]
    let timestamp = std::time::SystemTime::now()
        .duration_since(std::time::UNIX_EPOCH)
        .map_or(0, |d| d.as_secs() as i64);

    let mut battle_log = Vec::new();
    battle_log.push(format!(
        "Walka trwała {} rund.",
        result.half_rounds_played / 2,
    ));

    let (outcome_str, gold_gain) = match result.outcome {
        PvpOutcome::AttackerWin => {
            let map_steal_roll = {
                let mut rng = rand::thread_rng();
                rng.gen_range(1..=20)
            };
            let rewards = pvp::pvp_win_rewards(
                &attacker,
                &defender,
                true,
                true,
                false,
                formulas::AttackType::Melee,
                map_steal_roll,
            );
            log_err!(
                combat::apply_pvp_winner(
                    &app.pool,
                    player_id,
                    &opponent_row.username,
                    i64::from(rewards.gold_stolen),
                )
                .await,
                "apply pvp winner"
            );
            log_err!(
                combat::apply_pvp_loser(
                    &app.pool,
                    opponent_id,
                    &player_row.username,
                    result.defender_hp,
                )
                .await,
                "apply pvp loser"
            );
            log_err!(
                combat::insert_battle_log(&app.pool, player_id, opponent_id, player_id, timestamp)
                    .await,
                "insert battle log"
            );
            battle_log.push(format!(
                "Wygrywasz! Zdobywasz {} złota.",
                rewards.gold_stolen,
            ));
            ("attacker_wins", i64::from(rewards.gold_stolen))
        }
        PvpOutcome::DefenderWin => {
            let map_steal_roll = {
                let mut rng = rand::thread_rng();
                rng.gen_range(1..=20)
            };
            let rewards = pvp::pvp_win_rewards(
                &defender,
                &attacker,
                true,
                true,
                false,
                formulas::AttackType::Melee,
                map_steal_roll,
            );
            log_err!(
                combat::apply_pvp_winner(
                    &app.pool,
                    opponent_id,
                    &player_row.username,
                    i64::from(rewards.gold_stolen),
                )
                .await,
                "apply pvp winner"
            );
            log_err!(
                combat::apply_pvp_loser(
                    &app.pool,
                    player_id,
                    &opponent_row.username,
                    result.attacker_hp,
                )
                .await,
                "apply pvp loser"
            );
            log_err!(
                combat::insert_battle_log(
                    &app.pool,
                    player_id,
                    opponent_id,
                    opponent_id,
                    timestamp,
                )
                .await,
                "insert battle log"
            );
            battle_log.push("Przegrywasz walkę!".to_owned());
            ("defender_wins", 0)
        }
        PvpOutcome::Draw => {
            log_err!(
                combat::insert_battle_log(&app.pool, player_id, opponent_id, 0, timestamp).await,
                "insert battle log"
            );
            battle_log.push("Remis!".to_owned());
            ("draw", 0)
        }
    };

    let meta = PageMeta::titled("Arena Walk — Wynik").with_back_link("/arena", "Wróć do areny");
    let base = app.templates.build_context(&ctx, &meta);

    let view = PvpResultView {
        base,
        outcome: outcome_str.to_owned(),
        opponent_name: opponent_row.username.clone(),
        gold_gain,
        battle_log,
    };
    app.templates.render_value("pvp_result.html", &view)
}

// =========================================================================
// Hunter guild handlers
// =========================================================================

/// GET /hunters — hunter guild main menu.
pub async fn hunters_show(
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

    if player_row.location != "Altara" && player_row.location != "Ardulith" {
        return error_page(&app, &ctx, "Nie znajdujesz się w mieście.");
    }

    let quest_key = match player_row.location.as_str() {
        "Altara" => "hunteraltara",
        _ => "hunterardulith",
    };
    let has_quest = match settings_q::get_setting(&app.pool, quest_key).await {
        Ok(Some(r)) => r.value.is_some_and(|v| !v.is_empty()),
        Ok(None) => false,
        Err(e) => {
            tracing::error!(error = ?e, "failed to load hunters quest setting");
            false
        }
    };

    let meta = PageMeta::titled("Gildia Łowców").with_back_link("/city", "Wróć do miasta");
    let base = app.templates.build_context(&ctx, &meta);

    let view = HuntersView {
        base,
        state: "menu".to_owned(),
        has_quest,
        monsters_city1: Vec::new(),
        monsters_city2: Vec::new(),
        max_rows: 0,
        monster_name: String::new(),
        monster_description: String::new(),
        quest_description: String::new(),
        quest_message: String::new(),
    };
    app.templates.render_value("hunters.html", &view)
}

/// GET /hunters/bestiary — bestiary list.
pub async fn hunters_bestiary(
    State(app): State<AppState>,
    Extension(ctx): Extension<RequestContext>,
) -> Response {
    let Some(ref _user) = ctx.session_user else {
        return crate::page::redirect("/login");
    };

    let entries = match combat::load_bestiary(&app.pool).await {
        Ok(v) => v,
        Err(e) => {
            tracing::error!(error = ?e, "failed to load bestiary");
            Vec::new()
        }
    };

    let mut city1: Vec<combat::BestiaryEntry> = entries
        .iter()
        .filter(|e| e.location == "Altara")
        .cloned()
        .collect();
    let mut city2: Vec<combat::BestiaryEntry> = entries
        .iter()
        .filter(|e| e.location != "Altara")
        .cloned()
        .collect();

    let max_rows = city1.len().max(city2.len());
    while city1.len() < max_rows {
        city1.push(combat::BestiaryEntry {
            id: 0,
            name: String::new(),
            location: String::new(),
        });
    }
    while city2.len() < max_rows {
        city2.push(combat::BestiaryEntry {
            id: 0,
            name: String::new(),
            location: String::new(),
        });
    }

    let meta = PageMeta::titled("Bestiariusz").with_back_link("/hunters", "Wróć do gildii");
    let base = app.templates.build_context(&ctx, &meta);

    let view = HuntersView {
        base,
        state: "bestiary".to_owned(),
        has_quest: false,
        monsters_city1: city1,
        monsters_city2: city2,
        max_rows,
        monster_name: String::new(),
        monster_description: String::new(),
        quest_description: String::new(),
        quest_message: String::new(),
    };
    app.templates.render_value("hunters.html", &view)
}

/// GET /hunters/monster/:id — monster description.
pub async fn hunters_monster(
    State(app): State<AppState>,
    Extension(ctx): Extension<RequestContext>,
    Path(monster_id): Path<i32>,
) -> Response {
    let Some(ref _user) = ctx.session_user else {
        return crate::page::redirect("/login");
    };

    let Ok(Some((name, description))) =
        combat::load_monster_description(&app.pool, monster_id).await
    else {
        return error_page(&app, &ctx, "Nie ma opisu tego potwora.");
    };

    let meta =
        PageMeta::titled(&name).with_back_link("/hunters/bestiary", "Wróć do spisu potworów");
    let base = app.templates.build_context(&ctx, &meta);

    let view = HuntersView {
        base,
        state: "monster".to_owned(),
        has_quest: false,
        monsters_city1: Vec::new(),
        monsters_city2: Vec::new(),
        max_rows: 0,
        monster_name: name,
        monster_description: description,
        quest_description: String::new(),
        quest_message: String::new(),
    };
    app.templates.render_value("hunters.html", &view)
}

/// GET /hunters/quest — show quest board.
pub async fn hunters_quest_show(
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

    let quest_key = match player_row.location.as_str() {
        "Altara" => "hunteraltara",
        _ => "hunterardulith",
    };

    let quest_value = match settings_q::get_setting(&app.pool, quest_key).await {
        Ok(Some(r)) => r.value.unwrap_or_default(),
        Ok(None) => String::new(),
        Err(e) => {
            tracing::error!(error = ?e, "failed to load quest setting for show");
            String::new()
        }
    };

    if quest_value.is_empty() {
        return error_page(&app, &ctx, "Nie ma zleceń w gildii.");
    }

    let description = format_quest_description(&app.pool, &quest_value).await;

    let meta = PageMeta::titled("Tablica ogłoszeń").with_back_link("/hunters", "Wróć do gildii");
    let base = app.templates.build_context(&ctx, &meta);

    let view = HuntersView {
        base,
        state: "quest_board".to_owned(),
        has_quest: true,
        monsters_city1: Vec::new(),
        monsters_city2: Vec::new(),
        max_rows: 0,
        monster_name: String::new(),
        monster_description: String::new(),
        quest_description: description,
        quest_message: String::new(),
    };
    app.templates.render_value("hunters.html", &view)
}

/// POST /hunters/quest — execute quest.
#[allow(clippy::too_many_lines)]
pub async fn hunters_quest_do(
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

    if player_row.hp <= 0 {
        return error_page(
            &app,
            &ctx,
            "Nie możesz wykonywać zadań, kiedy jesteś martwy.",
        );
    }

    if player_row.energy < 1.0 {
        return error_page(&app, &ctx, "Nie masz energii aby móc przyjąć to zadanie.");
    }

    let quest_key = match player_row.location.as_str() {
        "Altara" => "hunteraltara",
        _ => "hunterardulith",
    };

    let quest_value = match settings_q::get_setting(&app.pool, quest_key).await {
        Ok(Some(r)) => r.value.unwrap_or_default(),
        Ok(None) => String::new(),
        Err(e) => {
            tracing::error!(error = ?e, "failed to load quest setting for do");
            String::new()
        }
    };

    if quest_value.is_empty() {
        return error_page(&app, &ctx, "Nie ma zleceń w gildii.");
    }

    let parts: Vec<&str> = quest_value.split(';').collect();
    let quest_type = parts.first().copied().unwrap_or("");

    let (message, gold_reward) = match quest_type {
        "I" | "B" | "P" => {
            let qty: i32 = parts.get(2).and_then(|s| s.parse().ok()).unwrap_or(1);
            let tier: i32 = parts.get(3).and_then(|s| s.parse().ok()).unwrap_or(0);
            let base_cost = if quest_type == "P" { 50 } else { 100 };
            let tier_bonus = [1.0, 1.05, 1.1, 1.15, 1.2];
            #[allow(clippy::cast_sign_loss)]
            let bonus = tier_bonus.get(tier as usize).copied().unwrap_or(1.0);
            #[allow(clippy::cast_possible_truncation)]
            let gold = (f64::from(base_cost) * bonus * f64::from(qty)).ceil() as i64;
            (
                "Dziękujemy za dostarczenie zapasów do Gildii.".to_owned(),
                gold,
            )
        }
        "L" => {
            let monster_id: i32 = parts.get(1).and_then(|s| s.parse().ok()).unwrap_or(0);
            let monster = match combat::load_monster(&app.pool, monster_id).await {
                Ok(v) => v,
                Err(e) => {
                    tracing::error!(monster_id, error = ?e, "failed to load monster for quest L");
                    None
                }
            };
            let gold = monster.as_ref().map_or(100, |m| i64::from(m.level) * 100);
            (
                "Dziękujemy za dostarczenie potrzebnych rzeczy do badań.".to_owned(),
                gold,
            )
        }
        "F" => {
            let monster_id: i32 = parts.get(1).and_then(|s| s.parse().ok()).unwrap_or(0);
            let qty: i32 = parts.get(2).and_then(|s| s.parse().ok()).unwrap_or(1);
            let monster = match combat::load_monster(&app.pool, monster_id).await {
                Ok(v) => v,
                Err(e) => {
                    tracing::error!(monster_id, error = ?e, "failed to load monster for quest F");
                    None
                }
            };
            let gold = monster
                .as_ref()
                .map_or(100, |m| i64::from(m.level) * 10 * i64::from(qty));
            (
                "Dziękujemy za oczyszczenie okolicy z potworów.".to_owned(),
                gold,
            )
        }
        _ => ("Zlecenie wykonane.".to_owned(), 0),
    };

    log_err!(
        combat::deduct_energy(&app.pool, player_id, 1.0).await,
        "deduct energy"
    );
    if gold_reward > 0 {
        log_err!(
            combat::add_gold(&app.pool, player_id, gold_reward).await,
            "add gold"
        );
    }
    log_err!(
        settings_q::upsert_setting(&app.pool, quest_key, "").await,
        "upsert setting"
    );

    let final_message = if gold_reward > 0 {
        format!("{message} Dostajesz {gold_reward} sztuk złota.")
    } else {
        message
    };

    let meta = PageMeta::titled("Zlecenie").with_back_link("/hunters", "Wróć do gildii");
    let base = app.templates.build_context(&ctx, &meta);

    let view = HuntersView {
        base,
        state: "quest_result".to_owned(),
        has_quest: false,
        monsters_city1: Vec::new(),
        monsters_city2: Vec::new(),
        max_rows: 0,
        monster_name: String::new(),
        monster_description: String::new(),
        quest_description: String::new(),
        quest_message: final_message,
    };
    app.templates.render_value("hunters.html", &view)
}

// =========================================================================
// Helpers
// =========================================================================

async fn load_player(state: &AppState, player_id: i32) -> Result<player_q::PlayerRow, Response> {
    match player_q::find_player_by_id(&state.pool, player_id).await {
        Ok(Some(row)) => Ok(row),
        Ok(None) => Err(crate::page::redirect("/login")),
        Err(e) => {
            tracing::error!(error = %e, "load_player failed");
            Err(server_error())
        }
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

fn find_stat(stats: &[PlayerStat], key: &str) -> i32 {
    stats
        .iter()
        .find(|s| s.stat_key == key)
        .map_or(1, |s| s.trained.max(1))
}

fn find_skill(skills: &[PlayerSkill], key: &str) -> i32 {
    skills
        .iter()
        .find(|s| s.skill_key == key)
        .map_or(1, |s| s.level.max(1))
}

fn back_url_for_location(location: &str) -> String {
    match location {
        "Las" => "/forest".to_owned(),
        "Góry" => "/mountains".to_owned(),
        "Podróż" => "/travel".to_owned(),
        _ => "/city".to_owned(),
    }
}

fn spell_from_row(row: &vallheru_data::queries::item::SpellRow) -> Spell {
    Spell {
        id: row.id,
        name: row.nazwa.clone(),
        owner_id: row.gracz,
        cost: row.cena,
        level: row.poziom,
        spell_type: SpellType::from_db(&row.typ).unwrap_or(SpellType::Battle),
        multiplier: row.obr,
        status: SpellStatus::from_db(&row.status).unwrap_or(SpellStatus::Active),
        element: Element::from_spell_code(&row.element),
    }
}

/// Build a [`PvpCombatant`] from database data.
#[allow(clippy::cast_possible_truncation)]
async fn build_pvp_combatant(
    row: &player_q::PlayerRow,
    pool: &sqlx::PgPool,
) -> vallheru_domain::combat::pvp::PvpCombatant {
    use vallheru_domain::combat::pvp::{PvpCombatant, PvpSpell};

    let id = row.id;
    let stats = match player_q::load_stats(pool, id).await {
        Ok(v) => v,
        Err(e) => {
            tracing::error!(player_id = id, error = ?e, "failed to load stats for pvp combatant");
            Vec::new()
        }
    };
    let skills = match player_q::load_skills(pool, id).await {
        Ok(v) => v,
        Err(e) => {
            tracing::error!(player_id = id, error = ?e, "failed to load skills for pvp combatant");
            Vec::new()
        }
    };
    let equipped = match vallheru_data::queries::item::find_equipped_items(pool, id).await {
        Ok(v) => v,
        Err(e) => {
            tracing::error!(player_id = id, error = ?e, "failed to load equipped for pvp combatant");
            Vec::new()
        }
    };
    let equipped_domain: Vec<OwnedEquipment> =
        equipped.iter().map(|e| e.clone().into_domain()).collect();
    let spell_rows = match vallheru_data::queries::item::find_spells_by_owner(pool, id).await {
        Ok(v) => v,
        Err(e) => {
            tracing::error!(player_id = id, error = ?e, "failed to load spells for pvp combatant");
            Vec::new()
        }
    };

    let find_equip = |et: EquipmentType| -> Option<OwnedEquipment> {
        equipped_domain
            .iter()
            .find(|e| e.equipment_type == et)
            .cloned()
    };

    let battle_spell = spell_rows
        .iter()
        .find(|s| s.status == "E" && s.typ == "B")
        .map(|s| PvpSpell {
            power: (s.obr * 10.0) as i32,
            level: s.poziom,
            element: Element::from_spell_code(&s.element),
        });
    let defense_spell = spell_rows
        .iter()
        .find(|s| s.status == "E" && s.typ == "O")
        .map(|s| PvpSpell {
            power: (s.obr * 10.0) as i32,
            level: s.poziom,
            element: Element::from_spell_code(&s.element),
        });

    PvpCombatant {
        player_id: row.id,
        name: row.username.clone(),
        class: Class::from_db(&row.class).unwrap_or(Class::Warrior),
        hp: row.hp,
        mana: row.pm,
        antidote: vallheru_domain::item::PoisonType::from_db(row.antidote.as_deref().unwrap_or("")),
        reputation: row.reputation,
        credits: row.credits,
        maps: row.maps,
        strength: find_stat(&stats, "strength"),
        agility: find_stat(&stats, "agility"),
        speed: find_stat(&stats, "speed"),
        condition: find_stat(&stats, "condition"),
        wisdom: find_stat(&stats, "wisdom"),
        intelligence: find_stat(&stats, "inteli"),
        attack_skill: find_skill(&skills, "attack"),
        shoot_skill: find_skill(&skills, "shoot"),
        dodge_skill: find_skill(&skills, "dodge"),
        magic_skill: find_skill(&skills, "magic"),
        weapon: find_equip(EquipmentType::Weapon),
        bow: find_equip(EquipmentType::Bow),
        arrows: find_equip(EquipmentType::Arrows),
        helmet: find_equip(EquipmentType::Helmet),
        armor: find_equip(EquipmentType::Armor),
        legs: find_equip(EquipmentType::Legs),
        shield: find_equip(EquipmentType::Shield),
        wand: find_equip(EquipmentType::Wand),
        second_weapon: None,
        battle_spell,
        defense_spell,
        pet_attack: 0,
        pet_defense: 0,
        assassin_bonus: 0,
        rage_bonus: 0.0,
        defender_bonus: 0.0,
    }
}

async fn format_quest_description(pool: &sqlx::PgPool, quest_value: &str) -> String {
    let parts: Vec<&str> = quest_value.split(';').collect();
    let quest_type = parts.first().copied().unwrap_or("");

    match quest_type {
        "F" => {
            let monster_id: i32 = parts.get(1).and_then(|s| s.parse().ok()).unwrap_or(0);
            let qty: i32 = parts.get(2).and_then(|s| s.parse().ok()).unwrap_or(1);
            let monster = combat::load_monster(pool, monster_id).await.ok().flatten();
            let name = monster.as_ref().map_or("Nieznany", |m| m.name.as_str());
            let gold = monster.as_ref().map_or(0, |m| m.level * 10 * qty);
            format!(
                "Gildia Łowców poszukuje śmiałka. Potwór: {name}, ilość: {qty}. Nagroda: {gold} złota."
            )
        }
        "I" | "B" | "P" => {
            let qty: i32 = parts.get(2).and_then(|s| s.parse().ok()).unwrap_or(1);
            format!("Gildia Łowców poszukuje kogoś, kto dostarczy zapasy. Ilość: {qty}.")
        }
        "L" => {
            let monster_id: i32 = parts.get(1).and_then(|s| s.parse().ok()).unwrap_or(0);
            let monster = combat::load_monster(pool, monster_id).await.ok().flatten();
            let name = monster.as_ref().map_or("Nieznany", |m| m.name.as_str());
            let gold = monster.as_ref().map_or(0, |m| m.level * 100);
            format!("Gildia poszukuje materiałów od potwora: {name}. Nagroda: {gold} złota.")
        }
        _ => "Brak opisu zlecenia.".to_owned(),
    }
}
