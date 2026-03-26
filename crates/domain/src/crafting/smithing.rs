//! Smithing, armorer, and weapon production rules.
//!
//! The blacksmith (`kowal.php`) system covers:
//! - Purchasing item plans
//! - Crafting normal items (weapons, armor, shields, helmets, plate legs, tools)
//! - Crafting elite items (craftsman-only, using monster loot)
//! - Building astral constructions
//!
//! ## Item types
//!
//! | Code | Category     | Base power          | Base agility           |
//! |------|------------- |---------------------|------------------------|
//! | `W`  | Weapon       | level               | 0                      |
//! | `A`  | Armor        | level * 3           | level / 2              |
//! | `H`  | Helmet       | level               | 0                      |
//! | `L`  | Plate legs   | level               | level / 5              |
//! | `S`  | Shield       | level               | 0                      |
//! | `E`  | Tool         | 10 + level          | 0                      |
//!
//! ## Mineral tiers
//!
//! | Index | Mineral  | Normal durability (W/A) | Normal durability (other) | Normal max-bonus |
//! |-------|----------|-------------------------|---------------------------|------------------|
//! | 0     | Copper   | 40                      | 20                        | 6                |
//! | 1     | Bronze   | 80                      | 40                        | 10               |
//! | 2     | Brass    | 160                     | 80                        | 14               |
//! | 3     | Iron     | 320                     | 160                       | 17               |
//! | 4     | Steel    | 640                     | 320                       | 20               |

// ---------------------------------------------------------------------------
// Item type
// ---------------------------------------------------------------------------

/// Smithing item category.
#[derive(Debug, Clone, Copy, PartialEq, Eq)]
pub enum SmithItemType {
    Weapon,
    Armor,
    Helmet,
    PlateLeg,
    Shield,
    Tool,
}

impl SmithItemType {
    pub fn from_db(s: &str) -> Option<Self> {
        match s {
            "W" => Some(Self::Weapon),
            "A" => Some(Self::Armor),
            "H" => Some(Self::Helmet),
            "L" => Some(Self::PlateLeg),
            "S" => Some(Self::Shield),
            "E" => Some(Self::Tool),
            _ => None,
        }
    }

    pub fn as_db(&self) -> &'static str {
        match self {
            Self::Weapon => "W",
            Self::Armor => "A",
            Self::Helmet => "H",
            Self::PlateLeg => "L",
            Self::Shield => "S",
            Self::Tool => "E",
        }
    }
}

// ---------------------------------------------------------------------------
// Mineral tier
// ---------------------------------------------------------------------------

/// Mineral index (0 = copper .. 4 = steel).
#[derive(Debug, Clone, Copy, PartialEq, Eq)]
pub enum Mineral {
    Copper = 0,
    Bronze = 1,
    Brass = 2,
    Iron = 3,
    Steel = 4,
}

impl Mineral {
    pub fn from_key(s: &str) -> Option<Self> {
        match s {
            "copper" => Some(Self::Copper),
            "bronze" => Some(Self::Bronze),
            "brass" => Some(Self::Brass),
            "iron" => Some(Self::Iron),
            "steel" => Some(Self::Steel),
            _ => None,
        }
    }

    pub fn as_key(&self) -> &'static str {
        match self {
            Self::Copper => "copper",
            Self::Bronze => "bronze",
            Self::Brass => "brass",
            Self::Iron => "iron",
            Self::Steel => "steel",
        }
    }

    fn index(self) -> usize {
        self as usize
    }
}

// ---------------------------------------------------------------------------
// Base stats for normal items
// ---------------------------------------------------------------------------

/// Normal item durability per mineral tier.
///
/// Weapons and armor use higher durability values than other items.
const DURABILITY_HIGH: [i32; 5] = [40, 80, 160, 320, 640];
const DURABILITY_LOW: [i32; 5] = [20, 40, 80, 160, 320];
const DURABILITY_TOOL: [i32; 5] = [10, 15, 20, 25, 30];

/// Normal item max-bonus multiplier per mineral tier.
const MAX_BONUS_NORMAL: [i32; 5] = [6, 10, 14, 17, 20];
const MAX_BONUS_TOOL: [i32; 5] = [2, 4, 6, 8, 10];

/// Elite item max-bonus multiplier per mineral tier.
const MAX_BONUS_ELITE: [i32; 5] = [21, 25, 30, 35, 40];

/// Elite item durability (weapons/armor vs other).
const DURABILITY_ELITE_HIGH: [i32; 5] = [50, 90, 170, 330, 650];
const DURABILITY_ELITE_LOW: [i32; 5] = [30, 50, 90, 170, 330];

/// Repair cost multiplier per mineral tier.
const REPAIR_TIER: [i32; 5] = [1, 4, 16, 64, 256];

/// Base stats for a normal crafted item.
#[derive(Debug, Clone, PartialEq, Eq)]
pub struct BaseItemStats {
    pub power: i32,
    pub agility: i32,
    pub durability: i32,
    pub repair_cost: i32,
    pub sell_cost: i32,
}

