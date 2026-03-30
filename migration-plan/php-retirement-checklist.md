# PHP Retirement Checklist

Produced for **MP-16-09**. Ordered steps for disabling PHP traffic, retaining rollback hooks, and declaring Rust the system of record.

## Retirement Phases

### Phase R-1: Preparation (Before Any Traffic Moves)

- [ ] `vallheru reconcile` reports zero ERRORs
- [ ] All integration tests pass (`cargo test --workspace`)
- [ ] Production binary built and deployed (`docker compose -f compose.prod.yaml build`)
- [ ] Nginx `rust-routes.conf` prepared (all groups commented out)
- [ ] Database backup taken and verified
- [ ] Rollback procedure reviewed by operator (see [reconciliation-procedures.md](reconciliation-procedures.md))
- [ ] Session secret generated and stored securely
- [ ] Scheduled job cron entries prepared (energy-tick, daily-reset)
- [ ] `/healthz` and `/readyz` responding on Rust binary

### Phase R-2: Route-by-Route Cutover

Follow the activation order in [cutover-rules.md](cutover-rules.md):

| Step | Group | Action | Verification |
|---|---|---|---|
| 1 | **0: Operational** | Already live | `/healthz` returns 200 |
| 2 | **A: Auth** | Uncomment in `rust-routes.conf`, reload Nginx | Login → city → logout flow works |
| 3 | **B: City & Navigation** | Activate, reload | City hub renders, travel works |
| 4 | **D: Deity/Temple/Tower** | Activate, reload | Temple page loads |
| 5 | **E: Equipment & Shops** | Activate, reload | Buy → equip → sell item |
| 6 | **F: Economy & Banking** | Activate, reload | Deposit → withdraw |
| 7 | **G: Markets** | Activate, reload | List → buy offer |
| 8 | **H: Gathering & Crafting** | Activate, reload | Mine → smelt flow |
| 9 | **J: Social** | Activate, reload | Chat, mail, forums |
| 10 | **K: Content** | Activate, reload | News page loads |
| 11 | **M: Outposts** | Activate, reload | Outpost management |
| 12 | **N: Quests** | Activate, reload | Maze exploration |
| 13 | **Z: House** | Activate, reload | House management |
| 14 | **Q: Memberlist** | Activate, reload | Member list loads |
| 15 | **O: Staff & Moderation** | Activate, reload | Staff panel |
| 16 | **P: Admin** | Activate, reload | Admin panel |

**Between each step**:
- Wait 15 minutes minimum
- Monitor logs for errors
- Spot-check the affected pages
- If errors: roll back immediately (comment out, reload)

### Phase R-3: Soak Period

After all groups are activated:

- [ ] All route groups have been rust-primary for ≥ 7 days with no rollback
- [ ] `vallheru reconcile` reports zero ERRORs daily
- [ ] No 500 errors in logs above baseline
- [ ] Scheduled jobs running successfully (check advisory lock log messages)
- [ ] Player reports reviewed — no regression complaints

### Phase R-4: PHP Cold Standby

PHP remains available but receives no traffic:

- [ ] PHP-FPM process stopped (`docker compose stop php-fpm` or `systemctl stop php-fpm`)
- [ ] Nginx config has no PHP-FPM upstream references
- [ ] All routes confirmed on Rust via `/migration-status`
- [ ] PHP files remain on disk as rollback reference (do not delete yet)
- [ ] Legacy `docker-compose.yaml` (PHP) preserved but not running

**Duration**: 14 days minimum in cold standby.

### Phase R-5: Point of No Return

After cold standby with no rollback events:

- [ ] **Decision**: PHP can be retired permanently. This is irreversible in practice.

**Actions**:
- [ ] Remove PHP-FPM from Docker Compose
- [ ] Remove `docker/php.Dockerfile`
- [ ] Remove `docker/entrypoint.sh` (PHP permissions script)
- [ ] Remove `docker/custom.php.ini`
- [ ] Remove legacy `docker-compose.yaml` (or archive it)
- [ ] Remove `docker/nginx.conf` (PHP-era config) — keep `docker/nginx-rust.conf`
- [ ] Archive PHP source files (move to `legacy/` branch or tag, do not keep in main)
- [ ] Remove `adodb/`, `class/`, `includes/`, `libs/`, `mailer/`, `languages/`, `i18n/` directories
- [ ] Remove `install/` directory (PHP web installer)
- [ ] Remove `templates/` (Smarty templates) — keep `templates_jinja/`
- [ ] Remove `templates_c/` (compiled Smarty cache)
- [ ] Remove PHP entry point files (`*.php` in root)
- [ ] Update `.gitignore` to ignore PHP artifacts

