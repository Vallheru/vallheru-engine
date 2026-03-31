//! Queries for player outposts, veterans, and outpost beasts.

use sqlx::PgPool;

// ---------------------------------------------------------------------------
// Transactional multi-step operations (TD-046)
// ---------------------------------------------------------------------------

/// Purchase army units and deduct from global reserves in a single transaction.
pub async fn purchase_army_tx(
    pool: &PgPool,
    outpost_id: i32,
    warriors: i32,
    archers: i32,
    catapults: i32,
    barricades: i32,
    cost: i32,
) -> sqlx::Result<()> {
    let mut tx = pool.begin().await?;

    sqlx::query(
        "UPDATE outposts
         SET gold = gold - $1,
             warriors = warriors + $2,
             archers = archers + $3,
             catapults = catapults + $4,
             barricades = barricades + $5
         WHERE id = $6",
    )
    .bind(cost)
    .bind(warriors)
    .bind(archers)
    .bind(catapults)
    .bind(barricades)
    .bind(outpost_id)
    .execute(&mut *tx)
    .await?;

    let units: &[(&str, i32)] = &[
        ("warriors", warriors),
        ("archers", archers),
        ("catapults", catapults),
        ("barricades", barricades),
    ];
    for &(setting, amount) in units {
        if amount > 0 {
            sqlx::query(
                "UPDATE settings SET value = (CAST(value AS INTEGER) - $1)::TEXT
                 WHERE setting = $2",
            )
            .bind(amount)
            .bind(setting)
            .execute(&mut *tx)
            .await?;
        }
    }

    tx.commit().await?;
    Ok(())
}

/// Upgrade outpost size, deduct platinum and pine in a single transaction.
pub async fn upgrade_size_tx(
    pool: &PgPool,
    outpost_id: i32,
    player_id: i32,
    levels: i32,
    gold_cost: i32,
    plat_cost: i32,
    pine_cost: i32,
) -> sqlx::Result<()> {
    let mut tx = pool.begin().await?;

    sqlx::query("UPDATE outposts SET size = size + $1, gold = gold - $2 WHERE id = $3")
        .bind(levels)
        .bind(gold_cost)
        .bind(outpost_id)
        .execute(&mut *tx)
        .await?;

    sqlx::query("UPDATE players SET platinum = platinum - $1 WHERE id = $2")
        .bind(plat_cost)
        .bind(player_id)
        .execute(&mut *tx)
        .await?;

    sqlx::query("UPDATE minerals SET pine = pine - $1 WHERE owner = $2")
        .bind(pine_cost)
        .bind(player_id)
        .execute(&mut *tx)
        .await?;

    tx.commit().await?;
    Ok(())
}

/// Parameters for building a structure (lair or barracks) with mineral cost.
pub struct BuildStructureParams<'a> {
    pub outpost_id: i32,
    pub player_id: i32,
    pub structure: &'a str,
    pub amount: i32,
    pub gold_cost: i32,
    pub meteor_cost: i32,
    pub other_mineral: &'a str,
    pub other_cost: i32,
}

/// Build a structure and deduct mineral costs in a single transaction.
pub async fn build_structure_tx(pool: &PgPool, p: &BuildStructureParams<'_>) -> sqlx::Result<()> {
    let structure_sql = match p.structure {
        "fence" => "UPDATE outposts SET fence = fence + $1, gold = gold - $2 WHERE id = $3",
        "barracks" => {
            "UPDATE outposts SET barracks = barracks + $1, gold = gold - $2 WHERE id = $3"
        }
        _ => return Ok(()),
    };
    let mineral_sql = match p.other_mineral {
        "crystal" => {
            "UPDATE minerals SET meteor = meteor - $1, crystal = crystal - $2 WHERE owner = $3"
        }
        "adamantium" => {
            "UPDATE minerals SET meteor = meteor - $1, adamantium = adamantium - $2 WHERE owner = $3"
        }
        _ => return Ok(()),
    };

    let mut tx = pool.begin().await?;

    sqlx::query(structure_sql)
        .bind(p.amount)
        .bind(p.gold_cost)
        .bind(p.outpost_id)
        .execute(&mut *tx)
        .await?;

    sqlx::query(mineral_sql)
        .bind(p.meteor_cost)
        .bind(p.other_cost)
        .bind(p.player_id)
        .execute(&mut *tx)
        .await?;

    tx.commit().await?;
    Ok(())
}

