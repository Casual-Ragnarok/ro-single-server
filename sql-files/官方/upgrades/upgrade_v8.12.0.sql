/*
---------------------------------------------
-         以下适用于全部v8.11.0用户         -
---------------------------------------------
*/

ALTER TABLE  `char` ADD COLUMN `sex` ENUM('M','F','U') NOT NULL default 'U';

--
-- Table structure for `db_roulette`
--

CREATE TABLE `db_roulette` (
  `index` int(11) NOT NULL default '0',
  `level` smallint(5) unsigned NOT NULL,
  `item_id` smallint(5) unsigned NOT NULL,
  `amount` smallint(5) unsigned NOT NULL DEFAULT '1',
  `flag` smallint(5) unsigned NOT NULL DEFAULT '1',
  PRIMARY KEY (`index`)
) ENGINE=MyISAM;

-- ----------------------------
-- Records of db_roulette
-- ----------------------------
-- Info: http://ro.gnjoy.com/news/update/View.asp?seq=157&curpage=1

INSERT INTO `db_roulette`(`index`, `level`, `item_id`, `amount`, `flag` ) VALUES ( 0, 1, 675, 1, 1 ); -- Silver_Coin
INSERT INTO `db_roulette`(`index`, `level`, `item_id`, `amount`, `flag` ) VALUES ( 1, 1, 671, 1, 0 ); -- Gold_Coin
INSERT INTO `db_roulette`(`index`, `level`, `item_id`, `amount`, `flag` ) VALUES ( 2, 1, 678, 1, 0 ); -- Poison_Bottle
INSERT INTO `db_roulette`(`index`, `level`, `item_id`, `amount`, `flag` ) VALUES ( 3, 1, 604, 1, 0 ); -- Branch_Of_Dead_Tree
INSERT INTO `db_roulette`(`index`, `level`, `item_id`, `amount`, `flag` ) VALUES ( 4, 1, 522, 1, 0 ); -- Fruit_Of_Mastela
INSERT INTO `db_roulette`(`index`, `level`, `item_id`, `amount`, `flag` ) VALUES ( 5, 1, 671, 1, 0 ); -- Old_Ore_Box
INSERT INTO `db_roulette`(`index`, `level`, `item_id`, `amount`, `flag` ) VALUES ( 6, 1, 12523, 1, 0 ); -- E_Inc_Agi_10_Scroll
INSERT INTO `db_roulette`(`index`, `level`, `item_id`, `amount`, `flag` ) VALUES ( 7, 1, 985, 1, 0 ); -- Elunium
INSERT INTO `db_roulette`(`index`, `level`, `item_id`, `amount`, `flag` ) VALUES ( 8, 1, 984, 1, 0 ); -- Oridecon

INSERT INTO `db_roulette`(`index`, `level`, `item_id`, `amount`, `flag` ) VALUES ( 9, 2, 675, 1, 1 ); -- Silver_Coin
INSERT INTO `db_roulette`(`index`, `level`, `item_id`, `amount`, `flag` ) VALUES ( 10, 2, 671, 1, 0 ); -- Gold_Coin
INSERT INTO `db_roulette`(`index`, `level`, `item_id`, `amount`, `flag` ) VALUES ( 11, 2, 603, 1, 0 ); -- Old_Blue_Box
INSERT INTO `db_roulette`(`index`, `level`, `item_id`, `amount`, `flag` ) VALUES ( 12, 2, 608, 1, 0 ); -- Seed_Of_Yggdrasil
INSERT INTO `db_roulette`(`index`, `level`, `item_id`, `amount`, `flag` ) VALUES ( 13, 2, 607, 1, 0 ); -- Yggdrasilberry
INSERT INTO `db_roulette`(`index`, `level`, `item_id`, `amount`, `flag` ) VALUES ( 14, 2, 12522, 1, 0 ); -- E_Blessing_10_Scroll
INSERT INTO `db_roulette`(`index`, `level`, `item_id`, `amount`, `flag` ) VALUES ( 15, 2, 6223, 1, 0 ); -- Carnium
INSERT INTO `db_roulette`(`index`, `level`, `item_id`, `amount`, `flag` ) VALUES ( 16, 2, 6224, 1, 0 ); -- Bradium

