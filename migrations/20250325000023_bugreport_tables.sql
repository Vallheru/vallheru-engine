-- Bug reports and comments tables.
--
-- bugreport: player-submitted bug reports with staff resolution workflow.
-- bug_comments: staff comments on bug reports (for "more info" / "works for me" flows).
--
-- The legacy `bugtrack` table (PHP automated error tracking) is NOT migrated;
-- structured logging via tracing replaces it.

CREATE TABLE IF NOT EXISTS bugreport (
    id         SERIAL PRIMARY KEY,
    sender     INTEGER      NOT NULL DEFAULT 0,
    title      VARCHAR(255) NOT NULL DEFAULT '',
    location   VARCHAR(255) NOT NULL DEFAULT '',
    body       TEXT         NOT NULL DEFAULT '',
    resolution SMALLINT     NOT NULL DEFAULT 0
);

CREATE INDEX idx_bugreport_sender ON bugreport (sender);

CREATE TABLE IF NOT EXISTS bug_comments (
    id      SERIAL PRIMARY KEY,
    bug_id  INTEGER      NOT NULL DEFAULT 0,
    author  VARCHAR(40)  NOT NULL DEFAULT '',
    body    TEXT         NOT NULL DEFAULT '',
    created DATE
);

CREATE INDEX idx_bug_comments_bug_id ON bug_comments (bug_id);
