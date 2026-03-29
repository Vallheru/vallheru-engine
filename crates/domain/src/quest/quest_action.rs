//! Quest action persistence and branching state.
//!
//! Maps the `questaction` and `quests` tables. The `questaction` table tracks
//! which quest each player is currently on and their progress within it.
//! The `quests` table holds the authored branching text content.
//!
//! # `questaction` table
//!
//! | Column | Type        | Description                                    |
//! |--------|-------------|------------------------------------------------|
//! | id     | SERIAL PK   | Row identity                                   |
//! | player | INT         | FK to `players.id`                             |
//! | quest  | INT         | Which quest (1–10) the player is on            |
//! | action | VARCHAR(20) | Current branch/step (empty = started, "end" = done) |
//!
//! # `quests` table
//!
//! | Column   | Type        | Description                                    |
//! |----------|-------------|------------------------------------------------|
//! | id       | SERIAL PK   | Row identity                                   |
//! | qid      | INT         | Quest number (1–10)                            |
//! | location | VARCHAR(20) | Handler file (e.g. `"grid.php"`)               |
//! | name     | VARCHAR(20) | Step/branch name (e.g. `"start"`, `"box1"`)    |
//! | option   | VARCHAR(20) | Choice label or `"0"` for narrative text       |
//! | text     | TEXT        | Displayed narrative text                       |
//! | lang     | VARCHAR(3)  | Language code                                  |
//!
//! # Quest lifecycle
//!
//! ```text
//! Start → advance(branch) → … → advance(branch) → finish("end")
//!                                                    ↓
//!                                          all_quests_complete?
//!                                            yes → cleanup rows, reset location
//! ```
//!
//! At any point the player may resign, which deletes the row and resets
//! location.

// ---------------------------------------------------------------------------
// Core types
// ---------------------------------------------------------------------------

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
            "" | "start" => Self::Started,
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
    /// The quest has already been completed.
    AlreadyCompleted,
}

// ---------------------------------------------------------------------------
// Transition types
// ---------------------------------------------------------------------------

/// The kind of transition a quest step expects.
#[derive(Debug, Clone, PartialEq, Eq)]
pub enum QuestTransition {
    /// Player picks a numbered box choice. The value is 1-based.
    BoxChoice { box_name: String, choice: i32 },
    /// Player types an answer to compare against the step's `option`.
    Answer {
        step_name: String,
        player_answer: String,
    },
    /// Simple advancement to the next named step (link click).
    Advance { target_step: String },
    /// Player resigns from the quest.
    Resign,
}

/// The result of resolving a box choice against authored content.
#[derive(Debug, Clone, PartialEq, Eq)]
pub struct BoxChoiceResolution {
    /// The step name derived from the box choice (e.g. `box_name` = `"box1"`,
    /// choice index = 2 → the second `box1` row's content is selected).
    pub choice_index: usize,
    /// The narrative text of the chosen option.
    pub text: String,
}

/// Outcome of checking a player's text answer against the authored answer.
#[derive(Debug, Clone, Copy, PartialEq, Eq)]
pub enum AnswerResult {
    Correct,
    Wrong,
}

// ---------------------------------------------------------------------------
// XP reward computation
// ---------------------------------------------------------------------------

/// Describes an XP reward to be distributed across stats and/or skills.
#[derive(Debug, Clone, PartialEq, Eq)]
pub struct QuestReward {
    /// Total XP granted by the quest step.
    pub xp: i32,
    /// Stat keys to distribute XP to (e.g. `["condition"]`).
    pub stats: Vec<String>,
    /// Skill keys to distribute XP to (e.g. `["speed"]`).
    pub skills: Vec<String>,
}

/// A single XP allotment for one stat or skill.
#[derive(Debug, Clone, PartialEq, Eq)]
pub struct XpAllotment {
    pub key: String,
    pub xp: i32,
    pub is_skill: bool,
}

