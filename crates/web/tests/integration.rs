//! Integration tests for core user journeys.
//!
//! These tests exercise the full HTTP stack (router → middleware → handler → response)
//! without a live database. Routes that need no database queries (healthz, buildinfo,
//! migration-status, fallback) are tested directly. Routes behind authentication
//! middleware are verified to return 401 for unauthenticated requests.
//!
//! DB-backed journey tests (login → navigate → action) are gated behind the
//! `TEST_DATABASE_URL` environment variable.

use axum::body::Body;
use axum::http::{Request, StatusCode};
use tower::ServiceExt;

/// Build a test app router backed by a lazy (non-connecting) pool.
///
/// Suitable for routes that do not execute database queries.
fn test_app() -> axum::Router {
    use sqlx::postgres::PgPoolOptions;
    use vallheru_web::{AppState, Catalog, ContextDefaults, TemplateEngine, TemplateEngineConfig};

    // Lazy pool — does not connect until a query runs.
    let pool = PgPoolOptions::new()
        .connect_lazy("postgres://fake:fake@localhost:5432/fake")
        .expect("lazy pool creation should not fail");

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
        email: vallheru_web::EmailService::log_only(),
    };

    vallheru_web::build_router(state)
}

fn get(uri: &str) -> Request<Body> {
    Request::builder().uri(uri).body(Body::empty()).unwrap()
}

fn post(uri: &str) -> Request<Body> {
    Request::builder()
        .method("POST")
        .uri(uri)
        .body(Body::empty())
        .unwrap()
}

// ---------------------------------------------------------------------------
// Operational endpoints (no auth, no DB)
// ---------------------------------------------------------------------------

#[tokio::test]
async fn healthz_returns_200() {
    let app = test_app();
    let resp = app.oneshot(get("/healthz")).await.unwrap();
    assert_eq!(resp.status(), StatusCode::OK);
}

#[tokio::test]
async fn buildinfo_returns_json() {
    let app = test_app();
    let resp = app.oneshot(get("/buildinfo")).await.unwrap();
    assert_eq!(resp.status(), StatusCode::OK);

    let ct = resp
        .headers()
        .get("content-type")
        .expect("buildinfo should have content-type");
    assert!(
        ct.to_str().unwrap().contains("application/json"),
        "expected JSON content-type, got {ct:?}"
    );
}

#[tokio::test]
async fn migration_status_returns_route_list() {
    let app = test_app();
    let resp = app.oneshot(get("/migration-status")).await.unwrap();
    assert_eq!(resp.status(), StatusCode::OK);

    let body = axum::body::to_bytes(resp.into_body(), 1024 * 1024)
        .await
        .unwrap();
    let routes: serde_json::Value = serde_json::from_slice(&body).unwrap();
    let arr = routes.as_array().expect("expected JSON array");

    // Should have many routes registered.
    assert!(
        arr.len() > 50,
        "expected >50 registered routes, got {}",
        arr.len()
    );

    // Check a few key routes are present.
    let paths: Vec<&str> = arr
        .iter()
        .filter_map(|r| r.get("path").and_then(|p| p.as_str()))
        .collect();
    assert!(paths.contains(&"/healthz"), "healthz missing");
    assert!(paths.contains(&"/city"), "city missing");
    assert!(paths.contains(&"/bank"), "bank missing");
    assert!(paths.contains(&"/outposts"), "outposts missing");
}

// ---------------------------------------------------------------------------
// Fallback (unmigrated routes)
// ---------------------------------------------------------------------------

#[tokio::test]
async fn unknown_path_returns_404() {
    let app = test_app();
    let resp = app.oneshot(get("/this-does-not-exist")).await.unwrap();
    assert_eq!(resp.status(), StatusCode::NOT_FOUND);
}

#[tokio::test]
async fn php_extension_returns_404() {
    let app = test_app();
    let resp = app.oneshot(get("/city.php")).await.unwrap();
    assert_eq!(resp.status(), StatusCode::NOT_FOUND);
}

