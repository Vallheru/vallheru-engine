//! Quest action persistence and branching state.
//!
//! Maps the `questaction` and `quests` tables. The `questaction` table tracks
//! which quest each player is currently on and their progress within it.
//! The `quests` table holds the authored branching text content.
//!
//! # `questaction` table
//!
//! ```text
//! id      INT AUTO_INCREMENT
//! player  INT          — FK to players.id
//! quest   INT          — which quest (1-10) the player is on
//! action  VARCHAR(20)  — current branch/step name (e.g. "1", "1.2", "end")
//! ```
//!
//! # `quests` table
//!
//! ```text
//! id       INT AUTO_INCREMENT
//! qid      INT          — quest number (1-10)
//! location VARCHAR(20)  — PHP file that handles this quest step (e.g. "grid.php")
//! name     VARCHAR(20)  — step/branch name (e.g. "start", "box1", "1", "1.2")
//! option   VARCHAR(20)  — choice label or "0" for narrative text
//! text     TEXT         — the displayed text content
//! lang     VARCHAR(3)   — language code
//! ```
//!
//! # Key behaviors from PHP
//!
//! - Starting a quest: inserts a `questaction` row with `action = ''`,
//!   sets player location to `Podróż` (Travelling).
//! - Advancing: updates `questaction.action` to the branch name.
//! - Finishing: sets `action = 'end'`, awards XP, and when all quests are
//!   done (`action = 'end'` for every completed quest), deletes all
//!   `questaction` rows and resets location to `Altara`.
//! - Resigning: deletes the `questaction` row and resets location.
//! - Fighting: if a quest step has a `fight` field, it triggers a
//!   `turnfight` with the referenced monster.
//! - Answering: text-matching against the `option` field of the quest step.
//! - Box selection: choosing between numbered options that branch the quest.

/// A player's active quest progress as stored in the `questaction` table.
#[derive(Debug, Clone, PartialEq, Eq)]
pub struct QuestAction {
    pub id: i32,
    pub player_id: i32,
    /// Which quest number (1–10).
    pub quest_id: i32,
    /// Current branch/step name. Empty string means just started.
    /// `"end"` means completed.
    pub action: String,
}

/// A single authored step/choice in a quest, from the `quests` table.
#[derive(Debug, Clone, PartialEq, Eq)]
pub struct QuestStep {
    pub id: i32,
    /// Quest number (1–10).
    pub qid: i32,
    /// PHP handler file (e.g. `"grid.php"`).
    pub location: String,
    /// Step/branch name within the quest.
    pub name: String,
    /// Choice label or `"0"` for narrative-only text.
    pub option: String,
    /// Displayed narrative text.
    pub text: String,
    /// Language code.
    pub lang: String,
}

/// The status of a quest action.
#[derive(Debug, Clone, Copy, PartialEq, Eq)]
pub enum QuestStatus {
    /// Quest was just started, no branch chosen yet.
    Started,
    /// Player is in the middle of a quest branch.
    InProgress,
    /// Quest completed successfully.
    Completed,
}

impl QuestStatus {
    pub fn from_action(action: &str) -> Self {
        match action {
            "" => Self::Started,
            "end" => Self::Completed,
            _ => Self::InProgress,
        }
    }
}

/// Errors that can occur during quest operations.
#[derive(Debug, Clone, PartialEq, Eq)]
pub enum QuestError {
    /// Player is not in a city (required to start a quest).
    NotInCity,
    /// Player is dead.
    Dead,
    /// Player already has an active (non-ended) quest.
    AlreadyOnQuest,
    /// The requested quest does not exist.
    QuestNotFound,
    /// The requested branch/choice is invalid for the current step.
    InvalidChoice,
    /// No quest action found (player isn't on a quest).
    NotOnQuest,
}

/// Outcome of finishing all quests (all `questaction` rows have `action = 'end'`).
#[derive(Debug, Clone, PartialEq, Eq)]
pub struct AllQuestsComplete {
    /// Total XP to distribute across stats/skills.
    pub total_xp: i32,
}

/// Check whether a player can start a quest.
pub fn can_start_quest(
    is_in_city: bool,
    hp: i32,
    has_active_quest: bool,
) -> Result<(), QuestError> {
    if hp <= 0 {
        return Err(QuestError::Dead);
    }
    if !is_in_city {
        return Err(QuestError::NotInCity);
    }
    if has_active_quest {
        return Err(QuestError::AlreadyOnQuest);
    }
    Ok(())
}

/// Check whether a quest action represents a completed quest.
pub fn is_quest_complete(action: &str) -> bool {
    action == "end"
}

/// Determine whether all quests for a player are complete.
///
/// `actions` is the list of all `action` values from `questaction` for this player.
/// Returns `true` only when every row has `action = 'end'`.
pub fn all_quests_complete(actions: &[&str]) -> bool {
    !actions.is_empty() && actions.iter().all(|a| *a == "end")
}

// ---------------------------------------------------------------------------
// Tests
// ---------------------------------------------------------------------------

#[cfg(test)]
mod tests {
    use super::*;

    #[test]
    fn quest_status_from_action() {
        assert_eq!(QuestStatus::from_action(""), QuestStatus::Started);
        assert_eq!(QuestStatus::from_action("1"), QuestStatus::InProgress);
        assert_eq!(QuestStatus::from_action("1.2"), QuestStatus::InProgress);
        assert_eq!(QuestStatus::from_action("end"), QuestStatus::Completed);
    }

    #[test]
    fn can_start_quest_requires_city() {
        assert_eq!(
            can_start_quest(false, 100, false),
            Err(QuestError::NotInCity)
        );
    }

    #[test]
    fn can_start_quest_requires_alive() {
        assert_eq!(can_start_quest(true, 0, false), Err(QuestError::Dead));
    }

    #[test]
    fn can_start_quest_rejects_active() {
        assert_eq!(
            can_start_quest(true, 100, true),
            Err(QuestError::AlreadyOnQuest)
        );
    }

    #[test]
    fn can_start_quest_ok() {
        assert_eq!(can_start_quest(true, 100, false), Ok(()));
    }

    #[test]
    fn is_quest_complete_checks_end() {
        assert!(is_quest_complete("end"));
        assert!(!is_quest_complete(""));
        assert!(!is_quest_complete("1.2"));
    }

    #[test]
    fn all_quests_complete_requires_all_end() {
        assert!(all_quests_complete(&["end", "end", "end"]));
        assert!(!all_quests_complete(&["end", "1", "end"]));
        assert!(!all_quests_complete(&[]));
    }
}
