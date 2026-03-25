//! Application state shared across all Axum handlers.

use vallheru_data::pool::PgPool;

/// Shared application state available to all Axum handlers.
#[derive(Clone)]
pub struct AppState {
    pub pool: PgPool,
}
