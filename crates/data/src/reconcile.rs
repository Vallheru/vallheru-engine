//! Data reconciliation between the legacy and target databases.
//!
//! Compares row counts for migrated tables and reports mismatches grouped
//! by severity. Intended as a pre-cutover gate.

use std::fmt;

/// A table comparison entry.
struct TableCheck {
    table: &'static str,
    pg_count: i64,
    mysql_count: Option<i64>,
    severity: Severity,
}

/// Mismatch severity.
#[derive(Clone, Copy, PartialEq, Eq, PartialOrd, Ord)]
enum Severity {
    Ok,
    Info,
    Warning,
    Error,
}

impl fmt::Display for Severity {
    fn fmt(&self, f: &mut fmt::Formatter<'_>) -> fmt::Result {
        match self {
            Self::Ok => write!(f, "  OK  "),
            Self::Info => write!(f, " INFO "),
            Self::Warning => write!(f, " WARN "),
            Self::Error => write!(f, "ERROR "),
        }
    }
}

/// Tables to reconcile and their expected minimum row counts.
/// The legacy table name may differ from the PG table name.
struct TableSpec {
    pg_table: &'static str,
    mysql_table: &'static str,
    min_expected: i64,
}

/// Shorthand constructor for readability.
const fn t(pg: &'static str, mysql: &'static str, min: i64) -> TableSpec {
    TableSpec {
        pg_table: pg,
        mysql_table: mysql,
        min_expected: min,
    }
}

const TABLES: &[TableSpec] = &[
    // ── Reference / catalog tables ──────────────────────────────
    t("settings", "settings", 40),
    t("monsters", "monsters", 100),
    t("bows", "bows", 25),
    t("rings", "rings", 5),
    t("tools", "tools", 50),
    t("plans", "plans", 50),
    t("bonuses", "bonuses", 40),
    t("spells", "spells", 10),
    t("mage_items", "mage_items", 5),
    t("potions", "potions", 5),
    t("herbs", "herbs", 5),
    t("minerals", "minerals", 5),
    t("core", "core", 5),
    // ── Player tables ───────────────────────────────────────────
    t("players", "players", 1),
    t("player_stats", "player_stats", 0),
    t("player_skills", "player_skills", 0),
    t("player_bonuses", "player_bonuses", 0),
    t("equipment", "equipment", 0),
    // ── Economy ─────────────────────────────────────────────────
    t("amarket", "amarket", 0),
    t("hmarket", "hmarket", 0),
    t("pmarket", "pmarket", 0),
    t("core_market", "core_market", 0),
    // ── Gathering & crafting ────────────────────────────────────
    t("mines", "mines", 0),
    t("mines_search", "mines_search", 0),
    t("smelter", "smelter", 0),
    t("lumberjack", "lumberjack", 0),
    t("farms", "farms", 0),
    t("smith", "smith", 0),
    t("smith_work", "smith_work", 0),
    t("jeweller", "jeweller", 0),
    t("jeweller_work", "jeweller_work", 0),
    t("astral_bank", "astral_bank", 0),
    t("astral_plans", "astral_plans", 0),
    // ── Social ──────────────────────────────────────────────────
    t("chat_messages", "chat_messages", 0),
    t("rooms", "rooms", 0),
    t("room_messages", "room_messages", 0),
    t("mail_messages", "mail_messages", 0),
    t("forum_categories", "forum_categories", 0),
    t("forum_topics", "forum_topics", 0),
    t("forum_replies", "forum_replies", 0),
    // ── Content ─────────────────────────────────────────────────
    t("news", "news", 0),
    t("game_updates", "game_updates", 0),
    t("newspaper_articles", "newspaper_articles", 0),
    t("polls", "polls", 0),
    t("notes", "notes", 0),
    t("library_texts", "library_texts", 0),
    t("chronicle_missions", "chronicle_missions", 0),
    t("donators", "donators", 0),
    // ── Housing ─────────────────────────────────────────────────
    t("houses", "houses", 0),
    // ── Tribes ──────────────────────────────────────────────────
    t("tribes", "tribes", 0),
    t("tribe_oczek", "tribe_oczek", 0),
    t("tribe_topics", "tribe_topics", 0),
    t("tribe_replies", "tribe_replies", 0),
    // ── Quests ──────────────────────────────────────────────────
    t("quests", "quests", 0),
    t("questaction", "questaction", 0),
    // ── Outposts ────────────────────────────────────────────────
    t("outposts", "outposts", 0),
    t("outpost_monsters", "outpost_monsters", 0),
    t("outpost_veterans", "outpost_veterans", 0),
    // ── Moderation ──────────────────────────────────────────────
    t("bugreport", "bugreport", 0),
    t("court_cases", "court_cases", 0),
    t("jail", "jail", 0),
    t("game_log", "game_log", 0),
];

