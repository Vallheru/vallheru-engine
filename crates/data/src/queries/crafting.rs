//! Crafting workshop data queries.
//!
//! SQL access for `smith`, `smith_work`, `jeweller`, `jeweller_work`,
//! `alchemy_mill`, `mill`, `mill_work`, `cores`, `rings`, and `astral`
//! tables used by the workshop handlers.

use sqlx::PgPool;

// =========================================================================
// Row structs
// =========================================================================

/// Row from the `smith` table (plan catalog or player-owned plan).
#[derive(Debug, Clone, sqlx::FromRow)]
pub struct SmithPlanRow {
    pub id: i32,
    pub owner: i32,
    pub name: String,
    #[sqlx(rename = "type")]
    pub item_type: String,
    pub cost: i32,
    pub amount: i32,
    pub level: i16,
    pub lang: String,
    pub twohand: String,
    pub elite: i32,
    pub elitetype: String,
}

/// Row from the `smith_work` table (work in progress).
#[derive(Debug, Clone, sqlx::FromRow)]
pub struct SmithWorkRow {
    pub id: i32,
    pub owner: i32,
    pub name: String,
    pub n_energy: i16,
    pub u_energy: i16,
    pub mineral: String,
    pub elite: i32,
}

/// Row from the `jeweller` table (ring plan catalog or player-owned plan).
#[derive(Debug, Clone, sqlx::FromRow)]
pub struct JewellerPlanRow {
    pub id: i32,
    pub owner: i32,
    pub name: String,
    #[sqlx(rename = "type")]
    pub ring_type: String,
    pub cost: i32,
    pub level: i16,
    pub bonus: i32,
    pub lang: String,
}

/// Row from the `jeweller_work` table.
#[derive(Debug, Clone, sqlx::FromRow)]
pub struct JewellerWorkRow {
    pub id: i32,
    pub owner: i32,
    pub name: String,
    pub n_energy: f64,
    pub u_energy: f64,
    pub bonus: String,
    #[sqlx(rename = "type")]
    pub work_type: String,
}

/// Row from the `alchemy_mill` table (recipe catalog or player-owned).
#[derive(Debug, Clone, sqlx::FromRow)]
pub struct AlchemyRecipeRow {
    pub id: i32,
    pub name: String,
    pub owner: i32,
    pub illani: i32,
    pub illanias: i32,
    pub nutari: i32,
    pub cost: i32,
    pub level: i16,
    pub status: String,
    pub dynallca: i32,
    pub lang: String,
}

/// Row from the `mill` table (bow/arrow plan catalog or player-owned).
#[derive(Debug, Clone, sqlx::FromRow)]
pub struct MillPlanRow {
    pub id: i32,
    pub owner: i32,
    pub name: String,
    #[sqlx(rename = "type")]
    pub item_type: String,
    pub cost: i32,
    pub amount: i32,
    pub level: i16,
    pub lang: String,
    pub twohand: String,
    pub elite: i32,
    pub elitetype: String,
}

/// Row from the `mill_work` table.
#[derive(Debug, Clone, sqlx::FromRow)]
pub struct MillWorkRow {
    pub id: i32,
    pub owner: i32,
    pub name: String,
    pub n_energy: i16,
    pub u_energy: i16,
    pub mineral: String,
    pub elite: i32,
}

/// Row from the `cores` reference catalog table.
#[derive(Debug, Clone, sqlx::FromRow)]
pub struct CoreDefRow {
    pub id: i32,
    pub name: String,
    #[sqlx(rename = "type")]
    pub core_type: String,
    pub power: f64,
    pub defense: f64,
    pub rarity: i16,
    pub descr: String,
    pub lang: String,
}

/// Row from the `core` table (player-owned creatures).
#[derive(Debug, Clone, sqlx::FromRow)]
pub struct PlayerCoreRow {
    pub id: i32,
    pub owner: i32,
    pub name: String,
    #[sqlx(rename = "type")]
    pub core_type: String,
    pub ref_id: i32,
    pub power: f64,
    pub defense: f64,
    pub status: String,
    pub active: String,
    pub corename: String,
    pub gender: String,
    pub wins: i32,
    pub losses: i32,
}

/// Row from the `rings` catalog table.
#[derive(Debug, Clone, sqlx::FromRow)]
pub struct RingShopRow {
    pub id: i32,
    pub name: String,
    pub amount: i32,
}

/// Row from the `astral` table (player astral component storage).
#[derive(Debug, Clone, sqlx::FromRow)]
pub struct AstralRow {
    pub owner: i32,
    pub name: String,
    pub amount: i32,
}

/// Row from the `astral_plans` table.
#[derive(Debug, Clone, sqlx::FromRow)]
pub struct AstralPlanRow {
    pub owner: i32,
    pub name: String,
    pub amount: i32,
    pub location: String,
}

// =========================================================================
// Smith queries
// =========================================================================

