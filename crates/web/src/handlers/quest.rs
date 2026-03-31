//! Quest, mission, and labyrinth handlers.
//!
//! Ported from `grid.php`, `chronicle.php`, `mission.php`, `thieves.php`,
//! and `maze.php`. Uses domain models from `vallheru_domain::quest` and
//! data queries from `vallheru_data::queries::{quest, mission}`.

use axum::response::Response;
use axum::{Extension, Form, extract::State};
use rand::Rng;
use std::fmt::Write;

use crate::middleware::context::RequestContext;
use crate::page::{Flash, FlashKind, PageMeta};
use crate::state::AppState;
use vallheru_data::queries::{mission as mq, player as pq, quest as qq};
use vallheru_domain::quest::maze;
use vallheru_domain::quest::mission::{self, MissionType};
use vallheru_domain::quest::mission_loader;
use vallheru_domain::quest::quest_action;

// =========================================================================
// View models
// =========================================================================

#[derive(serde::Serialize)]
struct LabyrinthView {
    #[serde(flatten)]
    base: crate::render::RenderContext,
    /// Labyrinth entrance text shown before exploring.
    lab_info: String,
    /// Maximum affordable explorations.
    max_amount: i32,
}

#[derive(serde::Deserialize)]
pub struct ExploreForm {
    amount: Option<i32>,
}

#[derive(serde::Serialize)]
struct LabyrinthResultView {
    #[serde(flatten)]
    base: crate::render::RenderContext,
    result_text: String,
    quest_triggered: bool,
    quest_id: i32,
}

#[derive(serde::Serialize)]
struct ChronicleListView {
    #[serde(flatten)]
    base: crate::render::RenderContext,
    info: String,
    stories: Vec<ChronicleLinkView>,
    old_stories: Vec<ChronicleLinkView>,
    other_stories: Vec<ChronicleLinkView>,
}

#[derive(serde::Serialize)]
struct ChronicleLinkView {
    id: i32,
    title: String,
}

#[derive(serde::Serialize)]
struct ChronicleDetailView {
    #[serde(flatten)]
    base: crate::render::RenderContext,
    mission_id: i32,
    name: String,
    intro: String,
    can_start: bool,
}

#[derive(serde::Deserialize)]
pub struct ChronicleStartForm {
    qid: i32,
}

#[derive(serde::Serialize)]
struct MissionRoomView {
    #[serde(flatten)]
    base: crate::render::RenderContext,
    text: String,
    options: Vec<MissionOptionView>,
    finished: bool,
    finish_link: String,
}

#[derive(serde::Serialize)]
struct MissionOptionView {
    target: String,
    label: String,
}

#[derive(serde::Deserialize)]
pub struct MissionActionForm {
    action: Option<String>,
}

#[derive(serde::Serialize)]
struct MazeView {
    #[serde(flatten)]
    base: crate::render::RenderContext,
    maze_info: String,
    max_amount: i32,
}

#[derive(serde::Serialize)]
struct MazeResultView {
    #[serde(flatten)]
    base: crate::render::RenderContext,
    result_text: String,
}

/// Pre-generated random rolls for one labyrinth step (avoids holding RNG across await).
struct StepRoll {
    outcome: maze::LabyrinthStepOutcome,
    gold_roll: i32,
    mithril_roll: i32,
    quest_roll: i32,
    map_roll: i32,
}

// =========================================================================
// Labyrinth (grid.php)
// =========================================================================

/// GET /labyrinth — show labyrinth entrance.
pub async fn labyrinth_show(
    State(app): State<AppState>,
    Extension(ctx): Extension<RequestContext>,
) -> Response {
    let (player, _user) = match require_player(&app, &ctx).await {
        Ok(v) => v,
        Err(r) => return r,
    };

    if player.location != "Altara" && player.location != "Podróż" {
        return err(&app, &ctx, "Nie znajdujesz się w odpowiednim miejscu.");
    }

    // Check if player has an active quest — redirect to quest view.
    if let Ok(actions) = qq::find_all_quest_actions(&app.pool, player.id).await {
        if quest_action::has_active_quest(
            &actions
                .iter()
                .map(|r| quest_action::QuestAction {
                    id: r.id,
                    player_id: r.player,
                    quest_id: r.quest,
                    action: r.action.clone(),
                })
                .collect::<Vec<_>>(),
        ) {
            return crate::page::redirect("/quest");
        }
    }

    #[allow(clippy::cast_possible_truncation)]
    let max_amount = (player.energy / maze::LABYRINTH_ENERGY_COST).floor() as i32;

    let meta = PageMeta::titled("Labirynt");
    let base = app.templates.build_context(&ctx, &meta);
    let view = LabyrinthView {
        base,
        lab_info: "Idziesz starą drogą w Zachodniej Części miasta. Gwarna część dzielnicy \
                   pozostała daleko za Tobą. Mijasz stare opuszczone kamieniczki. Wielkie \
                   wrota w kamiennej zrujnowanej obudowie. Przed zniszczonymi wrotami \
                   siedzi starzec. Czy chcesz tam wejść?"
            .into(),
        max_amount,
    };
    app.templates.render_value("labyrinth.html", &view)
}

