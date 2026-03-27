//! Player housing handler.
//!
//! Ported from `house.php`. Covers: landing page, land purchase, building,
//! house overview, bedroom rest, wardrobe (basic), house listing, sale/buy,
//! locator management, rename.

use axum::{
    Extension, Form,
    extract::{Query, State},
    response::Response,
};
use rand::Rng;

use crate::middleware::context::RequestContext;
use crate::page::{Flash, FlashKind, PageMeta};
use crate::state::AppState;
use vallheru_data::queries::house as hq;
use vallheru_domain::location::Location;
use vallheru_domain::temple::house_type_name;

// ---------------------------------------------------------------------------
// View models
// ---------------------------------------------------------------------------

#[derive(serde::Serialize)]
pub struct HouseView {
    #[serde(flatten)]
    pub base: crate::render::RenderContext,
    /// Which section: "" (menu), "my", "land", "build", "list", "rent".
    pub section: String,
    pub has_house: bool,
    pub house: Option<HouseInfo>,
    pub houses_list: Vec<HouseListEntry>,
    pub for_sale_list: Vec<HouseForSaleEntry>,
    pub land_cost: String,
    pub player_id: i32,
}

#[derive(serde::Serialize)]
pub struct HouseInfo {
    pub id: i32,
    pub name: String,
    pub house_type: String,
    pub size: i32,
    pub build: i32,
    pub value: i32,
    pub points: i32,
    pub unused_rooms: i32,
    pub has_bedroom: bool,
    pub wardrobe_count: i32,
    pub owner_name: String,
    pub owner_id: i32,
    pub locator_name: String,
    pub locator_id: i32,
    pub is_owner: bool,
    pub item_count: i32,
}

#[derive(serde::Serialize)]
pub struct HouseListEntry {
    pub name: String,
    pub house_type: String,
    pub size: i32,
    pub build: i32,
    pub owner_name: String,
}

#[derive(serde::Serialize)]
pub struct HouseForSaleEntry {
    pub id: i32,
    pub name: String,
    pub house_type: String,
    pub build: i32,
    pub cost: i32,
    pub seller_name: String,
    pub is_own: bool,
}

// ---------------------------------------------------------------------------
// Query / form params
// ---------------------------------------------------------------------------

#[derive(serde::Deserialize, Default)]
pub struct HouseQuery {
    pub action: Option<String>,
    pub step: Option<String>,
}

#[derive(serde::Deserialize)]
pub struct HouseBuildForm {
    pub name: Option<String>,
}

#[derive(serde::Deserialize)]
pub struct HouseAdornForm {
    pub points: Option<i32>,
}

#[derive(serde::Deserialize)]
pub struct HouseRenameForm {
    pub name: Option<String>,
}

#[derive(serde::Deserialize)]
pub struct HouseSellForm {
    pub cost: Option<i32>,
}

#[derive(serde::Deserialize)]
pub struct HouseLocatorForm {
    pub lid: Option<i32>,
    pub loc: Option<String>,
}

// ---------------------------------------------------------------------------
// GET /house — main hub
// ---------------------------------------------------------------------------

pub async fn house_show(
    State(state): State<AppState>,
    Extension(ctx): Extension<RequestContext>,
    Query(query): Query<HouseQuery>,
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

    let house = hq::find_player_house(&state.pool, player_id, &player_row.location)
        .await
        .ok()
        .flatten();

    let has_house = house.is_some();

    match query.action.as_deref() {
        Some("my") => render_my_house(&state, &ctx, player_id, &player_row.location, house).await,
        Some("land") => render_land(&state, &ctx, player_id, house.as_ref()),
        Some("build") => render_build(&state, &ctx, player_id, house),
        Some("list") => render_list(&state, &ctx, &player_row.location).await,
        Some("rent") => render_rent(&state, &ctx, player_id, &player_row.location, has_house).await,
        _ => {
            let meta = PageMeta::titled("Domy");
            let base = state.templates.build_context(&ctx, &meta);
            state.templates.render_value(
                "house.html",
                &HouseView {
                    base,
                    section: String::new(),
                    has_house,
                    house: None,
                    houses_list: vec![],
                    for_sale_list: vec![],
                    land_cost: String::new(),
                    player_id,
                },
            )
        }
    }
}

