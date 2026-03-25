pub mod assets;
pub mod handlers;
pub mod i18n;
pub mod middleware;
pub mod page;
pub mod render;
pub mod routes;
pub mod state;

pub use i18n::{Catalog, CatalogError};
pub use middleware::context::{ContextDefaults, RequestContext, SessionUser};
pub use middleware::guards::{
    Rank, require_admin, require_any_rank, require_authenticated, require_staff,
};
pub use page::{Flash, FlashKind, PageMeta, redirect, redirect_after_post, safe_back_or};
pub use render::{RenderContext, TemplateEngine, TemplateEngineConfig, theme_base_template};
pub use routes::build_router;
pub use state::AppState;
