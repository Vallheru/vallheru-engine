//! Player progression mutations — race, class, deity, training, AP bonuses.
//!
//! These are pure domain functions that validate inputs and produce
//! mutation outcomes. They do NOT touch the database; callers persist changes.
//!
//! Ported from PHP: `rasa.php`, `klasa.php`, `deity.php`, `train.php`, `ap.php`.

use crate::location::Location;
use crate::player::bonuses::PlayerBonus;
use crate::player::stats::PlayerStat;
use crate::player::{Class, Race};

// ---------------------------------------------------------------------------
// Race selection
// ---------------------------------------------------------------------------

/// Stat bonuses and max-stat caps for a given race.
///
/// The six entries map to: strength, agility, condition, speed, inteli, wisdom.
/// The seventh entry is HP per condition level (from race).
struct RaceTemplate {
    stat_bonuses: [i32; 6],
    max_stats: [i32; 6],
}

fn race_template(race: &Race) -> RaceTemplate {
    match race {
        Race::Human => RaceTemplate {
            stat_bonuses: [3, 3, 3, 3, 3, 3],
            max_stats: [50, 50, 50, 50, 50, 50],
        },
        Race::Elf => RaceTemplate {
            stat_bonuses: [2, 4, 3, 3, 3, 3],
            max_stats: [40, 60, 50, 55, 50, 50],
        },
        Race::Dwarf => RaceTemplate {
            stat_bonuses: [4, 2, 4, 2, 3, 3],
            max_stats: [60, 40, 60, 40, 50, 50],
        },
        Race::Hobbit => RaceTemplate {
            stat_bonuses: [2, 4, 2, 4, 3, 3],
            max_stats: [40, 60, 45, 60, 50, 50],
        },
        Race::Lizardman => RaceTemplate {
            stat_bonuses: [4, 3, 3, 4, 2, 3],
            max_stats: [60, 50, 50, 60, 40, 50],
        },
        Race::Gnome => RaceTemplate {
            stat_bonuses: [2, 4, 2, 3, 4, 2],
            max_stats: [40, 55, 40, 50, 55, 40],
        },
    }
}

/// Errors that can occur when choosing a race.
#[derive(Debug, Clone, PartialEq, Eq)]
pub enum RaceSelectionError {
    AlreadyChosen,
}

/// Apply race selection to player stats.
///
/// Adds racial stat bonuses and sets max stat caps (`base`).
/// The caller must persist the race on the player row and the updated stats.
///
/// Returns the updated stats (mutated in place), or an error.
pub fn select_race(
    current_race: &str,
    race: &Race,
    stats: &mut [PlayerStat],
) -> Result<(), RaceSelectionError> {
    if !current_race.is_empty() {
        return Err(RaceSelectionError::AlreadyChosen);
    }

    let template = race_template(race);
    let stat_order = [
        "strength",
        "agility",
        "condition",
        "speed",
        "inteli",
        "wisdom",
    ];

    for (i, key) in stat_order.iter().enumerate() {
        if let Some(stat) = stats.iter_mut().find(|s| s.stat_key == *key) {
            stat.base = template.max_stats[i];
            stat.trained += template.stat_bonuses[i];
            stat.modified += template.stat_bonuses[i];
        }
    }

    Ok(())
}

// ---------------------------------------------------------------------------
// Class selection
// ---------------------------------------------------------------------------

/// Stat bonuses for a given class.
///
/// The six entries map to: strength, agility, condition, speed, inteli, wisdom.
/// The seventh entry is HP bonus per condition level (from class).
struct ClassTemplate {
    stat_bonuses: [i32; 6],
    hp_per_condition: i32,
}

fn class_template(class: &Class) -> ClassTemplate {
    match class {
        Class::Warrior => ClassTemplate {
            stat_bonuses: [1, 1, 1, 0, -1, 0],
            hp_per_condition: 5,
        },
        Class::Mage => ClassTemplate {
            stat_bonuses: [0, 0, 0, 0, 1, 1],
            hp_per_condition: 3,
        },
        Class::Craftsman => ClassTemplate {
            stat_bonuses: [0, 0, 0, 0, 0, 0],
            hp_per_condition: 2,
        },
        Class::Barbarian => ClassTemplate {
            stat_bonuses: [1, 1, 1, 0, -1, 1],
            hp_per_condition: 5,
        },
        Class::Thief => ClassTemplate {
            stat_bonuses: [0, 1, -1, 1, 1, -1],
            hp_per_condition: 4,
        },
    }
}

/// Errors that can occur when choosing a class.
#[derive(Debug, Clone, PartialEq, Eq)]
pub enum ClassSelectionError {
    AlreadyChosen,
}

/// Apply class selection to player stats.
///
/// Adds class stat bonuses. The caller must persist the class on the player
/// row and the updated stats.
pub fn select_class(
    current_class: &str,
    class: &Class,
    stats: &mut [PlayerStat],
) -> Result<(), ClassSelectionError> {
    if !current_class.is_empty() {
        return Err(ClassSelectionError::AlreadyChosen);
    }

    let template = class_template(class);
    let stat_order = [
        "strength",
        "agility",
        "condition",
        "speed",
        "inteli",
        "wisdom",
    ];

    for (i, key) in stat_order.iter().enumerate() {
        if let Some(stat) = stats.iter_mut().find(|s| s.stat_key == *key) {
            stat.trained += template.stat_bonuses[i];
            stat.modified += template.stat_bonuses[i];
        }
    }

    Ok(())
}

