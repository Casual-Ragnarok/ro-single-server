/*
--------------------------------------------
-         以下适用于全部v8.7.0用户         -
--------------------------------------------
*/

ALTER TABLE `char` ADD `font` tinyint(3) unsigned NOT NULL default '0';

UPDATE `auction` SET `card0` = 256 WHERE `card0` = -256;
UPDATE `cart_inventory` SET `card0` = 256 WHERE `card0` = -256;
UPDATE `guild_storage` SET `card0` = 256 WHERE `card0` = -256;
UPDATE `inventory` SET `card0` = 256 WHERE `card0` = -256;
UPDATE `mail` SET `card0` = 256 WHERE `card0` = -256;
UPDATE `storage` SET `card0` = 256 WHERE `card0` = -256;

ALTER TABLE `auction` MODIFY `nameid` smallint(5) unsigned NOT NULL default '0';
ALTER TABLE `auction` MODIFY `card0` smallint(5) unsigned NOT NULL default '0';
ALTER TABLE `auction` MODIFY `card1` smallint(5) unsigned NOT NULL default '0';
ALTER TABLE `auction` MODIFY `card2` smallint(5) unsigned NOT NULL default '0';
ALTER TABLE `auction` MODIFY `card3` smallint(5) unsigned NOT NULL default '0';
ALTER TABLE `cart_inventory` MODIFY `nameid` smallint(5) unsigned NOT NULL default '0';
ALTER TABLE `cart_inventory` MODIFY `card0` smallint(5) unsigned NOT NULL default '0';
ALTER TABLE `cart_inventory` MODIFY `card1` smallint(5) unsigned NOT NULL default '0';
ALTER TABLE `cart_inventory` MODIFY `card2` smallint(5) unsigned NOT NULL default '0';
ALTER TABLE `cart_inventory` MODIFY `card3` smallint(5) unsigned NOT NULL default '0';
ALTER TABLE `guild_storage` MODIFY `nameid` smallint(5) unsigned NOT NULL default '0';
ALTER TABLE `guild_storage` MODIFY `card0` smallint(5) unsigned NOT NULL default '0';
ALTER TABLE `guild_storage` MODIFY `card1` smallint(5) unsigned NOT NULL default '0';
ALTER TABLE `guild_storage` MODIFY `card2` smallint(5) unsigned NOT NULL default '0';
ALTER TABLE `guild_storage` MODIFY `card3` smallint(5) unsigned NOT NULL default '0';
ALTER TABLE `inventory` MODIFY `nameid` smallint(5) unsigned NOT NULL default '0';
ALTER TABLE `inventory` MODIFY `card0` smallint(5) unsigned NOT NULL default '0';
ALTER TABLE `inventory` MODIFY `card1` smallint(5) unsigned NOT NULL default '0';
ALTER TABLE `inventory` MODIFY `card2` smallint(5) unsigned NOT NULL default '0';
ALTER TABLE `inventory` MODIFY `card3` smallint(5) unsigned NOT NULL default '0';
ALTER TABLE `mail` MODIFY `nameid` smallint(5) unsigned NOT NULL default '0';
ALTER TABLE `mail` MODIFY `card0` smallint(5) unsigned NOT NULL default '0';
ALTER TABLE `mail` MODIFY `card1` smallint(5) unsigned NOT NULL default '0';
ALTER TABLE `mail` MODIFY `card2` smallint(5) unsigned NOT NULL default '0';
ALTER TABLE `mail` MODIFY `card3` smallint(5) unsigned NOT NULL default '0';
ALTER TABLE `pet` MODIFY `egg_id` smallint(5) unsigned NOT NULL default '0';
ALTER TABLE `storage` MODIFY `nameid` smallint(5) unsigned NOT NULL default '0';
ALTER TABLE `storage` MODIFY `card0` smallint(5) unsigned NOT NULL default '0';
ALTER TABLE `storage` MODIFY `card1` smallint(5) unsigned NOT NULL default '0';
ALTER TABLE `storage` MODIFY `card2` smallint(5) unsigned NOT NULL default '0';
ALTER TABLE `storage` MODIFY `card3` smallint(5) unsigned NOT NULL default '0';

UPDATE `picklog` SET `card0` = 256 WHERE `card0` = -256;

