//! Tribe (clan) domain logic — lifecycle and membership.
//!
//! Ported from PHP `tribes.php` and the membership-related parts of
//! `tribeadmin.php`.  Tribes are persistent organisations with tiered
//! levels, member caps, and a waiting-list join flow.
//!
//! **Scope:** creation, joining, leaving, disbanding, kicking, and
//! upgrading.  Permissions, ranks, shared storage, and combat are
//! handled in separate modules (MP-13-03, MP-13-04, etc.).

// ---------------------------------------------------------------------------
// Tribe level
// ---------------------------------------------------------------------------

/// The five structural tiers a tribe can reach.
///
/// Each level unlocks features and raises (or removes) the member cap.
/// Levels 4 and 5 are alternate upgrades from level 3 — a tribe at
/// level 3 can go to either 4 (Dwór) or 5 (Zamek), and 4 can still
/// upgrade to 5.
#[derive(Debug, Clone, Copy, PartialEq, Eq, PartialOrd, Ord, Hash)]
#[repr(i16)]
pub enum TribeLevel {
    /// Kryjówka — max 5 members.
    Hideout = 1,
    /// Kamienica — max 10, adds armory + warehouse.
    Tenement = 2,
    /// Dworek — max 20, adds treasury + herb storage + astral vault.
    Manor = 3,
    /// Dwór — unlimited members.
    Court = 4,
    /// Zamek — unlimited members, astral machine + clan wars.
    Castle = 5,
}

impl TribeLevel {
    /// Parse from the database `i16` value.
    pub fn from_db(v: i16) -> Option<Self> {
        match v {
            1 => Some(Self::Hideout),
            2 => Some(Self::Tenement),
            3 => Some(Self::Manor),
            4 => Some(Self::Court),
            5 => Some(Self::Castle),
            _ => None,
        }
    }

    /// Database representation.
    pub fn to_db(self) -> i16 {
        self as i16
    }

    /// Maximum number of members allowed at this level.
    ///
    /// `None` means unlimited.
    pub fn member_cap(self) -> Option<u32> {
        match self {
            Self::Hideout => Some(5),
            Self::Tenement => Some(10),
            Self::Manor => Some(20),
            Self::Court | Self::Castle => None,
        }
    }

    /// Gold cost to *create* a tribe directly at this level.
    ///
    /// PHP: `level × 500_000`.
    pub fn creation_cost(self) -> i64 {
        i64::from(self.to_db()) * 500_000
    }

    /// Gold cost to upgrade from `current` to `self`.
    ///
    /// PHP: `target × 500_000 − current × 500_000`.
    /// Returns `None` if `self <= current` (downgrade / same level).
    pub fn upgrade_cost_from(self, current: Self) -> Option<i64> {
        if self <= current {
            return None;
        }
        Some(self.creation_cost() - current.creation_cost())
    }

    /// Whether this level enables the armory and warehouse.
    pub fn has_armory(self) -> bool {
        self >= Self::Tenement
    }

    /// Whether this level enables treasury, herb storage, and astral
    /// vault.
    pub fn has_treasury(self) -> bool {
        self >= Self::Manor
    }

    /// Whether this level enables soldiers, fortifications, and clan
    /// wars.
    pub fn has_warfare(self) -> bool {
        self >= Self::Castle
    }

    /// Whether this level enables the astral machine.
    pub fn has_astral_machine(self) -> bool {
        self >= Self::Castle
    }

    /// Maximum number of traps / agents allowed at this level.
    ///
    /// PHP: `$arrAmount = array(0, 5, 10, 20, 40, 50)` indexed by level
    /// (1-based).
    pub fn max_defences(self) -> u32 {
        match self {
            Self::Hideout => 5,
            Self::Tenement => 10,
            Self::Manor => 20,
            Self::Court => 40,
            Self::Castle => 50,
        }
    }
}

/// All valid tribe levels for iteration.
pub const ALL_TRIBE_LEVELS: [TribeLevel; 5] = [
    TribeLevel::Hideout,
    TribeLevel::Tenement,
    TribeLevel::Manor,
    TribeLevel::Court,
    TribeLevel::Castle,
];

// ---------------------------------------------------------------------------
// Tribe creation
// ---------------------------------------------------------------------------

