-- Initial core PostgreSQL schema for Vallheru.
-- Covers: players (auth columns only at this stage), settings, activation,
-- password reset, bans, game log, and staff log.
-- Serialized player columns (settings, stats, skills, bonuses) are kept as
-- text for now and will be normalized per MP-02-03.

-- Global game settings (key-value).
CREATE TABLE IF NOT EXISTS settings (
    setting VARCHAR(255) NOT NULL,
    value   VARCHAR(255),
    PRIMARY KEY (setting)
);

-- Account activation tokens.
CREATE TABLE IF NOT EXISTS activations (
    id         SERIAL PRIMARY KEY,
    username   VARCHAR(15)  NOT NULL,
    email      VARCHAR(60)  NOT NULL,
    pass_hash  VARCHAR(255) NOT NULL,
    token      INTEGER      NOT NULL DEFAULT 0,
    referrer   INTEGER      NOT NULL DEFAULT 0,
    ip         VARCHAR(50)  NOT NULL DEFAULT '',
    created_at DATE         NOT NULL DEFAULT CURRENT_DATE,
    game_type  VARCHAR(1)   NOT NULL DEFAULT ''
);

CREATE INDEX idx_activations_username ON activations (username);

-- Ban list (account bans, IP bans, etc.).
CREATE TABLE IF NOT EXISTS bans (
    id     SERIAL PRIMARY KEY,
    type   VARCHAR(10) NOT NULL DEFAULT '',
    amount VARCHAR(50) NOT NULL DEFAULT ''
);

CREATE INDEX idx_bans_type ON bans (type);

-- Password reset tokens.
CREATE TABLE IF NOT EXISTS password_resets (
    id         SERIAL PRIMARY KEY,
    token      VARCHAR(255) NOT NULL,
    email      VARCHAR(100) NOT NULL DEFAULT '',
    new_pass   VARCHAR(255) NOT NULL DEFAULT '',
    player_id  INTEGER      NOT NULL DEFAULT 0,
    new_email  VARCHAR(100) NOT NULL DEFAULT ''
);

CREATE INDEX idx_password_resets_token ON password_resets (token);
CREATE INDEX idx_password_resets_email ON password_resets (email);

