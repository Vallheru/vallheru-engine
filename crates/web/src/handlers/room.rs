//! Tavern room handlers.
//!
//! Ported from `room.php` (room page + admin panel) and `roommsgs.php`
//! (AJAX room-message fetch).  Mirrors the existing global-chat handler
//! pattern: a shell page with an iframe/AJAX message fragment endpoint.

use std::time::{SystemTime, UNIX_EPOCH};

use axum::extract::{Query, State};
use axum::response::{IntoResponse, Response};
use axum::{Extension, Form};

use vallheru_data::queries::chat as chatq;
use vallheru_data::queries::room as rq;
use vallheru_domain::social::room as room_domain;
use vallheru_domain::text;

use crate::middleware::context::RequestContext;
use crate::page::{Flash, FlashKind, PageMeta};
use crate::state::AppState;

// =========================================================================
// View models
// =========================================================================

#[derive(serde::Serialize)]
pub struct RoomPageView {
    #[serde(flatten)]
    pub base: crate::render::RenderContext,
    pub room_name: String,
    pub description: String,
    pub description_bbcode: String,
    pub days_remaining: i16,
    pub is_admin: bool,
    pub is_owner: bool,
    pub members: Vec<MemberView>,
    pub npcs: Vec<String>,
    pub has_npcs: bool,
    pub rent_options: Vec<RentOptionView>,
    pub color_options: Vec<ColorOptionView>,
    pub message_length: i32,
    pub rank: String,
}

#[derive(serde::Serialize)]
pub struct MemberView {
    pub id: i64,
    pub name: String,
}

#[derive(serde::Serialize)]
pub struct RentOptionView {
    pub days: i16,
    pub label: String,
}

#[derive(serde::Serialize)]
pub struct ColorOptionView {
    pub value: String,
    pub label: String,
}

#[derive(serde::Serialize)]
pub struct RoomMessagesView {
    pub messages: Vec<RoomMessageView>,
    pub online_players: Vec<MemberView>,
    pub online_count: usize,
    pub show_admin: bool,
    pub player_id: i64,
    pub message_length: i32,
}

#[derive(serde::Serialize)]
pub struct RoomMessageView {
    pub id: i64,
    pub author_html: String,
    pub body: String,
    pub sender_id: i64,
    pub time_ago: String,
}

// =========================================================================
// Form / query types
// =========================================================================

#[derive(serde::Deserialize, Default)]
pub struct RoomQuery {
    pub more: Option<String>,
    pub less: Option<String>,
}

#[derive(serde::Deserialize, Default)]
pub struct RoomMsgQuery {
    pub length: Option<i32>,
}

#[derive(serde::Deserialize)]
pub struct RoomSendForm {
    pub msg: Option<String>,
    pub person: Option<i32>,
}

#[derive(serde::Deserialize)]
pub struct RoomDeleteQuery {
    pub tid: Option<i64>,
}

#[derive(serde::Deserialize)]
pub struct RoomPlayerForm {
    pub pid: Option<i64>,
}

#[derive(serde::Deserialize)]
pub struct RoomDescForm {
    pub desc: Option<String>,
}

#[derive(serde::Deserialize)]
pub struct RoomNameForm {
    pub rname: Option<String>,
}

#[derive(serde::Deserialize)]
pub struct RoomNpcForm {
    pub npc: Option<String>,
}

#[derive(serde::Deserialize)]
pub struct RoomCoOwnerForm {
    pub pid: Option<i64>,
    /// 0 = add, 1 = remove.
    pub action: Option<i32>,
}

#[derive(serde::Deserialize)]
pub struct RoomColorForm {
    pub pid: Option<i64>,
    pub color: Option<String>,
}

#[derive(serde::Deserialize)]
pub struct RoomRentForm {
    pub rent: Option<i16>,
}

// =========================================================================
// GET /room — main room page
// =========================================================================

