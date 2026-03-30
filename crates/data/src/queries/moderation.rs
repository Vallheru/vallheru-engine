//! Moderation queries — jail, court documents, communication bans, and
//! administrative punishment actions.

use sqlx::PgPool;

// ---------------------------------------------------------------------------
// Jail
// ---------------------------------------------------------------------------

/// A row from the `jail` table joined with the prisoner's username.
#[derive(sqlx::FromRow)]
pub struct JailRow {
    pub id: i32,
    pub prisoner: i32,
    pub prisoner_name: String,
    pub duration: i32,
    pub sentenced: String,
    pub verdict: String,
    pub cost: i32,
}

/// List all current prisoners.
pub async fn list_prisoners(pool: &PgPool) -> Result<Vec<JailRow>, sqlx::Error> {
    sqlx::query_as::<_, JailRow>(
        "SELECT j.id, j.prisoner, p.username AS prisoner_name, \
                j.duration, TO_CHAR(j.sentenced, 'YYYY-MM-DD') AS sentenced, \
                j.verdict, j.cost \
         FROM jail j \
         JOIN players p ON p.id = j.prisoner \
         ORDER BY j.id ASC",
    )
    .fetch_all(pool)
    .await
}

/// Find a single jail record by jail id.
pub async fn find_jail_record(pool: &PgPool, jail_id: i32) -> Result<Option<JailRow>, sqlx::Error> {
    sqlx::query_as::<_, JailRow>(
        "SELECT j.id, j.prisoner, p.username AS prisoner_name, \
                j.duration, TO_CHAR(j.sentenced, 'YYYY-MM-DD') AS sentenced, \
                j.verdict, j.cost \
         FROM jail j \
         JOIN players p ON p.id = j.prisoner \
         WHERE j.id = $1",
    )
    .bind(jail_id)
    .fetch_optional(pool)
    .await
}

/// Find a jail record by prisoner player id.
pub async fn find_jail_by_prisoner(
    pool: &PgPool,
    prisoner_id: i32,
) -> Result<Option<JailRow>, sqlx::Error> {
    sqlx::query_as::<_, JailRow>(
        "SELECT j.id, j.prisoner, p.username AS prisoner_name, \
                j.duration, TO_CHAR(j.sentenced, 'YYYY-MM-DD') AS sentenced, \
                j.verdict, j.cost \
         FROM jail j \
         JOIN players p ON p.id = j.prisoner \
         WHERE j.prisoner = $1",
    )
    .bind(prisoner_id)
    .fetch_optional(pool)
    .await
}

/// Send a player to jail or extend their existing sentence.
pub async fn send_to_jail(
    pool: &PgPool,
    prisoner_id: i32,
    verdict: &str,
    duration_days: i32,
) -> Result<(), sqlx::Error> {
    // Check if already jailed — if so, extend.
    let existing: Option<(i32,)> = sqlx::query_as("SELECT id FROM jail WHERE prisoner = $1")
        .bind(prisoner_id)
        .fetch_optional(pool)
        .await?;

    if let Some((jail_id,)) = existing {
        sqlx::query(
            "UPDATE jail SET duration = duration + $1, \
                    verdict = verdict || '; ' || $2 \
             WHERE id = $3",
        )
        .bind(duration_days)
        .bind(verdict)
        .bind(jail_id)
        .execute(pool)
        .await?;
    } else {
        sqlx::query(
            "INSERT INTO jail (prisoner, verdict, duration, cost) \
             VALUES ($1, $2, $3, 0)",
        )
        .bind(prisoner_id)
        .bind(verdict)
        .bind(duration_days)
        .execute(pool)
        .await?;
    }

    // Move player to jail location.
    sqlx::query("UPDATE players SET location = 'Lochy' WHERE id = $1")
        .bind(prisoner_id)
        .execute(pool)
        .await?;

    Ok(())
}

