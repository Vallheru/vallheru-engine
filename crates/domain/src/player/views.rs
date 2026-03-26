//! Player-facing read models for profile, stats, hall of fame, and member list.
//!
//! These are presentation-layer view models projected from the core domain
//! types. They carry only the data that templates actually need, formatted
//! for display. They do NOT touch the database or web layer.

use serde::Serialize;

use super::Player;
use super::bonuses::PlayerBonus;
use super::skills::PlayerSkill;
use super::stats::PlayerStat;

// ---------------------------------------------------------------------------
// Public player profile (view.php)
// ---------------------------------------------------------------------------

/// Read model for viewing another player's profile.
///
/// Carries pre-formatted display strings so templates stay logic-free.
#[derive(Debug, Clone, Serialize)]
pub struct ProfileView {
    pub id: i32,
    pub username: String,
    pub race: String,
    pub class: String,
    pub rank: String,
    pub location: String,
    pub age: i32,
    pub max_hp: i32,
    pub wins: i32,
    pub losses: i32,
    pub fight_ratio: Option<String>,
    pub last_killed: String,
    pub last_killed_by: String,
    pub reputation: i32,
    pub vallars: i32,
    pub gender: Option<String>,
    pub deity: Option<String>,
    pub profile: String,
    pub messenger: String,
    pub avatar: String,
    pub short_rpg: String,
    pub is_alive: bool,
    pub is_immune: bool,
    pub is_frozen: bool,
    pub tribe_id: i32,
    pub tribe_rank: String,
}

/// Build a [`ProfileView`] from a domain [`Player`].
pub fn build_profile_view(player: &Player) -> ProfileView {
    let fight_ratio = {
        let total = player.wins + player.losses;
        if total > 0 {
            let pct = (f64::from(player.wins) / f64::from(total)) * 100.0;
            Some(format!("{pct:.3}"))
        } else {
            None
        }
    };

    ProfileView {
        id: player.id,
        username: player.username.clone(),
        race: player.race.clone(),
        class: player.class.clone(),
        rank: format_rank(player.rank.to_db(), player.gender.as_deref()),
        location: player.location.clone(),
        age: player.age,
        max_hp: player.max_hp,
        wins: player.wins,
        losses: player.losses,
        fight_ratio,
        last_killed: player.last_killed.clone(),
        last_killed_by: player.last_killed_by.clone(),
        reputation: player.reputation,
        vallars: player.vallars,
        gender: player.gender.clone(),
        deity: player.deity.clone(),
        profile: player.profile.clone(),
        messenger: player.messenger.clone(),
        avatar: player.avatar.clone(),
        short_rpg: player.short_rpg.clone(),
        is_alive: player.hp > 0,
        is_immune: player.immune,
        is_frozen: player.freeze > 0,
        tribe_id: player.tribe_id,
        tribe_rank: player.tribe_rank.clone(),
    }
}

// ---------------------------------------------------------------------------
// Own stats page (stats.php)
// ---------------------------------------------------------------------------

/// A single stat display entry for the stats page.
#[derive(Debug, Clone, Serialize)]
pub struct StatDisplay {
    pub label: String,
    pub current: i32,
    /// Positive = green bonus, negative = red penalty, zero = neutral.
    pub delta: i32,
    /// XP progress as a percentage (0.0–100.0), `None` if at cap.
    pub xp_progress: Option<f64>,
}

/// A single skill display entry for the stats page.
#[derive(Debug, Clone, Serialize)]
pub struct SkillDisplay {
    pub label: String,
    pub current: i32,
    /// Change from base (after bonuses/equipment).
    pub delta: i32,
    /// XP progress as a percentage (0.0–100.0), `None` if at cap.
    pub xp_progress: Option<f64>,
}

/// A single bonus display entry for the stats page.
#[derive(Debug, Clone, Serialize)]
pub struct BonusDisplay {
    pub name: String,
    /// Effective percentage: `value * duration`.
    pub effective_pct: i32,
}

