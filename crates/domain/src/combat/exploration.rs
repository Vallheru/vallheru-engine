//! Exploration loop for forest and mountain areas.
//!
//! Ported from PHP `explore.php`.  Each exploration step spends energy and
//! rolls on a location-specific random event table.  Possible outcomes
//! include finding gold, herbs, meteors, maps, astral components, nothing,
//! encountering a monster, or triggering a special event (Bridge of Death
//! in mountains only).
//!
//! All functions are pure — RNG values are passed in so callers can
//! control them in tests.

// ---------------------------------------------------------------------------
// Location
// ---------------------------------------------------------------------------

/// The two exploration regions with different loot tables.
#[derive(Debug, Clone, Copy, PartialEq, Eq)]
pub enum ExploreRegion {
    /// Forest (Las / Avantiel) — explore.php `action=forest`
    Forest,
    /// Mountains (Góry) — explore.php `action=moutains`
    Mountains,
}

impl ExploreRegion {
    /// Resolve from the player's current location string.
    ///
    /// PHP: `$player->location == 'Las'` → forest, `'Góry'` → mountains.
    pub fn from_location(location: &str) -> Option<Self> {
        match location {
            "Las" => Some(Self::Forest),
            "Góry" => Some(Self::Mountains),
            _ => None,
        }
    }

    /// Die size for the per-step roll.
    ///
    /// Mountains: d20 (1..=20), Forest: d19 (1..=19).
    pub fn die_size(self) -> i32 {
        match self {
            Self::Forest => 19,
            Self::Mountains => 20,
        }
    }
}

// ---------------------------------------------------------------------------
// Validation
// ---------------------------------------------------------------------------

/// Error when exploration cannot begin.
#[derive(Debug, Clone, PartialEq)]
pub enum ExploreError {
    /// Player is not in an explorable area.
    WrongLocation,
    /// Already engaged in combat.
    AlreadyFighting { monster_name: String },
    /// Not enough energy (minimum 0.5).
    InsufficientEnergy,
    /// Requested amount below the 0.5 minimum.
    AmountTooLow,
    /// Player is dead.
    PlayerDead,
}

/// Validate that a player can begin an exploration walk.
///
/// `requested_energy` is the energy the player wants to spend (float,
/// minimum 0.5).
pub fn validate_explore(
    location: &str,
    hp: i32,
    energy: f64,
    requested_energy: f64,
    fight_id: i32,
    fight_monster_name: Option<&str>,
) -> Result<ExploreRegion, ExploreError> {
    let region = ExploreRegion::from_location(location).ok_or(ExploreError::WrongLocation)?;

    if hp <= 0 {
        return Err(ExploreError::PlayerDead);
    }

    if fight_id != 0 {
        return Err(ExploreError::AlreadyFighting {
            monster_name: fight_monster_name.unwrap_or("?").to_owned(),
        });
    }

    if requested_energy < 0.5 {
        return Err(ExploreError::AmountTooLow);
    }

    if requested_energy > energy {
        return Err(ExploreError::InsufficientEnergy);
    }

    Ok(region)
}

// ---------------------------------------------------------------------------
// Per-step event outcome
// ---------------------------------------------------------------------------

/// The result of a single exploration step (one d20/d19 roll).
#[derive(Debug, Clone, PartialEq)]
pub enum StepEvent {
    /// Nothing happened.
    Nothing,
    /// Found some gold (credits).
    Gold(i32),
    /// Found meteor ore (mountains only).
    Meteor(i32),
    /// Found energy (forest only).
    Energy(i32),
    /// Found herbs: `[illani, illanias, nutari, dynallca]`.
    Herbs([i32; 4]),
    /// Found a treasure map (1 map).
    Map,
    /// Found an astral component.
    Astral,
    /// Encountered a monster — exploration stops.
    MonsterEncounter,
    /// Bridge of Death event (mountains only) — exploration stops.
    BridgeOfDeath,
}

// ---------------------------------------------------------------------------
// Per-step roll resolution
// ---------------------------------------------------------------------------

