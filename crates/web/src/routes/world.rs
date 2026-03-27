//! City, map, and world navigation routes.

use axum::{Router, middleware, routing};

use crate::handlers::{city, locations, map, travel};
use crate::middleware::guards::require_authenticated;
use crate::state::AppState;

/// Register city, travel, map, and secondary location routes.
pub fn routes() -> Router<AppState> {
    Router::new()
        .route("/city", routing::get(city::show))
        .route("/travel", routing::get(travel::show))
        .route("/map", routing::get(map::show))
        .route("/mountains", routing::get(locations::mountains))
        .route("/forest", routing::get(locations::forest))
        .route("/alley", routing::get(locations::alley))
        .route(
            "/landfill",
            routing::get(locations::landfill_show).post(locations::landfill_work),
        )
        .route(
            "/rest",
            routing::get(locations::rest_show).post(locations::rest_recover),
        )
        .layer(middleware::from_fn(require_authenticated))
}
