//! Equipment loadout and stat bonus application.
//!
//! Recreates the behavior of PHP `player_class.php::equipment()` (loading
//! the 13-slot equipped item array) and `curstats()` / `curskills()` /
//! `checkbonus()` (applying equipped item effects to player stats and skills).
//!
//! The PHP code uses a positional `array[0..12]` for slots:
//!
//!   0=Weapon  1=Bow  2=Helmet  3=Armor  4=Legs  5=Shield
//!   6=Arrows  7=Wand  8=MageClothing  9=Ring1  10=Ring2
//!   11=SecondWeapon  12=Elemental
//!
//! This module replaces the positional array with a named struct.

use crate::item::{EquipmentSlot, EquipmentType, OwnedEquipment};
use crate::player::bonuses::PlayerBonus;
use crate::player::skills::PlayerSkill;
use crate::player::stats::PlayerStat;
use serde::{Deserialize, Serialize};

// ---------------------------------------------------------------------------
// Equipment loadout — 13 named slots
// ---------------------------------------------------------------------------

/// The full set of equipped items for a player, one per slot.
///
/// `None` means the slot is empty. This replaces the PHP `$this->equip`
/// positional array.
#[derive(Debug, Clone, Default, Serialize, Deserialize)]
pub struct EquipmentLoadout {
    pub weapon: Option<OwnedEquipment>,
    pub bow: Option<OwnedEquipment>,
    pub helmet: Option<OwnedEquipment>,
    pub armor: Option<OwnedEquipment>,
    pub legs: Option<OwnedEquipment>,
    pub shield: Option<OwnedEquipment>,
    pub arrows: Option<OwnedEquipment>,
    pub wand: Option<OwnedEquipment>,
    pub mage_clothing: Option<OwnedEquipment>,
    pub ring1: Option<OwnedEquipment>,
    pub ring2: Option<OwnedEquipment>,
    pub second_weapon: Option<OwnedEquipment>,
    pub elemental: Option<OwnedEquipment>,
}

impl EquipmentLoadout {
    /// Build an equipment loadout from a list of equipped items.
    ///
    /// Mirrors the PHP `equipment()` method slot-assignment logic. Items
    /// that map to Rings or Weapons may occupy the second slot if the
    /// first is already taken.
    pub fn from_equipped_items(items: Vec<OwnedEquipment>) -> Self {
        let mut loadout = Self::default();
        for item in items {
            match item.equipment_type {
                EquipmentType::Weapon => {
                    if loadout.weapon.is_none() {
                        loadout.weapon = Some(item);
                    } else {
                        loadout.second_weapon = Some(item);
                    }
                }
                EquipmentType::Bow => loadout.bow = Some(item),
                EquipmentType::Helmet => loadout.helmet = Some(item),
                EquipmentType::Armor => loadout.armor = Some(item),
                EquipmentType::Legs => loadout.legs = Some(item),
                EquipmentType::Shield => loadout.shield = Some(item),
                EquipmentType::Arrows => loadout.arrows = Some(item),
                EquipmentType::Wand => loadout.wand = Some(item),
                EquipmentType::MageClothing => loadout.mage_clothing = Some(item),
                EquipmentType::Ring => {
                    if loadout.ring1.is_none() {
                        loadout.ring1 = Some(item);
                    } else {
                        loadout.ring2 = Some(item);
                    }
                }
                EquipmentType::Elemental => loadout.elemental = Some(item),
                EquipmentType::Quest | EquipmentType::Other | EquipmentType::Plan => {}
            }
        }
        loadout
    }