/// Random values needed to resolve one exploration step.
#[derive(Debug, Clone)]
pub struct StepRolls {
    /// The d20 (mountains) or d19 (forest) roll, 1-based.
    pub event_roll: i32,
    /// Amount roll — used for gold (1..=1000), herbs (1..=10),
    /// meteor (1..=20), or energy (1..=2).
    pub amount_roll: i32,
    /// Map chance: 1..=50, map found if == 50.
    pub map_chance_roll: i32,
    /// Astral sub-chance: 1..=`chance_param`, success if == `chance_param`.
    /// PHP: `findastral(2)` means `rand(1,2)==2`.
    pub astral_chance_roll: i32,
}

/// Context needed for map discovery.
#[derive(Debug, Clone, Copy)]
pub struct MapContext {
    /// Current global maps available (`settings.maps` value).
    pub maps_available: i32,
    /// Player's current map count.
    pub player_maps: i32,
    /// Whether the player has the `Bohater` (Hero) rank.
    pub is_hero: bool,
}

/// Resolve a single exploration step to an event.
///
/// The event roll ranges and outcomes differ between mountains and forest.
///
/// ## Mountains (d20)
///
/// | Roll  | Event                         |
/// |-------|-------------------------------|
/// | 1–5   | Nothing                       |
/// | 6–8   | Monster encounter (stop)      |
/// | 9     | Gold (1..=1000)               |
/// | 10    | Meteor ore (1..=20)           |
/// | 11–13 | Illani herbs (1..=10)         |
/// | 14–15 | Illanias herbs (1..=10)       |
/// | 16    | Nutari herbs (1..=10)         |
/// | 17    | Bridge of Death (stop)        |
/// | 18    | Dynallca herbs (1..=10)       |
/// | 19    | Map (1/50 chance, conditions) |
/// | 20    | Astral component (1/2 chance) |
///
/// ## Forest (d19)
///
/// | Roll  | Event                         |
/// |-------|-------------------------------|
/// | 1–5   | Nothing                       |
/// | 6–8   | Monster encounter (stop)      |
/// | 9     | Gold (1..=1000)               |
/// | 10    | Energy (1..=2)                |
/// | 11–13 | Illani herbs (1..=10)         |
/// | 14–15 | Illanias herbs (1..=10)       |
/// | 16    | Nutari herbs (1..=10)         |
/// | 17    | Dynallca herbs (1..=10)       |
/// | 18    | Map (1/50 chance, conditions) |
/// | 19    | Astral component (1/2 chance) |
pub fn resolve_step(
    region: ExploreRegion,
    rolls: &StepRolls,
    map_ctx: &MapContext,
    player_bridge_done: bool,
) -> StepEvent {
    match region {
        ExploreRegion::Mountains => resolve_mountain_step(rolls, map_ctx, player_bridge_done),
        ExploreRegion::Forest => resolve_forest_step(rolls, map_ctx),
    }
}

fn resolve_mountain_step(
    rolls: &StepRolls,
    map_ctx: &MapContext,
    player_bridge_done: bool,
) -> StepEvent {
    match rolls.event_roll {
        6..=8 => StepEvent::MonsterEncounter,
        9 => StepEvent::Gold(rolls.amount_roll),
        10 => StepEvent::Meteor(rolls.amount_roll),
        11..=13 => StepEvent::Herbs([rolls.amount_roll, 0, 0, 0]),
        14..=15 => StepEvent::Herbs([0, rolls.amount_roll, 0, 0]),
        16 => StepEvent::Herbs([0, 0, rolls.amount_roll, 0]),
        17 => {
            // Bridge of Death — only if not already completed.
            if player_bridge_done {
                StepEvent::Nothing
            } else {
                StepEvent::BridgeOfDeath
            }
        }
        18 => StepEvent::Herbs([0, 0, 0, rolls.amount_roll]),
        19 => try_find_map(rolls, map_ctx),
        20 => try_find_astral(rolls),
        _ => StepEvent::Nothing,
    }
}

