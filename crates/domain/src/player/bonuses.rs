//! Player bonuses — maps to the `player_bonuses` table.
//!
//! Bonuses are accumulated by combat, items, quests, deity actions,
//! and AP spending. They are consumed/decremented by gameplay systems.

use serde::{Deserialize, Serialize};

/// A single active bonus on a player.
#[derive(Debug, Clone, PartialEq, Eq, Serialize, Deserialize)]
pub struct PlayerBonus {
    /// Database row ID (0 for new/unsaved).
    pub id: i32,
    /// Bonus name / trigger key (e.g. `"mining"`, `"strength"`, `"assasin"`).
    pub bonus_name: String,
    /// Bonus value (magnitude).
    pub value: i32,
    /// Remaining duration / charges.
    pub duration: i32,
}

/// Parse bonuses from the legacy semicolon-delimited format.
///
/// Format: `name,value,duration;name,value,duration;...`
/// The PHP code actually stores 4 fields per bonus: `name,value,trigger,duration`
/// where index [2] is the trigger key used in `checkbonus()`. In the
/// normalized table we store only name/value/duration; trigger mapping
/// is handled by the domain logic.
pub fn parse_legacy_bonuses(raw: &str) -> Vec<PlayerBonus> {
    if raw.is_empty() {
        return Vec::new();
    }
    let mut bonuses = Vec::new();
    for entry in raw.split(';') {
        if entry.is_empty() {
            continue;
        }
        let parts: Vec<&str> = entry.split(',').collect();
        if parts.len() < 3 {
            tracing::debug!(raw = entry, "malformed legacy bonus entry");
            continue;
        }
        bonuses.push(PlayerBonus {
            id: 0,
            bonus_name: parts[0].to_owned(),
            value: parts[1].parse().unwrap_or(0),
            duration: parts[2].parse().unwrap_or(0),
        });
    }
    bonuses
}

/// Serialize bonuses back to the legacy format.
pub fn to_legacy_bonuses(bonuses: &[PlayerBonus]) -> String {
    let mut out = String::new();
    for b in bonuses {
        if !out.is_empty() {
            out.push(';');
        }
        out.push_str(&b.bonus_name);
        out.push(',');
        out.push_str(&b.value.to_string());
        out.push(',');
        out.push_str(&b.duration.to_string());
    }
    out
}

#[cfg(test)]
mod tests {
    use super::*;

    #[test]
    fn empty_string_gives_no_bonuses() {
        let bonuses = parse_legacy_bonuses("");
        assert!(bonuses.is_empty());
    }

    #[test]
    fn parse_single_bonus() {
        let raw = "mining,5,10";
        let bonuses = parse_legacy_bonuses(raw);
        assert_eq!(bonuses.len(), 1);
        assert_eq!(bonuses[0].bonus_name, "mining");
        assert_eq!(bonuses[0].value, 5);
        assert_eq!(bonuses[0].duration, 10);
    }

    #[test]
    fn parse_multiple_bonuses() {
        let raw = "strength,3,5;speed,2,8;";
        let bonuses = parse_legacy_bonuses(raw);
        assert_eq!(bonuses.len(), 2);
    }

    #[test]
    fn roundtrip_legacy() {
        let original = vec![
            PlayerBonus {
                id: 0,
                bonus_name: "mining".to_owned(),
                value: 5,
                duration: 10,
            },
            PlayerBonus {
                id: 0,
                bonus_name: "speed".to_owned(),
                value: 3,
                duration: 7,
            },
        ];
        let serialized = to_legacy_bonuses(&original);
        let parsed = parse_legacy_bonuses(&serialized);
        assert_eq!(parsed, original);
    }
}
