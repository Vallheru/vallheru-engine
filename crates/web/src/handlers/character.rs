//! Character progression handlers — stats, training, race, class, AP bonuses, Hall of Fame.
//!
//! Ported from PHP: stats, train, rasa, klasa, ap, hof, hof2.

use axum::{
    Extension, Form,
    extract::{Query, State},
    response::Response,
};

use crate::middleware::context::RequestContext;
use crate::page::{Flash, FlashKind, PageMeta};
use crate::state::AppState;
use vallheru_data::queries::player as pq;
use vallheru_domain::location::Location;
use vallheru_domain::player::mutations;
use vallheru_domain::player::progression;
use vallheru_domain::player::views;
use vallheru_domain::player::{Class, Race};

// ---------------------------------------------------------------------------
// View models
// ---------------------------------------------------------------------------

#[derive(serde::Serialize)]
struct StatsPage {
    #[serde(flatten)]
    base: crate::render::RenderContext,
    // Left column – game stats
    avatar: String,
    section: String,
    ap: i32,
    race: String,
    class: String,
    deity: String,
    gender_display: String,
    stats: Vec<views::StatDisplay>,
    mana: i32,
    max_mana: i32,
    pw: i32,
    energy_display: String,
    bless_label: Option<String>,
    bless_value: i32,
    antidote_label: Option<String>,
    antidote_code: String,
    reputation: i32,
    wins: i32,
    losses: i32,
    last_killed: String,
    last_killed_by: String,
    mpoints: i32,
    // Right column – info
    rank_label: String,
    location: String,
    age: i32,
    logins: i32,
    ip: String,
    email: String,
    messenger: String,
    newbie: i16,
    tribe_name: String,
    tribe_rank: String,
    // Bottom – skills & bonuses
    skills: Vec<views::SkillDisplay>,
    bonuses: Vec<views::BonusDisplay>,
}

#[derive(serde::Serialize)]
struct TrainPage {
    #[serde(flatten)]
    base: crate::render::RenderContext,
    description: String,
    train_info: String,
    train_info2: String,
    trainable_stats: Vec<(String, String)>,
}

#[derive(serde::Serialize)]
struct HofPage {
    #[serde(flatten)]
    base: crate::render::RenderContext,
    heroes: Vec<HofHero>,
}

#[derive(serde::Serialize)]
struct HofHero {
    hero_name: String,
    hero_id: i32,
    current_player_id: Option<i32>,
    race: String,
}

#[derive(serde::Serialize)]
struct HofMachinesPage {
    #[serde(flatten)]
    base: crate::render::RenderContext,
    machines: Vec<HofMachine>,
}

#[derive(serde::Serialize)]
struct HofMachine {
    tribe_name: String,
    leader_name: String,
    build_date: String,
}

#[derive(serde::Serialize)]
struct ApPage {
    #[serde(flatten)]
    base: crate::render::RenderContext,
    ap: i32,
    available_bonuses: Vec<ApBonusEntry>,
}

#[derive(serde::Serialize)]
struct ApBonusEntry {
    id: i32,
    name: String,
    description: String,
    current_level: i32,
    max_levels: i32,
    cost: i32,
}

#[derive(serde::Serialize)]
struct RacePage {
    #[serde(flatten)]
    base: crate::render::RenderContext,
    section: String,
    race_slug: String,
    race_description: String,
    stat_bonuses: Vec<RaceStatBonus>,
}

#[derive(serde::Serialize)]
struct RaceStatBonus {
    label: String,
    value: i32,
    max_label: String,
}

#[derive(serde::Serialize)]
struct ClassPage {
    #[serde(flatten)]
    base: crate::render::RenderContext,
    section: String,
    class_slug: String,
    class_description: String,
    stat_bonuses: Vec<ClassStatBonus>,
}

#[derive(serde::Serialize)]
struct ClassStatBonus {
    label: String,
    value: String,
}

// ---------------------------------------------------------------------------
// Query / form params
// ---------------------------------------------------------------------------

#[derive(serde::Deserialize, Default)]
pub struct StatsQuery {
    pub action: Option<String>,
}

#[derive(serde::Deserialize)]
pub struct GenderForm {
    pub gender: String,
}

#[derive(serde::Deserialize)]
pub struct TrainForm {
    pub stat_key: String,
    pub rep: Option<String>,
}

#[derive(serde::Deserialize, Default)]
pub struct RaceQuery {
    pub race: Option<String>,
}

#[derive(serde::Deserialize)]
pub struct RaceForm {
    pub race: String,
}

#[derive(serde::Deserialize, Default)]
pub struct ClassQuery {
    pub class: Option<String>,
}

#[derive(serde::Deserialize)]
pub struct ClassForm {
    pub class: String,
}

#[derive(serde::Deserialize)]
pub struct ApBuyForm {
    pub bonus_id: i32,
    pub confirm: Option<String>,
}

// ---------------------------------------------------------------------------
// GET /stats
// ---------------------------------------------------------------------------

