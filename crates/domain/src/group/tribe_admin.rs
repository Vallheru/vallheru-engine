//! Tribe permissions, ranks, and admin actions.
//!
//! Ported from `tribeadmin.php` — permission flags, rank labels, and
//! admin-gate validation.  The owner can assign per-member permissions
//! and ranks.  Some permissions are gated by tribe level.

use super::tribe::TribeLevel;

// ---------------------------------------------------------------------------
// Permission flags
// ---------------------------------------------------------------------------

/// The 15 boolean permission flags assignable to tribe members.
///
/// Each corresponds to a column in `tribe_perm`.  The owner implicitly
/// has all permissions and never needs a row.
#[derive(Debug, Clone, Copy, PartialEq, Eq, Hash)]
pub enum TribePermission {
    /// Can edit clan descriptions (public/private messages).
    Messages,
    /// Can accept/reject pending join requests.
    Wait,
    /// Can kick members.
    Kick,
    /// Can buy soldiers and fortifications (Castle only).
    Army,
    /// Can initiate attacks on other clans (Castle only).
    Attack,
    /// Can loan money from the treasury.
    Loan,
    /// Can give items from the armory (level ≥ 2).
    Armory,
    /// Can give items from the warehouse (level ≥ 2).
    Warehouse,
    /// Can give minerals from the treasury (level ≥ 3).
    Bank,
    /// Can give herbs from the herb storage (level ≥ 3).
    Herbs,
    /// Can delete posts on the tribe forum.
    Forum,
    /// Can assign ranks to members.
    Ranks,
    /// Can send mass mail to all members.
    Mail,
    /// **Inverted**: when set, the member *cannot* view tribe info.
    InfoRestricted,
    /// Can manage astral vault and give astral items (level ≥ 3).
    AstralVault,
}

/// All permissions in DB column order.
pub const ALL_PERMISSIONS: [TribePermission; 15] = [
    TribePermission::Messages,
    TribePermission::Wait,
    TribePermission::Kick,
    TribePermission::Army,
    TribePermission::Attack,
    TribePermission::Loan,
    TribePermission::Armory,
    TribePermission::Warehouse,
    TribePermission::Bank,
    TribePermission::Herbs,
    TribePermission::Forum,
    TribePermission::Ranks,
    TribePermission::Mail,
    TribePermission::InfoRestricted,
    TribePermission::AstralVault,
];

impl TribePermission {
    /// Database column name for this permission.
    pub fn column_name(self) -> &'static str {
        match self {
            Self::Messages => "messages",
            Self::Wait => "wait",
            Self::Kick => "kick",
            Self::Army => "army",
            Self::Attack => "attack",
            Self::Loan => "loan",
            Self::Armory => "armory",
            Self::Warehouse => "warehouse",
            Self::Bank => "bank",
            Self::Herbs => "herbs",
            Self::Forum => "forum",
            Self::Ranks => "ranks",
            Self::Mail => "mail",
            Self::InfoRestricted => "info",
            Self::AstralVault => "astralvault",
        }
    }

    /// Whether this permission is available at the given tribe level.
    ///
    /// Unavailable permissions are forced to `false` regardless of what
    /// the owner sets.
    pub fn available_at(self, level: TribeLevel) -> bool {
        match self {
            Self::Army | Self::Attack => level >= TribeLevel::Castle,
            Self::AstralVault | Self::Bank | Self::Herbs => level >= TribeLevel::Manor,
            Self::Armory | Self::Warehouse => level >= TribeLevel::Tenement,
            _ => true,
        }
    }
}

// ---------------------------------------------------------------------------
// Permission set
// ---------------------------------------------------------------------------

/// A concrete set of permission flags for one member.
///
/// Stored as a simple bitfield for cheap copying and testing.
#[derive(Debug, Clone, Copy, PartialEq, Eq, Default)]
pub struct PermissionSet(u16);

impl PermissionSet {
    /// Build from an array of 15 booleans in `ALL_PERMISSIONS` order.
    pub fn from_flags(flags: [bool; 15]) -> Self {
        let mut bits = 0u16;
        for (i, &flag) in flags.iter().enumerate() {
            if flag {
                bits |= 1 << i;
            }
        }
        Self(bits)
    }

