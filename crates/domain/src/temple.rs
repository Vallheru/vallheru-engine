//! Temple domain logic — work for piety, prayer/blessing system.
//!
//! Ported from `temple.php`. The temple lets players:
//! - Work devotion (spend energy to gain piety / `pw` points).
//! - Pray for blessings (spend piety + energy, random chance of stat/skill buff).
//!
//! The prayer system has a race×deity cost matrix and deity-specific blessing pools.

use crate::player::mutations::Deity;

// ---------------------------------------------------------------------------
// Temple service — work for piety
// ---------------------------------------------------------------------------

/// Errors from attempting temple work.
#[derive(Debug, Clone, PartialEq)]
pub enum TempleWorkError {
    NoDeity,
    Dead,
    InsufficientEnergy { needed: f64, available: f64 },
    InvalidAmount,
}

/// Result of a successful temple work action.
#[derive(Debug, Clone, PartialEq)]
pub struct TempleWorkResult {
    /// Piety points gained.
    pub piety_gained: i32,
    /// Energy spent.
    pub energy_cost: f64,
}

/// Validate and compute a temple work action.
///
/// The player spends `amount * 0.2` energy to gain `amount` piety (pw).
pub fn compute_temple_work(
    deity: &Option<String>,
    hp: i32,
    energy: f64,
    amount: i32,
) -> Result<TempleWorkResult, TempleWorkError> {
    if deity.as_ref().is_none_or(String::is_empty) {
        return Err(TempleWorkError::NoDeity);
    }
    if hp <= 0 {
        return Err(TempleWorkError::Dead);
    }
    if amount <= 0 {
        return Err(TempleWorkError::InvalidAmount);
    }

    let energy_cost = (f64::from(amount) * 0.2 * 10.0).round() / 10.0;
    if energy < energy_cost {
        return Err(TempleWorkError::InsufficientEnergy {
            needed: energy_cost,
            available: energy,
        });
    }

    Ok(TempleWorkResult {
        piety_gained: amount,
        energy_cost,
    })
}

// ---------------------------------------------------------------------------
// Prayer / blessing system
// ---------------------------------------------------------------------------

/// A blessable attribute with its piety cost.
#[derive(Debug, Clone)]
pub struct BlessingOption {
    /// Index into the global blessing array (0..=20).
    pub index: usize,
    /// Display name for the attribute.
    pub name: &'static str,
    /// DB column key for the blessed attribute.
    pub stat_key: &'static str,
    /// Piety cost for this race/attribute combination.
    pub cost: i32,
}

/// Errors from attempting to pray.
#[derive(Debug, Clone, PartialEq)]
pub enum PrayerError {
    Dead,
    AlreadyBlessed,
    InvalidSelection,
    InsufficientEnergy { needed: i32, available: i32 },
    InsufficientPiety { needed: i32, available: i32 },
}

/// Outcome of a prayer attempt.
#[derive(Debug, Clone, PartialEq)]
pub enum PrayerOutcome {
    /// Blessing applied successfully.
    Success {
        stat_key: &'static str,
        stat_name: &'static str,
        blessing_value: i32,
    },
    /// Prayer failed, deity ignored the request.
    Failure,
    /// Deity was angered — player dies.
    DeityWrath,
}

/// The 21 global blessing definitions. Index matches the PHP `$arrBless` array.
static BLESSINGS: [(/* name */ &str, /* stat_key */ &str); 21] = [
    ("Zręczności", "agility"),
    ("Siły", "strength"),
    ("Inteligencji", "inteli"),
    ("Mądrości", "wisdom"),
    ("Szybkości", "speed"),
    ("Kondycji", "condition"),
    ("Kowalstwa", "smith"),
    ("Alchemii", "alchemy"),
    ("Stolarki", "carpentry"),
    ("Broni", "weapon"),
    ("Strzelectwa", "shoot"),
    ("Uników", "dodge"),
    ("Rzucania Czarów", "cast"),
    ("Hodowli", "breeding"),
    ("Górnictwa", "mining"),
    ("Drwalnictwa", "lumberjack"),
    ("Zielarstwa", "herbalist"),
    ("Jubilerstwa", "jeweller"),
    ("Spostrzegawczości", "perception"),
    ("Złodziejstwa", "thievery"),
    ("Hutnictwa", "metallurgy"),
];

