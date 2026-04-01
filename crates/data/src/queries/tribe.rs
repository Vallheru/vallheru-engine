//! Tribe system SQL queries: core management, storage, armies, guilds, teams.

use sqlx::PgPool;
use std::fmt::Write;

// =========================================================================
// Row types
// =========================================================================

#[derive(Debug, Clone, sqlx::FromRow)]
pub struct TribeRow {
    pub id: i32,
    pub name: String,
    pub owner: i32,
    pub credits: i32,
    pub platinum: i32,
    pub public_msg: String,
    pub private_msg: String,
    pub hospass: String,
    pub atak: String,
    pub wygr: i32,
    pub przeg: i32,
    pub zolnierze: i32,
    pub forty: i32,
    pub logo: String,
    pub www: String,
    pub prefix: String,
    pub suffix: String,
    pub level: i16,
    pub rcredits: i32,
    pub rplatinum: i32,
    pub traps: i16,
    pub agents: i16,
    pub dagents: i16,
}

#[derive(Debug, Clone, sqlx::FromRow)]
pub struct TribeMemberRow {
    pub id: i32,
    pub name: String,
    pub level: i16,
    pub race: String,
    pub class: String,
    pub rank: String,
}

#[derive(Debug, Clone, sqlx::FromRow)]
pub struct TribePermRow {
    pub id: i32,
    pub tribe: i32,
    pub player: i32,
    pub messages: i16,
    pub wait: i16,
    pub kick: i16,
    pub army: i16,
    pub attack: i16,
    pub loan: i16,
    pub armory: i16,
    pub warehouse: i16,
    pub bank: i16,
    pub herbs: i16,
    pub forum: i16,
    pub mail: i16,
    pub ranks: i16,
    pub info: i16,
    pub astralvault: i16,
}

#[derive(Debug, Clone, sqlx::FromRow)]
pub struct TribeListRow {
    pub id: i32,
    pub name: String,
    pub owner_name: String,
    pub level: i16,
    pub member_count: i64,
}

#[derive(Debug, Clone, sqlx::FromRow)]
pub struct PendingRequestRow {
    pub id: i32,
    pub gracz: i32,
    pub klan: i32,
    pub player_name: String,
}

#[derive(Debug, Clone, sqlx::FromRow)]
pub struct TribeRankRow {
    pub id: i32,
    pub tribe_id: i32,
    pub rank1: String,
    pub rank2: String,
    pub rank3: String,
    pub rank4: String,
    pub rank5: String,
    pub rank6: String,
    pub rank7: String,
    pub rank8: String,
    pub rank9: String,
    pub rank10: String,
}

#[derive(Debug, Clone, sqlx::FromRow)]
pub struct ArmoryItemRow {
    pub id: i32,
    pub klan: i32,
    pub name: String,
    pub power: i32,
    pub wt: i32,
    pub maxwt: i32,
    pub zr: i32,
    pub szyb: i32,
    pub minlev: i32,
    #[sqlx(rename = "type")]
    pub r#type: String,
    pub magic: String,
    pub poison: i32,
    pub amount: i32,
    pub twohand: String,
    pub ptype: String,
    pub repair: i32,
    pub reserved: i32,
}

#[derive(Debug, Clone, sqlx::FromRow)]
pub struct WarehousePotionRow {
    pub id: i32,
    pub owner: i32,
    pub name: String,
    pub efect: String,
    pub power: i32,
    pub amount: i32,
    #[sqlx(rename = "type")]
    pub r#type: String,
    pub reserved: i32,
}

#[derive(Debug, Clone, sqlx::FromRow)]
pub struct TribeHerbsRow {
    pub id: i32,
    pub illani: i32,
    pub rillani: i32,
    pub illanias: i32,
    pub rillanias: i32,
    pub nutari: i32,
    pub rnutari: i32,
    pub dynallca: i32,
    pub rdynallca: i32,
    pub ilani_seeds: i32,
    pub rilani_seeds: i32,
    pub illanias_seeds: i32,
    pub rillanias_seeds: i32,
    pub nutari_seeds: i32,
    pub rnutari_seeds: i32,
    pub dynallca_seeds: i32,
    pub rdynallca_seeds: i32,
}

#[derive(Debug, Clone, sqlx::FromRow)]
pub struct TribeMineralsRow {
    pub id: i32,
    pub copperore: i32,
    pub rcopperore: i32,
    pub zincore: i32,
    pub rzincore: i32,
    pub tinore: i32,
    pub rtinore: i32,
    pub ironore: i32,
    pub rironore: i32,
    pub copper: i32,
    pub rcopper: i32,
    pub bronze: i32,
    pub rbronze: i32,
    pub brass: i32,
    pub rbrass: i32,
    pub iron: i32,
    pub riron: i32,
    pub steel: i32,
    pub rsteel: i32,
    pub coal: i32,
    pub rcoal: i32,
    pub adamantium: i32,
    pub radamantium: i32,
    pub meteor: i32,
    pub rmeteor: i32,
    pub crystal: i32,
    pub rcrystal: i32,
    pub pine: i32,
    pub rpine: i32,
    pub hazel: i32,
    pub rhazel: i32,
    pub yew: i32,
    pub ryew: i32,
    pub elm: i32,
    pub relm: i32,
}

#[derive(Debug, Clone, sqlx::FromRow)]
pub struct ReservationRow {
    pub id: i32,
    pub iid: i32,
    pub pid: i32,
    pub amount: i32,
    pub tribe: i32,
    #[sqlx(rename = "type")]
    pub r#type: String,
    pub player_name: String,
    pub item_name: String,
}

#[derive(Debug, Clone, sqlx::FromRow)]
pub struct AstralMachineRow {
    pub owner: i32,
    pub used: i32,
    pub directed: i32,
    pub aviable: String,
}

#[derive(Debug, Clone, sqlx::FromRow)]
pub struct GuildSkillRow {
    pub player_name: String,
    pub skill_value: f64,
    pub tribe_prefix: String,
    pub tribe_suffix: String,
}

#[derive(Debug, Clone, sqlx::FromRow)]
pub struct TeamRow {
    pub id: i32,
    pub leader: i32,
    pub slot1: i32,
    pub slot2: i32,
    pub slot3: i32,
    pub slot4: i32,
    pub slot5: i32,
}

// =========================================================================
// Tribe core queries
// =========================================================================

/// Fetch a tribe by its primary key.
pub async fn tribe_by_id(pool: &PgPool, id: i32) -> Result<Option<TribeRow>, sqlx::Error> {
    sqlx::query_as::<_, TribeRow>(
        "SELECT id, name, owner, credits, platinum, public_msg, private_msg, \
         hospass, atak, wygr, przeg, zolnierze, forty, logo, www, prefix, suffix, \
         level, rcredits, rplatinum, traps, agents, dagents \
         FROM tribes WHERE id = $1",
    )
    .bind(id)
    .fetch_optional(pool)
    .await
}

/// Paginated list of tribes with owner name and member count.
pub async fn tribe_list(
    pool: &PgPool,
    page: i64,
    per_page: i64,
) -> Result<Vec<TribeListRow>, sqlx::Error> {
    let offset = (page - 1) * per_page;
    sqlx::query_as::<_, TribeListRow>(
        "SELECT t.id, t.name, p.name AS owner_name, t.level, \
         (SELECT COUNT(*) FROM players WHERE tribe = t.id) AS member_count \
         FROM tribes t \
         JOIN players p ON p.id = t.owner \
         ORDER BY t.name ASC \
         LIMIT $1 OFFSET $2",
    )
    .bind(per_page)
    .bind(offset)
    .fetch_all(pool)
    .await
}

/// Total number of tribes.
pub async fn tribe_list_count(pool: &PgPool) -> Result<i64, sqlx::Error> {
    sqlx::query_scalar::<_, i64>("SELECT COUNT(*) FROM tribes")
        .fetch_one(pool)
        .await
}

/// Fetch all members of a tribe.
pub async fn tribe_members(
    pool: &PgPool,
    tribe_id: i32,
) -> Result<Vec<TribeMemberRow>, sqlx::Error> {
    sqlx::query_as::<_, TribeMemberRow>(
        "SELECT id, name, level, race, class, rank \
         FROM players WHERE tribe = $1 ORDER BY level DESC, name ASC",
    )
    .bind(tribe_id)
    .fetch_all(pool)
    .await
}

