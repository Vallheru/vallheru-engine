# Vallheru PHP to Rust Migration Plan

## Current Application Summary

This repository is a legacy browser RPG implemented as a classic multi-page PHP application.

- Top-level web surface: 110 PHP entry points in the repository root.
- Shared bootstrap: `includes/head.php` loads config, session handling, Smarty, language files, and the `Player` object for almost every authenticated page.
- Templates: 228 Smarty templates across `templates/` and `templates/layout1/`.
- Data layer: inline ADOdb SQL spread across controllers, includes, and classes.
- Schema: 112 MySQL tables in `install/db/mysql.sql`, using MyISAM-era conventions and almost no enforced relational constraints.
- Runtime model: synchronous request/response PHP, file-based sessions, page-triggered resets instead of a true scheduler, and writable filesystem directories for cache, avatars, and compiled templates.
- Domain shape: player/account systems, combat, map/travel, inventory/equipment, markets, professions, tribes/teams/outposts, quests/missions/random events, chat/forums/mail, publishing/news, moderation/admin, and reset-era operations.

The most important architectural findings from the code are:

- Request routing is file-per-page, not centrally declared.
- Business rules live in page scripts, `includes/*.php`, `class/player_class.php`, and even template assumptions.
- Player state is partly relational and partly serialized into string columns such as `players.settings`, `players.stats`, `players.skills`, and `players.bonuses`.
- Security-sensitive behavior is legacy: MD5 passwords, direct SQL string interpolation, ad hoc authorization checks, and writable runtime directories.
- Some subsystems are tightly coupled through session state, especially chat, battle loops, and mission/quest progress.

## Scope of Migration

This plan covers a full rewrite of the PHP application into Rust, preserving current gameplay and route behavior before any redesign.

**In scope:**
- All 110 PHP entry points and their associated business logic.
- All 228 Smarty templates converted to MiniJinja.
- The full MySQL schema (112 tables) translated to PostgreSQL.
- The 5 PHP classes, ~30 include files, and ~100 language files.
- 12 page-specific JS files and 7 CSS theme files, embedded into the binary.
- Operational tooling: installer, era reset, data import, reconciliation.
- Docker-based deployment adapted for the Rust binary.

**Out of scope (deferred to post-migration):**
- UI redesign or frontend framework migration.
- Additional language/locale support beyond current Polish content.
- Performance optimization beyond parity.
- New gameplay features.
- CDN integration or external service dependencies.

## Target Rust Architecture

The recommended target is one Rust workspace that still produces a simple final deployment unit.

### Workspace Layout

- `crates/server`
  - Axum bootstrap, router composition, middleware, error responses, health endpoints, asset serving, and CLI subcommands.
- `crates/web`
  - HTTP handlers, request parsing, response view models, MiniJinja rendering, route helpers, and page-level composition.
- `crates/domain`
  - Pure game rules and state transitions for player progression, combat, inventory, economy, quests, tribe systems, and reset logic.
- `crates/data`
  - PostgreSQL access using explicit SQL, row mapping, transaction boundaries, import/reconciliation jobs, and repository modules.

This is intentionally not a microservice split. The application is currently monolithic and should remain one deployable service until the behavior is stable in Rust.

### Runtime Shape

- One Axum binary for web serving and operational subcommands.
- PostgreSQL as the only required external service after cutover.
- MiniJinja for server-rendered pages and reusable partials.
- Static files, templates, and language/catalog content embedded into the binary.
- Optional Nginx only during strangler-style migration; not required after final cutover.

### Module Boundaries

These are the recommended Rust modules, mapped from the real PHP codebase:

1. Platform foundations
2. Database and PostgreSQL migration
3. HTTP routing and middleware
4. Rendering, assets, and localization
5. Authentication, accounts, and sessions
6. Player state and progression
7. World map, travel, and locations
8. Combat, encounters, and battle state
9. Items, inventory, and equipment
10. Economy, markets, and banking
11. Crafting, gathering, and workshops
12. Social systems, chat, mail, and content
13. Guilds, tribes, teams, and outposts
14. Quests, missions, and events
15. Admin, moderation, and runtime operations
16. Testing, parity, and cutover

