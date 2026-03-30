//! `PvE` battle execution — turn-based state machine.
//!
//! Ported from PHP `includes/turnfight.php`.  The design preserves
//! user-visible behaviour (action points, stances, multi-monster,
//! 24-round limit, spell failure, equipment wear) while expressing the
//! state machine as an explicit, testable Rust type.
//!
//! **All functions are pure** — callers inject RNG rolls and equipment
//! snapshots so the domain layer stays deterministic and testable.

use crate::combat::formulas::{
    self, AttackStance, AttackType, BaseDamage, CombatXpShares, DamageContext, HitLocation,
    SpellMisfire,
};
use crate::item::{Element, OwnedEquipment, Spell};
use crate::player::Class;
use crate::player::bonuses::PlayerBonus;
use crate::player::skills::PlayerSkill;
use crate::player::stats::PlayerStat;

// ---------------------------------------------------------------------------
// Constants
// ---------------------------------------------------------------------------

/// Maximum rounds before the fight is declared a draw.
pub const MAX_ROUNDS: i32 = 24;

// ---------------------------------------------------------------------------
// Player action
// ---------------------------------------------------------------------------

/// An action the player can take on their turn.
#[derive(Debug, Clone, Copy, PartialEq, Eq)]
pub enum PlayerAction {
    /// Melee/ranged attack with current weapon.
    Attack(AttackStance),
    /// Cast a battle spell (1 AP).
    CastSpell,
    /// Burst-cast a battle spell (2 AP, adds bonus from magic skill).
    BurstSpell { burst_power: i32 },
    /// Cast a defensive burst spell (2 AP, shields against incoming damage).
    DefensiveBurstSpell { burst_power: i32 },
    /// Drink a potion (1 AP).
    DrinkPotion,
    /// Equip new arrows from inventory (1 AP).
    EquipArrows,
    /// Switch weapon mid-fight (2 AP).
    EquipWeapon,
    /// Attempt to escape the fight.
    Escape,
    /// Rest to reduce exhaustion.
    Rest,
}

impl PlayerAction {
    /// Action-point cost for the action.
    pub fn ap_cost(self) -> i32 {
        match self {
            Self::Attack(stance) => formulas::stance_ap_cost(stance),
            Self::CastSpell | Self::DrinkPotion | Self::EquipArrows => 1,
            Self::BurstSpell { .. } | Self::DefensiveBurstSpell { .. } | Self::EquipWeapon => 2,
            Self::Escape | Self::Rest => 0,
        }
    }
}

// ---------------------------------------------------------------------------
// Battle outcome
// ---------------------------------------------------------------------------

/// Top-level outcome of a battle.
#[derive(Debug, Clone, Copy, PartialEq, Eq)]
pub enum BattleOutcome {
    /// Fight still in progress — more rounds remain.
    InProgress,
    /// Player killed all monsters.
    Victory,
    /// Player HP reached zero.
    Defeat,
    /// Round limit reached without resolution.
    Draw,
    /// Player successfully escaped.
    Escaped,
}

// ---------------------------------------------------------------------------
// Monster combat state
// ---------------------------------------------------------------------------

/// Per-monster mutable state during a fight.
#[derive(Debug, Clone)]
pub struct MonsterCombatState {
    pub current_hp: i32,
    pub max_hp: i32,
    /// Monster base damage (pre-roll).
    pub base_damage: i32,
    pub speed: i32,
    pub level: i32,
    pub agility: i32,
    pub dmg_element: Element,
    pub name: String,
}

impl MonsterCombatState {
    pub fn is_alive(&self) -> bool {
        self.current_hp > 0
    }
}

// ---------------------------------------------------------------------------
// Battle state
// ---------------------------------------------------------------------------

/// Full mutable state of a `PvE` battle.
#[derive(Debug, Clone)]
pub struct BattleState {
    pub round: i32,
    pub action_points: i32,
    pub max_action_points: i32,
    pub exhaustion: f64,
    pub player_hp: i32,
    pub player_mana: i32,
    pub monsters: Vec<MonsterCombatState>,
    pub outcome: BattleOutcome,
    /// Tracks whether the player attacked (melee/ranged) this fight.
    pub did_attack: bool,
    /// Tracks whether the player dodged in this fight.
    pub did_dodge: bool,
    /// Tracks whether the player cast a spell in this fight.
    pub did_cast_spell: bool,
    /// Attack type last used (for XP distribution).
    pub last_attack_type: AttackType,
    /// Currently active defensive spell reduction (from defensive burst).
    pub defensive_spell_reduction: f64,
}

impl BattleState {
    /// Create a new battle with the given monsters.
    ///
    /// `action_points_per_round` is typically `ceil(player_speed / avg enemy speed)`, max 5.
    pub fn new(
        player_hp: i32,
        player_mana: i32,
        action_points_per_round: i32,
        monsters: Vec<MonsterCombatState>,
    ) -> Self {
        Self {
            round: 1,
            action_points: action_points_per_round,
            max_action_points: action_points_per_round,
            exhaustion: 0.0,
            player_hp,
            player_mana,
            monsters,
            outcome: BattleOutcome::InProgress,
            did_attack: false,
            did_dodge: false,
            did_cast_spell: false,
            last_attack_type: AttackType::Melee,
            defensive_spell_reduction: 0.0,
        }
    }

    /// How many monsters are still alive.
    pub fn alive_count(&self) -> usize {
        self.monsters.iter().filter(|m| m.is_alive()).count()
    }

    /// Check if all monsters are dead.
    pub fn all_monsters_dead(&self) -> bool {
        self.alive_count() == 0
    }

