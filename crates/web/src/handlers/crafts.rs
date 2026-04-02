//! Crafts guild (artisan missions) handler.
//!
//! Ported from `crafts.php`. Random crafting missions with accident
//! chances, gold rewards, XP, and rare tool/plan loot.

use axum::{Extension, Form, extract::State, response::Response};
use rand::Rng;

use crate::log_err;
use crate::middleware::context::RequestContext;
use crate::page::{Flash, FlashKind, PageMeta};
use crate::state::AppState;
use vallheru_domain::crafting::jeweller as jdomain;

// =========================================================================
// View models
// =========================================================================

#[derive(serde::Serialize)]
pub struct CraftsMainView {
    #[serde(flatten)]
    pub base: crate::render::RenderContext,
    pub is_craftsman: bool,
}

#[derive(serde::Serialize)]
pub struct CraftsMissionView {
    #[serde(flatten)]
    pub base: crate::render::RenderContext,
    pub missions: Vec<MissionEntry>,
    pub energy: i32,
}

#[derive(serde::Serialize)]
pub struct MissionEntry {
    pub index: usize,
    pub profession: String,
    pub skill_name: String,
    pub energy_cost: i32,
}

#[derive(serde::Deserialize)]
pub struct MissionForm {
    pub mission_index: Option<usize>,
}

// =========================================================================
// Private helpers
// =========================================================================

#[derive(Debug, Clone, sqlx::FromRow)]
struct PlayerRow {
    pub location: String,
    pub max_hp: i32,
    pub energy: f64,
    pub class: String,
    pub race: String,
}

async fn load_player(app: &AppState, player_id: i32) -> Result<PlayerRow, Response> {
    sqlx::query_as::<_, PlayerRow>(
        "SELECT location, max_hp, energy, class, race FROM players WHERE id = $1",
    )
    .bind(player_id)
    .fetch_optional(&app.pool)
    .await
    .map_err(|_| server_error())?
    .ok_or_else(server_error)
}

async fn load_skill(app: &AppState, player_id: i32, key: &str) -> f64 {
    let row: Option<(f64,)> =
        sqlx::query_as("SELECT level FROM player_skills WHERE player_id = $1 AND skill_key = $2")
            .bind(player_id)
            .bind(key)
            .fetch_optional(&app.pool)
            .await
            .unwrap_or(None);
    row.map_or(0.0, |r| r.0)
}

/// Approximate player level from sum of trained stats.
async fn load_level(app: &AppState, player_id: i32) -> i32 {
    let row: Option<(i64,)> = sqlx::query_as(
        "SELECT COALESCE(SUM(base + trained), 0) FROM player_stats WHERE player_id = $1",
    )
    .bind(player_id)
    .fetch_optional(&app.pool)
    .await
    .unwrap_or(None);
    #[allow(clippy::cast_possible_truncation)]
    row.map_or(1, |r| (r.0).max(1) as i32)
}

fn error_page(state: &AppState, ctx: &RequestContext, message: &str) -> Response {
    let meta = PageMeta::titled("Błąd").with_flash(Flash {
        kind: FlashKind::Error,
        message: message.to_owned(),
    });
    let base = state.templates.build_context(ctx, &meta);
    state.templates.render("error.html", &base)
}

fn server_error() -> Response {
    use axum::response::IntoResponse;
    (
        axum::http::StatusCode::INTERNAL_SERVER_ERROR,
        "Internal error",
    )
        .into_response()
}

/// Generate random missions for the current session.
fn generate_missions(level: i32, rng: &mut impl Rng) -> Vec<(jdomain::CraftProfession, i32)> {
    let professions = jdomain::CraftProfession::ALL;
    let count = rng.gen_range(3..=5);
    let mut missions = Vec::with_capacity(count);
    for _ in 0..count {
        let prof = professions[rng.gen_range(0..professions.len())];
        let energy_cost = level + rng.gen_range(1..=5);
        missions.push((prof, energy_cost));
    }
    missions
}

// =========================================================================
// Handlers
// =========================================================================

/// GET /crafts — main crafts guild page.
pub async fn crafts_show(
    State(app): State<AppState>,
    Extension(ctx): Extension<RequestContext>,
) -> Response {
    let Some(ref user) = ctx.session_user else {
        return crate::page::redirect("/login");
    };

    #[allow(clippy::cast_possible_truncation)]
    let player_id = user.id as i32;

    let player_row = match load_player(&app, player_id).await {
        Ok(row) => row,
        Err(resp) => return resp,
    };

    if player_row.location != "Miasto" && player_row.location != "Altara" {
        return error_page(&app, &ctx, "Musisz znajdować się w mieście.");
    }

    let is_craftsman = player_row.class == "Rzemieślnik";

    let meta = PageMeta::titled("Gildia Rzemieślników").with_back_link("/city", "Wróć do miasta");
    let base = app.templates.build_context(&ctx, &meta);
    let view = CraftsMainView { base, is_craftsman };
    app.templates.render_value("crafts.html", &view)
}