/// Full read model for the player's own stats page.
#[derive(Debug, Clone, Serialize)]
pub struct StatsView {
    pub id: i32,
    pub username: String,
    pub race: String,
    pub class: String,
    pub rank: String,
    pub location: String,
    pub age: i32,
    pub logins: i32,
    pub gender: Option<String>,
    pub deity: Option<String>,
    pub ap: i32,
    pub hp: i32,
    pub max_hp: i32,
    pub mana: i32,
    pub max_mana: i32,
    pub energy: f64,
    pub max_energy: f64,
    pub pw: i32,
    pub wins: i32,
    pub losses: i32,
    pub last_killed: String,
    pub last_killed_by: String,
    pub reputation: i32,
    pub newbie: i16,
    pub avatar: String,
    pub messenger: String,
    pub tribe_name: Option<String>,
    pub tribe_rank: String,
    pub bless_label: Option<String>,
    pub bless_value: i32,
    pub antidote_label: Option<String>,
    pub mpoints: i32,
    pub stats: Vec<StatDisplay>,
    pub skills: Vec<SkillDisplay>,
    pub bonuses: Vec<BonusDisplay>,
}

/// Build stat display entries by comparing base (unmodified) stats with
/// calculated (modified) stats.
pub fn build_stat_displays(
    base_stats: &[PlayerStat],
    calc_stats: &[PlayerStat],
) -> Vec<StatDisplay> {
    base_stats
        .iter()
        .map(|base| {
            let modified = calc_stats
                .iter()
                .find(|s| s.stat_key == base.stat_key)
                .map_or(base.trained, |s| s.modified);

            let delta = modified - base.trained;

            // XP progress: trained * 500 is the threshold for next level.
            // If trained >= base, stat is at cap → no progress shown.
            let xp_progress = if base.trained < base.base && base.trained > 0 {
                let threshold = base.trained * 500;
                Some((f64::from(base.xp) / f64::from(threshold)) * 100.0)
            } else {
                None
            };

            StatDisplay {
                label: base.label.clone(),
                current: modified,
                delta,
                xp_progress,
            }
        })
        .collect()
}

/// Build skill display entries by comparing base skills with calculated
/// (modified) skills.
///
/// If `hide_thievery` is true, the thievery skill is excluded unless the
/// player is a Thief class.
pub fn build_skill_displays(
    base_skills: &[PlayerSkill],
    calc_skills: &[PlayerSkill],
    is_thief: bool,
) -> Vec<SkillDisplay> {
    base_skills
        .iter()
        .filter(|s| {
            if s.skill_key == "thievery" && !is_thief {
                return false;
            }
            !s.label.is_empty()
        })
        .map(|base| {
            let modified = calc_skills
                .iter()
                .find(|s| s.skill_key == base.skill_key)
                .map_or(base.level, |s| s.level);

            let delta = modified - base.level;

            let xp_progress = if base.level < 100 && base.level > 0 {
                let threshold = base.level * 100;
                Some((f64::from(base.xp) / f64::from(threshold)) * 100.0)
            } else {
                None
            };

            SkillDisplay {
                label: base.label.clone(),
                current: modified,
                delta,
                xp_progress,
            }
        })
        .collect()
}

/// Build bonus display entries from active player bonuses.
pub fn build_bonus_displays(bonuses: &[PlayerBonus]) -> Vec<BonusDisplay> {
    bonuses
        .iter()
        .map(|b| BonusDisplay {
            name: b.bonus_name.clone(),
            effective_pct: b.value * b.duration,
        })
        .collect()
}

/// Map the blessing key to a Polish label.
pub fn bless_label(bless_key: &str) -> Option<&'static str> {
    match bless_key {
        "agility" => Some("Zręczności"),
        "strength" => Some("Siły"),
        "inteli" => Some("Inteligencji"),
        "wisdom" => Some("Siły Woli"),
        "speed" => Some("Szybkości"),
        "condition" => Some("Kondycji"),
        "smith" => Some("Kowalstwa"),
        "alchemy" => Some("Alchemii"),
        "carpentry" => Some("Stolarstwa"),
        "attack" => Some("Walki Bronią"),
        "shoot" => Some("Strzelectwa"),
        "dodge" => Some("Uników"),
        "magic" => Some("Rzucania Czarów"),
        "breeding" => Some("Hodowli"),
        "mining" => Some("Górnictwa"),
        "lumberjack" => Some("Drwalnictwa"),
        "herbalism" => Some("Zielarstwa"),
        "jewellry" => Some("Jubilerstwa"),
        "perception" => Some("Spostrzegawczości"),
        "thievery" => Some("Złodziejstwa"),
        "smelting" => Some("Hutnictwa"),
        _ => None,
    }
}

/// Map the antidote code to a Polish label.
pub fn antidote_label(code: &str) -> Option<&'static str> {
    match code {
        "I" => Some("na truciznę z Illani"),
        "D" => Some("na truciznę z Dynallca"),
        "N" => Some("na truciznę z Nutari"),
        "R" => Some("Wypita mikstura Oszukania śmierci"),
        _ => None,
    }
}