    /// Advance to the next round.
    pub fn next_round(&mut self) {
        self.round += 1;
        self.action_points = self.max_action_points;

        if self.round > MAX_ROUNDS {
            self.outcome = BattleOutcome::Draw;
        }
    }
}

// ---------------------------------------------------------------------------
// RNG rolls passed in by callers
// ---------------------------------------------------------------------------

/// All random values needed to resolve a player attack action.
#[derive(Debug, Clone)]
pub struct AttackRolls {
    /// `rand(1, skill_level)` — added to damage.
    pub skill_roll: i32,
    /// Wand roll for spell attacks (0 if no wand).
    pub wand_roll: i32,
    /// Critical-hit check: `rand(1, 1000)`.
    pub crit_roll_large: i32,
    /// Critical-hit check: `rand(1, 100)`.
    pub crit_roll_small: i32,
    /// Monster dodge check roll.
    pub dodge_roll: i32,
    /// Which monster to target (0-indexed).
    pub target_monster: usize,
}

/// All random values needed to resolve a spell cast.
#[derive(Debug, Clone)]
pub struct SpellRolls {
    /// Misfire check: `rand(1, 100)`.
    pub fizzle_roll: i32,
    /// Misfire outcome: `rand(1, 100)`.
    pub misfire_roll: i32,
    /// `rand(1, magic_skill)` — added to damage.
    pub skill_roll: i32,
    /// Wand roll (0 if no wand).
    pub wand_roll: i32,
    /// Critical-hit check: `rand(1, 1000)`.
    pub crit_roll_large: i32,
    /// Critical-hit check: `rand(1, 100)`.
    pub crit_roll_small: i32,
    /// Monster dodge check.
    pub dodge_roll: i32,
    /// Target monster (0-indexed).
    pub target_monster: usize,
}

/// Random values for escape attempt.
#[derive(Debug, Clone)]
pub struct EscapeRolls {
    pub player_roll: i32,
    pub monster_roll: i32,
}

/// Random values for a single monster attack against the player.
#[derive(Debug, Clone)]
pub struct MonsterAttackRoll {
    /// Monster per-attack damage roll: `rand(0, level)`.
    pub damage_roll: i32,
    /// Player dodge check roll.
    pub dodge_roll: i32,
    /// Shield block check: `rand(1, 100)`.
    pub block_roll: i32,
    /// Hit location: `rand(1, 100)`.
    pub hit_location_roll: i32,
}

/// All rolls for a complete monster turn (all alive monsters, all their attacks).
#[derive(Debug, Clone)]
pub struct MonsterTurnRolls {
    /// Outer vec = per monster, inner vec = per attack.
    pub attacks: Vec<Vec<MonsterAttackRoll>>,
}

// ---------------------------------------------------------------------------
// Action results (returned to callers)
// ---------------------------------------------------------------------------

/// Result of a player attack action.
#[derive(Debug, Clone)]
pub struct AttackResult {
    pub damage_dealt: i32,
    pub target_index: usize,
    pub target_killed: bool,
    pub was_critical: bool,
    pub was_dodged: bool,
    pub attack_type: AttackType,
    pub fatigue_added: f64,
}

/// Result of a spell cast action.
#[derive(Debug, Clone)]
pub struct SpellResult {
    pub damage_dealt: i32,
    pub target_index: usize,
    pub target_killed: bool,
    pub was_critical: bool,
    pub was_dodged: bool,
    pub misfire: Option<SpellMisfire>,
    pub mana_cost: i32,
    pub self_damage: i32,
}

/// Result of an escape attempt.
#[derive(Debug, Clone, Copy)]
pub struct EscapeResult {
    pub succeeded: bool,
}

/// A single incoming hit from a monster.
#[derive(Debug, Clone)]
pub struct MonsterHitResult {
    pub monster_index: usize,
    pub damage_dealt: i32,
    pub was_dodged: bool,
    pub was_blocked: bool,
    pub hit_location: HitLocation,
    /// Mana lost from maintaining a defensive spell while hit.
    pub mana_lost: i32,
    /// Armor slot that took durability wear (if any).
    pub armor_slot_worn: Option<HitLocation>,
}

/// Result of the full monster turn (all monster attacks for the round).
#[derive(Debug, Clone)]
pub struct MonsterTurnResult {
    pub hits: Vec<MonsterHitResult>,
    pub total_damage: i32,
    pub player_dodged_any: bool,
}

// ---------------------------------------------------------------------------
// Action validation
// ---------------------------------------------------------------------------

/// Reasons why a player action is invalid.
#[derive(Debug, Clone, Copy, PartialEq, Eq)]
pub enum ActionError {
    BattleNotInProgress,
    InsufficientActionPoints,
    StanceNotAvailable,
    DefensiveStanceNotAvailable,
    AggressiveStanceNotAvailable,
    NoWeapon,
    NoSpell,
    InsufficientMana,
    TargetDead,
    TargetOutOfRange,
}

/// Inputs for action validation (avoids long parameter list).
pub struct ActionValidationCtx {
    pub class: Class,
    pub has_weapon: bool,
    pub has_spell: bool,
    pub current_mana: i32,
    pub spell_mana_cost: i32,
    pub target_monster: usize,
}

