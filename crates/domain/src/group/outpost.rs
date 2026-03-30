//! Outpost domain logic: resource checks, combat resolution, veteran stats.

// ---------------------------------------------------------------------------
// Upgrade resource calculations (matches PHP `checkresources()`)
// ---------------------------------------------------------------------------

/// How many size levels a player can afford with current resources.
#[allow(clippy::cast_possible_truncation)]
pub fn max_size_upgrades(current_size: i32, outpost_gold: i32, platinum: i32, pine: i32) -> i32 {
    if outpost_gold <= 0 || platinum <= 0 || pine <= 0 {
        return 0;
    }
    let s = f64::from(current_size);
    let g = f64::from(outpost_gold);
    let delta = 4.0 * s * (s + 3.0) + 9.0 + 4.0 * g / 125.0;
    let max_gold = ((delta.sqrt() - 3.0) / 2.0 - s).floor() as i32;

    let max_platinum = platinum / 10;

    let delta_pine = 4.0 * s * (s - 1.0) + 1.0 + 8.0 * f64::from(pine);
    let max_pine = ((delta_pine.sqrt() + 1.0 - 2.0 * s) / 2.0).floor() as i32;

    max_gold.min(max_platinum).min(max_pine).max(0)
}

/// How many lairs or barracks a player can build.
#[allow(clippy::cast_possible_truncation)]
pub fn max_structure_upgrades(
    current_count: i32,
    outpost_size: i32,
    other_structures: i32,
    outpost_gold: i32,
    meteor: i32,
    secondary_mineral: i32,
) -> i32 {
    if outpost_gold <= 0 || meteor <= 0 || secondary_mineral <= 0 {
        return 0;
    }
    let c = f64::from(current_count);
    let g = f64::from(outpost_gold);

    let delta = 4.0 * c * (c + 3.0) + 9.0 + 4.0 * g / 25.0;
    let max_gold = ((delta.sqrt() - 3.0) / 2.0 - c).floor() as i32;

    let delta_meteor = 4.0 * c * (c + 1.0) + 1.0 + 8.0 * f64::from(meteor);
    let max_meteor = ((delta_meteor.sqrt() + 1.0 - 2.0 * c) / 2.0).floor() as i32 - 1;

    let delta_sec = 4.0 * c * (c + 1.0) + 1.0 + 8.0 * f64::from(secondary_mineral) / 5.0;
    let max_sec = ((delta_sec.sqrt() + 1.0 - 2.0 * c) / 2.0).floor() as i32 - 1;

    let max_space = outpost_size / 4 - current_count - other_structures;

    max_gold.min(max_meteor).min(max_sec).min(max_space).max(0)
}

/// Calculate gold cost for upgrading size by `levels`.
pub fn size_upgrade_cost(current_size: i32, levels: i32) -> (i32, i32, i32) {
    let mut gold = 0;
    let mut pine = 0;
    for i in 1..=levels {
        gold += (i + current_size + 1) * 250;
        pine += i + current_size - 1;
    }
    let platinum = levels * 10;
    (gold, platinum, pine)
}

/// Calculate resource cost for building lairs/barracks.
pub fn structure_build_cost(current_count: i32, amount: i32) -> (i32, i32, i32) {
    // meteor_sum = amount * (current_count + (amount+1)/2)
    let meteor_sum = amount * (current_count + (amount + 1) / 2);
    let gold = meteor_sum * 50;
    let secondary = meteor_sum * 5;
    (gold, meteor_sum, secondary)
}

// ---------------------------------------------------------------------------
// Veteran power calculation
// ---------------------------------------------------------------------------

/// Computed attack and defense for a single veteran.
pub struct VeteranStats {
    pub attack: i32,
    pub defense: i32,
}

/// Input for computing veteran attack/defense.
pub struct VeteranEquipment<'a> {
    pub wpower: i32,
    pub weapon_name: &'a str,
    pub opower: i32,
    pub apower: i32,
    pub hpower: i32,
    pub lpower: i32,
    pub ring1: Option<&'a str>,
    pub rpower1: i32,
    pub ring2: Option<&'a str>,
    pub rpower2: i32,
}

