//! Player profile handler — view another player's public info.

use axum::Extension;
use axum::extract::{Path, State};
use axum::response::{IntoResponse, Redirect, Response};

use vallheru_data::queries::player as pq;

use crate::middleware::context::RequestContext;
use crate::page::PageMeta;
use crate::state::AppState;

#[derive(serde::Serialize)]
struct ProfileView {
    #[serde(flatten)]
    base: crate::render::RenderContext,
    player_id: i32,
    player_name: String,
    avatar: Option<String>,
    rank: String,
    location: String,
    age: i32,
    race: String,
    class: String,
    gender: Option<String>,
    deity: Option<String>,
    max_hp: i32,
    reputation: i32,
    wins: i32,
    losses: i32,
    ratio: Option<String>,
    last_killed: String,
    last_killed_by: String,
    profile: String,
    prev_id: Option<i32>,
    next_id: Option<i32>,
}

/// GET /player/{id} — view a player's public profile.
pub async fn player_profile(
    State(app): State<AppState>,
    Extension(ctx): Extension<RequestContext>,
    Path(id): Path<i32>,
) -> Response {
    let Some(ref _user) = ctx.session_user else {
        return Redirect::to("/").into_response();
    };

    let Some(target) = (match pq::find_player_by_id(&app.pool, id).await {
        Ok(v) => v,
        Err(e) => {
            tracing::error!(player_id = id, error = ?e, "failed to load player profile");
            None
        }
    }) else {
        return Redirect::to("/city").into_response();
    };

    let (prev_id, next_id) = adjacent_player_ids(&app, id).await;

    let total_fights = target.wins + target.losses;
    let ratio = if total_fights > 0 {
        let r = (f64::from(target.wins) / f64::from(total_fights)) * 100.0;
        Some(format!("{r:.1}"))
    } else {
        None
    };

    let avatar = if target.avatar.is_empty() {
        None
    } else {
        Some(target.avatar.clone())
    };

    let gender_display = target.gender.as_deref().map(|g| match g {
        "M" => "Mężczyzna".to_string(),
        "F" | "K" => "Kobieta".to_string(),
        other => other.to_string(),
    });

    let meta = PageMeta::titled(format!("Profil — {}", target.username));
    let base = app.templates.build_context(&ctx, &meta);

    let view = ProfileView {
        base,
        player_id: target.id,
        player_name: target.username,
        avatar,
        rank: target.rank,
        location: target.location,
        age: target.age,
        race: target.race,
        class: target.class,
        gender: gender_display,
        deity: target.deity,
        max_hp: target.max_hp,
        reputation: target.reputation,
        wins: target.wins,
        losses: target.losses,
        ratio,
        last_killed: target.last_killed,
        last_killed_by: target.last_killed_by,
        profile: target.profile,
        prev_id,
        next_id,
    };

    app.templates.render_value("player_profile.html", &view)
}

async fn adjacent_player_ids(app: &AppState, player_id: i32) -> (Option<i32>, Option<i32>) {
    let prev: Option<(i32,)> =
        match sqlx::query_as("SELECT id FROM players WHERE id < $1 ORDER BY id DESC LIMIT 1")
            .bind(player_id)
            .fetch_optional(&app.pool)
            .await
        {
            Ok(v) => v,
            Err(e) => {
                tracing::error!(player_id, error = ?e, "failed to find prev player id");
                None
            }
        };

    let next: Option<(i32,)> =
        match sqlx::query_as("SELECT id FROM players WHERE id > $1 ORDER BY id ASC LIMIT 1")
            .bind(player_id)
            .fetch_optional(&app.pool)
            .await
        {
            Ok(v) => v,
            Err(e) => {
                tracing::error!(player_id, error = ?e, "failed to find next player id");
                None
            }
        };

    (prev.map(|r| r.0), next.map(|r| r.0))
}
