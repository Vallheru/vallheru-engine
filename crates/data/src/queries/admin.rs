//! Admin and staff queries.
//!
//! Provides data access for the admin panel, staff panel, staff list,
//! bug reports, admin logs, and member list pages.
//! Moderation-specific queries will be added by MP-15-02.

use sqlx::PgPool;

// ---------------------------------------------------------------------------
// Staff list (audience hall)
// ---------------------------------------------------------------------------

/// A row from the staff list query.
#[derive(Debug, sqlx::FromRow)]
pub struct StaffRow {
    pub id: i32,
    pub username: String,
    pub rank: String,
}

/// Load all players whose rank is in the given set.
///
/// Used by the staff list (audience hall) page to display members
/// grouped by rank.
pub async fn list_staff_members(
    pool: &PgPool,
    ranks: &[&str],
) -> Result<Vec<StaffRow>, sqlx::Error> {
    sqlx::query_as::<_, StaffRow>(
        "SELECT id, username, rank \
         FROM players \
         WHERE rank = ANY($1) \
         ORDER BY rank, id",
    )
    .bind(ranks)
    .fetch_all(pool)
    .await
}

// ---------------------------------------------------------------------------
// Bug reports
// ---------------------------------------------------------------------------

/// A row from the bug report listing.
#[derive(Debug, sqlx::FromRow)]
pub struct BugReportRow {
    pub id: i32,
    pub sender: i32,
    pub title: String,
    pub location: String,
    pub body: String,
    pub resolution: i16,
}

/// A bug comment row.
#[derive(Debug, sqlx::FromRow)]
pub struct BugCommentRow {
    pub id: i32,
    pub author: String,
    pub body: String,
    pub created: Option<String>,
}

/// List all bug reports ordered by id.
pub async fn list_bug_reports(pool: &PgPool) -> Result<Vec<BugReportRow>, sqlx::Error> {
    sqlx::query_as::<_, BugReportRow>(
        "SELECT id, sender, title, location, body, resolution \
         FROM bugreport ORDER BY id ASC",
    )
    .fetch_all(pool)
    .await
}

/// Load a single bug report by id.
pub async fn find_bug_report(
    pool: &PgPool,
    bug_id: i32,
) -> Result<Option<BugReportRow>, sqlx::Error> {
    sqlx::query_as::<_, BugReportRow>(
        "SELECT id, sender, title, location, body, resolution \
         FROM bugreport WHERE id = $1",
    )
    .bind(bug_id)
    .fetch_optional(pool)
    .await
}

/// Load comments for a bug report.
pub async fn bug_comments(pool: &PgPool, bug_id: i32) -> Result<Vec<BugCommentRow>, sqlx::Error> {
    sqlx::query_as::<_, BugCommentRow>(
        "SELECT id, author, body, \
               TO_CHAR(created, 'YYYY-MM-DD') AS created \
         FROM bug_comments WHERE bug_id = $1 ORDER BY id ASC",
    )
    .bind(bug_id)
    .fetch_all(pool)
    .await
}

/// Delete a resolved bug report and its comments.
pub async fn delete_bug_report(pool: &PgPool, bug_id: i32) -> Result<(), sqlx::Error> {
    sqlx::query("DELETE FROM bug_comments WHERE bug_id = $1")
        .bind(bug_id)
        .execute(pool)
        .await?;
    sqlx::query("DELETE FROM bugreport WHERE id = $1")
        .bind(bug_id)
        .execute(pool)
        .await?;
    Ok(())
}

/// Update the resolution status of a bug report.
pub async fn update_bug_resolution(
    pool: &PgPool,
    bug_id: i32,
    resolution: i16,
) -> Result<(), sqlx::Error> {
    sqlx::query("UPDATE bugreport SET resolution = $1 WHERE id = $2")
        .bind(resolution)
        .bind(bug_id)
        .execute(pool)
        .await?;
    Ok(())
}

/// Insert a staff comment on a bug report.
pub async fn add_bug_comment(
    pool: &PgPool,
    bug_id: i32,
    author: &str,
    body: &str,
) -> Result<(), sqlx::Error> {
    sqlx::query(
        "INSERT INTO bug_comments (bug_id, author, body, created) \
         VALUES ($1, $2, $3, CURRENT_DATE)",
    )
    .bind(bug_id)
    .bind(author)
    .bind(body)
    .execute(pool)
    .await?;
    Ok(())
}

