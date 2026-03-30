# Production Runbook

Produced for **MP-16-08**. Complete operational guide for the Rust Vallheru engine in production.

## Architecture

```
[Client] → [Nginx :80] → [Rust :3000] → [PostgreSQL :5432]
```

- **Nginx**: reverse proxy, TLS termination, static upload serving, security headers.
- **Rust binary** (`vallheru`): single statically-linked binary. All templates, CSS, JS embedded. No writable directories required.
- **PostgreSQL 16**: sole data store. Sessions, player data, catalog data, game log.

No PHP-FPM. No MySQL. No Smarty template cache. No writable `templates_c/`.

---

## Prerequisites

| Component | Version | Notes |
|---|---|---|
| PostgreSQL | 16+ | `pg_isready` for health checks |
| Nginx | 1.24+ | Optional, can run Rust directly |
| Docker + Compose | 24+ / v2 | For container deployment |

---

## Startup

### Option A: Docker Compose (Recommended)

```bash
# 1. Set required environment
export SESSION_SECRET="$(openssl rand -base64 96)"
export POSTGRES_PASSWORD="$(openssl rand -base64 32)"
export GAME_BASE_URL="https://your-domain.com"

# 2. First-time setup: build, migrate, seed, create admin
docker compose -f compose.prod.yaml build
docker compose -f compose.prod.yaml run --rm app migrate
docker compose -f compose.prod.yaml run --rm app import
docker compose -f compose.prod.yaml run --rm app bootstrap \
  --admin-user Admin \
  --admin-email admin@example.com \
  --admin-password "$(openssl rand -base64 24)"

# 3. Start all services
docker compose -f compose.prod.yaml up -d

# 4. Verify
curl -f http://localhost/healthz     # returns 200 "ok"
curl -f http://localhost/readyz      # returns 200 when DB is reachable
curl -s http://localhost/buildinfo   # returns JSON with git hash and version
```

### Option B: Bare Metal / systemd

```bash
# 1. Build release binary
cargo build --release
# Binary at: target/release/vallheru

# 2. Copy binary + config
cp target/release/vallheru /usr/local/bin/vallheru
cp vallheru.sample.toml /etc/vallheru/vallheru.toml
# Edit /etc/vallheru/vallheru.toml with real values

# 3. Migrate + seed
vallheru -c /etc/vallheru/vallheru.toml migrate
vallheru -c /etc/vallheru/vallheru.toml import
vallheru -c /etc/vallheru/vallheru.toml bootstrap \
  --admin-user Admin \
  --admin-email admin@example.com \
  --admin-password changeme

# 4. Create systemd unit (see below)
# 5. Start
systemctl enable --now vallheru
```

#### systemd Unit

```ini
# /etc/systemd/system/vallheru.service
[Unit]
Description=Vallheru Game Engine
After=network.target postgresql.service
Requires=postgresql.service

[Service]
Type=exec
User=vallheru
Group=vallheru
ExecStart=/usr/local/bin/vallheru -c /etc/vallheru/vallheru.toml serve
Restart=on-failure
RestartSec=5

# Security hardening
NoNewPrivileges=yes
ProtectSystem=strict
ProtectHome=yes
ReadOnlyPaths=/etc/vallheru
PrivateTmp=yes

# Environment (alternative to config file)
# EnvironmentFile=/etc/vallheru/env

[Install]
WantedBy=multi-user.target
```

---

## Configuration

### Config File

Copy `vallheru.sample.toml` and customize. File location resolved in order:

1. `--config <path>` CLI flag
2. `VALLHERU_CONFIG` environment variable
3. `vallheru.toml` in the working directory (if it exists)
4. Environment variables only (no file)

### Environment Variables

All settings can be provided via environment variables with the `VALLHERU_` prefix. Double underscores separate sections:

| Variable | Required | Default | Description |
|---|---|---|---|
| `VALLHERU_DATABASE__URL` | **Yes** | — | PostgreSQL connection string |
| `VALLHERU_SESSION__SECRET` | **Yes** | — | Session signing key (≥ 64 chars) |
| `VALLHERU_GAME__NAME` | **Yes** | — | Game instance display name |
| `VALLHERU_GAME__EMAIL` | **Yes** | — | Contact email shown to players |
| `VALLHERU_GAME__BASE_URL` | **Yes** | — | Public URL (no trailing slash) |
| `VALLHERU_GAME__ADMIN_NAME` | **Yes** | — | Admin display name |
| `VALLHERU_GAME__ADMIN_EMAIL` | **Yes** | — | Admin contact email |
| `VALLHERU_GAME__LANG` | No | `pl` | Default locale code |
| `VALLHERU_GAME__PLAYER_LIMIT` | No | `0` | Max registered players (0 = unlimited) |
| `VALLHERU_SERVER__BIND` | No | `0.0.0.0:3000` | Listen address |
| `VALLHERU_DATABASE__MAX_CONNECTIONS` | No | `10` | Connection pool size |
| `VALLHERU_DATABASE__LEGACY_URL` | No | — | Legacy MySQL URL for reconciliation |

Environment variables override the config file.

---

## CLI Commands

```
vallheru [OPTIONS] <COMMAND>

COMMANDS:
  serve        Start the HTTP game server
  migrate      Run pending database migrations
  import       Import seed/catalog data from SQL dumps
  bootstrap    Run migrations + seeds + create admin account
  job <name>   Run a scheduled job (energy-tick | daily-reset)
  reset-era    Reset gameplay data (preserve accounts) [--confirm-reset]
  reconcile    Compare PG table counts against expected values (or legacy MySQL)
```

