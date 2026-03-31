-- Enforce username uniqueness at the DB level (case-insensitive).
-- Replaces the non-unique idx_players_username index.

DROP INDEX IF EXISTS idx_players_username;
CREATE UNIQUE INDEX idx_players_username ON players (LOWER(username));
