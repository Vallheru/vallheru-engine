use axum::Json;
use serde::Serialize;

/// Process health — returns 200 if the server is running.
pub async fn healthz() -> axum::http::StatusCode {
    axum::http::StatusCode::OK
}

/// Readiness probe — returns 200 when all dependencies are available.
/// Currently a stub; will check the database pool once wired.
pub async fn readyz() -> axum::http::StatusCode {
    // TODO: check database connectivity once the pool is in app state.
    axum::http::StatusCode::OK
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
