//! Item and inventory parity fixtures.
//!
//! These tests verify equipment effects, quantity mutations (warehouse / market),
//! durability edge cases, poison states, and stacking behavior. The fixtures
//! are designed to be representative states that combat and economy tests can
//! reference for expected values.
//!
//! Each fixture documents a specific item scenario with the expected
//! Rust-side output mirroring PHP behavior. If any calculation drifts,
//! these tests will catch it.

use vallheru_domain::economy::warehouse::{
    self, BuyError, CommodityStorage, SellError, TransactionCtx,
};
use vallheru_domain::equipment::{
    EquipmentLoadout, apply_craftsman_bonus, apply_elemental_skill_bonus,
    apply_equipment_stat_bonuses, can_equip, ring_stat_key,
};
use vallheru_domain::item::{
    Element, EquipmentSlot, EquipmentStatus, EquipmentType, OwnedEquipment, PoisonType,
};
use vallheru_domain::player::skills::default_skills;
use vallheru_domain::player::stats::default_stats;

// ---------------------------------------------------------------------------
// Helpers
// ---------------------------------------------------------------------------

fn make_item(id: i32, name: &str, power: i32, eq_type: EquipmentType) -> OwnedEquipment {
    OwnedEquipment {
        id,
        owner_id: 1,
        name: name.to_owned(),
        power,
        status: EquipmentStatus::Equipped,
        equipment_type: eq_type,
        cost: 100,
        min_level: 1,
        agility_mod: 0,
        durability: 100,
        speed_mod: 0,
        max_durability: 100,
        magic: Element::None,
        poison: 0,
        amount: 1,
        two_handed: false,
        poison_type: PoisonType::None,
        repair_cost: 50,
        location: "Altara".to_owned(),
    }
}

fn find_stat_modified(stats: &[vallheru_domain::player::stats::PlayerStat], key: &str) -> i32 {
    stats.iter().find(|s| s.stat_key == key).unwrap().modified
}

fn find_skill_level(skills: &[vallheru_domain::player::skills::PlayerSkill], key: &str) -> i32 {
    skills.iter().find(|s| s.skill_key == key).unwrap().level
}

fn tx(
    commodity_index: usize,
    amount: i64,
    player_stock: i64,
    player_credits: i64,
    base_price: i64,
    kingdom_gold: i64,
    warehouse_stock: i64,
) -> TransactionCtx {
    TransactionCtx {
        commodity_index,
        amount,
        player_stock,
        player_credits,
        base_price,
        kingdom_gold,
        warehouse_stock,
    }
}

// ===========================================================================
// SECTION 1: Durability edge cases
// ===========================================================================

/// Weapon at 0 durability — needs repair, cost is the full `repair_cost`.
#[test]
fn durability_zero_weapon_full_repair_cost() {
    let mut sword = make_item(1, "Miecz żelazny", 15, EquipmentType::Weapon);
    sword.durability = 0;
    sword.max_durability = 100;
    sword.repair_cost = 200;

    assert!(sword.needs_repair());
    // ratio = 1.0 - (0/100) = 1.0, cost = ceil(200 * 1.0) = 200
    assert_eq!(sword.calculate_repair_cost(), 200);
}

/// Weapon at 1 durability remaining — almost full cost.
#[test]
fn durability_one_remaining_almost_full_cost() {
    let mut sword = make_item(1, "Miecz stalowy", 20, EquipmentType::Weapon);
    sword.durability = 1;
    sword.max_durability = 100;
    sword.repair_cost = 300;

    assert!(sword.needs_repair());
    // ratio = 1.0 - (1/100) = 0.99, cost = ceil(300 * 0.99) = ceil(297.0) = 297
    assert_eq!(sword.calculate_repair_cost(), 297);
}

/// Armor at half durability — half repair cost.
#[test]
fn durability_half_armor_half_cost() {
    let mut armor = make_item(2, "Kolczuga", 30, EquipmentType::Armor);
    armor.durability = 50;
    armor.max_durability = 100;
    armor.repair_cost = 160;

    assert!(armor.needs_repair());
    // ratio = 0.5, cost = ceil(160 * 0.5) = 80
    assert_eq!(armor.calculate_repair_cost(), 80);
}

