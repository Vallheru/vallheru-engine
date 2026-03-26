//! Mission room parsing and state advancement.
//!
//! Converts the semicolon/comma-delimited raw strings stored in the `missions`
//! and `mactions` tables into strongly-typed structures, and provides the
//! room-generation logic (rolling chances, filtering by type/class, ensuring
//! at least one exit).
//!
//! This is the "generic mission graph loader" referenced by MP-14-02.

use super::mission::{ItemType, MissionRoom, MobType};

// ---------------------------------------------------------------------------
// Parsed room elements
// ---------------------------------------------------------------------------

/// A parsed exit from a mission room.
///
/// Raw format: `"Label,target_room_name"`.
/// May be prefixed with `[T]`, `[E]`, etc. indicating mission-type filter.
#[derive(Debug, Clone, PartialEq, Eq)]
pub struct ParsedExit {
    /// Human-readable label shown to the player (e.g. "Obserwuj i czekaj").
    pub label: String,
    /// Target room name (e.g. "thief10wait").
    pub target: String,
    /// Optional single-char type filter (e.g. 'T' for thief-only).
    /// `None` means available to all mission types.
    pub type_filter: Option<char>,
}

/// A parsed mob from a mission room.
///
/// Raw format: `"Name,Type,Description[,ActionLabel,ActionTarget]*"`.
#[derive(Debug, Clone, PartialEq, Eq)]
pub struct ParsedMob {
    /// Mob name.
    pub name: String,
    /// Mob type (`Aggressive` / `Target` / `QuestTarget`).
    pub mob_type: MobType,
    /// Descriptive text appended to room narrative.
    pub description: String,
    /// Interactive actions the player can take (label → target pairs).
    pub actions: Vec<(String, String)>,
}

/// A parsed item from a mission room.
///
/// Raw format: `"Name,Type,Description[,ActionLabel,ActionTarget]*"`.
#[derive(Debug, Clone, PartialEq, Eq)]
pub struct ParsedItem {
    /// Item name.
    pub name: String,
    /// Item type.
    pub item_type: ItemType,
    /// Descriptive text appended to room narrative.
    pub description: String,
    /// Interactive actions (label → target pairs).
    pub actions: Vec<(String, String)>,
}

/// The extra info attached to a room, parsed from `moreinfo`.
///
/// Raw format (semicolon-delimited):
/// - `"combat;monster_level;count;win_room;lose_room"` → combat encounter
/// - `"skill;skill_key"` → skill XP grant on entry
/// - Empty → nothing
#[derive(Debug, Clone, PartialEq, Eq)]
pub enum ParsedMoreInfo {
    /// No extra info.
    None,
    /// A combat encounter in this room.
    Combat {
        /// Monster level bracket for `randommonster()`.
        monster_level: String,
        /// Number of fights required.
        fight_count: i32,
        /// Room name to go to on win.
        win_room: String,
        /// Room name to go to on loss.
        lose_room: String,
    },
    /// Grant skill XP on entering this room.
    SkillGrant {
        /// The skill key to award XP for (e.g. "thievery").
        skill_key: String,
    },
    /// Raw unrecognised data (forward-compat).
    Other(Vec<String>),
}

/// The name prefix of a mission room (e.g. "ele1" from "ele1room3").
///
/// Used to find the finish/fail rooms: `{prefix}finish`, `{prefix}finishgood`,
/// `{prefix}fail`.
pub fn extract_room_prefix(room_name: &str) -> &str {
    // Pattern: letters followed by digits, then the rest.
    // PHP uses: preg_match('/^[a-zA-Z]+[0-9]+/', ...)
    let end = room_name
        .char_indices()
        .skip_while(|(_, c)| c.is_ascii_alphabetic())
        .skip_while(|(_, c)| c.is_ascii_digit())
        .map(|(i, _)| i)
        .next()
        .unwrap_or(room_name.len());

    &room_name[..end]
}

// ---------------------------------------------------------------------------
// Parsing functions
// ---------------------------------------------------------------------------

/// Parse the raw exits string from a `MissionRoom` into typed exits.
///
/// Handles the `[X]` type-filter prefix and class-placeholder expansions.
pub fn parse_exits(raw: &str) -> Vec<ParsedExit> {
    if raw.is_empty() {
        return Vec::new();
    }

    raw.split(';')
        .filter(|s| !s.is_empty())
        .filter_map(|entry| {
            let (type_filter, clean) = extract_type_filter(entry);
            let parts: Vec<&str> = clean.splitn(2, ',').collect();
            if parts.len() < 2 {
                return Option::None;
            }
            Some(ParsedExit {
                label: parts[0].to_owned(),
                target: parts[1].to_owned(),
                type_filter,
            })
        })
        .collect()
}

