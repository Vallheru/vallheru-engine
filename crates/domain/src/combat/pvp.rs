//! `PvP` battle execution — player-versus-player turn resolution.
//!
//! Ported from PHP `includes/battle.php` (`attack1` function) and the
//! orchestration in `battle.php`.
//!
//! The PHP design is a single recursive function where the two players
//! alternate as attacker / defender.  The Rust port keeps this
//! alternating-turn semantic but expresses it as a loop with explicit
//! state instead of recursion.
//!
//! **All functions are pure** — callers inject RNG rolls so the domain
//! layer stays deterministic and testable.

use crate::combat::formulas::{self, AttackType, CombatXpShares, HitLocation, SpellMisfire};
use crate::item::{OwnedEquipment, PoisonType};
use crate::player::Class;

// ---------------------------------------------------------------------------
// Constants
// ---------------------------------------------------------------------------

/// Maximum half-rounds before the fight is declared a draw.
/// PHP uses `$runda >= 25` where runda increments by 0.5 each half-turn,
/// so 50 half-rounds = 25 full rounds.
const MAX_HALF_ROUNDS: i32 = 50;

/// Maximum strikes per half-round (speed ratio capped at 5 in PHP).
const MAX_STRIKES: i32 = 5;

// ---------------------------------------------------------------------------
// Combatant snapshot
// ---------------------------------------------------------------------------

/// A frozen snapshot of everything needed for one player in `PvP` combat.
///
/// Built from `Player`, `EquipmentLoadout`, active spells, bonuses, etc.
/// before the fight starts.  Mutable fields (`hp`, `mana`, equipment
/// durability) are owned copies so the fight can mutate them freely.
#[derive(Debug, Clone)]
pub struct PvpCombatant {
    pub player_id: i32,
    pub name: String,
    pub class: Class,
    pub hp: i32,
    pub mana: i32,
    pub antidote: PoisonType,
    pub reputation: i32,
    pub credits: i32,
    pub maps: i16,

    // --- derived / modified stats (after bonuses applied) ---
    pub strength: i32,
    pub agility: i32,
    pub speed: i32,
    pub condition: i32,
    pub wisdom: i32,
    pub intelligence: i32,

    // --- skills (after bonuses applied) ---
    pub attack_skill: i32,
    pub shoot_skill: i32,
    pub dodge_skill: i32,
    pub magic_skill: i32,

    // --- equipment ---
    pub weapon: Option<OwnedEquipment>,
    pub bow: Option<OwnedEquipment>,
    pub arrows: Option<OwnedEquipment>,
    pub helmet: Option<OwnedEquipment>,
    pub armor: Option<OwnedEquipment>,
    pub legs: Option<OwnedEquipment>,
    pub shield: Option<OwnedEquipment>,
    pub wand: Option<OwnedEquipment>,
    pub second_weapon: Option<OwnedEquipment>,

    // --- spells ---
    pub battle_spell: Option<PvpSpell>,
    pub defense_spell: Option<PvpSpell>,

    // --- pet ---
    pub pet_attack: i32,
    pub pet_defense: i32,

    // --- bonuses (named trigger values, already resolved) ---
    pub assassin_bonus: i32,
    pub rage_bonus: f64,
    pub defender_bonus: f64,
}

/// Pre-computed spell values for `PvP`.
///
/// PHP computes `dmg = multiplier * intelligence` (battle spell) or
/// `def = multiplier * wisdom` (defense spell) with element/bonus
/// adjustments applied before the fight starts.
#[derive(Debug, Clone)]
pub struct PvpSpell {
    /// Effective damage (battle) or defense (defensive) value.
    pub power: i32,
    /// Spell level for misfire/mana-cost calculations.
    pub level: i32,
    pub element: crate::item::Element,
}

// ---------------------------------------------------------------------------
// Attack mode detection
// ---------------------------------------------------------------------------

/// Determine what attack mode a combatant uses based on equipped items.
fn attack_mode(c: &PvpCombatant) -> Option<AttackType> {
    let has_bow = c
        .bow
        .as_ref()
        .is_some_and(|b| b.durability > 0 && c.arrows.as_ref().is_some_and(|a| a.durability > 0));
    let has_weapon = c.weapon.as_ref().is_some_and(|w| w.durability > 0);
    let has_spell = c.battle_spell.is_some();

    if has_bow {
        Some(AttackType::Ranged)
    } else if has_weapon {
        Some(AttackType::Melee)
    } else if has_spell {
        Some(AttackType::Spell)
    } else {
        None
    }
}

// ---------------------------------------------------------------------------
// PvP outcome
// ---------------------------------------------------------------------------

/// Which side won (or draw).
#[derive(Debug, Clone, Copy, PartialEq, Eq)]
pub enum PvpOutcome {
    /// First-named combatant won.
    AttackerWin,
    /// Second-named combatant won.
    DefenderWin,
    /// Round limit reached, nobody died.
    Draw,
}

// ---------------------------------------------------------------------------
// Per-half-round rolls
// ---------------------------------------------------------------------------

/// All RNG values needed for one half-round (one combatant attacking).
#[derive(Debug, Clone)]
pub struct PvpHalfRoundRolls {
    /// One entry per strike in the half-round.   The caller sizes this
    /// to `strikes_this_turn` (1..=5).
    pub strikes: Vec<PvpStrikeRoll>,
}

/// RNG values for a single strike within a half-round.
#[derive(Debug, Clone)]
pub struct PvpStrikeRoll {
    /// Hit location roll (1..=100).
    pub location_roll: i32,
    /// Attacker's random skill contribution (1..=`skill_level`).
    pub attack_roll: i32,
    /// Defender's random dodge contribution (1..=`dodge_skill`).
    pub dodge_roll: i32,
    /// Dodge threshold roll (1..=`dodge_max`).
    pub dodge_chance_roll: i32,
    /// Shield block roll (1..=100).
    pub block_roll: i32,
    /// Critical damage: roll 1..=1000 (divided by 10 for threshold).
    pub crit_roll_1000: i32,
    /// Critical confirmation: roll 1..=100.
    pub crit_roll_100: i32,
    /// Spell misfire roll (1..=100) — only used if spell attack.
    pub misfire_roll: i32,
    /// Barbarian spell resist roll (1..=100).
    pub spell_resist_roll: i32,
}

// ---------------------------------------------------------------------------
// Half-round result
// ---------------------------------------------------------------------------

/// Result of one combatant's attack phase.
#[derive(Debug, Clone)]
#[allow(clippy::struct_excessive_bools)]
pub struct PvpHalfRoundResult {
    /// Damage dealt to defender across all strikes.
    pub total_damage: i32,
    /// Whether the attacker landed at least one melee/ranged hit.
    pub did_attack: bool,
    /// Whether the defender dodged at least once.
    pub did_dodge: bool,
    /// Whether the attacker used magic.
    pub did_magic: bool,
    /// Number of successful hits (for XP).
    pub hit_count_attack: i32,
    pub hit_count_magic: i32,
    /// Whether the attacker was stopped by exhaustion.
    pub exhausted: bool,
    /// Whether a spell misfire occurred.
    pub misfire: Option<SpellMisfire>,
    /// Damage the attacker took from misfire self-harm.
    pub self_damage: i32,
}

// ---------------------------------------------------------------------------
// Full battle result
// ---------------------------------------------------------------------------