/// Load catalog plans (owner = 0) available for purchase, optionally
/// filtered by item type.
pub async fn smith_catalog(
    pool: &PgPool,
    item_type: Option<&str>,
) -> Result<Vec<SmithPlanRow>, sqlx::Error> {
    match item_type {
        Some(t) => sqlx::query_as::<_, SmithPlanRow>(
            "SELECT id, owner, name, type, cost, amount, level, lang, twohand, elite, elitetype \
                 FROM smith WHERE owner = 0 AND type = $1 AND elite = 0 ORDER BY level ASC",
        )
        .bind(t)
        .fetch_all(pool)
        .await,
        None => sqlx::query_as::<_, SmithPlanRow>(
            "SELECT id, owner, name, type, cost, amount, level, lang, twohand, elite, elitetype \
                 FROM smith WHERE owner = 0 AND elite = 0 ORDER BY type ASC, level ASC",
        )
        .fetch_all(pool)
        .await,
    }
}

/// Load elite catalog plans (owner = 0, elite > 0).
pub async fn smith_elite_catalog(pool: &PgPool) -> Result<Vec<SmithPlanRow>, sqlx::Error> {
    sqlx::query_as::<_, SmithPlanRow>(
        "SELECT id, owner, name, type, cost, amount, level, lang, twohand, elite, elitetype \
         FROM smith WHERE owner = 0 AND elite > 0 ORDER BY type ASC, level ASC",
    )
    .fetch_all(pool)
    .await
}

/// Load plans owned by a player.
pub async fn smith_player_plans(
    pool: &PgPool,
    player_id: i32,
) -> Result<Vec<SmithPlanRow>, sqlx::Error> {
    sqlx::query_as::<_, SmithPlanRow>(
        "SELECT id, owner, name, type, cost, amount, level, lang, twohand, elite, elitetype \
         FROM smith WHERE owner = $1 ORDER BY type ASC, level ASC",
    )
    .bind(player_id)
    .fetch_all(pool)
    .await
}

/// Find a specific catalog plan by ID and owner.
pub async fn smith_find_plan(
    pool: &PgPool,
    plan_id: i32,
    owner: i32,
) -> Result<Option<SmithPlanRow>, sqlx::Error> {
    sqlx::query_as::<_, SmithPlanRow>(
        "SELECT id, owner, name, type, cost, amount, level, lang, twohand, elite, elitetype \
         FROM smith WHERE id = $1 AND owner = $2",
    )
    .bind(plan_id)
    .bind(owner)
    .fetch_optional(pool)
    .await
}

/// Check if player already owns a plan with this name.
pub async fn smith_player_has_plan(
    pool: &PgPool,
    player_id: i32,
    plan_name: &str,
) -> Result<bool, sqlx::Error> {
    let row: Option<(i64,)> =
        sqlx::query_as("SELECT COUNT(*) FROM smith WHERE owner = $1 AND name = $2")
            .bind(player_id)
            .bind(plan_name)
            .fetch_optional(pool)
            .await?;
    Ok(row.is_some_and(|r| r.0 > 0))
}

/// Buy (copy) a plan: insert a new row with the player's owner id.
pub async fn smith_buy_plan(
    pool: &PgPool,
    player_id: i32,
    plan: &SmithPlanRow,
) -> Result<(), sqlx::Error> {
    sqlx::query(
        "INSERT INTO smith (owner, name, type, cost, amount, level, lang, twohand, elite, elitetype) \
         VALUES ($1, $2, $3, $4, $5, $6, $7, $8, $9, $10)",
    )
    .bind(player_id)
    .bind(&plan.name)
    .bind(&plan.item_type)
    .bind(plan.cost)
    .bind(plan.amount)
    .bind(plan.level)
    .bind(&plan.lang)
    .bind(&plan.twohand)
    .bind(plan.elite)
    .bind(&plan.elitetype)
    .execute(pool)
    .await?;
    Ok(())
}

/// Load active works for a player.
pub async fn smith_active_works(
    pool: &PgPool,
    player_id: i32,
) -> Result<Vec<SmithWorkRow>, sqlx::Error> {
    sqlx::query_as::<_, SmithWorkRow>(
        "SELECT id, owner, name, n_energy, u_energy, mineral, elite \
         FROM smith_work WHERE owner = $1 ORDER BY id ASC",
    )
    .bind(player_id)
    .fetch_all(pool)
    .await
}

/// Find a specific work-in-progress item.
pub async fn smith_find_work(
    pool: &PgPool,
    work_id: i32,
    player_id: i32,
) -> Result<Option<SmithWorkRow>, sqlx::Error> {
    sqlx::query_as::<_, SmithWorkRow>(
        "SELECT id, owner, name, n_energy, u_energy, mineral, elite \
         FROM smith_work WHERE id = $1 AND owner = $2",
    )
    .bind(work_id)
    .bind(player_id)
    .fetch_optional(pool)
    .await
}

/// Create a new work-in-progress entry.
pub async fn smith_create_work(
    pool: &PgPool,
    player_id: i32,
    name: &str,
    n_energy: i16,
    u_energy: i16,
    mineral: &str,
    elite: i32,
) -> Result<(), sqlx::Error> {
    sqlx::query(
        "INSERT INTO smith_work (owner, name, n_energy, u_energy, mineral, elite) \
         VALUES ($1, $2, $3, $4, $5, $6)",
    )
    .bind(player_id)
    .bind(name)
    .bind(n_energy)
    .bind(u_energy)
    .bind(mineral)
    .bind(elite)
    .execute(pool)
    .await?;
    Ok(())
}