fn resolve_forest_step(rolls: &StepRolls, map_ctx: &MapContext) -> StepEvent {
    match rolls.event_roll {
        6..=8 => StepEvent::MonsterEncounter,
        9 => StepEvent::Gold(rolls.amount_roll),
        10 => StepEvent::Energy(rolls.amount_roll),
        11..=13 => StepEvent::Herbs([rolls.amount_roll, 0, 0, 0]),
        14..=15 => StepEvent::Herbs([0, rolls.amount_roll, 0, 0]),
        16 => StepEvent::Herbs([0, 0, rolls.amount_roll, 0]),
        17 => StepEvent::Herbs([0, 0, 0, rolls.amount_roll]),
        18 => try_find_map(rolls, map_ctx),
        19 => try_find_astral(rolls),
        _ => StepEvent::Nothing,
    }
}

fn try_find_map(rolls: &StepRolls, ctx: &MapContext) -> StepEvent {
    // PHP: `rand(1,50)==50 && maps_available > 0 && player_maps < 20 && rank != 'Bohater'`
    if rolls.map_chance_roll == 50 && ctx.maps_available > 0 && ctx.player_maps < 20 && !ctx.is_hero
    {
        StepEvent::Map
    } else {
        StepEvent::Nothing
    }
}

fn try_find_astral(rolls: &StepRolls) -> StepEvent {
    // PHP: `findastral(2)` → `rand(1,2)==2`
    if rolls.astral_chance_roll == 2 {
        StepEvent::Astral
    } else {
        StepEvent::Nothing
    }
}

// ---------------------------------------------------------------------------
// Full exploration walk
// ---------------------------------------------------------------------------

/// The accumulated result of an entire exploration walk.
#[derive(Debug, Clone, PartialEq)]
pub struct ExploreResult {
    /// Total gold found.
    pub gold: i32,
    /// Herbs found: `[illani, illanias, nutari, dynallca]`.
    pub herbs: [i32; 4],
    /// Meteor ore found (mountains only).
    pub meteor: i32,
    /// Energy found (forest only, reduces net energy cost).
    pub energy_found: i32,
    /// Maps found.
    pub maps_found: i32,
    /// Astral components found.
    pub astral_found: i32,
    /// How many steps were actually taken before stopping.
    pub steps_taken: i32,
    /// Net energy cost: `steps_taken / 2.0 - energy_found`.
    pub energy_cost: f64,
    /// Whether a monster was encountered (exploration stopped early).
    pub monster_encountered: bool,
    /// Whether the Bridge of Death was triggered (exploration stopped early).
    pub bridge_triggered: bool,
}

/// Run a full exploration walk, consuming an array of per-step rolls.
///
/// PHP: `$intAmount2 = floor(energy * 2)`, then iterate `for ($i=1; $i < $intAmount2; $i++)`.
/// Each half-energy unit is one step.  Stops early on encounter or bridge.
///
/// `step_rolls` must have at least `max_steps` entries.
pub fn run_exploration(
    region: ExploreRegion,
    requested_energy: f64,
    step_rolls: &[StepRolls],
    map_ctx: &mut MapContext,
    player_bridge_done: bool,
) -> ExploreResult {
    // PHP uses floor(energy * 2) as the loop bound, starting at i=1:
    // for ($i = 1; $i < floor(energy*2); $i++)
    // So max_steps = floor(energy * 2) - 1 (the loop runs i=1..max_steps-1)
    #[allow(clippy::cast_possible_truncation, clippy::cast_sign_loss)]
    let max_steps = (requested_energy * 2.0).floor() as usize;
    // PHP loop: for ($i = 1; $i < max_steps; $i++) → runs max_steps - 1 times
    let target_steps = if max_steps > 0 { max_steps - 1 } else { 0 };

    let mut result = ExploreResult {
        gold: 0,
        herbs: [0; 4],
        meteor: 0,
        energy_found: 0,
        maps_found: 0,
        astral_found: 0,
        steps_taken: 0,
        energy_cost: 0.0,
        monster_encountered: false,
        bridge_triggered: false,
    };

    for i in 0..target_steps {
        if i >= step_rolls.len() {
            break;
        }

        result.steps_taken += 1;

        let event = resolve_step(region, &step_rolls[i], map_ctx, player_bridge_done);

        match event {
            StepEvent::Nothing => {}
            StepEvent::Gold(g) => result.gold += g,
            StepEvent::Meteor(m) => result.meteor += m,
            StepEvent::Energy(e) => result.energy_found += e,
            StepEvent::Herbs(h) => {
                for (j, &val) in h.iter().enumerate() {
                    result.herbs[j] += val;
                }
            }
            StepEvent::Map => {
                result.maps_found += 1;
                map_ctx.maps_available -= 1;
                map_ctx.player_maps += 1;
            }
            StepEvent::Astral => result.astral_found += 1,
            StepEvent::MonsterEncounter => {
                result.monster_encountered = true;
                break;
            }
            StepEvent::BridgeOfDeath => {
                result.bridge_triggered = true;
                break;
            }
        }
    }

    // PHP: energy_cost = steps_taken / 2 (mountains: credited = gold only)
    // Forest: energy_cost = steps_taken / 2 - energy_found
    let raw_cost = f64::from(result.steps_taken) / 2.0;
    result.energy_cost = match region {
        ExploreRegion::Forest => (raw_cost - f64::from(result.energy_found)).max(0.0),
        ExploreRegion::Mountains => raw_cost,
    };

    result
}