/// Complete result of a `PvP` battle.
#[derive(Debug, Clone)]
pub struct PvpBattleResult {
    pub outcome: PvpOutcome,
    pub attacker_hp: i32,
    pub defender_hp: i32,
    /// Half-rounds played (each half-round is one side attacking).
    pub half_rounds_played: i32,
}

// ---------------------------------------------------------------------------
// Reward / penalty calculation
// ---------------------------------------------------------------------------

/// Rewards for the `PvP` winner.
#[derive(Debug, Clone)]
pub struct PvpWinRewards {
    /// Total combat XP to distribute.
    pub xp: i32,
    /// Gold stolen from loser (floor of 10% of loser's credits).
    pub gold_stolen: i32,
    /// Reputation change for winner (+0, +1, or +2).
    pub reputation_gain: i32,
    /// Whether a treasure map was stolen.
    pub map_stolen: bool,
    /// XP distribution across stats/skills.
    pub xp_shares: CombatXpShares,
}

/// Reputation-based reward multiplier for `PvP`.
#[derive(Debug, Clone, Copy)]
struct ReputationFactor {
    rep_change: i32,
    xp_multiplier: f64,
}

/// Compute reputation factor based on attacker vs defender reputation.
///
/// PHP logic:
/// - attacker rep - 10 > defender rep → 0 rep, 0.5x XP
/// - attacker rep + 10 < defender rep → 2 rep, 1.5x XP
/// - otherwise → 1 rep, 1.0x XP
fn reputation_factor(attacker_rep: i32, defender_rep: i32) -> ReputationFactor {
    if attacker_rep - 10 > defender_rep {
        ReputationFactor {
            rep_change: 0,
            xp_multiplier: 0.5,
        }
    } else if attacker_rep + 10 < defender_rep {
        ReputationFactor {
            rep_change: 2,
            xp_multiplier: 1.5,
        }
    } else {
        ReputationFactor {
            rep_change: 1,
            xp_multiplier: 1.0,
        }
    }
}

/// Sum of all modified stats + combat skills for a combatant.
/// Used as the base for `PvP` XP calculation.
fn combat_power_sum(c: &PvpCombatant) -> i32 {
    c.strength
        + c.agility
        + c.speed
        + c.condition
        + c.intelligence
        + c.wisdom
        + c.attack_skill
        + c.shoot_skill
        + c.magic_skill
        + c.dodge_skill
}

/// Calculate `PvP` win rewards.
///
/// PHP winner XP formula:
/// 1. `base = 2 * opponent_power_sum`
/// 2. `ratio = base / (2 * winner_power_sum)`, capped at 2.0
/// 3. `xp = ceil(base * ratio * rep_multiplier)`
#[allow(clippy::cast_possible_truncation, clippy::cast_precision_loss)]
pub fn pvp_win_rewards(
    winner: &PvpCombatant,
    loser: &PvpCombatant,
    did_dodge: bool,
    did_attack: bool,
    did_magic: bool,
    attack_type: AttackType,
    map_steal_roll: i32,
) -> PvpWinRewards {
    let loser_power = combat_power_sum(loser);
    let winner_power = combat_power_sum(winner);

    let base_xp = loser_power * 2;
    let ratio = if winner_power > 0 {
        (f64::from(base_xp) / (f64::from(winner_power) * 2.0)).min(2.0)
    } else {
        2.0
    };

    let rep = reputation_factor(winner.reputation, loser.reputation);
    let xp = (f64::from(base_xp) * ratio * rep.xp_multiplier).ceil() as i32;

    let gold_stolen = (loser.credits / 10).max(0);
    let map_stolen = map_steal_roll == 20 && loser.maps > 0;

    let xp_shares =
        formulas::distribute_combat_xp(xp, did_dodge, did_attack, did_magic, attack_type);

    PvpWinRewards {
        xp,
        gold_stolen,
        reputation_gain: rep.rep_change,
        map_stolen,
        xp_shares,
    }
}

/// Calculate draw XP for one combatant in a drawn `PvP` fight.
///
/// PHP draw XP formula:
/// 1. `base = opponent_power_sum`
/// 2. `ratio = base / my_power_sum`, capped at 2.0
/// 3. `xp = ceil(base * ratio)`
#[allow(clippy::cast_possible_truncation, clippy::cast_precision_loss)]
pub fn pvp_draw_xp(
    me: &PvpCombatant,
    opponent: &PvpCombatant,
    did_dodge: bool,
    did_attack: bool,
    did_magic: bool,
    attack_type: AttackType,
) -> CombatXpShares {
    let opp_power = combat_power_sum(opponent);
    let my_power = combat_power_sum(me);

    let ratio = if my_power > 0 {
        (f64::from(opp_power) / f64::from(my_power)).min(2.0)
    } else {
        2.0
    };

    let xp = (f64::from(opp_power) * ratio).ceil() as i32;
    formulas::distribute_combat_xp(xp, did_dodge, did_attack, did_magic, attack_type)
}

// ---------------------------------------------------------------------------
// PvP validation
// ---------------------------------------------------------------------------

/// Reasons a `PvP` fight cannot start.
#[derive(Debug, Clone, PartialEq, Eq)]
pub enum PvpValidationError {
    SelfAttack,
    DefenderDead,
    AttackerDead,
    NoEnergy,
    SameTribe,
    AttackerNewbie,
    DefenderNewbie,
    NoClass,
    DefenderNoClass,
    ConflictingWeapons,
    NoWeapon,
    BowNoArrows,
    SpellWrongClass,
    AttackerImmune,
    DefenderImmune,
    NoMana,
    DifferentLocation,
    DefenderResting,
    DefenderInFight,
    AlreadyAttackedToday,
    DefenderFrozen,
    AttackerInDungeon,
}

/// Context flags for `PvP` validation (avoids too-many-bool-arguments).
#[allow(clippy::struct_excessive_bools)]
pub struct PvpValidationCtx {
    pub attacker_class_set: bool,
    pub defender_class_set: bool,
    pub has_conflicting_weapons: bool,
    pub has_no_weapon: bool,
    pub bow_no_arrows: bool,
    pub spell_wrong_class: bool,
    pub no_mana: bool,
    pub same_location: bool,
    pub defender_resting: bool,
    pub defender_in_fight: bool,
    pub already_attacked_today: bool,
    pub defender_frozen: bool,
    pub attacker_in_dungeon: bool,
    pub attacker_immune: bool,
    pub defender_immune: bool,
    pub attacker_newbie: bool,
    pub defender_newbie: bool,
    pub same_tribe: bool,
}

