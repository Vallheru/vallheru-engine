//! Shared market workflow types and validation.
//!
//! The game has 8 player-to-player market pages plus a hub page.
//! Despite using different tables and item shapes, they all share the
//! same core workflow:
//!
//! 1. **Browse** — paginated listing with search/sort/filter
//! 2. **List** (add offer) — move goods from inventory to market
//! 3. **Buy** — pay credits, seller receives gold in bank
//! 4. **Cancel** (withdraw) — return goods to seller's inventory
//! 5. **Top-up** (add to existing offer) — increase quantity on an offer
//! 6. **Change price** — update the asking price of an offer
//! 7. **Delete all** — bulk-cancel all of a player's offers
//!
//! Each market category has its own storage table and item shape; those
//! differences are captured in [`MarketCategory`] and the per-category
//! rules in [`market_rules`](super::market_rules).

use crate::economy::currency::CurrencyError;

// ---------------------------------------------------------------------------
// Market category enum
// ---------------------------------------------------------------------------

/// The 8 player-to-player market categories.
///
/// The PHP codebase uses separate pages and tables for each.
/// In Rust we unify the workflow and parameterize on this enum.
#[derive(Debug, Clone, Copy, PartialEq, Eq, Hash)]
pub enum MarketCategory {
    /// Minerals & ores (table `pmarket`, includes mithril/platinum).
    Minerals,
    /// Player-crafted equipment — weapons, armor, shields, etc (table `equipment` with `status='R'`, type NOT IN ('I','O','Q')).
    Equipment,
    /// Potions (table `potions` with `status='R'`).
    Potions,
    /// Herbs & seeds (table `hmarket`).
    Herbs,
    /// Astral crafting components — maps, plans, recipes, compounds, constructs, elixirs (table `amarket`).
    Astral,
    /// Rings and jewellery (table `equipment` with `status='R'`, type='I').
    Jewellery,
    /// Loot items (table `equipment` with `status='R'`, type='O').
    Loot,
    /// Companion pets (table `core_market`).
    Pets,
}

impl MarketCategory {
    /// The URL path slug used for this market (matches legacy PHP filename without `.php`).
    pub fn slug(self) -> &'static str {
        match self {
            Self::Minerals => "pmarket",
            Self::Equipment => "imarket",
            Self::Potions => "mmarket",
            Self::Herbs => "hmarket",
            Self::Astral => "amarket",
            Self::Jewellery => "rmarket",
            Self::Loot => "lmarket",
            Self::Pets => "cmarket",
        }
    }

    /// Parse from the URL slug.
    pub fn from_slug(s: &str) -> Option<Self> {
        match s {
            "pmarket" => Some(Self::Minerals),
            "imarket" => Some(Self::Equipment),
            "mmarket" => Some(Self::Potions),
            "hmarket" => Some(Self::Herbs),
            "amarket" => Some(Self::Astral),
            "rmarket" => Some(Self::Jewellery),
            "lmarket" => Some(Self::Loot),
            "cmarket" => Some(Self::Pets),
            _ => None,
        }
    }

    /// All market categories in display order (matches PHP `$arrFiles`).
    pub const ALL: [Self; 8] = [
        Self::Minerals,
        Self::Equipment,
        Self::Potions,
        Self::Herbs,
        Self::Astral,
        Self::Jewellery,
        Self::Loot,
        Self::Pets,
    ];

    /// Whether the category supports partial quantity purchases.
    pub fn supports_partial_buy(self) -> bool {
        !matches!(self, Self::Pets)
    }

    /// Whether the category supports the "add to existing offer" (top-up) workflow.
    pub fn supports_topup(self) -> bool {
        !matches!(self, Self::Pets)
    }

    /// Maximum simultaneous offers a player can have on this market.
    /// `None` means unlimited.
    pub fn max_offers(self) -> Option<u32> {
        match self {
            Self::Pets => Some(5),
            _ => None,
        }
    }

    /// Whether listing an existing duplicate should merge into the current offer.
    ///
    /// Minerals and herbs merge by commodity name; items, potions, and astrals
    /// merge by exact attribute match; pets never merge.
    pub fn auto_merges_offers(self) -> bool {
        !matches!(self, Self::Pets)
    }
}

// ---------------------------------------------------------------------------
// Listing validation
// ---------------------------------------------------------------------------