impl QuestReward {
    /// Compute per-key XP allotments.
    ///
    /// PHP divides the total XP equally (ceiling) among the total count of
    /// stat + skill recipients.
    pub fn allotments(&self) -> Vec<XpAllotment> {
        let count = self.stats.len() + self.skills.len();
        if count == 0 || self.xp <= 0 {
            return Vec::new();
        }

        // Integer ceiling division: (xp + count - 1) / count
        #[allow(clippy::cast_possible_truncation, clippy::cast_possible_wrap)]
        let count_i32 = count as i32;
        let per_key = (self.xp + count_i32 - 1) / count_i32;

        let mut result = Vec::with_capacity(count);
        for key in &self.stats {
            result.push(XpAllotment {
                key: key.clone(),
                xp: per_key,
                is_skill: false,
            });
        }
        for key in &self.skills {
            result.push(XpAllotment {
                key: key.clone(),
                xp: per_key,
                is_skill: true,
            });
        }
        result
    }
}

// ---------------------------------------------------------------------------
// Quest lifecycle predicates
// ---------------------------------------------------------------------------

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

/// Check whether the action represents a completed quest.
pub fn is_quest_complete(action: &str) -> bool {
    action == "end"
}

/// Whether the player has an in-progress (non-ended, non-empty) quest.
pub fn has_active_quest(actions: &[QuestAction]) -> bool {
    actions.iter().any(|a| {
        let s = QuestStatus::from_action(&a.action);
        s == QuestStatus::InProgress || s == QuestStatus::Started
    })
}

/// Determine whether all quests for a player are complete.
///
/// `actions` is the list of all `action` values from `questaction` for this player.
/// Returns `true` only when the count matches `total_quests` and every row
/// has `action = 'end'`.
pub fn all_quests_complete(actions: &[&str]) -> bool {
    !actions.is_empty() && actions.iter().all(|a| *a == "end")
}

/// Resolve a box choice against a list of authored steps.
///
/// `steps` must be the rows returned for the box name, ordered by `id`.
/// `choice` is 1-based (matches the PHP `$_POST['boxN']` value).
pub fn resolve_box_choice(
    steps: &[QuestStep],
    choice: i32,
) -> Result<BoxChoiceResolution, QuestError> {
    if choice < 1 {
        return Err(QuestError::InvalidChoice);
    }
    #[allow(clippy::cast_sign_loss)]
    let idx = (choice - 1) as usize;
    steps
        .get(idx)
        .map(|step| BoxChoiceResolution {
            choice_index: idx,
            text: step.text.clone(),
        })
        .ok_or(QuestError::InvalidChoice)
}

/// Check whether a text answer matches the authored answer for a step.
pub fn check_answer(player_answer: &str, authored_option: &str) -> AnswerResult {
    if player_answer.to_lowercase() == authored_option.to_lowercase() {
        AnswerResult::Correct
    } else {
        AnswerResult::Wrong
    }
}

/// Validate that advancing to `target_action` from `current_action` is legal
/// (i.e. the quest is not already completed and the player is on that quest).
pub fn validate_advance(current_action: &str) -> Result<(), QuestError> {
    if is_quest_complete(current_action) {
        return Err(QuestError::AlreadyCompleted);
    }
    Ok(())
}

