-- Player quick-links (custom navigation shortcuts).
-- Owned by module 05 (Auth, Accounts, and Sessions).

CREATE TABLE player_links (
    id         BIGSERIAL PRIMARY KEY,
    owner_id   INTEGER   NOT NULL REFERENCES players(id) ON DELETE CASCADE,
    label      TEXT      NOT NULL DEFAULT '',
    url        TEXT      NOT NULL DEFAULT '',
    sort_order INTEGER   NOT NULL DEFAULT 0
);

CREATE INDEX idx_player_links_owner ON player_links (owner_id);
