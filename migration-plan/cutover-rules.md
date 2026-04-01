# Route-by-Route Cutover and Fallback Rules

Produced for **MP-16-04**. Defines the exact sequence for enabling Rust routes, keeping PHP fallbacks, and deciding when a route is safe to switch permanently.

## Deployment Topology

```text
[Client] → [Nginx] ─┬→ [Rust :3000]   (Axum — migrated routes)
                     └→ [PHP  :8080]   (PHP-FPM — everything else)
```

Nginx is the routing authority. It decides which backend serves each path. The Rust app has a `.fallback()` handler that returns a 404 diagnostic for any request it doesn't own.

## Cutover States

Each route has exactly one state at any time:

| State | Traffic | Backend | Description |
|---|---|---|---|
| **php** | All | PHP-FPM | Default. Nginx sends to PHP. |
| **staged** | None | Rust (registered but not proxied) | Route exists in Axum, not yet in Nginx upstream. Used for development/testing only. |
| **rust-primary** | All | Rust | Nginx sends to Rust. PHP route still exists as cold backup. |
| **retired** | All | Rust | PHP file removed or disabled. No fallback. |

The "shadowed" and "mirrored-read" intermediate states from earlier planning are collapsed into **staged** for simplicity—real traffic validation happens via integration tests and smoke checks, not production traffic duplication.

## Activation Mechanism

### Nginx Route List

The single source of truth for which backend serves each path is the Nginx configuration. A migration include file lists Rust-owned paths:

```nginx
# /etc/nginx/conf.d/rust-routes.conf — included by main server block

# Operational (always Rust)
location = /healthz     { proxy_pass http://rust:3000; }
location = /readyz      { proxy_pass http://rust:3000; }
location = /buildinfo   { proxy_pass http://rust:3000; }
location = /migration-status { proxy_pass http://rust:3000; }

# Static assets embedded in binary
location /static/ { proxy_pass http://rust:3000; }

# --- Cutover groups below (uncomment to activate) ---

# Group A: Auth & Account
# location = /login    { proxy_pass http://rust:3000; }
# location = /logout   { proxy_pass http://rust:3000; }
# location = /register { proxy_pass http://rust:3000; }
# ...
```

To activate a group: uncomment the block, `nginx -t && nginx -s reload`.

To roll back: comment it out, reload.

### Rust Migration Registry

The `migrated_routes()` function in `crates/web/src/routes/fallback.rs` tracks which routes the Rust app considers its own. The `/migration-status` endpoint returns this list as JSON. This is informational — it does not control traffic routing.

## Cutover Groups

Routes are grouped by transactional coherence. All routes in a group must cut over together to avoid split-brain behavior.

---

### Group 0: Operational (ALREADY LIVE)

These are infrastructure endpoints that have no PHP equivalent.

| Rust Path | Handler | State |
|---|---|---|
| `/healthz` | `health::healthz` | **rust-primary** |
| `/readyz` | `health::readyz` | **rust-primary** |
| `/buildinfo` | `health::buildinfo` | **rust-primary** |
| `/migration-status` | `fallback::migration_status` | **rust-primary** |
| `/static/*` | asset handler | **rust-primary** |
| `/rss` | `content::rss_feed` | **staged** |

**Rollback**: N/A — no PHP equivalent.

---

### Group A: Auth & Account Lifecycle

Must cut over together because login creates the session used by all other pages.

| PHP File | Rust Path | State |
|---|---|---|
| `index.php` (login) | `/login` | **staged** |
| `logout.php` | `/logout` | **staged** |
| `register.php` | `/register` | **staged** |
| `aktywacja.php` | `/activate` | **staged** |
| `reset.php` | `/lost-password` | **staged** |
| `preset.php` (pw confirm) | `/preset` | **staged** |
| `referrals.php` | `/referrals` | **staged** |
| `account.php` | `/account`, `/account/*` | **staged** |

**Pre-cutover checklist**:
1. Verify session compatibility: Rust sessions must be readable by PHP if any PHP routes remain active. Alternatively, cut over all remaining groups simultaneously.
2. Test: registration → activation email → login → city page → logout flow.
3. Verify password hashing upgrade (MD5→Argon2) works on login.

**Rollback**: Comment out Group A in Nginx. Users lose active Rust sessions but can log in again via PHP. No data loss.

