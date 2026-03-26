//! Item domain types — catalog definitions and player-owned instances.
//!
//! The legacy system stores everything in a single `equipment` table with a
//! `type` char that distinguishes weapons, armor, shields, helmets, leg armor,
//! bows (via the separate `bows` table), rings, mage clothing, wands, quest
//! items, plans, and elemental items. Spells (`czary`), potions, and mage
//! items each have their own tables.
//!
//! The Rust model keeps category differences explicit via typed enums while
//! sharing common fields through a single `OwnedEquipment` struct for the
//! `equipment` table.

use serde::{Deserialize, Serialize};

// ---------------------------------------------------------------------------
// Equipment type — maps to `equipment.type` char column
// ---------------------------------------------------------------------------

/// Equipment slot/category stored as a single char in the `type` column.
#[derive(Debug, Clone, Copy, PartialEq, Eq, Hash, Serialize, Deserialize)]
pub enum EquipmentType {
    /// Weapon (melee).
    Weapon,
    /// Body armor.
    Armor,
    /// Shield.
    Shield,
    /// Helmet.
    Helmet,
    /// Leg armor.
    Legs,
    /// Bow/ranged weapon (links to `bows` catalog for base stats).
    Bow,
    /// Arrows/ammo (consumable, measured in shots).
    Arrows,
    /// Mage clothing (provides mana bonus).
    MageClothing,
    /// Wand/staff (enhances spell power).
    Wand,
    /// Ring (stat bonus accessory).
    Ring,
    /// Elemental item.
    Elemental,
    /// Quest item.
    Quest,
    /// Other/miscellaneous.
    Other,
    /// Crafting plan.
    Plan,
}

impl EquipmentType {
    /// Parse from the single-char value in the database `type` column.
    pub fn from_db(c: &str) -> Option<Self> {
        match c {
            "W" => Some(Self::Weapon),
            "A" => Some(Self::Armor),
            "S" => Some(Self::Shield),
            "H" => Some(Self::Helmet),
            "L" => Some(Self::Legs),
            "B" => Some(Self::Bow),
            "R" => Some(Self::Arrows),
            "C" => Some(Self::MageClothing),
            "T" => Some(Self::Wand),
            "I" => Some(Self::Ring),
            "E" => Some(Self::Elemental),
            "Q" => Some(Self::Quest),
            "O" => Some(Self::Other),
            "P" => Some(Self::Plan),
            _ => None,
        }
    }

    /// Return the single-char string for database storage.
    pub fn to_db(&self) -> &'static str {
        match self {
            Self::Weapon => "W",
            Self::Armor => "A",
            Self::Shield => "S",
            Self::Helmet => "H",
            Self::Legs => "L",
            Self::Bow => "B",
            Self::Arrows => "R",
            Self::MageClothing => "C",
            Self::Wand => "T",
            Self::Ring => "I",
            Self::Elemental => "E",
            Self::Quest => "Q",
            Self::Other => "O",
            Self::Plan => "P",
        }
    }

    /// Whether this type occupies a combat equipment slot.
    pub fn is_equippable(&self) -> bool {
        !matches!(self, Self::Quest | Self::Other | Self::Plan)
    }

    /// Whether this item type has durability tracking.
    pub fn has_durability(&self) -> bool {
        !matches!(
            self,
            Self::Quest | Self::Other | Self::Ring | Self::Plan | Self::Arrows
        )
    }
}

// ---------------------------------------------------------------------------
// Equipment status — maps to `equipment.status` char column
// ---------------------------------------------------------------------------

/// Status of an equipment item: equipped, unequipped, or shop stock.
#[derive(Debug, Clone, Copy, PartialEq, Eq, Hash, Serialize, Deserialize)]
pub enum EquipmentStatus {
    /// Equipped by the player.
    Equipped,
    /// In the player's backpack (unequipped).
    Unequipped,
    /// Shop stock template (owner = 0).
    Shop,
}

