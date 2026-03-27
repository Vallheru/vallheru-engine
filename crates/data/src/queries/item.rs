//! Item and inventory query module.
//!
//! SQL access for `equipment`, `spells`, `potions`, `mage_items`, `bows`,
//! and `rings` tables. Row structs stay close to the SQL and are separate
//! from the domain types in `vallheru-domain::item`.

use sqlx::PgPool;
use vallheru_domain::item::{Element, EquipmentStatus, EquipmentType, OwnedEquipment, PoisonType};

// ---------------------------------------------------------------------------
// Row structs
// ---------------------------------------------------------------------------

/// Row from the `equipment` table.
#[derive(Debug, Clone, sqlx::FromRow)]
pub struct EquipmentRow {
    pub id: i32,
    pub owner: i32,
    pub name: String,
    pub power: i32,
    pub status: String,
    #[sqlx(rename = "type")]
    pub equipment_type: String,
    pub cost: i64,
    pub minlev: i32,
    pub zr: i32,
    pub wt: i32,
    pub szyb: i32,
    pub maxwt: i32,
    pub magic: String,
    pub poison: i32,
    pub amount: i32,
    pub twohand: String,
    pub lang: String,
    pub ptype: String,
    pub repair: i32,
    pub location: String,
}

/// Row from the `spells` table (legacy: `czary`).
#[derive(Debug, Clone, sqlx::FromRow)]
pub struct SpellRow {
    pub id: i32,
    pub nazwa: String,
    pub gracz: i32,
    pub cena: i64,
    pub poziom: i32,
    pub typ: String,
    pub obr: f64,
    pub status: String,
    pub element: String,
}

/// Row from the `potions` table.
#[derive(Debug, Clone, sqlx::FromRow)]
pub struct PotionRow {
    pub id: i32,
    pub owner: i32,
    pub name: String,
    #[sqlx(rename = "type")]
    pub potion_type: String,
    pub efect: String,
    pub status: String,
    pub power: i32,
    pub amount: i32,
    pub lang: String,
    pub cost: i64,
}

/// Row from the `mage_items` catalog table.
#[derive(Debug, Clone, sqlx::FromRow)]
pub struct MageItemRow {
    pub id: i32,
    pub name: String,
    pub power: i32,
    #[sqlx(rename = "type")]
    pub item_type: String,
    pub cost: i64,
    pub minlev: i32,
}

/// Row from the `bows` catalog table.
#[derive(Debug, Clone, sqlx::FromRow)]
pub struct BowRow {
    pub id: i32,
    pub name: String,
    pub power: i32,
    #[sqlx(rename = "type")]
    pub bow_type: String,
    pub cost: i64,
    pub minlev: i32,
    pub zr: i32,
    pub szyb: i32,
    pub maxwt: i32,
    pub repair: i32,
}

/// Row from the `rings` catalog table.
#[derive(Debug, Clone, sqlx::FromRow)]
pub struct RingRow {
    pub id: i32,
    pub name: String,
    pub amount: i32,
}

impl EquipmentRow {
    /// Convert to the domain `OwnedEquipment` type.
    pub fn into_domain(self) -> OwnedEquipment {
        OwnedEquipment {
            id: self.id,
            owner_id: self.owner,
            name: self.name,
            power: self.power,
            status: EquipmentStatus::from_db(&self.status).unwrap_or(EquipmentStatus::Unequipped),
            equipment_type: EquipmentType::from_db(&self.equipment_type)
                .unwrap_or(EquipmentType::Other),
            cost: self.cost,
            min_level: self.minlev,
            agility_mod: self.zr,
            durability: self.wt,
            speed_mod: self.szyb,
            max_durability: self.maxwt,
            magic: Element::from_equipment_code(&self.magic),
            poison: self.poison,
            amount: self.amount,
            two_handed: self.twohand == "Y",
            poison_type: PoisonType::from_db(&self.ptype),
            repair_cost: self.repair,
            location: self.location,
        }
    }
}

// ---------------------------------------------------------------------------
// Equipment queries
// ---------------------------------------------------------------------------

