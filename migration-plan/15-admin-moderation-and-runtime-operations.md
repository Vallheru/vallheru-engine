# 15 Admin, Moderation, and Runtime Operations

## Source Surface

- `admin.php`
- `staff.php`
- `stafflist.php`
- `bugtrack.php`
- `jail.php`
- `court.php`
- `memberlist.php`
- `log.php`
- `news.php`
- `updates.php`
- `addnews.php`
- `addupdate.php`
- `includes/admin/*.php`
- `includes/resets.php`
- `install/install.php`
- `install/resetall.php`
- `docker-compose.yaml`
- `docker/entrypoint.sh`

## Goal

Port the staff-facing tools and replace legacy runtime scripts and page-triggered resets with explicit operational commands and scheduled jobs.

## Tasks

### MP-15-01: Port staff and admin route trees with shared guards

- Description: Rebuild the admin and staff panels, including route dispatch and rank-based access control.
- Estimated time: 1.5h
- Dependencies: MP-03-04, MP-05-01.
- Acceptance criteria:
  - Staff-only and admin-only pages are routed in Rust.
  - Shared rank guards replace duplicated per-page checks.
  - Navigation links render based on the operator's role.
- Technical notes: Mirror the current role names first; cleanups can wait until after cutover.
- In scope: Staff/admin shells and route organization.
- Out of scope: Every admin action implementation.

### MP-15-02: Port moderation actions for jail, chat, forum, and mail restrictions

- Description: Rebuild the moderation flows that jail players, ban chat/forum writing, and restrict mail interactions.
- Estimated time: 2h
- Dependencies: MP-15-01, MP-12-01, MP-12-03, MP-12-04.
- Acceptance criteria:
  - Moderation actions apply and expire correctly.
  - The moderated player experiences the same restrictions in migrated routes.
  - Operator actions are logged.
- Technical notes: Keep expiration rules aligned with the reset/scheduler model.
- In scope: Moderation mutations and enforcement hooks.
- Out of scope: Out-of-band alerting.

### MP-15-03: Port bug reporting, logs, and support views

- Description: Rebuild bugtrack-style reporting, player/staff lists, and activity/log viewing pages.
- Estimated time: 1.5h
- Dependencies: MP-15-01, MP-01-05.
- Acceptance criteria:
  - Operators can view reported issues and relevant logs in Rust.
  - Player and staff listing pages render from PostgreSQL.
  - Internal diagnostics stay out of public routes.
- Technical notes: Replace the old PHP error capture model with structured logging plus a simple support UI.
- In scope: Support dashboards and read models.
- Out of scope: External issue tracker integration.

### MP-15-04: Port court, update, and staff publishing tools

- Description: Rebuild content-management pages used by staff for rules, updates, and front-page/news publication.
- Estimated time: 1.5h
- Dependencies: MP-12-05, MP-15-01.
- Acceptance criteria:
  - Staff can publish or edit rules/news/update content in Rust.
  - Public pages consume the same PostgreSQL-backed content.
  - Edit actions are protected and logged.
- Technical notes: Keep editing flows simple and server-rendered.
- In scope: Staff publishing UI and persistence.
- Out of scope: Rich text editor replacement work.

### MP-15-05: Replace page-triggered resets with explicit scheduled jobs

- Description: Move energy resets, daily resets, hunter quest generation, jail expiration, and similar behaviors out of page loads and into explicit jobs.
- Estimated time: 2h
- Dependencies: MP-01-06, MP-02-04.
- Acceptance criteria:
  - Reset logic exists as callable Rust job functions.
  - A scheduler or cron-invoked subcommand can run the jobs safely.
  - Job runs are idempotent or protected against duplicate execution.
- Technical notes: Keep the first version simple: one binary, scheduled subcommands, PostgreSQL locks where needed.
- In scope: Reset job extraction and execution path.
- Out of scope: Distributed job orchestration.

### MP-15-06: Port installer and bootstrap operational commands

- Description: Replace `install/install.php` with explicit Rust commands for schema setup, seed loading, and initial bootstrap tasks.
- Estimated time: 1.5h
- Dependencies: MP-01-06, MP-02-05.
- Acceptance criteria:
  - A new environment can initialize PostgreSQL from Rust commands.
  - Seed and bootstrap steps are explicit and repeatable.
  - Command output is usable in local development and CI.
- Technical notes: Keep the setup flow obvious; avoid hidden side effects.
- In scope: Install/bootstrap command path.
- Out of scope: Era reset logic.

### MP-15-07: Port era-reset operational commands

- Description: Replace `install/resetall.php` with auditable Rust CLI steps for season reset, archival, and confirmation-gated destructive actions.
- Estimated time: 2h
- Dependencies: MP-01-06, MP-15-05, MP-15-06.
- Acceptance criteria:
  - Era reset logic is implemented as auditable CLI steps, not a web script.
  - Destructive commands require explicit confirmation flags.
  - Reset-side data moves or archival steps are documented and testable.
- Technical notes: Keep the final ops model compatible with both local development and a single production binary/container.
- In scope: Era reset and archival tooling.
- Out of scope: Full self-service admin automation.