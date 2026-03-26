//! Mission graph and active mission state models.
//!
//! Maps the `missions`, `mactions`, and `missions2` tables. Missions are a
//! room-based graph exploration system used by chronicle stories, thief jobs,
//! and other adventures.
//!
//! # `missions` table (room graph)
//!
//! ```text
//! id        INT AUTO_INCREMENT
//! name      VARCHAR(255)  — room identifier (e.g. "thief10start", "ele1room3")
//! text      TEXT          — narrative text displayed to the player
//! exits     TEXT          — semicolon-delimited exit definitions
//! chances   VARCHAR(255)  — semicolon-delimited chance percentages for exits
//! mobs      TEXT          — semicolon-delimited mob definitions
//! chances2  VARCHAR(255)  — chance percentages for mobs
//! items     TEXT          — semicolon-delimited item definitions
//! chances3  VARCHAR(255)  — chance percentages for items
//! moreinfo  TEXT          — extra data (e.g. "combat;monster_id;count;win_room;lose_room")
//! ```
//!
//! ## Exit format
//!
//! Each exit is: `"Label,target_room_name"` (e.g. `"Obserwuj i czekaj,thief10wait"`)
//!
//! Some exits have type tags like `[T]` (thief-only), `[E]` (everyone).
//!
//! ## Mob format
//!
//! Each mob is: `"Name,Type,Description[,ActionLabel,ActionTarget]*"`
//! - Type `A` = aggressive (increases difficulty)
//! - Type `T` = target (successful interaction = +1 success)
//! - Type `Q` = quest target (immediate mission finish)
//!
//! ## Item format
//!
//! Each item is: `"Name,Type,Description[,ActionLabel,ActionTarget]*"`
//! - Type `Q` = quest target (mission finish)
//! - Type `T` = target item (+1 success)
//! - Type `O` = other (quest inventory item)
//! - Type `E` = equipment item (real gear)
//!
//! # `mactions` table (active mission per player)
//!
//! ```text
//! pid        INT UNIQUE   — FK to players.id
//! location   INT          — current room id in `missions`
//! exits      TEXT         — current room's active exits (semicolon-delimited)
//! mobs       TEXT         — current room's active mobs
//! items      TEXT         — current room's active items
//! type       CHAR(1)      — mission type: T=thief, E=story, Q=main quest, O=old
//! loot       VARCHAR(255) — loot table spec (e.g. "tools;=1;T")
//! rooms      SMALLINT     — rooms remaining
//! successes  INT          — successful target interactions
//! bonus      INT          — bonus multiplier for rewards
//! place      VARCHAR(30)  — return location after mission ends
//! target     CHAR(1)      — Y=has primary target, N=no target
//! moreinfo   TEXT         — extra state data
//! ```
//!
//! # `missions2` table (chronicle catalog)
//!
//! ```text
//! id        INT AUTO_INCREMENT
//! name      VARCHAR(255)  — mission name prefix (e.g. "ele1")
//! type      CHAR(1)       — Q=main quest, O=old story, E=event/other
//! intro     TEXT          — introduction text displayed before starting
//! location  VARCHAR(50)   — city where mission starts (e.g. "Altara")
//! shortdesc VARCHAR(255)  — short title for the chronicle list
//! chapter   TINYINT       — chapter gate (player must have >= this chapter)
//! ```

/// A single room in the mission graph, from the `missions` table.
#[derive(Debug, Clone, PartialEq, Eq)]
pub struct MissionRoom {
    pub id: i32,
    /// Room identifier string (e.g. `"thief10start"`).
    pub name: String,
    /// Narrative text for this room.
    pub text: String,
    /// Raw semicolon-delimited exit string from DB.
    pub raw_exits: String,
    /// Raw semicolon-delimited exit chances.
    pub raw_chances: String,
    /// Raw semicolon-delimited mob string.
    pub raw_mobs: String,
    /// Raw mob chances.
    pub raw_chances2: String,
    /// Raw semicolon-delimited item string.
    pub raw_items: String,
    /// Raw item chances.
    pub raw_chances3: String,
    /// Raw extra info string.
    pub raw_moreinfo: String,
}

