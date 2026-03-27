//! NPC item shop handlers.
//!
//! Ported from `weapons.php`, `armor.php`, and `bows.php`. Each shop displays
//! a catalog of items and allows the player to buy them.

use axum::{
    Extension, Form, extract::Path, extract::State, response::IntoResponse, response::Response,
};

use crate::middleware::context::RequestContext;
use crate::page::{Flash, FlashKind, PageMeta};
use crate::state::AppState;

// =========================================================================
// View models
// =========================================================================

/// Generic shop catalog view.
#[derive(serde::Serialize)]
pub struct ShopView {
    #[serde(flatten)]
    pub base: crate::render::RenderContext,
    pub shop_name: &'static str,
    pub items: Vec<ShopItemEntry>,
    /// For armor shop: the current sub-category (A, H, L, S).
    pub category: String,
}

/// A single item listing in a shop catalog.
#[derive(serde::Serialize)]
pub struct ShopItemEntry {
    pub id: i32,
    pub name: String,
    pub power: i32,
    pub agility_mod: i32,
    pub speed_mod: i32,
    pub durability: i32,
    pub min_level: i32,
    pub cost: String,
    /// Route action for buy link (e.g. "buy" or "arrows").
    pub buy_action: &'static str,
}

/// Arrow purchase form view.
#[derive(serde::Serialize)]
pub struct ArrowBuyView {
    #[serde(flatten)]
    pub base: crate::render::RenderContext,
    pub arrow_id: i32,
    pub arrow_name: String,
    pub pack_cost: i64,
    pub pack_size: i32,
    pub per_arrow_cost: i64,
}

/// Form for buying an item by ID.
#[derive(serde::Deserialize)]
pub struct BuyForm {
    pub amount: Option<i32>,
}

// =========================================================================
// GET /weapons — weapon shop (weapons.php)
// =========================================================================

pub async fn weapons_show(
    State(app): State<AppState>,
    Extension(ctx): Extension<RequestContext>,
) -> Response {
    let Some(ref user) = ctx.session_user else {
        return crate::page::redirect("/login");
    };

    #[allow(clippy::cast_possible_truncation)]
    let player_id = user.id as i32;

    let player = match load_player(&app, player_id).await {
        Ok(p) => p,
        Err(resp) => return resp,
    };

    if player.location != "Altara" {
        return error_page(
            &app,
            &ctx,
            "Musisz być w Altarze, aby odwiedzić zbrojmistrza.",
        );
    }

    let items =
        match vallheru_data::queries::item::find_shop_items_by_type_and_lang(&app.pool, "W", "pl")
            .await
        {
            Ok(rows) => rows
                .into_iter()
                .map(|r| ShopItemEntry {
                    id: r.id,
                    name: r.name,
                    power: r.power,
                    agility_mod: r.zr,
                    speed_mod: r.szyb,
                    durability: r.wt,
                    min_level: r.minlev,
                    cost: r.cost.to_string(),
                    buy_action: "buy",
                })
                .collect(),
            Err(e) => {
                tracing::error!(error = %e, "weapons_show: failed to load catalog");
                return server_error();
            }
        };

    let meta = PageMeta::titled("Zbrojmistrz").with_back_link("/city", "Wróć do miasta");
    let base = app.templates.build_context(&ctx, &meta);
    let view = ShopView {
        base,
        shop_name: "Zbrojmistrz",
        items,
        category: String::new(),
    };
    app.templates.render_value("shop.html", &view)
}

// =========================================================================
// POST /weapons/buy/{id} — buy a weapon
// =========================================================================

pub async fn weapons_buy(
    State(app): State<AppState>,
    Extension(ctx): Extension<RequestContext>,
    Path(item_id): Path<i32>,
) -> Response {
    let Some(ref user) = ctx.session_user else {
        return crate::page::redirect("/login");
    };

    #[allow(clippy::cast_possible_truncation)]
    let player_id = user.id as i32;

    let player = match load_player(&app, player_id).await {
        Ok(p) => p,
        Err(resp) => return resp,
    };

    if player.location != "Altara" {
        return error_page(&app, &ctx, "Musisz być w Altarze.");
    }

    let shop_item = match vallheru_data::queries::item::find_equipment_by_id(&app.pool, item_id)
        .await
    {
        Ok(Some(row)) if row.status == "S" && row.owner == 0 && row.equipment_type == "W" => row,
        Ok(_) => return error_page(&app, &ctx, "Nie znaleziono przedmiotu."),
        Err(e) => {
            tracing::error!(error = %e, "weapons_buy: db error");
            return server_error();
        }
    };

    if shop_item.cost > i64::from(player.credits) {
        return error_page(&app, &ctx, "Nie masz wystarczająco złota.");
    }

    if let Err(e) =
        vallheru_data::queries::item::buy_shop_equipment(&app.pool, &shop_item, player_id).await
    {
        tracing::error!(error = %e, "weapons_buy: purchase failed");
        return server_error();
    }

    tracing::info!(player_id, item_id, "weapon purchased");
    let msg = format!(
        "Kupiono <b>{}</b> za <b>{}</b> sztuk złota.",
        shop_item.name, shop_item.cost
    );
    flash_and_redirect(&app, &ctx, &msg, "/weapons")
}

