/*
--------------------------------------------
-         以下适用于全部v8.5.0用户         -
--------------------------------------------
*/

ALTER TABLE `inventory` ADD COLUMN `bound` TINYINT(3) UNSIGNED NOT NULL DEFAULT '0' AFTER `favorite`;
ALTER TABLE `cart_inventory` ADD COLUMN `bound` TINYINT(3) UNSIGNED NOT NULL default '0' AFTER `expire_time`;
ALTER TABLE `storage` ADD COLUMN `bound` TINYINT(3) UNSIGNED NOT NULL default '0' AFTER `expire_time`;
ALTER TABLE `guild_storage` ADD COLUMN `bound` TINYINT(3) UNSIGNED NOT NULL default '0' AFTER `expire_time`;

CREATE TABLE IF NOT EXISTS `skillcooldown` (
  `account_id` int(11) unsigned NOT NULL,
  `char_id` int(11) unsigned NOT NULL,
  `skill` smallint(11) unsigned NOT NULL DEFAULT '0',
  `tick` int(11) NOT NULL,
  KEY `account_id` (`account_id`),
  KEY `char_id` (`char_id`)
) ENGINE=MyISAM;

ALTER TABLE `login` ADD `bank_vault` int(11) NOT NULL DEFAULT '0';

--
-- Table structure for table `bonus_script`
--