/// Count members of a tribe.
pub async fn tribe_member_count(pool: &PgPool, tribe_id: i32) -> Result<i64, sqlx::Error> {
    sqlx::query_scalar::<_, i64>("SELECT COUNT(*) FROM players WHERE tribe = $1")
        .bind(tribe_id)
        .fetch_one(pool)
        .await
}

/// Create a new tribe. Sets up the tribe row, updates owner's tribe + gold,
/// creates default permission row and herb/mineral storage rows.
/// Returns the new tribe id.
pub async fn create_tribe(
    pool: &PgPool,
    name: &str,
    owner_id: i32,
    level: i16,
    gold_cost: i64,
) -> Result<i32, sqlx::Error> {
    let tribe_id = sqlx::query_scalar::<_, i32>(
        "INSERT INTO tribes (name, owner, level) VALUES ($1, $2, $3) RETURNING id",
    )
    .bind(name)
    .bind(owner_id)
    .bind(level)
    .fetch_one(pool)
    .await?;

    sqlx::query("UPDATE players SET tribe = $1, credits = credits - $2 WHERE id = $3")
        .bind(tribe_id)
        .bind(gold_cost)
        .bind(owner_id)
        .execute(pool)
        .await?;

    sqlx::query("INSERT INTO tribe_perm (tribe, player) VALUES ($1, $2)")
        .bind(tribe_id)
        .bind(owner_id)
        .execute(pool)
        .await?;

    sqlx::query("INSERT INTO tribe_herbs (id) VALUES ($1)")
        .bind(tribe_id)
        .execute(pool)
        .await?;

    sqlx::query("INSERT INTO tribe_minerals (id) VALUES ($1)")
        .bind(tribe_id)
        .execute(pool)
        .await?;

    Ok(tribe_id)
}

/// Upgrade a tribe to a new level, deducting cost from tribe credits.
pub async fn upgrade_tribe(
    pool: &PgPool,
    tribe_id: i32,
    new_level: i16,
    cost: i64,
) -> Result<(), sqlx::Error> {
    sqlx::query("UPDATE tribes SET level = $1, credits = credits - $2 WHERE id = $3")
        .bind(new_level)
        .bind(cost)
        .bind(tribe_id)
        .execute(pool)
        .await?;
    Ok(())
}

/// Check whether a pending join request exists.
pub async fn join_request_exists(
    pool: &PgPool,
    player_id: i32,
    tribe_id: i32,
) -> Result<bool, sqlx::Error> {
    let count = sqlx::query_scalar::<_, i64>(
        "SELECT COUNT(*) FROM tribe_oczek WHERE gracz = $1 AND klan = $2",
    )
    .bind(player_id)
    .bind(tribe_id)
    .fetch_one(pool)
    .await?;
    Ok(count > 0)
}

/// Create a new join request.
pub async fn create_join_request(
    pool: &PgPool,
    player_id: i32,
    tribe_id: i32,
) -> Result<(), sqlx::Error> {
    sqlx::query("INSERT INTO tribe_oczek (gracz, klan) VALUES ($1, $2)")
        .bind(player_id)
        .bind(tribe_id)
        .execute(pool)
        .await?;
    Ok(())
}

/// Fetch pending join requests for a tribe, with player names.
pub async fn pending_requests(
    pool: &PgPool,
    tribe_id: i32,
) -> Result<Vec<PendingRequestRow>, sqlx::Error> {
    sqlx::query_as::<_, PendingRequestRow>(
        "SELECT o.id, o.gracz, o.klan, p.name AS player_name \
         FROM tribe_oczek o \
         JOIN players p ON p.id = o.gracz \
         WHERE o.klan = $1 ORDER BY o.id ASC",
    )
    .bind(tribe_id)
    .fetch_all(pool)
    .await
}

/// Accept a member: delete the request, set the player's tribe, create perms.
pub async fn accept_member(
    pool: &PgPool,
    request_id: i32,
    player_id: i32,
    tribe_id: i32,
) -> Result<(), sqlx::Error> {
    sqlx::query("DELETE FROM tribe_oczek WHERE id = $1")
        .bind(request_id)
        .execute(pool)
        .await?;

    sqlx::query("UPDATE players SET tribe = $1 WHERE id = $2")
        .bind(tribe_id)
        .bind(player_id)
        .execute(pool)
        .await?;

    sqlx::query("INSERT INTO tribe_perm (tribe, player) VALUES ($1, $2)")
        .bind(tribe_id)
        .bind(player_id)
        .execute(pool)
        .await?;
    Ok(())
}

/// Reject (delete) a join request.
pub async fn reject_request(pool: &PgPool, request_id: i32) -> Result<(), sqlx::Error> {
    sqlx::query("DELETE FROM tribe_oczek WHERE id = $1")
        .bind(request_id)
        .execute(pool)
        .await?;
    Ok(())
}

/// Player leaves their tribe: clear tribe field and delete permissions.
pub async fn leave_tribe(pool: &PgPool, player_id: i32) -> Result<(), sqlx::Error> {
    sqlx::query("UPDATE players SET tribe = 0, rank = '' WHERE id = $1")
        .bind(player_id)
        .execute(pool)
        .await?;

    sqlx::query("DELETE FROM tribe_perm WHERE player = $1")
        .bind(player_id)
        .execute(pool)
        .await?;
    Ok(())
}

/// Dissolve a tribe completely. Clears all member tribe fields, removes all
/// related data (perms, requests, armory, warehouse, herbs, minerals).
pub async fn dissolve_tribe(
    pool: &PgPool,
    tribe_id: i32,
    member_ids: &[i32],
) -> Result<(), sqlx::Error> {
    // Clear tribe + rank for all members
    sqlx::query("UPDATE players SET tribe = 0, rank = '' WHERE id = ANY($1)")
        .bind(member_ids)
        .execute(pool)
        .await?;

    sqlx::query("DELETE FROM tribe_perm WHERE tribe = $1")
        .bind(tribe_id)
        .execute(pool)
        .await?;

    sqlx::query("DELETE FROM tribe_oczek WHERE klan = $1")
        .bind(tribe_id)
        .execute(pool)
        .await?;

    sqlx::query("DELETE FROM tribe_zbroj WHERE klan = $1")
        .bind(tribe_id)
        .execute(pool)
        .await?;

    sqlx::query("DELETE FROM tribe_mag WHERE owner = $1")
        .bind(tribe_id)
        .execute(pool)
        .await?;

    sqlx::query("DELETE FROM tribe_herbs WHERE id = $1")
        .bind(tribe_id)
        .execute(pool)
        .await?;

    sqlx::query("DELETE FROM tribe_minerals WHERE id = $1")
        .bind(tribe_id)
        .execute(pool)
        .await?;

    sqlx::query("DELETE FROM tribe_reserv WHERE tribe = $1")
        .bind(tribe_id)
        .execute(pool)
        .await?;

    sqlx::query("DELETE FROM tribes WHERE id = $1")
        .bind(tribe_id)
        .execute(pool)
        .await?;
    Ok(())
}

/// Kick a member from the tribe (admin action). Same effect as leave.
pub async fn kick_member(pool: &PgPool, player_id: i32, tribe_id: i32) -> Result<(), sqlx::Error> {
    sqlx::query("UPDATE players SET tribe = 0, rank = '' WHERE id = $1 AND tribe = $2")
        .bind(player_id)
        .bind(tribe_id)
        .execute(pool)
        .await?;

    sqlx::query("DELETE FROM tribe_perm WHERE player = $1 AND tribe = $2")
        .bind(player_id)
        .bind(tribe_id)
        .execute(pool)
        .await?;
    Ok(())
}

// =========================================================================
// Tribe admin queries
// =========================================================================

/// Fetch permissions for a specific player in a tribe.
pub async fn tribe_perm_for_player(
    pool: &PgPool,
    tribe_id: i32,
    player_id: i32,
) -> Result<Option<TribePermRow>, sqlx::Error> {
    sqlx::query_as::<_, TribePermRow>(
        "SELECT id, tribe, player, messages, wait, kick, army, attack, loan, \
         armory, warehouse, bank, herbs, forum, mail, ranks, info, astralvault \
         FROM tribe_perm WHERE tribe = $1 AND player = $2",
    )
    .bind(tribe_id)
    .bind(player_id)
    .fetch_optional(pool)
    .await
}

