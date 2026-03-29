# 14 Quests, Missions, and Events

## Current State

- `mission.php`
- `hunters.php`
- `thieves.php`
- `chronicle.php`
- `maze.php`
- `grid.php` (labyrinth exploration and quest encounters)
- `quests/*.php` (quest1.php through quest10.php — scripted quest content)
- `class/quests_class.php` (quest state management)
- `includes/revent.php` (random event generation)
- `includes/steal.php` (thief steal mechanics, shared with economy)
- `mactions`, `missions`, `questaction`, and related tables

## Why This Module Exists

Port the scripted and semi-random narrative systems without flattening them into one generic engine prematurely.

## Target Rust Shape

- `crates/domain/src/quest/state.rs` — Quest and mission state inventory, table ownership documentation.
- `crates/domain/src/quest/mission.rs` — Mission graph models, active mission state, thief mission types, reward calculation.
- `crates/domain/src/quest/quest_action.rs` — Quest action persistence, branching decisions, quest status.
- `crates/domain/src/quest/maze.rs` — Maze and labyrinth exploration state, validation guards.
- `crates/domain/src/quest/events.rs` — Random event state machine, hunter quest availability.
- `crates/data/src/quest.rs` — Quest/mission/event queries and state persistence.
- `crates/web/src/handlers/quest.rs` — Mission, maze, grid, thieves, hunters, chronicle handlers.

## Module Dependencies

- 05 Auth, Accounts, and Sessions (session state for multi-request quest flows).
- 06 Player State and Progression (player level/class prerequisites for quests).
- 08 Combat, Encounters, and Random Battle (fights triggered within quests and missions).
- 15 Admin, Moderation, and Runtime Operations (scheduled job generation for hunter quests).

## Risks and Notes

- `grid.php` is a 273-line labyrinth exploration page that was missing from the original plan. It interacts with `questaction` table and has its own encounter/treasure mechanics.
- The 10 scripted quest files (`quest1.php`–`quest10.php`) each contain unique branching logic. They cannot be collapsed into a single generic handler.
- Mission/quest state currently lives partially in PHP sessions and partially in DB tables. The Rust implementation must handle both persistence points.
- Random events depend on page-triggered generation, not a real scheduler; this couples to module 15's reset work.

## Tasks

### MP-14-01: Inventory quest and mission state models ✅

- Description: Map all quest and mission tables, session state, and route entry points to a typed Rust state model.
- Estimate: 1.5h
- Depends on: MP-02-01, MP-05-06.
- Functional acceptance criteria:
  - Each quest or mission system has an identified persistence model.
  - Session-only fields are listed separately from persisted fields.
  - The ownership of each table is assigned to this module.
- Technical notes: Do not assume all quests can share one storage format.
- In scope: State inventory and target model.
- Out of scope: Full runtime implementation.
- Status: **Complete**. Created `quest/state.rs` (table ownership inventory), `quest/mission.rs` (mission graph + active state + thief missions + rewards), `quest/quest_action.rs` (quest progress + branching), `quest/maze.rs` (labyrinth exploration), `quest/events.rs` (random event state machine + hunter quests). 50 new tests.

### MP-14-02: Port the generic mission graph loader ✅

- Description: Implement the reusable loader for mission room text, exits, items, mobs, and progression from the `missions` and `mactions` tables.
- Estimate: 2h
- Depends on: MP-14-01, MP-02-04.
- Functional acceptance criteria:
  - The Rust app can load mission graph/state data from PostgreSQL.
  - Exit and room traversal is represented explicitly.
  - Handlers can resume mission state across requests.
- Technical notes: Keep the data shape close to how the authored content is stored.
- In scope: Mission graph load and resume logic.
- Out of scope: Fight resolution inside missions.
- Status: **Complete**. Created `quest/mission_loader.rs` (room template parsing, class placeholder expansion, chance rolling, room generation, serialization roundtrips) and `data/queries/mission.rs` (mission room/active mission/chronicle SQL queries with `RoomAdvance` struct). 40 new tests.

### MP-14-03: Port quest action persistence and branching ✅

- Description: Rebuild the storage and branching logic for quest progression currently handled in PHP scripts and `quests_class.php`.
- Estimate: 1.5h
- Depends on: MP-14-01, MP-14-02.
- Functional acceptance criteria:
  - Quest progress can be advanced, checked, and resumed in Rust.
  - Branching decisions are expressed as typed transitions.
  - State changes are persisted consistently.
- Technical notes: Keep PHP-script-specific quirks documented so they can be revalidated after porting.
- In scope: Quest progression and branching.
- Out of scope: Content authoring tools.
- Status: **Complete**. Created `migrations/20250325000021_quest_tables.sql` (questaction + quests tables with indexes), `crates/data/src/queries/quest.rs` (full CRUD: find/insert/update/delete quest actions, quest content queries, answer checking), extended `crates/domain/src/quest/quest_action.rs` with: typed `QuestTransition` enum (BoxChoice/Answer/Advance/Resign), `QuestReward` + `XpAllotment` for XP distribution with ceiling division, `resolve_box_choice()` for box-style branching, `check_answer()` for case-insensitive text answers, `validate_advance()` guard, `has_active_quest()` predicate, `substitute_city_names()` for narrative text placeholders. 25 tests covering all new logic.

