//! Bank, wealth overview, magic shop, and player transfer handlers.
//!
//! Ported from `bank.php` (deposit/withdraw + player-to-player transfers),
//! `zloto.php` (wealth overview), and `msklep.php` (potion shop).

use axum::{Extension, Form, extract::State, response::IntoResponse, response::Response};

use crate::middleware::context::RequestContext;
use crate::page::{Flash, FlashKind, PageMeta};
use crate::state::AppState;
use vallheru_domain::economy::currency;

// =========================================================================
// View models
// =========================================================================

/// Wealth overview page — shows all currencies, minerals, and herbs.
#[derive(serde::Serialize)]
pub struct WealthView {
    #[serde(flatten)]
    pub base: crate::render::RenderContext,
    pub credits: i32,
    pub bank: i32,
    pub platinum: i32,
    pub vallars: i32,
    pub maps: i16,
    pub minerals: Vec<ResourceEntry>,
    pub herbs: Vec<ResourceEntry>,
}

/// A single resource entry (label + amount) for display in the wealth table.
#[derive(serde::Serialize)]
pub struct ResourceEntry {
    pub label: &'static str,
    pub amount: i32,
}

/// Bank page — shows balances and deposit/withdraw forms.
#[derive(serde::Serialize)]
pub struct BankView {
    #[serde(flatten)]
    pub base: crate::render::RenderContext,
    pub credits: i32,
    pub bank: i32,
}

/// Form data for bank deposit/withdraw.
#[derive(serde::Deserialize)]
pub struct BankForm {
    pub amount: Option<i32>,
    pub action: Option<String>,
}

/// Magic shop browse view — lists potions available for purchase.
#[derive(serde::Serialize)]
pub struct MagicShopView {
    #[serde(flatten)]
    pub base: crate::render::RenderContext,
    pub potions: Vec<ShopPotionEntry>,
}

/// A single potion listing in the shop.
#[derive(serde::Serialize)]
pub struct ShopPotionEntry {
    pub id: i32,
    pub name: String,
    pub power: i32,
    pub efect: String,
    pub amount: i32,
    pub cost: i32,
}

/// Magic shop buy confirmation view.
#[derive(serde::Serialize)]
pub struct MagicShopBuyView {
    #[serde(flatten)]
    pub base: crate::render::RenderContext,
    pub potion_id: i32,
    pub potion_name: String,
}

/// Form data for potion purchase.
#[derive(serde::Deserialize)]
pub struct BuyPotionForm {
    pub amount: Option<i32>,
}

// =========================================================================
// GET /wealth — wealth overview (zloto.php)
// =========================================================================

pub async fn wealth_show(
    State(app): State<AppState>,
    Extension(ctx): Extension<RequestContext>,
) -> Response {
    let Some(ref user) = ctx.session_user else {
        return crate::page::redirect("/login");
    };

    #[allow(clippy::cast_possible_truncation)]
    let player_id = user.id as i32;

    let player = match load_player(&app, player_id).await {
        Ok(row) => row,
        Err(resp) => return resp,
    };

    let minerals =
        match vallheru_data::queries::gathering::load_minerals(&app.pool, player_id).await {
            Ok(Some(m)) => build_minerals(&m),
            Ok(None) => build_minerals(&vallheru_data::queries::gathering::MineralsRow::default()),
            Err(e) => {
                tracing::error!(error = %e, "wealth: failed to load minerals");
                return server_error();
            }
        };

    let herbs = match vallheru_data::queries::gathering::load_herbs(&app.pool, player_id).await {
        Ok(Some(h)) => build_herbs(&h),
        Ok(None) => build_herbs(&vallheru_data::queries::gathering::HerbsRow::default()),
        Err(e) => {
            tracing::error!(error = %e, "wealth: failed to load herbs");
            return server_error();
        }
    };

    let meta = PageMeta::titled("Bogactwa").with_back_link("/city", "Wróć do miasta");
    let base = app.templates.build_context(&ctx, &meta);

    let view = WealthView {
        base,
        credits: player.credits,
        bank: player.bank,
        platinum: player.platinum,
        vallars: player.vallars,
        maps: player.maps,
        minerals,
        herbs,
    };

    app.templates.render_value("wealth.html", &view)
}

// =========================================================================
// GET /bank — bank page (bank.php)
// =========================================================================

pub async fn bank_show(
    State(app): State<AppState>,
    Extension(ctx): Extension<RequestContext>,
) -> Response {
    let Some(ref user) = ctx.session_user else {
        return crate::page::redirect("/login");
    };

    #[allow(clippy::cast_possible_truncation)]
    let player_id = user.id as i32;

    let player = match load_player(&app, player_id).await {
        Ok(row) => row,
        Err(resp) => return resp,
    };

    if !is_in_city(&player.location) {
        return error_page(&app, &ctx, "Nie znajdujesz się w mieście.");
    }

    let meta = PageMeta::titled("Bank").with_back_link("/city", "Wróć do miasta");
    let base = app.templates.build_context(&ctx, &meta);

    let view = BankView {
        base,
        credits: player.credits,
        bank: player.bank,
    };

    app.templates.render_value("bank.html", &view)
}

