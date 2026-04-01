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

// =========================================================================
// Player-to-player transfers
// =========================================================================

/// Transfer gold from sender's bank to recipient's bank.
///
/// Both updates are in one statement so either both succeed or the DB
/// constraint guards prevent negative balances.
pub async fn transfer_gold(
    pool: &PgPool,
    sender_id: i32,
    recipient_id: i32,
    amount: i32,
) -> Result<bool, sqlx::Error> {
    let res = sqlx::query("UPDATE players SET bank = bank - $1 WHERE id = $2 AND bank >= $1")
        .bind(amount)
        .bind(sender_id)
        .execute(pool)
        .await?;

    if res.rows_affected() == 0 {
        return Ok(false);
    }

    sqlx::query("UPDATE players SET bank = bank + $1 WHERE id = $2")
        .bind(amount)
        .bind(recipient_id)
        .execute(pool)
        .await?;

    Ok(true)
}

/// Transfer mithril (platinum) between players.
pub async fn transfer_mithril(
    pool: &PgPool,
    sender_id: i32,
    recipient_id: i32,
    amount: i32,
) -> Result<bool, sqlx::Error> {
    let res =
        sqlx::query("UPDATE players SET platinum = platinum - $1 WHERE id = $2 AND platinum >= $1")
            .bind(amount)
            .bind(sender_id)
            .execute(pool)
            .await?;

    if res.rows_affected() == 0 {
        return Ok(false);
    }

    sqlx::query("UPDATE players SET platinum = platinum + $1 WHERE id = $2")
        .bind(amount)
        .bind(recipient_id)
        .execute(pool)
        .await?;

    Ok(true)
}

/// Transfer a specific mineral from sender to recipient.
///
/// Validates column name against an allowlist to prevent SQL injection.
/// Returns `false` if the sender doesn't have enough.
pub async fn transfer_mineral(
    pool: &PgPool,
    sender_id: i32,
    recipient_id: i32,
    mineral_col: &str,
    amount: i32,
) -> Result<bool, sqlx::Error> {
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
    if !ALLOWED.contains(&mineral_col) {
        return Ok(false);
    }

    // Deduct from sender (with balance check).
    let sql = format!(
        "UPDATE minerals SET {mineral_col} = {mineral_col} - $1 \
         WHERE owner = $2 AND {mineral_col} >= $1"
    );
    let res = sqlx::query(&sql)
        .bind(amount)
        .bind(sender_id)
        .execute(pool)
        .await?;

    if res.rows_affected() == 0 {
        return Ok(false);
    }

    // Ensure recipient has a minerals row, then add.
    sqlx::query("INSERT INTO minerals (owner) VALUES ($1) ON CONFLICT (owner) DO NOTHING")
        .bind(recipient_id)
        .execute(pool)
        .await?;

    let sql = format!("UPDATE minerals SET {mineral_col} = {mineral_col} + $1 WHERE owner = $2");
    sqlx::query(&sql)
        .bind(amount)
        .bind(recipient_id)
        .execute(pool)
        .await?;

    Ok(true)
}

/// Transfer a specific herb from sender to recipient.
pub async fn transfer_herb(
    pool: &PgPool,
    sender_id: i32,
    recipient_id: i32,
    herb_col: &str,
    amount: i32,
) -> Result<bool, sqlx::Error> {
    const ALLOWED: &[&str] = &[
        "illani",
        "illanias",
        "nutari",
        "dynallca",
        "ilani_seeds",
        "illanias_seeds",
        "nutari_seeds",
        "dynallca_seeds",
    ];
    if !ALLOWED.contains(&herb_col) {
        return Ok(false);
    }

    let sql = format!(
        "UPDATE herbs SET {herb_col} = {herb_col} - $1 \
         WHERE gracz = $2 AND {herb_col} >= $1"
    );
    let res = sqlx::query(&sql)
        .bind(amount)
        .bind(sender_id)
        .execute(pool)
        .await?;

    if res.rows_affected() == 0 {
        return Ok(false);
    }

    sqlx::query("INSERT INTO herbs (gracz) VALUES ($1) ON CONFLICT DO NOTHING")
        .bind(recipient_id)
        .execute(pool)
        .await?;

    let sql = format!("UPDATE herbs SET {herb_col} = {herb_col} + $1 WHERE gracz = $2");
    sqlx::query(&sql)
        .bind(amount)
        .bind(recipient_id)
        .execute(pool)
        .await?;

    Ok(true)
}