/// All mutations from a single combat round, applied atomically.
pub struct CombatRoundMutations {
    pub attacker_id: i32,
    pub att_warriors: i32,
    pub att_archers: i32,
    pub att_catapults: i32,
    pub att_barricades: i32,
    pub att_fatigue: i32,
    pub defender_id: i32,
    pub def_warriors: i32,
    pub def_archers: i32,
    pub def_catapults: i32,
    pub def_barricades: i32,
    pub attacker_gold_delta: i32,
    pub defender_gold_delta: i32,
    pub attacker_morale_delta: f64,
    pub defender_morale_delta: f64,
    pub delete_monster_ids: Vec<i32>,
    pub delete_veteran_ids: Vec<i32>,
}

/// Apply all combat round mutations in a single transaction.
pub async fn apply_combat_round(pool: &PgPool, m: &CombatRoundMutations) -> sqlx::Result<()> {
    let mut tx = pool.begin().await?;

    // Attacker army + fatigue
    sqlx::query(
        "UPDATE outposts
         SET warriors = $1, archers = $2, catapults = $3, barricades = $4, fatigue = $5
         WHERE id = $6",
    )
    .bind(m.att_warriors)
    .bind(m.att_archers)
    .bind(m.att_catapults)
    .bind(m.att_barricades)
    .bind(m.att_fatigue)
    .bind(m.attacker_id)
    .execute(&mut *tx)
    .await?;

    // Defender army
    sqlx::query(
        "UPDATE outposts SET warriors = $1, archers = $2, catapults = $3, barricades = $4
         WHERE id = $5",
    )
    .bind(m.def_warriors)
    .bind(m.def_archers)
    .bind(m.def_catapults)
    .bind(m.def_barricades)
    .bind(m.defender_id)
    .execute(&mut *tx)
    .await?;

    // Gold transfers
    if m.defender_gold_delta != 0 {
        sqlx::query("UPDATE outposts SET gold = gold + $1 WHERE id = $2")
            .bind(m.defender_gold_delta)
            .bind(m.defender_id)
            .execute(&mut *tx)
            .await?;
    }
    if m.attacker_gold_delta != 0 {
        sqlx::query("UPDATE outposts SET gold = gold + $1 WHERE id = $2")
            .bind(m.attacker_gold_delta)
            .bind(m.attacker_id)
            .execute(&mut *tx)
            .await?;
    }

    // Morale
    sqlx::query("UPDATE outposts SET morale = morale + $1 WHERE id = $2")
        .bind(m.attacker_morale_delta)
        .bind(m.attacker_id)
        .execute(&mut *tx)
        .await?;
    sqlx::query("UPDATE outposts SET morale = morale + $1 WHERE id = $2")
        .bind(m.defender_morale_delta)
        .bind(m.defender_id)
        .execute(&mut *tx)
        .await?;

    // Monster/veteran deaths
    for &id in &m.delete_monster_ids {
        sqlx::query("DELETE FROM outpost_monsters WHERE id = $1")
            .bind(id)
            .execute(&mut *tx)
            .await?;
    }
    for &id in &m.delete_veteran_ids {
        sqlx::query("DELETE FROM outpost_veterans WHERE id = $1")
            .bind(id)
            .execute(&mut *tx)
            .await?;
    }

    // Record attack and spend turn
    sqlx::query("UPDATE outposts SET attacks = attacks + 1 WHERE id = $1")
        .bind(m.defender_id)
        .execute(&mut *tx)
        .await?;
    sqlx::query("UPDATE outposts SET turns = turns - 1 WHERE id = $1")
        .bind(m.attacker_id)
        .execute(&mut *tx)
        .await?;

    tx.commit().await?;
    Ok(())
}

/// Item equip parameters for a single veteran slot.
pub struct VeteranEquipItem<'a> {
    pub veteran_id: i32,
    pub owner_id: i32,
    pub item_id: i32,
    pub slot: &'a str,
    pub power_col: &'a str,
    pub item_name: &'a str,
    pub item_power: i32,
    pub is_arrows: bool,
}