// =========================================================================
// POST /bank — deposit or withdraw (bank.php)
// =========================================================================

pub async fn bank_action(
    State(app): State<AppState>,
    Extension(ctx): Extension<RequestContext>,
    Form(form): Form<BankForm>,
) -> Response {
    let Some(ref user) = ctx.session_user else {
        return crate::page::redirect("/login");
    };

    #[allow(clippy::cast_possible_truncation)]
    let player_id = user.id as i32;

    let player = match load_player(&app, player_id).await {
        Ok(row) => row,
        Err(resp) => return resp,
    };

    if !is_in_city(&player.location) {
        return error_page(&app, &ctx, "Nie znajdujesz się w mieście.");
    }

    let amount = match form.amount {
        Some(a) if a > 0 => a,
        _ => return error_page(&app, &ctx, "Podaj prawidłową kwotę."),
    };

    let action = form.action.as_deref().unwrap_or("");

    match action {
        "deposit" => {
            let Ok(change) = currency::deposit_gold(player.credits, player.bank, amount) else {
                return error_page(&app, &ctx, "Nie masz wystarczająco złota.");
            };

            if let Err(e) =
                vallheru_data::queries::bank::deposit_to_bank(&app.pool, player_id, change.amount)
                    .await
            {
                tracing::error!(error = %e, "bank deposit failed");
                return server_error();
            }

            let meta = PageMeta::titled("Bank")
                .with_back_link("/bank", "Wróć do banku")
                .with_flash(Flash::success(format!(
                    "Wpłacono {} sztuk złota do banku.",
                    change.amount
                )));
            let base = app.templates.build_context(&ctx, &meta);
            let view = BankView {
                base,
                credits: change.new_source,
                bank: change.new_dest,
            };
            app.templates.render_value("bank.html", &view)
        }
        "withdraw" => {
            let Ok(change) = currency::withdraw_gold(player.credits, player.bank, amount) else {
                return error_page(&app, &ctx, "Nie masz wystarczająco złota w banku.");
            };

            if let Err(e) = vallheru_data::queries::bank::withdraw_from_bank(
                &app.pool,
                player_id,
                change.amount,
            )
            .await
            {
                tracing::error!(error = %e, "bank withdrawal failed");
                return server_error();
            }

            let meta = PageMeta::titled("Bank")
                .with_back_link("/bank", "Wróć do banku")
                .with_flash(Flash::success(format!(
                    "Wypłacono {} sztuk złota z banku.",
                    change.amount
                )));
            let base = app.templates.build_context(&ctx, &meta);
            let view = BankView {
                base,
                credits: change.new_dest,
                bank: change.new_source,
            };
            app.templates.render_value("bank.html", &view)
        }
        _ => error_page(&app, &ctx, "Nieznana akcja."),
    }
}

// =========================================================================
// GET /magic-shop — shop browse (msklep.php)
// =========================================================================

pub async fn magic_shop_show(
    State(app): State<AppState>,
    Extension(ctx): Extension<RequestContext>,
) -> Response {
    let Some(ref user) = ctx.session_user else {
        return crate::page::redirect("/login");
    };

    #[allow(clippy::cast_possible_truncation)]
    let player_id = user.id as i32;

    let player = match load_player(&app, player_id).await {
        Ok(row) => row,
        Err(resp) => return resp,
    };

    if !is_in_city(&player.location) {
        return error_page(&app, &ctx, "Nie znajdujesz się w mieście.");
    }

    let lang = &ctx.locale;
    let shop_potions = match vallheru_data::queries::item::find_shop_potions(&app.pool, lang).await
    {
        Ok(rows) => rows,
        Err(e) => {
            tracing::error!(error = %e, "magic_shop: failed to load potions");
            return server_error();
        }
    };

    let potions: Vec<ShopPotionEntry> = shop_potions
        .iter()
        .map(|p| ShopPotionEntry {
            id: p.id,
            name: p.name.clone(),
            power: p.power,
            efect: p.efect.clone(),
            amount: p.amount,
            cost: currency::potion_shop_price(&p.potion_type, p.power),
        })
        .collect();

    let meta = PageMeta::titled("Alchemik").with_back_link("/city", "Wróć do miasta");
    let base = app.templates.build_context(&ctx, &meta);

    let view = MagicShopView { base, potions };
    app.templates.render_value("magic_shop.html", &view)
}

// =========================================================================
// GET /magic-shop/buy/:id — buy confirmation page
// =========================================================================

