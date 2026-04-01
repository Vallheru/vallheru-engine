//! Portal of Truth and Astral Planes handlers.
//!
//! Ported from `portal.php` (single boss fight for Portal of Truth)
//! and `portals.php` (7 astral plane boss encounters with component rewards).

use axum::{
    Extension, Form,
    extract::{Path, Query, State},
    response::Response,
};
use rand::Rng;

use crate::middleware::context::RequestContext;
use crate::page::{Flash, FlashKind, PageMeta};
use crate::state::AppState;
use vallheru_data::queries::{combat, player as player_q, portal as portal_q};
use vallheru_domain::combat::{
    battle::{
        self, AttackRolls, BattleOutcome, BattleState, EscapeRolls, MonsterAttackRoll,
        MonsterCombatState, MonsterTurnRolls, SpellRolls,
    },
    formulas::{self, AttackStance, DamageContext, MonsterResistance, ResistanceStrength},
};
use vallheru_domain::item::{
    Element, EquipmentType, OwnedEquipment, Spell, SpellStatus, SpellType,
};
use vallheru_domain::player::Class;
use vallheru_domain::player::skills::PlayerSkill;
use vallheru_domain::player::stats::PlayerStat;

// =========================================================================
// Constants — Portal of Truth
// =========================================================================

/// Sentinel fight ID for the Portal guardian.
const PORTAL_FIGHT_ID: i32 = 999;
const PORTAL_GUARDIAN_NAME: &str = "Strażnik Skarbu";
const PORTAL_GUARDIAN_STRENGTH: i32 = 5000;
const PORTAL_GUARDIAN_AGILITY: i32 = 5000;
const PORTAL_GUARDIAN_SPEED: i32 = 5000;
const PORTAL_GUARDIAN_ENDURANCE: i32 = 5000;
const PORTAL_GUARDIAN_HP: i32 = 50_000;
const PORTAL_GUARDIAN_LEVEL: i32 = 1;
const PORTAL_XP_REWARD: i64 = 1_000_000;
const PORTAL_GOLD_REWARD: i64 = 1_000_000;

// =========================================================================
// Constants — Astral Planes
// =========================================================================

struct AstralBoss {
    name: &'static str,
    plan_name: &'static str,
    strength: i32,
    agility: i32,
    speed: i32,
    endurance: i32,
    hp: i32,
    level: i32,
    exp_min: i32,
    exp_max: i32,
    component_drop_pct: i32,
    skill_bonus: i32,
    resistance: (Element, ResistanceStrength),
    dmg_element: Element,
}

const ASTRAL_BOSSES: [AstralBoss; 7] = [
    AstralBoss {
        name: "Glabrezu",
        plan_name: "plan demoniczny",
        strength: 400,
        agility: 450,
        speed: 450,
        endurance: 400,
        hp: 1000,
        level: 10,
        exp_min: 500,
        exp_max: 600,
        component_drop_pct: 90,
        skill_bonus: 1,
        resistance: (Element::Fire, ResistanceStrength::Weak),
        dmg_element: Element::Fire,
    },
    AstralBoss {
        name: "Barlog",
        plan_name: "plan ognisty",
        strength: 750,
        agility: 650,
        speed: 700,
        endurance: 750,
        hp: 1500,
        level: 20,
        exp_min: 750,
        exp_max: 900,
        component_drop_pct: 85,
        skill_bonus: 1,
        resistance: (Element::Fire, ResistanceStrength::Medium),
        dmg_element: Element::Fire,
    },
    AstralBoss {
        name: "Zgłębiczart",
        plan_name: "plan piekielny",
        strength: 1100,
        agility: 1050,
        speed: 1150,
        endurance: 1100,
        hp: 2300,
        level: 50,
        exp_min: 1000,
        exp_max: 1250,
        component_drop_pct: 80,
        skill_bonus: 2,
        resistance: (Element::Fire, ResistanceStrength::Strong),
        dmg_element: Element::Fire,
    },
    AstralBoss {
        name: "Pustynna Skorpendra",
        plan_name: "plan pustynny",
        strength: 1700,
        agility: 1850,
        speed: 1750,
        endurance: 1600,
        hp: 4000,
        level: 100,
        exp_min: 1250,
        exp_max: 1500,
        component_drop_pct: 75,
        skill_bonus: 2,
        resistance: (Element::Earth, ResistanceStrength::Strong),
        dmg_element: Element::Earth,
    },
    AstralBoss {
        name: "Podwodny Kraken",
        plan_name: "plan wodny",
        strength: 2200,
        agility: 2300,
        speed: 2300,
        endurance: 2200,
        hp: 6500,
        level: 150,
        exp_min: 1500,
        exp_max: 1750,
        component_drop_pct: 70,
        skill_bonus: 3,
        resistance: (Element::Water, ResistanceStrength::Strong),
        dmg_element: Element::Water,
    },
    AstralBoss {
        name: "Niebiański Tytan",
        plan_name: "plan niebiański",
        strength: 3000,
        agility: 2900,
        speed: 3050,
        endurance: 3200,
        hp: 8500,
        level: 200,
        exp_min: 1750,
        exp_max: 2000,
        component_drop_pct: 65,
        skill_bonus: 3,
        resistance: (Element::Wind, ResistanceStrength::Strong),
        dmg_element: Element::Wind,
    },
    AstralBoss {
        name: "Szkarłatny Lich",
        plan_name: "plan śmiertelny",
        strength: 4200,
        agility: 4100,
        speed: 4150,
        endurance: 4100,
        hp: 11_000,
        level: 250,
        exp_min: 2000,
        exp_max: 2500,
        component_drop_pct: 60,
        skill_bonus: 4,
        resistance: (Element::Fire, ResistanceStrength::Strong),
        dmg_element: Element::Fire,
    },
];

