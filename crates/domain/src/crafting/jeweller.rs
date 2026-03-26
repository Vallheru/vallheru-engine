//! Jewellery crafting: rings, stat-bonus rings, special/relic rings, and
//! craftsman guild missions.
//!
//! Ported from `jeweller.php`, `jewellershop.php`, and the jewellery
//! portions of `crafts.php`.
//!
//! ## Ring tiers
//!
//! 1. **Simple rings** — 1 adamantium + 1 energy each, any class.
//! 2. **Stat-bonus rings** — craftsman only, require plan + minerals + plain
//!    rings + energy. May require multiple sessions (partial work).
//! 3. **Special/Relic rings** — craftsman only, upgrade stat-bonus rings with
//!    meteor. Can produce "God" tier (×4 power) or normal special (×2 power).
//!
//! ## Craftsman guild missions
//!
//! Random tasks across 9 professions. Each mission has an energy cost, a 5%
//! accident chance, and rare tool/plan loot.

use std::fmt;

// ---------------------------------------------------------------------------
// Ring stat bonuses
// ---------------------------------------------------------------------------

/// The six stat types a ring can grant a bonus to.
#[derive(Debug, Clone, Copy, PartialEq, Eq, Hash)]
pub enum RingStat {
    Agility,
    Strength,
    Intelligence,
    Wisdom,
    Speed,
    Condition,
}

impl RingStat {
    /// DB column key for the stat.
    #[must_use]
    pub fn db_key(self) -> &'static str {
        match self {
            Self::Agility => "agility",
            Self::Strength => "strength",
            Self::Intelligence => "inteli",
            Self::Wisdom => "wisdom",
            Self::Speed => "speed",
            Self::Condition => "condition",
        }
    }

    /// Polish display names used in ring names (matches PHP `$arrStats`).
    #[must_use]
    pub fn polish_name(self) -> &'static str {
        match self {
            Self::Agility => "zręczności",
            Self::Strength => "siły",
            Self::Intelligence => "inteligencji",
            Self::Wisdom => "siły woli",
            Self::Speed => "szybkości",
            Self::Condition => "kondycji",
        }
    }

    /// All six stat variants in the order used by PHP arrays.
    pub const ALL: [Self; 6] = [
        Self::Agility,
        Self::Strength,
        Self::Intelligence,
        Self::Wisdom,
        Self::Speed,
        Self::Condition,
    ];
}

// ---------------------------------------------------------------------------
// Simple ring crafting
// ---------------------------------------------------------------------------

/// Success chance for crafting simple (plain) rings.
///
/// PHP: `skill * 100`, capped at 95. The check uses `roll < chance`
/// (strict less-than).
#[must_use]
#[allow(clippy::cast_possible_truncation)]
pub fn simple_ring_chance(jewellery_skill: f64) -> i32 {
    let chance = (jewellery_skill * 100.0) as i32;
    chance.min(95)
}

/// XP gained from crafting simple rings.
///
/// PHP: XP = number of successes, doubled for craftsman.
#[must_use]
pub fn simple_ring_xp(successes: i32, is_craftsman: bool) -> i32 {
    if is_craftsman {
        successes * 2
    } else {
        successes
    }
}

// ---------------------------------------------------------------------------
// Stat-bonus ring crafting
// ---------------------------------------------------------------------------

/// Mineral costs for crafting one stat-bonus ring at a given plan level.
///
/// Returns `(adamantium, crystal, meteor)`.
///
/// PHP: adamantium = level, crystal = `ceil(level / 2)`, meteor = `ceil(level / 4)`.
#[must_use]
#[allow(clippy::cast_possible_truncation)]
pub fn stat_ring_mineral_cost(plan_level: i32) -> (i32, i32, i32) {
    let adam = plan_level;
    let cryst = (f64::from(plan_level) / 2.0).ceil() as i32;
    let meteor = (f64::from(plan_level) / 4.0).ceil() as i32;
    (adam, cryst, meteor)
}

/// Energy cost per stat-bonus ring = plan level (1 energy per level).
#[must_use]
pub fn stat_ring_energy_cost(plan_level: i32) -> i32 {
    plan_level
}

