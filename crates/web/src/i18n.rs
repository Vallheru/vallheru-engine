//! Localization catalog loader.
//!
//! Replaces the PHP `define()` constants in `languages/pl/*.php` with
//! structured TOML catalogs loaded at startup. Strings are keyed by
//! module and accessible to both Rust handlers and templates.

use std::collections::HashMap;
use std::sync::Arc;

/// A loaded localization catalog — one per supported locale.
///
/// Strings are grouped by module (e.g. `"head"`, `"city"`, `"battle"`)
/// and keyed by a string constant name matching the PHP `define()` name.
#[derive(Debug, Clone)]
pub struct Catalog {
    locale: String,
    modules: Arc<HashMap<String, HashMap<String, String>>>,
}

impl Catalog {
    /// Create an empty catalog for the given locale.
    pub fn empty(locale: impl Into<String>) -> Self {
        Self {
            locale: locale.into(),
            modules: Arc::new(HashMap::new()),
        }
    }

    /// Load a catalog from TOML source text.
    ///
    /// Expected format:
    /// ```toml
    /// [head]
    /// HEALTH_PTS = "Punkty życia"
    /// MANA_PTS = "Mana"
    ///
    /// [city]
    /// NO_CITY = "Nie znajdujesz się w mieście"
    /// ```
    ///
    /// # Errors
    ///
    /// Returns an error if the TOML is malformed or values are not strings.
    pub fn from_toml(locale: impl Into<String>, source: &str) -> Result<Self, CatalogError> {
        let table: toml::Table =
            toml::from_str(source).map_err(|e| CatalogError::Parse(e.to_string()))?;

        let mut modules: HashMap<String, HashMap<String, String>> = HashMap::new();

        for (module_key, value) in table {
            let toml::Value::Table(entries) = value else {
                return Err(CatalogError::Structure(format!(
                    "expected table for module '{module_key}', got {value}"
                )));
            };

            let mut map = HashMap::with_capacity(entries.len());
            for (key, val) in entries {
                let toml::Value::String(s) = val else {
                    return Err(CatalogError::Structure(format!(
                        "expected string for {module_key}.{key}"
                    )));
                };
                map.insert(key, s);
            }
            modules.insert(module_key, map);
        }

        Ok(Self {
            locale: locale.into(),
            modules: Arc::new(modules),
        })
    }

    /// Load a catalog from the embedded `i18n/` directory.
    ///
    /// Looks for a file named `{locale}.toml` in the embedded i18n dir.
    ///
    /// # Errors
    ///
    /// Returns an error if the file is not found or fails to parse.
    pub fn load_embedded(locale: &str) -> Result<Self, CatalogError> {
        let filename = format!("{locale}.toml");
        let file = crate::assets::I18N_DIR.get_file(&filename).ok_or_else(|| {
            CatalogError::NotFound(format!("i18n/{filename} not found in embedded assets"))
        })?;

        let source = file
            .contents_utf8()
            .ok_or_else(|| CatalogError::Parse(format!("i18n/{filename} is not valid UTF-8")))?;

        Self::from_toml(locale, source)
    }

    /// Look up a translated string by module and key.
    ///
    /// Returns `None` if the module or key is missing. In development,
    /// callers should log a warning so missing translations are visible.
    pub fn get(&self, module: &str, key: &str) -> Option<&str> {
        self.modules
            .get(module)
            .and_then(|m| m.get(key))
            .map(String::as_str)
    }

    /// Look up a translated string, returning the key itself as fallback.
    ///
    /// This is useful in templates where a missing translation should
    /// still render something visible rather than nothing.
    pub fn get_or_key<'a>(&'a self, module: &str, key: &'a str) -> &'a str {
        self.get(module, key).unwrap_or(key)
    }

    /// The locale code for this catalog (e.g. `"pl"`).
    pub fn locale(&self) -> &str {
        &self.locale
    }

    /// Number of modules loaded.
    pub fn module_count(&self) -> usize {
        self.modules.len()
    }

    /// Total number of strings across all modules.
    pub fn string_count(&self) -> usize {
        self.modules.values().map(HashMap::len).sum()
    }
}

