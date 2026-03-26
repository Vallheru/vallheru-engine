-- Seed data for mage items catalog (converted from MySQL install dump).

INSERT INTO mage_items (id, name, power, type, cost, minlev, lang) VALUES
(1, 'elfia różdżka', 0, 'T', 1000, 1, 'pl'),
(2, 'elfie szaty', 10, 'C', 1000, 1, 'pl'),
(3, 'różdżka adepta', 0, 'T', 50000, 20, 'pl'),
(4, 'różdżka maga', 0, 'T', 150000, 40, 'pl'),
(5, 'różdżka magii', 0, 'T', 450000, 60, 'pl'),
(6, 'różdżka intelektu', 0, 'T', 1000000, 80, 'pl'),
(7, 'różdżka arcymaga', 0, 'T', 5000000, 100, 'pl'),
(8, 'szata adepta', 20, 'C', 20000, 20, 'pl'),
(9, 'szata maga', 50, 'C', 300000, 40, 'pl'),
(10, 'szata magii', 100, 'C', 4000000, 60, 'pl'),
(11, 'szata mądrości', 150, 'C', 100000000, 80, 'pl'),
(12, 'szata arcymaga', 200, 'C', 200000000, 100, 'pl')
ON CONFLICT (id) DO NOTHING;
