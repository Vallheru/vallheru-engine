//! Thieves guild handler.
//!
//! Ported from `thieves.php`. Covers thief missions, lockpick shop, and
//! monument robbery.

use axum::{Extension, Form, extract::State, response::Response};
use rand::Rng;

use crate::log_err;
use crate::middleware::context::RequestContext;
use crate::page::{Flash, FlashKind, PageMeta};
use crate::state::AppState;

// =========================================================================
// View models
// =========================================================================

#[derive(serde::Serialize)]
pub struct ThievesMainView {
    #[serde(flatten)]
    pub base: crate::render::RenderContext,
    pub is_thief: bool,
    pub mpoints: i32,
}

#[derive(serde::Serialize)]
pub struct ThievesMissionsView {
    #[serde(flatten)]
    pub base: crate::render::RenderContext,
    pub missions: Vec<MissionEntry>,
    pub energy: i32,
    pub lockpicks: i32,
}

#[derive(serde::Serialize)]
pub struct MissionEntry {
    pub key: String,
    pub name: String,
    pub energy_cost: i32,
    pub min_mpoints: i32,
}

#[derive(serde::Serialize)]
pub struct ThievesShopView {
    #[serde(flatten)]
    pub base: crate::render::RenderContext,
    pub lockpick_cost: i32,
    pub gold: i32,
    pub lockpicks: i32,
}

#[derive(serde::Deserialize)]
pub struct MissionForm {
    pub mission: Option<String>,
}

#[derive(serde::Deserialize)]
pub struct ShopForm {
    pub amount: Option<i32>,
}

// =========================================================================
// Constants
// =========================================================================

const LOCKPICK_COST: i32 = 200;

// =========================================================================
// Private helpers
// =========================================================================

#[derive(Debug, Clone, sqlx::FromRow)]
struct PlayerRow {
    pub location: String,
    pub max_hp: i32,
    pub energy: f64,
    pub credits: i32,
    pub class: String,
    pub race: String,
    pub mpoints: i32,
}