/// Errors that can occur when creating or modifying a market listing.
#[derive(Debug, Clone, PartialEq, Eq)]
pub enum ListingError {
    /// Player is not in a city with a market.
    WrongLocation,
    /// Player has no stock of the requested item.
    NoStock,
    /// Requested quantity exceeds available inventory.
    InsufficientQuantity,
    /// Price must be positive.
    InvalidPrice,
    /// Quantity must be positive.
    InvalidQuantity,
    /// Maximum number of offers reached for this category.
    TooManyOffers { max: u32 },
    /// The item being listed cannot be traded on this market.
    InvalidItem,
    /// The pet is dead and cannot be sold.
    PetDead,
}

impl std::fmt::Display for ListingError {
    fn fmt(&self, f: &mut std::fmt::Formatter<'_>) -> std::fmt::Result {
        match self {
            Self::WrongLocation => write!(f, "You must be in a city to use the market"),
            Self::NoStock => write!(f, "You don't have that item"),
            Self::InsufficientQuantity => write!(f, "Not enough quantity"),
            Self::InvalidPrice => write!(f, "Price must be positive"),
            Self::InvalidQuantity => write!(f, "Quantity must be positive"),
            Self::TooManyOffers { max } => write!(f, "Maximum {max} offers reached"),
            Self::InvalidItem => write!(f, "This item cannot be listed on this market"),
            Self::PetDead => write!(f, "Cannot sell a dead pet"),
        }
    }
}

impl std::error::Error for ListingError {}

/// Validate that a player can list an item on the market.
///
/// Returns `Ok(())` if the listing preconditions are met.
pub fn validate_listing(
    location: &str,
    price: i32,
    quantity: i32,
    available: i32,
    current_offers: Option<u32>,
    category: MarketCategory,
) -> Result<(), ListingError> {
    if location != "Altara" && location != "Ardulith" {
        return Err(ListingError::WrongLocation);
    }
    if price <= 0 {
        return Err(ListingError::InvalidPrice);
    }
    if quantity <= 0 {
        return Err(ListingError::InvalidQuantity);
    }
    if available <= 0 {
        return Err(ListingError::NoStock);
    }
    if quantity > available {
        return Err(ListingError::InsufficientQuantity);
    }
    if let Some(max) = category.max_offers() {
        if let Some(current) = current_offers {
            if current >= max {
                return Err(ListingError::TooManyOffers { max });
            }
        }
    }
    Ok(())
}

// ---------------------------------------------------------------------------
// Purchase validation
// ---------------------------------------------------------------------------

/// Errors that can occur when buying from the market.
#[derive(Debug, Clone, PartialEq, Eq)]
pub enum PurchaseError {
    /// Player is not in a city with a market.
    WrongLocation,
    /// The listing no longer exists.
    ListingNotFound,
    /// Cannot buy your own listing.
    OwnListing,
    /// Not enough gold (credits) to pay.
    InsufficientFunds,
    /// Requested quantity exceeds what's available.
    InsufficientQuantity,
    /// Quantity must be positive.
    InvalidQuantity,
    /// Currency operation failed.
    Currency(CurrencyError),
}

impl std::fmt::Display for PurchaseError {
    fn fmt(&self, f: &mut std::fmt::Formatter<'_>) -> std::fmt::Result {
        match self {
            Self::WrongLocation => write!(f, "You must be in a city to use the market"),
            Self::ListingNotFound => write!(f, "This listing no longer exists"),
            Self::OwnListing => write!(f, "Cannot buy your own listing"),
            Self::InsufficientFunds => write!(f, "Not enough gold"),
            Self::InsufficientQuantity => write!(f, "Not enough quantity on listing"),
            Self::InvalidQuantity => write!(f, "Quantity must be positive"),
            Self::Currency(e) => write!(f, "{e}"),
        }
    }
}

impl std::error::Error for PurchaseError {}

impl From<CurrencyError> for PurchaseError {
    fn from(e: CurrencyError) -> Self {
        Self::Currency(e)
    }
}

/// Input data for a market purchase validation.
#[derive(Debug, Clone)]
pub struct PurchaseCheck<'a> {
    pub buyer_location: &'a str,
    pub buyer_id: i32,
    pub buyer_credits: i64,
    pub seller_id: i32,
    pub seller_bank: i64,
    pub unit_cost: i64,
    pub listing_quantity: i32,
    pub buy_quantity: i32,
}

