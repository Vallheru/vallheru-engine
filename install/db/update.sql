ALTER TABLE  `players` ADD  `settings` VARCHAR( 1024 ) NOT NULL DEFAULT  'style:light.css;graphic:;graphbar:N;forumcats:All;autodrink:N;rinvites:Y;battlelog:N;'
ALTER TABLE `players`
  DROP `style`,
  DROP `graphic`,
  DROP `battlelog`,
  DROP `graphbar`,
  DROP `autodrink`,
  DROP `forumcats`,
  DROP `rinvites`;
