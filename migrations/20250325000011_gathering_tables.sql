-- Gathering-related tables: minerals storage, mine deposits, mine search,
-- smelter/lumberjack levels, herbs, farm plots, and plantations.

-- Player mineral and bar storage (both ores, refined bars, and wood).
CREATE TABLE IF NOT EXISTS minerals (
    owner       INTEGER NOT NULL REFERENCES players(id) ON DELETE CASCADE,
    copperore   INTEGER NOT NULL DEFAULT 0,
    zincore     INTEGER NOT NULL DEFAULT 0,
    tinore      INTEGER NOT NULL DEFAULT 0,
    ironore     INTEGER NOT NULL DEFAULT 0,
    coal        INTEGER NOT NULL DEFAULT 0,
    copper      INTEGER NOT NULL DEFAULT 0,
    bronze      INTEGER NOT NULL DEFAULT 0,
    brass       INTEGER NOT NULL DEFAULT 0,
    iron        INTEGER NOT NULL DEFAULT 0,
    steel       INTEGER NOT NULL DEFAULT 0,
    pine        INTEGER NOT NULL DEFAULT 0,
    hazel       INTEGER NOT NULL DEFAULT 0,
    yew         INTEGER NOT NULL DEFAULT 0,
    elm         INTEGER NOT NULL DEFAULT 0,
    crystal     INTEGER NOT NULL DEFAULT 0,
    adamantium  INTEGER NOT NULL DEFAULT 0,
    meteor      INTEGER NOT NULL DEFAULT 0,
    PRIMARY KEY (owner)
);

-- Mine ore deposits owned by player (discovered by geologist).
CREATE TABLE IF NOT EXISTS mines (
    owner   INTEGER NOT NULL REFERENCES players(id) ON DELETE CASCADE,
    copper  INTEGER NOT NULL DEFAULT 0,
    zinc    INTEGER NOT NULL DEFAULT 0,
    tin     INTEGER NOT NULL DEFAULT 0,
    iron    INTEGER NOT NULL DEFAULT 0,
    coal    INTEGER NOT NULL DEFAULT 0,
    PRIMARY KEY (owner)
);

-- Active geologist search.
CREATE TABLE IF NOT EXISTS mines_search (
    player      INTEGER NOT NULL REFERENCES players(id) ON DELETE CASCADE,
    days        SMALLINT NOT NULL DEFAULT 0,
    mineral     VARCHAR(30) NOT NULL DEFAULT '',
    searchdays  SMALLINT NOT NULL DEFAULT 0,
    PRIMARY KEY (player)
);

-- Player smelter upgrade level.
CREATE TABLE IF NOT EXISTS smelter (
    owner   INTEGER NOT NULL REFERENCES players(id) ON DELETE CASCADE,
    level   SMALLINT NOT NULL DEFAULT 0,
    PRIMARY KEY (owner)
);

-- Player lumberjack license level.
CREATE TABLE IF NOT EXISTS lumberjack (
    owner   INTEGER NOT NULL REFERENCES players(id) ON DELETE CASCADE,
    level   SMALLINT NOT NULL DEFAULT 0,
    PRIMARY KEY (owner)
);

-- Player herb inventory (fresh herbs and dried seeds).
CREATE TABLE IF NOT EXISTS herbs (
    id              SERIAL PRIMARY KEY,
    gracz           INTEGER NOT NULL REFERENCES players(id) ON DELETE CASCADE,
    illani          INTEGER NOT NULL DEFAULT 0,
    illanias        INTEGER NOT NULL DEFAULT 0,
    nutari          INTEGER NOT NULL DEFAULT 0,
    dynallca        INTEGER NOT NULL DEFAULT 0,
    ilani_seeds     INTEGER NOT NULL DEFAULT 0,
    illanias_seeds  INTEGER NOT NULL DEFAULT 0,
    nutari_seeds    INTEGER NOT NULL DEFAULT 0,
    dynallca_seeds  INTEGER NOT NULL DEFAULT 0
);
CREATE INDEX IF NOT EXISTS idx_herbs_gracz ON herbs(gracz);

-- Plantations (per player per location).
CREATE TABLE IF NOT EXISTS farms (
    id          SERIAL PRIMARY KEY,
    owner       INTEGER NOT NULL REFERENCES players(id) ON DELETE CASCADE,
    lands       INTEGER NOT NULL DEFAULT 0,
    glasshouse  INTEGER NOT NULL DEFAULT 0,
    irrigation  INTEGER NOT NULL DEFAULT 0,
    creeper     INTEGER NOT NULL DEFAULT 0,
    location    VARCHAR(30) NOT NULL DEFAULT 'Altara'
);
CREATE INDEX IF NOT EXISTS idx_farms_owner ON farms(owner);

-- Individual farm plots (herb plantings).
CREATE TABLE IF NOT EXISTS farm (
    id      SERIAL PRIMARY KEY,
    farmid  INTEGER NOT NULL REFERENCES farms(id) ON DELETE CASCADE,
    amount  INTEGER NOT NULL DEFAULT 0,
    name    VARCHAR(20),
    age     INTEGER NOT NULL DEFAULT 0,
    owner   INTEGER NOT NULL REFERENCES players(id) ON DELETE CASCADE
);
CREATE INDEX IF NOT EXISTS idx_farm_farmid ON farm(farmid);
