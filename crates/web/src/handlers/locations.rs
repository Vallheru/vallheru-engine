//! Secondary location page handlers.
//!
//! Ported from `gory.php`, `las.php`, `alley.php`, `landfill.php`, and
//! `rest.php`. These are navigation hubs for exploration areas and simple
//! city services (energy→gold work, energy→mana rest).

use axum::{
    Extension, Form,
    extract::{Query, State},
    response::Response,
};

use crate::middleware::context::RequestContext;
use crate::page::{Flash, FlashKind, PageMeta};
use crate::state::AppState;
use vallheru_domain::location::Location;

// ---------------------------------------------------------------------------
// View models
// ---------------------------------------------------------------------------

/// Mountains / forest hub — shows available actions for the location.
#[derive(serde::Serialize)]
pub struct LocationHubView {
    #[serde(flatten)]
    pub base: crate::render::RenderContext,
    pub location_name: &'static str,
    pub info_text: &'static str,
    pub links: Vec<LocationNavLink>,
    pub is_dead: bool,
    pub return_city: &'static str,
    /// Hermit section: "none", "offer", or "wait".
    pub hermit: &'static str,
    /// Gold cost for hermit resurrection (only when hermit == "offer").
    pub hermit_cost: i32,
}

/// Query parameters for location hub pages.
#[derive(serde::Deserialize)]
pub struct HubQuery {
    #[serde(default)]
    pub action: Option<String>,
}

#[derive(serde::Serialize)]
pub struct LocationNavLink {
    pub href: &'static str,
    pub label: &'static str,
}

/// Alley of the Deserving — vallars leaderboard.
#[derive(serde::Serialize)]
pub struct AlleyView {
    #[serde(flatten)]
    pub base: crate::render::RenderContext,
    pub leaderboard: Vec<VallarsEntry>,
    pub donators: Vec<String>,
}

#[derive(serde::Serialize)]
pub struct VallarsEntry {
    pub player_id: i32,
    pub username: String,
    pub vallars: i32,
}

/// Landfill / city cleanup — earn gold by spending energy.
#[derive(serde::Serialize)]
pub struct LandfillView {
    #[serde(flatten)]
    pub base: crate::render::RenderContext,
    pub gold_per_energy: i32,
    pub energy: i32,
    pub description: &'static str,
}

/// Rest — recover mana by spending energy.
#[derive(serde::Serialize)]
pub struct RestView {
    #[serde(flatten)]
    pub base: crate::render::RenderContext,
    pub energy_to_full: i32,
    pub current_mana: i32,
    pub max_mana: i32,
}

// ---------------------------------------------------------------------------
// Form inputs
// ---------------------------------------------------------------------------

#[derive(serde::Deserialize)]
pub struct LandfillForm {
    pub amount: Option<i32>,
}

#[derive(serde::Deserialize)]
pub struct RestForm {
    pub pm: Option<i32>,
}

// ---------------------------------------------------------------------------
// Mountains hub
// ---------------------------------------------------------------------------

