//! Royal Warehouse handler — buy and sell minerals and herbs.
//!
//! Ported from `warehouse.php`. Players in Altara or Ardulith can sell
//! minerals/herbs to the kingdom at base price, or buy stock back at 2×
//! the base price. Prices come from the `settings` table, stock from
//! the `warehouse` table, and kingdom gold from `settings.gold`.

use axum::{Extension, Form, extract::State, response::IntoResponse, response::Response};

use crate::middleware::context::RequestContext;
use crate::page::{Flash, FlashKind, PageMeta};
use crate::state::AppState;
use vallheru_data::queries::{gathering as gq, settings as sq};

// =========================================================================
// Item catalogue
// =========================================================================

/// All 26 tradeable commodities in PHP order.
/// Indices 0–16 = minerals, 17 = mithril (platinum on player), 18–25 = herbs.
const ITEMS: &[&str] = &[
    "copperore",
    "zincore",
    "tinore",
    "ironore",
    "copper",
    "bronze",
    "brass",
    "iron",
    "steel",
    "coal",
    "adamantium",
    "meteor",
    "crystal",
    "pine",
    "hazel",
    "yew",
    "elm",
    "mithril",
    "illani",
    "illanias",
    "nutari",
    "dynallca",
    "illani_seeds",
    "illanias_seeds",
    "nutari_seeds",
    "dynallca_seeds",
];

/// Display names (genitive, for "X units of …").
const ITEM_NAMES: &[&str] = &[
    "rudy miedzi",
    "rudy cynku",
    "rudy cyny",
    "rudy żelaza",
    "sztabek miedzi",
    "sztabek brązu",
    "sztabek mosiądzu",
    "sztabek żelaza",
    "sztabek stali",
    "brył węgla",
    "brył adamantium",
    "kawałków meteorytu",
    "kryształów",
    "drewna sosnowego",
    "drewna z leszczyny",
    "drewna cisowego",
    "drewna z wiązu",
    "mithrilu",
    "illani",
    "illanias",
    "nutari",
    "dynallca",
    "nasion illani",
    "nasion illanias",
    "nasion nutari",
    "nasion dynallca",
];

/// Allowlist of mineral column names for dynamic SQL safety.
const ALLOWED_MINERALS: &[&str] = &[
    "copperore",
    "zincore",
    "tinore",
    "ironore",
    "copper",
    "bronze",
    "brass",
    "iron",
    "steel",
    "coal",
    "adamantium",
    "meteor",
    "crystal",
    "pine",
    "hazel",
    "yew",
    "elm",
];

/// Allowlist of herb column names for dynamic SQL safety.
const ALLOWED_HERBS: &[&str] = &[
    "illani",
    "illanias",
    "nutari",
    "dynallca",
    "ilani_seeds",
    "illanias_seeds",
    "nutari_seeds",
    "dynallca_seeds",
];

/// Map a warehouse item name to its DB herb column (handles the `illani_seeds`
/// vs `ilani_seeds` typo in the original DB schema).
fn herb_db_column(item_key: &str) -> &str {
    if item_key == "illani_seeds" {
        "ilani_seeds"
    } else {
        item_key
    }
}

/// Read the player's owned quantity of a single commodity.
fn player_amount_mineral(minerals: &gq::MineralsRow, idx: usize) -> i32 {
    match idx {
        0 => minerals.copperore,
        1 => minerals.zincore,
        2 => minerals.tinore,
        3 => minerals.ironore,
        4 => minerals.copper,
        5 => minerals.bronze,
        6 => minerals.brass,
        7 => minerals.iron,
        8 => minerals.steel,
        9 => minerals.coal,
        10 => minerals.adamantium,
        11 => minerals.meteor,
        12 => minerals.crystal,
        13 => minerals.pine,
        14 => minerals.hazel,
        15 => minerals.yew,
        16 => minerals.elm,
        _ => 0,
    }
}

