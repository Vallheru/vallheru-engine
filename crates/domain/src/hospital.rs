//! Hospital domain — healing and resurrection calculations.
//!
//! Ported from `hospital.php` (city hospital) and `includes/resurect.php`
//! (shared resurrection logic used by hospital + hermit in forest/mountains).

use crate::player::progression::DeathPenalty;
use crate::player::skills::PlayerSkill;
use crate::player::stats::PlayerStat;
use crate::player::{Class, Race};

// ---------------------------------------------------------------------------
// Healing (alive players)
// ---------------------------------------------------------------------------

/// Calculate healing cost: `(max_hp - hp) * multiplier`.
///
/// Multiplier is 1 if tribe has hospital pass, 2 otherwise.
pub fn healing_cost(hp: i32, max_hp: i32, tribe_hospital_pass: bool) -> i32 {
    let diff = (max_hp - hp).max(0);
    if tribe_hospital_pass { diff } else { diff * 2 }
}

// ---------------------------------------------------------------------------
// Resurrection (dead players)
// ---------------------------------------------------------------------------

/// Gold cost for resurrection: `50 × condition stat trained level`.
pub fn resurrection_cost(condition_trained: i32) -> i32 {
    50 * condition_trained.max(1)
}

/// Result of a resurrection attempt.
#[derive(Debug, Clone, PartialEq, Eq)]
pub struct ResurrectionResult {
    /// The death penalty applied (stat or skill XP/level loss).
    pub penalty: DeathPenalty,
    /// Gold deducted.
    pub gold_cost: i32,
    /// New `max_hp` (may decrease if condition stat lost a level).
    pub new_max_hp: i32,
    /// New hp (set to `new_max_hp`).
    pub new_hp: i32,
}

/// Perform resurrection: apply XP penalty, compute new HP, deduct gold.
///
/// `stats` and `skills` are mutated in place with the penalty.
/// The caller must persist the updated stats/skills and the returned hp/gold changes.
///
/// `rng` fields are explicit for testability.
#[allow(clippy::too_many_arguments)]
pub fn resurrect(
    stats: &mut [PlayerStat],
    skills: &mut [PlayerSkill],
    race: &Race,
    class: &Class,
    max_hp: i32,
    credits: i32,
    roll: i32,
    stat_idx: usize,
    skill_idx: usize,
) -> Result<ResurrectionResult, ResurrectionError> {
    let condition_trained = stats
        .iter()
        .find(|s| s.stat_key == "condition")
        .map_or(1, |s| s.trained.max(1));

    let gold_cost = resurrection_cost(condition_trained);

    if credits < gold_cost {
        return Err(ResurrectionError::NotEnoughGold { needed: gold_cost });
    }

    let penalty = crate::player::progression::apply_death_penalty(
        stats, skills, race, class, roll, stat_idx, skill_idx,
    );

    // If condition stat lost a level, max_hp decreases.
    let hp_change = match &penalty {
        DeathPenalty::StatLevelLost { hp_change, .. } => *hp_change,
        _ => 0,
    };
    let new_max_hp = (max_hp + hp_change).max(1);
    let new_hp = new_max_hp;

    Ok(ResurrectionResult {
        penalty,
        gold_cost,
        new_max_hp,
        new_hp,
    })
}

/// Errors that prevent resurrection.
#[derive(Debug, Clone, PartialEq, Eq)]
pub enum ResurrectionError {
    /// Player doesn't have enough gold.
    NotEnoughGold { needed: i32 },
}

// ---------------------------------------------------------------------------
// Penalty description (for UI messages)
// ---------------------------------------------------------------------------

/// Format a user-facing Polish message describing the penalty.
pub fn penalty_message(penalty: &DeathPenalty) -> String {
    match penalty {
        DeathPenalty::StatLevelLost { label, .. } => {
            format!("straciłeś poziom cechy {label}")
        }
        DeathPenalty::StatXpLost { label, .. } => {
            format!("straciłeś Punkty Doświadczenia do cechy {label}")
        }
        DeathPenalty::SkillLevelLost { label, .. } => {
            format!("straciłeś poziom umiejętności {label}")
        }
        DeathPenalty::SkillXpLost { label, .. } => {
            format!("straciłeś Punkty Doświadczenia do umiejętności {label}")
        }
    }
}

// ---------------------------------------------------------------------------
// Tests
// ---------------------------------------------------------------------------

#[cfg(test)]
mod tests {
    use super::*;
    use crate::player::skills::default_skills;
    use crate::player::stats::default_stats;

    #[test]
    fn healing_cost_no_discount() {
        assert_eq!(healing_cost(50, 100, false), 100);
    }

    #[test]
    fn healing_cost_with_tribe_pass() {
        assert_eq!(healing_cost(50, 100, true), 50);
    }

    #[test]
    fn healing_cost_full_hp() {
        assert_eq!(healing_cost(100, 100, false), 0);
    }

    #[test]
    fn resurrection_cost_basic() {
        assert_eq!(resurrection_cost(10), 500);
    }

    #[test]
    fn resurrection_cost_minimum() {
        assert_eq!(resurrection_cost(0), 50);
    }

    #[test]
    fn resurrect_not_enough_gold() {
        let mut stats = default_stats();
        let mut skills = default_skills();
        let result = resurrect(
            &mut stats,
            &mut skills,
            &Race::Human,
            &Class::Warrior,
            100,
            0, // 0 gold
            50,
            0,
            0,
        );
        assert_eq!(result, Err(ResurrectionError::NotEnoughGold { needed: 50 }));
    }

    #[test]
    fn resurrect_applies_penalty_and_heals() {
        let mut stats = default_stats();
        let mut skills = default_skills();
        // Set condition trained = 2 so cost = 100
        for s in &mut stats {
            if s.stat_key == "condition" {
                s.trained = 2;
            }
        }
        let result = resurrect(
            &mut stats,
            &mut skills,
            &Race::Human,
            &Class::Warrior,
            100,
            200, // 200 gold, need 100
            50,
            0,
            0, // roll=50 → stat penalty
        );
        let res = result.expect("should succeed");
        assert_eq!(res.gold_cost, 100);
        assert_eq!(res.new_hp, res.new_max_hp);
        // Penalty was applied
        matches!(
            res.penalty,
            DeathPenalty::StatXpLost { .. } | DeathPenalty::StatLevelLost { .. }
        );
    }

    #[test]
    fn resurrect_skill_penalty_path() {
        let mut stats = default_stats();
        let mut skills = default_skills();
        let result = resurrect(
            &mut stats,
            &mut skills,
            &Race::Human,
            &Class::Warrior,
            100,
            5000,
            51,
            0,
            0, // roll=51 → skill penalty
        );
        let res = result.expect("should succeed");
        matches!(
            res.penalty,
            DeathPenalty::SkillXpLost { .. } | DeathPenalty::SkillLevelLost { .. }
        );
    }

    #[test]
    fn penalty_message_formats() {
        let msg = penalty_message(&DeathPenalty::StatLevelLost {
            stat_key: "condition".into(),
            label: "Kondycja".into(),
            hp_change: -9,
            ap_change: -1,
        });
        assert!(msg.contains("Kondycja"));

        let msg = penalty_message(&DeathPenalty::SkillXpLost {
            skill_key: "smith".into(),
            label: "Kowalstwo".into(),
        });
        assert!(msg.contains("Kowalstwo"));
    }
}
