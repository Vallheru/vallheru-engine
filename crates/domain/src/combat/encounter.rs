//! Monster encounter selection and reward calculation.
//!
//! Ported from PHP `includes/monsters.php` and reward logic embedded in
//! `explore.php`, `farm.php`, and `hunters.php`.
//!
//! All functions are pure — RNG values are passed in so callers can
//! control them in tests.

use crate::combat::formulas::{MonsterResistance, ResistanceStrength};
use crate::item::Element;

// ---------------------------------------------------------------------------
// Monster catalog row
// ---------------------------------------------------------------------------

/// A monster as read from the `monsters` catalog table.
#[derive(Debug, Clone)]
pub struct Monster {
    pub id: i32,
    pub name: String,
    pub level: i32,
    pub hp: i32,
    pub strength: f64,
    pub agility: f64,
    pub speed: f64,
    pub endurance: f64,
    pub location: String,
    pub loot: Vec<LootEntry>,
    pub resistance: MonsterResistance,
    pub dmgtype: Element,
}

/// A single loot entry on a monster: name + cumulative chance out of 100.
#[derive(Debug, Clone, PartialEq)]
pub struct LootEntry {
    pub name: String,
    /// Cumulative chance threshold (0–100).
    pub chance: i32,
}

impl Monster {
    /// The monster's total "power" stat sum used as base for encounter tier
    /// matching and reward calculations.
    ///
    /// PHP: `strength + agility + speed + endurance + level + hp`
    pub fn stat_sum(&self) -> f64 {
        self.strength
            + self.agility
            + self.speed
            + self.endurance
            + f64::from(self.level)
            + f64::from(self.hp)
    }
}

// ---------------------------------------------------------------------------
// Loot parsing
// ---------------------------------------------------------------------------

/// Parse semicolon-separated loot fields from the database into entries.
///
/// PHP stores `lootnames` as `"Kieł;Skóra;Pazur;Czaszka"` and
/// `lootchances` as `"45;77;95;100"`.  Empty strings produce an empty vec.
pub fn parse_loot(names_raw: &str, chances_raw: &str) -> Vec<LootEntry> {
    let names: Vec<&str> = names_raw.split(';').collect();
    let chances: Vec<&str> = chances_raw.split(';').collect();

    if names.len() != chances.len() || names_raw.is_empty() || chances_raw.is_empty() {
        return Vec::new();
    }

    names
        .into_iter()
        .zip(chances)
        .filter_map(|(n, c)| {
            let chance = c.trim().parse::<i32>().ok()?;
            if n.trim().is_empty() {
                return None;
            }
            Some(LootEntry {
                name: n.trim().to_owned(),
                chance,
            })
        })
        .collect()
}

/// Pick a loot item from the loot table given a roll in 1..=100.
///
/// Returns `None` when the table is empty.
pub fn pick_loot(loot: &[LootEntry], roll_1_to_100: i32) -> Option<&LootEntry> {
    // Entries are stored with cumulative chance thresholds.
    // E.g. [45, 77, 95, 100] means:
    //   roll 1–45  → entry 0
    //   roll 46–77 → entry 1
    //   roll 78–95 → entry 2
    //   roll 96–100 → entry 3
    loot.iter().find(|e| roll_1_to_100 <= e.chance)
}

// ---------------------------------------------------------------------------
// Player power level (encounter matching)
// ---------------------------------------------------------------------------

/// Inputs needed to compute the player's encounter-matching power level.
///
/// This flattens the sprawling PHP `$player` accesses into an explicit struct.
pub struct PlayerCombatProfile {
    pub hp: i32,
    pub condition_mod: i32,
    pub speed_mod: i32,
    pub agility_mod: i32,
    pub strength_mod: i32,
    pub wisdom_mod: i32,
    pub intelligence_mod: i32,
    pub dodge_skill: i32,
    pub attack_skill: i32,
    pub shoot_skill: i32,
    pub magic_skill: i32,
    pub has_weapon: bool,
    pub has_second_weapon: bool,
    pub has_bow: bool,
}

