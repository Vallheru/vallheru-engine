//! Session store queries for the `sessions` table.
//!
//! Maps the `sessions` table to typed session operations. The session ID
//! is a 64-character hex string generated from OS randomness.

use serde_json::Value as JsonValue;
use sqlx::PgPool;

/// A session row from the database.
#[derive(Debug, sqlx::FromRow)]
pub struct SessionRow {
    pub id: String,
    pub player_id: Option<i32>,
    pub data: JsonValue,
}

/// Create a new session and return its ID.
pub async fn create_session(
    pool: &PgPool,
    session_id: &str,
    player_id: Option<i32>,
) -> Result<(), sqlx::Error> {
    sqlx::query(
        "INSERT INTO sessions (id, player_id) VALUES ($1, $2) \
         ON CONFLICT (id) DO UPDATE SET player_id = EXCLUDED.player_id, \
         expires_at = NOW() + INTERVAL '24 hours'",
    )
    .bind(session_id)
    .bind(player_id)
    .execute(pool)
    .await?;
    Ok(())
}

/// Load a session by ID, only if it has not expired.
pub async fn load_session(
    pool: &PgPool,
    session_id: &str,
) -> Result<Option<SessionRow>, sqlx::Error> {
    sqlx::query_as::<_, SessionRow>(
        "SELECT id, player_id, data FROM sessions WHERE id = $1 AND expires_at > NOW()",
    )
    .bind(session_id)
    .fetch_optional(pool)
    .await
}

/// Update the session's player ID (used on login to bind an anonymous
/// session to a player, and on logout to clear it).
pub async fn set_session_player(
    pool: &PgPool,
    session_id: &str,
    player_id: Option<i32>,
) -> Result<(), sqlx::Error> {
    sqlx::query("UPDATE sessions SET player_id = $1, expires_at = NOW() + INTERVAL '24 hours' WHERE id = $2")
        .bind(player_id)
        .bind(session_id)
        .execute(pool)
        .await?;
    Ok(())
}

/// Store a JSON blob in the session's `data` column.
pub async fn set_session_data(
    pool: &PgPool,
    session_id: &str,
    data: &JsonValue,
) -> Result<(), sqlx::Error> {
    sqlx::query("UPDATE sessions SET data = $1 WHERE id = $2")
        .bind(data)
        .bind(session_id)
        .execute(pool)
        .await?;
    Ok(())
}

/// Delete a session (used on logout).
pub async fn delete_session(pool: &PgPool, session_id: &str) -> Result<(), sqlx::Error> {
    sqlx::query("DELETE FROM sessions WHERE id = $1")
        .bind(session_id)
        .execute(pool)
        .await?;
    Ok(())
}

/// Delete a session and create a new one with the same player, returning
/// the new session ID. This defends against session fixation attacks.
pub async fn regenerate_session(
    pool: &PgPool,
    old_id: &str,
    new_id: &str,
    player_id: Option<i32>,
) -> Result<(), sqlx::Error> {
    // Copy data from old session if it exists.
    let data = match load_session(pool, old_id).await? {
        Some(row) => row.data,
        None => JsonValue::Object(serde_json::Map::new()),
    };

    // Delete old.
    delete_session(pool, old_id).await?;

    // Create new with same data.
    sqlx::query(
        "INSERT INTO sessions (id, player_id, data) VALUES ($1, $2, $3) \
         ON CONFLICT (id) DO UPDATE SET player_id = EXCLUDED.player_id, \
         data = EXCLUDED.data, expires_at = NOW() + INTERVAL '24 hours'",
    )
    .bind(new_id)
    .bind(player_id)
    .bind(&data)
    .execute(pool)
    .await?;
    Ok(())
}

/// Remove all expired sessions (for periodic cleanup).
pub async fn purge_expired(pool: &PgPool) -> Result<u64, sqlx::Error> {
    let result = sqlx::query("DELETE FROM sessions WHERE expires_at <= NOW()")
        .execute(pool)
        .await?;
    Ok(result.rows_affected())
}

/// Minimal player data returned for session context population.
#[derive(Debug, sqlx::FromRow)]
pub struct SessionPlayer {
    pub id: i32,
    pub username: String,
    pub rank: String,
}

/// Load minimal player data for populating session context.
pub async fn load_session_player(
    pool: &PgPool,
    player_id: i32,
) -> Result<Option<SessionPlayer>, sqlx::Error> {
    sqlx::query_as::<_, SessionPlayer>("SELECT id, username, rank FROM players WHERE id = $1")
        .bind(player_id)
        .fetch_optional(pool)
        .await
}
