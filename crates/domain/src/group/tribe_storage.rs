//! Tribe shared storage — deposit, reserve, withdraw, and give flows.
//!
//! Ported from `tribearmor.php`, `tribeware.php`, `tribeherbs.php`,
//! `tribeminerals.php`, and `tribeastral.php`.
//!
//! Five storage areas share a common access pattern:
//!
//! | Area       | Table           | Min level | Permission    |
//! |------------|-----------------|-----------|---------------|
//! | Armory     | `tribe_zbroj`   | 2         | `armory`      |
//! | Warehouse  | `tribe_mag`     | 2         | `warehouse`   |
//! | Herbs      | `tribe_herbs`   | 3         | `herbs`       |
//! | Treasury   | `tribe_minerals`| 3         | `bank`        |
//! | Astral     | `astral`        | 3         | `astralvault` |
//!
//! Common workflow:
//! 1. **Deposit** — any member puts personal items into tribe storage.
//! 2. **Reserve** — a member requests items, creating a reservation that
//!    increments the reserved count and blocks that quantity from being
//!    given to someone else until approved or rejected.
//! 3. **Give** — owner/permissioned member transfers items directly to a
//!    member.

use super::tribe::TribeLevel;
use super::tribe_admin::TribePermission;

// ---------------------------------------------------------------------------
// Storage area
// ---------------------------------------------------------------------------

/// Which tribe shared storage area an operation targets.
#[derive(Debug, Clone, Copy, PartialEq, Eq, Hash)]
pub enum StorageArea {
    /// Equipment: weapons, armour, arrows, rings, loot, tools, plans.
    Armory,
    /// Potions and mage scrolls.
    Warehouse,
    /// Herb and seed storage.
    Herbs,
    /// Minerals, ores, woods — plus gold/mithril from tribe treasury.
    Treasury,
    /// Astral vault: map/plan/recipe pieces, components, constructs,
    /// elixirs, and a safe-box upgrade path.
    Astral,
}

impl StorageArea {
    /// Minimum [`TribeLevel`] required to access this area.
    pub fn min_level(self) -> TribeLevel {
        match self {
            Self::Armory | Self::Warehouse => TribeLevel::Tenement,
            Self::Herbs | Self::Treasury | Self::Astral => TribeLevel::Manor,
        }
    }

    /// The [`TribePermission`] that grants a non-owner the right to
    /// **give** items from this area to other members.
    pub fn give_permission(self) -> TribePermission {
        match self {
            Self::Armory => TribePermission::Armory,
            Self::Warehouse => TribePermission::Warehouse,
            Self::Herbs => TribePermission::Herbs,
            Self::Treasury => TribePermission::Bank,
            Self::Astral => TribePermission::AstralVault,
        }
    }

    /// Reservation type code stored in `tribe_reserv.type`.
    pub fn reservation_code(self) -> char {
        match self {
            Self::Armory => 'A',
            Self::Warehouse => 'P',
            Self::Herbs => 'H',
            Self::Treasury => 'M',
            // Astral vault does not use the reservation table.
            Self::Astral => 'X',
        }
    }

    /// Whether regular members can create reservations (request items).
    ///
    /// All areas except Astral use the `tribe_reserv` table.
    pub fn supports_reservations(self) -> bool {
        !matches!(self, Self::Astral)
    }
}

// ---------------------------------------------------------------------------
// Access check
// ---------------------------------------------------------------------------

/// Errors when validating access to a storage area.
#[derive(Debug, Clone, Copy, PartialEq, Eq)]
pub enum StorageAccessError {
    /// Player is not in any tribe.
    NotInTribe,
    /// Tribe level is too low for this storage area.
    LevelTooLow {
        area: StorageArea,
        required: TribeLevel,
        actual: TribeLevel,
    },
}

/// Validate that a player can access a storage area (view / deposit).
///
/// Viewing and depositing are available to every tribe member as long as
/// the tribe meets the level requirement.
pub fn validate_access(
    player_tribe: Option<i32>,
    tribe_id: i32,
    tribe_level: TribeLevel,
    area: StorageArea,
) -> Result<(), StorageAccessError> {
    match player_tribe {
        Some(t) if t == tribe_id => {}
        _ => return Err(StorageAccessError::NotInTribe),
    }
    let required = area.min_level();
    if tribe_level < required {
        return Err(StorageAccessError::LevelTooLow {
            area,
            required,
            actual: tribe_level,
        });
    }
    Ok(())
}

