//! Gathering, mining, lumberjack, smelting, and farm domain rules.
//!
//! # Systems
//!
//! ## Mountain mining (`kopalnia.php`)
//!
//! Location: Mountains. Player spends energy → per-unit RNG loop.
//! Possible outcomes per energy: nothing, crystals, adamantium, mithril
//! (stored as gold), diamonds (stored as gold), or cave-in (speed check,
//! fail = death).
//!
//! ## Mine digging (`mines.php`)
//!
//! Location: Altara. Player has mine deposits discovered by geologists.
//! Spends energy → extracts ore (copper/zinc/tin/iron/coal) using
//! mining skill + strength bonuses. Deposits are consumed.
//!
//! ## Lumberjack (`lumberjack.php`)
//!
//! Location: Forest. Requires lumber license (level 1–4). Player
//! selects wood type, spends energy → per-unit RNG loop. Possible
//! outcomes: wood, gold, tree-fall (injury/death), or nothing.
//!
//! ## Smelting (`smelter.php`)
//!
//! Location: Altara. Requires smelter level (1–5). Two modes:
//! - Ore smelting: combines ores + coal → metal bars
//! - Item smelting: melts equipment → recovers ~75% bars
//!
//! Both use smelting skill + condition stat for success rolls.
//!
//! ## Farm (`farm.php`)
//!
//! Location: Altara or Ardulith (any city). Herb plantation system
//! with land purchase, sowing, growing (age ticks), harvesting, and
//! drying herbs → seeds. Equipment (glasshouse, irrigation, creeper
//! supports) needed for advanced herbs.

use super::workshop::{self, WorkshopError};

// =========================================================================
// Mountain mining
// =========================================================================

/// Outcome of a single energy unit during mountain mining.
#[derive(Debug, Clone, PartialEq)]
pub enum MountainMineEvent {
    /// No yield this tick.
    Nothing,
    /// Found crystals.
    Crystals(i32),
    /// Found adamantium.
    Adamantium(i32),
    /// Found mithril.
    Mithril(i32),
    /// Found diamonds (converted to gold).
    Diamonds(i32),
    /// Cave-in! Player must pass a speed check or die.
    CaveIn,
}

/// Result of processing the full mountain mining session.
#[derive(Debug, Clone, Default)]
pub struct MountainMineResult {
    pub crystals: i32,
    pub adamantium: i32,
    pub mithril: i32,
    pub gold: i32,
    pub xp_strength: i32,
    pub xp_speed: i32,
    pub xp_mining: i32,
    pub player_died: bool,
}

/// Mining bonus multiplier: `1 + (mining_skill + strength) / 20`.
pub fn mountain_mine_bonus(mining_skill: i32, strength: i32) -> f64 {
    1.0 + f64::from(mining_skill + strength) / 20.0
}

/// Interpret a single RNG roll (1–10) into a mining event.
///
/// PHP mapping:
/// - 1–4: nothing
/// - 5: crystals = ceil(rand(1,20) * 1/8 * bonus)
/// - 6–7: adamantium = ceil(rand(1,20) * 1/5 * bonus)
/// - 8: mithril = ceil(rand(1,20) * 1/3 * bonus)
/// - 9: diamonds = ceil(rand(50,200) * bonus) → gold
/// - 10: cave-in
pub fn interpret_mountain_roll(roll: i32, sub_roll: i32, bonus: f64) -> MountainMineEvent {
    match roll {
        1..=4 => MountainMineEvent::Nothing,
        5 => {
            #[allow(clippy::cast_possible_truncation)]
            let amount = (f64::from(sub_roll) * (1.0 / 8.0) * bonus).ceil() as i32;
            MountainMineEvent::Crystals(amount.max(1))
        }
        6 | 7 => {
            #[allow(clippy::cast_possible_truncation)]
            let amount = (f64::from(sub_roll) * (1.0 / 5.0) * bonus).ceil() as i32;
            MountainMineEvent::Adamantium(amount.max(1))
        }
        8 => {
            #[allow(clippy::cast_possible_truncation)]
            let amount = (f64::from(sub_roll) * (1.0 / 3.0) * bonus).ceil() as i32;
            MountainMineEvent::Mithril(amount.max(1))
        }
        9 => {
            // sub_roll here should be rand(50, 200) range
            #[allow(clippy::cast_possible_truncation)]
            let amount = (f64::from(sub_roll) * bonus).ceil() as i32;
            MountainMineEvent::Diamonds(amount.max(1))
        }
        _ => MountainMineEvent::CaveIn,
    }
}

/// Whether the player survives a cave-in based on speed.
///
/// PHP: `rand(1, speed * 10) > rand(1, 200)`.
pub fn survives_cave_in(speed_roll: i32, difficulty_roll: i32) -> bool {
    speed_roll > difficulty_roll
}

