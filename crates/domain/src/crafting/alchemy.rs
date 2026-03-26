//! Alchemy workshop: potion production, herb consumption, astral potions.
//!
//! Ported from `alchemik.php`. Covers:
//!
//! - **Potion types**: Mana, Health, Poison, Antidote — each using different
//!   stat sources and bonus keys.
//! - **Brew loop**: multi-success mechanic where chance decreases by 50 per
//!   success within a single attempt, up to 20 successes.
//! - **Quality tiers**: Normal, Craftsman-Special `(S)`, or Failed `(K)`.
//! - **Astral potions**: 5 tiers with fixed herb costs, energy costs, and
//!   craftsman-specific failure-loss reduction.
//! - **XP distribution**: split equally among alchemy skill and relevant stats.

use std::fmt;

// ---------------------------------------------------------------------------
// Potion type
// ---------------------------------------------------------------------------

/// The four potion categories.
#[derive(Debug, Clone, Copy, PartialEq, Eq, Hash)]
pub enum PotionType {
    Mana,
    Health,
    Poison,
    Antidote,
}

impl PotionType {
    /// Parse from the single-char DB column value.
    #[must_use]
    pub fn from_db(ch: &str) -> Option<Self> {
        match ch {
            "M" => Some(Self::Mana),
            "H" => Some(Self::Health),
            "P" => Some(Self::Poison),
            "A" => Some(Self::Antidote),
            _ => None,
        }
    }

    /// DB column value.
    #[must_use]
    pub fn as_db(self) -> &'static str {
        match self {
            Self::Mana => "M",
            Self::Health => "H",
            Self::Poison => "P",
            Self::Antidote => "A",
        }
    }
}

impl fmt::Display for PotionType {
    fn fmt(&self, f: &mut fmt::Formatter<'_>) -> fmt::Result {
        f.write_str(self.as_db())
    }
}

// ---------------------------------------------------------------------------
// Stat source
// ---------------------------------------------------------------------------

/// Which player stats contribute to alchemy chance for a given potion type.
///
/// The bonus-skill key (e.g. `amana`, `ahealth`) is also associated per type.
#[derive(Debug, Clone, PartialEq)]
pub struct AlchemyStatSource {
    /// Stat keys used for the chance bonus (e.g. `["wisdom"]`).
    pub stat_keys: &'static [&'static str],
    /// The bonus-skill key from equipment (e.g. `"amana"`).
    pub bonus_key: &'static str,
}

/// Return the stat source configuration for a potion type.
///
/// PHP computes `fltStat` differently per type:
/// - Mana: `wisdom`
/// - Health: `inteli` (intelligence)
/// - Poison: `(min(wisdom, inteli) + agility) / 2`
/// - Antidote: `(min(wisdom, inteli) + speed) / 2`
#[must_use]
pub fn stat_source(potion_type: PotionType) -> AlchemyStatSource {
    match potion_type {
        PotionType::Mana => AlchemyStatSource {
            stat_keys: &["wisdom"],
            bonus_key: "amana",
        },
        PotionType::Health => AlchemyStatSource {
            stat_keys: &["inteli"],
            bonus_key: "ahealth",
        },
        PotionType::Poison => AlchemyStatSource {
            stat_keys: &["wisdom", "inteli", "agility"],
            bonus_key: "apoison",
        },
        PotionType::Antidote => AlchemyStatSource {
            stat_keys: &["wisdom", "inteli", "speed"],
            bonus_key: "aantidote",
        },
    }
}

/// Compute the stat bonus (`fltStat`) from raw stat values for a potion type.
///
/// The handler must supply stats in the order matching [`stat_source`]`.stat_keys`.
/// - Mana: `[wisdom]` → wisdom
/// - Health: `[inteli]` → inteli
/// - Poison: `[wisdom, inteli, agility]` → `(min(wisdom, inteli) + agility) / 2`
/// - Antidote: `[wisdom, inteli, speed]` → `(min(wisdom, inteli) + speed) / 2`
#[must_use]
pub fn compute_stat_bonus(potion_type: PotionType, stats: &[f64]) -> f64 {
    match potion_type {
        PotionType::Mana | PotionType::Health => stats.first().copied().unwrap_or(0.0),
        PotionType::Poison | PotionType::Antidote => {
            let w = stats.first().copied().unwrap_or(0.0);
            let i = stats.get(1).copied().unwrap_or(0.0);
            let third = stats.get(2).copied().unwrap_or(0.0);
            f64::midpoint(w.min(i), third)
        }
    }
}

// ---------------------------------------------------------------------------
// XP distribution stats
// ---------------------------------------------------------------------------

/// Returns the stat keys that receive XP alongside the alchemy skill.
///
/// XP is divided equally: `total_xp / (len(stats) + 1)` to each stat and
/// the same share to the alchemy skill itself.
#[must_use]
pub fn xp_stat_keys(potion_type: PotionType) -> &'static [&'static str] {
    match potion_type {
        PotionType::Mana => &["wisdom"],
        PotionType::Health => &["inteli"],
        PotionType::Poison => &["wisdom", "inteli", "agility"],
        PotionType::Antidote => &["wisdom", "inteli", "speed"],
    }
}

// ---------------------------------------------------------------------------
// Energy cost
// ---------------------------------------------------------------------------