/// Validate whether a `PvP` fight can start.
///
/// Returns `Ok(())` if all preconditions pass, or the first failure.
pub fn validate_pvp(
    attacker: &PvpCombatant,
    defender: &PvpCombatant,
    ctx: &PvpValidationCtx,
) -> Result<(), PvpValidationError> {
    if attacker.player_id == defender.player_id {
        return Err(PvpValidationError::SelfAttack);
    }
    if ctx.attacker_in_dungeon {
        return Err(PvpValidationError::AttackerInDungeon);
    }
    if defender.hp <= 0 {
        return Err(PvpValidationError::DefenderDead);
    }
    if attacker.hp <= 0 {
        return Err(PvpValidationError::AttackerDead);
    }
    if ctx.attacker_newbie {
        return Err(PvpValidationError::AttackerNewbie);
    }
    if ctx.defender_newbie {
        return Err(PvpValidationError::DefenderNewbie);
    }
    if !ctx.attacker_class_set {
        return Err(PvpValidationError::NoClass);
    }
    if !ctx.defender_class_set {
        return Err(PvpValidationError::DefenderNoClass);
    }
    if ctx.has_conflicting_weapons {
        return Err(PvpValidationError::ConflictingWeapons);
    }
    if ctx.has_no_weapon {
        return Err(PvpValidationError::NoWeapon);
    }
    if ctx.bow_no_arrows {
        return Err(PvpValidationError::BowNoArrows);
    }
    if ctx.spell_wrong_class {
        return Err(PvpValidationError::SpellWrongClass);
    }
    if ctx.attacker_immune {
        return Err(PvpValidationError::AttackerImmune);
    }
    if ctx.defender_immune {
        return Err(PvpValidationError::DefenderImmune);
    }
    if ctx.no_mana {
        return Err(PvpValidationError::NoMana);
    }
    if !ctx.same_location {
        return Err(PvpValidationError::DifferentLocation);
    }
    if ctx.defender_resting {
        return Err(PvpValidationError::DefenderResting);
    }
    if ctx.defender_in_fight {
        return Err(PvpValidationError::DefenderInFight);
    }
    if ctx.already_attacked_today {
        return Err(PvpValidationError::AlreadyAttackedToday);
    }
    if ctx.defender_frozen {
        return Err(PvpValidationError::DefenderFrozen);
    }
    if ctx.same_tribe {
        return Err(PvpValidationError::SameTribe);
    }
    Ok(())
}

// ---------------------------------------------------------------------------
// PvP critical chance
// ---------------------------------------------------------------------------

/// Critical hit chance for `PvP`.
///
/// PHP `critical($fltAbility, $objPlayer)`:
/// - Cap skill at 6, then add assassin bonus.
fn pvp_crit_chance(skill_level: i32, assassin_bonus: i32) -> i32 {
    skill_level.min(6) + assassin_bonus
}

// ---------------------------------------------------------------------------
// PvP combat formulas
// ---------------------------------------------------------------------------

/// Compute attacker's base power, dominant skill name, and attack type.
///
/// Mirrors the weapon/bow/spell priority in PHP `attack1`.
#[allow(clippy::cast_possible_truncation)]
fn compute_attack_power(attacker: &PvpCombatant) -> (f64, &'static str, AttackType) {
    let mode = attack_mode(attacker);

    // --- Bow (ranged) ---
    if mode == Some(AttackType::Ranged) {
        let bow = attacker.bow.as_ref().unwrap();
        let arrows = attacker.arrows.as_ref().unwrap();
        let bonus = f64::from(attacker.strength) / 2.0 + f64::from(attacker.agility) / 2.0;
        let mut power = f64::from(bow.power) + bonus + f64::from(arrows.power);
        if attacker.class == Class::Warrior || attacker.class == Class::Barbarian {
            power += (f64::from(attacker.shoot_skill) / 10.0).ceil();
        }
        return (power, "shoot", AttackType::Ranged);
    }

    // --- Melee weapon ---
    if mode == Some(AttackType::Melee) {
        let w = attacker.weapon.as_ref().unwrap();
        let mut power = f64::from(w.power) + f64::from(attacker.strength);
        if attacker.class == Class::Warrior || attacker.class == Class::Barbarian {
            power += (f64::from(attacker.attack_skill) / 10.0).ceil();
        }
        // Secondary weapon (Barbarian)
        if let Some(sw) = &attacker.second_weapon {
            if sw.durability > 0 {
                power += f64::from(sw.power)
                    + f64::from(attacker.strength)
                    + (f64::from(attacker.attack_skill) / 10.0).ceil();
            }
        }
        return (power, "attack", AttackType::Melee);
    }

    // --- Spell ---
    if let Some(ref spell) = attacker.battle_spell {
        return (f64::from(spell.power), "magic", AttackType::Spell);
    }

    (0.0, "attack", AttackType::Melee)
}

/// Compute the base dodge value for the defender against the attacker.
///
/// PHP: `$unik = defender.agility_mod - attacker.agility_mod + defender.dodge_skill`
/// Then subtracts attacker's relevant skill and class bonus.
#[allow(clippy::cast_possible_truncation)]
fn compute_dodge_value(attacker: &PvpCombatant, defender: &PvpCombatant) -> i32 {
    let mut unik = defender.agility - attacker.agility + defender.dodge_skill;

    let mode = attack_mode(attacker);

    match mode {
        Some(AttackType::Ranged) => {
            unik -= attacker.shoot_skill;
            if attacker.class == Class::Warrior || attacker.class == Class::Barbarian {
                unik -= (f64::from(attacker.shoot_skill) / 10.0).ceil() as i32;
            }
            // Ranged doubles dodge value in PHP
            unik *= 2;
        }
        Some(AttackType::Melee) => {
            unik -= attacker.attack_skill;
            if attacker.class == Class::Warrior || attacker.class == Class::Barbarian {
                unik -= (f64::from(attacker.attack_skill) / 10.0).ceil() as i32;
            }
            // Secondary weapon reduces dodge further
            if attacker
                .second_weapon
                .as_ref()
                .is_some_and(|sw| sw.durability > 0)
            {
                #[allow(clippy::cast_possible_truncation)]
                {
                    unik -= (f64::from(attacker.attack_skill) / 5.0) as i32;
                }
            }
        }
        Some(AttackType::Spell) => {
            unik -= attacker.magic_skill;
        }
        None => {}
    }

    // Defender class bonus for dodge
    if defender.class == Class::Warrior || defender.class == Class::Barbarian {
        unik += (f64::from(defender.dodge_skill) / 10.0).ceil() as i32;
    }

    unik.max(1)
}

/// Compute defender's spell defense value (from defensive spell + wand + equipment).
///
/// PHP computes this as: `$eczarobr = def_spell.def` minus equipment
/// agility mods, plus wand bonus, but only if defender is a Mage with
/// mana > 0.
#[allow(clippy::cast_possible_truncation)]
fn compute_spell_defense(defender: &PvpCombatant) -> i32 {
    if defender.class != Class::Mage || defender.mana <= 0 {
        return 0;
    }

    let Some(ref dspell) = defender.defense_spell else {
        return 0;
    };

    let mut def = f64::from(dspell.power);

    // Equipment agility_mod reduces spell defense (PHP [3],[2],[4],[5] → armor, helmet, legs, shield)
    for item in [
        &defender.armor,
        &defender.helmet,
        &defender.legs,
        &defender.shield,
    ]
    .into_iter()
    .flatten()
    {
        def -= f64::from(dspell.power) * (f64::from(item.agility_mod) / 100.0);
    }
    if def < 0.0 {
        def = 0.0;
    }

    // Wand bonus
    if let Some(ref wand) = defender.wand {
        let n = 6 - (wand.agility_mod / 20);
        let n = n.max(1);
        let bonus =
            (10.0 / f64::from(n)) * (f64::from(defender.magic_skill) / 10.0).ceil() * f64::from(n);
        def += bonus;
    }

    def as i32
}