    /// Get a reference to the item in a specific slot.
    pub fn get(&self, slot: EquipmentSlot) -> Option<&OwnedEquipment> {
        match slot {
            EquipmentSlot::Weapon => self.weapon.as_ref(),
            EquipmentSlot::Bow => self.bow.as_ref(),
            EquipmentSlot::Helmet => self.helmet.as_ref(),
            EquipmentSlot::Armor => self.armor.as_ref(),
            EquipmentSlot::Legs => self.legs.as_ref(),
            EquipmentSlot::Shield => self.shield.as_ref(),
            EquipmentSlot::Arrows => self.arrows.as_ref(),
            EquipmentSlot::Wand => self.wand.as_ref(),
            EquipmentSlot::MageClothing => self.mage_clothing.as_ref(),
            EquipmentSlot::Ring1 => self.ring1.as_ref(),
            EquipmentSlot::Ring2 => self.ring2.as_ref(),
            EquipmentSlot::SecondWeapon => self.second_weapon.as_ref(),
            EquipmentSlot::Elemental => self.elemental.as_ref(),
        }
    }

    /// Iterate over all occupied slots.
    pub fn iter_occupied(&self) -> impl Iterator<Item = (EquipmentSlot, &OwnedEquipment)> {
        let slots = [
            (EquipmentSlot::Weapon, &self.weapon),
            (EquipmentSlot::Bow, &self.bow),
            (EquipmentSlot::Helmet, &self.helmet),
            (EquipmentSlot::Armor, &self.armor),
            (EquipmentSlot::Legs, &self.legs),
            (EquipmentSlot::Shield, &self.shield),
            (EquipmentSlot::Arrows, &self.arrows),
            (EquipmentSlot::Wand, &self.wand),
            (EquipmentSlot::MageClothing, &self.mage_clothing),
            (EquipmentSlot::Ring1, &self.ring1),
            (EquipmentSlot::Ring2, &self.ring2),
            (EquipmentSlot::SecondWeapon, &self.second_weapon),
            (EquipmentSlot::Elemental, &self.elemental),
        ];
        slots
            .into_iter()
            .filter_map(|(slot, opt)| opt.as_ref().map(|item| (slot, item)))
    }
}

// ---------------------------------------------------------------------------
// Ring stat resolution — determine which stat a ring modifies
// ---------------------------------------------------------------------------

/// The six combat stats in the same order used by the PHP ring-parsing array.
const RING_STAT_KEYS: [&str; 6] = [
    "agility",
    "strength",
    "inteli",
    "wisdom",
    "speed",
    "condition",
];

/// Polish ring-name suffixes that determine the boosted stat.
///
/// The order matches `RING_STAT_KEYS` — index 0 = agility, etc.
const RING_NAME_SUFFIXES: [&str; 6] = [
    "zręczności",
    "siły",
    "inteligencji",
    "woli",
    "szybkości",
    "kondycji",
];

/// Determine which player stat a ring boosts by inspecting the last word of
/// the ring's Polish name.
///
/// Returns the stat key (e.g. `"strength"`) or `None` if unrecognised.
pub fn ring_stat_key(ring_name: &str) -> Option<&'static str> {
    let last_word = ring_name.split_whitespace().next_back()?;
    RING_NAME_SUFFIXES
        .iter()
        .position(|&suffix| suffix == last_word)
        .map(|idx| RING_STAT_KEYS[idx])
}

// ---------------------------------------------------------------------------
// Stat bonus application — port of curstats()
// ---------------------------------------------------------------------------

