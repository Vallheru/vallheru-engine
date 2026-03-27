//! Chat domain logic: validation, bot behaviour, message classification.

use rand::Rng;

// =========================================================================
// Innkeeper bot (ELIZA-style pattern matcher)
// =========================================================================

/// Pattern-response pairs for the innkeeper bot.
///
/// Index 0 and 1 are special "give item" patterns handled differently.
/// Index 2 is "any news?" (pulls a random event).
/// The rest are generic ELIZA-style substitutions.
const BOT_PATTERNS: &[&str] = &[
    r".+ dla [0-9]+$",      // 0: give item to player
    r".+ dla wszystkich!$", // 1: give item to everyone
    r"jakieś wieści\?",     // 2: any news?
    r"potrzebuję .*",       // 3
    r"jestem .*",           // 4
    r"witaj",               // 5
    r"czuję się .*",        // 6
    r"jesteś .*",           // 7
    r"nie mogę .*",         // 8
    r"dlaczego .*",         // 9
    r"dawaj .*",            // 10
    r"daj .*",              // 11
    r"o której.*reset\?",   // 12
    r"co sądzisz o .*",     // 13
    r"tak",                 // 14
];

/// Responses per pattern index. Each inner slice has multiple variants for
/// random selection. `%1` is replaced with the captured subject, `%2` with
/// the sender name, `%3` with the target player name.
const BOT_RESPONSES: &[&[&str]] = &[
    // 0: give to player
    &["Proszę %3 oto%1od %2."],
    // 1: give to everyone
    &["Uwaga %2 stawia wszystkim%1!"],
    // 2: news
    &[
        "Oczywiście! %1",
        "Nic ciekawego się nie wydarzyło ostatnio.",
    ],
    // 3: need
    &[
        "Dlaczego potrzebujesz %1?",
        "Jesteś pewien że potrzebujesz %1?",
        "Próbowałeś szukać %1 gdzieś indziej?",
    ],
    // 4: I am
    &[
        "Dlaczego uważasz że jesteś %1?",
        "Nie martw się, to przejdzie.",
        "Jesteś pewny?",
    ],
    // 5: hello
    &[
        "Miło znów ciebie widzieć.",
        "Witaj podróżniku. Co podać?",
        "A, znowu ty.",
    ],
    // 6: I feel
    &[
        "Dlaczego czujesz się %1?",
        "Przeszkadza ci to?",
        "Może chcesz coś mocniejszego dlatego że czujesz się %1?",
    ],
    // 7: you are
    &[
        "Dlaczego uważasz że jestem %1?",
        "A może tylko tak ci się wydaje?",
        "Może i racja.",
    ],
    // 8: I can't
    &[
        "Dlaczego nie możesz %1?",
        "Coś ci w tym przeszkadza?",
        "Na pewno próbowałeś %1?",
    ],
    // 9: why
    &[
        "A dlaczego uważasz że nie?",
        "Nie mam pojęcia dlaczego %1",
        "Pewnie dlatego że to niemożliwe.",
    ],
    // 10: give me (rude)
    &[
        "Może tak trochę grzeczniej?",
        "Nie mam %1, jest tylko piwo.",
        "A masz pieniądze na %1?",
    ],
    // 11: give me (polite)
    &["Dam %1 jak zapłacisz", "Zaraz podam.", "I co jeszcze?"],
    // 12: reset times
    &["Resety są o godzinach 12, 14, 16, 18, 20, 22, 24."],
    // 13: what do you think
    &[
        "Nie mam zdania.",
        "Dlaczego pytasz mnie o %1?",
        "Ciężko powiedzieć.",
    ],
    // 14: yes
    &["Cieszę się że się zgadzamy.", "Dlaczego tak uważasz?"],
];

/// Fallback responses when no pattern matched.
const BOT_FALLBACK: &[&str] = &[
    "Nie rozumiem",
    "Coś chciałeś?",
    "Barnaba pokaż temu klientowi drzwi!",
    "Hmm w jakim to języku?",
    "Pytania są tendencyjne",
    "Do mnie mówisz?",
];

/// The name prefix that triggers the bot.
pub const BOT_TRIGGER: &str = "Karczmarzu";

/// Check whether a message is addressed to the innkeeper bot.
pub fn is_bot_message(message: &str) -> bool {
    message.starts_with(BOT_TRIGGER)
}

/// Generate a bot response for a message addressed to the innkeeper.
///
/// Returns `None` if the message is not addressed to the bot.
/// `sender_name` is the player sending the message.
/// `target_name` is resolved from the trailing player ID for pattern 0, or
/// `None` if not applicable.
/// `event_text` is a random event blurb for pattern 2.
pub fn bot_response(
    message: &str,
    sender_name: &str,
    target_name: Option<&str>,
    event_text: Option<&str>,
) -> Option<String> {
    if !is_bot_message(message) {
        return None;
    }

    let mut rng = rand::thread_rng();

    // Find matching pattern.
    let mut matched_idx: Option<usize> = None;
    for (i, pattern) in BOT_PATTERNS.iter().enumerate() {
        if let Ok(re) = regex::Regex::new(pattern) {
            if re.is_match(message) {
                matched_idx = Some(i);
                break;
            }
        }
    }

    let answer = match matched_idx {
        Some(0) => {
            // "give to player" — extract item words and target name.
            let words: Vec<&str> = message.split_whitespace().collect();
            let target = target_name.unwrap_or(sender_name);
            let item_end = words.len().saturating_sub(2);
            let item: String = words[1..item_end.max(1)].join(" ");
            BOT_RESPONSES[0][0]
                .replace("%3", target)
                .replace("%2", sender_name)
                .replace("%1", &format!(" {item} "))
        }
        Some(1) => {
            // "give to everyone"
            let words: Vec<&str> = message.split_whitespace().collect();
            let item_end = words.len().saturating_sub(2);
            let item: String = words[1..item_end.max(1)].join(" ");
            BOT_RESPONSES[1][0]
                .replace("%2", sender_name)
                .replace("%1", &format!(" {item} "))
        }
        Some(2) => {
            // "any news?"
            if let Some(evt) = event_text {
                BOT_RESPONSES[2][0].replace("%1", evt)
            } else {
                BOT_RESPONSES[2][1].to_owned()
            }
        }
        Some(idx) if idx < BOT_RESPONSES.len() => {
            // Generic pattern — extract subject after the matched keyword.
            let pattern_text = BOT_PATTERNS[idx]
                .replace(['.', '*'], "")
                .to_lowercase();
            let lower = message.replace(BOT_TRIGGER, "").to_lowercase();
            let subject = lower.replace(&pattern_text, "").trim().to_owned();

            let responses = BOT_RESPONSES[idx];
            let pick = rng.gen_range(0..responses.len());
            responses[pick].replace("%1", &subject)
        }
        _ => {
            // No pattern matched — use fallback.
            let pick = rng.gen_range(0..BOT_FALLBACK.len());
            BOT_FALLBACK[pick].to_owned()
        }
    };

    Some(answer)
}

