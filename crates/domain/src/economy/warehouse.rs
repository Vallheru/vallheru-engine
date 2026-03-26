//! Royal Warehouse — bulk commodity buy/sell against kingdom reserves.
//!
//! The warehouse lets players sell minerals, herbs, and mithril to the
//! kingdom at a base price, or buy them back at double the base price.
//!
//! # Location
//!
//! Only available in cities that have a market (Altara, Ardulith).
//!
//! # Commodity mapping
//!
//! The warehouse trades 26 commodity types that map onto three storage
//! backends:
//!
//! | Index | Column            | Storage        |
//! |-------|-------------------|----------------|
//! | 0–16  | mineral columns   | `minerals`     |
//! | 17    | mithril           | `players.platinum` |
//! | 18–25 | herb columns      | `herbs`        |
//!
//! Prices are stored in the `settings` table keyed by commodity slug.
//! The warehouse's own stock is tracked in the `warehouse` table (rows
//! with `reset = 1`).

use super::market_rules;

// ---------------------------------------------------------------------------
// Commodity catalogue
// ---------------------------------------------------------------------------

/// All 26 warehouse commodity slugs in PHP index order.
///
/// These match the `settings.setting` keys for base prices and the
/// `warehouse.mineral` keys for stock tracking.
pub const COMMODITY_SLUGS: [&str; 26] = [
    "copperore",      // 0
    "zincore",        // 1
    "tinore",         // 2
    "ironore",        // 3
    "copper",         // 4
    "bronze",         // 5
    "brass",          // 6
    "iron",           // 7
    "steel",          // 8
    "coal",           // 9
    "adamantium",     // 10
    "meteor",         // 11
    "crystal",        // 12
    "pine",           // 13
    "hazel",          // 14
    "yew",            // 15
    "elm",            // 16
    "mithril",        // 17
    "illani",         // 18
    "illanias",       // 19
    "nutari",         // 20
    "dynallca",       // 21
    "illani_seeds",   // 22
    "illanias_seeds", // 23
    "nutari_seeds",   // 24
    "dynallca_seeds", // 25
];

/// Which storage backend a commodity uses.
#[derive(Debug, Clone, Copy, PartialEq, Eq)]
pub enum CommodityStorage {
    /// `minerals` table column.
    Mineral,
    /// `players.platinum`.
    Mithril,
    /// `herbs` table column.
    Herb,
}

/// A resolved warehouse commodity with its storage location.
#[derive(Debug, Clone, Copy, PartialEq, Eq)]
pub struct Commodity {
    /// Index in [`COMMODITY_SLUGS`] (0–25).
    pub index: usize,
    /// The slug used in `settings` / `warehouse` tables.
    pub slug: &'static str,
    /// Where the player's stock lives.
    pub storage: CommodityStorage,
    /// Column name in the player's storage table.
    ///
    /// For minerals: the `minerals` table column.
    /// For mithril: `"platinum"` (on `players`).
    /// For herbs: the `herbs` table column (note: `illani_seeds` → `ilani_seeds`).
    pub storage_column: &'static str,
}

/// Resolve a commodity index (0–25) to its full description.
///
/// Returns `None` for out-of-range indices.
pub fn resolve_commodity(index: usize) -> Option<Commodity> {
    if index >= COMMODITY_SLUGS.len() {
        return None;
    }

    let slug = COMMODITY_SLUGS[index];

    let (storage, storage_column) = match index {
        0..=16 => {
            // Minerals. The `market_rules::MINERAL_COLUMNS` array has
            // mithril at index 0 and minerals at 1..=17, so we offset by +1.
            let col_index = index + 1;
            (
                CommodityStorage::Mineral,
                market_rules::MINERAL_COLUMNS[col_index],
            )
        }
        17 => (CommodityStorage::Mithril, "platinum"),
        18..=25 => {
            let herb_index = index - 18;
            (
                CommodityStorage::Herb,
                market_rules::HERB_COLUMNS[herb_index],
            )
        }
        _ => unreachable!(),
    };

    Some(Commodity {
        index,
        slug,
        storage,
        storage_column,
    })
}

// ---------------------------------------------------------------------------
// Pricing
// ---------------------------------------------------------------------------

/// Buy-price multiplier: the warehouse sells at 2× the base (sell) price.
pub const BUY_PRICE_MULTIPLIER: i64 = 2;

/// Compute the total cost for selling `amount` units at `base_price`.
///
/// The player receives this many credits in exchange.
pub fn sell_total(base_price: i64, amount: i64) -> i64 {
    base_price.saturating_mul(amount)
}

