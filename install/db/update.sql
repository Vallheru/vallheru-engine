ALTER TABLE `players` CHANGE `energy` `energy` DOUBLE( 11, 2 ) NOT NULL DEFAULT '10.00',
CHANGE `max_energy` `max_energy` DOUBLE( 11, 2 ) NOT NULL DEFAULT '100.00',
CHANGE `hp` `hp` INT( 11 ) UNSIGNED NOT NULL DEFAULT '10',
CHANGE `max_hp` `max_hp` INT( 11 ) NOT NULL DEFAULT '10',
CHANGE `antidote` `antidote` CHAR( 4 ) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL,
CHANGE `pm` `pm` INT( 11 ) NOT NULL DEFAULT '0';
ALTER TABLE `players`
  DROP `level`,
  DROP `exp`,
  DROP `strength`,
  DROP `agility`,
  DROP `inteli`,
  DROP `szyb`,
  DROP `wytrz`,
  DROP `wisdom`,
  DROP `ability`,
  DROP `atak`,
  DROP `unik`,
  DROP `magia`,
  DROP `alchemia`,
  DROP `shoot`,
  DROP `fletcher`,
  DROP `leadership`,
  DROP `breeding`,
  DROP `mining`,
  DROP `lumberjack`,
  DROP `herbalist`,
  DROP `jeweller`,
  DROP `thievery`,
  DROP `perception`,
  DROP `metallurgy`;
ALTER TABLE `players` ADD `stats` VARCHAR( 2048 ) NOT NULL DEFAULT 'strength:Siła,0,0,0;agility:Zręczność,0,0,0;condition:Kondycja,0,0,0;speed:Szybkość,0,0,0;inteli:Inteligencja,0,0,0;wisdom:Siła Woli,0,0,0;';
ALTER TABLE `players` ADD `skills` VARCHAR( 4096 ) NOT NULL DEFAULT 'smith:Kowalstwo,1,0;shoot:Strzelectwo,1,0;alchemy:Alchemia,1,0;dodge:Uniki,1,0;carpentry:Stolarstwo,1,0;magic:Rzucanie Czarów,1,0;attack:Walka Bronią,1,0;leadership:Dowodzenie,1,0;breeding:Hodowla,1,0;mining:Górnictwo,1,0;lumberjack:Drwalnictwo,1,0;herbalism:Zielarstwo,1,0;jewellry:Jubilerstwo,1,0;smelting:Hutnictwo,1,0;thievery:Złodziejstwo,1,0;perception:Spostrzegawczość,1,0;';
ALTER TABLE `monsters`
  DROP `exp1`,
  DROP `exp2`,
  DROP `lang`;
