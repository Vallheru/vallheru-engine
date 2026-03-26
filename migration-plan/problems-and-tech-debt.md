# Problems and Technical Debt Register

This file tracks technical debt, bugs, migration gaps, and design risks discovered during migration execution.

## Format

Each entry includes:
- **ID**: `TD-NNN`
- **Title**: Short description
- **Type**: `bug` | `tech-debt` | `migration-gap` | `test-gap` | `design-risk` | `missing-prerequisite` | `runtime-risk` | `performance-risk`
- **Discovered in**: Task ID where found
- **Description**: What the problem is
- **Impact**: What happens if not addressed
- **Action**: Recommended fix
- **Fixable in existing task**: Yes/No + task ID
- **Needs new task**: Yes/No
- **Status**: `open` | `planned` | `reduced` | `resolved` | `wont-fix`
- **Related tasks**: Task IDs

---

## Entries

### TD-001: Stub lib crates have no real content yet

- **Type**: tech-debt
- **Discovered in**: MP-01-01
- **Description**: `web`, `domain`, and `data` crates contain only a `lib_stub()` placeholder. These will be populated as later tasks add real modules.
- **Impact**: None immediate — stubs exist only to satisfy the workspace dependency graph.
- **Action**: Remove stubs as real public API is added in each crate.
- **Fixable in existing task**: Yes — each subsequent task touching these crates should remove/replace stubs.
- **Needs new task**: No
- **Status**: reduced
- **Related tasks**: MP-01-02 through MP-01-06, MP-02-xx onward
- **Notes**: `data` crate populated in MP-02-04/05/06. `web` crate populated in MP-03-02. Only `domain` remains a stub.

### TD-002: Character reset SQL references unmigrated gameplay tables

- **Type**: runtime-risk
- **Discovered in**: MP-05-05
- **Description**: `execute_full_reset` and `execute_partial_reset` in `crates/data/src/queries/character_reset.rs` perform DELETE/UPDATE on ~20 legacy gameplay tables (equipment, potions, herbs, mines, farms, astral, etc.) that haven't been created in Postgres migrations yet.
- **Impact**: Character reset will fail at runtime until those tables exist. The route itself is staged (not active in production).
- **Action**: Add migration for gameplay tables as those modules are ported (MP-09 through MP-12), or create a migration with empty stub tables.
- **Fixable in existing task**: No — depends on gameplay module migrations.
- **Needs new task**: No — will be resolved naturally as modules MP-09 through MP-12 are migrated.
- **Status**: open
- **Related tasks**: MP-05-05, MP-09-01, MP-10-01, MP-11-01, MP-12-01
- **Notes**: equipment, spells, potions, mage_items tables now exist (MP-09-01). Remaining: herbs, minerals, mines, farms, astral, potions status resets, tribe storage, etc.

### TD-003: Unmigrated account.php views

- **Type**: migration-gap
- **Discovered in**: MP-05-04
- **Description**: The PHP `account.php` contains ~20 sub-views. MP-05-04 ports the core account management (settings, password, name, profile). Remaining views (links, bugtrack, bugreport, changes, freeze, immunity, avatar upload, email change, style picker, vallars history, forum subscriptions, roleplay profile, ignored list, contacts, proposals) are not yet migrated.
- **Impact**: Low — these are secondary features. Players can still use the PHP versions during the migration window.
- **Action**: Port remaining views as needed, likely in dedicated tasks within later modules.
- **Fixable in existing task**: No — too many views for one task.
- **Needs new task**: Yes — consider grouping into 2-3 follow-up tasks.
- **Status**: open
- **Related tasks**: MP-05-04

### TD-004: Legacy compatibility code pervasive across codebase — RESOLVED

- **Type**: tech-debt
- **Discovered in**: Remediation pass (post MP-09-01)
- **Description**: Legacy semicolon-delimited format parsers (`parse_legacy_stats`, `parse_legacy_skills`, `parse_legacy_bonuses`, `PlayerSettings::from_legacy`/`to_legacy`), `_raw` column usage (`settings_raw`, `stats_raw`, `skills_raw`, `bonuses_raw`), fallback logic in data layer (`load_sub_models` with `_raw` fallback, `parse_legacy_sub_models`), and legacy naming convention (`from_legacy`/`as_legacy` on ~15 enum types) were spread throughout the codebase.
- **Impact**: Made the Rust code mimic PHP storage formats rather than using clean domain models. Created maintenance burden and confusion about canonical data paths.
- **Action**: Full remediation pass executed — removed all legacy parsers, removed `_raw` column reads/writes, renamed `from_legacy`/`as_legacy` to `from_db`/`to_db` (or `from_*_code`/`to_*_code`), rewrote character reset to use normalized tables, rewrote account activation to store JSONB settings. Added migration to drop `_raw` columns.
- **Fixable in existing task**: N/A — standalone remediation commit.
- **Needs new task**: No
- **Status**: resolved
- **Related tasks**: All prior tasks that created legacy artifacts, especially MP-02-04 through MP-05-06
- **Resolution**: Committed as `refactor(domain,data,web): remove legacy compatibility code`
