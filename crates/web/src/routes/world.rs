//! City, map, and world navigation routes.

use axum::{Router, middleware, routing};

use crate::handlers::{
    bank, city, equipment, gathering, locations, map, market, shops, spells, travel,
};
use crate::middleware::guards::require_authenticated;
use crate::state::AppState;

/// Register city, travel, map, and secondary location routes.
pub fn routes() -> Router<AppState> {
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
        // Economy routes
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
        .layer(middleware::from_fn(require_authenticated))
}