    /// Export to an array of 15 booleans in `ALL_PERMISSIONS` order.
    pub fn to_flags(self) -> [bool; 15] {
        let mut flags = [false; 15];
        for (i, flag) in flags.iter_mut().enumerate() {
            *flag = self.0 & (1 << i) != 0;
        }
        flags
    }

    /// Check whether a specific permission is granted.
    pub fn has(self, perm: TribePermission) -> bool {
        let idx = ALL_PERMISSIONS
            .iter()
            .position(|p| *p == perm)
            .expect("all permissions are in ALL_PERMISSIONS");
        self.0 & (1 << idx) != 0
    }

    /// Set a specific permission.
    pub fn set(&mut self, perm: TribePermission, value: bool) {
        let idx = ALL_PERMISSIONS
            .iter()
            .position(|p| *p == perm)
            .expect("all permissions are in ALL_PERMISSIONS");
        if value {
            self.0 |= 1 << idx;
        } else {
            self.0 &= !(1 << idx);
        }
    }

    /// Returns true if any permission is granted.
    pub fn any_granted(self) -> bool {
        self.0 != 0
    }

    /// Mask out permissions that are not available at the given level.
    ///
    /// This is what the PHP code does when saving: it forces unavailable
    /// permissions to 0 before writing to DB.
    pub fn apply_level_mask(&mut self, level: TribeLevel) {
        for (i, perm) in ALL_PERMISSIONS.iter().enumerate() {
            if !perm.available_at(level) {
                self.0 &= !(1 << i);
            }
        }
    }
}

// ---------------------------------------------------------------------------
// Permission validation
// ---------------------------------------------------------------------------

/// Error when setting member permissions.
#[derive(Debug, Clone, Copy, PartialEq, Eq)]
pub enum SetPermissionsError {
    /// Only the tribe owner can set permissions.
    NotOwner,
    /// Target player is not in this tribe.
    NotInTribe,
}

/// Validate that the caller can set permissions for a member.
pub fn validate_set_permissions(
    caller_id: i32,
    owner_id: i32,
    target_tribe_id: i32,
    tribe_id: i32,
) -> Result<(), SetPermissionsError> {
    if caller_id != owner_id {
        return Err(SetPermissionsError::NotOwner);
    }
    if target_tribe_id != tribe_id {
        return Err(SetPermissionsError::NotInTribe);
    }
    Ok(())
}

// ---------------------------------------------------------------------------
// Admin panel access
// ---------------------------------------------------------------------------

/// Whether a player can access the tribe admin panel at all.
///
/// PHP: owner always can; non-owner needs at least one permission flag set.
pub fn can_access_admin(player_id: i32, owner_id: i32, perms: PermissionSet) -> bool {
    player_id == owner_id || perms.any_granted()
}

/// Whether a player can perform a specific admin action.
///
/// Owner always can.  Otherwise the member needs the corresponding
/// permission flag.
pub fn has_admin_permission(
    player_id: i32,
    owner_id: i32,
    perms: PermissionSet,
    required: TribePermission,
) -> bool {
    player_id == owner_id || perms.has(required)
}

// ---------------------------------------------------------------------------
// Rank system
// ---------------------------------------------------------------------------

/// Maximum number of rank slots per tribe.
pub const MAX_RANK_SLOTS: usize = 10;

/// Maximum length (in characters) of a single rank label.
pub const MAX_RANK_LABEL_LEN: usize = 60;

/// Error when creating or editing ranks.
#[derive(Debug, Clone, Copy, PartialEq, Eq)]
pub enum RankError {
    /// Caller has no permission (not owner, no `ranks` perm).
    NoPermission,
    /// A rank label exceeds the maximum length.
    LabelTooLong,
}

/// Validate that the caller can manage ranks.
fn check_rank_permission(
    caller_id: i32,
    owner_id: i32,
    perms: PermissionSet,
) -> Result<(), RankError> {
    if !has_admin_permission(caller_id, owner_id, perms, TribePermission::Ranks) {
        return Err(RankError::NoPermission);
    }
    Ok(())
}