/// Get the HP per condition level-up for a class (used in rasa.php/klasa.php display).
pub fn class_hp_per_condition(class: &Class) -> i32 {
    class_template(class).hp_per_condition
}

// ---------------------------------------------------------------------------
// Deity selection and change
// ---------------------------------------------------------------------------

/// The eight deities available in-game.
#[derive(Debug, Clone, PartialEq, Eq)]
pub enum Deity {
    Illuminati,
    Karserth,
    Anariel,
    Heluvald,
    Tartus,
    Oregarl,
    Daeraell,
    TeatheDi,
}

impl Deity {
    /// Parse from the URL slug used in the PHP system.
    pub fn from_slug(s: &str) -> Option<Self> {
        match s {
            "illuminati" => Some(Self::Illuminati),
            "karserth" => Some(Self::Karserth),
            "anariel" => Some(Self::Anariel),
            "heluvald" => Some(Self::Heluvald),
            "tartus" => Some(Self::Tartus),
            "oregarl" => Some(Self::Oregarl),
            "daeraell" => Some(Self::Daeraell),
            "teathedi" => Some(Self::TeatheDi),
            _ => None,
        }
    }

    /// Database string (matches PHP `$arrDeityname`).
    pub fn to_db(&self) -> &'static str {
        match self {
            Self::Illuminati => "Illuminati",
            Self::Karserth => "Karserth",
            Self::Anariel => "Anariel",
            Self::Heluvald => "Heluvald",
            Self::Tartus => "Tartus",
            Self::Oregarl => "Oregarl",
            Self::Daeraell => "Daeraell",
            Self::TeatheDi => "Teathe-di",
        }
    }
}

/// Errors that can occur when selecting a deity.
#[derive(Debug, Clone, PartialEq, Eq)]
pub enum DeitySelectionError {
    AlreadyHasDeity,
    InvalidDeity,
}

/// Select a deity for the first time.
///
/// Returns the deity DB string. Caller must persist.
pub fn select_deity(
    current_deity: &Option<String>,
    slug: &str,
) -> Result<String, DeitySelectionError> {
    if current_deity.as_ref().is_some_and(|d| !d.is_empty()) {
        return Err(DeitySelectionError::AlreadyHasDeity);
    }

    let deity = Deity::from_slug(slug).ok_or(DeitySelectionError::InvalidDeity)?;
    Ok(deity.to_db().to_owned())
}

/// Errors that can occur when changing a deity.
#[derive(Debug, Clone, PartialEq, Eq)]
pub enum DeityChangeError {
    NoDeityToChange,
    InsufficientPw { cost: i32 },
}

/// Calculate the cost to change deity: `100 * 2^change_count`.
#[allow(clippy::cast_sign_loss)]
pub fn deity_change_cost(change_count: i32) -> i32 {
    100 * 2_i32.saturating_pow(change_count as u32)
}

/// Validate a deity change. Returns the cost if successful.
///
/// Caller must: clear deity, deduct pw, increment `change_deity` counter,
/// then the player can pick a new deity.
pub fn validate_deity_change(
    current_deity: &Option<String>,
    change_count: i32,
    current_pw: i32,
) -> Result<i32, DeityChangeError> {
    if current_deity.as_ref().is_none_or(String::is_empty) {
        return Err(DeityChangeError::NoDeityToChange);
    }

    let cost = deity_change_cost(change_count);
    if current_pw < cost {
        return Err(DeityChangeError::InsufficientPw { cost });
    }

    Ok(cost)
}

// ---------------------------------------------------------------------------
// Training
// ---------------------------------------------------------------------------

/// Energy multiplier for training a stat, determined by race and class.
///
/// Returns the energy cost per training repetition.
/// The base rule is from `train.php`'s nested race/class conditionals.
pub fn training_energy_multiplier(race: &Race, class: &Class, stat_key: &str) -> f64 {
    // Mental stats (inteli/wisdom) use class-based multiplier
    if stat_key == "inteli" || stat_key == "wisdom" {
        // Gnome wisdom override: always 0.4
        if *race == Race::Gnome && stat_key == "wisdom" {
            return 0.4;
        }
        return match class {
            Class::Warrior | Class::Barbarian => 0.06,
            Class::Mage => 0.2,
            Class::Craftsman | Class::Thief => 0.3,
        };
    }

    // Physical stats use race-based multiplier
    match race {
        Race::Human => 0.3,
        Race::Elf => match stat_key {
            "strength" | "condition" => 0.4,
            "agility" | "speed" => 0.2,
            _ => 0.3,
        },
        Race::Dwarf => match stat_key {
            "strength" | "condition" => 0.2,
            "agility" | "speed" => 0.4,
            _ => 0.3,
        },
        Race::Hobbit => match stat_key {
            "agility" | "condition" => 0.2,
            "strength" | "speed" => 0.4,
            _ => 0.3,
        },
        Race::Lizardman => match stat_key {
            "strength" | "speed" => 0.2,
            "agility" | "condition" => 0.4,
            _ => 0.3,
        },
        Race::Gnome => match stat_key {
            "strength" | "speed" => 0.4,
            _ => 0.3,
        },
    }
}

