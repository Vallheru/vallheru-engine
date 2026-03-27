//! Content handlers — updates, news, newspaper, polls, proposals, comments, RSS.

use axum::extract::{Path, Query, State};
use axum::response::{IntoResponse, Redirect, Response};
use axum::{Extension, Form};

use vallheru_data::queries::content as cq;
use vallheru_domain::social::content as content_domain;
use vallheru_domain::text;

use crate::middleware::context::RequestContext;
use crate::page::PageMeta;
use crate::state::AppState;

// =========================================================================
// View models
// =========================================================================

#[derive(serde::Serialize)]
pub struct UpdatesView {
    #[serde(flatten)]
    pub base: crate::render::RenderContext,
    pub updates: Vec<UpdateItem>,
    pub is_admin: bool,
}

#[derive(serde::Serialize)]
pub struct UpdateItem {
    pub id: i64,
    pub title: String,
    pub body: String,
    pub author_name: String,
    pub date: String,
    pub comment_count: i64,
}

#[derive(serde::Serialize)]
pub struct NewsView {
    #[serde(flatten)]
    pub base: crate::render::RenderContext,
    pub news_items: Vec<NewsItem>,
    pub pending_count: i64,
    pub can_add: bool,
}

#[derive(serde::Serialize)]
pub struct NewsItem {
    pub id: i64,
    pub title: String,
    pub body: String,
    pub author_name: String,
    pub date: String,
    pub comment_count: i64,
}

#[derive(serde::Serialize)]
pub struct CommentsView {
    #[serde(flatten)]
    pub base: crate::render::RenderContext,
    pub target_type: String,
    pub target_id: i64,
    pub comments: Vec<CommentItem>,
    pub page: i64,
    pub total_pages: i64,
    pub is_staff: bool,
    pub back_url: String,
}

#[derive(serde::Serialize)]
pub struct CommentItem {
    pub id: i64,
    pub author_name: String,
    pub author_id: i64,
    pub body: String,
    pub date: String,
}

#[derive(serde::Serialize)]
pub struct NewspaperView {
    #[serde(flatten)]
    pub base: crate::render::RenderContext,
    pub sections: Vec<NewspaperSection>,
    pub issue_id: i64,
    pub is_editor: bool,
}

#[derive(serde::Serialize)]
pub struct NewspaperSection {
    pub type_code: String,
    pub type_label: String,
    pub articles: Vec<ArticleSummaryItem>,
}

#[derive(serde::Serialize)]
pub struct ArticleSummaryItem {
    pub id: i64,
    pub title: String,
    pub author_name: String,
}

#[derive(serde::Serialize)]
pub struct ArticleReadView {
    #[serde(flatten)]
    pub base: crate::render::RenderContext,
    pub article: ArticleDetail,
    pub comment_count: i64,
    pub is_editor: bool,
}

#[derive(serde::Serialize)]
pub struct ArticleDetail {
    pub id: i64,
    pub title: String,
    pub body: String,
    pub author_name: String,
}

#[derive(serde::Serialize)]
pub struct NewspaperArchiveView {
    #[serde(flatten)]
    pub base: crate::render::RenderContext,
    pub issue_ids: Vec<i64>,
}

#[derive(serde::Serialize)]
pub struct PollView {
    #[serde(flatten)]
    pub base: crate::render::RenderContext,
    pub poll: Option<PollDetail>,
    pub can_vote: bool,
    pub comment_count: i64,
}

#[derive(serde::Serialize)]
pub struct PollDetail {
    pub id: i64,
    pub question: String,
    pub description: String,
    pub options: Vec<PollOptionItem>,
    pub total_votes: i64,
    pub participation_pct: f64,
    pub days_left: i32,
}

#[derive(serde::Serialize)]
pub struct PollOptionItem {
    pub id: i64,
    pub label: String,
    pub votes: i32,
    pub percent: f64,
}

#[derive(serde::Serialize)]
pub struct PollListView {
    #[serde(flatten)]
    pub base: crate::render::RenderContext,
    pub polls: Vec<PollDetail>,
}

