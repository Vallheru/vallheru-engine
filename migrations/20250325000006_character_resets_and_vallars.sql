-- Tables for player character reset and vallar (referral credit) history.

-- Character reset requests (player-initiated reset confirmation tokens).
CREATE TABLE IF NOT EXISTS character_resets (
    id         SERIAL PRIMARY KEY,
    player_id  INTEGER     NOT NULL REFERENCES players(id) ON DELETE CASCADE,
    code       INTEGER     NOT NULL,
    reset_type CHAR(1)     NOT NULL DEFAULT 'P',
    created_at TIMESTAMPTZ NOT NULL DEFAULT NOW()
);

CREATE INDEX idx_character_resets_player ON character_resets (player_id);

-- Vallar (referral reward) transaction history.
CREATE TABLE IF NOT EXISTS vallar_history (
    id         SERIAL PRIMARY KEY,
    owner_id   INTEGER     NOT NULL REFERENCES players(id) ON DELETE CASCADE,
    amount     INTEGER     NOT NULL DEFAULT 0,
    reason     TEXT        NOT NULL DEFAULT '',
    created_at TIMESTAMPTZ NOT NULL DEFAULT NOW()
);

CREATE INDEX idx_vallar_history_owner ON vallar_history (owner_id);
