//! Scheduled job execution.
//!
//! Each job acquires a `PostgreSQL` advisory lock to prevent concurrent
//! execution, then runs the appropriate SQL statements.  If the lock
//! cannot be acquired (another instance is running), the job is skipped.

use sqlx::PgPool;
use tracing::{info, warn};
use vallheru_domain::admin::reset::Job;
use vallheru_domain::combat::hunter_quest::{
    HunterQuestType, QuestGenInputs, generate_quest, serialize_quest,
};

/// Run a scheduled job, protected by an advisory lock.
///
/// Returns `Ok(true)` if the job ran, `Ok(false)` if it was skipped
/// (lock held by another process), or an error on failure.
pub async fn run_job(pool: &PgPool, job: Job) -> anyhow::Result<bool> {
    let lock_key = job.advisory_lock_key();

    // Try to acquire a session-level advisory lock (non-blocking).
    let acquired: bool = sqlx::query_scalar("SELECT pg_try_advisory_lock($1)")
        .bind(lock_key)
        .fetch_one(pool)
        .await?;

    if !acquired {
        warn!(job = %job, "skipping — another instance holds the lock");
        return Ok(false);
    }

    info!(job = %job, "executing");

    let result = match job {
        Job::EnergyTick => energy_tick(pool).await,
        Job::DailyReset => daily_reset(pool).await,
    };

    // Release the advisory lock regardless of success/failure.
    let _ = sqlx::query("SELECT pg_advisory_unlock($1)")
        .bind(lock_key)
        .execute(pool)
        .await;

    result?;
    info!(job = %job, "completed");
    Ok(true)
}

// ---------------------------------------------------------------------------
// Energy tick — runs every ~20 minutes
// ---------------------------------------------------------------------------

/// Regenerate energy for active, non-frozen players.
///
/// PHP: `UPDATE players SET energy=energy+(max_energy/72) WHERE miejsce!='Lochy'
///       AND freeze=0 AND rasa!='' AND klasa!='' AND energy<(21*max_energy)`
async fn energy_tick(pool: &PgPool) -> anyhow::Result<()> {
    let rows = sqlx::query(
        "UPDATE players SET energy = energy + (max_energy::float8 / 72.0) \
         WHERE location != 'Lochy' \
           AND \"freeze\" = 0 \
           AND race IS NOT NULL AND race != '' \
           AND class IS NOT NULL AND class != '' \
           AND energy < (21.0 * max_energy::float8)",
    )
    .execute(pool)
    .await?;

    info!(affected = rows.rows_affected(), "energy tick applied");
    Ok(())
}

// ---------------------------------------------------------------------------
// Daily reset — runs once per day
// ---------------------------------------------------------------------------