/// Run reconciliation and print a comparison report.
pub async fn run_reconciliation(pg_url: &str, mysql_url: Option<&str>) -> anyhow::Result<()> {
    let pg_pool = sqlx::postgres::PgPoolOptions::new()
        .max_connections(1)
        .connect(pg_url)
        .await?;

    let mysql_pool = if let Some(url) = mysql_url {
        Some(
            sqlx::mysql::MySqlPoolOptions::new()
                .max_connections(1)
                .connect(url)
                .await?,
        )
    } else {
        None
    };

    tracing::info!(
        pg = true,
        mysql = mysql_pool.is_some(),
        "starting reconciliation ({} tables)",
        TABLES.len()
    );

    let mut checks = Vec::with_capacity(TABLES.len());

    for spec in TABLES {
        let pg_count = count_pg(&pg_pool, spec.pg_table).await?;

        let mysql_count = if let Some(ref pool) = mysql_pool {
            Some(count_mysql(pool, spec.mysql_table).await?)
        } else {
            None
        };

        let severity = classify(pg_count, mysql_count, spec.min_expected);

        checks.push(TableCheck {
            table: spec.pg_table,
            pg_count,
            mysql_count,
            severity,
        });
    }

    print_report(&checks);

    pg_pool.close().await;
    if let Some(pool) = mysql_pool {
        pool.close().await;
    }

    let errors = checks
        .iter()
        .filter(|c| c.severity == Severity::Error)
        .count();
    if errors > 0 {
        anyhow::bail!("{errors} table(s) have ERROR-level mismatches");
    }

    Ok(())
}

async fn count_pg(pool: &sqlx::PgPool, table: &str) -> anyhow::Result<i64> {
    // Table names come from the compile-time TABLES constant, not user input.
    let sql = format!("SELECT COUNT(*) FROM {table}");
    let row: (i64,) = sqlx::query_as(&sql).fetch_one(pool).await?;
    Ok(row.0)
}

async fn count_mysql(pool: &sqlx::MySqlPool, table: &str) -> anyhow::Result<i64> {
    let sql = format!("SELECT COUNT(*) FROM `{table}`");
    let row: (i64,) = sqlx::query_as(&sql).fetch_one(pool).await?;
    Ok(row.0)
}

fn classify(pg: i64, mysql: Option<i64>, min_expected: i64) -> Severity {
    if let Some(my) = mysql {
        if pg == my {
            return Severity::Ok;
        }
        // Allow PG to have more rows (seed data might differ slightly).
        if pg > my {
            return Severity::Info;
        }
        // PG has fewer rows — significant gap is a warning/error.
        // Row counts are small enough that precision loss is irrelevant.
        #[allow(clippy::cast_precision_loss)]
        let ratio = pg as f64 / my as f64;
        if ratio < 0.9 {
            return Severity::Error;
        }
        return Severity::Warning;
    }
    // No MySQL connection — check against minimum expected.
    if pg >= min_expected {
        Severity::Ok
    } else if pg == 0 && min_expected > 0 {
        Severity::Error
    } else {
        Severity::Warning
    }
}

fn print_report(checks: &[TableCheck]) {
    println!();
    println!("╔══════════════════════════════════════════════════════════════════╗");
    println!("║               Data Reconciliation Report                       ║");
    println!("╠════════════════════╦══════════╦══════════╦══════════╦═══════════╣");
    println!("║ Table              ║ PG Rows  ║ MY Rows  ║  Delta   ║ Severity  ║");
    println!("╠════════════════════╬══════════╬══════════╬══════════╬═══════════╣");

    for check in checks {
        let my_str = check
            .mysql_count
            .map_or_else(|| "  n/a   ".to_owned(), |c| format!("{c:>8}"));
        let delta_str = check.mysql_count.map_or_else(
            || "  n/a   ".to_owned(),
            |my| {
                let d = check.pg_count - my;
                format!("{d:>+8}")
            },
        );
        println!(
            "║ {:<18} ║ {:>8} ║ {} ║ {} ║ {} ║",
            check.table, check.pg_count, my_str, delta_str, check.severity
        );
    }

    println!("╚════════════════════╩══════════╩══════════╩══════════╩═══════════╝");

    let summary = |sev: Severity| checks.iter().filter(|c| c.severity == sev).count();
    println!();
    println!(
        "Summary: {} OK, {} INFO, {} WARN, {} ERROR",
        summary(Severity::Ok),
        summary(Severity::Info),
        summary(Severity::Warning),
        summary(Severity::Error),
    );
    println!();
}
