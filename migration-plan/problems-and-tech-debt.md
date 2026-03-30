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
- **Status**: resolved — all three crates are fully populated with real modules.
- **Related tasks**: MP-01-02 through MP-01-06, MP-02-xx onward

### TD-002: Character reset SQL references unmigrated gameplay tables

- **Type**: runtime-risk
- **Discovered in**: MP-05-05
- **Description**: `execute_full_reset` and `execute_partial_reset` in `crates/data/src/queries/character_reset.rs` perform DELETE/UPDATE on ~20 legacy gameplay tables (equipment, potions, herbs, mines, farms, astral, etc.) that haven't been created in Postgres migrations yet.
- **Impact**: Character reset will fail at runtime until those tables exist. The route itself is staged (not active in production).
- **Action**: Add migration for gameplay tables as those modules are ported (MP-09 through MP-12), or create a migration with empty stub tables.
- **Fixable in existing task**: No — depends on gameplay module migrations.
- **Needs new task**: No — will be resolved naturally as modules MP-09 through MP-12 are migrated.
- **Status**: resolved — migration 000026 adds smith, smith_work, jeweller, jeweller_work, astral_bank, astral_plans, tribe_oczek. Fixed `czary` → `spells` reference. Added tables to era_reset and reconcile.
- **Related tasks**: MP-05-05, MP-09-01, MP-10-01, MP-11-01, MP-12-01
- **Notes**: All tables referenced by character_reset.rs now exist in PostgreSQL migrations.

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

### TD-005: MP-06-02 made obsolete by architecture rule change

- **Type**: migration-gap
- **Discovered in**: State reconciliation audit
- **Description**: MP-06-02 ("Port legacy field parsing and serialization") was originally about parsing semicolon-delimited PHP storage formats. The legacy parsers were implemented, then removed during remediation. Under the updated architecture rules, no legacy data compatibility is needed — data lives in normalized PostgreSQL tables.
- **Impact**: None — the task's purpose no longer exists.
- **Action**: Marked obsolete in plan. Dependency edges from MP-06-03 updated.
- **Fixable in existing task**: N/A
- **Needs new task**: No
- **Status**: resolved
- **Related tasks**: MP-06-02, MP-06-03

### TD-006: Gnome craftsman bonus was under-calculated

- **Type**: bug
- **Discovered in**: MP-06-06
- **Description**: `apply_craftsman_bonus()` in `equipment.rs` computed Gnome bonus as `ceil(level/10) + ceil(level/20)` instead of `ceil(level/10) * 2`. PHP source (`rasa.php`) states "Gnomy mają podwojoną premię z profesji Rzemieślnik" — the base 1/10 bonus should be doubled, not supplemented by an additional 1/20.
- **Impact**: Gnome craftsman skills got +15% instead of the intended +20% at all levels.
- **Action**: Fixed formula to `base_bonus * 2` for Gnome. Updated unit test.
- **Fixable in existing task**: Yes — fixed in MP-06-06
- **Needs new task**: No
- **Status**: resolved
- **Related tasks**: MP-09-02, MP-06-06

### TD-007: `_pub` wrapper functions in formulas.rs

- **Type**: tech-debt
- **Discovered in**: MP-08-03
- **Description**: `stat_modified_pub()`, `skill_level_pub()`, and `bonus_value_pub()` are thin public wrappers around private helpers in `formulas.rs`, added so `battle.rs` can call them. The naming convention is awkward.
- **Impact**: Minor API clutter. The helpers should simply be made `pub` or `pub(crate)` instead of keeping private originals with separate wrappers.
- **Action**: Rename the private functions to `pub(crate)` and remove the `_pub` wrappers.
- **Fixable in existing task**: Yes — can be done during any combat or formula task.
- **Needs new task**: No
- **Status**: resolved — private functions made `pub(crate)` and `_pub` wrappers removed.
- **Related tasks**: MP-08-03

### TD-008: Astral planes and portal combat not yet migrated

- **Type**: migration-gap
- **Discovered in**: MP-07-03
- **Description**: The `portal.php` (magic portal boss fight) and `portals.php` (astral plane monsters) pages depend on `astral_plans` and `astral` tables that don't exist in PostgreSQL migrations yet. These features involve full combat encounters which are out of scope for MP-07-03. The travel handler shows a "disabled" notice for the portal entry.
- **Impact**: Players cannot access the magic portal or astral plane features until the tables and combat integration are implemented.
- **Action**: Create `astral_plans` and `astral` tables in a migration (likely part of MP-12 or a new task). Implement portal/astral combat integration once the combat system is connected.
- **Fixable in existing task**: No — requires dedicated table migration and combat wiring.
- **Needs new task**: Yes — portal combat + astral plane handlers.
- **Status**: open
- **Related tasks**: MP-07-03, MP-08 (combat system)