/// Load all equipment owned by a player.
pub async fn find_equipment_by_owner(
    pool: &PgPool,
    owner_id: i32,
) -> Result<Vec<EquipmentRow>, sqlx::Error> {
    sqlx::query_as::<_, EquipmentRow>(
        "SELECT id, owner, name, power, status, type, cost, minlev, \
               zr, wt, szyb, maxwt, magic, poison, amount, twohand, \
               lang, ptype, repair, location \
         FROM equipment \
         WHERE owner = $1 \
         ORDER BY minlev ASC, name ASC",
    )
    .bind(owner_id)
    .fetch_all(pool)
    .await
}

/// Load currently equipped items for a player (status = 'E').
pub async fn find_equipped_items(
    pool: &PgPool,
    owner_id: i32,
) -> Result<Vec<EquipmentRow>, sqlx::Error> {
    sqlx::query_as::<_, EquipmentRow>(
        "SELECT id, owner, name, power, status, type, cost, minlev, \
               zr, wt, szyb, maxwt, magic, poison, amount, twohand, \
               lang, ptype, repair, location \
         FROM equipment \
         WHERE owner = $1 AND status = 'E' \
         ORDER BY type ASC",
    )
    .bind(owner_id)
    .fetch_all(pool)
    .await
}

/// Load backpack items for a player (status = 'U') of a given type.
pub async fn find_backpack_items_by_type(
    pool: &PgPool,
    owner_id: i32,
    equipment_type: &str,
) -> Result<Vec<EquipmentRow>, sqlx::Error> {
    sqlx::query_as::<_, EquipmentRow>(
        "SELECT id, owner, name, power, status, type, cost, minlev, \
               zr, wt, szyb, maxwt, magic, poison, amount, twohand, \
               lang, ptype, repair, location \
         FROM equipment \
         WHERE owner = $1 AND type = $2 AND status = 'U' \
         ORDER BY minlev ASC, name ASC",
    )
    .bind(owner_id)
    .bind(equipment_type)
    .fetch_all(pool)
    .await
}

/// Find a specific equipment item by ID.
pub async fn find_equipment_by_id(
    pool: &PgPool,
    item_id: i32,
) -> Result<Option<EquipmentRow>, sqlx::Error> {
    sqlx::query_as::<_, EquipmentRow>(
        "SELECT id, owner, name, power, status, type, cost, minlev, \
               zr, wt, szyb, maxwt, magic, poison, amount, twohand, \
               lang, ptype, repair, location \
         FROM equipment \
         WHERE id = $1",
    )
    .bind(item_id)
    .fetch_optional(pool)
    .await
}

/// Load shop stock items (owner = 0) of a given type.
pub async fn find_shop_items_by_type(
    pool: &PgPool,
    equipment_type: &str,
) -> Result<Vec<EquipmentRow>, sqlx::Error> {
    sqlx::query_as::<_, EquipmentRow>(
        "SELECT id, owner, name, power, status, type, cost, minlev, \
               zr, wt, szyb, maxwt, magic, poison, amount, twohand, \
               lang, ptype, repair, location \
         FROM equipment \
         WHERE owner = 0 AND type = $1 AND status = 'S' \
         ORDER BY minlev ASC, name ASC",
    )
    .bind(equipment_type)
    .fetch_all(pool)
    .await
}

// ---------------------------------------------------------------------------
// Spell queries
// ---------------------------------------------------------------------------

/// Load all spells owned by a player.
pub async fn find_spells_by_owner(
    pool: &PgPool,
    owner_id: i32,
) -> Result<Vec<SpellRow>, sqlx::Error> {
    sqlx::query_as::<_, SpellRow>(
        "SELECT id, nazwa, gracz, cena, poziom, typ, obr, status, element \
         FROM spells \
         WHERE gracz = $1 \
         ORDER BY poziom ASC, nazwa ASC",
    )
    .bind(owner_id)
    .fetch_all(pool)
    .await
}

