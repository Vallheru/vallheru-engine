//! Queries for player housing.

use sqlx::PgPool;

// ---------------------------------------------------------------------------
// Row types
// ---------------------------------------------------------------------------

/// A house row from the `houses` table.
#[derive(Debug, sqlx::FromRow)]
pub struct HouseRow {
    pub id: i32,
    pub owner: i32,
    pub locator: i32,
    pub location: String,
    pub name: String,
    pub size: i32,
    pub build: i32,
    pub value: i32,
    pub points: i32,
    pub used: i32,
    pub bedroom: bool,
    pub wardrobe: i32,
    pub cost: i32,
    pub seller: i32,
}

/// Minimal info for house listing.
#[derive(Debug, sqlx::FromRow)]
pub struct HouseListRow {
    pub id: i32,
    pub owner: i32,
    pub locator: i32,
    pub name: String,
    pub size: i32,
    pub build: i32,
    pub value: i32,
}

/// For sale listing.
#[derive(Debug, sqlx::FromRow)]
pub struct HouseForSaleRow {
    pub id: i32,
    pub name: String,
    pub size: i32,
    pub build: i32,
    pub value: i32,
    pub cost: i32,
    pub seller: i32,
}

// ---------------------------------------------------------------------------
// Queries
// ---------------------------------------------------------------------------

/// Find a house that a player owns or is a locator of in a city.
pub async fn find_player_house(
    pool: &PgPool,
    player_id: i32,
    location: &str,
) -> Result<Option<HouseRow>, sqlx::Error> {
    sqlx::query_as::<_, HouseRow>(
        "SELECT * FROM houses WHERE location = $1 AND (owner = $2 OR locator = $2) LIMIT 1",
    )
    .bind(location)
    .bind(player_id)
    .fetch_optional(pool)
    .await
}

/// Find a house by id.
pub async fn find_house_by_id(
    pool: &PgPool,
    house_id: i32,
) -> Result<Option<HouseRow>, sqlx::Error> {
    sqlx::query_as::<_, HouseRow>("SELECT * FROM houses WHERE id = $1")
        .bind(house_id)
        .fetch_optional(pool)
        .await
}

/// Buy the initial land parcel (costs platinum).
pub async fn buy_initial_land(
    pool: &PgPool,
    player_id: i32,
    location: &str,
) -> Result<i32, sqlx::Error> {
    let row = sqlx::query_scalar::<_, i32>(
        "INSERT INTO houses (owner, location) VALUES ($1, $2) RETURNING id",
    )
    .bind(player_id)
    .bind(location)
    .fetch_one(pool)
    .await?;

    sqlx::query("UPDATE players SET platinum = platinum - 20 WHERE id = $1")
        .bind(player_id)
        .execute(pool)
        .await?;

    Ok(row)
}

/// Expand house land (costs gold).
pub async fn expand_land(
    pool: &PgPool,
    house_id: i32,
    player_id: i32,
    gold_cost: i32,
) -> Result<(), sqlx::Error> {
    sqlx::query("UPDATE houses SET size = size + 1 WHERE id = $1")
        .bind(house_id)
        .execute(pool)
        .await?;
    sqlx::query("UPDATE players SET credits = credits - $1 WHERE id = $2")
        .bind(gold_cost)
        .bind(player_id)
        .execute(pool)
        .await?;
    Ok(())
}

/// Build a new house on owned land.
pub async fn build_house(
    pool: &PgPool,
    house_id: i32,
    player_id: i32,
    name: &str,
) -> Result<(), sqlx::Error> {
    sqlx::query(
        "UPDATE houses SET name = $1, build = build + 1, points = points - 10 WHERE id = $2",
    )
    .bind(name)
    .bind(house_id)
    .execute(pool)
    .await?;
    sqlx::query("UPDATE players SET credits = credits - 1000 WHERE id = $1")
        .bind(player_id)
        .execute(pool)
        .await?;
    Ok(())
}

