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
