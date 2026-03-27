//! Mail SQL queries — messages, contacts, and block lists.

use sqlx::PgPool;

// =========================================================================
// Row types
// =========================================================================

#[derive(sqlx::FromRow, Debug)]
pub struct MailMessageRow {
    pub id: i64,
    pub sender_id: i64,
    pub sender_name: String,
    pub owner_id: i64,
    pub recipient_id: i64,
    pub recipient_name: String,
    pub topic_id: i64,
    pub subject: String,
    pub body: String,
    pub is_read: bool,
    pub is_saved: bool,
    pub created_at_formatted: String,
}

/// Lightweight row for inbox/saved listing — one per topic.
#[derive(sqlx::FromRow, Debug)]
pub struct MailThreadRow {
    pub id: i64,
    pub sender_id: i64,
    pub sender_name: String,
    pub recipient_id: i64,
    pub recipient_name: String,
    pub topic_id: i64,
    pub subject: String,
    pub is_read: bool,
}

#[derive(sqlx::FromRow, Debug)]
pub struct ContactRow {
    pub player_id: i64,
    pub player_name: String,
}

#[derive(sqlx::FromRow, Debug)]
pub struct PlayerNameRow {
    pub id: i64,
    pub user_name: String,
}

// =========================================================================
// Message queries
// =========================================================================

/// List inbox threads (non-saved messages), newest-first, one row per topic.
/// Returns the latest message per topic.
pub async fn list_inbox_threads(
    pool: &PgPool,
    owner_id: i64,
    limit: i64,
    offset: i64,
) -> Result<Vec<MailThreadRow>, sqlx::Error> {
    sqlx::query_as::<_, MailThreadRow>(
        "SELECT DISTINCT ON (topic_id)
            id, sender_id, sender_name, recipient_id, recipient_name,
            topic_id, subject, is_read
         FROM mail_messages
         WHERE owner_id = $1 AND NOT is_saved
         ORDER BY topic_id DESC, id DESC
         LIMIT $2 OFFSET $3",
    )
    .bind(owner_id)
    .bind(limit)
    .bind(offset)
    .fetch_all(pool)
    .await
}

/// Count distinct topics in inbox.
pub async fn count_inbox_topics(pool: &PgPool, owner_id: i64) -> Result<i64, sqlx::Error> {
    sqlx::query_scalar::<_, i64>(
        "SELECT count(DISTINCT topic_id) FROM mail_messages
         WHERE owner_id = $1 AND NOT is_saved",
    )
    .bind(owner_id)
    .fetch_one(pool)
    .await
}

/// List saved message threads.
pub async fn list_saved_threads(
    pool: &PgPool,
    owner_id: i64,
    limit: i64,
    offset: i64,
) -> Result<Vec<MailThreadRow>, sqlx::Error> {
    sqlx::query_as::<_, MailThreadRow>(
        "SELECT DISTINCT ON (topic_id)
            id, sender_id, sender_name, recipient_id, recipient_name,
            topic_id, subject, is_read
         FROM mail_messages
         WHERE owner_id = $1 AND is_saved
         ORDER BY topic_id DESC, id DESC
         LIMIT $2 OFFSET $3",
    )
    .bind(owner_id)
    .bind(limit)
    .bind(offset)
    .fetch_all(pool)
    .await
}

/// Count distinct saved topics.
pub async fn count_saved_topics(pool: &PgPool, owner_id: i64) -> Result<i64, sqlx::Error> {
    sqlx::query_scalar::<_, i64>(
        "SELECT count(DISTINCT topic_id) FROM mail_messages
         WHERE owner_id = $1 AND is_saved",
    )
    .bind(owner_id)
    .fetch_one(pool)
    .await
}

/// Read a single thread's messages, oldest-first, paginated.
pub async fn list_thread_messages(
    pool: &PgPool,
    owner_id: i64,
    topic_id: i64,
    limit: i64,
    offset: i64,
) -> Result<Vec<MailMessageRow>, sqlx::Error> {
    sqlx::query_as::<_, MailMessageRow>(
        "SELECT id, sender_id, sender_name, owner_id, recipient_id, recipient_name,
                topic_id, subject, body, is_read, is_saved,
                to_char(created_at, 'YYYY-MM-DD HH24:MI') AS created_at_formatted
         FROM mail_messages
         WHERE owner_id = $1 AND topic_id = $2
         ORDER BY id ASC
         LIMIT $3 OFFSET $4",
    )
    .bind(owner_id)
    .bind(topic_id)
    .bind(limit)
    .bind(offset)
    .fetch_all(pool)
    .await
}

