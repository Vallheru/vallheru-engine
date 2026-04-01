-- Tribe shared storage tables: armory, warehouse (potions), herbs,
-- minerals, reservations, teams, and the astral machine tracker.

-- =========================================================================
-- tribe_zbroj (tribe armory — shared equipment)
-- =========================================================================
CREATE TABLE IF NOT EXISTS tribe_zbroj (
    id        SERIAL PRIMARY KEY,
    klan      INTEGER      NOT NULL DEFAULT 0,
    name      VARCHAR(60)  NOT NULL DEFAULT '',
    power     INTEGER      NOT NULL DEFAULT 0,
    wt        INTEGER      NOT NULL DEFAULT 0,
    maxwt     INTEGER      NOT NULL DEFAULT 0,
    zr        INTEGER      NOT NULL DEFAULT 0,
    szyb      INTEGER      NOT NULL DEFAULT 0,
    minlev    INTEGER      NOT NULL DEFAULT 0,
    type      CHAR(1)      NOT NULL DEFAULT '',
    magic     CHAR(1)      NOT NULL DEFAULT 'N',
    poison    INTEGER      NOT NULL DEFAULT 0,
    amount    INTEGER      NOT NULL DEFAULT 1,
    twohand   CHAR(1)      NOT NULL DEFAULT 'N',
    ptype     CHAR(1)      NOT NULL DEFAULT '',
    repair    INTEGER      NOT NULL DEFAULT 10,
    reserved  INTEGER      NOT NULL DEFAULT 0
);
CREATE INDEX IF NOT EXISTS idx_tribe_zbroj_klan ON tribe_zbroj (klan);

-- =========================================================================
-- tribe_mag (tribe warehouse — shared potions)
-- =========================================================================
CREATE TABLE IF NOT EXISTS tribe_mag (
    id        SERIAL PRIMARY KEY,
    owner     INTEGER      NOT NULL DEFAULT 0,
    name      VARCHAR(80)  NOT NULL DEFAULT '',
    efect     VARCHAR(30)  NOT NULL DEFAULT '',
    power     INTEGER      NOT NULL DEFAULT 0,
    amount    INTEGER      NOT NULL DEFAULT 0,
    type      CHAR(1)      NOT NULL DEFAULT '',
    reserved  INTEGER      NOT NULL DEFAULT 0
);
CREATE INDEX IF NOT EXISTS idx_tribe_mag_owner ON tribe_mag (owner);

-- =========================================================================
-- tribe_herbs (tribe herb storage — one row per tribe)
-- =========================================================================
CREATE TABLE IF NOT EXISTS tribe_herbs (
    id               INTEGER PRIMARY KEY,
    illani           INTEGER NOT NULL DEFAULT 0,
    rillani          INTEGER NOT NULL DEFAULT 0,
    illanias         INTEGER NOT NULL DEFAULT 0,
    rillanias        INTEGER NOT NULL DEFAULT 0,
    nutari           INTEGER NOT NULL DEFAULT 0,
    rnutari          INTEGER NOT NULL DEFAULT 0,
    dynallca         INTEGER NOT NULL DEFAULT 0,
    rdynallca        INTEGER NOT NULL DEFAULT 0,
    ilani_seeds      INTEGER NOT NULL DEFAULT 0,
    rilani_seeds     INTEGER NOT NULL DEFAULT 0,
    illanias_seeds   INTEGER NOT NULL DEFAULT 0,
    rillanias_seeds  INTEGER NOT NULL DEFAULT 0,
    nutari_seeds     INTEGER NOT NULL DEFAULT 0,
    rnutari_seeds    INTEGER NOT NULL DEFAULT 0,
    dynallca_seeds   INTEGER NOT NULL DEFAULT 0,
    rdynallca_seeds  INTEGER NOT NULL DEFAULT 0
);

