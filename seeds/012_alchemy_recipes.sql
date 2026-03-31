-- Alchemy recipe catalog (owner = 0). Ported from PHP alchemy_mill seed data.
-- Columns: name, owner, illani, illanias, nutari, cost, level, status, dynallca, lang
INSERT INTO alchemy_mill (name, owner, illani, illanias, nutari, cost, level, status, dynallca, lang) VALUES
-- Nutari health potions (H type, uses nutari herb)
('bardzo silna mikstura z Nutari', 0, 0, 0, 50, 50000, 50, 'S', 0, 'pl'),
('silna mikstura z Nutari', 0, 0, 0, 10, 20000, 20, 'S', 0, 'pl'),
('mikstura z Nutari', 0, 0, 0, 5, 10000, 10, 'S', 0, 'pl'),
('słaba mikstura z Nutari', 0, 0, 0, 2, 4000, 5, 'S', 0, 'pl'),
('bardzo słaba mikstura z Nutari', 0, 0, 0, 1, 2000, 1, 'S', 0, 'pl'),
-- Dynallca poisons (P type, uses dynallca herb)
('bardzo słaba trucizna z Dynallca', 0, 0, 0, 0, 2000, 1, 'S', 1, 'pl'),
('słaba trucizna z Dynallca', 0, 0, 0, 0, 10000, 10, 'S', 5, 'pl'),
('trucizna z Dynallca', 0, 0, 0, 0, 20000, 20, 'S', 10, 'pl'),
('silna trucizna z Dynallca', 0, 0, 0, 0, 50000, 30, 'S', 20, 'pl'),
('bardzo silna trucizna z Dynallca', 0, 0, 0, 0, 100000, 50, 'S', 30, 'pl'),
-- Illani mana potions (M type, uses illani herb)
('bardzo słaba mikstura z Illani', 0, 1, 0, 0, 2000, 1, 'S', 0, 'pl'),
('słaba mikstura z Illani', 0, 2, 0, 0, 4000, 5, 'S', 0, 'pl'),
('mikstura z Illani', 0, 5, 0, 0, 10000, 10, 'S', 0, 'pl'),
('silna mikstura z Illani', 0, 10, 0, 0, 20000, 20, 'S', 0, 'pl'),
('bardzo silna mikstura z Illani', 0, 20, 0, 0, 40000, 50, 'S', 0, 'pl'),
-- Illani poisons (P type, illani + dynallca)
('bardzo słaba trucizna z Illani', 0, 2, 0, 0, 4000, 5, 'S', 1, 'pl'),
('słaba trucizna z Illani', 0, 5, 0, 0, 15000, 15, 'S', 2, 'pl'),
('trucizna z Illani', 0, 10, 0, 0, 25000, 25, 'S', 5, 'pl'),
('silna trucizna z Illani', 0, 20, 0, 0, 100000, 40, 'S', 10, 'pl'),
('bardzo silna trucizna z Illani', 0, 30, 0, 0, 200000, 60, 'S', 15, 'pl'),
-- Nutari poisons (P type, nutari + dynallca)
('bardzo słaba trucizna z Nutari', 0, 0, 0, 2, 4000, 5, 'S', 1, 'pl'),
('słaba trucizna z Nutari', 0, 0, 0, 5, 15000, 15, 'S', 2, 'pl'),
('trucizna z Nutari', 0, 0, 0, 10, 25000, 25, 'S', 5, 'pl'),
('silna trucizna z Nutari', 0, 0, 0, 20, 100000, 40, 'S', 10, 'pl'),
('bardzo silna trucizna z Nutari', 0, 0, 0, 30, 200000, 60, 'S', 15, 'pl'),
-- Antidotes (A type)
('antidotum na truciznę z Illani', 0, 10, 10, 0, 20000, 20, 'S', 0, 'pl'),
('antidotum na truciznę z Nutari', 0, 0, 10, 10, 20000, 20, 'S', 0, 'pl'),
('antidotum na truciznę z Dynallca', 0, 0, 5, 0, 10000, 10, 'S', 5, 'pl'),
-- Special potions
('Oszukanie śmierci', 0, 30, 20, 15, 50000, 50, 'S', 20, 'pl'),
('Silne oszukanie śmierci', 0, 60, 40, 30, 100000, 100, 'S', 40, 'pl')
ON CONFLICT DO NOTHING;