#[allow(clippy::too_many_lines)]
pub async fn stats_show(
    State(app): State<AppState>,
    Extension(ctx): Extension<RequestContext>,
    Query(query): Query<StatsQuery>,
) -> Response {
    let Some(ref user) = ctx.session_user else {
        return crate::page::redirect("/login");
    };

    #[allow(clippy::cast_possible_truncation)]
    let player_id = user.id as i32;

    let player_row = match load_player(&app, player_id).await {
        Ok(r) => r,
        Err(resp) => return resp,
    };

    let subs = match pq::load_sub_models(&app.pool, &player_row).await {
        Ok(s) => s,
        Err(e) => {
            tracing::error!(error = %e, "stats: load_sub_models");
            return server_error();
        }
    };

    // Newbie-off confirmation screen
    let section = if query.action.as_deref() == Some("newbie") {
        "newbie_confirm"
    } else {
        ""
    };

    let is_thief = player_row.class == "Złodziej";
    let stat_displays = views::build_stat_displays(&subs.stats, &subs.stats);
    let skill_displays = views::build_skill_displays(&subs.skills, &subs.skills, is_thief);
    let bonus_displays = views::build_bonus_displays(&subs.bonuses);

    let gender_display = match player_row.gender.as_deref() {
        Some("M") => "Mężczyzna".to_owned(),
        Some("F") => "Kobieta".to_owned(),
        Some(g) if !g.is_empty() => g.to_owned(),
        _ => String::new(),
    };

    let bless_label = if player_row.bless.is_empty() {
        None
    } else {
        views::bless_label(&player_row.bless).map(str::to_owned)
    };

    let antidote_label = player_row
        .antidote
        .as_deref()
        .and_then(views::antidote_label)
        .map(str::to_owned);
    let antidote_code = player_row.antidote.clone().unwrap_or_default();

    let race_enum = Race::from_db(&player_row.race);
    let class_enum = Class::from_db(&player_row.class);

    let max_mana = match (&race_enum, &class_enum) {
        (Some(_), Some(cls)) => progression::max_mana(&subs.stats, cls, 0),
        _ => 0,
    };

    let tribe_name = match pq::tribe_name_for_player(&app.pool, player_row.tribe_id).await {
        Ok(name) => name.unwrap_or_default(),
        Err(e) => {
            tracing::warn!(error = %e, "stats: tribe_name_for_player");
            String::new()
        }
    };

    let rank_label = views::format_rank(player_row.rank.as_str(), player_row.gender.as_deref());

    let energy_display = format!("{:.1}/{:.1}", player_row.energy, player_row.max_energy);

    let meta = PageMeta::titled("Statystyki").with_back_link("/city", "Miasto");
    let base = app.templates.build_context(&ctx, &meta);

    app.templates.render_value(
        "stats.html",
        &StatsPage {
            base,
            avatar: player_row.avatar.clone(),
            section: section.to_owned(),
            ap: player_row.ap,
            race: player_row.race.clone(),
            class: player_row.class.clone(),
            deity: player_row.deity.clone().unwrap_or_default(),
            gender_display,
            stats: stat_displays,
            mana: player_row.pm,
            max_mana,
            pw: player_row.pw,
            energy_display,
            bless_label,
            bless_value: player_row.bless_value,
            antidote_label,
            antidote_code,
            reputation: player_row.reputation,
            wins: player_row.wins,
            losses: player_row.losses,
            last_killed: player_row.last_killed.clone(),
            last_killed_by: player_row.last_killed_by.clone(),
            mpoints: player_row.mpoints,
            rank_label,
            location: player_row.location.clone(),
            age: player_row.age,
            logins: player_row.logins,
            ip: player_row.ip.clone(),
            email: player_row.email.clone(),
            messenger: player_row.messenger.clone(),
            newbie: player_row.newbie,
            tribe_name,
            tribe_rank: player_row.tribe_rank.clone(),
            skills: skill_displays,
            bonuses: bonus_displays,
        },
    )
}

// ---------------------------------------------------------------------------
// POST /stats/gender
// ---------------------------------------------------------------------------

pub async fn stats_gender(
    State(app): State<AppState>,
    Extension(ctx): Extension<RequestContext>,
    Form(form): Form<GenderForm>,
) -> Response {
    let Some(ref user) = ctx.session_user else {
        return crate::page::redirect("/login");
    };

    #[allow(clippy::cast_possible_truncation)]
    let player_id = user.id as i32;

    if form.gender != "M" && form.gender != "F" {
        return error_page(&app, &ctx, "Nieprawidłowa płeć.");
    }

    if let Err(e) = pq::set_gender(&app.pool, player_id, &form.gender).await {
        tracing::error!(error = %e, "stats_gender: set_gender");
        return server_error();
    }

    crate::page::redirect_after_post("/stats")
}

// ---------------------------------------------------------------------------
// POST /stats/newbie-off
// ---------------------------------------------------------------------------

pub async fn stats_newbie_off(
    State(app): State<AppState>,
    Extension(ctx): Extension<RequestContext>,
) -> Response {
    let Some(ref user) = ctx.session_user else {
        return crate::page::redirect("/login");
    };

    #[allow(clippy::cast_possible_truncation)]
    let player_id = user.id as i32;

    if let Err(e) = pq::disable_newbie(&app.pool, player_id).await {
        tracing::error!(error = %e, "stats_newbie_off: disable_newbie");
        return server_error();
    }

    crate::page::redirect_after_post("/stats")
}

// ---------------------------------------------------------------------------
// GET /train
// ---------------------------------------------------------------------------