/// Error when attempting to create a tribe.
#[derive(Debug, Clone, PartialEq, Eq)]
pub enum CreateTribeError {
    /// Player is already in a tribe.
    AlreadyInTribe,
    /// Player does not have enough gold.
    InsufficientGold,
    /// The chosen name is empty or exceeds limits.
    InvalidName,
    /// Player is not in a city that has a tribe office.
    WrongLocation,
}

/// Required cities for tribe administration.
const TRIBE_CITIES: &[&str] = &["Altara", "Ardulith"];

/// Validate whether a player can create a new tribe.
///
/// - `player_tribe_id`: current tribe (0 = none).
/// - `player_gold`: player's gold balance.
/// - `name`: desired tribe name (trimmed by caller).
/// - `location`: player's current location string.
pub fn validate_create_tribe(
    player_tribe_id: i32,
    player_gold: i64,
    name: &str,
    location: &str,
) -> Result<i64, CreateTribeError> {
    if !TRIBE_CITIES.contains(&location) {
        return Err(CreateTribeError::WrongLocation);
    }
    if player_tribe_id != 0 {
        return Err(CreateTribeError::AlreadyInTribe);
    }
    if name.is_empty() || name.len() > 60 {
        return Err(CreateTribeError::InvalidName);
    }
    let cost = TribeLevel::Hideout.creation_cost();
    if player_gold < cost {
        return Err(CreateTribeError::InsufficientGold);
    }
    Ok(cost)
}

// ---------------------------------------------------------------------------
// Tribe upgrade
// ---------------------------------------------------------------------------

/// Error when attempting to upgrade a tribe.
#[derive(Debug, Clone, Copy, PartialEq, Eq)]
pub enum UpgradeTribeError {
    /// Only the owner can upgrade.
    NotOwner,
    /// Target level is not higher than current.
    InvalidTarget,
    /// Target level exceeds maximum (5).
    ExceedsMaxLevel,
    /// The tribe treasury does not have enough gold.
    InsufficientGold,
}

/// Validate a tribe upgrade and return the gold cost if valid.
///
/// - `player_id` / `owner_id`: ownership check.
/// - `current_level`: current `TribeLevel`.
/// - `target_level_raw`: raw level value from user input.
/// - `tribe_gold`: tribe treasury gold.
pub fn validate_upgrade(
    player_id: i32,
    owner_id: i32,
    current_level: TribeLevel,
    target_level_raw: i16,
    tribe_gold: i64,
) -> Result<(TribeLevel, i64), UpgradeTribeError> {
    if player_id != owner_id {
        return Err(UpgradeTribeError::NotOwner);
    }
    if target_level_raw > 5 {
        return Err(UpgradeTribeError::ExceedsMaxLevel);
    }
    let target = TribeLevel::from_db(target_level_raw).ok_or(UpgradeTribeError::InvalidTarget)?;
    let cost = target
        .upgrade_cost_from(current_level)
        .ok_or(UpgradeTribeError::InvalidTarget)?;
    if tribe_gold < cost {
        return Err(UpgradeTribeError::InsufficientGold);
    }
    Ok((target, cost))
}

// ---------------------------------------------------------------------------
// Join (request to join)
// ---------------------------------------------------------------------------

/// Error when a player requests to join a tribe.
#[derive(Debug, Clone, Copy, PartialEq, Eq)]
pub enum JoinRequestError {
    /// Player already belongs to a tribe.
    AlreadyInTribe,
    /// Player already has a pending request for this tribe.
    AlreadyRequested,
    /// Player is not in a city.
    WrongLocation,
}

/// Validate whether a player can submit a join request.
///
/// - `player_tribe_id`: 0 = not in any tribe.
/// - `already_requested`: whether a row already exists in
///   `tribe_oczek` for this player + tribe pair.
/// - `location`: player's current location.
pub fn validate_join_request(
    player_tribe_id: i32,
    already_requested: bool,
    location: &str,
) -> Result<(), JoinRequestError> {
    if !TRIBE_CITIES.contains(&location) {
        return Err(JoinRequestError::WrongLocation);
    }
    if player_tribe_id != 0 {
        return Err(JoinRequestError::AlreadyInTribe);
    }
    if already_requested {
        return Err(JoinRequestError::AlreadyRequested);
    }
    Ok(())
}

// ---------------------------------------------------------------------------
// Accept / reject pending member
// ---------------------------------------------------------------------------

/// Error when accepting a pending join request.
#[derive(Debug, Clone, Copy, PartialEq, Eq)]
pub enum AcceptMemberError {
    /// The pending request does not belong to this tribe.
    RequestNotForTribe,
    /// The tribe has reached its member cap.
    TribeFull,
}

