//! Registration domain logic.
//!
//! Validates registration input (username, email, password) according to
//! the rules ported from the PHP `verifypass.php` and `register.php`.

/// Errors that can occur during registration validation.
#[derive(Debug, Clone, PartialEq, Eq, thiserror::Error)]
pub enum ValidationError {
    #[error("all fields are required")]
    EmptyFields,
    #[error("password must be at least 5 characters")]
    PasswordTooShort,
    #[error("password must contain at least one letter and one digit")]
    PasswordNoLetterOrDigit,
    #[error("password must contain at least one uppercase letter")]
    PasswordNoUppercase,
    #[error("email addresses do not match")]
    EmailMismatch,
    #[error("invalid email format")]
    InvalidEmail,
    #[error("invalid game type selection")]
    InvalidGameType,
}

/// Validated registration input, ready for persistence.
#[derive(Debug, Clone)]
pub struct ValidatedRegistration {
    pub username: String,
    pub email: String,
    pub password: String,
    pub game_type: GameType,
    pub referrer_id: Option<i32>,
}

/// Visual theme chosen at registration.
#[derive(Debug, Clone, Copy, PartialEq, Eq)]
pub enum GameType {
    /// Text-based (minimal graphics, dark background).
    Text,
    /// Graphical (light background).
    Graphic,
}

impl GameType {
    /// The single-character code stored in the database.
    pub fn as_code(&self) -> &'static str {
        match self {
            Self::Text => "T",
            Self::Graphic => "G",
        }
    }

    /// Parse from the form's radio button value.
    pub fn from_code(code: &str) -> Option<Self> {
        match code {
            "T" => Some(Self::Text),
            "G" => Some(Self::Graphic),
            _ => None,
        }
    }
}

/// Validate registration form input.
///
/// Mirrors the checks in PHP `register.php` and `verifypass.php`:
/// - All fields non-empty
/// - Password >= 5 chars, contains letter + digit, contains uppercase
/// - Email confirmation matches
/// - Email has valid basic format
/// - Game type is T or G
pub fn validate_registration(
    username: &str,
    email: &str,
    confirm_email: &str,
    password: &str,
    game_type_code: &str,
    referrer_raw: &str,
) -> Result<ValidatedRegistration, ValidationError> {
    // Check for empty fields.
    if username.is_empty() || email.is_empty() || confirm_email.is_empty() || password.is_empty() {
        return Err(ValidationError::EmptyFields);
    }

    // Password rules from verifypass.php.
    if password.len() < 5 {
        return Err(ValidationError::PasswordTooShort);
    }
    let has_letter = password.chars().any(|c| c.is_ascii_alphabetic());
    let has_digit = password.chars().any(|c| c.is_ascii_digit());
    if !has_letter || !has_digit {
        return Err(ValidationError::PasswordNoLetterOrDigit);
    }
    if !password.chars().any(|c| c.is_ascii_uppercase()) {
        return Err(ValidationError::PasswordNoUppercase);
    }

    // Email confirmation.
    if email != confirm_email {
        return Err(ValidationError::EmailMismatch);
    }

    // Basic email format check (equivalent to PHP MailVal level 1).
    if !is_basic_valid_email(email) {
        return Err(ValidationError::InvalidEmail);
    }

    // Game type.
    let game_type = GameType::from_code(game_type_code).ok_or(ValidationError::InvalidGameType)?;

    // Referrer ID: parse or ignore.
    let referrer_id = referrer_raw.trim().parse::<i32>().ok().filter(|&id| id > 0);

    // Strip HTML tags from username and email (like PHP's strip_tags).
    let clean_username = strip_tags(username);
    let clean_email = strip_tags(email);

    Ok(ValidatedRegistration {
        username: clean_username,
        email: clean_email,
        password: password.to_owned(),
        game_type,
        referrer_id,
    })
}

/// Basic email format validation (PHP `MailVal` level 1 equivalent).
///
/// Checks: has exactly one `@`, something on both sides, at least one dot
/// on the right side, and a TLD of 2-4 characters.
fn is_basic_valid_email(email: &str) -> bool {
    let parts: Vec<&str> = email.splitn(2, '@').collect();
    if parts.len() != 2 || parts[0].is_empty() || parts[1].is_empty() {
        return false;
    }
    let domain = parts[1];
    let dot_pos = domain.rfind('.');
    match dot_pos {
        Some(pos) => {
            let tld = &domain[pos + 1..];
            let before_tld = &domain[..pos];
            !before_tld.is_empty() && tld.len() >= 2 && tld.len() <= 4
        }
        None => false,
    }
}

