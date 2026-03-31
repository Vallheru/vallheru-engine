-- Workshop catalog tables: alchemy recipes, bow/arrow plans, creature definitions.
-- These reference catalogs (owner = 0) and player-owned copies (owner > 0)
-- are needed by the crafting workshop handlers.

-- Alchemy recipe catalog. owner = 0 for catalog, owner > 0 for player copy.
CREATE TABLE IF NOT EXISTS alchemy_mill (
    id         SERIAL PRIMARY KEY,
    name       VARCHAR(60)  NOT NULL DEFAULT '',
    owner      INTEGER      NOT NULL DEFAULT 0,
    illani     INTEGER      NOT NULL DEFAULT 0,
    illanias   INTEGER      NOT NULL DEFAULT 0,
    nutari     INTEGER      NOT NULL DEFAULT 0,
    cost       INTEGER      NOT NULL DEFAULT 0,
    level      SMALLINT     NOT NULL DEFAULT 0,
    status     CHAR(1)      NOT NULL DEFAULT 'S',
    dynallca   INTEGER      NOT NULL DEFAULT 0,
    lang       VARCHAR(3)   NOT NULL DEFAULT 'pl'
);
CREATE INDEX IF NOT EXISTS idx_alchemy_mill_owner ON alchemy_mill (owner);

-- Bow/arrow plan catalog. owner = 0 for catalog, owner > 0 for player copy.
CREATE TABLE IF NOT EXISTS mill (
    id         SERIAL PRIMARY KEY,
    owner      INTEGER      NOT NULL DEFAULT 0,
    name       VARCHAR(60)  NOT NULL DEFAULT '',
    type       CHAR(1)      NOT NULL DEFAULT '',
    cost       INTEGER      NOT NULL DEFAULT 0,
    amount     INTEGER      NOT NULL DEFAULT 0,
    level      SMALLINT     NOT NULL DEFAULT 0,
    lang       VARCHAR(2)   NOT NULL DEFAULT 'pl',
    twohand    CHAR(1)      NOT NULL DEFAULT 'N',
    elite      INTEGER      NOT NULL DEFAULT 0,
    elitetype  VARCHAR(1)   NOT NULL DEFAULT 'S'
);
CREATE INDEX IF NOT EXISTS idx_mill_owner ON mill (owner);

-- Bow/arrow work-in-progress.
CREATE TABLE IF NOT EXISTS mill_work (
    id         SERIAL PRIMARY KEY,
    owner      INTEGER      NOT NULL DEFAULT 0,
    name       VARCHAR(60)  NOT NULL DEFAULT '',
    n_energy   SMALLINT     NOT NULL DEFAULT 0,
    u_energy   SMALLINT     NOT NULL DEFAULT 0,
    mineral    VARCHAR(10)  NOT NULL DEFAULT '',
    elite      INTEGER      NOT NULL DEFAULT 0
);
CREATE INDEX IF NOT EXISTS idx_mill_work_owner ON mill_work (owner);

-- Creature type definitions (reference catalog).
CREATE TABLE IF NOT EXISTS cores (
    id       SERIAL PRIMARY KEY,
    name     VARCHAR(20)  NOT NULL DEFAULT '',
    type     VARCHAR(20)  NOT NULL DEFAULT '',
    power    DOUBLE PRECISION NOT NULL DEFAULT 1.0,
    defense  DOUBLE PRECISION NOT NULL DEFAULT 1.0,
    rarity   SMALLINT     NOT NULL DEFAULT 1,
    descr    TEXT         NOT NULL DEFAULT '',
    lang     VARCHAR(3)   NOT NULL DEFAULT 'pl'
);