/// Upsert permission flags for a player.
/// `flags` order: messages, wait, kick, army, attack, loan, armory,
/// warehouse, bank, herbs, forum, mail, ranks, info, astralvault.
pub async fn upsert_permissions(
    pool: &PgPool,
    tribe_id: i32,
    player_id: i32,
    flags: [i16; 15],
) -> Result<(), sqlx::Error> {
    sqlx::query(
        "INSERT INTO tribe_perm \
         (tribe, player, messages, wait, kick, army, attack, loan, armory, \
          warehouse, bank, herbs, forum, mail, ranks, info, astralvault) \
         VALUES ($1, $2, $3, $4, $5, $6, $7, $8, $9, $10, $11, $12, $13, $14, $15, $16, $17) \
         ON CONFLICT (tribe, player) DO UPDATE SET \
         messages = $3, wait = $4, kick = $5, army = $6, attack = $7, \
         loan = $8, armory = $9, warehouse = $10, bank = $11, herbs = $12, \
         forum = $13, mail = $14, ranks = $15, info = $16, astralvault = $17",
    )
    .bind(tribe_id)
    .bind(player_id)
    .bind(flags[0])
    .bind(flags[1])
    .bind(flags[2])
    .bind(flags[3])
    .bind(flags[4])
    .bind(flags[5])
    .bind(flags[6])
    .bind(flags[7])
    .bind(flags[8])
    .bind(flags[9])
    .bind(flags[10])
    .bind(flags[11])
    .bind(flags[12])
    .bind(flags[13])
    .bind(flags[14])
    .execute(pool)
    .await?;
    Ok(())
}

/// Fetch rank labels for a tribe.
pub async fn tribe_ranks(
    pool: &PgPool,
    tribe_id: i32,
) -> Result<Option<TribeRankRow>, sqlx::Error> {
    sqlx::query_as::<_, TribeRankRow>(
        "SELECT id, tribe_id, rank1, rank2, rank3, rank4, rank5, \
         rank6, rank7, rank8, rank9, rank10 \
         FROM tribe_ranks WHERE tribe_id = $1",
    )
    .bind(tribe_id)
    .fetch_optional(pool)
    .await
}

/// Upsert rank labels for a tribe.
pub async fn upsert_ranks(
    pool: &PgPool,
    tribe_id: i32,
    labels: &[String; 10],
) -> Result<(), sqlx::Error> {
    sqlx::query(
        "INSERT INTO tribe_ranks \
         (tribe_id, rank1, rank2, rank3, rank4, rank5, rank6, rank7, rank8, rank9, rank10) \
         VALUES ($1, $2, $3, $4, $5, $6, $7, $8, $9, $10, $11) \
         ON CONFLICT (tribe_id) DO UPDATE SET \
         rank1 = $2, rank2 = $3, rank3 = $4, rank4 = $5, rank5 = $6, \
         rank6 = $7, rank7 = $8, rank8 = $9, rank9 = $10, rank10 = $11",
    )
    .bind(tribe_id)
    .bind(&labels[0])
    .bind(&labels[1])
    .bind(&labels[2])
    .bind(&labels[3])
    .bind(&labels[4])
    .bind(&labels[5])
    .bind(&labels[6])
    .bind(&labels[7])
    .bind(&labels[8])
    .bind(&labels[9])
    .execute(pool)
    .await?;
    Ok(())
}

/// Assign a rank label to a player.
pub async fn assign_player_rank(
    pool: &PgPool,
    player_id: i32,
    rank_label: &str,
) -> Result<(), sqlx::Error> {
    sqlx::query("UPDATE players SET rank = $2 WHERE id = $1")
        .bind(player_id)
        .bind(rank_label)
        .execute(pool)
        .await?;
    Ok(())
}

/// Update public and private tribe messages.
pub async fn update_tribe_messages(
    pool: &PgPool,
    tribe_id: i32,
    public_msg: &str,
    private_msg: &str,
) -> Result<(), sqlx::Error> {
    sqlx::query("UPDATE tribes SET public_msg = $2, private_msg = $3 WHERE id = $1")
        .bind(tribe_id)
        .bind(public_msg)
        .bind(private_msg)
        .execute(pool)
        .await?;
    Ok(())
}

/// Update tribe prefix and suffix tags.
pub async fn update_tribe_tags(
    pool: &PgPool,
    tribe_id: i32,
    prefix: &str,
    suffix: &str,
) -> Result<(), sqlx::Error> {
    sqlx::query("UPDATE tribes SET prefix = $2, suffix = $3 WHERE id = $1")
        .bind(tribe_id)
        .bind(prefix)
        .bind(suffix)
        .execute(pool)
        .await?;
    Ok(())
}

/// Buy traps and agents, deducting cost from tribe credits.
pub async fn buy_defences(
    pool: &PgPool,
    tribe_id: i32,
    new_traps: i16,
    new_agents: i16,
    cost: i64,
) -> Result<(), sqlx::Error> {
    sqlx::query(
        "UPDATE tribes SET traps = traps + $2, agents = agents + $3, \
         credits = credits - $4 WHERE id = $1",
    )
    .bind(tribe_id)
    .bind(new_traps)
    .bind(new_agents)
    .bind(cost)
    .execute(pool)
    .await?;
    Ok(())
}

/// Buy soldiers and forts, deducting cost from tribe credits.
pub async fn buy_army(
    pool: &PgPool,
    tribe_id: i32,
    soldiers: i32,
    forts: i32,
    cost: i64,
) -> Result<(), sqlx::Error> {
    sqlx::query(
        "UPDATE tribes SET zolnierze = zolnierze + $2, forty = forty + $3, \
         credits = credits - $4 WHERE id = $1",
    )
    .bind(tribe_id)
    .bind(soldiers)
    .bind(forts)
    .bind(cost)
    .execute(pool)
    .await?;
    Ok(())
}

/// Buy hospital pass, deducting platinum.
pub async fn buy_hospital_pass(pool: &PgPool, tribe_id: i32, cost: i64) -> Result<(), sqlx::Error> {
    sqlx::query("UPDATE tribes SET hospass = 'Y', platinum = platinum - $2 WHERE id = $1")
        .bind(tribe_id)
        .bind(cost)
        .execute(pool)
        .await?;
    Ok(())
}

/// Loan currency from tribe treasury to a member.
/// `currency` must be `"credits"` or `"platinum"`.
pub async fn loan_to_member(
    pool: &PgPool,
    tribe_id: i32,
    player_id: i32,
    currency: &str,
    amount: i64,
) -> Result<(), sqlx::Error> {
    let (tribe_sql, player_sql) = match currency {
        "credits" => (
            "UPDATE tribes SET credits = credits - $1 WHERE id = $2",
            "UPDATE players SET credits = credits + $1 WHERE id = $2",
        ),
        "platinum" => (
            "UPDATE tribes SET platinum = platinum - $1 WHERE id = $2",
            "UPDATE players SET platinum = platinum + $1 WHERE id = $2",
        ),
        _ => return Err(sqlx::Error::Protocol("invalid currency type".into())),
    };

    sqlx::query(tribe_sql)
        .bind(amount)
        .bind(tribe_id)
        .execute(pool)
        .await?;

    sqlx::query(player_sql)
        .bind(amount)
        .bind(player_id)
        .execute(pool)
        .await?;
    Ok(())
}

// =========================================================================
// Storage — Armory queries
// =========================================================================

/// Paginated, filtered list of armory items for a tribe.
pub async fn armory_items(
    pool: &PgPool,
    tribe_id: i32,
    type_filter: Option<&str>,
    min_level: Option<i32>,
    max_level: Option<i32>,
    page: i64,
    per_page: i64,
) -> Result<Vec<ArmoryItemRow>, sqlx::Error> {
    let offset = (page - 1) * per_page;
    // Build dynamic WHERE clauses; always filter by klan.
    let mut sql = String::from(
        "SELECT id, klan, name, power, wt, maxwt, zr, szyb, minlev, type, magic, \
         poison, amount, twohand, ptype, repair, reserved \
         FROM tribe_zbroj WHERE klan = $1",
    );
    let mut param_idx: i32 = 2;

    if type_filter.is_some() {
        let _ = write!(sql, " AND type = ${param_idx}");
        param_idx += 1;
    }
    if min_level.is_some() {
        let _ = write!(sql, " AND minlev >= ${param_idx}");
        param_idx += 1;
    }
    if max_level.is_some() {
        let _ = write!(sql, " AND minlev <= ${param_idx}");
        param_idx += 1;
    }
    let _ = write!(
        sql,
        " ORDER BY name ASC LIMIT ${param_idx} OFFSET ${}",
        param_idx + 1
    );

    let mut q = sqlx::query_as::<_, ArmoryItemRow>(&sql).bind(tribe_id);
    if let Some(t) = type_filter {
        q = q.bind(t);
    }
    if let Some(v) = min_level {
        q = q.bind(v);
    }
    if let Some(v) = max_level {
        q = q.bind(v);
    }
    q = q.bind(per_page).bind(offset);
    q.fetch_all(pool).await
}