/// Consume an equipment item and assign it to a veteran slot in one transaction.
pub async fn equip_veteran_item_tx(pool: &PgPool, p: &VeteranEquipItem<'_>) -> sqlx::Result<()> {
    let (sc, pc) = match (p.slot, p.power_col) {
        ("weapon", "wpower") => ("weapon", "wpower"),
        ("armor", "apower") => ("armor", "apower"),
        ("helm", "hpower") => ("helm", "hpower"),
        ("legs", "lpower") => ("legs", "lpower"),
        ("ring1", "rpower1") => ("ring1", "rpower1"),
        ("ring2", "rpower2") => ("ring2", "rpower2"),
        ("arrows", "opower") => ("arrows", "opower"),
        _ => return Ok(()),
    };

    let mut tx = pool.begin().await?;

    // Consume the item from inventory
    if p.is_arrows {
        sqlx::query("UPDATE equipment SET wt = wt - 20 WHERE id = $1 AND owner = $2 AND wt > 20")
            .bind(p.item_id)
            .bind(p.owner_id)
            .execute(&mut *tx)
            .await?;
        sqlx::query("DELETE FROM equipment WHERE id = $1 AND owner = $2 AND wt <= 20")
            .bind(p.item_id)
            .bind(p.owner_id)
            .execute(&mut *tx)
            .await?;
    } else {
        sqlx::query(
            "UPDATE equipment SET amount = amount - 1
             WHERE id = $1 AND owner = $2 AND amount > 1",
        )
        .bind(p.item_id)
        .bind(p.owner_id)
        .execute(&mut *tx)
        .await?;
        sqlx::query("DELETE FROM equipment WHERE id = $1 AND owner = $2 AND amount <= 1")
            .bind(p.item_id)
            .bind(p.owner_id)
            .execute(&mut *tx)
            .await?;
    }

    // Assign to veteran
    let sql = format!("UPDATE outpost_veterans SET {sc} = $1, {pc} = $2 WHERE id = $3");
    sqlx::query(&sql)
        .bind(p.item_name)
        .bind(p.item_power)
        .bind(p.veteran_id)
        .execute(&mut *tx)
        .await?;

    tx.commit().await?;
    Ok(())
}

// ---------------------------------------------------------------------------
// Row types
// ---------------------------------------------------------------------------

/// Full outpost row.
#[derive(Debug, Clone, sqlx::FromRow)]
pub struct OutpostRow {
    pub id: i32,
    pub owner: i32,
    pub size: i32,
    pub warriors: i32,
    pub archers: i32,
    pub catapults: i32,
    pub barricades: i32,
    pub gold: i32,
    pub turns: i32,
    pub battack: i16,
    pub bdefense: i16,
    pub btax: i16,
    pub blost: i16,
    pub bcost: i16,
    pub fence: i32,
    pub barracks: i32,
    pub fatigue: i32,
    pub morale: f64,
    pub attacks: i16,
}

/// Outpost listing row (for searching).
#[derive(Debug, sqlx::FromRow)]
pub struct OutpostListRow {
    pub id: i32,
    pub size: i32,
    pub owner: i32,
    pub owner_name: String,
    pub tribe: i32,
}

/// Outpost monster (beast) row.
#[derive(Debug, Clone, sqlx::FromRow)]
pub struct OutpostMonsterRow {
    pub id: i32,
    pub outpost: i32,
    pub name: String,
    pub power: i32,
    pub defense: i32,
}

/// Outpost veteran row.
#[derive(Debug, Clone, sqlx::FromRow)]
pub struct OutpostVeteranRow {
    pub id: i32,
    pub outpost: i32,
    pub name: String,
    pub weapon: Option<String>,
    pub wpower: i32,
    pub armor: Option<String>,
    pub apower: i32,
    pub helm: Option<String>,
    pub hpower: i32,
    pub legs: Option<String>,
    pub lpower: i32,
    pub ring1: Option<String>,
    pub rpower1: i32,
    pub ring2: Option<String>,
    pub rpower2: i32,
    pub arrows: Option<String>,
    pub opower: i32,
}

/// Player core/pet row (subset used for outpost assignment).
#[derive(Debug, sqlx::FromRow)]
pub struct CoreRow {
    pub id: i32,
    pub name: String,
    pub power: f64,
    pub defense: f64,
    pub corename: String,
}

/// Available equipment item for veteran assignment.
#[derive(Debug, sqlx::FromRow)]
pub struct EquipForVeteranRow {
    pub id: i32,
    pub name: String,
    pub power: i32,
    pub wt: i32,
    pub amount: i32,
}

