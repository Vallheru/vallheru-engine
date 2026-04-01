//! Travel service: costs, methods, and destination logic.
//!
//! Implements the travel rules from `travel.php`. Players can move between
//! locations using three methods: caravan (gold), walking (energy), or
//! magic portal (gold). Each method has different costs depending on the
//! route.

use crate::location::{Location, MovementDenied, can_travel};

/// How the player travels between locations.
#[derive(Debug, Clone, Copy, PartialEq, Eq)]
pub enum TravelMethod {
    /// Travel by caravan — costs gold, low bandit encounter chance.
    Caravan,
    /// Walk on foot — costs energy, higher bandit encounter chance.
    Walk,
    /// Use a magic portal — costs 4000 gold, instant (no encounter).
    MagicPortal,
}

impl TravelMethod {
    /// Parse from a query-string value.
    pub fn from_param(s: &str) -> Option<Self> {
        match s {
            "caravan" => Some(Self::Caravan),
            "walk" => Some(Self::Walk),
            "magic" => Some(Self::MagicPortal),
            _ => None,
        }
    }
}

/// A named travel destination the player can reach from their current location.
#[derive(Debug, Clone, Copy, PartialEq, Eq)]
pub enum Destination {
    /// Góry Kazad-nar.
    Mountains,
    /// Las Avantiel.
    Forest,
    /// Ardulith (elf city).
    Ardulith,
    /// Altara (return to main city).
    Altara,
}

impl Destination {
    /// Parse from a query-string value (matching legacy `akcja` parameter).
    pub fn from_param(s: &str) -> Option<Self> {
        match s {
            "gory" => Some(Self::Mountains),
            "las" => Some(Self::Forest),
            "city2" => Some(Self::Ardulith),
            "powrot" => Some(Self::Altara),
            _ => None,
        }
    }

    /// The URL param value used in links.
    pub fn param(self) -> &'static str {
        match self {
            Self::Mountains => "gory",
            Self::Forest => "las",
            Self::Ardulith => "city2",
            Self::Altara => "powrot",
        }
    }

    /// The target `Location` for this destination.
    pub fn target_location(self) -> Location {
        match self {
            Self::Mountains => Location::Mountains,
            Self::Forest => Location::Forest,
            Self::Ardulith => Location::Ardulith,
            Self::Altara => Location::Altara,
        }
    }

    /// Human-readable Polish label for the arrival message.
    pub fn arrival_label(self) -> &'static str {
        match self {
            Self::Mountains => "Gór Kazad-nar",
            Self::Forest => "Lasu Avantiel",
            Self::Ardulith => "Ardulith",
            Self::Altara => "Altary",
        }
    }

    /// Display name shown in links.
    pub fn display_name(self) -> &'static str {
        match self {
            Self::Mountains => "Góry Kazad-nar",
            Self::Forest => "Las Avantiel",
            Self::Ardulith => "Ardulith",
            Self::Altara => "Altara",
        }
    }
}

/// Error when a travel attempt is refused.
#[derive(Debug, Clone, PartialEq, Eq)]
pub enum TravelError {
    /// Movement guard denied the trip.
    MovementDenied(MovementDenied),
    /// Not enough gold for caravan or magic portal.
    InsufficientGold { need: i32, have: i32 },
    /// Not enough energy for walking.
    InsufficientEnergy { need: i32, have: i32 },
}

/// Available destinations from a given location, matching PHP travel.php logic.
pub fn available_destinations(from: Location) -> &'static [Destination] {
    match from {
        Location::Altara => &[
            Destination::Mountains,
            Destination::Forest,
            Destination::Ardulith,
        ],
        Location::Ardulith => &[Destination::Mountains, Destination::Altara],
        Location::Forest => &[Destination::Altara],
        Location::Mountains => &[Destination::Forest, Destination::Altara],
        _ => &[],
    }
}

/// Compute the gold cost for caravan travel from `from` to `dest`.
///
/// Ardulith↔Mountains and Mountains↔Forest cost 1200, all others 1000.
pub fn caravan_cost(from: Location, dest: Destination) -> i32 {
    let is_indirect = matches!(
        (from, dest),
        (Location::Ardulith, Destination::Mountains) | (Location::Mountains, Destination::Forest)
    );
    if is_indirect { 1200 } else { 1000 }
}

/// Compute the energy cost for walking from `from` to `dest`.
///
/// Ardulith↔Mountains and Mountains↔Forest cost 6, all others 5.
pub fn walk_cost(from: Location, dest: Destination) -> i32 {
    let is_indirect = matches!(
        (from, dest),
        (Location::Ardulith, Destination::Mountains) | (Location::Mountains, Destination::Forest)
    );
    if is_indirect { 6 } else { 5 }
}

