//! Player loading and persistence queries.
//!
//! Provides a flat `PlayerRow` struct (DB-coupled via `sqlx::FromRow`) and
//! conversion to the domain `Player` type. Sub-models (settings, stats,
//! skills, bonuses) are loaded from their normalized tables, with fallback
//! parsing of legacy raw columns during the migration window.

use sqlx::PgPool;
use vallheru_domain::player::{
    Player, Rank,
    bonuses::{PlayerBonus, parse_legacy_bonuses},
    settings::PlayerSettings,
    skills::{PlayerSkill, parse_legacy_skills},
    stats::{PlayerStat, parse_legacy_stats},
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
    // Legacy serialized columns — non-empty only during migration window.
    pub settings_raw: String,
    pub stats_raw: String,
    pub skills_raw: String,
    pub bonuses_raw: String,
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
        rank: Rank::from_legacy(&row.rank),
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

/// Resolve player settings: prefer JSONB column, fall back to legacy raw.
pub fn settings_from_row(row: &PlayerRow) -> PlayerSettings {
    // If the JSONB column has data, use it.
    if !row.settings.is_null()
        && row.settings != serde_json::Value::Object(serde_json::Map::default())
    {
        match serde_json::from_value::<PlayerSettings>(row.settings.clone()) {
            Ok(s) => return s,
            Err(e) => {
                tracing::warn!(
                    player_id = row.id,
                    error = %e,
                    "failed to parse JSONB settings, falling back to legacy"
                );
            }
        }
    }
    // Fall back to the legacy semicolon format.
    if !row.settings_raw.is_empty() {
        return PlayerSettings::from_legacy(&row.settings_raw);
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
    reputation, settings_raw, stats_raw, skills_raw, bonuses_raw,
    settings
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
        "SELECT stat_key, label, base, trained, modified FROM player_stats WHERE player_id = $1",
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
        "SELECT id, bonus_name, value, duration FROM player_bonuses WHERE player_id = $1",
    )
    .bind(player_id)
    .fetch_all(pool)
    .await?;
    Ok(bonuses_from_rows(rows))
}

/// Resolved sub-models for a player, from either normalized tables or
/// legacy raw columns.
pub struct PlayerSubModels {
    pub settings: PlayerSettings,
    pub stats: Vec<PlayerStat>,
    pub skills: Vec<PlayerSkill>,
    pub bonuses: Vec<PlayerBonus>,
}

/// Load sub-models with fallback: if the normalized tables are empty,
/// parse from the legacy raw columns.
///
/// This is the main entry point during the migration window where both
/// old and new storage may coexist.
pub async fn load_sub_models(
    pool: &PgPool,
    row: &PlayerRow,
) -> Result<PlayerSubModels, sqlx::Error> {
    let settings = settings_from_row(row);

    // Load normalized stats; fall back to legacy raw if empty.
    let mut stats = load_stats(pool, row.id).await?;
    if stats.is_empty() && !row.stats_raw.is_empty() {
        tracing::debug!(
            player_id = row.id,
            "no normalized stats found, parsing from legacy stats_raw"
        );
        stats = parse_legacy_stats(&row.stats_raw);
    }

    // Load normalized skills; fall back to legacy raw if empty.
    let mut skills = load_skills(pool, row.id).await?;
    if skills.is_empty() && !row.skills_raw.is_empty() {
        tracing::debug!(
            player_id = row.id,
            "no normalized skills found, parsing from legacy skills_raw"
        );
        skills = parse_legacy_skills(&row.skills_raw);
    }

    // Load normalized bonuses; fall back to legacy raw if empty.
    let mut bonuses = load_bonuses(pool, row.id).await?;
    if bonuses.is_empty() && !row.bonuses_raw.is_empty() {
        tracing::debug!(
            player_id = row.id,
            "no normalized bonuses found, parsing from legacy bonuses_raw"
        );
        bonuses = parse_legacy_bonuses(&row.bonuses_raw);
    }

    Ok(PlayerSubModels {
        settings,
        stats,
        skills,
        bonuses,
    })
}

/// Parse all sub-models purely from legacy raw columns, without any DB
/// queries. Used during data import when normalized tables are not yet
/// populated.
pub fn parse_legacy_sub_models(row: &PlayerRow) -> PlayerSubModels {
    let settings = if row.settings_raw.is_empty() {
        PlayerSettings::default()
    } else {
        PlayerSettings::from_legacy(&row.settings_raw)
    };
    let stats = parse_legacy_stats(&row.stats_raw);
    let skills = parse_legacy_skills(&row.skills_raw);
    let bonuses = parse_legacy_bonuses(&row.bonuses_raw);

    PlayerSubModels {
        settings,
        stats,
        skills,
        bonuses,
    }
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
            "INSERT INTO player_stats (player_id, stat_key, label, base, trained, modified) \
             VALUES ($1, $2, $3, $4, $5, $6)",
        )
        .bind(player_id)
        .bind(&s.stat_key)
        .bind(&s.label)
        .bind(s.base)
        .bind(s.trained)
        .bind(s.modified)
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
            "INSERT INTO player_bonuses (player_id, bonus_name, value, duration) \
             VALUES ($1, $2, $3, $4)",
        )
        .bind(player_id)
        .bind(&b.bonus_name)
        .bind(b.value)
        .bind(b.duration)
        .execute(&mut *tx)
        .await?;
    }

    tx.commit().await
}
