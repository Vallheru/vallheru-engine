//! Web request handlers, grouped by feature area.

pub mod account;
pub mod account_settings;
pub mod admin;
pub mod admin_logs;
pub mod auth;
pub mod bank;
pub mod bugreport;
pub mod chat;
pub mod city;
pub mod content;
pub mod court;
pub mod deity;
pub mod equipment;
pub mod forums;
pub mod gathering;
pub mod hospital;
pub mod house;
pub mod jail;
pub mod locations;
pub mod mail;
pub mod map;
pub mod market;
pub mod memberlist;
pub mod moderation;
pub mod outpost;
pub mod pages;
pub mod preset;
pub mod quest;
pub mod registration;
pub mod room;
pub mod shops;
pub mod spells;
pub mod staff;
pub mod temple;
pub mod tower;
pub mod travel;
pub mod tribe_forum;

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