/// Race-specific base piety costs for each of the 21 blessings.
fn race_base_costs(race: &str) -> Option<[i32; 21]> {
    Some(match race {
        "Człowiek" => [
            10, 10, 10, 10, 10, 10, 10, 10, 10, 10, 10, 10, 10, 10, 10, 10, 10, 10, 10, 10, 10,
        ],
        "Elf" => [
            7, 15, 10, 10, 7, 15, 15, 7, 7, 15, 7, 10, 7, 7, 15, 7, 7, 10, 7, 10, 15,
        ],
        "Krasnolud" => [
            15, 7, 10, 10, 15, 7, 7, 15, 10, 7, 15, 10, 15, 15, 7, 15, 15, 7, 10, 15, 7,
        ],
        "Hobbit" => [
            7, 15, 10, 10, 7, 10, 10, 15, 10, 10, 10, 10, 7, 7, 10, 10, 7, 10, 7, 7, 10,
        ],
        "Jaszczuroczłek" => [
            7, 7, 15, 15, 7, 7, 10, 10, 10, 10, 10, 10, 15, 15, 15, 15, 15, 15, 15, 15, 15,
        ],
        "Gnom" => [
            10, 15, 10, 15, 15, 10, 7, 7, 7, 15, 15, 15, 15, 7, 7, 15, 7, 7, 10, 7, 10,
        ],
        _ => return None,
    })
}

/// Blessing indices available per deity.
fn deity_blessing_indices(deity: &Deity) -> &'static [usize] {
    match deity {
        Deity::Illuminati => &[
            0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20,
        ],
        Deity::Karserth => &[0, 1, 2, 3, 4, 5, 9, 10, 11],
        Deity::Anariel => &[0, 1, 2, 3, 4, 5, 7, 11, 12],
        Deity::Heluvald => &[0, 1, 2, 3, 4, 5, 6, 7, 8, 13, 14, 15, 16, 17, 18, 20],
        Deity::Daeraell => &[0, 1, 2, 3, 4, 5, 6, 7, 8, 13, 14, 15, 16, 17, 18, 19, 20],
        Deity::Tartus => &[0, 1, 2, 3, 4, 5, 9, 12],
        Deity::Oregarl => &[0, 1, 2, 3, 4, 5, 6, 9, 14, 17, 18, 20],
        Deity::TeatheDi => &[0, 1, 2, 3, 4, 5, 10, 11, 18, 19],
    }
}

/// Get the available blessing options for a player's race and deity.
///
/// Returns `None` if the race or deity is invalid.
pub fn available_blessings(race: &str, deity_str: &str) -> Option<Vec<BlessingOption>> {
    let base_costs = race_base_costs(race)?;
    let deity = Deity::from_slug(&deity_str.to_lowercase())?;
    let indices = deity_blessing_indices(&deity);
    let is_illuminati = deity == Deity::Illuminati;

    let options = indices
        .iter()
        .map(|&idx| {
            let mut cost = base_costs[idx];
            if is_illuminati {
                cost *= 2;
            }
            BlessingOption {
                index: idx,
                name: BLESSINGS[idx].0,
                stat_key: BLESSINGS[idx].1,
                cost,
            }
        })
        .collect();

    Some(options)
}

