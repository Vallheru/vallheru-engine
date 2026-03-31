//! Jail handlers — public view of prisoners, bail payment, and escape.
//!
//! Ported from `jail.php`. Players can view prisoners from Altara/Ardulith,
//! pay bail for others, and thieves can attempt to escape.

use axum::{
    Extension,
    extract::{Path, State},
    response::{IntoResponse, Redirect, Response},
};
use std::fmt::Write;

use crate::middleware::context::RequestContext;
use crate::page::{Flash, FlashKind, PageMeta};
use crate::state::AppState;

// ---------------------------------------------------------------------------
// View models
// ---------------------------------------------------------------------------

/// A prisoner entry for the list view.
#[derive(serde::Serialize)]
pub struct PrisonerEntry {
    pub jail_id: i32,
    pub player_id: i32,
    pub name: String,
    pub sentenced: String,
    pub verdict: String,
    pub duration_weeks: i32,
    pub duration_raw: i32,
    pub cost: i32,
    pub can_bail: bool,
}

/// View model for jail page — city visitor view.
#[derive(serde::Serialize)]
pub struct JailListView {
    #[serde(flatten)]
    pub base: crate::render::RenderContext,
    pub prisoners: Vec<PrisonerEntry>,
    pub description: String,
}

/// View model for jail page — prisoner's own view.
#[derive(serde::Serialize)]
pub struct JailPrisonerView {
    #[serde(flatten)]
    pub base: crate::render::RenderContext,
    pub sentenced: String,
    pub verdict: String,
    pub duration_weeks: i32,
    pub duration_raw: i32,
    pub cost: i32,
    pub can_escape: bool,
}

/// View model for bail confirmation.
#[derive(serde::Serialize)]
pub struct JailBailView {
    #[serde(flatten)]
    pub base: crate::render::RenderContext,
    pub jail_id: i32,
    pub prisoner_name: String,
    pub cost: i32,
}

// ---------------------------------------------------------------------------
// Handlers
// ---------------------------------------------------------------------------

/// GET /jail — View jail. Shows different content based on location:
/// - Altara/Ardulith: list of all prisoners + bail options
/// - Lochy: the player's own sentence info
pub async fn jail_view(
    State(state): State<AppState>,
    Extension(ctx): Extension<RequestContext>,
) -> Response {
    let Some(user) = ctx.session_user.as_ref() else {
        return Redirect::to("/login").into_response();
    };

    #[allow(clippy::cast_possible_truncation)]
    let player_id = user.id as i32;

    // Get player location.
    let location: Option<String> = sqlx::query_scalar("SELECT location FROM players WHERE id = $1")
        .bind(player_id)
        .fetch_optional(&state.pool)
        .await
        .unwrap_or(None);

    let location = location.unwrap_or_default();

    if location == "Lochy" {
        return jail_prisoner_view(&state, &ctx, player_id).await;
    }

    if location != "Altara" && location != "Ardulith" {
        let meta = PageMeta::titled("Błąd").with_flash(Flash {
            kind: FlashKind::Error,
            message: "Musisz znajdować się w mieście, aby odwiedzić lochy.".to_owned(),
        });
        let base = state.templates.build_context(&ctx, &meta);
        return state.templates.render("error.html", &base);
    }

    // City view — list prisoners.
    let rows = vallheru_data::queries::moderation::list_prisoners(&state.pool)
        .await
        .unwrap_or_default();

    let prisoners: Vec<PrisonerEntry> = rows
        .into_iter()
        .map(|r| PrisonerEntry {
            jail_id: r.id,
            player_id: r.prisoner,
            name: r.prisoner_name,
            sentenced: r.sentenced,
            verdict: r.verdict,
            duration_weeks: (r.duration + 6) / 7,
            duration_raw: r.duration,
            cost: r.cost,
            can_bail: r.cost > 0 && r.prisoner != player_id,
        })
        .collect();

    let meta = PageMeta::titled("Lochy").with_back_link("/city", "Miasto");
    let view = JailListView {
        base: state.templates.build_context(&ctx, &meta),
        prisoners,
        description: String::from(
            "Tutaj znajdują się lochy, do których wtrącani są \
             wszyscy obywatele łamiący miejscowe prawo.",
        ),
    };

    state.templates.render_value("jail.html", &view)
}

