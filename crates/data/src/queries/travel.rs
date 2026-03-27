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