/// Add energy to work-in-progress.
pub async fn smith_add_work_energy(
    pool: &PgPool,
    work_id: i32,
    energy: i16,
) -> Result<(), sqlx::Error> {
    sqlx::query("UPDATE smith_work SET u_energy = u_energy + $1 WHERE id = $2")
        .bind(energy)
        .bind(work_id)
        .execute(pool)
        .await?;
    Ok(())
}

/// Delete a completed work entry.
pub async fn smith_delete_work(pool: &PgPool, work_id: i32) -> Result<(), sqlx::Error> {
    sqlx::query("DELETE FROM smith_work WHERE id = $1")
        .bind(work_id)
        .execute(pool)
        .await?;
    Ok(())
}

// =========================================================================
// Alchemy queries
// =========================================================================

/// Load recipe catalog (owner = 0).
pub async fn alchemy_catalog(pool: &PgPool) -> Result<Vec<AlchemyRecipeRow>, sqlx::Error> {
    sqlx::query_as::<_, AlchemyRecipeRow>(
        "SELECT id, name, owner, illani, illanias, nutari, cost, level, status, dynallca, lang \
         FROM alchemy_mill WHERE owner = 0 ORDER BY level ASC",
    )
    .fetch_all(pool)
    .await
}

/// Load player-owned recipes.
pub async fn alchemy_player_recipes(
    pool: &PgPool,
    player_id: i32,
) -> Result<Vec<AlchemyRecipeRow>, sqlx::Error> {
    sqlx::query_as::<_, AlchemyRecipeRow>(
        "SELECT id, name, owner, illani, illanias, nutari, cost, level, status, dynallca, lang \
         FROM alchemy_mill WHERE owner = $1 ORDER BY level ASC",
    )
    .bind(player_id)
    .fetch_all(pool)
    .await
}

/// Find a specific recipe by ID and owner.
pub async fn alchemy_find_recipe(
    pool: &PgPool,
    recipe_id: i32,
    owner: i32,
) -> Result<Option<AlchemyRecipeRow>, sqlx::Error> {
    sqlx::query_as::<_, AlchemyRecipeRow>(
        "SELECT id, name, owner, illani, illanias, nutari, cost, level, status, dynallca, lang \
         FROM alchemy_mill WHERE id = $1 AND owner = $2",
    )
    .bind(recipe_id)
    .bind(owner)
    .fetch_optional(pool)
    .await
}

/// Check if player already owns a recipe.
pub async fn alchemy_player_has_recipe(
    pool: &PgPool,
    player_id: i32,
    recipe_name: &str,
) -> Result<bool, sqlx::Error> {
    let row: Option<(i64,)> =
        sqlx::query_as("SELECT COUNT(*) FROM alchemy_mill WHERE owner = $1 AND name = $2")
            .bind(player_id)
            .bind(recipe_name)
            .fetch_optional(pool)
            .await?;
    Ok(row.is_some_and(|r| r.0 > 0))
}

/// Buy a recipe (copy from catalog to player).
pub async fn alchemy_buy_recipe(
    pool: &PgPool,
    player_id: i32,
    recipe: &AlchemyRecipeRow,
) -> Result<(), sqlx::Error> {
    sqlx::query(
        "INSERT INTO alchemy_mill (name, owner, illani, illanias, nutari, cost, level, status, dynallca, lang) \
         VALUES ($1, $2, $3, $4, $5, $6, $7, $8, $9, $10)",
    )
    .bind(&recipe.name)
    .bind(player_id)
    .bind(recipe.illani)
    .bind(recipe.illanias)
    .bind(recipe.nutari)
    .bind(recipe.cost)
    .bind(recipe.level)
    .bind(&recipe.status)
    .bind(recipe.dynallca)
    .bind(&recipe.lang)
    .execute(pool)
    .await?;
    Ok(())
}

// =========================================================================
// Mill (lumbermill) queries
// =========================================================================

/// Load bow/arrow catalog (owner = 0, normal plans).
pub async fn mill_catalog(
    pool: &PgPool,
    item_type: Option<&str>,
) -> Result<Vec<MillPlanRow>, sqlx::Error> {
    match item_type {
        Some(t) => sqlx::query_as::<_, MillPlanRow>(
            "SELECT id, owner, name, type, cost, amount, level, lang, twohand, elite, elitetype \
                 FROM mill WHERE owner = 0 AND type = $1 AND elite = 0 ORDER BY level ASC",
        )
        .bind(t)
        .fetch_all(pool)
        .await,
        None => sqlx::query_as::<_, MillPlanRow>(
            "SELECT id, owner, name, type, cost, amount, level, lang, twohand, elite, elitetype \
                 FROM mill WHERE owner = 0 AND elite = 0 ORDER BY type ASC, level ASC",
        )
        .fetch_all(pool)
        .await,
    }
}

