//! Shared workshop action pattern for profession actions.
//!
//! Every profession in the game follows the same high-level workflow:
//!
//! 1. **Precondition check** — location, skill level, energy, required inputs.
//! 2. **Consume inputs** — energy, gold, materials (ore, herbs, etc.).
//! 3. **Roll for outcome** — RNG determines success and quality.
//! 4. **Produce outputs** — items, resources, XP gains.
//!
//! This module provides shared types so each profession reuses the same
//! validation and consumption structure rather than inventing its own.

/// A named material quantity consumed or produced by a workshop action.
#[derive(Debug, Clone, PartialEq, Eq)]
pub struct MaterialCost {
    /// Column or resource key (e.g. `"iron"`, `"copper"`, `"illani"`).
    pub resource_key: String,
    /// Quantity required (for inputs) or produced (for outputs).
    pub quantity: i32,
}

/// Inputs required before a workshop action can proceed.
#[derive(Debug, Clone)]
pub struct WorkshopInputs {
    /// Energy cost (deducted from `player.energy`). Must be > 0.
    pub energy_cost: f64,
    /// Gold cost (deducted from `player.credits`). 0 if free.
    pub gold_cost: i32,
    /// Materials consumed (e.g. metal bars, herbs). May be empty.
    pub materials: Vec<MaterialCost>,
    /// Minimum skill level required. `None` if no skill check.
    pub min_skill_level: Option<i32>,
}

/// Errors during precondition validation.
#[derive(Debug, Clone, PartialEq)]
pub enum WorkshopError {
    /// Player is not in the right location.
    WrongLocation { required: String, actual: String },
    /// Not enough energy to perform the action.
    InsufficientEnergy { required: f64, available: f64 },
    /// Not enough gold to pay the cost.
    InsufficientGold { required: i32, available: i32 },
    /// Not enough of a material.
    InsufficientMaterial {
        resource_key: String,
        required: i32,
        available: i32,
    },
    /// Skill level too low.
    SkillTooLow { required: i32, actual: i32 },
    /// Generic validation failure.
    InvalidAction(String),
}

impl std::fmt::Display for WorkshopError {
    fn fmt(&self, f: &mut std::fmt::Formatter<'_>) -> std::fmt::Result {
        match self {
            Self::WrongLocation { required, actual } => {
                write!(f, "Must be in {required}, currently in {actual}")
            }
            Self::InsufficientEnergy {
                required,
                available,
            } => write!(f, "Need {required} energy, have {available}"),
            Self::InsufficientGold {
                required,
                available,
            } => write!(f, "Need {required} gold, have {available}"),
            Self::InsufficientMaterial {
                resource_key,
                required,
                available,
            } => write!(f, "Need {required} {resource_key}, have {available}"),
            Self::SkillTooLow { required, actual } => {
                write!(f, "Skill level {actual} too low, need {required}")
            }
            Self::InvalidAction(msg) => write!(f, "{msg}"),
        }
    }
}

impl std::error::Error for WorkshopError {}

/// The outcome of a workshop action roll.
#[derive(Debug, Clone, PartialEq, Eq)]
pub enum WorkshopOutcome {
    /// Action succeeded with standard quality.
    Success,
    /// Action succeeded with enhanced quality (special prefix, bonus stats).
    CriticalSuccess {
        /// Flavor label for the enhancement (e.g. "Dragon", "Dwarven", "Elven").
        quality_label: String,
    },
    /// Action failed — inputs were consumed but no output produced.
    Failure,
}

/// Validate that a player meets the energy requirement.
pub fn check_energy(available: f64, required: f64) -> Result<(), WorkshopError> {
    if available < required {
        return Err(WorkshopError::InsufficientEnergy {
            required,
            available,
        });
    }
    Ok(())
}

/// Validate that a player can afford the gold cost.
pub fn check_gold(available: i32, required: i32) -> Result<(), WorkshopError> {
    if required > 0 && available < required {
        return Err(WorkshopError::InsufficientGold {
            required,
            available,
        });
    }
    Ok(())
}