// =========================================================================
// View models
// =========================================================================

#[derive(serde::Serialize)]
#[allow(clippy::struct_excessive_bools)]
struct PortalView {
    #[serde(flatten)]
    base: crate::render::RenderContext,
    state: String,
    description: String,
    // Battle fields
    monster_name: String,
    player_hp: i32,
    player_max_hp: i32,
    player_mana: i32,
    monster_hp: i32,
    monster_max_hp: i32,
    round: i32,
    action_points: i32,
    battle_log: Vec<String>,
    has_weapon: bool,
    has_bow: bool,
    has_spell: bool,
    has_def_spell: bool,
    has_potion: bool,
    // Reward fields
    xp_gain: i64,
    gold_gain: i64,
    result_text: String,
}

impl PortalView {
    fn empty(base: crate::render::RenderContext, state: &str) -> Self {
        Self {
            base,
            state: state.to_owned(),
            description: String::new(),
            monster_name: String::new(),
            player_hp: 0,
            player_max_hp: 0,
            player_mana: 0,
            monster_hp: 0,
            monster_max_hp: 0,
            round: 0,
            action_points: 0,
            battle_log: Vec::new(),
            has_weapon: false,
            has_bow: false,
            has_spell: false,
            has_def_spell: false,
            has_potion: false,
            xp_gain: 0,
            gold_gain: 0,
            result_text: String::new(),
        }
    }
}

#[derive(serde::Serialize)]
#[allow(clippy::struct_excessive_bools)]
struct PortalsView {
    #[serde(flatten)]
    base: crate::render::RenderContext,
    state: String,
    step: usize,
    plan_name: String,
    monster_name: String,
    description: String,
    // Battle fields
    player_hp: i32,
    player_max_hp: i32,
    player_mana: i32,
    monster_hp: i32,
    monster_max_hp: i32,
    round: i32,
    action_points: i32,
    battle_log: Vec<String>,
    has_weapon: bool,
    has_bow: bool,
    has_spell: bool,
    has_def_spell: bool,
    has_potion: bool,
    // Reward fields
    xp_gain: i64,
    result_text: String,
    component_found: bool,
}

impl PortalsView {
    fn empty(base: crate::render::RenderContext, state: &str, step: usize) -> Self {
        Self {
            base,
            state: state.to_owned(),
            step,
            plan_name: String::new(),
            monster_name: String::new(),
            description: String::new(),
            player_hp: 0,
            player_max_hp: 0,
            player_mana: 0,
            monster_hp: 0,
            monster_max_hp: 0,
            round: 0,
            action_points: 0,
            battle_log: Vec::new(),
            has_weapon: false,
            has_bow: false,
            has_spell: false,
            has_def_spell: false,
            has_potion: false,
            xp_gain: 0,
            result_text: String::new(),
            component_found: false,
        }
    }
}

#[derive(serde::Deserialize)]
pub struct PortalActionForm {
    pub action: Option<String>,
}

#[derive(serde::Deserialize)]
pub struct PortalQuery {
    pub step: Option<String>,
}

// =========================================================================
// Portal of Truth — GET /portal
// =========================================================================