// ---------------------------------------------------------------------------
// Outpost CRUD
// ---------------------------------------------------------------------------

/// Find a player's outpost.
pub async fn find_by_owner(pool: &PgPool, owner_id: i32) -> sqlx::Result<Option<OutpostRow>> {
    sqlx::query_as::<_, OutpostRow>("SELECT * FROM outposts WHERE owner = $1")
        .bind(owner_id)
        .fetch_optional(pool)
        .await
}

/// Find an outpost by its ID.
pub async fn find_by_id(pool: &PgPool, outpost_id: i32) -> sqlx::Result<Option<OutpostRow>> {
    sqlx::query_as::<_, OutpostRow>("SELECT * FROM outposts WHERE id = $1")
        .bind(outpost_id)
        .fetch_optional(pool)
        .await
}

/// Find an outpost by owner player ID (for attacking by player ID).
pub async fn find_by_owner_id(pool: &PgPool, owner_id: i32) -> sqlx::Result<Option<OutpostRow>> {
    find_by_owner(pool, owner_id).await
}

/// Create a new outpost for a player.
pub async fn create_outpost(pool: &PgPool, owner_id: i32) -> sqlx::Result<i32> {
    let row: (i32,) = sqlx::query_as("INSERT INTO outposts (owner) VALUES ($1) RETURNING id")
        .bind(owner_id)
        .fetch_one(pool)
        .await?;
    Ok(row.0)
}

/// Update outpost gold (add or subtract).
pub async fn update_gold(pool: &PgPool, outpost_id: i32, delta: i32) -> sqlx::Result<()> {
    sqlx::query("UPDATE outposts SET gold = gold + $1 WHERE id = $2")
        .bind(delta)
        .bind(outpost_id)
        .execute(pool)
        .await?;
    Ok(())
}

/// Update outpost turns (subtract).
pub async fn spend_turns(pool: &PgPool, outpost_id: i32, amount: i32) -> sqlx::Result<()> {
    sqlx::query("UPDATE outposts SET turns = turns - $1 WHERE id = $2")
        .bind(amount)
        .bind(outpost_id)
        .execute(pool)
        .await?;
    Ok(())
}

/// Increment a leadership bonus field by 1.
pub async fn increment_bonus(pool: &PgPool, outpost_id: i32, field: &str) -> sqlx::Result<()> {
    // Only allow known bonus fields to prevent SQL injection.
    let sql = match field {
        "battack" => "UPDATE outposts SET battack = battack + 1 WHERE id = $1",
        "bdefense" => "UPDATE outposts SET bdefense = bdefense + 1 WHERE id = $1",
        "btax" => "UPDATE outposts SET btax = btax + 1 WHERE id = $1",
        "blost" => "UPDATE outposts SET blost = blost + 1 WHERE id = $1",
        "bcost" => "UPDATE outposts SET bcost = bcost + 1 WHERE id = $1",
        _ => return Ok(()),
    };
    sqlx::query(sql).bind(outpost_id).execute(pool).await?;
    Ok(())
}

/// Collect taxes: update gold, turns consumed, fatigue, and morale.
pub async fn collect_taxes(
    pool: &PgPool,
    outpost_id: i32,
    gold_gain: i32,
    turns_spent: i32,
    new_fatigue: i32,
    new_morale: f64,
) -> sqlx::Result<()> {
    sqlx::query(
        "UPDATE outposts
         SET gold = gold + $1,
             turns = turns - $2,
             fatigue = $3,
             morale = $4
         WHERE id = $5",
    )
    .bind(gold_gain)
    .bind(turns_spent)
    .bind(new_fatigue)
    .bind(new_morale)
    .bind(outpost_id)
    .execute(pool)
    .await?;
    Ok(())
}

/// Buy troops or equipment.
pub async fn buy_army(
    pool: &PgPool,
    outpost_id: i32,
    warriors: i32,
    archers: i32,
    catapults: i32,
    barricades: i32,
    cost: i32,
) -> sqlx::Result<()> {
    sqlx::query(
        "UPDATE outposts
         SET gold = gold - $1,
             warriors = warriors + $2,
             archers = archers + $3,
             catapults = catapults + $4,
             barricades = barricades + $5
         WHERE id = $6",
    )
    .bind(cost)
    .bind(warriors)
    .bind(archers)
    .bind(catapults)
    .bind(barricades)
    .bind(outpost_id)
    .execute(pool)
    .await?;
    Ok(())
}

