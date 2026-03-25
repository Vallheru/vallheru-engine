# Route Manifest

Produced for MP-03-01. Every top-level PHP entry point maps to a planned Axum
route, with auth requirements and module ownership.

## Auth Levels

| Level | Description |
|---|---|
| **public** | No session required. |
| **authenticated** | Valid player session required (includes `head.php`). |
| **staff** | Requires rank: Staff, Admin, or module-specific elevated rank. |
| **admin** | Requires rank: Admin. |

## Bootstrap Patterns

| Pattern | Count | Auth Level |
|---|---|---|
| `require_once("includes/head.php")` | ~96 | authenticated |
| `require_once('includes/config.php')` | 4 | public (or special) |
| `require 'libs/Smarty.class.php'` | 3 | public |
| `require_once('includes/sessions.php')` | 1 | special (logout) |
| Custom/none | 1 | special (source.php) |

---

## Route Inventory

### Public Routes (no session required)

| PHP File | Planned Route | Method | Module | Notes |
|---|---|---|---|---|
| `index.php` | `/` | GET/POST | 05-auth | Login page, landing. |
| `register.php` | `/register` | GET/POST | 05-auth | Player registration. |
| `aktywacja.php` | `/activate` | GET | 05-auth | Account activation via token. |
| `reset.php` | `/reset-password` | GET/POST | 05-auth | Password reset request. |
| `preset.php` | `/reset-password/confirm` | GET/POST | 05-auth | Password reset confirmation. |
| `rss.php` | `/rss` | GET | 12-social | RSS feed (XML). |
| `source.php` | — | — | — | Source viewer — will not be migrated. |

### Special Endpoints (config.php bootstrap, AJAX/partial)

| PHP File | Planned Route | Method | Module | Notes |
|---|---|---|---|---|
| `chatmsgs.php` | `/api/chat/messages` | GET | 12-social | Chat message polling (AJAX). |
| `roommsgs.php` | `/api/room/messages` | GET | 12-social | Room message polling (AJAX). |
| `logout.php` | `/logout` | GET/POST | 05-auth | Session destruction. |

### Authenticated Routes — Module 05: Auth & Accounts

| PHP File | Planned Route | Method | Module | Notes |
|---|---|---|---|---|
| `account.php` | `/account` | GET/POST | 05-auth | Account settings. |
| `rasa.php` | `/character/race` | GET/POST | 05-auth | Race selection. |
| `klasa.php` | `/character/class` | GET/POST | 05-auth | Class selection. |

### Authenticated Routes — Module 06: Player State & Progression

| PHP File | Planned Route | Method | Module | Notes |
|---|---|---|---|---|
| `stats.php` | `/stats` | GET | 06-player | Player statistics. |
| `train.php` | `/train` | GET/POST | 06-player | Training page. |
| `hof.php` | `/hall-of-fame` | GET | 06-player | Hall of fame. |
| `hof2.php` | `/hall-of-fame/secondary` | GET | 06-player | Secondary hall of fame. |
| `ap.php` | `/action-points` | GET | 06-player | Action points overview. |
| `rest.php` | `/rest` | GET/POST | 06-player | Resting. |
| `deity.php` | `/deity` | GET/POST | 06-player | Deity interaction. |
| `temple.php` | `/temple` | GET/POST | 06-player | Temple (stat bonuses). |
| `preset.php` | — | — | — | (handled above as public route) |
| `referrals.php` | `/referrals` | GET | 06-player | Referral tracking. |

### Authenticated Routes — Module 07: World Map, Travel & Locations

| PHP File | Planned Route | Method | Module | Notes |
|---|---|---|---|---|
| `city.php` | `/city` | GET | 07-world | City hub page. |
| `map.php` | `/map` | GET | 07-world | World map. |
| `travel.php` | `/travel` | GET/POST | 07-world | Travel between locations. |
| `grid.php` | `/grid` | GET | 07-world | Grid/map navigation. |
| `explore.php` | `/explore` | GET/POST | 07-world | Location exploration. |
| `las.php` | `/forest` | GET/POST | 07-world | Forest area. |
| `gory.php` | `/mountains` | GET/POST | 07-world | Mountain area. |
| `portal.php` | `/portal` | GET/POST | 07-world | Portal room. |
| `portals.php` | `/portals` | GET/POST | 07-world | Portal listing. |
| `room.php` | `/room` | GET/POST | 07-world | Room view. |
| `alley.php` | `/alley` | GET | 07-world | Alley area. |
| `maze.php` | `/maze` | GET/POST | 07-world | Maze area. |
| `hospital.php` | `/hospital` | GET/POST | 07-world | Hospital. |

### Authenticated Routes — Module 08: Combat & Encounters