pub async fn room_page(
    State(app): State<AppState>,
    Extension(ctx): Extension<RequestContext>,
    Query(query): Query<RoomQuery>,
) -> Response {
    let Some(ref user) = ctx.session_user else {
        return crate::page::redirect("/login");
    };

    let room_id = match rq::player_room(&app.pool, user.id).await {
        Ok(id) if id > 0 => id,
        _ => {
            return error_page(
                &app,
                &ctx,
                "Nie posiadasz bądź nie zostałeś zaproszony do jakiegokolwiek pokoju.",
            );
        }
    };

    let Some(room) = rq::find_room(&app.pool, room_id).await.ok().flatten() else {
        return error_page(&app, &ctx, "Pokój nie istnieje.");
    };

    let _ = rq::set_page_room(&app.pool, user.id).await;

    let is_admin = room_domain::is_admin(room.owner_id, &room.co_owners, user.id);
    let is_owner = room_domain::is_owner(room.owner_id, user.id);

    // Message length (stateless via query params).
    let mut msg_length = room_domain::DEFAULT_MESSAGE_LENGTH;
    if query.more.is_some() {
        msg_length += room_domain::MESSAGE_LENGTH_STEP;
    }
    if query.less.is_some() {
        msg_length -= room_domain::MESSAGE_LENGTH_STEP;
    }
    msg_length = msg_length.clamp(
        room_domain::DEFAULT_MESSAGE_LENGTH,
        room_domain::MAX_MESSAGE_LENGTH,
    );

    let members_rows = rq::list_room_members(&app.pool, room_id)
        .await
        .unwrap_or_default();
    let members: Vec<MemberView> = members_rows
        .into_iter()
        .map(|m| MemberView {
            id: m.id,
            name: m.user,
        })
        .collect();

    let description_bbcode = text::html_to_bbcode(&room.description);

    let rent_options: Vec<RentOptionView> = room_domain::RENT_OPTIONS
        .iter()
        .map(|(d, l)| RentOptionView {
            days: *d,
            label: (*l).to_owned(),
        })
        .collect();

    let color_options: Vec<ColorOptionView> = room_domain::NICK_COLORS
        .iter()
        .map(|(v, l)| ColorOptionView {
            value: (*v).to_owned(),
            label: (*l).to_owned(),
        })
        .collect();

    let meta = PageMeta::titled("Pokój w karczmie");
    let base = app.templates.build_context(&ctx, &meta);

    let view = RoomPageView {
        base,
        room_name: room.name,
        description: room.description,
        description_bbcode,
        days_remaining: room.days_remaining,
        is_admin,
        is_owner,
        members,
        npcs: room.npcs.clone(),
        has_npcs: !room.npcs.is_empty(),
        rent_options,
        color_options,
        message_length: msg_length,
        rank: user.rank.clone(),
    };

    app.templates.render_value("room.html", &view)
}

// =========================================================================
// GET /room/messages — AJAX message fetch (HTML fragment)
// =========================================================================

pub async fn room_messages(
    State(app): State<AppState>,
    Extension(ctx): Extension<RequestContext>,
    Query(query): Query<RoomMsgQuery>,
) -> Response {
    let Some(ref user) = ctx.session_user else {
        return (axum::http::StatusCode::UNAUTHORIZED, "").into_response();
    };

    let room_id = match rq::player_room(&app.pool, user.id).await {
        Ok(id) if id > 0 => id,
        _ => return (axum::http::StatusCode::FORBIDDEN, "").into_response(),
    };

    let Some(room) = rq::find_room(&app.pool, room_id).await.ok().flatten() else {
        return (axum::http::StatusCode::NOT_FOUND, "").into_response();
    };

    let msg_length = query
        .length
        .unwrap_or(room_domain::DEFAULT_MESSAGE_LENGTH)
        .clamp(
            room_domain::DEFAULT_MESSAGE_LENGTH,
            room_domain::MAX_MESSAGE_LENGTH,
        );

    let is_admin = room_domain::is_admin(room.owner_id, &room.co_owners, user.id);

    let raw_messages = rq::list_room_messages(&app.pool, room_id, user.id, msg_length)
        .await
        .unwrap_or_default();

    let now_epoch = current_epoch();
    let messages: Vec<RoomMessageView> = raw_messages
        .into_iter()
        .map(|m| {
            let age = now_epoch.saturating_sub(m.created_epoch);
            RoomMessageView {
                id: m.id,
                author_html: m.author_html,
                body: m.body,
                sender_id: m.sender_id,
                time_ago: format_time_ago(age),
            }
        })
        .collect();

    let online = rq::online_in_room(&app.pool, room_id)
        .await
        .unwrap_or_default();
    let online_count = online.len();
    let online_players: Vec<MemberView> = online
        .into_iter()
        .map(|p| MemberView {
            id: p.id,
            name: p.user,
        })
        .collect();

    let view = RoomMessagesView {
        messages,
        online_players,
        online_count,
        show_admin: is_admin,
        player_id: user.id,
        message_length: msg_length,
    };

    app.templates.render_value("room_messages.html", &view)
}

