//! Forum SQL queries: categories, topics, replies, bans, search.

use sqlx::PgPool;

// =========================================================================
// Row types
// =========================================================================

/// A forum category row.
#[derive(sqlx::FromRow, Debug)]
pub struct CategoryRow {
    pub id: i64,
    pub name: String,
    pub description: String,
    pub perm_visit: String,
    pub perm_write: String,
    pub perm_topic: String,
    pub topic_count: i64,
}

/// A topic row for listing.
#[derive(sqlx::FromRow, Debug)]
pub struct TopicListRow {
    pub id: i64,
    pub title: String,
    pub author_name: String,
    pub author_id: i64,
    pub is_sticky: bool,
    pub is_closed: bool,
    pub reply_count: i32,
    pub last_post_at_epoch: i64,
}

/// A full topic row for reading.
#[derive(sqlx::FromRow, Debug)]
pub struct TopicDetailRow {
    pub id: i64,
    pub category_id: i64,
    pub title: String,
    pub body: String,
    pub author_name: String,
    pub author_id: i64,
    pub is_sticky: bool,
    pub is_closed: bool,
    pub reply_count: i32,
    pub created_at_formatted: String,
}

/// A reply row.
#[derive(sqlx::FromRow, Debug)]
pub struct ReplyRow {
    pub id: i64,
    pub author_name: String,
    pub author_id: i64,
    pub body: String,
    pub created_at_formatted: String,
}

/// A minimal category row for move dropdown.
#[derive(sqlx::FromRow, Debug)]
pub struct CategoryNameRow {
    pub id: i64,
    pub name: String,
}

// =========================================================================
// Category queries
// =========================================================================

/// List categories the player can see, with topic counts.
pub async fn list_categories(pool: &PgPool) -> Result<Vec<CategoryRow>, sqlx::Error> {
    sqlx::query_as::<_, CategoryRow>(
        "SELECT c.id, c.name, c.description,
                c.perm_visit, c.perm_write, c.perm_topic,
                (SELECT count(*) FROM forum_topics t WHERE t.category_id = c.id) AS topic_count
         FROM forum_categories c
         ORDER BY c.sort_order ASC, c.id ASC",
    )
    .fetch_all(pool)
    .await
}

/// List all category names for a move dropdown.
pub async fn list_category_names(pool: &PgPool) -> Result<Vec<CategoryNameRow>, sqlx::Error> {
    sqlx::query_as::<_, CategoryNameRow>(
        "SELECT id, name FROM forum_categories ORDER BY sort_order ASC, id ASC",
    )
    .fetch_all(pool)
    .await
}

/// Get a single category's permission fields.
#[derive(sqlx::FromRow, Debug)]
pub struct CategoryPermRow {
    pub perm_visit: String,
    pub perm_write: String,
    pub perm_topic: String,
}

pub async fn get_category_perms(
    pool: &PgPool,
    category_id: i64,
) -> Result<Option<CategoryPermRow>, sqlx::Error> {
    sqlx::query_as::<_, CategoryPermRow>(
        "SELECT perm_visit, perm_write, perm_topic FROM forum_categories WHERE id = $1",
    )
    .bind(category_id)
    .fetch_optional(pool)
    .await
}

// =========================================================================
// Topic queries
// =========================================================================

/// Count non-sticky topics in a category.
pub async fn count_topics(pool: &PgPool, category_id: i64) -> Result<i64, sqlx::Error> {
    sqlx::query_scalar::<_, i64>(
        "SELECT count(*) FROM forum_topics WHERE category_id = $1 AND NOT is_sticky",
    )
    .bind(category_id)
    .fetch_one(pool)
    .await
}

/// List sticky topics in a category.
pub async fn list_sticky_topics(
    pool: &PgPool,
    category_id: i64,
) -> Result<Vec<TopicListRow>, sqlx::Error> {
    sqlx::query_as::<_, TopicListRow>(
        "SELECT id, title, author_name, author_id, is_sticky, is_closed,
                reply_count, extract(epoch from last_post_at)::bigint AS last_post_at_epoch
         FROM forum_topics
         WHERE category_id = $1 AND is_sticky
         ORDER BY id ASC",
    )
    .bind(category_id)
    .fetch_all(pool)
    .await
}