/// Whether a player can choose which stat the ring bonuses (instead of random).
///
/// PHP: `skill >= level * 4`.
#[must_use]
pub fn can_choose_stat(jewellery_skill: f64, plan_level: i32) -> bool {
    jewellery_skill >= f64::from(plan_level * 4)
}

/// Success chance for stat-bonus ring crafting.
///
/// PHP: `((skill + stat) / level) * 50`, capped at 95.
#[must_use]
#[allow(clippy::cast_possible_truncation)]
pub fn stat_ring_chance(jewellery_skill: f64, stat_value: f64, plan_level: i32) -> i32 {
    let chance = ((jewellery_skill + stat_value) / f64::from(plan_level)) * 50.0;
    (chance as i32).min(95)
}

/// How many rings can be made at once given energy spent and plan level.
///
/// PHP: `floor(energy_spent / level)`. If < 1, a partial-work session starts.
#[must_use]
pub fn stat_ring_batch_size(energy_spent: i32, plan_level: i32) -> i32 {
    if plan_level <= 0 {
        return 0;
    }
    energy_spent / plan_level
}

/// Compute the bonus value for a successfully crafted stat-bonus ring.
///
/// PHP: `floor(rand(0, skill) + stat / 5)`, clamped to `[1, max_bonus]`.
///
/// `roll_0_to_skill` is the random component in `[0, skill]`.
#[must_use]
#[allow(clippy::cast_possible_truncation)]
pub fn stat_ring_bonus(roll_0_to_skill: f64, stat_value: f64, max_bonus: i32) -> i32 {
    let raw = (roll_0_to_skill + stat_value / 5.0).floor() as i32;
    raw.max(1).min(max_bonus)
}

/// Sell cost of a stat-bonus ring.
///
/// PHP: `ceil(plan_cost / 20)`.
#[must_use]
#[allow(clippy::cast_possible_truncation)]
pub fn stat_ring_sell_cost(plan_cost: i32) -> i32 {
    (f64::from(plan_cost) / 20.0).ceil() as i32
}

/// XP for crafting stat-bonus rings.
///
/// PHP: success → `level * 10` per ring, failure → `2`. XP split evenly
/// between jewellery skill and the ring's stat.
#[must_use]
pub fn stat_ring_xp(plan_level: i32, successes: i32) -> i32 {
    plan_level * 10 * successes
}

/// XP on failure (too little energy, no success before completion).
pub const STAT_RING_FAIL_XP: i32 = 2;

// ---------------------------------------------------------------------------
// Special/Relic ring crafting
// ---------------------------------------------------------------------------

/// The three special ring prefixes (before "God" tier).
#[derive(Debug, Clone, Copy, PartialEq, Eq)]
pub enum SpecialPrefix {
    /// Elfi (Elven)
    Elven,
    /// Krasnoludzki (Dwarven)
    Dwarven,
    /// Gnomi (Gnomish)
    Gnomish,
}

impl SpecialPrefix {
    /// Polish prefix string used in ring names.
    #[must_use]
    pub fn polish_prefix(self) -> &'static str {
        match self {
            Self::Elven => "Elfi ",
            Self::Dwarven => "Krasnoludzki ",
            Self::Gnomish => "Gnomi ",
        }
    }
}

impl fmt::Display for SpecialPrefix {
    fn fmt(&self, f: &mut fmt::Formatter<'_>) -> fmt::Result {
        f.write_str(self.polish_prefix())
    }
}

/// Determine the special prefix based on the stat of the ring being upgraded.
///
/// PHP uses two different stat orderings for mapping to prefixes. The
/// `create` action uses: `[agility, speed, strength, condition, inteli, wisdom]`
/// → `[Elfi, Krasnoludzki, Gnomi]` (pairs of 2).
///
/// The `continue` action uses: `[inteli, wisdom, agility, speed, strength, condition]`
/// → `[Gnomi, Elfi, Krasnoludzki]` (pairs of 2).
///
/// Both orderings map the same stat to the same prefix:
/// - Agility, Speed → Elven
/// - Strength, Condition → Dwarven
/// - Intelligence, Wisdom → Gnomish
#[must_use]
pub fn special_prefix_for_stat(stat: RingStat) -> SpecialPrefix {
    match stat {
        RingStat::Agility | RingStat::Speed => SpecialPrefix::Elven,
        RingStat::Strength | RingStat::Condition => SpecialPrefix::Dwarven,
        RingStat::Intelligence | RingStat::Wisdom => SpecialPrefix::Gnomish,
    }
}

