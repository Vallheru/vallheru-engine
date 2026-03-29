//! Staff panel and staff list handlers.
//!
//! Ported from `staff.php` and `stafflist.php`. The staff panel shows
//! a menu of moderation tools available to Staff, Admin, and Builder
//! ranks. The staff list (audience hall) shows all players holding
//! special ranks, grouped by section.

use axum::{Extension, extract::State, response::IntoResponse, response::Response};

use crate::middleware::context::RequestContext;
use crate::middleware::guards::Rank;
use crate::page::PageMeta;
use crate::state::AppState;

// ---------------------------------------------------------------------------
// Staff panel
// ---------------------------------------------------------------------------

/// A navigation link in the staff panel.
#[derive(serde::Serialize)]
pub struct StaffLink {
    pub href: &'static str,
    pub label: &'static str,
}

/// View model for the staff panel template.
#[derive(serde::Serialize)]
pub struct StaffView {
    #[serde(flatten)]
    pub base: crate::render::RenderContext,
    pub links: Vec<StaffLink>,
}

/// GET /staff — staff panel menu.
pub async fn staff_panel(
    State(state): State<AppState>,
    Extension(ctx): Extension<RequestContext>,
) -> Response {
    let meta = PageMeta::titled("Panel Administracyjny").with_back_link("/city", "Wróć do miasta");
    let base = state.templates.build_context(&ctx, &meta);

    let rank = ctx
        .session_user
        .as_ref()
        .map_or(Rank::Player, |u| Rank::from_db(&u.rank));

    let links = build_staff_links(&rank);

    let view = StaffView { base, links };
    state.templates.render_value("staff.html", &view)
}

/// Build the staff panel links based on the operator's rank.
///
/// Builders only see bug reports. Staff and Admin see the full set.
fn build_staff_links(rank: &Rank) -> Vec<StaffLink> {
    if *rank == Rank::Builder {
        return vec![
            StaffLink {
                href: "/bugtrack",
                label: "Bugtrack",
            },
            StaffLink {
                href: "/staff/bugreport",
                label: "Zgłoszone błędy",
            },
        ];
    }

    vec![
        StaffLink {
            href: "/admin/news",
            label: "Dodaj plotkę",
        },
        StaffLink {
            href: "/staff/takeaway",
            label: "Zabierz item",
        },
        StaffLink {
            href: "/staff/clearc",
            label: "Wyczyść potyczki",
        },
        StaffLink {
            href: "/staff/chat-admin",
            label: "Zarządzanie czatem",
        },
        StaffLink {
            href: "/staff/forum-ban",
            label: "Zablokuj forum",
        },
        StaffLink {
            href: "/staff/tags",
            label: "Tagi graczy",
        },
        StaffLink {
            href: "/staff/jail",
            label: "Więzienie",
        },
        StaffLink {
            href: "/staff/addtext",
            label: "Edytuj teksty",
        },
        StaffLink {
            href: "/staff/innarchive",
            label: "Archiwum karczmy",
        },
        StaffLink {
            href: "/staff/logs",
            label: "Logi graczy",
        },
        StaffLink {
            href: "/staff/banmail",
            label: "Zablokuj pocztę",
        },
        StaffLink {
            href: "/bugtrack",
            label: "Bugtrack",
        },
        StaffLink {
            href: "/staff/bugreport",
            label: "Zgłoszone błędy",
        },
    ]
}

// ---------------------------------------------------------------------------
// Staff list (Audience Hall)
// ---------------------------------------------------------------------------

/// A staff member entry for the list.
#[derive(serde::Serialize)]
pub struct StaffMember {
    pub id: i32,
    pub name: String,
}

/// A section in the staff list grouped by rank category.
#[derive(serde::Serialize)]
pub struct StaffSection {
    pub title: &'static str,
    pub groups: Vec<StaffGroup>,
}

/// A rank group within a section (e.g., "Władcy" with list of admins).
#[derive(serde::Serialize)]
pub struct StaffGroup {
    pub rank_label: &'static str,
    pub members: Vec<StaffMember>,
}

/// View model for the staff list template.
#[derive(serde::Serialize)]
pub struct StaffListView {
    #[serde(flatten)]
    pub base: crate::render::RenderContext,
    pub info: &'static str,
    pub sections: Vec<StaffSection>,
}

/// Ranks that appear on the staff list page.
const STAFF_RANKS: &[&str] = &[
    "Admin",
    "Staff",
    "Karczmarka",
    "Bibliotekarz",
    "Marszałek Rady",
    "Poseł",
    "Redaktor",
    "Rycerz",
    "Dama",
];

/// GET /stafflist — audience hall page listing all staff members.
pub async fn staff_list(
    State(state): State<AppState>,
    Extension(ctx): Extension<RequestContext>,
) -> Response {
    let meta = PageMeta::titled("Sala audiencyjna").with_back_link("/city", "Wróć do miasta");
    let base = state.templates.build_context(&ctx, &meta);

    let rows =
        match vallheru_data::queries::admin::list_staff_members(&state.pool, STAFF_RANKS).await {
            Ok(r) => r,
            Err(e) => {
                tracing::error!(error = %e, "failed to load staff list");
                return (
                    axum::http::StatusCode::INTERNAL_SERVER_ERROR,
                    "Błąd bazy danych",
                )
                    .into_response();
            }
        };

    let sections = build_staff_sections(&rows);
    let view = StaffListView {
        base,
        info: "Poniżej znajdziesz listę mieszkańców pełniących jakieś funkcje.",
        sections,
    };
    state.templates.render_value("stafflist.html", &view)
}

/// Group staff rows into the display sections used by the template.
fn build_staff_sections(rows: &[vallheru_data::queries::admin::StaffRow]) -> Vec<StaffSection> {
    /// Helper to extract members matching a given rank.
    fn members_for(
        rows: &[vallheru_data::queries::admin::StaffRow],
        rank: &str,
    ) -> Vec<StaffMember> {
        rows.iter()
            .filter(|r| r.rank == rank)
            .map(|r| StaffMember {
                id: r.id,
                name: r.username.clone(),
            })
            .collect()
    }

    vec![
        StaffSection {
            title: "Funkcje porządkowe",
            groups: vec![
                StaffGroup {
                    rank_label: "Władcy",
                    members: members_for(rows, "Admin"),
                },
                StaffGroup {
                    rank_label: "Książęta",
                    members: members_for(rows, "Staff"),
                },
                StaffGroup {
                    rank_label: "Karczmarze",
                    members: members_for(rows, "Karczmarka"),
                },
                StaffGroup {
                    rank_label: "Bibliotekarze",
                    members: members_for(rows, "Bibliotekarz"),
                },
            ],
        },
        StaffSection {
            title: "Rada Królewska",
            groups: vec![
                StaffGroup {
                    rank_label: "Marszałkowie Rady",
                    members: members_for(rows, "Marszałek Rady"),
                },
                StaffGroup {
                    rank_label: "Radni",
                    members: members_for(rows, "Poseł"),
                },
            ],
        },
        StaffSection {
            title: "Gazeta",
            groups: vec![StaffGroup {
                rank_label: "Redaktorzy",
                members: members_for(rows, "Redaktor"),
            }],
        },
        StaffSection {
            title: "Zaszczytne rangi",
            groups: vec![
                StaffGroup {
                    rank_label: "Rycerze",
                    members: members_for(rows, "Rycerz"),
                },
                StaffGroup {
                    rank_label: "Damy",
                    members: members_for(rows, "Dama"),
                },
            ],
        },
    ]
}