/// Validate whether a pending member can be accepted.
///
/// - `request_tribe_id`: the tribe the request targets.
/// - `tribe_id`: the tribe performing the accept.
/// - `level`: current tribe level (determines cap).
/// - `current_member_count`: existing member count.
pub fn validate_accept_member(
    request_tribe_id: i32,
    tribe_id: i32,
    level: TribeLevel,
    current_member_count: u32,
) -> Result<(), AcceptMemberError> {
    if request_tribe_id != tribe_id {
        return Err(AcceptMemberError::RequestNotForTribe);
    }
    if let Some(cap) = level.member_cap() {
        if current_member_count >= cap {
            return Err(AcceptMemberError::TribeFull);
        }
    }
    Ok(())
}

// ---------------------------------------------------------------------------
// Leave tribe
// ---------------------------------------------------------------------------

/// What happens when a player leaves a tribe.
#[derive(Debug, Clone, PartialEq, Eq)]
pub enum LeaveOutcome {
    /// A regular member left.  Handler should: clear `players.tribe`,
    /// delete their `tribe_perm` row, and log to owner + admins.
    MemberLeft,
    /// The owner left, dissolving the entire tribe.  Handler should:
    /// delete the tribe row, clear all members' `tribe` fields,
    /// delete related `tribe_zbroj`, `tribe_mag`, `tribe_oczek`,
    /// `tribe_perm` rows, and notify all members.
    Dissolved {
        /// Player IDs of all non-owner members who need notification
        /// and tribe-field clearing.
        member_ids: Vec<i32>,
    },
}

/// Error when leaving a tribe.
#[derive(Debug, Clone, Copy, PartialEq, Eq)]
pub enum LeaveTribeError {
    /// Player is not in any tribe.
    NotInTribe,
}

/// Determine the outcome of a player leaving their tribe.
///
/// - `player_id`: the leaving player.
/// - `player_tribe_id`: their current tribe (0 = none).
/// - `owner_id`: tribe owner's player id.
/// - `other_member_ids`: all member IDs in the tribe *except* the
///   leaving player (used only when owner dissolves).
pub fn leave_tribe(
    player_id: i32,
    player_tribe_id: i32,
    owner_id: i32,
    other_member_ids: Vec<i32>,
) -> Result<LeaveOutcome, LeaveTribeError> {
    if player_tribe_id == 0 {
        return Err(LeaveTribeError::NotInTribe);
    }
    if player_id == owner_id {
        Ok(LeaveOutcome::Dissolved {
            member_ids: other_member_ids,
        })
    } else {
        Ok(LeaveOutcome::MemberLeft)
    }
}

// ---------------------------------------------------------------------------
// Kick member
// ---------------------------------------------------------------------------

/// Error when kicking a member from the tribe.
#[derive(Debug, Clone, Copy, PartialEq, Eq)]
pub enum KickMemberError {
    /// The kicker has no permission to kick (not owner, no `kick` perm).
    NoPermission,
    /// Cannot kick the owner.
    CannotKickOwner,
    /// Target is not in the same tribe.
    NotInTribe,
}

/// Validate whether a member can be kicked.
///
/// - `kicker_id`: player performing the kick.
/// - `owner_id`: tribe owner's player id.
/// - `kicker_has_kick_perm`: whether the kicker has the `kick`
///   permission flag set.
/// - `target_id`: player being kicked.
/// - `target_tribe_id`: target's `tribe` field.
/// - `tribe_id`: the tribe in question.
pub fn validate_kick(
    kicker_id: i32,
    owner_id: i32,
    kicker_has_kick_perm: bool,
    target_id: i32,
    target_tribe_id: i32,
    tribe_id: i32,
) -> Result<(), KickMemberError> {
    if target_tribe_id != tribe_id {
        return Err(KickMemberError::NotInTribe);
    }
    if target_id == owner_id {
        return Err(KickMemberError::CannotKickOwner);
    }
    if kicker_id != owner_id && !kicker_has_kick_perm {
        return Err(KickMemberError::NoPermission);
    }
    Ok(())
}

// ---------------------------------------------------------------------------
// Defence purchases (traps and agents)
// ---------------------------------------------------------------------------