/// Check whether a player action is valid in the current state.
pub fn validate_action(
    state: &BattleState,
    action: PlayerAction,
    ctx: &ActionValidationCtx,
) -> Result<(), ActionError> {
    if state.outcome != BattleOutcome::InProgress {
        return Err(ActionError::BattleNotInProgress);
    }

    if !matches!(action, PlayerAction::Escape | PlayerAction::Rest)
        && state.action_points < action.ap_cost()
    {
        return Err(ActionError::InsufficientActionPoints);
    }

    match action {
        PlayerAction::Attack(stance) => {
            if !ctx.has_weapon {
                return Err(ActionError::NoWeapon);
            }
            validate_stance(stance, &ctx.class)?;
            validate_target(state, ctx.target_monster)?;
        }
        PlayerAction::CastSpell | PlayerAction::BurstSpell { .. } => {
            validate_spell_prereqs(ctx)?;
            validate_target(state, ctx.target_monster)?;
        }
        PlayerAction::DefensiveBurstSpell { .. } => {
            validate_spell_prereqs(ctx)?;
        }
        PlayerAction::Escape
        | PlayerAction::Rest
        | PlayerAction::DrinkPotion
        | PlayerAction::EquipArrows
        | PlayerAction::EquipWeapon => {}
    }

    Ok(())
}

fn validate_stance(stance: AttackStance, class: &Class) -> Result<(), ActionError> {
    match stance {
        AttackStance::Berserker => {
            if !matches!(class, Class::Warrior | Class::Barbarian) {
                return Err(ActionError::StanceNotAvailable);
            }
        }
        AttackStance::Defensive => {
            if *class != Class::Warrior {
                return Err(ActionError::DefensiveStanceNotAvailable);
            }
        }
        AttackStance::Aggressive => {
            if !matches!(class, Class::Warrior | Class::Barbarian) {
                return Err(ActionError::AggressiveStanceNotAvailable);
            }
        }
        AttackStance::Normal => {}
    }
    Ok(())
}

fn validate_spell_prereqs(ctx: &ActionValidationCtx) -> Result<(), ActionError> {
    if !ctx.has_spell {
        return Err(ActionError::NoSpell);
    }
    if ctx.current_mana < ctx.spell_mana_cost {
        return Err(ActionError::InsufficientMana);
    }
    Ok(())
}

fn validate_target(state: &BattleState, target: usize) -> Result<(), ActionError> {
    if target >= state.monsters.len() {
        return Err(ActionError::TargetOutOfRange);
    }
    if !state.monsters[target].is_alive() {
        return Err(ActionError::TargetDead);
    }
    Ok(())
}

// ---------------------------------------------------------------------------
// Action resolution
// ---------------------------------------------------------------------------

/// Resolve a player melee/ranged attack.
///
/// Applies damage to the target monster, handles dodge, critical hit, and
/// equipment fatigue. Returns the result and mutates `state`.
#[allow(clippy::cast_possible_truncation)]
pub fn resolve_attack(
    state: &mut BattleState,
    ctx: &DamageContext<'_>,
    stance: AttackStance,
    rolls: &AttackRolls,
    weapon_weight: i32,
) -> AttackResult {
    let base = formulas::player_base_damage(ctx);
    let stance_dmg = formulas::stance_damage_modifier(base.damage, stance);
    let modified_base = BaseDamage {
        damage: stance_dmg,
        crit_chance: base.crit_chance,
        attack_type: base.attack_type,
        skill_key: base.skill_key,
    };

    let target = rolls
        .target_monster
        .min(state.monsters.len().saturating_sub(1));
    let monster = &state.monsters[target];

    // Compute enemy dodge
    let player_agility = formulas::stat_modified(ctx.stats, "agility");
    let combat_skill = formulas::skill_level(ctx.skills, base.skill_key);
    let is_ranged = base.attack_type == AttackType::Ranged;
    let eagle_eye = formulas::bonus_value("eagleeye", ctx.stats, ctx.skills, ctx.bonuses);

    let monster_dodge_val = formulas::monster_dodge(
        ctx.class,
        player_agility,
        monster.agility,
        combat_skill,
        ctx.second_weapon.is_some(),
        formulas::skill_level(ctx.skills, "attack"),
        is_ranged,
        eagle_eye,
    );
    let monster_dodge_val = formulas::stance_monster_dodge_modifier(monster_dodge_val, stance);

    let dodge_max = formulas::monster_dodge_max(player_agility, combat_skill);
    let condition = formulas::stat_modified(ctx.stats, "condition");
    let exhaustion_ok = !formulas::is_exhausted(state.exhaustion, condition);

    let dodged = formulas::dodge_check(
        monster_dodge_val,
        dodge_max,
        rolls.dodge_roll,
        exhaustion_ok,
    );

    let fatigue = formulas::attack_fatigue_cost(weapon_weight);
    state.exhaustion += fatigue;

    if dodged {
        state.action_points -= formulas::stance_ap_cost(stance);
        state.did_attack = true;
        state.last_attack_type = base.attack_type;
        return AttackResult {
            damage_dealt: 0,
            target_index: target,
            target_killed: false,
            was_critical: false,
            was_dodged: true,
            attack_type: base.attack_type,
            fatigue_added: fatigue,
        };
    }

    let mut final_dmg = formulas::finalize_damage(
        &modified_base,
        rolls.skill_roll,
        rolls.wand_roll,
        monster.base_damage,
        monster.current_hp,
    );

    let is_crit = formulas::is_critical_hit(
        modified_base.crit_chance,
        rolls.crit_roll_large,
        rolls.crit_roll_small,
    );

    if is_crit {
        final_dmg =
            ((f64::from(final_dmg) * 1.5).ceil() as i32).min(state.monsters[target].current_hp);
    }

    state.monsters[target].current_hp -= final_dmg;
    let killed = state.monsters[target].current_hp <= 0;
    if killed {
        state.monsters[target].current_hp = 0;
    }

    state.action_points -= formulas::stance_ap_cost(stance);
    state.did_attack = true;
    state.last_attack_type = base.attack_type;

    if state.all_monsters_dead() {
        state.outcome = BattleOutcome::Victory;
    }

    AttackResult {
        damage_dealt: final_dmg,
        target_index: target,
        target_killed: killed,
        was_critical: is_crit,
        was_dodged: false,
        attack_type: base.attack_type,
        fatigue_added: fatigue,
    }
}

