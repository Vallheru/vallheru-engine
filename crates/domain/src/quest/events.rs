//! Random city events and hunter quest state models.
//!
//! # `revent` table (random event state per player)
//!
//! ```text
//! pid       INT UNIQUE   — FK to players.id
//! state     TINYINT      — current event state (0–8)
//! qtime     SMALLINT     — cooldown ticks remaining until next event
//! location  VARCHAR(255) — event-specific location data (e.g. "Ardulith;Biblioteka")
//! ```
//!
//! # `events` table (event text templates)
//!
//! ```text
//! id   INT AUTO_INCREMENT
//! text TEXT          — template text
//! lang VARCHAR(3)   — language code
//! ```
//!
//! # Random event state machine (from `includes/revent.php`)
//!
//! State 0: No active event → trigger a new random event (3 types).
//!
//! Type 0 — Delivery quest:
//!   State 1: Old man asks to deliver something to another city.
//!   State 2: Player accepted, carries "Solidna sakiewka" (quest item).
//!            Reaches the target city + location → state 3.
//!   State 3: Delivered successfully, +1000 gold. Cooldown → state 5.
//!
//! Type 1 — Beggar encounter:
//!   State 6: Beggar approaches.
//!     - 50% chance: beggar leaves (not interested) → cooldown, state 5.
//!     - 50% chance: asks for money.
//!       - Give money → state 7 (short cooldown).
//!       - Refuse → state 5 (long cooldown).
//!
//! Type 2 — Suspicious figure:
//!   State 6 initially, then:
//!     - High perception check: combat with ratman (monster id 4) → state 8.
//!     - Low perception: figure leaves → state 5 (long cooldown).
//!
//! State 5: Cooling down. `qtime` ticks down each game tick.
//!          When qtime reaches 0, the `revent` row is deleted → back to 0.
//!
//! State 7: Short cooldown (reward variant), similar to state 5.
//!
//! State 8: In combat with the ratman. After combat ends → state 5.

/// Random event state as stored in the `revent` table.
#[derive(Debug, Clone, PartialEq, Eq)]
pub struct RandomEventState {
    pub player_id: i32,
    pub state: EventPhase,
    /// Cooldown ticks remaining.
    pub cooldown_ticks: i16,
    /// Extra location data (e.g. `"Ardulith;Biblioteka"`).
    pub location_data: String,
}

/// The phases of a random event, derived from the `state` column.
#[derive(Debug, Clone, Copy, PartialEq, Eq)]
pub enum EventPhase {
    /// No active event (state 0 or no row).
    None,
    /// Delivery quest offered (state 1).
    DeliveryOffered,
    /// Carrying delivery item (state 2).
    DeliveryInProgress,
    /// Delivery completed, reward given (state 3).
    DeliveryComplete,
    /// Quest item sold/disposed — leads to punishment at reset (state 4).
    ItemDisposed,
    /// Cooling down after event completion (state 5).
    CooldownNormal,
    /// Beggar/suspicious figure initial encounter (state 6).
    EncounterOffered,
    /// Short cooldown after giving money (state 7).
    CooldownReward,
    /// In combat from suspicious figure event (state 8).
    InCombat,
}

impl EventPhase {
    pub fn from_db(state: i16) -> Self {
        match state {
            1 => Self::DeliveryOffered,
            2 => Self::DeliveryInProgress,
            3 => Self::DeliveryComplete,
            4 => Self::ItemDisposed,
            5 => Self::CooldownNormal,
            6 => Self::EncounterOffered,
            7 => Self::CooldownReward,
            8 => Self::InCombat,
            _ => Self::None,
        }
    }

    pub fn to_db(self) -> i16 {
        match self {
            Self::None => 0,
            Self::DeliveryOffered => 1,
            Self::DeliveryInProgress => 2,
            Self::DeliveryComplete => 3,
            Self::ItemDisposed => 4,
            Self::CooldownNormal => 5,
            Self::EncounterOffered => 6,
            Self::CooldownReward => 7,
            Self::InCombat => 8,
        }
    }

    /// Whether this phase means the player has a blocking event.
    pub fn is_active(self) -> bool {
        !matches!(
            self,
            Self::None | Self::CooldownNormal | Self::CooldownReward
        )
    }

    /// Whether this phase is a cooldown waiting to expire.
    pub fn is_cooldown(self) -> bool {
        matches!(self, Self::CooldownNormal | Self::CooldownReward)
    }
}

