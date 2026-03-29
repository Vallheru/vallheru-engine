//! Parity fixture tests for quests, missions, and labyrinth.
//!
//! These tests capture representative game states and transitions from the
//! PHP version (grid.php, chronicle.php, mission.php, quest1.php etc.) and
//! verify the Rust domain layer produces identical outcomes.
//!
//! Each section documents the original PHP flow being tested.

#[cfg(test)]
mod tests {
    // =====================================================================
    // 1. Quest action lifecycle — quest1.php parity
    //
    // Quest 1 is located in grid.php (labyrinth). The quest has the
    // following state machine:
    //
    //   start → box1 → choice 1 → action "1" → box2 → choice → "1.1" (finish)
    //                                                           "1.2" (fight)
    //                 → choice 2 → action "2" → speed check → box3 → …
    //                 → choice 3 → action "3" (resign)
    //
    // We test the key paths without DB by exercising domain functions.
    // =====================================================================

    use crate::quest::quest_action::*;

    // -- Helper: simulate a quest action entry --

    fn action(id: i32, quest_id: i32, action: &str) -> QuestAction {
        QuestAction {
            id,
            player_id: 1,
            quest_id,
            action: action.into(),
        }
    }

    fn step(name: &str, option: &str, text: &str) -> QuestStep {
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

    // -- Quest 1 path: start → box1 choice 1 → "1" → box2 choice 1 → "1.1" finish --

    #[test]
    fn quest1_start_to_first_branch() {
        // Player is in city, alive, no active quest → can start
        assert_eq!(can_start_quest(true, 100, false), Ok(()));

        // After starting, action is "" or "start" → quest is active
        let actions = vec![action(1, 1, "start")];
        assert!(has_active_quest(&actions));
        assert_eq!(QuestStatus::from_action("start"), QuestStatus::Started);
    }

    #[test]
    fn quest1_box1_three_choices() {
        // box1 presents 3 choices: go left (1), dodge (2), turn back (3)
        let steps = vec![
            step("box1", "1", "Postanawiasz iść prosto."),
            step("box1", "2", "Próbujesz się wymknąć."),
            step("box1", "3", "Wracasz."),
        ];

        // Choice 1 → valid
        let res = resolve_box_choice(&steps, 1).unwrap();
        assert_eq!(res.choice_index, 0);
        assert_eq!(res.text, "Postanawiasz iść prosto.");

        // Choice 2 → valid
        let res = resolve_box_choice(&steps, 2).unwrap();
        assert_eq!(res.choice_index, 1);

        // Choice 3 → resign path
        let res = resolve_box_choice(&steps, 3).unwrap();
        assert_eq!(res.choice_index, 2);

        // Choice 4 → out of range
        assert_eq!(
            resolve_box_choice(&steps, 4),
            Err(QuestError::InvalidChoice)
        );
    }

    #[test]
    fn quest1_resign_path() {
        // If player picks choice 3 from box1, action becomes "3" then quest
        // finishes with Finish(20, ['condition']).
        // After finish, action = "end".
        assert!(is_quest_complete("end"));
        assert!(!is_quest_complete("3"));
    }

    #[test]
    fn quest1_path_1_to_finish() {
        // Choice 1 at box1 → action "1" → box2 presented.
        // Choice 1 at box2 → action "1.1" → Finish(20, ['condition']).
        assert_eq!(QuestStatus::from_action("1"), QuestStatus::InProgress);
        assert_eq!(QuestStatus::from_action("1.1"), QuestStatus::InProgress);

        // The reward for this path: xp=20, stats=['condition']
        let reward = QuestReward {
            xp: 20,
            stats: vec!["condition".into()],
            skills: vec![],
        };
        let allots = reward.allotments();
        assert_eq!(allots.len(), 1);
        assert_eq!(allots[0].key, "condition");
        assert_eq!(allots[0].xp, 20);
        assert!(!allots[0].is_skill);
    }

    #[test]
    fn quest1_path_1_fight_then_win() {
        // Choice 2 at box2 → action "1.2" → fight.
        // After winning fight → action "winfight1" → intelligence check →
        // action "int1" or "int2" → action "end1" → Finish(40, ['condition']).
        assert_eq!(validate_advance("1.2"), Ok(()));

        // Finish with 40 xp to condition
        let reward = QuestReward {
            xp: 40,
            stats: vec!["condition".into()],
            skills: vec![],
        };
        let allots = reward.allotments();
        assert_eq!(allots.len(), 1);
        assert_eq!(allots[0].xp, 40);
    }

    #[test]
    fn quest1_path_2_speed_check_then_answer() {
        // Choice 2 at box1 → action "2" → speed check.
        // Then → box3. Choice 2 at box3 → action "2.2" → answer puzzle.
        // Correct answer → "answer1" → Gainexp(30, ['inteli']).
        assert_eq!(validate_advance("2"), Ok(()));

        // Answer check is case-insensitive
        assert_eq!(check_answer("correct", "CORRECT"), AnswerResult::Correct);
        assert_eq!(check_answer("wrong", "correct"), AnswerResult::Wrong);

        // Reward for correct answer path
        let reward = QuestReward {
            xp: 30,
            stats: vec!["inteli".into()],
            skills: vec![],
        };
        let allots = reward.allotments();
        assert_eq!(allots.len(), 1);
        assert_eq!(allots[0].key, "inteli");
        assert_eq!(allots[0].xp, 30);
    }

    #[test]
    fn quest1_path_final_door_puzzle() {
        // After reaching "door" state → box4 with 2 choices.
        // door1 → answer puzzle with limited attempts (temp=5).
        // door2 → Finish(30, ['condition']).
        // Correct answer at door1 → grants map + Finish(50, ['inteli']).
        let reward_door2 = QuestReward {
            xp: 30,
            stats: vec!["condition".into()],
            skills: vec![],
        };
        assert_eq!(reward_door2.allotments()[0].xp, 30);

        let reward_door1_correct = QuestReward {
            xp: 50,
            stats: vec!["inteli".into()],
            skills: vec![],
        };
        assert_eq!(reward_door1_correct.allotments()[0].xp, 50);
    }

    #[test]
    fn quest_all_complete_after_finishing_all() {
        // If a player has quests 1, 2, 3 all completed:
        assert!(all_quests_complete(&["end", "end", "end"]));

        // But if quest 2 is still in progress:
        assert!(!all_quests_complete(&["end", "1.2", "end"]));

        // Empty (no quests started) is not complete:
        assert!(!all_quests_complete(&[]));
    }

    #[test]
    fn quest_cannot_start_while_active() {
        // PHP prevents starting a new quest while one is active.
        assert_eq!(
            can_start_quest(true, 100, true),
            Err(QuestError::AlreadyOnQuest)
        );
    }

    #[test]
    fn quest_cannot_advance_completed() {
        // Once a quest is "end", no further advances are possible.
        assert_eq!(validate_advance("end"), Err(QuestError::AlreadyCompleted));
    }

    #[test]
    fn quest_reward_splits_across_multiple_recipients() {
        // Some quests award xp to multiple stats/skills.
        // e.g. xp=60 to ['condition', 'speed', 'dodge']
        // → 60/3 = 20 each
        let reward = QuestReward {
            xp: 60,
            stats: vec!["condition".into(), "speed".into()],
            skills: vec!["dodge".into()],
        };
        let allots = reward.allotments();
        assert_eq!(allots.len(), 3);
        for a in &allots {
            assert_eq!(a.xp, 20); // 60 / 3 = 20
        }
        assert!(!allots[0].is_skill);
        assert!(!allots[1].is_skill);
        assert!(allots[2].is_skill);
    }

    #[test]
    fn quest_city_name_substitution_in_text() {
        // Quest texts use city1, city1a, city1b, city2 as placeholders.
        // PHP: str_replace(array('city1a','city1b','city1','city2'),
        //                  array($city1a,$city1b,$city1,$city2), $text)
        let text = "Wyruszasz z city1 do city2. Musisz przejść przez city1a i city1b.";
        let result = substitute_city_names(text, "Altara", "Haven", "Port", "Ardulith");
        assert_eq!(
            result,
            "Wyruszasz z Altara do Ardulith. Musisz przejść przez Haven i Port."
        );
    }

    // =====================================================================
    // 2. Mission traversal — chronicle.php + mission.php parity
    //
    // A typical story mission (type=E) in the chronicle:
    //   1. Player views chronicle list, picks a mission
    //   2. Checks: alive, energy >= 2, in correct city, not on another mission
    //   3. Mission starts at the start room (rooms_remaining=10)
    //   4. Player navigates rooms via exits/mob/item actions
    //   5. Mission ends when rooms_remaining=0 or terminal room reached
    //   6. Rewards are calculated based on successes, bonus, quest target
    // =====================================================================

    use crate::quest::mission::*;
    use crate::quest::mission_loader::*;

    #[test]
    fn mission_start_check_all_conditions() {
        // A typical Story mission — player has enough chapter, right location, etc.
        let check = StartMissionCheck {
            player_chapter: 3,
            mission_chapter: 2,
            mission_type: MissionType::Story,
            player_location: "Altara",
            mission_location: "Altara",
            player_hp: 200,
            player_energy: 5.0,
            craft_missions_remaining: 5,
            has_active_mission: false,
        };
        assert!(can_start_chronicle_mission(&check).is_ok());
    }

    #[test]
    fn mission_main_quest_requires_chapter() {
        // MainQuest missions gate on chapter >= mission chapter.
        let check = StartMissionCheck {
            player_chapter: 1,
            mission_chapter: 2,
            mission_type: MissionType::MainQuest,
            player_location: "Altara",
            mission_location: "Altara",
            player_hp: 200,
            player_energy: 5.0,
            craft_missions_remaining: 5,
            has_active_mission: false,
        };
        assert_eq!(
            can_start_chronicle_mission(&check),
            Err(StartMissionError::ChapterLocked)
        );

        // Same chapter or higher → ok
        let check_ok = StartMissionCheck {
            player_chapter: 2,
            ..check
        };
        assert!(can_start_chronicle_mission(&check_ok).is_ok());
    }

    #[test]
    fn mission_room_parsing_and_navigation() {
        // Simulate a room from the missions table.
        // PHP: exits = "Idź dalej,room2;Wróć,room1"
        //      mobs = "Strażnik,A,Stoi tu strażnik.;Kupiec,T,Widzisz kupca.,Okradnij,thief10steal"
        //      items = "Klucz,Q,Leży tu klucz.,Podnieś,take_key"
        let exits = parse_exits("Idź dalej,room2;Wróć,room1");
        let mobs = parse_mobs(
            "Strażnik,A,Stoi tu strażnik.;Kupiec,T,Widzisz kupca.,Okradnij,thief10steal",
        );
        let items = parse_items("Klucz,Q,Leży tu klucz.,Podnieś,take_key");

        // Build room text: base text + mob/item descriptions
        let text = build_room_text("Wchodzisz do ciemnej jaskini.", &mobs, &items);
        assert!(text.contains("Wchodzisz do ciemnej jaskini."));
        assert!(text.contains("Stoi tu strażnik."));
        assert!(text.contains("Widzisz kupca."));
        assert!(text.contains("Leży tu klucz."));

        // Collect actions: exits + mob actions + item actions
        let actions = collect_room_actions(&exits, &mobs, &items);
        assert_eq!(actions.len(), 4);
        // exits
        assert_eq!(actions[0], ("room2".to_owned(), "Idź dalej".to_owned()));
        assert_eq!(actions[1], ("room1".to_owned(), "Wróć".to_owned()));
        // mob action
        assert_eq!(
            actions[2],
            ("thief10steal".to_owned(), "Okradnij".to_owned())
        );
        // item action
        assert_eq!(actions[3], ("take_key".to_owned(), "Podnieś".to_owned()));
    }

    #[test]
    fn mission_room_valid_action_targets() {
        let exits = parse_exits("Idź dalej,room2;Wróć,room1");
        let mobs = parse_mobs("Kupiec,T,Widzisz kupca.,Okradnij,thief10steal");
        let items = parse_items("Klucz,Q,Leży tu klucz.,Podnieś,take_key");
        let moreinfo = parse_moreinfo("");

        let targets = valid_action_targets(&exits, &mobs, &items, &moreinfo);
        assert!(targets.contains(&"room2".to_owned()));
        assert!(targets.contains(&"room1".to_owned()));
        assert!(targets.contains(&"thief10steal".to_owned()));
        assert!(targets.contains(&"take_key".to_owned()));
        assert!(!targets.contains(&"nonexistent".to_owned()));
    }

    #[test]
    fn mission_room_combat_adds_win_lose_targets() {
        let exits = parse_exits("Idź,room2");
        let mobs = parse_mobs("");
        let items = parse_items("");
        let moreinfo = parse_moreinfo("combat;5;1;winroom;loseroom");

        let targets = valid_action_targets(&exits, &mobs, &items, &moreinfo);
        assert!(targets.contains(&"room2".to_owned()));
        assert!(targets.contains(&"winroom".to_owned()));
        assert!(targets.contains(&"loseroom".to_owned()));
    }

    #[test]
    fn mission_terminal_room_detection() {
        // PHP: mission ends when room name contains resign, finish, or fail
        assert!(is_terminal_room("ele1resign"));
        assert!(is_terminal_room("thief10finish"));
        assert!(is_terminal_room("ele1fail"));
        assert!(is_terminal_room("some_resign_path"));

        // Non-terminal
        assert!(!is_terminal_room("ele1room3"));
        assert!(!is_terminal_room("thief10start"));
        assert!(!is_terminal_room("ele1room5"));
    }

    #[test]
    fn mission_thief_exits_filtered_by_type() {
        // In thief missions, some exits are only available to thieves.
        // PHP uses [T] prefix on exit labels.
        let exits = parse_exits("[T]Kradnij,thief10steal;[E]Walcz,ele1fight;Idź dalej,room2");

        // Thief player sees thief-specific + unfiltered exits
        let thief_exits = filter_exits_by_type(&exits, "T");
        assert_eq!(thief_exits.len(), 2);
        assert_eq!(thief_exits[0].target, "thief10steal");
        assert_eq!(thief_exits[1].target, "room2");

        // Non-thief player sees only their type + unfiltered
        let story_exits = filter_exits_by_type(&exits, "E");
        assert_eq!(story_exits.len(), 2);
        assert_eq!(story_exits[0].target, "ele1fight");
        assert_eq!(story_exits[1].target, "room2");
    }

    #[test]
    fn mission_class_placeholder_expansion() {
        // PHP replaces %fight%, %thief%, %mage%, %hunt%, %prof% with player's class
        let raw_exits = "Porozmawiaj z %prof%,talk_prof;Walcz %fight%,fight_target";

        // Wojownik (fighter) → %fight% matches, %prof% always matches
        let result = expand_class_placeholders(raw_exits, "Wojownik");
        assert!(result.contains("Porozmawiaj z Wojownik"));
        assert!(result.contains("Walcz Wojownik"));
    }

    #[test]
    fn mission_active_rooms_exhausted() {
        // When rooms_remaining reaches 0, the mission is over.
        let m = ActiveMission {
            player_id: 1,
            current_room_id: 42,
            raw_exits: "Idź,room2".into(),
            raw_mobs: String::new(),
            raw_items: String::new(),
            mission_type: MissionType::Story,
            loot_spec: String::new(),
            rooms_remaining: 0,
            successes: 5,
            bonus: 10,
            return_location: "Altara".into(),
            has_target: false,
            raw_moreinfo: String::new(),
        };
        assert!(m.is_rooms_exhausted());
    }

    #[test]
    fn mission_quest_target_requires_both_flag_and_successes() {
        // PHP gives quest rewards only when has_target AND successes >= 10
        let base = ActiveMission {
            player_id: 1,
            current_room_id: 42,
            raw_exits: String::new(),
            raw_mobs: String::new(),
            raw_items: String::new(),
            mission_type: MissionType::Story,
            loot_spec: String::new(),
            rooms_remaining: 0,
            successes: 10,
            bonus: 0,
            return_location: "Altara".into(),
            has_target: true,
            raw_moreinfo: String::new(),
        };

        // Both conditions met
        assert!(base.reached_quest_target());

        // Missing target flag
        let no_target = ActiveMission {
            has_target: false,
            ..base.clone()
        };
        assert!(!no_target.reached_quest_target());

        // Not enough successes
        let low_success = ActiveMission {
            successes: 9,
            ..base
        };
        assert!(!low_success.reached_quest_target());
    }

    #[test]
    fn mission_reward_full_success_with_target() {
        // PHP: $reward = $successes * 5 * $bonus + $quest_target * 10 * $bonus
        // Gold: successes * 5 * 50 * bonus_mult + quest_target_bonus
        // For successes=10, bonus=10, target hit:
        //   xp = 5*10 + 5*10 = 100
        //   gold = 5*10*50 + 10*10 = 2600
        //   mission_points = 1
        let r = calculate_mission_reward(10, 10, true, true);
        assert_eq!(r.xp, 100);
        assert_eq!(r.gold, 2600);
        assert_eq!(r.mission_points, 1);
    }

    #[test]
    fn mission_reward_partial_success_no_target() {
        // 5 successes, bonus=10, no target
        let r = calculate_mission_reward(5, 10, true, false);
        assert_eq!(r.xp, 25);
        assert_eq!(r.gold, 250);
        assert_eq!(r.mission_points, 0);
    }

    #[test]
    fn mission_reward_zero_successes() {
        // 0 successes → minimum xp=1, no gold, no mpoints
        let r = calculate_mission_reward(0, 10, true, false);
        assert_eq!(r.xp, 1);
        assert_eq!(r.gold, 0);
        assert_eq!(r.mission_points, 0);
    }

    #[test]
    fn mission_reward_no_bonus_multiplier() {
        // When bonus=0 (non-target path), xp and gold only depend on successes.
        // xp = 5 * successes = 25, gold = successes * 50 = 250
        let r = calculate_mission_reward(5, 0, true, false);
        assert_eq!(r.xp, 25);
        assert_eq!(r.gold, 250);
        assert_eq!(r.mission_points, 0);
    }

    // =====================================================================
    // 3. Thief mission fixtures
    //
    // PHP thieves.php generates 3 random jobs. The steal difficulty
    // calculation determines success chance.
    // =====================================================================

    #[test]
    fn thief_steal_difficulty_typical_scenario() {
        // Typical room: 2 mobs, 1 aggressive, non-aggressive target, skill=5
        // diff = 10 + 5*2 + 10*1 - 5 = 25
        assert_eq!(steal_difficulty(2, 1, false, 5), 25);
    }

    #[test]
    fn thief_steal_difficulty_hard_room() {
        // Hard room: 5 mobs, 3 aggressive, aggressive target, skill=0
        // diff = 10 + 5*5 + 10*3 + 20 - 0 = 85
        assert_eq!(steal_difficulty(5, 3, true, 0), 85);
    }

    #[test]
    fn thief_steal_difficulty_skilled_thief_easy_room() {
        // Easy room with skilled thief: 1 mob, 0 aggressive, skill=50
        // diff = 10 + 5*1 + 10*0 - 50 = -35 → clamped to 5
        assert_eq!(steal_difficulty(1, 0, false, 50), 5);
    }

    #[test]
    fn thief_steal_difficulty_overcrowded_room() {
        // Max difficulty scenario: many mobs → clamped to 95
        assert_eq!(steal_difficulty(15, 15, true, 0), 95);
    }

    #[test]
    fn thief_mission_kind_gating() {
        // Pickpocket and Tracking are available from mpoints=0.
        // HomeRobbery requires mpoints >= 10.
        // GuardDuty requires mpoints >= 5.
        assert_eq!(ThiefMissionKind::Pickpocket.min_mpoints(), 0);
        assert_eq!(ThiefMissionKind::Tracking.min_mpoints(), 0);
        assert_eq!(ThiefMissionKind::GuardDuty.min_mpoints(), 5);
        assert_eq!(ThiefMissionKind::HomeRobbery.min_mpoints(), 10);
    }

    #[test]
    fn thief_loot_reward_specs() {
        // PHP generates loot_spec strings stored in the mission session.
        assert_eq!(ThiefLootReward::None.to_loot_spec(), "");
        assert_eq!(
            ThiefLootReward::OrdinaryLockpick.to_loot_spec(),
            "tools;=1;T"
        );
        assert_eq!(ThiefLootReward::BetterLockpick.to_loot_spec(), "tools;>1;T");
        assert_eq!(ThiefLootReward::OrdinaryPlan.to_loot_spec(), "plans;=1;T");
        assert_eq!(ThiefLootReward::BetterPlan.to_loot_spec(), "plans;>1;T");
    }

    // =====================================================================
    // 4. Labyrinth exploration — grid.php parity
    //
    // PHP runs a loop of N explorations, each rolling rand(1,11).
    // Each step costs 0.3 energy. Results accumulate:
    //   3 → gold += rand(1,100)
    //   6 → mithril += rand(1,3)
    //   7 → energy_bonus += 1 (a spring that restores energy)
    //   10 → 1/5 chance of quest, else check for map (1/50)
    //   others → nothing
    //
    // If a quest triggers, the loop breaks immediately.
    // =====================================================================

    use crate::quest::maze::*;

    #[test]
    fn labyrinth_nothing_happens() {
        // All rolls produce nothing (outcome rolls != 3,6,7,10)
        let rolls = vec![
            LabyrinthRoll {
                outcome_roll: 1,
                gold_roll: 0,
                mithril_roll: 0,
                quest_roll: 0,
                map_roll: 0,
            },
            LabyrinthRoll {
                outcome_roll: 2,
                gold_roll: 0,
                mithril_roll: 0,
                quest_roll: 0,
                map_roll: 0,
            },
            LabyrinthRoll {
                outcome_roll: 4,
                gold_roll: 0,
                mithril_roll: 0,
                quest_roll: 0,
                map_roll: 0,
            },
        ];

        let result = process_labyrinth_steps(&rolls, || None, |_| false);
        assert_eq!(result.steps_completed, 3);
        assert_eq!(result.gold, 0);
        assert_eq!(result.mithril, 0);
        assert_eq!(result.energy_lost, 0);
        assert_eq!(result.maps_found, 0);
        assert!(result.quest_triggered.is_none());
    }

    #[test]
    fn labyrinth_gold_and_mithril_accumulate() {
        // Steps: gold(50), mithril(2), gold(75), nothing
        let rolls = vec![
            LabyrinthRoll {
                outcome_roll: 3,
                gold_roll: 50,
                mithril_roll: 0,
                quest_roll: 0,
                map_roll: 0,
            },
            LabyrinthRoll {
                outcome_roll: 6,
                gold_roll: 0,
                mithril_roll: 2,
                quest_roll: 0,
                map_roll: 0,
            },
            LabyrinthRoll {
                outcome_roll: 3,
                gold_roll: 75,
                mithril_roll: 0,
                quest_roll: 0,
                map_roll: 0,
            },
            LabyrinthRoll {
                outcome_roll: 5,
                gold_roll: 0,
                mithril_roll: 0,
                quest_roll: 0,
                map_roll: 0,
            },
        ];

        let result = process_labyrinth_steps(&rolls, || None, |_| false);
        assert_eq!(result.steps_completed, 4);
        assert_eq!(result.gold, 125); // 50 + 75
        assert_eq!(result.mithril, 2);
        assert_eq!(result.energy_lost, 0);
    }

    #[test]
    fn labyrinth_energy_loss_counts() {
        // 3 energy loss steps
        let rolls: Vec<LabyrinthRoll> = (0..3)
            .map(|_| LabyrinthRoll {
                outcome_roll: 7,
                gold_roll: 0,
                mithril_roll: 0,
                quest_roll: 0,
                map_roll: 0,
            })
            .collect();

        let result = process_labyrinth_steps(&rolls, || None, |_| false);
        assert_eq!(result.energy_lost, 3);
        assert_eq!(result.steps_completed, 3);
    }

    #[test]
    fn labyrinth_quest_trigger_stops_exploration() {
        // Step 1: nothing, Step 2: quest chance with quest_roll=5 → triggers,
        // Step 3: should NOT be reached
        let rolls = vec![
            LabyrinthRoll {
                outcome_roll: 1,
                gold_roll: 0,
                mithril_roll: 0,
                quest_roll: 0,
                map_roll: 0,
            },
            LabyrinthRoll {
                outcome_roll: 10,
                gold_roll: 0,
                mithril_roll: 0,
                quest_roll: 5,
                map_roll: 0,
            },
            LabyrinthRoll {
                outcome_roll: 3,
                gold_roll: 99,
                mithril_roll: 0,
                quest_roll: 0,
                map_roll: 0,
            },
        ];

        let result = process_labyrinth_steps(&rolls, || Some(42), |_| false);
        assert_eq!(result.quest_triggered, Some(42));
        assert_eq!(result.steps_completed, 2); // stopped at step 2
        assert_eq!(result.gold, 0); // step 3 never reached
    }

    #[test]
    fn labyrinth_quest_chance_but_no_quest_available() {
        // Quest roll fires (roll=5) but no quest available → check for map
        let rolls = vec![LabyrinthRoll {
            outcome_roll: 10,
            gold_roll: 0,
            mithril_roll: 0,
            quest_roll: 5,
            map_roll: 1,
        }];

        // No quest, but map eligible when roll=1
        let result = process_labyrinth_steps(&rolls, || None, |roll| roll == 1);
        assert!(result.quest_triggered.is_none());
        assert_eq!(result.maps_found, 1);
        assert_eq!(result.steps_completed, 1);
    }

    #[test]
    fn labyrinth_quest_chance_no_trigger_roll() {
        // outcome_roll=10 (quest chance) but quest_roll != 5 → no quest trigger
        // map_roll is checked as fallback
        let rolls = vec![LabyrinthRoll {
            outcome_roll: 10,
            gold_roll: 0,
            mithril_roll: 0,
            quest_roll: 3, // not 5
            map_roll: 1,
        }];

        let result = process_labyrinth_steps(&rolls, || panic!("should not be called"), |r| r == 1);
        assert!(result.quest_triggered.is_none());
        assert_eq!(result.maps_found, 1);
    }

    #[test]
    fn labyrinth_mixed_scenario() {
        // A realistic 5-step exploration:
        // Step 1: Gold (47)
        // Step 2: Nothing
        // Step 3: Energy loss
        // Step 4: Mithril (3)
        // Step 5: Quest chance → rolls 2 (no trigger) → map roll 25 (no map)
        let rolls = vec![
            LabyrinthRoll {
                outcome_roll: 3,
                gold_roll: 47,
                mithril_roll: 0,
                quest_roll: 0,
                map_roll: 0,
            },
            LabyrinthRoll {
                outcome_roll: 8,
                gold_roll: 0,
                mithril_roll: 0,
                quest_roll: 0,
                map_roll: 0,
            },
            LabyrinthRoll {
                outcome_roll: 7,
                gold_roll: 0,
                mithril_roll: 0,
                quest_roll: 0,
                map_roll: 0,
            },
            LabyrinthRoll {
                outcome_roll: 6,
                gold_roll: 0,
                mithril_roll: 3,
                quest_roll: 0,
                map_roll: 0,
            },
            LabyrinthRoll {
                outcome_roll: 10,
                gold_roll: 0,
                mithril_roll: 0,
                quest_roll: 2,
                map_roll: 25,
            },
        ];

        let result = process_labyrinth_steps(&rolls, || None, |_| false);
        assert_eq!(result.steps_completed, 5);
        assert_eq!(result.gold, 47);
        assert_eq!(result.mithril, 3);
        assert_eq!(result.energy_lost, 1);
        assert_eq!(result.maps_found, 0);
        assert!(result.quest_triggered.is_none());
    }

    #[test]
    fn labyrinth_energy_cost_calculation() {
        // PHP: energy_cost = steps * 0.3
        // 10 steps × 0.3 = 3.0
        let steps = 10;
        let cost = f64::from(steps) * LABYRINTH_ENERGY_COST;
        assert!((cost - 3.0).abs() < f64::EPSILON);

        // With 2 energy springs found, net energy change = 2.0 - 3.0 = -1.0
        let energy_bonus = 2;
        let delta = f64::from(energy_bonus) - cost;
        assert!((delta - (-1.0)).abs() < f64::EPSILON);
    }

    #[test]
    fn labyrinth_entry_while_travelling() {
        // PHP allows labyrinth from "Podróż" (travelling) state
        assert!(can_explore_labyrinth("Podróż", 100, 3.0, 5, false).is_ok());
    }

    #[test]
    fn labyrinth_step_clamp_to_affordable() {
        // Player with 0.9 energy can do 3 steps (0.9 / 0.3 = 3)
        let steps = can_explore_labyrinth("Altara", 100, 0.9, 10, false).unwrap();
        assert_eq!(steps, 3);
    }

    // =====================================================================
    // 5. Maze entry (maze.php) — Ardulith parity
    // =====================================================================

    #[test]
    fn maze_ardulith_only() {
        // maze.php checks location == "Ardulith"
        assert!(can_enter_maze("Ardulith", 100).is_ok());
        assert_eq!(can_enter_maze("Altara", 100), Err(MazeError::WrongLocation));
        assert_eq!(can_enter_maze("Podróż", 100), Err(MazeError::WrongLocation));
    }

    // =====================================================================
    // 6. Random event state machine — events parity
    // =====================================================================

    use crate::quest::events::*;

    #[test]
    fn event_phase_lifecycle() {
        // A delivery event goes: None → DeliveryOffered → DeliveryInProgress → DeliveryComplete → CooldownReward
        assert!(!EventPhase::None.is_active());
        assert!(EventPhase::DeliveryOffered.is_active());
        assert!(EventPhase::DeliveryInProgress.is_active());
        assert!(EventPhase::DeliveryComplete.is_active());
        assert!(!EventPhase::CooldownReward.is_active());

        // Cooldown phases
        assert!(EventPhase::CooldownNormal.is_cooldown());
        assert!(EventPhase::CooldownReward.is_cooldown());
    }

    #[test]
    fn event_delivery_target_city() {
        // Delivery events always go to the other city
        assert_eq!(delivery_target_city("Altara"), "Ardulith");
        assert_eq!(delivery_target_city("Ardulith"), "Altara");
    }

    #[test]
    fn event_kind_from_roll_values() {
        // PHP: switch(rand(0,2)) → 0=Delivery, 1=Beggar, 2=SuspiciousFigure
        assert_eq!(RandomEventKind::from_roll(0), RandomEventKind::Delivery);
        assert_eq!(RandomEventKind::from_roll(1), RandomEventKind::Beggar);
        assert_eq!(
            RandomEventKind::from_roll(2),
            RandomEventKind::SuspiciousFigure
        );
    }

    // =====================================================================
    // 7. Mission loader roll_options — chance-based content filtering
    //
    // PHP rooms have comma-separated chance values. Each exit/mob/item has
    // a percentage chance of appearing. e.g. "50;80;30" means:
    //   exit 0: 50% chance, exit 1: 80%, exit 2: 30%
    // =====================================================================

    #[test]
    fn roll_options_deterministic_filter() {
        // 3 items with chances 40, 70, 10
        let items = vec!["goblin", "chest", "trap"];
        let chances = vec![40, 70, 10];
        let mut call_idx = 0;
        let rolls = [30, 50, 5]; // 30<40 pass, 50<70 pass, 5<10 pass
        let result = roll_options(&items, &chances, || {
            let r = rolls[call_idx];
            call_idx += 1;
            r
        });
        assert_eq!(result, vec!["goblin", "chest", "trap"]);
    }

    #[test]
    fn roll_options_none_pass_high_rolls() {
        let items = vec!["goblin", "chest"];
        let chances = vec![30, 50];
        let result = roll_options(&items, &chances, || 90); // 90 >= all chances
        assert!(result.is_empty());
    }

    #[test]
    fn roll_options_partial_pass() {
        let items = vec!["a", "b", "c", "d"];
        let chances = vec![50, 50, 50, 50];
        let mut idx = 0;
        let rolls = [10, 60, 20, 80]; // pass, fail, pass, fail
        let result = roll_options(&items, &chances, || {
            let r = rolls[idx];
            idx += 1;
            r
        });
        assert_eq!(result, vec!["a", "c"]);
    }

    // =====================================================================
    // 8. Room prefix extraction — mission naming convention
    //
    // PHP mission rooms are named like "ele1room3", "thief10start".
    // The prefix (e.g. "ele1", "thief10") groups rooms into missions.
    // =====================================================================

    #[test]
    fn room_prefix_story_missions() {
        // Story missions typically use patterns like ele1, his2, etc.
        assert_eq!(extract_room_prefix("ele1room3"), "ele1");
        assert_eq!(extract_room_prefix("ele1start"), "ele1");
        assert_eq!(extract_room_prefix("ele1finish"), "ele1");
        assert_eq!(extract_room_prefix("ele1resign"), "ele1");
        assert_eq!(extract_room_prefix("his2room1"), "his2");
    }

    #[test]
    fn room_prefix_thief_missions() {
        assert_eq!(extract_room_prefix("thief10start"), "thief10");
        assert_eq!(extract_room_prefix("thief10steal"), "thief10");
        assert_eq!(extract_room_prefix("thief1room2"), "thief1");
    }

    #[test]
    fn room_prefix_edge_cases() {
        // No suffix → entire name is the prefix
        assert_eq!(extract_room_prefix("abc123"), "abc123");
        // Only letters → entire name
        assert_eq!(extract_room_prefix("abcdef"), "abcdef");
        // Empty
        assert_eq!(extract_room_prefix(""), "");
    }

    // =====================================================================
    // 9. End-to-end mission flow simulation (without DB)
    //
    // This simulates the lifecycle:
    //   start mission → navigate 3 rooms → reach terminal → get reward
    // =====================================================================

    #[test]
    fn mission_lifecycle_simulation() {
        // Phase 1: Check mission start eligibility
        let check = StartMissionCheck {
            player_chapter: 1,
            mission_chapter: 0,
            mission_type: MissionType::Story,
            player_location: "Altara",
            mission_location: "Altara",
            player_hp: 200,
            player_energy: 5.0,
            craft_missions_remaining: 3,
            has_active_mission: false,
        };
        assert!(can_start_chronicle_mission(&check).is_ok());

        // Phase 2: Create active mission (normally done by DB insert)
        let mut mission = ActiveMission {
            player_id: 1,
            current_room_id: 100,
            raw_exits: "Idź dalej,ele1room2;Wróć,ele1resign".into(),
            raw_mobs: "Gnom,A,Widzisz gnoma.".into(),
            raw_items: String::new(),
            mission_type: MissionType::Story,
            loot_spec: String::new(),
            rooms_remaining: 3,
            successes: 0,
            bonus: 10,
            return_location: "Altara".into(),
            has_target: false,
            raw_moreinfo: String::new(),
        };

        // Phase 3: Navigate room 1
        assert!(!mission.is_rooms_exhausted());
        let exits = parse_exits(&mission.raw_exits);
        let mobs = parse_mobs(&mission.raw_mobs);
        let items = parse_items(&mission.raw_items);
        let targets = valid_action_targets(
            &exits,
            &mobs,
            &items,
            &parse_moreinfo(&mission.raw_moreinfo),
        );
        assert!(targets.contains(&"ele1room2".to_owned()));

        // Player chooses "Idź dalej" → moves to ele1room2
        mission.rooms_remaining -= 1;
        mission.raw_exits = "Idź dalej,ele1room3".into();
        mission.raw_mobs = String::new();

        // Phase 4: Navigate room 2
        assert!(!mission.is_rooms_exhausted());
        mission.rooms_remaining -= 1;
        mission.raw_exits = "Zakończ,ele1finish".into();

        // Phase 5: Navigate to terminal room
        assert!(is_terminal_room("ele1finish"));
        mission.rooms_remaining -= 1;
        assert!(mission.is_rooms_exhausted());

        // Phase 6: Calculate reward
        let reward = calculate_mission_reward(
            mission.successes,
            mission.bonus,
            true,
            mission.reached_quest_target(),
        );
        // 0 successes → minimum xp=1
        assert_eq!(reward.xp, 1);
    }

    #[test]
    fn mission_lifecycle_with_quest_target() {
        // Simulate a mission where the player finds quest targets
        let mission = ActiveMission {
            player_id: 1,
            current_room_id: 100,
            raw_exits: String::new(),
            raw_mobs: String::new(),
            raw_items: String::new(),
            mission_type: MissionType::Story,
            loot_spec: String::new(),
            rooms_remaining: 0,
            successes: 12,
            bonus: 15,
            return_location: "Altara".into(),
            has_target: true,
            raw_moreinfo: String::new(),
        };

        assert!(mission.reached_quest_target());

        let reward = calculate_mission_reward(
            mission.successes,
            mission.bonus,
            true,
            mission.reached_quest_target(),
        );
        // successes=12, bonus=15, quest_target hit
        // xp = 5*12 + 5*15 = 60 + 75 = 135
        // gold = 5*12*50 + 10*15 = 3000 + 150 = 3150
        assert_eq!(reward.xp, 135);
        assert_eq!(reward.gold, 3150);
        assert_eq!(reward.mission_points, 1);
    }

    #[test]
    fn mission_resume_preserves_state() {
        // After a page reload, PHP reconstructs the mission from the DB row.
        // The raw_exits/mobs/items are re-parsed. This test verifies round-trip.
        // Note: type_filter ([T] prefix) is NOT preserved in serialization
        // because it's consumed during filtering before DB storage.
        let exits_str = "Idź dalej,room2;Wróć,room1";
        let mobs_str = "Strażnik,A,Opis strażnika.;NPC,T,Opis NPC.,Mów,talk";
        let items_str = "Miecz,E,Leży tu miecz.,Weź,take_sword";

        // Parse
        let exits = parse_exits(exits_str);
        let mobs = parse_mobs(mobs_str);
        let items = parse_items(items_str);
        assert_eq!(exits.len(), 2);
        assert_eq!(mobs.len(), 2);
        assert_eq!(items.len(), 1);

        // Serialize back
        let exits_round = serialize_exits(&exits);
        let mobs_round = serialize_mobs(&mobs);
        let items_round = serialize_items(&items);

        // Re-parse
        let exits2 = parse_exits(&exits_round);
        let mobs2 = parse_mobs(&mobs_round);
        let items2 = parse_items(&items_round);

        assert_eq!(exits2.len(), exits.len());
        assert_eq!(mobs2.len(), mobs.len());
        assert_eq!(items2.len(), items.len());

        // Verify content preserved
        for (a, b) in exits.iter().zip(exits2.iter()) {
            assert_eq!(a.label, b.label);
            assert_eq!(a.target, b.target);
        }
    }
}