pub async fn train_show(
    State(app): State<AppState>,
    Extension(ctx): Extension<RequestContext>,
) -> Response {
    let Some(ref user) = ctx.session_user else {
        return crate::page::redirect("/login");
    };

    #[allow(clippy::cast_possible_truncation)]
    let player_id = user.id as i32;

    let player_row = match load_player(&app, player_id).await {
        Ok(r) => r,
        Err(resp) => return resp,
    };

    let location = Location::from_db(&player_row.location);
    if location.is_none_or(|l| !l.is_city()) {
        return error_page(&app, &ctx, "Musisz być w mieście, aby trenować.");
    }

    if player_row.hp == 0 {
        return error_page(&app, &ctx, "Nie możesz trenować — jesteś martwy.");
    }

    let description = format!(
        "Stoisz na arenie treningowej. Twoja energia: {:.1}/{:.1}, złoto: {}.",
        player_row.energy, player_row.max_energy, player_row.credits
    );

    let trainable_stats = vec![
        ("strength".to_owned(), "Siła".to_owned()),
        ("agility".to_owned(), "Zręczność".to_owned()),
        ("condition".to_owned(), "Kondycja".to_owned()),
        ("speed".to_owned(), "Szybkość".to_owned()),
        ("inteli".to_owned(), "Inteligencja".to_owned()),
        ("wisdom".to_owned(), "Siła Woli".to_owned()),
    ];

    let meta = PageMeta::titled("Trening").with_back_link("/city", "Miasto");
    let base = app.templates.build_context(&ctx, &meta);

    app.templates.render_value(
        "train.html",
        &TrainPage {
            base,
            description,
            train_info: String::new(),
            train_info2: String::new(),
            trainable_stats,
        },
    )
}

// ---------------------------------------------------------------------------
// POST /train
// ---------------------------------------------------------------------------

#[allow(clippy::too_many_lines)]
pub async fn train_action(
    State(app): State<AppState>,
    Extension(ctx): Extension<RequestContext>,
    Form(form): Form<TrainForm>,
) -> Response {
    let Some(ref user) = ctx.session_user else {
        return crate::page::redirect("/login");
    };

    #[allow(clippy::cast_possible_truncation)]
    let player_id = user.id as i32;

    let player_row = match load_player(&app, player_id).await {
        Ok(r) => r,
        Err(resp) => return resp,
    };

    let Some(race) = Race::from_db(&player_row.race) else {
        return error_page(&app, &ctx, "Musisz najpierw wybrać rasę.");
    };
    let Some(class) = Class::from_db(&player_row.class) else {
        return error_page(&app, &ctx, "Musisz najpierw wybrać klasę.");
    };

    let Some(location) = Location::from_db(&player_row.location) else {
        return error_page(&app, &ctx, "Nieznana lokacja.");
    };

    let mut stats = match pq::load_stats(&app.pool, player_id).await {
        Ok(s) => s,
        Err(e) => {
            tracing::error!(error = %e, "train_action: load_stats");
            return server_error();
        }
    };

    let Some(stat) = stats.iter().find(|s| s.stat_key == form.stat_key) else {
        return error_page(&app, &ctx, "Nieznana cecha.");
    };

    let repetitions: i32 = form.rep.as_deref().unwrap_or("1").parse().unwrap_or(0);

    let result = match mutations::validate_training(
        &location,
        player_row.hp,
        &race,
        &class,
        &form.stat_key,
        stat.trained,
        stat.base,
        repetitions,
        player_row.energy,
        player_row.credits,
    ) {
        Ok(r) => r,
        Err(mutations::TrainingError::NotInCity) => {
            return error_page(&app, &ctx, "Musisz być w mieście, aby trenować.");
        }
        Err(mutations::TrainingError::Dead) => {
            return error_page(&app, &ctx, "Nie możesz trenować — jesteś martwy.");
        }
        Err(mutations::TrainingError::NoRace) => {
            return error_page(&app, &ctx, "Musisz najpierw wybrać rasę.");
        }
        Err(mutations::TrainingError::NoClass) => {
            return error_page(&app, &ctx, "Musisz najpierw wybrać klasę.");
        }
        Err(mutations::TrainingError::InvalidStat) => {
            return error_page(&app, &ctx, "Nieznana cecha.");
        }
        Err(mutations::TrainingError::AtCap) => {
            return error_page(&app, &ctx, "Ta cecha osiągnęła już maksymalny poziom.");
        }
        Err(mutations::TrainingError::InvalidRepetitions) => {
            return error_page(&app, &ctx, "Podaj prawidłową liczbę powtórzeń.");
        }
        Err(mutations::TrainingError::InsufficientEnergy) => {
            return error_page(&app, &ctx, "Nie masz wystarczająco energii.");
        }
        Err(mutations::TrainingError::InsufficientGold { needed }) => {
            return error_page(
                &app,
                &ctx,
                &format!("Potrzebujesz {needed} złota aby trenować."),
            );
        }
    };

    // Apply XP to the stat
    let target_stat = stats
        .iter_mut()
        .find(|s| s.stat_key == form.stat_key)
        .expect("stat validated above");

    let xp_result = progression::apply_stat_xp(target_stat, result.xp_gained, &race, &class);

    // Persist: deduct energy/gold
    if let Err(e) =
        pq::apply_training(&app.pool, player_id, result.energy_cost, result.gold_cost).await
    {
        tracing::error!(error = %e, "train_action: apply_training");
        return server_error();
    }

    // Persist: save stats
    if let Err(e) = pq::save_stats(&app.pool, player_id, &stats).await {
        tracing::error!(error = %e, "train_action: save_stats");
        return server_error();
    }

    // Persist: AP and HP gains from level-ups
    if xp_result.ap_change > 0 || xp_result.hp_change > 0 {
        if let Err(e) = apply_levelup_rewards(
            &app.pool,
            player_id,
            xp_result.ap_change,
            xp_result.hp_change,
        )
        .await
        {
            tracing::error!(error = %e, "train_action: apply_levelup_rewards");
            return server_error();
        }
    }

    let mut msg = format!(
        "Wytrenirowałeś {} razy. Zdobyłeś {} doświadczenia.",
        repetitions, result.xp_gained
    );
    if xp_result.levels_gained > 0 {
        use std::fmt::Write;
        write!(
            msg,
            " Wzrost o {} poziomów! +{} AP",
            xp_result.levels_gained, xp_result.ap_change
        )
        .unwrap();
        if xp_result.hp_change > 0 {
            write!(msg, ", +{} HP", xp_result.hp_change).unwrap();
        }
        msg.push('.');
    }

    let meta = PageMeta::titled("Trening")
        .with_back_link("/city", "Miasto")
        .with_flash(Flash::success(msg));
    let base = app.templates.build_context(&ctx, &meta);

    let description = format!(
        "Stoisz na arenie treningowej. Twoja energia: {:.1}/{:.1}, złoto: {}.",
        player_row.energy - result.energy_cost,
        player_row.max_energy,
        player_row.credits - result.gold_cost
    );

    let trainable_stats = vec![
        ("strength".to_owned(), "Siła".to_owned()),
        ("agility".to_owned(), "Zręczność".to_owned()),
        ("condition".to_owned(), "Kondycja".to_owned()),
        ("speed".to_owned(), "Szybkość".to_owned()),
        ("inteli".to_owned(), "Inteligencja".to_owned()),
        ("wisdom".to_owned(), "Siła Woli".to_owned()),
    ];

    app.templates.render_value(
        "train.html",
        &TrainPage {
            base,
            description,
            train_info: String::new(),
            train_info2: String::new(),
            trainable_stats,
        },
    )
}

