//! PostgreSQL-backed session middleware.
//!
//! On each request, reads the `session_id` cookie, loads the session from
//! the database, and populates `RequestContext.session_user` if a player
//! is associated with the session.
//!
//! The session cookie is `HttpOnly`, `SameSite=Lax`, and `Path=/`.
//! Session IDs are 64-character random hex strings.

use axum::{
    extract::{Request, State},
    http::header,
    middleware::Next,
    response::Response,
};
use rand::Rng;

use super::context::{RequestContext, SessionUser};

/// Cookie name used for session tracking.
pub const SESSION_COOKIE: &str = "sid";

/// Generate a cryptographically random 64-character hex session ID.
pub fn generate_session_id() -> String {
    let mut bytes = [0u8; 32];
    rand::thread_rng().fill(&mut bytes);
    hex::encode(bytes)
}

/// State passed to the session resolution middleware.
///
/// Contains only the database pool, extracted from `AppState` during
/// router construction.
pub type SessionPool = sqlx::PgPool;

/// Axum middleware that resolves the session from a cookie and populates
/// the request context with session user data.
///
/// Must run after [`inject_request_context`](super::context::inject_request_context).
pub async fn resolve_session(
    State(pool): State<SessionPool>,
    mut req: Request,
    next: Next,
) -> Response {
    let session_id = extract_session_cookie(&req);

    if let Some(sid) = &session_id {
        if let Ok(Some(session)) = vallheru_data::queries::session::load_session(&pool, sid).await {
            if let Some(player_id) = session.player_id {
                // Load minimal player info for the context.
                if let Ok(Some(player)) =
                    vallheru_data::queries::session::load_session_player(&pool, player_id).await
                {
                    // Update the existing RequestContext with session data.
                    if let Some(ctx) = req.extensions_mut().get_mut::<RequestContext>() {
                        ctx.session_user = Some(SessionUser {
                            id: i64::from(player.id),
                            name: player.username,
                            rank: player.rank,
                        });
                    }
                }
            }
        }
    }

    next.run(req).await
}

/// Extract the session cookie value from the request headers.
fn extract_session_cookie(req: &Request) -> Option<String> {
    let cookie_header = req.headers().get(header::COOKIE)?.to_str().ok()?;
    for pair in cookie_header.split(';') {
        let pair = pair.trim();
        if let Some(value) = pair.strip_prefix("sid=") {
            let value = value.trim();
            if !value.is_empty() {
                return Some(value.to_owned());
            }
        }
    }
    None
}

/// Build a `Set-Cookie` header value for the session.
pub fn session_cookie_header(session_id: &str) -> String {
    format!("{SESSION_COOKIE}={session_id}; Path=/; HttpOnly; SameSite=Lax")
}

/// Build a `Set-Cookie` header that expires the session cookie.
pub fn clear_session_cookie_header() -> String {
    format!("{SESSION_COOKIE}=; Path=/; HttpOnly; SameSite=Lax; Max-Age=0")
}

#[cfg(test)]
mod tests {
    use super::*;

    #[test]
    fn generate_session_id_is_64_hex_chars() {
        let id = generate_session_id();
        assert_eq!(id.len(), 64);
        assert!(id.chars().all(|c| c.is_ascii_hexdigit()));
    }

    #[test]
    fn generate_session_id_is_unique() {
        let a = generate_session_id();
        let b = generate_session_id();
        assert_ne!(a, b);
    }

    #[test]
    fn session_cookie_header_format() {
        let header = session_cookie_header("abc123");
        assert!(header.contains("sid=abc123"));
        assert!(header.contains("HttpOnly"));
        assert!(header.contains("SameSite=Lax"));
        assert!(header.contains("Path=/"));
    }

    #[test]
    fn clear_cookie_sets_max_age_zero() {
        let header = clear_session_cookie_header();
        assert!(header.contains("Max-Age=0"));
        assert!(header.contains("sid="));
    }

    #[test]
    fn extract_cookie_from_header() {
        use axum::http;
        let req = http::Request::builder()
            .header(header::COOKIE, "sid=abc123; other=val")
            .body(axum::body::Body::empty())
            .unwrap();
        assert_eq!(extract_session_cookie(&req), Some("abc123".to_owned()));
    }

    #[test]
    fn extract_cookie_missing() {
        use axum::http;
        let req = http::Request::builder()
            .body(axum::body::Body::empty())
            .unwrap();
        assert_eq!(extract_session_cookie(&req), None);
    }

    #[test]
    fn extract_cookie_no_session() {
        use axum::http;
        let req = http::Request::builder()
            .header(header::COOKIE, "other=val; foo=bar")
            .body(axum::body::Body::empty())
            .unwrap();
        assert_eq!(extract_session_cookie(&req), None);
    }
}
