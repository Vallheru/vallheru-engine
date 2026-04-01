//! Magic Tower handler — buy spells, staffs, and mage clothing.
//!
//! Ported from `wieza.php`. The magic tower is a city shop where players
//! can buy battle/defense/utility spells and mage equipment (wands, capes).

use axum::{Extension, extract::Path, extract::State, response::IntoResponse, response::Response};

use vallheru_data::queries::{item as item_q, player as player_q};

use crate::middleware::context::RequestContext;
use crate::page::{Flash, FlashKind, PageMeta};
use crate::state::AppState;

// ---------------------------------------------------------------------------
// View models
// ---------------------------------------------------------------------------

/// Main magic tower view — shows category menu and optional listing.
#[derive(serde::Serialize)]
pub struct MagicTowerView {
    #[serde(flatten)]
    pub base: crate::render::RenderContext,
    pub info_text: &'static str,
    /// Currently selected category code (empty = menu only).
    pub category: String,
    /// True if the listing shows spells (grouped by element).
    pub is_spell_listing: bool,
    /// Spell groups by element (only when `is_spell_listing` = true).
    pub spell_groups: Vec<SpellGroup>,
    /// Mage item listing (when `is_spell_listing` = false and category is set).
    pub items: Vec<MageItemEntry>,
    /// Column header for the effect/power column.
    pub power_label: String,
}

/// A group of spells for one element.
#[derive(serde::Serialize)]
pub struct SpellGroup {
    pub element_name: String,
    pub spells: Vec<SpellEntry>,
}

/// A single spell available for purchase.
#[derive(serde::Serialize)]
pub struct SpellEntry {
    pub id: i32,
    pub name: String,
    pub effect: String,
    pub price: i64,
    pub level: i32,
    /// Whether the player already owns this spell (same name+element).
    pub owned: bool,
}

/// A single mage item available for purchase.
#[derive(serde::Serialize)]
pub struct MageItemEntry {
    pub id: i32,
    pub name: String,
    pub power_text: String,
    pub cost: i64,
    pub min_level: i32,
}

/// Form data for buying.
#[derive(serde::Deserialize)]
pub struct BuyParams {
    /// "S" for spell, "I" for mage item.
    pub buy_type: String,
}

// ---------------------------------------------------------------------------
// Constants
// ---------------------------------------------------------------------------

const INFO_ALTARA: &str = "Widzisz przed sobą ogromną wieżę stojącą na samym \
    środku miasta. Wchodząc wyczuwasz w niej prawdziwą potęgę jej mistycznej \
    magii. Ściany owijają purpurowe migoczące blaski. Od tego pięknego widoku \
    odrywa cię melodyjny głos <i>Witaj, wiem czego chcesz, chodź za mną.</i>. \
    Idąc za elfim magiem dostrzegasz wiele innych postaci w pięknych jedwabnych \
    szatach praktykujących swoją magię. Dochodzisz do ogromnej biblioteki. \
    <i>Które zaklęcie cię interesuje? Jeśli masz pieniądze mogę wprowadzić cię \
    w wyższe arkana naszej magii, lecz te są dostępne tylko dla magów o wielkich \
    umiejętnościach. Chodź, wybierz drogę magii którą chcesz podążać.</i> \
    Czarów obronnych czy bojowych mogą używać tylko magowie.";

const INFO_ARDULITH: &str = "Widzisz przed sobą ogromną wieżę, wyróżniającą się \
    spośród całego sadu. Wchodząc wyczuwasz w niej prawdziwą potęgę jej \
    mistycznej magii. Ściany owijają purpurowe migoczące blaski. Od tego \
    pięknego widoku odrywa cię melodyjny głos: <br /><i>- Witaj, wiem czego \
    chcesz, chodź za mną...</i><br /> Idąc za elfim magiem dostrzegasz wiele \
    innych postaci w pięknych jedwabnych szatach praktykujących swoją magię. \
    Dochodzisz do ogromnej biblioteki. <br /><i>- Które zaklęcie cię interesuje? \
    A może potrzebujesz nowej różdżki lub szaty? Wszystko jest do Twojej \
    dyspozycji, o ile posiadasz odpowiednie pieniądze i odpowiednie doświadczenie \
    w posługiwaniu się magią.</i>";