**Constraint**: If PHP routes remain active after this group cuts over, the session store must be shared (both read from `tower_sessions` table in PostgreSQL). This is the highest-risk group.

---

### Group B: City Hub & Navigation

The core game loop entry point.

| PHP File | Rust Path | State |
|---|---|---|
| `city.php` | `/city` | **staged** |
| `map.php` | `/map` | **staged** |
| `travel.php` | `/travel` | **staged** |
| `las.php` | `/forest` | **staged** |
| `gory.php` | `/mountains` | **staged** |
| `alley.php` | `/alley` | **staged** |
| `rest.php` | `/rest` | **staged** |
| `landfill.php` | `/landfill` | **staged** |

**Pre-cutover checklist**:
1. Group A must be active (sessions required).
2. Verify city hub renders all navigation links correctly.
3. Verify travel between locations works.

**Rollback**: Comment out Group B. Players fall back to PHP city/travel.

**Known gaps** (documented in tech-debt register):
- TD-009: Bandit encounters during travel not implemented.
- TD-011: Hermit resurrection in mountains/forest not implemented.
- TD-013: Landfill condition XP not awarded.

---

### Group C: Player Profile & Progression

Player stats, training, and character display pages.

| PHP File | Rust Path | State | Notes |
|---|---|---|---|
| `stats.php` | `/stats` | **staged** | Handler: `character::stats_show` |
| `train.php` | `/train` | **staged** | Handler: `character::train_show` / `train_action` |
| `hof.php` | `/hall-of-fame` | **staged** | Handler: `character::hof_show` |
| `hof2.php` | `/hall-of-fame/machines` | **staged** | Handler: `character::hof_machines_show` |
| `ap.php` | `/action-points` | **staged** | Handler: `character::ap_show` / `ap_buy` |
| `klasa.php` | `/character/class` | **staged** | Handler: `character::class_show` / `class_select` |
| `rasa.php` | `/character/race` | **staged** | Handler: `character::race_show` / `race_select` |
| `view.php` | `/player/:id` | **active** | Already implemented: `player_profile::player_profile` |

**Pre-cutover checklist**:
1. Group A must be active.
2. These routes must be implemented before cutover.

**Status**: All routes implemented. Domain logic, handlers, templates, and routing in place. Ready for integration testing.

---

### Group D: Deity, Temple & Tower

Religious and tower gameplay.

| PHP File | Rust Path | State |
|---|---|---|
| `deity.php` | `/deity`, `/deity/*` | **staged** |
| `temple.php` | `/temple`, `/temple/*` | **staged** |
| `tower.php` | `/tower` | **staged** |

**Pre-cutover checklist**:
1. Groups A + B must be active.
2. Test deity selection, temple work/prayer, temple book/pantheon.

**Rollback**: Comment out Group D. No data impact — deity/temple are read-heavy.

---

### Group E: Equipment & NPC Shops

Item management and NPC purchase flows.

| PHP File | Rust Path | State |
|---|---|---|
| `equip.php` | `/equipment`, `/equipment/*` | **staged** |
| `weapons.php` | `/weapons`, `/weapons/*` | **staged** |
| `armor.php` | `/armor`, `/armor/*` | **staged** |
| `bows.php` | `/fletcher`, `/fletcher/*` | **staged** |
| `czary.php` | `/spellbook`, `/spellbook/*` | **staged** |
| `msklep.php` | `/magic-shop`, `/magic-shop/*` | **staged** |

**Pre-cutover checklist**:
1. Group A must be active.
2. Test: buy item → equip → unequip → sell → repair loop.
3. Verify gold deductions are correct.

**Rollback**: Comment out Group E. Item state is in the database — no inconsistency risk.

**Known gaps**:
- TD-015: Spell enchantment system not migrated.

---

### Group F: Economy & Banking

Financial operations.

| PHP File | Rust Path | State |
|---|---|---|
| `bank.php` | `/bank`, `/wealth` | **staged** |
| `zloto.php` | `/wealth` | **staged** (merged into bank) |

**Pre-cutover checklist**:
1. Group A must be active.
2. Test deposit/withdraw/currency exchange.

**Rollback**: Comment out Group F. Bank balances are in database.

**Known gaps**:
- TD-014: Player-to-player transfers and donations not migrated.

---