// ---------------------------------------------------------------------------
// Bridge of Death
// ---------------------------------------------------------------------------

/// Phase of the Bridge of Death encounter.
#[derive(Debug, Clone, Copy, PartialEq, Eq)]
pub enum BridgePhase {
    /// First question — identity check.
    First,
    /// Second question — game name check.
    Second,
    /// Third question — random trivia from `bridge` table.
    Third,
}

/// Error from a bridge question.
#[derive(Debug, Clone, PartialEq, Eq)]
pub enum BridgeError {
    /// Wrong answer — player dies (HP → 0).
    WrongAnswer,
    /// Player hadn't started the bridge sequence.
    NotStarted,
    /// Player already completed the bridge.
    AlreadyDone,
}

/// Validate first bridge question: player must confirm their identity.
///
/// PHP: The player just checks a checkbox, answer is irrelevant.
pub fn bridge_first_confirmed(has_check: bool) -> bool {
    has_check
}

/// Validate second bridge question: "What is the name of the game?"
///
/// PHP: `strtolower($answer) == strtolower($gamename)`
pub fn bridge_check_game_name(answer: &str, game_name: &str) -> bool {
    answer.to_lowercase() == game_name.to_lowercase()
}

/// Validate third bridge question: compare against stored answer.
///
/// PHP: `$panswer == $answer->fields['answer']`
pub fn bridge_check_trivia(player_answer: &str, correct_answer: &str) -> bool {
    player_answer == correct_answer
}

/// Result of successfully completing the Bridge of Death.
///
/// The player receives a random equipment item scaled to their combat skill.
#[derive(Debug, Clone, PartialEq, Eq)]
pub struct BridgeReward {
    /// Whether the player has an antidote (`antidote == 'R'`), which saves
    /// them from death on wrong answer (HP set to 1 instead of 0).
    pub antidote_saves: bool,
}

/// Whether the player survives a wrong bridge answer due to antidote.
///
/// PHP: if `$player->antidote == 'R'`, HP → 1 instead of 0.
pub fn bridge_wrong_answer_survived(antidote: &str) -> bool {
    antidote == "R"
}

// ---------------------------------------------------------------------------
// Post-combat energy deduction
// ---------------------------------------------------------------------------

/// Energy to deduct after a turn-based exploration fight ends.
///
/// PHP: After a turn fight completes in explore, reduces energy by 1.
/// Only applied to turn-based fights (`type == 'T'`), not normal fights.
pub const EXPLORE_FIGHT_ENERGY_COST: f64 = 1.0;

