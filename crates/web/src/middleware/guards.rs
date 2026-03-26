//! Authorization middleware and guard helpers.
//!
//! Guards verify that the current request meets access requirements
//! (authenticated, specific rank, game-state precondition, etc.) and
//! return a consistent error response when they fail.
//!
//! These replace the scattered `$player->rank != 'Admin'` checks found
//! throughout the PHP codebase.

use axum::{
    extract::Request,
    http::StatusCode,
    middleware::Next,
    response::{IntoResponse, Response},
};

use crate::middleware::context::RequestContext;

// ---------------------------------------------------------------------------
// Rank enum
// ---------------------------------------------------------------------------

/// Known player ranks, matching the `rank` column values.
///
/// The ordering loosely reflects privilege level (higher = more privileged)
/// but comparisons should use the helper methods, not numeric ordering.
#[derive(Debug, Clone, PartialEq, Eq)]
pub enum Rank {
    /// Regular player (default after registration).
    Player,
    /// Chronicle writer.
    Chronicler,
    /// Lawyer in the court system.
    Lawyer,
    /// Chancellor of the court.
    Chancellor,
    /// Judge.
    Judge,
    /// Builder (can edit world content).
    Builder,
    /// Royal Jester (bypasses player limits).
    Jester,
    /// Moderator / staff.
    Staff,
    /// Full administrator.
    Admin,
}

impl Rank {
    /// Parse a rank from the database string.
    pub fn from_db(s: &str) -> Self {
        match s {
            "Admin" => Self::Admin,
            "Staff" => Self::Staff,
            "Budowniczy" => Self::Builder,
            "Sędzia" => Self::Judge,
            "Kronikarz" => Self::Chronicler,
            "Królewski Błazen" => Self::Jester,
            "Prawnik" => Self::Lawyer,
            "Kanclerz Sądu" => Self::Chancellor,
            _ => Self::Player,
        }
    }

    /// Whether this rank is at least staff-level (Staff or Admin).
    pub fn is_staff_or_above(&self) -> bool {
        matches!(self, Self::Staff | Self::Admin)
    }

    /// Whether this rank is Admin.
    pub fn is_admin(&self) -> bool {
        *self == Self::Admin
    }
}

// ---------------------------------------------------------------------------
// Guard middleware functions
// ---------------------------------------------------------------------------

/// Middleware that requires an authenticated session.
///
/// Returns `401 Unauthorized` if `RequestContext.session_user` is `None`.
///
/// Usage:
/// ```ignore
/// Router::new()
///     .route("/protected", get(handler))
///     .layer(axum::middleware::from_fn(require_authenticated))
/// ```
pub async fn require_authenticated(req: Request, next: Next) -> Response {
    let ctx = req.extensions().get::<RequestContext>();

    match ctx {
        Some(ctx) if ctx.session_user.is_some() => next.run(req).await,
        _ => (StatusCode::UNAUTHORIZED, "Authentication required").into_response(),
    }
}

/// Middleware that requires the session user to be an Admin.
///
/// Also implicitly requires authentication (returns 401 if not logged in).
pub async fn require_admin(req: Request, next: Next) -> Response {
    match extract_rank(&req) {
        None => (StatusCode::UNAUTHORIZED, "Authentication required").into_response(),
        Some(rank) if rank.is_admin() => next.run(req).await,
        Some(_) => (StatusCode::FORBIDDEN, "Admin access required").into_response(),
    }
}

/// Middleware that requires at least Staff rank (Staff or Admin).
///
/// Also implicitly requires authentication.
pub async fn require_staff(req: Request, next: Next) -> Response {
    match extract_rank(&req) {
        None => (StatusCode::UNAUTHORIZED, "Authentication required").into_response(),
        Some(rank) if rank.is_staff_or_above() => next.run(req).await,
        Some(_) => (StatusCode::FORBIDDEN, "Staff access required").into_response(),
    }
}

/// Middleware that requires the user to hold any of the given ranks.
///
/// This is a factory — call it to create a middleware closure for a specific
/// set of allowed ranks.
///
/// Usage:
/// ```ignore
/// Router::new()
///     .route("/court", get(handler))
///     .layer(axum::middleware::from_fn(
///         require_any_rank(&[Rank::Judge, Rank::Admin])
///     ))
/// ```
pub fn require_any_rank(
    allowed: &'static [Rank],
) -> impl Fn(Request, Next) -> std::pin::Pin<Box<dyn std::future::Future<Output = Response> + Send>>
+ Clone
+ Send {
    move |req: Request, next: Next| {
        let allowed = allowed;
        Box::pin(async move {
            match extract_rank(&req) {
                None => (StatusCode::UNAUTHORIZED, "Authentication required").into_response(),
                Some(rank) if allowed.contains(&rank) => next.run(req).await,
                Some(_) => (StatusCode::FORBIDDEN, "Insufficient rank").into_response(),
            }
        })
    }
}

// ---------------------------------------------------------------------------
// Helpers
// ---------------------------------------------------------------------------

/// Extract the current user's [`Rank`] from the request context, if any.
fn extract_rank(req: &Request) -> Option<Rank> {
    req.extensions()
        .get::<RequestContext>()
        .and_then(|ctx| ctx.session_user.as_ref())
        .map(|u| Rank::from_db(&u.rank))
}

// ---------------------------------------------------------------------------
// Tests
// ---------------------------------------------------------------------------

#[cfg(test)]
mod tests {
    use super::*;

    #[test]
    fn rank_from_db_known_values() {
        assert_eq!(Rank::from_db("Admin"), Rank::Admin);
        assert_eq!(Rank::from_db("Staff"), Rank::Staff);
        assert_eq!(Rank::from_db("Budowniczy"), Rank::Builder);
        assert_eq!(Rank::from_db("Sędzia"), Rank::Judge);
        assert_eq!(Rank::from_db("Kronikarz"), Rank::Chronicler);
        assert_eq!(Rank::from_db("Królewski Błazen"), Rank::Jester);
        assert_eq!(Rank::from_db("Prawnik"), Rank::Lawyer);
        assert_eq!(Rank::from_db("Kanclerz Sądu"), Rank::Chancellor);
        assert_eq!(Rank::from_db("Gracz"), Rank::Player);
        assert_eq!(Rank::from_db("UnknownRank"), Rank::Player);
    }

    #[test]
    fn rank_privileges() {
        assert!(Rank::Admin.is_admin());
        assert!(Rank::Admin.is_staff_or_above());
        assert!(!Rank::Staff.is_admin());
        assert!(Rank::Staff.is_staff_or_above());
        assert!(!Rank::Player.is_admin());
        assert!(!Rank::Player.is_staff_or_above());
        assert!(!Rank::Judge.is_staff_or_above());
    }
}
