//! Queries for secondary location pages (alley, landfill, rest, temple, tower, deity).

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

/// Load all donator names, alphabetically.
pub async fn list_donators(pool: &PgPool) -> Result<Vec<String>, sqlx::Error> {
    sqlx::query_scalar::<_, String>("SELECT name FROM donators ORDER BY name")
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

// ---------------------------------------------------------------------------
// Temple — piety / blessing
// ---------------------------------------------------------------------------

/// Deduct energy and add piety (pw) from temple work.
pub async fn temple_work(
    pool: &PgPool,
    player_id: i32,
    energy_cost: f64,
    piety_gained: i32,
) -> Result<(), sqlx::Error> {
    sqlx::query("UPDATE players SET energy = energy - $1, pw = pw + $2 WHERE id = $3")
        .bind(energy_cost)
        .bind(piety_gained)
        .bind(player_id)
        .execute(pool)
        .await?;
    Ok(())
}

/// Apply a blessing after successful prayer.
pub async fn apply_blessing(
    pool: &PgPool,
    player_id: i32,
    stat_key: &str,
    bless_value: i32,
    piety_cost: i32,
    energy_cost: i32,
) -> Result<(), sqlx::Error> {
    sqlx::query(
        "UPDATE players SET bless = $1, bless_value = $2, \
         pw = pw - $3, energy = energy - $4 WHERE id = $5",
    )
    .bind(stat_key)
    .bind(bless_value)
    .bind(piety_cost)
    .bind(energy_cost)
    .bind(player_id)
    .execute(pool)
    .await?;
    Ok(())
}

/// Apply prayer cost when prayer fails (no blessing applied).
pub async fn prayer_fail(
    pool: &PgPool,
    player_id: i32,
    piety_cost: i32,
    energy_cost: i32,
) -> Result<(), sqlx::Error> {
    sqlx::query("UPDATE players SET pw = pw - $1, energy = energy - $2 WHERE id = $3")
        .bind(piety_cost)
        .bind(energy_cost)
        .bind(player_id)
        .execute(pool)
        .await?;
    Ok(())
}

/// Prayer wrath: kill the player and deduct costs.
pub async fn prayer_wrath(
    pool: &PgPool,
    player_id: i32,
    piety_cost: i32,
    energy_cost: i32,
) -> Result<(), sqlx::Error> {
    sqlx::query("UPDATE players SET hp = 0, pw = pw - $1, energy = energy - $2 WHERE id = $3")
        .bind(piety_cost)
        .bind(energy_cost)
        .bind(player_id)
        .execute(pool)
        .await?;
    Ok(())
}

// ---------------------------------------------------------------------------
// Deity — selection and change
// ---------------------------------------------------------------------------

/// Set the player's deity for the first time.
pub async fn select_deity(
    pool: &PgPool,
    player_id: i32,
    deity_name: &str,
) -> Result<(), sqlx::Error> {
    sqlx::query("UPDATE players SET deity = $1 WHERE id = $2")
        .bind(deity_name)
        .bind(player_id)
        .execute(pool)
        .await?;
    Ok(())
}

/// Clear the player's deity and deduct piety cost.
pub async fn change_deity(
    pool: &PgPool,
    player_id: i32,
    piety_cost: i32,
) -> Result<(), sqlx::Error> {
    sqlx::query(
        "UPDATE players SET deity = NULL, pw = pw - $1, change_deity = change_deity + 1 \
         WHERE id = $2",
    )
    .bind(piety_cost)
    .bind(player_id)
    .execute(pool)
    .await?;
    Ok(())
}

// ---------------------------------------------------------------------------
// Tower — game clock
// ---------------------------------------------------------------------------

/// Load the game age and day from settings.
pub async fn load_game_clock(pool: &PgPool) -> Result<(i32, i32), sqlx::Error> {
    let age_row =
        sqlx::query_scalar::<_, Option<String>>("SELECT value FROM settings WHERE setting = 'age'")
            .fetch_optional(pool)
            .await?;

    let day_row =
        sqlx::query_scalar::<_, Option<String>>("SELECT value FROM settings WHERE setting = 'day'")
            .fetch_optional(pool)
            .await?;

    let age = age_row.flatten().and_then(|v| v.parse().ok()).unwrap_or(1);
    let day = day_row.flatten().and_then(|v| v.parse().ok()).unwrap_or(1);

    Ok((age, day))
}

// ---------------------------------------------------------------------------
// Hospital — healing and resurrection
// ---------------------------------------------------------------------------

/// Check whether a tribe has the hospital pass (50% healing discount).
pub async fn has_hospital_pass(pool: &PgPool, tribe_id: i32) -> Result<bool, sqlx::Error> {
    if tribe_id <= 0 {
        return Ok(false);
    }
    let result = sqlx::query_scalar::<_, String>("SELECT hospass FROM tribes WHERE id = $1")
        .bind(tribe_id)
        .fetch_optional(pool)
        .await?;
    Ok(result.as_deref() == Some("Y"))
}

/// Heal a living player: set hp = `max_hp` and deduct gold cost.
pub async fn heal_player(pool: &PgPool, player_id: i32, gold_cost: i32) -> Result<(), sqlx::Error> {
    sqlx::query("UPDATE players SET hp = max_hp, credits = credits - $1 WHERE id = $2")
        .bind(gold_cost)
        .bind(player_id)
        .execute(pool)
        .await?;
    Ok(())
}

/// Resurrect a dead player: set hp and `max_hp`, deduct gold.
/// Stats and skills must be saved separately via `save_stats` / `save_skills`.
pub async fn resurrect_player(
    pool: &PgPool,
    player_id: i32,
    new_hp: i32,
    new_max_hp: i32,
    gold_cost: i32,
) -> Result<(), sqlx::Error> {
    sqlx::query("UPDATE players SET hp = $1, max_hp = $2, credits = credits - $3 WHERE id = $4")
        .bind(new_hp)
        .bind(new_max_hp)
        .bind(gold_cost)
        .bind(player_id)
        .execute(pool)
        .await?;
    Ok(())
}

/// Move a player to a different location.
pub async fn move_player_to(
    pool: &PgPool,
    player_id: i32,
    location: &str,
) -> Result<(), sqlx::Error> {
    sqlx::query("UPDATE players SET location = $1 WHERE id = $2")
        .bind(location)
        .bind(player_id)
        .execute(pool)
        .await?;
    Ok(())
}
