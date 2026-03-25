//! Template environment setup, shared globals, and helper functions.
//!
//! This module replaces the Smarty templating layer used in the PHP
//! codebase. Templates are loaded from an embedded source (see MP-04-03)
//! and rendered with per-request context built from [`RenderContext`].

use std::sync::Arc;

use axum::{
    http::StatusCode,
    response::{Html, IntoResponse, Response},
};
use minijinja::Environment;

use crate::i18n::{Catalog, make_translate_fn};
use crate::middleware::context::RequestContext;
use crate::page::PageMeta;

/// Immutable, thread-safe wrapper around the minijinja [`Environment`].
///
/// Stored in [`AppState`](crate::state::AppState). Created once at startup
/// and shared across all requests via `Arc`.
#[derive(Clone)]
pub struct TemplateEngine {
    env: Arc<Environment<'static>>,
    game_name: String,
    base_url: String,
}

/// Configuration for initialising the template engine.
pub struct TemplateEngineConfig {
    /// Game display name (available as `game_name` in every template).
    pub game_name: String,
    /// Public base URL (available as `base_url` in every template).
    pub base_url: String,
}

/// Data bag passed to every template render call.
///
/// Handlers build this from the request context and page metadata, then
/// call [`TemplateEngine::render`].
#[derive(serde::Serialize)]
pub struct RenderContext {
    // Global / config values
    pub game_name: String,
    pub base_url: String,

    // Per-request values
    pub locale: String,
    pub theme: String,
    /// CSS file for the active theme (e.g. `"default.css"`, `"layout1.css"`).
    pub theme_css: String,
    pub request_id: String,

    // Page-level values
    pub title: String,
    pub back_link_url: String,
    pub back_link_label: String,
    pub flashes: Vec<FlashView>,

    // Authentication
    pub is_authenticated: bool,
    pub user_name: String,
    pub user_rank: String,

    // Page-level asset declarations
    /// Extra CSS files for this page (paths relative to `/static/css/`).
    pub extra_css: Vec<String>,
    /// Extra JS files for this page (paths relative to `/static/js/`).
    pub extra_js: Vec<String>,
}

/// Serialisable flash message for template rendering.
#[derive(serde::Serialize)]
pub struct FlashView {
    pub kind: String,
    pub message: String,
}

impl TemplateEngine {
    /// Create a new template engine with the given configuration.
    ///
    /// Embedded templates from `templates_jinja/` are loaded automatically.
    /// The `catalog` is wired into a `t(module, key)` template function.
    /// Additional templates can be registered via [`add_template`](Self::add_template).
    pub fn new(config: &TemplateEngineConfig, catalog: &Catalog) -> Self {
        let mut env = Environment::new();

        // Global values available in every template.
        env.add_global(
            "game_name",
            minijinja::Value::from(config.game_name.clone()),
        );
        env.add_global("base_url", minijinja::Value::from(config.base_url.clone()));

        // Register shared helper functions.
        env.add_function("asset_url", asset_url);
        env.add_function("t", make_translate_fn(catalog.clone()));

        // Load all embedded templates from the compiled-in directory.
        crate::assets::load_templates_into(&mut env);

        Self {
            env: Arc::new(env),
            game_name: config.game_name.clone(),
            base_url: config.base_url.clone(),
        }
    }

    /// Register a template by name and source string.
    ///
    /// Useful for testing and for bootstrapping initial templates before
    /// the embedded asset loader is wired.
    pub fn add_template(&mut self, name: &'static str, source: &'static str) {
        Arc::get_mut(&mut self.env)
            .expect("add_template must be called before the engine is shared")
            .add_template(name, source)
            .unwrap_or_else(|e| {
                panic!("failed to add template {name}: {e}");
            });
    }

    /// Render a named template with the given context.
    ///
    /// Returns an HTML response on success or a 500 error with template
    /// error details logged.
    pub fn render(&self, template_name: &str, ctx: &RenderContext) -> Response {
        let tmpl = match self.env.get_template(template_name) {
            Ok(t) => t,
            Err(e) => {
                tracing::error!(template = template_name, error = %e, "template not found");
                return (StatusCode::INTERNAL_SERVER_ERROR, "Template not found").into_response();
            }
        };

        match tmpl.render(minijinja::Value::from_serialize(ctx)) {
            Ok(html) => Html(html).into_response(),
            Err(e) => {
                tracing::error!(template = template_name, error = %e, "template render error");
                (StatusCode::INTERNAL_SERVER_ERROR, "Template render error").into_response()
            }
        }
    }