/// Compute base stats for a normal (non-elite) crafted item.
///
/// PHP: power, agility, durability, repair cost all depend on item type,
/// plan level, mineral tier, and plan cost.
pub fn normal_base_stats(
    item_type: SmithItemType,
    plan_level: i32,
    mineral: Mineral,
    plan_cost: i32,
) -> BaseItemStats {
    let mi = mineral.index();

    let (power, agility) = match item_type {
        SmithItemType::Armor => (plan_level * 3, plan_level / 2),
        SmithItemType::PlateLeg => (plan_level, plan_level / 5),
        SmithItemType::Tool => (10 + plan_level, 0),
        _ => (plan_level, 0),
    };

    let durability = match item_type {
        SmithItemType::Weapon | SmithItemType::Armor => DURABILITY_HIGH[mi],
        SmithItemType::Tool => DURABILITY_TOOL[mi],
        _ => DURABILITY_LOW[mi],
    };

    let repair_mult = match item_type {
        SmithItemType::Weapon | SmithItemType::Armor => 2,
        SmithItemType::Tool => {
            // PHP: (level + 20) * repair_tier
            // Special formula—return early.
            return BaseItemStats {
                power,
                agility,
                durability,
                repair_cost: (plan_level + 20) * REPAIR_TIER[mi],
                sell_cost: (plan_cost + 19) / 20, // ceil(cost / 20)
            };
        }
        _ => 1,
    };

    BaseItemStats {
        power,
        agility,
        durability,
        repair_cost: plan_level * REPAIR_TIER[mi] * repair_mult,
        sell_cost: (plan_cost + 19) / 20, // ceil(cost / 20)
    }
}

/// Compute base stats for an elite crafted item.
pub fn elite_base_stats(
    item_type: SmithItemType,
    plan_level: i32,
    mineral: Mineral,
    plan_cost: i32,
) -> BaseItemStats {
    let mi = mineral.index();

    let (power, agility) = match item_type {
        SmithItemType::Armor => (plan_level * 3, plan_level / 2),
        SmithItemType::PlateLeg => (plan_level, plan_level / 5),
        _ => (plan_level, 0),
    };

    let durability = match item_type {
        SmithItemType::Weapon | SmithItemType::Armor => DURABILITY_ELITE_HIGH[mi],
        _ => DURABILITY_ELITE_LOW[mi],
    };

    let repair_cost = match item_type {
        SmithItemType::Weapon | SmithItemType::Armor => plan_level * REPAIR_TIER[mi] * 2,
        _ => plan_level * REPAIR_TIER[mi],
    };

    BaseItemStats {
        power,
        agility,
        durability,
        repair_cost,
        sell_cost: (plan_cost + 199) / 200, // ceil(cost / 200)
    }
}

// ---------------------------------------------------------------------------
// Success chance
// ---------------------------------------------------------------------------

/// Normal crafting success chance (percentage, 1–95).
///
/// PHP: `(50 - max_bonus[mineral]) * smith_skill / plan_level`, capped at 95.
#[allow(clippy::cast_possible_truncation)]
pub fn normal_success_chance(smith_skill: f64, plan_level: i32, mineral: Mineral) -> i32 {
    let max_bonus = MAX_BONUS_NORMAL[mineral.index()];
    let chance = f64::from(50 - max_bonus) * smith_skill / f64::from(plan_level);
    (chance as i32).clamp(1, 95)
}

/// Normal crafting success chance for tools.
#[allow(clippy::cast_possible_truncation)]
pub fn normal_tool_success_chance(smith_skill: f64, plan_level: i32, mineral: Mineral) -> i32 {
    let max_bonus = MAX_BONUS_TOOL[mineral.index()];
    let chance = f64::from(50 - max_bonus) * smith_skill / f64::from(plan_level);
    (chance as i32).clamp(1, 95)
}

/// Elite crafting success chance (percentage, 1–90).
///
/// PHP: `smith_skill / plan_level`, capped at 90.
#[allow(clippy::cast_possible_truncation)]
pub fn elite_success_chance(smith_skill: f64, plan_level: i32) -> i32 {
    let chance = smith_skill / f64::from(plan_level);
    (chance as i32).clamp(1, 90)
}

// ---------------------------------------------------------------------------
// Crafting skill bonus type
// ---------------------------------------------------------------------------

/// Which bonus skill applies for a given item type.
pub fn smith_bonus_key(item_type: SmithItemType) -> &'static str {
    match item_type {
        SmithItemType::Weapon => "weaponsmith",
        SmithItemType::Armor => "armorsmith",
        SmithItemType::Helmet => "helmsmith",
        SmithItemType::PlateLeg => "legsmith",
        SmithItemType::Shield => "shieldsmith",
        SmithItemType::Tool => "toolsmith",
    }
}

// ---------------------------------------------------------------------------
// Special item quality (normal crafting)
// ---------------------------------------------------------------------------

/// The quality tier rolled for a special (enhanced) normal item.
#[derive(Debug, Clone, Copy, PartialEq, Eq)]
pub enum SpecialQuality {
    /// Dragon prefix: bonus to power.
    Dragon,
    /// Dwarven prefix: bonus to durability. Craftsman-only.
    Dwarven,
    /// Elven prefix: bonus to agility (less penalty). Craftsman-only.
    Elven,
    /// Dragon + Dwarven combined. Craftsman-only.
    DragonDwarven,
    /// Elven + Dwarven combined. Craftsman-only.
    ElvenDwarven,
}

