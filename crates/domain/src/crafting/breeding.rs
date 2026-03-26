//! Core pet breeding, training, arena combat, exploration, and healing rules.
//!
//! "Cores" are pet creatures that players can collect, breed, train, and battle.
//!
//! ## System overview
//!
//! | Action    | Costs                         | Inputs                       |
//! |-----------|-------------------------------|------------------------------|
//! | License   | 500 gold                      | —                            |
//! | Explore   | 0.1 energy/attempt + platinum | Region choice                |
//! | Breed     | platinum + 15 trains          | Male + female same species   |
//! | Train     | training points               | Core + stat choice + reps    |
//! | Fight     | 0.2 energy                    | Active core vs opponent      |
//! | Heal all  | gold + platinum               | All dead cores revived       |

/// Core pet type/region category.
#[derive(Debug, Clone, Copy, PartialEq, Eq)]
pub enum CoreType {
    Plant,
    Aqua,
    Material,
    Element,
    Alien,
    Ancient,
    Hybrid,
    Secret,
}

impl CoreType {
    pub fn from_db(s: &str) -> Option<Self> {
        match s {
            "Plant" => Some(Self::Plant),
            "Aqua" => Some(Self::Aqua),
            "Material" => Some(Self::Material),
            "Element" => Some(Self::Element),
            "Alien" => Some(Self::Alien),
            "Ancient" => Some(Self::Ancient),
            "Hybrid" => Some(Self::Hybrid),
            "Secret" => Some(Self::Secret),
            _ => None,
        }
    }

    pub fn as_db(&self) -> &'static str {
        match self {
            Self::Plant => "Plant",
            Self::Aqua => "Aqua",
            Self::Material => "Material",
            Self::Element => "Element",
            Self::Alien => "Alien",
            Self::Ancient => "Ancient",
            Self::Hybrid => "Hybrid",
            Self::Secret => "Secret",
        }
    }

    /// Platinum cost to explore in this region.
    pub fn explore_platinum_cost(&self) -> i32 {
        match self {
            Self::Aqua => 50,
            Self::Material => 100,
            Self::Element => 150,
            Self::Alien => 200,
            Self::Ancient => 250,
            Self::Plant | Self::Hybrid | Self::Secret => 0,
        }
    }
}

/// Core gender.
#[derive(Debug, Clone, Copy, PartialEq, Eq)]
pub enum CoreGender {
    Male,
    Female,
}

impl CoreGender {
    pub fn from_db(s: &str) -> Option<Self> {
        match s {
            "M" => Some(Self::Male),
            "F" => Some(Self::Female),
            _ => None,
        }
    }

    pub fn as_db(&self) -> &'static str {
        match self {
            Self::Male => "M",
            Self::Female => "F",
        }
    }
}

/// Core status.
#[derive(Debug, Clone, Copy, PartialEq, Eq)]
pub enum CoreStatus {
    Alive,
    Dead,
}

impl CoreStatus {
    pub fn from_db(s: &str) -> Option<Self> {
        match s {
            "Alive" => Some(Self::Alive),
            "Dead" => Some(Self::Dead),
            _ => None,
        }
    }
}

// ---------------------------------------------------------------------------
// Breeding
// ---------------------------------------------------------------------------

/// Breeding difficulty parameters derived from the `ref_id` column.
///
/// PHP: if `ref_id` is null/0, chance=30, xp=50, ability=0.01.
/// Otherwise: chance = `ref_id`, xp = chance*10, ability = chance/100.
#[derive(Debug, Clone, Copy)]
pub struct BreedingDifficulty {
    /// Breeding check threshold (higher = harder).
    pub chance: i32,
    /// XP rewarded on success.
    pub success_xp: i32,
}

/// Derive breeding difficulty from the `ref_id` column value.
pub fn breeding_difficulty(ref_id: Option<i32>) -> BreedingDifficulty {
    match ref_id.filter(|&v| v > 0) {
        None => BreedingDifficulty {
            chance: 30,
            success_xp: 50,
        },
        Some(c) => BreedingDifficulty {
            chance: c,
            success_xp: c * 10,
        },
    }
}

/// Platinum cost to breed two parents.
///
/// PHP: `ceil((male.power + male.defense + female.power + female.defense) / 4)`
#[allow(clippy::cast_possible_truncation)]
pub fn breeding_platinum_cost(
    male_power: f64,
    male_defense: f64,
    female_power: f64,
    female_defense: f64,
) -> i32 {
    ((male_power + male_defense + female_power + female_defense) / 4.0).ceil() as i32
}

