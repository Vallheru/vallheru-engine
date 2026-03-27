-- Market tables for module 10: player-to-player market listings.
-- Covers: pmarket (minerals), hmarket (herbs), amarket (astral), core_market (pets).
-- Equipment, Potions, and Jewellery/Loot markets reuse the equipment/potions tables
-- with status='R' convention.

-- Mineral market listings.
CREATE TABLE IF NOT EXISTS pmarket (
    id              SERIAL PRIMARY KEY,
    seller          INTEGER         NOT NULL DEFAULT 0,
    ilosc           INTEGER         NOT NULL DEFAULT 0,
    cost            BIGINT          NOT NULL DEFAULT 0,
    nazwa           VARCHAR(20)     NOT NULL DEFAULT 'mithril',
    lang            VARCHAR(3)      NOT NULL DEFAULT 'pl'
);

CREATE INDEX IF NOT EXISTS idx_pmarket_seller ON pmarket (seller);
CREATE INDEX IF NOT EXISTS idx_pmarket_nazwa  ON pmarket (nazwa);
CREATE INDEX IF NOT EXISTS idx_pmarket_cost   ON pmarket (cost);

-- Herb market listings.
CREATE TABLE IF NOT EXISTS hmarket (
    id              SERIAL PRIMARY KEY,
    seller          INTEGER         NOT NULL DEFAULT 0,
    ilosc           INTEGER         NOT NULL DEFAULT 0,
    cost            BIGINT          NOT NULL DEFAULT 0,
    nazwa           VARCHAR(30)     NOT NULL DEFAULT '',
    lang            VARCHAR(3)      NOT NULL DEFAULT 'pl'
);

CREATE INDEX IF NOT EXISTS idx_hmarket_seller ON hmarket (seller);
CREATE INDEX IF NOT EXISTS idx_hmarket_nazwa  ON hmarket (nazwa);

-- Astral market listings.
CREATE TABLE IF NOT EXISTS amarket (
    id              SERIAL PRIMARY KEY,
    seller          INTEGER         NOT NULL DEFAULT 0,
    type            VARCHAR(2)      NOT NULL DEFAULT '',
    number          SMALLINT        NOT NULL DEFAULT 0,
    amount          INTEGER         NOT NULL DEFAULT 1,
    cost            BIGINT          NOT NULL DEFAULT 0
);

CREATE INDEX IF NOT EXISTS idx_amarket_seller ON amarket (seller);
CREATE INDEX IF NOT EXISTS idx_amarket_type   ON amarket (type);

-- Astral inventory (player-owned astral items).
CREATE TABLE IF NOT EXISTS astral (
    owner           INTEGER         NOT NULL DEFAULT 0,
    type            VARCHAR(2)      NOT NULL DEFAULT '',
    number          SMALLINT        NOT NULL DEFAULT 0,
    amount          INTEGER         NOT NULL DEFAULT 1,
    location        CHAR(1)         NOT NULL DEFAULT 'V'
);

CREATE INDEX IF NOT EXISTS idx_astral_owner ON astral (owner);
CREATE INDEX IF NOT EXISTS idx_astral_type  ON astral (type);

-- Core (pet) market listings.
CREATE TABLE IF NOT EXISTS core_market (
    id              SERIAL PRIMARY KEY,
    name            VARCHAR(20)     NOT NULL DEFAULT '',
    cost            BIGINT          NOT NULL DEFAULT 0,
    seller          INTEGER         NOT NULL DEFAULT 0,
    type            VARCHAR(20)     NOT NULL DEFAULT '',
    power           DOUBLE PRECISION NOT NULL DEFAULT 0.0,
    defense         DOUBLE PRECISION NOT NULL DEFAULT 0.0,
    gender          CHAR(1)         NOT NULL DEFAULT '',
    ref_id          INTEGER         NOT NULL DEFAULT 0,
    wins            INTEGER         NOT NULL DEFAULT 0,
    losses          INTEGER         NOT NULL DEFAULT 0
);

CREATE INDEX IF NOT EXISTS idx_core_market_seller ON core_market (seller);