/// Upgrade (add a building level).
pub async fn upgrade_house(
    pool: &PgPool,
    house_id: i32,
    player_id: i32,
    gold_cost: i32,
    new_value: i32,
) -> Result<(), sqlx::Error> {
    sqlx::query(
        "UPDATE houses SET build = build + 1, points = points - 10, value = $1 WHERE id = $2",
    )
    .bind(new_value)
    .bind(house_id)
    .execute(pool)
    .await?;
    sqlx::query("UPDATE players SET credits = credits - $1 WHERE id = $2")
        .bind(gold_cost)
        .bind(player_id)
        .execute(pool)
        .await?;
    Ok(())
}

/// Build a bedroom.
pub async fn build_bedroom(
    pool: &PgPool,
    house_id: i32,
    player_id: i32,
) -> Result<(), sqlx::Error> {
    sqlx::query(
        "UPDATE houses SET bedroom = TRUE, points = points - 10, used = used + 1 WHERE id = $1",
    )
    .bind(house_id)
    .execute(pool)
    .await?;
    sqlx::query("UPDATE players SET credits = credits - 10000 WHERE id = $1")
        .bind(player_id)
        .execute(pool)
        .await?;
    Ok(())
}

/// Build a wardrobe (storage room).
pub async fn build_wardrobe(
    pool: &PgPool,
    house_id: i32,
    player_id: i32,
    gold_cost: i32,
) -> Result<(), sqlx::Error> {
    sqlx::query(
        "UPDATE houses SET wardrobe = wardrobe + 1, points = points - 10, used = used + 1 \
         WHERE id = $1",
    )
    .bind(house_id)
    .execute(pool)
    .await?;
    sqlx::query("UPDATE players SET credits = credits - $1 WHERE id = $2")
        .bind(gold_cost)
        .bind(player_id)
        .execute(pool)
        .await?;
    Ok(())
}

/// Adorn the house (spend points to increase value).
pub async fn adorn_house(
    pool: &PgPool,
    house_id: i32,
    player_id: i32,
    points_spent: i32,
    gold_cost: i32,
) -> Result<(), sqlx::Error> {
    sqlx::query("UPDATE houses SET points = points - $1, value = value + $1 WHERE id = $2")
        .bind(points_spent)
        .bind(house_id)
        .execute(pool)
        .await?;
    sqlx::query("UPDATE players SET credits = credits - $1 WHERE id = $2")
        .bind(gold_cost)
        .bind(player_id)
        .execute(pool)
        .await?;
    Ok(())
}

/// Rename a house.
pub async fn rename_house(pool: &PgPool, house_id: i32, new_name: &str) -> Result<(), sqlx::Error> {
    sqlx::query("UPDATE houses SET name = $1 WHERE id = $2")
        .bind(new_name)
        .bind(house_id)
        .execute(pool)
        .await?;
    Ok(())
}

/// Mark house rest as used for the day and apply HP/energy/mana gains.
pub async fn house_rest(
    pool: &PgPool,
    player_id: i32,
    new_hp: i32,
    energy_gain: f64,
    new_mana: i32,
) -> Result<(), sqlx::Error> {
    sqlx::query(
        "UPDATE players SET house_rest = TRUE, hp = $1, energy = energy + $2, pm = $3 \
         WHERE id = $4",
    )
    .bind(new_hp)
    .bind(energy_gain)
    .bind(new_mana)
    .bind(player_id)
    .execute(pool)
    .await?;
    Ok(())
}

/// Leave a house (owner or locator).
pub async fn leave_house_locator(pool: &PgPool, house_id: i32) -> Result<(), sqlx::Error> {
    sqlx::query("UPDATE houses SET locator = 0 WHERE id = $1")
        .bind(house_id)
        .execute(pool)
        .await?;
    Ok(())
}