/// The type of a mission, stored as a single character in `mactions.type`
/// and `missions2.type`.
#[derive(Debug, Clone, Copy, PartialEq, Eq)]
pub enum MissionType {
    /// Thief mission (`T`).
    Thief,
    /// Story/event mission (`E`).
    Story,
    /// Main quest chapter (`Q`).
    MainQuest,
    /// Old/past story (`O`).
    OldStory,
}

impl MissionType {
    pub fn from_db(c: &str) -> Option<Self> {
        match c {
            "T" => Some(Self::Thief),
            "E" => Some(Self::Story),
            "Q" => Some(Self::MainQuest),
            "O" => Some(Self::OldStory),
            _ => None,
        }
    }

    pub fn to_db(self) -> &'static str {
        match self {
            Self::Thief => "T",
            Self::Story => "E",
            Self::MainQuest => "Q",
            Self::OldStory => "O",
        }
    }
}

/// Mob interaction type within a mission room.
#[derive(Debug, Clone, Copy, PartialEq, Eq)]
pub enum MobType {
    /// Aggressive — increases steal difficulty, no direct interaction.
    Aggressive,
    /// Target — successful interaction counts as +1 success.
    Target,
    /// Quest target — interaction finishes the mission immediately.
    QuestTarget,
}

impl MobType {
    pub fn from_code(c: &str) -> Option<Self> {
        match c {
            "A" => Some(Self::Aggressive),
            "T" => Some(Self::Target),
            "Q" => Some(Self::QuestTarget),
            _ => None,
        }
    }
}

/// Item interaction type within a mission room.
#[derive(Debug, Clone, Copy, PartialEq, Eq)]
pub enum ItemType {
    /// Quest target — finishes the mission immediately.
    QuestTarget,
    /// Target item — +1 success counter.
    Target,
    /// Other/quest inventory item (type `O`).
    QuestItem,
    /// Real equipment item (type `E`).
    Equipment,
}

impl ItemType {
    pub fn from_code(c: &str) -> Option<Self> {
        match c {
            "Q" => Some(Self::QuestTarget),
            "T" => Some(Self::Target),
            "O" => Some(Self::QuestItem),
            "E" => Some(Self::Equipment),
            _ => None,
        }
    }
}

/// Active mission state for a player, from the `mactions` table.
///
/// This is the persisted portion; the session-only derived fields
/// (parsed exit/mob/item vectors) are computed on load.
#[derive(Debug, Clone, PartialEq, Eq)]
pub struct ActiveMission {
    /// Player ID (unique key).
    pub player_id: i32,
    /// Current room ID in the `missions` table.
    pub current_room_id: i32,
    /// Raw exits string (persisted, semicolon-delimited).
    pub raw_exits: String,
    /// Raw mobs string.
    pub raw_mobs: String,
    /// Raw items string.
    pub raw_items: String,
    /// Mission type.
    pub mission_type: MissionType,
    /// Loot table specification (e.g. `"tools;=1;T"`). Empty = no loot.
    pub loot_spec: String,
    /// Rooms remaining before mission auto-completes.
    pub rooms_remaining: i16,
    /// Successful target interaction counter.
    pub successes: i32,
    /// Bonus multiplier for rewards.
    pub bonus: i32,
    /// Location to return to when mission ends.
    pub return_location: String,
    /// Whether this mission has a primary target (`Y` / `N`).
    pub has_target: bool,
    /// Raw extra info string.
    pub raw_moreinfo: String,
}

impl ActiveMission {
    /// Whether the mission should auto-complete (rooms exhausted).
    pub fn is_rooms_exhausted(&self) -> bool {
        self.rooms_remaining <= 0
    }

    /// Whether the player achieved the quest target.
    pub fn reached_quest_target(&self) -> bool {
        self.has_target && self.successes >= 10
    }
}