/// Resolve a spell cast action.
///
/// Handles spell fizzle, misfire outcomes, damage, and mana cost.
#[allow(clippy::cast_possible_truncation)]
pub fn resolve_spell(
    state: &mut BattleState,
    spell: &Spell,
    magic_skill: i32,
    rolls: &SpellRolls,
    burst_power: i32,
    monster_endurance: i32,
) -> SpellResult {
    let mana_cost = spell.level;
    let target = rolls
        .target_monster
        .min(state.monsters.len().saturating_sub(1));
    let ap_cost = if burst_power > 0 { 2 } else { 1 };

    // Check for spell fizzle
    if formulas::spell_fizzles(magic_skill, spell.level, rolls.fizzle_roll) {
        let misfire = formulas::spell_misfire_outcome(rolls.misfire_roll);
        let (self_damage, lost_mana) = apply_misfire(state, misfire, spell, magic_skill);

        state.action_points -= ap_cost;
        state.did_cast_spell = true;

        return SpellResult {
            damage_dealt: 0,
            target_index: target,
            target_killed: false,
            was_critical: false,
            was_dodged: false,
            misfire: Some(misfire),
            mana_cost: lost_mana,
            self_damage,
        };
    }

    // Normal spell resolution
    let base_damage = spell.multiplier * f64::from(magic_skill) + f64::from(burst_power);
    let crit_chance = magic_skill.min(6);
    let is_crit =
        formulas::is_critical_hit(crit_chance, rolls.crit_roll_large, rolls.crit_roll_small);

    let mut final_dmg = base_damage + f64::from(rolls.skill_roll) + f64::from(rolls.wand_roll)
        - f64::from(monster_endurance);

    if is_crit {
        final_dmg *= 1.5;
    }

    let mut dmg = final_dmg.floor().max(0.0) as i32;
    dmg = dmg.min(state.monsters[target].current_hp);

    state.monsters[target].current_hp -= dmg;
    let killed = state.monsters[target].current_hp <= 0;
    if killed {
        state.monsters[target].current_hp = 0;
    }

    state.player_mana -= mana_cost;
    state.action_points -= ap_cost;
    state.did_cast_spell = true;

    if state.all_monsters_dead() {
        state.outcome = BattleOutcome::Victory;
    }

    SpellResult {
        damage_dealt: dmg,
        target_index: target,
        target_killed: killed,
        was_critical: is_crit,
        was_dodged: false,
        misfire: None,
        mana_cost,
        self_damage: 0,
    }
}

/// Apply misfire effects and return `(self_damage, mana_lost)`.
#[allow(clippy::cast_possible_truncation)]
fn apply_misfire(
    state: &mut BattleState,
    misfire: SpellMisfire,
    spell: &Spell,
    magic_skill: i32,
) -> (i32, i32) {
    match misfire {
        SpellMisfire::LoseOneMana => {
            state.player_mana = (state.player_mana - 1).max(0);
            (0, 1)
        }
        SpellMisfire::LoseConcentration | SpellMisfire::PartialHit { .. } => (0, 0),
        SpellMisfire::LoseAllMana => {
            let lost = state.player_mana;
            state.player_mana = 0;
            (0, lost)
        }
        SpellMisfire::SelfDamage => {
            let dmg = (spell.multiplier * f64::from(magic_skill)).ceil() as i32;
            let dmg = dmg.min(state.player_hp);
            state.player_hp -= dmg;
            if state.player_hp <= 0 {
                state.outcome = BattleOutcome::Defeat;
            }
            (dmg, 0)
        }
        SpellMisfire::PartialHitAndSelfDamage { damage_percent } => {
            let base = (spell.multiplier * f64::from(magic_skill)).ceil() as i32;
            let self_dmg = (base * damage_percent / 100).max(1).min(state.player_hp);
            state.player_hp -= self_dmg;
            if state.player_hp <= 0 {
                state.outcome = BattleOutcome::Defeat;
            }
            (self_dmg, 0)
        }
    }
}

/// Resolve an escape attempt.
pub fn resolve_escape(
    state: &mut BattleState,
    player_speed: i32,
    perception_skill: i32,
    rolls: &EscapeRolls,
    avg_monster_speed: i32,
) -> EscapeResult {
    let succeeded = formulas::escape_chance(
        player_speed,
        perception_skill,
        rolls.player_roll,
        avg_monster_speed,
        rolls.monster_roll,
    );

    if succeeded {
        state.outcome = BattleOutcome::Escaped;
    }

    EscapeResult { succeeded }
}

/// Resolve a rest action — reduces exhaustion by condition/10.
pub fn resolve_rest(state: &mut BattleState, condition: i32) {
    let recovery = formulas::rest_recovery(condition);
    state.exhaustion = (state.exhaustion - recovery).max(0.0);
}

// ---------------------------------------------------------------------------
// Monster turn
// ---------------------------------------------------------------------------

