//! Data queries for content publishing: news, updates, newspaper, polls, proposals, comments.

use sqlx::PgPool;

// =========================================================================
// Row types
// =========================================================================

#[derive(Debug, sqlx::FromRow, serde::Serialize)]
pub struct UpdateRow {
    pub id: i64,
    pub title: String,
    pub body: String,
    pub author_name: String,
    pub published_at_formatted: String,
}

#[derive(Debug, sqlx::FromRow, serde::Serialize)]
pub struct NewsRow {
    pub id: i64,
    pub title: String,
    pub body: String,
    pub author_name: String,
    pub status: String,
    pub published_at_formatted: String,
}

#[derive(Debug, sqlx::FromRow, serde::Serialize)]
pub struct ArticleRow {
    pub id: i64,
    pub issue_id: i64,
    pub title: String,
    pub body: String,
    pub author_name: String,
    pub article_type: String,
    pub is_published: bool,
}

#[derive(Debug, sqlx::FromRow, serde::Serialize)]
pub struct ArticleSummaryRow {
    pub id: i64,
    pub title: String,
    pub author_name: String,
    pub article_type: String,
}

#[derive(Debug, sqlx::FromRow, serde::Serialize)]
pub struct PollRow {
    pub id: i64,
    pub question: String,
    pub description: String,
    pub days_left: i32,
    pub member_base: i32,
    pub is_active: bool,
}

#[derive(Debug, sqlx::FromRow, serde::Serialize)]
pub struct PollOptionRow {
    pub id: i64,
    pub label: String,
    pub votes: i32,
}

#[derive(Debug, sqlx::FromRow, serde::Serialize)]
pub struct ProposalRow {
    pub id: i64,
    pub player_id: i64,
    pub proposal_type: String,
    pub name: String,
    pub data: String,
    pub info: String,
}

#[derive(Debug, sqlx::FromRow, serde::Serialize)]
pub struct CommentRow {
    pub id: i64,
    pub author_name: String,
    pub author_id: i64,
    pub body: String,
    pub created_at_formatted: String,
}

// =========================================================================
// Game Updates
// =========================================================================

/// Get the latest update.
pub async fn get_latest_update(pool: &PgPool) -> Result<Option<UpdateRow>, sqlx::Error> {
    sqlx::query_as::<_, UpdateRow>(
        "SELECT id, title, body, author_name,
                to_char(published_at, 'YYYY-MM-DD') AS published_at_formatted
         FROM game_updates ORDER BY id DESC LIMIT 1",
    )
    .fetch_optional(pool)
    .await
}

/// Get the last N updates.
pub async fn list_updates(pool: &PgPool, limit: i64) -> Result<Vec<UpdateRow>, sqlx::Error> {
    sqlx::query_as::<_, UpdateRow>(
        "SELECT id, title, body, author_name,
                to_char(published_at, 'YYYY-MM-DD') AS published_at_formatted
         FROM game_updates ORDER BY id DESC LIMIT $1",
    )
    .bind(limit)
    .fetch_all(pool)
    .await
}

/// Insert a new update (admin only).
pub async fn insert_update(
    pool: &PgPool,
    title: &str,
    body: &str,
    author_name: &str,
) -> Result<i64, sqlx::Error> {
    sqlx::query_scalar::<_, i64>(
        "INSERT INTO game_updates (title, body, author_name) VALUES ($1, $2, $3) RETURNING id",
    )
    .bind(title)
    .bind(body)
    .bind(author_name)
    .fetch_one(pool)
    .await
}

/// Update an existing update.
pub async fn edit_update(
    pool: &PgPool,
    id: i64,
    title: &str,
    body: &str,
) -> Result<(), sqlx::Error> {
    sqlx::query("UPDATE game_updates SET title = $2, body = $3 WHERE id = $1")
        .bind(id)
        .bind(title)
        .bind(body)
        .execute(pool)
        .await?;
    Ok(())
}

