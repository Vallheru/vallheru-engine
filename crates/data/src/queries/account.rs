//! Account activation and password-reset database queries.

use sqlx::PgPool;

// ---------------------------------------------------------------------------
// Activation
// ---------------------------------------------------------------------------

/// A pending activation record.
#[derive(Debug, sqlx::FromRow)]
pub struct ActivationRow {
    pub id: i32,
    pub username: String,
    pub email: String,
    pub pass_hash: String,
    pub referrer: i32,
    pub ip: String,
    pub game_type: String,
}

/// Look up a pending activation by its integer token.
pub async fn find_activation_by_token(
    pool: &PgPool,
    token: i32,
) -> Result<Option<ActivationRow>, sqlx::Error> {
    sqlx::query_as::<_, ActivationRow>(
        "SELECT id, username, email, pass_hash, referrer, ip, game_type \
         FROM activations WHERE token = $1",
    )
    .bind(token)
    .fetch_optional(pool)
    .await
}

/// Create a new player from an activation record and delete the activation.
///
/// Runs both statements inside a transaction.
pub async fn activate_player(
    pool: &PgPool,
    activation: &ActivationRow,
    settings_raw: &str,
) -> Result<(), sqlx::Error> {
    let mut tx = pool.begin().await?;

    sqlx::query(
        "INSERT INTO players (username, email, pass_hash, referrals, ip, settings_raw) \
         VALUES ($1, $2, $3, $4, $5, $6)",
    )
    .bind(&activation.username)
    .bind(&activation.email)
    .bind(&activation.pass_hash)
    .bind(activation.referrer)
    .bind(&activation.ip)
    .bind(settings_raw)
    .execute(&mut *tx)
    .await?;

    sqlx::query("DELETE FROM activations WHERE id = $1")
        .bind(activation.id)
        .execute(&mut *tx)
        .await?;

    tx.commit().await
}

// ---------------------------------------------------------------------------
// Password reset
// ---------------------------------------------------------------------------

/// Insert a password-reset record with a time-bounded token.
pub async fn insert_password_reset(
    pool: &PgPool,
    token: &str,
    email: &str,
    new_pass_hash: &str,
    player_id: i32,
) -> Result<(), sqlx::Error> {
    sqlx::query(
        "INSERT INTO password_resets (token, email, new_pass, player_id) \
         VALUES ($1, $2, $3, $4)",
    )
    .bind(token)
    .bind(email)
    .bind(new_pass_hash)
    .bind(player_id)
    .execute(pool)
    .await?;
    Ok(())
}

/// Row returned when looking up a password-reset record.
#[derive(Debug, sqlx::FromRow)]
pub struct PasswordResetRow {
    pub new_pass: String,
    pub player_id: i32,
}

/// Find a non-expired password-reset record by token and email.
pub async fn find_password_reset(
    pool: &PgPool,
    token: &str,
    email: &str,
) -> Result<Option<PasswordResetRow>, sqlx::Error> {
    sqlx::query_as::<_, PasswordResetRow>(
        "SELECT new_pass, player_id FROM password_resets \
         WHERE token = $1 AND email = $2 AND expires_at > NOW()",
    )
    .bind(token)
    .bind(email)
    .fetch_optional(pool)
    .await
}

/// Apply the password reset: update the player and delete the reset record.
///
/// Runs both statements in a transaction.
pub async fn apply_password_reset(
    pool: &PgPool,
    token: &str,
    email: &str,
    reset: &PasswordResetRow,
) -> Result<(), sqlx::Error> {
    let mut tx = pool.begin().await?;

    sqlx::query("UPDATE players SET pass_hash = $1 WHERE id = $2 AND email = $3")
        .bind(&reset.new_pass)
        .bind(reset.player_id)
        .bind(email)
        .execute(&mut *tx)
        .await?;

    sqlx::query("DELETE FROM password_resets WHERE token = $1 AND email = $2 AND player_id = $3")
        .bind(token)
        .bind(email)
        .bind(reset.player_id)
        .execute(&mut *tx)
        .await?;

    tx.commit().await
}

/// Find a player ID by email (for password-reset requests).
pub async fn find_player_id_by_email(
    pool: &PgPool,
    email: &str,
) -> Result<Option<i32>, sqlx::Error> {
    let row: Option<(i32,)> = sqlx::query_as("SELECT id FROM players WHERE email = $1")
        .bind(email)
        .fetch_optional(pool)
        .await?;
    Ok(row.map(|(id,)| id))
}

/// Purge expired password-reset records.
pub async fn purge_expired_resets(pool: &PgPool) -> Result<u64, sqlx::Error> {
    let result = sqlx::query("DELETE FROM password_resets WHERE expires_at <= NOW()")
        .execute(pool)
        .await?;
    Ok(result.rows_affected())
}