fn player_amount_herb(herbs: &gq::HerbsRow, idx: usize) -> i32 {
    match idx {
        18 => herbs.illani,
        19 => herbs.illanias,
        20 => herbs.nutari,
        21 => herbs.dynallca,
        22 => herbs.ilani_seeds,
        23 => herbs.illanias_seeds,
        24 => herbs.nutari_seeds,
        25 => herbs.dynallca_seeds,
        _ => 0,
    }
}

// =========================================================================
// View models
// =========================================================================

#[derive(serde::Serialize)]
pub struct WarehouseView {
    #[serde(flatten)]
    pub base: crate::render::RenderContext,
    pub minerals: Vec<CommodityEntry>,
    pub herbs: Vec<CommodityEntry>,
    pub kingdom_gold: i64,
    pub warehouse_info: String,
    pub caravan: bool,
}

#[derive(serde::Serialize)]
pub struct CommodityEntry {
    /// Display name (nominative).
    pub label: String,
    /// Item index used in sell/buy URL.
    pub idx: usize,
    /// Current buy price (what kingdom pays the player).
    pub buy_price: i64,
    /// Current sell price (what player pays the kingdom) = 2× `buy_price`.
    pub sell_price: i64,
    /// Amount currently in warehouse stock.
    pub stock: i64,
}

#[derive(serde::Serialize)]
pub struct TradeFormView {
    #[serde(flatten)]
    pub base: crate::render::RenderContext,
    pub item_name: String,
    pub item_idx: usize,
    pub price: i64,
    pub available: i64,
    pub action: String,
    pub kingdom_gold: i64,
}

#[derive(serde::Deserialize)]
pub struct TradeQuery {
    pub item: Option<usize>,
}

#[derive(serde::Deserialize)]
pub struct TradeForm {
    pub amount: Option<i64>,
}

// Nominative item labels for the main table.
const MINERAL_LABELS: &[&str] = &[
    "Ruda miedzi",
    "Ruda cynku",
    "Ruda cyny",
    "Ruda żelaza",
    "Sztabki miedzi",
    "Sztabki brązu",
    "Sztabki mosiądzu",
    "Sztabki żelaza",
    "Sztabki stali",
    "Bryły węgla",
    "Bryły adamantium",
    "Kawałki meteorytu",
    "Kryształy",
    "Drewno sosnowe",
    "Drewno z leszczyny",
    "Drewno cisowe",
    "Drewno z wiązu",
    "Mithril",
];

const HERB_LABELS: &[&str] = &[
    "Illani",
    "Illanias",
    "Nutari",
    "Dynallca",
    "Nasiona Illani",
    "Nasiona Illanias",
    "Nasiona Nutari",
    "Nasiona Dynallca",
];

// =========================================================================
// GET /warehouse — main listing
// =========================================================================

