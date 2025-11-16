use super::model::PlayerProfile;

/// Returns a placeholder profile that allows the HTTP layer to be wired up.
pub fn sample_profile() -> PlayerProfile {
    PlayerProfile { name: "Adventurer".to_string(), level: 1 }
}
