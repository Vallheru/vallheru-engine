//! Domain logic for notes, library, roleplay profiles, and chronicle.

/// Notes per page for pagination.
pub const NOTES_PER_PAGE: i64 = 25;

/// Library text types.
#[derive(Debug, Clone, Copy, PartialEq, Eq)]
pub enum LibraryTextType {
    Tale,
    Poetry,
}

impl LibraryTextType {
    #[must_use]
    pub fn parse(s: &str) -> Self {
        match s {
            "poetry" => Self::Poetry,
            _ => Self::Tale,
        }
    }

    #[must_use]
    pub fn as_str(self) -> &'static str {
        match self {
            Self::Tale => "tale",
            Self::Poetry => "poetry",
        }
    }

    #[must_use]
    pub fn label(self) -> &'static str {
        match self {
            Self::Tale => "Opowiadania",
            Self::Poetry => "Poezja",
        }
    }
}

/// Chronicle mission types.
#[derive(Debug, Clone, Copy, PartialEq, Eq)]
pub enum MissionType {
    /// Main quest storyline.
    MainQuest,
    /// Old/historical story.
    OldStory,
    /// Side event / other.
    Other,
}

impl MissionType {
    #[must_use]
    pub fn from_code(c: &str) -> Self {
        match c {
            "Q" => Self::MainQuest,
            "O" => Self::OldStory,
            _ => Self::Other,
        }
    }
}

/// Whether a rank can manage the library (approve/reject texts).
#[must_use]
pub fn can_manage_library(rank: &str) -> bool {
    matches!(rank, "Admin" | "Bibliotekarz")
}

/// Library sort options.
#[derive(Debug, Clone, Copy, PartialEq, Eq)]
pub enum LibrarySort {
    Author,
    Title,
    Date,
}

impl LibrarySort {
    #[must_use]
    pub fn parse(s: &str) -> Self {
        match s {
            "title" => Self::Title,
            "id" | "date" => Self::Date,
            _ => Self::Author,
        }
    }

    #[must_use]
    pub fn as_str(self) -> &'static str {
        match self {
            Self::Author => "author",
            Self::Title => "title",
            Self::Date => "date",
        }
    }
}

#[cfg(test)]
mod tests {
    use super::*;

    #[test]
    fn library_text_type_roundtrip() {
        assert_eq!(LibraryTextType::parse("tale"), LibraryTextType::Tale);
        assert_eq!(LibraryTextType::parse("poetry"), LibraryTextType::Poetry);
        assert_eq!(LibraryTextType::parse("invalid"), LibraryTextType::Tale);
        assert_eq!(LibraryTextType::Tale.as_str(), "tale");
        assert_eq!(LibraryTextType::Poetry.as_str(), "poetry");
    }

    #[test]
    fn mission_type_from_code() {
        assert_eq!(MissionType::from_code("Q"), MissionType::MainQuest);
        assert_eq!(MissionType::from_code("O"), MissionType::OldStory);
        assert_eq!(MissionType::from_code("E"), MissionType::Other);
        assert_eq!(MissionType::from_code("X"), MissionType::Other);
    }

    #[test]
    fn can_manage_library_roles() {
        assert!(can_manage_library("Admin"));
        assert!(can_manage_library("Bibliotekarz"));
        assert!(!can_manage_library("Gracz"));
        assert!(!can_manage_library("Staff"));
    }

    #[test]
    fn library_sort_from_str() {
        assert_eq!(LibrarySort::parse("title"), LibrarySort::Title);
        assert_eq!(LibrarySort::parse("id"), LibrarySort::Date);
        assert_eq!(LibrarySort::parse("date"), LibrarySort::Date);
        assert_eq!(LibrarySort::parse("author"), LibrarySort::Author);
        assert_eq!(LibrarySort::parse("junk"), LibrarySort::Author);
    }
}