// ---------------------------------------------------------------------------
// Consider opponent (view.php?consider)
// ---------------------------------------------------------------------------

/// The power-level bands used by the "consider" feature.
///
/// The ordering reflects increasing threat: the player sees a descriptive
/// Polish string for the band the opponent falls into.
#[derive(Debug, Clone, Copy, PartialEq, Eq, Serialize)]
pub enum ThreatLevel {
    /// ratio ≤ 0.1 — "nie stanowi zagrożenia"
    Negligible,
    /// 0.1 < ratio ≤ 0.5
    Harmless,
    /// 0.5 < ratio ≤ 0.9
    AlmostChallenge,
    /// 0.9 < ratio ≤ 1.2
    EqualPower,
    /// 1.2 < ratio ≤ 1.3
    SlightlyDangerous,
    /// 1.3 < ratio ≤ 1.5
    Dangerous,
    /// 1.5 < ratio ≤ 1.7
    VeryDangerous,
    /// ratio > 1.7
    Lethal,
}

impl ThreatLevel {
    /// Polish description string matching the PHP `view.php` consider output.
    pub fn description(&self) -> &'static str {
        match self {
            Self::Negligible => "nie stanowi zagrożenia dla ciebie.",
            Self::Harmless => "jest niegroźny dla ciebie.",
            Self::AlmostChallenge => "stanowi prawie wyzwanie dla ciebie.",
            Self::EqualPower => "jest tak samo potężny jak ty.",
            Self::SlightlyDangerous => "jest nieco niebezpieczny dla ciebie.",
            Self::Dangerous => "jest groźny dla ciebie.",
            Self::VeryDangerous => "jest niebezpieczny dla ciebie.",
            Self::Lethal => "zabije ciebie jednym ciosem.",
        }
    }
}

/// Compute the combat power level for a player, used by the consider feature.
///
/// Mirrors the PHP formula exactly:
/// - base = condition + speed + agility + dodge + hp
/// - melee/ranged: + strength + `attack_or_shoot`
/// - magic: + wisdom + intelligence + `magic_skill`
pub fn combat_power_level(
    stats: &[PlayerStat],
    skills: &[PlayerSkill],
    hp: i32,
    has_weapon: bool,
    has_bow: bool,
) -> i32 {
    let stat_val = |key: &str| -> i32 {
        stats
            .iter()
            .find(|s| s.stat_key == key)
            .map_or(0, |s| s.modified)
    };
    let skill_val = |key: &str| -> i32 {
        skills
            .iter()
            .find(|s| s.skill_key == key)
            .map_or(0, |s| s.level)
    };

    let mut power =
        stat_val("condition") + stat_val("speed") + stat_val("agility") + skill_val("dodge") + hp;

    if has_weapon || has_bow {
        power += stat_val("strength");
        if has_weapon {
            power += skill_val("attack");
        } else {
            power += skill_val("shoot");
        }
    } else {
        power += stat_val("wisdom") + stat_val("inteli") + skill_val("magic");
    }

    power
}

/// Determine the threat level of an opponent relative to the player.
///
/// `player_power` and `enemy_power` are obtained from [`combat_power_level`].
pub fn consider_threat(player_power: i32, enemy_power: i32) -> ThreatLevel {
    if player_power == 0 {
        return ThreatLevel::Lethal;
    }

    let ratio = f64::from(enemy_power) / f64::from(player_power);

    if ratio <= 0.1 {
        ThreatLevel::Negligible
    } else if ratio <= 0.5 {
        ThreatLevel::Harmless
    } else if ratio <= 0.9 {
        ThreatLevel::AlmostChallenge
    } else if ratio <= 1.2 {
        ThreatLevel::EqualPower
    } else if ratio <= 1.3 {
        ThreatLevel::SlightlyDangerous
    } else if ratio <= 1.5 {
        ThreatLevel::Dangerous
    } else if ratio <= 1.7 {
        ThreatLevel::VeryDangerous
    } else {
        ThreatLevel::Lethal
    }
}

// ---------------------------------------------------------------------------
// Hall of Fame (hof.php)
// ---------------------------------------------------------------------------

