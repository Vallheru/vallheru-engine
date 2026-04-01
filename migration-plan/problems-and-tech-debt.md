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
- **Description**: The `portal.php` (magic portal boss fight) and `portals.php` (astral plane monsters) pages need Rust handlers. All required tables exist (`astral` in migration 000012, `astral_bank`/`astral_plans` in 000026, `astral_machine` in 000033). The combat system is complete (Group C/I). The remaining gap is implementing the portal and portals handlers with combat integration.
- **Impact**: Players cannot access the magic portal or astral plane features until the handlers are implemented.
- **Action**: Implement `/portal` and `/portals` handlers with combat encounters using the existing combat system and astral tables.
- **Fixable in existing task**: No — requires dedicated handler implementation.
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
- **Status**: resolved — implemented hospital handler (`/hospital` with `?action=heal|resurrect`), hermit resurrection in mountains/forest location hubs (`?action=hermit|resurrect|back|wait`), domain module `vallheru_domain::hospital` with healing/resurrection cost calculations and death penalty logic, data layer queries for healing/resurrection/movement, and shared `do_resurrect` function used by both hospital and hermit paths. Templates updated for both flows. Hospital added to city navigation.
- **Related tasks**: MP-07-04

### TD-012: Rest max-mana calculation missing equipment bonus

- **Type**: migration-gap
- **Discovered in**: MP-07-04
- **Description**: PHP `rest.php` includes an equipment bonus from the rod slot (`equip[8][2] / 100 * maxmana`). The Rust rest handler only uses the stat-based formula since the equipment module is not yet migrated.
- **Impact**: Mana cap may be lower than in PHP for players with rod equipment bonuses.
- **Action**: Wire in equipment bonus once the equipment/inventory module is available.
- **Fixable in existing task**: No — depends on equipment module.
- **Needs new task**: No — can be added when equipment is migrated.
- **Status**: resolved — mage clothing (DB type 'C', PHP slot 8) power bonus now applied in compute_max_mana(). Commit `36153d6`.
- **Related tasks**: MP-07-04

### TD-013: Landfill work missing condition XP award
- **Impact**: Players don't gain condition XP from landfill work. Low priority since XP system needs separate implementation.
- **Action**: Add stat XP awards once the stat progression system is migrated.
- **Fixable in existing task**: No
- **Needs new task**: No — part of stat progression module.
- **Status**: resolved — landfill handler now calls `award_condition_xp` which uses `apply_stat_xp` to award condition XP equal to energy spent, with level-up handling and HP increase on condition level-up. Added `add_player_hp` data query.
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
- **Status**: resolved — `PostRateLimiter` added to `AppState` with 10-second cooldown, wired into both tribe forum topic and reply handlers. Unit tests added.
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

### TD-021: Gathering handlers calculate XP but never persist it

- **Type**: migration-gap
- **Discovered in**: TD-013 follow-up
- **Description**: Mountain mining, city mines, lumberjack, and smelter handlers all computed XP values and displayed them in flash messages, but never called `apply_stat_xp` / `apply_skill_xp` to save the XP to the database. Players would see "Zdobyłeś X PD" but their stats/skills never actually gained experience.
- **Impact**: **High** — all gathering XP silently discarded; stat/skill progression from gathering completely broken.
- **Action**: Created shared `apply_gathering_xp` helper in gathering.rs. Wired into all four handlers with correct stat/skill splits per PHP originals: mountain mining (strength + speed stats, mining skill), city mines (strength + speed stats, mining skill, each 1/3), lumberjack (strength stat, lumberjack skill), smelter (condition stat half, smelting skill half).
- **Fixable in existing task**: No — standalone fix.
- **Needs new task**: No
- **Status**: resolved
- **Related tasks**: TD-013

### TD-022: City navigation links point to wrong route paths

- **Type**: bug
- **Discovered in**: TD-021 follow-up audit
- **Description**: City navigation arrays in `city.rs` used `/outpost` (PHP name) instead of `/garrison` (Rust route) for the garrison missions link, `/grid` instead of `/labyrinth` for the labyrinth link, and `/bows` instead of `/fletcher` for the fletcher shop link. Players clicking these in Altara or Ardulith would get 404s or fall through to PHP.
- **Impact**: **Medium** — two city navigation links broken in both cities.
- **Action**: Fixed hrefs to `/garrison`, `/labyrinth`, and `/fletcher` matching the actual Rust route registrations in `world.rs`.
- **Fixable in existing task**: No — standalone fix.
- **Needs new task**: No
- **Status**: resolved
- **Related tasks**: TD-020

