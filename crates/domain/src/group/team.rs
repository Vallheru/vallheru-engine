//! Team (party) domain logic.
//!
//! Ported from PHP `class/team_class.php`, `team.php`, and invitation
//! flows in `view.php` / `log.php`.
//!
//! Teams are small parties (up to 5 members) led by one player.  Members
//! pool stats for combat.  Invitations go through a pending-invite field
//! on the target player and are accepted/rejected via the log page.

// ---------------------------------------------------------------------------
// Constants
// ---------------------------------------------------------------------------

/// Maximum number of slots in a team (including the leader).
pub const MAX_TEAM_SLOTS: usize = 5;

// ---------------------------------------------------------------------------
// Member status (read-model)
// ---------------------------------------------------------------------------

/// Observable status of a team member for display purposes.
///
/// PHP `team.php` computes this from energy, hp, `max_hp`, and location
/// relative to the viewer.
#[derive(Debug, Clone, Copy, PartialEq, Eq)]
pub enum MemberStatus {
    /// HP == 0.
    Dead,
    /// Energy < 1.
    Tired,
    /// 0 < HP < `max_hp`.
    Wounded,
    /// Member is in a different location from the viewer.
    Separated,
    /// Fully healthy and co-located.
    Healthy,
}

/// Determine a team member's observable status.
///
/// `viewer_location` is the location string of the player inspecting the
/// team roster.
pub fn member_status(
    hp: i32,
    max_hp: i32,
    energy: f64,
    member_location: &str,
    viewer_location: &str,
) -> MemberStatus {
    // PHP checks energy first, then hp < max_hp, then hp == 0, then location.
    // But hp == 0 should logically come first since a dead player is also
    // tired.  The PHP ordering has a bug: if hp == 0 AND energy < 1 the
    // player shows as "Tired" instead of "Dead".  We preserve the PHP
    // behavior for parity.
    if energy < 1.0 {
        MemberStatus::Tired
    } else if hp > 0 && hp < max_hp {
        MemberStatus::Wounded
    } else if hp == 0 {
        MemberStatus::Dead
    } else if member_location != viewer_location {
        MemberStatus::Separated
    } else {
        MemberStatus::Healthy
    }
}

// ---------------------------------------------------------------------------
// Team creation
// ---------------------------------------------------------------------------

/// Error when trying to create a team.
#[derive(Debug, Clone, Copy, PartialEq, Eq)]
pub enum CreateTeamError {
    /// The player already belongs to a team.
    AlreadyInTeam,
}

/// Validate whether a player can create a new team.
pub fn can_create_team(player_team_id: i32) -> Result<(), CreateTeamError> {
    if player_team_id != 0 {
        return Err(CreateTeamError::AlreadyInTeam);
    }
    Ok(())
}

// ---------------------------------------------------------------------------
// Invite
// ---------------------------------------------------------------------------

/// Error when trying to invite a player to a team.
#[derive(Debug, Clone, Copy, PartialEq, Eq)]
pub enum InviteError {
    /// The inviter does not have a team.
    NoTeam,
    /// The inviter is not the team leader.
    NotLeader,
    /// The target already belongs to some team.
    TargetAlreadyInTeam,
    /// The target already has a pending invitation.
    TargetAlreadyInvited,
    /// The target has blocked invitations from this player.
    Blocked,
    /// All team slots are occupied.
    TeamFull,
}

/// Inputs for validating a team invitation.
pub struct InviteCheck {
    /// Inviter's current `team_id` (0 = no team).
    pub inviter_team_id: i32,
    /// Inviter's player id.
    pub inviter_id: i32,
    /// Team's leader player id.
    pub team_leader_id: i32,
    /// Number of occupied slots in the team (1–5).
    pub occupied_slots: usize,
    /// Target player's current `team_id` (0 = no team).
    pub target_team_id: i32,
    /// Target player's current tinvite value (0 = no pending invite).
    pub target_invite_id: i32,
    /// Whether the target has the inviter on their ignore list for
    /// team invitations.
    pub target_blocks_inviter: bool,
}