/// Calculate the player "power level" used for encounter tier matching.
///
/// PHP `encounter()` / `battle()` in `explore.php`:
/// ```text
/// plevel = condition + speed + agility + dodge + hp
/// if (weapon || second_weapon || bow):
///     plevel += strength
///     if (weapon || second_weapon): plevel += attack
///     else: plevel += shoot
/// else:
///     plevel += wisdom + intelligence + magic
/// ```
pub fn player_power_level(p: &PlayerCombatProfile) -> f64 {
    let mut level = f64::from(p.condition_mod)
        + f64::from(p.speed_mod)
        + f64::from(p.agility_mod)
        + f64::from(p.dodge_skill)
        + f64::from(p.hp);

    if p.has_weapon || p.has_second_weapon || p.has_bow {
        level += f64::from(p.strength_mod);
        if p.has_weapon || p.has_second_weapon {
            level += f64::from(p.attack_skill);
        } else {
            level += f64::from(p.shoot_skill);
        }
    } else {
        level += f64::from(p.wisdom_mod) + f64::from(p.intelligence_mod) + f64::from(p.magic_skill);
    }

    level
}

// ---------------------------------------------------------------------------
// Encounter difficulty tier
// ---------------------------------------------------------------------------

/// Difficulty tier that controls the ratio range for monster selection.
#[derive(Debug, Clone, Copy, PartialEq)]
pub struct DifficultyTier {
    pub min_ratio: f64,
    pub max_ratio: f64,
}

/// Select the difficulty tier from a d100 roll.
///
/// PHP `encounter()`:
/// ```text
/// roll < 25        → (0.0, 0.5)   easy
/// 25 <= roll < 90  → (0.5, 1.2)   normal
/// roll >= 90       → (1.2, 2.0)   hard
/// ```
pub fn difficulty_tier(roll_1_to_100: i32) -> DifficultyTier {
    if roll_1_to_100 < 25 {
        DifficultyTier {
            min_ratio: 0.0,
            max_ratio: 0.5,
        }
    } else if roll_1_to_100 < 90 {
        DifficultyTier {
            min_ratio: 0.5,
            max_ratio: 1.2,
        }
    } else {
        DifficultyTier {
            min_ratio: 1.2,
            max_ratio: 2.0,
        }
    }
}

/// The fallback tier used when no monster matched the initial tier.
pub const FALLBACK_TIER: DifficultyTier = DifficultyTier {
    min_ratio: 0.0,
    max_ratio: 2.0,
};

/// Check whether a monster falls within the given difficulty tier for a player.
///
/// `player_power` is the result of [`player_power_level`].
pub fn monster_matches_tier(monster: &Monster, player_power: f64, tier: &DifficultyTier) -> bool {
    if player_power <= 0.0 {
        return false;
    }
    let ratio = monster.stat_sum() / player_power;
    ratio >= tier.min_ratio && ratio <= tier.max_ratio
}

/// Map a DB location string to the encounter region.
///
/// PHP: anything not `'Las'` maps to `'Altara'`.
pub fn encounter_region(player_location: &str) -> &'static str {
    if player_location == "Las" {
        "Ardulith"
    } else {
        "Altara"
    }
}

// ---------------------------------------------------------------------------
// Reward calculation
// ---------------------------------------------------------------------------

/// XP and gold reward from a monster kill.
///
/// PHP `explore.php` → `battle()`:
/// ```text
/// span = (monster_stat_sum / player_power), capped at 2.0
/// expgain = ceil(monster_stat_sum * span)
/// goldgain = ceil(monster_stat_sum * span)
/// ```
#[derive(Debug, Clone, Copy, PartialEq)]
pub struct EncounterReward {
    pub xp: i64,
    pub gold: i64,
}

#[allow(clippy::cast_possible_truncation)]
pub fn encounter_reward(monster: &Monster, player_power: f64) -> EncounterReward {
    let stat_sum = monster.stat_sum();
    let span = if player_power > 0.0 {
        (stat_sum / player_power).min(2.0)
    } else {
        2.0
    };
    let xp = (stat_sum * span).ceil() as i64;
    let gold = xp; // same formula in PHP for explore encounters
    EncounterReward { xp, gold }
}