### TD-023: Unsafe `unwrap()` on `session_user` in staff handlers

- **Type**: tech-debt
- **Discovered in**: TD-021 follow-up audit
- **Description**: `court_add_comment`, `staff_jail_action`, `staff_chat_ban_action`, `staff_forum_ban_action`, and `staff_takeaway_action` used `ctx.session_user.as_ref().unwrap()`. Although protected by `require_any_rank` middleware, a misconfigured route could cause a runtime panic.
- **Impact**: **Low** — middleware guarantees safety, but pattern is fragile and inconsistent with the `let Some(ref user) = ... else { return redirect(...) }` convention used everywhere else.
- **Action**: Replaced all five `unwrap()` calls with `let Some(ref user) = ctx.session_user else { return redirect(...) }` pattern.
- **Fixable in existing task**: No — standalone fix.
- **Needs new task**: No
- **Status**: resolved
- **Related tasks**: None

### TD-024: Hospital route missing from fallback migration registry

- **Type**: bug
- **Discovered in**: TD-021 follow-up audit
- **Description**: The `/hospital` route was registered in `world.rs` but not listed in `fallback.rs::migrated_routes()`. Nginx reverse proxy uses this list to decide which routes go to Rust vs PHP — missing the entry means `/hospital` requests would be forwarded to PHP instead of Rust.
- **Impact**: **Medium** — hospital page would not work when behind the nginx proxy.
- **Action**: Added `r("/hospital", "world", RouteStatus::Staged)` to the migration registry.
- **Fixable in existing task**: No — standalone fix.
- **Needs new task**: No
- **Status**: resolved
- **Related tasks**: TD-011

### TD-025: Mining and lumberjack death does not persist HP = 0

- **Type**: bug
- **Discovered in**: TD-021 follow-up audit
- **Description**: When a player dies from a cave-in (mountain mining) or falling tree (lumberjack), `result.player_died` was set to `true` and a death message shown, but the player's HP was never set to 0 in the database. The player would see "you died" but remain alive on the next page load, bypassing the resurrection flow entirely.
- **Impact**: **Critical** — death in mining/lumberjack had no real consequence. Players never needed resurrection and never paid the death penalty (stat/skill loss).
- **Action**: Added `kill_player` query (`UPDATE players SET hp = 0`) in `crates/data/src/queries/player.rs`. Called from both `mining_work` and `lumberjack_work` handlers when `result.player_died` is true.
- **Fixable in existing task**: No — standalone fix.
- **Needs new task**: No
- **Status**: resolved
- **Related tasks**: TD-021

### TD-026: Outpost patrol XP bypasses skill level-up detection

- **Type**: bug
- **Discovered in**: TD-025 follow-up audit
- **Description**: The `grant_skill_exp` helper in outpost.rs did a raw SQL `UPDATE player_skills SET xp = xp + $1` without calling `progression::apply_skill_xp`. XP accumulated in the database but level-ups never fired — the skill level column was never incremented when XP thresholds were crossed.
- **Impact**: **High** — garrison patrol skill XP never caused level-ups. Players gained XP numbers but the skill level never actually increased.
- **Action**: Rewrote `grant_skill_exp` to load skills, apply `progression::apply_skill_xp` for proper level-up detection, save skills back, and return a message about any level-ups gained. Updated call sites to include level-up messages in flash text.
- **Fixable in existing task**: No — standalone fix.
- **Needs new task**: No
- **Status**: resolved
- **Related tasks**: TD-021, TD-025

### TD-027: No DB-level guard against negative currency balances

- **Type**: design-risk
- **Discovered in**: TD-025 follow-up audit
- **Description**: All 20+ SQL queries that deduct credits/platinum/bank do `credits = credits - $N` without an `AND credits >= $N` guard or a CHECK constraint. While every handler checks the balance in Rust before the deduction, a concurrent request can slip through the TOCTOU window and drive balances negative.
- **Impact**: **Medium** — race condition could allow gold/platinum/bank duplication via concurrent requests. Unlikely in normal play but exploitable.
- **Action**: Added migration 000028 with CHECK constraints: `credits >= 0`, `platinum >= 0`, `bank >= 0`. These make the DB the last line of defense. Handler-level checks remain for good UX messages.
- **Fixable in existing task**: No — standalone migration.
- **Needs new task**: No
- **Status**: resolved
- **Related tasks**: None

### TD-028: Broken /quest link in labyrinth result template

