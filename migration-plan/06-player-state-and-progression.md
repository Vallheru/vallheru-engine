# 06 Player State and Progression

## Current State

- `class/player_class.php`
- `stats.php`
- `view.php`
- `hof.php`
- `hof2.php`
- `ap.php`
- `train.php`
- `rasa.php`
- `klasa.php`
- `deity.php`

## Why This Module Exists

Define the central Rust player model and port all derived calculations and progression mechanics that other modules depend on.

## Target Rust Shape

- `crates/domain/src/player.rs` — Core player aggregate: persisted fields, derived stats, transient state.
- `crates/domain/src/player/stats.rs` — Derived stat, mana, and bonus calculations.
- `crates/domain/src/player/progression.rs` — AP spending, training, class/race/deity mutations.
- `crates/domain/src/player/legacy.rs` — Import/parity logic for serialized legacy columns.
- `crates/data/src/player.rs` — Player repository with explicit SQL queries.
- `crates/web/src/handlers/player.rs` — Stats, view, HoF, AP, train, race, class, deity page handlers.

## Module Dependencies

- 02 Database and PostgreSQL (player table schema, normalized field strategy).
- 04 Rendering, Assets, and Localization (stat page templates).

## Risks and Notes

- `class/player_class.php` is the most coupled file in the codebase. Many pages depend on its methods and derived values.
- The serialized columns (`settings`, `stats`, `skills`, `bonuses`) require careful parsing. Round-trip compatibility may be needed during the migration window.
- Derived stat calculations interact with equipment, blessings, race/class, and temporary buffs. All must be documented and tested.

## Tasks

### MP-06-01: Define the Rust player aggregate ✅

- **Status**: completed-correctly (commit `717e8a2`)
- Description: Design the core player domain types, separating persisted fields, derived fields, and transient request/session state.
- Estimate: 1.5h
- Depends on: MP-02-03.
- Functional acceptance criteria:
  - The player aggregate has a stable Rust API for other modules.
  - Persisted and computed values are not mixed into one unstructured blob.
  - Session-only state is identified explicitly.
- Technical notes: Keep domain types close to how gameplay rules are expressed, not how the old table happened to store them.
- In scope: Player domain model.
- Out of scope: Full repository implementation.

### MP-06-02: ~~Port legacy field parsing and serialization~~ OBSOLETE

- **Status**: obsolete
- Description: Originally about legacy semicolon-delimited format parsers. These were implemented, then removed during remediation. Under the new architecture rules, no legacy compatibility parsing is needed. Data lives in normalized tables with clean Rust types.
- **Reason obsolete**: No backward compatibility with legacy PHP data formats required.

### MP-06-03: Port derived stat, mana, and bonus calculations ✅

- **Status**: not-started (partially covered by MP-09-02's equipment.rs)
- Description: Create the unified player calculation snapshot. Equipment stat/skill bonus application already exists in `equipment.rs` (MP-09-02). Remaining: XP gain/level-up logic, HP-per-condition tables, max mana formula, seeker perception bonus, and an orchestrator function that produces a fully calculated player view.
- Estimate: 2h
- Depends on: MP-06-01, MP-09-02.
- Functional acceptance criteria:
  - Derived stats match legacy behavior for representative players.
  - Mana, health-related caps, and bonus application order are documented and tested.
  - The web layer can request a fully calculated player snapshot without mutating storage.
- Technical notes: Separate pure calculations from repository reads so parity tests are straightforward. `equipment.rs` already handles `curstats()`, `curskills()`, and `checkbonus()`. This task adds XP progression, mana caps, HP from condition, and the snapshot orchestrator.
- In scope: Calculation engine, XP leveling, mana/HP formulas, snapshot function.
- Out of scope: Battle resolution.

### MP-06-04: Port AP, training, class, race, and deity mutations ✅

- **Status**: completed
- Description: Migrate the domain logic for AP bonus purchasing, stat training, race/class/deity selection mutations. Pure domain functions with validation, cost calculations, and typed error handling.
- Estimate: 1.5h
- Depends on: MP-06-03.
- Functional acceptance criteria:
  - AP spending and training enforce current prerequisites.
  - Race, class, and deity choices update the player model correctly.
  - Changes are persisted transactionally.
- Technical notes: Keep mutation logic in domain services, not handlers.
- In scope: Progression mutations.
- Out of scope: Item or combat side effects outside direct progression changes.

### MP-06-05: Port player-facing read models ✅

- **Status**: completed
- Description: Rebuild profile pages, player inspection, hall-of-fame views, and stats screens.
- Estimate: 1.5h
- Depends on: MP-04-05, MP-06-03.
- Functional acceptance criteria:
  - Public and authenticated profile/stat pages render from PostgreSQL-backed Rust view models.
  - Hall-of-fame ordering matches current rules.
  - Page-specific formatting is isolated from domain logic.
- Technical notes: Implemented in `crates/domain/src/player/views.rs` — contains `ProfileView`, `StatsView`, `HofEntry`, `HofMachineEntry`, `MemberListEntry`, `Pagination`, `MemberSort`, `ThreatLevel`, `BattleLogEntry`, `StatDisplay`, `SkillDisplay`, `BonusDisplay` types plus pure functions for combat power, consider threat, rank/last-seen/battle-result formatting, XP progress, and blessing/antidote labels. 48 unit tests.
- In scope: Read models and pages for player status.
- Out of scope: Account settings edits.

### MP-06-06: Add parity fixtures for player calculations ✅

- **Status**: completed
- Description: Capture representative player records and expected derived values from PHP, then codify them as Rust tests. Found and fixed a bug in Gnome craftsman bonus (was +15% instead of correct doubled +20%).
- Estimate: 2h
- Depends on: MP-06-03.
- Functional acceptance criteria:
  - Fixtures cover at least class, race, blessing, and equipment bonus combinations.
  - Tests fail on calculation drift.
  - Fixture sources are documented so they can be updated safely.
- Technical notes: This task reduces regressions in nearly every later gameplay module.
- In scope: Test fixtures and assertions for player state.
- Out of scope: End-to-end browser tests.