/// Parse the raw mobs string into typed mobs.
pub fn parse_mobs(raw: &str) -> Vec<ParsedMob> {
    if raw.is_empty() {
        return Vec::new();
    }

    raw.split(';')
        .filter(|s| !s.is_empty())
        .filter_map(|entry| {
            let parts: Vec<&str> = entry.split(',').collect();
            if parts.len() < 3 {
                return Option::None;
            }
            let mob_type = MobType::from_code(parts[1])?;
            let mut actions = Vec::new();
            let mut i = 3;
            while i + 1 < parts.len() {
                actions.push((parts[i].to_owned(), parts[i + 1].to_owned()));
                i += 2;
            }
            Some(ParsedMob {
                name: parts[0].to_owned(),
                mob_type,
                description: parts[2].to_owned(),
                actions,
            })
        })
        .collect()
}

/// Parse the raw items string into typed items.
pub fn parse_items(raw: &str) -> Vec<ParsedItem> {
    if raw.is_empty() {
        return Vec::new();
    }

    raw.split(';')
        .filter(|s| !s.is_empty())
        .filter_map(|entry| {
            let parts: Vec<&str> = entry.split(',').collect();
            if parts.len() < 3 {
                return Option::None;
            }
            let item_type = ItemType::from_code(parts[1])?;
            let mut actions = Vec::new();
            let mut i = 3;
            while i + 1 < parts.len() {
                actions.push((parts[i].to_owned(), parts[i + 1].to_owned()));
                i += 2;
            }
            Some(ParsedItem {
                name: parts[0].to_owned(),
                item_type,
                description: parts[2].to_owned(),
                actions,
            })
        })
        .collect()
}

/// Parse the `moreinfo` field.
pub fn parse_moreinfo(raw: &str) -> ParsedMoreInfo {
    if raw.is_empty() {
        return ParsedMoreInfo::None;
    }

    let parts: Vec<&str> = raw.split(';').collect();
    match parts.first().copied() {
        Some("combat") if parts.len() >= 5 => ParsedMoreInfo::Combat {
            monster_level: parts[1].to_owned(),
            fight_count: parts[2].parse().unwrap_or(1),
            win_room: parts[3].to_owned(),
            lose_room: parts[4].to_owned(),
        },
        Some("skill") if parts.len() >= 2 => ParsedMoreInfo::SkillGrant {
            skill_key: parts[1].to_owned(),
        },
        _ => ParsedMoreInfo::Other(parts.into_iter().map(String::from).collect()),
    }
}

/// Extract a `[X]` type-filter prefix from a string, returning the filter
/// char and the remaining string.
fn extract_type_filter(s: &str) -> (Option<char>, &str) {
    let s = s.trim();
    if s.len() >= 3 && s.starts_with('[') {
        if let Some(end) = s.find(']') {
            let filter_str = &s[1..end];
            if filter_str.len() == 1 {
                let c = filter_str.chars().next().unwrap();
                return (Some(c), s[end + 1..].trim_start());
            }
        }
    }
    (None, s)
}

// ---------------------------------------------------------------------------
// Class placeholder expansion
// ---------------------------------------------------------------------------

/// Map of class names to their placeholder tokens used in mission definitions.
const CLASS_PLACEHOLDERS: &[(&str, &str)] = &[
    ("Wojownik", "%fight%"),
    ("Mag", "%mag%"),
    ("Barbarzyńca", "%barb%"),
    ("Złodziej", "%thief%"),
    ("Rzemieślnik", "%craft%"),
];

/// Expand class placeholders in a raw option string.
///
/// - `%prof%` → replaced with the player's class name
/// - `%fight%`, `%mag%` etc. → if the player is that class, replaced with
///   the class name; otherwise the containing action pair is removed.
///
/// This mirrors the PHP `parseOptions()` function in `mission.php`.
pub fn expand_class_placeholders(raw: &str, player_class: &str) -> String {
    let mut result = raw.replace("%prof%", player_class);

    for &(class_name, placeholder) in CLASS_PLACEHOLDERS {
        if !result.contains(placeholder) {
            continue;
        }
        if player_class == class_name {
            result = result.replace(placeholder, class_name);
        } else {
            // Remove the action pair containing this placeholder.
            // The pair is two consecutive comma-separated fields.
            result = remove_placeholder_pair(&result, placeholder);
        }
    }

    result
}

/// Remove the comma-separated pair containing the placeholder from a
/// comma-delimited string.
///
/// E.g. for `"Label1,target1,FightAction,%fight%target"` and placeholder
/// `%fight%`, removes `",FightAction,%fight%target"` → `"Label1,target1"`.
fn remove_placeholder_pair(csv: &str, placeholder: &str) -> String {
    let parts: Vec<&str> = csv.split(',').collect();
    let mut result: Vec<&str> = Vec::with_capacity(parts.len());
    let mut i = 0;
    while i < parts.len() {
        if parts[i].contains(placeholder) {
            // Remove this field and the preceding field (the label).
            if !result.is_empty() {
                result.pop();
            }
            i += 1;
            continue;
        }
        // Check if the *next* field contains the placeholder — then skip
        // both the label (current) and the target (next).
        if i + 1 < parts.len() && parts[i + 1].contains(placeholder) {
            i += 2;
            continue;
        }
        result.push(parts[i]);
        i += 1;
    }
    result.join(",")
}

