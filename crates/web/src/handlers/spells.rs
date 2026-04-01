//! Spell book handler.
//!
//! Ported from `czary.php`. Shows owned spells grouped by element and type,
//! allows activation/deactivation of battle and defense spells, and
//! item enchantment via utility spells.

use axum::{
    Extension, Form, extract::Path, extract::State, response::IntoResponse, response::Response,
};

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

// =========================================================================
// Enchantment view models
// =========================================================================

/// Item selection view for enchantment.
#[derive(serde::Serialize)]
pub struct EnchantSelectView {
    #[serde(flatten)]
    pub base: crate::render::RenderContext,
    pub spell_id: i32,
    pub spell_name: String,
    pub items: Vec<EnchantItemEntry>,
}

/// An enchantable item entry for the template.
#[derive(serde::Serialize)]
pub struct EnchantItemEntry {
    pub id: i32,
    pub name: String,
    pub power: i32,
    pub equipment_type: String,
    pub amount: i32,
    pub wt: i32,
    pub maxwt: i32,
    pub szyb: i32,
    pub zr: i32,
    /// Pre-formatted stats string for display.
    pub stats_display: String,
}

/// Form for enchantment action.
#[derive(serde::Deserialize)]
pub struct EnchantForm {
    pub spell_id: i32,
    pub item_id: i32,
}

// =========================================================================
// GET /spellbook/enchant/:spell_id — show items to enchant
// =========================================================================

pub async fn enchant_show(
    State(app): State<AppState>,
    Extension(ctx): Extension<RequestContext>,
    Path(spell_id): Path<i32>,
) -> Response {
    let Some(ref user) = ctx.session_user else {
        return crate::page::redirect("/login");
    };

    #[allow(clippy::cast_possible_truncation)]
    let player_id = user.id as i32;

    let spell = match vallheru_data::queries::item::find_spell_by_id(&app.pool, spell_id).await {
        Ok(Some(s)) => s,
        Ok(None) => return error_page(&app, &ctx, "Nie posiadasz takiego czaru!"),
        Err(e) => {
            tracing::error!(error = %e, "enchant_show: load spell failed");
            return server_error();
        }
    };

    if spell.gracz != player_id {
        return error_page(&app, &ctx, "To nie twój czar!");
    }

    let Some(kind) = vallheru_domain::enchantment::EnchantKind::from_spell_name(&spell.nazwa)
    else {
        return error_page(&app, &ctx, "To nie jest czar ulepszający.");
    };

    let excluded = kind.excluded_type_codes();
    let items =
        match vallheru_data::queries::item::find_enchantable_items(&app.pool, player_id, excluded)
            .await
        {
            Ok(items) => items,
            Err(e) => {
                tracing::error!(error = %e, "enchant_show: load items failed");
                return server_error();
            }
        };

    let entries: Vec<EnchantItemEntry> = items
        .into_iter()
        .map(|item| {
            let amount_display = if item.equipment_type == "R" {
                item.wt
            } else {
                item.amount
            };
            let stats_display = format_item_stats(&item);
            EnchantItemEntry {
                id: item.id,
                name: item.name,
                power: item.power,
                equipment_type: item.equipment_type,
                amount: amount_display,
                wt: item.wt,
                maxwt: item.maxwt,
                szyb: item.szyb,
                zr: item.zr,
                stats_display,
            }
        })
        .collect();

    let meta = PageMeta::titled("Rzuć czar").with_back_link("/spellbook", "Wróć do księgi");
    let base = app.templates.build_context(&ctx, &meta);
    let view = EnchantSelectView {
        base,
        spell_id,
        spell_name: spell.nazwa,
        items: entries,
    };
    app.templates.render_value("spellbook_enchant.html", &view)
}

// =========================================================================
// POST /spellbook/enchant — perform enchantment
// =========================================================================

