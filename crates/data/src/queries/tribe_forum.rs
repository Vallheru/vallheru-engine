//! Tribe forum SQL queries: topics, replies, permission checks.

use sqlx::PgPool;

// =========================================================================
// Row types
// =========================================================================

/// A tribe topic row.
pub struct TribeTopicRow {
    pub id: i32,
    pub topic: String,
    pub body: String,
    pub starter: String,
    pub tribe: i32,
    pub w_time: i64,
    pub sticky: String,
    pub pid: i32,
}

/// A tribe reply row.
pub struct TribeReplyRow {
    pub id: i32,
    pub starter: String,
    pub topic_id: i32,
    pub body: String,
    pub pid: i32,
}

/// Tribe tags (prefix/suffix for author labels).
pub struct TribeTags {
    pub owner: i32,
    pub prefix: String,
    pub suffix: String,
}

// =========================================================================
// Permission queries
// =========================================================================

/// Check whether a player has the `forum` permission for their tribe.
///
/// Returns `true` if the player is the tribe owner OR has `forum = 1` in
/// `tribe_perm`.
pub async fn has_forum_permission(
    pool: &PgPool,
    player_id: i64,
    tribe_id: i32,
) -> Result<bool, sqlx::Error> {
    let row = sqlx::query_as::<_, (i32,)>("SELECT owner FROM tribes WHERE id = $1")
        .bind(tribe_id)
        .fetch_optional(pool)
        .await?;
    let Some((owner,)) = row else {
        return Ok(false);
    };
    if i64::from(owner) == player_id {
        return Ok(true);
    }
    #[allow(clippy::cast_possible_truncation)]
    let pid32 = player_id as i32;
    let perm = sqlx::query_scalar::<_, i16>(
        "SELECT forum FROM tribe_perm WHERE tribe = $1 AND player = $2",
    )
    .bind(tribe_id)
    .bind(pid32)
    .fetch_optional(pool)
    .await?;
    Ok(perm.unwrap_or(0) != 0)
}

/// Fetch tribe tags (prefix, suffix, owner) for a tribe.
pub async fn tribe_tags(pool: &PgPool, tribe_id: i32) -> Result<Option<TribeTags>, sqlx::Error> {
    let row = sqlx::query_as::<_, (i32, String, String)>(
        "SELECT owner, prefix, suffix FROM tribes WHERE id = $1",
    )
    .bind(tribe_id)
    .fetch_optional(pool)
    .await?;
    Ok(row.map(|(owner, prefix, suffix)| TribeTags {
        owner,
        prefix,
        suffix,
    }))
}

// =========================================================================
// Topic queries
// =========================================================================

/// Count non-sticky topics for a tribe.
pub async fn count_topics(pool: &PgPool, tribe_id: i32) -> Result<i64, sqlx::Error> {
    sqlx::query_scalar::<_, i64>(
        "SELECT count(*) FROM tribe_topics WHERE tribe = $1 AND sticky = 'N'",
    )
    .bind(tribe_id)
    .fetch_one(pool)
    .await
}

/// Count topics modified after a given timestamp (for "new posts" view).
pub async fn count_new_topics(
    pool: &PgPool,
    tribe_id: i32,
    since: i64,
) -> Result<i64, sqlx::Error> {
    sqlx::query_scalar::<_, i64>(
        "SELECT count(*) FROM tribe_topics WHERE tribe = $1 AND w_time > $2",
    )
    .bind(tribe_id)
    .bind(since)
    .fetch_one(pool)
    .await
}

/// Fetch sticky topics for a tribe.
pub async fn list_sticky_topics(
    pool: &PgPool,
    tribe_id: i32,
) -> Result<Vec<TribeTopicRow>, sqlx::Error> {
    let rows = sqlx::query_as::<_, (i32, String, String, String, i32, i64, String, i32)>(
        "SELECT id, topic, body, starter, tribe, w_time, sticky, pid \
         FROM tribe_topics WHERE tribe = $1 AND sticky = 'Y' ORDER BY id ASC",
    )
    .bind(tribe_id)
    .fetch_all(pool)
    .await?;
    Ok(rows
        .into_iter()
        .map(
            |(id, topic, body, starter, tribe, w_time, sticky, pid)| TribeTopicRow {
                id,
                topic,
                body,
                starter,
                tribe,
                w_time,
                sticky,
                pid,
            },
        )
        .collect())
}