/// The three random event types that can be generated.
#[derive(Debug, Clone, Copy, PartialEq, Eq)]
pub enum RandomEventKind {
    /// Old man delivery quest (type 0).
    Delivery,
    /// Beggar encounter (type 1).
    Beggar,
    /// Suspicious figure / possible combat (type 2).
    SuspiciousFigure,
}

impl RandomEventKind {
    pub fn from_roll(roll: i32) -> Self {
        match roll {
            0 => Self::Delivery,
            1 => Self::Beggar,
            _ => Self::SuspiciousFigure,
        }
    }
}

/// Delivery quest target is always the other city.
pub fn delivery_target_city(current_city: &str) -> &'static str {
    if current_city == "Altara" {
        "Ardulith"
    } else {
        "Altara"
    }
}

/// Possible delivery target locations within the destination city.
pub const DELIVERY_LOCATIONS: &[&str] = &[
    "Rynek",
    "Arena Walk",
    "Miejskie Plotki",
    "Magiczna wieża",
    "Biblioteka",
    "Farma",
    "Świątynia",
];

/// The fixed delivery reward in gold.
pub const DELIVERY_REWARD_GOLD: i32 = 1000;

/// The delivery quest item name.
pub const DELIVERY_ITEM_NAME: &str = "Solidna sakiewka";

/// Monster ID for the suspicious figure combat encounter (Szczurołak).
pub const RATMAN_MONSTER_ID: i32 = 4;

/// Hunter quest state.
///
/// Hunter quests are generated periodically (via admin/scheduler) and stored
/// in the `settings` table as `hunteraltara` / `hunterardulith`.
/// The actual quest content uses the same `missions` / `mactions` system
/// but is triggered from the hunters guild notice board.
///
/// This struct represents the hunter quest availability, not the active
/// mission state (which uses [`super::mission::ActiveMission`]).
#[derive(Debug, Clone, PartialEq, Eq)]
pub struct HunterQuestAvailability {
    /// Whether a hunter quest is currently available in Altara.
    pub altara_available: bool,
    /// Whether a hunter quest is currently available in Ardulith.
    pub ardulith_available: bool,
}

// ---------------------------------------------------------------------------
// Event generation
// ---------------------------------------------------------------------------

/// Pre-rolled dice for event generation, enabling deterministic testing.
#[derive(Debug, Clone)]
pub struct EventGenRolls {
    /// Which event type (0–2).
    pub type_roll: i32,
    /// Roll for beggar encounter: < 51 means beggar not interested (out of 100).
    pub beggar_roll: i32,
    /// Perception skill of the player (for suspicious figure).
    pub perception: i32,
    /// Perception threshold roll (10–300 range).
    pub perception_threshold: i32,
    /// Location index for delivery quest (`0..DELIVERY_LOCATIONS.len()`).
    pub location_index: usize,
    /// Cooldown ticks when needed (18–36 range).
    pub cooldown_ticks: i16,
}

/// The outcome of generating a new event for a player who has no active event.
#[derive(Debug, Clone, PartialEq, Eq)]
pub enum EventGenOutcome {
    /// Delivery quest: old man asks to deliver something.
    DeliveryOffered,
    /// Beggar approaches but leaves (not interested in the player).
    BeggarDismissed { cooldown: i16 },
    /// Beggar asks for money.
    BeggarAsks,
    /// Suspicious figure turns out to be a ratman (perception check passed).
    RatmanFight { cooldown: i16 },
    /// Suspicious figure leaves (perception check failed).
    FigureDismissed { cooldown: i16 },
}

/// Determine the outcome of triggering a new random event.
///
/// Returns `None` if the player already has an event (non-None phase).
pub fn generate_event(rolls: &EventGenRolls) -> EventGenOutcome {
    let kind = RandomEventKind::from_roll(rolls.type_roll);
    match kind {
        RandomEventKind::Delivery => EventGenOutcome::DeliveryOffered,
        RandomEventKind::Beggar => {
            if rolls.beggar_roll < 51 {
                EventGenOutcome::BeggarDismissed {
                    cooldown: rolls.cooldown_ticks,
                }
            } else {
                EventGenOutcome::BeggarAsks
            }
        }
        RandomEventKind::SuspiciousFigure => {
            if rolls.perception > rolls.perception_threshold {
                EventGenOutcome::RatmanFight {
                    cooldown: rolls.cooldown_ticks,
                }
            } else {
                EventGenOutcome::FigureDismissed {
                    cooldown: rolls.cooldown_ticks,
                }
            }
        }
    }
}

