# 11 Crafting, Gathering, and Workshops

## Source Surface

- `alchemik.php`
- `kowal.php`
- `jeweller.php`
- `jewellershop.php`
- `crafts.php`
- `kopalnia.php`
- `mines.php`
- `lumberjack.php`
- `lumbermill.php`
- `smelter.php`
- `core.php`

## Goal

Port the profession systems and resource loops that create most player-owned items and materials.

## Tasks

### MP-11-01: Define a shared workshop action pattern

- Description: Create a common Rust pattern for profession actions that consume resources, spend energy/training, and produce outputs.
- Estimated time: 1.5h
- Dependencies: MP-06-01, MP-10-02.
- Acceptance criteria:
  - Workshop actions share a consistent command/result structure.
  - Resource consumption and reward production happen transactionally.
  - The pattern supports long PHP pages without forcing one mega-handler.
- Technical notes: This task exists to stop every profession from inventing its own mutation protocol.
- In scope: Shared workshop service design.
- Out of scope: Specific profession rules.

### MP-11-02: Port smithing, armorer, and weapon production

- Description: Rebuild the blacksmith, armorer, and weapon-related production flows from `kowal.php`, `armor.php`, `weapons.php`, and related pages.
- Estimated time: 2h
- Dependencies: MP-11-01, MP-09-01.
- Acceptance criteria:
  - Production actions validate resources, skill levels, and item recipes.
  - Produced items land in player inventory correctly.
  - Failure states do not partially consume resources.
- Technical notes: Keep recipe definitions data-driven where the old code already implies static catalogs.
- In scope: Smithing/armorer production logic.
- Out of scope: Market resale of produced goods.

### MP-11-03: Port alchemy, herbs, potions, and antidotes

- Description: Rebuild herb consumption, potion production, poison/antidote flows, and alchemy-specific skill effects.
- Estimated time: 2h
- Dependencies: MP-11-01, MP-09-01.
- Acceptance criteria:
  - Alchemy recipes and skill-driven success rules work in Rust.
  - Produced potions stack or merge according to legacy rules.
  - Poison and antidote items integrate with combat aftermath flows.
- Technical notes: This module touches combat indirectly, so keep the integration seam clean.
- In scope: Alchemy and potion production.
- Out of scope: Potion market handling.

### MP-11-04: Port mining, lumber, smelting, and farm gathering loops

- Description: Migrate the gather-and-refine loops for mines, lumber, farms, and smelter-style pages.
- Estimated time: 2h
- Dependencies: MP-11-01, MP-07-04.
- Acceptance criteria:
  - Resource gathering spends the correct energy and updates inventories/material tables.
  - Refinement actions produce correct outputs.
  - Location requirements and profession requirements are enforced.
- Technical notes: These flows often depend on both player stats and simple RNG.
- In scope: Gathering and refinement loops.
- Out of scope: Outpost production.

### MP-11-05: Port jeweller, crafts, and astral production

- Description: Rebuild the jeweller and astral-oriented crafting flows, including plan access and component handling.
- Estimated time: 2h
- Dependencies: MP-11-01, MP-09-01, MP-02-05.
- Acceptance criteria:
  - Astral and jeweller workflows can load recipe/component data.
  - Inputs and outputs are persisted correctly.
  - The service supports later tribe-shared crafting extensions.
- Technical notes: Keep astral plan storage separate from generic inventory where the data model differs materially.
- In scope: Jeweller and astral production.
- Out of scope: Tribe craft storage.

### MP-11-06: Port core breeding rules

- Description: Migrate the `core.php` breeding inputs, costs, success chances, and offspring generation rules into Rust.
- Estimated time: 1.5h
- Dependencies: MP-06-03, MP-11-01.
- Acceptance criteria:
  - Core breeding inputs, costs, and success chances match legacy rules.
  - New core records are created correctly.
  - Breeding tests cover success and failure paths.
- Technical notes: This is one of the most hidden rule-heavy parts of the repository.
- In scope: Core breeding domain logic.
- Out of scope: Active pet combat integration and ranking UI.

### MP-11-07: Port active pet state and core ranking views

- Description: Rebuild active core pet selection, derived player/combat integration, and core ranking displays.
- Estimated time: 1h
- Dependencies: MP-11-06, MP-08-01.
- Acceptance criteria:
  - Active pet data feeds back into derived player and combat state correctly.
  - Core ranking displays can read from PostgreSQL.
  - Switching active pets updates the correct persisted state.
- Technical notes: Keep ranking reads separate from breeding mutations.
- In scope: Active pet state and rankings.
- Out of scope: Full UI polish for ranking tables.