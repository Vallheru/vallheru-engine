//! Pure combat formulas extracted from PHP `includes/funkcje.php`,
//! `includes/turnfight.php`, and `includes/resurect.php`.
//!
//! Every function here is deterministic (no RNG, no DB). Where the PHP
//! code uses `rand()`, the Rust caller passes the roll result so formulas
//! remain testable.
//!
//! ## PHP slot index → Rust mapping
//!
//! | PHP index | Rust field                |
//! |-----------|---------------------------|
//! | 0         | weapon                    |
//! | 1         | bow                       |
//! | 2         | helmet                    |
//! | 3         | armor                     |
//! | 4         | legs                      |
//! | 5         | shield                    |
//! | 6         | arrows                    |
//! | 7         | wand                      |
//! | 8         | `mage_clothing`           |
//! | 9         | ring1                     |
//! | 10        | ring2                     |
//! | 11        | `second_weapon`           |
//! | 12        | elemental                 |
//!
//! ## PHP equip sub-indices → Rust `OwnedEquipment` fields
//!
//! | PHP `[n]` | Meaning       | Rust field       |
//! |-----------|---------------|------------------|
//! | `[0]`     | item id       | `id`             |
//! | `[2]`     | power         | `power`          |
//! | `[3]`     | poison type   | `poison_type`    |
//! | `[4]`     | weight/agmod  | `agility_mod`    |
//! | `[6]`     | durability    | `durability`     |
//! | `[8]`     | poison bonus  | `poison`         |
//! | `[10]`    | element       | `magic`          |

use crate::item::{Element, OwnedEquipment, PoisonType, Spell};
use crate::player::bonuses::PlayerBonus;
use crate::player::skills::PlayerSkill;
use crate::player::stats::PlayerStat;
use crate::player::{Class, Race};

// ---------------------------------------------------------------------------
// Lookup helpers
// ---------------------------------------------------------------------------

fn stat_modified(stats: &[PlayerStat], key: &str) -> i32 {
    stats
        .iter()
        .find(|s| s.stat_key == key)
        .map_or(0, |s| s.modified)
}

fn skill_level(skills: &[PlayerSkill], key: &str) -> i32 {
    skills
        .iter()
        .find(|s| s.skill_key == key)
        .map_or(0, |s| s.level)
}

/// Check bonus value for a named trigger.
///
/// Re-uses the existing `crate::equipment::check_bonus` function.
fn bonus_value(
    trigger: &str,
    stats: &[PlayerStat],
    skills: &[PlayerSkill],
    bonuses: &[PlayerBonus],
) -> i32 {
    crate::equipment::check_bonus(trigger, stats, skills, bonuses)
}

// ---------------------------------------------------------------------------
// Hit location
// ---------------------------------------------------------------------------

/// Body area hit by an attack.
///
/// PHP `hitlocation` returns 0–3 based on `rand(1,100)`:
/// 1–10 → head(0), 11–70 → body(1), 71–85 → legs(2), 86–100 → arms(3).
#[derive(Debug, Clone, Copy, PartialEq, Eq)]
pub enum HitLocation {
    Head,
    Body,
    Legs,
    Arms,
}

impl HitLocation {
    /// Resolve a roll in `1..=100` to a hit location.
    pub fn from_roll(roll: i32) -> Self {
        match roll {
            1..=10 => Self::Head,
            11..=70 => Self::Body,
            71..=85 => Self::Legs,
            _ => Self::Arms,
        }
    }

    /// Armor slot index for this body part (Helmet=0, Armor=1, Legs=2, Shield=3).
    /// Maps to PHP `$intHit + 2` into the equip array (indices 2–5).
    pub fn armor_slot_offset(&self) -> usize {
        match self {
            Self::Head => 0, // helmet
            Self::Body => 1, // armor
            Self::Legs => 2, // legs
            Self::Arms => 3, // shield
        }
    }
}

// ---------------------------------------------------------------------------
// Attack type — determines which skill/stat feeds into damage
// ---------------------------------------------------------------------------

/// The mode of player attack.
#[derive(Debug, Clone, Copy, PartialEq, Eq)]
pub enum AttackType {
    Melee,
    Ranged,
    Spell,
}

// ---------------------------------------------------------------------------
// Element interaction
// ---------------------------------------------------------------------------

/// Element that is strong against the given monster damage type.
///
/// PHP `$arrElements3`: water→W, fire→F, wind→A, earth→E
/// (element that the armor should have to gain bonus defense).
pub fn element_strong_against(monster_element: Element) -> Element {
    monster_element
}

/// Element that is weak against the given monster damage type.
///
/// PHP `$arrElements4`: water→F, fire→A, wind→E, earth→W
pub fn element_weak_against(monster_element: Element) -> Element {
    match monster_element {
        Element::Water => Element::Fire,
        Element::Fire => Element::Wind,
        Element::Wind => Element::Earth,
        Element::Earth => Element::Water,
        Element::None => Element::None,
    }
}

/// Elemental counter for defensive spells.
///
/// PHP: `$arrElements = array('water'=>'fire', 'fire'=>'wind', 'wind'=>'earth', 'earth'=>'water')`.
/// If spell element == monster damage type → 2× defense.
/// If spell element == counter of monster damage type → ½ defense.
pub fn defensive_spell_element_counter(monster_dmg: Element) -> Element {
    match monster_dmg {
        Element::Water => Element::Fire,
        Element::Fire => Element::Wind,
        Element::Wind => Element::Earth,
        Element::Earth => Element::Water,
        Element::None => Element::None,
    }
}

// ---------------------------------------------------------------------------
// Elemental resistance (monster side)
// ---------------------------------------------------------------------------

/// Monster elemental resistance strength.
#[derive(Debug, Clone, Copy, PartialEq, Eq)]
pub enum ResistanceStrength {
    None,
    Weak,
    Medium,
    Strong,
}

impl ResistanceStrength {
    pub fn parse(s: &str) -> Self {
        match s {
            "weak" => Self::Weak,
            "medium" => Self::Medium,
            "strong" => Self::Strong,
            _ => Self::None,
        }
    }

    /// Fraction of damage reduced against matching elemental attacks.
    pub fn weapon_reduction_fraction(&self) -> f64 {
        match self {
            Self::None => 0.0,
            Self::Weak => 0.1,
            Self::Medium => 0.25,
            Self::Strong => 0.5,
        }
    }

    /// Fraction of spell damage reduced against matching elemental attacks.
    pub fn spell_reduction_fraction(&self) -> f64 {
        match self {
            Self::None => 0.0,
            Self::Weak => 0.25,
            Self::Medium => 0.5,
            Self::Strong => 0.75,
        }
    }
}

/// Monster elemental resistance.
#[derive(Debug, Clone, Copy)]
pub struct MonsterResistance {
    pub element: Element,
    pub strength: ResistanceStrength,
}

impl MonsterResistance {
    pub fn none() -> Self {
        Self {
            element: Element::None,
            strength: ResistanceStrength::None,
        }
    }
}

// ---------------------------------------------------------------------------
// Weapon effective power (with poison + elemental adjustments)
// ---------------------------------------------------------------------------

/// Effective power of a melee weapon after Dynallca poison and elemental
/// resistance adjustments.
///
/// PHP `fightmonster()`:
/// ```php
/// if ($player->equip[0][3] == 'D')
///     $player->equip[0][2] = $player->equip[0][2] + $player->equip[0][8];
/// if ($player->equip[0][10] != 'N' && $player->equip[0][10] == $enemy['resistance'][0])
///     switch ($enemy['resistance'][1]) { ... reduction ... }
/// ```
pub fn effective_weapon_power(weapon: &OwnedEquipment, resistance: MonsterResistance) -> f64 {
    let mut power = f64::from(weapon.power);

    // Dynallca poison bonus
    if weapon.poison_type == PoisonType::Dynallca {
        power += f64::from(weapon.poison);
    }

    // Elemental resistance reduction
    if weapon.magic != Element::None && weapon.magic == resistance.element {
        power -= power * resistance.strength.weapon_reduction_fraction();
    }

    power
}

