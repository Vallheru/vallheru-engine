# 09 Items, Inventory, and Equipment

## Source Surface

- `equip.php`
- `armor.php`
- `weapons.php`
- `bows.php`
- `czary.php`
- `warehouse.php`
- `source.php`
- equipment-related reads in `class/player_class.php`

## Goal

Port player-owned item management, equipment bonuses, and storage flows into explicit Rust services.

## Tasks

### MP-09-01: Define item catalog and owned-item models

- Description: Separate immutable item definitions from player-owned inventory records, including weapons, armor, bows, spells, and special item variants.
- Estimated time: 1.5h
- Dependencies: MP-02-01, MP-02-05.
- Acceptance criteria:
  - Rust types exist for catalog items and owned instances.
  - Shared item attributes are normalized where practical.
  - The model supports legacy fields such as repair, poison, durability, and equipped status.
- Technical notes: Avoid forcing every item category into one giant enum if it makes rules harder to read.
- In scope: Item domain types.
- Out of scope: Market listing behavior.

### MP-09-02: Port equipment loadout and stat bonus logic

- Description: Recreate how equipped items modify player stats, mana, speed, and special effects.
- Estimated time: 2h
- Dependencies: MP-06-01, MP-09-01.
- Acceptance criteria:
  - Equipment changes update the derived player snapshot correctly.
  - Ring, cape, and specialty bonus ordering matches legacy behavior.
  - Equip and unequip operations are transactional.
- Technical notes: This module must align with the player calculation fixtures from file 06.
- In scope: Equipment effects and loadout state.
- Out of scope: Combat damage formulas.

### MP-09-03: Port inventory and equipment pages

- Description: Rebuild the main equipment/inventory screens for browsing, equipping, unequipping, and discarding items.
- Estimated time: 1.5h
- Dependencies: MP-04-05, MP-09-02.
- Acceptance criteria:
  - The migrated pages display the same essential item information as the PHP screens.
  - Common actions complete without partial inventory corruption.
  - Validation messages cover invalid ownership and invalid quantity cases.
- Technical notes: Keep per-page templates thin and backed by view models.
- In scope: Inventory/equipment handlers and templates.
- Out of scope: Market-facing item transfers.

### MP-09-04: Port spell, bow, and specialty item handling

- Description: Port category-specific routes and rules that differ from generic equipment, especially bows and spells.
- Estimated time: 1.5h
- Dependencies: MP-09-03.
- Acceptance criteria:
  - Bow- and spell-specific attributes render and persist correctly.
  - Category-specific restrictions are enforced.
  - Shared item infrastructure is reused where possible.
- Technical notes: Keep the category differences explicit; do not hide them in fragile generic field maps.
- In scope: Bows, spells, and specialty item actions.
- Out of scope: Profession-crafted item generation.

### MP-09-05: Port warehouse and storage transfers

- Description: Rebuild item storage, retrieval, and transfer flows for warehouse-style pages.
- Estimated time: 1.5h
- Dependencies: MP-09-01, MP-10-02.
- Acceptance criteria:
  - Deposits and withdrawals are transactional.
  - Quantity updates are correct under repeated requests.
  - Ownership and location constraints are preserved.
- Technical notes: Storage pages are a good stress test for quantity mutation code.
- In scope: Warehouse handlers, service methods, and templates.
- Out of scope: Tribe shared storage.

### MP-09-06: Add item parity tests and sample fixtures

- Description: Capture representative inventory states and use them to verify equipment effects and transfer behavior.
- Estimated time: 2h
- Dependencies: MP-09-02, MP-09-05.
- Acceptance criteria:
  - Fixtures cover equipped items, stacked items, and durability/poison edge cases.
  - Tests fail on quantity drift and bonus drift.
  - The fixtures are reusable by combat and economy modules.
- Technical notes: Reuse imported test players wherever possible.
- In scope: Item/inventory parity tests.
- Out of scope: Browser-driven UI tests.