ALTER TABLE  `players` ADD  `settings` VARCHAR( 1024 ) NOT NULL DEFAULT  'style:light.css;graphic:;graphbar:N;forumcats:All;autodrink:N;rinvites:Y;battlelog:N;'
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
