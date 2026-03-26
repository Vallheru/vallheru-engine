//! Maze and labyrinth exploration state models.
//!
//! Two distinct labyrinth systems exist in the game:
//!
//! ## `grid.php` — Altara labyrinth
//!
//! A replayable dungeon exploration accessed from Altara. Players spend
//! energy (0.3 per exploration) and can:
//! - Find gold (random 1–100)
//! - Find mithril (random 1–3)
//! - Lose energy (random chance)
//! - Discover quest entrances (5% chance per exploration, then 20% to trigger)
//! - Find map fragments (1 in 50, if maps are available)
//!
//! Quest encounters in the labyrinth use the `questaction` + `quests` tables
//! and follow the same branching system as `quest.rs`.
//!
//! ### Session state
//!
//! `grid.php` uses POST-based multi-exploration (batch N times) with no
//! persistent session beyond `questaction` rows. The N explorations are
//! processed server-side in a loop.
//!
//! ## `maze.php` — Ardulith labyrinth
//!
//! A more complex dungeon exploration in Ardulith. Players explore
//! square-by-square on an implicit grid, encountering:
//! - Monsters (turn-based combat via `turnfight`)
//! - Gold and items
//! - Random events
//!
//! ### Session state
//!
//! `maze.php` has extensive session state for mid-combat and exploration
//! continuity, but all critical state ultimately persists to the player row
//! (hp, energy, gold, equipment) and `questaction` for quest triggers.

/// Reward accumulated from labyrinth exploration (grid.php batch runs).
#[derive(Debug, Clone, PartialEq, Eq, Default)]
pub struct LabyrinthExploreResult {
    /// Gold found.
    pub gold: i32,
    /// Mithril found.
    pub mithril: i32,
    /// Energy lost from unlucky encounters.
    pub energy_lost: i32,
    /// Number of exploration steps completed.
    pub steps_completed: i32,
    /// Map fragments found.
    pub maps_found: i32,
    /// If a quest encounter was triggered, its quest ID.
    pub quest_triggered: Option<i32>,
}

/// The possible hex-crawl outcomes for a single labyrinth step.
///
/// From `grid.php` switch on `rand(1, 11)`:
/// - 3 → gold
/// - 6 → mithril
/// - 7 → lose energy
/// - 10 → possible quest encounter (further 1/5 chance)
/// - all other → nothing
#[derive(Debug, Clone, Copy, PartialEq, Eq)]
pub enum LabyrinthStepOutcome {
    Nothing,
    Gold,
    Mithril,
    EnergyLoss,
    QuestChance,
}

impl LabyrinthStepOutcome {
    /// Map a roll (1–11) to the outcome.
    pub fn from_roll(roll: i32) -> Self {
        match roll {
            3 => Self::Gold,
            6 => Self::Mithril,
            7 => Self::EnergyLoss,
            10 => Self::QuestChance,
            _ => Self::Nothing,
        }
    }
}

/// Energy cost per labyrinth exploration step in `grid.php`.
pub const LABYRINTH_ENERGY_COST: f64 = 0.3;

/// Validate that a player can explore the labyrinth.
pub fn can_explore_labyrinth(
    location: &str,
    hp: i32,
    energy: f64,
    requested_steps: i32,
    has_active_quest: bool,
) -> Result<i32, LabyrinthError> {
    if location != "Altara" && location != "Podróż" {
        return Err(LabyrinthError::WrongLocation);
    }
    if hp <= 0 {
        return Err(LabyrinthError::Dead);
    }
    if requested_steps <= 0 {
        return Err(LabyrinthError::InvalidAmount);
    }
    if has_active_quest {
        return Err(LabyrinthError::AlreadyOnQuest);
    }
    #[allow(clippy::cast_possible_truncation)]
    let affordable = (energy / LABYRINTH_ENERGY_COST).floor() as i32;
    if affordable <= 0 {
        return Err(LabyrinthError::NotEnoughEnergy);
    }
    Ok(requested_steps.min(affordable))
}