/// Determine whether a crafted item gets special quality.
///
/// PHP: If `roll2 < 21`, the item is special. Then `roll3` (1–101)
/// determines which prefix. Craftsmen get a reduced roll2 threshold
/// via skill bonus.
///
/// Returns `None` if not special, otherwise the quality tier.
pub fn special_quality_roll(
    roll2_1_to_100: i32,
    roll3_1_to_101: i32,
    is_craftsman: bool,
    item_type: SmithItemType,
) -> Option<SpecialQuality> {
    if roll2_1_to_100 >= 21 {
        return None;
    }

    // Armor and plate legs use different prefix assignment than weapons/shields/etc.
    let is_armor_family = item_type == SmithItemType::Armor || item_type == SmithItemType::PlateLeg;

    if is_armor_family {
        match roll3_1_to_101 {
            r if r < 34 => Some(SpecialQuality::Dragon),
            34 if is_craftsman => Some(SpecialQuality::DragonDwarven),
            35..=67 if is_craftsman => Some(SpecialQuality::Dwarven),
            68 if is_craftsman => Some(SpecialQuality::ElvenDwarven),
            r if r > 68 && is_craftsman => Some(SpecialQuality::Elven),
            _ => None,
        }
    } else {
        match roll3_1_to_101 {
            r if r < 51 => Some(SpecialQuality::Dragon),
            51 if is_craftsman => Some(SpecialQuality::DragonDwarven),
            r if r > 51 && is_craftsman && item_type != SmithItemType::Tool => {
                Some(SpecialQuality::Dwarven)
            }
            _ => None,
        }
    }
}

/// Inputs for computing special item bonuses.
#[derive(Debug, Clone)]
pub struct SpecialBonusInputs {
    /// `rand(1, ceil(smith_skill))` — the random item bonus roll.
    pub item_bonus_roll: i32,
    /// Player's strength stat (derived/blessed).
    pub strength_stat: f64,
    /// Player's intelligence stat (derived/blessed).
    pub intelligence_stat: f64,
    /// Player's agility stat (derived/blessed).
    pub agility_stat: f64,
}

/// Computed adjustments for a special-quality item.
#[derive(Debug, Clone, PartialEq, Eq)]
pub struct SpecialBonusResult {
    /// Additional power (added to base).
    pub power_bonus: i32,
    /// Additional durability (added to base).
    pub durability_bonus: i32,
    /// Agility adjustment. For armor-family: negative means better (less penalty).
    /// The raw value produced should be subtracted from the base agility.
    pub agility_bonus: i32,
}

/// Compute the stat bonuses for a special-quality normal item.
///
/// Encodes the PHP logic for Dragon/Dwarven/Elven bonuses with
/// per-type max-bonus capping.
#[allow(clippy::cast_possible_truncation)]
pub fn compute_special_bonus(
    quality: SpecialQuality,
    item_type: SmithItemType,
    base: &BaseItemStats,
    mineral: Mineral,
    inputs: &SpecialBonusInputs,
) -> SpecialBonusResult {
    let mi = mineral.index();
    let max_bonus_arr = if item_type == SmithItemType::Tool {
        &MAX_BONUS_TOOL
    } else {
        &MAX_BONUS_NORMAL
    };

    // For armor type with Dragon quality, item bonus is doubled.
    let item_bonus = if item_type == SmithItemType::Armor
        && matches!(
            quality,
            SpecialQuality::Dragon | SpecialQuality::DragonDwarven
        ) {
        inputs.item_bonus_roll * 2
    } else {
        inputs.item_bonus_roll
    };

    let mut power_bonus = 0;
    let mut durability_bonus = 0;
    let mut agility_bonus = 0;

    let compute_power = |ib: i32| -> i32 {
        let raw = ib + (inputs.strength_stat / 5.0) as i32;
        let cap = max_bonus_arr[mi] * base.power;
        raw.min(cap)
    };

    let compute_durability = |ib: i32| -> i32 {
        let raw = ib + (inputs.intelligence_stat / 5.0) as i32;
        let cap = base.durability * 10;
        raw.min(cap)
    };

    let compute_agility = |ib: i32| -> i32 {
        let raw = ib + (inputs.agility_stat / 5.0) as i32;
        let cap = max_bonus_arr[mi] * base.agility;
        let clamped = raw.min(cap);
        if clamped == 0 { 1 } else { clamped }
    };

    let compute_agility_half = |ib: i32| -> i32 {
        let half = (ib + 1) / 2; // ceil(ib / 2)
        let raw = half + (inputs.agility_stat / 5.0) as i32;
        let cap = max_bonus_arr[mi] * base.agility;
        let clamped = raw.min(cap);
        if clamped == 0 { 1 } else { clamped }
    };

    match quality {
        SpecialQuality::Dragon => {
            power_bonus = compute_power(item_bonus);
        }
        SpecialQuality::Dwarven => {
            durability_bonus = compute_durability(inputs.item_bonus_roll);
        }
        SpecialQuality::Elven => {
            let is_armor_family =
                item_type == SmithItemType::Armor || item_type == SmithItemType::PlateLeg;
            if is_armor_family && item_type == SmithItemType::Armor {
                agility_bonus = compute_agility(inputs.item_bonus_roll);
            } else {
                // PlateLeg uses half bonus
                agility_bonus = compute_agility_half(inputs.item_bonus_roll);
            }
        }
        SpecialQuality::DragonDwarven => {
            // Dragon power bonus uses doubled item_bonus for armor
            let power_ib = if item_type == SmithItemType::Armor {
                item_bonus * 2
            } else {
                item_bonus
            };
            power_bonus = compute_power(power_ib);
            durability_bonus = compute_durability(inputs.item_bonus_roll);
        }
        SpecialQuality::ElvenDwarven => {
            if item_type == SmithItemType::Armor {
                agility_bonus = compute_agility(inputs.item_bonus_roll);
            } else {
                agility_bonus = compute_agility_half(inputs.item_bonus_roll);
            }
            durability_bonus = compute_durability(inputs.item_bonus_roll);
        }
    }

    SpecialBonusResult {
        power_bonus,
        durability_bonus,
        agility_bonus,
    }
}