/// Energy cost per single crafting attempt.
///
/// PHP: if `level > 1` → `level * 0.2`, else `1.0`.
#[must_use]
pub fn energy_per_attempt(recipe_level: i32) -> f64 {
    if recipe_level > 1 {
        f64::from(recipe_level) * 0.2
    } else {
        1.0
    }
}

// ---------------------------------------------------------------------------
// Brew success loop
// ---------------------------------------------------------------------------

/// Number of potions produced in a single brew attempt.
///
/// PHP logic: start with `chance = alchemy_skill + stat_bonus`. Roll once
/// (`roll_1_to_100`). While `roll < chance`, increment successes and reduce
/// chance by 50. Maximum 20 successes per attempt.
///
/// Returns 0 if the attempt fails entirely (the caller should produce a
/// failed `(K)` consolation potion in that case).
#[must_use]
pub fn brew_success_count(effective_skill: f64, stat_bonus: f64, roll_1_to_100: i32) -> i32 {
    let mut chance = effective_skill + stat_bonus;
    let roll = f64::from(roll_1_to_100);
    let mut count = 0i32;
    while roll < chance {
        count += 1;
        chance -= 50.0;
        if count == 20 {
            break;
        }
    }
    count
}

// ---------------------------------------------------------------------------
// Quality tier
// ---------------------------------------------------------------------------

/// Quality of a brewed batch within one attempt.
#[derive(Debug, Clone, Copy, PartialEq, Eq)]
pub enum BrewQuality {
    /// Normal success (no suffix).
    Normal,
    /// Craftsman special — `(S)` suffix, boosted power.
    CraftsmanSpecial,
    /// Failed attempt — `(K)` suffix, consolation item.
    Failed,
}

/// Craftsman bonus added to the quality roll.
///
/// PHP: `floor(alchemy_skill / 10) - recipe_level`, clamped to `[0, 50]`.
#[must_use]
#[allow(clippy::cast_possible_truncation)]
pub fn craftsman_quality_bonus(alchemy_skill: f64, recipe_level: i32) -> i32 {
    let bonus = (alchemy_skill / 10.0).floor() as i64 - i64::from(recipe_level);
    bonus.clamp(0, 50) as i32
}

/// Determine the brew quality for a successful attempt (`success_count` > 0).
///
/// - Craftsman class: `roll2 + craftsman_bonus > 89` and type ≠ Antidote →
///   `CraftsmanSpecial`.
/// - Otherwise: `Normal`.
///
/// `roll2_1_to_100` is the second random roll.
#[must_use]
pub fn determine_quality(
    is_craftsman: bool,
    potion_type: PotionType,
    roll2_1_to_100: i32,
    alchemy_skill: f64,
    recipe_level: i32,
) -> BrewQuality {
    if !is_craftsman {
        return BrewQuality::Normal;
    }
    if potion_type == PotionType::Antidote {
        return BrewQuality::Normal;
    }
    let bonus = craftsman_quality_bonus(alchemy_skill, recipe_level);
    if roll2_1_to_100 + bonus > 89 {
        BrewQuality::CraftsmanSpecial
    } else {
        BrewQuality::Normal
    }
}

// ---------------------------------------------------------------------------
// Potion power
// ---------------------------------------------------------------------------

/// Compute the power of a brewed potion.
///
/// Returns `(power, max_power)` — power is clamped to `max_power`.
///
/// PHP rules:
///
/// **`CraftsmanSpecial`**:
/// - Mana/Health: power = `recipe_level + alchemy_skill`, max = `level * 2`
/// - Poison: power = `ceil(alchemy_skill / 2)`, max = `level * 4`
/// - Antidote: unreachable (antidotes never get special quality)
///
/// **`Normal`**:
/// - Mana/Health: power = `ceil(alchemy_skill / 2)`, max = `level`
/// - Poison/Antidote: power = `ceil(alchemy_skill / 2)`, max = `level * 2`
///
/// **`Failed`**:
/// - Non-Poison: power = `alchemy_skill`, max = `level`
/// - Poison: power = `ceil(alchemy_skill / 2)`, max = `level`
#[must_use]
#[allow(clippy::cast_possible_truncation)]
pub fn potion_power(
    quality: BrewQuality,
    potion_type: PotionType,
    recipe_level: i32,
    alchemy_skill: f64,
) -> i32 {
    let lvl = recipe_level;
    let (raw, max) = match quality {
        BrewQuality::CraftsmanSpecial => match potion_type {
            PotionType::Mana | PotionType::Health => {
                let raw = (f64::from(lvl) + alchemy_skill).ceil() as i32;
                (raw, lvl * 2)
            }
            PotionType::Poison => {
                let raw = (alchemy_skill / 2.0).ceil() as i32;
                (raw, lvl * 4)
            }
            // Antidote never gets CraftsmanSpecial, but handle gracefully.
            PotionType::Antidote => {
                let raw = (alchemy_skill / 2.0).ceil() as i32;
                (raw, lvl * 2)
            }
        },
        BrewQuality::Normal => match potion_type {
            PotionType::Mana | PotionType::Health => {
                let raw = (alchemy_skill / 2.0).ceil() as i32;
                (raw, lvl)
            }
            PotionType::Poison | PotionType::Antidote => {
                let raw = (alchemy_skill / 2.0).ceil() as i32;
                (raw, lvl * 2)
            }
        },
        BrewQuality::Failed => {
            if potion_type == PotionType::Poison {
                let raw = (alchemy_skill / 2.0).ceil() as i32;
                (raw, lvl)
            } else {
                let raw = alchemy_skill as i32;
                (raw, lvl)
            }
        }
    };
    raw.min(max)
}

