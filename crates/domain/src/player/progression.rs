//! Player progression — derived calculations, XP leveling, HP/mana formulas.
//!
//! Ports the pure calculation logic from PHP `player_class.php`:
//! - `checkexp()`: XP gain → level-up for stats and skills
//! - `dying()`: XP loss on death
//! - Max mana formula (from `includes/head.php`)
//! - HP per condition level-up table (from `checkexp`)
//!
//! Equipment bonus application lives in `crate::equipment` (MP-09-02).
//! This module handles the remaining progression formulas.

use crate::player::skills::PlayerSkill;
use crate::player::stats::PlayerStat;
use crate::player::{Class, Race};

// ---------------------------------------------------------------------------
// HP per condition level-up
// ---------------------------------------------------------------------------

/// HP gained per condition level-up, indexed by race.
pub fn hp_per_condition_for_race(race: &Race) -> i32 {
    match race {
        Race::Human | Race::Hobbit => 4,
        Race::Elf => 3,
        Race::Dwarf | Race::Lizardman => 5,
        Race::Gnome => 2,
    }
}

/// HP gained per condition level-up, indexed by class.
pub fn hp_per_condition_for_class(class: &Class) -> i32 {
    match class {
        Class::Barbarian => 6,
        Class::Warrior => 5,
        Class::Thief => 4,
        Class::Mage => 3,
        Class::Craftsman => 2,
    }
}

/// Total HP gained per condition level-up = race contribution + class contribution.
pub fn hp_per_condition_levelup(race: &Race, class: &Class) -> i32 {
    hp_per_condition_for_race(race) + hp_per_condition_for_class(class)
}

// ---------------------------------------------------------------------------
// Max mana calculation
// ---------------------------------------------------------------------------

/// Calculate max mana from derived stats and equipment.
///
/// Formula (from `includes/head.php`):
///   1. `max_mana = floor(inteli_modified + wisdom_modified)`
///   2. If class is Mage: `max_mana *= 2`
///   3. `max_mana += floor((mage_clothing_power / 100) * max_mana)`
#[allow(clippy::cast_possible_truncation)]
pub fn max_mana(stats: &[PlayerStat], class: &Class, mage_clothing_power: i32) -> i32 {
    let inteli = stat_modified(stats, "inteli");
    let wisdom = stat_modified(stats, "wisdom");

    let mut mana = (f64::from(inteli) + f64::from(wisdom)).floor() as i32;

    if *class == Class::Mage {
        mana *= 2;
    }

    if mage_clothing_power != 0 {
        mana += (f64::from(mage_clothing_power) / 100.0 * f64::from(mana)).floor() as i32;
    }

    mana
}

// ---------------------------------------------------------------------------
// XP gain and level-up — stats
// ---------------------------------------------------------------------------

/// Result of applying XP to a stat.
#[derive(Debug, Clone, PartialEq, Eq)]
pub struct StatXpResult {
    /// Number of level-ups that occurred.
    pub levels_gained: i32,
    /// HP change (only nonzero for condition stat).
    pub hp_change: i32,
    /// AP change (equals `levels_gained`).
    pub ap_change: i32,
}

/// Apply XP to a single stat, processing any resulting level-ups.
///
/// Mirrors PHP `checkexp()` for stats:
/// - XP needed for next level = `trained * 500`
/// - Stat can't level beyond `base` (the cap)
/// - Each level-up grants +1 AP
/// - Condition level-ups also grant HP (race+class specific)
///
/// Mutates `stat` in place. Returns a summary of what changed.
pub fn apply_stat_xp(
    stat: &mut PlayerStat,
    xp_gained: i32,
    race: &Race,
    class: &Class,
) -> StatXpResult {
    let mut result = StatXpResult {
        levels_gained: 0,
        hp_change: 0,
        ap_change: 0,
    };

    // Can't gain XP if already at cap
    if stat.trained >= stat.base && stat.base > 0 {
        return result;
    }

    stat.xp += xp_gained;

    loop {
        // At cap — stop
        if stat.base > 0 && stat.trained >= stat.base {
            break;
        }

        let needed = stat.trained * 500;
        if needed <= 0 || stat.xp < needed {
            break;
        }

        // Level up
        stat.trained += 1;
        stat.modified += 1;
        stat.xp -= needed;
        result.levels_gained += 1;
        result.ap_change += 1;

        // Condition level-ups grant HP
        if stat.stat_key == "condition" {
            let hp = hp_per_condition_levelup(race, class);
            result.hp_change += hp;
        }
    }

    result
}

