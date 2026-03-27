//! Location model and movement guards.
//!
//! Replaces the scattered string-based location checks in PHP page scripts
//! with a typed enum system. Every location string stored in the database
//! maps to exactly one `Location` variant.

use serde::{Deserialize, Serialize};

/// All game locations a player can be in.
///
/// The database stores the Polish string (e.g. `"Altara"`, `"Góry"`).
/// This enum gives those values a type-safe representation.
#[derive(Debug, Clone, Copy, PartialEq, Eq, Hash, Serialize, Deserialize)]
pub enum Location {
    /// Main city (Altara).
    Altara,
    /// Second city (Ardulith).
    Ardulith,
    /// Travelling between locations.
    Travelling,
    /// Mountains (exploration area).
    Mountains,
    /// Forest (exploration area).
    Forest,
    /// Dungeon / jail area.
    Dungeon,
    /// Portal area.
    Portal,
    /// Astral plane.
    AstralPlane,
    /// Adventure / quest / mission.
    Adventure,
}

impl Location {
    /// Parse from the Polish string stored in `players.location`.
    pub fn from_db(s: &str) -> Option<Self> {
        match s {
            "Altara" => Some(Self::Altara),
            "Ardulith" => Some(Self::Ardulith),
            "Podróż" => Some(Self::Travelling),
            "Góry" => Some(Self::Mountains),
            "Las" => Some(Self::Forest),
            "Lochy" => Some(Self::Dungeon),
            "Portal" => Some(Self::Portal),
            "Astralny plan" => Some(Self::AstralPlane),
            "Przygoda" => Some(Self::Adventure),
            _ => None,
        }
    }

    /// Return the Polish string for database storage.
    pub fn to_db(self) -> &'static str {
        match self {
            Self::Altara => "Altara",
            Self::Ardulith => "Ardulith",
            Self::Travelling => "Podróż",
            Self::Mountains => "Góry",
            Self::Forest => "Las",
            Self::Dungeon => "Lochy",
            Self::Portal => "Portal",
            Self::AstralPlane => "Astralny plan",
            Self::Adventure => "Przygoda",
        }
    }

    /// Whether this location is a city (Altara or Ardulith).
    pub fn is_city(self) -> bool {
        matches!(self, Self::Altara | Self::Ardulith)
    }

    /// Whether this location is an exploration area (mountains or forest).
    pub fn is_exploration(self) -> bool {
        matches!(self, Self::Mountains | Self::Forest)
    }

    /// Whether the player can access city services (bank, hospital, market, etc.)
    /// from this location.
    pub fn has_city_services(self) -> bool {
        self.is_city()
    }

    /// Whether combat encounters can trigger at this location.
    pub fn allows_combat(self) -> bool {
        self.is_exploration() || self == Self::AstralPlane
    }
}

// ---------------------------------------------------------------------------
// Movement guards
// ---------------------------------------------------------------------------

/// Reason a movement was denied.
#[derive(Debug, Clone, PartialEq, Eq)]
pub enum MovementDenied {
    /// Player is dead (HP <= 0), must heal first.
    Dead,
    /// Player is in combat, must finish first.
    InCombat,
    /// Player is immune (can't leave city).
    Immune,
    /// Player is in dungeon and can't travel out.
    InDungeon,
    /// Player is on a quest/adventure and must finish first.
    OnAdventure,
    /// The target location is the same as current.
    AlreadyThere,
    /// The route is not valid (e.g. direct `Altara` → `AstralPlane`).
    InvalidRoute,
}

/// Check whether a player can travel from one location to another.
///
/// This centralises the scattered location checks from PHP page scripts.
pub fn can_travel(
    from: Location,
    to: Location,
    hp: i32,
    fight_id: i32,
    is_immune: bool,
) -> Result<(), MovementDenied> {
    if hp <= 0 {
        return Err(MovementDenied::Dead);
    }

    if fight_id != 0 {
        return Err(MovementDenied::InCombat);
    }

    if from == to {
        return Err(MovementDenied::AlreadyThere);
    }

    if from == Location::Dungeon {
        return Err(MovementDenied::InDungeon);
    }

    if from == Location::Adventure {
        return Err(MovementDenied::OnAdventure);
    }

    if is_immune && !to.is_city() {
        return Err(MovementDenied::Immune);
    }

    // Validate route: exploration areas and astral plane are reachable
    // only from cities (via travel/stables).
    match to {
        Location::Mountains | Location::Forest => {
            if !from.is_city() && from != Location::Travelling {
                return Err(MovementDenied::InvalidRoute);
            }
        }
        Location::AstralPlane => {
            if !from.is_city() && from != Location::Portal {
                return Err(MovementDenied::InvalidRoute);
            }
        }
        Location::Portal => {
            if !from.is_city() {
                return Err(MovementDenied::InvalidRoute);
            }
        }
        Location::Dungeon | Location::Adventure => {
            // These are entered through specific game mechanics, not travel
            return Err(MovementDenied::InvalidRoute);
        }
        Location::Altara | Location::Ardulith | Location::Travelling => {
            // Can always travel to cities or be in transit
        }
    }

    Ok(())
}