// =========================================================================
// News (player gossip)
// =========================================================================

/// Get the latest approved/visible news item.
pub async fn get_latest_news(pool: &PgPool) -> Result<Option<NewsRow>, sqlx::Error> {
    sqlx::query_as::<_, NewsRow>(
        "SELECT id, title, body, author_name, status,
                to_char(published_at, 'YYYY-MM-DD') AS published_at_formatted
         FROM news WHERE status = 'approved' ORDER BY id DESC LIMIT 1",
    )
    .fetch_optional(pool)
    .await
}

/// List recent approved news.
pub async fn list_news(pool: &PgPool, limit: i64) -> Result<Vec<NewsRow>, sqlx::Error> {
    sqlx::query_as::<_, NewsRow>(
        "SELECT id, title, body, author_name, status,
                to_char(published_at, 'YYYY-MM-DD') AS published_at_formatted
         FROM news WHERE status = 'approved' ORDER BY id DESC LIMIT $1",
    )
    .bind(limit)
    .fetch_all(pool)
    .await
}

/// Count pending news items.
pub async fn count_pending_news(pool: &PgPool) -> Result<i64, sqlx::Error> {
    sqlx::query_scalar::<_, i64>("SELECT count(*) FROM news WHERE status = 'pending'")
        .fetch_one(pool)
        .await
}

/// Insert a news submission (pending by default).
pub async fn insert_news(
    pool: &PgPool,
    title: &str,
    body: &str,
    author_name: &str,
    author_id: i64,
) -> Result<i64, sqlx::Error> {
    sqlx::query_scalar::<_, i64>(
        "INSERT INTO news (title, body, author_name, author_id, status)
         VALUES ($1, $2, $3, $4, 'pending') RETURNING id",
    )
    .bind(title)
    .bind(body)
    .bind(author_name)
    .bind(author_id)
    .fetch_one(pool)
    .await
}

// =========================================================================
// Newspaper
// =========================================================================

/// Get the latest published issue ID.
pub async fn get_latest_issue_id(pool: &PgPool) -> Result<Option<i64>, sqlx::Error> {
    sqlx::query_scalar::<_, i64>(
        "SELECT issue_id FROM newspaper_articles WHERE is_published = true
         GROUP BY issue_id ORDER BY issue_id DESC LIMIT 1",
    )
    .fetch_optional(pool)
    .await
}

/// List articles for a specific issue by type.
pub async fn list_articles_by_issue(
    pool: &PgPool,
    issue_id: i64,
) -> Result<Vec<ArticleSummaryRow>, sqlx::Error> {
    sqlx::query_as::<_, ArticleSummaryRow>(
        "SELECT id, title, author_name, article_type
         FROM newspaper_articles
         WHERE issue_id = $1 AND is_published = true
         ORDER BY article_type, id",
    )
    .bind(issue_id)
    .fetch_all(pool)
    .await
}

/// List unpublished articles (for redaction).
pub async fn list_unpublished_articles(
    pool: &PgPool,
) -> Result<Vec<ArticleSummaryRow>, sqlx::Error> {
    sqlx::query_as::<_, ArticleSummaryRow>(
        "SELECT id, title, author_name, article_type
         FROM newspaper_articles
         WHERE is_published = false
         ORDER BY article_type, id",
    )
    .fetch_all(pool)
    .await
}

/// Get a single article by ID.
pub async fn get_article(pool: &PgPool, id: i64) -> Result<Option<ArticleRow>, sqlx::Error> {
    sqlx::query_as::<_, ArticleRow>(
        "SELECT id, issue_id, title, body, author_name, article_type, is_published
         FROM newspaper_articles WHERE id = $1",
    )
    .bind(id)
    .fetch_optional(pool)
    .await
}

