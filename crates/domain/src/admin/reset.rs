//! Scheduled reset job definitions and pure calculation helpers.
//!
//! The game's daily lifecycle depends on three reset levels:
//!
//! | Job              | Frequency  | Description                          |
//! |------------------|-----------|--------------------------------------|
//! | `EnergyTick`     | ~20 min   | Regenerate player energy             |
//! | `DailyReset`     | 1×/day    | Full daily reset (main + sub-reset)  |
//!
//! Each job is invoked via `vallheru job <name>` and protected by a
//! `PostgreSQL` advisory lock so concurrent runs are harmlessly skipped.

use std::fmt;

// ---------------------------------------------------------------------------
// Job registry
// ---------------------------------------------------------------------------

/// Scheduled job types that can be invoked from the CLI.
#[derive(Debug, Clone, Copy, PartialEq, Eq)]
pub enum Job {
    /// Regenerate energy for all active, non-frozen players (~20 min cadence).
    EnergyTick,
    /// Full daily reset: player stats, farm growth, jail expiry, outpost
    /// maintenance, warehouse pricing, army recruitment, random events, etc.
    DailyReset,
}

impl Job {
    /// `PostgreSQL` advisory lock key for this job.
    ///
    /// Using distinct keys ensures different jobs can run concurrently but
    /// the same job cannot overlap with itself.
    pub fn advisory_lock_key(self) -> i64 {
        match self {
            Self::EnergyTick => 0x5641_4C4C_0001, // "VALL" prefix + 1
            Self::DailyReset => 0x5641_4C4C_0002,
        }
    }

    /// Parse from the CLI argument string.
    pub fn from_cli(s: &str) -> Option<Self> {
        match s {
            "energy-tick" => Some(Self::EnergyTick),
            "daily-reset" => Some(Self::DailyReset),
            _ => None,
        }
    }
}

impl fmt::Display for Job {
    fn fmt(&self, f: &mut fmt::Formatter<'_>) -> fmt::Result {
        match self {
            Self::EnergyTick => f.write_str("energy-tick"),
            Self::DailyReset => f.write_str("daily-reset"),
        }
    }
}

// ---------------------------------------------------------------------------
// Outpost maintenance cost calculation
// ---------------------------------------------------------------------------

/// Inputs for computing an outpost's daily maintenance cost.
#[derive(Debug, Clone)]
pub struct OutpostCostInput {
    pub warriors: i32,
    pub archers: i32,
    pub catapults: i32,
    pub monster_count: i32,
    pub veteran_count: i32,
    /// Cost reduction bonus percentage (0–100).
    pub bcost: i32,
    pub gold: i64,
}

/// Result of computing outpost maintenance.
#[derive(Debug, Clone, PartialEq, Eq)]
pub struct OutpostMaintenance {
    /// Gold remaining after paying.
    pub gold_remaining: i64,
    /// Warriors lost due to inability to pay.
    pub warriors_lost: i32,
    /// Archers lost.
    pub archers_lost: i32,
    /// Catapults lost.
    pub catapults_lost: i32,
    /// Whether the outpost should warn about low funds.
    pub low_funds_warning: bool,
}