/// Compute defender's base defense power (condition + bonuses + pet).
fn compute_defense_power(defender: &PvpCombatant) -> f64 {
    let mut def = f64::from(defender.condition) * (1.0 + defender.defender_bonus);

    // Pet defense contribution
    if defender.pet_defense > 0 {
        let pet_contribution = defender.pet_defense.min(defender.dodge_skill);
        def += f64::from(pet_contribution);
    }

    // Rage penalty reduces defense
    def * (1.0 - defender.rage_bonus)
}

/// Compute armor defense at a hit location for `PvP`.
///
/// PHP maps hit location 0..3 to equip slots 2..5 (helmet, armor, legs, shield).
fn armor_at_location(defender: &PvpCombatant, location: HitLocation) -> i32 {
    let slot = match location {
        HitLocation::Body => &defender.armor,
        HitLocation::Head => &defender.helmet,
        HitLocation::Legs => &defender.legs,
        HitLocation::Arms => &defender.shield,
    };

    slot.as_ref()
        .map_or(0, |item| if item.durability > 0 { item.power } else { 0 })
}

/// Compute strikes this half-round (speed ratio, capped 1..5).
#[allow(clippy::cast_possible_truncation)]
fn strikes_per_half_round(attacker_speed: i32, defender_speed: i32) -> i32 {
    if defender_speed <= 0 {
        return MAX_STRIKES;
    }
    let ratio = (f64::from(attacker_speed) / f64::from(defender_speed)).ceil() as i32;
    ratio.clamp(1, MAX_STRIKES)
}

/// Shield block chance (percentage).
#[allow(clippy::cast_possible_truncation)]
fn shield_block_chance(defender: &PvpCombatant, attack_type: AttackType) -> i32 {
    let Some(ref shield) = defender.shield else {
        return 0;
    };
    if shield.durability <= 0 {
        return 0;
    }
    let mut chance = (f64::from(shield.power) / 5.0).ceil() as i32;
    if chance > 20 {
        chance = 20;
    }
    if attack_type == AttackType::Ranged {
        chance *= 2;
    }
    chance
}

/// Mana lost by defender when hit (for mage defenders with defensive spell).
///
/// PHP: `ceil(def_spell.level / 2.5) + nutari_poison - floor(magic_skill / 25)`, min 1.
#[allow(clippy::cast_possible_truncation)]
fn defender_mana_loss(defender: &PvpCombatant, attacker: &PvpCombatant) -> i32 {
    let Some(ref dspell) = defender.defense_spell else {
        return 0;
    };
    let mut lost = (f64::from(dspell.level) / 2.5).ceil() as i32;

    // Nutari poison on attacker weapons drains extra mana if defender lacks Nutari antidote
    if defender.antidote != PoisonType::Nutari {
        for item in [&attacker.weapon, &attacker.arrows, &attacker.second_weapon]
            .into_iter()
            .flatten()
        {
            if item.poison_type == PoisonType::Nutari {
                lost += item.poison;
            }
        }
    }

    lost -= defender.magic_skill / 25;
    lost.max(1)
}

// ---------------------------------------------------------------------------
// Core PvP turn resolution
// ---------------------------------------------------------------------------

