-- Donators / contributors recognition list
CREATE TABLE IF NOT EXISTS donators (
    id SERIAL PRIMARY KEY,
    name VARCHAR(60) NOT NULL
);

CREATE INDEX idx_donators_name ON donators (name);