CREATE TABLE IF NOT EXISTS `bonus_script` (
  `char_id` int(11) NOT NULL,
  `script` varchar(1024) NOT NULL,
  `tick` int(11) NOT NULL,
  `flag` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `type` tinyint(1) unsigned NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

ALTER TABLE `inventory` MODIFY `equip` int(11) unsigned NOT NULL default '0';
ALTER TABLE `storage` MODIFY `equip` int(11) unsigned NOT NULL default '0';
ALTER TABLE `cart_inventory` MODIFY `equip` int(11) unsigned NOT NULL default '0';
ALTER TABLE `guild_storage` MODIFY `equip` int(11) unsigned NOT NULL default '0';

ALTER TABLE  `login` ADD  `vip_time` int(11) UNSIGNED NULL DEFAULT '0';
ALTER TABLE  `login` ADD  `old_group` tinyint(3) NOT NULL default '0';
ALTER TABLE `char` ADD `unban_time` int(11) unsigned NOT NULL default '0';

ALTER TABLE  `bonus_script` CHANGE  `char_id`  `char_id` VARCHAR( 11 ) NOT NULL;
ALTER TABLE  `bonus_script` CHANGE  `tick`  `tick` VARCHAR( 11 ) NOT NULL DEFAULT '0';
ALTER TABLE  `bonus_script` CHANGE  `flag`  `flag` VARCHAR( 3 ) NOT NULL DEFAULT '0';
ALTER TABLE  `bonus_script` CHANGE  `type`  `type` CHAR( 1 ) NOT NULL DEFAULT '0';
ALTER TABLE  `bonus_script` ADD  `icon` VARCHAR( 3 ) NOT NULL DEFAULT  '-1';

/*
---------------------------------------------
-  以下适用于将DB使用SQL数据库者，非者勿用  -
---------------------------------------------
*/

ALTER TABLE  `item_db` CHANGE  `equip_locations`  `equip_locations` MEDIUMINT( 7 ) UNSIGNED NULL DEFAULT NULL;
ALTER TABLE  `item_db_re` CHANGE  `equip_locations`  `equip_locations` MEDIUMINT( 7 ) UNSIGNED NULL DEFAULT NULL;
ALTER TABLE  `item_db2` CHANGE  `equip_locations`  `equip_locations` MEDIUMINT( 7 ) UNSIGNED NULL DEFAULT NULL;

ALTER TABLE `item_db` CHANGE `type` `type` TINYINT( 2 ) UNSIGNED NOT NULL DEFAULT '0';
ALTER TABLE `item_db` CHANGE `price_buy` `price_buy` MEDIUMINT( 8 ) UNSIGNED NULL DEFAULT NULL;
ALTER TABLE `item_db` CHANGE `price_sell` `price_sell` MEDIUMINT( 8 ) UNSIGNED NULL DEFAULT NULL;
ALTER TABLE `item_db` CHANGE `weight` `weight` SMALLINT( 5 ) UNSIGNED NOT NULL DEFAULT '0';
ALTER TABLE `item_db` CHANGE `attack` `attack` SMALLINT( 5 ) UNSIGNED NULL DEFAULT NULL;
ALTER TABLE `item_db` CHANGE `defence` `defence` SMALLINT( 5 ) UNSIGNED NULL DEFAULT NULL;
ALTER TABLE `item_db` CHANGE `range` `range` TINYINT( 2 ) UNSIGNED NULL DEFAULT NULL;
ALTER TABLE `item_db` CHANGE `slots` `slots` TINYINT( 2 ) UNSIGNED NULL DEFAULT NULL;
ALTER TABLE `item_db` CHANGE `equip_jobs` `equip_jobs` INT( 10 ) UNSIGNED NULL DEFAULT NULL;
ALTER TABLE `item_db` CHANGE `equip_upper` `equip_upper` TINYINT( 2 ) UNSIGNED NULL DEFAULT NULL;
ALTER TABLE `item_db` CHANGE `equip_genders` `equip_genders` TINYINT( 1 ) UNSIGNED NULL DEFAULT NULL;
ALTER TABLE `item_db` CHANGE `equip_locations` `equip_locations` MEDIUMINT( 7 ) UNSIGNED NULL DEFAULT NULL;
ALTER TABLE `item_db` CHANGE `weapon_level` `weapon_level` TINYINT( 1 ) UNSIGNED NULL DEFAULT NULL;
ALTER TABLE `item_db` CHANGE `equip_level` `equip_level` TINYINT( 3 ) NULL DEFAULT NULL;
ALTER TABLE `item_db` CHANGE `refineable` `refineable` TINYINT( 1 ) UNSIGNED NULL DEFAULT NULL;
ALTER TABLE `item_db` CHANGE `view` `view` SMALLINT( 5 ) UNSIGNED NULL DEFAULT NULL;

ALTER TABLE `item_db2` CHANGE `type` `type` TINYINT( 2 ) UNSIGNED NOT NULL DEFAULT '0';
ALTER TABLE `item_db2` CHANGE `price_buy` `price_buy` MEDIUMINT( 8 ) UNSIGNED NULL DEFAULT NULL;
ALTER TABLE `item_db2` CHANGE `price_sell` `price_sell` MEDIUMINT( 8 ) UNSIGNED NULL DEFAULT NULL;
ALTER TABLE `item_db2` CHANGE `weight` `weight` SMALLINT( 5 ) UNSIGNED NOT NULL DEFAULT '0';
ALTER TABLE `item_db2` CHANGE `attack` `attack` SMALLINT( 5 ) UNSIGNED NULL DEFAULT NULL;
ALTER TABLE `item_db2` CHANGE `defence` `defence` SMALLINT( 5 ) UNSIGNED NULL DEFAULT NULL;
ALTER TABLE `item_db2` CHANGE `range` `range` TINYINT( 2 ) UNSIGNED NULL DEFAULT NULL;
ALTER TABLE `item_db2` CHANGE `slots` `slots` TINYINT( 2 ) UNSIGNED NULL DEFAULT NULL;
ALTER TABLE `item_db2` CHANGE `equip_jobs` `equip_jobs` INT( 10 ) UNSIGNED NULL DEFAULT NULL;
ALTER TABLE `item_db2` CHANGE `equip_upper` `equip_upper` TINYINT( 2 ) UNSIGNED NULL DEFAULT NULL;
ALTER TABLE `item_db2` CHANGE `equip_genders` `equip_genders` TINYINT( 1 ) UNSIGNED NULL DEFAULT NULL;
ALTER TABLE `item_db2` CHANGE `equip_locations` `equip_locations` MEDIUMINT( 7 ) UNSIGNED NULL DEFAULT NULL;
ALTER TABLE `item_db2` CHANGE `weapon_level` `weapon_level` TINYINT( 1 ) UNSIGNED NULL DEFAULT NULL;
ALTER TABLE `item_db2` CHANGE `equip_level` `equip_level` TINYINT( 3 ) NULL DEFAULT NULL;
ALTER TABLE `item_db2` CHANGE `refineable` `refineable` TINYINT( 1 ) UNSIGNED NULL DEFAULT NULL;
ALTER TABLE `item_db2` CHANGE `view` `view` SMALLINT( 5 ) UNSIGNED NULL DEFAULT NULL;

ALTER TABLE `item_db_re` CHANGE `type` `type` TINYINT( 2 ) UNSIGNED NOT NULL DEFAULT '0';
ALTER TABLE `item_db_re` CHANGE `price_buy` `price_buy` MEDIUMINT( 8 ) UNSIGNED NULL DEFAULT NULL;
ALTER TABLE `item_db_re` CHANGE `price_sell` `price_sell` MEDIUMINT( 8 ) UNSIGNED NULL DEFAULT NULL;
ALTER TABLE `item_db_re` CHANGE `weight` `weight` SMALLINT( 5 ) UNSIGNED NOT NULL DEFAULT '0';
ALTER TABLE `item_db_re` CHANGE `atk:matk` `atk:matk` VARCHAR( 10 ) NULL DEFAULT NULL;
ALTER TABLE `item_db_re` CHANGE `defence` `defence` SMALLINT( 5 ) UNSIGNED NULL DEFAULT NULL;
ALTER TABLE `item_db_re` CHANGE `range` `range` TINYINT( 2 ) UNSIGNED NULL DEFAULT NULL;
ALTER TABLE `item_db_re` CHANGE `slots` `slots` TINYINT( 2 ) UNSIGNED NULL DEFAULT NULL;
ALTER TABLE `item_db_re` CHANGE `equip_jobs` `equip_jobs` INT( 10 ) UNSIGNED NULL DEFAULT NULL;
ALTER TABLE `item_db_re` CHANGE `equip_upper` `equip_upper` TINYINT( 2 ) UNSIGNED NULL DEFAULT NULL;
ALTER TABLE `item_db_re` CHANGE `equip_genders` `equip_genders` TINYINT( 1 ) UNSIGNED NULL DEFAULT NULL;
ALTER TABLE `item_db_re` CHANGE `equip_locations` `equip_locations` MEDIUMINT( 7 ) UNSIGNED NULL DEFAULT NULL;
ALTER TABLE `item_db_re` CHANGE `weapon_level` `weapon_level` TINYINT( 1 ) UNSIGNED NULL DEFAULT NULL;
ALTER TABLE `item_db_re` CHANGE `equip_level` `equip_level` VARCHAR( 10 ) NULL DEFAULT NULL;
ALTER TABLE `item_db_re` CHANGE `refineable` `refineable` TINYINT( 1 ) UNSIGNED NULL DEFAULT NULL;
ALTER TABLE `item_db_re` CHANGE `view` `view` SMALLINT( 5 ) UNSIGNED NULL DEFAULT NULL;

ALTER TABLE  `item_db_re` CHANGE  `atk:matk`  `atk:matk` VARCHAR( 11 ) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL

