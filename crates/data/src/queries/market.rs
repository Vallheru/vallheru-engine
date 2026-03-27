//! Market-related persistence queries.
//!
//! Covers all 8 player-to-player market categories: browse (paginated),
//! list, buy, cancel, top-up, price change, and bulk-delete operations.
//!
//! Each category uses a different table (pmarket, hmarket, amarket,
//! `core_market`) or the shared `equipment` / `potions` tables with
//! status='R'. The query functions are grouped by category but share
//! naming conventions and return consistent row structs.

use sqlx::PgPool;

// =========================================================================
// Row structs
// =========================================================================

/// A mineral market listing row (pmarket table).
#[derive(Debug, sqlx::FromRow, serde::Serialize)]
pub struct MineralListingRow {
    pub id: i32,
    pub seller: i32,
    pub ilosc: i32,
    pub cost: i64,
    pub nazwa: String,
    pub seller_name: Option<String>,
}

/// A herb market listing row (hmarket table).
#[derive(Debug, sqlx::FromRow, serde::Serialize)]
pub struct HerbListingRow {
    pub id: i32,
    pub seller: i32,
    pub ilosc: i32,
    pub cost: i64,
    pub nazwa: String,
    pub seller_name: Option<String>,
}

/// An equipment market listing row (equipment table with status='R').
#[derive(Debug, sqlx::FromRow, serde::Serialize)]
pub struct EquipmentListingRow {
    pub id: i32,
    pub owner: i32,
    pub name: String,
    pub power: i32,
    #[serde(rename = "type")]
    pub r#type: String,
    pub cost: i64,
    pub minlev: i32,
    pub zr: i32,
    pub wt: i32,
    pub szyb: i32,
    pub maxwt: i32,
    pub poison: i32,
    pub amount: i32,
    pub twohand: String,
    pub ptype: String,
    pub owner_name: Option<String>,
}

/// A potion market listing row (potions table with status='R').
#[derive(Debug, sqlx::FromRow, serde::Serialize)]
pub struct PotionListingRow {
    pub id: i32,
    pub owner: i32,
    pub name: String,
    #[serde(rename = "type")]
    pub r#type: String,
    pub efect: String,
    pub power: i32,
    pub amount: i32,
    pub cost: i64,
    pub owner_name: Option<String>,
}

/// An astral market listing row (amarket table).
#[derive(Debug, sqlx::FromRow, serde::Serialize)]
pub struct AstralListingRow {
    pub id: i32,
    pub seller: i32,
    #[serde(rename = "type")]
    pub r#type: String,
    pub number: i16,
    pub amount: i32,
    pub cost: i64,
    pub seller_name: Option<String>,
}

/// A pet market listing row (`core_market` table).
#[derive(Debug, sqlx::FromRow, serde::Serialize)]
pub struct PetListingRow {
    pub id: i32,
    pub name: String,
    pub cost: i64,
    pub seller: i32,
    #[serde(rename = "type")]
    pub r#type: String,
    pub power: f64,
    pub defense: f64,
    pub gender: String,
    pub ref_id: i32,
    pub wins: i32,
    pub losses: i32,
    pub seller_name: Option<String>,
}

// =========================================================================
// Mineral market (pmarket)
// =========================================================================

/// Count mineral market listings, optionally filtered by name.
pub async fn count_mineral_listings(
    pool: &PgPool,
    search: Option<&str>,
) -> Result<i64, sqlx::Error> {
    let row: (i64,) = if let Some(s) = search {
        sqlx::query_as("SELECT COUNT(*) FROM pmarket WHERE nazwa ILIKE $1")
            .bind(s)
            .fetch_one(pool)
            .await?
    } else {
        sqlx::query_as("SELECT COUNT(*) FROM pmarket")
            .fetch_one(pool)
            .await?
    };
    Ok(row.0)
}

/// Browse mineral market listings with pagination and sorting.
///
/// `sort_col` and `sort_dir` must be validated by the domain layer before
/// calling this function. We use a safe allow-list approach for the
/// dynamic ORDER BY since sqlx doesn't support parameterized column names.
pub async fn browse_mineral_listings(
    pool: &PgPool,
    search: Option<&str>,
    sort_col: &str,
    sort_dir: &str,
    limit: i32,
    offset: i32,
) -> Result<Vec<MineralListingRow>, sqlx::Error> {
    // Build safe ORDER BY clause from validated inputs.
    let order_col = match sort_col {
        "nazwa" => "p.nazwa",
        "ilosc" => "p.ilosc",
        "cost" => "p.cost",
        "seller" => "pl.username",
        _ => "p.id",
    };
    let order_dir = if sort_dir == "ASC" { "ASC" } else { "DESC" };
    let query = format!(
        "SELECT p.id, p.seller, p.ilosc, p.cost, p.nazwa, \
         pl.username AS seller_name \
         FROM pmarket p \
         LEFT JOIN players pl ON pl.id = p.seller \
         {where_clause} \
         ORDER BY {order_col} {order_dir} \
         LIMIT $1 OFFSET $2",
        where_clause = if search.is_some() {
            "WHERE p.nazwa ILIKE $3"
        } else {
            ""
        },
        order_col = order_col,
        order_dir = order_dir,
    );

    if let Some(s) = search {
        sqlx::query_as(&query)
            .bind(limit)
            .bind(offset)
            .bind(s)
            .fetch_all(pool)
            .await
    } else {
        sqlx::query_as(&query)
            .bind(limit)
            .bind(offset)
            .fetch_all(pool)
            .await
    }
}

/// Find a single mineral listing by ID.
pub async fn find_mineral_listing(
    pool: &PgPool,
    listing_id: i32,
) -> Result<Option<MineralListingRow>, sqlx::Error> {
    sqlx::query_as(
        "SELECT p.id, p.seller, p.ilosc, p.cost, p.nazwa, \
         pl.username AS seller_name \
         FROM pmarket p \
         LEFT JOIN players pl ON pl.id = p.seller \
         WHERE p.id = $1",
    )
    .bind(listing_id)
    .fetch_optional(pool)
    .await
}

