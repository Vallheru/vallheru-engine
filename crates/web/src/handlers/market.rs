//! Player-to-player market handlers.
//!
//! Implements the market hub, per-category browse/buy/sell, my-offers,
//! and bulk-cancel flows for all 8 market categories.
//!
//! The PHP codebase uses 9 separate files; here we unify on a single
//! handler module parameterized by `MarketCategory`.

use axum::{
    Extension, Form,
    extract::{Path, Query, State},
    response::IntoResponse,
    response::Response,
};

use crate::middleware::context::RequestContext;
use crate::page::{Flash, FlashKind, PageMeta};
use crate::state::AppState;
use vallheru_domain::economy::market::{self, MarketCategory, PurchaseCheck, SortOrder};

// =========================================================================
// Query / form types
// =========================================================================

/// Query params for market browse pages.
#[derive(serde::Deserialize)]
pub struct BrowseParams {
    pub page: Option<i32>,
    pub sort: Option<String>,
    pub order: Option<String>,
    pub search: Option<String>,
    /// Equipment sub-type filter (W, B, T, R, H, A, S, C, L, E, P).
    #[serde(rename = "type")]
    pub type_filter: Option<String>,
    pub mlevel: Option<i32>,
    pub maxlev: Option<i32>,
}

/// Form data for adding a listing.
#[derive(serde::Deserialize)]
pub struct AddListingForm {
    pub item: Option<String>,
    pub amount: Option<i32>,
    pub cost: Option<i64>,
}

/// Form data for buying from a listing.
#[derive(serde::Deserialize)]
pub struct BuyForm {
    pub amount: Option<i32>,
}

/// Form data for changing a listing's price.
#[derive(serde::Deserialize)]
pub struct ChangePriceForm {
    pub price: Option<i64>,
}

// =========================================================================
// View models
// =========================================================================

/// Market hub page — links to all 8 market categories.
#[derive(serde::Serialize)]
pub struct MarketHubView {
    #[serde(flatten)]
    pub base: crate::render::RenderContext,
    pub categories: Vec<MarketCategoryEntry>,
    pub location: String,
}

/// A single market category link.
#[derive(serde::Serialize)]
pub struct MarketCategoryEntry {
    pub slug: String,
    pub label: String,
}

/// Browse view for commodity markets (minerals, herbs).
#[derive(serde::Serialize)]
pub struct CommodityBrowseView {
    #[serde(flatten)]
    pub base: crate::render::RenderContext,
    pub market_slug: String,
    pub market_name: String,
    pub listings: Vec<CommodityListingEntry>,
    pub page: i32,
    pub total_pages: i32,
    pub sort: String,
    pub order: String,
    pub search: String,
    pub player_id: i32,
}

/// A single commodity listing entry for the browse template.
#[derive(serde::Serialize)]
pub struct CommodityListingEntry {
    pub id: i32,
    pub seller_id: i32,
    pub name: String,
    pub quantity: i32,
    pub unit_cost: i64,
    pub total_cost: i64,
    pub seller_name: String,
}

/// Browse view for equipment-based markets (equipment, jewellery, loot).
#[derive(serde::Serialize)]
pub struct EquipmentBrowseView {
    #[serde(flatten)]
    pub base: crate::render::RenderContext,
    pub market_slug: String,
    pub market_name: String,
    pub listings: Vec<EquipmentListingEntry>,
    pub page: i32,
    pub total_pages: i32,
    pub sort: String,
    pub order: String,
    pub search: String,
    pub player_id: i32,
    pub type_filter: String,
}

/// A single equipment listing entry.
#[derive(serde::Serialize)]
pub struct EquipmentListingEntry {
    pub id: i32,
    pub owner_id: i32,
    pub name: String,
    pub power: i32,
    pub durability: i32,
    pub max_durability: i32,
    pub speed_mod: i32,
    pub agility_mod: i32,
    pub min_level: i32,
    pub amount: i32,
    pub unit_cost: i64,
    pub total_cost: i64,
    pub poison: i32,
    pub twohand: bool,
    pub owner_name: String,
}

/// Browse view for potion market.
#[derive(serde::Serialize)]
pub struct PotionBrowseView {
    #[serde(flatten)]
    pub base: crate::render::RenderContext,
    pub market_slug: String,
    pub market_name: String,
    pub listings: Vec<PotionListingEntry>,
    pub page: i32,
    pub total_pages: i32,
    pub sort: String,
    pub order: String,
    pub search: String,
    pub player_id: i32,
}

/// A single potion listing entry.
#[derive(serde::Serialize)]
pub struct PotionListingEntry {
    pub id: i32,
    pub owner_id: i32,
    pub name: String,
    pub effect: String,
    pub power: i32,
    pub amount: i32,
    pub unit_cost: i64,
    pub total_cost: i64,
    pub owner_name: String,
}

/// Browse view for astral market.
#[derive(serde::Serialize)]
pub struct AstralBrowseView {
    #[serde(flatten)]
    pub base: crate::render::RenderContext,
    pub market_slug: String,
    pub market_name: String,
    pub listings: Vec<AstralListingEntry>,
    pub page: i32,
    pub total_pages: i32,
    pub sort: String,
    pub order: String,
    pub player_id: i32,
}

/// A single astral listing entry.
#[derive(serde::Serialize)]
pub struct AstralListingEntry {
    pub id: i32,
    pub seller_id: i32,
    pub item_type: String,
    pub number: i16,
    pub amount: i32,
    pub unit_cost: i64,
    pub total_cost: i64,
    pub seller_name: String,
}

/// Browse view for pet market.
#[derive(serde::Serialize)]
pub struct PetBrowseView {
    #[serde(flatten)]
    pub base: crate::render::RenderContext,
    pub market_slug: String,
    pub market_name: String,
    pub listings: Vec<PetListingEntry>,
    pub page: i32,
    pub total_pages: i32,
    pub sort: String,
    pub order: String,
    pub player_id: i32,
}

/// A single pet listing entry.
#[derive(serde::Serialize)]
pub struct PetListingEntry {
    pub id: i32,
    pub seller_id: i32,
    pub name: String,
    pub pet_type: String,
    pub power: f64,
    pub defense: f64,
    pub gender: String,
    pub cost: i64,
    pub wins: i32,
    pub losses: i32,
    pub seller_name: String,
}