#[allow(clippy::too_many_lines, clippy::assigning_clones)]
pub async fn portal_show(
    State(app): State<AppState>,
    Extension(ctx): Extension<RequestContext>,
    Query(query): Query<PortalQuery>,
) -> Response {
    let Some(ref user) = ctx.session_user else {
        return crate::page::redirect("/login");
    };
    #[allow(clippy::cast_possible_truncation)]
    let player_id = user.id as i32;

    // Handle conclusion page (after victory, player clicks "Continue")
    if query.step.as_deref() == Some("1") {
        let _ = portal_q::set_player_location(&app.pool, player_id, "Altara").await;
        let meta = PageMeta::titled("Portal Prawdy");
        let base = app.templates.build_context(&ctx, &meta);
        let mut view = PortalView::empty(base, "conclusion");
        view.result_text = "A teraz proszę odejdź stąd, pozwól tym, którzy tu \
             mieszkają odpoczywać w spokoju Bohaterze.</i> Powoli wychodzisz z zamku, \
             przechodzisz przez most i wracasz w kierunku portalu. Kiedy w pewnym \
             momencie ostatni raz odwracasz się aby spojrzeć na zamek zauważasz że \
             ten zniknął, a na jego miejscu zostało jedynie niewielkie jezioro. \
             Rozglądając się w około widzisz że dziwna mgła opadła, a do około ciebie \
             rosną jakieś nieznane rośliny. W około panuje cisza, jakbyś był jedyną \
             żywą istotą w okolicy. Zbliżasz się do portalu i przechodzisz przez niego. \
             Po chwilowych zawrotach głowy znów słyszysz wokół siebie gwar rozmów \
             różnych istot. Jesteś z powrotem w Altarze."
            .to_owned();
        return app.templates.render_value("portal.html", &view);
    }

    let player = match load_player(&app, player_id).await {
        Ok(p) => p,
        Err(resp) => return resp,
    };

    if player.location != "Portal" {
        return error_page(&app, &ctx, "Nie możesz tu wejść.");
    }

    if player.hp <= 0 {
        let _ = portal_q::set_player_location(&app.pool, player_id, "Altara").await;
        return error_page(
            &app,
            &ctx,
            "Ponieważ jesteś martwy, twa dusza podąża z powrotem do szpitala.",
        );
    }

    let meta = PageMeta::titled("Portal Prawdy");
    let base = app.templates.build_context(&ctx, &meta);

    if player.fight == PORTAL_FIGHT_ID {
        // Show battle controls — guardian fight already initiated
        let equipped = vallheru_data::queries::item::find_equipped_items(&app.pool, player.id)
            .await
            .unwrap_or_default();
        let equipped_domain: Vec<OwnedEquipment> =
            equipped.iter().map(|e| e.clone().into_domain()).collect();

        let spells = vallheru_data::queries::item::find_spells_by_owner(&app.pool, player.id)
            .await
            .unwrap_or_default();
        let potions = vallheru_data::queries::item::find_potions_by_owner(&app.pool, player.id)
            .await
            .unwrap_or_default();
        let stats = player_q::load_stats(&app.pool, player.id)
            .await
            .unwrap_or_default();

        let speed = find_stat(&stats, "speed");
        #[allow(clippy::cast_possible_truncation)]
        let ap = ((f64::from(speed) / f64::from(PORTAL_GUARDIAN_SPEED)).ceil() as i32).clamp(1, 5);

        let mut view = PortalView::empty(base, "battle");
        view.monster_name = PORTAL_GUARDIAN_NAME.to_owned();
        view.player_hp = player.hp;
        view.player_max_hp = player.max_hp;
        view.player_mana = player.pm;
        view.monster_hp = PORTAL_GUARDIAN_HP;
        view.monster_max_hp = PORTAL_GUARDIAN_HP;
        view.action_points = ap;
        view.has_weapon = equipped_domain.iter().any(|e| {
            matches!(
                e.equipment_type,
                EquipmentType::Weapon | EquipmentType::Shield
            )
        });
        view.has_bow = equipped_domain
            .iter()
            .any(|e| e.equipment_type == EquipmentType::Bow);
        view.has_spell = spells.iter().any(|s| s.status == "E" && s.typ == "B");
        view.has_def_spell = spells.iter().any(|s| s.status == "E" && s.typ == "O");
        view.has_potion = potions
            .iter()
            .any(|p| p.status == "K" && p.potion_type == "H");
        return app.templates.render_value("portal.html", &view);
    }

    // Show main menu
    let mut view = PortalView::empty(base, "menu");
    view.description = "Tuż po przejściu przez magiczną bramę, przez moment czujesz \
         jak kręci ci się w głowie. Jednak już po chwili wszystko wraca do normy i \
         zaczynasz rozglądać się do okoła. Dostrzegasz że jesteś w jakimś nieznanym \
         miejscu, gdzie nawet gwiazdy wyglądają inaczej. Wszystko otulone jest lekką \
         szarą mgłą. Przyglądając się kawałkom znalezionych przez siebie map, zaczynasz \
         mniej więcej dostrzegać którą drogą wyruszyć. Idąc słyszysz wokół siebie \
         szepty w nieznanym ci języku, jednak masz dziwne przeczucie że owe rozmowy \
         dotyczą ciebie. Nie wiesz sam przez jaki okres czasu podróżowałeś przez ten \
         nieco nierealny świat, kiedy twoja podróż dobiega końca. Przed sobą widzisz \
         most przerzucony nad przepaścią prowadzący do największego zamku jaki w życiu \
         widziałeś. Podążając mostem, tuż przy bramie dostrzegasz wysoką, dobrze \
         zbudowaną postać odzianą w pełną zbroję płytową wykonaną z nieznanego ci \
         minerału. Kiedy podchodzisz bliżej z niepokojem zauważasz, że w oczodołach \
         istoty zapalają się niebieskie ognie a ona sama rusza w twoim kierunku. Twój \
         instynkt ci mówi, że teraz masz ostatnią szansę aby uciec zanim rozpocznie \
         się walka. Co robisz?"
        .to_owned();
    app.templates.render_value("portal.html", &view)
}

// =========================================================================
// Portal of Truth — POST /portal
// =========================================================================

