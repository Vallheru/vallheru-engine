//! Handlers for personal notes, library, roleplay profiles, and chronicle.

use axum::extract::{Path, Query, State};
use axum::response::{IntoResponse, Redirect, Response};
use axum::{Extension, Form};

use vallheru_data::queries::pages as pq;
use vallheru_domain::social::pages as pages_domain;
use vallheru_domain::text;

use crate::middleware::context::RequestContext;
use crate::page::PageMeta;
use crate::state::AppState;

// =========================================================================
// View models
// =========================================================================

#[derive(serde::Serialize)]
pub struct NotesView {
    #[serde(flatten)]
    pub base: crate::render::RenderContext,
    pub notes: Vec<pq::NoteRow>,
    pub page: i64,
    pub total_pages: i64,
}

#[derive(serde::Serialize)]
pub struct NoteFormView {
    #[serde(flatten)]
    pub base: crate::render::RenderContext,
    pub edit_id: Option<i64>,
    pub edit_title: String,
    pub edit_body: String,
}

#[derive(serde::Serialize)]
pub struct LibraryIndexView {
    #[serde(flatten)]
    pub base: crate::render::RenderContext,
    pub tale_count: i64,
    pub poetry_count: i64,
    pub pending_count: i64,
    pub can_manage: bool,
}

#[derive(serde::Serialize)]
pub struct LibraryListView {
    #[serde(flatten)]
    pub base: crate::render::RenderContext,
    pub text_type: String,
    pub type_label: String,
    pub sort: String,
    pub texts: Vec<pq::LibraryListRow>,
    pub authors: Vec<pq::LibraryAuthorRow>,
    pub can_manage: bool,
}

#[derive(serde::Serialize)]
pub struct LibraryTextView {
    #[serde(flatten)]
    pub base: crate::render::RenderContext,
    pub text: LibraryTextDetail,
    pub comment_count: i64,
    pub can_manage: bool,
}

#[derive(serde::Serialize)]
pub struct LibraryTextDetail {
    pub id: i64,
    pub title: String,
    pub body: String,
    pub author_name: String,
    pub author_id: i64,
}

#[derive(serde::Serialize)]
pub struct LibraryAddView {
    #[serde(flatten)]
    pub base: crate::render::RenderContext,
    pub type_options: Vec<TypeOption>,
}

#[derive(serde::Serialize)]
pub struct TypeOption {
    pub code: String,
    pub label: String,
}

#[derive(serde::Serialize)]
pub struct LibraryAdminView {
    #[serde(flatten)]
    pub base: crate::render::RenderContext,
    pub pending: Vec<pq::LibraryListRow>,
}

#[derive(serde::Serialize)]
pub struct LibraryAdminEditView {
    #[serde(flatten)]
    pub base: crate::render::RenderContext,
    pub text_id: i64,
    pub text_title: String,
    pub text_body: String,
    pub text_type: String,
    pub type_options: Vec<TypeOption>,
}

#[derive(serde::Serialize)]
pub struct RoleplayView {
    #[serde(flatten)]
    pub base: crate::render::RenderContext,
    pub player_name: String,
    pub roleplay: String,
    pub ooc: String,
    pub prev_id: Option<i64>,
    pub next_id: Option<i64>,
}

#[derive(serde::Serialize)]
pub struct ChronicleView {
    #[serde(flatten)]
    pub base: crate::render::RenderContext,
    pub missions: Vec<ChronicleItem>,
    pub location: String,
}

#[derive(serde::Serialize)]
pub struct ChronicleItem {
    pub id: i64,
    pub name: String,
    pub mission_type: String,
    pub short_desc: String,
}

#[derive(serde::Serialize)]
pub struct ChronicleMissionView {
    #[serde(flatten)]
    pub base: crate::render::RenderContext,
    pub mission: ChronicleMissionDetail,
}

#[derive(serde::Serialize)]
pub struct ChronicleMissionDetail {
    pub id: i64,
    pub name: String,
    pub intro: String,
    pub mission_type: String,
}

// =========================================================================
// Query / form types
// =========================================================================

fn default_page() -> i64 {
    1
}

#[derive(serde::Deserialize)]
pub struct PageQuery {
    #[serde(default = "default_page")]
    pub page: i64,
}

#[derive(serde::Deserialize)]
pub struct NoteForm {
    #[serde(default)]
    pub title: String,
    #[serde(default)]
    pub body: String,
}

#[derive(serde::Deserialize)]
pub struct LibraryAddForm {
    #[serde(default)]
    pub title: String,
    #[serde(default)]
    pub body: String,
    #[serde(default)]
    pub text_type: String,
}