/// A chronicle mission entry from the `missions2` table.
#[derive(Debug, Clone, PartialEq, Eq)]
pub struct ChronicleMission {
    pub id: i32,
    /// Name prefix used to look up rooms (e.g. `"ele1"`).
    pub name: String,
    /// Mission type.
    pub mission_type: MissionType,
    /// Introduction text shown before starting.
    pub intro: String,
    /// City where the mission starts.
    pub location: String,
    /// Short description for the chronicle list.
    pub short_desc: String,
    /// Chapter gate — player must have `chapter >= this` to access.
    pub chapter_required: i16,
}

/// Input for checking whether a player can start a chronicle mission.
pub struct StartMissionCheck<'a> {
    pub player_chapter: i16,
    pub mission_chapter: i16,
    pub mission_type: MissionType,
    pub player_location: &'a str,
    pub mission_location: &'a str,
    pub player_hp: i32,
    pub player_energy: f64,
    pub craft_missions_remaining: i16,
    pub has_active_mission: bool,
}

/// Whether a player can start a specific chronicle mission.
pub fn can_start_chronicle_mission(c: &StartMissionCheck<'_>) -> Result<(), StartMissionError> {
    if c.player_hp <= 0 {
        return Err(StartMissionError::Dead);
    }
    if c.player_energy < 2.0 {
        return Err(StartMissionError::NotEnoughEnergy);
    }
    if c.craft_missions_remaining <= 0 {
        return Err(StartMissionError::NoMissionsLeft);
    }
    if c.has_active_mission {
        return Err(StartMissionError::AlreadyOnMission);
    }
    if c.player_location != c.mission_location {
        return Err(StartMissionError::WrongLocation);
    }
    if c.mission_type == MissionType::MainQuest && c.player_chapter < c.mission_chapter {
        return Err(StartMissionError::ChapterLocked);
    }
    Ok(())
}

/// Errors preventing a new mission from starting.
#[derive(Debug, Clone, PartialEq, Eq)]
pub enum StartMissionError {
    Dead,
    NotEnoughEnergy,
    NoMissionsLeft,
    AlreadyOnMission,
    WrongLocation,
    ChapterLocked,
}

/// Thief mission category (from `$_SESSION['mission']` in PHP).
#[derive(Debug, Clone, Copy, PartialEq, Eq)]
pub enum ThiefMissionKind {
    /// Home robbery (case 0).
    HomeRobbery,
    /// Steal from people (case 1).
    Pickpocket,
    /// Tracking people (case 2).
    Tracking,
    /// Guard position (case 3).
    GuardDuty,
}

impl ThiefMissionKind {
    pub fn from_index(i: i32) -> Option<Self> {
        match i {
            0 => Some(Self::HomeRobbery),
            1 => Some(Self::Pickpocket),
            2 => Some(Self::Tracking),
            3 => Some(Self::GuardDuty),
            _ => None,
        }
    }

    /// Minimum `mpoints` required to unlock this mission kind.
    pub fn min_mpoints(self) -> i32 {
        match self {
            Self::HomeRobbery => 10,
            Self::Pickpocket | Self::Tracking => 0,
            Self::GuardDuty => 5,
        }
    }
}

/// Loot reward tier for thief mission completion.
#[derive(Debug, Clone, Copy, PartialEq, Eq)]
pub enum ThiefLootReward {
    /// No extra loot.
    None,
    /// Ordinary lockpick (roll >= 80).
    OrdinaryLockpick,
    /// Better lockpick (roll >= 85).
    BetterLockpick,
    /// Ordinary lockpick plan (roll >= 90).
    OrdinaryPlan,
    /// Better lockpick plan (roll >= 97).
    BetterPlan,
}

impl ThiefLootReward {
    /// Build the loot spec string for the `mactions` table.
    pub fn to_loot_spec(self) -> &'static str {
        match self {
            Self::None => "",
            Self::OrdinaryLockpick => "tools;=1;T",
            Self::BetterLockpick => "tools;>1;T",
            Self::OrdinaryPlan => "plans;=1;T",
            Self::BetterPlan => "plans;>1;T",
        }
    }
}

