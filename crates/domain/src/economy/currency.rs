//! Currency types, balance operations, and transfer validation.
//!
//! The game has three currency stores on each player:
//!
//! | Field      | Meaning                        |
//! |------------|--------------------------------|
//! | `credits`  | Gold on hand (pocket gold)     |
//! | `bank`     | Gold deposited in the bank     |
//! | `platinum` | Premium currency (mithril)     |
//!
//! Bank deposit/withdraw moves gold between `credits` and `bank`.
//! Player-to-player transfers move `bank` gold or `platinum`.
//! All mutations are validated to prevent negative balances.

/// Result of a validated balance change.
#[derive(Debug, Clone, Copy, PartialEq, Eq)]
pub struct BalanceChange {
    /// Amount actually moved (may be clamped to available balance).
    pub amount: i32,
    /// Sender's new value for the source field.
    pub new_source: i32,
    /// Receiver's new value for the destination field (or same player for deposit/withdraw).
    pub new_dest: i32,
}

/// Error returned when a currency operation is invalid.
#[derive(Debug, Clone, Copy, PartialEq, Eq)]
pub enum CurrencyError {
    /// The requested amount is zero or negative.
    InvalidAmount,
    /// Cannot transfer to yourself.
    SelfTransfer,
    /// Insufficient balance to complete the operation.
    InsufficientFunds,
    /// Recipient player was not found.
    RecipientNotFound,
}

impl std::fmt::Display for CurrencyError {
    fn fmt(&self, f: &mut std::fmt::Formatter<'_>) -> std::fmt::Result {
        match self {
            Self::InvalidAmount => write!(f, "Amount must be positive"),
            Self::SelfTransfer => write!(f, "Cannot transfer to yourself"),
            Self::InsufficientFunds => write!(f, "Insufficient funds"),
            Self::RecipientNotFound => write!(f, "Recipient not found"),
        }
    }
}

impl std::error::Error for CurrencyError {}

/// Validate and compute a bank deposit (credits → bank).
///
/// PHP behavior: if the requested amount exceeds available credits,
/// it is silently clamped to the available balance.
pub fn deposit_gold(
    credits: i32,
    bank: i32,
    requested: i32,
) -> Result<BalanceChange, CurrencyError> {
    if requested <= 0 {
        return Err(CurrencyError::InvalidAmount);
    }
    let amount = requested.min(credits);
    if amount <= 0 {
        return Err(CurrencyError::InsufficientFunds);
    }
    Ok(BalanceChange {
        amount,
        new_source: credits - amount,
        new_dest: bank + amount,
    })
}

/// Validate and compute a bank withdrawal (bank → credits).
///
/// PHP behavior: if the requested amount exceeds available bank balance,
/// it is silently clamped.
pub fn withdraw_gold(
    credits: i32,
    bank: i32,
    requested: i32,
) -> Result<BalanceChange, CurrencyError> {
    if requested <= 0 {
        return Err(CurrencyError::InvalidAmount);
    }
    let amount = requested.min(bank);
    if amount <= 0 {
        return Err(CurrencyError::InsufficientFunds);
    }
    Ok(BalanceChange {
        amount,
        new_source: bank - amount,
        new_dest: credits + amount,
    })
}

/// Result of a player-to-player transfer.
#[derive(Debug, Clone, Copy, PartialEq, Eq)]
pub struct TransferResult {
    /// Amount actually transferred.
    pub amount: i32,
    /// Sender's new balance for the transferred currency.
    pub sender_new_balance: i32,
    /// Recipient's new balance for the transferred currency.
    pub recipient_new_balance: i32,
}

/// Validate and compute a gold transfer between players (bank → bank).
///
/// PHP: transfers come from the sender's bank balance and land in the
/// recipient's bank balance.
pub fn transfer_gold(
    sender_id: i32,
    sender_bank: i32,
    recipient_id: i32,
    recipient_bank: i32,
    amount: i32,
) -> Result<TransferResult, CurrencyError> {
    if amount <= 0 {
        return Err(CurrencyError::InvalidAmount);
    }
    if sender_id == recipient_id {
        return Err(CurrencyError::SelfTransfer);
    }
    if sender_bank < amount {
        return Err(CurrencyError::InsufficientFunds);
    }
    Ok(TransferResult {
        amount,
        sender_new_balance: sender_bank - amount,
        recipient_new_balance: recipient_bank + amount,
    })
}

