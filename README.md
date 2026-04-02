# Vallheru Engine

A browser-based RPG game engine built with Rust, Axum, MiniJinja, and PostgreSQL.

## Requirements

- Rust 1.85+ (edition 2024)
- PostgreSQL 16+
- Docker & Docker Compose (for local development)

## Quick Start

```bash
# 1. Start PostgreSQL
docker compose up -d

# 2. Copy and edit configuration
cp vallheru.sample.toml vallheru.toml

# 3. Bootstrap the database (runs migrations, seeds, creates admin)
cargo run -- -c vallheru.toml bootstrap \
  --admin-user admin \
  --admin-email admin@example.com \
  --admin-password changeme

# 4. Start the server
cargo run -- -c vallheru.toml serve
```

The game is available at `http://localhost:3000`.

## CLI Commands

| Command | Description |
|---------|-------------|
| `serve` | Start the HTTP game server |
| `migrate` | Run pending database migrations |
| `import` | Import reference data from embedded seed files |
| `job <name>` | Run a scheduled job (`energy-tick`, `daily-reset`) |
| `bootstrap` | Full setup: migrate + seed + create admin account |
| `reset-era` | Reset game era (wipe progress, keep accounts) |

## Development

```bash
# Format, lint, test, build
cargo fmt --all -- --check
cargo clippy --workspace --all-targets --all-features -- -D warnings
cargo test --workspace --all-targets --all-features
cargo build --release
```

## Production

```bash
docker compose -f compose.prod.yaml up -d
```

## Architecture

- **Single binary** — all templates, CSS, JS, images, and i18n data are embedded at compile time.
- **4 crates** — `server` (CLI/main), `web` (handlers/routing/templates), `domain` (game logic), `data` (persistence/SQL).
- **Axum** web framework with typed extractors and middleware.
- **MiniJinja** templates with typed context structs.
- **sqlx** for explicit SQL queries against PostgreSQL (no ORM).
- **Argon2id** password hashing.