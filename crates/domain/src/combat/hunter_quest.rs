//! Hunter guild quest types, validation, and reward calculation.
//!
//! Ported from PHP `hunters.php`.  The hunter guild offers periodic quests
//! in each city.  Quest types:
//!
//! - **Fight** (`F`) — kill N copies of a specific monster.
//! - **Item delivery** (`I`) — deliver N pieces of equipment.
//! - **Bow delivery** (`B`) — deliver N bows.
//! - **Potion delivery** (`P`) — deliver N potions.
//! - **Loot delivery** (`L`) — deliver a specific monster loot drop.
//!
//! All functions are pure — no I/O or global state.

// ---------------------------------------------------------------------------
// Quest type
// ---------------------------------------------------------------------------

/// The five hunter quest types, encoded as a single char in the DB
/// `settings.value` field.
#[derive(Debug, Clone, Copy, PartialEq, Eq)]
pub enum HunterQuestType {
    /// Kill N of a specific monster.
    Fight,
    /// Deliver N equipment items.
    Item,
    /// Deliver N bows.
    Bow,
    /// Deliver N potions.
    Potion,
    /// Deliver a specific loot drop from a monster.
    Loot,
}

impl HunterQuestType {
    /// Parse from the PHP single-char code.
    pub fn from_code(code: &str) -> Option<Self> {
        match code {
            "F" => Some(Self::Fight),
            "I" => Some(Self::Item),
            "B" => Some(Self::Bow),
            "P" => Some(Self::Potion),
            "L" => Some(Self::Loot),
            _ => None,
        }
    }

    /// Convert back to the single-char code for DB storage.
    pub fn to_code(self) -> &'static str {
        match self {
            Self::Fight => "F",
            Self::Item => "I",
            Self::Bow => "B",
            Self::Potion => "P",
            Self::Loot => "L",
        }
    }
}

// ---------------------------------------------------------------------------
// Material quality tier (for Item/Bow quests)
// ---------------------------------------------------------------------------

/// Material tier index (0–4) used for equipment and bow quests.
///
/// Equipment materials: copper(0), bronze(1), brass(2), iron(3), steel(4).
/// Bow materials: hazel(0), yew(1), elm(2), reinforced(3), composite(4).
pub const MATERIAL_TIERS: usize = 5;

/// Price multiplier by material tier.
///
/// PHP: `$arrBonus = array(1, 1.05, 1.1, 1.15, 1.2);`
pub const MATERIAL_BONUS: [f64; MATERIAL_TIERS] = [1.0, 1.05, 1.10, 1.15, 1.20];

// ---------------------------------------------------------------------------
// Parsed quest
// ---------------------------------------------------------------------------

/// A fully parsed hunter quest from the settings value.
///
/// PHP stores quest data as `"TYPE;ID;AMOUNT[;TIER]"` in
/// `settings.value` where `setting='hunteraltara'` or `'hunterardulith'`.
#[derive(Debug, Clone, PartialEq)]
pub struct HunterQuest {
    pub quest_type: HunterQuestType,
    /// Monster ID (Fight/Loot) or item/bow/potion catalog ID (Item/Bow/Potion).
    pub target_id: i32,
    /// Required amount (kill count or delivery count) for Fight/Item/Bow/Potion.
    /// For Loot quests, this is the loot table index.
    pub amount: i32,
    /// Material tier (0–4) for Item/Bow quests. Unused for others.
    pub material_tier: Option<usize>,
}

/// Parse a quest from the semicolon-delimited settings value.
///
/// Examples:
/// - `"F;42;5"` → Fight monster #42, 5 kills
/// - `"I;10;3;2"` → Deliver 3× equipment #10, tier 2 (brass)
/// - `"L;7;1"` → Deliver loot index 1 from monster #7
pub fn parse_quest(value: &str) -> Option<HunterQuest> {
    if value.is_empty() {
        return None;
    }
    let parts: Vec<&str> = value.split(';').collect();
    if parts.len() < 3 {
        return None;
    }
    let quest_type = HunterQuestType::from_code(parts[0])?;
    let target_id = parts[1].parse::<i32>().ok()?;
    let amount = parts[2].parse::<i32>().ok()?;

    let material_tier = match quest_type {
        HunterQuestType::Item | HunterQuestType::Bow => {
            let tier = parts.get(3).and_then(|s| s.parse::<usize>().ok())?;
            if tier >= MATERIAL_TIERS {
                return None;
            }
            Some(tier)
        }
        HunterQuestType::Potion => {
            // Potions also store a tier field but it's always 0.
            Some(0)
        }
        _ => None,
    };

    Some(HunterQuest {
        quest_type,
        target_id,
        amount,
        material_tier,
    })
}

