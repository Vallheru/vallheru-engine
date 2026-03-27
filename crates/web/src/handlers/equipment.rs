//! Equipment and inventory handlers.
//!
//! Ported from `equip.php`. Provides inventory browsing, equip/unequip,
//! sell, and repair actions.

use axum::{Extension, Form, extract::State, response::IntoResponse, response::Response};
use std::fmt::Write;

use crate::middleware::context::RequestContext;
use crate::page::{Flash, FlashKind, PageMeta};
use crate::state::AppState;

// =========================================================================
// View models
// =========================================================================

/// Main equipment page view model.
#[derive(serde::Serialize)]
pub struct EquipmentView {
    #[serde(flatten)]
    pub base: crate::render::RenderContext,
    pub equipped: Vec<EquippedSlot>,
    pub backpack: Vec<BackpackSection>,
    pub potions: Vec<PotionEntry>,
}

/// A single equipped item slot.
#[derive(serde::Serialize)]
pub struct EquippedSlot {
    pub slot_label: &'static str,
    pub item: Option<ItemSummary>,
}

/// An item summary for display.
#[derive(serde::Serialize, Clone)]
pub struct ItemSummary {
    pub id: i32,
    pub name: String,
    pub power: i32,
    pub durability: String,
    pub agility_mod: String,
    pub speed_mod: String,
    pub amount: i32,
    pub cost: i64,
    pub repair_cost: i64,
    pub needs_repair: bool,
    pub can_equip: bool,
    pub can_sell: bool,
}

/// A section of backpack items grouped by category.
#[derive(serde::Serialize)]
pub struct BackpackSection {
    pub category: &'static str,
    pub items: Vec<ItemSummary>,
}

/// A potion entry for display.
#[derive(serde::Serialize)]
pub struct PotionEntry {
    pub id: i32,
    pub name: String,
    pub power: i32,
    pub efect: String,
    pub amount: i32,
    pub cost: i64,
}

/// Form for item actions (equip, unequip, sell, repair).
#[derive(serde::Deserialize)]
pub struct ItemActionForm {
    pub item_id: Option<i32>,
}

// =========================================================================
// GET /equipment — main inventory page
// =========================================================================

#[allow(clippy::too_many_lines)]
pub async fn equipment_show(
    State(app): State<AppState>,
    Extension(ctx): Extension<RequestContext>,
) -> Response {
    let Some(ref user) = ctx.session_user else {
        return crate::page::redirect("/login");
    };

    #[allow(clippy::cast_possible_truncation)]
    let player_id = user.id as i32;

    // Load all equipment owned by the player
    let all_items =
        match vallheru_data::queries::item::find_equipment_by_owner(&app.pool, player_id).await {
            Ok(items) => items,
            Err(e) => {
                tracing::error!(error = %e, "equipment: failed to load items");
                return server_error();
            }
        };

    // Load potions
    let potions =
        match vallheru_data::queries::item::find_potions_by_owner(&app.pool, player_id).await {
            Ok(p) => p
                .into_iter()
                .map(|p| PotionEntry {
                    id: p.id,
                    name: p.name,
                    power: p.power,
                    efect: p.efect,
                    amount: p.amount,
                    cost: p.cost,
                })
                .collect(),
            Err(e) => {
                tracing::error!(error = %e, "equipment: failed to load potions");
                return server_error();
            }
        };

    // Partition into equipped and backpack
    let mut equipped_items: Vec<_> = all_items.iter().filter(|i| i.status == "E").collect();
    let backpack_items: Vec<_> = all_items.iter().filter(|i| i.status == "U").collect();

    // Sort equipped items by type for consistent slot order
    equipped_items.sort_by_key(|i| slot_order(&i.equipment_type));

    // Build equipped slots
    let slot_types: &[(&str, &str)] = &[
        ("W", "Broń"),
        ("B", "Łuk"),
        ("R", "Strzały"),
        ("H", "Hełm"),
        ("A", "Zbroja"),
        ("C", "Szata maga"),
        ("S", "Tarcza"),
        ("L", "Nogawice"),
        ("T", "Różdżka"),
        ("I", "Pierścień"),
        ("E", "Żywioł"),
    ];

    let equipped: Vec<EquippedSlot> = slot_types
        .iter()
        .map(|(type_code, label)| {
            let item = equipped_items
                .iter()
                .find(|i| i.equipment_type == *type_code)
                .map(|i| to_item_summary(i, false));
            EquippedSlot {
                slot_label: label,
                item,
            }
        })
        .collect();

    // Build backpack sections
    let backpack_categories: &[(&str, &str, bool)] = &[
        ("W", "Bronie", true),
        ("A", "Zbroje", true),
        ("H", "Hełmy", true),
        ("L", "Nogawice", true),
        ("S", "Tarcze", true),
        ("B", "Łuki", true),
        ("R", "Strzały", true),
        ("C", "Szaty maga", true),
        ("T", "Różdżki", true),
        ("I", "Pierścienie", true),
        ("E", "Żywioły", true),
        ("Q", "Przedmioty z questów", false),
        ("O", "Inne", false),
        ("P", "Plany", false),
    ];

    let backpack: Vec<BackpackSection> = backpack_categories
        .iter()
        .filter_map(|(type_code, label, can_equip)| {
            let items: Vec<ItemSummary> = backpack_items
                .iter()
                .filter(|i| i.equipment_type == *type_code)
                .map(|i| to_item_summary(i, *can_equip))
                .collect();
            if items.is_empty() {
                None
            } else {
                Some(BackpackSection {
                    category: label,
                    items,
                })
            }
        })
        .collect();

    let meta = PageMeta::titled("Ekwipunek").with_back_link("/city", "Wróć do miasta");
    let base = app.templates.build_context(&ctx, &meta);

    let view = EquipmentView {
        base,
        equipped,
        backpack,
        potions,
    };
    app.templates.render_value("equipment.html", &view)
}