/// Outcome of a successful mission completion.
#[derive(Debug, Clone, PartialEq, Eq)]
pub struct MissionReward {
    /// XP awarded (goes to condition stat).
    pub xp: i32,
    /// Gold awarded.
    pub gold: i32,
    /// Mission points awarded (0 or 1).
    pub mission_points: i32,
}

/// Calculate the reward for a completed mission.
///
/// Based on the PHP logic in `mission.php` lines 620–650:
/// - If the mission has targets and the quest target was reached,
///   XP = `5 * successes + 5 * bonus`, gold = `5 * successes * 50 + 10 * bonus`.
/// - If targets exist but quest wasn't reached,
///   XP = `5 * successes`, gold = `successes * 50`.
/// - If no successes at all, XP = 1, gold = 0.
pub fn calculate_mission_reward(
    successes: i32,
    bonus: i32,
    has_target: bool,
    quest_target_reached: bool,
) -> MissionReward {
    if successes <= 0 {
        return MissionReward {
            xp: 1,
            gold: 0,
            mission_points: 0,
        };
    }

    if has_target && quest_target_reached {
        MissionReward {
            xp: 5 * successes + 5 * bonus,
            gold: 5 * successes * 50 + 10 * bonus,
            mission_points: 1,
        }
    } else {
        MissionReward {
            xp: 5 * successes,
            gold: successes * 50,
            mission_points: 0,
        }
    }
}

/// The difficulty check for interacting with a mob/target during a mission.
///
/// Based on PHP logic in `mission.php`:
/// - Start at difficulty 10
/// - +5 per mob in the room
/// - +10 per aggressive mob
/// - +20 if the action target is an aggressive mob
/// - subtract player's thievery skill
/// - clamp to [5, 95]
///
/// Returns the difficulty percentage (1–100 roll must be <= this to fail).
pub fn steal_difficulty(
    room_mob_count: i32,
    aggressive_mob_count: i32,
    target_is_aggressive: bool,
    thievery_skill: i32,
) -> i32 {
    let mut diff = 10 + 5 * room_mob_count + 10 * aggressive_mob_count;
    if target_is_aggressive {
        diff += 20;
    }
    diff -= thievery_skill;
    diff.clamp(5, 95)
}

// ---------------------------------------------------------------------------
// Tests
// ---------------------------------------------------------------------------

#[cfg(test)]
mod tests {
    use super::*;

    #[test]
    fn mission_type_roundtrip() {
        for &mt in &[
            MissionType::Thief,
            MissionType::Story,
            MissionType::MainQuest,
            MissionType::OldStory,
        ] {
            assert_eq!(MissionType::from_db(mt.to_db()), Some(mt));
        }
    }

    #[test]
    fn mission_type_invalid() {
        assert_eq!(MissionType::from_db("X"), None);
    }

    #[test]
    fn mob_type_parsing() {
        assert_eq!(MobType::from_code("A"), Some(MobType::Aggressive));
        assert_eq!(MobType::from_code("T"), Some(MobType::Target));
        assert_eq!(MobType::from_code("Q"), Some(MobType::QuestTarget));
        assert_eq!(MobType::from_code("Z"), None);
    }

    #[test]
    fn item_type_parsing() {
        assert_eq!(ItemType::from_code("Q"), Some(ItemType::QuestTarget));
        assert_eq!(ItemType::from_code("T"), Some(ItemType::Target));
        assert_eq!(ItemType::from_code("O"), Some(ItemType::QuestItem));
        assert_eq!(ItemType::from_code("E"), Some(ItemType::Equipment));
        assert_eq!(ItemType::from_code("X"), None);
    }

    #[test]
    fn active_mission_rooms_exhausted() {
        let m = ActiveMission {
            player_id: 1,
            current_room_id: 10,
            raw_exits: String::new(),
            raw_mobs: String::new(),
            raw_items: String::new(),
            mission_type: MissionType::Thief,
            loot_spec: String::new(),
            rooms_remaining: 0,
            successes: 3,
            bonus: 10,
            return_location: "Altara".into(),
            has_target: true,
            raw_moreinfo: String::new(),
        };
        assert!(m.is_rooms_exhausted());
    }

