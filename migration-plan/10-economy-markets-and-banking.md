# 10 Economy, Markets, and Banking

## Source Surface

- `market.php`
- `amarket.php`
- `cmarket.php`
- `hmarket.php`
- `imarket.php`
- `lmarket.php`
- `mmarket.php`
- `pmarket.php`
- `rmarket.php`
- `bank.php`
- `zloto.php`
- `msklep.php`

## Goal

Port the currency, banking, and market systems as reusable transactional services rather than per-page SQL scripts.

## Tasks

### MP-10-01: Map market variants to shared workflows

- Description: Classify each market page by the workflow it implements: browse, list, buy, cancel, deliver, or catalog shop.
- Estimated time: 1.5h
- Dependencies: MP-02-01, MP-09-01.
- Acceptance criteria:
  - Every market page is mapped to a shared Rust service concept.
  - Unique rules for herbs, potions, astral goods, and player equipment are documented.
  - Duplicated PHP logic is identified for consolidation.
- Technical notes: The point is to share workflows, not to erase category-specific rules.
- In scope: Market workflow matrix.
- Out of scope: Handler implementation.

### MP-10-02: Port currencies, bank balances, and transfers

- Description: Implement the core money services for credits, bank balance, platinum, and related currency movements.
- Estimated time: 1.5h
- Dependencies: MP-06-01, MP-02-04.
- Acceptance criteria:
  - Money movements are represented as explicit service operations.
  - Transfers are transaction-safe.
  - Validation prevents negative balances and double application.
- Technical notes: Keep money mutations centralized so every market reuses them.
- In scope: Currency domain and persistence.
- Out of scope: Offer listings.

### MP-10-03: Port shared market listing and purchase flows

- Description: Rebuild shared listing, sorting, filtering, buy, and cancel operations used by multiple market pages.
- Estimated time: 2h
- Dependencies: MP-10-01, MP-10-02.
- Acceptance criteria:
  - The Rust app can browse and purchase at least one market category end to end.
  - Pagination and sorting behavior are preserved where currently supported.
  - Purchases update buyer inventory and seller proceeds in one transaction.
- Technical notes: Keep buyer-side and seller-side effects together in one domain service.
- In scope: Shared market service and first handler implementations.
- Out of scope: Category-specific stock rules.

### MP-10-04: Port category-specific market rules

- Description: Implement the category-specific behaviors for potions, herbs, player equipment, astral goods, rings, and other special inventory types.
- Estimated time: 2h
- Dependencies: MP-10-03, MP-09-04, MP-11-05.
- Acceptance criteria:
  - Each migrated market type respects its quantity and item-shape rules.
  - Cross-table mutations are transaction-safe.
  - The route handlers reuse the shared market service skeleton.
- Technical notes: This is where the PHP market sprawl gets folded into a manageable set of rule modules.
- In scope: Category rule adapters and handlers.
- Out of scope: Shop-stock generation.

### MP-10-05: Port bank, gold, and shop-style pages

- Description: Rebuild bank interactions, gold-related pages, and stock-based shop pages such as `msklep.php`.
- Estimated time: 1.5h
- Dependencies: MP-10-02, MP-04-05.
- Acceptance criteria:
  - Deposit and withdrawal flows work.
  - Shop pages can render stock and process purchases.
  - Logs or audit events exist for money-changing operations.
- Technical notes: Treat fixed shops separately from player-to-player markets.
- In scope: Bank and shop handlers.
- Out of scope: Crafting production.

### MP-10-06: Add market reconciliation tests

- Description: Create tests that validate no money or quantity is lost across typical listing and buying sequences.
- Estimated time: 1.5h
- Dependencies: MP-10-03, MP-10-04, MP-10-05.
- Acceptance criteria:
  - Tests cover list, buy, partial buy, cancel, and insufficient-funds cases.
  - Buyer inventory, seller proceeds, and listing counts reconcile after each scenario.
  - Failures identify the broken invariant clearly.
- Technical notes: This is the main safety net against silent economy corruption.
- In scope: Economy invariants and tests.
- Out of scope: Full performance testing.