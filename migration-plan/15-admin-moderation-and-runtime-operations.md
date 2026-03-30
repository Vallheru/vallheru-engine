# 15 Admin, Moderation, and Runtime Operations

## Current State

- `admin.php`
- `staff.php`
- `stafflist.php`
- `bugtrack.php`
- `jail.php`
- `court.php`
- `sedzia.php` (judge panel — rank/member management by judges)
- `memberlist.php`
- `log.php`
- `news.php`
- `updates.php`
- `addnews.php`
- `addupdate.php`
- `includes/admin/*.php` (11 admin action includes: addtext, banmail, bugreport, clearc, czat, innarchive, jail, logs, tags, takeaway)
- `includes/resets.php` (page-triggered energy/daily reset logic)
- `includes/ranks.php` (rank selection and display)
- `install/install.php` (schema setup/seeding)
- `install/resetall.php` (era-wide reset)
- `docker-compose.yaml`
- `docker/entrypoint.sh`

## Why This Module Exists

Port the staff-facing tools and replace legacy runtime scripts and page-triggered resets with explicit operational commands and scheduled jobs.

## Target Rust Shape

- `crates/web/src/handlers/admin.rs` — Admin panel dispatch and admin action handlers.
- `crates/web/src/handlers/staff.rs` — Staff panel, bug tracking, logs, member lists.
- `crates/web/src/handlers/moderation.rs` — Jail, court, judge, chat/forum/mail restriction handlers.
- `crates/web/src/handlers/publishing.rs` — News, updates, rules publishing by staff.
- `crates/domain/src/admin/moderation.rs` — Moderation action logic, jail expiration, ban enforcement.
- `crates/domain/src/admin/reset.rs` — Energy resets, daily resets, era-wide reset logic.
- `crates/domain/src/admin/install.rs` — Schema bootstrap, seed loading.
- `crates/data/src/admin.rs` — Admin queries, log reads, moderation state queries.

## Module Dependencies

- 01 Platform Foundations (CLI shell for operational commands).
- 02 Database and PostgreSQL (schema setup, reference data import).
- 03 HTTP Routing and Middleware (rank-based authorization guards).
- 05 Auth, Accounts, and Sessions (staff/admin authentication).
- 12 Social, Chat, Mail, and Content (moderation enforcement hooks in chat/forum/mail).

## Risks and Notes

- `sedzia.php` (judge panel) was missing from the original plan. It allows judges to change player ranks and manage members — a significant moderation capability.
- `includes/admin/` contains 11 admin action sub-scripts that are dispatched from `admin.php`. Each must be individually mapped.
- `includes/ranks.php` contains rank display/selection logic used across admin and player pages.
- Page-triggered resets (`includes/resets.php`) are a critical operational concern. Moving them to explicit scheduled jobs changes runtime behavior.
- `install/install.php` and `install/resetall.php` contain destructive operations that must require explicit confirmation in the Rust CLI.

## Tasks

### MP-15-01: Port staff and admin route trees with shared guards ✅

- Description: Rebuild the admin and staff panels, including route dispatch and rank-based access control.
- Estimate: 1.5h
- Depends on: MP-03-04, MP-05-01.
- Functional acceptance criteria:
  - ✅ Staff-only and admin-only pages are routed in Rust.
  - ✅ Shared rank guards replace duplicated per-page checks.
  - ✅ Navigation links render based on the operator's role.
- Technical notes: Mirror the current role names first; cleanups can wait until after cutover.
- In scope: Staff/admin shells and route organization.
- Out of scope: Every admin action implementation.
- Implementation notes:
  - `crates/web/src/handlers/admin.rs`: Admin panel shell with sectioned menu links.
  - `crates/web/src/handlers/staff.rs`: Staff panel (role-filtered links) + staff list (audience hall).
  - `crates/web/src/routes/admin.rs`: Route groups with `require_admin`, `require_any_rank`, `require_authenticated` guards.
  - `crates/data/src/queries/admin.rs`: `list_staff_members()` query using `ANY($1)` for rank filtering.
  - Templates: `admin.html`, `staff.html`, `stafflist.html`.
  - Builder rank sees only bug report links; Staff/Admin see full moderation menu.

### MP-15-02: Port moderation actions for jail, court, judge panel, and communication restrictions