#[allow(clippy::too_many_lines, clippy::assigning_clones)]
pub async fn portal_action(
    State(app): State<AppState>,
    Extension(ctx): Extension<RequestContext>,
    Form(form): Form<PortalActionForm>,
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

    if player.location != "Portal" {
        return error_page(&app, &ctx, "Nie możesz tu wejść.");
    }

    let action = form.action.as_deref().unwrap_or("");

    // --- Retreat ---
    if action == "retreat" {
        let _ = portal_q::exhaust_and_move(&app.pool, player_id, "Altara").await;
        let _ = combat::clear_player_fight(&app.pool, player_id).await;
        let meta = PageMeta::titled("Portal Prawdy — Odwrót");
        let base = app.templates.build_context(&ctx, &meta);
        let mut view = PortalView::empty(base, "retreat");
        view.result_text = "Szybko odwracasz się i zaczynasz uciekać w stronę portalu, czując na swoich plecach oddech owej dziwnej istoty. Nie wiesz jak długo biegłeś z powrotem kiedy w końcu szybko wbiegasz w bramę portalu. Po chwili słyszysz do okoła siebie gwar różnych rozmów — znak że jesteś z powrotem w Altarze. Jednak owa podróż zmęczyła cię niesamowicie. Dlatego na razie musisz odpocząć.".to_owned();
        return app.templates.render_value("portal.html", &view);
    }

    // --- Start fight ---
    if action == "start_fight" {
        if player.hp <= 0 {
            return error_page(&app, &ctx, "Nie masz wystarczająco dużo życia aby walczyć.");
        }
        if player.energy <= 0.0 {
            return error_page(
                &app,
                &ctx,
                "Nie masz wystarczająco dużo energii aby walczyć.",
            );
        }
        let _ = combat::set_player_fight(&app.pool, player_id, PORTAL_FIGHT_ID).await;
        return crate::page::redirect("/portal");
    }

    // --- Battle action (fight in progress) ---
    if player.fight != PORTAL_FIGHT_ID {
        return error_page(&app, &ctx, "Nie jesteś w walce ze Strażnikiem.");
    }
    if player.hp <= 0 {
        return error_page(&app, &ctx, "Nie możesz walczyć, ponieważ nie żyjesz!");
    }

    let monster_resistance = MonsterResistance::none();
    let (outcome, battle_state, log) = resolve_boss_battle(
        &app,
        &player,
        action,
        PORTAL_GUARDIAN_NAME,
        PORTAL_GUARDIAN_STRENGTH,
        PORTAL_GUARDIAN_AGILITY,
        PORTAL_GUARDIAN_SPEED,
        PORTAL_GUARDIAN_ENDURANCE,
        PORTAL_GUARDIAN_HP,
        PORTAL_GUARDIAN_LEVEL,
        Element::None,
        monster_resistance,
    )
    .await;

    let meta = PageMeta::titled("Portal Prawdy — Walka");
    let base = app.templates.build_context(&ctx, &meta);

    match outcome {
        BattleOutcome::Victory => {
            let hp_delta = battle_state.player_hp - player.hp;
            let mana_delta = battle_state.player_mana - player.pm;
            let _ = combat::apply_combat_results(
                &app.pool,
                player_id,
                hp_delta,
                PORTAL_GOLD_REWARD,
                mana_delta,
            )
            .await;
            let _ = combat::clear_player_fight(&app.pool, player_id).await;
            // energy=0 but stay at Portal for conclusion
            let _ = portal_q::exhaust_and_move(&app.pool, player_id, "Portal").await;

            let mut view = PortalView::empty(base, "victory");
            view.monster_name = PORTAL_GUARDIAN_NAME.to_owned();
            view.battle_log = log;
            view.xp_gain = PORTAL_XP_REWARD;
            view.gold_gain = PORTAL_GOLD_REWARD;
            view.result_text = "Po twoim ostatnim ciosie, Strażnik stanął nieruchomo w miejscu, a następnie powoli zaczął się rozpadać. Droga do zamku stoi przed tobą otworem. Kiedy podchodzisz do masywnych wrót te pchane jakąś niewidzialną siłą otwierają się na oścież. Wewnątrz widzisz olbrzymi, długi korytarz. Na jego ścianach wiszą gobeliny przedstawiające jakieś dziwne istoty żyjące na Vallheru u zarania dziejów. Odgłos twoich kroków tłumi bogato zdobiony czerwony dywan. Mimo iż cała twierdza wygląda na opuszczoną, nigdzie nie dostrzegasz choćby garstki kurzu. Ciekawie rozglądając się, idziesz cały czas przed siebie. Nie dostrzegasz nigdzie drzwi prowadzących do innych pomieszczeń, wydaje się jakby cała budowla była tylko tym jednym korytarzem. Kiedy zbliżasz się do jego końca, zauważasz owego starca, którego spotkałeś w Altarze. Kiedy podchodzisz do niego uśmiecha się na twój widok i mówi: <em>Witaj bohaterze, udało ci się pokonać Strażnika, moje gratulacje. Dotarłeś do Shar-lan-hazi, ostatniej twierdzy Pierwszych na Vallheru. Niestety, jak widzisz, nic więcej tutaj nie ma.</em>".to_owned();
            app.templates.render_value("portal.html", &view)
        }
        BattleOutcome::Defeat => {
            let _ = portal_q::apply_portal_defeat(&app.pool, player_id, "Altara").await;
            let mut view = PortalView::empty(base, "defeat");
            view.monster_name = PORTAL_GUARDIAN_NAME.to_owned();
            view.battle_log = log;
            view.result_text = "Ostatnią rzeczą jaką zauważasz to miecz Strażnika spadający na twą głowę, chwilę potem następuje ciemność.".to_owned();
            app.templates.render_value("portal.html", &view)
        }
        BattleOutcome::Escaped | BattleOutcome::Draw | BattleOutcome::InProgress => {
            let hp_delta = battle_state.player_hp - player.hp;
            let mana_delta = battle_state.player_mana - player.pm;
            let _ =
                combat::apply_combat_results(&app.pool, player_id, hp_delta, 0, mana_delta).await;
            let _ = combat::clear_player_fight(&app.pool, player_id).await;
            let _ = portal_q::exhaust_and_move(&app.pool, player_id, "Altara").await;

            let mut view = PortalView::empty(base, "escaped");
            view.monster_name = PORTAL_GUARDIAN_NAME.to_owned();
            view.battle_log = log;
            view.result_text =
                "Udało ci się uciec przed Strażnikiem. Wyczerpany wracasz do Altary.".to_owned();
            app.templates.render_value("portal.html", &view)
        }
    }
}

// =========================================================================
// Astral Planes — GET /portals/:step
// =========================================================================