/// A single entry in the Hall of Fame (heroes).
#[derive(Debug, Clone, Serialize)]
pub struct HofEntry {
    /// Original hero name.
    pub hero_name: String,
    /// Original hero ID (from the era they won).
    pub hero_id: i32,
    /// Current player ID (reincarnation). 0 or absent = "has left".
    pub current_player_id: Option<i32>,
    /// Hero's race at time of ascension.
    pub race: String,
}

/// A single entry in the Hall of Machines (hof2.php).
#[derive(Debug, Clone, Serialize)]
pub struct HofMachineEntry {
    /// Tribe name that built the astral machine.
    pub tribe_name: String,
    /// Leader who commanded the build.
    pub leader_name: String,
    /// Day of the era when it was built.
    pub build_date: String,
}

// ---------------------------------------------------------------------------
// Member list (memberlist.php)
// ---------------------------------------------------------------------------

/// Allowed sort columns for the member list.
#[derive(Debug, Clone, Copy, PartialEq, Eq)]
pub enum MemberSort {
    Id,
    Username,
    Rank,
    Race,
    Location,
}

impl MemberSort {
    /// Parse from the query parameter string.
    pub fn from_param(s: &str) -> Self {
        match s {
            "user" => Self::Username,
            "rank" => Self::Rank,
            "rasa" => Self::Race,
            "miejsce" => Self::Location,
            _ => Self::Id,
        }
    }

    /// SQL column name for ORDER BY.
    pub fn sql_column(&self) -> &'static str {
        match self {
            Self::Id => "id",
            Self::Username => "username",
            Self::Rank => "rank",
            Self::Race => "race",
            Self::Location => "location",
        }
    }
}

/// A single row in the member list table.
#[derive(Debug, Clone, Serialize)]
pub struct MemberListEntry {
    pub id: i32,
    pub username: String,
    pub rank: String,
    pub race: String,
    pub location: String,
    pub gender: Option<String>,
    pub short_rpg: String,
    pub tribe_id: i32,
}

/// Pagination metadata for the member list.
#[derive(Debug, Clone, Serialize)]
pub struct Pagination {
    pub current_page: i32,
    pub total_pages: i32,
    pub total_items: i64,
    pub per_page: i32,
}

impl Pagination {
    /// Create pagination from total items and page parameters.
    pub fn new(total_items: i64, current_page: i32, per_page: i32) -> Self {
        let total_pages = if total_items == 0 {
            1
        } else {
            #[allow(clippy::cast_precision_loss, clippy::cast_possible_truncation)]
            { ((total_items as f64) / f64::from(per_page)).ceil() as i32 }
        };
        let current_page = current_page.clamp(1, total_pages);
        Self {
            current_page,
            total_pages,
            total_items,
            per_page,
        }
    }

    /// SQL OFFSET for the current page.
    pub fn offset(&self) -> i64 {
        i64::from((self.current_page - 1) * self.per_page)
    }
}

// ---------------------------------------------------------------------------
// Rank formatting (includes/ranks.php)
// ---------------------------------------------------------------------------

/// Format a rank string with gender-appropriate Polish suffix.
///
/// Mirrors the PHP `selectrank()` function from `includes/ranks.php`.
pub fn format_rank(rank: &str, gender: Option<&str>) -> String {
    let is_female = gender == Some("F");

    match rank {
        "Admin" => {
            if is_female {
                "Administratorka".to_owned()
            } else {
                "Administrator".to_owned()
            }
        }
        "Staff" => {
            if is_female {
                "Moderatorka".to_owned()
            } else {
                "Moderator".to_owned()
            }
        }
        "Budowniczy" => {
            if is_female {
                "Budownicza".to_owned()
            } else {
                "Budowniczy".to_owned()
            }
        }
        "Sędzia" => {
            if is_female {
                "Sędzina".to_owned()
            } else {
                "Sędzia".to_owned()
            }
        }
        "Kronikarz" => {
            if is_female {
                "Kronikarka".to_owned()
            } else {
                "Kronikarz".to_owned()
            }
        }
        "Królewski Błazen" => "Królewski Błazen".to_owned(),
        "Prawnik" => {
            if is_female {
                "Prawniczka".to_owned()
            } else {
                "Prawnik".to_owned()
            }
        }
        "Kanclerz Sądu" => "Kanclerz Sądu".to_owned(),
        _ => {
            if is_female {
                "Graczka".to_owned()
            } else {
                "Gracz".to_owned()
            }
        }
    }
}

