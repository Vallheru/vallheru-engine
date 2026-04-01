//! Queries for global game settings.

use sqlx::PgPool;

/// A single key-value setting row.
#[derive(Debug, sqlx::FromRow)]
pub struct SettingRow {
    pub setting: String,
    pub value: Option<String>,
}

/// Fetch a setting by name.
pub async fn get_setting(pool: &PgPool, name: &str) -> sqlx::Result<Option<SettingRow>> {
    sqlx::query_as::<_, SettingRow>("SELECT setting, value FROM settings WHERE setting = $1")
        .bind(name)
        .fetch_optional(pool)
        .await
}

/// Upsert a setting.
pub async fn upsert_setting(pool: &PgPool, name: &str, value: &str) -> sqlx::Result<()> {
    sqlx::query(
        "INSERT INTO settings (setting, value) VALUES ($1, $2) \
         ON CONFLICT (setting) DO UPDATE SET value = EXCLUDED.value",
    )
    .bind(name)
    .bind(value)
    .execute(pool)
    .await?;
    Ok(())
}

/// Fetch multiple settings by name in one query.
pub async fn get_settings_batch(pool: &PgPool, names: &[&str]) -> sqlx::Result<Vec<SettingRow>> {
    let owned: Vec<String> = names.iter().map(|s| (*s).to_owned()).collect();
    sqlx::query_as::<_, SettingRow>("SELECT setting, value FROM settings WHERE setting = ANY($1)")
        .bind(&owned)
        .fetch_all(pool)
        .await
}

/// Atomically subtract from the kingdom gold setting. Returns `Err` if
/// the setting doesn't exist or can't be parsed.
pub async fn adjust_kingdom_gold(pool: &PgPool, delta: i64) -> sqlx::Result<()> {
    sqlx::query(
        "UPDATE settings SET value = (CAST(value AS BIGINT) + $1)::TEXT \
         WHERE setting = 'gold'",
    )
    .bind(delta)
    .execute(pool)
    .await?;
    Ok(())
}