/// Calculate outpost daily maintenance.
///
/// Mirrors the PHP logic in `mainreset()`:
/// - Base cost = warriors×7 + archers×7 + catapults×14 + monsters×70 + veterans×70
/// - Bonus reduction applied
/// - If gold is insufficient, troops are lost proportionally
pub fn calculate_outpost_maintenance(input: &OutpostCostInput) -> OutpostMaintenance {
    let base_cost = i64::from(input.warriors) * 7
        + i64::from(input.archers) * 7
        + i64::from(input.catapults) * 14
        + i64::from(input.monster_count) * 70
        + i64::from(input.veteran_count) * 70;

    let bonus = (base_cost * i64::from(input.bcost)) / 100;
    let cost = base_cost - bonus;

    let low_funds_warning = input.gold < cost * 2;

    if input.gold >= cost {
        return OutpostMaintenance {
            gold_remaining: input.gold - cost,
            warriors_lost: 0,
            archers_lost: 0,
            catapults_lost: 0,
            low_funds_warning,
        };
    }

    // Not enough gold — lose troops proportionally.
    #[allow(clippy::cast_precision_loss)]
    let loss_pct = if input.gold > 0 && cost > 0 {
        ((cost - input.gold) as f64) / (cost as f64)
    } else {
        1.0
    };

    #[allow(clippy::cast_possible_truncation, clippy::cast_sign_loss)]
    let troop_loss = |count: i32| -> i32 {
        if input.gold == 0 {
            count
        } else if count > 0 {
            let lost = (f64::from(count) * loss_pct).ceil() as i32;
            lost.min(count)
        } else {
            0
        }
    };

    OutpostMaintenance {
        gold_remaining: 0,
        warriors_lost: troop_loss(input.warriors),
        archers_lost: troop_loss(input.archers),
        catapults_lost: troop_loss(input.catapults),
        low_funds_warning,
    }
}

// ---------------------------------------------------------------------------
// Warehouse price recalculation
// ---------------------------------------------------------------------------

/// Price aging coefficients for the 10-period rolling window.
///
/// Index 0 is the most recent reset, index 9 is the oldest.
const PRICE_WEIGHTS: [f64; 10] = [0.14, 0.13, 0.12, 0.11, 0.1, 0.1, 0.09, 0.08, 0.07, 0.06];

/// One period's data for a commodity in the rolling price window.
#[derive(Debug, Clone, Default)]
pub struct PricePeriod {
    pub cost: i64,
    pub sell: i64,
    pub buy: i64,
}

/// Min/max price ranges per commodity, in PHP index order.
///
/// `(min_price, max_price)` for each of the 26 commodities.
pub const PRICE_RANGES: [(i64, i64); 26] = [
    (2, 4),     // copperore
    (4, 6),     // zincore
    (6, 8),     // tinore
    (10, 12),   // ironore
    (5, 7),     // copper
    (14, 18),   // bronze
    (20, 30),   // brass
    (15, 25),   // iron
    (15, 25),   // steel
    (1, 3),     // coal
    (15, 25),   // adamantium
    (300, 400), // meteor
    (25, 35),   // crystal
    (4, 6),     // pine
    (7, 10),    // hazel
    (10, 15),   // yew
    (12, 18),   // elm
    (25, 35),   // mithril ← index 17 matches PHP arrPricesmin[17]
    (25, 35),   // illani
    (10, 20),   // illanias
    (25, 35),   // nutari
    (25, 35),   // dynallca
    (10, 25),   // illani_seeds ← PHP has seeds grouped at end
    (25, 35),   // illanias_seeds
    (10, 20),   // nutari_seeds
    (25, 35),   // dynallca_seeds
];

/// Calculate the new warehouse price for a commodity using the rolling
/// weighted-average formula from PHP `mainreset()`.
///
/// `periods` must be sorted oldest-first (index 0 = oldest, index 9 = newest),
/// matching the DB `ORDER BY reset ASC` order.
///
/// Returns `None` if the input is empty (first-ever reset; caller should use
/// a random price in the range instead).
pub fn recalculate_commodity_price(periods: &[PricePeriod], current_amount: i64) -> Option<i64> {
    if periods.is_empty() {
        return None;
    }

    // Normalize sell/buy volumes into ratios.
    let total_volume: i64 = periods.iter().map(|p| p.sell + p.buy).sum();

    #[allow(clippy::cast_precision_loss)]
    let (sell_ratios, buy_ratios): (Vec<f64>, Vec<f64>) = if total_volume > 0 {
        periods
            .iter()
            .map(|p| {
                (
                    p.sell as f64 / total_volume as f64,
                    p.buy as f64 / total_volume as f64,
                )
            })
            .unzip()
    } else {
        periods
            .iter()
            .map(|p| (p.sell as f64, p.buy as f64))
            .unzip()
    };

    // Weighted price = Σ (buy_ratio - sell_ratio + 1) × cost × weight
    let mut price: f64 = 0.0;
    for (i, period) in periods.iter().enumerate() {
        let weight = PRICE_WEIGHTS.get(i).copied().unwrap_or(0.06);
        #[allow(clippy::cast_precision_loss)]
        let cost_f64 = period.cost as f64;
        price += (buy_ratios[i] - sell_ratios[i] + 1.0) * cost_f64 * weight;
    }

    // If current stock is depleted, use previous price + 1 instead.
    if current_amount < 1 {
        // Newest period is the last element.
        return Some(periods.last().map_or(1, |p| p.cost + 1));
    }

    #[allow(clippy::cast_possible_truncation)]
    Some((price.ceil()) as i64)
}

