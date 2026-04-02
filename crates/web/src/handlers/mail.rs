//! Mail handlers — inbox, saved, compose, read thread, search, admin forward.

use axum::extract::{Query, State};
use axum::response::{IntoResponse, Redirect, Response};
use axum::{Extension, Form};

use vallheru_data::queries::mail as mq;
use vallheru_domain::social::mail as mail_domain;
use vallheru_domain::text;

use crate::middleware::context::RequestContext;
use crate::page::PageMeta;
use crate::state::AppState;

// =========================================================================
// View models
// =========================================================================

#[derive(serde::Serialize)]
pub struct MailIndexView {
    #[serde(flatten)]
    pub base: crate::render::RenderContext,
}

#[derive(serde::Serialize)]
pub struct MailListView {
    #[serde(flatten)]
    pub base: crate::render::RenderContext,
    pub box_type: String,
    pub threads: Vec<ThreadView>,
    pub page: i64,
    pub total_pages: i64,
    pub extra_query: String,
}

#[derive(serde::Serialize)]
pub struct ThreadView {
    pub id: i64,
    pub topic_id: i64,
    pub sender_name: String,
    pub sender_id: i64,
    pub subject: String,
    pub is_unread: bool,
}

#[derive(serde::Serialize)]
pub struct MailReadView {
    #[serde(flatten)]
    pub base: crate::render::RenderContext,
    pub subject: String,
    pub topic_id: i64,
    pub messages: Vec<MessageDetailView>,
    pub receiver_id: i64,
    pub page: i64,
    pub total_pages: i64,
    pub single_message: bool,
}

#[derive(serde::Serialize)]
pub struct MessageDetailView {
    pub id: i64,
    pub sender_name: String,
    pub sender_id: i64,
    pub body: String,
    pub date: String,
    pub is_saved: bool,
}

#[derive(serde::Serialize)]
pub struct MailComposeView {
    #[serde(flatten)]
    pub base: crate::render::RenderContext,
    pub contacts: Vec<ContactView>,
    pub to: String,
    pub subject: String,
    pub body: String,
    pub topic_id: i64,
}

#[derive(serde::Serialize)]
pub struct ContactView {
    pub id: i64,
    pub name: String,
}

#[derive(serde::Serialize)]
pub struct MailSearchView {
    #[serde(flatten)]
    pub base: crate::render::RenderContext,
    pub results: Vec<ThreadView>,
    pub has_results: bool,
    pub searched: bool,
    pub page: i64,
    pub total_pages: i64,
    pub query: String,
}

#[derive(serde::Serialize)]
pub struct MailForwardView {
    #[serde(flatten)]
    pub base: crate::render::RenderContext,
    pub message_id: i64,
    pub staff: Vec<ContactView>,
}

// =========================================================================
// Query / form types
// =========================================================================

#[derive(serde::Deserialize)]
pub struct PageQuery {
    #[serde(default = "default_page")]
    pub page: i64,
}

fn default_page() -> i64 {
    1
}

#[derive(serde::Deserialize)]
pub struct ReadQuery {
    pub topic: i64,
    #[serde(default = "default_page")]
    pub page: i64,
    #[serde(default)]
    pub single: bool,
}

#[derive(serde::Deserialize)]
pub struct SingleReadQuery {
    pub id: i64,
}

#[derive(serde::Deserialize)]
pub struct ComposeForm {
    #[serde(default)]
    pub to: String,
    #[serde(default)]
    pub player: i64,
    #[serde(default)]
    pub subject: String,
    #[serde(default)]
    pub body: String,
    #[serde(default)]
    pub topic: i64,
}

#[derive(serde::Deserialize)]
pub struct BulkActionForm {
    #[serde(default)]
    pub action: String,
    #[serde(default)]
    pub ids: String,
}

#[derive(serde::Deserialize)]
pub struct DeleteOldForm {
    #[serde(default)]
    pub days: i32,
}

#[derive(serde::Deserialize)]
pub struct BlockQuery {
    pub player_id: i64,
}

#[derive(serde::Deserialize)]
pub struct SaveQuery {
    pub id: i64,
}

#[derive(serde::Deserialize)]
pub struct DeleteQuery {
    pub id: i64,
}