/// Allowed category codes.
const VALID_CATEGORIES: &[&str] = &["T", "C", "B", "O", "U"];

/// Map DB element keys to Polish display names.
fn element_display(element: &str) -> &'static str {
    match element {
        "earth" => "Ziemia",
        "water" => "Woda",
        "wind" => "Powietrze",
        "fire" => "Ogień",
        _ => "Nieznany",
    }
}

/// Spell effect text based on type.
fn spell_effect_text(spell: &item_q::SpellRow) -> String {
    match spell.typ.as_str() {
        "B" => format!("{} x Int obrażeń", spell.obr),
        "O" => format!("{} x SW obrony", spell.obr),
        "U" => {
            if spell.nazwa == "Ulepszenie przedmiotu" {
                "Zwiększa siłę przedmiotu".to_owned()
            } else if spell.nazwa == "Utwardzenie przedmiotu" {
                "Zwiększa wytrzymałość przedmiotu".to_owned()
            } else if spell.nazwa == "Umagicznienie przedmiotu" {
                "Zwiększa premię szybkości lub zręczności przedmiotu".to_owned()
            } else {
                format!("{}", spell.obr)
            }
        }
        _ => String::new(),
    }
}

// ---------------------------------------------------------------------------
// GET /tower/magic — magic tower main page
// ---------------------------------------------------------------------------

pub async fn magic_tower_show(
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
        return error_page(&app, &ctx, "Nie znajdujesz się w mieście.");
    }

    let info = if player.location == "Ardulith" {
        INFO_ARDULITH
    } else {
        INFO_ALTARA
    };

    let meta = PageMeta::titled("Magiczna wieża").with_back_link("/city", "Wróć do miasta");
    let base = app.templates.build_context(&ctx, &meta);

    let view = MagicTowerView {
        base,
        info_text: info,
        category: String::new(),
        is_spell_listing: false,
        spell_groups: Vec::new(),
        items: Vec::new(),
        power_label: String::new(),
    };

    app.templates.render_value("magic_tower.html", &view)
}

// ---------------------------------------------------------------------------
// GET /tower/magic/{category} — browse a category
// ---------------------------------------------------------------------------

