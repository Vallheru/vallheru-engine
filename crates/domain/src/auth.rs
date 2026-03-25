//! Authentication domain logic.
//!
//! Handles password verification with legacy MD5 compatibility and
//! transparent Argon2 upgrade on successful login.

use argon2::Argon2;
use argon2::password_hash::{PasswordHash, PasswordHasher, PasswordVerifier, SaltString};
use md5::{Digest, Md5};
use rand::rngs::OsRng;

/// Result of verifying a password against a stored hash.
#[derive(Debug, Clone, PartialEq, Eq)]
pub enum VerifyResult {
    /// Password matches; no rehash needed.
    Ok,
    /// Password matches a legacy MD5 hash; the caller should upgrade
    /// the stored hash to the returned Argon2 value.
    OkNeedsRehash(String),
    /// Password does not match.
    Invalid,
}

/// Verify a plaintext password against the stored hash.
///
/// Supports two formats:
/// - **Argon2**: hashes starting with `$argon2`
/// - **Legacy MD5**: 32-character hex strings (PHP `MD5()` output)
///
/// When a legacy MD5 hash matches, the function returns
/// [`VerifyResult::OkNeedsRehash`] with a fresh Argon2 hash so the
/// caller can upgrade the stored password transparently.
pub fn verify_password(password: &str, stored_hash: &str) -> VerifyResult {
    if stored_hash.starts_with("$argon2") {
        verify_argon2(password, stored_hash)
    } else {
        verify_legacy_md5(password, stored_hash)
    }
}

/// Hash a plaintext password with Argon2id (for new accounts and rehash).
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

/// Compute the legacy PHP-compatible MD5 hex hash.
///
/// This matches `MD5($password)` in the PHP codebase.
pub fn legacy_md5_hash(password: &str) -> String {
    let mut hasher = Md5::new();
    hasher.update(password.as_bytes());
    hex::encode(hasher.finalize())
}

fn verify_argon2(password: &str, stored_hash: &str) -> VerifyResult {
    let Ok(parsed) = PasswordHash::new(stored_hash) else {
        tracing::warn!("invalid argon2 hash format in stored password");
        return VerifyResult::Invalid;
    };

    if Argon2::default()
        .verify_password(password.as_bytes(), &parsed)
        .is_ok()
    {
        VerifyResult::Ok
    } else {
        VerifyResult::Invalid
    }
}

fn verify_legacy_md5(password: &str, stored_hash: &str) -> VerifyResult {
    let computed = legacy_md5_hash(password);

    if constant_time_eq(computed.as_bytes(), stored_hash.as_bytes()) {
        let new_hash = hash_password(password);
        tracing::info!("legacy MD5 password matched; triggering argon2 rehash");
        VerifyResult::OkNeedsRehash(new_hash)
    } else {
        VerifyResult::Invalid
    }
}

/// Constant-time byte comparison to prevent timing attacks.
fn constant_time_eq(a: &[u8], b: &[u8]) -> bool {
    if a.len() != b.len() {
        return false;
    }
    let mut diff = 0u8;
    for (x, y) in a.iter().zip(b.iter()) {
        diff |= x ^ y;
    }
    diff == 0
}

#[cfg(test)]
mod tests {
    use super::*;

    #[test]
    fn legacy_md5_matches_php() {
        // PHP: MD5("test") = "098f6bcd4621d373cade4e832627b4f6"
        assert_eq!(legacy_md5_hash("test"), "098f6bcd4621d373cade4e832627b4f6");
    }

    #[test]
    fn verify_legacy_md5_password() {
        let stored = "098f6bcd4621d373cade4e832627b4f6"; // MD5("test")
        let result = verify_password("test", stored);
        match result {
            VerifyResult::OkNeedsRehash(new_hash) => {
                assert!(new_hash.starts_with("$argon2"));
            }
            other => panic!("expected OkNeedsRehash, got {other:?}"),
        }
    }

    #[test]
    fn verify_legacy_md5_wrong_password() {
        let stored = "098f6bcd4621d373cade4e832627b4f6"; // MD5("test")
        assert_eq!(verify_password("wrong", stored), VerifyResult::Invalid);
    }

    #[test]
    fn verify_argon2_password() {
        let hash = hash_password("mypassword");
        assert!(hash.starts_with("$argon2"));
        assert_eq!(verify_password("mypassword", &hash), VerifyResult::Ok);
    }

    #[test]
    fn verify_argon2_wrong_password() {
        let hash = hash_password("mypassword");
        assert_eq!(verify_password("wrong", &hash), VerifyResult::Invalid);
    }

    #[test]
    fn hash_password_produces_unique_salts() {
        let h1 = hash_password("same");
        let h2 = hash_password("same");
        assert_ne!(h1, h2, "each hash should have a unique salt");
    }
}