// ---------------------------------------------------------------------------
// Room generation (chance rolling)
// ---------------------------------------------------------------------------

/// Filter parsed exits by mission type, removing type-gated exits that
/// don't match.
pub fn filter_exits_by_type(exits: &[ParsedExit], mission_type_code: &str) -> Vec<ParsedExit> {
    exits
        .iter()
        .filter(|e| match e.type_filter {
            Some(c) => {
                let mut buf = [0u8; 4];
                c.encode_utf8(&mut buf);
                &buf[..c.len_utf8()] == mission_type_code.as_bytes()
            }
            None => true,
        })
        .cloned()
        .collect()
}

/// Roll chances for a set of options, returning only those that pass.
///
/// `chances` is a parallel array of percentage thresholds (0..100).
/// For each entry, a random roll in `[0, 100]` must be strictly less than
/// the chance value for the entry to be included.
///
/// The `roll_fn` parameter allows injection for deterministic testing.
pub fn roll_options<T: Clone>(
    options: &[T],
    chances: &[i32],
    mut roll_fn: impl FnMut() -> i32,
) -> Vec<T> {
    options
        .iter()
        .zip(chances.iter())
        .filter_map(|(opt, &chance)| {
            let roll = roll_fn();
            if roll < chance {
                Some(opt.clone())
            } else {
                None
            }
        })
        .collect()
}

/// Parse a semicolon-delimited chances string into integer percentages.
pub fn parse_chances(raw: &str) -> Vec<i32> {
    if raw.is_empty() {
        return Vec::new();
    }
    raw.split(';')
        .map(|s| s.trim().parse::<i32>().unwrap_or(0))
        .collect()
}

// ---------------------------------------------------------------------------
// Display helpers
// ---------------------------------------------------------------------------

/// Collect all available player actions from the current room state.
///
/// Returns a list of `(action_target, display_label)` pairs matching the
/// template's `Moptions` map.
pub fn collect_room_actions(
    exits: &[ParsedExit],
    mobs: &[ParsedMob],
    items: &[ParsedItem],
) -> Vec<(String, String)> {
    let mut actions = Vec::new();

    for exit in exits {
        actions.push((exit.target.clone(), exit.label.clone()));
    }
    for mob in mobs {
        for (label, target) in &mob.actions {
            actions.push((target.clone(), label.clone()));
        }
    }
    for item in items {
        for (label, target) in &item.actions {
            actions.push((target.clone(), label.clone()));
        }
    }

    actions
}

/// Build the full room narrative text by appending mob/item descriptions.
pub fn build_room_text(base_text: &str, mobs: &[ParsedMob], items: &[ParsedItem]) -> String {
    let mut text = base_text.to_owned();
    for mob in mobs {
        if !mob.description.is_empty() {
            text.push(' ');
            text.push_str(&mob.description);
        }
    }
    for item in items {
        if !item.description.is_empty() {
            text.push(' ');
            text.push_str(&item.description);
        }
    }
    text
}

/// Check whether a room name indicates a terminal state (resign/finish/fail).
pub fn is_terminal_room(room_name: &str) -> bool {
    room_name.contains("resign") || room_name.contains("finish") || room_name.contains("fail")
}

// ---------------------------------------------------------------------------
// Action validation
// ---------------------------------------------------------------------------

/// All valid action targets from the current room state.
pub fn valid_action_targets(
    exits: &[ParsedExit],
    mobs: &[ParsedMob],
    items: &[ParsedItem],
    moreinfo: &ParsedMoreInfo,
) -> Vec<String> {
    let mut targets: Vec<String> = exits.iter().map(|e| e.target.clone()).collect();

    for mob in mobs {
        for (_, target) in &mob.actions {
            targets.push(target.clone());
        }
    }
    for item in items {
        for (_, target) in &item.actions {
            targets.push(target.clone());
        }
    }

    // Combat moreinfo adds win/lose room names as valid transitions.
    if let ParsedMoreInfo::Combat {
        win_room,
        lose_room,
        ..
    } = moreinfo
    {
        targets.push(win_room.clone());
        targets.push(lose_room.clone());
    }

    targets
}

/// Determine whether a player's chosen action is a mob/item interaction
/// (as opposed to an exit).
pub fn is_mob_or_item_action(action: &str, mobs: &[ParsedMob], items: &[ParsedItem]) -> bool {
    mobs.iter()
        .any(|m| m.actions.iter().any(|(_, t)| t == action))
        || items
            .iter()
            .any(|i| i.actions.iter().any(|(_, t)| t == action))
}