    /// Build a [`RenderContext`] from per-request state and page metadata.
    pub fn build_context(&self, req_ctx: &RequestContext, meta: &PageMeta) -> RenderContext {
        let (back_link_url, back_link_label) = meta
            .back_link
            .as_ref()
            .map(|(u, l)| (u.clone(), l.clone()))
            .unwrap_or_default();

        let flashes = meta
            .flashes
            .iter()
            .map(|f| FlashView {
                kind: match f.kind {
                    crate::page::FlashKind::Success => "success".to_owned(),
                    crate::page::FlashKind::Info => "info".to_owned(),
                    crate::page::FlashKind::Warning => "warning".to_owned(),
                    crate::page::FlashKind::Error => "error".to_owned(),
                },
                message: f.message.clone(),
            })
            .collect();

        let (is_authenticated, user_name, user_rank) = if let Some(user) = &req_ctx.session_user {
            (true, user.name.clone(), user.rank.clone())
        } else {
            (false, String::new(), String::new())
        };

        RenderContext {
            game_name: self.game_name.clone(),
            base_url: self.base_url.clone(),
            locale: req_ctx.locale.clone(),
            theme_css: resolve_theme_css(&req_ctx.theme),
            theme: req_ctx.theme.clone(),
            request_id: req_ctx.request_id.to_string(),
            title: meta.title.clone(),
            back_link_url,
            back_link_label,
            flashes,
            is_authenticated,
            user_name,
            user_rank,
            extra_css: meta.extra_css.clone(),
            extra_js: meta.extra_js.clone(),
        }
    }
}

// ---------------------------------------------------------------------------
// Template helper functions
// ---------------------------------------------------------------------------

/// Template function: `asset_url(path)` — returns a versioned asset URL.
///
/// For now returns the path unchanged. Once content hashing is wired
/// (MP-04-03) this will append a cache-busting query parameter.
fn asset_url(path: String) -> String {
    // TODO(MP-04-03): append content hash query parameter
    path
}

/// Map a theme key to the CSS filename templates should load.
///
/// The PHP codebase stores the CSS filename in `player.settings['style']`
/// and `layout1` uses `layout1.css`. We normalise so an empty string
/// falls back to `default.css`.
fn resolve_theme_css(theme: &str) -> String {
    match theme {
        "" => "default.css".to_owned(),
        "layout1" => "layout1.css".to_owned(),
        other => format!("{other}.css"),
    }
}

/// Return the base template name for a given theme.
///
/// Used by handlers that want to render a page extending the theme base.
/// - `""` → `"themes/default/base.html"`
/// - `"layout1"` → `"themes/layout1/base.html"`
pub fn theme_base_template(theme: &str) -> &'static str {
    match theme {
        "layout1" => "themes/layout1/base.html",
        _ => "themes/default/base.html",
    }
}

// ---------------------------------------------------------------------------
// Tests
// ---------------------------------------------------------------------------

#[cfg(test)]
mod tests {
    use super::*;
    use crate::i18n::Catalog;
    use crate::middleware::context::{RequestContext, SessionUser};
    use crate::page::{Flash, PageMeta};
    use uuid::Uuid;

    fn make_engine() -> TemplateEngine {
        let catalog = Catalog::empty("pl");
        let mut engine = TemplateEngine::new(
            &TemplateEngineConfig {
                game_name: "TestGame".to_owned(),
                base_url: "https://example.com".to_owned(),
            },
            &catalog,
        );
        engine.add_template("hello", "Hello {{ title }}!");
        engine
    }

    fn make_request_context() -> RequestContext {
        RequestContext {
            request_id: Uuid::nil(),
            locale: "pl".to_owned(),
            theme: String::new(),
            session_user: None,
        }
    }

    #[test]
    fn render_simple_template() {
        let engine = make_engine();
        let req_ctx = make_request_context();
        let meta = PageMeta::titled("World");
        let ctx = engine.build_context(&req_ctx, &meta);
        let resp = engine.render("hello", &ctx);
        assert_eq!(resp.status(), StatusCode::OK);
    }

    #[test]
    fn render_missing_template_returns_500() {
        let engine = make_engine();
        let req_ctx = make_request_context();
        let meta = PageMeta::titled("World");
        let ctx = engine.build_context(&req_ctx, &meta);
        let resp = engine.render("nonexistent", &ctx);
        assert_eq!(resp.status(), StatusCode::INTERNAL_SERVER_ERROR);
    }

