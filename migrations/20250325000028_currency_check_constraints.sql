-- Prevent currency columns from going negative.
-- Handler-level checks already validate balances before deductions, but these
-- constraints act as a last line of defense against TOCTOU race conditions.

ALTER TABLE players ADD CONSTRAINT credits_non_negative CHECK (credits >= 0);
ALTER TABLE players ADD CONSTRAINT platinum_non_negative CHECK (platinum >= 0);
ALTER TABLE players ADD CONSTRAINT bank_non_negative CHECK (bank >= 0);
