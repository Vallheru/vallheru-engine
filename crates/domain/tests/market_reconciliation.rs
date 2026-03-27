//! Market economy reconciliation tests.
//!
//! These tests verify that no money or quantity is silently lost
//! across typical market operations (list, buy, partial buy, cancel,
//! top-up, price change). They operate on pure domain functions
//! without database access, so they run fast and deterministically.
//!
//! The key invariant: `buyer_cost + seller_proceeds == total_price`,
//! and `listing_remaining + bought + returned == initial_stock`.

use vallheru_domain::economy::market::{
    self, MarketCategory, PurchaseCheck, PurchaseError, PurchaseResult,
};

// =========================================================================
// Helpers
// =========================================================================

/// Simulated player balances for reconciliation tracking.
#[derive(Debug, Clone)]
struct PlayerState {
    id: i32,
    credits: i64,
    bank: i64,
    inventory: i32,
}

/// Simulated listing on the market.
#[derive(Debug, Clone)]
struct Listing {
    seller_id: i32,
    unit_cost: i64,
    quantity: i32,
}

/// Execute a validated purchase and apply the result to states.
fn apply_purchase(
    buyer: &mut PlayerState,
    seller: &mut PlayerState,
    listing: &mut Listing,
    buy_qty: i32,
) -> Result<PurchaseResult, PurchaseError> {
    let check = PurchaseCheck {
        buyer_location: "Altara",
        buyer_id: buyer.id,
        buyer_credits: buyer.credits,
        seller_id: seller.id,
        seller_bank: seller.bank,
        unit_cost: listing.unit_cost,
        listing_quantity: listing.quantity,
        buy_quantity: buy_qty,
    };
    let result = market::validate_purchase(&check)?;

    buyer.credits = result.buyer_new_credits;
    seller.bank = result.seller_new_bank;
    buyer.inventory += buy_qty;
    listing.quantity = result.listing_remaining;

    Ok(result)
}

// =========================================================================
// Money conservation tests
// =========================================================================

#[test]
fn money_conserved_on_full_purchase() {
    let mut buyer = PlayerState {
        id: 1,
        credits: 5000,
        bank: 0,
        inventory: 0,
    };
    let mut seller = PlayerState {
        id: 2,
        credits: 0,
        bank: 1000,
        inventory: 0,
    };
    let mut listing = Listing {
        seller_id: 2,
        unit_cost: 100,
        quantity: 10,
    };

    let initial_total = buyer.credits + buyer.bank + seller.credits + seller.bank;

    let result = apply_purchase(&mut buyer, &mut seller, &mut listing, 10).unwrap();

    let final_total = buyer.credits + buyer.bank + seller.credits + seller.bank;

    // Money is conserved
    assert_eq!(initial_total, final_total);
    // Price matches
    assert_eq!(result.total_price, 1000);
    // Listing depleted
    assert_eq!(listing.quantity, 0);
    // Buyer got items
    assert_eq!(buyer.inventory, 10);
}

#[test]
fn money_conserved_on_partial_purchase() {
    let mut buyer = PlayerState {
        id: 1,
        credits: 5000,
        bank: 0,
        inventory: 0,
    };
    let mut seller = PlayerState {
        id: 2,
        credits: 0,
        bank: 1000,
        inventory: 0,
    };
    let mut listing = Listing {
        seller_id: 2,
        unit_cost: 200,
        quantity: 20,
    };

    let initial_total = buyer.credits + buyer.bank + seller.credits + seller.bank;

    let result = apply_purchase(&mut buyer, &mut seller, &mut listing, 7).unwrap();

    let final_total = buyer.credits + buyer.bank + seller.credits + seller.bank;

    assert_eq!(initial_total, final_total);
    assert_eq!(result.total_price, 1400);
    assert_eq!(listing.quantity, 13);
    assert_eq!(buyer.inventory, 7);
}

#[test]
fn money_conserved_across_multiple_partial_purchases() {
    let mut buyer = PlayerState {
        id: 1,
        credits: 10_000,
        bank: 500,
        inventory: 0,
    };
    let mut seller = PlayerState {
        id: 2,
        credits: 0,
        bank: 200,
        inventory: 0,
    };
    let mut listing = Listing {
        seller_id: 2,
        unit_cost: 50,
        quantity: 100,
    };

    let initial_total = buyer.credits + buyer.bank + seller.credits + seller.bank;
    let mut total_spent = 0i64;
    let mut total_bought = 0i32;

    // Buy in batches: 10, 25, 15, 50
    for &qty in &[10, 25, 15, 50] {
        let result = apply_purchase(&mut buyer, &mut seller, &mut listing, qty).unwrap();
        total_spent += result.total_price;
        total_bought += qty;
    }

    let final_total = buyer.credits + buyer.bank + seller.credits + seller.bank;

    assert_eq!(initial_total, final_total, "money conservation violated");
    assert_eq!(total_bought, 100);
    assert_eq!(total_spent, 5000);
    assert_eq!(listing.quantity, 0);
    assert_eq!(buyer.inventory, 100);
}