INSERT INTO `db_roulette`(`index`, `level`, `item_id`, `amount`, `flag` ) VALUES ( 17, 3, 675, 1, 1 ); -- Silver_Coin
INSERT INTO `db_roulette`(`index`, `level`, `item_id`, `amount`, `flag` ) VALUES ( 18, 3, 671, 1, 0 ); -- Gold_Coin
INSERT INTO `db_roulette`(`index`, `level`, `item_id`, `amount`, `flag` ) VALUES ( 19, 3, 12108, 1, 0 ); -- Bundle_Of_Magic_Scroll
INSERT INTO `db_roulette`(`index`, `level`, `item_id`, `amount`, `flag` ) VALUES ( 20, 3, 617, 1, 0 ); -- Old_Violet_Box
INSERT INTO `db_roulette`(`index`, `level`, `item_id`, `amount`, `flag` ) VALUES ( 21, 3, 12514, 1, 0 ); -- E_Abrasive
INSERT INTO `db_roulette`(`index`, `level`, `item_id`, `amount`, `flag` ) VALUES ( 22, 3, 7444, 1, 0 ); -- Treasure_Box
INSERT INTO `db_roulette`(`index`, `level`, `item_id`, `amount`, `flag` ) VALUES ( 23, 3, 969, 1, 0 ); -- Gold

INSERT INTO `db_roulette`(`index`, `level`, `item_id`, `amount`, `flag` ) VALUES ( 24, 4, 675, 1, 1 ); -- Silver_Coin
INSERT INTO `db_roulette`(`index`, `level`, `item_id`, `amount`, `flag` ) VALUES ( 25, 4, 671, 1, 0 ); -- Gold_Coin
INSERT INTO `db_roulette`(`index`, `level`, `item_id`, `amount`, `flag` ) VALUES ( 26, 4, 616, 1, 0 ); -- Old_Card_Album
INSERT INTO `db_roulette`(`index`, `level`, `item_id`, `amount`, `flag` ) VALUES ( 27, 4, 12516, 1, 0 ); -- E_Small_Life_Potion
INSERT INTO `db_roulette`(`index`, `level`, `item_id`, `amount`, `flag` ) VALUES ( 28, 4, 22777, 1, 0 ); -- Gift_Buff_Set
INSERT INTO `db_roulette`(`index`, `level`, `item_id`, `amount`, `flag` ) VALUES ( 29, 4, 6231, 1, 0 ); -- Guarantee_Weapon_6Up

INSERT INTO `db_roulette`(`index`, `level`, `item_id`, `amount`, `flag` ) VALUES ( 30, 5, 671, 1, 1 ); -- Gold_Coin
INSERT INTO `db_roulette`(`index`, `level`, `item_id`, `amount`, `flag` ) VALUES ( 31, 5, 12246, 1, 0 ); -- Magic_Card_Album
INSERT INTO `db_roulette`(`index`, `level`, `item_id`, `amount`, `flag` ) VALUES ( 32, 5, 12263, 1, 0 ); -- Comp_Battle_Manual
INSERT INTO `db_roulette`(`index`, `level`, `item_id`, `amount`, `flag` ) VALUES ( 33, 5, 671, 1, 0 ); -- Potion_Box
INSERT INTO `db_roulette`(`index`, `level`, `item_id`, `amount`, `flag` ) VALUES ( 34, 5, 6235, 1, 0 ); -- Guarantee_Armor_6Up

INSERT INTO `db_roulette`(`index`, `level`, `item_id`, `amount`, `flag` ) VALUES ( 35, 6, 671, 1, 1 ); -- Gold_Coin
INSERT INTO `db_roulette`(`index`, `level`, `item_id`, `amount`, `flag` ) VALUES ( 36, 6, 12766, 1, 0 ); -- Reward_Job_BM25
INSERT INTO `db_roulette`(`index`, `level`, `item_id`, `amount`, `flag` ) VALUES ( 37, 6, 6234, 1, 0 ); -- Guarantee_Armor_7Up
INSERT INTO `db_roulette`(`index`, `level`, `item_id`, `amount`, `flag` ) VALUES ( 38, 6, 6233, 1, 0 ); -- Guarantee_Armor_8Up

INSERT INTO `db_roulette`(`index`, `level`, `item_id`, `amount`, `flag` ) VALUES ( 39, 7, 671, 1, 1 ); -- Gold_Coin
INSERT INTO `db_roulette`(`index`, `level`, `item_id`, `amount`, `flag` ) VALUES ( 40, 7, 6233, 1, 0 ); -- Guarantee_Armor_8Up
INSERT INTO `db_roulette`(`index`, `level`, `item_id`, `amount`, `flag` ) VALUES ( 41, 7, 6233, 1, 0 ); -- Guarantee_Armor_8Up	// KRO lists this twice

ALTER TABLE `picklog` MODIFY `type` enum('M','P','L','T','V','S','N','C','A','R','G','E','B','O','I','X','D','U','$','F','Y') NOT NULL default 'P';

ALTER TABLE `picklog` CHANGE `type` `type` ENUM('M','P','L','T','V','S','N','C','A','R','G','E','B','O','I','X','D','U','$','F','Z') NOT NULL DEFAULT 'P';