/// Error when purchasing traps or agents.
#[derive(Debug, Clone, Copy, PartialEq, Eq)]
pub enum BuyDefencesError {
    /// No permission (not owner and no `army` perm).
    NoPermission,
    /// Both quantities are zero.
    NothingToBuy,
    /// Adding traps would exceed max for this level.
    TrapCapExceeded,
    /// Adding agents would exceed max for this level.
    AgentCapExceeded,
    /// Tribe treasury doesn't have enough gold.
    InsufficientGold,
}

/// Cost per trap (one-time).
pub const TRAP_COST: i64 = 1_000;
/// Cost per agent (one-time, plus daily upkeep elsewhere).
pub const AGENT_COST: i64 = 10_000;

/// Inputs for a defence purchase.
pub struct DefencePurchase {
    pub buyer_id: i32,
    pub owner_id: i32,
    pub buyer_has_army_perm: bool,
    pub level: TribeLevel,
    pub current_traps: u32,
    pub current_agents: u32,
    pub buy_traps: u32,
    pub buy_agents: u32,
    pub tribe_gold: i64,
}

/// Result of a successful defence purchase.
#[derive(Debug, Clone, Copy, PartialEq, Eq)]
pub struct DefencePurchaseResult {
    pub new_traps: u32,
    pub new_agents: u32,
    pub gold_cost: i64,
}

/// Validate and compute a defence purchase.
pub fn validate_buy_defences(
    p: &DefencePurchase,
) -> Result<DefencePurchaseResult, BuyDefencesError> {
    if p.buyer_id != p.owner_id && !p.buyer_has_army_perm {
        return Err(BuyDefencesError::NoPermission);
    }
    if p.buy_traps == 0 && p.buy_agents == 0 {
        return Err(BuyDefencesError::NothingToBuy);
    }
    let max = p.level.max_defences();
    let new_traps = p.current_traps + p.buy_traps;
    let new_agents = p.current_agents + p.buy_agents;
    if new_traps > max {
        return Err(BuyDefencesError::TrapCapExceeded);
    }
    if new_agents > max {
        return Err(BuyDefencesError::AgentCapExceeded);
    }
    let cost = i64::from(p.buy_traps) * TRAP_COST + i64::from(p.buy_agents) * AGENT_COST;
    if p.tribe_gold < cost {
        return Err(BuyDefencesError::InsufficientGold);
    }
    Ok(DefencePurchaseResult {
        new_traps,
        new_agents,
        gold_cost: cost,
    })
}

// ---------------------------------------------------------------------------
// Army purchases (soldiers and fortifications) — Castle only
// ---------------------------------------------------------------------------

/// Error when purchasing soldiers or fortifications.
#[derive(Debug, Clone, Copy, PartialEq, Eq)]
pub enum BuyArmyError {
    /// No permission.
    NoPermission,
    /// Tribe level too low (needs Castle).
    LevelTooLow,
    /// Both quantities are zero.
    NothingToBuy,
    /// Tribe treasury doesn't have enough gold.
    InsufficientGold,
}

/// Cost per soldier or fortification unit.
pub const ARMY_UNIT_COST: i64 = 1_000;

/// Inputs for an army purchase.
pub struct ArmyPurchase {
    pub buyer_id: i32,
    pub owner_id: i32,
    pub buyer_has_army_perm: bool,
    pub level: TribeLevel,
    pub buy_soldiers: u32,
    pub buy_fortifications: u32,
    pub tribe_gold: i64,
}

/// Result of a successful army purchase.
#[derive(Debug, Clone, Copy, PartialEq, Eq)]
pub struct ArmyPurchaseResult {
    pub soldiers_added: u32,
    pub fortifications_added: u32,
    pub gold_cost: i64,
}

/// Validate and compute an army purchase.
pub fn validate_buy_army(p: &ArmyPurchase) -> Result<ArmyPurchaseResult, BuyArmyError> {
    if p.buyer_id != p.owner_id && !p.buyer_has_army_perm {
        return Err(BuyArmyError::NoPermission);
    }
    if !p.level.has_warfare() {
        return Err(BuyArmyError::LevelTooLow);
    }
    if p.buy_soldiers == 0 && p.buy_fortifications == 0 {
        return Err(BuyArmyError::NothingToBuy);
    }
    let cost = i64::from(p.buy_soldiers) * ARMY_UNIT_COST
        + i64::from(p.buy_fortifications) * ARMY_UNIT_COST;
    if p.tribe_gold < cost {
        return Err(BuyArmyError::InsufficientGold);
    }
    Ok(ArmyPurchaseResult {
        soldiers_added: p.buy_soldiers,
        fortifications_added: p.buy_fortifications,
        gold_cost: cost,
    })
}