/// Resolve the monster turn: each alive monster attacks `attacks_per_round` times.
///
/// `active_def_spell`: optional defensive spell currently active on the player.
/// `shield`: player's shield for block checks.
/// `armor_pieces`: armor at [Head, Body, Legs, Arms] — 4 slots.
/// `player_dodge_value`: pre-computed from `formulas::player_dodge()`.
/// `stance`: the stance used by the player this round (affects dodge).
///
/// Context is bundled into [`MonsterTurnCtx`] to keep the signature short.
pub fn resolve_monster_turn(
    state: &mut BattleState,
    ctx: &MonsterTurnCtx<'_>,
    rolls: &MonsterTurnRolls,
) -> MonsterTurnResult {
    let alive_count = state.alive_count().max(1);
    #[allow(clippy::cast_possible_wrap, clippy::cast_possible_truncation)]
    let adjusted_dodge = if alive_count > 1 {
        (ctx.player_dodge_value / alive_count as i32).max(1)
    } else {
        ctx.player_dodge_value
    };
    let adjusted_dodge = formulas::stance_player_dodge_modifier(adjusted_dodge, ctx.stance);

    let block_chance = formulas::shield_block_chance(ctx.shield);
    let condition = formulas::stat_modified(ctx.stats, "condition");

    let mut hits = Vec::new();
    let mut total_damage = 0;
    let mut player_dodged_any = false;

    for mi in 0..state.monsters.len() {
        if !state.monsters[mi].is_alive() {
            continue;
        }

        let Some(attack_rolls) = rolls.attacks.get(mi) else {
            continue;
        };

        // Copy monster combat values before borrowing state mutably.
        let monster_snapshot = state.monsters[mi].clone();

        #[allow(clippy::cast_sign_loss)]
        let max_attacks = ctx.monster_attacks_per_round as usize;

        for (ai, roll) in attack_rolls.iter().enumerate() {
            if ai >= max_attacks {
                break;
            }

            let hit = resolve_single_monster_attack(
                state,
                ctx,
                mi,
                &monster_snapshot,
                roll,
                adjusted_dodge,
                block_chance,
                condition,
            );
            if hit.was_dodged {
                player_dodged_any = true;
            }
            total_damage += hit.damage_dealt;
            hits.push(hit);
        }
    }

    if state.player_hp <= 0 {
        state.player_hp = 0;
        state.outcome = BattleOutcome::Defeat;
    }

    MonsterTurnResult {
        hits,
        total_damage,
        player_dodged_any,
    }
}

/// Context for [`resolve_monster_turn`].
pub struct MonsterTurnCtx<'a> {
    pub player_dodge_value: i32,
    pub stance: AttackStance,
    pub shield: Option<&'a OwnedEquipment>,
    /// Armor at `[Head, Body, Legs, Arms]`.
    pub armor_pieces: [Option<&'a OwnedEquipment>; 4],
    pub stats: &'a [PlayerStat],
    pub skills: &'a [PlayerSkill],
    pub bonuses: &'a [PlayerBonus],
    pub monster_attacks_per_round: i32,
    pub active_def_spell: Option<&'a Spell>,
}

/// Process one attack from one monster.
#[allow(clippy::too_many_arguments, clippy::cast_possible_truncation)]
fn resolve_single_monster_attack(
    state: &mut BattleState,
    ctx: &MonsterTurnCtx<'_>,
    mi: usize,
    monster: &MonsterCombatState,
    roll: &MonsterAttackRoll,
    adjusted_dodge: i32,
    block_chance: i32,
    condition: i32,
) -> MonsterHitResult {
    // Dodge check
    let dodge_max = formulas::player_dodge_max(monster.agility);
    let exhaustion_ok = !formulas::is_exhausted(state.exhaustion, condition);
    if formulas::dodge_check(adjusted_dodge, dodge_max, roll.dodge_roll, exhaustion_ok) {
        state.did_dodge = true;
        if let Some(armor) = ctx.armor_pieces[1] {
            state.exhaustion += formulas::dodge_fatigue_cost(armor.agility_mod);
        }
        return MonsterHitResult {
            monster_index: mi,
            damage_dealt: 0,
            was_dodged: true,
            was_blocked: false,
            hit_location: HitLocation::Body,
            mana_lost: 0,
            armor_slot_worn: None,
        };
    }

    // Shield block
    if formulas::shield_block_check(block_chance, roll.block_roll) {
        if let Some(s) = ctx.shield {
            state.exhaustion += formulas::dodge_fatigue_cost(s.agility_mod);
        }
        return MonsterHitResult {
            monster_index: mi,
            damage_dealt: 0,
            was_dodged: false,
            was_blocked: true,
            hit_location: HitLocation::Body,
            mana_lost: 0,
            armor_slot_worn: None,
        };
    }

    // Damage calculation
    let mut dmg = formulas::monster_damage_with_roll(monster.base_damage, roll.damage_roll);

    // Defensive spell reduction
    if state.defensive_spell_reduction > 0.0 {
        dmg = (f64::from(dmg) - state.defensive_spell_reduction).max(1.0) as i32;
    }

    // Armor defense at hit location
    let hit_loc = HitLocation::from_roll(roll.hit_location_roll);
    let slot_idx = hit_loc.armor_slot_offset();
    let armor_def = formulas::armor_defense_at_location(
        ctx.armor_pieces[slot_idx],
        0, // pet defense handled separately
        formulas::skill_level(ctx.skills, "dodge"),
        ctx.stats,
        ctx.skills,
        ctx.bonuses,
        monster.dmg_element,
    );

    dmg = (f64::from(dmg) - armor_def).max(1.0) as i32;

    state.player_hp -= dmg;

    // Mana loss from maintaining defensive spell
    let mana_lost = if let Some(def_spell) = ctx.active_def_spell {
        if state.player_mana >= def_spell.level {
            let lost = formulas::mana_loss_on_hit(
                def_spell.level,
                formulas::skill_level(ctx.skills, "magic"),
            );
            state.player_mana = (state.player_mana - lost).max(0);
            lost
        } else {
            0
        }
    } else {
        0
    };

    let armor_worn = ctx.armor_pieces[slot_idx].map(|_| hit_loc);

    MonsterHitResult {
        monster_index: mi,
        damage_dealt: dmg,
        was_dodged: false,
        was_blocked: false,
        hit_location: hit_loc,
        mana_lost,
        armor_slot_worn: armor_worn,
    }
}

