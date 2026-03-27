//! Queries for gathering, mining, smelting, lumberjack, and farm systems.

use sqlx::PgPool;

// =========================================================================
// Minerals storage
// =========================================================================

/// A row from the minerals table.
#[derive(Debug, Clone, Default, sqlx::FromRow)]
pub struct MineralsRow {
    pub owner: i32,
    pub copperore: i32,
    pub zincore: i32,
    pub tinore: i32,
    pub ironore: i32,
    pub coal: i32,
    pub copper: i32,
    pub bronze: i32,
    pub brass: i32,
    pub iron: i32,
    pub steel: i32,
    pub pine: i32,
    pub hazel: i32,
    pub yew: i32,
    pub elm: i32,
    pub crystal: i32,
    pub adamantium: i32,
    pub meteor: i32,
}

/// Load a player's mineral inventory. Returns `None` if no row exists.
pub async fn load_minerals(
    pool: &PgPool,
    player_id: i32,
) -> Result<Option<MineralsRow>, sqlx::Error> {
    sqlx::query_as::<_, MineralsRow>("SELECT * FROM minerals WHERE owner = $1")
        .bind(player_id)
        .fetch_optional(pool)
        .await
}

/// Ensure a minerals row exists for the player (insert if missing).
pub async fn ensure_minerals(pool: &PgPool, player_id: i32) -> Result<(), sqlx::Error> {
    sqlx::query("INSERT INTO minerals (owner) VALUES ($1) ON CONFLICT (owner) DO NOTHING")
        .bind(player_id)
        .execute(pool)
        .await?;
    Ok(())
}

/// Add amounts to specific mineral columns. Uses dynamic column names
/// validated against an allowlist.
pub async fn add_minerals(
    pool: &PgPool,
    player_id: i32,
    updates: &[(&str, i32)],
) -> Result<(), sqlx::Error> {
    const ALLOWED: &[&str] = &[
        "copperore",
        "zincore",
        "tinore",
        "ironore",
        "coal",
        "copper",
        "bronze",
        "brass",
        "iron",
        "steel",
        "pine",
        "hazel",
        "yew",
        "elm",
        "crystal",
        "adamantium",
        "meteor",
    ];

    if updates.is_empty() {
        return Ok(());
    }

    // Build SET clause dynamically but only for allowed columns.
    let mut set_parts = Vec::new();
    let mut values: Vec<i32> = Vec::new();
    let mut param_idx = 1;

    for &(col, amount) in updates {
        if !ALLOWED.contains(&col) {
            continue;
        }
        // Use format! for column names (validated against allowlist above).
        set_parts.push(format!("{col} = {col} + ${param_idx}"));
        values.push(amount);
        param_idx += 1;
    }

    if set_parts.is_empty() {
        return Ok(());
    }

    let sql = format!(
        "UPDATE minerals SET {} WHERE owner = ${param_idx}",
        set_parts.join(", ")
    );

    let mut query = sqlx::query(&sql);
    for v in &values {
        query = query.bind(*v);
    }
    query = query.bind(player_id);
    query.execute(pool).await?;
    Ok(())
}

// =========================================================================
// Mine deposits
// =========================================================================

/// A row from the mines table (ore deposits).
#[derive(Debug, Clone, Default, sqlx::FromRow)]
pub struct MinesRow {
    pub owner: i32,
    pub copper: i32,
    pub zinc: i32,
    pub tin: i32,
    pub iron: i32,
    pub coal: i32,
}

/// Load a player's mine deposits.
pub async fn load_mines(pool: &PgPool, player_id: i32) -> Result<Option<MinesRow>, sqlx::Error> {
    sqlx::query_as::<_, MinesRow>("SELECT * FROM mines WHERE owner = $1")
        .bind(player_id)
        .fetch_optional(pool)
        .await
}

/// Deduct ore from deposits after digging.
pub async fn deduct_deposit(
    pool: &PgPool,
    player_id: i32,
    ore_column: &str,
    amount: i32,
) -> Result<(), sqlx::Error> {
    const ALLOWED: &[&str] = &["copper", "zinc", "tin", "iron", "coal"];
    if !ALLOWED.contains(&ore_column) {
        return Ok(());
    }
    let sql = format!("UPDATE mines SET {ore_column} = {ore_column} - $1 WHERE owner = $2");
    sqlx::query(&sql)
        .bind(amount)
        .bind(player_id)
        .execute(pool)
        .await?;
    Ok(())
}

// =========================================================================
// Geologist search
// =========================================================================

/// Active geologist search row.
#[derive(Debug, Clone, sqlx::FromRow)]
pub struct MinesSearchRow {
    pub player: i32,
    pub days: i16,
    pub mineral: String,
    pub searchdays: i16,
}

/// Load active search for a player.
pub async fn load_mines_search(
    pool: &PgPool,
    player_id: i32,
) -> Result<Option<MinesSearchRow>, sqlx::Error> {
    sqlx::query_as::<_, MinesSearchRow>("SELECT * FROM mines_search WHERE player = $1")
        .bind(player_id)
        .fetch_optional(pool)
        .await
}

/// Start a new geologist search.
pub async fn start_mines_search(
    pool: &PgPool,
    player_id: i32,
    mineral: &str,
    days: i16,
) -> Result<(), sqlx::Error> {
    sqlx::query(
        "INSERT INTO mines_search (player, days, mineral, searchdays) VALUES ($1, $2, $3, $4)",
    )
    .bind(player_id)
    .bind(days)
    .bind(mineral)
    .bind(days)
    .execute(pool)
    .await?;
    Ok(())
}