    #[test]
    fn active_mission_not_exhausted() {
        let m = ActiveMission {
            player_id: 1,
            current_room_id: 10,
            raw_exits: String::new(),
            raw_mobs: String::new(),
            raw_items: String::new(),
            mission_type: MissionType::Thief,
            loot_spec: String::new(),
            rooms_remaining: 5,
            successes: 0,
            bonus: 0,
            return_location: "Altara".into(),
            has_target: false,
            raw_moreinfo: String::new(),
        };
        assert!(!m.is_rooms_exhausted());
    }

    #[test]
    fn quest_target_reached_needs_target_and_ten_successes() {
        let m = ActiveMission {
            player_id: 1,
            current_room_id: 10,
            raw_exits: String::new(),
            raw_mobs: String::new(),
            raw_items: String::new(),
            mission_type: MissionType::Thief,
            loot_spec: String::new(),
            rooms_remaining: 0,
            successes: 10,
            bonus: 0,
            return_location: "Altara".into(),
            has_target: true,
            raw_moreinfo: String::new(),
        };
        assert!(m.reached_quest_target());

        // Not enough successes
        let m2 = ActiveMission {
            successes: 9,
            ..m.clone()
        };
        assert!(!m2.reached_quest_target());

        // No target flag
        let m3 = ActiveMission {
            has_target: false,
            successes: 10,
            ..m
        };
        assert!(!m3.reached_quest_target());
    }

    #[test]
    fn can_start_chronicle_ok() {
        assert!(can_start_chronicle_mission(&StartMissionCheck {
            player_chapter: 1,
            mission_chapter: 0,
            mission_type: MissionType::Story,
            player_location: "Altara",
            mission_location: "Altara",
            player_hp: 100,
            player_energy: 5.0,
            craft_missions_remaining: 3,
            has_active_mission: false,
        })
        .is_ok());
    }

    #[test]
    fn can_start_chronicle_dead() {
        assert_eq!(
            can_start_chronicle_mission(&StartMissionCheck {
                player_chapter: 1,
                mission_chapter: 0,
                mission_type: MissionType::Story,
                player_location: "Altara",
                mission_location: "Altara",
                player_hp: 0,
                player_energy: 5.0,
                craft_missions_remaining: 3,
                has_active_mission: false,
            }),
            Err(StartMissionError::Dead)
        );
    }

    #[test]
    fn can_start_chronicle_low_energy() {
        assert_eq!(
            can_start_chronicle_mission(&StartMissionCheck {
                player_chapter: 1,
                mission_chapter: 0,
                mission_type: MissionType::Story,
                player_location: "Altara",
                mission_location: "Altara",
                player_hp: 100,
                player_energy: 1.5,
                craft_missions_remaining: 3,
                has_active_mission: false,
            }),
            Err(StartMissionError::NotEnoughEnergy)
        );
    }

    #[test]
    fn can_start_chronicle_no_missions() {
        assert_eq!(
            can_start_chronicle_mission(&StartMissionCheck {
                player_chapter: 1,
                mission_chapter: 0,
                mission_type: MissionType::Story,
                player_location: "Altara",
                mission_location: "Altara",
                player_hp: 100,
                player_energy: 5.0,
                craft_missions_remaining: 0,
                has_active_mission: false,
            }),
            Err(StartMissionError::NoMissionsLeft)
        );
    }

    #[test]
    fn can_start_chronicle_already_active() {
        assert_eq!(
            can_start_chronicle_mission(&StartMissionCheck {
                player_chapter: 1,
                mission_chapter: 0,
                mission_type: MissionType::Story,
                player_location: "Altara",
                mission_location: "Altara",
                player_hp: 100,
                player_energy: 5.0,
                craft_missions_remaining: 3,
                has_active_mission: true,
            }),
            Err(StartMissionError::AlreadyOnMission)
        );
    }

    #[test]
    fn can_start_chronicle_wrong_location() {
        assert_eq!(
            can_start_chronicle_mission(&StartMissionCheck {
                player_chapter: 1,
                mission_chapter: 0,
                mission_type: MissionType::Story,
                player_location: "Ardulith",
                mission_location: "Altara",
                player_hp: 100,
                player_energy: 5.0,
                craft_missions_remaining: 3,
                has_active_mission: false,
            }),
            Err(StartMissionError::WrongLocation)
        );
    }