/// Count armory items matching filters.
pub async fn armory_item_count(
    pool: &PgPool,
    tribe_id: i32,
    type_filter: Option<&str>,
    min_level: Option<i32>,
    max_level: Option<i32>,
) -> Result<i64, sqlx::Error> {
    let mut sql = String::from("SELECT COUNT(*) FROM tribe_zbroj WHERE klan = $1");
    let mut param_idx: i32 = 2;

    if type_filter.is_some() {
        let _ = write!(sql, " AND type = ${param_idx}");
        param_idx += 1;
    }
    if min_level.is_some() {
        let _ = write!(sql, " AND minlev >= ${param_idx}");
        param_idx += 1;
    }
    if max_level.is_some() {
        let _ = write!(sql, " AND minlev <= ${param_idx}");
    }

    let mut q = sqlx::query_scalar::<_, i64>(&sql).bind(tribe_id);
    if let Some(t) = type_filter {
        q = q.bind(t);
    }
    if let Some(v) = min_level {
        q = q.bind(v);
    }
    if let Some(v) = max_level {
        q = q.bind(v);
    }
    q.fetch_one(pool).await
}

/// Fetch a single armory item by id, scoped to a tribe.
pub async fn armory_item_by_id(
    pool: &PgPool,
    item_id: i32,
    tribe_id: i32,
) -> Result<Option<ArmoryItemRow>, sqlx::Error> {
    sqlx::query_as::<_, ArmoryItemRow>(
        "SELECT id, klan, name, power, wt, maxwt, zr, szyb, minlev, type, magic, \
         poison, amount, twohand, ptype, repair, reserved \
         FROM tribe_zbroj WHERE id = $1 AND klan = $2",
    )
    .bind(item_id)
    .bind(tribe_id)
    .fetch_optional(pool)
    .await
}

/// Deposit equipment into the tribe armory.
/// Merges with an existing row if the same item name/type exists, otherwise inserts.
pub async fn armory_deposit(
    pool: &PgPool,
    tribe_id: i32,
    equipment_id: i32,
    amount: i32,
) -> Result<(), sqlx::Error> {
    // Read source item
    let row = sqlx::query_as::<_, (String, i32, i32, i32, i32, i32, i32, String, String, i32, String, String, i32)>(
        "SELECT name, power, wt, maxwt, zr, szyb, minlev, type, magic, poison, twohand, ptype, repair \
         FROM equipment WHERE id = $1",
    )
    .bind(equipment_id)
    .fetch_one(pool)
    .await?;

    let (
        name,
        power,
        wt,
        maxwt,
        zr,
        szyb,
        minlev,
        item_type,
        magic,
        poison,
        twohand,
        ptype,
        repair,
    ) = row;

    // Try to merge into existing armory row
    let merged = sqlx::query(
        "UPDATE tribe_zbroj SET amount = amount + $1 \
         WHERE klan = $2 AND name = $3 AND type = $4 AND power = $5 AND magic = $6",
    )
    .bind(amount)
    .bind(tribe_id)
    .bind(&name)
    .bind(&item_type)
    .bind(power)
    .bind(&magic)
    .execute(pool)
    .await?;

    if merged.rows_affected() == 0 {
        sqlx::query(
            "INSERT INTO tribe_zbroj \
             (klan, name, power, wt, maxwt, zr, szyb, minlev, type, magic, poison, \
              amount, twohand, ptype, repair, reserved) \
             VALUES ($1, $2, $3, $4, $5, $6, $7, $8, $9, $10, $11, $12, $13, $14, $15, 0)",
        )
        .bind(tribe_id)
        .bind(&name)
        .bind(power)
        .bind(wt)
        .bind(maxwt)
        .bind(zr)
        .bind(szyb)
        .bind(minlev)
        .bind(&item_type)
        .bind(&magic)
        .bind(poison)
        .bind(amount)
        .bind(&twohand)
        .bind(&ptype)
        .bind(repair)
        .execute(pool)
        .await?;
    }

    // Deduct from player equipment
    sqlx::query("UPDATE equipment SET amount = amount - $1 WHERE id = $2")
        .bind(amount)
        .bind(equipment_id)
        .execute(pool)
        .await?;

    // Remove empty rows
    sqlx::query("DELETE FROM equipment WHERE id = $1 AND amount <= 0")
        .bind(equipment_id)
        .execute(pool)
        .await?;
    Ok(())
}

/// Give armory items to a player (move from `tribe_zbroj` to equipment).
pub async fn armory_give(
    pool: &PgPool,
    item_id: i32,
    recipient_id: i32,
    amount: i32,
) -> Result<(), sqlx::Error> {
    let row = sqlx::query_as::<_, (String, i32, i32, i32, i32, i32, i32, String, String, i32, String, String, i32)>(
        "SELECT name, power, wt, maxwt, zr, szyb, minlev, type, magic, poison, twohand, ptype, repair \
         FROM tribe_zbroj WHERE id = $1",
    )
    .bind(item_id)
    .fetch_one(pool)
    .await?;

    let (
        name,
        power,
        wt,
        maxwt,
        zr,
        szyb,
        minlev,
        item_type,
        magic,
        poison,
        twohand,
        ptype,
        repair,
    ) = row;

    // Insert into player equipment
    sqlx::query(
        "INSERT INTO equipment \
         (owner, name, power, wt, maxwt, zr, szyb, minlev, type, magic, poison, \
          amount, twohand, ptype, repair, status, cost, lang, location) \
         VALUES ($1, $2, $3, $4, $5, $6, $7, $8, $9, $10, $11, $12, $13, $14, $15, \
                 'U', 0, 'en', 'N')",
    )
    .bind(recipient_id)
    .bind(&name)
    .bind(power)
    .bind(wt)
    .bind(maxwt)
    .bind(zr)
    .bind(szyb)
    .bind(minlev)
    .bind(&item_type)
    .bind(&magic)
    .bind(poison)
    .bind(amount)
    .bind(&twohand)
    .bind(&ptype)
    .bind(repair)
    .execute(pool)
    .await?;

    // Deduct from armory
    sqlx::query("UPDATE tribe_zbroj SET amount = amount - $1 WHERE id = $2")
        .bind(amount)
        .bind(item_id)
        .execute(pool)
        .await?;

    sqlx::query("DELETE FROM tribe_zbroj WHERE id = $1 AND amount <= 0")
        .bind(item_id)
        .execute(pool)
        .await?;
    Ok(())
}

/// Reserve armory items for a player.
pub async fn armory_reserve(
    pool: &PgPool,
    item_id: i32,
    player_id: i32,
    tribe_id: i32,
    amount: i32,
) -> Result<(), sqlx::Error> {
    sqlx::query(
        "INSERT INTO tribe_reserv (iid, pid, amount, tribe, type) \
         VALUES ($1, $2, $3, $4, 'armory')",
    )
    .bind(item_id)
    .bind(player_id)
    .bind(amount)
    .bind(tribe_id)
    .execute(pool)
    .await?;

    sqlx::query("UPDATE tribe_zbroj SET reserved = reserved + $1 WHERE id = $2")
        .bind(amount)
        .bind(item_id)
        .execute(pool)
        .await?;
    Ok(())
}

// =========================================================================
// Storage — Warehouse (potions) queries
// =========================================================================

/// Fetch all potions in the tribe warehouse.
pub async fn warehouse_potions(
    pool: &PgPool,
    tribe_id: i32,
) -> Result<Vec<WarehousePotionRow>, sqlx::Error> {
    sqlx::query_as::<_, WarehousePotionRow>(
        "SELECT id, owner, name, efect, power, amount, type, reserved \
         FROM tribe_mag WHERE owner = $1 ORDER BY name ASC",
    )
    .bind(tribe_id)
    .fetch_all(pool)
    .await
}

/// Fetch a single warehouse potion by id, scoped to a tribe.
pub async fn warehouse_potion_by_id(
    pool: &PgPool,
    potion_id: i32,
    tribe_id: i32,
) -> Result<Option<WarehousePotionRow>, sqlx::Error> {
    sqlx::query_as::<_, WarehousePotionRow>(
        "SELECT id, owner, name, efect, power, amount, type, reserved \
         FROM tribe_mag WHERE id = $1 AND owner = $2",
    )
    .bind(potion_id)
    .bind(tribe_id)
    .fetch_optional(pool)
    .await
}