/// Validate and sanitize a set of rank labels.
///
/// Returns the labels trimmed.  Empty strings are kept as empty (unused
/// slots).
pub fn validate_rank_labels(
    caller_id: i32,
    owner_id: i32,
    perms: PermissionSet,
    labels: &[String; MAX_RANK_SLOTS],
) -> Result<[String; MAX_RANK_SLOTS], RankError> {
    check_rank_permission(caller_id, owner_id, perms)?;
    let mut result: [String; MAX_RANK_SLOTS] = Default::default();
    for (i, label) in labels.iter().enumerate() {
        let trimmed = label.trim().to_string();
        if trimmed.chars().count() > MAX_RANK_LABEL_LEN {
            return Err(RankError::LabelTooLong);
        }
        result[i] = trimmed;
    }
    Ok(result)
}

/// Error when assigning a rank to a player.
#[derive(Debug, Clone, Copy, PartialEq, Eq)]
pub enum AssignRankError {
    /// Caller has no permission.
    NoPermission,
    /// No ranks have been defined yet.
    NoRanksDefined,
    /// Target player is not in this tribe.
    NotInTribe,
}

/// Validate assigning a rank label to a member.
///
/// `ranks_exist`: whether the tribe has a `tribe_rank` row.
pub fn validate_assign_rank(
    caller_id: i32,
    owner_id: i32,
    perms: PermissionSet,
    ranks_exist: bool,
    target_tribe_id: i32,
    tribe_id: i32,
) -> Result<(), AssignRankError> {
    if !has_admin_permission(caller_id, owner_id, perms, TribePermission::Ranks) {
        return Err(AssignRankError::NoPermission);
    }
    if !ranks_exist {
        return Err(AssignRankError::NoRanksDefined);
    }
    if target_tribe_id != tribe_id {
        return Err(AssignRankError::NotInTribe);
    }
    Ok(())
}

// ---------------------------------------------------------------------------
// Tribe messages and tags
// ---------------------------------------------------------------------------

/// Maximum length of tribe tags (prefix/suffix before/after player names).
pub const MAX_TAG_LEN: usize = 5;

/// Error when editing tribe messages or tags.
#[derive(Debug, Clone, Copy, PartialEq, Eq)]
pub enum EditMessagesError {
    /// Caller has no permission.
    NoPermission,
}

/// Validate that the caller can edit tribe descriptions/messages.
pub fn validate_edit_messages(
    caller_id: i32,
    owner_id: i32,
    perms: PermissionSet,
) -> Result<(), EditMessagesError> {
    if !has_admin_permission(caller_id, owner_id, perms, TribePermission::Messages) {
        return Err(EditMessagesError::NoPermission);
    }
    Ok(())
}

/// Error when setting tribe tags.
#[derive(Debug, Clone, Copy, PartialEq, Eq)]
pub enum SetTagsError {
    /// Caller has no permission.
    NoPermission,
    /// A tag exceeds the maximum character length.
    TagTooLong,
}

/// Validate and sanitize tribe tags (prefix/suffix displayed around
/// member names).
pub fn validate_set_tags(
    caller_id: i32,
    owner_id: i32,
    perms: PermissionSet,
    prefix: &str,
    suffix: &str,
) -> Result<(String, String), SetTagsError> {
    if !has_admin_permission(caller_id, owner_id, perms, TribePermission::Messages) {
        return Err(SetTagsError::NoPermission);
    }
    let prefix = prefix.trim().to_string();
    let suffix = suffix.trim().to_string();
    if prefix.chars().count() > MAX_TAG_LEN {
        return Err(SetTagsError::TagTooLong);
    }
    if suffix.chars().count() > MAX_TAG_LEN {
        return Err(SetTagsError::TagTooLong);
    }
    Ok((prefix, suffix))
}

// ---------------------------------------------------------------------------
// Mass mail
// ---------------------------------------------------------------------------

/// Error when sending mail to all tribe members.
#[derive(Debug, Clone, Copy, PartialEq, Eq)]
pub enum TribeMailError {
    /// Caller has no permission.
    NoPermission,
    /// Title or body is empty.
    EmptyFields,
}

