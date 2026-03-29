//! Admin panel handler.
//!
//! Ported from `admin.php`. Shows the admin dashboard with navigation
//! links to admin sub-sections. Individual admin action handlers will
//! be added by later tasks (MP-15-02 through MP-15-04).

use axum::{Extension, extract::State, response::Response};

use crate::middleware::context::RequestContext;
use crate::page::PageMeta;
use crate::state::AppState;

/// A navigation link in the admin panel menu.
#[derive(serde::Serialize)]
pub struct AdminLink {
    pub href: &'static str,
    pub label: &'static str,
}

/// A group of admin links under a section heading.
#[derive(serde::Serialize)]
pub struct AdminSection {
    pub title: &'static str,
    pub links: Vec<AdminLink>,
}

/// View model for the admin panel template.
#[derive(serde::Serialize)]
pub struct AdminView {
    #[serde(flatten)]
    pub base: crate::render::RenderContext,
    pub sections: Vec<AdminSection>,
}

/// GET /admin — admin panel dashboard.
pub async fn admin_panel(
    State(state): State<AppState>,
    Extension(ctx): Extension<RequestContext>,
) -> Response {
    let meta = PageMeta::titled("Panel Administracyjny").with_back_link("/city", "Wróć do miasta");
    let base = state.templates.build_context(&ctx, &meta);

    let sections = build_admin_sections();

    let view = AdminView { base, sections };
    state.templates.render_value("admin.html", &view)
}

/// Build the admin panel menu sections.
///
/// Menu structure mirrors the PHP `admin.tpl` sidebar groups.
fn build_admin_sections() -> Vec<AdminSection> {
    vec![
        text_section(),
        moderation_section(),
        management_section(),
        player_management_section(),
        game_data_section(),
    ]
}

fn text_section() -> AdminSection {
    AdminSection {
        title: "Teksty gry",
        links: vec![
            AdminLink {
                href: "/admin/news",
                label: "Dodaj plotkę",
            },
            AdminLink {
                href: "/admin/updates",
                label: "Dodaj aktualizację",
            },
            AdminLink {
                href: "/admin/addtext",
                label: "Edytuj teksty",
            },
        ],
    }
}

fn moderation_section() -> AdminSection {
    AdminSection {
        title: "Moderacja",
        links: vec![
            AdminLink {
                href: "/staff/takeaway",
                label: "Zabierz item",
            },
            AdminLink {
                href: "/staff/clearc",
                label: "Wyczyść potyczki",
            },
            AdminLink {
                href: "/staff/chat-admin",
                label: "Zarządzanie czatem",
            },
            AdminLink {
                href: "/staff/forum-ban",
                label: "Zablokuj forum",
            },
            AdminLink {
                href: "/staff/tags",
                label: "Tagi graczy",
            },
            AdminLink {
                href: "/staff/jail",
                label: "Więzienie",
            },
            AdminLink {
                href: "/staff/banmail",
                label: "Zablokuj pocztę",
            },
            AdminLink {
                href: "/staff/innarchive",
                label: "Archiwum karczmy",
            },
            AdminLink {
                href: "/staff/logs",
                label: "Logi graczy",
            },
            AdminLink {
                href: "/staff/bugreport",
                label: "Zgłoszone błędy",
            },
        ],
    }
}

fn management_section() -> AdminSection {
    AdminSection {
        title: "Zarządzanie grą",
        links: vec![
            AdminLink {
                href: "/admin/vallars",
                label: "Vallary",
            },
            AdminLink {
                href: "/admin/changelog",
                label: "Dziennik zmian",
            },
            AdminLink {
                href: "/admin/meta",
                label: "Ustawienia gry",
            },
            AdminLink {
                href: "/admin/forums",
                label: "Zarządzanie forum",
            },
            AdminLink {
                href: "/admin/register",
                label: "Rejestracja",
            },
        ],
    }
}

fn player_management_section() -> AdminSection {
    AdminSection {
        title: "Zarządzanie graczami",
        links: vec![
            AdminLink {
                href: "/admin/ban",
                label: "Zbanuj gracza",
            },
            AdminLink {
                href: "/admin/del",
                label: "Zarządzaj graczem",
            },
            AdminLink {
                href: "/admin/srank",
                label: "Ustaw rangę",
            },
            AdminLink {
                href: "/admin/mail",
                label: "Wyślij masowy mail",
            },
        ],
    }
}

fn game_data_section() -> AdminSection {
    AdminSection {
        title: "Dane gry",
        links: vec![
            AdminLink {
                href: "/admin/monsters",
                label: "Potwory",
            },
            AdminLink {
                href: "/admin/equipment",
                label: "Ekwipunek",
            },
            AdminLink {
                href: "/admin/smith",
                label: "Kowal",
            },
            AdminLink {
                href: "/admin/spells",
                label: "Czary",
            },
            AdminLink {
                href: "/admin/potions",
                label: "Mikstury",
            },
            AdminLink {
                href: "/admin/portals",
                label: "Portale",
            },
        ],
    }
}