/// Hunter guild quest rewards use a different formula with exp1/exp2 ranges.
///
/// PHP `hunters.php`:
/// ```text
/// span = (monster.level / player.level), capped at 2.0
/// exp_base = rand(exp1, exp2) * span  (first kill)
/// for k=2..=count: exp += ceil(exp_base / 5 * (sqrt(k) + 4.5))
/// gold = ceil(rand(credits1, credits2) * count * span)
/// ```
#[allow(clippy::cast_possible_truncation, clippy::cast_precision_loss)]
pub fn hunter_quest_reward(
    monster_level: i32,
    player_level: i32,
    exp_roll: i32,
    gold_roll: i32,
    kill_count: i32,
) -> EncounterReward {
    let span = if player_level > 0 {
        (f64::from(monster_level) / f64::from(player_level)).min(2.0)
    } else {
        2.0
    };

    let exp_base = (f64::from(exp_roll) * span).ceil() as i64;
    let mut total_xp = exp_base;
    for k in 2..=kill_count {
        let bonus = (exp_base as f64 / 5.0 * (f64::from(k).sqrt() + 4.5)).ceil() as i64;
        total_xp += bonus;
    }

    let gold = (f64::from(gold_roll) * f64::from(kill_count) * span).ceil() as i64;

    EncounterReward { xp: total_xp, gold }
}

// ---------------------------------------------------------------------------
// Random monster generation
// ---------------------------------------------------------------------------

/// Inputs for generating a random (synthetic) monster matching a player.
///
/// PHP `randommonster()` in `includes/monsters.php`.
pub struct RandomMonsterInputs {
    pub name: String,
    pub player_hp: i32,
    pub condition_mod: i32,
    pub speed_mod: i32,
    pub strength_mod: i32,
    pub intelligence_mod: i32,
    pub agility_mod: i32,
    pub wisdom_mod: i32,
    pub dodge_skill: i32,
    pub attack_skill: i32,
    pub shoot_skill: i32,
    pub magic_skill: i32,
    pub has_weapon: bool,
    pub has_bow: bool,
    pub weapon_power: f64,
    pub bow_power: f64,
    pub arrow_power: f64,
}

/// Per-stat random variation: a bonus percentage (0–15) and a coin flip.
/// PHP: `bonus = rand(0,15)/100 * stat; if rand(1,100)>50 bonus *= -1`
pub struct StatVariation {
    pub pct_0_to_15: i32,
    pub positive: bool,
}

/// Random element/resistance roll results.
pub struct ElementRoll {
    /// 1..=100 — determines whether there is an element assignment.
    pub chance_roll: i32,
    /// 0..=3 — selects which element (water/fire/wind/earth).
    pub element_index: i32,
}

/// Generate a synthetic monster based on player stats.
///
/// Returns `(strength, agility, hp, endurance, speed, level, element, resistance)`.
///
/// Uses explicit RNG inputs so the function is deterministic and testable.
#[allow(clippy::cast_possible_truncation, clippy::cast_sign_loss)]
pub fn generate_random_monster(
    inputs: &RandomMonsterInputs,
    variations: &[StatVariation; 5],
    element_roll: &ElementRoll,
) -> Monster {
    // Base stats: [str_or_int, agi_or_wis+dodge, hp, condition, speed]
    let base_strength = if inputs.strength_mod > inputs.intelligence_mod {
        f64::from(inputs.strength_mod)
    } else {
        f64::from(inputs.intelligence_mod)
    };

    let base_agility = if inputs.agility_mod > inputs.wisdom_mod {
        f64::from(inputs.agility_mod)
    } else {
        f64::from(inputs.wisdom_mod)
    };
    let base_agility = base_agility + f64::from(inputs.dodge_skill);

    let mut base_str = base_strength;
    if inputs.has_weapon {
        base_str += inputs.weapon_power + f64::from(inputs.attack_skill);
    } else if inputs.has_bow {
        base_str += inputs.bow_power + inputs.arrow_power + f64::from(inputs.shoot_skill);
    } else {
        base_str += f64::from(inputs.magic_skill);
    }

    let mut stats = [
        base_str,
        base_agility,
        f64::from(inputs.player_hp),
        f64::from(inputs.condition_mod),
        f64::from(inputs.speed_mod),
    ];

    // Apply random variation to each stat
    for (stat, var) in stats.iter_mut().zip(variations.iter()) {
        let bonus = (f64::from(var.pct_0_to_15) / 100.0) * *stat;
        if var.positive {
            *stat += bonus;
        } else {
            *stat -= bonus;
        }
    }

    // Element / resistance
    let elements = [Element::Water, Element::Fire, Element::Wind, Element::Earth];
    let idx = (element_roll.element_index.clamp(0, 3)) as usize;

    let (dmgtype, resistance) = if element_roll.chance_roll < 75 {
        (Element::None, MonsterResistance::none())
    } else {
        let el = elements[idx];
        let strength = if element_roll.chance_roll < 90 {
            "weak"
        } else if element_roll.chance_roll < 97 {
            "medium"
        } else {
            "strong"
        };
        (
            el,
            MonsterResistance {
                element: el,
                strength: ResistanceStrength::parse(strength),
            },
        )
    };

    let level = (stats[0] + stats[1] + stats[4] + stats[3] + stats[2]) as i32;

    Monster {
        id: 0,
        name: inputs.name.clone(),
        level,
        hp: stats[2] as i32,
        strength: stats[0],
        agility: stats[1],
        speed: stats[4],
        endurance: stats[3],
        loot: Vec::new(),
        resistance,
        dmgtype,
        location: String::new(),
    }
}