/// POST /labyrinth/explore — batch-explore the labyrinth.
#[allow(clippy::too_many_lines)]
pub async fn labyrinth_explore(
    State(app): State<AppState>,
    Extension(ctx): Extension<RequestContext>,
    Form(form): Form<ExploreForm>,
) -> Response {
    let (player, _user) = match require_player(&app, &ctx).await {
        Ok(v) => v,
        Err(r) => return r,
    };

    let requested = form.amount.unwrap_or(0);

    let steps = match maze::can_explore_labyrinth(
        &player.location,
        player.hp,
        player.energy,
        requested,
        false, // already filtered active quest in show
    ) {
        Ok(s) => s,
        Err(maze::LabyrinthError::NotEnoughEnergy) => {
            return err(
                &app,
                &ctx,
                "Nie masz wystarczająco energii aby zwiedzać labirynt.",
            );
        }
        Err(maze::LabyrinthError::Dead) => {
            return err(
                &app,
                &ctx,
                "Nie możesz zwiedzać labiryntu ponieważ jesteś martwy!",
            );
        }
        Err(_) => return err(&app, &ctx, "Zapomnij o tym."),
    };

    // Pre-generate all random rolls so the RNG doesn't live across .await.
    let rolls: Vec<StepRoll> = {
        let mut rng = rand::thread_rng();
        (0..steps)
            .map(|_| StepRoll {
                outcome: maze::LabyrinthStepOutcome::from_roll(rng.gen_range(1..=11)),
                gold_roll: rng.gen_range(1..=100),
                mithril_roll: rng.gen_range(1..=3),
                quest_roll: rng.gen_range(1..=5),
                map_roll: rng.gen_range(1..=50),
            })
            .collect()
    };

    let mut gold: i32 = 0;
    let mut mithril: i32 = 0;
    let mut energy_bonus: i32 = 0;
    let mut maps_found: i32 = 0;
    let mut quest_triggered: Option<i32> = None;

    for roll in &rolls {
        match roll.outcome {
            maze::LabyrinthStepOutcome::Gold => gold += roll.gold_roll,
            maze::LabyrinthStepOutcome::Mithril => mithril += roll.mithril_roll,
            maze::LabyrinthStepOutcome::EnergyLoss => energy_bonus += 1,
            maze::LabyrinthStepOutcome::QuestChance => {
                if roll.quest_roll == 5 && quest_triggered.is_none() {
                    quest_triggered = find_available_quest(&app, player.id).await;
                }
                if quest_triggered.is_none() {
                    maps_found += try_find_map(&app, &player, roll.map_roll).await;
                }
            }
            maze::LabyrinthStepOutcome::Nothing => {}
        }
        if quest_triggered.is_some() {
            break;
        }
    }

    // Compute energy cost.
    let energy_cost = f64::from(steps) * maze::LABYRINTH_ENERGY_COST;
    let energy_delta = (f64::from(energy_bonus)) - energy_cost;

    // Build result text.
    let gender_suffix = if player.gender.as_deref() == Some("F") {
        "aś"
    } else {
        "eś"
    };

    let mut text = String::new();
    if gold > 0 || mithril > 0 || energy_bonus > 0 || maps_found > 0 {
        let _ = write!(
            text,
            "Podczas swojej wędrówki znalazł{gender_suffix}:<br />"
        );
        if gold > 0 {
            let _ = write!(text, "{gold} sztuk złota<br />");
        }
        if mithril > 0 {
            let _ = write!(text, "{mithril} sztuk mithrilu<br />");
        }
        if energy_bonus > 0 {
            let _ = write!(text, "{energy_bonus} razy źródełko<br />");
        }
        if maps_found > 0 {
            let _ = write!(text, "{maps_found} kawałków mapy<br />");
        }
    } else {
        let _ = write!(
            text,
            "Wędrował{gender_suffix} jakiś czas ale nic ciekawego nie znalazł{gender_suffix}.<br />"
        );
    }
    let _ = write!(
        text,
        "Zużył{gender_suffix} na to {energy_cost:.1} energii.<br />"
    );

    // Update player stats.
    #[allow(clippy::cast_possible_truncation)]
    let maps_i16 = maps_found as i16;
    if let Err(e) = sqlx::query(
        "UPDATE players SET credits = credits + $1, platinum = platinum + $2, \
         energy = energy + $3, maps = maps + $4 WHERE id = $5",
    )
    .bind(gold)
    .bind(mithril)
    .bind(energy_delta)
    .bind(maps_i16)
    .bind(player.id)
    .execute(&app.pool)
    .await
    {
        tracing::error!(error = %e, "labyrinth update failed");
        return server_error();
    }

    let meta = PageMeta::titled("Labirynt");
    let base = app.templates.build_context(&ctx, &meta);
    let view = LabyrinthResultView {
        base,
        result_text: text,
        quest_triggered: quest_triggered.is_some(),
        quest_id: quest_triggered.unwrap_or(0),
    };
    app.templates.render_value("labyrinth_result.html", &view)
}