pub async fn warehouse_show(
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

    if !is_in_city(&player.location) {
        return error_page(&app, &ctx, "Nie znajdujesz się w mieście.");
    }

    // Load prices, stock, kingdom gold, and caravan status.
    let item_refs = ITEMS.to_vec();
    let (prices_res, stock_res, gold_res, caravan_res) = tokio::join!(
        sq::get_settings_batch(&app.pool, &item_refs),
        gq::load_warehouse_stock(&app.pool, &item_refs),
        sq::get_setting(&app.pool, "gold"),
        sq::get_setting(&app.pool, "caravan"),
    );

    let prices = match prices_res {
        Ok(v) => v,
        Err(e) => {
            tracing::error!(error = %e, "warehouse: load prices");
            return server_error();
        }
    };
    let stock = match stock_res {
        Ok(v) => v,
        Err(e) => {
            tracing::error!(error = %e, "warehouse: load stock");
            return server_error();
        }
    };

    let kingdom_gold = parse_gold_setting(gold_res);
    let caravan = parse_caravan_setting(caravan_res);

    let price_map = build_price_map(&prices);
    let stock_map = build_stock_map(&stock);

    // Build mineral entries (indices 0..18).
    let minerals: Vec<CommodityEntry> = (0..18)
        .map(|i| {
            let buy_price = price_map.get(ITEMS[i]).copied().unwrap_or(0);
            CommodityEntry {
                label: MINERAL_LABELS[i].to_owned(),
                idx: i,
                buy_price,
                sell_price: buy_price * 2,
                stock: stock_map.get(ITEMS[i]).copied().unwrap_or(0),
            }
        })
        .collect();

    // Build herb entries (indices 18..26).
    let herbs: Vec<CommodityEntry> = (18..26)
        .map(|i| {
            let buy_price = price_map.get(ITEMS[i]).copied().unwrap_or(0);
            CommodityEntry {
                label: HERB_LABELS[i - 18].to_owned(),
                idx: i,
                buy_price,
                sell_price: buy_price * 2,
                stock: stock_map.get(ITEMS[i]).copied().unwrap_or(0),
            }
        })
        .collect();

    let warehouse_info = if player.location == "Ardulith" {
        "Schodzisz krętymi schodami w dół. Czujesz lekki swąd palących się świec, \
         a oczy zaczynają ci łzawić. Schody prowadzą do jakiegoś pomieszczenia... \
         Po chwili twoje oczy przyzwyczajają się do półmroku. Widzisz ogromną podziemną \
         halę wypełnioną stertami wszelkiego rodzaju rud, liczne regały z miksturami \
         oraz wielkie wiklinowe kosze wypełnione po brzegi ziołami."
    } else {
        "Wchodzisz do Magazynu Królewskiego. Jesteś pod wrażeniem widząc wnętrze tej \
         ogromnej budowli. Oto ceny obowiązujące dzisiaj."
    };

    let meta = PageMeta::titled("Magazyn Królewski")
        .with_back_link("/city", "Wróć do miasta")
        .with_js("warehouse.js");
    let base = app.templates.build_context(&ctx, &meta);

    let view = WarehouseView {
        base,
        minerals,
        herbs,
        kingdom_gold,
        warehouse_info: format!(
            "{warehouse_info} Obecnie dysponujemy {kingdom_gold} sztukami złota."
        ),
        caravan,
    };

    app.templates.render_value("warehouse.html", &view)
}

// =========================================================================
// GET /warehouse/sell?item=N — sell form
// =========================================================================

pub async fn sell_form(
    State(app): State<AppState>,
    Extension(ctx): Extension<RequestContext>,
    axum::extract::Query(q): axum::extract::Query<TradeQuery>,
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

    if !is_in_city(&player.location) {
        return error_page(&app, &ctx, "Nie znajdujesz się w mieście.");
    }

    let idx = match q.item {
        Some(i) if i < 26 => i,
        _ => return error_page(&app, &ctx, "Nieprawidłowy przedmiot."),
    };

    // Determine how much the player has.
    let available = player_owned_amount(&app, player_id, idx, player.platinum).await;

    // Fetch the buy price from settings.
    let price = load_item_price(&app, idx).await;

    let kingdom_gold = load_kingdom_gold(&app).await;

    let meta = PageMeta::titled("Magazyn Królewski — Sprzedaj")
        .with_back_link("/warehouse", "Wróć")
        .with_js("warehouse.js");
    let base = app.templates.build_context(&ctx, &meta);

    let view = TradeFormView {
        base,
        item_name: ITEM_NAMES[idx].to_owned(),
        item_idx: idx,
        price,
        available,
        action: "sell".to_owned(),
        kingdom_gold,
    };

    app.templates.render_value("warehouse.html", &view)
}

// =========================================================================
// POST /warehouse/sell?item=N — execute sell
// =========================================================================