/// Insert a new mineral listing.
pub async fn insert_mineral_listing(
    pool: &PgPool,
    seller_id: i32,
    quantity: i32,
    cost: i64,
    nazwa: &str,
) -> Result<(), sqlx::Error> {
    sqlx::query(
        "INSERT INTO pmarket (seller, ilosc, cost, nazwa, lang) \
         VALUES ($1, $2, $3, $4, 'pl')",
    )
    .bind(seller_id)
    .bind(quantity)
    .bind(cost)
    .bind(nazwa)
    .execute(pool)
    .await?;
    Ok(())
}

/// Find an existing mineral listing by seller and commodity name (for merging).
pub async fn find_mineral_listing_by_seller(
    pool: &PgPool,
    seller_id: i32,
    nazwa: &str,
) -> Result<Option<i32>, sqlx::Error> {
    let row: Option<(i32,)> =
        sqlx::query_as("SELECT id FROM pmarket WHERE seller = $1 AND nazwa = $2")
            .bind(seller_id)
            .bind(nazwa)
            .fetch_optional(pool)
            .await?;
    Ok(row.map(|r| r.0))
}

/// Top-up an existing mineral listing (add quantity).
pub async fn topup_mineral_listing(
    pool: &PgPool,
    listing_id: i32,
    add_quantity: i32,
) -> Result<(), sqlx::Error> {
    sqlx::query("UPDATE pmarket SET ilosc = ilosc + $1 WHERE id = $2")
        .bind(add_quantity)
        .bind(listing_id)
        .execute(pool)
        .await?;
    Ok(())
}

/// Reduce quantity on a mineral listing; delete if it reaches zero.
pub async fn reduce_mineral_listing(
    pool: &PgPool,
    listing_id: i32,
    buy_quantity: i32,
    remaining: i32,
) -> Result<(), sqlx::Error> {
    if remaining <= 0 {
        sqlx::query("DELETE FROM pmarket WHERE id = $1")
            .bind(listing_id)
            .execute(pool)
            .await?;
    } else {
        sqlx::query("UPDATE pmarket SET ilosc = ilosc - $1 WHERE id = $2")
            .bind(buy_quantity)
            .bind(listing_id)
            .execute(pool)
            .await?;
    }
    Ok(())
}

/// Delete a mineral listing (cancel).
pub async fn delete_mineral_listing(pool: &PgPool, listing_id: i32) -> Result<(), sqlx::Error> {
    sqlx::query("DELETE FROM pmarket WHERE id = $1")
        .bind(listing_id)
        .execute(pool)
        .await?;
    Ok(())
}

/// Delete all mineral listings for a seller.
pub async fn delete_all_mineral_listings(
    pool: &PgPool,
    seller_id: i32,
) -> Result<Vec<(String, i32)>, sqlx::Error> {
    let rows: Vec<(String, i32)> =
        sqlx::query_as("SELECT nazwa, ilosc FROM pmarket WHERE seller = $1")
            .bind(seller_id)
            .fetch_all(pool)
            .await?;
    sqlx::query("DELETE FROM pmarket WHERE seller = $1")
        .bind(seller_id)
        .execute(pool)
        .await?;
    Ok(rows)
}

/// Change price on a mineral listing.
pub async fn change_mineral_price(
    pool: &PgPool,
    listing_id: i32,
    new_price: i64,
) -> Result<(), sqlx::Error> {
    sqlx::query("UPDATE pmarket SET cost = $1 WHERE id = $2")
        .bind(new_price)
        .bind(listing_id)
        .execute(pool)
        .await?;
    Ok(())
}

/// Count a player's mineral listings.
pub async fn count_player_mineral_listings(
    pool: &PgPool,
    seller_id: i32,
) -> Result<i64, sqlx::Error> {
    let row: (i64,) = sqlx::query_as("SELECT COUNT(*) FROM pmarket WHERE seller = $1")
        .bind(seller_id)
        .fetch_one(pool)
        .await?;
    Ok(row.0)
}

// =========================================================================
// Herb market (hmarket)
// =========================================================================

/// Count herb market listings, optionally filtered by name.
pub async fn count_herb_listings(pool: &PgPool, search: Option<&str>) -> Result<i64, sqlx::Error> {
    let row: (i64,) = if let Some(s) = search {
        sqlx::query_as("SELECT COUNT(*) FROM hmarket WHERE nazwa ILIKE $1")
            .bind(s)
            .fetch_one(pool)
            .await?
    } else {
        sqlx::query_as("SELECT COUNT(*) FROM hmarket")
            .fetch_one(pool)
            .await?
    };
    Ok(row.0)
}

/// Browse herb market listings with pagination and sorting.
pub async fn browse_herb_listings(
    pool: &PgPool,
    search: Option<&str>,
    sort_col: &str,
    sort_dir: &str,
    limit: i32,
    offset: i32,
) -> Result<Vec<HerbListingRow>, sqlx::Error> {
    let order_col = match sort_col {
        "nazwa" => "h.nazwa",
        "ilosc" => "h.ilosc",
        "cost" => "h.cost",
        "seller" => "pl.username",
        _ => "h.id",
    };
    let order_dir = if sort_dir == "ASC" { "ASC" } else { "DESC" };
    let query = format!(
        "SELECT h.id, h.seller, h.ilosc, h.cost, h.nazwa, \
         pl.username AS seller_name \
         FROM hmarket h \
         LEFT JOIN players pl ON pl.id = h.seller \
         {where_clause} \
         ORDER BY {order_col} {order_dir} \
         LIMIT $1 OFFSET $2",
        where_clause = if search.is_some() {
            "WHERE h.nazwa ILIKE $3"
        } else {
            ""
        },
        order_col = order_col,
        order_dir = order_dir,
    );

    if let Some(s) = search {
        sqlx::query_as(&query)
            .bind(limit)
            .bind(offset)
            .bind(s)
            .fetch_all(pool)
            .await
    } else {
        sqlx::query_as(&query)
            .bind(limit)
            .bind(offset)
            .fetch_all(pool)
            .await
    }
}