/// Full daily reset mirroring PHP `mainreset()` + `smallreset()`.
///
/// This is broken into sequenced steps. Each step logs its effect.
/// The whole reset runs in a single connection but NOT a single transaction —
/// matching the PHP behaviour where each statement executes independently.
#[allow(clippy::too_many_lines)]
async fn daily_reset(pool: &PgPool) -> anyhow::Result<()> {
    // --- Sub-reset steps (from smallreset) ---

    // Clear events table
    sqlx::query("TRUNCATE TABLE events").execute(pool).await?;
    info!("cleared events");

    // Clear attacks table
    sqlx::query("TRUNCATE TABLE attacks").execute(pool).await?;
    info!("cleared attacks");

    // Grow herbs — age all plants, remove old ones
    sqlx::query("UPDATE farm SET age = age + 1")
        .execute(pool)
        .await?;
    sqlx::query("DELETE FROM farm WHERE age > 26")
        .execute(pool)
        .await?;
    info!("aged farm plants");

    // Restock potions with random amounts (1–50)
    sqlx::query(
        "UPDATE potions SET amount = floor(random() * 50 + 1)::int \
         WHERE owner = 0",
    )
    .execute(pool)
    .await?;
    info!("restocked potions");

    // Jail countdown — decrement durations and free expired prisoners
    sqlx::query("UPDATE jail SET duration = duration - 1")
        .execute(pool)
        .await?;
    // Free those whose sentence is up
    sqlx::query(
        "UPDATE players SET location = 'Altara' \
         WHERE id IN (SELECT prisoner FROM jail WHERE duration <= 0)",
    )
    .execute(pool)
    .await?;
    sqlx::query("DELETE FROM jail WHERE duration <= 0")
        .execute(pool)
        .await?;
    info!("processed jail sentences");

    // Chat ban countdown
    sqlx::query("UPDATE chat_bans SET resets = resets - 1")
        .execute(pool)
        .await?;
    sqlx::query("DELETE FROM chat_bans WHERE resets <= 0")
        .execute(pool)
        .await?;
    info!("processed chat bans");

    // Forum ban countdown
    sqlx::query("UPDATE forum_bans SET resets = resets - 1")
        .execute(pool)
        .await?;
    sqlx::query("DELETE FROM forum_bans WHERE resets <= 0")
        .execute(pool)
        .await?;
    info!("processed forum bans");

    // Remove poisons from equipment
    sqlx::query(
        "UPDATE equipment SET name = regexp_replace(name, '^Zatruty ', ''), \
         poison = 0, ptype = '' WHERE poison > 0 AND type != 'R'",
    )
    .execute(pool)
    .await?;
    info!("cleaned poisoned equipment");

    // Outpost daily updates
    sqlx::query("UPDATE outposts SET turns = turns + 2, fatigue = 100, attacks = 0")
        .execute(pool)
        .await?;
    info!("reset outpost turns");

    // Tribe attack flags
    sqlx::query("UPDATE tribes SET attack = 'N'")
        .execute(pool)
        .await?;

    // House points
    sqlx::query("UPDATE houses SET points = points + 2")
        .execute(pool)
        .await?;

    // Energy tick (included in daily reset)
    energy_tick(pool).await?;

    // Reset maps setting
    sqlx::query("UPDATE settings SET value = '20' WHERE setting = 'maps'")
        .execute(pool)
        .await?;

    // Restock rings in shop (add 1-4, cap at 28)
    sqlx::query(
        "UPDATE rings SET amount = LEAST(amount + floor(random() * 4 + 1)::int, 28) \
         WHERE amount < 28",
    )
    .execute(pool)
    .await?;
    info!("restocked rings");

    // --- Main reset steps (from mainreset) ---

    // Age players, heal, reset daily flags
    let rows = sqlx::query(
        "UPDATE players SET \
         age = age + 1, \
         hp = max_hp, \
         bridge = 'N', \
         house_rest = 'N', \
         craft_mission = 7",
    )
    .execute(pool)
    .await?;
    info!(affected = rows.rows_affected(), "daily player reset");

    // Decrement newbie protection
    sqlx::query("UPDATE players SET newbie = newbie - 1 WHERE newbie > 0")
        .execute(pool)
        .await?;

    // Core pass training bonus
    sqlx::query(
        "UPDATE players SET trains = trains + 15 \
         WHERE core_pass = true AND \"freeze\" = 0",
    )
    .execute(pool)
    .await?;

    // Decrement freeze counters
    sqlx::query(
        "UPDATE players SET \"freeze\" = \"freeze\" - 1 \
         WHERE \"freeze\" > 0",
    )
    .execute(pool)
    .await?;

    // Thief crime increment
    sqlx::query(
        "UPDATE players SET crime = crime + 1, astral_crime = true \
         WHERE class = 'Złodziej' AND \"freeze\" = 0",
    )
    .execute(pool)
    .await?;

    // Room rental countdown and cleanup
    sqlx::query("UPDATE rooms SET days = days - 1")
        .execute(pool)
        .await?;
    sqlx::query(
        "UPDATE players SET room = 0 \
         WHERE room IN (SELECT id FROM rooms WHERE days <= 0)",
    )
    .execute(pool)
    .await?;
    sqlx::query("DELETE FROM chatrooms WHERE room IN (SELECT id FROM rooms WHERE days <= 0)")
        .execute(pool)
        .await?;
    sqlx::query("DELETE FROM rooms WHERE days <= 0")
        .execute(pool)
        .await?;
    info!("processed room rentals");

    // Generate daily hunter quests for both cities
    generate_hunter_quests(pool).await?;

    // Random event processing (countdown + resolution)
    process_random_events(pool).await?;

    // Reopen game
    sqlx::query("UPDATE settings SET value = 'Y' WHERE setting = 'open'")
        .execute(pool)
        .await?;
    sqlx::query("UPDATE settings SET value = '' WHERE setting = 'close_reason'")
        .execute(pool)
        .await?;

    info!("daily reset complete");
    Ok(())
}