/// GET /crafts/missions — show available missions.
pub async fn crafts_missions_show(
    State(app): State<AppState>,
    Extension(ctx): Extension<RequestContext>,
) -> Response {
    let Some(ref user) = ctx.session_user else {
        return crate::page::redirect("/login");
    };

    #[allow(clippy::cast_possible_truncation)]
    let player_id = user.id as i32;

    let player_row = match load_player(&app, player_id).await {
        Ok(row) => row,
        Err(resp) => return resp,
    };

    if player_row.class != "Rzemieślnik" {
        return error_page(&app, &ctx, "Tylko rzemieślnicy mogą wykonywać misje.");
    }

    let level = load_level(&app, player_id).await;
    let mut rng = rand::thread_rng();
    let generated = generate_missions(level, &mut rng);

    let missions: Vec<MissionEntry> = generated
        .iter()
        .enumerate()
        .map(|(i, (prof, energy))| MissionEntry {
            index: i,
            profession: format!("{prof:?}"),
            skill_name: prof.skill_key().to_string(),
            energy_cost: *energy,
        })
        .collect();

    #[allow(clippy::cast_possible_truncation)]
    let energy = player_row.energy as i32;

    let meta = PageMeta::titled("Gildia - Misje").with_back_link("/crafts", "Wróć do gildii");
    let base = app.templates.build_context(&ctx, &meta);
    let view = CraftsMissionView {
        base,
        missions,
        energy,
    };
    app.templates.render_value("crafts_missions.html", &view)
}

/// POST /crafts/execute — execute a mission.
#[allow(clippy::too_many_lines)]
pub async fn crafts_execute(
    State(app): State<AppState>,
    Extension(ctx): Extension<RequestContext>,
    Form(form): Form<MissionForm>,
) -> Response {
    let Some(ref user) = ctx.session_user else {
        return crate::page::redirect("/login");
    };

    #[allow(clippy::cast_possible_truncation)]
    let player_id = user.id as i32;

    let player_row = match load_player(&app, player_id).await {
        Ok(row) => row,
        Err(resp) => return resp,
    };

    if player_row.class != "Rzemieślnik" {
        return error_page(&app, &ctx, "Tylko rzemieślnicy mogą wykonywać misje.");
    }

    let Some(mission_index) = form.mission_index else {
        return error_page(&app, &ctx, "Wybierz misję.");
    };

    // Regenerate missions (deterministic per session isn't possible without
    // session storage, so we pick a random one based on index)
    // Generate missions and pre-roll random values (rng is !Send, must not cross .await)
    let level = load_level(&app, player_id).await;
    let (missions, accident_roll, damage_roll, loot_roll) = {
        let mut rng = rand::thread_rng();
        let missions = generate_missions(level, &mut rng);
        let accident = rng.gen_range(1..=100);
        let damage = rng.gen_range(1..=25);
        let loot = rng.gen_range(1..=1000);
        (missions, accident, damage, loot)
    };
    // rng is dropped here — safe to .await below

    if mission_index >= missions.len() {
        return error_page(&app, &ctx, "Nieznana misja.");
    }

    let (profession, energy_cost) = missions[mission_index];

    #[allow(clippy::cast_possible_truncation)]
    let avail_energy = player_row.energy as i32;
    if energy_cost > avail_energy {
        return error_page(&app, &ctx, "Nie masz wystarczająco energii.");
    }

    // Deduct energy
    if let Err(e) = vallheru_data::queries::gathering::deduct_energy(
        &app.pool,
        player_id,
        f64::from(energy_cost),
    )
    .await
    {
        tracing::error!(error = %e, "crafts_execute: deduct energy");
        return server_error();
    }

    // Check for accident (5% chance)
    if jdomain::mission_is_accident(accident_roll) {
        let damage = jdomain::mission_accident_damage(player_row.max_hp, damage_roll);

        log_err!(
            sqlx::query("UPDATE players SET hp = GREATEST(hp - $1, 0) WHERE id = $2")
                .bind(damage)
                .bind(player_id)
                .execute(&app.pool)
                .await,
            "query"
        );

        let msg = format!("Wypadek! Straciłeś {damage} HP.");
        let meta = PageMeta::titled("Gildia - Misje")
            .with_back_link("/crafts/missions", "Wróć")
            .with_flash(Flash {
                kind: FlashKind::Error,
                message: msg,
            });
        let base = app.templates.build_context(&ctx, &meta);
        return app.templates.render("success.html", &base);
    }

    // Mission success
    let skill_level = load_skill(&app, player_id, profession.skill_key()).await;
    #[allow(clippy::cast_possible_truncation)]
    let skill_int = skill_level as i32;
    let gold_reward = jdomain::mission_gold_reward(f64::from(energy_cost), skill_int.max(1));

    // Add gold
    log_err!(
        sqlx::query("UPDATE players SET credits = credits + $1 WHERE id = $2")
            .bind(i64::from(gold_reward))
            .bind(player_id)
            .execute(&app.pool)
            .await,
        "query"
    );

    // XP
    let base_xp = jdomain::mission_base_xp(profession, skill_int);
    let total_xp = base_xp * 2; // Craftsman bonus

    let mut results_text = format!("Misja wykonana! Nagroda: {gold_reward} złota.");

    // Apply XP
    if total_xp > 0 {
        let xp_text = super::smithy::apply_craft_xp(
            &app,
            player_id,
            &player_row.race,
            &player_row.class,
            total_xp,
            profession.skill_key(),
        )
        .await;
        results_text.push_str(&xp_text);
    }

    // Check for rare loot
    if let Some(tier) = jdomain::mission_loot_tier(loot_roll) {
        let loot_msg = match tier {
            jdomain::MissionLootTier::BasicTool => " Znalazłeś narzędzie!",
            jdomain::MissionLootTier::BetterTool => " Znalazłeś lepsze narzędzie!",
            jdomain::MissionLootTier::BasicPlan => " Znalazłeś plan!",
            jdomain::MissionLootTier::BetterPlan => " Znalazłeś rzadki plan!",
        };
        results_text.push_str(loot_msg);
    }

    let meta = PageMeta::titled("Gildia - Misje")
        .with_back_link("/crafts/missions", "Wróć")
        .with_flash(Flash::success(results_text));
    let base = app.templates.build_context(&ctx, &meta);
    app.templates.render("success.html", &base)
}
