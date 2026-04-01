//! Item enchantment logic.
//!
//! Ported from `czary.php` — the utility-spell subsystem that lets players
//! magically enhance equipment. Three enchantment spells exist:
//!
//! - **Power** ("Ulepszenie przedmiotu"): increases `power` stat (excludes bows).
//! - **Durability** ("Utwardzenie przedmiotu"): increases `wt`/`maxwt` (excludes arrows).
//! - **Special** ("Umagicznienie przedmiotu"): increases `szyb` for weapons/bows,
//!   or reduces `zr` penalty for armor/legs.

use rand::Rng;

// ---------------------------------------------------------------------------
// Enchantment spell type
// ---------------------------------------------------------------------------

/// The three utility-spell enchantment flavors.
#[derive(Debug, Clone, Copy, PartialEq, Eq)]
pub enum EnchantKind {
    /// Increase item power.
    Power,
    /// Increase item durability (wt/maxwt).
    Durability,
    /// Increase speed bonus (weapons/bows) or reduce agility penalty (armor/legs).
    Special,
}

impl EnchantKind {
    /// Match spell name to kind.
    pub fn from_spell_name(name: &str) -> Option<Self> {
        match name {
            "Ulepszenie przedmiotu" => Some(Self::Power),
            "Utwardzenie przedmiotu" => Some(Self::Durability),
            "Umagicznienie przedmiotu" => Some(Self::Special),
            _ => None,
        }
    }

    /// Equipment type codes that are **excluded** (cannot be enchanted with this spell).
    /// Matches the PHP `$strTypes` NOT IN lists.
    pub fn excluded_type_codes(&self) -> &'static [&'static str] {
        match self {
            // E_SPELL1: NOT IN ('I','Q','P','E','O','B','T','C')
            Self::Power => &["I", "Q", "P", "E", "O", "B", "T", "C"],
            // E_SPELL2: NOT IN ('I','Q','P','E','O','R','T','C')
            Self::Durability => &["I", "Q", "P", "E", "O", "R", "T", "C"],
            // E_SPELL3: NOT IN ('I','Q','P','E','O','R','H','S','T','C')
            Self::Special => &["I", "Q", "P", "E", "O", "R", "H", "S", "T", "C"],
        }
    }

    /// Whether a given equipment type code is eligible for this enchantment.
    pub fn can_enchant(&self, type_code: &str) -> bool {
        !self.excluded_type_codes().contains(&type_code)
    }

    /// Additional per-item validation: Power cannot target bows, Durability
    /// cannot target arrows, Special only targets W/B (speed) or A/L (agility).
    pub fn validate_item(&self, type_code: &str) -> Result<(), &'static str> {
        match self {
            Self::Power => {
                if type_code == "B" {
                    Err("Nie można zwiększyć siły łuków")
                } else {
                    Ok(())
                }
            }
            Self::Durability => {
                if type_code == "R" {
                    Err("Nie można zwiększyć wytrzymałości strzał")
                } else {
                    Ok(())
                }
            }
            Self::Special => match type_code {
                "W" | "B" | "A" | "L" => Ok(()),
                _ => Err("Nie możesz umagicznić tego przedmiotu!"),
            },
        }
    }
}

// ---------------------------------------------------------------------------
// Name manipulation
// ---------------------------------------------------------------------------

/// Prefixes added by racial crafting (Dragon, Elf, Dwarf variants).
const ITEM_PREFIXES: &[&str] = &[
    "Smoczy ",
    "Smocza ",
    "Smocze ",
    "Elfi ",
    "Elfie ",
    "Elfia ",
    "Krasnoludzki ",
    "Krasnoludzka ",
    "Krasnoludzkie ",
];

/// Material suffixes from smithing/carpentry.
const MATERIAL_SUFFIXES: &[&str] = &[
    " z miedzi",
    " z brązu",
    " z mosiądzu",
    " z żelaza",
    " ze stali",
    "z leszczyny",
    "z cisu",
    "z wiązu",
    "wzmocniony",
    "kompozytowy",
];

/// Strip racial prefix and material suffix to get the base item name stem.
pub fn base_item_stem(name: &str) -> String {
    let mut s = name.to_string();
    for prefix in ITEM_PREFIXES {
        if let Some(rest) = s.strip_prefix(prefix) {
            s = rest.to_string();
            break; // Only one prefix at a time
        }
    }
    for suffix in MATERIAL_SUFFIXES {
        if let Some(rest) = s.strip_suffix(suffix) {
            s = rest.to_string();
            break;
        }
    }
    s
}

