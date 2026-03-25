-- Normalized tables for legacy serialized player fields.
-- See migration-plan/player-field-normalization.md for design rationale.

-- Replace the settings_raw TEXT column with JSONB.
ALTER TABLE players ADD COLUMN IF NOT EXISTS settings JSONB NOT NULL DEFAULT '{}';

-- Player stats (6 fixed stat types per player).
CREATE TABLE IF NOT EXISTS player_stats (
    player_id  INTEGER     NOT NULL REFERENCES players(id) ON DELETE CASCADE,
    stat_key   VARCHAR(20) NOT NULL,
    label      VARCHAR(40) NOT NULL,
    base       INTEGER     NOT NULL DEFAULT 0,
    trained    INTEGER     NOT NULL DEFAULT 0,
    modified   INTEGER     NOT NULL DEFAULT 0,
    PRIMARY KEY (player_id, stat_key)
);

-- Player skills (16 fixed skill types per player).
CREATE TABLE IF NOT EXISTS player_skills (
    player_id  INTEGER     NOT NULL REFERENCES players(id) ON DELETE CASCADE,
    skill_key  VARCHAR(20) NOT NULL,
    label      VARCHAR(60) NOT NULL,
    level      INTEGER     NOT NULL DEFAULT 1,
    xp         INTEGER     NOT NULL DEFAULT 0,
    PRIMARY KEY (player_id, skill_key)
);

-- Player active bonuses (variable count per player).
CREATE TABLE IF NOT EXISTS player_bonuses (
    id         SERIAL  PRIMARY KEY,
    player_id  INTEGER     NOT NULL REFERENCES players(id) ON DELETE CASCADE,
    bonus_name VARCHAR(60) NOT NULL,
    value      INTEGER     NOT NULL DEFAULT 0,
    duration   INTEGER     NOT NULL DEFAULT 0
);

CREATE INDEX IF NOT EXISTS idx_player_bonuses_player ON player_bonuses (player_id);