/// Validate whether the invite can proceed.
pub fn can_invite(check: &InviteCheck) -> Result<(), InviteError> {
    if check.inviter_team_id == 0 {
        return Err(InviteError::NoTeam);
    }
    if check.inviter_id != check.team_leader_id {
        return Err(InviteError::NotLeader);
    }
    if check.target_team_id != 0 {
        return Err(InviteError::TargetAlreadyInTeam);
    }
    if check.target_invite_id != 0 {
        return Err(InviteError::TargetAlreadyInvited);
    }
    if check.target_blocks_inviter {
        return Err(InviteError::Blocked);
    }
    if check.occupied_slots >= MAX_TEAM_SLOTS {
        return Err(InviteError::TeamFull);
    }
    Ok(())
}

// ---------------------------------------------------------------------------
// Accept / reject invitation
// ---------------------------------------------------------------------------

/// Error when trying to accept a team invitation.
#[derive(Debug, Clone, Copy, PartialEq, Eq)]
pub enum AcceptInviteError {
    /// Player already belongs to a team.
    AlreadyInTeam,
    /// Player has no pending team invitation.
    NoPendingInvite,
    /// The team has no empty slot (race condition — someone else filled it).
    TeamFull,
}

/// Validate whether a player can accept a pending team invitation.
///
/// `occupied_slots` is the current count of non-zero slots on the team.
pub fn can_accept_invite(
    player_team_id: i32,
    player_invite_id: i32,
    occupied_slots: usize,
) -> Result<(), AcceptInviteError> {
    if player_team_id != 0 {
        return Err(AcceptInviteError::AlreadyInTeam);
    }
    if player_invite_id == 0 {
        return Err(AcceptInviteError::NoPendingInvite);
    }
    if occupied_slots >= MAX_TEAM_SLOTS {
        return Err(AcceptInviteError::TeamFull);
    }
    Ok(())
}

/// Error when trying to reject a team invitation.
#[derive(Debug, Clone, Copy, PartialEq, Eq)]
pub enum RejectInviteError {
    /// Player has no pending team invitation.
    NoPendingInvite,
}

/// Validate whether a player can reject a pending team invitation.
pub fn can_reject_invite(player_invite_id: i32) -> Result<(), RejectInviteError> {
    if player_invite_id == 0 {
        return Err(RejectInviteError::NoPendingInvite);
    }
    Ok(())
}

// ---------------------------------------------------------------------------
// Leave team
// ---------------------------------------------------------------------------

/// Error when trying to leave a team.
#[derive(Debug, Clone, Copy, PartialEq, Eq)]
pub enum LeaveTeamError {
    /// Player is not in any team.
    NotInTeam,
}

/// Validate whether a player can leave their current team.
pub fn can_leave_team(player_team_id: i32) -> Result<(), LeaveTeamError> {
    if player_team_id == 0 {
        return Err(LeaveTeamError::NotInTeam);
    }
    Ok(())
}

/// Outcome of a leader leaving (which disbands the entire team).
#[derive(Debug, Clone, PartialEq, Eq)]
pub struct DisbandOutcome {
    /// Player IDs of all other members who were in the team.
    pub other_member_ids: Vec<i32>,
}

/// When the leader leaves, compute which members need to be notified
/// and have their `team_id` cleared.
///
/// `slot_ids` contains the player IDs occupying each of the 5 team slots
/// (0 means empty).
pub fn disband_members(leader_id: i32, slot_ids: &[i32; MAX_TEAM_SLOTS]) -> DisbandOutcome {
    let other_member_ids = slot_ids
        .iter()
        .copied()
        .filter(|&id| id != 0 && id != leader_id)
        .collect();
    DisbandOutcome { other_member_ids }
}

// ---------------------------------------------------------------------------
// Kick member
// ---------------------------------------------------------------------------

/// Error when trying to kick a team member.
#[derive(Debug, Clone, Copy, PartialEq, Eq)]
pub enum KickError {
    /// The kicker does not have a team.
    NoTeam,
    /// Cannot kick yourself.
    CannotKickSelf,
    /// Target is not in the kicker's team.
    NotInTeam,
    /// Only the leader can kick members.
    NotLeader,
}