// ---------------------------------------------------------------------------
// POST /house/land — buy land
// ---------------------------------------------------------------------------

pub async fn house_buy_land(
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

    let house = hq::find_player_house(&state.pool, player_id, &player_row.location)
        .await
        .ok()
        .flatten();

    match house {
        None => {
            // First land purchase — costs 20 platinum.
            if player_row.platinum < 20 {
                return error_page(
                    &state,
                    &ctx,
                    "Nie masz wystarczająco mithrilu aby kupić ziemię.",
                );
            }
            if let Err(e) = hq::buy_initial_land(&state.pool, player_id, &player_row.location).await
            {
                tracing::error!(error = %e, "buy_initial_land DB failed");
                return server_error();
            }
            let meta = PageMeta::titled("Domy")
                .with_flash(Flash::success("Kupiłeś działkę ziemi.".to_owned()));
            let base = state.templates.build_context(&ctx, &meta);
            state.templates.render_value(
                "house.html",
                &HouseView {
                    base,
                    section: String::new(),
                    has_house: true,
                    house: None,
                    houses_list: vec![],
                    for_sale_list: vec![],
                    land_cost: String::new(),
                    player_id,
                },
            )
        }
        Some(h) => {
            // Expand land — costs size * 1000 gold.
            let cost = h.size * 1000;
            if player_row.credits < cost {
                return error_page(&state, &ctx, "Nie masz wystarczająco złota.");
            }
            if let Err(e) = hq::expand_land(&state.pool, h.id, player_id, cost).await {
                tracing::error!(error = %e, "expand_land DB failed");
                return server_error();
            }
            let meta = PageMeta::titled("Domy")
                .with_flash(Flash::success("Powiększyłeś swoją działkę.".to_owned()));
            let base = state.templates.build_context(&ctx, &meta);
            state.templates.render_value(
                "house.html",
                &HouseView {
                    base,
                    section: String::new(),
                    has_house: true,
                    house: None,
                    houses_list: vec![],
                    for_sale_list: vec![],
                    land_cost: String::new(),
                    player_id,
                },
            )
        }
    }
}

// ---------------------------------------------------------------------------
// POST /house/build — build or upgrade house
// ---------------------------------------------------------------------------

pub async fn house_build_action(
    State(state): State<AppState>,
    Extension(ctx): Extension<RequestContext>,
    Form(form): Form<HouseBuildForm>,
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

    let Some(house) = hq::find_player_house(&state.pool, player_id, &player_row.location)
        .await
        .ok()
        .flatten()
    else {
        return error_page(&state, &ctx, "Nie posiadasz ziemi.");
    };

    if house.points < 10 {
        return error_page(&state, &ctx, "Nie masz wystarczająco punktów budowy.");
    }

    if house.build == 0 {
        // Build new house.
        if player_row.credits < 1000 {
            return error_page(&state, &ctx, "Nie masz wystarczająco złota.");
        }
        let name = form
            .name
            .as_deref()
            .unwrap_or("Dom")
            .chars()
            .filter(|c| !matches!(c, '<' | '>' | '&'))
            .take(100)
            .collect::<String>();
        if let Err(e) = hq::build_house(&state.pool, house.id, player_id, &name).await {
            tracing::error!(error = %e, "build_house DB failed");
            return server_error();
        }
        let meta =
            PageMeta::titled("Domy").with_flash(Flash::success("Zbudowałeś swój dom.".to_owned()));
        let base = state.templates.build_context(&ctx, &meta);
        state.templates.render_value(
            "house.html",
            &HouseView {
                base,
                section: String::new(),
                has_house: true,
                house: None,
                houses_list: vec![],
                for_sale_list: vec![],
                land_cost: String::new(),
                player_id,
            },
        )
    } else {
        // Upgrade existing house.
        let cost = 1000 * house.build;
        if player_row.credits < cost {
            return error_page(&state, &ctx, "Nie masz wystarczająco złota.");
        }
        if house.size == house.build {
            return error_page(&state, &ctx, "Nie masz miejsca na rozbudowę.");
        }
        let new_value = (house.value - 10).max(1);
        if let Err(e) = hq::upgrade_house(&state.pool, house.id, player_id, cost, new_value).await {
            tracing::error!(error = %e, "upgrade_house DB failed");
            return server_error();
        }

        let meta = PageMeta::titled("Domy")
            .with_flash(Flash::success("Rozbudowałeś swój dom.".to_owned()));
        let base = state.templates.build_context(&ctx, &meta);
        state.templates.render_value(
            "house.html",
            &HouseView {
                base,
                section: String::new(),
                has_house: true,
                house: None,
                houses_list: vec![],
                for_sale_list: vec![],
                land_cost: String::new(),
                player_id,
            },
        )
    }
}