/// Validate that a player has enough of a specific material.
pub fn check_material(
    resource_key: &str,
    available: i32,
    required: i32,
) -> Result<(), WorkshopError> {
    if available < required {
        return Err(WorkshopError::InsufficientMaterial {
            resource_key: resource_key.to_owned(),
            required,
            available,
        });
    }
    Ok(())
}

/// Validate that a player's skill level meets the minimum.
pub fn check_skill(actual: i32, required: i32) -> Result<(), WorkshopError> {
    if actual < required {
        return Err(WorkshopError::SkillTooLow { required, actual });
    }
    Ok(())
}

/// Validate that the player is in the required location.
pub fn check_location(actual: &str, required: &str) -> Result<(), WorkshopError> {
    if actual != required {
        return Err(WorkshopError::WrongLocation {
            required: required.to_owned(),
            actual: actual.to_owned(),
        });
    }
    Ok(())
}

/// Validate all inputs for a workshop action at once.
///
/// `available_materials` should provide the current quantity for each
/// resource key listed in `inputs.materials`. Pass a lookup closure.
pub fn validate_inputs(
    inputs: &WorkshopInputs,
    player_energy: f64,
    player_gold: i32,
    player_skill: Option<i32>,
    material_lookup: impl Fn(&str) -> i32,
) -> Result<(), WorkshopError> {
    check_energy(player_energy, inputs.energy_cost)?;
    check_gold(player_gold, inputs.gold_cost)?;
    if let Some(min_level) = inputs.min_skill_level {
        let actual = player_skill.unwrap_or(0);
        check_skill(actual, min_level)?;
    }
    for mat in &inputs.materials {
        let available = material_lookup(&mat.resource_key);
        check_material(&mat.resource_key, available, mat.quantity)?;
    }
    Ok(())
}

/// Compute XP gained for a single workshop action.
///
/// PHP pattern: `rand(1, base_xp)` per energy unit spent, or a fixed
/// amount per action. Callers pass the roll result.
pub fn workshop_xp(base_xp: i32, energy_spent: i32, roll_sum: i32) -> i32 {
    if base_xp <= 0 || energy_spent <= 0 {
        return 0;
    }
    roll_sum.max(0)
}

/// Compute the crafting success chance as a percentage (0–100).
///
/// PHP pattern: base chance modified by skill level.
/// Returns the threshold — if a `rand(1,100)` roll is <= this, the action succeeds.
pub fn success_chance(base_chance: i32, skill_bonus: i32) -> i32 {
    (base_chance + skill_bonus).clamp(1, 100)
}

/// Check whether a roll succeeds against a chance threshold.
pub fn roll_succeeds(threshold: i32, roll: i32) -> bool {
    roll <= threshold
}

#[cfg(test)]
mod tests {
    use super::*;

    // --- check_energy ---

    #[test]
    fn energy_sufficient() {
        assert!(check_energy(10.0, 5.0).is_ok());
    }

    #[test]
    fn energy_exact() {
        assert!(check_energy(5.0, 5.0).is_ok());
    }

    #[test]
    fn energy_insufficient() {
        let err = check_energy(3.0, 5.0).unwrap_err();
        assert_eq!(
            err,
            WorkshopError::InsufficientEnergy {
                required: 5.0,
                available: 3.0
            }
        );
    }

    // --- check_gold ---

    #[test]
    fn gold_sufficient() {
        assert!(check_gold(100, 50).is_ok());
    }

    #[test]
    fn gold_zero_cost() {
        assert!(check_gold(0, 0).is_ok());
    }

    #[test]
    fn gold_insufficient() {
        let err = check_gold(30, 50).unwrap_err();
        assert_eq!(
            err,
            WorkshopError::InsufficientGold {
                required: 50,
                available: 30
            }
        );
    }

    // --- check_material ---

    #[test]
    fn material_sufficient() {
        assert!(check_material("iron", 10, 5).is_ok());
    }

    #[test]
    fn material_insufficient() {
        let err = check_material("iron", 2, 5).unwrap_err();
        assert_eq!(
            err,
            WorkshopError::InsufficientMaterial {
                resource_key: "iron".to_owned(),
                required: 5,
                available: 2
            }
        );
    }

    // --- check_skill ---

    #[test]
    fn skill_sufficient() {
        assert!(check_skill(10, 5).is_ok());
    }