// =========================================================================
// Chronicle (chronicle.php)
// =========================================================================

/// GET /chronicle — show chronicle mission catalog.
pub async fn chronicle_show(
    State(app): State<AppState>,
    Extension(ctx): Extension<RequestContext>,
) -> Response {
    let (player, _user) = match require_player(&app, &ctx).await {
        Ok(v) => v,
        Err(r) => return r,
    };

    if player.location != "Altara" && player.location != "Ardulith" {
        return err(&app, &ctx, "Nie znajdujesz się w mieście.");
    }

    let missions = mq::list_chronicle_missions_at(&app.pool, &player.location)
        .await
        .unwrap_or_default();

    let mut stories = Vec::new();
    let mut old_stories = Vec::new();
    let mut other_stories = Vec::new();

    for m in missions {
        let link = ChronicleLinkView {
            id: m.id,
            title: m.short_desc.clone(),
        };
        match m.mission_type {
            MissionType::MainQuest => {
                if m.chapter_required <= player.chapter {
                    stories.push(link);
                }
            }
            MissionType::OldStory => old_stories.push(link),
            _ => other_stories.push(link),
        }
    }

    let meta = PageMeta::titled("Kronika");
    let base = app.templates.build_context(&ctx, &meta);
    let view = ChronicleListView {
        base,
        info: "Wchodzisz do niewielkiego budynku. Na jego środku stoi kamienny pedestał \
               a na nim olbrzymia księga. Księga zaczyna lekko lśnić białym blaskiem i \
               otwiera się na spisie treści."
            .into(),
        stories,
        old_stories,
        other_stories,
    };
    app.templates.render_value("chronicle.html", &view)
}

/// GET /chronicle/:id — show mission detail.
pub async fn chronicle_detail(
    State(app): State<AppState>,
    Extension(ctx): Extension<RequestContext>,
    axum::extract::Path(mission_id): axum::extract::Path<i32>,
) -> Response {
    let (player, _user) = match require_player(&app, &ctx).await {
        Ok(v) => v,
        Err(r) => return r,
    };

    let Some(m) = mq::find_chronicle_mission_by_id(&app.pool, mission_id)
        .await
        .ok()
        .flatten()
    else {
        return err(&app, &ctx, "Nie ma takiej przygody.");
    };

    if m.location != player.location {
        return err(&app, &ctx, "Ta przygoda rozpoczyna się w innym mieście.");
    }

    let can_start = mission::can_start_chronicle_mission(&mission::StartMissionCheck {
        player_chapter: player.chapter,
        mission_chapter: m.chapter_required,
        mission_type: m.mission_type,
        player_location: &player.location,
        mission_location: &m.location,
        player_hp: player.hp,
        player_energy: player.energy,
        craft_missions_remaining: player.craft_mission,
        has_active_mission: mq::find_active_mission(&app.pool, player.id)
            .await
            .ok()
            .flatten()
            .is_some(),
    })
    .is_ok();

    let meta = PageMeta::titled(&m.name);
    let base = app.templates.build_context(&ctx, &meta);
    let view = ChronicleDetailView {
        base,
        mission_id: m.id,
        name: m.name.clone(),
        intro: m.intro,
        can_start,
    };
    app.templates.render_value("chronicle_detail.html", &view)
}