/// Render the prisoner's own jail view.
async fn jail_prisoner_view(state: &AppState, ctx: &RequestContext, player_id: i32) -> Response {
    let record = vallheru_data::queries::moderation::find_jail_by_prisoner(&state.pool, player_id)
        .await
        .ok()
        .flatten();

    let (sentenced, verdict, duration, cost) = match record {
        Some(r) => (r.sentenced, r.verdict, r.duration, r.cost),
        None => {
            return Redirect::to("/city").into_response();
        }
    };

    // Only thieves can attempt escape, and only if not admin-sentenced (cost > 0).
    let player_class: Option<String> =
        sqlx::query_scalar("SELECT class FROM players WHERE id = $1")
            .bind(player_id)
            .fetch_optional(&state.pool)
            .await
            .unwrap_or(None);

    let can_escape = cost > 0 && player_class.as_deref() == Some("Złodziej");

    let meta = PageMeta::titled("Lochy");
    let view = JailPrisonerView {
        base: state.templates.build_context(ctx, &meta),
        sentenced,
        verdict,
        duration_weeks: (duration + 6) / 7,
        duration_raw: duration,
        cost,
        can_escape,
    };

    state.templates.render_value("jail_prisoner.html", &view)
}

/// GET /jail/bail/:id — Bail confirmation page.
pub async fn jail_bail_confirm(
    State(state): State<AppState>,
    Extension(ctx): Extension<RequestContext>,
    Path(jail_id): Path<i32>,
) -> Response {
    let Some(user) = ctx.session_user.as_ref() else {
        return Redirect::to("/login").into_response();
    };

    let record = vallheru_data::queries::moderation::find_jail_record(&state.pool, jail_id)
        .await
        .ok()
        .flatten();

    let Some(record) = record else {
        return Redirect::to("/jail").into_response();
    };

    if record.cost == 0 {
        return crate::page::redirect("/jail");
    }

    #[allow(clippy::cast_possible_truncation)]
    let player_id = user.id as i32;
    if record.prisoner == player_id {
        return crate::page::redirect("/jail");
    }

    let meta = PageMeta::titled("Kaucja").with_back_link("/jail", "Lochy");
    let view = JailBailView {
        base: state.templates.build_context(&ctx, &meta),
        jail_id,
        prisoner_name: record.prisoner_name,
        cost: record.cost,
    };

    state.templates.render_value("jail_bail.html", &view)
}

/// POST /jail/bail/:id — Process bail payment.
pub async fn jail_bail_pay(
    State(state): State<AppState>,
    Extension(ctx): Extension<RequestContext>,
    Path(jail_id): Path<i32>,
) -> Response {
    let Some(user) = ctx.session_user.as_ref() else {
        return Redirect::to("/login").into_response();
    };

    #[allow(clippy::cast_possible_truncation)]
    let payer_id = user.id as i32;

    let record = vallheru_data::queries::moderation::find_jail_record(&state.pool, jail_id)
        .await
        .ok()
        .flatten();

    let record = match record {
        Some(r) if r.cost > 0 && r.prisoner != payer_id => r,
        _ => return crate::page::redirect("/jail"),
    };

    // Check payer has enough gold.
    let payer_gold: Option<i32> = sqlx::query_scalar("SELECT credits FROM players WHERE id = $1")
        .bind(payer_id)
        .fetch_optional(&state.pool)
        .await
        .unwrap_or(None);

    if payer_gold.unwrap_or(0) < record.cost {
        return crate::page::redirect("/jail");
    }

    let prisoner_id =
        vallheru_data::queries::moderation::pay_bail(&state.pool, jail_id, payer_id, record.cost)
            .await;

    match prisoner_id {
        Ok(pid) => {
            let msg = format!(
                "Kaucję za ciebie wpłacił gracz ID {payer_id}. \
                 Zostałeś zwolniony z lochów."
            );
            let _ =
                vallheru_data::queries::moderation::insert_game_log(&state.pool, pid, &msg, 'J')
                    .await;

            crate::page::redirect("/jail")
        }
        Err(_) => crate::page::redirect("/jail"),
    }
}