/// Effective power of an arrow after Dynallca poison and elemental
/// resistance adjustments.
pub fn effective_arrow_power(
    bow: &OwnedEquipment,
    arrows: &OwnedEquipment,
    resistance: MonsterResistance,
) -> f64 {
    let mut base = f64::from(bow.power) + f64::from(arrows.power);

    // Dynallca poison on arrows
    if arrows.poison_type == PoisonType::Dynallca {
        base += f64::from(arrows.poison);
    }

    // Elemental resistance on arrows
    if arrows.magic != Element::None && arrows.magic == resistance.element {
        base -= f64::from(arrows.power) * resistance.strength.weapon_reduction_fraction();
    }

    base
}

// ---------------------------------------------------------------------------
// Player damage calculation
// ---------------------------------------------------------------------------

/// Input context needed for damage calculation — avoids parameter explosion.
#[derive(Debug, Clone)]
pub struct DamageContext<'a> {
    pub class: &'a Class,
    pub stats: &'a [PlayerStat],
    pub skills: &'a [PlayerSkill],
    pub bonuses: &'a [PlayerBonus],
    pub weapon: Option<&'a OwnedEquipment>,
    pub second_weapon: Option<&'a OwnedEquipment>,
    pub bow: Option<&'a OwnedEquipment>,
    pub arrows: Option<&'a OwnedEquipment>,
    pub wand: Option<&'a OwnedEquipment>,
    pub helmet: Option<&'a OwnedEquipment>,
    pub armor: Option<&'a OwnedEquipment>,
    pub legs: Option<&'a OwnedEquipment>,
    pub shield: Option<&'a OwnedEquipment>,
    pub attack_spell: Option<&'a Spell>,
    pub pet_attack: i32,
    pub pet_defense: i32,
    pub monster_resistance: MonsterResistance,
}

/// Result of base damage calculation before random roll.
#[derive(Debug, Clone)]
pub struct BaseDamage {
    /// Computed base damage (before skill roll and endurance subtraction).
    pub damage: f64,
    /// Critical hit chance percentage (max 6 + assassin bonus).
    pub crit_chance: i32,
    /// Attack type resolved from equipment.
    pub attack_type: AttackType,
    /// The combat skill key used (attack/shoot/magic).
    pub skill_key: &'static str,
}

/// Compute the player's base damage from equipment and stats.
///
/// This is the deterministic portion before the random skill roll
/// (`rand(1, skill_level)`) and before subtracting monster endurance.
///
/// PHP: main body of `fightmonster()` / `attack()` damage setup.
pub fn player_base_damage(ctx: &DamageContext<'_>) -> BaseDamage {
    let is_fighter = matches!(ctx.class, Class::Warrior | Class::Barbarian);
    let strength = stat_modified(ctx.stats, "strength");
    let agility = stat_modified(ctx.stats, "agility");
    let inteli = stat_modified(ctx.stats, "inteli");
    let attack_skill = skill_level(ctx.skills, "attack")
        + bonus_value("weaponmaster", ctx.stats, ctx.skills, ctx.bonuses);
    let shoot_skill = skill_level(ctx.skills, "shoot")
        + bonus_value("weaponmaster", ctx.stats, ctx.skills, ctx.bonuses);
    let magic_skill = skill_level(ctx.skills, "magic");

    let mut damage: f64 = 0.0;
    let mut crit_chance: i32;
    let mut attack_type = AttackType::Melee;
    let mut skill_key: &str = "magic";

    // --- Melee weapon ---
    if let Some(weapon) = ctx.weapon {
        let wp = effective_weapon_power(weapon, ctx.monster_resistance);
        if is_fighter {
            // Warrior/Barbarian: strength + weapon_power + attack_skill
            damage = f64::from(strength) + wp + f64::from(attack_skill);
        } else {
            damage = f64::from(strength) + wp;
        }
        crit_chance = attack_skill.min(6);
        crit_chance += bonus_value("assasin", ctx.stats, ctx.skills, ctx.bonuses);
        attack_type = AttackType::Melee;
        skill_key = "attack";
    } else {
        crit_chance = 0;
    }

    // --- Second weapon (Barbarian dual-wield) ---
    if let Some(sw) = ctx.second_weapon {
        // PHP: $stat['damage'] += (($player->equip[11][2] + $player->stats['strength'][2]) + ceil($player->skills['attack'][1] / 10));
        // Note: in fightmonster, it uses full attack_skill; in turnfight attack() it also uses full.
        // Actually re-checking: fightmonster line 1057: ceil($player->skills['attack'][1] / 10)
        damage +=
            f64::from(sw.power) + f64::from(strength) + (f64::from(attack_skill) / 10.0).ceil();
        skill_key = "attack";
    }

    // --- Ranged (bow + arrows) ---
    if let Some(bow) = ctx.bow {
        if let Some(arrows) = ctx.arrows {
            let arrow_power = effective_arrow_power(bow, arrows, ctx.monster_resistance);
            let stat_bonus = f64::from(strength) / 2.0 + f64::from(agility) / 2.0;
            if is_fighter {
                // PHP in turnfight: (bonus2 + bonus) + shoot_skill (full, not /10)
                // PHP in fightmonster: (bonus2 + bonus) + ceil(shoot_skill / 10)
                // Using fightmonster version as the fast-fight baseline:
                damage = stat_bonus + arrow_power + (f64::from(shoot_skill) / 10.0).ceil();
            } else {
                damage = stat_bonus + arrow_power;
            }
            crit_chance = shoot_skill.min(6);
            crit_chance += bonus_value("assasin", ctx.stats, ctx.skills, ctx.bonuses);

            if arrows.id == 0 {
                // No arrows equipped → no damage
                damage = 0.0;
            }
            attack_type = AttackType::Ranged;
            skill_key = "shoot";
        }
    }

    // --- Spell attack ---
    if let Some(spell) = ctx.attack_spell {
        let mut spell_dmg = spell.multiplier * f64::from(inteli);

        // Bonus: bspells
        let bspell_bonus = bonus_value("bspells", ctx.stats, ctx.skills, ctx.bonuses);
        spell_dmg += spell_dmg * f64::from(bspell_bonus) / 100.0;

        // Bonus: element-specific
        let elem_bonus = bonus_value(
            spell.element.to_spell_code(),
            ctx.stats,
            ctx.skills,
            ctx.bonuses,
        );
        spell_dmg += spell_dmg * f64::from(elem_bonus) / 100.0;

        // Monster elemental resistance to spells
        if ctx.monster_resistance.element == spell.element {
            spell_dmg -= spell_dmg * ctx.monster_resistance.strength.spell_reduction_fraction();
        }

        // Armor weight penalty on spell damage
        // PHP: each armor slot reduces spell damage by weight%
        for piece in [ctx.armor, ctx.helmet, ctx.legs, ctx.shield]
            .into_iter()
            .flatten()
        {
            spell_dmg -= spell_dmg * (f64::from(piece.agility_mod) / 100.0);
        }

        // Wand bonus is random (magic_skill * rand(1, ceil(weight/20)));
        // it is added via `wand_roll` in `finalize_damage`, not here.

        if spell_dmg < 0.0 {
            spell_dmg = 0.0;
        }

        crit_chance = magic_skill.min(6);
        damage = spell_dmg;
        attack_type = AttackType::Spell;
        skill_key = "magic";
    }

    // --- Craftsman penalty: -25% ---
    if *ctx.class == Class::Craftsman {
        damage -= damage / 4.0;
    }

    // --- Rage bonus ---
    let rage_bonus = bonus_value("rage", ctx.stats, ctx.skills, ctx.bonuses);
    damage += damage * f64::from(rage_bonus) / 100.0;

    // --- Pet attack bonus ---
    if ctx.pet_attack > 0 {
        let relevant_skill = skill_level(ctx.skills, skill_key);
        damage += f64::from(ctx.pet_attack.min(relevant_skill));
    }

    BaseDamage {
        damage,
        crit_chance,
        attack_type,
        skill_key,
    }
}

/// Apply the random skill roll and monster endurance to get final damage.
///
/// PHP: `$rzut2 = rand(1, skill_level); $stat['damage'] += $rzut2;`
///      `$stat['damage'] -= $enemy['endurance'];`
///      Clamped to `[0, enemy_hp]`.
///
/// `wand_roll`: if the player has a wand, `magic_skill * rand(1, ceil(wand_weight/20))`.
///              Pass 0 if no wand.
#[allow(clippy::cast_possible_truncation)]
pub fn finalize_damage(
    base: &BaseDamage,
    skill_roll: i32,
    wand_roll: i32,
    monster_endurance: i32,
    monster_hp: i32,
) -> i32 {
    let mut dmg = base.damage + f64::from(skill_roll);

    // Wand bonus (only for spells)
    if base.attack_type == AttackType::Spell {
        dmg += f64::from(wand_roll);
    }

    dmg -= f64::from(monster_endurance);

    let final_dmg = dmg.floor() as i32;
    final_dmg.clamp(0, monster_hp)
}

