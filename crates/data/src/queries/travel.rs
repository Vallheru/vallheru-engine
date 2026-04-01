//! Travel-related database queries.

use sqlx::PgPool;

/// Update the player's location and deduct gold (credits) for caravan or
/// magic portal travel.
pub async fn move_player_deduct_gold(
    pool: &PgPool,
    player_id: i32,
    new_location: &str,
    gold_cost: i32,
) -> Result<(), sqlx::Error> {
    sqlx::query("UPDATE players SET miejsce = $1, credits = credits - $2 WHERE id = $3")
        .bind(new_location)
        .bind(gold_cost)
        .bind(player_id)
        .execute(pool)
        .await?;
    Ok(())
}

/// Update the player's location and deduct energy for walking travel.
pub async fn move_player_deduct_energy(
    pool: &PgPool,
    player_id: i32,
    new_location: &str,
    energy_cost: i32,
) -> Result<(), sqlx::Error> {
    sqlx::query("UPDATE players SET miejsce = $1, energy = energy - $2 WHERE id = $3")
        .bind(new_location)
        .bind(energy_cost)
        .bind(player_id)
        .execute(pool)
        .await?;
    Ok(())
}

// ---------------------------------------------------------------------------
// Travel encounters (bandit)
// ---------------------------------------------------------------------------

/// A row from the `travel_encounters` table.
#[derive(Debug, sqlx::FromRow)]
pub struct TravelEncounterRow {
    pub player_id: i32,
    pub destination: String,
    pub method: String,
    pub travel_cost: i32,
    pub monster_id: i32,
}

/// Insert a generated bandit monster into the `monsters` catalog (with
/// `location = 'Travel'`) and return its auto-assigned ID.
#[allow(clippy::too_many_arguments)]
pub async fn insert_travel_monster(
    pool: &PgPool,
    name: &str,
    level: i32,
    hp: i32,
    strength: f64,
    agility: f64,
    speed: f64,
    endurance: f64,
    dmgtype: &str,
    resistance: &str,
) -> sqlx::Result<i32> {
    let row: (i32,) = sqlx::query_as(
        "INSERT INTO monsters (name, level, hp, strength, agility, speed, endurance, \
         location, lootnames, lootchances, description, dmgtype, resistance) \
         VALUES ($1,$2,$3,$4,$5,$6,$7,'Travel','','','',$8,$9) RETURNING id",
    )
    .bind(name)
    .bind(level)
    .bind(hp)
    .bind(strength)
    .bind(agility)
    .bind(speed)
    .bind(endurance)
    .bind(dmgtype)
    .bind(resistance)
    .fetch_one(pool)
    .await?;
    Ok(row.0)
}

/// Record a travel encounter for a player.
pub async fn insert_travel_encounter(
    pool: &PgPool,
    player_id: i32,
    destination: &str,
    method: &str,
    cost: i32,
    monster_id: i32,
) -> sqlx::Result<()> {
    sqlx::query(
        "INSERT INTO travel_encounters (player_id, destination, method, travel_cost, monster_id) \
         VALUES ($1,$2,$3,$4,$5) \
         ON CONFLICT (player_id) DO UPDATE \
         SET destination=$2, method=$3, travel_cost=$4, monster_id=$5, created_at=NOW()",
    )
    .bind(player_id)
    .bind(destination)
    .bind(method)
    .bind(cost)
    .bind(monster_id)
    .execute(pool)
    .await?;
    Ok(())
}

/// Load the active travel encounter for a player.
pub async fn load_travel_encounter(
    pool: &PgPool,
    player_id: i32,
) -> sqlx::Result<Option<TravelEncounterRow>> {
    sqlx::query_as::<_, TravelEncounterRow>(
        "SELECT player_id, destination, method, travel_cost, monster_id \
         FROM travel_encounters WHERE player_id = $1",
    )
    .bind(player_id)
    .fetch_optional(pool)
    .await
}

/// Delete a travel encounter and its temporary monster row.
pub async fn delete_travel_encounter(pool: &PgPool, player_id: i32) -> sqlx::Result<()> {
    // Load monster_id first so we can clean up the temp monster.
    let enc = sqlx::query_as::<_, (i32,)>(
        "DELETE FROM travel_encounters WHERE player_id = $1 RETURNING monster_id",
    )
    .bind(player_id)
    .fetch_optional(pool)
    .await?;

    if let Some((monster_id,)) = enc {
        sqlx::query("DELETE FROM monsters WHERE id = $1 AND location = 'Travel'")
            .bind(monster_id)
            .execute(pool)
            .await?;
    }
    Ok(())
}

/// Set a player's location to "Podróż" (travelling) and fight to
/// a specific monster for a bandit encounter.
pub async fn set_player_travelling(
    pool: &PgPool,
    player_id: i32,
    monster_id: i32,
) -> sqlx::Result<()> {
    sqlx::query("UPDATE players SET miejsce = 'Podróż', fight = $1 WHERE id = $2")
        .bind(monster_id)
        .bind(player_id)
        .execute(pool)
        .await?;
    Ok(())
}
