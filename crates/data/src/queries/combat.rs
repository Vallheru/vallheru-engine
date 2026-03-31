//! Combat-related data queries.
//!
//! Covers monster loading, battle state tracking (fight field),
//! `PvP` battle logs, exploration resource persistence, and
//! hunter quest settings.

use sqlx::PgPool;
use vallheru_domain::combat::encounter::{Monster, parse_loot};
use vallheru_domain::combat::formulas::MonsterResistance;
use vallheru_domain::item::Element;

// ---------------------------------------------------------------------------
// Row types
// ---------------------------------------------------------------------------

/// A row from the `monsters` catalog table.
#[derive(Debug, Clone, sqlx::FromRow)]
pub struct MonsterRow {
    pub id: i32,
    pub name: String,
    pub level: i32,
    pub hp: i32,
    pub agility: f64,
    pub strength: f64,
    pub speed: f64,
    pub endurance: f64,
    pub location: String,
    pub lootnames: String,
    pub lootchances: String,
    pub description: String,
    pub resistance: String,
    pub dmgtype: String,
}

impl MonsterRow {
    /// Convert to domain `Monster`.
    pub fn into_domain(self) -> Monster {
        let loot = parse_loot(&self.lootnames, &self.lootchances);
        let resistance = MonsterResistance::parse(&self.resistance);
        let dmgtype = Element::from_equipment_code(&self.dmgtype);

        Monster {
            id: self.id,
            name: self.name,
            level: self.level,
            hp: self.hp,
            strength: self.strength,
            agility: self.agility,
            speed: self.speed,
            endurance: self.endurance,
            location: self.location,
            loot,
            resistance,
            dmgtype,
        }
    }
}

/// A row for bestiary display (name + id + location, with description check).
#[derive(Debug, Clone, sqlx::FromRow, serde::Serialize)]
pub struct BestiaryEntry {
    pub id: i32,
    pub name: String,
    pub location: String,
}

/// A row from the `battlelogs` table.
#[derive(Debug, Clone, sqlx::FromRow)]
pub struct BattleLogRow {
    pub id: i32,
    pub pid: i32,
    pub did: i32,
    pub wid: i32,
    pub bdate: i64,
}

/// A `PvP` opponent candidate.
#[derive(Debug, Clone, sqlx::FromRow, serde::Serialize)]
pub struct ArenaOpponent {
    pub id: i32,
    pub username: String,
    pub rank: String,
    pub hp: i32,
    pub max_hp: i32,
    pub location: String,
}

// ---------------------------------------------------------------------------
// Monster queries
// ---------------------------------------------------------------------------

/// Load a single monster by ID.
pub async fn load_monster(pool: &PgPool, monster_id: i32) -> sqlx::Result<Option<MonsterRow>> {
    sqlx::query_as::<_, MonsterRow>(
        "SELECT id, name, level, hp, agility, strength, speed, endurance, \
         location, lootnames, lootchances, description, resistance, dmgtype \
         FROM monsters WHERE id = $1",
    )
    .bind(monster_id)
    .fetch_optional(pool)
    .await
}

/// Load all monsters for a given location.
pub async fn load_monsters_by_location(
    pool: &PgPool,
    location: &str,
) -> sqlx::Result<Vec<MonsterRow>> {
    sqlx::query_as::<_, MonsterRow>(
        "SELECT id, name, level, hp, agility, strength, speed, endurance, \
         location, lootnames, lootchances, description, resistance, dmgtype \
         FROM monsters WHERE location = $1 ORDER BY level",
    )
    .bind(location)
    .fetch_all(pool)
    .await
}

/// Load bestiary entries — monsters that have a non-empty description.
pub async fn load_bestiary(pool: &PgPool) -> sqlx::Result<Vec<BestiaryEntry>> {
    sqlx::query_as::<_, BestiaryEntry>(
        "SELECT id, name, location FROM monsters \
         WHERE description != '' ORDER BY level",
    )
    .fetch_all(pool)
    .await
}

/// Load a monster's description by ID.
pub async fn load_monster_description(
    pool: &PgPool,
    monster_id: i32,
) -> sqlx::Result<Option<(String, String)>> {
    let row: Option<(String, String)> = sqlx::query_as(
        "SELECT name, description FROM monsters WHERE id = $1 AND description != ''",
    )
    .bind(monster_id)
    .fetch_optional(pool)
    .await?;
    Ok(row)
}

// ---------------------------------------------------------------------------
// Player fight state
// ---------------------------------------------------------------------------

/// Set the player's current fight target (monster ID or 0).
pub async fn set_player_fight(pool: &PgPool, player_id: i32, monster_id: i32) -> sqlx::Result<()> {
    sqlx::query("UPDATE players SET fight = $1 WHERE id = $2")
        .bind(monster_id)
        .bind(player_id)
        .execute(pool)
        .await?;
    Ok(())
}

/// Clear the player's fight target (set to 0).
pub async fn clear_player_fight(pool: &PgPool, player_id: i32) -> sqlx::Result<()> {
    set_player_fight(pool, player_id, 0).await
}

/// Apply combat results to a player: update HP, gold, mana.
pub async fn apply_combat_results(
    pool: &PgPool,
    player_id: i32,
    hp_change: i32,
    gold_change: i64,
    mana_change: i32,
) -> sqlx::Result<()> {
    sqlx::query(
        "UPDATE players SET hp = GREATEST(0, hp + $1), \
         credits = credits + $2, pm = GREATEST(0, pm + $3) \
         WHERE id = $4",
    )
    .bind(hp_change)
    .bind(gold_change)
    .bind(mana_change)
    .bind(player_id)
    .execute(pool)
    .await?;
    Ok(())
}