/// Calculate XP split for mountain mining.
///
/// PHP: `exp = crystals + adamantium×2 + mithril×5` (approximate from code analysis).
/// Craftsman class doubles XP. Splits evenly into strength, speed, mining.
pub fn mountain_mine_xp(
    crystals: i32,
    adamantium: i32,
    mithril: i32,
    gold: i32,
    is_craftsman: bool,
) -> (i32, i32, i32) {
    // PHP uses: exp per iteration based on the value gained
    let base = crystals + adamantium * 2 + mithril * 3 + gold / 50;
    let total = if is_craftsman { base * 2 } else { base };
    let third = total / 3;
    (third, third, third)
}

// =========================================================================
// Mine ore digging
// =========================================================================

/// Ore types available in mines.
#[derive(Debug, Clone, Copy, PartialEq, Eq)]
pub enum OreType {
    Copper,
    Zinc,
    Tin,
    Iron,
    Coal,
}

impl OreType {
    pub fn parse(s: &str) -> Option<Self> {
        match s {
            "copper" => Some(Self::Copper),
            "zinc" => Some(Self::Zinc),
            "tin" => Some(Self::Tin),
            "iron" => Some(Self::Iron),
            "coal" => Some(Self::Coal),
            _ => None,
        }
    }

    pub fn as_str(self) -> &'static str {
        match self {
            Self::Copper => "copper",
            Self::Zinc => "zinc",
            Self::Tin => "tin",
            Self::Iron => "iron",
            Self::Coal => "coal",
        }
    }

    /// The output column in the `minerals` table.
    pub fn ore_column(self) -> &'static str {
        match self {
            Self::Copper => "copperore",
            Self::Zinc => "zincore",
            Self::Tin => "tinore",
            Self::Iron => "ironore",
            Self::Coal => "coal",
        }
    }

    /// Difficulty factor for this ore (used in dig calculations).
    fn difficulty(self) -> i32 {
        match self {
            Self::Copper | Self::Coal => 1,
            Self::Zinc => 2,
            Self::Tin => 3,
            Self::Iron => 4,
        }
    }
}

/// Result of digging ore from mines.
#[derive(Debug, Clone)]
pub struct MineDigResult {
    pub ore_type: OreType,
    pub ore_gained: i32,
    pub xp: i32,
}

/// Calculate ore gained from mining deposits.
///
/// PHP formula:
/// ```text
/// amount = ceil((rand(1,20) * energy / difficulty) * ((mining + strength) / 20)) - difficulty
/// ```
/// Capped by available deposit, minimum 1.
pub fn dig_ore(
    ore_type: OreType,
    energy: i32,
    mining_skill: i32,
    strength: i32,
    roll: i32,
    deposit_available: i32,
    is_craftsman: bool,
) -> MineDigResult {
    let difficulty = ore_type.difficulty();
    let bonus_factor = f64::from(mining_skill + strength) / 20.0;
    let raw =
        (f64::from(roll * energy) / f64::from(difficulty)) * bonus_factor - f64::from(difficulty);
    #[allow(clippy::cast_possible_truncation)]
    let mut amount = raw.ceil() as i32;
    amount = amount.max(1).min(deposit_available);

    let mut xp = (amount * difficulty) / 4;
    if is_craftsman {
        xp *= 2;
    }

    MineDigResult {
        ore_type,
        ore_gained: amount,
        xp,
    }
}

/// Cost to hire a geologist to search for deposits.
///
/// Returns `(gold_cost, mithril_cost, search_days)`.
pub fn geologist_search_cost(ore_type: OreType, size: GeologistSearchSize) -> (i32, i32, i32) {
    let base_cost: f64 = match ore_type {
        OreType::Coal => 0.75,
        OreType::Copper => 1.0,
        OreType::Zinc => 2.0,
        OreType::Tin => 3.0,
        OreType::Iron => 4.0,
    };

    let (gold_mult, mith_mult, days) = match size {
        GeologistSearchSize::Small => (500.0, 1.0, 1),
        GeologistSearchSize::Medium => (1000.0, 2.0, 2),
        GeologistSearchSize::Large => (1500.0, 3.0, 3),
    };

    #[allow(clippy::cast_possible_truncation)]
    let gold = (base_cost * gold_mult) as i32;
    #[allow(clippy::cast_possible_truncation)]
    let mithril = if base_cost < 1.0 {
        0
    } else {
        (base_cost * mith_mult).ceil() as i32
    };

    (gold, mithril, days)
}

/// Size of geologist search expedition.
#[derive(Debug, Clone, Copy, PartialEq, Eq)]
pub enum GeologistSearchSize {
    Small,
    Medium,
    Large,
}

impl GeologistSearchSize {
    pub fn parse(s: &str) -> Option<Self> {
        match s {
            "small" => Some(Self::Small),
            "medium" => Some(Self::Medium),
            "large" => Some(Self::Large),
            _ => None,
        }
    }
}

