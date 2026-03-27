//! Tavern room domain logic — constants, validation, permissions.

/// Default number of room messages displayed per fetch.
pub const DEFAULT_MESSAGE_LENGTH: i32 = 25;

/// Maximum number of messages per fetch.
pub const MAX_MESSAGE_LENGTH: i32 = 150;

/// Step size for more/less paging.
pub const MESSAGE_LENGTH_STEP: i32 = 25;

/// Maximum room rent in days (absolute cap).
pub const MAX_RENT_DAYS: i16 = 100;

/// Gold cost per day of room rent.
pub const RENT_COST_PER_DAY: i32 = 100;

/// Allowed rent extension options (days).
pub const RENT_OPTIONS: &[(i16, &str)] = &[
    (1, "1 dzień za 100"),
    (3, "3 dni za 300"),
    (7, "7 dni za 700"),
    (14, "14 dni za 1400"),
    (21, "21 dni za 2100"),
];

/// Available nick colours that room owners can assign.
pub const NICK_COLORS: &[(&str, &str)] = &[
    ("aqua", "cyjan"),
    ("blue", "niebieski"),
    ("fuchsia", "fuksja"),
    ("green", "zielony"),
    ("grey", "szary"),
    ("lime", "limonka"),
    ("maroon", "wiśniowy"),
    ("navy", "granatowy"),
    ("olive", "oliwkowy"),
    ("purple", "fioletowy"),
    ("red", "czerwony"),
    ("silver", "srebrny"),
    ("teal", "morski"),
    ("yellow", "żółty"),
];

/// Check whether a player is the room owner.
pub fn is_owner(room_owner_id: i64, player_id: i64) -> bool {
    room_owner_id == player_id
}

/// Check whether a player is an owner or co-owner.
pub fn is_admin(room_owner_id: i64, co_owners: &[i64], player_id: i64) -> bool {
    room_owner_id == player_id || co_owners.contains(&player_id)
}

/// Check whether a requested rent extension is valid.
pub fn validate_rent(days: i16, current_days: i16) -> Result<i32, &'static str> {
    if !RENT_OPTIONS.iter().any(|(d, _)| *d == days) {
        return Err("Nieprawidłowa opcja wynajmu.");
    }
    if days + current_days > MAX_RENT_DAYS {
        return Err("Nie możesz przedłużyć aż o tyle dni wynajęcia pokoju.");
    }
    let cost = i32::from(days) * RENT_COST_PER_DAY;
    Ok(cost)
}

/// Validate a colour name against the allowed set.
pub fn validate_color(color: &str) -> bool {
    NICK_COLORS.iter().any(|(c, _)| *c == color)
}

/// Sanitise a room name (strip tags and trim, like PHP version).
pub fn sanitise_name(raw: &str) -> String {
    let stripped = raw
        .replace(['\'', '<', '>'], "")
        .replace("&nbsp;", "")
        .trim()
        .to_owned();
    if stripped.is_empty() {
        "Pokój".to_owned()
    } else {
        stripped
    }
}

/// Sanitise an NPC name.
pub fn sanitise_npc_name(raw: &str) -> String {
    raw.replace(['\'', '<', '>'], "")
        .replace("&nbsp;", "")
        .trim()
        .to_owned()
}

/// Build the author HTML for a room message, with optional colour.
pub fn room_author_html(player_id: i64, player_name: &str, color: Option<&str>) -> String {
    match color {
        Some(c) => format!("<a href=\"/view/{player_id}\" style=\"color:{c};\">{player_name}</a>"),
        None => format!("<a href=\"/view/{player_id}\">{player_name}</a>"),
    }
}

/// Options for who the message appears to come from.
#[derive(Debug, Clone, Copy, PartialEq, Eq)]
pub enum RoomPersona {
    /// The player themselves (index 0).
    Player,
    /// Description / narration (index 1) — no author shown.
    Description,
    /// An NPC persona by index into the room's NPC list.
    Npc(usize),
}

impl RoomPersona {
    /// Parse from form "person" field value.
    pub fn from_index(idx: i32, npc_count: usize) -> Option<Self> {
        match idx {
            0 => Some(Self::Player),
            1 => Some(Self::Description),
            n if n >= 2 => {
                let npc_idx = usize::try_from(n - 2).ok()?;
                if npc_idx < npc_count {
                    Some(Self::Npc(npc_idx))
                } else {
                    None
                }
            }
            _ => None,
        }
    }
}

#[cfg(test)]
mod tests {
    use super::*;

    #[test]
    fn rent_validation_basic() {
        assert!(validate_rent(7, 50).is_ok());
        assert_eq!(validate_rent(7, 50).unwrap(), 700);
    }

    #[test]
    fn rent_validation_exceeds_cap() {
        assert!(validate_rent(21, 90).is_err());
    }

    #[test]
    fn rent_validation_invalid_option() {
        assert!(validate_rent(5, 10).is_err());
    }

    #[test]
    fn color_validation() {
        assert!(validate_color("aqua"));
        assert!(validate_color("red"));
        assert!(!validate_color("pink"));
        assert!(!validate_color(""));
    }

    #[test]
    fn sanitise_name_strips_tags() {
        assert_eq!(sanitise_name("<b>Test</b>"), "bTest/b");
        assert_eq!(sanitise_name("  Hello World  "), "Hello World");
        assert_eq!(sanitise_name(""), "Pokój");
    }

    #[test]
    fn persona_parsing() {
        assert_eq!(RoomPersona::from_index(0, 2), Some(RoomPersona::Player));
        assert_eq!(
            RoomPersona::from_index(1, 2),
            Some(RoomPersona::Description)
        );
        assert_eq!(RoomPersona::from_index(2, 2), Some(RoomPersona::Npc(0)));
        assert_eq!(RoomPersona::from_index(3, 2), Some(RoomPersona::Npc(1)));
        assert_eq!(RoomPersona::from_index(4, 2), None);
        assert_eq!(RoomPersona::from_index(-1, 2), None);
    }

    #[test]
    fn is_admin_checks() {
        assert!(is_admin(10, &[20, 30], 10)); // owner
        assert!(is_admin(10, &[20, 30], 20)); // co-owner
        assert!(!is_admin(10, &[20, 30], 40)); // neither
    }

    #[test]
    fn room_author_html_with_color() {
        let html = room_author_html(5, "Alice", Some("red"));
        assert!(html.contains("color:red"));
        assert!(html.contains("Alice"));
    }

    #[test]
    fn room_author_html_without_color() {
        let html = room_author_html(5, "Alice", None);
        assert!(!html.contains("color:"));
        assert!(html.contains("Alice"));
    }
}