/// Result of a validated purchase.
#[derive(Debug, Clone, Copy, PartialEq, Eq)]
pub struct PurchaseResult {
    /// Total price paid (quantity × unit cost).
    pub total_price: i64,
    /// Buyer's new credits balance.
    pub buyer_new_credits: i64,
    /// Seller's new bank balance.
    pub seller_new_bank: i64,
    /// Remaining quantity on the listing (0 means listing should be deleted).
    pub listing_remaining: i32,
}

/// Validate and compute a market purchase.
///
/// PHP pattern: `buyer.credits -= total`, `seller.bank += total`.
/// If the buyer purchases all remaining stock, the listing is removed.
pub fn validate_purchase(check: &PurchaseCheck<'_>) -> Result<PurchaseResult, PurchaseError> {
    if check.buyer_location != "Altara" && check.buyer_location != "Ardulith" {
        return Err(PurchaseError::WrongLocation);
    }
    if check.buyer_id == check.seller_id {
        return Err(PurchaseError::OwnListing);
    }
    if check.buy_quantity <= 0 {
        return Err(PurchaseError::InvalidQuantity);
    }
    if check.buy_quantity > check.listing_quantity {
        return Err(PurchaseError::InsufficientQuantity);
    }
    let total_price = i64::from(check.buy_quantity) * check.unit_cost;
    if total_price > check.buyer_credits {
        return Err(PurchaseError::InsufficientFunds);
    }
    Ok(PurchaseResult {
        total_price,
        buyer_new_credits: check.buyer_credits - total_price,
        seller_new_bank: check.seller_bank + total_price,
        listing_remaining: check.listing_quantity - check.buy_quantity,
    })
}

// ---------------------------------------------------------------------------
// Cancel (withdraw) validation
// ---------------------------------------------------------------------------

/// Errors for cancelling (withdrawing) a listing.
#[derive(Debug, Clone, PartialEq, Eq)]
pub enum CancelError {
    /// The listing doesn't belong to this player.
    NotOwner,
    /// The listing was not found.
    NotFound,
    /// Partial cancel: quantity exceeds listed amount.
    InsufficientQuantity,
    /// Quantity must be positive or zero (zero = cancel all).
    InvalidQuantity,
}

impl std::fmt::Display for CancelError {
    fn fmt(&self, f: &mut std::fmt::Formatter<'_>) -> std::fmt::Result {
        match self {
            Self::NotOwner => write!(f, "This is not your listing"),
            Self::NotFound => write!(f, "Listing not found"),
            Self::InsufficientQuantity => write!(f, "Not enough quantity on listing"),
            Self::InvalidQuantity => write!(f, "Invalid quantity"),
        }
    }
}

impl std::error::Error for CancelError {}

/// Result of a validated cancel operation.
#[derive(Debug, Clone, Copy, PartialEq, Eq)]
pub struct CancelResult {
    /// Amount being returned to inventory.
    pub return_quantity: i32,
    /// Remaining quantity on the listing (0 = delete listing).
    pub listing_remaining: i32,
}

/// Validate a listing cancellation.
///
/// `cancel_quantity` of 0 means "cancel the entire listing".
/// A positive value means "withdraw that many units".
pub fn validate_cancel(
    owner_id: i32,
    listing_owner_id: i32,
    listing_quantity: i32,
    cancel_quantity: i32,
) -> Result<CancelResult, CancelError> {
    if owner_id != listing_owner_id {
        return Err(CancelError::NotOwner);
    }
    if cancel_quantity < 0 {
        return Err(CancelError::InvalidQuantity);
    }
    let actual = if cancel_quantity == 0 {
        listing_quantity
    } else {
        if cancel_quantity > listing_quantity {
            return Err(CancelError::InsufficientQuantity);
        }
        cancel_quantity
    };
    Ok(CancelResult {
        return_quantity: actual,
        listing_remaining: listing_quantity - actual,
    })
}

// ---------------------------------------------------------------------------
// Top-up validation
// ---------------------------------------------------------------------------

/// Validate adding more quantity to an existing listing.
pub fn validate_topup(available: i32, add_quantity: i32) -> Result<i32, ListingError> {
    if add_quantity <= 0 {
        return Err(ListingError::InvalidQuantity);
    }
    if available <= 0 {
        return Err(ListingError::NoStock);
    }
    if add_quantity > available {
        return Err(ListingError::InsufficientQuantity);
    }
    Ok(add_quantity)
}