// =========================================================================
// Lumberjack
// =========================================================================

/// Wood types available for lumberjack harvesting.
#[derive(Debug, Clone, Copy, PartialEq, Eq)]
pub enum WoodType {
    Pine,
    Hazel,
    Yew,
    Elm,
}

impl WoodType {
    pub fn as_str(self) -> &'static str {
        match self {
            Self::Pine => "pine",
            Self::Hazel => "hazel",
            Self::Yew => "yew",
            Self::Elm => "elm",
        }
    }

    /// Minimum lumberjack license level required.
    pub fn required_level(self) -> i32 {
        match self {
            Self::Pine => 1,
            Self::Hazel => 2,
            Self::Yew => 3,
            Self::Elm => 4,
        }
    }
}

/// Outcome of a single energy unit during lumberjack work.
#[derive(Debug, Clone, PartialEq)]
pub enum LumberEvent {
    /// No yield this tick.
    Nothing,
    /// Harvested wood.
    Wood(i32),
    /// Found gold while working.
    Gold(i32),
    /// Tree fell on the player (50% chance of injury, can die).
    TreeFall,
}

/// Result of processing the full lumberjack session.
#[derive(Debug, Clone, Default)]
pub struct LumberResult {
    pub wood: i32,
    pub gold: i32,
    pub xp_lumberjack: i32,
    pub xp_strength: i32,
    pub player_died: bool,
}

/// Lumberjack bonus: `1 + (lumberjack_skill + strength) / 20`, capped at 30.
pub fn lumber_bonus(lumberjack_skill: i32, strength: i32) -> f64 {
    let raw = 1.0 + f64::from(lumberjack_skill + strength) / 20.0;
    raw.min(30.0)
}

/// Interpret a single RNG roll (1–8) into a lumber event.
///
/// PHP mapping:
/// - 1–4: nothing
/// - 5–6: wood (type-dependent amount, modified by bonus)
/// - 7: gold = rand(1, 100)
/// - 8: tree fall
pub fn interpret_lumber_roll(roll: i32, sub_roll: i32, bonus: f64) -> LumberEvent {
    match roll {
        1..=4 => LumberEvent::Nothing,
        5 | 6 => {
            #[allow(clippy::cast_possible_truncation)]
            let amount = (f64::from(sub_roll) * bonus / 5.0).ceil() as i32;
            LumberEvent::Wood(amount.max(1))
        }
        7 => LumberEvent::Gold(sub_roll.max(1)),
        _ => LumberEvent::TreeFall,
    }
}

/// Whether the player survives a tree fall.
///
/// PHP: 50% chance injury, then if already low HP → death.
pub fn survives_tree_fall(roll: i32) -> bool {
    roll > 50
}

/// Lumberjack license cost.
///
/// Returns `(gold_cost, mithril_cost)` for upgrading to the given level.
pub fn lumber_license_cost(current_level: i32) -> Option<(i32, i32)> {
    match current_level {
        0 => Some((1_000, 0)),
        1 => Some((2_000, 10)),
        2 => Some((10_000, 50)),
        3 => Some((50_000, 250)),
        _ => None, // Already max level
    }
}

// =========================================================================
// Smelting
// =========================================================================

/// Smelter upgrade costs.
pub fn smelter_upgrade_cost(current_level: i32) -> Option<i32> {
    match current_level {
        0 => Some(1_000),
        1 => Some(5_000),
        2 => Some(20_000),
        3 => Some(60_000),
        4 => Some(120_000),
        _ => None,
    }
}

/// Metal bar types produced by smelting.
#[derive(Debug, Clone, Copy, PartialEq, Eq)]
pub enum MetalBar {
    Copper,
    Bronze,
    Brass,
    Iron,
    Steel,
}

impl MetalBar {
    /// Smelter level required to produce this bar.
    pub fn required_level(self) -> i32 {
        match self {
            Self::Copper => 1,
            Self::Bronze => 2,
            Self::Brass => 3,
            Self::Iron => 4,
            Self::Steel => 5,
        }
    }

    /// DB column name in the minerals table.
    pub fn column(self) -> &'static str {
        match self {
            Self::Copper => "copper",
            Self::Bronze => "bronze",
            Self::Brass => "brass",
            Self::Iron => "iron",
            Self::Steel => "steel",
        }
    }

    /// Difficulty factor for smelting rolls.
    pub fn difficulty(self) -> i32 {
        match self {
            Self::Copper => 1,
            Self::Bronze => 2,
            Self::Brass => 3,
            Self::Iron => 5,
            Self::Steel => 8,
        }
    }

    pub fn parse(s: &str) -> Option<Self> {
        match s {
            "copper" => Some(Self::Copper),
            "bronze" => Some(Self::Bronze),
            "brass" => Some(Self::Brass),
            "iron" => Some(Self::Iron),
            "steel" => Some(Self::Steel),
            _ => None,
        }
    }
}