/// GET /mountains — mountains area hub.
pub async fn mountains(
    State(state): State<AppState>,
    Extension(ctx): Extension<RequestContext>,
    Query(query): Query<HubQuery>,
) -> Response {
    let Some(ref user) = ctx.session_user else {
        return crate::page::redirect("/login");
    };

    #[allow(clippy::cast_possible_truncation)]
    let player_id = user.id as i32;

    let player_row = match load_player(&state, player_id).await {
        Ok(row) => row,
        Err(resp) => return resp,
    };

    let Some(location) = Location::from_db(&player_row.location) else {
        return crate::page::redirect("/login");
    };

    if location != Location::Mountains {
        return error_page(&state, &ctx, "Nie znajdujesz się w górach.");
    }

    let is_dead = player_row.hp <= 0;

    // Handle dead-player actions: return to city, hermit encounter, resurrection.
    if is_dead {
        return handle_dead_hub(
            &state,
            &ctx,
            query.action.as_deref(),
            player_id,
            &player_row,
            "Góry Kazad-nar",
            "Altara",
        )
        .await;
    }

    let links = vec![
        LocationNavLink {
            href: "/mines",
            label: "Idź do kopalni",
        },
        LocationNavLink {
            href: "/explore",
            label: "Zwiedzaj góry",
        },
        LocationNavLink {
            href: "/travel",
            label: "Stajnia",
        },
    ];

    let meta = PageMeta::titled("Góry Kazad-nar");
    let base = state.templates.build_context(&ctx, &meta);

    let view = LocationHubView {
        base,
        location_name: "Góry Kazad-nar",
        info_text: "Witaj w Górach Kazad-nar, co chcesz robić?",
        links,
        is_dead: false,
        return_city: "Altara",
        hermit: "none",
        hermit_cost: 0,
    };

    state.templates.render_value("location_hub.html", &view)
}

// ---------------------------------------------------------------------------
// Forest hub
// ---------------------------------------------------------------------------

/// GET /forest — forest area hub.
pub async fn forest(
    State(state): State<AppState>,
    Extension(ctx): Extension<RequestContext>,
    Query(query): Query<HubQuery>,
) -> Response {
    let Some(ref user) = ctx.session_user else {
        return crate::page::redirect("/login");
    };

    #[allow(clippy::cast_possible_truncation)]
    let player_id = user.id as i32;

    let player_row = match load_player(&state, player_id).await {
        Ok(row) => row,
        Err(resp) => return resp,
    };

    let Some(location) = Location::from_db(&player_row.location) else {
        return crate::page::redirect("/login");
    };

    if location != Location::Forest {
        return error_page(&state, &ctx, "Nie znajdujesz się w lesie.");
    }

    let is_dead = player_row.hp <= 0;

    if is_dead {
        return handle_dead_hub(
            &state,
            &ctx,
            query.action.as_deref(),
            player_id,
            &player_row,
            "Las Avantiel",
            "Ardulith",
        )
        .await;
    }

    let links = vec![
        LocationNavLink {
            href: "/lumberjack",
            label: "Idź rąbać drewno",
        },
        LocationNavLink {
            href: "/explore",
            label: "Zwiedzaj las",
        },
        LocationNavLink {
            href: "/travel",
            label: "Stajnia",
        },
    ];

    let meta = PageMeta::titled("Las Avantiel");
    let base = state.templates.build_context(&ctx, &meta);

    let view = LocationHubView {
        base,
        location_name: "Las Avantiel",
        info_text: "Witaj w Lesie Avantiel, co chcesz robić?",
        links,
        is_dead: false,
        return_city: "Ardulith",
        hermit: "none",
        hermit_cost: 0,
    };

    state.templates.render_value("location_hub.html", &view)
}

// ---------------------------------------------------------------------------
// Alley of the Deserving
// ---------------------------------------------------------------------------

/// GET /alley — vallars leaderboard page.
pub async fn alley(
    State(state): State<AppState>,
    Extension(ctx): Extension<RequestContext>,
) -> Response {
    let leaderboard = vallheru_data::queries::locations::vallars_leaderboard(&state.pool, 10)
        .await
        .unwrap_or_default();

    let donators = vallheru_data::queries::locations::list_donators(&state.pool)
        .await
        .unwrap_or_default();

    let entries: Vec<VallarsEntry> = leaderboard
        .into_iter()
        .map(|row| VallarsEntry {
            player_id: row.id,
            username: row.username,
            vallars: row.vallars,
        })
        .collect();

    let meta = PageMeta::titled("Aleja Zasłużonych");
    let base = state.templates.build_context(&ctx, &meta);
    let view = AlleyView {
        base,
        leaderboard: entries,
        donators,
    };
    state.templates.render_value("alley.html", &view)
}

