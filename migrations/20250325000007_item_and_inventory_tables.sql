-- Item and inventory tables for module 09.
-- Covers: equipment (owned items), spells (czary), potions, mage item catalog.
-- Bows and rings catalog tables already exist in 20250325000003.

-- Equipment table — player-owned items and shop stock templates.
-- owner = 0 means shop stock; owner > 0 means player-owned.
CREATE TABLE IF NOT EXISTS equipment (
    id              SERIAL PRIMARY KEY,
    owner           INTEGER         NOT NULL DEFAULT 0,
    name            VARCHAR(100)    NOT NULL DEFAULT '',
    power           INTEGER         NOT NULL DEFAULT 0,
    status          CHAR(1)         NOT NULL DEFAULT 'U',
    type            CHAR(1)         NOT NULL DEFAULT 'W',
    cost            BIGINT          NOT NULL DEFAULT 0,
    minlev          INTEGER         NOT NULL DEFAULT 1,
    zr              INTEGER         NOT NULL DEFAULT 0,
    wt              INTEGER         NOT NULL DEFAULT 0,
    szyb            INTEGER         NOT NULL DEFAULT 0,
    maxwt           INTEGER         NOT NULL DEFAULT 0,
    magic           CHAR(1)         NOT NULL DEFAULT 'N',
    poison          INTEGER         NOT NULL DEFAULT 0,
    amount          INTEGER         NOT NULL DEFAULT 1,
    twohand         CHAR(1)         NOT NULL DEFAULT 'N',
    lang            VARCHAR(3)      NOT NULL DEFAULT 'pl',
    ptype           CHAR(1)         NOT NULL DEFAULT '',
    repair          INTEGER         NOT NULL DEFAULT 10,
    location        VARCHAR(20)     NOT NULL DEFAULT 'Altara'
);

CREATE INDEX IF NOT EXISTS idx_equipment_owner  ON equipment (owner);
CREATE INDEX IF NOT EXISTS idx_equipment_status ON equipment (status);
CREATE INDEX IF NOT EXISTS idx_equipment_type   ON equipment (type);
CREATE INDEX IF NOT EXISTS idx_equipment_minlev ON equipment (minlev);

-- Spells table (czary) — catalog entries (owner = 0) and player-owned spells.
CREATE TABLE IF NOT EXISTS spells (
    id              SERIAL PRIMARY KEY,
    nazwa           VARCHAR(30)     NOT NULL DEFAULT '',
    gracz           INTEGER         NOT NULL DEFAULT 0,
    cena            BIGINT          NOT NULL DEFAULT 0,
    poziom          INTEGER         NOT NULL DEFAULT 1,
    typ             CHAR(1)         NOT NULL DEFAULT 'B',
    obr             DOUBLE PRECISION NOT NULL DEFAULT 1.0,
    status          CHAR(1)         NOT NULL DEFAULT 'S',
    element         VARCHAR(20)     NOT NULL DEFAULT ''
);

CREATE INDEX IF NOT EXISTS idx_spells_gracz ON spells (gracz);

-- Potions table — catalog entries and player-owned potions.
CREATE TABLE IF NOT EXISTS potions (
    id              SERIAL PRIMARY KEY,
    owner           INTEGER         NOT NULL DEFAULT 0,
    name            VARCHAR(80)     NOT NULL DEFAULT '',
    type            CHAR(1)         NOT NULL DEFAULT '',
    efect           VARCHAR(30)     NOT NULL DEFAULT '',
    status          CHAR(1)         NOT NULL DEFAULT 'S',
    power           INTEGER         NOT NULL DEFAULT 100,
    amount          INTEGER         NOT NULL DEFAULT 0,
    lang            VARCHAR(3)      NOT NULL DEFAULT 'pl',
    cost            BIGINT          NOT NULL DEFAULT 0
);

CREATE INDEX IF NOT EXISTS idx_potions_owner ON potions (owner);

-- Mage item catalog — base definitions for wands and mage clothing.
-- Player instances are stored in the equipment table with type T or C.
CREATE TABLE IF NOT EXISTS mage_items (
    id              SERIAL PRIMARY KEY,
    name            VARCHAR(60)     NOT NULL DEFAULT '',
    power           INTEGER         NOT NULL DEFAULT 0,
    type            CHAR(1)         NOT NULL DEFAULT 'B',
    cost            BIGINT          NOT NULL DEFAULT 0,
    minlev          INTEGER         NOT NULL DEFAULT 1,
    lang            VARCHAR(3)      NOT NULL DEFAULT 'pl'
);

CREATE INDEX IF NOT EXISTS idx_mage_items_type ON mage_items (type);
