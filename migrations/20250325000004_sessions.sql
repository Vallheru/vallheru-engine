-- PostgreSQL-backed session store for the Rust application.
-- Replaces PHP's native session_start() / $_SESSION mechanism.
-- Sessions survive application restarts and can be cleaned with a cron sweep.

CREATE TABLE IF NOT EXISTS sessions (
    id         VARCHAR(64)  NOT NULL PRIMARY KEY,
    player_id  INTEGER,
    data       JSONB        NOT NULL DEFAULT '{}'::jsonb,
    created_at TIMESTAMPTZ  NOT NULL DEFAULT NOW(),
    expires_at TIMESTAMPTZ  NOT NULL DEFAULT NOW() + INTERVAL '24 hours'
);

CREATE INDEX idx_sessions_player_id ON sessions (player_id);
CREATE INDEX idx_sessions_expires_at ON sessions (expires_at);
