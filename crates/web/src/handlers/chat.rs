//! Chat (tavern / inn) handlers.
//!
//! Ported from `chat.php` (main page + send + admin) and `chatmsgs.php`
//! (AJAX message fetch).  The original PHP uses an iframe + polling for
//! messages; here we keep a similar polling endpoint while serving the
//! page shell as a normal `MiniJinja` render.

use std::collections::HashMap;
use std::time::{SystemTime, UNIX_EPOCH};

use axum::extract::{Query, State};
use axum::response::{IntoResponse, Response};
use axum::{Extension, Form};

use vallheru_data::queries::chat as q;
use vallheru_data::queries::tribe_forum as tfq;
use vallheru_domain::social::chat::{
    self as chat_domain, DEFAULT_CHAT_LENGTH, MAX_CHAT_LENGTH, MessageTarget,
};
use vallheru_domain::text;

use crate::middleware::context::RequestContext;
use crate::page::PageMeta;
use crate::state::AppState;

// =========================================================================
// View models
// =========================================================================

#[derive(serde::Serialize)]
pub struct ChatPageView {
    #[serde(flatten)]
    pub base: crate::render::RenderContext,
    pub rank: String,
    pub chat_length: i32,
}

#[derive(serde::Serialize)]
pub struct ChatMessagesView {
    pub messages: Vec<MessageView>,
    pub online_players: Vec<OnlinePlayerView>,
    pub total_messages: i64,
    pub online_count: usize,
    pub show_admin: bool,
    pub player_id: i64,
    pub chat_length: i32,
    pub active_tab: i64,
    pub tabs: Vec<TabView>,
}

#[derive(serde::Serialize)]
pub struct MessageView {
    pub id: i64,
    pub author_html: String,
    pub body: String,
    pub sender_id: i64,
    pub time_ago: String,
}

#[derive(serde::Serialize)]
pub struct OnlinePlayerView {
    pub id: i64,
    pub name: String,
}

#[derive(serde::Serialize)]
pub struct TabView {
    pub id: i64,
    pub label: String,
    pub active: bool,
    pub has_new: bool,
}

// =========================================================================
// Form types
// =========================================================================

#[derive(serde::Deserialize)]
pub struct ChatSendForm {
    pub msg: Option<String>,
}

#[derive(serde::Deserialize)]
pub struct ChatAdminBanForm {
    pub banid: Option<i64>,
    pub ban: Option<String>,
    pub duration: Option<i32>,
    pub verdict: Option<String>,
}

#[derive(serde::Deserialize)]
pub struct ChatAdminGiveForm {
    pub giveid: Option<i64>,
    pub item: Option<String>,
    pub item2: Option<String>,
    pub innkeeper: Option<String>,
}

#[derive(serde::Deserialize)]
pub struct ChatDeleteQuery {
    pub tid: Option<i64>,
}

#[derive(serde::Deserialize)]
pub struct ChatQuery {
    pub more: Option<String>,
    pub less: Option<String>,
    pub close: Option<String>,
    pub tab: Option<i64>,
}

// =========================================================================
// GET /chat — main chat page
// =========================================================================

pub async fn chat_page(
    State(app): State<AppState>,
    Extension(ctx): Extension<RequestContext>,
    Query(query): Query<ChatQuery>,
) -> Response {
    let Some(ref user) = ctx.session_user else {
        return crate::page::redirect("/login");
    };

    // Mark player as on the Chat page.
    if let Err(e) = q::set_page_chat(&app.pool, user.id).await {
        tracing::warn!("Failed to set chat page marker: {e}");
    }

    // Session-like state: we store chat_length as a query param round-trip.
    // For simplicity, default to 25 and use query params to adjust.
    let mut chat_length = query
        .more
        .as_ref()
        .map(|_| DEFAULT_CHAT_LENGTH + chat_domain::CHAT_LENGTH_STEP)
        .or_else(|| {
            query
                .less
                .as_ref()
                .map(|_| DEFAULT_CHAT_LENGTH - chat_domain::CHAT_LENGTH_STEP)
        })
        .unwrap_or(DEFAULT_CHAT_LENGTH);
    chat_length = chat_length.clamp(DEFAULT_CHAT_LENGTH, MAX_CHAT_LENGTH);

    let meta = PageMeta::titled("Karczma").with_js("/js/chat.js");
    let base = app.templates.build_context(&ctx, &meta);

    let view = ChatPageView {
        base,
        rank: user.rank.clone(),
        chat_length,
    };

    app.templates.render_value("chat.html", &view)
}