pub async fn magic_shop_buy_show(
    State(app): State<AppState>,
    Extension(ctx): Extension<RequestContext>,
    axum::extract::Path(potion_id): axum::extract::Path<i32>,
) -> Response {
    let Some(ref user) = ctx.session_user else {
        return crate::page::redirect("/login");
    };

    #[allow(clippy::cast_possible_truncation)]
    let player_id = user.id as i32;

    let player = match load_player(&app, player_id).await {
        Ok(row) => row,
        Err(resp) => return resp,
    };

    if !is_in_city(&player.location) {
        return error_page(&app, &ctx, "Nie znajdujesz się w mieście.");
    }

    let potion = match vallheru_data::queries::item::find_potion_by_id(&app.pool, potion_id).await {
        Ok(Some(p)) => p,
        Ok(None) => return error_page(&app, &ctx, "Nie znaleziono mikstury."),
        Err(e) => {
            tracing::error!(error = %e, "magic_shop buy: failed to load potion");
            return server_error();
        }
    };

    let meta = PageMeta::titled("Kup miksturę").with_back_link("/magic-shop", "Wróć do alchemika");
    let base = app.templates.build_context(&ctx, &meta);

    let view = MagicShopBuyView {
        base,
        potion_id: potion.id,
        potion_name: potion.name,
    };
    app.templates.render_value("magic_shop_buy.html", &view)
}

// =========================================================================
// POST /magic-shop/buy/:id — execute purchase
// =========================================================================