/// Serialize a quest back to the settings value format.
pub fn serialize_quest(quest: &HunterQuest) -> String {
    let base = format!(
        "{};{};{}",
        quest.quest_type.to_code(),
        quest.target_id,
        quest.amount,
    );
    match quest.material_tier {
        Some(tier) => format!("{base};{tier}"),
        None => base,
    }
}

// ---------------------------------------------------------------------------
// Reward calculation
// ---------------------------------------------------------------------------

/// Gold reward for a fight quest.
///
/// PHP: `$intGold = ($monster_level * 10 * $quest_amount)`
pub fn fight_quest_gold(monster_level: i32, kill_count: i32) -> i64 {
    i64::from(monster_level) * 10 * i64::from(kill_count)
}

/// Gold reward for an item/bow delivery quest.
///
/// PHP: `ceil($item_cost * $material_bonus * $amount)`
#[allow(clippy::cast_precision_loss, clippy::cast_possible_truncation)]
pub fn delivery_quest_gold(item_base_cost: i64, material_tier: usize, amount: i32) -> i64 {
    let bonus = if material_tier < MATERIAL_TIERS {
        MATERIAL_BONUS[material_tier]
    } else {
        1.0
    };
    (item_base_cost as f64 * bonus * f64::from(amount)).ceil() as i64
}

/// Gold reward for a potion delivery quest.
///
/// PHP: `$item_cost = $potion_power * 3; ceil($item_cost * 1.0 * $amount)`
pub fn potion_quest_gold(potion_power: i32, amount: i32) -> i64 {
    let cost = i64::from(potion_power) * 3;
    cost * i64::from(amount)
}

/// Gold reward for a loot delivery quest.
///
/// PHP: `$monster_level * 100`
pub fn loot_quest_gold(monster_level: i32) -> i64 {
    i64::from(monster_level) * 100
}

// ---------------------------------------------------------------------------
// Quest execution validation
// ---------------------------------------------------------------------------

/// Error when a quest cannot be executed.
#[derive(Debug, Clone, PartialEq, Eq)]
pub enum QuestError {
    /// No quest is available at this location.
    NoQuest,
    /// Not in a valid city.
    WrongLocation,
    /// Player is dead.
    PlayerDead,
    /// Not enough energy to take the quest (minimum 1).
    InsufficientEnergy,
    /// Player doesn't have the required item in their inventory.
    MissingItem,
    /// Player doesn't have enough of the required item.
    InsufficientItemCount { have: i32, need: i32 },
}

/// Energy cost to execute a hunter guild quest.
pub const HUNTER_QUEST_ENERGY: f64 = 1.0;

/// Validate that a player can attempt a hunter quest.
pub fn validate_quest_attempt(
    location: &str,
    hp: i32,
    energy: f64,
    quest_available: bool,
) -> Result<(), QuestError> {
    if location != "Altara" && location != "Ardulith" {
        return Err(QuestError::WrongLocation);
    }
    if !quest_available {
        return Err(QuestError::NoQuest);
    }
    if hp <= 0 {
        return Err(QuestError::PlayerDead);
    }
    if energy < HUNTER_QUEST_ENERGY {
        return Err(QuestError::InsufficientEnergy);
    }
    Ok(())
}

/// Validate that a delivery quest can be fulfilled.
///
/// `player_item_count` is how many matching items the player has in
/// their backpack.
pub fn validate_delivery(player_item_count: i32, required_amount: i32) -> Result<(), QuestError> {
    if player_item_count <= 0 {
        return Err(QuestError::MissingItem);
    }
    if player_item_count < required_amount {
        return Err(QuestError::InsufficientItemCount {
            have: player_item_count,
            need: required_amount,
        });
    }
    Ok(())
}