/// POST /chronicle/start — start a chronicle mission.
pub async fn chronicle_start(
    State(app): State<AppState>,
    Extension(ctx): Extension<RequestContext>,
    Form(form): Form<ChronicleStartForm>,
) -> Response {
    let (player, _user) = match require_player(&app, &ctx).await {
        Ok(v) => v,
        Err(r) => return r,
    };

    let Some(m) = mq::find_chronicle_mission_by_id(&app.pool, form.qid)
        .await
        .ok()
        .flatten()
    else {
        return err(&app, &ctx, "Nie ma takiej przygody.");
    };

    if let Err(_e) = mission::can_start_chronicle_mission(&mission::StartMissionCheck {
        player_chapter: player.chapter,
        mission_chapter: m.chapter_required,
        mission_type: m.mission_type,
        player_location: &player.location,
        mission_location: &m.location,
        player_hp: player.hp,
        player_energy: player.energy,
        craft_missions_remaining: player.craft_mission,
        has_active_mission: mq::find_active_mission(&app.pool, player.id)
            .await
            .ok()
            .flatten()
            .is_some(),
    }) {
        return err(&app, &ctx, "Nie możesz rozpocząć tej przygody.");
    }

    // Find the starting room.
    let Some(start_room) = mq::find_start_room(&app.pool, &m.name).await.ok().flatten() else {
        return err(&app, &ctx, "Nie można znaleźć pokoju startowego przygody.");
    };

    // Generate the first room state.
    let exits = mission_loader::parse_exits(&start_room.raw_exits);
    let mobs = mission_loader::parse_mobs(&start_room.raw_mobs);
    let items = mission_loader::parse_items(&start_room.raw_items);
    let _moreinfo = mission_loader::parse_moreinfo(&start_room.raw_moreinfo);

    let active = vallheru_domain::quest::mission::ActiveMission {
        player_id: player.id,
        current_room_id: start_room.id,
        raw_exits: start_room.raw_exits.clone(),
        raw_mobs: start_room.raw_mobs.clone(),
        raw_items: start_room.raw_items.clone(),
        mission_type: m.mission_type,
        loot_spec: String::new(),
        rooms_remaining: 10,
        successes: 0,
        bonus: 0,
        return_location: player.location.clone(),
        has_target: false,
        raw_moreinfo: start_room.raw_moreinfo.clone(),
    };

    if let Err(e) = mq::insert_active_mission(&app.pool, &active).await {
        tracing::error!(error = %e, "insert active mission failed");
        return server_error();
    }

    // Set player location to travelling.
    if let Err(e) = sqlx::query("UPDATE players SET location = 'Podróż' WHERE id = $1")
        .bind(player.id)
        .execute(&app.pool)
        .await
    {
        tracing::error!(error = %e, "Failed to set player location for mission");
    }

    // Render the first room.
    let actions = mission_loader::collect_room_actions(&exits, &mobs, &items);
    let text = mission_loader::build_room_text(&start_room.text, &mobs, &items);
    let is_terminal = mission_loader::is_terminal_room(&start_room.name);

    let options: Vec<MissionOptionView> = actions
        .iter()
        .map(|(target, label)| MissionOptionView {
            target: target.clone(),
            label: label.clone(),
        })
        .collect();

    let meta = PageMeta::titled("Przygoda");
    let base = app.templates.build_context(&ctx, &meta);
    let view = MissionRoomView {
        base,
        text,
        options,
        finished: is_terminal,
        finish_link: "/city".into(),
    };
    app.templates.render_value("mission.html", &view)
}

// =========================================================================
// Mission room navigation (mission.php)
// =========================================================================