/// Errors preventing labyrinth exploration.
#[derive(Debug, Clone, PartialEq, Eq)]
pub enum LabyrinthError {
    WrongLocation,
    Dead,
    NotEnoughEnergy,
    InvalidAmount,
    AlreadyOnQuest,
}

/// Validate that a player can access the Ardulith maze.
pub fn can_enter_maze(location: &str, hp: i32) -> Result<(), MazeError> {
    if location != "Ardulith" {
        return Err(MazeError::WrongLocation);
    }
    if hp <= 0 {
        return Err(MazeError::Dead);
    }
    Ok(())
}

/// Errors preventing maze access.
#[derive(Debug, Clone, PartialEq, Eq)]
pub enum MazeError {
    WrongLocation,
    Dead,
}

// ---------------------------------------------------------------------------
// Tests
// ---------------------------------------------------------------------------

#[cfg(test)]
mod tests {
    use super::*;

    #[test]
    fn labyrinth_step_outcomes() {
        assert_eq!(
            LabyrinthStepOutcome::from_roll(1),
            LabyrinthStepOutcome::Nothing
        );
        assert_eq!(
            LabyrinthStepOutcome::from_roll(3),
            LabyrinthStepOutcome::Gold
        );
        assert_eq!(
            LabyrinthStepOutcome::from_roll(6),
            LabyrinthStepOutcome::Mithril
        );
        assert_eq!(
            LabyrinthStepOutcome::from_roll(7),
            LabyrinthStepOutcome::EnergyLoss
        );
        assert_eq!(
            LabyrinthStepOutcome::from_roll(10),
            LabyrinthStepOutcome::QuestChance
        );
        assert_eq!(
            LabyrinthStepOutcome::from_roll(11),
            LabyrinthStepOutcome::Nothing
        );
    }

    #[test]
    fn can_explore_labyrinth_ok() {
        let steps = can_explore_labyrinth("Altara", 100, 3.0, 5, false).unwrap();
        // 3.0 / 0.3 = 10 affordable, requested 5
        assert_eq!(steps, 5);
    }

    #[test]
    fn can_explore_labyrinth_clamps_to_affordable() {
        let steps = can_explore_labyrinth("Altara", 100, 1.2, 10, false).unwrap();
        // 1.2 / 0.3 = 4 affordable
        assert_eq!(steps, 4);
    }

    #[test]
    fn can_explore_labyrinth_travelling_ok() {
        // "Podróż" is also allowed (player may already be on a quest path)
        assert!(can_explore_labyrinth("Podróż", 100, 3.0, 5, false).is_ok());
    }

    #[test]
    fn can_explore_labyrinth_wrong_location() {
        assert_eq!(
            can_explore_labyrinth("Ardulith", 100, 3.0, 5, false),
            Err(LabyrinthError::WrongLocation)
        );
    }

    #[test]
    fn can_explore_labyrinth_dead() {
        assert_eq!(
            can_explore_labyrinth("Altara", 0, 3.0, 5, false),
            Err(LabyrinthError::Dead)
        );
    }

    #[test]
    fn can_explore_labyrinth_no_energy() {
        assert_eq!(
            can_explore_labyrinth("Altara", 100, 0.1, 5, false),
            Err(LabyrinthError::NotEnoughEnergy)
        );
    }

    #[test]
    fn can_explore_labyrinth_invalid_amount() {
        assert_eq!(
            can_explore_labyrinth("Altara", 100, 3.0, 0, false),
            Err(LabyrinthError::InvalidAmount)
        );
    }

    #[test]
    fn can_explore_labyrinth_on_quest() {
        assert_eq!(
            can_explore_labyrinth("Altara", 100, 3.0, 5, true),
            Err(LabyrinthError::AlreadyOnQuest)
        );
    }

    #[test]
    fn can_enter_maze_ok() {
        assert!(can_enter_maze("Ardulith", 100).is_ok());
    }

    #[test]
    fn can_enter_maze_wrong_location() {
        assert_eq!(can_enter_maze("Altara", 100), Err(MazeError::WrongLocation));
    }

    #[test]
    fn can_enter_maze_dead() {
        assert_eq!(can_enter_maze("Ardulith", 0), Err(MazeError::Dead));
    }
}