async fn load_player(app: &AppState, player_id: i32) -> Result<PlayerRow, Response> {
    sqlx::query_as::<_, PlayerRow>(
        "SELECT location, max_hp, energy, credits, class, race, mpoints FROM players WHERE id = $1",
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
            .unwrap_or_else(|e| {
                tracing::error!(error = %e, player_id, "load_skill: fetch_optional failed");
                None
            });
    row.map_or(0.0, |r| r.0)
}

async fn load_level(app: &AppState, player_id: i32) -> i32 {
    let row: Option<(i64,)> = sqlx::query_as(
        "SELECT COALESCE(SUM(base + trained), 0) FROM player_stats WHERE player_id = $1",
    )
    .bind(player_id)
    .fetch_optional(&app.pool)
    .await
    .unwrap_or_else(|e| {
        tracing::error!(error = %e, player_id, "load_level: fetch_optional failed");
        None
    });
    #[allow(clippy::cast_possible_truncation)]
    row.map_or(1, |r| (r.0).max(1) as i32)
}

async fn load_lockpicks(app: &AppState, player_id: i32) -> i32 {
    // Lockpicks stored in equipment as tools
    let row: Option<(i64,)> = sqlx::query_as(
        "SELECT COALESCE(SUM(amount), 0) FROM equipment WHERE owner = $1 AND name = 'Wytrych' AND type = 'E'",
    )
    .bind(player_id)
    .fetch_optional(&app.pool)
    .await
    .unwrap_or_else(|e| {
        tracing::error!(error = %e, player_id, "load_lockpicks: fetch_optional failed");
        None
    });
    #[allow(clippy::cast_possible_truncation)]
    row.map_or(0, |r| r.0 as i32)
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

// =========================================================================
// Handlers
// =========================================================================

/// GET /thieves — main thieves guild page.
pub async fn thieves_show(
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

    let is_thief = player_row.class == "Złodziej";

    let meta = PageMeta::titled("Gildia Złodziei").with_back_link("/city", "Wróć do miasta");
    let base = app.templates.build_context(&ctx, &meta);
    let view = ThievesMainView {
        base,
        is_thief,
        mpoints: player_row.mpoints,
    };
    app.templates.render_value("thieves.html", &view)
}

/// GET /thieves/missions — show available missions.
pub async fn thieves_missions_show(
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

    if player_row.class != "Złodziej" {
        return error_page(&app, &ctx, "Tylko złodzieje mogą wykonywać misje.");
    }

    let lockpicks = load_lockpicks(&app, player_id).await;
    let level = load_level(&app, player_id).await;

    let missions = vec![
        MissionEntry {
            key: "pickpocket".to_string(),
            name: "Kradzież kieszonkowa".to_string(),
            energy_cost: level,
            min_mpoints: 0,
        },
        MissionEntry {
            key: "tracking".to_string(),
            name: "Śledzenie".to_string(),
            energy_cost: level + 2,
            min_mpoints: 0,
        },
        MissionEntry {
            key: "guard".to_string(),
            name: "Służba wartownicza".to_string(),
            energy_cost: level + 3,
            min_mpoints: 5,
        },
        MissionEntry {
            key: "robbery".to_string(),
            name: "Włamanie do domu".to_string(),
            energy_cost: level + 5,
            min_mpoints: 10,
        },
    ];

    #[allow(clippy::cast_possible_truncation)]
    let energy = player_row.energy as i32;

    let meta = PageMeta::titled("Złodzieje - Misje").with_back_link("/thieves", "Wróć do gildii");
    let base = app.templates.build_context(&ctx, &meta);
    let view = ThievesMissionsView {
        base,
        missions,
        energy,
        lockpicks,
    };
    app.templates.render_value("thieves_missions.html", &view)
}

/// POST /thieves/execute — execute a mission.
#[allow(clippy::too_many_lines)]
pub async fn thieves_execute(
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

    if player_row.class != "Złodziej" {
        return error_page(&app, &ctx, "Tylko złodzieje mogą wykonywać misje.");
    }

    let mission_key = match form.mission.as_deref() {
        Some(k) if !k.is_empty() => k,
        _ => return error_page(&app, &ctx, "Wybierz misję."),
    };

    let level = load_level(&app, player_id).await;
    let (energy_cost, min_mpoints) = match mission_key {
        "pickpocket" => (level, 0),
        "tracking" => (level + 2, 0),
        "guard" => (level + 3, 5),
        "robbery" => (level + 5, 10),
        _ => return error_page(&app, &ctx, "Nieznana misja."),
    };

    if player_row.mpoints < min_mpoints {
        return error_page(
            &app,
            &ctx,
            &format!("Potrzebujesz co najmniej {min_mpoints} punktów misji."),
        );
    }

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
        tracing::error!(error = %e, "thieves_execute: deduct energy");
        return server_error();
    }

    // Pre-roll random values (rng is !Send, must not cross .await)
    let (roll, damage_roll) = {
        let mut rng = rand::thread_rng();
        (
            rng.gen_range(1..=100i32),
            rng.gen_range(1..=player_row.max_hp / 10 + 1),
        )
    };
    // rng is dropped here

    let thieving_skill = load_skill(&app, player_id, "thieving").await;

    // Success chance: (thieving_skill + speed_stat) / 2, capped at 90
    let speed = load_skill(&app, player_id, "speed").await;
    #[allow(clippy::cast_possible_truncation)]
    let chance = f64::midpoint(thieving_skill, speed) as i32;
    let chance = chance.min(90);

    let msg = if roll <= chance {
        // Success: gold and XP
        #[allow(clippy::cast_possible_truncation)]
        let skill_int = thieving_skill as i32;
        let gold_reward = energy_cost * skill_int.max(1) * 15;

        log_err!(
            sqlx::query(
                "UPDATE players SET credits = credits + $1, mpoints = mpoints + 1 WHERE id = $2",
            )
            .bind(i64::from(gold_reward))
            .bind(player_id)
            .execute(&app.pool)
            .await,
            "query"
        );

        let total_xp = energy_cost * 5;
        let mut result = format!("Misja udana! Nagroda: {gold_reward} złota.");

        if total_xp > 0 {
            let xp_text = super::smithy::apply_craft_xp(
                &app,
                player_id,
                &player_row.race,
                &player_row.class,
                total_xp,
                "thieving",
            )
            .await;
            result.push_str(&xp_text);
        }

        result
    } else {
        // Failure — may take damage
        let damage = damage_roll;
        log_err!(
            sqlx::query("UPDATE players SET hp = GREATEST(hp - $1, 0) WHERE id = $2")
                .bind(damage)
                .bind(player_id)
                .execute(&app.pool)
                .await,
            "query"
        );

        format!("Misja nie powiodła się! Straciłeś {damage} HP.")
    };

    let flash_kind = if roll <= chance {
        FlashKind::Success
    } else {
        FlashKind::Error
    };

    let meta = PageMeta::titled("Złodzieje - Misje")
        .with_back_link("/thieves/missions", "Wróć")
        .with_flash(Flash {
            kind: flash_kind,
            message: msg,
        });
    let base = app.templates.build_context(&ctx, &meta);
    app.templates.render("success.html", &base)
}

/// GET /thieves/shop — lockpick shop.
pub async fn thieves_shop_show(
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

    let lockpicks = load_lockpicks(&app, player_id).await;

    let meta = PageMeta::titled("Złodzieje - Sklep").with_back_link("/thieves", "Wróć do gildii");
    let base = app.templates.build_context(&ctx, &meta);
    let view = ThievesShopView {
        base,
        lockpick_cost: LOCKPICK_COST,
        gold: player_row.credits,
        lockpicks,
    };
    app.templates.render_value("thieves_shop.html", &view)
}

/// POST /thieves/shop/buy — buy lockpicks.
pub async fn thieves_shop_buy(
    State(app): State<AppState>,
    Extension(ctx): Extension<RequestContext>,
    Form(form): Form<ShopForm>,
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

    let amount = match form.amount {
        Some(a) if a > 0 => a,
        _ => return error_page(&app, &ctx, "Podaj ilość."),
    };

    #[allow(clippy::cast_possible_truncation)]
    let total_cost_i32 = (i64::from(LOCKPICK_COST) * i64::from(amount)) as i32;
    let total_cost = i64::from(total_cost_i32);
    if i64::from(player_row.credits) < total_cost {
        return error_page(&app, &ctx, "Nie masz wystarczająco złota.");
    }

    // Deduct gold
    if let Err(e) =
        vallheru_data::queries::gathering::deduct_currency(&app.pool, player_id, total_cost_i32, 0)
            .await
    {
        tracing::error!(error = %e, "thieves shop: deduct gold");
        return server_error();
    }

    // Add lockpicks (as equipment tool items)
    if let Err(e) = vallheru_data::queries::crafting::insert_crafted_item(
        &app.pool,
        player_id,
        "Wytrych",
        1,                             // power
        "E",                           // item_type (equipment)
        i64::from(LOCKPICK_COST) / 10, // cost (sell value)
        0,                             // min_level
        0,
        0,
        0,
        0,     // agi, dur, speed, max_dur
        false, // two_handed
        0,     // repair_cost
    )
    .await
    {
        tracing::error!(error = %e, "thieves shop: add lockpicks");
        return server_error();
    }

    let msg = format!("Kupiłeś {amount} wytrychów.");
    let meta = PageMeta::titled("Złodzieje - Sklep")
        .with_back_link("/thieves/shop", "Wróć")
        .with_flash(Flash::success(msg));
    let base = app.templates.build_context(&ctx, &meta);
    app.templates.render("success.html", &base)
}
