//! Per-module route groups.
//!
//! Each submodule exposes a `routes()` function returning an `axum::Router`
//! scoped to that module's path prefix. The top-level [`build_router`]
//! merges them together with the operational routes.

use axum::Router;

use crate::middleware::context;
use crate::state::AppState;

pub mod admin;
pub mod auth;
pub mod fallback;
pub mod health;
pub mod world;

/// Assemble the full application router from per-module route groups.
///
/// Operational routes (healthz, readyz, buildinfo) are included directly.
/// Game routes will be added here as modules are migrated.
pub fn build_router(state: AppState) -> Router {
    let ctx_defaults = state.context_defaults.clone();
    let pool = state.pool.clone();

    Router::new()
        // Operational routes (no auth, not module-owned).
        .merge(health::routes())
        // Migration diagnostics.
        .merge(fallback::routes())
        // Static assets (CSS, JS, images).
        .merge(crate::assets::routes())
        // Authentication routes.
        .merge(auth::routes())
        // World navigation (city, travel, locations).
        .merge(world::routes())
        // Admin and staff routes.
        .merge(admin::routes())
        .fallback(fallback::not_found_fallback)
        .with_state(state)
        // Session resolution (runs after context injection, closer to handler).
        .layer(axum::middleware::from_fn_with_state(
            pool,
            crate::middleware::session::resolve_session,
        ))
        // Request context middleware (outermost, runs first).
        .layer(axum::middleware::from_fn_with_state(
            ctx_defaults,
            context::inject_request_context,
        ))
}
