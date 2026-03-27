-- Chat messages and configuration tables.
-- Owned by module 12 (Social, Chat, Mail, and Content).

CREATE TABLE IF NOT EXISTS chat_messages (
    id          BIGSERIAL    PRIMARY KEY,
    -- Pre-rendered HTML author label (includes tribe tags, rank colour, link).
    author_html TEXT         NOT NULL DEFAULT '',
    body        TEXT         NOT NULL,
    sender_id   BIGINT       NOT NULL DEFAULT 0,
    -- 0 = public message; non-zero = private whisper target player id.
    recipient_id BIGINT      NOT NULL DEFAULT 0,
    created_at  TIMESTAMPTZ  NOT NULL DEFAULT now()
);

CREATE INDEX idx_chat_messages_recipient ON chat_messages (recipient_id);
CREATE INDEX idx_chat_messages_created   ON chat_messages (created_at);

CREATE TABLE IF NOT EXISTS chat_bans (
    id          BIGSERIAL    PRIMARY KEY,
    player_id   BIGINT       NOT NULL UNIQUE REFERENCES players(id) ON DELETE CASCADE,
    -- Number of remaining reset cycles for the ban.
    resets      INT          NOT NULL DEFAULT 0
);

CREATE INDEX idx_chat_bans_player ON chat_bans (player_id);
