//! Tribe astral vault handler — pieces, components, plans, safe-box.
//!
//! Ported from `tribeastral.php`.

use axum::{Extension, Form, extract::State, response::Response};

use crate::handlers::tribe_storage::{
    MemberEntry, can_give, load_members, load_tribe_and_storage_access, require_tribe_member,
    server_error, storage_error_page,
};
use crate::middleware::context::RequestContext;
use crate::page::PageMeta;
use crate::state::AppState;
use vallheru_data::queries::tribe as tq;
use vallheru_domain::group::tribe_admin::TribePermission;
use vallheru_domain::group::tribe_storage::{
    SAFE_BOX_COSTS, SAFE_BOX_MAX_LEVEL, SafeBoxResources, StorageArea, validate_safe_box_upgrade,
};

// =========================================================================
// Astral item labels
// =========================================================================

/// Label for a piece type (M/P/R) or component type (C/O/T).
///
/// Plans (P) and constructions (O) share the same labels by design,
/// as do recipes (R) and potions (T).
#[allow(clippy::match_same_arms)]
fn astral_type_label(code: &str) -> &'static str {
    match code {
        "M0" => "Plan demoniczny",
        "M1" => "Plan ognisty",
        "M2" => "Plan piekielny",
        "M3" => "Plan pustynny",
        "M4" => "Plan wodny",
        "M5" => "Plan niebiański",
        "M6" => "Plan śmiertelny",
        "P0" => "Astralny komponent",
        "P1" => "Gwiezdny portal",
        "P2" => "Świetlisty obelisk",
        "P3" => "Płomienny znicz",
        "P4" => "Srebrzysta fontanna",
        "R0" => "Magiczna esensja",
        "R1" => "Gwiezdna maść",
        "R2" => "Eliksir Illuminati",
        "R3" => "Astralne medium",
        "R4" => "Magiczny absynt",
        "C0" => "Ząb Glabrezu",
        "C1" => "Ognisty pył",
        "C2" => "Pazur Zgłębiczarta",
        "C3" => "Łuska Skorpendry",
        "C4" => "Macka Krakena",
        "C5" => "Piorun Tytana",
        "C6" => "Żebro Licha",
        "O0" => "Astralny komponent",
        "O1" => "Gwiezdny portal",
        "O2" => "Świetlny obelisk",
        "O3" => "Płomienny znicz",
        "O4" => "Srebrzysta fontanna",
        "T0" => "Magiczna esensja",
        "T1" => "Gwiezdna maść",
        "T2" => "Eliksir Illuminati",
        "T3" => "Astralne medium",
        "T4" => "Magiczny absynt",
        _ => "Nieznany",
    }
}

/// Human-readable category for grouping in templates.
fn astral_category(type_code: &str) -> &'static str {
    match type_code.chars().next() {
        Some('M') => "Mapy",
        Some('P') => "Plany",
        Some('R') => "Przepisy",
        Some('C') => "Komponenty",
        Some('O') => "Konstrukcje",
        Some('T') => "Mikstury",
        _ => "Inne",
    }
}

/// Whether a type code is a "piece" (M/P/R) vs. a "component" (C/O/T).
fn is_piece_type(type_code: &str) -> bool {
    matches!(type_code.chars().next(), Some('M' | 'P' | 'R'))
}

/// All valid astral type codes.
const VALID_TYPE_CODES: &[&str] = &[
    "M0", "M1", "M2", "M3", "M4", "M5", "M6", "P0", "P1", "P2", "P3", "P4", "R0", "R1", "R2", "R3",
    "R4", "C0", "C1", "C2", "C3", "C4", "C5", "C6", "O0", "O1", "O2", "O3", "O4", "T0", "T1", "T2",
    "T3", "T4",
];

fn is_valid_type_code(code: &str) -> bool {
    VALID_TYPE_CODES.contains(&code)
}

// =========================================================================
// View models
// =========================================================================

#[derive(serde::Serialize)]
pub struct AstralView {
    #[serde(flatten)]
    pub base: crate::render::RenderContext,
    /// Grouped inventory: pieces (M/P/R) in tribe vault.
    pub pieces: Vec<AstralItemView>,
    /// Grouped inventory: components (C/O/T) in tribe vault.
    pub components: Vec<AstralItemView>,
    /// Completed plans in tribe vault.
    pub plans: Vec<AstralPlanView>,
    /// Can this user give items (owner or astralvault permission)?
    pub can_give: bool,
    /// Tribe members for the give dropdown.
    pub members: Vec<MemberEntry>,
    /// Current safe-box level (0–3).
    pub safebox_level: i16,
    /// Safe-box is at max level.
    pub safebox_max: bool,
    /// Cost description for next upgrade (if not max).
    pub safebox_next_cost: Option<SafeBoxCostView>,
}