// =========================================================================
// GET /chat/messages — AJAX message fetch (returns HTML fragment)
// =========================================================================

#[derive(serde::Deserialize)]
pub struct MessagesQuery {
    pub length: Option<i32>,
    pub tab: Option<i64>,
}

pub async fn chat_messages(
    State(app): State<AppState>,
    Extension(ctx): Extension<RequestContext>,
    Query(query): Query<MessagesQuery>,
) -> Response {
    let Some(ref user) = ctx.session_user else {
        return (axum::http::StatusCode::UNAUTHORIZED, "").into_response();
    };

    let chat_length = query
        .length
        .unwrap_or(DEFAULT_CHAT_LENGTH)
        .clamp(DEFAULT_CHAT_LENGTH, MAX_CHAT_LENGTH);
    let active_tab = query.tab.unwrap_or(0);
    let is_admin = chat_domain::is_chat_admin(&user.rank);

    // Fetch messages based on active tab.
    let raw_messages = if active_tab == 0 {
        q::list_public_messages(&app.pool, chat_length)
            .await
            .unwrap_or_default()
    } else {
        q::list_whisper_messages(&app.pool, user.id, active_tab, 0, chat_length)
            .await
            .unwrap_or_default()
    };

    let now_epoch = current_epoch();
    let messages: Vec<MessageView> = raw_messages
        .into_iter()
        .map(|m| {
            let age = now_epoch.saturating_sub(m.created_epoch);
            let time_ago = format_time_ago(age, m.created_epoch);
            MessageView {
                id: m.id,
                author_html: m.author_html,
                body: m.body,
                sender_id: m.sender_id,
                time_ago,
            }
        })
        .collect();

    // Online players.
    let online = match q::online_in_tavern(&app.pool).await {
        Ok(v) => v,
        Err(e) => {
            tracing::error!(error = ?e, "failed to load online tavern players");
            Vec::new()
        }
    };
    let online_count = online.len();
    let online_players: Vec<OnlinePlayerView> = online
        .into_iter()
        .map(|p| OnlinePlayerView {
            id: p.id,
            name: p.username,
        })
        .collect();

    // Total public message count.
    let total_messages = match q::count_public_messages(&app.pool).await {
        Ok(v) => v,
        Err(e) => {
            tracing::error!(error = ?e, "failed to count public chat messages");
            0
        }
    };

    // Build whisper tabs.
    let tabs = build_whisper_tabs(&app, user.id, active_tab).await;

    let view = ChatMessagesView {
        messages,
        online_players,
        total_messages,
        online_count,
        show_admin: is_admin,
        player_id: user.id,
        chat_length,
        active_tab,
        tabs,
    };

    // Render the messages fragment template.
    app.templates.render_value("chat_messages.html", &view)
}

// =========================================================================
// POST /chat/send — send a message
// =========================================================================

