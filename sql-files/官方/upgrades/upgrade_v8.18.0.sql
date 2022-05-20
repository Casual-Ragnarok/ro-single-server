/*
---------------------------------------------
-    以下适用于全部v8.17.0用户 [Main]       -
---------------------------------------------
*/

-- ----------------------------
-- upgrade_20180404.sql
-- ----------------------------
ALTER TABLE `homunculus`
	ADD COLUMN `autofeed` tinyint(2) NOT NULL default '0' AFTER `vaporize`;

-- ----------------------------
-- upgrade_v8.18.0 for `login`
-- ----------------------------
ALTER TABLE `login` ADD `server` int(10) NOT NULL default '0';

-- ----------------------------
-- upgrade_20180623.sql
-- ----------------------------
ALTER TABLE `guild_position` MODIFY COLUMN `mode` smallint(11) unsigned NOT NULL default '0';

/*
---------------------------------------------
-    以下适用于全部v8.17.0用户 [Logs]       -
---------------------------------------------
*/

-- ----------------------------
-- upgrade_20180705_logs.sql
-- ----------------------------
ALTER TABLE `picklog`
	MODIFY COLUMN `type` enum('M','P','L','T','V','S','N','C','A','R','G','E','B','O','I','X','D','U','$','F','Y','Z','Q') NOT NULL default 'P';

/*
---------------------------------------------
-  以下适用于将DB使用SQL数据库者，非者勿用  -
---------------------------------------------
*/