/// Validate sending a mass mail to all tribe members.
pub fn validate_tribe_mail(
    caller_id: i32,
    owner_id: i32,
    perms: PermissionSet,
    title: &str,
    body: &str,
) -> Result<(), TribeMailError> {
    if !has_admin_permission(caller_id, owner_id, perms, TribePermission::Mail) {
        return Err(TribeMailError::NoPermission);
    }
    if title.trim().is_empty() || body.trim().is_empty() {
        return Err(TribeMailError::EmptyFields);
    }
    Ok(())
}

// ---------------------------------------------------------------------------
// Pending member management
// ---------------------------------------------------------------------------

/// Error when accepting/rejecting a pending member from the admin panel.
#[derive(Debug, Clone, Copy, PartialEq, Eq)]
pub enum ManagePendingError {
    /// Caller has no permission (not owner, no `wait` perm).
    NoPermission,
}

/// Validate that the caller can accept/reject pending join requests.
pub fn validate_manage_pending(
    caller_id: i32,
    owner_id: i32,
    perms: PermissionSet,
) -> Result<(), ManagePendingError> {
    if !has_admin_permission(caller_id, owner_id, perms, TribePermission::Wait) {
        return Err(ManagePendingError::NoPermission);
    }
    Ok(())
}

// ---------------------------------------------------------------------------
// Level-gated admin menu items
// ---------------------------------------------------------------------------

/// Which admin menu items are available at a given tribe level.
///
/// Returns a list of step2 identifiers that should be shown.
pub fn available_admin_actions(level: TribeLevel, is_owner: bool) -> Vec<&'static str> {
    let mut actions = vec![
        "permissions",
        "rank",
        "mail",
        "messages",
        "nowy",
        "kick",
        "loan",
        "te",
        "asks",
        "traps",
    ];
    if level >= TribeLevel::Castle {
        actions.push("wojsko");
        actions.push("walka");
    }
    if is_owner && level < TribeLevel::Court {
        actions.push("upgrade");
    }
    actions
}

// ---------------------------------------------------------------------------
// Tests
// ---------------------------------------------------------------------------

#[cfg(test)]
mod tests {
    use super::*;

    // --- permission availability ---

    #[test]
    fn army_requires_castle() {
        assert!(!TribePermission::Army.available_at(TribeLevel::Court));
        assert!(TribePermission::Army.available_at(TribeLevel::Castle));
    }

    #[test]
    fn bank_requires_manor() {
        assert!(!TribePermission::Bank.available_at(TribeLevel::Tenement));
        assert!(TribePermission::Bank.available_at(TribeLevel::Manor));
    }

    #[test]
    fn armory_requires_tenement() {
        assert!(!TribePermission::Armory.available_at(TribeLevel::Hideout));
        assert!(TribePermission::Armory.available_at(TribeLevel::Tenement));
    }

    #[test]
    fn universally_available_perms() {
        for perm in [
            TribePermission::Messages,
            TribePermission::Wait,
            TribePermission::Kick,
            TribePermission::Loan,
            TribePermission::Forum,
            TribePermission::Ranks,
            TribePermission::Mail,
            TribePermission::InfoRestricted,
        ] {
            assert!(
                perm.available_at(TribeLevel::Hideout),
                "{perm:?} should be available at Hideout"
            );
        }
    }

    // --- PermissionSet ---

    #[test]
    fn permission_set_roundtrip() {
        let mut flags = [false; 15];
        flags[0] = true; // messages
        flags[5] = true; // loan
        flags[14] = true; // astralvault
        let set = PermissionSet::from_flags(flags);
        let out = set.to_flags();
        assert_eq!(flags, out);
    }

    #[test]
    fn permission_set_has() {
        let mut set = PermissionSet::default();
        assert!(!set.has(TribePermission::Kick));
        set.set(TribePermission::Kick, true);
        assert!(set.has(TribePermission::Kick));
        set.set(TribePermission::Kick, false);
        assert!(!set.has(TribePermission::Kick));
    }

    #[test]
    fn permission_set_any_granted() {
        assert!(!PermissionSet::default().any_granted());
        let mut set = PermissionSet::default();
        set.set(TribePermission::Forum, true);
        assert!(set.any_granted());
    }

