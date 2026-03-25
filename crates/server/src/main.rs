pub mod cli;
pub mod config;
pub mod errors;
pub mod health;

use clap::Parser;
use cli::{Cli, Command};
use config::AppConfig;

fn main() -> anyhow::Result<()> {
    // Initialize tracing early so all startup messages are captured.
    init_tracing();

    let cli = Cli::parse();

    let config_path = cli.config.or_else(resolve_default_config_path);

    let config = AppConfig::load(config_path.as_deref())?;
    config.log_summary();

    match cli.command {
        Command::Serve => {
            let rt = tokio::runtime::Builder::new_multi_thread()
                .enable_all()
                .build()?;
            rt.block_on(serve(config))
        }
        Command::Migrate => {
            tracing::info!("migrate: not yet implemented");
            Ok(())
        }
        Command::Import => {
            tracing::info!("import: not yet implemented");
            Ok(())
        }
        Command::ResetEra => {
            tracing::info!("reset-era: not yet implemented");
            Ok(())
        }
        Command::Reconcile => {
            tracing::info!("reconcile: not yet implemented");
            Ok(())
        }
    }
}

fn init_tracing() {
    use tracing_subscriber::{EnvFilter, fmt};

    let filter = EnvFilter::try_from_default_env().unwrap_or_else(|_| EnvFilter::new("info"));

    fmt().with_env_filter(filter).with_target(true).init();
}

/// Resolve the config file path from env or default.
fn resolve_default_config_path() -> Option<std::path::PathBuf> {
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

async fn serve(config: AppConfig) -> anyhow::Result<()> {
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