/// Count messages in a thread.
pub async fn count_thread_messages(
    pool: &PgPool,
    owner_id: i64,
    topic_id: i64,
) -> Result<i64, sqlx::Error> {
    sqlx::query_scalar::<_, i64>(
        "SELECT count(id) FROM mail_messages WHERE owner_id = $1 AND topic_id = $2",
    )
    .bind(owner_id)
    .bind(topic_id)
    .fetch_one(pool)
    .await
}

/// Read a single message (for one-off view).
pub async fn get_message(
    pool: &PgPool,
    owner_id: i64,
    message_id: i64,
) -> Result<Option<MailMessageRow>, sqlx::Error> {
    sqlx::query_as::<_, MailMessageRow>(
        "SELECT id, sender_id, sender_name, owner_id, recipient_id, recipient_name,
                topic_id, subject, body, is_read, is_saved,
                to_char(created_at, 'YYYY-MM-DD HH24:MI') AS created_at_formatted
         FROM mail_messages
         WHERE owner_id = $1 AND id = $2",
    )
    .bind(owner_id)
    .bind(message_id)
    .fetch_optional(pool)
    .await
}

/// Mark all messages in a thread as read.
pub async fn mark_thread_read(
    pool: &PgPool,
    owner_id: i64,
    topic_id: i64,
) -> Result<(), sqlx::Error> {
    sqlx::query(
        "UPDATE mail_messages SET is_read = TRUE
         WHERE owner_id = $1 AND topic_id = $2 AND NOT is_read",
    )
    .bind(owner_id)
    .bind(topic_id)
    .execute(pool)
    .await?;
    Ok(())
}

/// Mark specific messages as read.
pub async fn mark_messages_read(
    pool: &PgPool,
    owner_id: i64,
    ids: &[i64],
) -> Result<(), sqlx::Error> {
    sqlx::query(
        "UPDATE mail_messages SET is_read = TRUE
         WHERE owner_id = $1 AND id = ANY($2)",
    )
    .bind(owner_id)
    .bind(ids)
    .execute(pool)
    .await?;
    Ok(())
}

/// Mark specific messages as unread.
pub async fn mark_messages_unread(
    pool: &PgPool,
    owner_id: i64,
    ids: &[i64],
) -> Result<(), sqlx::Error> {
    sqlx::query(
        "UPDATE mail_messages SET is_read = FALSE
         WHERE owner_id = $1 AND id = ANY($2)",
    )
    .bind(owner_id)
    .bind(ids)
    .execute(pool)
    .await?;
    Ok(())
}

/// Delete all messages in the given topics owned by the player.
pub async fn delete_by_topics(
    pool: &PgPool,
    owner_id: i64,
    topic_ids: &[i64],
) -> Result<(), sqlx::Error> {
    sqlx::query("DELETE FROM mail_messages WHERE owner_id = $1 AND topic_id = ANY($2)")
        .bind(owner_id)
        .bind(topic_ids)
        .execute(pool)
        .await?;
    Ok(())
}

/// Delete a single message by id.
pub async fn delete_message(
    pool: &PgPool,
    owner_id: i64,
    message_id: i64,
) -> Result<(), sqlx::Error> {
    sqlx::query("DELETE FROM mail_messages WHERE owner_id = $1 AND id = $2")
        .bind(owner_id)
        .bind(message_id)
        .execute(pool)
        .await?;
    Ok(())
}

/// Delete all inbox (non-saved) messages.
pub async fn clear_inbox(pool: &PgPool, owner_id: i64) -> Result<(), sqlx::Error> {
    sqlx::query("DELETE FROM mail_messages WHERE owner_id = $1 AND NOT is_saved")
        .bind(owner_id)
        .execute(pool)
        .await?;
    Ok(())
}

/// Delete all saved messages.
pub async fn clear_saved(pool: &PgPool, owner_id: i64) -> Result<(), sqlx::Error> {
    sqlx::query("DELETE FROM mail_messages WHERE owner_id = $1 AND is_saved")
        .bind(owner_id)
        .execute(pool)
        .await?;
    Ok(())
}