// ---------------------------------------------------------------------------
// Landfill (city cleanup work)
// ---------------------------------------------------------------------------

/// GET /landfill — show work-for-gold form.
pub async fn landfill_show(
    State(state): State<AppState>,
    Extension(ctx): Extension<RequestContext>,
) -> Response {
    let Some(ref user) = ctx.session_user else {
        return crate::page::redirect("/login");
    };

    #[allow(clippy::cast_possible_truncation)]
    let player_id = user.id as i32;

    let player_row = match load_player(&state, player_id).await {
        Ok(row) => row,
        Err(resp) => return resp,
    };

    let Some(location) = Location::from_db(&player_row.location) else {
        return crate::page::redirect("/login");
    };

    if !location.is_city() {
        return error_page(&state, &ctx, "Nie znajdujesz się w mieście.");
    }

    if player_row.hp <= 0 {
        return error_page(&state, &ctx, "Nie możesz pracować, ponieważ jesteś martwy!");
    }

    let condition = load_condition_stat(&state, player_id).await;
    let gold_per_energy = condition * 25;

    let description = if location == Location::Altara {
        "Pragniesz zarobić nieco sztuk złota? W porządku. Za każdy worek śmieci jakie uprzątniesz, dam ci"
    } else {
        "Witaj na Polanie drwali. Możesz tutaj poświęcić nieco swojego czasu, aby zarobić złoto. Za każdy punkt energii jaki zużyjesz, dostaniesz"
    };

    #[allow(clippy::cast_possible_truncation)]
    let energy = player_row.energy as i32;

    let meta = PageMeta::titled("Oczyszczanie miasta");
    let base = state.templates.build_context(&ctx, &meta);

    let view = LandfillView {
        base,
        gold_per_energy,
        energy,
        description,
    };

    state.templates.render_value("landfill.html", &view)
}

/// POST /landfill — execute city cleanup work.
pub async fn landfill_work(
    State(state): State<AppState>,
    Extension(ctx): Extension<RequestContext>,
    Form(form): Form<LandfillForm>,
) -> Response {
    let Some(ref user) = ctx.session_user else {
        return crate::page::redirect("/login");
    };

    #[allow(clippy::cast_possible_truncation)]
    let player_id = user.id as i32;

    let player_row = match load_player(&state, player_id).await {
        Ok(row) => row,
        Err(resp) => return resp,
    };

    let Some(location) = Location::from_db(&player_row.location) else {
        return crate::page::redirect("/login");
    };

    if !location.is_city() {
        return error_page(&state, &ctx, "Nie znajdujesz się w mieście.");
    }

    if player_row.hp <= 0 {
        return error_page(&state, &ctx, "Nie możesz pracować, ponieważ jesteś martwy!");
    }

    let amount = match form.amount {
        Some(a) if a > 0 => a,
        _ => return error_page(&state, &ctx, "Podaj ile czasu chcesz spędzić pracując!"),
    };

    #[allow(clippy::cast_possible_truncation)]
    let available_energy = player_row.energy as i32;
    if amount > available_energy {
        return error_page(&state, &ctx, "Nie masz tyle energii aby pracować.");
    }

    let condition = load_condition_stat(&state, player_id).await;
    let gold_gained = condition * 25 * amount;

    if let Err(e) = vallheru_data::queries::locations::landfill_work(
        &state.pool,
        player_id,
        amount,
        gold_gained,
    )
    .await
    {
        tracing::error!(error = %e, "landfill_work: DB update failed");
        return server_error();
    }

    // Award condition XP equal to energy spent.
    let xp_msg = award_condition_xp(&state, player_id, &player_row, amount).await;

    let msg = format!(
        "Podczas pracy zużyłeś {amount} punkt(ów) energii i zarobiłeś {gold_gained} sztuk złota \
         oraz {amount} punktów doświadczenia.{xp_msg}"
    );

    let meta = PageMeta::titled("Oczyszczanie miasta").with_flash(Flash::success(msg));
    let base = state.templates.build_context(&ctx, &meta);

    let new_energy = available_energy - amount;
    let view = LandfillView {
        base,
        gold_per_energy: condition * 25,
        energy: new_energy,
        description: if location == Location::Altara {
            "Pragniesz zarobić nieco sztuk złota? W porządku. Za każdy worek śmieci jakie uprzątniesz, dam ci"
        } else {
            "Witaj na Polanie drwali. Możesz tutaj poświęcić nieco swojego czasu, aby zarobić złoto. Za każdy punkt energii jaki zużyjesz, dostaniesz"
        },
    };

    state.templates.render_value("landfill.html", &view)
}

