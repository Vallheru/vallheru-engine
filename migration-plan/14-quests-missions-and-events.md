# 14 Quests, Missions, and Events

## Source Surface

- `mission.php`
- `hunters.php`
- `thieves.php`
- `chronicle.php`
- `maze.php`
- `quests/*.php`
- `class/quests_class.php`
- `includes/revent.php`
- `mactions`, `missions`, `questaction`, and related tables

## Goal

Port the scripted and semi-random narrative systems without flattening them into one generic engine prematurely.

## Tasks

### MP-14-01: Inventory quest and mission state models

- Description: Map all quest and mission tables, session state, and route entry points to a typed Rust state model.
- Estimated time: 1.5h
- Dependencies: MP-02-01, MP-05-06.
- Acceptance criteria:
  - Each quest or mission system has an identified persistence model.
  - Session-only fields are listed separately from persisted fields.
  - The ownership of each table is assigned to this module.
- Technical notes: Do not assume all quests can share one storage format.
- In scope: State inventory and target model.
- Out of scope: Full runtime implementation.

### MP-14-02: Port the generic mission graph loader

- Description: Implement the reusable loader for mission room text, exits, items, mobs, and progression from the `missions` and `mactions` tables.
- Estimated time: 2h
- Dependencies: MP-14-01, MP-02-04.
- Acceptance criteria:
  - The Rust app can load mission graph/state data from PostgreSQL.
  - Exit and room traversal is represented explicitly.
  - Handlers can resume mission state across requests.
- Technical notes: Keep the data shape close to how the authored content is stored.
- In scope: Mission graph load and resume logic.
- Out of scope: Fight resolution inside missions.

### MP-14-03: Port quest action persistence and branching

- Description: Rebuild the storage and branching logic for quest progression currently handled in PHP scripts and `quests_class.php`.
- Estimated time: 1.5h
- Dependencies: MP-14-01, MP-14-02.
- Acceptance criteria:
  - Quest progress can be advanced, checked, and resumed in Rust.
  - Branching decisions are expressed as typed transitions.
  - State changes are persisted consistently.
- Technical notes: Keep PHP-script-specific quirks documented so they can be revalidated after porting.
- In scope: Quest progression and branching.
- Out of scope: Content authoring tools.

### MP-14-04: Port maze, thieves, and chronicle-style mission flows

- Description: Rebuild the larger session-heavy mission pages that generate temporary state and interact with map-like structures.
- Estimated time: 2h
- Dependencies: MP-14-02, MP-14-03, MP-08-03.
- Acceptance criteria:
  - Players can start, resume, and finish these mission flows in Rust.
  - Temporary mission state no longer depends on raw PHP session arrays.
  - Reward and failure outcomes are persisted correctly.
- Technical notes: Keep each mission family behind its own service so one bug does not destabilize all quest content.
- In scope: Large mission flows and their state handling.
- Out of scope: Admin authoring interfaces.

### MP-14-05: Port random event and hunter quest generation

- Description: Rebuild random events, daily/periodic hunter tasks, and related state mutation logic.
- Estimated time: 1.5h
- Dependencies: MP-14-01, MP-15-05.
- Acceptance criteria:
  - Random events can be generated and resumed from Rust-managed data.
  - Hunter quest state is created using the scheduler/reset infrastructure, not page-load side effects.
  - Event outcomes integrate with combat and rewards.
- Technical notes: Separate event generation from event rendering so future scheduling changes stay local.
- In scope: Event generation and persistence.
- Out of scope: Full scheduling framework.

### MP-14-06: Add quest and mission parity fixtures

- Description: Capture representative quest states and mission transitions from the PHP version and codify them as tests.
- Estimated time: 1.5h
- Dependencies: MP-14-02, MP-14-03, MP-14-04.
- Acceptance criteria:
  - Tests cover resume, success, failure, and reward application cases.
  - Fixture data is documented by quest or mission type.
  - The module can be regression-tested without manual playthroughs.
- Technical notes: Focus first on the most stateful quest paths.
- In scope: Quest/mission fixtures and tests.
- Out of scope: Full storyline coverage.