/// Transfer a potion (or partial stack) to another player.
///
/// If the recipient already has a matching potion stack (same name, power,
/// status='K'), it merges. Otherwise creates a new row.
pub async fn transfer_potion(
    pool: &PgPool,
    sender_id: i32,
    potion_id: i32,
    recipient_id: i32,
    amount: i32,
) -> Result<bool, sqlx::Error> {
    // Load the potion and verify ownership.
    let potion = sqlx::query_as::<_, super::item::PotionRow>(
        "SELECT * FROM potions WHERE id = $1 AND owner = $2 AND status = 'K'",
    )
    .bind(potion_id)
    .bind(sender_id)
    .fetch_optional(pool)
    .await?;

    let Some(potion) = potion else {
        return Ok(false);
    };

    if potion.amount < amount {
        return Ok(false);
    }

    // Check if recipient has a matching stack.
    let existing: Option<(i32,)> = sqlx::query_as(
        "SELECT id FROM potions WHERE name = $1 AND owner = $2 AND status = 'K' AND power = $3",
    )
    .bind(&potion.name)
    .bind(recipient_id)
    .bind(potion.power)
    .fetch_optional(pool)
    .await?;

    if let Some((existing_id,)) = existing {
        sqlx::query("UPDATE potions SET amount = amount + $1 WHERE id = $2")
            .bind(amount)
            .bind(existing_id)
            .execute(pool)
            .await?;
    } else {
        sqlx::query(
            "INSERT INTO potions (owner, name, efect, power, amount, status, type) \
             VALUES ($1, $2, $3, $4, $5, 'K', $6)",
        )
        .bind(recipient_id)
        .bind(&potion.name)
        .bind(&potion.efect)
        .bind(potion.power)
        .bind(amount)
        .bind(&potion.potion_type)
        .execute(pool)
        .await?;
    }

    // Deduct from sender.
    if amount >= potion.amount {
        sqlx::query("DELETE FROM potions WHERE id = $1")
            .bind(potion_id)
            .execute(pool)
            .await?;
    } else {
        sqlx::query("UPDATE potions SET amount = amount - $1 WHERE id = $2")
            .bind(amount)
            .bind(potion_id)
            .execute(pool)
            .await?;
    }

    Ok(true)
}

