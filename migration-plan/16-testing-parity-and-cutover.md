# 16 Testing, Parity, and Cutover

## Source Surface

- Entire web route surface
- `docker-compose.yaml`
- `docker/nginx.conf`
- generated data from MySQL and PostgreSQL during migration

## Goal

Move from a growing Rust shadow implementation to a safe production cutover with clear verification and rollback rules.

## Tasks

### MP-16-01: Build a golden-master capture harness for critical pages

- Description: Capture representative PHP responses and key derived values for high-risk routes before they are replaced.
- Estimated time: 2h
- Dependencies: MP-03-01, MP-04-05.
- Acceptance criteria:
  - A repeatable capture process exists for selected public and authenticated pages.
  - The capture stores enough data to compare HTML sections or structured values later.
  - High-risk routes are prioritized first.
- Technical notes: Use structured snapshots where raw HTML is too noisy.
- In scope: Capture harness and fixtures.
- Out of scope: Full-site visual regression tooling.

### MP-16-02: Add integration tests for core user journeys

- Description: Build end-to-end integration tests for login, navigation, account flows, and at least one route in each major module.
- Estimated time: 2h
- Dependencies: MP-05-01, MP-07-02, MP-10-03, MP-12-01.
- Acceptance criteria:
  - Tests cover authenticated and unauthenticated paths.
  - Failures point to one user journey, not a generic server error.
  - The tests run against PostgreSQL-backed Rust handlers.
- Technical notes: Keep the first suite small but representative.
- In scope: Cross-module integration tests.
- Out of scope: Browser automation.

### MP-16-03: Add invariant tests for combat, economy, and inventory

- Description: Codify the most expensive-to-break gameplay invariants in tests.
- Estimated time: 2h
- Dependencies: MP-08-06, MP-09-06, MP-10-06.
- Acceptance criteria:
  - Tests cover no-negative-money, no-item-loss, and expected combat outcome properties.
  - Failures identify the broken invariant and fixture.
  - The suite can run in CI or local development without manual setup beyond PostgreSQL.
- Technical notes: Prefer a few strong invariants over many weak assertions.
- In scope: Domain invariant testing.
- Out of scope: Performance benchmarking.

### MP-16-04: Define route-by-route cutover and fallback rules

- Description: Produce the exact sequence for enabling Rust routes, keeping PHP fallbacks, and deciding when a route is safe to switch permanently.
- Estimated time: 2h
- Dependencies: MP-03-05, MP-03-01.
- Acceptance criteria:
  - Each route has a cutover state: PHP, shadowed, mirrored-read, or Rust-primary.
  - Rollback rules are documented per route group.
  - Operators know which configuration switch controls each group.
- Technical notes: Do not allow partial cutovers inside a workflow that must stay transactionally coherent.
- In scope: Cutover matrix and activation rules.
- Out of scope: Final PHP removal.

### MP-16-05: Build data reconciliation and rollback procedures

- Description: Define the operational steps for validating migrated data and rolling back safely if a route or module misbehaves.
- Estimated time: 2h
- Dependencies: MP-02-06, MP-16-04.
- Acceptance criteria:
  - There is a repeatable reconciliation checklist for each cutover batch.
  - Rollback steps are explicit, reversible, and tested at least once in staging.
  - Data ownership transitions are documented.
- Technical notes: Rollback should prefer routing traffic back to PHP first, then data repair if needed.
- In scope: Reconciliation and rollback runbooks.
- Out of scope: Automated rollback orchestration.

### MP-16-06: Package the Axum server as the primary runtime

- Description: Build the production artifact shape around one Rust binary plus PostgreSQL.
- Estimated time: 2h
- Dependencies: MP-15-06, MP-16-04.
- Acceptance criteria:
  - Production can run on one Rust binary plus PostgreSQL.
  - The runtime contract for config, health checks, and jobs is explicit.
  - Packaging does not assume writable template caches.
- Technical notes: Keep the runtime boring and easy to explain.
- In scope: Primary runtime packaging.
- Out of scope: PHP removal sequencing.

### MP-16-07: Remove PHP-only runtime dependencies from deployment

- Description: Eliminate PHP-FPM, generated config scripts, and writable Smarty/template cache assumptions from the deployment path.
- Estimated time: 2h
- Dependencies: MP-16-06.
- Acceptance criteria:
  - PHP-FPM is no longer required for Rust-primary environments.
  - Shipped assets are served from the embedded binary.
  - Deployment scripts no longer require writable template cache directories.
- Technical notes: Keep user-uploaded asset storage separate from shipped assets.
- In scope: PHP runtime dependency removal.
- Out of scope: Route rollback rules.

### MP-16-08: Write the final production startup and job runbook

- Description: Document the final startup path, scheduled job invocation, health checks, and operator commands for production.
- Estimated time: 2h
- Dependencies: MP-16-06, MP-16-07.
- Acceptance criteria:
  - Operators can start the service and scheduled jobs from one runbook.
  - Required environment variables and commands are listed.
  - Health and readiness checks are documented.
- Technical notes: This runbook replaces current tribal knowledge in Docker entrypoints and web installers.
- In scope: Production runbook.
- Out of scope: Staging rollback rehearsal.

### MP-16-09: Finalize PHP retirement and rollback references

- Description: Produce the final checklist for disabling PHP traffic, retaining rollback hooks, and declaring Rust the system of record.
- Estimated time: 2h
- Dependencies: MP-16-04, MP-16-05, MP-16-07, MP-16-08.
- Acceptance criteria:
  - PHP retirement steps are ordered and reversible until the final point of no return.
  - Rollback references point to the reconciliation and fallback procedures.
  - The cutover checklist identifies the exact moment PostgreSQL becomes authoritative for each route group.
- Technical notes: Treat retirement as an operations event, not just a code merge.
- In scope: PHP retirement checklist and rollback references.
- Out of scope: Post-launch feature redesign.