/// Gold cost per training repetition for a stat.
///
/// Normal: `ceil(trained * 20)`
/// Ardulith discount (inteli/wisdom only): `ceil(trained * 20 - trained * 20 / 10)`
///   = `ceil(trained * 18)`
#[allow(clippy::cast_possible_truncation)]
pub fn training_gold_cost_per_rep(trained_value: i32, stat_key: &str, location: &Location) -> i32 {
    let base = f64::from(trained_value) * 20.0;
    if *location == Location::Ardulith && (stat_key == "inteli" || stat_key == "wisdom") {
        (base - base / 10.0).ceil() as i32
    } else {
        base.ceil() as i32
    }
}

/// Errors that can occur during training.
#[derive(Debug, Clone, PartialEq, Eq)]
pub enum TrainingError {
    NotInCity,
    Dead,
    NoRace,
    NoClass,
    InvalidStat,
    AtCap,
    InvalidRepetitions,
    InsufficientEnergy,
    InsufficientGold { needed: i32 },
}

/// Result of a successful training action.
#[derive(Debug, Clone, PartialEq)]
pub struct TrainingResult {
    /// XP gained (repetitions * 5).
    pub xp_gained: i32,
    /// Total gold spent.
    pub gold_cost: i32,
    /// Total energy spent.
    pub energy_cost: f64,
}

/// Validate and calculate a training action.
///
/// Does NOT apply XP (caller uses `apply_stat_xp` from progression module).
/// Returns the costs and XP to apply if valid.
#[allow(clippy::too_many_arguments)]
pub fn validate_training(
    location: &Location,
    hp: i32,
    race: &Race,
    class: &Class,
    stat_key: &str,
    trained_value: i32,
    base_value: i32,
    repetitions: i32,
    current_energy: f64,
    current_gold: i32,
) -> Result<TrainingResult, TrainingError> {
    // Must be in a city
    if !location.is_city() {
        return Err(TrainingError::NotInCity);
    }

    // Must be alive
    if hp == 0 {
        return Err(TrainingError::Dead);
    }

    // Validate stat key
    let valid_stats = [
        "strength",
        "agility",
        "inteli",
        "speed",
        "condition",
        "wisdom",
    ];
    if !valid_stats.contains(&stat_key) {
        return Err(TrainingError::InvalidStat);
    }

    // Can't train at cap
    if base_value > 0 && trained_value >= base_value {
        return Err(TrainingError::AtCap);
    }

    if repetitions <= 0 {
        return Err(TrainingError::InvalidRepetitions);
    }

    let multiplier = training_energy_multiplier(race, class, stat_key);
    let energy_cost = round_to_one_decimal(f64::from(repetitions) * multiplier);
    let gold_per_rep = training_gold_cost_per_rep(trained_value, stat_key, location);
    let gold_cost = gold_per_rep * repetitions;

    if energy_cost > current_energy {
        return Err(TrainingError::InsufficientEnergy);
    }

    if gold_cost > current_gold {
        return Err(TrainingError::InsufficientGold { needed: gold_cost });
    }

    let xp_gained = repetitions * 5;

    Ok(TrainingResult {
        xp_gained,
        gold_cost,
        energy_cost,
    })
}

/// Round to one decimal place (matching PHP `round($val, 1)`).
fn round_to_one_decimal(val: f64) -> f64 {
    (val * 10.0).round() / 10.0
}

// ---------------------------------------------------------------------------
// AP bonus purchasing
// ---------------------------------------------------------------------------

/// A bonus catalog entry (from the `bonuses` table).
#[derive(Debug, Clone)]
pub struct BonusCatalogEntry {
    pub id: i32,
    pub name: String,
    pub cost: i32,
    pub max_levels: i32,
    pub trigger_key: String,
    pub bonus_magnitude: i32,
    /// Comma-separated list of allowed races, or "All".
    pub race_restriction: String,
    /// Comma-separated list of allowed classes, or "All".
    pub class_restriction: String,
}

/// Errors that can occur when purchasing an AP bonus.
#[derive(Debug, Clone, PartialEq, Eq)]
pub enum ApBonusError {
    NoRaceOrClass,
    BonusNotFound,
    RaceNotAllowed,
    ClassNotAllowed,
    AtMaxLevel,
    InsufficientAp { cost: i32 },
}

/// Check if a race/class string restriction allows the given value.
///
/// The PHP code uses `strpos($field, $value)` — substring match.
/// Values are like `"Człowiek,Elf"` or `"All"`.
fn restriction_allows(restriction: &str, value: &str) -> bool {
    if restriction.contains("All") {
        return true;
    }
    restriction.contains(value)
}

/// Calculate the cost to purchase the next level of a bonus.
///
/// - First purchase: `base_cost`
/// - Upgrade: `base_cost * (current_level + 1)`
pub fn ap_bonus_cost(base_cost: i32, current_level: i32) -> i32 {
    if current_level == 0 {
        base_cost
    } else {
        base_cost * (current_level + 1)
    }
}