/// Find which mob the action targets and compute the steal difficulty context.
///
/// Returns `(room_mob_count, aggressive_mob_count, target_is_aggressive)`.
#[allow(clippy::cast_possible_truncation, clippy::cast_possible_wrap)]
pub fn mob_difficulty_context(action: &str, mobs: &[ParsedMob]) -> (i32, i32, bool) {
    let room_mob_count = mobs.len() as i32;
    let aggressive_mob_count = mobs
        .iter()
        .filter(|m| m.mob_type == MobType::Aggressive)
        .count() as i32;
    let target_is_aggressive = mobs
        .iter()
        .any(|m| m.mob_type == MobType::Aggressive && m.actions.iter().any(|(_, t)| t == action));

    (room_mob_count, aggressive_mob_count, target_is_aggressive)
}

/// Find the item entry that matches the given action target.
pub fn find_item_by_action<'a>(action: &str, items: &'a [ParsedItem]) -> Option<&'a ParsedItem> {
    items
        .iter()
        .find(|i| i.actions.iter().any(|(_, t)| t == action))
}

/// Serialize the current room state back to semicolon-delimited strings
/// for persistence in `mactions`.
pub fn serialize_exits(exits: &[ParsedExit]) -> String {
    exits
        .iter()
        .map(|e| format!("{},{}", e.label, e.target))
        .collect::<Vec<_>>()
        .join(";")
}

/// Serialize parsed mobs back to the raw format.
pub fn serialize_mobs(mobs: &[ParsedMob]) -> String {
    mobs.iter()
        .map(|m| {
            let mut parts = vec![
                m.name.clone(),
                mob_type_code(m.mob_type).to_owned(),
                m.description.clone(),
            ];
            for (label, target) in &m.actions {
                parts.push(label.clone());
                parts.push(target.clone());
            }
            parts.join(",")
        })
        .collect::<Vec<_>>()
        .join(";")
}

/// Serialize parsed items back to the raw format.
pub fn serialize_items(items: &[ParsedItem]) -> String {
    items
        .iter()
        .map(|i| {
            let mut parts = vec![
                i.name.clone(),
                item_type_code(i.item_type).to_owned(),
                i.description.clone(),
            ];
            for (label, target) in &i.actions {
                parts.push(label.clone());
                parts.push(target.clone());
            }
            parts.join(",")
        })
        .collect::<Vec<_>>()
        .join(";")
}

/// Serialize `ParsedMoreInfo` back to semicolon-delimited string.
pub fn serialize_moreinfo(info: &ParsedMoreInfo) -> String {
    match info {
        ParsedMoreInfo::None => String::new(),
        ParsedMoreInfo::Combat {
            monster_level,
            fight_count,
            win_room,
            lose_room,
        } => format!("combat;{monster_level};{fight_count};{win_room};{lose_room}"),
        ParsedMoreInfo::SkillGrant { skill_key } => format!("skill;{skill_key}"),
        ParsedMoreInfo::Other(parts) => parts.join(";"),
    }
}

fn mob_type_code(mt: MobType) -> &'static str {
    match mt {
        MobType::Aggressive => "A",
        MobType::Target => "T",
        MobType::QuestTarget => "Q",
    }
}

fn item_type_code(it: ItemType) -> &'static str {
    match it {
        ItemType::QuestTarget => "Q",
        ItemType::Target => "T",
        ItemType::QuestItem => "O",
        ItemType::Equipment => "E",
    }
}

// ---------------------------------------------------------------------------
// Room generation from a MissionRoom template
// ---------------------------------------------------------------------------

/// A fully parsed room ready for display or state storage.
#[derive(Debug, Clone)]
pub struct GeneratedRoom {
    pub room_id: i32,
    pub room_name: String,
    pub text: String,
    pub exits: Vec<ParsedExit>,
    pub mobs: Vec<ParsedMob>,
    pub items: Vec<ParsedItem>,
    pub moreinfo: ParsedMoreInfo,
}

/// Generate a room from a [`MissionRoom`] template, rolling chances and
/// filtering by mission type and player class.
///
/// `roll_fn` is called to produce a random `[0, 100]` value for each
/// option — inject a deterministic function for testing.
///
/// Exits are re-rolled until at least one is present (matching PHP behavior).
pub fn generate_room(
    room: &MissionRoom,
    mission_type_code: &str,
    player_class: &str,
    mut roll_fn: impl FnMut() -> i32,
) -> GeneratedRoom {
    let expanded_exits = expand_class_placeholders(&room.raw_exits, player_class);
    let expanded_mobs = expand_class_placeholders(&room.raw_mobs, player_class);
    let expanded_items = expand_class_placeholders(&room.raw_items, player_class);

    let all_exits = parse_exits(&expanded_exits);
    let filtered_exits = filter_exits_by_type(&all_exits, mission_type_code);
    let exit_chances = parse_chances(&room.raw_chances);
    let mob_chances = parse_chances(&room.raw_chances2);
    let item_chances = parse_chances(&room.raw_chances3);

    // Roll exits until at least one is produced (PHP: while count==0 loop).
    let exits = loop {
        let rolled = roll_options(&filtered_exits, &exit_chances, &mut roll_fn);
        if !rolled.is_empty() || filtered_exits.is_empty() {
            break rolled;
        }
    };

    let mobs = {
        let all_mobs = parse_mobs(&expanded_mobs);
        roll_options(&all_mobs, &mob_chances, &mut roll_fn)
    };

    let items = {
        let all_items = parse_items(&expanded_items);
        roll_options(&all_items, &item_chances, &mut roll_fn)
    };

    let moreinfo = parse_moreinfo(&room.raw_moreinfo);

    GeneratedRoom {
        room_id: room.id,
        room_name: room.name.clone(),
        text: room.text.clone(),
        exits,
        mobs,
        items,
        moreinfo,
    }
}

