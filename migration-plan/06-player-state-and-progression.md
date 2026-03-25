# 06 Player State and Progression

## Source Surface

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

## Goal

Define the central Rust player model and port all derived calculations and progression mechanics that other modules depend on.

## Tasks

### MP-06-01: Define the Rust player aggregate

- Description: Design the core player domain types, separating persisted fields, derived fields, and transient request/session state.
- Estimated time: 1.5h
- Dependencies: MP-02-03.
- Acceptance criteria:
  - The player aggregate has a stable Rust API for other modules.
  - Persisted and computed values are not mixed into one unstructured blob.
  - Session-only state is identified explicitly.
- Technical notes: Keep domain types close to how gameplay rules are expressed, not how the old table happened to store them.
- In scope: Player domain model.
- Out of scope: Full repository implementation.

### MP-06-02: Port legacy field parsing and serialization

- Description: Implement import/parity logic for legacy settings, stats, skills, and bonuses while the new schema is phased in.
- Estimated time: 1.5h
- Dependencies: MP-06-01, MP-02-03.
- Acceptance criteria:
  - Imported legacy player rows can be turned into Rust player structs.
  - Round-trip conversion rules exist where temporary compatibility is needed.
  - Parsing failures are logged with enough context to fix bad data.
- Technical notes: Keep legacy parsing isolated so it can be deleted after full cutover.
- In scope: Legacy compatibility parsing.
- Out of scope: Final normalized persistence format.

### MP-06-03: Port derived stat, mana, and bonus calculations

- Description: Recreate the calculations currently performed in `player_class.php`, including equipment, blessings, race/class, and temporary bonus effects.
- Estimated time: 2h
- Dependencies: MP-06-01, MP-06-02, MP-09-02.
- Acceptance criteria:
  - Derived stats match legacy behavior for representative players.
  - Mana, health-related caps, and bonus application order are documented and tested.
  - The web layer can request a fully calculated player snapshot without mutating storage.
- Technical notes: Separate pure calculations from repository reads so parity tests are straightforward.
- In scope: Calculation engine.
- Out of scope: Battle resolution.

### MP-06-04: Port AP, training, class, race, and deity mutations

- Description: Migrate the routes and services that change player progression state through AP spending, training, and alignment/class selection.
- Estimated time: 1.5h
- Dependencies: MP-06-03.
- Acceptance criteria:
  - AP spending and training enforce current prerequisites.
  - Race, class, and deity choices update the player model correctly.
  - Changes are persisted transactionally.
- Technical notes: Keep mutation logic in domain services, not handlers.
- In scope: Progression mutations.
- Out of scope: Item or combat side effects outside direct progression changes.

### MP-06-05: Port player-facing read models

- Description: Rebuild profile pages, player inspection, hall-of-fame views, and stats screens.
- Estimated time: 1.5h
- Dependencies: MP-04-05, MP-06-03.
- Acceptance criteria:
  - Public and authenticated profile/stat pages render from PostgreSQL-backed Rust view models.
  - Hall-of-fame ordering matches current rules.
  - Page-specific formatting is isolated from domain logic.
- Technical notes: These are good early read-only cutover candidates.
- In scope: Read models and pages for player status.
- Out of scope: Account settings edits.

### MP-06-06: Add parity fixtures for player calculations

- Description: Capture representative player records and expected derived values from PHP, then codify them as Rust tests.
- Estimated time: 2h
- Dependencies: MP-06-03.
- Acceptance criteria:
  - Fixtures cover at least class, race, blessing, and equipment bonus combinations.
  - Tests fail on calculation drift.
  - Fixture sources are documented so they can be updated safely.
- Technical notes: This task reduces regressions in nearly every later gameplay module.
- In scope: Test fixtures and assertions for player state.
- Out of scope: End-to-end browser tests.