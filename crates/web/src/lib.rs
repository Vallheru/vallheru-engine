pub mod assets;
pub mod email;
pub mod handlers;
pub mod i18n;
pub mod middleware;
pub mod page;
pub mod render;
pub mod routes;
pub mod state;

pub use email::{EmailConfig, EmailService};
pub use i18n::{Catalog, CatalogError};

/// Log-and-discard helper for "fire-and-forget" async DB operations.
///
/// Usage: `log_err!(expr, "context message")`
///
/// If `expr` evaluates to `Err(e)`, logs an error with the message and error.
/// Does not halt execution — same semantics as `let _ =` but observable.
#[macro_export]
macro_rules! log_err {
    ($expr:expr, $msg:literal) => {
        if let Err(__e) = $expr {
            tracing::error!(error = %__e, $msg);
        }
    };
    ($expr:expr, $($arg:tt)+) => {
        if let Err(__e) = $expr {
            tracing::error!(error = %__e, $($arg)+);
        }
    };
}
pub use middleware::context::{ContextDefaults, RequestContext, SessionUser};
pub use middleware::guards::{
    Rank, require_admin, require_any_rank, require_authenticated, require_staff,
};
pub use page::{Flash, FlashKind, PageMeta, redirect, redirect_after_post, safe_back_or};
pub use render::{RenderContext, TemplateEngine, TemplateEngineConfig, theme_base_template};
pub use routes::build_router;
pub use state::{AppState, PostRateLimiter};