#[allow(clippy::too_many_lines)]
pub async fn magic_shop_buy_action(
    State(app): State<AppState>,
    Extension(ctx): Extension<RequestContext>,
    axum::extract::Path(potion_id): axum::extract::Path<i32>,
    Form(form): Form<BuyPotionForm>,
) -> Response {
    let Some(ref user) = ctx.session_user else {
        return crate::page::redirect("/login");
    };

    #[allow(clippy::cast_possible_truncation)]
    let player_id = user.id as i32;

    let player = match load_player(&app, player_id).await {
        Ok(row) => row,
        Err(resp) => return resp,
    };

    if !is_in_city(&player.location) {
        return error_page(&app, &ctx, "Nie znajdujesz się w mieście.");
    }

    let buy_amount = match form.amount {
        Some(a) if a > 0 => a,
        _ => return error_page(&app, &ctx, "Podaj liczbę mikstur do kupienia."),
    };

    let potion = match vallheru_data::queries::item::find_potion_by_id(&app.pool, potion_id).await {
        Ok(Some(p)) => p,
        Ok(None) => return error_page(&app, &ctx, "Nie znaleziono mikstury."),
        Err(e) => {
            tracing::error!(error = %e, "magic_shop buy: failed to load potion");
            return server_error();
        }
    };

    if potion.status != "S" {
        return error_page(&app, &ctx, "Ta mikstura nie jest dostępna w sprzedaży.");
    }

    if buy_amount > potion.amount {
        return error_page(&app, &ctx, "Nie ma tylu mikstur w magazynie.");
    }

    let unit_price = currency::potion_shop_price(&potion.potion_type, potion.power);
    let total_cost = unit_price * buy_amount;

    if let Err(_e) = currency::spend_credits(player.credits, total_cost) {
        return error_page(&app, &ctx, "Nie masz wystarczająco złota.");
    }

    // Check if the player already owns this potion (same name, power, status K)
    let existing = match vallheru_data::queries::item::find_player_potion_stack(
        &app.pool,
        player_id,
        &potion.name,
        potion.power,
    )
    .await
    {
        Ok(opt) => opt,
        Err(e) => {
            tracing::error!(error = %e, "magic_shop buy: failed to check existing potions");
            return server_error();
        }
    };

    let resale_cost = currency::potion_resale_cost(unit_price);

    // Add potions to player inventory
    let result = if let Some(existing_stack) = existing {
        vallheru_data::queries::item::add_to_potion_stack(&app.pool, existing_stack.id, buy_amount)
            .await
    } else {
        vallheru_data::queries::item::create_player_potion(
            &app.pool,
            player_id,
            &potion.name,
            &potion.efect,
            &potion.potion_type,
            potion.power,
            buy_amount,
            resale_cost,
        )
        .await
    };

    if let Err(e) = result {
        tracing::error!(error = %e, "magic_shop buy: failed to give potions to player");
        return server_error();
    }

    // Deduct gold and decrease shop stock
    if let Err(e) =
        vallheru_data::queries::bank::deduct_credits(&app.pool, player_id, total_cost).await
    {
        tracing::error!(error = %e, "magic_shop buy: failed to deduct credits");
        return server_error();
    }

    if let Err(e) =
        vallheru_data::queries::item::decrease_potion_stock(&app.pool, potion_id, buy_amount).await
    {
        tracing::error!(error = %e, "magic_shop buy: failed to decrease stock");
        return server_error();
    }

    tracing::info!(
        player_id,
        potion_id,
        buy_amount,
        total_cost,
        "potion purchase completed"
    );

    let meta = PageMeta::titled("Alchemik")
        .with_back_link("/magic-shop", "Wróć do alchemika")
        .with_flash(Flash::success(format!(
            "Zapłacono {total_cost} sztuk złota za {buy_amount}× {}.",
            potion.name
        )));
    let base = app.templates.build_context(&ctx, &meta);
    let view = MagicShopView {
        base,
        potions: vec![],
    };
    app.templates.render_value("magic_shop.html", &view)
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

fn build_minerals(m: &vallheru_data::queries::gathering::MineralsRow) -> Vec<ResourceEntry> {
    vec![
        ResourceEntry {
            label: "Rudy miedzi",
            amount: m.copperore,
        },
        ResourceEntry {
            label: "Drewna sosnowego",
            amount: m.pine,
        },
        ResourceEntry {
            label: "Rudy cynku",
            amount: m.zincore,
        },
        ResourceEntry {
            label: "Drewna z leszczyny",
            amount: m.hazel,
        },
        ResourceEntry {
            label: "Rudy cyny",
            amount: m.tinore,
        },
        ResourceEntry {
            label: "Drewna cisowego",
            amount: m.yew,
        },
        ResourceEntry {
            label: "Rudy żelaza",
            amount: m.ironore,
        },
        ResourceEntry {
            label: "Drewna z wiązu",
            amount: m.elm,
        },
        ResourceEntry {
            label: "Sztabek miedzi",
            amount: m.copper,
        },
        ResourceEntry {
            label: "Sztabek brązu",
            amount: m.bronze,
        },
        ResourceEntry {
            label: "Sztabek mosiądzu",
            amount: m.brass,
        },
        ResourceEntry {
            label: "Sztabek żelaza",
            amount: m.iron,
        },
        ResourceEntry {
            label: "Sztabek stali",
            amount: m.steel,
        },
        ResourceEntry {
            label: "Brył węgla",
            amount: m.coal,
        },
        ResourceEntry {
            label: "Brył adamantium",
            amount: m.adamantium,
        },
        ResourceEntry {
            label: "Kawałków meteorytu",
            amount: m.meteor,
        },
        ResourceEntry {
            label: "Kryształów",
            amount: m.crystal,
        },
    ]
}

fn build_herbs(h: &vallheru_data::queries::gathering::HerbsRow) -> Vec<ResourceEntry> {
    vec![
        ResourceEntry {
            label: "Illani",
            amount: h.illani,
        },
        ResourceEntry {
            label: "Nasiona Illani",
            amount: h.ilani_seeds,
        },
        ResourceEntry {
            label: "Illanias",
            amount: h.illanias,
        },
        ResourceEntry {
            label: "Nasiona Illanias",
            amount: h.illanias_seeds,
        },
        ResourceEntry {
            label: "Nutari",
            amount: h.nutari,
        },
        ResourceEntry {
            label: "Nasiona Nutari",
            amount: h.nutari_seeds,
        },
        ResourceEntry {
            label: "Dynallca",
            amount: h.dynallca,
        },
        ResourceEntry {
            label: "Nasiona Dynallca",
            amount: h.dynallca_seeds,
        },
    ]
}

// =========================================================================
// Player-to-player Transfers (bank.php donation system)
// =========================================================================

/// Transfer page view model — shows available resources and forms.
#[derive(serde::Serialize)]
pub struct TransferView {
    #[serde(flatten)]
    pub base: crate::render::RenderContext,
    pub bank: i32,
    pub platinum: i32,
    pub contacts: Vec<ContactEntry>,
    pub items: Vec<TransferItemEntry>,
    pub potions: Vec<TransferPotionEntry>,
    pub minerals: Vec<TransferResourceEntry>,
    pub herbs: Vec<TransferResourceEntry>,
    pub pets: Vec<TransferPetEntry>,
}

#[derive(serde::Serialize)]
pub struct ContactEntry {
    pub id: i64,
    pub name: String,
}

#[derive(serde::Serialize)]
pub struct TransferItemEntry {
    pub id: i32,
    pub name: String,
    pub amount: i32,
    pub equipment_type: String,
}

#[derive(serde::Serialize)]
pub struct TransferPotionEntry {
    pub id: i32,
    pub name: String,
    pub power: i32,
    pub amount: i32,
}

#[derive(serde::Serialize)]
pub struct TransferResourceEntry {
    pub key: String,
    pub label: String,
    pub amount: i32,
}

#[derive(serde::Serialize)]
pub struct TransferPetEntry {
    pub id: i32,
    pub display_name: String,
}

/// Form for all transfer actions.
#[derive(serde::Deserialize)]
pub struct TransferForm {
    /// The type of transfer: gold, mithril, mineral, herb, potion, item, pet.
    pub transfer_type: String,
    /// Recipient player ID (manual entry or from contacts dropdown).
    pub recipient_id: Option<i32>,
    /// Contact selection (if chosen from dropdown instead of typing ID).
    pub contact_id: Option<i32>,
    /// Amount to transfer (for gold, mithril, minerals, herbs, potions, items).
    pub amount: Option<i32>,
    /// Specific resource key (mineral column or herb column).
    pub resource_key: Option<String>,
    /// Item/potion/pet ID for specific-item transfers.
    pub item_id: Option<i32>,
    /// Optional transfer memo (max 50 chars).
    pub title: Option<String>,
}

/// GET /bank/transfer — show transfer forms.
pub async fn transfer_show(
    State(app): State<AppState>,
    Extension(ctx): Extension<RequestContext>,
) -> Response {
    let Some(ref user) = ctx.session_user else {
        return crate::page::redirect("/login");
    };

    #[allow(clippy::cast_possible_truncation)]
    let player_id = user.id as i32;

    let player = match load_player(&app, player_id).await {
        Ok(row) => row,
        Err(resp) => return resp,
    };

    if !is_in_city(&player.location) {
        return error_page(&app, &ctx, "Nie znajdujesz się w mieście.");
    }

    build_transfer_page(&app, &ctx, player_id, &player, None).await
}

/// POST /bank/transfer — execute a transfer.
pub async fn transfer_action(
    State(app): State<AppState>,
    Extension(ctx): Extension<RequestContext>,
    Form(form): Form<TransferForm>,
) -> Response {
    let Some(ref user) = ctx.session_user else {
        return crate::page::redirect("/login");
    };

    #[allow(clippy::cast_possible_truncation)]
    let player_id = user.id as i32;

    let player = match load_player(&app, player_id).await {
        Ok(row) => row,
        Err(resp) => return resp,
    };

    if !is_in_city(&player.location) {
        return error_page(&app, &ctx, "Nie znajdujesz się w mieście.");
    }

    // Resolve recipient: prefer contact_id if set, else recipient_id.
    let recipient_id = form
        .contact_id
        .filter(|&id| id > 0)
        .or(form.recipient_id)
        .unwrap_or(0);

    if recipient_id <= 0 {
        return transfer_error(&app, &ctx, player_id, &player, "Podaj gracza.").await;
    }

    if recipient_id == player_id {
        return transfer_error(
            &app,
            &ctx,
            player_id,
            &player,
            "Nie możesz przekazać zasobów samemu sobie.",
        )
        .await;
    }

    // Verify recipient exists.
    let recipient = match vallheru_data::queries::player::find_player_by_id(&app.pool, recipient_id)
        .await
    {
        Ok(Some(r)) => r,
        Ok(None) => {
            return transfer_error(&app, &ctx, player_id, &player, "Nie ma takiego gracza.").await;
        }
        Err(e) => {
            tracing::error!(error = %e, "transfer: lookup recipient failed");
            return server_error();
        }
    };

    let tctx = TransferContext {
        player_id,
        recipient_id,
        sender_name: &player.username,
        recipient_name: &recipient.username,
        memo_suffix: build_memo_suffix(form.title.as_deref()),
    };

    match form.transfer_type.as_str() {
        "gold" => do_transfer_gold(&app, &ctx, &player, &tctx, &form).await,
        "mithril" => do_transfer_mithril(&app, &ctx, &player, &tctx, &form).await,
        "mineral" => do_transfer_mineral(&app, &ctx, &player, &tctx, &form).await,
        "herb" => do_transfer_herb(&app, &ctx, &player, &tctx, &form).await,
        "potion" => do_transfer_potion(&app, &ctx, &player, &tctx, &form).await,
        "item" => do_transfer_item(&app, &ctx, &player, &tctx, &form).await,
        "pet" => do_transfer_pet(&app, &ctx, &player, &tctx, &form).await,
        _ => error_page(&app, &ctx, "Nieznana akcja."),
    }
}

// =========================================================================
// Transfer helpers
// =========================================================================

/// Shared context for all transfer sub-handlers.
struct TransferContext<'a> {
    player_id: i32,
    recipient_id: i32,
    sender_name: &'a str,
    recipient_name: &'a str,
    memo_suffix: String,
}

