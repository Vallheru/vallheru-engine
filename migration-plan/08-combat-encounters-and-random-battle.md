# 08 Combat, Encounters, and Random Battle

## Source Surface

- `battle.php`
- `explore.php`
- `hunters.php`
- `farm.php`
- `includes/funkcje.php`
- `includes/turnfight.php`
- `includes/battle.php`
- `includes/monsters.php`
- `hospital.php`
- `battlelogs` and related combat tables

## Goal

Extract and port the battle engine and the encounter flows that depend on it without losing state consistency.

## Tasks

### MP-08-01: Isolate combat formulas from PHP helpers

- Description: Identify and port the pure damage, hit, defense, speed, and reward formulas from `includes/funkcje.php` and `includes/turnfight.php` into Rust domain functions.
- Estimated time: 2h
- Dependencies: MP-06-03.
- Acceptance criteria:
  - Core formulas are implemented as pure Rust functions.
  - Legacy formula order and rounding behavior are documented.
  - Formula tests exist for representative cases.
- Technical notes: Do not mix database reads into these functions.
- In scope: Pure battle formulas.
- Out of scope: Route orchestration.

### MP-08-02: Port monster and encounter selection

- Description: Rebuild the logic that chooses monsters, encounter ranges, and loot tables for explore/farm/hunter style flows.
- Estimated time: 1.5h
- Dependencies: MP-02-05, MP-08-01.
- Acceptance criteria:
  - Encounter generation reads from PostgreSQL reference data.
  - Location and quest-specific monster selection rules are preserved.
  - The service can be used by multiple routes.
- Technical notes: Keep RNG injection explicit so tests can control outcomes.
- In scope: Encounter generation and monster loading.
- Out of scope: Full fight resolution.

### MP-08-03: Port player-versus-monster battle execution

- Description: Implement the request flow that starts, advances, and resolves PvE battles.
- Estimated time: 2h
- Dependencies: MP-08-01, MP-08-02, MP-05-06.
- Acceptance criteria:
  - A player can start and resolve a PvE battle in Rust.
  - Battle state survives across requests where the old PHP flow required it.
  - Victory and defeat side effects are persisted transactionally.
- Technical notes: Keep battle state outside ad hoc template variables.
- In scope: PvE execution flow and persistence.
- Out of scope: PvP and quest-specific branches.

### MP-08-04: Port player-versus-player and arena-style flows

- Description: Recreate PvP battle setup, resolution, logging, and player state changes.
- Estimated time: 2h
- Dependencies: MP-08-03.
- Acceptance criteria:
  - PvP battles apply correct win/loss and status effects.
  - Battle logs or summaries are stored in PostgreSQL.
  - Mutual player updates happen in one transaction.
- Technical notes: Treat PvP as a separate service boundary because rollback rules are stricter.
- In scope: PvP execution and persistence.
- Out of scope: Team or tribe warfare.

### MP-08-05: Port route-specific combat entry points

- Description: Wire the battle engine into `explore.php`, `hunters.php`, `farm.php`, and other battle-triggering pages.
- Estimated time: 2h
- Dependencies: MP-08-03, MP-08-04.
- Acceptance criteria:
  - Route-specific reward and energy costs are preserved.
  - Each route uses shared combat services rather than duplicating logic.
  - Rust handlers can replace these PHP routes independently.
- Technical notes: Keep route-owned preconditions in the web layer and battle-owned state changes in the domain layer.
- In scope: Entry-point integration.
- Out of scope: Quest-specific battle scripts.

### MP-08-06: Port defeat, hospital, and resurrection side effects

- Description: Migrate the non-happy-path mechanics around defeat, hospital recovery, and resurrection-style outcomes.
- Estimated time: 1.5h
- Dependencies: MP-08-03.
- Acceptance criteria:
  - Defeat updates player health and persistent status consistently.
  - Hospital recovery matches current constraints.
  - Resurrection-style flows are covered by tests.
- Technical notes: These side effects are easy to miss because they are split across helpers and page scripts.
- In scope: Defeat, recovery, and resurrection aftermath.
- Out of scope: Poison and antidote interactions.

### MP-08-07: Port poison and antidote aftermath rules

- Description: Rebuild the status-effect mechanics that apply poison, clear antidote state, and connect battle aftermath to alchemy items.
- Estimated time: 1h
- Dependencies: MP-08-03, MP-11-03.
- Acceptance criteria:
  - Poison state changes persist correctly after battle.
  - Antidote interactions behave consistently with the PHP version.
  - Tests cover at least one poisoned and one antidote-cleared outcome.
- Technical notes: Keep these rules isolated so combat and alchemy can share them safely.
- In scope: Poison and antidote aftermath rules.
- Out of scope: Era reset healing.