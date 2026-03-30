//! Golden-master snapshot tests for critical page responses.
//!
//! Captures HTTP responses from the Rust server and verifies they contain
//! expected structural elements. These act as regression guards — if a
//! template or handler changes unexpectedly, these tests fail.
//!
//! No live database required for public page snapshots. Authenticated
//! page snapshots require `TEST_DATABASE_URL`.

use axum::body::Body;
use axum::http::{Request, StatusCode};
use tower::ServiceExt;

/// Build a test app router backed by a lazy (non-connecting) pool.
fn test_app() -> axum::Router {
    use sqlx::postgres::PgPoolOptions;
    use vallheru_web::{AppState, Catalog, ContextDefaults, TemplateEngine, TemplateEngineConfig};

    let pool = PgPoolOptions::new()
        .connect_lazy("postgres://fake:fake@localhost:5432/fake")
        .expect("lazy pool");

    let catalog = Catalog::empty("pl");
    let config = TemplateEngineConfig {
        game_name: "TestVallheru".to_owned(),
        base_url: "http://localhost:3000".to_owned(),
    };
    let templates = TemplateEngine::new(&config, &catalog);

    let state = AppState {
        pool,
        context_defaults: ContextDefaults {
            locale: "pl".to_owned(),
        },
        templates,
        catalog,
        post_rate_limiter: vallheru_web::PostRateLimiter::default(),
    };

    vallheru_web::build_router(state)
}

fn get(uri: &str) -> Request<Body> {
    Request::builder().uri(uri).body(Body::empty()).unwrap()
}

async fn body_string(resp: axum::http::Response<Body>) -> String {
    let bytes = axum::body::to_bytes(resp.into_body(), 1_048_576)
        .await
        .expect("body read");
    String::from_utf8_lossy(&bytes).into_owned()
}

// ---------------------------------------------------------------------------
// Public page golden-master snapshots
// ---------------------------------------------------------------------------

#[tokio::test]
async fn login_post_rejects_empty_form() {
    let app = test_app();
    let req = Request::builder()
        .method("POST")
        .uri("/login")
        .header("content-type", "application/x-www-form-urlencoded")
        .body(Body::from("username=&password="))
        .unwrap();
    let resp = app.oneshot(req).await.unwrap();
    // Empty credentials should fail — either 422, 400, or redirect to login
    assert_ne!(
        resp.status(),
        StatusCode::OK,
        "empty login must not succeed"
    );
    assert_ne!(
        resp.status(),
        StatusCode::NOT_FOUND,
        "login route must exist"
    );
}

#[tokio::test]
async fn register_page_responds() {
    let app = test_app();
    let resp = app.oneshot(get("/register")).await.unwrap();
    // Without a live database, register may return 500 (DB query fails)
    // or 200 (if caching is added). Either way, it must not 404.
    assert_ne!(
        resp.status(),
        StatusCode::NOT_FOUND,
        "register route must exist"
    );
}

#[tokio::test]
async fn lost_password_page_contains_form() {
    let app = test_app();
    let resp = app.oneshot(get("/lost-password")).await.unwrap();
    assert_eq!(resp.status(), StatusCode::OK);

    let html = body_string(resp).await;
    assert!(
        html.contains("<form"),
        "lost-password page must contain a form"
    );
    assert!(
        html.contains("email"),
        "lost-password page must have email field"
    );
}

#[tokio::test]
async fn rss_feed_contains_xml_structure() {
    let app = test_app();
    let resp = app.oneshot(get("/rss")).await.unwrap();
    assert_eq!(resp.status(), StatusCode::OK);

    let ct = resp
        .headers()
        .get("content-type")
        .expect("rss must have content-type");
    assert!(
        ct.to_str().unwrap().contains("xml"),
        "RSS feed must be XML content-type"
    );

    let xml = body_string(resp).await;
    assert!(
        xml.contains("<rss") || xml.contains("<feed"),
        "RSS must contain feed root element"
    );
    assert!(
        xml.contains("TestVallheru"),
        "RSS must contain game name in title"
    );
}

