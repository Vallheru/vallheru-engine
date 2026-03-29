-- Tribe forum tables: topics and replies for clan-internal discussion boards.
-- Also creates the `tribes` and `tribe_perm` tables needed for forum
-- permission checks (these tables support the full tribe module ported
-- in MP-13-02/03/04 domain layer).

-- =========================================================================
-- tribes (base clan table — needed for forum author tags and ownership)
-- =========================================================================
CREATE TABLE IF NOT EXISTS tribes (
    id            SERIAL PRIMARY KEY,
    name          VARCHAR(255) NOT NULL DEFAULT '',
    owner         INTEGER      NOT NULL DEFAULT 0,
    credits       INTEGER      NOT NULL DEFAULT 0,
    platinum      INTEGER      NOT NULL DEFAULT 0,
    public_msg    TEXT         NOT NULL DEFAULT '',
    private_msg   TEXT         NOT NULL DEFAULT '',
    hospass       CHAR(1)      NOT NULL DEFAULT 'N',
    atak          CHAR(1)      NOT NULL DEFAULT 'N',
    wygr          INTEGER      NOT NULL DEFAULT 0,
    przeg         INTEGER      NOT NULL DEFAULT 0,
    zolnierze     INTEGER      NOT NULL DEFAULT 0,
    forty         INTEGER      NOT NULL DEFAULT 0,
    logo          VARCHAR(36)  NOT NULL DEFAULT '',
    www           VARCHAR(60)  NOT NULL DEFAULT '',
    prefix        VARCHAR(5)   NOT NULL DEFAULT '',
    suffix        VARCHAR(5)   NOT NULL DEFAULT '',
    level         SMALLINT     NOT NULL DEFAULT 1,
    rcredits      INTEGER      NOT NULL DEFAULT 0,
    rplatinum     INTEGER      NOT NULL DEFAULT 0,
    traps         SMALLINT     NOT NULL DEFAULT 0,
    agents        SMALLINT     NOT NULL DEFAULT 0,
    dagents       SMALLINT     NOT NULL DEFAULT 0
);

-- =========================================================================
-- tribe_perm (per-member permission flags)
-- =========================================================================
CREATE TABLE IF NOT EXISTS tribe_perm (
    id            SERIAL PRIMARY KEY,
    tribe         INTEGER  NOT NULL DEFAULT 0,
    player        INTEGER  NOT NULL DEFAULT 0,
    messages      SMALLINT NOT NULL DEFAULT 0,
    wait          SMALLINT NOT NULL DEFAULT 0,
    kick          SMALLINT NOT NULL DEFAULT 0,
    army          SMALLINT NOT NULL DEFAULT 0,
    attack        SMALLINT NOT NULL DEFAULT 0,
    loan          SMALLINT NOT NULL DEFAULT 0,
    armory        SMALLINT NOT NULL DEFAULT 0,
    warehouse     SMALLINT NOT NULL DEFAULT 0,
    bank          SMALLINT NOT NULL DEFAULT 0,
    herbs         SMALLINT NOT NULL DEFAULT 0,
    forum         SMALLINT NOT NULL DEFAULT 0,
    mail          SMALLINT NOT NULL DEFAULT 0,
    ranks         SMALLINT NOT NULL DEFAULT 0,
    info          SMALLINT NOT NULL DEFAULT 0,
    astralvault   SMALLINT NOT NULL DEFAULT 0
);

CREATE INDEX idx_tribe_perm_tribe_player ON tribe_perm (tribe, player);

-- =========================================================================
-- tribe_rank (custom rank labels per tribe)
-- =========================================================================
CREATE TABLE IF NOT EXISTS tribe_rank (
    id        SERIAL PRIMARY KEY,
    tribe_id  INTEGER      NOT NULL DEFAULT 0 UNIQUE,
    rank1     VARCHAR(60)  NOT NULL DEFAULT '',
    rank2     VARCHAR(60)  NOT NULL DEFAULT '',
    rank3     VARCHAR(60)  NOT NULL DEFAULT '',
    rank4     VARCHAR(60)  NOT NULL DEFAULT '',
    rank5     VARCHAR(60)  NOT NULL DEFAULT '',
    rank6     VARCHAR(60)  NOT NULL DEFAULT '',
    rank7     VARCHAR(60)  NOT NULL DEFAULT '',
    rank8     VARCHAR(60)  NOT NULL DEFAULT '',
    rank9     VARCHAR(60)  NOT NULL DEFAULT '',
    rank10    VARCHAR(60)  NOT NULL DEFAULT ''
);

-- =========================================================================
-- tribe_topics
-- =========================================================================
CREATE TABLE IF NOT EXISTS tribe_topics (
    id       SERIAL  PRIMARY KEY,
    topic    TEXT     NOT NULL DEFAULT '',
    body     TEXT     NOT NULL DEFAULT '',
    starter  VARCHAR(30) NOT NULL DEFAULT '',
    tribe    INTEGER  NOT NULL DEFAULT 0,
    w_time   BIGINT   NOT NULL DEFAULT 0,
    sticky   CHAR(1)  NOT NULL DEFAULT 'N',
    pid      INTEGER  NOT NULL DEFAULT 0
);

CREATE INDEX idx_tribe_topics_tribe ON tribe_topics (tribe);

-- =========================================================================
-- tribe_replies
-- =========================================================================
CREATE TABLE IF NOT EXISTS tribe_replies (
    id        SERIAL  PRIMARY KEY,
    starter   VARCHAR(30) NOT NULL DEFAULT '',
    topic_id  INTEGER  NOT NULL DEFAULT 0,
    body      TEXT     NOT NULL DEFAULT '',
    pid       INTEGER  NOT NULL DEFAULT 0
);

CREATE INDEX idx_tribe_replies_topic ON tribe_replies (topic_id);