// ---------------------------------------------------------------------------
// Victory / defeat helpers
// ---------------------------------------------------------------------------

/// Calculate XP shares after a fight.
pub fn combat_xp_distribution(total_xp: i32, state: &BattleState) -> CombatXpShares {
    formulas::distribute_combat_xp(
        total_xp,
        state.did_dodge,
        state.did_attack,
        state.did_cast_spell,
        state.last_attack_type,
    )
}

/// Collect equipment durability wear from monster-turn hits.
pub fn equipment_wear_from_hits(result: &MonsterTurnResult) -> Vec<(HitLocation, i32)> {
    result
        .hits
        .iter()
        .filter_map(|hit| hit.armor_slot_worn.map(|loc| (loc, 1)))
        .collect()
}

// ---------------------------------------------------------------------------
// Tests
// ---------------------------------------------------------------------------

#[cfg(test)]
mod tests {
    use super::*;
    use crate::combat::formulas::AttackStance;

    fn make_monster(hp: i32, base_damage: i32, speed: i32) -> MonsterCombatState {
        MonsterCombatState {
            current_hp: hp,
            max_hp: hp,
            base_damage,
            speed,
            level: 10,
            agility: 50,
            dmg_element: Element::None,
            name: "Test Monster".to_owned(),
        }
    }

    fn make_state(
        player_hp: i32,
        player_mana: i32,
        monsters: Vec<MonsterCombatState>,
    ) -> BattleState {
        BattleState::new(player_hp, player_mana, 3, monsters)
    }

    // --- BattleState basics ---

    #[test]
    fn new_battle_starts_in_progress() {
        let state = make_state(100, 50, vec![make_monster(50, 10, 20)]);
        assert_eq!(state.outcome, BattleOutcome::InProgress);
        assert_eq!(state.round, 1);
        assert_eq!(state.action_points, 3);
    }

    #[test]
    fn alive_count_tracks_monsters() {
        let mut state = make_state(
            100,
            50,
            vec![make_monster(50, 10, 20), make_monster(30, 5, 15)],
        );
        assert_eq!(state.alive_count(), 2);

        state.monsters[0].current_hp = 0;
        assert_eq!(state.alive_count(), 1);
        assert!(!state.all_monsters_dead());

        state.monsters[1].current_hp = 0;
        assert!(state.all_monsters_dead());
    }

    #[test]
    fn next_round_advances_and_refills_ap() {
        let mut state = make_state(100, 50, vec![make_monster(50, 10, 20)]);
        state.action_points = 0;
        state.next_round();
        assert_eq!(state.round, 2);
        assert_eq!(state.action_points, 3);
    }

    #[test]
    fn round_limit_triggers_draw() {
        let mut state = make_state(100, 50, vec![make_monster(50, 10, 20)]);
        for _ in 0..MAX_ROUNDS {
            state.next_round();
        }
        assert_eq!(state.outcome, BattleOutcome::Draw);
    }

    // --- PlayerAction AP cost ---

    #[test]
    fn action_ap_costs() {
        assert_eq!(PlayerAction::Attack(AttackStance::Normal).ap_cost(), 1);
        assert_eq!(PlayerAction::Attack(AttackStance::Aggressive).ap_cost(), 1);
        assert_eq!(PlayerAction::Attack(AttackStance::Defensive).ap_cost(), 1);
        assert_eq!(PlayerAction::Attack(AttackStance::Berserker).ap_cost(), 2);
        assert_eq!(PlayerAction::CastSpell.ap_cost(), 1);
        assert_eq!(PlayerAction::BurstSpell { burst_power: 10 }.ap_cost(), 2);
        assert_eq!(
            PlayerAction::DefensiveBurstSpell { burst_power: 10 }.ap_cost(),
            2
        );
        assert_eq!(PlayerAction::DrinkPotion.ap_cost(), 1);
        assert_eq!(PlayerAction::EquipArrows.ap_cost(), 1);
        assert_eq!(PlayerAction::EquipWeapon.ap_cost(), 2);
        assert_eq!(PlayerAction::Escape.ap_cost(), 0);
        assert_eq!(PlayerAction::Rest.ap_cost(), 0);
    }

    // --- Validation ---

    #[test]
    fn validate_rejects_when_not_in_progress() {
        let mut state = make_state(100, 50, vec![make_monster(50, 10, 20)]);
        state.outcome = BattleOutcome::Victory;

        let err = validate_action(
            &state,
            PlayerAction::Attack(AttackStance::Normal),
            &ActionValidationCtx {
                class: Class::Warrior,
                has_weapon: true,
                has_spell: false,
                current_mana: 50,
                spell_mana_cost: 0,
                target_monster: 0,
            },
        );
        assert_eq!(err, Err(ActionError::BattleNotInProgress));
    }

    #[test]
    fn validate_rejects_insufficient_ap() {
        let mut state = make_state(100, 50, vec![make_monster(50, 10, 20)]);
        state.action_points = 1;

        let err = validate_action(
            &state,
            PlayerAction::Attack(AttackStance::Berserker),
            &ActionValidationCtx {
                class: Class::Warrior,
                has_weapon: true,
                has_spell: false,
                current_mana: 50,
                spell_mana_cost: 0,
                target_monster: 0,
            },
        );
        assert_eq!(err, Err(ActionError::InsufficientActionPoints));
    }