/// Find a single herb listing by ID.
pub async fn find_herb_listing(
    pool: &PgPool,
    listing_id: i32,
) -> Result<Option<HerbListingRow>, sqlx::Error> {
    sqlx::query_as(
        "SELECT h.id, h.seller, h.ilosc, h.cost, h.nazwa, \
         pl.username AS seller_name \
         FROM hmarket h \
         LEFT JOIN players pl ON pl.id = h.seller \
         WHERE h.id = $1",
    )
    .bind(listing_id)
    .fetch_optional(pool)
    .await
}

/// Insert a new herb listing.
pub async fn insert_herb_listing(
    pool: &PgPool,
    seller_id: i32,
    quantity: i32,
    cost: i64,
    nazwa: &str,
) -> Result<(), sqlx::Error> {
    sqlx::query(
        "INSERT INTO hmarket (seller, ilosc, cost, nazwa, lang) \
         VALUES ($1, $2, $3, $4, 'pl')",
    )
    .bind(seller_id)
    .bind(quantity)
    .bind(cost)
    .bind(nazwa)
    .execute(pool)
    .await?;
    Ok(())
}

/// Find an existing herb listing by seller and name (for merging).
pub async fn find_herb_listing_by_seller(
    pool: &PgPool,
    seller_id: i32,
    nazwa: &str,
) -> Result<Option<i32>, sqlx::Error> {
    let row: Option<(i32,)> =
        sqlx::query_as("SELECT id FROM hmarket WHERE seller = $1 AND nazwa = $2")
            .bind(seller_id)
            .bind(nazwa)
            .fetch_optional(pool)
            .await?;
    Ok(row.map(|r| r.0))
}

/// Top-up an existing herb listing.
pub async fn topup_herb_listing(
    pool: &PgPool,
    listing_id: i32,
    add_quantity: i32,
) -> Result<(), sqlx::Error> {
    sqlx::query("UPDATE hmarket SET ilosc = ilosc + $1 WHERE id = $2")
        .bind(add_quantity)
        .bind(listing_id)
        .execute(pool)
        .await?;
    Ok(())
}

/// Reduce quantity on a herb listing.
pub async fn reduce_herb_listing(
    pool: &PgPool,
    listing_id: i32,
    buy_quantity: i32,
    remaining: i32,
) -> Result<(), sqlx::Error> {
    if remaining <= 0 {
        sqlx::query("DELETE FROM hmarket WHERE id = $1")
            .bind(listing_id)
            .execute(pool)
            .await?;
    } else {
        sqlx::query("UPDATE hmarket SET ilosc = ilosc - $1 WHERE id = $2")
            .bind(buy_quantity)
            .bind(listing_id)
            .execute(pool)
            .await?;
    }
    Ok(())
}

/// Delete a herb listing.
pub async fn delete_herb_listing(pool: &PgPool, listing_id: i32) -> Result<(), sqlx::Error> {
    sqlx::query("DELETE FROM hmarket WHERE id = $1")
        .bind(listing_id)
        .execute(pool)
        .await?;
    Ok(())
}

/// Delete all herb listings for a seller.
pub async fn delete_all_herb_listings(
    pool: &PgPool,
    seller_id: i32,
) -> Result<Vec<(String, i32)>, sqlx::Error> {
    let rows: Vec<(String, i32)> =
        sqlx::query_as("SELECT nazwa, ilosc FROM hmarket WHERE seller = $1")
            .bind(seller_id)
            .fetch_all(pool)
            .await?;
    sqlx::query("DELETE FROM hmarket WHERE seller = $1")
        .bind(seller_id)
        .execute(pool)
        .await?;
    Ok(rows)
}

/// Change price on a herb listing.
pub async fn change_herb_price(
    pool: &PgPool,
    listing_id: i32,
    new_price: i64,
) -> Result<(), sqlx::Error> {
    sqlx::query("UPDATE hmarket SET cost = $1 WHERE id = $2")
        .bind(new_price)
        .bind(listing_id)
        .execute(pool)
        .await?;
    Ok(())
}

/// Count a player's herb listings.
pub async fn count_player_herb_listings(pool: &PgPool, seller_id: i32) -> Result<i64, sqlx::Error> {
    let row: (i64,) = sqlx::query_as("SELECT COUNT(*) FROM hmarket WHERE seller = $1")
        .bind(seller_id)
        .fetch_one(pool)
        .await?;
    Ok(row.0)
}

// =========================================================================
// Equipment market (equipment table, status='R', type NOT IN ('I', 'O', 'Q'))
// =========================================================================

/// Count equipment market listings.
pub async fn count_equipment_listings(
    pool: &PgPool,
    search: Option<&str>,
    type_filter: Option<&str>,
    min_level: Option<i32>,
    max_level: Option<i32>,
) -> Result<i64, sqlx::Error> {
    use std::fmt::Write;
    let mut sql = String::from(
        "SELECT COUNT(*) FROM equipment WHERE status = 'R' AND type NOT IN ('I', 'O', 'Q')",
    );
    let mut param_idx = 1;
    let mut binds: Vec<String> = Vec::new();

    if let Some(t) = type_filter {
        let _ = write!(sql, " AND type = ${param_idx}");
        param_idx += 1;
        binds.push(t.to_string());
    }
    if let Some(lo) = min_level {
        let _ = write!(sql, " AND minlev >= ${param_idx}");
        param_idx += 1;
        binds.push(lo.to_string());
    }
    if let Some(hi) = max_level {
        let _ = write!(sql, " AND minlev <= ${param_idx}");
        param_idx += 1;
        binds.push(hi.to_string());
    }
    if let Some(s) = search {
        let _ = write!(sql, " AND name ILIKE ${param_idx}");
        let _ = param_idx;
        binds.push(s.to_string());
    }

    // Build the query dynamically
    let mut q = sqlx::query_as::<_, (i64,)>(&sql);
    for b in &binds {
        q = q.bind(b);
    }
    let row = q.fetch_one(pool).await?;
    Ok(row.0)
}

