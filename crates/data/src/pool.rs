pub use sqlx::PgPool;
use sqlx::postgres::PgPoolOptions;

/// Create a Postgres connection pool from a database URL.
pub async fn create_pool(database_url: &str, max_connections: u32) -> anyhow::Result<PgPool> {
    let pool = PgPoolOptions::new()
        .max_connections(max_connections)
        .connect(database_url)
        .await?;

    tracing::info!(max_connections, "database pool connected");
    Ok(pool)
}

/// Check that the database connection is alive.
pub async fn check_health(pool: &PgPool) -> anyhow::Result<()> {
    sqlx::query("SELECT 1").execute(pool).await?;
    Ok(())
}

/// Type alias for a Postgres transaction.
///
/// Callers obtain a transaction via `pool.begin().await?`, execute queries
/// against `&mut *tx`, and explicitly call `tx.commit().await?`. This keeps
/// transaction boundaries visible at the call site.
pub type Tx<'a> = sqlx::Transaction<'a, sqlx::Postgres>;