// =========================================================================
// POST /room/send — send a room message
// =========================================================================

pub async fn room_send(
    State(app): State<AppState>,
    Extension(ctx): Extension<RequestContext>,
    Form(form): Form<RoomSendForm>,
) -> Response {
    let Some(ref user) = ctx.session_user else {
        return crate::page::redirect("/login");
    };

    let raw_msg = form.msg.unwrap_or_default();
    if raw_msg.trim().is_empty() {
        return crate::page::redirect_after_post("/room");
    }

    let room_id = match rq::player_room(&app.pool, user.id).await {
        Ok(id) if id > 0 => id,
        _ => return crate::page::redirect_after_post("/room"),
    };

    let Some(room) = rq::find_room(&app.pool, room_id).await.ok().flatten() else {
        return crate::page::redirect_after_post("/room");
    };

    let is_admin = room_domain::is_admin(room.owner_id, &room.co_owners, user.id);

    // Load bad words for BBCode filter.
    let bad_words = chatq::list_bad_words(&app.pool).await.unwrap_or_default();

    // Determine persona (only admins can speak as NPC or narration).
    let persona_idx = form.person.unwrap_or(0);
    let persona = if is_admin {
        room_domain::RoomPersona::from_index(persona_idx, room.npcs.len())
            .unwrap_or(room_domain::RoomPersona::Player)
    } else {
        room_domain::RoomPersona::Player
    };

    // Build colour map from room JSONB.
    let color_map = room.color_map();
    let player_color = color_map.get(&user.id).map(String::as_str);

    // Process BBCode.
    let processed = text::bbcode_to_html(&raw_msg, &bad_words, true);
    let stripped = text::strip_tags(&processed);
    if stripped.trim().is_empty() {
        return crate::page::redirect_after_post("/room");
    }

    let mut body = processed;
    let author: String;

    match persona {
        room_domain::RoomPersona::Player => {
            // Check for @me emote.
            let (emote_body, is_emote) = text::apply_emote(&body, user.id, &user.name);
            if is_emote {
                author = String::new();
                body = emote_body;
            } else {
                author = room_domain::room_author_html(user.id, &user.name, player_color);
            }
        }
        room_domain::RoomPersona::Description => {
            // Narration — replace @me with player link.
            body = body.replace(
                "@me",
                &format!("<a href=\"/view/{}\">{}</a>", user.id, user.name),
            );
            author = String::new();
        }
        room_domain::RoomPersona::Npc(idx) => {
            let npc_name = &room.npcs[idx];
            // Replace /me with NPC name in the message.
            body = body.replace("/me", npc_name);
            // If NPC name still appears in body, it's an emote style — no separate author.
            if body.contains(npc_name) {
                author = String::new();
            } else {
                author = npc_name.clone();
            }
        }
    }

    // Detect whisper: "ID=text" pattern.
    let (body_to_store, recipient_id) = detect_whisper(&body);

    let _ = rq::insert_room_message(
        &app.pool,
        room_id,
        &author,
        &body_to_store,
        user.id,
        recipient_id,
    )
    .await;

    crate::page::redirect_after_post("/room")
}

// =========================================================================
// POST /room/admin/delete-msg — delete a message (admin)
// =========================================================================

pub async fn room_admin_delete_msg(
    State(app): State<AppState>,
    Extension(ctx): Extension<RequestContext>,
    Query(query): Query<RoomDeleteQuery>,
) -> Response {
    let Some(ref user) = ctx.session_user else {
        return crate::page::redirect("/login");
    };

    let room_id = match rq::player_room(&app.pool, user.id).await {
        Ok(id) if id > 0 => id,
        _ => return crate::page::redirect_after_post("/room"),
    };

    let Some(room) = rq::find_room(&app.pool, room_id).await.ok().flatten() else {
        return crate::page::redirect_after_post("/room");
    };

    if !room_domain::is_admin(room.owner_id, &room.co_owners, user.id) {
        return crate::page::redirect_after_post("/room");
    }

    if let Some(tid) = query.tid {
        let _ = rq::delete_room_message(&app.pool, tid, room_id).await;
    }

    crate::page::redirect_after_post("/room")
}

