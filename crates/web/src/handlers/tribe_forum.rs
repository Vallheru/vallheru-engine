//! Tribe forum handlers — topic list, topic view, add/reply/delete, search.

use std::time::{SystemTime, UNIX_EPOCH};

use axum::extract::{Query, State};
use axum::response::{IntoResponse, Redirect, Response};
use axum::{Extension, Form};

use vallheru_data::queries::player::{self as pq, PlayerRow};
use vallheru_data::queries::tribe_forum as tfq;
use vallheru_domain::text;

use crate::middleware::context::RequestContext;
use crate::page::{Flash, FlashKind, PageMeta};
use crate::state::AppState;

// =========================================================================
// Constants
// =========================================================================

const PER_PAGE: i64 = 25;

// =========================================================================
// View models
// =========================================================================

#[derive(serde::Serialize)]
pub struct TopicListView {
    #[serde(flatten)]
    pub base: crate::render::RenderContext,
    pub topics: Vec<TopicListItem>,
    pub page: i64,
    pub total_pages: i64,
    pub can_admin: bool,
    pub message: String,
}

#[derive(serde::Serialize)]
pub struct TopicListItem {
    pub id: i32,
    pub title: String,
    pub author: String,
    pub author_id: i32,
    pub reply_count: i64,
    pub is_sticky: bool,
    pub is_new: bool,
}

#[derive(serde::Serialize)]
pub struct TopicView {
    #[serde(flatten)]
    pub base: crate::render::RenderContext,
    pub topic_id: i32,
    pub topic_title: String,
    pub topic_body: String,
    pub topic_author: String,
    pub topic_author_id: i32,
    pub replies: Vec<ReplyItem>,
    pub page: i64,
    pub total_pages: i64,
    pub can_admin: bool,
    pub is_sticky: bool,
    pub quote_text: String,
}

#[derive(serde::Serialize)]
pub struct ReplyItem {
    pub id: i32,
    pub author: String,
    pub author_id: i32,
    pub body: String,
}

#[derive(serde::Serialize)]
pub struct NewPostsView {
    #[serde(flatten)]
    pub base: crate::render::RenderContext,
    pub topics: Vec<NewPostItem>,
    pub page: i64,
    pub total_pages: i64,
}

#[derive(serde::Serialize)]
pub struct NewPostItem {
    pub id: i32,
    pub title: String,
}

#[derive(serde::Serialize)]
pub struct SearchResultView {
    #[serde(flatten)]
    pub base: crate::render::RenderContext,
    pub results: Vec<SearchItem>,
    pub count: usize,
}