// ---------------------------------------------------------------------------
// Elite item bonus
// ---------------------------------------------------------------------------

/// Compute the bonus for an elite crafted item.
///
/// PHP: Dragon type (`elitetype == 'S'`) adds to power, Elven adds negative agility.
/// Bonus = `rand(1, smith_skill) + stat`, capped by `max_bonus_elite[mineral] * level`.
#[allow(clippy::cast_possible_truncation)]
pub fn elite_item_bonus(
    _elite_type_is_dragon: bool,
    skill_roll: i32,
    relevant_stat: f64,
    plan_level: i32,
    mineral: Mineral,
) -> i32 {
    let mi = mineral.index();
    let cap = MAX_BONUS_ELITE[mi] * plan_level;
    let raw = (f64::from(skill_roll) + relevant_stat).floor() as i32;
    raw.min(cap)
}

// ---------------------------------------------------------------------------
// Energy cost
// ---------------------------------------------------------------------------

/// Energy required to craft one normal item.
///
/// Armor costs `level * 2`, everything else costs `level`.
pub fn normal_energy_cost(item_type: SmithItemType, plan_level: i32) -> i32 {
    match item_type {
        SmithItemType::Armor => plan_level * 2,
        _ => plan_level,
    }
}

/// Energy required to craft one elite item.
///
/// Weapons and armor cost `level * 10`, everything else `level * 5`.
pub fn elite_energy_cost(item_type: SmithItemType, plan_level: i32) -> i32 {
    match item_type {
        SmithItemType::Weapon | SmithItemType::Armor => plan_level * 10,
        _ => plan_level * 5,
    }
}

// ---------------------------------------------------------------------------
// XP calculation
// ---------------------------------------------------------------------------

/// XP multiplier for a crafting action.
///
/// PHP: armor XP is `level * 2`, other is `level * 1`.
/// Craftsman class doubles this.
pub fn normal_xp_multiplier(item_type: SmithItemType, is_craftsman: bool) -> i32 {
    let base = match item_type {
        SmithItemType::Armor => 2,
        _ => 1,
    };
    if is_craftsman { base * 2 } else { base }
}

/// Elite XP multiplier.
///
/// PHP: armor = 4, others = 2 (craftsman-only, so no class check needed).
pub fn elite_xp_multiplier(item_type: SmithItemType) -> i32 {
    match item_type {
        SmithItemType::Armor => 4,
        _ => 2,
    }
}

/// XP gained when creating a normal special item.
///
/// PHP: `level * (smith_skill * 10)`.
#[allow(clippy::cast_possible_truncation)]
pub fn special_item_xp(plan_level: i32, smith_skill: f64) -> i32 {
    (f64::from(plan_level) * (smith_skill * 10.0)) as i32
}

/// XP gained when creating a normal non-special item.
///
/// PHP: `level * 2` (per successful item).
pub fn normal_item_xp(plan_level: i32) -> i32 {
    plan_level * 2
}

/// XP gained when creating an elite item.
///
/// PHP: `level * (smith_skill * 20)`.
#[allow(clippy::cast_possible_truncation)]
pub fn elite_item_xp(plan_level: i32, smith_skill: f64) -> i32 {
    (f64::from(plan_level) * (smith_skill * 20.0)) as i32
}

/// Distribute crafting XP between smith skill and stat categories.
///
/// For normal crafting, XP is divided by `(1 + number_of_boosted_stats)`.
/// Each stat that was boosted by a special item gets its share.
///
/// Returns `(smith_xp, per_stat_xp)`.
pub fn distribute_normal_xp(total_xp: i32, boosted_stat_count: i32) -> (i32, i32) {
    let divisor = boosted_stat_count + 1;
    let share = total_xp / divisor;
    (share, share)
}

/// For elite crafting, XP is split 50/50 between smith skill and the relevant stat.
///
/// Returns `(smith_xp, stat_xp)`.
pub fn distribute_elite_xp(total_xp: i32) -> (i32, i32) {
    (total_xp / 2, total_xp / 2)
}

// ---------------------------------------------------------------------------
// Monster loot requirements for elite items
// ---------------------------------------------------------------------------