// ---------------------------------------------------------------------------
// Escape chance (explore context)
// ---------------------------------------------------------------------------

/// Escape check in exploration context.
///
/// PHP `explore.php`:
/// ```text
/// chance = (player.speed_mod + player.perception + rand(1,100))
///        - (monster.speed + rand(1,100))
/// ```
/// Positive result → escape succeeds.
#[allow(clippy::cast_possible_truncation)]
pub fn explore_escape_check(
    player_speed_mod: i32,
    player_perception: i32,
    player_roll_1_to_100: i32,
    monster_speed: f64,
    monster_roll_1_to_100: i32,
) -> i32 {
    (player_speed_mod + player_perception + player_roll_1_to_100)
        - (monster_speed as i32 + monster_roll_1_to_100)
}

/// XP for a successful escape.
///
/// PHP: `ceil((speed + endurance + agility + strength) / 100)`
#[allow(clippy::cast_possible_truncation)]
pub fn escape_xp(monster: &Monster) -> i64 {
    ((monster.speed + monster.endurance + monster.agility + monster.strength) / 100.0).ceil() as i64
}

// ---------------------------------------------------------------------------
// Tests
// ---------------------------------------------------------------------------

#[cfg(test)]
#[allow(clippy::float_cmp)]
mod tests {
    use super::*;

    fn test_monster(id: i32, level: i32, hp: i32, str_val: f64, location: &str) -> Monster {
        Monster {
            id,
            name: format!("Monster {id}"),
            level,
            hp,
            strength: str_val,
            agility: str_val,
            speed: str_val,
            endurance: str_val,
            location: location.to_owned(),
            loot: Vec::new(),
            resistance: MonsterResistance::none(),
            dmgtype: Element::None,
        }
    }

    // --- Loot parsing ---

    #[test]
    fn parse_loot_normal() {
        let entries = parse_loot("Kieł;Skóra;Pazur;Czaszka", "45;77;95;100");
        assert_eq!(entries.len(), 4);
        assert_eq!(entries[0].name, "Kieł");
        assert_eq!(entries[0].chance, 45);
        assert_eq!(entries[3].name, "Czaszka");
        assert_eq!(entries[3].chance, 100);
    }

    #[test]
    fn parse_loot_empty() {
        assert!(parse_loot("", "").is_empty());
        assert!(parse_loot("a;b", "1").is_empty());
    }

    #[test]
    fn pick_loot_selects_correct_entry() {
        let entries = parse_loot("A;B;C;D", "25;50;75;100");
        assert_eq!(pick_loot(&entries, 1).unwrap().name, "A");
        assert_eq!(pick_loot(&entries, 25).unwrap().name, "A");
        assert_eq!(pick_loot(&entries, 26).unwrap().name, "B");
        assert_eq!(pick_loot(&entries, 50).unwrap().name, "B");
        assert_eq!(pick_loot(&entries, 75).unwrap().name, "C");
        assert_eq!(pick_loot(&entries, 100).unwrap().name, "D");
    }

    #[test]
    fn pick_loot_empty_table() {
        assert!(pick_loot(&[], 50).is_none());
    }