pub async fn enchant_action(
    State(app): State<AppState>,
    Extension(ctx): Extension<RequestContext>,
    Form(form): Form<EnchantForm>,
) -> Response {
    use vallheru_domain::enchantment::{self, EnchantKind};

    let Some(ref user) = ctx.session_user else {
        return crate::page::redirect("/login");
    };

    #[allow(clippy::cast_possible_truncation)]
    let player_id = user.id as i32;

    // Load and validate spell
    let spell = match vallheru_data::queries::item::find_spell_by_id(&app.pool, form.spell_id).await
    {
        Ok(Some(s)) => s,
        Ok(None) => return error_page(&app, &ctx, "Nie posiadasz takiego czaru!"),
        Err(e) => {
            tracing::error!(error = %e, "enchant_action: load spell");
            return server_error();
        }
    };
    if spell.gracz != player_id {
        return error_page(&app, &ctx, "To nie twój czar!");
    }
    let Some(kind) = EnchantKind::from_spell_name(&spell.nazwa) else {
        return error_page(&app, &ctx, "To nie jest czar ulepszający.");
    };

    // Load and validate player
    let player = match vallheru_data::queries::player::find_player_by_id(&app.pool, player_id).await
    {
        Ok(Some(p)) => p,
        Ok(None) => return server_error(),
        Err(e) => {
            tracing::error!(error = %e, "enchant_action: load player");
            return server_error();
        }
    };
    if player.pm < spell.poziom {
        return error_page(&app, &ctx, "Nie masz tyle punktów magii!");
    }
    if player.energy < f64::from(spell.poziom) {
        return error_page(&app, &ctx, "Nie masz tyle energii!");
    }
    if player.class == "Barbarzyńca" {
        return error_page(
            &app,
            &ctx,
            "Nie możesz używać czarów ponieważ jesteś Barbarzyńcą!",
        );
    }

    // Load and validate item
    let item =
        match vallheru_data::queries::item::find_equipment_by_id(&app.pool, form.item_id).await {
            Ok(Some(i)) => i,
            Ok(None) => return error_page(&app, &ctx, "Nie ma takiego przedmiotu!"),
            Err(e) => {
                tracing::error!(error = %e, "enchant_action: load item");
                return server_error();
            }
        };
    if item.owner != player_id {
        return error_page(&app, &ctx, "Ten przedmiot nie należy do ciebie!");
    }
    if item.magic != "N" {
        return error_page(&app, &ctx, "Ten przedmiot jest już umagiczniony!");
    }
    if let Err(msg) = kind.validate_item(&item.equipment_type) {
        return error_page(&app, &ctx, msg);
    }

    let Some(name_prefix) = enchantment::magic_name_prefix(&item.equipment_type) else {
        return error_page(&app, &ctx, "Nie możesz umagiczniać tego przedmiotu!");
    };
    let enchanted_name = format!("{name_prefix}{}", item.name);

    // Look up base stats for bonus cap
    let cap_stat = match lookup_enchant_cap(&app, kind, &item).await {
        Ok(cap) => cap,
        Err(resp) => return resp,
    };

    // Resolve enchant and apply progression
    let magic_code = enchantment::element_to_magic_code(&spell.element);
    resolve_and_persist_enchant(
        &app,
        &ctx,
        player_id,
        &player,
        &spell,
        kind,
        &item,
        &enchanted_name,
        cap_stat,
        magic_code,
    )
    .await
}

/// Look up base item stats and return the appropriate cap for the enchant kind.
async fn lookup_enchant_cap(
    app: &AppState,
    kind: vallheru_domain::enchantment::EnchantKind,
    item: &vallheru_data::queries::item::EquipmentRow,
) -> Result<i32, Response> {
    use vallheru_domain::enchantment::{self, EnchantKind};

    let stem = enchantment::base_item_stem(&item.name);
    let lookup_name = enchantment::base_lookup_name(&stem, &item.equipment_type);
    let base_stats = if enchantment::uses_bows_table(&item.equipment_type) {
        vallheru_data::queries::item::find_base_bow_stats(&app.pool, &lookup_name).await
    } else {
        vallheru_data::queries::item::find_base_equipment_stats(&app.pool, &lookup_name).await
    };
    let base_stats = match base_stats {
        Ok(Some(b)) => b,
        Ok(None) => vallheru_data::queries::item::BaseStatRow {
            power: 1,
            maxwt: 1,
            szyb: 1,
            zr: 1,
        },
        Err(e) => {
            tracing::error!(error = %e, "enchant: load base stats");
            return Err(server_error());
        }
    };
    Ok(match kind {
        EnchantKind::Power => base_stats.power,
        EnchantKind::Durability => base_stats.maxwt,
        EnchantKind::Special => match item.equipment_type.as_str() {
            "W" | "B" => base_stats.szyb,
            "A" | "L" => base_stats.zr,
            _ => 1,
        },
    })
}