/// Minimum energy after deduction (clamped to 0).
pub fn deduct_fight_energy(current_energy: f64) -> f64 {
    (current_energy - EXPLORE_FIGHT_ENERGY_COST).max(0.0)
}

// ---------------------------------------------------------------------------
// Tests
// ---------------------------------------------------------------------------

#[cfg(test)]
mod tests {
    use super::*;

    // --- Validation ---

    #[test]
    fn validate_explore_forest() {
        let r = validate_explore("Las", 100, 5.0, 2.0, 0, None);
        assert_eq!(r, Ok(ExploreRegion::Forest));
    }

    #[test]
    fn validate_explore_mountains() {
        let r = validate_explore("Góry", 100, 5.0, 2.0, 0, None);
        assert_eq!(r, Ok(ExploreRegion::Mountains));
    }

    #[test]
    fn validate_wrong_location() {
        let r = validate_explore("Altara", 100, 5.0, 2.0, 0, None);
        assert_eq!(r, Err(ExploreError::WrongLocation));
    }

    #[test]
    fn validate_dead() {
        let r = validate_explore("Las", 0, 5.0, 2.0, 0, None);
        assert_eq!(r, Err(ExploreError::PlayerDead));
    }

    #[test]
    fn validate_already_fighting() {
        let r = validate_explore("Las", 100, 5.0, 2.0, 42, Some("Goblin"));
        assert_eq!(
            r,
            Err(ExploreError::AlreadyFighting {
                monster_name: "Goblin".to_owned()
            })
        );
    }

    #[test]
    fn validate_amount_too_low() {
        let r = validate_explore("Las", 100, 5.0, 0.3, 0, None);
        assert_eq!(r, Err(ExploreError::AmountTooLow));
    }

    #[test]
    fn validate_insufficient_energy() {
        let r = validate_explore("Las", 100, 1.0, 2.0, 0, None);
        assert_eq!(r, Err(ExploreError::InsufficientEnergy));
    }

    // --- Region ---

    #[test]
    fn region_die_sizes() {
        assert_eq!(ExploreRegion::Forest.die_size(), 19);
        assert_eq!(ExploreRegion::Mountains.die_size(), 20);
    }

    // --- Step resolution ---

    fn rolls(event: i32, amount: i32) -> StepRolls {
        StepRolls {
            event_roll: event,
            amount_roll: amount,
            map_chance_roll: 1,
            astral_chance_roll: 1,
        }
    }

    fn default_map_ctx() -> MapContext {
        MapContext {
            maps_available: 10,
            player_maps: 5,
            is_hero: false,
        }
    }

    #[test]
    fn mountain_nothing_low_rolls() {
        for r in 1..=5 {
            assert_eq!(
                resolve_step(
                    ExploreRegion::Mountains,
                    &rolls(r, 1),
                    &default_map_ctx(),
                    false,
                ),
                StepEvent::Nothing
            );
        }
    }

    #[test]
    fn mountain_monster_encounter() {
        for r in 6..=8 {
            assert_eq!(
                resolve_step(
                    ExploreRegion::Mountains,
                    &rolls(r, 1),
                    &default_map_ctx(),
                    false,
                ),
                StepEvent::MonsterEncounter
            );
        }
    }

    #[test]
    fn mountain_gold() {
        assert_eq!(
            resolve_step(
                ExploreRegion::Mountains,
                &rolls(9, 500),
                &default_map_ctx(),
                false,
            ),
            StepEvent::Gold(500)
        );
    }

    #[test]
    fn mountain_meteor() {
        assert_eq!(
            resolve_step(
                ExploreRegion::Mountains,
                &rolls(10, 15),
                &default_map_ctx(),
                false,
            ),
            StepEvent::Meteor(15)
        );
    }

    #[test]
    fn mountain_herbs_illani() {
        for r in 11..=13 {
            assert_eq!(
                resolve_step(
                    ExploreRegion::Mountains,
                    &rolls(r, 7),
                    &default_map_ctx(),
                    false,
                ),
                StepEvent::Herbs([7, 0, 0, 0])
            );
        }
    }