/// Energy cost per special ring = `level * 1.5`.
#[must_use]
pub fn special_ring_energy_cost(plan_level: i32) -> f64 {
    f64::from(plan_level) * 1.5
}

/// Meteor cost per special ring = `ceil(level / 2)`.
#[must_use]
#[allow(clippy::cast_possible_truncation)]
pub fn special_ring_meteor_cost(plan_level: i32) -> i32 {
    (f64::from(plan_level) / 2.0).ceil() as i32
}

/// Success chance for special ring crafting.
///
/// PHP: `floor(((skill + stat) / 50) * 0.5) + 5`, capped at 15.
#[must_use]
#[allow(clippy::cast_possible_truncation)]
pub fn special_ring_chance(jewellery_skill: f64, stat_value: f64) -> i32 {
    let chance = ((jewellery_skill + stat_value) / 50.0 * 0.5).floor() as i32 + 5;
    chance.min(15)
}

/// Outcome of a single special ring crafting roll.
#[derive(Debug, Clone, Copy, PartialEq, Eq)]
pub enum SpecialRingOutcome {
    /// "Boski" (God) tier — power ×4, XP = `energy_cost * 50`.
    God,
    /// Normal special (Elven/Dwarven/Gnomish) — power ×2, XP = `energy_cost * 25`.
    Special,
    /// Failed — XP = 2.
    Failed,
}

/// Determine the outcome of a single special ring roll.
///
/// PHP logic:
/// - `roll == 1` AND `coin_flip == 1` (1-in-2) → God
/// - `roll <= chance` → Special
/// - else → Failed
///
/// `roll_1_to_100` is the main roll, `coin_flip_1_to_2` is the secondary.
#[must_use]
pub fn special_ring_roll(
    roll_1_to_100: i32,
    coin_flip_1_to_2: i32,
    chance: i32,
) -> SpecialRingOutcome {
    if roll_1_to_100 == 1 && coin_flip_1_to_2 == 1 {
        return SpecialRingOutcome::God;
    }
    if roll_1_to_100 <= chance {
        SpecialRingOutcome::Special
    } else {
        SpecialRingOutcome::Failed
    }
}

/// Power multiplier for special ring outcomes.
#[must_use]
pub fn special_ring_power_multiplier(outcome: SpecialRingOutcome) -> i32 {
    match outcome {
        SpecialRingOutcome::God => 4,
        SpecialRingOutcome::Special => 2,
        SpecialRingOutcome::Failed => 0,
    }
}

/// XP for a single special ring outcome.
///
/// PHP: God = `energy_cost * 50`, Special = `energy_cost * 25`, Failed = 2.
/// XP is split evenly between jewellery skill and the ring's stat.
#[must_use]
#[allow(clippy::cast_possible_truncation)]
pub fn special_ring_xp(outcome: SpecialRingOutcome, energy_per_ring: f64) -> i32 {
    match outcome {
        SpecialRingOutcome::God => (energy_per_ring * 50.0) as i32,
        SpecialRingOutcome::Special => (energy_per_ring * 25.0) as i32,
        SpecialRingOutcome::Failed => 2,
    }
}

/// Sell cost of a special ring.
///
/// PHP: `ceil(plan_cost / 10)`.
#[must_use]
#[allow(clippy::cast_possible_truncation)]
pub fn special_ring_sell_cost(plan_cost: i32) -> i32 {
    (f64::from(plan_cost) / 10.0).ceil() as i32
}

// ---------------------------------------------------------------------------
// Jeweller shop (jewellershop.php)
// ---------------------------------------------------------------------------