### Group G: Player Markets

All player-to-player trading.

| PHP File | Rust Path | State |
|---|---|---|
| `market.php` | `/market` | **staged** |
| `amarket.php` | `/market/alchemy` | **staged** (unified) |
| `cmarket.php` | `/market/core` | **staged** (unified) |
| `hmarket.php` | `/market/herbs` | **staged** (unified) |
| `imarket.php` | `/market/items` | **staged** (unified) |
| `lmarket.php` | `/market/lumber` | **staged** (unified) |
| `mmarket.php` | `/market/minerals` | **staged** (unified) |
| `pmarket.php` | `/market/potions` | **staged** (unified) |
| `rmarket.php` | `/market/rings` | **staged** (unified) |

**Note**: PHP has separate files per market type. Rust unifies them under `/market/{slug}`. All markets must cut over together.

**Pre-cutover checklist**:
1. Groups A + F must be active (markets use banking).
2. Test: list offer → buy offer → cancel offer flow.

**Rollback**: Comment out Group G. Market offers are in database.

---

### Group H: Gathering & Crafting

Resource collection and crafting systems.

| PHP File | Rust Path | State |
|---|---|---|
| `kopalnia.php` | `/mining` | **staged** |
| `mines.php` | `/mines`, `/mines/dig` | **staged** |
| `smelter.php` | `/smelter`, `/smelter/*` | **staged** |
| `lumberjack.php` | `/lumberjack` | **staged** |
| `farm.php` | `/farm` | **staged** |

**Pre-cutover checklist**:
1. Group A must be active.
2. Test: mine → smelt → sell flow.

**Rollback**: Comment out Group H. Resource state is in database.

| `kowal.php` | `/smithy`, `/smithy/*` | **staged** |
| `crafts.php` | `/crafts`, `/crafts/*` | **staged** |
| `jeweller.php` | `/jeweller`, `/jeweller/*` | **staged** |
| `jewellershop.php` | `/jeweller/shop`, `/jeweller/shop/*` | **staged** |
| `alchemik.php` | `/alchemy`, `/alchemy/*` | **staged** |
| `lumbermill.php` | `/lumbermill`, `/lumbermill/*` | **staged** |
| `core.php` | `/core`, `/core/*` | **staged** |
| `thieves.php` | `/thieves`, `/thieves/*` | **staged** |

---

### Group I: Combat & Encounters

| PHP File | Rust Path | State |
|---|---|---|
| `battle.php` | `/battle/pve` | **staged** |
| `hunters.php` | `/hunters`, `/hunters/*` | **staged** |
| `explore.php` | `/explore`, `/explore/*` | **staged** |
| `hospital.php` | `/hospital` | **staged** |
| `wieza.php` | `/tower/combat` | **not implemented** |

**Status**: Combat handlers are implemented. Explore (PvE random encounters), battle (PvE combat), arena (PvP), and hunters guild are fully wired. Hospital is in `location_routes()`. Only `wieza.php` tower combat is not yet migrated.

**Pre-cutover checklist**:
1. Groups A + B must be active.
2. Test: explore → encounter → fight → win/lose → hospital flow.
3. Test: arena challenge → PvP fight → result.
4. Test: hunters guild → bestiary → quest accept → quest fight.

**Rollback**: Comment out Group I. Combat state is in database.

---

### Group J: Social & Communication

| PHP File | Rust Path | State |
|---|---|---|
| `chat.php` | `/chat` | **staged** |
| `chatmsgs.php` | `/chat/messages` | **staged** |
| `room.php` | `/room`, `/room/*` | **staged** |
| `roommsgs.php` | `/room/messages` | **staged** |
| `mail.php` | `/mail`, `/mail/*` | **staged** |
| `forums.php` | `/forums`, `/forums/*` | **staged** |

**Pre-cutover checklist**:
1. Group A must be active.
2. Test: send chat → see message → send mail → read mail flow.
3. Test: forum post → reply → delete flow.

**Rollback**: Comment out Group J. Message data is in database.

---

### Group K: Content & Publishing

