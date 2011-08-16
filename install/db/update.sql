ALTER TABLE `topics` ADD INDEX ( `cat_id` );
ALTER TABLE `replies` CHANGE `topic_id` `topic_id` INT NOT NULL;
ALTER TABLE `replies` ADD INDEX ( `topic_id` );
ALTER TABLE `upd_comments` ADD INDEX ( `updateid` );
ALTER TABLE `monsters` CHANGE `name` `name` VARCHAR( 45 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '';
UPDATE `monsters` SET `name` = 'Krótkowłosy Niedźwieżuk' WHERE `id`=71;
UPDATE `monsters` SET `credits2`='2',`exp2`='2' WHERE `id`=56;
UPDATE `monsters` SET `exp1` = '775',`exp2` = '850' WHERE `id`=84;
UPDATE `monsters` SET `exp1` = '1100', `exp2` = '1200' WHERE `monsters`.`id`=88;
