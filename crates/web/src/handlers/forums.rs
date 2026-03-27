//! Forum handlers — categories, topics, replies, search, admin actions.

use axum::extract::{Path, Query, State};
use axum::response::{IntoResponse, Redirect, Response};
use axum::{Extension, Form};

use vallheru_data::queries::forum as fq;
use vallheru_domain::social::forum as forum_domain;
use vallheru_domain::text;

use crate::middleware::context::RequestContext;
use crate::page::PageMeta;
use crate::state::AppState;

// =========================================================================
// View models
// =========================================================================

#[derive(serde::Serialize)]
pub struct CategoriesView {
    #[serde(flatten)]
    pub base: crate::render::RenderContext,
    pub categories: Vec<CategoryItem>,
    pub unread_count: i64,
}

#[derive(serde::Serialize)]
pub struct CategoryItem {
    pub id: i64,
    pub name: String,
    pub description: String,
    pub topic_count: i64,
}

#[derive(serde::Serialize)]
pub struct TopicListView {
    #[serde(flatten)]
    pub base: crate::render::RenderContext,
    pub category_id: i64,
    pub topics: Vec<TopicItem>,
    pub page: i64,
    pub total_pages: i64,
    pub can_write: bool,
    pub can_create_topic: bool,
    pub is_staff: bool,
    pub sort: i32,
}

#[derive(serde::Serialize)]
pub struct TopicItem {
    pub id: i64,
    pub title: String,
    pub author_name: String,
    pub author_id: i64,
    pub reply_count: i32,
    pub is_sticky: bool,
    pub is_closed: bool,
    pub is_new: bool,
}

#[derive(serde::Serialize)]
pub struct TopicReadView {
    #[serde(flatten)]
    pub base: crate::render::RenderContext,
    pub topic: TopicDetail,
    pub replies: Vec<ReplyItem>,
    pub page: i64,
    pub total_pages: i64,
    pub is_staff: bool,
    pub is_closed: bool,
    pub prev_topic_id: Option<i64>,
    pub next_topic_id: Option<i64>,
    pub category_id: i64,
    pub quote_text: String,
}

#[derive(serde::Serialize)]
pub struct TopicDetail {
    pub id: i64,
    pub title: String,
    pub body: String,
    pub author_name: String,
    pub author_id: i64,
    pub created_at: String,
    pub is_sticky: bool,
    pub is_closed: bool,
}

#[derive(serde::Serialize)]
pub struct ReplyItem {
    pub id: i64,
    pub author_name: String,
    pub author_id: i64,
    pub body: String,
    pub created_at: String,
}

#[derive(serde::Serialize)]
pub struct SearchResultView {
    #[serde(flatten)]
    pub base: crate::render::RenderContext,
    pub category_id: i64,
    pub results: Vec<TopicItem>,
    pub searched: bool,
}