/// Calculate veteran's effective attack and defense from equipment.
pub fn veteran_stats(eq: &VeteranEquipment<'_>) -> VeteranStats {
    let mut attack = eq.wpower + 1;
    let is_bow = eq.weapon_name.contains("Łuk") || eq.weapon_name.contains("łuk");
    if is_bow {
        attack += eq.opower;
    }
    let mut defense = eq.apower + eq.hpower + eq.lpower + 1;

    if let Some(r1) = eq.ring1 {
        if r1.contains("siły") || r1.contains("zręczności") {
            attack += eq.rpower1;
        } else {
            defense += eq.rpower1;
        }
    }
    if let Some(r2) = eq.ring2 {
        if r2.contains("siły") || r2.contains("zręczności") {
            attack += eq.rpower2;
        } else {
            defense += eq.rpower2;
        }
    }

    VeteranStats { attack, defense }
}

// ---------------------------------------------------------------------------
// Outpost combat stats
// ---------------------------------------------------------------------------

/// Aggregate outpost combat stats.
pub struct OutpostCombatStats {
    pub attack: f64,
    pub defense: f64,
}

/// Input data for computing outpost combat strength.
pub struct OutpostCombatInput {
    pub warriors: i32,
    pub archers: i32,
    pub catapults: i32,
    pub barricades: i32,
    pub battack: i16,
    pub bdefense: i16,
    pub morale: f64,
    /// Sum of monster attack from all outpost monsters.
    pub monster_attack: i32,
    /// Sum of monster defense.
    pub monster_defense: i32,
    /// Sum of veteran attack.
    pub veteran_attack: i32,
    /// Sum of veteran defense.
    pub veteran_defense: i32,
    /// Random variation (-5..=5 / 100).
    pub random_bonus: f64,
}

/// Compute outpost attack and defense power.
pub fn compute_combat_stats(input: &OutpostCombatInput) -> OutpostCombatStats {
    let base_attack = f64::from(
        input.warriors * 3
            + input.archers
            + input.catapults * 3
            + input.monster_attack
            + input.veteran_attack,
    );
    let base_defense = f64::from(
        input.warriors
            + input.archers * 3
            + input.barricades * 3
            + input.monster_defense
            + input.veteran_defense,
    );

    // Morale bonus
    let morale_modifier = compute_morale_modifier(input.morale);

    let morale_attack = base_attack * (1.0 + morale_modifier);
    let morale_defense = base_defense * (1.0 + morale_modifier);

    // Leadership bonus
    let attack_bonus = morale_attack * (f64::from(input.battack) / 100.0);
    let defense_bonus = morale_defense * (f64::from(input.bdefense) / 100.0);

    // Random variation
    let attack = morale_attack + morale_attack * input.random_bonus + attack_bonus;
    let defense = morale_defense + morale_defense * input.random_bonus + defense_bonus;

    OutpostCombatStats { attack, defense }
}

#[allow(clippy::cast_possible_truncation)]
fn compute_morale_modifier(morale: f64) -> f64 {
    if morale >= 50.0 {
        let bonus = if (morale - 50.0).abs() < f64::EPSILON {
            5
        } else {
            let extra = ((morale - 50.0) / 75.0).floor() as i32;
            (extra * 5 + 5).min(80)
        };
        f64::from(bonus) / 100.0
    } else if morale <= -50.0 {
        let penalty = if (morale + 50.0).abs() < f64::EPSILON {
            -5
        } else {
            let extra = ((morale + 50.0) / 75.0).floor() as i32;
            (extra * 5 - 5).max(-75)
        };
        f64::from(penalty) / 100.0
    } else {
        0.0
    }
}

// ---------------------------------------------------------------------------
// Battle loss calculations
// ---------------------------------------------------------------------------