/// Apply all equipment-derived bonuses to a mutable slice of player stats.
///
/// This mirrors the PHP `curstats()` method:
///
/// 1. Agility penalty: every equipped item's `agility_mod` reduces agility.
/// 2. Bow speed bonus: bow's `speed_mod` adds to speed.
/// 3. Ring stat bonuses: each ring adds its `power` to the stat determined
///    by the ring's Polish name suffix.
/// 4. Blessing bonus: if the player has an active blessing on a stat, add it.
/// 5. General bonuses: for each of the six stats, apply `checkbonus()`.
pub fn apply_equipment_stat_bonuses(
    stats: &mut [PlayerStat],
    loadout: &EquipmentLoadout,
    bless_stat: &str,
    bless_value: i32,
    bonuses: &[PlayerBonus],
) {
    // 1. Agility penalty from all equipped items
    for (_slot, item) in loadout.iter_occupied() {
        if item.agility_mod != 0 {
            if let Some(agi) = find_stat_mut(stats, "agility") {
                agi.modified -= item.agility_mod;
            }
        }
    }

    // 2. Bow speed bonus
    if let Some(bow) = &loadout.bow {
        if bow.speed_mod != 0 {
            if let Some(spd) = find_stat_mut(stats, "speed") {
                spd.modified += bow.speed_mod;
            }
        }
    }

    // 3. Ring stat bonuses
    apply_ring_bonus(stats, loadout.ring1.as_ref());
    apply_ring_bonus(stats, loadout.ring2.as_ref());

    // 4. Blessing bonus
    if !bless_stat.is_empty() && bless_value != 0 {
        if let Some(stat) = find_stat_mut(stats, bless_stat) {
            stat.modified += bless_value;
        }
    }

    // 5. General bonuses (checkbonus for each stat)
    for key in &RING_STAT_KEYS {
        let bonus = check_stat_bonus(key, stats, bonuses);
        if bonus != 0 {
            if let Some(stat) = find_stat_mut(stats, key) {
                stat.modified += bonus;
            }
        }
    }
}

/// Apply a ring's stat bonus, if the slot is occupied.
fn apply_ring_bonus(stats: &mut [PlayerStat], ring: Option<&OwnedEquipment>) {
    if let Some(ring_item) = ring {
        if ring_item.power != 0 {
            if let Some(stat_key) = ring_stat_key(&ring_item.name) {
                if let Some(stat) = find_stat_mut(stats, stat_key) {
                    stat.modified += ring_item.power;
                }
            }
        }
    }
}

// ---------------------------------------------------------------------------
// checkbonus() — port of the PHP bonus calculation
// ---------------------------------------------------------------------------

/// Calculate the bonus value for a stat trigger.
///
/// PHP formula: `ceil(base_value * ((level * magnitude) / 100))`
///
/// For the six player stats, `base_value` is the stat's current modified value.
/// The `assassin` trigger returns raw `level * magnitude` without division.
/// The default case returns `(level * magnitude) / 100` (integer division).
///
/// Note: the PHP bonuses array stores 4 fields: `[catalog_id, level, trigger, magnitude]`.
/// Our `PlayerBonus` struct maps: `bonus_name` = trigger, `value` = level, `duration` = magnitude.
#[allow(clippy::cast_possible_truncation)]
pub fn check_bonus(
    trigger: &str,
    stats: &[PlayerStat],
    skills: &[PlayerSkill],
    bonuses: &[PlayerBonus],
) -> i32 {
    let Some(bonus) = bonuses.iter().find(|b| b.bonus_name == trigger) else {
        return 0;
    };

    let level = bonus.value;
    let magnitude = bonus.duration;

    match trigger {
        // Skill-scaling bonuses
        "mining" | "crystal" | "adamantium" | "copper" | "zinc" | "tin" | "iron" | "coal" => {
            skill_scaled_bonus("mining", skills, level, magnitude)
        }
        "herbalism" | "illani" | "illanias" | "nutari" | "dynallca" => {
            skill_scaled_bonus("herbalism", skills, level, magnitude)
        }
        "smith" | "weaponsmith" | "armorsmith" | "helmsmith" | "shieldsmith" | "legsmith"
        | "toolsmith" => skill_scaled_bonus("smith", skills, level, magnitude),
        "carpentry" | "bowyer" | "arrowmaker" => {
            skill_scaled_bonus("carpentry", skills, level, magnitude)
        }
        "alchemy" | "amana" | "ahealth" | "apoison" | "aantidote" => {
            skill_scaled_bonus("alchemy", skills, level, magnitude)
        }
        "jewellry" | "smelting" | "breeding" | "lumberjack" => {
            skill_scaled_bonus(trigger, skills, level, magnitude)
        }
        "enchant" => skill_scaled_bonus("magic", skills, level, magnitude),
        // Stat-scaling bonuses
        "tactic" => stat_scaled_bonus("speed", stats, level, magnitude),
        "will" | "antimagic" => stat_scaled_bonus("wisdom", stats, level, magnitude),
        "eagleeye" => skill_scaled_bonus("shoot", skills, level, magnitude),
        "pickpocket" | "steal" | "spy" => skill_scaled_bonus("thievery", skills, level, magnitude),
        "assasin" => level * magnitude, // No division by 100
        "seeker" => skill_scaled_bonus("perception", skills, level, magnitude),
        // The six player stats
        "speed" | "strength" | "wisdom" | "inteli" | "agility" | "condition" => {
            stat_scaled_bonus(trigger, stats, level, magnitude)
        }
        // Default
        _ => (level * magnitude) / 100,
    }
}