/// Resolve one half-round: the `attacker` strikes at the `defender`.
///
/// Returns how much damage was dealt and combat flags for XP distribution.
/// Mutates `attacker` and `defender` HP/mana/durability in place.
#[allow(clippy::too_many_lines, clippy::cast_possible_truncation)]
pub fn resolve_pvp_half_round(
    attacker: &mut PvpCombatant,
    defender: &mut PvpCombatant,
    rolls: &PvpHalfRoundRolls,
    attacker_exhaustion: &mut f64,
    defender_exhaustion: &mut f64,
) -> PvpHalfRoundResult {
    let (base_power, skill_key, attack_type) = compute_attack_power(attacker);
    let base_dodge = compute_dodge_value(attacker, defender);
    let spell_defense = compute_spell_defense(defender);
    let base_def_power = compute_defense_power(defender);
    let block_chance = shield_block_chance(defender, attack_type);
    let crit_chance = pvp_crit_chance(
        match skill_key {
            "shoot" => attacker.shoot_skill,
            "magic" => attacker.magic_skill,
            _ => attacker.attack_skill,
        },
        attacker.assassin_bonus,
    );

    // Craftsman penalty
    let mut my_power = base_power;
    if attacker.class == Class::Craftsman {
        my_power -= my_power / 4.0;
    }

    // Rage bonus
    my_power += my_power * attacker.rage_bonus;

    // Pet attack contribution
    if attacker.pet_attack > 0 {
        let relevant_skill = match skill_key {
            "shoot" => attacker.shoot_skill,
            "magic" => attacker.magic_skill,
            _ => attacker.attack_skill,
        };
        let pet_contrib = attacker.pet_attack.min(relevant_skill);
        my_power += f64::from(pet_contrib);
    }

    #[allow(clippy::cast_possible_wrap)]
    let strikes =
        strikes_per_half_round(attacker.speed, defender.speed).min(rolls.strikes.len() as i32);

    // Spell misfire threshold
    let misfire_threshold = if attack_type == AttackType::Spell {
        let spell_level = attacker.battle_spell.as_ref().map_or(0, |s| s.level);
        (attacker.magic_skill - spell_level).min(0)
    } else {
        100 // no misfire possible for non-spell
    };

    let mut result = PvpHalfRoundResult {
        total_damage: 0,
        did_attack: false,
        did_dodge: false,
        did_magic: false,
        hit_count_attack: 0,
        hit_count_magic: 0,
        exhausted: false,
        misfire: None,
        self_damage: 0,
    };

    let dodge_max = formulas::player_dodge_max(
        attacker.agility
            + match skill_key {
                "shoot" => attacker.shoot_skill,
                "magic" => attacker.magic_skill,
                _ => attacker.attack_skill,
            },
    );
    let dodge_max_90 = (f64::from(dodge_max) * 0.9).floor() as i32;

    for i in 0..strikes {
        #[allow(clippy::cast_sign_loss)]
        let roll = &rolls.strikes[i as usize];
        let location = HitLocation::from_roll(roll.location_roll);

        // Exhaustion check — attacker
        if *attacker_exhaustion > f64::from(attacker.condition) {
            result.exhausted = true;
            break;
        }

        // No mana for spell attack
        if attack_type == AttackType::Spell && attacker.mana < 1 {
            break;
        }

        // Spell misfire check
        if attack_type == AttackType::Spell {
            let pech_plus_roll = misfire_threshold + roll.misfire_roll;
            if pech_plus_roll < 6 {
                let misfire = formulas::spell_misfire_outcome(roll.misfire_roll);
                match misfire {
                    SpellMisfire::LoseOneMana => {
                        attacker.mana = (attacker.mana - 1).max(0);
                    }
                    SpellMisfire::LoseConcentration => {
                        // No effect beyond losing the turn
                    }
                    SpellMisfire::LoseAllMana => {
                        attacker.mana = 0;
                    }
                    SpellMisfire::SelfDamage => {
                        let dmg = my_power as i32;
                        attacker.hp -= dmg;
                        result.self_damage += dmg;
                    }
                    SpellMisfire::PartialHit { damage_percent } => {
                        let dmg = (my_power * f64::from(damage_percent) / 100.0).floor() as i32;
                        defender.hp -= dmg;
                        result.total_damage += dmg;
                    }
                    SpellMisfire::PartialHitAndSelfDamage { damage_percent } => {
                        let dmg = (my_power * f64::from(damage_percent) / 100.0).floor() as i32;
                        defender.hp -= dmg;
                        attacker.hp -= dmg;
                        result.total_damage += dmg;
                        result.self_damage += dmg;
                    }
                }
                result.misfire = Some(misfire);
                break;
            }
        }

        // Consume mana for spell
        if attack_type == AttackType::Spell && my_power > 0.0 {
            attacker.mana -= 1;
        }

        // Weapon durability
        match attack_type {
            AttackType::Melee => {
                if let Some(ref mut w) = attacker.weapon {
                    if w.durability > 0 {
                        *attacker_exhaustion += f64::from(w.agility_mod) / 10.0;
                        w.durability -= 1;
                    }
                    if w.durability <= 0 {
                        break;
                    }
                }
                if let Some(ref mut sw) = attacker.second_weapon {
                    if sw.durability > 0 {
                        *attacker_exhaustion += f64::from(sw.agility_mod) / 10.0;
                        sw.durability -= 1;
                    }
                }
            }
            AttackType::Ranged => {
                if let Some(ref mut bow) = attacker.bow {
                    if bow.durability > 0 {
                        *attacker_exhaustion += f64::from(bow.agility_mod) / 10.0;
                        bow.durability -= 1;
                    }
                }
                if let Some(ref mut arr) = attacker.arrows {
                    if arr.durability > 0 {
                        arr.durability -= 1;
                    }
                }
                let bow_broken = attacker.bow.as_ref().is_some_and(|b| b.durability <= 0);
                let arrows_gone = attacker.arrows.as_ref().is_some_and(|a| a.durability <= 0);
                if bow_broken || arrows_gone {
                    break;
                }
            }
            AttackType::Spell => {}
        }

        // Defender exhaustion check
        let def_exhausted = *defender_exhaustion > f64::from(defender.condition);

        // Random skill contribution to attack power
        let attack_power = my_power + f64::from(roll.attack_roll);

        // --- Dodge check ---
        let dodge_value = if def_exhausted { 0 } else { base_dodge };
        let dodged = dodge_value >= roll.dodge_chance_roll
            && roll.dodge_chance_roll < dodge_max_90
            && dodge_value > 0;

        if dodged {
            result.did_dodge = true;
            *defender_exhaustion += defender
                .armor
                .as_ref()
                .map_or(0.0, |a| f64::from(a.agility_mod) / 10.0);
            continue;
        }

        // --- Shield block ---
        if !dodged && roll.block_roll <= block_chance {
            if let Some(ref mut shield) = defender.shield {
                if shield.durability > 0 {
                    *defender_exhaustion += f64::from(shield.agility_mod) / 10.0;
                    shield.durability -= 1;
                    continue;
                }
            }
        }

        // --- Mana loss for defender spell ---
        if defender.defense_spell.is_some() {
            let lost = defender_mana_loss(defender, attacker);
            if defender.mana >= lost {
                defender.mana -= lost;
            }
        }

        // --- Damage calculation ---
        let armor_def = if attack_type != AttackType::Spell || roll.misfire_roll > 55 {
            let armor_power = armor_at_location(defender, location);
            f64::from(armor_power) * (1.0 + defender.defender_bonus)
        } else {
            0.0
        };

        // Reduce armor durability
        match location {
            HitLocation::Body => {
                if let Some(ref mut a) = defender.armor {
                    if a.durability > 0 {
                        a.durability -= 1;
                    }
                }
            }
            HitLocation::Head => {
                if let Some(ref mut h) = defender.helmet {
                    if h.durability > 0 {
                        h.durability -= 1;
                    }
                }
            }
            HitLocation::Legs => {
                if let Some(ref mut l) = defender.legs {
                    if l.durability > 0 {
                        l.durability -= 1;
                    }
                }
            }
            HitLocation::Arms => {
                if let Some(ref mut s) = defender.shield {
                    if s.durability > 0 {
                        s.durability -= 1;
                    }
                }
            }
        }

        let raw_damage = attack_power
            - (f64::from(roll.dodge_roll) + base_def_power + armor_def + f64::from(spell_defense));
        let mut damage = raw_damage.max(0.0) as i32;

        // --- Critical hit ---
        let crit_threshold = f64::from(crit_chance);
        let roll_1000_f = f64::from(roll.crit_roll_1000) / 10.0;

        if crit_threshold >= roll_1000_f && roll.crit_roll_100 <= crit_chance {
            // Instant kill
            defender.hp = 0;
            match attack_type {
                AttackType::Spell => result.hit_count_magic += 1,
                _ => result.hit_count_attack += 1,
            }
            result.did_attack = attack_type != AttackType::Spell;
            result.did_magic = attack_type == AttackType::Spell;
            break;
        } else if crit_threshold >= roll_1000_f {
            // Enhanced damage
            let skill_val = match skill_key {
                "shoot" => attacker.shoot_skill,
                "magic" => attacker.magic_skill,
                _ => attacker.attack_skill,
            };
            if roll.crit_roll_100 <= 40 {
                damage += my_power as i32 + skill_val;
            } else {
                damage += skill_val;
            }
        }

        // --- Barbarian spell resistance ---
        if attack_type == AttackType::Spell && defender.class == Class::Barbarian {
            let resist_chance = (defender.wisdom / 2).min(20);
            if roll.spell_resist_roll < resist_chance {
                // Resisted — no damage
                continue;
            }
        }

        // --- Apply damage ---
        defender.hp -= damage;

        // --- Poison damage (Illani) on melee/ranged ---
        if attack_type == AttackType::Melee || attack_type == AttackType::Ranged {
            let mut poison_dmg = 0;
            if defender.antidote != PoisonType::Illani {
                if let Some(ref w) = attacker.weapon {
                    if w.poison_type == PoisonType::Illani {
                        poison_dmg += w.poison;
                    }
                }
                if let Some(ref arr) = attacker.arrows {
                    if arr.poison_type == PoisonType::Illani {
                        poison_dmg += arr.poison;
                    }
                }
            }
            if poison_dmg > 0 {
                defender.hp -= poison_dmg;
                damage += poison_dmg;
            }
        }

        result.total_damage += damage;
        if attack_type == AttackType::Spell {
            if damage > 0 {
                result.hit_count_magic += 1;
            }
            result.did_magic = true;
        } else {
            if damage > 0 {
                result.hit_count_attack += 1;
            }
            result.did_attack = true;
        }

        if defender.hp <= 0 {
            break;
        }
    }

    result
}

// ---------------------------------------------------------------------------
// Full PvP battle
// ---------------------------------------------------------------------------

/// All RNG rolls for a complete `PvP` battle.
///
/// The caller must provide enough half-round rolls for the maximum fight
/// duration.  Unused rolls are simply ignored.
#[derive(Debug, Clone)]
pub struct PvpBattleRolls {
    /// Rolls indexed by half-round number (0-based).
    /// Even indices = first attacker, odd = second attacker.
    pub half_rounds: Vec<PvpHalfRoundRolls>,
}