    #[test]
    fn skill_too_low() {
        let err = check_skill(3, 5).unwrap_err();
        assert_eq!(
            err,
            WorkshopError::SkillTooLow {
                required: 5,
                actual: 3
            }
        );
    }

    // --- check_location ---

    #[test]
    fn location_correct() {
        assert!(check_location("Altara", "Altara").is_ok());
    }

    #[test]
    fn location_wrong() {
        let err = check_location("Ardulith", "Altara").unwrap_err();
        assert_eq!(
            err,
            WorkshopError::WrongLocation {
                required: "Altara".to_owned(),
                actual: "Ardulith".to_owned()
            }
        );
    }

    // --- validate_inputs ---

    #[test]
    fn validate_inputs_all_ok() {
        let inputs = WorkshopInputs {
            energy_cost: 5.0,
            gold_cost: 100,
            materials: vec![
                MaterialCost {
                    resource_key: "iron".to_owned(),
                    quantity: 3,
                },
                MaterialCost {
                    resource_key: "coal".to_owned(),
                    quantity: 1,
                },
            ],
            min_skill_level: Some(5),
        };
        let result = validate_inputs(&inputs, 10.0, 200, Some(10), |key| match key {
            "iron" => 5,
            "coal" => 2,
            _ => 0,
        });
        assert!(result.is_ok());
    }

    #[test]
    fn validate_inputs_energy_fail() {
        let inputs = WorkshopInputs {
            energy_cost: 5.0,
            gold_cost: 0,
            materials: vec![],
            min_skill_level: None,
        };
        let result = validate_inputs(&inputs, 3.0, 0, None, |_| 0);
        assert!(matches!(
            result,
            Err(WorkshopError::InsufficientEnergy { .. })
        ));
    }

    #[test]
    fn validate_inputs_gold_fail() {
        let inputs = WorkshopInputs {
            energy_cost: 1.0,
            gold_cost: 100,
            materials: vec![],
            min_skill_level: None,
        };
        let result = validate_inputs(&inputs, 10.0, 50, None, |_| 0);
        assert!(matches!(
            result,
            Err(WorkshopError::InsufficientGold { .. })
        ));
    }

    #[test]
    fn validate_inputs_skill_fail() {
        let inputs = WorkshopInputs {
            energy_cost: 1.0,
            gold_cost: 0,
            materials: vec![],
            min_skill_level: Some(10),
        };
        let result = validate_inputs(&inputs, 10.0, 0, Some(5), |_| 0);
        assert!(matches!(result, Err(WorkshopError::SkillTooLow { .. })));
    }

    #[test]
    fn validate_inputs_material_fail() {
        let inputs = WorkshopInputs {
            energy_cost: 1.0,
            gold_cost: 0,
            materials: vec![MaterialCost {
                resource_key: "iron".to_owned(),
                quantity: 5,
            }],
            min_skill_level: None,
        };
        let result = validate_inputs(&inputs, 10.0, 0, None, |_| 2);
        assert!(matches!(
            result,
            Err(WorkshopError::InsufficientMaterial { .. })
        ));
    }

    // --- success_chance ---

    #[test]
    fn success_chance_normal() {
        assert_eq!(success_chance(50, 10), 60);
    }

    #[test]
    fn success_chance_clamped_high() {
        assert_eq!(success_chance(90, 20), 100);
    }

    #[test]
    fn success_chance_clamped_low() {
        assert_eq!(success_chance(0, 0), 1);
    }

    // --- roll_succeeds ---

    #[test]
    fn roll_success() {
        assert!(roll_succeeds(50, 30));
    }

    #[test]
    fn roll_exact() {
        assert!(roll_succeeds(50, 50));
    }

    #[test]
    fn roll_failure() {
        assert!(!roll_succeeds(50, 51));
    }

    // --- workshop_xp ---

    #[test]
    fn xp_normal() {
        assert_eq!(workshop_xp(10, 3, 15), 15);
    }

    #[test]
    fn xp_zero_base() {
        assert_eq!(workshop_xp(0, 3, 15), 0);
    }

    #[test]
    fn xp_negative_roll_clamped() {
        assert_eq!(workshop_xp(10, 3, -5), 0);
    }
}