// ---------------------------------------------------------------------------
// XP gain and level-up — skills
// ---------------------------------------------------------------------------

/// Result of applying XP to a skill.
#[derive(Debug, Clone, PartialEq, Eq)]
pub struct SkillXpResult {
    /// Number of level-ups that occurred.
    pub levels_gained: i32,
}

/// Apply XP to a single skill, processing any resulting level-ups.
///
/// Mirrors PHP `checkexp()` for skills:
/// - XP needed for next level = `level * 100`
/// - Skill can't exceed level 100
pub fn apply_skill_xp(skill: &mut PlayerSkill, xp_gained: i32) -> SkillXpResult {
    let mut result = SkillXpResult { levels_gained: 0 };

    if skill.level >= 100 {
        return result;
    }

    skill.xp += xp_gained;

    loop {
        if skill.level >= 100 {
            break;
        }

        let needed = skill.level * 100;
        if needed <= 0 || skill.xp < needed {
            break;
        }

        skill.level += 1;
        skill.xp -= needed;
        result.levels_gained += 1;
    }

    result
}

// ---------------------------------------------------------------------------
// Death / XP loss
// ---------------------------------------------------------------------------

/// Outcome of the death penalty.
#[derive(Debug, Clone, PartialEq, Eq)]
pub enum DeathPenalty {
    /// The player lost a stat level.
    StatLevelLost {
        stat_key: String,
        label: String,
        hp_change: i32,
        ap_change: i32,
    },
    /// The player lost some stat XP (but no level).
    StatXpLost { stat_key: String, label: String },
    /// The player lost a skill level.
    SkillLevelLost { skill_key: String, label: String },
    /// The player lost some skill XP (but no level).
    SkillXpLost { skill_key: String, label: String },
}

/// Apply the death penalty to stats/skills.
///
/// Mirrors PHP `dying()`:
/// - 50% chance stat penalty, 50% chance skill penalty
/// - If the stat/skill is at max, lose a level; otherwise lose 1% of current XP
///
/// Returns the penalty applied (caller must also set HP to 0 and persist).
///
/// `roll` should be a random number 1..=100. Pass it explicitly for testability.
/// `stat_idx` / `skill_idx` are the random indices into the arrays.
pub fn apply_death_penalty(
    stats: &mut [PlayerStat],
    skills: &mut [PlayerSkill],
    race: &Race,
    class: &Class,
    roll: i32,
    stat_idx: usize,
    skill_idx: usize,
) -> DeathPenalty {
    if roll <= 50 && !stats.is_empty() {
        // Lose stat
        let idx = stat_idx % stats.len();
        let stat = &mut stats[idx];

        if stat.base > 0 && stat.trained >= stat.base {
            // At cap: lose a level
            let stat_key = stat.stat_key.clone();
            let label = stat.label.clone();
            stat.trained -= 1;
            stat.modified -= 1;
            stat.xp = 0;

            let mut hp_change = 0;
            let ap_change = -1;

            if stat_key == "condition" {
                hp_change = -hp_per_condition_levelup(race, class);
            }

            DeathPenalty::StatLevelLost {
                stat_key,
                label,
                hp_change,
                ap_change,
            }
        } else {
            // Not at cap: lose 1% of current XP
            let label = stat.label.clone();
            let stat_key = stat.stat_key.clone();
            let lost = (stat.xp + 99) / 100; // ceil(xp / 100)
            stat.xp = (stat.xp - lost).max(0);

            DeathPenalty::StatXpLost { stat_key, label }
        }
    } else if !skills.is_empty() {
        // Lose skill
        let idx = skill_idx % skills.len();
        let skill = &mut skills[idx];

        if skill.level >= 100 {
            // At max: lose a level
            let skill_key = skill.skill_key.clone();
            let label = skill.label.clone();
            skill.level -= 1;
            skill.xp = 0;

            DeathPenalty::SkillLevelLost { skill_key, label }
        } else {
            // Not at max: lose 10% of current XP
            let label = skill.label.clone();
            let skill_key = skill.skill_key.clone();
            let lost = (skill.xp + 9) / 10; // ceil(xp / 10)
            skill.xp = (skill.xp - lost).max(0);

            DeathPenalty::SkillXpLost { skill_key, label }
        }
    } else {
        // Edge case: both empty — no penalty
        DeathPenalty::StatXpLost {
            stat_key: String::new(),
            label: String::new(),
        }
    }
}