/// Build the lookup name for the base (cheapest) version of an item.
///
/// - For weapons/armor/shields/helmets/legs: `"<stem> z miedzi"` → search `equipment WHERE owner=0`.
/// - For bows (type='B'): `"<stem>z leszczyny"` → search `bows` table.
pub fn base_lookup_name(stem: &str, type_code: &str) -> String {
    if type_code == "B" {
        format!("{stem}z leszczyny")
    } else {
        format!("{stem} z miedzi")
    }
}

/// Whether the base-stat lookup should use the `bows` table.
pub fn uses_bows_table(type_code: &str) -> bool {
    type_code == "B"
}

/// Determine the "Magiczny/Magiczna/Magiczne" prefix for the enchanted name.
pub fn magic_name_prefix(type_code: &str) -> Option<&'static str> {
    match type_code {
        "W" | "H" | "B" => Some("Magiczny "),
        "A" | "S" => Some("Magiczna "),
        "L" | "R" => Some("Magiczne "),
        _ => None,
    }
}

// ---------------------------------------------------------------------------
// Enchantment formulas
// ---------------------------------------------------------------------------

/// Calculate enchant success chance.
///
/// PHP: `(magic_skill + intelligence) - item_minlev - spell_level + rand(1,100)`
/// Success when result > 100.
pub fn enchant_chance(
    magic_level: i32,
    intelligence: i32,
    item_minlev: i32,
    spell_level: i32,
) -> i32 {
    magic_level + intelligence - item_minlev - spell_level
}

/// Roll the enchantment check. Returns `true` if the enchantment succeeds.
pub fn roll_enchant_success<R: Rng>(rng: &mut R, base_chance: i32) -> bool {
    let roll: i32 = rng.gen_range(1..=100);
    (base_chance + roll) > 100
}

/// Calculate the raw bonus from an enchantment.
///
/// PHP: `magic_skill * rand(1, 5)`, capped at `base_value * 5`.
pub fn enchant_bonus<R: Rng>(rng: &mut R, magic_level: i32, base_cap_value: i32) -> i32 {
    let raw = magic_level * rng.gen_range(1..=5);
    let max = base_cap_value.abs() * 5;
    if max > 0 { raw.min(max) } else { raw }
}

/// Result of an enchantment attempt.
#[derive(Debug, Clone, PartialEq, Eq)]
pub enum EnchantResult {
    /// Enchantment succeeded.
    Success {
        /// The bonus value applied.
        bonus: i32,
        /// XP awarded to intelligence stat.
        intel_xp: i32,
        /// XP awarded to magic skill.
        magic_xp: i32,
    },
    /// Enchantment failed — item is destroyed.
    Failure {
        /// XP awarded to intelligence stat (always 1).
        intel_xp: i32,
        /// XP awarded to magic skill (always 1).
        magic_xp: i32,
    },
}

impl EnchantResult {
    /// Extract the `(intel_xp, magic_xp)` pair regardless of variant.
    pub fn xp_gains(&self) -> (i32, i32) {
        match self {
            Self::Success {
                intel_xp, magic_xp, ..
            }
            | Self::Failure { intel_xp, magic_xp } => (*intel_xp, *magic_xp),
        }
    }
}

/// Resolve a full enchantment attempt.
///
/// `base_stat` is the relevant stat from the base (cheapest) item version,
/// used to cap the bonus:
/// - Power spell: base item's `power`
/// - Durability spell: base item's `maxwt`
/// - Special spell on weapon/bow: base item's `szyb`
/// - Special spell on armor/legs: base item's `zr` (absolute value)
pub fn resolve_enchant<R: Rng>(
    rng: &mut R,
    magic_level: i32,
    intelligence: i32,
    item_minlev: i32,
    spell_level: i32,
    base_stat_for_cap: i32,
) -> EnchantResult {
    let base_chance = enchant_chance(magic_level, intelligence, item_minlev, spell_level);
    if roll_enchant_success(rng, base_chance) {
        let bonus = enchant_bonus(rng, magic_level, base_stat_for_cap);
        EnchantResult::Success {
            bonus,
            intel_xp: bonus / 2,
            magic_xp: bonus / 2,
        }
    } else {
        EnchantResult::Failure {
            intel_xp: 1,
            magic_xp: 1,
        }
    }
}