/// Fetch non-sticky topics for a tribe, paginated.
pub async fn list_topics(
    pool: &PgPool,
    tribe_id: i32,
    limit: i64,
    offset: i64,
) -> Result<Vec<TribeTopicRow>, sqlx::Error> {
    let rows = sqlx::query_as::<_, (i32, String, String, String, i32, i64, String, i32)>(
        "SELECT id, topic, body, starter, tribe, w_time, sticky, pid \
         FROM tribe_topics WHERE tribe = $1 AND sticky = 'N' \
         ORDER BY w_time DESC LIMIT $2 OFFSET $3",
    )
    .bind(tribe_id)
    .bind(limit)
    .bind(offset)
    .fetch_all(pool)
    .await?;
    Ok(rows
        .into_iter()
        .map(
            |(id, topic, body, starter, tribe, w_time, sticky, pid)| TribeTopicRow {
                id,
                topic,
                body,
                starter,
                tribe,
                w_time,
                sticky,
                pid,
            },
        )
        .collect())
}

/// Fetch new (unread) topics for a tribe, paginated.
pub async fn list_new_topics(
    pool: &PgPool,
    tribe_id: i32,
    since: i64,
    limit: i64,
    offset: i64,
) -> Result<Vec<TribeTopicRow>, sqlx::Error> {
    let rows = sqlx::query_as::<_, (i32, String, String, String, i32, i64, String, i32)>(
        "SELECT id, topic, body, starter, tribe, w_time, sticky, pid \
         FROM tribe_topics WHERE tribe = $1 AND w_time > $2 \
         ORDER BY id ASC LIMIT $3 OFFSET $4",
    )
    .bind(tribe_id)
    .bind(since)
    .bind(limit)
    .bind(offset)
    .fetch_all(pool)
    .await?;
    Ok(rows
        .into_iter()
        .map(
            |(id, topic, body, starter, tribe, w_time, sticky, pid)| TribeTopicRow {
                id,
                topic,
                body,
                starter,
                tribe,
                w_time,
                sticky,
                pid,
            },
        )
        .collect())
}

/// Fetch a single topic by ID, scoped to a tribe.
pub async fn find_topic(
    pool: &PgPool,
    topic_id: i32,
    tribe_id: i32,
) -> Result<Option<TribeTopicRow>, sqlx::Error> {
    let row = sqlx::query_as::<_, (i32, String, String, String, i32, i64, String, i32)>(
        "SELECT id, topic, body, starter, tribe, w_time, sticky, pid \
         FROM tribe_topics WHERE id = $1 AND tribe = $2",
    )
    .bind(topic_id)
    .bind(tribe_id)
    .fetch_optional(pool)
    .await?;
    Ok(row.map(
        |(id, topic, body, starter, tribe, w_time, sticky, pid)| TribeTopicRow {
            id,
            topic,
            body,
            starter,
            tribe,
            w_time,
            sticky,
            pid,
        },
    ))
}

/// Reply count for a topic.
pub async fn reply_count(pool: &PgPool, topic_id: i32) -> Result<i64, sqlx::Error> {
    sqlx::query_scalar::<_, i64>("SELECT count(*) FROM tribe_replies WHERE topic_id = $1")
        .bind(topic_id)
        .fetch_one(pool)
        .await
}

/// Data needed to create a new tribe topic.
pub struct NewTopic<'a> {
    pub tribe_id: i32,
    pub title: &'a str,
    pub body: &'a str,
    pub starter: &'a str,
    pub pid: i32,
    pub w_time: i64,
    pub sticky: &'a str,
}

