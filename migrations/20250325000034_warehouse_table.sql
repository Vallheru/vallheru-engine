-- Royal Warehouse: tracks mineral/herb stock, prices, and trade history per reset cycle.
-- Each row represents one commodity for one reset period.

CREATE TABLE IF NOT EXISTS warehouse (
    id         SERIAL PRIMARY KEY,
    reset      SMALLINT NOT NULL DEFAULT 0,
    mineral    VARCHAR(30) NOT NULL DEFAULT '',
    sell       BIGINT NOT NULL DEFAULT 0,
    buy        BIGINT NOT NULL DEFAULT 0,
    cost       DOUBLE PRECISION NOT NULL DEFAULT 0,
    amount     BIGINT NOT NULL DEFAULT 0
);

CREATE INDEX IF NOT EXISTS idx_warehouse_reset   ON warehouse (reset);
CREATE INDEX IF NOT EXISTS idx_warehouse_mineral ON warehouse (mineral);
