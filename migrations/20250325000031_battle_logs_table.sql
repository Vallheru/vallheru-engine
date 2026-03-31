-- Battle log table for PvP fight records.
-- Ported from the legacy `battlelogs` table.
CREATE TABLE IF NOT EXISTS battlelogs (
    id    SERIAL PRIMARY KEY,
    pid   INTEGER NOT NULL REFERENCES players(id),
    did   INTEGER NOT NULL REFERENCES players(id),
    wid   INTEGER NOT NULL DEFAULT 0,
    bdate BIGINT  NOT NULL DEFAULT 0
);

CREATE INDEX IF NOT EXISTS idx_battlelogs_pid ON battlelogs (pid);
CREATE INDEX IF NOT EXISTS idx_battlelogs_did ON battlelogs (did);