/// Compute the initial DB state for a generated event.
pub fn event_gen_db_state(outcome: &EventGenOutcome) -> (i16, i16) {
    match outcome {
        EventGenOutcome::DeliveryOffered => (EventPhase::DeliveryOffered.to_db(), 0),
        EventGenOutcome::BeggarDismissed { cooldown }
        | EventGenOutcome::FigureDismissed { cooldown } => {
            (EventPhase::CooldownNormal.to_db(), *cooldown)
        }
        EventGenOutcome::BeggarAsks => (EventPhase::EncounterOffered.to_db(), 0),
        EventGenOutcome::RatmanFight { cooldown } => (EventPhase::InCombat.to_db(), *cooldown),
    }
}

// ---------------------------------------------------------------------------
// Event interaction outcomes
// ---------------------------------------------------------------------------

/// Response choices for the delivery offer (state 1).
#[derive(Debug, Clone, Copy, PartialEq, Eq)]
pub enum DeliveryResponse {
    Accept,
    Refuse,
}

/// Outcome of accepting a delivery quest.
#[derive(Debug, Clone, PartialEq, Eq)]
pub struct DeliveryAccepted {
    /// Target city+location, e.g. `"Ardulith;Biblioteka"`.
    pub location_data: String,
    /// Cooldown until delivery expires.
    pub qtime: i16,
}

/// Process the delivery response.
///
/// Returns `Some(accepted)` on accept, `None` on refuse (row should be
/// deleted).
pub fn process_delivery_response(
    response: DeliveryResponse,
    current_city: &str,
    location_roll: usize,
    cooldown: i16,
) -> Option<DeliveryAccepted> {
    match response {
        DeliveryResponse::Refuse => None,
        DeliveryResponse::Accept => {
            let target = delivery_target_city(current_city);
            let idx = location_roll % DELIVERY_LOCATIONS.len();
            let loc = DELIVERY_LOCATIONS[idx];
            Some(DeliveryAccepted {
                location_data: format!("{target};{loc}"),
                qtime: cooldown,
            })
        }
    }
}

/// Response choices for the beggar encounter (state 6 from beggar type).
#[derive(Debug, Clone, Copy, PartialEq, Eq)]
pub enum BeggarResponse {
    GiveMoney,
    Refuse,
}

/// Range for the beggar's gold demand.
pub const BEGGAR_GOLD_MIN: i32 = 10;
pub const BEGGAR_GOLD_MAX: i32 = 100;

/// Outcome of responding to the beggar.
#[derive(Debug, Clone, PartialEq, Eq)]
pub enum BeggarOutcome {
    /// Player gave money.
    Gave { gold_cost: i32, cooldown: i16 },
    /// Player couldn't afford it.
    CantAfford { cooldown: i16 },
    /// Player refused.
    Refused { cooldown: i16 },
}

/// Process the beggar response.
pub fn process_beggar_response(
    response: BeggarResponse,
    player_gold: i64,
    gold_demand: i32,
    cooldown: i16,
) -> BeggarOutcome {
    match response {
        BeggarResponse::Refuse => BeggarOutcome::Refused { cooldown },
        BeggarResponse::GiveMoney => {
            if player_gold < i64::from(gold_demand) {
                BeggarOutcome::CantAfford { cooldown }
            } else {
                BeggarOutcome::Gave {
                    gold_cost: gold_demand,
                    cooldown,
                }
            }
        }
    }
}

/// DB state after beggar interaction.
pub fn beggar_outcome_db_state(outcome: &BeggarOutcome) -> (i16, i16) {
    match outcome {
        BeggarOutcome::Gave { cooldown, .. } => (EventPhase::CooldownReward.to_db(), *cooldown),
        BeggarOutcome::CantAfford { cooldown } | BeggarOutcome::Refused { cooldown } => {
            (EventPhase::CooldownNormal.to_db(), *cooldown)
        }
    }
}

// ---------------------------------------------------------------------------
// Reset-time event resolution
// ---------------------------------------------------------------------------

/// Outcome of resolving an expired event at reset time.
#[derive(Debug, Clone, PartialEq, Eq)]
pub enum ResetResolution {
    /// State 2: delivery not completed in time — remove quest item.
    DeliveryExpired,
    /// State 3: delivery completed — award gold to bank.
    DeliveryReward,
    /// State 4: player sold the quest item — punishment.
    ItemSoldPunishment,
    /// State 7: gave money to beggar — possible veteran recruitment.
    BeggarReward,
    /// Other cooldown expired — just clean up.
    CooldownExpired,
}

