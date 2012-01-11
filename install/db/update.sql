ALTER TABLE `players` ADD `craftmission` CHAR( 1 ) NOT NULL DEFAULT 'N';
ALTER TABLE `players` ADD `mpoints` INT( 11 ) NOT NULL DEFAULT '0';
CREATE TABLE IF NOT EXISTS `brecords` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `pid` int(11) NOT NULL,
  `mdate` smallint(3) NOT NULL,
  `mlevel` int(11) NOT NULL,
  `mname` varchar(65) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `mlevel` (`mlevel`),
  KEY `pid` (`pid`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;
CREATE TABLE IF NOT EXISTS `ban_forum` (
  `pid` int(11) NOT NULL,
  `resets` int(11) NOT NULL,
  KEY `pid` (`pid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
ALTER TABLE `chat` CHANGE `user` `user` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '';
CREATE TABLE IF NOT EXISTS `chatrooms` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user` varchar(255) NOT NULL DEFAULT '',
  `chat` text NOT NULL,
  `senderid` int(11) NOT NULL DEFAULT '0',
  `ownerid` int(11) NOT NULL DEFAULT '0',
  `sdate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `room` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 PACK_KEYS=0 AUTO_INCREMENT=1 ;
ALTER TABLE `players` ADD `room` INT( 11 ) NOT NULL DEFAULT '0';
CREATE TABLE IF NOT EXISTS `rooms` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `owner` int(11) NOT NULL,
  `npcs` text NOT NULL,
  `desc` text NOT NULL,
  `name` varchar(255) NOT NULL DEFAULT 'Pokój',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;
CREATE TABLE IF NOT EXISTS `missions` (
  `name` varchar(255) NOT NULL,
  `text` text NOT NULL,
  `exits` varchar(255) NOT NULL,
  `chances` varchar(255) NOT NULL,
  `mobs` varchar(255) NOT NULL,
  `chances2` varchar(255) NOT NULL,
  `items` varchar(255) NOT NULL,
  `chances3` varchar(255) NOT NULL,
  KEY `name` (`name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
ALTER TABLE `rooms` ADD `days` SMALLINT( 3 ) NOT NULL DEFAULT '1';
CREATE TABLE IF NOT EXISTS `mactions` (
  `pid` int(11) NOT NULL,
  `location` varchar(255) NOT NULL,
  `exits` varchar(255) NOT NULL,
  `mobs` varchar(255) NOT NULL,
  `items` varchar(255) NOT NULL,
  `type` char(1) NOT NULL,
  `loot` varchar(255) NOT NULL,
  PRIMARY KEY (`pid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
ALTER TABLE `rooms` ADD `owners` VARCHAR( 255 ) NOT NULL ;
ALTER TABLE `mactions` ADD `rooms` SMALLINT( 3 ) NOT NULL ;
ALTER TABLE `mactions` CHANGE `exits` `exits` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL ,
CHANGE `mobs` `mobs` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL ,
CHANGE `items` `items` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL ;
ALTER TABLE `missions` CHANGE `exits` `exits` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL ,
CHANGE `mobs` `mobs` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL ,
CHANGE `items` `items` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL;