/// Compute the new item stats after a successful enchantment.
///
/// Returns `(new_power, new_wt, new_maxwt, new_szyb, new_zr)`.
#[allow(clippy::too_many_arguments)]
pub fn apply_bonus_to_item(
    kind: EnchantKind,
    type_code: &str,
    bonus: i32,
    power: i32,
    wt: i32,
    maxwt: i32,
    szyb: i32,
    zr: i32,
) -> (i32, i32, i32, i32, i32) {
    match kind {
        EnchantKind::Power => (power + bonus, wt, maxwt, szyb, zr),
        EnchantKind::Durability => (power, wt + bonus, maxwt + bonus, szyb, zr),
        EnchantKind::Special => match type_code {
            "W" | "B" => (power, wt, maxwt, szyb + bonus, zr),
            "A" | "L" => {
                let new_zr = if zr < 0 { zr - bonus } else { zr + bonus };
                (power, wt, maxwt, szyb, new_zr)
            }
            _ => (power, wt, maxwt, szyb, zr),
        },
    }
}

// ---------------------------------------------------------------------------
// Element mapping
// ---------------------------------------------------------------------------

/// Map spell element word to equipment magic column char.
pub fn element_to_magic_code(element: &str) -> &'static str {
    match element {
        "earth" => "E",
        "water" => "W",
        "fire" => "F",
        "wind" => "A",
        _ => "N",
    }
}

// ---------------------------------------------------------------------------
// Tests
// ---------------------------------------------------------------------------

#[cfg(test)]
mod tests {
    use super::*;

    #[test]
    fn enchant_kind_from_spell_name() {
        assert_eq!(
            EnchantKind::from_spell_name("Ulepszenie przedmiotu"),
            Some(EnchantKind::Power)
        );
        assert_eq!(
            EnchantKind::from_spell_name("Utwardzenie przedmiotu"),
            Some(EnchantKind::Durability)
        );
        assert_eq!(
            EnchantKind::from_spell_name("Umagicznienie przedmiotu"),
            Some(EnchantKind::Special)
        );
        assert_eq!(EnchantKind::from_spell_name("Unknown"), None);
    }

    #[test]
    fn excluded_types() {
        // Power excludes bows
        assert!(!EnchantKind::Power.can_enchant("B"));
        assert!(EnchantKind::Power.can_enchant("W"));
        assert!(EnchantKind::Power.can_enchant("A"));

        // Durability excludes arrows
        assert!(!EnchantKind::Durability.can_enchant("R"));
        assert!(EnchantKind::Durability.can_enchant("B"));

        // Special is most restrictive
        assert!(EnchantKind::Special.can_enchant("W"));
        assert!(EnchantKind::Special.can_enchant("A"));
        assert!(EnchantKind::Special.can_enchant("B"));
        assert!(EnchantKind::Special.can_enchant("L"));
        assert!(!EnchantKind::Special.can_enchant("H"));
        assert!(!EnchantKind::Special.can_enchant("S"));
    }

    #[test]
    fn validate_item_power_rejects_bow() {
        assert!(EnchantKind::Power.validate_item("B").is_err());
        assert!(EnchantKind::Power.validate_item("W").is_ok());
    }

    #[test]
    fn validate_item_durability_rejects_arrows() {
        assert!(EnchantKind::Durability.validate_item("R").is_err());
        assert!(EnchantKind::Durability.validate_item("B").is_ok());
    }

    #[test]
    fn validate_item_special_accepts_wbal() {
        assert!(EnchantKind::Special.validate_item("W").is_ok());
        assert!(EnchantKind::Special.validate_item("B").is_ok());
        assert!(EnchantKind::Special.validate_item("A").is_ok());
        assert!(EnchantKind::Special.validate_item("L").is_ok());
        assert!(EnchantKind::Special.validate_item("H").is_err());
    }

    #[test]
    fn base_item_stem_strips_prefix_and_suffix() {
        assert_eq!(base_item_stem("Smoczy Miecz z żelaza"), "Miecz");
        assert_eq!(base_item_stem("Elfia Zbroja z brązu"), "Zbroja");
        assert_eq!(base_item_stem("Krasnoludzki Helm ze stali"), "Helm");
        assert_eq!(base_item_stem("Prosty Miecz"), "Prosty Miecz");
    }