/// Delete old messages (non-saved) older than a given number of days.
pub async fn delete_old_inbox(pool: &PgPool, owner_id: i64, days: i32) -> Result<(), sqlx::Error> {
    sqlx::query(
        "DELETE FROM mail_messages
         WHERE owner_id = $1 AND NOT is_saved
           AND created_at < now() - make_interval(days => $2)",
    )
    .bind(owner_id)
    .bind(days)
    .execute(pool)
    .await?;
    Ok(())
}

/// Delete old saved messages older than a given number of days.
pub async fn delete_old_saved(pool: &PgPool, owner_id: i64, days: i32) -> Result<(), sqlx::Error> {
    sqlx::query(
        "DELETE FROM mail_messages
         WHERE owner_id = $1 AND is_saved
           AND created_at < now() - make_interval(days => $2)",
    )
    .bind(owner_id)
    .bind(days)
    .execute(pool)
    .await?;
    Ok(())
}

/// Mark a message as saved.
pub async fn save_message(
    pool: &PgPool,
    owner_id: i64,
    message_id: i64,
) -> Result<(), sqlx::Error> {
    sqlx::query(
        "UPDATE mail_messages SET is_saved = TRUE
         WHERE owner_id = $1 AND id = $2",
    )
    .bind(owner_id)
    .bind(message_id)
    .execute(pool)
    .await?;
    Ok(())
}

// =========================================================================
// Send messages
// =========================================================================

/// Get the next topic id.
pub async fn next_topic_id(pool: &PgPool) -> Result<i64, sqlx::Error> {
    let max: Option<i64> = sqlx::query_scalar("SELECT max(topic_id) FROM mail_messages")
        .fetch_one(pool)
        .await?;
    Ok(max.unwrap_or(0) + 1)
}

/// Parameters for inserting a mail message.
pub struct InsertMessageParams<'a> {
    pub sender_id: i64,
    pub sender_name: &'a str,
    pub owner_id: i64,
    pub recipient_id: i64,
    pub recipient_name: &'a str,
    pub topic_id: i64,
    pub subject: &'a str,
    pub body: &'a str,
    pub is_read: bool,
}

/// Insert a mail message.
pub async fn insert_message(
    pool: &PgPool,
    p: &InsertMessageParams<'_>,
) -> Result<i64, sqlx::Error> {
    sqlx::query_scalar::<_, i64>(
        "INSERT INTO mail_messages
            (sender_id, sender_name, owner_id, recipient_id, recipient_name,
             topic_id, subject, body, is_read)
         VALUES ($1, $2, $3, $4, $5, $6, $7, $8, $9)
         RETURNING id",
    )
    .bind(p.sender_id)
    .bind(p.sender_name)
    .bind(p.owner_id)
    .bind(p.recipient_id)
    .bind(p.recipient_name)
    .bind(p.topic_id)
    .bind(p.subject)
    .bind(p.body)
    .bind(p.is_read)
    .fetch_one(pool)
    .await
}

// =========================================================================
// Unread counters
// =========================================================================

/// Count unread messages for a player.
pub async fn count_unread(pool: &PgPool, owner_id: i64) -> Result<i64, sqlx::Error> {
    sqlx::query_scalar::<_, i64>(
        "SELECT count(id) FROM mail_messages WHERE owner_id = $1 AND NOT is_read",
    )
    .bind(owner_id)
    .fetch_one(pool)
    .await
}

// =========================================================================
// Contacts
// =========================================================================

/// List contacts for a player, ordered by `sort_order`.
pub async fn list_contacts(pool: &PgPool, owner_id: i64) -> Result<Vec<ContactRow>, sqlx::Error> {
    sqlx::query_as::<_, ContactRow>(
        "SELECT mc.player_id, p.user_name AS player_name
         FROM mail_contacts mc
         JOIN players p ON mc.player_id = p.id
         WHERE mc.owner_id = $1
         ORDER BY mc.sort_order ASC",
    )
    .bind(owner_id)
    .fetch_all(pool)
    .await
}

/// Add a contact.
pub async fn add_contact(pool: &PgPool, owner_id: i64, player_id: i64) -> Result<(), sqlx::Error> {
    sqlx::query(
        "INSERT INTO mail_contacts (owner_id, player_id)
         VALUES ($1, $2) ON CONFLICT DO NOTHING",
    )
    .bind(owner_id)
    .bind(player_id)
    .execute(pool)
    .await?;
    Ok(())
}

/// Remove a contact.
pub async fn remove_contact(
    pool: &PgPool,
    owner_id: i64,
    player_id: i64,
) -> Result<(), sqlx::Error> {
    sqlx::query("DELETE FROM mail_contacts WHERE owner_id = $1 AND player_id = $2")
        .bind(owner_id)
        .bind(player_id)
        .execute(pool)
        .await?;
    Ok(())
}