/// Losses for the attacker's troops after a round.
pub struct AttackerLosses {
    pub warriors: i32,
    pub archers: i32,
    pub catapults: i32,
    pub new_fatigue: i32,
}

/// Input for attacker loss calculation.
pub struct AttackerLossInput {
    pub warriors: i32,
    pub archers: i32,
    pub catapults: i32,
    pub attacker_stronger: bool,
    pub blost: i16,
    pub fatigue: i32,
    pub attacker_size: i32,
    pub defender_size: i32,
    pub rolls: [i32; 3],
}

/// Calculate attacker troop losses.
#[allow(clippy::cast_possible_truncation)]
pub fn attacker_losses(input: &AttackerLossInput) -> AttackerLosses {
    let troops = [input.warriors, input.archers, input.catapults];
    let mut remaining = [0i32; 3];

    for (i, &count) in troops.iter().enumerate() {
        if count > 0 {
            let survive_roll = (f64::from(count) * f64::from(input.rolls[i]) / 100.0).ceil() as i32;
            let mut lost = count - survive_roll;
            if input.attacker_stronger {
                let extra = (f64::from(count) * 0.03).ceil() as i32;
                lost += extra;
            }
            let bonus = (f64::from(survive_roll) * f64::from(input.blost) / 100.0).ceil() as i32;
            let bonus = bonus.min(survive_roll);
            lost -= bonus;
            remaining[i] = (count - lost).clamp(0, count);
        }
    }

    let new_fatigue = if input.attacker_size < input.defender_size && input.fatigue > 40 {
        input.fatigue - 20
    } else if input.fatigue <= 40 && input.fatigue > 30 {
        30
    } else if input.fatigue <= 30 {
        25
    } else if input.attacker_size >= input.defender_size && input.fatigue > 40 {
        input.fatigue - 15
    } else {
        input.fatigue
    };

    AttackerLosses {
        warriors: remaining[0],
        archers: remaining[1],
        catapults: remaining[2],
        new_fatigue,
    }
}

/// Losses for the defender's troops.
pub struct DefenderLosses {
    pub warriors: i32,
    pub archers: i32,
    pub catapults: i32,
    pub barricades: i32,
}

/// Input for defender loss calculation.
pub struct DefenderLossInput {
    pub warriors: i32,
    pub archers: i32,
    pub catapults: i32,
    pub barricades: i32,
    pub blost: i16,
    pub rolls: [i32; 4],
    pub cap_to_attacker_losses: bool,
    pub attacker_total_losses: i32,
}

/// Calculate defender troop losses.
#[allow(clippy::cast_possible_truncation)]
pub fn defender_losses(input: &DefenderLossInput) -> DefenderLosses {
    let troops = [
        input.warriors,
        input.archers,
        input.catapults,
        input.barricades,
    ];
    let mut remaining = [0i32; 4];
    let mut total_def_losses = 0;

    for (i, &count) in troops.iter().enumerate() {
        if count > 0 {
            let survive_roll = (f64::from(count) * f64::from(input.rolls[i]) / 100.0).ceil() as i32;
            let mut lost = count - survive_roll;
            let bonus = (f64::from(survive_roll) * f64::from(input.blost) / 100.0).ceil() as i32;
            let bonus = bonus.min(survive_roll);
            lost -= bonus;
            remaining[i] = (count - lost).clamp(0, count);
            total_def_losses += count - remaining[i];
        }
    }

    if input.cap_to_attacker_losses && total_def_losses > input.attacker_total_losses {
        let excess = total_def_losses - input.attacker_total_losses;
        let mut to_add = excess;
        for (i, &count) in troops.iter().enumerate() {
            if count > 0 && to_add > 0 {
                let can_add = count - remaining[i];
                let add = can_add.min(to_add);
                remaining[i] += add;
                to_add -= add;
            }
        }
    }

    DefenderLosses {
        warriors: remaining[0],
        archers: remaining[1],
        catapults: remaining[2],
        barricades: remaining[3],
    }
}

