//! Operational health and readiness routes.

use axum::extract::State;
use axum::{Json, Router, routing::get};
use serde::Serialize;

use crate::state::AppState;

/// Register operational routes: `/healthz`, `/readyz`, `/buildinfo`.
pub fn routes() -> Router<AppState> {
    Router::new()
        .route("/healthz", get(healthz))
        .route("/readyz", get(readyz))
        .route("/buildinfo", get(build_info))
}

/// Process health — returns 200 if the server is running.
async fn healthz() -> axum::http::StatusCode {
    axum::http::StatusCode::OK
}

/// Readiness probe — returns 200 when all dependencies are available.
async fn readyz(State(state): State<AppState>) -> axum::http::StatusCode {
    match vallheru_data::pool::check_health(&state.pool).await {
        Ok(()) => axum::http::StatusCode::OK,
        Err(e) => {
            tracing::warn!(error = %e, "readiness check failed");
            axum::http::StatusCode::SERVICE_UNAVAILABLE
        }
    }
}

#[derive(Serialize)]
struct BuildInfo {
    version: &'static str,
    git_hash: &'static str,
}

/// Returns build metadata as JSON.
async fn build_info() -> Json<BuildInfo> {
    Json(BuildInfo {
        version: env!("CARGO_PKG_VERSION"),
        git_hash: option_env!("VALLHERU_GIT_HASH").unwrap_or("unknown"),
    })
}