// ---------------------------------------------------------------------------
// Monster damage against player
// ---------------------------------------------------------------------------

/// Compute base monster damage against the player.
///
/// PHP: `$enemy['damage'] = $enemy['strength'] - ($player->stats['condition'][2]
///        + ($player->stats['condition'][2] * $player->checkbonus('defender')));`
/// For Warrior/Barbarian: additional `-ceil(dodge/10)`.
#[allow(clippy::cast_possible_truncation)]
pub fn monster_base_damage(
    monster_strength: i32,
    class: &Class,
    stats: &[PlayerStat],
    skills: &[PlayerSkill],
    bonuses: &[PlayerBonus],
) -> i32 {
    let condition = stat_modified(stats, "condition");
    let defender_bonus = bonus_value("defender", stats, skills, bonuses);
    let condition_reduction =
        f64::from(condition) + f64::from(condition) * f64::from(defender_bonus) / 100.0;

    let mut damage = f64::from(monster_strength) - condition_reduction;

    if matches!(class, Class::Warrior | Class::Barbarian) {
        let dodge = skill_level(skills, "dodge");
        damage -= (f64::from(dodge) / 10.0).ceil();
    }

    damage.floor() as i32
}

/// Defensive spell reduction of incoming monster damage.
///
/// PHP: `$myczarobr = ($player->stats['wisdom'][2] * $myczaro->fields['obr']);`
/// with elemental interactions, armor weight penalty, and wand bonus.
///
/// `wand_roll`: `magic_skill * rand(1, ceil(wand_weight/20))`, 0 if no wand.
#[allow(clippy::too_many_arguments)]
pub fn defensive_spell_reduction(
    def_spell: &Spell,
    stats: &[PlayerStat],
    skills: &[PlayerSkill],
    bonuses: &[PlayerBonus],
    armor: Option<&OwnedEquipment>,
    helmet: Option<&OwnedEquipment>,
    legs: Option<&OwnedEquipment>,
    shield: Option<&OwnedEquipment>,
    wand: Option<&OwnedEquipment>,
    monster_dmg_element: Element,
    wand_roll: i32,
) -> f64 {
    let wisdom = stat_modified(stats, "wisdom");
    let mut def = f64::from(wisdom) * def_spell.multiplier;

    // dspells bonus
    let dspell_bonus = bonus_value("dspells", stats, skills, bonuses);
    def += def * f64::from(dspell_bonus) / 100.0;

    // element bonus
    let elem_bonus = bonus_value(def_spell.element.to_spell_code(), stats, skills, bonuses);
    def += def * f64::from(elem_bonus) / 100.0;

    let base_def = def;

    // Elemental interaction with monster's damage type
    if monster_dmg_element != Element::None {
        if def_spell.element == monster_dmg_element {
            def *= 2.0;
        }
        if def_spell.element == defensive_spell_element_counter(monster_dmg_element) {
            def /= 2.0;
        }
    }

    // Armor weight penalty (reduces from base_def, not current def)
    for item in [armor, helmet, legs, shield].into_iter().flatten() {
        def -= base_def * (f64::from(item.agility_mod) / 100.0);
    }

    // Wand bonus
    if wand.is_some() {
        def += f64::from(wand_roll);
    }

    def.max(0.0)
}

/// Armor defense value for a specific hit location.
///
/// PHP: `$defpower = equip[hit+2][2] + (equip[hit+2][2] * checkbonus('defender'))`.
/// Elemental interactions: strong→+power, weak→-ceil(power/2).
/// Rage trade-off: defense reduced by rage bonus.
pub fn armor_defense_at_location(
    armor_piece: Option<&OwnedEquipment>,
    pet_defense: i32,
    dodge_skill: i32,
    stats: &[PlayerStat],
    skills: &[PlayerSkill],
    bonuses: &[PlayerBonus],
    monster_dmg_element: Element,
) -> f64 {
    let mut def: f64 = 0.0;

    // Pet defense
    if pet_defense > 0 {
        def += f64::from(pet_defense.min(dodge_skill));
    }

    if let Some(piece) = armor_piece {
        if piece.durability > 0 {
            let power = f64::from(piece.power);
            let defender_bonus = bonus_value("defender", stats, skills, bonuses);
            def += power + power * f64::from(defender_bonus) / 100.0;

            // Elemental armor interaction
            if piece.magic != Element::None && monster_dmg_element != Element::None {
                let strong = element_strong_against(monster_dmg_element);
                let weak = element_weak_against(monster_dmg_element);

                if piece.magic == strong {
                    def += power;
                } else if piece.magic == weak {
                    def -= (power / 2.0).ceil();
                }
            }
        }
    }

    // Rage trade-off: defense reduced
    let rage_bonus = bonus_value("rage", stats, skills, bonuses);
    def -= def * f64::from(rage_bonus) / 100.0;

    def
}

// ---------------------------------------------------------------------------
// Dodge formulas
// ---------------------------------------------------------------------------

/// Player dodge value against monster attacks.
///
/// PHP Warrior/Barbarian:
///   `$myunik = (agility - enemy_agility) + dodge_skill + ceil(dodge_skill/10)`
/// Others:
///   `$myunik = agility - enemy_agility + dodge_skill`
/// Clamped to min 1.
#[allow(clippy::cast_possible_truncation)]
pub fn player_dodge(
    class: &Class,
    player_agility: i32,
    enemy_agility: i32,
    dodge_skill: i32,
) -> i32 {
    let base = player_agility - enemy_agility + dodge_skill;
    let dodge = if matches!(class, Class::Warrior | Class::Barbarian) {
        base + (f64::from(dodge_skill) / 10.0).ceil() as i32
    } else {
        base
    };
    dodge.max(1)
}

/// Monster dodge (evasion) value against player attacks.
///
/// PHP Warrior/Barbarian:
///   `$eunik = (enemy_agility - player_agility) - (skill + ceil(skill/10))`
///   If second weapon: `$eunik -= attack_skill / 5`
/// Others:
///   `$eunik = (enemy_agility - player_agility) - skill`
///
/// For bow users, `eagle_eye_bonus` is subtracted and result is doubled.
/// Clamped to min 1.
#[allow(clippy::too_many_arguments, clippy::cast_possible_truncation)]
pub fn monster_dodge(
    class: &Class,
    player_agility: i32,
    enemy_agility: i32,
    combat_skill: i32,
    has_second_weapon: bool,
    attack_skill: i32,
    is_ranged: bool,
    eagle_eye_bonus: i32,
) -> i32 {
    let is_fighter = matches!(class, Class::Warrior | Class::Barbarian);

    let base = enemy_agility - player_agility;
    let mut dodge = if is_fighter {
        base - (combat_skill + (f64::from(combat_skill) / 10.0).ceil() as i32)
    } else {
        base - combat_skill
    };

    if has_second_weapon {
        dodge -= attack_skill / 5;
    }

    if is_ranged {
        dodge -= eagle_eye_bonus;
        dodge *= 2;
    }

    dodge.max(1)
}

/// Evaluate whether a dodge succeeds.
///
/// PHP: `$szansa = rand(1, $intDodgemax);`
///      `if ($dodge_value >= $szansa && $exhaustion <= condition && $szansa < $intDodgemax2)`
///
/// `dodge_max` is `min(relevant_agility, 100)`, clamped to min 4.
/// `dodge_max2` is `floor(dodge_max * 0.97)`.
#[allow(clippy::cast_possible_truncation)]
pub fn dodge_check(dodge_value: i32, dodge_max: i32, roll: i32, exhaustion_ok: bool) -> bool {
    let effective_max = dodge_max.clamp(4, 100);
    let cap = (f64::from(effective_max) * 0.97).floor() as i32;
    dodge_value >= roll && exhaustion_ok && roll < cap
}

