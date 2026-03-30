# Multi-stage Rust build for the Vallheru game engine.
#
# Produces a minimal container with:
#   - The vallheru binary (statically linked with musl)
#   - No PHP, Nginx, or writable template caches
#   - Single external dependency: PostgreSQL (via DATABASE_URL)
#
# Build:
#   docker build -f docker/rust.Dockerfile -t vallheru .
#
# Run:
#   docker run -e VALLHERU_DATABASE__URL=postgres://... \
#              -e VALLHERU_SESSION__SECRET=... \
#              -e VALLHERU_GAME__NAME=Vallheru \
#              -e VALLHERU_GAME__EMAIL=game@example.com \
#              -e VALLHERU_GAME__BASE_URL=http://localhost:3000 \
#              -e VALLHERU_GAME__ADMIN_NAME=Admin \
#              -e VALLHERU_GAME__ADMIN_EMAIL=admin@example.com \
#              -p 3000:3000 vallheru serve

# ---------------------------------------------------------------------------
# Stage 1: Build
# ---------------------------------------------------------------------------
FROM rust:1.85-bookworm AS builder

WORKDIR /build

# Copy workspace manifests first for better layer caching.
COPY Cargo.toml Cargo.lock ./
COPY crates/server/Cargo.toml crates/server/Cargo.toml
COPY crates/web/Cargo.toml crates/web/Cargo.toml
COPY crates/domain/Cargo.toml crates/domain/Cargo.toml
COPY crates/data/Cargo.toml crates/data/Cargo.toml

# Create dummy source files to pre-build dependencies.
RUN mkdir -p crates/server/src crates/web/src crates/domain/src crates/data/src \
    && echo "fn main() {}" > crates/server/src/main.rs \
    && echo "" > crates/web/src/lib.rs \
    && echo "" > crates/domain/src/lib.rs \
    && echo "" > crates/data/src/lib.rs

# Pre-build workspace dependencies (cached unless Cargo.toml changes).
RUN cargo build --release --workspace 2>/dev/null || true

# Copy real source code and embedded assets.
COPY crates/ crates/
COPY templates_jinja/ templates_jinja/
COPY css/ css/
COPY js/ js/
COPY images/ images/
COPY i18n/ i18n/
COPY migrations/ migrations/
COPY seeds/ seeds/
COPY quests/ quests/

# Touch source files to invalidate the dummy build cache.
RUN find crates/ -name "*.rs" -exec touch {} +

# Embed git hash for /buildinfo endpoint.
ARG GIT_HASH=unknown
ENV VALLHERU_GIT_HASH=${GIT_HASH}

# Build the release binary.
RUN cargo build --release --bin vallheru

# ---------------------------------------------------------------------------
# Stage 2: Runtime
# ---------------------------------------------------------------------------
FROM debian:bookworm-slim AS runtime

RUN apt-get update && apt-get install -y --no-install-recommends \
        ca-certificates \
        libssl3 \
    && rm -rf /var/lib/apt/lists/*

# Create a non-root user.
RUN groupadd -r vallheru && useradd -r -g vallheru -d /app vallheru

WORKDIR /app

# Copy the compiled binary.
COPY --from=builder /build/target/release/vallheru /app/vallheru

# Copy the sample config as a reference (not used at runtime by default).
COPY vallheru.sample.toml /app/vallheru.sample.toml

# Ensure the binary is executable.
RUN chmod +x /app/vallheru

USER vallheru

# Default command: start the game server.
# Health probes should target GET /healthz (liveness) and GET /readyz (readiness).
ENTRYPOINT ["/app/vallheru"]
CMD ["serve"]

EXPOSE 3000