/// Compute the total cost for buying `amount` units at `base_price`.
///
/// The player pays this many credits.
pub fn buy_total(base_price: i64, amount: i64) -> i64 {
    base_price
        .saturating_mul(BUY_PRICE_MULTIPLIER)
        .saturating_mul(amount)
}

// ---------------------------------------------------------------------------
// Shared transaction context
// ---------------------------------------------------------------------------

/// Pre-fetched state needed to validate a warehouse transaction.
///
/// Handlers build this from DB queries, then pass it to [`validate_sell`] or
/// [`validate_buy`].
#[derive(Debug, Clone, Copy)]
pub struct TransactionCtx {
    /// 0–25 index into [`COMMODITY_SLUGS`].
    pub commodity_index: usize,
    /// Number of units the player wants to trade.
    pub amount: i64,
    /// Player's current quantity of this commodity.
    pub player_stock: i64,
    /// Player's current credits (gold on hand).
    pub player_credits: i64,
    /// Unit sell price from `settings` table.
    pub base_price: i64,
    /// Kingdom's current gold reserve (`settings.gold`).
    pub kingdom_gold: i64,
    /// Warehouse's current stock of this commodity.
    pub warehouse_stock: i64,
}

// ---------------------------------------------------------------------------
// Validation — sell to warehouse
// ---------------------------------------------------------------------------

/// Errors that can occur when selling commodities to the warehouse.
#[derive(Debug, Clone, Copy, PartialEq, Eq)]
pub enum SellError {
    /// Player is not in a warehouse city.
    WrongLocation,
    /// Invalid commodity index.
    InvalidCommodity,
    /// Requested amount is zero or negative.
    InvalidAmount,
    /// Player does not have enough of the commodity.
    InsufficientStock,
    /// The kingdom does not have enough gold to pay.
    KingdomBroke,
}

impl std::fmt::Display for SellError {
    fn fmt(&self, f: &mut std::fmt::Formatter<'_>) -> std::fmt::Result {
        match self {
            Self::WrongLocation => write!(f, "Not in a warehouse city"),
            Self::InvalidCommodity => write!(f, "Invalid commodity"),
            Self::InvalidAmount => write!(f, "Amount must be positive"),
            Self::InsufficientStock => write!(f, "Not enough of this commodity"),
            Self::KingdomBroke => write!(f, "Kingdom cannot afford this purchase"),
        }
    }
}

impl std::error::Error for SellError {}

/// Outcome of a validated sell operation.
#[derive(Debug, Clone, Copy, PartialEq, Eq)]
pub struct SellResult {
    /// The resolved commodity.
    pub commodity: Commodity,
    /// Number of units being sold.
    pub amount: i64,
    /// Total credits the player receives.
    pub total_price: i64,
    /// Player's new credits balance.
    pub new_player_credits: i64,
    /// Player's new commodity stock (after deducting the sold amount).
    pub new_player_stock: i64,
    /// Kingdom's new gold balance (after paying for the commodities).
    pub new_kingdom_gold: i64,
    /// Warehouse's new stock of this commodity.
    pub new_warehouse_stock: i64,
}

/// Validate a "sell to warehouse" operation.
pub fn validate_sell(location: &str, ctx: &TransactionCtx) -> Result<SellResult, SellError> {
    if !market_rules::has_market(location) {
        return Err(SellError::WrongLocation);
    }

    let commodity = resolve_commodity(ctx.commodity_index).ok_or(SellError::InvalidCommodity)?;

    if ctx.amount <= 0 {
        return Err(SellError::InvalidAmount);
    }

    if ctx.amount > ctx.player_stock {
        return Err(SellError::InsufficientStock);
    }

    let total_price = sell_total(ctx.base_price, ctx.amount);

    if total_price > ctx.kingdom_gold {
        return Err(SellError::KingdomBroke);
    }

    Ok(SellResult {
        commodity,
        amount: ctx.amount,
        total_price,
        new_player_credits: ctx.player_credits + total_price,
        new_player_stock: ctx.player_stock - ctx.amount,
        new_kingdom_gold: ctx.kingdom_gold - total_price,
        new_warehouse_stock: ctx.warehouse_stock + ctx.amount,
    })
}

// ---------------------------------------------------------------------------
// Validation — buy from warehouse
// ---------------------------------------------------------------------------

