-- Creature type definitions (reference catalog). Ported from PHP cores seed data.
-- 6 types × 5 creatures each = 30 total. rarity: 1=common, 2=uncommon, 3=rare
INSERT INTO cores (name, type, power, defense, rarity, descr, lang) VALUES
-- Forest / Plant creatures
('Łasica',    'Plant',    1.0,  1.0,  1, '', 'pl'),
('Sokół',     'Plant',    2.0,  2.0,  1, '', 'pl'),
('Jeleń',     'Plant',    3.0,  3.0,  1, '', 'pl'),
('Dzik',      'Plant',    4.0,  4.0,  2, '', 'pl'),
('Niedźwiedź','Plant',    5.0,  5.0,  3, '', 'pl'),
-- Ocean / Aqua creatures
('Delfin',    'Aqua',     6.0,  6.0,  1, '', 'pl'),
('Rekin',     'Aqua',     7.0,  7.0,  1, '', 'pl'),
('Wieloryb',  'Aqua',     8.0,  8.0,  1, '', 'pl'),
('Sylfida',   'Aqua',     9.0,  9.0,  2, '', 'pl'),
('Kraken',    'Aqua',    10.0, 10.0,  3, '', 'pl'),
-- Mountains / Material creatures
('Wilk',           'Material', 11.0, 11.0, 1, '', 'pl'),
('Wielki Orzeł',   'Material', 12.0, 12.0, 1, '', 'pl'),
('Harpia',         'Material', 13.0, 13.0, 1, '', 'pl'),
('Czarny Niedźwiedź','Material',14.0,14.0, 2, '', 'pl'),
('Sasquatch',      'Material', 15.0, 15.0, 3, '', 'pl'),
-- Plains / Element creatures
('Bizon',      'Element', 16.0, 16.0, 1, '', 'pl'),
('Nosorożec',  'Element', 17.0, 17.0, 1, '', 'pl'),
('Feniks',     'Element', 18.0, 18.0, 1, '', 'pl'),
('Pegaz',      'Element', 19.0, 19.0, 2, '', 'pl'),
('Jednorożec', 'Element', 20.0, 20.0, 3, '', 'pl'),
-- Desert / Alien creatures
('Wielki Skorpion', 'Alien', 21.0, 21.0, 1, '', 'pl'),
('Pustynny wąż',    'Alien', 22.0, 22.0, 1, '', 'pl'),
('Gryf',            'Alien', 23.0, 23.0, 1, '', 'pl'),
('Manticora',       'Alien', 24.0, 24.0, 2, '', 'pl'),
('Olbrzymi jaszczur','Alien', 25.0, 25.0, 3, '', 'pl'),
-- Magic / Ancient creatures
('Astralny Wojownik','Ancient', 26.0, 26.0, 1, '', 'pl'),
('Behemoth',         'Ancient', 27.0, 27.0, 1, '', 'pl'),
('Zielony Smok',     'Ancient', 50.0, 50.0, 1, '', 'pl'),
('Czerwony Smok',    'Ancient', 75.0, 75.0, 2, '', 'pl'),
('Czarny Smok',      'Ancient',100.0,100.0, 3, '', 'pl')
ON CONFLICT DO NOTHING;
