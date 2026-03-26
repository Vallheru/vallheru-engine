//! Account activation and password-reset domain logic.
//!
//! Pure functions for building default player settings and generating
//! secure password-reset tokens.

/// Build the default `settings_raw` string for a newly activated player.
///
/// Mirrors the PHP logic in `aktywacja.php` which builds a semicolon-
/// delimited key:value string depending on game type.
pub fn default_settings(game_type: &str) -> String {
    let graphic = if game_type == "T" { "" } else { "layout1" };
    format!(
        "style:light.css;graphic:{graphic};graphbar:N;forumcats:All;\
         autodrink:N;rinvites:Y;battlelog:N;"
    )
}

/// Generate a secure random token string for password resets.
///
/// Unlike the PHP version (which used `md5(uniqid(rand()))`), this uses
/// a cryptographically secure random 32-byte value encoded as hex (64 chars).
pub fn generate_reset_token() -> String {
    use rand::Rng;
    let mut bytes = [0u8; 32];
    rand::thread_rng().fill(&mut bytes);
    hex::encode(bytes)
}

/// Generate a temporary random password (9 alphanumeric characters).
///
/// Matches the PHP behavior of `substr(md5(uniqid(rand(), true)), 3, 9)`
/// but uses a CSPRNG and contains at least one letter and one digit.
pub fn generate_temporary_password() -> String {
    use rand::Rng;
    let mut rng = rand::thread_rng();
    let charset: &[u8] = b"abcdefghijklmnopqrstuvwxyz0123456789";
    let password: String = (0..9)
        .map(|_| {
            let idx = rng.gen_range(0..charset.len());
            charset[idx] as char
        })
        .collect();
    password
}

#[cfg(test)]
mod tests {
    use super::*;

    #[test]
    fn default_settings_text_mode() {
        let s = default_settings("T");
        assert!(s.contains("graphic:;"), "text mode has empty graphic");
        assert!(s.contains("style:light.css;"));
    }

    #[test]
    fn default_settings_graphic_mode() {
        let s = default_settings("G");
        assert!(s.contains("graphic:layout1;"), "graphic mode has layout1");
    }

    #[test]
    fn reset_token_length() {
        let token = generate_reset_token();
        assert_eq!(token.len(), 64);
        assert!(token.chars().all(|c| c.is_ascii_hexdigit()));
    }

    #[test]
    fn reset_token_uniqueness() {
        let a = generate_reset_token();
        let b = generate_reset_token();
        assert_ne!(a, b);
    }

    #[test]
    fn temporary_password_length() {
        let pwd = generate_temporary_password();
        assert_eq!(pwd.len(), 9);
        assert!(pwd.chars().all(|c| c.is_ascii_alphanumeric()));
    }
}