/// Release a prisoner (delete jail record + move to Altara).
pub async fn release_prisoner(pool: &PgPool, prisoner_id: i32) -> Result<(), sqlx::Error> {
    sqlx::query("DELETE FROM jail WHERE prisoner = $1")
        .bind(prisoner_id)
        .execute(pool)
        .await?;
    sqlx::query("UPDATE players SET location = 'Altara' WHERE id = $1")
        .bind(prisoner_id)
        .execute(pool)
        .await?;
    Ok(())
}

/// Pay bail for a prisoner: deduct cost from payer, release prisoner.
pub async fn pay_bail(
    pool: &PgPool,
    jail_id: i32,
    payer_id: i32,
    cost: i32,
) -> Result<i32, sqlx::Error> {
    // Get prisoner id.
    let (prisoner_id,): (i32,) = sqlx::query_as("SELECT prisoner FROM jail WHERE id = $1")
        .bind(jail_id)
        .fetch_one(pool)
        .await?;

    // Deduct from payer.
    sqlx::query("UPDATE players SET credits = credits - $1 WHERE id = $2")
        .bind(cost)
        .bind(payer_id)
        .execute(pool)
        .await?;

    // Release prisoner.
    release_prisoner(pool, prisoner_id).await?;

    Ok(prisoner_id)
}

// ---------------------------------------------------------------------------
// Court documents (rules, cases, verdicts)
// ---------------------------------------------------------------------------

/// A row from the `court` table.
#[derive(sqlx::FromRow)]
pub struct CourtDocRow {
    pub id: i32,
    pub title: String,
    pub body: Option<String>,
    pub lang: Option<String>,
    pub kind: String,
    pub dated: Option<String>,
}

/// List court documents of a given kind.
pub async fn list_court_docs(pool: &PgPool, kind: &str) -> Result<Vec<CourtDocRow>, sqlx::Error> {
    sqlx::query_as::<_, CourtDocRow>(
        "SELECT id, title, '' AS body, lang, kind, \
                TO_CHAR(dated, 'YYYY-MM-DD') AS dated \
         FROM court WHERE kind = $1 ORDER BY id ASC",
    )
    .bind(kind)
    .fetch_all(pool)
    .await
}

/// Find a single court document by id.
pub async fn find_court_doc(
    pool: &PgPool,
    doc_id: i32,
) -> Result<Option<CourtDocRow>, sqlx::Error> {
    sqlx::query_as::<_, CourtDocRow>(
        "SELECT id, title, body, lang, kind, \
                TO_CHAR(dated, 'YYYY-MM-DD') AS dated \
         FROM court WHERE id = $1",
    )
    .bind(doc_id)
    .fetch_optional(pool)
    .await
}

/// Create a new court document.
pub async fn create_court_doc(
    pool: &PgPool,
    title: &str,
    body: &str,
    kind: &str,
) -> Result<(), sqlx::Error> {
    sqlx::query("INSERT INTO court (title, body, kind) VALUES ($1, $2, $3)")
        .bind(title)
        .bind(body)
        .bind(kind)
        .execute(pool)
        .await?;
    Ok(())
}

/// Update an existing court document.
pub async fn update_court_doc(
    pool: &PgPool,
    doc_id: i32,
    title: &str,
    body: &str,
) -> Result<(), sqlx::Error> {
    sqlx::query("UPDATE court SET title = $1, body = $2, dated = CURRENT_DATE WHERE id = $3")
        .bind(title)
        .bind(body)
        .bind(doc_id)
        .execute(pool)
        .await?;
    Ok(())
}

// ---------------------------------------------------------------------------
// Court case comments
// ---------------------------------------------------------------------------

/// A comment on a court case.
#[derive(sqlx::FromRow)]
pub struct CourtCommentRow {
    pub id: i32,
    pub author: String,
    pub body: String,
}

/// List comments for a court document.
pub async fn list_court_comments(
    pool: &PgPool,
    text_id: i32,
) -> Result<Vec<CourtCommentRow>, sqlx::Error> {
    sqlx::query_as::<_, CourtCommentRow>(
        "SELECT id, author, body FROM court_cases WHERE text_id = $1 ORDER BY id ASC",
    )
    .bind(text_id)
    .fetch_all(pool)
    .await
}