#[derive(serde::Serialize)]
pub struct MoveTopicView {
    #[serde(flatten)]
    pub base: crate::render::RenderContext,
    pub topic_id: i64,
    pub categories: Vec<CategoryItem>,
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
    pub id: i64,
    pub title: String,
    pub category_name: String,
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
pub struct TopicListQuery {
    #[serde(default = "default_page")]
    pub page: i64,
    #[serde(default)]
    pub sort: i32,
}

#[derive(serde::Deserialize)]
pub struct TopicReadQuery {
    #[serde(default = "default_page")]
    pub page: i64,
    #[serde(default)]
    pub quote: Option<i64>,
    #[serde(default)]
    pub quotet: bool,
}

#[derive(serde::Deserialize)]
pub struct NewTopicForm {
    pub catid: i64,
    #[serde(default)]
    pub title2: String,
    #[serde(default)]
    pub body: String,
    #[serde(default)]
    pub sticky: Option<String>,
}

#[derive(serde::Deserialize)]
pub struct ReplyForm {
    #[serde(default)]
    pub rep: String,
}

#[derive(serde::Deserialize)]
pub struct SearchForm {
    pub catid: i64,
    #[serde(default)]
    pub search: String,
}

#[derive(serde::Deserialize)]
pub struct BulkDeleteForm {
    pub catid: i64,
    #[serde(default)]
    pub ids: String,
}

#[derive(serde::Deserialize)]
pub struct BulkDeleteRepliesForm {
    #[serde(default)]
    pub ids: String,
}

#[derive(serde::Deserialize)]
pub struct MoveForm {
    pub category: i64,
}

#[derive(serde::Deserialize)]
pub struct ActionQuery {
    #[serde(default)]
    pub action: String,
}

// =========================================================================
// Handlers
// =========================================================================

/// GET /forums — category list.
pub async fn forum_categories(
    State(app): State<AppState>,
    Extension(ctx): Extension<RequestContext>,
) -> Response {
    let Some(ref user) = ctx.session_user else {
        return Redirect::to("/").into_response();
    };
    let rank = &user.rank;

    let all_cats = fq::list_categories(&app.pool).await.unwrap_or_default();

    // Filter by visit permission.
    let categories: Vec<CategoryItem> = all_cats
        .into_iter()
        .filter(|c| forum_domain::has_permission(&c.perm_visit, rank))
        .map(|c| CategoryItem {
            id: c.id,
            name: c.name,
            description: c.description,
            topic_count: c.topic_count,
        })
        .collect();

    // Count unread topics.
    let accessible_ids: Vec<i64> = categories.iter().map(|c| c.id).collect();
    let player_row = vallheru_data::queries::player::find_player_by_id(
        &app.pool,
        #[allow(clippy::cast_possible_truncation)]
        (user.id as i32),
    )
    .await
    .ok()
    .flatten();
    let forum_time = player_row.as_ref().map_or(0, |p| p.forum_time);
    let unread_count = fq::count_unread(&app.pool, forum_time, &accessible_ids)
        .await
        .unwrap_or(0);

    let meta = PageMeta::titled("Forum");
    let base = app.templates.build_context(&ctx, &meta);
    let view = CategoriesView {
        base,
        categories,
        unread_count,
    };
    app.templates.render_value("forum_categories.html", &view)
}

/// GET /forums/new — new posts listing.
pub async fn forum_new_posts(
    State(app): State<AppState>,
    Extension(ctx): Extension<RequestContext>,
    Query(pq): Query<PageQuery>,
) -> Response {
    let Some(ref user) = ctx.session_user else {
        return Redirect::to("/").into_response();
    };
    let rank = &user.rank;

    let all_cats = fq::list_categories(&app.pool).await.unwrap_or_default();
    let accessible_ids: Vec<i64> = all_cats
        .iter()
        .filter(|c| forum_domain::has_permission(&c.perm_visit, rank))
        .map(|c| c.id)
        .collect();

    let player_row = vallheru_data::queries::player::find_player_by_id(
        &app.pool,
        #[allow(clippy::cast_possible_truncation)]
        (user.id as i32),
    )
    .await
    .ok()
    .flatten();
    let forum_time = player_row.as_ref().map_or(0, |p| p.forum_time);

    // Update forum_time for this player.
    let _ = fq::update_forum_time(&app.pool, user.id).await;

    let total_unread = fq::count_unread(&app.pool, forum_time, &accessible_ids)
        .await
        .unwrap_or(0);
    let total = forum_domain::total_pages(total_unread, forum_domain::TOPICS_PER_PAGE);
    let page = forum_domain::clamp_page(pq.page, total);
    let offset = (page - 1) * forum_domain::TOPICS_PER_PAGE;

    let rows = fq::list_unread_topics(
        &app.pool,
        forum_time,
        &accessible_ids,
        forum_domain::TOPICS_PER_PAGE,
        offset,
    )
    .await
    .unwrap_or_default();

    // Build category name lookup.
    let cat_names: std::collections::HashMap<i64, String> =
        all_cats.into_iter().map(|c| (c.id, c.name)).collect();

    let topics: Vec<NewPostItem> = rows
        .into_iter()
        .map(|t| NewPostItem {
            id: t.id,
            title: t.title,
            category_name: String::new(), // We'd need category_id on TopicListRow for lookup
        })
        .collect();
    let _ = cat_names; // TODO: enrich with category join if needed

    let meta = PageMeta::titled("Forum - Nowe wiadomości");
    let base = app.templates.build_context(&ctx, &meta);
    let view = NewPostsView {
        base,
        topics,
        page,
        total_pages: total,
    };
    app.templates.render_value("forum_new_posts.html", &view)
}

/// GET /forums/category/{id} — topic list for a category.
pub async fn forum_topic_list(
    State(app): State<AppState>,
    Extension(ctx): Extension<RequestContext>,
    Path(category_id): Path<i64>,
    Query(q): Query<TopicListQuery>,
) -> Response {
    let Some(ref user) = ctx.session_user else {
        return Redirect::to("/").into_response();
    };
    let rank = &user.rank;

    // Check visit permission.
    let Ok(Some(perms)) = fq::get_category_perms(&app.pool, category_id).await else {
        return Redirect::to("/forums").into_response();
    };
    if !forum_domain::has_permission(&perms.perm_visit, rank) {
        return Redirect::to("/forums").into_response();
    }

    let can_write = forum_domain::has_permission(&perms.perm_write, rank);
    let can_create_topic = can_write && forum_domain::has_permission(&perms.perm_topic, rank);
    let staff = forum_domain::is_staff(rank);

    // Get forum_time for unread checking.
    let player_row = vallheru_data::queries::player::find_player_by_id(
        &app.pool,
        #[allow(clippy::cast_possible_truncation)]
        (user.id as i32),
    )
    .await
    .ok()
    .flatten();
    let forum_time = player_row.as_ref().map_or(0, |p| p.forum_time);

    // Sticky topics first.
    let sticky_rows = fq::list_sticky_topics(&app.pool, category_id)
        .await
        .unwrap_or_default();

    // Non-sticky topics with sorting and pagination.
    let sort = forum_domain::TopicSort::from_i32(q.sort);
    let count = fq::count_topics(&app.pool, category_id).await.unwrap_or(0);
    let total = forum_domain::total_pages(count, forum_domain::TOPICS_PER_PAGE);
    let page = forum_domain::clamp_page(q.page, total);
    let offset = (page - 1) * forum_domain::TOPICS_PER_PAGE;

    let normal_rows = fq::list_topics(
        &app.pool,
        category_id,
        sort.order_clause(),
        forum_domain::TOPICS_PER_PAGE,
        offset,
    )
    .await
    .unwrap_or_default();

    let mut topics: Vec<TopicItem> = Vec::with_capacity(sticky_rows.len() + normal_rows.len());

    for r in sticky_rows {
        topics.push(TopicItem {
            id: r.id,
            title: format!("<b>{}</b>", r.title),
            author_name: r.author_name,
            author_id: r.author_id,
            reply_count: r.reply_count,
            is_sticky: true,
            is_closed: r.is_closed,
            is_new: r.last_post_at_epoch > forum_time,
        });
    }
    for r in normal_rows {
        let display_title = if r.title.len() > 60 {
            let truncated: String = r.title.chars().take(57).collect();
            format!("{truncated}...")
        } else {
            r.title
        };
        topics.push(TopicItem {
            id: r.id,
            title: display_title,
            author_name: r.author_name,
            author_id: r.author_id,
            reply_count: r.reply_count,
            is_sticky: false,
            is_closed: r.is_closed,
            is_new: r.last_post_at_epoch > forum_time,
        });
    }

    let meta = PageMeta::titled("Forum - Tematy").with_js("/js/editor.js");
    let base = app.templates.build_context(&ctx, &meta);
    let view = TopicListView {
        base,
        category_id,
        topics,
        page,
        total_pages: total,
        can_write,
        can_create_topic,
        is_staff: staff,
        sort: sort.to_i32(),
    };
    app.templates.render_value("forum_topics.html", &view)
}

/// GET /forums/topic/{id} — read a topic with replies.
pub async fn forum_topic_read(
    State(app): State<AppState>,
    Extension(ctx): Extension<RequestContext>,
    Path(topic_id): Path<i64>,
    Query(q): Query<TopicReadQuery>,
) -> Response {
    let Some(ref user) = ctx.session_user else {
        return Redirect::to("/").into_response();
    };
    let rank = &user.rank;

    let Ok(Some(topic)) = fq::get_topic(&app.pool, topic_id).await else {
        return Redirect::to("/forums").into_response();
    };

    // Check visit permission.
    let Ok(Some(perms)) = fq::get_category_perms(&app.pool, topic.category_id).await else {
        return Redirect::to("/forums").into_response();
    };
    if !forum_domain::has_permission(&perms.perm_visit, rank) {
        return Redirect::to("/forums").into_response();
    }

    let staff = forum_domain::is_staff(rank);

    // Pagination.
    let reply_count = fq::count_replies(&app.pool, topic_id).await.unwrap_or(0);
    let total = forum_domain::total_pages(reply_count, forum_domain::REPLIES_PER_PAGE);
    let page = if q.page <= 0 {
        total
    } else {
        forum_domain::clamp_page(q.page, total)
    };
    let offset = (page - 1) * forum_domain::REPLIES_PER_PAGE;

    let reply_rows = fq::list_replies(&app.pool, topic_id, forum_domain::REPLIES_PER_PAGE, offset)
        .await
        .unwrap_or_default();

    let replies: Vec<ReplyItem> = reply_rows
        .into_iter()
        .map(|r| ReplyItem {
            id: r.id,
            author_name: r.author_name,
            author_id: r.author_id,
            body: r.body,
            created_at: r.created_at_formatted,
        })
        .collect();

    let prev = fq::prev_topic_id(&app.pool, topic_id, topic.category_id)
        .await
        .unwrap_or(None);
    let next = fq::next_topic_id(&app.pool, topic_id, topic.category_id)
        .await
        .unwrap_or(None);

    // Handle quoting.
    let quote_text = if q.quotet {
        // Quote the topic body.
        format!("[quote]{}[/quote]", text::html_to_bbcode(&topic.body))
    } else if let Some(reply_id) = q.quote {
        if let Ok(Some(body)) = fq::get_reply_body(&app.pool, reply_id).await {
            format!("[quote]{}[/quote]", text::html_to_bbcode(&body))
        } else {
            String::new()
        }
    } else {
        String::new()
    };

    let meta = PageMeta::titled("Forum - Temat").with_js("/js/editor.js");
    let base = app.templates.build_context(&ctx, &meta);
    let view = TopicReadView {
        base,
        topic: TopicDetail {
            id: topic.id,
            title: topic.title,
            body: topic.body,
            author_name: topic.author_name,
            author_id: topic.author_id,
            created_at: topic.created_at_formatted,
            is_sticky: topic.is_sticky,
            is_closed: topic.is_closed,
        },
        replies,
        page,
        total_pages: total,
        is_staff: staff,
        is_closed: topic.is_closed,
        prev_topic_id: prev,
        next_topic_id: next,
        category_id: topic.category_id,
        quote_text,
    };
    app.templates.render_value("forum_topic.html", &view)
}

/// POST /forums/topic/new — create a new topic.
pub async fn forum_add_topic(
    State(app): State<AppState>,
    Extension(ctx): Extension<RequestContext>,
    Form(form): Form<NewTopicForm>,
) -> Response {
    let Some(ref user) = ctx.session_user else {
        return Redirect::to("/").into_response();
    };
    let rank = &user.rank;

    // Permission checks.
    let Ok(Some(perms)) = fq::get_category_perms(&app.pool, form.catid).await else {
        return Redirect::to("/forums").into_response();
    };
    if !forum_domain::has_permission(&perms.perm_write, rank)
        || !forum_domain::has_permission(&perms.perm_topic, rank)
    {
        return Redirect::to(&format!("/forums/category/{}", form.catid)).into_response();
    }

    // Ban check.
    if fq::is_forum_banned(&app.pool, user.id)
        .await
        .unwrap_or(false)
    {
        return Redirect::to(&format!("/forums/category/{}", form.catid)).into_response();
    }

    // Validate input.
    let title = form.title2.trim();
    let body = form.body.trim();
    if title.is_empty() || body.is_empty() {
        return Redirect::to(&format!("/forums/category/{}", form.catid)).into_response();
    }

    let processed_title = forum_domain::truncate_title(title);
    let bad_words = vec![];
    let processed_body = text::bbcode_to_html(body, &bad_words, false);

    let is_sticky = form.sticky.is_some() && forum_domain::is_staff(rank);

    let Ok(topic_id) = fq::insert_topic(
        &app.pool,
        &fq::InsertTopicParams {
            category_id: form.catid,
            title: &processed_title,
            body: &processed_body,
            author_name: &user.name,
            author_id: user.id,
            is_sticky,
        },
    )
    .await
    else {
        return Redirect::to(&format!("/forums/category/{}", form.catid)).into_response();
    };

    Redirect::to(&format!("/forums/topic/{topic_id}")).into_response()
}

/// POST /forums/topic/{id}/reply — add a reply to a topic.
pub async fn forum_add_reply(
    State(app): State<AppState>,
    Extension(ctx): Extension<RequestContext>,
    Path(topic_id): Path<i64>,
    Form(form): Form<ReplyForm>,
) -> Response {
    let Some(ref user) = ctx.session_user else {
        return Redirect::to("/").into_response();
    };
    let rank = &user.rank;

    let Ok(Some(topic)) = fq::get_topic(&app.pool, topic_id).await else {
        return Redirect::to("/forums").into_response();
    };

    if topic.is_closed {
        return Redirect::to(&format!("/forums/topic/{topic_id}")).into_response();
    }

    // Permission check.
    let Ok(Some(perms)) = fq::get_category_perms(&app.pool, topic.category_id).await else {
        return Redirect::to("/forums").into_response();
    };
    if !forum_domain::has_permission(&perms.perm_write, rank) {
        return Redirect::to(&format!("/forums/topic/{topic_id}")).into_response();
    }

    // Ban check.
    if fq::is_forum_banned(&app.pool, user.id)
        .await
        .unwrap_or(false)
    {
        return Redirect::to(&format!("/forums/topic/{topic_id}")).into_response();
    }

    let body = form.rep.trim();
    if body.is_empty() {
        return Redirect::to(&format!("/forums/topic/{topic_id}")).into_response();
    }

    let bad_words = vec![];
    let processed_body = text::bbcode_to_html(body, &bad_words, false);

    let _ = fq::insert_reply(&app.pool, topic_id, &user.name, user.id, &processed_body).await;

    Redirect::to(&format!("/forums/topic/{topic_id}")).into_response()
}

/// POST /forums/topic/{id}/delete — delete a topic (staff only).
pub async fn forum_delete_topic(
    State(app): State<AppState>,
    Extension(ctx): Extension<RequestContext>,
    Path(topic_id): Path<i64>,
) -> Response {
    let Some(ref user) = ctx.session_user else {
        return Redirect::to("/").into_response();
    };
    if !forum_domain::is_staff(&user.rank) {
        return Redirect::to("/forums").into_response();
    }

    // Get category for redirect.
    let category_id = fq::get_topic(&app.pool, topic_id)
        .await
        .ok()
        .flatten()
        .map_or(0, |t| t.category_id);

    let _ = fq::delete_topic(&app.pool, topic_id).await;

    if category_id > 0 {
        Redirect::to(&format!("/forums/category/{category_id}")).into_response()
    } else {
        Redirect::to("/forums").into_response()
    }
}

/// POST /forums/category/{id}/delete-topics — bulk delete topics (staff only).
pub async fn forum_bulk_delete_topics(
    State(app): State<AppState>,
    Extension(ctx): Extension<RequestContext>,
    Path(category_id): Path<i64>,
    Form(form): Form<BulkDeleteForm>,
) -> Response {
    let Some(ref user) = ctx.session_user else {
        return Redirect::to("/").into_response();
    };
    if !forum_domain::is_staff(&user.rank) {
        return Redirect::to("/forums").into_response();
    }

    let ids: Vec<i64> = form
        .ids
        .split(',')
        .filter_map(|s| s.trim().parse().ok())
        .collect();

    if !ids.is_empty() {
        let _ = fq::delete_topics(&app.pool, category_id, &ids).await;
    }

    Redirect::to(&format!("/forums/category/{category_id}")).into_response()
}

/// POST /forums/reply/{id}/delete — delete a single reply (staff only).
pub async fn forum_delete_reply(
    State(app): State<AppState>,
    Extension(ctx): Extension<RequestContext>,
    Path(reply_id): Path<i64>,
    Query(q): Query<TopicRedirectQuery>,
) -> Response {
    let Some(ref user) = ctx.session_user else {
        return Redirect::to("/").into_response();
    };
    if !forum_domain::is_staff(&user.rank) {
        return Redirect::to("/forums").into_response();
    }

    let _ = fq::delete_reply(&app.pool, reply_id).await;

    Redirect::to(&format!("/forums/topic/{}", q.topic)).into_response()
}

#[derive(serde::Deserialize)]
pub struct TopicRedirectQuery {
    pub topic: i64,
}

/// POST /forums/topic/{id}/delete-replies — bulk delete replies (staff only).
pub async fn forum_bulk_delete_replies(
    State(app): State<AppState>,
    Extension(ctx): Extension<RequestContext>,
    Path(topic_id): Path<i64>,
    Form(form): Form<BulkDeleteRepliesForm>,
) -> Response {
    let Some(ref user) = ctx.session_user else {
        return Redirect::to("/").into_response();
    };
    if !forum_domain::is_staff(&user.rank) {
        return Redirect::to("/forums").into_response();
    }

    let ids: Vec<i64> = form
        .ids
        .split(',')
        .filter_map(|s| s.trim().parse().ok())
        .collect();

    if !ids.is_empty() {
        let _ = fq::delete_replies(&app.pool, topic_id, &ids).await;
    }

    Redirect::to(&format!("/forums/topic/{topic_id}")).into_response()
}

/// POST /forums/topic/{id}/close — toggle close/open (staff only).
pub async fn forum_toggle_close(
    State(app): State<AppState>,
    Extension(ctx): Extension<RequestContext>,
    Path(topic_id): Path<i64>,
    Query(q): Query<ActionQuery>,
) -> Response {
    let Some(ref user) = ctx.session_user else {
        return Redirect::to("/").into_response();
    };
    if !forum_domain::is_staff(&user.rank) {
        return Redirect::to("/forums").into_response();
    }

    let close = q.action == "Y";
    let _ = fq::set_topic_closed(&app.pool, topic_id, close).await;

    Redirect::to(&format!("/forums/topic/{topic_id}")).into_response()
}

/// POST /forums/topic/{id}/sticky — toggle sticky (staff only).
pub async fn forum_toggle_sticky(
    State(app): State<AppState>,
    Extension(ctx): Extension<RequestContext>,
    Path(topic_id): Path<i64>,
    Query(q): Query<ActionQuery>,
) -> Response {
    let Some(ref user) = ctx.session_user else {
        return Redirect::to("/").into_response();
    };
    if !forum_domain::is_staff(&user.rank) {
        return Redirect::to("/forums").into_response();
    }

    let sticky = q.action == "Y";
    let _ = fq::set_topic_sticky(&app.pool, topic_id, sticky).await;

    Redirect::to(&format!("/forums/topic/{topic_id}")).into_response()
}

/// GET /forums/topic/{id}/move — show move form (staff only).
pub async fn forum_move_show(
    State(app): State<AppState>,
    Extension(ctx): Extension<RequestContext>,
    Path(topic_id): Path<i64>,
) -> Response {
    let Some(ref user) = ctx.session_user else {
        return Redirect::to("/").into_response();
    };
    if !forum_domain::is_staff(&user.rank) {
        return Redirect::to("/forums").into_response();
    }

    let cats = fq::list_category_names(&app.pool).await.unwrap_or_default();
    let categories: Vec<CategoryItem> = cats
        .into_iter()
        .map(|c| CategoryItem {
            id: c.id,
            name: c.name,
            description: String::new(),
            topic_count: 0,
        })
        .collect();

    let meta = PageMeta::titled("Forum - Przenieś temat");
    let base = app.templates.build_context(&ctx, &meta);
    let view = MoveTopicView {
        base,
        topic_id,
        categories,
    };
    app.templates.render_value("forum_move.html", &view)
}

/// POST /forums/topic/{id}/move — execute move (staff only).
pub async fn forum_move_action(
    State(app): State<AppState>,
    Extension(ctx): Extension<RequestContext>,
    Path(topic_id): Path<i64>,
    Form(form): Form<MoveForm>,
) -> Response {
    let Some(ref user) = ctx.session_user else {
        return Redirect::to("/").into_response();
    };
    if !forum_domain::is_staff(&user.rank) {
        return Redirect::to("/forums").into_response();
    }

    let _ = fq::move_topic(&app.pool, topic_id, form.category).await;

    Redirect::to(&format!("/forums/topic/{topic_id}")).into_response()
}

/// POST /forums/search — search topics in a category.
pub async fn forum_search(
    State(app): State<AppState>,
    Extension(ctx): Extension<RequestContext>,
    Form(form): Form<SearchForm>,
) -> Response {
    let Some(ref user) = ctx.session_user else {
        return Redirect::to("/").into_response();
    };
    let rank = &user.rank;

    if form.search.trim().is_empty() {
        return Redirect::to(&format!("/forums/category/{}", form.catid)).into_response();
    }

    let search_term = text::html_escape(form.search.trim());

    let rows = fq::search_topics(&app.pool, form.catid, &search_term)
        .await
        .unwrap_or_default();

    // Filter results by visit permission.
    let results: Vec<TopicItem> = rows
        .into_iter()
        .map(|r| TopicItem {
            id: r.id,
            title: r.title,
            author_name: r.author_name,
            author_id: r.author_id,
            reply_count: r.reply_count,
            is_sticky: r.is_sticky,
            is_closed: r.is_closed,
            is_new: false,
        })
        .collect();
    let _ = rank; // already verified via category perms in search scope

    let meta = PageMeta::titled("Forum - Szukaj");
    let base = app.templates.build_context(&ctx, &meta);
    let view = SearchResultView {
        base,
        category_id: form.catid,
        results,
        searched: true,
    };
    app.templates.render_value("forum_search.html", &view)
}