#[derive(serde::Deserialize)]
pub struct ForwardForm {
    pub message_id: i64,
    pub staff_id: i64,
}

#[derive(serde::Deserialize)]
pub struct SearchQuery {
    #[serde(default)]
    pub q: String,
    #[serde(default = "default_page")]
    pub page: i64,
}

fn default_box() -> String {
    "inbox".to_owned()
}

#[derive(serde::Deserialize)]
pub struct BoxQuery {
    #[serde(default = "default_box", rename = "box")]
    pub box_type: String,
}

#[derive(serde::Deserialize)]
pub struct ComposeQuery {
    #[serde(default)]
    pub to: String,
    #[serde(default)]
    pub topic: i64,
    #[serde(default)]
    pub subject: String,
}

// =========================================================================
// Handlers
// =========================================================================

/// GET /mail — main menu: inbox, saved, write, search.
pub async fn mail_index(
    State(app): State<AppState>,
    Extension(ctx): Extension<RequestContext>,
) -> Response {
    let meta = PageMeta::titled("Poczta");
    let base = app.templates.build_context(&ctx, &meta);
    let view = MailIndexView { base };
    app.templates.render_value("mail.html", &view)
}

/// GET /mail/inbox — inbox message list.
pub async fn mail_inbox(
    State(app): State<AppState>,
    Extension(ctx): Extension<RequestContext>,
    Query(pq): Query<PageQuery>,
) -> Response {
    let Some(ref user) = ctx.session_user else {
        return Redirect::to("/").into_response();
    };
    let player_id = user.id;
    let count = mq::count_inbox_topics(&app.pool, player_id)
        .await
        .unwrap_or(0);
    let total = mail_domain::total_pages(count, mail_domain::MESSAGES_PER_PAGE);
    let page = mail_domain::clamp_page(pq.page, total);
    let offset = (page - 1) * mail_domain::MESSAGES_PER_PAGE;

    let rows = mq::list_inbox_threads(&app.pool, player_id, mail_domain::MESSAGES_PER_PAGE, offset)
        .await
        .unwrap_or_default();

    let threads = rows
        .into_iter()
        .map(|r| {
            let (display_name, display_id) = thread_display(&r, player_id);
            ThreadView {
                id: r.id,
                topic_id: r.topic_id,
                sender_name: display_name,
                sender_id: display_id,
                subject: r.subject,
                is_unread: !r.is_read,
            }
        })
        .collect();

    let meta = PageMeta::titled("Poczta - Skrzynka");
    let base = app.templates.build_context(&ctx, &meta);
    let view = MailListView {
        base,
        box_type: "inbox".to_owned(),
        threads,
        page,
        total_pages: total,
        extra_query: String::new(),
    };
    app.templates.render_value("mail_list.html", &view)
}

/// GET /mail/saved — saved message list.
pub async fn mail_saved(
    State(app): State<AppState>,
    Extension(ctx): Extension<RequestContext>,
    Query(pq): Query<PageQuery>,
) -> Response {
    let Some(ref user) = ctx.session_user else {
        return Redirect::to("/").into_response();
    };
    let player_id = user.id;
    let count = mq::count_saved_topics(&app.pool, player_id)
        .await
        .unwrap_or(0);
    let total = mail_domain::total_pages(count, mail_domain::MESSAGES_PER_PAGE);
    let page = mail_domain::clamp_page(pq.page, total);
    let offset = (page - 1) * mail_domain::MESSAGES_PER_PAGE;

    let rows = mq::list_saved_threads(&app.pool, player_id, mail_domain::MESSAGES_PER_PAGE, offset)
        .await
        .unwrap_or_default();

    let threads = rows
        .into_iter()
        .map(|r| ThreadView {
            id: r.id,
            topic_id: r.topic_id,
            sender_name: r.sender_name.clone(),
            sender_id: r.sender_id,
            subject: r.subject,
            is_unread: false,
        })
        .collect();

    let meta = PageMeta::titled("Poczta - Zapisane");
    let base = app.templates.build_context(&ctx, &meta);
    let view = MailListView {
        base,
        box_type: "saved".to_owned(),
        threads,
        page,
        total_pages: total,
        extra_query: String::new(),
    };
    app.templates.render_value("mail_list.html", &view)
}

