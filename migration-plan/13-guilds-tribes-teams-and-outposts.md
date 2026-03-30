# 13 Guilds, Tribes, Teams, and Outposts

## Current State

- `guilds.php`
- `guilds2.php`
- `team.php`
- `tribes.php`
- `tribeadmin.php`
- `tribearmor.php`
- `tribeastral.php`
- `tribeherbs.php`
- `tribeminerals.php`
- `tribeware.php`
- `outpost.php`
- `outposts.php`
- `includes/tribemenu.php` (tribe navigation partial)
- `includes/tribefight.php` (tribe warfare helpers)
- `class/team_class.php` (team state management)

## Why This Module Exists

Port group-oriented systems that share inventories, permissions, and combat-adjacent state between multiple players.

## Target Rust Shape

- `crates/domain/src/group/team.rs` — Team/party invitations, membership, leader checks.
- `crates/domain/src/group/tribe.rs` — Tribe creation, joining, leaving, roles/ranks, permissions.
- `crates/domain/src/group/tribe_storage.rs` — Shared tribe resources: armor, herbs, minerals, ware, astral.
- `crates/domain/src/group/outpost.rs` — Outpost ownership, troop state, warfare.
- `crates/data/src/group.rs` — Team, tribe, outpost queries.
- `crates/web/src/handlers/team.rs` — Team page handlers.
- `crates/web/src/handlers/tribe.rs` — Tribe admin, storage, and forum handlers.
- `crates/web/src/handlers/outpost.rs` — Outpost page handlers.

## Module Dependencies

- 05 Auth, Accounts, and Sessions (session for team tracking).
- 06 Player State and Progression (player tribe membership).
- 10 Economy, Markets, and Banking (resource costs for tribe actions).
- 11 Crafting, Gathering, and Workshops (astral items in shared storage).
- 12 Social, Chat, Mail, and Content (tribe forums reuse forum abstractions).

## Risks and Notes

- `class/team_class.php` manages transient team state and is not listed in the original plan. It must be ported.
- Tribe shared resource mutation (give/take across tribe members) is a high-risk corruption area. Quantity checks must be transactional.
- Outpost warfare is tightly coupled to reset logic (module 15). Keep the scheduler seam visible.
- `includes/tribefight.php` contains tribe combat helpers that differ from regular PvP.

## Tasks

### MP-13-01: Port team invitations and membership state ✅

- Description: Rebuild the team/party flows for invitations, membership slots, leader checks, and read models.
- Estimate: 1.5h
- Depends on: MP-05-06, MP-06-01.
- Functional acceptance criteria:
  - Team creation and invitation flows work.
  - Membership changes are transaction-safe.
  - Team state can be read cleanly by later combat or mission modules.
- Technical notes: Keep team logic separate from tribe/guild structures.
- In scope: Team membership workflows.
- Out of scope: Team battle mechanics.

### MP-13-02: Port guild and tribe membership flows ✅

- Description: Rebuild tribe creation, joining, leaving, and member-list flows from the guild and tribe pages.
- Estimate: 2h
- Depends on: MP-13-01, MP-10-02.
- Functional acceptance criteria:
  - Players can create or join tribes under the same constraints as today.
  - Member rosters render from PostgreSQL.
  - Joining/leaving updates related player state correctly.
- Technical notes: Use clear transaction boundaries because tribe membership touches multiple tables.
- In scope: Tribe lifecycle and roster behavior.
- Out of scope: Shared storage and permissions.
- Status: **Complete**. Created `group/tribe.rs` with TribeLevel enum (5 tiers), creation/upgrade/join/accept/leave/dissolve/kick validation, defence/army purchases, hospital pass, loan validation. 57 tests.

### MP-13-03: Port tribe permissions, ranks, and admin actions ✅

- Description: Migrate tribe ranks, permission flags, and admin/owner actions from `tribeadmin.php` and related helpers.
- Estimate: 1.5h
- Depends on: MP-13-02.
- Functional acceptance criteria:
  - Permission checks are centralized and typed.
  - Rank assignment and member admin actions are persisted safely.
  - Permission-dependent routes can reuse shared guards.
