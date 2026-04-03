//! Per-request context resolved once by middleware and available to all handlers.
//!
//! This replaces the implicit global state built in the PHP `includes/head.php`.
//! The actual session/player resolution will be wired in module 05 (auth).
//! Until then handlers receive an anonymous context with sensible defaults.

use axum::{extract::Request, middleware::Next, response::Response};
use uuid::Uuid;

/// Typed per-request context available to all handlers via `Extension<RequestContext>`.
///
/// Fields are populated once per request by [`inject_request_context`] and are
/// immutable for the remainder of the request lifecycle.
#[derive(Debug, Clone)]
pub struct RequestContext {
    /// Unique identifier for this request, useful for tracing and log correlation.
    pub request_id: Uuid,

    /// Display locale for the request (e.g. `"pl"`). Determined from config;
    /// per-player override will be added when sessions exist.
    pub locale: String,

    /// Template theme/skin name. Empty string means the default skin.
    /// Per-player override will be added when sessions exist.
    pub theme: String,

    /// Authenticated session user, if any.
    /// Populated by the session middleware (module 05). `None` means anonymous.
    pub session_user: Option<SessionUser>,

    /// Sidebar player data (stats, gold, location). Populated for authenticated requests.
    pub sidebar: Option<SidebarData>,

    /// Online players for the right sidebar. Populated for authenticated requests.
    pub online_players: Vec<OnlinePlayerView>,
}

/// Minimal session user identity extracted from a session cookie.
///
/// This will be expanded as the auth module is built out. For now it
/// carries just enough to gate access and personalise pages.
#[derive(Debug, Clone)]
pub struct SessionUser {
    /// Player primary key.
    pub id: i64,
    /// Display name.
    pub name: String,
    /// Role / rank string (e.g. `"Admin"`, `"Staff"`, `"Gracz"`).
    pub rank: String,
}

/// Player stats and info for the left sidebar.
#[derive(Debug, Clone, serde::Serialize)]
pub struct SidebarData {
    pub hp: i32,
    pub max_hp: i32,
    pub energy: i32,
    pub max_energy: i32,
    pub credits: i32,
    pub bank: i32,
    pub platinum: i32,
    pub vallars: i32,
    pub location: String,
    pub class: String,
    pub tribe_id: i32,
    pub tribe_rank: String,
    pub room: i32,
}

/// A single online player for the right sidebar.
#[derive(Debug, Clone, serde::Serialize)]
pub struct OnlinePlayerView {
    pub id: i32,
    pub name: String,
    pub rank: String,
}

/// Default values used to seed anonymous request contexts.
///
/// Stored in [`AppState`](crate::state::AppState) and shared across requests
/// so each middleware invocation avoids re-reading config.
#[derive(Debug, Clone)]
pub struct ContextDefaults {
    /// Default locale from config (`game.lang`).
    pub locale: String,
}

/// Axum middleware that creates a [`RequestContext`] and inserts it into
/// request extensions.
///
/// Usage: `.layer(axum::middleware::from_fn_with_state(state, inject_request_context))`
pub async fn inject_request_context(
    axum::extract::State(defaults): axum::extract::State<ContextDefaults>,
    mut req: Request,
    next: Next,
) -> Response {
    let request_id = Uuid::new_v4();

    let ctx = RequestContext {
        request_id,
        locale: defaults.locale.clone(),
        theme: String::new(),
        session_user: None,
        sidebar: None,
        online_players: Vec::new(),
    };

    // Make the request id available as a tracing span field.
    let span = tracing::info_span!("request", id = %request_id);
    let _guard = span.enter();

    req.extensions_mut().insert(ctx);

    next.run(req).await
}

#[cfg(test)]
mod tests {
    use super::*;

    #[test]
    fn request_context_is_anonymous_by_default() {
        let ctx = RequestContext {
            request_id: Uuid::new_v4(),
            locale: "pl".to_owned(),
            theme: String::new(),
            session_user: None,
            sidebar: None,
            online_players: Vec::new(),
        };
        assert!(ctx.session_user.is_none());
        assert_eq!(ctx.locale, "pl");
        assert!(ctx.theme.is_empty());
        assert!(ctx.sidebar.is_none());
        assert!(ctx.online_players.is_empty());
    }

    #[test]
    fn session_user_carries_identity() {
        let user = SessionUser {
            id: 42,
            name: "Aragorn".to_owned(),
            rank: "Admin".to_owned(),
        };
        assert_eq!(user.id, 42);
        assert_eq!(user.rank, "Admin");
    }
}