/// Cost to buy a ring from the jeweller shop.
pub const SHOP_RING_COST: i32 = 500;

/// Ring IDs in the shop are 1..=6.
pub const SHOP_RING_COUNT: i32 = 6;

// ---------------------------------------------------------------------------
// Plan purchase
// ---------------------------------------------------------------------------

/// Errors when trying to buy a jeweller plan.
#[derive(Debug, Clone, PartialEq, Eq)]
pub enum PlanBuyError {
    AlreadyOwned,
    NotAvailable,
    InsufficientGold { cost: i32, available: i32 },
    SkillTooLow { required: i32, actual: i32 },
    NotCraftsman,
}

impl fmt::Display for PlanBuyError {
    fn fmt(&self, f: &mut fmt::Formatter<'_>) -> fmt::Result {
        match self {
            Self::AlreadyOwned => write!(f, "Plan already owned"),
            Self::NotAvailable => write!(f, "Plan not available"),
            Self::InsufficientGold { cost, available } => {
                write!(f, "Need {cost} gold, have {available}")
            }
            Self::SkillTooLow { required, actual } => {
                write!(f, "Need jewellery level {required}, have {actual}")
            }
            Self::NotCraftsman => write!(f, "Only craftsmen can buy this plan"),
        }
    }
}

impl std::error::Error for PlanBuyError {}

/// Validate whether a player can buy a jeweller plan.
///
/// PHP: Non-craftsmen can only buy plan #1 (simple ring). Plans > 1
/// require craftsman class. Also checks gold, skill level, and ownership.
pub fn can_buy_plan(
    plan_id: i32,
    is_craftsman: bool,
    already_owned: bool,
    plan_cost: i32,
    plan_level: i32,
    player_gold: i32,
    player_jewellery_skill: i32,
) -> Result<(), PlanBuyError> {
    if already_owned {
        return Err(PlanBuyError::AlreadyOwned);
    }
    if plan_id > 1 && !is_craftsman {
        return Err(PlanBuyError::NotCraftsman);
    }
    if plan_cost > player_gold {
        return Err(PlanBuyError::InsufficientGold {
            cost: plan_cost,
            available: player_gold,
        });
    }
    if player_jewellery_skill < plan_level {
        return Err(PlanBuyError::SkillTooLow {
            required: plan_level,
            actual: player_jewellery_skill,
        });
    }
    Ok(())
}

// ---------------------------------------------------------------------------
// Craftsman guild missions (crafts.php)
// ---------------------------------------------------------------------------

/// The 9 craft profession types used in mission generation.
#[derive(Debug, Clone, Copy, PartialEq, Eq, Hash)]
pub enum CraftProfession {
    Smelting,
    Lumberjack,
    Mining,
    Breeding,
    Jewellery,
    Herbalism,
    Alchemy,
    Carpentry,
    Smith,
}

impl CraftProfession {
    /// DB skill column name.
    #[must_use]
    pub fn skill_key(self) -> &'static str {
        match self {
            Self::Smelting => "smelting",
            Self::Lumberjack => "lumberjack",
            Self::Mining => "mining",
            Self::Breeding => "breeding",
            Self::Jewellery => "jewellry",
            Self::Herbalism => "herbalism",
            Self::Alchemy => "alchemy",
            Self::Carpentry => "carpentry",
            Self::Smith => "smith",
        }
    }

    /// The stat that receives XP alongside the skill for this profession.
    #[must_use]
    pub fn xp_stat_key(self) -> &'static str {
        match self {
            Self::Smelting | Self::Breeding => "condition",
            Self::Lumberjack | Self::Mining | Self::Smith => "strength",
            Self::Jewellery | Self::Herbalism | Self::Carpentry => "agility",
            Self::Alchemy => "wisdom",
        }
    }

    /// All 9 professions in PHP index order.
    pub const ALL: [Self; 9] = [
        Self::Smelting,
        Self::Lumberjack,
        Self::Mining,
        Self::Breeding,
        Self::Jewellery,
        Self::Herbalism,
        Self::Alchemy,
        Self::Carpentry,
        Self::Smith,
    ];
}