#[derive(serde::Deserialize)]
pub struct LibrarySortQuery {
    #[serde(default)]
    pub sort: String,
    #[serde(default)]
    pub author_id: Option<i64>,
}

#[derive(serde::Deserialize)]
pub struct ChronicleLocationQuery {
    #[serde(default = "default_location")]
    pub location: String,
}

fn default_location() -> String {
    "Altara".to_string()
}

// =========================================================================
// Handlers — Notes (personal notebook)
// =========================================================================

/// GET /notes — paginated list of personal notes.
pub async fn notes_page(
    State(app): State<AppState>,
    Extension(ctx): Extension<RequestContext>,
    Query(q): Query<PageQuery>,
) -> Response {
    let Some(ref user) = ctx.session_user else {
        return Redirect::to("/").into_response();
    };

    let total = match pq::count_notes(&app.pool, user.id).await {
        Ok(v) => v,
        Err(e) => {
            tracing::error!(user_id = user.id, error = ?e, "failed to count notes");
            0
        }
    };
    let per_page = pages_domain::NOTES_PER_PAGE;
    #[allow(clippy::cast_possible_truncation, clippy::cast_precision_loss)]
    let total_pages = (total as f64 / per_page as f64).ceil() as i64;
    let page = q.page.clamp(1, total_pages.max(1));
    let offset = (page - 1) * per_page;

    let notes = pq::list_notes(&app.pool, user.id, per_page, offset)
        .await
        .unwrap_or_default();

    let meta = PageMeta::titled("Notatnik");
    let base = app.templates.build_context(&ctx, &meta);
    let view = NotesView {
        base,
        notes,
        page,
        total_pages,
    };
    app.templates.render_value("notes.html", &view)
}

/// GET /notes/add — form for a new or editing an existing note.
pub async fn note_form(
    State(app): State<AppState>,
    Extension(ctx): Extension<RequestContext>,
    Query(q): Query<NoteEditQuery>,
) -> Response {
    let Some(ref user) = ctx.session_user else {
        return Redirect::to("/").into_response();
    };

    let (edit_id, edit_title, edit_body) = if let Some(id) = q.edit {
        match pq::get_note(&app.pool, id, user.id).await {
            Ok(Some(n)) => (Some(n.id), n.title, n.body),
            _ => (None, String::new(), String::new()),
        }
    } else {
        (None, String::new(), String::new())
    };

    let meta = PageMeta::titled("Notatnik").with_js("/js/editor.js");
    let base = app.templates.build_context(&ctx, &meta);
    let view = NoteFormView {
        base,
        edit_id,
        edit_title,
        edit_body,
    };
    app.templates.render_value("note_form.html", &view)
}

#[derive(serde::Deserialize)]
pub struct NoteEditQuery {
    #[serde(default)]
    pub edit: Option<i64>,
}

/// POST /notes/add — create or update a note.
pub async fn note_save(
    State(app): State<AppState>,
    Extension(ctx): Extension<RequestContext>,
    Query(q): Query<NoteEditQuery>,
    Form(form): Form<NoteForm>,
) -> Response {
    let Some(ref user) = ctx.session_user else {
        return Redirect::to("/").into_response();
    };

    let title = form.title.trim();
    let body_raw = form.body.trim();

    if !title.is_empty() && !body_raw.is_empty() {
        let body = text::bbcode_to_html(body_raw, &[], false);
        if let Some(id) = q.edit {
            if let Err(e) = pq::update_note(&app.pool, id, user.id, title, &body).await {
                tracing::error!(error = %e, "Failed to update note");
            }
        } else if let Err(e) = pq::insert_note(&app.pool, user.id, title, &body).await {
            tracing::error!(error = %e, "Failed to insert note");
        }
    }

    Redirect::to("/notes").into_response()
}

/// POST /notes/delete/{id} — delete a note.
pub async fn note_delete(
    State(app): State<AppState>,
    Extension(ctx): Extension<RequestContext>,
    Path(id): Path<i64>,
) -> Response {
    let Some(ref user) = ctx.session_user else {
        return Redirect::to("/").into_response();
    };

    if let Err(e) = pq::delete_note(&app.pool, id, user.id).await {
        tracing::error!(error = %e, "Failed to delete note");
    }
    Redirect::to("/notes").into_response()
}

// =========================================================================
// Handlers — Library
// =========================================================================

fn library_type_options() -> Vec<TypeOption> {
    vec![
        TypeOption {
            code: "tale".to_string(),
            label: "Opowieść".to_string(),
        },
        TypeOption {
            code: "poetry".to_string(),
            label: "Poezja".to_string(),
        },
    ]
}

