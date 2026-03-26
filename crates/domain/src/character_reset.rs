//! Character reset domain logic.
//!
//! Players can request a character reset (full or partial) which is
//! confirmed via a code sent by email. This module handles validation
//! and code generation.

use rand::Rng;

/// Reset type: full (A) deletes equipment/economy, partial (P) keeps items.
#[derive(Debug, Clone, Copy, PartialEq, Eq)]
pub enum ResetType {
    Full,
    Partial,
}

impl ResetType {
    pub fn from_code(s: &str) -> Option<Self> {
        match s {
            "A" => Some(Self::Full),
            "P" => Some(Self::Partial),
            _ => None,
        }
    }

    pub fn as_code(&self) -> &'static str {
        match self {
            Self::Full => "A",
            Self::Partial => "P",
        }
    }
}

/// Generate a random confirmation code for a character reset request.
pub fn generate_reset_code() -> i32 {
    let mut rng = rand::thread_rng();
    rng.gen_range(1..=1_000_000)
}

/// Default stats string for a freshly reset character (legacy format).
pub const RESET_STATS_RAW: &str = "strength:Siła,0,0,0;agility:Zręczność,0,0,0;\
    condition:Kondycja,0,0,0;speed:Szybkość,0,0,0;inteli:Inteligencja,0,0,0;\
    wisdom:Siła Woli,0,0,0;";

/// Default skills string for a freshly reset character (legacy format).
pub const RESET_SKILLS_RAW: &str = "smith:Kowalstwo,1,0;shoot:Strzelectwo,1,0;\
    alchemy:Alchemia,1,0;dodge:Uniki,1,0;carpentry:Stolarstwo,1,0;\
    magic:Rzucanie Czarów,1,0;attack:Walka Bronią,1,0;\
    leadership:Dowodzenie,1,0;breeding:Hodowla,1,0;mining:Górnictwo,1,0;\
    lumberjack:Drwalnictwo,1,0;herbalism:Zielarstwo,1,0;\
    jewellry:Jubilerstwo,1,0;smelting:Hutnictwo,1,0;\
    thievery:Złodziejstwo,1,0;perception:Spostrzegawczość,1,0;";

#[cfg(test)]
mod tests {
    use super::*;

    #[test]
    fn reset_type_roundtrip() {
        assert_eq!(ResetType::from_code("A"), Some(ResetType::Full));
        assert_eq!(ResetType::from_code("P"), Some(ResetType::Partial));
        assert_eq!(ResetType::from_code("X"), None);
        assert_eq!(ResetType::Full.as_code(), "A");
        assert_eq!(ResetType::Partial.as_code(), "P");
    }

    #[test]
    fn generate_code_in_range() {
        for _ in 0..100 {
            let code = generate_reset_code();
            assert!((1..=1_000_000).contains(&code));
        }
    }
}
