//! Application state shared across all Axum handlers.

use std::collections::HashMap;
use std::sync::{Arc, Mutex};
use std::time::Instant;

use vallheru_data::pool::PgPool;

use crate::i18n::Catalog;
use crate::middleware::context::ContextDefaults;
use crate::render::TemplateEngine;

/// Simple per-user cooldown tracker for post rate limiting.
///
/// Stores the last post [`Instant`] per user-ID. Entries are pruned on each
/// `check_and_record` call to avoid unbounded growth.
#[derive(Clone, Default)]
pub struct PostRateLimiter {
    inner: Arc<Mutex<HashMap<i64, Instant>>>,
}

impl PostRateLimiter {
    const COOLDOWN_SECS: u64 = 10;

    /// Returns `true` if the user is allowed to post (cooldown has elapsed or
    /// this is their first post). Records the current instant on success.
    pub fn check_and_record(&self, user_id: i64) -> bool {
        let mut map = self.inner.lock().expect("rate limiter lock poisoned");

        // Prune stale entries (older than 2× cooldown) to prevent growth.
        let cutoff = Instant::now()
            .checked_sub(std::time::Duration::from_secs(Self::COOLDOWN_SECS * 2))
            .unwrap_or_else(Instant::now);
        map.retain(|_, ts| *ts > cutoff);

        let now = Instant::now();
        if let Some(last) = map.get(&user_id) {
            if now.duration_since(*last).as_secs() < Self::COOLDOWN_SECS {
                return false;
            }
        }
        map.insert(user_id, now);
        true
    }
}

/// Shared application state available to all Axum handlers.
#[derive(Clone)]
pub struct AppState {
    pub pool: PgPool,
    /// Defaults used by the request-context middleware.
    pub context_defaults: ContextDefaults,
    /// Template rendering engine.
    pub templates: TemplateEngine,
    /// Localization catalog for the active locale.
    pub catalog: Catalog,
    /// Server-side rate limiter for forum/tribe-forum posts.
    pub post_rate_limiter: PostRateLimiter,
}

#[cfg(test)]
mod tests {
    use super::*;

    #[test]
    fn rate_limiter_allows_first_post() {
        let rl = PostRateLimiter::default();
        assert!(rl.check_and_record(1));
    }

    #[test]
    fn rate_limiter_blocks_rapid_second_post() {
        let rl = PostRateLimiter::default();
        assert!(rl.check_and_record(1));
        assert!(!rl.check_and_record(1));
    }

    #[test]
    fn rate_limiter_allows_different_users() {
        let rl = PostRateLimiter::default();
        assert!(rl.check_and_record(1));
        assert!(rl.check_and_record(2));
    }
}
