//! Admin and staff route trees.
//!
//! Provides route groups for the admin panel (admin-only), staff panel
//! (staff/admin/builder), and the staff list (any authenticated user).
//! Individual action routes will be added by MP-15-02 through MP-15-04.

use axum::{Router, middleware, routing};

use crate::handlers::{admin, staff};
use crate::middleware::guards::{Rank, require_admin, require_any_rank, require_authenticated};
use crate::state::AppState;

/// All admin and staff routes.
///
/// Structure:
/// - `/admin` — admin-only panel and sub-routes
/// - `/staff` — staff panel (Staff, Admin, Builder)
/// - `/stafflist` — audience hall (any authenticated user)
pub fn routes() -> Router<AppState> {
    Router::new()
        // Staff list is visible to all authenticated users.
        .merge(
            Router::new()
                .route("/stafflist", routing::get(staff::staff_list))
                .layer(middleware::from_fn(require_authenticated)),
        )
        // Staff panel — accessible to Staff, Admin, and Builder.
        .merge(
            Router::new()
                .route("/staff", routing::get(staff::staff_panel))
                .layer(middleware::from_fn(require_any_rank(&[
                    Rank::Staff,
                    Rank::Admin,
                    Rank::Builder,
                ]))),
        )
        // Admin panel — admin only.
        .merge(
            Router::new()
                .route("/admin", routing::get(admin::admin_panel))
                .layer(middleware::from_fn(require_admin)),
        )
}
