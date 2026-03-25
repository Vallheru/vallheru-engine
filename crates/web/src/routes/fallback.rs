//! Legacy fallback for the strangler-fig migration pattern.
//!
//! During migration, both the Rust app and the legacy PHP app run
//! simultaneously behind a reverse proxy (Nginx). Routes are migrated
//! one at a time. This module provides:
//!
//! - A fallback handler for unmatched routes that informs operators.
//! - A migration registry that tracks which routes are owned by Rust.
//! - A `/migration-status` endpoint (staff-only once auth exists) that
//!   reports the registry for operational visibility.
//!
//! # Deployment topology
//!
//! ```text
//! [Client] -> [Nginx] --+--> [Rust :3000]   (migrated routes)
//!                        \--> [PHP  :8080]   (everything else)
//! ```
//!
//! Nginx is configured with an explicit list of migrated paths and
//! forwards them to Rust; all other paths go to PHP. This file does
//! NOT proxy to PHP — that is Nginx's responsibility. If a request
//! reaches the Rust app for a route it does not own, the fallback
//! handler returns 404 with a diagnostic message.

use axum::{Json, Router, http::StatusCode, response::IntoResponse, routing::get};
use serde::Serialize;

use crate::state::AppState;

/// A route entry in the migration registry.
#[derive(Debug, Clone, Serialize)]
pub struct MigratedRoute {
    /// The URL path pattern (e.g. `/healthz`, `/city`).
    pub path: &'static str,
    /// The module that owns the route.
    pub module: &'static str,
    /// Human-readable migration status.
    pub status: RouteStatus,
}

/// Status of a route in the migration.
#[derive(Debug, Clone, Serialize)]
#[serde(rename_all = "snake_case")]
pub enum RouteStatus {
    /// Fully handled by the Rust app.
    Migrated,
    /// Route exists in Rust but is behind a feature flag / not yet default.
    Staged,
}

/// Central registry of routes that are owned by the Rust application.
///
/// Updated as modules are migrated. This is the single source of truth for
/// generating the Nginx route list and the `/migration-status` endpoint.
///
/// Routes NOT in this list are assumed to still be served by PHP.
pub fn migrated_routes() -> Vec<MigratedRoute> {
    vec![
        MigratedRoute {
            path: "/healthz",
            module: "ops",
            status: RouteStatus::Migrated,
        },
        MigratedRoute {
            path: "/readyz",
            module: "ops",
            status: RouteStatus::Migrated,
        },
        MigratedRoute {
            path: "/buildinfo",
            module: "ops",
            status: RouteStatus::Migrated,
        },
        MigratedRoute {
            path: "/login",
            module: "auth",
            status: RouteStatus::Staged,
        },
        MigratedRoute {
            path: "/logout",
            module: "auth",
            status: RouteStatus::Staged,
        },
        MigratedRoute {
            path: "/register",
            module: "auth",
            status: RouteStatus::Staged,
        },
    ]
}

/// Fallback handler for requests that reach Rust but don't match any route.
///
/// In the strangler topology, this means Nginx routed a request here by
/// mistake, or the user navigated to a non-existent path. Returns 404
/// with a short diagnostic.
pub async fn legacy_fallback() -> impl IntoResponse {
    (
        StatusCode::NOT_FOUND,
        "This route is not yet migrated to the new engine.",
    )
}

/// Register the migration-status diagnostic endpoint.
///
/// `GET /migration-status` returns a JSON array of migrated route entries.
pub fn routes() -> Router<AppState> {
    Router::new().route("/migration-status", get(migration_status))
}

async fn migration_status() -> Json<Vec<MigratedRoute>> {
    Json(migrated_routes())
}

#[cfg(test)]
mod tests {
    use super::*;

    #[test]
    fn registry_contains_operational_routes() {
        let routes = migrated_routes();
        assert!(
            routes.iter().any(|r| r.path == "/healthz"),
            "healthz should be registered"
        );
        assert!(
            routes.iter().any(|r| r.path == "/readyz"),
            "readyz should be registered"
        );
        assert!(
            routes.iter().any(|r| r.path == "/buildinfo"),
            "buildinfo should be registered"
        );
    }

    #[test]
    fn registry_is_non_empty() {
        assert!(!migrated_routes().is_empty());
    }
}