// ---------------------------------------------------------------------------
// POST /house/bedroom — build bedroom
// ---------------------------------------------------------------------------

pub async fn house_build_bedroom(
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

    let Some(house) = hq::find_player_house(&state.pool, player_id, &player_row.location)
        .await
        .ok()
        .flatten()
    else {
        return error_page(&state, &ctx, "Nie posiadasz domu.");
    };

    if house.used >= house.build {
        return error_page(&state, &ctx, "Brak wolnych pomieszczeń.");
    }
    if house.bedroom {
        return error_page(&state, &ctx, "Masz już sypialnię.");
    }
    if player_row.credits < 10000 {
        return error_page(&state, &ctx, "Nie masz wystarczająco złota.");
    }
    if house.points < 10 {
        return error_page(&state, &ctx, "Nie masz wystarczająco punktów budowy.");
    }

    if let Err(e) = hq::build_bedroom(&state.pool, house.id, player_id).await {
        tracing::error!(error = %e, "build_bedroom DB failed");
        return server_error();
    }

    let _meta =
        PageMeta::titled("Domy").with_flash(Flash::success("Wybudowałeś sypialnię.".to_owned()));
    redirect_to_house()
}

// ---------------------------------------------------------------------------
// POST /house/wardrobe — build wardrobe
// ---------------------------------------------------------------------------

pub async fn house_build_wardrobe(
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

    let Some(house) = hq::find_player_house(&state.pool, player_id, &player_row.location)
        .await
        .ok()
        .flatten()
    else {
        return error_page(&state, &ctx, "Nie posiadasz domu.");
    };

    if house.used >= house.build {
        return error_page(&state, &ctx, "Brak wolnych pomieszczeń.");
    }
    if house.points < 10 {
        return error_page(&state, &ctx, "Nie masz wystarczająco punktów budowy.");
    }

    let cost = if house.wardrobe == 0 {
        1000
    } else {
        house.wardrobe * 1000
    };
    if player_row.credits < cost {
        return error_page(&state, &ctx, "Nie masz wystarczająco złota.");
    }

    if let Err(e) = hq::build_wardrobe(&state.pool, house.id, player_id, cost).await {
        tracing::error!(error = %e, "build_wardrobe DB failed");
        return server_error();
    }

    redirect_to_house()
}

// ---------------------------------------------------------------------------
// POST /house/adorn — adorn / increase value
// ---------------------------------------------------------------------------

pub async fn house_adorn(
    State(state): State<AppState>,
    Extension(ctx): Extension<RequestContext>,
    Form(form): Form<HouseAdornForm>,
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

    let Some(house) = hq::find_player_house(&state.pool, player_id, &player_row.location)
        .await
        .ok()
        .flatten()
    else {
        return error_page(&state, &ctx, "Nie posiadasz domu.");
    };

    let points = match form.points {
        Some(p) if p > 0 => p,
        _ => return error_page(&state, &ctx, "Podaj ilość punktów."),
    };

    if points > house.points {
        return error_page(&state, &ctx, "Nie masz tylu punktów budowy.");
    }

    let gold_cost = 1000 * points;
    if player_row.credits < gold_cost {
        return error_page(&state, &ctx, "Nie masz wystarczająco złota.");
    }

    if let Err(e) = hq::adorn_house(&state.pool, house.id, player_id, points, gold_cost).await {
        tracing::error!(error = %e, "adorn_house DB failed");
        return server_error();
    }

    redirect_to_house()
}

