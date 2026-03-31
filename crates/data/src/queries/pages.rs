//! Data queries for notes, library, roleplay profiles, and chronicle missions.

use sqlx::PgPool;

// =========================================================================
// Row types
// =========================================================================

#[derive(Debug, sqlx::FromRow, serde::Serialize)]
pub struct NoteRow {
    pub id: i64,
    pub title: String,
    pub body: String,
    pub created_at_formatted: String,
}

#[derive(Debug, sqlx::FromRow)]
pub struct NoteEditRow {
    pub id: i64,
    pub title: String,
    pub body: String,
}

#[derive(Debug, sqlx::FromRow, serde::Serialize)]
pub struct LibraryTextRow {
    pub id: i64,
    pub title: String,
    pub body: String,
    pub author_name: String,
    pub author_id: i64,
    pub text_type: String,
    pub is_approved: bool,
}

#[derive(Debug, sqlx::FromRow, serde::Serialize)]
pub struct LibraryListRow {
    pub id: i64,
    pub title: String,
    pub author_name: String,
    pub author_id: i64,
}

#[derive(Debug, sqlx::FromRow, serde::Serialize)]
pub struct LibraryAuthorRow {
    pub author_name: String,
    pub author_id: i64,
    pub text_count: i64,
}

#[derive(Debug, sqlx::FromRow, serde::Serialize)]
pub struct RoleplayRow {
    pub id: i64,
    pub user: String,
    pub roleplay: String,
    pub ooc: String,
}

#[derive(Debug, sqlx::FromRow, serde::Serialize)]
pub struct ChronicleMissionRow {
    pub id: i64,
    pub name: String,
    pub mission_type: String,
    pub short_desc: String,
    pub intro: String,
    pub chapter: i16,
}

// =========================================================================
// Notes
// =========================================================================

pub async fn count_notes(pool: &PgPool, player_id: i64) -> Result<i64, sqlx::Error> {
    let row: (i64,) = sqlx::query_as("SELECT COUNT(*) FROM notes WHERE player_id = $1")
        .bind(player_id)
        .fetch_one(pool)
        .await?;
    Ok(row.0)
}

pub async fn list_notes(
    pool: &PgPool,
    player_id: i64,
    limit: i64,
    offset: i64,
) -> Result<Vec<NoteRow>, sqlx::Error> {
    sqlx::query_as::<_, NoteRow>(
        "SELECT id, title, body,
                to_char(created_at, 'YYYY-MM-DD HH24:MI') AS created_at_formatted
         FROM notes
         WHERE player_id = $1
         ORDER BY id DESC
         LIMIT $2 OFFSET $3",
    )
    .bind(player_id)
    .bind(limit)
    .bind(offset)
    .fetch_all(pool)
    .await
}

pub async fn get_note(
    pool: &PgPool,
    note_id: i64,
    player_id: i64,
) -> Result<Option<NoteEditRow>, sqlx::Error> {
    sqlx::query_as::<_, NoteEditRow>(
        "SELECT id, title, body FROM notes WHERE id = $1 AND player_id = $2",
    )
    .bind(note_id)
    .bind(player_id)
    .fetch_optional(pool)
    .await
}

pub async fn insert_note(
    pool: &PgPool,
    player_id: i64,
    title: &str,
    body: &str,
) -> Result<(), sqlx::Error> {
    sqlx::query("INSERT INTO notes (player_id, title, body) VALUES ($1, $2, $3)")
        .bind(player_id)
        .bind(title)
        .bind(body)
        .execute(pool)
        .await?;
    Ok(())
}

pub async fn update_note(
    pool: &PgPool,
    note_id: i64,
    player_id: i64,
    title: &str,
    body: &str,
) -> Result<(), sqlx::Error> {
    sqlx::query(
        "UPDATE notes SET title = $1, body = $2, created_at = now()
         WHERE id = $3 AND player_id = $4",
    )
    .bind(title)
    .bind(body)
    .bind(note_id)
    .bind(player_id)
    .execute(pool)
    .await?;
    Ok(())
}

pub async fn delete_note(pool: &PgPool, note_id: i64, player_id: i64) -> Result<(), sqlx::Error> {
    sqlx::query("DELETE FROM notes WHERE id = $1 AND player_id = $2")
        .bind(note_id)
        .bind(player_id)
        .execute(pool)
        .await?;
    Ok(())
}

// =========================================================================
// Library
// =========================================================================