// ---------------------------------------------------------------------------
// Price change validation
// ---------------------------------------------------------------------------

/// Validate changing the price on an existing listing.
pub fn validate_price_change(
    owner_id: i32,
    listing_owner_id: i32,
    new_price: i32,
) -> Result<i32, ListingError> {
    if owner_id != listing_owner_id {
        return Err(ListingError::InvalidItem);
    }
    if new_price <= 0 {
        return Err(ListingError::InvalidPrice);
    }
    Ok(new_price)
}

// ---------------------------------------------------------------------------
// Fixed-price shop (msklep.php style)
// ---------------------------------------------------------------------------

/// A fixed-stock shop item (NPC vendor, not player market).
///
/// Used by `msklep.php` (potion shop) and similar NPC stores.
/// The price is computed from item properties, not set by players.
#[derive(Debug, Clone, PartialEq, Eq)]
pub struct ShopItem {
    pub id: i32,
    pub name: String,
    pub available: i32,
    pub unit_price: i64,
}

/// Errors for NPC shop purchases.
#[derive(Debug, Clone, PartialEq, Eq)]
pub enum ShopError {
    /// Player is not in the right city.
    WrongLocation,
    /// Item not found or not for sale.
    ItemNotFound,
    /// Not enough stock.
    InsufficientStock,
    /// Not enough credits.
    InsufficientFunds,
    /// Quantity must be positive.
    InvalidQuantity,
}

impl std::fmt::Display for ShopError {
    fn fmt(&self, f: &mut std::fmt::Formatter<'_>) -> std::fmt::Result {
        match self {
            Self::WrongLocation => write!(f, "You must be in a city to use the shop"),
            Self::ItemNotFound => write!(f, "Item not found"),
            Self::InsufficientStock => write!(f, "Not enough stock"),
            Self::InsufficientFunds => write!(f, "Not enough gold"),
            Self::InvalidQuantity => write!(f, "Quantity must be positive"),
        }
    }
}

impl std::error::Error for ShopError {}

/// Result of a shop purchase.
#[derive(Debug, Clone, Copy, PartialEq, Eq)]
pub struct ShopPurchaseResult {
    pub total_cost: i64,
    pub buyer_new_credits: i64,
    pub remaining_stock: i32,
}

/// Validate and compute an NPC shop purchase.
pub fn validate_shop_purchase(
    buyer_location: &str,
    buyer_credits: i64,
    stock: i32,
    unit_price: i64,
    buy_quantity: i32,
) -> Result<ShopPurchaseResult, ShopError> {
    if buyer_location != "Altara" && buyer_location != "Ardulith" {
        return Err(ShopError::WrongLocation);
    }
    if buy_quantity <= 0 {
        return Err(ShopError::InvalidQuantity);
    }
    if buy_quantity > stock {
        return Err(ShopError::InsufficientStock);
    }
    let total_cost = i64::from(buy_quantity) * unit_price;
    if total_cost > buyer_credits {
        return Err(ShopError::InsufficientFunds);
    }
    Ok(ShopPurchaseResult {
        total_cost,
        buyer_new_credits: buyer_credits - total_cost,
        remaining_stock: stock - buy_quantity,
    })
}

// ---------------------------------------------------------------------------
// Potion shop pricing formula
// ---------------------------------------------------------------------------

/// Calculate the price of a potion at the city alchemist shop.
///
/// From `msklep.php`:
/// - Mana potions ('M'): power × 3
/// - Health potions: (2 × power) × 3
pub fn potion_shop_price(potion_type: &str, power: i32) -> i64 {
    if potion_type == "M" {
        i64::from(power) * 3
    } else {
        i64::from(power) * 2 * 3
    }
}

// ---------------------------------------------------------------------------
// Browse / sort / pagination
// ---------------------------------------------------------------------------

/// Items per page for market listing pagination.
pub const PAGE_SIZE: i32 = 30;

/// Sort direction.
#[derive(Debug, Clone, Copy, PartialEq, Eq, Default)]
pub enum SortOrder {
    #[default]
    Desc,
    Asc,
}

