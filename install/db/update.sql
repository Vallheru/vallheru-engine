INSERT INTO `quests` (`qid`, `location`, `name`, `option`, `text`) VALUES(10, 'grid.php', 'start', '0', 'Znalazłeś się w ciemnej i zaniedbanej części labiryntu. Z lekką odrazą badasz wilgotne ściany, uważając, żeby się nie przewrócić. Mimo Twojej czujności grunt osuwa Ci się spod stopy. Rzucasz się w tył, upadasz, syczysz z bólu po twardym lądowaniu. Z irytacją wyciągasz ręce, badasz otoczenie i odkrywasz, że ziemia osunęła się tworząc wchodzący pod ścianę tunel. Woda z korytarza spływa tędy gdzieś w dół. Kąt nachylenia daje szansę na powrót, jeśli zdecydujesz się na zjazd w ciemność po błotno-kamienistej pochylni.');
INSERT INTO `quests` (`qid`, `location`, `name`, `option`, `text`) VALUES(10, 'grid.php', 'box1', '1', 'Poświęcasz ubranie i ostrożnie zsuwasz się w tunel.');
INSERT INTO `quests` (`qid`, `location`, `name`, `option`, `text`) VALUES(10, 'grid.php', 'box1', '2', 'Wstajesz, otrzepujesz ubranie, łukiem omijasz zdradliwe osuwisko i ruszasz dalej korytarzami labiryntu.');
INSERT INTO `quests` (`qid`, `location`, `name`, `option`, `text`) VALUES(10, 'grid.php', '1.1', '0', 'Cały ubłocony lądujesz w niewielkiej grocie. Ze zdziwieniem spostrzegasz, ze zrobiło się jasno. Mdłe światło pochodzi od fosforyzujących grzybów pokrywających wilgotne ściany. Płynie tu podziemny strumień, który wcina się w skałę tworząc wąską szczelinę. ');
INSERT INTO `quests` (`qid`, `location`, `name`, `option`, `text`) VALUES(10, 'grid.php', '1.1.next', '0', 'Zanurzasz stopy w wodzie, zimno sprawia Ci dotkliwy ból. Wciągając brzuch, na wydechu, przeciskasz się z trudem dalej. Po kilku chwilach, które dłużyły się niemiłosiernie, wychodzisz do większej groty. Strumień płynie dalej, na ścianie nad nim te same grzyby emitują bladą poświatę. Zdumiony pięknym widokiem uderzasz głową w stalaktyt. Rozcierasz rozbite czoło i rozglądasz się spokojniej.');
INSERT INTO `quests` (`qid`, `location`, `name`, `option`, `text`) VALUES(10, 'grid.php', '1.1.n.n', '0', 'Jaskinia w której się znajdujesz pełna jest wspaniałych stalaktytów i stalagmitów, niektóre z nich łączą się w dostojne kolumny. Koniec groty niknie gdzieś w mroku, gdzie nie sięga nędzny blask grzybów znad strumienia. Odgłos kapiącej wody brzmi jak tajemniczy dzwon. W zachwycie robisz kilka niebacznych kroków i potykasz się o odłamany stalaktyt. Już masz to zignorować, kiedy orientujesz się... że widziałeś w kamieniu błysk? Kucasz, żeby się przyjrzeć.');
INSERT INTO `quests` (`qid`, `location`, `name`, `option`, `text`) VALUES(10, 'grid.php', '1.1.n.n.n', '0', 'Z kamienia wystaje duży kryształ. Wyjmujesz nóż i pieczołowicie wydłubujesz go. Kiedy tak klęczysz, na podłodze dostrzegasz kolejne dwa kryształy między odłamami skalnymi. Podnosisz się z trzema klejnotami w dłoni i z zamyśleniem zerkasz na zwieszające się z sufitu stalaktyty. Bardzo prawdopodobne, że kryją w sobie kolejne skarby.');
INSERT INTO `quests` (`qid`, `location`, `name`, `option`, `text`) VALUES(10, 'grid.php', 'box2', '1', 'Postanawiasz, ze nie będziesz niszczyć tak zachwycającego miejsca i odchodzisz z tym, co udało Ci się znaleźć.');
INSERT INTO `quests` (`qid`, `location`, `name`, `option`, `text`) VALUES(10, 'grid.php', 'box2', '2', 'Z pomocą noża i kamieni próbujesz odłupać tak wiele stalaktytów ile zdołasz.');
INSERT INTO `quests` (`qid`, `location`, `name`, `option`, `text`) VALUES(10, 'grid.php', '2.1', '0', 'Wracasz do mrocznych korytarzy labiryntu ubłocony, z trzema kryształami i zachwytem w sercu. Postarasz się zapamiętać lokalizację groty i wrócić tam kiedyś z lampką, żeby obejrzeć cuda przyrody ponownie.');
INSERT INTO `quests` (`qid`, `location`, `name`, `option`, `text`) VALUES(10, 'grid.php', 'str1', '0', 'Nie okazałeś się dość silny, żeby cokolwiek zdziałać. Zły odchodzisz z trzema kryształami.');
INSERT INTO `quests` (`qid`, `location`, `name`, `option`, `text`) VALUES(10, 'grid.php', 'str2', '0', 'Udaje Ci się oderwać jeszcze kilka mniejszych stalaktytów... Gorączkowo dłubiesz w nich, wyłuskując kryształy.');
INSERT INTO `quests` (`qid`, `location`, `name`, `option`, `text`) VALUES(10, 'grid.php', 'agi1', '0', 'Niestety łamiesz nóż i przecinasz sobie dłoń a kamień spada Ci na stopę. Krzyczysz z bólu. Podnosisz kolejny kryształ i odchodzisz niezadowolony, utykając.');
INSERT INTO `quests` (`qid`, `location`, `name`, `option`, `text`) VALUES(10, 'grid.php', 'agi2', '0', 'Sprawnie manewrując prymitywnymi narzędziami wydobywasz 8 kryształów. Odchodzisz pozostawiając zdewastowaną grotę.');
INSERT INTO `quests` (`qid`, `location`, `name`, `option`, `text`) VALUES(10, 'grid.php', 'end1', '0', 'Postanawiasz zostawić ten tunel w spokoju. Kto wie jakie niebezpieczeństwa tam na Ciebie czekają. Szybko zbierasz się i ruszasz ponownie na zwiedzanie labiryntu.');

