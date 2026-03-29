//! Admin and staff route trees.
//!
//! Provides route groups for the admin panel (admin-only), staff panel
//! (staff/admin/builder), staff list, bug reports, admin logs, and
//! member list.

use axum::{Router, middleware, routing};

use crate::handlers::{admin, admin_logs, bugreport, memberlist, staff};
use crate::middleware::guards::{Rank, require_admin, require_any_rank, require_authenticated};
use crate::state::AppState;

/// All admin and staff routes.
///
/// Structure:
/// - `/admin` — admin-only panel and sub-routes
/// - `/staff` — staff panel (Staff, Admin, Builder)
/// - `/staff/bugreport` — bug reports (Staff, Admin, Builder)
/// - `/staff/logs` — admin logs (Staff, Admin)
/// - `/stafflist` — audience hall (any authenticated user)
/// - `/memberlist` — player list (any authenticated user)
pub fn routes() -> Router<AppState> {
    Router::new()
        // Public authenticated routes.
        .merge(
            Router::new()
                .route("/stafflist", routing::get(staff::staff_list))
                .route("/memberlist", routing::get(memberlist::member_list))
                .layer(middleware::from_fn(require_authenticated)),
        )
        // Staff panel and tools — accessible to Staff, Admin, and Builder.
        .merge(
            Router::new()
                .route("/staff", routing::get(staff::staff_panel))
                .route("/staff/bugreport", routing::get(bugreport::bugreport_list))
                .route(
                    "/staff/bugreport/{id}",
                    routing::get(bugreport::bugreport_detail).post(bugreport::bugreport_resolve),
                )
                .route("/staff/logs", routing::get(admin_logs::admin_logs))
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