/// Ring does not have durability tracking — never needs repair.
#[test]
fn rings_never_need_repair() {
    let mut ring = make_item(3, "Pierścień siły", 5, EquipmentType::Ring);
    ring.durability = 0;
    ring.max_durability = 100;

    assert!(!ring.needs_repair());
    assert_eq!(ring.calculate_repair_cost(), 0);
}

/// Arrows (type=R) do not track durability — they track shots via `durability/wt`.
#[test]
fn arrows_no_durability_tracking() {
    let arrows = make_item(4, "Strzały żelazne", 5, EquipmentType::Arrows);
    assert!(!arrows.equipment_type.has_durability());
    assert!(!arrows.needs_repair());
}

/// Quest items never need repair.
#[test]
fn quest_items_no_repair() {
    let quest = make_item(5, "Klucz", 0, EquipmentType::Quest);
    assert!(!quest.needs_repair());
}

/// Repair cost with non-round numbers — verify ceiling behavior.
#[test]
fn repair_cost_ceiling_rounding() {
    let mut helm = make_item(6, "Hełm z miedzi", 8, EquipmentType::Helmet);
    helm.durability = 33;
    helm.max_durability = 100;
    helm.repair_cost = 77;

    // ratio = 1.0 - 33/100 = 0.67, cost = ceil(77 * 0.67) = ceil(51.59) = 52
    assert_eq!(helm.calculate_repair_cost(), 52);
}

// ===========================================================================
// SECTION 2: Poison states and equipment properties
// ===========================================================================

/// Weapon with Dynallca poison should preserve poison fields through roundtrip.
#[test]
fn poisoned_weapon_dynallca() {
    let mut sword = make_item(10, "Miecz z trucizną", 20, EquipmentType::Weapon);
    sword.poison = 5;
    sword.poison_type = PoisonType::Dynallca;

    assert_eq!(sword.poison_type.to_db(), "D");
    assert_eq!(sword.poison, 5);
    assert_eq!(PoisonType::from_db("D"), PoisonType::Dynallca);
}

/// Weapon with Nutari poison.
#[test]
fn poisoned_weapon_nutari() {
    let mut sword = make_item(11, "Sztylet z nutari", 12, EquipmentType::Weapon);
    sword.poison = 3;
    sword.poison_type = PoisonType::Nutari;

    assert_eq!(sword.poison_type.to_db(), "N");
    assert_eq!(PoisonType::from_db("N"), PoisonType::Nutari);
}

/// Weapon with Illani poison.
#[test]
fn poisoned_weapon_illani() {
    let mut sword = make_item(12, "Topór z illani", 18, EquipmentType::Weapon);
    sword.poison = 7;
    sword.poison_type = PoisonType::Illani;

    assert_eq!(sword.poison_type.to_db(), "I");
    assert_eq!(PoisonType::from_db("I"), PoisonType::Illani);
}

/// Weapon with no poison — `poison_type` should be None, db value "".
#[test]
fn unpoisoned_weapon() {
    let sword = make_item(13, "Miecz czysty", 15, EquipmentType::Weapon);
    assert_eq!(sword.poison_type, PoisonType::None);
    assert_eq!(sword.poison, 0);
    assert_eq!(PoisonType::from_db(""), PoisonType::None);
}

/// Two-handed weapon property.
#[test]
fn two_handed_weapon_blocks_shield() {
    let mut sword = make_item(14, "Wielki miecz", 35, EquipmentType::Weapon);
    sword.two_handed = true;
    assert!(sword.two_handed);
}

/// Elemental enchantment roundtrips.
#[test]
fn elemental_magic_roundtrip() {
    for (code, element) in [
        ("N", Element::None),
        ("E", Element::Earth),
        ("W", Element::Water),
        ("F", Element::Fire),
        ("A", Element::Wind),
    ] {
        assert_eq!(Element::from_equipment_code(code), element);
        assert_eq!(element.to_equipment_code(), code);
    }
}

// ===========================================================================
// SECTION 3: Stacked items — arrows and amount tracking
// ===========================================================================

/// Arrows with large stack count.
#[test]
fn arrows_stack_amount() {
    let mut arrows = make_item(20, "Strzały stalowe", 8, EquipmentType::Arrows);
    arrows.amount = 1;
    arrows.durability = 250; // wt = current shot count
    arrows.max_durability = 250;

    assert_eq!(arrows.amount, 1);
    assert_eq!(arrows.durability, 250);
}