// ---------------------------------------------------------------------------
// GET /hall-of-fame
// ---------------------------------------------------------------------------

pub async fn hof_show(
    State(app): State<AppState>,
    Extension(ctx): Extension<RequestContext>,
) -> Response {
    let heroes = match pq::load_hall_of_fame(&app.pool).await {
        Ok(rows) => rows
            .into_iter()
            .map(|r| HofHero {
                hero_name: r.oldname,
                hero_id: r.heroid,
                current_player_id: if r.newid > 0 { Some(r.newid) } else { None },
                race: r.herorace,
            })
            .collect(),
        Err(e) => {
            tracing::error!(error = %e, "hof_show: load_hall_of_fame");
            vec![]
        }
    };

    let meta = PageMeta::titled("Galeria Bohaterów").with_back_link("/city", "Miasto");
    let base = app.templates.build_context(&ctx, &meta);

    app.templates
        .render_value("hall_of_fame.html", &HofPage { base, heroes })
}

// ---------------------------------------------------------------------------
// GET /hall-of-fame/machines
// ---------------------------------------------------------------------------

pub async fn hof_machines_show(
    State(app): State<AppState>,
    Extension(ctx): Extension<RequestContext>,
) -> Response {
    let machines = match pq::load_hall_of_machines(&app.pool).await {
        Ok(rows) => rows
            .into_iter()
            .map(|r| HofMachine {
                tribe_name: r.tribe,
                leader_name: r.leader,
                build_date: r.bdate,
            })
            .collect(),
        Err(e) => {
            tracing::error!(error = %e, "hof_machines_show: load_hall_of_machines");
            vec![]
        }
    };

    let meta = PageMeta::titled("Galeria Machin").with_back_link("/city", "Miasto");
    let base = app.templates.build_context(&ctx, &meta);

    app.templates
        .render_value("hall_of_machines.html", &HofMachinesPage { base, machines })
}

// ---------------------------------------------------------------------------
// GET /action-points
// ---------------------------------------------------------------------------

pub async fn ap_show(
    State(app): State<AppState>,
    Extension(ctx): Extension<RequestContext>,
) -> Response {
    let Some(ref user) = ctx.session_user else {
        return crate::page::redirect("/login");
    };

    #[allow(clippy::cast_possible_truncation)]
    let player_id = user.id as i32;

    let player_row = match load_player(&app, player_id).await {
        Ok(r) => r,
        Err(resp) => return resp,
    };

    if player_row.race.is_empty() || player_row.class.is_empty() {
        return error_page(&app, &ctx, "Musisz najpierw wybrać rasę i klasę.");
    }

    let catalog = match pq::load_bonus_catalog(&app.pool).await {
        Ok(c) => c,
        Err(e) => {
            tracing::error!(error = %e, "ap_show: load_bonus_catalog");
            return server_error();
        }
    };

    let bonuses = match pq::load_bonuses(&app.pool, player_id).await {
        Ok(b) => b,
        Err(e) => {
            tracing::error!(error = %e, "ap_show: load_bonuses");
            return server_error();
        }
    };

    // Convert catalog rows to domain entries
    let catalog_entries: Vec<mutations::BonusCatalogEntry> = catalog
        .iter()
        .map(|r| mutations::BonusCatalogEntry {
            id: r.id,
            name: r.name.clone(),
            cost: r.cost,
            max_levels: i32::from(r.levels),
            trigger_key: r.trigger_key.clone(),
            bonus_magnitude: i32::from(r.bonus),
            race_restriction: r.race.clone(),
            class_restriction: r.class_restriction.clone(),
        })
        .collect();

    let available = mutations::available_bonuses(
        &player_row.race,
        &player_row.class,
        &catalog_entries,
        &bonuses,
    );

    let available_bonuses: Vec<ApBonusEntry> = available
        .iter()
        .map(|ab| {
            let desc = catalog
                .iter()
                .find(|r| r.id == ab.entry.id)
                .map(|r| r.description.clone())
                .unwrap_or_default();
            ApBonusEntry {
                id: ab.entry.id,
                name: ab.entry.name.clone(),
                description: desc,
                current_level: ab.current_level,
                max_levels: ab.entry.max_levels,
                cost: ab.cost,
            }
        })
        .collect();

    let meta = PageMeta::titled("Punkty Astralne").with_back_link("/stats", "Statystyki");
    let base = app.templates.build_context(&ctx, &meta);

    app.templates.render_value(
        "action_points.html",
        &ApPage {
            base,
            ap: player_row.ap,
            available_bonuses,
        },
    )
}