// ---------------------------------------------------------------------------
// Calculated player snapshot
// ---------------------------------------------------------------------------

/// A fully calculated player view, combining persisted state with derived values.
///
/// This is the read-only snapshot that the web layer uses for rendering pages.
/// It does NOT mutate storage — just computes derived values from inputs.
#[derive(Debug, Clone)]
pub struct CalculatedPlayer {
    /// Stats after equipment/blessing/bonus application.
    pub stats: Vec<PlayerStat>,
    /// Skills after blessing/craftsman/elemental-tool application.
    pub skills: Vec<PlayerSkill>,
    /// Maximum mana derived from stats + class + mage clothing.
    pub max_mana: i32,
    /// Current mana percentage (0–100), for UI bars.
    pub mana_percent: i32,
    /// Current HP percentage (0–100), for UI bars.
    pub hp_percent: i32,
    /// Current energy percentage (0–100), for UI bars.
    pub energy_percent: i32,
}

/// Build a fully calculated player snapshot from all required inputs.
///
/// This is the main entry point for the web layer to get a read-only
/// player view with all derived values computed.
#[allow(clippy::too_many_arguments, clippy::cast_possible_truncation)]
pub fn build_snapshot(
    stats: &[PlayerStat],
    skills: &[PlayerSkill],
    bonuses: &[crate::player::bonuses::PlayerBonus],
    loadout: &crate::equipment::EquipmentLoadout,
    class: &Class,
    race: &Race,
    bless_stat: &str,
    bless_value: i32,
    current_hp: i32,
    max_hp: i32,
    current_mana: i32,
    current_energy: f64,
    max_energy: f64,
    is_craftsman_context: bool,
    craft_skill_keys: &[&str],
) -> CalculatedPlayer {
    // 1. Clone stats and initialize modified = trained
    let mut calc_stats: Vec<PlayerStat> = stats
        .iter()
        .map(|s| {
            let mut c = s.clone();
            c.modified = c.trained;
            c
        })
        .collect();

    // 2. Apply equipment stat bonuses
    crate::equipment::apply_equipment_stat_bonuses(
        &mut calc_stats,
        loadout,
        bless_stat,
        bless_value,
        bonuses,
    );

    // 3. Clone skills and apply skill bonuses
    let mut calc_skills = skills.to_vec();

    // Skill blessing
    crate::equipment::apply_skill_bless(&mut calc_skills, bless_stat, bless_value);

    // Craftsman class bonus (only in crafting contexts)
    if is_craftsman_context && *class == Class::Craftsman {
        let is_gnome = *race == Race::Gnome;
        crate::equipment::apply_craftsman_bonus(&mut calc_skills, craft_skill_keys, is_gnome);
    }

    // Elemental tool bonus (only in crafting contexts)
    if is_craftsman_context {
        crate::equipment::apply_elemental_skill_bonus(&mut calc_skills, loadout, craft_skill_keys);
    }

    // Seeker bonus on perception
    let seeker_bonus = crate::equipment::check_bonus("seeker", &calc_stats, &calc_skills, bonuses);
    if seeker_bonus != 0 {
        if let Some(percep) = calc_skills.iter_mut().find(|s| s.skill_key == "perception") {
            percep.level += seeker_bonus;
        }
    }

    // 4. Max mana
    let mage_clothing_power = loadout.mage_clothing.as_ref().map_or(0, |item| item.power);
    let calc_max_mana = max_mana(&calc_stats, class, mage_clothing_power);

    // 5. Percentage bars
    let hp_percent = if max_hp > 0 {
        ((f64::from(current_hp) / f64::from(max_hp)) * 100.0)
            .round()
            .min(100.0) as i32
    } else {
        0
    };

    let mana_percent = if calc_max_mana > 0 {
        ((f64::from(current_mana) / f64::from(calc_max_mana)) * 100.0)
            .round()
            .min(100.0) as i32
    } else {
        0
    };

    let energy_percent = if max_energy > 0.0 {
        ((current_energy / max_energy) * 100.0).round().min(100.0) as i32
    } else {
        0
    };

    CalculatedPlayer {
        stats: calc_stats,
        skills: calc_skills,
        max_mana: calc_max_mana,
        mana_percent,
        hp_percent,
        energy_percent,
    }
}