/// Convenience: compute bonus for a stat trigger only (used in `apply_equipment_stat_bonuses`).
fn check_stat_bonus(trigger: &str, stats: &[PlayerStat], bonuses: &[PlayerBonus]) -> i32 {
    // For stat triggers we only need stats, not skills.
    let Some(bonus) = bonuses.iter().find(|b| b.bonus_name == trigger) else {
        return 0;
    };
    let level = bonus.value;
    let magnitude = bonus.duration;
    stat_scaled_bonus(trigger, stats, level, magnitude)
}

/// `ceil(skill_level * ((level * magnitude) / 100))`.
#[allow(clippy::cast_possible_truncation)]
fn skill_scaled_bonus(skill_key: &str, skills: &[PlayerSkill], level: i32, magnitude: i32) -> i32 {
    let skill_level = skills
        .iter()
        .find(|s| s.skill_key == skill_key)
        .map_or(0, |s| s.level);
    (f64::from(skill_level) * (f64::from(level) * f64::from(magnitude) / 100.0)).ceil() as i32
}

/// `ceil(stat_modified * ((level * magnitude) / 100))`.
#[allow(clippy::cast_possible_truncation)]
fn stat_scaled_bonus(stat_key: &str, stats: &[PlayerStat], level: i32, magnitude: i32) -> i32 {
    let stat_modified = stats
        .iter()
        .find(|s| s.stat_key == stat_key)
        .map_or(0, |s| s.modified);
    (f64::from(stat_modified) * (f64::from(level) * f64::from(magnitude) / 100.0)).ceil() as i32
}

// ---------------------------------------------------------------------------
// Skill bonus application — port of curskills()
// ---------------------------------------------------------------------------

/// Tool name mapping: elemental item name substring → skill key.
const ELEMENTAL_TOOLS: [(&str, &str); 9] = [
    ("miechy", "smelting"),
    ("piła", "lumberjack"),
    ("kilof", "mining"),
    ("uprząż", "breeding"),
    ("nożyk", "jewellry"),
    ("sierp", "herbalism"),
    ("moździerz", "alchemy"),
    ("ciesak", "carpentry"),
    ("młot", "smith"),
];

/// Apply skill bonuses from the elemental tool item (slot 12).
///
/// For each crafting skill in `skill_keys`, if the elemental item name
/// contains the corresponding tool name, add `floor((power/100) * skill_level)`.
///
/// This mirrors the PHP `curskills()` elemental-tool logic.
/// Note: durability reduction (DB write) is NOT handled here — that is a
/// side-effect that belongs in the handler/service layer.
#[allow(clippy::cast_possible_truncation)]
pub fn apply_elemental_skill_bonus(
    skills: &mut [PlayerSkill],
    loadout: &EquipmentLoadout,
    skill_keys: &[&str],
) {
    let Some(elemental) = &loadout.elemental else {
        return;
    };

    let item_name_lower = elemental.name.to_lowercase();

    for &skill_key in skill_keys {
        // Find which tool maps to this skill
        let tool_name = ELEMENTAL_TOOLS
            .iter()
            .find(|&&(_, sk)| sk == skill_key)
            .map(|&(tn, _)| tn);

        if let Some(tool) = tool_name {
            if item_name_lower.contains(&tool.to_lowercase()) {
                if let Some(skill) = skills.iter_mut().find(|s| s.skill_key == skill_key) {
                    let bonus = (f64::from(elemental.power) / 100.0 * f64::from(skill.level))
                        .floor() as i32;
                    skill.level += bonus;
                }
            }
        }
    }
}