### Common Operations

```bash
# Apply new migrations after a release
vallheru migrate

# Import updated seed data (catalog tables: monsters, items, etc.)
vallheru import

# Run energy regeneration tick
vallheru job energy-tick

# Run daily reset (refresh daily limits, expire old data)
vallheru job daily-reset

# Compare data with legacy DB
VALLHERU_DATABASE__LEGACY_URL=mysql://... vallheru reconcile

# Reset game era (destructive — wipes gameplay, keeps accounts)
vallheru reset-era --confirm-reset
```

---

## Scheduled Jobs

Two recurring jobs must be executed externally (cron, systemd timer, or container scheduler):

| Job | Frequency | Command |
|---|---|---|
| `energy-tick` | Every 5 minutes | `vallheru job energy-tick` |
| `daily-reset` | Once per day (midnight) | `vallheru job daily-reset` |

Jobs use PostgreSQL advisory locks, so overlapping invocations are safe — the duplicate is skipped.

### Docker Compose Cron

```cron
# /etc/cron.d/vallheru-jobs
*/5 * * * *  root  docker compose -f /path/to/compose.prod.yaml run --rm app job energy-tick 2>&1 | logger -t vallheru-energy
0   0 * * *  root  docker compose -f /path/to/compose.prod.yaml run --rm app job daily-reset 2>&1 | logger -t vallheru-daily
```

### systemd Timer (Bare Metal)

```ini
# /etc/systemd/system/vallheru-energy.timer
[Unit]
Description=Vallheru energy tick timer

[Timer]
OnCalendar=*:0/5
Persistent=true

[Install]
WantedBy=timers.target

# /etc/systemd/system/vallheru-energy.service
[Unit]
Description=Vallheru energy tick

[Service]
Type=oneshot
User=vallheru
ExecStart=/usr/local/bin/vallheru -c /etc/vallheru/vallheru.toml job energy-tick
```

---

## Health Checks

| Endpoint | Method | Success | Failure | Purpose |
|---|---|---|---|---|
| `/healthz` | GET | `200 ok` | N/A (always responds if process alive) | Liveness probe |
| `/readyz` | GET | `200 ok` | `503` | Readiness probe (DB reachable) |
| `/buildinfo` | GET | JSON with `git_hash`, `version` | — | Version identification |
| `/migration-status` | GET | JSON with route registry | — | Cutover tracking |

### Docker Compose Health Check

Already configured in `compose.prod.yaml`:

```yaml
healthcheck:
  test: ["CMD-SHELL", "wget -qO- http://localhost:3000/healthz || exit 1"]
  interval: 30s
  timeout: 5s
  start_period: 10s
  retries: 3
```

### External Monitoring

```bash
# Simple cron-based check
*/1 * * * * curl -sf http://localhost/healthz > /dev/null || systemctl restart vallheru
```

---

## Logging

The binary uses `tracing` with JSON-compatible structured output. Control log level via:

```bash
# Environment variable
RUST_LOG=info vallheru serve           # default level
RUST_LOG=debug vallheru serve          # verbose
RUST_LOG=warn vallheru serve           # quiet
RUST_LOG=vallheru_web=debug vallheru serve  # verbose for one crate
```

In Docker Compose, logs are available via:

```bash
docker compose -f compose.prod.yaml logs -f app
docker compose -f compose.prod.yaml logs -f nginx
```

---

## Database Maintenance

### Backups

```bash
# Logical backup
docker compose -f compose.prod.yaml exec postgres \
  pg_dump -U vallheru --format=custom vallheru > backup-$(date +%Y%m%d).dump

# Restore
docker compose -f compose.prod.yaml exec -T postgres \
  pg_restore -U vallheru -d vallheru < backup-20250101.dump
```

### Connection Pool

Default pool size is 10. Adjust via `VALLHERU_DATABASE__MAX_CONNECTIONS` or `max_connections` in the config file. The energy-tick and daily-reset jobs use separate single-connection pools.

---

## Upgrade Procedure

```bash
# 1. Pull new code
git pull

# 2. Build new image
docker compose -f compose.prod.yaml build

# 3. Apply migrations (safe to run repeatedly — skips applied migrations)
docker compose -f compose.prod.yaml run --rm app migrate

# 4. Import updated seed data (idempotent)
docker compose -f compose.prod.yaml run --rm app import

# 5. Rolling restart
docker compose -f compose.prod.yaml up -d

# 6. Verify
curl -s http://localhost/buildinfo | jq .git_hash
```

---

## Troubleshooting

| Symptom | Likely Cause | Action |
|---|---|---|
| 502 Bad Gateway | Rust app not started or crashed | Check `docker compose logs app` |
| `/readyz` returns 503 | PostgreSQL unreachable | Check `docker compose logs postgres`, verify connection string |
| "session.secret must be at least 64 bytes" | Missing or too-short secret | Set `SESSION_SECRET` to ≥ 64 characters |
| "database.url must not be empty" | Missing database URL | Set `VALLHERU_DATABASE__URL` |
| Job skipped (lock held) | Previous job still running | Safe to ignore. Advisory lock prevents double execution |
| Login fails after upgrade | Schema migration needed | Run `vallheru migrate` |
| Missing catalog data | Seed not imported after migration | Run `vallheru import` |