/// Cost to register at the craftsman guild for a given skill level.
///
/// PHP: `skill_level * 1000`.
#[must_use]
pub fn guild_registration_cost(skill_level: i32) -> i32 {
    skill_level * 1000
}

/// Whether a mission roll resulted in an accident.
///
/// PHP: `roll < 6` (5% chance) → accident, player takes HP damage.
#[must_use]
pub fn mission_is_accident(roll_1_to_100: i32) -> bool {
    roll_1_to_100 < 6
}

/// HP damage from a mission accident.
///
/// PHP: `ceil((max_hp / 100) * rand(1, 25))`.
#[must_use]
#[allow(clippy::cast_possible_truncation)]
pub fn mission_accident_damage(max_hp: i32, damage_roll_1_to_25: i32) -> i32 {
    (f64::from(max_hp) / 100.0 * f64::from(damage_roll_1_to_25)).ceil() as i32
}

/// Gold reward for a successful mission.
///
/// PHP: `energy_cost * skill_level * 10`.
#[must_use]
#[allow(clippy::cast_possible_truncation)]
pub fn mission_gold_reward(energy_cost: f64, skill_level: i32) -> i32 {
    (energy_cost * f64::from(skill_level) * 10.0) as i32
}

/// XP for a mission's profession component.
///
/// The exact formula depends on the profession type. Most use
/// `(index + 1) * 10` where index is the resource level. For jewellery
/// and alchemy it's `plan_level * 20` or `plan_level * 12`. For breeding
/// it's `20 * rand(1, 5)`.
///
/// PHP distributes XP: `exp / 2` to skill and `exp / 2` to stat.
#[must_use]
pub fn mission_base_xp(profession: CraftProfession, resource_level: i32) -> i32 {
    match profession {
        CraftProfession::Smelting
        | CraftProfession::Lumberjack
        | CraftProfession::Mining
        | CraftProfession::Herbalism => (resource_level + 1) * 10,
        CraftProfession::Jewellery | CraftProfession::Breeding => resource_level * 20,
        CraftProfession::Alchemy => resource_level * 12,
        CraftProfession::Carpentry | CraftProfession::Smith => resource_level * 10,
    }
}

/// Whether a loot roll awards a rare tool or plan drop.
///
/// PHP: roll in `[995, 1000]` out of `rand(1, 1000)`.
///
/// Returns the loot tier:
/// - `995` or `996` → new tool (level=1)
/// - `997` → better tool (level>1)
/// - `998` or `999` → plan (level=1)
/// - `1000` → better plan (level>1)
/// - anything else → no loot
#[must_use]
pub fn mission_loot_tier(roll_1_to_1000: i32) -> Option<MissionLootTier> {
    match roll_1_to_1000 {
        995 | 996 => Some(MissionLootTier::BasicTool),
        997 => Some(MissionLootTier::BetterTool),
        998 | 999 => Some(MissionLootTier::BasicPlan),
        1000 => Some(MissionLootTier::BetterPlan),
        _ => None,
    }
}

/// Rare loot categories from craftsman guild missions.
#[derive(Debug, Clone, Copy, PartialEq, Eq)]
pub enum MissionLootTier {
    /// Tool with level=1.
    BasicTool,
    /// Tool with level>1.
    BetterTool,
    /// Plan with level=1.
    BasicPlan,
    /// Plan with level>1.
    BetterPlan,
}

impl MissionLootTier {
    /// DB table to query for the loot item.
    #[must_use]
    pub fn table(self) -> &'static str {
        match self {
            Self::BasicTool | Self::BetterTool => "tools",
            Self::BasicPlan | Self::BetterPlan => "plans",
        }
    }

    /// SQL level condition for selecting the loot item.
    #[must_use]
    pub fn level_condition(self) -> &'static str {
        match self {
            Self::BasicTool | Self::BasicPlan => "=1",
            Self::BetterTool | Self::BetterPlan => ">1",
        }
    }
}

// ---------------------------------------------------------------------------
// Tests
// ---------------------------------------------------------------------------

#[cfg(test)]
mod tests {
    use super::*;

    // -- RingStat --

