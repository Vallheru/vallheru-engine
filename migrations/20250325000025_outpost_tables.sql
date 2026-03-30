-- Outpost (garrison/watchtower) tables.
-- Covers: outposts, outpost_monsters (beasts), outpost_veterans (equipped NPCs),
-- and the core (pets) table needed for beast assignment.

-- Player-owned pets (chowańce).
CREATE TABLE IF NOT EXISTS core (
    id        SERIAL       PRIMARY KEY,
    owner     INTEGER      NOT NULL DEFAULT 0,
    name      VARCHAR(20)  NOT NULL DEFAULT '',
    type      VARCHAR(20)  NOT NULL DEFAULT '',
    ref_id    INTEGER      NOT NULL DEFAULT 0,
    power     DOUBLE PRECISION NOT NULL DEFAULT 0.0,
    defense   DOUBLE PRECISION NOT NULL DEFAULT 0.0,
    status    VARCHAR(5)   NOT NULL DEFAULT 'Alive',
    active    CHAR(1)      NOT NULL DEFAULT 'N',
    corename  VARCHAR(30)  NOT NULL DEFAULT '',
    gender    CHAR(1)      NOT NULL DEFAULT '',
    wins      INTEGER      NOT NULL DEFAULT 0,
    losses    INTEGER      NOT NULL DEFAULT 0
);
CREATE INDEX IF NOT EXISTS idx_core_owner ON core (owner);
CREATE INDEX IF NOT EXISTS idx_core_name ON core (name);

-- Player-owned outposts.
CREATE TABLE IF NOT EXISTS outposts (
    id         SERIAL       PRIMARY KEY,
    owner      INTEGER      NOT NULL DEFAULT 0,
    size       INTEGER      NOT NULL DEFAULT 1,
    warriors   INTEGER      NOT NULL DEFAULT 0,
    archers    INTEGER      NOT NULL DEFAULT 0,
    catapults  INTEGER      NOT NULL DEFAULT 0,
    barricades INTEGER      NOT NULL DEFAULT 0,
    gold       INTEGER      NOT NULL DEFAULT 500,
    turns      INTEGER      NOT NULL DEFAULT 3,
    battack    SMALLINT     NOT NULL DEFAULT 0,
    bdefense   SMALLINT     NOT NULL DEFAULT 0,
    btax       SMALLINT     NOT NULL DEFAULT 0,
    blost      SMALLINT     NOT NULL DEFAULT 0,
    bcost      SMALLINT     NOT NULL DEFAULT 0,
    fence      INTEGER      NOT NULL DEFAULT 0,
    barracks   INTEGER      NOT NULL DEFAULT 0,
    fatigue    INTEGER      NOT NULL DEFAULT 100,
    morale     DOUBLE PRECISION NOT NULL DEFAULT 0.0,
    attacks    SMALLINT     NOT NULL DEFAULT 0
);
CREATE INDEX IF NOT EXISTS idx_outposts_owner ON outposts (owner);

-- Beasts assigned to an outpost (from pets/core).
CREATE TABLE IF NOT EXISTS outpost_monsters (
    id       SERIAL   PRIMARY KEY,
    outpost  INTEGER  NOT NULL DEFAULT 0 REFERENCES outposts(id) ON DELETE CASCADE,
    name     VARCHAR(50) NOT NULL DEFAULT '',
    power    INTEGER  NOT NULL DEFAULT 0,
    defense  INTEGER  NOT NULL DEFAULT 0
);
CREATE INDEX IF NOT EXISTS idx_outpost_monsters_outpost ON outpost_monsters (outpost);

-- Veteran soldiers assigned to an outpost with equipment.
CREATE TABLE IF NOT EXISTS outpost_veterans (
    id       SERIAL       PRIMARY KEY,
    outpost  INTEGER      NOT NULL DEFAULT 0 REFERENCES outposts(id) ON DELETE CASCADE,
    name     VARCHAR(20)  NOT NULL DEFAULT '',
    weapon   VARCHAR(60),
    wpower   INTEGER      NOT NULL DEFAULT 0,
    armor    VARCHAR(60),
    apower   INTEGER      NOT NULL DEFAULT 0,
    helm     VARCHAR(60),
    hpower   INTEGER      NOT NULL DEFAULT 0,
    legs     VARCHAR(60),
    lpower   INTEGER      NOT NULL DEFAULT 0,
    ring1    VARCHAR(60),
    rpower1  INTEGER      NOT NULL DEFAULT 0,
    ring2    VARCHAR(60),
    rpower2  INTEGER      NOT NULL DEFAULT 0,
    arrows   VARCHAR(60),
    opower   INTEGER      NOT NULL DEFAULT 0
);
CREATE INDEX IF NOT EXISTS idx_outpost_veterans_outpost ON outpost_veterans (outpost);
