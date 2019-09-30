/*
---------------------------------------------
-    以下适用于全部v8.18.0用户 [Main]       -
---------------------------------------------
*/

-- ----------------------------
-- upgrade_20180830.sql
-- ----------------------------
UPDATE `char` ch, `skill` sk SET `ch`.`skill_point` = `ch`.`skill_point` + `sk`.`lv` WHERE `sk`.`id` = 2049 AND `ch`.`char_id` = `sk`.`char_id`;

DELETE FROM `skill` WHERE `id` = 2049;

-- ----------------------------
-- upgrade_20181010.sql
-- ----------------------------
--
-- Table structure for table `guild_storage_log`
--

CREATE TABLE IF NOT EXISTS `guild_storage_log` (
  `id` int(11) NOT NULL auto_increment,
  `guild_id` int(11) unsigned NOT NULL default '0',
  `time` datetime NOT NULL,
  `char_id` int(11) NOT NULL default '0',
  `name` varchar(24) NOT NULL default '',
  `nameid` smallint(5) unsigned NOT NULL default '0',
  `amount` int(11) NOT NULL default '1',
  `identify` smallint(6) NOT NULL default '0',
  `refine` tinyint(3) unsigned NOT NULL default '0',
  `attribute` tinyint(4) unsigned NOT NULL default '0',
  `card0` smallint(5) unsigned NOT NULL default '0',
  `card1` smallint(5) unsigned NOT NULL default '0',
  `card2` smallint(5) unsigned NOT NULL default '0',
  `card3` smallint(5) unsigned NOT NULL default '0',
  `option_id0` smallint(5) NOT NULL default '0',
  `option_val0` smallint(5) NOT NULL default '0',
  `option_parm0` tinyint(3) NOT NULL default '0',
  `option_id1` smallint(5) NOT NULL default '0',
  `option_val1` smallint(5) NOT NULL default '0',
  `option_parm1` tinyint(3) NOT NULL default '0',
  `option_id2` smallint(5) NOT NULL default '0',
  `option_val2` smallint(5) NOT NULL default '0',
  `option_parm2` tinyint(3) NOT NULL default '0',
  `option_id3` smallint(5) NOT NULL default '0',
  `option_val3` smallint(5) NOT NULL default '0',
  `option_parm3` tinyint(3) NOT NULL default '0',
  `option_id4` smallint(5) NOT NULL default '0',
  `option_val4` smallint(5) NOT NULL default '0',
  `option_parm4` tinyint(3) NOT NULL default '0',
  `expire_time` int(11) unsigned NOT NULL default '0',
  `unique_id` bigint(20) unsigned NOT NULL default '0',
  `bound` tinyint(1) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`),
  INDEX (`guild_id`)
) ENGINE=MyISAM AUTO_INCREMENT=1;

-- ----------------------------
-- upgrade_20181220.sql
-- ----------------------------
ALTER TABLE `bonus_script` MODIFY COLUMN `tick` bigint(20) NOT NULL default '0';

ALTER TABLE `elemental` MODIFY COLUMN `life_time` bigint(20) NOT NULL default '0';

ALTER TABLE `mercenary` MODIFY COLUMN `life_time` bigint(20) NOT NULL default '0';

ALTER TABLE `sc_data` MODIFY COLUMN `tick` bigint(20) NOT NULL;

ALTER TABLE `skillcooldown` MODIFY COLUMN `tick` bigint(20) NOT NULL;

-- ----------------------------
-- upgrade_20181224.sql
-- ----------------------------
alter table `inventory`
	add column `equip_switch` int(11) unsigned NOT NULL default '0' after `unique_id`;

/*
---------------------------------------------
-    以下适用于全部v8.18.0用户 [Logs]       -
---------------------------------------------
*/

-- ----------------------------
-- upgrade_20180729_logs.sql
-- ----------------------------
ALTER TABLE `picklog`
	MODIFY COLUMN `type` enum('M','P','L','T','V','S','N','C','A','R','G','E','B','O','I','X','D','U','$','F','Y','Z','Q','H') NOT NULL default 'P';

/*
---------------------------------------------
-  以下适用于将DB使用SQL数据库者，非者勿用  -
---------------------------------------------
*/