// ---------------------------------------------------------------------------
// Hunter quest generation — part of daily reset
// ---------------------------------------------------------------------------

/// Generate a new hunter quest for each city.
///
/// For each city (Altara, Ardulith):
/// 1. Pick a random quest type (F/I/L/B/P).
/// 2. Pick a random target (monster or item catalog entry).
/// 3. Store the quest string and completion count in the settings table.
///
/// Note: PHP had the random type selection commented out and hardcoded to 'I'.
/// We implement the full random selection as originally designed.
async fn generate_hunter_quests(pool: &PgPool) -> anyhow::Result<()> {
    use rand::Rng;

    for city in &["Altara", "Ardulith"] {
        let city_lower = city.to_ascii_lowercase();
        let quest_key = format!("hunter{city_lower}");
        let amount_key = format!("hunter{city_lower}amount");

        let quest_str = {
            let mut rng = rand::thread_rng();
            let type_roll: usize = rng.gen_range(0..5);
            let quest_type = match type_roll {
                0 => HunterQuestType::Fight,
                1 => HunterQuestType::Item,
                2 => HunterQuestType::Loot,
                3 => HunterQuestType::Bow,
                _ => HunterQuestType::Potion,
            };

            let target_ids = match quest_type {
                HunterQuestType::Fight => {
                    crate::queries::event::monster_ids_by_location(pool, city).await?
                }
                HunterQuestType::Item => crate::queries::event::catalog_equipment_ids(pool).await?,
                HunterQuestType::Bow => crate::queries::event::catalog_bow_ids(pool).await?,
                HunterQuestType::Potion => crate::queries::event::catalog_potion_ids(pool).await?,
                HunterQuestType::Loot => {
                    crate::queries::event::monster_ids_with_loot(pool, city).await?
                }
            };

            if target_ids.is_empty() {
                String::new()
            } else {
                let mut rng = rand::thread_rng();
                let target_id = target_ids[rng.gen_range(0..target_ids.len())];
                let amount_roll: i32 = rng.gen_range(1..=10);
                let material_roll: usize = rng.gen_range(0..5);
                let loot_index: i32 = rng.gen_range(0..=3);

                let inputs = QuestGenInputs {
                    type_roll,
                    target_id,
                    amount_roll,
                    material_roll,
                    loot_index_roll: loot_index,
                };

                match generate_quest(&inputs) {
                    Some(q) => serialize_quest(&q),
                    None => String::new(),
                }
            }
        };

        let completions = {
            let mut rng = rand::thread_rng();
            rng.gen_range(1..=20)
        };

        crate::queries::settings::upsert_setting(pool, &quest_key, &quest_str).await?;
        crate::queries::settings::upsert_setting(pool, &amount_key, &completions.to_string())
            .await?;
    }

    info!("generated hunter quests");
    Ok(())
}

// ---------------------------------------------------------------------------
// Random event resolution — part of daily reset
// ---------------------------------------------------------------------------

/// Process random event countdowns and resolve completed events.
///
/// Mirrors PHP `smallreset()` revent processing:
/// - Decrement qtime for active events
/// - Resolve expired events based on state
async fn process_random_events(pool: &PgPool) -> anyhow::Result<()> {
    // Decrement countdowns
    sqlx::query("UPDATE revent SET qtime = qtime - 1 WHERE qtime > 1")
        .execute(pool)
        .await?;

    // Resolve expired events (qtime <= 1)
    // State 2: Unfinished delivery — remove quest item
    sqlx::query(
        "DELETE FROM equipment \
         WHERE name = 'Solidna sakiewka' AND type = 'Q' \
           AND owner IN (SELECT pid FROM revent WHERE qtime <= 1 AND state = 2)",
    )
    .execute(pool)
    .await?;

    // State 3: Finished delivery — award gold to bank
    sqlx::query(
        "UPDATE players SET bank = bank + floor(random() * 7001 + 1000)::int \
         WHERE id IN (SELECT pid FROM revent WHERE qtime <= 1 AND state = 3)",
    )
    .execute(pool)
    .await?;

    // State 4: Item sold/disposed — punishment handled per-player
    resolve_item_sold_punishments(pool).await?;

    // State 7: Gave money to beggar — possible veteran recruitment
    resolve_beggar_rewards(pool).await?;

    // Clean up all resolved events
    sqlx::query("DELETE FROM revent WHERE qtime <= 1")
        .execute(pool)
        .await?;

    info!("processed random events");
    Ok(())
}

