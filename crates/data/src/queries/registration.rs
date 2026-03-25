//! Registration queries against activations and players tables.

use sqlx::PgPool;

/// Check if registration is currently open by reading the `register` setting.
///
/// Returns `true` if open (value != "N"), `false` if closed.
/// Also returns the close reason if registration is closed.
pub async fn is_registration_open(pool: &PgPool) -> Result<(bool, String), sqlx::Error> {
    let row: Option<(Option<String>,)> =
        sqlx::query_as("SELECT value FROM settings WHERE setting = 'register'")
            .fetch_optional(pool)
            .await?;

    let is_open = match &row {
        Some((Some(v),)) => v != "N",
        _ => true, // Default to open if setting is missing.
    };

    if !is_open {
        let reason_row: Option<(Option<String>,)> =
            sqlx::query_as("SELECT value FROM settings WHERE setting = 'close_register'")
                .fetch_optional(pool)
                .await?;
        let reason = reason_row.and_then(|(v,)| v).unwrap_or_default();
        return Ok((false, reason));
    }

    Ok((true, String::new()))
}

/// Count total registered players.
pub async fn count_players(pool: &PgPool) -> Result<i64, sqlx::Error> {
    let row: (i64,) = sqlx::query_as("SELECT COUNT(id) FROM players")
        .fetch_one(pool)
        .await?;
    Ok(row.0)
}

/// Check if a username is already taken.
pub async fn username_exists(pool: &PgPool, username: &str) -> Result<bool, sqlx::Error> {
    let row: (bool,) = sqlx::query_as("SELECT EXISTS(SELECT 1 FROM players WHERE username = $1)")
        .bind(username)
        .fetch_one(pool)
        .await?;
    Ok(row.0)
}

/// Check if an email is already registered.
pub async fn email_exists(pool: &PgPool, email: &str) -> Result<bool, sqlx::Error> {
    let row: (bool,) = sqlx::query_as("SELECT EXISTS(SELECT 1 FROM players WHERE email = $1)")
        .bind(email)
        .fetch_one(pool)
        .await?;
    Ok(row.0)
}

/// Data needed to create a pending activation record.
pub struct NewActivation<'a> {
    pub username: &'a str,
    pub email: &'a str,
    pub pass_hash: &'a str,
    pub token: i32,
    pub referrer: i32,
    pub ip: &'a str,
    pub game_type: &'a str,
}

/// Insert a pending activation record.
///
/// The record is stored in the `activations` table and awaits email
/// confirmation before being promoted to a full player account.
pub async fn insert_activation(pool: &PgPool, data: &NewActivation<'_>) -> Result<(), sqlx::Error> {
    sqlx::query(
        "INSERT INTO activations (username, email, pass_hash, token, referrer, ip, game_type) \
         VALUES ($1, $2, $3, $4, $5, $6, $7)",
    )
    .bind(data.username)
    .bind(data.email)
    .bind(data.pass_hash)
    .bind(data.token)
    .bind(data.referrer)
    .bind(data.ip)
    .bind(data.game_type)
    .execute(pool)
    .await?;
    Ok(())
}
