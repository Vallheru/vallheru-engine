//! Domain-grouped query modules.
//!
//! Each submodule owns the SQL for one bounded context. Row structs live next
//! to their queries, separate from the domain entities in `vallheru-domain`.

pub mod account;
pub mod account_settings;
pub mod auth;
pub mod bank;
pub mod character_reset;
pub mod chat;
pub mod content;
pub mod forum;
pub mod gathering;
pub mod house;
pub mod item;
pub mod locations;
pub mod mail;
pub mod market;
pub mod mission;
pub mod pages;
pub mod player;
pub mod registration;
pub mod room;
pub mod session;
pub mod settings;
pub mod travel;
