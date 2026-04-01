# Migration Task Status

Tracks the real status of every migration task against the current architectural rules.

**Legend:**
- `completed` — implemented, passes quality gate, matches architecture rules.
- `obsolete` — task no longer applies under current rules.
- `remediated` — initially completed with issues, since corrected.

Last updated: 2026-04-02

---

## Phase 1: Foundations and Data Shape

### 01 — Platform Foundations
| Task | Title | Status |
|------|-------|--------|
| MP-01-01 | Create the Rust workspace skeleton | completed |
| MP-01-02 | Add typed configuration loading | completed |
| MP-01-03 | Add application bootstrap and structured logging | completed |
| MP-01-04 | Add health, readiness, and build information endpoints | completed |
| MP-01-05 | Add shared error and response infrastructure | completed |
| MP-01-06 | Consolidate runtime commands into one binary | completed |

### 02 — Database and PostgreSQL
| Task | Title | Status |
|------|-------|--------|
| MP-02-01 | Produce a table ownership map | completed |
| MP-02-02 | Create initial PostgreSQL migrations for core tables | completed |
| MP-02-03 | Design the legacy player field normalization strategy | completed |
| MP-02-04 | Add PostgreSQL repository scaffolding with explicit SQL | completed |
| MP-02-05 | Build a reference-data import command | completed |
| MP-02-06 | Add data reconciliation reporting | completed |

### 03 — HTTP Routing and Middleware
| Task | Title | Status |
|------|-------|--------|
| MP-03-01 | Build the canonical route manifest | completed |
| MP-03-02 | Implement router composition by module | completed |
| MP-03-03 | Add request context middleware | completed |
| MP-03-04 | Add authorization guards | completed |
| MP-03-05 | Add legacy fallback strategy | completed |
| MP-03-06 | Standardize redirects, back-links, and page titles | completed |

### 04 — Rendering, Assets, and Localization
| Task | Title | Status |
|------|-------|--------|
| MP-04-01 | Build the MiniJinja environment | completed |
| MP-04-02 | Port the base layouts and theme selection | completed |
| MP-04-03 | Embed shipped assets into the binary | completed |
| MP-04-04 | Define localization catalog loading | completed |
| MP-04-05 | Rebuild common form and message components | completed |
| MP-04-06 | Port page-level JS and CSS loading rules | completed |

---

## Phase 2: Identity and Core Player State

### 05 — Auth, Accounts, and Sessions
| Task | Title | Status |
|------|-------|--------|
| MP-05-01 | Port login and logout with compatibility hashing | completed |
| MP-05-02 | Port registration validation and account creation | completed |
| MP-05-03 | Port activation and lost-password flows | completed |
| MP-05-04 | Port account settings and profile management | completed |
| MP-05-05 | Port preset, reset, and referral screens | completed |
| MP-05-06 | Replace PHP session semantics with explicit Rust session handling | completed |

### 06 — Player State and Progression
| Task | Title | Status |
|------|-------|--------|
| MP-06-01 | Define the Rust player aggregate | completed |
| MP-06-02 | Port legacy field parsing and serialization | obsolete |
| MP-06-03 | Port derived stat, mana, and bonus calculations | completed |
| MP-06-04 | Port AP, training, class, race, and deity mutations | completed |
| MP-06-05 | Port player-facing read models | completed |
| MP-06-06 | Add parity fixtures for player calculations | completed |

---

## Phase 3: Core Gameplay Vertical Slices

### 07 — World Map, Travel, and Locations
| Task | Title | Status |
|------|-------|--------|
| MP-07-01 | Model locations and movement guards | completed |
| MP-07-02 | Port the city dashboard and global navigation hub | completed |
| MP-07-03 | Port travel, map, and portal flows | completed |
| MP-07-04 | Port secondary location pages | completed |
| MP-07-05 | Port housing, library, temple, and tower-style pages | completed |
| MP-07-06 | Add navigation parity checks | completed |

### 08 — Combat, Encounters, and Random Battle
| Task | Title | Status |
|------|-------|--------|
| MP-08-01 | Isolate combat formulas from PHP helpers | completed |
| MP-08-02 | Port monster and encounter selection | completed |
| MP-08-03 | Port player-versus-monster battle execution | completed |
| MP-08-04 | Port player-versus-player and arena-style flows | completed |
| MP-08-05 | Port route-specific combat entry points | completed |
| MP-08-06 | Port defeat, hospital, and resurrection side effects | completed |
| MP-08-07 | Port poison and antidote aftermath rules | completed |

### 09 — Items, Inventory, and Equipment
| Task | Title | Status |
|------|-------|--------|
| MP-09-01 | Define item catalog and owned-item models | completed |
| MP-09-02 | Port equipment loadout and stat bonus logic | completed |
| MP-09-03 | Port inventory and equipment pages | completed |
| MP-09-04 | Port spell, bow, and specialty item handling | completed |
| MP-09-05 | Port warehouse and storage transfers | completed |
| MP-09-06 | Add item parity tests and sample fixtures | completed |

