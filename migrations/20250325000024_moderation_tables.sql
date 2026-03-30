-- Moderation tables: jail, court documents, communication bans.

-- Jail sentences.
CREATE TABLE IF NOT EXISTS jail (
    id        SERIAL       PRIMARY KEY,
    prisoner  INT          NOT NULL,
    duration  INT          NOT NULL DEFAULT 0,
    sentenced DATE         NOT NULL DEFAULT CURRENT_DATE,
    verdict   TEXT         NOT NULL DEFAULT '',
    cost      INT          NOT NULL DEFAULT 0
);
CREATE INDEX idx_jail_prisoner ON jail (prisoner);

-- Court documents (rules, cases, verdicts).
CREATE TABLE IF NOT EXISTS court (
    id    SERIAL       PRIMARY KEY,
    title VARCHAR(255) NOT NULL DEFAULT '',
    body  TEXT         NOT NULL DEFAULT '',
    lang  VARCHAR(2)   NOT NULL DEFAULT 'pl',
    kind  VARCHAR(20)  NOT NULL DEFAULT 'case',
    dated DATE         NOT NULL DEFAULT CURRENT_DATE
);

-- Comments on court cases.
CREATE TABLE IF NOT EXISTS court_cases (
    id      SERIAL      PRIMARY KEY,
    text_id INT         NOT NULL REFERENCES court(id) ON DELETE CASCADE,
    author  VARCHAR(40) NOT NULL DEFAULT '',
    body    TEXT        NOT NULL DEFAULT ''
);
CREATE INDEX idx_court_cases_text ON court_cases (text_id);

-- Chat ban (player blocked from chat for N resets).
CREATE TABLE IF NOT EXISTS chat_ban (
    id      SERIAL PRIMARY KEY,
    player  INT    NOT NULL UNIQUE,
    resets  INT    NOT NULL DEFAULT 0
);

-- Forum ban (player blocked from forum for N resets).
CREATE TABLE IF NOT EXISTS forum_ban (
    id      SERIAL PRIMARY KEY,
    player  INT    NOT NULL UNIQUE,
    resets  INT    NOT NULL DEFAULT 0
);

-- Mail ban (player blocked from sending mail).
CREATE TABLE IF NOT EXISTS mail_ban (
    id      SERIAL PRIMARY KEY,
    player  INT    NOT NULL DEFAULT 0,
    owner   INT    NOT NULL DEFAULT 0
);
CREATE INDEX idx_mail_ban_owner ON mail_ban (owner);