// ---------------------------------------------------------------------------
// Give permission check
// ---------------------------------------------------------------------------

/// Errors when validating the give/withdraw right.
#[derive(Debug, Clone, Copy, PartialEq, Eq)]
pub enum GivePermissionError {
    /// Caller has no permission to give items from this area.
    NoPermission,
}

/// Check whether the caller can give (withdraw) items from a storage
/// area. The tribe owner always can; other members need the area's
/// specific permission flag.
pub fn validate_give_permission(
    caller_id: i32,
    owner_id: i32,
    has_permission: bool,
) -> Result<(), GivePermissionError> {
    if caller_id == owner_id || has_permission {
        Ok(())
    } else {
        Err(GivePermissionError::NoPermission)
    }
}

// ---------------------------------------------------------------------------
// Deposit validation
// ---------------------------------------------------------------------------

/// Errors when depositing items into tribe storage.
#[derive(Debug, Clone, Copy, PartialEq, Eq)]
pub enum DepositError {
    /// Amount must be positive.
    ZeroOrNegativeAmount,
    /// Player does not own enough of the item.
    InsufficientPersonalStock { have: i64, requested: i64 },
}

/// Validate a deposit (player → tribe storage).
///
/// `personal_stock` is how many the player currently owns.
/// `amount` is how many they want to deposit.
pub fn validate_deposit(personal_stock: i64, amount: i64) -> Result<(), DepositError> {
    if amount <= 0 {
        return Err(DepositError::ZeroOrNegativeAmount);
    }
    if personal_stock < amount {
        return Err(DepositError::InsufficientPersonalStock {
            have: personal_stock,
            requested: amount,
        });
    }
    Ok(())
}

// ---------------------------------------------------------------------------
// Reservation (request) validation
// ---------------------------------------------------------------------------

/// Errors when requesting (reserving) items from tribe storage.
#[derive(Debug, Clone, Copy, PartialEq, Eq)]
pub enum ReserveError {
    /// Amount must be positive.
    ZeroOrNegativeAmount,
    /// Not enough *available* (total − reserved) items.
    InsufficientAvailable { available: i64, requested: i64 },
    /// This storage area does not support reservations.
    NotSupported,
}

/// Available quantity = total − reserved (floored at zero).
pub fn available_quantity(total: i64, reserved: i64) -> i64 {
    (total - reserved).max(0)
}

/// Validate a reservation request.
///
/// `total` is the total stock in tribe storage, `reserved` is how many
/// are already reserved by other requests.
pub fn validate_reserve(
    area: StorageArea,
    total: i64,
    reserved: i64,
    amount: i64,
) -> Result<(), ReserveError> {
    if !area.supports_reservations() {
        return Err(ReserveError::NotSupported);
    }
    if amount <= 0 {
        return Err(ReserveError::ZeroOrNegativeAmount);
    }
    let avail = available_quantity(total, reserved);
    if avail < amount {
        return Err(ReserveError::InsufficientAvailable {
            available: avail,
            requested: amount,
        });
    }
    Ok(())
}

// ---------------------------------------------------------------------------
// Give (withdrawal) validation
// ---------------------------------------------------------------------------

/// Errors when giving items from tribe storage to a member.
#[derive(Debug, Clone, Copy, PartialEq, Eq)]
pub enum GiveError {
    /// Amount must be positive.
    ZeroOrNegativeAmount,
    /// Recipient is not in the same tribe.
    RecipientNotInTribe,
    /// Not enough *available* (total − reserved) items for non-reserved
    /// give, or not enough total for any give.
    InsufficientStock { have: i64, requested: i64 },
}

/// Input for a give validation.
pub struct GiveCheck {
    /// Total quantity in tribe storage.
    pub total: i64,
    /// Currently reserved (already claimed by pending requests).
    pub reserved: i64,
    /// Amount the admin wants to give.
    pub amount: i64,
    /// Whether this give is fulfilment of a pending reservation.
    ///
    /// When `true`, the reserved amount is not subtracted from the
    /// available pool (the reservation already accounts for it).
    pub is_reservation_fulfilment: bool,
    /// Tribe id of the recipient player.
    pub recipient_tribe: Option<i32>,
    /// The tribe performing the give.
    pub tribe_id: i32,
}