/// Validate whether a player can kick another player from their team.
pub fn can_kick(
    kicker_id: i32,
    kicker_team_id: i32,
    team_leader_id: i32,
    target_id: i32,
    target_team_id: i32,
) -> Result<(), KickError> {
    if kicker_team_id == 0 {
        return Err(KickError::NoTeam);
    }
    if target_id == kicker_id {
        return Err(KickError::CannotKickSelf);
    }
    if target_team_id != kicker_team_id {
        return Err(KickError::NotInTeam);
    }
    if kicker_id != team_leader_id {
        return Err(KickError::NotLeader);
    }
    Ok(())
}

/// Find which slot index (0–4) holds the given player ID.
///
/// Returns `None` if the player is not in any slot.
pub fn find_slot(slot_ids: &[i32; MAX_TEAM_SLOTS], player_id: i32) -> Option<usize> {
    slot_ids.iter().position(|&id| id == player_id)
}

/// Count the number of occupied slots.
pub fn occupied_slot_count(slot_ids: &[i32; MAX_TEAM_SLOTS]) -> usize {
    slot_ids.iter().filter(|&&id| id != 0).count()
}

/// Find the first empty slot index (0–4).
pub fn first_empty_slot(slot_ids: &[i32; MAX_TEAM_SLOTS]) -> Option<usize> {
    slot_ids.iter().position(|&id| id == 0)
}

// ---------------------------------------------------------------------------
// Team combat stat aggregation
// ---------------------------------------------------------------------------

/// A lightweight snapshot of a team member's stats for combat pooling.
///
/// PHP `Team::getStats()` adds each co-located member's stats and skills
/// to the leader's totals.  Only members with hp > 0 AND energy > 0 are
/// included.
pub struct CombatMember {
    pub id: i32,
    pub hp: i32,
    pub max_hp: i32,
    pub energy: f64,
    /// Stat `modified` values keyed by `stat_key`.
    pub stats: Vec<(String, i32)>,
    /// Non-battle skill levels keyed by `skill_key`.
    pub support_skills: Vec<(String, i32)>,
    /// The highest level among attack, shoot, and magic skills.
    pub best_battle_skill: i32,
}

/// Skills that are NOT pooled directly (battle skills).
const BATTLE_SKILLS: &[&str] = &["attack", "shoot", "magic"];

/// Result of aggregating team stats for combat.
pub struct TeamCombatBonus {
    /// Stat bonuses to add to the leader's modified stats: `(stat_key, bonus)`.
    pub stat_bonuses: Vec<(String, i32)>,
    /// Support-skill bonuses to add: `(skill_key, bonus)`.
    pub support_skill_bonuses: Vec<(String, i32)>,
    /// Battle-skill bonus (same value added to attack, shoot, and magic).
    pub battle_skill_bonus: i32,
    /// Extra HP added from members.
    pub hp_bonus: i32,
    /// Extra `max_hp` added from members.
    pub max_hp_bonus: i32,
}

/// Aggregate combat bonuses from team members (excluding the leader).
///
/// Only members with `hp > 0` AND `energy > 0` are counted, matching the
/// PHP `Team` class behavior.
pub fn team_combat_bonus(leader_id: i32, members: &[CombatMember]) -> TeamCombatBonus {
    let mut stat_bonuses: Vec<(String, i32)> = Vec::new();
    let mut support_skill_bonuses: Vec<(String, i32)> = Vec::new();
    let mut battle_skill_bonus = 0i32;
    let mut hp_bonus = 0i32;
    let mut max_hp_bonus = 0i32;

    for member in members {
        if member.id == leader_id || member.hp <= 0 || member.energy <= 0.0 {
            continue;
        }

        // Aggregate stats.
        for (key, value) in &member.stats {
            if let Some(existing) = stat_bonuses.iter_mut().find(|(k, _)| k == key) {
                existing.1 += value;
            } else {
                stat_bonuses.push((key.clone(), *value));
            }
        }

        // Aggregate non-battle skills.
        for (key, value) in &member.support_skills {
            if BATTLE_SKILLS.contains(&key.as_str()) {
                continue;
            }
            if let Some(existing) = support_skill_bonuses.iter_mut().find(|(k, _)| k == key) {
                existing.1 += value;
            } else {
                support_skill_bonuses.push((key.clone(), *value));
            }
        }

        // Battle skill: add max(attack, shoot, magic) for each member.
        battle_skill_bonus += member.best_battle_skill;

        hp_bonus += member.hp;
        max_hp_bonus += member.max_hp;
    }

    TeamCombatBonus {
        stat_bonuses,
        support_skill_bonuses,
        battle_skill_bonus,
        hp_bonus,
        max_hp_bonus,
    }
}