/// GET /library — index showing counts and links.
pub async fn library_index(
    State(app): State<AppState>,
    Extension(ctx): Extension<RequestContext>,
) -> Response {
    let Some(ref user) = ctx.session_user else {
        return Redirect::to("/").into_response();
    };

    let lang = "pl";
    let tale_count = match pq::count_library_texts(&app.pool, "tale", true, lang).await {
        Ok(v) => v,
        Err(e) => {
            tracing::error!(error = ?e, "failed to count tales");
            0
        }
    };
    let poetry_count = match pq::count_library_texts(&app.pool, "poetry", true, lang).await {
        Ok(v) => v,
        Err(e) => {
            tracing::error!(error = ?e, "failed to count poetry");
            0
        }
    };
    let can_manage = pages_domain::can_manage_library(&user.rank);
    let pending_count = if can_manage {
        match pq::count_pending_library(&app.pool, lang).await {
            Ok(v) => v,
            Err(e) => {
                tracing::error!(error = ?e, "failed to count pending library texts");
                0
            }
        }
    } else {
        0
    };

    let meta = PageMeta::titled("Biblioteka");
    let base = app.templates.build_context(&ctx, &meta);
    let view = LibraryIndexView {
        base,
        tale_count,
        poetry_count,
        pending_count,
        can_manage,
    };
    app.templates.render_value("library.html", &view)
}

/// GET `/library/{text_type}` — list texts by type with sorting.
pub async fn library_list(
    State(app): State<AppState>,
    Extension(ctx): Extension<RequestContext>,
    Path(text_type): Path<String>,
    Query(q): Query<LibrarySortQuery>,
) -> Response {
    let Some(ref user) = ctx.session_user else {
        return Redirect::to("/").into_response();
    };

    let tt = pages_domain::LibraryTextType::parse(&text_type);
    let type_code = tt.as_str();
    let lang = "pl";
    let sort = pages_domain::LibrarySort::parse(&q.sort);

    let texts = if let Some(author_id) = q.author_id {
        pq::list_library_by_author(&app.pool, type_code, author_id, lang)
            .await
            .unwrap_or_default()
    } else {
        match sort {
            pages_domain::LibrarySort::Author => Vec::new(), // show authors list instead
            pages_domain::LibrarySort::Title => {
                pq::list_library_by_title(&app.pool, type_code, lang)
                    .await
                    .unwrap_or_default()
            }
            pages_domain::LibrarySort::Date => pq::list_library_by_date(&app.pool, type_code, lang)
                .await
                .unwrap_or_default(),
        }
    };

    let authors = if matches!(sort, pages_domain::LibrarySort::Author) && q.author_id.is_none() {
        pq::list_library_authors(&app.pool, type_code, lang)
            .await
            .unwrap_or_default()
    } else {
        Vec::new()
    };

    let can_manage = pages_domain::can_manage_library(&user.rank);

    let meta = PageMeta::titled(tt.label());
    let base = app.templates.build_context(&ctx, &meta);
    let view = LibraryListView {
        base,
        text_type: type_code.to_string(),
        type_label: tt.label().to_string(),
        sort: sort.as_str().to_string(),
        texts,
        authors,
        can_manage,
    };
    app.templates.render_value("library_list.html", &view)
}

/// GET /library/text/{id} — read a single text.
pub async fn library_text(
    State(app): State<AppState>,
    Extension(ctx): Extension<RequestContext>,
    Path(id): Path<i64>,
) -> Response {
    let Some(ref user) = ctx.session_user else {
        return Redirect::to("/").into_response();
    };

    let Some(row) = (match pq::get_library_text(&app.pool, id).await {
        Ok(v) => v,
        Err(e) => {
            tracing::error!(text_id = id, error = ?e, "failed to load library text");
            None
        }
    }) else {
        return Redirect::to("/library").into_response();
    };

    let can_manage = pages_domain::can_manage_library(&user.rank);

    // Non-admins may only view approved texts.
    if !row.is_approved && !can_manage {
        return Redirect::to("/library").into_response();
    }

    let comment_count = vallheru_data::queries::content::count_comments(&app.pool, "library", id)
        .await
        .unwrap_or(0);

    let meta = PageMeta::titled(&row.title);
    let base = app.templates.build_context(&ctx, &meta);
    let view = LibraryTextView {
        base,
        text: LibraryTextDetail {
            id: row.id,
            title: row.title,
            body: row.body,
            author_name: row.author_name,
            author_id: row.author_id,
        },
        comment_count,
        can_manage,
    };
    app.templates.render_value("library_text.html", &view)
}

