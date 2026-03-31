//! Staff moderation action handlers — jail, bans, confiscation, immunity,
//! and judge panel rank management.
//!
//! Ported from `includes/admin/jail.php`, `includes/admin/czat.php`,
//! `includes/admin/banmail.php`, `includes/admin/takeaway.php`,
//! `includes/admin/tags.php`, and `sedzia.php`.

use axum::{Extension, Form, extract::State, response::Response};

use crate::middleware::context::RequestContext;
use crate::page::PageMeta;
use crate::state::AppState;

// ---------------------------------------------------------------------------
// View models
// ---------------------------------------------------------------------------

/// Staff jail management form.
#[derive(serde::Serialize)]
pub struct StaffJailView {
    #[serde(flatten)]
    pub base: crate::render::RenderContext,
}

/// Chat ban management view.
#[derive(serde::Serialize)]
pub struct ChatBanView {
    #[serde(flatten)]
    pub base: crate::render::RenderContext,
    pub bans: Vec<BanEntry>,
    pub target: String,
}

#[derive(serde::Serialize)]
pub struct BanEntry {
    pub player: i32,
    pub resets: i32,
}

/// Mail ban management view.
#[derive(serde::Serialize)]
pub struct MailBanView {
    #[serde(flatten)]
    pub base: crate::render::RenderContext,
    pub banned: Vec<i32>,
}

/// Take away gold form.
#[derive(serde::Serialize)]
pub struct TakeawayView {
    #[serde(flatten)]
    pub base: crate::render::RenderContext,
}

/// Immunity grant form.
#[derive(serde::Serialize)]
pub struct ImmunityView {
    #[serde(flatten)]
    pub base: crate::render::RenderContext,
}

/// Judge panel view.
#[derive(serde::Serialize)]
pub struct JudgePanelView {
    #[serde(flatten)]
    pub base: crate::render::RenderContext,
}

// ---------------------------------------------------------------------------
// Form types
// ---------------------------------------------------------------------------

#[derive(serde::Deserialize)]
pub struct JailForm {
    pub prisoner: i32,
    pub verdict: String,
    pub time: i32,
}

#[derive(serde::Deserialize)]
pub struct BanForm {
    pub czat_id: i32,
    pub czat: String,
    #[serde(default)]
    pub duration: Option<i32>,
    #[serde(default)]
    pub verdict: Option<String>,
}

#[derive(serde::Deserialize)]
pub struct MailBanForm {
    pub mail_id: i32,
    pub mail: String,
}

#[derive(serde::Deserialize)]
pub struct TakeawayForm {
    pub id: i32,
    pub id2: i32,
    pub taken: i32,
    pub verdict: String,
}

#[derive(serde::Deserialize)]
pub struct ImmunityForm {
    pub tag_id: i32,
}

#[derive(serde::Deserialize)]
pub struct JudgeForm {
    pub aid: i32,
    pub rank: String,
}

// ---------------------------------------------------------------------------
// Handlers: Jail (staff)
// ---------------------------------------------------------------------------

/// GET /staff/jail — Staff jail form.
pub async fn staff_jail_form(
    State(state): State<AppState>,
    Extension(ctx): Extension<RequestContext>,
) -> Response {
    let meta = PageMeta::titled("Wtrącenie do lochów").with_back_link("/staff", "Staff");
    let view = StaffJailView {
        base: state.templates.build_context(&ctx, &meta),
    };
    state.templates.render_value("staff_jail.html", &view)
}