/// GET /mail/read — read a thread.
pub async fn mail_read(
    State(app): State<AppState>,
    Extension(ctx): Extension<RequestContext>,
    Query(rq): Query<ReadQuery>,
) -> Response {
    let Some(ref user) = ctx.session_user else {
        return Redirect::to("/").into_response();
    };
    let player_id = user.id;

    // Mark thread as read.
    if let Err(e) = mq::mark_thread_read(&app.pool, player_id, rq.topic).await {
        tracing::warn!(topic = rq.topic, error = %e, "Failed to mark thread as read");
    }

    let count = mq::count_thread_messages(&app.pool, player_id, rq.topic)
        .await
        .unwrap_or(0);
    let total = mail_domain::total_pages(count, mail_domain::THREAD_PAGE_SIZE);
    // Default to last page (newest messages).
    let page = if rq.page == 0 {
        total
    } else {
        mail_domain::clamp_page(rq.page, total)
    };
    let offset = (page - 1) * mail_domain::THREAD_PAGE_SIZE;

    let rows = mq::list_thread_messages(
        &app.pool,
        player_id,
        rq.topic,
        mail_domain::THREAD_PAGE_SIZE,
        offset,
    )
    .await
    .unwrap_or_default();

    if rows.is_empty() {
        return Redirect::to("/mail/inbox").into_response();
    }

    let subject = rows[0].subject.clone();
    let mut receiver_id: i64 = 0;
    let messages: Vec<MessageDetailView> = rows
        .into_iter()
        .map(|r| {
            if receiver_id == 0 {
                if r.sender_id != player_id {
                    receiver_id = r.sender_id;
                } else if r.recipient_id != player_id {
                    receiver_id = r.recipient_id;
                }
            }
            MessageDetailView {
                id: r.id,
                sender_name: r.sender_name,
                sender_id: r.sender_id,
                body: r.body,
                date: r.created_at_formatted,
                is_saved: r.is_saved,
            }
        })
        .collect();

    let meta = PageMeta::titled("Poczta - Czytaj").with_js("/js/editor.js");
    let base = app.templates.build_context(&ctx, &meta);
    let view = MailReadView {
        base,
        subject,
        topic_id: rq.topic,
        messages,
        receiver_id,
        page,
        total_pages: total,
        single_message: rq.single,
    };
    app.templates.render_value("mail_read.html", &view)
}

/// GET /mail/compose — compose form.
pub async fn mail_compose(
    State(app): State<AppState>,
    Extension(ctx): Extension<RequestContext>,
    Query(params): Query<ComposeQuery>,
) -> Response {
    let Some(ref user) = ctx.session_user else {
        return Redirect::to("/").into_response();
    };
    let player_id = user.id;
    let contacts = load_contacts(&app, player_id).await;

    let meta = PageMeta::titled("Poczta - Napisz").with_js("/js/editor.js");
    let base = app.templates.build_context(&ctx, &meta);
    let view = MailComposeView {
        base,
        contacts,
        to: params.to,
        subject: params.subject,
        body: String::new(),
        topic_id: params.topic,
    };
    app.templates.render_value("mail_compose.html", &view)
}