/// Errors that can occur when buying commodities from the warehouse.
#[derive(Debug, Clone, Copy, PartialEq, Eq)]
pub enum BuyError {
    /// Player is not in a warehouse city.
    WrongLocation,
    /// Invalid commodity index.
    InvalidCommodity,
    /// Requested amount is zero or negative.
    InvalidAmount,
    /// Warehouse does not have enough stock.
    InsufficientWarehouseStock,
    /// Player does not have enough credits.
    InsufficientCredits,
}

impl std::fmt::Display for BuyError {
    fn fmt(&self, f: &mut std::fmt::Formatter<'_>) -> std::fmt::Result {
        match self {
            Self::WrongLocation => write!(f, "Not in a warehouse city"),
            Self::InvalidCommodity => write!(f, "Invalid commodity"),
            Self::InvalidAmount => write!(f, "Amount must be positive"),
            Self::InsufficientWarehouseStock => {
                write!(f, "Warehouse does not have enough stock")
            }
            Self::InsufficientCredits => write!(f, "Not enough gold"),
        }
    }
}

impl std::error::Error for BuyError {}

/// Outcome of a validated buy operation.
#[derive(Debug, Clone, Copy, PartialEq, Eq)]
pub struct BuyResult {
    /// The resolved commodity.
    pub commodity: Commodity,
    /// Number of units being bought.
    pub amount: i64,
    /// Total credits the player pays.
    pub total_price: i64,
    /// Player's new credits balance.
    pub new_player_credits: i64,
    /// Player's new commodity stock (after adding the bought amount).
    pub new_player_stock: i64,
    /// Kingdom's new gold balance (after receiving payment).
    pub new_kingdom_gold: i64,
    /// Warehouse's new stock of this commodity.
    pub new_warehouse_stock: i64,
}

/// Validate a "buy from warehouse" operation.
pub fn validate_buy(location: &str, ctx: &TransactionCtx) -> Result<BuyResult, BuyError> {
    if !market_rules::has_market(location) {
        return Err(BuyError::WrongLocation);
    }

    let commodity = resolve_commodity(ctx.commodity_index).ok_or(BuyError::InvalidCommodity)?;

    if ctx.amount <= 0 {
        return Err(BuyError::InvalidAmount);
    }

    if ctx.amount > ctx.warehouse_stock {
        return Err(BuyError::InsufficientWarehouseStock);
    }

    let total_price = buy_total(ctx.base_price, ctx.amount);

    if total_price > ctx.player_credits {
        return Err(BuyError::InsufficientCredits);
    }

    Ok(BuyResult {
        commodity,
        amount: ctx.amount,
        total_price,
        new_player_credits: ctx.player_credits - total_price,
        new_player_stock: ctx.player_stock + ctx.amount,
        new_kingdom_gold: ctx.kingdom_gold + total_price,
        new_warehouse_stock: ctx.warehouse_stock - ctx.amount,
    })
}

// ---------------------------------------------------------------------------
// Tests
// ---------------------------------------------------------------------------

#[cfg(test)]
mod tests {
    use super::*;

    // -- Commodity resolution -------------------------------------------------

    #[test]
    fn resolve_first_mineral() {
        let c = resolve_commodity(0).unwrap();
        assert_eq!(c.slug, "copperore");
        assert_eq!(c.storage, CommodityStorage::Mineral);
        assert_eq!(c.storage_column, "copperore");
    }

    #[test]
    fn resolve_last_mineral() {
        let c = resolve_commodity(16).unwrap();
        assert_eq!(c.slug, "elm");
        assert_eq!(c.storage, CommodityStorage::Mineral);
        assert_eq!(c.storage_column, "elm");
    }

    #[test]
    fn resolve_mithril() {
        let c = resolve_commodity(17).unwrap();
        assert_eq!(c.slug, "mithril");
        assert_eq!(c.storage, CommodityStorage::Mithril);
        assert_eq!(c.storage_column, "platinum");
    }

    #[test]
    fn resolve_first_herb() {
        let c = resolve_commodity(18).unwrap();
        assert_eq!(c.slug, "illani");
        assert_eq!(c.storage, CommodityStorage::Herb);
        assert_eq!(c.storage_column, "illani");
    }

    #[test]
    fn resolve_illani_seeds_maps_to_ilani_seeds() {
        // PHP typo: warehouse uses "illani_seeds" but herbs table has "ilani_seeds"
        let c = resolve_commodity(22).unwrap();
        assert_eq!(c.slug, "illani_seeds");
        assert_eq!(c.storage_column, "ilani_seeds");
    }

