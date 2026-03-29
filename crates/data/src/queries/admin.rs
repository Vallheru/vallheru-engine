//! Admin and staff queries.
//!
//! Provides data access for the admin panel, staff panel, and staff
//! list pages. Moderation-specific queries will be added by MP-15-02.

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