-- Main players table.
-- This carries all columns from the legacy table, translated to PostgreSQL types.
-- Serialized string columns (settings, stats, skills, bonuses) are TEXT for now.
CREATE TABLE IF NOT EXISTS players (
    id            SERIAL PRIMARY KEY,
    username      VARCHAR(20)  NOT NULL DEFAULT '',
    email         VARCHAR(60)  NOT NULL DEFAULT '',
    pass_hash     VARCHAR(255) NOT NULL DEFAULT '',
    rank          VARCHAR(20)  NOT NULL DEFAULT 'Member',
    credits       INTEGER      NOT NULL DEFAULT 1000,
    energy        DOUBLE PRECISION NOT NULL DEFAULT 10.0,
    max_energy    DOUBLE PRECISION NOT NULL DEFAULT 100.0,
    ap            INTEGER      NOT NULL DEFAULT 5,
    wins          INTEGER      NOT NULL DEFAULT 0,
    losses        INTEGER      NOT NULL DEFAULT 0,
    last_killed   VARCHAR(60)  NOT NULL DEFAULT '...',
    last_killed_by VARCHAR(60) NOT NULL DEFAULT '...',
    platinum      INTEGER      NOT NULL DEFAULT 0,
    age           INTEGER      NOT NULL DEFAULT 1,
    logins        INTEGER      NOT NULL DEFAULT 0,
    hp            INTEGER      NOT NULL DEFAULT 10,
    max_hp        INTEGER      NOT NULL DEFAULT 10,
    bank          INTEGER      NOT NULL DEFAULT 0,
    last_page_visit BIGINT    NOT NULL DEFAULT 0,
    current_page  VARCHAR(100) NOT NULL DEFAULT '',
    ip            VARCHAR(50)  NOT NULL DEFAULT '',
    tribe_id      INTEGER      NOT NULL DEFAULT 0,
    profile       TEXT         NOT NULL DEFAULT '',
    referrals     INTEGER      NOT NULL DEFAULT 0,
    core_pass     BOOLEAN      NOT NULL DEFAULT FALSE,
    fight         INTEGER      NOT NULL DEFAULT 0,
    trains        INTEGER      NOT NULL DEFAULT 5,
    race          VARCHAR(20)  NOT NULL DEFAULT '',
    class         VARCHAR(20)  NOT NULL DEFAULT '',
    pw            INTEGER      NOT NULL DEFAULT 0,
    immune        BOOLEAN      NOT NULL DEFAULT FALSE,
    pm            INTEGER      NOT NULL DEFAULT 0,
    location      VARCHAR(15)  NOT NULL DEFAULT 'Altara',
    messenger     VARCHAR(255) NOT NULL DEFAULT '0',
    avatar        VARCHAR(36)  NOT NULL DEFAULT '',
    tribe_rank    VARCHAR(60)  NOT NULL DEFAULT '',
    deity         VARCHAR(20),
    maps          SMALLINT     NOT NULL DEFAULT 0,
    resting       BOOLEAN      NOT NULL DEFAULT FALSE,
    crime         INTEGER      NOT NULL DEFAULT 1,
    gender        CHAR(1),
    bridge        BOOLEAN      NOT NULL DEFAULT FALSE,
    temp          INTEGER      NOT NULL DEFAULT 0,
    forum_time    BIGINT       NOT NULL DEFAULT 0,
    tforum_time   BIGINT       NOT NULL DEFAULT 0,
    bless         VARCHAR(30)  NOT NULL DEFAULT '',
    bless_value   INTEGER      NOT NULL DEFAULT 0,
    antidote      VARCHAR(4),
    freeze        SMALLINT     NOT NULL DEFAULT 0,
    house_rest    BOOLEAN      NOT NULL DEFAULT FALSE,
    poll          BOOLEAN      NOT NULL DEFAULT FALSE,
    astral_crime  BOOLEAN      NOT NULL DEFAULT TRUE,
    change_deity  INTEGER      NOT NULL DEFAULT 0,
    vallars       INTEGER      NOT NULL DEFAULT 0,
    newbie        SMALLINT     NOT NULL DEFAULT 3,
    roleplay      TEXT         NOT NULL DEFAULT '',
    ooc           TEXT         NOT NULL DEFAULT '',
    short_rpg     VARCHAR(40)  NOT NULL DEFAULT '',
    craft_mission SMALLINT     NOT NULL DEFAULT 7,
    mpoints       INTEGER      NOT NULL DEFAULT 0,
    room          INTEGER      NOT NULL DEFAULT 0,
    chapter       SMALLINT     NOT NULL DEFAULT 1,
    -- Serialized fields — will be normalized in a later migration (MP-02-03).
    settings_raw  TEXT         NOT NULL DEFAULT '',
    stats_raw     TEXT         NOT NULL DEFAULT '',
    skills_raw    TEXT         NOT NULL DEFAULT '',
    craft_skill   VARCHAR(30)  NOT NULL DEFAULT '',
    chat_times    VARCHAR(512) NOT NULL DEFAULT '',
    bonuses_raw   TEXT         NOT NULL DEFAULT '',
    ring_invite   INTEGER      NOT NULL DEFAULT 0,
    tribe_invite  INTEGER      NOT NULL DEFAULT 0,
    team_id       INTEGER      NOT NULL DEFAULT 0,
    reputation    INTEGER      NOT NULL DEFAULT 0
);

CREATE INDEX idx_players_username ON players (username);
CREATE INDEX idx_players_email ON players (email);
CREATE INDEX idx_players_last_page_visit ON players (last_page_visit);
CREATE INDEX idx_players_room ON players (room);

-- Game event / activity log (per-player).
CREATE TABLE IF NOT EXISTS game_log (
    id         SERIAL PRIMARY KEY,
    owner_id   INTEGER NOT NULL DEFAULT 0,
    message    TEXT    NOT NULL DEFAULT '',
    unread     BOOLEAN NOT NULL DEFAULT TRUE,
    created_at TIMESTAMPTZ NOT NULL DEFAULT NOW(),
    log_type   CHAR(1) NOT NULL DEFAULT 'U'
);

CREATE INDEX idx_game_log_owner ON game_log (owner_id);
CREATE INDEX idx_game_log_type ON game_log (log_type);

-- Secondary log (shorter entries, date-only granularity).
CREATE TABLE IF NOT EXISTS game_log_daily (
    id         SERIAL PRIMARY KEY,
    owner_id   INTEGER      NOT NULL DEFAULT 0,
    message    VARCHAR(255) NOT NULL DEFAULT '',
    created_at DATE         NOT NULL DEFAULT CURRENT_DATE
);

CREATE INDEX idx_game_log_daily_owner ON game_log_daily (owner_id);