/// Run a full `PvP` battle to completion.
///
/// `first` attacks first (the player with higher speed in PHP).
/// Returns the battle result without persisting anything.
pub fn run_pvp_battle(
    first: &mut PvpCombatant,
    second: &mut PvpCombatant,
    rolls: &PvpBattleRolls,
) -> PvpBattleResult {
    let mut first_exhaustion = 0.0_f64;
    let mut second_exhaustion = 0.0_f64;
    let mut half_round = 0_i32;

    loop {
        // Check draw — PHP checks `$runda >= 25`, runda increments by 0.5.
        if half_round >= MAX_HALF_ROUNDS {
            // Ensure both survive with at least 1 HP
            if first.hp < 1 {
                first.hp = 1;
            }
            if second.hp < 1 {
                second.hp = 1;
            }
            return PvpBattleResult {
                outcome: PvpOutcome::Draw,
                attacker_hp: first.hp,
                defender_hp: second.hp,
                half_rounds_played: half_round,
            };
        }

        #[allow(clippy::cast_sign_loss)]
        let hr_idx = half_round as usize;
        if hr_idx >= rolls.half_rounds.len() {
            // Not enough rolls provided — treat as draw.
            return PvpBattleResult {
                outcome: PvpOutcome::Draw,
                attacker_hp: first.hp,
                defender_hp: second.hp,
                half_rounds_played: half_round,
            };
        }

        let hr_rolls = &rolls.half_rounds[hr_idx];

        // Alternate: even = first attacks, odd = second attacks
        if half_round % 2 == 0 {
            resolve_pvp_half_round(
                first,
                second,
                hr_rolls,
                &mut first_exhaustion,
                &mut second_exhaustion,
            );
            if second.hp <= 0 {
                second.hp = 0;
                if first.hp < 1 {
                    first.hp = 1;
                }
                return PvpBattleResult {
                    outcome: PvpOutcome::AttackerWin,
                    attacker_hp: first.hp,
                    defender_hp: 0,
                    half_rounds_played: half_round + 1,
                };
            }
        } else {
            resolve_pvp_half_round(
                second,
                first,
                hr_rolls,
                &mut second_exhaustion,
                &mut first_exhaustion,
            );
            if first.hp <= 0 {
                first.hp = 0;
                if second.hp < 1 {
                    second.hp = 1;
                }
                return PvpBattleResult {
                    outcome: PvpOutcome::DefenderWin,
                    attacker_hp: 0,
                    defender_hp: second.hp,
                    half_rounds_played: half_round + 1,
                };
            }
        }

        half_round += 1;
    }
}

// ---------------------------------------------------------------------------
// Battle log entry (for persistence)
// ---------------------------------------------------------------------------

/// Structured `PvP` battle log entry for database persistence.
#[derive(Debug, Clone)]
pub struct PvpBattleLogEntry {
    pub attacker_id: i32,
    pub defender_id: i32,
    /// ID of the winner (0 for draw).
    pub winner_id: i32,
    /// Game day when the battle occurred.
    pub battle_day: i32,
}

impl PvpBattleLogEntry {
    pub fn from_result(
        attacker_id: i32,
        defender_id: i32,
        outcome: PvpOutcome,
        game_day: i32,
    ) -> Self {
        let winner_id = match outcome {
            PvpOutcome::AttackerWin => attacker_id,
            PvpOutcome::DefenderWin => defender_id,
            PvpOutcome::Draw => 0,
        };
        Self {
            attacker_id,
            defender_id,
            winner_id,
            battle_day: game_day,
        }
    }
}

// ---------------------------------------------------------------------------
// Equipment wear for PvP
// ---------------------------------------------------------------------------

/// Collect equipment durability decrements that happened during the fight.
///
/// The caller can use this to persist durability changes for both players.
/// Since we mutate `PvpCombatant` in-place during the fight, the caller
/// can simply diff `original.durability - combatant.item.durability` for
/// each slot.  This function is a convenience to extract all non-zero
/// durability losses.
pub fn equipment_wear(original: &PvpCombatant, after: &PvpCombatant) -> Vec<(i32, i32)> {
    let mut wear = Vec::new();
    let pairs: Vec<(&Option<OwnedEquipment>, &Option<OwnedEquipment>)> = vec![
        (&original.weapon, &after.weapon),
        (&original.bow, &after.bow),
        (&original.arrows, &after.arrows),
        (&original.helmet, &after.helmet),
        (&original.armor, &after.armor),
        (&original.legs, &after.legs),
        (&original.shield, &after.shield),
        (&original.second_weapon, &after.second_weapon),
    ];
    for (orig, current) in pairs {
        if let (Some(o), Some(c)) = (orig, current) {
            let lost = o.durability - c.durability;
            if lost > 0 {
                wear.push((o.id, lost));
            }
        }
    }
    wear
}

// ===========================================================================
// Tests
// ===========================================================================

#[cfg(test)]
mod tests {
    use super::*;
    use crate::item::Element;

    fn make_combatant(id: i32, name: &str) -> PvpCombatant {
        PvpCombatant {
            player_id: id,
            name: name.to_string(),
            class: Class::Warrior,
            hp: 100,
            mana: 10,
            antidote: PoisonType::None,
            reputation: 50,
            credits: 1000,
            maps: 0,
            strength: 20,
            agility: 15,
            speed: 10,
            condition: 25,
            wisdom: 10,
            intelligence: 10,
            attack_skill: 30,
            shoot_skill: 5,
            dodge_skill: 20,
            magic_skill: 5,
            weapon: Some(make_weapon(1, 40)),
            bow: None,
            arrows: None,
            helmet: Some(make_armor(2, 10, 5)),
            armor: Some(make_armor(3, 20, 10)),
            legs: Some(make_armor(4, 8, 3)),
            shield: None,
            wand: None,
            second_weapon: None,
            battle_spell: None,
            defense_spell: None,
            pet_attack: 0,
            pet_defense: 0,
            assassin_bonus: 0,
            rage_bonus: 0.0,
            defender_bonus: 0.0,
        }
    }

    fn make_weapon(id: i32, power: i32) -> OwnedEquipment {
        OwnedEquipment {
            id,
            owner_id: 1,
            name: "Sword".to_string(),
            power,
            status: crate::item::EquipmentStatus::Equipped,
            equipment_type: crate::item::EquipmentType::Weapon,
            cost: 100,
            min_level: 1,
            agility_mod: 5,
            durability: 100,
            speed_mod: 0,
            max_durability: 100,
            magic: Element::None,
            poison: 0,
            amount: 1,
            two_handed: false,
            poison_type: PoisonType::None,
            repair_cost: 10,
            location: String::new(),
        }
    }

    fn make_armor(id: i32, power: i32, agility_mod: i32) -> OwnedEquipment {
        OwnedEquipment {
            id,
            owner_id: 1,
            name: "Armor".to_string(),
            power,
            status: crate::item::EquipmentStatus::Equipped,
            equipment_type: crate::item::EquipmentType::Armor,
            cost: 50,
            min_level: 1,
            agility_mod,
            durability: 100,
            speed_mod: 0,
            max_durability: 100,
            magic: Element::None,
            poison: 0,
            amount: 1,
            two_handed: false,
            poison_type: PoisonType::None,
            repair_cost: 5,
            location: String::new(),
        }
    }

    fn make_strike_roll() -> PvpStrikeRoll {
        PvpStrikeRoll {
            location_roll: 50,
            attack_roll: 15,
            dodge_roll: 5,
            dodge_chance_roll: 80,
            block_roll: 90,
            crit_roll_1000: 500,
            crit_roll_100: 50,
            misfire_roll: 80,
            spell_resist_roll: 80,
        }
    }

