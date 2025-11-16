use serde::Serialize;

/// Basic representation of a player profile.
#[derive(Clone, Debug, Serialize)]
pub struct PlayerProfile {
    pub name: String,
    pub level: u32,
}