    #[test]
    fn can_start_chronicle_chapter_locked() {
        assert_eq!(
            can_start_chronicle_mission(&StartMissionCheck {
                player_chapter: 0,
                mission_chapter: 1,
                mission_type: MissionType::MainQuest,
                player_location: "Altara",
                mission_location: "Altara",
                player_hp: 100,
                player_energy: 5.0,
                craft_missions_remaining: 3,
                has_active_mission: false,
            }),
            Err(StartMissionError::ChapterLocked)
        );
    }

    #[test]
    fn can_start_non_quest_ignores_chapter() {
        // Story type ignores chapter gate
        assert!(can_start_chronicle_mission(&StartMissionCheck {
            player_chapter: 0,
            mission_chapter: 5,
            mission_type: MissionType::Story,
            player_location: "Altara",
            mission_location: "Altara",
            player_hp: 100,
            player_energy: 5.0,
            craft_missions_remaining: 3,
            has_active_mission: false,
        })
        .is_ok());
    }

    #[test]
    fn reward_no_successes() {
        let r = calculate_mission_reward(0, 10, true, false);
        assert_eq!(r.xp, 1);
        assert_eq!(r.gold, 0);
        assert_eq!(r.mission_points, 0);
    }

    #[test]
    fn reward_with_quest_target() {
        let r = calculate_mission_reward(10, 10, true, true);
        assert_eq!(r.xp, 5 * 10 + 5 * 10); // 100
        assert_eq!(r.gold, 5 * 10 * 50 + 10 * 10); // 2600
        assert_eq!(r.mission_points, 1);
    }

    #[test]
    fn reward_without_quest_target() {
        let r = calculate_mission_reward(5, 10, true, false);
        assert_eq!(r.xp, 25);
        assert_eq!(r.gold, 250);
        assert_eq!(r.mission_points, 0);
    }

    #[test]
    fn thief_mission_kind_min_mpoints() {
        assert_eq!(ThiefMissionKind::HomeRobbery.min_mpoints(), 10);
        assert_eq!(ThiefMissionKind::Pickpocket.min_mpoints(), 0);
        assert_eq!(ThiefMissionKind::Tracking.min_mpoints(), 0);
        assert_eq!(ThiefMissionKind::GuardDuty.min_mpoints(), 5);
    }

    #[test]
    fn thief_loot_spec_roundtrip() {
        assert_eq!(ThiefLootReward::None.to_loot_spec(), "");
        assert_eq!(
            ThiefLootReward::OrdinaryLockpick.to_loot_spec(),
            "tools;=1;T"
        );
        assert_eq!(ThiefLootReward::BetterLockpick.to_loot_spec(), "tools;>1;T");
        assert_eq!(ThiefLootReward::OrdinaryPlan.to_loot_spec(), "plans;=1;T");
        assert_eq!(ThiefLootReward::BetterPlan.to_loot_spec(), "plans;>1;T");
    }

    #[test]
    fn steal_difficulty_base() {
        // 1 mob, 0 aggressive, not aggressive target, 0 thievery
        // diff = 10 + 5*1 + 10*0 - 0 = 15
        assert_eq!(steal_difficulty(1, 0, false, 0), 15);
    }

    #[test]
    fn steal_difficulty_with_aggressive() {
        // 3 mobs, 2 aggressive, target is aggressive, 10 thievery
        // diff = 10 + 5*3 + 10*2 + 20 - 10 = 55
        assert_eq!(steal_difficulty(3, 2, true, 10), 55);
    }

    #[test]
    fn steal_difficulty_clamps_low() {
        // High thievery skill brings it below 5
        assert_eq!(steal_difficulty(0, 0, false, 100), 5);
    }

    #[test]
    fn steal_difficulty_clamps_high() {
        // Many mobs max at 95
        assert_eq!(steal_difficulty(20, 20, true, 0), 95);
    }
}