#[allow(clippy::assigning_clones)]
pub async fn portals_show(
    State(app): State<AppState>,
    Extension(ctx): Extension<RequestContext>,
    Path(step): Path<usize>,
) -> Response {
    let Some(ref user) = ctx.session_user else {
        return crate::page::redirect("/login");
    };
    #[allow(clippy::cast_possible_truncation)]
    let player_id = user.id as i32;

    if step > 6 {
        return error_page(&app, &ctx, "Zapomnij o tym!");
    }

    let player = match load_player(&app, player_id).await {
        Ok(p) => p,
        Err(resp) => return resp,
    };

    if player.location != "Altara" && player.location != "Astralny plan" {
        return error_page(&app, &ctx, "Zapomnij o tym!");
    }

    if player.energy < 1.0 {
        return error_page(&app, &ctx, "Zapomnij o tym!");
    }

    if player.hp <= 0 {
        let _ = portal_q::set_player_location(&app.pool, player_id, "Altara").await;
        return error_page(&app, &ctx, "Zapomnij o tym!");
    }

    let map_name = format!("M{}", step + 1);
    let has_map = portal_q::has_astral_map(&app.pool, player_id, &map_name)
        .await
        .unwrap_or(false);
    if !has_map {
        return error_page(&app, &ctx, "Nie masz mapy do tego planu!");
    }

    let boss = &ASTRAL_BOSSES[step];
    let fight_value = i32::try_from(step).unwrap_or(0) + 1;

    // Set fight state and location for astral plane
    let _ =
        portal_q::set_fight_and_location(&app.pool, player_id, fight_value, "Astralny plan").await;

    let meta = PageMeta::titled("Astralny plan");
    let base = app.templates.build_context(&ctx, &meta);

    // Show plane description with fight button
    let mut view = PortalsView::empty(base, "menu", step);
    view.plan_name = boss.plan_name.to_owned();
    view.monster_name = boss.name.to_owned();
    view.description = format!(
        "Przekraczasz portal i wchodzisz na {}. Czujesz lekkie zawroty głowy \
         spowodowane magiczną podróżą. Kiedy dochodzisz do siebie, twoją uwagę \
         przykuwa szarżujący wprost na ciebie {}! Rozpoczyna się walka!",
        boss.plan_name, boss.name,
    );
    app.templates.render_value("portals.html", &view)
}

// =========================================================================
// Astral Planes — POST /portals/:step
// =========================================================================