#[allow(clippy::too_many_lines)]
pub async fn chat_send(
    State(app): State<AppState>,
    Extension(ctx): Extension<RequestContext>,
    Form(form): Form<ChatSendForm>,
) -> Response {
    let Some(ref user) = ctx.session_user else {
        return crate::page::redirect("/login");
    };

    let raw_msg = form.msg.unwrap_or_default();
    if raw_msg.trim().is_empty() {
        return crate::page::redirect_after_post("/chat");
    }

    // Check ban — fail-closed: if DB is down, deny posting.
    match q::is_banned(&app.pool, user.id).await {
        Ok(true) => {
            return error_redirect("Nie możesz pisać wiadomości na czacie.");
        }
        Err(e) => {
            tracing::error!(user_id = user.id, error = ?e, "failed to check chat ban status");
            return error_redirect("Błąd systemu. Spróbuj ponownie.");
        }
        Ok(false) => {}
    }

    // Load bad words for BBCode filter.
    let bad_words = match q::list_bad_words(&app.pool).await {
        Ok(v) => v,
        Err(e) => {
            tracing::error!(error = ?e, "failed to load bad words list");
            Vec::new()
        }
    };

    // Process BBCode.
    let processed = text::bbcode_to_html(&raw_msg, &bad_words, true);

    // Check if the processed message has visible content.
    let stripped = text::strip_tags(&processed);
    if stripped.trim().is_empty() {
        return crate::page::redirect_after_post("/chat");
    }

    // Build author label with tribe prefix/suffix.
    let tribe_id = match q::player_tribe_id(&app.pool, user.id).await {
        Ok(v) => v,
        Err(e) => {
            tracing::error!(user_id = user.id, error = ?e, "failed to load player tribe_id for chat");
            0
        }
    };
    let tags = if tribe_id > 0 {
        tfq::tribe_tags(&app.pool, tribe_id).await.ok().flatten()
    } else {
        None
    };
    let (prefix, suffix) = tags
        .as_ref()
        .map_or(("", ""), |t| (t.prefix.as_str(), t.suffix.as_str()));
    let author_label = text::chat_author_label(user.id, &user.name, &user.rank, prefix, suffix);

    // Check for @me emote.
    let (message_body, is_emote) = text::apply_emote(&processed, user.id, &user.name);
    let author = if is_emote {
        String::new()
    } else {
        author_label.clone()
    };

    // Classify: public or whisper.
    let target = chat_domain::classify_message(&raw_msg);

    let (body_to_store, recipient_id) = match &target {
        MessageTarget::Public { .. } => (message_body.clone(), 0i64),
        MessageTarget::Whisper { recipient_id, body } => {
            // Cannot whisper to self.
            if *recipient_id == user.id {
                return error_redirect("Nie możesz szeptać do siebie.");
            }
            // Verify recipient exists.
            if match q::player_name_by_id(&app.pool, *recipient_id).await {
                Ok(v) => v.is_none(),
                Err(e) => {
                    tracing::error!(error = %e, recipient_id, "Failed to verify whisper recipient");
                    true
                }
            } {
                return error_redirect("Nie znaleziono gracza o podanym ID.");
            }
            // Process the whisper body through BBCode.
            let whisper_body = text::bbcode_to_html(body, &bad_words, true);
            (whisper_body, *recipient_id)
        }
    };

    // Insert message.
    if let Err(e) =
        q::insert_message(&app.pool, &author, &body_to_store, user.id, recipient_id).await
    {
        tracing::error!("Failed to insert chat message: {e}");
        return error_redirect("Wystąpił błąd podczas wysyłania wiadomości.");
    }

    // Innkeeper bot response (public messages only).
    if recipient_id == 0 {
        // Check for throw/shoot inn actions.
        if let Some((npc_target, npc_response)) = text::check_inn_action(&raw_msg, &user.name) {
            if let Err(e) = q::insert_message(&app.pool, &npc_target, &npc_response, 0, 0).await {
                tracing::warn!("Failed to insert NPC action: {e}");
            }
        }

        // Check for bot trigger.
        if chat_domain::is_bot_message(&raw_msg) {
            // Only respond if no innkeeper player is online.
            let innkeeper_online = q::innkeeper_on_chat(&app.pool).await.ok().flatten();
            if let Some(innkeeper_name) = innkeeper_online {
                // Innkeeper player is online — Barnaba redirects.
                let barnaba_msg = format!(
                    "Karczmarza nie ma, teraz {} tu rządzi!",
                    text::strip_tags(&innkeeper_name),
                );
                if let Err(e) =
                    q::insert_message(&app.pool, "<i>Barnaba</i>", &barnaba_msg, 0, 0).await
                {
                    tracing::warn!("Failed to insert Barnaba response: {e}");
                }
            } else {
                // Bot responds.
                let bot_answer = chat_domain::bot_response(&raw_msg, &user.name, None, None);
                if let Some(answer) = bot_answer {
                    if let Err(e) =
                        q::insert_message(&app.pool, "<i>Karczmarz</i>", &answer, 0, 0).await
                    {
                        tracing::warn!("Failed to insert bot response: {e}");
                    }
                }
            }
        }
    }

    crate::page::redirect_after_post("/chat")
}