| PHP File | Rust Path | State |
|---|---|---|
| `news.php` | `/news` | **staged** |
| `addnews.php` | `/news/add` | **staged** |
| `updates.php` | `/updates` | **staged** |
| `addupdate.php` | `/updates/add` | **staged** |
| `newspaper.php` | `/newspaper`, `/newspaper/*` | **staged** |
| `polls.php` | `/polls`, `/polls/*` | **staged** |
| `proposals.php` | `/proposals/*` | **staged** |
| `notatnik.php` | `/notes`, `/notes/*` | **staged** |
| `library.php` | `/library`, `/library/*` | **staged** |
| `roleplay.php` | `/roleplay/{id}` | **staged** |
| `chronicle.php` | `/chronicle`, `/chronicle/*` | **staged** |

**Pre-cutover checklist**:
1. Group A must be active.
2. Test: read news → add news → approve news flow.

**Rollback**: Comment out Group K. Content is in database.

---

### Group L: Tribes & Team Management

| PHP File | Rust Path | State |
|---|---|---|
| `tribes.php` | `/tribe` | **staged** |
| `tribeadmin.php` | `/tribe/admin` | **staged** |
| `tribearmor.php` | `/tribe/armory` | **staged** |
| `tribeastral.php` | `/tribe/astral` | staged |
| `tribeherbs.php` | `/tribe/herbs` | **staged** |
| `tribeminerals.php` | `/tribe/minerals` | **staged** |
| `tribeware.php` | `/tribe/warehouse` | **staged** |
| `team.php` | `/team` | **staged** |
| `guilds.php` | `/guilds` | **staged** |
| `guilds2.php` | `/guilds/gladiator` | staged |
| `tforums.php` | `/tforums`, `/tforums/*` | **staged** |

**Status**: Tribe management, storage (armory/warehouse/herbs/minerals), admin, guilds, and team handlers implemented. Tribe astral machine and guilds detail page remain. Tribe forums previously staged.

---

### Group M: Outposts & Garrison

| PHP File | Rust Path | State |
|---|---|---|
| `outposts.php` | `/outposts`, `/outposts/*` | **staged** |
| `outpost.php` | `/garrison`, `/garrison/*` | **staged** |

**Pre-cutover checklist**:
1. Groups A + E must be active (outposts use equipment).
2. Test: buy outpost → upgrade → hire army → battle → collect taxes flow.
3. Test: generate garrison → execute garrison mission.

**Rollback**: Comment out Group M. Outpost state is in database.

---

### Group N: Quests & Missions

| PHP File | Rust Path | State |
|---|---|---|
| `grid.php` | `/labyrinth`, `/labyrinth/explore` | **staged** |
| `chronicle.php` | `/chronicle`, `/chronicle/*` | **staged** |
| `mission.php` | `/mission` | **staged** |
| `maze.php` | `/maze`, `/maze/explore` | **staged** |

**Pre-cutover checklist**:
1. Groups A + B must be active.
2. Test: start maze → explore steps → complete.
3. Test: start chronicle → advance mission → complete.

**Rollback**: Comment out Group N. Quest state is in database.

**Note**: Chronicle has routes in both Group K (content display) and Group N (quest gameplay). Both must cut over together if they share the same `/chronicle` prefix, or the routes must be disambiguated.

---

### Group O: Staff & Moderation

| PHP File | Rust Path | State |
|---|---|---|
| `staff.php` | `/staff` | **staged** |
| `bugtrack.php` | `/staff/bugreport`, `/staff/bugreport/*` | **staged** |
| `log.php` | `/staff/logs` | **staged** |
| `jail.php` | `/jail`, `/jail/*` | **staged** |
| `stafflist.php` | `/stafflist` | **staged** |
| `sedzia.php` | `/judge` | **staged** |
| `court.php` | `/court`, `/court/*` | **staged** |

**Pre-cutover checklist**:
1. Group A must be active.
2. Test: staff panel → jail player → unjail flow.
3. Test: approve news, view logs.

**Rollback**: Comment out Group O. Moderation data is in database.

---

### Group P: Admin Panel

| PHP File | Rust Path | State |
|---|---|---|
| `admin.php` | `/admin` | **staged** |

**Pre-cutover checklist**:
1. Groups A + O must be active.
2. Test: admin panel loads and shows correct information.

**Rollback**: Comment out Group P.

---

### Group Q: Member Directory

| PHP File | Rust Path | State |
|---|---|---|
| `memberlist.php` | `/memberlist` | **staged** |

**Pre-cutover checklist**:
1. Group A must be active.
2. Test: member list loads with pagination and search.

