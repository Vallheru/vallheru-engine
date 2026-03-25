# Table Ownership Map

Produced for MP-02-01. Every legacy table from `install/db/mysql.sql` (112 tables)
is assigned to exactly one migration module owner.

## Legend

- **Owner**: Module responsible for creating the PostgreSQL equivalent and primary read/write queries.
- **Shared readers**: Other modules that SELECT from this table.
- **Hotspot notes**: Tables with writes from many routes — coordination needed.

---

## Module 05 — Auth, Accounts, and Sessions

| Table | Notes |
|---|---|
| `players` | **Central hotspot** — nearly every module reads; writes from auth, progression, combat, economy, guilds. Owner manages schema, shared modules use coordinated queries. |
| `sessions` | PHP file-based sessions replacement. Will become `tower-sessions` PostgreSQL store. |
| `aktywacja` | Account activation tokens. |
| `lost_pass` | Password reset tokens. |
| `ban` | Account-level bans. |
| `settings` | Global game settings (open/closed, etc.). Shared read from most modules. |
| `log` | General game activity log. Writes from many modules. |
| `logs` | Secondary log table. |
| `donators` | Donor/premium accounts. |
| `referrals` | — (no table found, handled in PHP logic) |

## Module 06 — Player State and Progression

| Table | Notes |
|---|---|
| `bonuses` | Player bonus effects. |
| `halloffame` | Hall of fame rankings. |
| `halloffame2` | Secondary hall of fame. |
| `reset` | Era reset tracking. |

## Module 07 — World Map, Travel, and Locations

| Table | Notes |
|---|---|
| `rooms` | Location/room definitions. |
| `bridge` | Bridge/path connections. |
| `links` | Navigation links between rooms. |

## Module 08 — Combat, Encounters, and Battle

| Table | Notes |
|---|---|
| `monsters` | Monster catalog — read by combat, outposts. |
| `attacks` | Attack records. |
| `battlelogs` | Battle log history. |
| `brecords` | Battle records/stats. |
| `events` | Random encounter events. |
| `revent` | Random event instances. |
| `outpost_monsters` | Outpost-specific monster spawns. |

## Module 09 — Items, Inventory, and Equipment

| Table | Notes |
|---|---|
| `equipment` | Player inventory/equipment. |
| `bows` | Bow item catalog. |
| `czary` | Spell/magic items. |
| `mage_items` | Mage-specific items. |
| `rings` | Ring items. |
| `tools` | Tool items. |
| `potions` | Potion items. |
| `warehouse` | Warehouse storage. |

## Module 10 — Economy, Markets, and Banking

| Table | Notes |
|---|---|
| `amarket` | Alchemy market listings. |
| `core_market` | Core market listings. |
| `hmarket` | Herb market listings. |
| `pmarket` | Potion market listings. |
| `vallars` | Premium currency. |

## Module 11 — Crafting, Gathering, and Workshops

| Table | Notes |
|---|---|
| `alchemy_mill` | Alchemy mill state. |
| `core` | Crafting core items. |
| `cores` | Core inventory. |
| `farm` | Farm plot state. |
| `farms` | Farm definitions. |
| `herbs` | Herb inventory/catalog. |
| `jeweller` | Jeweller workshop state. |
| `jeweller_work` | Jeweller work queue. |
| `lumberjack` | Lumberjack profession state. |
| `mill` | Mill state. |
| `mill_work` | Mill work queue. |
| `minerals` | Mineral inventory/catalog. |
| `mines` | Mine state. |
| `mines_search` | Mine search progress. |
| `plans` | Crafting plans/recipes. |
| `smelter` | Smelter state. |
| `smith` | Smithing state/catalog. |
| `smith_work` | Smith work queue. |

## Module 12 — Social, Chat, Mail, and Content

| Table | Notes |
|---|---|
| `chat` | Chat messages. |
| `chat_config` | Chat configuration. |
| `chatrooms` | Chat room definitions. |
| `contacts` | Player contact list. |
| `mail` | In-game mail. |
| `categories` | Forum categories. |
| `topics` | Forum topics. |
| `replies` | Forum replies. |
| `bad_words` | Chat/content filter words. |
| `ignored` | Player ignore list. |
| `library` | Player-authored library entries. |
| `lib_comments` | Library comments. |
| `news` | Game news posts. |
| `news_comments` | News comments. |
| `newspaper` | In-game newspaper entries. |
| `newspaper_comments` | Newspaper comments. |
| `notatnik` | Player notepad. |
| `polls` | Polls. |
| `polls_comments` | Poll comments/votes. |
| `changelog` | Public changelog. |
| `updates` | Update posts. |
| `upd_comments` | Update comments. |
| `proposals` | Player proposals. |
| `ban_forum` | Forum-specific bans. |
| `ban_mail` | Mail-specific bans. |

## Module 13 — Guilds, Tribes, Teams, and Outposts

| Table | Notes |
|---|---|
| `tribes` | Tribe definitions. |
| `tribe_herbs` | Tribe herb storage. |
| `tribe_mag` | Tribe magic storage. |
| `tribe_minerals` | Tribe mineral storage. |
| `tribe_oczek` | Tribe training/stats. |
| `tribe_perm` | Tribe permissions. |
| `tribe_rank` | Tribe ranks. |
| `tribe_replies` | Tribe forum replies. |
| `tribe_reserv` | Tribe reservations. |
| `tribe_topics` | Tribe forum topics. |
| `tribe_zbroj` | Tribe armory. |
| `teams` | Team definitions. |
| `outposts` | Outpost locations. |
| `outpost_veterans` | Outpost veteran records. |

## Module 14 — Quests, Missions, and Events

| Table | Notes |
|---|---|
| `quests` | Quest definitions. |
| `questaction` | Quest action/step state. |
| `missions` | Mission definitions. |
| `missions2` | Extended mission data. |
| `mactions` | Mission action state. |

## Module 15 — Admin, Moderation, and Runtime Operations

| Table | Notes |
|---|---|
| `bugtrack` | Bug reports. |
| `bugreport` | Bug report details. |
| `bug_comments` | Bug report comments. |
| `court` | In-game court system. |
| `court_cases` | Court case records. |
| `jail` | Jail records. |
| `slog` | Staff/admin log. |
| `slogconf` | Staff log configuration. |
| `adodb_logsql` | ADOdb SQL logging (legacy — will not be migrated). |

## Module 08/09 — Astral (Combat + Items cross-module)

| Table | Notes |
|---|---|
| `astral` | Astral plane state. |
| `astral_bank` | Astral banking. |
| `astral_machine` | Astral machines. |
| `astral_plans` | Astral crafting plans. |

## Cross-Module Hotspots

| Table | Primary owner | Heavy writers |
|---|---|---|
| `players` | Module 05 (auth) | 06 (progression), 08 (combat rewards), 09 (equip), 10 (economy), 11 (crafting), 13 (tribes) |
| `settings` | Module 05 (auth) | 15 (admin) |
| `log` | Module 05 (auth) | Nearly all modules append logs |
| `equipment` | Module 09 (items) | 08 (combat loot), 10 (market trades), 11 (crafting output) |
| `monsters` | Module 08 (combat) | 15 (admin catalog edits) |

## Tables Not Migrated

| Table | Reason |
|---|---|
| `adodb_logsql` | ADOdb internal logging — replaced by `tracing`. |

---

**112 tables assigned. 1 table explicitly skipped (adodb_logsql). 111 tables mapped to modules.**