- **Type**: bug
- **Discovered in**: TD-027 follow-up audit
- **Description**: `labyrinth_result.html` had `href="/quest?id={{ quest_id }}"` but no `/quest` route exists in the Rust app. The correct route is `/chronicle/{{ quest_id }}`.
- **Impact**: **Medium** — clicking the "view quest" link after completing a labyrinth quest would 404.
- **Action**: Changed link to `href="/chronicle/{{ quest_id }}"` in `templates_jinja/labyrinth_result.html`.
- **Fixable in existing task**: No — standalone fix.
- **Needs new task**: No
- **Status**: resolved
- **Related tasks**: None

### TD-029: Stored XSS via library texts rendered with |safe

- **Type**: bug
- **Discovered in**: TD-028 follow-up audit
- **Description**: `library_add_action` and `library_admin_edit_action` stored user-submitted body text raw (no HTML escaping, no BBCode processing). The template rendered it with `{{ text.body|safe }}`, bypassing MiniJinja auto-escaping. Additionally, `get_library_text` did not filter by `is_approved`, allowing any user to access unapproved texts via `/library/text/{id}`.
- **Impact**: **Critical** — any user could submit a library text with `<script>` tags, then share the URL to execute arbitrary JavaScript in other users' browsers.
- **Action**: (a) Process body through `text::bbcode_to_html` (which html-escapes first) before storing in both `library_add_action` and `library_admin_edit_action`. (b) Added `is_approved` field to `LibraryTextRow` and the query. (c) Handler now blocks non-admin access to unapproved texts.
- **Fixable in existing task**: No — standalone fix.
- **Needs new task**: No
- **Status**: resolved
- **Related tasks**: None

### TD-030: Stored XSS via updates body rendered with |safe

- **Type**: bug
- **Discovered in**: TD-028 follow-up audit
- **Description**: `add_update_action` only replaced `\n` with `<br/>` — no HTML escaping. The template rendered with `{{ item.body|safe }}`. Staff/admin users could inject arbitrary HTML/JS.
- **Impact**: **High** — staff could inject scripts visible to all players via the updates page.
- **Action**: Replaced `body_raw.replace('\n', "<br/>")` with `text::bbcode_to_html(body_raw, &[], false)`, consistent with news.
- **Fixable in existing task**: No — standalone fix.
- **Needs new task**: No
- **Status**: resolved
- **Related tasks**: TD-029

### TD-031: Stored XSS via notes body rendered with |safe

- **Type**: bug
- **Discovered in**: TD-028 follow-up audit
- **Description**: `note_save` stored note body raw. Template rendered with `{{ n.body|safe }}`. Self-XSS only (notes visible to owning player), but inconsistent with other content processing.
- **Impact**: **Low** — self-XSS only, but should be sanitized for consistency.
- **Action**: Process body through `text::bbcode_to_html` before storing in `note_save`.
- **Fixable in existing task**: No — standalone fix.
- **Needs new task**: No
- **Status**: resolved
- **Related tasks**: TD-029

### TD-032: Roleplay/OOC fields rendered raw with |safe

- **Type**: bug
- **Discovered in**: TD-028 follow-up audit
- **Description**: `roleplay_view` passed `profile.roleplay` and `profile.ooc` directly to the template, which rendered with `|safe`. Legacy PHP-era data may contain unsanitized HTML.
- **Impact**: **Medium** — depends on PHP-era storage format. If raw HTML was stored, it would execute.
- **Action**: Process both fields through `text::bbcode_to_html` on read in `roleplay_view`.
- **Fixable in existing task**: No — standalone fix.
- **Needs new task**: No
- **Status**: resolved
- **Related tasks**: TD-029

### TD-033: Username uniqueness not enforced at DB level

- **Type**: design-risk
- **Discovered in**: TD-028 follow-up audit
- **Description**: `idx_players_username` was a regular INDEX, not UNIQUE. The `change_name` handler checks `is_username_taken` then updates, but concurrent rename requests to the same name could both pass the check (TOCTOU).
- **Impact**: **Medium** — race condition could create duplicate usernames.
- **Action**: Added migration 000029 that drops the old index and creates `CREATE UNIQUE INDEX idx_players_username ON players (LOWER(username))`.
- **Fixable in existing task**: No — standalone migration.
- **Needs new task**: No
- **Status**: resolved
- **Related tasks**: TD-027

### TD-034: /tower-clock dead link in city navigation