/// Apply skill bless bonus (the single bless on the player, if it targets a skill).
pub fn apply_skill_bless(skills: &mut [PlayerSkill], bless_stat: &str, bless_value: i32) {
    if !bless_stat.is_empty() && bless_value != 0 {
        if let Some(skill) = skills.iter_mut().find(|s| s.skill_key == bless_stat) {
            skill.level += bless_value;
        }
    }
}

/// Apply craftsman class bonus: +10% to all specified skills.
/// If race is Gnome, the bonus is doubled (Gnome craftsman gets +20%).
#[allow(clippy::cast_possible_truncation)]
pub fn apply_craftsman_bonus(skills: &mut [PlayerSkill], skill_keys: &[&str], is_gnome: bool) {
    for &key in skill_keys {
        if let Some(skill) = skills.iter_mut().find(|s| s.skill_key == key) {
            let base_bonus = (f64::from(skill.level) / 10.0).ceil() as i32;
            let total_bonus = if is_gnome { base_bonus * 2 } else { base_bonus };
            skill.level += total_bonus;
        }
    }
}

// ---------------------------------------------------------------------------
// Equip validation — level and class checks
// ---------------------------------------------------------------------------

/// Determine the skill required to equip an item, based on its type.
///
/// Returns the skill key used for the level check, or `None` if no skill
/// check is needed (quest items, etc.).
pub fn required_skill_for_equip(
    equipment_type: EquipmentType,
    item_name: &str,
    skills: &[PlayerSkill],
) -> Option<&'static str> {
    match equipment_type {
        EquipmentType::Weapon => Some("attack"),
        EquipmentType::Bow | EquipmentType::Arrows => Some("shoot"),
        EquipmentType::MageClothing | EquipmentType::Wand => Some("magic"),
        EquipmentType::Helmet
        | EquipmentType::Armor
        | EquipmentType::Shield
        | EquipmentType::Legs => {
            // Armor uses the highest of attack/shoot/magic
            let attack = skill_level(skills, "attack");
            let shoot = skill_level(skills, "shoot");
            let magic = skill_level(skills, "magic");
            if attack > shoot && attack > magic {
                Some("attack")
            } else if shoot > attack && shoot > magic {
                Some("shoot")
            } else {
                Some("magic")
            }
        }
        EquipmentType::Elemental => {
            // Elemental tools use the skill matching the tool type
            let name_lower = item_name.to_lowercase();
            for &(tool_name, skill_key) in &ELEMENTAL_TOOLS {
                if name_lower.contains(&tool_name.to_lowercase()) {
                    return Some(skill_key);
                }
            }
            None
        }
        EquipmentType::Ring | EquipmentType::Quest | EquipmentType::Other | EquipmentType::Plan => {
            None
        }
    }
}

/// Check whether a player can equip an item (level requirement).
pub fn can_equip(
    equipment_type: EquipmentType,
    item_name: &str,
    min_level: i32,
    skills: &[PlayerSkill],
) -> bool {
    match required_skill_for_equip(equipment_type, item_name, skills) {
        Some(skill_key) => skill_level(skills, skill_key) >= min_level,
        None => true,
    }
}

fn skill_level(skills: &[PlayerSkill], key: &str) -> i32 {
    skills
        .iter()
        .find(|s| s.skill_key == key)
        .map_or(0, |s| s.level)
}

fn find_stat_mut<'a>(stats: &'a mut [PlayerStat], key: &str) -> Option<&'a mut PlayerStat> {
    stats.iter_mut().find(|s| s.stat_key == key)
}

// ---------------------------------------------------------------------------
// Tests
// ---------------------------------------------------------------------------

#[cfg(test)]
mod tests {
    use super::*;
    use crate::item::*;
    use crate::player::bonuses::PlayerBonus;
    use crate::player::skills::default_skills;
    use crate::player::stats::default_stats;

