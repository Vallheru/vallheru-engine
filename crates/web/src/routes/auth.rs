//! Authentication routes (login / logout).

use axum::{Router, routing};

use crate::handlers::auth;
use crate::state::AppState;

/// Register authentication routes.
pub fn routes() -> Router<AppState> {
    Router::new()
        .route("/login", routing::post(auth::login))
        .route("/logout", routing::get(auth::logout))
}
