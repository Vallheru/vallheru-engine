//! Deity selection and change handler.
//!
//! Ported from `deity.php`.

use axum::{
    Extension, Form,
    extract::{Query, State},
    response::Response,
};

use crate::middleware::context::RequestContext;
use crate::page::{Flash, FlashKind, PageMeta};
use crate::state::AppState;
use vallheru_domain::player::mutations;
use vallheru_domain::temple;

// ---------------------------------------------------------------------------
// View models
// ---------------------------------------------------------------------------

#[derive(serde::Serialize)]
pub struct DeityView {
    #[serde(flatten)]
    pub base: crate::render::RenderContext,
    pub player_deity: String,
    /// Which sub-view: "select", "info", "change", "confirm", empty for index.
    pub section: String,
    pub deities: Vec<DeityOption>,
    /// Info about a specific deity (when section == "info").
    pub deity_info: Option<DeityDetail>,
    /// Cost to change deity.
    pub change_cost: i32,
}

#[derive(serde::Serialize)]
pub struct DeityOption {
    pub slug: String,
    pub name: String,
}

#[derive(serde::Serialize)]
pub struct DeityDetail {
    pub slug: String,
    pub name: String,
    pub description: String,
}

// ---------------------------------------------------------------------------
// Query params
// ---------------------------------------------------------------------------

#[derive(serde::Deserialize, Default)]
pub struct DeityQuery {
    pub deity: Option<String>,
    pub step: Option<String>,
}

#[derive(serde::Deserialize)]
pub struct DeityChangeForm {
    pub confirm: Option<String>,
}

// ---------------------------------------------------------------------------
// GET /deity
// ---------------------------------------------------------------------------

pub async fn deity_show(
    State(state): State<AppState>,
    Extension(ctx): Extension<RequestContext>,
    Query(query): Query<DeityQuery>,
) -> Response {
    let Some(ref user) = ctx.session_user else {
        return crate::page::redirect("/login");
    };

    #[allow(clippy::cast_possible_truncation)]
    let player_id = user.id as i32;

    let player_row = match load_player(&state, player_id).await {
        Ok(row) => row,
        Err(resp) => return resp,
    };

    let current_deity = player_row.deity.clone().unwrap_or_default();

    let deities: Vec<DeityOption> = temple::pantheon()
        .into_iter()
        .map(|d| DeityOption {
            slug: d.slug.to_owned(),
            name: d.name.to_owned(),
        })
        .collect();

    // Player already has a deity — show error if trying to select.
    if !current_deity.is_empty() && query.step.is_none() {
        return error_page(
            &state,
            &ctx,
            "Masz już wyznanie. Możesz je zmienić w świątyni.",
        );
    }

    // Show specific deity info.
    if let Some(ref slug) = query.deity {
        if !current_deity.is_empty() {
            return error_page(&state, &ctx, "Masz już wyznanie.");
        }

        let detail = temple::pantheon()
            .into_iter()
            .find(|d| d.slug == slug.as_str())
            .map(|d| DeityDetail {
                slug: d.slug.to_owned(),
                name: d.name.to_owned(),
                description: d.description.to_owned(),
            });

        if detail.is_none() {
            return error_page(&state, &ctx, "Nieznane bóstwo.");
        }

        let meta = PageMeta::titled("Wybierz wyznanie");
        let base = state.templates.build_context(&ctx, &meta);

        return state.templates.render_value(
            "deity.html",
            &DeityView {
                base,
                player_deity: current_deity,
                section: "info".to_owned(),
                deities,
                deity_info: detail,
                change_cost: 0,
            },
        );
    }

    // Change deity flow.
    if query.step.as_deref() == Some("change") {
        if current_deity.is_empty() {
            return error_page(&state, &ctx, "Nie masz wyznania do zmiany.");
        }

        let cost = mutations::deity_change_cost(player_row.change_deity);
        let meta = PageMeta::titled("Zmiana wyznania");
        let base = state.templates.build_context(&ctx, &meta);

        return state.templates.render_value(
            "deity.html",
            &DeityView {
                base,
                player_deity: current_deity,
                section: "change".to_owned(),
                deities,
                deity_info: None,
                change_cost: cost,
            },
        );
    }

    // Default: show selection list.
    let meta = PageMeta::titled("Wybierz wyznanie");
    let base = state.templates.build_context(&ctx, &meta);

    state.templates.render_value(
        "deity.html",
        &DeityView {
            base,
            player_deity: current_deity,
            section: "select".to_owned(),
            deities,
            deity_info: None,
            change_cost: 0,
        },
    )
}

// ---------------------------------------------------------------------------
// POST /deity/select/{slug}
// ---------------------------------------------------------------------------