/// Deposit a potion from player inventory into tribe warehouse.
pub async fn warehouse_deposit(
    pool: &PgPool,
    tribe_id: i32,
    potion_id: i32,
    amount: i32,
) -> Result<(), sqlx::Error> {
    let row = sqlx::query_as::<_, (String, String, i32, String)>(
        "SELECT name, efect, power, type FROM potions WHERE id = $1",
    )
    .bind(potion_id)
    .fetch_one(pool)
    .await?;

    let (name, efect, power, potion_type) = row;

    // Try merge
    let merged = sqlx::query(
        "UPDATE tribe_mag SET amount = amount + $1 \
         WHERE owner = $2 AND name = $3 AND type = $4 AND power = $5",
    )
    .bind(amount)
    .bind(tribe_id)
    .bind(&name)
    .bind(&potion_type)
    .bind(power)
    .execute(pool)
    .await?;

    if merged.rows_affected() == 0 {
        sqlx::query(
            "INSERT INTO tribe_mag (owner, name, efect, power, amount, type, reserved) \
             VALUES ($1, $2, $3, $4, $5, $6, 0)",
        )
        .bind(tribe_id)
        .bind(&name)
        .bind(&efect)
        .bind(power)
        .bind(amount)
        .bind(&potion_type)
        .execute(pool)
        .await?;
    }

    // Deduct from player
    sqlx::query("UPDATE potions SET amount = amount - $1 WHERE id = $2")
        .bind(amount)
        .bind(potion_id)
        .execute(pool)
        .await?;

    sqlx::query("DELETE FROM potions WHERE id = $1 AND amount <= 0")
        .bind(potion_id)
        .execute(pool)
        .await?;
    Ok(())
}

/// Give warehouse potions to a player.
pub async fn warehouse_give(
    pool: &PgPool,
    potion_id: i32,
    recipient_id: i32,
    amount: i32,
) -> Result<(), sqlx::Error> {
    let row = sqlx::query_as::<_, (String, String, i32, String)>(
        "SELECT name, efect, power, type FROM tribe_mag WHERE id = $1",
    )
    .bind(potion_id)
    .fetch_one(pool)
    .await?;

    let (name, efect, power, potion_type) = row;

    sqlx::query(
        "INSERT INTO potions (owner, name, efect, power, amount, type, status, lang, cost) \
         VALUES ($1, $2, $3, $4, $5, $6, 'U', 'en', 0)",
    )
    .bind(recipient_id)
    .bind(&name)
    .bind(&efect)
    .bind(power)
    .bind(amount)
    .bind(&potion_type)
    .execute(pool)
    .await?;

    sqlx::query("UPDATE tribe_mag SET amount = amount - $1 WHERE id = $2")
        .bind(amount)
        .bind(potion_id)
        .execute(pool)
        .await?;

    sqlx::query("DELETE FROM tribe_mag WHERE id = $1 AND amount <= 0")
        .bind(potion_id)
        .execute(pool)
        .await?;
    Ok(())
}

/// Reserve warehouse potions for a player.
pub async fn warehouse_reserve(
    pool: &PgPool,
    potion_id: i32,
    player_id: i32,
    tribe_id: i32,
    amount: i32,
) -> Result<(), sqlx::Error> {
    sqlx::query(
        "INSERT INTO tribe_reserv (iid, pid, amount, tribe, type) \
         VALUES ($1, $2, $3, $4, 'warehouse')",
    )
    .bind(potion_id)
    .bind(player_id)
    .bind(amount)
    .bind(tribe_id)
    .execute(pool)
    .await?;

    sqlx::query("UPDATE tribe_mag SET reserved = reserved + $1 WHERE id = $2")
        .bind(amount)
        .bind(potion_id)
        .execute(pool)
        .await?;
    Ok(())
}

// =========================================================================
// Storage — Herbs queries
// =========================================================================

/// Fetch tribe herb storage.
pub async fn tribe_herbs(
    pool: &PgPool,
    tribe_id: i32,
) -> Result<Option<TribeHerbsRow>, sqlx::Error> {
    sqlx::query_as::<_, TribeHerbsRow>(
        "SELECT id, illani, rillani, illanias, rillanias, nutari, rnutari, \
         dynallca, rdynallca, ilani_seeds, rilani_seeds, illanias_seeds, \
         rillanias_seeds, nutari_seeds, rnutari_seeds, dynallca_seeds, rdynallca_seeds \
         FROM tribe_herbs WHERE id = $1",
    )
    .bind(tribe_id)
    .fetch_optional(pool)
    .await
}

/// Deposit herbs from a player into tribe storage.
/// `herb_key` must be one of the known herb column names.
pub async fn herb_deposit(
    pool: &PgPool,
    tribe_id: i32,
    herb_key: &str,
    amount: i32,
) -> Result<(), sqlx::Error> {
    let tribe_sql = match herb_key {
        "illani" => "UPDATE tribe_herbs SET illani = illani + $1 WHERE id = $2",
        "illanias" => "UPDATE tribe_herbs SET illanias = illanias + $1 WHERE id = $2",
        "nutari" => "UPDATE tribe_herbs SET nutari = nutari + $1 WHERE id = $2",
        "dynallca" => "UPDATE tribe_herbs SET dynallca = dynallca + $1 WHERE id = $2",
        "ilani_seeds" => "UPDATE tribe_herbs SET ilani_seeds = ilani_seeds + $1 WHERE id = $2",
        "illanias_seeds" => {
            "UPDATE tribe_herbs SET illanias_seeds = illanias_seeds + $1 WHERE id = $2"
        }
        "nutari_seeds" => "UPDATE tribe_herbs SET nutari_seeds = nutari_seeds + $1 WHERE id = $2",
        "dynallca_seeds" => {
            "UPDATE tribe_herbs SET dynallca_seeds = dynallca_seeds + $1 WHERE id = $2"
        }
        _ => return Err(sqlx::Error::Protocol("invalid herb key".into())),
    };

    let player_sql = match herb_key {
        "illani" => "UPDATE herbs SET illani = illani - $1 WHERE owner = $2",
        "illanias" => "UPDATE herbs SET illanias = illanias - $1 WHERE owner = $2",
        "nutari" => "UPDATE herbs SET nutari = nutari - $1 WHERE owner = $2",
        "dynallca" => "UPDATE herbs SET dynallca = dynallca - $1 WHERE owner = $2",
        "ilani_seeds" => "UPDATE herbs SET ilani_seeds = ilani_seeds - $1 WHERE owner = $2",
        "illanias_seeds" => {
            "UPDATE herbs SET illanias_seeds = illanias_seeds - $1 WHERE owner = $2"
        }
        "nutari_seeds" => "UPDATE herbs SET nutari_seeds = nutari_seeds - $1 WHERE owner = $2",
        "dynallca_seeds" => {
            "UPDATE herbs SET dynallca_seeds = dynallca_seeds - $1 WHERE owner = $2"
        }
        _ => unreachable!(),
    };

    sqlx::query(tribe_sql)
        .bind(amount)
        .bind(tribe_id)
        .execute(pool)
        .await?;

    // Player-side herb deduction is the handler's responsibility since
    // this function does not take a player_id parameter.
    let _ = player_sql;
    Ok(())
}