/// Format the "last seen" relative time string in Polish.
///
/// Mirrors the PHP `view.php` logic for lpv-based relative timestamps.
pub fn format_last_seen(last_page_visit: i64, now: i64) -> String {
    if last_page_visit == 0 {
        return "Nigdy".to_owned();
    }

    let diff = now - last_page_visit;
    if diff < 0 {
        return "Teraz".to_owned();
    }

    let days = diff / 86400;
    if days > 1 {
        return format!("około {days} dni temu.");
    }
    if days == 1 {
        return "Wczoraj".to_owned();
    }

    let hours = diff / 3600;
    if hours > 0 {
        return format!("około {hours} godzin temu.");
    }

    let minutes = diff / 60;
    if minutes > 0 {
        return format!("około {minutes} minut temu.");
    }

    "Teraz".to_owned()
}

/// Determine whether a player can see another player's battle log.
///
/// Battle logs are viewable for any specific player via `view.php?view=X&logs`.
#[derive(Debug, Clone, Serialize)]
pub struct BattleLogEntry {
    /// Opponent player ID.
    pub opponent_id: i32,
    /// Opponent username (or "Nieobecny(a)" if deleted).
    pub opponent_name: String,
    /// Result: "zwyciężył" / "przegrał" / "zremisował" (gender-suffixed).
    pub result: String,
    /// Date of the battle (era day).
    pub battle_date: String,
}

/// Build the result string for a battle log entry with gender suffix.
pub fn battle_result_label(viewer_id: i32, winner_id: i32, gender: Option<&str>) -> String {
    let suffix = if gender == Some("F") { "a" } else { "" };

    if winner_id == viewer_id {
        format!("zwyciężył{suffix}")
    } else if winner_id == 0 {
        format!("zremisował{suffix}")
    } else {
        format!("przegrał{suffix}")
    }
}

// ============================================================================
// Tests
// ============================================================================

#[cfg(test)]
mod tests {
    use super::*;

    // --- fight ratio ---------------------------------------------------------

    #[test]
    fn fight_ratio_with_fights() {
        let player = test_player(10, 5);
        let view = build_profile_view(&player);
        assert_eq!(view.fight_ratio.as_deref(), Some("66.667"));
    }

    #[test]
    fn fight_ratio_no_fights() {
        let player = test_player(0, 0);
        let view = build_profile_view(&player);
        assert!(view.fight_ratio.is_none());
    }

    // --- consider / threat level ---------------------------------------------

    #[test]
    fn consider_negligible() {
        assert_eq!(consider_threat(1000, 50), ThreatLevel::Negligible);
    }

    #[test]
    fn consider_harmless() {
        assert_eq!(consider_threat(1000, 300), ThreatLevel::Harmless);
    }

    #[test]
    fn consider_almost_challenge() {
        assert_eq!(consider_threat(1000, 700), ThreatLevel::AlmostChallenge);
    }

    #[test]
    fn consider_equal() {
        assert_eq!(consider_threat(1000, 1000), ThreatLevel::EqualPower);
    }

    #[test]
    fn consider_slightly_dangerous() {
        assert_eq!(consider_threat(1000, 1250), ThreatLevel::SlightlyDangerous);
    }

    #[test]
    fn consider_dangerous() {
        assert_eq!(consider_threat(1000, 1400), ThreatLevel::Dangerous);
    }

    #[test]
    fn consider_very_dangerous() {
        assert_eq!(consider_threat(1000, 1600), ThreatLevel::VeryDangerous);
    }

    #[test]
    fn consider_lethal() {
        assert_eq!(consider_threat(1000, 2000), ThreatLevel::Lethal);
    }

    #[test]
    fn consider_zero_player_power() {
        assert_eq!(consider_threat(0, 100), ThreatLevel::Lethal);
    }

    #[test]
    fn consider_boundary_0_1() {
        // ratio = 100/1000 = 0.1 → Negligible (≤ 0.1)
        assert_eq!(consider_threat(1000, 100), ThreatLevel::Negligible);
    }

    #[test]
    fn consider_boundary_0_5() {
        // ratio = 500/1000 = 0.5 → Harmless (≤ 0.5)
        assert_eq!(consider_threat(1000, 500), ThreatLevel::Harmless);
    }

    #[test]
    fn consider_boundary_0_9() {
        // ratio = 900/1000 = 0.9 → AlmostChallenge (≤ 0.9)
        assert_eq!(consider_threat(1000, 900), ThreatLevel::AlmostChallenge);
    }