ALTER TABLE `picklog` MODIFY `nameid` smallint(5) unsigned NOT NULL default '0';
ALTER TABLE `picklog` MODIFY `card0` smallint(5) unsigned NOT NULL default '0';
ALTER TABLE `picklog` MODIFY `card1` smallint(5) unsigned NOT NULL default '0';
ALTER TABLE `picklog` MODIFY `card2` smallint(5) unsigned NOT NULL default '0';
ALTER TABLE `picklog` MODIFY `card3` smallint(5) unsigned NOT NULL default '0';
ALTER TABLE `mvplog` MODIFY `prize` smallint(5) unsigned NOT NULL default '0';

UPDATE `auction` SET `card0` = 256 WHERE `card0` = 65280;
UPDATE `cart_inventory` SET `card0` = 256 WHERE `card0` = 65280;
UPDATE `guild_storage` SET `card0` = 256 WHERE `card0` = 65280;
UPDATE `inventory` SET `card0` = 256 WHERE `card0` = 65280;
UPDATE `mail` SET `card0` = 256 WHERE `card0` = 65280;
UPDATE `storage` SET `card0` = 256 WHERE `card0` = 65280;

ALTER TABLE `pet` CHANGE `incuvate` `incubate` int(11) unsigned NOT NULL default '0';

ALTER TABLE `zenylog` CHANGE `type` `type` ENUM('T','V','P','M','S','N','D','C','A','E','I','B','K') NOT NULL DEFAULT 'S';

ALTER TABLE `vendings` ADD `body_direction` CHAR( 1 ) NOT NULL DEFAULT '4',
ADD `head_direction` CHAR( 1 ) NOT NULL DEFAULT '0',
ADD `sit` CHAR( 1 ) NOT NULL DEFAULT '1';

ALTER TABLE `buyingstores` ADD `body_direction` CHAR( 1 ) NOT NULL DEFAULT '4',
ADD `head_direction` CHAR( 1 ) NOT NULL DEFAULT '0',
ADD `sit` CHAR( 1 ) NOT NULL DEFAULT '1';

ALTER TABLE `char` ADD COLUMN `uniqueitem_counter` bigint(20) NOT NULL AFTER `font`;

/*
---------------------------------------------
-  以下适用于将DB使用SQL数据库者，非者勿用  -
---------------------------------------------
*/

ALTER TABLE `mob_db` MODIFY `MVP1id` smallint(5) unsigned NOT NULL default '0';
ALTER TABLE `mob_db` MODIFY `MVP2id` smallint(5) unsigned NOT NULL default '0';
ALTER TABLE `mob_db` MODIFY `MVP3id` smallint(5) unsigned NOT NULL default '0';
ALTER TABLE `mob_db` MODIFY `Drop1id` smallint(5) unsigned NOT NULL default '0';
ALTER TABLE `mob_db` MODIFY `Drop2id` smallint(5) unsigned NOT NULL default '0';
ALTER TABLE `mob_db` MODIFY `Drop3id` smallint(5) unsigned NOT NULL default '0';
ALTER TABLE `mob_db` MODIFY `Drop4id` smallint(5) unsigned NOT NULL default '0';
ALTER TABLE `mob_db` MODIFY `Drop5id` smallint(5) unsigned NOT NULL default '0';
ALTER TABLE `mob_db` MODIFY `Drop6id` smallint(5) unsigned NOT NULL default '0';
ALTER TABLE `mob_db` MODIFY `Drop7id` smallint(5) unsigned NOT NULL default '0';
ALTER TABLE `mob_db` MODIFY `Drop8id` smallint(5) unsigned NOT NULL default '0';
ALTER TABLE `mob_db` MODIFY `Drop9id` smallint(5) unsigned NOT NULL default '0';
ALTER TABLE `mob_db` MODIFY `DropCardid` smallint(5) unsigned NOT NULL default '0';

