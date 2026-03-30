# Data Reconciliation and Rollback Procedures

Produced for **MP-16-05**. Defines repeatable reconciliation checklists, rollback steps, and data ownership transitions for each cutover batch.

## Overview

Both the PHP and Rust applications share the same PostgreSQL database. This means:

- **No data migration between backends** is needed during cutover — both read/write the same tables.
- **Rollback is a routing change**, not a data operation. Traffic switches back to PHP via Nginx.
- **Data repair** is only needed if a Rust bug corrupts rows. The reconciliation tool detects this.

## Pre-Cutover Reconciliation Checklist

Run this checklist **before activating any cutover group**.

### 1. Schema Verification

```bash
# Ensure all migrations have been applied
vallheru migrate

# Verify the schema version
psql "$DATABASE_URL" -c "SELECT * FROM _sqlx_migrations ORDER BY installed_on DESC LIMIT 5;"
```

### 2. Data Reconciliation Report

```bash
# PG-only mode (no legacy MySQL comparison)
vallheru reconcile

# With legacy MySQL comparison (if legacy DB is still available)
# Set legacy_url in vallheru.toml under [database] or via VALLHERU_DATABASE__LEGACY_URL
vallheru reconcile
```

The report checks ~60 tables and classifies results:

| Severity | Meaning | Action |
|---|---|---|
| OK | Row counts match (or meet minimum threshold) | None |
| INFO | PG has more rows than MySQL (expected for seed data) | Review if unexpected |
| WARN | PG has fewer rows (within 10% tolerance) | Investigate before cutover |
| ERROR | PG empty when rows expected, or >10% deficit | **Block cutover** — fix before proceeding |

**Gate**: Zero ERROR entries required before cutover.

### 3. Application Health

```bash
# Binary starts and responds to health checks
curl -f http://localhost:3000/healthz
curl -f http://localhost:3000/readyz

# Route registry is complete
curl -s http://localhost:3000/migration-status | jq '.routes | length'
```

### 4. Integration Tests

```bash
cargo test --workspace --all-targets --all-features
```

All tests must pass. No exceptions.

---

## Per-Group Reconciliation

Each cutover group (defined in [cutover-rules.md](cutover-rules.md)) has specific data to validate.

### Group A: Auth & Account

**Tables**: `players`, `sessions`, `password_resets`, `activations`, `vallar_history`

| Check | Command | Expected |
|---|---|---|
| Player count matches | `SELECT COUNT(*) FROM players;` | Same as legacy |
| Sessions table exists | `SELECT COUNT(*) FROM sessions;` | ≥ 0 (populated at runtime) |
| Password hash format | `SELECT COUNT(*) FROM players WHERE password LIKE '$argon2%';` | ≥ 0 (upgrades on login) |

**Data ownership note**: After Group A activation, the Rust app creates sessions in `sessions`. PHP sessions (if any remain) must also read from this table or be disabled.

### Group B: City & Navigation

**Tables**: `players` (location fields), `revent`

| Check | Command | Expected |
|---|---|---|
| Player locations valid | `SELECT DISTINCT location FROM players;` | Known location values |
| Random events | `SELECT COUNT(*) FROM revent;` | ≥ 0 |

### Group D: Deity, Temple & Tower

**Tables**: `players` (deity, temple fields)

| Check | Command | Expected |
|---|---|---|
| Deity values | `SELECT DISTINCT deity FROM players;` | Known deity values or NULL |

### Group E: Equipment & Shops

**Tables**: `equipment`, `spells`, `mage_items`, `bows`, `tools`, `plans`, `rings`

| Check | Command | Expected |
|---|---|---|
| Catalog populated | `vallheru reconcile` (bows, rings, tools, plans sections) | All OK |
| Equipment references valid | `SELECT COUNT(*) FROM equipment WHERE player_id NOT IN (SELECT id FROM players);` | 0 |

### Group F: Economy & Banking

**Tables**: `players` (gold, bank, credits fields)

| Check | Command | Expected |
|---|---|---|
| No negative balances | `SELECT COUNT(*) FROM players WHERE gold < 0 OR bank < 0;` | 0 |

### Group G: Markets

**Tables**: `amarket`, `hmarket`, `pmarket`, `core_market`

| Check | Command | Expected |
|---|---|---|
| Market offer integrity | `SELECT COUNT(*) FROM amarket WHERE player_id NOT IN (SELECT id FROM players);` | 0 per table |
| Price sanity | `SELECT COUNT(*) FROM amarket WHERE price <= 0;` | 0 per table |

### Group H: Gathering & Crafting

**Tables**: `mines`, `mines_search`, `smelter`, `lumberjack`, `farms`

| Check | Command | Expected |
|---|---|---|
| Mine ownership | `SELECT COUNT(*) FROM mines WHERE player_id NOT IN (SELECT id FROM players);` | 0 |

### Group J: Social & Communication

**Tables**: `chat_messages`, `rooms`, `room_messages`, `mail_messages`, `forum_categories`, `forum_topics`, `forum_replies`

