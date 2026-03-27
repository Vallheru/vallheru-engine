//! Web request handlers, grouped by feature area.

pub mod account;
pub mod account_settings;
pub mod auth;
pub mod bank;
pub mod city;
pub mod equipment;
pub mod gathering;
pub mod locations;
pub mod map;
pub mod market;
pub mod preset;
pub mod registration;
pub mod shops;
pub mod spells;
pub mod travel;

use crate::middleware::context::RequestContext;
use crate::page::PageMeta;
use crate::render::RenderContext;
use crate::state::AppState;

/// Build a [`RenderContext`] for an unauthenticated visitor.
///
/// Shared by login, registration, activation, and password-reset handlers
/// which all operate outside an authenticated session.
pub(crate) fn build_anon_context(state: &AppState, meta: &PageMeta) -> RenderContext {
    let req_ctx = RequestContext {
        request_id: uuid::Uuid::new_v4(),
        locale: state.context_defaults.locale.clone(),
        theme: String::new(),
        session_user: None,
    };
    state.templates.build_context(&req_ctx, meta)
}
