# 08 Combat, Encounters, and Random Battle

## Current State

- `battle.php`
- `explore.php`
- `hunters.php`
- `farm.php`
- `hospital.php`
- `includes/funkcje.php` (core damage/hit/defense formulas)
- `includes/turnfight.php` (turn-based fight execution)
- `includes/battle.php` (battle state helpers)
- `includes/monsters.php` (monster data loading)
- `includes/resurect.php` (resurrection after defeat)
- `includes/functions.php` (potion drinking, equipment wear during combat)
- battle log and combat-related tables

## Why This Module Exists

Extract and port the battle engine and the encounter flows that depend on it without losing state consistency.

## Target Rust Shape

- `crates/domain/src/combat/formulas.rs` — Pure damage, hit, defense, speed, reward formulas.
- `crates/domain/src/combat/encounter.rs` — Monster selection, encounter generation, loot tables.
- `crates/domain/src/combat/battle.rs` — PvE and PvP battle execution, turn resolution, state machine.
- `crates/domain/src/combat/aftermath.rs` — Defeat, hospital, resurrection, poison/antidote side effects.
- `crates/data/src/combat.rs` — Monster queries, battle log persistence, player state updates.
- `crates/web/src/handlers/combat.rs` — Battle, explore, farm, hunters, hospital page handlers.

## Module Dependencies

- 06 Player State and Progression (derived stats feed into combat formulas).
- 05 Auth, Accounts, and Sessions (session-backed battle state across requests).
- 02 Database and PostgreSQL (monster reference data, battle logs).

## Risks and Notes

- Combat formulas are spread across `includes/funkcje.php`, `includes/turnfight.php`, and inline code. All must be identified and consolidated.
- Battle state in the current PHP version relies on PHP session arrays. The Rust replacement must handle multi-request battle flows.
- PvP must update two players atomically in one transaction.
- Poison/antidote rules touch both alchemy items and combat aftermath; the integration seam must be clean.

## Tasks

### MP-08-01: Isolate combat formulas from PHP helpers ✅

- Status: **Complete**
- Description: Identify and port the pure damage, hit, defense, speed, and reward formulas from `includes/funkcje.php` and `includes/turnfight.php` into Rust domain functions.
- Estimate: 2h
- Depends on: MP-06-03.
- Functional acceptance criteria:
  - Core formulas are implemented as pure Rust functions.
  - Legacy formula order and rounding behavior are documented.
  - Formula tests exist for representative cases.
- Technical notes: Do not mix database reads into these functions.
- In scope: Pure battle formulas.
- Out of scope: Route orchestration.

### MP-08-02: Port monster and encounter selection ✅

- Description: Rebuild the logic that chooses monsters, encounter ranges, and loot tables for explore/farm/hunter style flows.
- Estimate: 1.5h
- Depends on: MP-02-05, MP-08-01.
- Functional acceptance criteria:
  - Encounter generation reads from PostgreSQL reference data.
  - Location and quest-specific monster selection rules are preserved.
  - The service can be used by multiple routes.
- Technical notes: Keep RNG injection explicit so tests can control outcomes.
- In scope: Encounter generation and monster loading.
- Out of scope: Full fight resolution.

### MP-08-03: Port player-versus-monster battle execution

- Description: Implement the request flow that starts, advances, and resolves PvE battles.
- Estimate: 2h
- Depends on: MP-08-01, MP-08-02, MP-05-06.
- Functional acceptance criteria:
  - A player can start and resolve a PvE battle in Rust.
  - Battle state survives across requests where the old PHP flow required it.
  - Victory and defeat side effects are persisted transactionally.
- Technical notes: Keep battle state outside ad hoc template variables.
- In scope: PvE execution flow and persistence.
- Out of scope: PvP and quest-specific branches.

### MP-08-04: Port player-versus-player and arena-style flows

- Description: Recreate PvP battle setup, resolution, logging, and player state changes.
- Estimate: 2h
- Depends on: MP-08-03.
- Functional acceptance criteria:
  - PvP battles apply correct win/loss and status effects.
  - Battle logs or summaries are stored in PostgreSQL.
  - Mutual player updates happen in one transaction.
- Technical notes: Treat PvP as a separate service boundary because rollback rules are stricter.
- In scope: PvP execution and persistence.
- Out of scope: Team or tribe warfare.

### MP-08-05: Port route-specific combat entry points

- Description: Wire the battle engine into `explore.php`, `hunters.php`, `farm.php`, and other battle-triggering pages.
- Estimate: 2h
- Depends on: MP-08-03, MP-08-04.
- Functional acceptance criteria:
  - Route-specific reward and energy costs are preserved.
  - Each route uses shared combat services rather than duplicating logic.
  - Rust handlers can replace these PHP routes independently.
- Technical notes: Keep route-owned preconditions in the web layer and battle-owned state changes in the domain layer.
- In scope: Entry-point integration.
- Out of scope: Quest-specific battle scripts.

### MP-08-06: Port defeat, hospital, and resurrection side effects

- Description: Migrate the non-happy-path mechanics around defeat, hospital recovery, and resurrection-style outcomes.
- Estimate: 1.5h
- Depends on: MP-08-03.
- Functional acceptance criteria:
  - Defeat updates player health and persistent status consistently.
  - Hospital recovery matches current constraints.
  - Resurrection-style flows are covered by tests.
- Technical notes: These side effects are easy to miss because they are split across helpers and page scripts.
- In scope: Defeat, recovery, and resurrection aftermath.
- Out of scope: Poison and antidote interactions.

### MP-08-07: Port poison and antidote aftermath rules

- Description: Rebuild the status-effect mechanics that apply poison, clear antidote state, and connect battle aftermath to alchemy items.
- Estimate: 1h
- Depends on: MP-08-03, MP-11-03.
- Functional acceptance criteria:
  - Poison state changes persist correctly after battle.
  - Antidote interactions behave consistently with the PHP version.
  - Tests cover at least one poisoned and one antidote-cleared outcome.
- Technical notes: Keep these rules isolated so combat and alchemy can share them safely.
- In scope: Poison and antidote aftermath rules.
- Out of scope: Era reset healing.