/// POST /mail/send — send a message.
pub async fn mail_send(
    State(app): State<AppState>,
    Extension(ctx): Extension<RequestContext>,
    Form(form): Form<ComposeForm>,
) -> Response {
    let Some(ref user) = ctx.session_user else {
        return Redirect::to("/").into_response();
    };
    let player_id = user.id;
    let player_name = &user.name;

    // Resolve recipient ID — prefer contact dropdown over text field.
    let recipient_id = if form.player > 0 {
        form.player
    } else {
        form.to.trim().parse().unwrap_or(0)
    };

    // Validate.
    if let Err(msg) = mail_domain::validate_compose(player_id, recipient_id, &form.body) {
        return error_redirect(msg);
    }

    // Check recipient exists.
    let Ok(Some(recipient_name)) = mq::player_exists(&app.pool, recipient_id).await else {
        return error_redirect("Nie ma takiego gracza.");
    };

    // Check if blocked by recipient.
    if mq::is_mail_blocked(&app.pool, recipient_id, player_id)
        .await
        .unwrap_or(false)
    {
        return error_redirect("Nie możesz wysyłać listów, ponieważ zostałeś zablokowany!");
    }

    // Process body through BBCode.
    let bad_words = vec![];
    let processed_body = text::bbcode_to_html(&form.body, &bad_words, false);
    let subject = mail_domain::normalise_subject(&form.subject, &form.body);

    // Determine topic — new or reply.
    let topic_id = if form.topic > 0 {
        form.topic
    } else {
        mq::next_topic_id(&app.pool).await.unwrap_or(1)
    };

    // Insert both copies atomically.
    if let Err(e) = mq::send_message_pair_tx(
        &app.pool,
        &mq::InsertMessageParams {
            sender_id: player_id,
            sender_name: player_name,
            owner_id: recipient_id,
            recipient_id,
            recipient_name: &recipient_name,
            topic_id,
            subject: &subject,
            body: &processed_body,
            is_read: false,
        },
        &mq::InsertMessageParams {
            sender_id: player_id,
            sender_name: player_name,
            owner_id: player_id,
            recipient_id,
            recipient_name: &recipient_name,
            topic_id,
            subject: &subject,
            body: &processed_body,
            is_read: true,
        },
    )
    .await
    {
        tracing::error!("Failed to send mail: {e}");
        return error_redirect("Wystąpił błąd podczas wysyłania wiadomości.");
    }

    let redirect_url = format!("/mail/read?topic={topic_id}");
    Redirect::to(&redirect_url).into_response()
}

/// POST /mail/bulk — delete, mark read, mark unread.
pub async fn mail_bulk(
    State(app): State<AppState>,
    Extension(ctx): Extension<RequestContext>,
    Query(bq): Query<BoxQuery>,
    Form(form): Form<BulkActionForm>,
) -> Response {
    let Some(ref user) = ctx.session_user else {
        return Redirect::to("/").into_response();
    };
    let player_id = user.id;
    let box_type = &bq.box_type;

    // Parse selected IDs (comma-separated topic IDs).
    let ids: Vec<i64> = form
        .ids
        .split(',')
        .filter_map(|s| s.trim().parse().ok())
        .collect();

    if !ids.is_empty() {
        match form.action.as_str() {
            "delete" => {
                if let Err(e) = mq::delete_by_topics(&app.pool, player_id, &ids).await {
                    tracing::error!(error = %e, "Failed to delete mail topics");
                }
            }
            "read" => {
                if let Err(e) = mq::mark_messages_read(&app.pool, player_id, &ids).await {
                    tracing::error!(error = %e, "Failed to mark messages as read");
                }
            }
            "unread" => {
                if let Err(e) = mq::mark_messages_unread(&app.pool, player_id, &ids).await {
                    tracing::error!(error = %e, "Failed to mark messages as unread");
                }
            }
            _ => {}
        }
    }

    let redirect = if box_type == "saved" {
        "/mail/saved"
    } else {
        "/mail/inbox"
    };
    Redirect::to(redirect).into_response()
}

/// POST /mail/delete-old — delete messages older than N days.
pub async fn mail_delete_old(
    State(app): State<AppState>,
    Extension(ctx): Extension<RequestContext>,
    Query(bq): Query<BoxQuery>,
    Form(form): Form<DeleteOldForm>,
) -> Response {
    let Some(ref user) = ctx.session_user else {
        return Redirect::to("/").into_response();
    };
    let player_id = user.id;
    let box_type = &bq.box_type;

    if mail_domain::validate_delete_days(form.days) {
        if box_type == "saved" {
            if let Err(e) = mq::delete_old_saved(&app.pool, player_id, form.days).await {
                tracing::error!(error = %e, "Failed to delete old saved messages");
            }
        } else if let Err(e) = mq::delete_old_inbox(&app.pool, player_id, form.days).await {
            tracing::error!(error = %e, "Failed to delete old inbox messages");
        }
    }

    let redirect = if box_type == "saved" {
        "/mail/saved"
    } else {
        "/mail/inbox"
    };
    Redirect::to(redirect).into_response()
}

