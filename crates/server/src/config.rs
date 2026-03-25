use serde::Deserialize;
use std::net::SocketAddr;

/// Top-level application configuration.
///
/// Loaded from environment variables (prefixed `VALLHERU_`) with optional
/// TOML file override. Missing required values cause a fast startup failure.
#[derive(Debug, Deserialize)]
pub struct AppConfig {
    pub server: ServerConfig,
    pub database: DatabaseConfig,
    pub game: GameConfig,
    pub session: SessionConfig,
}

#[derive(Debug, Deserialize)]
pub struct ServerConfig {
    /// Socket address the HTTP server binds to (e.g. `0.0.0.0:3000`).
    #[serde(default = "default_bind")]
    pub bind: SocketAddr,
}

#[derive(Debug, Deserialize)]
pub struct DatabaseConfig {
    /// Postgres connection string, e.g.
    /// `postgres://user:pass@localhost:5432/vallheru`
    pub url: String,

    /// Legacy database connection string for reconciliation, e.g.
    /// `mysql://user:pass@localhost:3306/vallheru`
    #[serde(default)]
    pub legacy_url: Option<String>,

    /// Maximum connections in the pool.
    #[serde(default = "default_max_connections")]
    pub max_connections: u32,
}

#[derive(Debug, Deserialize)]
pub struct GameConfig {
    /// Display name of the game instance.
    pub name: String,

    /// Contact email shown to players.
    pub email: String,

    /// Public base URL (no trailing slash).
    pub base_url: String,

    /// Admin display name.
    pub admin_name: String,

    /// Admin contact email.
    pub admin_email: String,

    /// Maximum registered players (0 = unlimited).
    #[serde(default)]
    pub player_limit: u32,

    /// Default locale code.
    #[serde(default = "default_lang")]
    pub lang: String,
}

#[derive(Debug, Deserialize)]
pub struct SessionConfig {
    /// Secret used for signing session cookies. Must be at least 64 bytes.
    pub secret: String,
}

fn default_bind() -> SocketAddr {
    ([0, 0, 0, 0], 3000).into()
}

fn default_max_connections() -> u32 {
    10
}

fn default_lang() -> String {
    "pl".to_owned()
}

impl AppConfig {
    /// Load config from an optional TOML file, then overlay environment
    /// variables. Environment variables use the prefix `VALLHERU_` with
    /// double-underscore as section separator, e.g.
    /// `VALLHERU_DATABASE__URL=postgres://...`.
    ///
    /// # Errors
    ///
    /// Returns an error if required fields are missing or values are invalid.
    pub fn load(config_path: Option<&std::path::Path>) -> anyhow::Result<Self> {
        // Start with an optional TOML file.
        let toml_str = match config_path {
            Some(path) => match std::fs::read_to_string(path) {
                Ok(s) => s,
                Err(e) if e.kind() == std::io::ErrorKind::NotFound => {
                    tracing::warn!(?path, "config file not found, using env-only config");
                    String::new()
                }
                Err(e) => return Err(e.into()),
            },
            None => String::new(),
        };

        let mut config: toml::Value = if toml_str.is_empty() {
            toml::Value::Table(toml::map::Map::new())
        } else {
            toml::from_str(&toml_str)?
        };

        // Overlay environment variables with VALLHERU_ prefix.
        overlay_env(&mut config);

        let cfg: AppConfig = config
            .try_into()
            .map_err(|e| anyhow::anyhow!("configuration error: {e}"))?;

        cfg.validate()?;

        Ok(cfg)
    }

    fn validate(&self) -> anyhow::Result<()> {
        if self.database.url.is_empty() {
            anyhow::bail!("database.url must not be empty");
        }
        if self.session.secret.len() < 64 {
            anyhow::bail!("session.secret must be at least 64 bytes");
        }
        if self.game.name.is_empty() {
            anyhow::bail!("game.name must not be empty");
        }
        Ok(())
    }

    /// Produce a redacted summary suitable for startup logging.
    pub fn log_summary(&self) {
        tracing::info!(
            bind = %self.server.bind,
            db_url = mask_url(&self.database.url),
            db_max_conn = self.database.max_connections,
            game = %self.game.name,
            lang = %self.game.lang,
            "loaded configuration"
        );
    }
}

/// Overlay VALLHERU_* env vars into a TOML table value.
/// `VALLHERU_SECTION__KEY=val` → `config["section"]["key"] = val`.
fn overlay_env(config: &mut toml::Value) {
    let Some(table) = config.as_table_mut() else {
        return;
    };

    for (key, val) in std::env::vars() {
        let Some(rest) = key.strip_prefix("VALLHERU_") else {
            continue;
        };
        let parts: Vec<&str> = rest.split("__").collect();
        if parts.len() == 2 {
            let section = parts[0].to_lowercase();
            let field = parts[1].to_lowercase();
            let section_table = table
                .entry(&section)
                .or_insert_with(|| toml::Value::Table(toml::map::Map::new()));
            if let Some(t) = section_table.as_table_mut() {
                // Try to parse as integer first, then fall back to string.
                let toml_val = if let Ok(n) = val.parse::<i64>() {
                    toml::Value::Integer(n)
                } else {
                    toml::Value::String(val)
                };
                t.insert(field, toml_val);
            }
        }
    }
}

/// Mask credentials in a database URL for safe logging.
fn mask_url(url: &str) -> String {
    // Replace password portion between :// user:PASS@ with ***
    if let Some(at) = url.find('@') {
        if let Some(scheme_end) = url.find("://") {
            let user_start = scheme_end + 3;
            if let Some(colon) = url[user_start..at].find(':') {
                let mask_start = user_start + colon + 1;
                return format!("{}***{}", &url[..mask_start], &url[at..]);
            }
        }
    }
    url.to_owned()
}

#[cfg(test)]
mod tests {
    use super::*;

    #[test]
    fn mask_url_hides_password() {
        let url = "postgres://admin:supersecret@localhost:5432/vallheru";
        assert_eq!(
            mask_url(url),
            "postgres://admin:***@localhost:5432/vallheru"
        );
    }

    #[test]
    fn mask_url_no_password() {
        let url = "postgres://localhost:5432/vallheru";
        assert_eq!(mask_url(url), url);
    }

    #[test]
    fn load_from_toml_string() {
        let toml_str = r#"
[server]
bind = "127.0.0.1:8080"

[database]
url = "postgres://user:pass@localhost:5432/vallheru"
max_connections = 5

[game]
name = "Test Vallheru"
email = "test@example.com"
base_url = "http://localhost:8080"
admin_name = "Admin"
admin_email = "admin@example.com"
player_limit = 100

[session]
secret = "aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa"
"#;
        let config: toml::Value = toml::from_str(toml_str).unwrap();
        let cfg: AppConfig = config.try_into().unwrap();
        assert_eq!(cfg.server.bind, ([127, 0, 0, 1], 8080).into());
        assert_eq!(cfg.database.max_connections, 5);
        assert_eq!(cfg.game.player_limit, 100);
        assert_eq!(cfg.game.lang, "pl");
    }
}