/// Insert a newspaper article.
pub async fn insert_article(
    pool: &PgPool,
    issue_id: i64,
    title: &str,
    body: &str,
    author_name: &str,
    article_type: &str,
) -> Result<i64, sqlx::Error> {
    sqlx::query_scalar::<_, i64>(
        "INSERT INTO newspaper_articles (issue_id, title, body, author_name, article_type)
         VALUES ($1, $2, $3, $4, $5) RETURNING id",
    )
    .bind(issue_id)
    .bind(title)
    .bind(body)
    .bind(author_name)
    .bind(article_type)
    .fetch_one(pool)
    .await
}

/// Update an existing article.
pub async fn edit_article(
    pool: &PgPool,
    id: i64,
    title: &str,
    body: &str,
    article_type: &str,
) -> Result<(), sqlx::Error> {
    sqlx::query(
        "UPDATE newspaper_articles SET title = $2, body = $3, article_type = $4 WHERE id = $1",
    )
    .bind(id)
    .bind(title)
    .bind(body)
    .bind(article_type)
    .execute(pool)
    .await?;
    Ok(())
}

/// Delete an article.
pub async fn delete_article(pool: &PgPool, id: i64) -> Result<(), sqlx::Error> {
    sqlx::query("DELETE FROM newspaper_articles WHERE id = $1")
        .bind(id)
        .execute(pool)
        .await?;
    Ok(())
}

/// Publish all unpublished articles (release new issue).
pub async fn publish_pending_articles(pool: &PgPool) -> Result<(), sqlx::Error> {
    sqlx::query("UPDATE newspaper_articles SET is_published = true WHERE is_published = false")
        .execute(pool)
        .await?;
    Ok(())
}

/// List archived issue IDs (all published issues except the latest).
pub async fn list_archive_issue_ids(pool: &PgPool) -> Result<Vec<i64>, sqlx::Error> {
    sqlx::query_scalar::<_, i64>(
        "SELECT issue_id FROM newspaper_articles
         WHERE is_published = true
         GROUP BY issue_id
         ORDER BY issue_id DESC
         OFFSET 1",
    )
    .fetch_all(pool)
    .await
}

// =========================================================================
// Polls
// =========================================================================

/// Get the latest active poll.
pub async fn get_active_poll(pool: &PgPool) -> Result<Option<PollRow>, sqlx::Error> {
    sqlx::query_as::<_, PollRow>(
        "SELECT id, question, description, days_left, member_base, is_active
         FROM polls WHERE is_active = true ORDER BY id DESC LIMIT 1",
    )
    .fetch_optional(pool)
    .await
}

/// Get poll options for a poll.
pub async fn get_poll_options(
    pool: &PgPool,
    poll_id: i64,
) -> Result<Vec<PollOptionRow>, sqlx::Error> {
    sqlx::query_as::<_, PollOptionRow>(
        "SELECT id, label, votes FROM poll_options WHERE poll_id = $1 ORDER BY id",
    )
    .bind(poll_id)
    .fetch_all(pool)
    .await
}

/// Vote on a poll option.
pub async fn vote_poll(pool: &PgPool, option_id: i64) -> Result<(), sqlx::Error> {
    sqlx::query("UPDATE poll_options SET votes = votes + 1 WHERE id = $1")
        .bind(option_id)
        .execute(pool)
        .await?;
    Ok(())
}

/// List last N polls (by question row, votes = -1 marker not needed — we use `is_active`).
pub async fn list_recent_polls(pool: &PgPool, limit: i64) -> Result<Vec<PollRow>, sqlx::Error> {
    sqlx::query_as::<_, PollRow>(
        "SELECT id, question, description, days_left, member_base, is_active
         FROM polls ORDER BY id DESC LIMIT $1",
    )
    .bind(limit)
    .fetch_all(pool)
    .await
}

// =========================================================================
// Proposals
// =========================================================================