/// Compute the dodge-max for player dodge (uses enemy agility).
pub fn player_dodge_max(enemy_agility: i32) -> i32 {
    if enemy_agility < 100 {
        enemy_agility.max(4)
    } else {
        100
    }
}

/// Compute the dodge-max for monster dodge (uses player agility + skill).
pub fn monster_dodge_max(player_agility: i32, combat_skill: i32) -> i32 {
    let sum = player_agility + combat_skill;
    if sum < 100 { sum.max(4) } else { 100 }
}

// ---------------------------------------------------------------------------
// Shield block
// ---------------------------------------------------------------------------

/// Shield block chance percentage.
///
/// PHP: `$intBlock = ceil(shield_power / 5); if ($intBlock > 20) $intBlock = 20;`
#[allow(clippy::cast_possible_truncation)]
pub fn shield_block_chance(shield: Option<&OwnedEquipment>) -> i32 {
    match shield {
        Some(s) if s.durability > 0 => {
            let chance = (f64::from(s.power) / 5.0).ceil() as i32;
            chance.min(20)
        }
        _ => 0,
    }
}

/// Check if a shield block succeeds.
pub fn shield_block_check(block_chance: i32, roll: i32) -> bool {
    block_chance > 0 && roll <= block_chance
}

// ---------------------------------------------------------------------------
// Critical hit
// ---------------------------------------------------------------------------

/// Check if a critical hit lands.
///
/// PHP uses two rolls:
///   `$rzut = rand(1, 1000) / 10;` (0.1–100.0)
///   `$intRoll = rand(1, 100);`
///   Critical if `crit_chance >= rzut && intRoll <= crit_chance`
///
/// Parameters match these rolls.
pub fn is_critical_hit(crit_chance: i32, roll_1000: i32, roll_100: i32) -> bool {
    let threshold = f64::from(roll_1000) / 10.0;
    f64::from(crit_chance) >= threshold && roll_100 <= crit_chance
}

// ---------------------------------------------------------------------------
// Speed / initiative (attacks per round)
// ---------------------------------------------------------------------------

/// Player attacks per round.
///
/// PHP: `$stat['attackstr'] = ceil(player_speed / enemy_speed); max 5`
#[allow(clippy::cast_possible_truncation)]
pub fn player_attacks_per_round(player_speed: i32, enemy_speed: i32) -> i32 {
    if enemy_speed <= 0 {
        return 5;
    }
    let attacks = (f64::from(player_speed) / f64::from(enemy_speed)).ceil() as i32;
    attacks.min(5)
}

/// Monster attacks per round.
///
/// PHP: `$enemy['attackstr'] = ceil(enemy_speed / player_speed); max 5`
#[allow(clippy::cast_possible_truncation)]
pub fn monster_attacks_per_round(enemy_speed: i32, player_speed: i32) -> i32 {
    if player_speed <= 0 {
        return 5;
    }
    let attacks = (f64::from(enemy_speed) / f64::from(player_speed)).ceil() as i32;
    attacks.min(5)
}

/// Turn-based combat action points per round.
///
/// Same formula as player attacks: `ceil(player_speed / enemy_speed)`, max 5.
pub fn action_points_per_round(player_speed: i32, enemy_speed: i32) -> i32 {
    player_attacks_per_round(player_speed, enemy_speed)
}

// ---------------------------------------------------------------------------
// Exhaustion
// ---------------------------------------------------------------------------

/// Fatigue cost for one attack swing.
///
/// PHP: `$zmeczenie += ($player->equip[slot][4] / 10);`
/// Slot `[4]` = `agility_mod` (weight).
pub fn attack_fatigue_cost(weapon_weight: i32) -> f64 {
    f64::from(weapon_weight) / 10.0
}

/// Fatigue cost for one dodge.
///
/// PHP: `$zmeczenie += ($player->equip[3][4] / 10);` (armor weight on dodge)
pub fn dodge_fatigue_cost(armor_weight: i32) -> f64 {
    f64::from(armor_weight) / 10.0
}

/// Whether the combatant is exhausted.
pub fn is_exhausted(exhaustion: f64, condition: i32) -> bool {
    exhaustion > f64::from(condition)
}

/// Rest recovery amount per round.
///
/// PHP: `$zmeczenie -= ($player->stats['condition'][2] / 10);`
pub fn rest_recovery(condition: i32) -> f64 {
    f64::from(condition) / 10.0
}

// ---------------------------------------------------------------------------
// Mana cost on being hit (defensive spell maintenance)
// ---------------------------------------------------------------------------

/// Mana lost when hit while maintaining a defensive spell.
///
/// PHP: `$lost_mana = ceil(spell_level / 2.5) - (magic_skill / 25); min 1`
#[allow(clippy::cast_possible_truncation)]
pub fn mana_loss_on_hit(spell_level: i32, magic_skill: i32) -> i32 {
    let base = (f64::from(spell_level) / 2.5).ceil() as i32;
    let reduction = magic_skill / 25;
    (base - reduction).max(1)
}

// ---------------------------------------------------------------------------
// Spell misfire outcomes
// ---------------------------------------------------------------------------

/// Possible outcomes when a spell fails (pech roll <= 5).
///
/// PHP: `$pechowy = rand(1,100);`
/// 1–25: lose 1 mana, 26–45: lose concentration (nothing), 46–50: lose all mana,
/// 51–55: self-damage full, 56–85: partial hit (75%/50%/25%), 86–100: partial hit + self-damage.
#[derive(Debug, Clone, Copy, PartialEq, Eq)]
pub enum SpellMisfire {
    /// Lose 1 mana point.
    LoseOneMana,
    /// Lose concentration — no effect.
    LoseConcentration,
    /// Lose all mana.
    LoseAllMana,
    /// Full damage to self.
    SelfDamage,
    /// Partial damage to enemy (fraction of base).
    PartialHit { damage_percent: i32 },
    /// Partial damage to both enemy and self.
    PartialHitAndSelfDamage { damage_percent: i32 },
}

/// Determine misfire outcome from a roll in 1..=100.
pub fn spell_misfire_outcome(roll: i32) -> SpellMisfire {
    match roll {
        1..=25 => SpellMisfire::LoseOneMana,
        26..=45 => SpellMisfire::LoseConcentration,
        46..=50 => SpellMisfire::LoseAllMana,
        51..=55 => SpellMisfire::SelfDamage,
        56..=85 => {
            let pct = if roll < 65 {
                75
            } else if roll < 75 {
                50
            } else {
                25
            };
            SpellMisfire::PartialHit {
                damage_percent: pct,
            }
        }
        _ => {
            let pct = if roll < 90 {
                25
            } else if roll < 95 {
                50
            } else {
                75
            };
            SpellMisfire::PartialHitAndSelfDamage {
                damage_percent: pct,
            }
        }
    }
}

/// Whether a spell cast attempt fails.
///
/// PHP: `$pech = floor(magic_skill - spell_level); if ($pech > 0) $pech = 0;`
///      `$pech += rand(1, 100); if ($pech > 5) -> success`
pub fn spell_fizzles(magic_skill: i32, spell_level: i32, roll: i32) -> bool {
    let mut pech = (magic_skill - spell_level).min(0);
    pech += roll;
    pech <= 5
}

// ---------------------------------------------------------------------------
// XP / reward distribution after combat
// ---------------------------------------------------------------------------

/// Compute XP shares for stats and skills after a fight.
///
/// PHP `gainability()`:
/// - Always: condition, wisdom, speed.
/// - If dodged: +agility, +dodge skill.
/// - If attacked (melee): +strength, +attack skill.
/// - If attacked (ranged): +agility, +strength, +shoot skill.
/// - If used magic: +inteli, +magic skill.
///
/// XP divided equally among all stat+skill recipients.
pub fn distribute_combat_xp(
    total_xp: i32,
    dodged: bool,
    attacked: bool,
    used_magic: bool,
    attack_type: AttackType,
) -> CombatXpShares {
    let mut stat_keys: Vec<&'static str> = vec!["condition", "wisdom", "speed"];
    let mut skill_keys: Vec<&'static str> = vec![];

    if dodged {
        if !stat_keys.contains(&"agility") {
            stat_keys.push("agility");
        }
        skill_keys.push("dodge");
    }

    if attacked {
        match attack_type {
            AttackType::Ranged => {
                if !stat_keys.contains(&"agility") {
                    stat_keys.push("agility");
                }
                if !stat_keys.contains(&"strength") {
                    stat_keys.push("strength");
                }
                skill_keys.push("shoot");
            }
            AttackType::Melee => {
                if !stat_keys.contains(&"strength") {
                    stat_keys.push("strength");
                }
                skill_keys.push("attack");
            }
            AttackType::Spell => {}
        }
    }

    if used_magic {
        if !stat_keys.contains(&"inteli") {
            stat_keys.push("inteli");
        }
        skill_keys.push("magic");
    }

    let total_recipients = stat_keys.len() + skill_keys.len();
    #[allow(clippy::cast_possible_truncation, clippy::cast_precision_loss)]
    let xp_each = if total_recipients > 0 {
        (f64::from(total_xp) / total_recipients as f64).ceil() as i32
    } else {
        0
    };

    CombatXpShares {
        stat_shares: stat_keys.into_iter().map(|k| (k, xp_each)).collect(),
        skill_shares: skill_keys.into_iter().map(|k| (k, xp_each)).collect(),
    }
}