/// Gold gained from a winning attack.
pub fn attack_gold_gain(
    attacker_warriors_remain: i32,
    attacker_archers_remain: i32,
    defender_warriors_remain: i32,
    defender_archers_remain: i32,
    looted_gold: i32,
    bonus_roll: i32,
) -> i32 {
    let troop_gold = (attacker_warriors_remain
        + attacker_archers_remain
        + defender_warriors_remain
        + defender_archers_remain)
        * 10
        + (defender_warriors_remain + defender_archers_remain) * 6;
    let mut total = looted_gold + troop_gold;
    if bonus_roll < 6 {
        #[allow(clippy::cast_possible_truncation)]
        {
            total += (f64::from(total) * f64::from(bonus_roll) / 100.0) as i32;
        }
    }
    total
}

/// Battle aftermath troop counts for experience calculation.
pub struct BattleAftermath {
    pub att_warriors_start: i32,
    pub att_warriors_remain: i32,
    pub att_archers_start: i32,
    pub att_archers_remain: i32,
    pub def_warriors_start: i32,
    pub def_warriors_remain: i32,
    pub def_archers_start: i32,
    pub def_archers_remain: i32,
}

/// Leadership experience from winning.
pub fn exp_win(ba: &BattleAftermath, defender_size: i32) -> i32 {
    let exp = (ba.att_warriors_start - ba.att_warriors_remain)
        + (ba.att_archers_start - ba.att_archers_remain)
        + (ba.def_warriors_start - ba.def_warriors_remain)
        + (ba.def_archers_start - ba.def_archers_remain)
        + defender_size;
    exp.max(1)
}

/// Leadership experience from losing.
pub fn exp_lose(ba: &BattleAftermath) -> i32 {
    let exp = (ba.att_warriors_start - ba.att_warriors_remain)
        + (ba.att_archers_start - ba.att_archers_remain)
        + (ba.def_warriors_start - ba.def_warriors_remain)
        + (ba.def_archers_start - ba.def_archers_remain);
    exp.max(1)
}

/// Tax collection gold calculation.
#[allow(clippy::cast_possible_truncation, clippy::cast_sign_loss)]
pub fn tax_gold(army_count: i32, times: i32, btax: i16, rolls: &[i32]) -> i32 {
    let mut total = 0;
    for &roll in rolls.iter().take(times as usize) {
        total += roll;
    }
    total *= army_count;
    let bonus = (f64::from(total) * f64::from(btax) / 100.0).round() as i32;
    total + bonus
}

/// New morale after collecting taxes.
pub fn tax_morale(current: f64, times: i32) -> f64 {
    let new = current + f64::from(times) / 10.0;
    new.min(10.0)
}

/// New fatigue after collecting taxes.
pub fn tax_fatigue(current: i32, times: i32) -> i32 {
    (current + 10 * times).min(100)
}

/// Morale label.
pub fn morale_label(morale: f64) -> &'static str {
    if morale > 49.0 {
        "Bojowe"
    } else if morale < -49.0 {
        "Bunt"
    } else {
        "Neutralne"
    }
}

/// Daily maintenance cost (matches PHP and existing reset.rs domain).
#[allow(clippy::cast_possible_truncation)]
pub fn maintenance_cost(
    warriors: i32,
    archers: i32,
    catapults: i32,
    monster_count: i32,
    veteran_count: i32,
    bcost: i16,
) -> i32 {
    let base =
        warriors * 7 + archers * 7 + catapults * 14 + monster_count * 70 + veteran_count * 70;
    let bonus = (f64::from(base) * f64::from(bcost) / 100.0).round() as i32;
    base - bonus
}

// ---------------------------------------------------------------------------
// Garrison mission logic (outpost.php)
// ---------------------------------------------------------------------------