/// Validate and compute a platinum (mithril) transfer between players.
///
/// PHP: transfers come from the sender's platinum field and land in the
/// recipient's platinum field.
pub fn transfer_platinum(
    sender_id: i32,
    sender_platinum: i32,
    recipient_id: i32,
    recipient_platinum: i32,
    amount: i32,
) -> Result<TransferResult, CurrencyError> {
    if amount <= 0 {
        return Err(CurrencyError::InvalidAmount);
    }
    if sender_id == recipient_id {
        return Err(CurrencyError::SelfTransfer);
    }
    if sender_platinum < amount {
        return Err(CurrencyError::InsufficientFunds);
    }
    Ok(TransferResult {
        amount,
        sender_new_balance: sender_platinum - amount,
        recipient_new_balance: recipient_platinum + amount,
    })
}

/// Check whether a player can afford a purchase with pocket gold (credits).
///
/// Returns the remaining credits after the purchase.
pub fn spend_credits(credits: i32, cost: i32) -> Result<i32, CurrencyError> {
    if cost <= 0 {
        return Err(CurrencyError::InvalidAmount);
    }
    if credits < cost {
        return Err(CurrencyError::InsufficientFunds);
    }
    Ok(credits - cost)
}

/// Check whether a player can afford a purchase with platinum.
///
/// Returns the remaining platinum after the purchase.
pub fn spend_platinum(platinum: i32, cost: i32) -> Result<i32, CurrencyError> {
    if cost <= 0 {
        return Err(CurrencyError::InvalidAmount);
    }
    if platinum < cost {
        return Err(CurrencyError::InsufficientFunds);
    }
    Ok(platinum - cost)
}

/// Add gold credits to a player (e.g. quest reward, loot).
///
/// The amount must be positive. Returns the new balance.
pub fn earn_credits(credits: i32, amount: i32) -> Result<i32, CurrencyError> {
    if amount <= 0 {
        return Err(CurrencyError::InvalidAmount);
    }
    Ok(credits + amount)
}

/// Add platinum to a player (e.g. reward, gift system).
///
/// The amount must be positive. Returns the new balance.
pub fn earn_platinum(platinum: i32, amount: i32) -> Result<i32, CurrencyError> {
    if amount <= 0 {
        return Err(CurrencyError::InvalidAmount);
    }
    Ok(platinum + amount)
}

// ---------------------------------------------------------------------------
// Potion shop pricing
// ---------------------------------------------------------------------------

/// Compute the shop price for a single potion.
///
/// Formula from `msklep.php`:
/// - Type `M` (mana potions): `power * 3`
/// - All others: `2 * power * 3`
pub fn potion_shop_price(potion_type: &str, power: i32) -> i32 {
    if potion_type == "M" {
        power * 3
    } else {
        2 * power * 3
    }
}

/// Compute the resale cost stored on purchased potions.
///
/// Formula from `msklep.php`: `unit_price / 20`.
pub fn potion_resale_cost(unit_price: i32) -> i64 {
    i64::from(unit_price) / 20
}

#[cfg(test)]
mod tests {
    use super::*;

    // --- deposit_gold ---

    #[test]
    fn deposit_gold_normal() {
        let r = deposit_gold(500, 100, 200).unwrap();
        assert_eq!(r.amount, 200);
        assert_eq!(r.new_source, 300); // credits
        assert_eq!(r.new_dest, 300); // bank
    }

    #[test]
    fn deposit_gold_clamped() {
        let r = deposit_gold(100, 200, 500).unwrap();
        assert_eq!(r.amount, 100);
        assert_eq!(r.new_source, 0);
        assert_eq!(r.new_dest, 300);
    }

    #[test]
    fn deposit_gold_zero_amount() {
        assert_eq!(deposit_gold(100, 200, 0), Err(CurrencyError::InvalidAmount));
    }

    #[test]
    fn deposit_gold_negative_amount() {
        assert_eq!(
            deposit_gold(100, 200, -5),
            Err(CurrencyError::InvalidAmount)
        );
    }

    #[test]
    fn deposit_gold_zero_credits() {
        assert_eq!(
            deposit_gold(0, 200, 50),
            Err(CurrencyError::InsufficientFunds)
        );
    }

    // --- withdraw_gold ---

    #[test]
    fn withdraw_gold_normal() {
        let r = withdraw_gold(100, 500, 200).unwrap();
        assert_eq!(r.amount, 200);
        assert_eq!(r.new_source, 300); // bank
        assert_eq!(r.new_dest, 300); // credits
    }

    #[test]
    fn withdraw_gold_clamped() {
        let r = withdraw_gold(100, 50, 200).unwrap();
        assert_eq!(r.amount, 50);
        assert_eq!(r.new_source, 0);
        assert_eq!(r.new_dest, 150);
    }