### 10 — Economy, Markets, and Banking
| Task | Title | Status |
|------|-------|--------|
| MP-10-01 | Map market variants to shared workflows | completed |
| MP-10-02 | Port currencies, bank balances, and transfers | completed |
| MP-10-03 | Port shared market listing and purchase flows | completed |
| MP-10-04 | Port category-specific market rules | completed |
| MP-10-05 | Port bank, gold, and shop-style pages | completed |
| MP-10-06 | Add market reconciliation tests | completed |

### 11 — Crafting, Gathering, and Workshops
| Task | Title | Status |
|------|-------|--------|
| MP-11-01 | Define a shared workshop action pattern | completed |
| MP-11-02 | Port smithing, armorer, and weapon production | completed |
| MP-11-03 | Port alchemy, herbs, potions, and antidotes | completed |
| MP-11-04 | Port mining, lumber, smelting, and farm gathering loops | completed |
| MP-11-05 | Port jeweller, crafts, and astral production | completed |
| MP-11-06 | Port core breeding rules | completed |
| MP-11-07 | Port active pet state and core ranking views | completed |

---

## Phase 4: Community, Clan, and Operations

### 12 — Social, Chat, Mail, and Content
| Task | Title | Status |
|------|-------|--------|
| MP-12-01 | Port global chat, whispers, and inn bot integration | completed |
| MP-12-02 | Port room chat and tavern room state | completed |
| MP-12-03 | Port mail, contacts, and unread counters | completed |
| MP-12-04 | Port forums and discussion formatting | completed |
| MP-12-05 | Port news, newspaper, proposals, polls, and RSS outputs | completed |
| MP-12-06 | Port notes, library, roleplay, and chronicle content pages | completed |

### 13 — Guilds, Tribes, Teams, and Outposts
| Task | Title | Status |
|------|-------|--------|
| MP-13-01 | Port team invitations and membership state | completed |
| MP-13-02 | Port guild and tribe membership flows | completed |
| MP-13-03 | Port tribe permissions, ranks, and admin actions | completed |
| MP-13-04 | Port tribe shared resources and crafting stores | completed |
| MP-13-05 | Port outpost ownership and warfare state | completed |
| MP-13-06 | Port tribe forums and navigation surfaces | completed |

### 14 — Quests, Missions, and Events
| Task | Title | Status |
|------|-------|--------|
| MP-14-01 | Inventory quest and mission state models | completed |
| MP-14-02 | Port the generic mission graph loader | completed |
| MP-14-03 | Port quest action persistence and branching | completed |
| MP-14-04 | Port maze, labyrinth, thieves, and chronicle-style mission flows | completed |
| MP-14-05 | Port random event and hunter quest generation | completed |
| MP-14-06 | Add quest and mission parity fixtures | completed |

### 15 — Admin, Moderation, and Runtime Operations
| Task | Title | Status |
|------|-------|--------|
| MP-15-01 | Port staff and admin route trees with shared guards | completed |
| MP-15-02 | Port moderation actions for jail, court, judge panel, and restriction | completed |
| MP-15-03 | Port bug reporting, logs, and support views | completed |
| MP-15-04 | Port court, update, and staff publishing tools | completed |
| MP-15-05 | Replace page-triggered resets with explicit scheduled jobs | completed |
| MP-15-06 | Port installer and bootstrap operational commands | completed |
| MP-15-07 | Port era-reset operational commands | completed |

---

## Phase 5: Verification and Cutover

### 16 — Testing, Parity, and Cutover
| Task | Title | Status |
|------|-------|--------|
| MP-16-01 | Build a golden-master capture harness for critical pages | completed |
| MP-16-02 | Add integration tests for core user journeys | completed |
| MP-16-03 | Add invariant tests for combat, economy, and inventory | completed |
| MP-16-04 | Define route-by-route cutover and fallback rules | completed |
| MP-16-05 | Build data reconciliation and rollback procedures | completed |
| MP-16-06 | Package the Axum server as the primary runtime | completed |
| MP-16-07 | Remove PHP-only runtime dependencies from deployment | completed |
| MP-16-08 | Write the final production startup and job runbook | completed |
| MP-16-09 | Finalize PHP retirement and rollback references | completed |

---

## Summary

| Status | Count |
|--------|------:|
| completed | 101 |
| obsolete | 1 |
| **Total** | **102** |

## Post-Plan Work

Beyond the original 102 tasks, additional implementation was done via tech debt items:

| Item | Description | Status |
|------|-------------|--------|
| TD-008 | Portal and astral planes handlers | resolved |
| TD-009 | Bandit encounters during travel | resolved |
| TD-011 | Hospital healing and hermit resurrection | resolved |
| TD-013 | Landfill condition XP | resolved |
| TD-014 | Bank transfers and donations | resolved |
| TD-015 | Spell enchantment system | resolved |
| TD-016 | Tribe forum post rate limiting | resolved |
| wieza.php | Magic tower spell/item shop | completed |
| TD-003 | Unmigrated account.php views | open (low priority) |