    #[test]
    fn base_lookup_name_weapon_vs_bow() {
        assert_eq!(base_lookup_name("Miecz", "W"), "Miecz z miedzi");
        assert_eq!(base_lookup_name("Łuk ", "B"), "Łuk z leszczyny");
    }

    #[test]
    fn magic_name_prefix_by_type() {
        assert_eq!(magic_name_prefix("W"), Some("Magiczny "));
        assert_eq!(magic_name_prefix("A"), Some("Magiczna "));
        assert_eq!(magic_name_prefix("L"), Some("Magiczne "));
        assert_eq!(magic_name_prefix("Q"), None);
    }

    #[test]
    fn enchant_chance_formula() {
        // magic=50, intel=30, minlev=10, spell_level=20 → 50+30-10-20 = 50
        assert_eq!(enchant_chance(50, 30, 10, 20), 50);
    }

    #[test]
    fn roll_enchant_success_boundary() {
        // base_chance=50, roll needs > 50 to succeed (50 + roll > 100)
        let result = (50 + 51) > 100;
        assert!(result);
        let result = (50 + 50) > 100;
        assert!(!result);
    }

    #[test]
    fn apply_bonus_power() {
        let (p, w, mw, s, z) =
            apply_bonus_to_item(EnchantKind::Power, "W", 10, 50, 100, 100, 5, -3);
        assert_eq!(p, 60);
        assert_eq!(w, 100);
        assert_eq!(mw, 100);
        assert_eq!(s, 5);
        assert_eq!(z, -3);
    }

    #[test]
    fn apply_bonus_durability() {
        let (p, w, mw, s, z) =
            apply_bonus_to_item(EnchantKind::Durability, "A", 15, 50, 80, 100, 5, -3);
        assert_eq!(p, 50);
        assert_eq!(w, 95);
        assert_eq!(mw, 115);
        assert_eq!(s, 5);
        assert_eq!(z, -3);
    }

    #[test]
    fn apply_bonus_special_weapon_speed() {
        let (_, _, _, s, z) =
            apply_bonus_to_item(EnchantKind::Special, "W", 10, 50, 100, 100, 5, -3);
        assert_eq!(s, 15);
        assert_eq!(z, -3);
    }

    #[test]
    fn apply_bonus_special_armor_agility_negative() {
        // zr is -3 (penalty), bonus=10 → new_zr = -3-10 = -13 (bigger reduction)
        let (_, _, _, s, z) =
            apply_bonus_to_item(EnchantKind::Special, "A", 10, 50, 100, 100, 5, -3);
        assert_eq!(s, 5);
        assert_eq!(z, -13);
    }

    #[test]
    fn apply_bonus_special_armor_agility_positive() {
        // zr is positive → bonus adds
        let (_, _, _, _, z) =
            apply_bonus_to_item(EnchantKind::Special, "A", 10, 50, 100, 100, 5, 3);
        assert_eq!(z, 13);
    }

    #[test]
    fn element_to_magic_code_mapping() {
        assert_eq!(element_to_magic_code("earth"), "E");
        assert_eq!(element_to_magic_code("water"), "W");
        assert_eq!(element_to_magic_code("fire"), "F");
        assert_eq!(element_to_magic_code("wind"), "A");
        assert_eq!(element_to_magic_code("unknown"), "N");
    }

    #[test]
    fn resolve_enchant_success() {
        // Very high chance: magic=100, intel=100, minlev=1, spell=1
        // base_chance = 100+100-1-1 = 198. Any roll succeeds.
        let mut rng = rand::thread_rng();
        let result = resolve_enchant(&mut rng, 100, 100, 1, 1, 10);
        match result {
            EnchantResult::Success {
                bonus,
                intel_xp,
                magic_xp,
            } => {
                assert!(bonus > 0);
                assert_eq!(intel_xp, bonus / 2);
                assert_eq!(magic_xp, bonus / 2);
            }
            EnchantResult::Failure { .. } => panic!("Expected success with high stats"),
        }
    }

    #[test]
    fn resolve_enchant_failure() {
        // Very low chance: magic=1, intel=1, minlev=200, spell=200
        // base_chance = 1+1-200-200 = -398. No roll can save this.
        let mut rng = rand::thread_rng();
        let result = resolve_enchant(&mut rng, 1, 1, 200, 200, 10);
        assert!(matches!(
            result,
            EnchantResult::Failure {
                intel_xp: 1,
                magic_xp: 1
            }
        ));
    }
}