pub async fn count_library_texts(
    pool: &PgPool,
    text_type: &str,
    approved: bool,
    lang: &str,
) -> Result<i64, sqlx::Error> {
    let row: (i64,) = sqlx::query_as(
        "SELECT COUNT(*) FROM library_texts
         WHERE text_type = $1 AND is_approved = $2 AND lang = $3",
    )
    .bind(text_type)
    .bind(approved)
    .bind(lang)
    .fetch_one(pool)
    .await?;
    Ok(row.0)
}

pub async fn count_pending_library(pool: &PgPool, lang: &str) -> Result<i64, sqlx::Error> {
    let row: (i64,) = sqlx::query_as(
        "SELECT COUNT(*) FROM library_texts WHERE is_approved = FALSE AND lang = $1",
    )
    .bind(lang)
    .fetch_one(pool)
    .await?;
    Ok(row.0)
}

pub async fn list_library_by_title(
    pool: &PgPool,
    text_type: &str,
    lang: &str,
) -> Result<Vec<LibraryListRow>, sqlx::Error> {
    sqlx::query_as::<_, LibraryListRow>(
        "SELECT id, title, author_name, author_id
         FROM library_texts
         WHERE is_approved = TRUE AND text_type = $1 AND lang = $2
         ORDER BY title ASC",
    )
    .bind(text_type)
    .bind(lang)
    .fetch_all(pool)
    .await
}

pub async fn list_library_by_date(
    pool: &PgPool,
    text_type: &str,
    lang: &str,
) -> Result<Vec<LibraryListRow>, sqlx::Error> {
    sqlx::query_as::<_, LibraryListRow>(
        "SELECT id, title, author_name, author_id
         FROM library_texts
         WHERE is_approved = TRUE AND text_type = $1 AND lang = $2
         ORDER BY id DESC",
    )
    .bind(text_type)
    .bind(lang)
    .fetch_all(pool)
    .await
}

pub async fn list_library_authors(
    pool: &PgPool,
    text_type: &str,
    lang: &str,
) -> Result<Vec<LibraryAuthorRow>, sqlx::Error> {
    sqlx::query_as::<_, LibraryAuthorRow>(
        "SELECT author_name, author_id, COUNT(*) AS text_count
         FROM library_texts
         WHERE is_approved = TRUE AND text_type = $1 AND lang = $2
         GROUP BY author_name, author_id
         ORDER BY author_name ASC",
    )
    .bind(text_type)
    .bind(lang)
    .fetch_all(pool)
    .await
}

pub async fn list_library_by_author(
    pool: &PgPool,
    text_type: &str,
    author_id: i64,
    lang: &str,
) -> Result<Vec<LibraryListRow>, sqlx::Error> {
    sqlx::query_as::<_, LibraryListRow>(
        "SELECT id, title, author_name, author_id
         FROM library_texts
         WHERE is_approved = TRUE AND text_type = $1 AND author_id = $2 AND lang = $3
         ORDER BY id DESC",
    )
    .bind(text_type)
    .bind(author_id)
    .bind(lang)
    .fetch_all(pool)
    .await
}

pub async fn get_library_text(
    pool: &PgPool,
    text_id: i64,
) -> Result<Option<LibraryTextRow>, sqlx::Error> {
    sqlx::query_as::<_, LibraryTextRow>(
        "SELECT id, title, body, author_name, author_id, text_type, is_approved
         FROM library_texts WHERE id = $1",
    )
    .bind(text_id)
    .fetch_optional(pool)
    .await
}

pub async fn insert_library_text(
    pool: &PgPool,
    title: &str,
    body: &str,
    author_name: &str,
    author_id: i64,
    text_type: &str,
    lang: &str,
) -> Result<(), sqlx::Error> {
    sqlx::query(
        "INSERT INTO library_texts (title, body, author_name, author_id, text_type, lang)
         VALUES ($1, $2, $3, $4, $5, $6)",
    )
    .bind(title)
    .bind(body)
    .bind(author_name)
    .bind(author_id)
    .bind(text_type)
    .bind(lang)
    .execute(pool)
    .await?;
    Ok(())
}

pub async fn update_library_text(
    pool: &PgPool,
    text_id: i64,
    title: &str,
    body: &str,
    text_type: &str,
) -> Result<(), sqlx::Error> {
    sqlx::query("UPDATE library_texts SET title = $1, body = $2, text_type = $3 WHERE id = $4")
        .bind(title)
        .bind(body)
        .bind(text_type)
        .bind(text_id)
        .execute(pool)
        .await?;
    Ok(())
}

