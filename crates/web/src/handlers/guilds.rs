//! Guild leaderboard handlers — crafts and gladiator rankings.
//!
//! Ported from `guilds.php` (crafts) and `guilds2.php` (gladiator).
//! Read-only displays: no writes, no forms.

use axum::Extension;
use axum::extract::State;
use axum::response::Response;

use crate::middleware::context::RequestContext;
use crate::page::PageMeta;
use crate::state::AppState;

// =========================================================================
// View models
// =========================================================================

#[derive(serde::Serialize)]
pub struct GuildsView {
    #[serde(flatten)]
    pub base: crate::render::RenderContext,
    pub categories: Vec<GuildCategory>,
}

#[derive(serde::Serialize)]
pub struct GuildCategory {
    pub title: String,
    pub description: String,
    pub entries: Vec<GuildEntry>,
}

#[derive(serde::Serialize)]
pub struct GuildEntry {
    pub rank: usize,
    pub player_name: String,
    pub player_id: i32,
    pub value: i64,
}

#[derive(serde::Serialize)]
pub struct GladiatorView {
    #[serde(flatten)]
    pub base: crate::render::RenderContext,
    pub combat_categories: Vec<GuildCategory>,
    pub mission_top: Vec<GuildEntry>,
    pub records: Vec<BattleRecord>,
}

#[derive(serde::Serialize)]
pub struct BattleRecord {
    pub monster: String,
    pub killer: String,
    pub date: String,
}

// =========================================================================
// Skill leaderboard query
// =========================================================================

/// Fetch top-N players for a given `skill_key` from `player_skills`,
/// decorated with tribe tags.
async fn top_by_skill(
    app: &AppState,
    skill_key: &str,
    limit: i64,
) -> Result<Vec<GuildEntry>, sqlx::Error> {
    let rows: Vec<(i32, String, i64, String, String)> = sqlx::query_as(
        "SELECT p.id, p.username, COALESCE(ps.level, 0)::BIGINT,
                COALESCE(t.prefix, ''), COALESCE(t.suffix, '')
         FROM players p
         LEFT JOIN player_skills ps ON ps.player_id = p.id AND ps.skill_key = $1
         LEFT JOIN tribes t ON t.id = p.tribe_id
         ORDER BY COALESCE(ps.level, 0) DESC
         LIMIT $2",
    )
    .bind(skill_key)
    .bind(limit)
    .fetch_all(&app.pool)
    .await?;

    Ok(rows
        .into_iter()
        .enumerate()
        .map(|(i, (pid, name, val, pre, suf))| {
            let display = format!("{pre} {name} {suf}").trim().to_owned();
            GuildEntry {
                rank: i + 1,
                player_name: display,
                player_id: pid,
                value: val,
            }
        })
        .collect())
}

/// Fetch top-N crafters by `mpoints`.
async fn top_by_mpoints(app: &AppState, limit: i64) -> Result<Vec<GuildEntry>, sqlx::Error> {
    let rows: Vec<(i32, String, i64, String, String)> = sqlx::query_as(
        "SELECT p.id, p.username, p.mpoints::BIGINT,
                COALESCE(t.prefix, ''), COALESCE(t.suffix, '')
         FROM players p
         LEFT JOIN tribes t ON t.id = p.tribe_id
         WHERE p.class = 'Rzemieślnik'
         ORDER BY p.mpoints DESC
         LIMIT $1",
    )
    .bind(limit)
    .fetch_all(&app.pool)
    .await?;

    Ok(rows
        .into_iter()
        .enumerate()
        .map(|(i, (pid, name, val, pre, suf))| {
            let display = format!("{pre} {name} {suf}").trim().to_owned();
            GuildEntry {
                rank: i + 1,
                player_name: display,
                player_id: pid,
                value: val,
            }
        })
        .collect())
}

// =========================================================================
// Handlers
// =========================================================================

/// GET /guilds — crafts guild leaderboard.
pub async fn guilds_crafts(
    State(app): State<AppState>,
    Extension(ctx): Extension<RequestContext>,
) -> Response {
    let skill_keys = [
        ("smith", "Najwyższe Kowalstwo", "Kowalstwo"),
        ("carpentry", "Najwyższe Stolarstwo", "Stolarstwo"),
        ("alchemy", "Najwyższa Alchemia", "Alchemia"),
        ("herbalism", "Najwyższe Zielarstwo", "Zielarstwo"),
        ("jewellry", "Najwyższe Jubilerstwo", "Jubilerstwo"),
        ("breeding", "Najwyższa Hodowla", "Hodowla"),
        ("mining", "Najwyższe Górnictwo", "Górnictwo"),
        ("lumberjack", "Najwyższe Drwalnictwo", "Drwalnictwo"),
        ("smelting", "Najwyższe Hutnictwo", "Hutnictwo"),
    ];

    let mut categories = Vec::with_capacity(skill_keys.len() + 1);
    for (key, title, desc) in skill_keys {
        let entries = top_by_skill(&app, key, 10).await.unwrap_or_default();
        categories.push(GuildCategory {
            title: title.to_owned(),
            description: desc.to_owned(),
            entries,
        });
    }

    // Mission points (mpoints) — crafters only
    let mp_entries = top_by_mpoints(&app, 10).await.unwrap_or_default();
    categories.push(GuildCategory {
        title: "Wykonanych Zadań".to_owned(),
        description: "Zadań".to_owned(),
        entries: mp_entries,
    });

    let meta = PageMeta::titled("Gildia Rzemieślników").with_back_link("/city", "Wróć do miasta");
    let base = app.templates.build_context(&ctx, &meta);
    let view = GuildsView { base, categories };
    app.templates.render_value("guilds.html", &view)
}

/// GET /guilds/gladiator — gladiator hall leaderboard.
pub async fn guilds_gladiator(
    State(app): State<AppState>,
    Extension(ctx): Extension<RequestContext>,
) -> Response {
    let combat_keys = [
        ("melee", "Najwyższy Atak", "Atak"),
        ("defense", "Najwyższa Obrona", "Obrona"),
        ("magic", "Najwyższa Magia", "Magia"),
        ("archery", "Najwyższa Łucznictwo", "Łucznictwo"),
    ];

    let mut combat_categories = Vec::with_capacity(combat_keys.len());
    for (key, title, desc) in combat_keys {
        let entries = top_by_skill(&app, key, 10).await.unwrap_or_default();
        combat_categories.push(GuildCategory {
            title: title.to_owned(),
            description: desc.to_owned(),
            entries,
        });
    }

    let mission_top = top_by_mpoints(&app, 10).await.unwrap_or_default();

    // Battle records (first kills)
    let record_rows: Vec<(String, String, String)> = sqlx::query_as(
        "SELECT monster, killer, COALESCE(TO_CHAR(killed_at, 'YYYY-MM-DD'), '') \
         FROM brecords ORDER BY id",
    )
    .fetch_all(&app.pool)
    .await
    .unwrap_or_default();

    let records: Vec<BattleRecord> = record_rows
        .into_iter()
        .map(|(monster, killer, date)| BattleRecord {
            monster,
            killer,
            date,
        })
        .collect();

    let meta = PageMeta::titled("Sala Gladiatorów").with_back_link("/city", "Wróć do miasta");
    let base = app.templates.build_context(&ctx, &meta);
    let view = GladiatorView {
        base,
        combat_categories,
        mission_top,
        records,
    };
    app.templates.render_value("guilds_gladiator.html", &view)
}
