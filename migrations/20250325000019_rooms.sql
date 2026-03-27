-- Tavern rooms and room-specific chat messages.
-- Owned by module 12 (Social, Chat, Mail, and Content) — task MP-12-02.

CREATE TABLE IF NOT EXISTS rooms (
    id              SERIAL       PRIMARY KEY,
    owner_id        BIGINT       NOT NULL REFERENCES players(id) ON DELETE CASCADE,
    name            VARCHAR(255) NOT NULL DEFAULT 'Pokój',
    description     TEXT         NOT NULL DEFAULT '',
    days_remaining  SMALLINT     NOT NULL DEFAULT 1,
    -- Co-owner player IDs.
    co_owners       BIGINT[]     NOT NULL DEFAULT '{}',
    -- Custom NPC persona names available to owners.
    npcs            TEXT[]       NOT NULL DEFAULT '{}',
    -- Per-player nick colours: JSON object { "player_id": "color_name" }.
    colors          JSONB        NOT NULL DEFAULT '{}'
);

CREATE INDEX idx_rooms_owner ON rooms (owner_id);

CREATE TABLE IF NOT EXISTS room_messages (
    id            BIGSERIAL    PRIMARY KEY,
    room_id       INT          NOT NULL REFERENCES rooms(id) ON DELETE CASCADE,
    author_html   TEXT         NOT NULL DEFAULT '',
    body          TEXT         NOT NULL,
    sender_id     BIGINT       NOT NULL DEFAULT 0,
    -- 0 = visible to all room members; non-zero = whisper target player id.
    recipient_id  BIGINT       NOT NULL DEFAULT 0,
    created_at    TIMESTAMPTZ  NOT NULL DEFAULT now()
);

CREATE INDEX idx_room_messages_room      ON room_messages (room_id);
CREATE INDEX idx_room_messages_created   ON room_messages (created_at);