/// Load spell catalog (all base spells, gracz = 0).
pub async fn find_spell_catalog(pool: &PgPool) -> Result<Vec<SpellRow>, sqlx::Error> {
    sqlx::query_as::<_, SpellRow>(
        "SELECT id, nazwa, gracz, cena, poziom, typ, obr, status, element \
         FROM spells \
         WHERE gracz = 0 \
         ORDER BY poziom ASC, nazwa ASC",
    )
    .fetch_all(pool)
    .await
}

/// Find a specific spell by ID.
pub async fn find_spell_by_id(
    pool: &PgPool,
    spell_id: i32,
) -> Result<Option<SpellRow>, sqlx::Error> {
    sqlx::query_as::<_, SpellRow>(
        "SELECT id, nazwa, gracz, cena, poziom, typ, obr, status, element \
         FROM spells \
         WHERE id = $1",
    )
    .bind(spell_id)
    .fetch_optional(pool)
    .await
}

// ---------------------------------------------------------------------------
// Potion queries
// ---------------------------------------------------------------------------

/// Load all potions owned by a player.
pub async fn find_potions_by_owner(
    pool: &PgPool,
    owner_id: i32,
) -> Result<Vec<PotionRow>, sqlx::Error> {
    sqlx::query_as::<_, PotionRow>(
        "SELECT id, owner, name, type, efect, status, power, amount, lang, cost \
         FROM potions \
         WHERE owner = $1 \
         ORDER BY type ASC, power ASC",
    )
    .bind(owner_id)
    .fetch_all(pool)
    .await
}

/// Load potion catalog (owner = 0).
pub async fn find_potion_catalog(pool: &PgPool) -> Result<Vec<PotionRow>, sqlx::Error> {
    sqlx::query_as::<_, PotionRow>(
        "SELECT id, owner, name, type, efect, status, power, amount, lang, cost \
         FROM potions \
         WHERE owner = 0 \
         ORDER BY type ASC, power ASC",
    )
    .fetch_all(pool)
    .await
}

/// Load shop potions available for purchase (owner = 0, status = 'S').
pub async fn find_shop_potions(pool: &PgPool, lang: &str) -> Result<Vec<PotionRow>, sqlx::Error> {
    sqlx::query_as::<_, PotionRow>(
        "SELECT id, owner, name, type, efect, status, power, amount, lang, cost \
         FROM potions \
         WHERE owner = 0 AND status = 'S' AND lang = $1 \
         ORDER BY power ASC",
    )
    .bind(lang)
    .fetch_all(pool)
    .await
}

/// Find a specific potion by ID.
pub async fn find_potion_by_id(
    pool: &PgPool,
    potion_id: i32,
) -> Result<Option<PotionRow>, sqlx::Error> {
    sqlx::query_as::<_, PotionRow>(
        "SELECT id, owner, name, type, efect, status, power, amount, lang, cost \
         FROM potions \
         WHERE id = $1",
    )
    .bind(potion_id)
    .fetch_optional(pool)
    .await
}

/// Find a player's existing potion stack matching name, power, and status 'K'.
pub async fn find_player_potion_stack(
    pool: &PgPool,
    owner_id: i32,
    name: &str,
    power: i32,
) -> Result<Option<PotionRow>, sqlx::Error> {
    sqlx::query_as::<_, PotionRow>(
        "SELECT id, owner, name, type, efect, status, power, amount, lang, cost \
         FROM potions \
         WHERE owner = $1 AND name = $2 AND power = $3 AND status = 'K'",
    )
    .bind(owner_id)
    .bind(name)
    .bind(power)
    .fetch_optional(pool)
    .await
}

/// Add amount to an existing potion stack.
pub async fn add_to_potion_stack(
    pool: &PgPool,
    potion_id: i32,
    amount: i32,
) -> Result<(), sqlx::Error> {
    sqlx::query("UPDATE potions SET amount = amount + $1 WHERE id = $2")
        .bind(amount)
        .bind(potion_id)
        .execute(pool)
        .await?;
    Ok(())
}