/// Loot quantities required per elite crafting attempt.
///
/// PHP: Weapons/armor need `[8, 4, 3, 1]` per item; others `[4, 3, 2, 1]`.
/// These scale by batch size.
pub fn elite_loot_requirements(item_type: SmithItemType, batch_size: i32) -> [i32; 4] {
    let base = match item_type {
        SmithItemType::Weapon | SmithItemType::Armor => [8, 4, 3, 1],
        _ => [4, 3, 2, 1],
    };
    [
        base[0] * batch_size,
        base[1] * batch_size,
        base[2] * batch_size,
        base[3] * batch_size,
    ]
}

// ---------------------------------------------------------------------------
// Astral construction
// ---------------------------------------------------------------------------

/// Astral component tier (1-indexed, matching `P1`..`P5` plan names).
#[derive(Debug, Clone, Copy, PartialEq, Eq)]
pub struct AstralTier(u8);

impl AstralTier {
    /// Create a tier from the 1-based index (1–5).
    pub fn new(tier: u8) -> Option<Self> {
        if (1..=5).contains(&tier) {
            Some(Self(tier))
        } else {
            None
        }
    }

    pub fn index(self) -> usize {
        (self.0 - 1) as usize
    }
}

/// Mineral costs for astral construction, indexed by tier (0–4).
///
/// Order: adamantium, crystal, meteor, pine, hazel, yew, elm, steel,
/// ironore, copperore, tinore, zincore, coal.
const ASTRAL_MINERAL_COSTS: [[i32; 13]; 5] = [
    [
        2500, 1250, 250, 6000, 4000, 1500, 1000, 500, 750, 3000, 2000, 1000, 10000,
    ],
    [
        4000, 2000, 300, 8000, 5500, 2500, 1500, 750, 1000, 5000, 3000, 2000, 15000,
    ],
    [
        6500, 2500, 400, 12000, 7000, 4000, 2000, 1000, 1500, 7000, 4000, 3000, 20000,
    ],
    [
        8000, 4000, 500, 17000, 8500, 5500, 2500, 1250, 2000, 9000, 5000, 4000, 25000,
    ],
    [
        10000, 5000, 600, 20000, 10000, 7000, 3000, 1500, 2500, 11000, 6000, 5000, 30000,
    ],
];

/// Mithril (platinum) cost per astral tier.
const ASTRAL_PLATINUM_COSTS: [i32; 5] = [4000, 5000, 6000, 7000, 8000];

/// Energy cost per astral tier.
const ASTRAL_ENERGY_COSTS: [i32; 5] = [50, 75, 100, 125, 150];

/// Success chance weight per astral tier.
///
/// PHP: `chance = floor((smith_skill * weight) + (carpentry_skill * weight))`,
/// capped at 95.
const ASTRAL_CHANCE_WEIGHTS: [f64; 5] = [0.3, 0.25, 0.2, 0.15, 0.1];

/// Mineral resource keys for astral construction in order.
pub const ASTRAL_MINERAL_KEYS: [&str; 13] = [
    "adamantium",
    "crystal",
    "meteor",
    "pine",
    "hazel",
    "yew",
    "elm",
    "steel",
    "ironore",
    "copperore",
    "tinore",
    "zincore",
    "coal",
];

/// Get the mineral costs for an astral tier.
pub fn astral_mineral_costs(tier: AstralTier) -> &'static [i32; 13] {
    &ASTRAL_MINERAL_COSTS[tier.index()]
}

/// Get the platinum cost for an astral tier.
pub fn astral_platinum_cost(tier: AstralTier) -> i32 {
    ASTRAL_PLATINUM_COSTS[tier.index()]
}

/// Get the energy cost for an astral tier.
pub fn astral_energy_cost(tier: AstralTier) -> i32 {
    ASTRAL_ENERGY_COSTS[tier.index()]
}

/// Compute astral construction success chance.
///
/// PHP: `floor((smith_skill * weight) + (carpentry_skill * weight))`, max 95.
#[allow(clippy::cast_possible_truncation)]
pub fn astral_success_chance(smith_skill: f64, carpentry_skill: f64, tier: AstralTier) -> i32 {
    let w = ASTRAL_CHANCE_WEIGHTS[tier.index()];
    let chance = (smith_skill * w + carpentry_skill * w).floor() as i32;
    chance.clamp(1, 95)
}

/// XP range for a successful astral construction.
///
/// Returns `(min_xp, max_xp)` for the given tier.
pub fn astral_xp_range(tier: AstralTier) -> (i32, i32) {
    const RANGES: [(i32, i32); 5] = [
        (500, 1000),
        (1000, 1500),
        (1500, 2000),
        (2000, 2500),
        (2500, 3000),
    ];
    RANGES[tier.index()]
}