impl EquipmentStatus {
    pub fn from_db(c: &str) -> Option<Self> {
        match c {
            "E" => Some(Self::Equipped),
            "U" => Some(Self::Unequipped),
            "S" => Some(Self::Shop),
            _ => None,
        }
    }

    pub fn to_db(&self) -> &'static str {
        match self {
            Self::Equipped => "E",
            Self::Unequipped => "U",
            Self::Shop => "S",
        }
    }
}

// ---------------------------------------------------------------------------
// Magic element — maps to `equipment.magic` and `czary.element`
// ---------------------------------------------------------------------------

/// Elemental affinity for magic enhancement or spell element.
#[derive(Debug, Clone, Copy, PartialEq, Eq, Hash, Serialize, Deserialize)]
pub enum Element {
    None,
    Earth,
    Water,
    Fire,
    Wind,
}

impl Element {
    /// Parse from equipment `magic` column (single char: N/E/W/F/A).
    pub fn from_equipment_code(c: &str) -> Self {
        match c {
            "E" => Self::Earth,
            "W" => Self::Water,
            "F" => Self::Fire,
            "A" => Self::Wind,
            _ => Self::None,
        }
    }

    /// Return the equipment `magic` column char.
    pub fn to_equipment_code(&self) -> &'static str {
        match self {
            Self::None => "N",
            Self::Earth => "E",
            Self::Water => "W",
            Self::Fire => "F",
            Self::Wind => "A",
        }
    }

    /// Parse from spell `element` column (word: earth/water/fire/wind).
    pub fn from_spell_code(s: &str) -> Self {
        match s {
            "earth" => Self::Earth,
            "water" => Self::Water,
            "fire" => Self::Fire,
            "wind" => Self::Wind,
            _ => Self::None,
        }
    }

    /// Return the spell element string.
    pub fn to_spell_code(&self) -> &'static str {
        match self {
            Self::None => "none",
            Self::Earth => "earth",
            Self::Water => "water",
            Self::Fire => "fire",
            Self::Wind => "wind",
        }
    }
}

// ---------------------------------------------------------------------------
// Poison type — maps to `equipment.ptype`
// ---------------------------------------------------------------------------

/// Type of poison applied to a weapon.
#[derive(Debug, Clone, Copy, PartialEq, Eq, Hash, Serialize, Deserialize)]
pub enum PoisonType {
    None,
    /// Dynallca poison — weapon damage bonus.
    Dynallca,
    /// Nutari poison — reduces opponent's PM.
    Nutari,
    /// Illani poison — special damage bonus.
    Illani,
}

impl PoisonType {
    pub fn from_db(c: &str) -> Self {
        match c {
            "D" => Self::Dynallca,
            "N" => Self::Nutari,
            "I" => Self::Illani,
            _ => Self::None,
        }
    }

    pub fn to_db(&self) -> &'static str {
        match self {
            Self::None => "",
            Self::Dynallca => "D",
            Self::Nutari => "N",
            Self::Illani => "I",
        }
    }
}

// ---------------------------------------------------------------------------
// Owned equipment — player-owned instance from the `equipment` table
// ---------------------------------------------------------------------------

/// A player-owned equipment item (row from the `equipment` table).
/// `owner = 0` means shop stock template.
#[derive(Debug, Clone, Serialize, Deserialize)]
pub struct OwnedEquipment {
    pub id: i32,
    pub owner_id: i32,
    pub name: String,
    pub power: i32,
    pub status: EquipmentStatus,
    pub equipment_type: EquipmentType,
    pub cost: i64,
    pub min_level: i32,
    /// Agility modifier (negative = penalty in legacy, but stored as positive
    /// for armor meaning "agility reduction").
    pub agility_mod: i32,
    /// Current durability (for arrows: remaining shots).
    pub durability: i32,
    /// Speed bonus.
    pub speed_mod: i32,
    /// Maximum durability.
    pub max_durability: i32,
    /// Magic element enhancement.
    pub magic: Element,
    /// Poison strength (0 = none).
    pub poison: i32,
    /// Stack count (for stackable items like mage clothing, arrows).
    pub amount: i32,
    /// Whether this is a two-handed weapon.
    pub two_handed: bool,
    /// Poison type applied to this item.
    pub poison_type: PoisonType,
    /// Repair cost multiplier.
    pub repair_cost: i32,
    /// Location where item is stored/available.
    pub location: String,
}