/// Buy confirmation page.
#[derive(serde::Serialize)]
pub struct BuyView {
    #[serde(flatten)]
    pub base: crate::render::RenderContext,
    pub market_slug: String,
    pub listing_id: i32,
    pub item_name: String,
    pub quantity: i32,
    pub unit_cost: i64,
    pub total_cost: i64,
    pub seller_name: String,
    /// Whether partial quantity buy is supported.
    pub supports_partial: bool,
}

/// My offers page.
#[derive(serde::Serialize)]
pub struct MyOffersView {
    #[serde(flatten)]
    pub base: crate::render::RenderContext,
    pub offer_counts: Vec<OfferCountEntry>,
    pub total: i64,
}

/// Offer count per category.
#[derive(serde::Serialize)]
pub struct OfferCountEntry {
    pub slug: String,
    pub label: String,
    pub count: i64,
}

// =========================================================================
// Market category metadata
// =========================================================================

fn category_label(cat: MarketCategory) -> &'static str {
    match cat {
        MarketCategory::Minerals => "Rynek minerałów",
        MarketCategory::Equipment => "Rynek z przedmiotami",
        MarketCategory::Potions => "Rynek mikstur",
        MarketCategory::Herbs => "Rynek ziół",
        MarketCategory::Astral => "Rynek astralny",
        MarketCategory::Jewellery => "Rynek pierścieni",
        MarketCategory::Loot => "Rynek zdobyczy",
        MarketCategory::Pets => "Rynek zwierzaków",
    }
}

// =========================================================================
// Handlers
// =========================================================================

/// GET /market — market hub page.
#[allow(clippy::cast_possible_truncation)]
pub async fn market_hub(
    State(app): State<AppState>,
    Extension(ctx): Extension<RequestContext>,
) -> Response {
    let Some(ref user) = ctx.session_user else {
        return crate::page::redirect("/login");
    };
    let player = match load_player(&app, user.id as i32).await {
        Ok(p) => p,
        Err(resp) => return resp,
    };

    if player.location != "Altara" && player.location != "Ardulith" {
        return error_page(&app, &ctx, "Musisz być w mieście, aby odwiedzić rynek.");
    }

    let categories: Vec<MarketCategoryEntry> = MarketCategory::ALL
        .iter()
        .map(|c| MarketCategoryEntry {
            slug: c.slug().to_string(),
            label: category_label(*c).to_string(),
        })
        .collect();

    let meta = PageMeta::titled("Rynek");
    let view = MarketHubView {
        base: app.templates.build_context(&ctx, &meta),
        categories,
        location: player.location.clone(),
    };
    app.templates.render_value("market_hub.html", &view)
}

/// GET /market/:slug — browse market listings.
#[allow(clippy::cast_possible_truncation)]
pub async fn market_browse(
    State(app): State<AppState>,
    Extension(ctx): Extension<RequestContext>,
    Path(slug): Path<String>,
    Query(params): Query<BrowseParams>,
) -> Response {
    let Some(ref user) = ctx.session_user else {
        return crate::page::redirect("/login");
    };
    let player_id = user.id as i32;
    let player = match load_player(&app, player_id).await {
        Ok(p) => p,
        Err(resp) => return resp,
    };

    if player.location != "Altara" && player.location != "Ardulith" {
        return error_page(&app, &ctx, "Musisz być w mieście, aby odwiedzić rynek.");
    }

    let Some(category) = MarketCategory::from_slug(&slug) else {
        return error_page(&app, &ctx, "Nieznany typ rynku.");
    };

    let sort_col = category.validate_sort_column(params.sort.as_deref().unwrap_or("id"));
    let sort_order = SortOrder::from_str_param(params.order.as_deref().unwrap_or("DESC"));
    let search_raw = params.search.as_deref().unwrap_or("");
    let search = market::sanitize_search(search_raw);
    let search_opt = if search.is_empty() {
        None
    } else {
        Some(search.as_str())
    };

    match category {
        MarketCategory::Minerals => {
            browse_minerals(
                &app, &ctx, player_id, &params, &sort_col, sort_order, search_opt,
            )
            .await
        }
        MarketCategory::Herbs => {
            browse_herbs(
                &app, &ctx, player_id, &params, &sort_col, sort_order, search_opt,
            )
            .await
        }
        MarketCategory::Equipment => {
            browse_equipment(
                &app, &ctx, player_id, &params, &sort_col, sort_order, search_opt,
            )
            .await
        }
        MarketCategory::Potions => {
            browse_potions(
                &app, &ctx, player_id, &params, &sort_col, sort_order, search_opt,
            )
            .await
        }
        MarketCategory::Astral => {
            browse_astral(&app, &ctx, player_id, &params, &sort_col, sort_order).await
        }
        MarketCategory::Jewellery => {
            browse_jewellery(
                &app, &ctx, player_id, &params, &sort_col, sort_order, search_opt,
            )
            .await
        }
        MarketCategory::Loot => {
            browse_loot(
                &app, &ctx, player_id, &params, &sort_col, sort_order, search_opt,
            )
            .await
        }
        MarketCategory::Pets => {
            browse_pets(&app, &ctx, player_id, &params, &sort_col, sort_order).await
        }
    }
}

