//! Bank and currency persistence queries.
//!
//! Provides SQL for deposit/withdraw operations and player-balance updates.

use sqlx::PgPool;

/// Update player credits and bank after a deposit or withdrawal.
///
/// Both `new_credits` and `new_bank` are absolute values computed by the
/// domain layer (`currency::deposit_gold` / `currency::withdraw_gold`).
pub async fn set_player_balance(
    pool: &PgPool,
    player_id: i32,
    new_credits: i32,
    new_bank: i32,
) -> Result<(), sqlx::Error> {
    sqlx::query("UPDATE players SET credits = $1, bank = $2 WHERE id = $3")
        .bind(new_credits)
        .bind(new_bank)
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