pub async fn sell_action(
    State(app): State<AppState>,
    Extension(ctx): Extension<RequestContext>,
    axum::extract::Query(q): axum::extract::Query<TradeQuery>,
    Form(form): Form<TradeForm>,
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

    if !is_in_city(&player.location) {
        return error_page(&app, &ctx, "Nie znajdujesz się w mieście.");
    }

    let idx = match q.item {
        Some(i) if i < 26 => i,
        _ => return error_page(&app, &ctx, "Nieprawidłowy przedmiot."),
    };

    let amount = match form.amount {
        Some(a) if a > 0 => a,
        _ => return error_page(&app, &ctx, "Podaj prawidłową ilość."),
    };

    let available = player_owned_amount(&app, player_id, idx, player.platinum).await;
    if amount > available {
        return error_page(
            &app,
            &ctx,
            &format!("Nie masz tyle sztuk {}.", ITEM_NAMES[idx]),
        );
    }

    let unit_price = load_item_price(&app, idx).await;
    let total_gold = unit_price * amount;

    // Check kingdom gold.
    let kingdom_gold = load_kingdom_gold(&app).await;
    if total_gold > kingdom_gold {
        return error_page(
            &app,
            &ctx,
            "Nie posiadamy aż tyle złota aby móc kupić tyle surowców.",
        );
    }

    // Execute the trade in a transaction.
    let mut tx = match app.pool.begin().await {
        Ok(tx) => tx,
        Err(e) => {
            tracing::error!(error = %e, "warehouse sell: begin tx");
            return server_error();
        }
    };

    if let Err(e) = execute_sell_tx(&mut tx, player_id, idx, amount, total_gold).await {
        tracing::error!(error = %e, "warehouse sell: tx failed");
        return server_error();
    }

    if let Err(e) = tx.commit().await {
        tracing::error!(error = %e, "warehouse sell: commit");
        return server_error();
    }

    let meta = PageMeta::titled("Magazyn Królewski")
        .with_back_link("/warehouse", "Wróć do magazynu")
        .with_flash(Flash::success(format!(
            "Sprzedałeś {amount} sztuk {} za {total_gold} sztuk złota.",
            ITEM_NAMES[idx]
        )));
    let base = app.templates.build_context(&ctx, &meta);
    app.templates.render("success.html", &base)
}

// =========================================================================
// GET /warehouse/buy?item=N — buy form
// =========================================================================

pub async fn buy_form(
    State(app): State<AppState>,
    Extension(ctx): Extension<RequestContext>,
    axum::extract::Query(q): axum::extract::Query<TradeQuery>,
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

    if !is_in_city(&player.location) {
        return error_page(&app, &ctx, "Nie znajdujesz się w mieście.");
    }

    let idx = match q.item {
        Some(i) if i < 26 => i,
        _ => return error_page(&app, &ctx, "Nieprawidłowy przedmiot."),
    };

    // Fetch warehouse stock for this item.
    let stock = match gq::get_warehouse_item(&app.pool, ITEMS[idx]).await {
        Ok(Some(row)) => row.amount,
        Ok(None) => 0,
        Err(e) => {
            tracing::error!(error = %e, "warehouse buy: load stock");
            return server_error();
        }
    };

    // Buy price = 2× base price.
    let price = load_item_price(&app, idx).await * 2;

    let kingdom_gold = load_kingdom_gold(&app).await;

    let meta = PageMeta::titled("Magazyn Królewski — Kup")
        .with_back_link("/warehouse", "Wróć")
        .with_js("warehouse.js");
    let base = app.templates.build_context(&ctx, &meta);

    let view = TradeFormView {
        base,
        item_name: ITEM_NAMES[idx].to_owned(),
        item_idx: idx,
        price,
        available: stock,
        action: "buy".to_owned(),
        kingdom_gold,
    };

    app.templates.render_value("warehouse.html", &view)
}

// =========================================================================
// POST /warehouse/buy?item=N — execute buy
// =========================================================================