/// GET /library/add — form to submit a new text.
pub async fn library_add_form(
    State(app): State<AppState>,
    Extension(ctx): Extension<RequestContext>,
) -> Response {
    let Some(ref _user) = ctx.session_user else {
        return Redirect::to("/").into_response();
    };

    let meta = PageMeta::titled("Dodaj tekst").with_js("/js/editor.js");
    let base = app.templates.build_context(&ctx, &meta);
    let view = LibraryAddView {
        base,
        type_options: library_type_options(),
    };
    app.templates.render_value("library_add.html", &view)
}

/// POST /library/add — submit a new text.
pub async fn library_add_action(
    State(app): State<AppState>,
    Extension(ctx): Extension<RequestContext>,
    Form(form): Form<LibraryAddForm>,
) -> Response {
    let Some(ref user) = ctx.session_user else {
        return Redirect::to("/").into_response();
    };

    let title = form.title.trim();
    let body_raw = form.body.trim();
    let tt = pages_domain::LibraryTextType::parse(&form.text_type);

    if !title.is_empty() && !body_raw.is_empty() {
        let body = text::bbcode_to_html(body_raw, &[], false);
        if let Err(e) = pq::insert_library_text(
            &app.pool,
            title,
            &body,
            &user.name,
            user.id,
            tt.as_str(),
            "pl",
        )
        .await
        {
            tracing::error!(error = %e, "Failed to insert library text");
        }
    }

    Redirect::to("/library").into_response()
}

/// GET /library/admin — moderation panel for pending texts.
pub async fn library_admin(
    State(app): State<AppState>,
    Extension(ctx): Extension<RequestContext>,
) -> Response {
    let Some(ref user) = ctx.session_user else {
        return Redirect::to("/").into_response();
    };

    if !pages_domain::can_manage_library(&user.rank) {
        return Redirect::to("/library").into_response();
    }

    let pending = pq::list_pending_library(&app.pool, "pl")
        .await
        .unwrap_or_default();

    let meta = PageMeta::titled("Moderacja biblioteki");
    let base = app.templates.build_context(&ctx, &meta);
    let view = LibraryAdminView { base, pending };
    app.templates.render_value("library_admin.html", &view)
}

/// GET /library/admin/edit/{id} — edit a pending text.
pub async fn library_admin_edit(
    State(app): State<AppState>,
    Extension(ctx): Extension<RequestContext>,
    Path(id): Path<i64>,
) -> Response {
    let Some(ref user) = ctx.session_user else {
        return Redirect::to("/").into_response();
    };

    if !pages_domain::can_manage_library(&user.rank) {
        return Redirect::to("/library").into_response();
    }

    let Some(row) = (match pq::get_library_text(&app.pool, id).await {
        Ok(v) => v,
        Err(e) => {
            tracing::error!(text_id = id, error = ?e, "failed to load library text for edit");
            None
        }
    }) else {
        return Redirect::to("/library/admin").into_response();
    };

    let meta = PageMeta::titled("Edytuj tekst").with_js("/js/editor.js");
    let base = app.templates.build_context(&ctx, &meta);
    let view = LibraryAdminEditView {
        base,
        text_id: row.id,
        text_title: row.title,
        text_body: row.body,
        text_type: row.text_type,
        type_options: library_type_options(),
    };
    app.templates.render_value("library_admin_edit.html", &view)
}

/// POST /library/admin/edit/{id} — save edits.
pub async fn library_admin_edit_action(
    State(app): State<AppState>,
    Extension(ctx): Extension<RequestContext>,
    Path(id): Path<i64>,
    Form(form): Form<LibraryAddForm>,
) -> Response {
    let Some(ref user) = ctx.session_user else {
        return Redirect::to("/").into_response();
    };

    if !pages_domain::can_manage_library(&user.rank) {
        return Redirect::to("/library").into_response();
    }

    let title = form.title.trim();
    let body_raw = form.body.trim();
    let tt = pages_domain::LibraryTextType::parse(&form.text_type);

    if !title.is_empty() && !body_raw.is_empty() {
        let body = text::bbcode_to_html(body_raw, &[], false);
        if let Err(e) = pq::update_library_text(&app.pool, id, title, &body, tt.as_str()).await {
            tracing::error!(error = %e, "Failed to update library text");
        }
    }

    Redirect::to("/library/admin").into_response()
}