    #[test]
    fn resolve_last_herb() {
        let c = resolve_commodity(25).unwrap();
        assert_eq!(c.slug, "dynallca_seeds");
        assert_eq!(c.storage, CommodityStorage::Herb);
        assert_eq!(c.storage_column, "dynallca_seeds");
    }

    #[test]
    fn resolve_out_of_range() {
        assert!(resolve_commodity(26).is_none());
        assert!(resolve_commodity(100).is_none());
    }

    #[test]
    fn all_26_commodities_resolve() {
        for i in 0..26 {
            assert!(resolve_commodity(i).is_some(), "commodity {i} must resolve");
        }
    }

    // -- Pricing --------------------------------------------------------------

    #[test]
    fn sell_total_basic() {
        assert_eq!(sell_total(100, 5), 500);
    }

    #[test]
    fn buy_total_double_price() {
        assert_eq!(buy_total(100, 5), 1000);
    }

    #[test]
    fn buy_total_single_unit() {
        assert_eq!(buy_total(37, 1), 74);
    }

    // -- Sell validation ------------------------------------------------------

    fn sell_ctx(
        commodity_index: usize,
        amount: i64,
        player_stock: i64,
        player_credits: i64,
        base_price: i64,
        kingdom_gold: i64,
        warehouse_stock: i64,
    ) -> TransactionCtx {
        TransactionCtx {
            commodity_index,
            amount,
            player_stock,
            player_credits,
            base_price,
            kingdom_gold,
            warehouse_stock,
        }
    }

    #[test]
    fn sell_ok() {
        let r = validate_sell("Altara", &sell_ctx(0, 10, 50, 1000, 20, 5000, 100)).unwrap();
        assert_eq!(r.amount, 10);
        assert_eq!(r.total_price, 200);
        assert_eq!(r.new_player_credits, 1200);
        assert_eq!(r.new_player_stock, 40);
        assert_eq!(r.new_kingdom_gold, 4800);
        assert_eq!(r.new_warehouse_stock, 110);
    }

    #[test]
    fn sell_ardulith_ok() {
        assert!(validate_sell("Ardulith", &sell_ctx(5, 1, 10, 0, 50, 1000, 0)).is_ok());
    }

    #[test]
    fn sell_wrong_location() {
        assert_eq!(
            validate_sell("Las", &sell_ctx(0, 1, 10, 0, 10, 1000, 0)).unwrap_err(),
            SellError::WrongLocation,
        );
    }

    #[test]
    fn sell_invalid_commodity() {
        assert_eq!(
            validate_sell("Altara", &sell_ctx(26, 1, 10, 0, 10, 1000, 0)).unwrap_err(),
            SellError::InvalidCommodity,
        );
    }

    #[test]
    fn sell_zero_amount() {
        assert_eq!(
            validate_sell("Altara", &sell_ctx(0, 0, 10, 0, 10, 1000, 0)).unwrap_err(),
            SellError::InvalidAmount,
        );
    }

    #[test]
    fn sell_negative_amount() {
        assert_eq!(
            validate_sell("Altara", &sell_ctx(0, -1, 10, 0, 10, 1000, 0)).unwrap_err(),
            SellError::InvalidAmount,
        );
    }

    #[test]
    fn sell_insufficient_stock() {
        assert_eq!(
            validate_sell("Altara", &sell_ctx(0, 11, 10, 0, 10, 1000, 0)).unwrap_err(),
            SellError::InsufficientStock,
        );
    }

    #[test]
    fn sell_kingdom_broke() {
        // 10 units at price 100 = 1000 cost, but kingdom only has 500
        assert_eq!(
            validate_sell("Altara", &sell_ctx(0, 10, 50, 0, 100, 500, 0)).unwrap_err(),
            SellError::KingdomBroke,
        );
    }

    #[test]
    fn sell_exact_kingdom_gold() {
        // Exactly enough kingdom gold
        let r = validate_sell("Altara", &sell_ctx(0, 10, 50, 0, 100, 1000, 0)).unwrap();
        assert_eq!(r.new_kingdom_gold, 0);
    }

    #[test]
    fn sell_mithril() {
        let r = validate_sell("Altara", &sell_ctx(17, 5, 20, 100, 30, 10000, 0)).unwrap();
        assert_eq!(r.commodity.storage, CommodityStorage::Mithril);
        assert_eq!(r.total_price, 150);
        assert_eq!(r.new_player_stock, 15);
    }

    #[test]
    fn sell_herb() {
        let r = validate_sell("Altara", &sell_ctx(20, 3, 10, 100, 25, 10000, 50)).unwrap();
        assert_eq!(r.commodity.storage, CommodityStorage::Herb);
        assert_eq!(r.commodity.storage_column, "nutari");
        assert_eq!(r.total_price, 75);
        assert_eq!(r.new_warehouse_stock, 53);
    }

