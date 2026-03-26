//! Per-category market rules and item-shape documentation.
//!
//! Each [`MarketCategory`] stores goods in a different way. This module
//! documents the storage shape, listing rules, quantity semantics, and
//! buy-side effects for each category so that the shared market service
//! (MP-10-03) can delegate correctly.
//!
//! # Category–table mapping
//!
//! | Category   | Table(s)                     | Qty field        | Merge key                              |
//! |------------|------------------------------|------------------|---------------------------------------|
//! | Minerals   | `pmarket`                    | `ilosc`          | `nazwa` (commodity name)              |
//! | Equipment  | `equipment` (status='R')     | `amount`/`wt`    | full attribute match (see below)      |
//! | Potions    | `potions` (status='R')       | `amount`         | `name` + `power`                      |
//! | Herbs      | `hmarket`                    | `ilosc`          | `nazwa` (herb name)                   |
//! | Astral     | `amarket`                    | `amount`         | `type` + `number`                     |
//! | Jewellery  | `equipment` (status='R', type='I') | `amount`  | full attribute match                  |
//! | Loot       | `equipment` (status='R', type='O') | `amount`  | full attribute match                  |
//! | Pets       | `core_market`                | 1 per row        | never merges                          |
//!
//! # Equipment merge logic
//!
//! Equipment, Jewellery, and Loot share the `equipment` table.
//! When listing or cancelling, the PHP code checks for an existing row
//! with identical stats to merge quantities rather than creating a new
//! row. The match key uses: `name`, `wt`, `type`, `power`, `zr`, `szyb`,
//! `maxwt`, `poison`, `ptype`, `twohand`.
//!
//! Arrows (type='R') are special: `wt` tracks quantity instead of `amount`,
//! and `maxwt` mirrors `wt`.

use super::market::MarketCategory;

// ---------------------------------------------------------------------------
// Quantity semantics
// ---------------------------------------------------------------------------

/// How quantity is tracked on a listing row for each category.
#[derive(Debug, Clone, Copy, PartialEq, Eq)]
pub enum QuantitySemantic {
    /// A dedicated integer column holds the listing quantity.
    ColumnBased,
    /// Each row is exactly one unit (pets).
    OnePerRow,
    /// Equipment arrows (type='R'): `wt` field holds quantity, regular items use `amount`.
    EquipmentDual,
}

impl MarketCategory {
    /// How quantity is represented in the market table for this category.
    pub fn quantity_semantic(self) -> QuantitySemantic {
        match self {
            Self::Minerals | Self::Herbs | Self::Potions | Self::Astral => {
                QuantitySemantic::ColumnBased
            }
            Self::Equipment | Self::Jewellery | Self::Loot => QuantitySemantic::EquipmentDual,
            Self::Pets => QuantitySemantic::OnePerRow,
        }
    }

    /// Whether the buy operation credits the seller's *bank* (not hand credits).
    ///
    /// In the PHP code, every marketplace purchase credits `players.bank`
    /// on the seller, and debits `players.credits` on the buyer.
    pub fn seller_receives_bank(self) -> bool {
        true
    }

    /// Whether the buy operation logs a notification for the seller.
    pub fn logs_seller_notification(self) -> bool {
        true
    }
}

// ---------------------------------------------------------------------------
// Mineral-specific rules
// ---------------------------------------------------------------------------

/// The 18 mineral commodity types in order.
///
/// Index 0 = Mithril (uses `players.platinum`), indices 1–17 use `minerals` table columns.
pub const MINERAL_COLUMNS: [&str; 18] = [
    "",           // 0: Mithril → players.platinum
    "copperore",  // 1
    "zincore",    // 2
    "tinore",     // 3
    "ironore",    // 4
    "copper",     // 5
    "bronze",     // 6
    "brass",      // 7
    "iron",       // 8
    "steel",      // 9
    "coal",       // 10
    "adamantium", // 11
    "meteor",     // 12
    "crystal",    // 13
    "pine",       // 14
    "hazel",      // 15
    "yew",        // 16
    "elm",        // 17
];

/// Whether a mineral index refers to Mithril/platinum (special handling).
pub fn is_mithril(mineral_index: usize) -> bool {
    mineral_index == 0
}

// ---------------------------------------------------------------------------
// Herb-specific rules
// ---------------------------------------------------------------------------

/// The 8 herb column names in `herbs` table order.
pub const HERB_COLUMNS: [&str; 8] = [
    "illani",
    "illanias",
    "nutari",
    "dynallca",
    "ilani_seeds",
    "illanias_seeds",
    "nutari_seeds",
    "dynallca_seeds",
];

// ---------------------------------------------------------------------------
// Astral-specific rules
// ---------------------------------------------------------------------------

/// Astral item type prefix → category offset for display name mapping.
///
/// The PHP uses a single-char prefix (M/P/R/C/O/T) with a numeric suffix
/// to identify astral subtypes:
///
/// | Prefix | Meaning       | Name offset |
/// |--------|---------------|-------------|
/// | M      | Maps          | 0           |
/// | P      | Plans         | 7           |
/// | R      | Recipes       | 12          |
/// | C      | Components    | 17          |
/// | O      | Constructs    | 24          |
/// | T      | Elixirs       | 29          |
///
/// Items with prefix M/P/R have a `number` sub-field (displayed as number+1).
/// Items with prefix C/O/T show "-" for the number column.
#[derive(Debug, Clone, Copy, PartialEq, Eq)]
pub enum AstralPrefix {
    Map,
    Plan,
    Recipe,
    Component,
    Construct,
    Elixir,
}