/// Upgrade outpost size.
pub async fn upgrade_size(
    pool: &PgPool,
    outpost_id: i32,
    levels: i32,
    gold_cost: i32,
) -> sqlx::Result<()> {
    sqlx::query("UPDATE outposts SET size = size + $1, gold = gold - $2 WHERE id = $3")
        .bind(levels)
        .bind(gold_cost)
        .bind(outpost_id)
        .execute(pool)
        .await?;
    Ok(())
}

/// Build lairs or barracks.
pub async fn build_structure(
    pool: &PgPool,
    outpost_id: i32,
    field: &str,
    amount: i32,
    gold_cost: i32,
) -> sqlx::Result<()> {
    let sql = match field {
        "fence" => "UPDATE outposts SET fence = fence + $1, gold = gold - $2 WHERE id = $3",
        "barracks" => {
            "UPDATE outposts SET barracks = barracks + $1, gold = gold - $2 WHERE id = $3"
        }
        _ => return Ok(()),
    };
    sqlx::query(sql)
        .bind(amount)
        .bind(gold_cost)
        .bind(outpost_id)
        .execute(pool)
        .await?;
    Ok(())
}

/// List outposts by size range (excluding own), with attack limit < 3.
pub async fn list_by_size_range(
    pool: &PgPool,
    min_size: i32,
    max_size: i32,
    exclude_id: i32,
) -> sqlx::Result<Vec<OutpostListRow>> {
    sqlx::query_as::<_, OutpostListRow>(
        "SELECT o.id, o.size, o.owner, p.username AS owner_name, p.tribe
         FROM outposts o
         JOIN players p ON p.id = o.owner
         WHERE o.size >= $1 AND o.size <= $2
           AND o.id != $3 AND o.attacks < 3
         ORDER BY o.size DESC",
    )
    .bind(min_size)
    .bind(max_size)
    .bind(exclude_id)
    .fetch_all(pool)
    .await
}

/// Set army counts directly (after battle losses).
pub async fn set_army(
    pool: &PgPool,
    outpost_id: i32,
    warriors: i32,
    archers: i32,
    catapults: i32,
    barricades: i32,
) -> sqlx::Result<()> {
    sqlx::query(
        "UPDATE outposts
         SET warriors = $1, archers = $2, catapults = $3, barricades = $4
         WHERE id = $5",
    )
    .bind(warriors)
    .bind(archers)
    .bind(catapults)
    .bind(barricades)
    .bind(outpost_id)
    .execute(pool)
    .await?;
    Ok(())
}

/// Update fatigue after battle.
pub async fn set_fatigue(pool: &PgPool, outpost_id: i32, fatigue: i32) -> sqlx::Result<()> {
    sqlx::query("UPDATE outposts SET fatigue = $1 WHERE id = $2")
        .bind(fatigue)
        .bind(outpost_id)
        .execute(pool)
        .await?;
    Ok(())
}

/// Update morale after battle.
pub async fn adjust_morale(pool: &PgPool, outpost_id: i32, delta: f64) -> sqlx::Result<()> {
    sqlx::query("UPDATE outposts SET morale = morale + $1 WHERE id = $2")
        .bind(delta)
        .bind(outpost_id)
        .execute(pool)
        .await?;
    Ok(())
}

/// Increment attack counter on a defended outpost.
pub async fn increment_attacks(pool: &PgPool, outpost_id: i32) -> sqlx::Result<()> {
    sqlx::query("UPDATE outposts SET attacks = attacks + 1 WHERE id = $1")
        .bind(outpost_id)
        .execute(pool)
        .await?;
    Ok(())
}

// ---------------------------------------------------------------------------
// Veterans
// ---------------------------------------------------------------------------

/// List all veterans for an outpost.
pub async fn list_veterans(pool: &PgPool, outpost_id: i32) -> sqlx::Result<Vec<OutpostVeteranRow>> {
    sqlx::query_as::<_, OutpostVeteranRow>("SELECT * FROM outpost_veterans WHERE outpost = $1")
        .bind(outpost_id)
        .fetch_all(pool)
        .await
}

