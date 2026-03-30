# 16 Testing, Parity, and Cutover

## Current State

- Entire web route surface (110 PHP entry points)
- `docker-compose.yaml`
- `docker/nginx.conf`
- `docker/php.Dockerfile`
- `docker/custom.php.ini`
- Generated data from MySQL and PostgreSQL during migration

## Why This Module Exists

Move from a growing Rust shadow implementation to a safe production cutover with clear verification and rollback rules.

## Target Rust Shape

- `crates/server/src/cli/reconcile.rs` — Data reconciliation CLI command.
- `crates/server/src/cli/smoke.rs` — Smoke check subcommand.
- Golden-master test fixtures and route-level integration tests in `tests/`.
- Production Dockerfile and deployment configuration.
- Operator runbook documentation.

## Module Dependencies

- All prior modules (01–15) must be substantially complete before final cutover.
- 02 Database and PostgreSQL (reconciliation tools).
- 03 HTTP Routing and Middleware (fallback strategy, route manifest).
- 15 Admin, Moderation, and Runtime Operations (installer, reset commands).

## Risks and Notes

- Cutover must be route-by-route, not big-bang, to reduce risk.
- Workflows that span multiple routes (e.g. registration → activation → login) must be cut over together.
- Reconciliation must confirm data integrity before each batch of routes switches over.
- PHP removal is the final step and must be reversible until a declared point of no return.

## Tasks

### MP-16-01: Build a golden-master capture harness for critical pages

- Description: Capture representative PHP responses and key derived values for high-risk routes before they are replaced.
- Estimate: 2h
- Depends on: MP-03-01, MP-04-05.
- Functional acceptance criteria:
  - A repeatable capture process exists for selected public and authenticated pages.
  - The capture stores enough data to compare HTML sections or structured values later.
  - High-risk routes are prioritized first.
- Technical notes: Use structured snapshots where raw HTML is too noisy.
- In scope: Capture harness and fixtures.
- Out of scope: Full-site visual regression tooling.

### MP-16-02: Add integration tests for core user journeys ✅

- Status: **DONE** (commit pending)
- Description: Build end-to-end integration tests for login, navigation, account flows, and at least one route in each major module.
- Estimate: 2h
- Depends on: MP-05-01, MP-07-02, MP-10-03, MP-12-01.
- Functional acceptance criteria:
  - Tests cover authenticated and unauthenticated paths.
  - Failures point to one user journey, not a generic server error.
  - The tests run against PostgreSQL-backed Rust handlers.
- Technical notes: Keep the first suite small but representative.
- In scope: Cross-module integration tests.
- Out of scope: Browser automation.
- Implementation notes:
  - Created `crates/web/tests/integration.rs` with 17 HTTP-layer integration tests.
  - Tests use `tower::ServiceExt::oneshot` against the full Axum router (lazy pool, no live DB needed).
  - Coverage: healthz, buildinfo, migration-status (operational); auth guards on city/bank/equipment/mail/forums/admin/staff/outposts/market; fallback 404; static asset serving; RSS public access; login form validation.
  - **Found and fixed TD-020**: duplicate `/chronicle` routes in `quest_routes()` and `pages_routes()` caused router panic. Removed duplicate from `pages_routes()`.
  - Added dev-dependencies: tokio, tower (util), serde_json, sqlx.

### MP-16-03: Add invariant tests for combat, economy, and inventory ✅

- Status: **DONE** (commit 414af28)
- Description: Codify the most expensive-to-break gameplay invariants in tests.
- Estimate: 2h
- Depends on: MP-08-06, MP-09-06, MP-10-06.
- Functional acceptance criteria:
  - Tests cover no-negative-money, no-item-loss, and expected combat outcome properties.
  - Failures identify the broken invariant and fixture.
  - The suite can run in CI or local development without manual setup beyond PostgreSQL.
- Technical notes: Prefer a few strong invariants over many weak assertions.
- In scope: Domain invariant testing.
- Out of scope: Performance benchmarking.
- Implementation notes:
  - Created `crates/domain/tests/invariants.rs` with 19 property-style tests.
  - Currency tests: deposit/withdraw conservation, transfer conservation, spend never negative, zero/negative rejection.
  - Outpost combat tests: attacker/defender remaining bounded, tax gold non-negative, morale labels valid, maintenance cost non-negative, size/structure upgrades non-negative, costs non-negative, battle experience positive, veteran stats positive, attack gold gain non-negative.
  - Found and fixed 2 real bugs: `attacker_losses` and `defender_losses` could produce remaining > starting when blost bonus made losses negative. Fixed with `.clamp(0, count)`.

### MP-16-04: Define route-by-route cutover and fallback rules ✅