    #[test]
    fn validate_rejects_no_weapon() {
        let state = make_state(100, 50, vec![make_monster(50, 10, 20)]);
        let err = validate_action(
            &state,
            PlayerAction::Attack(AttackStance::Normal),
            &ActionValidationCtx {
                class: Class::Warrior,
                has_weapon: false,
                has_spell: false,
                current_mana: 50,
                spell_mana_cost: 0,
                target_monster: 0,
            },
        );
        assert_eq!(err, Err(ActionError::NoWeapon));
    }

    #[test]
    fn validate_rejects_berserker_for_mage() {
        let state = make_state(100, 50, vec![make_monster(50, 10, 20)]);
        let err = validate_action(
            &state,
            PlayerAction::Attack(AttackStance::Berserker),
            &ActionValidationCtx {
                class: Class::Mage,
                has_weapon: true,
                has_spell: false,
                current_mana: 50,
                spell_mana_cost: 0,
                target_monster: 0,
            },
        );
        assert_eq!(err, Err(ActionError::StanceNotAvailable));
    }

    #[test]
    fn validate_rejects_defensive_for_barbarian() {
        let state = make_state(100, 50, vec![make_monster(50, 10, 20)]);
        let err = validate_action(
            &state,
            PlayerAction::Attack(AttackStance::Defensive),
            &ActionValidationCtx {
                class: Class::Barbarian,
                has_weapon: true,
                has_spell: false,
                current_mana: 50,
                spell_mana_cost: 0,
                target_monster: 0,
            },
        );
        assert_eq!(err, Err(ActionError::DefensiveStanceNotAvailable));
    }

    #[test]
    fn validate_rejects_dead_target() {
        let mut state = make_state(
            100,
            50,
            vec![make_monster(50, 10, 20), make_monster(30, 5, 15)],
        );
        state.monsters[0].current_hp = 0;

        let err = validate_action(
            &state,
            PlayerAction::Attack(AttackStance::Normal),
            &ActionValidationCtx {
                class: Class::Warrior,
                has_weapon: true,
                has_spell: false,
                current_mana: 50,
                spell_mana_cost: 0,
                target_monster: 0,
            },
        );
        assert_eq!(err, Err(ActionError::TargetDead));
    }

    #[test]
    fn validate_rejects_out_of_range_target() {
        let state = make_state(100, 50, vec![make_monster(50, 10, 20)]);
        let err = validate_action(
            &state,
            PlayerAction::Attack(AttackStance::Normal),
            &ActionValidationCtx {
                class: Class::Warrior,
                has_weapon: true,
                has_spell: false,
                current_mana: 50,
                spell_mana_cost: 0,
                target_monster: 5,
            },
        );
        assert_eq!(err, Err(ActionError::TargetOutOfRange));
    }

    #[test]
    fn validate_allows_escape_with_zero_ap() {
        let mut state = make_state(100, 50, vec![make_monster(50, 10, 20)]);
        state.action_points = 0;
        let result = validate_action(
            &state,
            PlayerAction::Escape,
            &ActionValidationCtx {
                class: Class::Warrior,
                has_weapon: true,
                has_spell: false,
                current_mana: 50,
                spell_mana_cost: 0,
                target_monster: 0,
            },
        );
        assert!(result.is_ok());
    }

    #[test]
    fn validate_spell_checks_mana() {
        let state = make_state(100, 5, vec![make_monster(50, 10, 20)]);
        let err = validate_action(
            &state,
            PlayerAction::CastSpell,
            &ActionValidationCtx {
                class: Class::Mage,
                has_weapon: false,
                has_spell: true,
                current_mana: 5,
                spell_mana_cost: 10,
                target_monster: 0,
            },
        );
        assert_eq!(err, Err(ActionError::InsufficientMana));
    }

    // --- Escape ---

    #[test]
    fn escape_success_sets_outcome() {
        let mut state = make_state(100, 50, vec![make_monster(50, 10, 20)]);
        let rolls = EscapeRolls {
            player_roll: 90,
            monster_roll: 10,
        };
        let result = resolve_escape(&mut state, 100, 50, &rolls, 20);
        assert!(result.succeeded);
        assert_eq!(state.outcome, BattleOutcome::Escaped);
    }

    #[test]
    fn escape_failure_keeps_in_progress() {
        let mut state = make_state(100, 50, vec![make_monster(50, 10, 200)]);
        let rolls = EscapeRolls {
            player_roll: 10,
            monster_roll: 90,
        };
        let result = resolve_escape(&mut state, 10, 5, &rolls, 200);
        assert!(!result.succeeded);
        assert_eq!(state.outcome, BattleOutcome::InProgress);
    }

    // --- Rest ---

    #[test]
    fn rest_reduces_exhaustion() {
        let mut state = make_state(100, 50, vec![make_monster(50, 10, 20)]);
        state.exhaustion = 50.0;
        resolve_rest(&mut state, 100);
        assert!((state.exhaustion - 40.0).abs() < 0.01);
    }

    #[test]
    fn rest_does_not_go_below_zero() {
        let mut state = make_state(100, 50, vec![make_monster(50, 10, 20)]);
        state.exhaustion = 1.0;
        resolve_rest(&mut state, 100);
        assert!((state.exhaustion - 0.0).abs() < 0.01);
    }

    // --- Spell misfire ---