// ---------------------------------------------------------------------------
// Quest generation
// ---------------------------------------------------------------------------

/// Inputs for generating a new random hunter quest.
#[derive(Debug, Clone)]
pub struct QuestGenInputs {
    /// Random type index 0–4 → F/I/L/B/P.
    pub type_roll: usize,
    /// ID chosen from available candidates (monster or item).
    pub target_id: i32,
    /// Random amount 1–10.
    pub amount_roll: i32,
    /// Material tier 0–4 (for Item/Bow).
    pub material_roll: usize,
    /// Loot index 0–3 (for Loot quests).
    pub loot_index_roll: i32,
}

/// Available quest types in generation order.
const QUEST_TYPES: [HunterQuestType; 5] = [
    HunterQuestType::Fight,
    HunterQuestType::Item,
    HunterQuestType::Loot,
    HunterQuestType::Bow,
    HunterQuestType::Potion,
];

/// Generate a new random quest from the given inputs.
///
/// The caller is responsible for selecting a valid `target_id` from the
/// available candidates (monsters for F/L, items for I, bows for B,
/// potions for P).
///
/// Returns `None` if the type roll is out of range.
pub fn generate_quest(inputs: &QuestGenInputs) -> Option<HunterQuest> {
    let quest_type = *QUEST_TYPES.get(inputs.type_roll)?;

    let material_tier = match quest_type {
        HunterQuestType::Item | HunterQuestType::Bow => {
            Some(inputs.material_roll.min(MATERIAL_TIERS - 1))
        }
        HunterQuestType::Potion => Some(0),
        _ => None,
    };

    let amount = match quest_type {
        HunterQuestType::Loot => inputs.loot_index_roll,
        _ => inputs.amount_roll,
    };

    Some(HunterQuest {
        quest_type,
        target_id: inputs.target_id,
        amount,
        material_tier,
    })
}

// ---------------------------------------------------------------------------
// Quest completion state
// ---------------------------------------------------------------------------

/// Outcome of completing a hunter quest.
#[derive(Debug, Clone, PartialEq)]
pub struct QuestCompletion {
    /// Gold reward earned.
    pub gold_reward: i64,
    /// Remaining quest completions before the quest expires.
    pub remaining_completions: i32,
    /// Whether a new quest was generated to replace this one.
    pub new_quest_generated: bool,
}

/// Process a quest completion, computing reward and checking if the
/// quest slot should be regenerated.
///
/// `completions_remaining` is the `hunter{city}amount` settings value.
/// When it reaches 0, the quest is removed.
pub fn complete_quest(
    quest: &HunterQuest,
    monster_level: i32,
    item_base_cost: i64,
    potion_power: i32,
    completions_remaining: i32,
) -> QuestCompletion {
    let gold_reward = match quest.quest_type {
        HunterQuestType::Fight => fight_quest_gold(monster_level, quest.amount),
        HunterQuestType::Item | HunterQuestType::Bow => delivery_quest_gold(
            item_base_cost,
            quest.material_tier.unwrap_or(0),
            quest.amount,
        ),
        HunterQuestType::Potion => potion_quest_gold(potion_power, quest.amount),
        HunterQuestType::Loot => loot_quest_gold(monster_level),
    };

    let remaining = completions_remaining - 1;
    let new_quest_generated = remaining > 0;

    QuestCompletion {
        gold_reward,
        remaining_completions: remaining,
        new_quest_generated,
    }
}

// ---------------------------------------------------------------------------
// Bestiary
// ---------------------------------------------------------------------------

/// A monster entry in the bestiary list.
#[derive(Debug, Clone, PartialEq, Eq)]
pub struct BestiaryEntry {
    pub id: i32,
    pub name: String,
}

