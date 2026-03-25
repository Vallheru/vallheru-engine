//! Authentication and registration routes.

use axum::{Router, routing};

use crate::handlers::{auth, registration};
use crate::state::AppState;

/// Register authentication and registration routes.
pub fn routes() -> Router<AppState> {
    Router::new()
        .route("/login", routing::post(auth::login))
        .route("/logout", routing::get(auth::logout))
        .route(
            "/register",
            routing::get(registration::show_form).post(registration::submit),
        )
}
