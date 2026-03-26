//! Mission graph and active mission queries.
//!
//! SQL access for `missions`, `mactions`, and `missions2` tables.

use sqlx::PgPool;
use vallheru_domain::quest::mission::{ActiveMission, ChronicleMission, MissionRoom, MissionType};

// ---------------------------------------------------------------------------
// Row structs
// ---------------------------------------------------------------------------

/// Row from the `missions` table.
#[derive(Debug, Clone, sqlx::FromRow)]
pub struct MissionRoomRow {
    pub id: i32,
    pub name: String,
    pub text: String,
    pub exits: String,
    pub chances: String,
    pub mobs: String,
    pub chances2: String,
    pub items: String,
    pub chances3: String,
    pub moreinfo: String,
}

impl MissionRoomRow {
    pub fn into_domain(self) -> MissionRoom {
        MissionRoom {
            id: self.id,
            name: self.name,
            text: self.text,
            raw_exits: self.exits,
            raw_chances: self.chances,
            raw_mobs: self.mobs,
            raw_chances2: self.chances2,
            raw_items: self.items,
            raw_chances3: self.chances3,
            raw_moreinfo: self.moreinfo,
        }
    }
}

/// Row from the `mactions` table.
#[derive(Debug, Clone, sqlx::FromRow)]
pub struct ActiveMissionRow {
    pub pid: i32,
    pub location: i32,
    pub exits: String,
    pub mobs: String,
    pub items: String,
    #[sqlx(rename = "type")]
    pub mission_type: String,
    pub loot: String,
    pub rooms: i16,
    pub successes: i32,
    pub bonus: i32,
    pub place: String,
    pub target: String,
    pub moreinfo: String,
}

impl ActiveMissionRow {
    pub fn into_domain(self) -> ActiveMission {
        ActiveMission {
            player_id: self.pid,
            current_room_id: self.location,
            raw_exits: self.exits,
            raw_mobs: self.mobs,
            raw_items: self.items,
            mission_type: MissionType::from_db(&self.mission_type).unwrap_or(MissionType::OldStory),
            loot_spec: self.loot,
            rooms_remaining: self.rooms,
            successes: self.successes,
            bonus: self.bonus,
            return_location: self.place,
            has_target: self.target == "Y",
            raw_moreinfo: self.moreinfo,
        }
    }
}

/// Row from the `missions2` (chronicle catalog) table.
#[derive(Debug, Clone, sqlx::FromRow)]
pub struct ChronicleMissionRow {
    pub id: i32,
    pub name: String,
    #[sqlx(rename = "type")]
    pub mission_type: String,
    pub intro: String,
    pub location: String,
    pub shortdesc: String,
    pub chapter: i16,
}

impl ChronicleMissionRow {
    pub fn into_domain(self) -> ChronicleMission {
        ChronicleMission {
            id: self.id,
            name: self.name,
            mission_type: MissionType::from_db(&self.mission_type).unwrap_or(MissionType::OldStory),
            intro: self.intro,
            location: self.location,
            short_desc: self.shortdesc,
            chapter_required: self.chapter,
        }
    }
}

// ---------------------------------------------------------------------------
// Queries — mission rooms
// ---------------------------------------------------------------------------

/// Load a single mission room by ID.
pub async fn find_room_by_id(pool: &PgPool, room_id: i32) -> sqlx::Result<Option<MissionRoom>> {
    let row = sqlx::query_as::<_, MissionRoomRow>(
        "SELECT id, name, text, exits, chances, mobs, chances2, items, chances3, moreinfo \
         FROM missions WHERE id = $1",
    )
    .bind(room_id)
    .fetch_optional(pool)
    .await?;

    Ok(row.map(MissionRoomRow::into_domain))
}

/// Load a random mission room whose name matches exactly.
///
/// Mirrors the PHP: `SELECT * FROM missions WHERE name='...' ORDER BY RAND() LIMIT 1`.
pub async fn find_random_room_by_name(
    pool: &PgPool,
    room_name: &str,
) -> sqlx::Result<Option<MissionRoom>> {
    let row = sqlx::query_as::<_, MissionRoomRow>(
        "SELECT id, name, text, exits, chances, mobs, chances2, items, chances3, moreinfo \
         FROM missions WHERE name = $1 ORDER BY random() LIMIT 1",
    )
    .bind(room_name)
    .fetch_optional(pool)
    .await?;

    Ok(row.map(MissionRoomRow::into_domain))
}

// ---------------------------------------------------------------------------
// Queries — active missions
// ---------------------------------------------------------------------------