| Check | Command | Expected |
|---|---|---|
| Forum structure | `SELECT COUNT(*) FROM forum_categories;` | > 0 |
| Foreign keys valid | `SELECT COUNT(*) FROM mail_messages WHERE sender_id NOT IN (SELECT id FROM players);` | 0 |

### Group K: Content

**Tables**: `news`, `game_updates`, `newspaper_articles`, `polls`, `poll_options`, `notes`, `library_texts`, `chronicle_missions`

| Check | Command | Expected |
|---|---|---|
| News exists | `SELECT COUNT(*) FROM news;` | ≥ 0 |
| Polls have options | `SELECT p.id FROM polls p WHERE NOT EXISTS (SELECT 1 FROM poll_options o WHERE o.poll_id = p.id);` | 0 rows |

### Group M: Outposts

**Tables**: `outposts`, `outpost_monsters`, `outpost_veterans`

| Check | Command | Expected |
|---|---|---|
| Outpost owners valid | `SELECT COUNT(*) FROM outposts WHERE owner_id NOT IN (SELECT id FROM players);` | 0 |

### Group N: Quests

**Tables**: `quests`, `questaction`, `chronicle_missions`

| Check | Command | Expected |
|---|---|---|
| Quest state valid | `SELECT COUNT(*) FROM quests WHERE player_id NOT IN (SELECT id FROM players);` | 0 |

### Group O: Staff & Moderation

**Tables**: `bugreport`, `bug_comments`, `court_cases`, `court`, `jail`, `game_log`, `game_log_daily`, `bans`

| Check | Command | Expected |
|---|---|---|
| Active bans consistent | `SELECT COUNT(*) FROM bans WHERE until_date < NOW() AND active = true;` | Review expired active bans |

---

## Rollback Procedures

### General Rollback (Any Group)

**Time target**: < 60 seconds.

```bash
# 1. Comment out the group in Nginx config
vim /etc/nginx/conf.d/rust-routes.conf
# Comment the relevant location blocks

# 2. Test and reload
nginx -t && nginx -s reload

# 3. Verify PHP is serving
curl -I http://localhost/city.php
# Should return 200 from PHP-FPM
```

**Data state**: No data repair needed. Both backends use the same database. PHP will see whatever state Rust left the data in — this is safe because both follow the same schema.

### Group A Rollback (Special Case)

Rolling back Group A (Auth) is the most disruptive operation because all other Rust routes depend on Rust-issued sessions.

```bash
# 1. Roll back ALL active Rust route groups (in reverse activation order)
# Edit rust-routes.conf and comment out ALL groups

# 2. Reload Nginx
nginx -t && nginx -s reload

# 3. Users will need to log in again via PHP
# Active sessions are invalidated because PHP issues its own sessions
```

**Constraint**: Never roll back Group A alone while other groups are active. Always roll back all groups when rolling back Auth.

### Data Repair (Emergency Only)

If a Rust bug has corrupted data:

```bash
# 1. Roll back the affected group immediately
# 2. Assess damage scope
psql "$DATABASE_URL" -c "SELECT * FROM game_log WHERE created_at > NOW() - INTERVAL '1 hour' ORDER BY created_at DESC;"

# 3. If damage is limited to known rows, repair manually
# Use game_log to identify affected records

# 4. If damage is widespread, consider point-in-time recovery
# This requires PostgreSQL WAL archiving to be configured
```

---

## Data Ownership Transitions

### During Mixed Operation (PHP + Rust)

| Data | Owner | Notes |
|---|---|---|
| Sessions | Whichever backend issued them | Rust sessions in `sessions` table. PHP sessions may use separate mechanism. |
| Player state | Shared | Both backends write to `players` table. Schema is identical. |
| Game log | Both | Both backends write to `game_log`. |
| Catalog data | Static | Reference tables (`monsters`, `bows`, etc.) are read-only after import. |

### After Full Cutover

| Data | Owner | Notes |
|---|---|---|
| All tables | Rust | PHP is offline. Rust is the sole writer. |
| Sessions | Rust (`sessions` table) | Tower-sessions manages lifecycle. |
| Scheduled jobs | Rust CLI | `vallheru job energy-tick`, `vallheru job daily-reset` via cron/systemd. |
| Schema migrations | Rust (`vallheru migrate`) | sqlx migrations in `migrations/` directory. |

---

## Reconciliation Schedule

| When | Action |
|---|---|
| Before each group cutover | Run full reconciliation checklist + group-specific checks |
| First hour after cutover | Monitor logs, spot-check affected pages |
| Daily during transition | Run `vallheru reconcile` to verify table integrity |
| After 7-day soak per group | Mark group eligible for retirement (see cutover-rules.md) |
| After all groups retired | Shut down PHP-FPM, remove PHP config from Nginx |

---

## Emergency Contacts and Escalation

| Severity | Action |
|---|---|
| Single route 500 error | Roll back group, investigate in staging |
| Data inconsistency (< 10 rows) | Roll back group, repair rows manually, re-cutover |
| Data inconsistency (> 10 rows) | Roll back ALL groups, assess via `vallheru reconcile`, consider PITR |
| Session failures | Roll back Group A (and all downstream), restore PHP sessions |
| Database unavailable | Both PHP and Rust are down — standard DB recovery procedures apply |
