//! Queries for global game settings.

use sqlx::PgPool;

/// A single key-value setting row.
#[derive(Debug, sqlx::FromRow)]
pub struct SettingRow {
    pub key: String,
    pub value: String,
}

/// Fetch a setting by key.
pub async fn get_setting(pool: &PgPool, key: &str) -> sqlx::Result<Option<SettingRow>> {
    sqlx::query_as::<_, SettingRow>("SELECT key, value FROM settings WHERE key = $1")
        .bind(key)
        .fetch_optional(pool)
        .await
}

/// Upsert a setting.
pub async fn upsert_setting(pool: &PgPool, key: &str, value: &str) -> sqlx::Result<()> {
    sqlx::query(
        "INSERT INTO settings (key, value) VALUES ($1, $2) \
         ON CONFLICT (key) DO UPDATE SET value = EXCLUDED.value",
    )
    .bind(key)
    .bind(value)
    .execute(pool)
    .await?;
    Ok(())
}