/// Split monsters into two location lists for the bestiary display.
///
/// PHP splits by location into Altara and non-Altara (Ardulith).
pub fn split_bestiary(
    monsters: Vec<BestiaryEntry>,
    locations: &[(i32, String)],
) -> (Vec<BestiaryEntry>, Vec<BestiaryEntry>) {
    let mut altara = Vec::new();
    let mut ardulith = Vec::new();

    for entry in monsters {
        let loc = locations
            .iter()
            .find(|(id, _)| *id == entry.id)
            .map(|(_, l)| l.as_str());

        if loc == Some("Altara") {
            altara.push(entry);
        } else {
            ardulith.push(entry);
        }
    }

    (altara, ardulith)
}

// ---------------------------------------------------------------------------
// Tests
// ---------------------------------------------------------------------------

#[cfg(test)]
mod tests {
    use super::*;

    // --- Quest type parsing ---

    #[test]
    fn quest_type_roundtrip() {
        for code in &["F", "I", "B", "P", "L"] {
            let qt = HunterQuestType::from_code(code).unwrap();
            assert_eq!(qt.to_code(), *code);
        }
    }

    #[test]
    fn quest_type_invalid() {
        assert!(HunterQuestType::from_code("X").is_none());
        assert!(HunterQuestType::from_code("").is_none());
    }

    // --- Quest parsing ---

    #[test]
    fn parse_fight_quest() {
        let q = parse_quest("F;42;5").unwrap();
        assert_eq!(q.quest_type, HunterQuestType::Fight);
        assert_eq!(q.target_id, 42);
        assert_eq!(q.amount, 5);
        assert_eq!(q.material_tier, None);
    }

    #[test]
    fn parse_item_quest() {
        let q = parse_quest("I;10;3;2").unwrap();
        assert_eq!(q.quest_type, HunterQuestType::Item);
        assert_eq!(q.target_id, 10);
        assert_eq!(q.amount, 3);
        assert_eq!(q.material_tier, Some(2));
    }

    #[test]
    fn parse_bow_quest() {
        let q = parse_quest("B;7;2;4").unwrap();
        assert_eq!(q.quest_type, HunterQuestType::Bow);
        assert_eq!(q.target_id, 7);
        assert_eq!(q.amount, 2);
        assert_eq!(q.material_tier, Some(4));
    }

    #[test]
    fn parse_potion_quest() {
        let q = parse_quest("P;5;10;0").unwrap();
        assert_eq!(q.quest_type, HunterQuestType::Potion);
        assert_eq!(q.target_id, 5);
        assert_eq!(q.amount, 10);
        assert_eq!(q.material_tier, Some(0));
    }

    #[test]
    fn parse_loot_quest() {
        let q = parse_quest("L;7;1").unwrap();
        assert_eq!(q.quest_type, HunterQuestType::Loot);
        assert_eq!(q.target_id, 7);
        assert_eq!(q.amount, 1);
        assert_eq!(q.material_tier, None);
    }

    #[test]
    fn parse_empty() {
        assert!(parse_quest("").is_none());
    }

    #[test]
    fn parse_too_few_parts() {
        assert!(parse_quest("F;42").is_none());
    }

    #[test]
    fn parse_invalid_tier() {
        assert!(parse_quest("I;10;3;5").is_none()); // tier 5 out of range
    }

    #[test]
    fn parse_item_missing_tier() {
        assert!(parse_quest("I;10;3").is_none()); // Item needs tier
    }

    // --- Serialization roundtrip ---

    #[test]
    fn serialize_roundtrip() {
        let values = ["F;42;5", "I;10;3;2", "B;7;2;4", "P;5;10;0", "L;7;1"];
        for v in values {
            let q = parse_quest(v).unwrap();
            assert_eq!(serialize_quest(&q), v);
        }
    }

    // --- Rewards ---

    #[test]
    fn fight_gold_reward() {
        // level=10, kills=5 → 10*10*5 = 500
        assert_eq!(fight_quest_gold(10, 5), 500);
    }

    #[test]
    fn delivery_gold_tier0() {
        // cost=100, tier=0 (1.0×), amount=3 → 300
        assert_eq!(delivery_quest_gold(100, 0, 3), 300);
    }

    #[test]
    fn delivery_gold_tier4() {
        // cost=100, tier=4 (1.2×), amount=2 → ceil(100*1.2*2) = 240
        assert_eq!(delivery_quest_gold(100, 4, 2), 240);
    }