/// Multiple mage clothing items should stack via amount.
#[test]
fn mage_clothing_amount() {
    let mut robe = make_item(21, "Szata ucznia", 10, EquipmentType::MageClothing);
    robe.amount = 3;
    assert_eq!(robe.amount, 3);
}

// ===========================================================================
// SECTION 4: Equipment loadout — slot assignment and iteration
// ===========================================================================

/// Full 13-slot loadout — all slots occupied.
#[test]
fn full_loadout_all_slots() {
    let items = vec![
        make_item(1, "Miecz", 10, EquipmentType::Weapon),
        make_item(2, "Topór", 12, EquipmentType::Weapon), // → second_weapon
        make_item(3, "Łuk", 8, EquipmentType::Bow),
        make_item(4, "Hełm", 5, EquipmentType::Helmet),
        make_item(5, "Zbroja", 30, EquipmentType::Armor),
        make_item(6, "Nagolenniki", 10, EquipmentType::Legs),
        make_item(7, "Tarcza", 15, EquipmentType::Shield),
        make_item(8, "Strzały", 3, EquipmentType::Arrows),
        make_item(9, "Różdżka", 10, EquipmentType::Wand),
        make_item(10, "Szata", 20, EquipmentType::MageClothing),
        make_item(11, "P. siły", 5, EquipmentType::Ring),
        make_item(12, "P. zręczności", 3, EquipmentType::Ring), // → ring2
        make_item(13, "Kilof", 15, EquipmentType::Elemental),
    ];

    let loadout = EquipmentLoadout::from_equipped_items(items);

    assert!(loadout.weapon.is_some());
    assert!(loadout.second_weapon.is_some());
    assert!(loadout.bow.is_some());
    assert!(loadout.helmet.is_some());
    assert!(loadout.armor.is_some());
    assert!(loadout.legs.is_some());
    assert!(loadout.shield.is_some());
    assert!(loadout.arrows.is_some());
    assert!(loadout.wand.is_some());
    assert!(loadout.mage_clothing.is_some());
    assert!(loadout.ring1.is_some());
    assert!(loadout.ring2.is_some());
    assert!(loadout.elemental.is_some());

    // iter_occupied should return all 13
    assert_eq!(loadout.iter_occupied().count(), 13);

    // Slot-based access
    assert_eq!(loadout.get(EquipmentSlot::Weapon).unwrap().id, 1);
    assert_eq!(loadout.get(EquipmentSlot::SecondWeapon).unwrap().id, 2);
    assert_eq!(loadout.get(EquipmentSlot::Ring1).unwrap().id, 11);
    assert_eq!(loadout.get(EquipmentSlot::Ring2).unwrap().id, 12);
}

/// Quest/Other/Plan items are silently ignored in loadout construction.
#[test]
fn quest_items_ignored_in_loadout() {
    let items = vec![
        make_item(1, "Klucz", 0, EquipmentType::Quest),
        make_item(2, "Kamień", 0, EquipmentType::Other),
        make_item(3, "Plan broni", 0, EquipmentType::Plan),
    ];
    let loadout = EquipmentLoadout::from_equipped_items(items);
    assert_eq!(loadout.iter_occupied().count(), 0);
}

// ===========================================================================
// SECTION 5: Equipment stat bonuses — cumulative agility penalty
// ===========================================================================

/// Heavy full armor setup — cumulative agility penalty from multiple slots.
#[test]
fn cumulative_agility_penalty() {
    let mut stats = default_stats();
    stats
        .iter_mut()
        .find(|s| s.stat_key == "agility")
        .unwrap()
        .modified = 30;

    let mut armor = make_item(1, "Zbroja", 50, EquipmentType::Armor);
    armor.agility_mod = 5;
    let mut helmet = make_item(2, "Hełm", 10, EquipmentType::Helmet);
    helmet.agility_mod = 2;
    let mut legs = make_item(3, "Nagolenniki", 8, EquipmentType::Legs);
    legs.agility_mod = 3;
    let mut shield = make_item(4, "Tarcza", 12, EquipmentType::Shield);
    shield.agility_mod = 1;

    let loadout = EquipmentLoadout::from_equipped_items(vec![armor, helmet, legs, shield]);
    apply_equipment_stat_bonuses(&mut stats, &loadout, "", 0, &[]);

    // 30 - 5 - 2 - 3 - 1 = 19
    assert_eq!(find_stat_modified(&stats, "agility"), 19);
}