#[derive(serde::Serialize)]
pub struct AstralItemView {
    /// Type code, e.g. "M0"
    pub type_code: String,
    /// Human label, e.g. "Plan demoniczny"
    pub label: String,
    /// Category (Mapy / Plany / Przepisy / Komponenty / Konstrukcje / Mikstury)
    pub category: String,
    /// Piece number within type (for pieces)
    pub number: i16,
    /// Amount in tribe vault
    pub amount: i32,
}

#[derive(serde::Serialize)]
pub struct AstralPlanView {
    pub name: String,
    pub label: String,
    pub amount: i32,
}

#[derive(serde::Serialize)]
pub struct SafeBoxCostView {
    pub target_level: i16,
    pub gold: i64,
    pub mithril: i64,
    pub adamantium: i64,
    pub crystal: i64,
    pub meteor: i64,
}

// =========================================================================
// Form inputs
// =========================================================================

#[derive(serde::Deserialize)]
pub struct AstralDepositForm {
    /// Type code, e.g. "M0"
    pub item_type: String,
    /// Piece number within the type
    pub number: i16,
    /// How many to deposit
    #[serde(default = "default_amount")]
    pub amount: i32,
}

#[derive(serde::Deserialize)]
pub struct AstralGiveForm {
    pub item_type: String,
    pub number: i16,
    #[serde(default = "default_amount")]
    pub amount: i32,
    pub recipient_id: i32,
}

fn default_amount() -> i32 {
    1
}

// =========================================================================
// Helpers
// =========================================================================

fn error_page(state: &AppState, ctx: &RequestContext, message: &str) -> Response {
    storage_error_page(state, ctx, "Astralny skarbiec", message)
}

// =========================================================================
// Handlers
// =========================================================================

