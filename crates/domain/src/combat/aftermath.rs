//! Defeat, hospital healing, and resurrection side effects.
//!
//! Ported from PHP `hospital.php` and `includes/resurect.php`.

use crate::combat::formulas;
use crate::player::skills::PlayerSkill;
use crate::player::stats::PlayerStat;
use crate::player::{Class, Race};

// ---------------------------------------------------------------------------
// Hospital healing
// ---------------------------------------------------------------------------

/// Error returned when healing cannot proceed.
#[derive(Debug, Clone, PartialEq, Eq)]
pub enum HealError {
    /// Player is dead and needs resurrection instead.
    PlayerDead,
    /// Player is already at full health.
    AlreadyFullHp,
    /// Not enough gold to pay the healing fee.
    InsufficientGold { needed: i64, available: i64 },
}

/// Gold cost to heal from current HP to max HP.
///
/// PHP `hospital.php`:
/// - Normal: `(max_hp - hp) * 2`
/// - Tribe hospital pass: `(max_hp - hp) * 1`
pub fn heal_gold_cost(max_hp: i32, current_hp: i32, has_tribe_hospital_pass: bool) -> i64 {
    let deficit = i64::from((max_hp - current_hp).max(0));
    if has_tribe_hospital_pass {
        deficit
    } else {
        deficit * 2
    }
}

/// Validate whether a player can be healed at the hospital.
pub fn validate_heal(
    current_hp: i32,
    max_hp: i32,
    gold: i64,
    has_tribe_hospital_pass: bool,
) -> Result<i64, HealError> {
    if current_hp <= 0 {
        return Err(HealError::PlayerDead);
    }
    if current_hp >= max_hp {
        return Err(HealError::AlreadyFullHp);
    }
    let cost = heal_gold_cost(max_hp, current_hp, has_tribe_hospital_pass);
    if gold < cost {
        return Err(HealError::InsufficientGold {
            needed: cost,
            available: gold,
        });
    }
    Ok(cost)
}

// ---------------------------------------------------------------------------
// Resurrection
// ---------------------------------------------------------------------------

/// Error returned when resurrection cannot proceed.
#[derive(Debug, Clone, PartialEq, Eq)]
pub enum ResurrectError {
    /// Player is alive and does not need resurrection.
    NotDead,
    /// Not enough gold to pay the resurrection fee.
    InsufficientGold { needed: i64, available: i64 },
}

/// Validate whether a player can be resurrected.
pub fn validate_resurrect(
    current_hp: i32,
    gold: i64,
    condition: i32,
) -> Result<i64, ResurrectError> {
    if current_hp > 0 {
        return Err(ResurrectError::NotDead);
    }
    let cost = formulas::resurrection_gold_cost(condition);
    if gold < cost {
        return Err(ResurrectError::InsufficientGold {
            needed: cost,
            available: gold,
        });
    }
    Ok(cost)
}

/// Whether the penalty targets a stat or a skill.
#[derive(Debug, Clone, PartialEq, Eq)]
pub enum PenaltyTarget {
    /// A stat was penalized (by key, e.g. `"condition"`).
    Stat(String),
    /// A skill was penalized (by key, e.g. `"dodge"`).
    Skill(String),
}

/// The result of resurrection XP/level loss calculation.
#[derive(Debug, Clone, PartialEq, Eq)]
pub struct ResurrectionPenalty {
    /// Which stat or skill was affected.
    pub target: PenaltyTarget,
    /// How much XP was lost (for display).
    pub xp_lost: i32,
    /// Whether a full level was lost (rather than just partial XP).
    pub level_lost: bool,
    /// Change in `max_hp` (negative if condition level was lost).
    pub max_hp_change: i32,
}