-- =========================================================================
-- tribe_minerals (tribe mineral/wood treasury — one row per tribe)
-- =========================================================================
CREATE TABLE IF NOT EXISTS tribe_minerals (
    id           INTEGER PRIMARY KEY,
    copperore    INTEGER NOT NULL DEFAULT 0,
    rcopperore   INTEGER NOT NULL DEFAULT 0,
    zincore      INTEGER NOT NULL DEFAULT 0,
    rzincore     INTEGER NOT NULL DEFAULT 0,
    tinore       INTEGER NOT NULL DEFAULT 0,
    rtinore      INTEGER NOT NULL DEFAULT 0,
    ironore      INTEGER NOT NULL DEFAULT 0,
    rironore     INTEGER NOT NULL DEFAULT 0,
    copper       INTEGER NOT NULL DEFAULT 0,
    rcopper      INTEGER NOT NULL DEFAULT 0,
    bronze       INTEGER NOT NULL DEFAULT 0,
    rbronze      INTEGER NOT NULL DEFAULT 0,
    brass        INTEGER NOT NULL DEFAULT 0,
    rbrass       INTEGER NOT NULL DEFAULT 0,
    iron         INTEGER NOT NULL DEFAULT 0,
    riron        INTEGER NOT NULL DEFAULT 0,
    steel        INTEGER NOT NULL DEFAULT 0,
    rsteel       INTEGER NOT NULL DEFAULT 0,
    coal         INTEGER NOT NULL DEFAULT 0,
    rcoal        INTEGER NOT NULL DEFAULT 0,
    adamantium   INTEGER NOT NULL DEFAULT 0,
    radamantium  INTEGER NOT NULL DEFAULT 0,
    meteor       INTEGER NOT NULL DEFAULT 0,
    rmeteor      INTEGER NOT NULL DEFAULT 0,
    crystal      INTEGER NOT NULL DEFAULT 0,
    rcrystal     INTEGER NOT NULL DEFAULT 0,
    pine         INTEGER NOT NULL DEFAULT 0,
    rpine        INTEGER NOT NULL DEFAULT 0,
    hazel        INTEGER NOT NULL DEFAULT 0,
    rhazel       INTEGER NOT NULL DEFAULT 0,
    yew          INTEGER NOT NULL DEFAULT 0,
    ryew         INTEGER NOT NULL DEFAULT 0,
    elm          INTEGER NOT NULL DEFAULT 0,
    relm         INTEGER NOT NULL DEFAULT 0
);

-- =========================================================================
-- tribe_reserv (reservation requests across all storage areas)
-- =========================================================================
CREATE TABLE IF NOT EXISTS tribe_reserv (
    id      SERIAL PRIMARY KEY,
    iid     INTEGER  NOT NULL DEFAULT 0,
    pid     INTEGER  NOT NULL DEFAULT 0,
    amount  INTEGER  NOT NULL DEFAULT 0,
    tribe   INTEGER  NOT NULL DEFAULT 0,
    type    CHAR(1)  NOT NULL DEFAULT 'A'
);
CREATE INDEX IF NOT EXISTS idx_tribe_reserv_tribe ON tribe_reserv (tribe);

-- =========================================================================
-- teams (player parties — currently disabled in-game)
-- =========================================================================
CREATE TABLE IF NOT EXISTS teams (
    id      SERIAL PRIMARY KEY,
    leader  INTEGER NOT NULL DEFAULT 0,
    slot1   INTEGER NOT NULL DEFAULT 0,
    slot2   INTEGER NOT NULL DEFAULT 0,
    slot3   INTEGER NOT NULL DEFAULT 0,
    slot4   INTEGER NOT NULL DEFAULT 0,
    slot5   INTEGER NOT NULL DEFAULT 0
);

-- =========================================================================
-- astral_machine (one row per Castle-level tribe)
-- =========================================================================
CREATE TABLE IF NOT EXISTS astral_machine (
    owner    INTEGER NOT NULL DEFAULT 0,
    used     INTEGER NOT NULL DEFAULT 0,
    directed INTEGER NOT NULL DEFAULT 0,
    aviable  CHAR(1) NOT NULL DEFAULT 'N'
);
CREATE INDEX IF NOT EXISTS idx_astral_machine_owner ON astral_machine (owner);