/// Filter options for equipment market browse.
pub struct EquipmentBrowseFilter<'a> {
    pub search: Option<&'a str>,
    pub type_filter: Option<&'a str>,
    pub min_level: Option<i32>,
    pub max_level: Option<i32>,
    pub sort_col: &'a str,
    pub sort_dir: &'a str,
    pub limit: i32,
    pub offset: i32,
}

/// Browse equipment market listings with pagination and sorting.
#[allow(clippy::too_many_lines)]
pub async fn browse_equipment_listings(
    pool: &PgPool,
    filter: &EquipmentBrowseFilter<'_>,
) -> Result<Vec<EquipmentListingRow>, sqlx::Error> {
    use std::fmt::Write;

    let order_col = match filter.sort_col {
        "name" => "e.name",
        "power" => "e.power",
        "wt" => "e.wt",
        "szyb" => "e.szyb",
        "zr" => "e.zr",
        "minlev" => "e.minlev",
        "amount" => "e.amount",
        "cost" => "e.cost",
        "owner" => "pl.username",
        _ => "e.id",
    };
    let order_dir = if filter.sort_dir == "ASC" {
        "ASC"
    } else {
        "DESC"
    };

    let mut where_clause = String::from("WHERE e.status = 'R' AND e.type NOT IN ('I', 'O', 'Q')");
    let mut binds: Vec<String> = Vec::new();
    let mut pidx = 3; // $1=limit, $2=offset
    if let Some(t) = filter.type_filter {
        let _ = write!(where_clause, " AND e.type = ${pidx}");
        pidx += 1;
        binds.push(t.to_string());
    }
    if let Some(lo) = filter.min_level {
        let _ = write!(where_clause, " AND e.minlev >= ${pidx}");
        pidx += 1;
        binds.push(lo.to_string());
    }
    if let Some(hi) = filter.max_level {
        let _ = write!(where_clause, " AND e.minlev <= ${pidx}");
        pidx += 1;
        binds.push(hi.to_string());
    }
    if let Some(s) = filter.search {
        let _ = write!(where_clause, " AND e.name ILIKE ${pidx}");
        binds.push(s.to_string());
    }

    let query = format!(
        "SELECT e.id, e.owner, e.name, e.power, e.type, e.cost, e.minlev, \
         e.zr, e.wt, e.szyb, e.maxwt, e.poison, e.amount, e.twohand, e.ptype, \
         pl.username AS owner_name \
         FROM equipment e \
         LEFT JOIN players pl ON pl.id = e.owner \
         {where_clause} \
         ORDER BY {order_col} {order_dir} \
         LIMIT $1 OFFSET $2",
    );

    let mut q = sqlx::query_as::<_, EquipmentListingRow>(&query);
    q = q.bind(filter.limit).bind(filter.offset);
    for b in &binds {
        q = q.bind(b);
    }
    q.fetch_all(pool).await
}

/// Find a single equipment listing by ID.
pub async fn find_equipment_listing(
    pool: &PgPool,
    listing_id: i32,
) -> Result<Option<EquipmentListingRow>, sqlx::Error> {
    sqlx::query_as(
        "SELECT e.id, e.owner, e.name, e.power, e.type, e.cost, e.minlev, \
         e.zr, e.wt, e.szyb, e.maxwt, e.poison, e.amount, e.twohand, e.ptype, \
         pl.username AS owner_name \
         FROM equipment e \
         LEFT JOIN players pl ON pl.id = e.owner \
         WHERE e.id = $1 AND e.status = 'R'",
    )
    .bind(listing_id)
    .fetch_optional(pool)
    .await
}

/// Set equipment status to 'R' (market) and set cost for selling.
pub async fn list_equipment_on_market(
    pool: &PgPool,
    item_id: i32,
    cost: i64,
) -> Result<(), sqlx::Error> {
    sqlx::query("UPDATE equipment SET status = 'R', cost = $1 WHERE id = $2")
        .bind(cost)
        .bind(item_id)
        .execute(pool)
        .await?;
    Ok(())
}

/// Return equipment from market (status='R' → status='U').
pub async fn unlist_equipment_from_market(pool: &PgPool, item_id: i32) -> Result<(), sqlx::Error> {
    sqlx::query("UPDATE equipment SET status = 'U', cost = 1 WHERE id = $1")
        .bind(item_id)
        .execute(pool)
        .await?;
    Ok(())
}

/// Transfer equipment ownership on purchase.
pub async fn transfer_equipment_ownership(
    pool: &PgPool,
    item_id: i32,
    new_owner: i32,
) -> Result<(), sqlx::Error> {
    sqlx::query("UPDATE equipment SET owner = $1, status = 'U', cost = 1 WHERE id = $2")
        .bind(new_owner)
        .bind(item_id)
        .execute(pool)
        .await?;
    Ok(())
}

/// Reduce equipment listing amount for partial buy (equipment with amount > 1).
pub async fn reduce_equipment_amount(
    pool: &PgPool,
    item_id: i32,
    quantity: i32,
    remaining: i32,
) -> Result<(), sqlx::Error> {
    if remaining <= 0 {
        // Transfer happens separately
        return Ok(());
    }
    sqlx::query("UPDATE equipment SET amount = amount - $1 WHERE id = $2")
        .bind(quantity)
        .bind(item_id)
        .execute(pool)
        .await?;
    Ok(())
}

/// Count player's equipment listings on the market.
pub async fn count_player_equipment_listings(
    pool: &PgPool,
    owner_id: i32,
) -> Result<i64, sqlx::Error> {
    let row: (i64,) = sqlx::query_as(
        "SELECT COUNT(*) FROM equipment WHERE owner = $1 AND status = 'R' AND type NOT IN ('I', 'O', 'Q')",
    )
    .bind(owner_id)
    .fetch_one(pool)
    .await?;
    Ok(row.0)
}

/// Delete all equipment from market for a seller (return to unequipped status).
pub async fn unlist_all_equipment(pool: &PgPool, owner_id: i32) -> Result<u64, sqlx::Error> {
    let result = sqlx::query(
        "UPDATE equipment SET status = 'U', cost = 1 WHERE owner = $1 AND status = 'R' AND type NOT IN ('I', 'O', 'Q')",
    )
    .bind(owner_id)
    .execute(pool)
    .await?;
    Ok(result.rows_affected())
}