- **Type**: bug
- **Discovered in**: TD-028 follow-up audit
- **Description**: City navigation linked to `/tower-clock` which doesn't exist (PHP only had `tower.php`, mapped to `/tower` in Rust).
- **Impact**: **Low** — tower clock link 404'd.
- **Action**: Changed `/tower-clock` to `/tower` in both ALTARA_DISTRICTS and ARDULITH_DISTRICTS.
- **Fixable in existing task**: Yes — extends TD-022 city nav fixes.
- **Needs new task**: No
- **Status**: resolved
- **Related tasks**: TD-022

### TD-035: Market purchases lack transaction wrapping

- **Type**: design-risk
- **Discovered in**: TD-028 follow-up audit
- **Description**: All market purchase flows execute 3+ independent SQL statements (debit buyer, credit seller, transfer listing) without a transaction. Concurrent buyers can double-purchase the same listing or cause partial failures leaving inconsistent state.
- **Impact**: **Critical** — gold duplication/loss possible via concurrent market purchases.
- **Action**: Created `QuantityPurchase` struct and 6 transactional `purchase_*` functions in `queries/market.rs`. Each wraps debit buyer → credit seller → mutate listing → log in a single `pool.begin()` … `tx.commit()` transaction. Updated all 6 handler `execute_*_buy` functions to use the new transactional functions.
- **Fixable in existing task**: No — requires dedicated task.
- **Needs new task**: Yes
- **Status**: resolved
- **Related tasks**: TD-027

### TD-036: Outpost gold operations silently discard errors

- **Type**: bug
- **Discovered in**: TD-028 follow-up audit
- **Description**: Multiple outpost handlers use `let _ =` on gold-modifying SQL queries. If deduction succeeds but creation fails (or vice versa), state becomes inconsistent.
- **Impact**: **High** — gold loss or free outpost creation.
- **Action**: Wrapped outpost buy, treasury deposit, and treasury withdraw in `pool.begin()` … `tx.commit()` transactions with proper error handling. Remaining `let _ =` on non-financial operations are lower risk.
- **Fixable in existing task**: No — needs focused fix.
- **Needs new task**: No
- **Status**: resolved
- **Related tasks**: TD-035

### TD-037: NPC shop buy creates item before deducting gold

- **Type**: design-risk
- **Discovered in**: TD-028 follow-up audit
- **Description**: `buy_shop_equipment` INSERTs/UPDATEs the item then UPDATEs credits as separate queries. If credit deduction fails (CHECK constraint), item already exists — player gets free item.
- **Impact**: **Medium** — exploitable with credit CHECK constraint race.
- **Action**: Wrapped all three buy functions (`buy_shop_equipment`, `buy_bow`, `buy_arrows`) in `pool.begin()` … `tx.commit()` transactions with gold deducted first.
- **Fixable in existing task**: No — needs dedicated fix.
- **Needs new task**: No
- **Status**: resolved
- **Related tasks**: TD-027, TD-035

### TD-038: Bank deposit/withdraw TOCTOU race condition

- **Type**: design-risk
- **Discovered in**: TD-028 follow-up audit
- **Description**: Bank handler reads credits+bank, computes in Rust, writes absolute values back. Concurrent deposits can overwrite each other.
- **Impact**: **Medium** — deposit/withdrawal can be lost under concurrent requests.
- **Action**: Replaced `set_player_balance` (absolute value write) with `deposit_to_bank` and `withdraw_from_bank` functions using atomic relative SQL: `credits = credits - $1, bank = bank + $1 WHERE credits >= $1`. Combined with CHECK constraints from TD-027.
- **Fixable in existing task**: No — needs dedicated fix.
- **Needs new task**: No
- **Status**: resolved
- **Related tasks**: TD-027, TD-035

### TD-039: Missing /stats and /view player profile routes

- **Type**: migration-gap
- **Discovered in**: TD-028 follow-up audit
- **Description**: 10+ templates link to `/stats?id=N` or `/view?view=N` for player profiles, but no route or handler exists. Every player name link in mail, forums, chat, stafflist, memberlist, jail, court, and alley is broken.
- **Impact**: **High** — all player profile links are dead.
- **Action**: Implemented `/player/{id}` as canonical URL. Created handler with legacy redirects from `/view?view=N`, `/view/{id}`, `/stats?id=N`. Updated 17 links across 16 templates to use new canonical URL.
- **Fixable in existing task**: No — needs new task.
- **Needs new task**: Yes
- **Status**: resolved
- **Related tasks**: None

### TD-040: Missing /jail/escape route and handler