/// Recipe for smelting ore into bars.
///
/// Returns a list of `(ore_column, amount_per_bar)`.
pub fn smelt_recipe(bar: MetalBar) -> Vec<(&'static str, i32)> {
    match bar {
        MetalBar::Copper => vec![("copperore", 2), ("coal", 1)],
        MetalBar::Bronze => vec![("copperore", 1), ("tinore", 1), ("coal", 2)],
        MetalBar::Brass => vec![("copperore", 2), ("zincore", 1), ("coal", 2)],
        MetalBar::Iron => vec![("ironore", 2), ("coal", 3)],
        MetalBar::Steel => vec![("ironore", 3), ("coal", 7)],
    }
}

/// Energy cost per bar when smelting ore.
pub fn smelt_ore_energy(bar: MetalBar) -> f64 {
    f64::from(bar.difficulty()) / 10.0
}

/// Result of an ore-smelting session.
#[derive(Debug, Clone)]
pub struct SmeltOreResult {
    pub bars_produced: i32,
    pub xp: i32,
}

/// Simulate smelting ore for the given number of attempts.
///
/// Each attempt: `rand(1, (skill + condition) * 100)` vs `rand(1, diff * 100)`.
/// Success → 1 bar, critical success (roll * 2 > diff) → 2 bars. Fail → 0.
pub fn smelt_ore(
    bar: MetalBar,
    attempts: i32,
    _smelting_skill: i32,
    _condition: i32,
    rolls: &[(i32, i32)],
    is_craftsman: bool,
) -> SmeltOreResult {
    let diff = bar.difficulty() * 100;
    let mut bars = 0;

    #[allow(clippy::cast_sign_loss)]
    for &(skill_roll, diff_roll) in rolls.iter().take(attempts as usize) {
        if skill_roll > diff_roll {
            bars += 1;
            if skill_roll * 2 > diff {
                bars += 1;
            }
        }
    }

    let mut xp = bars * bar.difficulty() / 2;
    if is_craftsman {
        xp *= 2;
    }

    SmeltOreResult {
        bars_produced: bars,
        xp,
    }
}

/// Energy cost per item when smelting equipment back to bars.
pub fn smelt_item_energy(bar_index: usize, material_per_item: i32) -> f64 {
    let billets = [1, 2, 3, 5, 8];
    let factor = billets.get(bar_index).copied().unwrap_or(1);
    let raw = (f64::from(factor) / 10.0) * f64::from(material_per_item);
    (raw * 100.0).round() / 100.0
}

/// Coal cost for smelting equipment.
pub fn smelt_item_coal(material_per_item: i32, amount: i32) -> i32 {
    material_per_item * amount
}

/// Simulate melting items back to bars.
///
/// Each attempt: roll `(skill+condition)*100` vs `diff*100`.
/// Success → full material recovery, else partial (0–50%).
pub fn smelt_item(
    bar_index: usize,
    material_per_item: i32,
    amount: i32,
    _smelting_skill: i32,
    _condition: i32,
    rolls: &[(i32, i32, i32)],
    is_craftsman: bool,
) -> SmeltOreResult {
    let billets = [1, 2, 3, 5, 8];
    let mut total_bars = 0;

    #[allow(clippy::cast_sign_loss)]
    for &(skill_roll, diff_roll, partial_roll) in rolls.iter().take(amount as usize) {
        if skill_roll > diff_roll {
            total_bars += material_per_item;
        } else {
            let fraction = f64::from(partial_roll) / 100.0;
            #[allow(clippy::cast_possible_truncation)]
            let partial = (f64::from(material_per_item) * fraction).floor() as i32;
            total_bars += partial;
        }
    }

    let billets_factor = billets.get(bar_index).copied().unwrap_or(1);
    let mut xp = total_bars * billets_factor / 2;
    if is_craftsman {
        xp *= 2;
    }

    SmeltOreResult {
        bars_produced: total_bars,
        xp,
    }
}

// =========================================================================
// Farm
// =========================================================================

/// Herb types that can be grown on the farm.
#[derive(Debug, Clone, Copy, PartialEq, Eq)]
pub enum HerbType {
    Illani,
    Illanias,
    Nutari,
    Dynallca,
}

