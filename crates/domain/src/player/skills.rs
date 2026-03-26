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

/// Parse skills from the legacy semicolon-delimited format.
///
/// Format: `key:Label,level,xp;...`
pub fn parse_legacy_skills(raw: &str) -> Vec<PlayerSkill> {
    let mut skills = Vec::new();
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
        if parts.len() < 3 {
            tracing::debug!(key, raw = values_str, "malformed legacy skill entry");
            continue;
        }
        skills.push(PlayerSkill {
            skill_key: key.to_owned(),
            label: parts[0].to_owned(),
            level: parts[1].parse().unwrap_or(1),
            xp: parts[2].parse().unwrap_or(0),
        });
    }
    skills
}

/// Serialize skills back to the legacy format.
pub fn to_legacy_skills(skills: &[PlayerSkill]) -> String {
    let mut out = String::new();
    for s in skills {
        out.push_str(&s.skill_key);
        out.push(':');
        out.push_str(&s.label);
        out.push(',');
        out.push_str(&s.level.to_string());
        out.push(',');
        out.push_str(&s.xp.to_string());
        out.push(';');
    }
    out
}

#[cfg(test)]
mod tests {
    use super::*;

    #[test]
    fn default_skills_has_16_entries() {
        assert_eq!(default_skills().len(), 16);
    }

    #[test]
    fn parse_legacy_skills_default() {
        let raw = "smith:Kowalstwo,1,0;shoot:Strzelectwo,1,0;alchemy:Alchemia,1,0;\
                   dodge:Uniki,1,0;carpentry:Stolarstwo,1,0;magic:Rzucanie Czarów,1,0;\
                   attack:Walka Bronią,1,0;leadership:Dowodzenie,1,0;breeding:Hodowla,1,0;\
                   mining:Górnictwo,1,0;lumberjack:Drwalnictwo,1,0;herbalism:Zielarstwo,1,0;\
                   jewellry:Jubilerstwo,1,0;smelting:Hutnictwo,1,0;thievery:Złodziejstwo,1,0;\
                   perception:Spostrzegawczość,1,0;";
        let skills = parse_legacy_skills(raw);
        assert_eq!(skills.len(), 16);
        assert_eq!(skills[0].skill_key, "smith");
        assert_eq!(skills[0].level, 1);
    }

    #[test]
    fn roundtrip_legacy() {
        let original = default_skills();
        let serialized = to_legacy_skills(&original);
        let parsed = parse_legacy_skills(&serialized);
        assert_eq!(parsed, original);
    }

    #[test]
    fn parse_with_leveled_skill() {
        let raw = "mining:Górnictwo,42,8500;";
        let skills = parse_legacy_skills(raw);
        assert_eq!(skills.len(), 1);
        assert_eq!(skills[0].level, 42);
        assert_eq!(skills[0].xp, 8500);
    }

    #[test]
    fn empty_string_gives_no_skills() {
        let skills = parse_legacy_skills("");
        assert!(skills.is_empty());
    }
}
