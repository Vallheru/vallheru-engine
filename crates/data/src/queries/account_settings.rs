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
    pub immune: bool,
    pub class: String,
    pub freeze: i16,
    pub roleplay: String,
    pub ooc: String,
    pub short_rpg: String,
}

/// Load account info for the settings page.
pub async fn load_account_info(
    pool: &PgPool,
    player_id: i32,
) -> Result<Option<AccountInfo>, sqlx::Error> {
    sqlx::query_as::<_, AccountInfo>(
        "SELECT id, username, email, avatar, profile, messenger, vallars, \
                immune, class, \"freeze\", roleplay, ooc, short_rpg \
         FROM players WHERE id = $1",
    )
    .bind(player_id)
    .fetch_optional(pool)
    .await
}

// =========================================================================
// Account freeze
// =========================================================================

/// Set the freeze counter and delete all sessions for the player.
pub async fn freeze_account(pool: &PgPool, player_id: i32, days: i16) -> Result<(), sqlx::Error> {
    let mut tx = pool.begin().await?;
    sqlx::query("UPDATE players SET \"freeze\" = $1 WHERE id = $2")
        .bind(days)
        .bind(player_id)
        .execute(&mut *tx)
        .await?;
    sqlx::query("DELETE FROM sessions WHERE player_id = $1")
        .bind(player_id)
        .execute(&mut *tx)
        .await?;
    tx.commit().await?;
    Ok(())
}

// =========================================================================
// Immunity
// =========================================================================

/// Set the player's immunity flag.
pub async fn set_immunity(pool: &PgPool, player_id: i32, immune: bool) -> Result<(), sqlx::Error> {
    sqlx::query("UPDATE players SET immune = $1 WHERE id = $2")
        .bind(immune)
        .bind(player_id)
        .execute(pool)
        .await?;
    Ok(())
}

// =========================================================================
// Roleplay profile
// =========================================================================

/// Update the player's roleplay profile fields.
pub async fn update_roleplay(
    pool: &PgPool,
    player_id: i32,
    roleplay: &str,
    ooc: &str,
    short_rpg: &str,
) -> Result<(), sqlx::Error> {
    sqlx::query("UPDATE players SET roleplay = $1, ooc = $2, short_rpg = $3 WHERE id = $4")
        .bind(roleplay)
        .bind(ooc)
        .bind(short_rpg)
        .bind(player_id)
        .execute(pool)
        .await?;
    Ok(())
}

// =========================================================================
// Blocked users (ignored list)
// =========================================================================

/// One row from the block list with the target player's display name.
#[derive(Debug, sqlx::FromRow)]
pub struct BlockEntry {
    pub id: i64,
    pub blocked_id: i64,
    pub blocked_name: String,
    pub block_mail: bool,
    pub block_chat: bool,
}

/// List all blocked users for a player.
pub async fn list_blocked(pool: &PgPool, owner_id: i64) -> Result<Vec<BlockEntry>, sqlx::Error> {
    sqlx::query_as::<_, BlockEntry>(
        "SELECT b.id, b.blocked_id, p.username AS blocked_name, \
                b.block_mail, b.block_chat \
         FROM mail_blocks b \
         JOIN players p ON p.id = b.blocked_id \
         WHERE b.owner_id = $1 \
         ORDER BY p.username",
    )
    .bind(owner_id)
    .fetch_all(pool)
    .await
}

/// Add a player to the block list (blocks both mail and chat by default).
/// Returns `true` if a new row was inserted, `false` if already blocked.
pub async fn add_blocked(
    pool: &PgPool,
    owner_id: i64,
    blocked_id: i64,
) -> Result<bool, sqlx::Error> {
    let result = sqlx::query(
        "INSERT INTO mail_blocks (owner_id, blocked_id, block_mail, block_chat) \
         VALUES ($1, $2, TRUE, TRUE) \
         ON CONFLICT (owner_id, blocked_id) DO NOTHING",
    )
    .bind(owner_id)
    .bind(blocked_id)
    .execute(pool)
    .await?;
    Ok(result.rows_affected() > 0)
}

/// Remove a block entry by its row ID (only if owned by `owner_id`).
pub async fn remove_blocked(
    pool: &PgPool,
    owner_id: i64,
    block_id: i64,
) -> Result<(), sqlx::Error> {
    sqlx::query("DELETE FROM mail_blocks WHERE id = $1 AND owner_id = $2")
        .bind(block_id)
        .bind(owner_id)
        .execute(pool)
        .await?;
    Ok(())
}

/// Update block flags for a specific entry.
pub async fn update_block_flags(
    pool: &PgPool,
    owner_id: i64,
    block_id: i64,
    block_mail: bool,
    block_chat: bool,
) -> Result<(), sqlx::Error> {
    sqlx::query(
        "UPDATE mail_blocks SET block_mail = $1, block_chat = $2 \
         WHERE id = $3 AND owner_id = $4",
    )
    .bind(block_mail)
    .bind(block_chat)
    .bind(block_id)
    .bind(owner_id)
    .execute(pool)
    .await?;
    Ok(())
}

// =========================================================================
// Quick links
// =========================================================================

/// A player's custom navigation link.
#[derive(Debug, sqlx::FromRow)]
pub struct QuickLink {
    pub id: i64,
    pub label: String,
    pub url: String,
    pub sort_order: i32,
}

/// List all quick links for a player.
pub async fn list_links(pool: &PgPool, owner_id: i32) -> Result<Vec<QuickLink>, sqlx::Error> {
    sqlx::query_as::<_, QuickLink>(
        "SELECT id, label, url, sort_order \
         FROM player_links \
         WHERE owner_id = $1 \
         ORDER BY sort_order, id",
    )
    .bind(owner_id)
    .fetch_all(pool)
    .await
}

/// Insert a new quick link. Returns the new link ID.
pub async fn add_link(
    pool: &PgPool,
    owner_id: i32,
    label: &str,
    url: &str,
    sort_order: i32,
) -> Result<i64, sqlx::Error> {
    let row: (i64,) = sqlx::query_as(
        "INSERT INTO player_links (owner_id, label, url, sort_order) \
         VALUES ($1, $2, $3, $4) RETURNING id",
    )
    .bind(owner_id)
    .bind(label)
    .bind(url)
    .bind(sort_order)
    .fetch_one(pool)
    .await?;
    Ok(row.0)
}

/// Update an existing quick link (only if owned by `owner_id`).
pub async fn update_link(
    pool: &PgPool,
    owner_id: i32,
    link_id: i64,
    label: &str,
    url: &str,
    sort_order: i32,
) -> Result<(), sqlx::Error> {
    sqlx::query(
        "UPDATE player_links SET label = $1, url = $2, sort_order = $3 \
         WHERE id = $4 AND owner_id = $5",
    )
    .bind(label)
    .bind(url)
    .bind(sort_order)
    .bind(link_id)
    .bind(owner_id)
    .execute(pool)
    .await?;
    Ok(())
}

/// Delete a quick link (only if owned by `owner_id`).
pub async fn delete_link(pool: &PgPool, owner_id: i32, link_id: i64) -> Result<(), sqlx::Error> {
    sqlx::query("DELETE FROM player_links WHERE id = $1 AND owner_id = $2")
        .bind(link_id)
        .bind(owner_id)
        .execute(pool)
        .await?;
    Ok(())
}