// ---------------------------------------------------------------------------
// Helpers
// ---------------------------------------------------------------------------

fn stat_modified(stats: &[PlayerStat], key: &str) -> i32 {
    stats
        .iter()
        .find(|s| s.stat_key == key)
        .map_or(0, |s| s.modified)
}

// ---------------------------------------------------------------------------
// Tests
// ---------------------------------------------------------------------------

#[cfg(test)]
mod tests {
    use super::*;
    use crate::player::skills::default_skills;
    use crate::player::stats::default_stats;

    // -- HP per condition tests --

    #[test]
    fn hp_human_warrior() {
        assert_eq!(hp_per_condition_levelup(&Race::Human, &Class::Warrior), 9);
    }

    #[test]
    fn hp_dwarf_barbarian() {
        assert_eq!(
            hp_per_condition_levelup(&Race::Dwarf, &Class::Barbarian),
            11
        );
    }

    #[test]
    fn hp_gnome_craftsman() {
        assert_eq!(hp_per_condition_levelup(&Race::Gnome, &Class::Craftsman), 4);
    }

    #[test]
    fn hp_elf_mage() {
        assert_eq!(hp_per_condition_levelup(&Race::Elf, &Class::Mage), 6);
    }

    // -- Max mana tests --

    #[test]
    fn max_mana_basic() {
        let mut stats = default_stats();
        set_stat_modified(&mut stats, "inteli", 50);
        set_stat_modified(&mut stats, "wisdom", 30);
        // floor(50 + 30) = 80
        assert_eq!(max_mana(&stats, &Class::Warrior, 0), 80);
    }

    #[test]
    fn max_mana_mage_doubled() {
        let mut stats = default_stats();
        set_stat_modified(&mut stats, "inteli", 50);
        set_stat_modified(&mut stats, "wisdom", 30);
        // floor(80) * 2 = 160
        assert_eq!(max_mana(&stats, &Class::Mage, 0), 160);
    }

    #[test]
    fn max_mana_with_clothing() {
        let mut stats = default_stats();
        set_stat_modified(&mut stats, "inteli", 50);
        set_stat_modified(&mut stats, "wisdom", 30);
        // base = 80, clothing power 25 → floor(25/100 * 80) = floor(20) = 20 → 100
        assert_eq!(max_mana(&stats, &Class::Warrior, 25), 100);
    }

    #[test]
    fn max_mana_mage_with_clothing() {
        let mut stats = default_stats();
        set_stat_modified(&mut stats, "inteli", 50);
        set_stat_modified(&mut stats, "wisdom", 30);
        // base = 160 (mage), clothing power 50 → floor(50/100 * 160) = 80 → 240
        assert_eq!(max_mana(&stats, &Class::Mage, 50), 240);
    }

    #[test]
    fn max_mana_zero_stats() {
        let stats = default_stats();
        assert_eq!(max_mana(&stats, &Class::Mage, 0), 0);
    }

    // -- Stat XP tests --

    #[test]
    fn stat_xp_no_levelup() {
        let mut stat = make_stat("strength", 10, 0);
        let result = apply_stat_xp(&mut stat, 1000, &Race::Human, &Class::Warrior);
        assert_eq!(result.levels_gained, 0);
        assert_eq!(stat.xp, 1000);
        assert_eq!(stat.trained, 10);
    }

    #[test]
    fn stat_xp_single_levelup() {
        // Level 10 needs 10*500 = 5000 XP
        let mut stat = make_stat("strength", 10, 0);
        let result = apply_stat_xp(&mut stat, 5000, &Race::Human, &Class::Warrior);
        assert_eq!(result.levels_gained, 1);
        assert_eq!(result.ap_change, 1);
        assert_eq!(stat.trained, 11);
        assert_eq!(stat.xp, 0);
    }

