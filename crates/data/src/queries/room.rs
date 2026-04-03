//! Tavern room and room-message queries.

use std::collections::HashMap;

use sqlx::PgPool;

// =========================================================================
// Row types
// =========================================================================

/// A room row.
#[derive(Debug, sqlx::FromRow)]
pub struct RoomRow {
    pub id: i32,
    pub owner_id: i64,
    pub name: String,
    pub description: String,
    pub days_remaining: i16,
    pub co_owners: Vec<i64>,
    pub npcs: Vec<String>,
    pub colors: serde_json::Value,
}

impl RoomRow {
    /// Parse the JSONB `colors` field into `HashMap<player_id, color_name>`.
    pub fn color_map(&self) -> HashMap<i64, String> {
        let mut map = HashMap::new();
        if let Some(obj) = self.colors.as_object() {
            for (k, v) in obj {
                if let (Ok(id), Some(color)) = (k.parse::<i64>(), v.as_str()) {
                    if !color.is_empty() {
                        map.insert(id, color.to_owned());
                    }
                }
            }
        }
        map
    }
}

/// Build a JSONB value from a colour map.
pub fn color_map_to_json<S: ::std::hash::BuildHasher>(
    map: &HashMap<i64, String, S>,
) -> serde_json::Value {
    let obj: serde_json::Map<String, serde_json::Value> = map
        .iter()
        .map(|(k, v)| (k.to_string(), serde_json::Value::String(v.clone())))
        .collect();
    serde_json::Value::Object(obj)
}

/// A room message row.
#[derive(Debug, sqlx::FromRow)]
pub struct RoomMessageRow {
    pub id: i64,
    pub author_html: String,
    pub body: String,
    pub sender_id: i64,
    pub recipient_id: i64,
    pub created_epoch: i64,
}

/// Minimal player info for room member lists.
#[derive(Debug, sqlx::FromRow)]
pub struct RoomMemberRow {
    pub id: i64,
    pub username: String,
}

// =========================================================================
// Room queries
// =========================================================================

/// Load a room by ID.
pub async fn find_room(pool: &PgPool, room_id: i32) -> Result<Option<RoomRow>, sqlx::Error> {
    sqlx::query_as::<_, RoomRow>(
        "SELECT id, owner_id, name, description, days_remaining,
                co_owners, npcs, colors
         FROM rooms WHERE id = $1",
    )
    .bind(room_id)
    .fetch_optional(pool)
    .await
}

/// Create a new room and return its ID.
pub async fn create_room(pool: &PgPool, owner_id: i64, name: &str) -> Result<i32, sqlx::Error> {
    sqlx::query_scalar::<_, i32>("INSERT INTO rooms (owner_id, name) VALUES ($1, $2) RETURNING id")
        .bind(owner_id)
        .bind(name)
        .fetch_one(pool)
        .await
}

/// Delete a room by ID (cascades to `room_messages`).
pub async fn delete_room(pool: &PgPool, room_id: i32) -> Result<(), sqlx::Error> {
    sqlx::query("DELETE FROM rooms WHERE id = $1")
        .bind(room_id)
        .execute(pool)
        .await?;
    Ok(())
}

/// Update room description.
pub async fn update_description(
    pool: &PgPool,
    room_id: i32,
    description: &str,
) -> Result<(), sqlx::Error> {
    sqlx::query("UPDATE rooms SET description = $1 WHERE id = $2")
        .bind(description)
        .bind(room_id)
        .execute(pool)
        .await?;
    Ok(())
}

/// Update room name.
pub async fn update_name(pool: &PgPool, room_id: i32, name: &str) -> Result<(), sqlx::Error> {
    sqlx::query("UPDATE rooms SET name = $1 WHERE id = $2")
        .bind(name)
        .bind(room_id)
        .execute(pool)
        .await?;
    Ok(())
}

/// Extend room rent by `days`, deducting `cost` gold from the player.
pub async fn extend_rent(
    pool: &PgPool,
    room_id: i32,
    days: i16,
    player_id: i64,
    cost: i32,
) -> Result<(), sqlx::Error> {
    let mut tx = pool.begin().await?;
    sqlx::query("UPDATE rooms SET days_remaining = days_remaining + $1 WHERE id = $2")
        .bind(days)
        .bind(room_id)
        .execute(&mut *tx)
        .await?;
    sqlx::query("UPDATE players SET credits = credits - $1 WHERE id = $2")
        .bind(cost)
        .bind(player_id)
        .execute(&mut *tx)
        .await?;
    tx.commit().await?;
    Ok(())
}