/// Determine the resolution action for an expired event.
pub fn reset_resolution(state: i16) -> ResetResolution {
    match EventPhase::from_db(state) {
        EventPhase::DeliveryInProgress => ResetResolution::DeliveryExpired,
        EventPhase::DeliveryComplete => ResetResolution::DeliveryReward,
        EventPhase::ItemDisposed => ResetResolution::ItemSoldPunishment,
        EventPhase::CooldownReward => ResetResolution::BeggarReward,
        _ => ResetResolution::CooldownExpired,
    }
}

/// Item-sold punishment outcome (state 4 at reset).
/// 50/50 chance: jail or bandit attack.
#[derive(Debug, Clone, Copy, PartialEq, Eq)]
pub enum PunishmentKind {
    /// Jailed for stealing from the tax collector.
    Jail,
    /// Bandits attack: hp=0, credits=0, bank halved.
    BanditAttack,
}

impl PunishmentKind {
    pub fn from_roll(roll: i32) -> Self {
        if roll == 0 {
            Self::Jail
        } else {
            Self::BanditAttack
        }
    }
}

// ---------------------------------------------------------------------------
// Tests
// ---------------------------------------------------------------------------

#[cfg(test)]
mod tests {
    use super::*;

    #[test]
    fn event_phase_roundtrip() {
        for state in [0, 1, 2, 3, 4, 5, 6, 7, 8] {
            let phase = EventPhase::from_db(state);
            assert_eq!(phase.to_db(), state);
        }
    }

    #[test]
    fn event_phase_unknown_maps_to_none() {
        assert_eq!(EventPhase::from_db(99), EventPhase::None);
    }

    #[test]
    fn event_phase_is_active() {
        assert!(!EventPhase::None.is_active());
        assert!(EventPhase::DeliveryOffered.is_active());
        assert!(EventPhase::DeliveryInProgress.is_active());
        assert!(EventPhase::DeliveryComplete.is_active());
        assert!(EventPhase::ItemDisposed.is_active());
        assert!(!EventPhase::CooldownNormal.is_active());
        assert!(EventPhase::EncounterOffered.is_active());
        assert!(!EventPhase::CooldownReward.is_active());
        assert!(EventPhase::InCombat.is_active());
    }

    #[test]
    fn event_phase_is_cooldown() {
        assert!(EventPhase::CooldownNormal.is_cooldown());
        assert!(EventPhase::CooldownReward.is_cooldown());
        assert!(!EventPhase::None.is_cooldown());
        assert!(!EventPhase::DeliveryOffered.is_cooldown());
        assert!(!EventPhase::ItemDisposed.is_cooldown());
    }

    #[test]
    fn random_event_kind_from_roll() {
        assert_eq!(RandomEventKind::from_roll(0), RandomEventKind::Delivery);
        assert_eq!(RandomEventKind::from_roll(1), RandomEventKind::Beggar);
        assert_eq!(
            RandomEventKind::from_roll(2),
            RandomEventKind::SuspiciousFigure
        );
    }

    #[test]
    fn delivery_target_switches_city() {
        assert_eq!(delivery_target_city("Altara"), "Ardulith");
        assert_eq!(delivery_target_city("Ardulith"), "Altara");
    }

    #[test]
    fn delivery_locations_count() {
        assert_eq!(DELIVERY_LOCATIONS.len(), 7);
    }

    // --- Event generation ---

    #[test]
    fn generate_delivery_event() {
        let rolls = EventGenRolls {
            type_roll: 0,
            beggar_roll: 0,
            perception: 0,
            perception_threshold: 0,
            location_index: 0,
            cooldown_ticks: 20,
        };
        assert_eq!(generate_event(&rolls), EventGenOutcome::DeliveryOffered);
    }

    #[test]
    fn generate_beggar_dismissed() {
        let rolls = EventGenRolls {
            type_roll: 1,
            beggar_roll: 30,
            perception: 0,
            perception_threshold: 0,
            location_index: 0,
            cooldown_ticks: 25,
        };
        assert_eq!(
            generate_event(&rolls),
            EventGenOutcome::BeggarDismissed { cooldown: 25 }
        );
    }

    #[test]
    fn generate_beggar_asks() {
        let rolls = EventGenRolls {
            type_roll: 1,
            beggar_roll: 80,
            perception: 0,
            perception_threshold: 0,
            location_index: 0,
            cooldown_ticks: 20,
        };
        assert_eq!(generate_event(&rolls), EventGenOutcome::BeggarAsks);
    }