// ---------------------------------------------------------------------------
// Rest (mana recovery)
// ---------------------------------------------------------------------------

/// GET /rest — show mana recovery form.
pub async fn rest_show(
    State(state): State<AppState>,
    Extension(ctx): Extension<RequestContext>,
) -> Response {
    let Some(ref user) = ctx.session_user else {
        return crate::page::redirect("/login");
    };

    #[allow(clippy::cast_possible_truncation)]
    let player_id = user.id as i32;

    let player_row = match load_player(&state, player_id).await {
        Ok(row) => row,
        Err(resp) => return resp,
    };

    let max_mana = compute_max_mana(&state, player_id, &player_row).await;
    #[allow(clippy::cast_possible_truncation)]
    let energy_to_full = (f64::from(max_mana - player_row.pm) / 10.0).ceil() as i32;

    let meta = PageMeta::titled("Odpoczynek");
    let base = state.templates.build_context(&ctx, &meta);

    let view = RestView {
        base,
        energy_to_full,
        current_mana: player_row.pm,
        max_mana,
    };

    state.templates.render_value("rest.html", &view)
}

/// POST /rest — execute mana recovery.
pub async fn rest_recover(
    State(state): State<AppState>,
    Extension(ctx): Extension<RequestContext>,
    Form(form): Form<RestForm>,
) -> Response {
    let Some(ref user) = ctx.session_user else {
        return crate::page::redirect("/login");
    };

    #[allow(clippy::cast_possible_truncation)]
    let player_id = user.id as i32;

    let player_row = match load_player(&state, player_id).await {
        Ok(row) => row,
        Err(resp) => return resp,
    };

    let requested_mana = match form.pm {
        Some(m) if m > 0 => m,
        _ => return error_page(&state, &ctx, "Podaj ile punktów magii chcesz odzyskać."),
    };

    let max_mana = compute_max_mana(&state, player_id, &player_row).await;

    if player_row.pm >= max_mana {
        return error_page(&state, &ctx, "Nie musisz odpoczywać.");
    }

    let new_mana = player_row.pm + requested_mana;
    if new_mana > max_mana {
        return error_page(
            &state,
            &ctx,
            "Nie możesz odzyskać więcej Punktów Magii niż masz maksymalnie!",
        );
    }

    // 10 mana per 1 energy, rounded to 2 decimal places (as in PHP).
    let energy_cost = (f64::from(requested_mana) / 10.0 * 100.0).round() / 100.0;

    if player_row.energy < energy_cost {
        return error_page(&state, &ctx, "Nie masz tyle energii!");
    }

    if let Err(e) = vallheru_data::queries::locations::rest_recover(
        &state.pool,
        player_id,
        new_mana,
        energy_cost,
    )
    .await
    {
        tracing::error!(error = %e, "rest_recover: DB update failed");
        return server_error();
    }

    #[allow(clippy::cast_possible_truncation)]
    let energy_display = energy_cost as i32;
    let msg = format!(
        "Odpocząłeś sobie przez jakiś czas i odzyskałeś {requested_mana} punkty magii w zamian za {energy_display} energii."
    );

    let meta = PageMeta::titled("Odpoczynek").with_flash(Flash::success(msg));
    let base = state.templates.build_context(&ctx, &meta);

    let view = RestView {
        base,
        #[allow(clippy::cast_possible_truncation)]
        energy_to_full: (f64::from(max_mana - new_mana) / 10.0).ceil() as i32,
        current_mana: new_mana,
        max_mana,
    };

    state.templates.render_value("rest.html", &view)
}

