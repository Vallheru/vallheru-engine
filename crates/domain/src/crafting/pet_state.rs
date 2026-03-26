//! Active pet state management, pet combat bonuses, survival checks, and
//! core ranking logic.
//!
//! A "core" can be in one of four activation states:
//!
//! | DB value | Meaning                                      |
//! |----------|----------------------------------------------|
//! | `N`      | Inactive — sitting in library                |
//! | `T`      | Training — active in core arena system        |
//! | `B`      | Battle — the player's active combat companion |
//! | `Y`      | Arena — currently fighting in core arena      |

use super::breeding::CoreType;

// ---------------------------------------------------------------------------
// Activation states
// ---------------------------------------------------------------------------

/// Activation state of a core pet stored in the `active` column.
#[derive(Debug, Clone, Copy, PartialEq, Eq)]
pub enum CoreActive {
    /// Inactive, sitting in player library.
    Inactive,
    /// Entered into the core arena system (training mode).
    Training,
    /// Active combat companion for PvE/PvP.
    Battle,
    /// Currently fighting in a core arena bout.
    Arena,
}

impl CoreActive {
    pub fn from_db(s: &str) -> Option<Self> {
        match s {
            "N" => Some(Self::Inactive),
            "T" => Some(Self::Training),
            "B" => Some(Self::Battle),
            "Y" => Some(Self::Arena),
            _ => None,
        }
    }

    pub fn as_db(&self) -> &'static str {
        match self {
            Self::Inactive => "N",
            Self::Training => "T",
            Self::Battle => "B",
            Self::Arena => "Y",
        }
    }
}

// ---------------------------------------------------------------------------
// Active battle pet snapshot
// ---------------------------------------------------------------------------

/// Snapshot of the player's active battle pet used in combat calculations.
///
/// PHP: `$player->pet = array(core_id, power, defense)`.
#[derive(Debug, Clone, Copy, PartialEq)]
pub struct BattlePet {
    pub core_id: i32,
    pub power: f64,
    pub defense: f64,
}

/// Represents "no active battle pet".
pub const NO_BATTLE_PET: Option<BattlePet> = None;

// ---------------------------------------------------------------------------
// Activation validation
// ---------------------------------------------------------------------------

/// Errors that prevent a pet activation state change.
#[derive(Debug, Clone, Copy, PartialEq, Eq)]
pub enum ActivationError {
    /// Cannot use a dead core in battle.
    Dead,
    /// Core is currently in the arena — cannot reassign.
    InArena,
}

/// Check whether a core can be assigned as the battle pet.
///
/// PHP validates: status != Dead, active != Y (arena).
pub fn can_set_battle(
    status_alive: bool,
    current_active: CoreActive,
) -> Result<(), ActivationError> {
    if !status_alive {
        return Err(ActivationError::Dead);
    }
    if current_active == CoreActive::Arena {
        return Err(ActivationError::InArena);
    }
    Ok(())
}

// ---------------------------------------------------------------------------
// Pet combat bonuses
// ---------------------------------------------------------------------------

/// Pet bonus applied to player attack damage.
///
/// PHP: `damage += min(pet_power, weapon_skill_level)`.
pub fn pet_attack_bonus(pet: &BattlePet, weapon_skill_level: f64) -> f64 {
    pet.power.min(weapon_skill_level)
}

/// Pet bonus applied to player defense at the hit location.
///
/// PHP: `defpower = min(pet_defense, dodge_skill_level)`.
pub fn pet_defense_bonus(pet: &BattlePet, dodge_skill_level: f64) -> f64 {
    pet.defense.min(dodge_skill_level)
}

// ---------------------------------------------------------------------------
// Pet survival after combat
// ---------------------------------------------------------------------------

/// Check whether the pet survives after a battle.
///
/// If the player **lost** the battle, the pet always dies (`roll` is forced to 1).
/// If the player **won**, the pet dies only on `roll == 1` (1% chance when drawn
/// from `1..=100`).
///
/// Returns `true` if the pet survives.
pub fn pet_survives(player_lost: bool, roll_1_to_100: i32) -> bool {
    if player_lost {
        // PHP: $intRoll = 1 when $blnLost is true → pet always dies
        false
    } else {
        roll_1_to_100 != 1
    }
}

// ---------------------------------------------------------------------------
// Core ranking
// ---------------------------------------------------------------------------

/// The 6 ranked core types displayed in the Hall of Fame.
///
/// Hybrid and Secret types are excluded from ranking.
pub const RANKED_TYPES: [CoreType; 6] = [
    CoreType::Plant,
    CoreType::Aqua,
    CoreType::Material,
    CoreType::Element,
    CoreType::Alien,
    CoreType::Ancient,
];

/// Maximum number of cores shown per type in the ranking.
pub const RANKING_LIMIT: i32 = 5;