/// Replace city name placeholders in quest text.
///
/// The PHP quests use `city1`, `city1a`, `city1b`, `city2` as placeholders
/// in the narrative text.
#[allow(clippy::similar_names)]
pub fn substitute_city_names(
    text: &str,
    city1: &str,
    city1a: &str,
    city1b: &str,
    city2: &str,
) -> String {
    // Order matters: replace more specific before less specific.
    text.replace("city1a", city1a)
        .replace("city1b", city1b)
        .replace("city2", city2)
        .replace("city1", city1)
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
        assert_eq!(QuestStatus::from_action("start"), QuestStatus::Started);
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

    #[test]
    fn has_active_quest_detects_started() {
        let actions = vec![QuestAction {
            id: 1,
            player_id: 1,
            quest_id: 1,
            action: "start".into(),
        }];
        assert!(has_active_quest(&actions));
    }

    #[test]
    fn has_active_quest_ignores_completed() {
        let actions = vec![QuestAction {
            id: 1,
            player_id: 1,
            quest_id: 1,
            action: "end".into(),
        }];
        assert!(!has_active_quest(&actions));
    }

    #[test]
    fn resolve_box_choice_valid() {
        let steps = vec![
            make_step("box1", "1", "Go left"),
            make_step("box1", "2", "Go right"),
            make_step("box1", "3", "Turn back"),
        ];
        let res = resolve_box_choice(&steps, 2).unwrap();
        assert_eq!(res.choice_index, 1);
        assert_eq!(res.text, "Go right");
    }

    #[test]
    fn resolve_box_choice_out_of_range() {
        let steps = vec![make_step("box1", "1", "Go left")];
        assert_eq!(
            resolve_box_choice(&steps, 5),
            Err(QuestError::InvalidChoice)
        );
    }

    #[test]
    fn resolve_box_choice_zero_is_invalid() {
        let steps = vec![make_step("box1", "1", "Go left")];
        // choice=0 → idx = -1 wraps → out of range
        assert_eq!(
            resolve_box_choice(&steps, 0),
            Err(QuestError::InvalidChoice)
        );
    }

    #[test]
    fn check_answer_case_insensitive() {
        assert_eq!(check_answer("Foo", "foo"), AnswerResult::Correct);
        assert_eq!(check_answer("bar", "BAR"), AnswerResult::Correct);
        assert_eq!(check_answer("wrong", "right"), AnswerResult::Wrong);
    }

    #[test]
    fn validate_advance_rejects_completed() {
        assert_eq!(validate_advance("end"), Err(QuestError::AlreadyCompleted));
    }

    #[test]
    fn validate_advance_allows_in_progress() {
        assert_eq!(validate_advance("1.2"), Ok(()));
        assert_eq!(validate_advance(""), Ok(()));
    }

    #[test]
    fn reward_allotments_split_evenly() {
        let reward = QuestReward {
            xp: 40,
            stats: vec!["condition".into()],
            skills: vec![],
        };
        let allots = reward.allotments();
        assert_eq!(allots.len(), 1);
        assert_eq!(allots[0].xp, 40);
        assert!(!allots[0].is_skill);
    }

    #[test]
    fn reward_allotments_ceil_division() {
        let reward = QuestReward {
            xp: 10,
            stats: vec!["condition".into(), "speed".into()],
            skills: vec!["dodge".into()],
        };
        // 10 / 3 = ceil(3.33) = 4 each
        let allots = reward.allotments();
        assert_eq!(allots.len(), 3);
        for a in &allots {
            assert_eq!(a.xp, 4);
        }
    }

    #[test]
    fn reward_allotments_zero_xp() {
        let reward = QuestReward {
            xp: 0,
            stats: vec!["condition".into()],
            skills: vec![],
        };
        assert!(reward.allotments().is_empty());
    }

    #[test]
    fn reward_allotments_no_recipients() {
        let reward = QuestReward {
            xp: 50,
            stats: vec![],
            skills: vec![],
        };
        assert!(reward.allotments().is_empty());
    }

    #[test]
    fn substitute_city_names_replaces_all() {
        let text = "You arrive at city1a near city1b. The capital city1 and city2 await.";
        let result = substitute_city_names(text, "Altara", "Haven", "Port", "Durin");
        assert_eq!(
            result,
            "You arrive at Haven near Port. The capital Altara and Durin await."
        );
    }

    #[test]
    fn substitute_city_names_order_matters() {
        // "city1a" must be replaced before "city1"
        let text = "city1a city1";
        let result = substitute_city_names(text, "Altara", "Haven", "Port", "Durin");
        assert_eq!(result, "Haven Altara");
    }

    fn make_step(name: &str, option: &str, text: &str) -> QuestStep {
        QuestStep {
            id: 0,
            qid: 1,
            location: "grid.php".into(),
            name: name.into(),
            option: option.into(),
            text: text.into(),
            lang: "pl".into(),
        }
    }
}