/// Training points required to breed.
pub const BREEDING_TRAIN_COST: i32 = 15;

/// Check if a breeding attempt succeeds.
///
/// PHP: `breeding_skill + rand(1,100)/100 >= chance`
pub fn breeding_succeeds(breeding_skill: f64, chance: i32, roll_1_to_100: i32) -> bool {
    let roll_fraction = f64::from(roll_1_to_100) / 100.0;
    let result = breeding_skill + roll_fraction;
    result >= f64::from(chance)
}

/// Compute offspring stats from parents and breeder skill.
///
/// PHP: `power = min(parent_powers) + breeding_skill`, clamped to `max(parent_powers)`.
/// Same logic for defense.
pub fn offspring_power(parent_a: f64, parent_b: f64, breeding_skill: f64) -> f64 {
    let min_val = parent_a.min(parent_b);
    let max_val = parent_a.max(parent_b);
    (min_val + breeding_skill).min(max_val)
}

/// Compute offspring defense (same formula as power).
pub fn offspring_defense(parent_a: f64, parent_b: f64, breeding_skill: f64) -> f64 {
    offspring_power(parent_a, parent_b, breeding_skill)
}

// ---------------------------------------------------------------------------
// Training
// ---------------------------------------------------------------------------

/// Stat gain from training a core.
///
/// PHP: `gain = reps * 0.125`
pub fn training_stat_gain(reps: i32) -> f64 {
    f64::from(reps) * 0.125
}

// ---------------------------------------------------------------------------
// Arena combat
// ---------------------------------------------------------------------------

/// Compute attack power in core arena combat.
///
/// PHP: `my_power - enemy_defense`, min 0.
pub fn core_attack(my_power: f64, enemy_defense: f64) -> f64 {
    (my_power - enemy_defense).max(0.0)
}

/// Determine arena fight outcome.
///
/// Returns `1` if attacker wins, `-1` if defender wins, `0` for draw.
pub fn arena_outcome(attacker_attack: f64, defender_attack: f64) -> i32 {
    if attacker_attack > defender_attack {
        1
    } else if defender_attack > attacker_attack {
        -1
    } else {
        0
    }
}

/// Maximum gold reward after an arena victory.
///
/// PHP: `ceil((loser.power + loser.defense) * 10)`
#[allow(clippy::cast_possible_truncation)]
pub fn arena_gold_cap(loser_power: f64, loser_defense: f64) -> i32 {
    ((loser_power + loser_defense) * 10.0).ceil() as i32
}

/// Maximum platinum reward after an arena victory.
///
/// PHP: `ceil(gold_cap / 200)`
#[allow(clippy::cast_possible_truncation)]
pub fn arena_platinum_cap(gold_cap: i32) -> i32 {
    (f64::from(gold_cap) / 200.0).ceil() as i32
}

/// Energy cost for one arena fight.
pub const ARENA_ENERGY_COST: f64 = 0.2;

// ---------------------------------------------------------------------------
// Exploration / discovery
// ---------------------------------------------------------------------------

/// Energy cost per exploration attempt.
pub const EXPLORE_ENERGY_PER_ATTEMPT: f64 = 0.1;

/// Exploration find chance per rarity tier.
///
/// PHP uses matched `rand(1,N)` pairs — the chance of a match is `1/N^2`.
///
/// | Tier     | N   | Probability  |
/// |----------|-----|--------------|
/// | Common   | 50  | 1/2500       |
/// | Uncommon | 250 | 1/62500      |
/// | Rare     | 500 | 1/250000     |
#[derive(Debug, Clone, Copy, PartialEq, Eq)]
pub enum ExplorationRarity {
    Common,
    Uncommon,
    Rare,
}

/// Check if an exploration attempt results in a find.
///
/// PHP: `rand(1, tier) == rand(1, tier)` for the given rarity.
/// An external RNG provides `(roll_a, roll_b)` drawn from `1..=tier_size`.
pub fn exploration_find(roll_a: i32, roll_b: i32) -> bool {
    roll_a == roll_b
}

/// Return the die size for each rarity tier.
pub fn exploration_die_size(rarity: ExplorationRarity) -> i32 {
    match rarity {
        ExplorationRarity::Common => 50,
        ExplorationRarity::Uncommon => 250,
        ExplorationRarity::Rare => 500,
    }
}