/// POST /library/admin/approve/{id} — approve a text.
pub async fn library_admin_approve(
    State(app): State<AppState>,
    Extension(ctx): Extension<RequestContext>,
    Path(id): Path<i64>,
) -> Response {
    let Some(ref user) = ctx.session_user else {
        return Redirect::to("/").into_response();
    };

    if !pages_domain::can_manage_library(&user.rank) {
        return Redirect::to("/library").into_response();
    }

    if let Err(e) = pq::approve_library_text(&app.pool, id).await {
        tracing::error!(error = %e, "Failed to approve library text");
    }
    Redirect::to("/library/admin").into_response()
}

/// POST /library/admin/delete/{id} — delete a text.
pub async fn library_admin_delete(
    State(app): State<AppState>,
    Extension(ctx): Extension<RequestContext>,
    Path(id): Path<i64>,
) -> Response {
    let Some(ref user) = ctx.session_user else {
        return Redirect::to("/").into_response();
    };

    if !pages_domain::can_manage_library(&user.rank) {
        return Redirect::to("/library").into_response();
    }

    if let Err(e) = pq::delete_library_text(&app.pool, id).await {
        tracing::error!(error = %e, "Failed to delete library text");
    }
    Redirect::to("/library/admin").into_response()
}

// =========================================================================
// Handlers — Roleplay
// =========================================================================

/// GET /roleplay/{id} — view a player's roleplay profile.
pub async fn roleplay_view(
    State(app): State<AppState>,
    Extension(ctx): Extension<RequestContext>,
    Path(id): Path<i64>,
) -> Response {
    let Some(ref _user) = ctx.session_user else {
        return Redirect::to("/").into_response();
    };

    let Some(profile) = (match pq::get_roleplay_profile(&app.pool, id).await {
        Ok(v) => v,
        Err(e) => {
            tracing::error!(player_id = id, error = ?e, "failed to load roleplay profile");
            None
        }
    }) else {
        return Redirect::to("/city").into_response();
    };

    let (prev_id, next_id) = match pq::get_adjacent_roleplay_ids(&app.pool, id).await {
        Ok(v) => v,
        Err(e) => {
            tracing::error!(player_id = id, error = ?e, "failed to get adjacent roleplay ids");
            (None, None)
        }
    };

    let meta = PageMeta::titled(format!("Roleplay — {}", profile.username));
    let base = app.templates.build_context(&ctx, &meta);
    let view = RoleplayView {
        base,
        player_name: profile.username,
        roleplay: text::bbcode_to_html(&profile.roleplay, &[], false),
        ooc: text::bbcode_to_html(&profile.ooc, &[], false),
        prev_id,
        next_id,
    };
    app.templates.render_value("roleplay.html", &view)
}

// =========================================================================
// Handlers — Chronicle
// =========================================================================

/// GET /chronicle — list missions by location.
pub async fn chronicle_page(
    State(app): State<AppState>,
    Extension(ctx): Extension<RequestContext>,
    Query(q): Query<ChronicleLocationQuery>,
) -> Response {
    let Some(ref user) = ctx.session_user else {
        return Redirect::to("/").into_response();
    };

    let player_chapter = pq::get_player_chapter(&app.pool, user.id)
        .await
        .unwrap_or(0);
    let location = q.location.trim().to_string();

    let rows = pq::list_chronicle_missions(&app.pool, &location, player_chapter)
        .await
        .unwrap_or_default();

    let missions: Vec<ChronicleItem> = rows
        .into_iter()
        .map(|m| ChronicleItem {
            id: m.id,
            name: m.name,
            mission_type: m.mission_type,
            short_desc: m.short_desc,
        })
        .collect();

    let meta = PageMeta::titled("Kronika przygód");
    let base = app.templates.build_context(&ctx, &meta);
    let view = ChronicleView {
        base,
        missions,
        location,
    };
    app.templates.render_value("chronicle.html", &view)
}

/// GET /chronicle/{id} — view a single mission.
pub async fn chronicle_mission(
    State(app): State<AppState>,
    Extension(ctx): Extension<RequestContext>,
    Path(id): Path<i64>,
) -> Response {
    let Some(ref _user) = ctx.session_user else {
        return Redirect::to("/").into_response();
    };

    let Some(row) = pq::get_chronicle_mission(&app.pool, id)
        .await
        .ok()
        .flatten()
    else {
        return Redirect::to("/chronicle").into_response();
    };

    let meta = PageMeta::titled(&row.name);
    let base = app.templates.build_context(&ctx, &meta);
    let view = ChronicleMissionView {
        base,
        mission: ChronicleMissionDetail {
            id: row.id,
            name: row.name,
            intro: row.intro,
            mission_type: row.mission_type,
        },
    };
    app.templates.render_value("chronicle_mission.html", &view)
}
