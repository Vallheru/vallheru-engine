-- Mail, contacts, and block-list tables.
-- Owned by module 12 (Social, Chat, Mail, and Content).

CREATE TABLE mail_messages (
    id          BIGSERIAL   PRIMARY KEY,
    sender_id   BIGINT      NOT NULL DEFAULT 0,
    sender_name TEXT        NOT NULL DEFAULT '',
    owner_id    BIGINT      NOT NULL,
    recipient_id BIGINT     NOT NULL DEFAULT 0,
    recipient_name TEXT     NOT NULL DEFAULT '',
    topic_id    BIGINT      NOT NULL DEFAULT 0,
    subject     TEXT        NOT NULL DEFAULT '',
    body        TEXT        NOT NULL DEFAULT '',
    is_read     BOOLEAN     NOT NULL DEFAULT FALSE,
    is_saved    BOOLEAN     NOT NULL DEFAULT FALSE,
    created_at  TIMESTAMPTZ NOT NULL DEFAULT now()
);

CREATE INDEX idx_mail_owner      ON mail_messages (owner_id);
CREATE INDEX idx_mail_topic      ON mail_messages (topic_id);
CREATE INDEX idx_mail_sender     ON mail_messages (sender_id);
CREATE INDEX idx_mail_created    ON mail_messages (created_at);
CREATE INDEX idx_mail_unread     ON mail_messages (owner_id, is_read) WHERE NOT is_read;

CREATE TABLE mail_contacts (
    id        BIGSERIAL PRIMARY KEY,
    owner_id  BIGINT    NOT NULL,
    player_id BIGINT    NOT NULL,
    sort_order INT      NOT NULL DEFAULT 1,
    UNIQUE (owner_id, player_id)
);

CREATE INDEX idx_mail_contacts_owner ON mail_contacts (owner_id);

CREATE TABLE mail_blocks (
    id          BIGSERIAL PRIMARY KEY,
    owner_id    BIGINT    NOT NULL,
    blocked_id  BIGINT    NOT NULL,
    block_mail  BOOLEAN   NOT NULL DEFAULT TRUE,
    block_chat  BOOLEAN   NOT NULL DEFAULT TRUE,
    UNIQUE (owner_id, blocked_id)
);

CREATE INDEX idx_mail_blocks_owner ON mail_blocks (owner_id);
