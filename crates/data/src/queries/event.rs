//! Queries for random city events (revent table) and hunter quest generation.

use sqlx::PgPool;

// ---------------------------------------------------------------------------
// revent CRUD
// ---------------------------------------------------------------------------

/// A row from the `revent` table.
#[derive(Debug, Clone, sqlx::FromRow)]
pub struct ReventRow {
    pub pid: i32,
    pub state: i16,
    pub qtime: i16,
    pub location: String,
}

/// Fetch the player's current random event state, if any.
pub async fn find_revent(pool: &PgPool, player_id: i32) -> sqlx::Result<Option<ReventRow>> {
    sqlx::query_as::<_, ReventRow>("SELECT pid, state, qtime, location FROM revent WHERE pid = $1")
        .bind(player_id)
        .fetch_optional(pool)
        .await
}

/// Insert a new random event row.
pub async fn insert_revent(
    pool: &PgPool,
    player_id: i32,
    state: i16,
    qtime: i16,
    location: &str,
) -> sqlx::Result<()> {
    sqlx::query(
        "INSERT INTO revent (pid, state, qtime, location) VALUES ($1, $2, $3, $4) \
         ON CONFLICT (pid) DO UPDATE SET state = $2, qtime = $3, location = $4",
    )
    .bind(player_id)
    .bind(state)
    .bind(qtime)
    .bind(location)
    .execute(pool)
    .await?;
    Ok(())
}

/// Update the state and cooldown of an existing event row.
pub async fn update_revent(
    pool: &PgPool,
    player_id: i32,
    state: i16,
    qtime: i16,
) -> sqlx::Result<()> {
    sqlx::query("UPDATE revent SET state = $2, qtime = $3 WHERE pid = $1")
        .bind(player_id)
        .bind(state)
        .bind(qtime)
        .execute(pool)
        .await?;
    Ok(())
}

/// Update state, cooldown, and location.
pub async fn update_revent_full(
    pool: &PgPool,
    player_id: i32,
    state: i16,
    qtime: i16,
    location: &str,
) -> sqlx::Result<()> {
    sqlx::query("UPDATE revent SET state = $2, qtime = $3, location = $4 WHERE pid = $1")
        .bind(player_id)
        .bind(state)
        .bind(qtime)
        .bind(location)
        .execute(pool)
        .await?;
    Ok(())
}

/// Delete a player's event row (event dismissed or no longer relevant).
pub async fn delete_revent(pool: &PgPool, player_id: i32) -> sqlx::Result<()> {
    sqlx::query("DELETE FROM revent WHERE pid = $1")
        .bind(player_id)
        .execute(pool)
        .await?;
    Ok(())
}

// ---------------------------------------------------------------------------
// Hunter quest generation helpers
// ---------------------------------------------------------------------------

/// Fetch all monster IDs for a given city location.
pub async fn monster_ids_by_location(pool: &PgPool, location: &str) -> sqlx::Result<Vec<i32>> {
    let rows = sqlx::query_scalar::<_, i32>("SELECT id FROM monsters WHERE location = $1")
        .bind(location)
        .fetch_all(pool)
        .await?;
    Ok(rows)
}

/// Fetch all monster IDs that have loot drops in a given location.
pub async fn monster_ids_with_loot(pool: &PgPool, location: &str) -> sqlx::Result<Vec<i32>> {
    let rows = sqlx::query_scalar::<_, i32>(
        "SELECT id FROM monsters WHERE location = $1 AND lootnames IS NOT NULL AND lootnames != ''",
    )
    .bind(location)
    .fetch_all(pool)
    .await?;
    Ok(rows)
}

/// Fetch all catalog equipment IDs (owner = 0).
pub async fn catalog_equipment_ids(pool: &PgPool) -> sqlx::Result<Vec<i32>> {
    sqlx::query_scalar::<_, i32>("SELECT id FROM equipment WHERE owner = 0")
        .fetch_all(pool)
        .await
}

/// Fetch all catalog bow IDs (type = 'B').
pub async fn catalog_bow_ids(pool: &PgPool) -> sqlx::Result<Vec<i32>> {
    sqlx::query_scalar::<_, i32>("SELECT id FROM bows WHERE type = 'B'")
        .fetch_all(pool)
        .await
}

/// Fetch all catalog potion IDs (owner = 0).
pub async fn catalog_potion_ids(pool: &PgPool) -> sqlx::Result<Vec<i32>> {
    sqlx::query_scalar::<_, i32>("SELECT id FROM potions WHERE owner = 0")
        .fetch_all(pool)
        .await
}
