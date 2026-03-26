//! Player stats — maps to the `player_stats` table.
//!
//! The six fixed stats are: strength, agility, condition, speed,
//! intelligence (`inteli`), and wisdom.

use serde::{Deserialize, Serialize};

/// A single player stat row.
#[derive(Debug, Clone, PartialEq, Eq, Serialize, Deserialize)]
pub struct PlayerStat {
    /// Internal key: `strength`, `agility`, `condition`, `speed`, `inteli`, `wisdom`.
    pub stat_key: String,
    /// Display label (Polish).
    pub label: String,
    /// Base (unmodified) value.
    pub base: i32,
    /// Trained value (including level-ups from XP).
    pub trained: i32,
    /// Modified value (after equipment, blessings, bonuses).
    pub modified: i32,
}

/// The fixed set of stat keys used in the game.
pub const STAT_KEYS: &[&str] = &[
    "strength",
    "agility",
    "condition",
    "speed",
    "inteli",
    "wisdom",
];

/// Default stats for a freshly created player.
pub fn default_stats() -> Vec<PlayerStat> {
    vec![
        PlayerStat {
            stat_key: "strength".to_owned(),
            label: "Siła".to_owned(),
            base: 0,
            trained: 0,
            modified: 0,
        },
        PlayerStat {
            stat_key: "agility".to_owned(),
            label: "Zręczność".to_owned(),
            base: 0,
            trained: 0,
            modified: 0,
        },
        PlayerStat {
            stat_key: "condition".to_owned(),
            label: "Kondycja".to_owned(),
            base: 0,
            trained: 0,
            modified: 0,
        },
        PlayerStat {
            stat_key: "speed".to_owned(),
            label: "Szybkość".to_owned(),
            base: 0,
            trained: 0,
            modified: 0,
        },
        PlayerStat {
            stat_key: "inteli".to_owned(),
            label: "Inteligencja".to_owned(),
            base: 0,
            trained: 0,
            modified: 0,
        },
        PlayerStat {
            stat_key: "wisdom".to_owned(),
            label: "Siła Woli".to_owned(),
            base: 0,
            trained: 0,
            modified: 0,
        },
    ]
}

/// Parse stats from the legacy semicolon-delimited format.
///
/// Format: `key:Label,base,trained,modified;...`
pub fn parse_legacy_stats(raw: &str) -> Vec<PlayerStat> {
    let mut stats = Vec::new();
    for field in raw.split(';') {
        let mut kv = field.splitn(2, ':');
        let key = match kv.next() {
            Some(k) if !k.is_empty() => k,
            _ => continue,
        };
        let Some(values_str) = kv.next() else {
            continue;
        };
        let parts: Vec<&str> = values_str.split(',').collect();
        if parts.len() < 4 {
            tracing::debug!(key, raw = values_str, "malformed legacy stat entry");
            continue;
        }
        stats.push(PlayerStat {
            stat_key: key.to_owned(),
            label: parts[0].to_owned(),
            base: parts[1].parse().unwrap_or(0),
            trained: parts[2].parse().unwrap_or(0),
            modified: parts[3].parse().unwrap_or(0),
        });
    }
    stats
}

/// Serialize stats back to the legacy format.
pub fn to_legacy_stats(stats: &[PlayerStat]) -> String {
    let mut out = String::new();
    for s in stats {
        out.push_str(&s.stat_key);
        out.push(':');
        out.push_str(&s.label);
        out.push(',');
        out.push_str(&s.base.to_string());
        out.push(',');
        out.push_str(&s.trained.to_string());
        out.push(',');
        out.push_str(&s.modified.to_string());
        out.push(';');
    }
    out
}

#[cfg(test)]
mod tests {
    use super::*;

    #[test]
    fn parse_legacy_stats_default() {
        let raw = "strength:Siła,0,0,0;agility:Zręczność,0,0,0;condition:Kondycja,0,0,0;\
                   speed:Szybkość,0,0,0;inteli:Inteligencja,0,0,0;wisdom:Siła Woli,0,0,0;";
        let stats = parse_legacy_stats(raw);
        assert_eq!(stats.len(), 6);
        assert_eq!(stats[0].stat_key, "strength");
        assert_eq!(stats[0].label, "Siła");
    }

    #[test]
    fn roundtrip_legacy() {
        let original = default_stats();
        let serialized = to_legacy_stats(&original);
        let parsed = parse_legacy_stats(&serialized);
        assert_eq!(parsed, original);
    }

    #[test]
    fn parse_with_nonzero_values() {
        let raw = "strength:Siła,5,10,12;";
        let stats = parse_legacy_stats(raw);
        assert_eq!(stats.len(), 1);
        assert_eq!(stats[0].base, 5);
        assert_eq!(stats[0].trained, 10);
        assert_eq!(stats[0].modified, 12);
    }

    #[test]
    fn empty_string_gives_no_stats() {
        let stats = parse_legacy_stats("");
        assert!(stats.is_empty());
    }
}
