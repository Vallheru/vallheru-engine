use axum::Router;

use crate::players;

use super::health;

pub fn build_router() -> Router {
    Router::new().merge(health::router()).merge(players::router())
}
