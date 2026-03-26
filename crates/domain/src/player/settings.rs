//! Player settings (UI preferences) — maps to the JSONB `settings` column.

use serde::{Deserialize, Serialize};

/// Typed player settings matching the known key set from the legacy
/// semicolon-delimited format.
///
/// Stored as JSONB in the `players.settings` column.
#[derive(Debug, Clone, Serialize, Deserialize, PartialEq, Eq)]
pub struct PlayerSettings {
    /// CSS theme file name (e.g. `"light.css"`).
    #[serde(default = "default_style")]
    pub style: String,

    /// Graphic layout (empty for text mode, `"layout1"` for graphic mode).
    #[serde(default)]
    pub graphic: String,

    /// Show graphical stat bars (`"Y"` / `"N"`).
    #[serde(default = "default_n")]
    pub graphbar: String,

    /// Forum category filtering preference.
    #[serde(default = "default_all")]
    pub forumcats: String,

    /// Auto-drink potions in combat (`"Y"` / `"N"`).
    #[serde(default = "default_n")]
    pub autodrink: String,

    /// Accept room invitations (`"Y"` / `"N"`).
    #[serde(default = "default_y")]
    pub rinvites: String,

    /// Show detailed battle log (`"Y"` / `"N"`).
    #[serde(default = "default_n")]
    pub battlelog: String,

    /// Old chat preference (optional, not present on all accounts).
    #[serde(default)]
    pub oldchat: String,
}

impl Default for PlayerSettings {
    fn default() -> Self {
        Self {
            style: "light.css".to_owned(),
            graphic: String::new(),
            graphbar: "N".to_owned(),
            forumcats: "All".to_owned(),
            autodrink: "N".to_owned(),
            rinvites: "Y".to_owned(),
            battlelog: "N".to_owned(),
            oldchat: String::new(),
        }
    }
}

fn default_style() -> String {
    "light.css".to_owned()
}
fn default_n() -> String {
    "N".to_owned()
}
fn default_y() -> String {
    "Y".to_owned()
}
fn default_all() -> String {
    "All".to_owned()
}

impl PlayerSettings {
    /// Parse settings from the legacy semicolon-delimited format.
    ///
    /// Format: `key:value;key:value;...`
    pub fn from_legacy(raw: &str) -> Self {
        let mut settings = Self::default();
        for field in raw.split(';') {
            let mut parts = field.splitn(2, ':');
            let key = match parts.next() {
                Some(k) if !k.is_empty() => k,
                _ => continue,
            };
            let value = parts.next().unwrap_or("");
            match key {
                "style" => value.clone_into(&mut settings.style),
                "graphic" => value.clone_into(&mut settings.graphic),
                "graphbar" => value.clone_into(&mut settings.graphbar),
                "forumcats" => value.clone_into(&mut settings.forumcats),
                "autodrink" => value.clone_into(&mut settings.autodrink),
                "rinvites" => value.clone_into(&mut settings.rinvites),
                "battlelog" => value.clone_into(&mut settings.battlelog),
                "oldchat" => value.clone_into(&mut settings.oldchat),
                _ => {
                    tracing::debug!(key, value, "unknown legacy settings key");
                }
            }
        }
        settings
    }

    /// Serialize back to the legacy semicolon-delimited format.
    ///
    /// Used during the migration window when the PHP side may still read
    /// the `settings_raw` column.
    pub fn to_legacy(&self) -> String {
        format!(
            "style:{};graphic:{};graphbar:{};forumcats:{};autodrink:{};rinvites:{};battlelog:{};",
            self.style,
            self.graphic,
            self.graphbar,
            self.forumcats,
            self.autodrink,
            self.rinvites,
            self.battlelog,
        )
    }

    /// Whether the player uses graphic (layout) mode.
    pub fn is_graphic_mode(&self) -> bool {
        !self.graphic.is_empty()
    }
}

#[cfg(test)]
mod tests {
    use super::*;

    #[test]
    fn parse_default_settings() {
        let raw =
            "style:light.css;graphic:;graphbar:N;forumcats:All;autodrink:N;rinvites:Y;battlelog:N;";
        let s = PlayerSettings::from_legacy(raw);
        assert_eq!(s.style, "light.css");
        assert_eq!(s.graphic, "");
        assert!(!s.is_graphic_mode());
    }

    #[test]
    fn parse_graphic_mode() {
        let raw = "style:light.css;graphic:layout1;graphbar:N;forumcats:All;autodrink:N;rinvites:Y;battlelog:N;";
        let s = PlayerSettings::from_legacy(raw);
        assert_eq!(s.graphic, "layout1");
        assert!(s.is_graphic_mode());
    }

    #[test]
    fn roundtrip_legacy() {
        let raw =
            "style:light.css;graphic:;graphbar:N;forumcats:All;autodrink:N;rinvites:Y;battlelog:N;";
        let s = PlayerSettings::from_legacy(raw);
        assert_eq!(s.to_legacy(), raw);
    }

    #[test]
    fn unknown_keys_are_ignored() {
        let raw = "style:dark.css;foo:bar;";
        let s = PlayerSettings::from_legacy(raw);
        assert_eq!(s.style, "dark.css");
    }

    #[test]
    fn empty_string_gives_defaults() {
        let s = PlayerSettings::from_legacy("");
        assert_eq!(s, PlayerSettings::default());
    }

    #[test]
    fn json_roundtrip() {
        let original = PlayerSettings::default();
        let json = serde_json::to_string(&original).unwrap();
        let parsed: PlayerSettings = serde_json::from_str(&json).unwrap();
        assert_eq!(parsed, original);
    }
}