    #[test]
    fn consider_boundary_1_2() {
        // ratio = 1200/1000 = 1.2 → EqualPower (≤ 1.2)
        assert_eq!(consider_threat(1000, 1200), ThreatLevel::EqualPower);
    }

    #[test]
    fn consider_boundary_1_3() {
        // ratio = 1300/1000 = 1.3 → SlightlyDangerous (≤ 1.3)
        assert_eq!(consider_threat(1000, 1300), ThreatLevel::SlightlyDangerous);
    }

    #[test]
    fn consider_boundary_1_7() {
        // ratio = 1700/1000 = 1.7 → VeryDangerous (≤ 1.7)
        assert_eq!(consider_threat(1000, 1700), ThreatLevel::VeryDangerous);
    }

    // --- combat_power_level --------------------------------------------------

    #[test]
    fn power_level_melee() {
        let stats = vec![
            test_stat("condition", 100),
            test_stat("speed", 80),
            test_stat("agility", 70),
            test_stat("strength", 90),
            test_stat("wisdom", 50),
            test_stat("inteli", 40),
        ];
        let skills = vec![
            test_skill("dodge", 30),
            test_skill("attack", 60),
            test_skill("shoot", 40),
            test_skill("magic", 20),
        ];
        let power = combat_power_level(&stats, &skills, 200, true, false);
        // 100+80+70+30+200 + 90+60 = 630
        assert_eq!(power, 630);
    }

    #[test]
    fn power_level_ranged() {
        let stats = vec![
            test_stat("condition", 100),
            test_stat("speed", 80),
            test_stat("agility", 70),
            test_stat("strength", 90),
            test_stat("wisdom", 50),
            test_stat("inteli", 40),
        ];
        let skills = vec![
            test_skill("dodge", 30),
            test_skill("attack", 60),
            test_skill("shoot", 40),
            test_skill("magic", 20),
        ];
        let power = combat_power_level(&stats, &skills, 200, false, true);
        // 100+80+70+30+200 + 90+40 = 610
        assert_eq!(power, 610);
    }

    #[test]
    fn power_level_magic() {
        let stats = vec![
            test_stat("condition", 100),
            test_stat("speed", 80),
            test_stat("agility", 70),
            test_stat("strength", 90),
            test_stat("wisdom", 50),
            test_stat("inteli", 40),
        ];
        let skills = vec![
            test_skill("dodge", 30),
            test_skill("attack", 60),
            test_skill("shoot", 40),
            test_skill("magic", 20),
        ];
        let power = combat_power_level(&stats, &skills, 200, false, false);
        // 100+80+70+30+200 + 50+40+20 = 590
        assert_eq!(power, 590);
    }

    // --- stat display --------------------------------------------------------

    #[test]
    fn stat_display_with_bonus() {
        let base = vec![PlayerStat {
            stat_key: "strength".to_owned(),
            label: "Siła".to_owned(),
            base: 100,
            trained: 50,
            modified: 50,
            xp: 12500,
        }];
        let calc = vec![PlayerStat {
            stat_key: "strength".to_owned(),
            label: "Siła".to_owned(),
            base: 100,
            trained: 50,
            modified: 60,
            xp: 12500,
        }];
        let displays = build_stat_displays(&base, &calc);
        assert_eq!(displays.len(), 1);
        assert_eq!(displays[0].current, 60);
        assert_eq!(displays[0].delta, 10);
        // xp progress: 12500 / (50*500) * 100 = 50.0
        assert!((displays[0].xp_progress.unwrap() - 50.0).abs() < 0.001);
    }

    #[test]
    fn stat_display_at_cap() {
        let base = vec![PlayerStat {
            stat_key: "speed".to_owned(),
            label: "Szybkość".to_owned(),
            base: 50,
            trained: 50,
            modified: 50,
            xp: 0,
        }];
        let displays = build_stat_displays(&base, &base);
        assert_eq!(displays[0].delta, 0);
        assert!(displays[0].xp_progress.is_none());
    }

    // --- skill display -------------------------------------------------------

    #[test]
    fn skill_display_hides_thievery_for_non_thief() {
        let base = vec![
            test_skill("attack", 30),
            PlayerSkill {
                skill_key: "thievery".to_owned(),
                label: "Złodziejstwo".to_owned(),
                level: 10,
                xp: 0,
            },
        ];
        let displays = build_skill_displays(&base, &base, false);
        assert_eq!(displays.len(), 1);
        assert_eq!(displays[0].label, "Walka Bronią");
    }

