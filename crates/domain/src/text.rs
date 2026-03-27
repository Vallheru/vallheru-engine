//! Text-processing utilities shared across domain logic.

use rand::Rng;

/// Remove HTML tags from a string (equivalent to PHP `strip_tags`).
pub fn strip_tags(input: &str) -> String {
    let mut result = String::with_capacity(input.len());
    let mut in_tag = false;
    for ch in input.chars() {
        if ch == '<' {
            in_tag = true;
        } else if ch == '>' {
            in_tag = false;
        } else if !in_tag {
            result.push(ch);
        }
    }
    result
}

// =========================================================================
// BBCode processing
// =========================================================================

/// Convert BBCode-like markup to HTML.
///
/// When `is_chat` is true, `[center]`, `[quote]`, and `[color …]` tags are
/// stripped rather than converted, and chat-specific features (emotes, dice
/// rolls) are applied.
pub fn bbcode_to_html(text: &str, bad_words: &[String], is_chat: bool) -> String {
    let mut s = filter_bad_words(text, bad_words);

    // Escape HTML entities so user input cannot inject raw HTML.
    s = html_escape(&s);

    // Make URLs clickable.
    s = linkify(&s);

    // Basic BBCode tags shared between chat and non-chat contexts.
    let pairs: &[(&str, &str, &str, &str)] = &[
        ("[b]", "[/b]", "<b>", "</b>"),
        ("[i]", "[/i]", "<i>", "</i>"),
        ("[u]", "[/u]", "<u>", "</u>"),
    ];
    for &(open, close, html_open, html_close) in pairs {
        s = replace_bbcode_pair(&s, open, close, html_open, html_close);
    }

    if is_chat {
        // In chat mode, strip center / quote / color tags.
        s = s.replace("[center]", "");
        s = s.replace("[/center]", "");
        s = s.replace("[quote]", "");
        s = s.replace("[/quote]", "");
        // Strip any [color …] tags.
        strip_color_tags(&mut s);

        // Chat emotes: *text* → bold-italic.
        s = chat_emotes(&s);

        // Dice rolls: NkM±X
        s = dice_roll(&s);
    } else {
        s = replace_bbcode_pair(&s, "[center]", "[/center]", "<center>", "</center>");
        s = replace_bbcode_pair(&s, "[quote]", "[/quote]", "<br />Cytat:<br /><i>", "</i>");
        s = apply_color_tags(&s);
    }

    // Newlines → <br />.
    s = s.replace('\n', "<br />");

    // Emoticons.
    s = apply_emoticons(&s);

    s
}

/// Replace occurrences of a matched word in `text` with `[kwiatek]`.
fn filter_bad_words(text: &str, bad_words: &[String]) -> String {
    let mut result = text.to_owned();
    for word in bad_words {
        if word.is_empty() {
            continue;
        }
        // Build a regex pattern equivalent to PHP's `/\bWORD\b/i`.
        let pattern = format!(r"(?i)\b{}\b", regex::escape(word));
        if let Ok(re) = regex::Regex::new(&pattern) {
            result = re.replace_all(&result, "[kwiatek]").into_owned();
        }
    }
    result
}

/// Escape HTML special characters.
pub fn html_escape(input: &str) -> String {
    let mut out = String::with_capacity(input.len());
    for ch in input.chars() {
        match ch {
            '&' => out.push_str("&amp;"),
            '<' => out.push_str("&lt;"),
            '>' => out.push_str("&gt;"),
            '"' => out.push_str("&quot;"),
            '\'' => out.push_str("&#039;"),
            _ => out.push(ch),
        }
    }
    out
}