#[allow(clippy::too_many_lines, clippy::assigning_clones)]
pub async fn portals_action(
    State(app): State<AppState>,
    Extension(ctx): Extension<RequestContext>,
    Path(step): Path<usize>,
    Form(form): Form<PortalActionForm>,
) -> Response {
    let Some(ref user) = ctx.session_user else {
        return crate::page::redirect("/login");
    };
    #[allow(clippy::cast_possible_truncation)]
    let player_id = user.id as i32;

    if step > 6 {
        return error_page(&app, &ctx, "Zapomnij o tym!");
    }

    let player = match load_player(&app, player_id).await {
        Ok(p) => p,
        Err(resp) => return resp,
    };

    let fight_value = i32::try_from(step).unwrap_or(0) + 1;
    if player.fight != fight_value {
        return error_page(&app, &ctx, "Zapomnij o tym!");
    }

    if player.hp <= 0 {
        return error_page(&app, &ctx, "Nie możesz walczyć, ponieważ nie żyjesz!");
    }

    let boss = &ASTRAL_BOSSES[step];
    let map_name = format!("M{}", step + 1);
    let action = form.action.as_deref().unwrap_or("attack_normal");

    let monster_resistance = MonsterResistance {
        element: boss.resistance.0,
        strength: boss.resistance.1,
    };

    let (outcome, battle_state, log) = resolve_boss_battle(
        &app,
        &player,
        action,
        boss.name,
        boss.strength,
        boss.agility,
        boss.speed,
        boss.endurance,
        boss.hp,
        boss.level,
        boss.dmg_element,
        monster_resistance,
    )
    .await;

    let meta = PageMeta::titled("Astralny plan — Walka");
    let base = app.templates.build_context(&ctx, &meta);

    match outcome {
        BattleOutcome::Victory => {
            // Apply HP/mana changes
            let hp_delta = battle_state.player_hp - player.hp;
            let mana_delta = battle_state.player_mana - player.pm;
            let _ =
                combat::apply_combat_results(&app.pool, player_id, hp_delta, 0, mana_delta).await;
            let _ = combat::clear_player_fight(&app.pool, player_id).await;

            // Component drop roll
            let component_found = {
                let roll = rand::thread_rng().gen_range(1..=100);
                roll <= boss.component_drop_pct
            };

            if component_found {
                let component_type = format!("C{step}");
                let _ =
                    portal_q::award_astral_component(&app.pool, player_id, &component_type).await;
            }

            // Determine combat skill based on equipped weapon
            let equipped = vallheru_data::queries::item::find_equipped_items(&app.pool, player_id)
                .await
                .unwrap_or_default();
            let equipped_domain: Vec<OwnedEquipment> =
                equipped.iter().map(|e| e.clone().into_domain()).collect();
            let skill_key = if equipped_domain
                .iter()
                .any(|e| e.equipment_type == EquipmentType::Weapon)
            {
                "attack"
            } else if equipped_domain
                .iter()
                .any(|e| e.equipment_type == EquipmentType::Bow)
            {
                "shoot"
            } else {
                "magic"
            };
            let _ =
                portal_q::add_skill_level(&app.pool, player_id, skill_key, boss.skill_bonus).await;

            // Calculate XP reward
            #[allow(clippy::cast_possible_truncation)]
            let xp_gain = {
                let base_xp = rand::thread_rng().gen_range(boss.exp_min..=boss.exp_max);
                let span = (f64::from(boss.level) / f64::from(player.pw.max(1))).min(2.0);
                (f64::from(base_xp) * span).ceil() as i64
            };

            // Deduct energy, move to Altara, consume map
            let _ = combat::deduct_energy(&app.pool, player_id, 1.0).await;
            let _ = portal_q::set_player_location(&app.pool, player_id, "Altara").await;
            let _ = portal_q::consume_astral_map(&app.pool, player_id, &map_name).await;

            let result_text = if component_found {
                "Jeszcze tylko jeden cios i bestia pada martwa przed tobą. Krzywiąc się lekko z powodu nieprzyjemnego zapachu martwej bestii, przeszukujesz jej zwłoki. Okazuje się że twoje poświęcenie nie poszło na marne. Z cielska wyciągasz astralny komponent!".to_owned()
            } else {
                "Jeszcze tylko jeden cios i bestia pada martwa przed tobą. Po pewnym czasie stwierdzasz że to bezsensowne, ta poczwara nic przy sobie nie miała! Zrezygnowany wracasz do Altary.".to_owned()
            };

            let mut view = PortalsView::empty(base, "victory", step);
            view.monster_name = boss.name.to_owned();
            view.battle_log = log;
            view.xp_gain = xp_gain;
            view.result_text = result_text;
            view.component_found = component_found;
            app.templates.render_value("portals.html", &view)
        }
        BattleOutcome::Defeat => {
            let _ = portal_q::apply_portal_defeat(&app.pool, player_id, "Altara").await;
            let _ = combat::deduct_energy(&app.pool, player_id, 1.0).await;
            let _ = portal_q::consume_astral_map(&app.pool, player_id, &map_name).await;

            let mut view = PortalsView::empty(base, "defeat", step);
            view.monster_name = boss.name.to_owned();
            view.battle_log = log;
            view.result_text = "Ostatnią rzeczą jaką widzisz, jest spadające na ciebie uderzenie potwora. Potem otacza ciebie już tylko nieprzenikniona ciemność oraz najgłębsza cisza. Po pewnym czasie budzisz się w szpitalu w Altarze.".to_owned();
            app.templates.render_value("portals.html", &view)
        }
        BattleOutcome::Escaped | BattleOutcome::Draw | BattleOutcome::InProgress => {
            let hp_delta = battle_state.player_hp - player.hp;
            let mana_delta = battle_state.player_mana - player.pm;
            let _ =
                combat::apply_combat_results(&app.pool, player_id, hp_delta, 0, mana_delta).await;
            let _ = combat::clear_player_fight(&app.pool, player_id).await;
            let _ = combat::deduct_energy(&app.pool, player_id, 1.0).await;
            let _ = portal_q::set_player_location(&app.pool, player_id, "Altara").await;
            let _ = portal_q::consume_astral_map(&app.pool, player_id, &map_name).await;

            let mut view = PortalsView::empty(base, "escaped", step);
            view.monster_name = boss.name.to_owned();
            view.battle_log = log;
            view.result_text = "Przerażony rzucasz się do ucieczki. Biegnąc ile sił w nogach, odwracasz głowę aby spojrzeć za siebie. Widzisz, że bestia nadal ciebie goni. Szybko odwracasz się do przodu... i wpadasz na zdumionego przechodnia, przewracając go. Widzisz, że znów jesteś na ulicach Altary.".to_owned();
            app.templates.render_value("portals.html", &view)
        }
    }
}

// =========================================================================
// Shared battle resolution helper
// =========================================================================