### TD-009: Bandit encounters during travel not yet implemented

- **Type**: migration-gap
- **Discovered in**: MP-07-03
- **Description**: PHP `travel.php` has a random bandit encounter system during overland travel (20% chance for caravan, 30% for walking). This includes fight/pay/escape options with stat-based outcomes. The Rust travel handler currently skips encounters and always completes travel successfully.
- **Impact**: Travel is easier than in PHP — no risk of bandit encounters or gold loss during transit. This simplifies gameplay but is explicitly out of scope per the plan.
- **Action**: Wire up bandit encounters once the combat system is available for integration (post MP-08).
- **Fixable in existing task**: No — depends on combat integration.
- **Needs new task**: Yes — travel encounter system.
- **Status**: open
- **Related tasks**: MP-07-03, MP-08 (combat system)

### TD-010: Donators table not in migrations

- **Type**: migration-gap
- **Discovered in**: MP-07-04
- **Description**: PHP `alley.php` reads from a `donators` table to show a donator list. No migration in `migrations/` creates this table. The Rust alley page skips donator display entirely.
- **Impact**: Minor — donator recognition list is empty until the table is created and seeded.
- **Action**: Add a migration for the `donators` table if this feature is desired.
- **Fixable in existing task**: No
- **Needs new task**: Yes — donators table migration.
- **Status**: resolved — migration 000027 creates the donators table. Alley handler loads and displays donators. Template updated.
- **Related tasks**: MP-07-04

### TD-011: Hermit resurrection system not yet migrated

- **Type**: migration-gap
- **Discovered in**: MP-07-04
- **Description**: PHP `gory.php` and `las.php` have a hermit resurrection system for dead players in mountains/forest (gold cost = 50 × condition stat, or wait timer). The Rust location hubs show dead state but don't implement resurrection.
- **Impact**: Dead players in mountains/forest can use the return-to-city link but cannot resurrect via the hermit. This is a secondary resurrection path — the hospital is the primary one.
- **Action**: Implement hermit resurrection when the resurrection/death system is fully scoped.
- **Fixable in existing task**: No — needs death/resurrection domain logic.
- **Needs new task**: Yes
- **Status**: open
- **Related tasks**: MP-07-04

### TD-012: Rest max-mana calculation missing equipment bonus

- **Type**: migration-gap
- **Discovered in**: MP-07-04
- **Description**: PHP `rest.php` includes an equipment bonus from the rod slot (`equip[8][2] / 100 * maxmana`). The Rust rest handler only uses the stat-based formula since the equipment module is not yet migrated.
- **Impact**: Mana cap may be lower than in PHP for players with rod equipment bonuses.
- **Action**: Wire in equipment bonus once the equipment/inventory module is available.
- **Fixable in existing task**: No — depends on equipment module.
- **Needs new task**: No — can be added when equipment is migrated.
- **Status**: open
- **Related tasks**: MP-07-04

### TD-013: Landfill work missing condition XP award

- **Type**: migration-gap
- **Discovered in**: MP-07-04
- **Description**: PHP `landfill.php` calls `$player->checkexp(array('condition' => amount))` to award condition stat XP equal to the energy spent. The Rust handler does not yet award stat XP because the stat XP progression system is not migrated.
- **Impact**: Players don't gain condition XP from landfill work. Low priority since XP system needs separate implementation.
- **Action**: Add stat XP awards once the stat progression system is migrated.
- **Fixable in existing task**: No
- **Needs new task**: No — part of stat progression module.
- **Status**: open
- **Related tasks**: MP-07-04, MP-06 (player progression)

### TD-014: Bank transfers and donations not yet migrated

- **Type**: migration-gap
- **Discovered in**: MP-10-05
- **Description**: The PHP `bank.php` is a complex transfer hub (1474 lines) supporting gold transfers, mithril transfers, mineral/herb/potion/item/equipment donations between players. MP-10-05 only migrated deposit/withdraw and the potion shop. The full transfer/donation system needs a dedicated task.
- **Impact**: Players cannot transfer gold/mithril/items to other players through the bank. Medium priority.
- **Action**: Create a dedicated task for bank transfers/donations covering gold, mithril, minerals, herbs, potions, items, and equipment.
- **Fixable in existing task**: No — too large and complex.
- **Needs new task**: Yes
- **Status**: open
- **Related tasks**: MP-10-05

