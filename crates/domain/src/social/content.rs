//! Domain logic for content publishing: news, updates, newspaper, polls, proposals.

/// Newspaper article types (single-char codes from PHP legacy).
#[derive(Debug, Clone, Copy, PartialEq, Eq)]
pub enum ArticleType {
    News,
    CityNews,
    Court,
    Royal,
    King,
    Chronicle,
    Sensations,
    Humor,
    Interviews,
    Announcements,
    Poetry,
}

impl ArticleType {
    #[must_use]
    pub fn from_code(c: &str) -> Option<Self> {
        match c {
            "N" => Some(Self::News),
            "M" => Some(Self::CityNews),
            "O" => Some(Self::Court),
            "R" => Some(Self::Royal),
            "K" => Some(Self::King),
            "C" => Some(Self::Chronicle),
            "S" => Some(Self::Sensations),
            "H" => Some(Self::Humor),
            "I" => Some(Self::Interviews),
            "A" => Some(Self::Announcements),
            "P" => Some(Self::Poetry),
            _ => None,
        }
    }

    #[must_use]
    pub fn code(self) -> &'static str {
        match self {
            Self::News => "N",
            Self::CityNews => "M",
            Self::Court => "O",
            Self::Royal => "R",
            Self::King => "K",
            Self::Chronicle => "C",
            Self::Sensations => "S",
            Self::Humor => "H",
            Self::Interviews => "I",
            Self::Announcements => "A",
            Self::Poetry => "P",
        }
    }

    #[must_use]
    pub fn label(self) -> &'static str {
        match self {
            Self::News => "Wieści",
            Self::CityNews => "Wieści z miasta",
            Self::Court => "Z dworu",
            Self::Royal => "Królewskie",
            Self::King => "Królewskie skrypty",
            Self::Chronicle => "Kronika",
            Self::Sensations => "Sensacje",
            Self::Humor => "Humor",
            Self::Interviews => "Wywiady",
            Self::Announcements => "Doniesienia",
            Self::Poetry => "Poezja",
        }
    }

    /// All valid article types in display order.
    #[must_use]
    pub fn all() -> &'static [Self] {
        &[
            Self::News,
            Self::CityNews,
            Self::Court,
            Self::Royal,
            Self::King,
            Self::Chronicle,
            Self::Sensations,
            Self::Humor,
            Self::Interviews,
            Self::Announcements,
            Self::Poetry,
        ]
    }
}

/// Comment target types.
#[derive(Debug, Clone, Copy, PartialEq, Eq)]
pub enum CommentTarget {
    News,
    Update,
    Newspaper,
    Poll,
}

impl CommentTarget {
    #[must_use]
    pub fn as_str(self) -> &'static str {
        match self {
            Self::News => "news",
            Self::Update => "update",
            Self::Newspaper => "newspaper",
            Self::Poll => "poll",
        }
    }
}

/// Proposal types.
#[derive(Debug, Clone, Copy, PartialEq, Eq)]
pub enum ProposalType {
    Description,
    Item,
    Monster,
}

impl ProposalType {
    #[must_use]
    pub fn from_code(c: &str) -> Option<Self> {
        match c {
            "D" => Some(Self::Description),
            "I" => Some(Self::Item),
            "M" => Some(Self::Monster),
            _ => None,
        }
    }

    #[must_use]
    pub fn code(self) -> &'static str {
        match self {
            Self::Description => "D",
            Self::Item => "I",
            Self::Monster => "M",
        }
    }
}

/// Pagination constants for comments.
pub const COMMENTS_PER_PAGE: i64 = 15;

/// Returns `true` if the rank can edit newspaper content.
#[must_use]
pub fn can_edit_newspaper(rank: &str) -> bool {
    matches!(rank, "Admin" | "Redaktor")
}

/// Returns `true` if the rank can manage updates.
#[must_use]
pub fn can_manage_updates(rank: &str) -> bool {
    rank == "Admin"
}

/// Returns `true` if the rank can approve/reject/edit pending news.
#[must_use]
pub fn can_manage_news(rank: &str) -> bool {
    matches!(rank, "Admin" | "Staff")
}

/// Returns `true` if the rank can add news (gossip).
#[must_use]
pub fn can_add_news(rank: &str) -> bool {
    matches!(rank, "Admin" | "Staff" | "Kronikarz")
}

/// Compute vote percentage, rounded to two decimals.
#[must_use]
#[allow(clippy::cast_precision_loss)]
pub fn vote_percentage(votes: i64, total: i64) -> f64 {
    if total == 0 {
        0.0
    } else {
        let pct = (votes as f64 / total as f64) * 100.0;
        (pct * 100.0).round() / 100.0
    }
}

#[cfg(test)]
mod tests {
    use super::*;

    #[test]
    fn article_type_roundtrip() {
        for at in ArticleType::all() {
            assert_eq!(ArticleType::from_code(at.code()), Some(*at));
        }
    }

    #[test]
    fn article_type_invalid() {
        assert_eq!(ArticleType::from_code("X"), None);
    }

    #[test]
    fn proposal_type_roundtrip() {
        assert_eq!(
            ProposalType::from_code("D"),
            Some(ProposalType::Description)
        );
        assert_eq!(ProposalType::from_code("I"), Some(ProposalType::Item));
        assert_eq!(ProposalType::from_code("M"), Some(ProposalType::Monster));
        assert_eq!(ProposalType::from_code("Z"), None);
    }

    #[test]
    fn vote_percentage_zero_total() {
        assert!((vote_percentage(0, 0) - 0.0).abs() < f64::EPSILON);
    }

    #[test]
    fn vote_percentage_normal() {
        assert!((vote_percentage(50, 200) - 25.0).abs() < 0.01);
    }

    #[test]
    fn can_edit_newspaper_roles() {
        assert!(can_edit_newspaper("Admin"));
        assert!(can_edit_newspaper("Redaktor"));
        assert!(!can_edit_newspaper("Staff"));
        assert!(!can_edit_newspaper("Gracz"));
    }

    #[test]
    fn can_add_news_roles() {
        assert!(can_add_news("Admin"));
        assert!(can_add_news("Staff"));
        assert!(can_add_news("Kronikarz"));
        assert!(!can_add_news("Gracz"));
    }
}