// ---------------------------------------------------------------------------
// XP formulas
// ---------------------------------------------------------------------------

/// Raw XP earned from a single brew attempt (before craftsman doubling).
///
/// PHP formulas:
/// - **`CraftsmanSpecial`**: `level * 10` for the first success,
///   `+ level * 5 * (extra - 1)` for additional successes.
/// - **`Normal`** success: `level * 5` for the first success,
///   `+ level * (extra - 1)` for additional successes.
/// - **`Failed`**: `level * 2`.
#[must_use]
pub fn brew_xp(quality: BrewQuality, recipe_level: i32, success_count: i32) -> i32 {
    let lvl = recipe_level;
    match quality {
        BrewQuality::CraftsmanSpecial => {
            let base = lvl * 10;
            if success_count > 1 {
                base + lvl * 5 * (success_count - 1)
            } else {
                base
            }
        }
        BrewQuality::Normal => {
            let base = lvl * 5;
            if success_count > 1 {
                base + lvl * (success_count - 1)
            } else {
                base
            }
        }
        BrewQuality::Failed => lvl * 2,
    }
}

/// Apply the craftsman XP doubling.
///
/// PHP: `if (clas == 'Rzemieślnik') rpd *= 2;` — applied to the *total* XP
/// across all attempts before distribution.
#[must_use]
pub fn apply_craftsman_xp_bonus(total_xp: i32, is_craftsman: bool) -> i32 {
    if is_craftsman { total_xp * 2 } else { total_xp }
}

/// Distribute total XP among skill and stats.
///
/// PHP: `ceil(total_xp / (len(stats) + 1))` to each stat and to alchemy.
/// Returns `(alchemy_xp, per_stat_xp)`.
#[must_use]
#[allow(clippy::cast_possible_truncation, clippy::cast_possible_wrap)]
pub fn distribute_xp(total_xp: i32, stat_count: usize) -> (i32, i32) {
    let divisor = (stat_count + 1) as i32;
    if divisor <= 0 {
        return (total_xp, 0);
    }
    let share = (f64::from(total_xp) / f64::from(divisor)).ceil() as i32;
    (share, share)
}

// ---------------------------------------------------------------------------
// Potion sell cost
// ---------------------------------------------------------------------------

/// Gold value of a potion when sold to the shop.
///
/// PHP:
/// - Mana: `ceil(power * 3 / 20)`
/// - Others (Health, Poison, Antidote): `ceil(2 * power * 3 / 20)`
#[must_use]
#[allow(clippy::cast_possible_truncation)]
pub fn potion_sell_cost(potion_type: PotionType, power: i32) -> i32 {
    let base = match potion_type {
        PotionType::Mana => f64::from(power) * 3.0 / 20.0,
        _ => f64::from(power) * 2.0 * 3.0 / 20.0,
    };
    base.ceil() as i32
}

// ---------------------------------------------------------------------------
// Recipe purchase validation
// ---------------------------------------------------------------------------

/// Errors when trying to buy a recipe.
#[derive(Debug, Clone, PartialEq, Eq)]
pub enum RecipeBuyError {
    /// Player already owns this recipe.
    AlreadyOwned,
    /// Recipe not found or not for sale.
    NotAvailable,
    /// Not enough gold.
    InsufficientGold { cost: i32, available: i32 },
    /// Alchemy skill too low.
    SkillTooLow { required: i32, actual: i32 },
}

impl fmt::Display for RecipeBuyError {
    fn fmt(&self, f: &mut fmt::Formatter<'_>) -> fmt::Result {
        match self {
            Self::AlreadyOwned => write!(f, "Recipe already owned"),
            Self::NotAvailable => write!(f, "Recipe not available for purchase"),
            Self::InsufficientGold { cost, available } => {
                write!(f, "Need {cost} gold, have {available}")
            }
            Self::SkillTooLow { required, actual } => {
                write!(f, "Need alchemy level {required}, have {actual}")
            }
        }
    }
}

impl std::error::Error for RecipeBuyError {}

/// Validate whether a player can buy a specific recipe.
///
/// PHP checks in order: already owned → plan exists with `status='S'` →
/// gold → alchemy skill ≥ plan level.
pub fn can_buy_recipe(
    already_owned: bool,
    plan_for_sale: bool,
    plan_cost: i32,
    plan_level: i32,
    player_gold: i32,
    player_alchemy_skill: i32,
) -> Result<(), RecipeBuyError> {
    if already_owned {
        return Err(RecipeBuyError::AlreadyOwned);
    }
    if !plan_for_sale {
        return Err(RecipeBuyError::NotAvailable);
    }
    if plan_cost > player_gold {
        return Err(RecipeBuyError::InsufficientGold {
            cost: plan_cost,
            available: player_gold,
        });
    }
    if player_alchemy_skill < plan_level {
        return Err(RecipeBuyError::SkillTooLow {
            required: plan_level,
            actual: player_alchemy_skill,
        });
    }
    Ok(())
}

// ---------------------------------------------------------------------------
// Maximum brew attempts
// ---------------------------------------------------------------------------