/// Determine the resurrection penalty given pre-rolled random values.
///
/// `stat_or_skill_roll`: 1–100. Values 1–50 target a stat, 51–100 target a skill.
/// `target_index`: picks which stat/skill in the slice is affected (modulo len).
///
/// PHP `includes/resurect.php`:
/// - 50% chance stat loss, 50% chance skill loss.
/// - If stat at max level → lose one level, xp=0, reported loss = level*2000.
/// - If stat not at max → lose ceil(xp/100) XP.
/// - If stat is condition → also reduce `max_hp` by `resurrection_hp_per_condition`.
/// - If skill at max (level 100) → lose one level, xp=0, reported loss = 10000.
/// - If skill not at max → lose ceil(xp/10) XP.
pub fn resurrection_penalty(
    stats: &[PlayerStat],
    skills: &[PlayerSkill],
    race: &Race,
    class: &Class,
    stat_or_skill_roll: i32,
    target_index: usize,
) -> ResurrectionPenalty {
    if stat_or_skill_roll <= 50 && !stats.is_empty() {
        stat_penalty(stats, race, class, target_index)
    } else if !skills.is_empty() {
        skill_penalty(skills, target_index)
    } else {
        // Fallback: no stats or skills to penalize (shouldn't happen in practice).
        ResurrectionPenalty {
            target: PenaltyTarget::Stat(String::new()),
            xp_lost: 0,
            level_lost: false,
            max_hp_change: 0,
        }
    }
}

fn stat_penalty(
    stats: &[PlayerStat],
    race: &Race,
    class: &Class,
    target_index: usize,
) -> ResurrectionPenalty {
    let idx = target_index % stats.len();
    let stat = &stats[idx];

    if stat.trained == stat.base {
        // At max level: lose one level.
        let xp_lost = stat.trained * 2000;
        let max_hp_change = if stat.stat_key == "condition" {
            -formulas::resurrection_hp_per_condition(race, class)
        } else {
            0
        };
        ResurrectionPenalty {
            target: PenaltyTarget::Stat(stat.stat_key.clone()),
            xp_lost,
            level_lost: true,
            max_hp_change,
        }
    } else {
        // Partial: lose ceil(xp / 100).
        let xp_lost = div_ceil(stat.xp, 100).min(stat.xp);
        ResurrectionPenalty {
            target: PenaltyTarget::Stat(stat.stat_key.clone()),
            xp_lost,
            level_lost: false,
            max_hp_change: 0,
        }
    }
}

fn skill_penalty(skills: &[PlayerSkill], target_index: usize) -> ResurrectionPenalty {
    let idx = target_index % skills.len();
    let skill = &skills[idx];

    if skill.level >= 100 {
        // At max level: lose one level.
        ResurrectionPenalty {
            target: PenaltyTarget::Skill(skill.skill_key.clone()),
            xp_lost: 10_000,
            level_lost: true,
            max_hp_change: 0,
        }
    } else {
        // Partial: lose ceil(xp / 10).
        let xp_lost = div_ceil(skill.xp, 10).min(skill.xp);
        ResurrectionPenalty {
            target: PenaltyTarget::Skill(skill.skill_key.clone()),
            xp_lost,
            level_lost: false,
            max_hp_change: 0,
        }
    }
}

/// Integer ceiling division: `ceil(a / b)` for non-negative `a` and positive `b`.
fn div_ceil(a: i32, b: i32) -> i32 {
    if a <= 0 {
        return 0;
    }
    (a + b - 1) / b
}

/// Apply the resurrection penalty to mutable stat/skill slices.
///
/// Returns the gold cost deducted.
pub fn apply_resurrection_penalty(
    penalty: &ResurrectionPenalty,
    stats: &mut [PlayerStat],
    skills: &mut [PlayerSkill],
) {
    match &penalty.target {
        PenaltyTarget::Stat(key) => {
            if let Some(stat) = stats.iter_mut().find(|s| s.stat_key == *key) {
                if penalty.level_lost {
                    stat.trained -= 1;
                    stat.xp = 0;
                } else {
                    stat.xp = (stat.xp - penalty.xp_lost).max(0);
                }
            }
        }
        PenaltyTarget::Skill(key) => {
            if let Some(skill) = skills.iter_mut().find(|s| s.skill_key == *key) {
                if penalty.level_lost {
                    skill.level -= 1;
                    skill.xp = 0;
                } else {
                    skill.xp = (skill.xp - penalty.xp_lost).max(0);
                }
            }
        }
    }
}

// ---------------------------------------------------------------------------
// Tests
// ---------------------------------------------------------------------------

#[cfg(test)]
mod tests {
    use super::*;