/// Create a new potion entry for a player (purchase from shop).
#[allow(clippy::too_many_arguments)]
pub async fn create_player_potion(
    pool: &PgPool,
    owner_id: i32,
    name: &str,
    efect: &str,
    potion_type: &str,
    power: i32,
    amount: i32,
    resale_cost: i64,
) -> Result<(), sqlx::Error> {
    sqlx::query(
        "INSERT INTO potions (name, owner, efect, type, power, status, amount, cost) \
         VALUES ($1, $2, $3, $4, $5, 'K', $6, $7)",
    )
    .bind(name)
    .bind(owner_id)
    .bind(efect)
    .bind(potion_type)
    .bind(power)
    .bind(amount)
    .bind(resale_cost)
    .execute(pool)
    .await?;
    Ok(())
}

/// Decrease shop potion stock after a purchase.
pub async fn decrease_potion_stock(
    pool: &PgPool,
    potion_id: i32,
    amount: i32,
) -> Result<(), sqlx::Error> {
    sqlx::query("UPDATE potions SET amount = amount - $1 WHERE id = $2")
        .bind(amount)
        .bind(potion_id)
        .execute(pool)
        .await?;
    Ok(())
}

// ---------------------------------------------------------------------------
// Mage item queries
// ---------------------------------------------------------------------------

/// Load the full mage item catalog.
pub async fn find_mage_item_catalog(pool: &PgPool) -> Result<Vec<MageItemRow>, sqlx::Error> {
    sqlx::query_as::<_, MageItemRow>(
        "SELECT id, name, power, type, cost, minlev \
         FROM mage_items \
         ORDER BY type ASC, minlev ASC",
    )
    .fetch_all(pool)
    .await
}

// ---------------------------------------------------------------------------
// Bow queries
// ---------------------------------------------------------------------------

/// Load the full bow catalog.
pub async fn find_bow_catalog(pool: &PgPool) -> Result<Vec<BowRow>, sqlx::Error> {
    sqlx::query_as::<_, BowRow>(
        "SELECT id, name, power, type, cost, minlev, zr, szyb, maxwt, repair \
         FROM bows \
         ORDER BY type ASC, minlev ASC",
    )
    .fetch_all(pool)
    .await
}

// ---------------------------------------------------------------------------
// Ring queries
// ---------------------------------------------------------------------------

/// Load the full ring catalog.
pub async fn find_ring_catalog(pool: &PgPool) -> Result<Vec<RingRow>, sqlx::Error> {
    sqlx::query_as::<_, RingRow>(
        "SELECT id, name, amount \
         FROM rings \
         ORDER BY id ASC",
    )
    .fetch_all(pool)
    .await
}

// ---------------------------------------------------------------------------
// Equipment mutation queries
// ---------------------------------------------------------------------------

/// Set an item's status to 'E' (equipped). Only works on items owned by the player
/// that are currently in status 'U' (backpack).
pub async fn equip_item(pool: &PgPool, item_id: i32, owner_id: i32) -> Result<bool, sqlx::Error> {
    let result = sqlx::query(
        "UPDATE equipment SET status = 'E' \
         WHERE id = $1 AND owner = $2 AND status = 'U'",
    )
    .bind(item_id)
    .bind(owner_id)
    .execute(pool)
    .await?;
    Ok(result.rows_affected() > 0)
}

/// Set an item's status to 'U' (backpack). Only works on items owned by the player
/// that are currently in status 'E' (equipped).
pub async fn unequip_item(pool: &PgPool, item_id: i32, owner_id: i32) -> Result<bool, sqlx::Error> {
    let result = sqlx::query(
        "UPDATE equipment SET status = 'U' \
         WHERE id = $1 AND owner = $2 AND status = 'E'",
    )
    .bind(item_id)
    .bind(owner_id)
    .execute(pool)
    .await?;
    Ok(result.rows_affected() > 0)
}

