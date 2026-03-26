-- Drop legacy serialized columns now that all data uses normalized tables.
-- These columns were: settings_raw, stats_raw, skills_raw, bonuses_raw.

ALTER TABLE players DROP COLUMN IF EXISTS settings_raw;
ALTER TABLE players DROP COLUMN IF EXISTS stats_raw;
ALTER TABLE players DROP COLUMN IF EXISTS skills_raw;
ALTER TABLE players DROP COLUMN IF EXISTS bonuses_raw;