/// Maximum number of attempts a player can make, limited by herbs and energy.
///
/// PHP computes `intAmount` from the minimum herb ratio, then caps by
/// available energy. `recipe_level` determines energy per attempt.
///
/// `herb_stocks` = `[illani, illanias, nutari, dynallca]`
/// `herb_costs`  = `[illani, illanias, nutari, dynallca]` per recipe
#[must_use]
pub fn max_brew_attempts(
    herb_stocks: [i32; 4],
    herb_costs: [i32; 4],
    player_energy: f64,
    recipe_level: i32,
) -> i32 {
    let mut max_from_herbs = i32::MAX;
    for i in 0..4 {
        if herb_costs[i] > 0 {
            let ratio = herb_stocks[i] / herb_costs[i];
            if ratio < max_from_herbs {
                max_from_herbs = ratio;
            }
        }
    }
    if max_from_herbs == i32::MAX || max_from_herbs <= 0 {
        return 0;
    }

    let energy_cost = energy_per_attempt(recipe_level);
    #[allow(clippy::cast_possible_truncation)]
    let max_from_energy = if energy_cost > 0.0 {
        (player_energy / energy_cost).floor() as i32
    } else {
        max_from_herbs
    };

    max_from_herbs.min(max_from_energy)
}

// ---------------------------------------------------------------------------
// Potion name helpers
// ---------------------------------------------------------------------------

/// Build the final potion name with quality suffix.
///
/// PHP appends `" (S)"` for craftsman-special or `" (K)"` for failed.
#[must_use]
pub fn potion_name(base_name: &str, quality: BrewQuality) -> String {
    match quality {
        BrewQuality::CraftsmanSpecial => format!("{base_name} (S)"),
        BrewQuality::Failed => format!("{base_name} (K)"),
        BrewQuality::Normal => base_name.to_owned(),
    }
}

// ---------------------------------------------------------------------------
// Astral potions
// ---------------------------------------------------------------------------

/// Astral potion tier (1–5).
#[derive(Debug, Clone, Copy, PartialEq, Eq)]
pub struct AstralPotionTier(u8);

impl AstralPotionTier {
    /// Create a tier (1-based). Returns `None` for invalid values.
    #[must_use]
    pub fn new(tier: u8) -> Option<Self> {
        if (1..=5).contains(&tier) {
            Some(Self(tier))
        } else {
            None
        }
    }

    /// The 1-based tier number.
    #[must_use]
    pub fn value(self) -> u8 {
        self.0
    }

    /// Zero-based index for lookups.
    fn idx(self) -> usize {
        (self.0 - 1) as usize
    }
}

/// Names of the five astral potions.
pub const ASTRAL_POTION_NAMES: [&str; 5] = [
    "Magiczna esensja",
    "Gwiezdna maść",
    "Eliksir Illuminati",
    "Astralne medium",
    "Magiczny absynt",
];

/// Herb resource keys in DB column order: illani, nutari, illanias, dynallca.
///
/// Note: the PHP arrays index herbs as `[Illani, Nutari, Illanias, Dynalca]`
/// and the SQL UPDATE uses a *different* order. We store the costs in the
/// same order as PHP's `$arrAmount` and provide a mapping to DB columns.
pub const ASTRAL_HERB_KEYS: [&str; 4] = ["illani", "nutari", "illanias", "dynallca"];

/// Herb costs per tier: `[illani, nutari, illanias, dynallca]`.
///
/// PHP `$arrAmount`:
/// ```text
/// Tier 1: [3000, 1000, 2000, 1000]
/// Tier 2: [5000, 2500, 3500, 1500]
/// Tier 3: [7000, 3500, 5000, 2000]
/// Tier 4: [9000, 4500, 6500, 2500]
/// Tier 5: [12000, 6000, 8000, 3000]
/// ```
pub const ASTRAL_HERB_COSTS: [[i32; 4]; 5] = [
    [3000, 1000, 2000, 1000],
    [5000, 2500, 3500, 1500],
    [7000, 3500, 5000, 2000],
    [9000, 4500, 6500, 2500],
    [12_000, 6000, 8000, 3000],
];

/// Energy cost per tier.
pub const ASTRAL_ENERGY_COSTS: [i32; 5] = [50, 75, 100, 125, 150];

/// Herb costs for a given astral tier.
#[must_use]
pub fn astral_herb_costs(tier: AstralPotionTier) -> [i32; 4] {
    ASTRAL_HERB_COSTS[tier.idx()]
}

/// Energy cost for a given astral tier.
#[must_use]
pub fn astral_energy_cost(tier: AstralPotionTier) -> i32 {
    ASTRAL_ENERGY_COSTS[tier.idx()]
}

/// Success chance for an astral potion brew.
///
/// PHP: `floor(alchemy_skill * weight)`, capped at 95.
/// Weights per tier: `[0.3, 0.25, 0.2, 0.15, 0.1]`.
#[must_use]
#[allow(clippy::cast_possible_truncation)]
pub fn astral_success_chance(tier: AstralPotionTier, alchemy_skill: f64) -> i32 {
    const WEIGHTS: [f64; 5] = [0.3, 0.25, 0.2, 0.15, 0.1];
    let chance = (alchemy_skill * WEIGHTS[tier.idx()]).floor() as i32;
    chance.min(95)
}

