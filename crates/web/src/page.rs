//! Shared page metadata, redirect helpers, and flash messages.
//!
//! In the PHP codebase, every page sets `$title`, builds a `$arrLinks`
//! array, and uses `error()` / `message()` functions for user feedback.
//! This module provides typed Rust equivalents so handlers and templates
//! use one consistent mechanism.

use axum::{
    http::{StatusCode, Uri, header},
    response::{IntoResponse, Response},
};

// ---------------------------------------------------------------------------
// Page metadata
// ---------------------------------------------------------------------------

/// Metadata that every page handler can populate for the layout template.
///
/// Passed into the template context so the layout can render the title,
/// back link, and any flash messages without per-page conditionals.
#[derive(Debug, Clone, Default)]
pub struct PageMeta {
    /// Browser / HTML `<title>` text.
    pub title: String,

    /// Optional "back" link shown near the page content.
    /// Tuple of `(url, label)`.
    pub back_link: Option<(String, String)>,

    /// Flash messages to display at the top of the page.
    pub flashes: Vec<Flash>,
}

impl PageMeta {
    /// Create page metadata with only a title.
    pub fn titled(title: impl Into<String>) -> Self {
        Self {
            title: title.into(),
            ..Self::default()
        }
    }

    /// Set the back link and return self for chaining.
    #[must_use]
    pub fn with_back_link(mut self, url: impl Into<String>, label: impl Into<String>) -> Self {
        self.back_link = Some((url.into(), label.into()));
        self
    }

    /// Add a flash message and return self for chaining.
    #[must_use]
    pub fn with_flash(mut self, flash: Flash) -> Self {
        self.flashes.push(flash);
        self
    }
}

// ---------------------------------------------------------------------------
// Flash messages
// ---------------------------------------------------------------------------

/// A single flash message shown to the user.
#[derive(Debug, Clone)]
pub struct Flash {
    /// Visual category (maps to CSS class in templates).
    pub kind: FlashKind,
    /// The message body (plain text or safe HTML).
    pub message: String,
}

/// Flash message severity / visual style.
#[derive(Debug, Clone, Copy, PartialEq, Eq)]
pub enum FlashKind {
    /// Positive confirmation (green).
    Success,
    /// Informational notice (blue).
    Info,
    /// Warning that doesn't block the action (yellow).
    Warning,
    /// Error that blocked the action (red).
    Error,
}

impl Flash {
    pub fn success(message: impl Into<String>) -> Self {
        Self {
            kind: FlashKind::Success,
            message: message.into(),
        }
    }

    pub fn info(message: impl Into<String>) -> Self {
        Self {
            kind: FlashKind::Info,
            message: message.into(),
        }
    }

    pub fn warning(message: impl Into<String>) -> Self {
        Self {
            kind: FlashKind::Warning,
            message: message.into(),
        }
    }

    pub fn error(message: impl Into<String>) -> Self {
        Self {
            kind: FlashKind::Error,
            message: message.into(),
        }
    }
}

// ---------------------------------------------------------------------------
// Redirect helpers
// ---------------------------------------------------------------------------

/// Issue a `302 Found` redirect to the given path.
pub fn redirect(location: &str) -> Response {
    (StatusCode::FOUND, [(header::LOCATION, location)]).into_response()
}

/// Issue a `303 See Other` redirect (used after POST to prevent re-submit).
pub fn redirect_after_post(location: &str) -> Response {
    (StatusCode::SEE_OTHER, [(header::LOCATION, location)]).into_response()
}

/// Build a redirect URI from a `Uri` reference, falling back to `/` if
/// the back-link is missing or invalid.
pub fn safe_back_or(back: Option<&Uri>, fallback: &str) -> String {
    back.map_or_else(|| fallback.to_owned(), ToString::to_string)
}

// ---------------------------------------------------------------------------
// Tests
// ---------------------------------------------------------------------------

#[cfg(test)]
mod tests {
    use super::*;

    #[test]
    fn page_meta_builder() {
        let meta = PageMeta::titled("Miasto")
            .with_back_link("/city", "Wróć do miasta")
            .with_flash(Flash::success("Wyleczono!"));

        assert_eq!(meta.title, "Miasto");
        assert_eq!(
            meta.back_link.as_ref().map(|(u, _)| u.as_str()),
            Some("/city")
        );
        assert_eq!(meta.flashes.len(), 1);
        assert_eq!(meta.flashes[0].kind, FlashKind::Success);
    }

    #[test]
    fn redirect_returns_302() {
        let resp = redirect("/city");
        assert_eq!(resp.status(), StatusCode::FOUND);
    }

    #[test]
    fn redirect_after_post_returns_303() {
        let resp = redirect_after_post("/city");
        assert_eq!(resp.status(), StatusCode::SEE_OTHER);
    }

    #[test]
    fn safe_back_falls_back() {
        assert_eq!(safe_back_or(None, "/city"), "/city");
        let uri: Uri = "/bank".parse().unwrap();
        assert_eq!(safe_back_or(Some(&uri), "/city"), "/bank");
    }
}
