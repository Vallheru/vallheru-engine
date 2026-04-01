//! Player loading and persistence queries.
//!
//! Provides a flat `PlayerRow` struct (DB-coupled via `sqlx::FromRow`) and
//! conversion to the domain `Player` type. Sub-models (settings, stats,
//! skills, bonuses) are loaded from their normalized tables.

use sqlx::PgPool;
use vallheru_domain::player::{
    Player, Rank, bonuses::PlayerBonus, settings::PlayerSettings, skills::PlayerSkill,
    stats::PlayerStat,
};

// ---------------------------------------------------------------------------
// Row types (DB-coupled)
// ---------------------------------------------------------------------------

/// Full player row as stored in the `players` table.
///
/// Field names match the database column names so `sqlx::FromRow` works
/// without manual column mapping.
#[derive(Debug, sqlx::FromRow)]
#[allow(clippy::struct_excessive_bools)]
pub struct PlayerRow {
    pub id: i32,
    pub username: String,
    pub email: String,
    pub rank: String,
    pub credits: i32,
    pub energy: f64,
    pub max_energy: f64,
    pub ap: i32,
    pub wins: i32,
    pub losses: i32,
    pub last_killed: String,
    pub last_killed_by: String,
    pub platinum: i32,
    pub age: i32,
    pub logins: i32,
    pub hp: i32,
    pub max_hp: i32,
    pub bank: i32,
    pub pm: i32, // "mana" in domain
    pub last_page_visit: i64,
    pub current_page: String,
    pub ip: String,
    pub tribe_id: i32,
    pub profile: String,
    pub referrals: i32,
    pub core_pass: bool,
    pub fight: i32,
    pub trains: i32,
    pub race: String,
    pub class: String,
    pub pw: i32,
    pub immune: bool,
    pub location: String,
    pub messenger: String,
    pub avatar: String,
    pub tribe_rank: String,
    pub deity: Option<String>,
    pub maps: i16,
    pub resting: bool,
    pub crime: i32,
    pub gender: Option<String>,
    pub bridge: bool,
    pub temp: i32,
    pub forum_time: i64,
    pub tforum_time: i64,
    pub bless: String,
    pub bless_value: i32,
    pub antidote: Option<String>,
    pub freeze: i16,
    pub house_rest: bool,
    pub poll: bool,
    pub astral_crime: bool,
    pub change_deity: i32,
    pub vallars: i32,
    pub newbie: i16,
    pub roleplay: String,
    pub ooc: String,
    pub short_rpg: String,
    pub craft_mission: i16,
    pub mpoints: i32,
    pub room: i32,
    pub chapter: i16,
    pub craft_skill: String,
    pub chat_times: String,
    pub ring_invite: i32,
    pub tribe_invite: i32,
    pub team_id: i32,
    pub reputation: i32,
    // Normalized JSONB settings column.
    pub settings: serde_json::Value,
}

/// Row from `player_stats`.
#[derive(Debug, sqlx::FromRow)]
pub struct StatRow {
    pub stat_key: String,
    pub label: String,
    pub base: i32,
    pub trained: i32,
    pub modified: i32,
    pub xp: i32,
}

/// Row from `player_skills`.
#[derive(Debug, sqlx::FromRow)]
pub struct SkillRow {
    pub skill_key: String,
    pub label: String,
    pub level: i32,
    pub xp: i32,
}

/// Row from `player_bonuses`.
#[derive(Debug, sqlx::FromRow)]
pub struct BonusRow {
    pub id: i32,
    pub catalog_id: i32,
    pub bonus_name: String,
    pub value: i32,
    pub duration: i32,
}

// ---------------------------------------------------------------------------
// Row → domain conversions
// ---------------------------------------------------------------------------