impl OwnedEquipment {
    /// Whether this item needs repair (durability < max).
    pub fn needs_repair(&self) -> bool {
        self.equipment_type.has_durability() && self.durability < self.max_durability
    }

    /// Calculate repair gold cost based on current vs max durability.
    #[allow(clippy::cast_possible_truncation)]
    pub fn calculate_repair_cost(&self) -> i64 {
        if !self.needs_repair() {
            return 0;
        }
        let ratio = 1.0 - (f64::from(self.durability) / f64::from(self.max_durability));
        (f64::from(self.repair_cost) * ratio).ceil() as i64
    }

    /// Whether this item is a shop template (not player-owned).
    pub fn is_shop_stock(&self) -> bool {
        self.owner_id == 0
    }
}

// ---------------------------------------------------------------------------
// Spell types — maps to `czary.typ`
// ---------------------------------------------------------------------------

/// Spell category from the `czary` table.
#[derive(Debug, Clone, Copy, PartialEq, Eq, Hash, Serialize, Deserialize)]
pub enum SpellType {
    /// Battle/offensive spell.
    Battle,
    /// Defensive/protection spell.
    Defense,
    /// Utility spell (item enhancement).
    Utility,
}

impl SpellType {
    pub fn from_db(c: &str) -> Option<Self> {
        match c {
            "B" => Some(Self::Battle),
            "O" => Some(Self::Defense),
            "U" => Some(Self::Utility),
            _ => None,
        }
    }

    pub fn to_db(&self) -> &'static str {
        match self {
            Self::Battle => "B",
            Self::Defense => "O",
            Self::Utility => "U",
        }
    }
}

/// Spell status (owned vs shop).
#[derive(Debug, Clone, Copy, PartialEq, Eq, Hash, Serialize, Deserialize)]
pub enum SpellStatus {
    /// Available in shop / base definition.
    Shop,
    /// Owned/active for a player.
    Active,
}

impl SpellStatus {
    pub fn from_db(c: &str) -> Option<Self> {
        match c {
            "S" => Some(Self::Shop),
            "A" => Some(Self::Active),
            _ => None,
        }
    }

    pub fn to_db(&self) -> &'static str {
        match self {
            Self::Shop => "S",
            Self::Active => "A",
        }
    }
}

/// A spell record from the `czary` table. When `owner = 0`, this is a catalog
/// definition; otherwise it is a player-owned spell.
#[derive(Debug, Clone, Serialize, Deserialize)]
pub struct Spell {
    pub id: i32,
    /// Spell name.
    pub name: String,
    /// Owning player (0 = catalog entry).
    pub owner_id: i32,
    /// Gold cost.
    pub cost: i64,
    /// Minimum level to learn.
    pub level: i32,
    /// Spell type (battle/defense/utility).
    pub spell_type: SpellType,
    /// Damage/defense multiplier.
    pub multiplier: f64,
    /// Status (shop catalog or player-active).
    pub status: SpellStatus,
    /// Elemental affinity.
    pub element: Element,
}

// ---------------------------------------------------------------------------
// Potion types — maps to `potions.type`
// ---------------------------------------------------------------------------

/// Potion category from the `potions` table.
#[derive(Debug, Clone, Copy, PartialEq, Eq, Hash, Serialize, Deserialize)]
pub enum PotionType {
    /// Mana regeneration.
    Mana,
    /// Health regeneration.
    Health,
    /// Poison (weapon enhancement / combat debuff).
    Poison,
    /// Antidote / special.
    Antidote,
}

