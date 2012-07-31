ALTER TABLE  `players` ADD  `settings` VARCHAR( 1024 ) NOT NULL DEFAULT  'style:light.css;graphic:;graphbar:N;forumcats:All;autodrink:N;rinvites:Y;battlelog:N;oldchat:N;';
ALTER TABLE `players`
  DROP `style`,
  DROP `graphic`,
  DROP `battlelog`,
  DROP `graphbar`,
  DROP `autodrink`,
  DROP `forumcats`,
  DROP `rinvites`;
ALTER TABLE `players`
  DROP `lang`,
  DROP `seclang`;
ALTER TABLE `czary` DROP `lang`;
ALTER TABLE  `czary` ADD  `element` VARCHAR( 20 ) NOT NULL;
ALTER TABLE  `monsters` ADD  `resistance` VARCHAR( 100 ) NOT NULL DEFAULT  'none;none',
ADD  `dmgtype` VARCHAR( 20 ) NOT NULL DEFAULT  'none';
UPDATE `monsters` SET `resistance`='earth;weak' WHERE `id` IN (10,11,16,25,97);
UPDATE `monsters` SET `resistance`='wind;weak' WHERE `id` IN (12,65,79);
UPDATE `monsters` SET `resistance`='fire;strong' WHERE `id` IN (13,22,29,37,41,51,93);
UPDATE `monsters` SET `resistance`='water;medium' WHERE `id` IN (14,35,82);
UPDATE `monsters` SET `resistance`='wind;strong' WHERE `id` IN (15,19,55,80,91);
UPDATE `monsters` SET `resistance`='earth;strong' WHERE `id` IN (18,21,26,45,48,49,68,73,94);
UPDATE `monsters` SET `resistance`='water;strong' WHERE `id` IN (20,43,53,104);
UPDATE `monsters` SET `resistance`='earth;medium' WHERE `id` IN (23,52,90);
UPDATE `monsters` SET `resistance`='fire;weak' WHERE `id` IN (27,85,88);
UPDATE `monsters` SET `resistance`='fire;medium' WHERE `id` IN (28,30,100);
UPDATE `monsters` SET `resistance`='wind;medium' WHERE `id` IN (32,87);
UPDATE `monsters` SET `dmgtype`='earth' WHERE `id` IN (10,11,16,25,97,18,21,26,45,48,49,68,73,94,23,52,90);
UPDATE `monsters` SET `dmgtype`='wind' WHERE `id` IN (12,65,79,15,19,55,80,91,32,87);
UPDATE `monsters` SET `dmgtype`='fire' WHERE `id` IN (13,22,29,37,41,51,93,27,85,88,28,30,100);
UPDATE `monsters` SET `dmgtype`='water' WHERE `id` IN (14,35,82,20,43,53,104);
CREATE TABLE IF NOT EXISTS `battlelogs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `pid` int(11) NOT NULL,
  `did` int(11) NOT NULL,
  `wid` int(11) NOT NULL,
  `bdate` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;
ALTER TABLE  `notatnik` ADD  `title` VARCHAR( 255 ) NOT NULL DEFAULT  'Bez tytułu';
INSERT INTO `settings` (`setting`, `value`) VALUES ('gold', '0');
ALTER TABLE  `players` ADD  `craftskill` VARCHAR( 30 ) NOT NULL;
