-- Add catalog_id column to player_bonuses.
--
-- The PHP legacy format stores 4 fields per bonus:
--   catalog_id, level (value), trigger (bonus_name), magnitude (duration).
-- The original migration omitted catalog_id. This adds it for full fidelity.

ALTER TABLE player_bonuses
    ADD COLUMN IF NOT EXISTS catalog_id INTEGER NOT NULL DEFAULT 0;