/// Result of a successful AP bonus purchase.
#[derive(Debug, Clone, PartialEq, Eq)]
pub struct ApBonusResult {
    /// AP cost deducted.
    pub ap_cost: i32,
    /// The catalog entry that was purchased.
    pub catalog_id: i32,
    /// New level of the bonus after purchase.
    pub new_level: i32,
    /// The trigger key of the bonus.
    pub trigger_key: String,
    /// The bonus magnitude per level.
    pub bonus_magnitude: i32,
    /// Whether this was a new bonus (vs upgrade).
    pub is_new: bool,
}

/// Validate and calculate an AP bonus purchase.
///
/// On success, the caller must:
/// - Deduct `result.ap_cost` from player AP
/// - If `result.is_new`: insert new `PlayerBonus`
/// - Else: increment existing bonus value
pub fn validate_ap_purchase(
    race_db: &str,
    class_db: &str,
    current_ap: i32,
    catalog_entry: &BonusCatalogEntry,
    player_bonuses: &[PlayerBonus],
) -> Result<ApBonusResult, ApBonusError> {
    if race_db.is_empty() || class_db.is_empty() {
        return Err(ApBonusError::NoRaceOrClass);
    }

    // Check race restriction
    if !restriction_allows(&catalog_entry.race_restriction, race_db) {
        return Err(ApBonusError::RaceNotAllowed);
    }

    // Check class restriction
    if !restriction_allows(&catalog_entry.class_restriction, class_db) {
        return Err(ApBonusError::ClassNotAllowed);
    }

    // Find current level from player's bonuses
    let current_level = player_bonuses
        .iter()
        .find(|b| b.catalog_id == catalog_entry.id)
        .map_or(0, |b| b.value);

    // Check max level
    if current_level >= catalog_entry.max_levels {
        return Err(ApBonusError::AtMaxLevel);
    }

    // Calculate cost
    let cost = ap_bonus_cost(catalog_entry.cost, current_level);

    if current_ap < cost {
        return Err(ApBonusError::InsufficientAp { cost });
    }

    Ok(ApBonusResult {
        ap_cost: cost,
        catalog_id: catalog_entry.id,
        new_level: current_level + 1,
        trigger_key: catalog_entry.trigger_key.clone(),
        bonus_magnitude: catalog_entry.bonus_magnitude,
        is_new: current_level == 0,
    })
}

/// Filter bonus catalog entries that are available to a player based on race/class.
///
/// Also computes the current level and cost for each available bonus.
pub fn available_bonuses<'a>(
    race_db: &str,
    class_db: &str,
    catalog: &'a [BonusCatalogEntry],
    player_bonuses: &[PlayerBonus],
) -> Vec<AvailableBonus<'a>> {
    catalog
        .iter()
        .filter_map(|entry| {
            if !restriction_allows(&entry.race_restriction, race_db) {
                return None;
            }
            if !restriction_allows(&entry.class_restriction, class_db) {
                return None;
            }

            let current_level = player_bonuses
                .iter()
                .find(|b| b.catalog_id == entry.id)
                .map_or(0, |b| b.value);

            // Skip if already at max
            if current_level >= entry.max_levels {
                return None;
            }

            let cost = ap_bonus_cost(entry.cost, current_level);

            Some(AvailableBonus {
                entry,
                current_level,
                cost,
            })
        })
        .collect()
}

/// A bonus catalog entry that is available for the player to purchase.
#[derive(Debug, Clone)]
pub struct AvailableBonus<'a> {
    pub entry: &'a BonusCatalogEntry,
    pub current_level: i32,
    pub cost: i32,
}

// ---------------------------------------------------------------------------
// Tests
// ---------------------------------------------------------------------------

#[cfg(test)]
mod tests {
    use super::*;
    use crate::player::stats::default_stats;

    // -- Race selection --

    #[test]
    fn select_human_race() {
        let mut stats = default_stats();
        select_race("", &Race::Human, &mut stats).unwrap();

        let s = |key: &str| stats.iter().find(|s| s.stat_key == key).unwrap();
        assert_eq!(s("strength").trained, 3);
        assert_eq!(s("strength").base, 50);
        assert_eq!(s("agility").trained, 3);
        assert_eq!(s("agility").base, 50);
        assert_eq!(s("condition").trained, 3);
        assert_eq!(s("condition").base, 50);
        assert_eq!(s("speed").trained, 3);
        assert_eq!(s("speed").base, 50);
        assert_eq!(s("inteli").trained, 3);
        assert_eq!(s("inteli").base, 50);
        assert_eq!(s("wisdom").trained, 3);
        assert_eq!(s("wisdom").base, 50);
    }

    #[test]
    fn select_elf_race() {
        let mut stats = default_stats();
        select_race("", &Race::Elf, &mut stats).unwrap();

        let s = |key: &str| stats.iter().find(|s| s.stat_key == key).unwrap();
        assert_eq!(s("strength").trained, 2);
        assert_eq!(s("strength").base, 40);
        assert_eq!(s("agility").trained, 4);
        assert_eq!(s("agility").base, 60);
    }

