//! City, map, and world navigation routes.

use axum::{Router, middleware, routing};

use crate::handlers::{
    bank, chat, city, content, court, deity, equipment, forums, gathering, hospital, house, jail,
    locations, mail, map, market, outpost, pages, player_profile, quest, room, shops, spells,
    temple, tower, travel, tribe_forum,
};
use crate::middleware::guards::require_authenticated;
use crate::state::AppState;

/// Register city, travel, map, and secondary location routes.
pub fn routes() -> Router<AppState> {
    Router::new()
        // RSS feed — public, no auth required.
        .route("/rss", routing::get(content::rss_feed))
        .merge(
            Router::new()
                .merge(location_routes())
                .merge(economy_routes())
                .merge(combat_routes())
                .merge(outpost_routes())
                .merge(social_routes())
                .merge(quest_routes())
                .layer(middleware::from_fn(require_authenticated)),
        )
}

fn location_routes() -> Router<AppState> {
    Router::new()
        .route("/city", routing::get(city::show))
        .route("/travel", routing::get(travel::show))
        .route("/map", routing::get(map::show))
        .route("/mountains", routing::get(locations::mountains))
        .route("/forest", routing::get(locations::forest))
        .route("/alley", routing::get(locations::alley))
        .route("/hospital", routing::get(hospital::hospital_page))
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
        // Temple
        .route("/temple", routing::get(temple::temple_show))
        .route(
            "/temple/work",
            routing::get(temple::temple_work_show).post(temple::temple_work_action),
        )
        .route(
            "/temple/prayer",
            routing::get(temple::temple_prayer_show).post(temple::temple_prayer_action),
        )
        .route("/temple/book", routing::get(temple::temple_book))
        .route("/temple/pantheon", routing::get(temple::temple_pantheon))
        // Deity
        .route("/deity", routing::get(deity::deity_show))
        .route("/deity/select/{slug}", routing::post(deity::deity_select))
        .route("/deity/change", routing::post(deity::deity_change))
        // Tower
        .route("/tower", routing::get(tower::tower_show))
        // Housing
        .route("/house", routing::get(house::house_show))
        .route("/house/land", routing::post(house::house_buy_land))
        .route("/house/build", routing::post(house::house_build_action))
        .route("/house/bedroom", routing::post(house::house_build_bedroom))
        .route(
            "/house/wardrobe",
            routing::post(house::house_build_wardrobe),
        )
        .route("/house/adorn", routing::post(house::house_adorn))
        .route("/house/rest", routing::post(house::house_rest))
        .route("/house/rename", routing::post(house::house_rename))
        .route("/house/sell", routing::post(house::house_sell))
        .route("/house/leave", routing::post(house::house_leave))
        .route("/house/buy/{id}", routing::post(house::house_buy))
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

fn outpost_routes() -> Router<AppState> {
    Router::new()
        // Outpost management (outposts.php)
        .route("/outposts", routing::get(outpost::outpost_menu))
        .route("/outposts/buy", routing::post(outpost::buy_outpost))
        .route("/outposts/my", routing::get(outpost::my_outpost))
        .route("/outposts/bonus/{field}", routing::post(outpost::add_bonus))
        .route("/outposts/treasury", routing::get(outpost::treasury_show))
        .route(
            "/outposts/treasury/deposit",
            routing::post(outpost::treasury_deposit),
        )
        .route(
            "/outposts/treasury/withdraw",
            routing::post(outpost::treasury_withdraw),
        )
        .route("/outposts/shop", routing::get(outpost::shop_show))
        .route("/outposts/shop/army", routing::post(outpost::shop_buy_army))
        .route(
            "/outposts/shop/upgrade",
            routing::post(outpost::shop_upgrade),
        )
        .route(
            "/outposts/shop/lair",
            routing::post(outpost::shop_build_lair),
        )
        .route(
            "/outposts/shop/barracks",
            routing::post(outpost::shop_build_barracks),
        )
        .route(
            "/outposts/veterans/{id}",
            routing::get(outpost::veteran_detail),
        )
        .route(
            "/outposts/veterans/{id}/equip",
            routing::post(outpost::veteran_equip),
        )
        .route("/outposts/taxes", routing::get(outpost::taxes_show))
        .route(
            "/outposts/taxes/collect",
            routing::post(outpost::taxes_collect),
        )
        .route("/outposts/list", routing::get(outpost::list_outposts))
        .route("/outposts/battle", routing::get(outpost::battle_show))
        .route(
            "/outposts/battle/execute",
            routing::post(outpost::battle_execute),
        )
        .route("/outposts/guide", routing::get(outpost::guide))
        // Garrison missions (outpost.php)
        .route("/garrison", routing::get(outpost::garrison_show))
        .route(
            "/garrison/generate",
            routing::post(outpost::garrison_generate),
        )
        .route(
            "/garrison/execute/{index}",
            routing::post(outpost::garrison_execute),
        )
}

fn quest_routes() -> Router<AppState> {
    Router::new()
        // Labyrinth (grid.php)
        .route("/labyrinth", routing::get(quest::labyrinth_show))
        .route(
            "/labyrinth/explore",
            routing::post(quest::labyrinth_explore),
        )
        // Chronicle missions (chronicle.php)
        .route("/chronicle", routing::get(quest::chronicle_show))
        .route("/chronicle/{id}", routing::get(quest::chronicle_detail))
        .route("/chronicle/start", routing::post(quest::chronicle_start))
        // Active mission navigation (mission.php)
        .route("/mission", routing::post(quest::mission_advance))
        // Maze (maze.php)
        .route("/maze", routing::get(quest::maze_show))
        .route("/maze/explore", routing::post(quest::maze_explore))
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
        // Room chat
        .route("/room", routing::get(room::room_page))
        .route("/room/messages", routing::get(room::room_messages))
        .route("/room/send", routing::post(room::room_send))
        .route("/room/quit", routing::post(room::room_quit))
        .route(
            "/room/admin/delete-msg",
            routing::post(room::room_admin_delete_msg),
        )
        .route("/room/admin/remove", routing::post(room::room_admin_remove))
        .route("/room/admin/invite", routing::post(room::room_admin_invite))
        .route("/room/admin/desc", routing::post(room::room_admin_desc))
        .route("/room/admin/name", routing::post(room::room_admin_name))
        .route(
            "/room/admin/npc-add",
            routing::post(room::room_admin_npc_add),
        )
        .route(
            "/room/admin/npc-remove",
            routing::post(room::room_admin_npc_remove),
        )
        .route(
            "/room/admin/co-owner",
            routing::post(room::room_admin_co_owner),
        )
        .route("/room/admin/color", routing::post(room::room_admin_color))
        .route("/room/admin/rent", routing::post(room::room_admin_rent))
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
        .merge(forum_routes())
        .merge(tribe_forum_routes())
        .merge(content_routes())
        .merge(pages_routes())
        .merge(court_routes())
        // Jail
        .route("/jail", routing::get(jail::jail_view))
        .route(
            "/jail/bail/{id}",
            routing::get(jail::jail_bail_confirm).post(jail::jail_bail_pay),
        )
        // Player profiles
        .route("/player/{id}", routing::get(player_profile::player_profile))
        .route("/view", routing::get(player_profile::legacy_view_redirect))
        .route("/view/{id}", routing::get(player_profile::player_profile))
        .route(
            "/stats",
            routing::get(player_profile::legacy_stats_redirect),
        )
}

fn forum_routes() -> Router<AppState> {
    Router::new()
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

fn tribe_forum_routes() -> Router<AppState> {
    Router::new()
        .route("/tforums", routing::get(tribe_forum::tforums_topics))
        .route("/tforums/new", routing::get(tribe_forum::tforums_new_posts))
        .route(
            "/tforums/search",
            routing::post(tribe_forum::tforums_search),
        )
        .route(
            "/tforums/topic/new",
            routing::post(tribe_forum::tforums_add_topic),
        )
        .route(
            "/tforums/topic/{id}",
            routing::get(tribe_forum::tforums_topic_read),
        )
        .route(
            "/tforums/topic/{id}/reply",
            routing::post(tribe_forum::tforums_add_reply),
        )
        .route(
            "/tforums/topic/{id}/delete",
            routing::post(tribe_forum::tforums_delete_topic),
        )
        .route(
            "/tforums/topic/{id}/sticky",
            routing::post(tribe_forum::tforums_toggle_sticky),
        )
        .route(
            "/tforums/delete-topics",
            routing::post(tribe_forum::tforums_bulk_delete),
        )
        .route(
            "/tforums/reply/{id}/delete",
            routing::post(tribe_forum::tforums_delete_reply),
        )
}

fn content_routes() -> Router<AppState> {
    Router::new()
        // Updates
        .route("/updates", routing::get(content::updates_page))
        .route(
            "/updates/add",
            routing::get(content::add_update_form).post(content::add_update_action),
        )
        // News
        .route("/news", routing::get(content::news_page))
        .route(
            "/news/add",
            routing::get(content::add_news_form).post(content::add_news_action),
        )
        // Comments (unified)
        .route(
            "/comments/{target_type}/{target_id}",
            routing::get(content::comments_page).post(content::add_comment),
        )
        .route(
            "/comments/{target_type}/{target_id}/delete/{comment_id}",
            routing::post(content::delete_comment),
        )
        // Newspaper
        .route("/newspaper", routing::get(content::newspaper_page))
        .route(
            "/newspaper/archive",
            routing::get(content::newspaper_archive),
        )
        .route(
            "/newspaper/issue/{id}",
            routing::get(content::newspaper_issue),
        )
        .route(
            "/newspaper/article/{id}",
            routing::get(content::newspaper_article),
        )
        .route(
            "/newspaper/edit",
            routing::get(content::newspaper_edit_form).post(content::newspaper_edit_action),
        )
        .route(
            "/newspaper/release",
            routing::post(content::newspaper_release),
        )
        .route(
            "/newspaper/article/{id}/delete",
            routing::post(content::newspaper_delete_article),
        )
        // Polls
        .route("/polls", routing::get(content::polls_page))
        .route("/polls/vote", routing::post(content::polls_vote))
        .route("/polls/history", routing::get(content::polls_history))
        // Proposals
        .route(
            "/proposals/{ptype}",
            routing::get(content::proposal_form).post(content::proposal_submit),
        )
}

fn pages_routes() -> Router<AppState> {
    Router::new()
        // Notes (personal notebook)
        .route("/notes", routing::get(pages::notes_page))
        .route(
            "/notes/add",
            routing::get(pages::note_form).post(pages::note_save),
        )
        .route("/notes/delete/{id}", routing::post(pages::note_delete))
        // Library
        .route("/library", routing::get(pages::library_index))
        .route(
            "/library/add",
            routing::get(pages::library_add_form).post(pages::library_add_action),
        )
        .route("/library/admin", routing::get(pages::library_admin))
        .route(
            "/library/admin/edit/{id}",
            routing::get(pages::library_admin_edit).post(pages::library_admin_edit_action),
        )
        .route(
            "/library/admin/approve/{id}",
            routing::post(pages::library_admin_approve),
        )
        .route(
            "/library/admin/delete/{id}",
            routing::post(pages::library_admin_delete),
        )
        .route("/library/text/{id}", routing::get(pages::library_text))
        .route("/library/{text_type}", routing::get(pages::library_list))
        // Roleplay profiles
        .route("/roleplay/{id}", routing::get(pages::roleplay_view))
    // Chronicle routes are in quest_routes() — not duplicated here.
}

fn court_routes() -> Router<AppState> {
    Router::new()
        .route("/court", routing::get(court::court_menu))
        .route("/court/list/{role}", routing::get(court::court_staff_list))
        .route("/court/docs/{kind}", routing::get(court::court_doc_list))
        .route("/court/doc/{id}", routing::get(court::court_doc_detail))
        .route(
            "/court/create/{kind}",
            routing::get(court::court_doc_create_form).post(court::court_doc_create),
        )
        .route(
            "/court/edit/{id}",
            routing::get(court::court_doc_edit_form).post(court::court_doc_edit),
        )
        .route("/court/comment", routing::post(court::court_add_comment))
        .route(
            "/court/comment/delete/{id}",
            routing::post(court::court_delete_comment),
        )
}
