//! Era reset: wipe all gameplay data while preserving player accounts.
//!
//! The reset runs inside a single transaction:
//! 1. Save account-level data from `players` into a temp table.
//! 2. Truncate all gameplay tables (CASCADE handles FK dependencies).
//! 3. Re-insert preserved player accounts with default gameplay state.
//! 4. Reset game settings to defaults.

use sqlx::PgPool;

/// Tables that hold gameplay state and should be truncated during era reset.
///
/// Order does not matter because we use CASCADE.
const GAMEPLAY_TABLES: &[&str] = &[
    "activations",
    "amarket",
    "astral",
    "astral_bank",
    "astral_plans",
    "bans",
    "bug_comments",
    "bugreport",
    "character_resets",
    "chat_ban",
    "chat_bans",
    "chat_messages",
    "chronicle_missions",
    "content_comments",
    "core_market",
    "court",
    "court_cases",
    "equipment",
    "farm",
    "farms",
    "forum_ban",
    "forum_bans",
    "forum_replies",
    "forum_topics",
    "game_log",
    "game_log_daily",
    "game_updates",
    "herbs",
    "hmarket",
    "houses",
    "jail",
    "jeweller",
    "jeweller_work",
    "lumberjack",
    "mail_ban",
    "mail_blocks",
    "mail_contacts",
    "mail_messages",
    "minerals",
    "mines",
    "mines_search",
    "news",
    "newspaper_articles",
    "notes",
    "password_resets",
    "player_bonuses",
    "player_skills",
    "player_stats",
    "pmarket",
    "poll_options",
    "polls",
    "proposals",
    "questaction",
    "revent",
    "room_messages",
    "rooms",
    "sessions",
    "smelter",
    "smith",
    "smith_work",
    "tribe_oczek",
    "tribe_perm",
    "tribe_rank",
    "tribe_replies",
    "tribe_topics",
    "tribes",
    "vallar_history",
];

/// Tables that store owned items — delete player-owned rows (owner > 0)
/// while keeping catalog entries (owner = 0).
const OWNED_ITEM_TABLES: &[(&str, &str)] = &[("spells", "gracz"), ("potions", "owner")];

/// Run the full era reset inside a transaction.
pub async fn run_era_reset(pool: &PgPool) -> anyhow::Result<()> {
    let mut tx = pool.begin().await?;

    // Step 1: Save account data.
    tracing::info!("era-reset: saving account data");
    sqlx::query(
        "CREATE TEMP TABLE era_backup AS
         SELECT id, username, email, pass_hash, rank, age, logins,
                profile, avatar, vallars, roleplay, ooc, short_rpg, settings
         FROM players",
    )
    .execute(&mut *tx)
    .await?;

    let count: (i64,) = sqlx::query_as("SELECT count(*) FROM era_backup")
        .fetch_one(&mut *tx)
        .await?;
    tracing::info!(accounts = count.0, "era-reset: accounts backed up");

    // Step 2: Truncate gameplay tables.
    tracing::info!("era-reset: truncating gameplay tables");
    for table in GAMEPLAY_TABLES {
        let sql = format!("TRUNCATE TABLE {table} CASCADE");
        sqlx::query(&sql).execute(&mut *tx).await?;
    }

    // Step 3: Truncate players (will cascade to FK-dependent tables).
    tracing::info!("era-reset: truncating players");
    sqlx::query("TRUNCATE TABLE players CASCADE")
        .execute(&mut *tx)
        .await?;

    // Step 4: Delete player-owned items but keep catalog rows.
    for (table, col) in OWNED_ITEM_TABLES {
        let sql = format!("DELETE FROM {table} WHERE {col} > 0");
        sqlx::query(&sql).execute(&mut *tx).await?;
    }

    // Step 5: Re-insert preserved accounts with fresh gameplay defaults.
    tracing::info!("era-reset: restoring player accounts");
    sqlx::query(
        "INSERT INTO players (username, email, pass_hash, rank, age, logins,
                profile, avatar, vallars, roleplay, ooc, short_rpg, settings)
         SELECT username, email, pass_hash, rank, age + 1, logins,
                profile, avatar, vallars, roleplay, ooc, short_rpg, settings
         FROM era_backup
         ORDER BY id",
    )
    .execute(&mut *tx)
    .await?;

    // Step 6: Reset global settings.
    tracing::info!("era-reset: resetting game settings");
    sqlx::query("UPDATE settings SET value = '1' WHERE setting = 'day'")
        .execute(&mut *tx)
        .await?;
    sqlx::query("UPDATE settings SET value = '' WHERE setting = 'item'")
        .execute(&mut *tx)
        .await?;
    sqlx::query("UPDATE settings SET value = '' WHERE setting = 'player'")
        .execute(&mut *tx)
        .await?;
    sqlx::query("UPDATE settings SET value = '' WHERE setting = 'tribe'")
        .execute(&mut *tx)
        .await?;

    // Step 7: Drop temp table and commit.
    sqlx::query("DROP TABLE IF EXISTS era_backup")
        .execute(&mut *tx)
        .await?;

    tx.commit().await?;
    tracing::info!(accounts = count.0, "era-reset: complete");
    Ok(())
}