// =========================================================================
// GET /armor — armor shop (armor.php)
// =========================================================================

pub async fn armor_show(
    State(app): State<AppState>,
    Extension(ctx): Extension<RequestContext>,
) -> Response {
    let Some(ref user) = ctx.session_user else {
        return crate::page::redirect("/login");
    };

    #[allow(clippy::cast_possible_truncation)]
    let player_id = user.id as i32;

    let player = match load_player(&app, player_id).await {
        Ok(p) => p,
        Err(resp) => return resp,
    };

    if player.location != "Altara" {
        return error_page(&app, &ctx, "Musisz być w Altarze, aby odwiedzić płatnerza.");
    }

    let meta = PageMeta::titled("Płatnerz").with_back_link("/city", "Wróć do miasta");
    let base = app.templates.build_context(&ctx, &meta);
    let view = ShopView {
        base,
        shop_name: "Płatnerz",
        items: Vec::new(),
        category: String::new(),
    };
    app.templates.render_value("armor_shop.html", &view)
}

// =========================================================================
// GET /armor/{category} — armor shop with sub-category
// =========================================================================

pub async fn armor_category_show(
    State(app): State<AppState>,
    Extension(ctx): Extension<RequestContext>,
    Path(category): Path<String>,
) -> Response {
    let Some(ref user) = ctx.session_user else {
        return crate::page::redirect("/login");
    };

    #[allow(clippy::cast_possible_truncation)]
    let player_id = user.id as i32;

    let player = match load_player(&app, player_id).await {
        Ok(p) => p,
        Err(resp) => return resp,
    };

    if player.location != "Altara" {
        return error_page(&app, &ctx, "Musisz być w Altarze.");
    }

    if !matches!(category.as_str(), "A" | "H" | "L" | "S") {
        return error_page(&app, &ctx, "Nieznana kategoria.");
    }

    let items = match vallheru_data::queries::item::find_shop_items_by_type_and_lang(
        &app.pool, &category, "pl",
    )
    .await
    {
        Ok(rows) => rows
            .into_iter()
            .map(|r| ShopItemEntry {
                id: r.id,
                name: r.name,
                power: r.power,
                agility_mod: r.zr,
                speed_mod: r.szyb,
                durability: r.wt,
                min_level: r.minlev,
                cost: r.cost.to_string(),
                buy_action: "buy",
            })
            .collect(),
        Err(e) => {
            tracing::error!(error = %e, "armor_category_show: failed to load catalog");
            return server_error();
        }
    };

    let meta = PageMeta::titled("Płatnerz").with_back_link("/armor", "Wróć do płatnerza");
    let base = app.templates.build_context(&ctx, &meta);
    let view = ShopView {
        base,
        shop_name: "Płatnerz",
        items,
        category,
    };
    app.templates.render_value("armor_shop.html", &view)
}

// =========================================================================
// POST /armor/buy/{id} — buy armor/helmet/legs/shield
// =========================================================================

pub async fn armor_buy(
    State(app): State<AppState>,
    Extension(ctx): Extension<RequestContext>,
    Path(item_id): Path<i32>,
) -> Response {
    let Some(ref user) = ctx.session_user else {
        return crate::page::redirect("/login");
    };

    #[allow(clippy::cast_possible_truncation)]
    let player_id = user.id as i32;

    let player = match load_player(&app, player_id).await {
        Ok(p) => p,
        Err(resp) => return resp,
    };

    if player.location != "Altara" {
        return error_page(&app, &ctx, "Musisz być w Altarze.");
    }

    let valid_types = ["A", "H", "L", "S"];
    let shop_item =
        match vallheru_data::queries::item::find_equipment_by_id(&app.pool, item_id).await {
            Ok(Some(row))
                if row.status == "S"
                    && row.owner == 0
                    && valid_types.contains(&row.equipment_type.as_str()) =>
            {
                row
            }
            Ok(_) => return error_page(&app, &ctx, "Nie znaleziono przedmiotu."),
            Err(e) => {
                tracing::error!(error = %e, "armor_buy: db error");
                return server_error();
            }
        };

    if shop_item.cost > i64::from(player.credits) {
        return error_page(&app, &ctx, "Nie masz wystarczająco złota.");
    }

    if let Err(e) =
        vallheru_data::queries::item::buy_shop_equipment(&app.pool, &shop_item, player_id).await
    {
        tracing::error!(error = %e, "armor_buy: purchase failed");
        return server_error();
    }

    tracing::info!(player_id, item_id, "armor purchased");
    let msg = format!(
        "Kupiono <b>{}</b> za <b>{}</b> sztuk złota.",
        shop_item.name, shop_item.cost
    );
    flash_and_redirect(&app, &ctx, &msg, "/armor")
}