/// Load elite bow plans (owner = 0, elite > 0).
pub async fn mill_elite_catalog(pool: &PgPool) -> Result<Vec<MillPlanRow>, sqlx::Error> {
    sqlx::query_as::<_, MillPlanRow>(
        "SELECT id, owner, name, type, cost, amount, level, lang, twohand, elite, elitetype \
         FROM mill WHERE owner = 0 AND elite > 0 ORDER BY type ASC, level ASC",
    )
    .fetch_all(pool)
    .await
}

/// Load plans owned by a player.
pub async fn mill_player_plans(
    pool: &PgPool,
    player_id: i32,
) -> Result<Vec<MillPlanRow>, sqlx::Error> {
    sqlx::query_as::<_, MillPlanRow>(
        "SELECT id, owner, name, type, cost, amount, level, lang, twohand, elite, elitetype \
         FROM mill WHERE owner = $1 ORDER BY type ASC, level ASC",
    )
    .bind(player_id)
    .fetch_all(pool)
    .await
}

/// Find a specific mill plan by ID and owner.
pub async fn mill_find_plan(
    pool: &PgPool,
    plan_id: i32,
    owner: i32,
) -> Result<Option<MillPlanRow>, sqlx::Error> {
    sqlx::query_as::<_, MillPlanRow>(
        "SELECT id, owner, name, type, cost, amount, level, lang, twohand, elite, elitetype \
         FROM mill WHERE id = $1 AND owner = $2",
    )
    .bind(plan_id)
    .bind(owner)
    .fetch_optional(pool)
    .await
}

/// Check if player owns a mill plan by name.
pub async fn mill_player_has_plan(
    pool: &PgPool,
    player_id: i32,
    plan_name: &str,
) -> Result<bool, sqlx::Error> {
    let row: Option<(i64,)> =
        sqlx::query_as("SELECT COUNT(*) FROM mill WHERE owner = $1 AND name = $2")
            .bind(player_id)
            .bind(plan_name)
            .fetch_optional(pool)
            .await?;
    Ok(row.is_some_and(|r| r.0 > 0))
}

/// Buy a mill plan.
pub async fn mill_buy_plan(
    pool: &PgPool,
    player_id: i32,
    plan: &MillPlanRow,
) -> Result<(), sqlx::Error> {
    sqlx::query(
        "INSERT INTO mill (owner, name, type, cost, amount, level, lang, twohand, elite, elitetype) \
         VALUES ($1, $2, $3, $4, $5, $6, $7, $8, $9, $10)",
    )
    .bind(player_id)
    .bind(&plan.name)
    .bind(&plan.item_type)
    .bind(plan.cost)
    .bind(plan.amount)
    .bind(plan.level)
    .bind(&plan.lang)
    .bind(&plan.twohand)
    .bind(plan.elite)
    .bind(&plan.elitetype)
    .execute(pool)
    .await?;
    Ok(())
}

/// Load active mill works for a player.
pub async fn mill_active_works(
    pool: &PgPool,
    player_id: i32,
) -> Result<Vec<MillWorkRow>, sqlx::Error> {
    sqlx::query_as::<_, MillWorkRow>(
        "SELECT id, owner, name, n_energy, u_energy, mineral, elite \
         FROM mill_work WHERE owner = $1 ORDER BY id ASC",
    )
    .bind(player_id)
    .fetch_all(pool)
    .await
}

/// Find a specific mill work.
pub async fn mill_find_work(
    pool: &PgPool,
    work_id: i32,
    player_id: i32,
) -> Result<Option<MillWorkRow>, sqlx::Error> {
    sqlx::query_as::<_, MillWorkRow>(
        "SELECT id, owner, name, n_energy, u_energy, mineral, elite \
         FROM mill_work WHERE id = $1 AND owner = $2",
    )
    .bind(work_id)
    .bind(player_id)
    .fetch_optional(pool)
    .await
}

/// Create mill work entry.
pub async fn mill_create_work(
    pool: &PgPool,
    player_id: i32,
    name: &str,
    n_energy: i16,
    u_energy: i16,
    mineral: &str,
    elite: i32,
) -> Result<(), sqlx::Error> {
    sqlx::query(
        "INSERT INTO mill_work (owner, name, n_energy, u_energy, mineral, elite) \
         VALUES ($1, $2, $3, $4, $5, $6)",
    )
    .bind(player_id)
    .bind(name)
    .bind(n_energy)
    .bind(u_energy)
    .bind(mineral)
    .bind(elite)
    .execute(pool)
    .await?;
    Ok(())
}

/// Add energy to mill work.
pub async fn mill_add_work_energy(
    pool: &PgPool,
    work_id: i32,
    energy: i16,
) -> Result<(), sqlx::Error> {
    sqlx::query("UPDATE mill_work SET u_energy = u_energy + $1 WHERE id = $2")
        .bind(energy)
        .bind(work_id)
        .execute(pool)
        .await?;
    Ok(())
}

