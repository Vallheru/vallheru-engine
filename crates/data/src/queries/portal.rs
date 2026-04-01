//! Portal and astral plane data queries.
//!
//! Covers astral map checks, map consumption, component awards,
//! and player state updates specific to portal/astral encounters.

use sqlx::PgPool;

/// Check whether a player owns at least one astral map in their vault.
pub async fn has_astral_map(pool: &PgPool, player_id: i32, map_name: &str) -> sqlx::Result<bool> {
    let amount: Option<i32> = sqlx::query_scalar(
        "SELECT amount FROM astral_plans \
         WHERE owner = $1 AND name = $2 AND location = 'V'",
    )
    .bind(player_id)
    .bind(map_name)
    .fetch_optional(pool)
    .await?;
    Ok(amount.unwrap_or(0) > 0)
}

/// Consume one astral map from a player's vault.
///
/// Decrements amount by 1; deletes the row if amount reaches 0.
pub async fn consume_astral_map(pool: &PgPool, player_id: i32, map_name: &str) -> sqlx::Result<()> {
    let mut tx = pool.begin().await?;

    let amount: Option<i32> = sqlx::query_scalar(
        "SELECT amount FROM astral_plans \
         WHERE owner = $1 AND name = $2 AND location = 'V'",
    )
    .bind(player_id)
    .bind(map_name)
    .fetch_optional(&mut *tx)
    .await?;

    match amount {
        Some(1) => {
            sqlx::query(
                "DELETE FROM astral_plans \
                 WHERE owner = $1 AND name = $2 AND location = 'V'",
            )
            .bind(player_id)
            .bind(map_name)
            .execute(&mut *tx)
            .await?;
        }
        Some(n) if n > 1 => {
            sqlx::query(
                "UPDATE astral_plans SET amount = amount - 1 \
                 WHERE owner = $1 AND name = $2 AND location = 'V'",
            )
            .bind(player_id)
            .bind(map_name)
            .execute(&mut *tx)
            .await?;
        }
        _ => {}
    }

    tx.commit().await?;
    Ok(())
}

/// Award one astral component to a player's vault (insert or increment).
pub async fn award_astral_component(
    pool: &PgPool,
    player_id: i32,
    component_type: &str,
) -> sqlx::Result<()> {
    let merged = sqlx::query(
        "UPDATE astral SET amount = amount + 1 \
         WHERE owner = $1 AND type = $2 AND location = 'V'",
    )
    .bind(player_id)
    .bind(component_type)
    .execute(pool)
    .await?;

    if merged.rows_affected() == 0 {
        sqlx::query(
            "INSERT INTO astral (owner, type, number, amount, location) \
             VALUES ($1, $2, 0, 1, 'V')",
        )
        .bind(player_id)
        .bind(component_type)
        .execute(pool)
        .await?;
    }

    Ok(())
}

/// Set player location.
pub async fn set_player_location(
    pool: &PgPool,
    player_id: i32,
    location: &str,
) -> sqlx::Result<()> {
    sqlx::query("UPDATE players SET location = $1 WHERE id = $2")
        .bind(location)
        .bind(player_id)
        .execute(pool)
        .await?;
    Ok(())
}

/// Set player fight target and location together.
pub async fn set_fight_and_location(
    pool: &PgPool,
    player_id: i32,
    fight: i32,
    location: &str,
) -> sqlx::Result<()> {
    sqlx::query("UPDATE players SET fight = $1, location = $2 WHERE id = $3")
        .bind(fight)
        .bind(location)
        .bind(player_id)
        .execute(pool)
        .await?;
    Ok(())
}

/// Set player energy to zero and move to a location.
pub async fn exhaust_and_move(pool: &PgPool, player_id: i32, location: &str) -> sqlx::Result<()> {
    sqlx::query("UPDATE players SET energy = 0, location = $1 WHERE id = $2")
        .bind(location)
        .bind(player_id)
        .execute(pool)
        .await?;
    Ok(())
}

/// Apply portal defeat: hp=0, fight=0, energy=0, move to location.
pub async fn apply_portal_defeat(
    pool: &PgPool,
    player_id: i32,
    location: &str,
) -> sqlx::Result<()> {
    sqlx::query("UPDATE players SET hp = 0, fight = 0, energy = 0, location = $1 WHERE id = $2")
        .bind(location)
        .bind(player_id)
        .execute(pool)
        .await?;
    Ok(())
}

/// Add to a player's combat skill level directly (attack, shoot, or magic).
pub async fn add_skill_level(
    pool: &PgPool,
    player_id: i32,
    skill_key: &str,
    amount: i32,
) -> sqlx::Result<()> {
    sqlx::query(
        "UPDATE player_skills SET level = level + $1 \
         WHERE player_id = $2 AND skill_key = $3",
    )
    .bind(amount)
    .bind(player_id)
    .bind(skill_key)
    .execute(pool)
    .await?;
    Ok(())
}