    #[test]
    fn select_dwarf_race() {
        let mut stats = default_stats();
        select_race("", &Race::Dwarf, &mut stats).unwrap();

        let s = |key: &str| stats.iter().find(|s| s.stat_key == key).unwrap();
        assert_eq!(s("strength").trained, 4);
        assert_eq!(s("strength").base, 60);
        assert_eq!(s("agility").trained, 2);
        assert_eq!(s("agility").base, 40);
        assert_eq!(s("condition").trained, 4);
        assert_eq!(s("condition").base, 60);
        assert_eq!(s("speed").trained, 2);
        assert_eq!(s("speed").base, 40);
    }

    #[test]
    fn select_gnome_race() {
        let mut stats = default_stats();
        select_race("", &Race::Gnome, &mut stats).unwrap();

        let s = |key: &str| stats.iter().find(|s| s.stat_key == key).unwrap();
        assert_eq!(s("strength").trained, 2);
        assert_eq!(s("strength").base, 40);
        assert_eq!(s("agility").trained, 4);
        assert_eq!(s("agility").base, 55);
        assert_eq!(s("inteli").trained, 4);
        assert_eq!(s("inteli").base, 55);
        assert_eq!(s("wisdom").trained, 2);
        assert_eq!(s("wisdom").base, 40);
    }

    #[test]
    fn race_already_chosen() {
        let mut stats = default_stats();
        let err = select_race("Człowiek", &Race::Elf, &mut stats).unwrap_err();
        assert_eq!(err, RaceSelectionError::AlreadyChosen);
    }

    // -- Class selection --

    #[test]
    fn select_warrior_class() {
        let mut stats = default_stats();
        // Pre-set some trained values (race applied previously)
        for s in &mut stats {
            s.trained = 3;
            s.modified = 3;
        }
        select_class("", &Class::Warrior, &mut stats).unwrap();

        let s = |key: &str| stats.iter().find(|s| s.stat_key == key).unwrap();
        assert_eq!(s("strength").trained, 4);
        assert_eq!(s("agility").trained, 4);
        assert_eq!(s("condition").trained, 4);
        assert_eq!(s("speed").trained, 3);
        assert_eq!(s("inteli").trained, 2);
        assert_eq!(s("wisdom").trained, 3);
    }

    #[test]
    fn select_mage_class() {
        let mut stats = default_stats();
        for s in &mut stats {
            s.trained = 3;
            s.modified = 3;
        }
        select_class("", &Class::Mage, &mut stats).unwrap();

        let s = |key: &str| stats.iter().find(|s| s.stat_key == key).unwrap();
        assert_eq!(s("inteli").trained, 4);
        assert_eq!(s("wisdom").trained, 4);
        assert_eq!(s("strength").trained, 3);
    }

    #[test]
    fn select_thief_class() {
        let mut stats = default_stats();
        for s in &mut stats {
            s.trained = 3;
            s.modified = 3;
        }
        select_class("", &Class::Thief, &mut stats).unwrap();

        let s = |key: &str| stats.iter().find(|s| s.stat_key == key).unwrap();
        assert_eq!(s("agility").trained, 4);
        assert_eq!(s("condition").trained, 2);
        assert_eq!(s("speed").trained, 4);
        assert_eq!(s("inteli").trained, 4);
        assert_eq!(s("wisdom").trained, 2);
    }

    #[test]
    fn select_barbarian_class() {
        let mut stats = default_stats();
        for s in &mut stats {
            s.trained = 3;
            s.modified = 3;
        }
        select_class("", &Class::Barbarian, &mut stats).unwrap();

        let s = |key: &str| stats.iter().find(|s| s.stat_key == key).unwrap();
        assert_eq!(s("strength").trained, 4);
        assert_eq!(s("agility").trained, 4);
        assert_eq!(s("condition").trained, 4);
        assert_eq!(s("inteli").trained, 2);
        assert_eq!(s("wisdom").trained, 4);
    }

    #[test]
    fn class_already_chosen() {
        let mut stats = default_stats();
        let err = select_class("Wojownik", &Class::Mage, &mut stats).unwrap_err();
        assert_eq!(err, ClassSelectionError::AlreadyChosen);
    }

    #[test]
    fn class_hp_per_condition_values() {
        assert_eq!(class_hp_per_condition(&Class::Warrior), 5);
        assert_eq!(class_hp_per_condition(&Class::Barbarian), 5);
        assert_eq!(class_hp_per_condition(&Class::Thief), 4);
        assert_eq!(class_hp_per_condition(&Class::Mage), 3);
        assert_eq!(class_hp_per_condition(&Class::Craftsman), 2);
    }

    // -- Deity --