/// Divide XP among team members.
///
/// PHP `Team::checkexp()` divides each XP component by team size.
/// Returns the per-member share (rounded up, matching PHP `ceil`).
#[allow(clippy::cast_possible_truncation, clippy::cast_precision_loss)]
pub fn team_xp_share(xp: i64, team_size: usize) -> i64 {
    if team_size <= 1 {
        return xp;
    }
    (xp as f64 / team_size as f64).ceil() as i64
}

// ---------------------------------------------------------------------------
// Tests
// ---------------------------------------------------------------------------

#[cfg(test)]
mod tests {
    use super::*;

    // --- member_status ---

    #[test]
    fn status_tired_takes_priority_over_dead() {
        // PHP quirk: energy < 1 checked before hp == 0
        assert_eq!(
            member_status(0, 100, 0.0, "Altara", "Altara"),
            MemberStatus::Tired
        );
    }

    #[test]
    fn status_dead() {
        assert_eq!(
            member_status(0, 100, 5.0, "Altara", "Altara"),
            MemberStatus::Dead
        );
    }

    #[test]
    fn status_wounded() {
        assert_eq!(
            member_status(50, 100, 5.0, "Altara", "Altara"),
            MemberStatus::Wounded
        );
    }

    #[test]
    fn status_separated() {
        assert_eq!(
            member_status(100, 100, 5.0, "Las", "Altara"),
            MemberStatus::Separated
        );
    }

    #[test]
    fn status_healthy() {
        assert_eq!(
            member_status(100, 100, 5.0, "Altara", "Altara"),
            MemberStatus::Healthy
        );
    }

    // --- create team ---

    #[test]
    fn create_team_ok() {
        assert!(can_create_team(0).is_ok());
    }

    #[test]
    fn create_team_already_in() {
        assert_eq!(can_create_team(42), Err(CreateTeamError::AlreadyInTeam));
    }

    // --- invite ---

    fn base_invite() -> InviteCheck {
        InviteCheck {
            inviter_team_id: 1,
            inviter_id: 10,
            team_leader_id: 10,
            occupied_slots: 2,
            target_team_id: 0,
            target_invite_id: 0,
            target_blocks_inviter: false,
        }
    }

    #[test]
    fn invite_ok() {
        assert!(can_invite(&base_invite()).is_ok());
    }

    #[test]
    fn invite_no_team() {
        let mut c = base_invite();
        c.inviter_team_id = 0;
        assert_eq!(can_invite(&c), Err(InviteError::NoTeam));
    }

    #[test]
    fn invite_not_leader() {
        let mut c = base_invite();
        c.inviter_id = 99;
        assert_eq!(can_invite(&c), Err(InviteError::NotLeader));
    }

    #[test]
    fn invite_target_in_team() {
        let mut c = base_invite();
        c.target_team_id = 5;
        assert_eq!(can_invite(&c), Err(InviteError::TargetAlreadyInTeam));
    }

    #[test]
    fn invite_target_already_invited() {
        let mut c = base_invite();
        c.target_invite_id = 3;
        assert_eq!(can_invite(&c), Err(InviteError::TargetAlreadyInvited));
    }

