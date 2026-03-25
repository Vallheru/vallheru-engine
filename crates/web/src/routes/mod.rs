//! Per-module route groups.
//!
//! Each submodule exposes a `routes()` function returning an `axum::Router`
//! scoped to that module's path prefix. The top-level [`build_router`]
//! merges them together with the operational routes.

use axum::Router;

use crate::middleware::context;
use crate::state::AppState;

pub mod fallback;
pub mod health;

/// Assemble the full application router from per-module route groups.
///
/// Operational routes (healthz, readyz, buildinfo) are included directly.
/// Game routes will be added here as modules are migrated.
pub fn build_router(state: AppState) -> Router {
    let ctx_defaults = state.context_defaults.clone();

    Router::new()
        // Operational routes (no auth, not module-owned).
        .merge(health::routes())
        // Migration diagnostics.
        .merge(fallback::routes())
        // Static assets (CSS, JS, images).
        .merge(crate::assets::routes())
        // Future: .merge(auth::routes())
        // Future: .merge(player::routes())
        // Future: .merge(world::routes())
        // ...
        // Catch-all for unmigrated routes.
        .fallback(fallback::legacy_fallback)
        .with_state(state)
        // Request context middleware runs for every request.
        .layer(axum::middleware::from_fn_with_state(
            ctx_defaults,
            context::inject_request_context,
        ))
}