/// Magic portal always costs 4000 gold.
pub const MAGIC_PORTAL_COST: i32 = 4000;

/// Compute the cost for a travel method+route combo.
pub fn travel_cost(method: TravelMethod, from: Location, dest: Destination) -> i32 {
    match method {
        TravelMethod::Caravan => caravan_cost(from, dest),
        TravelMethod::Walk => walk_cost(from, dest),
        TravelMethod::MagicPortal => MAGIC_PORTAL_COST,
    }
}

/// Input for travel validation.
pub struct TravelAttempt {
    pub from: Location,
    pub dest: Destination,
    pub method: TravelMethod,
    pub hp: i32,
    pub fight_id: i32,
    pub is_immune: bool,
    pub credits: i32,
    pub energy: f64,
}

/// Validate and compute a travel attempt. Returns `Ok(cost)` if the
/// trip is allowed, or an appropriate error.
pub fn validate_travel(attempt: &TravelAttempt) -> Result<i32, TravelError> {
    let to = attempt.dest.target_location();
    can_travel(
        attempt.from,
        to,
        attempt.hp,
        attempt.fight_id,
        attempt.is_immune,
    )
    .map_err(TravelError::MovementDenied)?;

    let cost = travel_cost(attempt.method, attempt.from, attempt.dest);

    match attempt.method {
        TravelMethod::Caravan | TravelMethod::MagicPortal => {
            if attempt.credits < cost {
                return Err(TravelError::InsufficientGold {
                    need: cost,
                    have: attempt.credits,
                });
            }
        }
        TravelMethod::Walk => {
            #[allow(clippy::cast_possible_truncation)]
            let energy_int = attempt.energy as i32;
            if energy_int < cost {
                return Err(TravelError::InsufficientEnergy {
                    need: cost,
                    have: energy_int,
                });
            }
        }
    }

    Ok(cost)
}

// ---------------------------------------------------------------------------
// Bandit encounter formulas
// ---------------------------------------------------------------------------

/// The encounter chance (out of 100) for each travel method.
/// Magic portal is immune to encounters.
pub fn bandit_encounter_chance(method: TravelMethod) -> i32 {
    match method {
        TravelMethod::Caravan => 20,
        TravelMethod::Walk => 30,
        TravelMethod::MagicPortal => 0,
    }
}

/// Whether a bandit encounter triggers given a roll in 1..=100.
pub fn bandit_encounter_triggers(method: TravelMethod, roll_1_to_100: i32) -> bool {
    let chance = bandit_encounter_chance(method);
    chance > 0 && roll_1_to_100 <= chance
}

/// Result of a ransom attempt.
#[derive(Debug, PartialEq, Eq)]
pub enum RansomResult {
    /// Player pays the given amount of gold.
    Pay(i32),
    /// Player can't pay (not enough gold or the roll forces a fight).
    ForceFight,
}

/// Calculate the ransom a player must pay to avoid a bandit fight.
///
/// PHP logic:
/// - base cost = travel gold cost (caravan) or 0 (walk)
/// - roll 1..=100 determines multiplier bracket
/// - roll < 6  → 5 × `stat_level_sum`
/// - roll < 26 → 15 × `stat_level_sum`
/// - roll < 76 → 25 × `stat_level_sum`
/// - roll < 96 → 50 × `stat_level_sum`
/// - roll >= 96 → cost set to 0 (supposed to be free but triggers fight in PHP)
///
/// If the final cost exceeds `player_gold` or is 0, the player must fight.
/// Otherwise `cost - base_travel_cost` is deducted.
pub fn calculate_ransom(
    method: TravelMethod,
    travel_gold_cost: i32,
    stat_level_sum: i32,
    player_gold: i32,
    roll_1_to_100: i32,
) -> RansomResult {
    let base = match method {
        TravelMethod::Caravan => travel_gold_cost,
        _ => 0,
    };

    let cost = if roll_1_to_100 < 6 {
        base + 5 * stat_level_sum
    } else if roll_1_to_100 < 26 {
        base + 15 * stat_level_sum
    } else if roll_1_to_100 < 76 {
        base + 25 * stat_level_sum
    } else if roll_1_to_100 < 96 {
        base + 50 * stat_level_sum
    } else {
        // PHP: sets cost to 0 which triggers fight. We preserve this behavior.
        0
    };

    if cost == 0 || cost > player_gold {
        RansomResult::ForceFight
    } else {
        // Deduct only the ransom portion (base travel cost not charged again).
        RansomResult::Pay(cost - base)
    }
}

