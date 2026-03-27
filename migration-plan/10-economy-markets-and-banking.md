# 10 Economy, Markets, and Banking

## Current State

- `market.php`
- `amarket.php` (astral market)
- `cmarket.php` (core/pet market)
- `hmarket.php` (herb market)
- `imarket.php` (item market)
- `lmarket.php` (lumber market)
- `mmarket.php` (mineral market)
- `pmarket.php` (potion market)
- `rmarket.php` (ring market)
- `bank.php`
- `zloto.php` (gold/currency)
- `msklep.php` (magic shop)
- `includes/marketaddto.php` (add listing helper)
- `includes/marketdel.php` (delete listing helper)
- `includes/marketdelall.php` (delete all listings helper)
- `includes/steal.php` (steal from shops)

## Why This Module Exists

Port the currency, banking, and market systems as reusable transactional services rather than per-page SQL scripts.

## Target Rust Shape

- `crates/domain/src/economy/currency.rs` — Money types, balance operations, transfer validation.
- `crates/domain/src/economy/market.rs` — Shared market workflows: list, buy, cancel, deliver.
- `crates/domain/src/economy/market_rules.rs` — Category-specific rules per market variant.
- `crates/data/src/economy.rs` — Banking queries, market listing CRUD, shop stock queries.
- `crates/web/src/handlers/bank.rs` — Bank deposit/withdrawal handlers.
- `crates/web/src/handlers/market.rs` — Market browse/buy/sell handlers for all 9 market variants.
- `crates/web/src/handlers/shop.rs` — Fixed-stock shop handlers (`msklep.php`, `zloto.php`).

## Module Dependencies

- 06 Player State and Progression (player balance, energy costs).
- 09 Items, Inventory, and Equipment (item catalog for market listings).
- 02 Database and PostgreSQL (market tables, banking tables).

## Risks and Notes

- There are 9 distinct market PHP files. Many share workflow patterns but have category-specific listing/purchase rules. Consolidation must preserve these differences.
- Market helper includes (`marketaddto.php`, `marketdel.php`, `marketdelall.php`) contain shared SQL that is currently inlined. These become shared Rust service methods.
- `includes/steal.php` (shoplifting mechanic) couples economy to the thieves system; keep the integration seam explicit.
- Money mutations must be centralized to prevent silent economy corruption.

## Tasks

### MP-10-01: Map market variants to shared workflows ✅

- Status: **Complete**
- Description: Classify each market page by the workflow it implements: browse, list, buy, cancel, deliver, or catalog shop.
- Estimate: 1.5h
- Depends on: MP-02-01, MP-09-01.
- Functional acceptance criteria:
  - Every market page is mapped to a shared Rust service concept.
  - Unique rules for herbs, potions, astral goods, and player equipment are documented.
  - Duplicated PHP logic is identified for consolidation.
- Technical notes: The point is to share workflows, not to erase category-specific rules.
- In scope: Market workflow matrix.
- Out of scope: Handler implementation.

### MP-10-02: Port currencies, bank balances, and transfers ✅

- Status: **Complete**
- Description: Implement the core money services for credits, bank balance, platinum, and related currency movements.
- Estimate: 1.5h
- Depends on: MP-06-01, MP-02-04.
- Functional acceptance criteria:
  - Money movements are represented as explicit service operations.
  - Transfers are transaction-safe.
  - Validation prevents negative balances and double application.
- Technical notes: Keep money mutations centralized so every market reuses them.
- In scope: Currency domain and persistence.
- Out of scope: Offer listings.

### MP-10-03: Port shared market listing and purchase flows ✅

- Status: **Complete**
- Estimate: 2h
- Depends on: MP-10-01, MP-10-02.
- Functional acceptance criteria:
  - The Rust app can browse and purchase at least one market category end to end.
  - Pagination and sorting behavior are preserved where currently supported.
  - Purchases update buyer inventory and seller proceeds in one transaction.
- Technical notes: Keep buyer-side and seller-side effects together in one domain service.
- In scope: Shared market service and first handler implementations.
- Out of scope: Category-specific stock rules.

### MP-10-04: Port category-specific market rules ✅

- Status: **done**
- Description: Implement the category-specific behaviors for potions, herbs, player equipment, astral goods, rings, and other special inventory types.
- Estimate: 2h
- Depends on: MP-10-03, MP-09-04, MP-11-05.
- Functional acceptance criteria:
  - Each migrated market type respects its quantity and item-shape rules.
  - Cross-table mutations are transaction-safe.
  - The route handlers reuse the shared market service skeleton.
- Technical notes: This is where the PHP market sprawl gets folded into a manageable set of rule modules.
- In scope: Category rule adapters and handlers.
- Out of scope: Shop-stock generation.

### MP-10-05: Port bank, gold, and shop-style pages ✅

- Status: **done**
- Description: Rebuild bank interactions, gold-related pages, and stock-based shop pages such as `msklep.php`.
- Estimate: 1.5h
- Depends on: MP-10-02, MP-04-05.
- Functional acceptance criteria:
  - Deposit and withdrawal flows work.
  - Shop pages can render stock and process purchases.
  - Logs or audit events exist for money-changing operations.
- Technical notes: Treat fixed shops separately from player-to-player markets.
- In scope: Bank and shop handlers.
- Out of scope: Crafting production, bank transfers/donations (deferred).

### MP-10-06: Add market reconciliation tests

- Description: Create tests that validate no money or quantity is lost across typical listing and buying sequences.
- Estimate: 1.5h
- Depends on: MP-10-03, MP-10-04, MP-10-05.
- Status: **done**
- Functional acceptance criteria:
  - Tests cover list, buy, partial buy, cancel, and insufficient-funds cases.
  - Buyer inventory, seller proceeds, and listing counts reconcile after each scenario.
  - Failures identify the broken invariant clearly.
- Technical notes: This is the main safety net against silent economy corruption.
- In scope: Economy invariants and tests.
- Out of scope: Full performance testing.