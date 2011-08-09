ALTER TABLE `topics` ADD INDEX ( `cat_id` );
ALTER TABLE `replies` CHANGE `topic_id` `topic_id` INT NOT NULL;
ALTER TABLE `replies` ADD INDEX ( `topic_id` );
ALTER TABLE `upd_comments` ADD INDEX ( `updateid` );