    #[test]
    fn withdraw_gold_zero_amount() {
        assert_eq!(
            withdraw_gold(100, 200, 0),
            Err(CurrencyError::InvalidAmount)
        );
    }

    #[test]
    fn withdraw_gold_zero_bank() {
        assert_eq!(
            withdraw_gold(100, 0, 50),
            Err(CurrencyError::InsufficientFunds)
        );
    }

    // --- transfer_gold ---

    #[test]
    fn transfer_gold_normal() {
        let r = transfer_gold(1, 1000, 2, 500, 300).unwrap();
        assert_eq!(r.amount, 300);
        assert_eq!(r.sender_new_balance, 700);
        assert_eq!(r.recipient_new_balance, 800);
    }

    #[test]
    fn transfer_gold_exact_balance() {
        let r = transfer_gold(1, 100, 2, 0, 100).unwrap();
        assert_eq!(r.sender_new_balance, 0);
        assert_eq!(r.recipient_new_balance, 100);
    }

    #[test]
    fn transfer_gold_insufficient() {
        assert_eq!(
            transfer_gold(1, 100, 2, 0, 200),
            Err(CurrencyError::InsufficientFunds)
        );
    }

    #[test]
    fn transfer_gold_self() {
        assert_eq!(
            transfer_gold(1, 1000, 1, 1000, 100),
            Err(CurrencyError::SelfTransfer)
        );
    }

    #[test]
    fn transfer_gold_zero_amount() {
        assert_eq!(
            transfer_gold(1, 1000, 2, 0, 0),
            Err(CurrencyError::InvalidAmount)
        );
    }

    // --- transfer_platinum ---

    #[test]
    fn transfer_platinum_normal() {
        let r = transfer_platinum(1, 50, 2, 10, 20).unwrap();
        assert_eq!(r.amount, 20);
        assert_eq!(r.sender_new_balance, 30);
        assert_eq!(r.recipient_new_balance, 30);
    }

    #[test]
    fn transfer_platinum_insufficient() {
        assert_eq!(
            transfer_platinum(1, 5, 2, 10, 20),
            Err(CurrencyError::InsufficientFunds)
        );
    }

    #[test]
    fn transfer_platinum_self() {
        assert_eq!(
            transfer_platinum(1, 100, 1, 100, 10),
            Err(CurrencyError::SelfTransfer)
        );
    }

    // --- spend_credits ---

    #[test]
    fn spend_credits_normal() {
        assert_eq!(spend_credits(500, 200), Ok(300));
    }

    #[test]
    fn spend_credits_exact() {
        assert_eq!(spend_credits(100, 100), Ok(0));
    }

    #[test]
    fn spend_credits_insufficient() {
        assert_eq!(
            spend_credits(50, 100),
            Err(CurrencyError::InsufficientFunds)
        );
    }

    #[test]
    fn spend_credits_zero_cost() {
        assert_eq!(spend_credits(100, 0), Err(CurrencyError::InvalidAmount));
    }

    // --- spend_platinum ---

    #[test]
    fn spend_platinum_normal() {
        assert_eq!(spend_platinum(50, 20), Ok(30));
    }

    #[test]
    fn spend_platinum_insufficient() {
        assert_eq!(spend_platinum(5, 20), Err(CurrencyError::InsufficientFunds));
    }

    // --- earn_credits ---

    #[test]
    fn earn_credits_normal() {
        assert_eq!(earn_credits(100, 50), Ok(150));
    }

    #[test]
    fn earn_credits_zero() {
        assert_eq!(earn_credits(100, 0), Err(CurrencyError::InvalidAmount));
    }

    #[test]
    fn earn_credits_negative() {
        assert_eq!(earn_credits(100, -10), Err(CurrencyError::InvalidAmount));
    }

    // --- earn_platinum ---

    #[test]
    fn earn_platinum_normal() {
        assert_eq!(earn_platinum(10, 5), Ok(15));
    }

    #[test]
    fn earn_platinum_zero() {
        assert_eq!(earn_platinum(10, 0), Err(CurrencyError::InvalidAmount));
    }

    // --- potion pricing ---

    #[test]
    fn potion_price_mana_type() {
        // Type M: power * 3
        assert_eq!(potion_shop_price("M", 100), 300);
    }

    #[test]
    fn potion_price_other_type() {
        // Other: 2 * power * 3
        assert_eq!(potion_shop_price("H", 100), 600);
        assert_eq!(potion_shop_price("P", 50), 300);
    }

    #[test]
    fn potion_resale_cost_normal() {
        assert_eq!(potion_resale_cost(300), 15);
        assert_eq!(potion_resale_cost(600), 30);
    }
}
