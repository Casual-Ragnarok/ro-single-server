/*
---------------------------------------------
-    以下适用于全部v8.14.0用户 [Main]       -
---------------------------------------------
*/

-- ----------------------------
-- upgrade_20161013.sql
-- ----------------------------

ALTER TABLE `market`
	MODIFY `name` varchar(50) NOT NULL DEFAULT '';
	
-- ----------------------------
-- upgrade_20161116.sql
-- ----------------------------
-- Table structure for table `clan`
-- ----------------------------
CREATE TABLE `clan` (
  `clan_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(24) NOT NULL DEFAULT '',
  `master` varchar(24) NOT NULL DEFAULT '',
  `mapname` varchar(24) NOT NULL DEFAULT '',
  `max_member` smallint(6) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`clan_id`)
) ENGINE=MyISAM AUTO_INCREMENT=5;

-- ----------------------------
-- Records of clan
-- ----------------------------
INSERT INTO `clan` VALUES ('1', '剑士氏族', '瑞夫曼欧莱比亚', 'prontera', '500');
INSERT INTO `clan` VALUES ('2', '魔法师氏族', '德丰艾尔', 'geffen', '500');
INSERT INTO `clan` VALUES ('3', '服事氏族', '伯曼艾尔', 'prontera', '500');
INSERT INTO `clan` VALUES ('4', '弓箭手氏族', '沙姆鲁米', 'payon', '500');

ALTER TABLE `char`
	ADD COLUMN `clan_id` INT(11) UNSIGNED NOT NULL DEFAULT '0' AFTER `hotkey_rowshift`;
	
-- ----------------------------
-- Table structure for `clan_alliance`
-- ----------------------------
CREATE TABLE `clan_alliance` (
  `clan_id` int(11) unsigned NOT NULL DEFAULT '0',
  `opposition` int(11) unsigned NOT NULL DEFAULT '0',
  `alliance_id` int(11) unsigned NOT NULL DEFAULT '0',
  `name` varchar(24) NOT NULL DEFAULT '',
  PRIMARY KEY (`clan_id`,`alliance_id`),
  KEY `alliance_id` (`alliance_id`)
) ENGINE=MyISAM;

-- ----------------------------
-- Records of clan_alliance
-- ----------------------------
INSERT INTO `clan_alliance` VALUES ('1', '0', '3', '服事氏族');
INSERT INTO `clan_alliance` VALUES ('2', '0', '3', '服事氏族');
INSERT INTO `clan_alliance` VALUES ('2', '1', '4', '弓箭手氏族');
INSERT INTO `clan_alliance` VALUES ('3', '0', '1', '剑士氏族');
INSERT INTO `clan_alliance` VALUES ('3', '0', '2', '魔法师氏族');
INSERT INTO `clan_alliance` VALUES ('3', '0', '4', '弓箭手氏族');
INSERT INTO `clan_alliance` VALUES ('4', '0', '3', '服事氏族');
INSERT INTO `clan_alliance` VALUES ('4', '1', '2', '魔法师氏族');

-- ----------------------------
-- Records of fame_rank
-- ----------------------------
CREATE TABLE IF NOT EXISTS `fame_rank` (
 `char_id` int(10) unsigned NOT NULL default '1',
 `char_name` varchar(255) default 'Unknown',
 `class` int(10) unsigned default '0',
 `fame` tinyint(3) unsigned default '0',
 PRIMARY KEY (`char_id`)
 ) ENGINE=MyISAM;

-- ----------------------------
-- upgrade_20170215.sql
-- ----------------------------
-- Table structure for `sales`
-- ----------------------------

CREATE TABLE IF NOT EXISTS `sales` (
  `nameid` smallint(5) unsigned NOT NULL,
  `start` datetime NOT NULL,
  `end` datetime NOT NULL,
  `amount` int(11) NOT NULL,
  PRIMARY KEY (`nameid`)
) ENGINE=MyISAM;

-- ----------------------------
-- upgrade_20170218.sql
-- ----------------------------
ALTER TABLE `homunculus` MODIFY COLUMN `exp` bigint(20) unsigned NOT NULL DEFAULT '0';

/*
---------------------------------------------
-    以下适用于全部v8.14.0用户 [Logs]       -
---------------------------------------------
*/

-- ----------------------------
-- upgrade_20170113_log.sql
-- ----------------------------
ALTER TABLE `chatlog` 
	CHANGE COLUMN `type` `type` ENUM('O','W','P','G','M','C') NOT NULL DEFAULT 'O';

-- ----------------------------
-- upgrade_20170218_log.sql
-- ----------------------------
ALTER TABLE `mvplog` MODIFY COLUMN `mvpexp` bigint(20) unsigned NOT NULL DEFAULT '0';

/*
---------------------------------------------
-  以下适用于将DB使用SQL数据库者，非者勿用  -
---------------------------------------------
*/