/// Resolve a full boss battle (Portal guardian or Astral boss).
///
/// Returns the battle outcome, final state, and combat log.
#[allow(
    clippy::too_many_arguments,
    clippy::cast_possible_truncation,
    clippy::too_many_lines
)]
async fn resolve_boss_battle(
    app: &AppState,
    player: &player_q::PlayerRow,
    action: &str,
    monster_name: &str,
    monster_strength: i32,
    monster_agility: i32,
    monster_speed: i32,
    monster_endurance: i32,
    monster_hp: i32,
    monster_level: i32,
    dmg_element: Element,
    monster_resistance: MonsterResistance,
) -> (BattleOutcome, BattleState, Vec<String>) {
    let player_id = player.id;

    let stats = player_q::load_stats(&app.pool, player_id)
        .await
        .unwrap_or_default();
    let skills = player_q::load_skills(&app.pool, player_id)
        .await
        .unwrap_or_default();
    let bonuses = player_q::load_bonuses(&app.pool, player_id)
        .await
        .unwrap_or_default();
    let equipped = vallheru_data::queries::item::find_equipped_items(&app.pool, player_id)
        .await
        .unwrap_or_default();
    let equipped_domain: Vec<OwnedEquipment> =
        equipped.iter().map(|e| e.clone().into_domain()).collect();
    let spells = vallheru_data::queries::item::find_spells_by_owner(&app.pool, player_id)
        .await
        .unwrap_or_default();

    let weapon_ref = equipped_domain
        .iter()
        .find(|e| e.equipment_type == EquipmentType::Weapon);
    let second_weapon_ref = equipped_domain
        .iter()
        .find(|e| e.equipment_type == EquipmentType::Shield);
    let bow_ref = equipped_domain
        .iter()
        .find(|e| e.equipment_type == EquipmentType::Bow);
    let arrows_ref = equipped_domain
        .iter()
        .find(|e| e.equipment_type == EquipmentType::Arrows);
    let wand_ref = equipped_domain
        .iter()
        .find(|e| e.equipment_type == EquipmentType::Wand);
    let helmet_ref = equipped_domain
        .iter()
        .find(|e| e.equipment_type == EquipmentType::Helmet);
    let armor_ref = equipped_domain
        .iter()
        .find(|e| e.equipment_type == EquipmentType::Armor);
    let legs_ref = equipped_domain
        .iter()
        .find(|e| e.equipment_type == EquipmentType::Legs);
    let shield_ref = equipped_domain
        .iter()
        .find(|e| e.equipment_type == EquipmentType::Shield);

    let battle_spell_row = spells.iter().find(|s| s.status == "E" && s.typ == "B");
    let battle_spell: Option<Spell> = battle_spell_row.map(spell_from_row);
    let def_spell_row = spells.iter().find(|s| s.status == "E" && s.typ == "O");
    let def_spell: Option<Spell> = def_spell_row.map(spell_from_row);

    let player_class = Class::from_db(&player.class).unwrap_or(Class::Warrior);
    let speed = find_stat(&stats, "speed");
    let condition = find_stat(&stats, "condition");
    let magic_skill = find_skill(&skills, "magic");

    let base_damage = f64::midpoint(f64::from(monster_strength), f64::from(monster_agility)) as i32;

    let mon_state = MonsterCombatState {
        current_hp: monster_hp,
        max_hp: monster_hp,
        base_damage,
        speed: monster_speed,
        level: monster_level,
        agility: monster_agility,
        dmg_element,
        name: monster_name.to_owned(),
    };

    let ap = ((f64::from(speed) / f64::from(monster_speed)).ceil() as i32).clamp(1, 5);
    let mut battle = BattleState::new(player.hp, player.pm, ap, vec![mon_state]);

    let stance = match action {
        "attack_aggressive" => AttackStance::Aggressive,
        "attack_berserker" => AttackStance::Berserker,
        "attack_defensive" => AttackStance::Defensive,
        _ => AttackStance::Normal,
    };

    let wants_escape = action == "escape";
    let wants_rest = action == "rest";
    let wants_spell = action == "cast_spell" || action == "burst_spell";
    let weapon_weight = weapon_ref.map_or(0, |w| w.power / 10);

    let mut log: Vec<String> = Vec::new();

    let dmg_ctx = DamageContext {
        class: &player_class,
        stats: &stats,
        skills: &skills,
        bonuses: &bonuses,
        weapon: weapon_ref,
        second_weapon: second_weapon_ref,
        bow: bow_ref,
        arrows: arrows_ref,
        wand: wand_ref,
        helmet: helmet_ref,
        armor: armor_ref,
        legs: legs_ref,
        shield: shield_ref,
        attack_spell: battle_spell.as_ref(),
        pet_attack: 0,
        pet_defense: 0,
        monster_resistance,
    };

    let player_dodge_value = formulas::player_dodge(
        &player_class,
        find_stat(&stats, "agility"),
        monster_agility,
        find_skill(&skills, "dodge"),
    );
    let active_def_spell_ref = def_spell.as_ref();
    let monster_turn_ctx = battle::MonsterTurnCtx {
        player_dodge_value,
        stance,
        shield: shield_ref,
        armor_pieces: [helmet_ref, armor_ref, legs_ref, None],
        stats: &stats,
        skills: &skills,
        bonuses: &bonuses,
        monster_attacks_per_round: 1,
        active_def_spell: active_def_spell_ref,
    };

    // All RNG scoped in block — ThreadRng is !Send.
    run_battle_loop(
        &mut battle,
        &dmg_ctx,
        &monster_turn_ctx,
        battle_spell.as_ref(),
        &skills,
        speed,
        condition,
        magic_skill,
        monster_endurance,
        weapon_ref.is_some(),
        weapon_weight,
        wants_escape,
        wants_rest,
        wants_spell,
        stance,
        action,
        wand_ref,
        &mut log,
    );

    (battle.outcome, battle, log)
}