// =========================================================================
// POST /chat/admin/delete — delete a single message (admin)
// =========================================================================

pub async fn chat_admin_delete(
    State(app): State<AppState>,
    Extension(ctx): Extension<RequestContext>,
    Query(query): Query<ChatDeleteQuery>,
) -> Response {
    let Some(ref user) = ctx.session_user else {
        return crate::page::redirect("/login");
    };
    if !chat_domain::is_chat_admin(&user.rank) {
        return error_redirect("Zapomnij o tym!");
    }
    if let Some(tid) = query.tid {
        if let Err(e) = q::delete_message(&app.pool, tid).await {
            tracing::error!(message_id = tid, error = %e, "Failed to delete chat message");
        }
    }
    crate::page::redirect_after_post("/chat")
}

// =========================================================================
// POST /chat/admin/ban — ban/unban a player
// =========================================================================

pub async fn chat_admin_ban(
    State(app): State<AppState>,
    Extension(ctx): Extension<RequestContext>,
    Form(form): Form<ChatAdminBanForm>,
) -> Response {
    let Some(ref user) = ctx.session_user else {
        return crate::page::redirect("/login");
    };
    if !chat_domain::is_chat_admin(&user.rank) {
        return error_redirect("Zapomnij o tym!");
    }

    let ban_id = form.banid.unwrap_or(0);
    let action = form.ban.as_deref().unwrap_or("");
    let duration = form.duration.unwrap_or(0);

    if ban_id < 2 || duration < 1 {
        return error_redirect("Zapomnij o tym!");
    }

    match action {
        "ban" => {
            let resets = duration * 7;
            if let Err(e) = q::ban_player(&app.pool, ban_id, resets).await {
                tracing::error!(player_id = ban_id, error = %e, "Failed to ban player from chat");
            }
            // Log the ban.
            let verdict = form.verdict.as_deref().unwrap_or("");
            tracing::info!(
                player_id = ban_id,
                admin_id = user.id,
                duration,
                verdict,
                "Chat ban applied"
            );
        }
        "unban" => {
            if let Err(e) = q::unban_player(&app.pool, ban_id).await {
                tracing::error!(player_id = ban_id, error = %e, "Failed to unban player from chat");
            }
            tracing::info!(player_id = ban_id, admin_id = user.id, "Chat ban removed");
        }
        _ => {}
    }

    crate::page::redirect_after_post("/chat")
}

// =========================================================================
// POST /chat/admin/give — give item (innkeeper roleplay action)
// =========================================================================

pub async fn chat_admin_give(
    State(app): State<AppState>,
    Extension(ctx): Extension<RequestContext>,
    Form(form): Form<ChatAdminGiveForm>,
) -> Response {
    let Some(ref user) = ctx.session_user else {
        return crate::page::redirect("/login");
    };
    if !chat_domain::is_chat_admin(&user.rank) {
        return error_redirect("Zapomnij o tym!");
    }

    let give_id = form.giveid.unwrap_or(0);
    let item = form
        .item2
        .as_deref()
        .filter(|s| !s.is_empty())
        .or(form.item.as_deref())
        .unwrap_or("");
    let comment = form.innkeeper.as_deref().unwrap_or("");
    let author = format!("<i>{}</i>", text::strip_tags(&user.name));

    if give_id > 0 {
        let target_name = match q::player_name_by_id(&app.pool, give_id).await {
            Ok(v) => v.unwrap_or_default(),
            Err(e) => {
                tracing::error!(error = %e, give_id, "Failed to load player name for admin give");
                String::new()
            }
        };
        let body = format!("Proszę {target_name} oto {item} {comment}");
        if let Err(e) = q::insert_message(&app.pool, &author, &body, 0, 0).await {
            tracing::warn!(error = %e, "Failed to insert admin give message (targeted)");
        }
    } else {
        let body = format!("Uwaga! Oto {item} dla wszystkich {comment}");
        if let Err(e) = q::insert_message(&app.pool, &author, &body, 0, 0).await {
            tracing::warn!(error = %e, "Failed to insert admin give message (broadcast)");
        }
    }

    crate::page::redirect_after_post("/chat")
}