/// POST /staff/jail — Send player to jail.
pub async fn staff_jail_action(
    State(state): State<AppState>,
    Extension(ctx): Extension<RequestContext>,
    Form(form): Form<JailForm>,
) -> Response {
    let Some(ref user) = ctx.session_user else {
        return crate::page::redirect("/staff/jail");
    };

    if form.prisoner == 1 {
        return crate::page::redirect("/staff/jail");
    }

    if form.verdict.is_empty() || form.time < 1 {
        return crate::page::redirect("/staff/jail");
    }

    let duration_days = form.time * 7;
    let result = vallheru_data::queries::moderation::send_to_jail(
        &state.pool,
        form.prisoner,
        &form.verdict,
        duration_days,
    )
    .await;

    if result.is_err() {
        return crate::page::redirect("/staff/jail");
    }

    // Log for prisoner.
    let msg = format!(
        "Wtrącono cię do lochów na {} tyg. Powód: {}. Przez: {} ID: {}",
        form.time, form.verdict, user.name, user.id
    );
    let _ =
        vallheru_data::queries::moderation::insert_game_log(&state.pool, form.prisoner, &msg, 'U')
            .await;

    // Log for admins (owner_id=1).
    let admin_msg = format!(
        "{} - wtrącony do lochów na {} tyg. {}, przez {} ID: {}",
        form.prisoner, form.time, form.verdict, user.name, user.id
    );
    let _ =
        vallheru_data::queries::moderation::insert_game_log(&state.pool, 1, &admin_msg, 'U').await;

    crate::page::redirect_after_post("/staff/jail")
}

// ---------------------------------------------------------------------------
// Handlers: Chat/Forum ban
// ---------------------------------------------------------------------------

/// GET /staff/chatban — Chat ban management.
pub async fn staff_chat_ban(
    State(state): State<AppState>,
    Extension(ctx): Extension<RequestContext>,
) -> Response {
    let rows = vallheru_data::queries::moderation::list_chat_bans(&state.pool)
        .await
        .unwrap_or_default();

    let bans: Vec<BanEntry> = rows
        .into_iter()
        .map(|r| BanEntry {
            player: r.player,
            resets: r.resets,
        })
        .collect();

    let meta = PageMeta::titled("Blokada czatu").with_back_link("/staff", "Staff");
    let view = ChatBanView {
        base: state.templates.build_context(&ctx, &meta),
        bans,
        target: "chat".to_string(),
    };
    state.templates.render_value("staff_ban.html", &view)
}

/// GET /staff/forumban — Forum ban management.
pub async fn staff_forum_ban(
    State(state): State<AppState>,
    Extension(ctx): Extension<RequestContext>,
) -> Response {
    let rows = vallheru_data::queries::moderation::list_forum_bans(&state.pool)
        .await
        .unwrap_or_default();

    let bans: Vec<BanEntry> = rows
        .into_iter()
        .map(|r| BanEntry {
            player: r.player,
            resets: r.resets,
        })
        .collect();

    let meta = PageMeta::titled("Blokada forum").with_back_link("/staff", "Staff");
    let view = ChatBanView {
        base: state.templates.build_context(&ctx, &meta),
        bans,
        target: "forum".to_string(),
    };
    state.templates.render_value("staff_ban.html", &view)
}

/// POST /staff/chatban — Apply or remove chat ban.
pub async fn staff_chat_ban_action(
    State(state): State<AppState>,
    Extension(ctx): Extension<RequestContext>,
    Form(form): Form<BanForm>,
) -> Response {
    let Some(ref user) = ctx.session_user else {
        return crate::page::redirect("/staff/chatban");
    };

    if form.czat == "blok" {
        let weeks = form.duration.unwrap_or(1);
        let resets = weeks * 7;
        let _ =
            vallheru_data::queries::moderation::ban_from_chat(&state.pool, form.czat_id, resets)
                .await;

        let verdict = form.verdict.as_deref().unwrap_or("");
        let msg = format!(
            "Zablokowano ci czat na {weeks} tyg. Powód: {verdict}. \
             Przez: {} ID: {}",
            user.name, user.id
        );
        let _ = vallheru_data::queries::moderation::insert_game_log(
            &state.pool,
            form.czat_id,
            &msg,
            'U',
        )
        .await;

        crate::page::redirect_after_post("/staff/chatban")
    } else {
        let _ =
            vallheru_data::queries::moderation::unban_from_chat(&state.pool, form.czat_id).await;
        crate::page::redirect_after_post("/staff/chatban")
    }
}