/// List non-sticky topics in a category with dynamic sorting.
///
/// The `order_clause` is one of the validated constants from `TopicSort::order_clause()`.
pub async fn list_topics(
    pool: &PgPool,
    category_id: i64,
    order_clause: &str,
    limit: i64,
    offset: i64,
) -> Result<Vec<TopicListRow>, sqlx::Error> {
    // We build the query string with a validated order clause.
    let sql = format!(
        "SELECT id, title, author_name, author_id, is_sticky, is_closed,
                reply_count, extract(epoch from last_post_at)::bigint AS last_post_at_epoch
         FROM forum_topics
         WHERE category_id = $1 AND NOT is_sticky
         ORDER BY {order_clause}
         LIMIT $2 OFFSET $3"
    );
    sqlx::query_as::<_, TopicListRow>(&sql)
        .bind(category_id)
        .bind(limit)
        .bind(offset)
        .fetch_all(pool)
        .await
}

/// Get a topic by ID.
pub async fn get_topic(
    pool: &PgPool,
    topic_id: i64,
) -> Result<Option<TopicDetailRow>, sqlx::Error> {
    sqlx::query_as::<_, TopicDetailRow>(
        "SELECT id, category_id, title, body, author_name, author_id,
                is_sticky, is_closed, reply_count,
                to_char(created_at, 'DD-MM-YYYY HH24:MI') AS created_at_formatted
         FROM forum_topics
         WHERE id = $1",
    )
    .bind(topic_id)
    .fetch_optional(pool)
    .await
}

/// Count replies for a topic.
pub async fn count_replies(pool: &PgPool, topic_id: i64) -> Result<i64, sqlx::Error> {
    sqlx::query_scalar::<_, i64>("SELECT count(*) FROM forum_replies WHERE topic_id = $1")
        .bind(topic_id)
        .fetch_one(pool)
        .await
}

/// List replies for a topic, paginated.
pub async fn list_replies(
    pool: &PgPool,
    topic_id: i64,
    limit: i64,
    offset: i64,
) -> Result<Vec<ReplyRow>, sqlx::Error> {
    sqlx::query_as::<_, ReplyRow>(
        "SELECT id, author_name, author_id, body,
                to_char(created_at, 'DD-MM-YYYY HH24:MI') AS created_at_formatted
         FROM forum_replies
         WHERE topic_id = $1
         ORDER BY id ASC
         LIMIT $2 OFFSET $3",
    )
    .bind(topic_id)
    .bind(limit)
    .bind(offset)
    .fetch_all(pool)
    .await
}

/// Get the previous topic ID in the same category.
pub async fn prev_topic_id(
    pool: &PgPool,
    topic_id: i64,
    category_id: i64,
) -> Result<Option<i64>, sqlx::Error> {
    sqlx::query_scalar::<_, i64>(
        "SELECT id FROM forum_topics WHERE id < $1 AND category_id = $2 ORDER BY id DESC LIMIT 1",
    )
    .bind(topic_id)
    .bind(category_id)
    .fetch_optional(pool)
    .await
}

/// Get the next topic ID in the same category.
pub async fn next_topic_id(
    pool: &PgPool,
    topic_id: i64,
    category_id: i64,
) -> Result<Option<i64>, sqlx::Error> {
    sqlx::query_scalar::<_, i64>(
        "SELECT id FROM forum_topics WHERE id > $1 AND category_id = $2 ORDER BY id ASC LIMIT 1",
    )
    .bind(topic_id)
    .bind(category_id)
    .fetch_optional(pool)
    .await
}

// =========================================================================
// Write operations
// =========================================================================

/// Insert a new topic, returning its ID.
pub async fn insert_topic(pool: &PgPool, p: &InsertTopicParams<'_>) -> Result<i64, sqlx::Error> {
    sqlx::query_scalar::<_, i64>(
        "INSERT INTO forum_topics (category_id, title, body, author_name, author_id, is_sticky)
         VALUES ($1, $2, $3, $4, $5, $6)
         RETURNING id",
    )
    .bind(p.category_id)
    .bind(p.title)
    .bind(p.body)
    .bind(p.author_name)
    .bind(p.author_id)
    .bind(p.is_sticky)
    .fetch_one(pool)
    .await
}