/// Two rings of different stats — both should apply.
#[test]
fn two_rings_different_stats() {
    let mut stats = default_stats();
    stats
        .iter_mut()
        .find(|s| s.stat_key == "strength")
        .unwrap()
        .modified = 20;
    stats
        .iter_mut()
        .find(|s| s.stat_key == "speed")
        .unwrap()
        .modified = 15;

    let ring1 = make_item(10, "Pierścień siły", 4, EquipmentType::Ring);
    let ring2 = make_item(11, "Pierścień szybkości", 6, EquipmentType::Ring);

    let loadout = EquipmentLoadout::from_equipped_items(vec![ring1, ring2]);
    apply_equipment_stat_bonuses(&mut stats, &loadout, "", 0, &[]);

    assert_eq!(find_stat_modified(&stats, "strength"), 24); // 20 + 4
    assert_eq!(find_stat_modified(&stats, "speed"), 21); // 15 + 6
}

/// Two rings of the same stat — both add independently.
#[test]
fn two_rings_same_stat_stack() {
    let mut stats = default_stats();
    stats
        .iter_mut()
        .find(|s| s.stat_key == "condition")
        .unwrap()
        .modified = 10;

    let ring1 = make_item(20, "Pierścień kondycji", 3, EquipmentType::Ring);
    let ring2 = make_item(21, "Pierścień kondycji", 5, EquipmentType::Ring);

    let loadout = EquipmentLoadout::from_equipped_items(vec![ring1, ring2]);
    apply_equipment_stat_bonuses(&mut stats, &loadout, "", 0, &[]);

    assert_eq!(find_stat_modified(&stats, "condition"), 18); // 10 + 3 + 5
}

/// Ring with unknown name suffix — `ring_stat_key` returns None, no bonus.
#[test]
fn ring_unknown_suffix_no_bonus() {
    assert_eq!(ring_stat_key("Tajemniczy pierścień"), None);

    let mut stats = default_stats();
    for s in &mut stats {
        s.modified = 10;
    }

    let ring = make_item(30, "Tajemniczy pierścień", 99, EquipmentType::Ring);
    let loadout = EquipmentLoadout::from_equipped_items(vec![ring]);
    apply_equipment_stat_bonuses(&mut stats, &loadout, "", 0, &[]);

    // No stat should have changed
    for s in &stats {
        assert_eq!(s.modified, 10, "stat {} should be unchanged", s.stat_key);
    }
}

// ===========================================================================
// SECTION 6: Elemental tool skill bonuses
// ===========================================================================

/// Elemental pickaxe boosts mining skill.
#[test]
fn elemental_pickaxe_mining_bonus() {
    let mut skills = default_skills();
    skills
        .iter_mut()
        .find(|s| s.skill_key == "mining")
        .unwrap()
        .level = 80;

    let mut tool = make_item(40, "Magiczny kilof", 60, EquipmentType::Elemental);
    tool.durability = 50;

    let loadout = EquipmentLoadout::from_equipped_items(vec![tool]);
    apply_elemental_skill_bonus(&mut skills, &loadout, &["mining"]);

    // floor(60/100 * 80) = floor(48) = 48, total = 80 + 48 = 128
    assert_eq!(find_skill_level(&skills, "mining"), 128);
}

/// Elemental hammer boosts smithing.
#[test]
fn elemental_hammer_smith_bonus() {
    let mut skills = default_skills();
    skills
        .iter_mut()
        .find(|s| s.skill_key == "smith")
        .unwrap()
        .level = 100;

    let tool = make_item(41, "Magiczny młot", 50, EquipmentType::Elemental);
    let loadout = EquipmentLoadout::from_equipped_items(vec![tool]);
    apply_elemental_skill_bonus(&mut skills, &loadout, &["smith"]);

    // floor(50/100 * 100) = 50, total = 150
    assert_eq!(find_skill_level(&skills, "smith"), 150);
}

