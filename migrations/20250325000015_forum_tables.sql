-- Forum tables: categories, topics, replies, and bans.

CREATE TABLE IF NOT EXISTS forum_categories (
    id          BIGSERIAL    PRIMARY KEY,
    name        VARCHAR(100) NOT NULL DEFAULT '',
    description VARCHAR(255) NOT NULL DEFAULT '',
    perm_visit  VARCHAR(255) NOT NULL DEFAULT 'All',
    perm_write  VARCHAR(255) NOT NULL DEFAULT 'All',
    perm_topic  VARCHAR(255) NOT NULL DEFAULT 'All',
    sort_order  INT          NOT NULL DEFAULT 0
);

CREATE TABLE IF NOT EXISTS forum_topics (
    id          BIGSERIAL    PRIMARY KEY,
    category_id BIGINT       NOT NULL REFERENCES forum_categories(id) ON DELETE CASCADE,
    title       TEXT         NOT NULL,
    body        TEXT         NOT NULL DEFAULT '',
    author_name VARCHAR(60)  NOT NULL DEFAULT '',
    author_id   BIGINT       NOT NULL DEFAULT 0,
    is_sticky   BOOLEAN      NOT NULL DEFAULT FALSE,
    is_closed   BOOLEAN      NOT NULL DEFAULT FALSE,
    reply_count INT          NOT NULL DEFAULT 0,
    last_post_at TIMESTAMPTZ NOT NULL DEFAULT now(),
    created_at  TIMESTAMPTZ  NOT NULL DEFAULT now()
);

CREATE INDEX idx_forum_topics_category ON forum_topics (category_id);
CREATE INDEX idx_forum_topics_last_post ON forum_topics (last_post_at DESC);

CREATE TABLE IF NOT EXISTS forum_replies (
    id          BIGSERIAL    PRIMARY KEY,
    topic_id    BIGINT       NOT NULL REFERENCES forum_topics(id) ON DELETE CASCADE,
    author_name VARCHAR(60)  NOT NULL DEFAULT '',
    author_id   BIGINT       NOT NULL DEFAULT 0,
    body        TEXT         NOT NULL DEFAULT '',
    created_at  TIMESTAMPTZ  NOT NULL DEFAULT now()
);

CREATE INDEX idx_forum_replies_topic ON forum_replies (topic_id);

CREATE TABLE IF NOT EXISTS forum_bans (
    id          BIGSERIAL    PRIMARY KEY,
    player_id   BIGINT       NOT NULL UNIQUE,
    created_at  TIMESTAMPTZ  NOT NULL DEFAULT now()
);