/// Validate a give (tribe → member) transfer.
pub fn validate_give(check: &GiveCheck) -> Result<(), GiveError> {
    if check.amount <= 0 {
        return Err(GiveError::ZeroOrNegativeAmount);
    }
    match check.recipient_tribe {
        Some(t) if t == check.tribe_id => {}
        _ => return Err(GiveError::RecipientNotInTribe),
    }
    let effective_stock = if check.is_reservation_fulfilment {
        // The reservation already blocked these; just ensure total
        // actually has enough.
        check.total
    } else {
        available_quantity(check.total, check.reserved)
    };
    if effective_stock < check.amount {
        return Err(GiveError::InsufficientStock {
            have: effective_stock,
            requested: check.amount,
        });
    }
    Ok(())
}

// ---------------------------------------------------------------------------
// Armory-specific: equipment type filter
// ---------------------------------------------------------------------------

/// Equipment types storable in the armory.
///
/// Matches the PHP `$arrType` array in `tribearmor.php`.  Quest items
/// (`Q`) are explicitly excluded.
pub const ARMORY_EQUIPMENT_TYPES: [char; 13] = [
    'W', // Weapons
    'A', // Armours
    'H', // Helmets
    'L', // Legs
    'S', // Shields
    'B', // Bows
    'T', // Staffs
    'C', // Capes
    'R', // Arrows
    'I', // Rings
    'O', // Loot
    'E', // Tools
    'P', // Plans
];

/// Whether an equipment type code may be stored in the armory.
pub fn is_armory_eligible(eq_type: char) -> bool {
    ARMORY_EQUIPMENT_TYPES.contains(&eq_type)
}

/// Errors when depositing equipment into the armory.
#[derive(Debug, Clone, Copy, PartialEq, Eq)]
pub enum ArmoryDepositError {
    /// The equipment item is a quest item and cannot be deposited.
    QuestItem,
    /// The item must be unequipped (`status = 'U'`).
    NotUnequipped,
    /// General deposit validation failed.
    Deposit(DepositError),
}

/// Validate depositing equipment into the tribe armory.
///
/// PHP checks: type ≠ Q, status = U, owner matches, amount ≤ owned.
/// For arrows (type 'R'), the "amount" is actually the `wt` field.
pub fn validate_armory_deposit(
    eq_type: char,
    eq_status: char,
    personal_stock: i64,
    amount: i64,
) -> Result<(), ArmoryDepositError> {
    if eq_type == 'Q' {
        return Err(ArmoryDepositError::QuestItem);
    }
    if eq_status != 'U' {
        return Err(ArmoryDepositError::NotUnequipped);
    }
    validate_deposit(personal_stock, amount).map_err(ArmoryDepositError::Deposit)
}

// ---------------------------------------------------------------------------
// Warehouse-specific: potion deposit
// ---------------------------------------------------------------------------

/// Errors when depositing potions into the warehouse.
#[derive(Debug, Clone, Copy, PartialEq, Eq)]
pub enum WarehouseDepositError {
    /// Potion must have status 'K' (in player's inventory).
    NotInInventory,
    /// General deposit validation failed.
    Deposit(DepositError),
}

/// Validate depositing potions into the tribe warehouse.
///
/// PHP checks: potion status = 'K', owner matches, amount ≤ owned.
pub fn validate_warehouse_deposit(
    potion_status: char,
    personal_stock: i64,
    amount: i64,
) -> Result<(), WarehouseDepositError> {
    if potion_status != 'K' {
        return Err(WarehouseDepositError::NotInInventory);
    }
    validate_deposit(personal_stock, amount).map_err(WarehouseDepositError::Deposit)
}

// ---------------------------------------------------------------------------
// Treasury-specific: gold and mithril handling
// ---------------------------------------------------------------------------

/// Whether a treasury item key refers to gold or mithril (stored on
/// `tribes` table) rather than minerals (stored on `tribe_minerals`).
///
/// PHP: `in_array($key, array('credits', 'platinum'))`.
pub fn is_currency_key(key: &str) -> bool {
    matches!(key, "credits" | "platinum")
}

// ---------------------------------------------------------------------------
// Astral vault: safe-box upgrade
// ---------------------------------------------------------------------------