fn build_memo_suffix(title: Option<&str>) -> String {
    let memo: String = title.unwrap_or("").chars().take(50).collect();
    if memo.is_empty() {
        String::new()
    } else {
        format!(", tytułem: {memo}")
    }
}

fn require_amount(form: &TransferForm) -> Option<i32> {
    match form.amount {
        Some(a) if a > 0 => Some(a),
        _ => None,
    }
}

async fn do_transfer_gold(
    app: &AppState,
    ctx: &RequestContext,
    player: &vallheru_data::queries::player::PlayerRow,
    tc: &TransferContext<'_>,
    form: &TransferForm,
) -> Response {
    let Some(amount) = require_amount(form) else {
        return transfer_error(app, ctx, tc.player_id, player, "Podaj prawidłową kwotę.").await;
    };
    match vallheru_data::queries::bank::transfer_gold(
        &app.pool,
        tc.player_id,
        tc.recipient_id,
        amount,
    )
    .await
    {
        Ok(true) => {}
        Ok(false) => {
            return transfer_error(
                app,
                ctx,
                tc.player_id,
                player,
                "Nie masz wystarczająco złota w banku.",
            )
            .await;
        }
        Err(e) => {
            tracing::error!(error = %e, "transfer gold failed");
            return server_error();
        }
    }
    let desc = format!("{amount} sztuk złota");
    finish_transfer(app, ctx, tc, &desc).await
}

async fn do_transfer_mithril(
    app: &AppState,
    ctx: &RequestContext,
    player: &vallheru_data::queries::player::PlayerRow,
    tc: &TransferContext<'_>,
    form: &TransferForm,
) -> Response {
    let Some(amount) = require_amount(form) else {
        return transfer_error(app, ctx, tc.player_id, player, "Podaj prawidłową kwotę.").await;
    };
    match vallheru_data::queries::bank::transfer_mithril(
        &app.pool,
        tc.player_id,
        tc.recipient_id,
        amount,
    )
    .await
    {
        Ok(true) => {}
        Ok(false) => {
            return transfer_error(
                app,
                ctx,
                tc.player_id,
                player,
                "Nie masz wystarczająco mithrilu.",
            )
            .await;
        }
        Err(e) => {
            tracing::error!(error = %e, "transfer mithril failed");
            return server_error();
        }
    }
    let desc = format!("{amount} sztuk mithrilu");
    finish_transfer(app, ctx, tc, &desc).await
}

