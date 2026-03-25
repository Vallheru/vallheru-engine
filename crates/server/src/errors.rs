use axum::http::StatusCode;
use axum::response::{IntoResponse, Response};

/// Application-level error that Axum handlers can return.
///
/// Internal details are logged but never exposed to the client.
/// The user sees only a generic status-appropriate message.
#[derive(Debug)]
pub struct AppError {
    status: StatusCode,
    /// User-visible message (safe to display).
    user_message: String,
    /// Internal cause for logging.
    cause: Option<anyhow::Error>,
}

impl AppError {
    pub fn internal(cause: impl Into<anyhow::Error>) -> Self {
        Self {
            status: StatusCode::INTERNAL_SERVER_ERROR,
            user_message: "Wystąpił błąd serwera.".to_owned(),
            cause: Some(cause.into()),
        }
    }

    pub fn not_found(msg: impl Into<String>) -> Self {
        Self {
            status: StatusCode::NOT_FOUND,
            user_message: msg.into(),
            cause: None,
        }
    }

    pub fn bad_request(msg: impl Into<String>) -> Self {
        Self {
            status: StatusCode::BAD_REQUEST,
            user_message: msg.into(),
            cause: None,
        }
    }

    pub fn forbidden(msg: impl Into<String>) -> Self {
        Self {
            status: StatusCode::FORBIDDEN,
            user_message: msg.into(),
            cause: None,
        }
    }
}

impl std::fmt::Display for AppError {
    fn fmt(&self, f: &mut std::fmt::Formatter<'_>) -> std::fmt::Result {
        write!(f, "{} {}", self.status.as_u16(), self.user_message)
    }
}

impl IntoResponse for AppError {
    fn into_response(self) -> Response {
        if let Some(cause) = &self.cause {
            tracing::error!(
                status = self.status.as_u16(),
                error = %cause,
                "request error"
            );
        }

        // Minimal HTML error page. Will be replaced with a MiniJinja template
        // once the rendering stack is in place (MP-04).
        let body = format!(
            "<!DOCTYPE html>\
            <html><head><title>Błąd {code}</title></head>\
            <body><h1>{code}</h1><p>{msg}</p></body></html>",
            code = self.status.as_u16(),
            msg = html_escape(&self.user_message),
        );

        (self.status, axum::response::Html(body)).into_response()
    }
}

/// Convert `anyhow::Error` into an internal server error automatically.
impl From<anyhow::Error> for AppError {
    fn from(err: anyhow::Error) -> Self {
        Self::internal(err)
    }
}

/// Minimal HTML entity escaping for error messages.
fn html_escape(s: &str) -> String {
    s.replace('&', "&amp;")
        .replace('<', "&lt;")
        .replace('>', "&gt;")
        .replace('"', "&quot;")
}

#[cfg(test)]
mod tests {
    use super::*;

    #[test]
    fn internal_error_is_500() {
        let err = AppError::internal(anyhow::anyhow!("db exploded"));
        assert_eq!(err.status, StatusCode::INTERNAL_SERVER_ERROR);
        assert!(!err.user_message.contains("db exploded"));
    }

    #[test]
    fn not_found_preserves_message() {
        let err = AppError::not_found("Strona nie istnieje");
        assert_eq!(err.status, StatusCode::NOT_FOUND);
        assert_eq!(err.user_message, "Strona nie istnieje");
    }

    #[test]
    fn html_escape_works() {
        assert_eq!(html_escape("<b>test</b>"), "&lt;b&gt;test&lt;/b&gt;");
    }
}
