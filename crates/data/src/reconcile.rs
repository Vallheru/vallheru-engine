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

const TABLES: &[TableSpec] = &[
    TableSpec {
        pg_table: "settings",
        mysql_table: "settings",
        min_expected: 40,
    },
    TableSpec {
        pg_table: "monsters",
        mysql_table: "monsters",
        min_expected: 100,
    },
    TableSpec {
        pg_table: "bows",
        mysql_table: "bows",
        min_expected: 25,
    },
    TableSpec {
        pg_table: "rings",
        mysql_table: "rings",
        min_expected: 5,
    },
    TableSpec {
        pg_table: "tools",
        mysql_table: "tools",
        min_expected: 50,
    },
    TableSpec {
        pg_table: "plans",
        mysql_table: "plans",
        min_expected: 50,
    },
    TableSpec {
        pg_table: "bonuses",
        mysql_table: "bonuses",
        min_expected: 40,
    },
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
    if pg == 0 {
        return Severity::Error;
    }
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
    } else {
        Severity::Warning
    }
}

fn print_report(checks: &[TableCheck]) {
    println!();
    println!("╔══════════════════════════════════════════════════════════╗");
    println!("║             Data Reconciliation Report                  ║");
    println!("╠════════════╦══════════╦══════════╦══════════╦═══════════╣");
    println!("║ Table      ║ PG Rows  ║ MY Rows  ║  Delta   ║ Severity  ║");
    println!("╠════════════╬══════════╬══════════╬══════════╬═══════════╣");

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
            "║ {:<10} ║ {:>8} ║ {} ║ {} ║ {} ║",
            check.table, check.pg_count, my_str, delta_str, check.severity
        );
    }

    println!("╚════════════╩══════════╩══════════╩══════════╩═══════════╝");

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