    fn make_item(eq_type: EquipmentType, name: &str, power: i32) -> OwnedEquipment {
        OwnedEquipment {
            id: 1,
            owner_id: 1,
            name: name.to_owned(),
            power,
            status: EquipmentStatus::Equipped,
            equipment_type: eq_type,
            cost: 100,
            min_level: 1,
            agility_mod: 0,
            durability: 40,
            speed_mod: 0,
            max_durability: 40,
            magic: Element::None,
            poison: 0,
            amount: 1,
            two_handed: false,
            poison_type: PoisonType::None,
            repair_cost: 50,
            location: "Altara".to_owned(),
        }
    }

    #[test]
    fn loadout_from_empty() {
        let loadout = EquipmentLoadout::from_equipped_items(vec![]);
        assert!(loadout.weapon.is_none());
        assert!(loadout.bow.is_none());
        assert!(loadout.ring1.is_none());
    }

    #[test]
    fn loadout_weapon_and_second_weapon() {
        let w1 = make_item(EquipmentType::Weapon, "Miecz", 10);
        let mut w2 = make_item(EquipmentType::Weapon, "Topór", 12);
        w2.id = 2;
        let loadout = EquipmentLoadout::from_equipped_items(vec![w1, w2]);
        assert_eq!(loadout.weapon.as_ref().unwrap().name, "Miecz");
        assert_eq!(loadout.second_weapon.as_ref().unwrap().name, "Topór");
    }

    #[test]
    fn loadout_two_rings() {
        let r1 = make_item(EquipmentType::Ring, "Pierścień siły", 5);
        let mut r2 = make_item(EquipmentType::Ring, "Pierścień szybkości", 3);
        r2.id = 2;
        let loadout = EquipmentLoadout::from_equipped_items(vec![r1, r2]);
        assert!(loadout.ring1.is_some());
        assert!(loadout.ring2.is_some());
    }

    #[test]
    fn ring_stat_key_strength() {
        assert_eq!(ring_stat_key("Pierścień siły"), Some("strength"));
    }

    #[test]
    fn ring_stat_key_agility() {
        assert_eq!(ring_stat_key("Pierścień zręczności"), Some("agility"));
    }

    #[test]
    fn ring_stat_key_wisdom() {
        assert_eq!(ring_stat_key("Pierścień woli"), Some("wisdom"));
    }

    #[test]
    fn ring_stat_key_speed() {
        assert_eq!(ring_stat_key("Pierścień szybkości"), Some("speed"));
    }

    #[test]
    fn ring_stat_key_inteli() {
        assert_eq!(ring_stat_key("Pierścień inteligencji"), Some("inteli"));
    }

    #[test]
    fn ring_stat_key_condition() {
        assert_eq!(ring_stat_key("Pierścień kondycji"), Some("condition"));
    }

    #[test]
    fn ring_stat_key_unknown() {
        assert_eq!(ring_stat_key("Tajemniczy Pierścień"), None);
    }

    #[test]
    fn agility_penalty_from_armor() {
        let mut stats = default_stats();
        // Give agility a base modified value
        find_stat_mut(&mut stats, "agility").unwrap().modified = 100;

        let mut armor = make_item(EquipmentType::Armor, "Zbroja z miedzi", 30);
        armor.agility_mod = 5;
        let loadout = EquipmentLoadout::from_equipped_items(vec![armor]);

        apply_equipment_stat_bonuses(&mut stats, &loadout, "", 0, &[]);

        let agi = stats.iter().find(|s| s.stat_key == "agility").unwrap();
        assert_eq!(agi.modified, 95); // 100 - 5
    }

    #[test]
    fn bow_speed_bonus() {
        let mut stats = default_stats();
        find_stat_mut(&mut stats, "speed").unwrap().modified = 50;

        let mut bow = make_item(EquipmentType::Bow, "Łuk z miedzi", 0);
        bow.speed_mod = 10;
        let loadout = EquipmentLoadout::from_equipped_items(vec![bow]);

        apply_equipment_stat_bonuses(&mut stats, &loadout, "", 0, &[]);

        let spd = stats.iter().find(|s| s.stat_key == "speed").unwrap();
        assert_eq!(spd.modified, 60);
    }