/// Transfer an equipment item (or partial stack) to another player.
///
/// Quest items (type='Q') cannot be transferred.
/// Arrows (type='R') use `wt` as the quantity field instead of `amount`.
#[allow(clippy::too_many_lines)]
pub async fn transfer_equipment(
    pool: &PgPool,
    sender_id: i32,
    item_id: i32,
    recipient_id: i32,
    amount: i32,
) -> Result<bool, sqlx::Error> {
    let item = sqlx::query_as::<_, super::item::EquipmentRow>(
        "SELECT * FROM equipment WHERE id = $1 AND owner = $2 AND status = 'U'",
    )
    .bind(item_id)
    .bind(sender_id)
    .fetch_optional(pool)
    .await?;

    let Some(item) = item else {
        return Ok(false);
    };

    // Quest items cannot be transferred.
    if item.equipment_type == "Q" {
        return Ok(false);
    }

    let is_arrow = item.equipment_type == "R";
    let available = if is_arrow { item.wt } else { item.amount };

    if available < amount {
        return Ok(false);
    }

    if is_arrow {
        // Arrows: match by name, type, power, zr, szyb, poison, cost, magic, ptype.
        let existing: Option<(i32,)> = sqlx::query_as(
            "SELECT id FROM equipment WHERE name = $1 AND type = 'R' AND status = 'U' \
             AND owner = $2 AND power = $3 AND zr = $4 AND szyb = $5 \
             AND poison = $6 AND cost = $7 AND magic = $8 AND ptype = $9",
        )
        .bind(&item.name)
        .bind(recipient_id)
        .bind(item.power)
        .bind(item.zr)
        .bind(item.szyb)
        .bind(item.poison)
        .bind(item.cost)
        .bind(&item.magic)
        .bind(&item.ptype)
        .fetch_optional(pool)
        .await?;

        if let Some((eid,)) = existing {
            sqlx::query("UPDATE equipment SET wt = wt + $1 WHERE id = $2")
                .bind(amount)
                .bind(eid)
                .execute(pool)
                .await?;
        } else {
            sqlx::query(
                "INSERT INTO equipment (owner, name, power, type, cost, zr, wt, minlev, maxwt, \
                 amount, magic, poison, szyb, twohand, repair, ptype) \
                 VALUES ($1, $2, $3, $4, $5, $6, $7, $8, $9, 1, $10, $11, $12, $13, $14, $15)",
            )
            .bind(recipient_id)
            .bind(&item.name)
            .bind(item.power)
            .bind(&item.equipment_type)
            .bind(item.cost)
            .bind(item.zr)
            .bind(amount)
            .bind(item.minlev)
            .bind(amount)
            .bind(&item.magic)
            .bind(item.poison)
            .bind(item.szyb)
            .bind(&item.twohand)
            .bind(item.repair)
            .bind(&item.ptype)
            .execute(pool)
            .await?;
        }

        // Deduct from sender.
        if amount >= item.wt {
            sqlx::query("DELETE FROM equipment WHERE id = $1")
                .bind(item_id)
                .execute(pool)
                .await?;
        } else {
            sqlx::query("UPDATE equipment SET wt = wt - $1 WHERE id = $2")
                .bind(amount)
                .bind(item_id)
                .execute(pool)
                .await?;
        }
    } else {
        // Non-arrow: match by all stat fields.
        let existing: Option<(i32,)> = sqlx::query_as(
            "SELECT id FROM equipment WHERE name = $1 AND wt = $2 AND type = $3 \
             AND status = 'U' AND owner = $4 AND power = $5 AND zr = $6 AND szyb = $7 \
             AND maxwt = $8 AND poison = $9 AND cost = $10 AND minlev = $11 \
             AND magic = $12 AND ptype = $13",
        )
        .bind(&item.name)
        .bind(item.wt)
        .bind(&item.equipment_type)
        .bind(recipient_id)
        .bind(item.power)
        .bind(item.zr)
        .bind(item.szyb)
        .bind(item.maxwt)
        .bind(item.poison)
        .bind(item.cost)
        .bind(item.minlev)
        .bind(&item.magic)
        .bind(&item.ptype)
        .fetch_optional(pool)
        .await?;

        if let Some((eid,)) = existing {
            sqlx::query("UPDATE equipment SET amount = amount + $1 WHERE id = $2")
                .bind(amount)
                .bind(eid)
                .execute(pool)
                .await?;
        } else {
            sqlx::query(
                "INSERT INTO equipment (owner, name, power, type, cost, zr, wt, minlev, maxwt, \
                 amount, magic, poison, szyb, twohand, repair, ptype) \
                 VALUES ($1, $2, $3, $4, $5, $6, $7, $8, $9, $10, $11, $12, $13, $14, $15, $16)",
            )
            .bind(recipient_id)
            .bind(&item.name)
            .bind(item.power)
            .bind(&item.equipment_type)
            .bind(item.cost)
            .bind(item.zr)
            .bind(item.wt)
            .bind(item.minlev)
            .bind(item.maxwt)
            .bind(amount)
            .bind(&item.magic)
            .bind(item.poison)
            .bind(item.szyb)
            .bind(&item.twohand)
            .bind(item.repair)
            .bind(&item.ptype)
            .execute(pool)
            .await?;
        }

        // Deduct from sender.
        if amount >= item.amount {
            sqlx::query("DELETE FROM equipment WHERE id = $1")
                .bind(item_id)
                .execute(pool)
                .await?;
        } else {
            sqlx::query("UPDATE equipment SET amount = amount - $1 WHERE id = $2")
                .bind(amount)
                .bind(item_id)
                .execute(pool)
                .await?;
        }
    }

    Ok(true)
}

/// Transfer a pet to another player.
pub async fn transfer_pet(
    pool: &PgPool,
    sender_id: i32,
    pet_id: i32,
    recipient_id: i32,
) -> Result<bool, sqlx::Error> {
    let res = sqlx::query("UPDATE core SET owner = $1 WHERE id = $2 AND owner = $3")
        .bind(recipient_id)
        .bind(pet_id)
        .bind(sender_id)
        .execute(pool)
        .await?;

    Ok(res.rows_affected() > 0)
}

/// Load a pet row for display.
#[derive(Debug, Clone, sqlx::FromRow)]
pub struct PetRow {
    pub id: i32,
    pub name: String,
    pub corename: String,
}

/// List pets owned by a player.
pub async fn list_player_pets(pool: &PgPool, player_id: i32) -> Result<Vec<PetRow>, sqlx::Error> {
    sqlx::query_as::<_, PetRow>("SELECT id, name, corename FROM core WHERE owner = $1")
        .bind(player_id)
        .fetch_all(pool)
        .await
}
