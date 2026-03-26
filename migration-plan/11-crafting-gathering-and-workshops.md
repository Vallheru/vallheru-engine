# 11 Crafting, Gathering, and Workshops

## Current State

- `alchemik.php` (alchemy)
- `kowal.php` (blacksmith)
- `jeweller.php`
- `jewellershop.php`
- `crafts.php`
- `kopalnia.php` (mining)
- `mines.php`
- `lumberjack.php`
- `lumbermill.php`
- `smelter.php`
- `core.php` (pet breeding)
- `includes/checkastral.php` (astral item validation)
- `includes/findastral.php` (astral item discovery)
- `includes/astralsteal.php` (astral theft mechanics)
- `includes/astralvault.php` (astral vault storage)

## Why This Module Exists

Port the profession systems and resource loops that create most player-owned items and materials.

## Target Rust Shape

- `crates/domain/src/crafting/workshop.rs` — Shared workshop action pattern: consume inputs, spend energy, produce outputs.
- `crates/domain/src/crafting/smithing.rs` — Blacksmith, armorer, weapon production rules.
- `crates/domain/src/crafting/alchemy.rs` — Herb/potion production, poison/antidote rules.
- `crates/domain/src/crafting/gathering.rs` — Mining, lumber, smelting, farm gathering loops.
- `crates/domain/src/crafting/jeweller.rs` — Jeweller and astral crafting, plan/component handling.
- `crates/domain/src/crafting/breeding.rs` — Core pet breeding, offspring generation, active pet state.
- `crates/data/src/crafting.rs` — Recipe queries, material inventory, astral vault, pet records.
- `crates/web/src/handlers/crafting.rs` — Per-profession page handlers.

## Module Dependencies

- 06 Player State and Progression (skill levels, energy).
- 09 Items, Inventory, and Equipment (item catalog, produced items land in inventory).
- 10 Economy, Markets, and Banking (resource costs, currency spending).

## Risks and Notes

- Astral-related includes (`checkastral.php`, `findastral.php`, `astralsteal.php`, `astralvault.php`) implement a hidden subsystem for special items. These must be mapped carefully.
- `core.php` (pet breeding) is one of the most rule-heavy files in the codebase with hidden RNG and cost formulas.
- Gathering loops depend on both player stats and simple RNG; RNG must be injectable for testing.

## Tasks

### MP-11-01: Define a shared workshop action pattern ✅

- Status: **Complete**
- Description: Create a common Rust pattern for profession actions that consume resources, spend energy/training, and produce outputs.
- Estimate: 1.5h
- Depends on: MP-06-01, MP-10-02.
- Functional acceptance criteria:
  - Workshop actions share a consistent command/result structure.
  - Resource consumption and reward production happen transactionally.
  - The pattern supports long PHP pages without forcing one mega-handler.
- Technical notes: This task exists to stop every profession from inventing its own mutation protocol.
- In scope: Shared workshop service design.
- Out of scope: Specific profession rules.

### MP-11-02: Port smithing, armorer, and weapon production

- Description: Rebuild the blacksmith, armorer, and weapon-related production flows from `kowal.php`, `armor.php`, `weapons.php`, and related pages.
- Estimate: 2h
- Depends on: MP-11-01, MP-09-01.
- Functional acceptance criteria:
  - Production actions validate resources, skill levels, and item recipes.
  - Produced items land in player inventory correctly.
  - Failure states do not partially consume resources.
- Technical notes: Keep recipe definitions data-driven where the old code already implies static catalogs.
- In scope: Smithing/armorer production logic.
- Out of scope: Market resale of produced goods.

### MP-11-03: Port alchemy, herbs, potions, and antidotes

- Description: Rebuild herb consumption, potion production, poison/antidote flows, and alchemy-specific skill effects.
- Estimate: 2h
- Depends on: MP-11-01, MP-09-01.
- Functional acceptance criteria:
  - Alchemy recipes and skill-driven success rules work in Rust.
  - Produced potions stack or merge according to legacy rules.
  - Poison and antidote items integrate with combat aftermath flows.
- Technical notes: This module touches combat indirectly, so keep the integration seam clean.
- In scope: Alchemy and potion production.
- Out of scope: Potion market handling.

### MP-11-04: Port mining, lumber, smelting, and farm gathering loops

- Description: Migrate the gather-and-refine loops for mines, lumber, farms, and smelter-style pages.
- Estimate: 2h
- Depends on: MP-11-01, MP-07-04.
- Functional acceptance criteria:
  - Resource gathering spends the correct energy and updates inventories/material tables.
  - Refinement actions produce correct outputs.
  - Location requirements and profession requirements are enforced.
- Technical notes: These flows often depend on both player stats and simple RNG.
- In scope: Gathering and refinement loops.
- Out of scope: Outpost production.

### MP-11-05: Port jeweller, crafts, and astral production

- Description: Rebuild the jeweller and astral-oriented crafting flows, including plan access and component handling.
- Estimate: 2h
- Depends on: MP-11-01, MP-09-01, MP-02-05.
- Functional acceptance criteria:
  - Astral and jeweller workflows can load recipe/component data.
  - Inputs and outputs are persisted correctly.
  - The service supports later tribe-shared crafting extensions.
- Technical notes: Keep astral plan storage separate from generic inventory where the data model differs materially.
- In scope: Jeweller and astral production.
- Out of scope: Tribe craft storage.

### MP-11-06: Port core breeding rules ✅

- Description: Migrate the `core.php` breeding inputs, costs, success chances, and offspring generation rules into Rust.
- Estimate: 1.5h
- Depends on: MP-06-03, MP-11-01.
- Functional acceptance criteria:
  - Core breeding inputs, costs, and success chances match legacy rules.
  - New core records are created correctly.
  - Breeding tests cover success and failure paths.
- Technical notes: This is one of the most hidden rule-heavy parts of the repository.
- In scope: Core breeding domain logic.
- Out of scope: Active pet combat integration and ranking UI.

### MP-11-07: Port active pet state and core ranking views

- Description: Rebuild active core pet selection, derived player/combat integration, and core ranking displays.
- Estimate: 1h
- Depends on: MP-11-06, MP-08-01.
- Functional acceptance criteria:
  - Active pet data feeds back into derived player and combat state correctly.
  - Core ranking displays can read from PostgreSQL.
  - Switching active pets updates the correct persisted state.
- Technical notes: Keep ranking reads separate from breeding mutations.
- In scope: Active pet state and rankings.
- Out of scope: Full UI polish for ranking tables.