/// Delete completed mill work.
pub async fn mill_delete_work(pool: &PgPool, work_id: i32) -> Result<(), sqlx::Error> {
    sqlx::query("DELETE FROM mill_work WHERE id = $1")
        .bind(work_id)
        .execute(pool)
        .await?;
    Ok(())
}

// =========================================================================
// Jeweller queries
// =========================================================================

/// Load jeweller ring plan catalog (owner = 0).
pub async fn jeweller_catalog(pool: &PgPool) -> Result<Vec<JewellerPlanRow>, sqlx::Error> {
    sqlx::query_as::<_, JewellerPlanRow>(
        "SELECT id, owner, name, type, cost, level, bonus, lang \
         FROM jeweller WHERE owner = 0 ORDER BY level ASC",
    )
    .fetch_all(pool)
    .await
}

/// Load player-owned jeweller plans.
pub async fn jeweller_player_plans(
    pool: &PgPool,
    player_id: i32,
) -> Result<Vec<JewellerPlanRow>, sqlx::Error> {
    sqlx::query_as::<_, JewellerPlanRow>(
        "SELECT id, owner, name, type, cost, level, bonus, lang \
         FROM jeweller WHERE owner = $1 ORDER BY level ASC",
    )
    .bind(player_id)
    .fetch_all(pool)
    .await
}

/// Find specific jeweller plan.
pub async fn jeweller_find_plan(
    pool: &PgPool,
    plan_id: i32,
    owner: i32,
) -> Result<Option<JewellerPlanRow>, sqlx::Error> {
    sqlx::query_as::<_, JewellerPlanRow>(
        "SELECT id, owner, name, type, cost, level, bonus, lang \
         FROM jeweller WHERE id = $1 AND owner = $2",
    )
    .bind(plan_id)
    .bind(owner)
    .fetch_optional(pool)
    .await
}

/// Check if player owns a jeweller plan.
pub async fn jeweller_player_has_plan(
    pool: &PgPool,
    player_id: i32,
    plan_name: &str,
) -> Result<bool, sqlx::Error> {
    let row: Option<(i64,)> =
        sqlx::query_as("SELECT COUNT(*) FROM jeweller WHERE owner = $1 AND name = $2")
            .bind(player_id)
            .bind(plan_name)
            .fetch_optional(pool)
            .await?;
    Ok(row.is_some_and(|r| r.0 > 0))
}

/// Buy a jeweller plan.
pub async fn jeweller_buy_plan(
    pool: &PgPool,
    player_id: i32,
    plan: &JewellerPlanRow,
) -> Result<(), sqlx::Error> {
    sqlx::query(
        "INSERT INTO jeweller (owner, name, type, cost, level, bonus, lang) \
         VALUES ($1, $2, $3, $4, $5, $6, $7)",
    )
    .bind(player_id)
    .bind(&plan.name)
    .bind(&plan.ring_type)
    .bind(plan.cost)
    .bind(plan.level)
    .bind(plan.bonus)
    .bind(&plan.lang)
    .execute(pool)
    .await?;
    Ok(())
}

/// Load active jeweller works for a player.
pub async fn jeweller_active_works(
    pool: &PgPool,
    player_id: i32,
) -> Result<Vec<JewellerWorkRow>, sqlx::Error> {
    sqlx::query_as::<_, JewellerWorkRow>(
        "SELECT id, owner, name, n_energy, u_energy, bonus, type \
         FROM jeweller_work WHERE owner = $1 ORDER BY id ASC",
    )
    .bind(player_id)
    .fetch_all(pool)
    .await
}

/// Find a specific jeweller work.
pub async fn jeweller_find_work(
    pool: &PgPool,
    work_id: i32,
    player_id: i32,
) -> Result<Option<JewellerWorkRow>, sqlx::Error> {
    sqlx::query_as::<_, JewellerWorkRow>(
        "SELECT id, owner, name, n_energy, u_energy, bonus, type \
         FROM jeweller_work WHERE id = $1 AND owner = $2",
    )
    .bind(work_id)
    .bind(player_id)
    .fetch_optional(pool)
    .await
}

/// Create jeweller work entry.
pub async fn jeweller_create_work(
    pool: &PgPool,
    player_id: i32,
    name: &str,
    n_energy: f64,
    u_energy: f64,
    bonus: &str,
    work_type: &str,
) -> Result<(), sqlx::Error> {
    sqlx::query(
        "INSERT INTO jeweller_work (owner, name, n_energy, u_energy, bonus, type) \
         VALUES ($1, $2, $3, $4, $5, $6)",
    )
    .bind(player_id)
    .bind(name)
    .bind(n_energy)
    .bind(u_energy)
    .bind(bonus)
    .bind(work_type)
    .execute(pool)
    .await?;
    Ok(())
}

/// Add energy to jeweller work.
pub async fn jeweller_add_work_energy(
    pool: &PgPool,
    work_id: i32,
    energy: f64,
) -> Result<(), sqlx::Error> {
    sqlx::query("UPDATE jeweller_work SET u_energy = u_energy + $1 WHERE id = $2")
        .bind(energy)
        .bind(work_id)
        .execute(pool)
        .await?;
    Ok(())
}