    // -- Buy validation -------------------------------------------------------

    #[test]
    fn buy_ok() {
        let r = validate_buy("Altara", &sell_ctx(0, 10, 5, 5000, 20, 1000, 100)).unwrap();
        assert_eq!(r.amount, 10);
        assert_eq!(r.total_price, 400); // 20 * 2 * 10
        assert_eq!(r.new_player_credits, 4600);
        assert_eq!(r.new_player_stock, 15);
        assert_eq!(r.new_kingdom_gold, 1400);
        assert_eq!(r.new_warehouse_stock, 90);
    }

    #[test]
    fn buy_wrong_location() {
        assert_eq!(
            validate_buy("Las", &sell_ctx(0, 1, 0, 1000, 10, 0, 100)).unwrap_err(),
            BuyError::WrongLocation,
        );
    }

    #[test]
    fn buy_invalid_commodity() {
        assert_eq!(
            validate_buy("Altara", &sell_ctx(30, 1, 0, 1000, 10, 0, 100)).unwrap_err(),
            BuyError::InvalidCommodity,
        );
    }

    #[test]
    fn buy_zero_amount() {
        assert_eq!(
            validate_buy("Altara", &sell_ctx(0, 0, 0, 1000, 10, 0, 100)).unwrap_err(),
            BuyError::InvalidAmount,
        );
    }

    #[test]
    fn buy_insufficient_warehouse_stock() {
        assert_eq!(
            validate_buy("Altara", &sell_ctx(0, 11, 0, 10000, 10, 0, 10)).unwrap_err(),
            BuyError::InsufficientWarehouseStock,
        );
    }

    #[test]
    fn buy_insufficient_credits() {
        // 10 units at base_price 100 → buy price = 200/unit → 2000 total, but only 1000 credits
        assert_eq!(
            validate_buy("Altara", &sell_ctx(0, 10, 0, 1000, 100, 0, 100)).unwrap_err(),
            BuyError::InsufficientCredits,
        );
    }

    #[test]
    fn buy_exact_credits() {
        let r = validate_buy("Altara", &sell_ctx(0, 5, 0, 200, 20, 0, 100)).unwrap();
        assert_eq!(r.new_player_credits, 0);
    }

    #[test]
    fn buy_exact_stock() {
        let r = validate_buy("Altara", &sell_ctx(0, 100, 0, 100_000, 20, 0, 100)).unwrap();
        assert_eq!(r.new_warehouse_stock, 0);
    }

    #[test]
    fn buy_mithril() {
        let r = validate_buy("Altara", &sell_ctx(17, 3, 10, 10000, 50, 0, 20)).unwrap();
        assert_eq!(r.commodity.storage, CommodityStorage::Mithril);
        assert_eq!(r.total_price, 300); // 50 * 2 * 3
        assert_eq!(r.new_player_stock, 13);
    }

    #[test]
    fn buy_herb() {
        let r = validate_buy("Ardulith", &sell_ctx(24, 2, 0, 5000, 40, 100, 50)).unwrap();
        assert_eq!(r.commodity.storage, CommodityStorage::Herb);
        assert_eq!(r.commodity.storage_column, "nutari_seeds");
        assert_eq!(r.total_price, 160); // 40 * 2 * 2
        assert_eq!(r.new_warehouse_stock, 48);
    }

    // -- Commodity slug coverage ----------------------------------------------

    #[test]
    fn commodity_slugs_match_expected_count() {
        assert_eq!(COMMODITY_SLUGS.len(), 26);
    }

    #[test]
    fn mineral_slugs_align_with_market_rules() {
        // Warehouse indices 0–16 should match MINERAL_COLUMNS[1..=17]
        for i in 0..=16 {
            let c = resolve_commodity(i).unwrap();
            assert_eq!(
                c.storage_column,
                market_rules::MINERAL_COLUMNS[i + 1],
                "mineral index {i} storage column mismatch"
            );
        }
    }

    #[test]
    fn herb_slugs_align_with_market_rules() {
        // Warehouse indices 18–25 should match HERB_COLUMNS[0..=7]
        for i in 18..=25 {
            let c = resolve_commodity(i).unwrap();
            assert_eq!(
                c.storage_column,
                market_rules::HERB_COLUMNS[i - 18],
                "herb index {i} storage column mismatch"
            );
        }
    }
}
