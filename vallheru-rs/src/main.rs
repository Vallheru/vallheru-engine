mod config;
mod players;
mod web;

use tracing::{info, Level};
use tracing_subscriber::FmtSubscriber;

use crate::config::ServerConfig;

#[tokio::main]
async fn main() {
    init_tracing();

    let router = web::build_router();
    let listener = tokio::net::TcpListener::bind(ServerConfig::address())
        .await
        .expect("failed to bind TCP listener");

    info!(address = %listener.local_addr().unwrap(), "starting Vallheru server");

    if let Err(error) = axum::serve(listener, router.into_make_service()).await {
        tracing::error!(%error, "server exited with error");
    }
}

fn init_tracing() {
    let subscriber = FmtSubscriber::builder()
        .with_max_level(Level::INFO)
        .with_env_filter(tracing_subscriber::EnvFilter::from_default_env())
        .finish();

    tracing::subscriber::set_global_default(subscriber).expect("setting default subscriber failed");
}

#[cfg(test)]
mod tests {
    use axum::http::{Request, StatusCode};
    use tower::ServiceExt;

    use super::*;

    #[tokio::test]
    async fn health_check_returns_ok() {
        let app = web::build_router();

        let response = app
            .oneshot(Request::builder().uri("/health").body(axum::body::Body::empty()).unwrap())
            .await
            .unwrap();

        assert_eq!(response.status(), StatusCode::OK);
    }
}
