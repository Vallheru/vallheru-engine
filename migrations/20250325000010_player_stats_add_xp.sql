-- Add XP column to player_stats.
-- In the PHP game, stats accumulate XP toward the next level:
--   XP needed = current_level * 500
-- This mirrors stats[stat_key][3] in the PHP player class.
ALTER TABLE player_stats ADD COLUMN IF NOT EXISTS xp INTEGER NOT NULL DEFAULT 0;