/// Convert a `PlayerRow` to the domain `Player`.
pub fn player_from_row(row: PlayerRow) -> Player {
    Player {
        id: row.id,
        username: row.username,
        email: row.email,
        rank: Rank::from_db(&row.rank),
        credits: row.credits,
        energy: row.energy,
        max_energy: row.max_energy,
        ap: row.ap,
        wins: row.wins,
        losses: row.losses,
        last_killed: row.last_killed,
        last_killed_by: row.last_killed_by,
        platinum: row.platinum,
        age: row.age,
        logins: row.logins,
        hp: row.hp,
        max_hp: row.max_hp,
        bank: row.bank,
        mana: row.pm,
        last_page_visit: row.last_page_visit,
        current_page: row.current_page,
        ip: row.ip,
        tribe_id: row.tribe_id,
        profile: row.profile,
        referrals: row.referrals,
        core_pass: row.core_pass,
        fight: row.fight,
        trains: row.trains,
        race: row.race,
        class: row.class,
        pw: row.pw,
        immune: row.immune,
        location: row.location,
        messenger: row.messenger,
        avatar: row.avatar,
        tribe_rank: row.tribe_rank,
        deity: row.deity,
        maps: row.maps,
        resting: row.resting,
        crime: row.crime,
        gender: row.gender,
        bridge: row.bridge,
        temp: row.temp,
        forum_time: row.forum_time,
        tforum_time: row.tforum_time,
        bless: row.bless,
        bless_value: row.bless_value,
        antidote: row.antidote,
        freeze: row.freeze,
        house_rest: row.house_rest,
        poll: row.poll,
        astral_crime: row.astral_crime,
        change_deity: row.change_deity,
        vallars: row.vallars,
        newbie: row.newbie,
        roleplay: row.roleplay,
        ooc: row.ooc,
        short_rpg: row.short_rpg,
        craft_mission: row.craft_mission,
        mpoints: row.mpoints,
        room: row.room,
        chapter: row.chapter,
        craft_skill: row.craft_skill,
        chat_times: row.chat_times,
        ring_invite: row.ring_invite,
        tribe_invite: row.tribe_invite,
        team_id: row.team_id,
        reputation: row.reputation,
    }
}

/// Resolve player settings from the JSONB column.
pub fn settings_from_row(row: &PlayerRow) -> PlayerSettings {
    if !row.settings.is_null()
        && row.settings != serde_json::Value::Object(serde_json::Map::default())
    {
        match serde_json::from_value::<PlayerSettings>(row.settings.clone()) {
            Ok(s) => return s,
            Err(e) => {
                tracing::warn!(
                    player_id = row.id,
                    error = %e,
                    "failed to parse JSONB settings, using defaults"
                );
            }
        }
    }
    PlayerSettings::default()
}

fn stats_from_rows(rows: Vec<StatRow>) -> Vec<PlayerStat> {
    rows.into_iter()
        .map(|r| PlayerStat {
            stat_key: r.stat_key,
            label: r.label,
            base: r.base,
            trained: r.trained,
            modified: r.modified,
            xp: r.xp,
        })
        .collect()
}

fn skills_from_rows(rows: Vec<SkillRow>) -> Vec<PlayerSkill> {
    rows.into_iter()
        .map(|r| PlayerSkill {
            skill_key: r.skill_key,
            label: r.label,
            level: r.level,
            xp: r.xp,
        })
        .collect()
}

fn bonuses_from_rows(rows: Vec<BonusRow>) -> Vec<PlayerBonus> {
    rows.into_iter()
        .map(|r| PlayerBonus {
            id: r.id,
            catalog_id: r.catalog_id,
            bonus_name: r.bonus_name,
            value: r.value,
            duration: r.duration,
        })
        .collect()
}

// ---------------------------------------------------------------------------
// SQL constants
// ---------------------------------------------------------------------------

/// All columns from the players table in a deterministic order for SELECT.
const PLAYER_COLUMNS: &str = r"
    id, username, email, rank, credits, energy, max_energy, ap,
    wins, losses, last_killed, last_killed_by, platinum, age, logins,
    hp, max_hp, bank, pm, last_page_visit, current_page, ip,
    tribe_id, profile, referrals, core_pass, fight, trains, race,
    class, pw, immune, location, messenger, avatar, tribe_rank,
    deity, maps, resting, crime, gender, bridge, temp, forum_time,
    tforum_time, bless, bless_value, antidote, freeze, house_rest,
    poll, astral_crime, change_deity, vallars, newbie, roleplay,
    ooc, short_rpg, craft_mission, mpoints, room, chapter,
    craft_skill, chat_times, ring_invite, tribe_invite, team_id,
    reputation, settings
";

// ---------------------------------------------------------------------------
// Queries
// ---------------------------------------------------------------------------