/// XP range for a successful astral potion brew.
///
/// Returns `(min_xp, max_xp)` — caller uses `rand(min, max)`.
///
/// PHP:
/// ```text
/// Tier 1: [500, 1000]
/// Tier 2: [1000, 1500]
/// Tier 3: [1500, 2000]
/// Tier 4: [2000, 2500]
/// Tier 5: [2500, 3000]
/// ```
///
/// XP is distributed: `gain / 5` each to alchemy, wisdom, agility, inteli, speed.
#[must_use]
pub fn astral_xp_range(tier: AstralPotionTier) -> (i32, i32) {
    const RANGES: [(i32, i32); 5] = [
        (500, 1000),
        (1000, 1500),
        (1500, 2000),
        (2000, 2500),
        (2500, 3000),
    ];
    RANGES[tier.idx()]
}

/// Stat keys that receive XP from a successful astral brew.
///
/// Each gets `total_xp / 5`.
pub const ASTRAL_XP_STATS: [&str; 4] = ["wisdom", "agility", "inteli", "speed"];

/// Distribute astral XP among alchemy skill and stats.
///
/// PHP: `gain / 5` to each of alchemy, wisdom, agility, inteli, speed.
/// Returns `(alchemy_share, per_stat_share)`.
#[must_use]
pub fn distribute_astral_xp(total_xp: i32) -> (i32, i32) {
    let share = total_xp / 5;
    (share, share)
}

/// Astral failure herb-loss fraction per class.
///
/// On failure, remaining herbs consumed = `ceil(cost * fraction)`.
///
/// PHP logic for craftsman class:
/// ```text
/// roll2 in 1..=5   → 0.0   (no loss)
/// roll2 in 6..=20  → 0.2
/// roll2 in 21..=50 → 0.25
/// roll2 > 50       → 0.33
/// ```
///
/// Non-craftsman:
/// ```text
/// roll2 in 1..=5   → 0.0
/// roll2 in 6..=20  → 0.4
/// roll2 in 21..=50 → 0.5
/// roll2 > 50       → 0.66
/// ```
#[must_use]
pub fn astral_failure_loss_fraction(is_craftsman: bool, roll2_1_to_100: i32) -> f64 {
    if roll2_1_to_100 < 6 {
        0.0
    } else if roll2_1_to_100 < 21 {
        if is_craftsman { 0.2 } else { 0.4 }
    } else if roll2_1_to_100 < 51 {
        if is_craftsman { 0.25 } else { 0.5 }
    } else if is_craftsman {
        0.33
    } else {
        0.66
    }
}

/// Compute herb costs after a failed astral brew.
///
/// Each herb cost is `ceil(base_cost * fraction)`. Energy is always fully consumed.
#[must_use]
#[allow(clippy::cast_possible_truncation)]
pub fn astral_failure_herb_costs(
    tier: AstralPotionTier,
    is_craftsman: bool,
    roll2_1_to_100: i32,
) -> [i32; 4] {
    let fraction = astral_failure_loss_fraction(is_craftsman, roll2_1_to_100);
    let base = ASTRAL_HERB_COSTS[tier.idx()];
    [
        (f64::from(base[0]) * fraction).ceil() as i32,
        (f64::from(base[1]) * fraction).ceil() as i32,
        (f64::from(base[2]) * fraction).ceil() as i32,
        (f64::from(base[3]) * fraction).ceil() as i32,
    ]
}

/// The astral plan identifier stored in DB (e.g. `"R1"`, `"R2"`, ...).
#[must_use]
pub fn astral_plan_name(tier: AstralPotionTier) -> String {
    format!("R{}", tier.value())
}

/// The astral component type stored in the `astral` table on success.
///
/// PHP: `"T" + (tier - 1)` → `"T0"`, `"T1"`, `"T2"`, `"T3"`, `"T4"`.
#[must_use]
pub fn astral_component_type(tier: AstralPotionTier) -> String {
    format!("T{}", tier.idx())
}

// ---------------------------------------------------------------------------
// Tests
// ---------------------------------------------------------------------------

#[cfg(test)]
mod tests {
    use super::*;

    // -- PotionType --

    #[test]
    fn potion_type_roundtrip() {
        for (ch, expected) in [
            ("M", PotionType::Mana),
            ("H", PotionType::Health),
            ("P", PotionType::Poison),
            ("A", PotionType::Antidote),
        ] {
            let pt = PotionType::from_db(ch).unwrap();
            assert_eq!(pt, expected);
            assert_eq!(pt.as_db(), ch);
        }
    }

    #[test]
    fn potion_type_invalid() {
        assert!(PotionType::from_db("X").is_none());
    }

    // -- stat_source --

    #[test]
    fn stat_source_mana() {
        let s = stat_source(PotionType::Mana);
        assert_eq!(s.stat_keys, &["wisdom"]);
        assert_eq!(s.bonus_key, "amana");
    }

    #[test]
    fn stat_source_poison() {
        let s = stat_source(PotionType::Poison);
        assert_eq!(s.stat_keys, &["wisdom", "inteli", "agility"]);
        assert_eq!(s.bonus_key, "apoison");
    }

    // -- compute_stat_bonus --

    #[test]
    fn stat_bonus_mana() {
        assert!((compute_stat_bonus(PotionType::Mana, &[50.0]) - 50.0).abs() < f64::EPSILON);
    }

