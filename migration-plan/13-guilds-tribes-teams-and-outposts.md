# 13 Guilds, Tribes, Teams, and Outposts

## Source Surface

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
- `includes/tribemenu.php`
- `includes/tribefight.php`

## Goal

Port group-oriented systems that share inventories, permissions, and combat-adjacent state between multiple players.

## Tasks

### MP-13-01: Port team invitations and membership state

- Description: Rebuild the team/party flows for invitations, membership slots, leader checks, and read models.
- Estimated time: 1.5h
- Dependencies: MP-05-06, MP-06-01.
- Acceptance criteria:
  - Team creation and invitation flows work.
  - Membership changes are transaction-safe.
  - Team state can be read cleanly by later combat or mission modules.
- Technical notes: Keep team logic separate from tribe/guild structures.
- In scope: Team membership workflows.
- Out of scope: Team battle mechanics.

### MP-13-02: Port guild and tribe membership flows

- Description: Rebuild tribe creation, joining, leaving, and member-list flows from the guild and tribe pages.
- Estimated time: 2h
- Dependencies: MP-13-01, MP-10-02.
- Acceptance criteria:
  - Players can create or join tribes under the same constraints as today.
  - Member rosters render from PostgreSQL.
  - Joining/leaving updates related player state correctly.
- Technical notes: Use clear transaction boundaries because tribe membership touches multiple tables.
- In scope: Tribe lifecycle and roster behavior.
- Out of scope: Shared storage and permissions.

### MP-13-03: Port tribe permissions, ranks, and admin actions

- Description: Migrate tribe ranks, permission flags, and admin/owner actions from `tribeadmin.php` and related helpers.
- Estimated time: 1.5h
- Dependencies: MP-13-02.
- Acceptance criteria:
  - Permission checks are centralized and typed.
  - Rank assignment and member admin actions are persisted safely.
  - Permission-dependent routes can reuse shared guards.
- Technical notes: This is a good place to stop using raw integer or string flags in handler code.
- In scope: Tribe permissions and administration.
- Out of scope: Shared warehouse/resource moves.

### MP-13-04: Port tribe shared resources and crafting stores

- Description: Rebuild tribe armor, herbs, minerals, ware, and astral shared storage plus grant/withdrawal flows.
- Estimated time: 2h
- Dependencies: MP-13-03, MP-11-05.
- Acceptance criteria:
  - Shared tribe storage operations are transaction-safe.
  - Permission checks match current role rules.
  - Audit logs exist for give/take actions.
- Technical notes: Shared resource mutation is one of the highest-risk corruption areas; test quantities carefully.
- In scope: Tribe storage and specialty stores.
- Out of scope: Public market listings.

### MP-13-05: Port outpost ownership and warfare state

- Description: Migrate outpost ownership, troop state, attacks, and supporting view models.
- Estimated time: 2h
- Dependencies: MP-13-02, MP-15-05.
- Acceptance criteria:
  - Outpost pages render current ownership and troop data.
  - Attack-related mutable state is persisted in PostgreSQL.
  - Integration points for future tribe combat remain explicit.
- Technical notes: Outposts are tightly coupled to reset logic, so keep the scheduler seam visible.
- In scope: Outpost state and pages.
- Out of scope: Full tribe-vs-tribe combat engine rewrite.

### MP-13-06: Port tribe forums and navigation surfaces

- Description: Rebuild tribe forum and menu/navigation pages that depend on tribe membership and permissions.
- Estimated time: 2h
- Dependencies: MP-13-03, MP-12-04.
- Acceptance criteria:
  - Tribe-specific discussions render behind tribe access checks.
  - Shared tribe navigation is available as reusable template components.
  - Rust route handlers can replace the existing tribe forum entry points.
- Technical notes: Keep tribe forum logic aligned with the public forum abstractions where they overlap.
- In scope: Tribe UI/navigation and tribe discussions.
- Out of scope: Staff moderation of tribe content.