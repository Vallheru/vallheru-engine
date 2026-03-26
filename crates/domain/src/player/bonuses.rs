//! Player bonuses — maps to the `player_bonuses` table.
//!
//! Bonuses are purchased with Astral Points (AP) from a catalog.
//!
//! - `catalog_id`: row ID in the `bonuses` catalog table.
//! - `value`: number of times the bonus has been purchased/upgraded.
//! - `bonus_name`: key used by `checkbonus()` to match gameplay context.
//! - `duration`: base bonus coefficient per level.
//!
//! The `checkbonus()` formula is typically:
//! `ceil(base_value * ((value * duration) / 100))`.

use serde::{Deserialize, Serialize};

/// A single active bonus on a player.
#[derive(Debug, Clone, PartialEq, Eq, Serialize, Deserialize)]
pub struct PlayerBonus {
    /// Database row ID (0 for new/unsaved).
    pub id: i32,
    /// Bonus catalog ID from the `bonuses` table.
    pub catalog_id: i32,
    /// Trigger key used by `checkbonus()` (e.g. `"mining"`, `"strength"`, `"assasin"`).
    pub bonus_name: String,
    /// Current level (number of AP purchases).
    pub value: i32,
    /// Base magnitude per level.
    pub duration: i32,
}

#[cfg(test)]
mod tests {
    use super::*;

    #[test]
    fn bonus_struct_fields() {
        let b = PlayerBonus {
            id: 1,
            catalog_id: 42,
            bonus_name: "mining".to_owned(),
            value: 5,
            duration: 10,
        };
        assert_eq!(b.catalog_id, 42);
        assert_eq!(b.bonus_name, "mining");
        assert_eq!(b.value, 5);
        assert_eq!(b.duration, 10);
    }
}