## Migration Principles

1. **Parity before polish.** The first milestone is behavioral equivalence with the PHP version, not improvement. Preserve current gameplay, routes, and user-visible behavior.
2. **Incremental strangler migration.** Individual routes move to Rust behind Nginx while PHP serves unmigrated pages. No big-bang cutover.
3. **Explicit SQL, no ORM.** All database access uses `sqlx` with hand-written SQL. Row structs stay separate from domain types.
4. **Embedded everything.** Templates, CSS, JS, images, and language catalogs are compiled into the binary. No writable asset directories at runtime.
5. **One binary, one dependency.** The final deployment is one Rust binary plus PostgreSQL. No Redis, no background workers, no separate CLI tools.
6. **Small, testable tasks.** Every task in this plan is scoped to roughly 2 hours or less and has functional acceptance criteria.
7. **Domain logic in domain crates.** Business rules live in `crates/domain`, not in handlers or templates. Handlers are thin.
8. **Boring tools only.** Prefer well-maintained, widely-used Rust crates. Avoid niche or experimental dependencies.

## Recommended Tools and When to Introduce Them

| Stage | Tool | Why it fits this repo |
|---|---|---|
| Phase 1 | `axum` | Required target stack, small surface area, easy route composition, and good middleware support for a monolithic web app. |
| Phase 1 | `tracing` + `tracing-subscriber` | Replace ad hoc debug output and bugtrack-style runtime visibility with structured logs without adding infrastructure. |
| Phase 1 | `clap` | Keep installer/import/reset jobs in the same binary instead of introducing separate scripts or supervisors. |
| Phase 1 | `serde` + `toml` + environment variables | Typed config without a heavy framework; explicit, boring, and easy for both local and production startup. |
| Phase 1 | `include_dir` | Simple compile-time embedding for templates, CSS, JS, images, and language catalogs; avoids runtime writable asset directories. |
| Phase 2 | `sqlx` | Explicit SQL, compile-time query checking, PostgreSQL-first, and no ORM behavior leakage. |
| Phase 2 | `sqlx migrate` | Keeps schema evolution near the SQL layer and avoids adding a second migration tool. |
| Phase 2 | `argon2` | Safe password hashing with a straightforward compatibility bridge from legacy MD5-on-login. |
| Phase 2 | `tower-sessions` with PostgreSQL store | Preserves server-side session semantics without adding Redis, matching the current app's reliance on mutable session state. |
| Phase 4 | `lettre` | Needed only when registration, activation, and password reset email flows are ported. |

## Migration Phases

### Phase 1: Foundations and Data Shape

- Files: 01-04
- Estimated effort: 28 hours
- Goal: establish the Rust workspace, HTTP skeleton, PostgreSQL schema strategy, and embedded rendering stack without yet porting risky game behavior.

### Phase 2: Identity and Core Player State

- Files: 05-06
- Estimated effort: 20 hours
- Goal: move login/session/account behavior and player state calculations into reliable Rust services.

### Phase 3: Core Gameplay Vertical Slices

- Files: 07-11
- Estimated effort: 55 hours
- Goal: port the game loops that players touch most often: movement, combat, items, markets, and professions.

### Phase 4: Community, Clan, and Operations Features

- Files: 12-15
- Estimated effort: 43 hours
- Goal: port social systems, multiplayer coordination, quests/events, admin tools, and scheduled/reset behavior.

### Phase 5: Verification and Cutover

- Files: 16
- Estimated effort: 18 hours
- Goal: prove behavior parity, complete route-by-route switch-over, and remove PHP dependencies.

## Ordered Module Index

