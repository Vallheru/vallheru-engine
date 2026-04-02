//! Authentication domain logic.
//!
//! Handles password hashing and verification using Argon2id.

use argon2::Argon2;
use argon2::password_hash::{PasswordHash, PasswordHasher, PasswordVerifier, SaltString};
use rand::rngs::OsRng;

/// Verify a plaintext password against a stored Argon2 hash.
///
/// Returns `true` if the password matches, `false` otherwise.
pub fn verify_password(password: &str, stored_hash: &str) -> bool {
    let Ok(parsed) = PasswordHash::new(stored_hash) else {
        tracing::warn!("invalid password hash format");
        return false;
    };

    Argon2::default()
        .verify_password(password.as_bytes(), &parsed)
        .is_ok()
}

/// Hash a plaintext password with Argon2id.
///
/// # Panics
///
/// Panics if the OS random number generator fails (extremely unlikely).
pub fn hash_password(password: &str) -> String {
    let salt = SaltString::generate(&mut OsRng);
    let argon2 = Argon2::default();
    argon2
        .hash_password(password.as_bytes(), &salt)
        .expect("argon2 hashing should not fail with valid inputs")
        .to_string()
}

#[cfg(test)]
mod tests {
    use super::*;

    #[test]
    fn verify_argon2_password() {
        let hash = hash_password("mypassword");
        assert!(hash.starts_with("$argon2"));
        assert!(verify_password("mypassword", &hash));
    }

    #[test]
    fn verify_argon2_wrong_password() {
        let hash = hash_password("mypassword");
        assert!(!verify_password("wrong", &hash));
    }

    #[test]
    fn hash_password_produces_unique_salts() {
        let h1 = hash_password("same");
        let h2 = hash_password("same");
        assert_ne!(h1, h2, "each hash should have a unique salt");
    }

    #[test]
    fn invalid_hash_format_returns_false() {
        assert!(!verify_password("test", "not-a-valid-hash"));
    }
}