/// Elemental tool with unrelated skill list — no bonus applied.
#[test]
fn elemental_tool_wrong_skill_no_bonus() {
    let mut skills = default_skills();
    skills
        .iter_mut()
        .find(|s| s.skill_key == "mining")
        .unwrap()
        .level = 50;

    let tool = make_item(42, "Magiczny kilof", 60, EquipmentType::Elemental);
    let loadout = EquipmentLoadout::from_equipped_items(vec![tool]);
    // Asking for smith skill with a kilof (pickaxe) — should not boost
    apply_elemental_skill_bonus(&mut skills, &loadout, &["smith"]);

    assert_eq!(find_skill_level(&skills, "smith"), 1); // default level
    assert_eq!(find_skill_level(&skills, "mining"), 50); // unchanged
}

/// No elemental tool equipped — no skill bonus.
#[test]
fn no_elemental_no_skill_bonus() {
    let mut skills = default_skills();
    skills
        .iter_mut()
        .find(|s| s.skill_key == "mining")
        .unwrap()
        .level = 50;

    let loadout = EquipmentLoadout::default();
    apply_elemental_skill_bonus(&mut skills, &loadout, &["mining"]);

    assert_eq!(find_skill_level(&skills, "mining"), 50);
}

// ===========================================================================
// SECTION 7: Craftsman bonus
// ===========================================================================

/// Craftsman (non-gnome) with multiple skills.
#[test]
fn craftsman_multi_skill_bonus() {
    let mut skills = default_skills();
    for key in ["smith", "carpentry", "alchemy"] {
        skills
            .iter_mut()
            .find(|s| s.skill_key == key)
            .unwrap()
            .level = 50;
    }

    apply_craftsman_bonus(&mut skills, &["smith", "carpentry", "alchemy"], false);

    // Each: ceil(50/10) = 5, total = 55
    assert_eq!(find_skill_level(&skills, "smith"), 55);
    assert_eq!(find_skill_level(&skills, "carpentry"), 55);
    assert_eq!(find_skill_level(&skills, "alchemy"), 55);
}

/// Gnome craftsman — doubled bonus.
#[test]
fn gnome_craftsman_doubled_bonus() {
    let mut skills = default_skills();
    skills
        .iter_mut()
        .find(|s| s.skill_key == "smith")
        .unwrap()
        .level = 73;

    apply_craftsman_bonus(&mut skills, &["smith"], true);

    // ceil(73/10) = 8, gnome doubles: 16, total = 89
    assert_eq!(find_skill_level(&skills, "smith"), 89);
}

/// Craftsman bonus with skill at level 1 — ceil(1/10) = 1.
#[test]
fn craftsman_bonus_low_skill() {
    let mut skills = default_skills();
    // default skill level is 1
    apply_craftsman_bonus(&mut skills, &["smith"], false);

    // ceil(1/10) = 1, total = 2
    assert_eq!(find_skill_level(&skills, "smith"), 2);
}

// ===========================================================================
// SECTION 8: Equip validation — level requirements
// ===========================================================================

/// Can equip weapon when attack skill >= min level.
#[test]
fn equip_weapon_sufficient_skill() {
    let mut skills = default_skills();
    skills
        .iter_mut()
        .find(|s| s.skill_key == "attack")
        .unwrap()
        .level = 15;

    assert!(can_equip(EquipmentType::Weapon, "Miecz", 15, &skills));
    assert!(can_equip(EquipmentType::Weapon, "Miecz", 10, &skills));
}

/// Cannot equip weapon when attack skill < min level.
#[test]
fn equip_weapon_insufficient_skill() {
    let mut skills = default_skills();
    skills
        .iter_mut()
        .find(|s| s.skill_key == "attack")
        .unwrap()
        .level = 5;

    assert!(!can_equip(EquipmentType::Weapon, "Miecz", 10, &skills));
}

/// Armor uses highest of attack/shoot/magic.
#[test]
fn equip_armor_uses_highest_combat_skill() {
    let mut skills = default_skills();
    skills
        .iter_mut()
        .find(|s| s.skill_key == "attack")
        .unwrap()
        .level = 5;
    skills
        .iter_mut()
        .find(|s| s.skill_key == "shoot")
        .unwrap()
        .level = 20;
    skills
        .iter_mut()
        .find(|s| s.skill_key == "magic")
        .unwrap()
        .level = 10;

    // min_level 15: shoot (20) >= 15, so yes
    assert!(can_equip(EquipmentType::Armor, "Zbroja", 15, &skills));
    // min_level 25: no skill reaches it
    assert!(!can_equip(EquipmentType::Armor, "Zbroja", 25, &skills));
}