impl AstralPrefix {
    pub fn from_char(c: char) -> Option<Self> {
        match c {
            'M' => Some(Self::Map),
            'P' => Some(Self::Plan),
            'R' => Some(Self::Recipe),
            'C' => Some(Self::Component),
            'O' => Some(Self::Construct),
            'T' => Some(Self::Elixir),
            _ => None,
        }
    }

    /// Offset into the global astral names array for display.
    pub fn name_offset(self) -> usize {
        match self {
            Self::Map => 0,
            Self::Plan => 7,
            Self::Recipe => 12,
            Self::Component => 17,
            Self::Construct => 24,
            Self::Elixir => 29,
        }
    }

    /// Whether this astral type has a meaningful `number` sub-field.
    pub fn has_number(self) -> bool {
        matches!(self, Self::Map | Self::Plan | Self::Recipe)
    }
}

// ---------------------------------------------------------------------------
// Equipment arrows special handling
// ---------------------------------------------------------------------------

/// For equipment type='R' (arrows), quantity is stored in `wt` instead of `amount`.
/// This helper returns the correct field name for quantity operations.
pub fn equipment_quantity_field(equipment_type_char: &str) -> &'static str {
    if equipment_type_char == "R" {
        "wt"
    } else {
        "amount"
    }
}

// ---------------------------------------------------------------------------
// Market location gate
// ---------------------------------------------------------------------------

/// Cities that have a player market.
pub const MARKET_CITIES: [&str; 2] = ["Altara", "Ardulith"];

/// Whether the given location has a player market.
pub fn has_market(location: &str) -> bool {
    MARKET_CITIES.contains(&location)
}

#[cfg(test)]
mod tests {
    use super::*;

    #[test]
    fn mineral_mithril_is_index_zero() {
        assert!(is_mithril(0));
        assert!(!is_mithril(1));
    }

    #[test]
    fn herb_column_count() {
        assert_eq!(HERB_COLUMNS.len(), 8);
    }

    #[test]
    fn mineral_column_count() {
        assert_eq!(MINERAL_COLUMNS.len(), 18);
    }

    #[test]
    fn astral_prefix_roundtrip() {
        for (ch, expected) in [
            ('M', AstralPrefix::Map),
            ('P', AstralPrefix::Plan),
            ('R', AstralPrefix::Recipe),
            ('C', AstralPrefix::Component),
            ('O', AstralPrefix::Construct),
            ('T', AstralPrefix::Elixir),
        ] {
            assert_eq!(AstralPrefix::from_char(ch), Some(expected));
        }
    }

    #[test]
    fn astral_prefix_invalid() {
        assert_eq!(AstralPrefix::from_char('X'), None);
    }

    #[test]
    fn astral_name_offsets() {
        assert_eq!(AstralPrefix::Map.name_offset(), 0);
        assert_eq!(AstralPrefix::Plan.name_offset(), 7);
        assert_eq!(AstralPrefix::Recipe.name_offset(), 12);
        assert_eq!(AstralPrefix::Component.name_offset(), 17);
        assert_eq!(AstralPrefix::Construct.name_offset(), 24);
        assert_eq!(AstralPrefix::Elixir.name_offset(), 29);
    }

    #[test]
    fn astral_has_number() {
        assert!(AstralPrefix::Map.has_number());
        assert!(AstralPrefix::Plan.has_number());
        assert!(AstralPrefix::Recipe.has_number());
        assert!(!AstralPrefix::Component.has_number());
        assert!(!AstralPrefix::Construct.has_number());
        assert!(!AstralPrefix::Elixir.has_number());
    }

    #[test]
    fn equipment_qty_field_arrows() {
        assert_eq!(equipment_quantity_field("R"), "wt");
    }

    #[test]
    fn equipment_qty_field_normal() {
        assert_eq!(equipment_quantity_field("W"), "amount");
        assert_eq!(equipment_quantity_field("A"), "amount");
    }

    #[test]
    fn has_market_cities() {
        assert!(has_market("Altara"));
        assert!(has_market("Ardulith"));
        assert!(!has_market("Las"));
    }

    #[test]
    fn quantity_semantic_minerals() {
        assert_eq!(
            MarketCategory::Minerals.quantity_semantic(),
            QuantitySemantic::ColumnBased
        );
    }

    #[test]
    fn quantity_semantic_equipment() {
        assert_eq!(
            MarketCategory::Equipment.quantity_semantic(),
            QuantitySemantic::EquipmentDual
        );
    }

    #[test]
    fn quantity_semantic_pets() {
        assert_eq!(
            MarketCategory::Pets.quantity_semantic(),
            QuantitySemantic::OnePerRow
        );
    }

    #[test]
    fn all_categories_get_bank() {
        for cat in MarketCategory::ALL {
            assert!(cat.seller_receives_bank());
        }
    }
}