    #[test]
    fn ring_stat_db_keys() {
        assert_eq!(RingStat::Agility.db_key(), "agility");
        assert_eq!(RingStat::Wisdom.db_key(), "wisdom");
        assert_eq!(RingStat::Condition.db_key(), "condition");
    }

    // -- simple_ring_chance --

    #[test]
    fn simple_chance_low_skill() {
        // skill=0.5 → 50
        assert_eq!(simple_ring_chance(0.5), 50);
    }

    #[test]
    fn simple_chance_capped() {
        // skill=2.0 → 200, capped to 95
        assert_eq!(simple_ring_chance(2.0), 95);
    }

    // -- simple_ring_xp --

    #[test]
    fn simple_xp_non_craftsman() {
        assert_eq!(simple_ring_xp(5, false), 5);
    }

    #[test]
    fn simple_xp_craftsman() {
        assert_eq!(simple_ring_xp(5, true), 10);
    }

    // -- stat_ring_mineral_cost --

    #[test]
    fn mineral_cost_level_5() {
        let (a, c, m) = stat_ring_mineral_cost(5);
        assert_eq!(a, 5);
        assert_eq!(c, 3); // ceil(5/2) = 3
        assert_eq!(m, 2); // ceil(5/4) = 2
    }

    #[test]
    fn mineral_cost_level_4() {
        let (a, c, m) = stat_ring_mineral_cost(4);
        assert_eq!(a, 4);
        assert_eq!(c, 2); // ceil(4/2) = 2
        assert_eq!(m, 1); // ceil(4/4) = 1
    }

    // -- can_choose_stat --

    #[test]
    fn choose_stat_yes() {
        assert!(can_choose_stat(20.0, 5)); // 20 >= 5*4=20
    }

    #[test]
    fn choose_stat_no() {
        assert!(!can_choose_stat(19.0, 5)); // 19 < 20
    }

    // -- stat_ring_chance --

    #[test]
    fn stat_chance_basic() {
        // (50 + 30) / 5 * 50 = 800 → capped at 95
        assert_eq!(stat_ring_chance(50.0, 30.0, 5), 95);
    }

    #[test]
    fn stat_chance_low() {
        // (10 + 5) / 10 * 50 = 75
        assert_eq!(stat_ring_chance(10.0, 5.0, 10), 75);
    }

    // -- stat_ring_batch_size --

    #[test]
    fn batch_size_exact() {
        assert_eq!(stat_ring_batch_size(10, 5), 2);
    }

    #[test]
    fn batch_size_partial() {
        assert_eq!(stat_ring_batch_size(3, 5), 0);
    }

    // -- stat_ring_bonus --

    #[test]
    fn bonus_clamped_high() {
        // roll=100, stat=50: floor(100 + 10) = 110, max_bonus=5 → 5
        assert_eq!(stat_ring_bonus(100.0, 50.0, 5), 5);
    }

    #[test]
    fn bonus_clamped_low() {
        // roll=0, stat=0: floor(0 + 0) = 0, min 1
        assert_eq!(stat_ring_bonus(0.0, 0.0, 5), 1);
    }

    #[test]
    fn bonus_mid_range() {
        // roll=2, stat=10: floor(2 + 2) = 4, within [1, 10]
        assert_eq!(stat_ring_bonus(2.0, 10.0, 10), 4);
    }

    // -- stat_ring_sell_cost --

    #[test]
    fn sell_cost_100() {
        assert_eq!(stat_ring_sell_cost(100), 5);
    }

    #[test]
    fn sell_cost_rounds_up() {
        // 7 / 20 = 0.35 → ceil = 1
        assert_eq!(stat_ring_sell_cost(7), 1);
    }

    // -- stat_ring_xp --

    #[test]
    fn stat_xp_basic() {
        assert_eq!(stat_ring_xp(5, 3), 150); // 5 * 10 * 3
    }

    // -- special_prefix_for_stat --

