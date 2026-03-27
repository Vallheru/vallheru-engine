//! City and world navigation routes.

use axum::{Router, middleware, routing};

use crate::handlers::city;
use crate::middleware::guards::require_authenticated;
use crate::state::AppState;

/// Register city and world navigation routes.
pub fn routes() -> Router<AppState> {
    Router::new()
        .route("/city", routing::get(city::show))
        .layer(middleware::from_fn(require_authenticated))
}