/// Delete completed jeweller work.
pub async fn jeweller_delete_work(pool: &PgPool, work_id: i32) -> Result<(), sqlx::Error> {
    sqlx::query("DELETE FROM jeweller_work WHERE id = $1")
        .bind(work_id)
        .execute(pool)
        .await?;
    Ok(())
}

// =========================================================================
// Core (creature) queries
// =========================================================================

/// Load all creature type definitions.
pub async fn core_definitions(pool: &PgPool) -> Result<Vec<CoreDefRow>, sqlx::Error> {
    sqlx::query_as::<_, CoreDefRow>(
        "SELECT id, name, type, power, defense, rarity, descr, lang \
         FROM cores ORDER BY id ASC",
    )
    .fetch_all(pool)
    .await
}

/// Load creature definitions by type.
pub async fn core_definitions_by_type(
    pool: &PgPool,
    core_type: &str,
) -> Result<Vec<CoreDefRow>, sqlx::Error> {
    sqlx::query_as::<_, CoreDefRow>(
        "SELECT id, name, type, power, defense, rarity, descr, lang \
         FROM cores WHERE type = $1 ORDER BY id ASC",
    )
    .bind(core_type)
    .fetch_all(pool)
    .await
}

/// Find a creature definition by ID.
pub async fn core_find_definition(
    pool: &PgPool,
    core_id: i32,
) -> Result<Option<CoreDefRow>, sqlx::Error> {
    sqlx::query_as::<_, CoreDefRow>(
        "SELECT id, name, type, power, defense, rarity, descr, lang \
         FROM cores WHERE id = $1",
    )
    .bind(core_id)
    .fetch_optional(pool)
    .await
}

/// Load all creatures owned by a player.
pub async fn core_player_creatures(
    pool: &PgPool,
    player_id: i32,
) -> Result<Vec<PlayerCoreRow>, sqlx::Error> {
    sqlx::query_as::<_, PlayerCoreRow>(
        "SELECT id, owner, name, type, ref_id, power, defense, status, active, corename, gender, wins, losses \
         FROM core WHERE owner = $1 ORDER BY id ASC",
    )
    .bind(player_id)
    .fetch_all(pool)
    .await
}

/// Find a specific player creature.
pub async fn core_find_creature(
    pool: &PgPool,
    creature_id: i32,
    player_id: i32,
) -> Result<Option<PlayerCoreRow>, sqlx::Error> {
    sqlx::query_as::<_, PlayerCoreRow>(
        "SELECT id, owner, name, type, ref_id, power, defense, status, active, corename, gender, wins, losses \
         FROM core WHERE id = $1 AND owner = $2",
    )
    .bind(creature_id)
    .bind(player_id)
    .fetch_optional(pool)
    .await
}

/// Find a creature by ID (any owner) — for arena opponents.
pub async fn core_find_any(
    pool: &PgPool,
    creature_id: i32,
) -> Result<Option<PlayerCoreRow>, sqlx::Error> {
    sqlx::query_as::<_, PlayerCoreRow>(
        "SELECT id, owner, name, type, ref_id, power, defense, status, active, corename, gender, wins, losses \
         FROM core WHERE id = $1",
    )
    .bind(creature_id)
    .fetch_optional(pool)
    .await
}

/// Create a new player creature.
#[allow(clippy::too_many_arguments)]
pub async fn core_create_creature(
    pool: &PgPool,
    player_id: i32,
    name: &str,
    core_type: &str,
    ref_id: i32,
    power: f64,
    defense: f64,
    corename: &str,
    gender: &str,
) -> Result<i32, sqlx::Error> {
    let row: (i32,) = sqlx::query_as(
        "INSERT INTO core (owner, name, type, ref_id, power, defense, corename, gender) \
         VALUES ($1, $2, $3, $4, $5, $6, $7, $8) RETURNING id",
    )
    .bind(player_id)
    .bind(name)
    .bind(core_type)
    .bind(ref_id)
    .bind(power)
    .bind(defense)
    .bind(corename)
    .bind(gender)
    .fetch_one(pool)
    .await?;
    Ok(row.0)
}

/// Rename a creature.
pub async fn core_rename(
    pool: &PgPool,
    creature_id: i32,
    player_id: i32,
    new_name: &str,
) -> Result<(), sqlx::Error> {
    sqlx::query("UPDATE core SET corename = $1 WHERE id = $2 AND owner = $3")
        .bind(new_name)
        .bind(creature_id)
        .bind(player_id)
        .execute(pool)
        .await?;
    Ok(())
}

/// Activate/deactivate a creature.
pub async fn core_set_active(
    pool: &PgPool,
    creature_id: i32,
    player_id: i32,
    active: bool,
) -> Result<(), sqlx::Error> {
    let val = if active { "Y" } else { "N" };
    sqlx::query("UPDATE core SET active = $1 WHERE id = $2 AND owner = $3")
        .bind(val)
        .bind(creature_id)
        .bind(player_id)
        .execute(pool)
        .await?;
    Ok(())
}