/// Snapshot the generated room state into session-compatible raw strings
/// for persisting back to `mactions`.
pub fn snapshot_room_state(room: &GeneratedRoom) -> ActiveMissionRoomState {
    ActiveMissionRoomState {
        location: room.room_id,
        raw_exits: serialize_exits(&room.exits),
        raw_mobs: serialize_mobs(&room.mobs),
        raw_items: serialize_items(&room.items),
        raw_moreinfo: serialize_moreinfo(&room.moreinfo),
    }
}

/// The room-specific fields that go into an `mactions` UPDATE.
#[derive(Debug, Clone, PartialEq, Eq)]
pub struct ActiveMissionRoomState {
    pub location: i32,
    pub raw_exits: String,
    pub raw_mobs: String,
    pub raw_items: String,
    pub raw_moreinfo: String,
}

// ============================================================================
// Tests
// ============================================================================

#[cfg(test)]
mod tests {
    use super::*;

    // --- extract_room_prefix ------------------------------------------------

    #[test]
    fn prefix_standard() {
        assert_eq!(extract_room_prefix("ele1room3"), "ele1");
    }

    #[test]
    fn prefix_thief() {
        assert_eq!(extract_room_prefix("thief10start"), "thief10");
    }

    #[test]
    fn prefix_no_suffix() {
        assert_eq!(extract_room_prefix("abc123"), "abc123");
    }

    #[test]
    fn prefix_only_letters() {
        assert_eq!(extract_room_prefix("abcdef"), "abcdef");
    }

    #[test]
    fn prefix_empty() {
        assert_eq!(extract_room_prefix(""), "");
    }

    // --- parse_exits --------------------------------------------------------

    #[test]
    fn parse_exits_basic() {
        let exits = parse_exits("Idź dalej,room2;Wróć,room1");
        assert_eq!(exits.len(), 2);
        assert_eq!(exits[0].label, "Idź dalej");
        assert_eq!(exits[0].target, "room2");
        assert!(exits[0].type_filter.is_none());
        assert_eq!(exits[1].label, "Wróć");
        assert_eq!(exits[1].target, "room1");
    }

    #[test]
    fn parse_exits_with_type_filter() {
        let exits = parse_exits("[T]Kradnij,thief10steal;Idź,room2");
        assert_eq!(exits.len(), 2);
        assert_eq!(exits[0].type_filter, Some('T'));
        assert_eq!(exits[0].label, "Kradnij");
        assert_eq!(exits[0].target, "thief10steal");
        assert!(exits[1].type_filter.is_none());
    }

    #[test]
    fn parse_exits_empty() {
        assert!(parse_exits("").is_empty());
    }

    // --- parse_mobs ---------------------------------------------------------

    #[test]
    fn parse_mobs_basic() {
        let mobs = parse_mobs(
            "Strażnik,A,Widzisz strażnika.;Kupiec,T,Widzisz kupca.,Okradnij,thief10steal",
        );
        assert_eq!(mobs.len(), 2);
        assert_eq!(mobs[0].name, "Strażnik");
        assert_eq!(mobs[0].mob_type, MobType::Aggressive);
        assert_eq!(mobs[0].description, "Widzisz strażnika.");
        assert!(mobs[0].actions.is_empty());
        assert_eq!(mobs[1].name, "Kupiec");
        assert_eq!(mobs[1].mob_type, MobType::Target);
        assert_eq!(mobs[1].actions.len(), 1);
        assert_eq!(
            mobs[1].actions[0],
            ("Okradnij".to_owned(), "thief10steal".to_owned())
        );
    }

    #[test]
    fn parse_mobs_empty() {
        assert!(parse_mobs("").is_empty());
    }

    #[test]
    fn parse_mobs_multiple_actions() {
        let mobs = parse_mobs("NPC,T,Desc,Act1,tgt1,Act2,tgt2");
        assert_eq!(mobs[0].actions.len(), 2);
        assert_eq!(mobs[0].actions[1], ("Act2".to_owned(), "tgt2".to_owned()));
    }

