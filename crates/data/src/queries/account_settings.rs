//! Account settings and profile update queries.

use sqlx::PgPool;

/// Check if a username is already taken by another player.
pub async fn is_username_taken(
    pool: &PgPool,
    username: &str,
    exclude_player_id: i32,
) -> Result<bool, sqlx::Error> {
    let row: (bool,) = sqlx::query_as(
        "SELECT EXISTS(SELECT 1 FROM players WHERE LOWER(username) = LOWER($1) AND id != $2)",
    )
    .bind(username)
    .bind(exclude_player_id)
    .fetch_one(pool)
    .await?;
    Ok(row.0)
}

/// Update a player's display name.
pub async fn update_username(
    pool: &PgPool,
    player_id: i32,
    new_username: &str,
) -> Result<(), sqlx::Error> {
    sqlx::query("UPDATE players SET username = $1 WHERE id = $2")
        .bind(new_username)
        .bind(player_id)
        .execute(pool)
        .await?;
    Ok(())
}

/// Fetch a player's current password hash for verification.
pub async fn get_password_hash(
    pool: &PgPool,
    player_id: i32,
) -> Result<Option<String>, sqlx::Error> {
    let row: Option<(String,)> = sqlx::query_as("SELECT pass_hash FROM players WHERE id = $1")
        .bind(player_id)
        .fetch_optional(pool)
        .await?;
    Ok(row.map(|r| r.0))
}

/// Update a player's password hash.
pub async fn update_password(
    pool: &PgPool,
    player_id: i32,
    new_hash: &str,
) -> Result<(), sqlx::Error> {
    sqlx::query("UPDATE players SET pass_hash = $1 WHERE id = $2")
        .bind(new_hash)
        .bind(player_id)
        .execute(pool)
        .await?;
    Ok(())
}

/// Update a player's profile text.
pub async fn update_profile(
    pool: &PgPool,
    player_id: i32,
    profile: &str,
) -> Result<(), sqlx::Error> {
    sqlx::query("UPDATE players SET profile = $1 WHERE id = $2")
        .bind(profile)
        .bind(player_id)
        .execute(pool)
        .await?;
    Ok(())
}

/// Minimal player info needed for the account settings page.
#[derive(Debug, sqlx::FromRow)]
pub struct AccountInfo {
    pub id: i32,
    pub username: String,
    pub email: String,
    pub avatar: String,
    pub profile: String,
    pub messenger: String,
    pub vallars: i32,
}

/// Load minimal account info for the settings page.
pub async fn load_account_info(
    pool: &PgPool,
    player_id: i32,
) -> Result<Option<AccountInfo>, sqlx::Error> {
    sqlx::query_as::<_, AccountInfo>(
        "SELECT id, username, email, avatar, profile, messenger, vallars \
         FROM players WHERE id = $1",
    )
    .bind(player_id)
    .fetch_optional(pool)
    .await
}