/// Costs for astral safe-box upgrades (gold, mithril, adamantium,
/// crystal, meteor) by target level (1-indexed, level 1→2→3).
pub const SAFE_BOX_COSTS: [[i64; 5]; 3] = [
    [200_000, 200, 100, 0, 0],     // → level 1
    [400_000, 400, 200, 100, 0],   // → level 2
    [800_000, 800, 400, 200, 100], // → level 3
];

/// Maximum safe-box level.
pub const SAFE_BOX_MAX_LEVEL: i16 = 3;

/// Errors when upgrading the astral safe box.
#[derive(Debug, Clone, Copy, PartialEq, Eq)]
pub enum SafeBoxUpgradeError {
    /// Caller is not the owner or doesn't have astral vault permission.
    NoPermission,
    /// Safe box is already at maximum level.
    AlreadyMaxLevel,
    /// Not enough gold in tribe treasury.
    InsufficientGold { need: i64, have: i64 },
    /// Not enough mithril (platinum) in tribe treasury.
    InsufficientMithril { need: i64, have: i64 },
    /// Not enough adamantium in tribe mineral storage.
    InsufficientAdamantium { need: i64, have: i64 },
    /// Not enough crystal in tribe mineral storage.
    InsufficientCrystal { need: i64, have: i64 },
    /// Not enough meteor in tribe mineral storage.
    InsufficientMeteor { need: i64, have: i64 },
}

/// Resources available for the upgrade check.
pub struct SafeBoxResources {
    pub gold: i64,
    pub mithril: i64,
    pub adamantium: i64,
    pub crystal: i64,
    pub meteor: i64,
}

/// Validate an astral safe-box upgrade.
///
/// `current_level` is 0–2 (the existing level); target is current + 1.
pub fn validate_safe_box_upgrade(
    caller_id: i32,
    owner_id: i32,
    has_astral_perm: bool,
    current_level: i16,
    resources: &SafeBoxResources,
) -> Result<[i64; 5], SafeBoxUpgradeError> {
    if caller_id != owner_id && !has_astral_perm {
        return Err(SafeBoxUpgradeError::NoPermission);
    }
    if current_level >= SAFE_BOX_MAX_LEVEL {
        return Err(SafeBoxUpgradeError::AlreadyMaxLevel);
    }
    // current_level is 0..2 here (checked above), safe to cast.
    let idx = usize::try_from(current_level).expect("level is 0..2");
    let costs = SAFE_BOX_COSTS[idx];

    if resources.gold < costs[0] {
        return Err(SafeBoxUpgradeError::InsufficientGold {
            need: costs[0],
            have: resources.gold,
        });
    }
    if resources.mithril < costs[1] {
        return Err(SafeBoxUpgradeError::InsufficientMithril {
            need: costs[1],
            have: resources.mithril,
        });
    }
    if resources.adamantium < costs[2] {
        return Err(SafeBoxUpgradeError::InsufficientAdamantium {
            need: costs[2],
            have: resources.adamantium,
        });
    }
    if resources.crystal < costs[3] {
        return Err(SafeBoxUpgradeError::InsufficientCrystal {
            need: costs[3],
            have: resources.crystal,
        });
    }
    if resources.meteor < costs[4] {
        return Err(SafeBoxUpgradeError::InsufficientMeteor {
            need: costs[4],
            have: resources.meteor,
        });
    }
    Ok(costs)
}

// =========================================================================
// Tests
// =========================================================================

#[cfg(test)]
mod tests {
    use super::*;

    // -- StorageArea properties --

    #[test]
    fn area_min_levels() {
        assert_eq!(StorageArea::Armory.min_level(), TribeLevel::Tenement);
        assert_eq!(StorageArea::Warehouse.min_level(), TribeLevel::Tenement);
        assert_eq!(StorageArea::Herbs.min_level(), TribeLevel::Manor);
        assert_eq!(StorageArea::Treasury.min_level(), TribeLevel::Manor);
        assert_eq!(StorageArea::Astral.min_level(), TribeLevel::Manor);
    }

    #[test]
    fn area_give_permissions() {
        assert_eq!(
            StorageArea::Armory.give_permission(),
            TribePermission::Armory
        );
        assert_eq!(
            StorageArea::Warehouse.give_permission(),
            TribePermission::Warehouse
        );
        assert_eq!(StorageArea::Herbs.give_permission(), TribePermission::Herbs);
        assert_eq!(
            StorageArea::Treasury.give_permission(),
            TribePermission::Bank
        );
        assert_eq!(
            StorageArea::Astral.give_permission(),
            TribePermission::AstralVault
        );
    }