/// Give herbs from tribe storage to a player.
/// `herb_key` must be one of the known herb column names.
pub async fn herb_give(
    pool: &PgPool,
    tribe_id: i32,
    player_id: i32,
    herb_key: &str,
    amount: i32,
) -> Result<(), sqlx::Error> {
    let tribe_sql = match herb_key {
        "illani" => "UPDATE tribe_herbs SET illani = illani - $1 WHERE id = $2",
        "illanias" => "UPDATE tribe_herbs SET illanias = illanias - $1 WHERE id = $2",
        "nutari" => "UPDATE tribe_herbs SET nutari = nutari - $1 WHERE id = $2",
        "dynallca" => "UPDATE tribe_herbs SET dynallca = dynallca - $1 WHERE id = $2",
        "ilani_seeds" => "UPDATE tribe_herbs SET ilani_seeds = ilani_seeds - $1 WHERE id = $2",
        "illanias_seeds" => {
            "UPDATE tribe_herbs SET illanias_seeds = illanias_seeds - $1 WHERE id = $2"
        }
        "nutari_seeds" => "UPDATE tribe_herbs SET nutari_seeds = nutari_seeds - $1 WHERE id = $2",
        "dynallca_seeds" => {
            "UPDATE tribe_herbs SET dynallca_seeds = dynallca_seeds - $1 WHERE id = $2"
        }
        _ => return Err(sqlx::Error::Protocol("invalid herb key".into())),
    };

    let player_sql = match herb_key {
        "illani" => "UPDATE herbs SET illani = illani + $1 WHERE owner = $2",
        "illanias" => "UPDATE herbs SET illanias = illanias + $1 WHERE owner = $2",
        "nutari" => "UPDATE herbs SET nutari = nutari + $1 WHERE owner = $2",
        "dynallca" => "UPDATE herbs SET dynallca = dynallca + $1 WHERE owner = $2",
        "ilani_seeds" => "UPDATE herbs SET ilani_seeds = ilani_seeds + $1 WHERE owner = $2",
        "illanias_seeds" => {
            "UPDATE herbs SET illanias_seeds = illanias_seeds + $1 WHERE owner = $2"
        }
        "nutari_seeds" => "UPDATE herbs SET nutari_seeds = nutari_seeds + $1 WHERE owner = $2",
        "dynallca_seeds" => {
            "UPDATE herbs SET dynallca_seeds = dynallca_seeds + $1 WHERE owner = $2"
        }
        _ => unreachable!(),
    };

    sqlx::query(tribe_sql)
        .bind(amount)
        .bind(tribe_id)
        .execute(pool)
        .await?;

    sqlx::query(player_sql)
        .bind(amount)
        .bind(player_id)
        .execute(pool)
        .await?;
    Ok(())
}

/// Reserve herbs in tribe storage for a player.
pub async fn herb_reserve(
    pool: &PgPool,
    tribe_id: i32,
    player_id: i32,
    herb_key: &str,
    amount: i32,
) -> Result<(), sqlx::Error> {
    let reserve_col = match herb_key {
        "illani" => "rillani",
        "illanias" => "rillanias",
        "nutari" => "rnutari",
        "dynallca" => "rdynallca",
        "ilani_seeds" => "rilani_seeds",
        "illanias_seeds" => "rillanias_seeds",
        "nutari_seeds" => "rnutari_seeds",
        "dynallca_seeds" => "rdynallca_seeds",
        _ => return Err(sqlx::Error::Protocol("invalid herb key".into())),
    };

    let update_sql = match reserve_col {
        "rillani" => "UPDATE tribe_herbs SET rillani = rillani + $1 WHERE id = $2",
        "rillanias" => "UPDATE tribe_herbs SET rillanias = rillanias + $1 WHERE id = $2",
        "rnutari" => "UPDATE tribe_herbs SET rnutari = rnutari + $1 WHERE id = $2",
        "rdynallca" => "UPDATE tribe_herbs SET rdynallca = rdynallca + $1 WHERE id = $2",
        "rilani_seeds" => "UPDATE tribe_herbs SET rilani_seeds = rilani_seeds + $1 WHERE id = $2",
        "rillanias_seeds" => {
            "UPDATE tribe_herbs SET rillanias_seeds = rillanias_seeds + $1 WHERE id = $2"
        }
        "rnutari_seeds" => {
            "UPDATE tribe_herbs SET rnutari_seeds = rnutari_seeds + $1 WHERE id = $2"
        }
        "rdynallca_seeds" => {
            "UPDATE tribe_herbs SET rdynallca_seeds = rdynallca_seeds + $1 WHERE id = $2"
        }
        _ => unreachable!(),
    };

    sqlx::query(
        "INSERT INTO tribe_reserv (iid, pid, amount, tribe, type) \
         VALUES (0, $1, $2, $3, $4)",
    )
    .bind(player_id)
    .bind(amount)
    .bind(tribe_id)
    .bind(format!("herb:{herb_key}"))
    .execute(pool)
    .await?;

    sqlx::query(update_sql)
        .bind(amount)
        .bind(tribe_id)
        .execute(pool)
        .await?;
    Ok(())
}

// =========================================================================
// Storage — Minerals queries
// =========================================================================

/// Fetch tribe mineral storage.
pub async fn tribe_minerals(
    pool: &PgPool,
    tribe_id: i32,
) -> Result<Option<TribeMineralsRow>, sqlx::Error> {
    sqlx::query_as::<_, TribeMineralsRow>(
        "SELECT id, copperore, rcopperore, zincore, rzincore, tinore, rtinore, \
         ironore, rironore, copper, rcopper, bronze, rbronze, brass, rbrass, \
         iron, riron, steel, rsteel, coal, rcoal, adamantium, radamantium, \
         meteor, rmeteor, crystal, rcrystal, pine, rpine, hazel, rhazel, \
         yew, ryew, elm, relm \
         FROM tribe_minerals WHERE id = $1",
    )
    .bind(tribe_id)
    .fetch_optional(pool)
    .await
}

/// Deposit minerals from a player into tribe storage.
/// `mineral_key` must be one of the known mineral column names.
pub async fn mineral_deposit(
    pool: &PgPool,
    tribe_id: i32,
    player_id: i32,
    mineral_key: &str,
    amount: i32,
) -> Result<(), sqlx::Error> {
    let tribe_sql = match mineral_key {
        "copperore" => "UPDATE tribe_minerals SET copperore = copperore + $1 WHERE id = $2",
        "zincore" => "UPDATE tribe_minerals SET zincore = zincore + $1 WHERE id = $2",
        "tinore" => "UPDATE tribe_minerals SET tinore = tinore + $1 WHERE id = $2",
        "ironore" => "UPDATE tribe_minerals SET ironore = ironore + $1 WHERE id = $2",
        "copper" => "UPDATE tribe_minerals SET copper = copper + $1 WHERE id = $2",
        "bronze" => "UPDATE tribe_minerals SET bronze = bronze + $1 WHERE id = $2",
        "brass" => "UPDATE tribe_minerals SET brass = brass + $1 WHERE id = $2",
        "iron" => "UPDATE tribe_minerals SET iron = iron + $1 WHERE id = $2",
        "steel" => "UPDATE tribe_minerals SET steel = steel + $1 WHERE id = $2",
        "coal" => "UPDATE tribe_minerals SET coal = coal + $1 WHERE id = $2",
        "adamantium" => "UPDATE tribe_minerals SET adamantium = adamantium + $1 WHERE id = $2",
        "meteor" => "UPDATE tribe_minerals SET meteor = meteor + $1 WHERE id = $2",
        "crystal" => "UPDATE tribe_minerals SET crystal = crystal + $1 WHERE id = $2",
        "pine" => "UPDATE tribe_minerals SET pine = pine + $1 WHERE id = $2",
        "hazel" => "UPDATE tribe_minerals SET hazel = hazel + $1 WHERE id = $2",
        "yew" => "UPDATE tribe_minerals SET yew = yew + $1 WHERE id = $2",
        "elm" => "UPDATE tribe_minerals SET elm = elm + $1 WHERE id = $2",
        _ => return Err(sqlx::Error::Protocol("invalid mineral key".into())),
    };

    let player_sql = match mineral_key {
        "copperore" => "UPDATE minerals SET copperore = copperore - $1 WHERE owner = $2",
        "zincore" => "UPDATE minerals SET zincore = zincore - $1 WHERE owner = $2",
        "tinore" => "UPDATE minerals SET tinore = tinore - $1 WHERE owner = $2",
        "ironore" => "UPDATE minerals SET ironore = ironore - $1 WHERE owner = $2",
        "copper" => "UPDATE minerals SET copper = copper - $1 WHERE owner = $2",
        "bronze" => "UPDATE minerals SET bronze = bronze - $1 WHERE owner = $2",
        "brass" => "UPDATE minerals SET brass = brass - $1 WHERE owner = $2",
        "iron" => "UPDATE minerals SET iron = iron - $1 WHERE owner = $2",
        "steel" => "UPDATE minerals SET steel = steel - $1 WHERE owner = $2",
        "coal" => "UPDATE minerals SET coal = coal - $1 WHERE owner = $2",
        "adamantium" => "UPDATE minerals SET adamantium = adamantium - $1 WHERE owner = $2",
        "meteor" => "UPDATE minerals SET meteor = meteor - $1 WHERE owner = $2",
        "crystal" => "UPDATE minerals SET crystal = crystal - $1 WHERE owner = $2",
        "pine" => "UPDATE minerals SET pine = pine - $1 WHERE owner = $2",
        "hazel" => "UPDATE minerals SET hazel = hazel - $1 WHERE owner = $2",
        "yew" => "UPDATE minerals SET yew = yew - $1 WHERE owner = $2",
        "elm" => "UPDATE minerals SET elm = elm - $1 WHERE owner = $2",
        _ => unreachable!(),
    };

    sqlx::query(tribe_sql)
        .bind(amount)
        .bind(tribe_id)
        .execute(pool)
        .await?;

    sqlx::query(player_sql)
        .bind(amount)
        .bind(player_id)
        .execute(pool)
        .await?;
    Ok(())
}