/// Bandit escape check result.
#[derive(Debug)]
pub struct EscapeResult {
    /// Whether the escape succeeded.
    pub escaped: bool,
    /// XP gained (both for speed stat and perception skill).
    pub xp: i64,
}

/// Resolve a bandit escape attempt.
///
/// PHP logic:
/// - 4 bandit rolls (1..=75 each)
/// - chance = (`player_roll` + `speed_mod` + perception) - (`bandit_roll[0]` + `bandit_extra_roll`)
/// - If chance > 0: escape, XP = ceil(sum of 4 rolls / 100)
/// - If chance <= 0: fail, XP = 1
pub fn resolve_bandit_escape(
    player_speed: i32,
    perception: i32,
    player_roll_1_to_100: i32,
    bandit_rolls: &[i32; 4],
    bandit_extra_roll_1_to_100: i32,
) -> EscapeResult {
    let chance = (player_roll_1_to_100 + player_speed + perception)
        - (bandit_rolls[0] + bandit_extra_roll_1_to_100);

    if chance > 0 {
        let sum: i32 = bandit_rolls.iter().copied().sum();
        #[allow(clippy::cast_possible_truncation)]
        let xp = (f64::from(sum) / 100.0).ceil() as i64;
        EscapeResult {
            escaped: true,
            xp: xp.max(1),
        }
    } else {
        EscapeResult {
            escaped: false,
            xp: 1,
        }
    }
}

// ---------------------------------------------------------------------------
// Tests
// ---------------------------------------------------------------------------

#[cfg(test)]
mod tests {
    use super::*;

    #[test]
    fn destinations_from_altara() {
        let dests = available_destinations(Location::Altara);
        assert_eq!(dests.len(), 3);
        assert!(dests.contains(&Destination::Mountains));
        assert!(dests.contains(&Destination::Forest));
        assert!(dests.contains(&Destination::Ardulith));
    }

    #[test]
    fn destinations_from_ardulith() {
        let dests = available_destinations(Location::Ardulith);
        assert_eq!(dests.len(), 2);
        assert!(dests.contains(&Destination::Mountains));
        assert!(dests.contains(&Destination::Altara));
    }

    #[test]
    fn destinations_from_forest() {
        let dests = available_destinations(Location::Forest);
        assert_eq!(dests, &[Destination::Altara]);
    }

    #[test]
    fn destinations_from_mountains() {
        let dests = available_destinations(Location::Mountains);
        assert_eq!(dests.len(), 2);
    }

    #[test]
    fn caravan_cost_normal_route() {
        assert_eq!(caravan_cost(Location::Altara, Destination::Mountains), 1000);
        assert_eq!(caravan_cost(Location::Altara, Destination::Forest), 1000);
        assert_eq!(caravan_cost(Location::Altara, Destination::Ardulith), 1000);
    }

    #[test]
    fn caravan_cost_indirect_route() {
        assert_eq!(
            caravan_cost(Location::Ardulith, Destination::Mountains),
            1200
        );
        assert_eq!(caravan_cost(Location::Mountains, Destination::Forest), 1200);
    }

    #[test]
    fn walk_cost_values() {
        assert_eq!(walk_cost(Location::Altara, Destination::Mountains), 5);
        assert_eq!(walk_cost(Location::Ardulith, Destination::Mountains), 6);
    }

    fn attempt(method: TravelMethod, credits: i32, energy: f64, hp: i32) -> TravelAttempt {
        TravelAttempt {
            from: Location::Altara,
            dest: Destination::Mountains,
            method,
            hp,
            fight_id: 0,
            is_immune: false,
            credits,
            energy,
        }
    }

    #[test]
    fn validate_travel_success() {
        let result = validate_travel(&attempt(TravelMethod::Caravan, 5000, 10.0, 100));
        assert_eq!(result, Ok(1000));
    }

    #[test]
    fn validate_travel_insufficient_gold() {
        let result = validate_travel(&attempt(TravelMethod::Caravan, 500, 10.0, 100));
        assert!(matches!(result, Err(TravelError::InsufficientGold { .. })));
    }

    #[test]
    fn validate_travel_insufficient_energy() {
        let result = validate_travel(&attempt(TravelMethod::Walk, 5000, 2.0, 100));
        assert!(matches!(
            result,
            Err(TravelError::InsufficientEnergy { .. })
        ));
    }

