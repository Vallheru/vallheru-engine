//! Forum domain logic: validation, pagination, permissions.

/// Maximum length for a topic title.
pub const MAX_TITLE_LENGTH: usize = 100;

/// Topics per page in a category listing.
pub const TOPICS_PER_PAGE: i64 = 25;

/// Replies per page in a topic view.
pub const REPLIES_PER_PAGE: i64 = 25;

/// Minimum seconds between posts (anti-spam).
pub const POST_COOLDOWN_SECS: i64 = 10;

/// Sort orders available for topic lists.
#[derive(Debug, Clone, Copy, PartialEq, Eq, Default)]
pub enum TopicSort {
    #[default]
    NewestReply,
    OldestReply,
    NewestTopic,
    OldestTopic,
    MostReplies,
    LeastReplies,
}

impl TopicSort {
    /// Parse from integer (matches PHP `$_POST['sort']`).
    pub fn from_i32(v: i32) -> Self {
        match v {
            1 => Self::OldestReply,
            2 => Self::NewestTopic,
            3 => Self::OldestTopic,
            4 => Self::MostReplies,
            5 => Self::LeastReplies,
            _ => Self::NewestReply,
        }
    }

    /// SQL `ORDER BY` clause fragment.
    pub fn order_clause(self) -> &'static str {
        match self {
            Self::NewestReply => "last_post_at DESC",
            Self::OldestReply => "last_post_at ASC",
            Self::NewestTopic => "id DESC",
            Self::OldestTopic => "id ASC",
            Self::MostReplies => "reply_count DESC",
            Self::LeastReplies => "reply_count ASC",
        }
    }

    pub fn to_i32(self) -> i32 {
        match self {
            Self::NewestReply => 0,
            Self::OldestReply => 1,
            Self::NewestTopic => 2,
            Self::OldestTopic => 3,
            Self::MostReplies => 4,
            Self::LeastReplies => 5,
        }
    }
}

/// Check if a player rank has access to a permission string.
///
/// Permission strings are semicolon or comma-separated lists of rank names,
/// or "All" for everyone.
pub fn has_permission(perm_string: &str, rank: &str) -> bool {
    let trimmed = perm_string.trim();
    if trimmed.is_empty() || trimmed == "All" || trimmed == "All;" {
        return true;
    }
    if rank == "Admin" {
        return true;
    }
    trimmed.split([';', ',']).any(|s| s.trim() == rank)
}

/// Check if a player is staff (Admin or Staff rank).
pub fn is_staff(rank: &str) -> bool {
    rank == "Admin" || rank == "Staff"
}

/// Truncate a title to the max length, adding ellipsis if needed.
pub fn truncate_title(title: &str) -> String {
    let trimmed = title.trim();
    if trimmed.len() <= MAX_TITLE_LENGTH {
        trimmed.to_owned()
    } else {
        let truncated: String = trimmed.chars().take(MAX_TITLE_LENGTH - 3).collect();
        format!("{truncated}...")
    }
}

/// Calculate total pages.
pub fn total_pages(count: i64, page_size: i64) -> i64 {
    if count <= 0 || page_size <= 0 {
        return 1;
    }
    (count + page_size - 1) / page_size
}

/// Clamp a page number to valid bounds.
pub fn clamp_page(page: i64, total: i64) -> i64 {
    page.clamp(1, total.max(1))
}

#[cfg(test)]
mod tests {
    use super::*;

    #[test]
    fn permission_all() {
        assert!(has_permission("All", "Player"));
        assert!(has_permission("All;", "Anyone"));
    }

    #[test]
    fn permission_admin_always() {
        assert!(has_permission("Staff;Moderator", "Admin"));
    }

    #[test]
    fn permission_specific() {
        assert!(has_permission("Staff;Player", "Staff"));
        assert!(!has_permission("Staff;Moderator", "Player"));
    }

    #[test]
    fn permission_empty() {
        assert!(has_permission("", "Anyone"));
    }

    #[test]
    fn truncate_short() {
        let t = truncate_title("Hello");
        assert_eq!(t, "Hello");
    }

    #[test]
    fn truncate_long() {
        let long = "a".repeat(150);
        let t = truncate_title(&long);
        assert!(t.len() <= MAX_TITLE_LENGTH);
        assert!(t.ends_with("..."));
    }

    #[test]
    fn sort_roundtrip() {
        for i in 0..6 {
            let s = TopicSort::from_i32(i);
            assert_eq!(s.to_i32(), i);
        }
    }

    #[test]
    fn sort_default() {
        assert_eq!(TopicSort::from_i32(99), TopicSort::NewestReply);
    }

    #[test]
    fn total_pages_basic() {
        assert_eq!(total_pages(0, 25), 1);
        assert_eq!(total_pages(25, 25), 1);
        assert_eq!(total_pages(26, 25), 2);
        assert_eq!(total_pages(50, 25), 2);
        assert_eq!(total_pages(51, 25), 3);
    }
}
