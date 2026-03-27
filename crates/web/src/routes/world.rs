//! City, map, and world navigation routes.

use axum::{Router, middleware, routing};

use crate::handlers::{city, map, travel};
use crate::middleware::guards::require_authenticated;
use crate::state::AppState;

/// Register city, travel, and map routes.
pub fn routes() -> Router<AppState> {
    Router::new()
        .route("/city", routing::get(city::show))
        .route("/travel", routing::get(travel::show))
        .route("/map", routing::get(map::show))
        .layer(middleware::from_fn(require_authenticated))
}