// ---------------------------------------------------------------------------
// Admin logs (game_log_daily)
// ---------------------------------------------------------------------------

/// A row from the admin log viewer.
#[derive(Debug, sqlx::FromRow)]
pub struct AdminLogRow {
    pub owner_id: i32,
    pub message: String,
    pub created_at: String,
}

/// Count admin log entries, optionally filtered by player id.
pub async fn count_admin_logs(pool: &PgPool, player_id: Option<i32>) -> Result<i64, sqlx::Error> {
    match player_id {
        Some(pid) => {
            sqlx::query_scalar("SELECT COUNT(*) FROM game_log_daily WHERE owner_id = $1")
                .bind(pid)
                .fetch_one(pool)
                .await
        }
        None => {
            sqlx::query_scalar("SELECT COUNT(*) FROM game_log_daily")
                .fetch_one(pool)
                .await
        }
    }
}

/// Load a page of admin log entries (50 per page).
pub async fn list_admin_logs(
    pool: &PgPool,
    player_id: Option<i32>,
    page: i64,
) -> Result<Vec<AdminLogRow>, sqlx::Error> {
    let offset = (page - 1) * 50;
    match player_id {
        Some(pid) => {
            sqlx::query_as::<_, AdminLogRow>(
                "SELECT owner_id, message, \
                       TO_CHAR(created_at, 'YYYY-MM-DD') AS created_at \
                 FROM game_log_daily \
                 WHERE owner_id = $1 \
                 ORDER BY id DESC \
                 LIMIT 50 OFFSET $2",
            )
            .bind(pid)
            .bind(offset)
            .fetch_all(pool)
            .await
        }
        None => {
            sqlx::query_as::<_, AdminLogRow>(
                "SELECT owner_id, message, \
                       TO_CHAR(created_at, 'YYYY-MM-DD') AS created_at \
                 FROM game_log_daily \
                 ORDER BY id DESC \
                 LIMIT 50 OFFSET $1",
            )
            .bind(offset)
            .fetch_all(pool)
            .await
        }
    }
}

/// Delete all admin logs.
pub async fn clear_admin_logs(pool: &PgPool) -> Result<(), sqlx::Error> {
    sqlx::query("TRUNCATE TABLE game_log_daily")
        .execute(pool)
        .await?;
    Ok(())
}

// ---------------------------------------------------------------------------
// Member list
// ---------------------------------------------------------------------------

/// A row from the member list query.
#[derive(Debug, sqlx::FromRow)]
pub struct MemberRow {
    pub id: i32,
    pub username: String,
    pub rank: String,
}

/// Count total players, optionally filtered by name pattern.
pub async fn count_members(pool: &PgPool, search: Option<&str>) -> Result<i64, sqlx::Error> {
    match search {
        Some(pattern) => {
            sqlx::query_scalar("SELECT COUNT(*) FROM players WHERE username ILIKE $1")
                .bind(pattern)
                .fetch_one(pool)
                .await
        }
        None => {
            sqlx::query_scalar("SELECT COUNT(*) FROM players")
                .fetch_one(pool)
                .await
        }
    }
}

/// Load a page of members (25 per page), optionally filtered.
pub async fn list_members(
    pool: &PgPool,
    search: Option<&str>,
    page: i64,
) -> Result<Vec<MemberRow>, sqlx::Error> {
    let offset = (page - 1) * 25;
    match search {
        Some(pattern) => {
            sqlx::query_as::<_, MemberRow>(
                "SELECT id, username, rank \
                 FROM players \
                 WHERE username ILIKE $1 \
                 ORDER BY id ASC \
                 LIMIT 25 OFFSET $2",
            )
            .bind(pattern)
            .bind(offset)
            .fetch_all(pool)
            .await
        }
        None => {
            sqlx::query_as::<_, MemberRow>(
                "SELECT id, username, rank \
                 FROM players \
                 ORDER BY id ASC \
                 LIMIT 25 OFFSET $1",
            )
            .bind(offset)
            .fetch_all(pool)
            .await
        }
    }
}