### Phase R-6: Cleanup Complete

- [ ] Repository contains only Rust source, migrations, Jinja templates, and deployment config
- [ ] CI pipeline runs Rust quality gate only (no PHP linting)
- [ ] Legacy MySQL configuration removed from `vallheru.sample.toml` (keep `legacy_url` as optional for historical reconciliation)
- [ ] README updated to reflect Rust-only architecture
- [ ] All planned migration tasks marked complete in `migration-plan/`
- [ ] Tech debt register reviewed — remaining items are Rust-only

---

## Rollback References

| Phase | Rollback Method | Time to Recover | Data Impact |
|---|---|---|---|
| R-2 (cutover) | Comment out Nginx group, reload | < 60 seconds | None |
| R-3 (soak) | Comment out all Nginx groups, reload | < 60 seconds | None |
| R-4 (cold standby) | Start PHP-FPM, restore Nginx config, reload | < 5 minutes | None |
| R-5 (point of no return) | Restore from git tag + backup | Hours | Potential data gap |
| R-6 (cleanup complete) | Full restore from archived branch | Hours | Potential data gap |

**Key principle**: Rollback is always free (no data impact) until Phase R-5. After R-5, rollback requires restoring from backup.

---

## Data Authority Timeline

| Phase | PostgreSQL Authority | MySQL | Session Authority |
|---|---|---|---|
| R-1 | Shared with PHP | May exist for reconciliation | PHP |
| R-2 | Shared (same DB) | Read-only comparison | Transitioning to Rust |
| R-3 | Rust primary | Optional reconciliation | Rust |
| R-4 | Rust sole writer | Offline | Rust |
| R-5+ | Rust sole authority | Decommissioned | Rust |

---

## Specific PHP Artifacts to Remove at R-5

### Root PHP Files (110 entry points)

All `*.php` files in the repository root. Full list in [route-manifest.md](route-manifest.md).

### PHP Directories

| Directory | Contents | Action |
|---|---|---|
| `adodb/` | ADOdb database library | Remove |
| `class/` | PHP class files | Remove |
| `includes/` | PHP include files, config | Remove |
| `libs/` | Smarty template engine | Remove |
| `mailer/` | PHPMailer | Remove |
| `languages/` | PHP language files | Remove (translations in Rust domain crate) |
| `i18n/` | PHP i18n files | Remove |
| `install/` | Web installer | Remove |
| `templates/` | Smarty `.tpl` files | Remove (replaced by `templates_jinja/`) |
| `templates_c/` | Compiled Smarty cache | Remove |
| `cache/` | PHP cache directory | Remove |
| `quests/` | PHP quest data files | Remove (quest data in PostgreSQL) |
| `seeds/` | SQL seed files | Keep (used by `vallheru import`) |
| `migrations/` | SQL migration files | Keep (used by `vallheru migrate`) |
| `css/` | PHP-era stylesheets | Remove (CSS embedded in binary via `templates_jinja/static/`) |
| `js/` | PHP-era JavaScript | Remove (JS embedded in binary) |

### Docker Artifacts

| File | Action |
|---|---|
| `docker/php.Dockerfile` | Remove |
| `docker/entrypoint.sh` | Remove |
| `docker/custom.php.ini` | Remove |
| `docker/nginx.conf` | Remove (replaced by `docker/nginx-rust.conf`) |
| `docker-compose.yaml` | Remove (replaced by `compose.prod.yaml`) |

---

## Sign-Off Template

```
PHP Retirement Sign-Off — [Game Instance Name]
Date: YYYY-MM-DD

Phase R-1 completed: YYYY-MM-DD
Phase R-2 completed: YYYY-MM-DD (all groups activated)
Phase R-3 completed: YYYY-MM-DD (7-day soak)
Phase R-4 completed: YYYY-MM-DD (14-day cold standby)
Phase R-5 executed:  YYYY-MM-DD

Operator: _______________
Verified by: _______________

Notes:
```
