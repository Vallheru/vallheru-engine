//! Quest action and quest content queries.
//!
//! SQL access for `questaction` and `quests` tables.

use sqlx::PgPool;

// ---------------------------------------------------------------------------
// Row structs
// ---------------------------------------------------------------------------

/// Row from the `questaction` table.
#[derive(Debug, Clone, sqlx::FromRow)]
pub struct QuestActionRow {
    pub id: i32,
    pub player: i32,
    pub quest: i32,
    pub action: String,
}

/// Row from the `quests` table (authored content).
#[derive(Debug, Clone, sqlx::FromRow)]
pub struct QuestStepRow {
    pub id: i32,
    pub qid: i32,
    pub location: String,
    pub name: String,
    pub option: String,
    pub text: String,
    pub lang: String,
}

// ---------------------------------------------------------------------------
// Queries — quest progress (questaction)
// ---------------------------------------------------------------------------

/// Find a player's progress on a specific quest.
pub async fn find_quest_action(
    pool: &PgPool,
    player_id: i32,
    quest_id: i32,
) -> sqlx::Result<Option<QuestActionRow>> {
    sqlx::query_as::<_, QuestActionRow>(
        "SELECT id, player, quest, action FROM questaction \
         WHERE player = $1 AND quest = $2",
    )
    .bind(player_id)
    .bind(quest_id)
    .fetch_optional(pool)
    .await
}

/// Load all quest actions for a player (used to check completion status).
pub async fn find_all_quest_actions(
    pool: &PgPool,
    player_id: i32,
) -> sqlx::Result<Vec<QuestActionRow>> {
    sqlx::query_as::<_, QuestActionRow>(
        "SELECT id, player, quest, action FROM questaction \
         WHERE player = $1 ORDER BY quest",
    )
    .bind(player_id)
    .fetch_all(pool)
    .await
}

/// Count how many quests a player has completed (`action = 'end'`).
pub async fn count_completed_quests(pool: &PgPool, player_id: i32) -> sqlx::Result<i64> {
    let row: (i64,) = sqlx::query_as(
        "SELECT COUNT(*) FROM questaction \
         WHERE player = $1 AND action = 'end'",
    )
    .bind(player_id)
    .fetch_one(pool)
    .await?;
    Ok(row.0)
}

/// Insert a new quest action (start a quest).
pub async fn insert_quest_action(
    pool: &PgPool,
    player_id: i32,
    quest_id: i32,
    action: &str,
) -> sqlx::Result<()> {
    sqlx::query(
        "INSERT INTO questaction (player, quest, action) \
         VALUES ($1, $2, $3)",
    )
    .bind(player_id)
    .bind(quest_id)
    .bind(action)
    .execute(pool)
    .await?;
    Ok(())
}

/// Advance a quest to a new branch/step.
pub async fn update_quest_action(
    pool: &PgPool,
    player_id: i32,
    quest_id: i32,
    new_action: &str,
) -> sqlx::Result<()> {
    sqlx::query(
        "UPDATE questaction SET action = $1 \
         WHERE player = $2 AND quest = $3",
    )
    .bind(new_action)
    .bind(player_id)
    .bind(quest_id)
    .execute(pool)
    .await?;
    Ok(())
}

/// Delete a specific quest action (resign from quest).
pub async fn delete_quest_action(pool: &PgPool, player_id: i32, quest_id: i32) -> sqlx::Result<()> {
    sqlx::query("DELETE FROM questaction WHERE player = $1 AND quest = $2")
        .bind(player_id)
        .bind(quest_id)
        .execute(pool)
        .await?;
    Ok(())
}

/// Delete all quest actions for a player (used after all quests completed).
pub async fn delete_all_quest_actions(pool: &PgPool, player_id: i32) -> sqlx::Result<()> {
    sqlx::query("DELETE FROM questaction WHERE player = $1")
        .bind(player_id)
        .execute(pool)
        .await?;
    Ok(())
}

// ---------------------------------------------------------------------------
// Queries — quest content (quests)
// ---------------------------------------------------------------------------

/// Load the start text for a quest at a given location.
pub async fn find_quest_start_text(
    pool: &PgPool,
    quest_id: i32,
    location: &str,
    lang: &str,
) -> sqlx::Result<Option<QuestStepRow>> {
    sqlx::query_as::<_, QuestStepRow>(
        "SELECT id, qid, location, name, option, text, lang \
         FROM quests WHERE qid = $1 AND location = $2 AND name = 'start' AND lang = $3 \
         LIMIT 1",
    )
    .bind(quest_id)
    .bind(location)
    .bind(lang)
    .fetch_optional(pool)
    .await
}

/// Load all quest step rows matching a given step name (for box choices).
pub async fn find_quest_steps_by_name(
    pool: &PgPool,
    quest_id: i32,
    location: &str,
    name: &str,
    lang: &str,
) -> sqlx::Result<Vec<QuestStepRow>> {
    sqlx::query_as::<_, QuestStepRow>(
        "SELECT id, qid, location, name, option, text, lang \
         FROM quests WHERE qid = $1 AND location = $2 AND name = $3 AND lang = $4 \
         ORDER BY id",
    )
    .bind(quest_id)
    .bind(location)
    .bind(name)
    .bind(lang)
    .fetch_all(pool)
    .await
}

/// Load a single quest step by name (for narrative text or answer checking).
pub async fn find_quest_step(
    pool: &PgPool,
    quest_id: i32,
    location: &str,
    name: &str,
    lang: &str,
) -> sqlx::Result<Option<QuestStepRow>> {
    sqlx::query_as::<_, QuestStepRow>(
        "SELECT id, qid, location, name, option, text, lang \
         FROM quests WHERE qid = $1 AND location = $2 AND name = $3 AND lang = $4 \
         LIMIT 1",
    )
    .bind(quest_id)
    .bind(location)
    .bind(name)
    .bind(lang)
    .fetch_optional(pool)
    .await
}

/// Check the answer for a quest step (case-insensitive comparison).
///
/// Returns `true` if the player's answer matches the stored `option` field.
pub async fn check_quest_answer(
    pool: &PgPool,
    quest_id: i32,
    location: &str,
    name: &str,
    lang: &str,
    player_answer: &str,
) -> sqlx::Result<bool> {
    let step = find_quest_step(pool, quest_id, location, name, lang).await?;
    match step {
        Some(row) => Ok(row.option.to_lowercase() == player_answer.to_lowercase()),
        None => Ok(false),
    }
}
