# 01 Platform Foundations

## Current State

- `index.php`
- `includes/config.php`
- `includes/head.php`
- `includes/sessions.php`
- `docker-compose.yaml`
- `docker/entrypoint.sh`
- `docker/nginx.conf`
- `docker/php.Dockerfile`

## Why This Module Exists

Create a Rust workspace and runtime shell that can host the migrated application without committing yet to risky gameplay behavior.

## Target Rust Shape

- `crates/server/src/main.rs` — Axum bootstrap, CLI dispatch via `clap`, graceful shutdown.
- `crates/server/src/config.rs` — Typed config loaded from env + optional TOML.
- `crates/server/src/errors.rs` — Shared error type and HTML error rendering.
- `crates/server/src/health.rs` — `/healthz` and `/readyz` handlers.
- `crates/server/src/cli.rs` — Subcommand registration for `serve`, `migrate`, `import`, `reset-era`, `reconcile`.

## Module Dependencies

None — this is the first module and has no prerequisites.

## Risks and Notes

- The current PHP bootstrap in `includes/head.php` does many things implicitly (session, locale, player loading, DB connection). The Rust equivalent must be explicit and layered so later modules can compose on top of it.
- `includes/config.php` is empty in the repo, meaning config is generated outside version control today. The Rust config loader must fail fast on missing values.

## Tasks

### MP-01-01: Create the Rust workspace skeleton

- Description: Create the Cargo workspace, define the `server`, `web`, `domain`, and `data` crates, and add a minimal compileable dependency graph.
- Estimate: 1.5h
- Depends on: None.
- Functional acceptance criteria:
  - `cargo check` passes for the new workspace.
  - The crate boundaries match the architecture in `README.md`.
  - The binary crate starts and exits cleanly.
- Technical notes: Keep this monorepo-local; do not split into multiple deployables.
- In scope: Workspace layout, crate manifests, shared lint settings.
- Out of scope: Real routes, templates, or database access.

### MP-01-02: Add typed configuration loading

- Description: Replace the generated PHP config pattern with explicit Rust configuration loaded from environment variables and an optional local TOML file.
- Estimate: 1h
- Depends on: MP-01-01.
- Functional acceptance criteria:
  - Config covers HTTP bind address, PostgreSQL DSN, session secrets, email settings, and cutover flags.
  - Missing required config fails fast with a readable startup error.
  - A sample config file is documented in comments or a template file.
- Technical notes: Prefer direct `serde` deserialization over a heavy config framework.
- In scope: Server config model and loader.
- Out of scope: Secrets management platform integration.

### MP-01-03: Add application bootstrap and structured logging

- Description: Implement server startup, graceful shutdown, request tracing, and startup logging.
- Estimate: 1h
- Depends on: MP-01-01, MP-01-02.
- Functional acceptance criteria:
  - The binary starts an Axum server and logs startup configuration without secrets.
  - Request logs include route, status, duration, and correlation data.
  - Graceful shutdown works on SIGINT/SIGTERM.
- Technical notes: Use `tracing` and `tracing-subscriber` from the start so later parity work has usable logs.
- In scope: Bootstrap path and logging.
- Out of scope: Business-domain events.

### MP-01-04: Add health, readiness, and build information endpoints

- Description: Implement internal endpoints for process health, database readiness stub, and build metadata.
- Estimate: 1h
- Depends on: MP-01-03.
- Functional acceptance criteria:
  - `/healthz` returns process health.
  - `/readyz` can be wired to dependency checks later.
  - Build version and git revision are exposed in a machine-readable response.
- Technical notes: Keep these outside authenticated route trees.
- In scope: Operational endpoints.
- Out of scope: Monitoring dashboards.

### MP-01-05: Add shared error and response infrastructure

- Description: Define the common application error type and a consistent way to render user-facing errors, redirects, and flash messages.
- Estimate: 1h
- Depends on: MP-01-03.
- Functional acceptance criteria:
  - Handlers can return typed errors.
  - HTML requests render a standard error page path.
  - Logging captures internal causes without exposing them to users.
- Technical notes: Mirror the current PHP behavior of user-readable errors, but stop leaking raw runtime details.
- In scope: Error plumbing for the web layer.
- Out of scope: Domain-specific validation messages.

### MP-01-06: Consolidate runtime commands into one binary

- Description: Add CLI subcommands for future schema migration, import, reset, and parity jobs so operational tooling does not stay in shell/PHP scripts.
- Estimate: 1.5h
- Depends on: MP-01-02, MP-01-03.
- Functional acceptance criteria:
  - The binary exposes placeholder subcommands for `serve`, `migrate`, `import`, `reset-era`, and `reconcile`.
  - Command help output is clear enough for future engineers.
  - The design allows web and operational code to share config and logging.
- Technical notes: This is the replacement seam for `install/install.php` and `install/resetall.php` later.
- In scope: CLI shell and subcommand registration.
- Out of scope: Real migration or reset logic.