/// Insert a new topic.
pub async fn insert_topic(pool: &PgPool, t: &NewTopic<'_>) -> Result<i32, sqlx::Error> {
    sqlx::query_scalar::<_, i32>(
        "INSERT INTO tribe_topics (topic, body, starter, tribe, w_time, sticky, pid) \
         VALUES ($1, $2, $3, $4, $5, $6, $7) RETURNING id",
    )
    .bind(t.title)
    .bind(t.body)
    .bind(t.starter)
    .bind(t.tribe_id)
    .bind(t.w_time)
    .bind(t.sticky)
    .bind(t.pid)
    .fetch_one(pool)
    .await
}

/// Delete a topic and its replies.
pub async fn delete_topic(pool: &PgPool, topic_id: i32) -> Result<(), sqlx::Error> {
    sqlx::query("DELETE FROM tribe_replies WHERE topic_id = $1")
        .bind(topic_id)
        .execute(pool)
        .await?;
    sqlx::query("DELETE FROM tribe_topics WHERE id = $1")
        .bind(topic_id)
        .execute(pool)
        .await?;
    Ok(())
}

/// Bulk-delete topics by IDs within a tribe.
pub async fn delete_topics_bulk(
    pool: &PgPool,
    tribe_id: i32,
    topic_ids: &[i32],
) -> Result<(), sqlx::Error> {
    // Delete replies for all selected topics
    sqlx::query(
        "DELETE FROM tribe_replies WHERE topic_id = ANY( \
         SELECT id FROM tribe_topics WHERE tribe = $1 AND id = ANY($2))",
    )
    .bind(tribe_id)
    .bind(topic_ids)
    .execute(pool)
    .await?;
    sqlx::query("DELETE FROM tribe_topics WHERE tribe = $1 AND id = ANY($2)")
        .bind(tribe_id)
        .bind(topic_ids)
        .execute(pool)
        .await?;
    Ok(())
}

/// Toggle sticky flag on a topic.
pub async fn set_sticky(pool: &PgPool, topic_id: i32, sticky: &str) -> Result<(), sqlx::Error> {
    sqlx::query("UPDATE tribe_topics SET sticky = $2 WHERE id = $1")
        .bind(topic_id)
        .bind(sticky)
        .execute(pool)
        .await?;
    Ok(())
}

// =========================================================================
// Reply queries
// =========================================================================

/// Fetch replies for a topic, paginated.
pub async fn list_replies(
    pool: &PgPool,
    topic_id: i32,
    limit: i64,
    offset: i64,
) -> Result<Vec<TribeReplyRow>, sqlx::Error> {
    let rows = sqlx::query_as::<_, (i32, String, i32, String, i32)>(
        "SELECT id, starter, topic_id, body, pid \
         FROM tribe_replies WHERE topic_id = $1 \
         ORDER BY id ASC LIMIT $2 OFFSET $3",
    )
    .bind(topic_id)
    .bind(limit)
    .bind(offset)
    .fetch_all(pool)
    .await?;
    Ok(rows
        .into_iter()
        .map(|(id, starter, topic_id, body, pid)| TribeReplyRow {
            id,
            starter,
            topic_id,
            body,
            pid,
        })
        .collect())
}

/// Fetch a single reply by ID.
pub async fn find_reply(
    pool: &PgPool,
    reply_id: i32,
) -> Result<Option<TribeReplyRow>, sqlx::Error> {
    let row = sqlx::query_as::<_, (i32, String, i32, String, i32)>(
        "SELECT id, starter, topic_id, body, pid FROM tribe_replies WHERE id = $1",
    )
    .bind(reply_id)
    .fetch_optional(pool)
    .await?;
    Ok(row.map(|(id, starter, topic_id, body, pid)| TribeReplyRow {
        id,
        starter,
        topic_id,
        body,
        pid,
    }))
}