    #[test]
    fn prefix_mapping() {
        assert_eq!(
            special_prefix_for_stat(RingStat::Agility),
            SpecialPrefix::Elven
        );
        assert_eq!(
            special_prefix_for_stat(RingStat::Strength),
            SpecialPrefix::Dwarven
        );
        assert_eq!(
            special_prefix_for_stat(RingStat::Intelligence),
            SpecialPrefix::Gnomish
        );
    }

    // -- special_ring_energy_cost --

    #[test]
    fn special_energy_level_10() {
        assert!((special_ring_energy_cost(10) - 15.0).abs() < f64::EPSILON);
    }

    // -- special_ring_meteor_cost --

    #[test]
    fn special_meteor_level_5() {
        assert_eq!(special_ring_meteor_cost(5), 3); // ceil(5/2) = 3
    }

    // -- special_ring_chance --

    #[test]
    fn special_chance_basic() {
        // floor((100 + 50) / 50 * 0.5) + 5 = floor(1.5) + 5 = 6
        assert_eq!(special_ring_chance(100.0, 50.0), 6);
    }

    #[test]
    fn special_chance_capped() {
        // floor((500 + 500) / 50 * 0.5) + 5 = floor(10) + 5 = 15
        assert_eq!(special_ring_chance(500.0, 500.0), 15);
    }

    #[test]
    fn special_chance_very_high() {
        // floor((1000 + 1000) / 50 * 0.5) + 5 = 25, capped to 15
        assert_eq!(special_ring_chance(1000.0, 1000.0), 15);
    }

    // -- special_ring_roll --

    #[test]
    fn roll_god() {
        assert_eq!(special_ring_roll(1, 1, 10), SpecialRingOutcome::God);
    }

    #[test]
    fn roll_special_on_1_no_coin() {
        // roll=1 but coin=2 → not god, but roll<=chance → Special
        assert_eq!(special_ring_roll(1, 2, 10), SpecialRingOutcome::Special);
    }

    #[test]
    fn roll_special() {
        assert_eq!(special_ring_roll(5, 2, 10), SpecialRingOutcome::Special);
    }

    #[test]
    fn roll_failed() {
        assert_eq!(special_ring_roll(50, 2, 10), SpecialRingOutcome::Failed);
    }

    // -- special_ring_power_multiplier --

    #[test]
    fn power_multiplier() {
        assert_eq!(special_ring_power_multiplier(SpecialRingOutcome::God), 4);
        assert_eq!(
            special_ring_power_multiplier(SpecialRingOutcome::Special),
            2
        );
        assert_eq!(special_ring_power_multiplier(SpecialRingOutcome::Failed), 0);
    }

    // -- special_ring_xp --

    #[test]
    fn special_xp_god() {
        assert_eq!(special_ring_xp(SpecialRingOutcome::God, 15.0), 750);
    }

    #[test]
    fn special_xp_special() {
        assert_eq!(special_ring_xp(SpecialRingOutcome::Special, 15.0), 375);
    }

    #[test]
    fn special_xp_failed() {
        assert_eq!(special_ring_xp(SpecialRingOutcome::Failed, 15.0), 2);
    }

    // -- special_ring_sell_cost --

    #[test]
    fn special_sell_cost() {
        assert_eq!(special_ring_sell_cost(100), 10); // ceil(100/10) = 10
    }

    // -- can_buy_plan --

    #[test]
    fn buy_plan_success() {
        assert!(can_buy_plan(2, true, false, 100, 5, 200, 10).is_ok());
    }

    #[test]
    fn buy_plan_non_craftsman_plan_1() {
        assert!(can_buy_plan(1, false, false, 50, 1, 200, 10).is_ok());
    }

    #[test]
    fn buy_plan_non_craftsman_plan_2() {
        assert_eq!(
            can_buy_plan(2, false, false, 100, 5, 200, 10).unwrap_err(),
            PlanBuyError::NotCraftsman
        );
    }

    #[test]
    fn buy_plan_already_owned() {
        assert_eq!(
            can_buy_plan(1, true, true, 50, 1, 200, 10).unwrap_err(),
            PlanBuyError::AlreadyOwned
        );
    }

    #[test]
    fn buy_plan_insufficient_gold() {
        assert_eq!(
            can_buy_plan(1, true, false, 100, 1, 50, 10).unwrap_err(),
            PlanBuyError::InsufficientGold {
                cost: 100,
                available: 50
            }
        );
    }