// =========================================================================
// Potion market (potions table, status='R')
// =========================================================================

/// Count potion market listings.
pub async fn count_potion_listings(
    pool: &PgPool,
    search: Option<&str>,
) -> Result<i64, sqlx::Error> {
    let row: (i64,) = if let Some(s) = search {
        sqlx::query_as("SELECT COUNT(*) FROM potions WHERE status = 'R' AND name ILIKE $1")
            .bind(s)
            .fetch_one(pool)
            .await?
    } else {
        sqlx::query_as("SELECT COUNT(*) FROM potions WHERE status = 'R'")
            .fetch_one(pool)
            .await?
    };
    Ok(row.0)
}

/// Browse potion market listings.
pub async fn browse_potion_listings(
    pool: &PgPool,
    search: Option<&str>,
    sort_col: &str,
    sort_dir: &str,
    limit: i32,
    offset: i32,
) -> Result<Vec<PotionListingRow>, sqlx::Error> {
    let order_col = match sort_col {
        "name" => "p.name",
        "efect" => "p.efect",
        "power" => "p.power",
        "amount" => "p.amount",
        "cost" => "p.cost",
        "owner" => "pl.username",
        _ => "p.id",
    };
    let order_dir = if sort_dir == "ASC" { "ASC" } else { "DESC" };
    let query = format!(
        "SELECT p.id, p.owner, p.name, p.type, p.efect, p.power, p.amount, p.cost, \
         pl.username AS owner_name \
         FROM potions p \
         LEFT JOIN players pl ON pl.id = p.owner \
         {where_clause} \
         ORDER BY {order_col} {order_dir} \
         LIMIT $1 OFFSET $2",
        where_clause = if search.is_some() {
            "WHERE p.status = 'R' AND p.name ILIKE $3"
        } else {
            "WHERE p.status = 'R'"
        },
    );

    if let Some(s) = search {
        sqlx::query_as(&query)
            .bind(limit)
            .bind(offset)
            .bind(s)
            .fetch_all(pool)
            .await
    } else {
        sqlx::query_as(&query)
            .bind(limit)
            .bind(offset)
            .fetch_all(pool)
            .await
    }
}

/// Find a single potion listing by ID.
pub async fn find_potion_listing(
    pool: &PgPool,
    listing_id: i32,
) -> Result<Option<PotionListingRow>, sqlx::Error> {
    sqlx::query_as(
        "SELECT p.id, p.owner, p.name, p.type, p.efect, p.power, p.amount, p.cost, \
         pl.username AS owner_name \
         FROM potions p \
         LEFT JOIN players pl ON pl.id = p.owner \
         WHERE p.id = $1 AND p.status = 'R'",
    )
    .bind(listing_id)
    .fetch_optional(pool)
    .await
}

/// List a potion on the market (status='S'/'A' → 'R', set cost).
pub async fn list_potion_on_market(
    pool: &PgPool,
    potion_id: i32,
    cost: i64,
    amount: i32,
) -> Result<(), sqlx::Error> {
    sqlx::query("UPDATE potions SET status = 'R', cost = $1, amount = $2 WHERE id = $3")
        .bind(cost)
        .bind(amount)
        .bind(potion_id)
        .execute(pool)
        .await?;
    Ok(())
}

/// Unlist a potion from the market (status='R' → 'A').
pub async fn unlist_potion_from_market(pool: &PgPool, potion_id: i32) -> Result<(), sqlx::Error> {
    sqlx::query("UPDATE potions SET status = 'A', cost = 0 WHERE id = $1")
        .bind(potion_id)
        .execute(pool)
        .await?;
    Ok(())
}

/// Transfer potion ownership.
pub async fn transfer_potion_ownership(
    pool: &PgPool,
    potion_id: i32,
    new_owner: i32,
) -> Result<(), sqlx::Error> {
    sqlx::query("UPDATE potions SET owner = $1, status = 'A', cost = 0 WHERE id = $2")
        .bind(new_owner)
        .bind(potion_id)
        .execute(pool)
        .await?;
    Ok(())
}

/// Reduce potion listing amount.
pub async fn reduce_potion_amount(
    pool: &PgPool,
    potion_id: i32,
    quantity: i32,
    remaining: i32,
) -> Result<(), sqlx::Error> {
    if remaining <= 0 {
        return Ok(());
    }
    sqlx::query("UPDATE potions SET amount = amount - $1 WHERE id = $2")
        .bind(quantity)
        .bind(potion_id)
        .execute(pool)
        .await?;
    Ok(())
}

/// Count player's potion listings.
pub async fn count_player_potion_listings(
    pool: &PgPool,
    owner_id: i32,
) -> Result<i64, sqlx::Error> {
    let row: (i64,) =
        sqlx::query_as("SELECT COUNT(*) FROM potions WHERE owner = $1 AND status = 'R'")
            .bind(owner_id)
            .fetch_one(pool)
            .await?;
    Ok(row.0)
}

/// Unlist all potions for a seller.
pub async fn unlist_all_potions(pool: &PgPool, owner_id: i32) -> Result<u64, sqlx::Error> {
    let result =
        sqlx::query("UPDATE potions SET status = 'A', cost = 0 WHERE owner = $1 AND status = 'R'")
            .bind(owner_id)
            .execute(pool)
            .await?;
    Ok(result.rows_affected())
}

// =========================================================================
// Astral market (amarket)
// =========================================================================

/// Count astral market listings.
pub async fn count_astral_listings(pool: &PgPool) -> Result<i64, sqlx::Error> {
    let row: (i64,) = sqlx::query_as("SELECT COUNT(*) FROM amarket")
        .fetch_one(pool)
        .await?;
    Ok(row.0)
}

