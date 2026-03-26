//! Player skills — maps to the `player_skills` table.
//!
//! The 16 fixed skills cover combat, crafting, and social abilities.

use serde::{Deserialize, Serialize};

/// A single player skill row.
#[derive(Debug, Clone, PartialEq, Eq, Serialize, Deserialize)]
pub struct PlayerSkill {
    /// Internal key: `smith`, `shoot`, `alchemy`, etc.
    pub skill_key: String,
    /// Display label (Polish).
    pub label: String,
    /// Current skill level (1–100).
    pub level: i32,
    /// Accumulated XP towards the next level.
    pub xp: i32,
}

/// The fixed set of skill keys used in the game.
pub const SKILL_KEYS: &[&str] = &[
    "smith",
    "shoot",
    "alchemy",
    "dodge",
    "carpentry",
    "magic",
    "attack",
    "leadership",
    "breeding",
    "mining",
    "lumberjack",
    "herbalism",
    "jewellry",
    "smelting",
    "thievery",
    "perception",
];

/// Default skills for a freshly created player.
pub fn default_skills() -> Vec<PlayerSkill> {
    vec![
        skill("smith", "Kowalstwo"),
        skill("shoot", "Strzelectwo"),
        skill("alchemy", "Alchemia"),
        skill("dodge", "Uniki"),
        skill("carpentry", "Stolarstwo"),
        skill("magic", "Rzucanie Czarów"),
        skill("attack", "Walka Bronią"),
        skill("leadership", "Dowodzenie"),
        skill("breeding", "Hodowla"),
        skill("mining", "Górnictwo"),
        skill("lumberjack", "Drwalnictwo"),
        skill("herbalism", "Zielarstwo"),
        skill("jewellry", "Jubilerstwo"),
        skill("smelting", "Hutnictwo"),
        skill("thievery", "Złodziejstwo"),
        skill("perception", "Spostrzegawczość"),
    ]
}

fn skill(key: &str, label: &str) -> PlayerSkill {
    PlayerSkill {
        skill_key: key.to_owned(),
        label: label.to_owned(),
        level: 1,
        xp: 0,
    }
}

#[cfg(test)]
mod tests {
    use super::*;

    #[test]
    fn default_skills_has_16_entries() {
        assert_eq!(default_skills().len(), 16);
    }

    #[test]
    fn default_skills_start_at_level_one() {
        for s in default_skills() {
            assert_eq!(s.level, 1);
            assert_eq!(s.xp, 0);
        }
    }

    #[test]
    fn skill_keys_match_defaults() {
        let skills = default_skills();
        for (i, key) in SKILL_KEYS.iter().enumerate() {
            assert_eq!(&skills[i].skill_key, key);
        }
    }
}