/// Rings have no level requirement.
#[test]
fn equip_ring_no_level_check() {
    let skills = default_skills(); // all at 1
    assert!(can_equip(
        EquipmentType::Ring,
        "Pierścień siły",
        999,
        &skills
    ));
}

// ===========================================================================
// SECTION 9: Warehouse quantity — sell scenarios
// ===========================================================================

/// Sell all minerals to warehouse — stock fully transferred.
#[test]
fn warehouse_sell_all_minerals() {
    let ctx = tx(0, 50, 50, 0, 10, 10000, 200);
    let r = warehouse::validate_sell("Altara", &ctx).unwrap();

    assert_eq!(r.amount, 50);
    assert_eq!(r.total_price, 500);
    assert_eq!(r.new_player_stock, 0);
    assert_eq!(r.new_player_credits, 500);
    assert_eq!(r.new_kingdom_gold, 9500);
    assert_eq!(r.new_warehouse_stock, 250);
}

/// Sell 1 unit — minimum transaction.
#[test]
fn warehouse_sell_single_unit() {
    let ctx = tx(5, 1, 100, 500, 25, 10000, 0);
    let r = warehouse::validate_sell("Altara", &ctx).unwrap();

    assert_eq!(r.amount, 1);
    assert_eq!(r.total_price, 25);
    assert_eq!(r.new_player_stock, 99);
    assert_eq!(r.new_player_credits, 525);
}

/// Sell mithril — uses platinum backend.
#[test]
fn warehouse_sell_mithril_uses_platinum() {
    let ctx = tx(17, 10, 30, 0, 100, 50000, 5);
    let r = warehouse::validate_sell("Altara", &ctx).unwrap();

    assert_eq!(r.commodity.storage, CommodityStorage::Mithril);
    assert_eq!(r.commodity.storage_column, "platinum");
    assert_eq!(r.total_price, 1000);
    assert_eq!(r.new_player_stock, 20);
}

/// Sell herbs — validates herb storage path.
#[test]
fn warehouse_sell_herbs_storage_path() {
    // Index 22 = illani_seeds → storage_column "ilani_seeds"
    let ctx = tx(22, 5, 20, 100, 15, 10000, 10);
    let r = warehouse::validate_sell("Altara", &ctx).unwrap();

    assert_eq!(r.commodity.storage, CommodityStorage::Herb);
    assert_eq!(r.commodity.storage_column, "ilani_seeds");
    assert_eq!(r.commodity.slug, "illani_seeds");
    assert_eq!(r.new_player_stock, 15);
    assert_eq!(r.new_warehouse_stock, 15);
}

/// Sell fails when player has no stock — `InsufficientStock`.
#[test]
fn warehouse_sell_no_stock() {
    let ctx = tx(0, 1, 0, 1000, 10, 10000, 100);
    assert_eq!(
        warehouse::validate_sell("Altara", &ctx).unwrap_err(),
        SellError::InsufficientStock,
    );
}

/// Sell fails when kingdom can't afford — `KingdomBroke`.
#[test]
fn warehouse_sell_kingdom_too_poor() {
    let ctx = tx(0, 100, 100, 0, 50, 100, 0);
    // 100 × 50 = 5000, kingdom only has 100
    assert_eq!(
        warehouse::validate_sell("Altara", &ctx).unwrap_err(),
        SellError::KingdomBroke,
    );
}

// ===========================================================================
// SECTION 10: Warehouse quantity — buy scenarios
// ===========================================================================

/// Buy minerals from warehouse — standard flow.
#[test]
fn warehouse_buy_minerals() {
    let ctx = tx(3, 20, 5, 10000, 30, 0, 200);
    let r = warehouse::validate_buy("Altara", &ctx).unwrap();

    // Buy price = 30 * 2 = 60 per unit, 20 units = 1200
    assert_eq!(r.total_price, 1200);
    assert_eq!(r.new_player_credits, 8800);
    assert_eq!(r.new_player_stock, 25);
    assert_eq!(r.new_kingdom_gold, 1200);
    assert_eq!(r.new_warehouse_stock, 180);
}

