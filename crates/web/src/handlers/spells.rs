//! Spell book handler.
//!
//! Ported from `czary.php`. Shows owned spells grouped by element and type,
//! allows activation/deactivation of battle and defense spells.
//! Item enchantment (utility spells) is deferred to a future task.

use axum::{Extension, Form, extract::State, response::IntoResponse, response::Response};

use crate::middleware::context::RequestContext;
use crate::page::{Flash, FlashKind, PageMeta};
use crate::state::AppState;

// =========================================================================
// View models
// =========================================================================

/// Spell book page view.
#[derive(serde::Serialize)]
pub struct SpellBookView {
    #[serde(flatten)]
    pub base: crate::render::RenderContext,
    /// Currently active battle spell (if any).
    pub active_battle: Option<ActiveSpell>,
    /// Currently active defense spell (if any).
    pub active_defense: Option<ActiveSpell>,
    /// Battle spells grouped by element.
    pub battle_spells: Vec<SpellGroup>,
    /// Defense spells grouped by element.
    pub defense_spells: Vec<SpellGroup>,
    /// Utility (enchantment) spells grouped by element.
    pub utility_spells: Vec<SpellGroup>,
    /// Whether the player has any spells at all.
    pub has_spells: bool,
}

/// Currently active spell summary.
#[derive(serde::Serialize)]
pub struct ActiveSpell {
    pub id: i32,
    pub name: String,
    pub multiplier: f64,
}

/// A group of spells sharing the same element.
#[derive(serde::Serialize)]
pub struct SpellGroup {
    pub element: &'static str,
    pub spells: Vec<SpellEntry>,
}

/// A single spell entry.
#[derive(serde::Serialize)]
pub struct SpellEntry {
    pub id: i32,
    pub name: String,
    pub multiplier: f64,
    pub level: i32,
}

/// Form for spell activate/deactivate.
#[derive(serde::Deserialize)]
pub struct SpellActionForm {
    pub spell_id: Option<i32>,
}

// =========================================================================
// GET /spellbook — spell book page
// =========================================================================

pub async fn spellbook_show(
    State(app): State<AppState>,
    Extension(ctx): Extension<RequestContext>,
) -> Response {
    let Some(ref user) = ctx.session_user else {
        return crate::page::redirect("/login");
    };

    #[allow(clippy::cast_possible_truncation)]
    let player_id = user.id as i32;

    let all_spells =
        match vallheru_data::queries::item::find_spells_by_owner(&app.pool, player_id).await {
            Ok(s) => s,
            Err(e) => {
                tracing::error!(error = %e, "spellbook_show: failed to load spells");
                return server_error();
            }
        };

    // Find active battle and defense spells
    let active_battle = all_spells
        .iter()
        .find(|s| s.typ == "B" && s.status == "E")
        .map(|s| ActiveSpell {
            id: s.id,
            name: s.nazwa.clone(),
            multiplier: s.obr,
        });

    let active_defense = all_spells
        .iter()
        .find(|s| s.typ == "O" && s.status == "E")
        .map(|s| ActiveSpell {
            id: s.id,
            name: s.nazwa.clone(),
            multiplier: s.obr,
        });

    // Group inactive spells by type and element
    let battle_spells = group_spells(&all_spells, "B");
    let defense_spells = group_spells(&all_spells, "O");
    let utility_spells = group_spells(&all_spells, "U");

    let has_spells = !all_spells.is_empty();

    let meta = PageMeta::titled("Księga czarów").with_back_link("/city", "Wróć do miasta");
    let base = app.templates.build_context(&ctx, &meta);
    let view = SpellBookView {
        base,
        active_battle,
        active_defense,
        battle_spells,
        defense_spells,
        utility_spells,
        has_spells,
    };
    app.templates.render_value("spellbook.html", &view)
}

// =========================================================================
// POST /spellbook/activate — activate a spell
// =========================================================================

pub async fn spell_activate(
    State(app): State<AppState>,
    Extension(ctx): Extension<RequestContext>,
    Form(form): Form<SpellActionForm>,
) -> Response {
    let Some(ref user) = ctx.session_user else {
        return crate::page::redirect("/login");
    };

    #[allow(clippy::cast_possible_truncation)]
    let player_id = user.id as i32;

    let spell_id = match form.spell_id {
        Some(id) if id > 0 => id,
        _ => return error_page(&app, &ctx, "Nie podano czaru."),
    };

    match vallheru_data::queries::item::activate_spell(&app.pool, spell_id, player_id).await {
        Ok(true) => {
            tracing::info!(player_id, spell_id, "spell activated");
            crate::page::redirect_after_post("/spellbook")
        }
        Ok(false) => error_page(&app, &ctx, "Nie można aktywować tego czaru."),
        Err(e) => {
            tracing::error!(error = %e, "spell_activate failed");
            server_error()
        }
    }
}

// =========================================================================
// POST /spellbook/deactivate — deactivate a spell
// =========================================================================

pub async fn spell_deactivate(
    State(app): State<AppState>,
    Extension(ctx): Extension<RequestContext>,
    Form(form): Form<SpellActionForm>,
) -> Response {
    let Some(ref user) = ctx.session_user else {
        return crate::page::redirect("/login");
    };

    #[allow(clippy::cast_possible_truncation)]
    let player_id = user.id as i32;

    let spell_id = match form.spell_id {
        Some(id) if id > 0 => id,
        _ => return error_page(&app, &ctx, "Nie podano czaru."),
    };

    match vallheru_data::queries::item::deactivate_spell(&app.pool, spell_id, player_id).await {
        Ok(true) => {
            tracing::info!(player_id, spell_id, "spell deactivated");
            crate::page::redirect_after_post("/spellbook")
        }
        Ok(false) => error_page(&app, &ctx, "Nie można dezaktywować tego czaru."),
        Err(e) => {
            tracing::error!(error = %e, "spell_deactivate failed");
            server_error()
        }
    }
}

// =========================================================================
// Helpers
// =========================================================================

fn element_label(code: &str) -> &'static str {
    match code {
        "earth" => "Ziemia",
        "water" => "Woda",
        "fire" => "Ogień",
        "wind" => "Powietrze",
        _ => "Inne",
    }
}

fn group_spells(all: &[vallheru_data::queries::item::SpellRow], typ: &str) -> Vec<SpellGroup> {
    let elements = ["earth", "water", "fire", "wind"];
    let mut groups = Vec::new();

    for elem in &elements {
        let spells: Vec<SpellEntry> = all
            .iter()
            .filter(|s| s.typ == typ && s.status == "U" && s.element == *elem)
            .map(|s| SpellEntry {
                id: s.id,
                name: s.nazwa.clone(),
                multiplier: s.obr,
                level: s.poziom,
            })
            .collect();

        if !spells.is_empty() {
            groups.push(SpellGroup {
                element: element_label(elem),
                spells,
            });
        }
    }

    groups
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