/// Load a player row by ID. Returns `None` if not found.
pub async fn find_player_by_id(
    pool: &PgPool,
    player_id: i32,
) -> Result<Option<PlayerRow>, sqlx::Error> {
    let sql = format!("SELECT {PLAYER_COLUMNS} FROM players WHERE id = $1");
    sqlx::query_as::<_, PlayerRow>(&sql)
        .bind(player_id)
        .fetch_optional(pool)
        .await
}

/// Load a player row by username (case-insensitive). Returns `None` if not found.
pub async fn find_player_by_username(
    pool: &PgPool,
    username: &str,
) -> Result<Option<PlayerRow>, sqlx::Error> {
    let sql = format!("SELECT {PLAYER_COLUMNS} FROM players WHERE LOWER(username) = LOWER($1)");
    sqlx::query_as::<_, PlayerRow>(&sql)
        .bind(username)
        .fetch_optional(pool)
        .await
}

/// Load stats from the normalized `player_stats` table.
pub async fn load_stats(pool: &PgPool, player_id: i32) -> Result<Vec<PlayerStat>, sqlx::Error> {
    let rows = sqlx::query_as::<_, StatRow>(
        "SELECT stat_key, label, base, trained, modified, xp FROM player_stats WHERE player_id = $1",
    )
    .bind(player_id)
    .fetch_all(pool)
    .await?;
    Ok(stats_from_rows(rows))
}

/// Load skills from the normalized `player_skills` table.
pub async fn load_skills(pool: &PgPool, player_id: i32) -> Result<Vec<PlayerSkill>, sqlx::Error> {
    let rows = sqlx::query_as::<_, SkillRow>(
        "SELECT skill_key, label, level, xp FROM player_skills WHERE player_id = $1",
    )
    .bind(player_id)
    .fetch_all(pool)
    .await?;
    Ok(skills_from_rows(rows))
}

/// Load bonuses from the normalized `player_bonuses` table.
pub async fn load_bonuses(pool: &PgPool, player_id: i32) -> Result<Vec<PlayerBonus>, sqlx::Error> {
    let rows = sqlx::query_as::<_, BonusRow>(
        "SELECT id, catalog_id, bonus_name, value, duration FROM player_bonuses WHERE player_id = $1",
    )
    .bind(player_id)
    .fetch_all(pool)
    .await?;
    Ok(bonuses_from_rows(rows))
}

/// Resolved sub-models for a player.
pub struct PlayerSubModels {
    pub settings: PlayerSettings,
    pub stats: Vec<PlayerStat>,
    pub skills: Vec<PlayerSkill>,
    pub bonuses: Vec<PlayerBonus>,
}

/// Load all sub-models from normalized tables.
pub async fn load_sub_models(
    pool: &PgPool,
    row: &PlayerRow,
) -> Result<PlayerSubModels, sqlx::Error> {
    let settings = settings_from_row(row);
    let stats = load_stats(pool, row.id).await?;
    let skills = load_skills(pool, row.id).await?;
    let bonuses = load_bonuses(pool, row.id).await?;

    Ok(PlayerSubModels {
        settings,
        stats,
        skills,
        bonuses,
    })
}

/// Persist settings as JSONB in the `players.settings` column.
pub async fn save_settings(
    pool: &PgPool,
    player_id: i32,
    settings: &PlayerSettings,
) -> Result<(), sqlx::Error> {
    let json = serde_json::to_value(settings).unwrap_or_default();
    sqlx::query("UPDATE players SET settings = $1 WHERE id = $2")
        .bind(json)
        .bind(player_id)
        .execute(pool)
        .await?;
    Ok(())
}

/// Replace all stats for a player in the normalized table (delete + insert).
pub async fn save_stats(
    pool: &PgPool,
    player_id: i32,
    stats: &[PlayerStat],
) -> Result<(), sqlx::Error> {
    let mut tx = pool.begin().await?;

    sqlx::query("DELETE FROM player_stats WHERE player_id = $1")
        .bind(player_id)
        .execute(&mut *tx)
        .await?;

    for s in stats {
        sqlx::query(
            "INSERT INTO player_stats (player_id, stat_key, label, base, trained, modified, xp) \
             VALUES ($1, $2, $3, $4, $5, $6, $7)",
        )
        .bind(player_id)
        .bind(&s.stat_key)
        .bind(&s.label)
        .bind(s.base)
        .bind(s.trained)
        .bind(s.modified)
        .bind(s.xp)
        .execute(&mut *tx)
        .await?;
    }

    tx.commit().await
}