    // --- Player power level ---

    #[test]
    fn player_power_with_weapon() {
        let p = PlayerCombatProfile {
            hp: 100,
            condition_mod: 50,
            speed_mod: 40,
            agility_mod: 30,
            strength_mod: 60,
            wisdom_mod: 20,
            intelligence_mod: 25,
            dodge_skill: 10,
            attack_skill: 15,
            shoot_skill: 12,
            magic_skill: 18,
            has_weapon: true,
            has_second_weapon: false,
            has_bow: false,
        };
        // condition + speed + agility + dodge + hp + strength + attack
        // 50 + 40 + 30 + 10 + 100 + 60 + 15 = 305
        assert_eq!(player_power_level(&p), 305.0);
    }

    #[test]
    fn player_power_with_bow() {
        let p = PlayerCombatProfile {
            hp: 100,
            condition_mod: 50,
            speed_mod: 40,
            agility_mod: 30,
            strength_mod: 60,
            wisdom_mod: 20,
            intelligence_mod: 25,
            dodge_skill: 10,
            attack_skill: 15,
            shoot_skill: 12,
            magic_skill: 18,
            has_weapon: false,
            has_second_weapon: false,
            has_bow: true,
        };
        // condition + speed + agility + dodge + hp + strength + shoot
        // 50 + 40 + 30 + 10 + 100 + 60 + 12 = 302
        assert_eq!(player_power_level(&p), 302.0);
    }

    #[test]
    fn player_power_mage() {
        let p = PlayerCombatProfile {
            hp: 100,
            condition_mod: 50,
            speed_mod: 40,
            agility_mod: 30,
            strength_mod: 60,
            wisdom_mod: 20,
            intelligence_mod: 25,
            dodge_skill: 10,
            attack_skill: 15,
            shoot_skill: 12,
            magic_skill: 18,
            has_weapon: false,
            has_second_weapon: false,
            has_bow: false,
        };
        // condition + speed + agility + dodge + hp + wisdom + intelligence + magic
        // 50 + 40 + 30 + 10 + 100 + 20 + 25 + 18 = 293
        assert_eq!(player_power_level(&p), 293.0);
    }

    // --- Difficulty tier ---

    #[test]
    fn difficulty_tier_easy() {
        let t = difficulty_tier(1);
        assert_eq!(t.min_ratio, 0.0);
        assert_eq!(t.max_ratio, 0.5);
        let t2 = difficulty_tier(24);
        assert_eq!(t2.min_ratio, 0.0);
    }

    #[test]
    fn difficulty_tier_normal() {
        let t = difficulty_tier(25);
        assert_eq!(t.min_ratio, 0.5);
        assert_eq!(t.max_ratio, 1.2);
        let t2 = difficulty_tier(89);
        assert_eq!(t2.max_ratio, 1.2);
    }

    #[test]
    fn difficulty_tier_hard() {
        let t = difficulty_tier(90);
        assert_eq!(t.min_ratio, 1.2);
        assert_eq!(t.max_ratio, 2.0);
    }

    // --- Monster tier matching ---

    #[test]
    fn monster_matches_easy_tier() {
        let m = test_monster(1, 1, 4, 3.0, "Altara");
        // stat_sum = 3+3+3+3+1+4 = 17
        let player_power = 100.0;
        // ratio = 17/100 = 0.17 → should match easy tier
        let tier = DifficultyTier {
            min_ratio: 0.0,
            max_ratio: 0.5,
        };
        assert!(monster_matches_tier(&m, player_power, &tier));
    }

    #[test]
    fn monster_outside_tier() {
        let m = test_monster(1, 100, 500, 300.0, "Altara");
        // stat_sum = 300+300+300+300+100+500 = 1800
        let player_power = 100.0;
        // ratio = 18.0 → well above any tier
        let tier = DifficultyTier {
            min_ratio: 0.0,
            max_ratio: 2.0,
        };
        assert!(!monster_matches_tier(&m, player_power, &tier));
    }

    #[test]
    fn monster_matches_zero_player_power() {
        let m = test_monster(1, 1, 4, 3.0, "Altara");
        let tier = DifficultyTier {
            min_ratio: 0.0,
            max_ratio: 2.0,
        };
        assert!(!monster_matches_tier(&m, 0.0, &tier));
    }