impl HerbType {
    pub fn as_str(self) -> &'static str {
        match self {
            Self::Illani => "illani",
            Self::Illanias => "illanias",
            Self::Nutari => "nutari",
            Self::Dynallca => "dynallca",
        }
    }

    pub fn seed_column(self) -> &'static str {
        match self {
            Self::Illani => "ilani_seeds",
            Self::Illanias => "illanias_seeds",
            Self::Nutari => "nutari_seeds",
            Self::Dynallca => "dynallca_seeds",
        }
    }

    pub fn parse(s: &str) -> Option<Self> {
        match s {
            "illani" => Some(Self::Illani),
            "illanias" => Some(Self::Illanias),
            "nutari" => Some(Self::Nutari),
            "dynallca" => Some(Self::Dynallca),
            _ => None,
        }
    }

    /// Equipment requirements: 0 = none, 1 = glasshouse, 2 = irrigation,
    /// 3 = creeper support.
    pub fn equipment_tier(self) -> i32 {
        match self {
            Self::Illani => 0,
            Self::Illanias => 1,
            Self::Nutari => 2,
            Self::Dynallca => 3,
        }
    }

    /// Harvest modifier (divides yield).
    pub fn harvest_modifier(self) -> f64 {
        match self {
            Self::Illani => 1.0,
            Self::Illanias => 1.5,
            Self::Nutari => 2.0,
            Self::Dynallca => 2.5,
        }
    }
}

/// Growth stage of herbs based on age (in game ticks/resets).
#[derive(Debug, Clone, Copy, PartialEq, Eq)]
pub enum GrowthStage {
    /// Age 0–1: just planted.
    Seeded,
    /// Age 2–6: young seedling.
    Seedling,
    /// Age 7–10: young plant.
    YoungPlant,
    /// Age 11–13: blooming.
    Blooming,
    /// Age 14–17: ready to harvest.
    ReadyToHarvest,
    /// Age 18–22: past prime.
    Wilting,
    /// Age 23+: dead.
    Withered,
}

impl GrowthStage {
    pub fn from_age(age: i32) -> Self {
        match age {
            0..=1 => Self::Seeded,
            2..=6 => Self::Seedling,
            7..=10 => Self::YoungPlant,
            11..=13 => Self::Blooming,
            14..=17 => Self::ReadyToHarvest,
            18..=22 => Self::Wilting,
            _ => Self::Withered,
        }
    }
}

/// Yield factor based on herb age (from PHP `$arrAge` array).
///
/// The array is: `[0, 1, 2, 2, 3, 3, 4, 4, 5, 6, 7, 8, 9, 10, 10, 10, 10, 9, 8, 7, 6, 5, 4, 3, 2, 1]`
/// Index = age - 1 (only valid for age >= 1).
pub fn herb_age_yield_factor(age: i32) -> i32 {
    const YIELDS: [i32; 26] = [
        0, 1, 2, 2, 3, 3, 4, 4, 5, 6, 7, 8, 9, 10, 10, 10, 10, 9, 8, 7, 6, 5, 4, 3, 2, 1,
    ];
    #[allow(clippy::cast_sign_loss)]
    let idx = (age - 1).max(0) as usize;
    YIELDS.get(idx).copied().unwrap_or(0)
}

/// Calculate herb harvest yield.
///
/// PHP formula:
/// ```text
/// factor = ceil((agility + herbalism) / 10)
/// amount = floor((age_yield * plots / herb_modifier) * factor)
/// amount = floor(amount + (amount * rand_bonus))
/// ```
/// where `rand_bonus` is a float in `[-0.15, 0.15]`.
pub fn harvest_herbs(
    herb: HerbType,
    age: i32,
    plots: i32,
    agility: i32,
    herbalism_skill: i32,
    rand_bonus_percent: i32,
) -> i32 {
    if age < 1 {
        return 0;
    }
    let age_yield = herb_age_yield_factor(age);
    if age_yield == 0 {
        return 0;
    }

    let factor = ((f64::from(agility) + f64::from(herbalism_skill)) / 10.0).ceil();
    let base = (f64::from(age_yield * plots) / herb.harvest_modifier()) * factor;
    let bonus = f64::from(rand_bonus_percent) / 100.0;
    #[allow(clippy::cast_possible_truncation)]
    let amount = (base + base * bonus).floor() as i32;
    amount.max(0)
}

/// Energy cost to harvest from plots.
pub fn harvest_energy(plots: i32) -> f64 {
    f64::from(plots) * 1.5
}

/// XP from harvesting (only for age > 3).
pub fn harvest_xp(herbs_collected: i32, age: i32, is_craftsman: bool) -> i32 {
    if age <= 3 {
        return 0;
    }
    let base = herbs_collected * 2;
    if is_craftsman { base * 2 } else { base }
}

/// Energy cost to sow seeds.
pub fn sow_energy(plots: i32) -> f64 {
    f64::from(plots) * 0.2
}

/// XP from sowing.
pub fn sow_xp(plots: i32, is_craftsman: bool) -> i32 {
    let base = plots * 5;
    if is_craftsman { base * 2 } else { base }
}

