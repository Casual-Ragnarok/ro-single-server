/*
---------------------------------------------
-         以下适用于全部v8.12.0用户         -
---------------------------------------------
*/

--
-- Table structure for table `feedinglog`
--

CREATE TABLE IF NOT EXISTS `feedinglog` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `time` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
  `char_id` INT(11) NOT NULL,
  `target_id` INT(11) NOT NULL,
  `target_class` SMALLINT(11) NOT NULL,
  `type` ENUM('P','H','O') NOT NULL, -- P: Pet, H: Homunculus, O: Other
  `intimacy` INT(11) UNSIGNED NOT NULL,
  `item_id` SMALLINT(5) UNSIGNED NOT NULL,
  `map` VARCHAR(11) NOT NULL,
  `x` SMALLINT(5) UNSIGNED NOT NULL,
  `y` SMALLINT(5) UNSIGNED NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE = MyISAM AUTO_INCREMENT = 1;

ALTER TABLE `char` ADD `body`smallint(5) unsigned NOT NULL default '0' AFTER  `clothes_color`;

/*
---------------------------------------------
-  以下适用于将DB使用SQL数据库者，非者勿用  -
---------------------------------------------
*/

-- Please only run those, that you need

ALTER TABLE `item_db`
	ADD UNIQUE INDEX `UniqueAegisName` (`name_english`) ;

ALTER TABLE `item_db2`
	ADD UNIQUE INDEX `UniqueAegisName` (`name_english`) ;

ALTER TABLE `item_db_re`
	ADD UNIQUE INDEX `UniqueAegisName` (`name_english`) ;

ALTER TABLE `item_db2_re`
	ADD UNIQUE INDEX `UniqueAegisName` (`name_english`) ;