/// Sell one unit of an item. If the item has amount > 1, decrement; otherwise delete.
/// Returns the sale price on success.
pub async fn sell_one_item(
    pool: &PgPool,
    item_id: i32,
    owner_id: i32,
) -> Result<Option<i64>, sqlx::Error> {
    // Load the item first to validate ownership and get cost.
    let item = find_equipment_by_id(pool, item_id).await?;
    let Some(item) = item else {
        return Ok(None);
    };
    if item.owner != owner_id || item.status != "U" {
        return Ok(None);
    }

    // Arrows: sale price is per-shot × remaining shots
    let sale_price = if item.equipment_type == "R" {
        let per_shot = item.cost / 100;
        (per_shot * i64::from(item.wt)).max(1)
    } else {
        item.cost
    };

    if item.amount > 1 {
        sqlx::query("UPDATE equipment SET amount = amount - 1 WHERE id = $1")
            .bind(item_id)
            .execute(pool)
            .await?;
    } else {
        sqlx::query("DELETE FROM equipment WHERE id = $1 AND owner = $2")
            .bind(item_id)
            .bind(owner_id)
            .execute(pool)
            .await?;
    }

    // Credit the player
    sqlx::query("UPDATE players SET credits = credits + $1 WHERE id = $2")
        .bind(sale_price)
        .bind(owner_id)
        .execute(pool)
        .await?;

    Ok(Some(sale_price))
}

/// Repair an item to full durability in exchange for gold. Returns the repair cost,
/// or `None` if the item doesn't exist, isn't owned by the player, or doesn't need repair.
pub async fn repair_item(
    pool: &PgPool,
    item_id: i32,
    owner_id: i32,
) -> Result<Option<i64>, sqlx::Error> {
    let item = find_equipment_by_id(pool, item_id).await?;
    let Some(item) = item else {
        return Ok(None);
    };
    if item.owner != owner_id || item.status != "U" {
        return Ok(None);
    }
    // Types without durability don't need repair
    if item.equipment_type == "R"
        || item.equipment_type == "I"
        || item.equipment_type == "O"
        || item.equipment_type == "Q"
        || item.equipment_type == "P"
    {
        return Ok(None);
    }
    if item.wt >= item.maxwt {
        return Ok(None); // already at full durability
    }

    let ratio = 1.0 - (f64::from(item.wt) / f64::from(item.maxwt));
    #[allow(clippy::cast_possible_truncation)]
    let repair_cost = (f64::from(item.repair) * ratio).ceil() as i64;

    // Check player has enough gold
    let credits: (i32,) = sqlx::query_as("SELECT credits FROM players WHERE id = $1")
        .bind(owner_id)
        .fetch_one(pool)
        .await?;
    if i64::from(credits.0) < repair_cost {
        return Ok(None);
    }

    // Repair the item and deduct gold
    sqlx::query("UPDATE equipment SET wt = maxwt WHERE id = $1")
        .bind(item_id)
        .execute(pool)
        .await?;
    sqlx::query("UPDATE players SET credits = credits - $1 WHERE id = $2")
        .bind(repair_cost)
        .bind(owner_id)
        .execute(pool)
        .await?;

    Ok(Some(repair_cost))
}

// ---------------------------------------------------------------------------
// Shop purchase queries
// ---------------------------------------------------------------------------

/// Load shop equipment items filtered by type and lang.
pub async fn find_shop_items_by_type_and_lang(
    pool: &PgPool,
    equipment_type: &str,
    lang: &str,
) -> Result<Vec<EquipmentRow>, sqlx::Error> {
    sqlx::query_as::<_, EquipmentRow>(
        "SELECT id, owner, name, power, status, type, cost, minlev, \
               zr, wt, szyb, maxwt, magic, poison, amount, twohand, \
               lang, ptype, repair, location \
         FROM equipment \
         WHERE owner = 0 AND type = $1 AND status = 'S' AND lang = $2 \
         ORDER BY cost ASC",
    )
    .bind(equipment_type)
    .bind(lang)
    .fetch_all(pool)
    .await
}

/// Find a specific bow catalog entry by ID.
pub async fn find_bow_by_id(pool: &PgPool, bow_id: i32) -> Result<Option<BowRow>, sqlx::Error> {
    sqlx::query_as::<_, BowRow>(
        "SELECT id, name, power, type, cost, minlev, zr, szyb, maxwt, repair \
         FROM bows \
         WHERE id = $1",
    )
    .bind(bow_id)
    .fetch_optional(pool)
    .await
}