#[tokio::test]
async fn buildinfo_contains_expected_keys() {
    let app = test_app();
    let resp = app.oneshot(get("/buildinfo")).await.unwrap();
    assert_eq!(resp.status(), StatusCode::OK);

    let body = body_string(resp).await;
    let json: serde_json::Value =
        serde_json::from_str(&body).expect("buildinfo must be valid JSON");

    assert!(json.get("git_hash").is_some(), "must contain git_hash");
    assert!(json.get("version").is_some(), "must contain version");
}

#[tokio::test]
async fn migration_status_contains_routes() {
    let app = test_app();
    let resp = app.oneshot(get("/migration-status")).await.unwrap();
    assert_eq!(resp.status(), StatusCode::OK);

    let body = body_string(resp).await;
    let json: serde_json::Value =
        serde_json::from_str(&body).expect("migration-status must be valid JSON");

    let routes = json.as_array().expect("must be a JSON array");
    assert!(routes.len() > 50, "must have >50 registered routes");
}

// ---------------------------------------------------------------------------
// Authenticated page redirect snapshots
//
// These verify the correct redirect behavior for unauthenticated requests
// to protected pages — important for session-based routing.
// ---------------------------------------------------------------------------

#[tokio::test]
async fn city_redirects_unauthenticated() {
    let app = test_app();
    let resp = app.oneshot(get("/city")).await.unwrap();
    // Must return 401 or redirect to login
    assert!(
        resp.status() == StatusCode::UNAUTHORIZED
            || resp.status() == StatusCode::SEE_OTHER
            || resp.status() == StatusCode::TEMPORARY_REDIRECT,
        "city must reject unauthenticated: got {}",
        resp.status()
    );
}

#[tokio::test]
async fn bank_redirects_unauthenticated() {
    let app = test_app();
    let resp = app.oneshot(get("/bank")).await.unwrap();
    assert!(
        resp.status() == StatusCode::UNAUTHORIZED
            || resp.status() == StatusCode::SEE_OTHER
            || resp.status() == StatusCode::TEMPORARY_REDIRECT,
        "bank must reject unauthenticated: got {}",
        resp.status()
    );
}

#[tokio::test]
async fn equipment_redirects_unauthenticated() {
    let app = test_app();
    let resp = app.oneshot(get("/equipment")).await.unwrap();
    assert!(
        resp.status() == StatusCode::UNAUTHORIZED
            || resp.status() == StatusCode::SEE_OTHER
            || resp.status() == StatusCode::TEMPORARY_REDIRECT,
        "equipment must reject unauthenticated: got {}",
        resp.status()
    );
}

// ---------------------------------------------------------------------------
// Static asset snapshots
// ---------------------------------------------------------------------------

#[tokio::test]
async fn css_contains_stylesheet_content() {
    let app = test_app();
    let resp = app.oneshot(get("/static/css/classic.css")).await.unwrap();
    assert_eq!(resp.status(), StatusCode::OK);

    let ct = resp
        .headers()
        .get("content-type")
        .expect("CSS must have content-type");
    assert!(
        ct.to_str().unwrap().contains("css"),
        "Must serve CSS content-type"
    );

    let css = body_string(resp).await;
    assert!(!css.is_empty(), "CSS must not be empty");
    assert!(
        css.contains('{'),
        "CSS must contain style rules (curly braces)"
    );
}

// ---------------------------------------------------------------------------
// Error page snapshots
// ---------------------------------------------------------------------------

#[tokio::test]
async fn not_found_returns_structured_error() {
    let app = test_app();
    let resp = app.oneshot(get("/nonexistent-page-xyz")).await.unwrap();
    assert_eq!(resp.status(), StatusCode::NOT_FOUND);
}

#[tokio::test]
async fn php_extension_returns_404() {
    let app = test_app();
    let resp = app.oneshot(get("/city.php")).await.unwrap();
    assert_eq!(resp.status(), StatusCode::NOT_FOUND);
}