/// Browse astral market listings.
pub async fn browse_astral_listings(
    pool: &PgPool,
    sort_col: &str,
    sort_dir: &str,
    limit: i32,
    offset: i32,
) -> Result<Vec<AstralListingRow>, sqlx::Error> {
    let order_col = match sort_col {
        "type" => "a.type",
        "number" => "a.number",
        "amount" => "a.amount",
        "cost" => "a.cost",
        "seller" => "pl.username",
        _ => "a.id",
    };
    let order_dir = if sort_dir == "ASC" { "ASC" } else { "DESC" };
    let query = format!(
        "SELECT a.id, a.seller, a.type, a.number, a.amount, a.cost, \
         pl.username AS seller_name \
         FROM amarket a \
         LEFT JOIN players pl ON pl.id = a.seller \
         ORDER BY {order_col} {order_dir} \
         LIMIT $1 OFFSET $2",
    );

    sqlx::query_as(&query)
        .bind(limit)
        .bind(offset)
        .fetch_all(pool)
        .await
}

/// Find a single astral listing.
pub async fn find_astral_listing(
    pool: &PgPool,
    listing_id: i32,
) -> Result<Option<AstralListingRow>, sqlx::Error> {
    sqlx::query_as(
        "SELECT a.id, a.seller, a.type, a.number, a.amount, a.cost, \
         pl.username AS seller_name \
         FROM amarket a \
         LEFT JOIN players pl ON pl.id = a.seller \
         WHERE a.id = $1",
    )
    .bind(listing_id)
    .fetch_optional(pool)
    .await
}

/// Insert a new astral listing.
pub async fn insert_astral_listing(
    pool: &PgPool,
    seller_id: i32,
    item_type: &str,
    number: i16,
    amount: i32,
    cost: i64,
) -> Result<(), sqlx::Error> {
    sqlx::query(
        "INSERT INTO amarket (seller, type, number, amount, cost) \
         VALUES ($1, $2, $3, $4, $5)",
    )
    .bind(seller_id)
    .bind(item_type)
    .bind(number)
    .bind(amount)
    .bind(cost)
    .execute(pool)
    .await?;
    Ok(())
}

/// Find existing astral listing by seller, type, number (for merging).
pub async fn find_astral_listing_by_seller(
    pool: &PgPool,
    seller_id: i32,
    item_type: &str,
    number: i16,
) -> Result<Option<i32>, sqlx::Error> {
    let row: Option<(i32,)> =
        sqlx::query_as("SELECT id FROM amarket WHERE seller = $1 AND type = $2 AND number = $3")
            .bind(seller_id)
            .bind(item_type)
            .bind(number)
            .fetch_optional(pool)
            .await?;
    Ok(row.map(|r| r.0))
}

/// Top-up an existing astral listing.
pub async fn topup_astral_listing(
    pool: &PgPool,
    listing_id: i32,
    add_quantity: i32,
) -> Result<(), sqlx::Error> {
    sqlx::query("UPDATE amarket SET amount = amount + $1 WHERE id = $2")
        .bind(add_quantity)
        .bind(listing_id)
        .execute(pool)
        .await?;
    Ok(())
}

/// Reduce quantity on an astral listing.
pub async fn reduce_astral_listing(
    pool: &PgPool,
    listing_id: i32,
    buy_quantity: i32,
    remaining: i32,
) -> Result<(), sqlx::Error> {
    if remaining <= 0 {
        sqlx::query("DELETE FROM amarket WHERE id = $1")
            .bind(listing_id)
            .execute(pool)
            .await?;
    } else {
        sqlx::query("UPDATE amarket SET amount = amount - $1 WHERE id = $2")
            .bind(buy_quantity)
            .bind(listing_id)
            .execute(pool)
            .await?;
    }
    Ok(())
}

/// Delete an astral listing.
pub async fn delete_astral_listing(pool: &PgPool, listing_id: i32) -> Result<(), sqlx::Error> {
    sqlx::query("DELETE FROM amarket WHERE id = $1")
        .bind(listing_id)
        .execute(pool)
        .await?;
    Ok(())
}

/// Delete all astral listings for a seller.
pub async fn delete_all_astral_listings(
    pool: &PgPool,
    seller_id: i32,
) -> Result<Vec<(String, i16, i32)>, sqlx::Error> {
    let rows: Vec<(String, i16, i32)> =
        sqlx::query_as("SELECT type, number, amount FROM amarket WHERE seller = $1")
            .bind(seller_id)
            .fetch_all(pool)
            .await?;
    sqlx::query("DELETE FROM amarket WHERE seller = $1")
        .bind(seller_id)
        .execute(pool)
        .await?;
    Ok(rows)
}

/// Change price on an astral listing.
pub async fn change_astral_price(
    pool: &PgPool,
    listing_id: i32,
    new_price: i64,
) -> Result<(), sqlx::Error> {
    sqlx::query("UPDATE amarket SET cost = $1 WHERE id = $2")
        .bind(new_price)
        .bind(listing_id)
        .execute(pool)
        .await?;
    Ok(())
}

/// Count a player's astral listings.
pub async fn count_player_astral_listings(
    pool: &PgPool,
    seller_id: i32,
) -> Result<i64, sqlx::Error> {
    let row: (i64,) = sqlx::query_as("SELECT COUNT(*) FROM amarket WHERE seller = $1")
        .bind(seller_id)
        .fetch_one(pool)
        .await?;
    Ok(row.0)
}

// =========================================================================
// Jewellery market (equipment table, status='R', type='I')
// =========================================================================

/// Count jewellery listings.
pub async fn count_jewellery_listings(
    pool: &PgPool,
    search: Option<&str>,
) -> Result<i64, sqlx::Error> {
    let row: (i64,) = if let Some(s) = search {
        sqlx::query_as(
            "SELECT COUNT(*) FROM equipment WHERE status = 'R' AND type = 'I' AND name ILIKE $1",
        )
        .bind(s)
        .fetch_one(pool)
        .await?
    } else {
        sqlx::query_as("SELECT COUNT(*) FROM equipment WHERE status = 'R' AND type = 'I'")
            .fetch_one(pool)
            .await?
    };
    Ok(row.0)
}