    #[test]
    fn validate_travel_dead() {
        let result = validate_travel(&attempt(TravelMethod::Walk, 5000, 10.0, 0));
        assert!(matches!(
            result,
            Err(TravelError::MovementDenied(MovementDenied::Dead))
        ));
    }

    #[test]
    fn destination_roundtrip() {
        for dest in [
            Destination::Mountains,
            Destination::Forest,
            Destination::Ardulith,
            Destination::Altara,
        ] {
            assert_eq!(Destination::from_param(dest.param()), Some(dest));
        }
    }

    // -----------------------------------------------------------------------
    // Table-driven navigation parity tests (MP-07-06)
    // -----------------------------------------------------------------------

    /// Every starting location should have a consistent set of available
    /// destinations. This guards against accidentally adding or removing
    /// travel routes.
    #[test]
    fn destination_availability_table() {
        use Location::*;
        let cases: &[(Location, usize)] = &[
            (Altara, 3),    // Mountains, Forest, Ardulith
            (Ardulith, 2),  // Mountains, Altara
            (Mountains, 2), // Forest, Altara
            (Forest, 1),    // Altara
            (Travelling, 0),
            (Dungeon, 0),
            (Portal, 0),
            (AstralPlane, 0),
            (Adventure, 0),
        ];
        for &(from, count) in cases {
            assert_eq!(
                available_destinations(from).len(),
                count,
                "available_destinations({from:?}).len()"
            );
        }
    }

    /// Caravan and walk costs should follow the indirect-route rule.
    #[test]
    fn cost_matrix_table() {
        use Location::*;
        // (from, dest, caravan, walk)
        let cases: &[(Location, Destination, i32, i32)] = &[
            (Altara, Destination::Mountains, 1000, 5),
            (Altara, Destination::Forest, 1000, 5),
            (Altara, Destination::Ardulith, 1000, 5),
            (Ardulith, Destination::Mountains, 1200, 6),
            (Ardulith, Destination::Altara, 1000, 5),
            (Mountains, Destination::Forest, 1200, 6),
            (Mountains, Destination::Altara, 1000, 5),
            (Forest, Destination::Altara, 1000, 5),
        ];
        for &(from, dest, expected_caravan, expected_walk) in cases {
            assert_eq!(
                caravan_cost(from, dest),
                expected_caravan,
                "caravan_cost({from:?}, {dest:?})"
            );
            assert_eq!(
                walk_cost(from, dest),
                expected_walk,
                "walk_cost({from:?}, {dest:?})"
            );
        }
    }

    /// Magic portal cost is constant regardless of route.
    #[test]
    fn magic_portal_cost_is_constant() {
        assert_eq!(MAGIC_PORTAL_COST, 4000);
        assert_eq!(
            travel_cost(
                TravelMethod::MagicPortal,
                Location::Altara,
                Destination::Mountains
            ),
            4000
        );
        assert_eq!(
            travel_cost(
                TravelMethod::MagicPortal,
                Location::Ardulith,
                Destination::Altara
            ),
            4000
        );
    }

    /// `validate_travel` with all three methods for a well-funded player
    /// should succeed and return the correct cost.
    #[test]
    fn validate_travel_all_methods_funded() {
        for method in [
            TravelMethod::Caravan,
            TravelMethod::Walk,
            TravelMethod::MagicPortal,
        ] {
            let a = TravelAttempt {
                from: Location::Altara,
                dest: Destination::Mountains,
                method,
                hp: 100,
                fight_id: 0,
                is_immune: false,
                credits: 10_000,
                energy: 100.0,
            };
            let result = validate_travel(&a);
            assert!(
                result.is_ok(),
                "validate_travel failed for {method:?}: {result:?}"
            );
            let cost = result.unwrap();
            assert_eq!(
                cost,
                travel_cost(method, Location::Altara, Destination::Mountains)
            );
        }
    }

    /// Portal denial: magic portal to non-Portal location should still work
    /// (the portal is just a travel method, not a location check at this level).
    /// Portal *location* entry is guarded by `can_travel`.
    #[test]
    fn magic_portal_method_between_cities() {
        let a = TravelAttempt {
            from: Location::Altara,
            dest: Destination::Ardulith,
            method: TravelMethod::MagicPortal,
            hp: 100,
            fight_id: 0,
            is_immune: false,
            credits: 10_000,
            energy: 100.0,
        };
        assert_eq!(validate_travel(&a), Ok(4000));
    }