impl PotionType {
    pub fn from_db(c: &str) -> Option<Self> {
        match c {
            "M" => Some(Self::Mana),
            "H" => Some(Self::Health),
            "P" => Some(Self::Poison),
            "A" => Some(Self::Antidote),
            _ => None,
        }
    }

    pub fn to_db(&self) -> &'static str {
        match self {
            Self::Mana => "M",
            Self::Health => "H",
            Self::Poison => "P",
            Self::Antidote => "A",
        }
    }
}

/// A potion record from the `potions` table.
#[derive(Debug, Clone, Serialize, Deserialize)]
pub struct Potion {
    pub id: i32,
    /// Owning player (0 = catalog stock).
    pub owner_id: i32,
    pub name: String,
    pub potion_type: PotionType,
    /// Description of the effect.
    pub effect: String,
    /// Status (S = shop catalog, A = active/owned).
    pub status: SpellStatus,
    /// Effect strength.
    pub power: i32,
    /// Quantity.
    pub amount: i32,
    /// Gold cost.
    pub cost: i64,
}

// ---------------------------------------------------------------------------
// Mage item types — maps to `mage_items.type`
// ---------------------------------------------------------------------------

/// Mage item category from the `mage_items` catalog table.
#[derive(Debug, Clone, Copy, PartialEq, Eq, Hash, Serialize, Deserialize)]
pub enum MageItemType {
    /// Wand/staff (spell power enhancer).
    Wand,
    /// Mage clothing (mana bonus).
    Clothing,
}

impl MageItemType {
    pub fn from_db(c: &str) -> Option<Self> {
        match c {
            "T" => Some(Self::Wand),
            "C" => Some(Self::Clothing),
            _ => None,
        }
    }

    pub fn to_db(&self) -> &'static str {
        match self {
            Self::Wand => "T",
            Self::Clothing => "C",
        }
    }
}

/// A mage item catalog entry from the `mage_items` table.
/// These are base definitions; player instances are in the `equipment` table
/// with corresponding type codes (T for wand, C for clothing).
#[derive(Debug, Clone, Serialize, Deserialize)]
pub struct MageItemCatalog {
    pub id: i32,
    pub name: String,
    /// For clothing: mana % bonus. For wands: 0 (enhances spell power implicitly).
    pub power: i32,
    pub item_type: MageItemType,
    pub cost: i64,
    pub min_level: i32,
}

// ---------------------------------------------------------------------------
// Bow catalog — already in `bows` table migration, domain type for clarity
// ---------------------------------------------------------------------------

/// Bow/arrow catalog type from the `bows` table.
#[derive(Debug, Clone, Copy, PartialEq, Eq, Hash, Serialize, Deserialize)]
pub enum BowType {
    /// Bow (ranged weapon).
    Bow,
    /// Arrows (ammo, consumable).
    Arrows,
}

impl BowType {
    pub fn from_db(c: &str) -> Option<Self> {
        match c {
            "B" => Some(Self::Bow),
            "R" => Some(Self::Arrows),
            _ => None,
        }
    }

    pub fn to_db(&self) -> &'static str {
        match self {
            Self::Bow => "B",
            Self::Arrows => "R",
        }
    }
}

/// A bow/arrow catalog entry from the `bows` table.
#[derive(Debug, Clone, Serialize, Deserialize)]
pub struct BowCatalog {
    pub id: i32,
    pub name: String,
    /// For arrows: damage. For bows: 0 (base weapon).
    pub power: i32,
    pub bow_type: BowType,
    pub cost: i64,
    pub min_level: i32,
    /// Agility modifier.
    pub agility_mod: i32,
    /// Speed bonus.
    pub speed_mod: i32,
    /// Max durability (for bows) / max shots (for arrows).
    pub max_durability: i32,
    /// Repair cost multiplier.
    pub repair_cost: i32,
}