/// Buy a shop equipment item. Applies 75% resale price, stacks if identical exists.
pub async fn buy_shop_equipment(
    pool: &PgPool,
    shop_item: &EquipmentRow,
    owner_id: i32,
) -> Result<(), sqlx::Error> {
    let new_cost = (shop_item.cost * 3 + 3) / 4; // ceil(cost * 0.75)

    let existing: Option<(i32,)> = sqlx::query_as(
        "SELECT id FROM equipment \
         WHERE name = $1 AND wt = $2 AND type = $3 AND status = 'U' \
               AND owner = $4 AND power = $5 AND zr = $6 AND szyb = $7 \
               AND maxwt = $8 AND poison = 0 AND cost = $9",
    )
    .bind(&shop_item.name)
    .bind(shop_item.wt)
    .bind(&shop_item.equipment_type)
    .bind(owner_id)
    .bind(shop_item.power)
    .bind(shop_item.zr)
    .bind(shop_item.szyb)
    .bind(shop_item.maxwt)
    .bind(new_cost)
    .fetch_optional(pool)
    .await?;

    if let Some((existing_id,)) = existing {
        sqlx::query("UPDATE equipment SET amount = amount + 1 WHERE id = $1")
            .bind(existing_id)
            .execute(pool)
            .await?;
    } else {
        sqlx::query(
            "INSERT INTO equipment (owner, name, power, type, cost, zr, wt, minlev, maxwt, \
             amount, magic, szyb, lang, repair, status) \
             VALUES ($1, $2, $3, $4, $5, $6, $7, $8, $9, 1, $10, $11, $12, $13, 'U')",
        )
        .bind(owner_id)
        .bind(&shop_item.name)
        .bind(shop_item.power)
        .bind(&shop_item.equipment_type)
        .bind(new_cost)
        .bind(shop_item.zr)
        .bind(shop_item.wt)
        .bind(shop_item.minlev)
        .bind(shop_item.maxwt)
        .bind(&shop_item.magic)
        .bind(shop_item.szyb)
        .bind(&shop_item.lang)
        .bind(shop_item.repair)
        .execute(pool)
        .await?;
    }

    // Deduct gold
    sqlx::query("UPDATE players SET credits = credits - $1 WHERE id = $2")
        .bind(shop_item.cost)
        .bind(owner_id)
        .execute(pool)
        .await?;

    Ok(())
}

/// Buy a bow from the catalog. Applies 75% resale price, stacks if identical exists.
pub async fn buy_bow(pool: &PgPool, bow: &BowRow, owner_id: i32) -> Result<(), sqlx::Error> {
    let new_cost = (bow.cost * 3 + 3) / 4; // ceil(cost * 0.75)

    let existing: Option<(i32,)> = sqlx::query_as(
        "SELECT id FROM equipment \
         WHERE name = $1 AND wt = $2 AND type = 'B' AND status = 'U' \
               AND owner = $3 AND power = $4 AND zr = $5 AND szyb = $6 \
               AND maxwt = $7 AND cost = $8",
    )
    .bind(&bow.name)
    .bind(bow.maxwt)
    .bind(owner_id)
    .bind(bow.power)
    .bind(bow.zr)
    .bind(bow.szyb)
    .bind(bow.maxwt)
    .bind(new_cost)
    .fetch_optional(pool)
    .await?;

    if let Some((existing_id,)) = existing {
        sqlx::query("UPDATE equipment SET amount = amount + 1 WHERE id = $1")
            .bind(existing_id)
            .execute(pool)
            .await?;
    } else {
        sqlx::query(
            "INSERT INTO equipment (owner, name, power, type, cost, zr, wt, minlev, maxwt, \
             amount, szyb, twohand, repair, status) \
             VALUES ($1, $2, $3, 'B', $4, $5, $6, $7, $8, 1, $9, 'Y', $10, 'U')",
        )
        .bind(owner_id)
        .bind(&bow.name)
        .bind(bow.power)
        .bind(new_cost)
        .bind(bow.zr)
        .bind(bow.maxwt)
        .bind(bow.minlev)
        .bind(bow.maxwt)
        .bind(bow.szyb)
        .bind(bow.repair)
        .execute(pool)
        .await?;
    }

    // Deduct gold
    sqlx::query("UPDATE players SET credits = credits - $1 WHERE id = $2")
        .bind(bow.cost)
        .bind(owner_id)
        .execute(pool)
        .await?;

    Ok(())
}