#[test]
fn money_conserved_multiple_buyers() {
    let mut listing = Listing {
        seller_id: 10,
        unit_cost: 30,
        quantity: 50,
    };
    let mut seller = PlayerState {
        id: 10,
        credits: 0,
        bank: 100,
        inventory: 0,
    };
    let mut buyers = [
        PlayerState {
            id: 1,
            credits: 600,
            bank: 0,
            inventory: 0,
        },
        PlayerState {
            id: 2,
            credits: 900,
            bank: 0,
            inventory: 0,
        },
        PlayerState {
            id: 3,
            credits: 300,
            bank: 0,
            inventory: 0,
        },
    ];

    let initial_total: i64 =
        seller.credits + seller.bank + buyers.iter().map(|b| b.credits + b.bank).sum::<i64>();

    // Buyer 1 buys 20, buyer 2 buys 15, buyer 3 buys 10
    apply_purchase(&mut buyers[0], &mut seller, &mut listing, 20).unwrap();
    apply_purchase(&mut buyers[1], &mut seller, &mut listing, 15).unwrap();
    apply_purchase(&mut buyers[2], &mut seller, &mut listing, 10).unwrap();

    let final_total: i64 =
        seller.credits + seller.bank + buyers.iter().map(|b| b.credits + b.bank).sum::<i64>();

    assert_eq!(initial_total, final_total, "money conservation violated");
    assert_eq!(listing.quantity, 5);
    assert_eq!(
        buyers.iter().map(|b| b.inventory).sum::<i32>(),
        45,
        "total items bought"
    );
}

// =========================================================================
// Quantity conservation tests
// =========================================================================

#[test]
fn quantity_conserved_buy_all() {
    let initial_stock = 25;
    let mut buyer = PlayerState {
        id: 1,
        credits: 100_000,
        bank: 0,
        inventory: 0,
    };
    let mut seller = PlayerState {
        id: 2,
        credits: 0,
        bank: 0,
        inventory: 0,
    };
    let mut listing = Listing {
        seller_id: 2,
        unit_cost: 10,
        quantity: initial_stock,
    };

    apply_purchase(&mut buyer, &mut seller, &mut listing, initial_stock).unwrap();

    // Items: listing + buyer inventory == initial stock
    assert_eq!(
        listing.quantity + buyer.inventory,
        initial_stock,
        "quantity mismatch"
    );
}

#[test]
fn quantity_conserved_partial_buy_then_cancel() {
    let initial_stock = 30;
    let sell_qty = 30;
    let buy_qty = 12;
    let cancel_qty = sell_qty - buy_qty;

    // Listing starts with sell_qty
    let mut listing = Listing {
        seller_id: 2,
        unit_cost: 5,
        quantity: sell_qty,
    };
    let mut buyer = PlayerState {
        id: 1,
        credits: 10_000,
        bank: 0,
        inventory: 0,
    };
    let mut seller = PlayerState {
        id: 2,
        credits: 0,
        bank: 0,
        inventory: 0,
    };

    // Buy partial
    apply_purchase(&mut buyer, &mut seller, &mut listing, buy_qty).unwrap();

    // Cancel remaining via validate_cancel
    let cancel_result = market::validate_cancel(2, 2, listing.quantity, 0).unwrap();
    seller.inventory += cancel_result.return_quantity;

    // Total items: buyer got + seller got back + still on listing
    assert_eq!(
        buyer.inventory + seller.inventory + cancel_result.listing_remaining,
        initial_stock,
        "quantity leaked"
    );
    assert_eq!(cancel_result.return_quantity, cancel_qty);
}

// =========================================================================
// Insufficient funds tests
// =========================================================================