    #[test]
    fn generate_ratman_fight() {
        let rolls = EventGenRolls {
            type_roll: 2,
            beggar_roll: 0,
            perception: 200,
            perception_threshold: 100,
            location_index: 0,
            cooldown_ticks: 30,
        };
        assert_eq!(
            generate_event(&rolls),
            EventGenOutcome::RatmanFight { cooldown: 30 }
        );
    }

    #[test]
    fn generate_figure_dismissed() {
        let rolls = EventGenRolls {
            type_roll: 2,
            beggar_roll: 0,
            perception: 50,
            perception_threshold: 200,
            location_index: 0,
            cooldown_ticks: 22,
        };
        assert_eq!(
            generate_event(&rolls),
            EventGenOutcome::FigureDismissed { cooldown: 22 }
        );
    }

    #[test]
    fn event_gen_db_state_values() {
        let (s, t) = event_gen_db_state(&EventGenOutcome::DeliveryOffered);
        assert_eq!(s, 1);
        assert_eq!(t, 0);

        let (s, t) = event_gen_db_state(&EventGenOutcome::BeggarDismissed { cooldown: 25 });
        assert_eq!(s, 5);
        assert_eq!(t, 25);

        let (s, t) = event_gen_db_state(&EventGenOutcome::BeggarAsks);
        assert_eq!(s, 6);
        assert_eq!(t, 0);

        let (s, t) = event_gen_db_state(&EventGenOutcome::RatmanFight { cooldown: 30 });
        assert_eq!(s, 8);
        assert_eq!(t, 30);

        let (s, t) = event_gen_db_state(&EventGenOutcome::FigureDismissed { cooldown: 22 });
        assert_eq!(s, 5);
        assert_eq!(t, 22);
    }

    // --- Event interaction ---

    #[test]
    fn delivery_accept() {
        let result = process_delivery_response(DeliveryResponse::Accept, "Altara", 3, 4);
        let accepted = result.unwrap();
        assert_eq!(accepted.location_data, "Ardulith;Magiczna wieża");
        assert_eq!(accepted.qtime, 4);
    }

    #[test]
    fn delivery_refuse() {
        assert!(process_delivery_response(DeliveryResponse::Refuse, "Altara", 0, 4).is_none());
    }

    #[test]
    fn beggar_give_money() {
        let outcome = process_beggar_response(BeggarResponse::GiveMoney, 500, 50, 6);
        assert_eq!(
            outcome,
            BeggarOutcome::Gave {
                gold_cost: 50,
                cooldown: 6
            }
        );
    }

    #[test]
    fn beggar_cant_afford() {
        let outcome = process_beggar_response(BeggarResponse::GiveMoney, 10, 50, 6);
        assert_eq!(outcome, BeggarOutcome::CantAfford { cooldown: 6 });
    }

    #[test]
    fn beggar_refuse() {
        let outcome = process_beggar_response(BeggarResponse::Refuse, 500, 50, 6);
        assert_eq!(outcome, BeggarOutcome::Refused { cooldown: 6 });
    }

    #[test]
    fn beggar_outcome_db_states() {
        let (s, t) = beggar_outcome_db_state(&BeggarOutcome::Gave {
            gold_cost: 50,
            cooldown: 6,
        });
        assert_eq!(s, 7);
        assert_eq!(t, 6);

        let (s, t) = beggar_outcome_db_state(&BeggarOutcome::CantAfford { cooldown: 20 });
        assert_eq!(s, 5);
        assert_eq!(t, 20);

        let (s, t) = beggar_outcome_db_state(&BeggarOutcome::Refused { cooldown: 20 });
        assert_eq!(s, 5);
        assert_eq!(t, 20);
    }

    // --- Reset resolution ---

    #[test]
    fn reset_resolution_states() {
        assert_eq!(reset_resolution(2), ResetResolution::DeliveryExpired);
        assert_eq!(reset_resolution(3), ResetResolution::DeliveryReward);
        assert_eq!(reset_resolution(4), ResetResolution::ItemSoldPunishment);
        assert_eq!(reset_resolution(7), ResetResolution::BeggarReward);
        assert_eq!(reset_resolution(5), ResetResolution::CooldownExpired);
        assert_eq!(reset_resolution(8), ResetResolution::CooldownExpired);
    }

    #[test]
    fn punishment_kind_from_roll() {
        assert_eq!(PunishmentKind::from_roll(0), PunishmentKind::Jail);
        assert_eq!(PunishmentKind::from_roll(1), PunishmentKind::BanditAttack);
    }
}