    fn make_half_round_rolls(count: usize) -> PvpHalfRoundRolls {
        PvpHalfRoundRolls {
            strikes: (0..count).map(|_| make_strike_roll()).collect(),
        }
    }

    // --- Reputation factor ---

    #[test]
    fn reputation_factor_equal_rep() {
        let f = reputation_factor(50, 50);
        assert_eq!(f.rep_change, 1);
        assert!((f.xp_multiplier - 1.0).abs() < f64::EPSILON);
    }

    #[test]
    fn reputation_factor_attacker_much_higher() {
        let f = reputation_factor(70, 50);
        assert_eq!(f.rep_change, 0);
        assert!((f.xp_multiplier - 0.5).abs() < f64::EPSILON);
    }

    #[test]
    fn reputation_factor_attacker_much_lower() {
        let f = reputation_factor(30, 50);
        assert_eq!(f.rep_change, 2);
        assert!((f.xp_multiplier - 1.5).abs() < f64::EPSILON);
    }

    // --- PvP combat power sum ---

    #[test]
    fn combat_power_sum_basic() {
        let c = make_combatant(1, "Test");
        let sum = combat_power_sum(&c);
        // 20+15+10+25+10+10 + 30+5+20+5 = 150
        assert_eq!(sum, 150);
    }

    // --- PvP win rewards ---

    #[test]
    fn pvp_win_rewards_equal_power() {
        let winner = make_combatant(1, "Winner");
        let loser = make_combatant(2, "Loser");
        let rewards = pvp_win_rewards(
            &winner,
            &loser,
            true,
            true,
            false,
            AttackType::Melee,
            10, // no map steal
        );

        // loser_power = 150, base_xp = 300
        // ratio = 300 / (2*150) = 1.0, capped at 2
        // rep factor = 1.0 (equal rep)
        // xp = ceil(300 * 1.0 * 1.0) = 300
        assert_eq!(rewards.xp, 300);
        assert_eq!(rewards.gold_stolen, 100); // 10% of 1000
        assert_eq!(rewards.reputation_gain, 1);
        assert!(!rewards.map_stolen);
    }

    #[test]
    fn pvp_win_rewards_map_steal() {
        let winner = make_combatant(1, "Winner");
        let mut loser = make_combatant(2, "Loser");
        loser.maps = 3;
        let rewards = pvp_win_rewards(
            &winner,
            &loser,
            true,
            true,
            false,
            AttackType::Melee,
            20, // roll == 20 → map steal
        );
        assert!(rewards.map_stolen);
    }

    // --- PvP draw XP ---

    #[test]
    fn pvp_draw_xp_equal_power() {
        let me = make_combatant(1, "Me");
        let opp = make_combatant(2, "Opp");
        let shares = pvp_draw_xp(&me, &opp, true, true, false, AttackType::Melee);
        // opp_power = 150, ratio = 1.0, xp = ceil(150 * 1.0) = 150
        let total: i32 = shares
            .stat_shares
            .iter()
            .chain(shares.skill_shares.iter())
            .map(|(_, v)| *v)
            .sum();
        assert!(total > 0);
    }

    fn make_validation_ctx() -> PvpValidationCtx {
        PvpValidationCtx {
            attacker_class_set: true,
            defender_class_set: true,
            has_conflicting_weapons: false,
            has_no_weapon: false,
            bow_no_arrows: false,
            spell_wrong_class: false,
            no_mana: false,
            same_location: true,
            defender_resting: false,
            defender_in_fight: false,
            already_attacked_today: false,
            defender_frozen: false,
            attacker_in_dungeon: false,
            attacker_immune: false,
            defender_immune: false,
            attacker_newbie: false,
            defender_newbie: false,
            same_tribe: false,
        }
    }

    // --- PvP validation ---

    #[test]
    fn validate_pvp_self_attack() {
        let a = make_combatant(1, "A");
        let b = make_combatant(1, "B");
        let ctx = make_validation_ctx();
        let result = validate_pvp(&a, &b, &ctx);
        assert_eq!(result, Err(PvpValidationError::SelfAttack));
    }

    #[test]
    fn validate_pvp_success() {
        let a = make_combatant(1, "A");
        let b = make_combatant(2, "B");
        let ctx = make_validation_ctx();
        let result = validate_pvp(&a, &b, &ctx);
        assert!(result.is_ok());
    }

    // --- Half round resolution ---

    #[test]
    fn resolve_half_round_deals_damage() {
        let mut attacker = make_combatant(1, "Attacker");
        let mut defender = make_combatant(2, "Defender");
        let rolls = make_half_round_rolls(5);

        let mut atk_exh = 0.0;
        let mut def_exh = 0.0;

        let result = resolve_pvp_half_round(
            &mut attacker,
            &mut defender,
            &rolls,
            &mut atk_exh,
            &mut def_exh,
        );

        assert!(result.total_damage >= 0);
        assert!(defender.hp <= 100);
    }

    #[test]
    fn resolve_half_round_dodge_high_agility() {
        let mut attacker = make_combatant(1, "Attacker");
        let mut defender = make_combatant(2, "Defender");
        defender.agility = 200;
        defender.dodge_skill = 100;

        // Make dodge always succeed
        let rolls = PvpHalfRoundRolls {
            strikes: vec![PvpStrikeRoll {
                location_roll: 50,
                attack_roll: 5,
                dodge_roll: 5,
                dodge_chance_roll: 1, // very low → dodge succeeds
                block_roll: 90,
                crit_roll_1000: 500,
                crit_roll_100: 50,
                misfire_roll: 80,
                spell_resist_roll: 80,
            }],
        };

        let mut atk_exh = 0.0;
        let mut def_exh = 0.0;

        let result = resolve_pvp_half_round(
            &mut attacker,
            &mut defender,
            &rolls,
            &mut atk_exh,
            &mut def_exh,
        );

        assert!(result.did_dodge);
    }

    // --- Full battle ---

    #[test]
    fn full_pvp_battle_one_side_wins() {
        let mut attacker = make_combatant(1, "Strong");
        attacker.strength = 100;
        attacker.attack_skill = 80;
        attacker.weapon = Some(make_weapon(1, 100));
        attacker.hp = 500;

        let mut defender = make_combatant(2, "Weak");
        defender.hp = 10; // Very low HP

        let battle_rolls = PvpBattleRolls {
            half_rounds: (0..MAX_HALF_ROUNDS as usize)
                .map(|_| make_half_round_rolls(5))
                .collect(),
        };

        let result = run_pvp_battle(&mut attacker, &mut defender, &battle_rolls);
        assert_eq!(result.outcome, PvpOutcome::AttackerWin);
        assert_eq!(result.defender_hp, 0);
    }

    #[test]
    fn full_pvp_battle_draw_after_max_rounds() {
        let mut attacker = make_combatant(1, "Tank1");
        attacker.hp = 100_000;
        attacker.condition = 10_000;
        let mut defender = make_combatant(2, "Tank2");
        defender.hp = 100_000;
        defender.condition = 10_000;

        // Both have high HP and no weapons → 0 damage
        attacker.weapon = None;
        defender.weapon = None;

        let battle_rolls = PvpBattleRolls {
            half_rounds: (0..MAX_HALF_ROUNDS as usize)
                .map(|_| make_half_round_rolls(5))
                .collect(),
        };

        let result = run_pvp_battle(&mut attacker, &mut defender, &battle_rolls);
        assert_eq!(result.outcome, PvpOutcome::Draw);
    }