// ---------------------------------------------------------------------------
// Dead player hub — hermit resurrection (mountains / forest)
// ---------------------------------------------------------------------------

/// Handle a dead player at a location hub with hermit encounter.
///
/// Actions:
/// - `None` → show dead state with hermit link
/// - `"back"` → return player to city, redirect to hospital
/// - `"hermit"` → show hermit dialog with gold cost and wait option
/// - `"resurrect"` → perform resurrection via `do_resurrect`
/// - `"wait"` → show flavor text (no real timer, like PHP)
#[allow(clippy::too_many_arguments)]
async fn handle_dead_hub(
    app: &AppState,
    ctx: &RequestContext,
    action: Option<&str>,
    player_id: i32,
    player_row: &vallheru_data::queries::player::PlayerRow,
    location_name: &'static str,
    return_city: &'static str,
) -> Response {
    match action {
        Some("back") => {
            // Move player back to city so they can use the hospital.
            let city_location = if return_city == "Altara" {
                "Altara"
            } else {
                "Ardulith"
            };
            if let Err(e) = vallheru_data::queries::locations::move_player_to(
                &app.pool,
                player_id,
                city_location,
            )
            .await
            {
                tracing::error!(error = %e, "handle_dead_hub: move to city failed");
                return server_error();
            }
            crate::page::redirect("/hospital")
        }

        Some("hermit") => {
            let condition = load_condition_stat(app, player_id).await;
            let cost = vallheru_domain::hospital::resurrection_cost(condition);

            let meta = PageMeta::titled(location_name);
            let base = app.templates.build_context(ctx, &meta);
            let view = LocationHubView {
                base,
                location_name,
                info_text: "",
                links: vec![],
                is_dead: true,
                return_city,
                hermit: "offer",
                hermit_cost: cost,
            };
            app.templates.render_value("location_hub.html", &view)
        }

        Some("resurrect") => {
            crate::handlers::hospital::do_resurrect(app, ctx, player_id, player_row).await
        }

        Some("wait") => {
            let meta = PageMeta::titled(location_name).with_flash(Flash {
                kind: FlashKind::Info,
                message: "Przed Twoimi oczami przebiegają wydarzenia z przeszłości... \
                          To wspomnienia. Czas dłuży się niesamowicie... Nagle słyszysz słowa:\n\n\
                          Cierpliwości. Właśnie przygotowuję czar dla Ciebie. Na szczęście mam już \
                          potrzebne składniki, ale rzucenie wskrzeszającego czaru to nie taka prosta \
                          sprawa. Trzeba być ostrożnym."
                    .to_owned(),
            });
            let base = app.templates.build_context(ctx, &meta);
            let view = LocationHubView {
                base,
                location_name,
                info_text: "",
                links: vec![],
                is_dead: true,
                return_city,
                hermit: "none",
                hermit_cost: 0,
            };
            app.templates.render_value("location_hub.html", &view)
        }

        _ => {
            // Default dead state: show return + stay + hermit links.
            let meta = PageMeta::titled(location_name);
            let base = app.templates.build_context(ctx, &meta);
            let view = LocationHubView {
                base,
                location_name,
                info_text: "",
                links: vec![],
                is_dead: true,
                return_city,
                hermit: "none",
                hermit_cost: 0,
            };
            app.templates.render_value("location_hub.html", &view)
        }
    }
}

// ---------------------------------------------------------------------------
// Helpers
// ---------------------------------------------------------------------------

