# Player Field Normalization Strategy

Produced for MP-02-03. Defines how legacy serialized player columns migrate
to PostgreSQL-friendly structures.

## Current Legacy Format

The MySQL `players` table stores four serialized-string columns:

### `settings` (varchar 1024)
Format: `key:value;key:value;...`
Default: `style:light.css;graphic:;graphbar:N;forumcats:All;autodrink:N;rinvites:Y;battlelog:N;`

Known keys: `style`, `graphic`, `graphbar`, `forumcats`, `autodrink`, `rinvites`, `battlelog`, `oldchat`.

**Decision: JSONB column on players table.**
Rationale: These are UI preferences with low query filtering requirements. The set of keys is small, semi-open, and rarely used in WHERE clauses. JSONB keeps them in one place without needing a join table, and `serde_json` makes Rust access straightforward.

### `stats` (varchar 2048)
Format: `key:Label,base,trained,modified;...`
Default: `strength:Siła,0,0,0;agility:Zręczność,0,0,0;condition:Kondycja,0,0,0;speed:Szybkość,0,0,0;inteli:Inteligencja,0,0,0;wisdom:Siła Woli,0,0,0;`

Fixed keys (6): `strength`, `agility`, `condition`, `speed`, `inteli`, `wisdom`.
Array indices: `[0]` = display label, `[1]` = base value, `[2]` = trained value, `[3]` = modified value.

**Decision: Normalized `player_stats` table.**
Rationale: Stats are used in formula calculations, filtering (hall of fame, comparisons), and are updated by combat, training, and leveling. Normalization enables SQL aggregation, indexing, and simpler updates. The set of stat types is fixed (6 stats).

Schema:
```sql
CREATE TABLE player_stats (
    player_id  INTEGER NOT NULL REFERENCES players(id) ON DELETE CASCADE,
    stat_key   VARCHAR(20) NOT NULL,  -- strength, agility, etc.
    label      VARCHAR(40) NOT NULL,  -- display label (Polish)
    base       INTEGER NOT NULL DEFAULT 0,
    trained    INTEGER NOT NULL DEFAULT 0,
    modified   INTEGER NOT NULL DEFAULT 0,
    PRIMARY KEY (player_id, stat_key)
);
```

### `skills` (varchar 4096)
Format: `key:Label,level,xp;...`
Default: `smith:Kowalstwo,1,0;shoot:Strzelectwo,1,0;alchemy:Alchemia,1,0;dodge:Uniki,1,0;carpentry:Stolarstwo,1,0;magic:Rzucanie Czarów,1,0;attack:Walka Bronią,1,0;leadership:Dowodzenie,1,0;breeding:Hodowla,1,0;mining:Górnictwo,1,0;lumberjack:Drwalnictwo,1,0;herbalism:Zielarstwo,1,0;jewellry:Jubilerstwo,1,0;smelting:Hutnictwo,1,0;thievery:Złodziejstwo,1,0;perception:Spostrzegawczość,1,0;`

Fixed keys (16): `smith`, `shoot`, `alchemy`, `dodge`, `carpentry`, `magic`, `attack`, `leadership`, `breeding`, `mining`, `lumberjack`, `herbalism`, `jewellry`, `smelting`, `thievery`, `perception`.
Array indices: `[0]` = display label, `[1]` = skill level, `[2]` = XP.

**Decision: Normalized `player_skills` table.**
Rationale: Skills drive crafting checks, combat calculations, and prerequisite gates. Level comparisons are common. Normalization enables SQL-level filtering and formula participation.

Schema:
```sql
CREATE TABLE player_skills (
    player_id  INTEGER NOT NULL REFERENCES players(id) ON DELETE CASCADE,
    skill_key  VARCHAR(20) NOT NULL,  -- smith, shoot, etc.
    label      VARCHAR(60) NOT NULL,  -- display label (Polish)
    level      INTEGER NOT NULL DEFAULT 1,
    xp         INTEGER NOT NULL DEFAULT 0,
    PRIMARY KEY (player_id, skill_key)
);
```

### `bonuses` (text)
Format: `name,value,duration;name,value,duration;...`
Empty string when no bonuses active.

**Decision: Normalized `player_bonuses` table.**
Rationale: Bonuses are dynamically accumulated and consumed. They need expiry checking, stacking rules, and addition/removal by multiple systems (combat, items, quests, deity). A table with per-row entries is the natural fit.

Schema:
```sql
CREATE TABLE player_bonuses (
    id         SERIAL PRIMARY KEY,
    player_id  INTEGER NOT NULL REFERENCES players(id) ON DELETE CASCADE,
    bonus_name VARCHAR(60) NOT NULL,
    value      INTEGER NOT NULL DEFAULT 0,
    duration   INTEGER NOT NULL DEFAULT 0
);

CREATE INDEX idx_player_bonuses_player ON player_bonuses (player_id);
```

## Migration / Import Rules

1. **Parse on import**: A Rust import function parses the legacy semicolon/comma format and inserts rows into the normalized tables.
2. **settings** → Parse with `split(';')` then `split(':')`, store as JSONB.
3. **stats** → Parse each `key:label,base,trained,modified` entry, insert into `player_stats`.
4. **skills** → Parse each `key:label,level,xp` entry, insert into `player_skills`.
5. **bonuses** → Parse each `name,value,duration` triplet, insert into `player_bonuses`.
6. After import, the `_raw` TEXT columns on the `players` table can be dropped in a follow-up migration once all code paths use the normalized tables.

## Rust Domain Types

In `crates/domain`, define:
- `PlayerSettings` — typed struct with known fields (serde-friendly for JSONB).
- `PlayerStat` — `{ stat_key, label, base, trained, modified }`.
- `PlayerSkill` — `{ skill_key, label, level, xp }`.
- `PlayerBonus` — `{ bonus_name, value, duration }`.

These types are separate from the sqlx row structs in `crates/data`.

## Impact on Derived-Stat Calculations

The PHP `curstats()` method computes effective stats from base + equipment bonuses + skill bonuses. In Rust, this becomes a domain function that takes `Vec<PlayerStat>`, equipment data, and active bonuses, and returns effective stats. All source data is available from the normalized tables.
