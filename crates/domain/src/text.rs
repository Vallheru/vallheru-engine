//! Text-processing utilities shared across domain logic.

/// Remove HTML tags from a string (equivalent to PHP `strip_tags`).
pub fn strip_tags(input: &str) -> String {
    let mut result = String::with_capacity(input.len());
    let mut in_tag = false;
    for ch in input.chars() {
        if ch == '<' {
            in_tag = true;
        } else if ch == '>' {
            in_tag = false;
        } else if !in_tag {
            result.push(ch);
        }
    }
    result
}

#[cfg(test)]
mod tests {
    use super::*;

    #[test]
    fn strip_tags_removes_html() {
        assert_eq!(strip_tags("<b>bold</b>"), "bold");
        assert_eq!(strip_tags("no tags"), "no tags");
        assert_eq!(strip_tags("<script>alert('xss')</script>"), "alert('xss')");
        assert_eq!(strip_tags(""), "");
    }
}