/// Replace all skills for a player in the normalized table (delete + insert).
pub async fn save_skills(
    pool: &PgPool,
    player_id: i32,
    skills: &[PlayerSkill],
) -> Result<(), sqlx::Error> {
    let mut tx = pool.begin().await?;

    sqlx::query("DELETE FROM player_skills WHERE player_id = $1")
        .bind(player_id)
        .execute(&mut *tx)
        .await?;

    for s in skills {
        sqlx::query(
            "INSERT INTO player_skills (player_id, skill_key, label, level, xp) \
             VALUES ($1, $2, $3, $4, $5)",
        )
        .bind(player_id)
        .bind(&s.skill_key)
        .bind(&s.label)
        .bind(s.level)
        .bind(s.xp)
        .execute(&mut *tx)
        .await?;
    }

    tx.commit().await
}

/// Replace all bonuses for a player in the normalized table.
pub async fn save_bonuses(
    pool: &PgPool,
    player_id: i32,
    bonuses: &[PlayerBonus],
) -> Result<(), sqlx::Error> {
    let mut tx = pool.begin().await?;

    sqlx::query("DELETE FROM player_bonuses WHERE player_id = $1")
        .bind(player_id)
        .execute(&mut *tx)
        .await?;

    for b in bonuses {
        sqlx::query(
            "INSERT INTO player_bonuses (player_id, catalog_id, bonus_name, value, duration) \
             VALUES ($1, $2, $3, $4, $5)",
        )
        .bind(player_id)
        .bind(b.catalog_id)
        .bind(&b.bonus_name)
        .bind(b.value)
        .bind(b.duration)
        .execute(&mut *tx)
        .await?;
    }

    tx.commit().await
}

/// Set a player's HP to 0 (kill them). Penalty is applied at resurrection.
pub async fn kill_player(pool: &PgPool, player_id: i32) -> Result<(), sqlx::Error> {
    sqlx::query("UPDATE players SET hp = 0 WHERE id = $1")
        .bind(player_id)
        .execute(pool)
        .await?;
    Ok(())
}

/// Apply training: deduct energy and gold from the player.
pub async fn apply_training(
    pool: &PgPool,
    player_id: i32,
    energy_cost: f64,
    gold_cost: i32,
) -> Result<(), sqlx::Error> {
    sqlx::query("UPDATE players SET energy = energy - $1, credits = credits - $2 WHERE id = $3")
        .bind(energy_cost)
        .bind(gold_cost)
        .bind(player_id)
        .execute(pool)
        .await?;
    Ok(())
}

/// Set gender on a player who hasn't selected one yet.
pub async fn set_gender(pool: &PgPool, player_id: i32, gender: &str) -> Result<(), sqlx::Error> {
    sqlx::query("UPDATE players SET gender = $1 WHERE id = $2 AND gender IS NULL")
        .bind(gender)
        .bind(player_id)
        .execute(pool)
        .await?;
    Ok(())
}

/// Disable newbie protection.
pub async fn disable_newbie(pool: &PgPool, player_id: i32) -> Result<(), sqlx::Error> {
    sqlx::query("UPDATE players SET newbie = 0 WHERE id = $1")
        .bind(player_id)
        .execute(pool)
        .await?;
    Ok(())
}

/// Set race on a player (one-time).
pub async fn set_race(pool: &PgPool, player_id: i32, race: &str) -> Result<(), sqlx::Error> {
    sqlx::query("UPDATE players SET race = $1 WHERE id = $2")
        .bind(race)
        .bind(player_id)
        .execute(pool)
        .await?;
    Ok(())
}

/// Set class on a player (one-time).
pub async fn set_class(pool: &PgPool, player_id: i32, class: &str) -> Result<(), sqlx::Error> {
    sqlx::query("UPDATE players SET class = $1 WHERE id = $2")
        .bind(class)
        .bind(player_id)
        .execute(pool)
        .await?;
    Ok(())
}

/// Deduct AP from a player.
pub async fn deduct_ap(pool: &PgPool, player_id: i32, cost: i32) -> Result<(), sqlx::Error> {
    sqlx::query("UPDATE players SET ap = ap - $1 WHERE id = $2")
        .bind(cost)
        .bind(player_id)
        .execute(pool)
        .await?;
    Ok(())
}