/// One entry in the core ranking leaderboard.
#[derive(Debug, Clone, PartialEq, Eq)]
pub struct RankingEntry {
    pub core_id: i32,
    /// Display name: `corename` if set, otherwise `name`.
    pub display_name: String,
    pub wins: i32,
}

/// Resolve the display name for a core.
///
/// PHP: `if (!empty($corename)) { $name = $corename; } else { $name = $name; }`
pub fn core_display_name<'a>(name: &'a str, corename: &'a str) -> &'a str {
    if corename.is_empty() { name } else { corename }
}

// ---------------------------------------------------------------------------
// Tests
// ---------------------------------------------------------------------------

#[cfg(test)]
mod tests {
    use super::*;

    // --- CoreActive round-trip ---

    #[test]
    fn active_round_trip() {
        for (db, expected) in [
            ("N", CoreActive::Inactive),
            ("T", CoreActive::Training),
            ("B", CoreActive::Battle),
            ("Y", CoreActive::Arena),
        ] {
            let parsed = CoreActive::from_db(db).unwrap();
            assert_eq!(parsed, expected);
            assert_eq!(parsed.as_db(), db);
        }
    }

    #[test]
    fn active_invalid() {
        assert_eq!(CoreActive::from_db("X"), None);
        assert_eq!(CoreActive::from_db(""), None);
    }

    // --- Activation validation ---

    #[test]
    fn can_set_battle_alive_inactive() {
        assert!(can_set_battle(true, CoreActive::Inactive).is_ok());
    }

    #[test]
    fn can_set_battle_alive_training() {
        assert!(can_set_battle(true, CoreActive::Training).is_ok());
    }

    #[test]
    fn can_set_battle_alive_already_battle() {
        // Re-selecting the same pet is fine at domain level.
        assert!(can_set_battle(true, CoreActive::Battle).is_ok());
    }

    #[test]
    fn cannot_set_battle_dead() {
        assert_eq!(
            can_set_battle(false, CoreActive::Inactive),
            Err(ActivationError::Dead)
        );
    }

    #[test]
    fn cannot_set_battle_in_arena() {
        assert_eq!(
            can_set_battle(true, CoreActive::Arena),
            Err(ActivationError::InArena)
        );
    }

    // --- Pet combat bonuses ---

    #[test]
    fn attack_bonus_pet_weaker() {
        let pet = BattlePet {
            core_id: 1,
            power: 5.0,
            defense: 3.0,
        };
        // pet power < weapon skill → use pet power
        assert!((pet_attack_bonus(&pet, 10.0) - 5.0).abs() < f64::EPSILON);
    }

    #[test]
    fn attack_bonus_skill_weaker() {
        let pet = BattlePet {
            core_id: 1,
            power: 15.0,
            defense: 3.0,
        };
        // weapon skill < pet power → use weapon skill
        assert!((pet_attack_bonus(&pet, 10.0) - 10.0).abs() < f64::EPSILON);
    }

    #[test]
    fn defense_bonus_pet_weaker() {
        let pet = BattlePet {
            core_id: 1,
            power: 10.0,
            defense: 4.0,
        };
        assert!((pet_defense_bonus(&pet, 8.0) - 4.0).abs() < f64::EPSILON);
    }

    #[test]
    fn defense_bonus_skill_weaker() {
        let pet = BattlePet {
            core_id: 1,
            power: 10.0,
            defense: 12.0,
        };
        assert!((pet_defense_bonus(&pet, 8.0) - 8.0).abs() < f64::EPSILON);
    }

    // --- Pet survival ---

    #[test]
    fn pet_always_dies_on_loss() {
        // Regardless of roll, losing means death.
        assert!(!pet_survives(true, 50));
        assert!(!pet_survives(true, 1));
        assert!(!pet_survives(true, 100));
    }

    #[test]
    fn pet_survives_on_win_normal_roll() {
        assert!(pet_survives(false, 2));
        assert!(pet_survives(false, 50));
        assert!(pet_survives(false, 100));
    }

    #[test]
    fn pet_dies_on_win_unlucky_roll() {
        assert!(!pet_survives(false, 1));
    }

    // --- Display name ---

    #[test]
    fn display_name_prefers_corename() {
        assert_eq!(core_display_name("Wilk", "Fluffy"), "Fluffy");
    }

    #[test]
    fn display_name_falls_back_to_species() {
        assert_eq!(core_display_name("Wilk", ""), "Wilk");
    }

    // --- Ranking constants ---

    #[test]
    fn ranked_types_excludes_hybrid_secret() {
        assert!(!RANKED_TYPES.contains(&CoreType::Hybrid));
        assert!(!RANKED_TYPES.contains(&CoreType::Secret));
        assert_eq!(RANKED_TYPES.len(), 6);
    }
}