    #[test]
    fn deity_from_slug_all_valid() {
        assert_eq!(
            Deity::from_slug("illuminati").unwrap().to_db(),
            "Illuminati"
        );
        assert_eq!(Deity::from_slug("karserth").unwrap().to_db(), "Karserth");
        assert_eq!(Deity::from_slug("anariel").unwrap().to_db(), "Anariel");
        assert_eq!(Deity::from_slug("heluvald").unwrap().to_db(), "Heluvald");
        assert_eq!(Deity::from_slug("tartus").unwrap().to_db(), "Tartus");
        assert_eq!(Deity::from_slug("oregarl").unwrap().to_db(), "Oregarl");
        assert_eq!(Deity::from_slug("daeraell").unwrap().to_db(), "Daeraell");
        assert_eq!(Deity::from_slug("teathedi").unwrap().to_db(), "Teathe-di");
    }

    #[test]
    fn deity_from_invalid_slug() {
        assert!(Deity::from_slug("invalid").is_none());
    }

    #[test]
    fn select_deity_ok() {
        let result = select_deity(&None, "illuminati").unwrap();
        assert_eq!(result, "Illuminati");
    }

    #[test]
    fn select_deity_empty_string_ok() {
        let result = select_deity(&Some(String::new()), "karserth").unwrap();
        assert_eq!(result, "Karserth");
    }

    #[test]
    fn select_deity_already_has() {
        let err = select_deity(&Some("Illuminati".to_owned()), "karserth").unwrap_err();
        assert_eq!(err, DeitySelectionError::AlreadyHasDeity);
    }

    #[test]
    fn select_deity_invalid() {
        let err = select_deity(&None, "bogus").unwrap_err();
        assert_eq!(err, DeitySelectionError::InvalidDeity);
    }

    #[test]
    fn deity_change_cost_scaling() {
        assert_eq!(deity_change_cost(0), 100);
        assert_eq!(deity_change_cost(1), 200);
        assert_eq!(deity_change_cost(2), 400);
        assert_eq!(deity_change_cost(3), 800);
    }

    #[test]
    fn validate_deity_change_ok() {
        let cost = validate_deity_change(&Some("Illuminati".to_owned()), 0, 150).unwrap();
        assert_eq!(cost, 100);
    }

    #[test]
    fn validate_deity_change_no_deity() {
        let err = validate_deity_change(&None, 0, 500).unwrap_err();
        assert_eq!(err, DeityChangeError::NoDeityToChange);
    }

    #[test]
    fn validate_deity_change_insufficient_pw() {
        let err = validate_deity_change(&Some("Illuminati".to_owned()), 2, 300).unwrap_err();
        assert_eq!(err, DeityChangeError::InsufficientPw { cost: 400 });
    }

    // -- Training energy multipliers --

    #[test]
    fn human_uniform_energy() {
        assert!(
            (training_energy_multiplier(&Race::Human, &Class::Warrior, "strength") - 0.3).abs()
                < f64::EPSILON
        );
        assert!(
            (training_energy_multiplier(&Race::Human, &Class::Warrior, "agility") - 0.3).abs()
                < f64::EPSILON
        );
        assert!(
            (training_energy_multiplier(&Race::Human, &Class::Warrior, "condition") - 0.3).abs()
                < f64::EPSILON
        );
        assert!(
            (training_energy_multiplier(&Race::Human, &Class::Warrior, "speed") - 0.3).abs()
                < f64::EPSILON
        );
    }

    #[test]
    fn elf_physical_energy() {
        assert!(
            (training_energy_multiplier(&Race::Elf, &Class::Warrior, "strength") - 0.4).abs()
                < f64::EPSILON
        );
        assert!(
            (training_energy_multiplier(&Race::Elf, &Class::Warrior, "agility") - 0.2).abs()
                < f64::EPSILON
        );
        assert!(
            (training_energy_multiplier(&Race::Elf, &Class::Warrior, "condition") - 0.4).abs()
                < f64::EPSILON
        );
        assert!(
            (training_energy_multiplier(&Race::Elf, &Class::Warrior, "speed") - 0.2).abs()
                < f64::EPSILON
        );
    }

    #[test]
    fn dwarf_physical_energy() {
        assert!(
            (training_energy_multiplier(&Race::Dwarf, &Class::Warrior, "strength") - 0.2).abs()
                < f64::EPSILON
        );
        assert!(
            (training_energy_multiplier(&Race::Dwarf, &Class::Warrior, "agility") - 0.4).abs()
                < f64::EPSILON
        );
    }

    #[test]
    fn mental_stat_energy_by_class() {
        // Warrior/Barbarian: very low (0.06 — PHP says 0.06 despite displaying as 0.4 for warrior)
        assert!(
            (training_energy_multiplier(&Race::Human, &Class::Warrior, "inteli") - 0.06).abs()
                < f64::EPSILON
        );
        assert!(
            (training_energy_multiplier(&Race::Human, &Class::Barbarian, "wisdom") - 0.06).abs()
                < f64::EPSILON
        );
        // Mage: 0.2
        assert!(
            (training_energy_multiplier(&Race::Human, &Class::Mage, "inteli") - 0.2).abs()
                < f64::EPSILON
        );
        // Craftsman/Thief: 0.3
        assert!(
            (training_energy_multiplier(&Race::Human, &Class::Craftsman, "wisdom") - 0.3).abs()
                < f64::EPSILON
        );
        assert!(
            (training_energy_multiplier(&Race::Human, &Class::Thief, "inteli") - 0.3).abs()
                < f64::EPSILON
        );
    }