/// Available mission types by class.
pub fn mission_types_for_class(class: &str) -> &'static [i32] {
    match class {
        "Wojownik" => &[0, 1, 2, 5, 7, 9],
        "Mag" => &[0, 2, 7, 9, 10],
        "Barbarzyńca" => &[1, 3, 4, 6, 8],
        _ => &[],
    }
}

/// Whether a class can access the garrison.
pub fn can_access_garrison(class: &str) -> bool {
    matches!(class, "Wojownik" | "Barbarzyńca" | "Mag")
}

/// Player stats for garrison mission level calculation.
pub struct GarrisonPlayerStats {
    pub condition: i32,
    pub speed: i32,
    pub agility: i32,
    pub dodge: i32,
    pub hp: i32,
    pub strength: i32,
    pub wisdom: i32,
    pub intelligence: i32,
    pub attack_skill: i32,
    pub shoot_skill: i32,
    pub magic_skill: i32,
    pub has_melee_weapon: bool,
    pub has_bow: bool,
}

/// Calculate player combat level for garrison missions.
pub fn garrison_player_level(gps: &GarrisonPlayerStats) -> (i32, &'static str) {
    let mut level = gps.condition + gps.speed + gps.agility + gps.dodge + gps.hp;
    if gps.has_melee_weapon || gps.has_bow {
        level += gps.strength;
        if gps.has_melee_weapon {
            level += gps.attack_skill;
            (level, "attack")
        } else {
            level += gps.shoot_skill;
            (level, "shoot")
        }
    } else {
        level += gps.wisdom + gps.intelligence + gps.magic_skill;
        (level, "magic")
    }
}

#[cfg(test)]
mod tests {
    use super::*;

    #[test]
    fn morale_label_test() {
        assert_eq!(morale_label(100.0), "Bojowe");
        assert_eq!(morale_label(0.0), "Neutralne");
        assert_eq!(morale_label(-100.0), "Bunt");
    }

    #[test]
    fn max_size_upgrades_no_resources() {
        assert_eq!(max_size_upgrades(1, 0, 10, 10), 0);
        assert_eq!(max_size_upgrades(1, 1000, 0, 10), 0);
    }

    #[test]
    fn veteran_stats_with_bow() {
        let s = veteran_stats(&VeteranEquipment {
            wpower: 10,
            weapon_name: "Łuk krótki",
            opower: 5,
            apower: 3,
            hpower: 2,
            lpower: 1,
            ring1: Some("Pierścień siły"),
            rpower1: 4,
            ring2: None,
            rpower2: 0,
        });
        // attack = 10 + 1 (base) + 5 (arrows, is bow) + 4 (ring1 siły) = 20
        assert_eq!(s.attack, 20);
        // defense = 3 + 2 + 1 + 1 = 7
        assert_eq!(s.defense, 7);
    }

    #[test]
    fn veteran_stats_with_sword() {
        let s = veteran_stats(&VeteranEquipment {
            wpower: 10,
            weapon_name: "Miecz stalowy",
            opower: 5,
            apower: 3,
            hpower: 2,
            lpower: 1,
            ring1: Some("Pierścień kondycji"),
            rpower1: 4,
            ring2: None,
            rpower2: 0,
        });
        // attack = 10 + 1 = 11 (no bow bonus, no ring siły/zręczności)
        assert_eq!(s.attack, 11);
        // defense = 3 + 2 + 1 + 1 + 4 (ring kondycji → defense) = 11
        assert_eq!(s.defense, 11);
    }

    #[test]
    fn structure_build_cost_test() {
        let (gold, meteor, secondary) = structure_build_cost(0, 1);
        // meteor_sum = 1 * (0 + 1) = 1
        assert_eq!(meteor, 1);
        assert_eq!(gold, 50);
        assert_eq!(secondary, 5);
    }

    #[test]
    fn tax_gold_calculation() {
        let rolls = vec![3, 5, 2];
        let gold = tax_gold(10, 3, 10, &rolls);
        // (3+5+2)*10 = 100, bonus = 10% = 10, total = 110
        assert_eq!(gold, 110);
    }
}
