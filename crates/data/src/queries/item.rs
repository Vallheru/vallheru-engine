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
               ptype, repair, location \
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
               ptype, repair, location \
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
               ptype, repair, location \
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
               ptype, repair, location \
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
               ptype, repair, location \
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
        "SELECT id, owner, name, type, efect, status, power, amount, cost \
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
        "SELECT id, owner, name, type, efect, status, power, amount, cost \
         FROM potions \
         WHERE owner = 0 \
         ORDER BY type ASC, power ASC",
    )
    .fetch_all(pool)
    .await
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