#[derive(serde::Serialize)]
pub struct SearchItem {
    pub id: i32,
    pub title: String,
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
pub struct TopicReadQuery {
    #[serde(default = "default_page")]
    pub page: i64,
    #[serde(default)]
    pub quote: Option<i32>,
    #[serde(default)]
    pub quotet: bool,
}

#[derive(serde::Deserialize)]
pub struct NewTopicForm {
    #[serde(default)]
    pub title2: String,
    #[serde(default)]
    pub body: String,
    #[serde(default)]
    pub sticky: Option<String>,
}

#[derive(serde::Deserialize)]
pub struct ReplyForm {
    pub topic_id: i32,
    #[serde(default)]
    pub rep: String,
}

#[derive(serde::Deserialize)]
pub struct BulkDeleteForm {
    #[serde(default)]
    pub ids: String,
}

#[derive(serde::Deserialize)]
pub struct StickyQuery {
    pub id: i32,
    pub action: String,
}

#[derive(serde::Deserialize)]
pub struct DeleteReplyQuery {
    pub id: i32,
    pub topic: i32,
}

#[derive(serde::Deserialize)]
pub struct SearchForm {
    #[serde(default)]
    pub search: String,
}

// =========================================================================
// Helpers
// =========================================================================

fn current_epoch() -> i64 {
    #[allow(clippy::cast_possible_wrap)]
    let epoch = SystemTime::now()
        .duration_since(UNIX_EPOCH)
        .unwrap_or_default()
        .as_secs() as i64;
    epoch
}

fn total_pages(count: i64) -> i64 {
    ((count + PER_PAGE - 1) / PER_PAGE).max(1)
}

fn clamp_page(page: i64, total: i64) -> i64 {
    page.clamp(1, total)
}

fn error_page(state: &AppState, ctx: &RequestContext, message: &str) -> Response {
    let meta = PageMeta::titled("Forum klanu").with_flash(Flash {
        kind: FlashKind::Error,
        message: message.to_owned(),
    });
    let base = state.templates.build_context(ctx, &meta);
    state.templates.render("error.html", &base)
}

/// Build the author string with tribe tags: "prefix Name suffix".
/// Format a Unix epoch as "DD-MM-YY HH:MM:SS" in UTC.
#[allow(clippy::cast_sign_loss, clippy::cast_possible_truncation)]
fn format_datetime(epoch: i64) -> String {
    let mut rem = epoch;
    let secs = (rem % 60) as u32;
    rem /= 60;
    let mins = (rem % 60) as u32;
    rem /= 60;
    let hours = (rem % 24) as u32;
    let mut days = rem / 24;

    // Compute year.
    let mut year: i32 = 1970;
    loop {
        let ydays: i64 = if year % 4 == 0 && (year % 100 != 0 || year % 400 == 0) {
            366
        } else {
            365
        };
        if days < ydays {
            break;
        }
        days -= ydays;
        year += 1;
    }
    let leap = year % 4 == 0 && (year % 100 != 0 || year % 400 == 0);
    let mdays: [i64; 12] = [
        31,
        if leap { 29 } else { 28 },
        31,
        30,
        31,
        30,
        31,
        31,
        30,
        31,
        30,
        31,
    ];
    let mut month: u32 = 0;
    for &md in &mdays {
        if days < md {
            break;
        }
        days -= md;
        month += 1;
    }
    let day = days as u32 + 1;
    month += 1;
    format!(
        "{:02}-{:02}-{:02} {:02}:{:02}:{:02}",
        day,
        month,
        year % 100,
        hours,
        mins,
        secs
    )
}

fn build_author(name: &str, prefix: &str, suffix: &str) -> String {
    let mut s = String::new();
    if !prefix.is_empty() {
        s.push_str(prefix);
        s.push(' ');
    }
    s.push_str(name);
    if !suffix.is_empty() {
        s.push(' ');
        s.push_str(suffix);
    }
    s
}

/// Extract log-in user's `PlayerRow` or `None` if not logged in or DB error.
async fn require_player(pool: &sqlx::PgPool, ctx: &RequestContext) -> Option<PlayerRow> {
    let user = ctx.session_user.as_ref()?;
    #[allow(clippy::cast_possible_truncation)]
    let id = user.id as i32;
    match pq::find_player_by_id(pool, id).await {
        Ok(v) => v,
        Err(e) => {
            tracing::error!(player_id = id, error = ?e, "failed to load player for tribe forum");
            None
        }
    }
}

/// Build author string with tribe prefix/suffix tags.
async fn tribe_author(pool: &sqlx::PgPool, name: &str, tribe_id: i32) -> String {
    let tags = match tfq::tribe_tags(pool, tribe_id).await {
        Ok(v) => v,
        Err(e) => {
            tracing::error!(tribe_id, error = ?e, "failed to load tribe tags");
            None
        }
    };
    let (prefix, suffix) = tags
        .as_ref()
        .map_or(("", ""), |t| (t.prefix.as_str(), t.suffix.as_str()));
    build_author(name, prefix, suffix)
}

// =========================================================================
// Handlers
// =========================================================================

/// GET /tforums — topic list (default view).
pub async fn tforums_topics(
    State(app): State<AppState>,
    Extension(ctx): Extension<RequestContext>,
    Query(pq_query): Query<PageQuery>,
) -> Response {
    let Some(ref user) = ctx.session_user else {
        return Redirect::to("/").into_response();
    };
    let Some(player_row) = require_player(&app.pool, &ctx).await else {
        return Redirect::to("/").into_response();
    };
    if player_row.tribe_id == 0 {
        return error_page(&app, &ctx, "Nie należysz do żadnego klanu.");
    }
    let tribe_id = player_row.tribe_id;

    // Mark forum as read.
    if let Err(e) = tfq::update_tforum_time(&app.pool, user.id, current_epoch()).await {
        tracing::warn!(error = %e, "Failed to update tribe forum time");
    }
    let forum_time = player_row.tforum_time;

    let can_admin = tfq::has_forum_permission(&app.pool, user.id, tribe_id)
        .await
        .unwrap_or(false);

    // Sticky topics first.
    let sticky_topics = tfq::list_sticky_topics(&app.pool, tribe_id)
        .await
        .unwrap_or_default();

    // Count non-sticky topics.
    let non_sticky_count = tfq::count_topics(&app.pool, tribe_id).await.unwrap_or(0);
    let pages = total_pages(non_sticky_count);
    let page = clamp_page(pq_query.page, pages);
    let offset = (page - 1) * PER_PAGE;

    let regular_topics = tfq::list_topics(&app.pool, tribe_id, PER_PAGE, offset)
        .await
        .unwrap_or_default();

    // Build view items — stickies first, then regular.
    let mut topics = Vec::with_capacity(sticky_topics.len() + regular_topics.len());
    for t in &sticky_topics {
        let replies = tfq::reply_count(&app.pool, t.id).await.unwrap_or(0);
        topics.push(TopicListItem {
            id: t.id,
            title: format!("<b>{}</b>", t.topic),
            author: t.starter.clone(),
            author_id: t.pid,
            reply_count: replies,
            is_sticky: true,
            is_new: t.w_time > forum_time,
        });
    }
    for t in &regular_topics {
        let replies = tfq::reply_count(&app.pool, t.id).await.unwrap_or(0);
        topics.push(TopicListItem {
            id: t.id,
            title: t.topic.clone(),
            author: t.starter.clone(),
            author_id: t.pid,
            reply_count: replies,
            is_sticky: false,
            is_new: t.w_time > forum_time,
        });
    }

    let meta = PageMeta::titled("Forum klanu");
    let base = app.templates.build_context(&ctx, &meta);
    let view = TopicListView {
        base,
        topics,
        page,
        total_pages: pages,
        can_admin,
        message: String::new(),
    };
    app.templates.render_value("tforums_topics.html", &view)
}

/// GET /tforums/new — list of unread topics.
pub async fn tforums_new_posts(
    State(app): State<AppState>,
    Extension(ctx): Extension<RequestContext>,
    Query(pq_query): Query<PageQuery>,
) -> Response {
    let Some(ref user) = ctx.session_user else {
        return Redirect::to("/").into_response();
    };
    let Some(player_row) = require_player(&app.pool, &ctx).await else {
        return Redirect::to("/").into_response();
    };
    if player_row.tribe_id == 0 {
        return error_page(&app, &ctx, "Nie należysz do żadnego klanu.");
    }
    let tribe_id = player_row.tribe_id;
    let forum_time = player_row.tforum_time;

    // Update tforum_time.
    if let Err(e) = tfq::update_tforum_time(&app.pool, user.id, current_epoch()).await {
        tracing::warn!(error = %e, "Failed to update tribe forum time");
    }

    let new_count = tfq::count_new_topics(&app.pool, tribe_id, forum_time)
        .await
        .unwrap_or(0);
    if new_count == 0 {
        return error_page(
            &app,
            &ctx,
            "Nie ma nowych wpisów na forum. (<a href=\"/tforums\">Wróć</a>)",
        );
    }

    let pages = total_pages(new_count);
    let page = clamp_page(pq_query.page, pages);
    let offset = (page - 1) * PER_PAGE;

    let rows = tfq::list_new_topics(&app.pool, tribe_id, forum_time, PER_PAGE, offset)
        .await
        .unwrap_or_default();

    let topics: Vec<NewPostItem> = rows
        .into_iter()
        .map(|t| NewPostItem {
            id: t.id,
            title: t.topic,
        })
        .collect();

    let meta = PageMeta::titled("Forum klanu - Nowe wpisy");
    let base = app.templates.build_context(&ctx, &meta);
    let view = NewPostsView {
        base,
        topics,
        page,
        total_pages: pages,
    };
    app.templates.render_value("tforums_new.html", &view)
}

/// GET /tforums/topic/{id} — view a single topic with replies.
pub async fn tforums_topic_read(
    State(app): State<AppState>,
    Extension(ctx): Extension<RequestContext>,
    axum::extract::Path(topic_id): axum::extract::Path<i32>,
    Query(q): Query<TopicReadQuery>,
) -> Response {
    let Some(ref user) = ctx.session_user else {
        return Redirect::to("/").into_response();
    };
    let Some(player_row) = require_player(&app.pool, &ctx).await else {
        return Redirect::to("/").into_response();
    };
    if player_row.tribe_id == 0 {
        return error_page(&app, &ctx, "Nie należysz do żadnego klanu.");
    }
    let tribe_id = player_row.tribe_id;

    let can_admin = tfq::has_forum_permission(&app.pool, user.id, tribe_id)
        .await
        .unwrap_or(false);

    let Some(topic) = tfq::find_topic(&app.pool, topic_id, tribe_id)
        .await
        .unwrap_or(None)
    else {
        return error_page(&app, &ctx, "Nie ma takiego tematu.");
    };

    // Handle quote.
    let mut quote_text = String::new();
    if q.quotet {
        let bb = text::html_to_bbcode(&topic.body);
        quote_text = format!("[quote]{bb}[/quote]");
    } else if let Some(reply_id) = q.quote {
        if let Ok(Some(body)) = tfq::reply_body(&app.pool, reply_id).await {
            let bb = text::html_to_bbcode(&body);
            quote_text = format!("[quote]{bb}[/quote]");
        }
    }

    // Replies pagination.
    let total_replies = tfq::reply_count(&app.pool, topic_id).await.unwrap_or(0);
    let pages = total_pages(total_replies);
    let page = if q.page <= 0 {
        pages
    } else {
        clamp_page(q.page, pages)
    };
    let offset = (page - 1) * PER_PAGE;

    let reply_rows = tfq::list_replies(&app.pool, topic_id, PER_PAGE, offset)
        .await
        .unwrap_or_default();

    let replies: Vec<ReplyItem> = reply_rows
        .into_iter()
        .map(|r| ReplyItem {
            id: r.id,
            author: r.starter,
            author_id: r.pid,
            body: r.body,
        })
        .collect();

    let meta = PageMeta::titled("Forum klanu");
    let base = app.templates.build_context(&ctx, &meta);
    let view = TopicView {
        base,
        topic_id: topic.id,
        topic_title: topic.topic,
        topic_body: topic.body,
        topic_author: topic.starter,
        topic_author_id: topic.pid,
        replies,
        page,
        total_pages: pages,
        can_admin,
        is_sticky: topic.sticky == "Y",
        quote_text,
    };
    app.templates.render_value("tforums_topic.html", &view)
}

/// POST /tforums/topic/new — create a new topic.
#[allow(clippy::cast_possible_truncation)]
pub async fn tforums_add_topic(
    State(app): State<AppState>,
    Extension(ctx): Extension<RequestContext>,
    Form(form): Form<NewTopicForm>,
) -> Response {
    let Some(ref user) = ctx.session_user else {
        return Redirect::to("/").into_response();
    };
    let Some(player_row) = require_player(&app.pool, &ctx).await else {
        return Redirect::to("/").into_response();
    };
    if player_row.tribe_id == 0 {
        return Redirect::to("/tforums").into_response();
    }
    let tribe_id = player_row.tribe_id;

    // Rate limit: one post per 10 seconds.
    if !app.post_rate_limiter.check_and_record(user.id) {
        return error_page(&app, &ctx, "Musisz odczekać 10 sekund między postami.");
    }
    let now = current_epoch();

    let title = text::strip_tags(&form.title2);
    let body_raw = form.body.clone();
    if title.trim().is_empty() || body_raw.trim().is_empty() {
        return error_page(&app, &ctx, "Wypełnij wszystkie pola.");
    }

    // Determine sticky.
    let can_admin = tfq::has_forum_permission(&app.pool, user.id, tribe_id)
        .await
        .unwrap_or(false);
    let sticky = if form.sticky.is_some() && can_admin {
        "Y"
    } else {
        "N"
    };

    // Build author with tribe tags.
    let author = tribe_author(&app.pool, &user.name, tribe_id).await;

    // Process BBCode.
    let bad_words = vallheru_data::queries::chat::list_bad_words(&app.pool)
        .await
        .unwrap_or_default();
    let body_html = text::bbcode_to_html(&body_raw, &bad_words, false);

    // Prepend date/time to title (matching PHP behavior).
    let dt = format_datetime(now);
    let full_title = format!("<b>{dt}</b> {title}");

    let topic_id = tfq::insert_topic(
        &app.pool,
        &tfq::NewTopic {
            tribe_id,
            title: &full_title,
            body: &body_html,
            starter: &author,
            pid: user.id as i32,
            w_time: now,
            sticky,
        },
    )
    .await;

    match topic_id {
        Ok(tid) => Redirect::to(&format!("/tforums/topic/{tid}")).into_response(),
        Err(_) => Redirect::to("/tforums").into_response(),
    }
}

/// POST /tforums/topic/{id}/reply — add a reply to a topic.
#[allow(clippy::cast_possible_truncation)]
pub async fn tforums_add_reply(
    State(app): State<AppState>,
    Extension(ctx): Extension<RequestContext>,
    axum::extract::Path(topic_id): axum::extract::Path<i32>,
    Form(form): Form<ReplyForm>,
) -> Response {
    let Some(ref user) = ctx.session_user else {
        return Redirect::to("/").into_response();
    };
    let Some(player_row) = require_player(&app.pool, &ctx).await else {
        return Redirect::to("/").into_response();
    };
    if player_row.tribe_id == 0 {
        return Redirect::to("/tforums").into_response();
    }
    let tribe_id = player_row.tribe_id;

    // Rate limit: one post per 10 seconds.
    if !app.post_rate_limiter.check_and_record(user.id) {
        return error_page(&app, &ctx, "Musisz odczekać 10 sekund między postami.");
    }
    let now = current_epoch();

    // Check topic exists and belongs to tribe.
    if match tfq::find_topic(&app.pool, topic_id, tribe_id).await {
        Ok(v) => v.is_none(),
        Err(e) => {
            tracing::error!(error = %e, topic_id, tribe_id, "Failed to find tribe forum topic");
            true
        }
    } {
        return error_page(&app, &ctx, "Nie ma takiego tematu.");
    }

    let body_raw = form.rep.clone();
    if body_raw.trim().is_empty() {
        return error_page(&app, &ctx, "Wypełnij wszystkie pola.");
    }

    // Build author with tribe tags.
    let author = tribe_author(&app.pool, &user.name, tribe_id).await;

    // Process BBCode.
    let bad_words = vallheru_data::queries::chat::list_bad_words(&app.pool)
        .await
        .unwrap_or_default();
    let body_html = text::bbcode_to_html(&body_raw, &bad_words, false);

    // Prepend date/time to body.
    let dt = format_datetime(now);
    let full_body = format!("<b>{dt}</b><br />{body_html}");

    if let Err(e) = tfq::insert_reply(
        &app.pool,
        topic_id,
        &author,
        &full_body,
        user.id as i32,
        now,
    )
    .await
    {
        tracing::error!(error = %e, "Failed to insert tribe forum reply");
    }

    Redirect::to(&format!("/tforums/topic/{topic_id}")).into_response()
}

/// POST /tforums/topic/{id}/delete — delete a topic (admin only).
pub async fn tforums_delete_topic(
    State(app): State<AppState>,
    Extension(ctx): Extension<RequestContext>,
    axum::extract::Path(topic_id): axum::extract::Path<i32>,
) -> Response {
    let Some(ref user) = ctx.session_user else {
        return Redirect::to("/").into_response();
    };
    let Some(player_row) = require_player(&app.pool, &ctx).await else {
        return Redirect::to("/").into_response();
    };
    if player_row.tribe_id == 0 {
        return Redirect::to("/tforums").into_response();
    }

    let can_admin = tfq::has_forum_permission(&app.pool, user.id, player_row.tribe_id)
        .await
        .unwrap_or(false);
    if !can_admin {
        return error_page(&app, &ctx, "Nie posiadasz odpowiednich uprawnień.");
    }

    // Verify topic belongs to tribe.
    if match tfq::find_topic(&app.pool, topic_id, player_row.tribe_id).await {
        Ok(v) => v.is_none(),
        Err(e) => {
            tracing::error!(error = %e, topic_id, tribe_id = player_row.tribe_id, "Failed to find tribe forum topic");
            true
        }
    } {
        return error_page(&app, &ctx, "Nie ma takiego tematu.");
    }

    if let Err(e) = tfq::delete_topic(&app.pool, topic_id).await {
        tracing::error!(error = %e, "Failed to delete tribe forum topic");
    }
    Redirect::to("/tforums").into_response()
}

/// POST /tforums/delete-topics — bulk delete topics (admin only).
pub async fn tforums_bulk_delete(
    State(app): State<AppState>,
    Extension(ctx): Extension<RequestContext>,
    Form(form): Form<BulkDeleteForm>,
) -> Response {
    let Some(ref user) = ctx.session_user else {
        return Redirect::to("/").into_response();
    };
    let Some(player_row) = require_player(&app.pool, &ctx).await else {
        return Redirect::to("/").into_response();
    };
    if player_row.tribe_id == 0 {
        return Redirect::to("/tforums").into_response();
    }

    let can_admin = tfq::has_forum_permission(&app.pool, user.id, player_row.tribe_id)
        .await
        .unwrap_or(false);
    if !can_admin {
        return error_page(&app, &ctx, "Nie posiadasz odpowiednich uprawnień.");
    }

    let ids: Vec<i32> = form
        .ids
        .split(',')
        .filter_map(|s| s.trim().parse().ok())
        .collect();
    if !ids.is_empty() {
        if let Err(e) = tfq::delete_topics_bulk(&app.pool, player_row.tribe_id, &ids).await {
            tracing::error!(error = %e, "Failed to bulk delete tribe forum topics");
        }
    }

    Redirect::to("/tforums").into_response()
}

/// POST /tforums/topic/{id}/sticky — toggle sticky (admin only).
pub async fn tforums_toggle_sticky(
    State(app): State<AppState>,
    Extension(ctx): Extension<RequestContext>,
    axum::extract::Path(topic_id): axum::extract::Path<i32>,
    Query(q): Query<StickyQuery>,
) -> Response {
    let Some(ref user) = ctx.session_user else {
        return Redirect::to("/").into_response();
    };
    let Some(player_row) = require_player(&app.pool, &ctx).await else {
        return Redirect::to("/").into_response();
    };
    if player_row.tribe_id == 0 {
        return Redirect::to("/tforums").into_response();
    }

    let can_admin = tfq::has_forum_permission(&app.pool, user.id, player_row.tribe_id)
        .await
        .unwrap_or(false);
    if !can_admin {
        return error_page(&app, &ctx, "Nie posiadasz odpowiednich uprawnień.");
    }

    if q.action != "Y" && q.action != "N" {
        return Redirect::to("/tforums").into_response();
    }

    // Verify topic belongs to tribe.
    if match tfq::find_topic(&app.pool, topic_id, player_row.tribe_id).await {
        Ok(v) => v.is_none(),
        Err(e) => {
            tracing::error!(error = %e, topic_id, tribe_id = player_row.tribe_id, "Failed to find tribe forum topic");
            true
        }
    } {
        return error_page(&app, &ctx, "Nie ma takiego tematu.");
    }

    if let Err(e) = tfq::set_sticky(&app.pool, topic_id, &q.action).await {
        tracing::error!(error = %e, "Failed to toggle sticky on tribe forum topic");
    }
    Redirect::to(&format!("/tforums/topic/{topic_id}")).into_response()
}

/// POST /tforums/reply/{id}/delete — delete a reply (admin only).
pub async fn tforums_delete_reply(
    State(app): State<AppState>,
    Extension(ctx): Extension<RequestContext>,
    axum::extract::Path(reply_id): axum::extract::Path<i32>,
    Query(q): Query<DeleteReplyQuery>,
) -> Response {
    let Some(ref user) = ctx.session_user else {
        return Redirect::to("/").into_response();
    };
    let Some(player_row) = require_player(&app.pool, &ctx).await else {
        return Redirect::to("/").into_response();
    };
    if player_row.tribe_id == 0 {
        return Redirect::to("/tforums").into_response();
    }

    let can_admin = tfq::has_forum_permission(&app.pool, user.id, player_row.tribe_id)
        .await
        .unwrap_or(false);
    if !can_admin {
        return error_page(&app, &ctx, "Nie posiadasz odpowiednich uprawnień.");
    }

    if let Err(e) = tfq::delete_reply(&app.pool, reply_id, player_row.tribe_id).await {
        tracing::error!(error = %e, "Failed to delete tribe forum reply");
    }
    Redirect::to(&format!("/tforums/topic/{}", q.topic)).into_response()
}

/// POST /tforums/search — search tribe forum.
pub async fn tforums_search(
    State(app): State<AppState>,
    Extension(ctx): Extension<RequestContext>,
    Form(form): Form<SearchForm>,
) -> Response {
    let Some(player_row) = require_player(&app.pool, &ctx).await else {
        return Redirect::to("/").into_response();
    };
    if player_row.tribe_id == 0 {
        return Redirect::to("/tforums").into_response();
    }

    let query = text::strip_tags(&form.search);
    if query.trim().is_empty() {
        return error_page(&app, &ctx, "Wypełnij wszystkie pola.");
    }

    let results = tfq::search_topics(&app.pool, player_row.tribe_id, &query)
        .await
        .unwrap_or_default();

    let count = results.len();
    let items: Vec<SearchItem> = results
        .into_iter()
        .map(|(id, title)| SearchItem { id, title })
        .collect();

    let meta = PageMeta::titled("Forum klanu - Szukaj");
    let base = app.templates.build_context(&ctx, &meta);
    let view = SearchResultView {
        base,
        results: items,
        count,
    };
    app.templates.render_value("tforums_search.html", &view)
}
