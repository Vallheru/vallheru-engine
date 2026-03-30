//! Admin and staff route trees.
//!
//! Provides route groups for the admin panel (admin-only), staff panel
//! (staff/admin/builder), staff list, bug reports, admin logs, and
//! member list.

use axum::{Router, middleware, routing};

use crate::handlers::{admin, admin_logs, bugreport, content, memberlist, moderation, staff};
use crate::middleware::guards::{Rank, require_admin, require_any_rank, require_authenticated};
use crate::state::AppState;

/// All admin and staff routes.
///
/// Structure:
/// - `/admin` — admin-only panel and sub-routes
/// - `/staff` — staff panel (Staff, Admin, Builder)
/// - `/staff/bugreport` — bug reports (Staff, Admin, Builder)
/// - `/staff/logs` — admin logs (Staff, Admin)
/// - `/staff/jail` — jail management (Staff, Admin)
/// - `/staff/chatban` — chat ban (Staff, Admin)
/// - `/staff/forumban` — forum ban (Staff, Admin)
/// - `/staff/mailban` — mail ban (Staff, Admin)
/// - `/staff/takeaway` — confiscate gold (Staff, Admin)
/// - `/staff/immunity` — immunity grant (Staff, Admin)
/// - `/judge` — judge panel (Judge rank only)
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
                .route(
                    "/staff/jail",
                    routing::get(moderation::staff_jail_form).post(moderation::staff_jail_action),
                )
                .route(
                    "/staff/chatban",
                    routing::get(moderation::staff_chat_ban)
                        .post(moderation::staff_chat_ban_action),
                )
                .route(
                    "/staff/forumban",
                    routing::get(moderation::staff_forum_ban)
                        .post(moderation::staff_forum_ban_action),
                )
                .route(
                    "/staff/mailban",
                    routing::get(moderation::staff_mail_ban)
                        .post(moderation::staff_mail_ban_action),
                )
                .route(
                    "/staff/takeaway",
                    routing::get(moderation::staff_takeaway_form)
                        .post(moderation::staff_takeaway_action),
                )
                .route(
                    "/staff/immunity",
                    routing::get(moderation::staff_immunity_form)
                        .post(moderation::staff_immunity_action),
                )
                .route("/staff/news", routing::get(content::pending_news_list))
                .route(
                    "/staff/news/{id}/edit",
                    routing::get(content::edit_pending_news_form)
                        .post(content::edit_pending_news_action),
                )
                .route(
                    "/staff/news/{id}/approve",
                    routing::post(content::approve_news_action),
                )
                .route(
                    "/staff/news/{id}/delete",
                    routing::post(content::delete_news_action),
                )
                .layer(middleware::from_fn(require_any_rank(&[
                    Rank::Staff,
                    Rank::Admin,
                    Rank::Builder,
                ]))),
        )
        // Judge panel — Judge rank only.
        .merge(
            Router::new()
                .route(
                    "/judge",
                    routing::get(moderation::judge_panel).post(moderation::judge_assign_rank),
                )
                .layer(middleware::from_fn(require_any_rank(&[Rank::Judge]))),
        )
        // Admin panel — admin only.
        .merge(
            Router::new()
                .route("/admin", routing::get(admin::admin_panel))
                .layer(middleware::from_fn(require_admin)),
        )
}