    // --- parse_items --------------------------------------------------------

    #[test]
    fn parse_items_basic() {
        let items = parse_items("Klucz,Q,Widzisz klucz.,Podnieś,take_key");
        assert_eq!(items.len(), 1);
        assert_eq!(items[0].name, "Klucz");
        assert_eq!(items[0].item_type, ItemType::QuestTarget);
        assert_eq!(items[0].actions.len(), 1);
    }

    #[test]
    fn parse_items_equipment() {
        let items = parse_items("Miecz,E,Leży tu miecz.,Weź,take_sword");
        assert_eq!(items[0].item_type, ItemType::Equipment);
    }

    // --- parse_moreinfo -----------------------------------------------------

    #[test]
    fn moreinfo_combat() {
        let info = parse_moreinfo("combat;5;1;winroom;loseroom");
        match info {
            ParsedMoreInfo::Combat {
                monster_level,
                fight_count,
                win_room,
                lose_room,
            } => {
                assert_eq!(monster_level, "5");
                assert_eq!(fight_count, 1);
                assert_eq!(win_room, "winroom");
                assert_eq!(lose_room, "loseroom");
            }
            _ => panic!("expected Combat"),
        }
    }

    #[test]
    fn moreinfo_skill() {
        let info = parse_moreinfo("skill;thievery");
        match info {
            ParsedMoreInfo::SkillGrant { skill_key } => {
                assert_eq!(skill_key, "thievery");
            }
            _ => panic!("expected SkillGrant"),
        }
    }

    #[test]
    fn moreinfo_empty() {
        assert_eq!(parse_moreinfo(""), ParsedMoreInfo::None);
    }

    #[test]
    fn moreinfo_other() {
        let info = parse_moreinfo("unknown;data;here");
        match info {
            ParsedMoreInfo::Other(parts) => {
                assert_eq!(parts, vec!["unknown", "data", "here"]);
            }
            _ => panic!("expected Other"),
        }
    }

    // --- serialization roundtrip --------------------------------------------

    #[test]
    fn exits_roundtrip() {
        let exits = vec![
            ParsedExit {
                label: "Go".to_owned(),
                target: "room1".to_owned(),
                type_filter: None,
            },
            ParsedExit {
                label: "Back".to_owned(),
                target: "room2".to_owned(),
                type_filter: None,
            },
        ];
        let serialized = serialize_exits(&exits);
        let parsed = parse_exits(&serialized);
        assert_eq!(parsed.len(), 2);
        assert_eq!(parsed[0].label, "Go");
        assert_eq!(parsed[0].target, "room1");
        assert_eq!(parsed[1].target, "room2");
    }

    #[test]
    fn mobs_roundtrip() {
        let mobs = vec![ParsedMob {
            name: "Guard".to_owned(),
            mob_type: MobType::Aggressive,
            description: "A guard.".to_owned(),
            actions: vec![("Attack".to_owned(), "fight".to_owned())],
        }];
        let serialized = serialize_mobs(&mobs);
        let parsed = parse_mobs(&serialized);
        assert_eq!(parsed.len(), 1);
        assert_eq!(parsed[0].name, "Guard");
        assert_eq!(parsed[0].mob_type, MobType::Aggressive);
        assert_eq!(parsed[0].actions.len(), 1);
    }

    #[test]
    fn items_roundtrip() {
        let items = vec![ParsedItem {
            name: "Key".to_owned(),
            item_type: ItemType::Target,
            description: "A shiny key.".to_owned(),
            actions: vec![("Take".to_owned(), "pickup".to_owned())],
        }];
        let serialized = serialize_items(&items);
        let parsed = parse_items(&serialized);
        assert_eq!(parsed.len(), 1);
        assert_eq!(parsed[0].item_type, ItemType::Target);
    }

    #[test]
    fn moreinfo_combat_roundtrip() {
        let info = ParsedMoreInfo::Combat {
            monster_level: "5".to_owned(),
            fight_count: 3,
            win_room: "win".to_owned(),
            lose_room: "lose".to_owned(),
        };
        let serialized = serialize_moreinfo(&info);
        assert_eq!(serialized, "combat;5;3;win;lose");
        let parsed = parse_moreinfo(&serialized);
        assert_eq!(parsed, info);
    }

    #[test]
    fn moreinfo_skill_roundtrip() {
        let info = ParsedMoreInfo::SkillGrant {
            skill_key: "mining".to_owned(),
        };
        let serialized = serialize_moreinfo(&info);
        assert_eq!(serialized, "skill;mining");
        let parsed = parse_moreinfo(&serialized);
        assert_eq!(parsed, info);
    }

    // --- filter exits by type -----------------------------------------------