    #[test]
    fn invite_blocked() {
        let mut c = base_invite();
        c.target_blocks_inviter = true;
        assert_eq!(can_invite(&c), Err(InviteError::Blocked));
    }

    #[test]
    fn invite_team_full() {
        let mut c = base_invite();
        c.occupied_slots = 5;
        assert_eq!(can_invite(&c), Err(InviteError::TeamFull));
    }

    // --- accept invite ---

    #[test]
    fn accept_ok() {
        assert!(can_accept_invite(0, 3, 3).is_ok());
    }

    #[test]
    fn accept_already_in_team() {
        assert_eq!(
            can_accept_invite(1, 3, 3),
            Err(AcceptInviteError::AlreadyInTeam)
        );
    }

    #[test]
    fn accept_no_invite() {
        assert_eq!(
            can_accept_invite(0, 0, 3),
            Err(AcceptInviteError::NoPendingInvite)
        );
    }

    #[test]
    fn accept_team_full() {
        assert_eq!(can_accept_invite(0, 3, 5), Err(AcceptInviteError::TeamFull));
    }

    // --- reject invite ---

    #[test]
    fn reject_ok() {
        assert!(can_reject_invite(3).is_ok());
    }

    #[test]
    fn reject_no_invite() {
        assert_eq!(
            can_reject_invite(0),
            Err(RejectInviteError::NoPendingInvite)
        );
    }

    // --- leave team ---

    #[test]
    fn leave_ok() {
        assert!(can_leave_team(1).is_ok());
    }

    #[test]
    fn leave_not_in_team() {
        assert_eq!(can_leave_team(0), Err(LeaveTeamError::NotInTeam));
    }

    // --- disband ---

    #[test]
    fn disband_collects_other_members() {
        let slots = [10, 20, 30, 0, 0];
        let outcome = disband_members(10, &slots);
        assert_eq!(outcome.other_member_ids, vec![20, 30]);
    }

    #[test]
    fn disband_solo_team() {
        let slots = [10, 0, 0, 0, 0];
        let outcome = disband_members(10, &slots);
        assert!(outcome.other_member_ids.is_empty());
    }

    // --- kick ---

    #[test]
    fn kick_ok() {
        assert!(can_kick(10, 1, 10, 20, 1).is_ok());
    }

    #[test]
    fn kick_no_team() {
        assert_eq!(can_kick(10, 0, 10, 20, 1), Err(KickError::NoTeam));
    }

    #[test]
    fn kick_self() {
        assert_eq!(can_kick(10, 1, 10, 10, 1), Err(KickError::CannotKickSelf));
    }

    #[test]
    fn kick_not_in_team() {
        assert_eq!(can_kick(10, 1, 10, 20, 2), Err(KickError::NotInTeam));
    }

    #[test]
    fn kick_not_leader() {
        assert_eq!(can_kick(10, 1, 99, 20, 1), Err(KickError::NotLeader));
    }

    // --- slot helpers ---

    #[test]
    fn find_slot_present() {
        let slots = [10, 20, 30, 0, 0];
        assert_eq!(find_slot(&slots, 20), Some(1));
    }

    #[test]
    fn find_slot_absent() {
        let slots = [10, 20, 30, 0, 0];
        assert_eq!(find_slot(&slots, 99), None);
    }

    #[test]
    fn occupied_count() {
        let slots = [10, 20, 0, 0, 0];
        assert_eq!(occupied_slot_count(&slots), 2);
    }

    #[test]
    fn first_empty() {
        let slots = [10, 20, 0, 0, 0];
        assert_eq!(first_empty_slot(&slots), Some(2));
    }

    #[test]
    fn first_empty_full() {
        let slots = [1, 2, 3, 4, 5];
        assert_eq!(first_empty_slot(&slots), None);
    }

    // --- team combat bonus ---