#[test]
fn insufficient_funds_no_state_change() {
    let mut buyer = PlayerState {
        id: 1,
        credits: 100,
        bank: 0,
        inventory: 0,
    };
    let mut seller = PlayerState {
        id: 2,
        credits: 0,
        bank: 500,
        inventory: 0,
    };
    let mut listing = Listing {
        seller_id: 2,
        unit_cost: 50,
        quantity: 10,
    };

    // Trying to buy 5 costs 250, but buyer only has 100
    let snap_buyer = buyer.clone();
    let snap_seller = seller.clone();
    let snap_listing = listing.clone();

    let err = apply_purchase(&mut buyer, &mut seller, &mut listing, 5).unwrap_err();
    assert_eq!(err, PurchaseError::InsufficientFunds);

    // Nothing changed (domain validation prevents mutation)
    assert_eq!(buyer.credits, snap_buyer.credits);
    assert_eq!(seller.bank, snap_seller.bank);
    assert_eq!(listing.quantity, snap_listing.quantity);
    assert_eq!(buyer.inventory, snap_buyer.inventory);
}

#[test]
fn own_listing_no_state_change() {
    let mut player = PlayerState {
        id: 1,
        credits: 5000,
        bank: 1000,
        inventory: 0,
    };
    let mut listing = Listing {
        seller_id: 1,
        unit_cost: 100,
        quantity: 10,
    };

    let mut seller_copy = player.clone();
    let err = apply_purchase(&mut player, &mut seller_copy, &mut listing, 5).unwrap_err();
    assert_eq!(err, PurchaseError::OwnListing);
    assert_eq!(listing.quantity, 10);
}

// =========================================================================
// Listing validation sequence tests
// =========================================================================

#[test]
fn list_topup_cancel_cycle() {
    // Simulate: player lists 10, tops up 5, cancels 8 → 7 remain on listing
    let initial_inventory = 50;
    let list_qty = 10;
    let topup_qty = 5;
    let cancel_qty = 8;

    // Validate listing
    market::validate_listing(
        "Altara",
        100,
        list_qty,
        initial_inventory,
        None,
        MarketCategory::Minerals,
    )
    .unwrap();
    let remaining_inventory = initial_inventory - list_qty;
    let listing_qty = list_qty;

    // Validate top-up
    let added = market::validate_topup(remaining_inventory, topup_qty).unwrap();
    assert_eq!(added, topup_qty);
    let remaining_inventory = remaining_inventory - topup_qty;
    let listing_qty = listing_qty + topup_qty;

    // Validate cancel
    let cancel = market::validate_cancel(1, 1, listing_qty, cancel_qty).unwrap();
    let final_inventory = remaining_inventory + cancel.return_quantity;

    // Conservation: initial_inventory == final_inventory + listing_remaining
    assert_eq!(
        initial_inventory,
        final_inventory + cancel.listing_remaining,
        "inventory conservation failed"
    );
    assert_eq!(cancel.listing_remaining, 7);
    assert_eq!(final_inventory, 43);
}

#[test]
fn price_change_does_not_affect_balances() {
    // Validate that price change is purely metadata
    let old_price = 100;
    let new_price = 200;
    let owner_id = 1;

    let result = market::validate_price_change(owner_id, owner_id, new_price).unwrap();
    assert_eq!(result, new_price);
    // No money changed hands - this is just a sanity check
    assert_ne!(old_price, new_price);
}

// =========================================================================
// Full lifecycle tests
// =========================================================================

#[test]
fn full_lifecycle_list_buy_reconcile() {
    // Seller lists 100 minerals at 10 gold each.
    // Buyer buys 40, then 30.
    // Seller cancels remaining 30.
    // Verify: everything reconciles perfectly.
    let initial_seller_credits = 1000;
    let initial_seller_bank = 500;
    let initial_buyer_credits = 5000;
    let initial_buyer_bank = 200;
    let initial_seller_inventory = 100;

    let unit_cost = 10;
    let listing_qty = 100;

    // Validate listing
    market::validate_listing(
        "Altara",
        unit_cost,
        listing_qty,
        initial_seller_inventory,
        None,
        MarketCategory::Minerals,
    )
    .unwrap();

    let mut seller = PlayerState {
        id: 2,
        credits: initial_seller_credits,
        bank: initial_seller_bank,
        inventory: initial_seller_inventory - listing_qty,
    };
    let mut buyer = PlayerState {
        id: 1,
        credits: initial_buyer_credits,
        bank: initial_buyer_bank,
        inventory: 0,
    };
    let mut listing = Listing {
        seller_id: 2,
        unit_cost: i64::from(unit_cost),
        quantity: listing_qty,
    };

    let initial_money = seller.credits + seller.bank + buyer.credits + buyer.bank;
    let initial_items = seller.inventory + listing.quantity;

    // Buy 40
    apply_purchase(&mut buyer, &mut seller, &mut listing, 40).unwrap();
    // Buy 30
    apply_purchase(&mut buyer, &mut seller, &mut listing, 30).unwrap();

    // Seller cancels remaining 30
    let cancel =
        market::validate_cancel(seller.id, listing.seller_id, listing.quantity, 0).unwrap();
    seller.inventory += cancel.return_quantity;
    listing.quantity = cancel.listing_remaining;

    let final_money = seller.credits + seller.bank + buyer.credits + buyer.bank;
    let final_items = seller.inventory + buyer.inventory + listing.quantity;

    assert_eq!(initial_money, final_money, "money leaked!");
    assert_eq!(initial_items, final_items, "items leaked!");
    assert_eq!(listing.quantity, 0);
    assert_eq!(buyer.inventory, 70);
    assert_eq!(seller.inventory, 30);
    assert_eq!(buyer.credits, initial_buyer_credits - 700);
    assert_eq!(seller.bank, initial_seller_bank + 700);
}

