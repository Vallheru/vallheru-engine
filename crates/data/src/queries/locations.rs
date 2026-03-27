//! Queries for secondary location pages (alley, landfill, rest).

use sqlx::PgPool;

// ---------------------------------------------------------------------------
// Alley — vallars leaderboard
// ---------------------------------------------------------------------------

/// A row from the vallars leaderboard query.
#[derive(Debug, sqlx::FromRow)]
pub struct VallarsRow {
    pub id: i32,
    pub username: String,
    pub vallars: i32,
}

/// Load the top `limit` players by vallars score.
pub async fn vallars_leaderboard(
    pool: &PgPool,
    limit: i32,
) -> Result<Vec<VallarsRow>, sqlx::Error> {
    sqlx::query_as::<_, VallarsRow>(
        "SELECT id, username, vallars FROM players ORDER BY vallars DESC LIMIT $1",
    )
    .bind(limit)
    .fetch_all(pool)
    .await
}

// ---------------------------------------------------------------------------
// Landfill — energy→gold work
// ---------------------------------------------------------------------------

/// Deduct `energy_spent` energy and add `gold_gained` credits in one update.
pub async fn landfill_work(
    pool: &PgPool,
    player_id: i32,
    energy_spent: i32,
    gold_gained: i32,
) -> Result<(), sqlx::Error> {
    sqlx::query("UPDATE players SET energy = energy - $1, credits = credits + $2 WHERE id = $3")
        .bind(energy_spent)
        .bind(gold_gained)
        .bind(player_id)
        .execute(pool)
        .await?;
    Ok(())
}

// ---------------------------------------------------------------------------
// Rest — energy→mana recovery
// ---------------------------------------------------------------------------

/// Set mana to `new_mana` and deduct `energy_cost` in one update.
pub async fn rest_recover(
    pool: &PgPool,
    player_id: i32,
    new_mana: i32,
    energy_cost: f64,
) -> Result<(), sqlx::Error> {
    sqlx::query("UPDATE players SET pm = $1, energy = energy - $2 WHERE id = $3")
        .bind(new_mana)
        .bind(energy_cost)
        .bind(player_id)
        .execute(pool)
        .await?;
    Ok(())
}