/// Count veterans for an outpost.
pub async fn count_veterans(pool: &PgPool, outpost_id: i32) -> sqlx::Result<i64> {
    let row: (i64,) = sqlx::query_as("SELECT COUNT(*) FROM outpost_veterans WHERE outpost = $1")
        .bind(outpost_id)
        .fetch_one(pool)
        .await?;
    Ok(row.0)
}

/// Find a veteran by ID, must belong to the given outpost.
pub async fn find_veteran(
    pool: &PgPool,
    veteran_id: i32,
    outpost_id: i32,
) -> sqlx::Result<Option<OutpostVeteranRow>> {
    sqlx::query_as::<_, OutpostVeteranRow>(
        "SELECT * FROM outpost_veterans WHERE id = $1 AND outpost = $2",
    )
    .bind(veteran_id)
    .bind(outpost_id)
    .fetch_optional(pool)
    .await
}

/// Check if a veteran name already exists in the outpost.
pub async fn veteran_name_exists(pool: &PgPool, outpost_id: i32, name: &str) -> sqlx::Result<bool> {
    let row: (bool,) = sqlx::query_as(
        "SELECT EXISTS(SELECT 1 FROM outpost_veterans WHERE outpost = $1 AND name = $2)",
    )
    .bind(outpost_id)
    .bind(name)
    .fetch_one(pool)
    .await?;
    Ok(row.0)
}

/// Create a veteran with a given name and weapon.
pub async fn create_veteran(pool: &PgPool, outpost_id: i32, name: &str) -> sqlx::Result<i32> {
    let row: (i32,) =
        sqlx::query_as("INSERT INTO outpost_veterans (outpost, name) VALUES ($1, $2) RETURNING id")
            .bind(outpost_id)
            .bind(name)
            .fetch_one(pool)
            .await?;
    Ok(row.0)
}

/// Update a veteran's equipment slot.
pub async fn equip_veteran(
    pool: &PgPool,
    veteran_id: i32,
    slot: &str,
    power_col: &str,
    item_name: &str,
    item_power: i32,
) -> sqlx::Result<()> {
    // Whitelist columns to prevent SQL injection.
    let (sc, pc) = match (slot, power_col) {
        ("weapon", "wpower") => ("weapon", "wpower"),
        ("armor", "apower") => ("armor", "apower"),
        ("helm", "hpower") => ("helm", "hpower"),
        ("legs", "lpower") => ("legs", "lpower"),
        ("ring1", "rpower1") => ("ring1", "rpower1"),
        ("ring2", "rpower2") => ("ring2", "rpower2"),
        ("arrows", "opower") => ("arrows", "opower"),
        _ => return Ok(()),
    };
    let sql = format!("UPDATE outpost_veterans SET {sc} = $1, {pc} = $2 WHERE id = $3");
    sqlx::query(&sql)
        .bind(item_name)
        .bind(item_power)
        .bind(veteran_id)
        .execute(pool)
        .await?;
    Ok(())
}

/// Delete a veteran by ID.
pub async fn delete_veteran(pool: &PgPool, veteran_id: i32) -> sqlx::Result<()> {
    sqlx::query("DELETE FROM outpost_veterans WHERE id = $1")
        .bind(veteran_id)
        .execute(pool)
        .await?;
    Ok(())
}

// ---------------------------------------------------------------------------
// Monsters (beasts)
// ---------------------------------------------------------------------------

/// List all monsters for an outpost.
pub async fn list_monsters(pool: &PgPool, outpost_id: i32) -> sqlx::Result<Vec<OutpostMonsterRow>> {
    sqlx::query_as::<_, OutpostMonsterRow>("SELECT * FROM outpost_monsters WHERE outpost = $1")
        .bind(outpost_id)
        .fetch_all(pool)
        .await
}

/// Count monsters for an outpost.
pub async fn count_monsters(pool: &PgPool, outpost_id: i32) -> sqlx::Result<i64> {
    let row: (i64,) = sqlx::query_as("SELECT COUNT(*) FROM outpost_monsters WHERE outpost = $1")
        .bind(outpost_id)
        .fetch_one(pool)
        .await?;
    Ok(row.0)
}