/// Calculate the random initial price for a commodity (used when there are
/// fewer than 10 periods in the rolling window).
pub fn random_initial_price(index: usize, roll: impl FnOnce(i64, i64) -> i64) -> i64 {
    let (min_p, max_p) = PRICE_RANGES.get(index).copied().unwrap_or((1, 10));
    roll(min_p, max_p)
}

// ---------------------------------------------------------------------------
// Army recruitment scaling
// ---------------------------------------------------------------------------

/// Calculate the army recruitment range based on total outpost size.
pub fn army_recruitment_range(total_outpost_size: i32) -> ArmyRecruitment {
    ArmyRecruitment {
        min_troops: total_outpost_size * 15,
        max_troops: total_outpost_size * 20,
        min_specials: total_outpost_size * 7,
        max_specials: total_outpost_size * 10,
    }
}

/// Recruitment ranges for the daily army replenishment.
#[derive(Debug, Clone, PartialEq, Eq)]
pub struct ArmyRecruitment {
    pub min_troops: i32,
    pub max_troops: i32,
    pub min_specials: i32,
    pub max_specials: i32,
}

// ---------------------------------------------------------------------------
// Mana recalculation helpers
// ---------------------------------------------------------------------------

/// Calculate maximum mana from intelligence + wisdom ring bonuses.
///
/// Mirrors PHP: `max_mana = floor(inteli_bonus + wisdom_bonus)`, doubled for
/// Mag class, then increased by cape percentage.
pub fn calculate_max_mana(
    inteli_ring_bonus: i64,
    wisdom_ring_bonus: i64,
    is_mage: bool,
    cape_power: i64,
) -> i64 {
    let mut max_mana = inteli_ring_bonus + wisdom_ring_bonus;
    if is_mage {
        max_mana *= 2;
    }
    max_mana + (cape_power * max_mana) / 100
}

// ---------------------------------------------------------------------------
// Tests
// ---------------------------------------------------------------------------

#[cfg(test)]
mod tests {
    use super::*;

    // -- Job --

    #[test]
    fn job_from_cli_roundtrip() {
        assert_eq!(Job::from_cli("energy-tick"), Some(Job::EnergyTick));
        assert_eq!(Job::from_cli("daily-reset"), Some(Job::DailyReset));
        assert_eq!(Job::from_cli("unknown"), None);
    }

    #[test]
    fn job_display() {
        assert_eq!(Job::EnergyTick.to_string(), "energy-tick");
        assert_eq!(Job::DailyReset.to_string(), "daily-reset");
    }

    #[test]
    fn advisory_lock_keys_are_distinct() {
        assert_ne!(
            Job::EnergyTick.advisory_lock_key(),
            Job::DailyReset.advisory_lock_key()
        );
    }

    // -- Outpost maintenance --

    #[test]
    fn outpost_maintenance_can_pay() {
        let input = OutpostCostInput {
            warriors: 10,
            archers: 5,
            catapults: 2,
            monster_count: 1,
            veteran_count: 1,
            bcost: 10,
            gold: 10000,
        };
        let m = calculate_outpost_maintenance(&input);
        // cost = 10*7 + 5*7 + 2*14 + 1*70 + 1*70 = 70+35+28+70+70 = 273
        // bonus = 273 * 10 / 100 = 27
        // net cost = 246
        assert_eq!(m.gold_remaining, 10000 - 246);
        assert_eq!(m.warriors_lost, 0);
        assert!(!m.low_funds_warning);
    }

