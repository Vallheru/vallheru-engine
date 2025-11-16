use axum::{routing::get, Json, Router};

use super::{actions, model::PlayerProfile};

async fn profile() -> Json<PlayerProfile> {
    Json(actions::sample_profile())
}

/// Routes exposed by the player module.
pub fn router() -> Router {
    Router::new().route("/players/profile", get(profile))
}