async fn do_transfer_mineral(
    app: &AppState,
    ctx: &RequestContext,
    player: &vallheru_data::queries::player::PlayerRow,
    tc: &TransferContext<'_>,
    form: &TransferForm,
) -> Response {
    let Some(amount) = require_amount(form) else {
        return transfer_error(app, ctx, tc.player_id, player, "Podaj prawidłową ilość.").await;
    };
    let resource_key = form.resource_key.as_deref().unwrap_or("");
    let label = mineral_label(resource_key);
    if label.is_empty() {
        return transfer_error(app, ctx, tc.player_id, player, "Nieznany minerał.").await;
    }
    match vallheru_data::queries::bank::transfer_mineral(
        &app.pool,
        tc.player_id,
        tc.recipient_id,
        resource_key,
        amount,
    )
    .await
    {
        Ok(true) => {}
        Ok(false) => {
            return transfer_error(
                app,
                ctx,
                tc.player_id,
                player,
                &format!("Nie masz wystarczająco: {label}."),
            )
            .await;
        }
        Err(e) => {
            tracing::error!(error = %e, "transfer mineral failed");
            return server_error();
        }
    }
    let desc = format!("{amount} {label}");
    finish_transfer(app, ctx, tc, &desc).await
}

async fn do_transfer_herb(
    app: &AppState,
    ctx: &RequestContext,
    player: &vallheru_data::queries::player::PlayerRow,
    tc: &TransferContext<'_>,
    form: &TransferForm,
) -> Response {
    let Some(amount) = require_amount(form) else {
        return transfer_error(app, ctx, tc.player_id, player, "Podaj prawidłową ilość.").await;
    };
    let resource_key = form.resource_key.as_deref().unwrap_or("");
    let label = herb_label(resource_key);
    if label.is_empty() {
        return transfer_error(app, ctx, tc.player_id, player, "Nieznane zioło.").await;
    }
    match vallheru_data::queries::bank::transfer_herb(
        &app.pool,
        tc.player_id,
        tc.recipient_id,
        resource_key,
        amount,
    )
    .await
    {
        Ok(true) => {}
        Ok(false) => {
            return transfer_error(
                app,
                ctx,
                tc.player_id,
                player,
                &format!("Nie masz wystarczająco: {label}."),
            )
            .await;
        }
        Err(e) => {
            tracing::error!(error = %e, "transfer herb failed");
            return server_error();
        }
    }
    let desc = format!("{amount} {label}");
    finish_transfer(app, ctx, tc, &desc).await
}

async fn do_transfer_potion(
    app: &AppState,
    ctx: &RequestContext,
    player: &vallheru_data::queries::player::PlayerRow,
    tc: &TransferContext<'_>,
    form: &TransferForm,
) -> Response {
    let Some(amount) = require_amount(form) else {
        return transfer_error(app, ctx, tc.player_id, player, "Podaj prawidłową ilość.").await;
    };
    let item_id = form.item_id.unwrap_or(0);
    match vallheru_data::queries::bank::transfer_potion(
        &app.pool,
        tc.player_id,
        item_id,
        tc.recipient_id,
        amount,
    )
    .await
    {
        Ok(true) => {}
        Ok(false) => {
            return transfer_error(
                app,
                ctx,
                tc.player_id,
                player,
                "Nie posiadasz tego mikstury lub nie masz wystarczająco.",
            )
            .await;
        }
        Err(e) => {
            tracing::error!(error = %e, "transfer potion failed");
            return server_error();
        }
    }
    finish_transfer(app, ctx, tc, "mikstury").await
}

async fn do_transfer_item(
    app: &AppState,
    ctx: &RequestContext,
    player: &vallheru_data::queries::player::PlayerRow,
    tc: &TransferContext<'_>,
    form: &TransferForm,
) -> Response {
    let Some(amount) = require_amount(form) else {
        return transfer_error(app, ctx, tc.player_id, player, "Podaj prawidłową ilość.").await;
    };
    let item_id = form.item_id.unwrap_or(0);
    match vallheru_data::queries::bank::transfer_equipment(
        &app.pool,
        tc.player_id,
        item_id,
        tc.recipient_id,
        amount,
    )
    .await
    {
        Ok(true) => {}
        Ok(false) => {
            return transfer_error(
                app,
                ctx,
                tc.player_id,
                player,
                "Nie posiadasz tego przedmiotu lub nie masz wystarczająco.",
            )
            .await;
        }
        Err(e) => {
            tracing::error!(error = %e, "transfer equipment failed");
            return server_error();
        }
    }
    finish_transfer(app, ctx, tc, "przedmiot").await
}

