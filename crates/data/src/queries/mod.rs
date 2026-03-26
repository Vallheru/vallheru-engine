//! Domain-grouped query modules.
//!
//! Each submodule owns the SQL for one bounded context. Row structs live next
//! to their queries, separate from the domain entities in `vallheru-domain`.

pub mod account;
pub mod auth;
pub mod registration;
pub mod session;
pub mod settings;