/// Resolve state 4 (item sold) punishments at reset time.
///
/// 50/50 chance per player: jail or bandit attack.
async fn resolve_item_sold_punishments(pool: &PgPool) -> anyhow::Result<()> {
    use rand::Rng;

    let rows = sqlx::query_as::<_, (i32,)>("SELECT pid FROM revent WHERE qtime <= 1 AND state = 4")
        .fetch_all(pool)
        .await?;

    for &(pid,) in &rows {
        let roll: i32 = { rand::thread_rng().gen_range(0..=1) };

        if roll == 0 {
            // Jail: check if already in jail, extend or create sentence
            let in_jail: bool =
                sqlx::query_scalar("SELECT location = 'Lochy' FROM players WHERE id = $1")
                    .bind(pid)
                    .fetch_one(pool)
                    .await
                    .unwrap_or(false);

            let jail_cost: i32 = {
                let mut rng = rand::thread_rng();
                rng.gen_range(1..=100) * 10000
            };

            if in_jail {
                sqlx::query("UPDATE jail SET duration = duration + 18 WHERE prisoner = $1")
                    .bind(pid)
                    .execute(pool)
                    .await?;
            } else {
                sqlx::query(
                    "INSERT INTO jail (prisoner, verdict, duration, cost) \
                     VALUES ($1, 'Okradzenie poborcy podatkowego.', 18, $2)",
                )
                .bind(pid)
                .bind(jail_cost)
                .execute(pool)
                .await?;
                sqlx::query("UPDATE players SET location = 'Lochy' WHERE id = $1")
                    .bind(pid)
                    .execute(pool)
                    .await?;
            }
        } else {
            // Bandit attack: hp=0, credits=0, bank halved
            sqlx::query("UPDATE players SET hp = 0, credits = 0, bank = bank / 2 WHERE id = $1")
                .bind(pid)
                .execute(pool)
                .await?;
        }
    }

    if !rows.is_empty() {
        info!(count = rows.len(), "resolved item-sold punishments");
    }
    Ok(())
}

/// Resolve state 7 (gave money to beggar) rewards at reset time.
///
/// If the player has an outpost with barracks and room for veterans,
/// a new veteran is recruited.
async fn resolve_beggar_rewards(pool: &PgPool) -> anyhow::Result<()> {
    let rows = sqlx::query_as::<_, (i32,)>("SELECT pid FROM revent WHERE qtime <= 1 AND state = 7")
        .fetch_all(pool)
        .await?;

    for &(pid,) in &rows {
        // Check if player has an outpost with barracks
        let outpost: Option<(i32, i32)> =
            sqlx::query_as("SELECT id, barracks FROM outposts WHERE owner = $1 AND barracks > 0")
                .bind(pid)
                .fetch_optional(pool)
                .await?;

        if let Some((outpost_id, barracks_cap)) = outpost {
            let current: i64 =
                sqlx::query_scalar("SELECT count(id) FROM outpost_veterans WHERE outpost = $1")
                    .bind(outpost_id)
                    .fetch_one(pool)
                    .await
                    .unwrap_or(0);

            if current < i64::from(barracks_cap) {
                let name = format!("Weteran nr {current}");
                sqlx::query("INSERT INTO outpost_veterans (outpost, name) VALUES ($1, $2)")
                    .bind(outpost_id)
                    .bind(&name)
                    .execute(pool)
                    .await?;
            }
        }
    }

    Ok(())
}