/// Drying herbs: 10 herbs → 1 seed packet, 0.5 energy per packet.
/// Roll per packet: 95% chance to succeed.
pub fn dry_herbs_result(packets_requested: i32, rolls: &[i32], is_craftsman: bool) -> (i32, i32) {
    let mut seeds = 0;
    #[allow(clippy::cast_sign_loss)]
    for &roll in rolls.iter().take(packets_requested as usize) {
        if roll > 5 {
            seeds += 1;
        }
    }
    let mut xp = seeds;
    if is_craftsman {
        xp *= 2;
    }
    (seeds, xp)
}

/// Herbs needed per seed packet.
pub const HERBS_PER_SEED_PACKET: i32 = 10;
/// Energy per seed packet for drying.
pub const DRY_ENERGY_PER_PACKET: f64 = 0.5;

/// Farm land upgrade cost in mithril.
///
/// PHP: first purchase = 20 mithril. Additional lands cost depends on total:
///
/// - 1–10: 2 × `current_total` per new land
/// - 11–20: 5 × `current_total`
/// - 21–30: 10 × `current_total`
/// - 31+: 15 × `current_total`
pub fn farm_land_cost(current_lands: i32, additional: i32) -> i32 {
    if current_lands == 0 && additional >= 1 {
        return 20; // Initial purchase is fixed 20 mithril
    }
    let mut cost = 0;
    for offset in 0..additional {
        let current = current_lands + offset;
        let price_per_unit = match current {
            0..=10 => 2,
            11..=20 => 5,
            21..=30 => 10,
            _ => 15,
        };
        cost += price_per_unit * current;
    }
    cost
}

/// Farm equipment cost: 1000 gold per unit for glasshouse, irrigation, or creeper.
pub const FARM_EQUIPMENT_GOLD_PER_UNIT: i32 = 1000;

// =========================================================================
// Shared validation helpers
// =========================================================================

/// Validate that the player is alive (HP > 0).
pub fn check_alive(hp: i32) -> Result<(), WorkshopError> {
    if hp < 1 {
        Err(WorkshopError::InvalidAction(
            "Cannot perform action while dead".to_owned(),
        ))
    } else {
        Ok(())
    }
}

/// Validate location for mountain mining.
pub fn check_mountains(location: &str) -> Result<(), WorkshopError> {
    workshop::check_location(location, "Mountains")
}

/// Validate location for mine digging.
pub fn check_altara(location: &str) -> Result<(), WorkshopError> {
    workshop::check_location(location, "Altara")
}

/// Validate location for lumberjack.
pub fn check_forest(location: &str) -> Result<(), WorkshopError> {
    workshop::check_location(location, "Forest")
}

/// Validate location for lumbermill.
pub fn check_ardulith(location: &str) -> Result<(), WorkshopError> {
    workshop::check_location(location, "Ardulith")
}

/// Validate that player is in a city (farm).
pub fn check_city(location: &str) -> Result<(), WorkshopError> {
    if location != "Altara" && location != "Ardulith" {
        return Err(WorkshopError::WrongLocation {
            required: "Altara or Ardulith".to_owned(),
            actual: location.to_owned(),
        });
    }
    Ok(())
}

/// Validate lumber license level for a wood type.
pub fn check_lumber_license(current_level: i32, wood: WoodType) -> Result<(), WorkshopError> {
    let required = wood.required_level();
    if current_level < required {
        return Err(WorkshopError::SkillTooLow {
            required,
            actual: current_level,
        });
    }
    Ok(())
}

/// Validate smelter level for a bar type.
pub fn check_smelter_level(current_level: i32, bar: MetalBar) -> Result<(), WorkshopError> {
    let required = bar.required_level();
    if current_level < required {
        return Err(WorkshopError::SkillTooLow {
            required,
            actual: current_level,
        });
    }
    Ok(())
}

// =========================================================================
// Tests
// =========================================================================

#[cfg(test)]
mod tests {
    use super::*;

    #[test]
    fn mountain_mine_bonus_calculation() {
        // mining=10, strength=10 → 1 + 20/20 = 2.0
        assert!((mountain_mine_bonus(10, 10) - 2.0).abs() < f64::EPSILON);
        // mining=0, strength=0 → 1.0
        assert!((mountain_mine_bonus(0, 0) - 1.0).abs() < f64::EPSILON);
    }

    #[test]
    fn mountain_roll_nothing_for_low() {
        assert_eq!(
            interpret_mountain_roll(1, 10, 1.0),
            MountainMineEvent::Nothing
        );
        assert_eq!(
            interpret_mountain_roll(4, 10, 1.0),
            MountainMineEvent::Nothing
        );
    }

    #[test]
    fn mountain_roll_crystals() {
        // roll=5, sub=16, bonus=2.0 → ceil(16 * 0.125 * 2.0) = ceil(4.0) = 4
        assert_eq!(
            interpret_mountain_roll(5, 16, 2.0),
            MountainMineEvent::Crystals(4)
        );
    }

