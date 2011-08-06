-- phpMyAdmin SQL Dump
-- version 2.9.0.2
-- http://www.phpmyadmin.net
-- 
-- Host: localhost
-- Czas wygenerowania: 07 Mar 2007, 13:05
-- Wersja serwera: 5.0.26
-- Wersja PHP: 5.1.6-pl6-gentoo
-- 
-- Baza danych: `test`
-- 

-- --------------------------------------------------------

-- 
-- Struktura tabeli dla  `adodb_logsql`
-- 

CREATE TABLE `adodb_logsql` (
  `created` datetime NOT NULL default '0000-00-00 00:00:00',
  `sql0` varchar(250) NOT NULL default '',
  `sql1` text NOT NULL,
  `params` text NOT NULL,
  `tracer` text NOT NULL,
  `timer` decimal(16,6) NOT NULL default '0.000000'
) TYPE=MyISAM;

-- 
-- Zrzut danych tabeli `adodb_logsql`
-- 


-- --------------------------------------------------------

-- 
-- Struktura tabeli dla  `aktywacja`
-- 

CREATE TABLE `aktywacja` (
  `id` int(11) NOT NULL auto_increment,
  `user` varchar(15) NOT NULL default '',
  `email` varchar(60) NOT NULL default '',
  `pass` varchar(32) NOT NULL default '',
  `aktyw` int(11) NOT NULL default '0',
  `refs` int(11) NOT NULL default '0',
  `ip` varchar(50) NOT NULL default '',
  `data` date NOT NULL default '0000-00-00',
  `lang` varchar(3) NOT NULL default 'pl',
  PRIMARY KEY  (`id`),
  KEY `user` (`user`)
) TYPE=MyISAM PACK_KEYS=0 AUTO_INCREMENT=1 ;

-- 
-- Zrzut danych tabeli `aktywacja`
-- 


-- --------------------------------------------------------

-- 
-- Struktura tabeli dla  `alchemy_mill`
-- 

CREATE TABLE `alchemy_mill` (
  `id` int(11) NOT NULL auto_increment,
  `name` varchar(60) NOT NULL default '',
  `owner` int(11) NOT NULL default '0',
  `illani` int(11) NOT NULL default '0',
  `illanias` int(11) NOT NULL default '0',
  `nutari` int(11) NOT NULL default '0',
  `cost` int(11) NOT NULL default '0',
  `level` int(11) NOT NULL default '0',
  `status` char(1) NOT NULL default 'S',
  `dynallca` int(11) NOT NULL default '0',
  `lang` varchar(3) NOT NULL default 'pl',
  PRIMARY KEY  (`id`)
) TYPE=MyISAM  PACK_KEYS=0 AUTO_INCREMENT=29 ;

-- 
-- Zrzut danych tabeli `alchemy_mill`
-- 

INSERT INTO `alchemy_mill` (`id`, `name`, `owner`, `illani`, `illanias`, `nutari`, `cost`, `level`, `status`, `dynallca`, `lang`) VALUES 
(1, 'bardzo silna mikstura z Nutari', 0, 0, 0, 50, 50000, 50, 'S', 0, 'pl'),
(2, 'silna mikstura z Nutari', 0, 0, 0, 10, 20000, 20, 'S', 0, 'pl'),
(3, 'mikstura z Nutari', 0, 0, 0, 5, 10000, 10, 'S', 0, 'pl'),
(4, 'słaba mikstura z Nutari', 0, 0, 0, 2, 4000, 5, 'S', 0, 'pl'),
(5, 'bardzo słaba mikstura z Nutari', 0, 0, 0, 1, 2000, 1, 'S', 0, 'pl'),
(6, 'bardzo słaba trucizna z Dynallca', 0, 0, 0, 0, 2000, 1, 'S', 1, 'pl'),
(7, 'słaba trucizna z Dynallca', 0, 0, 0, 0, 10000, 10, 'S', 5, 'pl'),
(8, 'trucizna z Dynallca', 0, 0, 0, 0, 20000, 20, 'S', 10, 'pl'),
(9, 'silna trucizna z Dynallca', 0, 0, 0, 0, 50000, 30, 'S', 20, 'pl'),
(10, 'bardzo silna trucizna z Dynallca', 0, 0, 0, 0, 100000, 50, 'S', 30, 'pl'),
(11, 'bardzo słaba mikstura z Illani', 0, 1, 0, 0, 2000, 1, 'S', 0, 'pl'),
(12, 'słaba mikstura z Illani', 0, 2, 0, 0, 4000, 5, 'S', 0, 'pl'),
(13, 'mikstura z Illani', 0, 5, 0, 0, 10000, 10, 'S', 0, 'pl'),
(14, 'silna mikstura z Illani', 0, 10, 0, 0, 20000, 20, 'S', 0, 'pl'),
(15, 'bardzo silna mikstura z Illani', 0, 20, 0, 0, 40000, 50, 'S', 0, 'pl'),
(16, 'bardzo słaba trucizna z Illani', 0, 2, 0, 0, 4000, 5, 'S', 1, 'pl'),
(17, 'słaba trucizna z Illani', 0, 5, 0, 0, 15000, 15, 'S', 2, 'pl'),
(18, 'trucizna z Illani', 0, 10, 0, 0, 25000, 25, 'S', 5, 'pl'),
(19, 'silna trucizna z Illani', 0, 20, 0, 0, 100000, 40, 'S', 10, 'pl'),
(20, 'bardzo silna trucizna z Illani', 0, 30, 0, 0, 200000, 60, 'S', 15, 'pl'),
(21, 'bardzo słaba trucizna z Nutari', 0, 0, 0, 2, 4000, 5, 'S', 1, 'pl'),
(22, 'słaba trucizna z Nutari', 0, 0, 0, 5, 15000, 15, 'S', 2, 'pl'),
(23, 'trucizna z Nutari', 0, 0, 0, 10, 25000, 25, 'S', 5, 'pl'),
(24, 'silna trucizna z Nutari', 0, 0, 0, 20, 100000, 40, 'S', 10, 'pl'),
(25, 'bardzo silna trucizna z Nutari', 0, 0, 0, 30, 200000, 60, 'S', 15, 'pl'),
(26, 'antidotum na truciznę z Illani', 0, 10, 10, 0, 20000, 20, 'S', 0, 'pl'),
(27, 'antidotum na truciznę z Nutari', 0, 0, 10, 10, 20000, 20, 'S', 0, 'pl'),
(28, 'antidotum na truciznę z Dynallca', 0, 0, 5, 0, 10000, 10, 'S', 5, 'pl');

-- --------------------------------------------------------

-- 
-- Struktura tabeli dla  `amarket`
-- 

CREATE TABLE `amarket` (
  `id` int(11) NOT NULL auto_increment,
  `seller` int(11) NOT NULL default '0',
  `type` varchar(2) NOT NULL default '',
  `number` tinyint(2) NOT NULL default '0',
  `amount` int(3) NOT NULL default '1',
  `cost` int(11) unsigned NOT NULL default '0',
  KEY `type` (`type`),
  KEY `number` (`number`),
  KEY `cost` (`cost`),
  KEY `id` (`id`),
  KEY `seller` (`seller`)
) TYPE=MyISAM  AUTO_INCREMENT=14 ;

-- 
-- Zrzut danych tabeli `amarket`
-- 


-- --------------------------------------------------------

-- 
-- Struktura tabeli dla  `astral`
-- 

CREATE TABLE `astral` (
  `owner` int(11) NOT NULL default '0',
  `type` varchar(2) NOT NULL default '',
  `number` tinyint(2) NOT NULL default '0',
  `amount` int(3) NOT NULL default '1',
  `location` char(1) NOT NULL default 'V',
  KEY `owner` (`owner`),
  KEY `type` (`type`),
  KEY `location` (`location`),
  KEY `number` (`number`)
) TYPE=MyISAM;

-- 
-- Zrzut danych tabeli `astral`
-- 


-- --------------------------------------------------------

-- 
-- Struktura tabeli dla  `astral_bank`
-- 

CREATE TABLE `astral_bank` (
  `owner` int(11) NOT NULL default '0',
  `level` tinyint(2) NOT NULL default '0',
  `location` char(1) NOT NULL default 'V',
  KEY `owner` (`owner`),
  KEY `location` (`location`)
) TYPE=MyISAM;

-- 
-- Zrzut danych tabeli `astral_bank`
-- 


-- --------------------------------------------------------

-- 
-- Struktura tabeli dla  `astral_machine`
-- 

CREATE TABLE `astral_machine` (
  `owner` int(11) NOT NULL default '0',
  `used` int(11) NOT NULL default '0',
  `directed` int(11) NOT NULL default '0',
  `aviable` char(1) NOT NULL default 'N',
  KEY `owner` (`owner`)
) TYPE=MyISAM;

-- 
-- Zrzut danych tabeli `astral_machine`
-- 


-- --------------------------------------------------------

-- 
-- Struktura tabeli dla  `astral_plans`
-- 

CREATE TABLE `astral_plans` (
  `owner` int(11) NOT NULL default '0',
  `name` varchar(2) NOT NULL default '',
  `amount` int(11) NOT NULL default '0',
  `location` char(1) NOT NULL default 'V',
  KEY `owner` (`owner`),
  KEY `location` (`location`)
) TYPE=MyISAM;

-- 
-- Zrzut danych tabeli `astral_plans`
-- 


-- --------------------------------------------------------

-- 
-- Struktura tabeli dla  `bad_words`
-- 

CREATE TABLE `bad_words` (
  `bword` varchar(255) NOT NULL default '',
  KEY `bword` (`bword`)
) TYPE=MyISAM;

-- 
-- Zrzut danych tabeli `bad_words`
-- 

INSERT INTO `bad_words` (`bword`) VALUES 
('chuj'),
('Chuj'),
('cieciu'),
('Cieciu'),
('Cipa'),
('cipa'),
('Cipo'),
('cipo'),
('cipom'),
('ciul'),
('Ciul'),
('dupa'),
('Dupa'),
('dupę'),
('dupe'),
('Dupe'),
('dupy'),
('Dupy'),
('dureń'),
('durniów'),
('dziwka'),
('Dziwka'),
('dziwko'),
('Dziwko'),
('gówno'),
('Gówno'),
('guwno'),
('Guwno'),
('huj'),
('Huj'),
('jeb'),
('Jeb'),
('jeban'),
('Jeban'),
('jebańcu'),
('kurw'),
('Kurw'),
('kurwa'),
('Kurwa'),
('kutas'),
('Kutas'),
('pieprzon'),
('Pieprzon'),
('pierdol'),
('Pierdol'),
('pizd'),
('Pizd'),
('pizda'),
('Pizda'),
('pizdom'),
('podupcon'),
('Podupcon'),
('pojeban'),
('Pojeban'),
('popierdolon'),
('Popierdolon'),
('skurwysyn'),
('Skurwysyn'),
('spierdalaj'),
('SPIERDALAJ'),
('sranie'),
('sukinsyn'),
('Sukinsyn'),
('wypierdalaj'),
('zajeb'),
('Zajeb'),
('zbuk'),
('zjeb'),
('Zjeb');

-- --------------------------------------------------------

-- 
-- Struktura tabeli dla  `ban`
-- 

CREATE TABLE `ban` (
  `type` varchar(10) NOT NULL default '',
  `amount` varchar(50) NOT NULL default '',
  KEY `type` (`type`)
) TYPE=MyISAM;

-- 
-- Zrzut danych tabeli `ban`
-- 


-- --------------------------------------------------------

-- 
-- Struktura tabeli dla  `ban_mail`
-- 

CREATE TABLE `ban_mail` (
  `id` int(11) NOT NULL default '0',
  `owner` int(11) NOT NULL default '0',
  KEY `owner` (`owner`),
  KEY `id` (`id`)
) TYPE=MyISAM;

-- 
-- Zrzut danych tabeli `ban_mail`
-- 


-- --------------------------------------------------------

-- 
-- Struktura tabeli dla  `bows`
-- 

CREATE TABLE `bows` (
  `id` int(11) NOT NULL auto_increment,
  `name` varchar(60) NOT NULL default '',
  `power` int(11) NOT NULL default '0',
  `type` char(1) NOT NULL default 'B',
  `cost` int(11) NOT NULL default '0',
  `minlev` int(2) NOT NULL default '1',
  `zr` int(11) NOT NULL default '0',
  `szyb` int(11) NOT NULL default '0',
  `maxwt` int(11) NOT NULL default '0',
  `lang` varchar(3) NOT NULL default 'pl',
  `repair` int(11) NOT NULL default '10',
  KEY `type` (`type`),
  KEY `id` (`id`)
) TYPE=MyISAM  PACK_KEYS=0 AUTO_INCREMENT=31 ;

-- 
-- Zrzut danych tabeli `bows`
-- 

INSERT INTO `bows` (`id`, `name`, `power`, `type`, `cost`, `minlev`, `zr`, `szyb`, `maxwt`, `lang`, `repair`) VALUES 
(1, 'Łuk ćwiczebny z leszczyny', 0, 'B', 100, 1, 0, 1, 40, 'pl', 2),
(2, 'Łuk giermka z leszczyny', 0, 'B', 400, 3, 0, 3, 40, 'pl', 6),
(3, 'Łuk krótki z leszczyny', 0, 'B', 800, 5, 0, 5, 40, 'pl', 10),
(4, 'Łuk myśliwski z leszczyny', 0, 'B', 1600, 10, 0, 10, 40, 'pl', 20),
(5, 'Łuk łowiecki z leszczyny', 0, 'B', 3200, 15, 0, 15, 40, 'pl', 30),
(6, 'Łuk zwiadowcy z leszczyny', 0, 'B', 6400, 20, 0, 20, 40, 'pl', 40),
(7, 'Łuk wojskowy z leszczyny', 0, 'B', 12800, 25, 0, 25, 40, 'pl', 50),
(8, 'Łuk bitweny z leszczyny', 0, 'B', 25600, 30, 0, 30, 40, 'pl', 60),
(9, 'Łuk angularny z leszczyny', 0, 'B', 51200, 40, 0, 40, 40, 'pl', 80),
(10, 'Łuk wojenny z leszczyny', 0, 'B', 102400, 50, 0, 50, 40, 'pl', 100),
(11, 'Łuk podwójny z leszczyny', 0, 'B', 204800, 60, 0, 60, 40, 'pl', 120),
(12, 'Łuk długi z leszczyny', 0, 'B', 409600, 70, 0, 70, 40, 'pl', 140),
(13, 'Łuk bojowy z leszczyny', 0, 'B', 819200, 80, 0, 80, 40, 'pl', 160),
(14, 'Łuk refleksyjny z leszczyny', 0, 'B', 1638400, 90, 0, 90, 40, 'pl', 180),
(15, 'Łuk retrorefleksyjny z leszczyny', 0, 'B', 3276800, 100, 0, 100, 40, 'pl', 200),
(16, 'Strzały ćwiczebne', 1, 'R', 50, 1, 0, 0, 20, 'pl', 0),
(17, 'Strzały turniejowe', 3, 'R', 200, 3, 0, 0, 20, 'pl', 0),
(18, 'Strzały krótkie', 5, 'R', 400, 5, 0, 0, 20, 'pl', 0),
(19, 'Strzały myśliwskie', 10, 'R', 800, 10, 0, 0, 20, 'pl', 0),
(20, 'Strzały łowieckie', 15, 'R', 1600, 15, 0, 0, 20, 'pl', 0),
(21, 'Strzały zwiadowcy', 20, 'R', 3200, 20, 0, 0, 20, 'pl', 0),
(22, 'Strzały wojskowe', 25, 'R', 6400, 25, 0, 0, 20, 'pl', 0),
(23, 'Strzały bitewne', 30, 'R', 12800, 30, 0, 0, 20, 'pl', 0),
(24, 'Strzały liściaste', 40, 'R', 25600, 40, 0, 0, 20, 'pl', 0),
(25, 'Strzały wojenne', 50, 'R', 51200, 50, 0, 0, 20, 'pl', 0),
(26, 'Strzały haczykowe', 60, 'R', 102400, 60, 0, 0, 20, 'pl', 0),
(27, 'Strzały długie', 70, 'R', 2048000, 70, 0, 0, 20, 'pl', 0),
(28, 'Strzały bojowe', 80, 'R', 409600, 80, 0, 0, 20, 'pl', 0),
(29, 'Strzały wężowe', 90, 'R', 819200, 90, 0, 0, 20, 'pl', 0),
(30, 'Strzały ząbkowane', 100, 'R', 1638400, 100, 0, 0, 20, 'pl', 0);

-- --------------------------------------------------------

-- 
-- Struktura tabeli dla  `bridge`
-- 

CREATE TABLE `bridge` (
  `id` int(11) NOT NULL auto_increment,
  `question` text NOT NULL,
  `answer` text NOT NULL,
  `lang` varchar(3) NOT NULL default 'pl',
  KEY `id` (`id`)
) TYPE=MyISAM  AUTO_INCREMENT=17 ;

-- 
-- Zrzut danych tabeli `bridge`
-- 

INSERT INTO `bridge` (`id`, `question`, `answer`, `lang`) VALUES 
(1, 'Stolica Abanasyni', 'Łubu-dubu', 'pl'),
(2, 'Jak się nazywa władca Vallheru', 'Thindil', 'pl'),
(3, 'Jak się nazywa główne miasto Vallheru', 'Altara', 'pl'),
(4, 'Ile to 2+2', '4', 'pl'),
(5, 'Pociąg jedzie z punktu A do punktu B, w jedną stronę trwa to 2 godziny w drugą godzinę i 20 minut. Podaj jaki jest wiek maszynisty biorąc pod uwagę że nosi on ciemnoniebieską koszulę', 'Skąd mam wiedzieć?', 'pl'),
(6, 'Jaka jest prędkość przelotowa jaskółki europejskiej', '23', 'pl'),
(7, 'Jaki jest udźwig jaskółki afrykańskiej', '1/2 kokosa', 'pl'),
(8, 'Jakie jest drugie słowo w naszym języku ', 'języku', 'pl'),
(9, 'Co wojownik ma pod łóżkiem', 'sprzątać', 'pl'),
(10, 'Co powstanie ze skrzyżowania węgorza elektrycznego i jeża', 'Elektryczna szczoteczka do zębów', 'pl'),
(11, 'Ile to jest 2+2*2 ?', '6', 'pl'),
(12, 'Jak karpie nazywają święta Bożego Narodzenia? ', 'Zaduszki', 'pl'),
(13, 'Jakiego koloru są czarne skrzynki w samolotach?', 'Czerwonego', 'pl'),
(14, 'Jak się nazywa właściciel Avan Tirith?', 'Myrdalis', 'pl'),
(15, 'Jak się nazywał mag, którego imieniem, nazwano miasto Ardulith?', 'Ardulith', 'pl'),
(16, 'Jak się nazywa słynny rewolucjonista, który został skazany na 999 lat pobytu w lochach?', 'Grejpfrut', 'pl');

-- --------------------------------------------------------

-- 
-- Struktura tabeli dla  `bugreport`
-- 

CREATE TABLE `bugreport` (
  `id` int(11) NOT NULL auto_increment,
  `sender` int(11) NOT NULL default '0',
  `title` varchar(255) NOT NULL default '',
  `type` varchar(20) NOT NULL default '',
  `location` varchar(255) NOT NULL default '',
  `desc` text NOT NULL,
  `resolution` tinyint(2) NOT NULL default '0',
  KEY `id` (`id`)
) TYPE=MyISAM AUTO_INCREMENT=1 ;

-- 
-- Zrzut danych tabeli `bugreport`
-- 


-- --------------------------------------------------------

-- 
-- Struktura tabeli dla  `bugtrack`
-- 

CREATE TABLE `bugtrack` (
  `id` int(11) NOT NULL auto_increment,
  `type` int(5) NOT NULL default '0',
  `info` varchar(255) NOT NULL default '',
  `amount` int(11) NOT NULL default '1',
  `file` varchar(255) NOT NULL default '',
  `line` int(11) NOT NULL default '0',
  `referer` varchar(255) NOT NULL default '',
  KEY `id` (`id`)
) TYPE=MyISAM  AUTO_INCREMENT=57 ;

-- 
-- Zrzut danych tabeli `bugtrack`
-- 


-- --------------------------------------------------------

-- 
-- Struktura tabeli dla  `categories`
-- 

CREATE TABLE `categories` (
  `id` int(11) NOT NULL auto_increment,
  `name` varchar(100) NOT NULL default '',
  `desc` varchar(255) NOT NULL default '',
  `lang` varchar(3) NOT NULL default 'pl',
  `perm_write` varchar(255) NOT NULL default 'All;',
  `perm_visit` varchar(255) NOT NULL default 'All;',
  PRIMARY KEY  (`id`)
) TYPE=MyISAM AUTO_INCREMENT=1 ;

-- 
-- Zrzut danych tabeli `categories`
-- 


-- --------------------------------------------------------

-- 
-- Struktura tabeli dla  `changelog`
-- 

CREATE TABLE `changelog` (
  `id` int(11) NOT NULL auto_increment,
  `author` varchar(255) NOT NULL default '',
  `location` varchar(255) NOT NULL default '',
  `text` text NOT NULL,
  `date` datetime NOT NULL default '0000-00-00 00:00:00',
  `lang` varchar(2) NOT NULL default '',
  KEY `id` (`id`)
) TYPE=MyISAM AUTO_INCREMENT=1 ;

-- 
-- Zrzut danych tabeli `changelog`
-- 


-- --------------------------------------------------------

-- 
-- Struktura tabeli dla  `chat`
-- 

CREATE TABLE `chat` (
  `id` int(11) NOT NULL auto_increment,
  `user` varchar(100) NOT NULL default '',
  `chat` text NOT NULL,
  `senderid` int(11) NOT NULL default '0',
  `ownerid` int(11) NOT NULL default '0',
  `lang` varchar(3) NOT NULL default 'pl',
  PRIMARY KEY  (`id`),
  KEY `id_2` (`id`)
) TYPE=MyISAM PACK_KEYS=0 AUTO_INCREMENT=1 ;

-- 
-- Zrzut danych tabeli `chat`
-- 


-- --------------------------------------------------------

-- 
-- Struktura tabeli dla  `chat_config`
-- 

CREATE TABLE `chat_config` (
  `id` int(11) NOT NULL auto_increment,
  `cisza` char(2) NOT NULL default 'Y',
  `gracz` int(11) NOT NULL default '0',
  `resets` int(11) NOT NULL default '0',
  UNIQUE KEY `gracz` (`gracz`),
  KEY `id` (`id`)
) TYPE=MyISAM  PACK_KEYS=0 AUTO_INCREMENT=9 ;

-- 
-- Zrzut danych tabeli `chat_config`
-- 


-- --------------------------------------------------------

-- 
-- Struktura tabeli dla  `core`
-- 

CREATE TABLE `core` (
  `id` int(11) NOT NULL auto_increment,
  `owner` int(11) NOT NULL default '0',
  `name` varchar(20) NOT NULL default '',
  `type` varchar(20) NOT NULL default '',
  `ref_id` int(11) NOT NULL default '0',
  `power` double(11,3) NOT NULL default '0.000',
  `defense` double(11,3) NOT NULL default '0.000',
  `status` varchar(5) NOT NULL default 'Alive',
  `active` char(1) NOT NULL default 'N',
  `corename` varchar(30) NOT NULL default '',
  `gender` char(1) NOT NULL default '',
  `wins` int(11) NOT NULL default '0',
  `losses` int(11) NOT NULL default '0',
  KEY `id` (`id`),
  KEY `name` (`name`),
  KEY `corename` (`corename`),
  KEY `wins` (`wins`),
  KEY `owner` (`owner`)
) TYPE=MyISAM PACK_KEYS=0 AUTO_INCREMENT=1 ;

-- 
-- Zrzut danych tabeli `core`
-- 


-- --------------------------------------------------------

-- 
-- Struktura tabeli dla  `core_market`
-- 

CREATE TABLE `core_market` (
  `id` int(11) NOT NULL auto_increment,
  `name` varchar(20) NOT NULL default '',
  `cost` int(11) unsigned NOT NULL default '0',
  `seller` int(11) NOT NULL default '0',
  `type` varchar(20) NOT NULL default '',
  `power` double(11,3) NOT NULL default '0.000',
  `defense` double(11,3) NOT NULL default '0.000',
  `gender` char(1) NOT NULL default '',
  `ref_id` int(11) NOT NULL default '0',
  `wins` int(11) NOT NULL default '0',
  `losses` int(11) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `id` (`id`)
) TYPE=MyISAM  PACK_KEYS=0 AUTO_INCREMENT=6 ;

-- 
-- Zrzut danych tabeli `core_market`
-- 


-- --------------------------------------------------------

-- 
-- Struktura tabeli dla  `cores`
-- 

CREATE TABLE `cores` (
  `id` int(11) NOT NULL auto_increment,
  `name` varchar(20) NOT NULL default '',
  `type` varchar(20) NOT NULL default '',
  `power` double(11,4) NOT NULL default '1.0000',
  `defense` double(11,4) NOT NULL default '1.0000',
  `rarity` int(1) NOT NULL default '1',
  `desc` text NOT NULL,
  `lang` varchar(3) NOT NULL default 'pl',
  PRIMARY KEY  (`id`),
  KEY `id` (`id`)
) TYPE=MyISAM  AUTO_INCREMENT=31 ;

-- 
-- Zrzut danych tabeli `cores`
-- 

INSERT INTO `cores` (`id`, `name`, `type`, `power`, `defense`, `rarity`, `desc`, `lang`) VALUES 
(1, 'Łasica', 'Plant', 1.0000, 1.0000, 1, '', 'pl'),
(2, 'Sokół', 'Plant', 2.0000, 2.0000, 1, '', 'pl'),
(3, 'Jeleń', 'Plant', 3.0000, 3.0000, 1, '', 'pl'),
(4, 'Dzik', 'Plant', 4.0000, 4.0000, 2, '', 'pl'),
(5, 'Niedźwiedź', 'Plant', 5.0000, 5.0000, 3, '', 'pl'),
(6, 'Delfin', 'Aqua', 6.0000, 6.0000, 1, '', 'pl'),
(7, 'Rekin', 'Aqua', 7.0000, 7.0000, 1, '', 'pl'),
(8, 'Wieloryb', 'Aqua', 8.0000, 8.0000, 1, '', 'pl'),
(9, 'Sylfida', 'Aqua', 9.0000, 9.0000, 2, '', 'pl'),
(10, 'Kraken', 'Aqua', 10.0000, 10.0000, 3, '', 'pl'),
(11, 'Wilk', 'Material', 11.0000, 11.0000, 1, '', 'pl'),
(12, 'Wielki Orzeł', 'Material', 12.0000, 12.0000, 1, '', 'pl'),
(13, 'Harpia', 'Material', 13.0000, 13.0000, 1, '', 'pl'),
(14, 'Czarny Niedźwiedź', 'Material', 14.0000, 14.0000, 2, '', 'pl'),
(15, 'Sasquatch', 'Material', 15.0000, 15.0000, 3, '', 'pl'),
(16, 'Bizon', 'Element', 16.0000, 16.0000, 1, '', 'pl'),
(17, 'Nosorożec', 'Element', 17.0000, 17.0000, 1, '', 'pl'),
(18, 'Feniks', 'Element', 18.0000, 18.0000, 1, '', 'pl'),
(19, 'Pegaz', 'Element', 19.0000, 19.0000, 2, '', 'pl'),
(20, 'Jednorożec', 'Element', 20.0000, 20.0000, 3, '', 'pl'),
(21, 'Wielki Skorpion', 'Alien', 21.0000, 21.0000, 1, '', 'pl'),
(22, 'Pustynny wąż', 'Alien', 22.0000, 22.0000, 1, '', 'pl'),
(23, 'Gryf', 'Alien', 23.0000, 23.0000, 1, '', 'pl'),
(24, 'Manticora', 'Alien', 24.0000, 24.0000, 2, '', 'pl'),
(25, 'Olbrzymi jaszczur', 'Alien', 25.0000, 25.0000, 3, '', 'pl'),
(26, 'Astralny Wojownik', 'Ancient', 26.0000, 26.0000, 1, '', 'pl'),
(27, 'Behemoth', 'Ancient', 27.0000, 27.0000, 1, '', 'pl'),
(28, 'Zielony Smok', 'Ancient', 50.0000, 50.0000, 1, '', 'pl'),
(29, 'Czerwony Smok', 'Ancient', 100.0000, 100.0000, 2, '', 'pl'),
(30, 'Czarny Smok', 'Ancient', 150.0000, 150.0000, 3, '', 'pl');

-- --------------------------------------------------------

-- 
-- Struktura tabeli dla  `court`
-- 

CREATE TABLE `court` (
  `id` int(11) NOT NULL auto_increment,
  `title` varchar(255) NOT NULL default '',
  `body` text NOT NULL,
  `lang` varchar(2) NOT NULL default 'pl',
  `type` varchar(20) NOT NULL default 'case',
  `date` date NOT NULL default '0000-00-00',
  KEY `id` (`id`)
) TYPE=MyISAM AUTO_INCREMENT=1 ;

-- 
-- Zrzut danych tabeli `court`
-- 


-- --------------------------------------------------------

-- 
-- Struktura tabeli dla  `court_cases`
-- 

CREATE TABLE `court_cases` (
  `id` int(11) NOT NULL auto_increment,
  `textid` int(11) NOT NULL default '0',
  `author` varchar(40) NOT NULL default '',
  `body` text NOT NULL,
  KEY `id` (`id`)
) TYPE=MyISAM AUTO_INCREMENT=1 ;

-- 
-- Zrzut danych tabeli `court_cases`
-- 


-- --------------------------------------------------------

-- 
-- Struktura tabeli dla  `czary`
-- 

CREATE TABLE `czary` (
  `id` int(11) NOT NULL auto_increment,
  `nazwa` varchar(30) NOT NULL default '',
  `gracz` int(11) NOT NULL default '0',
  `cena` int(11) NOT NULL default '0',
  `poziom` int(11) NOT NULL default '1',
  `typ` char(1) NOT NULL default 'B',
  `obr` double(11,1) NOT NULL default '1.0',
  `status` char(1) NOT NULL default 'S',
  `lang` varchar(3) NOT NULL default 'pl',
  KEY `id` (`id`)
) TYPE=MyISAM  PACK_KEYS=0 AUTO_INCREMENT=46 ;

-- 
-- Zrzut danych tabeli `czary`
-- 

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

-- --------------------------------------------------------

-- 
-- Struktura tabeli dla  `donators`
-- 

CREATE TABLE `donators` (
  `name` varchar(30) NOT NULL default '',
  KEY `name` (`name`)
) TYPE=MyISAM;

-- 
-- Zrzut danych tabeli `donators`
-- 

INSERT INTO `donators` (`name`) VALUES 
('Agatka Saurelin'),
('Agloval'),
('Arronax'),
('Arvena Loria'),
('Corwin'),
('Dellas'),
('Dwalin'),
('Eldarion'),
('Furiomir'),
('Graffi'),
('Irian'),
('Jaro'),
('Kalarone'),
('Keraj'),
('Lupus'),
('malenka'),
('Mario'),
('Mariopan'),
('Nebu'),
('Niris'),
('PaVe'),
('Regulus'),
('Solostran'),
('Syraell'),
('Telcontar'),
('Topek'),
('Ugly'),
('WilQ'),
('Yarpan'),
('Zbójnik'),
('Zibbor');

-- --------------------------------------------------------

-- 
-- Struktura tabeli dla  `equipment`
-- 

CREATE TABLE `equipment` (
  `id` int(11) NOT NULL auto_increment,
  `owner` int(11) NOT NULL default '0',
  `name` varchar(100) NOT NULL default '',
  `power` int(11) NOT NULL default '0',
  `status` char(1) NOT NULL default 'U',
  `type` char(1) NOT NULL default 'W',
  `cost` int(11) unsigned NOT NULL default '0',
  `minlev` int(2) NOT NULL default '1',
  `zr` int(11) NOT NULL default '0',
  `wt` int(11) NOT NULL default '0',
  `szyb` int(11) NOT NULL default '0',
  `maxwt` int(11) NOT NULL default '0',
  `magic` char(1) NOT NULL default 'N',
  `poison` int(11) NOT NULL default '0',
  `amount` int(11) NOT NULL default '1',
  `twohand` char(1) NOT NULL default 'N',
  `lang` varchar(3) NOT NULL default 'pl',
  `ptype` char(1) NOT NULL default '',
  `repair` int(11) NOT NULL default '10',
  `location` varchar(20) NOT NULL default 'Altara',
  KEY `status` (`status`),
  KEY `type` (`type`),
  KEY `owner` (`owner`),
  KEY `id` (`id`)
) TYPE=MyISAM  PACK_KEYS=0 AUTO_INCREMENT=76 ;

-- 
-- Zrzut danych tabeli `equipment`
-- 

INSERT INTO `equipment` (`id`, `owner`, `name`, `power`, `status`, `type`, `cost`, `minlev`, `zr`, `wt`, `szyb`, `maxwt`, `magic`, `poison`, `amount`, `twohand`, `lang`, `ptype`, `repair`, `location`) VALUES 
(1, 0, 'Anima z miedzi', 3, 'S', 'A', 200, 1, 0, 40, 0, 40, 'N', 0, 3, 'N', 'pl', '', 2, 'Altara'),
(2, 0, 'Bajdana z miedzi', 9, 'S', 'A', 800, 3, 1, 40, 0, 40, 'N', 0, 1, 'N', 'pl', '', 6, 'Altara'),
(3, 0, 'Brygantyna z miedzi', 15, 'S', 'A', 1600, 5, 2, 40, 0, 40, 'N', 0, 1, 'N', 'pl', '', 10, 'Altara'),
(4, 0, 'Koszulka kolcza z miedzi', 30, 'S', 'A', 3200, 10, 5, 40, 0, 40, 'N', 0, 1, 'N', 'pl', '', 20, 'Altara'),
(5, 0, 'Kaftan kolczy z miedzi', 45, 'S', 'A', 6400, 15, 7, 40, 0, 40, 'N', 0, 1, 'N', 'pl', '', 30, 'Altara'),
(6, 0, 'Kirys z miedzi', 60, 'S', 'A', 12800, 20, 10, 40, 0, 40, 'N', 0, 1, 'N', 'pl', '', 40, 'Altara'),
(7, 0, 'Kolczuga z miedzi', 75, 'S', 'A', 25600, 25, 12, 40, 0, 40, 'N', 0, 1, 'N', 'pl', '', 50, 'Altara'),
(8, 0, 'Zbroja lamelkowa z miedzi', 90, 'S', 'A', 51200, 30, 15, 40, 0, 40, 'N', 0, 1, 'N', 'pl', '', 60, 'Altara'),
(9, 0, 'Zbroja karacenowa z miedzi', 150, 'S', 'A', 204800, 50, 25, 40, 0, 40, 'N', 0, 1, 'N', 'pl', '', 100, 'Altara'),
(10, 0, 'Zbroja paskowa z miedzi', 180, 'S', 'A', 409600, 60, 30, 40, 0, 40, 'N', 0, 1, 'N', 'pl', '', 120, 'Altara'),
(11, 0, 'Karacena z miedzi', 210, 'S', 'A', 819200, 70, 35, 40, 0, 40, 'N', 0, 1, 'N', 'pl', '', 140, 'Altara'),
(12, 0, 'Zbroja łuskowa z miedzi', 120, 'S', 'A', 102400, 40, 20, 40, 0, 40, 'N', 0, 1, 'N', 'pl', '', 80, 'Altara'),
(13, 0, 'Zbroja półpłytowa z miedzi', 240, 'S', 'A', 1638400, 80, 40, 40, 0, 40, 'N', 0, 1, 'N', 'pl', '', 160, 'Altara'),
(14, 0, 'Zbroja płytowa z miedzi', 270, 'S', 'A', 3276800, 90, 45, 40, 0, 40, 'N', 0, 1, 'N', 'pl', '', 180, 'Altara'),
(15, 0, 'Zbroja zwieciadlana z miedzi', 300, 'S', 'A', 6553600, 100, 50, 40, 0, 40, 'N', 0, 1, 'N', 'pl', '', 200, 'Altara'),
(16, 0, 'Mały puklerz z miedzi', 1, 'S', 'S', 100, 1, 0, 20, 0, 20, 'N', 0, 1, 'N', 'pl', '', 1, 'Altara'),
(17, 0, 'Puklerz z miedzi', 3, 'S', 'S', 400, 3, 0, 20, 0, 20, 'N', 0, 1, 'N', 'pl', '', 3, 'Altara'),
(18, 0, 'Mała tarcza z miedzi', 5, 'S', 'S', 800, 5, 0, 20, 0, 20, 'N', 0, 1, 'N', 'pl', '', 5, 'Altara'),
(19, 0, 'Sipar z miedzi', 10, 'S', 'S', 1600, 10, 0, 20, 0, 20, 'N', 0, 1, 'N', 'pl', '', 10, 'Altara'),
(20, 0, 'Średnia tarcza z miedzi', 15, 'S', 'S', 3200, 15, 0, 20, 0, 20, 'N', 0, 1, 'N', 'pl', '', 15, 'Altara'),
(21, 0, 'Trójkątna tacza z miedzi', 20, 'S', 'S', 6400, 20, 0, 20, 0, 20, 'N', 0, 1, 'N', 'pl', '', 20, 'Altara'),
(22, 0, 'Wielka tarcza z miedzi', 25, 'S', 'S', 12800, 25, 0, 20, 0, 20, 'N', 0, 1, 'N', 'pl', '', 25, 'Altara'),
(23, 0, 'Tarcza migdałowa z miedzi', 30, 'S', 'S', 25600, 30, 0, 20, 0, 20, 'N', 0, 1, 'N', 'pl', '', 30, 'Altara'),
(24, 0, 'Prostokątna tarcza z miedzi', 40, 'S', 'S', 51200, 40, 0, 20, 0, 20, 'N', 0, 1, 'N', 'pl', '', 40, 'Altara'),
(25, 0, 'Pawęż z miedzi', 50, 'S', 'S', 102400, 50, 0, 20, 0, 20, 'N', 0, 1, 'N', 'pl', '', 50, 'Altara'),
(26, 0, 'Ciężka tarcza z miedzi', 60, 'S', 'S', 204800, 60, 0, 20, 0, 20, 'N', 0, 1, 'N', 'pl', '', 60, 'Altara'),
(27, 0, 'Tarcza turniejowa z miedzi', 70, 'S', 'S', 409600, 70, 0, 20, 0, 20, 'N', 0, 1, 'N', 'pl', '', 70, 'Altara'),
(28, 0, 'Rycerska tarcza z miedzi', 80, 'S', 'S', 819200, 80, 0, 20, 0, 20, 'N', 0, 1, 'N', 'pl', '', 80, 'Altara'),
(29, 0, 'Kolczasta tarcza z miedzi', 90, 'S', 'S', 1638400, 90, 0, 20, 0, 20, 'N', 0, 1, 'N', 'pl', '', 90, 'Altara'),
(30, 0, 'Żołnierska tarcza z miedzi', 100, 'S', 'S', 3276800, 100, 0, 20, 0, 20, 'N', 0, 1, 'N', 'pl', '', 100, 'Altara'),
(31, 0, 'Kolczy czepiec z miedzi', 1, 'S', 'H', 100, 1, 0, 20, 0, 20, 'N', 0, 1, 'N', 'pl', '', 1, 'Altara'),
(32, 0, 'Szyszak z miedzi', 3, 'S', 'H', 400, 3, 0, 20, 0, 20, 'N', 0, 1, 'N', 'pl', '', 3, 'Altara'),
(33, 0, 'Szyszak z kołnierzem z miedzi', 5, 'S', 'H', 800, 5, 0, 20, 0, 20, 'N', 0, 1, 'N', 'pl', '', 5, 'Altara'),
(34, 0, 'Kapalin z miedzi', 10, 'S', 'H', 1600, 10, 0, 20, 0, 20, 'N', 0, 1, 'N', 'pl', '', 10, 'Altara'),
(35, 0, 'Łebka z miedzi', 15, 'S', 'H', 3200, 15, 0, 20, 0, 20, 'N', 0, 1, 'N', 'pl', '', 15, 'Altara'),
(36, 0, 'Hełm otwarty z miedzi', 20, 'S', 'H', 6400, 20, 0, 20, 0, 20, 'N', 0, 1, 'N', 'pl', '', 20, 'Altara'),
(37, 0, 'Hełm stożkowy z miedzi', 25, 'S', 'H', 12800, 25, 0, 20, 0, 20, 'N', 0, 1, 'N', 'pl', '', 25, 'Altara'),
(38, 0, 'Hełm garnczkowy z miedzi', 30, 'S', 'H', 25600, 30, 0, 20, 0, 20, 'N', 0, 1, 'N', 'pl', '', 30, 'Altara'),
(39, 0, 'Hełm zamknięty z miedzi', 40, 'S', 'H', 51200, 40, 0, 20, 0, 20, 'N', 0, 1, 'N', 'pl', '', 40, 'Altara'),
(40, 0, 'Hełm obręczowy z miedzi', 50, 'S', 'H', 102400, 50, 0, 20, 0, 20, 'N', 0, 1, 'N', 'pl', '', 50, 'Altara'),
(41, 0, 'Hełm rycerski z miedzi', 60, 'S', 'H', 204800, 60, 0, 20, 0, 20, 'N', 0, 1, 'N', 'pl', '', 60, 'Altara'),
(42, 0, 'hełm przyłbicowy z miedzi', 70, 'S', 'H', 409600, 70, 0, 20, 0, 20, 'N', 0, 1, 'N', 'pl', '', 70, 'Altara'),
(43, 0, 'Armet z miedzi', 80, 'S', 'H', 819200, 80, 0, 20, 0, 20, 'N', 0, 1, 'N', 'pl', '', 80, 'Altara'),
(44, 0, 'Rogaty hełm z miedzi', 90, 'S', 'H', 1638400, 90, 0, 20, 0, 20, 'N', 0, 1, 'N', 'pl', '', 90, 'Altara'),
(45, 0, 'Wielki hełm z miedzi', 100, 'S', 'H', 3276800, 100, 0, 20, 0, 20, 'N', 0, 1, 'N', 'pl', '', 100, 'Altara'),
(46, 0, 'Ochraniacze kolcze z miedzi', 1, 'S', 'L', 100, 1, 0, 20, 0, 20, 'N', 0, 1, 'N', 'pl', '', 1, 'Altara'),
(47, 0, 'Nagolenniki kolcze z miedzi', 3, 'S', 'L', 400, 3, 0, 20, 0, 20, 'N', 0, 1, 'N', 'pl', '', 3, 'Altara'),
(48, 0, 'Nogawice kolcze z miedzi', 5, 'S', 'L', 800, 5, 1, 20, 0, 20, 'N', 0, 1, 'N', 'pl', '', 5, 'Altara'),
(49, 0, 'Nagolenniki żeberkowe z miedzi', 10, 'S', 'L', 1600, 10, 2, 20, 0, 20, 'N', 0, 1, 'N', 'pl', '', 10, 'Altara'),
(50, 0, 'Nogawice żeberkowe z miedzi', 15, 'S', 'L', 3200, 15, 3, 20, 0, 20, 'N', 0, 1, 'N', 'pl', '', 15, 'Altara'),
(51, 0, 'Nagolenniki łuskowe z miedzi', 20, 'S', 'L', 6400, 20, 4, 20, 0, 20, 'N', 0, 1, 'N', 'pl', '', 20, 'Altara'),
(52, 0, 'Nogawice łuskowe z miedzi', 25, 'S', 'L', 12800, 25, 5, 20, 0, 20, 'N', 0, 1, 'N', 'pl', '', 25, 'Altara'),
(53, 0, 'Nagolenniki paskowe z miedzi', 30, 'S', 'L', 25600, 30, 6, 20, 0, 20, 'N', 0, 1, 'N', 'pl', '', 30, 'Altara'),
(54, 0, 'Nogawice paskowe z miedzi', 40, 'S', 'L', 51200, 40, 8, 20, 0, 20, 'N', 0, 1, 'N', 'pl', '', 40, 'Altara'),
(55, 0, 'Nagolenniki lamelkowe z miedzi', 50, 'S', 'L', 102400, 50, 10, 20, 0, 20, 'N', 0, 1, 'N', 'pl', '', 50, 'Altara'),
(56, 0, 'Nogawice lamelkowe z miedzi', 60, 'S', 'L', 204800, 60, 12, 20, 0, 20, 'N', 0, 1, 'N', 'pl', '', 60, 'Altara'),
(57, 0, 'Nagolenniki półpłytowe z miedzi', 70, 'S', 'L', 409600, 70, 14, 20, 0, 20, 'N', 0, 1, 'N', 'pl', '', 70, 'Altara'),
(58, 0, 'Nogawice półpłytowe z miedzi', 80, 'S', 'L', 819200, 80, 16, 20, 0, 20, 'N', 0, 1, 'N', 'pl', '', 80, 'Altara'),
(59, 0, 'Nagolenniki płytowe z miedzi', 90, 'S', 'L', 1638400, 90, 18, 20, 0, 20, 'N', 0, 1, 'N', 'pl', '', 90, 'Altara'),
(60, 0, 'Nogawice płytowe z miedzi', 100, 'S', 'L', 3276800, 100, 20, 20, 0, 20, 'N', 0, 1, 'N', 'pl', '', 100, 'Altara'),
(61, 0, 'Krótki miecz z miedzi', 1, 'S', 'W', 100, 1, 0, 40, 0, 40, 'N', 0, 1, 'N', 'pl', '', 2, 'Altara'),
(62, 0, 'Topór ręczny z miedzi', 3, 'S', 'W', 400, 3, 0, 40, 0, 40, 'N', 0, 1, 'N', 'pl', '', 6, 'Altara'),
(63, 0, 'Rapier z miedzi', 5, 'S', 'W', 800, 5, 0, 40, 0, 40, 'N', 0, 1, 'N', 'pl', '', 10, 'Altara'),
(64, 0, 'Szabla z miedzi', 10, 'S', 'W', 1600, 10, 0, 40, 0, 40, 'N', 0, 1, 'N', 'pl', '', 20, 'Altara'),
(65, 0, 'Morgensztern z miedzi', 15, 'S', 'W', 3200, 15, 0, 40, 0, 40, 'N', 0, 1, 'N', 'pl', '', 30, 'Altara'),
(66, 0, 'Scimitar z miedzi', 20, 'S', 'W', 6400, 20, 0, 40, 0, 40, 'N', 0, 1, 'N', 'pl', '', 40, 'Altara'),
(67, 0, 'Lekki korbacz z miedzi', 25, 'S', 'W', 12800, 25, 0, 40, 0, 40, 'N', 0, 1, 'N', 'pl', '', 50, 'Altara'),
(68, 0, 'Pałasz z miedzi', 30, 'S', 'W', 25600, 30, 0, 40, 0, 40, 'N', 0, 1, 'N', 'pl', '', 60, 'Altara'),
(69, 0, 'Topór żołnierski z miedzi', 40, 'S', 'W', 51200, 40, 0, 40, 0, 40, 'N', 0, 1, 'N', 'pl', '', 80, 'Altara'),
(70, 0, 'Młot bojowy z miedzi', 50, 'S', 'W', 102400, 50, 0, 40, 0, 40, 'N', 0, 1, 'N', 'pl', '', 100, 'Altara'),
(71, 0, 'Długi miecz z miedzi', 60, 'S', 'W', 204800, 60, 0, 40, 0, 40, 'N', 0, 1, 'N', 'pl', '', 120, 'Altara'),
(72, 0, 'Ciężki korbacz z miedzi', 70, 'S', 'W', 409600, 70, 0, 40, 0, 40, 'N', 0, 1, 'N', 'pl', '', 140, 'Altara'),
(73, 0, 'Katana z miedzi', 80, 'S', 'W', 819200, 80, 0, 40, 0, 40, 'N', 0, 1, 'N', 'pl', '', 160, 'Altara'),
(74, 0, 'Topór bitewny z miedzi', 90, 'S', 'W', 1638400, 90, 0, 40, 0, 40, 'N', 0, 1, 'N', 'pl', '', 180, 'Altara'),
(75, 0, 'Bastard z miedzi', 100, 'S', 'W', 3276800, 100, 0, 40, 0, 40, 'N', 0, 1, 'N', 'pl', '', 200, 'Altara');

-- --------------------------------------------------------

-- 
-- Struktura tabeli dla  `events`
-- 

CREATE TABLE `events` (
  `id` int(11) NOT NULL auto_increment,
  `text` text NOT NULL,
  `lang` varchar(3) NOT NULL default 'pl',
  KEY `id` (`id`)
) TYPE=MyISAM AUTO_INCREMENT=1 ;

-- 
-- Zrzut danych tabeli `events`
-- 


-- --------------------------------------------------------

-- 
-- Struktura tabeli dla  `farm`
-- 

CREATE TABLE `farm` (
  `id` int(11) NOT NULL auto_increment,
  `owner` int(11) NOT NULL default '0',
  `amount` int(11) NOT NULL default '0',
  `name` varchar(20) default NULL,
  `age` int(3) NOT NULL default '0',
  KEY `owner` (`owner`),
  KEY `id` (`id`)
) TYPE=MyISAM  PACK_KEYS=0 AUTO_INCREMENT=116 ;

-- 
-- Zrzut danych tabeli `farm`
-- 


-- --------------------------------------------------------

-- 
-- Struktura tabeli dla  `farms`
-- 

CREATE TABLE `farms` (
  `id` int(11) NOT NULL auto_increment,
  `owner` int(11) NOT NULL default '0',
  `lands` int(11) NOT NULL default '0',
  `glasshouse` int(11) NOT NULL default '0',
  `irrigation` int(11) NOT NULL default '0',
  `creeper` int(11) NOT NULL default '0',
  KEY `id` (`id`),
  KEY `owner` (`owner`)
) TYPE=MyISAM AUTO_INCREMENT=1 ;

-- 
-- Zrzut danych tabeli `farms`
-- 


-- --------------------------------------------------------

-- 
-- Struktura tabeli dla  `halloffame`
-- 

CREATE TABLE `halloffame` (
  `id` int(11) NOT NULL auto_increment,
  `heroid` int(11) NOT NULL default '0',
  `oldname` varchar(100) NOT NULL default '',
  `herorace` varchar(100) NOT NULL default '',
  `newid` int(11) NOT NULL default '0',
  KEY `id` (`id`)
) TYPE=MyISAM AUTO_INCREMENT=1 ;

-- 
-- Zrzut danych tabeli `halloffame`
-- 


-- --------------------------------------------------------

-- 
-- Struktura tabeli dla  `herbs`
-- 

CREATE TABLE `herbs` (
  `id` int(11) NOT NULL auto_increment,
  `gracz` int(11) NOT NULL default '0',
  `illani` int(11) NOT NULL default '0',
  `illanias` int(11) NOT NULL default '0',
  `nutari` int(11) NOT NULL default '0',
  `dynallca` int(11) NOT NULL default '0',
  `ilani_seeds` int(11) NOT NULL default '0',
  `illanias_seeds` int(11) NOT NULL default '0',
  `nutari_seeds` int(11) NOT NULL default '0',
  `dynallca_seeds` int(11) NOT NULL default '0',
  PRIMARY KEY  (`gracz`),
  UNIQUE KEY `id` (`id`)
) TYPE=MyISAM PACK_KEYS=0 AUTO_INCREMENT=1 ;

-- 
-- Zrzut danych tabeli `herbs`
-- 


-- --------------------------------------------------------

-- 
-- Struktura tabeli dla  `hmarket`
-- 

CREATE TABLE `hmarket` (
  `id` int(11) NOT NULL auto_increment,
  `seller` int(11) NOT NULL default '0',
  `ilosc` int(11) NOT NULL default '0',
  `cost` int(11) unsigned NOT NULL default '0',
  `nazwa` varchar(30) NOT NULL default '',
  `lang` varchar(3) NOT NULL default 'pl',
  UNIQUE KEY `id` (`id`)
) TYPE=MyISAM PACK_KEYS=0 AUTO_INCREMENT=1 ;

-- 
-- Zrzut danych tabeli `hmarket`
-- 


-- --------------------------------------------------------

-- 
-- Struktura tabeli dla  `houses`
-- 

CREATE TABLE `houses` (
  `id` int(11) NOT NULL auto_increment,
  `owner` int(11) NOT NULL default '0',
  `size` int(11) NOT NULL default '1',
  `value` int(11) NOT NULL default '1',
  `bedroom` char(1) NOT NULL default 'N',
  `wardrobe` int(11) NOT NULL default '0',
  `points` int(11) NOT NULL default '10',
  `name` varchar(60) default NULL,
  `used` int(11) NOT NULL default '0',
  `build` int(11) NOT NULL default '0',
  `locator` int(11) NOT NULL default '0',
  `cost` int(11) unsigned NOT NULL default '0',
  `seller` int(11) NOT NULL default '0',
  `location` varchar(20) NOT NULL default 'Altara',
  KEY `id` (`id`),
  KEY `owner` (`owner`)
) TYPE=MyISAM PACK_KEYS=0 AUTO_INCREMENT=1 ;

-- 
-- Zrzut danych tabeli `houses`
-- 


-- --------------------------------------------------------

-- 
-- Struktura tabeli dla  `jail`
-- 

CREATE TABLE `jail` (
  `id` int(11) NOT NULL auto_increment,
  `prisoner` int(11) NOT NULL default '0',
  `duration` int(3) NOT NULL default '0',
  `data` date NOT NULL default '0000-00-00',
  `verdict` text NOT NULL,
  `cost` int(11) unsigned NOT NULL default '0',
  KEY `id` (`id`)
) TYPE=MyISAM  PACK_KEYS=0 AUTO_INCREMENT=35 ;

-- 
-- Zrzut danych tabeli `jail`
-- 


-- --------------------------------------------------------

-- 
-- Struktura tabeli dla  `jeweller`
-- 

CREATE TABLE `jeweller` (
  `id` int(11) NOT NULL auto_increment,
  `owner` int(11) NOT NULL default '0',
  `name` varchar(60) NOT NULL default '',
  `type` char(1) NOT NULL default 'I',
  `cost` int(11) NOT NULL default '0',
  `level` smallint(4) NOT NULL default '0',
  `bonus` int(11) NOT NULL default '0',
  `lang` varchar(3) NOT NULL default 'pl',
  KEY `id` (`id`),
  KEY `owner` (`owner`),
  KEY `name` (`name`)
) TYPE=MyISAM  AUTO_INCREMENT=16 ;

-- 
-- Zrzut danych tabeli `jeweller`
-- 

INSERT INTO `jeweller` (`id`, `owner`, `name`, `type`, `cost`, `level`, `bonus`, `lang`) VALUES 
(1, 0, 'pierścień', 'I', 50, 1, 0, 'pl'),
(2, 0, 'pierścień nowicjusza', 'I', 100, 1, 1, 'pl'),
(3, 0, 'pierścień ucznia', 'I', 1000, 5, 5, 'pl'),
(4, 0, 'pierścień adepta', 'I', 2500, 10, 17, 'pl'),
(5, 0, 'pierścień badacza', 'I', 5000, 15, 29, 'pl'),
(6, 0, 'pierścień znawcy', 'I', 10000, 20, 46, 'pl'),
(7, 0, 'pierścień agenta', 'I', 20000, 25, 63, 'pl'),
(8, 0, 'pierścień immakulauta', 'I', 40000, 30, 91, 'pl'),
(9, 0, 'pierścień przeora', 'I', 80000, 40, 137, 'pl'),
(10, 0, 'pierścień eksperta', 'I', 160000, 50, 183, 'pl'),
(11, 0, 'pierścień lorda', 'I', 320000, 60, 231, 'pl'),
(12, 0, 'pierścień mistrza', 'I', 640000, 70, 279, 'pl'),
(13, 0, 'pierścień arcymistrza', 'I', 1280000, 80, 329, 'pl'),
(14, 0, 'pierścień wielkiego mistrza', 'I', 2560000, 90, 379, 'pl'),
(15, 0, 'pierścień nadmistrza', 'I', 5120000, 100, 431, 'pl');

-- --------------------------------------------------------

-- 
-- Struktura tabeli dla  `jeweller_work`
-- 

CREATE TABLE `jeweller_work` (
  `id` int(11) NOT NULL auto_increment,
  `owner` int(11) NOT NULL default '0',
  `name` varchar(100) NOT NULL default '',
  `n_energy` float(10,2) NOT NULL default '0.00',
  `u_energy` float(10,2) NOT NULL default '0.00',
  `bonus` varchar(30) NOT NULL default '',
  `type` char(1) NOT NULL default '',
  KEY `id` (`id`),
  KEY `owner` (`owner`)
) TYPE=MyISAM  AUTO_INCREMENT=17 ;

-- 
-- Zrzut danych tabeli `jeweller_work`
-- 


-- --------------------------------------------------------

-- 
-- Struktura tabeli dla  `lib_comments`
-- 

CREATE TABLE `lib_comments` (
  `id` int(11) NOT NULL auto_increment,
  `textid` int(11) NOT NULL default '0',
  `author` varchar(40) NOT NULL default '',
  `body` text NOT NULL,
  `time` date default NULL,
  KEY `id` (`id`)
) TYPE=MyISAM AUTO_INCREMENT=1 ;

-- 
-- Zrzut danych tabeli `lib_comments`
-- 


-- --------------------------------------------------------

-- 
-- Struktura tabeli dla  `library`
-- 

CREATE TABLE `library` (
  `id` int(11) NOT NULL auto_increment,
  `title` varchar(255) NOT NULL default '',
  `body` text NOT NULL,
  `added` char(1) NOT NULL default 'N',
  `type` varchar(20) NOT NULL default '',
  `lang` varchar(2) NOT NULL default 'pl',
  `author` varchar(50) NOT NULL default '',
  KEY `id` (`id`)
) TYPE=MyISAM AUTO_INCREMENT=1 ;

-- 
-- Zrzut danych tabeli `library`
-- 


-- --------------------------------------------------------

-- 
-- Struktura tabeli dla  `links`
-- 

CREATE TABLE `links` (
  `id` int(11) NOT NULL auto_increment,
  `owner` int(11) NOT NULL default '0',
  `file` varchar(255) NOT NULL default '',
  `text` varchar(100) NOT NULL default '',
  KEY `id` (`id`),
  KEY `owner` (`owner`)
) TYPE=MyISAM AUTO_INCREMENT=1 ;

-- 
-- Zrzut danych tabeli `links`
-- 


-- --------------------------------------------------------

-- 
-- Struktura tabeli dla  `log`
-- 

CREATE TABLE `log` (
  `id` int(11) NOT NULL auto_increment,
  `owner` int(11) NOT NULL default '0',
  `log` text NOT NULL,
  `unread` char(1) NOT NULL default 'F',
  `czas` datetime NOT NULL default '0000-00-00 00:00:00',
  KEY `id` (`id`),
  KEY `owner` (`owner`)
) TYPE=MyISAM AUTO_INCREMENT=1 ;

-- 
-- Zrzut danych tabeli `log`
-- 


-- --------------------------------------------------------

-- 
-- Struktura tabeli dla  `logs`
-- 

CREATE TABLE `logs` (
  `owner` int(11) NOT NULL default '0',
  `log` varchar(255) NOT NULL default '',
  `czas` date NOT NULL default '0000-00-00'
) TYPE=MyISAM;

-- 
-- Zrzut danych tabeli `logs`
-- 


-- --------------------------------------------------------

-- 
-- Struktura tabeli dla  `lost_pass`
-- 

CREATE TABLE `lost_pass` (
  `number` varchar(32) NOT NULL default '',
  `email` varchar(100) NOT NULL default '',
  `newpass` varchar(32) NOT NULL default '',
  `id` int(11) NOT NULL default '0',
  `newemail` varchar(100) NOT NULL default '',
  KEY `number` (`number`),
  KEY `email` (`email`),
  KEY `id` (`id`)
) TYPE=MyISAM;

-- 
-- Zrzut danych tabeli `lost_pass`
-- 


-- --------------------------------------------------------

-- 
-- Struktura tabeli dla  `lumberjack`
-- 

CREATE TABLE `lumberjack` (
  `owner` int(11) NOT NULL default '0',
  `level` tinyint(2) NOT NULL default '0',
  KEY `owner` (`owner`)
) TYPE=MyISAM;

-- 
-- Zrzut danych tabeli `lumberjack`
-- 


-- --------------------------------------------------------

-- 
-- Struktura tabeli dla  `mage_items`
-- 

CREATE TABLE `mage_items` (
  `id` int(11) NOT NULL auto_increment,
  `name` varchar(60) NOT NULL default '',
  `power` int(11) NOT NULL default '0',
  `type` char(1) NOT NULL default 'B',
  `cost` int(11) NOT NULL default '0',
  `minlev` int(2) NOT NULL default '1',
  `lang` varchar(3) NOT NULL default 'pl',
  PRIMARY KEY  (`id`),
  KEY `type` (`type`)
) TYPE=MyISAM  PACK_KEYS=0 AUTO_INCREMENT=13 ;

-- 
-- Zrzut danych tabeli `mage_items`
-- 

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

-- --------------------------------------------------------

-- 
-- Struktura tabeli dla  `mail`
-- 

CREATE TABLE `mail` (
  `id` int(11) NOT NULL auto_increment,
  `sender` varchar(20) NOT NULL default '',
  `senderid` int(11) NOT NULL default '0',
  `owner` int(11) NOT NULL default '0',
  `subject` varchar(50) NOT NULL default '',
  `body` text NOT NULL,
  `unread` char(1) NOT NULL default 'F',
  `zapis` char(1) NOT NULL default 'N',
  `send` int(11) NOT NULL default '0',
  `date` datetime default NULL,
  KEY `id` (`id`),
  KEY `owner` (`owner`),
  KEY `unread` (`unread`),
  KEY `zapis` (`zapis`),
  KEY `send` (`send`)
) TYPE=MyISAM PACK_KEYS=0 AUTO_INCREMENT=1 ;

-- 
-- Zrzut danych tabeli `mail`
-- 


-- --------------------------------------------------------

-- 
-- Struktura tabeli dla  `mill`
-- 

CREATE TABLE `mill` (
  `id` int(11) NOT NULL auto_increment,
  `owner` int(11) NOT NULL default '0',
  `name` varchar(60) NOT NULL default '',
  `type` char(1) NOT NULL default '',
  `cost` int(11) NOT NULL default '0',
  `amount` int(11) NOT NULL default '0',
  `level` tinyint(4) NOT NULL default '0',
  `lang` varchar(2) NOT NULL default 'pl',
  `twohand` char(1) NOT NULL default 'N',
  KEY `id` (`id`),
  KEY `owner` (`owner`)
) TYPE=MyISAM  AUTO_INCREMENT=31 ;

-- 
-- Zrzut danych tabeli `mill`
-- 

INSERT INTO `mill` (`id`, `owner`, `name`, `type`, `cost`, `amount`, `level`, `lang`, `twohand`) VALUES 
(1, 0, 'Łuk ćwiczebny', 'B', 500, 2, 1, 'pl', 'Y'),
(2, 0, 'Łuk giermka', 'B', 2000, 8, 3, 'pl', 'Y'),
(3, 0, 'Łuk krótki', 'B', 4000, 16, 5, 'pl', 'Y'),
(4, 0, 'Łuk myśliwski', 'B', 8000, 40, 10, 'pl', 'Y'),
(5, 0, 'Łuk łowiecki', 'B', 16000, 72, 15, 'pl', 'Y'),
(6, 0, 'Łuk zwiadowcy', 'B', 32000, 120, 20, 'pl', 'Y'),
(7, 0, 'Łuk wojskowy', 'B', 64000, 172, 25, 'pl', 'Y'),
(8, 0, 'Łuk bitewny', 'B', 128000, 240, 30, 'pl', 'Y'),
(9, 0, 'Łuk angularny', 'B', 256000, 400, 40, 'pl', 'Y'),
(10, 0, 'Łuk wojenny', 'B', 512000, 600, 50, 'pl', 'Y'),
(11, 0, 'Łuk podwójny', 'B', 1024000, 840, 60, 'pl', 'Y'),
(12, 0, 'Łuk długi', 'B', 2048000, 1120, 70, 'pl', 'Y'),
(13, 0, 'Łuk bojowy', 'B', 4096000, 1440, 80, 'pl', 'Y'),
(14, 0, 'Łuk refleksyjny', 'B', 8192000, 1800, 90, 'pl', 'Y'),
(15, 0, 'Łuk retrorefleksyjny', 'B', 16384000, 2200, 100, 'pl', 'Y'),
(16, 0, 'Strzały ćwiczebne', 'R', 250, 1, 1, 'pl', 'N'),
(17, 0, 'Strzały turniejowe', 'R', 1000, 4, 3, 'pl', 'N'),
(18, 0, 'Strzały krótkie', 'R', 2000, 8, 5, 'pl', 'N'),
(19, 0, 'Strzały myśliwskie', 'R', 4000, 20, 10, 'pl', 'N'),
(20, 0, 'Strzały łowieckie', 'R', 8000, 36, 15, 'pl', 'N'),
(21, 0, 'Strzały zwiadowcy', 'R', 16000, 60, 20, 'pl', 'N'),
(22, 0, 'Strzały wojskowe', 'R', 32000, 86, 25, 'pl', 'N'),
(23, 0, 'Strzały bitewne', 'R', 64000, 120, 30, 'pl', 'N'),
(24, 0, 'Strzały liściaste', 'R', 128000, 200, 40, 'pl', 'N'),
(25, 0, 'Strzały wojenne', 'R', 256000, 300, 50, 'pl', 'N'),
(26, 0, 'Strzały haczykowe', 'R', 512000, 420, 60, 'pl', 'N'),
(27, 0, 'Strzały długie', 'R', 1024000, 560, 70, 'pl', 'N'),
(28, 0, 'Strzały bojowe', 'R', 2048000, 720, 80, 'pl', 'N'),
(29, 0, 'Strzały wężowe', 'R', 4096000, 900, 90, 'pl', 'N'),
(30, 0, 'Strzały ząbkowane', 'R', 8192000, 1100, 100, 'pl', 'N');

-- --------------------------------------------------------

-- 
-- Struktura tabeli dla  `mill_work`
-- 

CREATE TABLE `mill_work` (
  `id` int(11) NOT NULL auto_increment,
  `owner` int(11) NOT NULL default '0',
  `name` varchar(60) NOT NULL default '',
  `n_energy` smallint(4) NOT NULL default '0',
  `u_energy` smallint(4) NOT NULL default '0',
  `mineral` char(1) NOT NULL default '',
  KEY `id` (`id`)
) TYPE=MyISAM  PACK_KEYS=0 AUTO_INCREMENT=2 ;

-- 
-- Zrzut danych tabeli `mill_work`
-- 


-- --------------------------------------------------------

-- 
-- Struktura tabeli dla  `minerals`
-- 

CREATE TABLE `minerals` (
  `owner` int(11) NOT NULL default '0',
  `copperore` int(11) NOT NULL default '0',
  `zincore` int(11) NOT NULL default '0',
  `tinore` int(11) NOT NULL default '0',
  `ironore` int(11) NOT NULL default '0',
  `coal` int(11) NOT NULL default '0',
  `copper` int(11) NOT NULL default '0',
  `bronze` int(11) NOT NULL default '0',
  `brass` int(11) NOT NULL default '0',
  `iron` int(11) NOT NULL default '0',
  `steel` int(11) NOT NULL default '0',
  `pine` int(11) NOT NULL default '0',
  `hazel` int(11) NOT NULL default '0',
  `yew` int(11) NOT NULL default '0',
  `elm` int(11) NOT NULL default '0',
  `crystal` int(11) NOT NULL default '0',
  `adamantium` int(11) NOT NULL default '0',
  `meteor` int(11) NOT NULL default '0',
  KEY `owner` (`owner`)
) TYPE=MyISAM;

-- 
-- Zrzut danych tabeli `minerals`
-- 


-- --------------------------------------------------------

-- 
-- Struktura tabeli dla  `mines`
-- 

CREATE TABLE `mines` (
  `owner` int(11) NOT NULL default '0',
  `copper` int(11) NOT NULL default '0',
  `zinc` int(11) NOT NULL default '0',
  `tin` int(11) NOT NULL default '0',
  `iron` int(11) NOT NULL default '0',
  `coal` int(11) NOT NULL default '0',
  KEY `owner` (`owner`)
) TYPE=MyISAM;

-- 
-- Zrzut danych tabeli `mines`
-- 


-- --------------------------------------------------------

-- 
-- Struktura tabeli dla  `mines_search`
-- 

CREATE TABLE `mines_search` (
  `player` int(11) NOT NULL default '0',
  `days` tinyint(2) NOT NULL default '0',
  `mineral` varchar(30) NOT NULL default '',
  `searchdays` tinyint(2) NOT NULL default '0',
  KEY `player` (`player`)
) TYPE=MyISAM;

-- 
-- Zrzut danych tabeli `mines_search`
-- 


-- --------------------------------------------------------

-- 
-- Struktura tabeli dla  `monsters`
-- 

CREATE TABLE `monsters` (
  `id` int(11) NOT NULL auto_increment,
  `name` varchar(25) NOT NULL default '',
  `level` int(11) NOT NULL default '0',
  `hp` int(11) NOT NULL default '0',
  `agility` double(11,2) NOT NULL default '0.00',
  `strength` double(11,2) NOT NULL default '0.00',
  `speed` double(11,2) NOT NULL default '0.00',
  `endurance` double(11,2) NOT NULL default '0.00',
  `credits1` int(11) NOT NULL default '0',
  `credits2` int(11) NOT NULL default '0',
  `exp1` int(11) NOT NULL default '0',
  `exp2` int(11) NOT NULL default '0',
  `lang` varchar(3) NOT NULL default 'pl',
  `location` varchar(20) NOT NULL default 'Altara',
  KEY `id` (`id`),
  KEY `level` (`level`),
  KEY `location` (`location`)
) TYPE=MyISAM  PACK_KEYS=0 AUTO_INCREMENT=106 ;

-- 
-- Zrzut danych tabeli `monsters`
-- 

INSERT INTO `monsters` (`id`, `name`, `level`, `hp`, `agility`, `strength`, `speed`, `endurance`, `credits1`, `credits2`, `exp1`, `exp2`, `lang`, `location`) VALUES 
(1, 'Szczur', 1, 10, 3.00, 3.00, 3.00, 3.00, 1, 2, 1, 2, 'pl', 'Altara'),
(2, 'Goblin', 2, 20, 5.00, 5.00, 5.00, 5.00, 2, 4, 3, 5, 'pl', 'Altara'),
(3, 'Ork', 4, 40, 10.00, 10.00, 10.00, 10.00, 4, 6, 6, 10, 'pl', 'Altara'),
(4, 'Szczurołak', 7, 70, 17.00, 24.00, 24.00, 17.00, 6, 10, 10, 15, 'pl', 'Altara'),
(5, 'Gwardzista', 10, 100, 30.00, 30.00, 30.00, 30.00, 10, 15, 15, 20, 'pl', 'Altara'),
(6, 'Troll', 14, 140, 38.00, 52.00, 38.00, 52.00, 15, 25, 20, 25, 'pl', 'Altara'),
(7, 'Ogr', 18, 180, 51.00, 69.00, 21.00, 69.00, 25, 40, 25, 30, 'pl', 'Altara'),
(8, 'Orog', 22, 220, 80.00, 80.00, 80.00, 80.00, 40, 55, 30, 40, 'pl', 'Altara'),
(9, 'Szkielet', 26, 260, 143.00, 117.00, 143.00, 117.00, 55, 80, 40, 50, 'pl', 'Altara'),
(10, 'Gigantyczny Pająk', 30, 390, 165.00, 195.00, 165.00, 195.00, 80, 100, 50, 60, 'pl', 'Altara'),
(11, 'Bezgłowy Rycerz', 35, 420, 233.00, 268.00, 268.00, 233.00, 100, 130, 60, 80, 'pl', 'Altara'),
(12, 'Ghul', 40, 480, 300.00, 340.00, 300.00, 340.00, 130, 160, 80, 100, 'pl', 'Altara'),
(13, 'Nekromanta', 45, 585, 378.00, 423.00, 423.00, 378.00, 160, 200, 100, 125, 'pl', 'Altara'),
(14, 'Wilkołak', 50, 650, 455.00, 505.00, 505.00, 455.00, 200, 250, 125, 150, 'pl', 'Altara'),
(15, 'Wampir', 55, 650, 598.00, 598.00, 543.00, 543.00, 250, 300, 150, 175, 'pl', 'Altara'),
(16, 'Lassaukaur', 60, 720, 690.00, 630.00, 690.00, 630.00, 300, 360, 175, 200, 'pl', 'Altara'),
(17, 'Obserwator', 65, 780, 750.00, 750.00, 750.00, 750.00, 360, 420, 200, 240, 'pl', 'Altara'),
(18, 'Golem', 70, 840, 825.00, 895.00, 825.00, 895.00, 420, 480, 240, 280, 'pl', 'Altara'),
(19, 'Żywiołak Powietrza', 75, 975, 943.00, 1018.00, 1018.00, 943.00, 480, 550, 280, 320, 'pl', 'Altara'),
(20, 'Żywiołak Wody', 80, 1120, 1100.00, 1100.00, 1100.00, 1100.00, 550, 620, 320, 360, 'pl', 'Altara'),
(21, 'Żywiołak Ziemi', 85, 1190, 1243.00, 1158.00, 1243.00, 1158.00, 620, 690, 360, 400, 'pl', 'Altara'),
(22, 'Żywiołak Ognia', 90, 1260, 1255.00, 1345.00, 1345.00, 1255.00, 690, 760, 400, 450, 'pl', 'Altara'),
(23, 'Troll Górski', 95, 1425, 1353.00, 1448.00, 1353.00, 1448.00, 760, 830, 450, 500, 'pl', 'Altara'),
(24, 'Centaur', 100, 1400, 1550.00, 1550.00, 1450.00, 1450.00, 830, 900, 500, 550, 'pl', 'Altara'),
(25, 'Gliniany Golem', 105, 1470, 1548.00, 1653.00, 1548.00, 1653.00, 900, 970, 550, 600, 'pl', 'Altara'),
(26, 'Kamienny Golem', 110, 1540, 1755.00, 1645.00, 1755.00, 1645.00, 970, 1040, 600, 650, 'pl', 'Altara'),
(27, 'Brązowy Golem', 115, 1610, 1858.00, 1743.00, 1858.00, 1743.00, 1040, 1110, 650, 700, 'pl', 'Altara'),
(28, 'Żelazny Golem', 120, 1680, 1960.00, 1960.00, 1840.00, 1840.00, 1110, 1180, 700, 775, 'pl', 'Altara'),
(29, 'Piekielny Pies', 125, 1875, 1938.00, 2063.00, 1938.00, 2063.00, 1180, 1250, 775, 850, 'pl', 'Altara'),
(30, 'Sukkub', 130, 2080, 2035.00, 2165.00, 2165.00, 2035.00, 1250, 1320, 850, 925, 'pl', 'Altara'),
(31, 'Widmowy Pies', 135, 2295, 2268.00, 2133.00, 2268.00, 2133.00, 1320, 1400, 925, 1000, 'pl', 'Altara'),
(32, 'Dżin', 140, 2240, 2300.00, 2300.00, 2300.00, 2300.00, 1400, 1480, 1000, 1100, 'pl', 'Altara'),
(33, 'Smoczy żuk', 145, 2320, 2328.00, 2473.00, 2328.00, 2473.00, 1480, 1560, 1100, 1200, 'pl', 'Altara'),
(34, 'Przeklęty Wojownik', 150, 2550, 2425.00, 2575.00, 2575.00, 2425.00, 1560, 1640, 1200, 1300, 'pl', 'Altara'),
(35, 'Ekkimu', 155, 2635, 2523.00, 2678.00, 2523.00, 2678.00, 1640, 1730, 1300, 1400, 'pl', 'Altara'),
(36, 'Czarny Jednorożec', 160, 2720, 2620.00, 2780.00, 2620.00, 2780.00, 1730, 1820, 1400, 1500, 'pl', 'Altara'),
(37, 'Lich', 165, 2805, 2718.00, 2883.00, 2718.00, 2883.00, 1820, 1910, 1500, 1600, 'pl', 'Altara'),
(38, 'Mroczne Widmo', 170, 2720, 2985.00, 2815.00, 2985.00, 2815.00, 1910, 2000, 1600, 1700, 'pl', 'Altara'),
(39, 'Alaghi', 175, 2975, 2913.00, 3088.00, 2913.00, 3088.00, 2000, 2100, 1700, 1800, 'pl', 'Altara'),
(40, 'Kyton', 180, 3240, 3100.00, 3100.00, 3100.00, 3100.00, 2100, 2200, 1800, 1900, 'pl', 'Altara'),
(41, 'Salamandra', 185, 3330, 3108.00, 3293.00, 3108.00, 3293.00, 2200, 2300, 1900, 2000, 'pl', 'Altara'),
(42, 'Vintaru', 190, 3420, 3350.00, 3350.00, 3350.00, 3350.00, 2300, 2400, 2000, 2100, 'pl', 'Altara'),
(43, 'Hydra', 195, 3510, 3403.00, 3598.00, 3403.00, 3598.00, 2400, 2500, 2100, 2200, 'pl', 'Altara'),
(44, 'Mimik', 200, 3800, 3550.00, 3750.00, 3750.00, 3550.00, 2500, 2600, 2200, 2300, 'pl', 'Altara'),
(45, 'Sfinks', 205, 3895, 3800.00, 3800.00, 3800.00, 3800.00, 2600, 2700, 2300, 2400, 'pl', 'Altara'),
(46, 'Feniks', 210, 3990, 3845.00, 4055.00, 3845.00, 4055.00, 2700, 2800, 2400, 2500, 'pl', 'Altara'),
(47, 'Cyklop', 215, 3870, 3993.00, 4208.00, 4208.00, 3993.00, 2800, 2900, 2500, 2600, 'pl', 'Altara'),
(48, 'Gigant', 220, 4180, 4140.00, 4360.00, 4140.00, 4360.00, 2900, 3000, 2600, 2700, 'pl', 'Altara'),
(49, 'Tytan', 225, 4275, 4400.00, 4400.00, 4400.00, 4400.00, 3000, 3100, 2700, 2800, 'pl', 'Altara'),
(50, 'Jabberwock', 230, 4600, 4600.00, 4600.00, 4600.00, 4600.00, 3100, 3200, 2800, 3000, 'pl', 'Altara'),
(51, 'Dracolich', 235, 4800, 4800.00, 4800.00, 4800.00, 4800.00, 3200, 3300, 3000, 3100, 'pl', 'Altara'),
(52, 'Bagienny Smok', 240, 5000, 5000.00, 5000.00, 5000.00, 5000.00, 3200, 3300, 3200, 3300, 'pl', 'Altara'),
(53, 'Lodowy Smok', 245, 5200, 5200.00, 5200.00, 5200.00, 5200.00, 3300, 3400, 3300, 3400, 'pl', 'Altara'),
(54, 'Widmowy Smok', 250, 5500, 5500.00, 5500.00, 5500.00, 5500.00, 3400, 3500, 3400, 3500, 'pl', 'Altara'),
(55, 'Mroczny Paladyn', 260, 6000, 6000.00, 6000.00, 6000.00, 6000.00, 3500, 3700, 3500, 3700, 'pl', 'Altara'),
(56, 'Szablozębna Łasica', 1, 10, 3.00, 3.00, 3.00, 3.00, 1, 1, 1, 1, 'pl', 'Ardulith'),
(57, 'Płaskostopy Kobold', 2, 20, 5.00, 5.00, 5.00, 5.00, 2, 4, 3, 5, 'pl', 'Ardulith'),
(58, 'Szerokonosy Goblin', 4, 40, 10.00, 10.00, 10.00, 10.00, 4, 6, 6, 10, 'pl', 'Ardulith'),
(59, 'Wysoki Hobogoblin', 7, 70, 20.00, 20.00, 20.00, 20.00, 6, 10, 10, 15, 'pl', 'Ardulith'),
(60, 'Leśny Wąż', 10, 100, 35.00, 35.00, 25.00, 25.00, 10, 15, 15, 20, 'pl', 'Ardulith'),
(61, 'Chaotyczny Chochlik', 14, 140, 52.00, 38.00, 52.00, 38.00, 15, 25, 20, 25, 'pl', 'Ardulith'),
(62, 'Gnoll Łupieżca', 18, 180, 60.00, 60.00, 60.00, 60.00, 25, 40, 25, 30, 'pl', 'Ardulith'),
(63, 'Zielony Ork', 22, 220, 80.00, 80.00, 80.00, 80.00, 40, 55, 30, 40, 'pl', 'Ardulith'),
(64, 'Ostrokły Krenshar', 26, 260, 117.00, 143.00, 143.00, 117.00, 55, 80, 40, 50, 'pl', 'Ardulith'),
(65, 'Baśniowy Pseudosmok', 30, 390, 165.00, 195.00, 165.00, 195.00, 80, 100, 50, 60, 'pl', 'Ardulith'),
(66, 'Nocny Wilk', 35, 420, 233.00, 285.00, 285.00, 233.00, 100, 130, 60, 80, 'pl', 'Ardulith'),
(67, 'Leśny Gepard', 40, 480, 340.00, 300.00, 340.00, 300.00, 130, 160, 80, 100, 'pl', 'Ardulith'),
(68, 'Ziemny Mefit', 45, 585, 378.00, 423.00, 423.00, 378.00, 160, 200, 100, 125, 'pl', 'Ardulith'),
(69, 'Pierścieniowy Lampart', 50, 600, 505.00, 455.00, 505.00, 455.00, 200, 250, 125, 150, 'pl', 'Ardulith'),
(70, 'Czarny Niedźwiedź', 55, 715, 543.00, 598.00, 543.00, 598.00, 250, 300, 150, 175, 'pl', 'Ardulith'),
(71, 'Krótkowłosy Niedźwież', 60, 780, 630.00, 690.00, 630.00, 690.00, 300, 360, 175, 200, 'pl', 'Ardulith'),
(72, 'Ogromny Pająk', 65, 780, 750.00, 750.00, 750.00, 750.00, 360, 420, 200, 240, 'pl', 'Ardulith'),
(73, 'Spaczony Satyr', 70, 840, 860.00, 860.00, 860.00, 860.00, 420, 480, 240, 280, 'pl', 'Ardulith'),
(74, 'Czarny Worg', 75, 900, 943.00, 1018.00, 1018.00, 943.00, 480, 550, 280, 320, 'pl', 'Ardulith'),
(75, 'Złowrogi Allip', 80, 1120, 1100.00, 1100.00, 1100.00, 1100.00, 550, 620, 320, 360, 'pl', 'Ardulith'),
(76, 'Leśny Centaur', 85, 1190, 1243.00, 1243.00, 1158.00, 1158.00, 620, 690, 360, 400, 'pl', 'Ardulith'),
(77, 'Czarnogrzywy Lew', 90, 1260, 1255.00, 1345.00, 1345.00, 1255.00, 690, 760, 400, 450, 'pl', 'Ardulith'),
(78, 'Złoty Jednorożec', 95, 1330, 1400.00, 1400.00, 1400.00, 1400.00, 760, 830, 450, 500, 'pl', 'Ardulith'),
(79, 'Złudna Bestia', 100, 1400, 1500.00, 1500.00, 1500.00, 1500.00, 830, 900, 500, 550, 'pl', 'Ardulith'),
(80, 'Srebny Pegaz', 105, 1470, 1653.00, 1548.00, 1653.00, 1548.00, 900, 970, 550, 600, 'pl', 'Ardulith'),
(81, 'Dwugłowa Aranea', 110, 1540, 1700.00, 1700.00, 1700.00, 1700.00, 970, 1040, 600, 650, 'pl', 'Ardulith'),
(82, 'Pajęczonogi Ettercap', 115, 1610, 1800.00, 1800.00, 1800.00, 1800.00, 1040, 1110, 650, 700, 'pl', 'Ardulith'),
(83, 'Czworopalczasta Meduza', 120, 1680, 1960.00, 1840.00, 1960.00, 1840.00, 1110, 1180, 700, 775, 'pl', 'Ardulith'),
(84, 'Bengalski Tygrys', 125, 1750, 1938.00, 2063.00, 2063.00, 1938.00, 1180, 1250, 850, 925, 'pl', 'Ardulith'),
(85, 'Leśna Wiedźma', 130, 2080, 2100.00, 2100.00, 2100.00, 2100.00, 1250, 1320, 850, 925, 'pl', 'Ardulith'),
(86, 'Białogrzywy Girallon', 135, 2295, 2133.00, 2268.00, 2133.00, 2268.00, 1320, 1400, 925, 1000, 'pl', 'Ardulith'),
(87, 'Skrzydlaty Koszmar', 140, 2240, 2300.00, 2300.00, 2300.00, 2300.00, 1400, 1480, 1000, 1100, 'pl', 'Ardulith'),
(88, 'Wściekły Wilkołak', 145, 2320, 2473.00, 2328.00, 2473.00, 2328.00, 1480, 1560, 1200, 1300, 'pl', 'Ardulith'),
(89, 'Jaskiniowy Niedźwiedź', 150, 2400, 2500.00, 2500.00, 2500.00, 2500.00, 1560, 1640, 1200, 1300, 'pl', 'Ardulith'),
(90, 'Bagienny Troll', 155, 2635, 2523.00, 2678.00, 2523.00, 2678.00, 1640, 1730, 1300, 1400, 'pl', 'Ardulith'),
(91, 'Brunatna Mantikora', 160, 2560, 2780.00, 2620.00, 2780.00, 2620.00, 1730, 1820, 1400, 1500, 'pl', 'Ardulith'),
(92, 'Wynaturzony Yuan-ti', 165, 2640, 2800.00, 2800.00, 2800.00, 2800.00, 1820, 1910, 1500, 1600, 'pl', 'Ardulith'),
(93, 'Ognisty Wywern', 170, 2720, 2985.00, 2815.00, 2985.00, 2815.00, 1900, 2000, 1600, 1700, 'pl', 'Ardulith'),
(94, 'Ziemny Żywiołak', 175, 2975, 2913.00, 3088.00, 2913.00, 3088.00, 2000, 2100, 1700, 1800, 'pl', 'Ardulith'),
(95, 'Złotoskóry Chuul', 180, 3240, 3100.00, 3100.00, 3100.00, 3100.00, 2100, 2200, 1800, 1900, 'pl', 'Ardulith'),
(96, 'Mroczny Mastiff', 185, 3330, 3293.00, 3108.00, 3293.00, 3108.00, 2200, 2300, 1900, 2000, 'pl', 'Ardulith'),
(97, 'Zielony Slaad', 190, 3420, 3350.00, 3350.00, 3350.00, 3350.00, 2300, 2400, 2000, 2100, 'pl', 'Ardulith'),
(98, 'Olbrzymi Drzewiec', 195, 3510, 3403.00, 3598.00, 3403.00, 3598.00, 2400, 2500, 2100, 2200, 'pl', 'Ardulith'),
(99, 'Czarny Lammasu', 200, 3600, 3550.00, 3750.00, 3750.00, 3550.00, 2500, 2600, 2200, 2300, 'pl', 'Ardulith'),
(100, 'Sukkub', 205, 3690, 3903.00, 3698.00, 3903.00, 3698.00, 2600, 2700, 2300, 2400, 'pl', 'Ardulith'),
(101, 'Szary Rozpruwacz', 210, 3990, 3845.00, 4055.00, 3845.00, 4055.00, 2700, 2800, 2400, 2500, 'pl', 'Ardulith'),
(102, 'Tygrysi Beschalor', 215, 3870, 4208.00, 4208.00, 3993.00, 3993.00, 2800, 2900, 2500, 2600, 'pl', 'Ardulith'),
(103, 'Czerwonooki Couatl', 220, 3960, 4250.00, 4250.00, 4250.00, 4250.00, 2900, 3000, 2600, 2700, 'pl', 'Ardulith'),
(104, 'Czworonożna Hydra', 225, 4050, 4400.00, 4400.00, 4400.00, 4400.00, 3000, 3100, 2700, 2800, 'pl', 'Ardulith'),
(105, 'Zielony Smok', 230, 4600, 4600.00, 4600.00, 4600.00, 4600.00, 3100, 3200, 2800, 3200, 'pl', 'Ardulith');

-- --------------------------------------------------------

-- 
-- Struktura tabeli dla  `news`
-- 

CREATE TABLE `news` (
  `id` int(11) NOT NULL auto_increment,
  `starter` text NOT NULL,
  `title` text NOT NULL,
  `news` text NOT NULL,
  `lang` varchar(3) NOT NULL default 'pl',
  `added` char(1) NOT NULL default 'Y',
  `show` char(1) NOT NULL default 'Y',
  UNIQUE KEY `id` (`id`)
) TYPE=MyISAM PACK_KEYS=0 AUTO_INCREMENT=1 ;

-- 
-- Zrzut danych tabeli `news`
-- 


-- --------------------------------------------------------

-- 
-- Struktura tabeli dla  `news_comments`
-- 

CREATE TABLE `news_comments` (
  `id` int(11) NOT NULL auto_increment,
  `newsid` int(11) NOT NULL default '0',
  `author` varchar(40) NOT NULL default '',
  `body` text NOT NULL,
  `time` date default NULL,
  KEY `id` (`id`)
) TYPE=MyISAM AUTO_INCREMENT=1 ;

-- 
-- Zrzut danych tabeli `news_comments`
-- 


-- --------------------------------------------------------

-- 
-- Struktura tabeli dla  `newspaper`
-- 

CREATE TABLE `newspaper` (
  `id` int(11) NOT NULL auto_increment,
  `paper_id` int(11) NOT NULL default '0',
  `title` varchar(255) NOT NULL default '',
  `body` text NOT NULL,
  `lang` varchar(3) NOT NULL default 'pl',
  `added` char(1) NOT NULL default 'N',
  `author` varchar(50) NOT NULL default '',
  `type` char(1) NOT NULL default '',
  KEY `id` (`id`)
) TYPE=MyISAM AUTO_INCREMENT=1 ;

-- 
-- Zrzut danych tabeli `newspaper`
-- 


-- --------------------------------------------------------

-- 
-- Struktura tabeli dla  `newspaper_comments`
-- 

CREATE TABLE `newspaper_comments` (
  `id` int(11) NOT NULL auto_increment,
  `textid` int(11) NOT NULL default '0',
  `author` varchar(40) NOT NULL default '',
  `body` text NOT NULL,
  `time` date default NULL,
  KEY `id` (`id`)
) TYPE=MyISAM PACK_KEYS=0 AUTO_INCREMENT=1 ;

-- 
-- Zrzut danych tabeli `newspaper_comments`
-- 


-- --------------------------------------------------------

-- 
-- Struktura tabeli dla  `notatnik`
-- 

CREATE TABLE `notatnik` (
  `id` int(11) NOT NULL auto_increment,
  `gracz` int(11) NOT NULL default '0',
  `tekst` text NOT NULL,
  `czas` datetime NOT NULL default '0000-00-00 00:00:00',
  KEY `id` (`id`)
) TYPE=MyISAM PACK_KEYS=0 AUTO_INCREMENT=1 ;

-- 
-- Zrzut danych tabeli `notatnik`
-- 


-- --------------------------------------------------------

-- 
-- Struktura tabeli dla  `outpost_monsters`
-- 

CREATE TABLE `outpost_monsters` (
  `id` int(11) NOT NULL auto_increment,
  `outpost` int(11) NOT NULL default '0',
  `name` varchar(50) NOT NULL default '',
  `power` int(11) NOT NULL default '0',
  `defense` int(11) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `id` (`id`),
  KEY `outpost` (`outpost`)
) TYPE=MyISAM AUTO_INCREMENT=1 ;

-- 
-- Zrzut danych tabeli `outpost_monsters`
-- 


-- --------------------------------------------------------

-- 
-- Struktura tabeli dla  `outpost_veterans`
-- 

CREATE TABLE `outpost_veterans` (
  `id` int(11) NOT NULL auto_increment,
  `outpost` int(11) NOT NULL default '0',
  `name` varchar(20) NOT NULL default '',
  `weapon` varchar(60) default NULL,
  `wpower` int(5) NOT NULL default '0',
  `armor` varchar(60) default NULL,
  `apower` int(5) NOT NULL default '0',
  `helm` varchar(60) default NULL,
  `hpower` int(5) NOT NULL default '0',
  `legs` varchar(60) default NULL,
  `lpower` int(5) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `outpost` (`outpost`)
) TYPE=MyISAM  AUTO_INCREMENT=3 ;

-- 
-- Zrzut danych tabeli `outpost_veterans`
-- 


-- --------------------------------------------------------

-- 
-- Struktura tabeli dla  `outposts`
-- 

CREATE TABLE `outposts` (
  `id` int(11) NOT NULL auto_increment,
  `owner` int(11) NOT NULL default '0',
  `size` int(11) NOT NULL default '1',
  `warriors` int(11) NOT NULL default '0',
  `archers` int(11) NOT NULL default '0',
  `catapults` int(11) NOT NULL default '0',
  `barricades` int(11) NOT NULL default '0',
  `gold` int(11) NOT NULL default '500',
  `turns` int(11) NOT NULL default '3',
  `battack` tinyint(2) NOT NULL default '0',
  `bdefense` tinyint(2) NOT NULL default '0',
  `btax` tinyint(2) NOT NULL default '0',
  `blost` tinyint(2) NOT NULL default '0',
  `bcost` tinyint(2) NOT NULL default '0',
  `fence` int(11) NOT NULL default '0',
  `barracks` int(11) NOT NULL default '0',
  `fatigue` int(3) NOT NULL default '100',
  `morale` double(11,1) NOT NULL default '0.0',
  `attacks` tinyint(2) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `id` (`id`)
) TYPE=MyISAM AUTO_INCREMENT=1 ;

-- 
-- Zrzut danych tabeli `outposts`
-- 


-- --------------------------------------------------------

-- 
-- Struktura tabeli dla  `players`
-- 

CREATE TABLE `players` (
  `id` int(11) NOT NULL auto_increment,
  `user` varchar(20) NOT NULL default '',
  `email` varchar(60) NOT NULL default '',
  `pass` varchar(32) NOT NULL default '',
  `rank` varchar(20) NOT NULL default 'Member',
  `level` int(11) NOT NULL default '1',
  `exp` int(11) NOT NULL default '0',
  `credits` int(11) NOT NULL default '1000',
  `energy` double(11,2) NOT NULL default '10.00',
  `max_energy` double(11,2) NOT NULL default '10.00',
  `strength` double(11,3) NOT NULL default '3.000',
  `agility` double(11,3) NOT NULL default '3.000',
  `ap` int(11) NOT NULL default '5',
  `wins` int(11) NOT NULL default '0',
  `losses` int(11) NOT NULL default '0',
  `lastkilled` varchar(60) NOT NULL default '...',
  `lastkilledby` varchar(60) NOT NULL default '...',
  `platinum` int(11) NOT NULL default '0',
  `age` int(11) NOT NULL default '1',
  `logins` int(11) NOT NULL default '0',
  `hp` int(11) unsigned NOT NULL default '15',
  `max_hp` int(11) NOT NULL default '15',
  `bank` int(11) NOT NULL default '0',
  `lpv` bigint(20) NOT NULL default '0',
  `page` varchar(100) NOT NULL default '',
  `ip` varchar(50) NOT NULL default '',
  `ability` double(11,2) NOT NULL default '0.01',
  `tribe` int(11) NOT NULL default '0',
  `profile` text NOT NULL,
  `refs` int(11) NOT NULL default '0',
  `corepass` char(1) NOT NULL default 'N',
  `fight` int(11) NOT NULL default '0',
  `trains` int(11) NOT NULL default '5',
  `rasa` varchar(20) NOT NULL default '',
  `klasa` varchar(20) NOT NULL default '',
  `inteli` double(11,3) NOT NULL default '3.000',
  `pw` int(11) NOT NULL default '0',
  `atak` double(11,2) NOT NULL default '0.01',
  `unik` double(11,2) NOT NULL default '0.01',
  `magia` double(11,2) NOT NULL default '0.01',
  `immu` char(1) NOT NULL default 'N',
  `pm` int(11) NOT NULL default '3',
  `miejsce` varchar(15) NOT NULL default 'Altara',
  `szyb` double(11,3) NOT NULL default '3.000',
  `wytrz` double(11,3) NOT NULL default '3.000',
  `alchemia` double(11,2) NOT NULL default '0.01',
  `gg` varchar(255) NOT NULL default '0',
  `avatar` varchar(36) NOT NULL default '',
  `wisdom` double(11,3) NOT NULL default '3.000',
  `shoot` double(11,2) NOT NULL default '0.01',
  `tribe_rank` varchar(60) NOT NULL default '',
  `fletcher` double(11,2) NOT NULL default '0.01',
  `deity` varchar(20) default NULL,
  `maps` int(2) NOT NULL default '0',
  `rest` char(1) NOT NULL default 'N',
  `crime` int(11) NOT NULL default '1',
  `gender` char(1) default NULL,
  `bridge` char(1) NOT NULL default 'N',
  `temp` int(11) NOT NULL default '0',
  `style` varchar(100) NOT NULL default 'light.css',
  `leadership` double(11,2) NOT NULL default '0.01',
  `graphic` varchar(255) default NULL,
  `lang` varchar(3) NOT NULL default 'pl',
  `seclang` varchar(3) default NULL,
  `forum_time` bigint(20) NOT NULL default '0',
  `tforum_time` bigint(20) NOT NULL default '0',
  `bless` varchar(30) NOT NULL default '',
  `blessval` int(11) NOT NULL default '0',
  `antidote` char(2) default NULL,
  `freeze` tinyint(3) NOT NULL default '0',
  `breeding` double(11,2) NOT NULL default '0.01',
  `battlelog` char(1) NOT NULL default 'N',
  `houserest` char(1) NOT NULL default 'N',
  `poll` char(1) NOT NULL default 'N',
  `mining` double(11,2) NOT NULL default '0.01',
  `lumberjack` double(11,2) NOT NULL default '0.01',
  `herbalist` double(11,2) NOT NULL default '0.01',
  `astralcrime` char(1) NOT NULL default 'Y',
  `changedeity` int(11) NOT NULL default '0',
  `jeweller` double(11,2) NOT NULL default '0.01',
  `graphbar` char(1) NOT NULL default 'N',
  KEY `user` (`user`),
  KEY `email` (`email`),
  KEY `lpv` (`lpv`),
  KEY `page` (`page`),
  KEY `refs` (`refs`),
  KEY `id` (`id`)
) TYPE=MyISAM PACK_KEYS=0 AUTO_INCREMENT=1 ;

-- 
-- Zrzut danych tabeli `players`
-- 


-- --------------------------------------------------------

-- 
-- Struktura tabeli dla  `pmarket`
-- 

CREATE TABLE `pmarket` (
  `id` int(11) NOT NULL auto_increment,
  `seller` int(11) NOT NULL default '0',
  `ilosc` int(11) NOT NULL default '0',
  `cost` int(11) unsigned NOT NULL default '0',
  `nazwa` varchar(20) NOT NULL default 'mithril',
  `lang` varchar(3) NOT NULL default 'pl',
  UNIQUE KEY `id` (`id`)
) TYPE=MyISAM PACK_KEYS=0 AUTO_INCREMENT=1 ;

-- 
-- Zrzut danych tabeli `pmarket`
-- 


-- --------------------------------------------------------

-- 
-- Struktura tabeli dla  `polls`
-- 

CREATE TABLE `polls` (
  `id` int(11) NOT NULL default '0',
  `poll` varchar(255) NOT NULL default '',
  `votes` int(11) NOT NULL default '0',
  `lang` varchar(2) NOT NULL default 'pl',
  `days` smallint(3) NOT NULL default '7',
  `members` int(11) NOT NULL default '0',
  KEY `id` (`id`)
) TYPE=MyISAM;

-- 
-- Zrzut danych tabeli `polls`
-- 


-- --------------------------------------------------------

-- 
-- Struktura tabeli dla  `polls_comments`
-- 

CREATE TABLE `polls_comments` (
  `id` int(11) NOT NULL auto_increment,
  `pollid` int(11) NOT NULL default '0',
  `author` varchar(40) NOT NULL default '',
  `body` text NOT NULL,
  `time` date default NULL,
  KEY `id` (`id`)
) TYPE=MyISAM AUTO_INCREMENT=1 ;

-- 
-- Zrzut danych tabeli `polls_comments`
-- 


-- --------------------------------------------------------

-- 
-- Struktura tabeli dla  `potions`
-- 

CREATE TABLE `potions` (
  `id` int(11) NOT NULL auto_increment,
  `owner` int(11) NOT NULL default '0',
  `name` varchar(80) NOT NULL default '',
  `type` char(1) NOT NULL default '',
  `efect` varchar(30) NOT NULL default '',
  `status` char(1) NOT NULL default 'S',
  `power` int(3) NOT NULL default '100',
  `amount` int(11) NOT NULL default '0',
  `lang` varchar(3) NOT NULL default 'pl',
  `cost` int(11) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`)
) TYPE=MyISAM  PACK_KEYS=0 AUTO_INCREMENT=29 ;

-- 
-- Zrzut danych tabeli `potions`
-- 

INSERT INTO `potions` (`id`, `owner`, `name`, `type`, `efect`, `status`, `power`, `amount`, `lang`, `cost`) VALUES 
(1, 0, 'bardzo silna mikstura z Nutari', 'M', 'regeneruje manę', 'S', 500, 5, 'pl', 0),
(2, 0, 'słaba mikstura z Nutari', 'M', 'regeneruje manę', 'S', 40, 44, 'pl', 0),
(3, 0, 'mikstura z Nutari', 'M', 'regeneruje manę', 'S', 100, 14, 'pl', 0),
(4, 0, 'bardzo słaba mikstura z Nutari', 'M', 'regeneruje manę', 'S', 20, 31, 'pl', 0),
(5, 0, 'silna mikstura z Nutari', 'M', 'regeneruje manę', 'S', 200, 37, 'pl', 0),
(6, 0, 'bardzo słaba mikstura z Illani', 'H', 'regeneruje życie', 'S', 10, 50, 'pl', 0),
(7, 0, 'słaba mikstura z Illani', 'H', 'regeneruje życie', 'S', 20, 18, 'pl', 0),
(8, 0, 'mikstura z Illani', 'H', 'regeneruje życie', 'S', 50, 16, 'pl', 0),
(9, 0, 'silna mikstura z Illani', 'H', 'regeneruje życie', 'S', 100, 34, 'pl', 0),
(10, 0, 'bardzo silna mikstura z Illani', 'H', 'regeneruje życie', 'S', 200, 31, 'pl', 0),
(11, 0, 'bardzo słaba trucizna z Dynallca', 'P', 'premia do obrażeń(broń)', 'A', 1, 33, 'pl', 0),
(12, 0, 'słaba trucizna z Dynallca', 'P', 'premia do obrażeń(broń)', 'A', 10, 38, 'pl', 0),
(13, 0, 'trucizna z Dynallca', 'P', 'premia do obrażeń (broń)', 'A', 20, 26, 'pl', 0),
(14, 0, 'silna trucizna z Dynallca', 'P', 'premia do obrażeń (broń)', 'A', 30, 32, 'pl', 0),
(15, 0, 'bardzo silna trucizna z Dynallca', 'P', 'premia do obrażeń(broń)', 'A', 50, 6, 'pl', 0),
(16, 0, 'bardzo słaba trucizna z Illani', 'P', 'specjalna premia do obrażeń', 'A', 5, 15, 'pl', 0),
(17, 0, 'słaba trucizna z Illani', 'P', 'specjalna premia do obrażeń', 'A', 15, 44, 'pl', 0),
(18, 0, 'trucizna z Illani', 'P', 'specjalna premia do obrażeń', 'A', 25, 33, 'pl', 0),
(19, 0, 'silna trucizna z Illani', 'P', 'specjalna premia do obrażeń', 'A', 40, 41, 'pl', 0),
(20, 0, 'bardzo silna trucizna z Illani', 'P', 'specjalna premia do obrażeń', 'A', 60, 2, 'pl', 0),
(21, 0, 'bardzo słaba trucizna z Nutari', 'P', 'zmniejsza PM przeciwnika', 'A', 5, 7, 'pl', 0),
(22, 0, 'słaba trucizna z Nutari', 'P', 'zmniejsza PM przeciwnika', 'A', 15, 24, 'pl', 0),
(23, 0, 'trucizna z Nutari', 'P', 'zmniejsza PM przeciwnika', 'A', 25, 47, 'pl', 0),
(24, 0, 'silna trucizna z Nutari', 'P', 'zmniejsza PM przeciwnika', 'A', 40, 13, 'pl', 0),
(25, 0, 'bardzo silna trucizna z Nutari', 'P', 'zmniejsza PM przeciwnika', 'A', 60, 16, 'pl', 0),
(26, 0, 'antidotum na truciznę z Dynallca', 'A', 'likwiduje zatrucie Dynallca', 'A', 100, 31, 'pl', 0),
(27, 0, 'antidotum na truciznę z Nutari', 'A', 'likwiduje zatrucie Nutari', 'A', 100, 17, 'pl', 0),
(28, 0, 'antidotum na truciznę z Illani', 'A', 'likwiduje zatrucie Illani', 'A', 100, 17, 'pl', 0);

-- --------------------------------------------------------

-- 
-- Struktura tabeli dla  `questaction`
-- 

CREATE TABLE `questaction` (
  `id` int(11) NOT NULL auto_increment,
  `player` int(11) NOT NULL default '0',
  `quest` int(11) NOT NULL default '0',
  `action` varchar(20) NOT NULL default '',
  KEY `id` (`id`),
  KEY `player` (`player`)
) TYPE=MyISAM AUTO_INCREMENT=1 ;

-- 
-- Zrzut danych tabeli `questaction`
-- 


-- --------------------------------------------------------

-- 
-- Struktura tabeli dla  `quests`
-- 

CREATE TABLE `quests` (
  `id` int(11) NOT NULL auto_increment,
  `qid` int(11) NOT NULL default '2',
  `location` varchar(20) NOT NULL default 'grid.php',
  `name` varchar(20) NOT NULL default '',
  `option` varchar(20) NOT NULL default '0',
  `text` text NOT NULL,
  `lang` varchar(3) NOT NULL default 'pl',
  KEY `id` (`id`),
  KEY `qid` (`qid`),
  KEY `location` (`location`),
  KEY `name` (`name`)
) TYPE=MyISAM  PACK_KEYS=0 AUTO_INCREMENT=664 ;

-- 
-- Zrzut danych tabeli `quests`
-- 

INSERT INTO `quests` (`id`, `qid`, `location`, `name`, `option`, `text`, `lang`) VALUES 
(1, 1, 'grid.php', 'start', '0', 'Wędrując korytarzami labiryntu w pewnym momencie dostrzegasz przejście prawie całkowicie zasłonięte pajęczynami. Wygląda to tak, jakby nikt nie wędrował tą drogą od setek lat. Zdecydowanym ruchem zrywasz pajęczyny. Twoim oczom ukazuje się długi korytarz prowadzący lekko w dół. Podłogę pokrywa gruba warstwa kurzu, która zmienia się w obłok przy każdym twoim kroku. Co jakiś czas korytarz skręca to w lewo to w prawo, jednak ciągle prowadzi lekko w dół. Po pewnym okresie wędrówki docierasz do rozwidlenia korytarza w kształcie litery T. Którą drogę wybierasz?', 'pl'),
(2, 1, 'grid.php', 'box1', '1', 'Korytarz w prawo', 'pl'),
(3, 1, 'grid.php', 'box1', '2', 'Korytarz w lewo', 'pl'),
(4, 1, 'grid.php', 'box1', '3', 'Zawracam', 'pl'),
(5, 1, 'grid.php', '3', '0', 'Postanowiłeś zostawić zagadkę tego korytarza nie rozwiązaną. Wróciłeś tą samą drogą do wyjścia z labiryntu.', 'pl'),
(6, 1, 'grid.php', '1', '0', 'Korytarz jest nieco mniejszy niż ten, którym wędrowałeś wcześniej. Jednak podobnie jak poprzedni pokryty jest grubą warstwą kurzu. Wędrując przed siebie dostrzegasz w pewnym momencie dziwne zarysowania na ścianach, jakby coś dużego poruszało się tym korytarzem. Twoja czujność wzrasta. Wędrując dalej, poczułeś że coś leży tuż przy twojej stopie. Delikatnie przeszukujesz podłogę, ksztusząc się od pyłu. W pewnym momencie twoja ręka trafia na jakiś przedmiot. Okazuje się że to kość! Rozglądając się uważnie po okolicy znajdujesz jeszcze parę kości oraz fragmenty tkaniny i resztki zardzewiałej broni, które rozsypują się pod wpływem dotyku. Kiedy tak stoisz i zastanawiasz się nad znaleziskiem, nagle z oddali słyszysz dziwne, metaliczne skrzypienie. Co robisz?', 'pl'),
(7, 1, 'grid.php', '2', '0', 'Postanowiłeś skręcić w lewo. Przez jakiś czas korytarz prowadził poziomo ale po pewnym czasie zaczął powoli się wznosić. Podobnie jak i wcześniejsza droga i ten pokryty jest grubą warstwą kurzu świadczącą iż nikt nie wędrował nim od wieków. Po pewnym czasie zauważasz na ścianach resztki jakiś malowideł startych obecnie przez czas. Gdzie niegdzie widać jeszcze sylwetki jakiś istot lecz nic więcej nie jesteś w stanie zobaczyć. Panuje tutaj martwa cisza, masz wrażenie że odgłos twoich kroków niesie się na wiele mil wokół ciebie. Wędrując ostrożnie prostym korytarzem nagle od prawej ściany usłyszałeś świst. Nie namyślając się ani chwili, natychmiast skoczyłeś przed siebie.', 'pl'),
(8, 1, 'grid.php', 'box2', '1', 'Szybko wycofujesz się z korytarza', 'pl'),
(9, 1, 'grid.php', 'box2', '2', 'Przygotowując się na najgorsze powoli zmierzasz w kierunku dziwnych odgłosów', 'pl'),
(10, 1, 'grid.php', '1.1', '0', 'Ponieważ nie wiesz co wydaje taki odgłos, postanowiłeś wycofać się i porzucić ten dziwny korytarz. Mimo to, za to że odkryłeś nieznane przejście dostajesz punkty doświadczenia.', 'pl'),
(11, 1, 'grid.php', '1.2', '0', 'Powoli zmierzasz w kierunku owych dziwnych odgłosów. Co jakiś czas napotykasz na szczeliny w korytarzu prowadzące na boki. Całość wygląda bardziej na naturalny korytarz skalny niż dzieło rąk jakiś istot rozumnych. Nagle od przodu uderza cię w nozdrza ostry zapach padliny. Z lekko łzawiącymi oczami posuwasz się naprzód. W pewnym momencie ogarnia ciebie tak nieprzenikniona ciemość iż wydaje się, że nawet światło pochodni nie jest w stanie jej rozjaśnić. W tym momencie słyszysz że skrzypienie ustało. Wiedziony ciekawością idziesz nadal przed siebie. Wtem na twej drodze niczym biała zasłona, pojawia się potężna, zagradzająca cały korytarz pajęczyna. Skonsternowany tym znaleziskiem rozglądasz się wokół. Kiedy w końcu postanawiasz przeciąć pajęczynę, nagle z lewej strony ponownie słyszysz metaliczny skrzypot. Lecz tym razem dobiega on z bardzo bliskiej odległości. Odwracasz się w tę stronę akurat na czas, aby dostrzec szarżującego na ciebie olbrzymiego pająka!', 'pl'),
(12, 1, 'grid.php', 'lostfight1', '0', 'Próbowałeś stawić opór ale stwór był silniejszy. Nagle zobaczyłeś nad swoją głową potężne kły z których ściekała oślizgła zielonkawa maź. Potem poczułeś potworny ból w klatce piersiowej i nastała ciemność. Budzisz się dopiero w szpitalu w city1a. Nigdy nie zapomnisz tego co widziałeś.', 'pl'),
(13, 1, 'grid.php', 'winfight1', '0', 'Po twym ostatnim ciosie potwór z hukiem padł na podłogę wzbijając tumany kurzu. Przez chwilę odpoczywałeś po walce, potem postanowiłeś podjąć ponownie wędrówkę. Ruszyłeś korytarzem z którego wyszła bestia. W świetle pochodni widzisziż jest to naturalny korytarz skalny a nie wytwór jakiejś cywilizacji. Od czasu do czasu napotykasz na resztki potężnych pajęczyn zwisających ze ścian, czasami pod ścianami widzisz stare szkielety dawnych śmiałków, którzy wędrowali tą drogą lecz mieli mniej szczęścia od ciebie. W pewnym momencie bardziej czujesz niż widzisz, że znalazłeś się w wielkiej podziemnej pieczarze. Ostrożnie poruszając się wzdłuż ścian badasz pomieszczenie.', 'pl'),
(14, 1, 'grid.php', 'int1', '0', 'Zabrałeś się za przeszukiwanie pomieszczenia jednak jedyne co znalazłeś to 100 sztuk złota rozrzucone wśród ofiar pająka', 'pl'),
(15, 1, 'grid.php', 'int2', '0', 'W północnej ścianie twoją uwagę zwróciło kilka kamieni ułożonych obok siebie. Zdziwiło cię to bardzo, ponieważ twoim zdaniem nie pasują one zupełnie do tego miejsca. Powoli rozrzucasz kamienie i twoim oczom ukazuje się nisza skalna a w niej sporych rozmiarów pakunek. Delikatnie wyciągasz znalezisko i rozwijasz stare, rozpadające się płótno. Twoim oczom ukazuje się doskonale zakonserwowany Smoczy miecz stalowy. Podekscytowany postanowiłeś przeszukać jeszcze resztę pomieszczenia. Jednak jedyne co znalazłeś to 100 sztuk złota rozrzucone między kośćmi ofiar.', 'pl'),
(16, 1, 'grid.php', 'end1', '0', 'Powoli wróciłeś tą samą drogą do rozstaju korytarzy, a następne z powrotem na powierzchnię. Kiedy wyszedłeś z labiryntu oślepił cię blask słońca a do twych uszu dobiegł gwar głosów mieszkańców city1b. Dopiero teraz zauważyłeś że jesteś cały pokrwawiony i pokryty kurzem oraz pajęczynami. Chyba przydałoby się nieco oczyścić.', 'pl'),
(17, 1, 'grid.php', 'speed1', '0', 'Nagle poczułeś w prawym boku delikatne ukłucie igieł. Po chwili zaczęło ci się kręcić w głowie tak, że musiałeś oprzeć się o ścianę. Czujesz jak powoli uciekają ci siły. Jednak czy to twoja naturalna odporność czy też fakt iż trucizna, którą pokryte były igły zestarzała się, po pewnym czasie sensacje mijają.', 'pl'),
(18, 1, 'grid.php', 'speed2', '0', 'Szybkim ruchem udało ci się uskoczyć do przodu. Za sobą usłyszałeś tylko delikatny dźwięk drobnych metalowych przedmiotów uderzających o ścianę. Przez chwilę odpoczywałeś po tym nagłym zdarzeniu.', 'pl'),
(19, 1, 'grid.php', '2next', '0', 'Ponownie ruszasz przed siebie korytarzem. Po jakimś czasie dostrzegasz na ścianach dziwne płaskorzeźby przedstawiające nieznane ci zupełnie stwory i istoty. Idąc dalej tą drogą w pewnym momencie raczej wyczuwasz niż zauważasz że zmierzasz w kierunku dużego pomieszczenia. Kiedy wchodzisz do niego, nagle całe rozświetla się jakby właśnie nastał dzień. Kiedy ochłonąłeś nieco z wrażenia, zacząłeś rozglądać się po okolicy. Pomieszczenie ma kształt kwadratu o wymiarach około 20 kroków długości, 20 szerokości oraz 10 wysokości. Wtopione w ścianę pod sufitem kryształy sprawiają iż światło twojej pochodni rozświetla tak jasno całe pomieszczenie. Jednak twoją uwagę przykuwa przede wszystkim coś innego. Po przeciwnej ścianie widzisz zamknięte, bogato zdobione drzwi rozmiarami przewyższające nieco przeciętnego człowieka. Po obu stronach drzwi w zagłębieniach w ścianie stoją dwa kamienne posągi dziwnych istot. Natomiast po swojej lewej stronie na ścianie znajdują się jakieś dziwne, ułożone w rządek niewielkie płytki. Co postanawiasz?', 'pl'),
(20, 1, 'grid.php', 'box3', '1', 'Podejść do drzwi i spróbować je otworzyć', 'pl'),
(21, 1, 'grid.php', 'box3', '2', 'Zbadać dokładniej zagadkowe płytki na ścianie', 'pl'),
(22, 1, 'grid.php', 'box3', '3', 'Wycofać się', 'pl'),
(23, 1, 'grid.php', '2.3', '0', 'Zdecydowałeś się nie podejmować wyzwania. Poprzednie wydarzenia wystarczą ci jak na jeden dzień. Wracasz z powrotem znanym sobie korytarzem, zachowując szczególną ostrożność w miejscu gdzie wcześniej natknąłeś się na pułapkę. Jednak tym razem udaje ci się przejść bez problemy. Po pewnym czasie wracasz do znanej ci już części labiryntu a następnie do miasta.', 'pl'),
(24, 1, 'grid.php', '2.2', '11', 'Kiedy podchodzisz do ściany widzisz kilka kafelków z naniesionymi na nich liczbami 1,2,3,5,7 ustawione w jednym szeregu. na ostatnim znajdują się dwa bębny na których umieszczone są pojedyncze cyfry od 0 do 9 oraz niewielki przycisk. Domyślasz się że trzeba ustawić jakąś konkretną liczbę i na dodatek musi ci się to udać za pierwszym razem.', 'pl'),
(25, 1, 'grid.php', 'answer1', '0', 'Kiedy wcisnąłeś przycisk, zobaczyłeś jak cały rząd łączy się w jeden element. Po chwili usłyszałeś za sobą zgrzyt. Odwracając się w tamtą stronę zauważyłeś iż otworzyły się tajemnicze drzwi.', 'pl'),
(26, 1, 'grid.php', 'answer2', '0', 'Zadowolony że udało ci się podać prawidłową odpowiedź naciskasz przycisk. Zauważasz że płytki połączyły się w jedną całość a za plecami usłyszałeś zgrzyt. Odwracając się w tamtą stronę zobaczyłeś iż otworzyły się drzwi. Jednak nie to przykuło twoją uwagę, gdyż w tym samym momencie rzeźby stojące po obu stronach drzwi otworzyły oczy i ruszyły w twoją stronę. To golemy! Błyskawicznie przygotowujesz się do walki.', 'pl'),
(27, 1, 'grid.php', 'lostfight2', '0', 'Przyparty do muru broniłeś się desperacko ale niestety twój opór nie na wiele się zdał. Czujesz jak powoli opuszczają cię siły. W pewnym momencie świat zawirował wokół ciebie i powoli zacząłeś tracić ostrość widzenia by po chwili zapaść w absolutną ciemność. To co przeżyłeś już na zawsze zostanie w twej pamięci.', 'pl'),
(28, 1, 'grid.php', 'winfight2', '0', 'Jeszcze tylko jeden twój atak i drugi golem rozpadł się na kawałki. Zdyszany i szczęśliwy stoisz pośród rumowiska. Po pewnym czasie zbierasz ekwipunek i wyruszasz na zbadanie tego co kryją za sobą drzwi.', 'pl'),
(29, 1, 'grid.php', '2.1', '0', 'Podchodzisz do drzwi i z zainteresowaniem przyglądasz się wzorom pokrywającym je. Po pewnym czasie domyślasz się, że gdzieś w tej plątaninie ukryty jest jakiś przycisk pozwalający otworzyć drzwi. Po chwili postanawiasz spróbować.', 'pl'),
(30, 1, 'grid.php', 'inteli3', '0', 'Przyglądając się uważnie, dostrzegasz że jedna z linii odstaje nieco od wzorów. Na jej końcu znajduje się niewielki trójkąt. Kiedy dotykasz tego miejsca, figura lekko zagłębia się w drzwiach a ty słyszył cichy trzask. Po chwili drzwi otwierają się.', 'pl'),
(31, 1, 'grid.php', 'inteli4', '0', 'Zaintrygował cię symbol diamentu na samym środku drzwi. Będąc absolutnie przekonany iż to jest właśnie klucz do nich bez wachania naciskasz go. Rzeczywiście drzwi otworzyły się. Jednak kątem oka zauważasz ruch po bokach. Spoglądając na lewo i prawo z przerażeniem dostrzegasz, że kamienne posągi zaczynają poruszać się w twoją stronę. To golemy! Szybko przygotowujesz się do walki.', 'pl'),
(32, 1, 'grid.php', 'door', '0', 'Ostrożnie przechodzisz przez drzwi sprawdzając czy nie ma gdzieś czyhających na ciebie pułapek. Na szczęście nic ci nie zagraża. Widzisz za to w półmroku niewielką komnatę. Ściany pokryte są starożytnymi rysunkami przedstawiającymi różne wymarłe już istoty oraz bestie. Mimo iż wszędzie do tej pory na podłodze spoczywała warstwa kurzu w tym pomieszczeniu nie ma go ani trochę, tak jakby ktoś opiekował się tym miejscem. W jednym z rogów komnaty widzisz niewielki, podłużny przedmiot leżący na ziemi. Kiedy podchodzisz bliżej, stwierdzasz że jest to najdziwniejsza skrzynia jaką widziałeś. Wygląda jak wykonana z jedengo kawałka bardzo ciemnego drewna. Nigdzie nie widzisz nawet śladów zamka czy zawiasów. Jedynie na wieku dostrzegasz jakieś dziwne zadrapania. Kiedy delikatnie dotykasz tego miejsca, nagle zadrapania zaczynają świecić by po chwili w magiczny sposób ułożyć się w zrozumiały dla ciebie napis. Czy chcesz próbować rozwiązać zagadkę?', 'pl'),
(33, 1, 'grid.php', 'box4', '1', 'Tak', 'pl'),
(34, 1, 'grid.php', 'box4', '2', 'Nie', 'pl'),
(35, 1, 'grid.php', 'door2', '0', 'Postanawiasz zostawić dziwną skrzynię w spokoju. Wychodzisz więc z pomieszczenia i wracasz korytarzem po swoich śladach omijając miejsce gdzie wcześniej natknąłeś się na pułapkę. Po jakimś czasie korytarz ponownie zaczyna prowadzić pod górę a ty powoli zbliżasz się do wyjścia z labiryntu. Kiedy opuszczasz katakumby w twoje oczy uderza blask słońca a do uszu dociera gwar rozmów. Potwornie zmęczony udajesz się w kierunku karczmy aby odpocząć co nieco jednak w twej pamięci ciągle tkwi obraz owej dziwnej skrzyni.', 'pl'),
(36, 1, 'grid.php', 'door1', 'pisarz', 'Pochylając się nad skrzynią dostrzegasz wyraźnie napis na niej. Głosi on:<br />\r\n<i>Nie jestem kobietą, a przecież<br />\r\nRodzę niewidzialne dzieci.<br />\r\nMoje córki muszą być piękne,<br />\r\nInaczej giną z mej ręki.<br />\r\nMoi synowi żyją wiecznie.<br />\r\nA teraz powiedz kim jestem?<br /></i>\r\nDomyślasz się, że aby otworzyć skrzynię, musisz odpowiedzieć na zagadkę. Obok napisu dostrzegasz jeszcze 5 niewielkich zadrapań.', 'pl'),
(37, 1, 'grid.php', 'answer3', '0', 'Kiedy wypowiadałeś odpowiedź, zauważyłeś że jedna kreska zniknęła a mimo to skrzynia ani drgnęła. Więc to chyba była zła odpowiedź. Zaczynasz zastanawiać się nad kolejną.', 'pl'),
(38, 1, 'grid.php', 'end2', '0', 'Kiedy wypowiedziałeś po raz piąty słowo będące twoim zdaniem rozwiązaniem tej zagadki, ostatnia kreska zniknęła, napis pojaśniał by po chwili ponownie przemienić się w zadrapania. Próbowałeś jeszcze parę razy przywrócić go, ale niestety okazało się to niemożliwe. Zrezygnowany postanowiłeś powrócić do miasta. Wędrując tą samą drogą (tym razem bacznej zwracając uwagę na pułapki) po pewnym czasie stanąłeś ponownie na ulicach city1b. Mimo iż wyprawa nie przyniosła jakichkolwiek materialnych korzyści, przynajmniej wiesz czego można spodziewać się w przyszłości.', 'pl'),
(39, 1, 'grid.php', 'end3', '0', 'Kiedy wypowiedziałeś słowo <i>pisarz</i>, na moment cały napis zaczął mocniej świecić by po chwili zgasnąć. W tym momencie bezszelestnie uniosło się wieko skrzyni. Kiedy zajrzałeś do środka, zauważyłeś na dnie kawałek starego pergaminu. Ostrożnie podnosząc go, stwierdzasz że to kawałek starożytnej mapy! Delikatnie chowasz go do plecaka i wyruszasz w drogę powrotną. Zachowując czujność omijasz miejsce gdzie wcześniej natrafiłeś na pułapkę, następnie wracasz do znanej ci już części labiryntu. Po pewnym czasie wychodzisz na powierzchnię. Kiedy już się przyzwyczaiłeś do światła dnia, zauważasz że wszyscy przyglądają się tobie. Dopiero teraz widzisz jak bardzo jesteś zakurzony i oblepiony starymi pajęczynami. Chyba czas doprowadzić się do porządku.', 'pl'),
(40, 1, 'grid.php', 'escape', '0', 'Szybko odróciłeś się i zacząłeś uciekać. Przez jakiś czas słyszałeś odgłosy pogoni za sobą, lecz coraz bardziej oddalały się od ciebie. Mimo to przestraszony nadal biegłeś. Zatrzymałeś się dopiero przy wyjściu z labiryntu', 'pl'),
(41, 2, 'grid.php', 'start', '0', 'Idąc korytarzem uważnie badasz jego ściany oraz podłogę. Nagle w pewnym momencie, zauważasz niewielki, wystający nieco ze ściany kamień. Kiedy podchodzisz bliżej i zaczynasz uważnie mu się przyglądać, zauważasz że można go wcisnąć lekko w ścianę. Przez chwilę zastanawiasz się co zrobić.', 'pl'),
(42, 2, 'grid.php', 'box1', '1', 'Wcisnąć przycisk', 'pl'),
(43, 2, 'grid.php', 'box1', '2', 'Zostawić w spokoju', 'pl'),
(44, 2, 'grid.php', '2', '0', 'Postanawiasz zostawić ów przycisk w spokoju i ponownie zająć się tym korytarzem, którym do tej pory wędrowałeś.', 'pl'),
(45, 2, 'grid.php', '1', '0', 'Kiedy kamyk zagłębia się w ścianie po swojej lewej stronie słyszysz lekki trzask, po chwili kawałek ściany niedaleko ciebie zaczyna się przesuwać, odsłaniając długi ciemny korytarz. Bez chwili wahania wchodzisz do środka. Kiedy przebyłeś kilka kroków, słyszysz jak ściana za tobą z powrotem zamyka się. Sprawdzając uważnie boczne ściany odkrywasz że na jednej z nich znajduje się również przycisk. Domyślasz się, że otwiera on owe tajemnicze drzwi z tej strony. Na razie jednak postanawiasz sprawdzić co kryje ten korytarz w którym znajdujesz się. Ostrożnie idąc przed siebie badasz teren dookoła siebie. Korytarz wygląda jak wyciosany w jednym kawałku skały, posiada kształt idealnego kwadratu o boku 3 kroków. Ściany są gładkie, bez jakichkolwiek zadrapań czy rysunków. Czasami widać tuż przy suficie jak odkłada się na nich nieco wilgoci. Czujnie rozglądając się, wędrujesz dalej. Po jakimś czasie powoli z mroku wyłania się skrzyżowanie. Korytarz rozwidla się na prawo, lewo oraz dalej prowadzi przed siebie. Którą drogę wybierasz?', 'pl'),
(46, 2, 'grid.php', 'box2', '1', 'Lewo', 'pl'),
(47, 2, 'grid.php', 'box2', '2', 'Prawo', 'pl'),
(48, 2, 'grid.php', 'box2', '3', 'Przód', 'pl'),
(49, 2, 'grid.php', 'box2', '4', 'Powrót', 'pl'),
(50, 2, 'grid.php', '1.4', '0', 'Postanowiłeś zawrócić do miasta. Idąc tą samą drogą otwierasz sobie ponownie ścianę, przez którą wszedłeś do tego korytarza. Po swoich śladach w pyle chodnika docierasz do wyjścia z labiryntu a następnie na ulice city1b.', 'pl'),
(51, 2, 'grid.php', '1.1', '0', 'Korytarz wije się raz w lewo raz w prawo, sprawiając iż cały czas czujnie obserwujesz otoczenie w oczekiwaniu na niespodzianki. W powietrzu unosi się lekki zapach wilgoci, ze ścian od czasu do czasu skapują na ziemię małe krople czystej wody by po chwili zniknąć gdzieś przy podłodze. Sama podłoga, wygląda dno jakiejś jaskini. Po pewnym czasie korytarz traci swe idealne kształty i powoli zaczyna zamieniać się w wąskie kamienne przejście. Co jakiś czas natykasz na swej drodze sterty kamieni, które ostrożnie omijasz.', 'pl'),
(52, 2, 'grid.php', 'int1', '0', 'W momencie kiedy omijałeś kolejną stertę, twą uwagę przykuł fragment podłogi. Przyglądając mu się z bliska, zauważasz, że jest to niewielka ruchoma płyta, delikatnie naciskając ją, odsłaniasz niewielkie zagłębienie w korytarzu. Nie wygląda to groźnie, ale postanawiasz ominąć owo miejsce. ', 'pl'),
(53, 2, 'grid.php', 'int2', '0', 'Przechodząc obok kolejnej kupki kamieni w pewnym momencie poczułeś jak twoja prawa noga zaczyna się zapadać w podłogę! Przerażony szybko złapałeś równowagę i postawiłeś nogę na pewnym gruncie. Przez kilka chwil odpoczywałeś po tej niespodziance i dopiero wtedy zacząłeś dokładniej przyglądać się podłodze. Zauważasz, że jest to niewielka ruchoma płyta, delikatnie naciskając ją, odsłaniasz niewielkie zagłębienie w korytarzu. Na szczęście tylko trochę cię odrapało.', 'pl'),
(54, 2, 'grid.php', '1.1next', '0', 'Wyruszasz dalej przed siebie. Po pewnym czasie musisz iść coraz wolniej ponieważ już nie tylko ściany ale i podłoga zaczyna być mokra. Nagle z przodu słyszysz cichy szmer wody. Ostrożnie kierujesz się do źródła dźwięku. Po kilku chwilach zauważasz że korytarz kończy się w dużej podziemnej jaskini. Na środku pomieszczenia widzisz małe podziemne jeziorko krystalicznie czystej wody. Ostrożnie zbliżając się do brzegu, zauważasz że w wodzie, blisko środka jeziora coś delikatnie pobłyskuje w świetle twojej pochodni. Ciężko ci określić co kryje się poza kręgiem światła, jednak owe błyski bardzo cię zaintrygowały. Co postanawiasz?', 'pl'),
(55, 2, 'grid.php', 'box3', '1', 'Zanurkować', 'pl'),
(56, 2, 'grid.php', 'box3', '2', 'Odejść', 'pl'),
(57, 2, 'grid.php', '1.1.2', '0', 'Postanowiłeś zostawić ów przedmiot w spokoju i wrócić do miasta. Chwilę jeszcze posiedziałeś sobie nad brzegiem jeziorka aby nieco odpocząć. W końcu zebrałeś swój ekwipunek i ruszyłeś w drogę powrotną. Kiedy odchodziłeś już od tafli wody, nagle usłyszałeś plusk. Szybko odwracając się, zobaczyłeś potężne cielsko nieznanej ci bestii jak powoli ponownie zanurza się pod wodę. Oglądając się niepewnie za plecy, szybko podążyłeś z powrotem korytarzem. Po jakimś czasie dotarłeś do skrzyżowania. ', 'pl'),
(58, 2, 'grid.php', '1.1.1', '0', 'Szybko zostawiłeś swój ekwipunek na brzegu, rozebrałeś się i skoczyłeś do wody. Powoli podpłynąłeś do miejsca, gdzie wcześniej widziałeś błysk na dnie. Nabrałeś powietrza w płuca i zanurkowałeś.', 'pl'),
(59, 2, 'grid.php', 'con1', '0', 'Woda w tym miejscu jest bardzo głęboka, ale udało ci się dojść do dna, po omacku chwyciłeś jakiś przedmiot i z resztką powietrza wypłynąłeś na powierzchnię. W swojej ręce widzisz stalowy miecz.', 'pl'),
(60, 2, 'grid.php', 'con2', '0', 'Niestety woda w tym miejscu okazała się zbyt głęboka i nie udało ci się dojść do dna. Krztusząc się wypływasz na powierzchnię.', 'pl'),
(61, 2, 'grid.php', '1.1.1next', '0', 'Przez chwilę dryfowałeś na wodzie, nagle w najciemniejszym punkcie jeziorka zauważyłeś dwa świecące punkty. Z przerażeniem zdałeś sobie sprawę, że powoli zbliżają się do ciebie. Szybko zacząłeś płynąć w kierunku brzegu. W ostatnim momencie wyskoczyłeś na ląd. Za sobą usłyszałeś tylko syk i plusk. Kiedy odwróciłeś się w tę stronę, zobaczyłeś szybko znikające pod wodą cielsko jakiegoś nieznanego ci potwora. Przez chwilę stałeś na brzegu przyglądając się uważnie tafli jeziorka ale nic więcej się nie wydarzyło. Odpocząłeś jakiś czas następnie zebrałeś swój ekwipunek i ruszyłeś w drogę powrotną. Omijając sterty kamieni wróciłeś do skrzyżowania.', 'pl'),
(62, 2, 'grid.php', 'box4', '1', 'Prawo', 'pl'),
(63, 2, 'grid.php', 'box4', '2', 'Przód', 'pl'),
(64, 2, 'grid.php', 'box4', '3', 'Powrót', 'pl'),
(65, 2, 'grid.php', '1.2', '0', 'Postanowiłeś pójść w prawo. Z początku korytarz wygląda tak samo jak wcześniejsze po których wędrowałeś. Na ścianach widać różne freski zatarte już przez czas, podłoga zrobiona jest z doskonale dopasowanych do siebie płyt. Zauważasz że im dalej wędrujesz przed siebie tym korytarz jest coraz bardziej suchy. Po jakimś czasie zaczyna się powoli zwężać tak, że ma praktycznie szerokość tylko dwóch kroków. W pewnym momencie zauważasz przed sobą, że wygląd podłogi nieco się zmienił. Płyty wcześniej jednokolorowe są teraz czarne lub białe, ustawione w dwóch rzędach naprzemiennie jak na szachownicy. Na ścianach mniej więcej na wysokości twojego brzucha widzisz dziwne otwory, jakby kiedyś w tym miejscu były wbite w poprzek korytarza jakieś pale. Przez moment zastanawiasz się co to może znaczyć.', 'pl'),
(66, 2, 'grid.php', 'int3', '0', 'Dochodzisz do wniosku, że aby przejść przez korytarz, musisz cały czas wędrować po jednym kolorze płyt. Który kolor wybierasz?', 'pl'),
(67, 2, 'grid.php', 'int4', '0', 'Przyglądałeś się jakiś czas podłodze, ale nic ciekawego nie przyszło ci do głowy. Postanawiasz ostrożnie pójść do przodu.', 'pl'),
(68, 2, 'grid.php', 'box5', '1', 'Iść po czarnych', 'pl'),
(69, 2, 'grid.php', 'box5', '2', 'Iść po białych', 'pl'),
(70, 2, 'grid.php', '1.2.1', '0', 'Ostrożnie stawiasz pierwszy krok na czarnej płycie. Nic się nie stało. Po chwili robisz drugi krok. Nadal cicho i spokojnie. Wydaje ci się że wybrałeś dobrą drogę, zaczynasz iść  powoli po czarnych płytach. Kiedy podchodzisz do pierwszych dziur w ścianach, nagle z ich wnętrza wylatują strugi płomieni! Zrywasz się do biegu podczas gdy płomienie owijają się dookoła ciebie.', 'pl'),
(71, 2, 'grid.php', '1.2.2', '0', 'Powoli stawiasz pierwszy krok na białej płycie. Na razie panuje spokój. Robisz drugi krok. Nic się nie stało. Ostrożnie, cały czas patrząc pod nogi przemierzasz ten dziwny korytarz. Kiedy przechodzisz obok owych dziur w ścianie, twoja czujność wzrasta. Jednak nic się nie dzieje. Idą tak po korytarzu docierasz do jego końca.', 'pl'),
(72, 2, 'grid.php', 'hp1', '0', 'W ostatnim momencie udało ci się przebiec na drugą stronę korytarza. Jednak jesteś mocno poparzony, straciłeś 100 punktów życia.', 'pl'),
(73, 2, 'grid.php', 'hp2', '0', 'Próbowałeś biec przed siebie aby jak najszybciej wydostać się z tego piekła. Niestety ogień okazał się silniejszy od ciebie. Nagle nogi ugieły się pod twoim ciężarem a ty powoli zapadłeś w ciemność.', 'pl'),
(74, 2, 'grid.php', '1.2next', '0', 'Przez chwilę odpoczywałeś po przejściu korytarza. Po pewnym czasie zebrałeś się ponownie do wyprawy. Teraz korytarz ponownie nieco się rozszerza. Idziesz dalej przed siebie ostrożnie badając drogę w poszukiwaniu niespodzianek. Co jakiś czas mijasz małe wnęki w ścianach lub wąskie, niskie oraz krótkie korytarze. Korytarz tutaj wygląda na wykonany rękoma jakiś istot rozumnych, jednak jego twórcy chyba niezbyt przykładali się do jego obróbki. Gdzie niegdzie widać jeszcze ślady narzędzi górniczych na ścianach, sufit oraz ściany są dość nierówne oraz krzywe względem siebie. W tym miejscu korytarz ma już szerokość ok 10 kroków i wysokość ok 3. W pewnym momecnie kiedy wyminąłeś kolejną niską i wąską odnogę korytarza usłyszałeś za swoimi plecami okrzyk: <i>Do ataku!</i> i z owej odnogi wypadła banda 5 goblinów! Szybko przygotowujesz się do walki.', 'pl'),
(75, 2, 'grid.php', 'lostfight1', '0', 'Otoczony ze wszystkich stron przez Gobliny, próbowałeś bronić się. Jednak przeciwnicy po prostu przygnietli ciebie liczebnie. Nagle w twej głowie eksplodowała jasna gwiazda i to była ostatnia rzecz jaką zapamiętałeś.', 'pl'),
(76, 2, 'grid.php', 'winfight1', '0', 'Ostatni z Goblinów próbował uciec przed twym gniewiem, lecz na niewiele mu się to zdało i skończył tak jak jego towarzysze. Zdyszany na moment oparłeś się o ścianę i przez chwilę odpoczywałeś. Następnie postanowiłeś zbadać ten mały korytarz, skąd wyskoczyli przeciwnicy. Prawie że wczołgując się do środka zauważasz że nieco głębiej prowadzi on do jakiejś małej groty. Kiedy docierasz do niej, możesz nieco wyprostować się. Widzisz tutaj kilka łóżek mnóstwo śmieci oraz sterty różnych przedmiotów. Większość z nich jest już niestety zniszczona przez czas. Jednak pod jednym z łóżek dostrzegasz jakiś niewielki, podłużny przedmiot. Ostrożnie wyciągasz go ze śmieci. Mimo że cały wysmarowałeś się w resztach jakiś dziwnych rzeczy, nie zwracasz na to uwagi, ponieważ w swojej ręce trzymasz Różdżkę Magii. Uszczęśliwiony tym znaleziskiem postanawiasz przeszukać dokładnie pomieszczenie. Przynosi to doskonałe rezultaty: w stertach śmieci oraz za łóżkami znajdujesz jeszcze 1000 sztuk złota. Zadowolony postanawiasz wrócić do rozdroża i zbadać pozostałe części korytarza. Zmęczony idziesz tą samą drogą co wcześniej. Kiedy docierasz do szachownicy niepewnie stajesz na jej brzegu. Jednak zauważasz że w ścianach zniknęły dziwne otwory. Ostrożnie przechodzisz przez korytarz i wracasz z powrotem do rozstaja.', 'pl'),
(77, 2, 'grid.php', 'box6', '1', 'Przód', 'pl'),
(78, 2, 'grid.php', 'box6', '2', 'Powrót', 'pl'),
(79, 2, 'grid.php', '1.3', '0', 'Korytarz prowadzi cały czas prosto, delikatnie wznosząc się. Po jakimś czasie dostrzegasz, że na ścianach znika wilgoć i powietrze staje się coraz bardziej suche. Podążasz dalej, ostrożnie badając drogę przed sobą. Po jakimś czasie korytarz zaczyna prowadzić ostro pod górę oraz zwężać się. W końcu ma tak małe rozmiary, że musisz nieco schylić się aby dalej iść tą drogą. W pewnym momencie widzisz parę kroków przed sobą niewielką salę. Ostrożnie wchodzisz do środka. Pomieszczenie jest niewielkich rozmiarów. Jedyną rzeczą jaka cię zainteresowała w nim to zamknięte na klucz potężne, okute żelazem drzwi. Kiedy podchodzisz bliżej do nich, wyczuwasz z drugiej strony powiew świeżego powietrza. Co postanawiasz?', 'pl'),
(80, 2, 'grid.php', 'box7', '1', 'Spróbować otworzyć zamek w drzwiach', 'pl'),
(81, 2, 'grid.php', 'box7', '2', 'Przeszukać pomieszczenie', 'pl'),
(82, 2, 'grid.php', 'box7', '3', 'Zawrócić', 'pl'),
(83, 2, 'grid.php', 'door1', '0', 'Próbowałeś manipulować przy zamku, ale niestety wydaje ci się, że to przerasta twoje możliwości. Przez chwilę odpoczywasz aby ponownie spróbować je otworzyć.', 'pl'),
(84, 2, 'grid.php', 'door2', '0', 'Niestety kiedy próbowałeś po raz piąty otworzyć drzwi, zamek zaciął się, blokując kompletnie możliwość przejścia. Zrezygnowany postanowiłeś wrócić z powrotem do skrzyżowania a następnie otwierasz sobie owo tajemne przejście, przez którą wszedłeś do tego korytarza. Po swoich śladach w pyle chodnika docierasz do wyjścia z labiryntu a następnie na ulice city1b.', 'pl'),
(85, 2, 'grid.php', 'door3', '0', 'Manipulowałeś przy zamku tak długo aż usłyszałeś cichy trzask dochodzący od strony drzwi &#8211; zamek poddał się tobie. Szczęśliwy, zbierasz swój ekwipunek i otwierasz drzwi.', 'pl'),
(86, 2, 'grid.php', 'door4', '0', 'Zacząłeś uważnie przyglądać się ścianom oraz podłodze pomieszczenia, badając kawałek po kawałku pomieszczenie w poszukiwaniu jakiejś wskazówki dotyczącej drzwi. Kiedy wydawało ci się już że jest to robota na marne, w pewnym momencie na wschodniej ścianie tuż przy podłodze dostrzegasz niewielki, dobrze ukryty w pyle podłogi przycisk. Bez wahania naciskasz go. W tym momencie za swoimi plecami usłyszałeś cichy trzask od strony drzwi. Powoli podchodząc do nich, naciskasz klamkę. Drzwi powoli otwierają się!', 'pl'),
(87, 2, 'grid.php', '1.3.1', '0', 'Kiedy otwierasz drzwi, w twoje oczy uderza blask słońca a do uszu docierają odgłosy różnych zwierząt. Przez moment stoisz oszołomiony nagłą zmianą otoczenia. W końcu jednak zaczynasz dostrzegać szczegóły otoczenia wokół ciebie. Znajdujesz się na niewielkiej leśnej polance za plecami masz idealnie zamaskowane drzwi przez które tutaj wszedłeś. Rozglądając się uważnie, widzisz po swojej lewej stronie fragment murów miejskich. Znalazłeś się poza miastem. Zastanawiasz się teraz co dalej robić:', 'pl'),
(88, 2, 'grid.php', 'box8', '2', 'Wrócić do miasta', 'pl'),
(89, 2, 'grid.php', 'box8', '1', 'Rozejrzeć się po okolicy', 'pl'),
(90, 2, 'grid.php', '1.3.1.1', '0', 'Postanawiasz rozejrzeć się nieco po okolicy. Wchodzi więc w las otaczający z tej strony miasto i zaczynasz wędrować wśród drzew. Dookoła siebie słyszysz gwar ptasich głosów, co jakiś czas wśród drzew widzisz przemykającą sarnę czy zająca. Pogoda idealnie nadaje się do pieszych wędrówek, więc nie spiesząc się, cały czas podążasz przed siebie. W pewnym momencie dostrzegasz przed sobą wąską leśną ścieżkę wydeptaną przez zwierzęta. Postanawiasz sprawdzić dokąd ona prowadzi.', 'pl'),
(91, 2, 'grid.php', '1.3.1.1.1', '0', 'Wędrujesz jakiś czas tą ścieżką. Drzewa wokół ciebie robią się coraz starsze, porośnięte mchem wyglądają jakby miały twarze które cały czas przyglądają się tobie. Powietrze zaczyna być coraz gęściejsze, światło dnia powoli przygasa nie mogąc przebić się przez gęste korony wiekowych drzew. W pewnym momencie zauważasz, że znikły gdzieś odgłosy zwierząt a dookoła ciebie panuje niesamowita cisza. Zdwajając czujność wędrujesz powoli szlakiem rozglądając się uważnie na boki. Po pewnym czasie dostrzegasz daleko przed sobą plamę jaśniejszego terenu. Zbliżając się do tamtego miejsca widzisz przed sobą niewielką, zarośniętą krzakami leśną polanę. W jednym z przeciwległych jej rogów widzisz niewielkie, tryskające źródełko wody. Natomiast po przeciwległej ścianie, wejście do małej jaskini. Ostrożnie zbliżasz się do wejścia. Kiedy przebyłeś już połowę drogi, nagle z prawej strony usłyszałeś gwałtowny świst. Szybko rzuciłeś się na ziemię, a nad twoją głową przeleciał dziwny kościany dysk. Błyskawicznie poderwałeś się z ziemi i obróciłeś w tamtą stronę akurat na czas aby zobaczyć szarżującego w twoim kierunku Lassaukara!', 'pl'),
(92, 2, 'grid.php', 'lostfight2', '0', 'Gwałtowność ataku istoty całkowicie cię zaskoczyła. Nie byłeś w stanie obronić się przed jego atakiem. Ostatkiem sił próbowałeś wycofać się z powrotem, ale potwór nie dał ci tej szansy, zatapiając swoje szpony w twoim karku. To była ostatnia rzecz jaką zapamiętałeś.', 'pl'),
(93, 2, 'grid.php', 'winfight2', 'karty', 'Mimo początkowego zaskoczenia udało ci nawiązać walkę ze stworem by po chwili powalić go na ziemię. Z walącym jak oszalałe sercem stoisz przez chwilę zbierając myśli. Po chwili jednak ponownie kierujesz się w stronę groty. Kiedy podchodzisz bliżej, widzisz, że jest to niewielka jaskinia jaką można czasami spotkać w lesie. Jej środkiem płynie niewielki strumyk wyciekający ze skały który po opuszczeniu groty skręca w lewą stronę. Przy najdalej położonej ścianie, widzisz jakiś dziwny, kwadratowy przedmiot. Kiedy podchodzisz bliżej dostrzegasz iż jest to dziwna czarna skrzynia. Nigdzie nie widzisz nawet śladów zamka czy zawiasów. Jedynie na wieku dostrzegasz jakieś dziwne zadrapania. Kiedy delikatnie dotykasz tego miejsca, nagle zadrapania zaczynają świecić by po chwili w magiczny sposób ułożyć się w zrozumiały dla ciebie napis: <br />\r\n<i>Podawane do stołu,<br />\r\nDzielone między wszystkich,<br />\r\nNigdy nie jedzone</i><br />\r\nCzy chcesz próbować rozwiązać zagadkę?', 'pl'),
(94, 2, 'grid.php', 'box9', '1', 'Tak', 'pl'),
(95, 2, 'grid.php', 'box9', '2', 'Nie', 'pl'),
(96, 2, 'grid.php', 'chest1', '0', 'Kiedy wypowiadałeś odpowiedź, zauważyłeś że jedna kreska zniknęła a mimo to skrzynia ani drgnęła. Więc to chyba była zła odpowiedź. Zaczynasz zastanawiać się nad kolejną.', 'pl'),
(97, 2, 'grid.php', 'chest2', '0', 'Kiedy po raz piąty wypowiadałeś słowo będące twoim zdaniem rozwiązaniem zagadki, zauważyłeś, że ostatnia kreska zniknęła a napis znów stał się nieczytelny. Mimo iż próbowałeś ponownie go uaktywnić, twoje wysiłki nie na wiele się zdały. Zrezygnowany skierowałeś się w kierunku, gdzie wcześniej widziałeś mury miejskie. Po jakimś czasie wyszedłeś na trakt wiodący do miasta. Trakt jest bardzo zaludniony, wiele karawan kieruje się z i do miasta. Wśród podróżnych wzbudzasz żywe zainteresowanie swoim wyglądem &#8211; pokryty kurzem, z potarganym ubraniem. Kiedy przechodzisz przez bramę miasta, strażnicy bardzo nieufnie przyglądają się twojej osobie. Postanawiasz jak najszybciej doprowadzić się do porządku.', 'pl'),
(98, 2, 'grid.php', 'chest3', '0', 'Kiedy wypowiedziałeś słowo <i>karty</i> , na moment cały napis zaczął mocniej świecić by po chwili zgasnąć. W tym momencie bezszelestnie uniosło się wieko skrzyni. Kiedy zajrzałeś do środka, zauważyłeś na dnie kawałek starego pergaminu. Ostrożnie podnosząc go, stwierdzasz że to kawałek starożytnej mapy! Delikatnie chowasz go do plecaka i wyruszasz w drogę powrotną w kierunku gdzie ostatnio widziałeś mury miasta. Wędrując jakiś czas przez las w końcu docierasz na trakt prowadzący do stolicy. Trakt jest bardzo zaludniony, wiele karawan kieruje się z i do miasta. Wśród podróżnych wzbudzasz żywe zainteresowanie swoim wyglądem &#8211; pokryty kurzem, z potarganym ubraniem. Kiedy przechodzisz przez bramę miasta, strażnicy bardzo nieufnie przyglądają się twojej osobie. Postanawiasz jak najszybciej doprowadzić się do porządku.', 'pl'),
(99, 2, 'grid.php', '1.3.1.1.2', '0', 'Wędrując ścieżką z zaciekawieniem rozglądasz się wokoło. Powoli drzewa wokół ciebie robią się coraz starsze, obrośnięte mchem nadającym im wygląd starych pomarszczonych twarzy. Wokół siebie słyszysz gwar ptasich śpiewów, co jakiś czas widzisz jak niedaleko ścieżki przebiega sarna lub zając. Pogoda idealnie nadaje się do pieszych wędrówek, powoli zaczynasz zapominać o tym co cię spotkało w labiryncie, nabierasz sił oraz ochoty do dalszej wędrówki. Kiedy tak wędrowałeś sobie jakiś czas, zauważyłeś w pewnym momencie że ścieżka zaczyna skręcać w głąb lasu. Po chwili do twoich uszu dobiegł szmer płynącej wody. Droga prowadzi dokładnie w jego kierunku. Zachowując ostrożność powoli zmierzasz w tamtą stronę. Mija moment i już widzisz niewielki leśny potok na końcu ścieżki. Podchodzisz nad jego brzeg i gasisz pragnienie. W pewnym momencie wyczuwasz że ktoś ci się przygląda. Rozglądając się w okół, widzisz w dole strumienia siedzącego nad brzegiem wędrowca. Jest to Człowiek w podeszłym już wieku, ubrany w podróżny skórzany kaftan, brązowe spodnie. Jesteś bardzo zdziwiony tym widokiem. Pchany ciekawością, postanawiasz podejść i porozmawiać z owym podróżnikiem. Kiedy podchodzisz do starca, ten odzywa się: <i>Witaj podróżniku, niech Illuminati zawsze kieruje twą drogą. Rzadko można spotkać kogoś do rozmowy w tej okolicy. Wybacz starcowi gadatliwość ale dawno z nikim już nie rozmawiałem. Cóż cię sprowadza w tak odległą okolicę? Czyżby pragnienie przygód? A może poszukiwanie wiedzy? Jeżeli chcesz mogę opowiedzieć ci jedną krótką historię tego świata. Jesteś zainteresowany?</i>', 'pl'),
(100, 2, 'grid.php', 'oldman1', '0', '<i>Doskonale, więc słuchaj uważne</i> odpowiada zadowolony starzec. Siadasz koło niego i słuchasz co ma do powiedzenia:<i>Nie do nas należy ten świat. Kto inny był na nim dawno temu, kto inny przybędzie na niego już po nas. Zasada ta dotyczy nie tylko śmiertelników ale i bogów. Dawno temu, kiedy świat był jeszcze młody inni bogowie nim rządzili. Interesowały ich inne sprawy niż dzisiaj, inaczej ułożyli ten świat. Kto inny wtedy chodził po jego powierzchni. Dziś tych dawnych panów nazywa się Pierwszymi, ale czy naprawdę byli pierwsi? A może przed nimi był jeszcze ktoś? Nikt nie zna odpowiedzi na to pytanie. Długi czas rządzili owi Pradawni na tych ziemiach ale w końcu nadszedł ich kres. Ich cywilizacja upadła, plotki głoszą iż jeszcze gdzieś na tym świecie można znaleźć po nich ślady. Wtedy to na świat wstąpili inni bogowie, ci których dzisiaj znamy. Było ich wielu lecz przewodził nim Illuminati. On to kierował poczynaniami swych braci i sióstr. Jednak nie wszyscy chcieli się go słuchać. Wybuchła wojna. Część z bogów zginęła w tych walkach, większość jednak poszła w zapomnienie. W tamtych czasach bogowie byli znacznie słabsi niż teraz albowiem moc boga w głównej mierze zależy od wiary jego wyznawców. Ci którzy zginęli odeszli już na zawsze, ale ci co zostali zapomniani nadal krążą wokół niczym szare cienie dawnej potęgi. Nikt już nie pamięta ich imion ani jak wyglądali. Lecz zawsze istnieje nadzieja iż ktoś ich odnajdzie i znów staną się tacy jak dawniej. Z wojny zwycięsko wyszła czwórka bogów, ich imiona znasz pewnie doskonale ;)</i> (uśmiecha się do ciebie) <i>Wtedy to właśnie na świecie pojawiły się inteligentne rasy. Czwórka bogów postanowiła zaopiekować się nimi. Troje skierowało swoją uwagę na wąskie grupy istot, jednynie Illuminati nadal pomagał każdemu kto prosił i zasługiwał na pomoc. Czas płynął wartko imperia podnosiły się i upadały, jednak bogowie nie zwracali uwagi na tak nieistotne sprawy. Nadal są wśród nas, nadal pomagają swym wyznawcą, nie zawsze w widoczny sposób, najczęściej w ukryciu, tak że nie zdajemy sobie z tego sprawy. Illuminati jest najpotężniejszy z całego panteonu, patronuje każdej istocie. Wiele razy zdarzało się że zupełnie beznadziejne sprawy udało się doprowadzić do szczęśliwego zakończenia dzięki jego pomocy. Nazywam go <b>on</b> ale niestety w języku śmiertelników nie istnieje słowo, które mogłoby opisać tę istotę. Często ponoć wędruje po świecie przyglądając mu się, albowiem jak żaden inny bóg pokochał to miejsce. Uważaj więc przyjacielu ponieważ pewnego dnia możesz się natknąć na niego</i> (starzec roześmiał się) Historia toczyła się jeszcze przez długi czas. Słuchałeś o dawnych czasach o dawnych królestwach istniejących na tych ziemiach, tak długo, że czas istnienia Vallheru wydał ci się zaledwie chwilą. Starzec okazał się znakomitym gawędziarzem, przykuł twoją uwagę aż do późnego wieczora. Wiele jeszcze razy w swych opowieściach wracał do Illuminati i innych bogów. Kiedy zmrok już zapadł starzec w końcu przerwał swą opowieść i zwrócił się do ciebie:<i>Cóż, późno już, pora odpocząć, wybacz starcowi że się tak rozgadał ale dawno już nie spotkałem tak uprzejmego słuchacza. Widzę że jesteś już zmęczony. Połóż się i śpij. Okolica tutaj jest bezpieczna, nie masz się więc czego obawiać</i> Dopiero teraz poczułeś jak bardzo jesteś zmęczony. Położyłeś się na ziemi i prawie natychmiast zasnąłeś. Budzisz się dopiero późnym rankiem. Ze zdziwieniem zauważasz że starzec zniknął już. Ty mimo wszystko czujesz się rześki i wypoczęty. Zbierasz swój ekwipunek i z powrotem ruszasz w kierunku gdzie wczoraj widziałeś mury miejskie.  Po jakimś czasie docierasz do traktu wiodącego do stolicy. Trakt jest bardzo zaludniony, wiele karawan kieruje się z i do miasta. Powoli dochodzisz do bram miasta i znów jesteś w miejscu doskonale ci znanym. ', 'pl'),
(101, 2, 'grid.php', 'oldman2', '0', '<i>No cóż twoja wola, wobec tego może usiądziesz obok i odpoczniemy razem?</i> Zgadzasz się na propozycję starca, siadasz wygodnie obok niego i do późnego wieczora rozmawiacie o różnych sprawach związanych z podróżami czy z codziennym życiem. Starzec wypytuje cię czasami o twoje życie a ty ze zdziwieniem zauważasz że odpowiadasz na wszystkie jego pytania. Późnym wieczorem starzec rzecze: <i>Pora nieco odpocząć po dniu pełnym przygód. Okolica tutaj jest bezpieczna więc proponuję nieco zdrzemnąć się.</i> Dopiero w tym momencie poczułeś jak bardzo jesteś zmęczony. Kładziesz się więc na ziemi i po chwili już zasypiasz twardym snem. Budzisz się dopiero późnym rankiem. Zauważasz że jesteś zupełnie sam, tajemniczy starzec gdzieś zniknął. Wypoczęty wracasz z powrotem do miasta, kierując się w stronę, gdzie wczoraj widziałeś jego mury. Po jakimś czasie docierasz do traktu wiodącego do stolicy. Trakt jest bardzo zaludniony, wiele karawan kieruje się z i do miasta. Powoli dochodzisz do bram miasta i znów jesteś w miejscu doskonale ci znanym', 'pl'),
(102, 2, 'grid.php', '1.3.1.2', '0', 'Zmęczony niedawnymi wydarzeniami, postanawiasz zawrócić z drogi i udać się z powrotem do miasta. Kierujesz się w stronę murów. Po jakimś czasie docierasz do głównego traktu prowadzącego do miasta. Trakt jest bardzo zaludniony, wiele karawan kieruje się z i do miasta. Wśród podróżnych wzbudzasz żywe zainteresowanie swoim wyglądem &#8211; pokryty kurzem, z potarganym ubraniem. Kiedy przechodzisz przez bramę miasta, strażnicy bardzo nieufnie przyglądają się twojej osobie. Postanawiasz jak najszybciej doprowadzić się do porządku. ', 'pl'),
(103, 3, 'grid.php', 'start', '0', 'Wędrując korytarzami labiryntu w pewnym momencie dostrzegasz leżący pod jedną ze ścian ludzki szkielet. Dookoła niego leżą zardzewiałe resztki uzbrojenia oraz fragmenty jakiś szmat, które pewnie w przeszłości były ubraniem tego osobnika. Delikatnie rozgarniając śmieci przeszukujesz okolicę w poszukiwaniu jakiś interesujących rzeczy. Po chwili twoja ciekawość zostaje wynagrodzona, znajdujesz niewielki kawałek bardzo starego i podniszczonego pergaminu. Delikatnie aby nie zniszczyć znaleziska podnosisz go z ziemi i lekko dmuchając, oczyszczasz z kurzu. Przyglądając mu się uważnie, spostrzegasz że jest to nieduża mapka przedstawiająca pewien obszar labiryntu. W jednym miejscu widać niewyraźnie zaznaczony czerwony punkt na mapie. Orientujesz się mniej więcej gdzie ów obszar może się znajdować. Zastanawiasz się co robić:', 'pl'),
(104, 3, 'grid.php', 'box1', '1', 'Sprawdzić obszar labiryntu zaznaczony na mapie', 'pl'),
(105, 3, 'grid.php', 'box1', '2', 'Ruszyć dalej swoją drogą', 'pl'),
(106, 3, 'grid.php', '2', '0', 'Postanowiłeś zostawić sobie rozwiązanie tej zagadki na później. Kiedy próbowałeś schować ów pergamin do plecaka, rozpadł on się na małe kawałki. Niestety teraz jest już zupełnie nieprzydatny. Po chwili zbierasz swoje rzeczy i ponownie wyruszasz na zwiedzanie labiryntu. Może następnym razem będziesz miał więcej szczęścia.', 'pl'),
(107, 3, 'grid.php', '1', '0', 'Postanowiłeś zobaczyć co kryje tajemnicza mapka. Zebrałeś z powrotem swój ekwipunek i ruszyłeś w kierunku pokazywanym przez pergamin. Mijasz po drodze różne odnogi labiryntu, cały czas kierując się do miejsca przeznaczenia. Po jakimś czasie dotarłeś do początku obszaru pokazanego na mapce. Ta część labiryntu wygląda na znacznie starszą niż korytarze, którymi wcześniej wędrowałeś. Ściany są doskonale obrobione, widać na nich jeszcze resztki dawnych fresków, dziś już zamazane prawie całkowicie przez czas. Podłogę pokrywa niewielka warstwa kurzu, każdy twój krok powoduje wzbijanie się niewielkich białych obłoczków. W pewnym momencie w mroku przed sobą dostrzegasz kontury jakiejś istoty. Zaniepokojony powoli zbliżasz się do tego miejsca. Kiedy podchodzisz bliżej, ściany korytarza znikają a ty znajdujesz się w dość dużym pomieszczeniu. Teraz zauważasz, że to co wcześniej wziąłeś za żywą istotę to tak naprawdę kamienny posąg na cokole. Przedstawia on ludzkiego mężczyznę w podeszłym wieku, ubranego w szaty, trzymającego w lewej ręce księgę. Kiedy obchodzisz posąg dookoła, zauważasz iż cały czas stoi on przodem do ciebie! Tak jakby obracał się na wszystkie strony. Z zaciekawieniem podziwiasz mistrzostwo dawnych rzemieślników. Na cokole, tuż przy ziemi zauważasz ledwo widoczny napis. Przyklękasz i zaczynasz przyglądać mu się uważniej. Napis głosi <i>Budowniczy Labiryntu</i>. Litera <b>o</b> w napisie wygląda znacznie wyraźniej niż pozostałe. Jej środek jest nieco głębszy, tak jakby brakowało tutaj jakiegoś elementu. Rozmyślając nad tą zagadką rozglądasz się po okolicy. Widzisz, że z tego pomieszczenia wychodzą jeszcze dwie drogi, oprócz tej, którą przyszedłeś. Może któraś z nich kryje odpowiedź tajemnicy posągu? Co postanawiasz?', 'pl'),
(108, 3, 'grid.php', 'box2', '1', 'Iść naprzód', 'pl'),
(109, 3, 'grid.php', 'box2', '2', 'Iść w prawo', 'pl'),
(110, 3, 'grid.php', 'box2', '3', 'Zawrócić', 'pl'),
(111, 3, 'grid.php', '1.3', '0', 'Postanowiłeś zostawić posąg wraz z jego tajemnicami i zawrócić do znanej ci części labiryntu. Kiedy ruszyłeś w drogę powrotną, doznałeś niejasnego przeczucia że o czymś zapomniałeś. Przystanąłeś na chwilę zastanawiając się. Już po chwili orientujesz się o co chodzi ? zgubiłeś gdzieś fragment mapki. Po chwili dochodzisz do wniosku, że i tak na razie nie będzie ci zupełnie potrzebna. Ruszasz z powrotem w swoją drogę, by po jakimś czasie dojść do dobrze znanej ci części labiryntu. Postanawiasz wrócić do miasta aby nieco odpocząć oraz doprowadzić się do porządku. Kierujesz się w stronę wyjścia z labiryntu a następnie wychodzisz na gwarne ulice miasta.', 'pl'),
(112, 3, 'grid.php', '1.1', '0', 'Skierowałeś się do korytarza prowadzącego na wprost od korytarza którym przyszedłeś. Prowadzi on cały czas prosto, czasami tylko skręcając nieznacznie to w prawo to w lewo. Ściany korytarza, podobnie jak tego którym tutaj przyszedłeś, są doskonale obrobione, na ścianach widać ślady dawnych fresków przedstawiających różne istoty inteligentne oraz zwierzęta i potwory. Na podłodze leży cienka warstewka kurzu, która zmienia się w niewielkie obłoczki, kiedy stawiasz kroki. Co jakiś czas widzisz niewielkie pajęczyny w rogach korytarza. Ostrożnie przyglądając się ścianom i podłodze idziesz cały czas przed siebie.', 'pl');
INSERT INTO `quests` (`id`, `qid`, `location`, `name`, `option`, `text`, `lang`) VALUES 
(113, 3, 'grid.php', 'int1', '0', 'W pewnym momencie z jednej ze ścian słyszysz odgłos delikatnego świstu a po chwili czujesz w lewym boku lekkie pieczenie. Po chwili dostajesz lekkich zawrotów głowy, które zaraz mijają. Odruchowo dotykasz dłonią tego miejsca na ciele. Pod palcami wyczuwasz dwie małe igiełki. Ostrożnie wyciągasz je z boku i odrzucasz na ziemię. Patrząc na lewą ścianę, dopiero teraz przyuważasz że jedna z twarzy na ścianie nie została do końca zatarta przez czas. Jej oczodoły to dwa niewielkie otwory z których prawdopodobnie wyleciały owe maleńkie strzałki. Przyglądając się uważnie podłodze dopiero teraz widzisz doskonale zakamuflowany mały, ruchomy fragment podłogi. Cóż, masz szczęście że tak to się tylko skończyło. Po krótkim odpoczynku ruszasz dalej przed siebie.', 'pl'),
(114, 3, 'grid.php', 'int2', '0', 'Przyglądając się ścianą zauważasz, że jedna z figur nie została zatarta przez czas. Jest to twarz jakiegoś nieznanego ci stworzenia. Oczy owej postaci są głębokimi otworami o średnicy palca. Wietrząc w tym jakiś podstęp uważnie rozglądasz się na boki oraz na podłogę. Po chwili twoje przypuszczenia okazują się słuszne. Przyglądając się uważnie podłodze widzisz doskonale zakamuflowany mały, ruchomy fragment podłogi. Prawdopodobnie jest to spust zwalniający jakąś pułapkę. Ostrożnie wymijasz niebezpieczny fragment i kierujesz się dalej przed siebie jeszcze uważniej sprawdzając okolicę.', 'pl'),
(115, 3, 'grid.php', '1.1next', '0', 'Podróżując dalej korytarzem w pewnym momencie zauważasz że zaczyna on się rozszerzać na boki. Po jakimś czasie widzisz że plama czerni przed tobą jakby się rozszerzyła. Domyślasz się że dochodzisz do jakiegoś pomieszczenia. Ostrożnie patrząc na wszystkie strony, wchodzisz do środka. Jest to niewielka komnata o rozmiarach 10 kroków na 6 kroków i wysokości ok 15 stóp. Jej ściany są wykonane z jednolitego, gładkiego kamienia. Jednak twoją uwagę przykuwa przede wszystkim podłoga. Ta wykonana jest z niewielkich płyt. Większość płyt jest na poziomie podłogi, ale dziewięć płytek ustawionych w trzech rzędach nieco wystaje ponad poziom. Kiedy ostrożnie naciskasz jedną płytkę nic się nie dzieje. Domyślasz się, że musisz ustawić jakiś wzór na nich przyciskając odpowiednie z nich.', 'pl'),
(116, 3, 'grid.php', 'box3', '1', 'Lewy rząd w pionie', 'pl'),
(117, 3, 'grid.php', 'box3', '2', 'Środkowy rząd w pionie', 'pl'),
(118, 3, 'grid.php', 'box3', '3', 'Prawy rząd w pionie', 'pl'),
(119, 3, 'grid.php', 'box3', '4', 'Krzyż +', 'pl'),
(120, 3, 'grid.php', 'box3', '5', 'Na ukos X', 'pl'),
(121, 3, 'grid.php', 'box3', '6', 'Górny rząd w poziomie', 'pl'),
(122, 3, 'grid.php', 'box3', '7', 'Środkowy rząd w poziomie', 'pl'),
(123, 3, 'grid.php', 'box3', '8', 'Dolny rząd w poziomie', 'pl'),
(124, 3, 'grid.php', 'plates1', '0', 'Kiedy ustawiłeś daną kombinację z napięciem oczekiwałeś na to co się wydarzy. Niestety oprócz tego co sam zrobiłeś nic innego się nie wydarzyło. Widocznie to nie była prawidłowa kombinacja. Po chwili przyciski ponownie wróciły do swojego poprzedniego stanu.', 'pl'),
(125, 3, 'grid.php', 'plates2', '0', 'Kiedy naciskałeś ostatni przycisk, usłyszałeś cichy trzask. Szybko odwróciłeś się w tę stronę. Widzisz, jak w jednej ze ścian otworzyła się niewielka nisza a w niej jakiś worek. Ostrożnie podchodzisz bliżej, bierzesz ów worek i zaglądasz do środka. Od razu zauważasz złotawy odblask ? to złoto! Znajdujesz 2000 sztuk złota. Szybko chowasz znalezisko do kieszeni i z powrotem idziesz do pomieszczenia z posągiem. Po drodze tylko ostrożnie wymijasz miejsce gdzie uprzednio natknąłeś się na pułapkę. Po jakimś czasie docierasz do posągu. Kiedy odwracasz się aby spojrzeć za siebie zauważasz, że korytarz, którym przed chwilą szedłeś zniknął! Masz teraz tylko dwie drogi do wyboru:', 'pl'),
(126, 3, 'grid.php', 'box4', '2', 'Wrócić do znanej ci części labiryntu', 'pl'),
(127, 3, 'grid.php', 'box4', '1', 'Korytarz w prawo', 'pl'),
(128, 3, 'grid.php', '1.1.1.2', '0', 'Stwierdzasz, że to co do tej pory znalazłeś, w zupełności ci wystarczy. Postanawiasz zawrócić do miasta. Powoli zmierzasz do znanej już ci części labiryntu. Kiedy ruszyłeś w drogę powrotną, doznałeś niejasnego przeczucia że o czymś zapomniałeś. Przystanąłeś na chwilę zastanawiając się. Już po chwili orientujesz się o co chodzi - zgubiłeś gdzieś fragment mapki. Po chwili dochodzisz do wniosku, że i tak na razie nie będzie ci zupełnie potrzebna. Ruszasz z powrotem w swoją drogę, by po jakimś czasie dojść do dobrze znanej ci części labiryntu. Postanawiasz wrócić do miasta aby nieco odpocząć oraz doprowadzić się do porządku. Kierujesz się w stronę wyjścia z labiryntu a następnie wychodzisz na gwarne ulice miasta.', 'pl'),
(129, 3, 'grid.php', '1.2', '0', 'Korytarz prowadzi cały czas prosto, delikatnie pnąc się nieco w górę. Podobnie jak korytarze, którymi wcześniej wędrowałeś ten również jest doskonale obrobiony, na ścianach widać różne freski. Z tego co zauważasz, większość z nich przedstawia wilki. Ostrożnie kierujesz się dalej, rozglądając się na boki w poszukiwaniu pułapek oraz zasadzek. Po jakimś czasie, widzisz, że plama czerni przed tobą powiększa się. Domyślasz się, że dochodzisz do jakiegoś pomieszczenia. Kiedy przekraczasz jego próg na chwilę stajesz zdezorientowany - tak dużego pomieszczenia jeszcze nie widziałeś w tym labiryncie. Światło pochodni ledwo dociera do sufitu, natomiast ściany boczne, są po prostu niewidoczne. Nieco dalej przed sobą, dostrzegasz niewielkie jaśniejsze punkty. Przyglądając się uważniej, widzisz, że w suficie znajdują się nieduże otwory, przez które wpada światło dnia. Stojąc tak przez chwilę, nagle z oddali słyszysz dziwny dźwięk, jakby warkot. Odruchowo gasisz szybko pochodnię. Po krótkiej chwili, twój wzrok przyzwyczaja się do półmroku jaki panuje w tej komnacie. Ostrożnie skradając się zmierzasz w kierunku odgłosów. Pomieszczenie jest ogromne, ów dziwny odgłos wydaje się wydobywać zewsząd. Twoje nerwy są napięte do ostatnich granic. Wtem w szarości komnaty, zauważasz kilkadziesiąt kroków przed sobą, leżący na ziemi dość duży, podłużny kształt. Ostrożnie zbliżając się do niego, stwierdzasz, że ów dziwny odgłos powoli narasta. Natomiast im bliżej jesteś tym przedmiot leżący na ziemi powoli zmienia się w pokrytą szarym futrem, potężną, śpiącą bestię. To wilkołak! W pewnym momencie zauważasz że płyty podłogi przed tobą mają narysowane na każdej z nich pysk wilka. Jest tylko bardzo wąski pas pomiędzy nimi. Twoje przeczucie mówi, że lepiej nie stąpać po naznaczonych wilkiem płytach. Na razie bestia śpi, ale co będzie dalej, nie wiadomo. Co postanawiasz?', 'pl'),
(130, 3, 'grid.php', 'box5', '1', 'Przekraść się obok', 'pl'),
(131, 3, 'grid.php', 'box5', '2', 'Zaatakować potwora', 'pl'),
(132, 3, 'grid.php', 'box5', '3', 'Zawrócić', 'pl'),
(133, 3, 'grid.php', '1.2.3', '0', 'Postanowiłeś zostawić zagadkę tego korytarza nie rozwiązaną i wrócić z powrotem do miasta. Ostrożnie wycofujesz się ze strefy wilkołaka i bezszelestnie zmierzasz w kierunku korytarza którym tutaj przyszedłeś. Po jakimś czasie docierasz do skrzyżowania z posągiem. Powoli zmierzasz do znanej już ci części labiryntu. Kiedy ruszyłeś w drogę powrotną, doznałeś niejasnego przeczucia że o czymś zapomniałeś. Przystanąłeś na chwilę zastanawiając się. Już po chwili orientujesz się o co chodzi ? zgubiłeś gdzieś fragment mapki. Po chwili dochodzisz do wniosku, że i tak na razie nie będzie ci zupełnie potrzebna. Ruszasz z powrotem w swoją drogę, by po jakimś czasie dojść do dobrze znanej ci części labiryntu. Postanawiasz wrócić do miasta aby nieco odpocząć oraz doprowadzić się do porządku. Kierujesz się w stronę wyjścia z labiryntu a następnie wychodzisz na gwarne ulice miasta.', 'pl'),
(134, 3, 'grid.php', '1.2.2', '0', 'Postanawiasz postawić wszystko na jedną kartę i spróbować pokonać potwora. Przygotowany do walki zaczynasz skradać się w jego stronę. Jednak gdy tylko postawiłeś swoją stopę na jednej z płyt z wizerunkiem wilka, chrapanie momentalnie ustało. Widzisz, jak bestia błyskawicznie zerwała się na nogi od razu spoglądając w twoją stronę. Usłyszałeś tylko niski, gardłowy warkot, dojrzałeś jak oczy wilkołaka rozgorzały czerwienią i stwór ruszył całym impetem na ciebie. Rozpoczyna się walka!', 'pl'),
(135, 3, 'grid.php', 'winfight', '0', 'Po twoim ostatnim ciosie potwór wydał śmiertelny charkot z siebie i padł nieżywy na ziemię. Przez chwilę odpoczywałeś, następnie zebrałeś ponownie swój ekwipunek i ruszyłeś przed siebie.', 'pl'),
(136, 3, 'grid.php', 'lostfight', '0', 'Impet ataku potwora przeraził cię. Desperacko próbowałeś stawić mu opór, ale niestety pierwotna furia tym razem wygrała z twoim doświadczeniem. Nagle poczułeś palący ból gardła a następnie cały świat zawirował by po chwili zniknąć w ciemności. Ostatnią rzeczą jaka została ci w pamięci był zakrwawiony pysk bestii pochylającej się nad tobą.', 'pl'),
(137, 3, 'grid.php', '1.2.1', '0', 'Postanowiłeś spróbować przejść po nie naznaczonym fragmencie korytarza. Ponieważ jest to bardzo wąski odcinek, będziesz musiał wykazać się niezłą zręcznością. Ostrożnie stawiasz pierwszy krok, za nim drugi...', 'pl'),
(138, 3, 'grid.php', 'agi1', '0', 'Idąc cały czas bardzo powoli zwracasz przede wszystkim uwagę pod nogi. Jednak odgłos chrapania obok oraz myśl o tym co go wydaje dekoncentruje ciebie. Przez nieuwagę nagle stawiasz jedną stopę na płytę z głową wilka. Momentalnie z przerażeniem stwierdzasz, że chrapanie umilkło. Odwracasz się w tamtym kierunku, akurat na czas, aby zobaczyć szarżującego na ciebie z furią w oczach Wilkołaka!', 'pl'),
(139, 3, 'grid.php', 'agi2', '0', 'Idąc cały czas bardzo powoli ostrożnie stawiasz stopy na ścieżce. Cały czas twoją uwagę rozprasza dźwięk dochodzący z boku oraz świadomość kto ten dźwięk wydaje. W końcu, kiedy docierasz do końca owych płyt z pyskiem wilka ogarnia cię uczucie zwycięstwa. Cały spocony z wysiłku przez pewien czas odpoczywasz by następnie zebrać swój ekwipunek i ruszyć przed siebie.', 'pl'),
(140, 3, 'grid.php', '1.2.1next', '0', 'Przez dość długi czas wędrujesz ową olbrzymią salą. Wydaje ci się, że nie ma ona końca. W jej półmroku widzisz dość wyraźnie na kilka kroków przed sobą. W pewnym momencie widzisz, że przed tobą zaczyna majaczyć obszar czerni. Zapalasz pochodnię i widzisz koniec komnaty przechodzący w korytarz. Ostrożnie ruszasz tą drogą. Ściany tutaj są gładkie, wykonane z jednolitego czarnego kamienia. Nawet na podłodze nie zauważasz ani śladu kurzu. Całość sprawia wrażenie naturalnej formacji skalnej a nie czegoś co zostało wykonane rękami inteligentnych istot. Po jakimś czasie docierasz do niewielkiego skrzyżowania w kształcie litery T. Którą drogę wybierasz?', 'pl'),
(141, 3, 'grid.php', 'box6', '1', 'Korytarz w prawo', 'pl'),
(142, 3, 'grid.php', 'box6', '2', 'Korytarz w lewo', 'pl'),
(143, 3, 'grid.php', 'box6', '3', 'Zawrócić do miasta', 'pl'),
(144, 3, 'grid.php', '1.2.1.3', '0', 'Postanowiłeś zawrócić i nie badać dalej korytarza. Ostrożnie wróciłeś do olbrzymiej sali. Niepewny co ciebie w niej czeka, powoli ruszyłeś przed siebie. Jednak ze zdziwieniem zauważasz, że tam, gdzie wcześniej były płyty z pyskiem wilka teraz jest zupełnie normalna podłoga. Nie ma nawet śladu Wilkołaka. Uspokojony przechodzisz szybkim krokiem całe pomieszczenie i po jakimś czasie docierasz do jego przeciwległego końca. Tutaj ponownie wędrujesz korytarzem w kierunku placu ze statuą. Po pewnym okresie docierasz do posągu a następnie wybierasz drogę do znanej ci już części labiryntu. Kiedy ruszyłeś w drogę powrotną, doznałeś niejasnego przeczucia że o czymś zapomniałeś. Przystanąłeś na chwilę zastanawiając się. Już po chwili orientujesz się o co chodzi ? zgubiłeś gdzieś fragment mapki. Po chwili dochodzisz do wniosku, że i tak na razie nie będzie ci zupełnie potrzebna. Ruszasz z powrotem w swoją drogę, by po jakimś czasie dojść do dobrze znanej ci części labiryntu. Postanawiasz wrócić do miasta aby nieco odpocząć oraz doprowadzić się do porządku. Kierujesz się w stronę wyjścia z labiryntu a następnie wychodzisz na gwarne ulice miasta.', 'pl'),
(145, 3, 'grid.php', '1.2.1.1', '0', 'Korytarz prowadzi cały czas prosto. W odróżnieniu od wcześniejszych korytarzy, którymi wędrowałeś, ten bardziej przypomina naturalny korytarz skalny niż dzieło rąk istot inteligentnych. Ściany są nierówne, czasami korytarz to zwęża się to rozszerza. Co jakiś czas na ziemi leżą kawałki odłupanych ścian, tak jakby prace nad tą częścią labiryntu nie zostały nigdy ukończone. Na ścianach osadzają się drobne kropelki wilgoci. Wędrujesz cały czas przed siebie uważnie rozglądając się na wszystkie strony w poszukiwaniu niebezpieczeństw. Po jakimś czasie, okazuje się że ten korytarz to ślepa uliczka.', 'pl'),
(146, 3, 'grid.php', 'int3', '0', 'Przez chwilę przyglądasz się uważnie ścianom, czy nie kryją jakiś przycisków czy innych zagadek. Niestety nie znajdujesz nic ciekawego oprócz kilku porozrzucanych kamieni. Zawiedziony wracasz z powrotem do skrzyżowania aby wybrać inną drogę.', 'pl'),
(147, 3, 'grid.php', 'int4', '0', 'Przez chwilę przyglądasz się uważnie ścianą w poszukiwaniu jakiś ukrytych przycisków. W pewnym momencie ściana położona po twojej lewej stronie wzbudza twoje podejrzenia. Podchodzisz bliżej aby dokładnie ją zbadać. Wprawdzie czujesz dotykiem chłód kamienia, ale wiedziony instynktem delikatnie napierasz na ścianę. Okazuje się, że twoja ręka przechodzi przez nią jak przez powietrze. To iluzja! Bierzesz głęboki oddech i przekraczasz ową nieistniejącą barierę.', 'pl'),
(148, 3, 'grid.php', '1.2.1.1next', '0', 'Przed sobą widzisz prosty korytarz prowadzący gdzieś w głąb labiryntu. Ostrożnie podążasz nim przed siebie. Co jakiś czas mijasz niewielkie wnęki w ścianach, resztki zasypanych korytarzy czy bardzo krótkie korytarze prowadzące donikąd. Przypomina ci to nieco starą, opuszczoną kopalnię. Po jakimś czasie docierasz do końca korytarza. Nauczony doświadczeniem, ponownie badasz ściany. W jednej z nich, tuż przy podłodze widzisz dość głęboką wnękę. Ostrożnie wkładając tam rękę wyczuwasz jakiś podłużny kształt. Kiedy wyciągasz na wierzch ów przedmiot, okazuje się że jest to niewielka skrzyneczka bez zamka. Delikatnie otwierasz ją. W środku znajduje się doskonale zakonserwowany stalowy miecz. Zawijasz go z powrotem w szmaty i chowasz do plecaka. Przez chwilę jeszcze dokładnie badasz okolicę, ale niestety nic ciekawego więcej nie znajdujesz. Postanawiasz zawrócić do skrzyżowania. Wracając ponownie przekraczasz iluzoryczną ścianę a następnie znanym sobie już korytarzem wracasz do skrzyżowania.', 'pl'),
(149, 3, 'grid.php', '1.2.1.2', 'żywiołak', 'Korytarz prowadzi cały czas prosto. Ze zdziwieniem zauważasz że kończy się już po paru dziesiątkach kroków. W ścianie po lewej stronie widzisz niewielką niszę, a w niej mały w kształcie jajka niebieski klejnot. Kiedy próbowałeś sięgnąć po niego poczułeś że twoja ręka natrafiła na jakąś niewidzialną barierę. W tym momencie, tuż nad niszą, ściana zamigotała na moment. Zaskoczony odsunąłeś się kawałek. Jednak nic ci nie zagraża. Zamiast tego zobaczyłeś na ścianie następujący napis:<i>Trwanie na świecie tak się zwie<br />W łodzi znajdują się aż dwa<br />Obszar to zboża każdy wie<br />Na końcu zawsze stoi K</i><br />Domyślasz się, że trzeba odpowiedzieć na tę zagadkę aby bariera blokująca niszę zniknęła. Zaczynasz zastanawiać się nad odpowiedzią.', 'pl'),
(150, 3, 'grid.php', 'answer1', '0', '<i>Trwanie na świecie tak się zwie<br />W łodzi znajdują się aż dwa<br />Obszar to zboża każdy wie<br />Na końcu zawsze stoi K</i><br />\r\nWypowiedziałeś słowo, które twoim zdaniem powinno być rozwiązaniem zagadki a następnie próbowałeś dotknąć klejnotu. Niestety znów twoja dłoń natrafiła na barierę. Widocznie to nie była poprawna odpowiedź. Zaczynasz zastanawiać się nad kolejną.', 'pl'),
(151, 3, 'grid.php', 'answer2', '0', 'Kiedy po raz piąty wypowiadałeś słowo będące twoim zdaniem odpowiedzią na zagadkę, napis zalśnił by po chwili zniknąć zupełnie. Niestety bariera blokująca klejnot nie zniknęła. Przez chwilę próbowałeś jeszcze raz uaktywnić czar, niestety nic się nie wydarzyło. Zniechęcony postanowiłeś zawrócić. Doszedłeś do skrzyżowania a następnie wróciłeś do olbrzymiej sali. Niepewny co ciebie w niej czeka, powoli ruszyłeś przed siebie. Jednak ze zdziwieniem zauważasz, że tam, gdzie wcześniej były płyty z pyskiem wilka teraz jest zupełnie normalna podłoga. Nie ma nawet śladu Wilkołaka. Uspokojony przechodzisz szybkim krokiem całe pomieszczenie i po jakimś czasie docierasz do jego przeciwległego końca. Tutaj ponownie wędrujesz korytarzem w kierunku placu ze statuą. Po pewnym okresie docierasz do posągu a następnie wybierasz drogę do znanej ci już części labiryntu. Kiedy ruszyłeś w drogę powrotną, doznałeś niejasnego przeczucia że o czymś zapomniałeś. Przystanąłeś na chwilę zastanawiając się. Już po chwili orientujesz się o co chodzi ? zgubiłeś gdzieś fragment mapki. Po chwili dochodzisz do wniosku, że i tak na razie nie będzie ci zupełnie potrzebna. Ruszasz z powrotem w swoją drogę, by po jakimś czasie dojść do dobrze znanej ci części labiryntu. Postanawiasz wrócić do miasta aby nieco odpocząć oraz doprowadzić się do porządku. Kierujesz się w stronę wyjścia z labiryntu a następnie wychodzisz na gwarne ulice miasta.', 'pl'),
(152, 3, 'grid.php', 'answer3', '0', 'Kiedy wypowiedziałeś słowo <i>Żywiołak</i> napis na ścianie zajaśniał na moment a następnie zniknął. Delikatnie skierowałeś dłoń w kierunku klejnotu. Tym razem bez problemu udało ci się go chwycić. Bariera zniknęła. Stwierdzasz, że kamień jest o dziwo ciepły w dotyku. Przyglądając mu się uważnie, nabierasz pewności, że idealnie pasowałby do napisu na posągu. Uradowany znaleziskiem postanawiasz wrócić i sprawdzić swoją teorię.', 'pl'),
(153, 3, 'grid.php', 'answernext', '0', 'Wracasz z powrotem do pomieszczenia z posągiem. Doszedłeś do skrzyżowania a następnie wróciłeś do olbrzymiej sali. Niepewny co ciebie w niej czeka, powoli ruszyłeś przed siebie. Jednak ze zdziwieniem zauważasz, że tam, gdzie wcześniej były płyty z pyskiem wilka teraz jest zupełnie normalna podłoga. Nie ma nawet śladu Wilkołaka. Uspokojony przechodzisz szybkim krokiem całe pomieszczenie i po jakimś czasie docierasz do jego przeciwległego końca. Tutaj ponownie wędrujesz korytarzem w kierunku placu ze statuą. Kiedy dochodzisz do placu, ostrożnie wyjmujesz z kieszeni klejnot, przyklękasz przy posągu i próbujesz dopasować go do litery <b>o</b> w napisie. Okazuje się że pasuje idealnie. Od lewej ściany dobiega głośny zgrzyt. Oglądając się w tę stronę, zauważasz, że kawałek ściany odsunął się na bok, odsłaniając nieznany ci korytarz. Ostrożnie podchodzisz do jego wejścia. W odróżnieniu od wszystkich korytarzy jakimi do tej pory wędrowałeś po labiryncie, ten jest oświetlony pochodniami. Zdziwiony tym znaleziskiem przyglądasz się uważniej jednej z pochodni. Jest wtopiona w ścianę, a jej płomień, okazuje się być magiczny ? nie wydziela ani odrobiny ciepła. Ściany korytarza wykonane są z szarych, niewielkich kamieni, podobnie jak jego podłoga. Sam korytarz jest bardzo wąski i dość niski. Ostrożnie udajesz się przed siebie. W korytarzu nie ma ani śladu kurzu czy wilgoci, tak jakby ktoś cały czas dbał o ten obszar. Po jakimś czasie docierasz do skrzyżowania w kształcie litery T. Którą drogę wybierasz?', 'pl'),
(154, 3, 'grid.php', 'box7', '1', 'Korytarz w prawo', 'pl'),
(155, 3, 'grid.php', 'box7', '2', 'Korytarz w lewo', 'pl'),
(156, 3, 'grid.php', 'box7', '3', 'Powrotną', 'pl'),
(157, 3, 'grid.php', '1.4.3', '0', 'Postanawiasz zostawić w spokoju korytarz wraz z jego tajemnicami i zawrócić do znanej ci części labiryntu. Kiedy ruszyłeś w drogę powrotną, doznałeś niejasnego przeczucia że o czymś zapomniałeś. Przystanąłeś na chwilę zastanawiając się. Już po chwili orientujesz się o co chodzi ? zgubiłeś gdzieś fragment mapki. Po chwili dochodzisz do wniosku, że i tak na razie nie będzie ci zupełnie potrzebna. Ruszasz z powrotem w swoją drogę, by po jakimś czasie dojść do dobrze znanej ci części labiryntu. Postanawiasz wrócić do miasta aby nieco odpocząć oraz doprowadzić się do porządku. Kierujesz się w stronę wyjścia z labiryntu a następnie wychodzisz na gwarne ulice miasta.', 'pl'),
(158, 3, 'grid.php', '1.4.2', '0', 'Korytarz przez pewien czas prowadzi prosto. Po jakimś czasie widzisz, że kończy się okutymi drzwiami. Kiedy naciskasz klamkę drzwi z cichym skrzypieniem otwierają się. Ostrożnie zaglądasz do środka. Ze zdumieniem widzisz przed sobą niewielkie pomieszczenie w którym znajduje się kilka regałów z książkami. Całość podobnie jak korytarz oświetlona jest magicznymi pochodniami. Powoli wchodzisz do komnaty. Ciekawość popycha cię do zbadania co kryją owe księgi. Prawie wszystkie traktują o alchemii. Większość z nich jest napisana w jakimś nie znanym ci języku. Jednak znajdujesz kilka interesujących pozycji zrozumiałych dla ciebie. Kiedy próbujesz je wynieść z sali, okazuje się, że nie możesz wyjść ? widocznie tych ksiąg nie da się stąd zabrać. Czy chcesz poświęcić nieco czasu na ich przeczytanie?', 'pl'),
(159, 3, 'grid.php', 'box8', '1', 'Tak', 'pl'),
(160, 3, 'grid.php', 'box8', '2', 'Nie', 'pl'),
(161, 3, 'grid.php', '1.4.2.2', '0', 'Postanawiasz zostawić księgi wraz z ich tajemnicami w spokoju. Wychodzisz z pomieszczenia i z powrotem wracasz korytarzem w kierunku placu z posągiem. Po jakimś czasie dochodzisz do posągu. Kiedy tylko wchodzisz na plac, usłyszałeś za plecami zgrzyt i ściana kryjąca tajemniczy korytarz zamknęła się za tobą. Kierujesz się do znanej ci już części labiryntu. Kiedy ruszyłeś w drogę powrotną, doznałeś niejasnego przeczucia że o czymś zapomniałeś. Przystanąłeś na chwilę zastanawiając się. Już po chwili orientujesz się o co chodzi ? zgubiłeś gdzieś fragment mapki. Po chwili dochodzisz do wniosku, że i tak na razie nie będzie ci zupełnie potrzebna. Ruszasz z powrotem w swoją drogę, by po jakimś czasie dojść do dobrze znanej ci części labiryntu. Postanawiasz wrócić do miasta aby nieco odpocząć oraz doprowadzić się do porządku. Kierujesz się w stronę wyjścia z labiryntu a następnie wychodzisz na gwarne ulice miasta.', 'pl'),
(162, 3, 'grid.php', '1.4.2.1', '0', 'Bierzesz pierwszą z interesujących cię ksiąg do ręki, siadasz sobie wygodnie na ziemi i zaczynasz czytać. Oświetlenie jest tutaj dość dobre, więc bez problemu odkrywasz zawartość księgi. Traktuje ona o różnych technikach wykonywania mikstur oraz o pracy alchemika. Czytając ją dowiadujesz się wielu nowych rzeczy o alchemii. W ten sposób zdobywasz 2 poziomy umiejętności alchemii. Po jakimś czasie poczułeś się zmęczony i postanowiłeś wrócić do miasta. Wychodzisz z pomieszczenia i z powrotem wracasz korytarzem w kierunku placu z posągiem. Po jakimś czasie dochodzisz do posągu. Kiedy tylko wchodzisz na plac, usłyszałeś za plecami zgrzyt i ściana kryjąca tajemniczy korytarz zamknęła się za tobą. Kierujesz się do znanej ci już części labiryntu. Kiedy ruszyłeś w drogę powrotną, doznałeś niejasnego przeczucia że o czymś zapomniałeś. Przystanąłeś na chwilę zastanawiając się. Już po chwili orientujesz się o co chodzi ? zgubiłeś gdzieś fragment mapki. Po chwili dochodzisz do wniosku, że i tak na razie nie będzie ci zupełnie potrzebna. Ruszasz z powrotem w swoją drogę, by po jakimś czasie dojść do dobrze znanej ci części labiryntu. Postanawiasz wrócić do miasta aby nieco odpocząć oraz doprowadzić się do porządku. Kierujesz się w stronę wyjścia z labiryntu a następnie wychodzisz na gwarne ulice miasta.', 'pl'),
(163, 3, 'grid.php', '1.4.1', '0', 'Korytarz prowadzi przez pewien czas prosto, by po jakimś czasie kończyć się wejściem do niewielkiej komnaty. Ostrożnie wchodzisz do środka sprawdzając czy w okolicy nie ma żadnych pułapek. Ściany pokryte są starożytnymi rysunkami przedstawiającymi różne wymarłe już istoty oraz bestie. Mimo iż wszędzie do tej pory na podłodze spoczywała warstwa kurzu w tym pomieszczeniu nie ma go ani trochę, tak jakby ktoś opiekował się tym miejscem. W jednym z rogów komnaty widzisz niewielki, podłużny przedmiot leżący na ziemi. Kiedy podchodzisz bliżej, stwierdzasz że jest to najdziwniejsza skrzynia jaką widziałeś. Wygląda jak wykonana z jednego kawałka bardzo ciemnego drewna. Nigdzie nie widzisz nawet śladów zamka czy zawiasów. Jedynie na wieku dostrzegasz jakieś dziwne zadrapania. Kiedy delikatnie dotykasz tego miejsca, nagle zadrapania zaczynają świecić by po chwili w magiczny sposób ułożyć się w zrozumiały dla ciebie napis. Czy chcesz próbować rozwiązać zagadkę?', 'pl'),
(164, 3, 'grid.php', '1.4.1.2', '0', 'Postanawiasz zostawić zagadkę skrzyni nie rozwiązaną. Wychodzisz z pomieszczenia i z powrotem wracasz korytarzem w kierunku placu z posągiem. Po jakimś czasie dochodzisz do posągu. Kiedy tylko wchodzisz na plac, usłyszałeś za plecami zgrzyt i ściana kryjąca tajemniczy korytarz zamknęła się za tobą. Kierujesz się do znanej ci już części labiryntu. Kiedy ruszyłeś w drogę powrotną, doznałeś niejasnego przeczucia że o czymś zapomniałeś. Przystanąłeś na chwilę zastanawiając się. Już po chwili orientujesz się o co chodzi ? zgubiłeś gdzieś fragment mapki. Po chwili dochodzisz do wniosku, że i tak na razie nie będzie ci zupełnie potrzebna. Ruszasz z powrotem w swoją drogę, by po jakimś czasie dojść do dobrze znanej ci części labiryntu. Postanawiasz wrócić do miasta aby nieco odpocząć oraz doprowadzić się do porządku. Kierujesz się w stronę wyjścia z labiryntu a następnie wychodzisz na gwarne ulice miasta.', 'pl'),
(165, 3, 'grid.php', '1.4.1.1', 'jajko', 'Pochylając się nad skrzynią dostrzegasz wyraźnie napis na niej. Głosi on:<br /><i>Skrzynka bez zawiasów, klucza i pokrywy<br />Lecz złocisty w środku skarb kryje prawdziwy</i><br />Domyślasz się, że aby otworzyć skrzynię, musisz odpowiedzieć na zagadkę. Obok napisu dostrzegasz jeszcze 5 niewielkich zadrapań.', 'pl'),
(166, 3, 'grid.php', 'answer4', '0', 'Kiedy wypowiadałeś odpowiedź, zauważyłeś że jedna kreska zniknęła a mimo to skrzynia ani drgnęła. Więc to chyba była zła odpowiedź. Zaczynasz zastanawiać się nad kolejną.', 'pl'),
(167, 3, 'grid.php', 'answer5', '0', 'Kiedy wypowiedziałeś po raz piąty słowo będące twoim zdaniem rozwiązaniem tej zagadki, ostatnia kreska zniknęła, napis pojaśniał by po chwili ponownie przemienić się w zadrapania. Próbowałeś jeszcze parę razy przywrócić go, ale niestety okazało się to niemożliwe. Zrezygnowany postanowiłeś powrócić do miasta. Wychodzisz z pomieszczenia i z powrotem wracasz korytarzem w kierunku placu z posągiem. Po jakimś czasie dochodzisz do posągu. Kiedy tylko wchodzisz na plac, usłyszałeś za plecami zgrzyt i ściana kryjąca tajemniczy korytarz zamknęła się za tobą. Kierujesz się do znanej ci już części labiryntu. Kiedy ruszyłeś w drogę powrotną, doznałeś niejasnego przeczucia że o czymś zapomniałeś. Przystanąłeś na chwilę zastanawiając się. Już po chwili orientujesz się o co chodzi ? zgubiłeś gdzieś fragment mapki. Po chwili dochodzisz do wniosku, że i tak na razie nie będzie ci zupełnie potrzebna. Ruszasz z powrotem w swoją drogę, by po jakimś czasie dojść do dobrze znanej ci części labiryntu. Postanawiasz wrócić do miasta aby nieco odpocząć oraz doprowadzić się do porządku. Kierujesz się w stronę wyjścia z labiryntu a następnie wychodzisz na gwarne ulice miasta.', 'pl'),
(168, 3, 'grid.php', 'answer6', '0', 'Kiedy wypowiedziałeś słowo <i>jajko</i>, na moment cały napis zaczął mocniej świecić by po chwili zgasnąć. W tym momencie bezszelestnie uniosło się wieko skrzyni. Kiedy zajrzałeś do środka, zauważyłeś na dnie kawałek starego pergaminu. Ostrożnie podnosząc go, stwierdzasz że to kawałek starożytnej mapy! Delikatnie chowasz go do plecaka i wyruszasz w drogę powrotną. Wychodzisz z pomieszczenia i z powrotem wracasz korytarzem w kierunku placu z posągiem. Po jakimś czasie dochodzisz do posągu. Kiedy tylko wchodzisz na plac, usłyszałeś za plecami zgrzyt i ściana kryjąca tajemniczy korytarz zamknęła się za tobą. Kierujesz się do znanej ci już części labiryntu. Kiedy ruszyłeś w drogę powrotną, doznałeś niejasnego przeczucia że o czymś zapomniałeś. Przystanąłeś na chwilę zastanawiając się. Już po chwili orientujesz się o co chodzi ? zgubiłeś gdzieś fragment mapki. Po chwili dochodzisz do wniosku, że i tak na razie nie będzie ci zupełnie potrzebna. Ruszasz z powrotem w swoją drogę, by po jakimś czasie dojść do dobrze znanej ci części labiryntu. Postanawiasz wrócić do miasta aby nieco odpocząć oraz doprowadzić się do porządku. Kierujesz się w stronę wyjścia z labiryntu a następnie wychodzisz na gwarne ulice miasta.', 'pl'),
(169, 3, 'grid.php', 'escape', '0', 'Rzuciłeś się do panicznej ucieczki. Przez pewien czas słyszysz za sobą powarkiwanie potwora próbującego cię ścigać, jednak strach dodaje ci skrzydeł. Powoli powarkiwanie cichnie, jednak dla pewności biegniesz tak długo aż docierasz do placu z posągiem. Powoli zmierzasz do znanej już ci części labiryntu. Kiedy ruszyłeś w drogę powrotną, doznałeś niejasnego przeczucia że o czymś zapomniałeś. Przystanąłeś na chwilę zastanawiając się. Już po chwili orientujesz się o co chodzi ? zgubiłeś gdzieś fragment mapki. Po chwili dochodzisz do wniosku, że i tak na razie nie będzie ci zupełnie potrzebna. Ruszasz z powrotem w swoją drogę, by po jakimś czasie dojść do dobrze znanej ci części labiryntu. Postanawiasz wrócić do miasta aby nieco odpocząć oraz doprowadzić się do porządku. Kierujesz się w stronę wyjścia z labiryntu a następnie wychodzisz na gwarne ulice miasta.', 'pl'),
(170, 3, 'grid.php', 'box9', '1', 'Korytarz w lewo', 'pl'),
(171, 3, 'grid.php', 'box9', '2', 'Zawrócić do miasta', 'pl'),
(172, 4, 'grid.php', 'start', '0', 'Wędrując korytarzami labiryntu dotarłeś do jego części, która nie jest ci dokładnie znana. Zwiększasz więc czujność i ruszasz przed siebie. Na pierwszy rzut oka, ten fragment labiryntu nie różni się za bardzo od tych którymi wcześniej poruszałeś się. Po jakimś czasie zauważasz po wschodniej stronie  okute żelazem, dębowe drzwi. Mimo iż pewnie zostały wykonane bardzo dawno temu, nadal sprawiają wrażenie solidnych. Ciekawe jaka tajemnica kryje się za nimi. Co postanawiasz?', 'pl'),
(173, 4, 'grid.php', 'box1', '1', 'Otworzyć drzwi', 'pl'),
(174, 4, 'grid.php', 'box1', '2', 'Iść dalej', 'pl'),
(175, 4, 'grid.php', '2', '0', 'Postanowiłeś zostawić zagadkowe drzwi w spokoju. Przez chwilę odpoczywałeś a następnie, zebrałeś swój ekwipunek i ponownie ruszyłeś w drogę. Przez jakiś czas wędrowałeś korytarzami ale niestety nie natrafiłeś na nic bardziej interesującego niż kurz na podłodze.', 'pl'),
(176, 4, 'grid.php', '1', '0', 'Ostrożnie chwytasz za klamkę i otwierasz drzwi. Nieco skrzypią ze starości podczas otwierania. Wydaje ci się, jakby ten dźwięk rozchodził się na wszystkie strony daleko w głąb labiryntu. Uchylając lekko drzwi zaglądasz co się za nimi kryje. Przed sobą widzisz niewielką komnatę z której wychodzą jeszcze trzy korytarze: na południe, zachód oraz północ. Otwierasz drzwi szerzej i ostrożnie wchodzisz do środka pomieszczenia. Przez chwilę nasłuchujesz czy nie dobiegają jakieś odgłosy z owych korytarzy. Następnie postanawiasz:', 'pl'),
(177, 4, 'grid.php', 'box2', '1', 'Iść na południe', 'pl'),
(178, 4, 'grid.php', 'box2', '2', 'Iść na północ', 'pl'),
(179, 4, 'grid.php', 'box2', '3', 'Iść na zachód', 'pl'),
(180, 4, 'grid.php', 'box2', '4', 'Zawrócić', 'pl'),
(181, 4, 'grid.php', '1.4', '0', 'Postanawiasz zostawić zagadki korytarzy nierozwiązane. Zbierasz więc swój ekwipunek i wychodzisz przez drzwi. Przez jakiś czas wędrowałeś korytarzami ale niestety nie natrafiłeś na nic bardziej interesującego niż kurz na podłodze. Stwierdziłeś, że masz na razie dość zwiedzania labiryntu i skierowałeś się ku wyjściu z niego. Po jakimś czasie docierasz do bramy, przekraczasz ją i ponownie wchodzisz na ulice miasta.', 'pl'),
(182, 4, 'grid.php', '1.1', '0', 'Kierujesz się do korytarza prowadzącego na południe. Korytarz wygląda tak samo jak te, którymi wędrowałeś wcześniej, na podłodze znajduje się niewielka warstwa kurzu która zmienia się w małe obłoczki kiedy stawiasz na niej kroki. Dookoła ciebie panuje całkowita cisza, wydaje ci się, że odgłos twoich kroków niesie się daleko przed ciebie. Ostrożnie wędrujesz korytarzem przyglądając się jego ścianom i podłodze w poszukiwaniu pułapek i innych niespodzianek. Idziesz tak przez pewien czas, aż w końcu przed sobą widzisz rozgałęzienie korytarza w kształcie litery T. Prowadzi on teraz na wschód i zachód. Ostrożnie przyglądasz się obu drogą zastanawiając się, którą wybrać.', 'pl'),
(183, 4, 'grid.php', 'box3', '1', 'Iść na zachód', 'pl'),
(184, 4, 'grid.php', 'box3', '2', 'Iść na wschód', 'pl'),
(185, 4, 'grid.php', 'box3', '3', 'Zawrócić', 'pl'),
(186, 4, 'grid.php', '1.1.3', '0', 'Postanawiasz zawrócić z drogi i ruszyć w inną część labiryntu. Wracasz więc z powrotem do pomieszczenia z drzwiami. Tutaj na moment przystajesz aby nieco odpocząć. Po jakimś czasie zbierasz swój ekwipunek i przechodzisz przez drzwi i kierujesz się z powrotem na znaną ci drogę. Po jakimś czasie docierasz do znanej już ci części labiryntu. Kierujesz się do wyjścia z labiryntu. Dochodzisz do niego a następnie opuszczasz tę lokację wychodząc na gwarne ulice miasta.', 'pl'),
(187, 4, 'grid.php', '1.1.1', '0', 'Postanawiasz zbadać odnogę prowadzącą na zachód. Powoli idąc korytarzem zwracasz uwagę na każdy szczegół, przygotowany na niespodzianki. Korytarz prowadzi cały czas prosto przed siebie. Wykonany jest z kamieni łączonych zaprawą. Ostrożnie badasz okolicę, co jakiś czas przystając i nasłuchując czy z naprzeciwka nie dobiegają jakieś odgłosy. Jednak otacza cię martwa cisza. Po jakimś czasie korytarz zmienia się w niewielkie pomieszczenie szerokie i długie na kilka kroków. Dokładnie badasz okolicę. Przy wschodniej ścianie pomieszczenia zauważasz leżący na ziemi brązowy sztylet. Jego rękojeść pokryta jest różnymi wzorami oraz kamieniami szlachetnymi, dzięki którym wzrasta nieco wartość owego przedmiotu. Chowasz znalezisko do kieszeni i postanawiasz wrócić. Docierasz do rozwidlenia i znów stajesz przed wyborem.', 'pl'),
(188, 4, 'grid.php', 'box4', '1', 'Iść na wschód', 'pl'),
(189, 4, 'grid.php', 'box4', '2', 'Zawrócić', 'pl'),
(190, 4, 'grid.php', '1.1.2', '0', 'Po chwili zastanowienia kierujesz się we wschodnią odnogę korytarza. Prowadzi on cały czas prosto, delikatnie opadając w dół. Ściany oraz podłoga, podobnie jak i pozostałych korytarzy wykonane są z kamiennych płyt. Każdy twój krok wzbija niewielkie obłoczki kurzu, który zalega na podłodze. Jednak ciebie interesuje przede wszystkim czy w okolicy nie ma pułapek lub innych niespodzianek. Ostrożnie rozglądając się na boki idziesz cały czas przed siebie. Po jakimś czasie wędrówki, widzisz że kawałek drogi przed tobą zapadł się fragment korytarza. Szczelina nie jest zbyt duża, raptem kilka kroków, jeżeli chcesz, możesz spróbować ją przeskoczyć.', 'pl'),
(191, 4, 'grid.php', 'box5', '1', 'Spróbować przeskoczyć', 'pl'),
(192, 4, 'grid.php', 'box5', '2', 'Wrócić', 'pl'),
(193, 4, 'grid.php', '1.1.2.2', '0', 'Postanawiasz nie ryzykować i wrócić drogą którą przyszedłeś. Ruszasz więc z powrotem w kierunku rozwidlenia korytarza. Wiedząc już, że droga jest bezpieczna poruszasz się znacznie szybciej. Po jakimś czasie ponownie docierasz do skrzyżowania. Następnie kierujesz się w stronę pomieszczenia z drzwiami. Kiedy do niego docierasz zaczynasz zastanawiać się, w którą stronę teraz iść.', 'pl'),
(194, 4, 'grid.php', '1.1.2.1', '0', 'Postanawiasz spróbować swoich sił i przeskoczyć ową szczelinę. Cofasz się kilkanaście kroków od dziury, chwilę odpoczywasz a następnie bierzesz rozbieg i skaczesz...', 'pl'),
(195, 4, 'grid.php', 'jump1', '0', 'Niestety przeliczyłeś się z siłami - już w połowie skoku zdałeś sobie sprawę że nie dasz rady dosięgnąć drugiego końca szczeliny. Z przerażeniem spoglądasz w dół widząc pod nogami zdawać by się mogło bezdenną pustkę. Po chwili krzycząc ze strachu lecisz w dół przepaści. Ostatnim widokiem jaki pamiętasz, to bardzo szybko zbliżająca się kamienna posadzka. Silne uderzenie w kamienny korytarz położony wiele kroków poniżej gwałtownie przerywa twoje badanie labiryntu. Po jakimś czasie budzisz się w szpitalu w city1a.', 'pl'),
(196, 4, 'grid.php', 'jump2', '0', 'Już w połowie skoku zdobyłeś całkowitą pewność iż uda ci się wylądować po drugiej stronie przepaści. Twardo wylądowałeś po przeciwnej stronie przepaści. Przez chwilę odpoczywałeś po wysiłku fizycznym, by następnie ruszyć dalej na zbadanie korytarza. Ta jego część niczym nie różni się od wcześniejszej, nadal korytarz prowadzi lekko w dół. Ostrożnie badasz okolicę w poszukiwaniu niespodzianek.', 'pl'),
(197, 4, 'grid.php', 'int1', '0', 'Po jakimś czasie wędrówki okazuje się że to ślepy korytarz. Tyle wysiłku na marne. Mimo wszystko dokładnie badasz okolicę w poszukiwaniu ukrytych przejść. Niestety mimo iż dokładnie przebadałeś całą okolicę nic nie znajdujesz. Zrezygnowany zawracasz w kierunku rozwidlenia.', 'pl'),
(198, 4, 'grid.php', 'int2', '0', 'Po jakimś czasie wędrówki docierasz do końca korytarza. Uważnie przyglądasz się przez moment wschodniej ścianie. Coś ci nie pasuje w jej wyglądzie. Mimo że wszystkie twoje zmysły mówią ci że jest to solidna skała, twój rozum podpowiada że jest coś nie tak. Postanawiasz ostrożnie ją zbadać. Kiedy przesuwasz delikatnie rękę po ścianie, ta nagle bez najmniejszego oporu zagłębia się w ścianie. Część kamieni to iluzja! Czując dziwne mrowienie na karku ostrożnie przechodzisz przez iluzję. Przed sobą widzisz że korytarz prowadzi dalej. Dokładnie badając go krok po kroku kierujesz się przed siebie. Po jakimś czasie znów dochodzisz do końca tunelu. Nauczony doświadczeniem badasz dokładnie czy nie ma gdzieś ukrytych przejść. Niestety okazuje się że tym razem to naprawdę koniec wędrówki w tę stronę. Zrezygnowany postanawiasz zawrócić do rozwidlenia. Ponieważ znasz już drogę, tym razem nieco szybciej docierasz do iluzorycznej ściany. Przekraczasz ją i wracasz tą samą drogą do rozwidlenia.', 'pl'),
(199, 4, 'grid.php', '1.1.2.1next', '0', 'Znów dochodzisz do rozpadliny ale tym razem już bez problemu udaje ci się przeskoczyć na drugą stronę. Ta część korytarza jest ci doskonale znana, dlatego poruszasz się po niej znacznie szybciej niż ostatnio. Po jakimś czasie ponownie docierasz do skrzyżowania, a następnie kierujesz się do pomieszczenia z drzwiami. Kiedy docierasz na miejsce zaczynasz zastanawiać się, którą drogę teraz wybrać?', 'pl'),
(200, 4, 'grid.php', '1.2', '0', 'Po chwili zastanowienia wybierasz drogę na północ. Korytarz podobnie jak te którymi poprzednio wędrowałeś wykonany jest z kamieni, ma szerokość 3 kroków i wysokość 5, cały czas prowadzi prosto. Idziesz przed siebie, czujnie obserwując okolicę w poszukiwaniu niespodzianek, co jakiś czas zatrzymujesz się i nasłuchujesz czy nie dobiegają jakieś podejrzane dźwięki z korytarza. Po jakimś czasie wędrówki, docierasz do rozwidlenia korytarza w kształcie litery T. Zastanawiasz się w którą stronę się udać.', 'pl'),
(201, 4, 'grid.php', 'box6', '1', 'Iść na wschód', 'pl'),
(202, 4, 'grid.php', 'box6', '2', 'Iść na zachód', 'pl'),
(203, 4, 'grid.php', 'box6', '3', 'Wrócić', 'pl'),
(204, 4, 'grid.php', '1.2.3', '0', 'Postanowiłeś zawrócić do drzwi - nie interesuje cię za bardzo co kryją owe korytarze. Przez chwilę odpoczywałeś a następnie pewnym krokiem, znanym już ci korytarzem zawróciłeś do komnaty z drzwiami.', 'pl'),
(205, 4, 'grid.php', 'box7', '1', 'Iść na zachód', 'pl'),
(206, 4, 'grid.php', 'box7', '2', 'Zawrócić', 'pl'),
(207, 4, 'grid.php', 'box8', '1', 'Iść na północ', 'pl'),
(208, 4, 'grid.php', 'box8', '2', 'Iść na zachód', 'pl'),
(209, 4, 'grid.php', 'box8', '3', 'Zawrócić', 'pl'),
(210, 4, 'grid.php', '1.2.1', '0', 'Początkowo korytarz wygląda tak samo jak wszystkie inne, którymi do tej pory podróżowałeś. Jednak po pewnym czasie zauważasz, że zaczyna powoli zmieniać się ze sztucznie wykonanego korytarza w naturalny. Ostrożnie badając drogę przed sobą powoli odkrywasz przed sobą drogę. Po jakimś czasie zaczynasz natrafiać na stosy kamieni na swojej drodze. W pewnym momencie, przystanąłeś na moment i zacząłeś nasłuchiwać. Wydaje ci się, że słyszysz ciche piszczenie dobiegające z przodu. Ze zdwojoną uwagą posuwasz się do przodu. Owe popiskiwania nasilają się. W pewnym momencie zauważasz tuż przy podłodze dziesięć niewielkich czerwonych punkcików. Nagle owe punkciki zaczynają poruszać się w twoim kierunku! Kiedy wpadają w krąg światła twojej pochodni, zamieniają się w pięć szczurów biegnących w twoim kierunku.', 'pl'),
(211, 4, 'grid.php', 'lostfight1', '0', 'Próbowałeś stawić opór gryzoniom, ale niestety było to ponad twoje siły. Trucizna płynąca w ranach zadanych przez nie, powoli pozbawiła cię sił. Nagle cały świat zawirował ci przed oczami i poczułeś że nogi się pod tobą uginają. Ostatnim obrazem jaki widziałeś, to morda szczura zbliżająca się do twojej szyi. Po tym wydarzeniu nastąpiła całkowita ciemność.', 'pl'),
(212, 4, 'grid.php', 'winfight1', '0', 'Bez problemu wygniotłeś wszystkie gryzonie. Więcej strachu się najadłeś niż wyrządziły ci jakiś szkód. Przez chwilę odpoczywasz po tym wydarzeniu. Następnie zbierasz swoje rzeczy i ponownie wyruszasz na zbadanie korytarza.', 'pl'),
(213, 4, 'grid.php', '1.2.1next', '0', 'Kiedy ostrożnie robisz kilka kroków, znów na moment przystajesz i zaczynasz nasłuchiwać. Wydaje ci się, że gdzieś przed tobą znów słychać odgłosy popiskiwania. Ostrożnie skradając się idziesz przed siebie, uważnie wsłuchując się w dźwięki dobiegające z przodu. Wydaje ci się że zbliżasz się do ich źródła. Korytarz zaczyna nieco skręcać w lewo, zmniejszając swoje gabaryty. Piszczenie przed tobą cały czas narasta. Po drodze co jakiś czas widzisz stare kości porozrzucane po całym korytarzu. Wędrujesz tak przez pewien czas, uważnie obserwując okolicę. W pewnym momencie widzisz że korytarz przed tobą kończy się, zamieniając w małą, półokrągłą podziemną jaskinię. Jednak nie zwracasz na to uwagi, ponieważ zauważasz że z owej komnaty wypada piętnaście szczurów biegnących w twoim kierunku.', 'pl'),
(214, 4, 'grid.php', 'winfight2', '0', 'Bez problemu wygniotłeś wszystkie gryzonie. Więcej strachu się najadłeś niż wyrządziły ci jakiś szkód. Przez chwilę odpoczywasz po tym wydarzeniu. Następnie zabierasz się za przeszukiwanie pomieszczenia. Przez jakiś czas przerzucasz sterty starych kości i innych śmieci leżących na podłodze. Wśród nich znajdujesz porozrzucane złote monety, które natychmiast zbierasz. W ten sposób uzbierałeś 200 sztuk złota. Po jakimś czasie zdobywasz pewność, że nic więcej nie znajdziesz tutaj. Zbierasz więc swój ekwipunek i ruszasz w drogę powrotną. Powrót znaną ci już drogą zajmuje znacznie mniej czasu. Po drodze omijasz sterty kamieni leżące na podłodze i ponownie wkraczasz na obszar labiryntu. Tym samym korytarzem dochodzisz do rozwidlenia. Którą drogę wybierasz?', 'pl'),
(215, 4, 'grid.php', 'box9', '1', 'Iść na zachód', 'pl'),
(216, 4, 'grid.php', 'box9', '2', 'Wrócić', 'pl'),
(217, 4, 'grid.php', '1.2.2', '0', 'Korytarz który wybrałeś co chwila skręca to w prawo to w lewo. Ostrożnie wędrujesz przed siebie bacznie zwracając uwagę na wszystkie szczegóły dookoła ciebie. Co jakiś czas mijasz niewielkie boczne odnogi korytarza. Za każdym razem uważnie przyglądasz się im i nasłuchujesz czy nie ma jakiś niebezpieczeństw. Niektóre na wszelki wypadek sprawdzasz czy nie kryją jakiś skarbów. Niestety nie ma w nich nic ciekawego. Idziesz jakiś czas, nie natrafiając na cokolwiek interesującego. Korytarz jest dość wąski, z ledwością zmieściłyby się w nim dwie osoby obok siebie. W pewnym momencie, kiedy zbliżałeś się do kolejnej odnogi usłyszałeś przed sobą odgłos toczącego się kamyka. Błyskawicznie zwróciłeś się w tę stronę i zobaczyłeś wychodzącego z bocznego korytarza, nie mniej zdziwionego niż ty Goblina! Co robisz?', 'pl'),
(218, 4, 'grid.php', 'box10', '1', 'Atak', 'pl'),
(219, 4, 'grid.php', 'box10', '2', 'Ucieczka', 'pl'),
(220, 4, 'grid.php', 'box10', '3', 'Przyjrzyj się', 'pl'),
(221, 4, 'grid.php', '1.2.2.2', '0', 'Natychmiast odwróciłeś się i zacząłeś biec w kierunku z którego przyszedłeś. Nie oglądając się za ramię szybko przemierzasz znaną ci już drogę. Po jakimś czasie zmęczony przystanąłeś na moment. Odwracając się, nie zauważyłeś pogoni. Przez chwilę odpoczywałeś a następnie zacząłeś badać dokładnie rozgałęzienia korytarza, cały czas kierując się w kierunku drzwi. Niestety nie znalazłeś nic ciekawego. Po pewnym czasie z powrotem docierasz do pomieszczenia z drzwiami.', 'pl'),
(222, 4, 'grid.php', '1.2.2.1', '0', 'Bez chwili zastanowienia rzucasz się w kierunku potwora, ten również natychmiast wyciąga broń i rusza z dzikim wrzaskiem na ciebie. Rozpoczyna się walka.', 'pl'),
(223, 4, 'grid.php', 'lostfight2', '0', 'Zaatakowanie stwora nie było zbyt rozsądnym pomysłem - okazał się w cale sprawnym szermierzem. Bez problemu odbił wszystkie twoje ataki a następnie sam zaatakował. Desperacko próbowałeś bronić się, jednak z przerażenia zacząłeś popełniać błędy. Ostatnią rzeczą jaką widziałeś, to spadająca na twoją głowę goblińska szabla. Potem już nastąpiła całkowita ciemność.', 'pl'),
(224, 4, 'grid.php', 'winfight3', '0', 'Bez trudu przebiłeś słabą obronę przeciwnika. Po kilku chwilach walki potwór z jękiem osunął się martwy na ziemię. Przez chwilę odpoczywałeś a następnie zebrałeś swój ekwipunek i ponownie wyruszyłeś na badanie korytarza.', 'pl'),
(225, 4, 'grid.php', '1.2.2.1next', '0', 'Wyruszyłeś znów przed siebie uważnie badając korytarz. Wędrujesz tak przez jakiś czas niestety nie natrafiając na nic ciekawego. Po jakimś czasie docierasz do końca korytarza. Przeszukujesz ten fragment w poszukiwaniu tajnych przejść czy dźwigni ale nic nie znajdujesz. Postanawiasz zawrócić w kierunku z którego przyszedłeś. Teraz po drodze dodatkowo próbowałeś zbadać również odnogi korytarza. W pewnym momencie zdałeś sobie sprawę, że jest to prawie niemożliwe ? boczne tunele łączą się w skomplikowany system dróg, sprawiając że gubisz w nich orientację. Z ledwością trafiasz z powrotem do znanego ci już tunelu i kierujesz się bezpośrednio w kierunku skrzyżowania. Po pewnym czasie z powrotem docierasz do pomieszczenia z drzwiami.', 'pl'),
(226, 4, 'grid.php', '1.2.2.3', '0', 'Stoisz niepewnie, przyglądając się stworowi przed tobą. Jest nieco niższy od ciebie, ubrany z skórzaną zbroję, przy pasie zwisa mu typowa goblińska szabla. Przygląda ci się niepewnie, widzisz, że cały czas przygotowany jest do ataku. Przez jakiś czas patrzycie tak na siebie obaj niepewni co robić. W końcu Goblin delikatnie prostuje się i zachrypniętym głosem mówi:<br /><i>Hej</i><br />Co robisz?', 'pl'),
(227, 4, 'grid.php', 'box11', '1', 'Atakuję', 'pl'),
(228, 4, 'grid.php', 'box11', '2', 'Odpowiadam', 'pl'),
(229, 4, 'grid.php', 'talk1', '0', '<i>Hej</i><br />Odpowiadasz niepewnie, zdziwiony sytuacją <br /><i>Słuchaj, nie mam nic do ciebie, więc może pogadamy?</i> odpowiada Goblin', 'pl'),
(230, 4, 'grid.php', 'box12', '1', 'Tak', 'pl'),
(231, 4, 'grid.php', 'box12', '2', 'Nie', 'pl');
INSERT INTO `quests` (`id`, `qid`, `location`, `name`, `option`, `text`, `lang`) VALUES 
(232, 4, 'grid.php', 'talk2.2', '0', '<i>Dobra, niech ci będzie</i> - mówi Goblin. - <i>Proponuję aby każdy poszedł w swoją stronę</i><br />Przystajesz na ten warunek, ostrożnie odstępujecie od siebie, cały czas patrząc uważnie co robi druga strona. Kiedy jesteście już jakiś kawałek drogi od siebie, widzisz, że Goblin skręca w jeden z bocznych tuneli. Przez chwilę stoisz nasłuchując odgłosów jego kroków, które po jakimś czasie całkowicie cichną.', 'pl'),
(233, 4, 'grid.php', 'talk2.1', '0', '<i>Czego tu szukasz?</i> - pyta się Goblin.<br /><i>Tego i owego</i> - odpowiadasz cały czas nieufnie przyglądając się stworowi<br /><i>Słuchaj, mam dla ciebie propozycję, znalazłem jakąś dziwną głupią skrzynię z durnymi napisami na niej, za 100 sztuk złota zaprowadzę cię do niej, chcesz?</i>', 'pl'),
(234, 4, 'grid.php', 'box13', '1', 'Tak', 'pl'),
(235, 4, 'grid.php', 'box13', '2', 'Nie', 'pl'),
(236, 4, 'grid.php', 'talk3', '0', '<i>Dobra, pokaż kasę a potem pójdziemy</i>', 'pl'),
(237, 4, 'grid.php', 'gold1', '0', 'Przeszukujesz przez moment swoją sakiewkę ale nie znajdujesz w niej aż tylu sztuk złota. Goblin zdegustowany patrzy na ciebie.<br /><i>Nawet nie wiesz ile masz kasy?? Ech, daruj sobie. Proponuję aby każdy poszedł w swoją stronę</i><br />Przystajesz na ten warunek, ostrożnie odstępujecie od siebie, cały czas patrząc uważnie co robi druga strona. Kiedy jesteście już jakiś kawałek drogi od siebie, widzisz, że Goblin skręca w jeden z bocznych tuneli. Przez chwilę stoisz nasłuchując odgłosów jego kroków, które po jakimś czasie całkowicie cichną.', 'pl'),
(238, 4, 'grid.php', 'gold2', '0', 'Sprawdzasz swoją sakiewkę i pokazujesz 100 sztuk złota Goblinowi. Od razu zaświeciły mu się oczy.<br /><i>No, to rozumiem, dawaj za mną, tylko się nie zgub, hehe</i> Odwraca się i wchodzi z powrotem w korytarz z którego wcześniej wyszedł na twoją drogę.', 'pl'),
(239, 4, 'grid.php', 'talk3next', 'człowiek', 'Ostrożnie idziesz cały czas za nim, rozglądając się na boki w poszukiwaniu pułapek. Goblin pewnie i szybko prowadzi cię przez korytarze. Gdybyś szedł samemu tą drogą, szybko byś się zgubił w plątaninie korytarzy. Idziecie, nie odzywając się do siebie. Po pewnym czasie docieracie do małej komnaty. Goblin zatrzymuje się i odwraca do ciebie.<br /><i>Dobra to tutaj, dawaj teraz kasę</i><br />Wypłacasz Goblinowi jego 100 sztuk złota.<br /><i>Fajnie się robi z tobą interesy, sam se chyba trafisz do wyjścia. Żegnaj</i><br />Zabiera swoje złoto i szybko znika gdzieś w korytarzach. Przez chwilę stoisz nasłuchując odgłosów jego kroków, które po jakimś czasie całkowicie cichną. Ostrożnie wchodzisz do pomieszczenia i rozglądasz się na boki. W jednym z rogów komnaty widzisz niewielki, podłużny przedmiot leżący na ziemi. Kiedy podchodzisz bliżej, stwierdzasz że jest to najdziwniejsza skrzynia jaką widziałeś. Wygląda jak wykonana z jedengo kawałka bardzo ciemnego drewna. Nigdzie nie widzisz nawet śladów zamka czy zawiasów. Jedynie na wieku dostrzegasz jakieś dziwne zadrapania. Kiedy delikatnie dotykasz tego miejsca, nagle zadrapania zaczynają świecić by po chwili w magiczny sposób ułożyć się w zrozumiały dla ciebie napis. Pochylając się nad skrzynią dostrzegasz wyraźnie napis na niej. Głosi on:<br /><i>Jakie zwierzę chodzi o poranku na 4 nogach<br />W południe na 2<br />A wieczorem na 3?</i><br />Domyślasz się, że aby otworzyć skrzynię, musisz odpowiedzieć na pytanie. Obok napisu dostrzegasz jeszcze 5 niewielkich zadrapań.', 'pl'),
(240, 4, 'grid.php', 'answer1', '0', '<i>Jakie zwierzę chodzi o poranku na 4 nogach<br />W południe na 2<br />A wieczorem na 3?</i><br />Kiedy wypowiadałeś odpowiedź, zauważyłeś że jedna kreska zniknęła a mimo to skrzynia ani drgnęła. Więc to chyba była zła odpowiedź. Zaczynasz zastanawiać się nad kolejną.', 'pl'),
(241, 4, 'grid.php', 'answer2', '0', 'Kiedy wypowiedziałeś po raz piąty słowo będące twoim zdaniem rozwiązaniem tej zagadki, ostatnia kreska zniknęła, napis pojaśniał by po chwili ponownie przemienić się w zadrapania. Próbowałeś jeszcze parę razy przywrócić go, ale niestety okazało się to niemożliwe. Zrezygnowany postanowiłeś powrócić do miasta. Wracasz tymi samymi korytarzami którymi przyszedłeś tutaj wraz ze swoim dziwnym przewodnikiem. Parę razy omal się nie zgubiłeś ale po pewnym czasie wyszedłeś na znany ci już dobrze szlak i kierujesz się bezpośrednio do skrzyżowania. Po pewnym czasie z powrotem docierasz do pomieszczenia z drzwiami. Tutaj na moment przystajesz aby nieco odpocząć. Po jakimś czasie zbierasz swój ekwipunek i przechodzisz przez drzwi i kierujesz się z powrotem na znaną ci drogę. Po jakimś czasie docierasz do znanej już ci części labiryntu. Kierujesz się do wyjścia z labiryntu. Dochodzisz do niego a następnie opuszczasz tę lokację wychodząc na gwarne ulice miasta.', 'pl'),
(242, 4, 'grid.php', 'answer3', '0', 'Kiedy wypowiedziałeś słowo <i>Człowiek</i>, na moment cały napis zaczął mocniej świecić by po chwili zgasnąć. W tym momencie bezszelestnie uniosło się wieko skrzyni. Kiedy zajrzałeś do środka, zauważyłeś na dnie kawałek starego pergaminu. Ostrożnie podnosząc go, stwierdzasz że to kawałek starożytnej mapy! Delikatnie chowasz go do plecaka i wyruszasz w drogę powrotną. Wracasz tymi samymi korytarzami którymi przyszedłeś tutaj wraz ze swoim dziwnym przewodnikiem. Parę razy omal się nie zgubiłeś ale po pewnym czasie wyszedłeś na znany ci już dobrze szlak i kierujesz się bezpośrednio do skrzyżowania. Po pewnym czasie z powrotem docierasz do pomieszczenia z drzwiami. Tutaj na moment przystajesz aby nieco odpocząć. Po jakimś czasie zbierasz swój ekwipunek i przechodzisz przez drzwi i kierujesz się z powrotem na znaną ci drogę. Po jakimś czasie docierasz do znanej już ci części labiryntu. Po pewnym czasie wychodzisz na powierzchnię. Kiedy już się przyzwyczaiłeś do światła dnia, zauważasz że wszyscy przyglądają się tobie. Dopiero teraz widzisz jak bardzo jesteś zakurzony i oblepiony starymi pajęczynami. Chyba czas doprowadzić się do porządku.', 'pl'),
(243, 4, 'grid.php', '1.3', '0', 'Wybierasz drogę prowadzącą na zachód. Korytarz prowadzi przez jakiś czas prosto. Wędrujesz powoli, cały czas rozglądając się na boki w poszukiwaniu pułapek, co chwila przystajesz, nasłuchując czy nie ma jakiś odgłosów dobiegających z tunelu. Po jakimś czasie dochodzisz do skrzyżowania korytarza. Masz teraz cztery drogi do wyboru:', 'pl'),
(244, 4, 'grid.php', 'box15', '1', 'Iść na południe', 'pl'),
(245, 4, 'grid.php', 'box15', '2', 'Iść na zachód', 'pl'),
(246, 4, 'grid.php', 'box15', '3', 'Iść na północ', 'pl'),
(247, 4, 'grid.php', 'box15', '4', 'Zawrócić', 'pl'),
(248, 4, 'grid.php', '1.3.4', '0', 'Postanawiasz zawrócić z powrotem do sali z drzwiami aby wybrać inną drogę. Trasa powrotna zajmuje ci nieco mniej czasu, znasz już w końcu ten korytarz. Po kilku chwilach ponownie jesteś w pomieszczeniu z drzwiami. Przez chwilę odpoczywałeś. Następnie zbierasz swój ekwipunek i wychodzisz przez drzwi. Przez jakiś czas wędrowałeś korytarzami ale niestety nie natrafiłeś na nic bardziej interesującego niż kurz na podłodze. Stwierdziłeś, że masz na razie dość zwiedzania labiryntu i skierowałeś się ku wyjściu z niego. Po jakimś czasie docierasz do bramy, przekraczasz ją i ponownie wchodzisz na ulice miasta.', 'pl'),
(249, 4, 'grid.php', '1.3.1', '0', 'Wybierasz drogę na południe. Korytarz którym teraz idziesz przypomina korytarze którymi wcześniej wędrowałeś. Wykonany jest z kamiennych bloków, jego podłogę pokrywa niewielka warstwa kurzu. Ostrożnie rozglądając się na boki idziesz przed siebie.', 'pl'),
(250, 4, 'grid.php', 'int3', '0', 'Nagle obok siebie usłyszałeś lekki świst i poczułeś piekący ból w boku. Spoglądając w tę stronę, zauważasz znikające w szczelinie w ścianie ostrze. Jednak na razie zwracasz przede wszystkim uwagę na swoją ranę z której wydobywa się krew. Szybko bandażujesz ją, mimo to i tak straciłeś nieco sił. Tracisz przez to 10 punktów życia. Patrzysz pod nogi i zauważasz wciśnięty niewielki przycisk, którego wcześniej nie widziałeś. Pewnie to on uruchomił pułapkę. Odpoczywasz przez moment a nastepnie znów wyruszasz na zwiedzanie tego korytarza.', 'pl'),
(251, 4, 'grid.php', 'int4', '0', 'Kiedy chciałeś postawić kolejny krok, zauważyłeś w kurzu podłogi podejrzane wybrzuszenie. Ostrożnie odgarniasz kurz, przyglądając się znalezisku. To niewielki kamień wystający nieco nad poziom podłogi. Uważnie zaczynasz przyglądać się ścianą. Po lewej stronie zauważasz wąską szczelinę w ścianie. Kiedy przybliżasz do niej pochodnię widzisz delikatny błysk żelaza z wnętrza. Domyślasz się już, że to jakaś pułapka a kamień na podłodze ją uruchamia.', 'pl'),
(252, 4, 'grid.php', '1.3.1next', '1E', 'Ostrożnie, nauczony doświadczeniem wyruszasz dalej na zwiedzanie labiryntu. Teraz twoja uwaga jest jeszcze bardziej napięta niż poprzednio. Wędrujesz dalej przed siebie. Korytarz cały czas prowadzi prosto, chwilami lekko pnąc się w górę by po chwili nieco opadać. Po jakimś czasie wędrówki docierasz do niewielkiego pomieszczenia. Prawie całe jest zajęte przez skrzynię wykonaną z drewna okutego żelazem. Na boku skrzyni, zamiast zamka widzisz dwa bębny a obok nich przycisk. Na bębnach znajdują się cyfry oraz litery. Tuż nad bębnami widzisz taki mniej więcej napis:<i>1-1, 5-5, 10-A, 12-C, 20-14, 30- </i>Domyślasz się, że aby otworzyć skrzynię musisz ustawić odpowiednią kombinację na bębnach. Zaczynasz zastanawiać się nad odpowiedzią.', 'pl'),
(253, 4, 'grid.php', 'answer4', '0', '<i>1-1, 5-5, 10-A, 12-C, 20-14, 30-</i><br />Kiedy ustawiłeś kombinację będącą twoim zdaniem odpowiedzią na zagadkę, nacisnąłeś przycisk. Niestety nic się nie wydarzyło. Więc to była złą odpowiedź. Przez chwilę stoisz zastanawiając się nad kolejną możliwością.', 'pl'),
(254, 4, 'grid.php', 'answer5', '0', 'Kiedy po raz dziesiąty próbowałeś ustawić prawidłową kombinację, przycisk wcisnął się na stałe do środka i nie wrócił do swojego położenia. Nie możesz też poruszyć bębnami. Przez jakiś czas stoisz nad skrzynią zniechęcony. Następnie zbierasz swój ekwipunek i wyruszasz z powrotem w kierunku skrzyżowania. Ponieważ znasz już ten korytarz, pokonujesz ten dystans szybciej niż ostatnio, jedynie w okolicy gdzie natrafiłeś na pułapkę, zwalniasz i przechodzisz ostrożnie. Po jakimś czasie docierasz do skrzyżowania.', 'pl'),
(255, 4, 'grid.php', 'answer6', '0', 'Kiedy ustawiłeś kombinację <i>1E</i> i nacisnąłeś przycisk, usłyszałeś ciche kliknięcie i wieko skrzyni podniosło się odrobinę. Ostrożnie podważyłeś je i zaglądnąłeś do środka. Wewnątrz widzisz jakieś zawiniątko. Delikatnie wyciągasz je ze skrzyni i rozwijasz okrycie. Wewnątrz znajdujesz doskonale zakonserwowaną żelazną kolczugę. Szybko chowasz ją do plecaka i przez chwilę odpoczywasz. Następnie zbierasz swój ekwipunek i wyruszasz z powrotem w kierunku skrzyżowania. Ponieważ znasz już ten korytarz, pokonujesz ten dystans szybciej niż ostatnio, jedynie w okolicy gdzie natrafiłeś na pułapkę, zwalniasz i przechodzisz ostrożnie. Po jakimś czasie docierasz do skrzyżowania.', 'pl'),
(256, 4, 'grid.php', 'box16', '1', 'Iść na zachód', 'pl'),
(257, 4, 'grid.php', 'box16', '2', 'Iść na północ', 'pl'),
(258, 4, 'grid.php', 'box16', '3', 'Wrócić', 'pl'),
(259, 4, 'grid.php', '1.3.2', '0', 'Kierujesz się do korytarza prowadzącego na zachód. Ostrożnie podążasz przed siebie, badając okolicę i co jakiś czas nasłuchując dźwięków dobiegających z przodu. Korytarz ten niczym nie różni się od tych, którymi wędrowałeś wcześniej. W pewnym momencie dostrzegasz tuż przy suficie resztki bardzo starych pajęczyn. Korytarz zaczyna wić się to w lewo to w prawo. Co jakiś czas natykasz się na krótkie odgałęzienia głównego korytarza. Sprawdzasz każde z nich, ale są to albo ślepe korytarze, albo ponownie prowadzą cię do korytarza głównego. Nagle gdzieś przed sobą usłyszałeś skrzypienie. Zwiększając czujność powoli zmierzasz w tym kierunku. Twoje nerwy są napięte do ostateczności. Nagle słyszysz jak skrzypienie zaczyna się coraz szybciej zbliżać. Próbując wzrokiem przebić ciemność wpatrujesz się przed siebie. Nagle widzisz kilkanaście czerwonych punkcików na wysokości twojej głowy, zbliżających się w twoim kierunku. Jeszcze chwila i owe punkty zmieniają się w szarżującego na ciebie Olbrzymiego Pająka!', 'pl'),
(260, 4, 'grid.php', 'lostfight4', '0', 'Próbowałeś bronić się przed furią bestii ale jej głód okazał się silniejszy od ciebie. Ostatnią rzeczą jaką pamiętasz to olbrzymie, pokryte trucizną kły nad twoją głową. Potem poczułeś gwałtowny ból w piersi, świat zawirował ci przed oczami i zapadłeś w ciemność. Budzisz się dopiero w szpitalu w city1a.', 'pl'),
(261, 4, 'grid.php', 'winfight4', '0', 'Bez problemu poradziłeś sobie z bestią. Twój ostatni cios doszedł nie opancerzonego miejsca ofiary. Przez moment pająk przeraźliwie zaskrzeczał a po chwili z łoskotem padł na ziemię martwy. Przez chwilę stoisz, łapiąc oddech, nieco zmęczony po walce. Następnie zbierasz ekwipunek i wyruszasz dalej na badanie korytarza.', 'pl'),
(262, 4, 'grid.php', '1.3.2next', '0', 'Ostrożnie idziesz przed siebie, w każdej chwili oczekując na spotkanie z pobratymcami bestii. Jednak w korytarzu panuje martwa cisza. Uważnie sprawdzasz korytarz i każdą jego odnogę, ale podobnie jak wcześniej, wszystkie boczne korytarze są albo ślepymi zaułkami albo z powrotem prowadzą do głównego korytarza. Po pewnym czasie docierasz do końca korytarza. Widzisz tutaj stos kości podróżników, którzy nie mieli tyle szczęścia co ty. Ostrożnie rozgarniając kości próbujesz znaleźć coś interesującego. Niestety są tu tylko stare szmaty i pordzewiałe resztki uzbrojenia. Dla pewności sprawdzasz jeszcze ściany korytarza, ale powoli, smród panujący w tym miejscu zaczyna ci mieszać w głowie. Wycofujesz się stąd wracając z powrotem w kierunku skrzyżowania. Wracasz tą samą drogą co przyszedłeś, wymijając tylko cielsko potwora po drodze. Po jakimś czasie ponownie docierasz do skrzyżowania.', 'pl'),
(263, 4, 'grid.php', 'box17', '1', 'Iść na północ', 'pl'),
(264, 4, 'grid.php', 'box17', '2', 'Wrócić', 'pl'),
(265, 4, 'grid.php', '1.3.3', '0', 'Korytarz prowadzi cały czas prosto przed siebie, delikatnie opadając w dół. Idziesz ostrożnie, badając każdy kawałek korytarza w poszukiwaniu pułapek czy innych niespodzianek, co jakiś czas przystajesz nasłuchując odgłosów. Jednak w korytarzu panuje absolutna cisza. Po jakimś czasie docierasz do końca korytarza. W ścianie widzisz solidne drewnianie drzwi. Kiedy naciskasz na klamkę, okazują się być zamknięte. Stoisz przez moment zastanawiając się. Co postanawiasz?', 'pl'),
(266, 4, 'grid.php', '1.3.3.1a', '0', 'Bierzesz kawałek miedzianego drutu, formujesz go i próbujesz przy jego pomocy otworzyć drzwi. Delikatnie manewrując udaje ci się pokonać zamek. Po chwili drzwi stoją otworem.', 'pl'),
(267, 4, 'grid.php', '1.3.3.1b', '0', 'Odkładasz na bok swój ekwipunek, cofasz się kilkanaście kroków a następnie bierzesz rozbieg i z całej siły uderzasz w drzwi. Potraktowane w ten sposób dosłownie rozpadają się na kawałki. Ty na szczęście jesteś cały, jedynie ramię cię trochę boli. Zbierasz z powrotem swój ekwipunek i przekraczasz resztki drzwi.', 'pl'),
(268, 4, 'grid.php', '1.3.3.1next', '0', 'Kiedy przeszedłeś przez drzwi ze zdumienia stanąłeś w miejscu. Przed sobą widzisz niewielką salę, która lekko promieniuje białym blaskiem od podłogi. Tuż nad ziemią stoją na niskich stojakach duże, zielone trawniki. Ściany komnaty pokryte są jakimiś dziwnymi napisami i wzorami, zupełnie ci nieznanymi. O dziwo nie zauważasz tutaj nawet śladu kurzu, jaki przecież do tej pory był wszechobecny. Powoli podchodzisz do jednego z owych trawniku. W tym momencie zauważasz, że to nie jest trawa a raczej farma z dużą ilością ziół Nutari. Przypominasz sobie wszystko co wiesz o tej roślinie, o tym jak ją zbierać. Wyciągasz nóż i ostrożnie zbierasz zioła. W ten sposób zdobywasz 100 porcji Nutari. Ostrożnie chowasz je do plecaka. Chwilę odpoczywasz w tym miejscu a następnie kierujesz się z powrotem do pomieszczenia z drzwiami. Trasa powrotna zajmuje ci nieco mniej czasu, znasz już w końcu ten korytarz. Po kilku chwilach ponownie jesteś w pomieszczeniu z drzwiami. Tutaj na moment przystajesz aby nieco odpocząć. Po jakimś czasie zbierasz swój ekwipunek i przechodzisz przez drzwi i kierujesz się z powrotem na znaną ci drogę. Po jakimś czasie docierasz do znanej już ci części labiryntu. Kierujesz się do wyjścia z labiryntu. Dochodzisz do niego a następnie opuszczasz tę lokację wychodząc na gwarne ulice miasta.', 'pl'),
(269, 4, 'grid.php', 'escape1', '0', 'W panice rzucasz się do ucieczki, słysząc cały czas za sobą popiskiwania gryzoni. Przerażony uciekasz przez jakiś czas, nawet nie wiedząc czy pogoń podąża za tobą. Pędem wracasz do pomieszczenia z drzwiami, szybko przebiegasz przez nie i zatrzaskujesz. Dopiero teraz zatrzymujesz się. Przez pewien czas stoisz oparty o ścianę dysząc ze zmęczenie. Po dość długiej chwili odpoczynku, postanawiasz zostawić tajemnice kryjące się za drzwiami w spokoju i wrócić do miasta. Masz dość wrażeń jak na jeden dzień. Zbierasz więc ekwipunek i podążasz znanymi sobie korytarzami w kierunku wyjścia. Po jakimś czasie docierasz do niego, wkraczając ponownie na ulice miasta.', 'pl'),
(270, 4, 'grid.php', 'escape2', '0', 'W panice rzucasz się do ucieczki, słysząc cały czas za sobą warkot i okrzyki Goblina. Przerażony uciekasz przez jakiś czas, nawet nie wiedząc czy pogoń podąża za tobą. Pędem wracasz do pomieszczenia z drzwiami, szybko przebiegasz przez nie i zatrzaskujesz. Dopiero teraz zatrzymujesz się. Przez pewien czas stoisz oparty o ścianę dysząc ze zmęczenie. Po dość długiej chwili odpoczynku, postanawiasz zostawić tajemnice kryjące się za drzwiami w spokoju i wrócić do miasta. Masz dość wrażeń jak na jeden dzień. Zbierasz więc ekwipunek i podążasz znanymi sobie korytarzami w kierunku wyjścia. Po jakimś czasie docierasz do niego, wkraczając ponownie na ulice miasta.', 'pl'),
(271, 4, 'grid.php', 'escape3', '0', 'W panice rzucasz się do ucieczki, słysząc cały czas za sobą skrzypot stawów potwora. Przerażony uciekasz przez jakiś czas, nawet nie wiedząc czy pogoń podąża za tobą. Pędem wracasz do pomieszczenia z drzwiami, szybko przebiegasz przez nie i zatrzaskujesz. Dopiero teraz zatrzymujesz się. Przez pewien czas stoisz oparty o ścianę dysząc ze zmęczenie. Po dość długiej chwili odpoczynku, postanawiasz zostawić tajemnice kryjące się za drzwiami w spokoju i wrócić do miasta. Masz dość wrażeń jak na jeden dzień. Zbierasz więc ekwipunek i podążasz znanymi sobie korytarzami w kierunku wyjścia. Po jakimś czasie docierasz do niego, wkraczając ponownie na ulice miasta.', 'pl'),
(272, 5, 'grid.php', 'start', '0', 'Idąc cały czas korytarzami labiryntu, uważnie rozglądasz się na boki w poszukiwaniu różnych niespodzianek. Po pewnym czasie trafiasz do tej części labiryntu, która nie jest ci zbyt dobrze znana. Zwiększasz więc czujność i ostrożnie ruszasz przed siebie. Wędrując tak przed siebie, w pewnym momencie widzisz w ścianie po lewej stronie, otwór jakiegoś tunelu. Korytarz ów, wygląda nieco inaczej niż te, którymi do tej pory wędrowałeś. Wyraźnie widzisz, że prowadzi on lekko w dół, jakby na niższy poziom labiryntu. Przez chwilę stoisz przed wejściem zastanawiając się co zrobić.', 'pl'),
(273, 5, 'grid.php', 'box1', '1', 'Wejść do tunelu', 'pl'),
(274, 5, 'grid.php', 'box1', '2', 'Iść dalej', 'pl'),
(275, 5, 'grid.php', '2', '0', 'Postanawiasz zostawić w spokoju nieznany ci tunel. Przez chwilę odpoczywasz niedaleko wejścia do niego. Następnie zbierasz swój ekwipunek i decydujesz się iść dalej, badając co kryje korytarz prowadzący naprzód.', 'pl'),
(276, 5, 'grid.php', '1', '0', 'Tunel został w dawnych czasach bardzo dokładnie obrobiony. Jego ściany są proste u góry łagodnie przechodzące w łukowate sklepienie. Na podłodze leży gruba na cal poduszka kurzu, powodująca iż przy każdym kroku u twoich stóp wzbija się niewielka mgła z kurzu. Widać, iż raczej nikt nie odwiedzał tej części labiryntu przez długi czas. Światło twojej pochodni oświetla najbliższą okolicę, dalszą część korytarza spowija mrok. Ostrożnie idziesz przed siebie, cały czas przeszukując otoczenie w poszukiwaniu pułapek i innych niespodzianek. Droga prowadzi delikatnie w dół, po jakimś czasie masz wrażenie że zszedłeś już mocno poniżej poziomu znanej ci części labiryntu. Powoli warstwa kurzu na podłodze zmniejsza się, by po jakimś czasie zniknąć prawie zupełnie. W pewnym momencie korytarz zaczyna z powrotem prowadzić poziomo. Po kilku chwilach dostrzegasz w półmroku przed sobą, że powoli docierasz do rozwidlenia w kształcie litery T. Ostrożnie podchodzisz do skrzyżowania i rozglądasz się na wszystkie strony. Po chwili postanawiasz:', 'pl'),
(277, 5, 'grid.php', 'box2', '1', 'Iść na wschód', 'pl'),
(278, 5, 'grid.php', 'box2', '2', 'Iść na zachód', 'pl'),
(279, 5, 'grid.php', 'box2', '3', 'Zawrócić', 'pl'),
(280, 5, 'grid.php', 'box5', '1', 'Iść na zachód', 'pl'),
(281, 5, 'grid.php', 'box5', '2', 'Zawrócić', 'pl'),
(282, 5, 'grid.php', '1.3', '0', 'Postanawiasz zawrócić do znanej ci już części labiryntu. Nie interesuje cię za bardzo co kryją owe tajemnicze korytarze. Zawracasz więc z powrotem do wyjścia. Droga powrotna jest nieco trudniejsza, gdyż prowadzi lekko pod górę. W połowie znanego ci już tunelu robisz sobie krótki postój na zebranie sił. Po chwili ruszasz ponownie przed siebie. Po pewnym czasie docierasz do wyjścia z tunelu. Przez chwilę odpoczywasz niedaleko wejścia do niego. Następnie zbierasz swój ekwipunek i decydujesz się iść dalej, badając co kryje korytarz prowadzący naprzód.', 'pl'),
(283, 5, 'grid.php', '1.1', '0', 'Korytarz prowadzi cały czas prosto. Dookoła ciebie panuje całkowita cisza, wydaje ci się, że odgłos twoich kroków niesie się na wiele mil dookoła. Ostrzożnie idziesz przed siebie, rozglądając się na wszystkie strony w poszukiwaniu niespodzianek. Podłoga korytarza jest w miarę czysta, tak więc bez problemu widzisz kamienną posadzkę.', 'pl'),
(284, 5, 'grid.php', 'int1', '0', 'Idąc tak, w pewnym momencie zauważasz prawie na samym środku korytarza, delikatnie zarysowane, jakby pęknięcie w podłodze. Przystajesz i zaczynasz przyglądać się uważniej. Powoli odkrywasz, że owo pęknięcie ma jakby kształt kwadratu. Po chwili domyślasz się, iż to nie tyle pęknięcie ile sprytnie ukryta zapadnia w podłodze. Na szczęście udało ci się ją w porę zauważyć. Ostrożnie przechodzisz tuż przy ścianie obok pułapki.', 'pl'),
(285, 5, 'grid.php', 'int2', '0', 'Ostrożnie idziesz przed siebie badając okolicę. W pewnym momencie, kiedy badałeś kawałek ściany, nagle cała podłoga uciekła ci spod stóp! Poczułeś tylko jak lecisz w dół.', 'pl'),
(286, 5, 'grid.php', 'hp1', '0', 'Na szczęście ów lot nie trwał zbyt długo. Nagle poczułeś ukłucie w prawej nodze a nastepnie twardo wylądowałeś na kamieniach. Przez chwilę dochodzisz do siebie. Pierwszą rzeczą na jaką zwracasz uwagę, jest cieknąca ci po łydce twoja krew. Szybko zatamowujesz krwawienie (tracisz przez to 10 punktów życia). Kiedy już się obandażowałeś, zaczynasz rozglądać się, co spowodowało ową ranę. Nie musisz szukać zbyt długo. Tuż przy samej nodze dostrzegasz wystający z podłogi, zaostrzony w szpic kamień. Przyglądając się uważnie pomieszczeniu w którym się znalazłeś, zauważasz, że takich kamieni jest więcej. Miałeś szczęście w nieszczęściu, że pułapka, pewnie z powodu starości, zadziałała nieco później niż powinna. Inaczej teraz leżałbyś nabity na owe kamienie. Kiedy spoglądasz do góry, zauważasz, że krawędź pułapki nie znajduje się zbyt wysoko. Postanawiasz jak najszybciej opuścić to nieprzyjazne miejsce. Wykorzystując zagłębienia w ścianie, szybko wspinasz się te kilka stóp do góry, by po chwili znaleźć się z powrotem w korytarzu.', 'pl'),
(287, 5, 'grid.php', 'hp2', '0', 'Przerażony krzyknąłeś, jednak na niewiele się to zdało. Na twoje nieszczęście, pułapka zadziałała tak jak powinna - updałeś na sam środek wielkiego dołu, wypełnionego metrowej długości ostrymi jak szpikulce kamieniami. Ostatnią rzeczą jaką zapamiętałeś, to biegnący z nierealną szybkością na spotkanie twojej głowy kamienny szpic. Następie poczułeś potworny ból w kilkunastu miejscach zwojego ciała i zapadłeś w całkowitą ciemność. W ten sposób zakończyło się nie tylko zwiedzanie labiryntu ale i twoje życie.', 'pl'),
(288, 5, 'grid.php', '1.1.next', '0', 'Przez chwilę odpoczywasz po ostatnich wydarzeniach. Następnie zbierasz swój ekwipunek i ponownie ruszasz przed siebie, tym razem jeszcze uważniej przyglądając się otoczeniu w poszukiwaniu różnych niespodzianek. Korytarz, wykuty w skale, cały czas prowadzi prosto. Ściany korytarza są gładkie, nie ma jakichkolwiek śladów rzeźb czy innych ozdobników. Ostrożnie kierujesz się dalej. Po pewnym czasie, zauważasz, że czerń korytarza przed tobą, jakby się poszerzała. Kiedy ostrożnie podchodzisz bliżej zauważasz, że przed tobą znajduje się małe pomieszczenie. Przed wejściem do niego sprawdzasz, czy nie kryje jakiś niespodzianek. Następnie powoli wchodzisz do środka. Widzisz, że znajdujesz się w niewielkiej komnacie, której wymiary to kilka kroków w obie strony. Nigdzie nie widać dalszej części korytarza. Co postanawiasz?', 'pl'),
(289, 5, 'grid.php', 'box3', '1', 'Przeszukać pomieszczenie', 'pl'),
(290, 5, 'grid.php', 'box3', '2', 'Zawrócić', 'pl'),
(291, 5, 'grid.php', '1.1.2', '0', 'Postanawiasz zawrócić z powrotem do skrzyżowania - wystarczy ci wrażeń jak na jeden raz. Odwracasz się więc i z powrotem kierujesz się znanym ci już korytarzem. W miejscu gdzie wcześniej natknąłeś się na pułapkę, zwalniasz i ostroznie omijasz dziurę w ziemi. Następnie szybkim krokiem kierujesz się do znanej ci już wcześniej części korytarza. Po jakimś czasie docierasz do skrzyżowania. W którym kierunku chcesz się teraz udać?', 'pl'),
(292, 5, 'grid.php', '1.1.1', '0', 'Zbyt wiele się wydarzyło pod drodze, twoim zdaniem aby teraz tak po prostu odejść. Tu musi być coś ukrytego. Zaczynasz więc dokładnie badać ściany w poszukiwaniu ukrytych przycisków czy tajemnych przejść.', 'pl'),
(293, 5, 'grid.php', 'int3', '0', 'Przeszukałeś od góry do dołu całe pomieszczenie. Niestety nic nie znalazłeś. Rozczarowany odpoczywałeś przez jakiś czas a następnie postanowiłeś zawrócić do skrzyżowania. Zbierasz więc swój ekwpunek i z powrotem kierujesz się znanym ci już korytarzem. W miejscu gdzie wcześniej natknąłeś się na pułapkę, zwalniasz i ostroznie omijasz dziurę w ziemi. Następnie szybkim krokiem kierujesz się do znanej ci już wcześniej części korytarza. Po jakimś czasie docierasz do skrzyżowania. W którym kierunku chcesz się teraz udać?', 'pl'),
(294, 5, 'grid.php', 'int4', '0', 'Badałeś przez pewien czas cal po calu całe pomieszczenie. Twoja metodyczność oraz poświęcenie po jakimś czasie zostało wynagrodzone. Kiedy ostrożnie badałeś południową ścianę, zastanowił cię jej fragment. Wprawdzie pod palcami czujesz chłód kamienia, ale wydaje ci się że coś jest nie tak. Ostrożnie nacisnąłeś mocniej. Okazało się, że twoja ręka bez problemu przeszła przez ścianę! Nieco głębiej czujesz lekki chłód korytarza. To iluzyjna ściana. Zbierasz szybko swój ekwipunek i powoli przechodzisz cały przez ścianę.', 'pl'),
(295, 5, 'grid.php', '1.1.1.next', '0', 'Widzisz przed sobą wąski, pokryty jakimiś płaskorzeźbami korytarz. Prowadzi on prosto. Uważniej przyglądasz się wzorom na ścianach. Widzisz iż przedstawiają one różne zwierzęta, istoty inteligentne oraz bestie. Część z przedstawionych istot rozpoznajesz, część natomiast widzisz po raz pierwszy w życiu. Ostrożnie idziesz przed siebie, badając okolicę w poszukiwaniu pułapek. Korytarz jest bardzo starannie wykonany, na podłodze nie widać ani drobiny kurzu. Dookoła panuje całkowita cisza, która sprawia że czujesz się trochę nieswojo. Idziesz tak przed siebie jakiś czas. W pewnym momencie widzisz, że korytarz kończy się drzwiami. Ostrożnie podchodzisz do nich, przyglądając im się. Przez chwilę przeszukujesz drzwi w poszukiwaniu pułapek i innych groźnych niespodzianek. Kiedy zdobywasz już pewność, iż nic ci nie grozi, delikatnie chwytasz za klamkę i naciskasz. O dziwo, okazuje się, że drzwi nie były zamknięte. Powoli otwierasz drzwi. Ze zdziwieniem zauważasz iż mimo że wyglądają na stare, wcale nie skrzypią. Przez szparę zaglądasz do środka. Widzisz przed sobą niewielki fragment pomieszczenia w którym znajduje się regał z jakimiś rzeczami. Otwierasz więc drzwi szerzej i uważnie obserwujesz całe pomieszczenie. Okazuje się ono być średnich rozmiarów komnatą. Ze środka dobiega do ciebie martwa cisza. Wchodzisz więc przez drzwi i dokładniej badasz pomieszczenie. Jest to sala o rozmiarach kilkudziesięciu kroków na kilkanaście, prawie po brzegi wypełniona różnego rodzaju regałami, na których znajdują się książki. Większość z nich jest napisana w nieznanych ci językach, jednak znajdujesz kilka napisanych w zrozumiały dla ciebie sposób. Opisują one dość ogólnie krainę w której żyjesz oraz istoty poruszające się po niej. Kiedy próbujesz schować je do plecaka i wyjść z pomieszczenia, natrafiasz na niewidzialną barierę. Przez moment wydaje ci się, że zostałeś tutaj uwięziony na zawsze. Jednak kiedy wyciągasz wszystkie księgi z plecaka, okazuje się że bez problemu możesz przejść przez drzwi. Wychodzi na to, że jeżeli chcesz się zapoznać z owymi dziełami, musisz niestety przeczytać je na miejscu. Czy chcesz poświęcić nieco czasu na zapoznanie się z zawartością biblioteki?', 'pl'),
(296, 5, 'grid.php', 'box4', '1', 'Tak', 'pl'),
(297, 5, 'grid.php', 'box4', '2', 'Nie', 'pl'),
(298, 5, 'grid.php', '1.1.1.2', '0', 'Postanawiasz nie tracić czasu na jakieś stare książki. Zbierasz więc swój ekwipunek i wychodzisz z owej biblioteki. Tym samym, bogato zdobionym korytarzem docierasz do iluzorycznej ściany i przechodzisz przez nią. Tutaj przez chwilę odpoczywasz. Następnie postanawiasz zawrócić z powrotem do skrzyżowania. Ruszasz więc z powrotem znanym ci już korytarzem. W miejscu gdzie wcześniej natknąłeś się na pułapkę, zwalniasz i ostroznie omijasz dziurę w ziemi. Następnie szybkim krokiem kierujesz się do znanej ci już wcześniej części korytarza. Po jakimś czasie docierasz do skrzyżowania. W którym kierunku chcesz się teraz udać?', 'pl'),
(299, 5, 'grid.php', '1.1.1.1', '0', 'Postanawiasz dokładniej przyjrzeć się książkom. Rozsiadasz się wygodnie pomiędzy regałami i zaczynasz je studiować. Zajmuje ci to nieco czasu. Kiedy przestudiowałeś jedną, zabierasz się za kolejną. Zaintrygowany informacjami zawartymi w nich, zapomiałeś o całym świecie. Zatrzymałeś się dopiero w momencie kiedy poczułeś się zmęczony. Przez chwilę odpoczywałeś a nastepnie zebrałeś swój ekwipunek i postanowiłeś ruszyć w drogę powrotną. Wyszedłeś więc z biblioteki. Tym samym, bogato zdobionym korytarzem docierasz do iluzorycznej ściany i przechodzisz przez nią. Tutaj przez chwilę odpoczywasz. Następnie postanawiasz zawrócić z powrotem do skrzyżowania. Ruszasz więc z powrotem znanym ci już korytarzem. W miejscu gdzie wcześniej natknąłeś się na pułapkę, zwalniasz i ostroznie omijasz dziurę w ziemi. Następnie szybkim krokiem kierujesz się do znanej ci już wcześniej części korytarza. Po jakimś czasie docierasz do skrzyżowania. W którym kierunku chcesz się teraz udać?', 'pl'),
(300, 5, 'grid.php', '1.2', '0', 'Doskonale wykuty w skale korytarz prowadzi cały czas przed siebie. Ostrożnie stąpasz po płytach chodnika, obserwując otoczenie w poszukiwaniu różnych niespodzianek. Cisza panująca dookoła ciebie nieco cię onieśmiela. Na podłodze nie dostrzegasz ani krztyny kurzu, natomiast przy suficie widzisz iż ściany pokrywa niewielka warstewka wilgoci. Powietrze dookoła nie jest tak duszne jak w korytarzach, którymi wcześniej wędrowałeś. Po pewnym czasie zauważasz przed sobą, że czerń korytarza jakby rozchodziła się na kilka stron. Powoli zbliżasz się do tego miejsca. Kiedy przechodzisz kilka kroków, widzisz iż znajdujesz się przed skrzyżowaniem - masz teraz do wyboru trzy drogi. Kiedy tak stojąc w ciszy przyglądasz się korytarzom, słyszysz nagle dobiegające od korytarza prowadzącego na południe, delikatny szmer. Przez chwilę zastanawiasz się co dalej. Co postanawiasz?', 'pl'),
(301, 5, 'grid.php', 'box6', '1', 'Iść na zachód', 'pl'),
(302, 5, 'grid.php', 'box6', '2', 'Iść na południe', 'pl'),
(303, 5, 'grid.php', 'box6', '3', 'Iść na północ', 'pl'),
(304, 5, 'grid.php', 'box6', '4', 'Zawrócić', 'pl'),
(305, 5, 'grid.php', 'box8', '1', 'Iść na południe', 'pl'),
(306, 5, 'grid.php', 'box8', '2', 'Iść na północ', 'pl'),
(307, 5, 'grid.php', 'box8', '3', 'Zawrócić', 'pl'),
(308, 5, 'grid.php', 'box10', '1', 'Iść na północ', 'pl'),
(309, 5, 'grid.php', 'box10', '2', 'Zawrócić', 'pl'),
(310, 5, 'grid.php', '1.2.4', '0', 'Postanawiasz zostawić wszelkie zagadki nierozwiązane. Przez chwilę odpoczywasz na skrzyżowaniu, następnie zbierasz swój ekwipunek i udajesz się w kierunku wyjścia z owego tunelu. Wstajesz więc z ziemi i z powrotem kierujesz się znanym ci już korytarzem. Szybkim krokiem kierujesz się do znanej ci już wcześniej części korytarza. Po jakimś czasie docierasz do skrzyżowania. Droga powrotna jest nieco trudniejsza, gdyż prowadzi lekko pod górę. W połowie znanego ci już tunelu robisz sobie krótki postój na zebranie sił. Po chwili ruszasz ponownie przed siebie. Po pewnym czasie docierasz do wyjścia z tunelu. Przez chwilę odpoczywasz niedaleko wejścia do niego. Następnie zbierasz swój ekwipunek i kierujesz się w stronę wyjścia z labiryntu. Kiedy docierasz do niego, w twoje oczy uderza blask dawno nie widzianego słońca.', 'pl'),
(311, 5, 'grid.php', '1.2.1', '0', 'Korytarz prowadzący cały czas prosto jest dość szeroki i wysoki, tak więc bez problemu podróżujesz nim. Po przejściu kilkunastu kroków dziwny szmer jaki słyszałeś na skrzyżowaniu zanikł zupełnie. Ostrożnie kierujesz się przed siebie, badając każdy fragment korytarza w poszukiwaniu niespodzianek. Jednak jak do tej pory nie natrafiłeś na nic niebezpiecznego. Idziesz przed siebie jakiś czas. W pewnej chwili widzisz daleko przed sobą w otaczającym cię półmroku coś jakby ciemy zarys jakiegoś prostokątnego przedmiotu. Ostrożnie idą w tę stronę, zauważasz że ów zarys powoli zmienia się w otwarte drzwi. Podchodząc ostrożnie bliżej, widzisz przed sobą niewielką komnatę. Niepewnie zaglądasz do środka. Twoim oczom ukazuje się pomieszczenie o rozmiarach kilkanaście kroków szerokie i prawie tyle samo długie. Ściany pokrywają płaskorzeźby. Powoli wchodzisz do komnaty. Kiedy przyglądasz się uważniej rzeźbom na ścianach, widzisz iż przedstawiają one różne gady oraz istoty walczące z nimi. Tuż przy drzwiach widzisz walczącego Krasnoluda z Alaghi. Obok nich narysowana jest klepsydra. Ściany prawie całkowicie pokryte są rzeźbami, podobnie jak i sufit. Nic więcej na pierwszy rzut oka nie wydaje ci się tutaj interesujące. Czy masz ochotę przeszukać całe to pomieszczenie?', 'pl'),
(312, 5, 'grid.php', 'box7', '1', 'Tak', 'pl'),
(313, 5, 'grid.php', 'box7', '2', 'Nie', 'pl'),
(314, 5, 'grid.php', '1.2.1.2', '0', 'Twoim zdaniem nie ma w tym pomieszczeniu niczego interesującego. Przez krótką chwilę jeszcze przyglądasz się całemu pomieszczeniu. Następnie poprawiasz swój plecak i zawracasz w kierunku skrzyżowania. Podróż tym korytarzem okazała się tylko stratą czasu. Idziesz więc z powrotem. Po pewnym czasie znów słyszysz dobiegający szmer i po chwili znów znajdujesz się na skrzyżowaniu. Którą drogę teraz wybierasz?', 'pl'),
(315, 5, 'grid.php', '1.2.1.1', '0', 'Postanowiłeś przeszukać dokładniej owo pomieszczenie. Przez pewien czas badałeś cal po calu ściany oraz podłogę. Niestety nic nie znalazłeś. Przez chwilę odpoczywałeś a nastepnie podjąłeś decyzję powrotu do skrzyżowania. Kiedy podnosiłeś swój ekwipunek z ziemi. Nagle za plecami usłyszałeś cichy szurgot. Szybko odwróciłeś się w tę stronę, akurat na czas aby zobaczyć jak, jedna ze ścian uniosła się a z powstałego przejścia błyskawicznie wyskoczył w twoim kierunku Alaghi!', 'pl'),
(316, 5, 'grid.php', 'lostfight1', '0', 'Zaskoczony próbowałeś bronić się przez jakiś czas. Niestety atak potwora był nie do zatrzymania. Pazurami rozszarpał prawie całe twoje ciało. Ostatnią rzeczą jaką zapamiętałeś to pysk bestii zbliżający się do twojego gardła. Nagle w twej głowie eksplodowała gwiazda bólu a ty martwy upadłeś na podłogę.', 'pl'),
(317, 5, 'grid.php', 'escape1', '0', 'Mówią, że strach dodaje skrzydeł - tak było i tym razem. Prawie że przefrunąłeś nad bestią w kierunku wejścia i pognałeś na złamanie karku przed siebie. Za swoimi plecami cały czas czujesz oddech bestii. Świadomość tego co biegnie za tobą dodaje ci sił. Błyskawicznie docierasz do skrzyżowania a nastepnie równie szybko kierujesz się w stronę wyjścia z owego tunelu. Przerażony nie oglądasz się za siebie, wydaje ci się, że potwór cały czas biegnie za tobą. W ten sposób docierasz do korytarza prowadzącego pod górę. Tutaj dopiero kompletnie zmęczony przystajesz na moment. Płuca pracują ci niczym miechy kowalskie. Ostrożnie odwracasz się, ale nigdzie nie dostrzegasz potwora. Odpoczywasz jakiś czas. Jednego jesteś pewnien - na pewno nie wrócisz do tego korytarza - gdzieś tam w mroku czai się bestia czyhająca na ciebie. Po pewnym czasie zbierasz swój ekwipunek i kierujesz się z powrotem do wyjścia labiryntu - wystarczy ci wrażeń jak na jeden dzień. Droga powrotna jest nieco trudniejsza, gdyż prowadzi lekko pod górę. W połowie znanego ci już tunelu robisz sobie krótki postój na zebranie sił. Po chwili ruszasz ponownie przed siebie. Po pewnym czasie docierasz do wyjścia z tunelu. Przez chwilę odpoczywasz niedaleko wejścia do niego. Następnie zbierasz swój ekwipunek i kierujesz się w stronę wyjścia z labiryntu. Kiedy docierasz do niego, w twoje oczy uderza blask dawno nie widzianego słońca.', 'pl'),
(318, 5, 'grid.php', 'winfight1', '0', 'Twój ostatni atak sprawił że potwór z charkotem padł martwy na ziemię. Przez moment stoisz w miejscu odpoczywając po nagłej walce. Następnie zbierasz swój ekwipunek i sprawdzasz miejsce z którego wyskoczył potwór. Jest to dość duża wnęka w ścianie. Na podłodze widzisz skomplikowany wzór magiczny - który jak podejrzewasz trzymał potwora w ryzach. Domyślasz się, iż być może twoja obecność w tym pomieszczeniu spowodowała uwolnienie się bestii. Przez chwilę dokładnie badasz ową wnękę w poszukiwaniu skarbów. Niestety nic ciekawego nie znajdujesz. Wychodzisz więc z pomieszczenia i kierujesz się do skrzyżowania. Po pewnym czasie znów słyszysz dobiegający szmer i po chwili znów znajdujesz się na skrzyżowaniu. Którą drogę teraz wybierasz?', 'pl'),
(319, 5, 'grid.php', '1.2.2', '0', 'To właśnie z tego korytarza słyszysz dobiegający z oddali szmer. Sam korytarz prowadzi lekko pod skosem w dół, na niższy poziom labiryntu. W powietrzu unosi się zapach wilgoci, na ścianach pojawiają się niewielkie strużki wody, które spływają na ziemię i wsiąkają między płytami tuż przy ścianach. Ostrożnie idziesz przed siebie, cały czas badając otoczenie w poszukiwaniu ukrytych przejść czy pułapek. Powoli korytarz zmienia się z wykonanego przez istoty rozumne przejścia w typowy tunel skalny. Ściany zwężają się, sufit, coraz bardziej obniża. Na ziemi co jakiś czas widać niewielkie kupki odłupanych kamieni. Cały czas słyszysz jak ów cichy szmer coraz bardziej narasta. Po pewnym czasie zdajesz sobie sprawę, iż jest to odłos oznajmiający dość duży zbiornik wodny. Po kilku chwilach, widzisz że korytarz gwałtownie się rozszerza, a w oddali pojawia się jakaś duża otwarta przestrzeń. Kiedy ostrożnie zbliżasz się do wylotu korytarza, widzisz przed sobą, olbrzymią jaskinię skalną. Prawie dokładnie na jej środku znajduje się niewielkie jeziorko. Na jego środku znajduje się maleńka wysepka. Kiedy wchodzisz do sali, nagle dookoła robi się bardzo jasno. Zaskoczony stoisz przez moment niepewnie. Dopiero po chwili zauważasz, że na suficie jak i w samej jaskini znajduje się dużo różnych rozmiarów kryształów w których odbija się światło twojej pochodni. Ostrożnie idąc, badasz całą jaskinię. Dokładnie przeszukanie podłogi oraz ścian niestety nie przyniosło jakichkolwiek ciekawych rezultatów. Ponownie zbliżasz się do jeziora. W jego krystalicznie czystych wodach widzisz pływające ryby. Jednak twoją uwagę przykuwa przede wszystkim owa maleńka wysepka na środku stawu. Obchodząc całe jezioro widzisz iż w jednym miejscu jest znacznie bliżej do wysepki. Widać jak na jej środku czerni się jakiś prostokątny kształt. Co postanawiasz?', 'pl'),
(320, 5, 'grid.php', 'box9', '1', 'Przeprawić się na wysepkę', 'pl'),
(321, 5, 'grid.php', 'box9', '2', 'Zawrócić', 'pl'),
(322, 5, 'grid.php', '1.2.2.2', 'oddech', 'Postanowiłeś zostawić ów przedmiot w spokoju - nie masz zamiaru wchodzić do wody. Przez chwilę urządziłeś sobie piknik na skraju jeziora. Po odpoczynku, zebrałeś swoje rzeczy i ruszyłeś w drogę powrotną. Okazała się ona nieco trudniejsza niż poprzednio - pewnie dlatego że tym razem musiałeś iść pod górkę. W połowie drogi urządziłeś sobie chwilowy odpoczynek. Kiedy nieco odpocząłeś, ponownie kierujesz się do skrzyżowania, by po chwili dotrzeć do miejsca w którym pragnąłeś się znaleźć.', 'pl'),
(323, 5, 'grid.php', '1.2.2.1', 'oddech', 'Przez chwilę stoisz w miejscu, następnie zaczynasz przygotowywać się do wejścia do wody. Zostawiasz na brzegu cały swój ekwipunek i rozebrany wchodzisz do wody. Okazuje się, że sięga ci ona zaledwie do piersi. Na dodatek jest bardzo ciepła. Ostrożnie idziesz w kierunku wysepki. Co jakiś czas wzdrygasz się, kiedy któraś z ryb ociera się o twoje nogi. Kiedy podchodzisz bliżej, stwierdzasz że ów cień, który wcześniej widziałeś to skrzynia. Wygląda jak wykonana z jedengo kawałka bardzo ciemnego drewna. Wychodzisz z wody i ostrożnie zaczynasz się jej przyglądać. Nigdzie nie widzisz nawet śladów zamka czy zawiasów. Jedynie na wieku dostrzegasz jakieś dziwne zadrapania. Kiedy delikatnie dotykasz tego miejsca, nagle zadrapania zaczynają świecić by po chwili w magiczny sposób ułożyć się w zrozumiały dla ciebie napis. Pochylając się nad skrzynią dostrzegasz wyraźnie napis na niej. Głosi on:<br /><i>Lżejszy od puchu<br />Widoczny tylko w zimie<br />Lecz potężną siłę posiada<br />Nawet Troll go nie zatrzyma</i><br />Domyślasz się, że aby otworzyć skrzynię, musisz odpowiedzieć na pytanie. Obok napisu dostrzegasz jeszcze 5 niewielkich zadrapań.', 'pl'),
(324, 5, 'grid.php', 'answer1', '0', '<i>Lżejszy od puchu<br />Widoczny tylko w zimie<br />Lecz potężną siłę posiada<br />Nawet Troll go nie zatrzyma</i><br />Kiedy wypowiadałeś odpowiedź, zauważyłeś że jedna kreska zniknęła a mimo to skrzynia ani drgnęła. Więc to chyba była zła odpowiedź. Zaczynasz zastanawiać się nad kolejną.', 'pl'),
(325, 5, 'grid.php', 'answer2', '0', 'Kiedy wypowiedziałeś po raz piąty słowo będące twoim zdaniem rozwiązaniem tej zagadki, ostatnia kreska zniknęła, napis pojaśniał by po chwili ponownie przemienić się w zadrapania. Próbowałeś jeszcze parę razy przywrócić go, ale niestety okazało się to niemożliwe. Zrezygnowany postanowiłeś powrócić do miasta. Z powrotem przeprawiłeś się przez jezioro, zebrałeś swój ekwipunek i ruszyłeś w drogę powrotną. Okazała się ona nieco trudniejsza niż poprzednio - pewnie dlatego że tym razem musiałeś iść pod górkę. W połowie drogi urządziłeś sobie chwilowy odpoczynek. Kiedy nieco odpocząłeś, ponownie kierujesz się do skrzyżowania. Przez chwilę odpoczywasz na skrzyżowaniu, następnie zbierasz swój ekwipunek i udajesz się w kierunku wyjścia z owego tunelu. Wstajesz więc z ziemi i z powrotem kierujesz się znanym ci już korytarzem. Szybkim krokiem kierujesz się do znanej ci już wcześniej części korytarza. Po jakimś czasie docierasz do skrzyżowania. Droga powrotna jest nieco trudniejsza, gdyż prowadzi lekko pod górę. W połowie znanego ci już tunelu robisz sobie krótki postój na zebranie sił. Po chwili ruszasz ponownie przed siebie. Po pewnym czasie docierasz do wyjścia z tunelu. Przez chwilę odpoczywasz niedaleko wejścia do niego. Następnie zbierasz swój ekwipunek i kierujesz się w stronę wyjścia z labiryntu. Kiedy docierasz do niego, w twoje oczy uderza blask dawno nie widzianego słońca.', 'pl'),
(326, 5, 'grid.php', 'answer3', '0', 'Kiedy wypowiedziałeś słowo <i>Oddech</i>, na moment cały napis zaczął mocniej świecić by po chwili zgasnąć. W tym momencie bezszelestnie uniosło się wieko skrzyni. Kiedy zajrzałeś do środka, zauważyłeś na dnie kawałek starego pergaminu. Ostrożnie podnosząc go, stwierdzasz że to kawałek starożytnej mapy! Delikatnie bierzesz go do ręki i wyruszasz w drogę powrotną. Z powrotem przeprawiłeś się przez jezioro, zebrałeś swój ekwipunek i ruszyłeś w drogę powrotną. Okazała się ona nieco trudniejsza niż poprzednio - pewnie dlatego że tym razem musiałeś iść pod górkę. W połowie drogi urządziłeś sobie chwilowy odpoczynek. Kiedy nieco odpocząłeś, ponownie kierujesz się do skrzyżowania. Przez chwilę odpoczywasz na skrzyżowaniu, następnie zbierasz swój ekwipunek i udajesz się w kierunku wyjścia z owego tunelu. Wstajesz więc z ziemi i z powrotem kierujesz się znanym ci już korytarzem. Szybkim krokiem kierujesz się do znanej ci już wcześniej części korytarza. Po jakimś czasie docierasz do skrzyżowania. Droga powrotna jest nieco trudniejsza, gdyż prowadzi lekko pod górę. W połowie znanego ci już tunelu robisz sobie krótki postój na zebranie sił. Po chwili ruszasz ponownie przed siebie. Po pewnym czasie docierasz do wyjścia z tunelu. Przez chwilę odpoczywasz niedaleko wejścia do niego. Następnie zbierasz swój ekwipunek i kierujesz się w stronę wyjścia z labiryntu. Kiedy docierasz do niego, w twoje oczy uderza blask dawno nie widzianego słońca.', 'pl'),
(327, 5, 'grid.php', '1.2.3', '0', 'Korytarz wije się raz w jedną, raz w drugą stronę. Sprawia to iż nie jesteś do końca pewien, co też cię czeka. Z tego też względu wędrujesz bardzo ostrożnie, z napięciem obserwując okolicę w poszukiwaniu niebezpieczeństw. Poruszając się tak przez jakiś czas w pewnym momencie, słyszysz gdzieś przed sobą jakiś podejrzany dźwięk. Niestety ze względu na zakręcający korytarz, nie jesteś w stanie stwierdzić co to było. Z nerwami napiętymi do ostatnich granic, powoli podchodzisz do załomu korytarza. Kiedy zaglądasz za jego róg widzisz kilka kroków przed sobą równie zdziwionego jak ty Orka! Natychmiast otrząsacie się z zaskoczenia. Ork, warknął tylko, wyciągnął szablę i błyskawicznie ruszył w twoim kierunku.', 'pl'),
(328, 5, 'grid.php', 'lostfight2', '0', 'Przez pewien czas próbowałeś powstrzymać ataki Orka, ale jego wprawa w posługiwaniu się bronią, była znacznie większa niż twoje doświadczenie w walce. Raz po raz, zadając cios, pozbawiał cię sił. W pewnym momencie jedyne co mogłeś tylko zrobić to ugiąć kolana i paść na ziemię. Ostatnią rzeczą jaką zapamiętałeś to szyderczy grymas na twarzy Orka. Potem nastąpiła ciemność.', 'pl');
INSERT INTO `quests` (`id`, `qid`, `location`, `name`, `option`, `text`, `lang`) VALUES 
(329, 5, 'grid.php', 'escape2', '0', 'Błyskawicznie odwróciłeś się na pięcie i pędem pognałeś z powrotem korytarzem. Za sobą usłyszałeś triumfalny wrzask zwycięstwa Orka a następnie odgłos podkutych butów uderzających o kamienną posadzkę. Ã“w dźwięk dodał ci sił do ucieczki. Błyskawicznie docierasz do skrzyżowania a nastepnie równie szybko kierujesz się w stronę wyjścia z owego tunelu. Przerażony nie oglądasz się za siebie, wydaje ci się, że potwór cały czas biegnie za tobą. W ten sposób docierasz do korytarza prowadzącego pod górę. Tutaj dopiero kompletnie zmęczony przystajesz na moment. Płuca pracują ci niczym miechy kowalskie. Ostrożnie odwracasz się, ale nigdzie nie dostrzegasz potwora. Odpoczywasz jakiś czas. Jednego jesteś pewnien - na pewno nie wrócisz do tego korytarza - gdzieś tam w mroku czai się bestia czyhająca na ciebie. Po pewnym czasie zbierasz swój ekwipunek i kierujesz się z powrotem do wyjścia labiryntu - wystarczy ci wrażeń jak na jeden dzień. Droga powrotna jest nieco trudniejsza, gdyż prowadzi lekko pod górę. W połowie znanego ci już tunelu robisz sobie krótki postój na zebranie sił. Po chwili ruszasz ponownie przed siebie. Po pewnym czasie docierasz do wyjścia z tunelu. Przez chwilę odpoczywasz niedaleko wejścia do niego. Następnie zbierasz swój ekwipunek i kierujesz się w stronę wyjścia z labiryntu. Kiedy docierasz do niego, w twoje oczy uderza blask dawno nie widzianego słońca.', 'pl'),
(330, 5, 'grid.php', 'winfight2', '0', 'Jeszcze tylko jeden cios i potwór który stanął ci na drodze padł martwy. Przez moment stoisz w miejscu ciężko dysząc ze zmęczenia, to niespodziewane wydarzenie nieco nadszarpnęło twoje nerwy. Przez kilka chwil dochodzisz do siebie. Szybko uświadamiasz sobie, że Ork mógł nie być sam. Jednak kiedy nasłuchujesz, dookoła otacza cię tylko martwa cisza. Zbierasz więc swój rozrzucony podczas walki ekwipunek i z jeszcze większą ostrożnością niż wcześniej zacznynasz przemierzać korytarz. ', 'pl'),
(331, 5, 'grid.php', '1.2.3.next', '0', 'Prowadzi on długi czas przed siebie, wijąc się to w lewo to w prawo. Przed każdym zakrętem, pełen najgorszych przeczuć ostrożnie badasz co kryje się za rogiem. Na szczęście nie spotykasz nic niebezpiecznego. Po jakimś czasie widzisz iż korytarz kończy się. Kiedy podchodzisz bliżej, Na wschodniej ścianie dostrzegasz dużą płaskorzeźbę przedstawiającą prawdopodobnie jakąś osadę istot rozumnych z dawnych czasów. Uważnie przyglądasz się ścianie.', 'pl'),
(332, 5, 'grid.php', 'int5', '0', 'Niestety nie dostrzegasz nic ciekawego. Na wszelki wypadek sprawdzasz jeszcze pozostałe ściany. Te też nie mają nic do ukrycia. Zdegustowany postanawiasz zawrócić do miasta. Idąc tym samym korytarzem którym tutaj przyszedłeś, dodatkowo badasz wszystkie ściany w poszukiwaniu ukrytych przejść. Szczególnie bacznie obserwujesz miejsce w którym natknąłeś się na Orka. Niestety nic nie znajdujesz. Po jakimś czasie dochodzisz do skrzyżowania. Przez chwilę odpoczywasz na skrzyżowaniu, następnie zbierasz swój ekwipunek i udajesz się w kierunku wyjścia z owego tunelu. Szybkim krokiem kierujesz się do znanej ci już wcześniej części korytarza. Po jakimś czasie docierasz do skrzyżowania. Droga powrotna jest nieco trudniejsza, gdyż prowadzi lekko pod górę. W połowie znanego ci już tunelu robisz sobie krótki postój na zebranie sił. Po chwili ruszasz ponownie przed siebie. Po pewnym czasie docierasz do wyjścia z tunelu. Przez chwilę odpoczywasz niedaleko wejścia do niego. Następnie zbierasz swój ekwipunek i kierujesz się w stronę wyjścia z labiryntu. Kiedy docierasz do niego, w twoje oczy uderza blask dawno nie widzianego słońca.', 'pl'),
(333, 5, 'grid.php', 'int6', '0', 'Uważnie przyglądając się płaskorzeźbie, zauważasz iż jedna z chmur jakby nie pasowała do reszty. Kiedy zaczynasz przyglądać się jej bliżej, dostrzegasz i można ją lekko wcisnąć w ścianę. Przez chwilę stoisz niezdecydowany, ale w końcu twoja ciekawość bierze górę nad ostrożnością. Kiedy naciskasz rzeźbę, po swojej lewej stronie słyszysz szuranie. Momentalnie odwracasz się w tamtą stronę i widzisz jak północna ściana znika w suficie. Przed tobą czernieje otwór kolejnego korytarza. Stoisz tak przez moment, by po chwili poprawić swój ekwipunek i ruszyć na badanie tego tajemnego przejścia.', 'pl'),
(334, 5, 'grid.php', '1.2.3.next2', '0', 'Powoli idziesz przed siebie. Korytarz którym obecnie wędrujesz jest znacznie węższy i niższy od tych, którymi szedłeś do tej pory. Kierujesz się cały czas przed siebie, pilnie rozglądając się na boki w poszukiwaniu niespodzianek. Po jakimś czasie, widzsz iż korytarz prowadzący prosto kończy się, za to rodziela się na dwa inne prowadzące na boki, tworząc tym samym skrzyżowanie w kształcie litery T. Chwilę stoisz niezdecydowany. Co postanawiasz?', 'pl'),
(335, 5, 'grid.php', 'box11', '1', 'Iść na zachód', 'pl'),
(336, 5, 'grid.php', 'box11', '2', 'Iść na wschód', 'pl'),
(337, 5, 'grid.php', 'box11', '3', 'Zawrócić', 'pl'),
(338, 5, 'grid.php', 'box12', '1', 'Iść na wschód', 'pl'),
(339, 5, 'grid.php', 'box12', '2', 'Zawrócić', 'pl'),
(340, 5, 'grid.php', '1.2.3.3', '0', 'Wystarczy ci niespodzianek jak na ten jeden raz. Postanawiasz zawrócić z powrotem do miasta. Przez chwilę odpoczywasz, nastepnie odwracasz się i ruszasz w drogę powrotną. Po jakimś czasie dochodzisz do tajemnego przejścia, przechodzisz przez nie. Idąc tym samym korytarzem którym tutaj przyszedłeś, dodatkowo badasz wszystkie ściany w poszukiwaniu ukrytych przejść. Szczególnie bacznie obserwujesz miejsce w którym natknąłeś się na Orka. Niestety nic nie znajdujesz. Po jakimś czasie dochodzisz do skrzyżowania. Przez chwilę odpoczywasz na skrzyżowaniu, następnie zbierasz swój ekwipunek i udajesz się w kierunku wyjścia z owego tunelu. Szybkim krokiem kierujesz się do znanej ci już wcześniej części korytarza. Po jakimś czasie docierasz do skrzyżowania. Droga powrotna jest nieco trudniejsza, gdyż prowadzi lekko pod górę. W połowie znanego ci już tunelu robisz sobie krótki postój na zebranie sił. Po chwili ruszasz ponownie przed siebie. Po pewnym czasie docierasz do wyjścia z tunelu. Przez chwilę odpoczywasz niedaleko wejścia do niego. Następnie zbierasz swój ekwipunek i kierujesz się w stronę wyjścia z labiryntu. Kiedy docierasz do niego, w twoje oczy uderza blask dawno nie widzianego słońca.', 'pl'),
(341, 5, 'grid.php', '1.2.3.1', '0', 'Postanawiasz skręcić na zachód, aby dokładniej zbadać tę część labiryntu. Ta odnoga nie wyróżnia się niczym szczególnym. Ostrożnie badasz otoczenie wokół siebie w poszukiwaniu niespodzianek. Po kilku chwilach, widzisz że korytarz kończy się a przed sobą masz niewielką komnatę. Ostrożnie zaglądasz do środka. Widzisz pomieszczenie o wymiarach kilkunastu kroków szerokości i tyleż długości. Po przeciwnej stronie pomieszczenia dostrzegasz leżący na ziemi podłóżny przedmiot. Kiedy ostrożnie podchodzisz bliżej widzisz iż jest to niewielka skrzynia bez zamka. Ostrożnie podnosisz wieko skrzyni. Wewnątrz widzisz stos złotych monet! Zaczynasz zgarniać je do plecaka, przeliczając jednocześnie ile ich jest. W ten sposób zdobywasz 1000 złotych monet.', 'pl'),
(342, 5, 'grid.php', '1.2.3.1.next', '0', 'Poruszony znaleziskiem, dokładnie przeszukujesz całe pomieszczenie w poszukiwaniu innych skarbów. Niestety nic ciekawego nie znajdujesz tutaj. Przez chwilę odpoczywasz, by następnie odwrócić się i ponownie skierować swoje kroki w kierunku skrzyżowania. Jako że już dobrze znasz korytarz, szybkim krokiem docierasz na miejsce.', 'pl'),
(343, 5, 'grid.php', '1.2.3.2', '0', 'Postanowiłeś zbadać wchodnią odnogę korytarza. Ostrożnie wchodzisz do niego. Na moment zatrzymujesz się, aby sprawdzić czy nie dobiegają z niego podejrzane odgłosy. Po chwili ruszasz w dalszą drogę. Okazuje się, że korytarz prowadzący w tę stronę ma długość zaledwie kilkudziesięciu kroków. Kiedy zbliżasz się do jego końca, zauważasz leżący na ziemi szkielet jakiegoś nieszczęśnika, który zaginął w tych korytarzach. Ostrożnie podchodzisz bliżej. W świetle pochodni widzisz iż między kośćmi coś błyszczy. Delikatnie nogą rozgarniasz szczątki. Pod nimi widzisz całkiem dobrą żelazną kolczugę. Bierzesz ją w ręce i chowasz do plecaka. Następnie rozpoczynasz dokładne przeszukanie całego korytarza. Cal po calu badasz ściany w poszukiwaniu ukrytych skarbów, przycisków czy przejść. Niestety nic nie znajdujesz. Zrezygnowany i zmęczony długą podróżą po labiryncie postanawiasz wrócić do miasta. Szybkim krokiem wracasz do skrzyżowania. Przez chwilę odpoczywasz, nastepnie odwracasz się i ruszasz w drogę powrotną. Po jakimś czasie dochodzisz do tajemnego przejścia, przechodzisz przez nie. Idąc tym samym korytarzem którym tutaj przyszedłeś, dodatkowo badasz wszystkie ściany w poszukiwaniu ukrytych przejść. Niestety nic nie znajdujesz. Po jakimś czasie dochodzisz do skrzyżowania. Przez chwilę odpoczywasz na skrzyżowaniu, następnie zbierasz swój ekwipunek i udajesz się w kierunku wyjścia z owego tunelu. Szybkim krokiem kierujesz się do znanej ci już wcześniej części korytarza. Po jakimś czasie docierasz do skrzyżowania. Droga powrotna jest nieco trudniejsza, gdyż prowadzi lekko pod górę. W połowie znanego ci już tunelu robisz sobie krótki postój na zebranie sił. Po chwili ruszasz ponownie przed siebie. Po pewnym czasie docierasz do wyjścia z tunelu. Przez chwilę odpoczywasz niedaleko wejścia do niego. Następnie zbierasz swój ekwipunek i kierujesz się w stronę wyjścia z labiryntu. Kiedy docierasz do niego, w twoje oczy uderza blask dawno nie widzianego słońca.', 'pl'),
(344, 6, 'grid.php', 'start', '0', 'Wędrując korytarzami labiryntu, w pewnym momencie twoją uwagę przykuwa jeden z bocznych korytarzy. Podchodząc do jego wylotu, zaczynasz przyglądać mu się bliżej. Widzisz że jego ściany są bogato rzeźbione. Płaskorzeźby przedstawiają różne sceny z dawnych dni - polowania, wojny, życie codzienne. Sam korytarz jest nieco większy niż ten, którym do tej pory wędrowałeś. Zaintrygowany stoisz przez pewien czas przy wejściu do korytarza, zastanawiając się co dalej robić. Po chwili wybierasz:', 'pl'),
(345, 6, 'grid.php', 'box1', '1', 'Sprawdzić co kryje ten korytarz', 'pl'),
(346, 6, 'grid.php', 'box1', '2', 'Iść dalej starym korytarzem', 'pl'),
(347, 6, 'grid.php', '2', '0', 'Nie pociąga cię ów korytarz aż tak bardzo, by do niego wchodzić. Któż wie, co się w nim kryje? Na wszelki wypadek postanawiasz nie kusić losu. Odwracasz się więc i postanawiasz zawrócić do wejścia labiryntu. Ponieważ znasz już dość dobrze okolicę szybko docierasz do niego.', 'pl'),
(348, 6, 'grid.php', '1', '0', 'Postanawiasz zbadać dokąd prowadzi ów korytarz. Ostrożnie wchodzisz więc do niego, rozglądając się na wszystkie strony. Widzisz iż ciągnie się on cały czas prosto, nie dostrzegasz nigdzie odgałęzień od niego czy zakrętów. Płaskorzeźby na ścianach sprawiają, że czujesz się nieco nieswojo w tym korytarzu - jakby jakieś istoty cały czas patrzyły na ciebie. Powoli wędrujesz więc, bacznie rozglądając się na wszystkie strony w poszukiwaniu niebezpieczeństw.', 'pl'),
(349, 6, 'grid.php', 'int1', '0', 'Idąc tak przed siebie w pewnym momencie twoją uwagę przykuwa jedna z płaskorzeźb znajdujących się po twojej lewej stronie. Ostrożnie podchodząc do niej, przyglądasz się z bliska. Przedstawia ona twarz Krasnoluda patrzącą w kierunku drugiej ściany. Po chwili zauważasz, że otwory, które mają być oczami, są nieco głębsze niż inne znajdujące się w ścianie. Zaczynasz domyślać się, że może to być jakaś pułapka. Kiedy ostrożnie badasz podłogę tuż pod ową rzeźbą, zauważasz, że ten fragment nieco różni się od reszty. Prawdopodobnie jest to jakiś mechanizm uruchamiający ową pułapkę. Ostrożnie przechodzisz nad nim. Przez chwilę odpoczywasz, następnie ponownie wyruszasz na badanie owego tajemniczego korytarza.', 'pl'),
(350, 6, 'grid.php', 'int2', '0', 'Kiedy wędrowałeś tak przed siebie, nagle od swojej lewej strony usłyszałeś cichy trzask. W tym samym momencie poczułeś w swoim lewym boku potworne pieczenie. Kiedy zerknąłeś w tę stronę, zauważyłeś wbite w swoje ciało dwie niewielkie igiełki. Zaczęło nagle kręcić ci się w głowie tak mocno, że musiałeś oprzeć się o ścianę.', 'pl'),
(351, 6, 'grid.php', 'hp1', '0', 'Przez chwilę wydawało ci się, że cały świat dookoła ciebie wiruje a żołądek podchodzi ci do gardła. Po pewnym czasie, wszystko powoli zaczęło wracać do normy. Czujesz się nieco gorzej niż przed wejściem do korytarza, ale na szczęście żyjesz. Domyślasz się, że strzałki były pokryte jakąś trucizną. Na szczęście udało ci się ją zwalczyć. Mimo to tracisz 20 punktów życia. Przez dłuższą chwilę odpoczywasz, by następnie zebrać swój ekwipunek i wyruszyć dalej na zwiedzanie owego tajemniczego korytarza.', 'pl'),
(352, 6, 'grid.php', 'hp2', '0', 'Zawroty głowy stają się coraz silniejsze, zmuszając cię do osunięcia się na ziemię. Twoim ciałem wstrząsają silne drgawki, czujesz że powoli ogarnia cię chłód. Przez pewien czas próbujesz z tym walczyć, ale okazuje się to silniejsze od ciebie. Powoli świat dookoła ciebie ciemnieje, wydaje ci się że zapadasz w sen, który będzie trwał wieki. W tym właśnie miejscu kończy się twoje zwiedzanie labiryntu.', 'pl'),
(353, 6, 'grid.php', '1.next', '0', 'Wędrujesz więc dalej przed siebie, jeszcze uważniej niż poprzednio rozglądając się na boki. Każde podejrzane miejsce korytarza sprawdzasz bardzo dokładnie zanim przejdziesz dalej. Korytarz cały czas prowadzi prosto, jedyne co się na nim zmienia to płaskorzeźby co jakiś czas przedstawiające inną scenę niż poprzednie. Po jakimś czasie dostrzegasz przed sobą iż czerń korytarza jakby rozszerzała się. Kiedy podchodzisz bliżej, widzisz i korytarz kończy się, a przed sobą masz skrzyżowanie. Przez chwilę zastanawiasz się w którą stronę teraz się udać.', 'pl'),
(354, 6, 'grid.php', 'box2', '1', 'Zachód', 'pl'),
(355, 6, 'grid.php', 'box2', '2', 'Północ', 'pl'),
(356, 6, 'grid.php', 'box2', '3', 'Wschód', 'pl'),
(357, 6, 'grid.php', 'box2', '4', 'Zawrócić', 'pl'),
(358, 6, 'grid.php', 'box4', '1', 'Północ', 'pl'),
(359, 6, 'grid.php', 'box4', '2', 'Wschód', 'pl'),
(360, 6, 'grid.php', 'box4', '3', 'Zawrócić', 'pl'),
(361, 6, 'grid.php', 'box7', '1', 'Wschód', 'pl'),
(362, 6, 'grid.php', 'box7', '2', 'Zawrócić', 'pl'),
(363, 6, 'grid.php', '1.4', '0', 'Postanawiasz wrócić z powrotem do miasta - wystarczy ci przygód jak na ten jeden raz. Jeżeli takie niebezpieczeństwa spotkałeś do tej pory, to co też czeka na ciebie dalej. Odpoczywasz więc przez chwilę a następnie kierujesz się w drogę powrotną. Wracają tą samą trasą, którą przyszedłeś, ostrożnie mijasz miejsce gdzie wcześniej natknąłeś się na pułapkę. Nastepnie przyspieszasz kroku i po jakimś czasie docierasz do wylotu korytarza. Tu znów na moment przystajesz aby odpocząć. Po chwili kierujesz się w stronę wyjścia z labiryntu. Kiedy do niego docierasz, w twoje oczy uderza blask niewidzianego od pewnego czasu słońca, zaś do twoich uszu dochodzi gwar rozmów wielu istot. Jesteś z powrotem w mieście.', 'pl'),
(364, 6, 'grid.php', '1.1', '0', 'Korytarz wygląda podobnie to tego, którym wcześniej wędrowałeś - ściany pokryte są płaskorzeźbami z dawnych czasów, przedstawiającymi różne sceny z życia. Jednak ten korytarz jest nieco mniejszy niż poprzedni - bez problemu jeżeli wyciągniesz ręce nad siebie, dotykasz nimi sufitu. Powoli idziesz przed siebie, dokładnie badając ściany oraz podłogę w poszukiwaniu pułapek. Co jakiś czas przystajesz nasłuchując czy przed tobą nie ma jakiś niespodzianek. Ostrożnie kierujesz się naprzód. Po jakimś czasie widzisz iż czerń drogi przed tobą jakby powiększa się. Kiedy powoli podchodzisz bliżej, zauważasz iż korytarz kończy się. Przed tobą natomiast znajduje się olbrzymia sala - największa jaką do tej pory widziałeś w labiryncie. W blasku pochodni nie dostrzegasz sufitu ani ścian. Widzisz za to przed sobą rzędy kamiennych, bogato zdobionych kolumn. Wydaje się że pomieszczenie to nie ma końca. W około ciebie panuje martwa cisza. Ostrożnie idąc przed siebie masz wrażenie że jesteś tylko niewielkim skrzatem w domu olbrzymów. Czujesz się bardzo samotnie w tym pomieszczeniu. To wszystko sprawia że twoje nerwy są napięte do ostatnich granic. Cały czas idziesz przed siebie i wydaje ci się że nie istnieje drugi koniec tego pomieszczenia. Po drodze mijasz kolejne rzędy kamiennych kolumn zdobionych w roślinne motywy. Twoje kroki wzbijają niewielkie fontanny kurzu, który zalega podłogę pewnie już od wielu lat. Po jakimś czasie wędrówki, w pewnym momencie odnosisz wrażenie iż nie jesteś w tym pomieszczeniu sam - ktoś cię obserwuje. Jednak kiedy już postanowiłeś zawrócić z powrotem, nieoczekiwanie zauważyłeś że zbliżasz się do końca pomieszczenia. Jednak nie to przykuło twoją uwagę. Z przodu zza dwóch kolumn wyszły dwa potężne Orogi! Na moment przystanęły przyglądając się tobie. Po chwili jeden z nich ryknął i ruszył w twoją stronę. Jego ryk spotęgowany przez echo w sali sparaliżował cię na moment - kiedy się opanowałeś, zostało ci tylko tyle czasu, aby odłożyć na bok zbędny ekwipunek i przygotować się do walki!', 'pl'),
(365, 6, 'grid.php', 'escape1', '0', 'Udało ci się odskoczyć w bok, zebrać szybko zostawiony ekwipunek na ziemi i pędem ruszyć w drogę powrotną. Kiedy zacząłeś biec przed siebie, znów usłyszałeś ryk potwora. Jednak tym razem spowodował on tylko tyle, że jeszcze bardziej przyspieszyłeś. Nagle usłyszałeś za sobą dźwięk przypominający jakby furkot skrzydeł potężnego ptaka. Odruchowo uchyliłeś się i w tym momencie nad twoją głową przeleciała potężna maczuga. W tym momencie zebrałeś wszystkie siły i zacząłeś biec jak nigdy w życiu. Nie zwracając nawet uwagi czy pogoń podąża za tobą w wielkim pędzie wypadłeś z sali do korytarza. Nie zwalniając nawet na moment biegiem docierasz do skrzyżowania. Tutaj dopiero poczułeś jak bardzo jesteś zmęczony. Usiadłeś na moment na ziemi aby zebrać siły. Kiedy tak odpoczywałeś, zauważyłeś iż z korytarza z którego przed chwilą uciekłeś widać zbliżające się z oddali cztery czerwone punkty dość wysoko nad ziemią. Domyślasz się, że to zbliża się pogoń! Zbierasz się szybko z ziemi i kierujesz się w stronę wyjścia z labiryntu. Błyskawicznie idziesz przed siebie, zwalniasz jedynie w okolicy gdzie napotkałeś wcześniej pułapkę. Kiedy odwracasz się, widzisz że pogoń cały czas podąża twoim śladem. Szybko docierasz do wyjścia z korytarza i kierujesz się w stronę wyjścia z labiryntu. Kiedy do niego docierasz, w twoje oczy uderza blask niewidzianego od pewnego czasu słońca, zaś do twoich uszu dochodzi gwar rozmów wielu istot. Jesteś z powrotem w mieście.', 'pl'),
(366, 6, 'grid.php', 'lostfight1', '0', 'Desperacko próbowałeś odpierać ataki bestii, ale pierwotna furia tym razem wygrała. Powoli osuwając się na ziemię, czujesz że nie dasz rady obronić się przed nimi. W zwolnionym tempie zauważyłeś jeszcze opadającą w twoim kierunku potężną maczugę. Po chwili nastała ciemność. Tak kończy się twoje zwiedzanie labiryntu.', 'pl'),
(367, 6, 'grid.php', 'winfight1', '0', 'Twoje doświadczenie w walce przyniosło rezultaty - bez problemu pokonałeś obu przeciwników. Po kilku chwilach walki obaj legli u twych stóp. Stoisz przez moment, odpoczywając po tym zdarzeniu. Po pewnym czasie, zbierasz swój ekwipunek, przechodzisz ponad ciałami przeciwników i ruszasz dalej na zbadanie drogi przed tobą.', 'pl'),
(368, 6, 'grid.php', '1.1.next', '0', 'Ostrożnie wchodzisz do korytarza. Widzisz iż ten korytarz nie jest ozdobiony tak jak poprzednie - wygląda na znacznie nowszą robotę niż sala którą przed chwilą szedłeś. Powoli idziesz przed siebie bacznie sprawdzając otoczenie w poszukiwaniu pułapek czy innych niespodzianek. Korytarz czasami skręca to w lewo to w prawo, jednak mniej więcej cały czas prowadzi na zachód. Po pewnym czasie docierasz do rozwidlenia drogi w kształcie litery T. Przez moment zastanawiasz się, którą drogę teraz wybrać.', 'pl'),
(369, 6, 'grid.php', 'box3', '1', 'Północ', 'pl'),
(370, 6, 'grid.php', 'box3', '2', 'Południe', 'pl'),
(371, 6, 'grid.php', 'box3', '3', 'Zawrócić', 'pl'),
(372, 6, 'grid.php', 'box5', '1', 'Południe', 'pl'),
(373, 6, 'grid.php', 'box5', '2', 'Zawrócić', 'pl'),
(374, 6, 'grid.php', '1.1.3', '0', 'Postanawiasz zostawić w spokoju oba nieznane ci korytarze i zawrócić z powrotem do pierwszego skrzyżowania. Przez chwilę stoisz w miejscu odpoczywając, następnie odwracasz się i ruszasz w drogę powrotną. Po jakimś czasie docierasz z powrotem do sali. Przechodzisz obok ciał Orogów i kierujesz się na drugą stronę sali. Znów przed tobą nużąca podróż wzdłuż kamiennych kolumn. Po pewnym czasie docierasz do korytarza położonego po drugiej stronie sali. Tutaj znów na moment przystajesz aby odpocząć. W tych ciemnościach jedynie światło twojej pochodni w jakiś sposób wyznacza upływ czasu. Jednak nie jesteś pewien ile już go spędziłeś pod ziemią. Po odpoczynku zbierasz swój ekwipunek i wyruszasz dalej przed siebie. Po pewnym czasie docierasz do skrzyżowania. Którą drogę teraz wybierasz?', 'pl'),
(375, 6, 'grid.php', '1.1.1', '0', 'Po chwili namysłu wybierasz drogę na północ. Podobnie jak wcześniejszy korytarz, i ten w swoim wyglądzie nie wyróżnia się niczym szczególnym. Ostrożnie idziesz przed siebie, badając każdy skrawek drogi w poszukiwaniu pułapek i innych niespodzianek. Korytarz cały czas biegnie prosto przed siebie. Jego podłogę pokrywa cienka warstwa kurzu, zmieniającego się w niewielkie obłoczki przy każdym twoim kroku. Co jakiś czas przystajesz w miejscu, nasłuchując czy od przodu nie dobiegają jakieś niepokojące odgłosy. Powoli wędrujesz przed siebie.', 'pl'),
(376, 6, 'grid.php', 'int3', '0', 'Twoja czujność okazała się uzasadniona. W pewnym momencie raczej poczułeś niż zauważyłeś, że coś jest nie tak z podłogą. Nachylając się niżej, delikatnie odgarniasz kurz z podłogi. Przez chwilę krztusisz się od kurzu. Jednak twój wysiłek na coś się przydał - widzisz przed sobą niewielką, ledwie widoczną płytę w chodniku korytarza. Kiedy uważniej rozglądasz się na wszystkie strony, zauważasz na suficie coś w rodzaju dużej, solidnej klapy. Domyślasz się, że może to być jakaś pułapka. Ostrożnie wymijasz podejrzany fragment podłogi i kierujesz się dalej przed siebie.', 'pl'),
(377, 6, 'grid.php', 'int4', '0', 'Kiedy tak szedłeś przed siebie, nagle od strony podłogi usłyszałeś cichy trzask. Prawie jednocześnie z nim, nad twoją głową rozległ się prawie taki sam odgłos. Spojrzałeś w górę i zamarłeś. Z przerażeniem zauważyłeś jak część sufitu rozstępuje się a w twoim kierunku lecą z góry olbrzymie głazy! To była ostatnia rzecz jaką zapamiętałeś zanim pierwszy z potężnych kamieni uderzył cię w głowę pozbawiając przytomności. Twoje zwiedzanie labiryntu zakończyło się pod stertą skał. Może następnym razem bogowie będą ci bardziej przychylni.', 'pl'),
(378, 6, 'grid.php', '1.1.1.next', '0', 'Po kilku chwilach wędrówki widzisz, że korytarz powoli rozszerza się, by po jakimś czasie, płynnie przejść w niewielką podziemną komnatę. Ostrożnie rozglądasz się na wszystkie strony, ale nie zauważasz nic podejrzanego. Za to twoją ciekawość wzbudza skrzynia stojąca tuż przy tobie. Kiedy podchodzisz bliżej, zauważasz że jest to solidna, okuta skrzynia zamknięta na zamek. Przyklękasz obok niej i zaczynasz dokłaniej badać znalezisko. Twoją uwagę przykuwa przede wszystkim zamknięcie skrzyni. Zamek wydaje ci się dość prosty, więc postanawiasz zaryzykować i spróbować otworzyć skrzynię.', 'pl'),
(379, 6, 'grid.php', 'agi1', '0', 'Chwilę majstrowałeś przy zamku. W końcu usłyszałeś dźwięk na który czekałeś - zamek stanął otworem, ostrożnie, obawiając się pułapki otwierasz skrzynię. W pierwszej chwili przeżywasz rozczarowanie - skrzynia jest pusta, tylko kilka starych szmat. Jednak kiedy zacząłeś rozgarniać owe szmaty, twoim oczom ukazał się bardzo dobrze zachowany Elfi żelazny miecz. Szybko bierzesz owe znalezisko i chowasz je do plecaka. Przez moment postanawiasz odpocząć. Następnie zbierasz swój ekwipunek i z powrotem wracasz do skrzyżowania. Po drodze zachowujesz szczególną czujność w miejscu gdzie wcześniej natknąłeś się na pułapkę. Po pewnym czasie jesteś z powrotem w znanym ci już miejscu. W którą stronę teraz chcesz się udać?', 'pl'),
(380, 6, 'grid.php', 'agi2', '0', 'Majstrujesz jakiś czas przy zamku. Jednak nie zauważasz aby twoje wysiłki na coś się zdały. Po kilku chwilach zmęczony postanawiasz przez moment odpocząć. Siadasz więc przed skrzynią i zaczynasz się jej ponownie przyglądać. Po chwili, kiedy nieco już odpocząłeś, ponownie zabierasz się za rozbrojenie tego zamka.', 'pl'),
(381, 6, 'grid.php', 'agi3', '0', 'Kiedy tak grzebałeś w zamku, nagle usłyszałeś jakiś trzask. W pierwszej chwili wydawało ci się, że to w końcu ty otworzyłeś zamek. Jednak wkrótce ogarnęły ciebie złe przeczucia - majstrując przy zamku, spowodowałeś jego uszkodzenie - teraz skrzyni nie da się w ogóle otworzyć. Poczułeś się mocno zawiedziony. Szybko zebrałeś swój ekwipunek i ruszyłeś w drogę powrotną do skrzyżowania. Po drodze zachowujesz szczególną czujność w miejscu gdzie wcześniej natknąłeś się na pułapkę. Po pewnym czasie jesteś z powrotem w znanym ci już miejscu. W którą stronę teraz chcesz się udać?', 'pl'),
(382, 6, 'grid.php', '1.1.2', '0', 'Postanawiasz udać się na południe. Korytarz prowadzący w tę stronę wygląda tak samo jak ten, którym przywędrowałeś tutaj. Gładkie kamienne ściany, wszystko wykute w jednym kawałku skały. Ostrożnie posuwasz się przed siebie, bacznie rozglądając się na wszystkie strony w poszukiwaniu pułapek. Korytarz nie jest długi, po kilku chwilach docierasz do jego końca. Nieco zawiedziony, zaczynasz rozglądać się uważnie na wszystkie strony w poszukiwaniu tajemnych przejść.', 'pl'),
(383, 6, 'grid.php', 'int5', '0', 'Uważnie przyglądając się południowej ścianie zauważasz niewielki przycisk tuż przy ziemi. Ostrożnie naciskasz go. Kiedy usłyszałeś cichy trzask, odruchowo odskoczyłeś od ściany. Jednak twój niepokój okazał się nieuzasadniony - widzisz jak fragment ściany podnosi się, otwierając przejście do dalszej części korytarza. Po chwili zastanowienia, postanawiasz spenetrować dalszą część korytarza.', 'pl'),
(384, 6, 'grid.php', 'int6', '0', 'Przez pewien czas sprawdzałeś cal po calu każdy fragment ścian oraz podłogi. Niestety nie znalazłeś nic ciekawego. Przez chwilę stoisz zawiedziony pod ścianą. Następnie zbierasz swój ekwipunek i postanawiasz opuścić to miejsce. Szybkim krokiem kierujesz się do skrzyżowania. Kiedy do niego docierasz, zatrzymujesz się na moment aby odpocząć. Następnie odwracasz się i ruszasz w drogę powrotną. Po jakimś czasie docierasz z powrotem do sali. Przechodzisz obok ciał Orogów i kierujesz się na drugą stronę sali. Znów przed tobą nużąca podróż wzdłuż kamiennych kolumn. Po pewnym czasie docierasz do korytarza położonego po drugiej stronie sali. Tutaj znów na moment przystajesz aby odpocząć. W tych ciemnościach jedynie światło twojej pochodni w jakiś sposób wyznacza upływ czasu. Jednak nie jesteś pewien ile już go spędziłeś pod ziemią. Po odpoczynku zbierasz swój ekwipunek i wyruszasz dalej przed siebie. Po pewnym czasie docierasz do skrzyżowania. Którą drogę teraz wybierasz?', 'pl'),
(385, 6, 'grid.php', '1.1.2.next', '0', 'Ostrożnie przekraczasz przejście. Przed sobą widzisz wąski korytarz prowadzący gdzieś naprzód. Powoli zwiedzasz go, bacznie rozglądając się na boki i przystając co jakiś czas aby nasłuchiwać odgłosów dobiegających z dalszej części korytarza. Jednak nic ciekawego się nie dzieje. Idziesz tak przez jakiś okres czasu. W końcu docierasz do końca owego tajemniczego korytarza. Widzisz że pod jedną ze ścian coś leży na ziemi. Kiedy podchodzisz bliżej zauważasz że jest to szkielet prawdopodobnie krasnoluda. Na ścianie przy której leży widać ślady zarysowań. Obok szkieletu walają się resztki jakiś szmat oraz przeżarty rdzą, poszczerbiony topór. Przeglądając uważnie owe szmaty, znajdujesz niewielki mieszek. Kiedy podnosisz go z ziemi, mieszek rozsypuje się, a z jego wnętrza wypadają złote monety. Szybko zbierasz je do swojej sakiewki. W ten sposób zdobywasz 200 sztuk złota. Na wszelki wypadek badasz jeszcze ściany w poszukiwaniu kolejnych przejść. Jednak nic ciekawego nie znajdujesz. Odwracasz się więc i postanawiasz zawrócić. Szybkim krokiem kierujesz się do wyjścia. Po jakimś czasie przechodzisz przez drzwi do tajnego korytarza i kierujesz się ku skrzyżowaniu. Kiedy do niego docierasz, zatrzymujesz się na moment aby odpocząć. Następnie odwracasz się i ruszasz w drogę powrotną. Po jakimś czasie docierasz z powrotem do sali. Przechodzisz obok ciał Orogów i kierujesz się na drugą stronę sali. Znów przed tobą nużąca podróż wzdłuż kamiennych kolumn. Po pewnym czasie docierasz do korytarza położonego po drugiej stronie sali. Tutaj znów na moment przystajesz aby odpocząć. W tych ciemnościach jedynie światło twojej pochodni w jakiś sposób wyznacza upływ czasu. Jednak nie jesteś pewien ile już go spędziłeś pod ziemią. Po odpoczynku zbierasz swój ekwipunek i wyruszasz dalej przed siebie. Po pewnym czasie docierasz do skrzyżowania. Którą drogę teraz wybierasz?', 'pl'),
(386, 6, 'grid.php', '1.2', 'góra', 'Ściany korytarza, podobnie jak i wcześniejszego pokryte są bogato zdobionymi płaskorzeźbami, przedstawiającymi różne sceny z życia w dawnych czasach. Nie jesteś w stanie jasno stwierdzić, kiedy powstały owe portrety, jednak podejrzewasz, że musiało to być wiele setek lat temu. Korytarz co jakiś czas skręca to w lewo to w prawo, jednak ogólnie cały czas prowadzi na północ. Owe zakręty sprawiają iż nie jesteś do końca pewny co dokładnie znajduje się przed tobą. Pokrywająca korytarz cienka warstwa kurzu, zmienia się w niewielkie obłoczki przy każdym twoim kroku. Droga jest dość szeroka a sufit znajduje się wysoko nad twoją głową. Idziesz cały czas powoli przed siebie, bacznie rozglądając się na wszystkie strony. Co kilka chwil przystajesz nasłuchując. Jednak jak na razie, na swej drodze nie natykasz się na jakiekolwiek niebezpieczeństwa. Po pewnym czasie, czując się nieco zmęczony, postanawiasz odpocząć przez moment. Siadasz więc sobie przy ścianie i uważniej przyglądasz się pokrywającym ją rzeźbom. Zauważasz że te akurat przedstawiają sceny polowań na dawno już wymarłe zwierzęta. Przez jakiś czas przyglądasz się owym kamiennym postaciom, podziwiając kunszt dawnych mistrzów. Kiedy odpocząłeś sobie co nieco, postanawiasz wyruszyć dalej. Okazuje się że po krótkim okresie czasu, docierasz do końca korytarza. W północnej ścianie widzisz bardzo dziwne drzwi. Nigdzie ani śladu klamki czy choćby zamka. Mimo to, kiedy próbowałeś je otworzyć, okazały się być zamknięte. Kiedy podnosisz nieco wyżej pochodnię, aby dokładniej przyjrzeć się otoczeniu, zauważasz nad drzwiami jakiś napis. Głosi on: <br /><i>Ta rzecz głębokie korzenie miewa,<br />wyższa jest niźli drzewa,<br />ku niebu sięga wyniośle,<br />chociaż ani piędzi nie rośnie</i><br />Zaczynasz domyślać się, że aby przejść dalej, musisz wpierw odpowiedzieć na ową zagadkę.', 'pl'),
(387, 6, 'grid.php', 'answer1', '0', '<i>Ta rzecz głębokie korzenie miewa,<br />wyższa jest niźli drzewa,<br />ku niebu sięga wyniośle,<br />chociaż ani piędzi nie rośnie</i><br />Kiedy wypowiadałeś słowo będące twoim zdaniem rozwiązaniem owej zagadki, drzwi ani drgnęły. Więc jednak to nie była prawidłowa odpowiedź. Zaczynasz więc intensywnie myśleć nad kolejną odpowiedzią.', 'pl'),
(388, 6, 'grid.php', 'answer2', '0', 'Kiedy po raz piąty podałeś słowo, które uważasz za rozwiązanie, drzwi jakby wgłębiły się w ścianę. Okazuje się że teraz zupełnie nie da się ich otworzyć. Zrezygnowany stoisz jakiś czas przed nimi w nadziei że jednak uda się je jakoś otworzyć. Niestety nic nie przychodzi ci do głowy. Zbierasz więc swój ekwipunek i zawracasz w kierunku skrzyżowania. Po drodze znów urządzasz sobie w pewnym momencie odpoczynek. Nie jesteś dokładnie w stanie stwierdzić ile już przebywasz w labiryncie. W tych ciemnościach jedynie światło twojej pochodni w jakiś sposób wyznacza upływ czasu. Jednak nie jesteś pewien ile już go spędziłeś pod ziemią. Po odpoczynku zbierasz swój ekwipunek i wyruszasz dalej przed siebie. Po pewnym czasie docierasz do skrzyżowania. Którą drogę teraz wybierasz?', 'pl'),
(389, 6, 'grid.php', 'answer3', '0', 'Kiedy wypowiedziałeś słowo <i>Góra</i> usłyszałeś lekki trzask od strony drzwi. Ostrożnie zbliżyłeś się do nich i lekko pchnąłeś ręką. Okazało się, że da się je otworzyć! Uradowany, zbierasz swój ekwipunek i przechodzisz na drugą stronę. Korytarz ten wygląda jak dalsza część drogi, którą wcześniej wędrowałeś. Również pokryty jest płaskorzeźbami. Robisz zaledwie parę kroków, kiedy widzisz przed sobą skrzyżowanie. Zastanawiasz się, którą drogę teraz wybrać.', 'pl'),
(390, 6, 'grid.php', 'box6', '1', 'Zachód', 'pl'),
(391, 6, 'grid.php', 'box6', '2', 'Wschód', 'pl'),
(392, 6, 'grid.php', 'box6', '3', 'Północ', 'pl'),
(393, 6, 'grid.php', 'box6', '4', 'Zawrócić', 'pl'),
(394, 6, 'grid.php', 'box8', '1', 'Wschód', 'pl'),
(395, 6, 'grid.php', 'box8', '2', 'Północ', 'pl'),
(396, 6, 'grid.php', 'box8', '3', 'Zawrócić', 'pl'),
(397, 6, 'grid.php', 'box9', '1', 'Północ', 'pl'),
(398, 6, 'grid.php', 'box9', '2', 'Zawrócić', 'pl'),
(399, 6, 'grid.php', '1.2.4', '0', 'Postanawiasz zawrócić z powrotem do głównego skrzyżowania. Nie jesteś za bardzo zainteresowany tym co znajduje się w tej części labiryntu. Szybko wracasz więc przez drzwi i udajesz się w drogę powrotną. Zbierasz więc swój ekwipunek i zawracasz w kierunku skrzyżowania. Po drodze znów urządzasz sobie w pewnym momencie odpoczynek. Nie jesteś dokładnie w stanie stwierdzić ile już przebywasz w labiryncie. W tych ciemnościach jedynie światło twojej pochodni w jakiś sposób wyznacza upływ czasu. Jednak nie jesteś pewien ile już go spędziłeś pod ziemią. Po odpoczynku zbierasz swój ekwipunek i wyruszasz dalej przed siebie. Po pewnym czasie docierasz do skrzyżowania. Którą drogę teraz wybierasz?', 'pl'),
(400, 6, 'grid.php', '1.2.1', '0', 'Postanawiasz sprawdzić korytarz prowadzący na zachód. Kiedy skręcasz w tę stronę, uważniej przyglądasz się tej odnodze. Zauważasz, że mimo iż wygląda na tak starą jak wcześniejsze korytarze którymi wędrowałeś, na jej ścianach nie ma jakichkolwiek ozdób. Podłogę, podobnie jak i w innych korytarzach pokrywa cienka warstewka kurzu, zmieniająca się w delikatną mgiełkę przy każdym twoim kroku. Ostrożnie idziesz przed siebie, badając każdy skrawek otoczenia. Po jakimś czasie docierasz do końca owego korytarza. W nadziei na znalezienie czegoś ciekawego, uważnie sprawdzasz ściany i podłogę. Niestety nic nie znajdujesz - wygląda to na nieukończony korytarz. Zawiedziony postanawiasz zawrócić z powrotem do skrzyżowania. Ruszasz więc szybkim krokiem w drogę powrotną i wkrótce docierasz ponownie na rozstaje. Którą drogą teraz chcesz podążać?', 'pl'),
(401, 6, 'grid.php', '1.2.2', 'pszczoły', 'Wybierasz korytarz prowadzący na wschód. Jest on nieco mniejszy od głównego korytarza, jednak równie bogato zdobiony jak tamten. Ze zdziwieniem zauważasz iż na podłodze nie ma ani grama kurzu - tak jakby ktoś opiekował się tym miejscem. To wzmacnia twoją czujność. Ostrożnie, krok po kroku wędrujesz korytarzem, badając uważnie okolicę w poszukiwaniu pułapek. Co jakiś czas przystajesz i próbujesz przebić wzrokiem ciemność przed tobą aby przekonać się co też kryje się dalej na drodze. Na razie jednak nic ciekawego się nie dzieje. Idziesz tak przez jakiś czas. Po pewnym czasie zauważasz, że korytarz zaczyna się zwężać. Po jakimś czasie jest tak wąski, że musisz przechodzić bokiem przez niego. Nagle ściany korytarza znów się rozszerzają. Kiedy uważnie rozglądasz się w około, zauważasz że znalazłeś się w niewielkiej komnacie. Jej ściany są pokryte starożytnymi rysunkami przedstawiającymi różne wymarłe już istoty i bestie. Podobnie jak i w całym korytarzu, na podłodze nie zauważasz cienkiej warstwy kurzu, jaka towarzyszy ci prawie przez cały czas wędrówek w labiryncie. W jednym z rogów komnaty widzisz niewielki, podłużny przedmiot leżący na ziemi. Kiedy podchodzisz bliżej, stwierdzasz że jest to najdziwniejsza skrzynia jaką widziałeś. Wygląda jak wykonana z jednego kawałka bardzo ciemnego drewna. Nigdzie nie widzisz nawet śladów zamka czy zawiasów. Jedynie na wieku dostrzegasz jakieś dziwne zadrapania. Kiedy delikatnie dotykasz tego miejsca, nagle zadrapania zaczynają świecić by po chwili w magiczny sposób ułożyć się w zrozumiały dla ciebie napis.<br /><i>Skrzętni murarze murują,<br />w ich murze ludzie smakują.</i><br />Domyślasz się, że aby otworzyć skrzynię, musisz odpowiedzieć na zagadkę. Obok napisu dostrzegasz jeszcze 5 niewielkich zadrapań.', 'pl'),
(402, 6, 'grid.php', 'answer4', '0', '<i>Skrzętni murarze murują,<br />w ich murze ludzie smakują.</i><br />Kiedy wypowiadałeś odpowiedź, zauważyłeś że jedna kreska zniknęła a mimo to skrzynia ani drgnęła. Więc to chyba była zła odpowiedź. Zaczynasz zastanawiać się nad kolejną.', 'pl'),
(403, 6, 'grid.php', 'answer5', '0', 'Kiedy wypowiedziałeś po raz piąty słowo będące twoim zdaniem rozwiązaniem tej zagadki, ostatnia kreska zniknęła, napis pojaśniał by po chwili ponownie przemienić się w zadrapania. Próbowałeś jeszcze parę razy przywrócić go, ale niestety okazało się to niemożliwe. Zrezygnowany postanowiłeś powrócić do skrzyżowania. Wracasz tą samą drogą, którą tutaj przyszedłeś. Kiedy docierasz do skrzyżowania postanawiasz chwilę odpocząć, zanim wyruszysz w dalszą drogę. Masz przed sobą następujące możliwości.', 'pl'),
(404, 6, 'grid.php', 'answer6', '0', 'Kiedy wypowiedziałeś słowo <i>pszczoły</i>, na moment cały napis zaczął mocniej świecić by po chwili zgasnąć. W tym momencie bezszelestnie uniosło się wieko skrzyni. Kiedy zajrzałeś do środka, zauważyłeś na dnie kawałek starego pergaminu. Ostrożnie podnosząc go, stwierdzasz że to kawałek starożytnej mapy! Delikatnie chowasz go do plecaka i wyruszasz w drogę powrotną. Kiedy docierasz do skrzyżowania, postanawiasz odpocząć sobie co nieco. Po pewnym czasie zbierasz swój ekwipunek i ponownie ruszasz w stronę wyjścia z labiryntu. Nie jesteś dokładnie w stanie stwierdzić ile już przebywasz w labiryncie. W tych ciemnościach jedynie światło twojej pochodni w jakiś sposób wyznacza upływ czasu. Jednak nie jesteś pewien ile już go spędziłeś pod ziemią. Po pewnym czasie docierasz do kolejnego skrzyżowania. Tutaj znów urządzasz sobie krótki odpoczynek. Następnie kierujesz się do wyjścia z owego bogato rzeźbionego tunelu. Po drodze ze zmęczenia omal nie zapomniałeś o czyhającej pułapce na drodze. Na szczęście zorientowałeś się zanim w nią wszedłeś. Przy wylocie korytarza znów na moment przystajesz aby odpocząć. Po chwili kierujesz się w stronę wyjścia z labiryntu. Kiedy do niego docierasz, w twoje oczy uderza blask niewidzianego od pewnego czasu słońca, zaś do twoich uszu dochodzi gwar rozmów wielu istot. Jesteś z powrotem w mieście.', 'pl'),
(405, 6, 'grid.php', '1.2.3', '0', 'Korytarz prowadzi na północ, cały czas nieznacznie opadając w dół. Ściany są gładkie, wykonane z jednego fragmentu skały. Jego podłoga w odróżnieniu od wcześniejszych korytarzy składa się z kamiennych płyt pokrytych cienką warstwą kurzu. Ostrożnie idziesz przed siebie, badając każdy fragment otoczenia w poszukiwaniu niespodzianek. Korytarz wije się na wszystkie strony, jednak cały czas mniej więcej prowadzi w tym samym kierunku. Idziesz jakiś czas tą drogą, co chwila przystając i nasłuchując czy z naprzeciwka nie dobiegają jakieś podejrzane odgłosy. Po pewnym czasie widzisz że korytarz kończy się rozwidleniem w kształcie litery T. Przystajesz na moment aby nieco odpocząć a następnie postanawiasz udać się na:', 'pl'),
(406, 6, 'grid.php', 'box10', '1', 'Zachód', 'pl'),
(407, 6, 'grid.php', 'box10', '2', 'Wschód', 'pl'),
(408, 6, 'grid.php', 'box10', '3', 'Zawrócić', 'pl'),
(409, 6, 'grid.php', 'box12', '1', 'Wschód', 'pl'),
(410, 6, 'grid.php', 'box12', '2', 'Zawrócić', 'pl'),
(411, 6, 'grid.php', '1.2.3.3', '0', 'Postanawiasz zawrócić z powrotem do skrzyżowania. Nie interesuje cię za bardzo, co też kryją owe korytarze. Zbierasz więc swój ekwipunek i udajesz się w drogę powrotną. Teraz jest ci nieco ciężej podróżować, ponieważ musisz się delikatnie wspinać pod górę. Kiedy docierasz do skrzyżowania, postanawiasz przez moment odpocząć. Następnie przechodzisz przez drzwi i udajesz się w drogę powrotną. Zbierasz więc swój ekwipunek i zawracasz w kierunku skrzyżowania. Po drodze znów urządzasz sobie w pewnym momencie odpoczynek. Nie jesteś dokładnie w stanie stwierdzić ile już przebywasz w labiryncie. W tych ciemnościach jedynie światło twojej pochodni w jakiś sposób wyznacza upływ czasu. Jednak nie jesteś pewien ile już go spędziłeś pod ziemią. Po odpoczynku zbierasz swój ekwipunek i wyruszasz dalej przed siebie. Po pewnym czasie docierasz do skrzyżowania. Którą drogę teraz wybierasz?', 'pl'),
(412, 6, 'grid.php', '1.2.3.1', '0', 'Po chwili odpoczynku wybierasz drogę na zachód. Ostrożnie wchodzisz w korytarz, uważnie rozglądając się na wszystkie strony i nasłuchując. Krok po kroku idziesz przed siebie. Ściany tunelu są gładkie, nie ma na nich jakichkolwiek zadrapań czy ozdób. Co jakiś czas mijasz niewielkie boczne korytarze. Przy każdym takim odgałęzieniu zatrzymujesz się na moment i nasłuchujesz. Następnie robisz kilkanaście kroków w głąb korytarza. Okazują się one albo ślepymi korytarzami albo małymi odgałęzieniami, które po kilkudziesięciu krokach ponownie łączą się z głównym tunelem. Jednak w pewnym momencie wędrówki, zauważasz na ziemi niepokojące sygnały. W warstwie kurzu zauważasz odciski jakiś butów. Ktoś oprócz ciebie również wędruje tym korytarzem. Ślady wyglądają na dość świeże. Wychodzą one z jednego z owych bocznych tunelów i prowadzą głównym tunelem na zachód. Aby nie ryzykować, postanawiasz zgasić światło pochodni i powoli iść dalej. Wędrujesz tak przez jakiś czas, kiedy do twych uszu docierają jakieś dźwięki. Kiedy robisz kolejnych kilka kroków, zaczynasz rozróżniać wyraźniej dźwięki. Dokładnie słyszysz rozmowę w jakimś języku prawdopodobnie trzech istot. Skulony, powoli idziesz prze siebie. Kiedy mijasz kolejny zakręt, dostrzegasz przed sobą delikatny blask ognia, znajdującego się gdzieś przed tobą. To jeszcze bardziej wzmacnia twoją czujność. Powoli blask staje się coraz silniejszy. Ostrożnie wyglądasz za zakrętu korytarza i dostrzegasz taką oto rzecz. Przed tobą znajduje się dość duże pomieszczenie. Kilkadziesiąt kroków od miejsca w którym stoisz płonie niewielkie ognisko. Przy nim siedzą trzy postacie rozmawiające ze sobą. To Orkowie. Dokładnie badając okolicę stwierdzasz, że możesz spróbować prześlizgnąć się obok nich - komnata jest dość duża, prawdopodobnie udałoby ci się to. Cofasz się za zakręt i zaczynasz zastanawiać się co począć.', 'pl'),
(413, 6, 'grid.php', 'box11', '1', 'Zaatakować', 'pl'),
(414, 6, 'grid.php', 'box11', '2', 'Przekraść się', 'pl'),
(415, 6, 'grid.php', 'box11', '3', 'Zawrócić', 'pl'),
(416, 6, 'grid.php', '1.2.3.1.3', '0', 'Postanawiasz nie kusić losu. Ostrożnie wycofujesz się  kilkanaście kroków w głąb korytarza a następnie szybkim krokiem kierujesz się do skrzyżowania. Nie jesteś do końca pewien ile masz czasu zanim Orkowie odkryją twoje ślady. Po jakimś czasie docierasz z powrotem do rozwidlenia. Tutaj na moment przystajesz aby odpocząć i ponownie wybrać w którą stronę chcesz się teraz udać.', 'pl'),
(417, 6, 'grid.php', '1.2.3.1.2', '0', 'Postanawiasz przejść do działania. Ponownie ostrożnie zbliżasz się do wyjścia z korytarza i bardzo powoli wychodzisz z niego idąc wzdłuż ściany, tak aby ominąć z daleka siedzące przy ognisku postacie. Każdy twój krok wydaje ci się wiecznością. Ostrożnie aby nie wywołać jakiegoś hałasu posuwasz się naprzód tuż przy ścianie. Z odległości kilkudziesięciu kroków widzisz cały czas siedzące i rozmawiające przy ogniu istoty. Powoli idziesz naprzód.', 'pl'),
(418, 6, 'grid.php', 'agi4', '0', 'Bez problemu prześlizgujesz się obok strażników. Na szczęście pomieszczenie jest dość duże. Ostrożnie wymijasz wszelkie podejrzane miejsca na swej drodze. Po jakimś czasie docierasz do przeciwległeś ściany komnaty. Widzisz iż wychodzi stąd kolejny tunel.', 'pl'),
(419, 6, 'grid.php', 'agi5', '0', 'Kiedy wydawało ci się, że już bez problemu wyminąłeś strażników, nagle w ciemności kopnąłeś niewielki kamyk. Ten potoczył się do ściany i usłyszałeś cichy stuk. Niestety Orkowie też go usłyszeli. Natychmiast podnieśli się z ziemi, wzięli w ręce pochodnie i rozpoczęli przeszukiwanie pomieszczenia. Nagle jeden z nich krzyknął i wskazał w twoim kierunku. A więc zostałeś zauważony. Błyskawicznie przygotowujesz się do walki.', 'pl'),
(420, 6, 'grid.php', '1.2.3.1.1', '0', 'Odkładasz na bok zbędną część ekwipunku i przygotowujesz się do walki. Następnie szybko wychodzisz z korytarza w kierunku ogniska. Orkowie, zauważają cię prawie natychmiast. Są zaskoczeni twoim pojawieniem się, lecz już po chwili wyciągają szable i ruszają w twoim kierunku. Rozpoczyna się walka.', 'pl'),
(421, 6, 'grid.php', 'escape2', '0', 'Wykonujesz gwałtowny unik, odwracasz się i zaczynasz uciekać. Słyszysz za sobą triumfalny wrzask przeciwników oraz tupot podkutych butów. Pędem wpadasz do korytarza z którego przybyłeś, szybko zbierasz swój ekwipunek i biegiem ruszasz w drogę powrotną. Za sobą cały czas słyszysz dzikie wrzaski Orków. Błyskawicznie docierasz do rozwidlenia i pędzisz dalej. Biegniesz pod górę, po ciemku, nie zatrzymujesz się ani na moment. Dopiero kiedy docierasz do skrzyżowania u góry, przystajesz na moment. Jednak twój odpoczynek nie trwa długo. Uważnie nasłuchując słyszysz z oddali zbliżające się odgłosy pogoni. Masz wprawdzie nad nimi niewielką czasową przewagę, ale lepiej nie ryzykować. Szybko podnosisz się z ziemi i ruszasz dalej w kierunku wyjścia z labiryntu. Wracasz więc przez drzwi i udajesz się w drogę powrotną. Zbierasz więc swój ekwipunek i zawracasz w kierunku skrzyżowania. Po drodze znów urządzasz sobie w pewnym momencie odpoczynek. Nie jesteś dokładnie w stanie stwierdzić ile już przebywasz w labiryncie. W tych ciemnościach jedynie światło twojej pochodni w jakiś sposób wyznacza upływ czasu. Jednak nie jesteś pewien ile już go spędziłeś pod ziemią. Po odpoczynku zbierasz swój ekwipunek i wyruszasz dalej przed siebie. Po pewnym czasie docierasz do skrzyżowania. Następnie kierujesz się do wyjścia z owego bogato rzeźbionego tunelu. Po drodze ze zmęczenia omal nie zapomniałeś o czyhającej pułapce na drodze. Na szczęście zorientowałeś się zanim w nią wszedłeś. Przy wylocie korytarza znów na moment przystajesz aby odpocząć. Po chwili kierujesz się w stronę wyjścia z labiryntu. Kiedy do niego docierasz, w twoje oczy uderza blask niewidzianego od pewnego czasu słońca, zaś do twoich uszu dochodzi gwar rozmów wielu istot. Jesteś z powrotem w mieście.', 'pl'),
(422, 6, 'grid.php', 'lostfight2', '0', 'Walka z potworami nie była najlepszym rozwiązaniem. Raz za razem ich ciosy spadały na ciebie, osłabiając cię coraz bardziej. Po pewnym czasie już ledwo stojąc na nogach, widzisz jak w zwolnionym tempie szabla orcza opada w twoim kierunku, podczas gdy na twarzach Orków maluje się wyraz zwycięstwa. Następnie w twojej głowie eksplodowała jasna gwiazda. To była ostatnia rzecz jaką zapamiętałeś. Po tym wydarzeniu ogarnęła cię całkowita ciemność.', 'pl');
INSERT INTO `quests` (`id`, `qid`, `location`, `name`, `option`, `text`, `lang`) VALUES 
(423, 6, 'grid.php', 'winfight2', '0', 'Jeden po drugim Orkowie charcząc padają martwi u twych stóp. Bez problemu pokonujesz ich wszystkich - nie stanowili dla ciebie poważniejszego zagrożenia. Przez moment stoisz w miejscu odpoczywając po walce. Następnie zbierasz swój ekwipunek i wyruszasz dalej na zbadanie tego co kryje się w pomieszczeniu. Ostrożnie idąc przed siebie, wkrótce docierasz do przeciwległego końca sali. Widzisz tutaj kolejny korytarz prowadzący na zachód.', 'pl'),
(424, 6, 'grid.php', '1.2.3.1.1.next', '0', 'Korytarz ten, jest znacznie mniejszy od tego którym wcześniej wędrowałeś. Jest też znacznie gorzej wykonany od poprzenich. Widać to pewnie robota tych Orków których spotkałeś. Prowadzi on kilkanaście kroków do niewielkiego pomieszczenia. Kiedy wchodzisz ostrożnie do środka - widzisz że jest to coś w rodzaju magazynu - pełno tutaj lekko nadgniłego jedzenia oraz różnych orczych trunków. Dokładnie przeszukujesz pomieszczenie w poszukiwaniu skarbów. Okazuje się że nie były to daremne poszukiwania. W jednym z rogów pomieszczenia znajdujesz wciśniętą pomiędzy amfory niewielką sakiewkę. Kiedy zaglądasz do środka znajdujesz w niej 20 sztuk mithrilu. Szybko chowasz ją do plecaka i kierujesz się w drogę powrotną. Spokojnie przechodzisz więc przez tunel a potem przez pomieszczenie, mijając martwych Orków a dochodząc do wyjścia z pomieszczenia. Następnie szybkim krokiem udajesz się z powrotem do skrzyżowania aby wybrać inną drogę.', 'pl'),
(425, 6, 'grid.php', '1.2.3.1.2.next', '0', 'orytarz ten, jest znacznie mniejszy od tego którym wcześniej wędrowałeś. Jest też znacznie gorzej wykonany od poprzenich. Widać to pewnie robota tych Orków których spotkałeś. Prowadzi on kilkanaście kroków do niewielkiego pomieszczenia. Kiedy wchodzisz ostrożnie do środka - widzisz że jest to coś w rodzaju magazynu - pełno tutaj lekko nadgniłego jedzenia oraz różnych orczych trunków. Dokładnie przeszukujesz pomieszczenie w poszukiwaniu skarbów. Okazuje się że nie były to daremne poszukiwania. W jednym z rogów pomieszczenia znajdujesz wciśniętą pomiędzy amfory niewielką sakiewkę. Kiedy zaglądasz do środka znajdujesz w niej 20 sztuk mithrilu. Szybko chowasz ją do plecaka i kierujesz się w drogę powrotną. Ostrożnie wchodzisz do pomieszczenia i tą samą drogą przekradasz się powoli w kierunku wyjścia. I tym razem udało ci się przejść niezauważonym. Przemykasz się do tunelu a następnie szybkim krokiem kierujesz się do skrzyżowania. Nie jesteś do końca pewien ile masz czasu zanim Orkowie odkryją twoje ślady. Po jakimś czasie docierasz z powrotem do rozwidlenia. Tutaj na moment przystajesz aby odpocząć i ponownie wybrać w którą stronę chcesz się teraz udać.', 'pl'),
(426, 6, 'grid.php', '1.2.3.2', '0', 'Ostrożnie kierujesz się na wschód. Korytarz prowadzi nieco pod górę. Krok po kroku podążasz przed siebie badając uważnie każdy fragment korytarza. Wykonany on jest z jednego kawałka skały, na podłodze widzisz niewielką warstewkę kurzu. Co jakiś czas zatrzymujesz się i nasłuchujesz. Zewsząd otacza cię martwa cisza. Po jakimś czasie wędrówki postanawiasz na chwilę odpocząć.', 'pl'),
(427, 6, 'grid.php', 'int7', '0', 'Kiedy chciałeś usiąść na ziemi, zauważyłeś niewielką płytkę w chodniku. Przyglądając się bliżej, dostrzegasz kilka cali nad ziemią ledwo widoczną szeroką szparę w ścianie. Kiedy przyglądasz się bliżej widzisz w jej głębi błysk stali. Właśnie znalazłeś kolejną pułapkę. Ostrożnie omijasz to miejsce i postanawiasz odpocząć nieco dalej.', 'pl'),
(428, 6, 'grid.php', 'hp3', '0', 'Kiedy usiadłeś na ziemi, nagle usłyszałeś cichy trzask od strony ściany a w prawym ramieniu potworny ból. Spojrzałeś w tę stronę i z przerażeniem zauważyłeś że kapie z niego krew na ziemię. Szybko zabandażowałeś ranę - mimo to i tak straciłeś 20 punktów życia. Uważnie zacząłeś rozglądać się, co mogło spowodować twoje obrażenia. Nagle zauważyłeś niewielką wciśniętą płytkę w chodniku. Przyglądając się bliżej, dostrzegasz kilka cali nad ziemią ledwo widoczną szeroką szparę w ścianie. Kiedy przyglądasz się bliżej widzisz w jej głębi błysk stali. Właśnie znalazłeś kolejną pułapkę. Przesuwasz się kilka kroków dalej i tam przez chwilę odpoczywasz po tym wstrząsającym wydarzeniu.', 'pl'),
(429, 6, 'grid.php', 'hp4', '0', 'Kiedy siadałeś na ziemi, usłyszałeś nagle cichy trzask dochodzący od podłogi i świst od strony ściany za twoimi plecami. Momentalnie w twojej głowie eksplodowała jasna gwiazda bólu. Po chwili zapadłeś w całkowitą ciemność. Tak oto kończy się twoje zwiedzanie labiryntu. Może następnym razem będziesz miał więcej szczęścia.', 'pl'),
(430, 6, 'grid.php', '1.2.3.2.next', '0', 'Kiedy już nieco odpocząłeś, postanawiasz sprawdzić co dalej kryje korytarz. Zbierasz więc swój ekwipunek i ponownie ruszasz przed siebie, cały czas zachowując czujność. Po pewnym czasie wędrówki, wydaje ci się, że w korytarzu zrobiło się nieco jaśniej niż było do tej pory. Ostrożnie idziesz więc przed siebie. Po pewnym czasie z naprzeciwka dobiega do twych uszu świergot ptaków. Kiedy podchodzisz bliżej dostrzegasz dużą, jasną plamę światła. Gasisz pochodnię i szybko kierujesz się w tę stronę. W pewnym momencie w oczy uderza cię światło słońca a dookoła siebie słyszysz odgłosy lasu. Okazuje się że jesteś na zewnątrz. Uważnie rozglądasz się na boki. Znajdujesz się tuż przy murach miasta. Zastanawiasz się, w którą stronę teraz się udać.', 'pl'),
(431, 6, 'grid.php', 'box13', '1', 'Zawrócić', 'pl'),
(432, 6, 'grid.php', 'box13', '2', 'Udać się do miasta', 'pl'),
(433, 6, 'grid.php', '1.2.3.2.2', '0', 'Postanawiasz zostawić mroczne korytarze labiryntu i wrócić do cywilizacji. Zbierasz więc swoje rzeczy i wchodzisz na ulice miasta. Ze zdziwieniem zauważasz, że przechodnie przyglądają ci się. Kiedy sam zaczynasz przyglądać się sobie, widzisz, że jesteś cały ubrudzony pajęczynami oraz kurzem. Chyba czas wziąść jakąś kąpiel.', 'pl'),
(434, 6, 'grid.php', '1.2.3.2.1', '0', 'Postanawiasz zawrócić do labiryntu. Droga powrotna jest nieco łatwiejsza niż wcześniej, przede wszystkim dlatego że teraz idziesz z górki. Na moment zwalniasz w miejscu gdzie wcześniej natknąłeś się na pułapkę. Następnie szybkim krokiem dochodzisz do rozwidlenia korytarza. Tutaj przystajesz na moment aby odpocząć. Po kilku chwilach postanawiasz zawrócić z powrotem do skrzyżowania. Zbierasz więc swój ekwipunek i udajesz się w drogę powrotną. Teraz jest ci nieco ciężej podróżować, ponieważ musisz się delikatnie wspinać pod górę. Kiedy docierasz do skrzyżowania, postanawiasz przez moment odpocząć. Następnie przechodzisz przez drzwi i udajesz się w drogę powrotną. Zbierasz więc swój ekwipunek i zawracasz w kierunku skrzyżowania. Po drodze znów urządzasz sobie w pewnym momencie odpoczynek. Nie jesteś dokładnie w stanie stwierdzić ile już przebywasz w labiryncie. W tych ciemnościach jedynie światło twojej pochodni w jakiś sposób wyznacza upływ czasu. Jednak nie jesteś pewien ile już go spędziłeś pod ziemią. Po odpoczynku zbierasz swój ekwipunek i wyruszasz dalej przed siebie. Po pewnym czasie docierasz do skrzyżowania. Którą drogę teraz wybierasz?', 'pl'),
(435, 6, 'grid.php', '1.3', '0', 'Korytarz bogato zdobiony prowadzi cały czas prosto. Ostrożnie podążasz nim, bacznie rozglądając się w poszukiwaniu pułapek. Na szczęście na nic takiego nie natrafiasz. Tańczący płomień twojej pochodni sprawia, że rzeźby na ścianach wyglądają jakby się poruszały. To oraz panująca w około cisza sprawia niesamowite wrażenie. Korytarz jest dość długi. W pewnym momencie zatrzymujesz się na chwilę aby odpocząć. Następnie zbierasz swój ekwipunek i wyruszasz dalej. Po jakimś okresie czasu, widzisz że zbliżasz się do jakiegoś pomieszczenia. Kiedy ostrożnie podchodzisz bliżej, dostrzegasz, że jest to niewielka okrągła komnata. Zdobienia na jej ścianach są bardzo podobne do zdobień korytarza którym przyszedłeś. Jednak ciebie najbardziej interesuje fakt, że z pomieszczenia wychodzą dwa kolejne korytarze. Przez moment stoisz i zastanawiasz się, którą drogę wybrać.', 'pl'),
(436, 6, 'grid.php', 'box14', '1', 'Północ', 'pl'),
(437, 6, 'grid.php', 'box14', '2', 'Południe', 'pl'),
(438, 6, 'grid.php', 'box14', '3', 'Zawrócić', 'pl'),
(439, 6, 'grid.php', 'box15', '1', 'Południe', 'pl'),
(440, 6, 'grid.php', 'box15', '2', 'Zawrócić', 'pl'),
(441, 6, 'grid.php', '1.3.3', '0', 'Postanawiasz zawrócić do miasta. Dość masz już na dzisiaj przygód. Odwracasz się więc i ruszasz w drogę powrotną. Szybko przemierzasz korytarz, którym wędrowałeś wcześniej. Po jakimś czasie ponownie znajdujesz się na znanym ci już dobrze skrzyżowaniu. Tutaj postanawiasz przez chwilę odpocząć. Następnie zbierasz swój ekwipunek i kierujesz się w stronę wyjścia z labiryntu. Nie jesteś dokładnie w stanie stwierdzić ile już przebywasz w labiryncie. W tych ciemnościach jedynie światło twojej pochodni w jakiś sposób wyznacza upływ czasu. Jednak nie jesteś pewien ile już go spędziłeś pod ziemią. Po drodze ze zmęczenia omal nie zapomniałeś o czyhającej pułapce na drodze. Na szczęście zorientowałeś się zanim w nią wszedłeś. Przy wylocie korytarza znów na moment przystajesz aby odpocząć. Po chwili kierujesz się w stronę wyjścia z labiryntu. Kiedy do niego docierasz, w twoje oczy uderza blask niewidzianego od pewnego czasu słońca, zaś do twoich uszu dochodzi gwar rozmów wielu istot. Jesteś z powrotem w mieście.', 'pl'),
(442, 6, 'grid.php', '1.3.1', '0', 'Korytarz ten jest nieco węższy niż te, którymi wędrowałeś do tej pory. Jednak podobnie jak tamte jest bogato zdobiony na ścianach, zaś jego podłogę pokrywa cienka warstwa kurzu. Ostrożnie idziesz przed siebie badając każdy cal korytarza w poszukiwaniu pułapek. W pewnym momencie podczas swej wędrówki, widzisz iż pod jedną ze ścian coś leży. Kiedy podchodzisz bliżej, dostrzegasz, że jest to szkielet jakiejś istoty rozumnej - człowieka lub elfa, okryty resztkami tkaniny. W zaciśniętej dłoni trzyma złamaną różdżkę. Przyglądając mu się dokładniej zauważasz że obok niego znajduje się stara podniszczona księga. Kiedy zaglądasz do środka, stwierdzasz, iż musi to być księga czarów. Niestety jest prawie całkowicie zniszczona pod wpływem czasu. Kiedy przewracasz jej karty, dosłownie rozsypują się one w rękach. Zawiedziony zostawiasz znalezisko i ruszasz dalej przed siebie. Niestety korytarz po kilkudziesięciu krokach kończy się. Z nadzieją przeszukujesz ściany próbując znaleźć jakieś tajemne przejścia. Niestety nic nie udaje ci się odkryć. Zrezygnowany wracasz z powrotem do skrzyżowania. Kiedy doń docierasz, zatrzymujesz się na moment, aby odpocząć i podjąć decyzję co do dalszego zwiedzania okolicy.', 'pl'),
(443, 6, 'grid.php', '1.3.2', '0', 'Postanowiłeś udać się na południe. Korytarz podobny jest do tych, którymi wcześniej wędrowałeś, bogato zdobione ściany z rzeźbami przedstawiającymi różne istoty. Jednak po przebyciu zaledwie kilkudziesięciu kroków docierasz do niewielkiego skrzyżowania w kształcie litery T. Przez chwilę stoisz w miejscu zastanawiając się, którą drogę teraz wybrać.', 'pl'),
(444, 6, 'grid.php', 'box16', '1', 'Wschód', 'pl'),
(445, 6, 'grid.php', 'box16', '2', 'Zachód', 'pl'),
(446, 6, 'grid.php', 'box16', '3', 'Zawrócić', 'pl'),
(447, 6, 'grid.php', 'box17', '1', 'Zachód', 'pl'),
(448, 6, 'grid.php', 'box17', '2', 'Zawrócić', 'pl'),
(449, 6, 'grid.php', '1.3.2.3', '0', 'Postanawiasz zawrócić do miasta - dość przygód jak na jeden raz. Poza tym przydałoby się nieco odpocząć. Wydaje ci się że spędziłeś w tych podziemiach wieki. Szybkim krokiem zawracasz w kierunku okrągłego pomieszczenia. Kiedy do niego docierasz, przystajesz na moment aby jeszcze raz dokładnie rozejrzeć się po okolicy. Niestety nic ciekawego nie odkrywasz. Następnie szybkim krokiem przemierzasz korytarz, którym wędrowałeś wcześniej. Po jakimś czasie ponownie znajdujesz się na znanym ci już dobrze skrzyżowaniu. Tutaj postanawiasz przez chwilę odpocząć. Następnie zbierasz swój ekwipunek i kierujesz się w stronę wyjścia z labiryntu. Nie jesteś dokładnie w stanie stwierdzić ile już przebywasz w labiryncie. W tych ciemnościach jedynie światło twojej pochodni w jakiś sposób wyznacza upływ czasu. Jednak nie jesteś pewien ile już go spędziłeś pod ziemią. Po drodze ze zmęczenia omal nie zapomniałeś o czyhającej pułapce na drodze. Na szczęście zorientowałeś się zanim w nią wszedłeś. Przy wylocie korytarza znów na moment przystajesz aby odpocząć. Po chwili kierujesz się w stronę wyjścia z labiryntu. Kiedy do niego docierasz, w twoje oczy uderza blask niewidzianego od pewnego czasu słońca, zaś do twoich uszu dochodzi gwar rozmów wielu istot. Jesteś z powrotem w mieście.', 'pl'),
(450, 6, 'grid.php', '1.3.2.1', '0', 'Korytarz ten wygląda na nieco nowszy niż ten, którym tutaj doszedłeś. Jego ściany nie są ozdobione rzeźbami a podłoga wykonana jest z kamiennych płyt. To zwiększa twoją czujność. Ostrożnie idziesz przed siebie, dokładnie badając korytarz. Po pewnym czasie dochodzisz do olbrzymiego rumowiska skalnego, które całkowicie blokuje dalszą część tunelu. Wygląda na to że dawno temu musiał się ów korytarz zawalić. Niestety nie jesteś w stanie otworzyć sobie przejścia. Jednak dokładnie przeszukując rumowisko, zauważasz wśród wolno leżących kamieni delikatny błysk. Kiedy podchodzisz bliżej i podnosisz ów przedmiot z ziemi, okazuje się że w rękach trzymasz nieco podniszczony żelazny sztylet. Jest w dość dobrym stanie, trzeba go tylko nieco oczyścić. Z nadzieją postanawiasz przeszukać dokładniej okolicę. Niestety nie znajdujesz nic więcej. Po krótkim odpoczynku ponownie wracasz do rozwidlenia aby wybrać inną drogę.', 'pl'),
(451, 6, 'grid.php', '1.3.2.2', '0', 'Postanowiłeś udać się na zachód. Ostrożnie idziesz przed siebie, bacznie rozglądając się w poszukiwaniu pułapek. Korytarz prowadzi nieco pod górę. Idziesz tak przez długi okres czasu nie natrafiając na nic ciekawego po drodze. W pewnym momencie widzisz że korytarz kończy się jakąś wielką salą. Kiedy wychodzisz z korytarza, stajesz na moment osłupiały. Znajdujesz się na niewielkim balkonie skalnym. Kilkanaście kroków niżej widzisz niezwykłą budowlę - olbrzymi labirynt oświetlony płonącymi kagankami. Ã“w labirynt znajduje się kilkanaście kroków poniżej miejsca w którym się znajdujesz. Po twojej lewej stronie widzisz schody prowadzące w dół do owej budowli. Zastanawiasz się kto i po co budował tutaj taką rzecz. Przez chwilę rozmyślasz nad swoimi kolejnymi krokami.', 'pl'),
(452, 6, 'grid.php', 'box18', '1', 'Zejść do labiryntu', 'pl'),
(453, 6, 'grid.php', 'box18', '2', 'Zawrócić', 'pl'),
(454, 6, 'grid.php', '1.3.2.2.2', '0', 'Niepokoi cię ta budowla. Nie jesteś pewien do końca czy iść na dół. Postanawiasz zawrócić do miasta. Dość masz przygód jak na ten jeden raz. Poza tym przydałoby się nieco odpocząć. Wydaje ci się że spędziłeś w tych podziemiach wieki. Szybkim krokiem zawracasz w kierunku okrągłego pomieszczenia. Kiedy do niego docierasz, przystajesz na moment aby jeszcze raz dokładnie rozejrzeć się po okolicy. Niestety nic ciekawego nie odkrywasz. Następnie szybkim krokiem przemierzasz korytarz, którym wędrowałeś wcześniej. Po jakimś czasie ponownie znajdujesz się na znanym ci już dobrze skrzyżowaniu. Tutaj postanawiasz przez chwilę odpocząć. Następnie zbierasz swój ekwipunek i kierujesz się w stronę wyjścia z labiryntu. Nie jesteś dokładnie w stanie stwierdzić ile już przebywasz w labiryncie. W tych ciemnościach jedynie światło twojej pochodni w jakiś sposób wyznacza upływ czasu. Jednak nie jesteś pewien ile już go spędziłeś pod ziemią. Po drodze ze zmęczenia omal nie zapomniałeś o czyhającej pułapce na drodze. Na szczęście zorientowałeś się zanim w nią wszedłeś. Przy wylocie korytarza znów na moment przystajesz aby odpocząć. Po chwili kierujesz się w stronę wyjścia z labiryntu. Kiedy do niego docierasz, w twoje oczy uderza blask niewidzianego od pewnego czasu słońca, zaś do twoich uszu dochodzi gwar rozmów wielu istot. Jesteś z powrotem w mieście.', 'pl'),
(455, 6, 'grid.php', '1.3.2.2.1', '0', 'Postanawiasz zaryzykować i zejść w dół aby zbadać co kryje ów tajemniczy labirynt. Być może są w nim schowane jakieś skarby. Ostrożnie schodzisz po kamiennych schodach w dół. Kiedy już znajdujesz się na miejscu, widzisz przed sobą wejście do labiryntu. Po obu stronach wejścia znajdują się płonące kaganki. Kiedy podchodzisz bliżej, zauważasz, że wewnątrz nich nie ma ani paliwa ani knotów - palą się dzięki magii. Wzbudza to w tobie niepokój, ale jesteś zdeterminowany aby odkryć tajemnice labiryntu. Pewnym krokiem wchodzisz do środka. Kiedy rozpoczynasz wędrówkę, zauważasz że ściany budowli wykonane są z jakiegoś zielonkawego kamienia. Ostrożnie idziesz przed siebie, zatrzymując się na każdym zakręcie aby sprawdzić co się za nim kryje. Kiedy wędrujesz tak przez jakiś czas, nagle zdajesz sobie sprawę, że nie jesteś w stanie przypomnieć sobie drogi powrotnej! Zaczynasz coraz bardziej się niepokoić. Wydaje ci się, że jedyny sposób aby wyjść z tego labiryntu to odkryć co też on kryje. Z tym przeświadczeniem wyruszasz w dalszą drogę.', 'pl'),
(456, 6, 'grid.php', 'atr1', '0', 'Im dalej idziesz tym coraz większą odczuwasz panikę. Jesteś już całkowicie pewien że się zgubiłeś. Nigdy nie znajdziesz drogi powrotnej, umrzesz w tym labiryncie. Przerażony zaczynasz biegać po nim, jednak twoje siły coraz bardziej się wyczerpują. Głodny i zmęczony nie jesteś już w stanie dalej podróżować. W pewnym momencie siadasz na ziemi. Wargi spuchły ci z pragnienia, przed oczami pojawiają się różne omamy. Próbujesz z tym walczyć jednak nie udaje ci się. Po jakimś czasie kolana pod tobą uginają się a ty zapadasz w mrok śmierci. Tak oto kończy się twoja podróż po tym magicznym labiryncie.', 'pl'),
(457, 6, 'grid.php', 'atr2', '0', 'Pewnym krokiem idziesz przed siebie. Co jakiś czas skręcasz raz w jedną raz w drugą stronę. Jednak wydaje ci się, że ciągle idziesz ogólnie w jednym kierunku. Po pewnym czasie twój upór zostaje wynagrodzony. Widzisz przed sobą niewielkie pomieszczenie a w nim długą, płaską skrzynię. Ostrożnie podchodzisz bliżej i otwierasz ją. Wewnątrz widzisz żelazną halabardę. Szybko bierzesz ją do ręki. W tym momencie wydaje ci się, że cały świat zawirował. Nagle otoczyła cię zielonkawa mgła. Kiedy przetarłeś oczy dostrzegasz że stoisz na środku pustego pomieszczenia. Cały labirynt zniknął gdzieś. W swoich rękach nadal trzymasz znalezisko. Na dodatek doskonale pamiętasz w którym kierunku znajduje się ów balkon z którego przyszedłeś tutaj. O dziwo, nie czujesz również zmęczenia. Szybko przytraczasz do plecaka znalezisko i ruszasz w drogę powrotną. Okazuje się że do schodów na górę było zaledwie kilkadziesiąt kroków. Wspinasz się na galerię a następnie szybkim krokiem zawracasz w kierunku okrągłego pomieszczenia. Kiedy do niego docierasz, przystajesz na moment aby jeszcze raz dokładnie rozejrzeć się po okolicy. Niestety nic ciekawego nie odkrywasz. Następnie szybkim krokiem przemierzasz korytarz, którym wędrowałeś wcześniej. Po jakimś czasie ponownie znajdujesz się na znanym ci już dobrze skrzyżowaniu. Tutaj postanawiasz przez chwilę odpocząć. Następnie zbierasz swój ekwipunek i kierujesz się w stronę wyjścia z labiryntu. Nie jesteś dokładnie w stanie stwierdzić ile już przebywasz w labiryncie. W tych ciemnościach jedynie światło twojej pochodni w jakiś sposób wyznacza upływ czasu. Jednak nie jesteś pewien ile już go spędziłeś pod ziemią. Po drodze ze zmęczenia omal nie zapomniałeś o czyhającej pułapce na drodze. Na szczęście zorientowałeś się zanim w nią wszedłeś. Przy wylocie korytarza znów na moment przystajesz aby odpocząć. Po chwili kierujesz się w stronę wyjścia z labiryntu. Kiedy do niego docierasz, w twoje oczy uderza blask niewidzianego od pewnego czasu słońca, zaś do twoich uszu dochodzi gwar rozmów wielu istot. Jesteś z powrotem w mieście.', 'pl'),
(458, 7, 'grid.php', 'start', '0', 'Gdy błądziłeś wśród mrocznych korytarzy labiryntu, jakiś tajemniczy blask przykuł twoją uwagę. Podszedłeś bliżej i zobaczyłeś w ścianie niewielkie wgłębienie, a w nim maleńką, kryształową dźwignię. Zatopiony w krysztale znak Klasztoru Czerwonego Smoka przerażał, ale i kusił...', 'pl'),
(459, 7, 'grid.php', 'box1', '1', 'Ciągniesz dźwignię w dół', 'pl'),
(460, 7, 'grid.php', 'box1', '2', 'Idziesz dalej swoją drogą', 'pl'),
(461, 7, 'grid.php', '1', '0', 'Pod naciskiem dźwignia ze zgrzytem opuszcza się otwierając ukryte w ścianie drzwi. Widzisz za nimi nowy korytarz, który rozdziela się na trzy niskie, cuchnące wilgocią i stęchlizną tunele. Trudno ci się zdecydować, który wybrać, jednak po chwili ufasz swemu przeczuciu i...', 'pl'),
(462, 7, 'grid.php', 'box2', '1', 'Idziesz w prawo', 'pl'),
(463, 7, 'grid.php', 'box2', '2', 'Idziesz prosto', 'pl'),
(464, 7, 'grid.php', 'box2', '3', 'Idziesz w lewo', 'pl'),
(465, 7, 'grid.php', 'answer2', '0', 'SERCE -szepczesz czując, że to słowo jest rozwiązaniem zagadki i nie mylisz się. Twój głos otwiera kolejne przejście. Dumny z siebie chcesz tam wejść, ale nagle coś każe ci się zatrzymać, zamknąć oczy i uklęknąć na jedno kolano. Gdy to robisz, w twej głowie huczy dziwny dźwięk, który po chwili przeradza się w ciepły głos Nubii:  ...czuwam nad tobą i twoimi krokami... podążasz w dobrym kierunku... powodzenia...<br />Gdy otwierasz oczy, głos w twoich myślach cichnie. Dobrze wiedzieć, że nie jesteś tu sam. Wstajesz i podążasz dalej...', 'pl'),
(466, 7, 'grid.php', 'answer2next', '0', 'Przejście nie różni się niczym od poprzednich. Jest równie wąskie, ciasne i śmierdzące. Masz wrażenie, jakby nikt tędy nie chodził od wieków... A może i nigdy...? Chciałbyś już poczuć świeżość powietrza lasów wokół city1b, jednak jedyne, co wciągasz w płuca to stęchlizna i unoszące się wszędzie wokół pajęczyny. Może czas zawrócić?', 'pl'),
(467, 7, 'grid.php', 'box3', '1', 'zawracasz', 'pl'),
(468, 7, 'grid.php', '1.1', 'serce', 'Tunel powoli rozszerza się i gdy możesz już bez problemu iść wyprostowany trafiasz do pełnej pajęczyn i kurzu komnaty. <i>...no tak, nie ma wyjścia</i> - myślisz klnąc pod nosem. Chcąc zawrócić zauważasz dziwną tabliczkę, na której -nie bez trudu- odnajdujesz słowa zagadki:<br /><br /><i>Choć uderza mocno, prawie nieprzerwanie,<br />To nie krzywdzi nigdy, nikogo nie rani.<br />Za to kiedy ból każe mu żyć w mękach,<br />Nie chce cierpieć i wtedy z żalu na kawałki pęka.</i>', 'pl'),
(469, 7, 'grid.php', 'box3', '2', 'idziesz dalej', 'pl'),
(470, 7, 'grid.php', '1.1.1', '0', 'Ile jeszcze? -pytasz sam siebie w myślach. Nagle korytarz się rozwidla i stajesz przed izdebką z kamienną ścianą z trojgiem drzwi. Może i wybierzesz któreś z nich, ale warto przeszukać pomieszczenie.', 'pl'),
(471, 7, 'grid.php', 'box4', '1', 'przeszukujesz komnatę', 'pl'),
(472, 7, 'grid.php', 'box4', '2', 'wybierasz drzwi w prawo', 'pl'),
(473, 7, 'grid.php', 'box4', '3', 'wybierasz drzwi na wprost', 'pl'),
(474, 7, 'grid.php', 'box4', '4', 'wybierasz drzwi w lewo', 'pl'),
(475, 7, 'grid.php', '1.1.1.1', '3', 'Twoje myśli nie są wystarczająco skupione na tej czynności. Ciekawe co je rozprasza? Może to stado 20 nadbiegających goblinów z łukami? Rozpoczyna się walka.', 'pl'),
(476, 7, 'grid.php', 'winfight1', '0', 'Mocno zmęczony przeszukujesz z obrzydzeniem ścierwo martwych goblinów, ale jedyne, co na tym zyskujesz, to nowe, mało ciekawe doświadczenie, o którym na pewno nie będziesz chciał opowiadać swoim dzieciom.', 'pl'),
(477, 7, 'grid.php', '1.1.1.2', '0', 'Naciskasz na klamkę i o mało nie zaczynasz popiskiwać z radości. Skryta za nią komnata lśni złotem!!! Wkraczasz do niej zafascynowany blaskiem kruszcu lecz szybko zdajesz sobie sprawę, że to piryt- złoto głupców. Ze śmiechem przechodzisz przez to pomieszczenie i w myślach dziękujesz alchemikowi, że nauczył cię rozróżniać metale.', 'pl'),
(478, 7, 'grid.php', '1.1.1.3', '0', 'Drzwi nie mają za sobą żadnego pomieszczenia, ani korytarza dokładnie za progiem zaczynają się zakręcone schody, którymi wspinasz się z mozołem. Po kilku godzinach zmęczony siadasz wreszcie na płaskiej podłodze jakiegoś pustego pomieszczenia z drewnianymi drzwiami. Naciskasz na klamkę i... Mijasz próg komnaty i słyszysz jak kamienne drzwi zatrzaskują się, a w miejscu, gdzie były jeszcze przed chwilą, nie ma po nich nawet najmniejszego śladu...', 'pl'),
(479, 7, 'grid.php', '1.1.1.4', '0', 'Drzwi nie mają za sobą żadnego pomieszczenia, ani korytarza dokładnie za progiem zaczynają się zakręcone schody, którymi wspinasz się z mozołem. Po kilku godzinach zmęczony siadasz wreszcie na płaskiej podłodze jakiegoś pustego pomieszczenia z drewnianymi drzwiami. Naciskasz na klamkę i... zrezygnowany, zły i zmęczony widzisz kolejny tunel...', 'pl'),
(480, 7, 'grid.php', '1.1.1.2.next', '0', 'Komnata zwęża się płynnie przechodząc w kolejny tunel, który prowadzi cię do niezwykłego miejsca. Stopy grzęzną ci w wilgotnym piasku, a toń podziemnego jeziora zaprasza zieloną głębią. Długo zastanawiasz się, co dalej, jednak nie masz ochoty zawracać. Mimo usilnych starań, nie udaje ci się znaleźć żadnej łodzi, ani nawet deski, na której mógłbyś popłynąć wzdłuż brzegu... <br />Zabezpieczasz sakwę z jedzeniem i wchodzisz do ciepłej, mulistej wody...', 'pl'),
(481, 7, 'grid.php', '1.1.1.2.next.2', '0', 'Woda przyjemnie pieści twoje ciało i rozgrzewa zmęczone błądzeniem po labiryncie stopy. Powoli zwalniasz ruch ramion i płyniesz unosząc się na zielonej powierzchni. Tafla jeziora prowadzi cię wzdłuż brzegu tak monotonnie, tak sennie...<br /> Obudź się!!! - słyszysz w głowie głos Nubii. -Obudź się!!!!!', 'pl'),
(482, 7, 'grid.php', '1.1.1.2.next.3', '0', 'Walczysz z unoszącym cię prądem wody, ale niestety jest już za późno. Wir porywa cię jak piórko, rzuca i wciąga w głębiny. Próbujesz krzyczeć, jednak głos więźnie ci w gardle, a woda zalewa cię od stóp po czubek głowy... To koniec...- myślisz, ale po chwili czujesz, że jesteś w jakiejś podziemnej jaskini poniżej poziomu jeziora. Przemoczony, głodny i zmarznięty wychodzisz na kamienny brzeg i chwiejnym krokiem ruszasz w głąb jaskini. ...Jesteś taki nieostrożny! - głos w głowie zdawał się krzyczeć. Miała rację... Lecz nagle przed sobą zauważasz dwa pomieszczenia. Już daleka widać stojące przed nimi marmurowe posągi. Nie masz wyjścia, musisz do któregoś wejść...', 'pl'),
(483, 7, 'grid.php', 'box5', '1', 'Wybierasz komnatę z Ekkimu', 'pl'),
(484, 7, 'grid.php', 'box5', '2', 'Wybierasz komnatę z Feniksem', 'pl'),
(485, 7, 'grid.php', '1.1.1.2.next.3.1', '0', 'Gdy mijasz posąg Ekkimu, masz wrażenie, że czyjeś oczy mocno ci się przyglądają... Ryk i tupot paskudnych racic... Posąg ożył! Mogłeś się tego spodziewać. Rozpoczyna się walka!', 'pl'),
(486, 7, 'grid.php', 'winfight2', '0', 'Mocno zmęczony przeszukujesz z obrzydzeniem ścierwo martwego Ekkimu, ale jedyne, co na tym zyskujesz, to nowe, mało ciekawe doświadczenie, o którym na pewno nie będziesz chciał opowiadać swoim dzieciom.', 'pl'),
(487, 7, 'grid.php', '1.1.1.2.next.3.2', '0', 'Gdy mijasz posąg Feniksa, masz wrażenie, że czyjeś oczy mocno ci się przyglądają... Ryk i tupot paskudnych racic... Posąg ożył! Mogłeś się tego spodziewać. Rozpoczyna się walka!', 'pl'),
(488, 7, 'grid.php', 'winfight3', '0', 'Dysząc stoisz nad ciałem Feniksa i nie możesz uwierzyć w swoje zwycięstwo. Osuwasz się zmęczony na ziemię, a głos Nubii szepcze z wyraźnym zadowoleniem: ...płonący potwór z przestworzy pokonany... teraz szukaj różdżki...', 'pl'),
(489, 7, 'grid.php', 'box6', '1', 'przeszukaj pomieszczenie', 'pl'),
(490, 7, 'grid.php', 'box6', '2', 'przeszukaj Feniksa', 'pl'),
(491, 7, 'grid.php', '1.1.1.2.n.3.1.2.n', 'czas', 'Rozwijasz pergamin i widzisz kolejną zagadkę Feniksa. Czytasz na głos następujące po sobie wersy:<br /><br /><i>Coś, przed czym w świecie nic nie uciecze,<br />Co gnie żelazo, przegryza miecze,<br />Pożera ptaki, zwierzęta, ziele,<br />Najtwardszy kamień na mąkę miele,<br />Królów nie szczędzi, rozwala mury,<br />Poniża nawet najwyższe góry.</i>', 'pl'),
(492, 7, 'grid.php', 'answer1', '0', 'Twoje myśli nie są wystarczająco skupione na tej czynności. Ciekawe co je rozprasza? Może to stado 20 nadbiegających goblinów z łukami? Rozpoczyna się walka.', 'pl'),
(493, 7, 'grid.php', 'answer3', '0', 'CZAS -szepczesz czując, że to słowo jest rozwiązaniem zagadki i nie mylisz się. Twój głos otwiera przejście. Rozpoznajesz z daleka mury city1b. Dumny z siebie chcesz tam wejść, ale nagle coś każe ci się zatrzymać, zamknąć oczy i uklęknąć na jedno kolano. Gdy to robisz, w twej głowie huczy dziwny dźwięk, który po chwili przeradza się w ciepły głos Nubii:  ...nie zapomnij po co tu przyszedłeś... Twój wzrok pada na cokół pomnika...', 'pl'),
(494, 7, 'grid.php', 'box7', '1', 'przeszukujesz postument', 'pl'),
(495, 7, 'grid.php', 'box7', '2', 'idziesz do city1b', 'pl'),
(496, 7, 'grid.php', '1.1.1.2.n.3.1.2.n.1', '0', 'Oglądasz z nadzieją stojący w przejściu cokół i nie zawiodłeś się. Jedna ze ścianek z łatwością ulega naporowi twoich dłoni i po chwili widzisz już ukrytą wewnątrz jak w sejfie diamentową różdżkę. Z triumfem na twarzy chwytasz ją mocno i podążasz w kierunku city1b.', 'pl'),
(497, 7, 'grid.php', '1.1.1.2.n.3.1.2.n.2', '0', 'Mijasz próg komnaty i słyszysz jak kamienne drzwi zatrzaskują się, a w miejscu, gdzie były jeszcze przed chwilą, nie ma po nich nawet najmniejszego śladu...', 'pl'),
(498, 7, 'grid.php', '1.1.1.2.next.3.2.2', '0', 'Boisz się dotknąć płonących piór Feniksa, jednak czujesz, że musisz to zrobić. Przełamawszy strach sięgasz pod obręcz wokół nogi ptaka i wyjmujesz z niej rulon złota i pergamin.', 'pl'),
(499, 7, 'grid.php', '1.1.1.2.next.3.2.1', '0', 'Komnata ma tylko jedne drzwi, więc pewny siebie naciskasz ich klamkę i... wracasz do city1b.', 'pl'),
(500, 7, 'grid.php', '1.1.1.2.n.3.1.n', '0', 'Rozejrzałeś się i poza padliną Ekkimu nie znalazłeś w tej komnacie niczego ciekawego. I co dalej? -pomyślałeś głośno. Nic...-smutno odpowiedział ci głos Nubii. Po chwili jej magia teleportowała cię do bram city1b. Oszołomiony nie wiesz, czy cieszyć się, czy kląć... Ważne, że uszedłeś z tego z życiem!', 'pl'),
(501, 7, 'grid.php', '1.2', '0', 'Idziesz przed siebie podziwiając wytrwałość budowniczych tego tunelu. Tyle trudu włożyli, by wykuć go w litej skale! Pewnie to robota silnych krasnoludów, albo magii elfów... W każdym razie bardzo chcesz w to wierzyć, bo nie uśmiecha ci się wizja spotkania potwora, który mógł się tu chronić przed mieszkańcami miasta.', 'pl'),
(502, 7, 'grid.php', '1.2.next', '0', 'W pewnej chwili zastanawiasz się, czy wkroczenie tu, to był dobry pomysł. Korytarzowi nie widać końca... Nagle...! ?-Czego tutaj chcesz, marna istoto? - słyszysz nad sobą. Gdy podnosisz wzrok, widzisz ogromnego, włochatego pająka z ludzką głową. Mimowolnie cofasz się z obrzydzeniem, a po chwili wraca ci głos:', 'pl'),
(503, 7, 'grid.php', 'box8', '1', 'Spadaj, parchu! -sięgasz po broń.', 'pl'),
(504, 7, 'grid.php', 'box8', '2', 'Aaaaa... Tak se pomykam... Fajnie tu...-mruczysz niepewnie.', 'pl'),
(505, 7, 'grid.php', 'box8', '3', 'Fajna fryzurka... -uśmiechasz się do potwora.', 'pl'),
(506, 7, 'grid.php', 'winfigth4', '0', 'Mocno zmęczony przeszukujesz z obrzydzeniem ścierwo martwego pająka. Żadnej kieszeni, sakwy, torby... Ciekawe gdzie mógł coś schować...? Sięgasz w głębię ciała pająka i... wyciągasz zwój pełny złotych monet.', 'pl'),
(507, 7, 'grid.php', '1.2.next.1', '0', 'Postanawiasz sprawdzić, czy nie ma tam jeszcze czegoś, ale gdy sięgasz ponownie do ciała, pająk agonalnym ruchem podnosi wielki łeb i strzela w ciebie paraliżującym jadem. Myślisz wtedy, że mama zawsze przestrzegała cię przed zachłannością, a po chwili ogarnia cię ciemność...', 'pl'),
(508, 7, 'grid.php', '1.2.next.2', '0', 'Pająk ironicznie uśmiecha się do ciebie i powoli spuszcza się po fosforyzującej błękitem nici. Przygląda ci się oblizując spierzchnięte usta...<br />-A fajnie, fajnie... -powtarza po tobie jak echo. Czujesz się niepewnie i nieswojo, a twoje myśli ciągle pracują nad tym, by się dowiedzieć, co on knuje!', 'pl'),
(509, 7, 'grid.php', 'box9', '1', 'Nie przysuwaj się tak do mnie, bo mam alergię na sierść...- mówisz starając się ukryć drżenie głosu.', 'pl'),
(510, 7, 'grid.php', 'box9', '2', 'Sam tu mieszkasz?- pytasz  przełykając głośno ślinę.', 'pl'),
(511, 7, 'grid.php', '1.2.next.2.1', '0', 'Nie przysuwaj się tak do mnie, bo mam alergię na sierść...- mówisz starając się ukryć drżenie głosu. -No patrz... A ja tak lubię zapach twojego mięsa... -szepcze pająk i staje gotowy do walki. Nie masz wyjścia- sięgasz po broń.', 'pl'),
(512, 7, 'grid.php', '1.2.next.3', '0', 'Fajna fryzurka- uśmiechasz się do potwora. <br />-Heh dzięki ci, szlachetny wojowniku...-pająk mimowolnie zawstydzonym ruchem przeczesuje włosy i policzki płoną mu rumieńcem. -Wiesz, w podziemiu, przy tej wilgotności trudno utrzymać sierść w dobrej kondycji... Cieszę się, że doceniłeś moje starania...<br />-Nie ma sprawy, stary...', 'pl'),
(513, 7, 'grid.php', 'box10', '1', 'Nie wiesz, czym się kończy ten tunel?', 'pl'),
(514, 7, 'grid.php', 'box10', '2', 'Przejdziesz się ze mną...?', 'pl'),
(515, 7, 'grid.php', '1.2.next.3.1', '0', '-Nie wiesz, czym się kończy ten tunel? Idę już chyba pół godziny i zastanawiam się, czy jest po co...<br />-Jeszcze chwila i dotrzesz do komnaty z posągami- cierpliwości- zachichotał pająk. -Jeśli chcesz, potowarzyszę ci kawałek.', 'pl'),
(516, 7, 'grid.php', '1.2.next.3.2', '0', '-Przejdziesz się ze mną kawałek? We dwóch będzie nam raźniej.<br />-Jasne, odprowadzę cię do najbliższej komnaty', 'pl'),
(517, 7, 'grid.php', '1.2.n.3.n', '0', 'Konwersując o higienie i kosmetyce włosów idziecie przed siebie. Pająk wypytuje o życie w city1a, a ty chętnie opowiadasz mu o cudach, jakie tam widziałeś. Po chwili twój wzrok rozpoznaje w oddali jasny punkt, który pająk opisuje jako Komnatę Posągów.<br />Obaj z radością witacie płonące magicznym ogniem pochodnie i przyspieszacie kroku, by znaleźć się w zasięgu ich światła.', 'pl'),
(518, 7, 'grid.php', '1.2.n.3.n.n', '0', 'Wkraczasz do cudnej komnaty, której zarówno ściany jak i strop pokryte są białym jak śnieg marmurem. Sufit sklepiony na kształt łuku podtrzymują dwa rzędy kolumn przedstawiające smukłe postacie elfów o słodkich, spokojnych twarzach. Podchodzisz do każdego z posągów i przyglądasz się uważnie. Te rysy twarzy kogoś ci przypominają...<br />-Za tobą!!!- krzyk pająka wyrywa cię z zamyślenia. Rozglądasz się i widzisz pojawiającą się jakby spod ziemi grupę 20 goblinów. Sięgasz po miecz, a pająk staje z tobą ramię w ramię.', 'pl'),
(519, 7, 'grid.php', 'winfight5', '0', 'Ocierasz rękawem pot z czoła i dyszysz ciężko ze zmęczenia. Gdy bicie serca nieco się uspokaja, chcesz podziękować pająkowi za ostrzeżenie i pomoc w walce z napastnikiem. Podchodzisz do niego. Jednak wśród lśniącego futra potwora, widzisz krwawą plamę.<br />-Dopadli mnie w trzech... -powiedział z trudem łapiąc oddech. -Nie wiem, skąd się tu wzięli... Odkąd wszyscy przychodzicie tu szukać różdżki, co raz więcej ich się pojawia... Lubią wasze mięso... Mnie nie da się już pomóc, ale dam ci dobrą radę. Zboczyłeś mocno z dobrej drogi, prawy wojowniku, a tą drogą musisz podążać od początku, aż po kres, gdzie czeka nagroda. Tutaj niczego już nie znajdziesz, no może poza śmiercią. Weź to, co mam przy sobie i szukaj różdżki tak, jak ci mówiłem, bo... -jego ciało przebiegł dreszcz i po chwili pająk już nie żył.', 'pl'),
(520, 7, 'grid.php', '1.2.next.2.2', '0', '-Sam tu mieszkasz...?- pytasz przełykając głośno ślinę. Wzrok pająka zdaje się przeglądać twoje myśli. Czujesz jak sięga w najgłębsze zakamarki twej jaźni i ironicznie uśmiecha się na ich widok.<br />-Nie, nie mieszkam sam... Teraz mieszkam tu z tobą... -szepnął paraliżując cię blaskiem swych pustych oczu i powoli owijając twoje ciało pajęczyną. Chcesz walczyć, ale dłoń nie słucha twych rozkazów! Chcesz uciekać, ale skamieniałe ciało stoi w miejscu! Chcesz krzyczeć, ale krzyk umiera w twym gardle zanim się narodzi! Słyszysz tylko chrzęst kruszonych kości i ostatkiem świadomości modlisz się do swych bogów.', 'pl'),
(521, 7, 'grid.php', '1.2.n.3.n.n.n', '0', 'Przeszukujesz z obrzydzeniem ciało martwego pająka. Żadnej kieszeni, sakwy, torby... Ciekawe gdzie mógł coś schować...? Sięgasz w głębię ciała pająka i... wyciągasz zwój pełny złotych monet. <br />Myśląc nad słowami pająka doszukujesz się ukrytego znaczenia rady, jednak nic mądrego nie przychodzi ci do głowy.', 'pl'),
(522, 7, 'grid.php', 'box11', '1', 'zawracasz', 'pl'),
(523, 7, 'grid.php', 'box11', '2', 'idziesz dalej', 'pl'),
(524, 7, 'grid.php', '1.2.n.3.n.n.n.1', '0', 'Myśląc nad słowami pająka doszukujesz się ukrytego znaczenia rady, jednak nic mądrego nie przychodzi ci do głowy. Coś nie daje ci przejść nad tymi słowami do porządku dziennego... -O czym mówił pająk? Zboczyłeś z prawej drogi? - słyszysz w głowie szept Nubii.- Wróć na nią jak najszybciej... W geście zgody kładziesz dłoń na sercu i zawracasz do punktu wyjścia, by jeszcze raz rozpocząć poszukiwania.', 'pl'),
(525, 7, 'grid.php', '1.2.n.3.n.n.n.1.n', '0', 'Zastanawiając się ciągle nad sensem porady podążasz ciemnymi zakątkami podziemi, aż w pewnej chwili otoczenie zmienia się w znajomą ci okolicę. Znów jesteś w city1a.', 'pl'),
(526, 7, 'grid.php', '1.2.n.3.n.n.n.2', '0', '-Fajnie- myślisz sobie,- nagadał frazesów i umarł... Ech z tymi pająkami...! Machasz zrezygnowany ręką i podążasz dalej podziwiając marmurowe posągi elfów. Ciągle masz wrażenie, że ktoś cię obserwuje...', 'pl'),
(527, 7, 'grid.php', '1.2.n.3.n.n.n.2.n', '12123', 'Na końcu marmurowej komnaty widzisz wreszcie wysokie drzwi do polowy przysypane stertą gruzu i kamieni. Z werwą godną prawdziwego zapaleńca odrzucasz przeszkody i po chwili stoisz już u wrót. Gdy jednak chcesz przez nie przejść zauważasz, że w miejscu, gdzie powinna być klamka jest pięć metalowych obręczy opisanych cyframi, z których - jak się domyślasz- należy ułożyć szyfr.  Nad drzwiami połyskuje blaskiem złota napis:<br /><i>Słońce, dłonie, serce, oczy...<br />Bez nich życie źle się toczy.<br />Mało ich, ale wystarczająco wiele.<br />Większą liczbą niech tylko jawią się przyjaciele:<br />Ten, co pomoże, wysłucha, ochroni<br />-lepiej na nich liczyć, niż na ostrze broni.</i>', 'pl'),
(528, 7, 'grid.php', 'answer5', '0', 'Przekręcasz metalowe obręcze i w pewnej chwili słyszysz głuchy trzask. Popychasz lekko drzwi, potem mocniej i mocniej... Czyżby stary mechanizm zawiódł? W pewnej chwili ciszę komnaty przecina głośny zgrzyt. Napis nad wrotami odwraca się wraz z kawałkiem marmurowej ściany, a spod niej wysypuje się na ciebie grad kamieni i gruzu.', 'pl'),
(529, 7, 'grid.php', 'answer4', '0', '-To banalne- mówisz do siebie i ustawiasz szyfr. Jednak drzwi ani drgną. Po chwili zastanowienia próbujesz ponownie.<br /><i>Słońce, dłonie, serce, oczy...<br />Bez nich życie źle się toczy.<br />Mało ich, ale wystarczająco wiele.<br />Większą liczbą niech tylko jawią się przyjaciele:<br />Ten, co pomoże, wysłucha, ochroni<br />-lepiej na nich liczyć, niż na ostrze broni.</i>', 'pl'),
(530, 7, 'grid.php', 'answer6', '0', 'Słońce - jedno, dłonie -dwie, serce- jedno, oczy- dwoje, więcej niż 1 i 2 to trzy... -mruczysz do siebie ustawiając szyfr 12123. Gdy kombinacja łączy wszystkie obręcze, drzwi pod naporem twej dłoni ustępują z łatwością. Za nimi widzisz kolejny tunel...', 'pl'),
(531, 7, 'grid.php', 'answer6.next', '0', 'Tym razem tunel nie jest zbyt długi. Podziwiasz namalowane na jego ścianach postacie wojowników i czas marszu upływa znacznie szybciej. Tunel unosi się nieco i widzisz u jego kresu... przepaść i dalszą część drogi.', 'pl'),
(532, 7, 'grid.php', 'box12', '1', 'próbujesz przeskoczyć', 'pl'),
(533, 7, 'grid.php', 'box12', '2', 'zawracasz', 'pl'),
(534, 7, 'grid.php', '1.3', '0', 'Idziesz w lewo i po chwili klniesz w duchu, że nie zabrałeś z sobą żadnej pochodni. Wszechobecna ciemność nie daje się niczym przełamać, więc brniesz w nią po omacku przesuwając dłonią po wilgotnej ścianie. Nagle zapach stęchlizny przecina powiew świeżego powietrza. Wyciągasz dłoń przed siebie i trafiasz nią prosto w wystający ze ściany posążek jednego z jaszczurczych bożków. Próbujesz mu się przyjrzeć dotykiem i zaczynasz powoli dochodzić do wniosku, że został wykonany z metalu... może ze złota?', 'pl'),
(535, 7, 'grid.php', 'box13', '1', 'zabierasz posążek', 'pl'),
(536, 7, 'grid.php', 'box13', '2', 'idziesz dalej', 'pl'),
(537, 7, 'grid.php', '1.3.1', '0', 'Na targu można za to dostać sporą sumkę, więc zabierasz z sobą posążek. Jednak gdy tylko unosisz go w górę, skalna ściana drży i pomrukuje groźnie. Czujesz się nieswojo słysząc za sobą szelest, którego pochodzenia nie możesz się nawet domyślać. Na wszelki wypadek bierzesz nogi za pas i biegniesz po omacku przed siebie.', 'pl'),
(538, 7, 'grid.php', '1.3.1.next', '0', 'Gdy biegniesz twoja stopa plącze się w wystający korzeń i padasz jak długi na ziemię. Próbujesz się uwolnić, jednak strach przed nieznanym zagrożeniem paraliżuje cię i nie pozwala wyszarpnąć nogi z uwięzi. Nóż!!! -przebiega ci przez myśl, ale jest za późno... Tocząca się tunelem kamienna kula nie omija cię... Przeszywa cię fala bólu, a po chwili ogarnia cię ciemność czarniejsza, niż tunel, w którym przyszło ci być.', 'pl'),
(539, 7, 'grid.php', '1.3.2', '0', 'Złoty posążek... Pewnie sporo waży... Nie, zdecydowanie nie chciało ci się przeciążać nosząc z sobą taki balast. Poza tym, ten bożek w niczym nie przypomina ani Illuminati, ani Anariel, ani Karsetha, ani Heluvalda... Może to jakieś lokalne bóstwo, które pomaga mieszkańcom tego podziemia, więc lepiej ich go nie pozbawiać i bez bożka mają tu ciężkie życie...<br />Myśląc o tym, kto może zamieszkiwać te tunele podążasz dalej przed siebie, gdy nagle potykasz się o stojącą na środku korytarza drewnianą skrzynię. Jest zamknięta na dość wiekowy, przerdzewiały zamek.', 'pl'),
(540, 7, 'grid.php', 'box14', '1', 'wyłamujesz zamek', 'pl'),
(541, 7, 'grid.php', 'box14', '2', 'próbujesz otworzyć zamek', 'pl'),
(542, 7, 'grid.php', '1.3.2.1', '0', 'Zamek bez większego trudu ustępuje pod naporem twej siły, a skrzynia oddaje ci swą zawartość. Zbierasz spróchniałe kawałki desek i podpalasz je niczym pochodnię. Teraz będzie dużo łatwiej iść- mówisz do siebie z promiennym uśmiechem.', 'pl'),
(543, 7, 'grid.php', '1.3.2.2', '0', 'Usilne próby otwarcia antycznego wręcz zamku okazały się ciągle być nieskuteczne. Twoja cierpliwość się skończyła- częstujesz skrzynię potężnym kopniakiem!', 'pl'),
(544, 7, 'grid.php', '1.3.2.1.1', '0', 'Idziesz dalej, a korytarz nie wydaje się być bardziej interesujący, niż w ciemności. Omszałe ściany, wilgoć i smród stęchlizny... aż chce się zawrócić. W tej chwili jednak widzisz przed sobą coś, co pozwoli choć na chwilę przegnać myśl o powrocie. Tunel rozbiega się w dwóch kierunkach.', 'pl'),
(545, 7, 'grid.php', '1.3.2.1.1.n', '0', 'IIdziesz w lewo, ale nie jesteś pewny, czy wybór drogi był trafny. Po chwili czujesz, że to właśnie z tego tunelu wieje co chwila fala świeżego powietrza i od razu poprawia ci się humor. Z nowymi siłami podążasz przed siebie, aż do... ślepego zaułka! -Skąd więc podmuchy wiatru?!- zastanawiasz się.', 'pl'),
(546, 7, 'grid.php', 'box15', '1', 'przeszukujesz tunel', 'pl'),
(547, 7, 'grid.php', 'box15', '2', 'zawracasz', 'pl'),
(548, 7, 'grid.php', '1.3.2.1.1.n.1', '0', 'Nie chcesz wierzyć oczom i poddajesz tunel dokładnym oględzinom. Opłacało się! Gdy przysuwasz pochodnię do ściany, płomień drga, szarpany podmuchami powietrza. Teraz jesteś pewny, że jest tu jakieś przejście i przeszukujesz dalej pomieszczenie.<br />Po chwili widzisz ukryte pod plątaniną korzeni płaskie kwadratowe kamyczki, na których wyryto symbole ras zamieszkujących Vallheru.', 'pl'),
(549, 7, 'grid.php', 'box16', '1', 'wciskasz symbol elfów', 'pl'),
(550, 7, 'grid.php', 'box16', '2', 'wciskasz symbol krasnoludów', 'pl'),
(551, 7, 'grid.php', 'box16', '3', 'wciskasz symbol hobbitów', 'pl'),
(552, 7, 'grid.php', 'box16', '4', 'wciskasz symbol jaszczuroczłeków', 'pl'),
(553, 7, 'grid.php', 'box16', '5', 'wciskasz symbol ludzi', 'pl'),
(554, 7, 'grid.php', '1.3.2.1.1.n.1.1', '0', 'Kamień ustępuje pod naciskiem twojej dłoni i lekko wsuwa się poniżej poziomu innych symboli. Słyszysz szelest za ścianą, więc z zaciekawieniem zbliżasz się do niej. Nagle ze uchylającego się sklepienia tunelu spada na ciebie potężny powietrzny wir i miota twym ciałem obijając je o podłogę, ściany i sufit korytarza.', 'pl'),
(555, 7, 'grid.php', '1.3.2.1.1.n.1.2', '0', 'Skoro te tereny chroni bożek jaszczuroludzi... Jak pomyślałeś, tak też uczyniłeś i po chwili mogłeś się już cieszyć widokiem rozstępującego się sklepienia, przez które przeskoczyłeś szybko i z niecierpliwością, by zobaczyć, co kryje się za tajemniczą ścianą.', 'pl'),
(556, 7, 'grid.php', '1.3.2.1.1.n.1.2.n', '0', 'Wkraczasz do niewielkiego pokoju wyrytego w ziemi, jak i tunel, który do niego prowadził. Na ścianach tkwią lśniące jasnym światłem kamienie, a na suficie wyryto napis: WIECZNOŚÄ†, CZY TYLKO CHWILA...? Ciekawe, o co w tym chodzi... Rozglądasz się dokładniej po pomieszczeniu i znajdujesz stary pergamin. Czytasz na głos wypisane czerwonym atramentem słowa starożytnego języka, którego nie znasz, ani ty, ani nikt z żyjących...', 'pl'),
(557, 7, 'grid.php', '1.3.2.1.1.n.1.2.n.n', '0', '-Wzywałeś mnie? -słyszysz za sobą głos i odwracasz się, by zobaczyć postać błękitnego dżina.', 'pl'),
(558, 7, 'grid.php', 'box17', '1', 'sięgasz po broń', 'pl'),
(559, 7, 'grid.php', 'box17', '2', 'uśmiechasz się przyjaźnie', 'pl'),
(560, 7, 'grid.php', '17.1', '0', 'Sięgasz po broń i rozpoczyna się walka.', 'pl'),
(561, 7, 'grid.php', 'winfight6', '0', 'Mocno zmęczony przeszukujesz z obrzydzeniem ciało martwego dżina, ale jedyne, co na tym zyskujesz, to nowe, mało ciekawe doświadczenie, o którym na pewno nie będziesz chciał opowiadać swoim dzieciom. Rzucasz w kąt pergamin i wracasz do city1b drogą, którą tu przybyłeś.', 'pl'),
(562, 7, 'grid.php', '17.2', '0', 'Uśmiechasz się przyjaźnie przedstawiając swoje imię. <br />-Nie wiedziałem, że ktoś tu jeszcze zajrzy... -ukłonił się dżin. -Tkwię zaklęty w tym pergaminie od kilku er... Dzięki za uwolnienie mnie. Muszę nadrobić stracony czas! Powodzenia!', 'pl'),
(563, 7, 'grid.php', 'box18', '1', 'zatrzymujesz dżina', 'pl'),
(564, 7, 'grid.php', 'box18', '2', 'ponownie przeszukujesz pomieszczenie', 'pl'),
(565, 7, 'grid.php', '17.2.1', '0', '-Ej stój! A co z życzeniami? Uwolnione dżiny ponoć je spełniają! -krzyczysz w ślad za oddalającym się dżinem.<br />-To tylko bajka wciskana grzecznym dzieciom... Ale... hmmm no ok... nie mam czasu na słuchanie życzeń, więc dam ci to. Powodzenia! <br />Dżin wcisnął ci coś w dłoń i zniknął w korytarzu chichocząc rubasznie. Zaglądasz do ręki i nie możesz powstrzymać przekleństwa.', 'pl'),
(566, 7, 'grid.php', '17.2.2', '0', 'Ponownie przeszukujesz pomieszczenie, ale nic tam nie ma. Zawiedziony, zły i głodny zawracasz do city1b.', 'pl'),
(567, 7, 'grid.php', '12.1', '0', 'Próbujesz przeskoczyć przepaść. Rozpęd jest utrudniony wznoszącym się terenem, ale twoje przywykłe do pracy nogi i tym razem nie zawodzą. Odbijasz się mocno i już chwilę potem szybujesz niczym ptak wprost na drugą stronę drogi.', 'pl'),
(568, 7, 'grid.php', '12.2', '0', 'Drogą, którą tu przybyłeś zawracasz do city1b.', 'pl');
INSERT INTO `quests` (`id`, `qid`, `location`, `name`, `option`, `text`, `lang`) VALUES 
(569, 7, 'grid.php', '12.1.n', '0', 'Opadasz na ubitą ziemię i dyszysz wyczerpany.<br />-Ładny skok -słyszysz za sobą. Odwracasz się, by zobaczyć stojącego obok starca w powłóczystej szacie.<br />-Kim jesteś? -pytasz.<br />-A co ci da moje imię? I tak mnie nie znasz... Ale dobrze... Nazywam się Trezor i od wieków strzegę przejścia do komnaty na końcu tej drogi.<br />Popatrzyłeś na kościstego starucha i zaśmiałeś się w duchu. Taka marnota strażnikiem?!<br />-A co jest w tej komnacie?<br />-Odpowiedz na moje pytanie, a sam się przekonasz...', 'pl'),
(570, 7, 'grid.php', 'box19', '1', '-Słucham zatem...', 'pl'),
(571, 7, 'grid.php', 'box19', '2', 'sięgasz po broń', 'pl'),
(572, 7, 'grid.php', '12.1.n.2', '0', 'Sięgasz po broń i szepczesz z chłodem w głosie:<br />-Znam inne metody przechodzenia przez zamknięte drzwi...<br />-Żebyś tylko nie rozbił sobie o nie głowy...<br />Gdy wyciągasz broń, twój przeciwnik mamrocze coś pod nosem i w pewnej chwili czujesz, że nie jesteś już w swoim ciele...', 'pl'),
(573, 7, 'grid.php', '12.1.n.1', 'gwiazdy', 'Starzec kładzie ci dłoń na ramieniu i przez chwilę milczy, po czym zadaje pytanie:?-Co śpi za dnia, by nocą służyć za drogowskaz wędrowcom?', 'pl'),
(574, 7, 'grid.php', 'answer7', '0', '-Gwiazdy... chyba...- mówisz niepewnie, a starzec daje ci klucz i znika, zanim cokolwiek zdążysz mu powiedzieć.<br />Podążasz dalej korytarzem i stajesz przed drzwiami, o których mówił strażnik.', 'pl'),
(575, 7, 'grid.php', 'answer7.next', '0', 'Otwierasz drzwi i stajesz naprożu komnaty. W środku widzisz posąg jakiejś kobiety w płaszczu z kapturem, a u jej stóp widzisz drewniane wieko jakiegoś włazu lub przejścia. Unosisz je z trudem i zanurzasz dłoń w złotych monetach.', 'pl'),
(576, 7, 'grid.php', 'answer7.next.n', '0', 'Sięgasz po złoto raz za razem, aż w pewnej chwili trafiasz dłonią na coś ostrego, co wbija się w twój palec. To grot strzały! O bogowie! Zatruty!', 'pl'),
(577, 7, 'grid.php', '2', '0', 'Postanawiasz zostawić w spokoju tajemnicze znalezisko. Kto wie jakie niebiezpieczeństwa za nim czychają. Odpoczywasz przez chwilę by następnie ponownie zagłębić się w sieć korytarzy labiryntu.', 'pl'),
(578, 7, 'grid.php', 'lostfight1', '0', 'Deszcz strzał spada na ciebie, kiedy przygotowywałeś się do walki. Jak w zwolnionym tempie widzisz, jak jedna po drugiej zagłębiają się w twoim ciele. Potworny ból wstrząsa twoim ciałem. Przez czerwoną mgłę widzisz jak przeciwnicy ponownie napinają łuki. Ostatnią rzeczą jaką zapamiętujesz to lecąca strzała wprost na twoje gardło. Potem ogarnia cię nieprzenikniona ciemność.', 'pl'),
(579, 7, 'grid.php', 'escape1', '0', 'Przerażony rzucasz się do ucieczki, jak najdalej od swych przeciwników. Słyszysz jak strzały świszczą ci koło głowy, za tobą rozglegają się odgłosy pogoni. To wszystko tylko dodaje ci jeszcze sił. Pędem biegniesz do wyjścia z tego mrożącego krew w żyłach korytarza. Dopiero, kiedy docierasz do znanej ci już części labiryntu, przystajesz nasłuchując. Serce wali ci jak oszalałe. Jednak za sobą nie słyszysz już pogoni. Przez chwilę odpoczywasz, by następnie skierować się do wyjścia z labiryntu. Wystarczy ci niebezpieczeństw jak na ten jeden raz.', 'pl'),
(580, 7, 'grid.php', 'answer8', '0', 'Wypowiedziałeś słowo, które miałobyć rozwiązaniem zagadki twoim zdaniem. Przez chwilę czekałeś z nadzieją. Niestety nic się nie stało. Ponownie więc zaczynasz zastanawiać się nad rozwiązaniem.<br /><i>Choć uderza mocno, prawie nieprzerwanie,<br />To nie krzywdzi nigdy, nikogo nie rani.<br />Za to kiedy ból każe mu żyć w mękach,<br />Nie chce cierpieć i wtedy z żalu na kawałki pęka.</i>', 'pl'),
(581, 7, 'grid.php', 'answer2next.1', '0', 'Postanawiasz zawrócić z powrotem do miasta. Masz dość przygód jak na jeden dzień. Przez chwilę stoisz na korytarzu odpoczywając. Następnie zbierasz swoje rzeczy i wracasz z powrotem do wyjścia z tajemniczego korytarza. Kiedy odwróciłeś się, zauważyłeś iż wejście do niego zniknęło! Jest tu teraz tylko gładka ściana. Przez chwilę stoisz zdziwiony, obmacując ją dokładnie. Po chwili, zbierasz swój ekwipunek i kierujesz się w stronę wyjścia z labiryntu. Po jakimś czasie dochodzisz do niego. Przez chwilę przyzwyczajasz się do dawno nie widzianego słonecznego światła.', 'pl'),
(582, 7, 'grid.php', 'winfight7', '0', 'Mocno zmęczony przeszukujesz z obrzydzeniem ścierwo martwych goblinów, ale jedyne, co na tym zyskujesz, to nowe, mało ciekawe doświadczenie, o którym na pewno nie będziesz chciał opowiadać swoim dzieciom.', 'pl'),
(583, 7, 'grid.php', '1.1.1.a', '0', 'Ile jeszcze? -pytasz sam siebie w myślach. Nagle korytarz się rozwidla i stajesz przed izdebką z kamienną ścianą z trojgiem drzwi. Może i wybierzesz któreś z nich.', 'pl'),
(584, 7, 'grid.php', 'box20', '1', 'wybierasz drzwi w prawo', 'pl'),
(585, 7, 'grid.php', 'box20', '2', 'wybierasz drzwi na wprost', 'pl'),
(586, 7, 'grid.php', 'box20', '3', 'wybierasz drzwi w lewo', 'pl'),
(587, 7, 'grid.php', 'lostfight2', '0', 'Uderzenia potężnych łap raz po raz spadają na ciebie, przepełniając całe twe ciało oszałamiającym bólem. Nie myślisz nawet o tym, aby się bronić. Czujesz jak bardzo szybko opuszczają cię siły. W pewnym momencie świat zawirował ci przed oczami i padłeś na kolana na posadzkę. Ostatnią rzeczą jaką zapamiętałeś, to potężna łapa potwora zmierzająca w twoim kierunku. Po tym wydarzeniu nastąpiła nieprzenikniona ciemność. Budzisz się dopiero w szpitalu w mieście.', 'pl'),
(588, 7, 'grid.php', 'escape2', '0', 'Ogarnięty panicznym strachem, rzucasz się do ucieczki. Błyskawicznie wskakujesz do wody i z prędkością o jaką nawet siebie nie podejrzewałeś, płyniesz w kierunku drugiego brzegu. Za sobą cały czas słyszysz wściekłe porykiwania potwora. Strach dodaje ci skrzydeł - kiedy tylko wychodzisz na brzeg, bez namysłu kierujesz się w stronę wyjścia z korytarza. W szaleńczym pędzie, dobiegasz do wyjścia z korytarza. Tutaj dopiero zatrzymujesz się nasłuchując. Serce wali ci jak młot ze zmęczenia, przed oczami latają dziwne plamy. Przez długi czas odpoczywasz na miejscu, nie zwracając nawet uwagi na chłód mokrego ubrania. Następnie postanawiasz wrócić do miasta. Masz dość przygód jak na ten raz.', 'pl'),
(589, 7, 'grid.php', 'answer9', '0', 'Wypowiedziałeś słowo, które miałobyć rozwiązaniem zagadki twoim zdaniem. Przez chwilę czekałeś z nadzieją. Niestety nic się nie stało. Ponownie więc zaczynasz zastanawiać się nad rozwiązaniem.<br /><i>Coś, przed czym w świecie nic nie uciecze,<br />Co gnie żelazo, przegryza miecze,<br />Pożera ptaki, zwierzęta, ziele,<br />Najtwardszy kamień na mąkę miele,<br />Królów nie szczędzi, rozwala mury,<br />Poniża nawet najwyższe góry.</i>', 'pl'),
(590, 7, 'grid.php', 'answer10', '0', 'Twoje myśli nie są wystarczająco skupione na tej czynności. Ciekawe co je rozprasza? Może to stado 20 nadbiegających goblinów z łukami? Rozpoczyna się walka.', 'pl'),
(591, 7, 'grid.php', 'winfight8', '0', 'Mocno zmęczony przeszukujesz z obrzydzeniem ścierwo martwych goblinów, ale jedyne, co na tym zyskujesz, to nowe, mało ciekawe doświadczenie, o którym na pewno nie będziesz chciał opowiadać swoim dzieciom.', 'pl'),
(592, 7, 'grid.php', 'lostfight3', '0', 'Uderzenia potężnych kłów raz po raz spadają na ciebie, przepełniając całe twe ciało oszałamiającym bólem. Nie myślisz nawet o tym, aby się bronić. Czujesz jak bardzo szybko opuszczają cię siły. W pewnym momencie świat zawirował ci przed oczami i padłeś na kolana na posadzkę. Ostatnią rzeczą jaką zapamiętałeś, to potężne kły potwora zmierzające w kierunku twojej szyi. Po tym wydarzeniu nastąpiła nieprzenikniona ciemność. Budzisz się dopiero w szpitalu w mieście.', 'pl'),
(593, 7, 'grid.php', '1.2.next.a', '0', 'Spadaj, parchu!- sięgasz po broń. Pająk błyskawicznie spuszcza się obok ciebie po fosforyzującej błękitem nici i staje gotowy do walki.', 'pl'),
(594, 7, 'grid.php', 'lostfight4', '0', 'Uderzenia potężnych zaklęć raz po raz spadają na ciebie, przepełniając całe twe ciało oszałamiającym bólem. Nie myślisz nawet o tym, aby się bronić. Czujesz jak bardzo szybko opuszczają cię siły. W pewnym momencie świat zawirował ci przed oczami i padłeś na kolana na posadzkę. Ostatnią rzeczą jaką zapamiętałeś, to potężny wir magii zmierzający w twoim kierunku. Po tym wydarzeniu nastąpiła nieprzenikniona ciemność. Budzisz się dopiero w szpitalu w mieście.', 'pl'),
(595, 7, 'grid.php', 'answer11', '0', 'Kiedy wypowiadasz słowo będące twoim zdaniem rozwiązaniem zagadki. Starzec tylko w milczeniu przecząco kręci głową. Przez chwilę stoicie w milczeniu patrząc na siebie. Po jakimś czasie twój przeciwnik ponownie odzywa się:<br />-Co śpi za dnia, by nocą służyć za drogowskaz wędrowcom?', 'pl'),
(596, 7, 'grid.php', 'answer12', '0', 'Twoje myśli nie są wystarczająco skupione na tej czynności. Ciekawe co je rozprasza? Może to stado 20 nadbiegających goblinów z łukami? Rozpoczyna się walka.', 'pl'),
(597, 7, 'grid.php', 'winfight9', '0', 'Mocno zmęczony przeszukujesz z obrzydzeniem ścierwo martwych goblinów, ale jedyne, co na tym zyskujesz, to nowe, mało ciekawe doświadczenie, o którym na pewno nie będziesz chciał opowiadać swoim dzieciom.', 'pl'),
(598, 8, 'grid.php', 'start', '0', 'Spacerując po labiryncie i wypatrując drogocennych rzeczy dochodzisz do miejsca,które szczególnie przykuwa twoja uwagę, a to za sprawą wielkich, stalowych drzwi, na których znajdują się wyryte sceny przedstawiające pole bitwy, zawzięcie walczące istoty, triumf jednych zaś porażkę drugich.. Przyglądając się im z zaciekawieniem, oglądasz doskonale odzwierciedlone szczegóły, wtem Â ogrania cię uczucie chłody i strachu, oddech twój przyspiesza zaś serce zaczyna walić jak młot, stoisz tak w bezruchu i czujesz ze nie jesteś w stanie wykonać żadnej czynności. Czujesz zimno drzwi ich mroczna tajemnice jaka skrywają? Wpatrujesz się w niej jak osłupiały jeszcze przez pewien czas, lecz po upływie kilku minut wszytko powoli zaczyna wracać do normy, oddech i tętno się normują zaś dziwne uczucie chłodu znika. Po opanowaniu zmysłów zastanawiasz się, co zrobić:', 'pl'),
(599, 8, 'grid.php', 'box1', '1', 'Zawrócić', 'pl'),
(600, 8, 'grid.php', 'box1', '2', 'Iść dalej', 'pl'),
(601, 8, 'grid.php', '1', '0', 'Wybierając takie rozwiązanie postanowiłeś nie otwierać tajemniczych drzwi, widocznie ta przygoda nie była tobie pisana.', 'pl'),
(602, 8, 'grid.php', '2', '0', 'Podchodzisz do drzwi i jednym energicznym szarpnięciem otwierasz je. Kurz, który zalegał na nich unosi się w powietrzu. Wyciągasz pochodnie przed siebie i delikatnie oraz ostrożnie zaczynasz iść wzdłuż korytarza. Jest on dość duży, zaś jego ściany pokryte są dziwnym nalotem bez smaku i zapachu. Jest on ślizgi niczym muł rzeczny, lecz skąd tu woda myślisz??  W powietrzu da się wyczuć wilgoć. Nie zważając na dziwny nalot idziesz dalej, kolejne twe kroki rozchodzą się głośny echem po tajemniczym korytarzu.. Ku twojemu zdziwieniu dziwny nalot zaczyna znikać wraz z dalszym zagłębianie się w zakamarki korytarza. W pewnym momencie dochodzisz do rozwidlenia, przystajesz na chwile i myślisz, która drogę wybrać?', 'pl'),
(603, 8, 'grid.php', 'box2', '1', 'Na Wschód', 'pl'),
(604, 8, 'grid.php', 'box2', '2', 'Na Zachód', 'pl'),
(605, 8, 'grid.php', '2.1', '0', 'Wybierając drogę na wschód postanowiłeś pójść mniejszym i bardziej ciasnym korytarzem, wydaje się jak by był on odnogą, głównego korytarzu, lecz mimo tych nieudogodnień idziesz w zaparte dalej. W tych ciemnościach nawet pochodnia za dużo nie daje w szczególności, gdy zacznie unosić się kurz z podłogi to wtedy widoczność jest ograniczona prawie, że do zera. Korytarz ten znacznie różnie się od poprzedniego przede wszystkim rozmiarami i tym ze powietrze w nim jest suche, bez krzty wilgoci. W pewnym momencie dostrzegasz leżące z boku zwłoki. Podchodzisz delikatnie do nich, zaczynasz się schylać nad nimi, gdy w ten czas wyskakuje z nich gromada szczurów, które bardzie się wystraszyły ciebie niż ty ich. Plądrując zwłoki odsłaniasz duże ubranie z skóry jakiegoś zwierzęcia, po wielkości szkieletu widać, iż musiał to być wysoki i dobrze zbudowany osobnik, znajdujesz przy nim dziwny, kamienny, bardzo stary klucz, który wydaje ci się do niczego nie przydatny, lecz na wszelki wypadek bierzesz go. Zostawiasz zwłoki i idziesz dalej, lecz korytarz zaczyna robić się tak mały i wąski, że nie sposób dalej iść. Po chwili zastanowienia postanawiasz zawrócić. Do chodzisz do znajomego ci rozwidlenia, zaś z twojej obserwacji wynika, że pozostała tylko jedna droga, bo wyboru, jeśli oczywiście masz iść zamiar dalej, co robisz?', 'pl'),
(606, 8, 'grid.php', 'box3', '1', 'Idziesz na Południe', 'pl'),
(607, 8, 'grid.php', 'box3', '2', 'Idziesz na Zachód', 'pl'),
(608, 8, 'grid.php', '2.1.1', '0', 'Wybierając tą drogę  postanowiłeś wyjść z labiryntu. Już nigdy się nie dowiesz, do czego mógłby się przydać tajemniczy klucz.', 'pl'),
(609, 8, 'grid.php', '2.2', '0', 'Widoczny jest także ich przywódca tęgi, ubrany w skóry, wysoki ,dobrze zbudowany z olbrzymim toporem, na który artysta uchwycił nawet ślady krwi. Przytłoczony dokładnością szczegółów rozmyślasz o tym co widziałeś, główna twoja myśl skupia się wokół łupów które zobaczyłeś na malowidłach. Prowadząc wewnętrzne przemyślenia dochodzisz do wniosku że owe łupy musza gdzieś się znajdować, a czy jest lepsze miejsce na ukrycie skarbów niż stary i opuszczony labirynt?? Mając tą myśl na uwadze wstajesz i ochoczo udajesz się w dalsza podróż. Po pewnym czasie dochodzisz do kolejnego rozwidlenia, tym razem masz do wyboru dwie drogi', 'pl'),
(610, 8, 'grid.php', 'box4', '1', 'Udajesz się na Północ', 'pl'),
(611, 8, 'grid.php', 'box4', '2', 'Udajesz się na Zachód', 'pl'),
(612, 8, 'grid.php', '2.2.1', '0', 'Wybrałeś drogę na północ gdyż uważałeś ją za bardziej trafiony wybór. Jednakże zagłębiając się coraz dalej w ciemnościach korytarza zaczynam mieć wątpliwości czy dobrze postąpiłeś, z jakiegoś powodu zaczynasz się pocić serce znów bije jak oszalałe a oddech twój jest nierównomierny. Wyczuwasz jakieś niebezpieczeństwo, coś na co nie jesteś przygotowany, cos lub kogoś kto może zakończyć twoją przygodę jednym trafnym ciosem. Jednakże mimo tego przeczucia powoli lecz systematycznie posuwasz się dalej, kroki swe stawisz ostrożnie na zimnej i zakurzonej posadzce. Na ścianach nie ma już malowideł zaś tylko jakieś napisy i teksy w niezrozumiałym dla ciebie dialekcie. Nie wiesz co to może jakaś przepowiednia lub ostrzeżenie przed jakimś niebezpieczeństwem, nie wiesz tego. Po przejściu już znacznego odcinka drogi zaczynasz czuć coraz większe zimno, które tylko potęguje twój strach i zaniepokojenie.<br />W pewnym momencie dochodzisz do kresu korytarza o twoim oczom ukazuje się olbrzymia komnata, tak dużo że światło twej pochodni nie jest w wstanie oświetlić jej całej. Szukając jakiegoś sposobu na rozświetlenie jej zauważasz obok siebie pochodnie przymocowaną do ściany, więc bez namysłu zapalasz ja, gdy to uczyniłeś stało się cos niezwykłego, zapalona pochodnia zaczęła zapalać inne pochodnie i w ten sposób po chwili twoim oczom ukazała się komnata w całej swej okazałości. Tak jak przypuszczałeś była ona ogromna z marmurowym ołtarzem na końcu, który otaczały 4 kolumny z szarego kamienia, pięknie przyozdobione i solidne, zaś na owym ołtarzu znajdowała się znaczna skrzynia. W całej komnacie było zimno dało się zauważyć parę wydobywająca z twoich ust. Pierwsze swe kroki oczywiście skierowałeś w strone fascynującego ołtarza. Po chwili coś jednak zaczęło się dziać, cisza jaka panowała w komnacie została zakłócona, z wszystkich stron zaczęły dochodzić do ciebie dziwne szepty i dźwięki. Byłeś bardzo czujny i gotów do walki. Nagle przed ołtarzem pojawiły się dwie postacie, zapewne strażnicy skarbu jaki tu spoczywa ? pomyślałeś, dwa Lassaukaury, które bez chwili zastanowienie ruszyły na ciebie, co robisz?', 'pl'),
(613, 8, 'grid.php', 'box5', '1', 'Stajesz do walki', 'pl'),
(614, 8, 'grid.php', 'box5', '2', 'Uciekasz', 'pl'),
(615, 8, 'grid.php', '2.2.2', '0', 'Idziesz w kierunku zachodnim, korytarzem może nie tak przestronnym jak poprzedni lecz spokojnie mieszczącym twoją skromna postać. Nie mam w nim nic szczególnego, ściany zbudowane są z szarego kamienia, na którym widoczne są jeszcze ślady dłuta osoby która odcinała go od reszty bloku skalnego w kamieniołomach. Korytarz ten jest kręty jak żaden przedtem, raz to prowadzi w dół zaś innym razem w górę by znów z niespotykanym dla tego rodzaju budowli impetem zagłębić się w głębiny ziemi. Idziesz z pochodnią wyciągnięto ku przodowi zaś druga rękę trzymasz na broni, nigdy nie wiadomo co się czai w ciemnościach ? myślisz sobie, lepiej być przygotowanym. Po przejściu już znacznego odcinka drogi zaczynasz czuć coraz większe zimno, które tylko potęguje twój strach i zaniepokojenie.<br />W pewnym momencie dochodzisz do kresu korytarza o twoim oczom ukazuje się olbrzymia komnata, tak dużo że światło twej pochodni nie jest w wstanie oświetlić jej całej. Szukając jakiegoś sposobu na rozświetlenie jej zauważasz obok siebie pochodnie przymocowaną do ściany, więc bez namysłu zapalasz ja, gdy to uczyniłeś stało się cos niezwykłego, zapalona pochodnia zaczęła zapalać inne pochodnie i w ten sposób po chwili twoim oczom ukazała się komnata w całej swej okazałości. Tak jak przypuszczałeś była ona ogromna z marmurowym ołtarzem na końcu, który otaczały 4 kolumny z szarego kamienia, pięknie przyozdobione i solidne, zaś na owym ołtarzu znajdowała się znaczna skrzynia. W całej komnacie było zimno dało się zauważyć parę wydobywająca z twoich ust. Pierwsze swe kroki oczywiście skierowałeś w stronę fascynującego ołtarza. Po chwili coś jednak zaczęło się dziać, cisza jaka panowała w komnacie została zakłócona, z wszystkich stron zaczęły dochodzić do ciebie dziwne szepty i dźwięki. Byłeś bardzo czujny i gotów do walki. Nagle przed ołtarzem pojawiły się dwie postacie, zapewne strażnicy skarbu jaki tu spoczywa- pomyślałeś, dwa Lassaukaury, które bez chwili zastanowienie ruszyły na ciebie, co robisz?', 'pl'),
(616, 8, 'grid.php', '2.2.2.1', '0', 'Będąc przygotowany na taka możliwość szybko dobierasz broni i rzucasz się w wir walki.', 'pl'),
(617, 8, 'grid.php', 'winfight1', '0', 'Po kilku pierwszych atakach przeciwników wyprowadzasz szybki kontratak i po chwili obydwaj napastnicy leża u twych stóp. Teraz twój widok jedynie przesłania ołtarz na którym znajduje się tajemnicza skrzynia.', 'pl'),
(618, 8, 'grid.php', 'lostfight1', '0', 'Przez pewien czas stawiałeś mężnie opór obu potworom. Niestety twoje zdolności bojowe okazały się za niskie aby zwyciężyć w tej walce. Raz po raz ciosy bestii dosięgały twego ciała, za każdym razem powodując eksplozję bólu w całym ciele. W zwolnionym tempie zobaczyłeś jak jedna z besti wykonuje potężny zamach łapą w kierunku twojej głowy. Następnie ogarnęła ciebie nieprzenikniona ciemność. Budzisz się dopiero w szpitalu w city1a.', 'pl'),
(619, 8, 'grid.php', '2.2.2.1.1', '0', 'Powoli i ostrożnie, lecz zdecydowanym krokiem zbliżasz się do niej. Oglądasz ja dokładnie, jest zamknięta, próbujesz otworzyć ja siła swoich mięśni, lecz to nie skutkuje. Przyglądając się jej jeszcze uważniej zauważasz mały otwór w jednej ze ścian, który kształtem pasuje do klucza, który znalazłeś przy zwłokach w jednym z korytarzy. Co robisz?', 'pl'),
(620, 8, 'grid.php', 'box6', '1', 'Wkładasz klucz do otworu', 'pl'),
(621, 8, 'grid.php', 'box6', '2', 'Zostawiasz skrzynkę w spokoju', 'pl'),
(622, 8, 'grid.php', '2.2.2.1.2', 'enty', 'Powoli i ostrożnie, lecz zdecydowanym krokiem zbliżasz się do niej. Oglądasz ja dokładnie, jest zamknięta, próbujesz otworzyć ja siła swoich mięśni, lecz to nie skutkuje. Przyglądając się jej jeszcze uważniej zauważasz na wiechu skrzyni napisy w znanym ci dialekcie:<br /><i>Nim kopano żelazo, zanim drzewo ścięto,<br />Gdy pagórek był młody pod młodym miesiącem<br />Nim Vallheru powstało, krainę odkryto,<br />To chodziło po lesie lat temu tysiące</i><br />Domyślasz się, że ta zagadka jest kluczem to otwarcia skrzyni. Nie widzisz miejsca na udzielenie odpowiedzi, wiec zapewne wystarczy wypowiedzieć głośnio ten wyraz a skrzynia się otworzy.', 'pl'),
(623, 8, 'grid.php', '2.2.2.2', '0', 'Widząc biegnących na ciebie napastników, wpadasz w panikę i ile sił w nogach rzucasz się od ucieczki, mimo tego iż robisz co możesz, przeciwnicy w mieniu oka dopadają cię.<br />Oślepiający gwizd bólu eksploduje w twojej głowie. Powoli padasz na kolana przed przeciwnikami. Ostatkiem sił widzisz w zwolnionym tempie jak ich ciosy spadają na twe ciało. Potem następuje już tylko ciemność...', 'pl'),
(624, 8, 'grid.php', '2.2.2.1.1.1', '0', 'Zdecydowanym ruchem wsuwasz klucz do dziurki i przekręcasz. Wyraźnie słychać jak zapadki w zamku skrzyni przeskakują. Po chwili skrzynia jest już otwarta. Podnosisz pokrywę zaś w wysadzanej aksamitnym materiałem skrzyni znajdujesz zwój papirusu. Wyciągasz go delikatnie, aby nie uszkodzić i zaczynasz czytać:<br /><i>"Pamiętaj żadne skarby tego świata, ni to diamenty, ni to złoto, ni to srebro, ni to szmaragdy nie są godne doświadczenie. To one ratuje ci życie, gdy przedmioty zawiodą, twe zdolności i umiejętności wiodą cię przez życie o rozwijanie ich powinno być twym celem podstawowym, pamiętając o tym Staniesz się wojownikiem doskonałym."</i><br />Po początkowym zdenerwowaniu, zaczynasz uspokajać się i rozmyślać nad sensem tego, co przeczytałeś. Po chwili przyznajesz racje autorowi tej sentencji i pokornie, lecz z nowym bagażem doświadczeń, które kiedyś mogą ci uratować życie, udajesz się w stronę wyjścia, wracasz do city1b.', 'pl'),
(625, 8, 'grid.php', '2.2.2.1.1.2', '0', 'Postanawiając pozostawić skrzynkę w spokoju zdecydowałeś się wrócić do city1b, lecz czy będziesz w stanie zapomnieć o tym, co tam mogłeś znaleźć to już inna historia.', 'pl'),
(626, 8, 'grid.php', 'answer1', '0', 'Po chwili skrzynia jest już otwarta. Podnosisz pokrywę zaś w wysadzanej aksamitnym materiałem skrzyni znajdujesz zwój papirusu. Wyciągasz go delikatnie aby nie uszkodzić i zaczynasz czytać:<br /><i>"Pamiętaj żadne skarby tego świata, ni to diamenty, ni to złoto, ni to srebro, ni to szmaragdy nie są godne doświadczenie. To one ratuje ci życie gdy przedmioty zawiodą, twe zdolności i umiejętności wiodą cię przez życie o rozwijanie ich powinno być twym celem podstawowym, pamiętając o tym Staniesz się wojownikiem doskonałym."</i><br />Po początkowym zdenerwowaniu, zaczynasz uspokajać się i rozmyślać nad sensem tego, co przeczytałeś. Po chwili przyznajesz racje autorowi tej sentencji i pokornie, lecz z nowym bagażem doświadczeń, które kiedyś mogą ci uratować życie, udajesz się w stronę wyjścia, wracasz do city1b.', 'pl'),
(627, 8, 'grid.php', 'answer2', '0', 'Kiedy wypowiadałeś słowo będące twoim zdaniem odpowiedzią na tę zagadkę z nadzieją spojrzałeś na skrzynię. Niestety ta ani drgnęła, przez chwilę stałeś zawiedziony porażką. Jednak po chwili ponownie zebrałeś się w sobie i postanowiłeś odgadnąć hasło<br /><i>Nim kopano żelazo, zanim drzewo ścięto,<br />Gdy pagórek był młody pod młodym miesiącem<br />Nim Vallheru powstało, krainę odkryto,<br />To chodziło po lesie lat temu tysiące</i><br />', 'pl'),
(628, 8, 'grid.php', 'answer3', '0', 'Kiedy wypowiadałeś słowo będące twoim zdaniem odpowiedzią na tę zagadkę z nadzieją spojrzałeś na skrzynię. Niestety ta ani drgnęła. Kiedy ponownie chciałeś spojrzeć na zagadkę, ze zdziwieniem zauważyłeś iż napis na skrzyni zniknął. Widać bogowie nie sprzyjali ci dzisiaj. Przez chwilę odpoczywasz, następnie zbierasz swój ekwipunek i kierujesz się w stronę wyjścia z labiryntu. Po pewny czasie do twoich oczu dociera blask dawno nie widzianego słońca. Jesteś z powrotem w mieście.', 'pl'),
(629, 9, 'grid.php', 'start', '0', 'Nagle ni stąd, ni zowąd ogarnia cię dziwne uczucie pustki, twoje nogi robią się miękkie niczym z waty. Oddech staje się coraz cięższy i jednocześnie płytszy. Nim dociera do ciebie,że znalazłeś się w miejscu przesiąkniętym do szpiku ciemną magią, twoje kończyny odmawiają posłuszeństwa. Jest już za późno na ucieczkę. Powoli tracisz przytomność...', 'pl'),
(630, 9, 'grid.php', 'next', '0', 'Odzyskujesz świadomość. Głowa nie bolała Cię tak bardzo od czasu ostatnich imienin wujka Jana. Jednak nie to jest twoim największym zmartwieniem. Ku swemu przerażeniu stwierdzasz, że leżysz spętany pośrodku ciemnej wilgotnej groty, a do twych uszu docierają przeraźliwe syki i głuche stąpnięcia. Twoje doświadczenie poszukiwacza przygód sprawia, że nie masz wątpliwości co cię spotkało. Jesteś porwany przez ghoule, które planują złożyć cię w jakiejś rytualnej ofierze, a potem najpewniej spożyć, gdy twoje zwłoki zaczną się już rozkładać. Myślisz, że tym razem kostucha Cię przechytrzyła i już chcesz się żegnać z życiem, gdy czujesz pod ręką jakieś draśnięcie. Na twoje szczęście znalazł się tam jakiś stary grot od strzały. Mimo, że czujesz na jego powierzchni sporo rdzy, to z wielkim wysiłkiem przecinasz liny i rzucasz się po swój ekwipunek. Chwytasz do ręki broń i zdajesz sobie sprawę z beznadziei sytuacji. Nie masz dokąd uciec. Jesteś otoczony i nie ma sposobu by uniknąć walki. Jednak Ty nigdy się nie poddajesz i z okrzykiem bitewnym na ustach ruszasz jako pierwszy do natarcia...', 'pl'),
(631, 9, 'grid.php', 'winfight', '0', 'Nie bez trudu udało ci się pokonać swych przeciwników. Jesteś wyczerpany po walce, więc postanawiasz chwilę odpocząć. Chowasz do kieszeni grot od starej strzały, który uratował ci życie. Po chwili odpoczynku uważnie przyglądasz się jaskini. Widzisz z niej dwa wyjścia. Wąskie tunele prowadzą na południe i na wschód. Dostrzegasz także starą zbutwiałą skrzynie w jednym z rogów pomieszczenia. Po 15 minutach dochodzenia do siebie, podnosisz się na nogi. Najwyższa pora na podjęcie jakiejś decyzji - mówisz sam do siebie.', 'pl'),
(632, 9, 'grid.php', 'lostfight', '0', 'Przez pewien czas mężnie stawiasz opór napastnikom. Niestety, szczęście, które towarzyszyło Tobie do tej pory, teraz odwróciło się od Ciebie. Zmęczenie oraz przewaga przeciwników dają znać o sobie. Raz za razem Twe ciało przeszywa fala bólu osłabiając Ciebie coraz bardziej. Nagle w Twej głowie eksploduje najjaśniejsza gwiadza jaką w życiu widziałeś. W zwolnionym tempie Twoje martwe ciało pada na ziemię. Po chwili ogarnia Ciebie nieprzenikniona ciemność.', 'pl'),
(633, 9, 'grid.php', 'escape', '0', 'Postanawiasz raz jeszcze zaufać swojemu szczęściu. Odwracasz się plecami do przeciwników i szybko ruszasz w drogę powrotną. Zza pleców przez jakiś jeszcze czas słyszysz odgłosy pogoni. Kiedy znikają, biegniesz jeszcze przez chwilę. Po pewnym czasie przystajesz nieco odpocząć. Masz dość przygód jak na ten jeden raz. Po krótkim postoju zbierasz swój ekwipunek i kierujesz się w stronę city1b. Czas wzmocnić się czymś mocniejszym w karczmie.', 'pl'),
(634, 9, 'grid.php', 'box1', '1', 'idź na południe', 'pl'),
(635, 9, 'grid.php', 'box1', '2', 'idź na wschód', 'pl'),
(636, 9, 'grid.php', 'box1', '3', 'dokładnie zbadaj skrzynię', 'pl'),
(637, 9, 'grid.php', '3', 'XXXIV', 'Mimo usilnych prób siła twoich mięśni jest niewystarczająca do otwarcia skrzyni. Nie pomaga także grzebanie wytrychem. Gdy uważniej przyglądasz się skrzyni, odkrywasz zatarty stary napis:<br /><i>Gdy jako I młodzieniec z city1b przechadzałem się po I parku, lubiłem z dala obserwować II króliki. Każdego wyróżniały III cechy szczególne jak np. brunatne łapki, czy też V plamki na nosie. Nie każdy jednak podziwiał mój entuzjazm do tych VIII zwierzątek. Niektórzy woleli na nie polować i kosztować pysznego pasztetu. Inni wykorzystywali je jako przynęty do polowań na XIII lisy, z których robili piękne futra. Prawda była taka, że bardzo szybko XXI króliki zniknęły z parku. Wtedy przestałem je oglądać. Miałem wtedy [............] lat. Teraz, jako stary człowiek nadal czuję sentyment do tych zwierząt. Może powinienem założyć własną hodowle.</i><br />Część napisu jest całkowicie nienaruszona, a po jej środku znajduje się puste pole. Po chwili zastanowienia przykładasz w to miejsce palec i kreślisz nim po powierzchni skrzyni:', 'pl'),
(638, 9, 'grid.php', 'answer1', '0', 'Gdy odrywasz palec od skrzyni, wieko bardzo powoli zaczyna się unosić. Gdy skrzypienie zawiasów ustało, zaglądasz do środka. Wewnątrz skrzyni znajduje się stara, zakurzona szata. Nie masz pewności, ale zabierasz ją w nadziei, że jest magiczna.', 'pl'),
(639, 9, 'grid.php', 'answer2', '0', 'Skrzynia rozsypała się w proch i żadna, nawet magiczna siła nie jest już wstanie przywrócić jej do poprzedniego kształtu. Nie masz już czego tu szukać. Nadszedł czas by opuścić to miejsce.', 'pl'),
(640, 9, 'grid.php', 'lostfight2', '0', 'Furia ataku potwora zupełnie zaskoczyła Ciebie. Próbowałeś przez pewien czas opierać się jego atakom, jednak zmęczenie oraz przewaga przeciwnika dały o sobie znać. W zwolnionym tempie widzisz jak potężna kamienna pięść spada na Twą głowę. Potem już nastaje nieprzenikniona ciemność oraz cisza.', 'pl'),
(641, 9, 'grid.php', 'escape2', '0', 'Strach dodaje skrzydeł - powiadają. I w tym wypadku rzeczywiście uskrzydlił Ciebie. Płynnym ruchem uchyliłeś się przed ciosem golema, szybko odwróciłeś się i zacząłeś uciekać. Przez pewien czas słyszałeś za sobą ciężkie kroki pogoni dudniące po ziemi. Jednak z czasem ucichły one. Przystajesz na moment zdyszany. Masz tego wszystkiego dość, zbyt wiele niebezpieczeństw czyha na tej drodze. Postanawiasz powrócić do miasta. Zbierasz swój ekwipunek i ruszasz w drogę powrotną. Może następnym razem będziesz miał więcej szczęścia.', 'pl'),
(642, 9, 'grid.php', 'answer4', '0', 'Niestety mimo nawoływań i krzyków nic się nie wydarzyło. Nie zadziałała żadna magia. Za to najrozmaitsze magiczne stwory natychmiast cię otoczyły. Nie masz żadnych szans w starciu z potęgą ich ilości. Atakowany przez dziesiątki goblinów i orków walczysz dzielnie, lecz w końcu ulegasz.', 'pl'),
(643, 9, 'grid.php', '2', '0', 'Obrana droga jest prosta i wygodna. Jakby kiedyś była wykorzystywana jako uczęszczane przejście podziemne. Udaje ci się także znaleźć kawałek drewna i naprędce zrobić z niego całkiem przyzwoitą pochodnię. Korytarz wiedzie cały czas prosto. Nie ma tu żadnych bocznych przejść, ani zakrętów. Po kilkukilometrowym marszu wprost przed siebie dochodzisz do ogromnej i przepięknej komnaty rozświetlonej milionem białych i niebieskich kryształów. Jej ściany są wyłożone gładkim jak pupa niemowlęcia marmurem, jakiego w swym pałacu nie ma nawet sam król Thindil. Podłoga, mimo że także jest marmurowa jest dziwnie ciepła i przyjemna w dotyku. Po środku komnaty znajduje się całkiem spore jezioro. Podchodzisz do niego, by zaspokoić pragnienie po długim marszu. Schylasz się nad brzegiem i łapczywie połykasz chłodny płyn. Lecz w pewnym momencie odnosisz dziwne wrażenie, że nie jesteś sam. Unosisz głowę do góry i widzisz przed sobą niezwykłej urody elfkę. Ubrana jest ona w prostą, jasną, jedwabną suknię, a jej gołe stopy unoszą się na tafli wody. Nie wiesz, czy wyciągać broń, czy się odezwać, a może ruszyć do ucieczki jak to zawsze radziła ciocia Róża. Pierwsza jednak odzywa się piękna nieznajoma:<br /><i>Witaj wędrowcze. Daleko zaszedłeś od swych rodzinnych stron. Nie jest to bowiem miejsce przeznaczone dla śmiertelników. Jest to magiczny świat zawieszony w eterze stworzony przed wiekami przez samego Iluminati i również przed wiekami zapomniany. Dobrze wiesz, że nie możesz tu zostać. Zaraz przeniosę Cię z powrotem do twej rodzinnej city1b. Już nie wrócisz do tego miejsca, ale wiedz, że zabierzesz jego cząstkę ze sobą. Woda w tym jeziorze nie jest zwykła. To tu spływają łzy naszego pana Iluminati i ma ona magiczne właściwości. Ponieważ jej skosztowałeś jego łaska zeszła na Ciebie. Wykorzystaj ją do właściwych celów wędrowcze. Żegnaj.</i><br />Nim zdążyłeś otworzyć usta w głowie zaczęło Ci wirować, a gdy odzyskałeś świadomość byłeś znów w city1a, pośród wąskich i krętych ulic slumsów.', 'pl'),
(644, 9, 'grid.php', 'box2', '1', 'idź na południe', 'pl'),
(645, 9, 'grid.php', 'box2', '2', 'idź na wschód', 'pl'),
(646, 9, 'grid.php', 'winfight2', 'WYJŚCIE', 'Udało Ci się wyjść cało ze spotkania z potworną bestią. Twoim największym marzeniem w tym momencie jest kufel zimnego piwa. Wyczerpany brniesz dalej, gdyż wiesz, że zatrzymywanie się w miejscu może jedynie ściągnąć kolejne dziwne stwory. Gdy już zaczynasz myśleć, że nie uda ci się wyjść cało z tej sytuacji, czujesz przepływ powietrza. Dziwi cię to tym bardziej, że powietrze wylatuje jakby wprost ze ściany. Podchodzisz do niej bliżej, lecz jest to twarda, lita skała. Jej powierzchnię zdobi dziwny wzór:<br /><br />[- ][+][- ][+][- ][+][+][+]<br />[- ][+][- ][+][+][- ][- ][+]<br />[- ][+][- ][- ][+][- ][+][- ]<br />[- ][+][- ][+][- ][- ][+][+]<br />[- ][+][- ][- ][- ][- ][+][+]<br />[- ][+][- ][- ][+][- ][- ][+]<br />[- ][+][- ][- ][- ][+][- ][+]<br /><br />W tym momencie słyszysz dobiegające cię zewsząd odgłosy krwiożerczych bestii. Jeszcze chwila i wpadną wprost na Ciebie. Wiesz, że twoją jedyną szansą są dziwne znaczki na ścianie. Twój mózg jeszcze nigdy nie pracował na takich obrotach. Musisz natychmiast podjąć decyzję, a wypowiedzieć możesz tylko jedno słowo:', 'pl'),
(647, 9, 'grid.php', '1', '0', 'Zagłębiasz się w ciemnym i wąskim korytarzu. Poruszasz się w zupełnej ciemności. Masz tego dnia pecha. Zawsze nosisz ze sobą krzesiwo, a tego dnia akurat musiałeś je zgubić. Prawdopodobnie cieszy się teraz z niego jakiś pijak w karczmie. Nic nie widząc, obijasz się o wystające zewsząd stalaktyty i stalagmity. Mimo to nie zatrzymujesz się. Brniesz dalej wgłąb skalnego labiryntu wymacując przed sobą drogę. W pewnym momencie doznajesz dziwnego wrażenia - jakby skała o którą się oparłeś poruszyła się. Zmysły cię nie myliły. Po chwili nad głową widzisz dwa czerwone punkciki wpatrujące się w Ciebie. Dzięki swemu wrodzonemu sprytowi udało Ci się uniknąć miażdżącego ciosu, lecz nie masz szans na ucieczkę wśród ciemności. Musisz stoczyć walkę z kamiennym golemem. Nim zdążyłeś siarczyście zaklnąć, rozpoczyna się starcie. Walka o życie, z której zwycięsko może wyjść tylko jeden.', 'pl'),
(648, 9, 'grid.php', 'answer3', '0', 'Po wykrzyknięciu słowa "WYJSCIE" obraz zaczął ci się rozmywać przed oczyma. Po chwili nie widzisz nic oprócz idealnej czerni. Zastanawiasz się, czy tak właśnie wygląda śmierć. Po chwili ciemność zaczyna się rozjaśniać, a jej miejsce zajmują czerwone pasy światła. Wtedy dostrzegasz, że wokół ciebie nie ma nic, a sam lewitujesz w niczym nieograniczonej przestrzeni. Światło dobiega cię zza pleców. Odwracasz się i twym oczom ukazuje się przepięknej urody miecz. Jego rękojeść jest  bogato zdobiona krwistoczerwonymi rubinami, a ostrze lśni, jakby w jego wnętrzu zamknięte były miliony świetlików. Wtedy dociera do ciebie, że dostąpiłeś niewyobrażalnego zaszczytu. Ten miecz jest darem od samego Karsetha, boga wojny, który z jakiegoś powodu wybrał właśnie Ciebie. Kładziesz dłoń na rękojeści i wszystko znowu zaczyna wirować. Gdy ponownie odzyskujesz świadomość, leżysz w jednym z rynsztoków city1b, a w ręku ściskasz rękojeść bogato zdobionej broni.', 'pl'),
(649, 10, 'grid.php', 'start', '0', 'Znalazłeś się w ciemnej i zaniedbanej części labiryntu. Z lekką odrazą badasz wilgotne ściany, uważając, żeby się nie przewrócić. Mimo Twojej czujności grunt osuwa Ci się spod stopy. Rzucasz się w tył, upadasz, syczysz z bólu po twardym lądowaniu. Z irytacją wyciągasz ręce, badasz otoczenie i odkrywasz, że ziemia osunęła się tworząc wchodzący pod ścianę tunel. Woda z korytarza spływa tędy gdzieś w dół. Kąt nachylenia daje szansę na powrót, jeśli zdecydujesz się na zjazd w ciemność po błotno-kamienistej pochylni.', 'pl'),
(650, 10, 'grid.php', 'box1', '1', 'Poświęcasz ubranie i ostrożnie zsuwasz się w tunel.', 'pl'),
(651, 10, 'grid.php', 'box1', '2', 'Wstajesz, otrzepujesz ubranie, łukiem omijasz zdradliwe osuwisko i ruszasz dalej korytarzami labiryntu.', 'pl'),
(652, 10, 'grid.php', '1.1', '0', 'Cały ubłocony lądujesz w niewielkiej grocie. Ze zdziwieniem spostrzegasz, ze zrobiło się jasno. Mdłe światło pochodzi od fosforyzujących grzybów pokrywających wilgotne ściany. Płynie tu podziemny strumień, który wcina się w skałę tworząc wąską szczelinę. ', 'pl'),
(653, 10, 'grid.php', '1.1.next', '0', 'Zanurzasz stopy w wodzie, zimno sprawia Ci dotkliwy ból. Wciągając brzuch, na wydechu, przeciskasz się z trudem dalej. Po kilku chwilach, które dłużyły się niemiłosiernie, wychodzisz do większej groty. Strumień płynie dalej, na ścianie nad nim te same grzyby emitują bladą poświatę. Zdumiony pięknym widokiem uderzasz głową w stalaktyt. Rozcierasz rozbite czoło i rozglądasz się spokojniej.', 'pl'),
(654, 10, 'grid.php', '1.1.n.n', '0', 'Jaskinia w której się znajdujesz pełna jest wspaniałych stalaktytów i stalagmitów, niektóre z nich łączą się w dostojne kolumny. Koniec groty niknie gdzieś w mroku, gdzie nie sięga nędzny blask grzybów znad strumienia. Odgłos kapiącej wody brzmi jak tajemniczy dzwon. W zachwycie robisz kilka niebacznych kroków i potykasz się o odłamany stalaktyt. Już masz to zignorować, kiedy orientujesz się... że widziałeś w kamieniu błysk? Kucasz, żeby się przyjrzeć.', 'pl'),
(655, 10, 'grid.php', '1.1.n.n.n', '0', 'Z kamienia wystaje duży kryształ. Wyjmujesz nóż i pieczołowicie wydłubujesz go. Kiedy tak klęczysz, na podłodze dostrzegasz kolejne dwa kryształy między odłamami skalnymi. Podnosisz się z trzema klejnotami w dłoni i z zamyśleniem zerkasz na zwieszające się z sufitu stalaktyty. Bardzo prawdopodobne, że kryją w sobie kolejne skarby.', 'pl'),
(656, 10, 'grid.php', 'box2', '1', 'Postanawiasz, ze nie będziesz niszczyć tak zachwycającego miejsca i odchodzisz z tym, co udało Ci się znaleźć.', 'pl'),
(657, 10, 'grid.php', 'box2', '2', 'Z pomocą noża i kamieni próbujesz odłupać tak wiele stalaktytów ile zdołasz.', 'pl'),
(658, 10, 'grid.php', '2.1', '0', 'Wracasz do mrocznych korytarzy labiryntu ubłocony, z trzema kryształami i zachwytem w sercu. Postarasz się zapamiętać lokalizację groty i wrócić tam kiedyś z lampką, żeby obejrzeć cuda przyrody ponownie.', 'pl'),
(659, 10, 'grid.php', 'str1', '0', 'Nie okazałeś się dość silny, żeby cokolwiek zdziałać. Zły odchodzisz z trzema kryształami.', 'pl'),
(660, 10, 'grid.php', 'str2', '0', 'Udaje Ci się oderwać jeszcze kilka mniejszych stalaktytów... Gorączkowo dłubiesz w nich, wyłuskując kryształy.', 'pl'),
(661, 10, 'grid.php', 'agi1', '0', 'Niestety łamiesz nóż i przecinasz sobie dłoń a kamień spada Ci na stopę. Krzyczysz z bólu. Podnosisz kolejny kryształ i odchodzisz niezadowolony, utykając.', 'pl'),
(662, 10, 'grid.php', 'agi2', '0', 'Sprawnie manewrując prymitywnymi narzędziami wydobywasz 8 kryształów. Odchodzisz pozostawiając zdewastowaną grotę.', 'pl'),
(663, 10, 'grid.php', 'end1', '0', 'Postanawiasz zostawić ten tunel w spokoju. Kto wie jakie niebezpieczeństwa tam na Ciebie czekają. Szybko zbierasz się i ruszasz ponownie na zwiedzanie labiryntu.', 'pl');

-- --------------------------------------------------------

-- 
-- Struktura tabeli dla  `replies`
-- 

CREATE TABLE `replies` (
  `id` int(11) NOT NULL auto_increment,
  `starter` varchar(30) NOT NULL default '',
  `topic_id` text NOT NULL,
  `body` text NOT NULL,
  `gracz` int(11) NOT NULL default '0',
  `lang` varchar(3) NOT NULL default 'pl',
  `w_time` bigint(20) NOT NULL default '0',
  UNIQUE KEY `id` (`id`)
) TYPE=MyISAM PACK_KEYS=0 AUTO_INCREMENT=1 ;

-- 
-- Zrzut danych tabeli `replies`
-- 


-- --------------------------------------------------------

-- 
-- Struktura tabeli dla  `reset`
-- 

CREATE TABLE `reset` (
  `id` int(11) NOT NULL auto_increment,
  `player` int(11) NOT NULL default '0',
  `code` int(11) NOT NULL default '0',
  KEY `id` (`id`)
) TYPE=MyISAM AUTO_INCREMENT=1 ;

-- 
-- Zrzut danych tabeli `reset`
-- 


-- --------------------------------------------------------

-- 
-- Struktura tabeli dla  `rings`
-- 

CREATE TABLE `rings` (
  `id` int(11) NOT NULL auto_increment,
  `name` varchar(60) NOT NULL default '',
  `amount` int(11) NOT NULL default '0',
  `lang` varchar(2) NOT NULL default 'pl',
  KEY `id` (`id`)
) TYPE=MyISAM  AUTO_INCREMENT=7 ;

-- 
-- Zrzut danych tabeli `rings`
-- 

INSERT INTO `rings` (`id`, `name`, `amount`, `lang`) VALUES 
(1, 'pierścień nowicjusza siły', 28, 'pl'),
(2, 'pierścień nowicjusza zręczności', 28, 'pl'),
(3, 'pierścień nowicjusza inteligencji', 28, 'pl'),
(4, 'pierścień nowicjusza siły woli', 28, 'pl'),
(5, 'pierścień nowicjusza szybkości', 28, 'pl'),
(6, 'pierścień nowicjusza wytrzymałości', 28, 'pl');

-- --------------------------------------------------------

-- 
-- Struktura tabeli dla  `sessions`
-- 

CREATE TABLE `sessions` (
  `sesskey` varchar(32) binary NOT NULL default '',
  `expiry` int(11) unsigned NOT NULL default '0',
  `expireref` varchar(64) default NULL,
  `data` longtext,
  PRIMARY KEY  (`sesskey`),
  KEY `expiry` (`expiry`)
) TYPE=MyISAM;

-- 
-- Zrzut danych tabeli `sessions`
-- 


-- --------------------------------------------------------

-- 
-- Struktura tabeli dla  `settings`
-- 

CREATE TABLE `settings` (
  `setting` varchar(255) NOT NULL default '',
  `value` varchar(255) default NULL,
  KEY `setting` (`setting`),
  KEY `value` (`value`)
) TYPE=MyISAM;

-- 
-- Zrzut danych tabeli `settings`
-- 

INSERT INTO `settings` (`setting`, `value`) VALUES 
('maps', '20'),
('item', NULL),
('player', NULL),
('open', 'Y'),
('reset', 'N'),
('warriors', '0'),
('archers', '0'),
('catapults', '0'),
('barricades', '0'),
('close_reason', ''),
('copper', '7'),
('iron', '15'),
('coal', '3'),
('mithril', '24'),
('adamantium', '24'),
('meteor', '395'),
('crystal', '30'),
('illani', '31'),
('illanias', '20'),
('nutari', '33'),
('dynallca', '35'),
('register', 'Y'),
('close_register', ''),
('poll', 'N'),
('age', '1'),
('day', '1'),
('copperore', '3'),
('zincore', '5'),
('tinore', '7'),
('ironore', '11'),
('bronze', '18'),
('brass', '22'),
('steel', '5'),
('pine', '7'),
('hazel', '10'),
('yew', '12'),
('elm', '10'),
('illani_seeds', '26'),
('illanias_seeds', '12'),
('nutari_seeds', '30'),
('dynallca_seeds', '25'),
('caravan', 'N'),
('metakeywords', NULL),
('metadescr', NULL),
('tribe', NULL),
('caravanday', '0');

-- --------------------------------------------------------

-- 
-- Struktura tabeli dla  `smelter`
-- 

CREATE TABLE `smelter` (
  `owner` int(11) NOT NULL default '0',
  `level` tinyint(2) NOT NULL default '0',
  KEY `owner` (`owner`)
) TYPE=MyISAM;

-- 
-- Zrzut danych tabeli `smelter`
-- 


-- --------------------------------------------------------

-- 
-- Struktura tabeli dla  `smith`
-- 

CREATE TABLE `smith` (
  `id` int(11) NOT NULL auto_increment,
  `owner` int(11) NOT NULL default '0',
  `name` varchar(60) NOT NULL default '',
  `type` char(1) NOT NULL default '',
  `cost` int(11) NOT NULL default '0',
  `amount` int(11) NOT NULL default '0',
  `level` tinyint(4) NOT NULL default '0',
  `lang` varchar(2) NOT NULL default 'pl',
  `twohand` char(1) NOT NULL default 'N',
  KEY `id` (`id`),
  KEY `owner` (`owner`)
) TYPE=MyISAM  AUTO_INCREMENT=78 ;

-- 
-- Zrzut danych tabeli `smith`
-- 

INSERT INTO `smith` (`id`, `owner`, `name`, `type`, `cost`, `amount`, `level`, `lang`, `twohand`) VALUES 
(2, 0, 'Bajdana', 'A', 4000, 8, 3, 'pl', 'N'),
(4, 0, 'Anima', 'A', 1000, 2, 1, 'pl', 'N'),
(5, 0, 'Brygantyna', 'A', 8000, 16, 5, 'pl', 'N'),
(6, 0, 'Koszulka kolcza', 'A', 16000, 40, 10, 'pl', 'N'),
(7, 0, 'Kaftan kolczy', 'A', 32000, 72, 15, 'pl', 'N'),
(8, 0, 'Kirys', 'A', 64000, 120, 20, 'pl', 'N'),
(9, 0, 'Kolczuga', 'A', 128000, 172, 25, 'pl', 'N'),
(10, 0, 'Zbroja lamelkowa', 'A', 256000, 240, 30, 'pl', 'N'),
(11, 0, 'Zbroja łuskowa', 'A', 512000, 400, 40, 'pl', 'N'),
(12, 0, 'Zbroja karacenowa', 'A', 1024000, 600, 50, 'pl', 'N'),
(13, 0, 'Zbroja paskowa', 'A', 2048000, 840, 60, 'pl', 'N'),
(14, 0, 'Karacena', 'A', 4096000, 1120, 70, 'pl', 'N'),
(15, 0, 'Zbroja półpłytowa', 'A', 8192000, 1440, 80, 'pl', 'N'),
(16, 0, 'Zbroja płytowa', 'A', 16384000, 1800, 90, 'pl', 'N'),
(17, 0, 'Zbroja zwierciadlana', 'A', 32768000, 2200, 100, 'pl', 'N'),
(18, 0, 'Mały puklerz', 'S', 500, 1, 1, 'pl', 'N'),
(19, 0, 'Puklerz', 'S', 2000, 3, 3, 'pl', 'N'),
(20, 0, 'Mała tarcza', 'S', 4000, 8, 5, 'pl', 'N'),
(21, 0, 'Sipar', 'S', 8000, 20, 10, 'pl', 'N'),
(22, 0, 'Średnia tarcza', 'S', 16000, 36, 15, 'pl', 'N'),
(23, 0, 'Trójkątna tarcza', 'S', 32000, 60, 20, 'pl', 'N'),
(24, 0, 'Wielka tarcza', 'S', 64000, 86, 25, 'pl', 'N'),
(25, 0, 'Tarcza migdałowa', 'S', 128000, 120, 30, 'pl', 'N'),
(26, 0, 'Prostokątna tarcza', 'S', 256000, 200, 40, 'pl', 'N'),
(27, 0, 'Pawęż', 'S', 512000, 300, 50, 'pl', 'N'),
(28, 0, 'Ciężka tarcza', 'S', 1024000, 420, 60, 'pl', 'N'),
(29, 0, 'Tarcza turniejowa', 'S', 2048000, 560, 70, 'pl', 'N'),
(30, 0, 'Rycerska tarcza', 'S', 4096000, 720, 80, 'pl', 'N'),
(31, 0, 'Kolczasta tarcza', 'S', 8192000, 900, 90, 'pl', 'N'),
(32, 0, 'Żołnierska tarcza', 'S', 16384000, 1100, 100, 'pl', 'N'),
(33, 0, 'Kolczy czepiec', 'H', 500, 1, 1, 'pl', 'N'),
(34, 0, 'Szyszak', 'H', 2000, 4, 3, 'pl', 'N'),
(35, 0, 'Szyszak z kołnierzem', 'H', 4000, 8, 5, 'pl', 'N'),
(36, 0, 'Kapalin', 'H', 8000, 20, 10, 'pl', 'N'),
(37, 0, 'Łebka', 'H', 16000, 36, 15, 'pl', 'N'),
(38, 0, 'Hełm otwarty', 'H', 32000, 60, 20, 'pl', 'N'),
(39, 0, 'Hełm stożkowy', 'H', 64000, 86, 25, 'pl', 'N'),
(40, 0, 'Hełm garnczkowy', 'H', 128000, 120, 30, 'pl', 'N'),
(41, 0, 'Hełm zamknięty', 'H', 256000, 200, 40, 'pl', 'N'),
(42, 0, 'Hełm obręczowy', 'H', 512000, 300, 50, 'pl', 'N'),
(43, 0, 'Hełm rycerski', 'H', 1024000, 420, 60, 'pl', 'N'),
(44, 0, 'Hełm przyłbicowy', 'H', 2048000, 560, 70, 'pl', 'N'),
(45, 0, 'Armet', 'H', 4096000, 720, 80, 'pl', 'N'),
(46, 0, 'Rogaty hełm', 'H', 8192000, 900, 90, 'pl', 'N'),
(47, 0, 'Wielki hełm', 'H', 16384000, 1100, 100, 'pl', 'N'),
(48, 0, 'Ochraniacze kolcze', 'L', 500, 1, 1, 'pl', 'N'),
(49, 0, 'Nagolenniki kolcze', 'L', 2000, 4, 3, 'pl', 'N'),
(50, 0, 'Nagolenniki żeberkowe', 'L', 8000, 20, 10, 'pl', 'N'),
(51, 0, 'Nagolenniki łuskowe', 'L', 32000, 60, 20, 'pl', 'N'),
(52, 0, 'Nagolenniki paskowe', 'L', 128000, 120, 30, 'pl', 'N'),
(53, 0, 'Nagolenniki lamelkowe', 'L', 512000, 300, 50, 'pl', 'N'),
(54, 0, 'Nagolenniki półpłytowe', 'L', 2048000, 560, 70, 'pl', 'N'),
(55, 0, 'Nagolenniki płytowe', 'L', 8192000, 900, 90, 'pl', 'N'),
(56, 0, 'Nogawice kolcze', 'L', 4000, 8, 5, 'pl', 'N'),
(57, 0, 'Nogawice żeberkowe', 'L', 16000, 36, 15, 'pl', 'N'),
(58, 0, 'Nogawice łuskowe', 'L', 64000, 86, 25, 'pl', 'N'),
(59, 0, 'Nogawice paskowe', 'L', 256000, 200, 40, 'pl', 'N'),
(60, 0, 'Nogawice lamelkowe', 'L', 1024000, 420, 60, 'pl', 'N'),
(61, 0, 'Nogawice półpłytowe', 'L', 4096000, 720, 80, 'pl', 'N'),
(62, 0, 'Nogawice płytowe', 'L', 16384000, 1100, 100, 'pl', 'N'),
(63, 0, 'Krótki miecz', 'W', 500, 1, 1, 'pl', 'N'),
(64, 0, 'Topór ręczny', 'W', 2000, 4, 3, 'pl', 'N'),
(65, 0, 'Rapier', 'W', 4000, 8, 5, 'pl', 'N'),
(66, 0, 'Szabla', 'W', 8000, 20, 10, 'pl', 'N'),
(67, 0, 'Morgensztern', 'W', 16000, 36, 15, 'pl', 'N'),
(68, 0, 'Pałasz', 'W', 128000, 120, 30, 'pl', 'N'),
(69, 0, 'Lekki korbacz', 'W', 64000, 86, 25, 'pl', 'N'),
(70, 0, 'Długi miecz', 'W', 1024000, 420, 60, 'pl', 'N'),
(71, 0, 'Topór żołnierski', 'W', 256000, 200, 40, 'pl', 'N'),
(72, 0, 'Młot bojowy', 'W', 512000, 300, 50, 'pl', 'N'),
(73, 0, 'Scimitar', 'W', 32000, 60, 20, 'pl', 'N'),
(74, 0, 'Ciężki korbacz', 'W', 2048000, 560, 70, 'pl', 'N'),
(75, 0, 'Katana', 'W', 4096000, 720, 80, 'pl', 'N'),
(76, 0, 'Topór bitewny', 'W', 8192000, 900, 90, 'pl', 'N'),
(77, 0, 'Bastard', 'W', 16384000, 1100, 100, 'pl', 'N');

-- --------------------------------------------------------

-- 
-- Struktura tabeli dla  `smith_work`
-- 

CREATE TABLE `smith_work` (
  `id` int(11) NOT NULL auto_increment,
  `owner` int(11) NOT NULL default '0',
  `name` varchar(60) NOT NULL default '',
  `n_energy` smallint(4) NOT NULL default '0',
  `u_energy` smallint(4) NOT NULL default '0',
  `mineral` varchar(10) NOT NULL default '',
  KEY `id` (`id`)
) TYPE=MyISAM AUTO_INCREMENT=1 ;

-- 
-- Zrzut danych tabeli `smith_work`
-- 


-- --------------------------------------------------------

-- 
-- Struktura tabeli dla  `topics`
-- 

CREATE TABLE `topics` (
  `id` int(11) NOT NULL auto_increment,
  `topic` text NOT NULL,
  `body` text NOT NULL,
  `starter` varchar(30) NOT NULL default '',
  `gracz` int(11) NOT NULL default '0',
  `cat_id` int(11) NOT NULL default '0',
  `lang` varchar(3) NOT NULL default 'pl',
  `w_time` bigint(20) NOT NULL default '0',
  `sticky` char(1) NOT NULL default 'N',
  UNIQUE KEY `id` (`id`)
) TYPE=MyISAM PACK_KEYS=0 AUTO_INCREMENT=1 ;

-- 
-- Zrzut danych tabeli `topics`
-- 


-- --------------------------------------------------------

-- 
-- Struktura tabeli dla  `tribe_mag`
-- 

CREATE TABLE `tribe_mag` (
  `id` int(11) NOT NULL auto_increment,
  `owner` int(11) NOT NULL default '0',
  `name` varchar(80) NOT NULL default '',
  `efect` varchar(30) NOT NULL default '',
  `power` int(11) NOT NULL default '0',
  `amount` int(11) NOT NULL default '0',
  `type` char(1) NOT NULL default '',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `id` (`id`),
  KEY `id_2` (`id`),
  KEY `klan` (`owner`)
) TYPE=MyISAM PACK_KEYS=0 AUTO_INCREMENT=1 ;

-- 
-- Zrzut danych tabeli `tribe_mag`
-- 


-- --------------------------------------------------------

-- 
-- Struktura tabeli dla  `tribe_oczek`
-- 

CREATE TABLE `tribe_oczek` (
  `id` int(11) NOT NULL auto_increment,
  `gracz` int(11) NOT NULL default '0',
  `klan` int(11) NOT NULL default '0',
  PRIMARY KEY  (`gracz`),
  KEY `id` (`id`)
) TYPE=MyISAM PACK_KEYS=0 AUTO_INCREMENT=1 ;

-- 
-- Zrzut danych tabeli `tribe_oczek`
-- 


-- --------------------------------------------------------

-- 
-- Struktura tabeli dla  `tribe_perm`
-- 

CREATE TABLE `tribe_perm` (
  `id` int(11) NOT NULL auto_increment,
  `tribe` int(11) NOT NULL default '0',
  `player` int(11) NOT NULL default '0',
  `messages` smallint(2) NOT NULL default '0',
  `wait` smallint(2) NOT NULL default '0',
  `kick` smallint(2) NOT NULL default '0',
  `army` smallint(2) NOT NULL default '0',
  `attack` smallint(2) NOT NULL default '0',
  `loan` smallint(2) NOT NULL default '0',
  `armory` smallint(2) NOT NULL default '0',
  `warehouse` smallint(2) NOT NULL default '0',
  `bank` smallint(2) NOT NULL default '0',
  `herbs` smallint(2) NOT NULL default '0',
  `forum` smallint(2) NOT NULL default '0',
  `mail` smallint(2) NOT NULL default '0',
  `ranks` smallint(2) NOT NULL default '0',
  `info` smallint(2) NOT NULL default '0',
  `astralvault` smallint(2) NOT NULL default '0',
  PRIMARY KEY  (`id`)
) TYPE=MyISAM AUTO_INCREMENT=1 ;

-- 
-- Zrzut danych tabeli `tribe_perm`
-- 


-- --------------------------------------------------------

-- 
-- Struktura tabeli dla  `tribe_rank`
-- 

CREATE TABLE `tribe_rank` (
  `id` int(11) NOT NULL auto_increment,
  `tribe_id` int(11) NOT NULL default '0',
  `rank1` varchar(60) NOT NULL default '',
  `rank2` varchar(60) NOT NULL default '',
  `rank3` varchar(60) NOT NULL default '',
  `rank4` varchar(60) NOT NULL default '',
  `rank5` varchar(60) NOT NULL default '',
  `rank6` varchar(60) NOT NULL default '',
  `rank7` varchar(60) NOT NULL default '',
  `rank8` varchar(60) NOT NULL default '',
  `rank9` varchar(60) NOT NULL default '',
  `rank10` varchar(60) NOT NULL default '',
  UNIQUE KEY `tribe_id` (`tribe_id`),
  KEY `id` (`id`)
) TYPE=MyISAM AUTO_INCREMENT=1 ;

-- 
-- Zrzut danych tabeli `tribe_rank`
-- 


-- --------------------------------------------------------

-- 
-- Struktura tabeli dla  `tribe_replies`
-- 

CREATE TABLE `tribe_replies` (
  `id` int(11) NOT NULL auto_increment,
  `starter` varchar(30) NOT NULL default '',
  `topic_id` int(11) NOT NULL default '0',
  `body` text NOT NULL,
  `w_time` bigint(20) NOT NULL default '0',
  `pid` int(11) NOT NULL default '0',
  UNIQUE KEY `id` (`id`),
  KEY `topic_id` (`topic_id`)
) TYPE=MyISAM  PACK_KEYS=0 AUTO_INCREMENT=2 ;

-- 
-- Zrzut danych tabeli `tribe_replies`
-- 


-- --------------------------------------------------------

-- 
-- Struktura tabeli dla  `tribe_topics`
-- 

CREATE TABLE `tribe_topics` (
  `id` int(11) NOT NULL auto_increment,
  `topic` text NOT NULL,
  `body` text NOT NULL,
  `starter` varchar(30) NOT NULL default '',
  `tribe` int(11) NOT NULL default '0',
  `w_time` bigint(20) NOT NULL default '0',
  `sticky` char(1) NOT NULL default 'N',
  `pid` int(11) NOT NULL default '0',
  UNIQUE KEY `id` (`id`)
) TYPE=MyISAM PACK_KEYS=0 AUTO_INCREMENT=1 ;

-- 
-- Zrzut danych tabeli `tribe_topics`
-- 


-- --------------------------------------------------------

-- 
-- Struktura tabeli dla  `tribe_zbroj`
-- 

CREATE TABLE `tribe_zbroj` (
  `id` int(11) NOT NULL auto_increment,
  `klan` int(11) NOT NULL default '0',
  `name` varchar(60) NOT NULL default '',
  `power` int(11) NOT NULL default '0',
  `wt` int(11) NOT NULL default '0',
  `maxwt` int(11) NOT NULL default '0',
  `zr` int(11) NOT NULL default '0',
  `szyb` int(11) NOT NULL default '0',
  `minlev` int(11) NOT NULL default '0',
  `type` char(1) NOT NULL default '',
  `magic` char(1) NOT NULL default 'N',
  `poison` int(11) NOT NULL default '0',
  `amount` int(11) NOT NULL default '1',
  `twohand` char(1) NOT NULL default 'N',
  `ptype` char(1) NOT NULL default '',
  `repair` int(11) NOT NULL default '10',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `id` (`id`),
  KEY `id_2` (`id`),
  KEY `klan` (`klan`)
) TYPE=MyISAM PACK_KEYS=0 AUTO_INCREMENT=1 ;

-- 
-- Zrzut danych tabeli `tribe_zbroj`
-- 


-- --------------------------------------------------------

-- 
-- Struktura tabeli dla  `tribes`
-- 

CREATE TABLE `tribes` (
  `id` int(11) NOT NULL auto_increment,
  `name` varchar(255) NOT NULL default '',
  `owner` int(11) NOT NULL default '0',
  `credits` int(11) NOT NULL default '0',
  `platinum` int(11) NOT NULL default '0',
  `public_msg` text NOT NULL,
  `private_msg` text NOT NULL,
  `hospass` char(1) NOT NULL default 'N',
  `atak` char(1) NOT NULL default 'N',
  `wygr` int(11) NOT NULL default '0',
  `przeg` int(11) NOT NULL default '0',
  `zolnierze` int(11) NOT NULL default '0',
  `forty` int(11) NOT NULL default '0',
  `copper` int(11) NOT NULL default '0',
  `illani` int(11) NOT NULL default '0',
  `illanias` int(11) NOT NULL default '0',
  `nutari` int(11) NOT NULL default '0',
  `logo` varchar(36) NOT NULL default '',
  `www` varchar(60) NOT NULL default '',
  `dynallca` int(11) NOT NULL default '0',
  `ilani_seeds` int(11) NOT NULL default '0',
  `illanias_seeds` int(11) NOT NULL default '0',
  `nutari_seeds` int(11) NOT NULL default '0',
  `dynallca_seeds` int(11) NOT NULL default '0',
  `copperore` int(11) NOT NULL default '0',
  `zincore` int(11) NOT NULL default '0',
  `tinore` int(11) NOT NULL default '0',
  `ironore` int(11) NOT NULL default '0',
  `coal` int(11) NOT NULL default '0',
  `bronze` int(11) NOT NULL default '0',
  `brass` int(11) NOT NULL default '0',
  `iron` int(11) NOT NULL default '0',
  `steel` int(11) NOT NULL default '0',
  `pine` int(11) NOT NULL default '0',
  `hazel` int(11) NOT NULL default '0',
  `yew` int(11) NOT NULL default '0',
  `elm` int(11) NOT NULL default '0',
  `crystal` int(11) NOT NULL default '0',
  `adamantium` int(11) NOT NULL default '0',
  `meteor` int(11) NOT NULL default '0',
  UNIQUE KEY `id` (`id`)
) TYPE=MyISAM AUTO_INCREMENT=1 ;

-- 
-- Zrzut danych tabeli `tribes`
-- 


-- --------------------------------------------------------

-- 
-- Struktura tabeli dla  `upd_comments`
-- 

CREATE TABLE `upd_comments` (
  `id` int(11) NOT NULL auto_increment,
  `updateid` int(11) NOT NULL default '0',
  `author` varchar(40) NOT NULL default '',
  `body` text NOT NULL,
  `time` date default NULL,
  KEY `id` (`id`)
) TYPE=MyISAM AUTO_INCREMENT=1 ;

-- 
-- Zrzut danych tabeli `upd_comments`
-- 


-- --------------------------------------------------------

-- 
-- Struktura tabeli dla  `updates`
-- 

CREATE TABLE `updates` (
  `id` int(11) NOT NULL auto_increment,
  `starter` text NOT NULL,
  `title` text NOT NULL,
  `updates` text NOT NULL,
  `time` date default NULL,
  `lang` varchar(3) NOT NULL default 'pl',
  UNIQUE KEY `id` (`id`)
) TYPE=MyISAM PACK_KEYS=0 AUTO_INCREMENT=1 ;

-- 
-- Zrzut danych tabeli `updates`
-- 


-- --------------------------------------------------------

-- 
-- Struktura tabeli dla  `warehouse`
-- 

CREATE TABLE `warehouse` (
  `reset` smallint(3) NOT NULL default '0',
  `mineral` varchar(30) NOT NULL default '',
  `sell` bigint(22) NOT NULL default '0',
  `buy` bigint(22) NOT NULL default '0',
  `cost` double(20,3) NOT NULL default '0.000',
  `amount` bigint(22) NOT NULL default '0',
  KEY `reset` (`reset`),
  KEY `mineral` (`mineral`)
) TYPE=MyISAM;

-- 
-- Zrzut danych tabeli `warehouse`
-- 