/// Load the active mission for a player.
pub async fn find_active_mission(
    pool: &PgPool,
    player_id: i32,
) -> sqlx::Result<Option<ActiveMission>> {
    let row = sqlx::query_as::<_, ActiveMissionRow>(
        "SELECT pid, location, exits, mobs, items, type, loot, rooms, \
                successes, bonus, place, target, moreinfo \
         FROM mactions WHERE pid = $1",
    )
    .bind(player_id)
    .fetch_optional(pool)
    .await?;

    Ok(row.map(ActiveMissionRow::into_domain))
}

/// Parameters for advancing a mission to a new room.
pub struct RoomAdvance<'a> {
    pub player_id: i32,
    pub location: i32,
    pub exits: &'a str,
    pub mobs: &'a str,
    pub items: &'a str,
    pub moreinfo: &'a str,
    pub successes: i32,
}

/// Update the room state of an active mission after advancing to a new room.
pub async fn update_active_mission_room(pool: &PgPool, adv: &RoomAdvance<'_>) -> sqlx::Result<()> {
    sqlx::query(
        "UPDATE mactions SET location = $1, exits = $2, mobs = $3, \
         items = $4, rooms = rooms - 1, successes = $5, moreinfo = $6 \
         WHERE pid = $7",
    )
    .bind(adv.location)
    .bind(adv.exits)
    .bind(adv.mobs)
    .bind(adv.items)
    .bind(adv.successes)
    .bind(adv.moreinfo)
    .bind(adv.player_id)
    .execute(pool)
    .await?;

    Ok(())
}

/// Delete the active mission for a player (mission completed or aborted).
pub async fn delete_active_mission(pool: &PgPool, player_id: i32) -> sqlx::Result<()> {
    sqlx::query("DELETE FROM mactions WHERE pid = $1")
        .bind(player_id)
        .execute(pool)
        .await?;
    Ok(())
}

/// Insert a new active mission record.
pub async fn insert_active_mission(pool: &PgPool, m: &ActiveMission) -> sqlx::Result<()> {
    sqlx::query(
        "INSERT INTO mactions (pid, location, exits, mobs, items, type, loot, rooms, \
         successes, bonus, place, target, moreinfo) \
         VALUES ($1, $2, $3, $4, $5, $6, $7, $8, $9, $10, $11, $12, $13)",
    )
    .bind(m.player_id)
    .bind(m.current_room_id)
    .bind(&m.raw_exits)
    .bind(&m.raw_mobs)
    .bind(&m.raw_items)
    .bind(m.mission_type.to_db())
    .bind(&m.loot_spec)
    .bind(m.rooms_remaining)
    .bind(m.successes)
    .bind(m.bonus)
    .bind(&m.return_location)
    .bind(if m.has_target { "Y" } else { "N" })
    .bind(&m.raw_moreinfo)
    .execute(pool)
    .await?;

    Ok(())
}

// ---------------------------------------------------------------------------
// Queries — chronicle catalog
// ---------------------------------------------------------------------------

/// Load all chronicle missions available at a given location.
pub async fn list_chronicle_missions_at(
    pool: &PgPool,
    location: &str,
) -> sqlx::Result<Vec<ChronicleMission>> {
    let rows = sqlx::query_as::<_, ChronicleMissionRow>(
        "SELECT id, name, type, intro, location, shortdesc, chapter \
         FROM missions2 WHERE location = $1 ORDER BY id",
    )
    .bind(location)
    .fetch_all(pool)
    .await?;

    Ok(rows
        .into_iter()
        .map(ChronicleMissionRow::into_domain)
        .collect())
}

/// Load a single chronicle mission by ID.
pub async fn find_chronicle_mission_by_id(
    pool: &PgPool,
    mission_id: i32,
) -> sqlx::Result<Option<ChronicleMission>> {
    let row = sqlx::query_as::<_, ChronicleMissionRow>(
        "SELECT id, name, type, intro, location, shortdesc, chapter \
         FROM missions2 WHERE id = $1",
    )
    .bind(mission_id)
    .fetch_optional(pool)
    .await?;

    Ok(row.map(ChronicleMissionRow::into_domain))
}

/// Find the starting room for a chronicle mission by name prefix.
///
/// Looks for `{name}start` in the missions table.
pub async fn find_start_room(
    pool: &PgPool,
    mission_name: &str,
) -> sqlx::Result<Option<MissionRoom>> {
    let start_name = format!("{mission_name}start");
    find_random_room_by_name(pool, &start_name).await
}