// ---------------------------------------------------------------------------
// Hospital pass purchase
// ---------------------------------------------------------------------------

/// Error when buying the free-hospital-pass perk.
#[derive(Debug, Clone, Copy, PartialEq, Eq)]
pub enum BuyHospitalPassError {
    /// Tribe already owns the pass.
    AlreadyOwned,
    /// Not enough mithril in tribe treasury.
    InsufficientMithril,
}

/// Mithril cost of the hospital pass.
pub const HOSPITAL_PASS_COST: i64 = 100;

/// Validate a hospital-pass purchase.
pub fn validate_buy_hospital_pass(
    tribe_has_pass: bool,
    tribe_mithril: i64,
) -> Result<i64, BuyHospitalPassError> {
    if tribe_has_pass {
        return Err(BuyHospitalPassError::AlreadyOwned);
    }
    if tribe_mithril < HOSPITAL_PASS_COST {
        return Err(BuyHospitalPassError::InsufficientMithril);
    }
    Ok(HOSPITAL_PASS_COST)
}

// ---------------------------------------------------------------------------
// Loan (give money to a member)
// ---------------------------------------------------------------------------

/// Error when loaning money from tribe treasury to a member.
#[derive(Debug, Clone, Copy, PartialEq, Eq)]
pub enum LoanError {
    /// No permission.
    NoPermission,
    /// Target is not in the same tribe.
    NotInTribe,
    /// Amount is zero or negative.
    InvalidAmount,
    /// Tribe treasury doesn't have enough of the chosen currency.
    InsufficientFunds,
}

/// Which currency to loan.
#[derive(Debug, Clone, Copy, PartialEq, Eq)]
pub enum LoanCurrency {
    Gold,
    Mithril,
}

impl LoanCurrency {
    /// Parse from the PHP form value (`"credits"` or `"platinum"`).
    pub fn from_php(s: &str) -> Option<Self> {
        match s {
            "credits" => Some(Self::Gold),
            "platinum" => Some(Self::Mithril),
            _ => None,
        }
    }
}

/// Validate a loan from tribe treasury to a member.
pub fn validate_loan(
    lender_id: i32,
    owner_id: i32,
    lender_has_loan_perm: bool,
    target_tribe_id: i32,
    tribe_id: i32,
    amount: i64,
    tribe_balance: i64,
) -> Result<(), LoanError> {
    if lender_id != owner_id && !lender_has_loan_perm {
        return Err(LoanError::NoPermission);
    }
    if target_tribe_id != tribe_id {
        return Err(LoanError::NotInTribe);
    }
    if amount <= 0 {
        return Err(LoanError::InvalidAmount);
    }
    if tribe_balance < amount {
        return Err(LoanError::InsufficientFunds);
    }
    Ok(())
}

// ---------------------------------------------------------------------------
// Tests
// ---------------------------------------------------------------------------

#[cfg(test)]
mod tests {
    use super::*;

    // --- TribeLevel ---

    #[test]
    fn level_roundtrip() {
        for level in ALL_TRIBE_LEVELS {
            assert_eq!(TribeLevel::from_db(level.to_db()), Some(level));
        }
    }

    #[test]
    fn level_from_db_invalid() {
        assert_eq!(TribeLevel::from_db(0), None);
        assert_eq!(TribeLevel::from_db(6), None);
        assert_eq!(TribeLevel::from_db(-1), None);
    }

    #[test]
    fn member_caps() {
        assert_eq!(TribeLevel::Hideout.member_cap(), Some(5));
        assert_eq!(TribeLevel::Tenement.member_cap(), Some(10));
        assert_eq!(TribeLevel::Manor.member_cap(), Some(20));
        assert_eq!(TribeLevel::Court.member_cap(), None);
        assert_eq!(TribeLevel::Castle.member_cap(), None);
    }

    #[test]
    fn creation_costs() {
        assert_eq!(TribeLevel::Hideout.creation_cost(), 500_000);
        assert_eq!(TribeLevel::Tenement.creation_cost(), 1_000_000);
        assert_eq!(TribeLevel::Manor.creation_cost(), 1_500_000);
        assert_eq!(TribeLevel::Court.creation_cost(), 2_000_000);
        assert_eq!(TribeLevel::Castle.creation_cost(), 2_500_000);
    }