    #[test]
    fn mountain_herbs_illanias() {
        for r in 14..=15 {
            assert_eq!(
                resolve_step(
                    ExploreRegion::Mountains,
                    &rolls(r, 3),
                    &default_map_ctx(),
                    false,
                ),
                StepEvent::Herbs([0, 3, 0, 0])
            );
        }
    }

    #[test]
    fn mountain_herbs_nutari() {
        assert_eq!(
            resolve_step(
                ExploreRegion::Mountains,
                &rolls(16, 4),
                &default_map_ctx(),
                false,
            ),
            StepEvent::Herbs([0, 0, 4, 0])
        );
    }

    #[test]
    fn mountain_bridge_of_death() {
        assert_eq!(
            resolve_step(
                ExploreRegion::Mountains,
                &rolls(17, 1),
                &default_map_ctx(),
                false,
            ),
            StepEvent::BridgeOfDeath
        );
    }

    #[test]
    fn mountain_bridge_already_done() {
        assert_eq!(
            resolve_step(
                ExploreRegion::Mountains,
                &rolls(17, 1),
                &default_map_ctx(),
                true,
            ),
            StepEvent::Nothing
        );
    }

    #[test]
    fn mountain_herbs_dynallca() {
        assert_eq!(
            resolve_step(
                ExploreRegion::Mountains,
                &rolls(18, 6),
                &default_map_ctx(),
                false,
            ),
            StepEvent::Herbs([0, 0, 0, 6])
        );
    }

    #[test]
    fn mountain_map_found() {
        let r = StepRolls {
            event_roll: 19,
            amount_roll: 1,
            map_chance_roll: 50,
            astral_chance_roll: 1,
        };
        assert_eq!(
            resolve_step(ExploreRegion::Mountains, &r, &default_map_ctx(), false),
            StepEvent::Map
        );
    }

    #[test]
    fn mountain_map_not_found_wrong_roll() {
        let r = StepRolls {
            event_roll: 19,
            amount_roll: 1,
            map_chance_roll: 25,
            astral_chance_roll: 1,
        };
        assert_eq!(
            resolve_step(ExploreRegion::Mountains, &r, &default_map_ctx(), false),
            StepEvent::Nothing
        );
    }

    #[test]
    fn mountain_map_not_found_hero() {
        let r = StepRolls {
            event_roll: 19,
            amount_roll: 1,
            map_chance_roll: 50,
            astral_chance_roll: 1,
        };
        let ctx = MapContext {
            maps_available: 10,
            player_maps: 5,
            is_hero: true,
        };
        assert_eq!(
            resolve_step(ExploreRegion::Mountains, &r, &ctx, false),
            StepEvent::Nothing
        );
    }

    #[test]
    fn mountain_map_not_found_too_many() {
        let r = StepRolls {
            event_roll: 19,
            amount_roll: 1,
            map_chance_roll: 50,
            astral_chance_roll: 1,
        };
        let ctx = MapContext {
            maps_available: 10,
            player_maps: 20,
            is_hero: false,
        };
        assert_eq!(
            resolve_step(ExploreRegion::Mountains, &r, &ctx, false),
            StepEvent::Nothing
        );
    }

    #[test]
    fn mountain_map_not_found_none_available() {
        let r = StepRolls {
            event_roll: 19,
            amount_roll: 1,
            map_chance_roll: 50,
            astral_chance_roll: 1,
        };
        let ctx = MapContext {
            maps_available: 0,
            player_maps: 5,
            is_hero: false,
        };
        assert_eq!(
            resolve_step(ExploreRegion::Mountains, &r, &ctx, false),
            StepEvent::Nothing
        );
    }

    #[test]
    fn mountain_astral_found() {
        let r = StepRolls {
            event_roll: 20,
            amount_roll: 1,
            map_chance_roll: 1,
            astral_chance_roll: 2,
        };
        assert_eq!(
            resolve_step(ExploreRegion::Mountains, &r, &default_map_ctx(), false),
            StepEvent::Astral
        );
    }

