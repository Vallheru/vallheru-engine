-- Travel encounter state: one active bandit encounter per player.
-- The generated bandit monster is stored in the `monsters` table with
-- location = 'Travel'; this table records the owning player and the
-- original travel parameters so the journey can complete after resolution.

CREATE TABLE IF NOT EXISTS travel_encounters (
    player_id   INT PRIMARY KEY,
    destination VARCHAR(50)  NOT NULL,
    method      VARCHAR(20)  NOT NULL,
    travel_cost INT          NOT NULL,
    monster_id  INT          NOT NULL,
    created_at  TIMESTAMPTZ  NOT NULL DEFAULT NOW()
);
