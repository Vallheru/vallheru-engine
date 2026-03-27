-- Player personal notes (replaces legacy `notatnik` table).
CREATE TABLE IF NOT EXISTS notes (
    id         BIGSERIAL    PRIMARY KEY,
    player_id  BIGINT       NOT NULL REFERENCES players(id) ON DELETE CASCADE,
    title      VARCHAR(255) NOT NULL DEFAULT '',
    body       TEXT         NOT NULL DEFAULT '',
    created_at TIMESTAMPTZ  NOT NULL DEFAULT now()
);
CREATE INDEX idx_notes_player ON notes (player_id, id DESC);

-- Library texts (player-submitted tales and poetry).
CREATE TABLE IF NOT EXISTS library_texts (
    id          BIGSERIAL    PRIMARY KEY,
    title       VARCHAR(255) NOT NULL DEFAULT '',
    body        TEXT         NOT NULL DEFAULT '',
    author_name VARCHAR(100) NOT NULL DEFAULT '',
    author_id   BIGINT       NOT NULL DEFAULT 0,
    text_type   VARCHAR(20)  NOT NULL DEFAULT 'tale',   -- 'tale' or 'poetry'
    is_approved BOOLEAN      NOT NULL DEFAULT FALSE,
    lang        VARCHAR(5)   NOT NULL DEFAULT 'pl',
    created_at  TIMESTAMPTZ  NOT NULL DEFAULT now()
);
CREATE INDEX idx_library_approved ON library_texts (is_approved, text_type, lang);

-- Library comments use the unified content_comments table (target_type = 'library').
-- No additional table needed.

-- Chronicle quest definitions (read-only listing for now, quest execution is out of scope).
CREATE TABLE IF NOT EXISTS chronicle_missions (
    id         BIGSERIAL    PRIMARY KEY,
    name       VARCHAR(255) NOT NULL,
    mission_type CHAR(1)    NOT NULL DEFAULT 'E',   -- Q=main quest, O=old story, E=event, etc.
    intro      TEXT         NOT NULL DEFAULT '',
    short_desc VARCHAR(255) NOT NULL DEFAULT '',
    location   VARCHAR(50)  NOT NULL DEFAULT 'Altara',
    chapter    SMALLINT     NOT NULL DEFAULT 0
);
CREATE INDEX idx_chronicle_location ON chronicle_missions (location);