/// Validate and resolve a prayer attempt.
///
/// `energy_offered` maps to the PHP `$_POST['praytype']` (0..=6).
/// `blessing_index` is the index into the available blessings list.
/// `roll` is a random 1..=10 value. The caller provides it for testability.
/// `stat_or_skill_level` is the current trained value of the blessed attribute.
///
/// Returns the prayer outcome; the caller is responsible for persistence.
#[allow(clippy::too_many_arguments)]
pub fn resolve_prayer(
    hp: i32,
    current_bless: &str,
    current_pw: i32,
    energy: i32,
    energy_offered: i32,
    blessings: &[BlessingOption],
    blessing_index: usize,
    stat_or_skill_level: i32,
    roll: i32,
) -> Result<(PrayerOutcome, PrayerCost), PrayerError> {
    if hp <= 0 {
        return Err(PrayerError::Dead);
    }
    if !current_bless.is_empty() {
        return Err(PrayerError::AlreadyBlessed);
    }
    if !(0..=6).contains(&energy_offered) {
        return Err(PrayerError::InvalidSelection);
    }

    let option = blessings
        .get(blessing_index)
        .ok_or(PrayerError::InvalidSelection)?;

    if energy < energy_offered {
        return Err(PrayerError::InsufficientEnergy {
            needed: energy_offered,
            available: energy,
        });
    }

    if current_pw < option.cost {
        return Err(PrayerError::InsufficientPiety {
            needed: option.cost,
            available: current_pw,
        });
    }

    let cost = PrayerCost {
        piety: option.cost,
        energy: energy_offered,
    };

    let outcome = match roll.cmp(&9) {
        std::cmp::Ordering::Less => {
            // Success — compute blessing bonus.
            let base = energy_offered + stat_or_skill_level;
            let blessing_value = if option.index > 5 { base / 10 } else { base };
            PrayerOutcome::Success {
                stat_key: option.stat_key,
                stat_name: option.name,
                blessing_value,
            }
        }
        std::cmp::Ordering::Equal => PrayerOutcome::Failure,
        std::cmp::Ordering::Greater => {
            // roll == 10: deity wrath → player killed
            PrayerOutcome::DeityWrath
        }
    };

    Ok((outcome, cost))
}

/// Cost deducted from the player after a prayer attempt (always applies).
#[derive(Debug, Clone, PartialEq)]
pub struct PrayerCost {
    pub piety: i32,
    pub energy: i32,
}

// ---------------------------------------------------------------------------
// Deity info / pantheon
// ---------------------------------------------------------------------------

/// Deity description for the pantheon display.
pub struct DeityInfo {
    pub slug: &'static str,
    pub name: &'static str,
    /// Localized deity description.
    pub description: &'static str,
}

/// Full pantheon for display purposes.
pub fn pantheon() -> Vec<DeityInfo> {
    vec![
        DeityInfo {
            slug: "illuminati",
            name: "Illuminati",
            description: "Illuminati to bóg wojny i zniszczenia. Jego wyznawcy mogą się modlić o wszystkie błogosławieństwa, ale koszty są dwa razy wyższe.",
        },
        DeityInfo {
            slug: "karserth",
            name: "Karserth",
            description: "Karserth jest bogiem walki. Jego wyznawcy specjalizują się w umiejętnościach bojowych.",
        },
        DeityInfo {
            slug: "anariel",
            name: "Anariel",
            description: "Anariel jest boginią magii i alchemii. Jej wyznawcy specjalizują się w magii i alchemii.",
        },
        DeityInfo {
            slug: "heluvald",
            name: "Heluvald",
            description: "Heluvald jest bogiem rzemiosła. Jego wyznawcy specjalizują się w produkcji i zbieractwie.",
        },
        DeityInfo {
            slug: "tartus",
            name: "Tartus",
            description: "Tartus jest bogiem siły. Jego wyznawcy specjalizują się w walce wręcz i magii ofensywnej.",
        },
        DeityInfo {
            slug: "oregarl",
            name: "Oregarl",
            description: "Oregarl jest bogiem mądrości i górnictwa. Jego wyznawcy specjalizują się w kowalstwie i wydobyciu.",
        },
        DeityInfo {
            slug: "daeraell",
            name: "Daeraell",
            description: "Daeraell jest bogiem natury. Jego wyznawcy specjalizują się w rzemiośle i zbieractwie.",
        },
        DeityInfo {
            slug: "teathedi",
            name: "Teathe-di",
            description: "Teathe-di jest bogiem cieni. Jego wyznawcy specjalizują się w zwinności i kradzieży.",
        },
    ]
}

// ---------------------------------------------------------------------------
// Housing types
// ---------------------------------------------------------------------------

/// Determine the house rank/type name based on value and build level.
///
/// Matches PHP `housetype()` in `house.php`.
pub fn house_type_name(value: i32, build: i32) -> &'static str {
    if value > 99 && build > 20 {
        "Pałac"
    } else if value > 50 && build > 10 {
        "Dwór"
    } else if value > 20 && build > 5 {
        "Rezydencja"
    } else if value > 5 && build > 3 {
        "Kamienica"
    } else {
        "Chatka"
    }
}

// ---------------------------------------------------------------------------
// Tests
// ---------------------------------------------------------------------------

#[cfg(test)]
mod tests {
    use super::*;