/// Select the rarity tier for one exploration attempt.
///
/// PHP: `rand(1,3)` → 1 = common, 2 = uncommon, 3 = rare.
pub fn exploration_rarity(roll_1_to_3: i32) -> ExplorationRarity {
    match roll_1_to_3 {
        1 => ExplorationRarity::Common,
        2 => ExplorationRarity::Uncommon,
        _ => ExplorationRarity::Rare,
    }
}

// ---------------------------------------------------------------------------
// Healing
// ---------------------------------------------------------------------------

/// Gold cost to heal all dead cores.
///
/// PHP: `sum((dead.power + dead.defense) * 5)` then `floor(cost)`.
#[allow(clippy::cast_possible_truncation)]
pub fn heal_all_gold_cost(dead_cores: &[(f64, f64)]) -> i32 {
    let total: f64 = dead_cores
        .iter()
        .map(|(power, defense)| (power + defense) * 5.0)
        .sum();
    total.floor() as i32
}

/// Platinum cost to heal all dead cores.
///
/// PHP: `floor(gold_cost / 200)`
pub fn heal_all_platinum_cost(gold_cost: i32) -> i32 {
    gold_cost / 200
}

// ---------------------------------------------------------------------------
// License
// ---------------------------------------------------------------------------

/// Gold cost to purchase a core license.
pub const LICENSE_GOLD_COST: i32 = 500;

#[cfg(test)]
mod tests {
    use super::*;

    // --- Breeding difficulty ---

    #[test]
    fn breeding_difficulty_default() {
        let d = breeding_difficulty(None);
        assert_eq!(d.chance, 30);
        assert_eq!(d.success_xp, 50);
    }

    #[test]
    fn breeding_difficulty_zero_ref() {
        let d = breeding_difficulty(Some(0));
        assert_eq!(d.chance, 30);
        assert_eq!(d.success_xp, 50);
    }

    #[test]
    fn breeding_difficulty_custom() {
        let d = breeding_difficulty(Some(15));
        assert_eq!(d.chance, 15);
        assert_eq!(d.success_xp, 150);
    }

    // --- Breeding cost ---

    #[test]
    fn breeding_cost_even() {
        // (10 + 5 + 8 + 7) / 4 = 30/4 = 7.5 → ceil = 8
        assert_eq!(breeding_platinum_cost(10.0, 5.0, 8.0, 7.0), 8);
    }

    #[test]
    fn breeding_cost_exact() {
        // (4 + 4 + 4 + 4) / 4 = 4
        assert_eq!(breeding_platinum_cost(4.0, 4.0, 4.0, 4.0), 4);
    }

    // --- Breeding success ---

    #[test]
    fn breeding_succeeds_high_skill() {
        // skill=29.5 + roll=50/100=0.5 = 30.0 >= 30 → true
        assert!(breeding_succeeds(29.5, 30, 50));
    }

    #[test]
    fn breeding_fails_low_skill() {
        // skill=1.0 + roll=1/100=0.01 = 1.01 < 30 → false
        assert!(!breeding_succeeds(1.0, 30, 1));
    }

    // --- Offspring stats ---

    #[test]
    fn offspring_power_skill_limited() {
        // parents: 10.0, 20.0; skill 5.0 → min=10 + 5 = 15, max=20 → 15
        assert!((offspring_power(10.0, 20.0, 5.0) - 15.0).abs() < f64::EPSILON);
    }

    #[test]
    fn offspring_power_clamped_to_max() {
        // parents: 10.0, 12.0; skill 5.0 → min=10 + 5 = 15, max=12 → clamped to 12
        assert!((offspring_power(10.0, 12.0, 5.0) - 12.0).abs() < f64::EPSILON);
    }

    #[test]
    fn offspring_power_skill_zero() {
        // skill 0 → just min parent
        assert!((offspring_power(8.0, 12.0, 0.0) - 8.0).abs() < f64::EPSILON);
    }

    // --- Training ---

    #[test]
    fn training_gain_normal() {
        assert!((training_stat_gain(8) - 1.0).abs() < f64::EPSILON);
    }

    #[test]
    fn training_gain_single() {
        assert!((training_stat_gain(1) - 0.125).abs() < f64::EPSILON);
    }

    // --- Arena combat ---

    #[test]
    fn core_attack_positive() {
        assert!((core_attack(15.0, 10.0) - 5.0).abs() < f64::EPSILON);
    }