    #[test]
    fn mountain_astral_not_found() {
        let r = StepRolls {
            event_roll: 20,
            amount_roll: 1,
            map_chance_roll: 1,
            astral_chance_roll: 1,
        };
        assert_eq!(
            resolve_step(ExploreRegion::Mountains, &r, &default_map_ctx(), false),
            StepEvent::Nothing
        );
    }

    // --- Forest-specific events ---

    #[test]
    fn forest_energy() {
        assert_eq!(
            resolve_step(
                ExploreRegion::Forest,
                &rolls(10, 2),
                &default_map_ctx(),
                false,
            ),
            StepEvent::Energy(2)
        );
    }

    #[test]
    fn forest_dynallca_on_17() {
        assert_eq!(
            resolve_step(
                ExploreRegion::Forest,
                &rolls(17, 5),
                &default_map_ctx(),
                false,
            ),
            StepEvent::Herbs([0, 0, 0, 5])
        );
    }

    #[test]
    fn forest_map_on_18() {
        let r = StepRolls {
            event_roll: 18,
            amount_roll: 1,
            map_chance_roll: 50,
            astral_chance_roll: 1,
        };
        assert_eq!(
            resolve_step(ExploreRegion::Forest, &r, &default_map_ctx(), false),
            StepEvent::Map
        );
    }

    #[test]
    fn forest_astral_on_19() {
        let r = StepRolls {
            event_roll: 19,
            amount_roll: 1,
            map_chance_roll: 1,
            astral_chance_roll: 2,
        };
        assert_eq!(
            resolve_step(ExploreRegion::Forest, &r, &default_map_ctx(), false),
            StepEvent::Astral
        );
    }

    // --- Full walk ---

    #[test]
    fn full_walk_nothing_found() {
        // 2.0 energy → floor(2.0*2) = 4, loop runs i=1..3 → 3 steps
        let step_rolls: Vec<StepRolls> = (0..3).map(|_| rolls(1, 1)).collect();
        let mut ctx = default_map_ctx();
        let r = run_exploration(ExploreRegion::Forest, 2.0, &step_rolls, &mut ctx, false);
        assert_eq!(r.steps_taken, 3);
        assert!(!r.monster_encountered);
        assert!(!r.bridge_triggered);
        assert_eq!(r.gold, 0);
        assert_eq!(r.herbs, [0, 0, 0, 0]);
        // Cost: 3/2 = 1.5
        assert!((r.energy_cost - 1.5).abs() < f64::EPSILON);
    }

    #[test]
    fn full_walk_minimum_energy() {
        // 0.5 energy → floor(0.5*2)=1, loop runs i=1..0 → 0 steps
        let step_rolls: Vec<StepRolls> = vec![rolls(1, 1)];
        let mut ctx = default_map_ctx();
        let r = run_exploration(ExploreRegion::Forest, 0.5, &step_rolls, &mut ctx, false);
        assert_eq!(r.steps_taken, 0);
        assert!((r.energy_cost).abs() < f64::EPSILON);
    }

    #[test]
    fn full_walk_1_energy() {
        // 1.0 energy → floor(1.0*2)=2, loop runs i=1..1 → 1 step
        let step_rolls = vec![rolls(1, 1)];
        let mut ctx = default_map_ctx();
        let r = run_exploration(ExploreRegion::Forest, 1.0, &step_rolls, &mut ctx, false);
        assert_eq!(r.steps_taken, 1);
        // Cost: 1/2 = 0.5
        assert!((r.energy_cost - 0.5).abs() < f64::EPSILON);
    }

    #[test]
    fn full_walk_accumulates_loot() {
        // 3 gold + 5 illani herbs
        let step_rolls = vec![
            rolls(9, 300), // gold
            rolls(11, 5),  // illani
            rolls(9, 200), // more gold
        ];
        let mut ctx = default_map_ctx();
        let r = run_exploration(ExploreRegion::Mountains, 2.5, &step_rolls, &mut ctx, false);
        assert_eq!(r.gold, 500);
        assert_eq!(r.herbs[0], 5);
        assert_eq!(r.steps_taken, 3);
    }