/// Deduct energy from a player (exploration cost).
pub async fn deduct_energy(pool: &PgPool, player_id: i32, amount: f64) -> sqlx::Result<()> {
    sqlx::query("UPDATE players SET energy = GREATEST(0, energy - $1) WHERE id = $2")
        .bind(amount)
        .bind(player_id)
        .execute(pool)
        .await?;
    Ok(())
}

/// Add gold to a player.
pub async fn add_gold(pool: &PgPool, player_id: i32, amount: i64) -> sqlx::Result<()> {
    sqlx::query("UPDATE players SET credits = credits + $1 WHERE id = $2")
        .bind(amount)
        .bind(player_id)
        .execute(pool)
        .await?;
    Ok(())
}

/// Update player's map count.
pub async fn set_player_maps(pool: &PgPool, player_id: i32, maps: i16) -> sqlx::Result<()> {
    sqlx::query("UPDATE players SET maps = $1 WHERE id = $2")
        .bind(maps)
        .bind(player_id)
        .execute(pool)
        .await?;
    Ok(())
}

// ---------------------------------------------------------------------------
// `PvP` battle logs
// ---------------------------------------------------------------------------

/// Insert a `PvP` battle log entry.
pub async fn insert_battle_log(
    pool: &PgPool,
    attacker_id: i32,
    defender_id: i32,
    winner_id: i32,
    timestamp: i64,
) -> sqlx::Result<()> {
    sqlx::query("INSERT INTO battlelogs (pid, did, wid, bdate) VALUES ($1, $2, $3, $4)")
        .bind(attacker_id)
        .bind(defender_id)
        .bind(winner_id)
        .bind(timestamp)
        .execute(pool)
        .await?;
    Ok(())
}

/// Load recent `PvP` opponents (eligible targets in a city).
pub async fn load_arena_opponents(
    pool: &PgPool,
    player_id: i32,
    location: &str,
) -> sqlx::Result<Vec<ArenaOpponent>> {
    sqlx::query_as::<_, ArenaOpponent>(
        "SELECT id, username, rank, hp, max_hp, location FROM players \
         WHERE id != $1 AND location = $2 AND hp > 0 AND immune = false \
         AND rank NOT IN ('Bohater', 'Admin', 'MG') \
         ORDER BY username LIMIT 50",
    )
    .bind(player_id)
    .bind(location)
    .fetch_all(pool)
    .await
}

/// Apply `PvP` result to a player: update wins/losses, HP, gold, `last_killed` fields.
pub async fn apply_pvp_winner(
    pool: &PgPool,
    player_id: i32,
    loser_name: &str,
    gold_reward: i64,
) -> sqlx::Result<()> {
    sqlx::query(
        "UPDATE players SET wins = wins + 1, `last_killed` = $1, \
         credits = credits + $2 WHERE id = $3",
    )
    .bind(loser_name)
    .bind(gold_reward)
    .bind(player_id)
    .execute(pool)
    .await?;
    Ok(())
}

/// Apply `PvP` result to the loser.
pub async fn apply_pvp_loser(
    pool: &PgPool,
    player_id: i32,
    winner_name: &str,
    hp: i32,
) -> sqlx::Result<()> {
    sqlx::query(
        "UPDATE players SET losses = losses + 1, `last_killed`_by = $1, \
         hp = $2 WHERE id = $3",
    )
    .bind(winner_name)
    .bind(hp)
    .bind(player_id)
    .execute(pool)
    .await?;
    Ok(())
}

// ---------------------------------------------------------------------------
// Exploration herb persistence
// ---------------------------------------------------------------------------

/// Add herbs found during exploration.
pub async fn add_herbs(
    pool: &PgPool,
    player_id: i32,
    illani: i32,
    illanias: i32,
    nutari: i32,
    dynallca: i32,
) -> sqlx::Result<()> {
    if illani == 0 && illanias == 0 && nutari == 0 && dynallca == 0 {
        return Ok(());
    }

    // Ensure row exists first.
    sqlx::query("INSERT INTO herbs (gracz) VALUES ($1) ON CONFLICT DO NOTHING")
        .bind(player_id)
        .execute(pool)
        .await?;

    sqlx::query(
        "UPDATE herbs SET illani = illani + $1, illanias = illanias + $2, \
         nutari = nutari + $3, dynallca = dynallca + $4 WHERE gracz = $5",
    )
    .bind(illani)
    .bind(illanias)
    .bind(nutari)
    .bind(dynallca)
    .bind(player_id)
    .execute(pool)
    .await?;
    Ok(())
}

/// Add meteors found during exploration.
pub async fn add_meteors(pool: &PgPool, player_id: i32, amount: i32) -> sqlx::Result<()> {
    if amount == 0 {
        return Ok(());
    }

    sqlx::query("INSERT INTO minerals (owner) VALUES ($1) ON CONFLICT (owner) DO NOTHING")
        .bind(player_id)
        .execute(pool)
        .await?;

    sqlx::query("UPDATE minerals SET meteor = meteor + $1 WHERE owner = $2")
        .bind(amount)
        .bind(player_id)
        .execute(pool)
        .await?;
    Ok(())
}