async fn do_transfer_pet(
    app: &AppState,
    ctx: &RequestContext,
    player: &vallheru_data::queries::player::PlayerRow,
    tc: &TransferContext<'_>,
    form: &TransferForm,
) -> Response {
    let item_id = form.item_id.unwrap_or(0);
    match vallheru_data::queries::bank::transfer_pet(
        &app.pool,
        tc.player_id,
        item_id,
        tc.recipient_id,
    )
    .await
    {
        Ok(true) => {}
        Ok(false) => {
            return transfer_error(
                app,
                ctx,
                tc.player_id,
                player,
                "Nie posiadasz tego chowańca.",
            )
            .await;
        }
        Err(e) => {
            tracing::error!(error = %e, "transfer pet failed");
            return server_error();
        }
    }
    finish_transfer(app, ctx, tc, "chowańca").await
}

/// Common ending for all successful transfers: log + render success page.
async fn finish_transfer(
    app: &AppState,
    ctx: &RequestContext,
    tc: &TransferContext<'_>,
    what: &str,
) -> Response {
    let msg_to = format!(
        "Gracz {} (ID:{}) przekazał tobie {what}{}.",
        tc.sender_name, tc.player_id, tc.memo_suffix
    );
    let msg_from = format!(
        "Przekazałeś graczowi {} (ID:{}) {what}{}.",
        tc.recipient_name, tc.recipient_id, tc.memo_suffix
    );
    log_transfer(app, tc.player_id, tc.recipient_id, &msg_from, &msg_to).await;

    transfer_success(
        app,
        ctx,
        &format!(
            "Przekazałeś graczowi {} (ID:{}) {what}.",
            tc.recipient_name, tc.recipient_id
        ),
    )
}

async fn load_transfer_data(
    pool: &sqlx::PgPool,
    player_id: i32,
) -> (
    Vec<ContactEntry>,
    Vec<TransferItemEntry>,
    Vec<TransferPotionEntry>,
    Vec<TransferResourceEntry>,
    Vec<TransferResourceEntry>,
    Vec<TransferPetEntry>,
) {
    let contacts =
        match vallheru_data::queries::mail::list_contacts(pool, i64::from(player_id)).await {
            Ok(v) => v,
            Err(e) => {
                tracing::error!(player_id, error = %e, "failed to load contacts for bank transfer");
                Vec::new()
            }
        }
        .into_iter()
        .map(|c| ContactEntry {
            id: c.player_id,
            name: c.player_name,
        })
        .collect::<Vec<_>>();

    let items =
        match vallheru_data::queries::item::find_equipment_by_owner(pool, player_id).await {
            Ok(v) => v,
            Err(e) => {
                tracing::error!(player_id, error = %e, "failed to load equipment for bank transfer");
                Vec::new()
            }
        }
        .into_iter()
        .filter(|i| i.status == "U" && i.equipment_type != "Q")
        .map(|i| {
            let qty = if i.equipment_type == "R" {
                i.wt
            } else {
                i.amount
            };
            TransferItemEntry {
                id: i.id,
                name: i.name,
                amount: qty,
                equipment_type: i.equipment_type,
            }
        })
        .collect::<Vec<_>>();

    let potions =
        match vallheru_data::queries::item::find_potions_by_owner(pool, player_id).await {
            Ok(v) => v,
            Err(e) => {
                tracing::error!(player_id, error = %e, "failed to load potions for bank transfer");
                Vec::new()
            }
        }
        .into_iter()
        .filter(|p| p.status == "K")
        .map(|p| TransferPotionEntry {
            id: p.id,
            name: p.name,
            power: p.power,
            amount: p.amount,
        })
        .collect::<Vec<_>>();

    let minerals = match vallheru_data::queries::gathering::load_minerals(pool, player_id).await {
        Ok(Some(m)) => build_transfer_minerals(&m),
        _ => vec![],
    };

    let herbs = match vallheru_data::queries::gathering::load_herbs(pool, player_id).await {
        Ok(Some(h)) => build_transfer_herbs(&h),
        _ => vec![],
    };

    let pets = match vallheru_data::queries::bank::list_player_pets(pool, player_id).await {
        Ok(v) => v,
        Err(e) => {
            tracing::error!(player_id, error = %e, "failed to load pets for bank transfer");
            Vec::new()
        }
    }
    .into_iter()
    .map(|p| {
        let display = if p.corename.is_empty() {
            p.name
        } else {
            format!("{} ({})", p.corename, p.name)
        };
        TransferPetEntry {
            id: p.id,
            display_name: display,
        }
    })
    .collect::<Vec<_>>();

    (contacts, items, potions, minerals, herbs, pets)
}

async fn build_transfer_page(
    app: &AppState,
    ctx: &RequestContext,
    player_id: i32,
    player: &vallheru_data::queries::player::PlayerRow,
    flash: Option<Flash>,
) -> Response {
    let (contacts, items, potions, minerals, herbs, pets) =
        load_transfer_data(&app.pool, player_id).await;

    let mut meta = PageMeta::titled("Bank — Przekazy").with_back_link("/bank", "Wróć do banku");
    if let Some(f) = flash {
        meta = meta.with_flash(f);
    }
    let base = app.templates.build_context(ctx, &meta);

    let view = TransferView {
        base,
        bank: player.bank,
        platinum: player.platinum,
        contacts,
        items,
        potions,
        minerals,
        herbs,
        pets,
    };

    app.templates.render_value("bank_transfer.html", &view)
}