/// Load a player row or return an error response.
async fn load_player(
    state: &AppState,
    player_id: i32,
) -> Result<vallheru_data::queries::player::PlayerRow, Response> {
    match vallheru_data::queries::player::find_player_by_id(&state.pool, player_id).await {
        Ok(Some(row)) => Ok(row),
        Ok(None) => Err(crate::page::redirect("/login")),
        Err(e) => {
            tracing::error!(error = %e, "load_player failed");
            Err(server_error())
        }
    }
}

/// Load the condition stat value for a player (defaults to 1 on error).
async fn load_condition_stat(app: &AppState, player_id: i32) -> i32 {
    let player_stats = vallheru_data::queries::player::load_stats(&app.pool, player_id)
        .await
        .unwrap_or_default();
    player_stats
        .iter()
        .find(|s| s.stat_key == "condition")
        .map_or(1, |s| s.trained.max(1))
}

/// Compute maximum mana: `(inteli + wisdom)` times class multiplier.
///
/// Equipment bonus is not yet available (equipment module not migrated).
async fn compute_max_mana(
    app: &AppState,
    player_id: i32,
    player_row: &vallheru_data::queries::player::PlayerRow,
) -> i32 {
    let player_stats = vallheru_data::queries::player::load_stats(&app.pool, player_id)
        .await
        .unwrap_or_default();

    let intelligence = player_stats
        .iter()
        .find(|s| s.stat_key == "inteli")
        .map_or(0, |s| s.trained);
    let wisdom = player_stats
        .iter()
        .find(|s| s.stat_key == "wisdom")
        .map_or(0, |s| s.trained);

    let mut max_mana = intelligence + wisdom;
    if player_row.class == "Mag" {
        max_mana *= 2;
    }
    // Equipment bonus (equip[8] / rod slot) is not yet available.
    max_mana
}

/// Award condition stat XP after work (landfill, etc.).
///
/// Returns a suffix string for the flash message (empty if no level-up,
/// or a description of the level-up if one occurred).
async fn award_condition_xp(
    app: &AppState,
    player_id: i32,
    player_row: &vallheru_data::queries::player::PlayerRow,
    xp_amount: i32,
) -> String {
    let Some(race) = vallheru_domain::player::Race::from_db(&player_row.race) else {
        return String::new();
    };
    let Some(class) = vallheru_domain::player::Class::from_db(&player_row.class) else {
        return String::new();
    };

    let mut stats = vallheru_data::queries::player::load_stats(&app.pool, player_id)
        .await
        .unwrap_or_default();

    let Some(condition) = stats.iter_mut().find(|s| s.stat_key == "condition") else {
        return String::new();
    };

    let result =
        vallheru_domain::player::progression::apply_stat_xp(condition, xp_amount, &race, &class);

    if result.levels_gained > 0 || result.hp_change > 0 {
        if let Err(e) =
            vallheru_data::queries::player::save_stats(&app.pool, player_id, &stats).await
        {
            tracing::error!(error = %e, "award_condition_xp: save_stats failed");
            return String::new();
        }

        if result.hp_change > 0 {
            if let Err(e) = vallheru_data::queries::locations::add_player_hp(
                &app.pool,
                player_id,
                result.hp_change,
            )
            .await
            {
                tracing::error!(error = %e, "award_condition_xp: add_player_hp failed");
            }
        }

        format!(
            " Twoja kondycja wzrosła o {} poziom(ów)!",
            result.levels_gained
        )
    } else {
        // XP gained but no level-up — still save updated XP.
        if let Err(e) =
            vallheru_data::queries::player::save_stats(&app.pool, player_id, &stats).await
        {
            tracing::error!(error = %e, "award_condition_xp: save_stats failed");
        }
        String::new()
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
    use axum::response::IntoResponse;
    (
        axum::http::StatusCode::INTERNAL_SERVER_ERROR,
        "Internal error",
    )
        .into_response()
}