/// POST /mission — advance to the next room in an active mission.
#[allow(clippy::too_many_lines)]
pub async fn mission_advance(
    State(app): State<AppState>,
    Extension(ctx): Extension<RequestContext>,
    Form(form): Form<MissionActionForm>,
) -> Response {
    let (player, _user) = match require_player(&app, &ctx).await {
        Ok(v) => v,
        Err(r) => return r,
    };

    let Some(active) = mq::find_active_mission(&app.pool, player.id)
        .await
        .ok()
        .flatten()
    else {
        return err(&app, &ctx, "Nie znajdujesz się w przygodzie.");
    };

    if player.hp <= 0 {
        // Dead — abort mission and return.
        if let Err(e) = mq::delete_active_mission(&app.pool, player.id).await {
            tracing::error!(error = %e, "Failed to delete active mission (player dead)");
        }
        if let Err(e) = sqlx::query("UPDATE players SET location = $1 WHERE id = $2")
            .bind(&active.return_location)
            .bind(player.id)
            .execute(&app.pool)
            .await
        {
            tracing::error!(error = %e, "Failed to restore player location (player dead)");
        }
        return err(
            &app,
            &ctx,
            "Nie możesz uczestniczyć w przygodzie, ponieważ jesteś martwy.",
        );
    }

    let action_target = form.action.unwrap_or_default();
    if action_target.is_empty() {
        return err(&app, &ctx, "Wybierz akcję.");
    }

    // Validate the action is reachable from the current room.
    let exits = mission_loader::parse_exits(&active.raw_exits);
    let mobs = mission_loader::parse_mobs(&active.raw_mobs);
    let items = mission_loader::parse_items(&active.raw_items);
    let moreinfo = mission_loader::parse_moreinfo(&active.raw_moreinfo);
    let valid_targets = mission_loader::valid_action_targets(&exits, &mobs, &items, &moreinfo);

    if !valid_targets.contains(&action_target) {
        return err(&app, &ctx, "Zapomnij o tym!");
    }

    // Look up the target room.
    let next_room = mq::find_random_room_by_name(&app.pool, &action_target)
        .await
        .ok()
        .flatten();

    let Some(next_room) = next_room else {
        return err(&app, &ctx, "Nie można znaleźć następnego pokoju.");
    };

    // Check if this is a terminal room.
    let is_terminal = mission_loader::is_terminal_room(&next_room.name);

    // Parse the new room's elements.
    let new_exits = mission_loader::parse_exits(&next_room.raw_exits);
    let new_mobs = mission_loader::parse_mobs(&next_room.raw_mobs);
    let new_items = mission_loader::parse_items(&next_room.raw_items);
    let _new_moreinfo = mission_loader::parse_moreinfo(&next_room.raw_moreinfo);

    // Check rooms remaining.
    let rooms_left = active.rooms_remaining - 1;
    let mission_complete = rooms_left <= 0 || is_terminal;

    if mission_complete {
        // Mission is done — clean up.
        if let Err(e) = mq::delete_active_mission(&app.pool, player.id).await {
            tracing::error!(error = %e, "Failed to delete active mission on completion");
        }
        if let Err(e) = sqlx::query("UPDATE players SET location = $1 WHERE id = $2")
            .bind(&active.return_location)
            .bind(player.id)
            .execute(&app.pool)
            .await
        {
            tracing::error!(error = %e, "Failed to restore player location on mission complete");
        }

        // Award rewards if applicable.
        let reward = mission::calculate_mission_reward(
            active.successes,
            active.bonus,
            active.has_target,
            active.reached_quest_target(),
        );
        if reward.gold > 0 || reward.mission_points > 0 {
            if let Err(e) = sqlx::query(
                "UPDATE players SET credits = credits + $1, mpoints = mpoints + $2 WHERE id = $3",
            )
            .bind(reward.gold)
            .bind(reward.mission_points)
            .bind(player.id)
            .execute(&app.pool)
            .await
            {
                tracing::error!(error = %e, "Failed to grant mission rewards");
            }
        }

        let meta = PageMeta::titled("Przygoda");
        let base = app.templates.build_context(&ctx, &meta);
        let view = MissionRoomView {
            base,
            text: next_room.text,
            options: Vec::new(),
            finished: true,
            finish_link: "/city".into(),
        };
        return app.templates.render_value("mission.html", &view);
    }

    // Update the active mission to the new room.
    let adv = mq::RoomAdvance {
        player_id: player.id,
        location: next_room.id,
        exits: &next_room.raw_exits,
        mobs: &next_room.raw_mobs,
        items: &next_room.raw_items,
        moreinfo: &next_room.raw_moreinfo,
        successes: active.successes,
    };
    if let Err(e) = mq::update_active_mission_room(&app.pool, &adv).await {
        tracing::error!(error = %e, "update mission room failed");
        return server_error();
    }

    // Render the new room.
    let actions = mission_loader::collect_room_actions(&new_exits, &new_mobs, &new_items);
    let text = mission_loader::build_room_text(&next_room.text, &new_mobs, &new_items);

    let options: Vec<MissionOptionView> = actions
        .iter()
        .map(|(target, label)| MissionOptionView {
            target: target.clone(),
            label: label.clone(),
        })
        .collect();

    let meta = PageMeta::titled("Przygoda");
    let base = app.templates.build_context(&ctx, &meta);
    let view = MissionRoomView {
        base,
        text,
        options,
        finished: false,
        finish_link: String::new(),
    };
    app.templates.render_value("mission.html", &view)
}

