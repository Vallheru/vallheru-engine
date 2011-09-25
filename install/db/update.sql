ALTER TABLE `topics` ADD INDEX ( `cat_id` );
ALTER TABLE `replies` CHANGE `topic_id` `topic_id` INT NOT NULL;
ALTER TABLE `replies` ADD INDEX ( `topic_id` );
ALTER TABLE `upd_comments` ADD INDEX ( `updateid` );
ALTER TABLE `monsters` CHANGE `name` `name` VARCHAR( 45 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '';
UPDATE `monsters` SET `name` = 'Krótkowłosy Niedźwieżuk' WHERE `id`=71;
UPDATE `monsters` SET `credits2`='2',`exp2`='2' WHERE `id`=56;
UPDATE `monsters` SET `exp1` = '775',`exp2` = '850' WHERE `id`=84;
UPDATE `monsters` SET `exp1` = '1100', `exp2` = '1200' WHERE `monsters`.`id`=88;
ALTER TABLE `equipment` ADD FULLTEXT ( `name` );
ALTER TABLE `potions` ADD FULLTEXT ( `name` );
ALTER TABLE `polls` ADD `desc` TEXT NOT NULL;
ALTER TABLE `players` ADD `vallars` INT( 11 ) NOT NULL DEFAULT '0';
ALTER TABLE `topics` ADD `closed` CHAR( 1 ) NOT NULL DEFAULT 'N';
CREATE TABLE IF NOT EXISTS `vallars` (
  `owner` int(11) NOT NULL,
  `reason` varchar(255) NOT NULL,
  `amount` int(11) NOT NULL,
  `vdate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY `owner` (`owner`),
  KEY `vdate` (`vdate`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
UPDATE `potions` SET `status` = 'A' WHERE `potions`.`id` =1;
UPDATE `potions` SET `status` = 'A' WHERE `potions`.`id` =2;
UPDATE `potions` SET `status` = 'A' WHERE `potions`.`id` =3;
UPDATE `potions` SET `status` = 'A' WHERE `potions`.`id` =5;
UPDATE `potions` SET `status` = 'A' WHERE `potions`.`id` =7;
UPDATE `potions` SET `status` = 'A' WHERE `potions`.`id` =8;
UPDATE `potions` SET `status` = 'A' WHERE `potions`.`id` =9;
UPDATE `potions` SET `status` = 'A' WHERE `potions`.`id` =10;
ALTER TABLE `players` ADD `newbie` TINYINT( 1 ) NOT NULL DEFAULT '3';
ALTER TABLE `players` ADD `autodrink` CHAR NOT NULL DEFAULT 'N';
ALTER TABLE `equipment` ADD INDEX ( `minlev` );
ALTER TABLE `news` ADD `pdate` DATE NOT NULL;
ALTER TABLE `links` ADD `number` INT( 11 ) NOT NULL;
ALTER TABLE `players` ADD `thievery` DOUBLE( 11, 2 ) NOT NULL DEFAULT '0.01',
ADD `perception` DOUBLE( 11, 2 ) NOT NULL DEFAULT '0.01';
ALTER TABLE `log` ADD `type` CHAR( 1 ) NOT NULL DEFAULT 'U', ADD INDEX ( `type` );
ALTER TABLE `replies` DROP `w_time`;
ALTER TABLE `bridge` DROP `lang` ;
ALTER TABLE `tribe_replies` DROP `w_time`;
ALTER TABLE `players` ADD `forumcats` VARCHAR( 255 ) NOT NULL DEFAULT 'All';
ALTER TABLE `mail` ADD FULLTEXT (
`subject` ,
`body`
);
ALTER TABLE `reset` ADD `type` CHAR( 1 ) NOT NULL DEFAULT 'A';
ALTER TABLE `topics` ADD `replies` INT( 11 ) NOT NULL DEFAULT '0';
ALTER TABLE `players` ADD `roleplay` TEXT NOT NULL, ADD `ooc` TEXT NOT NULL, ADD `shortrpg` VARCHAR( 40 ) NOT NULL;
ALTER TABLE `players` ADD `metallurgy` DOUBLE( 11, 2 ) NOT NULL DEFAULT '0.01';