// =========================================================================
// Block list
// =========================================================================

/// Check if sender is blocked from mailing the owner.
pub async fn is_mail_blocked(
    pool: &PgPool,
    owner_id: i64,
    sender_id: i64,
) -> Result<bool, sqlx::Error> {
    let row: Option<bool> = sqlx::query_scalar(
        "SELECT block_mail FROM mail_blocks WHERE owner_id = $1 AND blocked_id = $2",
    )
    .bind(owner_id)
    .bind(sender_id)
    .fetch_optional(pool)
    .await?;
    Ok(row.unwrap_or(false))
}

/// Toggle mail block for a player.  Returns the new blocked state.
pub async fn toggle_mail_block(
    pool: &PgPool,
    owner_id: i64,
    blocked_id: i64,
) -> Result<bool, sqlx::Error> {
    // Check current state.
    let current: Option<bool> = sqlx::query_scalar(
        "SELECT block_mail FROM mail_blocks WHERE owner_id = $1 AND blocked_id = $2",
    )
    .bind(owner_id)
    .bind(blocked_id)
    .fetch_optional(pool)
    .await?;

    match current {
        Some(true) => {
            // Unblock.
            sqlx::query(
                "UPDATE mail_blocks SET block_mail = FALSE WHERE owner_id = $1 AND blocked_id = $2",
            )
            .bind(owner_id)
            .bind(blocked_id)
            .execute(pool)
            .await?;
            Ok(false)
        }
        Some(false) => {
            // Re-block.
            sqlx::query(
                "UPDATE mail_blocks SET block_mail = TRUE WHERE owner_id = $1 AND blocked_id = $2",
            )
            .bind(owner_id)
            .bind(blocked_id)
            .execute(pool)
            .await?;
            Ok(true)
        }
        None => {
            // Insert new block.
            sqlx::query(
                "INSERT INTO mail_blocks (owner_id, blocked_id, block_mail, block_chat)
                 VALUES ($1, $2, TRUE, FALSE)",
            )
            .bind(owner_id)
            .bind(blocked_id)
            .execute(pool)
            .await?;
            Ok(true)
        }
    }
}

/// Check if a player exists and return their name.
pub async fn player_exists(pool: &PgPool, player_id: i64) -> Result<Option<String>, sqlx::Error> {
    sqlx::query_scalar::<_, String>("SELECT user_name FROM players WHERE id = $1")
        .bind(player_id)
        .fetch_optional(pool)
        .await
}

/// Search messages by text (subject or body).
pub async fn search_messages(
    pool: &PgPool,
    owner_id: i64,
    query: &str,
    limit: i64,
    offset: i64,
) -> Result<Vec<MailThreadRow>, sqlx::Error> {
    let pattern = format!("%{query}%");
    sqlx::query_as::<_, MailThreadRow>(
        "SELECT DISTINCT ON (topic_id)
            id, sender_id, sender_name, recipient_id, recipient_name,
            topic_id, subject, is_read
         FROM mail_messages
         WHERE owner_id = $1 AND (subject ILIKE $2 OR body ILIKE $2)
         ORDER BY topic_id DESC, id DESC
         LIMIT $3 OFFSET $4",
    )
    .bind(owner_id)
    .bind(&pattern)
    .bind(limit)
    .bind(offset)
    .fetch_all(pool)
    .await
}

/// Count matching search results.
pub async fn count_search_results(
    pool: &PgPool,
    owner_id: i64,
    query: &str,
) -> Result<i64, sqlx::Error> {
    let pattern = format!("%{query}%");
    sqlx::query_scalar::<_, i64>(
        "SELECT count(DISTINCT topic_id) FROM mail_messages
         WHERE owner_id = $1 AND (subject ILIKE $2 OR body ILIKE $2)",
    )
    .bind(owner_id)
    .bind(&pattern)
    .fetch_one(pool)
    .await
}

/// List staff/admin players for the "forward to staff" feature.
pub async fn list_staff(pool: &PgPool) -> Result<Vec<PlayerNameRow>, sqlx::Error> {
    sqlx::query_as::<_, PlayerNameRow>(
        "SELECT id, user_name FROM players WHERE rank IN ('Admin', 'Staff') ORDER BY id ASC",
    )
    .fetch_all(pool)
    .await
}
