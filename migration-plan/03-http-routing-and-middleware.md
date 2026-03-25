# 03 HTTP Routing and Middleware

## Source Surface

- Root PHP entry points such as `index.php`, `city.php`, `battle.php`, `equip.php`, `chat.php`, `staff.php`
- `includes/head.php`
- `includes/foot.php`
- `source.php`

## Goal

Replace file-per-page dispatch with an explicit Axum router and shared middleware stack, while preserving the legacy route surface during migration.

## Tasks

### MP-03-01: Build the canonical route manifest

- Description: Convert the 110 top-level PHP entry points into a structured route inventory with ownership, auth requirements, and migration order.
- Estimated time: 1h
- Dependencies: MP-02-01.
- Acceptance criteria:
  - Every top-level PHP file maps to a planned Axum route or an explicit non-web exception.
  - Each route is tagged as public, authenticated, staff, or admin.
  - The manifest records which module owns the route.
- Technical notes: This becomes the source of truth for cutover sequencing.
- In scope: Route manifest and metadata.
- Out of scope: Handler implementations.

### MP-03-02: Implement router composition by module

- Description: Create Axum route groups per migration module rather than one flat file.
- Estimated time: 1h
- Dependencies: MP-03-01, MP-01-03.
- Acceptance criteria:
  - The server assembles routers from module registration functions.
  - Public and authenticated route trees are separated cleanly.
  - Adding a new page does not require editing a single giant router file.
- Technical notes: Keep the layout close to the migration-plan file order.
- In scope: Router composition and nesting.
- Out of scope: Business logic.

### MP-03-03: Add request context middleware

- Description: Implement middleware that resolves session user, locale, theme, request id, and page context once per request.
- Estimated time: 1h
- Dependencies: MP-01-05, MP-02-04.
- Acceptance criteria:
  - Handlers can access a typed request context.
  - Missing or invalid session state is handled centrally.
  - Locale and theme selection are available to templates.
- Technical notes: This is the Rust replacement for the implicit global state created in `includes/head.php`.
- In scope: Request context middleware.
- Out of scope: Account or session creation.

### MP-03-04: Add authorization guards

- Description: Centralize checks that are currently scattered across pages, such as staff rank, admin rank, tribe membership, and location preconditions.
- Estimated time: 1h
- Dependencies: MP-03-03.
- Acceptance criteria:
  - Common guards exist for rank-based and state-based access.
  - Unauthorized access returns consistent user-facing responses.
  - Route handlers no longer duplicate the same rank checks.
- Technical notes: Start with rank and authentication, then add finer game-state guards as modules migrate.
- In scope: Guard framework and first guard set.
- Out of scope: Every feature-specific rule.

### MP-03-05: Add legacy fallback strategy

- Description: Define and implement the route-level fallback mechanism that lets Nginx or the Rust app pass unmigrated pages to PHP during the strangler phase.
- Estimated time: 1h
- Dependencies: MP-03-01.
- Acceptance criteria:
  - Migrated routes can be turned on individually.
  - Unmigrated routes still reach PHP predictably.
  - The cutover flag source is explicit and auditable.
- Technical notes: Keep the switching logic simple and route-based, not request-body-based.
- In scope: Fallback design and activation model.
- Out of scope: Final PHP removal.

### MP-03-06: Standardize redirects, back-links, and page titles

- Description: Recreate the shared page metadata behavior that is currently hidden in per-page PHP scripts and template includes.
- Estimated time: 1h
- Dependencies: MP-03-02, MP-03-03.
- Acceptance criteria:
  - Handlers can set page titles and breadcrumb-like back links consistently.
  - Redirect helpers preserve flash messages where needed.
  - Error pages and success pages use one shared mechanism.
- Technical notes: This reduces template conditionals later.
- In scope: Shared page metadata helpers.
- Out of scope: Final design polish.