    #[test]
    fn area_reservation_codes() {
        assert_eq!(StorageArea::Armory.reservation_code(), 'A');
        assert_eq!(StorageArea::Warehouse.reservation_code(), 'P');
        assert_eq!(StorageArea::Herbs.reservation_code(), 'H');
        assert_eq!(StorageArea::Treasury.reservation_code(), 'M');
    }

    #[test]
    fn astral_does_not_support_reservations() {
        assert!(!StorageArea::Astral.supports_reservations());
        assert!(StorageArea::Armory.supports_reservations());
    }

    // -- Access validation --

    #[test]
    fn access_ok_at_proper_level() {
        assert!(validate_access(Some(1), 1, TribeLevel::Tenement, StorageArea::Armory).is_ok());
        assert!(validate_access(Some(1), 1, TribeLevel::Manor, StorageArea::Herbs).is_ok());
        assert!(validate_access(Some(1), 1, TribeLevel::Castle, StorageArea::Astral).is_ok());
    }

    #[test]
    fn access_denied_no_tribe() {
        assert_eq!(
            validate_access(None, 1, TribeLevel::Castle, StorageArea::Armory),
            Err(StorageAccessError::NotInTribe)
        );
    }

    #[test]
    fn access_denied_different_tribe() {
        assert_eq!(
            validate_access(Some(2), 1, TribeLevel::Castle, StorageArea::Armory),
            Err(StorageAccessError::NotInTribe)
        );
    }

    #[test]
    fn access_denied_level_too_low() {
        let result = validate_access(Some(1), 1, TribeLevel::Hideout, StorageArea::Armory);
        assert!(matches!(
            result,
            Err(StorageAccessError::LevelTooLow { .. })
        ));
    }

    #[test]
    fn access_herbs_at_tenement_denied() {
        let result = validate_access(Some(1), 1, TribeLevel::Tenement, StorageArea::Herbs);
        assert!(matches!(
            result,
            Err(StorageAccessError::LevelTooLow { .. })
        ));
    }

    // -- Give permission --

    #[test]
    fn owner_can_always_give() {
        assert!(validate_give_permission(1, 1, false).is_ok());
    }

    #[test]
    fn permitted_member_can_give() {
        assert!(validate_give_permission(2, 1, true).is_ok());
    }

    #[test]
    fn unpermitted_member_cannot_give() {
        assert_eq!(
            validate_give_permission(2, 1, false),
            Err(GivePermissionError::NoPermission)
        );
    }

    // -- Deposit validation --

    #[test]
    fn deposit_ok() {
        assert!(validate_deposit(100, 50).is_ok());
        assert!(validate_deposit(1, 1).is_ok());
    }

    #[test]
    fn deposit_zero_amount() {
        assert_eq!(
            validate_deposit(10, 0),
            Err(DepositError::ZeroOrNegativeAmount)
        );
    }

    #[test]
    fn deposit_negative_amount() {
        assert_eq!(
            validate_deposit(10, -1),
            Err(DepositError::ZeroOrNegativeAmount)
        );
    }

    #[test]
    fn deposit_insufficient_stock() {
        assert_eq!(
            validate_deposit(5, 10),
            Err(DepositError::InsufficientPersonalStock {
                have: 5,
                requested: 10
            })
        );
    }

    // -- Reserve validation --

    #[test]
    fn reserve_ok() {
        assert!(validate_reserve(StorageArea::Armory, 100, 20, 50).is_ok());
    }

    #[test]
    fn reserve_exact_available() {
        assert!(validate_reserve(StorageArea::Herbs, 100, 60, 40).is_ok());
    }

    #[test]
    fn reserve_zero() {
        assert_eq!(
            validate_reserve(StorageArea::Armory, 100, 0, 0),
            Err(ReserveError::ZeroOrNegativeAmount)
        );
    }

    #[test]
    fn reserve_exceeds_available() {
        assert_eq!(
            validate_reserve(StorageArea::Armory, 100, 80, 30),
            Err(ReserveError::InsufficientAvailable {
                available: 20,
                requested: 30
            })
        );
    }

    #[test]
    fn reserve_astral_not_supported() {
        assert_eq!(
            validate_reserve(StorageArea::Astral, 100, 0, 1),
            Err(ReserveError::NotSupported)
        );
    }