| PHP File | Planned Route | Method | Module | Notes |
|---|---|---|---|---|
| `battle.php` | `/battle` | GET/POST | 08-combat | Combat page. |
| `hunters.php` | `/hunters` | GET/POST | 08-combat | Hunter encounters. |
| `wieza.php` | `/tower/combat` | GET/POST | 08-combat | Tower combat. |
| `tower.php` | `/tower` | GET/POST | 08-combat | Tower area. |
| `outpost.php` | `/outpost` | GET/POST | 08-combat | Single outpost. |
| `outposts.php` | `/outposts` | GET | 08-combat | Outpost listing. |
| `mission.php` | `/mission` | GET/POST | 08-combat | Mission execution. |

### Authenticated Routes — Module 09: Items, Inventory & Equipment

| PHP File | Planned Route | Method | Module | Notes |
|---|---|---|---|---|
| `equip.php` | `/equipment` | GET/POST | 09-items | Equipment management. |
| `bows.php` | `/shop/bows` | GET/POST | 09-items | Bow shop. |
| `weapons.php` | `/shop/weapons` | GET/POST | 09-items | Weapons shop. |
| `armor.php` | `/shop/armor` | GET/POST | 09-items | Armor shop. |
| `czary.php` | `/shop/spells` | GET/POST | 09-items | Spell shop. |
| `msklep.php` | `/shop/mage` | GET/POST | 09-items | Mage item shop. |
| `warehouse.php` | `/warehouse` | GET/POST | 09-items | Warehouse storage. |
| `landfill.php` | `/landfill` | GET/POST | 09-items | Item disposal. |

### Authenticated Routes — Module 10: Economy, Markets & Banking

| PHP File | Planned Route | Method | Module | Notes |
|---|---|---|---|---|
| `bank.php` | `/bank` | GET/POST | 10-economy | Banking. |
| `zloto.php` | `/gold` | GET | 10-economy | Gold overview. |
| `market.php` | `/market` | GET | 10-economy | Market hub. |
| `amarket.php` | `/market/alchemy` | GET/POST | 10-economy | Alchemy market. |
| `cmarket.php` | `/market/core` | GET/POST | 10-economy | Core market. |
| `hmarket.php` | `/market/herbs` | GET/POST | 10-economy | Herb market. |
| `imarket.php` | `/market/items` | GET/POST | 10-economy | Item market. |
| `lmarket.php` | `/market/lumber` | GET/POST | 10-economy | Lumber market. |
| `mmarket.php` | `/market/minerals` | GET/POST | 10-economy | Mineral market. |
| `pmarket.php` | `/market/potions` | GET/POST | 10-economy | Potion market. |
| `rmarket.php` | `/market/rings` | GET/POST | 10-economy | Ring market. |

### Authenticated Routes — Module 11: Crafting, Gathering & Workshops

| PHP File | Planned Route | Method | Module | Notes |
|---|---|---|---|---|
| `kopalnia.php` | `/mine` | GET/POST | 11-crafting | Mining. |
| `mines.php` | `/mines` | GET/POST | 11-crafting | Mine overview. |
| `smelter.php` | `/smelter` | GET/POST | 11-crafting | Ore smelting. |
| `kowal.php` | `/smithy` | GET/POST | 11-crafting | Smithing. |
| `crafts.php` | `/crafts` | GET/POST | 11-crafting | Crafting hub. |
| `jeweller.php` | `/jeweller` | GET/POST | 11-crafting | Jeweller workshop. |
| `jewellershop.php` | `/jeweller/shop` | GET/POST | 11-crafting | Jeweller shop. |
| `alchemik.php` | `/alchemy` | GET/POST | 11-crafting | Alchemy. |
| `farm.php` | `/farm` | GET/POST | 11-crafting | Farming. |
| `lumberjack.php` | `/lumberjack` | GET/POST | 11-crafting | Lumberjack. |
| `lumbermill.php` | `/lumbermill` | GET/POST | 11-crafting | Lumber mill. |
| `core.php` | `/core` | GET/POST | 11-crafting | Core item management. |
| `thieves.php` | `/thieves` | GET/POST | 11-crafting | Thief skills (crafting overlap). |

### Authenticated Routes — Module 12: Social, Chat, Mail & Content