    #[test]
    fn level_mask_clears_unavailable() {
        let mut set = PermissionSet::from_flags([true; 15]);
        set.apply_level_mask(TribeLevel::Hideout);
        // Army, Attack, AstralVault, Bank, Herbs, Armory, Warehouse should be off
        assert!(!set.has(TribePermission::Army));
        assert!(!set.has(TribePermission::Attack));
        assert!(!set.has(TribePermission::AstralVault));
        assert!(!set.has(TribePermission::Bank));
        assert!(!set.has(TribePermission::Herbs));
        assert!(!set.has(TribePermission::Armory));
        assert!(!set.has(TribePermission::Warehouse));
        // Universally available should stay on
        assert!(set.has(TribePermission::Messages));
        assert!(set.has(TribePermission::Kick));
        assert!(set.has(TribePermission::Loan));
    }

    #[test]
    fn level_mask_castle_keeps_all() {
        let mut set = PermissionSet::from_flags([true; 15]);
        set.apply_level_mask(TribeLevel::Castle);
        assert_eq!(set.to_flags(), [true; 15]);
    }

    // --- admin access ---

    #[test]
    fn owner_always_can_access_admin() {
        assert!(can_access_admin(1, 1, PermissionSet::default()));
    }

    #[test]
    fn member_needs_at_least_one_perm() {
        assert!(!can_access_admin(5, 1, PermissionSet::default()));
        let mut perms = PermissionSet::default();
        perms.set(TribePermission::Forum, true);
        assert!(can_access_admin(5, 1, perms));
    }

    #[test]
    fn has_admin_permission_owner() {
        assert!(has_admin_permission(
            1,
            1,
            PermissionSet::default(),
            TribePermission::Kick
        ));
    }

    #[test]
    fn has_admin_permission_member() {
        let mut perms = PermissionSet::default();
        perms.set(TribePermission::Kick, true);
        assert!(has_admin_permission(5, 1, perms, TribePermission::Kick));
        assert!(!has_admin_permission(
            5,
            1,
            perms,
            TribePermission::Messages
        ));
    }

    // --- set permissions ---

    #[test]
    fn set_permissions_owner_ok() {
        assert!(validate_set_permissions(1, 1, 10, 10).is_ok());
    }

    #[test]
    fn set_permissions_not_owner() {
        assert_eq!(
            validate_set_permissions(5, 1, 10, 10),
            Err(SetPermissionsError::NotOwner)
        );
    }

    #[test]
    fn set_permissions_target_not_in_tribe() {
        assert_eq!(
            validate_set_permissions(1, 1, 20, 10),
            Err(SetPermissionsError::NotInTribe)
        );
    }

    // --- rank labels ---

    #[test]
    fn validate_rank_labels_ok() {
        let labels: [String; 10] = std::array::from_fn(|i| format!("Rank {}", i + 1));
        let mut perms = PermissionSet::default();
        perms.set(TribePermission::Ranks, true);
        let result = validate_rank_labels(5, 1, perms, &labels).unwrap();
        assert_eq!(result[0], "Rank 1");
        assert_eq!(result[9], "Rank 10");
    }

    #[test]
    fn validate_rank_labels_no_perm() {
        let labels: [String; 10] = Default::default();
        assert_eq!(
            validate_rank_labels(5, 1, PermissionSet::default(), &labels),
            Err(RankError::NoPermission)
        );
    }

    #[test]
    fn validate_rank_labels_too_long() {
        let mut labels: [String; 10] = Default::default();
        labels[0] = "a".repeat(61);
        let mut perms = PermissionSet::default();
        perms.set(TribePermission::Ranks, true);
        assert_eq!(
            validate_rank_labels(5, 1, perms, &labels),
            Err(RankError::LabelTooLong)
        );
    }

    // --- assign rank ---

    #[test]
    fn assign_rank_ok() {
        let mut perms = PermissionSet::default();
        perms.set(TribePermission::Ranks, true);
        assert!(validate_assign_rank(5, 1, perms, true, 10, 10).is_ok());
    }

    #[test]
    fn assign_rank_no_ranks_defined() {
        let mut perms = PermissionSet::default();
        perms.set(TribePermission::Ranks, true);
        assert_eq!(
            validate_assign_rank(5, 1, perms, false, 10, 10),
            Err(AssignRankError::NoRanksDefined)
        );
    }