/// Set co-owners for a room.
pub async fn set_co_owners(
    pool: &PgPool,
    room_id: i32,
    co_owners: &[i64],
) -> Result<(), sqlx::Error> {
    sqlx::query("UPDATE rooms SET co_owners = $1 WHERE id = $2")
        .bind(co_owners)
        .bind(room_id)
        .execute(pool)
        .await?;
    Ok(())
}

/// Set NPCs for a room.
pub async fn set_npcs(pool: &PgPool, room_id: i32, npcs: &[String]) -> Result<(), sqlx::Error> {
    sqlx::query("UPDATE rooms SET npcs = $1 WHERE id = $2")
        .bind(npcs)
        .bind(room_id)
        .execute(pool)
        .await?;
    Ok(())
}

/// Set colours JSON for a room.
pub async fn set_colors(
    pool: &PgPool,
    room_id: i32,
    colors: &serde_json::Value,
) -> Result<(), sqlx::Error> {
    sqlx::query("UPDATE rooms SET colors = $1 WHERE id = $2")
        .bind(colors)
        .bind(room_id)
        .execute(pool)
        .await?;
    Ok(())
}

// =========================================================================
// Player room assignment
// =========================================================================

/// Assign a player to a room.
pub async fn assign_player_room(
    pool: &PgPool,
    player_id: i64,
    room_id: i32,
) -> Result<(), sqlx::Error> {
    sqlx::query("UPDATE players SET room = $1 WHERE id = $2")
        .bind(room_id)
        .bind(player_id)
        .execute(pool)
        .await?;
    Ok(())
}

/// Clear a player's room assignment.
pub async fn clear_player_room(pool: &PgPool, player_id: i64) -> Result<(), sqlx::Error> {
    sqlx::query("UPDATE players SET room = 0 WHERE id = $1")
        .bind(player_id)
        .execute(pool)
        .await?;
    Ok(())
}

/// Clear room for all players in a given room.
pub async fn clear_all_players_in_room(pool: &PgPool, room_id: i32) -> Result<(), sqlx::Error> {
    sqlx::query("UPDATE players SET room = 0 WHERE room = $1")
        .bind(room_id)
        .execute(pool)
        .await?;
    Ok(())
}

/// List all players currently assigned to a room.
pub async fn list_room_members(
    pool: &PgPool,
    room_id: i32,
) -> Result<Vec<RoomMemberRow>, sqlx::Error> {
    sqlx::query_as::<_, RoomMemberRow>(
        "SELECT id, username FROM players WHERE room = $1 ORDER BY username",
    )
    .bind(room_id)
    .fetch_all(pool)
    .await
}

/// Check a player's room assignment.
pub async fn player_room(pool: &PgPool, player_id: i64) -> Result<i32, sqlx::Error> {
    sqlx::query_scalar::<_, i32>("SELECT room FROM players WHERE id = $1")
        .bind(player_id)
        .fetch_one(pool)
        .await
}

/// Check if a player exists and get their room + whether they accept room invites.
pub async fn player_invite_check(
    pool: &PgPool,
    player_id: i64,
) -> Result<Option<(i64, i32, bool)>, sqlx::Error> {
    let row = sqlx::query_as::<_, (i64, i32, String)>(
        "SELECT id, room, COALESCE(settings::text, '{}')
         FROM players WHERE id = $1",
    )
    .bind(player_id)
    .fetch_optional(pool)
    .await?;

    Ok(row.map(|(id, room, settings_json)| {
        let accepts_invites: bool = match serde_json::from_str::<serde_json::Value>(&settings_json)
        {
            Ok(v) => v.get("rinvites").and_then(|r| r.as_str()) != Some("N"),
            Err(e) => {
                tracing::error!(error = %e, player_id, "Failed to parse room settings JSON");
                true
            }
        };
        (id, room, accepts_invites)
    }))
}