    #[test]
    fn upgrade_cost_same_level_is_none() {
        assert_eq!(
            TribeLevel::Hideout.upgrade_cost_from(TribeLevel::Hideout),
            None
        );
    }

    #[test]
    fn upgrade_cost_downgrade_is_none() {
        assert_eq!(
            TribeLevel::Hideout.upgrade_cost_from(TribeLevel::Manor),
            None
        );
    }

    #[test]
    fn upgrade_cost_normal() {
        // Hideout → Castle = 2_500_000 - 500_000 = 2_000_000
        assert_eq!(
            TribeLevel::Castle.upgrade_cost_from(TribeLevel::Hideout),
            Some(2_000_000)
        );
        // Manor → Castle = 2_500_000 - 1_500_000 = 1_000_000
        assert_eq!(
            TribeLevel::Castle.upgrade_cost_from(TribeLevel::Manor),
            Some(1_000_000)
        );
    }

    #[test]
    fn feature_flags() {
        assert!(!TribeLevel::Hideout.has_armory());
        assert!(TribeLevel::Tenement.has_armory());
        assert!(!TribeLevel::Tenement.has_treasury());
        assert!(TribeLevel::Manor.has_treasury());
        assert!(!TribeLevel::Court.has_warfare());
        assert!(TribeLevel::Castle.has_warfare());
        assert!(TribeLevel::Castle.has_astral_machine());
    }

    #[test]
    fn max_defences() {
        assert_eq!(TribeLevel::Hideout.max_defences(), 5);
        assert_eq!(TribeLevel::Tenement.max_defences(), 10);
        assert_eq!(TribeLevel::Manor.max_defences(), 20);
        assert_eq!(TribeLevel::Court.max_defences(), 40);
        assert_eq!(TribeLevel::Castle.max_defences(), 50);
    }

    // --- create tribe ---

    #[test]
    fn create_tribe_ok() {
        let cost = validate_create_tribe(0, 600_000, "Warriors", "Altara").unwrap();
        assert_eq!(cost, 500_000);
    }

    #[test]
    fn create_tribe_wrong_city() {
        assert_eq!(
            validate_create_tribe(0, 600_000, "Warriors", "Las"),
            Err(CreateTribeError::WrongLocation)
        );
    }

    #[test]
    fn create_tribe_already_in_tribe() {
        assert_eq!(
            validate_create_tribe(5, 600_000, "Warriors", "Altara"),
            Err(CreateTribeError::AlreadyInTribe)
        );
    }

    #[test]
    fn create_tribe_empty_name() {
        assert_eq!(
            validate_create_tribe(0, 600_000, "", "Altara"),
            Err(CreateTribeError::InvalidName)
        );
    }

    #[test]
    fn create_tribe_not_enough_gold() {
        assert_eq!(
            validate_create_tribe(0, 100_000, "Warriors", "Altara"),
            Err(CreateTribeError::InsufficientGold)
        );
    }

    // --- upgrade ---

    #[test]
    fn upgrade_ok() {
        let (target, cost) = validate_upgrade(1, 1, TribeLevel::Hideout, 3, 2_000_000).unwrap();
        assert_eq!(target, TribeLevel::Manor);
        assert_eq!(cost, 1_000_000);
    }

    #[test]
    fn upgrade_not_owner() {
        assert_eq!(
            validate_upgrade(2, 1, TribeLevel::Hideout, 3, 2_000_000),
            Err(UpgradeTribeError::NotOwner)
        );
    }

    #[test]
    fn upgrade_same_level() {
        assert_eq!(
            validate_upgrade(1, 1, TribeLevel::Manor, 3, 2_000_000),
            Err(UpgradeTribeError::InvalidTarget)
        );
    }

    #[test]
    fn upgrade_exceeds_max() {
        assert_eq!(
            validate_upgrade(1, 1, TribeLevel::Castle, 6, 10_000_000),
            Err(UpgradeTribeError::ExceedsMaxLevel)
        );
    }

    #[test]
    fn upgrade_insufficient_gold() {
        assert_eq!(
            validate_upgrade(1, 1, TribeLevel::Hideout, 5, 100_000),
            Err(UpgradeTribeError::InsufficientGold)
        );
    }

    // --- join request ---

    #[test]
    fn join_request_ok() {
        assert!(validate_join_request(0, false, "Altara").is_ok());
    }