    // --- Encounter region ---

    #[test]
    fn region_forest_is_ardulith() {
        assert_eq!(encounter_region("Las"), "Ardulith");
    }

    #[test]
    fn region_everything_else_is_altara() {
        assert_eq!(encounter_region("Góry"), "Altara");
        assert_eq!(encounter_region("Altara"), "Altara");
    }

    // --- Rewards ---

    #[test]
    fn encounter_reward_normal() {
        let m = test_monster(1, 10, 40, 21.0, "Altara");
        // stat_sum = 21+21+21+21+10+40 = 134
        let player_power = 134.0;
        // span = 1.0
        let r = encounter_reward(&m, player_power);
        assert_eq!(r.xp, 134);
        assert_eq!(r.gold, 134);
    }

    #[test]
    fn encounter_reward_capped_span() {
        let m = test_monster(1, 100, 500, 300.0, "Altara");
        // stat_sum = 1800
        let player_power = 100.0;
        // span = 18.0, capped to 2.0
        let r = encounter_reward(&m, player_power);
        assert_eq!(r.xp, 3600); // 1800 * 2.0
    }

    #[test]
    fn encounter_reward_zero_player() {
        let m = test_monster(1, 10, 40, 21.0, "Altara");
        let r = encounter_reward(&m, 0.0);
        // span defaults to 2.0
        assert_eq!(r.xp, 268); // ceil(134 * 2.0)
    }

    // --- Hunter quest reward ---

    #[test]
    fn hunter_reward_single_kill() {
        let r = hunter_quest_reward(10, 10, 100, 50, 1);
        // span = 1.0, exp = ceil(100 * 1.0) = 100, gold = ceil(50 * 1 * 1.0) = 50
        assert_eq!(r.xp, 100);
        assert_eq!(r.gold, 50);
    }

    #[test]
    fn hunter_reward_multiple_kills() {
        let r = hunter_quest_reward(10, 10, 100, 50, 3);
        // span = 1.0, exp_base = 100
        // k=2: ceil(100/5 * (sqrt(2) + 4.5)) = ceil(20 * 5.91421) = ceil(118.28) = 119
        // k=3: ceil(100/5 * (sqrt(3) + 4.5)) = ceil(20 * 6.23205) = ceil(124.64) = 125
        // total = 100 + 119 + 125 = 344
        // gold = ceil(50 * 3 * 1.0) = 150
        assert_eq!(r.xp, 344);
        assert_eq!(r.gold, 150);
    }

    // --- Escape ---

    #[test]
    fn escape_check_success() {
        // player: speed=50, perception=20, roll=80 → 150
        // monster: speed=60.0, roll=40 → 100
        // result = 150 - 100 = 50 > 0 → success
        assert_eq!(explore_escape_check(50, 20, 80, 60.0, 40), 50);
    }

    #[test]
    fn escape_check_failure() {
        assert!(explore_escape_check(10, 5, 10, 100.0, 90) < 0);
    }

    #[test]
    fn escape_xp_calculation() {
        let m = test_monster(1, 10, 40, 21.0, "Altara");
        // (21+21+21+21) / 100 = 0.84 → ceil = 1
        assert_eq!(escape_xp(&m), 1);

        let m2 = test_monster(2, 100, 500, 300.0, "Altara");
        // (300+300+300+300) / 100 = 12.0 → ceil = 12
        assert_eq!(escape_xp(&m2), 12);
    }

    // --- Random monster generation ---