/// Browse jewellery listings.
pub async fn browse_jewellery_listings(
    pool: &PgPool,
    search: Option<&str>,
    sort_col: &str,
    sort_dir: &str,
    limit: i32,
    offset: i32,
) -> Result<Vec<EquipmentListingRow>, sqlx::Error> {
    let order_col = match sort_col {
        "name" => "e.name",
        "power" => "e.power",
        "amount" => "e.amount",
        "cost" => "e.cost",
        "owner" => "pl.username",
        _ => "e.id",
    };
    let order_dir = if sort_dir == "ASC" { "ASC" } else { "DESC" };
    let query = format!(
        "SELECT e.id, e.owner, e.name, e.power, e.type, e.cost, e.minlev, \
         e.zr, e.wt, e.szyb, e.maxwt, e.poison, e.amount, e.twohand, e.ptype, \
         pl.username AS owner_name \
         FROM equipment e \
         LEFT JOIN players pl ON pl.id = e.owner \
         {where_clause} \
         ORDER BY {order_col} {order_dir} \
         LIMIT $1 OFFSET $2",
        where_clause = if search.is_some() {
            "WHERE e.status = 'R' AND e.type = 'I' AND e.name ILIKE $3"
        } else {
            "WHERE e.status = 'R' AND e.type = 'I'"
        },
    );

    if let Some(s) = search {
        sqlx::query_as(&query)
            .bind(limit)
            .bind(offset)
            .bind(s)
            .fetch_all(pool)
            .await
    } else {
        sqlx::query_as(&query)
            .bind(limit)
            .bind(offset)
            .fetch_all(pool)
            .await
    }
}

/// Count player's jewellery listings.
pub async fn count_player_jewellery_listings(
    pool: &PgPool,
    owner_id: i32,
) -> Result<i64, sqlx::Error> {
    let row: (i64,) = sqlx::query_as(
        "SELECT COUNT(*) FROM equipment WHERE owner = $1 AND status = 'R' AND type = 'I'",
    )
    .bind(owner_id)
    .fetch_one(pool)
    .await?;
    Ok(row.0)
}

/// Unlist all jewellery for a seller.
pub async fn unlist_all_jewellery(pool: &PgPool, owner_id: i32) -> Result<u64, sqlx::Error> {
    let result = sqlx::query(
        "UPDATE equipment SET status = 'U', cost = 1 WHERE owner = $1 AND status = 'R' AND type = 'I'",
    )
    .bind(owner_id)
    .execute(pool)
    .await?;
    Ok(result.rows_affected())
}

// =========================================================================
// Loot market (equipment table, status='R', type='O')
// =========================================================================

/// Count loot listings.
pub async fn count_loot_listings(pool: &PgPool, search: Option<&str>) -> Result<i64, sqlx::Error> {
    let row: (i64,) = if let Some(s) = search {
        sqlx::query_as(
            "SELECT COUNT(*) FROM equipment WHERE status = 'R' AND type = 'O' AND name ILIKE $1",
        )
        .bind(s)
        .fetch_one(pool)
        .await?
    } else {
        sqlx::query_as("SELECT COUNT(*) FROM equipment WHERE status = 'R' AND type = 'O'")
            .fetch_one(pool)
            .await?
    };
    Ok(row.0)
}

/// Browse loot listings.
pub async fn browse_loot_listings(
    pool: &PgPool,
    search: Option<&str>,
    sort_col: &str,
    sort_dir: &str,
    limit: i32,
    offset: i32,
) -> Result<Vec<EquipmentListingRow>, sqlx::Error> {
    let order_col = match sort_col {
        "name" => "e.name",
        "cost" => "e.cost",
        "amount" => "e.amount",
        "minlev" => "e.minlev",
        "owner" => "pl.username",
        _ => "e.id",
    };
    let order_dir = if sort_dir == "ASC" { "ASC" } else { "DESC" };
    let query = format!(
        "SELECT e.id, e.owner, e.name, e.power, e.type, e.cost, e.minlev, \
         e.zr, e.wt, e.szyb, e.maxwt, e.poison, e.amount, e.twohand, e.ptype, \
         pl.username AS owner_name \
         FROM equipment e \
         LEFT JOIN players pl ON pl.id = e.owner \
         {where_clause} \
         ORDER BY {order_col} {order_dir} \
         LIMIT $1 OFFSET $2",
        where_clause = if search.is_some() {
            "WHERE e.status = 'R' AND e.type = 'O' AND e.name ILIKE $3"
        } else {
            "WHERE e.status = 'R' AND e.type = 'O'"
        },
    );

    if let Some(s) = search {
        sqlx::query_as(&query)
            .bind(limit)
            .bind(offset)
            .bind(s)
            .fetch_all(pool)
            .await
    } else {
        sqlx::query_as(&query)
            .bind(limit)
            .bind(offset)
            .fetch_all(pool)
            .await
    }
}

/// Count player's loot listings.
pub async fn count_player_loot_listings(pool: &PgPool, owner_id: i32) -> Result<i64, sqlx::Error> {
    let row: (i64,) = sqlx::query_as(
        "SELECT COUNT(*) FROM equipment WHERE owner = $1 AND status = 'R' AND type = 'O'",
    )
    .bind(owner_id)
    .fetch_one(pool)
    .await?;
    Ok(row.0)
}

/// Unlist all loot for a seller.
pub async fn unlist_all_loot(pool: &PgPool, owner_id: i32) -> Result<u64, sqlx::Error> {
    let result = sqlx::query(
        "UPDATE equipment SET status = 'U', cost = 1 WHERE owner = $1 AND status = 'R' AND type = 'O'",
    )
    .bind(owner_id)
    .execute(pool)
    .await?;
    Ok(result.rows_affected())
}

// =========================================================================
// Pet market (core_market)
// =========================================================================

/// Count pet market listings.
pub async fn count_pet_listings(pool: &PgPool) -> Result<i64, sqlx::Error> {
    let row: (i64,) = sqlx::query_as("SELECT COUNT(*) FROM core_market")
        .fetch_one(pool)
        .await?;
    Ok(row.0)
}