/// Simple URL auto-linking.
fn linkify(text: &str) -> String {
    // Match http(s) URLs.
    static RE: std::sync::LazyLock<regex::Regex> = std::sync::LazyLock::new(|| {
        regex::Regex::new(r"(?i)(https?://[^\s<>\[\]]+)").expect("linkify regex")
    });
    RE.replace_all(text, r#"<a href="$1" target="_blank">$1</a>"#)
        .into_owned()
}

/// Replace matched `[tag]…[/tag]` pairs with their HTML equivalents,
/// closing unclosed openers.
fn replace_bbcode_pair(
    text: &str,
    bb_open: &str,
    bb_close: &str,
    html_open: &str,
    html_close: &str,
) -> String {
    let mut s = text.replace(bb_open, html_open);
    s = s.replace(bb_close, html_close);
    // Close any unclosed openers.
    let opens = s.matches(html_open).count();
    let closes = s.matches(html_close).count();
    for _ in 0..opens.saturating_sub(closes) {
        s.push_str(html_close);
    }
    s
}

/// Strip `[color …]` and `[/color]` from text (chat mode).
fn strip_color_tags(s: &mut String) {
    // Remove opening [color …] tags.
    while let Some(start) = s.find("[color") {
        if let Some(end) = s[start..].find(']') {
            s.replace_range(start..=(start + end), "");
        } else {
            break;
        }
    }
    *s = s.replace("[/color]", "");
}

/// Convert `[color NAME]…[/color]` to `<span style="color: NAME;">…</span>`
/// (non-chat mode).
fn apply_color_tags(text: &str) -> String {
    static RE: std::sync::LazyLock<regex::Regex> = std::sync::LazyLock::new(|| {
        regex::Regex::new(r"(?i)\[color\s+([a-zA-Z0-9#]+)\]").expect("color regex")
    });
    let mut s = RE
        .replace_all(text, r#"<span style="color: $1;">"#)
        .into_owned();
    s = s.replace("[/color]", "</span>");
    // Close unclosed spans.
    let opens = s.matches("<span").count();
    let closes = s.matches("</span>").count();
    for _ in 0..opens.saturating_sub(closes) {
        s.push_str("</span>");
    }
    s
}

/// Convert `*text*` to `<i><b>text</b></i>` for chat emotes.
fn chat_emotes(text: &str) -> String {
    let mut result = String::with_capacity(text.len() + 32);
    let mut chars = text.char_indices().peekable();
    let bytes = text.as_bytes();

    while let Some(&(i, ch)) = chars.peek() {
        if ch == '*' {
            // Find closing *.
            if let Some(end) = text[i + 1..].find('*') {
                let inner = &text[i + 1..i + 1 + end];
                if !inner.is_empty() && !inner.contains('<') {
                    result.push_str("<i><b>");
                    result.push_str(inner);
                    result.push_str("</b></i>");
                    // Skip past closing *.
                    let skip_to = i + 1 + end + 1;
                    while chars.peek().is_some_and(|&(j, _)| j < skip_to) {
                        chars.next();
                    }
                    continue;
                }
            }
        }
        result.push(bytes[i] as char);
        chars.next();
    }
    result
}

/// Parse and resolve `NkM±X` dice roll expressions.
fn dice_roll(text: &str) -> String {
    static RE: std::sync::LazyLock<regex::Regex> = std::sync::LazyLock::new(|| {
        regex::Regex::new(r"(\d+)k(\d+)([+-]\d+)?").expect("dice regex")
    });
    RE.replace_all(text, |caps: &regex::Captures| {
        let count: u32 = caps[1].parse().unwrap_or(1).clamp(1, 1000);
        let sides: u32 = caps[2].parse().unwrap_or(1).clamp(1, 1000);
        let modifier: i64 = caps
            .get(3)
            .and_then(|m| m.as_str().parse().ok())
            .unwrap_or(0)
            .clamp(-1000, 1000);
        let mut rng = rand::thread_rng();
        let mut total: i64 = 0;
        for _ in 0..count {
            total += i64::from(rng.gen_range(1..=sides));
        }
        total += modifier;
        let expr = &caps[0];
        format!(r#"<span style="color:silver;">{expr} =&gt; {total}</span>"#)
    })
    .into_owned()
}

/// Replace common text emoticons with `<img>` tags.
fn apply_emoticons(text: &str) -> String {
    let mut s = text.to_owned();
    let pairs: &[(&str, &str, &str)] = &[
        (":)", "smile.gif", ":) - uśmiech"),
        (":D", "bigsmile.gif", ":D - śmiech"),
        (":(", "frown.gif", ":( - smutny"),
        (":o", "suprised.gif", ":o - zdziwiony"),
        (";(", "cry.gif", ";( - płacze"),
        (":]", "cheesy.png", ":] - wesoły"),
        (":P", "tongue.gif", ":P - pokazuje język"),
        (":~", "sliniak-1.gif", ":~ - ślini się"),
    ];
    for &(token, file, title) in pairs {
        let replacement = format!(r#"<img src="/images/{file}" title="{title}" />"#);
        s = s.replace(token, &replacement);
    }
    s
}

// =========================================================================
// Chat-specific helpers
// =========================================================================

/// Build the pre-rendered HTML author label for a chat message.
///
/// Includes rank colour, tribe prefix/suffix, and a link to the player profile.
pub fn chat_author_label(
    player_id: i64,
    player_name: &str,
    rank: &str,
    tribe_prefix: &str,
    tribe_suffix: &str,
) -> String {
    let styled_name = match rank {
        "Admin" => format!(
            r#"<span style="color: #0066cc;">{}</span>"#,
            html_escape(player_name)
        ),
        "Staff" => format!(
            r#"<span style="color: #00ff00;">{}</span>"#,
            html_escape(player_name)
        ),
        _ => html_escape(player_name),
    };
    let prefix = if tribe_prefix.is_empty() {
        String::new()
    } else {
        format!("{tribe_prefix} ")
    };
    let suffix = if tribe_suffix.is_empty() {
        String::new()
    } else {
        format!(" {tribe_suffix}")
    };
    format!(r#"{prefix}<a href="/view/{player_id}">{styled_name}</a>{suffix}"#)
}

/// Apply the `@me` emote replacement for chat messages.
///
/// Replaces `@me` with a link to the sender's profile.
pub fn apply_emote(text: &str, player_id: i64, player_name: &str) -> (String, bool) {
    if text.contains("@me") {
        let replacement = format!(
            r#"<a href="/view/{player_id}" target="_parent">{}</a>"#,
            html_escape(player_name),
        );
        (text.replace("@me", &replacement), true)
    } else {
        (text.to_owned(), false)
    }
}

// =========================================================================
// Inn throw/shoot responses
// =========================================================================

/// Pre-defined NPC targets for throw/shoot actions in the inn.
const INN_TARGETS: &[(&str, &str)] = &[
    ("karczmarza", "Karczmarz"),
    ("barnab", "Barnaba"),
    ("barda", "Bard"),
];

/// Check if a chat message is a throw/shoot action targeting an inn NPC.
/// Returns `(npc_display_name, response_html)` if matched.
pub fn check_inn_action(message: &str, player_name: &str) -> Option<(String, String)> {
    let lower = message.to_lowercase();
    let is_throw = lower.contains("*rzuca");
    let is_shoot = lower.contains("*strzela do");
    if !is_throw && !is_shoot {
        return None;
    }

    for &(keyword, display) in INN_TARGETS {
        if lower.contains(keyword) {
            let escaped = html_escape(player_name);
            let answers = [
                "Ała, za co?".to_owned(),
                format!(
                    "{escaped} jednym niezwykle celnym trafieniem powala cel na ziemię. {escaped} dostaje gazylion PD i jeszcze więcej do umiejętności Powalanie."
                ),
                format!("*{escaped} nie trafia celu.* Buahahahahaha"),
                format!("*{display} zgrabnym ruchem unika trafienia.*"),
                format!("*{display} odpowiada ogniem ciągłym.*"),
            ];
            let idx = rand::thread_rng().gen_range(0..answers.len());
            let target = format!("<i>{display}</i>");
            return Some((target, answers[idx].clone()));
        }
    }
    None
}

/// Rough HTML-to-BBCode conversion for quoting forum posts.
///
/// This reverses the most common `BBCode` tags. It is not a full converter
/// — just enough to produce a reasonable quote body.
pub fn html_to_bbcode(html: &str) -> String {
    let mut s = html.to_owned();
    // Bold
    s = s.replace("<b>", "[b]").replace("</b>", "[/b]");
    s = s.replace("<strong>", "[b]").replace("</strong>", "[/b]");
    // Italic
    s = s.replace("<i>", "[i]").replace("</i>", "[/i]");
    s = s.replace("<em>", "[i]").replace("</em>", "[/i]");
    // Underline
    s = s.replace("<u>", "[u]").replace("</u>", "[/u]");
    // Linebreaks
    s = s
        .replace("<br>", "\n")
        .replace("<br />", "\n")
        .replace("<br/>", "\n");
    // Strip remaining HTML tags.
    strip_tags(&s)
}

#[cfg(test)]
mod tests {
    use super::*;

    #[test]
    fn strip_tags_removes_html() {
        assert_eq!(strip_tags("<b>bold</b>"), "bold");
        assert_eq!(strip_tags("no tags"), "no tags");
        assert_eq!(strip_tags("<script>alert('xss')</script>"), "alert('xss')");
        assert_eq!(strip_tags(""), "");
    }

    #[test]
    fn html_escape_special_chars() {
        assert_eq!(html_escape("<>&\"'"), "&lt;&gt;&amp;&quot;&#039;");
    }

    #[test]
    fn bbcode_basic_tags() {
        let result = bbcode_to_html("[b]bold[/b] [i]italic[/i]", &[], false);
        assert!(result.contains("<b>bold</b>"));
        assert!(result.contains("<i>italic</i>"));
    }

    #[test]
    fn bbcode_unclosed_tags_are_closed() {
        let result = bbcode_to_html("[b]unclosed", &[], false);
        assert!(result.contains("<b>unclosed</b>"));
    }

    #[test]
    fn bbcode_chat_mode_strips_quote_and_center() {
        let result = bbcode_to_html("[center]text[/center][quote]q[/quote]", &[], true);
        assert!(!result.contains("<center>"));
        assert!(!result.contains("Cytat"));
        assert!(result.contains("text"));
    }

    #[test]
    fn bad_word_filter() {
        let words = vec!["badword".to_owned()];
        let result = bbcode_to_html("this is badword here", &words, false);
        assert!(result.contains("[kwiatek]"));
        assert!(!result.contains("badword"));
    }

    #[test]
    fn emoticons_are_replaced() {
        let result = bbcode_to_html(":)", &[], false);
        assert!(result.contains("smile.gif"));
    }

    #[test]
    fn chat_emotes_wrap_in_bold_italic() {
        let result = chat_emotes("*throws a mug*");
        assert_eq!(result, "<i><b>throws a mug</b></i>");
    }

    #[test]
    fn dice_roll_is_resolved() {
        let result = dice_roll("1k6");
        assert!(result.contains("1k6 =&gt;"));
        assert!(result.contains("color:silver"));
    }

    #[test]
    fn author_label_admin_has_colour() {
        let label = chat_author_label(1, "TestAdmin", "Admin", "", "");
        assert!(label.contains("#0066cc"));
        assert!(label.contains("/view/1"));
    }

    #[test]
    fn author_label_player_no_colour() {
        let label = chat_author_label(2, "Player", "Gracz", "[T]", "[/T]");
        assert!(!label.contains("color:"));
        assert!(label.contains("[T]"));
        assert!(label.contains("[/T]"));
    }

    #[test]
    fn emote_replacement() {
        let (text, is_emote) = apply_emote("@me dances", 5, "Hero");
        assert!(is_emote);
        assert!(text.contains("/view/5"));
        assert!(text.contains("Hero"));
        assert!(!text.contains("@me"));
    }

    #[test]
    fn color_tags_non_chat() {
        let result = bbcode_to_html("[color red]text[/color]", &[], false);
        assert!(result.contains(r"color: red;"));
        assert!(result.contains("</span>"));
    }

    #[test]
    fn color_tags_stripped_in_chat() {
        let result = bbcode_to_html("[color red]text[/color]", &[], true);
        assert!(!result.contains("color:"));
        assert!(result.contains("text"));
    }

    #[test]
    fn linkify_makes_urls_clickable() {
        let result = linkify("visit https://example.com now");
        assert!(result.contains(r#"<a href="https://example.com""#));
    }

    #[test]
    fn inn_action_returns_response() {
        let result = check_inn_action("*rzuca poduszką w karczmarza*", "Hero");
        assert!(result.is_some());
        let (target, _response) = result.unwrap();
        assert!(target.contains("Karczmarz"));
    }

    #[test]
    fn inn_action_no_match_returns_none() {
        let result = check_inn_action("hello world", "Hero");
        assert!(result.is_none());
    }
}