/// Add XP to a specific stat for a player.
pub async fn add_stat_xp(
    pool: &PgPool,
    player_id: i32,
    stat_key: &str,
    xp: i32,
) -> Result<(), sqlx::Error> {
    sqlx::query("UPDATE player_stats SET xp = xp + $1 WHERE player_id = $2 AND stat_key = $3")
        .bind(xp)
        .bind(player_id)
        .bind(stat_key)
        .execute(pool)
        .await?;
    Ok(())
}

/// Add XP to a specific skill for a player.
pub async fn add_skill_xp(
    pool: &PgPool,
    player_id: i32,
    skill_key: &str,
    xp: i32,
) -> Result<(), sqlx::Error> {
    sqlx::query("UPDATE player_skills SET xp = xp + $1 WHERE player_id = $2 AND skill_key = $3")
        .bind(xp)
        .bind(player_id)
        .bind(skill_key)
        .execute(pool)
        .await?;
    Ok(())
}

/// Get tribe name for a player's `tribe_id`.
pub async fn tribe_name_for_player(
    pool: &PgPool,
    tribe_id: i32,
) -> Result<Option<String>, sqlx::Error> {
    if tribe_id == 0 {
        return Ok(None);
    }
    let row: Option<(String,)> = sqlx::query_as("SELECT name FROM tribes WHERE id = $1")
        .bind(tribe_id)
        .fetch_optional(pool)
        .await?;
    Ok(row.map(|r| r.0))
}

// ---------------------------------------------------------------------------
// Bonus catalog
// ---------------------------------------------------------------------------

/// A row from the `bonuses` catalog table.
#[derive(Debug, sqlx::FromRow)]
pub struct BonusCatalogRow {
    pub id: i32,
    pub name: String,
    #[sqlx(rename = "desc")]
    pub description: String,
    pub cost: i32,
    pub levels: i16,
    #[sqlx(rename = "trigger")]
    pub trigger_key: String,
    pub bonus: i16,
    pub race: String,
    #[sqlx(rename = "clas")]
    pub class_restriction: String,
}

/// Load all entries from the bonus catalog.
pub async fn load_bonus_catalog(pool: &PgPool) -> Result<Vec<BonusCatalogRow>, sqlx::Error> {
    sqlx::query_as::<_, BonusCatalogRow>(
        r#"SELECT id, name, "desc", cost, levels, "trigger", bonus, race, clas FROM bonuses ORDER BY id"#,
    )
    .fetch_all(pool)
    .await
}

// ---------------------------------------------------------------------------
// Hall of Fame
// ---------------------------------------------------------------------------

/// A row from the `halloffame` table (heroes).
#[derive(Debug, sqlx::FromRow)]
pub struct HofRow {
    pub oldname: String,
    pub heroid: i32,
    pub newid: i32,
    pub herorace: String,
}

/// Load all Hall of Fame heroes.
pub async fn load_hall_of_fame(pool: &PgPool) -> Result<Vec<HofRow>, sqlx::Error> {
    sqlx::query_as::<_, HofRow>(
        "SELECT oldname, heroid, newid, herorace FROM halloffame ORDER BY id",
    )
    .fetch_all(pool)
    .await
}

/// A row from the `halloffame2` table (astral machines).
#[derive(Debug, sqlx::FromRow)]
pub struct HofMachineRow {
    pub tribe: String,
    pub leader: String,
    pub bdate: String,
}

/// Load all Hall of Machines entries.
pub async fn load_hall_of_machines(pool: &PgPool) -> Result<Vec<HofMachineRow>, sqlx::Error> {
    sqlx::query_as::<_, HofMachineRow>(
        "SELECT tribe, leader, bdate FROM halloffame2 ORDER BY id ASC",
    )
    .fetch_all(pool)
    .await
}

/// Check whether a player ID currently exists.
pub async fn player_exists(pool: &PgPool, player_id: i32) -> Result<bool, sqlx::Error> {
    let row: Option<(i32,)> = sqlx::query_as("SELECT id FROM players WHERE id = $1")
        .bind(player_id)
        .fetch_optional(pool)
        .await?;
    Ok(row.is_some())
}