// ---------------------------------------------------------------------------
// POST /action-points/buy
// ---------------------------------------------------------------------------

pub async fn ap_buy(
    State(app): State<AppState>,
    Extension(ctx): Extension<RequestContext>,
    Form(form): Form<ApBuyForm>,
) -> Response {
    let Some(ref user) = ctx.session_user else {
        return crate::page::redirect("/login");
    };

    #[allow(clippy::cast_possible_truncation)]
    let player_id = user.id as i32;

    if form.confirm.as_deref() != Some("Y") {
        return error_page(&app, &ctx, "Musisz potwierdzić zakup.");
    }

    let player_row = match load_player(&app, player_id).await {
        Ok(r) => r,
        Err(resp) => return resp,
    };

    let catalog = match pq::load_bonus_catalog(&app.pool).await {
        Ok(c) => c,
        Err(e) => {
            tracing::error!(error = %e, "ap_buy: load_bonus_catalog");
            return server_error();
        }
    };

    let Some(catalog_row) = catalog.iter().find(|r| r.id == form.bonus_id) else {
        return error_page(&app, &ctx, "Nieznana premia.");
    };

    let catalog_entry = mutations::BonusCatalogEntry {
        id: catalog_row.id,
        name: catalog_row.name.clone(),
        cost: catalog_row.cost,
        max_levels: i32::from(catalog_row.levels),
        trigger_key: catalog_row.trigger_key.clone(),
        bonus_magnitude: i32::from(catalog_row.bonus),
        race_restriction: catalog_row.race.clone(),
        class_restriction: catalog_row.class_restriction.clone(),
    };

    let mut bonuses = match pq::load_bonuses(&app.pool, player_id).await {
        Ok(b) => b,
        Err(e) => {
            tracing::error!(error = %e, "ap_buy: load_bonuses");
            return server_error();
        }
    };

    let result = match mutations::validate_ap_purchase(
        &player_row.race,
        &player_row.class,
        player_row.ap,
        &catalog_entry,
        &bonuses,
    ) {
        Ok(r) => r,
        Err(mutations::ApBonusError::NoRaceOrClass) => {
            return error_page(&app, &ctx, "Musisz najpierw wybrać rasę i klasę.");
        }
        Err(mutations::ApBonusError::BonusNotFound) => {
            return error_page(&app, &ctx, "Nieznana premia.");
        }
        Err(mutations::ApBonusError::RaceNotAllowed) => {
            return error_page(&app, &ctx, "Ta premia nie jest dostępna dla twojej rasy.");
        }
        Err(mutations::ApBonusError::ClassNotAllowed) => {
            return error_page(&app, &ctx, "Ta premia nie jest dostępna dla twojej klasy.");
        }
        Err(mutations::ApBonusError::AtMaxLevel) => {
            return error_page(&app, &ctx, "Ta premia jest już na maksymalnym poziomie.");
        }
        Err(mutations::ApBonusError::InsufficientAp { cost }) => {
            return error_page(
                &app,
                &ctx,
                &format!("Potrzebujesz {cost} AP aby kupić tę premię."),
            );
        }
    };

    // Apply: update bonuses in memory
    if result.is_new {
        bonuses.push(vallheru_domain::player::bonuses::PlayerBonus {
            id: 0,
            catalog_id: result.catalog_id,
            bonus_name: result.trigger_key.clone(),
            value: 1,
            duration: result.bonus_magnitude,
        });
    } else if let Some(b) = bonuses
        .iter_mut()
        .find(|b| b.catalog_id == result.catalog_id)
    {
        b.value = result.new_level;
    }

    // Persist
    if let Err(e) = pq::deduct_ap(&app.pool, player_id, result.ap_cost).await {
        tracing::error!(error = %e, "ap_buy: deduct_ap");
        return server_error();
    }
    if let Err(e) = pq::save_bonuses(&app.pool, player_id, &bonuses).await {
        tracing::error!(error = %e, "ap_buy: save_bonuses");
        return server_error();
    }

    crate::page::redirect_after_post("/action-points")
}

// ---------------------------------------------------------------------------
// GET /character/race
// ---------------------------------------------------------------------------

pub async fn race_show(
    State(app): State<AppState>,
    Extension(ctx): Extension<RequestContext>,
    Query(query): Query<RaceQuery>,
) -> Response {
    let Some(ref user) = ctx.session_user else {
        return crate::page::redirect("/login");
    };

    #[allow(clippy::cast_possible_truncation)]
    let player_id = user.id as i32;

    let player_row = match load_player(&app, player_id).await {
        Ok(r) => r,
        Err(resp) => return resp,
    };

    if !player_row.race.is_empty() {
        return error_page(&app, &ctx, "Już wybrałeś rasę.");
    }

    // Show info for a specific race
    if let Some(ref slug) = query.race {
        let Some(race) = parse_race_slug(slug) else {
            return error_page(&app, &ctx, "Nieznana rasa.");
        };

        let (description, stat_bonuses) = race_info(&race);

        let meta = PageMeta::titled("Wybór rasy").with_back_link("/character/race", "Wróć");
        let base = app.templates.build_context(&ctx, &meta);

        return app.templates.render_value(
            "race_select.html",
            &RacePage {
                base,
                section: "info".to_owned(),
                race_slug: slug.clone(),
                race_description: description,
                stat_bonuses,
            },
        );
    }

    // Show race list
    let meta = PageMeta::titled("Wybór rasy").with_back_link("/stats", "Statystyki");
    let base = app.templates.build_context(&ctx, &meta);

    app.templates.render_value(
        "race_select.html",
        &RacePage {
            base,
            section: "list".to_owned(),
            race_slug: String::new(),
            race_description: String::new(),
            stat_bonuses: vec![],
        },
    )
}