pub async fn buy_action(
    State(app): State<AppState>,
    Extension(ctx): Extension<RequestContext>,
    axum::extract::Query(q): axum::extract::Query<TradeQuery>,
    Form(form): Form<TradeForm>,
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

    if !is_in_city(&player.location) {
        return error_page(&app, &ctx, "Nie znajdujesz się w mieście.");
    }

    let idx = match q.item {
        Some(i) if i < 26 => i,
        _ => return error_page(&app, &ctx, "Nieprawidłowy przedmiot."),
    };

    let amount = match form.amount {
        Some(a) if a > 0 => a,
        _ => return error_page(&app, &ctx, "Podaj prawidłową ilość."),
    };

    // Verify stock.
    let stock = match gq::get_warehouse_item(&app.pool, ITEMS[idx]).await {
        Ok(Some(row)) => row.amount,
        Ok(None) => 0,
        Err(e) => {
            tracing::error!(error = %e, "warehouse buy: load stock");
            return server_error();
        }
    };
    if amount > stock {
        return error_page(
            &app,
            &ctx,
            &format!("Nie ma tyle sztuk {} w magazynie.", ITEM_NAMES[idx]),
        );
    }

    // Buy price = 2× base price.
    let unit_price = load_item_price(&app, idx).await * 2;
    let total_gold = unit_price * amount;

    if total_gold > i64::from(player.credits) {
        return error_page(&app, &ctx, "Nie masz tylu sztuk złota przy sobie.");
    }

    // Execute the trade in a transaction.
    let mut tx = match app.pool.begin().await {
        Ok(tx) => tx,
        Err(e) => {
            tracing::error!(error = %e, "warehouse buy: begin tx");
            return server_error();
        }
    };

    if let Err(e) = execute_buy_tx(&mut tx, player_id, idx, amount, total_gold).await {
        tracing::error!(error = %e, "warehouse buy: tx failed");
        return server_error();
    }

    if let Err(e) = tx.commit().await {
        tracing::error!(error = %e, "warehouse buy: commit");
        return server_error();
    }

    let meta = PageMeta::titled("Magazyn Królewski")
        .with_back_link("/warehouse", "Wróć do magazynu")
        .with_flash(Flash::success(format!(
            "Kupiłeś {amount} sztuk {} za {total_gold} sztuk złota.",
            ITEM_NAMES[idx]
        )));
    let base = app.templates.build_context(&ctx, &meta);
    app.templates.render("success.html", &base)
}

// =========================================================================
// Transaction helpers
// =========================================================================

/// Execute all DB mutations for a sell action inside an existing transaction.
async fn execute_sell_tx(
    tx: &mut sqlx::Transaction<'_, sqlx::Postgres>,
    player_id: i32,
    idx: usize,
    amount: i64,
    total_gold: i64,
) -> Result<(), sqlx::Error> {
    let conn = tx.as_mut();

    // 1. Credit player gold.
    sqlx::query("UPDATE players SET credits = credits + $1 WHERE id = $2")
        .bind(i32::try_from(total_gold).unwrap_or(i32::MAX))
        .bind(player_id)
        .execute(&mut *conn)
        .await?;

    // 2. Deduct kingdom gold.
    sqlx::query(
        "UPDATE settings SET value = (CAST(value AS BIGINT) - $1)::TEXT WHERE setting = 'gold'",
    )
    .bind(total_gold)
    .execute(&mut *conn)
    .await?;

    // 3. Record warehouse stock increase.
    sqlx::query(
        "UPDATE warehouse SET sell = sell + $1, amount = amount + $1 \
         WHERE reset = 1 AND mineral = $2",
    )
    .bind(amount)
    .bind(ITEMS[idx])
    .execute(&mut *conn)
    .await?;

    // 4. Deduct from player inventory.
    deduct_player_inventory(&mut *conn, player_id, idx, amount).await
}

