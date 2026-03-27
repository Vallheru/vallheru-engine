//! World map handler.
//!
//! Ported from `map.php`. Simply renders a static map image with
//! location labels as clickable hotspots.

use axum::{Extension, extract::State, response::Response};

use crate::middleware::context::RequestContext;
use crate::page::PageMeta;
use crate::state::AppState;

/// View model for the map page.
#[derive(serde::Serialize)]
pub struct MapView {
    #[serde(flatten)]
    pub base: crate::render::RenderContext,
}

/// GET /map — world map.
pub async fn show(
    State(state): State<AppState>,
    Extension(ctx): Extension<RequestContext>,
) -> Response {
    let meta = PageMeta::titled("Mapa");
    let base = state.templates.build_context(&ctx, &meta);
    let view = MapView { base };
    state.templates.render_value("map.html", &view)
}