| PHP File | Planned Route | Method | Module | Notes |
|---|---|---|---|---|
| `chat.php` | `/chat` | GET/POST | 12-social | Chat room. |
| `mail.php` | `/mail` | GET/POST | 12-social | In-game mail. |
| `forums.php` | `/forums` | GET/POST | 12-social | Forum listing and threads. |
| `news.php` | `/news` | GET | 12-social | Game news. |
| `newspaper.php` | `/newspaper` | GET | 12-social | In-game newspaper. |
| `library.php` | `/library` | GET/POST | 12-social | Player library. |
| `chronicle.php` | `/chronicle` | GET | 12-social | Game chronicle. |
| `notatnik.php` | `/notepad` | GET/POST | 12-social | Player notepad. |
| `polls.php` | `/polls` | GET/POST | 12-social | Polls. |
| `proposals.php` | `/proposals` | GET/POST | 12-social | Player proposals. |
| `updates.php` | `/updates` | GET | 12-social | Game updates. |
| `memberlist.php` | `/members` | GET | 12-social | Player directory. |
| `view.php` | `/player/:id` | GET | 12-social | Player profile view. |
| `roleplay.php` | `/roleplay` | GET/POST | 12-social | Roleplay section. |

### Authenticated Routes — Module 13: Guilds, Tribes, Teams & Outposts

| PHP File | Planned Route | Method | Module | Notes |
|---|---|---|---|---|
| `tribes.php` | `/tribe` | GET/POST | 13-guilds | Tribe overview. |
| `tribeadmin.php` | `/tribe/admin` | GET/POST | 13-guilds | Tribe administration. |
| `tribearmor.php` | `/tribe/armory` | GET/POST | 13-guilds | Tribe armory. |
| `tribeastral.php` | `/tribe/astral` | GET/POST | 13-guilds | Tribe astral resources. |
| `tribeherbs.php` | `/tribe/herbs` | GET/POST | 13-guilds | Tribe herb storage. |
| `tribeminerals.php` | `/tribe/minerals` | GET/POST | 13-guilds | Tribe mineral storage. |
| `tribeware.php` | `/tribe/warehouse` | GET/POST | 13-guilds | Tribe warehouse. |
| `tforums.php` | `/tribe/forums` | GET/POST | 13-guilds | Tribe-specific forums. |
| `team.php` | `/team` | GET/POST | 13-guilds | Team management. |
| `guilds.php` | `/guilds` | GET | 13-guilds | Guild listing. |
| `guilds2.php` | `/guilds/detail` | GET/POST | 13-guilds | Guild detail view. |
| `house.php` | `/house` | GET/POST | 13-guilds | Player house. |

### Authenticated Routes — Module 14: Quests, Missions & Events

| PHP File | Planned Route | Method | Module | Notes |
|---|---|---|---|---|
| (quests routed via other pages) | `/quest` | GET/POST | 14-quests | Quest system. |

### Staff Routes (elevated rank required)

| PHP File | Planned Route | Method | Rank Required | Module | Notes |
|---|---|---|---|---|---|
| `staff.php` | `/staff` | GET/POST | Staff, Admin, Budowniczy | 15-admin | Staff panel. |
| `addnews.php` | `/staff/news` | GET/POST | Staff, Admin, Kronikarz | 15-admin | Add news. |
| `addupdate.php` | `/staff/updates` | GET/POST | Staff, Admin | 15-admin | Add updates. |
| `stafflist.php` | `/staff/list` | GET | authenticated | 15-admin | Staff listing (public-ish). |
| `bugtrack.php` | `/bugs` | GET/POST | authenticated (submit), Staff (manage) | 15-admin | Bug tracker. |
| `log.php` | `/log` | GET | Staff, Admin | 15-admin | Activity log. |
| `jail.php` | `/jail` | GET/POST | Staff, Admin | 15-admin | Jail management. |
| `court.php` | `/court` | GET/POST | authenticated (view), Sędzia (manage) | 15-admin | Court system. |
| `sedzia.php` | `/judge` | GET/POST | Sędzia | 15-admin | Judge panel. |

### Admin Routes (Admin rank only)

| PHP File | Planned Route | Method | Module | Notes |
|---|---|---|---|---|
| `admin.php` | `/admin` | GET/POST | 15-admin | Admin panel. |

### Non-Web / Will Not Migrate

| PHP File | Reason |
|---|---|
| `source.php` | Source code viewer — no Rust equivalent, not needed. |

---

## Summary

| Category | Count |
|---|---|
| Public | 7 |
| Special (AJAX/partial) | 3 |
| Authenticated | ~89 |
| Staff | 9 |
| Admin | 1 |
| Not migrated | 1 |
| **Total** | **110** |

## Migration Priority Order

1. **Phase A — Public routes**: index, register, activate, reset, rss, logout (Module 05)
2. **Phase B — Core game loop**: city, map, travel, battle, equip, stats, train (Modules 06-08)
3. **Phase C — Economy & crafting**: bank, markets, mines, smithy, crafts (Modules 10-11)
4. **Phase D — Social**: chat, mail, forums, news, library (Module 12)
5. **Phase E — Tribes & guilds**: tribes, team, guilds (Module 13)
6. **Phase F — Admin & moderation**: staff, admin, judge, jail, court (Module 15)