    #[test]
    fn full_walk_stops_on_monster() {
        let step_rolls = vec![
            rolls(1, 0), // nothing
            rolls(7, 0), // monster!
            rolls(1, 0), // should not be reached
        ];
        let mut ctx = default_map_ctx();
        let r = run_exploration(ExploreRegion::Forest, 5.0, &step_rolls, &mut ctx, false);
        assert!(r.monster_encountered);
        assert_eq!(r.steps_taken, 2);
    }

    #[test]
    fn full_walk_stops_on_bridge() {
        let step_rolls = vec![
            rolls(1, 0),  // nothing
            rolls(17, 0), // bridge
            rolls(1, 0),  // should not be reached
        ];
        let mut ctx = default_map_ctx();
        let r = run_exploration(ExploreRegion::Mountains, 5.0, &step_rolls, &mut ctx, false);
        assert!(r.bridge_triggered);
        assert_eq!(r.steps_taken, 2);
    }

    #[test]
    fn full_walk_forest_energy_reduces_cost() {
        // 3 steps: nothing, energy+2, nothing
        let step_rolls = vec![
            rolls(1, 0),  // nothing
            rolls(10, 2), // energy
            rolls(1, 0),  // nothing
        ];
        let mut ctx = default_map_ctx();
        let r = run_exploration(ExploreRegion::Forest, 2.5, &step_rolls, &mut ctx, false);
        assert_eq!(r.energy_found, 2);
        // Cost: 3/2 - 2 = -0.5 → clamped to 0
        assert!((r.energy_cost).abs() < f64::EPSILON);
    }

    #[test]
    fn full_walk_map_updates_context() {
        let map_rolls = StepRolls {
            event_roll: 19,
            amount_roll: 1,
            map_chance_roll: 50,
            astral_chance_roll: 1,
        };
        // Mountain: map on 19
        let step_rolls = vec![map_rolls];
        let mut ctx = MapContext {
            maps_available: 5,
            player_maps: 3,
            is_hero: false,
        };
        let r = run_exploration(ExploreRegion::Mountains, 1.5, &step_rolls, &mut ctx, false);
        assert_eq!(r.maps_found, 1);
        assert_eq!(ctx.maps_available, 4);
        assert_eq!(ctx.player_maps, 4);
    }

    #[test]
    fn full_walk_mountain_energy_not_reduced_by_finds() {
        // Mountains don't get energy finds — meteor is at roll 10 not energy.
        let step_rolls = vec![rolls(10, 15)]; // meteor
        let mut ctx = default_map_ctx();
        let r = run_exploration(ExploreRegion::Mountains, 1.5, &step_rolls, &mut ctx, false);
        assert_eq!(r.meteor, 15);
        assert_eq!(r.energy_found, 0);
        // Cost: 1/2 = 0.5
        assert!((r.energy_cost - 0.5).abs() < f64::EPSILON);
    }

    // --- Bridge helpers ---

    #[test]
    fn bridge_game_name_case_insensitive() {
        assert!(bridge_check_game_name("vallheru", "Vallheru"));
        assert!(bridge_check_game_name("VALLHERU", "Vallheru"));
        assert!(!bridge_check_game_name("wrong", "Vallheru"));
    }

    #[test]
    fn bridge_trivia_exact_match() {
        assert!(bridge_check_trivia("42", "42"));
        assert!(!bridge_check_trivia("42", "43"));
    }

    #[test]
    fn bridge_wrong_answer_antidote() {
        assert!(bridge_wrong_answer_survived("R"));
        assert!(!bridge_wrong_answer_survived(""));
        assert!(!bridge_wrong_answer_survived("N"));
    }

    // --- Post-combat energy ---

    #[test]
    fn deduct_fight_energy_normal() {
        assert!((deduct_fight_energy(5.0) - 4.0).abs() < f64::EPSILON);
    }

    #[test]
    fn deduct_fight_energy_clamps_to_zero() {
        assert!((deduct_fight_energy(0.3)).abs() < f64::EPSILON);
    }
}