pub async fn approve_library_text(pool: &PgPool, text_id: i64) -> Result<(), sqlx::Error> {
    sqlx::query("UPDATE library_texts SET is_approved = TRUE WHERE id = $1")
        .bind(text_id)
        .execute(pool)
        .await?;
    Ok(())
}

pub async fn delete_library_text(pool: &PgPool, text_id: i64) -> Result<(), sqlx::Error> {
    sqlx::query("DELETE FROM library_texts WHERE id = $1")
        .bind(text_id)
        .execute(pool)
        .await?;
    Ok(())
}

pub async fn list_pending_library(
    pool: &PgPool,
    lang: &str,
) -> Result<Vec<LibraryListRow>, sqlx::Error> {
    sqlx::query_as::<_, LibraryListRow>(
        "SELECT id, title, author_name, author_id
         FROM library_texts
         WHERE is_approved = FALSE AND lang = $1
         ORDER BY id ASC",
    )
    .bind(lang)
    .fetch_all(pool)
    .await
}

// =========================================================================
// Roleplay profiles
// =========================================================================

pub async fn get_roleplay_profile(
    pool: &PgPool,
    player_id: i64,
) -> Result<Option<RoleplayRow>, sqlx::Error> {
    sqlx::query_as::<_, RoleplayRow>(
        "SELECT id, \"user\", roleplay, ooc FROM players WHERE id = $1",
    )
    .bind(player_id)
    .fetch_optional(pool)
    .await
}

pub async fn get_adjacent_roleplay_ids(
    pool: &PgPool,
    player_id: i64,
) -> Result<(Option<i64>, Option<i64>), sqlx::Error> {
    let prev: Option<(i64,)> = sqlx::query_as(
        "SELECT id FROM players WHERE id < $1 AND roleplay != '' ORDER BY id DESC LIMIT 1",
    )
    .bind(player_id)
    .fetch_optional(pool)
    .await?;

    let next: Option<(i64,)> = sqlx::query_as(
        "SELECT id FROM players WHERE id > $1 AND roleplay != '' ORDER BY id ASC LIMIT 1",
    )
    .bind(player_id)
    .fetch_optional(pool)
    .await?;

    // Wrap around: if no prev, get max; if no next, get min.
    let prev_id = if let Some((id,)) = prev {
        Some(id)
    } else {
        let wrap: Option<(i64,)> =
            sqlx::query_as("SELECT MAX(id) FROM players WHERE roleplay != ''")
                .fetch_optional(pool)
                .await?;
        wrap.and_then(|r| if r.0 == player_id { None } else { Some(r.0) })
    };

    let next_id = if let Some((id,)) = next {
        Some(id)
    } else {
        let wrap: Option<(i64,)> =
            sqlx::query_as("SELECT MIN(id) FROM players WHERE roleplay != ''")
                .fetch_optional(pool)
                .await?;
        wrap.and_then(|r| if r.0 == player_id { None } else { Some(r.0) })
    };

    Ok((prev_id, next_id))
}

// =========================================================================
// Chronicle missions
// =========================================================================

pub async fn get_player_chapter(pool: &PgPool, player_id: i64) -> Result<i16, sqlx::Error> {
    let row: (i16,) = sqlx::query_as("SELECT chapter FROM players WHERE id = $1")
        .bind(player_id)
        .fetch_one(pool)
        .await?;
    Ok(row.0)
}

pub async fn list_chronicle_missions(
    pool: &PgPool,
    location: &str,
    player_chapter: i16,
) -> Result<Vec<ChronicleMissionRow>, sqlx::Error> {
    sqlx::query_as::<_, ChronicleMissionRow>(
        "SELECT id, name, mission_type, short_desc, intro, chapter
         FROM chronicle_missions
         WHERE location = $1
           AND (mission_type != 'Q' OR chapter <= $2)
         ORDER BY mission_type, id",
    )
    .bind(location)
    .bind(player_chapter)
    .fetch_all(pool)
    .await
}

pub async fn get_chronicle_mission(
    pool: &PgPool,
    mission_id: i64,
) -> Result<Option<ChronicleMissionRow>, sqlx::Error> {
    sqlx::query_as::<_, ChronicleMissionRow>(
        "SELECT id, name, mission_type, short_desc, intro, chapter
         FROM chronicle_missions WHERE id = $1",
    )
    .bind(mission_id)
    .fetch_optional(pool)
    .await
}
