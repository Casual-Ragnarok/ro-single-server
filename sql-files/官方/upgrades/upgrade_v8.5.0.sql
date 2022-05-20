ALTER TABLE `skill` ADD COLUMN `flag` TINYINT(1) UNSIGNED NOT NULL DEFAULT 0;
ALTER TABLE `login` ADD `character_slots` TINYINT(3) UNSIGNED NOT NULL DEFAULT '0';
-- This script resets all quests that were done by your users before this revision
DELETE FROM `quest` WHERE `quest_id` > 5034 AND `quest_id` < 5055;
DELETE FROM `quest` WHERE `quest_id` > 9154 AND `quest_id` < 9166;
DELETE FROM `global_reg_value` WHERE `str` = 'dewata_gatti';
DELETE FROM `global_reg_value` WHERE `str` = 'dewata_legend';
DELETE FROM `global_reg_value` WHERE `str` = 'dewata_oldman';
ALTER TABLE `login` ADD COLUMN `pincode` varchar(4) NOT NULL DEFAULT '';
ALTER TABLE `login` ADD COLUMN `pincode_change` int(11) unsigned NOT NULL DEFAULT '0';
ALTER TABLE `char` ADD COLUMN `moves` int(11) unsigned NOT NULL DEFAULT '0';

DROP TABLE IF EXISTS `cashlog`;

CREATE TABLE `cashlog` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `char_id` int(11) NOT NULL DEFAULT '0',
  `type` enum('T','V','P','M','S','N','D','C','A','E','I','B','$') NOT NULL DEFAULT 'S',
  `cash_type` enum('O','K','C') NOT NULL DEFAULT 'O',
  `amount` int(11) NOT NULL DEFAULT '0',
  `map` varchar(11) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  INDEX `type` (`type`)
) ENGINE=MyISAM AUTO_INCREMENT=1;

DROP TABLE IF EXISTS `item_cash_db`;
CREATE TABLE `item_cash_db` (
  `tab` smallint(6) NOT NULL,
  `item_id` smallint(5) unsigned NOT NULL,
  `price` mediumint(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`tab`,`item_id`)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `item_cash_db2`;
CREATE TABLE `item_cash_db2` (
  `tab` smallint(6) NOT NULL,
  `item_id` smallint(5) unsigned NOT NULL,
  `price` mediumint(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`tab`,`item_id`)
) ENGINE=MyISAM;

UPDATE `skill` SET `flag` = 0 WHERE `flag` = 4 AND `id` != 2535 AND `id` != 681;

ALTER TABLE `picklog` CHANGE `type` `type` enum('M','P','L','T','V','S','N','C','A','R','G','E','B','O','I','X','D','U','$') NOT NULL default 'P';