// =========================================================================
// POST /room/quit — leave or destroy the room
// =========================================================================

pub async fn room_quit(
    State(app): State<AppState>,
    Extension(ctx): Extension<RequestContext>,
) -> Response {
    let Some(ref user) = ctx.session_user else {
        return crate::page::redirect("/login");
    };

    let room_id = match rq::player_room(&app.pool, user.id).await {
        Ok(id) if id > 0 => id,
        _ => return crate::page::redirect("/chat"),
    };

    let Some(room) = rq::find_room(&app.pool, room_id).await.ok().flatten() else {
        return crate::page::redirect("/chat");
    };

    if room_domain::is_owner(room.owner_id, user.id) {
        // Owner destroys the room: notify all members, clear assignments, delete.
        let members = rq::list_room_members(&app.pool, room_id)
            .await
            .unwrap_or_default();
        let log_msg = format!("{} zlikwidował(a) pokój w karczmie.", user.name);
        for m in &members {
            if m.id != user.id {
                let _ = rq::insert_event_log(&app.pool, m.id, &log_msg).await;
            }
        }
        let _ = rq::clear_all_players_in_room(&app.pool, room_id).await;
        let _ = rq::delete_room(&app.pool, room_id).await;

        return error_page(
            &app,
            &ctx,
            "Zlikwidowałeś(aś) swój pokój w karczmie. \
             <a href=\"/chat\">Wróć do karczmy</a>",
        );
    }

    // Non-owner leaves: remove from co-owners if applicable.
    let mut co_owners = room.co_owners.clone();
    if let Some(pos) = co_owners.iter().position(|&id| id == user.id) {
        co_owners.remove(pos);
        let _ = rq::set_co_owners(&app.pool, room_id, &co_owners).await;
    }

    // Post a system message.
    let leave_msg = format!(
        "<a href=\"/view/{}\">{}</a> opuścił(a) pokój.",
        user.id, user.name
    );
    let _ = rq::insert_room_message(&app.pool, room_id, "", &leave_msg, user.id, 0).await;

    let _ = rq::clear_player_room(&app.pool, user.id).await;

    error_page(
        &app,
        &ctx,
        "Opuściłeś(aś) pokój w karczmie. \
         <a href=\"/chat\">Wróć do karczmy</a>",
    )
}

// =========================================================================
// POST /room/admin/remove — kick a player from the room
// =========================================================================

pub async fn room_admin_remove(
    State(app): State<AppState>,
    Extension(ctx): Extension<RequestContext>,
    Form(form): Form<RoomPlayerForm>,
) -> Response {
    let Some(ref user) = ctx.session_user else {
        return crate::page::redirect("/login");
    };

    let (room_id, room) = match load_admin_room(&app, user.id).await {
        Ok(v) => v,
        Err(resp) => return resp,
    };

    let pid = form.pid.unwrap_or(0);
    if pid < 1 {
        return error_page(&app, &ctx, "Zapomnij o tym.");
    }
    if pid == user.id {
        return error_page(&app, &ctx, "Nie możesz wyrzucić siebie z pokoju.");
    }

    // Verify target is in this room.
    let target_room = rq::player_room(&app.pool, pid).await.unwrap_or(0);
    if target_room != room_id {
        return error_page(
            &app,
            &ctx,
            "Ten gracz nie został zaproszony do tego pokoju.",
        );
    }

    let log_msg = format!("{} wyrzucił(a) Ciebie z pokoju w karczmie.", user.name);
    let _ = rq::insert_event_log(&app.pool, pid, &log_msg).await;
    let _ = rq::clear_player_room(&app.pool, pid).await;

    // Remove from co-owners if they were one.
    let mut co_owners = room.co_owners.clone();
    if let Some(pos) = co_owners.iter().position(|&id| id == pid) {
        co_owners.remove(pos);
        let _ = rq::set_co_owners(&app.pool, room_id, &co_owners).await;
    }

    success_redirect(&format!("Wyrzuciłeś(aś) gracza o ID: {pid} z pokoju."))
}

// =========================================================================
// POST /room/admin/invite — invite a player to the room
// =========================================================================

