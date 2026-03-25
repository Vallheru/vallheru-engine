use axum::Json;
use axum::extract::State;
use serde::Serialize;

use crate::AppState;

/// Process health — returns 200 if the server is running.
pub async fn healthz() -> axum::http::StatusCode {
    axum::http::StatusCode::OK
}

/// Readiness probe — returns 200 when all dependencies are available.
pub async fn readyz(State(state): State<AppState>) -> axum::http::StatusCode {
    match vallheru_data::pool::check_health(&state.pool).await {
        Ok(()) => axum::http::StatusCode::OK,
        Err(e) => {
            tracing::warn!(error = %e, "readiness check failed");
            axum::http::StatusCode::SERVICE_UNAVAILABLE
        }
    }
}

#[derive(Serialize)]
pub struct BuildInfo {
    pub version: &'static str,
    pub git_hash: &'static str,
}

/// Returns build metadata as JSON.
pub async fn build_info() -> Json<BuildInfo> {
    Json(BuildInfo {
        version: env!("CARGO_PKG_VERSION"),
        git_hash: option_env!("VALLHERU_GIT_HASH").unwrap_or("unknown"),
    })
}