/// Deactivate all creatures for a player.
pub async fn core_deactivate_all(pool: &PgPool, player_id: i32) -> Result<(), sqlx::Error> {
    sqlx::query("UPDATE core SET active = 'N' WHERE owner = $1")
        .bind(player_id)
        .execute(pool)
        .await?;
    Ok(())
}

/// Release (delete) a creature.
pub async fn core_release(
    pool: &PgPool,
    creature_id: i32,
    player_id: i32,
) -> Result<(), sqlx::Error> {
    sqlx::query("DELETE FROM core WHERE id = $1 AND owner = $2")
        .bind(creature_id)
        .bind(player_id)
        .execute(pool)
        .await?;
    Ok(())
}

/// Transfer creature to another player.
pub async fn core_transfer(
    pool: &PgPool,
    creature_id: i32,
    from_player: i32,
    to_player: i32,
) -> Result<(), sqlx::Error> {
    sqlx::query("UPDATE core SET owner = $1, active = 'N' WHERE id = $2 AND owner = $3")
        .bind(to_player)
        .bind(creature_id)
        .bind(from_player)
        .execute(pool)
        .await?;
    Ok(())
}

/// Train a creature (add to power or defense).
pub async fn core_train(
    pool: &PgPool,
    creature_id: i32,
    player_id: i32,
    power_add: f64,
    defense_add: f64,
) -> Result<(), sqlx::Error> {
    sqlx::query(
        "UPDATE core SET power = power + $1, defense = defense + $2 WHERE id = $3 AND owner = $4",
    )
    .bind(power_add)
    .bind(defense_add)
    .bind(creature_id)
    .bind(player_id)
    .execute(pool)
    .await?;
    Ok(())
}

/// Record arena battle outcome.
pub async fn core_arena_result(
    pool: &PgPool,
    creature_id: i32,
    won: bool,
) -> Result<(), sqlx::Error> {
    if won {
        sqlx::query("UPDATE core SET wins = wins + 1 WHERE id = $1")
            .bind(creature_id)
            .execute(pool)
            .await?;
    } else {
        sqlx::query("UPDATE core SET losses = losses + 1 WHERE id = $1")
            .bind(creature_id)
            .execute(pool)
            .await?;
    }
    Ok(())
}

/// Top creatures by type (for monuments/leaderboard).
pub async fn core_top_by_type(
    pool: &PgPool,
    core_type: &str,
    limit: i32,
) -> Result<Vec<PlayerCoreRow>, sqlx::Error> {
    sqlx::query_as::<_, PlayerCoreRow>(
        "SELECT id, owner, name, type, ref_id, power, defense, status, active, corename, gender, wins, losses \
         FROM core WHERE type = $1 ORDER BY (power + defense) DESC LIMIT $2",
    )
    .bind(core_type)
    .bind(limit)
    .fetch_all(pool)
    .await
}

/// Arena opponent list by creature type (exclude player's own).
pub async fn core_arena_opponents(
    pool: &PgPool,
    core_type: &str,
    player_id: i32,
) -> Result<Vec<PlayerCoreRow>, sqlx::Error> {
    sqlx::query_as::<_, PlayerCoreRow>(
        "SELECT id, owner, name, type, ref_id, power, defense, status, active, corename, gender, wins, losses \
         FROM core WHERE type = $1 AND owner != $2 AND status = 'Alive' AND active = 'Y' \
         ORDER BY (power + defense) ASC",
    )
    .bind(core_type)
    .bind(player_id)
    .fetch_all(pool)
    .await
}

// =========================================================================
// Ring shop queries
// =========================================================================

/// Load all rings in stock (amount > 0).
pub async fn ring_shop_list(pool: &PgPool) -> Result<Vec<RingShopRow>, sqlx::Error> {
    sqlx::query_as::<_, RingShopRow>(
        "SELECT id, name, amount FROM rings WHERE amount > 0 ORDER BY id ASC",
    )
    .fetch_all(pool)
    .await
}

/// Find a ring by ID.
pub async fn ring_shop_find(
    pool: &PgPool,
    ring_id: i32,
) -> Result<Option<RingShopRow>, sqlx::Error> {
    sqlx::query_as::<_, RingShopRow>("SELECT id, name, amount FROM rings WHERE id = $1")
        .bind(ring_id)
        .fetch_optional(pool)
        .await
}

/// Decrement ring stock by 1.
pub async fn ring_shop_decrement(pool: &PgPool, ring_id: i32) -> Result<(), sqlx::Error> {
    sqlx::query("UPDATE rings SET amount = amount - 1 WHERE id = $1 AND amount > 0")
        .bind(ring_id)
        .execute(pool)
        .await?;
    Ok(())
}

// =========================================================================
// Astral queries
// =========================================================================

/// Load player's astral components.
pub async fn astral_load(pool: &PgPool, player_id: i32) -> Result<Vec<AstralRow>, sqlx::Error> {
    sqlx::query_as::<_, AstralRow>(
        "SELECT owner, name, amount FROM astral WHERE owner = $1 ORDER BY name ASC",
    )
    .bind(player_id)
    .fetch_all(pool)
    .await
}