/// GET /tribe/astral — show the full astral vault.
#[allow(clippy::too_many_lines)]
pub async fn astral_show(
    State(app): State<AppState>,
    Extension(ctx): Extension<RequestContext>,
) -> Response {
    let (player_id, player) = match require_tribe_member(&app, &ctx).await {
        Ok(v) => v,
        Err(r) => return r,
    };

    let (tribe, perms) = match load_tribe_and_storage_access(
        &app,
        &ctx,
        player_id,
        player.tribe,
        StorageArea::Astral,
    )
    .await
    {
        Ok(v) => v,
        Err(r) => return r,
    };

    // Fetch tribe astral items
    let items = match tq::astral_items_for_tribe(&app.pool, tribe.id).await {
        Ok(v) => v,
        Err(e) => {
            tracing::error!(error = %e, tribe_id = tribe.id, "Failed to load astral items");
            Vec::new()
        }
    };

    let mut pieces = Vec::new();
    let mut components = Vec::new();
    for row in &items {
        let view = AstralItemView {
            type_code: row.r#type.clone(),
            label: astral_type_label(&row.r#type).to_owned(),
            category: astral_category(&row.r#type).to_owned(),
            number: row.number,
            amount: row.amount,
        };
        if is_piece_type(&row.r#type) {
            pieces.push(view);
        } else {
            components.push(view);
        }
    }

    // Fetch completed plans
    let plan_rows = match tq::astral_plans_for_tribe(&app.pool, tribe.id).await {
        Ok(v) => v,
        Err(e) => {
            tracing::error!(error = %e, tribe_id = tribe.id, "Failed to load astral plans");
            Vec::new()
        }
    };

    let plans: Vec<AstralPlanView> = plan_rows
        .into_iter()
        .map(|(name, amount)| AstralPlanView {
            label: astral_type_label(&name).to_owned(),
            name,
            amount,
        })
        .collect();

    // Safe-box info
    let safebox_level = tq::astral_safebox_level(&app.pool, tribe.id)
        .await
        .unwrap_or_else(|e| {
            tracing::error!(error = %e, tribe_id = tribe.id, "Failed to load astral safebox level");
            0
        });
    let safebox_max = safebox_level >= SAFE_BOX_MAX_LEVEL;
    let safebox_next_cost = if safebox_max {
        None
    } else {
        let idx = usize::try_from(safebox_level).unwrap_or(0);
        let c = SAFE_BOX_COSTS[idx];
        Some(SafeBoxCostView {
            target_level: safebox_level + 1,
            gold: c[0],
            mithril: c[1],
            adamantium: c[2],
            crystal: c[3],
            meteor: c[4],
        })
    };

    let give_ok = can_give(
        player_id,
        tribe.owner,
        perms,
        StorageArea::Astral.give_permission(),
    );
    let members = if let Ok(v) = load_members(&app, tribe.id).await {
        v
    } else {
        tracing::error!(
            tribe_id = tribe.id,
            "Failed to load tribe members for astral"
        );
        Vec::new()
    };

    let meta = PageMeta::titled("Astralny skarbiec").with_back_link("/tribe", "Wróć do klanu");
    let base = app.templates.build_context(&ctx, &meta);

    let view = AstralView {
        base,
        pieces,
        components,
        plans,
        can_give: give_ok,
        members,
        safebox_level,
        safebox_max,
        safebox_next_cost,
    };
    app.templates.render_value("tribe_astral.html", &view)
}

/// POST /tribe/astral/deposit — deposit a specific astral item.
pub async fn astral_deposit(
    State(app): State<AppState>,
    Extension(ctx): Extension<RequestContext>,
    Form(form): Form<AstralDepositForm>,
) -> Response {
    let (player_id, player) = match require_tribe_member(&app, &ctx).await {
        Ok(v) => v,
        Err(r) => return r,
    };

    if load_tribe_and_storage_access(&app, &ctx, player_id, player.tribe, StorageArea::Astral)
        .await
        .is_err()
    {
        return error_page(&app, &ctx, "Nie masz dostępu do astralnego skarbca.");
    }

    if !is_valid_type_code(&form.item_type) {
        return error_page(&app, &ctx, "Nieprawidłowy typ przedmiotu.");
    }
    if form.amount <= 0 {
        return error_page(&app, &ctx, "Ilość musi być większa od zera.");
    }

    if let Err(e) = tq::astral_deposit(
        &app.pool,
        player_id,
        player.tribe,
        &form.item_type,
        form.number,
        form.amount,
    )
    .await
    {
        tracing::warn!("astral_deposit failed: {e}");
        return error_page(
            &app,
            &ctx,
            "Nie udało się złożyć przedmiotu — sprawdź ilość.",
        );
    }

    crate::page::redirect_after_post("/tribe/astral")
}

/// POST /tribe/astral/deposit-all — deposit all pieces to tribe.
pub async fn astral_deposit_all(
    State(app): State<AppState>,
    Extension(ctx): Extension<RequestContext>,
) -> Response {
    let (player_id, player) = match require_tribe_member(&app, &ctx).await {
        Ok(v) => v,
        Err(r) => return r,
    };

    if load_tribe_and_storage_access(&app, &ctx, player_id, player.tribe, StorageArea::Astral)
        .await
        .is_err()
    {
        return error_page(&app, &ctx, "Nie masz dostępu do astralnego skarbca.");
    }

    if let Err(e) = tq::astral_deposit_all_pieces(&app.pool, player_id, player.tribe).await {
        tracing::warn!("astral_deposit_all failed: {e}");
        return error_page(&app, &ctx, "Nie udało się złożyć przedmiotów.");
    }

    crate::page::redirect_after_post("/tribe/astral")
}

/// POST /tribe/astral/give — give astral item from tribe to a member.
pub async fn astral_give(
    State(app): State<AppState>,
    Extension(ctx): Extension<RequestContext>,
    Form(form): Form<AstralGiveForm>,
) -> Response {
    let (player_id, player) = match require_tribe_member(&app, &ctx).await {
        Ok(v) => v,
        Err(r) => return r,
    };

    let (tribe, perms) = match load_tribe_and_storage_access(
        &app,
        &ctx,
        player_id,
        player.tribe,
        StorageArea::Astral,
    )
    .await
    {
        Ok(v) => v,
        Err(r) => return r,
    };

    if !can_give(
        player_id,
        tribe.owner,
        perms,
        StorageArea::Astral.give_permission(),
    ) {
        return error_page(&app, &ctx, "Nie masz uprawnień do wydawania przedmiotów.");
    }

    if !is_valid_type_code(&form.item_type) {
        return error_page(&app, &ctx, "Nieprawidłowy typ przedmiotu.");
    }
    if form.amount <= 0 {
        return error_page(&app, &ctx, "Ilość musi być większa od zera.");
    }

    // Verify recipient is in the same tribe
    let recipient_tribe: Option<i32> = match sqlx::query_scalar(
        "SELECT tribe_id FROM players WHERE id = $1",
    )
    .bind(form.recipient_id)
    .fetch_optional(&app.pool)
    .await
    {
        Ok(v) => v,
        Err(e) => {
            tracing::error!(error = %e, recipient_id = form.recipient_id, "Failed to fetch recipient tribe for astral give");
            None
        }
    };

    if recipient_tribe != Some(tribe.id) {
        return error_page(&app, &ctx, "Gracz nie należy do tego klanu.");
    }

    if let Err(e) = tq::astral_give(
        &app.pool,
        tribe.id,
        form.recipient_id,
        &form.item_type,
        form.number,
        form.amount,
    )
    .await
    {
        tracing::warn!("astral_give failed: {e}");
        return error_page(
            &app,
            &ctx,
            "Nie udało się wydać przedmiotu — sprawdź ilość.",
        );
    }

    crate::page::redirect_after_post("/tribe/astral")
}

/// POST /tribe/astral/safebox — upgrade the tribe safe-box.
pub async fn astral_safebox(
    State(app): State<AppState>,
    Extension(ctx): Extension<RequestContext>,
) -> Response {
    let (player_id, player) = match require_tribe_member(&app, &ctx).await {
        Ok(v) => v,
        Err(r) => return r,
    };

    let (tribe, perms) = match load_tribe_and_storage_access(
        &app,
        &ctx,
        player_id,
        player.tribe,
        StorageArea::Astral,
    )
    .await
    {
        Ok(v) => v,
        Err(r) => return r,
    };

    let has_perm = perms.has(TribePermission::AstralVault);

    // Fetch current safebox level
    let current_level = tq::astral_safebox_level(&app.pool, tribe.id)
        .await
        .map_err(|_| server_error())
        .unwrap_or(0);

    // Fetch tribe resources
    let minerals = tq::tribe_minerals(&app.pool, tribe.id)
        .await
        .map_err(|_| server_error());
    let minerals = match minerals {
        Ok(Some(m)) => m,
        Ok(None) => return error_page(&app, &ctx, "Brak danych skarbca."),
        Err(r) => return r,
    };

    let resources = SafeBoxResources {
        gold: i64::from(tribe.credits),
        mithril: i64::from(tribe.platinum),
        adamantium: i64::from(minerals.adamantium),
        crystal: i64::from(minerals.crystal),
        meteor: i64::from(minerals.meteor),
    };

    let costs = match validate_safe_box_upgrade(
        player_id,
        tribe.owner,
        has_perm,
        current_level,
        &resources,
    ) {
        Ok(c) => c,
        Err(e) => {
            let msg = match e {
                vallheru_domain::group::tribe_storage::SafeBoxUpgradeError::NoPermission => {
                    "Nie masz uprawnień do rozbudowy sejfu."
                }
                vallheru_domain::group::tribe_storage::SafeBoxUpgradeError::AlreadyMaxLevel => {
                    "Sejf jest już na maksymalnym poziomie."
                }
                vallheru_domain::group::tribe_storage::SafeBoxUpgradeError::InsufficientGold {
                    ..
                } => "Klan nie ma wystarczającej ilości złota.",
                vallheru_domain::group::tribe_storage::SafeBoxUpgradeError::InsufficientMithril {
                    ..
                } => "Klan nie ma wystarczającej ilości mithrilu.",
                vallheru_domain::group::tribe_storage::SafeBoxUpgradeError::InsufficientAdamantium { .. } => {
                    "Klan nie ma wystarczającej ilości adamantium."
                }
                vallheru_domain::group::tribe_storage::SafeBoxUpgradeError::InsufficientCrystal {
                    ..
                } => "Klan nie ma wystarczającej ilości kryształów.",
                vallheru_domain::group::tribe_storage::SafeBoxUpgradeError::InsufficientMeteor {
                    ..
                } => "Klan nie ma wystarczającej ilości meteorytu.",
            };
            return error_page(&app, &ctx, msg);
        }
    };

    if let Err(e) = tq::astral_safebox_upgrade(&app.pool, tribe.id, current_level, costs).await {
        tracing::warn!("astral_safebox_upgrade failed: {e}");
        return error_page(&app, &ctx, "Nie udało się rozbudować sejfu.");
    }

    crate::page::redirect_after_post("/tribe/astral")
}
