-- Crafting workshop, astral bank/plans, and tribe pending membership tables.
-- These are referenced by character_reset and various crafting/astral handlers.

-- Smithing finished products (player-owned forged weapons/armor).
CREATE TABLE IF NOT EXISTS smith (
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
CREATE INDEX IF NOT EXISTS idx_smith_owner ON smith (owner);

-- Smithing work-in-progress.
CREATE TABLE IF NOT EXISTS smith_work (
    id         SERIAL PRIMARY KEY,
    owner      INTEGER      NOT NULL DEFAULT 0,
    name       VARCHAR(60)  NOT NULL DEFAULT '',
    n_energy   SMALLINT     NOT NULL DEFAULT 0,
    u_energy   SMALLINT     NOT NULL DEFAULT 0,
    mineral    VARCHAR(10)  NOT NULL DEFAULT '',
    elite      INTEGER      NOT NULL DEFAULT 0
);
CREATE INDEX IF NOT EXISTS idx_smith_work_owner ON smith_work (owner);

-- Jeweller finished products (player-owned crafted jewellery).
CREATE TABLE IF NOT EXISTS jeweller (
    id         SERIAL PRIMARY KEY,
    owner      INTEGER      NOT NULL DEFAULT 0,
    name       VARCHAR(60)  NOT NULL DEFAULT '',
    type       CHAR(1)      NOT NULL DEFAULT 'I',
    cost       INTEGER      NOT NULL DEFAULT 0,
    level      SMALLINT     NOT NULL DEFAULT 0,
    bonus      INTEGER      NOT NULL DEFAULT 0,
    lang       VARCHAR(3)   NOT NULL DEFAULT 'pl'
);
CREATE INDEX IF NOT EXISTS idx_jeweller_owner ON jeweller (owner);
CREATE INDEX IF NOT EXISTS idx_jeweller_name  ON jeweller (name);

-- Jeweller work-in-progress.
CREATE TABLE IF NOT EXISTS jeweller_work (
    id         SERIAL PRIMARY KEY,
    owner      INTEGER        NOT NULL DEFAULT 0,
    name       VARCHAR(100)   NOT NULL DEFAULT '',
    n_energy   DOUBLE PRECISION NOT NULL DEFAULT 0.0,
    u_energy   DOUBLE PRECISION NOT NULL DEFAULT 0.0,
    bonus      VARCHAR(30)    NOT NULL DEFAULT '',
    type       CHAR(1)        NOT NULL DEFAULT ''
);
CREATE INDEX IF NOT EXISTS idx_jeweller_work_owner ON jeweller_work (owner);

-- Astral bank (player astral levels unlocked per location).
CREATE TABLE IF NOT EXISTS astral_bank (
    owner      INTEGER  NOT NULL DEFAULT 0,
    level      SMALLINT NOT NULL DEFAULT 0,
    location   CHAR(1)  NOT NULL DEFAULT 'V'
);
CREATE INDEX IF NOT EXISTS idx_astral_bank_owner    ON astral_bank (owner);
CREATE INDEX IF NOT EXISTS idx_astral_bank_location ON astral_bank (location);

-- Astral plans (crafting blueprints for astral items).
CREATE TABLE IF NOT EXISTS astral_plans (
    owner      INTEGER      NOT NULL DEFAULT 0,
    name       VARCHAR(2)   NOT NULL DEFAULT '',
    amount     INTEGER      NOT NULL DEFAULT 0,
    location   CHAR(1)      NOT NULL DEFAULT 'V'
);
CREATE INDEX IF NOT EXISTS idx_astral_plans_owner    ON astral_plans (owner);
CREATE INDEX IF NOT EXISTS idx_astral_plans_location ON astral_plans (location);

-- Tribe pending membership requests.
CREATE TABLE IF NOT EXISTS tribe_oczek (
    id         SERIAL PRIMARY KEY,
    gracz      INTEGER NOT NULL DEFAULT 0,
    klan       INTEGER NOT NULL DEFAULT 0
);
CREATE INDEX IF NOT EXISTS idx_tribe_oczek_gracz ON tribe_oczek (gracz);
