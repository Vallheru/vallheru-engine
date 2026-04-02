//! Authentication queries against the players table.

use sqlx::PgPool;

/// Minimal player credential row returned by login lookup.
#[derive(Debug, sqlx::FromRow)]
pub struct PlayerCredentials {
    pub id: i32,
    pub username: String,
    pub email: String,
    pub pass_hash: String,
    pub rank: String,
    pub freeze: i16,
}

/// Find a player by email for login verification.
///
/// Returns `None` if no player with the given email exists.
pub async fn find_by_email(
    pool: &PgPool,
    email: &str,
) -> Result<Option<PlayerCredentials>, sqlx::Error> {
    sqlx::query_as::<_, PlayerCredentials>(
        "SELECT id, username, email, pass_hash, rank, freeze FROM players WHERE email = $1",
    )
    .bind(email)
    .fetch_optional(pool)
    .await
}

/// Increment the login counter and clear resting flag.
pub async fn record_login(pool: &PgPool, player_id: i32) -> Result<(), sqlx::Error> {
    sqlx::query("UPDATE players SET logins = logins + 1, resting = FALSE WHERE id = $1")
        .bind(player_id)
        .execute(pool)
        .await?;
    Ok(())
}

/// Check if a player is banned by IP, ID, username, or email.
pub async fn is_banned(
    pool: &PgPool,
    ip: &str,
    player_id: i32,
    username: &str,
    email: &str,
) -> Result<bool, sqlx::Error> {
    let row: (bool,) = sqlx::query_as(
        r"SELECT EXISTS(
            SELECT 1 FROM bans
            WHERE (type = 'IP'        AND amount = $1)
               OR (type = 'ID'        AND amount = $2)
               OR (type = 'nick'      AND amount = $3)
               OR (type = 'mailadres' AND amount = $4)
        )",
    )
    .bind(ip)
    .bind(player_id.to_string())
    .bind(username)
    .bind(email)
    .fetch_one(pool)
    .await?;
    Ok(row.0)
}

/// Punish a player who was in combat when the session expired.
///
/// Matches the PHP logic: sets hp=0, fight=0, energy=energy-1.
pub async fn clear_stale_fight(pool: &PgPool, player_id: i32) -> Result<(), sqlx::Error> {
    sqlx::query(
        "UPDATE players SET hp = 0, fight = 0, energy = GREATEST(energy - 1, 0) \
         WHERE id = $1 AND fight != 0",
    )
    .bind(player_id)
    .execute(pool)
    .await?;
    Ok(())
}
