//! Mail domain logic — validation, threading, and business rules.

/// Maximum subject length (chars).
pub const MAX_SUBJECT_LENGTH: usize = 50;

/// Maximum body length (chars).
pub const MAX_BODY_LENGTH: usize = 10_000;

/// Messages per page for listing views.
pub const MESSAGES_PER_PAGE: i64 = 30;

/// Messages per page when reading a thread.
pub const THREAD_PAGE_SIZE: i64 = 20;

/// Validate a mail subject. Returns a normalised subject or auto-generates one
/// from the body if the subject is empty/whitespace.
pub fn normalise_subject(subject: &str, body: &str) -> String {
    let trimmed = subject.trim();
    if trimmed.is_empty() || !trimmed.chars().any(char::is_alphanumeric) {
        // Auto-generate from body like the PHP version.
        let preview: String = body.chars().take(10).collect();
        if preview.is_empty() {
            "...".to_owned()
        } else {
            format!("{preview}...")
        }
    } else {
        let mut s: String = trimmed.chars().take(MAX_SUBJECT_LENGTH).collect();
        s = s.replace('\u{00a0}', " "); // &nbsp → space
        s
    }
}

/// Validate a new message compose request.
/// Returns `Err(reason)` if invalid.
pub fn validate_compose(sender_id: i64, recipient_id: i64, body: &str) -> Result<(), &'static str> {
    if body.trim().is_empty() {
        return Err("Wypełnij wszystkie pola.");
    }
    if recipient_id == 0 {
        return Err("Wypełnij wszystkie pola.");
    }
    if recipient_id == sender_id {
        return Err("Nie możesz wysyłać listu do samego siebie!");
    }
    Ok(())
}

/// Compute total pages from item count and page size.
pub fn total_pages(count: i64, page_size: i64) -> i64 {
    if count == 0 {
        1
    } else {
        (count + page_size - 1) / page_size
    }
}

/// Clamp page number to valid range.
pub fn clamp_page(page: i64, total: i64) -> i64 {
    page.clamp(1, total.max(1))
}

/// Allowed "delete older than" durations in days.
pub const ALLOWED_DELETE_DAYS: [i32; 3] = [7, 14, 30];

/// Validate the delete-old duration.
pub fn validate_delete_days(days: i32) -> bool {
    ALLOWED_DELETE_DAYS.contains(&days)
}

#[cfg(test)]
mod tests {
    use super::*;

    #[test]
    fn normalise_empty_subject_uses_body_preview() {
        let result = normalise_subject("", "Hello world test message");
        assert_eq!(result, "Hello worl...");
    }

    #[test]
    fn normalise_valid_subject_passes_through() {
        let result = normalise_subject("My Subject", "body");
        assert_eq!(result, "My Subject");
    }

    #[test]
    fn normalise_whitespace_only_subject() {
        let result = normalise_subject("   ", "body text");
        assert_eq!(result, "body text...");
    }

    #[test]
    fn validate_compose_rejects_self_send() {
        assert!(validate_compose(1, 1, "hello").is_err());
    }

    #[test]
    fn validate_compose_rejects_empty_body() {
        assert!(validate_compose(1, 2, "  ").is_err());
    }

    #[test]
    fn validate_compose_accepts_valid() {
        assert!(validate_compose(1, 2, "hello").is_ok());
    }

    #[test]
    fn total_pages_calculation() {
        assert_eq!(total_pages(0, 30), 1);
        assert_eq!(total_pages(30, 30), 1);
        assert_eq!(total_pages(31, 30), 2);
        assert_eq!(total_pages(60, 30), 2);
        assert_eq!(total_pages(61, 30), 3);
    }

    #[test]
    fn clamp_page_range() {
        assert_eq!(clamp_page(0, 5), 1);
        assert_eq!(clamp_page(3, 5), 3);
        assert_eq!(clamp_page(10, 5), 5);
    }
}