    #[test]
    fn stat_bonus_poison() {
        // min(30, 40) + 20 = 50, / 2 = 25
        assert!(
            (compute_stat_bonus(PotionType::Poison, &[30.0, 40.0, 20.0]) - 25.0).abs()
                < f64::EPSILON
        );
    }

    #[test]
    fn stat_bonus_antidote() {
        // min(10, 20) + 30 = 40, / 2 = 20
        assert!(
            (compute_stat_bonus(PotionType::Antidote, &[10.0, 20.0, 30.0]) - 20.0).abs()
                < f64::EPSILON
        );
    }

    // -- energy_per_attempt --

    #[test]
    fn energy_level_1() {
        assert!((energy_per_attempt(1) - 1.0).abs() < f64::EPSILON);
    }

    #[test]
    fn energy_level_5() {
        assert!((energy_per_attempt(5) - 1.0).abs() < f64::EPSILON);
    }

    #[test]
    fn energy_level_10() {
        assert!((energy_per_attempt(10) - 2.0).abs() < f64::EPSILON);
    }

    // -- brew_success_count --

    #[test]
    fn brew_fail() {
        // skill 30 + stat 10 = 40. roll 50 >= 40 → 0 successes
        assert_eq!(brew_success_count(30.0, 10.0, 50), 0);
    }

    #[test]
    fn brew_one_success() {
        // skill 60 + stat 10 = 70. roll 50 < 70 → +1, chance = 20, 50 >= 20 → stop
        assert_eq!(brew_success_count(60.0, 10.0, 50), 1);
    }

    #[test]
    fn brew_two_successes() {
        // skill 100 + stat 20 = 120. roll 10:
        //   10 < 120 → +1, chance = 70
        //   10 < 70  → +2, chance = 20
        //   10 < 20  → +3, chance = -30
        //   10 >= -30 is false, wait 10 < -30 is false → stop
        // Actually: while roll < chance → 10 < -30 is false. So 3.
        assert_eq!(brew_success_count(100.0, 20.0, 10), 3);
    }

    #[test]
    fn brew_max_20() {
        // Very high skill → capped at 20
        assert_eq!(brew_success_count(2000.0, 500.0, 1), 20);
    }

    #[test]
    fn brew_boundary_equal() {
        // roll == chance → while roll < chance is false → 0
        assert_eq!(brew_success_count(50.0, 0.0, 50), 0);
    }

    // -- craftsman_quality_bonus --

    #[test]
    fn craftsman_bonus_basic() {
        // floor(100 / 10) - 5 = 5
        assert_eq!(craftsman_quality_bonus(100.0, 5), 5);
    }

    #[test]
    fn craftsman_bonus_capped_at_50() {
        assert_eq!(craftsman_quality_bonus(1000.0, 1), 50);
    }

    #[test]
    fn craftsman_bonus_negative_becomes_0() {
        // floor(10 / 10) - 5 = -4 → clamped to 0
        assert_eq!(craftsman_quality_bonus(10.0, 5), 0);
    }

    // -- determine_quality --

    #[test]
    fn quality_non_craftsman_always_normal() {
        assert_eq!(
            determine_quality(false, PotionType::Mana, 100, 200.0, 1),
            BrewQuality::Normal
        );
    }

    #[test]
    fn quality_antidote_always_normal() {
        assert_eq!(
            determine_quality(true, PotionType::Antidote, 100, 200.0, 1),
            BrewQuality::Normal
        );
    }

    #[test]
    fn quality_craftsman_special_with_bonus() {
        // roll2=80, bonus = floor(150/10) - 5 = 10, 80 + 10 = 90 > 89 → special
        assert_eq!(
            determine_quality(true, PotionType::Mana, 80, 150.0, 5),
            BrewQuality::CraftsmanSpecial
        );
    }

    #[test]
    fn quality_craftsman_normal_not_enough() {
        // roll2=50, bonus = floor(50/10) - 5 = 0, 50 + 0 = 50 ≤ 89 → normal
        assert_eq!(
            determine_quality(true, PotionType::Health, 50, 50.0, 5),
            BrewQuality::Normal
        );
    }

    // -- potion_power --

    #[test]
    fn power_normal_mana() {
        // ceil(80 / 2) = 40, max = 10 → clamped to 10
        assert_eq!(
            potion_power(BrewQuality::Normal, PotionType::Mana, 10, 80.0),
            10
        );
    }

    #[test]
    fn power_normal_poison() {
        // ceil(80 / 2) = 40, max = 10 * 2 = 20 → clamped to 20
        assert_eq!(
            potion_power(BrewQuality::Normal, PotionType::Poison, 10, 80.0),
            20
        );
    }

    #[test]
    fn power_special_mana() {
        // ceil(10 + 80) = 90, max = 10 * 2 = 20 → 20
        assert_eq!(
            potion_power(BrewQuality::CraftsmanSpecial, PotionType::Mana, 10, 80.0),
            20
        );
    }

    #[test]
    fn power_special_poison() {
        // ceil(80 / 2) = 40, max = 10 * 4 = 40 → 40
        assert_eq!(
            potion_power(BrewQuality::CraftsmanSpecial, PotionType::Poison, 10, 80.0),
            40
        );
    }