// =========================================================================
// POST /equipment/equip — equip an item
// =========================================================================

pub async fn equip_action(
    State(app): State<AppState>,
    Extension(ctx): Extension<RequestContext>,
    Form(form): Form<ItemActionForm>,
) -> Response {
    let Some(ref user) = ctx.session_user else {
        return crate::page::redirect("/login");
    };

    #[allow(clippy::cast_possible_truncation)]
    let player_id = user.id as i32;

    let item_id = match form.item_id {
        Some(id) if id > 0 => id,
        _ => return error_page(&app, &ctx, "Nie podano przedmiotu."),
    };

    match vallheru_data::queries::item::equip_item(&app.pool, item_id, player_id).await {
        Ok(true) => {
            tracing::info!(player_id, item_id, "item equipped");
            crate::page::redirect_after_post("/equipment")
        }
        Ok(false) => error_page(&app, &ctx, "Nie można założyć tego przedmiotu."),
        Err(e) => {
            tracing::error!(error = %e, "equip_item failed");
            server_error()
        }
    }
}

// =========================================================================
// POST /equipment/unequip — unequip an item
// =========================================================================

pub async fn unequip_action(
    State(app): State<AppState>,
    Extension(ctx): Extension<RequestContext>,
    Form(form): Form<ItemActionForm>,
) -> Response {
    let Some(ref user) = ctx.session_user else {
        return crate::page::redirect("/login");
    };

    #[allow(clippy::cast_possible_truncation)]
    let player_id = user.id as i32;

    let item_id = match form.item_id {
        Some(id) if id > 0 => id,
        _ => return error_page(&app, &ctx, "Nie podano przedmiotu."),
    };

    match vallheru_data::queries::item::unequip_item(&app.pool, item_id, player_id).await {
        Ok(true) => {
            tracing::info!(player_id, item_id, "item unequipped");
            crate::page::redirect_after_post("/equipment")
        }
        Ok(false) => error_page(&app, &ctx, "Nie można zdjąć tego przedmiotu."),
        Err(e) => {
            tracing::error!(error = %e, "unequip_item failed");
            server_error()
        }
    }
}

// =========================================================================
// POST /equipment/sell — sell an item
// =========================================================================

pub async fn sell_action(
    State(app): State<AppState>,
    Extension(ctx): Extension<RequestContext>,
    Form(form): Form<ItemActionForm>,
) -> Response {
    let Some(ref user) = ctx.session_user else {
        return crate::page::redirect("/login");
    };

    #[allow(clippy::cast_possible_truncation)]
    let player_id = user.id as i32;

    let item_id = match form.item_id {
        Some(id) if id > 0 => id,
        _ => return error_page(&app, &ctx, "Nie podano przedmiotu."),
    };

    match vallheru_data::queries::item::sell_one_item(&app.pool, item_id, player_id).await {
        Ok(Some(gold)) => {
            tracing::info!(player_id, item_id, gold, "item sold");
            let meta = PageMeta::titled("Ekwipunek")
                .with_back_link("/equipment", "Wróć do ekwipunku")
                .with_flash(Flash::success(format!("Sprzedano za {gold} sztuk złota.")));
            let base = app.templates.build_context(&ctx, &meta);
            app.templates.render("error.html", &base)
        }
        Ok(None) => error_page(&app, &ctx, "Nie można sprzedać tego przedmiotu."),
        Err(e) => {
            tracing::error!(error = %e, "sell_item failed");
            server_error()
        }
    }
}

