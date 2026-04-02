//! Authentication, registration, and account lifecycle routes.

use axum::{Router, middleware, routing};

use crate::handlers::{account, account_settings, auth, preset, registration};
use crate::middleware::guards::require_authenticated;
use crate::state::AppState;

/// Register authentication, registration, and account lifecycle routes.
pub fn routes() -> Router<AppState> {
    let account_routes = Router::new()
        .route("/account", routing::get(account_settings::show_account))
        .route(
            "/account/name",
            routing::post(account_settings::change_name),
        )
        .route(
            "/account/password",
            routing::post(account_settings::change_password),
        )
        .route(
            "/account/settings",
            routing::post(account_settings::save_settings),
        )
        .route(
            "/account/profile",
            routing::post(account_settings::save_profile),
        )
        .route(
            "/account/freeze",
            routing::post(account_settings::freeze_account),
        )
        .route(
            "/account/immunity",
            routing::post(account_settings::set_immunity),
        )
        .route(
            "/account/style",
            routing::post(account_settings::save_style),
        )
        .route(
            "/account/roleplay",
            routing::post(account_settings::save_roleplay),
        )
        .route(
            "/account/blocked",
            routing::post(account_settings::add_blocked),
        )
        .route(
            "/account/blocked/{id}/edit",
            routing::post(account_settings::edit_blocked),
        )
        .route(
            "/account/blocked/{id}/delete",
            routing::post(account_settings::remove_blocked),
        )
        .route("/account/links", routing::post(account_settings::add_link))
        .route(
            "/account/links/{id}/edit",
            routing::post(account_settings::edit_link),
        )
        .route(
            "/account/links/{id}/delete",
            routing::post(account_settings::delete_link),
        )
        .layer(middleware::from_fn(require_authenticated));

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
        .merge(account_routes)
}