/// XP amounts to award to each stat and skill after combat.
#[derive(Debug, Clone)]
pub struct CombatXpShares {
    pub stat_shares: Vec<(&'static str, i32)>,
    pub skill_shares: Vec<(&'static str, i32)>,
}

/// XP gained from escaping a fight.
///
/// PHP: `$expgain = ceil((speed + endurance + agility + strength) / 100);`
#[allow(clippy::cast_possible_truncation)]
pub fn escape_xp(
    monster_speed: i32,
    monster_endurance: i32,
    monster_agility: i32,
    monster_strength: i32,
) -> i32 {
    ((f64::from(monster_speed)
        + f64::from(monster_endurance)
        + f64::from(monster_agility)
        + f64::from(monster_strength))
        / 100.0)
        .ceil() as i32
}

/// Escape success check.
///
/// PHP: `$chance = (speed + perception + rand1_100) - (enemy_speed + rand1_100)`
/// Success if `chance > 0`.
pub fn escape_chance(
    player_speed: i32,
    perception_skill: i32,
    player_roll: i32,
    enemy_speed: i32,
    enemy_roll: i32,
) -> bool {
    let chance = (player_speed + perception_skill + player_roll) - (enemy_speed + enemy_roll);
    chance > 0
}

// ---------------------------------------------------------------------------
// Energy cost
// ---------------------------------------------------------------------------

/// Energy cost for arena-style combat.
///
/// PHP: `$intLostenergy = ($_POST['razy'] * floor(1 + ($enemy['level'] / 20)));`
#[allow(clippy::cast_possible_truncation)]
pub fn arena_energy_cost(fight_count: i32, monster_level: i32) -> i32 {
    fight_count * (1 + monster_level / 20)
}

/// Energy cost for exploration combat (always 1).
pub fn explore_energy_cost() -> i32 {
    1
}

// ---------------------------------------------------------------------------
// Resurrection
// ---------------------------------------------------------------------------

/// Gold cost to resurrect.
///
/// PHP resurect.php: `$crneed = (50 * $player->stats['condition'][2]);`
pub fn resurrection_gold_cost(condition: i32) -> i64 {
    i64::from(condition) * 50
}

/// HP per condition for resurrection penalty (same table as progression).
///
/// When a stat level is lost on death, `max_hp` decreases by
/// `hp_per_condition_for_race + hp_per_condition_for_class`.
///
/// PHP resurect.php uses a hardcoded array:
/// ```php
/// $arrHp = array('Barbarzyńca' => 6, 'Wojownik' => 5, 'Złodziej' => 4,
///                'Mag' => 3, 'Rzemieślnik' => 2,
///                'Człowiek' => 4, 'Elf' => 3, 'Krasnolud' => 5,
///                'Jaszczuroczłek' => 5, 'Hobbit' => 4, 'Gnom' => 2);
/// ```
pub fn resurrection_hp_per_condition(race: &Race, class: &Class) -> i32 {
    let race_hp = match race {
        Race::Human | Race::Hobbit => 4,
        Race::Elf => 3,
        Race::Dwarf | Race::Lizardman => 5,
        Race::Gnome => 2,
    };
    let class_hp = match class {
        Class::Barbarian => 6,
        Class::Warrior => 5,
        Class::Thief => 4,
        Class::Mage => 3,
        Class::Craftsman => 2,
    };
    race_hp + class_hp
}

// ---------------------------------------------------------------------------
// Turn-based combat modifiers
// ---------------------------------------------------------------------------

/// Attack stance modifier for turn-based combat.
#[derive(Debug, Clone, Copy, PartialEq, Eq)]
pub enum AttackStance {
    /// Normal attack (no modifier).
    Normal,
    /// Aggressive: +50% damage, -10% dodge, -50% player dodge.
    Aggressive,
    /// Berserker (Warrior/Barbarian): 2× damage, 0 dodge, costs 2 AP.
    Berserker,
    /// Defensive (Warrior only): -50% damage, +10% monster dodge, +50% player dodge.
    Defensive,
}

/// Apply attack stance to base damage.
pub fn stance_damage_modifier(damage: f64, stance: AttackStance) -> f64 {
    match stance {
        AttackStance::Normal => damage,
        AttackStance::Aggressive => damage + damage / 2.0,
        AttackStance::Berserker => damage * 2.0,
        AttackStance::Defensive => damage - damage / 2.0,
    }
}

/// Apply stance to monster dodge (eunik) for turn-based.
pub fn stance_monster_dodge_modifier(eunik: i32, stance: AttackStance) -> i32 {
    match stance {
        AttackStance::Normal | AttackStance::Berserker => eunik,
        AttackStance::Aggressive => eunik - eunik / 10,
        AttackStance::Defensive => eunik + eunik / 10,
    }
}

/// Apply stance to player dodge (myunik) adjustment (done _after_ monster attacks).
///
/// PHP: defensive → `$myunik += ($myunik / 2)`, aggressive → `$myunik /= 2`,
///      berserker → `$myunik = 0`.
pub fn stance_player_dodge_modifier(myunik: i32, stance: AttackStance) -> i32 {
    match stance {
        AttackStance::Normal => myunik,
        AttackStance::Defensive => myunik + myunik / 2,
        AttackStance::Aggressive => myunik / 2,
        AttackStance::Berserker => 0,
    }
}

/// AP cost for a stance.
pub fn stance_ap_cost(stance: AttackStance) -> i32 {
    match stance {
        AttackStance::Normal | AttackStance::Aggressive | AttackStance::Defensive => 1,
        AttackStance::Berserker => 2,
    }
}

// ---------------------------------------------------------------------------
// Pet survival
// ---------------------------------------------------------------------------

/// Whether the player's pet survives after combat.
///
/// PHP `checkpet()`: on loss, pet always dies. On win, 1% death chance.
pub fn pet_survives(won: bool, roll_1_100: i32) -> bool {
    if won {
        roll_1_100 > 1 // survives unless roll == 1
    } else {
        false
    }
}

// ---------------------------------------------------------------------------
// Monster damage roll
// ---------------------------------------------------------------------------

/// Add random roll to monster damage.
///
/// PHP: `$rzut1 = rand(0, enemy_level); $enemy['damage'] += $rzut1;`
/// Clamped to min 1.
pub fn monster_damage_with_roll(base_damage: i32, level_roll: i32) -> i32 {
    (base_damage + level_roll).max(1)
}

// ---------------------------------------------------------------------------
// Tests
// ---------------------------------------------------------------------------

#[cfg(test)]
mod tests {
    use super::*;
    use crate::item::{
        Element, EquipmentStatus, EquipmentType, OwnedEquipment, PoisonType, Spell, SpellStatus,
        SpellType,
    };
    use crate::player::skills::PlayerSkill;
    use crate::player::stats::PlayerStat;
    use crate::player::{Class, Race};

    // --- Helpers ---

    fn make_stat(key: &str, modified: i32) -> PlayerStat {
        PlayerStat {
            stat_key: key.to_owned(),
            label: String::new(),
            base: 50,
            trained: modified,
            modified,
            xp: 0,
        }
    }

    fn make_skill(key: &str, level: i32) -> PlayerSkill {
        PlayerSkill {
            skill_key: key.to_owned(),
            label: String::new(),
            level,
            xp: 0,
        }
    }

    fn make_weapon(power: i32, weight: i32, durability: i32) -> OwnedEquipment {
        OwnedEquipment {
            id: 1,
            owner_id: 1,
            name: "Test Sword".into(),
            power,
            status: EquipmentStatus::Equipped,
            equipment_type: EquipmentType::Weapon,
            cost: 100,
            min_level: 1,
            agility_mod: weight,
            durability,
            speed_mod: 0,
            max_durability: durability,
            magic: Element::None,
            poison: 0,
            amount: 1,
            two_handed: false,
            poison_type: PoisonType::None,
            repair_cost: 10,
            location: String::new(),
        }
    }

    fn make_bow(power: i32, weight: i32) -> OwnedEquipment {
        let mut w = make_weapon(power, weight, 50);
        w.equipment_type = EquipmentType::Bow;
        w.name = "Test Bow".into();
        w
    }

    fn make_arrows(power: i32, durability: i32) -> OwnedEquipment {
        let mut w = make_weapon(power, 0, durability);
        w.equipment_type = EquipmentType::Arrows;
        w.name = "Test Arrows".into();
        w.id = 2;
        w
    }

    fn make_armor(power: i32, weight: i32, durability: i32) -> OwnedEquipment {
        let mut w = make_weapon(power, weight, durability);
        w.equipment_type = EquipmentType::Armor;
        w.name = "Test Armor".into();
        w
    }

    fn make_shield(power: i32, weight: i32, durability: i32) -> OwnedEquipment {
        let mut w = make_weapon(power, weight, durability);
        w.equipment_type = EquipmentType::Shield;
        w.name = "Test Shield".into();
        w
    }

    fn make_spell(multiplier: f64, element: Element) -> Spell {
        Spell {
            id: 1,
            name: "Test Spell".into(),
            owner_id: 1,
            cost: 100,
            level: 10,
            spell_type: SpellType::Battle,
            multiplier,
            status: SpellStatus::Active,
            element,
        }
    }

    fn make_def_spell(multiplier: f64, element: Element) -> Spell {
        Spell {
            id: 2,
            name: "Test Defense".into(),
            owner_id: 1,
            cost: 100,
            level: 10,
            spell_type: SpellType::Defense,
            multiplier,
            status: SpellStatus::Active,
            element,
        }
    }

    fn base_stats() -> Vec<PlayerStat> {
        vec![
            make_stat("strength", 20),
            make_stat("agility", 15),
            make_stat("condition", 18),
            make_stat("speed", 12),
            make_stat("inteli", 10),
            make_stat("wisdom", 14),
        ]
    }

    fn base_skills() -> Vec<PlayerSkill> {
        vec![
            make_skill("attack", 25),
            make_skill("shoot", 20),
            make_skill("magic", 15),
            make_skill("dodge", 18),
            make_skill("perception", 10),
        ]
    }

    // --- Hit Location ---

    #[test]
    fn hit_location_ranges() {
        assert_eq!(HitLocation::from_roll(1), HitLocation::Head);
        assert_eq!(HitLocation::from_roll(10), HitLocation::Head);
        assert_eq!(HitLocation::from_roll(11), HitLocation::Body);
        assert_eq!(HitLocation::from_roll(70), HitLocation::Body);
        assert_eq!(HitLocation::from_roll(71), HitLocation::Legs);
        assert_eq!(HitLocation::from_roll(85), HitLocation::Legs);
        assert_eq!(HitLocation::from_roll(86), HitLocation::Arms);
        assert_eq!(HitLocation::from_roll(100), HitLocation::Arms);
    }

    // --- Element interactions ---

    #[test]
    fn defensive_spell_counter_elements() {
        assert_eq!(
            defensive_spell_element_counter(Element::Water),
            Element::Fire
        );
        assert_eq!(
            defensive_spell_element_counter(Element::Fire),
            Element::Wind
        );
        assert_eq!(
            defensive_spell_element_counter(Element::Wind),
            Element::Earth
        );
        assert_eq!(
            defensive_spell_element_counter(Element::Earth),
            Element::Water
        );
    }

    // --- Weapon power ---

    #[test]
    fn effective_weapon_power_no_modifiers() {
        let weapon = make_weapon(50, 10, 30);
        assert!((effective_weapon_power(&weapon, MonsterResistance::none()) - 50.0).abs() < 0.01);
    }

    #[test]
    fn effective_weapon_power_dynallca() {
        let mut weapon = make_weapon(50, 10, 30);
        weapon.poison_type = PoisonType::Dynallca;
        weapon.poison = 15;
        assert!((effective_weapon_power(&weapon, MonsterResistance::none()) - 65.0).abs() < 0.01);
    }

    #[test]
    fn effective_weapon_power_elemental_resistance() {
        let mut weapon = make_weapon(100, 10, 30);
        weapon.magic = Element::Fire;
        let resist = MonsterResistance {
            element: Element::Fire,
            strength: ResistanceStrength::Strong,
        };
        // 100 - (100 * 0.5) = 50
        assert!((effective_weapon_power(&weapon, resist) - 50.0).abs() < 0.01);
    }

    // --- Player base damage ---

    #[test]
    fn melee_damage_warrior() {
        let stats = base_stats();
        let skills = base_skills();
        let weapon = make_weapon(30, 10, 50);
        let ctx = DamageContext {
            class: &Class::Warrior,
            stats: &stats,
            skills: &skills,
            bonuses: &[],
            weapon: Some(&weapon),
            second_weapon: None,
            bow: None,
            arrows: None,
            wand: None,
            helmet: None,
            armor: None,
            legs: None,
            shield: None,
            attack_spell: None,
            pet_attack: 0,
            pet_defense: 0,
            monster_resistance: MonsterResistance::none(),
        };
        let result = player_base_damage(&ctx);
        // Warrior: strength(20) + weapon(30) + attack(25) = 75
        assert_eq!(result.attack_type, AttackType::Melee);
        assert!((result.damage - 75.0).abs() < 0.01);
        assert_eq!(result.crit_chance, 6); // min(25, 6) = 6
    }

    #[test]
    fn melee_damage_mage_no_skill_bonus() {
        let stats = base_stats();
        let skills = base_skills();
        let weapon = make_weapon(30, 10, 50);
        let ctx = DamageContext {
            class: &Class::Mage,
            stats: &stats,
            skills: &skills,
            bonuses: &[],
            weapon: Some(&weapon),
            second_weapon: None,
            bow: None,
            arrows: None,
            wand: None,
            helmet: None,
            armor: None,
            legs: None,
            shield: None,
            attack_spell: None,
            pet_attack: 0,
            pet_defense: 0,
            monster_resistance: MonsterResistance::none(),
        };
        let result = player_base_damage(&ctx);
        // Mage: strength(20) + weapon(30) = 50 (no attack skill bonus)
        assert!((result.damage - 50.0).abs() < 0.01);
    }

    #[test]
    fn ranged_damage_warrior() {
        let stats = base_stats();
        let skills = base_skills();
        let bow = make_bow(25, 8);
        let arrows = make_arrows(10, 20);
        let ctx = DamageContext {
            class: &Class::Warrior,
            stats: &stats,
            skills: &skills,
            bonuses: &[],
            weapon: None,
            second_weapon: None,
            bow: Some(&bow),
            arrows: Some(&arrows),
            wand: None,
            helmet: None,
            armor: None,
            legs: None,
            shield: None,
            attack_spell: None,
            pet_attack: 0,
            pet_defense: 0,
            monster_resistance: MonsterResistance::none(),
        };
        let result = player_base_damage(&ctx);
        // Warrior ranged: (str/2 + agi/2) + (bow + arrows) + ceil(shoot/10)
        // = (10 + 7.5) + (25 + 10) + ceil(20/10) = 17.5 + 35 + 2 = 54.5
        assert_eq!(result.attack_type, AttackType::Ranged);
        assert!((result.damage - 54.5).abs() < 0.01);
    }

    #[test]
    fn spell_damage_basic() {
        let stats = base_stats();
        let skills = base_skills();
        let spell = make_spell(2.5, Element::Fire);
        let ctx = DamageContext {
            class: &Class::Mage,
            stats: &stats,
            skills: &skills,
            bonuses: &[],
            weapon: None,
            second_weapon: None,
            bow: None,
            arrows: None,
            wand: None,
            helmet: None,
            armor: None,
            legs: None,
            shield: None,
            attack_spell: Some(&spell),
            pet_attack: 0,
            pet_defense: 0,
            monster_resistance: MonsterResistance::none(),
        };
        let result = player_base_damage(&ctx);
        // spell_multiplier(2.5) * inteli(10) = 25
        assert_eq!(result.attack_type, AttackType::Spell);
        assert!((result.damage - 25.0).abs() < 0.01);
    }

    #[test]
    fn spell_damage_with_armor_penalty() {
        let stats = base_stats();
        let skills = base_skills();
        let spell = make_spell(2.0, Element::Fire);
        let armor = make_armor(50, 20, 50); // weight=20 → 20% penalty
        let ctx = DamageContext {
            class: &Class::Mage,
            stats: &stats,
            skills: &skills,
            bonuses: &[],
            weapon: None,
            second_weapon: None,
            bow: None,
            arrows: None,
            wand: None,
            helmet: None,
            armor: Some(&armor),
            legs: None,
            shield: None,
            attack_spell: Some(&spell),
            pet_attack: 0,
            pet_defense: 0,
            monster_resistance: MonsterResistance::none(),
        };
        let result = player_base_damage(&ctx);
        // 2.0 * 10 = 20 base, minus 20% armor penalty = 20 - 4 = 16
        assert!((result.damage - 16.0).abs() < 0.01);
    }

    #[test]
    fn craftsman_penalty_applied() {
        let stats = base_stats();
        let skills = base_skills();
        let weapon = make_weapon(30, 10, 50);
        let ctx = DamageContext {
            class: &Class::Craftsman,
            stats: &stats,
            skills: &skills,
            bonuses: &[],
            weapon: Some(&weapon),
            second_weapon: None,
            bow: None,
            arrows: None,
            wand: None,
            helmet: None,
            armor: None,
            legs: None,
            shield: None,
            attack_spell: None,
            pet_attack: 0,
            pet_defense: 0,
            monster_resistance: MonsterResistance::none(),
        };
        let result = player_base_damage(&ctx);
        // Craftsman: (str 20 + weapon 30) = 50, minus 25% = 37.5
        assert!((result.damage - 37.5).abs() < 0.01);
    }

    #[test]
    fn finalize_damage_clamps() {
        let base = BaseDamage {
            damage: 100.0,
            crit_chance: 6,
            attack_type: AttackType::Melee,
            skill_key: "attack",
        };
        // With skill roll 10, endurance 50, HP 80: 100+10-50=60, clamped to min(60,80)=60
        assert_eq!(finalize_damage(&base, 10, 0, 50, 80), 60);
        // With endurance 200: 100+10-200=-90 → clamped to 0
        assert_eq!(finalize_damage(&base, 10, 0, 200, 80), 0);
        // With low HP 30: 100+10-0=110 → clamped to 30
        assert_eq!(finalize_damage(&base, 10, 0, 0, 30), 30);
    }

    // --- Monster damage ---

    #[test]
    fn monster_base_damage_basic() {
        let stats = base_stats();
        let skills = base_skills();
        // monster_strength 40, condition 18
        // 40 - (18 + 0) = 22
        let dmg = monster_base_damage(40, &Class::Mage, &stats, &skills, &[]);
        assert_eq!(dmg, 22);
    }

    #[test]
    fn monster_base_damage_warrior_dodge_reduction() {
        let stats = base_stats();
        let skills = base_skills();
        // dodge_skill = 18, ceil(18/10) = 2
        // 40 - 18 - 2 = 20
        let dmg = monster_base_damage(40, &Class::Warrior, &stats, &skills, &[]);
        assert_eq!(dmg, 20);
    }

    // --- Dodge ---

    #[test]
    fn player_dodge_warrior() {
        // (15 - 20) + 18 + ceil(18/10) = -5 + 18 + 2 = 15
        assert_eq!(player_dodge(&Class::Warrior, 15, 20, 18), 15);
    }

    #[test]
    fn player_dodge_mage_clamped() {
        // (5 - 25) + 3 = -17 → clamped to 1
        assert_eq!(player_dodge(&Class::Mage, 5, 25, 3), 1);
    }

    #[test]
    fn monster_dodge_basic() {
        // Mage: (20 - 15) - 15 = -10 → clamped to 1
        let d = monster_dodge(&Class::Mage, 15, 20, 15, false, 0, false, 0);
        assert_eq!(d, 1);
    }

    #[test]
    fn monster_dodge_ranged_doubled() {
        // Mage ranged: (30 - 15) - 10 = 5, eagle-eye 2 → (5-2)*2 = 6
        let d = monster_dodge(&Class::Mage, 15, 30, 10, false, 0, true, 2);
        assert_eq!(d, 6);
    }

    #[test]
    fn dodge_check_succeeds() {
        // dodge_value 15, max 50, roll 10, not exhausted → 15 >= 10 && true && 10 < 48
        assert!(dodge_check(15, 50, 10, true));
    }

    #[test]
    fn dodge_check_fails_exhausted() {
        assert!(!dodge_check(15, 50, 10, false));
    }

    #[test]
    fn dodge_check_fails_near_cap() {
        // max=50, cap=48, roll=49 → roll >= cap → false
        assert!(!dodge_check(100, 50, 49, true));
    }

    // --- Shield block ---

    #[test]
    fn shield_block_chance_basic() {
        let shield = make_shield(50, 10, 30);
        // ceil(50/5) = 10
        assert_eq!(shield_block_chance(Some(&shield)), 10);
    }

    #[test]
    fn shield_block_chance_capped() {
        let shield = make_shield(150, 10, 30);
        // ceil(150/5) = 30 → capped to 20
        assert_eq!(shield_block_chance(Some(&shield)), 20);
    }

    #[test]
    fn shield_block_no_durability() {
        let mut shield = make_shield(50, 10, 30);
        shield.durability = 0;
        assert_eq!(shield_block_chance(Some(&shield)), 0);
    }

    // --- Critical hit ---

    #[test]
    fn critical_hit_lands() {
        // crit=6, roll_1000=50 (50/10=5.0 <= 6), roll_100=3 (<= 6)
        assert!(is_critical_hit(6, 50, 3));
    }

    #[test]
    fn critical_hit_fails_first_roll() {
        // crit=6, roll_1000=70 (70/10=7.0 > 6) → false
        assert!(!is_critical_hit(6, 70, 3));
    }

    #[test]
    fn critical_hit_fails_second_roll() {
        // crit=6, roll_1000=50 (5.0 <= 6), roll_100=8 (> 6) → false
        assert!(!is_critical_hit(6, 50, 8));
    }

    // --- Speed / initiative ---

    #[test]
    fn attacks_per_round_basic() {
        // ceil(12 / 5) = 3
        assert_eq!(player_attacks_per_round(12, 5), 3);
        // ceil(5 / 12) = 1
        assert_eq!(monster_attacks_per_round(5, 12), 1);
    }

    #[test]
    fn attacks_per_round_capped() {
        // ceil(100/1) = 100 → capped to 5
        assert_eq!(player_attacks_per_round(100, 1), 5);
    }

    // --- Exhaustion ---

    #[test]
    fn exhaustion_check() {
        assert!(!is_exhausted(17.0, 18));
        assert!(is_exhausted(19.0, 18));
    }

    #[test]
    fn rest_recovery_value() {
        assert!((rest_recovery(18) - 1.8).abs() < 0.01);
    }

    // --- Mana loss ---

    #[test]
    fn mana_loss_basic() {
        // ceil(10/2.5) - (15/25) = 4 - 0 = 4
        assert_eq!(mana_loss_on_hit(10, 15), 4);
    }

    #[test]
    fn mana_loss_min_one() {
        // ceil(1/2.5) - (100/25) = 1 - 4 = -3 → max(1)
        assert_eq!(mana_loss_on_hit(1, 100), 1);
    }

    // --- Spell misfire ---

    #[test]
    fn spell_fizzle_check() {
        // magic 15, spell 20, roll 8: pech = min(15-20, 0) + 8 = -5 + 8 = 3 ≤ 5 → fizzles
        assert!(spell_fizzles(15, 20, 8));
        // magic 25, spell 20, roll 8: pech = min(5, 0) + 8 = 0 + 8 = 8 > 5 → success
        assert!(!spell_fizzles(25, 20, 8));
    }

    #[test]
    fn spell_misfire_outcomes() {
        assert_eq!(spell_misfire_outcome(1), SpellMisfire::LoseOneMana);
        assert_eq!(spell_misfire_outcome(25), SpellMisfire::LoseOneMana);
        assert_eq!(spell_misfire_outcome(30), SpellMisfire::LoseConcentration);
        assert_eq!(spell_misfire_outcome(48), SpellMisfire::LoseAllMana);
        assert_eq!(spell_misfire_outcome(53), SpellMisfire::SelfDamage);
        assert_eq!(
            spell_misfire_outcome(60),
            SpellMisfire::PartialHit { damage_percent: 75 }
        );
        assert_eq!(
            spell_misfire_outcome(70),
            SpellMisfire::PartialHit { damage_percent: 50 }
        );
        assert_eq!(
            spell_misfire_outcome(80),
            SpellMisfire::PartialHit { damage_percent: 25 }
        );
        assert_eq!(
            spell_misfire_outcome(88),
            SpellMisfire::PartialHitAndSelfDamage { damage_percent: 25 }
        );
        assert_eq!(
            spell_misfire_outcome(92),
            SpellMisfire::PartialHitAndSelfDamage { damage_percent: 50 }
        );
        assert_eq!(
            spell_misfire_outcome(98),
            SpellMisfire::PartialHitAndSelfDamage { damage_percent: 75 }
        );
    }

    // --- XP distribution ---

    #[test]
    fn xp_distribution_melee_with_dodge() {
        let shares = distribute_combat_xp(100, true, true, false, AttackType::Melee);
        // Stats: condition, wisdom, speed, agility, strength = 5
        // Skills: dodge, attack = 2
        // Total = 7, each = ceil(100/7) = 15
        assert_eq!(shares.stat_shares.len(), 5);
        assert_eq!(shares.skill_shares.len(), 2);
        for (_, xp) in &shares.stat_shares {
            assert_eq!(*xp, 15);
        }
    }

    #[test]
    fn xp_distribution_magic_only() {
        let shares = distribute_combat_xp(100, false, false, true, AttackType::Spell);
        // Stats: condition, wisdom, speed, inteli = 4
        // Skills: magic = 1
        // Total = 5, each = ceil(100/5) = 20
        assert_eq!(shares.stat_shares.len(), 4);
        assert_eq!(shares.skill_shares.len(), 1);
        assert_eq!(shares.skill_shares[0].0, "magic");
        for (_, xp) in &shares.stat_shares {
            assert_eq!(*xp, 20);
        }
    }

    // --- Escape ---

    #[test]
    fn escape_xp_calc() {
        // ceil((10 + 20 + 15 + 25) / 100) = ceil(70/100) = 1
        assert_eq!(escape_xp(10, 20, 15, 25), 1);
        // ceil((100 + 100 + 100 + 100) / 100) = 4
        assert_eq!(escape_xp(100, 100, 100, 100), 4);
    }

    #[test]
    fn escape_chance_basic() {
        // (12 + 10 + 50) - (15 + 60) = 72 - 75 = -3 → false
        assert!(!escape_chance(12, 10, 50, 15, 60));
        // (12 + 10 + 80) - (15 + 60) = 102 - 75 = 27 → true
        assert!(escape_chance(12, 10, 80, 15, 60));
    }

    // --- Energy cost ---

    #[test]
    fn arena_energy_cost_calc() {
        // 3 * (1 + 40/20) = 3 * 3 = 9
        assert_eq!(arena_energy_cost(3, 40), 9);
    }

    // --- Resurrection ---

    #[test]
    fn resurrection_cost() {
        assert_eq!(resurrection_gold_cost(18), 900);
    }

    #[test]
    fn resurrection_hp_per_condition_values() {
        assert_eq!(
            resurrection_hp_per_condition(&Race::Human, &Class::Warrior),
            9
        );
        assert_eq!(
            resurrection_hp_per_condition(&Race::Gnome, &Class::Craftsman),
            4
        );
        assert_eq!(
            resurrection_hp_per_condition(&Race::Dwarf, &Class::Barbarian),
            11
        );
    }

    // --- Stance modifiers ---

    #[test]
    fn stance_damage_modifiers() {
        assert!((stance_damage_modifier(100.0, AttackStance::Normal) - 100.0).abs() < 0.01);
        assert!((stance_damage_modifier(100.0, AttackStance::Aggressive) - 150.0).abs() < 0.01);
        assert!((stance_damage_modifier(100.0, AttackStance::Berserker) - 200.0).abs() < 0.01);
        assert!((stance_damage_modifier(100.0, AttackStance::Defensive) - 50.0).abs() < 0.01);
    }

    #[test]
    fn stance_dodge_modifiers() {
        assert_eq!(
            stance_player_dodge_modifier(20, AttackStance::Defensive),
            30
        );
        assert_eq!(
            stance_player_dodge_modifier(20, AttackStance::Aggressive),
            10
        );
        assert_eq!(stance_player_dodge_modifier(20, AttackStance::Berserker), 0);
    }

    // --- Pet survival ---

    #[test]
    fn pet_survival() {
        // Won and roll != 1 → lives
        assert!(pet_survives(true, 50));
        // Won and roll == 1 → dies
        assert!(!pet_survives(true, 1));
        // Lost → always dies
        assert!(!pet_survives(false, 50));
    }

    // --- Defensive spell ---

    #[test]
    fn defensive_spell_reduction_basic() {
        let stats = base_stats();
        let skills = base_skills();
        let spell = make_def_spell(2.0, Element::Water);
        // wisdom(14) * 2.0 = 28, no bonuses, no armor, no elemental interaction
        let def = defensive_spell_reduction(
            &spell,
            &stats,
            &skills,
            &[],
            None,
            None,
            None,
            None,
            None,
            Element::None,
            0,
        );
        assert!((def - 28.0).abs() < 0.01);
    }

    #[test]
    fn defensive_spell_elemental_match_doubles() {
        let stats = base_stats();
        let skills = base_skills();
        let spell = make_def_spell(2.0, Element::Fire);
        // wisdom(14) * 2.0 = 28, monster deals fire → match → 28 * 2 = 56
        let def = defensive_spell_reduction(
            &spell,
            &stats,
            &skills,
            &[],
            None,
            None,
            None,
            None,
            None,
            Element::Fire,
            0,
        );
        assert!((def - 56.0).abs() < 0.01);
    }

    #[test]
    fn defensive_spell_elemental_counter_halves() {
        let stats = base_stats();
        let skills = base_skills();
        // spell = fire, monster deals wind → counter(wind)=earth, fire != earth → no match
        // BUT: counter(wind) = earth. fire != earth → no halving.
        // Actually: counter(wind) = earth. Spell is fire. Does fire == counter(wind)=earth? No.
        // Let's use: spell=fire, monster=water → counter(water)=fire → fire==fire → halved
        let spell = make_def_spell(2.0, Element::Fire);
        // wisdom(14) * 2.0 = 28. fire != water (no double). counter(water)=fire, fire==fire → /2 = 14
        let def = defensive_spell_reduction(
            &spell,
            &stats,
            &skills,
            &[],
            None,
            None,
            None,
            None,
            None,
            Element::Water,
            0,
        );
        assert!((def - 14.0).abs() < 0.01);
    }

    // --- Armor defense ---

    #[test]
    fn armor_defense_basic() {
        let stats = base_stats();
        let skills = base_skills();
        let armor = make_armor(30, 15, 50);
        // power=30, no bonus, no element → 30.0
        let def =
            armor_defense_at_location(Some(&armor), 0, 18, &stats, &skills, &[], Element::None);
        assert!((def - 30.0).abs() < 0.01);
    }

    #[test]
    fn armor_defense_with_pet() {
        let stats = base_stats();
        let skills = base_skills();
        // pet_defense=10, dodge_skill=18 → min(10,18) = 10
        // No armor piece → just pet
        let def = armor_defense_at_location(None, 10, 18, &stats, &skills, &[], Element::None);
        assert!((def - 10.0).abs() < 0.01);
    }

    // --- Monster damage with roll ---

    #[test]
    fn monster_damage_roll() {
        assert_eq!(monster_damage_with_roll(10, 5), 15);
        assert_eq!(monster_damage_with_roll(-5, 3), 1); // clamped to 1
    }
}