/// Buy arrows from the catalog. Arrow quantity is measured by wt/maxwt.
pub async fn buy_arrows(
    pool: &PgPool,
    bow: &BowRow,
    owner_id: i32,
    arrow_count: i32,
    total_cost: i64,
) -> Result<(), sqlx::Error> {
    let new_cost = (bow.cost * 3 + 3) / 4; // ceil(cost * 0.75)

    let existing: Option<(i32,)> = sqlx::query_as(
        "SELECT id FROM equipment \
         WHERE name = $1 AND owner = $2 AND status = 'U' AND cost = $3",
    )
    .bind(&bow.name)
    .bind(owner_id)
    .bind(new_cost)
    .fetch_optional(pool)
    .await?;

    if let Some((existing_id,)) = existing {
        sqlx::query("UPDATE equipment SET wt = wt + $1, maxwt = maxwt + $1 WHERE id = $2")
            .bind(arrow_count)
            .bind(existing_id)
            .execute(pool)
            .await?;
    } else {
        sqlx::query(
            "INSERT INTO equipment (owner, name, power, cost, wt, szyb, minlev, maxwt, type, status) \
             VALUES ($1, $2, $3, $4, $5, $6, $7, $8, $9, 'U')",
        )
        .bind(owner_id)
        .bind(&bow.name)
        .bind(bow.power)
        .bind(new_cost)
        .bind(arrow_count)
        .bind(bow.szyb)
        .bind(bow.minlev)
        .bind(arrow_count)
        .bind(&bow.bow_type)
        .execute(pool)
        .await?;
    }

    // Deduct gold
    sqlx::query("UPDATE players SET credits = credits - $1 WHERE id = $2")
        .bind(total_cost)
        .bind(owner_id)
        .execute(pool)
        .await?;

    Ok(())
}

// ---------------------------------------------------------------------------
// Spell mutation queries
// ---------------------------------------------------------------------------

/// Activate a battle or defense spell (set status to 'E').
/// First deactivates any other spell of the same type for this player.
pub async fn activate_spell(
    pool: &PgPool,
    spell_id: i32,
    owner_id: i32,
) -> Result<bool, sqlx::Error> {
    let spell = find_spell_by_id(pool, spell_id).await?;
    let Some(spell) = spell else {
        return Ok(false);
    };
    if spell.gracz != owner_id || spell.status != "U" {
        return Ok(false);
    }

    // Deactivate currently active spell of the same type
    sqlx::query(
        "UPDATE spells SET status = 'U' \
         WHERE gracz = $1 AND typ = $2 AND status = 'E'",
    )
    .bind(owner_id)
    .bind(&spell.typ)
    .execute(pool)
    .await?;

    // Activate the chosen spell
    let result = sqlx::query(
        "UPDATE spells SET status = 'E' \
         WHERE id = $1 AND gracz = $2",
    )
    .bind(spell_id)
    .bind(owner_id)
    .execute(pool)
    .await?;

    Ok(result.rows_affected() > 0)
}

/// Deactivate a spell (set status from 'E' to 'U').
pub async fn deactivate_spell(
    pool: &PgPool,
    spell_id: i32,
    owner_id: i32,
) -> Result<bool, sqlx::Error> {
    let result = sqlx::query(
        "UPDATE spells SET status = 'U' \
         WHERE id = $1 AND gracz = $2 AND status = 'E'",
    )
    .bind(spell_id)
    .bind(owner_id)
    .execute(pool)
    .await?;

    Ok(result.rows_affected() > 0)
}