// =========================================================================
// GET /fletcher — bow and arrow shop (bows.php)
// =========================================================================

pub async fn fletcher_show(
    State(app): State<AppState>,
    Extension(ctx): Extension<RequestContext>,
) -> Response {
    let Some(ref user) = ctx.session_user else {
        return crate::page::redirect("/login");
    };

    #[allow(clippy::cast_possible_truncation)]
    let player_id = user.id as i32;

    let player = match load_player(&app, player_id).await {
        Ok(p) => p,
        Err(resp) => return resp,
    };

    if player.location != "Altara" && player.location != "Ardulith" {
        return error_page(&app, &ctx, "Musisz być w mieście, aby odwiedzić łucznika.");
    }

    let bows = match vallheru_data::queries::item::find_bow_catalog(&app.pool).await {
        Ok(rows) => rows
            .into_iter()
            .map(|r| {
                let cost = if r.bow_type == "R" {
                    // Arrows show pack cost / per-arrow cost
                    let per_arrow = (r.cost + 24) / 25; // ceil division
                    format!("{}/{}", r.cost, per_arrow)
                } else {
                    r.cost.to_string()
                };
                let buy_action = if r.bow_type == "R" { "arrows" } else { "buy" };
                ShopItemEntry {
                    id: r.id,
                    name: r.name,
                    power: r.power,
                    agility_mod: r.zr,
                    speed_mod: r.szyb,
                    durability: r.maxwt,
                    min_level: r.minlev,
                    cost,
                    buy_action,
                }
            })
            .collect(),
        Err(e) => {
            tracing::error!(error = %e, "fletcher_show: failed to load bow catalog");
            return server_error();
        }
    };

    let meta = PageMeta::titled("Łucznik").with_back_link("/city", "Wróć do miasta");
    let base = app.templates.build_context(&ctx, &meta);
    let view = ShopView {
        base,
        shop_name: "Łucznik",
        items: bows,
        category: String::new(),
    };
    app.templates.render_value("shop.html", &view)
}

// =========================================================================
// POST /fletcher/buy/{id} — buy a bow
// =========================================================================

pub async fn fletcher_buy_bow(
    State(app): State<AppState>,
    Extension(ctx): Extension<RequestContext>,
    Path(bow_id): Path<i32>,
) -> Response {
    let Some(ref user) = ctx.session_user else {
        return crate::page::redirect("/login");
    };

    #[allow(clippy::cast_possible_truncation)]
    let player_id = user.id as i32;

    let player = match load_player(&app, player_id).await {
        Ok(p) => p,
        Err(resp) => return resp,
    };

    if player.location != "Altara" && player.location != "Ardulith" {
        return error_page(&app, &ctx, "Musisz być w mieście.");
    }

    let bow = match vallheru_data::queries::item::find_bow_by_id(&app.pool, bow_id).await {
        Ok(Some(b)) if b.bow_type == "B" => b,
        Ok(_) => return error_page(&app, &ctx, "Nie znaleziono łuku."),
        Err(e) => {
            tracing::error!(error = %e, "fletcher_buy_bow: db error");
            return server_error();
        }
    };

    if bow.cost > i64::from(player.credits) {
        return error_page(&app, &ctx, "Nie masz wystarczająco złota.");
    }

    if let Err(e) = vallheru_data::queries::item::buy_bow(&app.pool, &bow, player_id).await {
        tracing::error!(error = %e, "fletcher_buy_bow: purchase failed");
        return server_error();
    }

    tracing::info!(player_id, bow_id, "bow purchased");
    let msg = format!(
        "Kupiono <b>{}</b> za <b>{}</b> sztuk złota.",
        bow.name, bow.cost
    );
    flash_and_redirect(&app, &ctx, &msg, "/fletcher")
}

