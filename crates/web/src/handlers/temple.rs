//! Temple handler — work for piety, prayer/blessing, pantheon, temple book.
//!
//! Ported from `temple.php`.

use axum::{Extension, Form, extract::State, response::Response};
use rand::Rng;

use crate::middleware::context::RequestContext;
use crate::page::{Flash, FlashKind, PageMeta};
use crate::state::AppState;
use vallheru_domain::location::Location;
use vallheru_domain::temple;

// ---------------------------------------------------------------------------
// View models
// ---------------------------------------------------------------------------

#[derive(serde::Serialize)]
pub struct TempleView {
    #[serde(flatten)]
    pub base: crate::render::RenderContext,
    pub deity: String,
    pub location: String,
    /// Which sub-page: "", "work", "prayer", "book", "pantheon".
    pub section: String,
    /// Prayer blessings (only when section == "prayer").
    pub blessings: Vec<BlessingRow>,
    /// Pantheon deities (only when section == "pantheon").
    pub pantheon: Vec<PantheonRow>,
    /// Book page number (0 = intro, 1, 2).
    pub book_page: i32,
}

#[derive(serde::Serialize)]
pub struct BlessingRow {
    pub index: usize,
    pub name: String,
    pub cost: i32,
}

#[derive(serde::Serialize)]
pub struct PantheonRow {
    pub name: String,
    pub description: String,
}

// ---------------------------------------------------------------------------
// Form inputs
// ---------------------------------------------------------------------------

#[derive(serde::Deserialize)]
pub struct TempleWorkForm {
    pub rep: Option<i32>,
}

#[derive(serde::Deserialize)]
pub struct PrayerForm {
    pub praytype: Option<i32>,
    pub pray: Option<usize>,
}

// ---------------------------------------------------------------------------
// GET /temple
// ---------------------------------------------------------------------------

pub async fn temple_show(
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

    let Some(loc) = Location::from_db(&player_row.location) else {
        return crate::page::redirect("/login");
    };

    if !loc.is_city() {
        return error_page(&state, &ctx, "Nie znajdujesz się w mieście.");
    }

    let deity_display = player_row.deity.clone().unwrap_or_default();

    let meta = PageMeta::titled("Świątynia");
    let base = state.templates.build_context(&ctx, &meta);

    let view = TempleView {
        base,
        deity: deity_display,
        location: player_row.location.clone(),
        section: String::new(),
        blessings: vec![],
        pantheon: vec![],
        book_page: 0,
    };

    state.templates.render_value("temple.html", &view)
}

// ---------------------------------------------------------------------------
// GET /temple/work
// ---------------------------------------------------------------------------

pub async fn temple_work_show(
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

    if player_row.deity.as_ref().is_none_or(String::is_empty) {
        return error_page(&state, &ctx, "Nie masz wyznania!");
    }

    let meta = PageMeta::titled("Służba w świątyni");
    let base = state.templates.build_context(&ctx, &meta);

    let view = TempleView {
        base,
        deity: player_row.deity.clone().unwrap_or_default(),
        location: player_row.location.clone(),
        section: "work".to_owned(),
        blessings: vec![],
        pantheon: vec![],
        book_page: 0,
    };

    state.templates.render_value("temple.html", &view)
}

// ---------------------------------------------------------------------------
// POST /temple/work
// ---------------------------------------------------------------------------

pub async fn temple_work_action(
    State(state): State<AppState>,
    Extension(ctx): Extension<RequestContext>,
    Form(form): Form<TempleWorkForm>,
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

    let amount = match form.rep {
        Some(a) if a > 0 => a,
        _ => return error_page(&state, &ctx, "Podaj ilość pracy."),
    };

    match temple::compute_temple_work(&player_row.deity, player_row.hp, player_row.energy, amount) {
        Ok(result) => {
            if let Err(e) = vallheru_data::queries::locations::temple_work(
                &state.pool,
                player_id,
                result.energy_cost,
                result.piety_gained,
            )
            .await
            {
                tracing::error!(error = %e, "temple_work DB failed");
                return server_error();
            }

            let msg = format!(
                "Przepracowałeś {} punktów i zyskałeś tyle samo punktów wiary.",
                result.piety_gained
            );
            let meta = PageMeta::titled("Służba w świątyni").with_flash(Flash::success(msg));
            let base = state.templates.build_context(&ctx, &meta);

            let view = TempleView {
                base,
                deity: player_row.deity.clone().unwrap_or_default(),
                location: player_row.location.clone(),
                section: "work".to_owned(),
                blessings: vec![],
                pantheon: vec![],
                book_page: 0,
            };
            state.templates.render_value("temple.html", &view)
        }
        Err(temple::TempleWorkError::NoDeity) => error_page(&state, &ctx, "Nie masz wyznania!"),
        Err(temple::TempleWorkError::Dead) => {
            error_page(&state, &ctx, "Nie możesz pracować, ponieważ jesteś martwy!")
        }
        Err(temple::TempleWorkError::InsufficientEnergy { .. }) => {
            error_page(&state, &ctx, "Nie masz tyle energii.")
        }
        Err(temple::TempleWorkError::InvalidAmount) => {
            error_page(&state, &ctx, "Podaj prawidłową ilość.")
        }
    }
}