/// POST /mail/clear — clear inbox or saved.
pub async fn mail_clear(
    State(app): State<AppState>,
    Extension(ctx): Extension<RequestContext>,
    Query(bq): Query<BoxQuery>,
) -> Response {
    let Some(ref user) = ctx.session_user else {
        return Redirect::to("/").into_response();
    };
    let player_id = user.id;
    let box_type = &bq.box_type;

    if box_type == "saved" {
        if let Err(e) = mq::clear_saved(&app.pool, player_id).await {
            tracing::error!(error = %e, "Failed to clear saved mailbox");
        }
    } else if let Err(e) = mq::clear_inbox(&app.pool, player_id).await {
        tracing::error!(error = %e, "Failed to clear inbox");
    }

    let redirect = if box_type == "saved" {
        "/mail/saved"
    } else {
        "/mail/inbox"
    };
    Redirect::to(redirect).into_response()
}

/// POST /mail/save — bookmark a message.
pub async fn mail_save_msg(
    State(app): State<AppState>,
    Extension(ctx): Extension<RequestContext>,
    Query(sq): Query<SaveQuery>,
) -> Response {
    let Some(ref user) = ctx.session_user else {
        return Redirect::to("/").into_response();
    };
    if let Err(e) = mq::save_message(&app.pool, user.id, sq.id).await {
        tracing::error!(message_id = sq.id, error = %e, "Failed to save message");
    }
    Redirect::to("/mail/inbox").into_response()
}

/// POST /mail/delete — delete a single message.
pub async fn mail_delete_msg(
    State(app): State<AppState>,
    Extension(ctx): Extension<RequestContext>,
    Query(dq): Query<DeleteQuery>,
) -> Response {
    let Some(ref user) = ctx.session_user else {
        return Redirect::to("/").into_response();
    };
    if let Err(e) = mq::delete_message(&app.pool, user.id, dq.id).await {
        tracing::error!(message_id = dq.id, error = %e, "Failed to delete message");
    }
    Redirect::to("/mail/inbox").into_response()
}

/// POST /mail/block — toggle mail block on a player.
pub async fn mail_block(
    State(app): State<AppState>,
    Extension(ctx): Extension<RequestContext>,
    Query(bq): Query<BlockQuery>,
) -> Response {
    let Some(ref user) = ctx.session_user else {
        return Redirect::to("/").into_response();
    };
    if let Err(e) = mq::toggle_mail_block(&app.pool, user.id, bq.player_id).await {
        tracing::error!(player_id = bq.player_id, error = %e, "Failed to toggle mail block");
    }
    Redirect::to("/mail/inbox").into_response()
}

/// GET /mail/search — search messages.
pub async fn mail_search(
    State(app): State<AppState>,
    Extension(ctx): Extension<RequestContext>,
    Query(sq): Query<SearchQuery>,
) -> Response {
    let Some(ref user) = ctx.session_user else {
        return Redirect::to("/").into_response();
    };
    let player_id = user.id;
    let meta = PageMeta::titled("Poczta - Szukaj");
    let base = app.templates.build_context(&ctx, &meta);

    if sq.q.is_empty() {
        let view = MailSearchView {
            base,
            results: vec![],
            has_results: false,
            searched: false,
            page: 1,
            total_pages: 1,
            query: String::new(),
        };
        return app.templates.render_value("mail_search.html", &view);
    }

    let sanitised: String = sq.q.chars().take(100).collect();
    let count = mq::count_search_results(&app.pool, player_id, &sanitised)
        .await
        .unwrap_or(0);
    let total = mail_domain::total_pages(count, mail_domain::MESSAGES_PER_PAGE);
    let page = mail_domain::clamp_page(sq.page, total);
    let offset = (page - 1) * mail_domain::MESSAGES_PER_PAGE;

    let rows = mq::search_messages(
        &app.pool,
        player_id,
        &sanitised,
        mail_domain::MESSAGES_PER_PAGE,
        offset,
    )
    .await
    .unwrap_or_default();

    let results = rows
        .into_iter()
        .map(|r| ThreadView {
            id: r.id,
            topic_id: r.topic_id,
            sender_name: r.sender_name,
            sender_id: r.sender_id,
            subject: r.subject,
            is_unread: !r.is_read,
        })
        .collect();

    let view = MailSearchView {
        base,
        results,
        has_results: count > 0,
        searched: true,
        page,
        total_pages: total,
        query: sanitised,
    };
    app.templates.render_value("mail_search.html", &view)
}