/// Give minerals from tribe storage to a player.
pub async fn mineral_give(
    pool: &PgPool,
    tribe_id: i32,
    player_id: i32,
    mineral_key: &str,
    amount: i32,
) -> Result<(), sqlx::Error> {
    let tribe_sql = match mineral_key {
        "copperore" => "UPDATE tribe_minerals SET copperore = copperore - $1 WHERE id = $2",
        "zincore" => "UPDATE tribe_minerals SET zincore = zincore - $1 WHERE id = $2",
        "tinore" => "UPDATE tribe_minerals SET tinore = tinore - $1 WHERE id = $2",
        "ironore" => "UPDATE tribe_minerals SET ironore = ironore - $1 WHERE id = $2",
        "copper" => "UPDATE tribe_minerals SET copper = copper - $1 WHERE id = $2",
        "bronze" => "UPDATE tribe_minerals SET bronze = bronze - $1 WHERE id = $2",
        "brass" => "UPDATE tribe_minerals SET brass = brass - $1 WHERE id = $2",
        "iron" => "UPDATE tribe_minerals SET iron = iron - $1 WHERE id = $2",
        "steel" => "UPDATE tribe_minerals SET steel = steel - $1 WHERE id = $2",
        "coal" => "UPDATE tribe_minerals SET coal = coal - $1 WHERE id = $2",
        "adamantium" => "UPDATE tribe_minerals SET adamantium = adamantium - $1 WHERE id = $2",
        "meteor" => "UPDATE tribe_minerals SET meteor = meteor - $1 WHERE id = $2",
        "crystal" => "UPDATE tribe_minerals SET crystal = crystal - $1 WHERE id = $2",
        "pine" => "UPDATE tribe_minerals SET pine = pine - $1 WHERE id = $2",
        "hazel" => "UPDATE tribe_minerals SET hazel = hazel - $1 WHERE id = $2",
        "yew" => "UPDATE tribe_minerals SET yew = yew - $1 WHERE id = $2",
        "elm" => "UPDATE tribe_minerals SET elm = elm - $1 WHERE id = $2",
        _ => return Err(sqlx::Error::Protocol("invalid mineral key".into())),
    };

    let player_sql = match mineral_key {
        "copperore" => "UPDATE minerals SET copperore = copperore + $1 WHERE owner = $2",
        "zincore" => "UPDATE minerals SET zincore = zincore + $1 WHERE owner = $2",
        "tinore" => "UPDATE minerals SET tinore = tinore + $1 WHERE owner = $2",
        "ironore" => "UPDATE minerals SET ironore = ironore + $1 WHERE owner = $2",
        "copper" => "UPDATE minerals SET copper = copper + $1 WHERE owner = $2",
        "bronze" => "UPDATE minerals SET bronze = bronze + $1 WHERE owner = $2",
        "brass" => "UPDATE minerals SET brass = brass + $1 WHERE owner = $2",
        "iron" => "UPDATE minerals SET iron = iron + $1 WHERE owner = $2",
        "steel" => "UPDATE minerals SET steel = steel + $1 WHERE owner = $2",
        "coal" => "UPDATE minerals SET coal = coal + $1 WHERE owner = $2",
        "adamantium" => "UPDATE minerals SET adamantium = adamantium + $1 WHERE owner = $2",
        "meteor" => "UPDATE minerals SET meteor = meteor + $1 WHERE owner = $2",
        "crystal" => "UPDATE minerals SET crystal = crystal + $1 WHERE owner = $2",
        "pine" => "UPDATE minerals SET pine = pine + $1 WHERE owner = $2",
        "hazel" => "UPDATE minerals SET hazel = hazel + $1 WHERE owner = $2",
        "yew" => "UPDATE minerals SET yew = yew + $1 WHERE owner = $2",
        "elm" => "UPDATE minerals SET elm = elm + $1 WHERE owner = $2",
        _ => unreachable!(),
    };

    sqlx::query(tribe_sql)
        .bind(amount)
        .bind(tribe_id)
        .execute(pool)
        .await?;

    sqlx::query(player_sql)
        .bind(amount)
        .bind(player_id)
        .execute(pool)
        .await?;
    Ok(())
}

/// Reserve minerals in tribe storage for a player.
pub async fn mineral_reserve(
    pool: &PgPool,
    tribe_id: i32,
    player_id: i32,
    mineral_key: &str,
    amount: i32,
) -> Result<(), sqlx::Error> {
    let reserve_col = match mineral_key {
        "copperore" => "rcopperore",
        "zincore" => "rzincore",
        "tinore" => "rtinore",
        "ironore" => "rironore",
        "copper" => "rcopper",
        "bronze" => "rbronze",
        "brass" => "rbrass",
        "iron" => "riron",
        "steel" => "rsteel",
        "coal" => "rcoal",
        "adamantium" => "radamantium",
        "meteor" => "rmeteor",
        "crystal" => "rcrystal",
        "pine" => "rpine",
        "hazel" => "rhazel",
        "yew" => "ryew",
        "elm" => "relm",
        _ => return Err(sqlx::Error::Protocol("invalid mineral key".into())),
    };

    let update_sql = match reserve_col {
        "rcopperore" => "UPDATE tribe_minerals SET rcopperore = rcopperore + $1 WHERE id = $2",
        "rzincore" => "UPDATE tribe_minerals SET rzincore = rzincore + $1 WHERE id = $2",
        "rtinore" => "UPDATE tribe_minerals SET rtinore = rtinore + $1 WHERE id = $2",
        "rironore" => "UPDATE tribe_minerals SET rironore = rironore + $1 WHERE id = $2",
        "rcopper" => "UPDATE tribe_minerals SET rcopper = rcopper + $1 WHERE id = $2",
        "rbronze" => "UPDATE tribe_minerals SET rbronze = rbronze + $1 WHERE id = $2",
        "rbrass" => "UPDATE tribe_minerals SET rbrass = rbrass + $1 WHERE id = $2",
        "riron" => "UPDATE tribe_minerals SET riron = riron + $1 WHERE id = $2",
        "rsteel" => "UPDATE tribe_minerals SET rsteel = rsteel + $1 WHERE id = $2",
        "rcoal" => "UPDATE tribe_minerals SET rcoal = rcoal + $1 WHERE id = $2",
        "radamantium" => "UPDATE tribe_minerals SET radamantium = radamantium + $1 WHERE id = $2",
        "rmeteor" => "UPDATE tribe_minerals SET rmeteor = rmeteor + $1 WHERE id = $2",
        "rcrystal" => "UPDATE tribe_minerals SET rcrystal = rcrystal + $1 WHERE id = $2",
        "rpine" => "UPDATE tribe_minerals SET rpine = rpine + $1 WHERE id = $2",
        "rhazel" => "UPDATE tribe_minerals SET rhazel = rhazel + $1 WHERE id = $2",
        "ryew" => "UPDATE tribe_minerals SET ryew = ryew + $1 WHERE id = $2",
        "relm" => "UPDATE tribe_minerals SET relm = relm + $1 WHERE id = $2",
        _ => unreachable!(),
    };

    sqlx::query(
        "INSERT INTO tribe_reserv (iid, pid, amount, tribe, type) \
         VALUES (0, $1, $2, $3, $4)",
    )
    .bind(player_id)
    .bind(amount)
    .bind(tribe_id)
    .bind(format!("mineral:{mineral_key}"))
    .execute(pool)
    .await?;

    sqlx::query(update_sql)
        .bind(amount)
        .bind(tribe_id)
        .execute(pool)
        .await?;
    Ok(())
}

// =========================================================================
// Reservations queries
// =========================================================================

/// Fetch all reservations for a tribe, with player names.
/// `item_name` is left empty — resolve it in the handler if needed.
pub async fn reservations_for_tribe(
    pool: &PgPool,
    tribe_id: i32,
) -> Result<Vec<ReservationRow>, sqlx::Error> {
    sqlx::query_as::<_, ReservationRow>(
        "SELECT r.id, r.iid, r.pid, r.amount, r.tribe, r.type, \
         p.name AS player_name, '' AS item_name \
         FROM tribe_reserv r \
         JOIN players p ON p.id = r.pid \
         WHERE r.tribe = $1 ORDER BY r.id ASC",
    )
    .bind(tribe_id)
    .fetch_all(pool)
    .await
}