impl SortOrder {
    /// Parse from query string value.
    pub fn from_str_param(s: &str) -> Self {
        if s.eq_ignore_ascii_case("asc") {
            Self::Asc
        } else {
            Self::Desc
        }
    }

    /// The SQL keyword.
    pub fn sql(self) -> &'static str {
        match self {
            Self::Asc => "ASC",
            Self::Desc => "DESC",
        }
    }

    /// The toggle (for the "click column to reverse" UI pattern).
    #[must_use]
    pub fn toggle(self) -> Self {
        match self {
            Self::Asc => Self::Desc,
            Self::Desc => Self::Asc,
        }
    }
}

/// A validated sort column for a market category.
///
/// Each category allows a different set of columns.
/// The caller resolves the column name via [`MarketCategory::validate_sort_column`].
#[derive(Debug, Clone, PartialEq, Eq)]
pub struct SortColumn(String);

impl SortColumn {
    /// The validated column name.
    pub fn as_str(&self) -> &str {
        &self.0
    }
}

impl MarketCategory {
    /// Allowed sort column names for this category's browse view.
    ///
    /// Derived from `in_array($_GET['lista'], ...)` checks in each PHP page.
    pub fn allowed_sort_columns(self) -> &'static [&'static str] {
        match self {
            Self::Minerals | Self::Herbs => &["id", "nazwa", "ilosc", "cost", "seller"],
            Self::Equipment => &[
                "id", "name", "power", "wt", "szyb", "zr", "minlev", "amount", "cost", "owner",
            ],
            Self::Potions => &["id", "name", "efect", "power", "amount", "cost", "owner"],
            Self::Astral => &["id", "type", "number", "amount", "cost", "seller"],
            Self::Jewellery => &["id", "name", "power", "amount", "cost", "owner"],
            Self::Loot => &["id", "name", "cost", "amount", "minlev", "owner"],
            Self::Pets => &["id", "name", "cost", "gender", "power", "defense", "seller"],
        }
    }

    /// Validate and return a [`SortColumn`] for this category.
    ///
    /// Falls back to `"id"` if the requested column is not allowed.
    pub fn validate_sort_column(self, requested: &str) -> SortColumn {
        if self.allowed_sort_columns().contains(&requested) {
            SortColumn(requested.to_string())
        } else {
            SortColumn("id".to_string())
        }
    }
}

/// Input for a paginated market browse request.
#[derive(Debug, Clone)]
pub struct BrowseRequest {
    pub category: MarketCategory,
    pub page: i32,
    pub sort_column: SortColumn,
    pub sort_order: SortOrder,
    /// Optional name/keyword search filter (already sanitized).
    pub search: Option<String>,
}

/// Computed pagination metadata for a market listing page.
#[derive(Debug, Clone, Copy, PartialEq, Eq)]
pub struct Pagination {
    /// Current (1-based) page.
    pub page: i32,
    /// Total number of pages.
    pub total_pages: i32,
    /// SQL `OFFSET` value.
    pub offset: i32,
    /// SQL `LIMIT` value (always [`PAGE_SIZE`]).
    pub limit: i32,
}

/// Compute pagination from total item count and requested page.
pub fn paginate(total_items: i32, requested_page: i32) -> Pagination {
    let total_pages = if total_items <= 0 {
        1
    } else {
        (total_items + PAGE_SIZE - 1) / PAGE_SIZE
    };
    let page = requested_page.clamp(1, total_pages);
    let offset = (page - 1) * PAGE_SIZE;
    Pagination {
        page,
        total_pages,
        offset,
        limit: PAGE_SIZE,
    }
}

/// Sanitize a user-provided search string for `LIKE` matching.
///
/// PHP converts `*` to `%`, then wraps the string in `%..%`
/// if it doesn't already contain `%`.
pub fn sanitize_search(raw: &str) -> String {
    let trimmed = raw.trim();
    if trimmed.is_empty() {
        return String::new();
    }
    let replaced = trimmed.replace('*', "%");
    if replaced.contains('%') {
        replaced
    } else {
        format!("%{replaced}%")
    }
}

// ---------------------------------------------------------------------------
// Offer summary (for "my offers" view)
// ---------------------------------------------------------------------------

/// Per-category offer count for the "my offers" summary.
#[derive(Debug, Clone, Copy, PartialEq, Eq)]
pub struct OfferCount {
    pub category: MarketCategory,
    pub count: i32,
}