- Technical notes: This is a good place to stop using raw integer or string flags in handler code.
- In scope: Tribe permissions and administration.
- Out of scope: Shared warehouse/resource moves.
- Status: **Complete**. Created `group/tribe_admin.rs` with TribePermission enum (15 flags), PermissionSet bitfield, level-gated availability, admin access gates, rank/tag/mail/pending validation. 36 tests.

### MP-13-04: Port tribe shared resources and crafting stores ✅

- Description: Rebuild tribe armor, herbs, minerals, ware, and astral shared storage plus grant/withdrawal flows.
- Estimate: 2h
- Depends on: MP-13-03, MP-11-05.
- Functional acceptance criteria:
  - Shared tribe storage operations are transaction-safe.
  - Permission checks match current role rules.
  - Audit logs exist for give/take actions.
- Technical notes: Shared resource mutation is one of the highest-risk corruption areas; test quantities carefully.
- In scope: Tribe storage and specialty stores.
- Out of scope: Public market listings.
- Status: **Complete**. Created `group/tribe_storage.rs` with StorageArea enum (5 areas), access/permission/deposit/reserve/give validation, armory equipment eligibility, warehouse potion deposit, treasury currency keys, astral safe-box upgrade with 5-resource costs. 48 tests.

### MP-13-05: Port outpost ownership and warfare state ✅

- Description: Migrate outpost ownership, troop state, attacks, and supporting view models.
- Estimate: 2h
- Depends on: MP-13-02, MP-15-05.
- Status: **Complete**
- Functional acceptance criteria:
  - Outpost pages render current ownership and troop data.
  - Attack-related mutable state is persisted in PostgreSQL.
  - Integration points for future tribe combat remain explicit.
- Technical notes: Outposts are tightly coupled to reset logic, so keep the scheduler seam visible.
- In scope: Outpost state and pages.
- Out of scope: Full tribe-vs-tribe combat engine rewrite.
- Implementation notes:
  - Migration `20250325000025_outpost_tables.sql` creates outposts, outpost_monsters, outpost_veterans, core tables.
  - Data layer: `crates/data/src/queries/outpost.rs` (~800 lines, 40 query functions).
  - Domain: `crates/domain/src/group/outpost.rs` (~500 lines) — resource calculations, veteran stats, combat resolution, tax collection, garrison missions, morale/fatigue, maintenance cost. Uses input structs (`VeteranEquipment`, `AttackerLossInput`, `DefenderLossInput`, `GarrisonPlayerStats`, `BattleAftermath`) to avoid clippy too_many_arguments. 6 tests.
  - Handler: `crates/web/src/handlers/outpost.rs` (~2100 lines) — 23 handler functions covering outpost menu, purchase, management, treasury, shop (army/upgrades/structures), taxes, veterans, battle, garrison missions.
  - 11 MiniJinja templates for all outpost/garrison views.
  - Routes wired in `crates/web/src/routes/world.rs` via `outpost_routes()`.
  - ThreadRng scoped in blocks before `.await` boundaries (Axum Send requirement).

### MP-13-06: Port tribe forums and navigation surfaces ✅

- Description: Rebuild tribe forum and menu/navigation pages that depend on tribe membership and permissions.
- Estimate: 2h
- Depends on: MP-13-03, MP-12-04.
- Status: **Completed**
- Functional acceptance criteria:
  - Tribe-specific discussions render behind tribe access checks.
  - Shared tribe navigation is available as reusable template components.
  - Rust route handlers can replace the existing tribe forum entry points.
- Technical notes: Keep tribe forum logic aligned with the public forum abstractions where they overlap.
- In scope: Tribe UI/navigation and tribe discussions.
- Out of scope: Staff moderation of tribe content.
- Implementation notes:
  - Migration `20250325000020` creates tribes, tribe_perm, tribe_rank, tribe_topics, tribe_replies tables.
  - Data layer: `crates/data/src/queries/tribe_forum.rs` — permission checks, topic/reply CRUD, search, `NewTopic` struct.
  - Handler: `crates/web/src/handlers/tribe_forum.rs` — 10 handlers with `require_player` helper, `tribe_author` helper, `format_datetime` for epoch-to-date formatting.
  - Templates: `tforums_topics.html`, `tforums_topic.html`, `tforums_new.html`, `tforums_search.html`.
  - Routes split into `tribe_forum_routes()` and `forum_routes()` sub-functions in `world.rs`.
  - Rate limiting deferred (session_data not yet available) — recorded as tech debt.