pub async fn room_admin_invite(
    State(app): State<AppState>,
    Extension(ctx): Extension<RequestContext>,
    Form(form): Form<RoomPlayerForm>,
) -> Response {
    let Some(ref user) = ctx.session_user else {
        return crate::page::redirect("/login");
    };

    let (room_id, _room) = match load_admin_room(&app, user.id).await {
        Ok(v) => v,
        Err(resp) => return resp,
    };

    let pid = form.pid.unwrap_or(0);
    if pid < 1 {
        return error_page(&app, &ctx, "Zapomnij o tym.");
    }

    // Validate target player.
    let Some((target_id, target_room, accepts_invites)) =
        rq::player_invite_check(&app.pool, pid).await.ok().flatten()
    else {
        return error_page(&app, &ctx, "Nie ma takiego gracza.");
    };

    if target_room != 0 {
        return error_page(
            &app,
            &ctx,
            "Ten gracz posiada już pokój bądź został już zaproszony do jakiegoś pokoju.",
        );
    }

    // Check per-player inn block.
    if rq::is_inn_blocked(&app.pool, target_id, user.id)
        .await
        .unwrap_or(false)
    {
        return error_page(&app, &ctx, "Ten gracz ignoruje zaproszenia od ciebie.");
    }

    // Check global room-invite setting.
    if !accepts_invites {
        return error_page(
            &app,
            &ctx,
            "Ta osoba ignoruje wszystkie zaproszenia do pokojów w karczmie.",
        );
    }

    let _ = rq::assign_player_room(&app.pool, target_id, room_id).await;

    let log_msg = format!(
        "{} zaprosił(a) Ciebie do swojego pokoju w karczmie.",
        user.name
    );
    let _ = rq::insert_event_log(&app.pool, target_id, &log_msg).await;

    success_redirect(&format!(
        "Zaprosiłeś(aś) gracza o ID: {pid} do swojego pokoju."
    ))
}

// =========================================================================
// POST /room/admin/desc — change room description
// =========================================================================

pub async fn room_admin_desc(
    State(app): State<AppState>,
    Extension(ctx): Extension<RequestContext>,
    Form(form): Form<RoomDescForm>,
) -> Response {
    let Some(ref user) = ctx.session_user else {
        return crate::page::redirect("/login");
    };

    let (room_id, _room) = match load_admin_room(&app, user.id).await {
        Ok(v) => v,
        Err(resp) => return resp,
    };

    let raw_desc = form.desc.unwrap_or_default();
    let bad_words = chatq::list_bad_words(&app.pool).await.unwrap_or_default();
    let processed = text::bbcode_to_html(&raw_desc, &bad_words, false);

    let _ = rq::update_description(&app.pool, room_id, &processed).await;

    success_redirect("Zmieniłeś(aś) opis pokoju.")
}

// =========================================================================
// POST /room/admin/name — change room name
// =========================================================================

pub async fn room_admin_name(
    State(app): State<AppState>,
    Extension(ctx): Extension<RequestContext>,
    Form(form): Form<RoomNameForm>,
) -> Response {
    let Some(ref user) = ctx.session_user else {
        return crate::page::redirect("/login");
    };

    let (room_id, _room) = match load_admin_room(&app, user.id).await {
        Ok(v) => v,
        Err(resp) => return resp,
    };

    let name = room_domain::sanitise_name(&form.rname.unwrap_or_default());
    let _ = rq::update_name(&app.pool, room_id, &name).await;

    success_redirect("Zmieniłeś(aś) nazwę pokoju.")
}

// =========================================================================
// POST /room/admin/npc-add — add NPC persona
// =========================================================================

pub async fn room_admin_npc_add(
    State(app): State<AppState>,
    Extension(ctx): Extension<RequestContext>,
    Form(form): Form<RoomNpcForm>,
) -> Response {
    let Some(ref user) = ctx.session_user else {
        return crate::page::redirect("/login");
    };

    let (room_id, room) = match load_admin_room(&app, user.id).await {
        Ok(v) => v,
        Err(resp) => return resp,
    };

    let npc_name = room_domain::sanitise_npc_name(&form.npc.unwrap_or_default());
    if npc_name.is_empty() {
        return error_page(&app, &ctx, "Zapomnij o tym.");
    }

    // NPC name must not match an existing player or existing NPC.
    let player_exists = rq::player_name_by_username(&app.pool, &npc_name)
        .await
        .ok()
        .flatten()
        .is_some();
    if player_exists || room.npcs.contains(&npc_name) {
        return error_page(&app, &ctx, "Nie możesz dodać NPC o takim imieniu.");
    }

    let mut npcs = room.npcs;
    npcs.push(npc_name);
    let _ = rq::set_npcs(&app.pool, room_id, &npcs).await;

    success_redirect("Dodałeś(aś) NPC do pokoju.")
}