    #[test]
    fn gnome_wisdom_override() {
        // Gnome wisdom is always 0.4, regardless of class
        assert!(
            (training_energy_multiplier(&Race::Gnome, &Class::Mage, "wisdom") - 0.4).abs()
                < f64::EPSILON
        );
        assert!(
            (training_energy_multiplier(&Race::Gnome, &Class::Warrior, "wisdom") - 0.4).abs()
                < f64::EPSILON
        );
    }

    #[test]
    fn lizardman_physical_energy() {
        assert!(
            (training_energy_multiplier(&Race::Lizardman, &Class::Warrior, "strength") - 0.2).abs()
                < f64::EPSILON
        );
        assert!(
            (training_energy_multiplier(&Race::Lizardman, &Class::Warrior, "speed") - 0.2).abs()
                < f64::EPSILON
        );
        assert!(
            (training_energy_multiplier(&Race::Lizardman, &Class::Warrior, "agility") - 0.4).abs()
                < f64::EPSILON
        );
        assert!(
            (training_energy_multiplier(&Race::Lizardman, &Class::Warrior, "condition") - 0.4)
                .abs()
                < f64::EPSILON
        );
    }

    #[test]
    fn hobbit_physical_energy() {
        assert!(
            (training_energy_multiplier(&Race::Hobbit, &Class::Warrior, "agility") - 0.2).abs()
                < f64::EPSILON
        );
        assert!(
            (training_energy_multiplier(&Race::Hobbit, &Class::Warrior, "condition") - 0.2).abs()
                < f64::EPSILON
        );
        assert!(
            (training_energy_multiplier(&Race::Hobbit, &Class::Warrior, "strength") - 0.4).abs()
                < f64::EPSILON
        );
        assert!(
            (training_energy_multiplier(&Race::Hobbit, &Class::Warrior, "speed") - 0.4).abs()
                < f64::EPSILON
        );
    }

    // -- Training gold cost --

    #[test]
    fn gold_cost_basic() {
        let cost = training_gold_cost_per_rep(10, "strength", &Location::Altara);
        assert_eq!(cost, 200); // 10 * 20
    }

    #[test]
    fn gold_cost_ardulith_inteli_discount() {
        let cost = training_gold_cost_per_rep(10, "inteli", &Location::Ardulith);
        // 10*20 = 200, discount = 200/10 = 20, result = ceil(180) = 180
        assert_eq!(cost, 180);
    }

    #[test]
    fn gold_cost_ardulith_no_discount_strength() {
        let cost = training_gold_cost_per_rep(10, "strength", &Location::Ardulith);
        assert_eq!(cost, 200); // No discount for physical stats
    }

    // -- Training validation --

    #[test]
    fn training_ok() {
        let result = validate_training(
            &Location::Altara,
            100,
            &Race::Human,
            &Class::Warrior,
            "strength",
            10,
            50,
            5,
            10.0,
            10000,
        )
        .unwrap();
        assert_eq!(result.xp_gained, 25); // 5 * 5
        assert_eq!(result.gold_cost, 1000); // 200 * 5
        assert!((result.energy_cost - 1.5).abs() < f64::EPSILON); // 5 * 0.3
    }

    #[test]
    fn training_not_in_city() {
        let err = validate_training(
            &Location::Forest,
            100,
            &Race::Human,
            &Class::Warrior,
            "strength",
            10,
            50,
            5,
            10.0,
            10000,
        )
        .unwrap_err();
        assert_eq!(err, TrainingError::NotInCity);
    }

    #[test]
    fn training_dead() {
        let err = validate_training(
            &Location::Altara,
            0,
            &Race::Human,
            &Class::Warrior,
            "strength",
            10,
            50,
            5,
            10.0,
            10000,
        )
        .unwrap_err();
        assert_eq!(err, TrainingError::Dead);
    }

    #[test]
    fn training_at_cap() {
        let err = validate_training(
            &Location::Altara,
            100,
            &Race::Human,
            &Class::Warrior,
            "strength",
            50,
            50,
            5,
            10.0,
            10000,
        )
        .unwrap_err();
        assert_eq!(err, TrainingError::AtCap);
    }

    #[test]
    fn training_insufficient_energy() {
        let err = validate_training(
            &Location::Altara,
            100,
            &Race::Human,
            &Class::Warrior,
            "strength",
            10,
            50,
            50,
            1.0,
            50000,
        )
        .unwrap_err();
        assert_eq!(err, TrainingError::InsufficientEnergy);
    }

    #[test]
    fn training_insufficient_gold() {
        let err = validate_training(
            &Location::Altara,
            100,
            &Race::Human,
            &Class::Warrior,
            "strength",
            10,
            50,
            5,
            10.0,
            100,
        )
        .unwrap_err();
        assert_eq!(err, TrainingError::InsufficientGold { needed: 1000 });
    }

    // -- AP bonus purchasing --

    fn test_catalog_entry() -> BonusCatalogEntry {
        BonusCatalogEntry {
            id: 1,
            name: "Test Bonus".to_owned(),
            cost: 10,
            max_levels: 5,
            trigger_key: "strength".to_owned(),
            bonus_magnitude: 5,
            race_restriction: "All".to_owned(),
            class_restriction: "All".to_owned(),
        }
    }