/// Material recovery fraction on astral construction failure.
///
/// PHP uses a `roll_1_to_100` to determine how much material is saved:
///
/// | Craftsman | Roll range | Lost fraction |
/// |-----------|------------|---------------|
/// | Yes       | 1–5        | 0% (free)     |
/// | Yes       | 6–20       | 20%           |
/// | Yes       | 21–50      | 25%           |
/// | Yes       | 51–100     | 33%           |
/// | No        | 1–5        | 0% (free)     |
/// | No        | 6–20       | 40%           |
/// | No        | 21–50      | 50%           |
/// | No        | 51–100     | 66%           |
pub fn astral_failure_loss_fraction(is_craftsman: bool, roll_1_to_100: i32) -> f64 {
    if is_craftsman {
        match roll_1_to_100 {
            1..=5 => 0.0,
            6..=20 => 0.2,
            21..=50 => 0.25,
            _ => 0.33,
        }
    } else {
        match roll_1_to_100 {
            1..=5 => 0.0,
            6..=20 => 0.4,
            21..=50 => 0.5,
            _ => 0.66,
        }
    }
}

/// Compute actual mineral consumed after a failed astral construction.
///
/// PHP: `ceil(cost * loss_fraction)`.
#[allow(clippy::cast_possible_truncation)]
pub fn astral_failure_cost(base_cost: i32, loss_fraction: f64) -> i32 {
    (f64::from(base_cost) * loss_fraction).ceil() as i32
}

// ---------------------------------------------------------------------------
// Plan purchase validation
// ---------------------------------------------------------------------------

/// Whether a player can buy a given smithing plan.
#[derive(Debug, Clone, Copy, PartialEq, Eq)]
pub enum PlanBuyError {
    /// Player already owns this plan.
    AlreadyOwned,
    /// Not enough gold.
    InsufficientGold,
    /// Only craftsmen can buy elite plans.
    NotCraftsman,
    /// Smith skill too low for this plan.
    SkillTooLow,
}

/// Validate whether a player can purchase a smithing plan.
pub fn can_buy_plan(
    already_owned: bool,
    player_gold: i32,
    plan_cost: i32,
    is_elite: bool,
    is_craftsman: bool,
    smith_skill_level: f64,
    plan_level: i32,
) -> Result<(), PlanBuyError> {
    if already_owned {
        return Err(PlanBuyError::AlreadyOwned);
    }
    if player_gold < plan_cost {
        return Err(PlanBuyError::InsufficientGold);
    }
    if is_elite && !is_craftsman {
        return Err(PlanBuyError::NotCraftsman);
    }
    if smith_skill_level < f64::from(plan_level) {
        return Err(PlanBuyError::SkillTooLow);
    }
    Ok(())
}

// ---------------------------------------------------------------------------
// Craftsman bonus to roll
// ---------------------------------------------------------------------------

/// Reduce the special-chance roll for craftsman class.
///
/// PHP: `roll2 = floor(roll2 - smith_skill/100)` for craftsmen.
/// Also Gnome craftsmen get double reduction.
#[allow(clippy::cast_possible_truncation)]
pub fn craftsman_roll_reduction(smith_skill: f64, is_gnome: bool) -> i32 {
    let base = smith_skill / 100.0;
    let total = if is_gnome { base * 2.0 } else { base };
    total.floor() as i32
}

// ---------------------------------------------------------------------------
// Tests
// ---------------------------------------------------------------------------

#[cfg(test)]
mod tests {
    use super::*;

    // --- Item type round-trip ---

    #[test]
    fn item_type_round_trip() {
        for (db, expected) in [
            ("W", SmithItemType::Weapon),
            ("A", SmithItemType::Armor),
            ("H", SmithItemType::Helmet),
            ("L", SmithItemType::PlateLeg),
            ("S", SmithItemType::Shield),
            ("E", SmithItemType::Tool),
        ] {
            let parsed = SmithItemType::from_db(db).unwrap();
            assert_eq!(parsed, expected);
            assert_eq!(parsed.as_db(), db);
        }
    }

    // --- Mineral round-trip ---

    #[test]
    fn mineral_round_trip() {
        for (key, expected) in [
            ("copper", Mineral::Copper),
            ("bronze", Mineral::Bronze),
            ("brass", Mineral::Brass),
            ("iron", Mineral::Iron),
            ("steel", Mineral::Steel),
        ] {
            let parsed = Mineral::from_key(key).unwrap();
            assert_eq!(parsed, expected);
            assert_eq!(parsed.as_key(), key);
        }
    }

    // --- Normal base stats ---

    #[test]
    fn normal_weapon_copper() {
        let stats = normal_base_stats(SmithItemType::Weapon, 10, Mineral::Copper, 200);
        assert_eq!(stats.power, 10);
        assert_eq!(stats.agility, 0);
        assert_eq!(stats.durability, 40);
        assert_eq!(stats.repair_cost, 20); // level(10) * tier(1) * 2
        assert_eq!(stats.sell_cost, 10); // ceil(200/20)
    }

    #[test]
    fn normal_armor_iron() {
        let stats = normal_base_stats(SmithItemType::Armor, 10, Mineral::Iron, 1000);
        assert_eq!(stats.power, 30); // level * 3
        assert_eq!(stats.agility, 5); // level / 2
        assert_eq!(stats.durability, 320);
        assert_eq!(stats.repair_cost, 10 * 64 * 2);
        assert_eq!(stats.sell_cost, 50); // ceil(1000/20)
    }

