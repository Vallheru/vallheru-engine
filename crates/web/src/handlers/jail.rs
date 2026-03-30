//! Jail handlers — public view of prisoners, bail payment, and escape.
//!
//! Ported from `jail.php`. Players can view prisoners from Altara/Ardulith,
//! pay bail for others, and thieves can attempt to escape.

use axum::{
    Extension,
    extract::{Path, State},
    response::{IntoResponse, Redirect, Response},
};

use crate::middleware::context::RequestContext;
use crate::page::{Flash, FlashKind, PageMeta};
use crate::state::AppState;

// ---------------------------------------------------------------------------
// View models
// ---------------------------------------------------------------------------

/// A prisoner entry for the list view.
#[derive(serde::Serialize)]
pub struct PrisonerEntry {
    pub jail_id: i32,
    pub player_id: i32,
    pub name: String,
    pub sentenced: String,
    pub verdict: String,
    pub duration_weeks: i32,
    pub duration_raw: i32,
    pub cost: i32,
    pub can_bail: bool,
}

/// View model for jail page — city visitor view.
#[derive(serde::Serialize)]
pub struct JailListView {
    #[serde(flatten)]
    pub base: crate::render::RenderContext,
    pub prisoners: Vec<PrisonerEntry>,
    pub description: String,
}

/// View model for jail page — prisoner's own view.
#[derive(serde::Serialize)]
pub struct JailPrisonerView {
    #[serde(flatten)]
    pub base: crate::render::RenderContext,
    pub sentenced: String,
    pub verdict: String,
    pub duration_weeks: i32,
    pub duration_raw: i32,
    pub cost: i32,
    pub can_escape: bool,
}

/// View model for bail confirmation.
#[derive(serde::Serialize)]
pub struct JailBailView {
    #[serde(flatten)]
    pub base: crate::render::RenderContext,
    pub jail_id: i32,
    pub prisoner_name: String,
    pub cost: i32,
}

// ---------------------------------------------------------------------------
// Handlers
// ---------------------------------------------------------------------------

/// GET /jail — View jail. Shows different content based on location:
/// - Altara/Ardulith: list of all prisoners + bail options
/// - Lochy: the player's own sentence info
pub async fn jail_view(
    State(state): State<AppState>,
    Extension(ctx): Extension<RequestContext>,
) -> Response {
    let Some(user) = ctx.session_user.as_ref() else {
        return Redirect::to("/login").into_response();
    };

    #[allow(clippy::cast_possible_truncation)]
    let player_id = user.id as i32;

    // Get player location.
    let location: Option<String> = sqlx::query_scalar("SELECT location FROM players WHERE id = $1")
        .bind(player_id)
        .fetch_optional(&state.pool)
        .await
        .unwrap_or(None);

    let location = location.unwrap_or_default();

    if location == "Lochy" {
        return jail_prisoner_view(&state, &ctx, player_id).await;
    }

    if location != "Altara" && location != "Ardulith" {
        let meta = PageMeta::titled("Błąd").with_flash(Flash {
            kind: FlashKind::Error,
            message: "Musisz znajdować się w mieście, aby odwiedzić lochy.".to_owned(),
        });
        let base = state.templates.build_context(&ctx, &meta);
        return state.templates.render("error.html", &base);
    }

    // City view — list prisoners.
    let rows = vallheru_data::queries::moderation::list_prisoners(&state.pool)
        .await
        .unwrap_or_default();

    let prisoners: Vec<PrisonerEntry> = rows
        .into_iter()
        .map(|r| PrisonerEntry {
            jail_id: r.id,
            player_id: r.prisoner,
            name: r.prisoner_name,
            sentenced: r.sentenced,
            verdict: r.verdict,
            duration_weeks: (r.duration + 6) / 7,
            duration_raw: r.duration,
            cost: r.cost,
            can_bail: r.cost > 0 && r.prisoner != player_id,
        })
        .collect();

    let meta = PageMeta::titled("Lochy").with_back_link("/city", "Miasto");
    let view = JailListView {
        base: state.templates.build_context(&ctx, &meta),
        prisoners,
        description: String::from(
            "Tutaj znajdują się lochy, do których wtrącani są \
             wszyscy obywatele łamiący miejscowe prawo.",
        ),
    };

    state.templates.render_value("jail.html", &view)
}