/// POST /staff/forumban — Apply or remove forum ban.
pub async fn staff_forum_ban_action(
    State(state): State<AppState>,
    Extension(ctx): Extension<RequestContext>,
    Form(form): Form<BanForm>,
) -> Response {
    let Some(ref user) = ctx.session_user else {
        return crate::page::redirect("/staff/forumban");
    };

    if form.czat == "blok" {
        let weeks = form.duration.unwrap_or(1);
        let resets = weeks * 7;
        let _ =
            vallheru_data::queries::moderation::ban_from_forum(&state.pool, form.czat_id, resets)
                .await;

        let verdict = form.verdict.as_deref().unwrap_or("");
        let msg = format!(
            "Zablokowano ci forum na {weeks} tyg. Powód: {verdict}. \
             Przez: {} ID: {}",
            user.name, user.id
        );
        let _ = vallheru_data::queries::moderation::insert_game_log(
            &state.pool,
            form.czat_id,
            &msg,
            'U',
        )
        .await;

        crate::page::redirect_after_post("/staff/forumban")
    } else {
        let _ =
            vallheru_data::queries::moderation::unban_from_forum(&state.pool, form.czat_id).await;
        crate::page::redirect_after_post("/staff/forumban")
    }
}

// ---------------------------------------------------------------------------
// Handlers: Mail ban
// ---------------------------------------------------------------------------

/// GET /staff/mailban — Mail ban management.
pub async fn staff_mail_ban(
    State(state): State<AppState>,
    Extension(ctx): Extension<RequestContext>,
) -> Response {
    let banned = vallheru_data::queries::moderation::list_mail_bans(&state.pool)
        .await
        .unwrap_or_default();

    let meta = PageMeta::titled("Blokada poczty").with_back_link("/staff", "Staff");
    let view = MailBanView {
        base: state.templates.build_context(&ctx, &meta),
        banned,
    };
    state.templates.render_value("staff_mailban.html", &view)
}

/// POST /staff/mailban — Block or unblock mail.
pub async fn staff_mail_ban_action(
    State(state): State<AppState>,
    Extension(_ctx): Extension<RequestContext>,
    Form(form): Form<MailBanForm>,
) -> Response {
    if form.mail == "blok" {
        let _ = vallheru_data::queries::moderation::ban_mail(&state.pool, form.mail_id).await;
        crate::page::redirect_after_post("/staff/mailban")
    } else {
        let _ = vallheru_data::queries::moderation::unban_mail(&state.pool, form.mail_id).await;
        crate::page::redirect_after_post("/staff/mailban")
    }
}

// ---------------------------------------------------------------------------
// Handlers: Takeaway (confiscate gold)
// ---------------------------------------------------------------------------

/// GET /staff/takeaway — Confiscation form.
pub async fn staff_takeaway_form(
    State(state): State<AppState>,
    Extension(ctx): Extension<RequestContext>,
) -> Response {
    let meta = PageMeta::titled("Konfiskata złota").with_back_link("/staff", "Staff");
    let view = TakeawayView {
        base: state.templates.build_context(&ctx, &meta),
    };
    state.templates.render_value("staff_takeaway.html", &view)
}