    #[test]
    fn build_context_with_session_user() {
        let engine = make_engine();
        let req_ctx = RequestContext {
            request_id: Uuid::nil(),
            locale: "pl".to_owned(),
            theme: "layout1".to_owned(),
            session_user: Some(SessionUser {
                id: 1,
                name: "Tester".to_owned(),
                rank: "Admin".to_owned(),
            }),
        };
        let meta = PageMeta::titled("Admin Panel")
            .with_back_link("/city", "Back")
            .with_flash(Flash::success("OK"));

        let ctx = engine.build_context(&req_ctx, &meta);
        assert!(ctx.is_authenticated);
        assert_eq!(ctx.user_name, "Tester");
        assert_eq!(ctx.title, "Admin Panel");
        assert_eq!(ctx.back_link_url, "/city");
        assert_eq!(ctx.flashes.len(), 1);
        assert_eq!(ctx.flashes[0].kind, "success");
    }

    #[test]
    fn build_context_anonymous() {
        let engine = make_engine();
        let req_ctx = make_request_context();
        let meta = PageMeta::titled("Login");
        let ctx = engine.build_context(&req_ctx, &meta);
        assert!(!ctx.is_authenticated);
        assert!(ctx.user_name.is_empty());
    }

    #[test]
    fn asset_url_passthrough() {
        assert_eq!(asset_url("css/main.css".to_owned()), "css/main.css");
    }

    #[test]
    fn resolve_theme_css_defaults() {
        assert_eq!(resolve_theme_css(""), "default.css");
        assert_eq!(resolve_theme_css("layout1"), "layout1.css");
        assert_eq!(resolve_theme_css("custom"), "custom.css");
    }

    #[test]
    fn theme_base_template_routing() {
        assert_eq!(theme_base_template(""), "themes/default/base.html");
        assert_eq!(theme_base_template("layout1"), "themes/layout1/base.html");
        assert_eq!(theme_base_template("unknown"), "themes/default/base.html");
    }

    #[test]
    fn build_context_sets_theme_css() {
        let engine = make_engine();
        let req_ctx = RequestContext {
            request_id: Uuid::nil(),
            locale: "pl".to_owned(),
            theme: "layout1".to_owned(),
            session_user: None,
        };
        let meta = PageMeta::titled("Test");
        let ctx = engine.build_context(&req_ctx, &meta);
        assert_eq!(ctx.theme_css, "layout1.css");
        assert_eq!(ctx.theme, "layout1");
    }

    #[test]
    fn ui_macros_are_loadable() {
        // Verify the embedded _macros/ui.html can be parsed and imported.
        let catalog = Catalog::empty("pl");
        let engine = TemplateEngine::new(
            &TemplateEngineConfig {
                game_name: "TestGame".to_owned(),
                base_url: "https://example.com".to_owned(),
            },
            &catalog,
        );

        // A template that imports and uses the UI macros.
        let source = r#"
{%- import "_macros/ui.html" as ui -%}
{{ ui.alert("success", "It works!") }}
{{ ui.error_box("Something failed.") }}
{{ ui.pagination(2, 5, "/items?page=") }}
{{ ui.empty_state("No items found.") }}
"#;
        let mut env = minijinja::Environment::new();
        // Load the embedded ui.html macro file.
        crate::assets::load_templates_into(&mut env);
        env.add_template("test_page", source).unwrap();

        let tmpl = env.get_template("test_page").unwrap();
        let output = tmpl.render(minijinja::context!()).unwrap();

        assert!(output.contains("alert-success"));
        assert!(output.contains("It works!"));
        assert!(output.contains("alert-error"));
        assert!(output.contains("Something failed."));
        assert!(output.contains("pagination"));
        assert!(output.contains("page-current"));
        assert!(output.contains("empty-state"));

        // Verify the engine itself loaded the macros file.
        drop(engine);
    }

    #[test]
    fn build_context_carries_extra_assets() {
        let catalog = Catalog::empty("pl");
        let engine = TemplateEngine::new(
            &TemplateEngineConfig {
                game_name: "TestGame".to_owned(),
                base_url: "https://example.com".to_owned(),
            },
            &catalog,
        );
        let req_ctx = make_request_context();
        let meta = PageMeta::titled("Bank")
            .with_js("bank.js")
            .with_css("custom.css");

        let ctx = engine.build_context(&req_ctx, &meta);
        assert_eq!(ctx.extra_js, vec!["bank.js"]);
        assert_eq!(ctx.extra_css, vec!["custom.css"]);
    }
}