/// Render the prisoner's own jail view.
async fn jail_prisoner_view(state: &AppState, ctx: &RequestContext, player_id: i32) -> Response {
    let record = vallheru_data::queries::moderation::find_jail_by_prisoner(&state.pool, player_id)
        .await
        .ok()
        .flatten();

    let (sentenced, verdict, duration, cost) = match record {
        Some(r) => (r.sentenced, r.verdict, r.duration, r.cost),
        None => {
            return Redirect::to("/city").into_response();
        }
    };

    // Only thieves can attempt escape, and only if not admin-sentenced (cost > 0).
    let player_class: Option<String> =
        sqlx::query_scalar("SELECT class FROM players WHERE id = $1")
            .bind(player_id)
            .fetch_optional(&state.pool)
            .await
            .unwrap_or(None);

    let can_escape = cost > 0 && player_class.as_deref() == Some("Złodziej");

    let meta = PageMeta::titled("Lochy");
    let view = JailPrisonerView {
        base: state.templates.build_context(ctx, &meta),
        sentenced,
        verdict,
        duration_weeks: (duration + 6) / 7,
        duration_raw: duration,
        cost,
        can_escape,
    };

    state.templates.render_value("jail_prisoner.html", &view)
}

/// GET /jail/bail/:id — Bail confirmation page.
pub async fn jail_bail_confirm(
    State(state): State<AppState>,
    Extension(ctx): Extension<RequestContext>,
    Path(jail_id): Path<i32>,
) -> Response {
    let Some(user) = ctx.session_user.as_ref() else {
        return Redirect::to("/login").into_response();
    };

    let record = vallheru_data::queries::moderation::find_jail_record(&state.pool, jail_id)
        .await
        .ok()
        .flatten();

    let Some(record) = record else {
        return Redirect::to("/jail").into_response();
    };

    if record.cost == 0 {
        return crate::page::redirect("/jail");
    }

    #[allow(clippy::cast_possible_truncation)]
    let player_id = user.id as i32;
    if record.prisoner == player_id {
        return crate::page::redirect("/jail");
    }

    let meta = PageMeta::titled("Kaucja").with_back_link("/jail", "Lochy");
    let view = JailBailView {
        base: state.templates.build_context(&ctx, &meta),
        jail_id,
        prisoner_name: record.prisoner_name,
        cost: record.cost,
    };

    state.templates.render_value("jail_bail.html", &view)
}

/// POST /jail/bail/:id — Process bail payment.
pub async fn jail_bail_pay(
    State(state): State<AppState>,
    Extension(ctx): Extension<RequestContext>,
    Path(jail_id): Path<i32>,
) -> Response {
    let Some(user) = ctx.session_user.as_ref() else {
        return Redirect::to("/login").into_response();
    };

    #[allow(clippy::cast_possible_truncation)]
    let payer_id = user.id as i32;

    let record = vallheru_data::queries::moderation::find_jail_record(&state.pool, jail_id)
        .await
        .ok()
        .flatten();

    let record = match record {
        Some(r) if r.cost > 0 && r.prisoner != payer_id => r,
        _ => return crate::page::redirect("/jail"),
    };

    // Check payer has enough gold.
    let payer_gold: Option<i32> = sqlx::query_scalar("SELECT credits FROM players WHERE id = $1")
        .bind(payer_id)
        .fetch_optional(&state.pool)
        .await
        .unwrap_or(None);

    if payer_gold.unwrap_or(0) < record.cost {
        return crate::page::redirect("/jail");
    }

    let prisoner_id =
        vallheru_data::queries::moderation::pay_bail(&state.pool, jail_id, payer_id, record.cost)
            .await;

    match prisoner_id {
        Ok(pid) => {
            let msg = format!(
                "Kaucję za ciebie wpłacił gracz ID {payer_id}. \
                 Zostałeś zwolniony z lochów."
            );
            let _ =
                vallheru_data::queries::moderation::insert_game_log(&state.pool, pid, &msg, 'J')
                    .await;

            crate::page::redirect("/jail")
        }
        Err(_) => crate::page::redirect("/jail"),
    }
}
