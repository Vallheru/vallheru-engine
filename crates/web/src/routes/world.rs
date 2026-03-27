//! City, map, and world navigation routes.

use axum::{Router, middleware, routing};

use crate::handlers::{
    bank, chat, city, equipment, forums, gathering, locations, mail, map, market, shops, spells,
    travel,
};
use crate::middleware::guards::require_authenticated;
use crate::state::AppState;

/// Register city, travel, map, and secondary location routes.
pub fn routes() -> Router<AppState> {
    Router::new()
        .merge(location_routes())
        .merge(economy_routes())
        .merge(combat_routes())
        .merge(social_routes())
        .layer(middleware::from_fn(require_authenticated))
}

fn location_routes() -> Router<AppState> {
    Router::new()
        .route("/city", routing::get(city::show))
        .route("/travel", routing::get(travel::show))
        .route("/map", routing::get(map::show))
        .route("/mountains", routing::get(locations::mountains))
        .route("/forest", routing::get(locations::forest))
        .route("/alley", routing::get(locations::alley))
        .route(
            "/landfill",
            routing::get(locations::landfill_show).post(locations::landfill_work),
        )
        .route(
            "/rest",
            routing::get(locations::rest_show).post(locations::rest_recover),
        )
        // Gathering routes
        .route(
            "/mining",
            routing::get(gathering::mining_show).post(gathering::mining_work),
        )
        .route("/mines", routing::get(gathering::mines_show))
        .route("/mines/dig", routing::post(gathering::mines_dig))
        .route(
            "/lumberjack",
            routing::get(gathering::lumberjack_show).post(gathering::lumberjack_work),
        )
        .route("/smelter", routing::get(gathering::smelter_show))
        .route("/smelter/smelt", routing::post(gathering::smelter_smelt))
        .route(
            "/smelter/upgrade",
            routing::post(gathering::smelter_upgrade),
        )
        .route("/farm", routing::get(gathering::farm_show))
}

fn economy_routes() -> Router<AppState> {
    Router::new()
        .route("/wealth", routing::get(bank::wealth_show))
        .route(
            "/bank",
            routing::get(bank::bank_show).post(bank::bank_action),
        )
        .route("/magic-shop", routing::get(bank::magic_shop_show))
        .route(
            "/magic-shop/buy/{id}",
            routing::get(bank::magic_shop_buy_show).post(bank::magic_shop_buy_action),
        )
        // Equipment routes
        .route("/equipment", routing::get(equipment::equipment_show))
        .route("/equipment/equip", routing::post(equipment::equip_action))
        .route(
            "/equipment/unequip",
            routing::post(equipment::unequip_action),
        )
        .route("/equipment/sell", routing::post(equipment::sell_action))
        .route("/equipment/repair", routing::post(equipment::repair_action))
        // NPC shops
        .route("/weapons", routing::get(shops::weapons_show))
        .route("/weapons/buy/{id}", routing::post(shops::weapons_buy))
        .route("/armor", routing::get(shops::armor_show))
        .route(
            "/armor/{category}",
            routing::get(shops::armor_category_show),
        )
        .route("/armor/buy/{id}", routing::post(shops::armor_buy))
        .route("/fletcher", routing::get(shops::fletcher_show))
        .route("/fletcher/buy/{id}", routing::post(shops::fletcher_buy_bow))
        .route(
            "/fletcher/arrows/{id}",
            routing::get(shops::fletcher_arrows_show).post(shops::fletcher_buy_arrows),
        )
        // Spell book
        .route("/spellbook", routing::get(spells::spellbook_show))
        .route("/spellbook/activate", routing::post(spells::spell_activate))
        .route(
            "/spellbook/deactivate",
            routing::post(spells::spell_deactivate),
        )
        // Player-to-player markets
        .route("/market", routing::get(market::market_hub))
        .route("/market/myoffers", routing::get(market::market_my_offers))
        .route("/market/{slug}", routing::get(market::market_browse))
        .route(
            "/market/{slug}/buy/{id}",
            routing::get(market::market_buy_show).post(market::market_buy_execute),
        )
        .route(
            "/market/{slug}/cancel/{id}",
            routing::post(market::market_cancel),
        )
}

fn combat_routes() -> Router<AppState> {
    // Placeholder — combat routes will be added by later tasks.
    Router::new()
}

fn social_routes() -> Router<AppState> {
    Router::new()
        // Chat / tavern
        .route("/chat", routing::get(chat::chat_page))
        .route("/chat/messages", routing::get(chat::chat_messages))
        .route("/chat/send", routing::post(chat::chat_send))
        .route("/chat/admin/delete", routing::post(chat::chat_admin_delete))
        .route("/chat/admin/ban", routing::post(chat::chat_admin_ban))
        .route("/chat/admin/give", routing::post(chat::chat_admin_give))
        .route("/chat/admin/prune", routing::post(chat::chat_admin_prune))
        // Mail
        .route("/mail", routing::get(mail::mail_index))
        .route("/mail/inbox", routing::get(mail::mail_inbox))
        .route("/mail/saved", routing::get(mail::mail_saved))
        .route("/mail/read", routing::get(mail::mail_read))
        .route("/mail/compose", routing::get(mail::mail_compose))
        .route("/mail/send", routing::post(mail::mail_send))
        .route("/mail/bulk", routing::post(mail::mail_bulk))
        .route("/mail/delete-old", routing::post(mail::mail_delete_old))
        .route("/mail/clear", routing::post(mail::mail_clear))
        .route("/mail/save", routing::post(mail::mail_save_msg))
        .route("/mail/delete", routing::post(mail::mail_delete_msg))
        .route("/mail/block", routing::post(mail::mail_block))
        .route("/mail/search", routing::get(mail::mail_search))
        .route(
            "/mail/forward",
            routing::get(mail::mail_forward_show).post(mail::mail_forward_action),
        )
        // Forums
        .route("/forums", routing::get(forums::forum_categories))
        .route("/forums/new", routing::get(forums::forum_new_posts))
        .route("/forums/search", routing::post(forums::forum_search))
        .route(
            "/forums/category/{id}",
            routing::get(forums::forum_topic_list),
        )
        .route(
            "/forums/category/{id}/delete-topics",
            routing::post(forums::forum_bulk_delete_topics),
        )
        .route("/forums/topic/new", routing::post(forums::forum_add_topic))
        .route("/forums/topic/{id}", routing::get(forums::forum_topic_read))
        .route(
            "/forums/topic/{id}/reply",
            routing::post(forums::forum_add_reply),
        )
        .route(
            "/forums/topic/{id}/delete",
            routing::post(forums::forum_delete_topic),
        )
        .route(
            "/forums/topic/{id}/delete-replies",
            routing::post(forums::forum_bulk_delete_replies),
        )
        .route(
            "/forums/topic/{id}/close",
            routing::post(forums::forum_toggle_close),
        )
        .route(
            "/forums/topic/{id}/sticky",
            routing::post(forums::forum_toggle_sticky),
        )
        .route(
            "/forums/topic/{id}/move",
            routing::get(forums::forum_move_show).post(forums::forum_move_action),
        )
        .route(
            "/forums/reply/{id}/delete",
            routing::post(forums::forum_delete_reply),
        )
}