#[allow(clippy::too_many_lines)]
pub async fn magic_tower_category(
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

    if player.location != "Altara" && player.location != "Ardulith" {
        return error_page(&app, &ctx, "Nie znajdujesz się w mieście.");
    }

    if !VALID_CATEGORIES.contains(&category.as_str()) {
        return error_page(&app, &ctx, "Zapomnij o tym!");
    }

    let info = if player.location == "Ardulith" {
        INFO_ARDULITH
    } else {
        INFO_ALTARA
    };

    let skills = match player_q::load_skills(&app.pool, player_id).await {
        Ok(s) => s,
        Err(e) => {
            tracing::error!(error = %e, "magic_tower: failed to load skills");
            return server_error();
        }
    };

    let magic_level = skills
        .iter()
        .find(|s| s.skill_key == "magic")
        .map_or(1, |s| s.level);

    let is_spell = matches!(category.as_str(), "B" | "O" | "U");

    let mut spell_groups = Vec::new();
    let mut items = Vec::new();
    let power_label;

    if is_spell {
        power_label = match category.as_str() {
            "B" => "Obrażenia".to_owned(),
            "O" => "Obrona".to_owned(),
            "U" => "Efekt".to_owned(),
            _ => String::new(),
        };

        let catalog = match item_q::find_spell_shop_by_type(&app.pool, &category, magic_level).await
        {
            Ok(rows) => rows,
            Err(e) => {
                tracing::error!(error = %e, "magic_tower: failed to load spell catalog");
                return server_error();
            }
        };

        // Load player's owned spells to mark duplicates.
        let owned = match item_q::find_spells_by_owner(&app.pool, player_id).await {
            Ok(rows) => rows,
            Err(e) => {
                tracing::error!(error = %e, "magic_tower: failed to load owned spells");
                return server_error();
            }
        };

        // Group spells by element.
        let element_order = ["earth", "water", "wind", "fire"];
        for elem_key in &element_order {
            let spells_for_elem: Vec<SpellEntry> = catalog
                .iter()
                .filter(|s| s.element == *elem_key)
                .map(|s| {
                    let already_owned = owned
                        .iter()
                        .any(|o| o.nazwa == s.nazwa && (s.typ != "U" || o.element == s.element));
                    SpellEntry {
                        id: s.id,
                        name: s.nazwa.clone(),
                        effect: spell_effect_text(s),
                        price: s.cena,
                        level: s.poziom,
                        owned: already_owned,
                    }
                })
                .filter(|e| !e.owned)
                .collect();

            if !spells_for_elem.is_empty() {
                spell_groups.push(SpellGroup {
                    element_name: element_display(elem_key).to_owned(),
                    spells: spells_for_elem,
                });
            }
        }
    } else {
        // Mage items (T=wands, C=clothing).
        power_label = "Siła".to_owned();

        let catalog = match item_q::find_mage_items_by_type(&app.pool, &category).await {
            Ok(rows) => rows,
            Err(e) => {
                tracing::error!(error = %e, "magic_tower: failed to load mage items");
                return server_error();
            }
        };

        items = catalog
            .into_iter()
            .map(|r| {
                let power_text = if r.item_type == "T" {
                    "Zwiększa siłę czarów".to_owned()
                } else {
                    format!("+{} % many", r.power)
                };
                MageItemEntry {
                    id: r.id,
                    name: r.name,
                    power_text,
                    cost: r.cost,
                    min_level: r.minlev,
                }
            })
            .collect();
    }

    let meta = PageMeta::titled("Magiczna wieża").with_back_link("/city", "Wróć do miasta");
    let base = app.templates.build_context(&ctx, &meta);

    let view = MagicTowerView {
        base,
        info_text: info,
        category,
        is_spell_listing: is_spell,
        spell_groups,
        items,
        power_label,
    };

    app.templates.render_value("magic_tower.html", &view)
}

// ---------------------------------------------------------------------------
// POST /tower/magic/buy/spell/{id} — buy a spell
// ---------------------------------------------------------------------------

pub async fn buy_spell(
    State(app): State<AppState>,
    Extension(ctx): Extension<RequestContext>,
    Path(spell_id): Path<i32>,
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
        return error_page(&app, &ctx, "Nie znajdujesz się w mieście.");
    }

    // Look up the catalog spell (gracz = 0, status = 'S').
    let catalog_spell = match item_q::find_spell_by_id(&app.pool, spell_id).await {
        Ok(Some(s)) if s.gracz == 0 && s.status == "S" => s,
        Ok(_) => return error_page(&app, &ctx, "Nie ma takiego czaru!"),
        Err(e) => {
            tracing::error!(error = %e, "buy_spell: db error");
            return server_error();
        }
    };

    // Check level requirement.
    let skills = match player_q::load_skills(&app.pool, player_id).await {
        Ok(s) => s,
        Err(e) => {
            tracing::error!(error = %e, "buy_spell: failed to load skills");
            return server_error();
        }
    };
    let magic_level = skills
        .iter()
        .find(|s| s.skill_key == "magic")
        .map_or(1, |s| s.level);

    if catalog_spell.poziom > magic_level {
        return error_page(&app, &ctx, "Twój poziom jest za niski dla tej rzeczy!");
    }

    // Mage-only restriction for battle (B) and defense (O) spells.
    if (catalog_spell.typ == "B" || catalog_spell.typ == "O") && player.class != "Mag" {
        return error_page(&app, &ctx, "Tylko mag może używać tego typu czarów!");
    }

    // Check gold.
    if catalog_spell.cena > i64::from(player.credits) {
        return error_page(&app, &ctx, "Nie stać cię!");
    }

    // Check ownership — player cannot buy duplicate name+element
    // (for B/O: same name; for U: same name AND same element).
    let owned = match item_q::find_spells_by_owner(&app.pool, player_id).await {
        Ok(rows) => rows,
        Err(e) => {
            tracing::error!(error = %e, "buy_spell: failed to load owned spells");
            return server_error();
        }
    };

    let already_has = owned.iter().any(|o| {
        o.nazwa == catalog_spell.nazwa
            && (catalog_spell.typ != "U" || o.element == catalog_spell.element)
    });

    if already_has {
        return error_page(&app, &ctx, "Masz już taki czar!");
    }

    // Execute purchase.
    if let Err(e) = item_q::buy_spell(&app.pool, player_id, &catalog_spell).await {
        tracing::error!(error = %e, "buy_spell: purchase failed");
        return server_error();
    }

    tracing::info!(player_id, spell_id, spell = %catalog_spell.nazwa, "spell purchased");
    let msg = format!(
        "Zapłaciłeś <b>{}</b> sztuk złota, i kupiłeś za to nowy czar <b>{}</b>.",
        catalog_spell.cena, catalog_spell.nazwa
    );
    flash_and_redirect(&app, &ctx, &msg, "/tower/magic")
}