    #[test]
    fn outpost_maintenance_partial_pay() {
        let input = OutpostCostInput {
            warriors: 10,
            archers: 10,
            catapults: 0,
            monster_count: 0,
            veteran_count: 0,
            bcost: 0,
            gold: 70, // cost = 140, only 70 gold
        };
        let m = calculate_outpost_maintenance(&input);
        assert_eq!(m.gold_remaining, 0);
        // loss_pct = (140-70)/140 = 0.5
        assert_eq!(m.warriors_lost, 5); // ceil(10 * 0.5) = 5
        assert_eq!(m.archers_lost, 5);
        assert!(m.low_funds_warning);
    }

    #[test]
    fn outpost_maintenance_zero_gold() {
        let input = OutpostCostInput {
            warriors: 3,
            archers: 2,
            catapults: 1,
            monster_count: 0,
            veteran_count: 0,
            bcost: 0,
            gold: 0,
        };
        let m = calculate_outpost_maintenance(&input);
        assert_eq!(m.warriors_lost, 3);
        assert_eq!(m.archers_lost, 2);
        assert_eq!(m.catapults_lost, 1);
    }

    // -- Warehouse pricing --

    #[test]
    fn price_recalculation_empty_returns_none() {
        assert_eq!(recalculate_commodity_price(&[], 100), None);
    }

    #[test]
    fn price_recalculation_depleted_stock() {
        let periods = vec![PricePeriod {
            cost: 10,
            sell: 0,
            buy: 0,
        }];
        // current_amount < 1 → return last cost + 1
        assert_eq!(recalculate_commodity_price(&periods, 0), Some(11));
    }

    #[test]
    fn price_recalculation_no_trades() {
        // When nobody trades, sell_ratio and buy_ratio are both raw (0),
        // so the formula gives: (0 - 0 + 1) * cost * weight = cost * weight
        let periods: Vec<PricePeriod> = (0..10)
            .map(|_| PricePeriod {
                cost: 20,
                sell: 0,
                buy: 0,
            })
            .collect();
        let price = recalculate_commodity_price(&periods, 50).unwrap();
        // sum of weights = 1.0, so price = 20 * 1.0 = 20
        assert_eq!(price, 20);
    }

    #[test]
    fn price_recalculation_with_buying_pressure() {
        // Heavy buying should push price up.
        let periods: Vec<PricePeriod> = (0..10)
            .map(|_| PricePeriod {
                cost: 10,
                sell: 0,
                buy: 100,
            })
            .collect();
        let price = recalculate_commodity_price(&periods, 50).unwrap();
        // buy_ratio per period = 100/(100*10) = 0.1; sell_ratio = 0
        // Each period: (0.1 + 1) * 10 * weight
        // = 1.1 * 10 * weight_sum = 11 (since weights sum to 1.0)
        assert_eq!(price, 11);
    }

    // -- Army recruitment --

    #[test]
    fn army_recruitment_scaling() {
        let r = army_recruitment_range(5);
        assert_eq!(r.min_troops, 75);
        assert_eq!(r.max_troops, 100);
        assert_eq!(r.min_specials, 35);
        assert_eq!(r.max_specials, 50);
    }

    // -- Mana --

    #[test]
    fn max_mana_basic() {
        assert_eq!(calculate_max_mana(50, 30, false, 0), 80);
    }

    #[test]
    fn max_mana_mage_doubled() {
        assert_eq!(calculate_max_mana(50, 30, true, 0), 160);
    }

    #[test]
    fn max_mana_with_cape() {
        // base = 80, cape 20% → 80 + 16 = 96
        assert_eq!(calculate_max_mana(50, 30, false, 20), 96);
    }
}