    #[test]
    fn delivery_gold_rounding() {
        // cost=33, tier=1 (1.05×), amount=1 → ceil(33*1.05) = ceil(34.65) = 35
        assert_eq!(delivery_quest_gold(33, 1, 1), 35);
    }

    #[test]
    fn potion_gold_reward() {
        // power=10, amount=5 → (10*3) * 5 = 150
        assert_eq!(potion_quest_gold(10, 5), 150);
    }

    #[test]
    fn loot_gold_reward() {
        // level=15 → 15*100 = 1500
        assert_eq!(loot_quest_gold(15), 1500);
    }

    // --- Validation ---

    #[test]
    fn validate_quest_ok_altara() {
        assert!(validate_quest_attempt("Altara", 100, 5.0, true).is_ok());
    }

    #[test]
    fn validate_quest_ok_ardulith() {
        assert!(validate_quest_attempt("Ardulith", 100, 5.0, true).is_ok());
    }

    #[test]
    fn validate_quest_wrong_location() {
        assert_eq!(
            validate_quest_attempt("Las", 100, 5.0, true),
            Err(QuestError::WrongLocation)
        );
    }

    #[test]
    fn validate_quest_no_quest() {
        assert_eq!(
            validate_quest_attempt("Altara", 100, 5.0, false),
            Err(QuestError::NoQuest)
        );
    }

    #[test]
    fn validate_quest_dead() {
        assert_eq!(
            validate_quest_attempt("Altara", 0, 5.0, true),
            Err(QuestError::PlayerDead)
        );
    }

    #[test]
    fn validate_quest_no_energy() {
        assert_eq!(
            validate_quest_attempt("Altara", 100, 0.5, true),
            Err(QuestError::InsufficientEnergy)
        );
    }

    #[test]
    fn validate_delivery_ok() {
        assert!(validate_delivery(5, 3).is_ok());
    }

    #[test]
    fn validate_delivery_exact() {
        assert!(validate_delivery(3, 3).is_ok());
    }

    #[test]
    fn validate_delivery_missing() {
        assert_eq!(validate_delivery(0, 3), Err(QuestError::MissingItem));
    }

    #[test]
    fn validate_delivery_insufficient() {
        assert_eq!(
            validate_delivery(2, 5),
            Err(QuestError::InsufficientItemCount { have: 2, need: 5 })
        );
    }

    // --- Quest generation ---

    #[test]
    fn generate_fight_quest() {
        let inputs = QuestGenInputs {
            type_roll: 0,
            target_id: 42,
            amount_roll: 7,
            material_roll: 0,
            loot_index_roll: 0,
        };
        let q = generate_quest(&inputs).unwrap();
        assert_eq!(q.quest_type, HunterQuestType::Fight);
        assert_eq!(q.target_id, 42);
        assert_eq!(q.amount, 7);
        assert_eq!(q.material_tier, None);
    }

    #[test]
    fn generate_item_quest() {
        let inputs = QuestGenInputs {
            type_roll: 1,
            target_id: 10,
            amount_roll: 3,
            material_roll: 2,
            loot_index_roll: 0,
        };
        let q = generate_quest(&inputs).unwrap();
        assert_eq!(q.quest_type, HunterQuestType::Item);
        assert_eq!(q.material_tier, Some(2));
    }

    #[test]
    fn generate_loot_quest_uses_loot_index() {
        let inputs = QuestGenInputs {
            type_roll: 2,
            target_id: 7,
            amount_roll: 99,
            material_roll: 0,
            loot_index_roll: 2,
        };
        let q = generate_quest(&inputs).unwrap();
        assert_eq!(q.quest_type, HunterQuestType::Loot);
        assert_eq!(q.amount, 2); // uses loot_index_roll, not amount_roll
    }

    #[test]
    fn generate_bow_quest() {
        let inputs = QuestGenInputs {
            type_roll: 3,
            target_id: 5,
            amount_roll: 4,
            material_roll: 3,
            loot_index_roll: 0,
        };
        let q = generate_quest(&inputs).unwrap();
        assert_eq!(q.quest_type, HunterQuestType::Bow);
        assert_eq!(q.material_tier, Some(3));
    }

