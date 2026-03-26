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

#[cfg(test)]
mod tests {
    use super::*;

    #[test]
    fn default_stats_has_six_entries() {
        let stats = default_stats();
        assert_eq!(stats.len(), 6);
        assert_eq!(stats[0].stat_key, "strength");
        assert_eq!(stats[5].stat_key, "wisdom");
    }

    #[test]
    fn default_stats_all_zeroed() {
        for s in default_stats() {
            assert_eq!(s.base, 0);
            assert_eq!(s.trained, 0);
            assert_eq!(s.modified, 0);
        }
    }

    #[test]
    fn stat_keys_match_defaults() {
        let stats = default_stats();
        for (i, key) in STAT_KEYS.iter().enumerate() {
            assert_eq!(&stats[i].stat_key, key);
        }
    }
}
