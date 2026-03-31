-- Hall of Fame tables (heroes and astral machines).
-- Migrated from MySQL `halloffame` and `halloffame2` tables.

CREATE TABLE IF NOT EXISTS halloffame (
    id        SERIAL PRIMARY KEY,
    oldname   VARCHAR(100) NOT NULL DEFAULT '',
    heroid    INTEGER      NOT NULL DEFAULT 0,
    newid     INTEGER      NOT NULL DEFAULT 0,
    herorace  VARCHAR(60)  NOT NULL DEFAULT ''
);

CREATE TABLE IF NOT EXISTS halloffame2 (
    id      SERIAL PRIMARY KEY,
    tribe   VARCHAR(100) NOT NULL DEFAULT '',
    leader  VARCHAR(100) NOT NULL DEFAULT '',
    bdate   VARCHAR(60)  NOT NULL DEFAULT ''
);