    fn make_stat(key: &str, base: i32, trained: i32, xp: i32) -> PlayerStat {
        PlayerStat {
            stat_key: key.to_owned(),
            label: key.to_owned(),
            base,
            trained,
            modified: trained,
            xp,
        }
    }

    fn make_skill(key: &str, level: i32, xp: i32) -> PlayerSkill {
        PlayerSkill {
            skill_key: key.to_owned(),
            label: key.to_owned(),
            level,
            xp,
        }
    }

    // --- Heal cost ---

    #[test]
    fn heal_cost_normal() {
        assert_eq!(heal_gold_cost(100, 60, false), 80);
    }

    #[test]
    fn heal_cost_tribe_discount() {
        assert_eq!(heal_gold_cost(100, 60, true), 40);
    }

    #[test]
    fn heal_cost_full_hp() {
        assert_eq!(heal_gold_cost(100, 100, false), 0);
    }

    // --- Heal validation ---

    #[test]
    fn validate_heal_dead_player() {
        assert_eq!(
            validate_heal(0, 100, 9999, false),
            Err(HealError::PlayerDead)
        );
    }

    #[test]
    fn validate_heal_full_hp() {
        assert_eq!(
            validate_heal(100, 100, 9999, false),
            Err(HealError::AlreadyFullHp)
        );
    }

    #[test]
    fn validate_heal_insufficient_gold() {
        assert_eq!(
            validate_heal(50, 100, 10, false),
            Err(HealError::InsufficientGold {
                needed: 100,
                available: 10
            })
        );
    }

    #[test]
    fn validate_heal_success() {
        let cost = validate_heal(50, 100, 200, false).unwrap();
        assert_eq!(cost, 100);
    }

    #[test]
    fn validate_heal_tribe_pass() {
        let cost = validate_heal(50, 100, 200, true).unwrap();
        assert_eq!(cost, 50);
    }

    // --- Resurrect validation ---

    #[test]
    fn validate_resurrect_alive() {
        assert_eq!(
            validate_resurrect(1, 9999, 10),
            Err(ResurrectError::NotDead)
        );
    }

    #[test]
    fn validate_resurrect_insufficient_gold() {
        // condition 10 → cost = 500
        assert_eq!(
            validate_resurrect(0, 100, 10),
            Err(ResurrectError::InsufficientGold {
                needed: 500,
                available: 100
            })
        );
    }

    #[test]
    fn validate_resurrect_success() {
        let cost = validate_resurrect(0, 1000, 10).unwrap();
        assert_eq!(cost, 500);
    }

    // --- Resurrection penalty ---

    #[test]
    fn penalty_stat_at_max_level() {
        let stats = vec![make_stat("condition", 20, 20, 5000)];
        let skills = vec![make_skill("dodge", 50, 1000)];

        let penalty = resurrection_penalty(&stats, &skills, &Race::Human, &Class::Warrior, 25, 0);

        assert_eq!(penalty.target, PenaltyTarget::Stat("condition".to_owned()));
        assert!(penalty.level_lost);
        assert_eq!(penalty.xp_lost, 40_000); // 20 * 2000
        // Human(4) + Warrior(5) = 9
        assert_eq!(penalty.max_hp_change, -9);
    }

    #[test]
    fn penalty_stat_partial_xp() {
        let stats = vec![make_stat("strength", 20, 15, 500)];
        let skills = vec![make_skill("dodge", 50, 1000)];

        let penalty = resurrection_penalty(&stats, &skills, &Race::Human, &Class::Warrior, 30, 0);

        assert_eq!(penalty.target, PenaltyTarget::Stat("strength".to_owned()));
        assert!(!penalty.level_lost);
        assert_eq!(penalty.xp_lost, 5); // ceil(500/100) = 5
        assert_eq!(penalty.max_hp_change, 0);
    }

    #[test]
    fn penalty_stat_partial_xp_zero() {
        let stats = vec![make_stat("agility", 20, 10, 0)];
        let skills = vec![make_skill("dodge", 50, 100)];

        let penalty = resurrection_penalty(&stats, &skills, &Race::Elf, &Class::Mage, 10, 0);

        assert!(!penalty.level_lost);
        assert_eq!(penalty.xp_lost, 0);
    }