/// Summary of a player's offers across all markets.
#[derive(Debug, Clone)]
pub struct OfferSummary {
    pub counts: Vec<OfferCount>,
}

impl OfferSummary {
    /// Total offers across all categories.
    pub fn total(&self) -> i32 {
        self.counts.iter().map(|c| c.count).sum()
    }
}

#[cfg(test)]
mod tests {
    use super::*;

    // --- MarketCategory ---

    #[test]
    fn category_slug_roundtrip() {
        for cat in MarketCategory::ALL {
            assert_eq!(MarketCategory::from_slug(cat.slug()), Some(cat));
        }
    }

    #[test]
    fn category_from_slug_invalid() {
        assert_eq!(MarketCategory::from_slug("nosuch"), None);
    }

    #[test]
    fn pets_max_5_offers() {
        assert_eq!(MarketCategory::Pets.max_offers(), Some(5));
    }

    #[test]
    fn pets_no_partial_buy() {
        assert!(!MarketCategory::Pets.supports_partial_buy());
    }

    #[test]
    fn equipment_unlimited_offers() {
        assert_eq!(MarketCategory::Equipment.max_offers(), None);
    }

    // --- validate_listing ---

    #[test]
    fn listing_ok() {
        assert!(validate_listing("Altara", 100, 5, 10, None, MarketCategory::Minerals).is_ok());
    }

    #[test]
    fn listing_wrong_location() {
        assert_eq!(
            validate_listing("Las", 100, 5, 10, None, MarketCategory::Minerals),
            Err(ListingError::WrongLocation)
        );
    }

    #[test]
    fn listing_zero_price() {
        assert_eq!(
            validate_listing("Altara", 0, 5, 10, None, MarketCategory::Minerals),
            Err(ListingError::InvalidPrice)
        );
    }

    #[test]
    fn listing_zero_quantity() {
        assert_eq!(
            validate_listing("Altara", 100, 0, 10, None, MarketCategory::Minerals),
            Err(ListingError::InvalidQuantity)
        );
    }

    #[test]
    fn listing_no_stock() {
        assert_eq!(
            validate_listing("Altara", 100, 5, 0, None, MarketCategory::Minerals),
            Err(ListingError::NoStock)
        );
    }

    #[test]
    fn listing_insufficient_quantity() {
        assert_eq!(
            validate_listing("Altara", 100, 15, 10, None, MarketCategory::Minerals),
            Err(ListingError::InsufficientQuantity)
        );
    }

    #[test]
    fn listing_too_many_pet_offers() {
        assert_eq!(
            validate_listing("Altara", 100, 1, 1, Some(5), MarketCategory::Pets),
            Err(ListingError::TooManyOffers { max: 5 })
        );
    }

    #[test]
    fn listing_pet_under_limit() {
        assert!(validate_listing("Altara", 100, 1, 1, Some(4), MarketCategory::Pets).is_ok());
    }

    #[test]
    fn listing_ardulith() {
        assert!(validate_listing("Ardulith", 50, 3, 10, None, MarketCategory::Herbs).is_ok());
    }

    // --- validate_purchase ---