- Description: Rebuild the moderation flows that jail players (`jail.php`), manage court proceedings (`court.php`), handle judge rank/member management (`sedzia.php`), ban chat/forum writing, and restrict mail interactions.
- Estimate: 2h
- Depends on: MP-15-01, MP-12-01, MP-12-03, MP-12-04.
- Functional acceptance criteria:
  - Moderation actions apply and expire correctly.
  - The judge panel (`sedzia.php`) rank and member management works under judge role guards.
  - The moderated player experiences the same restrictions in migrated routes.
  - Operator actions are logged.
- Technical notes: Keep expiration rules aligned with the reset/scheduler model. The judge panel is rank-gated to the "Sędzia" (Judge) rank.
- In scope: Moderation mutations and enforcement hooks, including jail, court, and judge panel.
- Out of scope: Out-of-band alerting.

### MP-15-03: Port bug reporting, logs, and support views ✅

- Description: Rebuild bugtrack-style reporting, player/staff lists, and activity/log viewing pages.
- Estimate: 1.5h
- Depends on: MP-15-01, MP-01-05.
- Functional acceptance criteria:
  - Operators can view reported issues and relevant logs in Rust.
  - Player and staff listing pages render from PostgreSQL.
  - Internal diagnostics stay out of public routes.
- Technical notes: Replace the old PHP error capture model with structured logging plus a simple support UI.
- In scope: Support dashboards and read models.
- Out of scope: External issue tracker integration.

### MP-15-04: Port court, update, and staff publishing tools

- Description: Rebuild content-management pages used by staff for rules, updates, and front-page/news publication.
- Estimate: 1.5h
- Depends on: MP-12-05, MP-15-01.
- Functional acceptance criteria:
  - Staff can publish or edit rules/news/update content in Rust.
  - Public pages consume the same PostgreSQL-backed content.
  - Edit actions are protected and logged.
- Technical notes: Keep editing flows simple and server-rendered.
- In scope: Staff publishing UI and persistence.
- Out of scope: Rich text editor replacement work.

### MP-15-05: Replace page-triggered resets with explicit scheduled jobs ✅

- Description: Move energy resets, daily resets, hunter quest generation, jail expiration, and similar behaviors out of page loads and into explicit jobs.
- Estimate: 2h
- Depends on: MP-01-06, MP-02-04.
- Functional acceptance criteria:
  - ✅ Reset logic exists as callable Rust job functions.
  - ✅ A scheduler or cron-invoked subcommand can run the jobs safely.
  - ✅ Job runs are idempotent or protected against duplicate execution.
- Technical notes: Keep the first version simple: one binary, scheduled subcommands, PostgreSQL locks where needed.
- In scope: Reset job extraction and execution path.
- Out of scope: Distributed job orchestration.
- Implementation notes:
  - `crates/data/src/jobs.rs`: `run_job()` with advisory lock protection, `energy_tick()`, `daily_reset()`, `process_random_events()`.
  - CLI: `vallheru job energy-tick` and `vallheru job daily-reset`.
  - Advisory lock keys from `Job::advisory_lock_key()` in `crates/domain/src/admin/reset.rs`.
  - `daily_reset` covers: event/attack cleanup, farm aging, potion restock, jail expiration, chat/forum ban countdown, poison removal, outpost turns, tribe flags, house points, energy tick, map reset, ring restock, player daily reset, newbie/freeze decrements, core pass bonus, thief crime, room rental, random event processing, game reopen.

### MP-15-06: Port installer and bootstrap operational commands

- Description: Replace `install/install.php` with explicit Rust commands for schema setup, seed loading, and initial bootstrap tasks.
- Estimate: 1.5h
- Depends on: MP-01-06, MP-02-05.
- Functional acceptance criteria:
  - A new environment can initialize PostgreSQL from Rust commands.
  - Seed and bootstrap steps are explicit and repeatable.
  - Command output is usable in local development and CI.
- Technical notes: Keep the setup flow obvious; avoid hidden side effects.
- In scope: Install/bootstrap command path.
- Out of scope: Era reset logic.

### MP-15-07: Port era-reset operational commands

- Description: Replace `install/resetall.php` with auditable Rust CLI steps for season reset, archival, and confirmation-gated destructive actions.
- Estimate: 2h
- Depends on: MP-01-06, MP-15-05, MP-15-06.
- Functional acceptance criteria:
  - Era reset logic is implemented as auditable CLI steps, not a web script.
  - Destructive commands require explicit confirmation flags.
  - Reset-side data moves or archival steps are documented and testable.
- Technical notes: Keep the final ops model compatible with both local development and a single production binary/container.
- In scope: Era reset and archival tooling.
- Out of scope: Full self-service admin automation.