/// Errors from loading a localization catalog.
#[derive(Debug, thiserror::Error)]
pub enum CatalogError {
    #[error("TOML parse error: {0}")]
    Parse(String),
    #[error("unexpected structure: {0}")]
    Structure(String),
    #[error("catalog not found: {0}")]
    NotFound(String),
}

/// minijinja template function: `t(module, key)`.
///
/// Looks up a translation string from the catalog. Returns the key
/// wrapped in `[MISSING: module.key]` if the translation is not found,
/// making missing translations visible during development.
pub fn make_translate_fn(catalog: Catalog) -> impl Fn(String, String) -> String + Send + Sync {
    move |module: String, key: String| {
        if let Some(value) = catalog.get(&module, &key) {
            value.to_owned()
        } else {
            tracing::warn!(module = %module, key = %key, "missing translation");
            format!("[MISSING: {module}.{key}]")
        }
    }
}

#[cfg(test)]
mod tests {
    use super::*;

    const SAMPLE_TOML: &str = r#"
[head]
HEALTH_PTS = "Punkty życia"
MANA_PTS = "Mana"
ENERGY_PTS = "Energia"

[city]
NO_CITY = "Nie znajdujesz się w mieście"
BATTLE_ARENA = "Arena Walk"
"#;

    #[test]
    fn load_from_toml() {
        let cat = Catalog::from_toml("pl", SAMPLE_TOML).unwrap();
        assert_eq!(cat.locale(), "pl");
        assert_eq!(cat.module_count(), 2);
        assert_eq!(cat.string_count(), 5);
    }

    #[test]
    fn lookup_existing_key() {
        let cat = Catalog::from_toml("pl", SAMPLE_TOML).unwrap();
        assert_eq!(cat.get("head", "HEALTH_PTS"), Some("Punkty życia"));
        assert_eq!(cat.get("city", "BATTLE_ARENA"), Some("Arena Walk"));
    }

    #[test]
    fn lookup_missing_key_returns_none() {
        let cat = Catalog::from_toml("pl", SAMPLE_TOML).unwrap();
        assert!(cat.get("head", "NONEXISTENT").is_none());
        assert!(cat.get("nonmodule", "HEALTH_PTS").is_none());
    }

    #[test]
    fn get_or_key_fallback() {
        let cat = Catalog::from_toml("pl", SAMPLE_TOML).unwrap();
        assert_eq!(cat.get_or_key("head", "HEALTH_PTS"), "Punkty życia");
        assert_eq!(cat.get_or_key("head", "MISSING"), "MISSING");
    }

    #[test]
    fn empty_catalog() {
        let cat = Catalog::empty("en");
        assert_eq!(cat.locale(), "en");
        assert_eq!(cat.module_count(), 0);
        assert_eq!(cat.string_count(), 0);
    }

    #[test]
    fn reject_non_string_values() {
        let bad = "[head]\nFOO = 42\n";
        assert!(Catalog::from_toml("pl", bad).is_err());
    }

    #[test]
    fn reject_non_table_module() {
        let bad = "head = \"not a table\"\n";
        assert!(Catalog::from_toml("pl", bad).is_err());
    }

    #[test]
    fn load_embedded_polish_catalog() {
        let cat = Catalog::load_embedded("pl").unwrap();
        assert_eq!(cat.locale(), "pl");
        assert!(cat.module_count() > 0);
        assert!(cat.get("head", "E_ERRORS").is_some());
    }

    #[test]
    fn load_embedded_missing_locale() {
        assert!(Catalog::load_embedded("xx").is_err());
    }

    #[test]
    fn translate_fn_returns_value() {
        let cat = Catalog::from_toml("pl", SAMPLE_TOML).unwrap();
        let t = make_translate_fn(cat);
        assert_eq!(t("head".into(), "HEALTH_PTS".into()), "Punkty życia");
    }

    #[test]
    fn translate_fn_missing_returns_marker() {
        let cat = Catalog::from_toml("pl", SAMPLE_TOML).unwrap();
        let t = make_translate_fn(cat);
        let result = t("head".into(), "MISSING".into());
        assert!(result.contains("[MISSING:"));
        assert!(result.contains("head.MISSING"));
    }
}
