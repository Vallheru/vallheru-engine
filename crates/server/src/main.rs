pub mod cli;
pub mod config;
pub mod errors;

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
            let rt = tokio::runtime::Builder::new_current_thread()
                .enable_all()
                .build()?;
            rt.block_on(vallheru_data::migrate::run_migrations(&config.database.url))
        }
        Command::Import => {
            let rt = tokio::runtime::Builder::new_current_thread()
                .enable_all()
                .build()?;
            rt.block_on(vallheru_data::import::run_seeds(&config.database.url))
        }
        Command::Job { name } => {
            let job = vallheru_domain::admin::reset::Job::from_cli(&name)
                .ok_or_else(|| anyhow::anyhow!("unknown job: {name}"))?;
            let rt = tokio::runtime::Builder::new_current_thread()
                .enable_all()
                .build()?;
            rt.block_on(run_job(&config.database.url, job))
        }
        Command::ResetEra => {
            tracing::info!("reset-era: not yet implemented");
            Ok(())
        }
        Command::Reconcile => {
            let rt = tokio::runtime::Builder::new_current_thread()
                .enable_all()
                .build()?;
            rt.block_on(vallheru_data::reconcile::run_reconciliation(
                &config.database.url,
                config.database.legacy_url.as_deref(),
            ))
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

async fn run_job(
    database_url: &str,
    job: vallheru_domain::admin::reset::Job,
) -> anyhow::Result<()> {
    let pool = vallheru_data::pool::create_pool(database_url, 2).await?;
    let ran = vallheru_data::jobs::run_job(&pool, job).await?;
    if !ran {
        tracing::warn!(job = %job, "job skipped (lock held)");
    }
    Ok(())
}

async fn serve(config: AppConfig) -> anyhow::Result<()> {
    let bind = config.server.bind;

    let pool =
        vallheru_data::pool::create_pool(&config.database.url, config.database.max_connections)
            .await?;

    let catalog = vallheru_web::Catalog::load_embedded(&config.game.lang)
        .unwrap_or_else(|e| {
            tracing::warn!(locale = %config.game.lang, error = %e, "failed to load locale catalog, using empty");
            vallheru_web::Catalog::empty(&config.game.lang)
        });

    let templates = vallheru_web::TemplateEngine::new(
        &vallheru_web::TemplateEngineConfig {
            game_name: config.game.name.clone(),
            base_url: config.game.base_url.clone(),
        },
        &catalog,
    );

    let state = vallheru_web::AppState {
        pool: pool.clone(),
        context_defaults: vallheru_web::ContextDefaults {
            locale: config.game.lang.clone(),
        },
        templates,
        catalog,
    };
    let app = vallheru_web::build_router(state);

    tracing::info!(%bind, "starting HTTP server");

    let listener = tokio::net::TcpListener::bind(bind).await?;
    axum::serve(listener, app)
        .with_graceful_shutdown(shutdown_signal())
        .await?;

    pool.close().await;
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
