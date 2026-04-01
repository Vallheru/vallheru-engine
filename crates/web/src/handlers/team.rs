//! Team (party) handler — currently disabled in-game.
//!
//! Ported from `team.php`. The PHP code shows "Czasowo niedostępne."
//! (temporarily unavailable) immediately. We replicate that behavior.

use axum::Extension;
use axum::extract::State;
use axum::response::Response;

use crate::middleware::context::RequestContext;
use crate::page::{Flash, FlashKind, PageMeta};
use crate::state::AppState;

// =========================================================================
// View model
// =========================================================================

#[derive(serde::Serialize)]
pub struct TeamView {
    #[serde(flatten)]
    pub base: crate::render::RenderContext,
}

// =========================================================================
// Handler
// =========================================================================

/// GET /team — show "temporarily unavailable" message.
pub async fn team_show(
    State(app): State<AppState>,
    Extension(ctx): Extension<RequestContext>,
) -> Response {
    let meta = PageMeta::titled("Drużyna")
        .with_back_link("/city", "Wróć do miasta")
        .with_flash(Flash {
            kind: FlashKind::Warning,
            message: "Czasowo niedostępne.".to_owned(),
        });
    let base = app.templates.build_context(&ctx, &meta);
    let view = TeamView { base };
    app.templates.render_value("team.html", &view)
}