// ---------------------------------------------------------------------------
// POST /house/rest — rest in bedroom
// ---------------------------------------------------------------------------

pub async fn house_rest(
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

    let Some(house) = hq::find_player_house(&state.pool, player_id, &player_row.location)
        .await
        .ok()
        .flatten()
    else {
        return error_page(&state, &ctx, "Nie posiadasz domu.");
    };

    if !house.bedroom {
        return error_page(&state, &ctx, "Nie masz sypialni.");
    }
    if player_row.hp <= 0 {
        return error_page(&state, &ctx, "Jesteś martwy!");
    }
    if player_row.race.is_empty() || player_row.class.is_empty() {
        return error_page(&state, &ctx, "Musisz najpierw wybrać rasę i klasę.");
    }
    if player_row.house_rest {
        return error_page(&state, &ctx, "Możesz odpoczywać w domu tylko raz dziennie.");
    }

    let roll: i32 = rand::thread_rng().gen_range(1..=100);
    if roll <= 5 {
        // Bad luck — set house_rest flag but no gains.
        if let Err(e) =
            hq::house_rest(&state.pool, player_id, player_row.hp, 0.0, player_row.pm).await
        {
            tracing::error!(error = %e, "house_rest (bad luck) DB failed");
            return server_error();
        }
        let meta = PageMeta::titled("Odpoczynek").with_flash(Flash::error(
            "Nie udało ci się wypocząć. Źle spałeś.".to_owned(),
        ));
        let base = state.templates.build_context(&ctx, &meta);
        return state.templates.render("error.html", &base);
    }

    // Calculate gains based on house value percentage.
    let value = house.value;

    #[allow(clippy::cast_possible_truncation, clippy::cast_precision_loss)]
    let energy_gain =
        ((player_row.max_energy / 100.0) * f64::from(value)).ceil().min(player_row.max_energy * 0.75);

    #[allow(clippy::cast_possible_truncation)]
    let hp_gain = ((f64::from(player_row.max_hp) / 100.0) * f64::from(value)).ceil() as i32;
    let new_hp = (player_row.hp + 4 * hp_gain).min(player_row.max_hp);

    // Max mana calculation (simplified — same as locations.rs compute_max_mana).
    let player_stats = vallheru_data::queries::player::load_stats(&state.pool, player_id)
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

    #[allow(clippy::cast_possible_truncation)]
    let mana_gain = ((f64::from(max_mana) / 100.0) * f64::from(value)).ceil() as i32;
    let new_mana = (player_row.pm + 4 * mana_gain).min(max_mana);

    if let Err(e) = hq::house_rest(&state.pool, player_id, new_hp, energy_gain, new_mana).await {
        tracing::error!(error = %e, "house_rest DB failed");
        return server_error();
    }

    #[allow(clippy::cast_possible_truncation)]
    let energy_display = energy_gain as i32;
    let hp_gained = new_hp - player_row.hp;
    let mana_gained = (new_mana - player_row.pm).max(0);
    let msg = format!(
        "Odpocząłeś i odzyskałeś {energy_display} energii, {hp_gained} punktów życia, \
         {mana_gained} punktów magii."
    );

    let meta = PageMeta::titled("Odpoczynek").with_flash(Flash::success(msg));
    let base = state.templates.build_context(&ctx, &meta);
    state.templates.render("error.html", &base)
}

// ---------------------------------------------------------------------------
// POST /house/rename
// ---------------------------------------------------------------------------