    #[test]
    fn power_failed_non_poison() {
        // alchemy_skill = 80 → 80 as i32 = 80, max = 10 → 10
        assert_eq!(
            potion_power(BrewQuality::Failed, PotionType::Mana, 10, 80.0),
            10
        );
    }

    #[test]
    fn power_failed_poison() {
        // ceil(80 / 2) = 40, max = 10 → 10
        assert_eq!(
            potion_power(BrewQuality::Failed, PotionType::Poison, 10, 80.0),
            10
        );
    }

    // -- brew_xp --

    #[test]
    fn xp_special_one_success() {
        // level=5, special, 1 success → 5 * 10 = 50
        assert_eq!(brew_xp(BrewQuality::CraftsmanSpecial, 5, 1), 50);
    }

    #[test]
    fn xp_special_three_successes() {
        // level=5, special, 3 successes → 50 + 5 * 5 * 2 = 50 + 50 = 100
        assert_eq!(brew_xp(BrewQuality::CraftsmanSpecial, 5, 3), 100);
    }

    #[test]
    fn xp_normal_one_success() {
        // level=5, normal, 1 → 5 * 5 = 25
        assert_eq!(brew_xp(BrewQuality::Normal, 5, 1), 25);
    }

    #[test]
    fn xp_normal_three_successes() {
        // level=5, normal, 3 → 25 + 5 * 2 = 35
        assert_eq!(brew_xp(BrewQuality::Normal, 5, 3), 35);
    }

    #[test]
    fn xp_failed() {
        // level=5 → 5 * 2 = 10
        assert_eq!(brew_xp(BrewQuality::Failed, 5, 0), 10);
    }

    // -- craftsman xp bonus --

    #[test]
    fn craftsman_doubles_xp() {
        assert_eq!(apply_craftsman_xp_bonus(100, true), 200);
    }

    #[test]
    fn non_craftsman_no_bonus() {
        assert_eq!(apply_craftsman_xp_bonus(100, false), 100);
    }

    // -- distribute_xp --

    #[test]
    fn distribute_xp_mana() {
        // 1 stat (wisdom) + alchemy = 2 shares. 100 / 2 = 50 each
        let (alchemy, per_stat) = distribute_xp(100, 1);
        assert_eq!(alchemy, 50);
        assert_eq!(per_stat, 50);
    }

    #[test]
    fn distribute_xp_poison() {
        // 3 stats + alchemy = 4 shares. 100 / 4 = 25 each
        let (alchemy, per_stat) = distribute_xp(100, 3);
        assert_eq!(alchemy, 25);
        assert_eq!(per_stat, 25);
    }

    #[test]
    fn distribute_xp_rounds_up() {
        // 10 / 2 = 5 exactly, but 11 / 2 = 5.5 → ceil = 6
        let (alchemy, per_stat) = distribute_xp(11, 1);
        assert_eq!(alchemy, 6);
        assert_eq!(per_stat, 6);
    }

    // -- potion_sell_cost --

    #[test]
    fn sell_cost_mana() {
        // power=20: ceil(20 * 3 / 20) = ceil(3) = 3
        assert_eq!(potion_sell_cost(PotionType::Mana, 20), 3);
    }

    #[test]
    fn sell_cost_health() {
        // power=20: ceil(2 * 20 * 3 / 20) = ceil(6) = 6
        assert_eq!(potion_sell_cost(PotionType::Health, 20), 6);
    }

    #[test]
    fn sell_cost_rounds_up() {
        // power=7, mana: ceil(7 * 3 / 20) = ceil(1.05) = 2
        assert_eq!(potion_sell_cost(PotionType::Mana, 7), 2);
    }

    // -- can_buy_recipe --

    #[test]
    fn buy_recipe_success() {
        assert!(can_buy_recipe(false, true, 100, 5, 200, 10).is_ok());
    }

    #[test]
    fn buy_recipe_already_owned() {
        assert_eq!(
            can_buy_recipe(true, true, 100, 5, 200, 10).unwrap_err(),
            RecipeBuyError::AlreadyOwned
        );
    }

    #[test]
    fn buy_recipe_not_available() {
        assert_eq!(
            can_buy_recipe(false, false, 100, 5, 200, 10).unwrap_err(),
            RecipeBuyError::NotAvailable
        );
    }

    #[test]
    fn buy_recipe_insufficient_gold() {
        assert_eq!(
            can_buy_recipe(false, true, 100, 5, 50, 10).unwrap_err(),
            RecipeBuyError::InsufficientGold {
                cost: 100,
                available: 50
            }
        );
    }

    #[test]
    fn buy_recipe_skill_too_low() {
        assert_eq!(
            can_buy_recipe(false, true, 100, 10, 200, 5).unwrap_err(),
            RecipeBuyError::SkillTooLow {
                required: 10,
                actual: 5
            }
        );
    }

    // -- max_brew_attempts --

    #[test]
    fn max_attempts_limited_by_herbs() {
        // 100 illani, cost 10 → 10 attempts. Other herbs unlimited.
        // Energy: 100.0 / 2.0 = 50
        assert_eq!(
            max_brew_attempts([100, 9999, 9999, 9999], [10, 0, 0, 0], 100.0, 10),
            10
        );
    }

    #[test]
    fn max_attempts_limited_by_energy() {
        // Herbs allow 100 attempts. Energy: 5.0 / 1.0 = 5
        assert_eq!(
            max_brew_attempts([1000, 1000, 1000, 1000], [10, 10, 10, 10], 5.0, 5),
            5
        );
    }