    #[test]
    fn filter_exits_removes_wrong_type() {
        let exits = vec![
            ParsedExit {
                label: "Thief only".to_owned(),
                target: "t1".to_owned(),
                type_filter: Some('T'),
            },
            ParsedExit {
                label: "Everyone".to_owned(),
                target: "e1".to_owned(),
                type_filter: None,
            },
            ParsedExit {
                label: "Story only".to_owned(),
                target: "s1".to_owned(),
                type_filter: Some('E'),
            },
        ];
        let filtered = filter_exits_by_type(&exits, "T");
        assert_eq!(filtered.len(), 2);
        assert_eq!(filtered[0].target, "t1");
        assert_eq!(filtered[1].target, "e1");
    }

    // --- class placeholder expansion ----------------------------------------

    #[test]
    fn expand_prof_placeholder() {
        let result = expand_class_placeholders("Porozmawiaj z %prof%,talk_prof", "Wojownik");
        assert_eq!(result, "Porozmawiaj z Wojownik,talk_prof");
    }

    #[test]
    fn expand_class_removes_other_class_actions() {
        // A mob with class-specific actions: one for fighters, one generic
        let raw = "NPC,T,Desc,Walcz,%fight%attack,Kradnij,%thief%steal";
        let result = expand_class_placeholders(raw, "Złodziej");
        // The %fight% pair should be removed, %thief% expanded
        assert!(result.contains("Złodziej"), "got: {result}");
        assert!(!result.contains("%fight%"), "got: {result}");
    }

    #[test]
    fn expand_class_keeps_matching_class() {
        let raw = "Action,%fight%target";
        let result = expand_class_placeholders(raw, "Wojownik");
        assert_eq!(result, "Action,Wojowniktarget");
        // Hmm, the placeholder includes the class name literally, so:
        // %fight% → "Wojownik" → "Action,Wojowniktarget" — which is correct
        // since PHP does str_replace('%fight%', $player->clas, ...)
    }

    // --- parse_chances ------------------------------------------------------

    #[test]
    fn parse_chances_basic() {
        let chances = parse_chances("50;80;30");
        assert_eq!(chances, vec![50, 80, 30]);
    }

    #[test]
    fn parse_chances_empty() {
        assert!(parse_chances("").is_empty());
    }

    // --- roll_options -------------------------------------------------------

    #[test]
    fn roll_all_pass() {
        let items = vec!["a", "b", "c"];
        let chances = vec![100, 100, 100];
        let result = roll_options(&items, &chances, || 0);
        assert_eq!(result, vec!["a", "b", "c"]);
    }

    #[test]
    fn roll_none_pass() {
        let items = vec!["a", "b"];
        let chances = vec![0, 0];
        let result = roll_options(&items, &chances, || 50);
        assert!(result.is_empty());
    }

    #[test]
    fn roll_selective() {
        let items = vec!["a", "b", "c"];
        let chances = vec![50, 50, 50];
        let mut calls = 0;
        let result = roll_options(&items, &chances, || {
            calls += 1;
            match calls {
                1 => 10, // < 50 → pass
                2 => 60, // >= 50 → fail
                3 => 30, // < 50 → pass
                _ => 99,
            }
        });
        assert_eq!(result, vec!["a", "c"]);
    }

    // --- collect_room_actions -----------------------------------------------

    #[test]
    fn collect_actions_combines_all() {
        let exits = vec![ParsedExit {
            label: "Go".to_owned(),
            target: "room1".to_owned(),
            type_filter: None,
        }];
        let mobs = vec![ParsedMob {
            name: "NPC".to_owned(),
            mob_type: MobType::Target,
            description: String::new(),
            actions: vec![("Talk".to_owned(), "talk_npc".to_owned())],
        }];
        let items = vec![ParsedItem {
            name: "Key".to_owned(),
            item_type: ItemType::Target,
            description: String::new(),
            actions: vec![("Take".to_owned(), "take_key".to_owned())],
        }];
        let actions = collect_room_actions(&exits, &mobs, &items);
        assert_eq!(actions.len(), 3);
        assert_eq!(actions[0], ("room1".to_owned(), "Go".to_owned()));
        assert_eq!(actions[1], ("talk_npc".to_owned(), "Talk".to_owned()));
        assert_eq!(actions[2], ("take_key".to_owned(), "Take".to_owned()));
    }

    // --- room text building -------------------------------------------------

    #[test]
    fn build_text_appends_descriptions() {
        let mobs = vec![ParsedMob {
            name: "Guard".to_owned(),
            mob_type: MobType::Aggressive,
            description: "Stoi tu strażnik.".to_owned(),
            actions: vec![],
        }];
        let items = vec![ParsedItem {
            name: "Key".to_owned(),
            item_type: ItemType::Target,
            description: "Leży klucz.".to_owned(),
            actions: vec![],
        }];
        let text = build_room_text("Jesteś w pokoju.", &mobs, &items);
        assert_eq!(text, "Jesteś w pokoju. Stoi tu strażnik. Leży klucz.");
    }

    // --- terminal room check ------------------------------------------------

