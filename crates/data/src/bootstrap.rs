//! Bootstrap operations for setting up a new game instance.

use sqlx::PgPool;

/// Create the initial admin account if no admin exists yet.
pub async fn create_admin_account(
    pool: &PgPool,
    username: &str,
    email: &str,
    pass_hash: &str,
) -> anyhow::Result<bool> {
    if admin_account_exists(pool).await? {
        return Ok(false);
    }

    sqlx::query(
        "INSERT INTO players (username, email, pass_hash, rank, settings)
         VALUES ($1, $2, $3, 'Admin', '{}')",
    )
    .bind(username)
    .bind(email)
    .bind(pass_hash)
    .execute(pool)
    .await?;

    Ok(true)
}

/// Check whether any admin account already exists.
pub async fn admin_account_exists(pool: &PgPool) -> anyhow::Result<bool> {
    let count: (i64,) = sqlx::query_as("SELECT count(*) FROM players WHERE rank = 'Admin'")
        .fetch_one(pool)
        .await?;
    Ok(count.0 > 0)
}
