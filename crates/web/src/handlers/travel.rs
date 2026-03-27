//! Travel handler — stables, destinations, and movement.
//!
//! Ported from `travel.php`. Players see travel options based on their
//! current location, pick a method (caravan/walk/magic), and move.
//! Bandit encounters during travel are not yet implemented (out of scope).

use axum::{
    Extension,
    extract::{Query, State},
    response::{IntoResponse, Response},
};
use serde::Deserialize;

use crate::middleware::context::RequestContext;
use crate::page::{Flash, FlashKind, PageMeta};
use crate::state::AppState;
use vallheru_domain::location::Location;
use vallheru_domain::travel::{
    Destination, TravelAttempt, TravelError, TravelMethod, available_destinations, travel_cost,
    validate_travel,
};

/// Query params common across travel views.
#[derive(Debug, Deserialize)]
pub struct TravelParams {
    /// Destination id (`gory`, `las`, `city2`, `powrot`).
    pub action: Option<String>,
    /// Travel method (`caravan`, `walk`, `magic`).
    pub step: Option<String>,
}

// ---------------------------------------------------------------------------
// View models
// ---------------------------------------------------------------------------

#[derive(serde::Serialize)]
pub struct TravelHubView {
    #[serde(flatten)]
    pub base: crate::render::RenderContext,
    pub location_name: &'static str,
    pub info_text: &'static str,
    pub destinations: Vec<DestinationLink>,
    /// True if player has maps >= 20 and is not immune (can enter portal).
    pub can_enter_portal: bool,
}

#[derive(serde::Serialize)]
pub struct DestinationLink {
    pub name: &'static str,
    pub param: &'static str,
}

#[derive(serde::Serialize)]
pub struct MethodSelectView {
    #[serde(flatten)]
    pub base: crate::render::RenderContext,
    pub destination_name: &'static str,
    pub dest_param: &'static str,
    pub caravan_cost: i32,
    pub walk_cost: i32,
    pub magic_cost: i32,
}

#[derive(serde::Serialize)]
pub struct TravelResultView {
    #[serde(flatten)]
    pub base: crate::render::RenderContext,
    pub message: String,
    pub next_url: String,
}

// ---------------------------------------------------------------------------
// Handler
// ---------------------------------------------------------------------------

/// GET /travel — stables hub, destination selection, or travel execution.
pub async fn show(
    State(state): State<AppState>,
    Extension(ctx): Extension<RequestContext>,
    Query(params): Query<TravelParams>,
) -> Response {
    let Some(ref user) = ctx.session_user else {
        return crate::page::redirect("/login");
    };

    #[allow(clippy::cast_possible_truncation)]
    let player_id = user.id as i32;

    let player_row =
        match vallheru_data::queries::player::find_player_by_id(&state.pool, player_id).await {
            Ok(Some(row)) => row,
            Ok(None) => return crate::page::redirect("/login"),
            Err(e) => {
                tracing::error!(error = %e, "travel: failed to load player");
                return server_error();
            }
        };

    let Some(location) = Location::from_db(&player_row.location) else {
        return crate::page::redirect("/login");
    };

    // Jail/dungeon check
    if location == Location::Dungeon {
        return error_page(&state, &ctx, "Nie możesz podróżować z tego miejsca.");
    }

    // Step 3: execute travel (destination + method given)
    if let (Some(action), Some(step)) = (&params.action, &params.step) {
        return execute_travel(&state, &ctx, &player_row, location, action, step).await;
    }

    // Step 2: show method selection (destination given, no method)
    if let Some(ref action) = params.action {
        return show_method_select(&state, &ctx, location, action);
    }

    // Step 1: show travel hub with available destinations
    show_hub(&state, &ctx, location, &player_row)
}

// ---------------------------------------------------------------------------
// Sub-handlers
// ---------------------------------------------------------------------------

fn show_hub(
    state: &AppState,
    ctx: &RequestContext,
    location: Location,
    player_row: &vallheru_data::queries::player::PlayerRow,
) -> Response {
    let dests = available_destinations(location);
    let destinations: Vec<DestinationLink> = dests
        .iter()
        .map(|d| DestinationLink {
            name: d.display_name(),
            param: d.param(),
        })
        .collect();

    let (location_name, info_text) = hub_text(location);

    let can_enter_portal =
        location == Location::Altara && player_row.maps >= 20 && !player_row.immune;

    let meta = PageMeta::titled("Stajnie");
    let base = state.templates.build_context(ctx, &meta);

    let view = TravelHubView {
        base,
        location_name,
        info_text,
        destinations,
        can_enter_portal,
    };

    state.templates.render_value("travel.html", &view)
}