async fn transfer_error(
    app: &AppState,
    ctx: &RequestContext,
    player_id: i32,
    player: &vallheru_data::queries::player::PlayerRow,
    message: &str,
) -> Response {
    build_transfer_page(
        app,
        ctx,
        player_id,
        player,
        Some(Flash {
            kind: FlashKind::Error,
            message: message.to_owned(),
        }),
    )
    .await
}

fn transfer_success(app: &AppState, ctx: &RequestContext, message: &str) -> Response {
    let meta = PageMeta::titled("Bank — Przekazy")
        .with_back_link("/bank/transfer", "Wróć do przekazów")
        .with_flash(Flash::success(message.to_owned()));
    let base = app.templates.build_context(ctx, &meta);
    app.templates.render("bank_transfer_result.html", &base)
}

async fn log_transfer(
    app: &AppState,
    sender_id: i32,
    recipient_id: i32,
    msg_from: &str,
    msg_to: &str,
) {
    // Log for sender.
    if let Err(e) =
        vallheru_data::queries::moderation::insert_game_log(&app.pool, sender_id, msg_from, 'N')
            .await
    {
        tracing::error!(error = %e, "transfer: failed to log for sender");
    }
    // Log for recipient.
    if let Err(e) =
        vallheru_data::queries::moderation::insert_game_log(&app.pool, recipient_id, msg_to, 'N')
            .await
    {
        tracing::error!(error = %e, "transfer: failed to log for recipient");
    }
}

fn build_transfer_minerals(
    m: &vallheru_data::queries::gathering::MineralsRow,
) -> Vec<TransferResourceEntry> {
    let all = [
        ("copperore", "Ruda miedzi", m.copperore),
        ("zincore", "Ruda cynku", m.zincore),
        ("tinore", "Ruda cyny", m.tinore),
        ("ironore", "Ruda żelaza", m.ironore),
        ("coal", "Węgiel", m.coal),
        ("copper", "Miedź", m.copper),
        ("bronze", "Brąz", m.bronze),
        ("brass", "Mosiądz", m.brass),
        ("iron", "Żelazo", m.iron),
        ("steel", "Stal", m.steel),
        ("pine", "Sosna", m.pine),
        ("hazel", "Leszczyna", m.hazel),
        ("yew", "Cis", m.yew),
        ("elm", "Wiąz", m.elm),
        ("crystal", "Kryształ", m.crystal),
        ("adamantium", "Adamantium", m.adamantium),
        ("meteor", "Meteoryt", m.meteor),
    ];
    all.iter()
        .filter(|&&(_, _, amt)| amt > 0)
        .map(|&(key, label, amt)| TransferResourceEntry {
            key: key.to_owned(),
            label: label.to_owned(),
            amount: amt,
        })
        .collect()
}

fn build_transfer_herbs(
    h: &vallheru_data::queries::gathering::HerbsRow,
) -> Vec<TransferResourceEntry> {
    let all = [
        ("illani", "Illani", h.illani),
        ("illanias", "Illanias", h.illanias),
        ("nutari", "Nutari", h.nutari),
        ("dynallca", "Dynallca", h.dynallca),
        ("ilani_seeds", "Nasiona Illani", h.ilani_seeds),
        ("illanias_seeds", "Nasiona Illanias", h.illanias_seeds),
        ("nutari_seeds", "Nasiona Nutari", h.nutari_seeds),
        ("dynallca_seeds", "Nasiona Dynallca", h.dynallca_seeds),
    ];
    all.iter()
        .filter(|&&(_, _, amt)| amt > 0)
        .map(|&(key, label, amt)| TransferResourceEntry {
            key: key.to_owned(),
            label: label.to_owned(),
            amount: amt,
        })
        .collect()
}

fn mineral_label(key: &str) -> &'static str {
    match key {
        "copperore" => "Ruda miedzi",
        "zincore" => "Ruda cynku",
        "tinore" => "Ruda cyny",
        "ironore" => "Ruda żelaza",
        "coal" => "Węgiel",
        "copper" => "Miedź",
        "bronze" => "Brąz",
        "brass" => "Mosiądz",
        "iron" => "Żelazo",
        "steel" => "Stal",
        "pine" => "Sosna",
        "hazel" => "Leszczyna",
        "yew" => "Cis",
        "elm" => "Wiąz",
        "crystal" => "Kryształ",
        "adamantium" => "Adamantium",
        "meteor" => "Meteoryt",
        _ => "",
    }
}

fn herb_label(key: &str) -> &'static str {
    match key {
        "illani" => "Illani",
        "illanias" => "Illanias",
        "nutari" => "Nutari",
        "dynallca" => "Dynallca",
        "ilani_seeds" => "Nasiona Illani",
        "illanias_seeds" => "Nasiona Illanias",
        "nutari_seeds" => "Nasiona Nutari",
        "dynallca_seeds" => "Nasiona Dynallca",
        _ => "",
    }
}