    #[allow(clippy::too_many_arguments)]
    fn pc(
        loc: &str,
        buyer: i32,
        credits: i64,
        seller: i32,
        bank: i64,
        cost: i64,
        qty: i32,
        buy: i32,
    ) -> PurchaseCheck<'_> {
        PurchaseCheck {
            buyer_location: loc,
            buyer_id: buyer,
            buyer_credits: credits,
            seller_id: seller,
            seller_bank: bank,
            unit_cost: cost,
            listing_quantity: qty,
            buy_quantity: buy,
        }
    }

    #[test]
    fn purchase_ok() {
        let r = validate_purchase(&pc("Altara", 1, 1000, 2, 500, 100, 10, 3)).unwrap();
        assert_eq!(r.total_price, 300);
        assert_eq!(r.buyer_new_credits, 700);
        assert_eq!(r.seller_new_bank, 800);
        assert_eq!(r.listing_remaining, 7);
    }

    #[test]
    fn purchase_exact_stock() {
        let r = validate_purchase(&pc("Altara", 1, 500, 2, 100, 50, 10, 10)).unwrap();
        assert_eq!(r.listing_remaining, 0);
        assert_eq!(r.total_price, 500);
    }

    #[test]
    fn purchase_own_listing() {
        assert_eq!(
            validate_purchase(&pc("Altara", 1, 1000, 1, 500, 100, 10, 3)),
            Err(PurchaseError::OwnListing)
        );
    }

    #[test]
    fn purchase_insufficient_funds() {
        assert_eq!(
            validate_purchase(&pc("Altara", 1, 100, 2, 500, 100, 10, 3)),
            Err(PurchaseError::InsufficientFunds)
        );
    }

    #[test]
    fn purchase_insufficient_quantity() {
        assert_eq!(
            validate_purchase(&pc("Altara", 1, 10000, 2, 500, 100, 5, 10)),
            Err(PurchaseError::InsufficientQuantity)
        );
    }

    #[test]
    fn purchase_wrong_location() {
        assert_eq!(
            validate_purchase(&pc("Las", 1, 1000, 2, 500, 100, 10, 3)),
            Err(PurchaseError::WrongLocation)
        );
    }

    #[test]
    fn purchase_zero_quantity() {
        assert_eq!(
            validate_purchase(&pc("Altara", 1, 1000, 2, 500, 100, 10, 0)),
            Err(PurchaseError::InvalidQuantity)
        );
    }

    // --- validate_cancel ---

    #[test]
    fn cancel_full() {
        let r = validate_cancel(1, 1, 10, 0).unwrap();
        assert_eq!(r.return_quantity, 10);
        assert_eq!(r.listing_remaining, 0);
    }

    #[test]
    fn cancel_partial() {
        let r = validate_cancel(1, 1, 10, 3).unwrap();
        assert_eq!(r.return_quantity, 3);
        assert_eq!(r.listing_remaining, 7);
    }

    #[test]
    fn cancel_not_owner() {
        assert_eq!(validate_cancel(1, 2, 10, 0), Err(CancelError::NotOwner));
    }

    #[test]
    fn cancel_too_many() {
        assert_eq!(
            validate_cancel(1, 1, 5, 10),
            Err(CancelError::InsufficientQuantity)
        );
    }

    #[test]
    fn cancel_negative() {
        assert_eq!(
            validate_cancel(1, 1, 10, -1),
            Err(CancelError::InvalidQuantity)
        );
    }

    // --- validate_topup ---

    #[test]
    fn topup_ok() {
        assert_eq!(validate_topup(10, 5), Ok(5));
    }

    #[test]
    fn topup_zero_available() {
        assert_eq!(validate_topup(0, 5), Err(ListingError::NoStock));
    }

    #[test]
    fn topup_exceeds_available() {
        assert_eq!(
            validate_topup(3, 5),
            Err(ListingError::InsufficientQuantity)
        );
    }

    #[test]
    fn topup_zero_quantity() {
        assert_eq!(validate_topup(10, 0), Err(ListingError::InvalidQuantity));
    }

    // --- validate_price_change ---

    #[test]
    fn price_change_ok() {
        assert_eq!(validate_price_change(1, 1, 200), Ok(200));
    }

    #[test]
    fn price_change_not_owner() {
        assert_eq!(
            validate_price_change(1, 2, 200),
            Err(ListingError::InvalidItem)
        );
    }

    #[test]
    fn price_change_zero() {
        assert_eq!(
            validate_price_change(1, 1, 0),
            Err(ListingError::InvalidPrice)
        );
    }

    // --- validate_shop_purchase ---

    #[test]
    fn shop_purchase_ok() {
        let r = validate_shop_purchase("Altara", 500, 10, 30, 5).unwrap();
        assert_eq!(r.total_cost, 150);
        assert_eq!(r.buyer_new_credits, 350);
        assert_eq!(r.remaining_stock, 5);
    }

    #[test]
    fn shop_purchase_insufficient_funds() {
        assert_eq!(
            validate_shop_purchase("Altara", 50, 10, 30, 5),
            Err(ShopError::InsufficientFunds)
        );
    }

    #[test]
    fn shop_purchase_insufficient_stock() {
        assert_eq!(
            validate_shop_purchase("Altara", 5000, 3, 30, 5),
            Err(ShopError::InsufficientStock)
        );
    }

    #[test]
    fn shop_purchase_wrong_location() {
        assert_eq!(
            validate_shop_purchase("Las", 500, 10, 30, 5),
            Err(ShopError::WrongLocation)
        );
    }

    // --- potion_shop_price ---

    #[test]
    fn potion_price_mana() {
        assert_eq!(potion_shop_price("M", 10), 30);
    }

    #[test]
    fn potion_price_health() {
        assert_eq!(potion_shop_price("H", 10), 60);
    }

    // --- SortOrder ---

    #[test]
    fn sort_order_default_is_desc() {
        assert_eq!(SortOrder::default(), SortOrder::Desc);
    }

    #[test]
    fn sort_order_parse() {
        assert_eq!(SortOrder::from_str_param("ASC"), SortOrder::Asc);
        assert_eq!(SortOrder::from_str_param("asc"), SortOrder::Asc);
        assert_eq!(SortOrder::from_str_param("DESC"), SortOrder::Desc);
        assert_eq!(SortOrder::from_str_param("other"), SortOrder::Desc);
    }

    #[test]
    fn sort_order_toggle() {
        assert_eq!(SortOrder::Asc.toggle(), SortOrder::Desc);
        assert_eq!(SortOrder::Desc.toggle(), SortOrder::Asc);
    }

    #[test]
    fn sort_order_sql() {
        assert_eq!(SortOrder::Asc.sql(), "ASC");
        assert_eq!(SortOrder::Desc.sql(), "DESC");
    }

    // --- SortColumn validation ---

    #[test]
    fn sort_column_valid() {
        let col = MarketCategory::Minerals.validate_sort_column("nazwa");
        assert_eq!(col.as_str(), "nazwa");
    }

    #[test]
    fn sort_column_invalid_falls_back() {
        let col = MarketCategory::Minerals.validate_sort_column("hacked");
        assert_eq!(col.as_str(), "id");
    }

    #[test]
    fn equipment_sort_columns_include_power() {
        let cols = MarketCategory::Equipment.allowed_sort_columns();
        assert!(cols.contains(&"power"));
        assert!(cols.contains(&"cost"));
    }

    // --- paginate ---

    #[test]
    fn paginate_page_one() {
        let p = paginate(100, 1);
        assert_eq!(p.page, 1);
        assert_eq!(p.total_pages, 4); // ceil(100/30) = 4
        assert_eq!(p.offset, 0);
        assert_eq!(p.limit, 30);
    }

    #[test]
    fn paginate_last_page() {
        let p = paginate(100, 4);
        assert_eq!(p.page, 4);
        assert_eq!(p.offset, 90);
    }

    #[test]
    fn paginate_clamps_high() {
        let p = paginate(100, 99);
        assert_eq!(p.page, 4);
    }

    #[test]
    fn paginate_clamps_low() {
        let p = paginate(100, 0);
        assert_eq!(p.page, 1);
    }

    #[test]
    fn paginate_zero_items() {
        let p = paginate(0, 1);
        assert_eq!(p.total_pages, 1);
        assert_eq!(p.page, 1);
    }

    #[test]
    fn paginate_exact_page_boundary() {
        let p = paginate(60, 2);
        assert_eq!(p.total_pages, 2);
        assert_eq!(p.page, 2);
        assert_eq!(p.offset, 30);
    }

    // --- sanitize_search ---

    #[test]
    fn search_empty() {
        assert_eq!(sanitize_search(""), "");
        assert_eq!(sanitize_search("  "), "");
    }

    #[test]
    fn search_wraps_plain() {
        assert_eq!(sanitize_search("iron"), "%iron%");
    }

    #[test]
    fn search_preserves_wildcards() {
        assert_eq!(sanitize_search("iron*"), "iron%");
    }

    #[test]
    fn search_star_conversion() {
        assert_eq!(sanitize_search("*steel*"), "%steel%");
    }

    #[test]
    fn search_already_has_percent() {
        assert_eq!(sanitize_search("%coal%"), "%coal%");
    }

    // --- OfferSummary ---

    #[test]
    fn offer_summary_total() {
        let summary = OfferSummary {
            counts: vec![
                OfferCount {
                    category: MarketCategory::Minerals,
                    count: 5,
                },
                OfferCount {
                    category: MarketCategory::Herbs,
                    count: 3,
                },
            ],
        };
        assert_eq!(summary.total(), 8);
    }

    #[test]
    fn offer_summary_empty() {
        let summary = OfferSummary { counts: vec![] };
        assert_eq!(summary.total(), 0);
    }
}