// =========================================================================
// GET /fletcher/arrows/{id} — arrow purchase form
// =========================================================================

pub async fn fletcher_arrows_show(
    State(app): State<AppState>,
    Extension(ctx): Extension<RequestContext>,
    Path(bow_id): Path<i32>,
) -> Response {
    let Some(ref user) = ctx.session_user else {
        return crate::page::redirect("/login");
    };

    #[allow(clippy::cast_possible_truncation)]
    let player_id = user.id as i32;

    let player = match load_player(&app, player_id).await {
        Ok(p) => p,
        Err(resp) => return resp,
    };

    if player.location != "Altara" && player.location != "Ardulith" {
        return error_page(&app, &ctx, "Musisz być w mieście.");
    }

    let bow = match vallheru_data::queries::item::find_bow_by_id(&app.pool, bow_id).await {
        Ok(Some(b)) if b.bow_type == "R" => b,
        Ok(_) => return error_page(&app, &ctx, "Nie znaleziono strzał."),
        Err(e) => {
            tracing::error!(error = %e, "fletcher_arrows_show: db error");
            return server_error();
        }
    };

    let per_arrow = (bow.cost + 24) / 25;

    let meta = PageMeta::titled("Kup strzały").with_back_link("/fletcher", "Wróć do łucznika");
    let base = app.templates.build_context(&ctx, &meta);
    let view = ArrowBuyView {
        base,
        arrow_id: bow.id,
        arrow_name: bow.name,
        pack_cost: bow.cost,
        pack_size: bow.maxwt,
        per_arrow_cost: per_arrow,
    };
    app.templates.render_value("arrow_buy.html", &view)
}

// =========================================================================
// POST /fletcher/arrows/{id} — buy arrows
// =========================================================================

pub async fn fletcher_buy_arrows(
    State(app): State<AppState>,
    Extension(ctx): Extension<RequestContext>,
    Path(bow_id): Path<i32>,
    Form(form): Form<BuyForm>,
) -> Response {
    let Some(ref user) = ctx.session_user else {
        return crate::page::redirect("/login");
    };

    #[allow(clippy::cast_possible_truncation)]
    let player_id = user.id as i32;

    let player = match load_player(&app, player_id).await {
        Ok(p) => p,
        Err(resp) => return resp,
    };

    if player.location != "Altara" && player.location != "Ardulith" {
        return error_page(&app, &ctx, "Musisz być w mieście.");
    }

    let bow = match vallheru_data::queries::item::find_bow_by_id(&app.pool, bow_id).await {
        Ok(Some(b)) if b.bow_type == "R" => b,
        Ok(_) => return error_page(&app, &ctx, "Nie znaleziono strzał."),
        Err(e) => {
            tracing::error!(error = %e, "fletcher_buy_arrows: db error");
            return server_error();
        }
    };

    let packs = form.amount.unwrap_or(0);
    if packs <= 0 {
        return error_page(&app, &ctx, "Nieprawidłowa ilość.");
    }

    let arrow_count = bow.maxwt * packs;
    let total_cost = bow.cost * i64::from(packs);

    if total_cost > i64::from(player.credits) {
        return error_page(&app, &ctx, "Nie masz wystarczająco złota.");
    }

    if let Err(e) = vallheru_data::queries::item::buy_arrows(
        &app.pool,
        &bow,
        player_id,
        arrow_count,
        total_cost,
    )
    .await
    {
        tracing::error!(error = %e, "fletcher_buy_arrows: purchase failed");
        return server_error();
    }

    tracing::info!(player_id, bow_id, arrow_count, "arrows purchased");
    let msg = format!(
        "Kupiono <b>{}</b> strzał <b>{}</b> za <b>{}</b> sztuk złota.",
        arrow_count, bow.name, total_cost
    );
    flash_and_redirect(&app, &ctx, &msg, "/fletcher")
}

// =========================================================================
// Helpers
// =========================================================================

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

fn error_page(state: &AppState, ctx: &RequestContext, message: &str) -> Response {
    let meta = PageMeta::titled("Błąd").with_flash(Flash {
        kind: FlashKind::Error,
        message: message.to_owned(),
    });
    let base = state.templates.build_context(ctx, &meta);
    state.templates.render("error.html", &base)
}

fn flash_and_redirect(
    state: &AppState,
    ctx: &RequestContext,
    message: &str,
    _path: &str,
) -> Response {
    let meta = PageMeta::titled("Sukces").with_flash(Flash::success(message.to_owned()));
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