/// Run the full auto-resolve battle loop, populating `log` with combat messages.
#[allow(
    clippy::too_many_arguments,
    clippy::too_many_lines,
    clippy::fn_params_excessive_bools
)]
fn run_battle_loop(
    battle: &mut BattleState,
    dmg_ctx: &DamageContext<'_>,
    monster_turn_ctx: &battle::MonsterTurnCtx<'_>,
    battle_spell: Option<&Spell>,
    skills: &[PlayerSkill],
    speed: i32,
    condition: i32,
    magic_skill: i32,
    monster_endurance: i32,
    has_weapon: bool,
    weapon_weight: i32,
    wants_escape: bool,
    wants_rest: bool,
    wants_spell: bool,
    stance: AttackStance,
    action: &str,
    wand_ref: Option<&OwnedEquipment>,
    log: &mut Vec<String>,
) {
    let mut rng = rand::thread_rng();

    while battle.outcome == BattleOutcome::InProgress {
        if wants_escape {
            let avg_speed = battle.monsters.first().map_or(1, |m| m.speed);
            let escape_rolls = EscapeRolls {
                player_roll: rng.gen_range(1..=100),
                monster_roll: rng.gen_range(1..=100),
            };
            let result = battle::resolve_escape(
                battle,
                speed,
                find_skill(skills, "perception"),
                &escape_rolls,
                avg_speed,
            );
            if result.succeeded {
                log.push("Udało Ci się uciec!".to_owned());
            } else {
                log.push("Nie udało Ci się uciec!".to_owned());
            }
        } else if wants_rest {
            battle::resolve_rest(battle, condition);
            log.push("Odpoczywasz...".to_owned());
        } else if wants_spell {
            if let Some(spell) = battle_spell {
                let spell_rolls = SpellRolls {
                    fizzle_roll: rng.gen_range(1..=100),
                    misfire_roll: rng.gen_range(1..=100),
                    skill_roll: rng.gen_range(1..=magic_skill.max(1)),
                    wand_roll: wand_ref.map_or(0, |w| rng.gen_range(1..=w.power.max(1))),
                    crit_roll_large: rng.gen_range(1..=1000),
                    crit_roll_small: rng.gen_range(1..=100),
                    dodge_roll: rng.gen_range(1..=100),
                    target_monster: 0,
                };
                let burst_power = if action == "burst_spell" {
                    magic_skill
                } else {
                    0
                };
                let result = battle::resolve_spell(
                    battle,
                    spell,
                    magic_skill,
                    &spell_rolls,
                    burst_power,
                    monster_endurance,
                );
                log.push(format!(
                    "Rzucasz zaklęcie! Zadajesz {} obrażeń.",
                    result.damage_dealt,
                ));
            } else {
                battle::resolve_rest(battle, condition);
                log.push("Nie masz czym walczyć... Odpoczywasz.".to_owned());
            }
        } else if has_weapon {
            let attack_rolls = AttackRolls {
                skill_roll: rng.gen_range(1..=find_skill(skills, "attack").max(1)),
                wand_roll: 0,
                crit_roll_large: rng.gen_range(1..=1000),
                crit_roll_small: rng.gen_range(1..=100),
                dodge_roll: rng.gen_range(1..=100),
                target_monster: 0,
            };
            let result =
                battle::resolve_attack(battle, dmg_ctx, stance, &attack_rolls, weapon_weight);
            if result.was_dodged {
                log.push(format!(
                    "Atakujesz {}! Potwór unika ciosu.",
                    battle.monsters[0].name
                ));
            } else {
                log.push(format!(
                    "Atakujesz {}! Zadajesz {} obrażeń.{}",
                    battle.monsters[0].name,
                    result.damage_dealt,
                    if result.was_critical {
                        " Trafienie krytyczne!"
                    } else {
                        ""
                    },
                ));
            }
        } else {
            battle::resolve_rest(battle, condition);
            log.push("Nie masz czym walczyć... Odpoczywasz.".to_owned());
        }

        if battle.outcome != BattleOutcome::InProgress {
            break;
        }

        // Monster turn
        let mon_attack_rolls: Vec<Vec<MonsterAttackRoll>> = battle
            .monsters
            .iter()
            .map(|_| {
                vec![MonsterAttackRoll {
                    damage_roll: rng.gen_range(0..=battle.monsters[0].level.max(1)),
                    dodge_roll: rng.gen_range(1..=100),
                    block_roll: rng.gen_range(1..=100),
                    hit_location_roll: rng.gen_range(1..=100),
                }]
            })
            .collect();

        let mon_rolls = MonsterTurnRolls {
            attacks: mon_attack_rolls,
        };

        let mon_result = battle::resolve_monster_turn(battle, monster_turn_ctx, &mon_rolls);
        if mon_result.total_damage > 0 {
            log.push(format!(
                "{} atakuje! Zadaje {} obrażeń.",
                battle.monsters[0].name, mon_result.total_damage,
            ));
        } else if mon_result.player_dodged_any {
            log.push(format!(
                "{} atakuje, ale unikasz ciosu!",
                battle.monsters[0].name
            ));
        }

        battle.next_round();
    }
}

// =========================================================================
// Shared helpers
// =========================================================================

async fn load_player(
    app_state: &AppState,
    player_id: i32,
) -> Result<player_q::PlayerRow, Response> {
    match player_q::find_player_by_id(&app_state.pool, player_id).await {
        Ok(Some(row)) => Ok(row),
        Ok(None) => Err(crate::page::redirect("/login")),
        Err(e) => {
            tracing::error!(error = %e, "portal load_player failed");
            Err(server_error())
        }
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
    use axum::response::IntoResponse;
    (
        axum::http::StatusCode::INTERNAL_SERVER_ERROR,
        "Internal error",
    )
        .into_response()
}

fn find_stat(stats: &[PlayerStat], key: &str) -> i32 {
    stats
        .iter()
        .find(|s| s.stat_key == key)
        .map_or(1, |s| s.trained.max(1))
}

fn find_skill(skills: &[PlayerSkill], key: &str) -> i32 {
    skills
        .iter()
        .find(|s| s.skill_key == key)
        .map_or(1, |s| s.level.max(1))
}

fn spell_from_row(row: &vallheru_data::queries::item::SpellRow) -> Spell {
    Spell {
        id: row.id,
        name: row.nazwa.clone(),
        owner_id: row.gracz,
        cost: row.cena,
        level: row.poziom,
        spell_type: SpellType::from_db(&row.typ).unwrap_or(SpellType::Battle),
        multiplier: row.obr,
        status: SpellStatus::from_db(&row.status).unwrap_or(SpellStatus::Active),
        element: Element::from_spell_code(&row.element),
    }
}