// =========================================================================
// Smelter level
// =========================================================================

/// Load a player's smelter level (0 if no row).
pub async fn load_smelter_level(pool: &PgPool, player_id: i32) -> Result<i32, sqlx::Error> {
    let row: Option<(i16,)> = sqlx::query_as("SELECT level FROM smelter WHERE owner = $1")
        .bind(player_id)
        .fetch_optional(pool)
        .await?;
    Ok(row.map_or(0, |r| i32::from(r.0)))
}

/// Upgrade smelter level (insert or update).
pub async fn upgrade_smelter(pool: &PgPool, player_id: i32) -> Result<(), sqlx::Error> {
    sqlx::query(
        "INSERT INTO smelter (owner, level) VALUES ($1, 1) \
         ON CONFLICT (owner) DO UPDATE SET level = smelter.level + 1",
    )
    .bind(player_id)
    .execute(pool)
    .await?;
    Ok(())
}

// =========================================================================
// Lumberjack license
// =========================================================================

/// Load a player's lumberjack license level (0 if no row).
pub async fn load_lumberjack_level(pool: &PgPool, player_id: i32) -> Result<i32, sqlx::Error> {
    let row: Option<(i16,)> = sqlx::query_as("SELECT level FROM lumberjack WHERE owner = $1")
        .bind(player_id)
        .fetch_optional(pool)
        .await?;
    Ok(row.map_or(0, |r| i32::from(r.0)))
}

/// Upgrade lumberjack license (insert or update).
pub async fn upgrade_lumberjack(pool: &PgPool, player_id: i32) -> Result<(), sqlx::Error> {
    sqlx::query(
        "INSERT INTO lumberjack (owner, level) VALUES ($1, 1) \
         ON CONFLICT (owner) DO UPDATE SET level = lumberjack.level + 1",
    )
    .bind(player_id)
    .execute(pool)
    .await?;
    Ok(())
}

// =========================================================================
// Herbs
// =========================================================================

/// A row from the herbs table.
#[derive(Debug, Clone, Default, sqlx::FromRow)]
pub struct HerbsRow {
    pub id: i32,
    pub gracz: i32,
    pub illani: i32,
    pub illanias: i32,
    pub nutari: i32,
    pub dynallca: i32,
    pub ilani_seeds: i32,
    pub illanias_seeds: i32,
    pub nutari_seeds: i32,
    pub dynallca_seeds: i32,
}

/// Load herb inventory for a player.
pub async fn load_herbs(pool: &PgPool, player_id: i32) -> Result<Option<HerbsRow>, sqlx::Error> {
    sqlx::query_as::<_, HerbsRow>("SELECT * FROM herbs WHERE gracz = $1")
        .bind(player_id)
        .fetch_optional(pool)
        .await
}

/// Ensure an herbs row exists for the player.
pub async fn ensure_herbs(pool: &PgPool, player_id: i32) -> Result<(), sqlx::Error> {
    sqlx::query("INSERT INTO herbs (gracz) VALUES ($1) ON CONFLICT DO NOTHING")
        .bind(player_id)
        .execute(pool)
        .await?;
    Ok(())
}

// =========================================================================
// Farm / Plantation
// =========================================================================

/// A row from the farms table.
#[derive(Debug, Clone, Default, sqlx::FromRow)]
pub struct FarmRow {
    pub id: i32,
    pub owner: i32,
    pub lands: i32,
    pub glasshouse: i32,
    pub irrigation: i32,
    pub creeper: i32,
    pub location: String,
}

/// Load a player's plantation at a specific location.
pub async fn load_farm(
    pool: &PgPool,
    player_id: i32,
    location: &str,
) -> Result<Option<FarmRow>, sqlx::Error> {
    sqlx::query_as::<_, FarmRow>("SELECT * FROM farms WHERE owner = $1 AND location = $2")
        .bind(player_id)
        .bind(location)
        .fetch_optional(pool)
        .await
}

/// A row from the farm (plots) table.
#[derive(Debug, Clone, sqlx::FromRow)]
pub struct FarmPlotRow {
    pub id: i32,
    pub farmid: i32,
    pub amount: i32,
    pub name: Option<String>,
    pub age: i32,
    pub owner: i32,
}

/// Load all farm plots for a plantation.
pub async fn load_farm_plots(pool: &PgPool, farm_id: i32) -> Result<Vec<FarmPlotRow>, sqlx::Error> {
    sqlx::query_as::<_, FarmPlotRow>("SELECT * FROM farm WHERE farmid = $1")
        .bind(farm_id)
        .fetch_all(pool)
        .await
}

/// Deduct energy from player after a gathering action.
pub async fn deduct_energy(pool: &PgPool, player_id: i32, energy: f64) -> Result<(), sqlx::Error> {
    sqlx::query("UPDATE players SET energy = energy - $1 WHERE id = $2")
        .bind(energy)
        .bind(player_id)
        .execute(pool)
        .await?;
    Ok(())
}

/// Deduct gold and mithril from player.
pub async fn deduct_currency(
    pool: &PgPool,
    player_id: i32,
    gold: i32,
    mithril: i32,
) -> Result<(), sqlx::Error> {
    sqlx::query(
        "UPDATE players SET credits = credits - $1, platinum = platinum - $2 WHERE id = $3",
    )
    .bind(gold)
    .bind(mithril)
    .bind(player_id)
    .execute(pool)
    .await?;
    Ok(())
}