// ---------------------------------------------------------------------------
// Tests
// ---------------------------------------------------------------------------

#[cfg(test)]
mod tests {
    use super::*;

    #[test]
    fn location_roundtrip() {
        let locations = [
            Location::Altara,
            Location::Ardulith,
            Location::Travelling,
            Location::Mountains,
            Location::Forest,
            Location::Dungeon,
            Location::Portal,
            Location::AstralPlane,
            Location::Adventure,
        ];
        for loc in locations {
            let db = loc.to_db();
            let parsed = Location::from_db(db).expect("should roundtrip");
            assert_eq!(parsed, loc);
        }
    }

    #[test]
    fn unknown_location_returns_none() {
        assert!(Location::from_db("Mordor").is_none());
    }

    #[test]
    fn city_checks() {
        assert!(Location::Altara.is_city());
        assert!(Location::Ardulith.is_city());
        assert!(!Location::Mountains.is_city());
        assert!(!Location::Travelling.is_city());
    }

    #[test]
    fn exploration_checks() {
        assert!(Location::Mountains.is_exploration());
        assert!(Location::Forest.is_exploration());
        assert!(!Location::Altara.is_exploration());
    }

    #[test]
    fn combat_areas() {
        assert!(Location::Mountains.allows_combat());
        assert!(Location::Forest.allows_combat());
        assert!(Location::AstralPlane.allows_combat());
        assert!(!Location::Altara.allows_combat());
        assert!(!Location::Portal.allows_combat());
    }

    #[test]
    fn travel_city_to_mountains() {
        assert!(can_travel(Location::Altara, Location::Mountains, 100, 0, false).is_ok());
    }

    #[test]
    fn travel_denied_dead() {
        assert_eq!(
            can_travel(Location::Altara, Location::Mountains, 0, 0, false),
            Err(MovementDenied::Dead)
        );
    }

    #[test]
    fn travel_denied_in_combat() {
        assert_eq!(
            can_travel(Location::Mountains, Location::Altara, 100, 42, false),
            Err(MovementDenied::InCombat)
        );
    }

    #[test]
    fn travel_denied_immune_leaving_city() {
        assert_eq!(
            can_travel(Location::Altara, Location::Mountains, 100, 0, true),
            Err(MovementDenied::Immune)
        );
    }

    #[test]
    fn travel_immune_between_cities_ok() {
        assert!(can_travel(Location::Altara, Location::Ardulith, 100, 0, true).is_ok());
    }

    #[test]
    fn travel_denied_from_dungeon() {
        assert_eq!(
            can_travel(Location::Dungeon, Location::Altara, 100, 0, false),
            Err(MovementDenied::InDungeon)
        );
    }

    #[test]
    fn travel_denied_from_adventure() {
        assert_eq!(
            can_travel(Location::Adventure, Location::Altara, 100, 0, false),
            Err(MovementDenied::OnAdventure)
        );
    }

    #[test]
    fn travel_denied_already_there() {
        assert_eq!(
            can_travel(Location::Altara, Location::Altara, 100, 0, false),
            Err(MovementDenied::AlreadyThere)
        );
    }

    #[test]
    fn travel_mountains_to_portal_denied() {
        assert_eq!(
            can_travel(Location::Mountains, Location::Portal, 100, 0, false),
            Err(MovementDenied::InvalidRoute)
        );
    }

    #[test]
    fn travel_city_to_portal_ok() {
        assert!(can_travel(Location::Altara, Location::Portal, 100, 0, false).is_ok());
    }

    #[test]
    fn travel_city_to_dungeon_denied() {
        // Dungeon is entered through game mechanics, not travel
        assert_eq!(
            can_travel(Location::Altara, Location::Dungeon, 100, 0, false),
            Err(MovementDenied::InvalidRoute)
        );
    }

    // -----------------------------------------------------------------------
    // Exhaustive table-driven navigation parity tests (MP-07-06)
    // -----------------------------------------------------------------------

