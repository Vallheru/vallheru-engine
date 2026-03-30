//! Hospital handler — healing and resurrection.
//!
//! Ported from `hospital.php` and `includes/resurect.php`.

use axum::{
    Extension,
    extract::{Query, State},
    response::Response,
};
use rand::Rng;

use crate::middleware::context::RequestContext;
use crate::page::{Flash, FlashKind, PageMeta};
use crate::state::AppState;
use vallheru_domain::hospital;
use vallheru_domain::location::Location;

// ---------------------------------------------------------------------------
// View model
// ---------------------------------------------------------------------------

#[derive(serde::Serialize)]
pub struct HospitalView {
    #[serde(flatten)]
    pub base: crate::render::RenderContext,
    /// Template state: `offer_heal`, `offer_resurrect`, or empty (after action done).
    pub state: &'static str,
    /// Gold cost for healing or resurrection.
    pub cost: i32,
    /// Whether tribe has hospital discount.
    pub tribe_discount: bool,
}

#[derive(serde::Deserialize)]
pub struct HospitalQuery {
    #[serde(default)]
    pub action: Option<String>,
}

// ---------------------------------------------------------------------------
// Handlers
// ---------------------------------------------------------------------------

/// GET /hospital — dispatches based on `?action=` query parameter.
pub async fn hospital_page(
    State(app): State<AppState>,
    Extension(ctx): Extension<RequestContext>,
    Query(query): Query<HospitalQuery>,
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

    let Some(location) = Location::from_db(&player_row.location) else {
        return crate::page::redirect("/login");
    };

    if !location.is_city() {
        return error_page(&app, &ctx, "Nie znajdujesz się w mieście.");
    }

    match query.action.as_deref() {
        Some("heal") => hospital_heal(&app, &ctx, player_id, &player_row).await,
        Some("resurrect") => hospital_resurrect(&app, &ctx, player_id, &player_row).await,
        _ => hospital_show(&app, &ctx, player_id, &player_row).await,
    }
}

/// Show healing or resurrection offer.
async fn hospital_show(
    app: &AppState,
    ctx: &RequestContext,
    player_id: i32,
    player_row: &vallheru_data::queries::player::PlayerRow,
) -> Response {
    if player_row.hp >= player_row.max_hp {
        return error_page(
            app,
            ctx,
            "Nie potrzebujesz leczenia (<a href=\"/city\">Wróć do miasta</a>).",
        );
    }

    let tribe_discount =
        vallheru_data::queries::locations::has_hospital_pass(&app.pool, player_row.tribe_id)
            .await
            .unwrap_or(false);

    if player_row.hp <= 0 {
        let condition = load_condition_trained(app, player_id).await;
        let cost = hospital::resurrection_cost(condition);

        if cost > player_row.credits {
            return error_page(
                app,
                ctx,
                &format!(
                    "Nie możesz zostać wskrzeszony. Potrzebujesz <b>{cost}</b> sztuk złota. \
                     (<a href=\"/city\">Wróć</a>)"
                ),
            );
        }

        let meta = PageMeta::titled("Szpital");
        let base = app.templates.build_context(ctx, &meta);
        let view = HospitalView {
            base,
            state: "offer_resurrect",
            cost,
            tribe_discount,
        };
        return app.templates.render_value("hospital.html", &view);
    }

    // Alive but not full HP — offer healing.
    let cost = hospital::healing_cost(player_row.hp, player_row.max_hp, tribe_discount);

    if cost > player_row.credits {
        return error_page(
            app,
            ctx,
            &format!(
                "Nie możesz być wyleczony. Potrzebujesz <b>{cost}</b> sztuk złota. \
                 (<a href=\"/city\">Wróć</a>)"
            ),
        );
    }

    let meta = PageMeta::titled("Szpital");
    let base = app.templates.build_context(ctx, &meta);
    let view = HospitalView {
        base,
        state: "offer_heal",
        cost,
        tribe_discount,
    };
    app.templates.render_value("hospital.html", &view)
}

/// Perform healing.
async fn hospital_heal(
    app: &AppState,
    ctx: &RequestContext,
    player_id: i32,
    player_row: &vallheru_data::queries::player::PlayerRow,
) -> Response {
    if player_row.hp <= 0 {
        return error_page(app, ctx, "Musisz być wskrzeszony, nie uleczony.");
    }

    if player_row.hp >= player_row.max_hp {
        return error_page(app, ctx, "Nie potrzebujesz leczenia.");
    }

    let tribe_discount =
        vallheru_data::queries::locations::has_hospital_pass(&app.pool, player_row.tribe_id)
            .await
            .unwrap_or(false);

    let cost = hospital::healing_cost(player_row.hp, player_row.max_hp, tribe_discount);

    if cost > player_row.credits {
        return error_page(
            app,
            ctx,
            &format!(
                "Nie możesz być wyleczony. Potrzebujesz <b>{cost}</b> sztuk złota. \
                 (<a href=\"/city\">Wróć</a>)"
            ),
        );
    }

    if let Err(e) = vallheru_data::queries::locations::heal_player(&app.pool, player_id, cost).await
    {
        tracing::error!(error = %e, "hospital heal failed");
        return server_error();
    }

    let meta = PageMeta::titled("Szpital").with_flash(Flash::success(
        "Jesteś kompletnie wyleczony. (<a href=\"/city\">Wróć</a>)".to_owned(),
    ));
    let base = app.templates.build_context(ctx, &meta);
    let view = HospitalView {
        base,
        state: "",
        cost: 0,
        tribe_discount,
    };
    app.templates.render_value("hospital.html", &view)
}

