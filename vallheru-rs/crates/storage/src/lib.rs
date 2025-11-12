//! Storage helpers for data access and integration.

/// Returns the name of the storage layer for sanity checks.
pub fn name() -> &'static str {
    "storage"
}

/// Example helper proving access to the engine layer.
pub fn engine_name() -> &'static str {
    engine::name()
}