// ---------------------------------------------------------------------------
// Ring catalog — from the `rings` table
// ---------------------------------------------------------------------------

/// A ring catalog entry from the `rings` table.
#[derive(Debug, Clone, Serialize, Deserialize)]
pub struct RingCatalog {
    pub id: i32,
    pub name: String,
    /// Stat bonus amount.
    pub amount: i32,
}

// ---------------------------------------------------------------------------
// Equipment slot mapping — for the `equipment()` method in player_class.php
// ---------------------------------------------------------------------------

/// Named equipment slots matching the PHP `equipment()` method's array
/// positions. Used for looking up what a player has equipped.
#[derive(Debug, Clone, Copy, PartialEq, Eq, Hash, Serialize, Deserialize)]
pub enum EquipmentSlot {
    Weapon,
    Bow,
    Helmet,
    Armor,
    Legs,
    Shield,
    Arrows,
    Wand,
    MageClothing,
    Ring1,
    Ring2,
    /// Second weapon (dual-wield for certain classes).
    SecondWeapon,
    /// Elemental item.
    Elemental,
}

impl EquipmentSlot {
    /// Map from `EquipmentType` to the primary slot. Note that some types
    /// (Ring, Weapon) can occupy multiple slots — use `resolve_slot` for
    /// context-aware placement.
    pub fn primary_for(eq_type: EquipmentType) -> Option<Self> {
        match eq_type {
            EquipmentType::Weapon => Some(Self::Weapon),
            EquipmentType::Armor => Some(Self::Armor),
            EquipmentType::Shield => Some(Self::Shield),
            EquipmentType::Helmet => Some(Self::Helmet),
            EquipmentType::Legs => Some(Self::Legs),
            EquipmentType::Bow => Some(Self::Bow),
            EquipmentType::Arrows => Some(Self::Arrows),
            EquipmentType::MageClothing => Some(Self::MageClothing),
            EquipmentType::Wand => Some(Self::Wand),
            EquipmentType::Ring => Some(Self::Ring1),
            EquipmentType::Elemental => Some(Self::Elemental),
            EquipmentType::Quest | EquipmentType::Other | EquipmentType::Plan => None,
        }
    }
}

// ---------------------------------------------------------------------------
// Tests
// ---------------------------------------------------------------------------

#[cfg(test)]
mod tests {
    use super::*;

    #[test]
    fn equipment_type_round_trip() {
        let types = [
            EquipmentType::Weapon,
            EquipmentType::Armor,
            EquipmentType::Shield,
            EquipmentType::Helmet,
            EquipmentType::Legs,
            EquipmentType::Bow,
            EquipmentType::Arrows,
            EquipmentType::MageClothing,
            EquipmentType::Wand,
            EquipmentType::Ring,
            EquipmentType::Elemental,
            EquipmentType::Quest,
            EquipmentType::Other,
            EquipmentType::Plan,
        ];
        for t in types {
            let db_val = t.to_db();
            let parsed = EquipmentType::from_db(db_val).unwrap();
            assert_eq!(parsed, t, "round-trip failed for {db_val}");
        }
    }

    #[test]
    fn equipment_status_round_trip() {
        for s in [
            EquipmentStatus::Equipped,
            EquipmentStatus::Unequipped,
            EquipmentStatus::Shop,
        ] {
            assert_eq!(EquipmentStatus::from_db(s.to_db()).unwrap(), s,);
        }
    }

    #[test]
    fn element_equipment_round_trip() {
        for e in [
            Element::None,
            Element::Earth,
            Element::Water,
            Element::Fire,
            Element::Wind,
        ] {
            assert_eq!(Element::from_equipment_code(e.to_equipment_code()), e,);
        }
    }

    #[test]
    fn element_spell_round_trip() {
        for e in [Element::Earth, Element::Water, Element::Fire, Element::Wind] {
            assert_eq!(Element::from_spell_code(e.to_spell_code()), e,);
        }
    }