/// Add or create astral component.
pub async fn astral_add(
    pool: &PgPool,
    player_id: i32,
    name: &str,
    amount: i32,
) -> Result<(), sqlx::Error> {
    sqlx::query(
        "INSERT INTO astral (owner, name, amount) VALUES ($1, $2, $3) \
         ON CONFLICT (owner, name) DO UPDATE SET amount = astral.amount + $3",
    )
    .bind(player_id)
    .bind(name)
    .bind(amount)
    .execute(pool)
    .await?;
    Ok(())
}

/// Load player's astral plans.
pub async fn astral_plans_load(
    pool: &PgPool,
    player_id: i32,
) -> Result<Vec<AstralPlanRow>, sqlx::Error> {
    sqlx::query_as::<_, AstralPlanRow>(
        "SELECT owner, name, amount, location FROM astral_plans WHERE owner = $1",
    )
    .bind(player_id)
    .fetch_all(pool)
    .await
}

/// Check if player owns a specific astral plan.
pub async fn astral_has_plan(
    pool: &PgPool,
    player_id: i32,
    plan_name: &str,
    location: &str,
) -> Result<bool, sqlx::Error> {
    let row: Option<(i64,)> = sqlx::query_as(
        "SELECT COUNT(*) FROM astral_plans WHERE owner = $1 AND name = $2 AND location = $3",
    )
    .bind(player_id)
    .bind(plan_name)
    .bind(location)
    .fetch_optional(pool)
    .await?;
    Ok(row.is_some_and(|r| r.0 > 0))
}

// =========================================================================
// Equipment helpers for crafting
// =========================================================================

/// Insert a new crafted equipment item.
#[allow(clippy::too_many_arguments)]
pub async fn insert_crafted_item(
    pool: &PgPool,
    player_id: i32,
    name: &str,
    power: i32,
    item_type: &str,
    cost: i64,
    min_level: i32,
    agility: i32,
    durability: i32,
    speed: i32,
    max_durability: i32,
    two_handed: bool,
    repair_cost: i32,
) -> Result<(), sqlx::Error> {
    let th = if two_handed { "Y" } else { "N" };
    sqlx::query(
        "INSERT INTO equipment (owner, name, power, type, cost, minlev, zr, wt, szyb, maxwt, twohand, repair) \
         VALUES ($1, $2, $3, $4, $5, $6, $7, $8, $9, $10, $11, $12)",
    )
    .bind(player_id)
    .bind(name)
    .bind(power)
    .bind(item_type)
    .bind(cost)
    .bind(min_level)
    .bind(agility)
    .bind(durability)
    .bind(speed)
    .bind(max_durability)
    .bind(th)
    .bind(repair_cost)
    .execute(pool)
    .await?;
    Ok(())
}

/// Find a player-owned item by name, type, and power to stack.
pub async fn find_stackable_item(
    pool: &PgPool,
    player_id: i32,
    name: &str,
    item_type: &str,
    power: i32,
) -> Result<Option<i32>, sqlx::Error> {
    let row: Option<(i32,)> = sqlx::query_as(
        "SELECT id FROM equipment WHERE owner = $1 AND name = $2 AND type = $3 AND power = $4 LIMIT 1",
    )
    .bind(player_id)
    .bind(name)
    .bind(item_type)
    .bind(power)
    .fetch_optional(pool)
    .await?;
    Ok(row.map(|r| r.0))
}

/// Increment amount on an existing equipment item.
pub async fn increment_item_amount(
    pool: &PgPool,
    item_id: i32,
    amount: i32,
) -> Result<(), sqlx::Error> {
    sqlx::query("UPDATE equipment SET amount = amount + $1 WHERE id = $2")
        .bind(amount)
        .bind(item_id)
        .execute(pool)
        .await?;
    Ok(())
}

/// Add or stack a potion for a player.
pub async fn add_or_stack_potion(
    pool: &PgPool,
    player_id: i32,
    name: &str,
    potion_type: &str,
    power: i32,
    amount: i32,
    cost: i64,
) -> Result<(), sqlx::Error> {
    // Try to find existing potion to stack
    let existing: Option<(i32,)> = sqlx::query_as(
        "SELECT id FROM potions WHERE owner = $1 AND name = $2 AND type = $3 AND power = $4 LIMIT 1",
    )
    .bind(player_id)
    .bind(name)
    .bind(potion_type)
    .bind(power)
    .fetch_optional(pool)
    .await?;

    if let Some((id,)) = existing {
        sqlx::query("UPDATE potions SET amount = amount + $1 WHERE id = $2")
            .bind(amount)
            .bind(id)
            .execute(pool)
            .await?;
    } else {
        sqlx::query(
            "INSERT INTO potions (owner, name, type, power, amount, cost) \
             VALUES ($1, $2, $3, $4, $5, $6)",
        )
        .bind(player_id)
        .bind(name)
        .bind(potion_type)
        .bind(power)
        .bind(amount)
        .bind(cost)
        .execute(pool)
        .await?;
    }
    Ok(())
}
