-- News (player gossip), updates (admin announcements), newspaper, polls, proposals

-- ==========================================================================
-- Updates (admin game announcements) — the primary "news" shown on login
-- ==========================================================================
CREATE TABLE IF NOT EXISTS game_updates (
    id          BIGSERIAL PRIMARY KEY,
    title       TEXT        NOT NULL,
    body        TEXT        NOT NULL,
    author_name VARCHAR(60) NOT NULL DEFAULT '',
    published_at TIMESTAMPTZ NOT NULL DEFAULT now(),
    lang        VARCHAR(3)  NOT NULL DEFAULT 'pl'
);
CREATE INDEX idx_game_updates_published ON game_updates (published_at DESC);

-- ==========================================================================
-- News (player-submitted gossip)
-- ==========================================================================
CREATE TABLE IF NOT EXISTS news (
    id          BIGSERIAL PRIMARY KEY,
    title       TEXT        NOT NULL,
    body        TEXT        NOT NULL,
    author_name VARCHAR(60) NOT NULL DEFAULT '',
    author_id   BIGINT      NOT NULL DEFAULT 0,
    status      VARCHAR(10) NOT NULL DEFAULT 'pending',  -- pending / approved / hidden
    published_at TIMESTAMPTZ NOT NULL DEFAULT now(),
    lang        VARCHAR(3)  NOT NULL DEFAULT 'pl'
);
CREATE INDEX idx_news_status ON news (status, published_at DESC);

-- ==========================================================================
-- Newspaper articles
-- ==========================================================================
CREATE TABLE IF NOT EXISTS newspaper_articles (
    id          BIGSERIAL PRIMARY KEY,
    issue_id    BIGINT      NOT NULL DEFAULT 0,
    title       TEXT        NOT NULL DEFAULT '',
    body        TEXT        NOT NULL DEFAULT '',
    author_name VARCHAR(60) NOT NULL DEFAULT '',
    article_type CHAR(1)    NOT NULL DEFAULT 'N',
    is_published BOOLEAN    NOT NULL DEFAULT false,
    lang        VARCHAR(3)  NOT NULL DEFAULT 'pl',
    created_at  TIMESTAMPTZ NOT NULL DEFAULT now()
);
CREATE INDEX idx_newspaper_issue ON newspaper_articles (issue_id, article_type);

-- ==========================================================================
-- Polls
-- ==========================================================================
CREATE TABLE IF NOT EXISTS polls (
    id          BIGSERIAL PRIMARY KEY,
    question    TEXT        NOT NULL,
    description TEXT        NOT NULL DEFAULT '',
    days_left   INT         NOT NULL DEFAULT 7,
    member_base INT         NOT NULL DEFAULT 0,
    is_active   BOOLEAN     NOT NULL DEFAULT true,
    lang        VARCHAR(3)  NOT NULL DEFAULT 'pl',
    created_at  TIMESTAMPTZ NOT NULL DEFAULT now()
);

CREATE TABLE IF NOT EXISTS poll_options (
    id          BIGSERIAL PRIMARY KEY,
    poll_id     BIGINT      NOT NULL REFERENCES polls(id) ON DELETE CASCADE,
    label       TEXT        NOT NULL,
    votes       INT         NOT NULL DEFAULT 0
);
CREATE INDEX idx_poll_options_poll ON poll_options (poll_id);

-- ==========================================================================
-- Proposals (location descriptions, items, monsters)
-- ==========================================================================
CREATE TABLE IF NOT EXISTS proposals (
    id          BIGSERIAL PRIMARY KEY,
    player_id   BIGINT      NOT NULL,
    proposal_type VARCHAR(1) NOT NULL DEFAULT 'D',  -- D=description, I=item, M=monster
    name        VARCHAR(255) NOT NULL DEFAULT '',
    data        TEXT        NOT NULL DEFAULT '',
    info        TEXT        NOT NULL DEFAULT '',
    created_at  TIMESTAMPTZ NOT NULL DEFAULT now()
);
CREATE INDEX idx_proposals_type ON proposals (proposal_type);

-- ==========================================================================
-- Unified comments (replaces news_comments, upd_comments, newspaper_comments, polls_comments)
-- ==========================================================================
CREATE TABLE IF NOT EXISTS content_comments (
    id          BIGSERIAL PRIMARY KEY,
    target_type VARCHAR(20) NOT NULL,  -- 'news', 'update', 'newspaper', 'poll'
    target_id   BIGINT      NOT NULL,
    author_name VARCHAR(60) NOT NULL DEFAULT '',
    author_id   BIGINT      NOT NULL DEFAULT 0,
    body        TEXT        NOT NULL DEFAULT '',
    created_at  TIMESTAMPTZ NOT NULL DEFAULT now()
);
CREATE INDEX idx_content_comments_target ON content_comments (target_type, target_id, created_at);