    #[test]
    fn mountain_roll_adamantium() {
        // roll=6, sub=10, bonus=1.0 → ceil(10 * 0.2 * 1.0) = 2
        assert_eq!(
            interpret_mountain_roll(6, 10, 1.0),
            MountainMineEvent::Adamantium(2)
        );
    }

    #[test]
    fn mountain_roll_mithril() {
        // roll=8, sub=9, bonus=1.0 → ceil(9 * 0.333 * 1.0) = ceil(3.0) = 3
        assert_eq!(
            interpret_mountain_roll(8, 9, 1.0),
            MountainMineEvent::Mithril(3)
        );
    }

    #[test]
    fn mountain_roll_diamonds() {
        // roll=9, sub=100, bonus=1.5 → ceil(100 * 1.5) = 150
        assert_eq!(
            interpret_mountain_roll(9, 100, 1.5),
            MountainMineEvent::Diamonds(150)
        );
    }

    #[test]
    fn mountain_roll_cave_in() {
        assert_eq!(
            interpret_mountain_roll(10, 0, 1.0),
            MountainMineEvent::CaveIn
        );
    }

    #[test]
    fn cave_in_survival() {
        assert!(survives_cave_in(201, 200));
        assert!(!survives_cave_in(100, 200));
    }

    #[test]
    fn ore_type_round_trip() {
        for s in &["copper", "zinc", "tin", "iron", "coal"] {
            let o = OreType::parse(s).unwrap();
            assert_eq!(o.as_str(), *s);
        }
    }

    #[test]
    fn dig_ore_minimum_one() {
        let result = dig_ore(OreType::Iron, 1, 1, 1, 1, 100, false);
        assert!(result.ore_gained >= 1);
    }

    #[test]
    fn dig_ore_capped_by_deposit() {
        let result = dig_ore(OreType::Copper, 100, 100, 100, 20, 5, false);
        assert_eq!(result.ore_gained, 5);
    }

    #[test]
    fn geologist_cost_coal_small() {
        let (gold, mith, days) = geologist_search_cost(OreType::Coal, GeologistSearchSize::Small);
        assert_eq!(gold, 375); // 0.75 * 500
        assert_eq!(mith, 0); // base < 1
        assert_eq!(days, 1);
    }

    #[test]
    fn geologist_cost_iron_large() {
        let (gold, mith, days) = geologist_search_cost(OreType::Iron, GeologistSearchSize::Large);
        assert_eq!(gold, 6000); // 4.0 * 1500
        assert_eq!(mith, 12); // ceil(4.0 * 3.0)
        assert_eq!(days, 3);
    }

    #[test]
    fn lumber_license_costs() {
        assert_eq!(lumber_license_cost(0), Some((1_000, 0)));
        assert_eq!(lumber_license_cost(1), Some((2_000, 10)));
        assert_eq!(lumber_license_cost(2), Some((10_000, 50)));
        assert_eq!(lumber_license_cost(3), Some((50_000, 250)));
        assert_eq!(lumber_license_cost(4), None);
    }

    #[test]
    fn lumber_bonus_cap() {
        let b = lumber_bonus(500, 500);
        assert!((b - 30.0).abs() < f64::EPSILON);
    }

    #[test]
    fn lumber_roll_nothing_and_wood() {
        assert_eq!(interpret_lumber_roll(1, 10, 1.0), LumberEvent::Nothing);
        // roll=5, sub=10, bonus=2.0 → ceil(10*2.0/5) = 4
        assert_eq!(interpret_lumber_roll(5, 10, 2.0), LumberEvent::Wood(4));
    }

    #[test]
    fn lumber_roll_gold_and_tree_fall() {
        assert_eq!(interpret_lumber_roll(7, 50, 1.0), LumberEvent::Gold(50));
        assert_eq!(interpret_lumber_roll(8, 0, 1.0), LumberEvent::TreeFall);
    }

    #[test]
    fn smelter_upgrade_costs() {
        assert_eq!(smelter_upgrade_cost(0), Some(1_000));
        assert_eq!(smelter_upgrade_cost(4), Some(120_000));
        assert_eq!(smelter_upgrade_cost(5), None);
    }

    #[test]
    fn smelt_recipe_copper() {
        let r = smelt_recipe(MetalBar::Copper);
        assert_eq!(r, vec![("copperore", 2), ("coal", 1)]);
    }

    #[test]
    fn smelt_recipe_steel() {
        let r = smelt_recipe(MetalBar::Steel);
        assert_eq!(r, vec![("ironore", 3), ("coal", 7)]);
    }

    #[test]
    fn smelt_ore_energy_costs() {
        assert!((smelt_ore_energy(MetalBar::Copper) - 0.1).abs() < f64::EPSILON);
        assert!((smelt_ore_energy(MetalBar::Steel) - 0.8).abs() < f64::EPSILON);
    }