/// Execute all DB mutations for a buy action inside an existing transaction.
async fn execute_buy_tx(
    tx: &mut sqlx::Transaction<'_, sqlx::Postgres>,
    player_id: i32,
    idx: usize,
    amount: i64,
    total_gold: i64,
) -> Result<(), sqlx::Error> {
    let conn = tx.as_mut();

    // 1. Deduct player gold.
    sqlx::query("UPDATE players SET credits = credits - $1 WHERE id = $2")
        .bind(i32::try_from(total_gold).unwrap_or(i32::MAX))
        .bind(player_id)
        .execute(&mut *conn)
        .await?;

    // 2. Credit kingdom gold.
    sqlx::query(
        "UPDATE settings SET value = (CAST(value AS BIGINT) + $1)::TEXT WHERE setting = 'gold'",
    )
    .bind(total_gold)
    .execute(&mut *conn)
    .await?;

    // 3. Record warehouse stock decrease.
    sqlx::query(
        "UPDATE warehouse SET buy = buy + $1, amount = amount - $1 \
         WHERE reset = 1 AND mineral = $2",
    )
    .bind(amount)
    .bind(ITEMS[idx])
    .execute(&mut *conn)
    .await?;

    // 4. Add to player inventory.
    add_player_inventory(&mut *conn, player_id, idx, amount).await
}

/// Deduct items from the player's minerals, platinum, or herbs.
async fn deduct_player_inventory(
    conn: &mut sqlx::PgConnection,
    player_id: i32,
    idx: usize,
    amount: i64,
) -> Result<(), sqlx::Error> {
    #[allow(clippy::cast_possible_truncation)]
    let amt = amount as i32;

    match idx {
        17 => {
            sqlx::query("UPDATE players SET platinum = platinum - $1 WHERE id = $2")
                .bind(amt)
                .bind(player_id)
                .execute(&mut *conn)
                .await?;
        }
        0..17 => {
            let col = ITEMS[idx];
            if ALLOWED_MINERALS.contains(&col) {
                let sql = format!("UPDATE minerals SET {col} = {col} - $1 WHERE owner = $2");
                sqlx::query(&sql)
                    .bind(amt)
                    .bind(player_id)
                    .execute(&mut *conn)
                    .await?;
            }
        }
        18..=25 => {
            let col = herb_db_column(ITEMS[idx]);
            if ALLOWED_HERBS.contains(&col) {
                let sql = format!("UPDATE herbs SET {col} = {col} - $1 WHERE gracz = $2");
                sqlx::query(&sql)
                    .bind(amt)
                    .bind(player_id)
                    .execute(&mut *conn)
                    .await?;
            }
        }
        _ => {}
    }
    Ok(())
}

/// Add items to the player's minerals, platinum, or herbs.
async fn add_player_inventory(
    conn: &mut sqlx::PgConnection,
    player_id: i32,
    idx: usize,
    amount: i64,
) -> Result<(), sqlx::Error> {
    #[allow(clippy::cast_possible_truncation)]
    let amt = amount as i32;

    match idx {
        17 => {
            sqlx::query("UPDATE players SET platinum = platinum + $1 WHERE id = $2")
                .bind(amt)
                .bind(player_id)
                .execute(&mut *conn)
                .await?;
        }
        0..17 => {
            sqlx::query("INSERT INTO minerals (owner) VALUES ($1) ON CONFLICT (owner) DO NOTHING")
                .bind(player_id)
                .execute(&mut *conn)
                .await?;
            let col = ITEMS[idx];
            if ALLOWED_MINERALS.contains(&col) {
                let sql = format!("UPDATE minerals SET {col} = {col} + $1 WHERE owner = $2");
                sqlx::query(&sql)
                    .bind(amt)
                    .bind(player_id)
                    .execute(&mut *conn)
                    .await?;
            }
        }
        18..=25 => {
            sqlx::query("INSERT INTO herbs (gracz) VALUES ($1) ON CONFLICT DO NOTHING")
                .bind(player_id)
                .execute(&mut *conn)
                .await?;
            let col = herb_db_column(ITEMS[idx]);
            if ALLOWED_HERBS.contains(&col) {
                let sql = format!("UPDATE herbs SET {col} = {col} + $1 WHERE gracz = $2");
                sqlx::query(&sql)
                    .bind(amt)
                    .bind(player_id)
                    .execute(&mut *conn)
                    .await?;
            }
        }
        _ => {}
    }
    Ok(())
}

// =========================================================================
// Shared helpers
// =========================================================================