/// GET /market/:slug/buy/:id — buy confirmation page.
#[allow(clippy::cast_possible_truncation, clippy::too_many_lines)]
pub async fn market_buy_show(
    State(app): State<AppState>,
    Extension(ctx): Extension<RequestContext>,
    Path((slug, listing_id)): Path<(String, i32)>,
) -> Response {
    let Some(ref user) = ctx.session_user else {
        return crate::page::redirect("/login");
    };
    let player_id = user.id as i32;
    let player = match load_player(&app, player_id).await {
        Ok(p) => p,
        Err(resp) => return resp,
    };

    if player.location != "Altara" && player.location != "Ardulith" {
        return error_page(&app, &ctx, "Musisz być w mieście, aby odwiedzić rynek.");
    }

    let Some(category) = MarketCategory::from_slug(&slug) else {
        return error_page(&app, &ctx, "Nieznany typ rynku.");
    };

    // Fetch listing details based on category
    let (item_name, quantity, unit_cost, seller_name, seller_id) = match category {
        MarketCategory::Minerals => {
            let Some(row) =
                vallheru_data::queries::market::find_mineral_listing(&app.pool, listing_id)
                    .await
                    .unwrap_or(None)
            else {
                return error_page(&app, &ctx, "Oferta nie istnieje.");
            };
            (
                row.nazwa,
                row.ilosc,
                row.cost,
                row.seller_name.unwrap_or_default(),
                row.seller,
            )
        }
        MarketCategory::Herbs => {
            let Some(row) =
                vallheru_data::queries::market::find_herb_listing(&app.pool, listing_id)
                    .await
                    .unwrap_or(None)
            else {
                return error_page(&app, &ctx, "Oferta nie istnieje.");
            };
            (
                row.nazwa,
                row.ilosc,
                row.cost,
                row.seller_name.unwrap_or_default(),
                row.seller,
            )
        }
        MarketCategory::Equipment | MarketCategory::Jewellery | MarketCategory::Loot => {
            let Some(row) =
                vallheru_data::queries::market::find_equipment_listing(&app.pool, listing_id)
                    .await
                    .unwrap_or(None)
            else {
                return error_page(&app, &ctx, "Oferta nie istnieje.");
            };
            (
                row.name,
                row.amount,
                row.cost,
                row.owner_name.unwrap_or_default(),
                row.owner,
            )
        }
        MarketCategory::Potions => {
            let Some(row) =
                vallheru_data::queries::market::find_potion_listing(&app.pool, listing_id)
                    .await
                    .unwrap_or(None)
            else {
                return error_page(&app, &ctx, "Oferta nie istnieje.");
            };
            (
                row.name,
                row.amount,
                row.cost,
                row.owner_name.unwrap_or_default(),
                row.owner,
            )
        }
        MarketCategory::Astral => {
            let Some(row) =
                vallheru_data::queries::market::find_astral_listing(&app.pool, listing_id)
                    .await
                    .unwrap_or(None)
            else {
                return error_page(&app, &ctx, "Oferta nie istnieje.");
            };
            (
                format!("{}{}", row.r#type, row.number),
                row.amount,
                row.cost,
                row.seller_name.unwrap_or_default(),
                row.seller,
            )
        }
        MarketCategory::Pets => {
            let Some(row) = vallheru_data::queries::market::find_pet_listing(&app.pool, listing_id)
                .await
                .unwrap_or(None)
            else {
                return error_page(&app, &ctx, "Oferta nie istnieje.");
            };
            (
                row.name,
                1,
                row.cost,
                row.seller_name.unwrap_or_default(),
                row.seller,
            )
        }
    };

    if seller_id == player_id {
        return error_page(&app, &ctx, "Nie możesz kupić własnej oferty.");
    }

    let meta = PageMeta::titled("Kup na rynku");
    let view = BuyView {
        base: app.templates.build_context(&ctx, &meta),
        market_slug: slug,
        listing_id,
        item_name,
        quantity,
        unit_cost,
        total_cost: unit_cost * i64::from(quantity),
        seller_name,
        supports_partial: category.supports_partial_buy(),
    };
    app.templates.render_value("market_buy.html", &view)
}

/// POST /market/:slug/buy/:id — execute purchase.
#[allow(clippy::cast_possible_truncation, clippy::too_many_lines)]
pub async fn market_buy_execute(
    State(app): State<AppState>,
    Extension(ctx): Extension<RequestContext>,
    Path((slug, listing_id)): Path<(String, i32)>,
    Form(form): Form<BuyForm>,
) -> Response {
    let Some(ref user) = ctx.session_user else {
        return crate::page::redirect("/login");
    };
    let player_id = user.id as i32;
    let player = match load_player(&app, player_id).await {
        Ok(p) => p,
        Err(resp) => return resp,
    };

    if player.location != "Altara" && player.location != "Ardulith" {
        return error_page(&app, &ctx, "Musisz być w mieście, aby odwiedzić rynek.");
    }

    let Some(category) = MarketCategory::from_slug(&slug) else {
        return error_page(&app, &ctx, "Nieznany typ rynku.");
    };

    let buy_quantity = form.amount.unwrap_or(1);

    match category {
        MarketCategory::Minerals => {
            execute_mineral_buy(&app, &ctx, &player, player_id, listing_id, buy_quantity).await
        }
        MarketCategory::Herbs => {
            execute_herb_buy(&app, &ctx, &player, player_id, listing_id, buy_quantity).await
        }
        MarketCategory::Equipment | MarketCategory::Jewellery | MarketCategory::Loot => {
            execute_equipment_buy(
                &app,
                &ctx,
                &player,
                player_id,
                listing_id,
                buy_quantity,
                &slug,
            )
            .await
        }
        MarketCategory::Potions => {
            execute_potion_buy(&app, &ctx, &player, player_id, listing_id, buy_quantity).await
        }
        MarketCategory::Astral => {
            execute_astral_buy(&app, &ctx, &player, player_id, listing_id, buy_quantity).await
        }
        MarketCategory::Pets => execute_pet_buy(&app, &ctx, &player, player_id, listing_id).await,
    }
}

/// GET /market/myoffers — my offers summary.
#[allow(clippy::cast_possible_truncation)]
pub async fn market_my_offers(
    State(app): State<AppState>,
    Extension(ctx): Extension<RequestContext>,
) -> Response {
    let Some(ref user) = ctx.session_user else {
        return crate::page::redirect("/login");
    };
    let player_id = user.id as i32;

    let counts =
        match vallheru_data::queries::market::player_offer_counts(&app.pool, player_id).await {
            Ok(c) => c,
            Err(e) => {
                tracing::error!(error = %e, "player_offer_counts failed");
                return server_error();
            }
        };

    let entries: Vec<OfferCountEntry> = counts
        .into_iter()
        .map(|(slug, count)| {
            let label = MarketCategory::from_slug(&slug)
                .map_or("?", category_label)
                .to_string();
            OfferCountEntry { slug, label, count }
        })
        .collect();

    let total: i64 = entries.iter().map(|e| e.count).sum();

    let meta = PageMeta::titled("Moje oferty");
    let view = MyOffersView {
        base: app.templates.build_context(&ctx, &meta),
        offer_counts: entries,
        total,
    };
    app.templates.render_value("market_my_offers.html", &view)
}

/// POST /market/:slug/cancel/:id — cancel a listing.
#[allow(clippy::cast_possible_truncation)]
pub async fn market_cancel(
    State(app): State<AppState>,
    Extension(ctx): Extension<RequestContext>,
    Path((slug, listing_id)): Path<(String, i32)>,
) -> Response {
    let Some(ref user) = ctx.session_user else {
        return crate::page::redirect("/login");
    };
    let player_id = user.id as i32;

    let Some(category) = MarketCategory::from_slug(&slug) else {
        return error_page(&app, &ctx, "Nieznany typ rynku.");
    };

    let result = match category {
        MarketCategory::Minerals => cancel_mineral(&app, player_id, listing_id).await,
        MarketCategory::Herbs => cancel_herb(&app, player_id, listing_id).await,
        MarketCategory::Equipment | MarketCategory::Jewellery | MarketCategory::Loot => {
            cancel_equipment(&app, player_id, listing_id).await
        }
        MarketCategory::Potions => cancel_potion(&app, player_id, listing_id).await,
        MarketCategory::Astral => cancel_astral(&app, player_id, listing_id).await,
        MarketCategory::Pets => cancel_pet(&app, player_id, listing_id).await,
    };

    match result {
        Ok(()) => flash_and_redirect(
            &app,
            &ctx,
            "Oferta została usunięta z rynku.",
            &format!("/market/{slug}"),
        ),
        Err(msg) => error_page(&app, &ctx, &msg),
    }
}

// =========================================================================
// Browse helpers
// =========================================================================

#[allow(clippy::cast_possible_truncation)]
async fn browse_minerals(
    app: &AppState,
    ctx: &RequestContext,
    player_id: i32,
    params: &BrowseParams,
    sort_col: &market::SortColumn,
    sort_order: SortOrder,
    search: Option<&str>,
) -> Response {
    let total =
        match vallheru_data::queries::market::count_mineral_listings(&app.pool, search).await {
            Ok(c) => c as i32,
            Err(e) => {
                tracing::error!(error = %e, "count_mineral_listings failed");
                return server_error();
            }
        };

    let pag = market::paginate(total, params.page.unwrap_or(1));

    let rows = match vallheru_data::queries::market::browse_mineral_listings(
        &app.pool,
        search,
        sort_col.as_str(),
        sort_order.sql(),
        pag.limit,
        pag.offset,
    )
    .await
    {
        Ok(r) => r,
        Err(e) => {
            tracing::error!(error = %e, "browse_mineral_listings failed");
            return server_error();
        }
    };

    let listings: Vec<CommodityListingEntry> = rows
        .into_iter()
        .map(|r| CommodityListingEntry {
            id: r.id,
            seller_id: r.seller,
            name: r.nazwa,
            quantity: r.ilosc,
            unit_cost: r.cost,
            total_cost: r.cost * i64::from(r.ilosc),
            seller_name: r.seller_name.unwrap_or_default(),
        })
        .collect();

    let meta = PageMeta::titled("Rynek minerałów");
    let view = CommodityBrowseView {
        base: app.templates.build_context(ctx, &meta),
        market_slug: "pmarket".to_string(),
        market_name: "Rynek minerałów".to_string(),
        listings,
        page: pag.page,
        total_pages: pag.total_pages,
        sort: sort_col.as_str().to_string(),
        order: sort_order.sql().to_string(),
        search: params.search.clone().unwrap_or_default(),
        player_id,
    };
    app.templates.render_value("market_commodity.html", &view)
}

#[allow(clippy::cast_possible_truncation)]
async fn browse_herbs(
    app: &AppState,
    ctx: &RequestContext,
    player_id: i32,
    params: &BrowseParams,
    sort_col: &market::SortColumn,
    sort_order: SortOrder,
    search: Option<&str>,
) -> Response {
    let total = match vallheru_data::queries::market::count_herb_listings(&app.pool, search).await {
        Ok(c) => c as i32,
        Err(e) => {
            tracing::error!(error = %e, "count_herb_listings failed");
            return server_error();
        }
    };

    let pag = market::paginate(total, params.page.unwrap_or(1));

    let rows = match vallheru_data::queries::market::browse_herb_listings(
        &app.pool,
        search,
        sort_col.as_str(),
        sort_order.sql(),
        pag.limit,
        pag.offset,
    )
    .await
    {
        Ok(r) => r,
        Err(e) => {
            tracing::error!(error = %e, "browse_herb_listings failed");
            return server_error();
        }
    };

    let listings: Vec<CommodityListingEntry> = rows
        .into_iter()
        .map(|r| CommodityListingEntry {
            id: r.id,
            seller_id: r.seller,
            name: r.nazwa,
            quantity: r.ilosc,
            unit_cost: r.cost,
            total_cost: r.cost * i64::from(r.ilosc),
            seller_name: r.seller_name.unwrap_or_default(),
        })
        .collect();

    let meta = PageMeta::titled("Rynek ziół");
    let view = CommodityBrowseView {
        base: app.templates.build_context(ctx, &meta),
        market_slug: "hmarket".to_string(),
        market_name: "Rynek ziół".to_string(),
        listings,
        page: pag.page,
        total_pages: pag.total_pages,
        sort: sort_col.as_str().to_string(),
        order: sort_order.sql().to_string(),
        search: params.search.clone().unwrap_or_default(),
        player_id,
    };
    app.templates.render_value("market_commodity.html", &view)
}

#[allow(clippy::cast_possible_truncation)]
async fn browse_equipment(
    app: &AppState,
    ctx: &RequestContext,
    player_id: i32,
    params: &BrowseParams,
    sort_col: &market::SortColumn,
    sort_order: SortOrder,
    search: Option<&str>,
) -> Response {
    let type_f = params.type_filter.as_deref();
    let total = match vallheru_data::queries::market::count_equipment_listings(
        &app.pool,
        search,
        type_f,
        params.mlevel,
        params.maxlev,
    )
    .await
    {
        Ok(c) => c as i32,
        Err(e) => {
            tracing::error!(error = %e, "count_equipment_listings failed");
            return server_error();
        }
    };

    let pag = market::paginate(total, params.page.unwrap_or(1));

    let filter = vallheru_data::queries::market::EquipmentBrowseFilter {
        search,
        type_filter: type_f,
        min_level: params.mlevel,
        max_level: params.maxlev,
        sort_col: sort_col.as_str(),
        sort_dir: sort_order.sql(),
        limit: pag.limit,
        offset: pag.offset,
    };
    let rows =
        match vallheru_data::queries::market::browse_equipment_listings(&app.pool, &filter).await {
            Ok(r) => r,
            Err(e) => {
                tracing::error!(error = %e, "browse_equipment_listings failed");
                return server_error();
            }
        };

    let listings: Vec<EquipmentListingEntry> = rows
        .into_iter()
        .map(|r| EquipmentListingEntry {
            id: r.id,
            owner_id: r.owner,
            name: r.name,
            power: r.power,
            durability: r.wt,
            max_durability: r.maxwt,
            speed_mod: r.szyb,
            agility_mod: r.zr,
            min_level: r.minlev,
            amount: r.amount,
            unit_cost: r.cost,
            total_cost: r.cost * i64::from(r.amount),
            poison: r.poison,
            twohand: r.twohand == "Y",
            owner_name: r.owner_name.unwrap_or_default(),
        })
        .collect();

    let meta = PageMeta::titled("Rynek z przedmiotami");
    let view = EquipmentBrowseView {
        base: app.templates.build_context(ctx, &meta),
        market_slug: "imarket".to_string(),
        market_name: "Rynek z przedmiotami".to_string(),
        listings,
        page: pag.page,
        total_pages: pag.total_pages,
        sort: sort_col.as_str().to_string(),
        order: sort_order.sql().to_string(),
        search: params.search.clone().unwrap_or_default(),
        player_id,
        type_filter: params.type_filter.clone().unwrap_or_default(),
    };
    app.templates.render_value("market_equipment.html", &view)
}

#[allow(clippy::cast_possible_truncation)]
async fn browse_potions(
    app: &AppState,
    ctx: &RequestContext,
    player_id: i32,
    params: &BrowseParams,
    sort_col: &market::SortColumn,
    sort_order: SortOrder,
    search: Option<&str>,
) -> Response {
    let total = match vallheru_data::queries::market::count_potion_listings(&app.pool, search).await
    {
        Ok(c) => c as i32,
        Err(e) => {
            tracing::error!(error = %e, "count_potion_listings failed");
            return server_error();
        }
    };

    let pag = market::paginate(total, params.page.unwrap_or(1));

    let rows = match vallheru_data::queries::market::browse_potion_listings(
        &app.pool,
        search,
        sort_col.as_str(),
        sort_order.sql(),
        pag.limit,
        pag.offset,
    )
    .await
    {
        Ok(r) => r,
        Err(e) => {
            tracing::error!(error = %e, "browse_potion_listings failed");
            return server_error();
        }
    };

    let listings: Vec<PotionListingEntry> = rows
        .into_iter()
        .map(|r| PotionListingEntry {
            id: r.id,
            owner_id: r.owner,
            name: r.name,
            effect: r.efect,
            power: r.power,
            amount: r.amount,
            unit_cost: r.cost,
            total_cost: r.cost * i64::from(r.amount),
            owner_name: r.owner_name.unwrap_or_default(),
        })
        .collect();

    let meta = PageMeta::titled("Rynek mikstur");
    let view = PotionBrowseView {
        base: app.templates.build_context(ctx, &meta),
        market_slug: "mmarket".to_string(),
        market_name: "Rynek mikstur".to_string(),
        listings,
        page: pag.page,
        total_pages: pag.total_pages,
        sort: sort_col.as_str().to_string(),
        order: sort_order.sql().to_string(),
        search: params.search.clone().unwrap_or_default(),
        player_id,
    };
    app.templates.render_value("market_potion.html", &view)
}

#[allow(clippy::cast_possible_truncation)]
async fn browse_astral(
    app: &AppState,
    ctx: &RequestContext,
    player_id: i32,
    params: &BrowseParams,
    sort_col: &market::SortColumn,
    sort_order: SortOrder,
) -> Response {
    let total = match vallheru_data::queries::market::count_astral_listings(&app.pool).await {
        Ok(c) => c as i32,
        Err(e) => {
            tracing::error!(error = %e, "count_astral_listings failed");
            return server_error();
        }
    };

    let pag = market::paginate(total, params.page.unwrap_or(1));

    let rows = match vallheru_data::queries::market::browse_astral_listings(
        &app.pool,
        sort_col.as_str(),
        sort_order.sql(),
        pag.limit,
        pag.offset,
    )
    .await
    {
        Ok(r) => r,
        Err(e) => {
            tracing::error!(error = %e, "browse_astral_listings failed");
            return server_error();
        }
    };

    let listings: Vec<AstralListingEntry> = rows
        .into_iter()
        .map(|r| AstralListingEntry {
            id: r.id,
            seller_id: r.seller,
            item_type: r.r#type,
            number: r.number,
            amount: r.amount,
            unit_cost: r.cost,
            total_cost: r.cost * i64::from(r.amount),
            seller_name: r.seller_name.unwrap_or_default(),
        })
        .collect();

    let meta = PageMeta::titled("Rynek astralny");
    let view = AstralBrowseView {
        base: app.templates.build_context(ctx, &meta),
        market_slug: "amarket".to_string(),
        market_name: "Rynek astralny".to_string(),
        listings,
        page: pag.page,
        total_pages: pag.total_pages,
        sort: sort_col.as_str().to_string(),
        order: sort_order.sql().to_string(),
        player_id,
    };
    app.templates.render_value("market_astral.html", &view)
}

#[allow(clippy::cast_possible_truncation)]
async fn browse_jewellery(
    app: &AppState,
    ctx: &RequestContext,
    player_id: i32,
    params: &BrowseParams,
    sort_col: &market::SortColumn,
    sort_order: SortOrder,
    search: Option<&str>,
) -> Response {
    let total =
        match vallheru_data::queries::market::count_jewellery_listings(&app.pool, search).await {
            Ok(c) => c as i32,
            Err(e) => {
                tracing::error!(error = %e, "count_jewellery_listings failed");
                return server_error();
            }
        };

    let pag = market::paginate(total, params.page.unwrap_or(1));

    let rows = match vallheru_data::queries::market::browse_jewellery_listings(
        &app.pool,
        search,
        sort_col.as_str(),
        sort_order.sql(),
        pag.limit,
        pag.offset,
    )
    .await
    {
        Ok(r) => r,
        Err(e) => {
            tracing::error!(error = %e, "browse_jewellery_listings failed");
            return server_error();
        }
    };

    let listings: Vec<EquipmentListingEntry> = rows
        .into_iter()
        .map(|r| EquipmentListingEntry {
            id: r.id,
            owner_id: r.owner,
            name: r.name,
            power: r.power,
            durability: r.wt,
            max_durability: r.maxwt,
            speed_mod: r.szyb,
            agility_mod: r.zr,
            min_level: r.minlev,
            amount: r.amount,
            unit_cost: r.cost,
            total_cost: r.cost * i64::from(r.amount),
            poison: r.poison,
            twohand: r.twohand == "Y",
            owner_name: r.owner_name.unwrap_or_default(),
        })
        .collect();

    let meta = PageMeta::titled("Rynek pierścieni");
    let view = EquipmentBrowseView {
        base: app.templates.build_context(ctx, &meta),
        market_slug: "rmarket".to_string(),
        market_name: "Rynek pierścieni".to_string(),
        listings,
        page: pag.page,
        total_pages: pag.total_pages,
        sort: sort_col.as_str().to_string(),
        order: sort_order.sql().to_string(),
        search: params.search.clone().unwrap_or_default(),
        player_id,
        type_filter: String::new(),
    };
    app.templates.render_value("market_equipment.html", &view)
}

#[allow(clippy::cast_possible_truncation)]
async fn browse_loot(
    app: &AppState,
    ctx: &RequestContext,
    player_id: i32,
    params: &BrowseParams,
    sort_col: &market::SortColumn,
    sort_order: SortOrder,
    search: Option<&str>,
) -> Response {
    let total = match vallheru_data::queries::market::count_loot_listings(&app.pool, search).await {
        Ok(c) => c as i32,
        Err(e) => {
            tracing::error!(error = %e, "count_loot_listings failed");
            return server_error();
        }
    };

    let pag = market::paginate(total, params.page.unwrap_or(1));

    let rows = match vallheru_data::queries::market::browse_loot_listings(
        &app.pool,
        search,
        sort_col.as_str(),
        sort_order.sql(),
        pag.limit,
        pag.offset,
    )
    .await
    {
        Ok(r) => r,
        Err(e) => {
            tracing::error!(error = %e, "browse_loot_listings failed");
            return server_error();
        }
    };

    let listings: Vec<EquipmentListingEntry> = rows
        .into_iter()
        .map(|r| EquipmentListingEntry {
            id: r.id,
            owner_id: r.owner,
            name: r.name,
            power: r.power,
            durability: r.wt,
            max_durability: r.maxwt,
            speed_mod: r.szyb,
            agility_mod: r.zr,
            min_level: r.minlev,
            amount: r.amount,
            unit_cost: r.cost,
            total_cost: r.cost * i64::from(r.amount),
            poison: r.poison,
            twohand: r.twohand == "Y",
            owner_name: r.owner_name.unwrap_or_default(),
        })
        .collect();

    let meta = PageMeta::titled("Rynek zdobyczy");
    let view = EquipmentBrowseView {
        base: app.templates.build_context(ctx, &meta),
        market_slug: "lmarket".to_string(),
        market_name: "Rynek zdobyczy".to_string(),
        listings,
        page: pag.page,
        total_pages: pag.total_pages,
        sort: sort_col.as_str().to_string(),
        order: sort_order.sql().to_string(),
        search: params.search.clone().unwrap_or_default(),
        player_id,
        type_filter: String::new(),
    };
    app.templates.render_value("market_equipment.html", &view)
}

#[allow(clippy::cast_possible_truncation)]
async fn browse_pets(
    app: &AppState,
    ctx: &RequestContext,
    player_id: i32,
    params: &BrowseParams,
    sort_col: &market::SortColumn,
    sort_order: SortOrder,
) -> Response {
    let total = match vallheru_data::queries::market::count_pet_listings(&app.pool).await {
        Ok(c) => c as i32,
        Err(e) => {
            tracing::error!(error = %e, "count_pet_listings failed");
            return server_error();
        }
    };

    let pag = market::paginate(total, params.page.unwrap_or(1));

    let rows = match vallheru_data::queries::market::browse_pet_listings(
        &app.pool,
        sort_col.as_str(),
        sort_order.sql(),
        pag.limit,
        pag.offset,
    )
    .await
    {
        Ok(r) => r,
        Err(e) => {
            tracing::error!(error = %e, "browse_pet_listings failed");
            return server_error();
        }
    };

    let listings: Vec<PetListingEntry> = rows
        .into_iter()
        .map(|r| PetListingEntry {
            id: r.id,
            seller_id: r.seller,
            name: r.name,
            pet_type: r.r#type,
            power: r.power,
            defense: r.defense,
            gender: r.gender,
            cost: r.cost,
            wins: r.wins,
            losses: r.losses,
            seller_name: r.seller_name.unwrap_or_default(),
        })
        .collect();

    let meta = PageMeta::titled("Rynek zwierzaków");
    let view = PetBrowseView {
        base: app.templates.build_context(ctx, &meta),
        market_slug: "cmarket".to_string(),
        market_name: "Rynek zwierzaków".to_string(),
        listings,
        page: pag.page,
        total_pages: pag.total_pages,
        sort: sort_col.as_str().to_string(),
        order: sort_order.sql().to_string(),
        player_id,
    };
    app.templates.render_value("market_pet.html", &view)
}

// =========================================================================
// Buy execution helpers
// =========================================================================

use vallheru_data::queries::player::PlayerRow;

async fn execute_mineral_buy(
    app: &AppState,
    ctx: &RequestContext,
    player: &PlayerRow,
    player_id: i32,
    listing_id: i32,
    buy_quantity: i32,
) -> Response {
    let Some(listing) = vallheru_data::queries::market::find_mineral_listing(&app.pool, listing_id)
        .await
        .unwrap_or(None)
    else {
        return error_page(app, ctx, "Oferta nie istnieje.");
    };

    let check = PurchaseCheck {
        buyer_location: &player.location,
        buyer_id: player_id,
        buyer_credits: i64::from(player.credits),
        seller_id: listing.seller,
        seller_bank: i64::from(player.bank), // seller bank loaded later
        unit_cost: listing.cost,
        listing_quantity: listing.ilosc,
        buy_quantity,
    };

    let result = match market::validate_purchase(&check) {
        Ok(r) => r,
        Err(e) => return error_page(app, ctx, &e.to_string()),
    };

    // Execute purchase in a single transaction
    let log_msg = format!(
        "{} kupił(a) {} × {} za {} sztuk złota na rynku minerałów.",
        player.username, buy_quantity, listing.nazwa, result.total_price
    );
    let purchase = vallheru_data::queries::market::QuantityPurchase {
        buyer_id: player_id,
        seller_id: listing.seller,
        listing_id,
        buy_quantity,
        remaining: result.listing_remaining,
        total_price: result.total_price,
        log_msg: &log_msg,
    };
    if let Err(e) = vallheru_data::queries::market::purchase_mineral(&app.pool, &purchase).await {
        tracing::error!(error = %e, "purchase_mineral failed");
        return server_error();
    }

    flash_and_redirect(
        app,
        ctx,
        &format!(
            "Kupiono <b>{}</b> × <b>{}</b> za <b>{}</b> sztuk złota.",
            buy_quantity, listing.nazwa, result.total_price
        ),
        "/market/pmarket",
    )
}

async fn execute_herb_buy(
    app: &AppState,
    ctx: &RequestContext,
    player: &PlayerRow,
    player_id: i32,
    listing_id: i32,
    buy_quantity: i32,
) -> Response {
    let Some(listing) = vallheru_data::queries::market::find_herb_listing(&app.pool, listing_id)
        .await
        .unwrap_or(None)
    else {
        return error_page(app, ctx, "Oferta nie istnieje.");
    };

    let check = PurchaseCheck {
        buyer_location: &player.location,
        buyer_id: player_id,
        buyer_credits: i64::from(player.credits),
        seller_id: listing.seller,
        seller_bank: 0,
        unit_cost: listing.cost,
        listing_quantity: listing.ilosc,
        buy_quantity,
    };

    let result = match market::validate_purchase(&check) {
        Ok(r) => r,
        Err(e) => return error_page(app, ctx, &e.to_string()),
    };

    let log_msg = format!(
        "{} kupił(a) {} × {} za {} sztuk złota na rynku ziół.",
        player.username, buy_quantity, listing.nazwa, result.total_price
    );
    let purchase = vallheru_data::queries::market::QuantityPurchase {
        buyer_id: player_id,
        seller_id: listing.seller,
        listing_id,
        buy_quantity,
        remaining: result.listing_remaining,
        total_price: result.total_price,
        log_msg: &log_msg,
    };
    if let Err(e) = vallheru_data::queries::market::purchase_herb(&app.pool, &purchase).await {
        tracing::error!(error = %e, "purchase_herb failed");
        return server_error();
    }

    flash_and_redirect(
        app,
        ctx,
        &format!(
            "Kupiono <b>{}</b> × <b>{}</b> za <b>{}</b> sztuk złota.",
            buy_quantity, listing.nazwa, result.total_price
        ),
        "/market/hmarket",
    )
}

async fn execute_equipment_buy(
    app: &AppState,
    ctx: &RequestContext,
    player: &PlayerRow,
    player_id: i32,
    listing_id: i32,
    buy_quantity: i32,
    slug: &str,
) -> Response {
    let Some(listing) =
        vallheru_data::queries::market::find_equipment_listing(&app.pool, listing_id)
            .await
            .unwrap_or(None)
    else {
        return error_page(app, ctx, "Oferta nie istnieje.");
    };

    let check = PurchaseCheck {
        buyer_location: &player.location,
        buyer_id: player_id,
        buyer_credits: i64::from(player.credits),
        seller_id: listing.owner,
        seller_bank: 0,
        unit_cost: listing.cost,
        listing_quantity: listing.amount,
        buy_quantity,
    };

    let result = match market::validate_purchase(&check) {
        Ok(r) => r,
        Err(e) => return error_page(app, ctx, &e.to_string()),
    };

    let log_msg = format!(
        "{} kupił(a) {} za {} sztuk złota na rynku.",
        player.username, listing.name, result.total_price
    );
    if let Err(e) = vallheru_data::queries::market::purchase_equipment(
        &app.pool,
        player_id,
        listing.owner,
        listing_id,
        result.total_price,
        &log_msg,
    )
    .await
    {
        tracing::error!(error = %e, "purchase_equipment failed");
        return server_error();
    }

    flash_and_redirect(
        app,
        ctx,
        &format!(
            "Kupiono <b>{}</b> za <b>{}</b> sztuk złota.",
            listing.name, result.total_price
        ),
        &format!("/market/{slug}"),
    )
}

async fn execute_potion_buy(
    app: &AppState,
    ctx: &RequestContext,
    player: &PlayerRow,
    player_id: i32,
    listing_id: i32,
    buy_quantity: i32,
) -> Response {
    let Some(listing) = vallheru_data::queries::market::find_potion_listing(&app.pool, listing_id)
        .await
        .unwrap_or(None)
    else {
        return error_page(app, ctx, "Oferta nie istnieje.");
    };

    let check = PurchaseCheck {
        buyer_location: &player.location,
        buyer_id: player_id,
        buyer_credits: i64::from(player.credits),
        seller_id: listing.owner,
        seller_bank: 0,
        unit_cost: listing.cost,
        listing_quantity: listing.amount,
        buy_quantity,
    };

    let result = match market::validate_purchase(&check) {
        Ok(r) => r,
        Err(e) => return error_page(app, ctx, &e.to_string()),
    };

    let log_msg = format!(
        "{} kupił(a) {} za {} sztuk złota na rynku mikstur.",
        player.username, listing.name, result.total_price
    );
    if let Err(e) = vallheru_data::queries::market::purchase_potion(
        &app.pool,
        player_id,
        listing.owner,
        listing_id,
        result.total_price,
        &log_msg,
    )
    .await
    {
        tracing::error!(error = %e, "purchase_potion failed");
        return server_error();
    }

    flash_and_redirect(
        app,
        ctx,
        &format!(
            "Kupiono <b>{}</b> za <b>{}</b> sztuk złota.",
            listing.name, result.total_price
        ),
        "/market/mmarket",
    )
}

async fn execute_astral_buy(
    app: &AppState,
    ctx: &RequestContext,
    player: &PlayerRow,
    player_id: i32,
    listing_id: i32,
    buy_quantity: i32,
) -> Response {
    let Some(listing) = vallheru_data::queries::market::find_astral_listing(&app.pool, listing_id)
        .await
        .unwrap_or(None)
    else {
        return error_page(app, ctx, "Oferta nie istnieje.");
    };

    let check = PurchaseCheck {
        buyer_location: &player.location,
        buyer_id: player_id,
        buyer_credits: i64::from(player.credits),
        seller_id: listing.seller,
        seller_bank: 0,
        unit_cost: listing.cost,
        listing_quantity: listing.amount,
        buy_quantity,
    };

    let result = match market::validate_purchase(&check) {
        Ok(r) => r,
        Err(e) => return error_page(app, ctx, &e.to_string()),
    };

    let item_desc = format!("{}{}", listing.r#type, listing.number);
    let log_msg = format!(
        "{} kupił(a) {} × {} za {} sztuk złota na rynku astralnym.",
        player.username, buy_quantity, item_desc, result.total_price
    );
    let purchase = vallheru_data::queries::market::QuantityPurchase {
        buyer_id: player_id,
        seller_id: listing.seller,
        listing_id,
        buy_quantity,
        remaining: result.listing_remaining,
        total_price: result.total_price,
        log_msg: &log_msg,
    };
    if let Err(e) = vallheru_data::queries::market::purchase_astral(&app.pool, &purchase).await {
        tracing::error!(error = %e, "purchase_astral failed");
        return server_error();
    }

    flash_and_redirect(
        app,
        ctx,
        &format!(
            "Kupiono <b>{}</b> × <b>{}</b> za <b>{}</b> sztuk złota.",
            buy_quantity, item_desc, result.total_price
        ),
        "/market/amarket",
    )
}

async fn execute_pet_buy(
    app: &AppState,
    ctx: &RequestContext,
    player: &PlayerRow,
    player_id: i32,
    listing_id: i32,
) -> Response {
    let Some(listing) = vallheru_data::queries::market::find_pet_listing(&app.pool, listing_id)
        .await
        .unwrap_or(None)
    else {
        return error_page(app, ctx, "Oferta nie istnieje.");
    };

    let check = PurchaseCheck {
        buyer_location: &player.location,
        buyer_id: player_id,
        buyer_credits: i64::from(player.credits),
        seller_id: listing.seller,
        seller_bank: 0,
        unit_cost: listing.cost,
        listing_quantity: 1,
        buy_quantity: 1,
    };

    let result = match market::validate_purchase(&check) {
        Ok(r) => r,
        Err(e) => return error_page(app, ctx, &e.to_string()),
    };

    let log_msg = format!(
        "{} kupił(a) {} za {} sztuk złota na rynku zwierzaków.",
        player.username, listing.name, result.total_price
    );
    if let Err(e) = vallheru_data::queries::market::purchase_pet(
        &app.pool,
        player_id,
        listing.seller,
        listing_id,
        result.total_price,
        &log_msg,
    )
    .await
    {
        tracing::error!(error = %e, "purchase_pet failed");
        return server_error();
    }

    flash_and_redirect(
        app,
        ctx,
        &format!(
            "Kupiono <b>{}</b> za <b>{}</b> sztuk złota.",
            listing.name, result.total_price
        ),
        "/market/cmarket",
    )
}

// =========================================================================
// Cancel helpers
// =========================================================================

async fn cancel_mineral(app: &AppState, player_id: i32, listing_id: i32) -> Result<(), String> {
    let listing = vallheru_data::queries::market::find_mineral_listing(&app.pool, listing_id)
        .await
        .map_err(|e| format!("Błąd bazy danych: {e}"))?
        .ok_or("Oferta nie istnieje.")?;

    if listing.seller != player_id {
        return Err("To nie jest twoja oferta.".to_string());
    }

    vallheru_data::queries::market::delete_mineral_listing(&app.pool, listing_id)
        .await
        .map_err(|e| format!("Błąd bazy danych: {e}"))
}

async fn cancel_herb(app: &AppState, player_id: i32, listing_id: i32) -> Result<(), String> {
    let listing = vallheru_data::queries::market::find_herb_listing(&app.pool, listing_id)
        .await
        .map_err(|e| format!("Błąd bazy danych: {e}"))?
        .ok_or("Oferta nie istnieje.")?;

    if listing.seller != player_id {
        return Err("To nie jest twoja oferta.".to_string());
    }

    vallheru_data::queries::market::delete_herb_listing(&app.pool, listing_id)
        .await
        .map_err(|e| format!("Błąd bazy danych: {e}"))
}

async fn cancel_equipment(app: &AppState, player_id: i32, listing_id: i32) -> Result<(), String> {
    let listing = vallheru_data::queries::market::find_equipment_listing(&app.pool, listing_id)
        .await
        .map_err(|e| format!("Błąd bazy danych: {e}"))?
        .ok_or("Oferta nie istnieje.")?;

    if listing.owner != player_id {
        return Err("To nie jest twoja oferta.".to_string());
    }

    vallheru_data::queries::market::unlist_equipment_from_market(&app.pool, listing_id)
        .await
        .map_err(|e| format!("Błąd bazy danych: {e}"))
}

async fn cancel_potion(app: &AppState, player_id: i32, listing_id: i32) -> Result<(), String> {
    let listing = vallheru_data::queries::market::find_potion_listing(&app.pool, listing_id)
        .await
        .map_err(|e| format!("Błąd bazy danych: {e}"))?
        .ok_or("Oferta nie istnieje.")?;

    if listing.owner != player_id {
        return Err("To nie jest twoja oferta.".to_string());
    }

    vallheru_data::queries::market::unlist_potion_from_market(&app.pool, listing_id)
        .await
        .map_err(|e| format!("Błąd bazy danych: {e}"))
}

async fn cancel_astral(app: &AppState, player_id: i32, listing_id: i32) -> Result<(), String> {
    let listing = vallheru_data::queries::market::find_astral_listing(&app.pool, listing_id)
        .await
        .map_err(|e| format!("Błąd bazy danych: {e}"))?
        .ok_or("Oferta nie istnieje.")?;

    if listing.seller != player_id {
        return Err("To nie jest twoja oferta.".to_string());
    }

    vallheru_data::queries::market::delete_astral_listing(&app.pool, listing_id)
        .await
        .map_err(|e| format!("Błąd bazy danych: {e}"))
}

async fn cancel_pet(app: &AppState, player_id: i32, listing_id: i32) -> Result<(), String> {
    let listing = vallheru_data::queries::market::find_pet_listing(&app.pool, listing_id)
        .await
        .map_err(|e| format!("Błąd bazy danych: {e}"))?
        .ok_or("Oferta nie istnieje.")?;

    if listing.seller != player_id {
        return Err("To nie jest twoja oferta.".to_string());
    }

    vallheru_data::queries::market::delete_pet_listing(&app.pool, listing_id)
        .await
        .map_err(|e| format!("Błąd bazy danych: {e}"))
}

// =========================================================================
// Shared helpers
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
    _state: &AppState,
    _ctx: &RequestContext,
    _message: &str,
    path: &str,
) -> Response {
    crate::page::redirect_after_post(path)
}

fn server_error() -> Response {
    (
        axum::http::StatusCode::INTERNAL_SERVER_ERROR,
        "Internal error",
    )
        .into_response()
}