**Rollback**: Comment out Group Q.

---

### Group Z: House

| PHP File | Rust Path | State |
|---|---|---|
| `house.php` | `/house`, `/house/*` | **staged** |

**Pre-cutover checklist**:
1. Groups A + F must be active (house uses gold).
2. Test: buy land → build → rest → sell flow.

**Rollback**: Comment out Group Z.

---

## Not Migrated

| PHP File | Reason |
|---|---|
| `source.php` | Source code viewer — will not be migrated. |
| `portal.php` | Astral portal boss fight — depends on astral tables + combat (TD-008). |
| `portals.php` | Astral plane monsters — depends on astral tables + combat (TD-008). |
| `warehouse.php` | Item warehouse — not yet implemented. |

---

## Cutover Sequence

Recommended activation order, from lowest to highest risk:

| Order | Group | Risk | Reason |
|---|---|---|---|
| 1 | **0: Operational** | None | Already live. |
| 2 | **A: Auth** | **High** | Session compatibility is the critical concern. All subsequent groups depend on this. |
| 3 | **B: City & Navigation** | Low | Read-heavy, few writes. Immediate player visibility. |
| 4 | **D: Deity/Temple/Tower** | Low | Isolated, low write frequency. |
| 5 | **E: Equipment & Shops** | Medium | Writes to inventory/gold — validate carefully. |
| 6 | **F: Economy & Banking** | Medium | Financial operations need exact parity. |
| 7 | **G: Markets** | Medium | Depends on F. Multi-player transactions. |
| 8 | **H: Gathering & Crafting** | Low | Resource operations, mostly self-contained. |
| 9 | **J: Social** | Low | Chat/mail/forums — high volume, low complexity. |
| 10 | **K: Content** | Low | News/polls/library — mostly read operations. |
| 11 | **M: Outposts** | Medium | Complex write operations (battle, treasury). |
| 12 | **N: Quests** | Medium | Stateful quest progression. |
| 13 | **Z: House** | Low | Isolated gameplay feature. |
| 14 | **Q: Memberlist** | None | Read-only directory. |
| 15 | **O: Staff & Moderation** | Low | Staff-only, low traffic. |
| 16 | **P: Admin** | Low | Admin-only, single page. |

Group L (Tribes) is blocked until its handlers are implemented.

Groups C (Player Profile), H (Gathering & Crafting), and I (Combat) are now **staged** and ready for cutover.

---

## Rollback Rules

### General Rollback Procedure

1. Comment out the group's location blocks in `rust-routes.conf`.
2. Run `nginx -t && nginx -s reload`.
3. Verify PHP is serving the rolled-back routes.
4. Investigate the issue. No data migration is needed — both backends share the same PostgreSQL database.

### Rollback Constraints

- **Group A (Auth) rollback**: terminates all active Rust sessions. Users must log in again via PHP. This is acceptable but disruptive.
- **Rolling back Group A while other groups are live is not safe** — it would break session resolution for all Rust routes. If Group A must roll back, all other groups must roll back first.
- **No partial group rollback**: all routes in a group go together.

### Time-to-rollback target

Each rollback should take < 60 seconds (comment block, nginx reload).

---

## Verification Before Cutover

Before activating any group:

1. **Integration tests pass** for all routes in the group.
2. **Smoke test** the Rust binary: `cargo run -- --help`.
3. **Manual walkthrough** of the critical user journey for the group.
4. **Database schema matches**: run `cargo run -- migrate` to ensure PostgreSQL schema is current.
5. **Check `/migration-status`** endpoint shows the routes as registered.

---

## Post-Cutover Monitoring

After activating a group:

1. Watch application logs for errors (first 15 minutes).
2. Verify no 500 responses from the Rust backend.
3. Spot-check the affected pages manually.
4. If errors > threshold: roll back immediately, investigate offline.

---

## Decision Criteria: When Is a Route "Safe to Retire"?

A route can move from **rust-primary** to **retired** (PHP file removed) when:

1. The route has been rust-primary for ≥ 7 days with no rollback.
2. No PHP-specific session data or file-system state is required.
3. The route's integration tests pass in CI.
4. The route is covered by at least one invariant test (if it involves currency, items, or combat).
5. The operator confirms retirement via checklist (see MP-16-09).