/// POST /staff/takeaway — Process gold confiscation.
pub async fn staff_takeaway_action(
    State(state): State<AppState>,
    Extension(ctx): Extension<RequestContext>,
    Form(form): Form<TakeawayForm>,
) -> Response {
    let Some(ref user) = ctx.session_user else {
        return crate::page::redirect("/staff/takeaway");
    };

    if form.verdict.is_empty() {
        return crate::page::redirect("/staff/takeaway");
    }

    if form.taken < 1 {
        return crate::page::redirect("/staff/takeaway");
    }

    // Verify both players exist.
    let offender_exists: Option<i32> = sqlx::query_scalar("SELECT id FROM players WHERE id = $1")
        .bind(form.id)
        .fetch_optional(&state.pool)
        .await
        .unwrap_or(None);
    let injured_exists: Option<i32> = sqlx::query_scalar("SELECT id FROM players WHERE id = $1")
        .bind(form.id2)
        .fetch_optional(&state.pool)
        .await
        .unwrap_or(None);

    if offender_exists.is_none() {
        return crate::page::redirect("/staff/takeaway");
    }
    if injured_exists.is_none() {
        return crate::page::redirect("/staff/takeaway");
    }

    let result = vallheru_data::queries::moderation::confiscate_gold(
        &state.pool,
        form.id,
        form.id2,
        form.taken,
    )
    .await;

    if result.is_err() {
        return crate::page::redirect("/staff/takeaway");
    }

    // Log for offender.
    let msg = format!(
        "Skonfiskowano ci {} złotych monet. Powód: {}. Przez: {} ID: {}",
        form.taken, form.verdict, user.name, user.id
    );
    let _ =
        vallheru_data::queries::moderation::insert_game_log(&state.pool, form.id, &msg, 'U').await;

    // Log for injured.
    let msg2 = format!(
        "Otrzymano {} złotych monet w ramach rekompensaty od gracza ID {}. Przez: {} ID: {}",
        form.taken / 2,
        form.id,
        user.name,
        user.id
    );
    let _ = vallheru_data::queries::moderation::insert_game_log(&state.pool, form.id2, &msg2, 'U')
        .await;

    crate::page::redirect_after_post("/staff/takeaway")
}

// ---------------------------------------------------------------------------
// Handlers: Immunity
// ---------------------------------------------------------------------------

/// GET /staff/immunity — Immunity grant form.
pub async fn staff_immunity_form(
    State(state): State<AppState>,
    Extension(ctx): Extension<RequestContext>,
) -> Response {
    let meta = PageMeta::titled("Nadaj immunitet").with_back_link("/staff", "Staff");
    let view = ImmunityView {
        base: state.templates.build_context(&ctx, &meta),
    };
    state.templates.render_value("staff_immunity.html", &view)
}

/// POST /staff/immunity — Grant immunity.
pub async fn staff_immunity_action(
    State(state): State<AppState>,
    Extension(_ctx): Extension<RequestContext>,
    Form(form): Form<ImmunityForm>,
) -> Response {
    let _ = vallheru_data::queries::moderation::grant_immunity(&state.pool, form.tag_id).await;

    crate::page::redirect_after_post("/staff/immunity")
}

// ---------------------------------------------------------------------------
// Handlers: Judge panel
// ---------------------------------------------------------------------------

/// GET /judge — Judge panel (rank assignment).
pub async fn judge_panel(
    State(state): State<AppState>,
    Extension(ctx): Extension<RequestContext>,
) -> Response {
    let meta = PageMeta::titled("Panel Sędziego").with_back_link("/court", "Sąd");
    let view = JudgePanelView {
        base: state.templates.build_context(&ctx, &meta),
    };
    state.templates.render_value("judge_panel.html", &view)
}

/// POST /judge — Assign rank to player.
pub async fn judge_assign_rank(
    State(state): State<AppState>,
    Extension(_ctx): Extension<RequestContext>,
    Form(form): Form<JudgeForm>,
) -> Response {
    let allowed_ranks = ["Member", "Prawnik", "Ławnik"];
    if !allowed_ranks.contains(&form.rank.as_str()) {
        return crate::page::redirect("/judge");
    }

    // Only allow changing rank if player currently has an allowed rank.
    let current_rank =
        vallheru_data::queries::moderation::get_player_rank(&state.pool, form.aid).await;

    let changeable = ["Member", "Ławnik", "Sędzia", "Prawnik"];
    match current_rank {
        Ok(Some(ref r)) if changeable.contains(&r.as_str()) => {}
        _ => {
            return crate::page::redirect("/judge");
        }
    }

    let _ = vallheru_data::queries::moderation::set_player_rank(&state.pool, form.aid, &form.rank)
        .await;

    crate::page::redirect_after_post("/judge")
}