    #[test]
    fn misfire_lose_one_mana() {
        let mut state = make_state(100, 50, vec![make_monster(50, 10, 20)]);
        let (self_dmg, mana_lost) =
            apply_misfire(&mut state, SpellMisfire::LoseOneMana, &test_spell(), 10);
        assert_eq!(mana_lost, 1);
        assert_eq!(self_dmg, 0);
        assert_eq!(state.player_mana, 49);
    }

    #[test]
    fn misfire_lose_all_mana() {
        let mut state = make_state(100, 50, vec![make_monster(50, 10, 20)]);
        let (self_dmg, mana_lost) =
            apply_misfire(&mut state, SpellMisfire::LoseAllMana, &test_spell(), 10);
        assert_eq!(mana_lost, 50);
        assert_eq!(self_dmg, 0);
        assert_eq!(state.player_mana, 0);
    }

    #[test]
    fn misfire_self_damage() {
        let mut state = make_state(100, 50, vec![make_monster(50, 10, 20)]);
        let spell = test_spell(); // multiplier 1.5
        let (self_dmg, mana_lost) = apply_misfire(&mut state, SpellMisfire::SelfDamage, &spell, 10);
        assert_eq!(mana_lost, 0);
        // 1.5 * 10 = 15.0 → ceil = 15
        assert_eq!(self_dmg, 15);
        assert_eq!(state.player_hp, 85);
    }

    #[test]
    fn misfire_self_damage_can_defeat() {
        let mut state = make_state(10, 50, vec![make_monster(50, 10, 20)]);
        let spell = test_spell(); // would deal 15 but capped at HP 10
        let (self_dmg, _) = apply_misfire(&mut state, SpellMisfire::SelfDamage, &spell, 10);
        assert_eq!(self_dmg, 10);
        assert_eq!(state.player_hp, 0);
        assert_eq!(state.outcome, BattleOutcome::Defeat);
    }

    // --- Monster turn ---

    #[test]
    fn monster_turn_applies_damage() {
        let mut state = make_state(200, 50, vec![make_monster(50, 10, 20)]);
        let rolls = MonsterTurnRolls {
            attacks: vec![vec![MonsterAttackRoll {
                damage_roll: 5,
                dodge_roll: 999,       // won't dodge
                block_roll: 100,       // won't block
                hit_location_roll: 50, // body
            }]],
        };

        let ctx = MonsterTurnCtx {
            player_dodge_value: 1, // very low dodge
            stance: AttackStance::Normal,
            shield: None,
            armor_pieces: [None, None, None, None],
            stats: &[],
            skills: &[],
            bonuses: &[],
            monster_attacks_per_round: 1,
            active_def_spell: None,
        };

        let result = resolve_monster_turn(&mut state, &ctx, &rolls);

        assert_eq!(result.hits.len(), 1);
        assert!(!result.hits[0].was_dodged);
        assert!(!result.hits[0].was_blocked);
        assert!(result.total_damage > 0);
        assert!(state.player_hp < 200);
    }

    #[test]
    fn monster_turn_defeat_on_zero_hp() {
        let mut state = make_state(1, 50, vec![make_monster(50, 100, 20)]);
        let rolls = MonsterTurnRolls {
            attacks: vec![vec![MonsterAttackRoll {
                damage_roll: 50,
                dodge_roll: 999,
                block_roll: 100,
                hit_location_roll: 50,
            }]],
        };

        let ctx = MonsterTurnCtx {
            player_dodge_value: 1,
            stance: AttackStance::Normal,
            shield: None,
            armor_pieces: [None, None, None, None],
            stats: &[],
            skills: &[],
            bonuses: &[],
            monster_attacks_per_round: 1,
            active_def_spell: None,
        };

        resolve_monster_turn(&mut state, &ctx, &rolls);

        assert_eq!(state.player_hp, 0);
        assert_eq!(state.outcome, BattleOutcome::Defeat);
    }

    // --- XP distribution ---

    #[test]
    fn xp_distribution_includes_attack_and_dodge() {
        let mut state = make_state(100, 50, vec![make_monster(50, 10, 20)]);
        state.did_attack = true;
        state.did_dodge = true;
        state.last_attack_type = AttackType::Melee;

        let shares = combat_xp_distribution(100, &state);
        // Stats: condition, wisdom, speed, agility(dodge), strength(melee)
        // Skills: dodge, attack
        assert!(shares.stat_shares.len() >= 4);
        assert!(shares.skill_shares.len() >= 2);
    }

    // --- Equipment wear ---

    #[test]
    fn equipment_wear_from_armor_hits() {
        let result = MonsterTurnResult {
            hits: vec![
                MonsterHitResult {
                    monster_index: 0,
                    damage_dealt: 10,
                    was_dodged: false,
                    was_blocked: false,
                    hit_location: HitLocation::Body,
                    mana_lost: 0,
                    armor_slot_worn: Some(HitLocation::Body),
                },
                MonsterHitResult {
                    monster_index: 0,
                    damage_dealt: 5,
                    was_dodged: true,
                    was_blocked: false,
                    hit_location: HitLocation::Head,
                    mana_lost: 0,
                    armor_slot_worn: None,
                },
            ],
            total_damage: 10,
            player_dodged_any: true,
        };

        let wear = equipment_wear_from_hits(&result);
        assert_eq!(wear.len(), 1);
        assert_eq!(wear[0].0, HitLocation::Body);
    }

    // --- Helper ---

    fn test_spell() -> Spell {
        Spell {
            id: 1,
            name: "Fireball".to_owned(),
            owner_id: 1,
            cost: 100,
            level: 5,
            spell_type: crate::item::SpellType::Battle,
            multiplier: 1.5,
            status: crate::item::SpellStatus::Active,
            element: Element::Fire,
        }
    }
}