TRUNCATE TABLE `mage_items;` 

INSERT INTO `mage_items` (`id`, `name`, `power`, `type`, `cost`, `minlev`, `lang`) VALUES 
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
(12, 'szata arcymaga', 200, 'C', 200000000, 100, 'pl');

TRUNCATE TABLE `czary`;

INSERT INTO `czary` (`id`, `nazwa`, `gracz`, `cena`, `poziom`, `typ`, `obr`, `status`, `lang`) VALUES 
(1, 'Piekielne płomienie', 0, 2500000, 65, 'B', 2.3, 'S', 'pl'),
(2, 'Uderzenie umysłu', 0, 2000000, 60, 'B', 2.2, 'S', 'pl'),
(3, 'Błyskawica', 0, 100000, 35, 'B', 1.7, 'S', 'pl'),
(4, 'Kula ognia', 0, 50000, 30, 'B', 1.6, 'S', 'pl'),
(5, 'Ognista strzała', 0, 15000, 25, 'B', 1.5, 'S', 'pl'),
(6, 'Ulepszenie przedmiotu', 0, 25000, 10, 'U', 0.0, 'S', 'pl'),
(7, 'Utwardzenie przedmiotu', 0, 500000, 25, 'U', 0.0, 'S', 'pl'),
(8, 'Umagicznienie przedmiotu', 0, 10000000, 50, 'U', 0.0, 'S', 'pl'),
(9, 'Tajfun', 0, 1500000, 55, 'B', 2.1, 'S', 'pl'),
(10, 'Lodowy pocisk', 0, 6000, 15, 'B', 1.3, 'S', 'pl'),
(11, 'Wodny pocisk', 0, 3000, 10, 'B', 1.2, 'S', 'pl'),
(12, 'Ognisty pocisk', 0, 1500, 5, 'B', 1.1, 'S', 'pl'),
(13, 'Ognisty oddech', 0, 1000000, 50, 'B', 2.0, 'S', 'pl'),
(14, 'Podpalenie', 0, 450000, 45, 'B', 1.9, 'S', 'pl'),
(15, 'Kula lodu', 0, 200000, 40, 'B', 1.8, 'S', 'pl'),
(16, 'Kamienny pocisk', 0, 500, 1, 'B', 1.0, 'S', 'pl'),
(17, 'Zabójczy deszcz', 0, 3000000, 70, 'B', 2.4, 'S', 'pl'),
(18, 'Trująca chmura', 0, 3500000, 75, 'B', 2.5, 'S', 'pl'),
(19, 'Oddech smoka', 0, 5000000, 80, 'B', 2.6, 'S', 'pl'),
(20, 'Trzęsienie ziemi', 0, 6200000, 85, 'B', 2.7, 'S', 'pl'),
(21, 'Obłok elektryczności', 0, 7300000, 90, 'B', 2.8, 'S', 'pl'),
(22, 'Deszcz meteorów', 0, 8500000, 95, 'B', 2.9, 'S', 'pl'),
(23, 'Armageddon', 0, 10000000, 100, 'B', 3.0, 'S', 'pl'),
(24, 'Pomoc', 0, 500, 1, 'O', 1.0, 'S', 'pl'),
(25, 'Skóra z drewna', 0, 1500, 5, 'O', 1.1, 'S', 'pl'),
(26, 'Skóra z kamienia', 0, 3000, 10, 'O', 1.2, 'S', 'pl'),
(27, 'Skóra z żelaza', 0, 6000, 15, 'O', 1.3, 'S', 'pl'),
(28, 'Ochrona', 0, 10000, 20, 'O', 1.4, 'S', 'pl'),
(29, 'Tarcza', 0, 15000, 25, 'O', 1.5, 'S', 'pl'),
(30, 'Tarcza mocy', 0, 50000, 30, 'O', 1.6, 'S', 'pl'),
(31, 'Obrona', 0, 100000, 35, 'O', 1.7, 'S', 'pl'),
(32, 'Magiczna szata', 0, 200000, 40, 'O', 1.8, 'S', 'pl'),
(33, 'Wyparowanie', 0, 450000, 45, 'O', 1.9, 'S', 'pl'),
(34, 'Magiczna sfera', 0, 1000000, 50, 'O', 2.0, 'S', 'pl'),
(35, 'Odbicie ataku', 0, 1500000, 55, 'O', 2.1, 'S', 'pl'),
(36, 'Zbroja', 0, 2000000, 60, 'O', 2.2, 'S', 'pl'),
(37, 'Twierdza umysłu', 0, 2500000, 65, 'O', 2.3, 'S', 'pl'),
(38, 'Pochłonięcie obrażeń', 0, 3000000, 70, 'O', 2.4, 'S', 'pl'),
(39, 'Sfera ochronna', 0, 3500000, 75, 'O', 2.5, 'S', 'pl'),
(40, 'Zbroja umysłu', 0, 5000000, 80, 'O', 2.6, 'S', 'pl'),
(41, 'Bastion mocy', 0, 6200000, 85, 'O', 2.7, 'S', 'pl'),
(42, 'Magiczna zbroja', 0, 7300000, 90, 'O', 2.8, 'S', 'pl'),
(43, 'Zbroja woli', 0, 8500000, 95, 'O', 2.9, 'S', 'pl'),
(44, 'Niewrażliwość', 0, 10000000, 100, 'O', 3.0, 'S', 'pl'),
(45, 'Magiczny pocisk', 0, 10000, 20, 'B', 1.4, 'S', 'pl');

ALTER TABLE `players` CHANGE `lastkilled` `lastkilled` VARCHAR( 60 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '...';
ALTER TABLE `players` CHANGE `lastkilledby` `lastkilledby` VARCHAR( 60 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '...';
ALTER TABLE `players` ADD `graphbar` VARCHAR( 1 ) NOT NULL DEFAULT 'N';

CREATE TABLE `links` (
  `id` int(11) NOT NULL auto_increment,
  `owner` int(11) NOT NULL default '0',
  `file` varchar(255) NOT NULL default '',
  `text` varchar(100) NOT NULL default '',
  KEY `id` (`id`),
  KEY `owner` (`owner`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

CREATE TABLE `bugreport` (
  `id` int(11) NOT NULL auto_increment,
  `sender` int(11) NOT NULL default '0',
  `title` varchar(255) NOT NULL default '',
  `type` varchar(20) NOT NULL default '',
  `location` varchar(255) NOT NULL default '',
  `desc` text NOT NULL,
  `resolution` tinyint(2) NOT NULL default '0',
  KEY `id` (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

CREATE TABLE `ban_mail` (
  `id` int(11) NOT NULL default '0',
  `owner` int(11) NOT NULL default '0',
  KEY `owner` (`owner`),
  KEY `id` (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