// ---------------------------------------------------------------------------
// POST /tower/magic/buy/item/{id} — buy a mage item
// ---------------------------------------------------------------------------

pub async fn buy_mage_item(
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

    if player.location != "Altara" && player.location != "Ardulith" {
        return error_page(&app, &ctx, "Nie znajdujesz się w mieście.");
    }

    let item = match item_q::find_mage_item_by_id(&app.pool, item_id).await {
        Ok(Some(i)) => i,
        Ok(None) => return error_page(&app, &ctx, "Nie ma takiego przedmiotu!"),
        Err(e) => {
            tracing::error!(error = %e, "buy_mage_item: db error");
            return server_error();
        }
    };

    // Mage-only check.
    if player.class != "Mag" {
        return error_page(&app, &ctx, "Tylko mag może używać tych przedmiotów!");
    }

    // Level check.
    let skills = match player_q::load_skills(&app.pool, player_id).await {
        Ok(s) => s,
        Err(e) => {
            tracing::error!(error = %e, "buy_mage_item: failed to load skills");
            return server_error();
        }
    };
    let magic_level = skills
        .iter()
        .find(|s| s.skill_key == "magic")
        .map_or(1, |s| s.level);

    if item.minlev > magic_level {
        return error_page(&app, &ctx, "Twój poziom jest za niski dla tej rzeczy!");
    }

    // Gold check.
    if item.cost > i64::from(player.credits) {
        return error_page(&app, &ctx, "Nie stać cię!");
    }

    // Execute purchase.
    if let Err(e) = item_q::buy_mage_item(&app.pool, player_id, &item).await {
        tracing::error!(error = %e, "buy_mage_item: purchase failed");
        return server_error();
    }

    tracing::info!(player_id, item_id, item_name = %item.name, "mage item purchased");
    let msg = format!(
        "Zapłaciłeś <b>{}</b> sztuk złota, i kupiłeś za to nowy przedmiot <b>{}</b>.",
        item.cost, item.name
    );
    flash_and_redirect(&app, &ctx, &msg, "/tower/magic")
}

// ---------------------------------------------------------------------------
// Helpers
// ---------------------------------------------------------------------------

async fn load_player(app: &AppState, player_id: i32) -> Result<player_q::PlayerRow, Response> {
    match player_q::find_player_by_id(&app.pool, player_id).await {
        Ok(Some(row)) => Ok(row),
        Ok(None) => Err(crate::page::redirect("/login")),
        Err(e) => {
            tracing::error!(error = %e, "magic_tower: load player failed");
            Err(server_error())
        }
    }
}

fn error_page(state: &AppState, ctx: &RequestContext, message: &str) -> Response {
    let meta = PageMeta::titled("Magiczna wieża").with_flash(Flash {
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
    _target: &str,
) -> Response {
    let meta = PageMeta::titled("Magiczna wieża").with_flash(Flash::success(message.to_owned()));
    let base = state.templates.build_context(ctx, &meta);
    state.templates.render("error.html", &base)
}

fn server_error() -> Response {
    (
        axum::http::StatusCode::INTERNAL_SERVER_ERROR,
        "Wewnętrzny błąd serwera.",
    )
        .into_response()
}