    #[test]
    fn random_monster_basic() {
        let inputs = RandomMonsterInputs {
            name: "Test Beast".to_owned(),
            player_hp: 100,
            condition_mod: 50,
            speed_mod: 40,
            strength_mod: 60,
            intelligence_mod: 25,
            agility_mod: 30,
            wisdom_mod: 20,
            dodge_skill: 10,
            attack_skill: 15,
            shoot_skill: 12,
            magic_skill: 18,
            has_weapon: true,
            has_bow: false,
            weapon_power: 10.0,
            bow_power: 0.0,
            arrow_power: 0.0,
        };
        // No variation (all zero)
        let vars = [
            StatVariation {
                pct_0_to_15: 0,
                positive: true,
            },
            StatVariation {
                pct_0_to_15: 0,
                positive: true,
            },
            StatVariation {
                pct_0_to_15: 0,
                positive: true,
            },
            StatVariation {
                pct_0_to_15: 0,
                positive: true,
            },
            StatVariation {
                pct_0_to_15: 0,
                positive: true,
            },
        ];
        let elem = ElementRoll {
            chance_roll: 50,
            element_index: 0,
        };
        let m = generate_random_monster(&inputs, &vars, &elem);

        assert_eq!(m.name, "Test Beast");
        // base_str = max(60,25) = 60, + weapon: 10 + attack: 15 = 85
        assert_eq!(m.strength, 85.0);
        // base_agi = max(30,20) = 30 + dodge 10 = 40
        assert_eq!(m.agility, 40.0);
        assert_eq!(m.hp, 100);
        assert_eq!(m.endurance, 50.0); // condition_mod
        assert_eq!(m.speed, 40.0); // speed_mod
        assert_eq!(m.dmgtype, Element::None);
    }

    #[test]
    fn random_monster_with_element() {
        let inputs = RandomMonsterInputs {
            name: "Fire Beast".to_owned(),
            player_hp: 100,
            condition_mod: 50,
            speed_mod: 40,
            strength_mod: 60,
            intelligence_mod: 25,
            agility_mod: 30,
            wisdom_mod: 20,
            dodge_skill: 10,
            attack_skill: 15,
            shoot_skill: 12,
            magic_skill: 18,
            has_weapon: true,
            has_bow: false,
            weapon_power: 10.0,
            bow_power: 0.0,
            arrow_power: 0.0,
        };
        let vars = [
            StatVariation {
                pct_0_to_15: 0,
                positive: true,
            },
            StatVariation {
                pct_0_to_15: 0,
                positive: true,
            },
            StatVariation {
                pct_0_to_15: 0,
                positive: true,
            },
            StatVariation {
                pct_0_to_15: 0,
                positive: true,
            },
            StatVariation {
                pct_0_to_15: 0,
                positive: true,
            },
        ];
        // chance_roll=80 (>74, <90 → weak), element_index=1 → Fire
        let elem = ElementRoll {
            chance_roll: 80,
            element_index: 1,
        };
        let m = generate_random_monster(&inputs, &vars, &elem);
        assert_eq!(m.dmgtype, Element::Fire);
    }

    #[test]
    fn random_monster_stat_variation() {
        let inputs = RandomMonsterInputs {
            name: "Varied".to_owned(),
            player_hp: 100,
            condition_mod: 100,
            speed_mod: 100,
            strength_mod: 100,
            intelligence_mod: 50,
            agility_mod: 80,
            wisdom_mod: 60,
            dodge_skill: 0,
            attack_skill: 0,
            shoot_skill: 0,
            magic_skill: 50,
            has_weapon: false,
            has_bow: false,
            weapon_power: 0.0,
            bow_power: 0.0,
            arrow_power: 0.0,
        };
        // +10% on stat 0 (str), -15% on stat 1 (agi)
        let vars = [
            StatVariation {
                pct_0_to_15: 10,
                positive: true,
            },
            StatVariation {
                pct_0_to_15: 15,
                positive: false,
            },
            StatVariation {
                pct_0_to_15: 0,
                positive: true,
            },
            StatVariation {
                pct_0_to_15: 0,
                positive: true,
            },
            StatVariation {
                pct_0_to_15: 0,
                positive: true,
            },
        ];
        let elem = ElementRoll {
            chance_roll: 50,
            element_index: 0,
        };
        let m = generate_random_monster(&inputs, &vars, &elem);

        // base_str = max(100,50) = 100 + magic_skill=50 = 150; +10% = 165
        assert!((m.strength - 165.0).abs() < 0.001);
        // base_agi = max(80,60) = 80 + dodge 0 = 80; -15% = 68
        assert!((m.agility - 68.0).abs() < 0.001);
    }

    // --- stat_sum ---

    #[test]
    fn monster_stat_sum() {
        let m = test_monster(1, 10, 40, 21.0, "Altara");
        // 21+21+21+21+10+40 = 134
        assert_eq!(m.stat_sum(), 134.0);
    }
}