ALTER TABLE `char` ADD COLUMN `hotkey_rowshift` TINYINT(3) UNSIGNED NOT NULL DEFAULT  '0';

ALTER TABLE `mapreg` ADD PRIMARY KEY (`varname`, `index`);
ALTER TABLE `mapreg` DROP INDEX `varname`;
ALTER TABLE `mapreg` DROP INDEX `index`;
ALTER TABLE `mapreg` MODIFY `varname` varchar(32) binary NOT NULL;

CREATE TABLE IF NOT EXISTS `acc_reg_num` (
  `account_id` int(11) unsigned NOT NULL default '0',
  `key` varchar(32) binary NOT NULL default '',
  `index` int(11) unsigned NOT NULL default '0',
  `value` int(11) NOT NULL default '0',
  PRIMARY KEY (`account_id`,`key`,`index`),
  KEY `account_id` (`account_id`)
) ENGINE=MyISAM;

CREATE TABLE IF NOT EXISTS `acc_reg_str` (
  `account_id` int(11) unsigned NOT NULL default '0',
  `key` varchar(32) binary NOT NULL default '',
  `index` int(11) unsigned NOT NULL default '0',
  `value` varchar(254) NOT NULL default '0',
  PRIMARY KEY (`account_id`,`key`,`index`),
  KEY `account_id` (`account_id`)
) ENGINE=MyISAM;

CREATE TABLE IF NOT EXISTS `char_reg_num` (
  `char_id` int(11) unsigned NOT NULL default '0',
  `key` varchar(32) binary NOT NULL default '',
  `index` int(11) unsigned NOT NULL default '0',
  `value` int(11) NOT NULL default '0',
  PRIMARY KEY (`char_id`,`key`,`index`),
  KEY `char_id` (`char_id`)
) ENGINE=MyISAM;

CREATE TABLE IF NOT EXISTS `char_reg_str` (
  `char_id` int(11) unsigned NOT NULL default '0',
  `key` varchar(32) binary NOT NULL default '',
  `index` int(11) unsigned NOT NULL default '0',
  `value` varchar(254) NOT NULL default '0',
  PRIMARY KEY (`char_id`,`key`,`index`),
  KEY `char_id` (`char_id`)
) ENGINE=MyISAM;

CREATE TABLE IF NOT EXISTS `global_acc_reg_num` (
  `account_id` int(11) unsigned NOT NULL default '0',
  `key` varchar(32) binary NOT NULL default '',
  `index` int(11) unsigned NOT NULL default '0',
  `value` int(11) NOT NULL default '0',
  PRIMARY KEY (`account_id`,`key`,`index`),
  KEY `account_id` (`account_id`)
) ENGINE=MyISAM;

CREATE TABLE IF NOT EXISTS `global_acc_reg_str` (
  `account_id` int(11) unsigned NOT NULL default '0',
  `key` varchar(32) binary NOT NULL default '',
  `index` int(11) unsigned NOT NULL default '0',
  `value` varchar(254) NOT NULL default '0',
  PRIMARY KEY (`account_id`,`key`,`index`),
  KEY `account_id` (`account_id`)
) ENGINE=MyISAM;

INSERT INTO `acc_reg_num` (`account_id`, `key`, `index`, `value`) SELECT `account_id`, `str`, 0, `value` FROM `global_reg_value` WHERE `type` = 2 AND `str` NOT LIKE '%$';
INSERT INTO `acc_reg_str` (`account_id`, `key`, `index`, `value`) SELECT `account_id`, `str`, 0, `value` FROM `global_reg_value` WHERE `type` = 2 AND `str` LIKE '%$';
INSERT INTO `char_reg_num` (`char_id`, `key`, `index`, `value`) SELECT `char_id`, `str`, 0, `value` FROM `global_reg_value` WHERE `type` = 3 AND `str` NOT LIKE '%$';
INSERT INTO `char_reg_str` (`char_id`, `key`, `index`, `value`) SELECT `char_id`, `str`, 0, `value` FROM `global_reg_value` WHERE `type` = 3 AND `str` LIKE '%$';
INSERT INTO `global_acc_reg_num` (`account_id`, `key`, `index`, `value`) SELECT `account_id`, `str`, 0, `value` FROM `global_reg_value` WHERE `type` = 1 AND `str` NOT LIKE '%$';
INSERT INTO `global_acc_reg_str` (`account_id`, `key`, `index`, `value`) SELECT `account_id`, `str`, 0, `value` FROM `global_reg_value` WHERE `type` = 1 AND `str` LIKE '%$';
# DROP TABLE `global_reg_value`;

ALTER TABLE `picklog` MODIFY `type` enum('M','P','L','T','V','S','N','C','A','R','G','E','B','O','I','X','D','U','$','F','Z','Q') NOT NULL default 'P';
