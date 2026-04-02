//! Route registry and fallback handler.
//!
//! Provides:
//! - A fallback handler for unmatched routes (returns 404).
//! - A migration registry listing all routes owned by the Rust engine.
//! - A `/migration-status` endpoint for operational visibility.

use axum::{Json, Router, http::StatusCode, response::IntoResponse, routing::get};
use serde::Serialize;

use crate::state::AppState;

/// A route entry in the route registry.
#[derive(Debug, Clone, Copy, Serialize)]
pub struct MigratedRoute {
    /// The URL path pattern (e.g. `/healthz`, `/city`).
    pub path: &'static str,
    /// The module that owns the route.
    pub module: &'static str,
}

/// Central registry of all routes owned by the Rust application.
pub fn migrated_routes() -> Vec<MigratedRoute> {
    vec![
        // Operational
        r("/healthz", "ops"),
        r("/readyz", "ops"),
        r("/buildinfo", "ops"),
        r("/rss", "content"),
        // Auth & Account
        r("/login", "auth"),
        r("/logout", "auth"),
        r("/register", "auth"),
        r("/activate", "auth"),
        r("/lost-password", "auth"),
        r("/preset", "account"),
        r("/referrals", "account"),
        r("/account", "account"),
        r("/account/*", "account"),
        // City & Navigation
        r("/city", "world"),
        r("/map", "world"),
        r("/travel", "world"),
        r("/forest", "world"),
        r("/mountains", "world"),
        r("/alley", "world"),
        r("/rest", "world"),
        r("/landfill", "world"),
        r("/hospital", "world"),
        // Deity, Temple & Tower
        r("/deity", "deity"),
        r("/deity/*", "deity"),
        r("/temple", "temple"),
        r("/temple/*", "temple"),
        r("/tower", "tower"),
        // Equipment & Shops
        r("/equipment", "equipment"),
        r("/equipment/*", "equipment"),
        r("/weapons", "shops"),
        r("/weapons/*", "shops"),
        r("/armor", "shops"),
        r("/armor/*", "shops"),
        r("/fletcher", "shops"),
        r("/fletcher/*", "shops"),
        r("/spellbook", "spells"),
        r("/spellbook/*", "spells"),
        r("/magic-shop", "shops"),
        r("/magic-shop/*", "shops"),
        // Economy & Banking
        r("/wealth", "bank"),
        r("/bank", "bank"),
        // Player Markets
        r("/market", "market"),
        r("/market/*", "market"),
        // Gathering & Crafting
        r("/mining", "gathering"),
        r("/mines", "gathering"),
        r("/mines/*", "gathering"),
        r("/smelter", "gathering"),
        r("/smelter/*", "gathering"),
        r("/lumberjack", "gathering"),
        r("/farm", "gathering"),
        // Social
        r("/chat", "chat"),
        r("/chat/*", "chat"),
        r("/room", "room"),
        r("/room/*", "room"),
        r("/mail", "mail"),
        r("/mail/*", "mail"),
        r("/forums", "forums"),
        r("/forums/*", "forums"),
        r("/tforums", "tribe_forum"),
        r("/tforums/*", "tribe_forum"),
        // Content
        r("/news", "content"),
        r("/news/*", "content"),
        r("/updates", "content"),
        r("/updates/*", "content"),
        r("/newspaper", "content"),
        r("/newspaper/*", "content"),
        r("/polls", "content"),
        r("/polls/*", "content"),
        r("/proposals/*", "content"),
        r("/comments/*", "content"),
        r("/notes", "pages"),
        r("/notes/*", "pages"),
        r("/library", "pages"),
        r("/library/*", "pages"),
        r("/roleplay/*", "pages"),
        r("/chronicle", "pages"),
        r("/chronicle/*", "pages"),
        // Outposts & Garrison
        r("/outposts", "outpost"),
        r("/outposts/*", "outpost"),
        r("/garrison", "outpost"),
        r("/garrison/*", "outpost"),
        // Quests & Missions
        r("/labyrinth", "quest"),
        r("/labyrinth/*", "quest"),
        r("/mission", "quest"),
        r("/maze", "quest"),
        r("/maze/*", "quest"),
        // Staff & Moderation
        r("/staff", "staff"),
        r("/staff/*", "staff"),
        r("/stafflist", "staff"),
        r("/judge", "moderation"),
        r("/court", "court"),
        r("/court/*", "court"),
        r("/jail", "jail"),
        r("/jail/*", "jail"),
        // Admin
        r("/admin", "admin"),
        // Member Directory
        r("/memberlist", "memberlist"),
        // House
        r("/house", "house"),
        r("/house/*", "house"),
    ]
}

/// Shorthand constructor.
const fn r(path: &'static str, module: &'static str) -> MigratedRoute {
    MigratedRoute { path, module }
}

/// Fallback handler for requests that don't match any known route.
pub async fn not_found_fallback() -> impl IntoResponse {
    (StatusCode::NOT_FOUND, "Not found.")
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