    #[test]
    fn ring_stat_bonus_applied() {
        let mut stats = default_stats();
        find_stat_mut(&mut stats, "strength").unwrap().modified = 20;

        let ring = make_item(EquipmentType::Ring, "Pierścień siły", 5);
        let loadout = EquipmentLoadout::from_equipped_items(vec![ring]);

        apply_equipment_stat_bonuses(&mut stats, &loadout, "", 0, &[]);

        let str_stat = stats.iter().find(|s| s.stat_key == "strength").unwrap();
        assert_eq!(str_stat.modified, 25);
    }

    #[test]
    fn blessing_adds_to_stat() {
        let mut stats = default_stats();
        find_stat_mut(&mut stats, "wisdom").unwrap().modified = 30;

        let loadout = EquipmentLoadout::default();
        apply_equipment_stat_bonuses(&mut stats, &loadout, "wisdom", 10, &[]);

        let wis = stats.iter().find(|s| s.stat_key == "wisdom").unwrap();
        assert_eq!(wis.modified, 40);
    }

    #[test]
    fn check_bonus_stat_scaled() {
        let mut stats = default_stats();
        find_stat_mut(&mut stats, "strength").unwrap().modified = 100;
        let skills = default_skills();

        let bonuses = vec![PlayerBonus {
            id: 0,
            catalog_id: 1,
            bonus_name: "strength".to_owned(),
            value: 2,    // level
            duration: 5, // magnitude
        }];

        // ceil(100 * (2 * 5 / 100)) = ceil(100 * 0.1) = ceil(10.0) = 10
        let result = check_bonus("strength", &stats, &skills, &bonuses);
        assert_eq!(result, 10);
    }

    #[test]
    fn check_bonus_skill_scaled() {
        let stats = default_stats();
        let mut skills = default_skills();
        skills
            .iter_mut()
            .find(|s| s.skill_key == "mining")
            .unwrap()
            .level = 50;

        let bonuses = vec![PlayerBonus {
            id: 0,
            catalog_id: 2,
            bonus_name: "mining".to_owned(),
            value: 3,
            duration: 10,
        }];

        // ceil(50 * (3 * 10 / 100)) = ceil(50 * 0.3) = ceil(15.0) = 15
        let result = check_bonus("mining", &stats, &skills, &bonuses);
        assert_eq!(result, 15);
    }

    #[test]
    fn check_bonus_assassin_raw() {
        let stats = default_stats();
        let skills = default_skills();
        let bonuses = vec![PlayerBonus {
            id: 0,
            catalog_id: 3,
            bonus_name: "assasin".to_owned(),
            value: 3,
            duration: 5,
        }];

        // assassin: level * magnitude = 3 * 5 = 15
        assert_eq!(check_bonus("assasin", &stats, &skills, &bonuses), 15);
    }

    #[test]
    fn check_bonus_missing() {
        let stats = default_stats();
        let skills = default_skills();
        assert_eq!(check_bonus("nonexistent", &stats, &skills, &[]), 0);
    }

    #[test]
    fn can_equip_weapon_sufficient_level() {
        let mut skills = default_skills();
        skills
            .iter_mut()
            .find(|s| s.skill_key == "attack")
            .unwrap()
            .level = 10;
        assert!(can_equip(EquipmentType::Weapon, "Miecz", 10, &skills));
    }

    #[test]
    fn cannot_equip_weapon_insufficient_level() {
        let mut skills = default_skills();
        skills
            .iter_mut()
            .find(|s| s.skill_key == "attack")
            .unwrap()
            .level = 5;
        assert!(!can_equip(EquipmentType::Weapon, "Miecz", 10, &skills));
    }