    #[test]
    fn max_attempts_zero_herbs() {
        assert_eq!(
            max_brew_attempts([0, 100, 100, 100], [10, 10, 10, 10], 100.0, 5),
            0
        );
    }

    // -- potion_name --

    #[test]
    fn name_normal() {
        assert_eq!(potion_name("Eliksir", BrewQuality::Normal), "Eliksir");
    }

    #[test]
    fn name_special() {
        assert_eq!(
            potion_name("Eliksir", BrewQuality::CraftsmanSpecial),
            "Eliksir (S)"
        );
    }

    #[test]
    fn name_failed() {
        assert_eq!(potion_name("Eliksir", BrewQuality::Failed), "Eliksir (K)");
    }

    // -- AstralPotionTier --

    #[test]
    fn astral_tier_valid() {
        for t in 1..=5 {
            assert!(AstralPotionTier::new(t).is_some());
        }
    }

    #[test]
    fn astral_tier_invalid() {
        assert!(AstralPotionTier::new(0).is_none());
        assert!(AstralPotionTier::new(6).is_none());
    }

    // -- astral herb costs --

    #[test]
    fn astral_tier_1_costs() {
        let t = AstralPotionTier::new(1).unwrap();
        assert_eq!(astral_herb_costs(t), [3000, 1000, 2000, 1000]);
    }

    #[test]
    fn astral_tier_5_costs() {
        let t = AstralPotionTier::new(5).unwrap();
        assert_eq!(astral_herb_costs(t), [12_000, 6000, 8000, 3000]);
    }

    // -- astral energy cost --

    #[test]
    fn astral_energy_tier_3() {
        let t = AstralPotionTier::new(3).unwrap();
        assert_eq!(astral_energy_cost(t), 100);
    }

    // -- astral success chance --

    #[test]
    fn astral_chance_tier_1() {
        let t = AstralPotionTier::new(1).unwrap();
        // floor(200 * 0.3) = 60
        assert_eq!(astral_success_chance(t, 200.0), 60);
    }

    #[test]
    fn astral_chance_capped() {
        let t = AstralPotionTier::new(1).unwrap();
        // floor(400 * 0.3) = 120 → capped at 95
        assert_eq!(astral_success_chance(t, 400.0), 95);
    }

    // -- astral xp range --

    #[test]
    fn astral_xp_tier_2() {
        let t = AstralPotionTier::new(2).unwrap();
        assert_eq!(astral_xp_range(t), (1000, 1500));
    }

    // -- distribute_astral_xp --

    #[test]
    fn astral_xp_distribution() {
        // 1000 / 5 = 200 each
        let (alchemy, per_stat) = distribute_astral_xp(1000);
        assert_eq!(alchemy, 200);
        assert_eq!(per_stat, 200);
    }

    // -- astral failure loss fraction --

    #[test]
    fn failure_loss_craftsman_lucky() {
        assert!((astral_failure_loss_fraction(true, 3) - 0.0).abs() < f64::EPSILON);
    }

    #[test]
    fn failure_loss_craftsman_mid() {
        assert!((astral_failure_loss_fraction(true, 10) - 0.2).abs() < f64::EPSILON);
    }

    #[test]
    fn failure_loss_craftsman_high() {
        assert!((astral_failure_loss_fraction(true, 30) - 0.25).abs() < f64::EPSILON);
    }

    #[test]
    fn failure_loss_craftsman_worst() {
        assert!((astral_failure_loss_fraction(true, 60) - 0.33).abs() < f64::EPSILON);
    }

    #[test]
    fn failure_loss_non_craftsman_worst() {
        assert!((astral_failure_loss_fraction(false, 60) - 0.66).abs() < f64::EPSILON);
    }

    #[test]
    fn failure_loss_non_craftsman_mid() {
        assert!((astral_failure_loss_fraction(false, 10) - 0.4).abs() < f64::EPSILON);
    }

    // -- astral failure herb costs --

    #[test]
    fn failure_costs_no_loss() {
        let t = AstralPotionTier::new(1).unwrap();
        // roll2=3 → fraction 0.0, all costs = 0
        assert_eq!(astral_failure_herb_costs(t, true, 3), [0, 0, 0, 0]);
    }

    #[test]
    fn failure_costs_partial() {
        let t = AstralPotionTier::new(1).unwrap();
        // roll2=60, craftsman → 0.33. ceil(3000*0.33)=990, ceil(1000*0.33)=330,
        // ceil(2000*0.33)=660, ceil(1000*0.33)=330
        let costs = astral_failure_herb_costs(t, true, 60);
        assert_eq!(costs, [990, 330, 660, 330]);
    }

    // -- astral plan name --

    #[test]
    fn plan_name_tier_3() {
        let t = AstralPotionTier::new(3).unwrap();
        assert_eq!(astral_plan_name(t), "R3");
    }

    // -- astral component type --

    #[test]
    fn component_type_tier_1() {
        let t = AstralPotionTier::new(1).unwrap();
        assert_eq!(astral_component_type(t), "T0");
    }

    #[test]
    fn component_type_tier_5() {
        let t = AstralPotionTier::new(5).unwrap();
        assert_eq!(astral_component_type(t), "T4");
    }
}