/// Resolve enchantment, apply XP, persist changes, and return result page.
#[allow(clippy::too_many_arguments)]
async fn resolve_and_persist_enchant(
    app: &AppState,
    ctx: &RequestContext,
    player_id: i32,
    player: &vallheru_data::queries::player::PlayerRow,
    spell: &vallheru_data::queries::item::SpellRow,
    kind: vallheru_domain::enchantment::EnchantKind,
    item: &vallheru_data::queries::item::EquipmentRow,
    enchanted_name: &str,
    cap_stat: i32,
    magic_code: &str,
) -> Response {
    use vallheru_domain::enchantment::{self, EnchantResult};

    // Load stats, skills, bonuses
    let mut stats = match vallheru_data::queries::player::load_stats(&app.pool, player_id).await {
        Ok(s) => s,
        Err(e) => {
            tracing::error!(error = %e, "enchant: load stats");
            return server_error();
        }
    };
    let mut skills = match vallheru_data::queries::player::load_skills(&app.pool, player_id).await {
        Ok(s) => s,
        Err(e) => {
            tracing::error!(error = %e, "enchant: load skills");
            return server_error();
        }
    };
    let bonuses = match vallheru_data::queries::player::load_bonuses(&app.pool, player_id).await {
        Ok(b) => b,
        Err(e) => {
            tracing::error!(error = %e, "enchant: load bonuses");
            return server_error();
        }
    };

    let magic_skill_level = skills
        .iter()
        .find(|s| s.skill_key == "magic")
        .map_or(0, |s| s.level);
    let enchant_bonus =
        vallheru_domain::equipment::check_bonus("enchant", &stats, &skills, &bonuses);
    let effective_magic = magic_skill_level + enchant_bonus;
    let intelligence = stats
        .iter()
        .find(|s| s.stat_key == "inteli")
        .map_or(0, |s| s.modified);

    // Resolve (scope rng to avoid non-Send across .await)
    let result = {
        let mut rng = rand::thread_rng();
        enchantment::resolve_enchant(
            &mut rng,
            effective_magic,
            intelligence,
            item.minlev,
            spell.poziom,
            cap_stat,
        )
    };

    // Apply XP regardless of outcome
    let (intel_xp, magic_xp) = result.xp_gains();
    apply_enchant_xp(&mut stats, &mut skills, intel_xp, magic_xp, player);

    // Persist stats, skills, mana/energy
    if let Err(e) = vallheru_data::queries::player::save_stats(&app.pool, player_id, &stats).await {
        tracing::error!(error = %e, "enchant: save stats");
    }
    if let Err(e) = vallheru_data::queries::player::save_skills(&app.pool, player_id, &skills).await
    {
        tracing::error!(error = %e, "enchant: save skills");
    }
    if let Err(e) =
        vallheru_data::queries::item::deduct_mana_and_energy(&app.pool, player_id, spell.poziom)
            .await
    {
        tracing::error!(error = %e, "enchant: deduct mana/energy");
    }

    // Destroy original item (consumed either way)
    let is_arrows = item.equipment_type == "R";
    if let Err(e) =
        vallheru_data::queries::item::deduct_item_unit(&app.pool, item.id, is_arrows).await
    {
        tracing::error!(error = %e, "enchant: deduct item");
    }

    match result {
        EnchantResult::Success { bonus, .. } => {
            persist_enchant_success(
                app,
                ctx,
                player_id,
                kind,
                item,
                enchanted_name,
                bonus,
                intel_xp,
                magic_code,
            )
            .await
        }
        EnchantResult::Failure { .. } => {
            let msg = format!(
                "Próbowałeś umagicznić {} ale niestety nie udało się. \
                 Na skutek nieudanego zaklęcia przedmiot niszczy się!",
                item.name
            );
            enchant_result_page(app, ctx, &msg, FlashKind::Error)
        }
    }
}

