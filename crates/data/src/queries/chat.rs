//! Chat message and ban queries.

use sqlx::PgPool;

// =========================================================================
// Row types
// =========================================================================

/// A chat message row.
#[derive(Debug, sqlx::FromRow, serde::Serialize)]
pub struct ChatMessageRow {
    pub id: i64,
    pub author_html: String,
    pub body: String,
    pub sender_id: i64,
    pub recipient_id: i64,
    /// Seconds since the Unix epoch.
    pub created_epoch: i64,
}

/// A player currently on the chat page (for "online in tavern" list).
#[derive(Debug, sqlx::FromRow, serde::Serialize)]
pub struct ChatOnlineRow {
    pub id: i64,
    pub user: String,
}

// =========================================================================
// Message queries
// =========================================================================

/// Fetch recent public chat messages (`recipient_id` = 0), newest first.
pub async fn list_public_messages(
    pool: &PgPool,
    limit: i32,
) -> Result<Vec<ChatMessageRow>, sqlx::Error> {
    sqlx::query_as::<_, ChatMessageRow>(
        "SELECT id, author_html, body, sender_id, recipient_id,
                extract(epoch from created_at)::bigint AS created_epoch
         FROM chat_messages
         WHERE recipient_id = 0
         ORDER BY id DESC
         LIMIT $1",
    )
    .bind(i64::from(limit))
    .fetch_all(pool)
    .await
}

/// Fetch whisper messages between two players (newest first), created after
/// a given cutoff (Unix epoch seconds).
pub async fn list_whisper_messages(
    pool: &PgPool,
    player_id: i64,
    partner_id: i64,
    since_epoch: i64,
    limit: i32,
) -> Result<Vec<ChatMessageRow>, sqlx::Error> {
    sqlx::query_as::<_, ChatMessageRow>(
        "SELECT id, author_html, body, sender_id, recipient_id,
                extract(epoch from created_at)::bigint AS created_epoch
         FROM chat_messages
         WHERE ((recipient_id = $1 AND sender_id = $2)
            OR  (recipient_id = $2 AND sender_id = $1))
           AND created_at > to_timestamp($3)
         ORDER BY id DESC
         LIMIT $4",
    )
    .bind(player_id)
    .bind(partner_id)
    .bind(since_epoch)
    .bind(i64::from(limit))
    .fetch_all(pool)
    .await
}

/// Insert a new chat message.
pub async fn insert_message(
    pool: &PgPool,
    author_html: &str,
    body: &str,
    sender_id: i64,
    recipient_id: i64,
) -> Result<i64, sqlx::Error> {
    let row = sqlx::query_scalar::<_, i64>(
        "INSERT INTO chat_messages (author_html, body, sender_id, recipient_id)
         VALUES ($1, $2, $3, $4)
         RETURNING id",
    )
    .bind(author_html)
    .bind(body)
    .bind(sender_id)
    .bind(recipient_id)
    .fetch_one(pool)
    .await?;
    Ok(row)
}

/// Delete a single chat message by ID (admin action).
pub async fn delete_message(pool: &PgPool, message_id: i64) -> Result<(), sqlx::Error> {
    sqlx::query("DELETE FROM chat_messages WHERE id = $1")
        .bind(message_id)
        .execute(pool)
        .await?;
    Ok(())
}

/// Delete all public chat messages (admin prune).
pub async fn prune_public_messages(pool: &PgPool) -> Result<(), sqlx::Error> {
    sqlx::query("DELETE FROM chat_messages WHERE recipient_id = 0")
        .execute(pool)
        .await?;
    Ok(())
}

/// Count total public messages.
pub async fn count_public_messages(pool: &PgPool) -> Result<i64, sqlx::Error> {
    sqlx::query_scalar::<_, i64>("SELECT count(id) FROM chat_messages WHERE recipient_id = 0")
        .fetch_one(pool)
        .await
}

/// Find distinct whisper partners for a player (other players who sent
/// whispers to this player).
pub async fn whisper_senders(pool: &PgPool, player_id: i64) -> Result<Vec<i64>, sqlx::Error> {
    sqlx::query_scalar::<_, i64>(
        "SELECT DISTINCT sender_id FROM chat_messages
         WHERE recipient_id = $1
         ORDER BY sender_id",
    )
    .bind(player_id)
    .fetch_all(pool)
    .await
}

/// Find distinct whisper recipients this player has sent messages to.
pub async fn whisper_recipients(pool: &PgPool, player_id: i64) -> Result<Vec<i64>, sqlx::Error> {
    sqlx::query_scalar::<_, i64>(
        "SELECT DISTINCT recipient_id FROM chat_messages
         WHERE sender_id = $1 AND recipient_id != 0
         ORDER BY recipient_id",
    )
    .bind(player_id)
    .fetch_all(pool)
    .await
}