pub async fn house_rename(
    State(state): State<AppState>,
    Extension(ctx): Extension<RequestContext>,
    Form(form): Form<HouseRenameForm>,
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

    let Some(house) = hq::find_player_house(&state.pool, player_id, &player_row.location)
        .await
        .ok()
        .flatten()
    else {
        return error_page(&state, &ctx, "Nie posiadasz domu.");
    };

    if house.owner != player_id {
        return error_page(&state, &ctx, "Tylko właściciel może zmienić nazwę.");
    }

    let name = match form.name {
        Some(ref n) if !n.is_empty() => n
            .chars()
            .filter(|c| !matches!(c, '<' | '>' | '&'))
            .take(100)
            .collect::<String>(),
        _ => return error_page(&state, &ctx, "Podaj nową nazwę domu."),
    };

    if let Err(e) = hq::rename_house(&state.pool, house.id, &name).await {
        tracing::error!(error = %e, "rename_house DB failed");
        return server_error();
    }

    redirect_to_house()
}

// ---------------------------------------------------------------------------
// POST /house/sell
// ---------------------------------------------------------------------------

pub async fn house_sell(
    State(state): State<AppState>,
    Extension(ctx): Extension<RequestContext>,
    Form(form): Form<HouseSellForm>,
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

    let Some(house) = hq::find_player_house(&state.pool, player_id, &player_row.location)
        .await
        .ok()
        .flatten()
    else {
        return error_page(&state, &ctx, "Nie posiadasz domu.");
    };

    if house.owner != player_id {
        return error_page(&state, &ctx, "Tylko właściciel może sprzedać dom.");
    }

    let price = match form.cost {
        Some(c) if c > 0 => c,
        _ => return error_page(&state, &ctx, "Podaj cenę sprzedaży."),
    };

    if let Err(e) = hq::sell_house(&state.pool, house.id, player_id, price).await {
        tracing::error!(error = %e, "sell_house DB failed");
        return server_error();
    }

    let meta = PageMeta::titled("Domy").with_flash(Flash::success(format!(
        "Wystawiłeś dom na sprzedaż za {price} sztuk złota."
    )));
    let base = state.templates.build_context(&ctx, &meta);
    state.templates.render_value(
        "house.html",
        &HouseView {
            base,
            section: String::new(),
            has_house: false,
            house: None,
            houses_list: vec![],
            for_sale_list: vec![],
            land_cost: String::new(),
            player_id,
        },
    )
}

// ---------------------------------------------------------------------------
// POST /house/leave
// ---------------------------------------------------------------------------

pub async fn house_leave(
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

    let Some(house) = hq::find_player_house(&state.pool, player_id, &player_row.location)
        .await
        .ok()
        .flatten()
    else {
        return error_page(&state, &ctx, "Nie posiadasz domu.");
    };

    if player_id == house.locator {
        if let Err(e) = hq::leave_house_locator(&state.pool, house.id).await {
            tracing::error!(error = %e, "leave_house_locator DB failed");
            return server_error();
        }
    } else if player_id == house.owner {
        let has_locator = house.locator > 0;
        if let Err(e) =
            hq::leave_house_owner(&state.pool, house.id, has_locator, house.locator).await
        {
            tracing::error!(error = %e, "leave_house_owner DB failed");
            return server_error();
        }
    } else {
        return error_page(&state, &ctx, "To nie jest twój dom.");
    }

    let meta = PageMeta::titled("Domy").with_flash(Flash::success("Opuściłeś dom.".to_owned()));
    let base = state.templates.build_context(&ctx, &meta);
    state.templates.render_value(
        "house.html",
        &HouseView {
            base,
            section: String::new(),
            has_house: false,
            house: None,
            houses_list: vec![],
            for_sale_list: vec![],
            land_cost: String::new(),
            player_id,
        },
    )
}

// ---------------------------------------------------------------------------
// POST /house/buy/{id}
// ---------------------------------------------------------------------------

pub async fn house_buy(
    State(state): State<AppState>,
    Extension(ctx): Extension<RequestContext>,
    axum::extract::Path(house_id): axum::extract::Path<i32>,
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

    // Player must not already have a house.
    let existing = hq::find_player_house(&state.pool, player_id, &player_row.location)
        .await
        .ok()
        .flatten();
    if existing.is_some() {
        return error_page(&state, &ctx, "Masz już dom w tym mieście.");
    }

    let Some(target) = hq::find_house_by_id(&state.pool, house_id)
        .await
        .ok()
        .flatten()
    else {
        return error_page(&state, &ctx, "Dom nie istnieje.");
    };

    if target.owner != 0 {
        return error_page(&state, &ctx, "Ten dom nie jest na sprzedaż.");
    }

    if player_row.credits < target.cost {
        return error_page(&state, &ctx, "Nie masz wystarczająco złota.");
    }

    if let Err(e) =
        hq::buy_house(&state.pool, house_id, player_id, target.seller, target.cost).await
    {
        tracing::error!(error = %e, "buy_house DB failed");
        return server_error();
    }

    redirect_to_house()
}