/// Insert a proposal.
pub async fn insert_proposal(
    pool: &PgPool,
    player_id: i64,
    proposal_type: &str,
    name: &str,
    data: &str,
    info: &str,
) -> Result<i64, sqlx::Error> {
    sqlx::query_scalar::<_, i64>(
        "INSERT INTO proposals (player_id, proposal_type, name, data, info)
         VALUES ($1, $2, $3, $4, $5) RETURNING id",
    )
    .bind(player_id)
    .bind(proposal_type)
    .bind(name)
    .bind(data)
    .bind(info)
    .fetch_one(pool)
    .await
}

/// List all proposals of a given type.
pub async fn list_proposals(
    pool: &PgPool,
    proposal_type: &str,
) -> Result<Vec<ProposalRow>, sqlx::Error> {
    sqlx::query_as::<_, ProposalRow>(
        "SELECT id, player_id, proposal_type, name, data, info
         FROM proposals WHERE proposal_type = $1 ORDER BY id DESC",
    )
    .bind(proposal_type)
    .fetch_all(pool)
    .await
}

// =========================================================================
// Unified comments
// =========================================================================

/// Count comments for a target.
pub async fn count_comments(
    pool: &PgPool,
    target_type: &str,
    target_id: i64,
) -> Result<i64, sqlx::Error> {
    sqlx::query_scalar::<_, i64>(
        "SELECT count(*) FROM content_comments WHERE target_type = $1 AND target_id = $2",
    )
    .bind(target_type)
    .bind(target_id)
    .fetch_one(pool)
    .await
}

/// List paginated comments for a target.
pub async fn list_comments(
    pool: &PgPool,
    target_type: &str,
    target_id: i64,
    limit: i64,
    offset: i64,
) -> Result<Vec<CommentRow>, sqlx::Error> {
    sqlx::query_as::<_, CommentRow>(
        "SELECT id, author_name, author_id, body,
                to_char(created_at, 'YYYY-MM-DD') AS created_at_formatted
         FROM content_comments
         WHERE target_type = $1 AND target_id = $2
         ORDER BY created_at ASC
         LIMIT $3 OFFSET $4",
    )
    .bind(target_type)
    .bind(target_id)
    .bind(limit)
    .bind(offset)
    .fetch_all(pool)
    .await
}

/// Insert a comment.
pub async fn insert_comment(
    pool: &PgPool,
    target_type: &str,
    target_id: i64,
    author_name: &str,
    author_id: i64,
    body: &str,
) -> Result<i64, sqlx::Error> {
    sqlx::query_scalar::<_, i64>(
        "INSERT INTO content_comments (target_type, target_id, author_name, author_id, body)
         VALUES ($1, $2, $3, $4, $5) RETURNING id",
    )
    .bind(target_type)
    .bind(target_id)
    .bind(author_name)
    .bind(author_id)
    .bind(body)
    .fetch_one(pool)
    .await
}

/// Delete a comment (staff only).
pub async fn delete_comment(pool: &PgPool, id: i64) -> Result<(), sqlx::Error> {
    sqlx::query("DELETE FROM content_comments WHERE id = $1")
        .bind(id)
        .execute(pool)
        .await?;
    Ok(())
}

// =========================================================================
// RSS helpers
// =========================================================================

#[derive(Debug, sqlx::FromRow)]
pub struct RssUpdateRow {
    pub title: String,
    pub body: String,
    pub author_name: String,
    pub published_at_rfc2822: String,
}

/// Get last N updates formatted for RSS.
pub async fn list_updates_for_rss(
    pool: &PgPool,
    limit: i64,
) -> Result<Vec<RssUpdateRow>, sqlx::Error> {
    sqlx::query_as::<_, RssUpdateRow>(
        "SELECT title, body, author_name,
                to_char(published_at AT TIME ZONE 'UTC', 'Dy, DD Mon YYYY HH24:MI:SS +0000')
                    AS published_at_rfc2822
         FROM game_updates ORDER BY id DESC LIMIT $1",
    )
    .bind(limit)
    .fetch_all(pool)
    .await
}