// =========================================================================
// POST /chat/admin/prune — clear all public messages
// =========================================================================

pub async fn chat_admin_prune(
    State(app): State<AppState>,
    Extension(ctx): Extension<RequestContext>,
) -> Response {
    let Some(ref user) = ctx.session_user else {
        return crate::page::redirect("/login");
    };
    if !chat_domain::is_chat_admin(&user.rank) {
        return error_redirect("Zapomnij o tym!");
    }

    if let Err(e) = q::prune_public_messages(&app.pool).await {
        tracing::error!(error = %e, "Failed to prune chat messages");
    }
    tracing::info!(admin_id = user.id, "Chat pruned");
    crate::page::redirect_after_post("/chat")
}

// =========================================================================
// Helpers
// =========================================================================

fn current_epoch() -> i64 {
    #[allow(clippy::cast_possible_truncation, clippy::cast_possible_wrap)]
    SystemTime::now()
        .duration_since(UNIX_EPOCH)
        .map_or(0, |d| d.as_secs() as i64)
}

fn format_time_ago(seconds: i64, _epoch: i64) -> String {
    // Reconstruct a rough date string from epoch for the parenthetical.
    // We don't need chrono for a simple display — just use the raw epoch.
    if seconds < 60 {
        format!("{seconds} sekund temu")
    } else if seconds < 3600 {
        let mins = seconds / 60;
        format!("{mins} minut temu")
    } else {
        let hours = seconds / 3600;
        format!("{hours} godzin temu")
    }
}

fn error_redirect(msg: &str) -> Response {
    // Encode error message as a percent-encoded query parameter.
    // Use a simple manually-safe encoding for the few error messages we have.
    let encoded: String = msg
        .bytes()
        .flat_map(|b| {
            if b.is_ascii_alphanumeric() || b == b'-' || b == b'_' || b == b'.' || b == b'~' {
                vec![b as char]
            } else if b == b' ' {
                vec!['+']
            } else {
                let hi = b >> 4;
                let lo = b & 0x0f;
                vec![
                    '%',
                    char::from_digit(u32::from(hi), 16).unwrap_or('0'),
                    char::from_digit(u32::from(lo), 16).unwrap_or('0'),
                ]
            }
        })
        .collect();
    crate::page::redirect_after_post(&format!("/chat?error={encoded}"))
}

/// Build whisper tab metadata for the current player.
async fn build_whisper_tabs(app: &AppState, player_id: i64, active_tab: i64) -> Vec<TabView> {
    let mut partner_ids: Vec<i64> = Vec::new();

    // Collect all unique whisper partners.
    if let Ok(senders) = q::whisper_senders(&app.pool, player_id).await {
        partner_ids.extend(senders);
    }
    if let Ok(recipients) = q::whisper_recipients(&app.pool, player_id).await {
        for id in recipients {
            if !partner_ids.contains(&id) {
                partner_ids.push(id);
            }
        }
    }

    // Limit to MAX_WHISPER_TABS.
    partner_ids.truncate(chat_domain::MAX_WHISPER_TABS);

    if partner_ids.is_empty() {
        return vec![];
    }

    // Look up names.
    let names = q::player_names_by_ids(&app.pool, &partner_ids)
        .await
        .unwrap_or_default();
    let name_map: HashMap<i64, String> = names.into_iter().collect();

    // Build tabs: first the "Karczma" public tab, then each whisper partner.
    let mut tabs = Vec::with_capacity(partner_ids.len() + 1);

    tabs.push(TabView {
        id: 0,
        label: "Karczma".to_owned(),
        active: active_tab == 0,
        has_new: false, // Could check count_public_since if needed.
    });

    for pid in &partner_ids {
        let label = name_map
            .get(pid)
            .cloned()
            .unwrap_or_else(|| format!("#{pid}"));
        tabs.push(TabView {
            id: *pid,
            label,
            active: active_tab == *pid,
            has_new: false,
        });
    }

    tabs
}