/// POST /jail/escape — Thief attempts to break out of jail.
pub async fn jail_escape(
    State(state): State<AppState>,
    Extension(ctx): Extension<RequestContext>,
) -> Response {
    let Some(user) = ctx.session_user.as_ref() else {
        return Redirect::to("/login").into_response();
    };

    #[allow(clippy::cast_possible_truncation)]
    let player_id = user.id as i32;

    let error_page = |msg: &str| -> Response {
        let meta = PageMeta::titled("Błąd").with_flash(Flash {
            kind: FlashKind::Error,
            message: msg.to_owned(),
        });
        let base = state.templates.build_context(&ctx, &meta);
        state.templates.render("error.html", &base)
    };

    // Load player.
    let Ok(Some(player)) =
        vallheru_data::queries::player::find_player_by_id(&state.pool, player_id).await
    else {
        return error_page("Nie znaleziono gracza.");
    };

    if player.location != "Lochy" {
        return error_page("Nie znajdujesz się w lochach.");
    }
    if player.class != "Złodziej" {
        return error_page("Tylko złodziej może próbować uciekać z więzienia.");
    }
    if player.energy < 2.0 {
        return error_page("Nie masz wystarczającej ilości energii.");
    }

    let jail_record =
        vallheru_data::queries::moderation::find_jail_by_prisoner(&state.pool, player_id)
            .await
            .ok()
            .flatten();

    let Some(record) = jail_record else {
        return error_page("Zapomnij o tym.");
    };

    if record.cost == 0 {
        return error_page(
            "Nie możesz próbować ucieczki, ponieważ została nałożona na ciebie kara administracyjna.",
        );
    }

    let (roll, chance) = compute_escape_chance(&state, player_id).await;

    let suffix = if player.gender.as_deref() == Some("M") {
        "eś"
    } else {
        "aś"
    };

    if chance < 1 {
        escape_failure(&state, &ctx, player_id, &player, suffix).await
    } else {
        escape_success(&state, &ctx, player_id, &player, roll, chance, suffix).await
    }
}

/// Compute escape roll and chance.
async fn compute_escape_chance(state: &AppState, player_id: i32) -> (i32, i32) {
    let roll: i32 = {
        use rand::Rng;
        rand::thread_rng().gen_range(1..=150)
    };

    let chance = if roll == 1 {
        0
    } else if roll >= 145 {
        1_000_000
    } else {
        let player_stats = vallheru_data::queries::player::load_stats(&state.pool, player_id)
            .await
            .unwrap_or_default();

        let agility = player_stats
            .iter()
            .find(|s| s.stat_key == "agility")
            .map_or(0, |s| s.trained);
        let intelligence = player_stats
            .iter()
            .find(|s| s.stat_key == "inteli")
            .map_or(0, |s| s.trained);
        let speed = player_stats
            .iter()
            .find(|s| s.stat_key == "speed")
            .map_or(0, |s| s.trained);

        let player_skills = vallheru_data::queries::player::load_skills(&state.pool, player_id)
            .await
            .unwrap_or_default();
        let thievery = player_skills
            .iter()
            .find(|s| s.skill_key == "thievery")
            .map_or(0, |s| s.level);

        agility + intelligence + thievery + speed - roll
    };

    (roll, chance)
}

/// Handle failed escape attempt.
async fn escape_failure(
    state: &AppState,
    ctx: &RequestContext,
    player_id: i32,
    player: &vallheru_data::queries::player::PlayerRow,
    suffix: &str,
) -> Response {
    let bail_increase = {
        let player_skills = vallheru_data::queries::player::load_skills(&state.pool, player_id)
            .await
            .unwrap_or_default();
        let thievery_level = player_skills
            .iter()
            .find(|s| s.skill_key == "thievery")
            .map_or(1, |s| s.level);
        1000 * thievery_level
    };

    let _ = sqlx::query("UPDATE players SET energy = energy - 2 WHERE id = $1")
        .bind(player_id)
        .execute(&state.pool)
        .await;

    let _ = sqlx::query(
        "UPDATE jail SET duration = duration + 7, cost = cost + $1 WHERE prisoner = $2",
    )
    .bind(bail_increase)
    .bind(player_id)
    .execute(&state.pool)
    .await;

    let xp_msg = apply_escape_xp(
        state,
        player_id,
        &player.race,
        &player.class,
        &[("agility", 1), ("inteli", 1), ("speed", 1)],
        &[("thievery", 1)],
    )
    .await;

    let msg = format!(
        "Próbował{suffix} wydostać się z celi. Z początku wszystko szło zgodnie z planem, \
         jednak w pewnym momencie straż zauważyła i pojmała ciebie. Wylądował{suffix} w innej \
         celi, tym razem znacznie lepiej strzeżonej. Na dodatek podniesiono kaucję za ciebie \
         oraz przedłużono lochy.{xp_msg}"
    );

    let meta = PageMeta::titled("Lochy")
        .with_flash(Flash {
            kind: FlashKind::Error,
            message: msg,
        })
        .with_back_link("/jail", "Lochy");
    let base = state.templates.build_context(ctx, &meta);
    state.templates.render("error.html", &base)
}