### MP-14-04: Port maze, labyrinth, thieves, and chronicle-style mission flows

- Description: Rebuild the larger session-heavy mission pages (`maze.php`, `grid.php`, `thieves.php`, `chronicle.php`) that generate temporary state and interact with map-like structures. `grid.php` is a 273-line labyrinth exploration page with its own encounter/treasure mechanics tied to the `questaction` table.
- Estimate: 2h
- Depends on: MP-14-02, MP-14-03, MP-08-03.
- Functional acceptance criteria:
  - Players can start, resume, and finish these mission flows in Rust.
  - Labyrinth exploration (`grid.php`) encounter and treasure mechanics work correctly.
  - Temporary mission state no longer depends on raw PHP session arrays.
  - Reward and failure outcomes are persisted correctly.
- Technical notes: Keep each mission family behind its own service so one bug does not destabilize all quest content. `grid.php` reads from `questaction` and generates random encounters and map discoveries.
- In scope: Large mission flows including maze, labyrinth grid, thieves, and chronicle, plus their state handling.
- Out of scope: Admin authoring interfaces.
- Status: **Complete**. Created `crates/web/src/handlers/quest.rs` (~900 lines) with handlers for labyrinth exploration (labyrinth_show, labyrinth_explore), chronicle mission catalog (chronicle_show, chronicle_detail, chronicle_start), active mission navigation (mission_advance), and Ardulith maze (maze_show, maze_explore). Pre-generates RNG rolls to avoid holding ThreadRng across .await boundaries. Added routes via `quest_routes()` in world.rs. Created 6 MiniJinja templates (labyrinth, labyrinth_result, chronicle_detail, mission, maze, maze_result). Updated existing chronicle.html template. Thieves den deferred to MP-14-06 scope refinement (needs combat integration from MP-08-03).

### MP-14-05: Port random event and hunter quest generation ✅

- Description: Rebuild random events, daily/periodic hunter tasks, and related state mutation logic.
- Estimate: 1.5h
- Depends on: MP-14-01, MP-15-05.
- Functional acceptance criteria:
  - ✅ Random events can be generated and resumed from Rust-managed data.
  - ✅ Hunter quest state is created using the scheduler/reset infrastructure, not page-load side effects.
  - ✅ Event outcomes integrate with combat and rewards.
- Technical notes: Separate event generation from event rendering so future scheduling changes stay local.
- In scope: Event generation and persistence.
- Out of scope: Full scheduling framework.
- Implementation notes:
  - Added `ItemDisposed` (state 4) to `EventPhase` enum — covers quest item sold/disposed punishment.
  - Added domain functions: `generate_event()`, `event_gen_db_state()`, `process_delivery_response()`, `process_beggar_response()`, `beggar_outcome_db_state()`, `reset_resolution()`, `PunishmentKind`.
  - Created `crates/data/src/queries/event.rs` with revent CRUD + hunter quest catalog queries.
  - Extended `crates/data/src/jobs.rs`: `generate_hunter_quests()` runs during daily reset, generates random F/I/L/B/P quests for both cities; `process_random_events()` now handles states 2/3/4/7 (delivery expired, delivery reward, item sold punishment, beggar reward/veteran recruitment).
  - Created migration `20250325000022_revent_table.sql`.
  - 14 new domain tests for event generation, interaction, and reset resolution.

### MP-14-06: Add quest and mission parity fixtures ✅

- Description: Capture representative quest states and mission transitions from the PHP version and codify them as tests.
- Estimate: 1.5h
- Depends on: MP-14-02, MP-14-03, MP-14-04.
- Functional acceptance criteria:
  - Tests cover resume, success, failure, and reward application cases.
  - Fixture data is documented by quest or mission type.
  - The module can be regression-tested without manual playthroughs.
- Technical notes: Focus first on the most stateful quest paths.
- In scope: Quest/mission fixtures and tests.
- Out of scope: Full storyline coverage.
- Implementation notes:
  - 55 new tests across 9 documented sections in `parity_fixtures.rs`.
  - Extracted `process_labyrinth_steps` and `LabyrinthRoll` to `maze.rs` for deterministic testing.
  - Sections: quest1 lifecycle (7 tests), mission traversal (5 tests), thief missions (5 tests), labyrinth exploration (9 tests), maze entry (1 test), events (3 tests), roll_options (3 tests), room prefixes (3 tests), end-to-end simulations (3 tests).
  - Type-filter lossiness in exit serialization documented (by design — filter applied before storage).