// ---------------------------------------------------------------------------
// POST /character/race
// ---------------------------------------------------------------------------

pub async fn race_select(
    State(app): State<AppState>,
    Extension(ctx): Extension<RequestContext>,
    Form(form): Form<RaceForm>,
) -> Response {
    let Some(ref user) = ctx.session_user else {
        return crate::page::redirect("/login");
    };

    #[allow(clippy::cast_possible_truncation)]
    let player_id = user.id as i32;

    let player_row = match load_player(&app, player_id).await {
        Ok(r) => r,
        Err(resp) => return resp,
    };

    let Some(race) = parse_race_slug(&form.race) else {
        return error_page(&app, &ctx, "Nieznana rasa.");
    };

    let mut stats = match pq::load_stats(&app.pool, player_id).await {
        Ok(s) => s,
        Err(e) => {
            tracing::error!(error = %e, "race_select: load_stats");
            return server_error();
        }
    };

    if let Err(mutations::RaceSelectionError::AlreadyChosen) =
        mutations::select_race(&player_row.race, &race, &mut stats)
    {
        return error_page(&app, &ctx, "Już wybrałeś rasę.");
    }

    // Persist race on player row
    if let Err(e) = pq::set_race(&app.pool, player_id, race.to_db()).await {
        tracing::error!(error = %e, "race_select: set_race");
        return server_error();
    }

    // Persist updated stats
    if let Err(e) = pq::save_stats(&app.pool, player_id, &stats).await {
        tracing::error!(error = %e, "race_select: save_stats");
        return server_error();
    }

    crate::page::redirect_after_post("/stats")
}

// ---------------------------------------------------------------------------
// GET /character/class
// ---------------------------------------------------------------------------

pub async fn class_show(
    State(app): State<AppState>,
    Extension(ctx): Extension<RequestContext>,
    Query(query): Query<ClassQuery>,
) -> Response {
    let Some(ref user) = ctx.session_user else {
        return crate::page::redirect("/login");
    };

    #[allow(clippy::cast_possible_truncation)]
    let player_id = user.id as i32;

    let player_row = match load_player(&app, player_id).await {
        Ok(r) => r,
        Err(resp) => return resp,
    };

    if !player_row.class.is_empty() {
        return error_page(&app, &ctx, "Już wybrałeś klasę.");
    }

    if let Some(ref slug) = query.class {
        let Some(class) = parse_class_slug(slug) else {
            return error_page(&app, &ctx, "Nieznana klasa.");
        };

        let (description, stat_bonuses) = class_info(&class);

        let meta = PageMeta::titled("Wybór klasy").with_back_link("/character/class", "Wróć");
        let base = app.templates.build_context(&ctx, &meta);

        return app.templates.render_value(
            "class_select.html",
            &ClassPage {
                base,
                section: "info".to_owned(),
                class_slug: slug.clone(),
                class_description: description,
                stat_bonuses,
            },
        );
    }

    let meta = PageMeta::titled("Wybór klasy").with_back_link("/stats", "Statystyki");
    let base = app.templates.build_context(&ctx, &meta);

    app.templates.render_value(
        "class_select.html",
        &ClassPage {
            base,
            section: "list".to_owned(),
            class_slug: String::new(),
            class_description: String::new(),
            stat_bonuses: vec![],
        },
    )
}

// ---------------------------------------------------------------------------
// POST /character/class
// ---------------------------------------------------------------------------

pub async fn class_select(
    State(app): State<AppState>,
    Extension(ctx): Extension<RequestContext>,
    Form(form): Form<ClassForm>,
) -> Response {
    let Some(ref user) = ctx.session_user else {
        return crate::page::redirect("/login");
    };

    #[allow(clippy::cast_possible_truncation)]
    let player_id = user.id as i32;

    let player_row = match load_player(&app, player_id).await {
        Ok(r) => r,
        Err(resp) => return resp,
    };

    let Some(class) = parse_class_slug(&form.class) else {
        return error_page(&app, &ctx, "Nieznana klasa.");
    };

    let mut stats = match pq::load_stats(&app.pool, player_id).await {
        Ok(s) => s,
        Err(e) => {
            tracing::error!(error = %e, "class_select: load_stats");
            return server_error();
        }
    };

    if let Err(mutations::ClassSelectionError::AlreadyChosen) =
        mutations::select_class(&player_row.class, &class, &mut stats)
    {
        return error_page(&app, &ctx, "Już wybrałeś klasę.");
    }

    if let Err(e) = pq::set_class(&app.pool, player_id, class.to_db()).await {
        tracing::error!(error = %e, "class_select: set_class");
        return server_error();
    }

    if let Err(e) = pq::save_stats(&app.pool, player_id, &stats).await {
        tracing::error!(error = %e, "class_select: save_stats");
        return server_error();
    }

    crate::page::redirect_after_post("/stats")
}

// ---------------------------------------------------------------------------
// Helpers
// ---------------------------------------------------------------------------

async fn load_player(app: &AppState, player_id: i32) -> Result<pq::PlayerRow, Response> {
    match pq::find_player_by_id(&app.pool, player_id).await {
        Ok(Some(row)) => Ok(row),
        Ok(None) => Err(crate::page::redirect("/login")),
        Err(e) => {
            tracing::error!(error = %e, "character: load_player failed");
            Err(server_error())
        }
    }
}