### TD-015: Spell enchantment system not yet migrated

- **Type**: migration-gap
- **Discovered in**: MP-09-04
- **Description**: The PHP `czary.php` utility spell enchantment system (enhancing items with element-based bonuses using magic skill + intelligence checks) is not yet implemented. Utility spells are displayed in the spell book but enchantment actions are not available.
- **Impact**: Players cannot enchant items. Medium priority — requires combat skill/stat interaction.
- **Action**: Implement enchantment as a separate feature once combat skill checks are available.
- **Fixable in existing task**: No — depends on skill/stat progression integration.
- **Needs new task**: Yes
- **Status**: open
- **Related tasks**: MP-09-04, MP-08

### TD-016: Tribe forum post rate limiting not implemented

- **Type**: tech-debt
- **Discovered in**: MP-13-06
- **Description**: The PHP tribe forum enforced a 10-second cooldown between posts using `$_SESSION['posttime']`. The Rust session system does not yet support arbitrary session data storage, so rate limiting is deferred with a TODO comment.
- **Impact**: Low — users could spam posts. No data integrity risk.
- **Action**: Implement server-side rate limiting when session data store or middleware rate limiter is available.
- **Fixable in existing task**: No — requires session data infrastructure.
- **Needs new task**: Yes (or fold into session middleware enhancement)
- **Status**: open
- **Related tasks**: MP-13-06, MP-05

### TD-017: game_log column name inconsistency between handlers

- **Type**: tech-debt
- **Discovered in**: MP-12-02
- **Description**: `market.rs` queries use legacy column names (`owner, log, czas, type`) while `room.rs` uses correct migration column names (`owner_id, message, log_type`). The migration schema confirms room.rs is correct.
- **Impact**: Low — market queries may fail against the migrated schema.
- **Action**: Align `market.rs` game_log queries to use migration column names.
- **Fixable in existing task**: No — needs targeted fix in market handler.
- **Needs new task**: No — can be fixed as part of any market-touching task.
- **Status**: resolved — `insert_market_log` now uses `owner_id, message, log_type` matching the migration schema.
- **Related tasks**: MP-12-02

### TD-018: Exit type_filter lost in serialize_exits roundtrip

- **Type**: tech-debt
- **Discovered in**: MP-14-06
- **Description**: `serialize_exits` does not write back the `[T]`/`[E]` type-filter prefix. This is by design since filtering happens before DB storage, but it means a raw `→ parse → serialize → parse` roundtrip loses type_filter information.
- **Impact**: Low — filter is applied at mission start before storing active mission state; no runtime issue.
- **Action**: Consider adding filter prefix to `serialize_exits` if two-way parity is ever needed. Currently not a problem.
- **Fixable in existing task**: No
- **Needs new task**: No
- **Status**: open
- **Related tasks**: MP-14-06, MP-14-02

### TD-019: Labyrinth accumulation logic partially in web handler

- **Type**: tech-debt
- **Discovered in**: MP-14-06
- **Description**: The labyrinth step accumulation loop in `quest.rs` handler duplicates logic now available as domain's `process_labyrinth_steps`. The handler still has its own `StepRoll` struct and async accumulation due to `find_available_quest` and `try_find_map` async callbacks.
- **Impact**: Low — domain function exists and is tested; handler works correctly. Slight duplication.
- **Action**: Refactor handler to use domain `process_labyrinth_steps` with pre-resolved quest/map data, or accept the duplication as necessary for async boundary.
- **Fixable in existing task**: No — would need handler refactor.
- **Needs new task**: No — acceptable as-is.
- **Status**: open
- **Related tasks**: MP-14-04, MP-14-06

### TD-020: Duplicate chronicle routes caused router panic

- **Type**: bug
- **Discovered in**: MP-16-02
- **Description**: `/chronicle` and `/chronicle/{id}` were registered in both `quest_routes()` and `pages_routes()`. Axum 0.8 panics at startup on overlapping method routes. The `pages::chronicle_page` and `pages::chronicle_mission` handlers are content-browsing views while the quest versions are gameplay-aware (check location, chapter).
- **Impact**: **Critical** — the application could not start: router construction panicked. Discovery was accidental — only found by integration tests. Production was unaffected because the app was never started with both route groups active.
- **Action**: Removed the duplicate routes from `pages_routes()`. The quest module handlers remain as the authoritative versions. The pages module functions are still exported but unused.
- **Fixable in existing task**: Yes — fixed in MP-16-02.
- **Needs new task**: No
- **Status**: resolved
- **Related tasks**: MP-16-02, MP-14-04, MP-12-06
