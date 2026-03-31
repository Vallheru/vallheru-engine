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
#[derive(Debug, Clone, Copy, Serialize)]
pub struct MigratedRoute {
    /// The URL path pattern (e.g. `/healthz`, `/city`).
    pub path: &'static str,
    /// The module that owns the route.
    pub module: &'static str,
    /// Human-readable migration status.
    pub status: RouteStatus,
}

/// Status of a route in the migration.
#[derive(Debug, Clone, Copy, Serialize)]
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
        // --- Group 0: Operational (always Rust) ---
        r("/healthz", "ops", RouteStatus::Migrated),
        r("/readyz", "ops", RouteStatus::Migrated),
        r("/buildinfo", "ops", RouteStatus::Migrated),
        r("/rss", "content", RouteStatus::Staged),
        // --- Group A: Auth & Account ---
        r("/login", "auth", RouteStatus::Staged),
        r("/logout", "auth", RouteStatus::Staged),
        r("/register", "auth", RouteStatus::Staged),
        r("/activate", "auth", RouteStatus::Staged),
        r("/lost-password", "auth", RouteStatus::Staged),
        r("/preset", "account", RouteStatus::Staged),
        r("/referrals", "account", RouteStatus::Staged),
        r("/account", "account", RouteStatus::Staged),
        r("/account/*", "account", RouteStatus::Staged),
        // --- Group B: City & Navigation ---
        r("/city", "world", RouteStatus::Staged),
        r("/map", "world", RouteStatus::Staged),
        r("/travel", "world", RouteStatus::Staged),
        r("/forest", "world", RouteStatus::Staged),
        r("/mountains", "world", RouteStatus::Staged),
        r("/alley", "world", RouteStatus::Staged),
        r("/rest", "world", RouteStatus::Staged),
        r("/landfill", "world", RouteStatus::Staged),
        r("/hospital", "world", RouteStatus::Staged),
        // --- Group D: Deity, Temple & Tower ---
        r("/deity", "deity", RouteStatus::Staged),
        r("/deity/*", "deity", RouteStatus::Staged),
        r("/temple", "temple", RouteStatus::Staged),
        r("/temple/*", "temple", RouteStatus::Staged),
        r("/tower", "tower", RouteStatus::Staged),
        // --- Group E: Equipment & Shops ---
        r("/equipment", "equipment", RouteStatus::Staged),
        r("/equipment/*", "equipment", RouteStatus::Staged),
        r("/weapons", "shops", RouteStatus::Staged),
        r("/weapons/*", "shops", RouteStatus::Staged),
        r("/armor", "shops", RouteStatus::Staged),
        r("/armor/*", "shops", RouteStatus::Staged),
        r("/fletcher", "shops", RouteStatus::Staged),
        r("/fletcher/*", "shops", RouteStatus::Staged),
        r("/spellbook", "spells", RouteStatus::Staged),
        r("/spellbook/*", "spells", RouteStatus::Staged),
        r("/magic-shop", "shops", RouteStatus::Staged),
        r("/magic-shop/*", "shops", RouteStatus::Staged),
        // --- Group F: Economy & Banking ---
        r("/wealth", "bank", RouteStatus::Staged),
        r("/bank", "bank", RouteStatus::Staged),
        // --- Group G: Player Markets ---
        r("/market", "market", RouteStatus::Staged),
        r("/market/*", "market", RouteStatus::Staged),
        // --- Group H: Gathering & Crafting ---
        r("/mining", "gathering", RouteStatus::Staged),
        r("/mines", "gathering", RouteStatus::Staged),
        r("/mines/*", "gathering", RouteStatus::Staged),
        r("/smelter", "gathering", RouteStatus::Staged),
        r("/smelter/*", "gathering", RouteStatus::Staged),
        r("/lumberjack", "gathering", RouteStatus::Staged),
        r("/farm", "gathering", RouteStatus::Staged),
        // --- Group J: Social ---
        r("/chat", "chat", RouteStatus::Staged),
        r("/chat/*", "chat", RouteStatus::Staged),
        r("/room", "room", RouteStatus::Staged),
        r("/room/*", "room", RouteStatus::Staged),
        r("/mail", "mail", RouteStatus::Staged),
        r("/mail/*", "mail", RouteStatus::Staged),
        r("/forums", "forums", RouteStatus::Staged),
        r("/forums/*", "forums", RouteStatus::Staged),
        r("/tforums", "tribe_forum", RouteStatus::Staged),
        r("/tforums/*", "tribe_forum", RouteStatus::Staged),
        // --- Group K: Content ---
        r("/news", "content", RouteStatus::Staged),
        r("/news/*", "content", RouteStatus::Staged),
        r("/updates", "content", RouteStatus::Staged),
        r("/updates/*", "content", RouteStatus::Staged),
        r("/newspaper", "content", RouteStatus::Staged),
        r("/newspaper/*", "content", RouteStatus::Staged),
        r("/polls", "content", RouteStatus::Staged),
        r("/polls/*", "content", RouteStatus::Staged),
        r("/proposals/*", "content", RouteStatus::Staged),
        r("/comments/*", "content", RouteStatus::Staged),
        r("/notes", "pages", RouteStatus::Staged),
        r("/notes/*", "pages", RouteStatus::Staged),
        r("/library", "pages", RouteStatus::Staged),
        r("/library/*", "pages", RouteStatus::Staged),
        r("/roleplay/*", "pages", RouteStatus::Staged),
        r("/chronicle", "pages", RouteStatus::Staged),
        r("/chronicle/*", "pages", RouteStatus::Staged),
        // --- Group M: Outposts & Garrison ---
        r("/outposts", "outpost", RouteStatus::Staged),
        r("/outposts/*", "outpost", RouteStatus::Staged),
        r("/garrison", "outpost", RouteStatus::Staged),
        r("/garrison/*", "outpost", RouteStatus::Staged),
        // --- Group N: Quests & Missions ---
        r("/labyrinth", "quest", RouteStatus::Staged),
        r("/labyrinth/*", "quest", RouteStatus::Staged),
        r("/mission", "quest", RouteStatus::Staged),
        r("/maze", "quest", RouteStatus::Staged),
        r("/maze/*", "quest", RouteStatus::Staged),
        // --- Group O: Staff & Moderation ---
        r("/staff", "staff", RouteStatus::Staged),
        r("/staff/*", "staff", RouteStatus::Staged),
        r("/stafflist", "staff", RouteStatus::Staged),
        r("/judge", "moderation", RouteStatus::Staged),
        r("/court", "court", RouteStatus::Staged),
        r("/court/*", "court", RouteStatus::Staged),
        r("/jail", "jail", RouteStatus::Staged),
        r("/jail/*", "jail", RouteStatus::Staged),
        // --- Group P: Admin ---
        r("/admin", "admin", RouteStatus::Staged),
        // --- Group Q: Member Directory ---
        r("/memberlist", "memberlist", RouteStatus::Staged),
        // --- Group Z: House ---
        r("/house", "house", RouteStatus::Staged),
        r("/house/*", "house", RouteStatus::Staged),
    ]
}

/// Shorthand constructor.
const fn r(path: &'static str, module: &'static str, status: RouteStatus) -> MigratedRoute {
    MigratedRoute {
        path,
        module,
        status,
    }
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