/// Perform resurrection.
async fn hospital_resurrect(
    app: &AppState,
    ctx: &RequestContext,
    player_id: i32,
    player_row: &vallheru_data::queries::player::PlayerRow,
) -> Response {
    if player_row.hp > 0 {
        return error_page(app, ctx, "Nie potrzebujesz wskrzeszenia.");
    }

    do_resurrect(app, ctx, player_id, player_row).await
}

// ---------------------------------------------------------------------------
// Shared resurrection logic (hospital + hermit)
// ---------------------------------------------------------------------------

/// Execute the resurrection process for a dead player.
///
/// Used by both the hospital and the hermit (forest/mountains). The caller
/// is responsible for verifying that the player is dead and in the correct
/// location.
pub async fn do_resurrect(
    app: &AppState,
    ctx: &RequestContext,
    player_id: i32,
    player_row: &vallheru_data::queries::player::PlayerRow,
) -> Response {
    let Some(race) = vallheru_domain::player::Race::from_db(&player_row.race) else {
        return error_page(app, ctx, "Nieprawidłowa rasa.");
    };
    let Some(class) = vallheru_domain::player::Class::from_db(&player_row.class) else {
        return error_page(app, ctx, "Nieprawidłowa klasa.");
    };

    let mut stats = vallheru_data::queries::player::load_stats(&app.pool, player_id)
        .await
        .unwrap_or_default();
    let mut skills = vallheru_data::queries::player::load_skills(&app.pool, player_id)
        .await
        .unwrap_or_default();

    // Scope the RNG so `ThreadRng` (!Send) is dropped before any `.await`.
    let (roll, stat_idx, skill_idx) = {
        let mut rng = rand::thread_rng();
        (
            rng.gen_range(1..=100),
            rng.gen_range(0..stats.len().max(1)),
            rng.gen_range(0..skills.len().max(1)),
        )
    };

    let result = hospital::resurrect(
        &mut stats,
        &mut skills,
        &race,
        &class,
        player_row.max_hp,
        player_row.credits,
        roll,
        stat_idx,
        skill_idx,
    );

    let res = match result {
        Ok(r) => r,
        Err(hospital::ResurrectionError::NotEnoughGold { needed }) => {
            return error_page(
                app,
                ctx,
                &format!(
                    "Nie możesz zostać wskrzeszony. Potrzebujesz <b>{needed}</b> sztuk złota."
                ),
            );
        }
    };

    // Persist: update hp, max_hp, gold.
    if let Err(e) = vallheru_data::queries::locations::resurrect_player(
        &app.pool,
        player_id,
        res.new_hp,
        res.new_max_hp,
        res.gold_cost,
    )
    .await
    {
        tracing::error!(error = %e, "hospital resurrect: player update failed");
        return server_error();
    }

    // Persist updated stats.
    if let Err(e) = vallheru_data::queries::player::save_stats(&app.pool, player_id, &stats).await {
        tracing::error!(error = %e, "hospital resurrect: save_stats failed");
        return server_error();
    }

    // Persist updated skills.
    if let Err(e) = vallheru_data::queries::player::save_skills(&app.pool, player_id, &skills).await
    {
        tracing::error!(error = %e, "hospital resurrect: save_skills failed");
        return server_error();
    }

    // Build result message.
    let penalty_msg = hospital::penalty_message(&res.penalty);
    let msg = format!("Zostałeś wskrzeszony, ale {penalty_msg}.");

    let meta = PageMeta::titled("Szpital").with_flash(Flash::success(msg));
    let base = app.templates.build_context(ctx, &meta);
    let view = HospitalView {
        base,
        state: "",
        cost: 0,
        tribe_discount: false,
    };
    app.templates.render_value("hospital.html", &view)
}

// ---------------------------------------------------------------------------
// Helpers
// ---------------------------------------------------------------------------

async fn load_player(
    state: &AppState,
    player_id: i32,
) -> Result<vallheru_data::queries::player::PlayerRow, Response> {
    match vallheru_data::queries::player::find_player_by_id(&state.pool, player_id).await {
        Ok(Some(row)) => Ok(row),
        Ok(None) => Err(crate::page::redirect("/login")),
        Err(e) => {
            tracing::error!(error = %e, "hospital load_player failed");
            Err(server_error())
        }
    }
}

async fn load_condition_trained(app: &AppState, player_id: i32) -> i32 {
    let stats = vallheru_data::queries::player::load_stats(&app.pool, player_id)
        .await
        .unwrap_or_default();
    stats
        .iter()
        .find(|s| s.stat_key == "condition")
        .map_or(1, |s| s.trained.max(1))
}

fn error_page(state: &AppState, ctx: &RequestContext, message: &str) -> Response {
    let meta = PageMeta::titled("Szpital").with_flash(Flash {
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
