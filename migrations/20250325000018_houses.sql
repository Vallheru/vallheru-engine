-- Player housing table.
-- Covers: land ownership, building levels, room features, sale/rent system.

CREATE TABLE IF NOT EXISTS houses (
    id        SERIAL PRIMARY KEY,
    owner     INTEGER NOT NULL DEFAULT 0,
    locator   INTEGER NOT NULL DEFAULT 0,
    location  VARCHAR(50) NOT NULL DEFAULT 'Altara',
    name      VARCHAR(100) NOT NULL DEFAULT '',
    size      INTEGER NOT NULL DEFAULT 1,
    build     INTEGER NOT NULL DEFAULT 0,
    value     INTEGER NOT NULL DEFAULT 1,
    points    INTEGER NOT NULL DEFAULT 10,
    used      INTEGER NOT NULL DEFAULT 0,
    bedroom   BOOLEAN NOT NULL DEFAULT FALSE,
    wardrobe  INTEGER NOT NULL DEFAULT 0,
    cost      INTEGER NOT NULL DEFAULT 0,
    seller    INTEGER NOT NULL DEFAULT 0
);

CREATE INDEX idx_houses_owner ON houses (owner);
CREATE INDEX idx_houses_location ON houses (location);