/// Handle successful escape.
async fn escape_success(
    state: &AppState,
    ctx: &RequestContext,
    player_id: i32,
    player: &vallheru_data::queries::player::PlayerRow,
    roll: i32,
    chance: i32,
    suffix: &str,
) -> Response {
    let mut xp_amount = roll * 5;
    if chance == 1_000_000 {
        xp_amount *= 2;
    }
    let xp_split = xp_amount / 4;

    // Delete jail record + move to Altara + deduct energy.
    let _ = sqlx::query("DELETE FROM jail WHERE prisoner = $1")
        .bind(player_id)
        .execute(&state.pool)
        .await;
    let _ =
        sqlx::query("UPDATE players SET energy = energy - 2, location = 'Altara' WHERE id = $1")
            .bind(player_id)
            .execute(&state.pool)
            .await;

    // Award XP split 4 ways.
    let xp_msg = apply_escape_xp(
        state,
        player_id,
        &player.race,
        &player.class,
        &[
            ("agility", xp_split),
            ("speed", xp_split),
            ("inteli", xp_split),
        ],
        &[("thievery", xp_split)],
    )
    .await;

    let msg = format!(
        "Wykorzystując nieuwagę straży, otworzył{suffix} drzwi celi i niepostrzeżenie \
         wydostał{suffix} się z lochów do miasta.{xp_msg}"
    );

    let meta = PageMeta::titled("Wolność!")
        .with_flash(Flash {
            kind: FlashKind::Success,
            message: msg,
        })
        .with_back_link("/city", "Miasto");
    let base = state.templates.build_context(ctx, &meta);
    state.templates.render("error.html", &base)
}

/// Apply stat and skill XP for escape attempts.
async fn apply_escape_xp(
    state: &AppState,
    player_id: i32,
    race: &str,
    class: &str,
    stat_xp: &[(&str, i32)],
    skill_xp: &[(&str, i32)],
) -> String {
    use vallheru_domain::player::progression;

    let Some(race) = vallheru_domain::player::Race::from_db(race) else {
        return String::new();
    };
    let Some(class) = vallheru_domain::player::Class::from_db(class) else {
        return String::new();
    };

    let mut extra = String::new();
    let mut hp_change = 0;

    if !stat_xp.is_empty() {
        let mut player_stats = vallheru_data::queries::player::load_stats(&state.pool, player_id)
            .await
            .unwrap_or_default();

        for &(key, xp) in stat_xp {
            if xp <= 0 {
                continue;
            }
            if let Some(stat) = player_stats.iter_mut().find(|s| s.stat_key == key) {
                let result = progression::apply_stat_xp(stat, xp, &race, &class);
                if result.levels_gained > 0 {
                    let _ = write!(
                        extra,
                        " Twój stat {} wzrósł o {} poziom(ów)!",
                        key, result.levels_gained
                    );
                }
                hp_change += result.hp_change;
            }
        }

        if let Err(e) =
            vallheru_data::queries::player::save_stats(&state.pool, player_id, &player_stats).await
        {
            tracing::error!(error = %e, "apply_escape_xp: save_stats failed");
        }
    }

    if !skill_xp.is_empty() {
        let mut player_skills = vallheru_data::queries::player::load_skills(&state.pool, player_id)
            .await
            .unwrap_or_default();

        for &(key, xp) in skill_xp {
            if xp <= 0 {
                continue;
            }
            if let Some(skill) = player_skills.iter_mut().find(|s| s.skill_key == key) {
                let result = progression::apply_skill_xp(skill, xp);
                if result.levels_gained > 0 {
                    let _ = write!(
                        extra,
                        " Twoja umiejętność {} wzrosła o {} poziom(ów)!",
                        key, result.levels_gained
                    );
                }
            }
        }

        if let Err(e) =
            vallheru_data::queries::player::save_skills(&state.pool, player_id, &player_skills)
                .await
        {
            tracing::error!(error = %e, "apply_escape_xp: save_skills failed");
        }
    }

    if hp_change != 0 {
        let _ = vallheru_data::queries::locations::add_player_hp(&state.pool, player_id, hp_change)
            .await;
    }

    extra
}
