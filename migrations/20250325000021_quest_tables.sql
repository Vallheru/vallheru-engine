-- Quest content and player quest progress tables.

-- Authored quest step content (branching text, choices, answers).
CREATE TABLE IF NOT EXISTS quests (
    id         SERIAL PRIMARY KEY,
    qid        INTEGER     NOT NULL DEFAULT 2,
    location   VARCHAR(20) NOT NULL DEFAULT 'grid.php',
    name       VARCHAR(20) NOT NULL DEFAULT '',
    option     VARCHAR(20) NOT NULL DEFAULT '0',
    text       TEXT        NOT NULL DEFAULT '',
    lang       VARCHAR(3)  NOT NULL DEFAULT 'pl'
);

CREATE INDEX idx_quests_qid      ON quests (qid);
CREATE INDEX idx_quests_location  ON quests (location);
CREATE INDEX idx_quests_name      ON quests (name);

-- Per-player active quest progress tracker.
CREATE TABLE IF NOT EXISTS questaction (
    id     SERIAL PRIMARY KEY,
    player INTEGER     NOT NULL DEFAULT 0,
    quest  INTEGER     NOT NULL DEFAULT 0,
    action VARCHAR(20) NOT NULL DEFAULT ''
);

CREATE INDEX idx_questaction_player ON questaction (player);