// ---------------------------------------------------------------------------
// Auth-protected routes return 401 without session
// ---------------------------------------------------------------------------

#[tokio::test]
async fn city_requires_auth() {
    let app = test_app();
    let resp = app.oneshot(get("/city")).await.unwrap();
    assert_eq!(resp.status(), StatusCode::UNAUTHORIZED);
}

#[tokio::test]
async fn bank_requires_auth() {
    let app = test_app();
    let resp = app.oneshot(get("/bank")).await.unwrap();
    assert_eq!(resp.status(), StatusCode::UNAUTHORIZED);
}

#[tokio::test]
async fn equipment_requires_auth() {
    let app = test_app();
    let resp = app.oneshot(get("/equipment")).await.unwrap();
    assert_eq!(resp.status(), StatusCode::UNAUTHORIZED);
}

#[tokio::test]
async fn mail_requires_auth() {
    let app = test_app();
    let resp = app.oneshot(get("/mail")).await.unwrap();
    assert_eq!(resp.status(), StatusCode::UNAUTHORIZED);
}

#[tokio::test]
async fn forums_requires_auth() {
    let app = test_app();
    let resp = app.oneshot(get("/forums")).await.unwrap();
    assert_eq!(resp.status(), StatusCode::UNAUTHORIZED);
}

#[tokio::test]
async fn admin_requires_auth() {
    let app = test_app();
    let resp = app.oneshot(get("/admin")).await.unwrap();
    assert_eq!(resp.status(), StatusCode::UNAUTHORIZED);
}

#[tokio::test]
async fn staff_requires_auth() {
    let app = test_app();
    let resp = app.oneshot(get("/staff")).await.unwrap();
    assert_eq!(resp.status(), StatusCode::UNAUTHORIZED);
}

#[tokio::test]
async fn outposts_requires_auth() {
    let app = test_app();
    let resp = app.oneshot(get("/outposts")).await.unwrap();
    assert_eq!(resp.status(), StatusCode::UNAUTHORIZED);
}

#[tokio::test]
async fn market_requires_auth() {
    let app = test_app();
    let resp = app.oneshot(get("/market")).await.unwrap();
    assert_eq!(resp.status(), StatusCode::UNAUTHORIZED);
}

// ---------------------------------------------------------------------------
// Login endpoint (POST-only, no GET registered)
// ---------------------------------------------------------------------------

#[tokio::test]
async fn login_post_without_body_does_not_crash() {
    let app = test_app();
    // POST /login with empty body — should fail gracefully (422 or 400),
    // not 500.
    let resp = app.oneshot(post("/login")).await.unwrap();
    let status = resp.status();
    assert!(
        status == StatusCode::BAD_REQUEST
            || status == StatusCode::UNPROCESSABLE_ENTITY
            || status == StatusCode::UNSUPPORTED_MEDIA_TYPE,
        "expected 400/415/422 for empty login POST, got {status}"
    );
}

// ---------------------------------------------------------------------------
// Static asset serving
// ---------------------------------------------------------------------------

#[tokio::test]
async fn static_css_returns_200() {
    let app = test_app();
    let resp = app.oneshot(get("/static/css/classic.css")).await.unwrap();
    // classic.css is embedded from css/ — should return 200.
    assert_eq!(
        resp.status(),
        StatusCode::OK,
        "embedded classic.css should be served"
    );
}

// ---------------------------------------------------------------------------
// RSS feed (public, no auth)
// ---------------------------------------------------------------------------

#[tokio::test]
async fn rss_requires_no_auth() {
    let app = test_app();
    let resp = app.oneshot(get("/rss")).await.unwrap();
    // RSS needs DB for news items, but it should NOT return 401.
    // It may return 500 if the lazy pool fails, but not 401.
    assert_ne!(
        resp.status(),
        StatusCode::UNAUTHORIZED,
        "RSS should not require authentication"
    );
}