    #[test]
    fn skill_display_shows_thievery_for_thief() {
        let base = vec![PlayerSkill {
            skill_key: "thievery".to_owned(),
            label: "Złodziejstwo".to_owned(),
            level: 10,
            xp: 500,
        }];
        let displays = build_skill_displays(&base, &base, true);
        assert_eq!(displays.len(), 1);
        assert_eq!(displays[0].label, "Złodziejstwo");
        // xp progress: 500 / (10*100) * 100 = 50.0
        assert!((displays[0].xp_progress.unwrap() - 50.0).abs() < 0.001);
    }

    // --- bonus display -------------------------------------------------------

    #[test]
    fn bonus_display_effective_pct() {
        let bonuses = vec![PlayerBonus {
            id: 1,
            catalog_id: 10,
            bonus_name: "mining".to_owned(),
            value: 5,
            duration: 10,
        }];
        let displays = build_bonus_displays(&bonuses);
        assert_eq!(displays.len(), 1);
        assert_eq!(displays[0].effective_pct, 50);
    }

    // --- bless / antidote labels --------------------------------------------

    #[test]
    fn bless_labels_all_keys() {
        assert_eq!(bless_label("strength"), Some("Siły"));
        assert_eq!(bless_label("agility"), Some("Zręczności"));
        assert_eq!(bless_label("smelting"), Some("Hutnictwa"));
        assert_eq!(bless_label("unknown"), None);
    }

    #[test]
    fn antidote_labels() {
        assert_eq!(antidote_label("I"), Some("na truciznę z Illani"));
        assert_eq!(antidote_label("D"), Some("na truciznę z Dynallca"));
        assert_eq!(antidote_label("N"), Some("na truciznę z Nutari"));
        assert_eq!(
            antidote_label("R"),
            Some("Wypita mikstura Oszukania śmierci")
        );
        assert_eq!(antidote_label("X"), None);
    }

    // --- last seen formatting ------------------------------------------------

    #[test]
    fn last_seen_never() {
        assert_eq!(format_last_seen(0, 1_000_000), "Nigdy");
    }

    #[test]
    fn last_seen_now() {
        assert_eq!(format_last_seen(1_000_000, 1_000_030), "Teraz");
    }

    #[test]
    fn last_seen_minutes() {
        let now = 1_000_000;
        let lpv = now - 300; // 5 minutes ago
        assert_eq!(format_last_seen(lpv, now), "około 5 minut temu.");
    }

    #[test]
    fn last_seen_hours() {
        let now = 1_000_000;
        let lpv = now - 7200; // 2 hours ago
        assert_eq!(format_last_seen(lpv, now), "około 2 godzin temu.");
    }

    #[test]
    fn last_seen_yesterday() {
        let now = 1_000_000;
        let lpv = now - 86400; // 1 day ago
        assert_eq!(format_last_seen(lpv, now), "Wczoraj");
    }

    #[test]
    fn last_seen_days() {
        let now = 1_000_000;
        let lpv = now - 259_200; // 3 days ago
        assert_eq!(format_last_seen(lpv, now), "około 3 dni temu.");
    }

    // --- rank formatting -----------------------------------------------------

    #[test]
    fn format_rank_admin_male() {
        assert_eq!(format_rank("Admin", Some("M")), "Administrator");
    }

    #[test]
    fn format_rank_admin_female() {
        assert_eq!(format_rank("Admin", Some("F")), "Administratorka");
    }

    #[test]
    fn format_rank_staff_male() {
        assert_eq!(format_rank("Staff", Some("M")), "Moderator");
    }

    #[test]
    fn format_rank_default_player() {
        assert_eq!(format_rank("Gracz", None), "Gracz");
    }

    #[test]
    fn format_rank_default_female() {
        assert_eq!(format_rank("Gracz", Some("F")), "Graczka");
    }

    // --- battle result label -------------------------------------------------

    #[test]
    fn battle_label_win_male() {
        assert_eq!(battle_result_label(1, 1, Some("M")), "zwyciężył");
    }

    #[test]
    fn battle_label_win_female() {
        assert_eq!(battle_result_label(1, 1, Some("F")), "zwyciężyła");
    }

    #[test]
    fn battle_label_loss() {
        assert_eq!(battle_result_label(1, 2, Some("M")), "przegrał");
    }

    #[test]
    fn battle_label_draw() {
        assert_eq!(battle_result_label(1, 0, None), "zremisował");
    }