/// Browse pet market listings.
pub async fn browse_pet_listings(
    pool: &PgPool,
    sort_col: &str,
    sort_dir: &str,
    limit: i32,
    offset: i32,
) -> Result<Vec<PetListingRow>, sqlx::Error> {
    let order_col = match sort_col {
        "name" => "c.name",
        "cost" => "c.cost",
        "gender" => "c.gender",
        "power" => "c.power",
        "defense" => "c.defense",
        "seller" => "pl.username",
        _ => "c.id",
    };
    let order_dir = if sort_dir == "ASC" { "ASC" } else { "DESC" };
    let query = format!(
        "SELECT c.id, c.name, c.cost, c.seller, c.type, c.power, c.defense, \
         c.gender, c.ref_id, c.wins, c.losses, \
         pl.username AS seller_name \
         FROM core_market c \
         LEFT JOIN players pl ON pl.id = c.seller \
         ORDER BY {order_col} {order_dir} \
         LIMIT $1 OFFSET $2",
    );

    sqlx::query_as(&query)
        .bind(limit)
        .bind(offset)
        .fetch_all(pool)
        .await
}

/// Find a single pet listing.
pub async fn find_pet_listing(
    pool: &PgPool,
    listing_id: i32,
) -> Result<Option<PetListingRow>, sqlx::Error> {
    sqlx::query_as(
        "SELECT c.id, c.name, c.cost, c.seller, c.type, c.power, c.defense, \
         c.gender, c.ref_id, c.wins, c.losses, \
         pl.username AS seller_name \
         FROM core_market c \
         LEFT JOIN players pl ON pl.id = c.seller \
         WHERE c.id = $1",
    )
    .bind(listing_id)
    .fetch_optional(pool)
    .await
}

/// Delete a pet listing.
pub async fn delete_pet_listing(pool: &PgPool, listing_id: i32) -> Result<(), sqlx::Error> {
    sqlx::query("DELETE FROM core_market WHERE id = $1")
        .bind(listing_id)
        .execute(pool)
        .await?;
    Ok(())
}

/// Delete all pet listings for a seller, returning the `ref_ids` for re-linking.
pub async fn delete_all_pet_listings(
    pool: &PgPool,
    seller_id: i32,
) -> Result<Vec<i32>, sqlx::Error> {
    let rows: Vec<(i32,)> = sqlx::query_as("SELECT ref_id FROM core_market WHERE seller = $1")
        .bind(seller_id)
        .fetch_all(pool)
        .await?;
    sqlx::query("DELETE FROM core_market WHERE seller = $1")
        .bind(seller_id)
        .execute(pool)
        .await?;
    Ok(rows.into_iter().map(|r| r.0).collect())
}

/// Count a player's pet listings.
pub async fn count_player_pet_listings(pool: &PgPool, seller_id: i32) -> Result<i64, sqlx::Error> {
    let row: (i64,) = sqlx::query_as("SELECT COUNT(*) FROM core_market WHERE seller = $1")
        .bind(seller_id)
        .fetch_one(pool)
        .await?;
    Ok(row.0)
}

// =========================================================================
// Cross-market: offer counts for "my offers" summary
// =========================================================================

/// Get offer counts across all market categories for a player.
pub async fn player_offer_counts(
    pool: &PgPool,
    player_id: i32,
) -> Result<Vec<(String, i64)>, sqlx::Error> {
    // Run a UNION ALL query for efficiency.
    let rows: Vec<(String, i64)> = sqlx::query_as(
        "SELECT 'pmarket' AS cat, COUNT(*) FROM pmarket WHERE seller = $1 \
         UNION ALL \
         SELECT 'imarket', COUNT(*) FROM equipment WHERE owner = $1 AND status = 'R' AND type NOT IN ('I', 'O', 'Q') \
         UNION ALL \
         SELECT 'mmarket', COUNT(*) FROM potions WHERE owner = $1 AND status = 'R' \
         UNION ALL \
         SELECT 'hmarket', COUNT(*) FROM hmarket WHERE seller = $1 \
         UNION ALL \
         SELECT 'amarket', COUNT(*) FROM amarket WHERE seller = $1 \
         UNION ALL \
         SELECT 'rmarket', COUNT(*) FROM equipment WHERE owner = $1 AND status = 'R' AND type = 'I' \
         UNION ALL \
         SELECT 'lmarket', COUNT(*) FROM equipment WHERE owner = $1 AND status = 'R' AND type = 'O' \
         UNION ALL \
         SELECT 'cmarket', COUNT(*) FROM core_market WHERE seller = $1",
    )
    .bind(player_id)
    .fetch_all(pool)
    .await?;
    Ok(rows)
}

// =========================================================================
// Market transaction helpers (money transfers)
// =========================================================================

/// Credit a seller's bank account after a market sale.
pub async fn credit_seller_bank(
    pool: &PgPool,
    seller_id: i32,
    amount: i64,
) -> Result<(), sqlx::Error> {
    sqlx::query("UPDATE players SET bank = bank + $1 WHERE id = $2")
        .bind(amount)
        .bind(seller_id)
        .execute(pool)
        .await?;
    Ok(())
}

/// Debit a buyer's credits after a market purchase.
pub async fn debit_buyer_credits(
    pool: &PgPool,
    buyer_id: i32,
    amount: i64,
) -> Result<(), sqlx::Error> {
    sqlx::query("UPDATE players SET credits = credits - $1 WHERE id = $2")
        .bind(amount)
        .bind(buyer_id)
        .execute(pool)
        .await?;
    Ok(())
}

/// Insert a game log entry (for sale notifications to sellers).
pub async fn insert_market_log(
    pool: &PgPool,
    owner_id: i32,
    message: &str,
) -> Result<(), sqlx::Error> {
    sqlx::query(
        "INSERT INTO game_log (owner, log, czas, type) \
         VALUES ($1, $2, EXTRACT(EPOCH FROM NOW())::BIGINT, 'M')",
    )
    .bind(owner_id)
    .bind(message)
    .execute(pool)
    .await?;
    Ok(())
}