/// Check if a player blocks room invites from another player.
pub async fn is_inn_blocked(
    pool: &PgPool,
    target_id: i64,
    inviter_id: i64,
) -> Result<bool, sqlx::Error> {
    let count = sqlx::query_scalar::<_, i64>(
        "SELECT count(id) FROM mail_blocks
         WHERE owner_id = $1 AND blocked_id = $2 AND block_chat = TRUE",
    )
    .bind(target_id)
    .bind(inviter_id)
    .fetch_one(pool)
    .await?;
    Ok(count > 0)
}

/// Look up a player name by ID.
pub async fn player_name(pool: &PgPool, player_id: i64) -> Result<Option<String>, sqlx::Error> {
    sqlx::query_scalar::<_, String>("SELECT username FROM players WHERE id = $1")
        .bind(player_id)
        .fetch_optional(pool)
        .await
}

/// Look up a player ID by username (for NPC name collision check).
pub async fn player_name_by_username(
    pool: &PgPool,
    username: &str,
) -> Result<Option<i64>, sqlx::Error> {
    sqlx::query_scalar::<_, i64>("SELECT id FROM players WHERE username = $1")
        .bind(username)
        .fetch_optional(pool)
        .await
}

// =========================================================================
// Room message queries
// =========================================================================

/// Fetch recent messages in a room, visible to a specific player.
/// Returns public messages (`recipient_id` = 0) plus whispers involving the player.
pub async fn list_room_messages(
    pool: &PgPool,
    room_id: i32,
    player_id: i64,
    limit: i32,
) -> Result<Vec<RoomMessageRow>, sqlx::Error> {
    sqlx::query_as::<_, RoomMessageRow>(
        "SELECT id, author_html, body, sender_id, recipient_id,
                extract(epoch from created_at)::bigint AS created_epoch
         FROM room_messages
         WHERE room_id = $1
           AND (recipient_id = 0 OR recipient_id = $2 OR sender_id = $2)
         ORDER BY id DESC
         LIMIT $3",
    )
    .bind(room_id)
    .bind(player_id)
    .bind(i64::from(limit))
    .fetch_all(pool)
    .await
}

/// Insert a room message.
pub async fn insert_room_message(
    pool: &PgPool,
    room_id: i32,
    author_html: &str,
    body: &str,
    sender_id: i64,
    recipient_id: i64,
) -> Result<i64, sqlx::Error> {
    sqlx::query_scalar::<_, i64>(
        "INSERT INTO room_messages (room_id, author_html, body, sender_id, recipient_id)
         VALUES ($1, $2, $3, $4, $5)
         RETURNING id",
    )
    .bind(room_id)
    .bind(author_html)
    .bind(body)
    .bind(sender_id)
    .bind(recipient_id)
    .fetch_one(pool)
    .await
}

/// Delete a single room message by ID (owner/admin action).
pub async fn delete_room_message(
    pool: &PgPool,
    message_id: i64,
    room_id: i32,
) -> Result<(), sqlx::Error> {
    sqlx::query("DELETE FROM room_messages WHERE id = $1 AND room_id = $2")
        .bind(message_id)
        .bind(room_id)
        .execute(pool)
        .await?;
    Ok(())
}

// =========================================================================
// Online in room
// =========================================================================

/// List players active in the room (lpv within 180 seconds).
pub async fn online_in_room(
    pool: &PgPool,
    room_id: i32,
) -> Result<Vec<RoomMemberRow>, sqlx::Error> {
    sqlx::query_as::<_, RoomMemberRow>(
        "SELECT id, username FROM players
         WHERE room = $1
           AND current_page = 'Pokój w karczmie'
           AND last_page_visit >= (extract(epoch from now()) - 180)::bigint
         ORDER BY username",
    )
    .bind(room_id)
    .fetch_all(pool)
    .await
}

/// Update a player's current page to 'Pokój w karczmie'.
pub async fn set_page_room(pool: &PgPool, player_id: i64) -> Result<(), sqlx::Error> {
    sqlx::query("UPDATE players SET current_page = 'Pokój w karczmie' WHERE id = $1")
        .bind(player_id)
        .execute(pool)
        .await?;
    Ok(())
}

// =========================================================================
// Game log entries (room events)
// =========================================================================