    #[test]
    fn generate_potion_quest() {
        let inputs = QuestGenInputs {
            type_roll: 4,
            target_id: 3,
            amount_roll: 8,
            material_roll: 0,
            loot_index_roll: 0,
        };
        let q = generate_quest(&inputs).unwrap();
        assert_eq!(q.quest_type, HunterQuestType::Potion);
        assert_eq!(q.material_tier, Some(0)); // always 0 for potions
    }

    #[test]
    fn generate_out_of_range() {
        let inputs = QuestGenInputs {
            type_roll: 5,
            target_id: 1,
            amount_roll: 1,
            material_roll: 0,
            loot_index_roll: 0,
        };
        assert!(generate_quest(&inputs).is_none());
    }

    #[test]
    fn material_tier_clamped() {
        let inputs = QuestGenInputs {
            type_roll: 1, // Item
            target_id: 1,
            amount_roll: 1,
            material_roll: 99, // out of range → clamped to 4
            loot_index_roll: 0,
        };
        let q = generate_quest(&inputs).unwrap();
        assert_eq!(q.material_tier, Some(4));
    }

    // --- Quest completion ---

    #[test]
    fn complete_fight_quest() {
        let quest = HunterQuest {
            quest_type: HunterQuestType::Fight,
            target_id: 1,
            amount: 5,
            material_tier: None,
        };
        let c = complete_quest(&quest, 10, 0, 0, 3);
        assert_eq!(c.gold_reward, 500);
        assert_eq!(c.remaining_completions, 2);
        assert!(c.new_quest_generated);
    }

    #[test]
    fn complete_quest_last_completion() {
        let quest = HunterQuest {
            quest_type: HunterQuestType::Fight,
            target_id: 1,
            amount: 1,
            material_tier: None,
        };
        let c = complete_quest(&quest, 5, 0, 0, 1);
        assert_eq!(c.remaining_completions, 0);
        assert!(!c.new_quest_generated);
    }

    #[test]
    fn complete_item_delivery() {
        let quest = HunterQuest {
            quest_type: HunterQuestType::Item,
            target_id: 10,
            amount: 3,
            material_tier: Some(2),
        };
        // cost=100, tier=2 (1.10×), amount=3 → ceil(100*1.1*3) = 330 or 331
        // (float precision: 100*1.1*3 = 330.0000...003, ceil → 331)
        let c = complete_quest(&quest, 0, 100, 0, 5);
        assert_eq!(c.gold_reward, 331);
    }

    #[test]
    fn complete_potion_delivery() {
        let quest = HunterQuest {
            quest_type: HunterQuestType::Potion,
            target_id: 5,
            amount: 10,
            material_tier: Some(0),
        };
        // power=10 → cost=30, amount=10 → 300
        let c = complete_quest(&quest, 0, 0, 10, 2);
        assert_eq!(c.gold_reward, 300);
    }

    #[test]
    fn complete_loot_delivery() {
        let quest = HunterQuest {
            quest_type: HunterQuestType::Loot,
            target_id: 7,
            amount: 1,
            material_tier: None,
        };
        // level=15 → 15*100 = 1500
        let c = complete_quest(&quest, 15, 0, 0, 4);
        assert_eq!(c.gold_reward, 1500);
    }

    // --- Bestiary ---

    #[test]
    fn split_bestiary_by_location() {
        let monsters = vec![
            BestiaryEntry {
                id: 1,
                name: "Wolf".into(),
            },
            BestiaryEntry {
                id: 2,
                name: "Dragon".into(),
            },
            BestiaryEntry {
                id: 3,
                name: "Troll".into(),
            },
        ];
        let locs = vec![
            (1, "Altara".into()),
            (2, "Ardulith".into()),
            (3, "Altara".into()),
        ];
        let (a, b) = split_bestiary(monsters, &locs);
        assert_eq!(a.len(), 2);
        assert_eq!(b.len(), 1);
        assert_eq!(a[0].name, "Wolf");
        assert_eq!(a[1].name, "Troll");
        assert_eq!(b[0].name, "Dragon");
    }
}
