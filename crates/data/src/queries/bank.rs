//! Bank and currency persistence queries.
//!
//! Provides SQL for deposit/withdraw operations and player-balance updates.

use sqlx::PgPool;

/// Atomically move gold from credits to bank (deposit).
///
/// Uses relative `credits = credits - $1, bank = bank + $1` to avoid
/// TOCTOU races where a concurrent request reads the same starting balance.
/// The CHECK constraints (`credits >= 0`, `bank >= 0`) from migration 000028
/// serve as a last-resort guard.
pub async fn deposit_to_bank(
    pool: &PgPool,
    player_id: i32,
    amount: i32,
) -> Result<(), sqlx::Error> {
    sqlx::query(
        "UPDATE players SET credits = credits - $1, bank = bank + $1 WHERE id = $2 AND credits >= $1",
    )
    .bind(amount)
    .bind(player_id)
    .execute(pool)
    .await?;
    Ok(())
}

/// Atomically move gold from bank to credits (withdrawal).
pub async fn withdraw_from_bank(
    pool: &PgPool,
    player_id: i32,
    amount: i32,
) -> Result<(), sqlx::Error> {
    sqlx::query(
        "UPDATE players SET credits = credits + $1, bank = bank - $1 WHERE id = $2 AND bank >= $1",
    )
    .bind(amount)
    .bind(player_id)
    .execute(pool)
    .await?;
    Ok(())
}

/// Deduct credits (pocket gold) from a player after a purchase.
pub async fn deduct_credits(pool: &PgPool, player_id: i32, amount: i32) -> Result<(), sqlx::Error> {
    sqlx::query("UPDATE players SET credits = credits - $1 WHERE id = $2")
        .bind(amount)
        .bind(player_id)
        .execute(pool)
        .await?;
    Ok(())
}