// ---------------------------------------------------------------------------
// Sub-view renderers
// ---------------------------------------------------------------------------

async fn render_my_house(
    state: &AppState,
    ctx: &RequestContext,
    player_id: i32,
    location: &str,
    house: Option<hq::HouseRow>,
) -> Response {
    let Some(house) = house else {
        return error_page(state, ctx, "Nie posiadasz domu.");
    };

    if house.build == 0 {
        return error_page(state, ctx, "Nie masz jeszcze wybudowanego domu.");
    }

    let owner_name = hq::player_username(&state.pool, house.owner)
        .await
        .ok()
        .flatten()
        .unwrap_or_else(|| "Nieznany".to_owned());

    let locator_name = if house.locator > 0 {
        hq::player_username(&state.pool, house.locator)
            .await
            .ok()
            .flatten()
            .unwrap_or_else(|| "Nieznany".to_owned())
    } else {
        "Brak".to_owned()
    };

    let item_count: i32 = sqlx::query_scalar(
        "SELECT COALESCE(SUM(amount), 0)::INT FROM equipment \
         WHERE owner = $1 AND status = 'H' AND location = $2",
    )
    .bind(player_id)
    .bind(location)
    .fetch_one(&state.pool)
    .await
    .unwrap_or(0);

    let info = HouseInfo {
        id: house.id,
        name: house.name.clone(),
        house_type: house_type_name(house.value, house.build).to_owned(),
        size: house.size,
        build: house.build,
        value: house.value,
        points: house.points,
        unused_rooms: house.build - house.used,
        has_bedroom: house.bedroom,
        wardrobe_count: house.wardrobe,
        owner_name,
        owner_id: house.owner,
        locator_name,
        locator_id: house.locator,
        is_owner: house.owner == player_id,
        item_count,
    };

    let title = format!("Dom: {}", house.name);
    let meta = PageMeta::titled(&title);
    let base = state.templates.build_context(ctx, &meta);

    state.templates.render_value(
        "house.html",
        &HouseView {
            base,
            section: "my".to_owned(),
            has_house: true,
            house: Some(info),
            houses_list: vec![],
            for_sale_list: vec![],
            land_cost: String::new(),
            player_id,
        },
    )
}

fn render_land(
    state: &AppState,
    ctx: &RequestContext,
    player_id: i32,
    house: Option<&hq::HouseRow>,
) -> Response {
    let land_cost = match house {
        None => "20 mithrilu".to_owned(),
        Some(h) => format!("{} sztuk złota", h.size * 1000),
    };

    let meta = PageMeta::titled("Zakup ziemi");
    let base = state.templates.build_context(ctx, &meta);

    state.templates.render_value(
        "house.html",
        &HouseView {
            base,
            section: "land".to_owned(),
            has_house: house.is_some(),
            house: None,
            houses_list: vec![],
            for_sale_list: vec![],
            land_cost,
            player_id,
        },
    )
}