fn parse_gold_setting(res: Result<Option<sq::SettingRow>, sqlx::Error>) -> i64 {
    match res {
        Ok(opt) => opt
            .and_then(|r| r.value.as_deref().and_then(|v| v.parse::<i64>().ok()))
            .unwrap_or(0),
        Err(e) => {
            tracing::error!(error = %e, "Failed to parse gold setting");
            0
        }
    }
}

fn parse_caravan_setting(res: Result<Option<sq::SettingRow>, sqlx::Error>) -> bool {
    match res {
        Ok(opt) => opt.and_then(|r| r.value).is_some_and(|v| v == "Y"),
        Err(e) => {
            tracing::error!(error = %e, "Failed to parse caravan setting");
            false
        }
    }
}

fn build_price_map(prices: &[sq::SettingRow]) -> std::collections::HashMap<&str, i64> {
    let mut map = std::collections::HashMap::new();
    for row in prices {
        if let Some(val) = row.value.as_deref() {
            if let Ok(p) = val.parse::<i64>() {
                if let Some(key) = ITEMS.iter().find(|&&k| k == row.setting) {
                    map.insert(*key, p);
                }
            }
        }
    }
    map
}

fn build_stock_map(stock: &[gq::WarehouseRow]) -> std::collections::HashMap<&str, i64> {
    let mut map = std::collections::HashMap::new();
    for row in stock {
        if let Some(key) = ITEMS.iter().find(|&&k| k == row.mineral) {
            map.insert(*key, row.amount);
        }
    }
    map
}

/// Get the amount of a commodity the player owns.
async fn player_owned_amount(app: &AppState, player_id: i32, idx: usize, platinum: i32) -> i64 {
    if idx == 17 {
        return i64::from(platinum);
    }

    if idx < 17 {
        let minerals = match gq::load_minerals(&app.pool, player_id).await {
            Ok(v) => v.unwrap_or_default(),
            Err(e) => {
                tracing::error!(error = %e, player_id, "Failed to load minerals for warehouse");
                gq::MineralsRow::default()
            }
        };
        return i64::from(player_amount_mineral(&minerals, idx));
    }

    // Herbs (idx 18..26)
    let herbs = match gq::load_herbs(&app.pool, player_id).await {
        Ok(v) => v.unwrap_or_default(),
        Err(e) => {
            tracing::error!(error = %e, player_id, "Failed to load herbs for warehouse");
            gq::HerbsRow::default()
        }
    };
    i64::from(player_amount_herb(&herbs, idx))
}

/// Load the base price for an item from settings.
async fn load_item_price(app: &AppState, idx: usize) -> i64 {
    match sq::get_setting(&app.pool, ITEMS[idx]).await {
        Ok(opt) => opt
            .and_then(|r| r.value.as_deref().and_then(|v| v.parse::<i64>().ok()))
            .unwrap_or(0),
        Err(e) => {
            tracing::error!(error = %e, item = ITEMS[idx], "Failed to load item price");
            0
        }
    }
}

/// Load kingdom gold from settings.
async fn load_kingdom_gold(app: &AppState) -> i64 {
    match sq::get_setting(&app.pool, "gold").await {
        Ok(opt) => opt
            .and_then(|r| r.value.as_deref().and_then(|v| v.parse::<i64>().ok()))
            .unwrap_or(0),
        Err(e) => {
            tracing::error!(error = %e, "Failed to load kingdom gold");
            0
        }
    }
}

async fn load_player(
    state: &AppState,
    player_id: i32,
) -> Result<vallheru_data::queries::player::PlayerRow, Response> {
    match vallheru_data::queries::player::find_player_by_id(&state.pool, player_id).await {
        Ok(Some(row)) => Ok(row),
        Ok(None) => Err(crate::page::redirect("/login")),
        Err(e) => {
            tracing::error!(error = %e, "warehouse load_player failed");
            Err(server_error())
        }
    }
}

fn is_in_city(location: &str) -> bool {
    location == "Altara" || location == "Ardulith"
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