    #[test]
    fn smelt_ore_success() {
        // All succeed with high roll, diff = 100 for copper
        let rolls = vec![(500, 50), (500, 50), (500, 50)];
        let result = smelt_ore(MetalBar::Copper, 3, 5, 5, &rolls, false);
        // Each success: 1 bar + possibly 1 crit (500*2=1000 > 100) → 2 bars each
        assert_eq!(result.bars_produced, 6);
    }

    #[test]
    fn smelt_ore_failure() {
        // All fail
        let rolls = vec![(1, 100), (1, 100)];
        let result = smelt_ore(MetalBar::Copper, 2, 1, 1, &rolls, false);
        assert_eq!(result.bars_produced, 0);
    }

    #[test]
    fn herb_growth_stages() {
        assert_eq!(GrowthStage::from_age(0), GrowthStage::Seeded);
        assert_eq!(GrowthStage::from_age(5), GrowthStage::Seedling);
        assert_eq!(GrowthStage::from_age(10), GrowthStage::YoungPlant);
        assert_eq!(GrowthStage::from_age(12), GrowthStage::Blooming);
        assert_eq!(GrowthStage::from_age(15), GrowthStage::ReadyToHarvest);
        assert_eq!(GrowthStage::from_age(20), GrowthStage::Wilting);
        assert_eq!(GrowthStage::from_age(25), GrowthStage::Withered);
    }

    #[test]
    fn herb_yield_at_peak() {
        // At age 14–17, yield factor is 10 (idx 13..=16)
        assert_eq!(herb_age_yield_factor(14), 10);
        assert_eq!(herb_age_yield_factor(17), 10);
        // age 18 starts declining
        assert_eq!(herb_age_yield_factor(18), 9);
    }

    #[test]
    fn herb_yield_withered() {
        assert_eq!(herb_age_yield_factor(27), 0);
    }

    #[test]
    fn harvest_herbs_basic() {
        // age=15 (yield=10), 5 plots, agility=10, herbalism=10, no bonus
        let amount = harvest_herbs(HerbType::Illani, 15, 5, 10, 10, 0);
        // factor = ceil(20/10) = 2, base = (10*5/1.0)*2 = 100
        assert_eq!(amount, 100);
    }

    #[test]
    fn harvest_herbs_zero_at_age_zero() {
        assert_eq!(harvest_herbs(HerbType::Illani, 0, 5, 10, 10, 0), 0);
    }

    #[test]
    fn dry_herbs_all_succeed() {
        let rolls: Vec<i32> = vec![10, 20, 30, 50, 90]; // All > 5
        let (seeds, xp) = dry_herbs_result(5, &rolls, false);
        assert_eq!(seeds, 5);
        assert_eq!(xp, 5);
    }

    #[test]
    fn dry_herbs_some_fail() {
        let rolls: Vec<i32> = vec![3, 4, 10, 1, 50]; // Three fail (≤5), two succeed
        let (seeds, _) = dry_herbs_result(5, &rolls, false);
        assert_eq!(seeds, 2);
    }

    #[test]
    fn farm_land_cost_initial() {
        assert_eq!(farm_land_cost(0, 1), 20);
    }

    #[test]
    fn farm_land_cost_additional() {
        // current=5, add 1 → price_per_unit=2, cost=2*5=10
        assert_eq!(farm_land_cost(5, 1), 10);
    }

    #[test]
    fn farm_land_cost_tier_boundary() {
        // current=10, add 1 → price_per_unit=2, cost=2*10=20
        assert_eq!(farm_land_cost(10, 1), 20);
        // current=11, add 1 → price_per_unit=5, cost=5*11=55
        assert_eq!(farm_land_cost(11, 1), 55);
    }

    #[test]
    fn check_alive_when_dead() {
        assert!(check_alive(0).is_err());
        assert!(check_alive(1).is_ok());
    }

    #[test]
    fn check_location_helpers() {
        assert!(check_mountains("Mountains").is_ok());
        assert!(check_mountains("Altara").is_err());
        assert!(check_altara("Altara").is_ok());
        assert!(check_forest("Forest").is_ok());
        assert!(check_ardulith("Ardulith").is_ok());
        assert!(check_city("Altara").is_ok());
        assert!(check_city("Ardulith").is_ok());
        assert!(check_city("Mountains").is_err());
    }

    #[test]
    fn smelter_level_checks() {
        assert!(check_smelter_level(1, MetalBar::Copper).is_ok());
        assert!(check_smelter_level(0, MetalBar::Copper).is_err());
        assert!(check_smelter_level(4, MetalBar::Iron).is_ok());
        assert!(check_smelter_level(5, MetalBar::Steel).is_ok());
    }

    #[test]
    fn lumber_license_checks() {
        assert!(check_lumber_license(1, WoodType::Pine).is_ok());
        assert!(check_lumber_license(0, WoodType::Pine).is_err());
        assert!(check_lumber_license(4, WoodType::Elm).is_ok());
    }
}