    #[test]
    fn buy_plan_skill_too_low() {
        assert_eq!(
            can_buy_plan(2, true, false, 100, 10, 200, 5).unwrap_err(),
            PlanBuyError::SkillTooLow {
                required: 10,
                actual: 5
            }
        );
    }

    // -- CraftProfession --

    #[test]
    fn profession_skill_keys() {
        assert_eq!(CraftProfession::Smelting.skill_key(), "smelting");
        assert_eq!(CraftProfession::Jewellery.skill_key(), "jewellry");
        assert_eq!(CraftProfession::Alchemy.skill_key(), "alchemy");
    }

    #[test]
    fn profession_xp_stats() {
        assert_eq!(CraftProfession::Smelting.xp_stat_key(), "condition");
        assert_eq!(CraftProfession::Smith.xp_stat_key(), "strength");
        assert_eq!(CraftProfession::Alchemy.xp_stat_key(), "wisdom");
    }

    // -- guild_registration_cost --

    #[test]
    fn registration_cost() {
        assert_eq!(guild_registration_cost(50), 50_000);
    }

    // -- mission_is_accident --

    #[test]
    fn accident_yes() {
        assert!(mission_is_accident(5));
    }

    #[test]
    fn accident_no() {
        assert!(!mission_is_accident(6));
    }

    // -- mission_accident_damage --

    #[test]
    fn accident_damage() {
        // ceil(100 / 100 * 25) = 25
        assert_eq!(mission_accident_damage(100, 25), 25);
    }

    #[test]
    fn accident_damage_rounds_up() {
        // ceil(150 / 100 * 10) = ceil(15) = 15
        assert_eq!(mission_accident_damage(150, 10), 15);
    }

    // -- mission_gold_reward --

    #[test]
    fn gold_reward() {
        // 5.0 * 10 * 10 = 500
        assert_eq!(mission_gold_reward(5.0, 10), 500);
    }

    // -- mission_base_xp --

    #[test]
    fn base_xp_smelting() {
        // (2 + 1) * 10 = 30
        assert_eq!(mission_base_xp(CraftProfession::Smelting, 2), 30);
    }

    #[test]
    fn base_xp_jewellery() {
        // 5 * 20 = 100
        assert_eq!(mission_base_xp(CraftProfession::Jewellery, 5), 100);
    }

    #[test]
    fn base_xp_alchemy() {
        // 3 * 12 = 36
        assert_eq!(mission_base_xp(CraftProfession::Alchemy, 3), 36);
    }

    // -- mission_loot_tier --

    #[test]
    fn loot_basic_tool() {
        assert_eq!(mission_loot_tier(995), Some(MissionLootTier::BasicTool));
        assert_eq!(mission_loot_tier(996), Some(MissionLootTier::BasicTool));
    }

    #[test]
    fn loot_better_tool() {
        assert_eq!(mission_loot_tier(997), Some(MissionLootTier::BetterTool));
    }

    #[test]
    fn loot_basic_plan() {
        assert_eq!(mission_loot_tier(998), Some(MissionLootTier::BasicPlan));
        assert_eq!(mission_loot_tier(999), Some(MissionLootTier::BasicPlan));
    }

    #[test]
    fn loot_better_plan() {
        assert_eq!(mission_loot_tier(1000), Some(MissionLootTier::BetterPlan));
    }

    #[test]
    fn loot_none() {
        assert_eq!(mission_loot_tier(500), None);
        assert_eq!(mission_loot_tier(994), None);
    }

    // -- MissionLootTier --

    #[test]
    fn loot_tier_table() {
        assert_eq!(MissionLootTier::BasicTool.table(), "tools");
        assert_eq!(MissionLootTier::BetterPlan.table(), "plans");
    }

    #[test]
    fn loot_tier_level_condition() {
        assert_eq!(MissionLootTier::BasicTool.level_condition(), "=1");
        assert_eq!(MissionLootTier::BetterTool.level_condition(), ">1");
    }
}