    #[test]
    fn stat_xp_multiple_levelups() {
        // Level 2 needs 1000, level 3 needs 1500 → total 2500 for 2 levels
        let mut stat = make_stat("strength", 2, 0);
        let result = apply_stat_xp(&mut stat, 2500, &Race::Human, &Class::Warrior);
        assert_eq!(result.levels_gained, 2);
        assert_eq!(stat.trained, 4);
        assert_eq!(stat.xp, 0);
    }

    #[test]
    fn stat_xp_condition_grants_hp() {
        let mut stat = make_stat("condition", 5, 0);
        let result = apply_stat_xp(&mut stat, 2500, &Race::Human, &Class::Warrior);
        assert_eq!(result.levels_gained, 1);
        assert_eq!(result.hp_change, 9); // Human(4) + Warrior(5)
    }

    #[test]
    fn stat_xp_capped() {
        let mut stat = make_stat("strength", 10, 0);
        stat.base = 10; // Already at cap
        let result = apply_stat_xp(&mut stat, 50000, &Race::Human, &Class::Warrior);
        assert_eq!(result.levels_gained, 0);
        assert_eq!(stat.xp, 0); // XP not added when at cap
    }

    #[test]
    fn stat_xp_partial_overflow() {
        // Level 10 needs 5000, we give 6000 → level up to 11 with 1000 remaining
        let mut stat = make_stat("strength", 10, 0);
        let result = apply_stat_xp(&mut stat, 6000, &Race::Human, &Class::Warrior);
        assert_eq!(result.levels_gained, 1);
        assert_eq!(stat.trained, 11);
        assert_eq!(stat.xp, 1000);
    }

    // -- Skill XP tests --

    #[test]
    fn skill_xp_no_levelup() {
        let mut skill = make_skill("mining", 10, 0);
        let result = apply_skill_xp(&mut skill, 500);
        assert_eq!(result.levels_gained, 0);
        assert_eq!(skill.xp, 500);
    }

    #[test]
    fn skill_xp_single_levelup() {
        // Level 10 needs 10*100 = 1000
        let mut skill = make_skill("mining", 10, 0);
        let result = apply_skill_xp(&mut skill, 1000);
        assert_eq!(result.levels_gained, 1);
        assert_eq!(skill.level, 11);
        assert_eq!(skill.xp, 0);
    }

    #[test]
    fn skill_xp_capped_at_100() {
        let mut skill = make_skill("mining", 100, 0);
        let result = apply_skill_xp(&mut skill, 50000);
        assert_eq!(result.levels_gained, 0);
        assert_eq!(skill.level, 100);
    }

    #[test]
    fn skill_xp_multiple_levelups() {
        // Level 2 needs 200, level 3 needs 300 → total 500 for 2 levels
        let mut skill = make_skill("mining", 2, 0);
        let result = apply_skill_xp(&mut skill, 500);
        assert_eq!(result.levels_gained, 2);
        assert_eq!(skill.level, 4);
        assert_eq!(skill.xp, 0);
    }

    // -- Death penalty tests --

    #[test]
    fn death_stat_level_lost_at_cap() {
        let mut stats = vec![make_stat("strength", 50, 0)];
        stats[0].base = 50; // at cap
        let mut skills = default_skills();
        let penalty = apply_death_penalty(
            &mut stats,
            &mut skills,
            &Race::Human,
            &Class::Warrior,
            25, // roll <= 50 → stat penalty
            0,
            0,
        );
        match penalty {
            DeathPenalty::StatLevelLost {
                stat_key,
                ap_change,
                ..
            } => {
                assert_eq!(stat_key, "strength");
                assert_eq!(ap_change, -1);
                assert_eq!(stats[0].trained, 49);
            }
            _ => panic!("expected StatLevelLost"),
        }
    }