    #[test]
    fn normal_plate_leg_steel() {
        let stats = normal_base_stats(SmithItemType::PlateLeg, 15, Mineral::Steel, 500);
        assert_eq!(stats.power, 15);
        assert_eq!(stats.agility, 3); // 15 / 5
        assert_eq!(stats.durability, 320); // low durability table
        assert_eq!(stats.repair_cost, 15 * 256);
    }

    #[test]
    fn normal_tool_brass() {
        let stats = normal_base_stats(SmithItemType::Tool, 5, Mineral::Brass, 100);
        assert_eq!(stats.power, 15); // 10 + level
        assert_eq!(stats.agility, 0);
        assert_eq!(stats.durability, 20); // tool durability
        assert_eq!(stats.repair_cost, (5 + 20) * 16); // (level+20) * tier
    }

    // --- Elite base stats ---

    #[test]
    fn elite_weapon_steel() {
        let stats = elite_base_stats(SmithItemType::Weapon, 20, Mineral::Steel, 2000);
        assert_eq!(stats.power, 20);
        assert_eq!(stats.durability, 650);
        assert_eq!(stats.repair_cost, 20 * 256 * 2);
        assert_eq!(stats.sell_cost, 10); // ceil(2000/200)
    }

    // --- Success chances ---

    #[test]
    fn normal_chance_high_skill() {
        // (50 - 6) * 200-skill / 10-level = 44 * 20 = 880 → capped at 95
        let chance = normal_success_chance(200.0, 10, Mineral::Copper);
        assert_eq!(chance, 95);
    }

    #[test]
    fn normal_chance_low() {
        // (50 - 20) * 10 / 30 = 30 * 0.33 = 10
        let chance = normal_success_chance(10.0, 30, Mineral::Steel);
        assert_eq!(chance, 10);
    }

    #[test]
    fn elite_chance_capped() {
        let chance = elite_success_chance(100.0, 1);
        assert_eq!(chance, 90);
    }

    #[test]
    fn elite_chance_low() {
        let chance = elite_success_chance(5.0, 20);
        assert_eq!(chance, 1);
    }

    // --- Energy costs ---

    #[test]
    fn normal_energy() {
        assert_eq!(normal_energy_cost(SmithItemType::Armor, 10), 20);
        assert_eq!(normal_energy_cost(SmithItemType::Weapon, 10), 10);
        assert_eq!(normal_energy_cost(SmithItemType::Shield, 10), 10);
    }

    #[test]
    fn elite_energy() {
        assert_eq!(elite_energy_cost(SmithItemType::Weapon, 10), 100);
        assert_eq!(elite_energy_cost(SmithItemType::Armor, 10), 100);
        assert_eq!(elite_energy_cost(SmithItemType::Shield, 10), 50);
    }

    // --- XP ---

    #[test]
    fn special_xp() {
        // level=10, skill=50 → 10 * 500 = 5000
        assert_eq!(special_item_xp(10, 50.0), 5000);
    }

    #[test]
    fn normal_xp_per_item() {
        assert_eq!(normal_item_xp(10), 20);
    }

    #[test]
    fn elite_xp_calculation() {
        // level=10, skill=50 → 10 * 1000 = 10000
        assert_eq!(elite_item_xp(10, 50.0), 10000);
    }

    #[test]
    fn distribute_normal_no_stats() {
        let (smith, stat) = distribute_normal_xp(100, 0);
        assert_eq!(smith, 100); // 100 / 1
        assert_eq!(stat, 100);
    }

    #[test]
    fn distribute_normal_two_stats() {
        let (smith, stat) = distribute_normal_xp(300, 2);
        assert_eq!(smith, 100); // 300 / 3
        assert_eq!(stat, 100);
    }

    #[test]
    fn distribute_elite() {
        let (smith, stat) = distribute_elite_xp(200);
        assert_eq!(smith, 100);
        assert_eq!(stat, 100);
    }

    // --- Elite loot requirements ---

    #[test]
    fn elite_loot_weapon() {
        assert_eq!(
            elite_loot_requirements(SmithItemType::Weapon, 1),
            [8, 4, 3, 1]
        );
        assert_eq!(
            elite_loot_requirements(SmithItemType::Weapon, 3),
            [24, 12, 9, 3]
        );
    }

    #[test]
    fn elite_loot_shield() {
        assert_eq!(
            elite_loot_requirements(SmithItemType::Shield, 1),
            [4, 3, 2, 1]
        );
    }

    // --- Special quality roll ---

    #[test]
    fn no_special_when_roll2_high() {
        assert_eq!(
            special_quality_roll(21, 50, true, SmithItemType::Weapon),
            None
        );
    }

    #[test]
    fn dragon_armor_low_roll3() {
        assert_eq!(
            special_quality_roll(10, 20, false, SmithItemType::Armor),
            Some(SpecialQuality::Dragon)
        );
    }

    #[test]
    fn dragon_weapon_low_roll3() {
        assert_eq!(
            special_quality_roll(10, 30, false, SmithItemType::Weapon),
            Some(SpecialQuality::Dragon)
        );
    }

    #[test]
    fn dwarven_armor_craftsman() {
        assert_eq!(
            special_quality_roll(10, 50, true, SmithItemType::Armor),
            Some(SpecialQuality::Dwarven)
        );
    }

