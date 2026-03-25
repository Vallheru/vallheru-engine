use sqlx::postgres::PgPoolOptions;
use std::path::Path;

/// Run all pending SQL migrations from the `migrations/` directory.
pub async fn run_migrations(database_url: &str) -> anyhow::Result<()> {
    tracing::info!("connecting to database for migration");

    let pool = PgPoolOptions::new()
        .max_connections(1)
        .connect(database_url)
        .await?;

    tracing::info!("running migrations");

    // Discover the migrations directory relative to the workspace root.
    // sqlx looks for ./migrations by default when using the migrate! macro,
    // but for runtime migration we point at the directory explicitly.
    let migrations_dir = find_migrations_dir()?;
    let migrator = sqlx::migrate::Migrator::new(migrations_dir).await?;

    migrator.run(&pool).await?;

    tracing::info!("migrations complete");
    pool.close().await;
    Ok(())
}

/// Walk upward from the current directory to find a `migrations/` folder.
fn find_migrations_dir() -> anyhow::Result<std::path::PathBuf> {
    let mut dir = std::env::current_dir()?;
    loop {
        let candidate = dir.join("migrations");
        if candidate.is_dir() {
            return Ok(candidate);
        }
        if !dir.pop() {
            break;
        }
    }
    // Fallback: check next to the binary.
    let exe = std::env::current_exe()?;
    if let Some(parent) = exe.parent() {
        let candidate = parent.join("migrations");
        if candidate.is_dir() {
            return Ok(candidate);
        }
    }
    anyhow::bail!(
        "could not find migrations/ directory from {} or binary location",
        Path::new(".").canonicalize()?.display()
    );
}