    // -- Available quantity --

    #[test]
    fn available_qty_normal() {
        assert_eq!(available_quantity(100, 30), 70);
    }

    #[test]
    fn available_qty_saturates() {
        assert_eq!(available_quantity(10, 20), 0);
    }

    // -- Give validation --

    #[test]
    fn give_ok() {
        let check = GiveCheck {
            total: 100,
            reserved: 20,
            amount: 50,
            is_reservation_fulfilment: false,
            recipient_tribe: Some(1),
            tribe_id: 1,
        };
        assert!(validate_give(&check).is_ok());
    }

    #[test]
    fn give_recipient_not_in_tribe() {
        let check = GiveCheck {
            total: 100,
            reserved: 0,
            amount: 1,
            is_reservation_fulfilment: false,
            recipient_tribe: Some(2),
            tribe_id: 1,
        };
        assert_eq!(validate_give(&check), Err(GiveError::RecipientNotInTribe));
    }

    #[test]
    fn give_recipient_no_tribe() {
        let check = GiveCheck {
            total: 100,
            reserved: 0,
            amount: 1,
            is_reservation_fulfilment: false,
            recipient_tribe: None,
            tribe_id: 1,
        };
        assert_eq!(validate_give(&check), Err(GiveError::RecipientNotInTribe));
    }

    #[test]
    fn give_zero_amount() {
        let check = GiveCheck {
            total: 100,
            reserved: 0,
            amount: 0,
            is_reservation_fulfilment: false,
            recipient_tribe: Some(1),
            tribe_id: 1,
        };
        assert_eq!(validate_give(&check), Err(GiveError::ZeroOrNegativeAmount));
    }

    #[test]
    fn give_exceeds_available_non_reserved() {
        let check = GiveCheck {
            total: 100,
            reserved: 90,
            amount: 20,
            is_reservation_fulfilment: false,
            recipient_tribe: Some(1),
            tribe_id: 1,
        };
        assert_eq!(
            validate_give(&check),
            Err(GiveError::InsufficientStock {
                have: 10,
                requested: 20
            })
        );
    }

    #[test]
    fn give_reservation_fulfilment_ignores_reserved() {
        let check = GiveCheck {
            total: 100,
            reserved: 90,
            amount: 50,
            is_reservation_fulfilment: true,
            recipient_tribe: Some(1),
            tribe_id: 1,
        };
        assert!(validate_give(&check).is_ok());
    }

    #[test]
    fn give_reservation_fulfilment_still_checks_total() {
        let check = GiveCheck {
            total: 30,
            reserved: 20,
            amount: 50,
            is_reservation_fulfilment: true,
            recipient_tribe: Some(1),
            tribe_id: 1,
        };
        assert_eq!(
            validate_give(&check),
            Err(GiveError::InsufficientStock {
                have: 30,
                requested: 50
            })
        );
    }

    // -- Armory deposit --

    #[test]
    fn armory_deposit_ok() {
        assert!(validate_armory_deposit('W', 'U', 10, 5).is_ok());
    }

    #[test]
    fn armory_deposit_quest_item() {
        assert_eq!(
            validate_armory_deposit('Q', 'U', 10, 5),
            Err(ArmoryDepositError::QuestItem)
        );
    }

    #[test]
    fn armory_deposit_equipped() {
        assert_eq!(
            validate_armory_deposit('W', 'E', 10, 5),
            Err(ArmoryDepositError::NotUnequipped)
        );
    }

    #[test]
    fn armory_deposit_insufficient() {
        assert_eq!(
            validate_armory_deposit('A', 'U', 3, 5),
            Err(ArmoryDepositError::Deposit(
                DepositError::InsufficientPersonalStock {
                    have: 3,
                    requested: 5
                }
            ))
        );
    }

    #[test]
    fn armory_eligible_types() {
        for t in &ARMORY_EQUIPMENT_TYPES {
            assert!(is_armory_eligible(*t));
        }
        assert!(!is_armory_eligible('Q'));
        assert!(!is_armory_eligible('X'));
    }

    // -- Warehouse deposit --

    #[test]
    fn warehouse_deposit_ok() {
        assert!(validate_warehouse_deposit('K', 20, 10).is_ok());
    }

    #[test]
    fn warehouse_deposit_wrong_status() {
        assert_eq!(
            validate_warehouse_deposit('E', 20, 10),
            Err(WarehouseDepositError::NotInInventory)
        );
    }

