//! Core player domain types.
//!
//! Separates persisted fields, structured sub-models (settings, stats,
//! skills, bonuses), and derived calculations into distinct types.
//! The PHP `player_class.php` mixes all of these into one flat object;
//! the Rust model keeps them apart for clarity and testability.

pub mod bonuses;
pub mod settings;
pub mod skills;
pub mod stats;

use serde::{Deserialize, Serialize};

// ---------------------------------------------------------------------------
// Enums for fixed player attributes
// ---------------------------------------------------------------------------

/// Player race.
#[derive(Debug, Clone, PartialEq, Eq, Serialize, Deserialize)]
pub enum Race {
    Human,
    Elf,
    Dwarf,
    Lizardman,
    Hobbit,
    Gnome,
}

impl Race {
    /// Parse from the legacy Polish string stored in the database.
    pub fn from_legacy(s: &str) -> Option<Self> {
        match s {
            "Człowiek" => Some(Self::Human),
            "Elf" => Some(Self::Elf),
            "Krasnolud" => Some(Self::Dwarf),
            "Jaszczuroczłek" => Some(Self::Lizardman),
            "Hobbit" => Some(Self::Hobbit),
            "Gnom" => Some(Self::Gnome),
            _ => None,
        }
    }

    /// Return the legacy Polish string for database storage.
    pub fn as_legacy(&self) -> &'static str {
        match self {
            Self::Human => "Człowiek",
            Self::Elf => "Elf",
            Self::Dwarf => "Krasnolud",
            Self::Lizardman => "Jaszczuroczłek",
            Self::Hobbit => "Hobbit",
            Self::Gnome => "Gnom",
        }
    }
}

/// Player class.
#[derive(Debug, Clone, PartialEq, Eq, Serialize, Deserialize)]
pub enum Class {
    Barbarian,
    Warrior,
    Thief,
    Mage,
    Craftsman,
}

impl Class {
    /// Parse from the legacy Polish string stored in the database.
    pub fn from_legacy(s: &str) -> Option<Self> {
        match s {
            "Barbarzyńca" => Some(Self::Barbarian),
            "Wojownik" => Some(Self::Warrior),
            "Złodziej" => Some(Self::Thief),
            "Mag" => Some(Self::Mage),
            "Rzemieślnik" => Some(Self::Craftsman),
            _ => None,
        }
    }

    /// Return the legacy Polish string for database storage.
    pub fn as_legacy(&self) -> &'static str {
        match self {
            Self::Barbarian => "Barbarzyńca",
            Self::Warrior => "Wojownik",
            Self::Thief => "Złodziej",
            Self::Mage => "Mag",
            Self::Craftsman => "Rzemieślnik",
        }
    }
}

/// Player rank (permission level).
#[derive(Debug, Clone, PartialEq, Eq, Serialize, Deserialize)]
pub enum Rank {
    Member,
    Moderator,
    Admin,
    /// Catch-all for unrecognised legacy values.
    Other(String),
}

impl Rank {
    pub fn from_legacy(s: &str) -> Self {
        match s {
            "Member" => Self::Member,
            "Moderator" => Self::Moderator,
            "Admin" => Self::Admin,
            other => Self::Other(other.to_owned()),
        }
    }

    pub fn as_legacy(&self) -> &str {
        match self {
            Self::Member => "Member",
            Self::Moderator => "Moderator",
            Self::Admin => "Admin",
            Self::Other(s) => s,
        }
    }
}

// ---------------------------------------------------------------------------
// Core player aggregate
// ---------------------------------------------------------------------------

/// Persisted player fields from the `players` table.
///
/// This struct maps 1:1 to the database row. It does NOT include derived
/// or computed values — those are produced by functions in submodules.
///
/// The related sub-models ([`settings::PlayerSettings`], [`stats::PlayerStat`],
/// [`skills::PlayerSkill`], [`bonuses::PlayerBonus`]) live in their own
/// tables and are loaded separately.
#[derive(Debug, Clone)]
#[allow(clippy::struct_excessive_bools)]
pub struct Player {
    pub id: i32,
    pub username: String,
    pub email: String,
    pub rank: Rank,
    pub credits: i32,
    pub energy: f64,
    pub max_energy: f64,
    pub ap: i32,
    pub wins: i32,
    pub losses: i32,
    pub last_killed: String,
    pub last_killed_by: String,
    pub platinum: i32,
    pub age: i32,
    pub logins: i32,
    pub hp: i32,
    pub max_hp: i32,
    pub bank: i32,
    pub mana: i32,
    pub last_page_visit: i64,
    pub current_page: String,
    pub ip: String,
    pub tribe_id: i32,
    pub profile: String,
    pub referrals: i32,
    pub core_pass: bool,
    pub fight: i32,
    pub trains: i32,
    pub race: String,
    pub class: String,
    pub pw: i32,
    pub immune: bool,
    pub location: String,
    pub messenger: String,
    pub avatar: String,
    pub tribe_rank: String,
    pub deity: Option<String>,
    pub maps: i16,
    pub resting: bool,
    pub crime: i32,
    pub gender: Option<String>,
    pub bridge: bool,
    pub temp: i32,
    pub forum_time: i64,
    pub tforum_time: i64,
    pub bless: String,
    pub bless_value: i32,
    pub antidote: Option<String>,
    pub freeze: i16,
    pub house_rest: bool,
    pub poll: bool,
    pub astral_crime: bool,
    pub change_deity: i32,
    pub vallars: i32,
    pub newbie: i16,
    pub roleplay: String,
    pub ooc: String,
    pub short_rpg: String,
    pub craft_mission: i16,
    pub mpoints: i32,
    pub room: i32,
    pub chapter: i16,
    pub ring_invite: i32,
    pub tribe_invite: i32,
    pub team_id: i32,
    pub reputation: i32,
}

impl Player {
    /// Parse the race field into a typed [`Race`], if known.
    pub fn parsed_race(&self) -> Option<Race> {
        Race::from_legacy(&self.race)
    }

    /// Parse the class field into a typed [`Class`], if known.
    pub fn parsed_class(&self) -> Option<Class> {
        Class::from_legacy(&self.class)
    }
}

#[cfg(test)]
mod tests {
    use super::*;

    #[test]
    fn race_roundtrip() {
        for race in [
            Race::Human,
            Race::Elf,
            Race::Dwarf,
            Race::Lizardman,
            Race::Hobbit,
            Race::Gnome,
        ] {
            let legacy = race.as_legacy();
            let parsed = Race::from_legacy(legacy).expect("should parse back");
            assert_eq!(parsed, race);
        }
    }

    #[test]
    fn class_roundtrip() {
        for class in [
            Class::Barbarian,
            Class::Warrior,
            Class::Thief,
            Class::Mage,
            Class::Craftsman,
        ] {
            let legacy = class.as_legacy();
            let parsed = Class::from_legacy(legacy).expect("should parse back");
            assert_eq!(parsed, class);
        }
    }

    #[test]
    fn rank_roundtrip() {
        assert_eq!(Rank::from_legacy("Member"), Rank::Member);
        assert_eq!(Rank::from_legacy("Admin").as_legacy(), "Admin");
        assert_eq!(
            Rank::from_legacy("SeniorMod"),
            Rank::Other("SeniorMod".to_owned())
        );
    }

    #[test]
    fn unknown_race_returns_none() {
        assert!(Race::from_legacy("Troll").is_none());
    }

    #[test]
    fn unknown_class_returns_none() {
        assert!(Class::from_legacy("Paladin").is_none());
    }
}