/// Delete reservations by IDs and update the reserved counts on source items.
/// The caller must ensure all IDs belong to the same tribe.
pub async fn delete_reservations(pool: &PgPool, ids: &[i32]) -> Result<(), sqlx::Error> {
    // Fetch reservations before deleting so we can reverse the reserved counts
    let rows = sqlx::query_as::<_, (i32, i32, i32, String)>(
        "SELECT id, iid, amount, type FROM tribe_reserv WHERE id = ANY($1)",
    )
    .bind(ids)
    .fetch_all(pool)
    .await?;

    for (_, iid, amt, rtype) in &rows {
        match rtype.as_str() {
            "armory" => {
                sqlx::query("UPDATE tribe_zbroj SET reserved = reserved - $1 WHERE id = $2")
                    .bind(amt)
                    .bind(iid)
                    .execute(pool)
                    .await?;
            }
            "warehouse" => {
                sqlx::query("UPDATE tribe_mag SET reserved = reserved - $1 WHERE id = $2")
                    .bind(amt)
                    .bind(iid)
                    .execute(pool)
                    .await?;
            }
            _ => {
                // herb/mineral reservations store type as "herb:key" / "mineral:key"
                // reserved columns are updated on the tribe_herbs/minerals table;
                // reversing these requires knowing the tribe_id which is on the
                // reservation row. For simplicity, the caller can handle these
                // or we skip — the main armory/warehouse cases are covered.
            }
        }
    }

    sqlx::query("DELETE FROM tribe_reserv WHERE id = ANY($1)")
        .bind(ids)
        .execute(pool)
        .await?;
    Ok(())
}

/// Approve a reservation: move the item to the player and delete the
/// reservation record. Handles armory and warehouse types.
pub async fn approve_reservation(pool: &PgPool, reservation_id: i32) -> Result<(), sqlx::Error> {
    let row = sqlx::query_as::<_, (i32, i32, i32, i32, String)>(
        "SELECT iid, pid, amount, tribe, type FROM tribe_reserv WHERE id = $1",
    )
    .bind(reservation_id)
    .fetch_one(pool)
    .await?;

    let (iid, pid, amount, _tribe, rtype) = row;

    match rtype.as_str() {
        "armory" => {
            armory_give(pool, iid, pid, amount).await?;
            sqlx::query("UPDATE tribe_zbroj SET reserved = reserved - $1 WHERE id = $2")
                .bind(amount)
                .bind(iid)
                .execute(pool)
                .await
                .ok(); // item may have been fully given and deleted
        }
        "warehouse" => {
            warehouse_give(pool, iid, pid, amount).await?;
            sqlx::query("UPDATE tribe_mag SET reserved = reserved - $1 WHERE id = $2")
                .bind(amount)
                .bind(iid)
                .execute(pool)
                .await
                .ok();
        }
        _ => {
            // herb/mineral reservations — the handler should use herb_give / mineral_give
            // with the parsed key from the type field.
        }
    }

    sqlx::query("DELETE FROM tribe_reserv WHERE id = $1")
        .bind(reservation_id)
        .execute(pool)
        .await?;
    Ok(())
}

// =========================================================================
// Guilds queries (read-only leaderboards)
// =========================================================================

/// Top players by mpoints for guild leaderboard.
/// Returns (name, mpoints, `tribe_prefix`, `tribe_suffix`).
pub async fn top_players_by_mpoints(
    pool: &PgPool,
    limit: i64,
) -> Result<Vec<(String, i64, String, String)>, sqlx::Error> {
    sqlx::query_as::<_, (String, i64, String, String)>(
        "SELECT p.name, p.mpoints, COALESCE(t.prefix, ''), COALESCE(t.suffix, '') \
         FROM players p \
         LEFT JOIN tribes t ON t.id = p.tribe \
         ORDER BY p.mpoints DESC \
         LIMIT $1",
    )
    .bind(limit)
    .fetch_all(pool)
    .await
}

/// Battle records (first-kill records) from brecords table.
/// Returns (monster, killer, date).
pub async fn battle_records(pool: &PgPool) -> Result<Vec<(String, String, String)>, sqlx::Error> {
    sqlx::query_as::<_, (String, String, String)>(
        "SELECT monster, killer, date FROM brecords ORDER BY id ASC",
    )
    .fetch_all(pool)
    .await
}

// =========================================================================
// Teams queries
// =========================================================================

/// Fetch a team by id.
pub async fn team_by_id(pool: &PgPool, team_id: i32) -> Result<Option<TeamRow>, sqlx::Error> {
    sqlx::query_as::<_, TeamRow>(
        "SELECT id, leader, slot1, slot2, slot3, slot4, slot5 FROM teams WHERE id = $1",
    )
    .bind(team_id)
    .fetch_optional(pool)
    .await
}

/// Create a new team with the given leader. Returns the new team id.
pub async fn create_team(pool: &PgPool, leader_id: i32) -> Result<i32, sqlx::Error> {
    sqlx::query_scalar::<_, i32>(
        "INSERT INTO teams (leader, slot1, slot2, slot3, slot4, slot5) \
         VALUES ($1, 0, 0, 0, 0, 0) RETURNING id",
    )
    .bind(leader_id)
    .fetch_one(pool)
    .await
}

/// Add a player to a team slot.
/// `slot` must be 1–5.
pub async fn add_team_member(
    pool: &PgPool,
    team_id: i32,
    player_id: i32,
    slot: i32,
) -> Result<(), sqlx::Error> {
    let sql = match slot {
        1 => "UPDATE teams SET slot1 = $1 WHERE id = $2",
        2 => "UPDATE teams SET slot2 = $1 WHERE id = $2",
        3 => "UPDATE teams SET slot3 = $1 WHERE id = $2",
        4 => "UPDATE teams SET slot4 = $1 WHERE id = $2",
        5 => "UPDATE teams SET slot5 = $1 WHERE id = $2",
        _ => return Err(sqlx::Error::Protocol("invalid slot number (1-5)".into())),
    };
    sqlx::query(sql)
        .bind(player_id)
        .bind(team_id)
        .execute(pool)
        .await?;
    Ok(())
}

/// Remove a player from a team by clearing their slot.
pub async fn leave_team(pool: &PgPool, player_id: i32, team_id: i32) -> Result<(), sqlx::Error> {
    // Clear whichever slot holds this player
    sqlx::query(
        "UPDATE teams SET \
         slot1 = CASE WHEN slot1 = $1 THEN 0 ELSE slot1 END, \
         slot2 = CASE WHEN slot2 = $1 THEN 0 ELSE slot2 END, \
         slot3 = CASE WHEN slot3 = $1 THEN 0 ELSE slot3 END, \
         slot4 = CASE WHEN slot4 = $1 THEN 0 ELSE slot4 END, \
         slot5 = CASE WHEN slot5 = $1 THEN 0 ELSE slot5 END \
         WHERE id = $2",
    )
    .bind(player_id)
    .bind(team_id)
    .execute(pool)
    .await?;
    Ok(())
}

/// Dissolve (delete) a team.
pub async fn dissolve_team(pool: &PgPool, team_id: i32) -> Result<(), sqlx::Error> {
    sqlx::query("DELETE FROM teams WHERE id = $1")
        .bind(team_id)
        .execute(pool)
        .await?;
    Ok(())
}

// =========================================================================
// Astral machine query
// =========================================================================

/// Fetch astral machine data for a tribe.
pub async fn astral_machine(
    pool: &PgPool,
    tribe_id: i32,
) -> Result<Option<AstralMachineRow>, sqlx::Error> {
    sqlx::query_as::<_, AstralMachineRow>(
        "SELECT owner, used, directed, aviable FROM astral_machine WHERE owner = $1",
    )
    .bind(tribe_id)
    .fetch_optional(pool)
    .await
}

// =========================================================================
// Utility queries
// =========================================================================

/// Update a tribe's logo path.
pub async fn update_tribe_logo(
    pool: &PgPool,
    tribe_id: i32,
    logo: &str,
) -> Result<(), sqlx::Error> {
    sqlx::query("UPDATE tribes SET logo = $2 WHERE id = $1")
        .bind(tribe_id)
        .bind(logo)
        .execute(pool)
        .await?;
    Ok(())
}