    #[test]
    fn combat_bonus_skips_leader() {
        let members = vec![CombatMember {
            id: 10,
            hp: 100,
            max_hp: 100,
            energy: 5.0,
            stats: vec![("strength".to_owned(), 50)],
            support_skills: vec![("dodge".to_owned(), 10)],
            best_battle_skill: 20,
        }];
        let bonus = team_combat_bonus(10, &members);
        assert!(bonus.stat_bonuses.is_empty());
        assert_eq!(bonus.battle_skill_bonus, 0);
        assert_eq!(bonus.hp_bonus, 0);
    }

    #[test]
    fn combat_bonus_skips_dead() {
        let members = vec![CombatMember {
            id: 20,
            hp: 0,
            max_hp: 100,
            energy: 5.0,
            stats: vec![("strength".to_owned(), 50)],
            support_skills: vec![],
            best_battle_skill: 10,
        }];
        let bonus = team_combat_bonus(10, &members);
        assert!(bonus.stat_bonuses.is_empty());
    }

    #[test]
    fn combat_bonus_skips_exhausted() {
        let members = vec![CombatMember {
            id: 20,
            hp: 100,
            max_hp: 100,
            energy: 0.0,
            stats: vec![("strength".to_owned(), 50)],
            support_skills: vec![],
            best_battle_skill: 10,
        }];
        let bonus = team_combat_bonus(10, &members);
        assert!(bonus.stat_bonuses.is_empty());
    }

    #[test]
    fn combat_bonus_aggregates_two_members() {
        let members = vec![
            CombatMember {
                id: 20,
                hp: 80,
                max_hp: 100,
                energy: 5.0,
                stats: vec![("strength".to_owned(), 30), ("agility".to_owned(), 20)],
                support_skills: vec![("dodge".to_owned(), 10)],
                best_battle_skill: 15,
            },
            CombatMember {
                id: 30,
                hp: 90,
                max_hp: 100,
                energy: 3.0,
                stats: vec![("strength".to_owned(), 25), ("speed".to_owned(), 18)],
                support_skills: vec![
                    ("dodge".to_owned(), 8),
                    ("attack".to_owned(), 12), // battle skill — ignored
                ],
                best_battle_skill: 20,
            },
        ];
        let bonus = team_combat_bonus(10, &members);

        // Strength: 30 + 25 = 55
        let str_bonus = bonus.stat_bonuses.iter().find(|(k, _)| k == "strength");
        assert_eq!(str_bonus.unwrap().1, 55);

        // Agility: 20
        let agi_bonus = bonus.stat_bonuses.iter().find(|(k, _)| k == "agility");
        assert_eq!(agi_bonus.unwrap().1, 20);

        // Speed: 18
        let spd_bonus = bonus.stat_bonuses.iter().find(|(k, _)| k == "speed");
        assert_eq!(spd_bonus.unwrap().1, 18);

        // Dodge: 10 + 8 = 18
        let dodge_bonus = bonus
            .support_skill_bonuses
            .iter()
            .find(|(k, _)| k == "dodge");
        assert_eq!(dodge_bonus.unwrap().1, 18);

        // "attack" from member 30 should NOT appear (it's a battle skill)
        let attack_bonus = bonus
            .support_skill_bonuses
            .iter()
            .find(|(k, _)| k == "attack");
        assert!(attack_bonus.is_none());

        // Battle skill: 15 + 20 = 35
        assert_eq!(bonus.battle_skill_bonus, 35);

        // HP: 80 + 90 = 170
        assert_eq!(bonus.hp_bonus, 170);

        // Max HP: 100 + 100 = 200
        assert_eq!(bonus.max_hp_bonus, 200);
    }

    // --- XP share ---

    #[test]
    fn xp_share_solo() {
        assert_eq!(team_xp_share(100, 1), 100);
    }

    #[test]
    fn xp_share_two_members() {
        assert_eq!(team_xp_share(100, 2), 50);
    }

    #[test]
    fn xp_share_rounds_up() {
        // 100 / 3 = 33.33... → ceil → 34
        assert_eq!(team_xp_share(100, 3), 34);
    }

    #[test]
    fn xp_share_exact_division() {
        assert_eq!(team_xp_share(90, 3), 30);
    }
}
