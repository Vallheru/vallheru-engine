//! Bank, wealth overview, and magic shop handlers.
//!
//! Ported from `bank.php` (deposit/withdraw only), `zloto.php` (wealth
//! overview), and `msklep.php` (potion shop).

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

            if let Err(e) = vallheru_data::queries::bank::set_player_balance(
                &app.pool,
                player_id,
                change.new_source,
                change.new_dest,
            )
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

            if let Err(e) = vallheru_data::queries::bank::set_player_balance(
                &app.pool,
                player_id,
                change.new_dest,
                change.new_source,
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