    #[test]
    fn join_request_already_in_tribe() {
        assert_eq!(
            validate_join_request(3, false, "Altara"),
            Err(JoinRequestError::AlreadyInTribe)
        );
    }

    #[test]
    fn join_request_already_requested() {
        assert_eq!(
            validate_join_request(0, true, "Altara"),
            Err(JoinRequestError::AlreadyRequested)
        );
    }

    #[test]
    fn join_request_wrong_location() {
        assert_eq!(
            validate_join_request(0, false, "Las"),
            Err(JoinRequestError::WrongLocation)
        );
    }

    // --- accept member ---

    #[test]
    fn accept_member_ok() {
        assert!(validate_accept_member(10, 10, TribeLevel::Hideout, 3).is_ok());
    }

    #[test]
    fn accept_member_wrong_tribe() {
        assert_eq!(
            validate_accept_member(10, 20, TribeLevel::Hideout, 3),
            Err(AcceptMemberError::RequestNotForTribe)
        );
    }

    #[test]
    fn accept_member_tribe_full() {
        assert_eq!(
            validate_accept_member(10, 10, TribeLevel::Hideout, 5),
            Err(AcceptMemberError::TribeFull)
        );
    }

    #[test]
    fn accept_member_unlimited_level() {
        // Court has no cap
        assert!(validate_accept_member(10, 10, TribeLevel::Court, 999).is_ok());
    }

    // --- leave tribe ---

    #[test]
    fn leave_tribe_member() {
        let outcome = leave_tribe(5, 10, 1, vec![]).unwrap();
        assert_eq!(outcome, LeaveOutcome::MemberLeft);
    }

    #[test]
    fn leave_tribe_owner_dissolves() {
        let outcome = leave_tribe(1, 10, 1, vec![2, 3, 4]).unwrap();
        assert_eq!(
            outcome,
            LeaveOutcome::Dissolved {
                member_ids: vec![2, 3, 4]
            }
        );
    }

    #[test]
    fn leave_tribe_not_in_tribe() {
        assert_eq!(
            leave_tribe(1, 0, 1, vec![]),
            Err(LeaveTribeError::NotInTribe)
        );
    }

    // --- kick member ---

    #[test]
    fn kick_by_owner() {
        assert!(validate_kick(1, 1, false, 5, 10, 10).is_ok());
    }

    #[test]
    fn kick_by_permitted_member() {
        assert!(validate_kick(3, 1, true, 5, 10, 10).is_ok());
    }

    #[test]
    fn kick_no_permission() {
        assert_eq!(
            validate_kick(3, 1, false, 5, 10, 10),
            Err(KickMemberError::NoPermission)
        );
    }

    #[test]
    fn kick_owner() {
        assert_eq!(
            validate_kick(3, 1, true, 1, 10, 10),
            Err(KickMemberError::CannotKickOwner)
        );
    }

    #[test]
    fn kick_not_in_tribe() {
        assert_eq!(
            validate_kick(1, 1, false, 5, 20, 10),
            Err(KickMemberError::NotInTribe)
        );
    }

    // --- defence purchase ---

    #[test]
    fn buy_defences_ok() {
        let result = validate_buy_defences(&DefencePurchase {
            buyer_id: 1,
            owner_id: 1,
            buyer_has_army_perm: false,
            level: TribeLevel::Manor,
            current_traps: 5,
            current_agents: 3,
            buy_traps: 3,
            buy_agents: 2,
            tribe_gold: 100_000,
        })
        .unwrap();
        assert_eq!(result.new_traps, 8);
        assert_eq!(result.new_agents, 5);
        assert_eq!(result.gold_cost, 3 * 1_000 + 2 * 10_000);
    }

    #[test]
    fn buy_defences_no_permission() {
        assert_eq!(
            validate_buy_defences(&DefencePurchase {
                buyer_id: 5,
                owner_id: 1,
                buyer_has_army_perm: false,
                level: TribeLevel::Manor,
                current_traps: 0,
                current_agents: 0,
                buy_traps: 1,
                buy_agents: 0,
                tribe_gold: 100_000,
            }),
            Err(BuyDefencesError::NoPermission)
        );
    }

    #[test]
    fn buy_defences_nothing() {
        assert_eq!(
            validate_buy_defences(&DefencePurchase {
                buyer_id: 1,
                owner_id: 1,
                buyer_has_army_perm: false,
                level: TribeLevel::Manor,
                current_traps: 0,
                current_agents: 0,
                buy_traps: 0,
                buy_agents: 0,
                tribe_gold: 100_000,
            }),
            Err(BuyDefencesError::NothingToBuy)
        );
    }