/// Add a monster to an outpost from a core/pet.
pub async fn add_monster(
    pool: &PgPool,
    outpost_id: i32,
    name: &str,
    power: i32,
    defense: i32,
) -> sqlx::Result<()> {
    sqlx::query(
        "INSERT INTO outpost_monsters (outpost, name, power, defense)
         VALUES ($1, $2, $3, $4)",
    )
    .bind(outpost_id)
    .bind(name)
    .bind(power)
    .bind(defense)
    .execute(pool)
    .await?;
    Ok(())
}

/// Delete a monster by ID.
pub async fn delete_monster(pool: &PgPool, monster_id: i32) -> sqlx::Result<()> {
    sqlx::query("DELETE FROM outpost_monsters WHERE id = $1")
        .bind(monster_id)
        .execute(pool)
        .await?;
    Ok(())
}

// ---------------------------------------------------------------------------
// Core (pets) — queries needed for outpost beast assignment
// ---------------------------------------------------------------------------

/// List a player's cores/pets.
pub async fn list_player_cores(pool: &PgPool, owner_id: i32) -> sqlx::Result<Vec<CoreRow>> {
    sqlx::query_as::<_, CoreRow>(
        "SELECT id, name, power, defense, corename FROM core WHERE owner = $1",
    )
    .bind(owner_id)
    .fetch_all(pool)
    .await
}

/// Find a core by ID and check ownership.
pub async fn find_core(
    pool: &PgPool,
    core_id: i32,
    owner_id: i32,
) -> sqlx::Result<Option<CoreRow>> {
    sqlx::query_as::<_, CoreRow>(
        "SELECT id, name, power, defense, corename
         FROM core WHERE id = $1 AND owner = $2",
    )
    .bind(core_id)
    .bind(owner_id)
    .fetch_optional(pool)
    .await
}

/// Delete a core after assigning it to an outpost.
pub async fn delete_core(pool: &PgPool, core_id: i32) -> sqlx::Result<()> {
    sqlx::query("DELETE FROM core WHERE id = $1")
        .bind(core_id)
        .execute(pool)
        .await?;
    Ok(())
}

// ---------------------------------------------------------------------------
// Equipment queries for veteran equipping
// ---------------------------------------------------------------------------

/// List available equipment of a given type for veteran assignment.
pub async fn list_equip_for_veteran(
    pool: &PgPool,
    owner_id: i32,
    eq_type: &str,
) -> sqlx::Result<Vec<EquipForVeteranRow>> {
    let sql = if eq_type == "W" {
        "SELECT id, name, power, wt, amount FROM equipment
         WHERE owner = $1 AND type IN ('W', 'B') AND status = 'U'"
    } else if eq_type == "I" {
        "SELECT id, name, power, wt, amount FROM equipment
         WHERE owner = $1 AND type = 'I' AND status = 'U'
           AND (name LIKE '%siły' OR name LIKE '%zręczności'
                OR name LIKE '%kondycji' OR name LIKE '%szybkości')"
    } else {
        "SELECT id, name, power, wt, amount FROM equipment
         WHERE owner = $1 AND type = $2 AND status = 'U'"
    };
    if eq_type == "W" || eq_type == "I" {
        sqlx::query_as::<_, EquipForVeteranRow>(sql)
            .bind(owner_id)
            .fetch_all(pool)
            .await
    } else {
        sqlx::query_as::<_, EquipForVeteranRow>(sql)
            .bind(owner_id)
            .bind(eq_type)
            .fetch_all(pool)
            .await
    }
}

/// Consume an equipment item (reduce amount or delete if last).
pub async fn consume_equipment(pool: &PgPool, item_id: i32, owner_id: i32) -> sqlx::Result<()> {
    // Try to decrement; if amount hits 0, delete.
    sqlx::query(
        "UPDATE equipment SET amount = amount - 1
         WHERE id = $1 AND owner = $2 AND amount > 1",
    )
    .bind(item_id)
    .bind(owner_id)
    .execute(pool)
    .await?;

    sqlx::query("DELETE FROM equipment WHERE id = $1 AND owner = $2 AND amount <= 1")
        .bind(item_id)
        .bind(owner_id)
        .execute(pool)
        .await?;
    Ok(())
}