    #[test]
    fn warehouse_deposit_insufficient() {
        assert_eq!(
            validate_warehouse_deposit('K', 3, 5),
            Err(WarehouseDepositError::Deposit(
                DepositError::InsufficientPersonalStock {
                    have: 3,
                    requested: 5
                }
            ))
        );
    }

    // -- Currency key detection --

    #[test]
    fn currency_keys() {
        assert!(is_currency_key("credits"));
        assert!(is_currency_key("platinum"));
        assert!(!is_currency_key("copperore"));
        assert!(!is_currency_key("iron"));
    }

    // -- Safe-box upgrade --

    #[test]
    fn safe_box_upgrade_level_0_ok() {
        let res = SafeBoxResources {
            gold: 200_000,
            mithril: 200,
            adamantium: 100,
            crystal: 0,
            meteor: 0,
        };
        let costs = validate_safe_box_upgrade(1, 1, false, 0, &res).unwrap();
        assert_eq!(costs, [200_000, 200, 100, 0, 0]);
    }

    #[test]
    fn safe_box_upgrade_level_2_ok() {
        let res = SafeBoxResources {
            gold: 1_000_000,
            mithril: 1000,
            adamantium: 500,
            crystal: 300,
            meteor: 200,
        };
        let costs = validate_safe_box_upgrade(1, 1, false, 2, &res).unwrap();
        assert_eq!(costs, [800_000, 800, 400, 200, 100]);
    }

    #[test]
    fn safe_box_upgrade_already_max() {
        let res = SafeBoxResources {
            gold: 1_000_000,
            mithril: 1000,
            adamantium: 500,
            crystal: 300,
            meteor: 200,
        };
        assert_eq!(
            validate_safe_box_upgrade(1, 1, false, 3, &res),
            Err(SafeBoxUpgradeError::AlreadyMaxLevel)
        );
    }

    #[test]
    fn safe_box_upgrade_no_permission() {
        let res = SafeBoxResources {
            gold: 1_000_000,
            mithril: 1000,
            adamantium: 500,
            crystal: 300,
            meteor: 200,
        };
        assert_eq!(
            validate_safe_box_upgrade(2, 1, false, 0, &res),
            Err(SafeBoxUpgradeError::NoPermission)
        );
    }

    #[test]
    fn safe_box_upgrade_with_permission() {
        let res = SafeBoxResources {
            gold: 200_000,
            mithril: 200,
            adamantium: 100,
            crystal: 0,
            meteor: 0,
        };
        assert!(validate_safe_box_upgrade(2, 1, true, 0, &res).is_ok());
    }

    #[test]
    fn safe_box_upgrade_insufficient_gold() {
        let res = SafeBoxResources {
            gold: 100,
            mithril: 200,
            adamantium: 100,
            crystal: 0,
            meteor: 0,
        };
        assert!(matches!(
            validate_safe_box_upgrade(1, 1, false, 0, &res),
            Err(SafeBoxUpgradeError::InsufficientGold { .. })
        ));
    }

    #[test]
    fn safe_box_upgrade_insufficient_mithril() {
        let res = SafeBoxResources {
            gold: 200_000,
            mithril: 10,
            adamantium: 100,
            crystal: 0,
            meteor: 0,
        };
        assert!(matches!(
            validate_safe_box_upgrade(1, 1, false, 0, &res),
            Err(SafeBoxUpgradeError::InsufficientMithril { .. })
        ));
    }

    #[test]
    fn safe_box_level_1_needs_crystal() {
        let res = SafeBoxResources {
            gold: 400_000,
            mithril: 400,
            adamantium: 200,
            crystal: 50,
            meteor: 0,
        };
        assert!(matches!(
            validate_safe_box_upgrade(1, 1, false, 1, &res),
            Err(SafeBoxUpgradeError::InsufficientCrystal { .. })
        ));
    }

    #[test]
    fn safe_box_level_2_needs_meteor() {
        let res = SafeBoxResources {
            gold: 800_000,
            mithril: 800,
            adamantium: 400,
            crystal: 200,
            meteor: 50,
        };
        assert!(matches!(
            validate_safe_box_upgrade(1, 1, false, 2, &res),
            Err(SafeBoxUpgradeError::InsufficientMeteor { .. })
        ));
    }
}