/// Insert a game-log entry for a player (type 'E' = event).
pub async fn insert_event_log(
    pool: &PgPool,
    owner_id: i64,
    message: &str,
) -> Result<(), sqlx::Error> {
    sqlx::query("INSERT INTO game_log (owner_id, message, log_type) VALUES ($1, $2, 'E')")
        .bind(owner_id)
        .bind(message)
        .execute(pool)
        .await?;
    Ok(())
}

// =========================================================================
// Transactional multi-step room operations (TD-047)
// =========================================================================

/// Destroy a room: notify members, clear room assignments, delete room — all atomic.
pub async fn destroy_room_tx(
    pool: &PgPool,
    room_id: i32,
    member_ids: &[i64],
    owner_id: i64,
    log_msg: &str,
) -> Result<(), sqlx::Error> {
    let mut tx = pool.begin().await?;

    for &mid in member_ids {
        if mid != owner_id {
            sqlx::query("INSERT INTO game_log (owner_id, message, log_type) VALUES ($1, $2, 'E')")
                .bind(mid)
                .bind(log_msg)
                .execute(&mut *tx)
                .await?;
        }
    }

    sqlx::query("UPDATE players SET room = 0 WHERE room = $1")
        .bind(room_id)
        .execute(&mut *tx)
        .await?;

    sqlx::query("DELETE FROM rooms WHERE id = $1")
        .bind(room_id)
        .execute(&mut *tx)
        .await?;

    tx.commit().await?;
    Ok(())
}

/// Remove a player from a room: send notification, clear assignment,
/// update co-owners list — all atomic.
pub async fn remove_from_room_tx(
    pool: &PgPool,
    player_id: i64,
    room_id: i32,
    co_owners: &[i64],
    log_msg: &str,
) -> Result<(), sqlx::Error> {
    let mut tx = pool.begin().await?;

    sqlx::query("INSERT INTO game_log (owner_id, message, log_type) VALUES ($1, $2, 'E')")
        .bind(player_id)
        .bind(log_msg)
        .execute(&mut *tx)
        .await?;

    sqlx::query("UPDATE players SET room = 0 WHERE id = $1")
        .bind(player_id)
        .execute(&mut *tx)
        .await?;

    sqlx::query("UPDATE rooms SET co_owners = $1 WHERE id = $2")
        .bind(co_owners)
        .bind(room_id)
        .execute(&mut *tx)
        .await?;

    tx.commit().await?;
    Ok(())
}

/// Invite a player to a room: assign room + send notification — atomic.
pub async fn invite_to_room_tx(
    pool: &PgPool,
    player_id: i64,
    room_id: i32,
    log_msg: &str,
) -> Result<(), sqlx::Error> {
    let mut tx = pool.begin().await?;

    sqlx::query("UPDATE players SET room = $1 WHERE id = $2")
        .bind(room_id)
        .bind(player_id)
        .execute(&mut *tx)
        .await?;

    sqlx::query("INSERT INTO game_log (owner_id, message, log_type) VALUES ($1, $2, 'E')")
        .bind(player_id)
        .bind(log_msg)
        .execute(&mut *tx)
        .await?;

    tx.commit().await?;
    Ok(())
}

/// Leave a room (non-owner): post system message, clear assignment,
/// optionally update co-owners — all atomic.
pub async fn leave_room_tx(
    pool: &PgPool,
    player_id: i64,
    room_id: i32,
    leave_msg: &str,
    sender_id: i64,
    co_owners: Option<&[i64]>,
) -> Result<(), sqlx::Error> {
    let mut tx = pool.begin().await?;

    if let Some(co) = co_owners {
        sqlx::query("UPDATE rooms SET co_owners = $1 WHERE id = $2")
            .bind(co)
            .bind(room_id)
            .execute(&mut *tx)
            .await?;
    }

    sqlx::query(
        "INSERT INTO room_messages (room_id, author_html, body, sender_id, recipient_id)
         VALUES ($1, '', $2, $3, 0)",
    )
    .bind(room_id)
    .bind(leave_msg)
    .bind(sender_id)
    .execute(&mut *tx)
    .await?;

    sqlx::query("UPDATE players SET room = 0 WHERE id = $1")
        .bind(player_id)
        .execute(&mut *tx)
        .await?;

    tx.commit().await?;
    Ok(())
}