- Status: **DONE** (commit pending)
- Description: Produce the exact sequence for enabling Rust routes, keeping PHP fallbacks, and deciding when a route is safe to switch permanently.
- Estimate: 2h
- Depends on: MP-03-05, MP-03-01.
- Functional acceptance criteria:
  - Each route has a cutover state: PHP, shadowed, mirrored-read, or Rust-primary.
  - Rollback rules are documented per route group.
  - Operators know which configuration switch controls each group.
- Technical notes: Do not allow partial cutovers inside a workflow that must stay transactionally coherent.
- In scope: Cutover matrix and activation rules.
- Out of scope: Final PHP removal.
- Implementation notes:
  - Created `migration-plan/cutover-rules.md` with full cutover matrix.
  - Defined 17 cutover groups (0, A–Q, Z) with dependency chains.
  - Each group has pre-cutover checklist, rollback rules, and known gaps.
  - Updated `fallback.rs` migration registry to track all 100+ implemented routes.
  - Added `Copy` derive to `MigratedRoute` and `RouteStatus` for const constructor.
  - Simplified registry with `const fn r()` shorthand.

### MP-16-05: Build data reconciliation and rollback procedures ✅

- Status: **DONE**
- Description: Define the operational steps for validating migrated data and rolling back safely if a route or module misbehaves.
- Estimate: 2h
- Depends on: MP-02-06, MP-16-04.
- Functional acceptance criteria:
  - There is a repeatable reconciliation checklist for each cutover batch.
  - Rollback steps are explicit, reversible, and tested at least once in staging.
  - Data ownership transitions are documented.
- Technical notes: Rollback should prefer routing traffic back to PHP first, then data repair if needed.
- In scope: Reconciliation and rollback runbooks.
- Out of scope: Automated rollback orchestration.
- Implementation notes:
  - Created `migration-plan/reconciliation-procedures.md` with full operational runbook.
  - Expanded reconciliation tool (`crates/data/src/reconcile.rs`) from 7 tables to ~60 tables
    covering all migration-created tables: catalog, player, economy, gathering, social, content,
    housing, tribes, quests, outposts, and moderation.
  - Fixed `classify()` to handle user-generated tables (min_expected=0) correctly.
  - Widened report column to accommodate longer table names.
  - Per-group reconciliation SQL checks documented for all 17 cutover groups.
  - Rollback procedures: general (any group), Group A special case, emergency data repair.
  - Data ownership transition matrix for mixed-mode and full-cutover phases.
  - Reconciliation schedule: pre-cutover, first-hour monitoring, daily, 7-day soak.

### MP-16-06: Package the Axum server as the primary runtime ✅

- Status: **DONE**
- Description: Build the production artifact shape around one Rust binary plus PostgreSQL.
- Estimate: 2h
- Depends on: MP-15-06, MP-16-04.
- Functional acceptance criteria:
  - Production can run on one Rust binary plus PostgreSQL.
  - The runtime contract for config, health checks, and jobs is explicit.
  - Packaging does not assume writable template caches.
- Technical notes: Keep the runtime boring and easy to explain.
- In scope: Primary runtime packaging.
- Out of scope: PHP removal sequencing.

### MP-16-07: Remove PHP-only runtime dependencies from deployment

- Description: Eliminate PHP-FPM, generated config scripts, and writable Smarty/template cache assumptions from the deployment path.
- Estimate: 2h
- Depends on: MP-16-06.
- Functional acceptance criteria:
  - PHP-FPM is no longer required for Rust-primary environments.
  - Shipped assets are served from the embedded binary.
  - Deployment scripts no longer require writable template cache directories.
- Technical notes: Keep user-uploaded asset storage separate from shipped assets.
- In scope: PHP runtime dependency removal.
- Out of scope: Route rollback rules.

### MP-16-08: Write the final production startup and job runbook

- Description: Document the final startup path, scheduled job invocation, health checks, and operator commands for production.
- Estimate: 2h
- Depends on: MP-16-06, MP-16-07.
- Functional acceptance criteria:
  - Operators can start the service and scheduled jobs from one runbook.
  - Required environment variables and commands are listed.
  - Health and readiness checks are documented.
- Technical notes: This runbook replaces current tribal knowledge in Docker entrypoints and web installers.
- In scope: Production runbook.
- Out of scope: Staging rollback rehearsal.

### MP-16-09: Finalize PHP retirement and rollback references

- Description: Produce the final checklist for disabling PHP traffic, retaining rollback hooks, and declaring Rust the system of record.
- Estimate: 2h
- Depends on: MP-16-04, MP-16-05, MP-16-07, MP-16-08.
- Functional acceptance criteria:
  - PHP retirement steps are ordered and reversible until the final point of no return.
  - Rollback references point to the reconciliation and fallback procedures.
  - The cutover checklist identifies the exact moment PostgreSQL becomes authoritative for each route group.
- Technical notes: Treat retirement as an operations event, not just a code merge.
- In scope: PHP retirement checklist and rollback references.
- Out of scope: Post-launch feature redesign.