fn error_page(app: &AppState, ctx: &RequestContext, message: &str) -> Response {
    let meta = PageMeta::titled("Błąd").with_flash(Flash {
        kind: FlashKind::Error,
        message: message.to_owned(),
    });
    let base = app.templates.build_context(ctx, &meta);
    app.templates.render("error.html", &base)
}

fn server_error() -> Response {
    use axum::response::IntoResponse;
    (
        axum::http::StatusCode::INTERNAL_SERVER_ERROR,
        "Internal error",
    )
        .into_response()
}

/// Apply AP and HP rewards from stat level-ups.
async fn apply_levelup_rewards(
    pool: &sqlx::PgPool,
    player_id: i32,
    ap_gain: i32,
    hp_gain: i32,
) -> Result<(), sqlx::Error> {
    sqlx::query(
        "UPDATE players SET ap = ap + $1, max_hp = max_hp + $2, hp = hp + $2 WHERE id = $3",
    )
    .bind(ap_gain)
    .bind(hp_gain)
    .bind(player_id)
    .execute(pool)
    .await?;
    Ok(())
}

fn parse_race_slug(slug: &str) -> Option<Race> {
    match slug {
        "human" => Some(Race::Human),
        "elf" => Some(Race::Elf),
        "dwarf" => Some(Race::Dwarf),
        "hobbit" => Some(Race::Hobbit),
        "lizardman" => Some(Race::Lizardman),
        "gnome" => Some(Race::Gnome),
        _ => None,
    }
}

fn parse_class_slug(slug: &str) -> Option<Class> {
    match slug {
        "warrior" => Some(Class::Warrior),
        "mage" => Some(Class::Mage),
        "craftsman" => Some(Class::Craftsman),
        "barbarian" => Some(Class::Barbarian),
        "thief" => Some(Class::Thief),
        _ => None,
    }
}

/// Description and stat bonuses for a race (for the selection page).
#[allow(clippy::too_many_lines)]
fn race_info(race: &Race) -> (String, Vec<RaceStatBonus>) {
    let labels = [
        "Siła",
        "Zręczność",
        "Kondycja",
        "Szybkość",
        "Inteligencja",
        "Siła Woli",
    ];
    match race {
        Race::Human => (
            "Ludzie są rasą najbardziej wszechstronną. Nie posiadają specjalnych zalet ani wad."
                .to_owned(),
            labels
                .iter()
                .map(|l| RaceStatBonus {
                    label: (*l).to_owned(),
                    value: 3,
                    max_label: "(max 50)".to_owned(),
                })
                .collect(),
        ),
        Race::Elf => (
            "Elfy są zwinne i szybkie, ale słabsze fizycznie od innych ras.".to_owned(),
            vec![
                RaceStatBonus {
                    label: "Siła".to_owned(),
                    value: 2,
                    max_label: "(max 40)".to_owned(),
                },
                RaceStatBonus {
                    label: "Zręczność".to_owned(),
                    value: 4,
                    max_label: "(max 60)".to_owned(),
                },
                RaceStatBonus {
                    label: "Kondycja".to_owned(),
                    value: 3,
                    max_label: "(max 50)".to_owned(),
                },
                RaceStatBonus {
                    label: "Szybkość".to_owned(),
                    value: 3,
                    max_label: "(max 55)".to_owned(),
                },
                RaceStatBonus {
                    label: "Inteligencja".to_owned(),
                    value: 3,
                    max_label: "(max 50)".to_owned(),
                },
                RaceStatBonus {
                    label: "Siła Woli".to_owned(),
                    value: 3,
                    max_label: "(max 50)".to_owned(),
                },
            ],
        ),
        Race::Dwarf => (
            "Krasnoludy są silne i wytrzymałe, ale powolne i niezgrabne.".to_owned(),
            vec![
                RaceStatBonus {
                    label: "Siła".to_owned(),
                    value: 4,
                    max_label: "(max 60)".to_owned(),
                },
                RaceStatBonus {
                    label: "Zręczność".to_owned(),
                    value: 2,
                    max_label: "(max 40)".to_owned(),
                },
                RaceStatBonus {
                    label: "Kondycja".to_owned(),
                    value: 4,
                    max_label: "(max 60)".to_owned(),
                },
                RaceStatBonus {
                    label: "Szybkość".to_owned(),
                    value: 2,
                    max_label: "(max 40)".to_owned(),
                },
                RaceStatBonus {
                    label: "Inteligencja".to_owned(),
                    value: 3,
                    max_label: "(max 50)".to_owned(),
                },
                RaceStatBonus {
                    label: "Siła Woli".to_owned(),
                    value: 3,
                    max_label: "(max 50)".to_owned(),
                },
            ],
        ),
        Race::Hobbit => (
            "Hobbity są zręczne i zwinne, ale niezbyt silne.".to_owned(),
            vec![
                RaceStatBonus {
                    label: "Siła".to_owned(),
                    value: 2,
                    max_label: "(max 40)".to_owned(),
                },
                RaceStatBonus {
                    label: "Zręczność".to_owned(),
                    value: 4,
                    max_label: "(max 60)".to_owned(),
                },
                RaceStatBonus {
                    label: "Kondycja".to_owned(),
                    value: 2,
                    max_label: "(max 45)".to_owned(),
                },
                RaceStatBonus {
                    label: "Szybkość".to_owned(),
                    value: 4,
                    max_label: "(max 60)".to_owned(),
                },
                RaceStatBonus {
                    label: "Inteligencja".to_owned(),
                    value: 3,
                    max_label: "(max 50)".to_owned(),
                },
                RaceStatBonus {
                    label: "Siła Woli".to_owned(),
                    value: 3,
                    max_label: "(max 50)".to_owned(),
                },
            ],
        ),
        Race::Lizardman => (
            "Jaszczuroczłeki są silne i szybkie, ale mające problem z nauką.".to_owned(),
            vec![
                RaceStatBonus {
                    label: "Siła".to_owned(),
                    value: 4,
                    max_label: "(max 60)".to_owned(),
                },
                RaceStatBonus {
                    label: "Zręczność".to_owned(),
                    value: 3,
                    max_label: "(max 50)".to_owned(),
                },
                RaceStatBonus {
                    label: "Kondycja".to_owned(),
                    value: 3,
                    max_label: "(max 50)".to_owned(),
                },
                RaceStatBonus {
                    label: "Szybkość".to_owned(),
                    value: 4,
                    max_label: "(max 60)".to_owned(),
                },
                RaceStatBonus {
                    label: "Inteligencja".to_owned(),
                    value: 2,
                    max_label: "(max 40)".to_owned(),
                },
                RaceStatBonus {
                    label: "Siła Woli".to_owned(),
                    value: 3,
                    max_label: "(max 50)".to_owned(),
                },
            ],
        ),
        Race::Gnome => (
            "Gnomy są inteligentne i zręczne, ale słabe fizycznie.".to_owned(),
            vec![
                RaceStatBonus {
                    label: "Siła".to_owned(),
                    value: 2,
                    max_label: "(max 40)".to_owned(),
                },
                RaceStatBonus {
                    label: "Zręczność".to_owned(),
                    value: 4,
                    max_label: "(max 55)".to_owned(),
                },
                RaceStatBonus {
                    label: "Kondycja".to_owned(),
                    value: 2,
                    max_label: "(max 40)".to_owned(),
                },
                RaceStatBonus {
                    label: "Szybkość".to_owned(),
                    value: 3,
                    max_label: "(max 50)".to_owned(),
                },
                RaceStatBonus {
                    label: "Inteligencja".to_owned(),
                    value: 4,
                    max_label: "(max 55)".to_owned(),
                },
                RaceStatBonus {
                    label: "Siła Woli".to_owned(),
                    value: 2,
                    max_label: "(max 40)".to_owned(),
                },
            ],
        ),
    }
}