// =========================================================================
// POST /equipment/repair — repair an item
// =========================================================================

pub async fn repair_action(
    State(app): State<AppState>,
    Extension(ctx): Extension<RequestContext>,
    Form(form): Form<ItemActionForm>,
) -> Response {
    let Some(ref user) = ctx.session_user else {
        return crate::page::redirect("/login");
    };

    #[allow(clippy::cast_possible_truncation)]
    let player_id = user.id as i32;

    let item_id = match form.item_id {
        Some(id) if id > 0 => id,
        _ => return error_page(&app, &ctx, "Nie podano przedmiotu."),
    };

    match vallheru_data::queries::item::repair_item(&app.pool, item_id, player_id).await {
        Ok(Some(cost)) => {
            tracing::info!(player_id, item_id, cost, "item repaired");
            let meta = PageMeta::titled("Ekwipunek")
                .with_back_link("/equipment", "Wróć do ekwipunku")
                .with_flash(Flash::success(format!("Naprawiono za {cost} sztuk złota.")));
            let base = app.templates.build_context(&ctx, &meta);
            app.templates.render("error.html", &base)
        }
        Ok(None) => error_page(
            &app,
            &ctx,
            "Nie można naprawić tego przedmiotu (brak złota lub nie wymaga naprawy).",
        ),
        Err(e) => {
            tracing::error!(error = %e, "repair_item failed");
            server_error()
        }
    }
}

// =========================================================================
// Helpers
// =========================================================================

fn to_item_summary(
    row: &vallheru_data::queries::item::EquipmentRow,
    can_equip: bool,
) -> ItemSummary {
    let durability = match row.equipment_type.as_str() {
        "R" => format!("{} strzał", row.wt),
        "O" | "Q" | "I" | "P" => String::new(),
        _ => format!("{}/{}", row.wt, row.maxwt),
    };

    let agility_mod = match row.zr.cmp(&0) {
        std::cmp::Ordering::Less => format!("+{} zr", -row.zr),
        std::cmp::Ordering::Greater => format!("-{} zr", row.zr),
        std::cmp::Ordering::Equal => String::new(),
    };

    let speed_mod = if row.szyb > 0 && row.equipment_type != "A" {
        format!("+{} szyb", row.szyb)
    } else {
        String::new()
    };

    let needs_repair =
        !matches!(row.equipment_type.as_str(), "R" | "I" | "O" | "Q" | "P") && row.wt < row.maxwt;

    let repair_cost = if needs_repair && row.maxwt > 0 {
        let ratio = 1.0 - (f64::from(row.wt) / f64::from(row.maxwt));
        #[allow(clippy::cast_possible_truncation)]
        let cost = (f64::from(row.repair) * ratio).ceil() as i64;
        cost
    } else {
        0
    };

    // Can sell if it's in backpack (already filtered) and has full durability or is arrows/ring
    let can_sell = matches!(row.status.as_str(), "U");

    ItemSummary {
        id: row.id,
        name: format_item_name(row),
        power: row.power,
        durability,
        agility_mod,
        speed_mod,
        amount: row.amount,
        cost: row.cost,
        repair_cost,
        needs_repair,
        can_equip,
        can_sell,
    }
}

/// Format an item name with poison and element annotations.
fn format_item_name(row: &vallheru_data::queries::item::EquipmentRow) -> String {
    let mut name = row.name.clone();

    match row.ptype.as_str() {
        "D" => {
            let _ = write!(name, " (Dynallca +{})", row.poison);
        }
        "N" => {
            let _ = write!(name, " (Nutari +{})", row.poison);
        }
        "I" => {
            let _ = write!(name, " (Illani +{})", row.poison);
        }
        _ => {}
    }

    // Element annotation
    match row.magic.as_str() {
        "E" => name.push_str(" (Żywioł: Ziemia)"),
        "W" => name.push_str(" (Żywioł: Woda)"),
        "F" => name.push_str(" (Żywioł: Ogień)"),
        "A" => name.push_str(" (Żywioł: Powietrze)"),
        _ => {}
    }

    name
}

/// Order for equipped items display.
fn slot_order(equipment_type: &str) -> i32 {
    match equipment_type {
        "W" => 0,
        "B" => 1,
        "R" => 2,
        "H" => 3,
        "A" => 4,
        "C" => 5,
        "S" => 6,
        "L" => 7,
        "T" => 8,
        "I" => 9,
        "E" => 10,
        _ => 99,
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