    #[test]
    fn temple_work_basic() {
        let result = compute_temple_work(&Some("Illuminati".to_owned()), 100, 10.0, 10);
        assert!(result.is_ok());
        let r = result.unwrap();
        assert_eq!(r.piety_gained, 10);
        assert!((r.energy_cost - 2.0).abs() < f64::EPSILON);
    }

    #[test]
    fn temple_work_no_deity() {
        let result = compute_temple_work(&None, 100, 10.0, 5);
        assert_eq!(result, Err(TempleWorkError::NoDeity));
    }

    #[test]
    fn temple_work_dead() {
        let result = compute_temple_work(&Some("Illuminati".to_owned()), 0, 10.0, 5);
        assert_eq!(result, Err(TempleWorkError::Dead));
    }

    #[test]
    fn temple_work_insufficient_energy() {
        let result = compute_temple_work(&Some("Illuminati".to_owned()), 100, 0.5, 10);
        assert!(matches!(
            result,
            Err(TempleWorkError::InsufficientEnergy { .. })
        ));
    }

    #[test]
    fn available_blessings_human_illuminati() {
        let blessings = available_blessings("Człowiek", "Illuminati").unwrap();
        assert_eq!(blessings.len(), 21);
        // Illuminati doubles costs for humans (base 10 → 20).
        assert_eq!(blessings[0].cost, 20);
    }

    #[test]
    fn available_blessings_elf_karserth() {
        let blessings = available_blessings("Elf", "Karserth").unwrap();
        assert_eq!(blessings.len(), 9);
        assert_eq!(blessings[0].stat_key, "agility");
        assert_eq!(blessings[0].cost, 7); // Elf agility cost
    }

    #[test]
    fn prayer_success() {
        let blessings = available_blessings("Człowiek", "Karserth").unwrap();
        let (outcome, cost) = resolve_prayer(100, "", 100, 5, 3, &blessings, 0, 10, 5).unwrap();
        assert_eq!(cost.piety, 10);
        assert_eq!(cost.energy, 3);
        match outcome {
            PrayerOutcome::Success {
                stat_key,
                blessing_value,
                ..
            } => {
                assert_eq!(stat_key, "agility");
                assert_eq!(blessing_value, 13); // 3 + 10
            }
            _ => panic!("expected success"),
        }
    }

    #[test]
    fn prayer_failure_roll() {
        let blessings = available_blessings("Człowiek", "Karserth").unwrap();
        let (outcome, _) = resolve_prayer(100, "", 100, 5, 3, &blessings, 0, 10, 9).unwrap();
        assert_eq!(outcome, PrayerOutcome::Failure);
    }

    #[test]
    fn prayer_wrath_roll() {
        let blessings = available_blessings("Człowiek", "Karserth").unwrap();
        let (outcome, _) = resolve_prayer(100, "", 100, 5, 3, &blessings, 0, 10, 10).unwrap();
        assert_eq!(outcome, PrayerOutcome::DeityWrath);
    }

    #[test]
    fn prayer_already_blessed() {
        let blessings = available_blessings("Człowiek", "Karserth").unwrap();
        let result = resolve_prayer(100, "agility", 100, 5, 3, &blessings, 0, 10, 5);
        assert_eq!(result, Err(PrayerError::AlreadyBlessed));
    }

    #[test]
    fn prayer_skill_blessing_divided() {
        // Skill blessings (index > 5) have value divided by 10.
        let blessings = available_blessings("Człowiek", "Karserth").unwrap();
        // Karserth has weapon at position index 6 → global index 9
        let weapon_idx = blessings
            .iter()
            .position(|b| b.stat_key == "weapon")
            .unwrap();
        let (outcome, _) =
            resolve_prayer(100, "", 100, 5, 3, &blessings, weapon_idx, 50, 5).unwrap();
        match outcome {
            PrayerOutcome::Success { blessing_value, .. } => {
                // (3 + 50) / 10 = 5
                assert_eq!(blessing_value, 5);
            }
            _ => panic!("expected success"),
        }
    }

    #[test]
    fn house_type_name_tiers() {
        assert_eq!(house_type_name(1, 1), "Chatka");
        assert_eq!(house_type_name(6, 4), "Kamienica");
        assert_eq!(house_type_name(21, 6), "Rezydencja");
        assert_eq!(house_type_name(51, 11), "Dwór");
        assert_eq!(house_type_name(100, 21), "Pałac");
    }
}