    // --- pagination ----------------------------------------------------------

    #[test]
    fn pagination_basic() {
        let p = Pagination::new(100, 1, 30);
        assert_eq!(p.total_pages, 4);
        assert_eq!(p.offset(), 0);
    }

    #[test]
    fn pagination_page_2() {
        let p = Pagination::new(100, 2, 30);
        assert_eq!(p.offset(), 30);
    }

    #[test]
    fn pagination_clamps_past_end() {
        let p = Pagination::new(100, 10, 30);
        assert_eq!(p.current_page, 4);
        assert_eq!(p.offset(), 90);
    }

    #[test]
    fn pagination_zero_items() {
        let p = Pagination::new(0, 1, 30);
        assert_eq!(p.total_pages, 1);
        assert_eq!(p.current_page, 1);
    }

    // --- member sort ---------------------------------------------------------

    #[test]
    fn member_sort_from_param() {
        assert_eq!(MemberSort::from_param("user"), MemberSort::Username);
        assert_eq!(MemberSort::from_param("rank"), MemberSort::Rank);
        assert_eq!(MemberSort::from_param("rasa"), MemberSort::Race);
        assert_eq!(MemberSort::from_param("miejsce"), MemberSort::Location);
        assert_eq!(MemberSort::from_param("id"), MemberSort::Id);
        assert_eq!(MemberSort::from_param("whatever"), MemberSort::Id);
    }

    #[test]
    fn member_sort_sql_columns() {
        assert_eq!(MemberSort::Id.sql_column(), "id");
        assert_eq!(MemberSort::Username.sql_column(), "username");
        assert_eq!(MemberSort::Rank.sql_column(), "rank");
        assert_eq!(MemberSort::Race.sql_column(), "race");
        assert_eq!(MemberSort::Location.sql_column(), "location");
    }

    // --- helpers for tests ---------------------------------------------------

    fn test_player(wins: i32, losses: i32) -> Player {
        Player {
            id: 1,
            username: "TestHero".to_owned(),
            email: "test@example.com".to_owned(),
            rank: super::super::Rank::Member,
            credits: 100,
            energy: 10.0,
            max_energy: 21.0,
            ap: 5,
            wins,
            losses,
            last_killed: "Monster".to_owned(),
            last_killed_by: "Dragon".to_owned(),
            platinum: 0,
            age: 42,
            logins: 100,
            hp: 500,
            max_hp: 500,
            bank: 1000,
            mana: 50,
            last_page_visit: 0,
            current_page: String::new(),
            ip: "127.0.0.1".to_owned(),
            tribe_id: 0,
            profile: "Hello".to_owned(),
            referrals: 0,
            core_pass: false,
            fight: 0,
            trains: 0,
            race: "Elf".to_owned(),
            class: "Mag".to_owned(),
            pw: 10,
            immune: false,
            location: "Altara".to_owned(),
            messenger: String::new(),
            avatar: String::new(),
            tribe_rank: String::new(),
            deity: None,
            maps: 0,
            resting: false,
            crime: 0,
            gender: Some("M".to_owned()),
            bridge: false,
            temp: 0,
            forum_time: 0,
            tforum_time: 0,
            bless: String::new(),
            bless_value: 0,
            antidote: None,
            freeze: 0,
            house_rest: false,
            poll: false,
            astral_crime: false,
            change_deity: 0,
            vallars: 0,
            newbie: 0,
            roleplay: String::new(),
            ooc: String::new(),
            short_rpg: String::new(),
            craft_mission: 0,
            mpoints: 0,
            room: 0,
            chapter: 0,
            craft_skill: String::new(),
            chat_times: String::new(),
            ring_invite: 0,
            tribe_invite: 0,
            team_id: 0,
            reputation: 0,
        }
    }

    fn test_stat(key: &str, modified: i32) -> PlayerStat {
        PlayerStat {
            stat_key: key.to_owned(),
            label: key.to_owned(),
            base: 100,
            trained: modified,
            modified,
            xp: 0,
        }
    }

    fn test_skill(key: &str, level: i32) -> PlayerSkill {
        let label = match key {
            "attack" => "Walka Bronią",
            "dodge" => "Uniki",
            "shoot" => "Strzelectwo",
            "magic" => "Rzucanie Czarów",
            _ => key,
        };
        PlayerSkill {
            skill_key: key.to_owned(),
            label: label.to_owned(),
            level,
            xp: 0,
        }
    }
}