// =========================================================================
// Maze (maze.php) — Ardulith labyrinth
// =========================================================================

/// GET /maze — show Ardulith maze entrance.
pub async fn maze_show(
    State(app): State<AppState>,
    Extension(ctx): Extension<RequestContext>,
) -> Response {
    let (player, _user) = match require_player(&app, &ctx).await {
        Ok(v) => v,
        Err(r) => return r,
    };

    if let Err(_e) = maze::can_enter_maze(&player.location, player.hp) {
        return err(&app, &ctx, "Nie możesz wejść do labiryntu z tego miejsca.");
    }

    #[allow(clippy::cast_possible_truncation)]
    let max_amount = (player.energy / maze::LABYRINTH_ENERGY_COST).floor() as i32;

    let meta = PageMeta::titled("Labirynt Ardulith");
    let base = app.templates.build_context(&ctx, &meta);
    let view = MazeView {
        base,
        maze_info: "Wchodzisz do mrocznego labiryntu pod miastem Ardulith. \
                    Ciemne, kamienne korytarze wiją się w głąb ziemi."
            .into(),
        max_amount,
    };
    app.templates.render_value("maze.html", &view)
}

/// POST /maze/explore — maze exploration (simplified; combat handled separately).
pub async fn maze_explore(
    State(app): State<AppState>,
    Extension(ctx): Extension<RequestContext>,
    Form(form): Form<ExploreForm>,
) -> Response {
    let (player, _user) = match require_player(&app, &ctx).await {
        Ok(v) => v,
        Err(r) => return r,
    };

    let requested = form.amount.unwrap_or(0);

    if player.hp <= 0 {
        return err(
            &app,
            &ctx,
            "Nie możesz zwiedzać labiryntu ponieważ jesteś martwy!",
        );
    }
    if requested <= 0 {
        return err(&app, &ctx, "Zapomnij o tym.");
    }
    #[allow(clippy::cast_possible_truncation)]
    let affordable = (player.energy / maze::LABYRINTH_ENERGY_COST).floor() as i32;
    if affordable <= 0 {
        return err(&app, &ctx, "Nie masz wystarczająco energii.");
    }
    let steps = requested.min(affordable);

    // Maze uses the same basic exploration loop as labyrinth.
    // Scope RNG so it drops before .await.
    let (gold, mithril, energy_bonus) = {
        let mut rng = rand::thread_rng();
        let mut gold: i32 = 0;
        let mut mithril: i32 = 0;
        let mut energy_bonus: i32 = 0;

        for _ in 0..steps {
            let roll = rng.gen_range(1..=11);
            match maze::LabyrinthStepOutcome::from_roll(roll) {
                maze::LabyrinthStepOutcome::Gold => gold += rng.gen_range(1..=150),
                maze::LabyrinthStepOutcome::Mithril => mithril += rng.gen_range(1..=5),
                maze::LabyrinthStepOutcome::EnergyLoss => energy_bonus += 1,
                _ => {}
            }
        }
        (gold, mithril, energy_bonus)
    };

    let energy_cost = f64::from(steps) * maze::LABYRINTH_ENERGY_COST;
    let energy_delta = f64::from(energy_bonus) - energy_cost;

    let gender_suffix = if player.gender.as_deref() == Some("F") {
        "aś"
    } else {
        "eś"
    };
    let mut text = String::new();
    if gold > 0 || mithril > 0 || energy_bonus > 0 {
        let _ = write!(
            text,
            "Podczas swojej wędrówki znalazł{gender_suffix}:<br />"
        );
        if gold > 0 {
            let _ = write!(text, "{gold} sztuk złota<br />");
        }
        if mithril > 0 {
            let _ = write!(text, "{mithril} sztuk mithrilu<br />");
        }
        if energy_bonus > 0 {
            let _ = write!(text, "{energy_bonus} razy źródełko<br />");
        }
    } else {
        let _ = write!(
            text,
            "Wędrował{gender_suffix} jakiś czas ale nic ciekawego nie znalazł{gender_suffix}.<br />"
        );
    }
    let _ = write!(
        text,
        "Zużył{gender_suffix} na to {energy_cost:.1} energii.<br />"
    );

    if let Err(e) = sqlx::query(
        "UPDATE players SET credits = credits + $1, platinum = platinum + $2, \
         energy = energy + $3 WHERE id = $4",
    )
    .bind(gold)
    .bind(mithril)
    .bind(energy_delta)
    .bind(player.id)
    .execute(&app.pool)
    .await
    {
        tracing::error!(error = %e, "Failed to grant maze rewards");
    }

    let meta = PageMeta::titled("Labirynt Ardulith");
    let base = app.templates.build_context(&ctx, &meta);
    let view = MazeResultView {
        base,
        result_text: text,
    };
    app.templates.render_value("maze_result.html", &view)
}