// =========================================================================
// Chat validation
// =========================================================================

/// Maximum message length (in chars) after `BBCode` processing.
pub const MAX_MESSAGE_LENGTH: usize = 2000;

/// Maximum number of messages shown per page / fetch.
pub const MAX_CHAT_LENGTH: i32 = 150;

/// Default number of messages per page.
pub const DEFAULT_CHAT_LENGTH: i32 = 25;

/// Step size for more/less paging.
pub const CHAT_LENGTH_STEP: i32 = 25;

/// Maximum number of whisper tabs.
pub const MAX_WHISPER_TABS: usize = 5;

/// Ranks that have chat admin privileges.
pub fn is_chat_admin(rank: &str) -> bool {
    matches!(rank, "Admin" | "Staff" | "Karczmarka")
}

/// Validate a chat message length.
pub fn validate_message(body: &str) -> Result<(), &'static str> {
    let trimmed = body.trim();
    if trimmed.is_empty() {
        return Err("Wiadomość nie może być pusta.");
    }
    if trimmed.len() > MAX_MESSAGE_LENGTH {
        return Err("Wiadomość jest zbyt długa.");
    }
    Ok(())
}

/// Clamp chat page length to valid range.
pub fn clamp_chat_length(current: i32, delta: i32) -> i32 {
    (current + delta).clamp(DEFAULT_CHAT_LENGTH, MAX_CHAT_LENGTH)
}

/// Classify a raw `msg` field value as either public or a whisper.
///
/// The PHP convention is `RECIPIENT_ID=text` for whispers.
pub fn classify_message(raw: &str) -> MessageTarget {
    if let Some(eq_pos) = raw.find('=') {
        let prefix = &raw[..eq_pos];
        if let Ok(id) = prefix.parse::<i64>() {
            if id > 0 {
                let body = &raw[eq_pos + 1..];
                return MessageTarget::Whisper {
                    recipient_id: id,
                    body: body.to_owned(),
                };
            }
        }
    }
    MessageTarget::Public {
        body: raw.to_owned(),
    }
}

/// Where a chat message should be delivered.
#[derive(Debug, Clone, PartialEq, Eq)]
pub enum MessageTarget {
    Public { body: String },
    Whisper { recipient_id: i64, body: String },
}

#[cfg(test)]
mod tests {
    use super::*;

    #[test]
    fn bot_trigger_detection() {
        assert!(is_bot_message("Karczmarzu witaj"));
        assert!(!is_bot_message("hello Karczmarzu"));
    }

    #[test]
    fn bot_hello_response() {
        let resp = bot_response("Karczmarzu witaj", "Hero", None, None);
        assert!(resp.is_some());
        let text = resp.unwrap();
        assert!(
            text == "Miło znów ciebie widzieć."
                || text == "Witaj podróżniku. Co podać?"
                || text == "A, znowu ty."
        );
    }

    #[test]
    fn bot_fallback_for_unknown() {
        let resp = bot_response("Karczmarzu blublabla", "Hero", None, None);
        assert!(resp.is_some());
    }

    #[test]
    fn classify_public_message() {
        assert_eq!(
            classify_message("hello world"),
            MessageTarget::Public {
                body: "hello world".to_owned(),
            }
        );
    }

    #[test]
    fn classify_whisper_message() {
        assert_eq!(
            classify_message("42=secret message"),
            MessageTarget::Whisper {
                recipient_id: 42,
                body: "secret message".to_owned(),
            }
        );
    }

    #[test]
    fn classify_message_with_zero_id_is_public() {
        assert_eq!(
            classify_message("0=still public"),
            MessageTarget::Public {
                body: "0=still public".to_owned(),
            }
        );
    }

    #[test]
    fn clamp_chat_length_bounds() {
        assert_eq!(clamp_chat_length(25, 25), 50);
        assert_eq!(clamp_chat_length(150, 25), 150);
        assert_eq!(clamp_chat_length(25, -25), 25);
    }

    #[test]
    fn validate_empty_message() {
        assert!(validate_message("").is_err());
        assert!(validate_message("  ").is_err());
    }

    #[test]
    fn validate_ok_message() {
        assert!(validate_message("hello").is_ok());
    }

    #[test]
    fn is_chat_admin_checks() {
        assert!(is_chat_admin("Admin"));
        assert!(is_chat_admin("Staff"));
        assert!(is_chat_admin("Karczmarka"));
        assert!(!is_chat_admin("Gracz"));
    }
}
