//! Character reset and vallar history queries.

use serde::Serialize;
use sqlx::PgPool;

/// An active character reset request.
#[derive(Debug, sqlx::FromRow)]
pub struct CharacterResetRow {
    pub id: i32,
    pub player_id: i32,
    pub code: i32,
    pub reset_type: String,
}

/// A single vallar history entry.
#[derive(Debug, Serialize, sqlx::FromRow)]
pub struct VallarHistoryRow {
    pub amount: i32,
    pub reason: String,
    pub created_date: String,
}

/// Find a pending reset request for a player.
pub async fn find_reset_request(
    pool: &PgPool,
    player_id: i32,
    code: i32,
) -> Result<Option<CharacterResetRow>, sqlx::Error> {
    sqlx::query_as::<_, CharacterResetRow>(
        "SELECT id, player_id, code, reset_type FROM character_resets \
         WHERE player_id = $1 AND code = $2",
    )
    .bind(player_id)
    .bind(code)
    .fetch_optional(pool)
    .await
}

/// Insert a new character reset request.
pub async fn insert_reset_request(
    pool: &PgPool,
    player_id: i32,
    code: i32,
    reset_type: &str,
) -> Result<(), sqlx::Error> {
    // Remove any existing request for this player first.
    sqlx::query("DELETE FROM character_resets WHERE player_id = $1")
        .bind(player_id)
        .execute(pool)
        .await?;

    sqlx::query("INSERT INTO character_resets (player_id, code, reset_type) VALUES ($1, $2, $3)")
        .bind(player_id)
        .bind(code)
        .bind(reset_type)
        .execute(pool)
        .await?;
    Ok(())
}

/// Cancel (delete) all pending reset requests for a player.
pub async fn cancel_reset_request(pool: &PgPool, player_id: i32) -> Result<(), sqlx::Error> {
    sqlx::query("DELETE FROM character_resets WHERE player_id = $1")
        .bind(player_id)
        .execute(pool)
        .await?;
    Ok(())
}

/// Execute a partial character reset (keeps equipment, resets stats/skills).
pub async fn execute_partial_reset(pool: &PgPool, player_id: i32) -> Result<(), sqlx::Error> {
    let mut tx = pool.begin().await?;

    sqlx::query(
        "UPDATE players SET \
         energy = 0, max_energy = 100, ap = 5, hp = 10, max_hp = 10, \
         core_pass = FALSE, trains = 5, pw = 0, immune = FALSE, pm = 6, \
         race = '', class = '', deity = NULL, gender = NULL, \
         wins = 0, losses = 0, last_killed = '...', last_killed_by = '...', \
         maps = 0, craft_mission = 7, mpoints = 0, \
         stats_raw = $2, skills_raw = $3, bonuses_raw = '', \
         bless = '', bless_value = 0 \
         WHERE id = $1",
    )
    .bind(player_id)
    .bind(vallheru_domain::character_reset::RESET_STATS_RAW)
    .bind(vallheru_domain::character_reset::RESET_SKILLS_RAW)
    .execute(&mut *tx)
    .await?;

    // Reset equipment to unequipped state.
    sqlx::query("UPDATE equipment SET status = 'U', cost = 1 WHERE owner = $1")
        .bind(player_id)
        .execute(&mut *tx)
        .await?;
    sqlx::query("UPDATE equipment SET amount = 1 WHERE amount = 0 AND owner = $1")
        .bind(player_id)
        .execute(&mut *tx)
        .await?;

    // Clean up normalized sub-model tables.
    sqlx::query("DELETE FROM player_stats WHERE player_id = $1")
        .bind(player_id)
        .execute(&mut *tx)
        .await?;
    sqlx::query("DELETE FROM player_skills WHERE player_id = $1")
        .bind(player_id)
        .execute(&mut *tx)
        .await?;
    sqlx::query("DELETE FROM player_bonuses WHERE player_id = $1")
        .bind(player_id)
        .execute(&mut *tx)
        .await?;

    // Move player to Altara unless in dungeon.
    sqlx::query("UPDATE players SET location = 'Altara' WHERE location != 'Lochy' AND id = $1")
        .bind(player_id)
        .execute(&mut *tx)
        .await?;

    // Clean up related gameplay records.
    cleanup_gameplay_records(&mut tx, player_id).await?;

    // Delete the reset request.
    sqlx::query("DELETE FROM character_resets WHERE player_id = $1")
        .bind(player_id)
        .execute(&mut *tx)
        .await?;

    tx.commit().await
}

