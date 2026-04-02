# Vallheru PHP → Rust Migration Plan — COMPLETE

> **Status**: All 102 migration tasks and 59 tech debt items have been completed.
> PHP source files have been removed from the repository. The application is now
> a Rust-only system.

## Application Summary

Vallheru Engine is a browser RPG implemented as a server-rendered Rust web application.

- **Runtime**: Single Axum binary serving all routes, with PostgreSQL as the only external dependency.
- **Templates**: ~192 MiniJinja templates in `templates_jinja/` (including theme variants), compiled into the binary.
- **Data layer**: Explicit SQL via `sqlx` in `crates/data/`, with PostgreSQL migrations in `migrations/`.
- **Schema**: PostgreSQL with enforced foreign keys, normalized player fields, and proper indexes.
- **Auth**: Argon2id password hashing. No legacy MD5 support.
- **Assets**: CSS, JS, images, i18n catalogs, and seed data embedded into the binary at compile time.
- **Domain shape**: player/account systems, combat, map/travel, inventory/equipment, markets, professions, tribes/teams/outposts, quests/missions/random events, chat/forums/mail, publishing/news, moderation/admin, and reset-era operations.
- **CLI**: Subcommands for `serve`, `migrate`, `import`, `job`, `bootstrap`, and `reset-era`.

## Migration Scope (Completed)

All items below have been implemented.

- All 110 PHP entry points rewritten as Axum handlers.
- All 228 Smarty templates converted to MiniJinja.
- The full MySQL schema (112 tables) translated to PostgreSQL.
- The 5 PHP classes, ~30 include files, and ~100 language files replaced by Rust domain/data crates.
- 12 page-specific JS files and 7 CSS theme files embedded into the binary.
- Operational tooling: era reset, data import, bootstrap, scheduled jobs.
- Docker-based deployment adapted for the Rust binary.
- PHP source files, legacy Docker configs, and backward-compatibility code removed.

## Rust Architecture

The application is one Rust workspace producing a single deployable binary.

### Workspace Layout

- `crates/server`
  - Axum bootstrap, router composition, middleware, error responses, health endpoints, asset serving, and CLI subcommands.
- `crates/web`
  - HTTP handlers, request parsing, response view models, MiniJinja rendering, route helpers, and page-level composition.
- `crates/domain`
  - Pure game rules and state transitions for player progression, combat, inventory, economy, quests, tribe systems, and reset logic.
- `crates/data`
  - PostgreSQL access using explicit SQL, row mapping, transaction boundaries, import jobs, and repository modules.

This is intentionally not a microservice split. The application is monolithic and deploys as one service.

### Runtime Shape

- One Axum binary for web serving and operational subcommands.
- PostgreSQL as the only external service.
- MiniJinja for server-rendered pages and reusable partials.
- Static files, templates, and language/catalog content embedded into the binary.
- User-generated uploads (avatars) served from external storage.

### Module Boundaries

The Rust modules are organized as follows:

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

## Design Principles

1. **Explicit SQL, no ORM.** All database access uses `sqlx` with hand-written SQL. Row structs stay separate from domain types.
2. **Embedded everything.** Templates, CSS, JS, images, and language catalogs are compiled into the binary. No writable asset directories at runtime (except avatar uploads).
3. **One binary, one dependency.** The deployment is one Rust binary plus PostgreSQL. No Redis, no background workers, no separate CLI tools.
4. **Domain logic in domain crates.** Business rules live in `crates/domain`, not in handlers or templates. Handlers are thin.
5. **Boring tools only.** Well-maintained, widely-used Rust crates. No niche or experimental dependencies.

## Key Dependencies

| Crate | Purpose |
|---|---|
| `axum` | HTTP framework, routing, middleware, extractors |
| `tracing` + `tracing-subscriber` | Structured logging |
| `clap` | CLI subcommands (serve, migrate, import, job, bootstrap, reset-era) |
| `serde` + `toml` | Typed configuration |
| `include_dir` | Compile-time asset embedding |
| `sqlx` | PostgreSQL access with compile-time query checking |
| `argon2` | Password hashing (Argon2id) |
| `tower-sessions` | Server-side sessions with PostgreSQL store |
| `minijinja` | Template rendering |
| `lettre` | Email (registration, activation, password reset) |

## Migration Phases (All Complete)

### Phase 1: Foundations and Data Shape (Complete)

- Files: 01-04
- Goal: Rust workspace, HTTP skeleton, PostgreSQL schema, and embedded rendering stack.

### Phase 2: Identity and Core Player State (Complete)

- Files: 05-06
- Goal: Login/session/account behavior and player state calculations.

### Phase 3: Core Gameplay Vertical Slices (Complete)

- Files: 07-11
- Goal: Movement, combat, items, markets, and professions.

### Phase 4: Community, Clan, and Operations Features (Complete)

- Files: 12-15
- Goal: Social systems, multiplayer coordination, quests/events, admin tools, and scheduled/reset behavior.

### Phase 5: Verification and Cutover (Complete)

- Files: 16
- Goal: Behavior parity verification, route switch-over, PHP removal.

## Module Index

