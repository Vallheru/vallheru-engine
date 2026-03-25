//! Domain-grouped query modules.
//!
//! Each submodule owns the SQL for one bounded context. Row structs live next
//! to their queries, separate from the domain entities in `vallheru-domain`.

pub mod settings;