| Order | File | Effort | Coverage |
|---|---|---:|---|
| 01 | [01-platform-foundations.md](./01-platform-foundations.md) | 7h | workspace, config, startup, errors, logging, CLI shell |
| 02 | [02-database-and-postgresql.md](./02-database-and-postgresql.md) | 8h | schema translation, imports, repositories, legacy field normalization |
| 03 | [03-http-routing-and-middleware.md](./03-http-routing-and-middleware.md) | 6h | route map, middleware, guards, fallback strategy |
| 04 | [04-rendering-assets-and-localization.md](./04-rendering-assets-and-localization.md) | 7h | MiniJinja, themes, asset embedding, i18n |
| 05 | [05-auth-accounts-and-sessions.md](./05-auth-accounts-and-sessions.md) | 10h | login, logout, registration, activation, password reset, account pages |
| 06 | [06-player-state-and-progression.md](./06-player-state-and-progression.md) | 10h | player aggregate, stats, skills, AP, class/race/deity, hall of fame |
| 07 | [07-world-map-travel-and-locations.md](./07-world-map-travel-and-locations.md) | 11h | city, map, travel, portals, exploration-adjacent locations |
| 08 | [08-combat-encounters-and-random-battle.md](./08-combat-encounters-and-random-battle.md) | 12h | battle loops, monsters, PvE/PvP, reward side effects |
| 09 | [09-items-inventory-and-equipment.md](./09-items-inventory-and-equipment.md) | 10h | inventory, equipment, bonuses, warehouse, spells/items |
| 10 | [10-economy-markets-and-banking.md](./10-economy-markets-and-banking.md) | 10h | banking, currencies, all market variants, offer lifecycle |
| 11 | [11-crafting-gathering-and-workshops.md](./11-crafting-gathering-and-workshops.md) | 12h | smithing, alchemy, mining, lumber, smelting, jeweller, core pets |
| 12 | [12-social-chat-mail-and-content.md](./12-social-chat-mail-and-content.md) | 10h | chat, rooms, mail, forums, news, publishing, notes, library |
| 13 | [13-guilds-tribes-teams-and-outposts.md](./13-guilds-tribes-teams-and-outposts.md) | 11h | teams, tribes, permissions, storages, outposts, tribe forums |
| 14 | [14-quests-missions-and-events.md](./14-quests-missions-and-events.md) | 10h | quest scripts, missions, random events, save/resume state |
| 15 | [15-admin-moderation-and-runtime-operations.md](./15-admin-moderation-and-runtime-operations.md) | 12h | staff/admin, moderation, bugtrack, resets, installer, era tools |
| 16 | [16-testing-parity-and-cutover.md](./16-testing-parity-and-cutover.md) | 18h | golden-master checks, rollout, rollback, final packaging |

## Supporting Documents

| File | Purpose |
|---|---|
| [table-ownership-map.md](./table-ownership-map.md) | Maps all 112 legacy MySQL tables to owning migration modules |
| [player-field-normalization.md](./player-field-normalization.md) | Normalization strategy for serialized player columns |
| [route-manifest.md](./route-manifest.md) | Maps all 110 PHP entry points to planned Axum routes |
| [cutover-rules.md](./cutover-rules.md) | Route-by-route cutover groups, activation rules, and rollback procedures |
| [reconciliation-procedures.md](./reconciliation-procedures.md) | Data reconciliation checklists, rollback steps, and ownership transitions |
| [production-runbook.md](./production-runbook.md) | Production startup, scheduled jobs, health checks, and operator commands |
| [php-retirement-checklist.md](./php-retirement-checklist.md) | Phased PHP retirement: cutover → soak → cold standby → removal |
| [problems-and-tech-debt.md](./problems-and-tech-debt.md) | Technical debt and problem register |

## Totals

- Total task files: 16
- Total tasks: 102
- Total estimated effort: 164 hours

**Effort per phase:**

| Phase | Files | Effort |
|---|---|---:|
| Phase 1: Foundations and Data Shape | 01–04 | 28h |
| Phase 2: Identity and Core Player State | 05–06 | 20h |
| Phase 3: Core Gameplay Vertical Slices | 07–11 | 55h |
| Phase 4: Community, Clan, and Operations | 12–15 | 43h |
| Phase 5: Verification and Cutover | 16 | 18h |
| **Total** | **01–16** | **164h** |

## Assumptions

