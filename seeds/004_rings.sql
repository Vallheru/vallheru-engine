-- Seed data for rings (converted from MySQL install dump).

INSERT INTO rings (id, name, amount, lang) VALUES
(1, 'pierścień nowicjusza siły', 28, 'pl'),
(2, 'pierścień nowicjusza zręczności', 28, 'pl'),
(3, 'pierścień nowicjusza inteligencji', 28, 'pl'),
(4, 'pierścień nowicjusza siły woli', 28, 'pl'),
(5, 'pierścień nowicjusza szybkości', 28, 'pl'),
(6, 'pierścień nowicjusza wytrzymałości', 28, 'pl')
ON CONFLICT (id) DO NOTHING;