| Order | File | Coverage |
|---|---|---|
| 01 | [01-platform-foundations.md](./01-platform-foundations.md) | workspace, config, startup, errors, logging, CLI shell |
| 02 | [02-database-and-postgresql.md](./02-database-and-postgresql.md) | schema translation, imports, repositories, legacy field normalization |
| 03 | [03-http-routing-and-middleware.md](./03-http-routing-and-middleware.md) | route map, middleware, guards, fallback strategy |
| 04 | [04-rendering-assets-and-localization.md](./04-rendering-assets-and-localization.md) | MiniJinja, themes, asset embedding, i18n |
| 05 | [05-auth-accounts-and-sessions.md](./05-auth-accounts-and-sessions.md) | login, logout, registration, activation, password reset, account pages |
| 06 | [06-player-state-and-progression.md](./06-player-state-and-progression.md) | player aggregate, stats, skills, AP, class/race/deity, hall of fame |
| 07 | [07-world-map-travel-and-locations.md](./07-world-map-travel-and-locations.md) | city, map, travel, portals, exploration-adjacent locations |
| 08 | [08-combat-encounters-and-random-battle.md](./08-combat-encounters-and-random-battle.md) | battle loops, monsters, PvE/PvP, reward side effects |
| 09 | [09-items-inventory-and-equipment.md](./09-items-inventory-and-equipment.md) | inventory, equipment, bonuses, warehouse, spells/items |
| 10 | [10-economy-markets-and-banking.md](./10-economy-markets-and-banking.md) | banking, currencies, all market variants, offer lifecycle |
| 11 | [11-crafting-gathering-and-workshops.md](./11-crafting-gathering-and-workshops.md) | smithing, alchemy, mining, lumber, smelting, jeweller, core pets |
| 12 | [12-social-chat-mail-and-content.md](./12-social-chat-mail-and-content.md) | chat, rooms, mail, forums, news, publishing, notes, library |
| 13 | [13-guilds-tribes-teams-and-outposts.md](./13-guilds-tribes-teams-and-outposts.md) | teams, tribes, permissions, storages, outposts, tribe forums |
| 14 | [14-quests-missions-and-events.md](./14-quests-missions-and-events.md) | quest scripts, missions, random events, save/resume state |
| 15 | [15-admin-moderation-and-runtime-operations.md](./15-admin-moderation-and-runtime-operations.md) | staff/admin, moderation, bugtrack, resets, installer, era tools |
| 16 | [16-testing-parity-and-cutover.md](./16-testing-parity-and-cutover.md) | golden-master checks, rollout, rollback, final packaging |

## Supporting Documents

| File | Purpose |
|---|---|
| [table-ownership-map.md](./table-ownership-map.md) | Maps all 112 legacy MySQL tables to owning migration modules |
| [player-field-normalization.md](./player-field-normalization.md) | Normalization strategy for serialized player columns |
| [route-manifest.md](./route-manifest.md) | Maps all 110 PHP entry points to Axum routes |
| [production-runbook.md](./production-runbook.md) | Production startup, scheduled jobs, health checks, and operator commands |
| [problems-and-tech-debt.md](./problems-and-tech-debt.md) | Technical debt and problem register |
| [task-status.md](./task-status.md) | Per-task completion status for all 102 tasks |

## Totals

- Total task files: 16
- Total tasks: 102 (all complete)
- Total tech debt items: 59 (all resolved)

## Architecture Notes
- The rewrite preserves current gameplay and route behavior.
- PostgreSQL is the only database; MySQL/MyISAM origins are historical.
- Polish strings and current theme assets are the minimum preserved content; additional localization cleanup can happen later.
- File uploads are limited to avatars and similar user assets; all shipped static assets are embedded into the binary.
- Configuration is loaded from `vallheru.toml` with environment variable overrides.

## Known Remaining Gaps

- Avatar upload handler is not yet implemented (no file-upload infrastructure in the Axum binary).
- Avatar serving route is missing — templates reference `/static/avatars/` but no route serves filesystem avatars.
- UI modernization and frontend framework migration deferred to post-migration.
- Additional language/locale support beyond current Polish content deferred.
- No browser automation or visual/UI testing yet.

## Implementation Notes

### Asset Embedding

All shipped assets are embedded at compile time.

- MiniJinja templates, CSS themes, JS files, images, and language catalogs embedded with `include_dir`.
- In-memory asset registry at startup with content type, cache headers, and content hash.
- Assets served directly from the Rust process.
- Only user-generated uploads (avatars) require external storage.

### PostgreSQL Access

The data access strategy is explicit SQL throughout.

- SQL lives in `crates/data/`, grouped by module.
- `sqlx::query!` and `sqlx::query_as!` for compile-time checked SQL.
- Row structs are separate from domain structs so schema details do not leak into business logic.
- Explicit transactions for workflows such as purchases, combat rewards, and tribe storage changes.

### Deployment

The application is operationally simple.

- One binary with subcommands for `serve`, `migrate`, `import`, `job`, `bootstrap`, and `reset-era`.
- PostgreSQL is the only external dependency.
- Docker Compose configs for development (`compose.yaml`) and production (`compose.prod.yaml`).
- No writable template caches or runtime-generated config files.

### Testing

- **Unit tests** for pure domain functions: combat formulas, stat calculations, economic invariants.
- **Integration tests** per module: Axum handlers against a real PostgreSQL database with seeded data.
- No browser automation yet. Visual/UI testing deferred.


- Rollback plan: flip the route flag back to PHP. No data rollback is needed for read-only routes. For write routes, reconciliation checks must pass before the flag is flipped permanently.