pub async fn deity_select(
    State(state): State<AppState>,
    Extension(ctx): Extension<RequestContext>,
    axum::extract::Path(slug): axum::extract::Path<String>,
) -> Response {
    let Some(ref user) = ctx.session_user else {
        return crate::page::redirect("/login");
    };

    #[allow(clippy::cast_possible_truncation)]
    let player_id = user.id as i32;

    let player_row = match load_player(&state, player_id).await {
        Ok(row) => row,
        Err(resp) => return resp,
    };

    match mutations::select_deity(&player_row.deity, &slug) {
        Ok(db_name) => {
            if let Err(e) =
                vallheru_data::queries::locations::select_deity(&state.pool, player_id, &db_name)
                    .await
            {
                tracing::error!(error = %e, "select_deity DB failed");
                return server_error();
            }

            let meta = PageMeta::titled("Wyznanie wybrane")
                .with_flash(Flash::success(format!("Wybrałeś wyznanie: {db_name}.")));
            let base = state.templates.build_context(&ctx, &meta);

            state.templates.render_value(
                "deity.html",
                &DeityView {
                    base,
                    player_deity: db_name,
                    section: "confirm".to_owned(),
                    deities: vec![],
                    deity_info: None,
                    change_cost: 0,
                },
            )
        }
        Err(mutations::DeitySelectionError::AlreadyHasDeity) => {
            error_page(&state, &ctx, "Masz już wyznanie.")
        }
        Err(mutations::DeitySelectionError::InvalidDeity) => {
            error_page(&state, &ctx, "Nieznane bóstwo.")
        }
    }
}

// ---------------------------------------------------------------------------
// POST /deity/change
// ---------------------------------------------------------------------------

pub async fn deity_change(
    State(state): State<AppState>,
    Extension(ctx): Extension<RequestContext>,
    Form(_form): Form<DeityChangeForm>,
) -> Response {
    let Some(ref user) = ctx.session_user else {
        return crate::page::redirect("/login");
    };

    #[allow(clippy::cast_possible_truncation)]
    let player_id = user.id as i32;

    let player_row = match load_player(&state, player_id).await {
        Ok(row) => row,
        Err(resp) => return resp,
    };

    match mutations::validate_deity_change(
        &player_row.deity,
        player_row.change_deity,
        player_row.pw,
    ) {
        Ok(cost) => {
            if let Err(e) =
                vallheru_data::queries::locations::change_deity(&state.pool, player_id, cost).await
            {
                tracing::error!(error = %e, "change_deity DB failed");
                return server_error();
            }

            let meta = PageMeta::titled("Wyznanie zmienione").with_flash(Flash::success(
                "Zmieniłeś wyznanie. Możesz teraz wybrać nowe.".to_owned(),
            ));
            let base = state.templates.build_context(&ctx, &meta);

            state.templates.render_value(
                "deity.html",
                &DeityView {
                    base,
                    player_deity: String::new(),
                    section: "select".to_owned(),
                    deities: temple::pantheon()
                        .into_iter()
                        .map(|d| DeityOption {
                            slug: d.slug.to_owned(),
                            name: d.name.to_owned(),
                        })
                        .collect(),
                    deity_info: None,
                    change_cost: 0,
                },
            )
        }
        Err(mutations::DeityChangeError::NoDeityToChange) => {
            error_page(&state, &ctx, "Nie masz wyznania do zmiany.")
        }
        Err(mutations::DeityChangeError::InsufficientPw { cost }) => error_page(
            &state,
            &ctx,
            &format!("Potrzebujesz {cost} punktów wiary aby zmienić wyznanie."),
        ),
    }
}

// ---------------------------------------------------------------------------
// Helpers
// ---------------------------------------------------------------------------

async fn load_player(
    state: &AppState,
    player_id: i32,
) -> Result<vallheru_data::queries::player::PlayerRow, Response> {
    match vallheru_data::queries::player::find_player_by_id(&state.pool, player_id).await {
        Ok(Some(row)) => Ok(row),
        Ok(None) => Err(crate::page::redirect("/login")),
        Err(e) => {
            tracing::error!(error = %e, "deity: load_player failed");
            Err(server_error())
        }
    }
}

fn error_page(state: &AppState, ctx: &RequestContext, message: &str) -> Response {
    let meta = PageMeta::titled("Błąd").with_flash(Flash {
        kind: FlashKind::Error,
        message: message.to_owned(),
    });
    let base = state.templates.build_context(ctx, &meta);
    state.templates.render("error.html", &base)
}

fn server_error() -> Response {
    use axum::response::IntoResponse;
    (
        axum::http::StatusCode::INTERNAL_SERVER_ERROR,
        "Internal error",
    )
        .into_response()
}