#[derive(serde::Serialize)]
pub struct ProposalFormView {
    #[serde(flatten)]
    pub base: crate::render::RenderContext,
    pub proposal_type: String,
}

#[derive(serde::Serialize)]
pub struct AddUpdateView {
    #[serde(flatten)]
    pub base: crate::render::RenderContext,
    pub edit_id: Option<i64>,
    pub edit_title: String,
    pub edit_body: String,
}

#[derive(serde::Serialize)]
pub struct AddNewsView {
    #[serde(flatten)]
    pub base: crate::render::RenderContext,
}

#[derive(serde::Serialize)]
pub struct NewspaperEditView {
    #[serde(flatten)]
    pub base: crate::render::RenderContext,
    pub article_id: Option<i64>,
    pub article_title: String,
    pub article_body: String,
    pub article_type: String,
    pub type_options: Vec<TypeOption>,
}

#[derive(serde::Serialize)]
pub struct TypeOption {
    pub code: String,
    pub label: String,
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
pub struct CommentsQuery {
    #[serde(default = "default_page")]
    pub page: i64,
}

#[derive(serde::Deserialize)]
pub struct CommentForm {
    #[serde(default)]
    pub body: String,
}

#[derive(serde::Deserialize)]
pub struct VoteForm {
    pub answer: i64,
}

#[derive(serde::Deserialize)]
pub struct ProposalForm {
    #[serde(default)]
    pub name: String,
    #[serde(default)]
    pub data: String,
    #[serde(default)]
    pub info: String,
}

#[derive(serde::Deserialize)]
pub struct AddUpdateForm {
    #[serde(default)]
    pub addtitle: String,
    #[serde(default)]
    pub addupdate: String,
}

#[derive(serde::Deserialize)]
pub struct UpdateEditQuery {
    #[serde(default)]
    pub modify: Option<i64>,
}

#[derive(serde::Deserialize)]
pub struct AddNewsForm {
    #[serde(default)]
    pub ttitle: String,
    #[serde(default)]
    pub body: String,
}

#[derive(serde::Deserialize)]
pub struct ArticleForm {
    #[serde(default)]
    pub mtitle: String,
    #[serde(default)]
    pub mbody: String,
    #[serde(default)]
    pub mail: String,
}

// =========================================================================
// Handlers — Updates
// =========================================================================

/// GET /updates — show latest update or last 10.
pub async fn updates_page(
    State(app): State<AppState>,
    Extension(ctx): Extension<RequestContext>,
    Query(q): Query<PageQuery>,
) -> Response {
    let Some(ref user) = ctx.session_user else {
        return Redirect::to("/").into_response();
    };
    let is_admin = content_domain::can_manage_updates(&user.rank);

    let show_all = q.page > 1; // page=2+ means "show all"

    let updates_rows = if show_all {
        cq::list_updates(&app.pool, 10).await.unwrap_or_default()
    } else {
        match cq::get_latest_update(&app.pool).await {
            Ok(Some(u)) => vec![u],
            _ => vec![],
        }
    };

    let mut items = Vec::new();
    for u in updates_rows {
        let cc = cq::count_comments(&app.pool, "update", u.id)
            .await
            .unwrap_or(0);
        items.push(UpdateItem {
            id: u.id,
            title: u.title,
            body: u.body,
            author_name: u.author_name,
            date: u.published_at_formatted,
            comment_count: cc,
        });
    }

    let meta = PageMeta::titled("Wieści");
    let base = app.templates.build_context(&ctx, &meta);
    let view = UpdatesView {
        base,
        updates: items,
        is_admin,
    };
    app.templates.render_value("updates.html", &view)
}

/// GET /updates/add — show add/edit update form (admin).
pub async fn add_update_form(
    State(app): State<AppState>,
    Extension(ctx): Extension<RequestContext>,
    Query(q): Query<UpdateEditQuery>,
) -> Response {
    let Some(ref user) = ctx.session_user else {
        return Redirect::to("/").into_response();
    };
    if !content_domain::can_manage_updates(&user.rank) {
        return Redirect::to("/updates").into_response();
    }

    let (edit_id, edit_title, edit_body) = if let Some(mid) = q.modify {
        match cq::get_latest_update(&app.pool).await {
            Ok(Some(u)) if u.id == mid => (Some(u.id), u.title, text::html_to_bbcode(&u.body)),
            _ => (None, String::new(), String::new()),
        }
    } else {
        (None, String::new(), String::new())
    };

    let meta = PageMeta::titled("Dodaj wieść");
    let base = app.templates.build_context(&ctx, &meta);
    let view = AddUpdateView {
        base,
        edit_id,
        edit_title,
        edit_body,
    };
    app.templates.render_value("add_update.html", &view)
}

/// POST /updates/add — submit new or edited update.
pub async fn add_update_action(
    State(app): State<AppState>,
    Extension(ctx): Extension<RequestContext>,
    Form(form): Form<AddUpdateForm>,
) -> Response {
    let Some(ref user) = ctx.session_user else {
        return Redirect::to("/").into_response();
    };
    if !content_domain::can_manage_updates(&user.rank) {
        return Redirect::to("/updates").into_response();
    }

    let title = form.addtitle.trim();
    let body_raw = form.addupdate.trim();
    if title.is_empty() || body_raw.is_empty() {
        return Redirect::to("/updates/add").into_response();
    }

    let body = body_raw.replace('\n', "<br/>");

    let author = format!("({})", user.name);
    let _ = cq::insert_update(&app.pool, title, &body, &author).await;

    Redirect::to("/updates").into_response()
}

// =========================================================================
// Handlers — News
// =========================================================================

/// GET /news — latest news or last 10.
pub async fn news_page(
    State(app): State<AppState>,
    Extension(ctx): Extension<RequestContext>,
    Query(q): Query<PageQuery>,
) -> Response {
    let Some(ref user) = ctx.session_user else {
        return Redirect::to("/").into_response();
    };
    let can_add = content_domain::can_add_news(&user.rank);

    let show_all = q.page > 1;
    let news_rows = if show_all {
        cq::list_news(&app.pool, 10).await.unwrap_or_default()
    } else {
        match cq::get_latest_news(&app.pool).await {
            Ok(Some(n)) => vec![n],
            _ => vec![],
        }
    };

    let pending_count = cq::count_pending_news(&app.pool).await.unwrap_or(0);

    let mut items = Vec::new();
    for n in news_rows {
        let cc = cq::count_comments(&app.pool, "news", n.id)
            .await
            .unwrap_or(0);
        items.push(NewsItem {
            id: n.id,
            title: n.title,
            body: n.body,
            author_name: n.author_name,
            date: n.published_at_formatted,
            comment_count: cc,
        });
    }

    let meta = PageMeta::titled("Plotki");
    let base = app.templates.build_context(&ctx, &meta);
    let view = NewsView {
        base,
        news_items: items,
        pending_count,
        can_add,
    };
    app.templates.render_value("news.html", &view)
}

/// GET /news/add — show news submission form.
pub async fn add_news_form(
    State(app): State<AppState>,
    Extension(ctx): Extension<RequestContext>,
) -> Response {
    let Some(ref _user) = ctx.session_user else {
        return Redirect::to("/").into_response();
    };

    let meta = PageMeta::titled("Dodaj plotkę").with_js("/js/editor.js");
    let base = app.templates.build_context(&ctx, &meta);
    let view = AddNewsView { base };
    app.templates.render_value("add_news.html", &view)
}

/// POST /news/add — submit a news item.
pub async fn add_news_action(
    State(app): State<AppState>,
    Extension(ctx): Extension<RequestContext>,
    Form(form): Form<AddNewsForm>,
) -> Response {
    let Some(ref user) = ctx.session_user else {
        return Redirect::to("/").into_response();
    };

    let title = form.ttitle.trim();
    let body_raw = form.body.trim();
    if title.is_empty() || body_raw.is_empty() {
        return Redirect::to("/news/add").into_response();
    }

    let bad_words = vec![];
    let body = text::bbcode_to_html(body_raw, &bad_words, false);
    let author = format!("{} ({})", user.name, user.id);

    let _ = cq::insert_news(&app.pool, title, &body, &author, user.id).await;

    Redirect::to("/news").into_response()
}

// =========================================================================
// Handlers — Comments (unified)
// =========================================================================

/// GET `/comments/{target_type}/{target_id}` — view comments.
pub async fn comments_page(
    State(app): State<AppState>,
    Extension(ctx): Extension<RequestContext>,
    Path((target_type, target_id)): Path<(String, i64)>,
    Query(q): Query<CommentsQuery>,
) -> Response {
    let Some(ref user) = ctx.session_user else {
        return Redirect::to("/").into_response();
    };

    // Validate target type.
    if !matches!(
        target_type.as_str(),
        "news" | "update" | "newspaper" | "poll" | "library"
    ) {
        return Redirect::to("/city").into_response();
    }

    let total = cq::count_comments(&app.pool, &target_type, target_id)
        .await
        .unwrap_or(0);
    let per_page = content_domain::COMMENTS_PER_PAGE;
    #[allow(clippy::cast_possible_truncation, clippy::cast_precision_loss)]
    let total_pages = (total as f64 / per_page as f64).ceil() as i64;
    let page = q.page.clamp(1, total_pages.max(1));
    let offset = (page - 1) * per_page;

    let rows = cq::list_comments(&app.pool, &target_type, target_id, per_page, offset)
        .await
        .unwrap_or_default();

    let comments: Vec<CommentItem> = rows
        .into_iter()
        .map(|r| CommentItem {
            id: r.id,
            author_name: r.author_name,
            author_id: r.author_id,
            body: r.body,
            date: r.created_at_formatted,
        })
        .collect();

    let is_staff = matches!(user.rank.as_str(), "Admin" | "Staff");

    let back_url = match target_type.as_str() {
        "news" => "/news".to_string(),
        "update" => "/updates".to_string(),
        "newspaper" => format!("/newspaper/article/{target_id}"),
        "poll" => "/polls".to_string(),
        "library" => format!("/library/text/{target_id}"),
        _ => "/city".to_string(),
    };

    let meta = PageMeta::titled("Komentarze").with_js("/js/editor.js");
    let base = app.templates.build_context(&ctx, &meta);
    let view = CommentsView {
        base,
        target_type,
        target_id,
        comments,
        page,
        total_pages,
        is_staff,
        back_url,
    };
    app.templates.render_value("comments.html", &view)
}

/// POST `/comments/{target_type}/{target_id}` — add a comment.
pub async fn add_comment(
    State(app): State<AppState>,
    Extension(ctx): Extension<RequestContext>,
    Path((target_type, target_id)): Path<(String, i64)>,
    Form(form): Form<CommentForm>,
) -> Response {
    let Some(ref user) = ctx.session_user else {
        return Redirect::to("/").into_response();
    };

    if !matches!(
        target_type.as_str(),
        "news" | "update" | "newspaper" | "poll" | "library"
    ) {
        return Redirect::to("/city").into_response();
    }

    let body_raw = form.body.trim();
    if !body_raw.is_empty() {
        let bad_words = vec![];
        let body = text::bbcode_to_html(body_raw, &bad_words, false);
        let _ = cq::insert_comment(
            &app.pool,
            &target_type,
            target_id,
            &user.name,
            user.id,
            &body,
        )
        .await;
    }

    Redirect::to(&format!("/comments/{target_type}/{target_id}")).into_response()
}

/// POST `/comments/{target_type}/{target_id}/delete/{comment_id}` — delete a comment (staff).
pub async fn delete_comment(
    State(app): State<AppState>,
    Extension(ctx): Extension<RequestContext>,
    Path((target_type, target_id, comment_id)): Path<(String, i64, i64)>,
) -> Response {
    let Some(ref user) = ctx.session_user else {
        return Redirect::to("/").into_response();
    };

    if !matches!(user.rank.as_str(), "Admin" | "Staff") {
        return Redirect::to(&format!("/comments/{target_type}/{target_id}")).into_response();
    }

    let _ = cq::delete_comment(&app.pool, comment_id).await;

    Redirect::to(&format!("/comments/{target_type}/{target_id}")).into_response()
}

// =========================================================================
// Handlers — Newspaper
// =========================================================================

/// GET /newspaper — show current issue table of contents.
pub async fn newspaper_page(
    State(app): State<AppState>,
    Extension(ctx): Extension<RequestContext>,
) -> Response {
    let Some(ref user) = ctx.session_user else {
        return Redirect::to("/").into_response();
    };
    let is_editor = content_domain::can_edit_newspaper(&user.rank);

    let issue_id = cq::get_latest_issue_id(&app.pool).await.unwrap_or(None);
    let issue_id = issue_id.unwrap_or(0);

    let articles = if issue_id > 0 {
        cq::list_articles_by_issue(&app.pool, issue_id)
            .await
            .unwrap_or_default()
    } else {
        vec![]
    };

    let sections = build_sections(&articles);

    let meta = PageMeta::titled("Gazeta");
    let base = app.templates.build_context(&ctx, &meta);
    let view = NewspaperView {
        base,
        sections,
        issue_id,
        is_editor,
    };
    app.templates.render_value("newspaper.html", &view)
}

/// GET /newspaper/archive — list archived issue IDs.
pub async fn newspaper_archive(
    State(app): State<AppState>,
    Extension(ctx): Extension<RequestContext>,
) -> Response {
    let Some(ref _user) = ctx.session_user else {
        return Redirect::to("/").into_response();
    };

    let ids = cq::list_archive_issue_ids(&app.pool)
        .await
        .unwrap_or_default();

    let meta = PageMeta::titled("Archiwum gazety");
    let base = app.templates.build_context(&ctx, &meta);
    let view = NewspaperArchiveView {
        base,
        issue_ids: ids,
    };
    app.templates.render_value("newspaper_archive.html", &view)
}

/// GET /newspaper/issue/{id} — read a specific issue.
pub async fn newspaper_issue(
    State(app): State<AppState>,
    Extension(ctx): Extension<RequestContext>,
    Path(issue_id): Path<i64>,
) -> Response {
    let Some(ref user) = ctx.session_user else {
        return Redirect::to("/").into_response();
    };
    let is_editor = content_domain::can_edit_newspaper(&user.rank);

    let articles = cq::list_articles_by_issue(&app.pool, issue_id)
        .await
        .unwrap_or_default();
    let sections = build_sections(&articles);

    let meta = PageMeta::titled("Gazeta");
    let base = app.templates.build_context(&ctx, &meta);
    let view = NewspaperView {
        base,
        sections,
        issue_id,
        is_editor,
    };
    app.templates.render_value("newspaper.html", &view)
}

/// GET /newspaper/article/{id} — read a single article.
pub async fn newspaper_article(
    State(app): State<AppState>,
    Extension(ctx): Extension<RequestContext>,
    Path(article_id): Path<i64>,
) -> Response {
    let Some(ref user) = ctx.session_user else {
        return Redirect::to("/").into_response();
    };

    let Ok(Some(art)) = cq::get_article(&app.pool, article_id).await else {
        return Redirect::to("/newspaper").into_response();
    };

    if !art.is_published && !content_domain::can_edit_newspaper(&user.rank) {
        return Redirect::to("/newspaper").into_response();
    }

    let cc = cq::count_comments(&app.pool, "newspaper", article_id)
        .await
        .unwrap_or(0);

    let is_editor = content_domain::can_edit_newspaper(&user.rank);

    let meta = PageMeta::titled(&art.title);
    let base = app.templates.build_context(&ctx, &meta);
    let view = ArticleReadView {
        base,
        article: ArticleDetail {
            id: art.id,
            title: art.title,
            body: art.body,
            author_name: art.author_name,
        },
        comment_count: cc,
        is_editor,
    };
    app.templates.render_value("newspaper_article.html", &view)
}

/// GET /newspaper/edit — show edit form (editor). Optional ?id= for editing existing.
pub async fn newspaper_edit_form(
    State(app): State<AppState>,
    Extension(ctx): Extension<RequestContext>,
    Query(q): Query<UpdateEditQuery>,
) -> Response {
    let Some(ref user) = ctx.session_user else {
        return Redirect::to("/").into_response();
    };
    if !content_domain::can_edit_newspaper(&user.rank) {
        return Redirect::to("/newspaper").into_response();
    }

    let (article_id, article_title, article_body, article_type) = if let Some(aid) = q.modify {
        match cq::get_article(&app.pool, aid).await {
            Ok(Some(a)) => (
                Some(a.id),
                text::html_to_bbcode(&a.title),
                text::html_to_bbcode(&a.body),
                a.article_type,
            ),
            _ => (None, String::new(), String::new(), "N".to_string()),
        }
    } else {
        (None, String::new(), String::new(), "N".to_string())
    };

    let type_options: Vec<TypeOption> = content_domain::ArticleType::all()
        .iter()
        .map(|at| TypeOption {
            code: at.code().to_string(),
            label: at.label().to_string(),
        })
        .collect();

    let meta = PageMeta::titled("Redakcja gazety").with_js("/js/editor.js");
    let base = app.templates.build_context(&ctx, &meta);
    let view = NewspaperEditView {
        base,
        article_id,
        article_title,
        article_body,
        article_type,
        type_options,
    };
    app.templates.render_value("newspaper_edit.html", &view)
}

/// POST /newspaper/edit — submit article (editor).
pub async fn newspaper_edit_action(
    State(app): State<AppState>,
    Extension(ctx): Extension<RequestContext>,
    Query(q): Query<UpdateEditQuery>,
    Form(form): Form<ArticleForm>,
) -> Response {
    let Some(ref user) = ctx.session_user else {
        return Redirect::to("/").into_response();
    };
    if !content_domain::can_edit_newspaper(&user.rank) {
        return Redirect::to("/newspaper").into_response();
    }

    let title = form.mtitle.trim();
    let body_raw = form.mbody.trim();
    let atype = form.mail.trim();

    if title.is_empty() || body_raw.is_empty() {
        return Redirect::to("/newspaper/edit").into_response();
    }

    if content_domain::ArticleType::from_code(atype).is_none() {
        return Redirect::to("/newspaper/edit").into_response();
    }

    let bad_words = vec![];
    let title_html = text::bbcode_to_html(title, &bad_words, false);
    let body_html = text::bbcode_to_html(body_raw, &bad_words, false);

    if let Some(aid) = q.modify {
        let _ = cq::edit_article(&app.pool, aid, &title_html, &body_html, atype).await;
    } else {
        // New article — assign to next unpublished issue.
        let latest = cq::get_latest_issue_id(&app.pool).await.unwrap_or(None);
        let issue_id = latest.map_or(1, |id| id + 1);
        let author = format!("{} ID: {}", user.name, user.id);
        let _ =
            cq::insert_article(&app.pool, issue_id, &title_html, &body_html, &author, atype).await;
    }

    Redirect::to("/newspaper").into_response()
}

/// POST /newspaper/release — publish all pending articles (editor).
pub async fn newspaper_release(
    State(app): State<AppState>,
    Extension(ctx): Extension<RequestContext>,
) -> Response {
    let Some(ref user) = ctx.session_user else {
        return Redirect::to("/").into_response();
    };
    if !content_domain::can_edit_newspaper(&user.rank) {
        return Redirect::to("/newspaper").into_response();
    }

    let _ = cq::publish_pending_articles(&app.pool).await;

    Redirect::to("/newspaper").into_response()
}

/// POST /newspaper/article/{id}/delete — delete an article (editor).
pub async fn newspaper_delete_article(
    State(app): State<AppState>,
    Extension(ctx): Extension<RequestContext>,
    Path(article_id): Path<i64>,
) -> Response {
    let Some(ref user) = ctx.session_user else {
        return Redirect::to("/").into_response();
    };
    if !content_domain::can_edit_newspaper(&user.rank) {
        return Redirect::to("/newspaper").into_response();
    }

    let _ = cq::delete_article(&app.pool, article_id).await;

    Redirect::to("/newspaper").into_response()
}

// =========================================================================
// Handlers — Polls
// =========================================================================

/// GET /polls — show current active poll.
pub async fn polls_page(
    State(app): State<AppState>,
    Extension(ctx): Extension<RequestContext>,
) -> Response {
    let Some(ref _user) = ctx.session_user else {
        return Redirect::to("/").into_response();
    };

    let poll_detail = match cq::get_active_poll(&app.pool).await {
        Ok(Some(p)) => {
            let options = cq::get_poll_options(&app.pool, p.id)
                .await
                .unwrap_or_default();
            let total_votes: i64 = options.iter().map(|o| i64::from(o.votes)).sum();
            let items: Vec<PollOptionItem> = options
                .into_iter()
                .map(|o| PollOptionItem {
                    id: o.id,
                    label: o.label,
                    votes: o.votes,
                    percent: content_domain::vote_percentage(i64::from(o.votes), total_votes),
                })
                .collect();
            let member_base = if p.member_base > 0 {
                i64::from(p.member_base)
            } else {
                total_votes.max(1)
            };
            Some(PollDetail {
                id: p.id,
                question: p.question,
                description: p.description,
                options: items,
                total_votes,
                participation_pct: content_domain::vote_percentage(total_votes, member_base),
                days_left: p.days_left,
            })
        }
        _ => None,
    };

    let cc = if let Some(ref pd) = poll_detail {
        cq::count_comments(&app.pool, "poll", pd.id)
            .await
            .unwrap_or(0)
    } else {
        0
    };

    // Check if player already voted — using player.poll flag (simplified: always allow for now since we don't have that flag yet).
    let can_vote = poll_detail.is_some() && poll_detail.as_ref().is_some_and(|p| p.days_left > 0);

    let meta = PageMeta::titled("Hala zgromadzeń");
    let base = app.templates.build_context(&ctx, &meta);
    let view = PollView {
        base,
        poll: poll_detail,
        can_vote,
        comment_count: cc,
    };
    app.templates.render_value("polls.html", &view)
}

/// POST /polls/vote — vote on a poll option.
pub async fn polls_vote(
    State(app): State<AppState>,
    Extension(ctx): Extension<RequestContext>,
    Form(form): Form<VoteForm>,
) -> Response {
    let Some(ref _user) = ctx.session_user else {
        return Redirect::to("/").into_response();
    };

    let _ = cq::vote_poll(&app.pool, form.answer).await;

    Redirect::to("/polls").into_response()
}

/// GET /polls/history — show last 10 polls.
pub async fn polls_history(
    State(app): State<AppState>,
    Extension(ctx): Extension<RequestContext>,
) -> Response {
    let Some(ref _user) = ctx.session_user else {
        return Redirect::to("/").into_response();
    };

    let poll_rows = cq::list_recent_polls(&app.pool, 10)
        .await
        .unwrap_or_default();
    let mut polls = Vec::new();
    for p in poll_rows {
        let options = cq::get_poll_options(&app.pool, p.id)
            .await
            .unwrap_or_default();
        let total_votes: i64 = options.iter().map(|o| i64::from(o.votes)).sum();
        let items: Vec<PollOptionItem> = options
            .into_iter()
            .map(|o| PollOptionItem {
                id: o.id,
                label: o.label,
                votes: o.votes,
                percent: content_domain::vote_percentage(i64::from(o.votes), total_votes),
            })
            .collect();
        let member_base = if p.member_base > 0 {
            i64::from(p.member_base)
        } else {
            total_votes.max(1)
        };
        polls.push(PollDetail {
            id: p.id,
            question: p.question,
            description: p.description,
            options: items,
            total_votes,
            participation_pct: content_domain::vote_percentage(total_votes, member_base),
            days_left: p.days_left,
        });
    }

    let meta = PageMeta::titled("Historia ankiet");
    let base = app.templates.build_context(&ctx, &meta);
    let view = PollListView { base, polls };
    app.templates.render_value("polls_history.html", &view)
}

// =========================================================================
// Handlers — Proposals
// =========================================================================

/// GET /proposals/{type} — show proposal form.
pub async fn proposal_form(
    State(app): State<AppState>,
    Extension(ctx): Extension<RequestContext>,
    Path(ptype): Path<String>,
) -> Response {
    let Some(ref _user) = ctx.session_user else {
        return Redirect::to("/").into_response();
    };

    if content_domain::ProposalType::from_code(&ptype).is_none() {
        return Redirect::to("/city").into_response();
    }

    let meta = PageMeta::titled("Propozycje").with_js("/js/editor.js");
    let base = app.templates.build_context(&ctx, &meta);
    let view = ProposalFormView {
        base,
        proposal_type: ptype,
    };
    app.templates.render_value("proposal_form.html", &view)
}

/// POST /proposals/{type} — submit a proposal.
pub async fn proposal_submit(
    State(app): State<AppState>,
    Extension(ctx): Extension<RequestContext>,
    Path(ptype): Path<String>,
    Form(form): Form<ProposalForm>,
) -> Response {
    let Some(ref user) = ctx.session_user else {
        return Redirect::to("/").into_response();
    };

    if content_domain::ProposalType::from_code(&ptype).is_none() {
        return Redirect::to("/city").into_response();
    }

    let name = form.name.trim();
    let data = form.data.trim();
    let info = form.info.trim();

    if name.is_empty() {
        return Redirect::to(&format!("/proposals/{ptype}")).into_response();
    }

    // For description proposals, convert BBCode body to HTML.
    let processed_data = if ptype == "D" {
        let bad_words = vec![];
        text::bbcode_to_html(data, &bad_words, false)
    } else {
        data.to_string()
    };

    let _ = cq::insert_proposal(&app.pool, user.id, &ptype, name, &processed_data, info).await;

    Redirect::to("/city").into_response()
}

// =========================================================================
// RSS
// =========================================================================

/// GET /rss — RSS feed of latest updates.
pub async fn rss_feed(State(app): State<AppState>) -> Response {
    use std::fmt::Write;

    let items = cq::list_updates_for_rss(&app.pool, 5)
        .await
        .unwrap_or_default();

    let game_name = app.templates.game_name();
    let game_url = app.templates.base_url();

    let mut xml = String::from("<?xml version=\"1.0\" encoding=\"utf-8\"?>\n");
    xml.push_str("<rss version=\"2.0\" xmlns:dc=\"http://purl.org/dc/elements/1.1/\">\n");
    xml.push_str("<channel>\n");
    let _ = writeln!(xml, "<title>{game_name}</title>");
    let _ = writeln!(xml, "<link>{game_url}</link>");
    let _ = writeln!(
        xml,
        "<description>Najnowsze wieści z {game_name}</description>"
    );
    xml.push_str("<language>pl</language>\n");

    for item in &items {
        let escaped_body = text::html_escape(&item.body);
        let escaped_title = text::html_escape(&item.title);
        let escaped_author = text::html_escape(&item.author_name);
        xml.push_str("<item>\n");
        let _ = writeln!(xml, "<title>{escaped_title}</title>");
        let _ = writeln!(xml, "<dc:creator>{escaped_author}</dc:creator>");
        let _ = writeln!(xml, "<description>{escaped_body}</description>");
        let _ = writeln!(xml, "<pubDate>{}</pubDate>", item.published_at_rfc2822);
        xml.push_str("</item>\n");
    }

    xml.push_str("</channel>\n");
    xml.push_str("</rss>\n");

    (
        [("content-type", "application/rss+xml; charset=utf-8")],
        xml,
    )
        .into_response()
}

// =========================================================================
// Helpers
// =========================================================================

fn build_sections(articles: &[cq::ArticleSummaryRow]) -> Vec<NewspaperSection> {
    content_domain::ArticleType::all()
        .iter()
        .filter_map(|at| {
            let section_articles: Vec<ArticleSummaryItem> = articles
                .iter()
                .filter(|a| a.article_type == at.code())
                .map(|a| ArticleSummaryItem {
                    id: a.id,
                    title: a.title.clone(),
                    author_name: a.author_name.clone(),
                })
                .collect();
            if section_articles.is_empty() {
                None
            } else {
                Some(NewspaperSection {
                    type_code: at.code().to_string(),
                    type_label: at.label().to_string(),
                    articles: section_articles,
                })
            }
        })
        .collect()
}
