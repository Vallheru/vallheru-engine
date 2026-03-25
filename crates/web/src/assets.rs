//! Embedded asset registry for templates, CSS, JS, and images.
//!
//! All shipped assets are compiled into the binary using `include_dir`.
//! Templates are loaded into the minijinja environment from embedded
//! sources. Static assets (CSS, JS, images) are served with proper
//! content-type headers and `ETag` for client-side caching.

use axum::{
    Router,
    extract::Path,
    http::{StatusCode, header},
    response::{IntoResponse, Response},
    routing::get,
};
use include_dir::{Dir, include_dir};

use crate::state::AppState;

/// Embedded Jinja templates (`templates_jinja/` at repo root).
pub static TEMPLATES_DIR: Dir<'static> = include_dir!("$CARGO_MANIFEST_DIR/../../templates_jinja");

/// Embedded CSS files (`css/` at repo root).
static CSS_DIR: Dir<'static> = include_dir!("$CARGO_MANIFEST_DIR/../../css");

/// Embedded JS files (`js/` at repo root).
static JS_DIR: Dir<'static> = include_dir!("$CARGO_MANIFEST_DIR/../../js");

/// Embedded image files (`images/` at repo root).
static IMAGES_DIR: Dir<'static> = include_dir!("$CARGO_MANIFEST_DIR/../../images");

/// Embedded localization files (`i18n/` at repo root).
pub static I18N_DIR: Dir<'static> = include_dir!("$CARGO_MANIFEST_DIR/../../i18n");

/// Register static asset serving routes.
///
/// - `GET /static/css/:path` — serves embedded CSS
/// - `GET /static/js/:path`  — serves embedded JS
/// - `GET /static/images/*path` — serves embedded images (supports subdirs)
pub fn routes() -> Router<AppState> {
    Router::new()
        .route("/static/css/{path}", get(serve_css))
        .route("/static/js/{path}", get(serve_js))
        .route("/static/images/{*path}", get(serve_image))
}

async fn serve_css(Path(path): Path<String>) -> Response {
    serve_from_dir(&CSS_DIR, &path)
}

async fn serve_js(Path(path): Path<String>) -> Response {
    serve_from_dir(&JS_DIR, &path)
}

async fn serve_image(Path(path): Path<String>) -> Response {
    serve_from_dir(&IMAGES_DIR, &path)
}

/// Serve a file from an embedded directory with content-type and etag.
fn serve_from_dir(dir: &Dir<'static>, path: &str) -> Response {
    let Some(file) = dir.get_file(path) else {
        return StatusCode::NOT_FOUND.into_response();
    };

    let contents = file.contents();
    let content_type = mime_guess::from_path(path)
        .first_or_octet_stream()
        .to_string();

    // Simple ETag based on content length + first/last bytes.
    let etag = format!("\"v{:x}-{}\"", contents.len(), env!("CARGO_PKG_VERSION"));

    (
        StatusCode::OK,
        [
            (header::CONTENT_TYPE, content_type),
            (header::ETAG, etag),
            (header::CACHE_CONTROL, "public, max-age=86400".to_owned()),
        ],
        contents,
    )
        .into_response()
}

/// Load all embedded templates into a minijinja environment.
///
/// Recursively walks `TEMPLATES_DIR` and registers each file as a
/// template named by its relative path (using `/` separators).
pub fn load_templates_into(env: &mut minijinja::Environment<'static>) {
    load_dir_recursive(&TEMPLATES_DIR, "", env);
}

fn load_dir_recursive(
    dir: &'static Dir<'static>,
    prefix: &str,
    env: &mut minijinja::Environment<'static>,
) {
    for file in dir.files() {
        let name = if prefix.is_empty() {
            file.path().to_string_lossy().to_string()
        } else {
            format!(
                "{prefix}/{}",
                file.path()
                    .file_name()
                    .unwrap_or_default()
                    .to_string_lossy()
            )
        };

        if let Some(source) = file.contents_utf8() {
            if let Err(e) = env.add_template_owned(name.clone(), source.to_owned()) {
                tracing::warn!(template = %name, error = %e, "failed to load embedded template");
            }
        }
    }

    for subdir in dir.dirs() {
        let sub_prefix = if prefix.is_empty() {
            subdir.path().to_string_lossy().to_string()
        } else {
            format!(
                "{prefix}/{}",
                subdir
                    .path()
                    .file_name()
                    .unwrap_or_default()
                    .to_string_lossy()
            )
        };
        load_dir_recursive(subdir, &sub_prefix, env);
    }
}

#[cfg(test)]
mod tests {
    use super::*;

    #[test]
    fn templates_dir_is_not_empty() {
        assert!(
            TEMPLATES_DIR.files().next().is_some() || TEMPLATES_DIR.dirs().next().is_some(),
            "TEMPLATES_DIR should contain files or directories"
        );
    }

    #[test]
    fn base_template_exists() {
        assert!(
            TEMPLATES_DIR.get_file("base.html").is_some(),
            "base.html should be embedded"
        );
    }

    #[test]
    fn css_dir_has_files() {
        assert!(
            CSS_DIR.files().next().is_some(),
            "CSS directory should contain files"
        );
    }

    #[test]
    fn serve_existing_css_file() {
        // classic.css should exist in the embedded CSS dir
        let resp = serve_from_dir(&CSS_DIR, "classic.css");
        assert_eq!(resp.status(), StatusCode::OK);
    }

    #[test]
    fn serve_missing_file_returns_404() {
        let resp = serve_from_dir(&CSS_DIR, "nonexistent.css");
        assert_eq!(resp.status(), StatusCode::NOT_FOUND);
    }

    #[test]
    fn load_templates_into_env() {
        let mut env = minijinja::Environment::new();
        load_templates_into(&mut env);
        assert!(
            env.get_template("base.html").is_ok(),
            "base.html should be loadable after load_templates_into"
        );
    }
}