    #[test]
    fn poison_type_round_trip() {
        for p in [
            PoisonType::None,
            PoisonType::Dynallca,
            PoisonType::Nutari,
            PoisonType::Illani,
        ] {
            assert_eq!(PoisonType::from_db(p.to_db()), p);
        }
    }

    #[test]
    fn spell_type_round_trip() {
        for t in [SpellType::Battle, SpellType::Defense, SpellType::Utility] {
            assert_eq!(SpellType::from_db(t.to_db()).unwrap(), t);
        }
    }

    #[test]
    fn potion_type_round_trip() {
        for t in [
            PotionType::Mana,
            PotionType::Health,
            PotionType::Poison,
            PotionType::Antidote,
        ] {
            assert_eq!(PotionType::from_db(t.to_db()).unwrap(), t);
        }
    }

    #[test]
    fn mage_item_type_round_trip() {
        for t in [MageItemType::Wand, MageItemType::Clothing] {
            assert_eq!(MageItemType::from_db(t.to_db()).unwrap(), t);
        }
    }

    #[test]
    fn bow_type_round_trip() {
        for t in [BowType::Bow, BowType::Arrows] {
            assert_eq!(BowType::from_db(t.to_db()).unwrap(), t);
        }
    }

    #[test]
    fn repair_cost_calculation() {
        let item = OwnedEquipment {
            id: 1,
            owner_id: 1,
            name: "Test Sword".to_owned(),
            power: 10,
            status: EquipmentStatus::Equipped,
            equipment_type: EquipmentType::Weapon,
            cost: 1000,
            min_level: 1,
            agility_mod: 0,
            durability: 20,
            speed_mod: 0,
            max_durability: 40,
            magic: Element::None,
            poison: 0,
            amount: 1,
            two_handed: false,
            poison_type: PoisonType::None,
            repair_cost: 100,
            location: "Altara".to_owned(),
        };
        // 50% worn = ceil(100 * 0.5) = 50
        assert_eq!(item.calculate_repair_cost(), 50);
        assert!(item.needs_repair());
    }

    #[test]
    fn full_durability_no_repair() {
        let item = OwnedEquipment {
            id: 2,
            owner_id: 1,
            name: "Full Armor".to_owned(),
            power: 30,
            status: EquipmentStatus::Equipped,
            equipment_type: EquipmentType::Armor,
            cost: 5000,
            min_level: 5,
            agility_mod: 2,
            durability: 40,
            speed_mod: 0,
            max_durability: 40,
            magic: Element::None,
            poison: 0,
            amount: 1,
            two_handed: false,
            poison_type: PoisonType::None,
            repair_cost: 60,
            location: "Altara".to_owned(),
        };
        assert_eq!(item.calculate_repair_cost(), 0);
        assert!(!item.needs_repair());
    }

    #[test]
    fn quest_items_not_equippable() {
        assert!(!EquipmentType::Quest.is_equippable());
        assert!(!EquipmentType::Other.is_equippable());
        assert!(!EquipmentType::Plan.is_equippable());
        assert!(EquipmentType::Weapon.is_equippable());
        assert!(EquipmentType::Ring.is_equippable());
    }

    #[test]
    fn durability_not_tracked_for_quest_and_arrows() {
        assert!(!EquipmentType::Quest.has_durability());
        assert!(!EquipmentType::Arrows.has_durability());
        assert!(!EquipmentType::Ring.has_durability());
        assert!(EquipmentType::Weapon.has_durability());
        assert!(EquipmentType::Armor.has_durability());
    }

    #[test]
    fn equipment_slot_mapping() {
        assert_eq!(
            EquipmentSlot::primary_for(EquipmentType::Weapon),
            Some(EquipmentSlot::Weapon)
        );
        assert_eq!(EquipmentSlot::primary_for(EquipmentType::Quest), None);
        assert_eq!(
            EquipmentSlot::primary_for(EquipmentType::Ring),
            Some(EquipmentSlot::Ring1)
        );
    }
}