// ---------------------------------------------------------------------------
// GET /temple/prayer
// ---------------------------------------------------------------------------

pub async fn temple_prayer_show(
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

    if !player_row.bless.is_empty() {
        return error_page(&state, &ctx, "Masz już aktywne błogosławieństwo.");
    }

    let deity_str = player_row.deity.clone().unwrap_or_default();
    let blessings = match temple::available_blessings(&player_row.race, &deity_str) {
        Some(b) => b
            .iter()
            .enumerate()
            .map(|(i, opt)| BlessingRow {
                index: i,
                name: opt.name.to_owned(),
                cost: opt.cost,
            })
            .collect(),
        None => return error_page(&state, &ctx, "Nie masz wyznania!"),
    };

    let meta = PageMeta::titled("Modlitwa");
    let base = state.templates.build_context(&ctx, &meta);

    let view = TempleView {
        base,
        deity: deity_str,
        location: player_row.location.clone(),
        section: "prayer".to_owned(),
        blessings,
        pantheon: vec![],
        book_page: 0,
    };

    state.templates.render_value("temple.html", &view)
}

// ---------------------------------------------------------------------------
// POST /temple/prayer
// ---------------------------------------------------------------------------

pub async fn temple_prayer_action(
    State(state): State<AppState>,
    Extension(ctx): Extension<RequestContext>,
    Form(form): Form<PrayerForm>,
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

    let energy_offered = form.praytype.unwrap_or(0);
    let Some(blessing_index) = form.pray else {
        return error_page(&state, &ctx, "Wybierz błogosławieństwo.");
    };

    let deity_str = player_row.deity.clone().unwrap_or_default();
    let Some(blessings) = temple::available_blessings(&player_row.race, &deity_str) else {
        return error_page(&state, &ctx, "Nie masz wyznania!");
    };

    // Load the stat/skill value for the chosen blessing.
    let stat_level = if let Some(opt) = blessings.get(blessing_index) {
        load_stat_or_skill(&state, player_id, opt.stat_key).await
    } else {
        0
    };

    let roll: i32 = rand::thread_rng().gen_range(1..=10);

    #[allow(clippy::cast_possible_truncation)]
    let energy_int = player_row.energy as i32;

    match temple::resolve_prayer(
        player_row.hp,
        &player_row.bless,
        player_row.pw,
        energy_int,
        energy_offered,
        &blessings,
        blessing_index,
        stat_level,
        roll,
    ) {
        Ok((outcome, cost)) => {
            let deity_display = deity_display_name(&deity_str);
            let result =
                persist_prayer_outcome(&state, player_id, &outcome, &cost, &deity_display).await;
            let (msg, flash_kind) = match result {
                Ok(pair) => pair,
                Err(resp) => return resp,
            };

            let meta = PageMeta::titled("Modlitwa").with_flash(Flash {
                kind: flash_kind,
                message: msg,
            });
            let base = state.templates.build_context(&ctx, &meta);

            let view = TempleView {
                base,
                deity: deity_str,
                location: player_row.location.clone(),
                section: "prayer".to_owned(),
                blessings: vec![],
                pantheon: vec![],
                book_page: 0,
            };
            state.templates.render_value("temple.html", &view)
        }
        Err(temple::PrayerError::Dead) => error_page(
            &state,
            &ctx,
            "Nie możesz się modlić, ponieważ jesteś martwy!",
        ),
        Err(temple::PrayerError::AlreadyBlessed) => {
            error_page(&state, &ctx, "Masz już aktywne błogosławieństwo.")
        }
        Err(temple::PrayerError::InvalidSelection) => {
            error_page(&state, &ctx, "Nieprawidłowy wybór.")
        }
        Err(temple::PrayerError::InsufficientEnergy { .. }) => {
            error_page(&state, &ctx, "Nie masz tyle energii.")
        }
        Err(temple::PrayerError::InsufficientPiety { .. }) => {
            error_page(&state, &ctx, "Nie masz wystarczająco dużo punktów wiary.")
        }
    }
}

// ---------------------------------------------------------------------------
// GET /temple/book, /temple/book/{page}
// ---------------------------------------------------------------------------