fn render_build(
    state: &AppState,
    ctx: &RequestContext,
    player_id: i32,
    house: Option<hq::HouseRow>,
) -> Response {
    let Some(house) = house else {
        return error_page(state, ctx, "Nie posiadasz ziemi.");
    };

    if house.points == 0 {
        return error_page(state, ctx, "Nie masz punktów budowy.");
    }

    let info = HouseInfo {
        id: house.id,
        name: house.name.clone(),
        house_type: house_type_name(house.value, house.build).to_owned(),
        size: house.size,
        build: house.build,
        value: house.value,
        points: house.points,
        unused_rooms: house.build - house.used,
        has_bedroom: house.bedroom,
        wardrobe_count: house.wardrobe,
        owner_name: String::new(),
        owner_id: house.owner,
        locator_name: String::new(),
        locator_id: house.locator,
        is_owner: house.owner == player_id,
        item_count: 0,
    };

    let meta = PageMeta::titled("Warsztat budowlany");
    let base = state.templates.build_context(ctx, &meta);

    state.templates.render_value(
        "house.html",
        &HouseView {
            base,
            section: "build".to_owned(),
            has_house: true,
            house: Some(info),
            houses_list: vec![],
            for_sale_list: vec![],
            land_cost: String::new(),
            player_id,
        },
    )
}

async fn render_list(state: &AppState, ctx: &RequestContext, location: &str) -> Response {
    let rows = hq::list_houses(&state.pool, location)
        .await
        .unwrap_or_default();

    if rows.is_empty() {
        return error_page(
            state,
            ctx,
            "Nie ma jeszcze wybudowanych domów w tym mieście.",
        );
    }

    // Collect owner/locator IDs for name lookup.
    let mut player_ids: Vec<i32> = rows.iter().map(|r| r.owner).collect();
    player_ids.sort_unstable();
    player_ids.dedup();

    let mut name_map = std::collections::HashMap::new();
    for &pid in &player_ids {
        if let Ok(Some(name)) = hq::player_username(&state.pool, pid).await {
            name_map.insert(pid, name);
        }
    }

    let entries: Vec<HouseListEntry> = rows
        .iter()
        .map(|r| HouseListEntry {
            name: r.name.clone(),
            house_type: house_type_name(r.value, r.build).to_owned(),
            size: r.size,
            build: r.build,
            owner_name: name_map
                .get(&r.owner)
                .cloned()
                .unwrap_or_else(|| "Nieznany".to_owned()),
        })
        .collect();

    let meta = PageMeta::titled("Lista domów");
    let base = state.templates.build_context(ctx, &meta);

    state.templates.render_value(
        "house.html",
        &HouseView {
            base,
            section: "list".to_owned(),
            has_house: false,
            house: None,
            houses_list: entries,
            for_sale_list: vec![],
            land_cost: String::new(),
            player_id: 0,
        },
    )
}

async fn render_rent(
    state: &AppState,
    ctx: &RequestContext,
    player_id: i32,
    location: &str,
    has_house: bool,
) -> Response {
    let rows = hq::list_houses_for_sale(&state.pool, location)
        .await
        .unwrap_or_default();

    if rows.is_empty() {
        return error_page(
            state,
            ctx,
            "Nie ma jeszcze domów na sprzedaż w tym mieście.",
        );
    }

    let mut seller_ids: Vec<i32> = rows.iter().map(|r| r.seller).collect();
    seller_ids.sort_unstable();
    seller_ids.dedup();

    let mut name_map = std::collections::HashMap::new();
    for &sid in &seller_ids {
        if let Ok(Some(name)) = hq::player_username(&state.pool, sid).await {
            name_map.insert(sid, name);
        }
    }

    let entries: Vec<HouseForSaleEntry> = rows
        .iter()
        .map(|r| HouseForSaleEntry {
            id: r.id,
            name: r.name.clone(),
            house_type: house_type_name(r.value, r.build).to_owned(),
            build: r.build,
            cost: r.cost,
            seller_name: name_map
                .get(&r.seller)
                .cloned()
                .unwrap_or_else(|| "Nieznany".to_owned()),
            is_own: r.seller == player_id,
        })
        .collect();

    let meta = PageMeta::titled("Domy na sprzedaż");
    let base = state.templates.build_context(ctx, &meta);

    state.templates.render_value(
        "house.html",
        &HouseView {
            base,
            section: "rent".to_owned(),
            has_house,
            house: None,
            houses_list: vec![],
            for_sale_list: entries,
            land_cost: String::new(),
            player_id,
        },
    )
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
            tracing::error!(error = %e, "house: load_player failed");
            Err(server_error())
        }
    }
}

fn redirect_to_house() -> Response {
    crate::page::redirect("/house?action=my")
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