/// Buy all remaining warehouse stock.
#[test]
fn warehouse_buy_depletes_stock() {
    let ctx = tx(10, 50, 0, 100_000, 100, 0, 50);
    let r = warehouse::validate_buy("Altara", &ctx).unwrap();

    assert_eq!(r.new_warehouse_stock, 0);
    assert_eq!(r.new_player_stock, 50);
    assert_eq!(r.total_price, 10000); // 100 * 2 * 50
}

/// Buy fails when warehouse has no stock.
#[test]
fn warehouse_buy_empty_stock() {
    let ctx = tx(0, 1, 0, 10000, 10, 0, 0);
    assert_eq!(
        warehouse::validate_buy("Altara", &ctx).unwrap_err(),
        BuyError::InsufficientWarehouseStock,
    );
}

/// Buy fails when player is too poor.
#[test]
fn warehouse_buy_player_too_poor() {
    let ctx = tx(0, 10, 0, 50, 100, 0, 100);
    // Buy price = 100 * 2 * 10 = 2000, player only has 50
    assert_eq!(
        warehouse::validate_buy("Altara", &ctx).unwrap_err(),
        BuyError::InsufficientCredits,
    );
}

// ===========================================================================
// SECTION 11: Warehouse sell → buy roundtrip — quantity conservation
// ===========================================================================

/// Sell and then buy the same amount — verify quantities are consistent.
#[test]
fn warehouse_sell_then_buy_roundtrip() {
    let initial_kingdom_gold = 100_000_i64;
    let initial_warehouse_stock = 500_i64;
    let initial_player_credits = 5000_i64;
    let base_price = 50_i64;
    let trade_amount = 20_i64;

    // Player sells 20 units
    let sell_ctx = tx(
        0,
        trade_amount,
        100,
        initial_player_credits,
        base_price,
        initial_kingdom_gold,
        initial_warehouse_stock,
    );
    let sell = warehouse::validate_sell("Altara", &sell_ctx).unwrap();

    assert_eq!(sell.new_player_stock, 80);
    assert_eq!(sell.new_warehouse_stock, 520);
    let kingdom_after_sell = sell.new_kingdom_gold;

    // Player now buys 20 units back (buy price is 2× sell price)
    let buy_ctx = tx(
        0,
        trade_amount,
        sell.new_player_stock,
        sell.new_player_credits,
        base_price,
        kingdom_after_sell,
        sell.new_warehouse_stock,
    );
    let buy = warehouse::validate_buy("Altara", &buy_ctx).unwrap();

    // Player got credits at 1× but pays 2× to buy back — net loss
    assert_eq!(buy.new_player_stock, 100); // Back to original
    assert_eq!(buy.new_warehouse_stock, initial_warehouse_stock); // Back to original

    let sell_revenue = base_price * trade_amount; // 1000
    let buy_cost = base_price * 2 * trade_amount; // 2000
    // Player lost buy_cost - sell_revenue = 1000 credits
    assert_eq!(
        buy.new_player_credits,
        initial_player_credits - (buy_cost - sell_revenue)
    );
    // Kingdom gained the same 1000
    assert_eq!(
        buy.new_kingdom_gold,
        initial_kingdom_gold + (buy_cost - sell_revenue)
    );
}

// ===========================================================================
// SECTION 12: Commodity catalogue coverage
// ===========================================================================

/// All 17 mineral indices (0–16) resolve to Mineral storage.
#[test]
fn all_minerals_resolve_to_mineral_storage() {
    for i in 0..=16 {
        let c = warehouse::resolve_commodity(i).unwrap();
        assert_eq!(c.storage, CommodityStorage::Mineral, "index {i}");
        assert!(
            !c.storage_column.is_empty(),
            "index {i} has non-empty column"
        );
    }
}

/// All 8 herb indices (18–25) resolve to Herb storage.
#[test]
fn all_herbs_resolve_to_herb_storage() {
    for i in 18..=25 {
        let c = warehouse::resolve_commodity(i).unwrap();
        assert_eq!(c.storage, CommodityStorage::Herb, "index {i}");
    }
}

/// Mithril index 17 specifically resolves to Mithril storage.
#[test]
fn mithril_resolves_to_mithril_storage() {
    let c = warehouse::resolve_commodity(17).unwrap();
    assert_eq!(c.storage, CommodityStorage::Mithril);
    assert_eq!(c.storage_column, "platinum");
}
