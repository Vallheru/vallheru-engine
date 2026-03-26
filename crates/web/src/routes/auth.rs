//! Authentication, registration, and account lifecycle routes.

use axum::{Router, routing};

use crate::handlers::{account, auth, preset, registration};
use crate::state::AppState;

/// Register authentication, registration, and account lifecycle routes.
pub fn routes() -> Router<AppState> {
    Router::new()
        .route("/login", routing::post(auth::login))
        .route("/logout", routing::get(auth::logout))
        .route(
            "/register",
            routing::get(registration::show_form).post(registration::submit),
        )
        .route("/activate", routing::get(account::activate))
        .route(
            "/lost-password",
            routing::get(account::show_lost_password).post(account::submit_lost_password),
        )
        .route("/preset", routing::get(preset::confirm_preset))
        .route("/referrals", routing::get(preset::referrals))
}
