use axum::{response::IntoResponse, routing::get, Json, Router};
use serde::Serialize;

#[derive(Serialize)]
struct HealthCheckResponse {
    status: &'static str,
}

const STATUS_OK: &str = "ok";

async fn health_check() -> impl IntoResponse {
    Json(HealthCheckResponse { status: STATUS_OK })
}

pub fn router() -> Router {
    Router::new().route("/health", get(health_check))
}
