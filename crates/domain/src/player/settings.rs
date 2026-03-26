//! Player settings (UI preferences) — maps to the JSONB `settings` column.

use serde::{Deserialize, Serialize};

/// Typed player settings.
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
    /// Build settings for a newly created player.
    ///
    /// `game_type` is `"T"` for text mode, anything else for graphic mode.
    pub fn for_new_player(game_type: &str) -> Self {
        let mut settings = Self::default();
        if game_type != "T" {
            "layout1".clone_into(&mut settings.graphic);
        }
        settings
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
    fn default_settings_values() {
        let s = PlayerSettings::default();
        assert_eq!(s.style, "light.css");
        assert_eq!(s.graphic, "");
        assert_eq!(s.graphbar, "N");
        assert_eq!(s.forumcats, "All");
        assert!(!s.is_graphic_mode());
    }

    #[test]
    fn new_player_text_mode() {
        let s = PlayerSettings::for_new_player("T");
        assert_eq!(s.graphic, "");
        assert!(!s.is_graphic_mode());
    }

    #[test]
    fn new_player_graphic_mode() {
        let s = PlayerSettings::for_new_player("G");
        assert_eq!(s.graphic, "layout1");
        assert!(s.is_graphic_mode());
    }

    #[test]
    fn json_roundtrip() {
        let original = PlayerSettings::default();
        let json = serde_json::to_string(&original).unwrap();
        let parsed: PlayerSettings = serde_json::from_str(&json).unwrap();
        assert_eq!(parsed, original);
    }

    #[test]
    fn json_with_missing_fields_gets_defaults() {
        let json = r#"{"style":"dark.css"}"#;
        let parsed: PlayerSettings = serde_json::from_str(json).unwrap();
        assert_eq!(parsed.style, "dark.css");
        assert_eq!(parsed.graphbar, "N");
        assert_eq!(parsed.rinvites, "Y");
    }
}