/// Owner leaves: transfer to locator or delete.
pub async fn leave_house_owner(
    pool: &PgPool,
    house_id: i32,
    has_locator: bool,
    locator_id: i32,
) -> Result<(), sqlx::Error> {
    if has_locator {
        sqlx::query("UPDATE houses SET owner = $1, locator = 0 WHERE id = $2")
            .bind(locator_id)
            .bind(house_id)
            .execute(pool)
            .await?;
    } else {
        sqlx::query("DELETE FROM houses WHERE id = $1")
            .bind(house_id)
            .execute(pool)
            .await?;
    }
    Ok(())
}

/// Put house for sale.
pub async fn sell_house(
    pool: &PgPool,
    house_id: i32,
    player_id: i32,
    asking_price: i32,
) -> Result<(), sqlx::Error> {
    sqlx::query("UPDATE houses SET cost = $1, seller = $2, owner = 0, locator = 0 WHERE id = $3")
        .bind(asking_price)
        .bind(player_id)
        .bind(house_id)
        .execute(pool)
        .await?;
    Ok(())
}

/// Buy a house from the market.
pub async fn buy_house(
    pool: &PgPool,
    house_id: i32,
    buyer_id: i32,
    seller_id: i32,
    price: i32,
) -> Result<(), sqlx::Error> {
    sqlx::query("UPDATE players SET credits = credits - $1 WHERE id = $2")
        .bind(price)
        .bind(buyer_id)
        .execute(pool)
        .await?;
    sqlx::query("UPDATE players SET bank = bank + $1 WHERE id = $2")
        .bind(price)
        .bind(seller_id)
        .execute(pool)
        .await?;
    sqlx::query("UPDATE houses SET cost = 0, seller = 0, owner = $1 WHERE id = $2")
        .bind(buyer_id)
        .bind(house_id)
        .execute(pool)
        .await?;
    Ok(())
}

/// Withdraw a house from the market (seller reclaims it).
pub async fn withdraw_house(
    pool: &PgPool,
    house_id: i32,
    player_id: i32,
) -> Result<(), sqlx::Error> {
    sqlx::query("UPDATE houses SET cost = 0, seller = 0, owner = $1 WHERE id = $2")
        .bind(player_id)
        .bind(house_id)
        .execute(pool)
        .await?;
    Ok(())
}

/// List built houses in a city (top 50 by build level).
pub async fn list_houses(pool: &PgPool, location: &str) -> Result<Vec<HouseListRow>, sqlx::Error> {
    sqlx::query_as::<_, HouseListRow>(
        "SELECT id, owner, locator, name, size, build, value \
         FROM houses WHERE build > 0 AND owner > 0 AND location = $1 \
         ORDER BY build DESC LIMIT 50",
    )
    .bind(location)
    .fetch_all(pool)
    .await
}

/// List houses for sale in a city.
pub async fn list_houses_for_sale(
    pool: &PgPool,
    location: &str,
) -> Result<Vec<HouseForSaleRow>, sqlx::Error> {
    sqlx::query_as::<_, HouseForSaleRow>(
        "SELECT id, name, size, build, value, cost, seller \
         FROM houses WHERE owner = 0 AND location = $1 ORDER BY build DESC",
    )
    .bind(location)
    .fetch_all(pool)
    .await
}

/// Set a locator on a house.
pub async fn set_locator(pool: &PgPool, house_id: i32, locator_id: i32) -> Result<(), sqlx::Error> {
    sqlx::query("UPDATE houses SET locator = $1 WHERE id = $2")
        .bind(locator_id)
        .bind(house_id)
        .execute(pool)
        .await?;
    Ok(())
}

/// Remove a locator from a house.
pub async fn remove_locator(pool: &PgPool, house_id: i32) -> Result<(), sqlx::Error> {
    sqlx::query("UPDATE houses SET locator = 0 WHERE id = $1")
        .bind(house_id)
        .execute(pool)
        .await?;
    Ok(())
}

/// Look up a player's username by id.
pub async fn player_username(pool: &PgPool, player_id: i32) -> Result<Option<String>, sqlx::Error> {
    sqlx::query_scalar::<_, String>("SELECT username FROM players WHERE id = $1")
        .bind(player_id)
        .fetch_optional(pool)
        .await
}