/// Create the enchanted item and return success page.
#[allow(clippy::too_many_arguments)]
async fn persist_enchant_success(
    app: &AppState,
    ctx: &RequestContext,
    player_id: i32,
    kind: vallheru_domain::enchantment::EnchantKind,
    item: &vallheru_data::queries::item::EquipmentRow,
    enchanted_name: &str,
    bonus: i32,
    intel_xp: i32,
    magic_code: &str,
) -> Response {
    use vallheru_domain::enchantment;

    let (new_power, new_wt, new_maxwt, new_szyb, new_zr) = enchantment::apply_bonus_to_item(
        kind,
        &item.equipment_type,
        bonus,
        item.power,
        item.wt,
        item.maxwt,
        item.szyb,
        item.zr,
    );
    if let Err(e) = vallheru_data::queries::item::create_or_merge_enchanted_item(
        &app.pool,
        player_id,
        enchanted_name,
        new_power,
        &item.equipment_type,
        item.cost,
        new_zr,
        new_wt,
        item.minlev,
        new_maxwt,
        magic_code,
        item.poison,
        new_szyb,
        &item.ptype,
        &item.twohand,
        item.repair,
    )
    .await
    {
        tracing::error!(error = %e, "enchant: create enchanted item");
        return server_error();
    }
    let msg = format!(
        "Ulepszenie udane! {} zyskał bonus +{bonus}. Zdobyłeś {intel_xp} PD.",
        item.name
    );
    enchant_result_page(app, ctx, &msg, FlashKind::Success)
}

/// Apply XP gains for intelligence stat and magic skill.
fn apply_enchant_xp(
    stats: &mut [vallheru_domain::player::stats::PlayerStat],
    skills: &mut [vallheru_domain::player::skills::PlayerSkill],
    intel_xp: i32,
    magic_xp: i32,
    player: &vallheru_data::queries::player::PlayerRow,
) {
    use vallheru_domain::player::progression;

    if let Some(stat) = stats.iter_mut().find(|s| s.stat_key == "inteli") {
        let race = vallheru_domain::player::Race::from_db(&player.race)
            .unwrap_or(vallheru_domain::player::Race::Human);
        let class = vallheru_domain::player::Class::from_db(&player.class)
            .unwrap_or(vallheru_domain::player::Class::Warrior);
        let _ = progression::apply_stat_xp(stat, intel_xp, &race, &class);
    }
    if let Some(skill) = skills.iter_mut().find(|s| s.skill_key == "magic") {
        let _ = progression::apply_skill_xp(skill, magic_xp);
    }
}

// =========================================================================
// Enchantment helpers
// =========================================================================

fn format_item_stats(item: &vallheru_data::queries::item::EnchantableItem) -> String {
    let mut parts = Vec::new();
    if item.power != 0 {
        parts.push(format!("+{} siły", item.power));
    }
    if item.szyb != 0 {
        parts.push(format!("+{} szyb", item.szyb));
    }
    if item.zr != 0 {
        parts.push(format!("{} zr", item.zr));
    }
    if item.equipment_type != "R" {
        parts.push(format!("{}/{} wt", item.wt, item.maxwt));
    }
    parts.join(", ")
}

fn enchant_result_page(
    app: &AppState,
    ctx: &RequestContext,
    message: &str,
    kind: FlashKind,
) -> Response {
    let meta = PageMeta::titled("Wynik zaklęcia")
        .with_back_link("/spellbook", "Wróć do księgi")
        .with_flash(Flash {
            kind,
            message: message.to_owned(),
        });
    let base = app.templates.build_context(ctx, &meta);
    app.templates.render("spellbook_enchant_result.html", &base)
}