ALTER TABLE `mob_db2` MODIFY `MVP1id` smallint(5) unsigned NOT NULL default '0';
ALTER TABLE `mob_db2` MODIFY `MVP2id` smallint(5) unsigned NOT NULL default '0';
ALTER TABLE `mob_db2` MODIFY `MVP3id` smallint(5) unsigned NOT NULL default '0';
ALTER TABLE `mob_db2` MODIFY `Drop1id` smallint(5) unsigned NOT NULL default '0';
ALTER TABLE `mob_db2` MODIFY `Drop2id` smallint(5) unsigned NOT NULL default '0';
ALTER TABLE `mob_db2` MODIFY `Drop3id` smallint(5) unsigned NOT NULL default '0';
ALTER TABLE `mob_db2` MODIFY `Drop4id` smallint(5) unsigned NOT NULL default '0';
ALTER TABLE `mob_db2` MODIFY `Drop5id` smallint(5) unsigned NOT NULL default '0';
ALTER TABLE `mob_db2` MODIFY `Drop6id` smallint(5) unsigned NOT NULL default '0';
ALTER TABLE `mob_db2` MODIFY `Drop7id` smallint(5) unsigned NOT NULL default '0';
ALTER TABLE `mob_db2` MODIFY `Drop8id` smallint(5) unsigned NOT NULL default '0';
ALTER TABLE `mob_db2` MODIFY `Drop9id` smallint(5) unsigned NOT NULL default '0';
ALTER TABLE `mob_db2` MODIFY `DropCardid` smallint(5) unsigned NOT NULL default '0';

ALTER TABLE `mob_db2_re` MODIFY `MVP1id` smallint(5) unsigned NOT NULL default '0';
ALTER TABLE `mob_db2_re` MODIFY `MVP2id` smallint(5) unsigned NOT NULL default '0';
ALTER TABLE `mob_db2_re` MODIFY `MVP3id` smallint(5) unsigned NOT NULL default '0';
ALTER TABLE `mob_db2_re` MODIFY `Drop1id` smallint(5) unsigned NOT NULL default '0';
ALTER TABLE `mob_db2_re` MODIFY `Drop2id` smallint(5) unsigned NOT NULL default '0';
ALTER TABLE `mob_db2_re` MODIFY `Drop3id` smallint(5) unsigned NOT NULL default '0';
ALTER TABLE `mob_db2_re` MODIFY `Drop4id` smallint(5) unsigned NOT NULL default '0';
ALTER TABLE `mob_db2_re` MODIFY `Drop5id` smallint(5) unsigned NOT NULL default '0';
ALTER TABLE `mob_db2_re` MODIFY `Drop6id` smallint(5) unsigned NOT NULL default '0';
ALTER TABLE `mob_db2_re` MODIFY `Drop7id` smallint(5) unsigned NOT NULL default '0';
ALTER TABLE `mob_db2_re` MODIFY `Drop8id` smallint(5) unsigned NOT NULL default '0';
ALTER TABLE `mob_db2_re` MODIFY `Drop9id` smallint(5) unsigned NOT NULL default '0';
ALTER TABLE `mob_db2_re` MODIFY `DropCardid` smallint(5) unsigned NOT NULL default '0';

ALTER TABLE `mob_db_re` MODIFY `MVP1id` smallint(5) unsigned NOT NULL default '0';
ALTER TABLE `mob_db_re` MODIFY `MVP2id` smallint(5) unsigned NOT NULL default '0';
ALTER TABLE `mob_db_re` MODIFY `MVP3id` smallint(5) unsigned NOT NULL default '0';
ALTER TABLE `mob_db_re` MODIFY `Drop1id` smallint(5) unsigned NOT NULL default '0';
ALTER TABLE `mob_db_re` MODIFY `Drop2id` smallint(5) unsigned NOT NULL default '0';
ALTER TABLE `mob_db_re` MODIFY `Drop3id` smallint(5) unsigned NOT NULL default '0';
ALTER TABLE `mob_db_re` MODIFY `Drop4id` smallint(5) unsigned NOT NULL default '0';
ALTER TABLE `mob_db_re` MODIFY `Drop5id` smallint(5) unsigned NOT NULL default '0';
ALTER TABLE `mob_db_re` MODIFY `Drop6id` smallint(5) unsigned NOT NULL default '0';
ALTER TABLE `mob_db_re` MODIFY `Drop7id` smallint(5) unsigned NOT NULL default '0';
ALTER TABLE `mob_db_re` MODIFY `Drop8id` smallint(5) unsigned NOT NULL default '0';
ALTER TABLE `mob_db_re` MODIFY `Drop9id` smallint(5) unsigned NOT NULL default '0';
ALTER TABLE `mob_db_re` MODIFY `DropCardid` smallint(5) unsigned NOT NULL default '0';