/// Parameters for inserting a topic.
pub struct InsertTopicParams<'a> {
    pub category_id: i64,
    pub title: &'a str,
    pub body: &'a str,
    pub author_name: &'a str,
    pub author_id: i64,
    pub is_sticky: bool,
}

/// Insert a reply, incrementing the topic reply count and updating `last_post_at`.
pub async fn insert_reply(
    pool: &PgPool,
    topic_id: i64,
    author_name: &str,
    author_id: i64,
    body: &str,
) -> Result<i64, sqlx::Error> {
    let id = sqlx::query_scalar::<_, i64>(
        "INSERT INTO forum_replies (topic_id, author_name, author_id, body)
         VALUES ($1, $2, $3, $4)
         RETURNING id",
    )
    .bind(topic_id)
    .bind(author_name)
    .bind(author_id)
    .bind(body)
    .fetch_one(pool)
    .await?;

    sqlx::query(
        "UPDATE forum_topics SET reply_count = reply_count + 1, last_post_at = now() WHERE id = $1",
    )
    .bind(topic_id)
    .execute(pool)
    .await?;

    Ok(id)
}

/// Delete a single topic and all its replies.
pub async fn delete_topic(pool: &PgPool, topic_id: i64) -> Result<(), sqlx::Error> {
    // Replies cascade-deleted via FK.
    sqlx::query("DELETE FROM forum_topics WHERE id = $1")
        .bind(topic_id)
        .execute(pool)
        .await?;
    Ok(())
}

/// Delete multiple topics by IDs within a category.
pub async fn delete_topics(
    pool: &PgPool,
    category_id: i64,
    topic_ids: &[i64],
) -> Result<(), sqlx::Error> {
    sqlx::query("DELETE FROM forum_topics WHERE category_id = $1 AND id = ANY($2)")
        .bind(category_id)
        .bind(topic_ids)
        .execute(pool)
        .await?;
    Ok(())
}

/// Delete a single reply, decrementing reply count.
pub async fn delete_reply(pool: &PgPool, reply_id: i64) -> Result<(), sqlx::Error> {
    let topic_id =
        sqlx::query_scalar::<_, i64>("DELETE FROM forum_replies WHERE id = $1 RETURNING topic_id")
            .bind(reply_id)
            .fetch_optional(pool)
            .await?;

    if let Some(tid) = topic_id {
        sqlx::query(
            "UPDATE forum_topics SET reply_count = GREATEST(reply_count - 1, 0) WHERE id = $1",
        )
        .bind(tid)
        .execute(pool)
        .await?;
    }
    Ok(())
}

/// Delete multiple replies by IDs within a topic.
pub async fn delete_replies(
    pool: &PgPool,
    topic_id: i64,
    reply_ids: &[i64],
) -> Result<u64, sqlx::Error> {
    let result = sqlx::query("DELETE FROM forum_replies WHERE topic_id = $1 AND id = ANY($2)")
        .bind(topic_id)
        .bind(reply_ids)
        .execute(pool)
        .await?;
    let deleted = result.rows_affected();
    if deleted > 0 {
        #[allow(clippy::cast_possible_truncation)]
        let count = deleted as i32;
        sqlx::query(
            "UPDATE forum_topics SET reply_count = GREATEST(reply_count - $2::int, 0) WHERE id = $1",
        )
        .bind(topic_id)
        .bind(count)
        .execute(pool)
        .await?;
    }
    Ok(deleted)
}

/// Toggle a topic's closed state.
pub async fn set_topic_closed(
    pool: &PgPool,
    topic_id: i64,
    closed: bool,
) -> Result<(), sqlx::Error> {
    sqlx::query("UPDATE forum_topics SET is_closed = $2 WHERE id = $1")
        .bind(topic_id)
        .bind(closed)
        .execute(pool)
        .await?;
    Ok(())
}

/// Toggle a topic's sticky state.
pub async fn set_topic_sticky(
    pool: &PgPool,
    topic_id: i64,
    sticky: bool,
) -> Result<(), sqlx::Error> {
    sqlx::query("UPDATE forum_topics SET is_sticky = $2 WHERE id = $1")
        .bind(topic_id)
        .bind(sticky)
        .execute(pool)
        .await?;
    Ok(())
}

