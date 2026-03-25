-- Reference catalog tables — stable, mostly read-only data.
-- These tables hold game content definitions seeded from the legacy MySQL data.

-- Monster catalog.
CREATE TABLE IF NOT EXISTS monsters (
    id         SERIAL PRIMARY KEY,
    name       VARCHAR(45)    NOT NULL,
    level      INTEGER        NOT NULL DEFAULT 0,
    hp         INTEGER        NOT NULL DEFAULT 0,
    agility    DOUBLE PRECISION NOT NULL DEFAULT 0.0,
    strength   DOUBLE PRECISION NOT NULL DEFAULT 0.0,
    speed      DOUBLE PRECISION NOT NULL DEFAULT 0.0,
    endurance  DOUBLE PRECISION NOT NULL DEFAULT 0.0,
    location   VARCHAR(20)    NOT NULL DEFAULT 'Altara',
    lootnames  TEXT           NOT NULL DEFAULT '',
    lootchances VARCHAR(255)  NOT NULL DEFAULT '',
    description TEXT          NOT NULL DEFAULT '',
    resistance VARCHAR(100)   NOT NULL DEFAULT 'none;none',
    dmgtype    VARCHAR(20)    NOT NULL DEFAULT 'none'
);

CREATE INDEX IF NOT EXISTS idx_monsters_level    ON monsters (level);
CREATE INDEX IF NOT EXISTS idx_monsters_location ON monsters (location);

-- Bow catalog (ranged weapon definitions).
CREATE TABLE IF NOT EXISTS bows (
    id      SERIAL PRIMARY KEY,
    name    VARCHAR(60) NOT NULL DEFAULT '',
    power   INTEGER     NOT NULL DEFAULT 0,
    type    CHAR(1)     NOT NULL DEFAULT 'B',
    cost    INTEGER     NOT NULL DEFAULT 0,
    minlev  INTEGER     NOT NULL DEFAULT 1,
    zr      INTEGER     NOT NULL DEFAULT 0,
    szyb    INTEGER     NOT NULL DEFAULT 0,
    maxwt   INTEGER     NOT NULL DEFAULT 0,
    lang    VARCHAR(3)  NOT NULL DEFAULT 'pl',
    repair  INTEGER     NOT NULL DEFAULT 10
);

CREATE INDEX IF NOT EXISTS idx_bows_type ON bows (type);

-- Ring catalog.
CREATE TABLE IF NOT EXISTS rings (
    id     SERIAL PRIMARY KEY,
    name   VARCHAR(60) NOT NULL DEFAULT '',
    amount INTEGER     NOT NULL DEFAULT 0,
    lang   VARCHAR(2)  NOT NULL DEFAULT 'pl'
);

-- Tool catalog.
CREATE TABLE IF NOT EXISTS tools (
    name   VARCHAR(50) NOT NULL,
    level  INTEGER     NOT NULL DEFAULT 1,
    power  INTEGER     NOT NULL DEFAULT 0,
    dur    INTEGER     NOT NULL DEFAULT 10,
    repair INTEGER     NOT NULL DEFAULT 20,
    type   CHAR(1)     NOT NULL DEFAULT 'T',
    PRIMARY KEY (name)
);

-- Crafting plan catalog.
CREATE TABLE IF NOT EXISTS plans (
    name   VARCHAR(255) NOT NULL,
    level  INTEGER      NOT NULL DEFAULT 1,
    amount INTEGER      NOT NULL DEFAULT 2,
    type   CHAR(1)      NOT NULL DEFAULT 'T',
    PRIMARY KEY (name)
);

-- Player bonus definitions.
CREATE TABLE IF NOT EXISTS bonuses (
    id        SERIAL PRIMARY KEY,
    name      VARCHAR(60) NOT NULL,
    "desc"    TEXT        NOT NULL DEFAULT '',
    cost      INTEGER     NOT NULL DEFAULT 0,
    levels    SMALLINT    NOT NULL DEFAULT 0,
    "trigger" VARCHAR(255) NOT NULL DEFAULT '',
    bonus     SMALLINT    NOT NULL DEFAULT 0,
    race      VARCHAR(255) NOT NULL DEFAULT '',
    clas      VARCHAR(255) NOT NULL DEFAULT ''
);