// =========================================================================
// Helpers
// =========================================================================

use crate::middleware::context::SessionUser;
use vallheru_data::queries::player::PlayerRow;

#[allow(clippy::cast_possible_truncation)]
async fn require_player<'a>(
    app: &AppState,
    ctx: &'a RequestContext,
) -> Result<(PlayerRow, &'a SessionUser), Response> {
    let Some(ref user) = ctx.session_user else {
        return Err(crate::page::redirect("/login"));
    };
    let player_id = user.id as i32;
    match pq::find_player_by_id(&app.pool, player_id).await {
        Ok(Some(row)) => Ok((row, user)),
        Ok(None) => Err(crate::page::redirect("/login")),
        Err(e) => {
            tracing::error!(error = %e, "require_player failed");
            Err(server_error())
        }
    }
}

fn err(state: &AppState, ctx: &RequestContext, message: &str) -> Response {
    let meta = PageMeta::titled("Błąd").with_flash(Flash {
        kind: FlashKind::Error,
        message: message.to_owned(),
    });
    let base = state.templates.build_context(ctx, &meta);
    state.templates.render("error.html", &base)
}

fn server_error() -> Response {
    use axum::response::IntoResponse;
    (
        axum::http::StatusCode::INTERNAL_SERVER_ERROR,
        "Internal error",
    )
        .into_response()
}

/// Find an available quest for the player that hasn't been started yet.
async fn find_available_quest(app: &AppState, player_id: i32) -> Option<i32> {
    // Load all quest starts available at grid.php.
    let steps = qq::find_quest_steps_by_name(&app.pool, 0, "grid.php", "start", "pl")
        .await
        .ok()?;

    // Get all quests this player already has.
    let actions = qq::find_all_quest_actions(&app.pool, player_id)
        .await
        .unwrap_or_default();
    let taken: Vec<i32> = actions.iter().map(|a| a.quest).collect();

    // Find quest IDs that are available and not yet started.
    let available: Vec<i32> = steps
        .iter()
        .filter(|s| !taken.contains(&s.qid))
        .map(|s| s.qid)
        .collect();

    if available.is_empty() {
        return None;
    }

    let mut rng = rand::thread_rng();
    let idx = rng.gen_range(0..available.len());
    Some(available[idx])
}

/// Try to find a map fragment using a pre-generated roll (1-50).
async fn try_find_map(app: &AppState, player: &PlayerRow, roll: i32) -> i32 {
    if roll != 50 || player.maps >= 20 || player.rank == "Bohater" {
        return 0;
    }

    // Check global maps availability.
    let maps_available: Option<(String,)> =
        sqlx::query_as("SELECT value FROM settings WHERE setting = 'maps'")
            .fetch_optional(&app.pool)
            .await
            .ok()
            .flatten();

    match maps_available {
        Some((val,)) => {
            let count: i32 = val.parse().unwrap_or(0);
            if count > 0 {
                if let Err(e) = sqlx::query("UPDATE settings SET value = $1 WHERE setting = 'maps'")
                    .bind((count - 1).to_string())
                    .execute(&app.pool)
                    .await
                {
                    tracing::error!(error = %e, "Failed to decrement map count");
                }
                1
            } else {
                0
            }
        }
        None => 0,
    }
}