    #[test]
    fn death_stat_xp_lost_below_cap() {
        let mut stats = vec![make_stat("agility", 20, 5000)];
        stats[0].base = 100;
        let mut skills = default_skills();
        let penalty = apply_death_penalty(
            &mut stats,
            &mut skills,
            &Race::Human,
            &Class::Warrior,
            30, // stat penalty
            0,
            0,
        );
        match penalty {
            DeathPenalty::StatXpLost { stat_key, .. } => {
                assert_eq!(stat_key, "agility");
                // Lost ceil(5000/100) = 50
                assert_eq!(stats[0].xp, 4950);
            }
            _ => panic!("expected StatXpLost"),
        }
    }

    #[test]
    fn death_skill_level_lost_at_100() {
        let mut stats = default_stats();
        let mut skills = vec![make_skill("mining", 100, 0)];
        let penalty = apply_death_penalty(
            &mut stats,
            &mut skills,
            &Race::Human,
            &Class::Warrior,
            75, // roll > 50 → skill penalty
            0,
            0,
        );
        match penalty {
            DeathPenalty::SkillLevelLost { skill_key, .. } => {
                assert_eq!(skill_key, "mining");
                assert_eq!(skills[0].level, 99);
            }
            _ => panic!("expected SkillLevelLost"),
        }
    }

    #[test]
    fn death_skill_xp_lost_below_100() {
        let mut stats = default_stats();
        let mut skills = vec![make_skill("mining", 50, 1000)];
        let penalty = apply_death_penalty(
            &mut stats,
            &mut skills,
            &Race::Human,
            &Class::Warrior,
            75,
            0,
            0,
        );
        match penalty {
            DeathPenalty::SkillXpLost { skill_key, .. } => {
                assert_eq!(skill_key, "mining");
                // Lost ceil(1000/10) = 100
                assert_eq!(skills[0].xp, 900);
            }
            _ => panic!("expected SkillXpLost"),
        }
    }

    #[test]
    fn death_condition_level_lost_reduces_hp() {
        let mut stats = vec![make_stat("condition", 30, 0)];
        stats[0].base = 30;
        let mut skills = default_skills();
        let penalty = apply_death_penalty(
            &mut stats,
            &mut skills,
            &Race::Dwarf,
            &Class::Barbarian,
            10, // stat penalty
            0,
            0,
        );
        match penalty {
            DeathPenalty::StatLevelLost { hp_change, .. } => {
                assert_eq!(hp_change, -11); // Dwarf(5) + Barbarian(6)
            }
            _ => panic!("expected StatLevelLost"),
        }
    }

    // -- Snapshot tests --

    #[test]
    fn build_snapshot_basic() {
        let stats = default_stats();
        let skills = default_skills();
        let loadout = crate::equipment::EquipmentLoadout::default();

        let snap = build_snapshot(
            &stats,
            &skills,
            &[],
            &loadout,
            &Class::Warrior,
            &Race::Human,
            "",
            0,
            100,  // hp
            200,  // max_hp
            50,   // mana
            80.0, // energy
            100.0,
            false,
            &[],
        );

        assert_eq!(snap.hp_percent, 50);
        assert_eq!(snap.energy_percent, 80);
        assert_eq!(snap.max_mana, 0); // stats all zero
        assert_eq!(snap.mana_percent, 0);
    }

    #[test]
    fn build_snapshot_with_stats() {
        let mut stats = default_stats();
        for s in &mut stats {
            s.trained = 20;
        }
        let skills = default_skills();
        let loadout = crate::equipment::EquipmentLoadout::default();

        let snap = build_snapshot(
            &stats,
            &skills,
            &[],
            &loadout,
            &Class::Mage,
            &Race::Elf,
            "",
            0,
            50,
            100,
            40,
            50.0,
            100.0,
            false,
            &[],
        );

        // inteli=20, wisdom=20 → 40, Mage doubles → 80
        assert_eq!(snap.max_mana, 80);
        assert_eq!(snap.mana_percent, 50); // 40/80
        assert_eq!(snap.hp_percent, 50);
    }

    // -- Helpers --

    fn make_stat(key: &str, trained: i32, xp: i32) -> PlayerStat {
        PlayerStat {
            stat_key: key.to_owned(),
            label: key.to_owned(),
            base: 100, // default high cap
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

    fn set_stat_modified(stats: &mut [PlayerStat], key: &str, value: i32) {
        if let Some(s) = stats.iter_mut().find(|s| s.stat_key == key) {
            s.modified = value;
        }
    }
}