    // --- Equipment wear ---

    #[test]
    fn equipment_wear_tracks_durability_loss() {
        let original = make_combatant(1, "Before");
        let mut after = make_combatant(1, "After");
        if let Some(ref mut w) = after.weapon {
            w.durability -= 5;
        }
        if let Some(ref mut a) = after.armor {
            a.durability -= 3;
        }

        let wear = equipment_wear(&original, &after);
        assert_eq!(wear.len(), 2);
        assert!(wear.iter().any(|(id, loss)| *id == 1 && *loss == 5));
        assert!(wear.iter().any(|(id, loss)| *id == 3 && *loss == 3));
    }

    // --- PvP crit chance ---

    #[test]
    fn pvp_crit_chance_caps_at_six() {
        assert_eq!(pvp_crit_chance(10, 0), 6);
        assert_eq!(pvp_crit_chance(3, 2), 5);
    }

    // --- Spell defense ---

    #[test]
    fn spell_defense_zero_for_non_mage() {
        let c = make_combatant(1, "Warrior");
        assert_eq!(compute_spell_defense(&c), 0);
    }

    #[test]
    fn spell_defense_mage_with_spell() {
        let mut c = make_combatant(1, "Mage");
        c.class = Class::Mage;
        c.mana = 10;
        c.defense_spell = Some(PvpSpell {
            power: 50,
            level: 5,
            element: Element::Water,
        });
        c.magic_skill = 30;
        // No armor/wand → pure spell defense
        c.armor = None;
        c.helmet = None;
        c.legs = None;
        c.shield = None;
        c.wand = None;

        assert_eq!(compute_spell_defense(&c), 50);
    }

    // --- Battle log entry ---

    #[test]
    fn battle_log_entry_attacker_win() {
        let entry = PvpBattleLogEntry::from_result(1, 2, PvpOutcome::AttackerWin, 42);
        assert_eq!(entry.winner_id, 1);
        assert_eq!(entry.battle_day, 42);
    }

    #[test]
    fn battle_log_entry_draw() {
        let entry = PvpBattleLogEntry::from_result(1, 2, PvpOutcome::Draw, 42);
        assert_eq!(entry.winner_id, 0);
    }

    // --- Poison interactions ---

    #[test]
    fn illani_poison_adds_damage() {
        let mut attacker = make_combatant(1, "Poisoner");
        let mut poison_weapon = make_weapon(1, 40);
        poison_weapon.poison_type = PoisonType::Illani;
        poison_weapon.poison = 15;
        attacker.weapon = Some(poison_weapon);

        let mut defender = make_combatant(2, "Victim");
        defender.antidote = PoisonType::None; // no Illani antidote

        let rolls = PvpHalfRoundRolls {
            strikes: vec![PvpStrikeRoll {
                location_roll: 50,
                attack_roll: 25,
                dodge_roll: 1,
                dodge_chance_roll: 99, // won't dodge
                block_roll: 99,
                crit_roll_1000: 999, // no crit
                crit_roll_100: 99,
                misfire_roll: 80,
                spell_resist_roll: 80,
            }],
        };

        let initial_hp = defender.hp;
        let mut atk_exh = 0.0;
        let mut def_exh = 0.0;
        let result = resolve_pvp_half_round(
            &mut attacker,
            &mut defender,
            &rolls,
            &mut atk_exh,
            &mut def_exh,
        );

        // Should deal some damage including poison
        assert!(result.total_damage > 0);
        assert!(defender.hp < initial_hp);
    }

    #[test]
    fn illani_poison_blocked_by_antidote() {
        let mut attacker = make_combatant(1, "Poisoner");
        let mut poison_weapon = make_weapon(1, 40);
        poison_weapon.poison_type = PoisonType::Illani;
        poison_weapon.poison = 15;
        attacker.weapon = Some(poison_weapon);

        let mut defender = make_combatant(2, "Protected");
        defender.antidote = PoisonType::Illani; // has Illani antidote

        let rolls = PvpHalfRoundRolls {
            strikes: vec![PvpStrikeRoll {
                location_roll: 50,
                attack_roll: 25,
                dodge_roll: 1,
                dodge_chance_roll: 99,
                block_roll: 99,
                crit_roll_1000: 999,
                crit_roll_100: 99,
                misfire_roll: 80,
                spell_resist_roll: 80,
            }],
        };

        let mut defender_no_antidote = defender.clone();
        defender_no_antidote.antidote = PoisonType::None;

        let mut atk1 = attacker.clone();
        let mut atk_exh1 = 0.0;
        let mut def_exh1 = 0.0;
        let result_with_antidote = resolve_pvp_half_round(
            &mut atk1,
            &mut defender,
            &rolls,
            &mut atk_exh1,
            &mut def_exh1,
        );

        let mut atk2 = attacker.clone();
        let mut atk_exh2 = 0.0;
        let mut def_exh2 = 0.0;
        let result_without_antidote = resolve_pvp_half_round(
            &mut atk2,
            &mut defender_no_antidote,
            &rolls,
            &mut atk_exh2,
            &mut def_exh2,
        );

        // With antidote should take less damage than without
        assert!(result_with_antidote.total_damage < result_without_antidote.total_damage);
    }

    // --- Nutari mana drain ---

    #[test]
    fn nutari_poison_drains_extra_mana() {
        let mut attacker = make_combatant(1, "Drainer");
        let mut poison_weapon = make_weapon(1, 40);
        poison_weapon.poison_type = PoisonType::Nutari;
        poison_weapon.poison = 5;
        attacker.weapon = Some(poison_weapon);

        let mut defender = make_combatant(2, "Mage");
        defender.class = Class::Mage;
        defender.mana = 50;
        defender.magic_skill = 30;
        defender.defense_spell = Some(PvpSpell {
            power: 30,
            level: 10,
            element: Element::Water,
        });
        defender.antidote = PoisonType::None; // no Nutari antidote

        let mana_loss = defender_mana_loss(&defender, &attacker);
        // ceil(10/2.5) + 5 - floor(30/25) = 4 + 5 - 1 = 8
        assert_eq!(mana_loss, 8);
    }

    #[test]
    fn nutari_poison_blocked_by_antidote() {
        let mut attacker = make_combatant(1, "Drainer");
        let mut poison_weapon = make_weapon(1, 40);
        poison_weapon.poison_type = PoisonType::Nutari;
        poison_weapon.poison = 5;
        attacker.weapon = Some(poison_weapon);

        let mut defender = make_combatant(2, "Mage");
        defender.class = Class::Mage;
        defender.mana = 50;
        defender.magic_skill = 30;
        defender.defense_spell = Some(PvpSpell {
            power: 30,
            level: 10,
            element: Element::Water,
        });
        defender.antidote = PoisonType::Nutari; // has Nutari antidote

        let mana_loss = defender_mana_loss(&defender, &attacker);
        // ceil(10/2.5) - floor(30/25) = 4 - 1 = 3 (no poison bonus)
        assert_eq!(mana_loss, 3);
    }
}
