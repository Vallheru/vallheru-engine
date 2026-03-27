# 07 World Map, Travel, and Locations

## Current State

- `city.php`
- `map.php`
- `travel.php`
- `portal.php`
- `portals.php`
- `alley.php`
- `gory.php`
- `las.php`
- `landfill.php`
- `house.php`
- `temple.php`
- `tower.php`
- `wieza.php`
- `rest.php`
- `library.php`
- `includes/counttime.php` (reset countdown displayed on location pages)

## Why This Module Exists

Port the world-navigation layer and stateful location pages that gate access to most other features.

## Target Rust Shape

- `crates/domain/src/location.rs` — Location enum, movement rules, and access guards.
- `crates/web/src/handlers/city.rs` — City dashboard hub.
- `crates/web/src/handlers/travel.rs` — Map, travel, portals.
- `crates/web/src/handlers/locations.rs` — Secondary location pages (alley, mountains, forest, landfill, rest, tower, etc.).
- `crates/web/src/handlers/house.rs` — Player housing.
- `crates/data/src/location.rs` — Location-related queries (portal costs, housing ownership).

## Module Dependencies

- 03 HTTP Routing and Middleware (authorization guards, route composition).
- 04 Rendering, Assets, and Localization (page templates, theme switching).
- 06 Player State and Progression (player location, energy, prerequisites).

## Risks and Notes

- Location checks are currently scattered across individual page scripts as string comparisons. Centralizing them in Rust is a significant improvement but requires exhaustive mapping.
- Some location pages (temple, tower) have deity/class interactions that couple to player progression rules.

## Tasks

### MP-07-01: Model locations and movement guards ✅

- Description: Define location identifiers, movement rules, and common access checks currently scattered across page scripts.
- Estimate: 1.5h
- Depends on: MP-03-04, MP-06-01.
- Functional acceptance criteria:
  - The domain layer exposes typed locations and movement guards.
  - Routes can reject invalid location access centrally.
  - Location state no longer depends on raw string comparison in handlers.
- Technical notes: Preserve current names for compatibility, even if internal enums are cleaner.
- In scope: Location model and guard service.
- Out of scope: Map rendering.

### MP-07-02: Port the city dashboard and global navigation hub ✅

- **Status**: completed
- Description: Recreate `city.php` as the authenticated landing hub that links to available actions based on player state.
- Estimate: 2h
- Depends on: MP-07-01, MP-04-02.
- Functional acceptance criteria:
  - The city page renders the same major navigation choices as the PHP version.
  - Closed/open game checks and location constraints still apply.
  - The Rust route can replace the PHP entry point behind the proxy.
- Technical notes: This is one of the best early authenticated cutover slices.
- In scope: City page and its view model.
- Out of scope: Every linked feature.

### MP-07-03: Port travel, map, and portal flows

- **Status**: not-started
- Description: Migrate world movement between cities and special travel pages including portal usage.
- Estimate: 2h
- Depends on: MP-07-01, MP-10-02.
- Functional acceptance criteria:
  - Movement updates player location transactionally.
  - Portal availability and costs match legacy rules.
  - Travel screens render success and denial states correctly.
- Technical notes: Keep path-specific rules in domain services rather than template conditionals.
- In scope: Map/travel/portal pages and services.
- Out of scope: Combat encountered during travel.

### MP-07-04: Port secondary location pages

- **Status**: not-started
- Description: Migrate alley, mountains, forest, landfill, rest, and similar non-market/non-combat location screens.
- Estimate: 2h
- Depends on: MP-07-02, MP-07-03.
- Functional acceptance criteria:
  - Each location page has a Rust handler and template.
  - Location-specific gating and simple mutations are preserved.
  - Cross-links back to the correct hub pages exist.
- Technical notes: Group these together because they share the same location guard patterns.
- In scope: Secondary location handlers and views.
- Out of scope: Deep feature logic owned by other modules.

### MP-07-05: Port housing, library, temple, and tower-style pages

- **Status**: not-started
- Description: Rebuild the stateful but mostly local pages for house access, temple/deity interactions, library content, and tower screens.
- Estimate: 1.5h
- Depends on: MP-07-02, MP-12-06.
- Functional acceptance criteria:
  - The pages render with Rust templates.
  - Player ownership or access rules are enforced.
  - Data reads and writes are isolated to dedicated query modules.
- Technical notes: Some content-heavy pages depend on later text/publishing migration; stub view-model seams now.
- In scope: Page shell and primary behavior.
- Out of scope: Staff editing of associated content.

### MP-07-06: Add navigation parity checks

- **Status**: not-started
- Description: Create route and view-model tests that confirm common location transitions and denial cases.
- Estimate: 2h
- Depends on: MP-07-02, MP-07-03, MP-07-04.
- Functional acceptance criteria:
  - Tests cover at least authenticated city entry, denied access, travel success, and portal denial.
  - Route-level regressions are caught before cutover.
  - Test fixtures do not require live PHP execution.
- Technical notes: These checks should stay fast and table-driven.
- In scope: Integration tests for navigation.
- Out of scope: Browser automation.