fn show_method_select(
    state: &AppState,
    ctx: &RequestContext,
    location: Location,
    action: &str,
) -> Response {
    let Some(dest) = Destination::from_param(action) else {
        return error_page(state, ctx, "Nieprawidłowy cel podróży.");
    };

    let cc = travel_cost(TravelMethod::Caravan, location, dest);
    let wc = travel_cost(TravelMethod::Walk, location, dest);
    let mc = travel_cost(TravelMethod::MagicPortal, location, dest);

    let meta = PageMeta::titled("Podróż");
    let base = state.templates.build_context(ctx, &meta);

    let view = MethodSelectView {
        base,
        destination_name: dest.display_name(),
        dest_param: dest.param(),
        caravan_cost: cc,
        walk_cost: wc,
        magic_cost: mc,
    };

    state.templates.render_value("travel_method.html", &view)
}

async fn execute_travel(
    state: &AppState,
    ctx: &RequestContext,
    player_row: &vallheru_data::queries::player::PlayerRow,
    location: Location,
    action: &str,
    step: &str,
) -> Response {
    let Some(dest) = Destination::from_param(action) else {
        return error_page(state, ctx, "Nieprawidłowy cel podróży.");
    };
    let Some(method) = TravelMethod::from_param(step) else {
        return error_page(state, ctx, "Nieprawidłowy sposób podróży.");
    };

    let result = validate_travel(&TravelAttempt {
        from: location,
        dest,
        method,
        hp: player_row.hp,
        fight_id: player_row.fight,
        is_immune: player_row.immune,
        credits: player_row.credits,
        energy: player_row.energy,
    });

    let cost = match result {
        Ok(cost) => cost,
        Err(e) => {
            let msg = travel_error_message(&e);
            return error_page(state, ctx, msg);
        }
    };

    // Execute the move
    let new_location = dest.target_location().to_db();
    let player_id = player_row.id;

    let db_result = match method {
        TravelMethod::Caravan | TravelMethod::MagicPortal => {
            vallheru_data::queries::travel::move_player_deduct_gold(
                &state.pool,
                player_id,
                new_location,
                cost,
            )
            .await
        }
        TravelMethod::Walk => {
            vallheru_data::queries::travel::move_player_deduct_energy(
                &state.pool,
                player_id,
                new_location,
                cost,
            )
            .await
        }
    };

    if let Err(e) = db_result {
        tracing::error!(error = %e, "travel: failed to update player");
        return server_error();
    }

    let next_url = match dest {
        Destination::Mountains => "/mountains".to_owned(),
        Destination::Forest => "/forest".to_owned(),
        Destination::Ardulith | Destination::Altara => "/city".to_owned(),
    };

    let message = format!(
        "Podróż przebiegała spokojnie, po pewnym czasie widzisz przed sobą cel \
         swej podróży. Dotarłeś do {}.",
        dest.arrival_label()
    );

    let meta = PageMeta::titled("Podróż");
    let base = state.templates.build_context(ctx, &meta);

    let view = TravelResultView {
        base,
        message,
        next_url,
    };

    state.templates.render_value("travel_result.html", &view)
}

// ---------------------------------------------------------------------------
// Helpers
// ---------------------------------------------------------------------------

fn hub_text(location: Location) -> (&'static str, &'static str) {
    match location {
        Location::Altara => (
            "Altara",
            "Witaj w Stajniach. Stąd możesz wyruszyć do innych miejsc świata Vallheru.",
        ),
        Location::Ardulith => (
            "Ardulith",
            "Witaj w Stajniach. Stąd możesz wyruszyć do innych miejsc świata Vallheru.",
        ),
        Location::Forest => (
            "Las Avantiel",
            "Witaj w Stajniach. Tędy możesz wrócić do stolicy Vallheru, Altary.",
        ),
        Location::Mountains => (
            "Góry Kazad-nar",
            "Witaj w Stajniach. Tędy możesz wrócić do stolicy Vallheru, Altary.",
        ),
        _ => ("Stajnie", "Nie możesz stąd podróżować."),
    }
}

fn travel_error_message(err: &TravelError) -> &'static str {
    match err {
        TravelError::MovementDenied(d) => match d {
            vallheru_domain::location::MovementDenied::Dead => {
                "Nie możesz podróżować, ponieważ jesteś martwy!"
            }
            vallheru_domain::location::MovementDenied::InCombat => {
                "Nie możesz podróżować podczas walki!"
            }
            vallheru_domain::location::MovementDenied::Immune => {
                "Nie możesz opuścić miasta podczas immunitetu."
            }
            vallheru_domain::location::MovementDenied::InDungeon => {
                "Nie możesz podróżować z lochów."
            }
            vallheru_domain::location::MovementDenied::OnAdventure => {
                "Musisz najpierw zakończyć przygodę."
            }
            vallheru_domain::location::MovementDenied::AlreadyThere => "Jesteś już w tym miejscu.",
            vallheru_domain::location::MovementDenied::InvalidRoute => {
                "Nie możesz dotrzeć do tego miejsca stąd."
            }
        },
        TravelError::InsufficientGold { .. } => "Nie masz tyle pieniędzy!",
        TravelError::InsufficientEnergy { .. } => "Nie masz energii aby podróżować!",
    }
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
    (
        axum::http::StatusCode::INTERNAL_SERVER_ERROR,
        "Internal error",
    )
        .into_response()
}
