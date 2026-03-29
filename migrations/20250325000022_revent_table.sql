-- Random city event state per player.
CREATE TABLE IF NOT EXISTS revent (
    pid      INTEGER     NOT NULL UNIQUE,
    state    SMALLINT    NOT NULL DEFAULT 0,
    qtime    SMALLINT    NOT NULL DEFAULT 0,
    location VARCHAR(255) NOT NULL DEFAULT ''
);