/// Add a comment to a court case.
pub async fn add_court_comment(
    pool: &PgPool,
    text_id: i32,
    author: &str,
    body: &str,
) -> Result<(), sqlx::Error> {
    sqlx::query("INSERT INTO court_cases (text_id, author, body) VALUES ($1, $2, $3)")
        .bind(text_id)
        .bind(author)
        .bind(body)
        .execute(pool)
        .await?;
    Ok(())
}

/// Delete a court case comment.
pub async fn delete_court_comment(pool: &PgPool, comment_id: i32) -> Result<(), sqlx::Error> {
    sqlx::query("DELETE FROM court_cases WHERE id = $1")
        .bind(comment_id)
        .execute(pool)
        .await?;
    Ok(())
}

// ---------------------------------------------------------------------------
// Court staff list (judges, aldermen, lawyers)
// ---------------------------------------------------------------------------

/// A minimal player row for court staff listings.
#[derive(sqlx::FromRow)]
pub struct CourtStaffRow {
    pub id: i32,
    pub username: String,
}

/// List players by rank.
pub async fn list_players_by_rank(
    pool: &PgPool,
    rank: &str,
) -> Result<Vec<CourtStaffRow>, sqlx::Error> {
    sqlx::query_as::<_, CourtStaffRow>(
        "SELECT id, username FROM players WHERE rank = $1 ORDER BY username ASC",
    )
    .bind(rank)
    .fetch_all(pool)
    .await
}

// ---------------------------------------------------------------------------
// Chat and forum bans
// ---------------------------------------------------------------------------

/// A chat or forum ban row.
#[derive(sqlx::FromRow)]
pub struct BanRow {
    pub id: Option<i32>,
    pub player: i32,
    pub resets: i32,
}

/// List all chat bans.
pub async fn list_chat_bans(pool: &PgPool) -> Result<Vec<BanRow>, sqlx::Error> {
    sqlx::query_as::<_, BanRow>("SELECT id, player, resets FROM chat_ban ORDER BY player ASC")
        .fetch_all(pool)
        .await
}

/// Ban a player from chat.
pub async fn ban_from_chat(pool: &PgPool, player_id: i32, resets: i32) -> Result<(), sqlx::Error> {
    sqlx::query(
        "INSERT INTO chat_ban (player, resets) VALUES ($1, $2) \
         ON CONFLICT (player) DO UPDATE SET resets = chat_ban.resets + $2",
    )
    .bind(player_id)
    .bind(resets)
    .execute(pool)
    .await?;
    Ok(())
}

/// Unban a player from chat.
pub async fn unban_from_chat(pool: &PgPool, player_id: i32) -> Result<(), sqlx::Error> {
    sqlx::query("DELETE FROM chat_ban WHERE player = $1")
        .bind(player_id)
        .execute(pool)
        .await?;
    Ok(())
}

/// List all forum bans.
pub async fn list_forum_bans(pool: &PgPool) -> Result<Vec<BanRow>, sqlx::Error> {
    sqlx::query_as::<_, BanRow>("SELECT id, player, resets FROM forum_ban ORDER BY player ASC")
        .fetch_all(pool)
        .await
}

/// Ban a player from forums.
pub async fn ban_from_forum(pool: &PgPool, player_id: i32, resets: i32) -> Result<(), sqlx::Error> {
    sqlx::query(
        "INSERT INTO forum_ban (player, resets) VALUES ($1, $2) \
         ON CONFLICT (player) DO UPDATE SET forum_ban.resets = forum_ban.resets + $2",
    )
    .bind(player_id)
    .bind(resets)
    .execute(pool)
    .await?;
    Ok(())
}

/// Unban a player from forums.
pub async fn unban_from_forum(pool: &PgPool, player_id: i32) -> Result<(), sqlx::Error> {
    sqlx::query("DELETE FROM forum_ban WHERE player = $1")
        .bind(player_id)
        .execute(pool)
        .await?;
    Ok(())
}

// ---------------------------------------------------------------------------
// Mail bans
// ---------------------------------------------------------------------------