- **Type**: migration-gap
- **Discovered in**: TD-028 follow-up audit
- **Description**: Template renders escape link for thief-class prisoners but no handler or route exists. Half-implemented feature.
- **Impact**: **Medium** — jail escape link 404s for thief players.
- **Action**: Implemented `jail_escape` handler with escape chance calculation, success/failure branches, XP awards, and bail/sentence adjustments. Lockpick equipment bonus omitted (equipment system not yet migrated).
- **Fixable in existing task**: No — needs new task.
- **Needs new task**: Yes
- **Status**: resolved
- **Related tasks**: None

### TD-041: POST-only routes linked via GET `<a>` tags — 405 errors

- **Type**: bug
- **Discovered in**: Template audit
- **Description**: 8 template links used `<a href>` (HTTP GET) to reach POST-only routes: mail save/delete/block/clear, court comment delete, forum topic delete/close/sticky. All returned 405 Method Not Allowed.
- **Impact**: **High** — mail management, court moderation, and forum staff actions were completely broken.
- **Action**: Converted all affected links to inline `<form method="post">` with `.link-btn` styled submit buttons. Added `.link-btn` utility class to `base.html`. Removed dead `data-method="post"` attributes.
- **Fixable in existing task**: No
- **Needs new task**: No
- **Status**: resolved
- **Related tasks**: None

### TD-042: player_profile.html extends nonexistent layout.html

- **Type**: bug
- **Discovered in**: Template audit
- **Description**: `player_profile.html` extended `layout.html` which does not exist. Every other template extends `base.html`. This caused a runtime template-not-found crash on every profile page view.
- **Impact**: **Critical** — `/player/{id}` route was completely broken.
- **Action**: Changed `{% extends "layout.html" %}` to `{% extends "base.html" %}`.
- **Fixable in existing task**: Yes
- **Needs new task**: No
- **Status**: resolved
- **Related tasks**: TD-039

### TD-043: Forum search bypasses category visit permissions (IDOR)

- **Type**: bug
- **Discovered in**: Handler audit
- **Description**: `forum_search` accepted any `catid` from user input without checking `perm_visit`. Users could search restricted/staff-only forum categories by crafting a POST with any `catid`. The `rank` variable was loaded but explicitly discarded with `let _ = rank;`.
- **Impact**: **High** — information disclosure of staff-only forum content.
- **Action**: Added `get_category_perms` + `has_permission` check before search query. Removed dead `let _ = rank;`.
- **Fixable in existing task**: Yes
- **Needs new task**: No
- **Status**: resolved
- **Related tasks**: None

### TD-044: State-changing actions on GET routes (CSRF vulnerability)

- **Type**: bug
- **Discovered in**: Handler audit
- **Description**: Several state-changing operations were registered as GET routes: `/jail/escape` (deletes jail record, deducts energy, awards XP), `/hospital?action=heal|resurrect` (deducts gold, modifies HP/stats), `/mountains?action=back|resurrect` and `/forest?action=back|resurrect` (moves player, performs resurrection). GET routes for mutations are vulnerable to CSRF via image tags and link prefetching.
- **Impact**: **High** — attackers could trigger jail escape, healing, or resurrections via crafted links.
- **Action**: Converted `/jail/escape` to POST-only route. Split `/hospital` into GET (view) + POST (action). Added POST handler for `/mountains` and `/forest`. Updated templates to use `<form method="post">` for all state-changing actions.
- **Fixable in existing task**: Yes
- **Needs new task**: No
- **Status**: resolved
- **Related tasks**: TD-040

### TD-045: Market flash_and_redirect ignores redirect path

- **Type**: bug
- **Discovered in**: Handler audit
- **Description**: `flash_and_redirect()` in market.rs accepted a `_path` parameter but never used it — it rendered an inline `error.html` page instead. After market purchases, users saw a success page with no navigation instead of being redirected back to the market listing.
- **Impact**: **Medium** — poor post-purchase UX, no POST-Redirect-GET pattern.
- **Action**: Changed `flash_and_redirect` to use `redirect_after_post(path)` (303 See Other), implementing proper PRG pattern.
- **Fixable in existing task**: Yes
- **Needs new task**: No
- **Status**: resolved
- **Related tasks**: None

### TD-046: Outpost multi-step writes not transactional

