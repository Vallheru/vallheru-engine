//! Application state shared across all Axum handlers.

use vallheru_data::pool::PgPool;

use crate::middleware::context::ContextDefaults;

/// Shared application state available to all Axum handlers.
#[derive(Clone)]
pub struct AppState {
    pub pool: PgPool,
    /// Defaults used by the request-context middleware.
    pub context_defaults: ContextDefaults,
}