    #[test]
    fn penalty_skill_at_max_level() {
        let stats = vec![make_stat("condition", 20, 15, 1000)];
        let skills = vec![make_skill("magic", 100, 5000)];

        let penalty = resurrection_penalty(&stats, &skills, &Race::Human, &Class::Mage, 75, 0);

        assert_eq!(penalty.target, PenaltyTarget::Skill("magic".to_owned()));
        assert!(penalty.level_lost);
        assert_eq!(penalty.xp_lost, 10_000);
    }

    #[test]
    fn penalty_skill_partial_xp() {
        let stats = vec![make_stat("condition", 20, 15, 1000)];
        let skills = vec![make_skill("attack", 50, 300)];

        let penalty = resurrection_penalty(&stats, &skills, &Race::Human, &Class::Warrior, 80, 0);

        assert_eq!(penalty.target, PenaltyTarget::Skill("attack".to_owned()));
        assert!(!penalty.level_lost);
        assert_eq!(penalty.xp_lost, 30); // ceil(300/10) = 30
    }

    #[test]
    fn penalty_selects_by_index() {
        let stats = vec![
            make_stat("strength", 20, 10, 200),
            make_stat("agility", 20, 12, 400),
        ];
        let skills = vec![make_skill("dodge", 50, 100)];

        let p1 = resurrection_penalty(&stats, &skills, &Race::Human, &Class::Warrior, 25, 0);
        assert_eq!(p1.target, PenaltyTarget::Stat("strength".to_owned()));

        let p2 = resurrection_penalty(&stats, &skills, &Race::Human, &Class::Warrior, 25, 1);
        assert_eq!(p2.target, PenaltyTarget::Stat("agility".to_owned()));

        // Index wraps around.
        let p3 = resurrection_penalty(&stats, &skills, &Race::Human, &Class::Warrior, 25, 2);
        assert_eq!(p3.target, PenaltyTarget::Stat("strength".to_owned()));
    }

    // --- Apply penalty ---

    #[test]
    fn apply_stat_level_loss() {
        let penalty = ResurrectionPenalty {
            target: PenaltyTarget::Stat("condition".to_owned()),
            xp_lost: 40_000,
            level_lost: true,
            max_hp_change: -9,
        };
        let mut stats = vec![make_stat("condition", 20, 20, 5000)];
        let mut skills = vec![];

        apply_resurrection_penalty(&penalty, &mut stats, &mut skills);

        assert_eq!(stats[0].trained, 19);
        assert_eq!(stats[0].xp, 0);
    }

    #[test]
    fn apply_stat_partial_xp_loss() {
        let penalty = ResurrectionPenalty {
            target: PenaltyTarget::Stat("strength".to_owned()),
            xp_lost: 5,
            level_lost: false,
            max_hp_change: 0,
        };
        let mut stats = vec![make_stat("strength", 20, 15, 500)];
        let mut skills = vec![];

        apply_resurrection_penalty(&penalty, &mut stats, &mut skills);

        assert_eq!(stats[0].trained, 15); // level unchanged
        assert_eq!(stats[0].xp, 495);
    }

    #[test]
    fn apply_skill_level_loss() {
        let penalty = ResurrectionPenalty {
            target: PenaltyTarget::Skill("magic".to_owned()),
            xp_lost: 10_000,
            level_lost: true,
            max_hp_change: 0,
        };
        let mut stats = vec![];
        let mut skills = vec![make_skill("magic", 100, 5000)];

        apply_resurrection_penalty(&penalty, &mut stats, &mut skills);

        assert_eq!(skills[0].level, 99);
        assert_eq!(skills[0].xp, 0);
    }

    #[test]
    fn apply_skill_partial_xp_loss() {
        let penalty = ResurrectionPenalty {
            target: PenaltyTarget::Skill("attack".to_owned()),
            xp_lost: 30,
            level_lost: false,
            max_hp_change: 0,
        };
        let mut stats = vec![];
        let mut skills = vec![make_skill("attack", 50, 300)];

        apply_resurrection_penalty(&penalty, &mut stats, &mut skills);

        assert_eq!(skills[0].level, 50); // level unchanged
        assert_eq!(skills[0].xp, 270);
    }
}