    /// Every (from, to) pair with expected outcome for a healthy, non-combat,
    /// non-immune player. This catches any regression in the route matrix.
    #[test]
    fn exhaustive_route_matrix() {
        use Location::*;

        // (from, to, expected_ok)
        let cases: &[(Location, Location, bool)] = &[
            // From Altara
            (Altara, Ardulith, true),
            (Altara, Mountains, true),
            (Altara, Forest, true),
            (Altara, Portal, true),
            (Altara, Travelling, true),
            (Altara, Dungeon, false),
            (Altara, Adventure, false),
            (Altara, AstralPlane, true),
            // From Ardulith
            (Ardulith, Altara, true),
            (Ardulith, Mountains, true),
            (Ardulith, Forest, true),
            (Ardulith, Portal, true),
            (Ardulith, Travelling, true),
            (Ardulith, Dungeon, false),
            (Ardulith, Adventure, false),
            (Ardulith, AstralPlane, true),
            // From Mountains
            (Mountains, Altara, true),
            (Mountains, Ardulith, true),
            (Mountains, Forest, false),
            (Mountains, Portal, false),
            (Mountains, Dungeon, false),
            (Mountains, Adventure, false),
            (Mountains, AstralPlane, false),
            (Mountains, Travelling, true),
            // From Forest
            (Forest, Altara, true),
            (Forest, Ardulith, true),
            (Forest, Mountains, false),
            (Forest, Portal, false),
            (Forest, Dungeon, false),
            (Forest, Adventure, false),
            (Forest, AstralPlane, false),
            (Forest, Travelling, true),
            // From Travelling
            (Travelling, Altara, true),
            (Travelling, Ardulith, true),
            (Travelling, Mountains, true),
            (Travelling, Forest, true),
            (Travelling, Portal, false),
            (Travelling, Dungeon, false),
            (Travelling, Adventure, false),
            (Travelling, AstralPlane, false),
            // From Dungeon — always denied
            (Dungeon, Altara, false),
            (Dungeon, Mountains, false),
            // From Adventure — always denied
            (Adventure, Altara, false),
            (Adventure, Mountains, false),
            // From Portal
            (Portal, Altara, true),
            (Portal, Ardulith, true),
            (Portal, AstralPlane, true),
            (Portal, Mountains, false),
            (Portal, Dungeon, false),
            // From AstralPlane
            (AstralPlane, Altara, true),
            (AstralPlane, Ardulith, true),
            (AstralPlane, Mountains, false),
            (AstralPlane, Portal, false),
        ];

        for &(from, to, expected_ok) in cases {
            let result = can_travel(from, to, 100, 0, false);
            assert_eq!(
                result.is_ok(),
                expected_ok,
                "can_travel({from:?}, {to:?}) = {result:?}, expected ok={expected_ok}"
            );
        }
    }

    /// All denial reasons for a player at the same location, covering the
    /// `AlreadyThere` guard across every variant.
    #[test]
    fn already_there_for_all_locations() {
        let locations = [
            Location::Altara,
            Location::Ardulith,
            Location::Mountains,
            Location::Forest,
        ];
        for loc in locations {
            assert_eq!(
                can_travel(loc, loc, 100, 0, false),
                Err(MovementDenied::AlreadyThere),
                "AlreadyThere not raised for {loc:?}"
            );
        }
    }

    /// Immune player can travel between cities but nowhere else.
    #[test]
    fn immune_restricts_to_cities_only() {
        use Location::*;
        // City-to-city is allowed
        assert!(can_travel(Altara, Ardulith, 100, 0, true).is_ok());
        assert!(can_travel(Ardulith, Altara, 100, 0, true).is_ok());

        // City-to-non-city is denied
        for dest in [Mountains, Forest, Portal, AstralPlane] {
            assert_eq!(
                can_travel(Altara, dest, 100, 0, true),
                Err(MovementDenied::Immune),
                "Immune player allowed to travel to {dest:?}"
            );
        }
    }

    /// Location classification correctness.
    #[test]
    fn location_classification_table() {
        use Location::*;
        let cases: &[(Location, bool, bool, bool, bool)] = &[
            // (loc, is_city, is_exploration, has_city_services, allows_combat)
            (Altara, true, false, true, false),
            (Ardulith, true, false, true, false),
            (Mountains, false, true, false, true),
            (Forest, false, true, false, true),
            (AstralPlane, false, false, false, true),
            (Portal, false, false, false, false),
            (Dungeon, false, false, false, false),
            (Travelling, false, false, false, false),
            (Adventure, false, false, false, false),
        ];
        for &(loc, city, explore, svc, combat) in cases {
            assert_eq!(loc.is_city(), city, "{loc:?}.is_city()");
            assert_eq!(loc.is_exploration(), explore, "{loc:?}.is_exploration()");
            assert_eq!(loc.has_city_services(), svc, "{loc:?}.has_city_services()");
            assert_eq!(loc.allows_combat(), combat, "{loc:?}.allows_combat()");
        }
    }
}
