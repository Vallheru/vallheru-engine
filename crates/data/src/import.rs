//! Reference-data import from seed SQL files.
//!
//! Seed files live in the `seeds/` directory at the project root and are
//! embedded into the binary at compile time. Each file must be valid
//! Postgres SQL using `INSERT ... ON CONFLICT DO NOTHING` for idempotency.

use sqlx::PgPool;

/// One embedded seed file: name + SQL content.
struct Seed {
    name: &'static str,
    sql: &'static str,
}

/// All reference-data seed files, executed in order.
const SEEDS: &[Seed] = &[
    Seed {
        name: "settings",
        sql: include_str!("../../../seeds/001_settings.sql"),
    },
    Seed {
        name: "monsters",
        sql: include_str!("../../../seeds/002_monsters.sql"),
    },
    Seed {
        name: "bows",
        sql: include_str!("../../../seeds/003_bows.sql"),
    },
    Seed {
        name: "rings",
        sql: include_str!("../../../seeds/004_rings.sql"),
    },
    Seed {
        name: "tools",
        sql: include_str!("../../../seeds/005_tools.sql"),
    },
    Seed {
        name: "plans",
        sql: include_str!("../../../seeds/006_plans.sql"),
    },
    Seed {
        name: "bonuses",
        sql: include_str!("../../../seeds/007_bonuses.sql"),
    },
];

/// Run all embedded seed files against the database.
///
/// Each seed is executed as a single statement inside its own transaction.
/// Seeds use `ON CONFLICT DO NOTHING` so re-running is safe.
pub async fn run_seeds(database_url: &str) -> anyhow::Result<()> {
    let pool = sqlx::postgres::PgPoolOptions::new()
        .max_connections(1)
        .connect(database_url)
        .await?;

    tracing::info!("starting reference-data import ({} seeds)", SEEDS.len());

    for seed in SEEDS {
        import_seed(&pool, seed).await?;
    }

    tracing::info!("reference-data import complete");
    pool.close().await;
    Ok(())
}

async fn import_seed(pool: &PgPool, seed: &Seed) -> anyhow::Result<()> {
    // Count rows before to calculate delta.
    let table = seed.name;
    let before: (i64,) = sqlx::query_as(&format!("SELECT COUNT(*) FROM {table}"))
        .fetch_one(pool)
        .await?;

    // Execute the seed SQL inside a transaction.
    let mut tx = pool.begin().await?;
    sqlx::query(seed.sql).execute(&mut *tx).await?;
    tx.commit().await?;

    let after: (i64,) = sqlx::query_as(&format!("SELECT COUNT(*) FROM {table}"))
        .fetch_one(pool)
        .await?;

    let inserted = after.0 - before.0;
    tracing::info!(table, total = after.0, new = inserted, "seeded {table}");

    Ok(())
}