    #[test]
    fn buy_defences_trap_cap() {
        assert_eq!(
            validate_buy_defences(&DefencePurchase {
                buyer_id: 1,
                owner_id: 1,
                buyer_has_army_perm: false,
                level: TribeLevel::Hideout, // max 5
                current_traps: 3,
                current_agents: 0,
                buy_traps: 5,
                buy_agents: 0,
                tribe_gold: 100_000,
            }),
            Err(BuyDefencesError::TrapCapExceeded)
        );
    }

    #[test]
    fn buy_defences_insufficient_gold() {
        assert_eq!(
            validate_buy_defences(&DefencePurchase {
                buyer_id: 1,
                owner_id: 1,
                buyer_has_army_perm: false,
                level: TribeLevel::Castle,
                current_traps: 0,
                current_agents: 0,
                buy_traps: 10,
                buy_agents: 5,
                tribe_gold: 10_000,
            }),
            Err(BuyDefencesError::InsufficientGold)
        );
    }

    // --- army purchase ---

    #[test]
    fn buy_army_ok() {
        let result = validate_buy_army(&ArmyPurchase {
            buyer_id: 1,
            owner_id: 1,
            buyer_has_army_perm: false,
            level: TribeLevel::Castle,
            buy_soldiers: 10,
            buy_fortifications: 5,
            tribe_gold: 100_000,
        })
        .unwrap();
        assert_eq!(result.soldiers_added, 10);
        assert_eq!(result.fortifications_added, 5);
        assert_eq!(result.gold_cost, 15_000);
    }

    #[test]
    fn buy_army_level_too_low() {
        assert_eq!(
            validate_buy_army(&ArmyPurchase {
                buyer_id: 1,
                owner_id: 1,
                buyer_has_army_perm: false,
                level: TribeLevel::Court,
                buy_soldiers: 1,
                buy_fortifications: 0,
                tribe_gold: 100_000,
            }),
            Err(BuyArmyError::LevelTooLow)
        );
    }

    #[test]
    fn buy_army_insufficient_gold() {
        assert_eq!(
            validate_buy_army(&ArmyPurchase {
                buyer_id: 1,
                owner_id: 1,
                buyer_has_army_perm: false,
                level: TribeLevel::Castle,
                buy_soldiers: 100,
                buy_fortifications: 100,
                tribe_gold: 10_000,
            }),
            Err(BuyArmyError::InsufficientGold)
        );
    }

    // --- hospital pass ---

    #[test]
    fn hospital_pass_ok() {
        let cost = validate_buy_hospital_pass(false, 200).unwrap();
        assert_eq!(cost, 100);
    }

    #[test]
    fn hospital_pass_already_owned() {
        assert_eq!(
            validate_buy_hospital_pass(true, 200),
            Err(BuyHospitalPassError::AlreadyOwned)
        );
    }

    #[test]
    fn hospital_pass_insufficient_mithril() {
        assert_eq!(
            validate_buy_hospital_pass(false, 50),
            Err(BuyHospitalPassError::InsufficientMithril)
        );
    }

    // --- loan ---

    #[test]
    fn loan_ok() {
        assert!(validate_loan(1, 1, false, 10, 10, 5000, 10000).is_ok());
    }

    #[test]
    fn loan_with_perm() {
        assert!(validate_loan(5, 1, true, 10, 10, 5000, 10000).is_ok());
    }

    #[test]
    fn loan_no_permission() {
        assert_eq!(
            validate_loan(5, 1, false, 10, 10, 5000, 10000),
            Err(LoanError::NoPermission)
        );
    }

    #[test]
    fn loan_not_in_tribe() {
        assert_eq!(
            validate_loan(1, 1, false, 20, 10, 5000, 10000),
            Err(LoanError::NotInTribe)
        );
    }

    #[test]
    fn loan_zero_amount() {
        assert_eq!(
            validate_loan(1, 1, false, 10, 10, 0, 10000),
            Err(LoanError::InvalidAmount)
        );
    }

    #[test]
    fn loan_insufficient_funds() {
        assert_eq!(
            validate_loan(1, 1, false, 10, 10, 5000, 3000),
            Err(LoanError::InsufficientFunds)
        );
    }
}