    #[test]
    fn elemental_skill_bonus() {
        let mut skills = default_skills();
        skills
            .iter_mut()
            .find(|s| s.skill_key == "mining")
            .unwrap()
            .level = 100;

        let mut elemental = make_item(EquipmentType::Elemental, "Magiczny kilof", 50);
        elemental.durability = 10;
        let loadout = EquipmentLoadout::from_equipped_items(vec![elemental]);

        apply_elemental_skill_bonus(&mut skills, &loadout, &["mining"]);

        let mining = skills.iter().find(|s| s.skill_key == "mining").unwrap();
        // floor(50/100 * 100) = floor(50) = 50, so 100 + 50 = 150
        assert_eq!(mining.level, 150);
    }

    #[test]
    fn craftsman_bonus_basic() {
        let mut skills = default_skills();
        skills
            .iter_mut()
            .find(|s| s.skill_key == "smith")
            .unwrap()
            .level = 100;

        apply_craftsman_bonus(&mut skills, &["smith"], false);

        let smith = skills.iter().find(|s| s.skill_key == "smith").unwrap();
        // ceil(100/10) = 10, so 100 + 10 = 110
        assert_eq!(smith.level, 110);
    }

    #[test]
    fn craftsman_bonus_gnome() {
        let mut skills = default_skills();
        skills
            .iter_mut()
            .find(|s| s.skill_key == "smith")
            .unwrap()
            .level = 100;

        apply_craftsman_bonus(&mut skills, &["smith"], true);

        let smith = skills.iter().find(|s| s.skill_key == "smith").unwrap();
        // Gnome doubles the base 1/10 bonus: ceil(100/10) * 2 = 20, so 100 + 20 = 120
        assert_eq!(smith.level, 120);
    }

    #[test]
    fn combined_equipment_bonuses() {
        let mut stats = default_stats();
        for s in &mut stats {
            s.modified = 50;
        }

        let mut armor = make_item(EquipmentType::Armor, "Zbroja", 30);
        armor.agility_mod = 3;
        let mut helmet = make_item(EquipmentType::Helmet, "Hełm", 10);
        helmet.id = 2;
        helmet.agility_mod = 1;
        let mut bow = make_item(EquipmentType::Bow, "Łuk", 0);
        bow.id = 3;
        bow.speed_mod = 5;
        let mut ring = make_item(EquipmentType::Ring, "Pierścień kondycji", 8);
        ring.id = 4;

        let loadout = EquipmentLoadout::from_equipped_items(vec![armor, helmet, bow, ring]);

        apply_equipment_stat_bonuses(&mut stats, &loadout, "inteli", 7, &[]);

        // Agility: 50 - 3 - 1 = 46
        let agi = stats.iter().find(|s| s.stat_key == "agility").unwrap();
        assert_eq!(agi.modified, 46);

        // Speed: 50 + 5 = 55
        let spd = stats.iter().find(|s| s.stat_key == "speed").unwrap();
        assert_eq!(spd.modified, 55);

        // Condition: 50 + 8 = 58
        let cond = stats.iter().find(|s| s.stat_key == "condition").unwrap();
        assert_eq!(cond.modified, 58);

        // Intelligence: 50 + 7 (bless) = 57
        let inteli = stats.iter().find(|s| s.stat_key == "inteli").unwrap();
        assert_eq!(inteli.modified, 57);
    }

    #[test]
    fn iter_occupied_counts() {
        let w = make_item(EquipmentType::Weapon, "Sword", 10);
        let mut a = make_item(EquipmentType::Armor, "Plate", 20);
        a.id = 2;
        let loadout = EquipmentLoadout::from_equipped_items(vec![w, a]);
        assert_eq!(loadout.iter_occupied().count(), 2);
    }

    #[test]
    fn empty_bless_does_nothing() {
        let mut stats = default_stats();
        find_stat_mut(&mut stats, "strength").unwrap().modified = 50;
        let loadout = EquipmentLoadout::default();
        apply_equipment_stat_bonuses(&mut stats, &loadout, "", 0, &[]);
        let str_stat = stats.iter().find(|s| s.stat_key == "strength").unwrap();
        assert_eq!(str_stat.modified, 50);
    }
}