/// Consume arrows (reduce wt by 20 or delete if exactly 20).
pub async fn consume_arrows(pool: &PgPool, item_id: i32, owner_id: i32) -> sqlx::Result<()> {
    sqlx::query(
        "UPDATE equipment SET wt = wt - 20
         WHERE id = $1 AND owner = $2 AND wt > 20",
    )
    .bind(item_id)
    .bind(owner_id)
    .execute(pool)
    .await?;

    sqlx::query("DELETE FROM equipment WHERE id = $1 AND owner = $2 AND wt <= 20")
        .bind(item_id)
        .bind(owner_id)
        .execute(pool)
        .await?;
    Ok(())
}

/// Deduct minerals (meteor and crystal/adamantium) for building.
pub async fn deduct_minerals(
    pool: &PgPool,
    owner_id: i32,
    meteor: i32,
    other_field: &str,
    other_amount: i32,
) -> sqlx::Result<()> {
    let sql = match other_field {
        "crystal" => {
            "UPDATE minerals SET meteor = meteor - $1, crystal = crystal - $2 WHERE owner = $3"
        }
        "adamantium" => {
            "UPDATE minerals SET meteor = meteor - $1, adamantium = adamantium - $2 WHERE owner = $3"
        }
        _ => return Ok(()),
    };
    sqlx::query(sql)
        .bind(meteor)
        .bind(other_amount)
        .bind(owner_id)
        .execute(pool)
        .await?;
    Ok(())
}

/// Deduct platinum from a player.
pub async fn deduct_platinum(pool: &PgPool, player_id: i32, amount: i32) -> sqlx::Result<()> {
    sqlx::query("UPDATE players SET platinum = platinum - $1 WHERE id = $2")
        .bind(amount)
        .bind(player_id)
        .execute(pool)
        .await?;
    Ok(())
}

/// Deduct pine from minerals.
pub async fn deduct_pine(pool: &PgPool, owner_id: i32, amount: i32) -> sqlx::Result<()> {
    sqlx::query("UPDATE minerals SET pine = pine - $1 WHERE owner = $2")
        .bind(amount)
        .bind(owner_id)
        .execute(pool)
        .await?;
    Ok(())
}

/// Read army reserves from settings table.
pub async fn army_reserves(pool: &PgPool) -> sqlx::Result<[i32; 4]> {
    let rows: Vec<(String, String)> = sqlx::query_as(
        "SELECT setting, value FROM settings
         WHERE setting IN ('warriors', 'archers', 'catapults', 'barricades')",
    )
    .fetch_all(pool)
    .await?;

    let mut reserves = [0i32; 4];
    for (setting, value) in &rows {
        let v = value.parse::<i32>().unwrap_or(0);
        match setting.as_str() {
            "warriors" => reserves[0] = v,
            "archers" => reserves[1] = v,
            "catapults" => reserves[2] = v,
            "barricades" => reserves[3] = v,
            _ => {}
        }
    }
    Ok(reserves)
}

/// Deduct from global army reserves.
pub async fn deduct_army_reserves(
    pool: &PgPool,
    warriors: i32,
    archers: i32,
    catapults: i32,
    barricades: i32,
) -> sqlx::Result<()> {
    let updates: &[(&str, i32)] = &[
        ("warriors", warriors),
        ("archers", archers),
        ("catapults", catapults),
        ("barricades", barricades),
    ];
    for &(setting, amount) in updates {
        if amount > 0 {
            sqlx::query(
                "UPDATE settings SET value = (CAST(value AS INTEGER) - $1)::TEXT
                 WHERE setting = $2",
            )
            .bind(amount)
            .bind(setting)
            .execute(pool)
            .await?;
        }
    }
    Ok(())
}

/// Get a player's mineral amounts.
#[derive(Debug, sqlx::FromRow)]
pub struct MineralsRow {
    pub pine: i32,
    pub crystal: i32,
    pub adamantium: i32,
    pub meteor: i32,
}

pub async fn get_minerals(pool: &PgPool, owner_id: i32) -> sqlx::Result<Option<MineralsRow>> {
    sqlx::query_as::<_, MineralsRow>(
        "SELECT pine, crystal, adamantium, meteor FROM minerals WHERE owner = $1",
    )
    .bind(owner_id)
    .fetch_optional(pool)
    .await
}

/// Get a player's platinum amount.
pub async fn get_platinum(pool: &PgPool, player_id: i32) -> sqlx::Result<i64> {
    let row: (i64,) = sqlx::query_as("SELECT platinum FROM players WHERE id = $1")
        .bind(player_id)
        .fetch_one(pool)
        .await?;
    Ok(row.0)
}