/// Description and stat bonuses for a class (for the selection page).
#[allow(clippy::too_many_lines)]
fn class_info(class: &Class) -> (String, Vec<ClassStatBonus>) {
    match class {
        Class::Warrior => (
            "Wojownik to klasa walcząca mieczem i tarczą. Silny i wytrzymały.".to_owned(),
            vec![
                ClassStatBonus {
                    label: "Siła".to_owned(),
                    value: "+1".to_owned(),
                },
                ClassStatBonus {
                    label: "Zręczność".to_owned(),
                    value: "+1".to_owned(),
                },
                ClassStatBonus {
                    label: "Kondycja".to_owned(),
                    value: "+1".to_owned(),
                },
                ClassStatBonus {
                    label: "Inteligencja".to_owned(),
                    value: "-1".to_owned(),
                },
                ClassStatBonus {
                    label: "HP/poziom kondycji".to_owned(),
                    value: "5".to_owned(),
                },
            ],
        ),
        Class::Mage => (
            "Mag to klasa magiczna. Posiada potężne zaklęcia, ale jest fizycznie słaby.".to_owned(),
            vec![
                ClassStatBonus {
                    label: "Inteligencja".to_owned(),
                    value: "+1".to_owned(),
                },
                ClassStatBonus {
                    label: "Siła Woli".to_owned(),
                    value: "+1".to_owned(),
                },
                ClassStatBonus {
                    label: "HP/poziom kondycji".to_owned(),
                    value: "3".to_owned(),
                },
            ],
        ),
        Class::Craftsman => (
            "Rzemieślnik to klasa rzemieślnicza. Wytwarza przedmioty.".to_owned(),
            vec![ClassStatBonus {
                label: "HP/poziom kondycji".to_owned(),
                value: "2".to_owned(),
            }],
        ),
        Class::Barbarian => (
            "Barbarzyńca to potężny wojownik. Silny, zwinny i wytrzymały.".to_owned(),
            vec![
                ClassStatBonus {
                    label: "Siła".to_owned(),
                    value: "+1".to_owned(),
                },
                ClassStatBonus {
                    label: "Zręczność".to_owned(),
                    value: "+1".to_owned(),
                },
                ClassStatBonus {
                    label: "Kondycja".to_owned(),
                    value: "+1".to_owned(),
                },
                ClassStatBonus {
                    label: "Inteligencja".to_owned(),
                    value: "-1".to_owned(),
                },
                ClassStatBonus {
                    label: "Siła Woli".to_owned(),
                    value: "+1".to_owned(),
                },
                ClassStatBonus {
                    label: "HP/poziom kondycji".to_owned(),
                    value: "5".to_owned(),
                },
            ],
        ),
        Class::Thief => (
            "Złodziej to klasa o niskiej sile, ale bardzo zręczna i szybka.".to_owned(),
            vec![
                ClassStatBonus {
                    label: "Zręczność".to_owned(),
                    value: "+1".to_owned(),
                },
                ClassStatBonus {
                    label: "Kondycja".to_owned(),
                    value: "-1".to_owned(),
                },
                ClassStatBonus {
                    label: "Szybkość".to_owned(),
                    value: "+1".to_owned(),
                },
                ClassStatBonus {
                    label: "Inteligencja".to_owned(),
                    value: "+1".to_owned(),
                },
                ClassStatBonus {
                    label: "Siła Woli".to_owned(),
                    value: "-1".to_owned(),
                },
                ClassStatBonus {
                    label: "HP/poziom kondycji".to_owned(),
                    value: "4".to_owned(),
                },
            ],
        ),
    }
}