/// Remove HTML tags from a string (equivalent to PHP `strip_tags`).
fn strip_tags(input: &str) -> String {
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

/// Generate a random activation token (matches PHP `rand(1,10000000)`).
pub fn generate_activation_token() -> i32 {
    use rand::Rng;
    rand::thread_rng().gen_range(1..=10_000_000)
}

#[cfg(test)]
mod tests {
    use super::*;

    #[test]
    fn valid_registration() {
        let result = validate_registration(
            "TestUser",
            "test@example.com",
            "test@example.com",
            "Pass1word",
            "T",
            "0",
        );
        assert!(result.is_ok());
        let reg = result.unwrap();
        assert_eq!(reg.username, "TestUser");
        assert_eq!(reg.email, "test@example.com");
        assert_eq!(reg.game_type, GameType::Text);
        assert!(reg.referrer_id.is_none());
    }

    #[test]
    fn valid_registration_with_referrer() {
        let result = validate_registration(
            "TestUser",
            "test@example.com",
            "test@example.com",
            "Pass1word",
            "G",
            "42",
        );
        let reg = result.unwrap();
        assert_eq!(reg.referrer_id, Some(42));
        assert_eq!(reg.game_type, GameType::Graphic);
    }

    #[test]
    fn empty_fields() {
        let result = validate_registration("", "a@b.com", "a@b.com", "Pass1word", "T", "");
        assert_eq!(result.unwrap_err(), ValidationError::EmptyFields);
    }

    #[test]
    fn password_too_short() {
        let result = validate_registration("User", "a@b.com", "a@b.com", "Ab1", "T", "");
        assert_eq!(result.unwrap_err(), ValidationError::PasswordTooShort);
    }

    #[test]
    fn password_no_digit() {
        let result = validate_registration("User", "a@b.com", "a@b.com", "Abcde", "T", "");
        assert_eq!(
            result.unwrap_err(),
            ValidationError::PasswordNoLetterOrDigit
        );
    }

    #[test]
    fn password_no_letter() {
        let result = validate_registration("User", "a@b.com", "a@b.com", "12345", "T", "");
        assert_eq!(
            result.unwrap_err(),
            ValidationError::PasswordNoLetterOrDigit
        );
    }

    #[test]
    fn password_no_uppercase() {
        let result = validate_registration("User", "a@b.com", "a@b.com", "pass1word", "T", "");
        assert_eq!(result.unwrap_err(), ValidationError::PasswordNoUppercase);
    }

    #[test]
    fn email_mismatch() {
        let result = validate_registration("User", "a@b.com", "c@d.com", "Pass1word", "T", "");
        assert_eq!(result.unwrap_err(), ValidationError::EmailMismatch);
    }

    #[test]
    fn invalid_email_no_at() {
        let result = validate_registration("User", "invalid", "invalid", "Pass1word", "T", "");
        assert_eq!(result.unwrap_err(), ValidationError::InvalidEmail);
    }

    #[test]
    fn invalid_email_no_dot() {
        let result = validate_registration("User", "a@bcom", "a@bcom", "Pass1word", "T", "");
        assert_eq!(result.unwrap_err(), ValidationError::InvalidEmail);
    }

    #[test]
    fn invalid_game_type() {
        let result = validate_registration("User", "a@b.com", "a@b.com", "Pass1word", "X", "");
        assert_eq!(result.unwrap_err(), ValidationError::InvalidGameType);
    }

    #[test]
    fn strip_tags_removes_html() {
        assert_eq!(strip_tags("hello<b>world</b>"), "helloworld");
        assert_eq!(strip_tags("no tags here"), "no tags here");
        assert_eq!(strip_tags("<script>alert('xss')</script>"), "alert('xss')");
    }

    #[test]
    fn basic_email_validation() {
        assert!(is_basic_valid_email("user@example.com"));
        assert!(is_basic_valid_email("user@sub.example.com"));
        assert!(!is_basic_valid_email("user@"));
        assert!(!is_basic_valid_email("@example.com"));
        assert!(!is_basic_valid_email("noatsign"));
        assert!(!is_basic_valid_email("user@nodot"));
    }

    #[test]
    fn activation_token_in_range() {
        for _ in 0..100 {
            let token = generate_activation_token();
            assert!((1..=10_000_000).contains(&token));
        }
    }
}