// =========================================================================
// POST /room/admin/npc-remove — remove NPC persona
// =========================================================================

pub async fn room_admin_npc_remove(
    State(app): State<AppState>,
    Extension(ctx): Extension<RequestContext>,
    Form(form): Form<RoomNpcForm>,
) -> Response {
    let Some(ref user) = ctx.session_user else {
        return crate::page::redirect("/login");
    };

    let (room_id, room) = match load_admin_room(&app, user.id).await {
        Ok(v) => v,
        Err(resp) => return resp,
    };

    let npc_name = room_domain::sanitise_npc_name(&form.npc.unwrap_or_default());
    let Some(idx) = room.npcs.iter().position(|n| n == &npc_name) else {
        return error_page(&app, &ctx, "Nie ma takiego NPC w pokoju.");
    };

    let mut npcs = room.npcs;
    npcs.remove(idx);
    let _ = rq::set_npcs(&app.pool, room_id, &npcs).await;

    success_redirect("Usunąłeś(aś) NPC z pokoju.")
}

// =========================================================================
// POST /room/admin/co-owner — add or remove a co-owner
// =========================================================================

pub async fn room_admin_co_owner(
    State(app): State<AppState>,
    Extension(ctx): Extension<RequestContext>,
    Form(form): Form<RoomCoOwnerForm>,
) -> Response {
    let Some(ref user) = ctx.session_user else {
        return crate::page::redirect("/login");
    };

    let (room_id, room) = match load_admin_room(&app, user.id).await {
        Ok(v) => v,
        Err(resp) => return resp,
    };

    // Only the room owner can manage co-owners.
    if !room_domain::is_owner(room.owner_id, user.id) {
        return error_page(
            &app,
            &ctx,
            "Tylko właściciel pokoju może ustawiać współwłaścicieli.",
        );
    }

    let pid = form.pid.unwrap_or(0);
    let action = form.action.unwrap_or(-1);

    if pid < 1 || pid == user.id {
        return error_page(&app, &ctx, "Zapomnij o tym.");
    }

    // Verify target exists and is in this room.
    let target_room = rq::player_room(&app.pool, pid).await.unwrap_or(0);
    if target_room != room_id {
        return error_page(
            &app,
            &ctx,
            "Ten gracz nie został zaproszony do tego pokoju.",
        );
    }

    let mut co_owners = room.co_owners;
    let is_co = co_owners.contains(&pid);

    match action {
        0 if is_co => {
            return error_page(
                &app,
                &ctx,
                "Ten gracz jest już współwłaścicielem Twojego pokoju.",
            );
        }
        0 => {
            co_owners.push(pid);
            let _ = rq::set_co_owners(&app.pool, room_id, &co_owners).await;
            let log_msg = format!(
                "{} dodał(a) Ciebie jako współwłaściciela pokoju w karczmie.",
                user.name
            );
            let _ = rq::insert_event_log(&app.pool, pid, &log_msg).await;
            return success_redirect(&format!(
                "Dodałeś gracza o ID: {pid} jako współwłaściciela do pokoju."
            ));
        }
        1 if !is_co => {
            return error_page(
                &app,
                &ctx,
                "Ten gracz nie jest współwłaścicielem Twojego pokoju.",
            );
        }
        1 => {
            co_owners.retain(|&id| id != pid);
            let _ = rq::set_co_owners(&app.pool, room_id, &co_owners).await;
            let log_msg = format!(
                "{} usunął Ciebie jako współwłaściciela pokoju w karczmie.",
                user.name
            );
            let _ = rq::insert_event_log(&app.pool, pid, &log_msg).await;
            return success_redirect(&format!(
                "Usunąłeś gracza o ID: {pid} jako współwłaściciela z pokoju."
            ));
        }
        _ => {}
    }

    crate::page::redirect_after_post("/room")
}

// =========================================================================
// POST /room/admin/color — set a player's nick colour
// =========================================================================

