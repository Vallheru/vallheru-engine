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
// Tests
// ---------------------------------------------------------------------------

#[cfg(test)]
mod tests {
    use super::*;

    #[test]
    fn event_phase_roundtrip() {
        for state in [0, 1, 2, 3, 5, 6, 7, 8] {
            let phase = EventPhase::from_db(state);
            assert_eq!(phase.to_db(), state);
        }
    }

    #[test]
    fn event_phase_unknown_maps_to_none() {
        assert_eq!(EventPhase::from_db(4), EventPhase::None);
        assert_eq!(EventPhase::from_db(99), EventPhase::None);
    }

    #[test]
    fn event_phase_is_active() {
        assert!(!EventPhase::None.is_active());
        assert!(EventPhase::DeliveryOffered.is_active());
        assert!(EventPhase::DeliveryInProgress.is_active());
        assert!(EventPhase::DeliveryComplete.is_active());
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
}