    // -----------------------------------------------------------------------
    // Bandit encounter tests
    // -----------------------------------------------------------------------

    #[test]
    fn bandit_encounter_chance_values() {
        assert_eq!(bandit_encounter_chance(TravelMethod::Caravan), 20);
        assert_eq!(bandit_encounter_chance(TravelMethod::Walk), 30);
        assert_eq!(bandit_encounter_chance(TravelMethod::MagicPortal), 0);
    }

    #[test]
    fn bandit_encounter_trigger_table() {
        // Caravan: 20% chance → rolls 1..=20 trigger, 21..=100 don't.
        assert!(bandit_encounter_triggers(TravelMethod::Caravan, 1));
        assert!(bandit_encounter_triggers(TravelMethod::Caravan, 20));
        assert!(!bandit_encounter_triggers(TravelMethod::Caravan, 21));
        assert!(!bandit_encounter_triggers(TravelMethod::Caravan, 100));

        // Walk: 30% chance.
        assert!(bandit_encounter_triggers(TravelMethod::Walk, 30));
        assert!(!bandit_encounter_triggers(TravelMethod::Walk, 31));

        // Magic portal: never triggers.
        assert!(!bandit_encounter_triggers(TravelMethod::MagicPortal, 1));
    }

    #[test]
    fn ransom_low_roll_bracket() {
        // roll < 6 → 5 × stat_level_sum
        let result = calculate_ransom(TravelMethod::Walk, 0, 100, 10_000, 3);
        assert_eq!(result, RansomResult::Pay(500)); // 0 + 5*100 = 500, base=0
    }

    #[test]
    fn ransom_medium_roll_bracket() {
        // roll 6..25 → 15 × stat_level_sum
        let result = calculate_ransom(TravelMethod::Walk, 0, 100, 10_000, 10);
        assert_eq!(result, RansomResult::Pay(1500));
    }

    #[test]
    fn ransom_high_roll_bracket() {
        // roll 26..75 → 25 × stat_level_sum
        let result = calculate_ransom(TravelMethod::Walk, 0, 100, 10_000, 50);
        assert_eq!(result, RansomResult::Pay(2500));
    }

    #[test]
    fn ransom_very_high_roll_bracket() {
        // roll 76..95 → 50 × stat_level_sum
        let result = calculate_ransom(TravelMethod::Walk, 0, 100, 10_000, 80);
        assert_eq!(result, RansomResult::Pay(5000));
    }

    #[test]
    fn ransom_free_roll_forces_fight() {
        // roll >= 96 → cost = 0 → fight (PHP bug preserved)
        let result = calculate_ransom(TravelMethod::Walk, 0, 100, 10_000, 96);
        assert_eq!(result, RansomResult::ForceFight);
    }

    #[test]
    fn ransom_caravan_includes_base() {
        // Caravan base = 1000 gold, roll < 6 → cost = 1000 + 5*10 = 1050
        // Deduction = 1050 - 1000 = 50
        let result = calculate_ransom(TravelMethod::Caravan, 1000, 10, 5000, 3);
        assert_eq!(result, RansomResult::Pay(50));
    }

    #[test]
    fn ransom_too_expensive_forces_fight() {
        let result = calculate_ransom(TravelMethod::Walk, 0, 1000, 100, 50);
        // 25 * 1000 = 25000 > 100 gold
        assert_eq!(result, RansomResult::ForceFight);
    }

    #[test]
    fn escape_success_gives_xp() {
        // High player stats + good roll → escape
        let result = resolve_bandit_escape(50, 30, 80, &[10, 20, 30, 40], 50);
        // chance = (80 + 50 + 30) - (10 + 50) = 100 > 0 → escape
        // xp = ceil((10+20+30+40)/100) = ceil(1.0) = 1
        assert!(result.escaped);
        assert_eq!(result.xp, 1);
    }

    #[test]
    fn escape_failure() {
        // Low player stats → fail
        let result = resolve_bandit_escape(5, 5, 10, &[70, 70, 70, 70], 90);
        // chance = (10 + 5 + 5) - (70 + 90) = -140 ≤ 0 → fail
        assert!(!result.escaped);
        assert_eq!(result.xp, 1);
    }

    #[test]
    fn escape_xp_ceiling() {
        let result = resolve_bandit_escape(50, 30, 80, &[75, 75, 75, 75], 10);
        // chance = (80+50+30) - (75+10) = 75 > 0 → escape
        // xp = ceil(300/100) = 3
        assert!(result.escaped);
        assert_eq!(result.xp, 3);
    }
}
