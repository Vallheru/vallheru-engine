//! Top-level state inventory for all quest, mission, and event systems.
//!
//! This module documents which tables, session fields, and route entry points
//! belong to the quest/mission subsystem without implementing full runtime
//! logic. Later tasks (MP-14-02 through MP-14-06) implement the actual
//! loaders, handlers, and persistence.
//!
//! # Table ownership
//!
//! | Table        | Owner module        | Description                               |
//! |-------------|---------------------|-------------------------------------------|
//! | `questaction`| `quest::quest`      | Per-player quest progress (player, quest, action) |
//! | `quests`     | `quest::quest`      | Quest content: branching text keyed by `(qid, location, name)` |
//! | `missions`   | `quest::mission`    | Mission room graph: text, exits, mobs, items, chances |
//! | `mactions`   | `quest::mission`    | Active mission session (one row per player in a mission) |
//! | `missions2`  | `quest::mission`    | Chronicle mission catalog with chapter gating |
//! | `revent`     | `quest::events`     | Random event state per player |
//! | `events`     | `quest::events`     | Event text templates |
//!
//! # Session-only state
//!
//! ## Mission session (`maction`)
//!
//! During an active mission, PHP stores a session array that mirrors and
//! extends the `mactions` row. The following fields are session-only and
//! reconstructed on session start from the DB row:
//!
//! - `exits: Vec<String>` — parsed from semicolon-delimited DB field
//! - `mobs: Vec<String>` — parsed from semicolon-delimited DB field
//! - `items: Vec<String>` — parsed from semicolon-delimited DB field
//! - `moreinfo: Vec<String>` — parsed from semicolon-delimited DB field
//!
//! The DB row is the source of truth; the session is a cache.
//!
//! ## Thief mission generation
//!
//! When a thief picks from the job board, PHP stores three session arrays
//! (`mission`, `mtype`, `reward`) indexing the three offered jobs. These
//! are consumed once on confirmation and can be modeled as a transient
//! struct rather than persisted state.
//!
//! ## Combat within missions
//!
//! `$_SESSION['enemy']` and `$_SESSION['razy']` hold fight state during
//! mission combat. These map to the existing combat session model.
//!
//! # Route entry points
//!
//! | PHP file       | System           | Rust target               |
//! |---------------|------------------|---------------------------|
//! | `grid.php`     | Labyrinth explore + quest encounters | `quest::maze` |
//! | `maze.php`     | Ardulith labyrinth + combat          | `quest::maze` |
//! | `mission.php`  | Active mission room navigation       | `quest::mission` |
//! | `chronicle.php`| Chronicle mission catalog + start    | `quest::mission` |
//! | `thieves.php`  | Thief den: shop, monuments, missions | `quest::mission` (thief variant) |
//! | `hunters.php`  | Hunter guild: bestiary, quests       | `quest::events` |
//! | `quests/*.php` | 10 scripted quests (unique branching) | `quest::quest` |
//! | `includes/revent.php` | Random city events            | `quest::events` |