    #[test]
    fn core_attack_clamped_zero() {
        assert!((core_attack(5.0, 10.0) - 0.0).abs() < f64::EPSILON);
    }

    #[test]
    fn arena_outcome_win() {
        assert_eq!(arena_outcome(10.0, 5.0), 1);
    }

    #[test]
    fn arena_outcome_loss() {
        assert_eq!(arena_outcome(3.0, 8.0), -1);
    }

    #[test]
    fn arena_outcome_draw() {
        assert_eq!(arena_outcome(5.0, 5.0), 0);
    }

    #[test]
    fn arena_gold_cap_value() {
        // (10.5 + 5.5) * 10 = 160.0 → 160
        assert_eq!(arena_gold_cap(10.5, 5.5), 160);
    }

    #[test]
    fn arena_gold_cap_ceil() {
        // (10.3 + 5.2) * 10 = 155.0 → 155
        assert_eq!(arena_gold_cap(10.3, 5.2), 155);
    }

    #[test]
    fn arena_platinum_cap_value() {
        // ceil(160 / 200) = ceil(0.8) = 1
        assert_eq!(arena_platinum_cap(160), 1);
    }

    #[test]
    fn arena_platinum_cap_large() {
        // ceil(1000 / 200) = 5
        assert_eq!(arena_platinum_cap(1000), 5);
    }

    // --- Exploration ---

    #[test]
    fn exploration_find_match() {
        assert!(exploration_find(25, 25));
    }

    #[test]
    fn exploration_find_miss() {
        assert!(!exploration_find(25, 30));
    }

    #[test]
    fn exploration_die_sizes() {
        assert_eq!(exploration_die_size(ExplorationRarity::Common), 50);
        assert_eq!(exploration_die_size(ExplorationRarity::Uncommon), 250);
        assert_eq!(exploration_die_size(ExplorationRarity::Rare), 500);
    }

    #[test]
    fn exploration_rarity_mapping() {
        assert_eq!(exploration_rarity(1), ExplorationRarity::Common);
        assert_eq!(exploration_rarity(2), ExplorationRarity::Uncommon);
        assert_eq!(exploration_rarity(3), ExplorationRarity::Rare);
    }

    // --- Healing ---

    #[test]
    fn heal_cost_empty() {
        assert_eq!(heal_all_gold_cost(&[]), 0);
        assert_eq!(heal_all_platinum_cost(0), 0);
    }

    #[test]
    fn heal_cost_single() {
        // (10.0 + 5.0) * 5 = 75
        assert_eq!(heal_all_gold_cost(&[(10.0, 5.0)]), 75);
        // 75 / 200 = 0.375 → floor = 0
        assert_eq!(heal_all_platinum_cost(75), 0);
    }

    #[test]
    fn heal_cost_multiple() {
        // (10 + 5) * 5 + (20 + 10) * 5 = 75 + 150 = 225
        assert_eq!(heal_all_gold_cost(&[(10.0, 5.0), (20.0, 10.0)]), 225);
        // 225 / 200 = 1.125 → floor = 1
        assert_eq!(heal_all_platinum_cost(225), 1);
    }

    // --- Core type ---

    #[test]
    fn core_type_roundtrip() {
        for s in [
            "Plant", "Aqua", "Material", "Element", "Alien", "Ancient", "Hybrid", "Secret",
        ] {
            let ct = CoreType::from_db(s).unwrap();
            assert_eq!(ct.as_db(), s);
        }
    }

    #[test]
    fn explore_platinum_costs() {
        assert_eq!(CoreType::Plant.explore_platinum_cost(), 0);
        assert_eq!(CoreType::Aqua.explore_platinum_cost(), 50);
        assert_eq!(CoreType::Material.explore_platinum_cost(), 100);
        assert_eq!(CoreType::Element.explore_platinum_cost(), 150);
        assert_eq!(CoreType::Alien.explore_platinum_cost(), 200);
        assert_eq!(CoreType::Ancient.explore_platinum_cost(), 250);
    }

    // --- Gender ---

    #[test]
    fn gender_roundtrip() {
        assert_eq!(CoreGender::from_db("M"), Some(CoreGender::Male));
        assert_eq!(CoreGender::from_db("F"), Some(CoreGender::Female));
        assert_eq!(CoreGender::Male.as_db(), "M");
        assert_eq!(CoreGender::Female.as_db(), "F");
    }
}