- **Type**: bug
- **Discovered in**: Handler audit
- **Description**: Outpost handlers for army purchase, size upgrade, structure building, combat rounds, and veteran equipment performed multiple sequential DB writes without transactions. Partial failures could leave the game state inconsistent — e.g. gold deducted but army not added, or combat damage applied to one side but not the other.
- **Impact**: **Critical** — economic exploits and data corruption possible under concurrent load or transient DB errors.
- **Action**: Created 5 transactional wrapper functions (`purchase_army_tx`, `upgrade_size_tx`, `build_structure_tx`, `apply_combat_round`, `equip_veteran_item_tx`) and rewired all handlers to use them with proper error propagation.
- **Fixable in existing task**: Yes
- **Needs new task**: No
- **Status**: resolved
- **Related tasks**: None

### TD-047: Mail/room multi-step writes not transactional

- **Type**: bug
- **Discovered in**: Handler audit
- **Description**: Mail send inserted two messages (recipient + sender copy) without a transaction; room operations (destroy, leave, remove, invite) performed 2-4 writes non-atomically. Partial failures could leave orphaned messages, ghost room members, or inconsistent co-owner lists.
- **Impact**: **High** — data consistency issues, potential for ghost state.
- **Action**: Created transactional functions (`send_message_pair_tx`, `destroy_room_tx`, `remove_from_room_tx`, `invite_to_room_tx`, `leave_room_tx`) and rewired handlers with error propagation.
- **Fixable in existing task**: Yes
- **Needs new task**: No
- **Status**: resolved
- **Related tasks**: None

### TD-048: Silent error suppression in forum/court/chat handlers

- **Type**: bug
- **Discovered in**: Handler audit
- **Description**: 24 `let _ =` patterns across forum (9), court (4), and chat (11) handlers silently discarded database errors. Failed writes (topic creation, reply posting, message deletion, bans, etc.) would succeed from the user's perspective while data was lost.
- **Impact**: **High** — silent data loss, no operational visibility into failures.
- **Action**: Replaced all patterns with `if let Err(e)` — critical mutations use `tracing::error!` + redirect, non-critical side-effects use `tracing::warn!`.
- **Fixable in existing task**: Yes
- **Needs new task**: No
- **Status**: resolved
- **Related tasks**: None

### TD-049: Silent error suppression in all remaining handlers

- **Type**: bug
- **Discovered in**: Handler audit
- **Description**: 85 `let _ =` patterns across 9 handler files (mail, room, outpost, content, quest, moderation, jail, tribe_forum, pages) silently discarded database operation errors. Covered deletions, bans, state updates, rewards, logs, and admin actions.
- **Impact**: **High** — silent data loss and silent state corruption across all major subsystems.
- **Action**: Replaced all DB-related `let _ =` with `if let Err(e)` + appropriate tracing level (error for mutations, warn for non-critical side-effects). Zero DB `let _ =` patterns remain in handler layer.
- **Fixable in existing task**: Yes
- **Needs new task**: No
- **Status**: resolved
- **Related tasks**: TD-048

### TD-050: herb_deposit query does not deduct herbs from player

- **Type**: bug
- **Discovered in**: Group L tribe storage implementation
- **Description**: `queries/tribe.rs::herb_deposit()` builds a `player_sql` UPDATE query to deduct herbs from the depositing player, but the query is discarded with `let _ = player_sql;` and never executed. This means herbs are added to the tribe storage without being removed from the player inventory.
- **Impact**: **High** — herb duplication exploit: players can deposit herbs without losing them.
- **Action**: Execute the player deduction query within the same transaction as the tribe deposit.
- **Fixable in existing task**: No (requires careful review of player herb column names)
- **Needs new task**: Yes
- **Status**: resolved
- **Related tasks**: Group L tribe storage
- **Resolution**: Fixed in commit 0e16ca2 — added player_id param to herb_deposit, execute both queries in transaction. Also wrapped all tribe storage operations (deposit/give/reserve for armory, warehouse, herbs, minerals, and delete_reservations) in database transactions.

### TD-051: tribeastral.php and guilds2.php not yet implemented

- **Type**: migration-gap
- **Discovered in**: Group L tribe implementation
- **Description**: The astral vault page (`tribeastral.php` → `/tribe/astral`) and guild detail page (`guilds2.php` → `/guilds/gladiator`) were not implemented in the Group L commit. `guilds2.php` was later found to be already implemented as `guilds_gladiator()`. The astral vault was implemented with full deposit/give/safebox flows.
- **Impact**: Resolved.
- **Action**: Implemented in commit `90986b0`.
- **Fixable in existing task**: N/A
- **Needs new task**: No
- **Status**: resolved
- **Related tasks**: Group L tribe system