/// Persist a prayer outcome to DB and return (message, `FlashKind`).
async fn persist_prayer_outcome(
    state: &AppState,
    player_id: i32,
    outcome: &temple::PrayerOutcome,
    cost: &temple::PrayerCost,
    deity_display: &str,
) -> Result<(String, FlashKind), Response> {
    match outcome {
        temple::PrayerOutcome::Success {
            stat_name,
            blessing_value,
            stat_key,
        } => {
            vallheru_data::queries::locations::apply_blessing(
                &state.pool,
                player_id,
                stat_key,
                *blessing_value,
                cost.piety,
                cost.energy,
            )
            .await
            .map_err(|e| {
                tracing::error!(error = %e, "apply_blessing DB failed");
                server_error()
            })?;
            Ok((
                format!(
                    "Modliłeś się do {deity_display}. Modlitwa powiodła się! \
                     Otrzymujesz błogosławieństwo {stat_name}."
                ),
                FlashKind::Success,
            ))
        }
        temple::PrayerOutcome::Failure => {
            vallheru_data::queries::locations::prayer_fail(
                &state.pool,
                player_id,
                cost.piety,
                cost.energy,
            )
            .await
            .map_err(|e| {
                tracing::error!(error = %e, "prayer_fail DB failed");
                server_error()
            })?;
            Ok((
                format!("Modliłeś się do {deity_display}, ale bóg zignorował twoją prośbę."),
                FlashKind::Error,
            ))
        }
        temple::PrayerOutcome::DeityWrath => {
            vallheru_data::queries::locations::prayer_wrath(
                &state.pool,
                player_id,
                cost.piety,
                cost.energy,
            )
            .await
            .map_err(|e| {
                tracing::error!(error = %e, "prayer_wrath DB failed");
                server_error()
            })?;
            Ok((
                format!("Modliłeś się do {deity_display}, ale bóg się rozgniewał i zabił cię!"),
                FlashKind::Error,
            ))
        }
    }
}

// ---------------------------------------------------------------------------
// GET /temple/book, /temple/book/{page}
// ---------------------------------------------------------------------------

pub async fn temple_book(
    State(state): State<AppState>,
    Extension(ctx): Extension<RequestContext>,
) -> Response {
    let meta = PageMeta::titled("Księga świątyni");
    let base = state.templates.build_context(&ctx, &meta);

    let view = TempleView {
        base,
        deity: String::new(),
        location: String::new(),
        section: "book".to_owned(),
        blessings: vec![],
        pantheon: vec![],
        book_page: 0,
    };
    state.templates.render_value("temple.html", &view)
}

// ---------------------------------------------------------------------------
// GET /temple/pantheon
// ---------------------------------------------------------------------------

pub async fn temple_pantheon(
    State(state): State<AppState>,
    Extension(ctx): Extension<RequestContext>,
) -> Response {
    let deities: Vec<PantheonRow> = temple::pantheon()
        .into_iter()
        .map(|d| PantheonRow {
            name: d.name.to_owned(),
            description: d.description.to_owned(),
        })
        .collect();

    let meta = PageMeta::titled("Panteon bogów");
    let base = state.templates.build_context(&ctx, &meta);

    let view = TempleView {
        base,
        deity: String::new(),
        location: String::new(),
        section: "pantheon".to_owned(),
        blessings: vec![],
        pantheon: deities,
        book_page: 0,
    };
    state.templates.render_value("temple.html", &view)
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
            tracing::error!(error = %e, "temple: load_player failed");
            Err(server_error())
        }
    }
}

async fn load_stat_or_skill(app: &AppState, player_id: i32, key: &str) -> i32 {
    // Try stats first, then skills.
    let player_stats = match vallheru_data::queries::player::load_stats(&app.pool, player_id).await
    {
        Ok(v) => v,
        Err(e) => {
            tracing::error!(error = %e, player_id, key, "load_stat_or_skill: load_stats failed");
            Vec::new()
        }
    };
    if let Some(stat) = player_stats.iter().find(|st| st.stat_key == key) {
        return stat.trained;
    }

    let skills = match vallheru_data::queries::player::load_skills(&app.pool, player_id).await {
        Ok(v) => v,
        Err(e) => {
            tracing::error!(error = %e, player_id, key, "load_stat_or_skill: load_skills failed");
            Vec::new()
        }
    };
    skills
        .iter()
        .find(|sk| sk.skill_key == key)
        .map_or(0, |sk| sk.level)
}

/// Get the display name of a deity (with grammatical case adjustments from PHP).
fn deity_display_name(deity_str: &str) -> String {
    match deity_str {
        "Heluvald" => "Heluvalda".to_owned(),
        "Karserth" => "Karsertha".to_owned(),
        _ => deity_str.to_owned(),
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