    #[test]
    fn terminal_room_detection() {
        assert!(is_terminal_room("ele1finish"));
        assert!(is_terminal_room("thief10finishgood"));
        assert!(is_terminal_room("ele1fail"));
        assert!(is_terminal_room("ele1resign"));
        assert!(!is_terminal_room("ele1room3"));
    }

    // --- valid_action_targets -----------------------------------------------

    #[test]
    fn valid_targets_includes_combat_rooms() {
        let exits = vec![];
        let mobs = vec![];
        let items = vec![];
        let moreinfo = ParsedMoreInfo::Combat {
            monster_level: "5".to_owned(),
            fight_count: 1,
            win_room: "win".to_owned(),
            lose_room: "lose".to_owned(),
        };
        let targets = valid_action_targets(&exits, &mobs, &items, &moreinfo);
        assert!(targets.contains(&"win".to_owned()));
        assert!(targets.contains(&"lose".to_owned()));
    }

    // --- mob_difficulty_context ----------------------------------------------

    #[test]
    fn difficulty_context_basic() {
        let mobs = vec![
            ParsedMob {
                name: "Guard".to_owned(),
                mob_type: MobType::Aggressive,
                description: String::new(),
                actions: vec![("Attack".to_owned(), "fight".to_owned())],
            },
            ParsedMob {
                name: "NPC".to_owned(),
                mob_type: MobType::Target,
                description: String::new(),
                actions: vec![("Talk".to_owned(), "talk".to_owned())],
            },
        ];
        let (count, aggro, target_aggro) = mob_difficulty_context("fight", &mobs);
        assert_eq!(count, 2);
        assert_eq!(aggro, 1);
        assert!(target_aggro);

        let (_, _, target_aggro2) = mob_difficulty_context("talk", &mobs);
        assert!(!target_aggro2);
    }

    // --- generate_room ------------------------------------------------------

    #[test]
    fn generate_room_basic() {
        let room = MissionRoom {
            id: 42,
            name: "ele1room1".to_owned(),
            text: "You enter a room.".to_owned(),
            raw_exits: "Go left,ele1room2;Go right,ele1room3".to_owned(),
            raw_chances: "100;100".to_owned(),
            raw_mobs: String::new(),
            raw_chances2: String::new(),
            raw_items: String::new(),
            raw_chances3: String::new(),
            raw_moreinfo: String::new(),
        };
        let result = generate_room(&room, "E", "Wojownik", || 0);
        assert_eq!(result.room_id, 42);
        assert_eq!(result.exits.len(), 2);
        assert!(result.mobs.is_empty());
        assert!(result.items.is_empty());
        assert_eq!(result.moreinfo, ParsedMoreInfo::None);
    }

    #[test]
    fn generate_room_retries_exits_until_at_least_one() {
        let room = MissionRoom {
            id: 1,
            name: "test1room".to_owned(),
            text: "Room.".to_owned(),
            raw_exits: "A,r1;B,r2".to_owned(),
            raw_chances: "30;30".to_owned(),
            raw_mobs: String::new(),
            raw_chances2: String::new(),
            raw_items: String::new(),
            raw_chances3: String::new(),
            raw_moreinfo: String::new(),
        };
        // First two calls fail (roll=50, 50), thirds succeed (roll=10, 10)
        let mut call = 0;
        let result = generate_room(&room, "E", "Wojownik", || {
            call += 1;
            if call <= 2 { 50 } else { 10 }
        });
        assert!(!result.exits.is_empty());
    }

    // --- snapshot_room_state ------------------------------------------------

    #[test]
    fn snapshot_and_reparse() {
        let room = GeneratedRoom {
            room_id: 5,
            room_name: "test1room".to_owned(),
            text: "Hello".to_owned(),
            exits: vec![ParsedExit {
                label: "Go".to_owned(),
                target: "next".to_owned(),
                type_filter: None,
            }],
            mobs: vec![ParsedMob {
                name: "NPC".to_owned(),
                mob_type: MobType::Target,
                description: "Desc".to_owned(),
                actions: vec![("Act".to_owned(), "tgt".to_owned())],
            }],
            items: vec![],
            moreinfo: ParsedMoreInfo::SkillGrant {
                skill_key: "mining".to_owned(),
            },
        };
        let state = snapshot_room_state(&room);
        assert_eq!(state.location, 5);

        // Re-parse and verify
        let exits = parse_exits(&state.raw_exits);
        assert_eq!(exits.len(), 1);
        assert_eq!(exits[0].target, "next");

        let mobs = parse_mobs(&state.raw_mobs);
        assert_eq!(mobs.len(), 1);
        assert_eq!(mobs[0].name, "NPC");

        let moreinfo = parse_moreinfo(&state.raw_moreinfo);
        assert_eq!(
            moreinfo,
            ParsedMoreInfo::SkillGrant {
                skill_key: "mining".to_owned()
            }
        );
    }
}