/// Execute a full character reset (deletes equipment, economy, and resets everything).
pub async fn execute_full_reset(pool: &PgPool, player_id: i32) -> Result<(), sqlx::Error> {
    let mut tx = pool.begin().await?;

    // Delete equipment and market listings.
    sqlx::query("DELETE FROM equipment WHERE owner = $1")
        .bind(player_id)
        .execute(&mut *tx)
        .await?;
    sqlx::query("DELETE FROM pmarket WHERE seller = $1")
        .bind(player_id)
        .execute(&mut *tx)
        .await?;
    sqlx::query("DELETE FROM hmarket WHERE seller = $1")
        .bind(player_id)
        .execute(&mut *tx)
        .await?;
    sqlx::query("DELETE FROM potions WHERE owner = $1")
        .bind(player_id)
        .execute(&mut *tx)
        .await?;
    sqlx::query("DELETE FROM herbs WHERE gracz = $1")
        .bind(player_id)
        .execute(&mut *tx)
        .await?;
    sqlx::query("DELETE FROM minerals WHERE owner = $1")
        .bind(player_id)
        .execute(&mut *tx)
        .await?;

    // Full stat/field reset.
    sqlx::query(
        "UPDATE players SET \
         credits = 0, energy = 0, max_energy = 100, ap = 5, platinum = 0, \
         hp = 10, max_hp = 10, bank = 0, core_pass = FALSE, trains = 5, \
         pw = 0, immune = FALSE, pm = 6, race = '', class = '', \
         deity = NULL, gender = NULL, wins = 0, losses = 0, \
         last_killed = '...', last_killed_by = '...', maps = 0, \
         craft_mission = 7, mpoints = 0, \
         stats_raw = $2, skills_raw = $3, bonuses_raw = '', \
         bless = '', bless_value = 0 \
         WHERE id = $1",
    )
    .bind(player_id)
    .bind(vallheru_domain::character_reset::RESET_STATS_RAW)
    .bind(vallheru_domain::character_reset::RESET_SKILLS_RAW)
    .execute(&mut *tx)
    .await?;

    // Handle house ownership transfer.
    let locator: Option<(i32,)> = sqlx::query_as("SELECT locator FROM houses WHERE owner = $1")
        .bind(player_id)
        .fetch_optional(&mut *tx)
        .await?;

    match locator {
        Some((loc,)) if loc > 0 => {
            sqlx::query("UPDATE houses SET owner = $1, locator = 0 WHERE owner = $2")
                .bind(loc)
                .bind(player_id)
                .execute(&mut *tx)
                .await?;
        }
        _ => {
            sqlx::query("DELETE FROM houses WHERE owner = $1")
                .bind(player_id)
                .execute(&mut *tx)
                .await?;
        }
    }

    // Clean up normalized sub-model tables.
    sqlx::query("DELETE FROM player_stats WHERE player_id = $1")
        .bind(player_id)
        .execute(&mut *tx)
        .await?;
    sqlx::query("DELETE FROM player_skills WHERE player_id = $1")
        .bind(player_id)
        .execute(&mut *tx)
        .await?;
    sqlx::query("DELETE FROM player_bonuses WHERE player_id = $1")
        .bind(player_id)
        .execute(&mut *tx)
        .await?;

    // Move player to Altara unless in dungeon.
    sqlx::query("UPDATE players SET location = 'Altara' WHERE location != 'Lochy' AND id = $1")
        .bind(player_id)
        .execute(&mut *tx)
        .await?;

    // Clean up related gameplay records.
    cleanup_gameplay_records(&mut tx, player_id).await?;

    // Delete the reset request.
    sqlx::query("DELETE FROM character_resets WHERE player_id = $1")
        .bind(player_id)
        .execute(&mut *tx)
        .await?;

    tx.commit().await
}

/// Delete gameplay records shared between full and partial resets.
async fn cleanup_gameplay_records(
    tx: &mut sqlx::Transaction<'_, sqlx::Postgres>,
    player_id: i32,
) -> Result<(), sqlx::Error> {
    // Delete spells, cores, logs, outposts, quest actions, etc.
    for table_clause in [
        "DELETE FROM czary WHERE gracz = $1",
        "DELETE FROM core WHERE owner = $1",
        "DELETE FROM core_market WHERE seller = $1",
        "DELETE FROM game_log WHERE owner_id = $1",
        "DELETE FROM outposts WHERE owner = $1",
        "DELETE FROM tribe_oczek WHERE gracz = $1",
        "DELETE FROM farms WHERE owner = $1",
        "DELETE FROM farm WHERE owner = $1",
        "DELETE FROM questaction WHERE player = $1",
        "DELETE FROM lumberjack WHERE owner = $1",
        "DELETE FROM mines WHERE owner = $1",
        "DELETE FROM mines_search WHERE player = $1",
        "DELETE FROM smelter WHERE owner = $1",
        "DELETE FROM smith WHERE owner = $1",
        "DELETE FROM smith_work WHERE owner = $1",
        "DELETE FROM jeweller WHERE owner = $1",
        "DELETE FROM jeweller_work WHERE owner = $1",
        "DELETE FROM astral WHERE location = 'V' AND owner = $1",
        "DELETE FROM astral_bank WHERE location = 'V' AND owner = $1",
        "DELETE FROM astral_plans WHERE location = 'V' AND owner = $1",
    ] {
        sqlx::query(table_clause)
            .bind(player_id)
            .execute(&mut **tx)
            .await?;
    }
    Ok(())
}

/// Load vallar history for a player (most recent first, limited).
pub async fn load_vallar_history(
    pool: &PgPool,
    owner_id: i32,
    limit: i64,
) -> Result<Vec<VallarHistoryRow>, sqlx::Error> {
    sqlx::query_as::<_, VallarHistoryRow>(
        "SELECT amount, reason, TO_CHAR(created_at, 'YYYY-MM-DD') AS created_date \
         FROM vallar_history \
         WHERE owner_id = $1 ORDER BY created_at DESC LIMIT $2",
    )
    .bind(owner_id)
    .bind(limit)
    .fetch_all(pool)
    .await
}

/// Get a player's current vallar count and username.
#[derive(Debug, sqlx::FromRow)]
pub struct PlayerVallarInfo {
    pub id: i32,
    pub username: String,
    pub vallars: i32,
}

pub async fn get_player_vallar_info(
    pool: &PgPool,
    player_id: i32,
) -> Result<Option<PlayerVallarInfo>, sqlx::Error> {
    sqlx::query_as::<_, PlayerVallarInfo>("SELECT id, username, vallars FROM players WHERE id = $1")
        .bind(player_id)
        .fetch_optional(pool)
        .await
}