/// Insert a reply.
pub async fn insert_reply(
    pool: &PgPool,
    topic_id: i32,
    starter: &str,
    body: &str,
    pid: i32,
    w_time: i64,
) -> Result<(), sqlx::Error> {
    sqlx::query("INSERT INTO tribe_replies (starter, topic_id, body, pid) VALUES ($1, $2, $3, $4)")
        .bind(starter)
        .bind(topic_id)
        .bind(body)
        .bind(pid)
        .execute(pool)
        .await?;
    // Update topic's w_time to bump it in listings.
    sqlx::query("UPDATE tribe_topics SET w_time = $2 WHERE id = $1")
        .bind(topic_id)
        .bind(w_time)
        .execute(pool)
        .await?;
    Ok(())
}

/// Delete a reply by ID (must belong to a topic in the player's tribe).
pub async fn delete_reply(
    pool: &PgPool,
    reply_id: i32,
    tribe_id: i32,
) -> Result<bool, sqlx::Error> {
    let topic_row = sqlx::query_as::<_, (i32,)>("SELECT topic_id FROM tribe_replies WHERE id = $1")
        .bind(reply_id)
        .fetch_optional(pool)
        .await?;
    let Some((topic_id,)) = topic_row else {
        return Ok(false);
    };
    // Verify the topic belongs to this tribe.
    let belongs = sqlx::query_scalar::<_, i64>(
        "SELECT count(*) FROM tribe_topics WHERE id = $1 AND tribe = $2",
    )
    .bind(topic_id)
    .bind(tribe_id)
    .fetch_one(pool)
    .await?;
    if belongs == 0 {
        return Ok(false);
    }
    sqlx::query("DELETE FROM tribe_replies WHERE id = $1")
        .bind(reply_id)
        .execute(pool)
        .await?;
    Ok(true)
}

/// Fetch reply body for quoting.
pub async fn reply_body(pool: &PgPool, reply_id: i32) -> Result<Option<String>, sqlx::Error> {
    sqlx::query_scalar::<_, String>("SELECT body FROM tribe_replies WHERE id = $1")
        .bind(reply_id)
        .fetch_optional(pool)
        .await
}

// =========================================================================
// Player helpers
// =========================================================================

/// Update a player's `tforum_time` to the given timestamp.
pub async fn update_tforum_time(
    pool: &PgPool,
    player_id: i64,
    now: i64,
) -> Result<(), sqlx::Error> {
    sqlx::query("UPDATE players SET tforum_time = $2 WHERE id = $1")
        .bind(player_id)
        .bind(now)
        .execute(pool)
        .await?;
    Ok(())
}

// =========================================================================
// Search
// =========================================================================

/// Search topics and replies for a text substring within a tribe.
/// Returns matching topic IDs.
pub async fn search_topics(
    pool: &PgPool,
    tribe_id: i32,
    query: &str,
) -> Result<Vec<(i32, String)>, sqlx::Error> {
    let pattern = format!("%{query}%");
    // Search in topic titles and bodies.
    let topic_hits = sqlx::query_as::<_, (i32, String)>(
        "SELECT id, topic FROM tribe_topics WHERE tribe = $1 AND (topic ILIKE $2 OR body ILIKE $2)",
    )
    .bind(tribe_id)
    .bind(&pattern)
    .fetch_all(pool)
    .await?;

    let mut result_ids: std::collections::HashSet<i32> =
        topic_hits.iter().map(|(id, _)| *id).collect();
    let mut results: Vec<(i32, String)> = topic_hits;

    // Search in replies and add their parent topics.
    let reply_hits = sqlx::query_as::<_, (i32,)>(
        "SELECT DISTINCT tr.topic_id FROM tribe_replies tr \
         JOIN tribe_topics tt ON tt.id = tr.topic_id \
         WHERE tt.tribe = $1 AND tr.body ILIKE $2",
    )
    .bind(tribe_id)
    .bind(&pattern)
    .fetch_all(pool)
    .await?;

    for (tid,) in reply_hits {
        if result_ids.insert(tid) {
            // Fetch topic title for this newly found ID.
            if let Some(title) =
                sqlx::query_scalar::<_, String>("SELECT topic FROM tribe_topics WHERE id = $1")
                    .bind(tid)
                    .fetch_optional(pool)
                    .await?
            {
                results.push((tid, title));
            }
        }
    }

    Ok(results)
}