    #[test]
    fn elven_armor_craftsman() {
        assert_eq!(
            special_quality_roll(10, 80, true, SmithItemType::Armor),
            Some(SpecialQuality::Elven)
        );
    }

    #[test]
    fn dragon_dwarven_armor_boundary() {
        assert_eq!(
            special_quality_roll(10, 34, true, SmithItemType::Armor),
            Some(SpecialQuality::DragonDwarven)
        );
    }

    #[test]
    fn non_craftsman_no_dwarven() {
        // roll3=50 on armor → not craftsman → no special
        assert_eq!(
            special_quality_roll(10, 50, false, SmithItemType::Armor),
            None
        );
    }

    // --- Plan buy validation ---

    #[test]
    fn plan_buy_valid() {
        assert!(can_buy_plan(false, 500, 200, false, false, 10.0, 10).is_ok());
    }

    #[test]
    fn plan_buy_already_owned() {
        assert_eq!(
            can_buy_plan(true, 500, 200, false, false, 10.0, 10),
            Err(PlanBuyError::AlreadyOwned)
        );
    }

    #[test]
    fn plan_buy_insufficient_gold() {
        assert_eq!(
            can_buy_plan(false, 100, 200, false, false, 10.0, 10),
            Err(PlanBuyError::InsufficientGold)
        );
    }

    #[test]
    fn plan_buy_elite_not_craftsman() {
        assert_eq!(
            can_buy_plan(false, 500, 200, true, false, 10.0, 10),
            Err(PlanBuyError::NotCraftsman)
        );
    }

    #[test]
    fn plan_buy_skill_too_low() {
        assert_eq!(
            can_buy_plan(false, 500, 200, false, false, 5.0, 10),
            Err(PlanBuyError::SkillTooLow)
        );
    }

    // --- Astral ---

    #[test]
    fn astral_tier_valid() {
        assert!(AstralTier::new(1).is_some());
        assert!(AstralTier::new(5).is_some());
        assert!(AstralTier::new(0).is_none());
        assert!(AstralTier::new(6).is_none());
    }

    #[test]
    fn astral_costs_tier1() {
        let tier = AstralTier::new(1).unwrap();
        let costs = astral_mineral_costs(tier);
        assert_eq!(costs[0], 2500); // adamantium
        assert_eq!(astral_platinum_cost(tier), 4000);
        assert_eq!(astral_energy_cost(tier), 50);
    }

    #[test]
    fn astral_chance_high() {
        let tier = AstralTier::new(1).unwrap();
        let chance = astral_success_chance(200.0, 200.0, tier);
        assert_eq!(chance, 95); // capped
    }

    #[test]
    fn astral_chance_low() {
        let tier = AstralTier::new(5).unwrap();
        let chance = astral_success_chance(10.0, 10.0, tier);
        // (10 * 0.1 + 10 * 0.1) = 2
        assert_eq!(chance, 2);
    }

    #[test]
    fn astral_failure_craftsman_lucky() {
        let loss = astral_failure_loss_fraction(true, 3);
        assert!((loss - 0.0).abs() < f64::EPSILON);
    }

    #[test]
    fn astral_failure_non_craftsman_bad() {
        let loss = astral_failure_loss_fraction(false, 75);
        assert!((loss - 0.66).abs() < f64::EPSILON);
    }

    #[test]
    fn astral_failure_cost_calculation() {
        assert_eq!(astral_failure_cost(1000, 0.33), 330);
        assert_eq!(astral_failure_cost(1000, 0.0), 0);
    }

    // --- Craftsman roll reduction ---

    #[test]
    fn craftsman_reduction_normal() {
        // skill=200 → 200/100 = 2
        assert_eq!(craftsman_roll_reduction(200.0, false), 2);
    }

    #[test]
    fn craftsman_reduction_gnome() {
        // skill=200 → 200/100 * 2 = 4
        assert_eq!(craftsman_roll_reduction(200.0, true), 4);
    }

    // --- Smith bonus key ---

    #[test]
    fn bonus_keys() {
        assert_eq!(smith_bonus_key(SmithItemType::Weapon), "weaponsmith");
        assert_eq!(smith_bonus_key(SmithItemType::Armor), "armorsmith");
        assert_eq!(smith_bonus_key(SmithItemType::Shield), "shieldsmith");
        assert_eq!(smith_bonus_key(SmithItemType::Helmet), "helmsmith");
        assert_eq!(smith_bonus_key(SmithItemType::PlateLeg), "legsmith");
        assert_eq!(smith_bonus_key(SmithItemType::Tool), "toolsmith");
    }

    // --- Elite item bonus ---

    #[test]
    fn elite_bonus_capped() {
        // cap = 21 * 10 = 210; roll=50 + stat=200 = 250 → capped at 210
        let bonus = elite_item_bonus(true, 50, 200.0, 10, Mineral::Copper);
        assert_eq!(bonus, 210);
    }

    #[test]
    fn elite_bonus_uncapped() {
        // cap = 21 * 10 = 210; roll=10 + stat=5 = 15
        let bonus = elite_item_bonus(true, 10, 5.0, 10, Mineral::Copper);
        assert_eq!(bonus, 15);
    }
}