pub async fn room_admin_color(
    State(app): State<AppState>,
    Extension(ctx): Extension<RequestContext>,
    Form(form): Form<RoomColorForm>,
) -> Response {
    let Some(ref user) = ctx.session_user else {
        return crate::page::redirect("/login");
    };

    let (room_id, room) = match load_admin_room(&app, user.id).await {
        Ok(v) => v,
        Err(resp) => return resp,
    };

    let pid = form.pid.unwrap_or(0);
    let color = form.color.unwrap_or_default();

    if pid < 1 || !room_domain::validate_color(&color) {
        return error_page(&app, &ctx, "Zapomnij o tym.");
    }

    // Verify target is in this room.
    let target_room = rq::player_room(&app.pool, pid).await.unwrap_or(0);
    if target_room != room_id {
        return error_page(
            &app,
            &ctx,
            "Ten gracz nie został zaproszony do tego pokoju.",
        );
    }

    let mut color_map = room.color_map();
    color_map.insert(pid, color);
    let new_colors = rq::color_map_to_json(&color_map);
    let _ = rq::set_colors(&app.pool, room_id, &new_colors).await;

    success_redirect(&format!("Ustawiłeś(aś) graczowi o ID: {pid} kolor nicka."))
}

// =========================================================================
// POST /room/admin/rent — extend room rent
// =========================================================================

pub async fn room_admin_rent(
    State(app): State<AppState>,
    Extension(ctx): Extension<RequestContext>,
    Form(form): Form<RoomRentForm>,
) -> Response {
    let Some(ref user) = ctx.session_user else {
        return crate::page::redirect("/login");
    };

    let (_room_id, room) = match load_admin_room(&app, user.id).await {
        Ok(v) => v,
        Err(resp) => return resp,
    };

    let days = form.rent.unwrap_or(0);
    let cost = match room_domain::validate_rent(days, room.days_remaining) {
        Ok(c) => c,
        Err(msg) => return error_page(&app, &ctx, msg),
    };

    // Check player gold.
    let player_credits = {
        let row = vallheru_data::queries::player::find_player_by_id(
            &app.pool,
            #[allow(clippy::cast_possible_truncation)]
            (user.id as i32),
        )
        .await;
        match row {
            Ok(Some(r)) => r.credits,
            _ => 0,
        }
    };

    if player_credits < cost {
        return error_page(
            &app,
            &ctx,
            &format!("Nie masz tyle sztuk złota przy sobie. Potrzebujesz {cost} sztuk złota."),
        );
    }

    let _ = rq::extend_rent(&app.pool, room.id, days, user.id, cost).await;

    success_redirect(&format!("Przedłużyłeś(aś) wynajem pokoju o {days} dni."))
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

fn format_time_ago(seconds: i64) -> String {
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

fn error_page(state: &AppState, ctx: &RequestContext, message: &str) -> Response {
    let meta = PageMeta::titled("Błąd").with_flash(Flash {
        kind: FlashKind::Error,
        message: message.to_owned(),
    });
    let base = state.templates.build_context(ctx, &meta);
    state.templates.render("error.html", &base)
}

fn success_redirect(msg: &str) -> Response {
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
    crate::page::redirect_after_post(&format!("/room?success={encoded}"))
}

/// Load room and verify admin access, returning `(room_id, RoomRow)`.
async fn load_admin_room(app: &AppState, player_id: i64) -> Result<(i32, rq::RoomRow), Response> {
    let room_id = rq::player_room(&app.pool, player_id).await.unwrap_or(0);
    if room_id <= 0 {
        return Err(crate::page::redirect_after_post("/room"));
    }

    let room = rq::find_room(&app.pool, room_id)
        .await
        .ok()
        .flatten()
        .ok_or_else(|| crate::page::redirect_after_post("/room"))?;

    if !room_domain::is_admin(room.owner_id, &room.co_owners, player_id) {
        return Err(crate::page::redirect_after_post("/room"));
    }

    Ok((room_id, room))
}

/// Detect whisper pattern "ID=text" in a message body.
fn detect_whisper(body: &str) -> (String, i64) {
    let text = text::strip_tags(body);
    if let Some(eq_pos) = text.find('=') {
        let prefix = &text[..eq_pos];
        if let Ok(id) = prefix.trim().parse::<i64>() {
            if id > 0 {
                let whisper_body = &text[eq_pos + 1..];
                let formatted = format!("<b>Szept</b>: {whisper_body}");
                return (formatted, id);
            }
        }
    }
    (body.to_owned(), 0)
}