    #[test]
    fn assign_rank_not_in_tribe() {
        let mut perms = PermissionSet::default();
        perms.set(TribePermission::Ranks, true);
        assert_eq!(
            validate_assign_rank(5, 1, perms, true, 20, 10),
            Err(AssignRankError::NotInTribe)
        );
    }

    // --- tags ---

    #[test]
    fn set_tags_ok() {
        let mut perms = PermissionSet::default();
        perms.set(TribePermission::Messages, true);
        let (p, s) = validate_set_tags(5, 1, perms, "[GW]", "(GW)").unwrap();
        assert_eq!(p, "[GW]");
        assert_eq!(s, "(GW)");
    }

    #[test]
    fn set_tags_too_long() {
        let mut perms = PermissionSet::default();
        perms.set(TribePermission::Messages, true);
        assert_eq!(
            validate_set_tags(5, 1, perms, "ABCDEF", ""),
            Err(SetTagsError::TagTooLong)
        );
    }

    #[test]
    fn set_tags_no_perm() {
        assert_eq!(
            validate_set_tags(5, 1, PermissionSet::default(), "A", "B"),
            Err(SetTagsError::NoPermission)
        );
    }

    // --- tribe mail ---

    #[test]
    fn tribe_mail_ok() {
        let mut perms = PermissionSet::default();
        perms.set(TribePermission::Mail, true);
        assert!(validate_tribe_mail(5, 1, perms, "Title", "Body").is_ok());
    }

    #[test]
    fn tribe_mail_no_perm() {
        assert_eq!(
            validate_tribe_mail(5, 1, PermissionSet::default(), "T", "B"),
            Err(TribeMailError::NoPermission)
        );
    }

    #[test]
    fn tribe_mail_empty_title() {
        let mut perms = PermissionSet::default();
        perms.set(TribePermission::Mail, true);
        assert_eq!(
            validate_tribe_mail(5, 1, perms, "", "Body"),
            Err(TribeMailError::EmptyFields)
        );
    }

    #[test]
    fn tribe_mail_empty_body() {
        let mut perms = PermissionSet::default();
        perms.set(TribePermission::Mail, true);
        assert_eq!(
            validate_tribe_mail(5, 1, perms, "Title", "   "),
            Err(TribeMailError::EmptyFields)
        );
    }

    // --- manage pending ---

    #[test]
    fn manage_pending_owner() {
        assert!(validate_manage_pending(1, 1, PermissionSet::default()).is_ok());
    }

    #[test]
    fn manage_pending_with_perm() {
        let mut perms = PermissionSet::default();
        perms.set(TribePermission::Wait, true);
        assert!(validate_manage_pending(5, 1, perms).is_ok());
    }

    #[test]
    fn manage_pending_no_perm() {
        assert_eq!(
            validate_manage_pending(5, 1, PermissionSet::default()),
            Err(ManagePendingError::NoPermission)
        );
    }

    // --- admin menu ---

    #[test]
    fn admin_menu_hideout_owner() {
        let actions = available_admin_actions(TribeLevel::Hideout, true);
        assert!(actions.contains(&"upgrade"));
        assert!(!actions.contains(&"wojsko"));
        assert!(!actions.contains(&"walka"));
    }

    #[test]
    fn admin_menu_castle_owner() {
        let actions = available_admin_actions(TribeLevel::Castle, true);
        assert!(actions.contains(&"wojsko"));
        assert!(actions.contains(&"walka"));
        assert!(!actions.contains(&"upgrade"));
    }

    #[test]
    fn admin_menu_court_non_owner() {
        let actions = available_admin_actions(TribeLevel::Court, false);
        assert!(!actions.contains(&"upgrade"));
        assert!(!actions.contains(&"wojsko"));
    }

    // --- column names ---

    #[test]
    fn all_column_names_unique() {
        let names: Vec<&str> = ALL_PERMISSIONS.iter().map(|p| p.column_name()).collect();
        let mut unique = names.clone();
        unique.sort_unstable();
        unique.dedup();
        assert_eq!(names.len(), unique.len());
    }
}