- The rewrite preserves current gameplay and route behavior before any redesign.
- The first Rust milestone prioritizes parity over UI modernization.
- PostgreSQL becomes the target schema early, but PHP may remain live behind a reverse proxy while individual routes move.
- During the incremental phase, read-only features can use synchronized PostgreSQL data before write-heavy features fully cut over.
- Existing Polish strings and current theme assets are the minimum content to preserve; additional localization cleanup can happen later.
- File uploads are limited to avatars and similar user assets; all shipped static assets should move into the binary.
- The empty checked-in `includes/config.php` means environment-specific config is generated outside version control today and should be replaced with explicit Rust config loading.

## Risks and Blockers

- Hidden rules in controllers and includes: many formulas and guards are embedded directly in page scripts.
- Serialized player columns: stats, skills, settings, and bonuses are not relational and need careful mapping.
- Session-coupled flows: chat tabs, battle state, and mission progress depend on PHP session behavior.
- MyISAM-era schema: missing foreign keys means data cleanup rules must be discovered empirically.
- Page-triggered resets: current behavior depends on a player visiting the game, not on an actual scheduler.
- Security debt: MD5 passwords and interpolated SQL complicate compatibility and require staged hardening.
- Operational side effects: writable template caches and avatar directories need a different runtime model in Rust.

## Implementation Notes

### Asset Embedding

All shipped assets are embedded at compile time.

- Embed MiniJinja templates, CSS themes, JS files, images, and language catalogs with `include_dir`.
- Build a small in-memory asset registry at startup with content type, cache headers, and a content hash.
- Serve assets directly from the Rust process during the final state.
- Keep only user-generated uploads (avatars) in external storage; do not treat shipped assets as files on disk.

### PostgreSQL Access Without an ORM

The data access strategy stays explicit.

- Put SQL in the `data` crate, grouped by module, not by generic repository abstractions.
- Use `sqlx::query!` and `sqlx::query_as!` for compile-time checked SQL wherever practical.
- Keep row structs separate from domain structs so legacy schema compromises do not leak into business logic.
- Use explicit transactions in service methods for workflows such as purchases, combat reward application, and tribe storage changes.
- Normalize obviously harmful structures during migration, especially serialized player fields, while retaining compatibility views or import helpers as needed.

### Keeping Startup and Deployment Simple

The final application is operationally simpler than the PHP stack.

- Produce one binary that serves HTTP and also exposes subcommands for migration, import, reset, reconciliation, and smoke checks.
- Require only PostgreSQL as an external dependency after cutover.
- During migration, place Axum behind Nginx and fall back to PHP-FPM for unmigrated routes.
- After cutover, remove PHP-FPM and serve the Rust binary directly behind systemd or a single container.
- Avoid writable template caches and avoid runtime-generated config files.

### Testing Strategy During Migration

- **Unit tests** for pure domain functions: combat formulas, stat calculations, economic invariants. Written alongside each module during porting.
- **Parity fixtures** captured from the PHP version: representative player records, quest states, and market transactions. Used to verify calculation equivalence.
- **Integration tests** per module: test Axum handlers against a real PostgreSQL database with seeded data.
- **Golden-master tests** for high-risk pages: capture PHP HTML output, compare key data points against Rust output.
- **Reconciliation checks** before each cutover batch: compare row counts and aggregates between MySQL and PostgreSQL.
- No browser automation during the migration phase. Visual/UI testing is deferred to post-cutover.

### Incremental Rollout and Compatibility Strategy

- During migration, Nginx routes requests to either Rust or PHP based on a per-route configuration flag.
- Read-only pages can cut over first with minimal risk (stats, hall of fame, library, news).
- Write-heavy pages (markets, combat, crafting) require both data migration and write-path verification before cutover.
- Workflows that span multiple routes (registration → activation → login) must be cut over as a group.
- PHP sessions and Rust sessions may coexist during the transition. Session data does not need to be shared; players re-authenticate when switching between stacks.
- Rollback plan: flip the route flag back to PHP. No data rollback is needed for read-only routes. For write routes, reconciliation checks must pass before the flag is flipped permanently.