/// Move a topic to a different category.
pub async fn move_topic(
    pool: &PgPool,
    topic_id: i64,
    new_category_id: i64,
) -> Result<(), sqlx::Error> {
    sqlx::query("UPDATE forum_topics SET category_id = $2 WHERE id = $1")
        .bind(topic_id)
        .bind(new_category_id)
        .execute(pool)
        .await?;
    Ok(())
}

// =========================================================================
// Forum bans
// =========================================================================

/// Check if a player is banned from posting.
pub async fn is_forum_banned(pool: &PgPool, player_id: i64) -> Result<bool, sqlx::Error> {
    let count =
        sqlx::query_scalar::<_, i64>("SELECT count(*) FROM forum_bans WHERE player_id = $1")
            .bind(player_id)
            .fetch_one(pool)
            .await?;
    Ok(count > 0)
}

// =========================================================================
// Unread tracking
// =========================================================================

/// Count unread topics since a given epoch timestamp, within accessible categories.
pub async fn count_unread(
    pool: &PgPool,
    since_epoch: i64,
    category_ids: &[i64],
) -> Result<i64, sqlx::Error> {
    if category_ids.is_empty() {
        return Ok(0);
    }
    sqlx::query_scalar::<_, i64>(
        "SELECT count(*) FROM forum_topics
         WHERE category_id = ANY($1)
           AND extract(epoch from last_post_at)::bigint > $2",
    )
    .bind(category_ids)
    .bind(since_epoch)
    .fetch_one(pool)
    .await
}

/// A topic row enriched with its category name, for the "new posts" view.
#[derive(sqlx::FromRow, Debug)]
pub struct UnreadTopicRow {
    pub id: i64,
    pub title: String,
    pub category_name: String,
}

/// List new (unread) topics since a given epoch, paginated.
pub async fn list_unread_topics(
    pool: &PgPool,
    since_epoch: i64,
    category_ids: &[i64],
    limit: i64,
    offset: i64,
) -> Result<Vec<UnreadTopicRow>, sqlx::Error> {
    if category_ids.is_empty() {
        return Ok(vec![]);
    }
    sqlx::query_as::<_, UnreadTopicRow>(
        "SELECT t.id, t.title, c.name AS category_name
         FROM forum_topics t
         JOIN forum_categories c ON c.id = t.category_id
         WHERE t.category_id = ANY($1)
           AND extract(epoch from t.last_post_at)::bigint > $2
         ORDER BY t.last_post_at DESC
         LIMIT $3 OFFSET $4",
    )
    .bind(category_ids)
    .bind(since_epoch)
    .bind(limit)
    .bind(offset)
    .fetch_all(pool)
    .await
}

/// Get a reply's body for quoting.
pub async fn get_reply_body(pool: &PgPool, reply_id: i64) -> Result<Option<String>, sqlx::Error> {
    sqlx::query_scalar::<_, String>("SELECT body FROM forum_replies WHERE id = $1")
        .bind(reply_id)
        .fetch_optional(pool)
        .await
}

// =========================================================================
// Search
// =========================================================================

/// Search topics and replies within a category for a keyword.
/// Returns distinct topic IDs that match.
pub async fn search_topics(
    pool: &PgPool,
    category_id: i64,
    keyword: &str,
) -> Result<Vec<TopicListRow>, sqlx::Error> {
    let pattern = format!("%{keyword}%");
    sqlx::query_as::<_, TopicListRow>(
        "SELECT DISTINCT t.id, t.title, t.author_name, t.author_id, t.is_sticky, t.is_closed,
                t.reply_count, extract(epoch from t.last_post_at)::bigint AS last_post_at_epoch
         FROM forum_topics t
         LEFT JOIN forum_replies r ON r.topic_id = t.id
         WHERE t.category_id = $1
           AND (t.title ILIKE $2 OR t.body ILIKE $2 OR r.body ILIKE $2)
         ORDER BY t.id DESC",
    )
    .bind(category_id)
    .bind(pattern)
    .fetch_all(pool)
    .await
}

/// Update player's `forum_time` to the current time.
pub async fn update_forum_time(pool: &PgPool, player_id: i64) -> Result<(), sqlx::Error> {
    sqlx::query("UPDATE players SET forum_time = extract(epoch from now())::bigint WHERE id = $1")
        .bind(player_id)
        .execute(pool)
        .await?;
    Ok(())
}