/// List all admin-level mail bans (owner = 0).
pub async fn list_mail_bans(pool: &PgPool) -> Result<Vec<i32>, sqlx::Error> {
    let rows: Vec<(i32,)> =
        sqlx::query_as("SELECT player FROM mail_ban WHERE owner = 0 ORDER BY player ASC")
            .fetch_all(pool)
            .await?;
    Ok(rows.into_iter().map(|(p,)| p).collect())
}

/// Ban a player from sending mail (admin-level ban).
pub async fn ban_mail(pool: &PgPool, player_id: i32) -> Result<(), sqlx::Error> {
    sqlx::query("INSERT INTO mail_ban (player, owner) VALUES ($1, 0) ON CONFLICT DO NOTHING")
        .bind(player_id)
        .execute(pool)
        .await?;
    Ok(())
}

/// Unban a player from sending mail (admin-level ban).
pub async fn unban_mail(pool: &PgPool, player_id: i32) -> Result<(), sqlx::Error> {
    sqlx::query("DELETE FROM mail_ban WHERE player = $1 AND owner = 0")
        .bind(player_id)
        .execute(pool)
        .await?;
    Ok(())
}

// ---------------------------------------------------------------------------
// Administrative punishment: confiscate gold
// ---------------------------------------------------------------------------

/// Confiscate gold from one player. Half goes to the injured party, half
/// to the kingdom treasury.
pub async fn confiscate_gold(
    pool: &PgPool,
    offender_id: i32,
    injured_id: i32,
    amount: i32,
) -> Result<(), sqlx::Error> {
    let half = amount / 2;

    sqlx::query("UPDATE players SET credits = credits - $1 WHERE id = $2")
        .bind(amount)
        .bind(offender_id)
        .execute(pool)
        .await?;

    sqlx::query("UPDATE players SET credits = credits + $1 WHERE id = $2")
        .bind(half)
        .bind(injured_id)
        .execute(pool)
        .await?;

    // Remaining half goes to kingdom treasury.
    sqlx::query("UPDATE settings SET value = (value::int + $1)::text WHERE setting = 'gold'")
        .bind(amount - half)
        .execute(pool)
        .await?;

    Ok(())
}

// ---------------------------------------------------------------------------
// Judge panel: change player rank
// ---------------------------------------------------------------------------

/// Set a player's rank. Only specific ranks are allowed via the judge panel.
pub async fn set_player_rank(
    pool: &PgPool,
    player_id: i32,
    new_rank: &str,
) -> Result<(), sqlx::Error> {
    sqlx::query("UPDATE players SET rank = $1 WHERE id = $2")
        .bind(new_rank)
        .bind(player_id)
        .execute(pool)
        .await?;
    Ok(())
}

/// Get a player's current rank.
pub async fn get_player_rank(pool: &PgPool, player_id: i32) -> Result<Option<String>, sqlx::Error> {
    sqlx::query_scalar("SELECT rank FROM players WHERE id = $1")
        .bind(player_id)
        .fetch_optional(pool)
        .await
}

// ---------------------------------------------------------------------------
// Immunity toggle
// ---------------------------------------------------------------------------

/// Grant immunity to a player.
pub async fn grant_immunity(pool: &PgPool, player_id: i32) -> Result<(), sqlx::Error> {
    sqlx::query("UPDATE players SET immune = TRUE WHERE id = $1")
        .bind(player_id)
        .execute(pool)
        .await?;
    Ok(())
}

// ---------------------------------------------------------------------------
// Game log helper
// ---------------------------------------------------------------------------

/// Insert a game log entry for a player.
pub async fn insert_game_log(
    pool: &PgPool,
    owner_id: i32,
    message: &str,
    log_type: char,
) -> Result<(), sqlx::Error> {
    sqlx::query("INSERT INTO game_log (owner_id, message, log_type) VALUES ($1, $2, $3)")
        .bind(owner_id)
        .bind(message)
        .bind(log_type.to_string())
        .execute(pool)
        .await?;
    Ok(())
}