/// GET /mail/forward — show forward-to-staff form.
pub async fn mail_forward_show(
    State(app): State<AppState>,
    Extension(ctx): Extension<RequestContext>,
    Query(q): Query<SingleReadQuery>,
) -> Response {
    let staff_rows = mq::list_staff(&app.pool).await.unwrap_or_default();
    let staff = staff_rows
        .into_iter()
        .map(|r| ContactView {
            id: r.id,
            name: r.username,
        })
        .collect();

    let meta = PageMeta::titled("Poczta - Wyślij do władcy");
    let base = app.templates.build_context(&ctx, &meta);
    let view = MailForwardView {
        base,
        message_id: q.id,
        staff,
    };
    app.templates.render_value("mail_forward.html", &view)
}

/// POST /mail/forward — forward a message to a staff member.
pub async fn mail_forward_action(
    State(app): State<AppState>,
    Extension(ctx): Extension<RequestContext>,
    Form(form): Form<ForwardForm>,
) -> Response {
    let Some(ref user) = ctx.session_user else {
        return Redirect::to("/").into_response();
    };
    let player_id = user.id;
    let player_name = &user.name;

    // Verify the staff recipient.
    let Ok(Some(staff_name)) = mq::player_exists(&app.pool, form.staff_id).await else {
        return error_redirect("Nie ma takiego gracza!");
    };

    // Get the original message.
    let Ok(Some(msg)) = mq::get_message(&app.pool, player_id, form.message_id).await else {
        return error_redirect("Nie ma takiej wiadomości.");
    };

    // Build forwarded body.
    let body = format!(
        "<b>Temat</b>: {}<br/><b>Data wysłania</b>: {}<br/><br/>{} napisał(a):<br/>{}",
        text::html_escape(&msg.subject),
        text::html_escape(&msg.created_at_formatted),
        text::html_escape(&msg.sender_name),
        msg.body,
    );
    let subject = format!("List gracza {player_name} o ID:{player_id}");
    let topic_id = mq::next_topic_id(&app.pool).await.unwrap_or(1);

    if let Err(e) = mq::insert_message(
        &app.pool,
        &mq::InsertMessageParams {
            sender_id: player_id,
            sender_name: player_name,
            owner_id: form.staff_id,
            recipient_id: form.staff_id,
            recipient_name: &staff_name,
            topic_id,
            subject: &subject,
            body: &body,
            is_read: false,
        },
    )
    .await
    {
        tracing::error!(error = %e, "Failed to send contact staff message");
    }

    Redirect::to(&format!("/mail/read?topic={}", msg.topic_id)).into_response()
}

// =========================================================================
// Helpers
// =========================================================================

fn error_redirect(msg: &str) -> Response {
    // Simple percent-encoding for the message.
    let encoded: String = msg
        .bytes()
        .flat_map(|b| {
            if b.is_ascii_alphanumeric() || b == b'-' || b == b'_' || b == b'.' || b == b'~' {
                vec![b as char]
            } else {
                format!("%{b:02X}").chars().collect()
            }
        })
        .collect();
    Redirect::to(&format!("/mail?error={encoded}")).into_response()
}

async fn load_contacts(app: &AppState, player_id: i64) -> Vec<ContactView> {
    mq::list_contacts(&app.pool, player_id)
        .await
        .unwrap_or_default()
        .into_iter()
        .map(|c| ContactView {
            id: c.player_id,
            name: c.player_name,
        })
        .collect()
}

/// For inbox display: show the "other party" name/id.
fn thread_display(row: &mq::MailThreadRow, player_id: i64) -> (String, i64) {
    if row.sender_id == player_id && row.recipient_id != 0 {
        (row.recipient_name.clone(), row.recipient_id)
    } else {
        (row.sender_name.clone(), row.sender_id)
    }
}
