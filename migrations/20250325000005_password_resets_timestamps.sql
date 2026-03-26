-- Add time-bounding columns to password_resets for token safety.
ALTER TABLE password_resets
    ADD COLUMN created_at  TIMESTAMPTZ NOT NULL DEFAULT NOW(),
    ADD COLUMN expires_at  TIMESTAMPTZ NOT NULL DEFAULT (NOW() + INTERVAL '24 hours');

CREATE INDEX idx_password_resets_expires_at ON password_resets (expires_at);