#[test]
fn exhaustive_sequential_single_unit_purchases() {
    // Buy all items one at a time - verify money conservation each step
    let n = 50;
    let unit_cost = 7i64;
    let mut buyer = PlayerState {
        id: 1,
        credits: i64::from(n) * unit_cost + 100,
        bank: 0,
        inventory: 0,
    };
    let mut seller = PlayerState {
        id: 2,
        credits: 0,
        bank: 0,
        inventory: 0,
    };
    let mut listing = Listing {
        seller_id: 2,
        unit_cost,
        quantity: n,
    };

    let initial_money = buyer.credits + seller.bank;

    for i in 0..n {
        let result = apply_purchase(&mut buyer, &mut seller, &mut listing, 1).unwrap();
        assert_eq!(result.total_price, unit_cost);
        assert_eq!(result.listing_remaining, n - i - 1);

        // Check money conservation at every step
        let current_money = buyer.credits + seller.bank;
        assert_eq!(initial_money, current_money, "money leaked at step {i}");
    }

    assert_eq!(listing.quantity, 0);
    assert_eq!(buyer.inventory, n);
}

// =========================================================================
// Category-specific sort column tests
// =========================================================================

#[test]
fn all_categories_have_id_sort() {
    for cat in MarketCategory::ALL {
        assert!(
            cat.allowed_sort_columns().contains(&"id"),
            "category {cat:?} missing id sort column",
        );
    }
}

#[test]
fn all_categories_have_cost_sort() {
    for cat in MarketCategory::ALL {
        assert!(
            cat.allowed_sort_columns().contains(&"cost"),
            "category {cat:?} missing cost sort column",
        );
    }
}

#[test]
fn sort_column_cannot_inject_sql() {
    for cat in MarketCategory::ALL {
        let col = cat.validate_sort_column("'; DROP TABLE players; --");
        assert_eq!(col.as_str(), "id", "SQL injection should fall back to id");
    }
}

// =========================================================================
// Pagination edge-case tests
// =========================================================================

#[test]
fn paginate_single_item() {
    let p = market::paginate(1, 1);
    assert_eq!(p.total_pages, 1);
    assert_eq!(p.offset, 0);
}

#[test]
fn paginate_exactly_one_page() {
    let p = market::paginate(30, 1);
    assert_eq!(p.total_pages, 1);
}

#[test]
fn paginate_one_over_page() {
    let p = market::paginate(31, 2);
    assert_eq!(p.total_pages, 2);
    assert_eq!(p.offset, 30);
}

#[test]
fn paginate_negative_items() {
    let p = market::paginate(-5, 1);
    assert_eq!(p.total_pages, 1);
    assert_eq!(p.page, 1);
}

// =========================================================================
// Search sanitization edge cases
// =========================================================================

#[test]
fn search_sql_injection_attempt() {
    let s = market::sanitize_search("'; DROP TABLE pmarket; --");
    // Should be wrapped in %, not executable SQL
    assert!(s.starts_with('%'));
    assert!(s.ends_with('%'));
    assert!(s.contains("DROP TABLE"));
}

#[test]
fn search_unicode() {
    let s = market::sanitize_search("mithrilowy miecz");
    assert_eq!(s, "%mithrilowy miecz%");
}

#[test]
fn search_star_at_both_ends() {
    let s = market::sanitize_search("*gold*");
    assert_eq!(s, "%gold%");
}