/// Count whisper messages between two players after a Unix epoch timestamp.
pub async fn count_whispers_since(
    pool: &PgPool,
    player_id: i64,
    partner_id: i64,
    since_epoch: i64,
) -> Result<i64, sqlx::Error> {
    sqlx::query_scalar::<_, i64>(
        "SELECT count(id) FROM chat_messages
         WHERE ((recipient_id = $1 AND sender_id = $2)
            OR  (recipient_id = $2 AND sender_id = $1))
           AND created_at > to_timestamp($3)",
    )
    .bind(player_id)
    .bind(partner_id)
    .bind(since_epoch)
    .fetch_one(pool)
    .await
}

/// Count public messages after a Unix epoch timestamp.
pub async fn count_public_since(pool: &PgPool, since_epoch: i64) -> Result<i64, sqlx::Error> {
    sqlx::query_scalar::<_, i64>(
        "SELECT count(id) FROM chat_messages
         WHERE recipient_id = 0 AND created_at > to_timestamp($1)",
    )
    .bind(since_epoch)
    .fetch_one(pool)
    .await
}

// =========================================================================
// Ban queries
// =========================================================================

/// Check if a player is banned from chat.
pub async fn is_banned(pool: &PgPool, player_id: i64) -> Result<bool, sqlx::Error> {
    let count =
        sqlx::query_scalar::<_, i64>("SELECT count(id) FROM chat_bans WHERE player_id = $1")
            .bind(player_id)
            .fetch_one(pool)
            .await?;
    Ok(count > 0)
}

/// Ban a player from chat for a given number of reset cycles.
pub async fn ban_player(pool: &PgPool, player_id: i64, resets: i32) -> Result<(), sqlx::Error> {
    sqlx::query(
        "INSERT INTO chat_bans (player_id, resets) VALUES ($1, $2)
         ON CONFLICT (player_id) DO UPDATE SET resets = $2",
    )
    .bind(player_id)
    .bind(resets)
    .execute(pool)
    .await?;
    Ok(())
}

/// Unban a player from chat.
pub async fn unban_player(pool: &PgPool, player_id: i64) -> Result<(), sqlx::Error> {
    sqlx::query("DELETE FROM chat_bans WHERE player_id = $1")
        .bind(player_id)
        .execute(pool)
        .await?;
    Ok(())
}

// =========================================================================
// Online players in tavern
// =========================================================================

/// List players who have `page = 'Chat'` and were active within the last
/// 180 seconds.
pub async fn online_in_tavern(pool: &PgPool) -> Result<Vec<ChatOnlineRow>, sqlx::Error> {
    sqlx::query_as::<_, ChatOnlineRow>(
        "SELECT id, \"user\" FROM players
         WHERE page = 'Chat'
           AND lpv >= (extract(epoch from now()) - 180)::bigint
         ORDER BY \"user\"",
    )
    .fetch_all(pool)
    .await
}

/// Update a player's current page to 'Chat'.
pub async fn set_page_chat(pool: &PgPool, player_id: i64) -> Result<(), sqlx::Error> {
    sqlx::query("UPDATE players SET page = 'Chat' WHERE id = $1")
        .bind(player_id)
        .execute(pool)
        .await?;
    Ok(())
}

// =========================================================================
// Bad words
// =========================================================================

/// Fetch all bad words for the content filter.
pub async fn list_bad_words(pool: &PgPool) -> Result<Vec<String>, sqlx::Error> {
    sqlx::query_scalar::<_, String>("SELECT bword FROM bad_words ORDER BY id")
        .fetch_all(pool)
        .await
}

// =========================================================================
// Player name lookup
// =========================================================================

/// Look up a player name by ID (for whisper target resolution).
pub async fn player_name_by_id(
    pool: &PgPool,
    player_id: i64,
) -> Result<Option<String>, sqlx::Error> {
    sqlx::query_scalar::<_, String>("SELECT \"user\" FROM players WHERE id = $1")
        .bind(player_id)
        .fetch_optional(pool)
        .await
}

/// Look up player names for a set of IDs.
pub async fn player_names_by_ids(
    pool: &PgPool,
    ids: &[i64],
) -> Result<Vec<(i64, String)>, sqlx::Error> {
    if ids.is_empty() {
        return Ok(vec![]);
    }
    sqlx::query_as::<_, (i64, String)>("SELECT id, \"user\" FROM players WHERE id = ANY($1)")
        .bind(ids)
        .fetch_all(pool)
        .await
}

/// Look up a player's `tribe_id` (0 if no tribe).
pub async fn player_tribe_id(pool: &PgPool, player_id: i64) -> Result<i32, sqlx::Error> {
    sqlx::query_scalar::<_, i32>("SELECT tribe_id FROM players WHERE id = $1")
        .bind(player_id)
        .fetch_optional(pool)
        .await
        .map(|opt| opt.unwrap_or(0))
}

/// Check if the innkeeper role player is actively on chat (within 180s).
pub async fn innkeeper_on_chat(pool: &PgPool) -> Result<Option<String>, sqlx::Error> {
    sqlx::query_scalar::<_, String>(
        "SELECT \"user\" FROM players
         WHERE rank = 'Karczmarka'
           AND page = 'Chat'
           AND lpv >= (extract(epoch from now()) - 180)::bigint
         LIMIT 1",
    )
    .fetch_optional(pool)
    .await
}
