//! Per-module route groups.
//!
//! Each submodule exposes a `routes()` function returning an `axum::Router`
//! scoped to that module's path prefix. The top-level [`build_router`]
//! merges them together with the operational routes.

use axum::Router;

use crate::state::AppState;

pub mod health;

/// Assemble the full application router from per-module route groups.
///
/// Operational routes (healthz, readyz, buildinfo) are included directly.
/// Game routes will be added here as modules are migrated.
pub fn build_router(state: AppState) -> Router {
    Router::new()
        // Operational routes (no auth, not module-owned).
        .merge(health::routes())
        // Future: .merge(auth::routes())
        // Future: .merge(player::routes())
        // Future: .merge(world::routes())
        // ...
        .with_state(state)
}