    #[test]
    fn ap_purchase_new_bonus() {
        let entry = test_catalog_entry();
        let result = validate_ap_purchase("Człowiek", "Wojownik", 50, &entry, &[]).unwrap();
        assert_eq!(result.ap_cost, 10);
        assert_eq!(result.new_level, 1);
        assert!(result.is_new);
    }

    #[test]
    fn ap_purchase_upgrade() {
        let entry = test_catalog_entry();
        let existing = vec![PlayerBonus {
            id: 1,
            catalog_id: 1,
            bonus_name: "strength".to_owned(),
            value: 2,
            duration: 5,
        }];
        let result = validate_ap_purchase("Człowiek", "Wojownik", 50, &entry, &existing).unwrap();
        assert_eq!(result.ap_cost, 30); // 10 * (2 + 1)
        assert_eq!(result.new_level, 3);
        assert!(!result.is_new);
    }

    #[test]
    fn ap_purchase_at_max() {
        let entry = test_catalog_entry();
        let existing = vec![PlayerBonus {
            id: 1,
            catalog_id: 1,
            bonus_name: "strength".to_owned(),
            value: 5,
            duration: 5,
        }];
        let err = validate_ap_purchase("Człowiek", "Wojownik", 100, &entry, &existing).unwrap_err();
        assert_eq!(err, ApBonusError::AtMaxLevel);
    }

    #[test]
    fn ap_purchase_insufficient_ap() {
        let entry = test_catalog_entry();
        let err = validate_ap_purchase("Człowiek", "Wojownik", 5, &entry, &[]).unwrap_err();
        assert_eq!(err, ApBonusError::InsufficientAp { cost: 10 });
    }

    #[test]
    fn ap_purchase_race_restricted() {
        let mut entry = test_catalog_entry();
        entry.race_restriction = "Elf,Krasnolud".to_owned();
        let err = validate_ap_purchase("Człowiek", "Wojownik", 50, &entry, &[]).unwrap_err();
        assert_eq!(err, ApBonusError::RaceNotAllowed);
    }

    #[test]
    fn ap_purchase_class_restricted() {
        let mut entry = test_catalog_entry();
        entry.class_restriction = "Mag".to_owned();
        let err = validate_ap_purchase("Człowiek", "Wojownik", 50, &entry, &[]).unwrap_err();
        assert_eq!(err, ApBonusError::ClassNotAllowed);
    }

    #[test]
    fn ap_purchase_no_race() {
        let entry = test_catalog_entry();
        let err = validate_ap_purchase("", "Wojownik", 50, &entry, &[]).unwrap_err();
        assert_eq!(err, ApBonusError::NoRaceOrClass);
    }

    #[test]
    fn ap_bonus_cost_formula() {
        assert_eq!(ap_bonus_cost(10, 0), 10); // First purchase
        assert_eq!(ap_bonus_cost(10, 1), 20); // 10 * (1+1)
        assert_eq!(ap_bonus_cost(10, 2), 30); // 10 * (2+1)
        assert_eq!(ap_bonus_cost(10, 4), 50); // 10 * (4+1)
    }

    #[test]
    fn available_bonuses_filters() {
        let catalog = vec![
            BonusCatalogEntry {
                id: 1,
                name: "For All".to_owned(),
                cost: 10,
                max_levels: 5,
                trigger_key: "test".to_owned(),
                bonus_magnitude: 5,
                race_restriction: "All".to_owned(),
                class_restriction: "All".to_owned(),
            },
            BonusCatalogEntry {
                id: 2,
                name: "Elf Only".to_owned(),
                cost: 20,
                max_levels: 3,
                trigger_key: "test2".to_owned(),
                bonus_magnitude: 10,
                race_restriction: "Elf".to_owned(),
                class_restriction: "All".to_owned(),
            },
        ];

        let available = available_bonuses("Człowiek", "Wojownik", &catalog, &[]);
        assert_eq!(available.len(), 1);
        assert_eq!(available[0].entry.id, 1);
        assert_eq!(available[0].current_level, 0);
        assert_eq!(available[0].cost, 10);

        let available_elf = available_bonuses("Elf", "Wojownik", &catalog, &[]);
        assert_eq!(available_elf.len(), 2);
    }

    #[test]
    fn available_bonuses_excludes_maxed() {
        let catalog = vec![test_catalog_entry()];
        let player_bonuses = vec![PlayerBonus {
            id: 1,
            catalog_id: 1,
            bonus_name: "strength".to_owned(),
            value: 5,
            duration: 5,
        }];
        let available = available_bonuses("Człowiek", "Wojownik", &catalog, &player_bonuses);
        assert!(available.is_empty());
    }

    // -- Round helper --

    #[test]
    fn round_to_one_decimal_cases() {
        assert!((round_to_one_decimal(1.55) - 1.6).abs() < f64::EPSILON);
        assert!((round_to_one_decimal(0.3) - 0.3).abs() < f64::EPSILON);
        assert!((round_to_one_decimal(1.23456) - 1.2).abs() < f64::EPSILON);
    }
}
