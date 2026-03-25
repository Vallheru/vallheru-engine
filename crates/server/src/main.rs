pub mod config;
pub mod health;

use config::AppConfig;

fn main() -> anyhow::Result<()> {
    // Initialize tracing early so all startup messages are captured.
    init_tracing();

    let config = AppConfig::load(resolve_config_path().as_deref())?;
    config.log_summary();

    let rt = tokio::runtime::Builder::new_multi_thread()
        .enable_all()
        .build()?;

    rt.block_on(run(config))
}

fn init_tracing() {
    use tracing_subscriber::{EnvFilter, fmt};

    let filter = EnvFilter::try_from_default_env().unwrap_or_else(|_| EnvFilter::new("info"));

    fmt().with_env_filter(filter).with_target(true).init();
}

/// Resolve the config file path: explicit env var, or default `vallheru.toml`.
fn resolve_config_path() -> Option<std::path::PathBuf> {
    if let Ok(p) = std::env::var("VALLHERU_CONFIG") {
        return Some(std::path::PathBuf::from(p));
    }
    let default = std::path::PathBuf::from("vallheru.toml");
    if default.exists() {
        Some(default)
    } else {
        None
    }
}

async fn run(config: AppConfig) -> anyhow::Result<()> {
    let bind = config.server.bind;

    let app = axum::Router::new()
        .route("/healthz", axum::routing::get(health::healthz))
        .route("/readyz", axum::routing::get(health::readyz))
        .route("/buildinfo", axum::routing::get(health::build_info));

    tracing::info!(%bind, "starting HTTP server");

    let listener = tokio::net::TcpListener::bind(bind).await?;
    axum::serve(listener, app)
        .with_graceful_shutdown(shutdown_signal())
        .await?;

    tracing::info!("server shut down");
    Ok(())
}

async fn shutdown_signal() {
    let ctrl_c = async {
        tokio::signal::ctrl_c()
            .await
            .expect("failed to install Ctrl+C handler");
    };

    #[cfg(unix)]
    let terminate = async {
        tokio::signal::unix::signal(tokio::signal::unix::SignalKind::terminate())
            .expect("failed to install SIGTERM handler")
            .recv()
            .await;
    };

    #[cfg(not(unix))]
    let terminate = std::future::pending::<()>();

    tokio::select! {
        () = ctrl_c => tracing::info!("received SIGINT"),
        () = terminate => tracing::info!("received SIGTERM"),
    }
}
