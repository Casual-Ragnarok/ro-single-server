--
-- Table structure for table `acc_reg_num`
--

CREATE TABLE IF NOT EXISTS `acc_reg_num` (
  `account_id` int(11) unsigned NOT NULL default '0',
  `key` varchar(32) binary NOT NULL default '',
  `index` int(11) unsigned NOT NULL default '0',
  `value` int(11) NOT NULL default '0',
  PRIMARY KEY (`account_id`,`key`,`index`),
  KEY `account_id` (`account_id`)
) ENGINE=MyISAM;

--
-- Table structure for table `acc_reg_str`
--

CREATE TABLE IF NOT EXISTS `acc_reg_str` (
  `account_id` int(11) unsigned NOT NULL default '0',
  `key` varchar(32) binary NOT NULL default '',
  `index` int(11) unsigned NOT NULL default '0',
  `value` varchar(254) NOT NULL default '0',
  PRIMARY KEY (`account_id`,`key`,`index`),
  KEY `account_id` (`account_id`)
) ENGINE=MyISAM;

--
-- Table structure for table `achievement`
--

CREATE TABLE IF NOT EXISTS `achievement` (
  `char_id` int(11) unsigned NOT NULL default '0',
  `id` bigint(11) unsigned NOT NULL,
  `count1` mediumint(8) unsigned NOT NULL default '0',
  `count2` mediumint(8) unsigned NOT NULL default '0',
  `count3` mediumint(8) unsigned NOT NULL default '0',
  `count4` mediumint(8) unsigned NOT NULL default '0',
  `count5` mediumint(8) unsigned NOT NULL default '0',
  `count6` mediumint(8) unsigned NOT NULL default '0',
  `count7` mediumint(8) unsigned NOT NULL default '0',
  `count8` mediumint(8) unsigned NOT NULL default '0',
  `count9` mediumint(8) unsigned NOT NULL default '0',
  `count10` mediumint(8) unsigned NOT NULL default '0',
  `completed` datetime,
  `rewarded` datetime,
  PRIMARY KEY (`char_id`,`id`),
  KEY `char_id` (`char_id`)
) ENGINE=MyISAM;

--
-- Table structure for table `auction`
--

CREATE TABLE IF NOT EXISTS `auction` (
  `auction_id` bigint(20) unsigned NOT NULL auto_increment,
  `seller_id` int(11) unsigned NOT NULL default '0',
  `seller_name` varchar(30) NOT NULL default '',
  `buyer_id` int(11) unsigned NOT NULL default '0',
  `buyer_name` varchar(30) NOT NULL default '',
  `price` int(11) unsigned NOT NULL default '0',
  `buynow` int(11) unsigned NOT NULL default '0',
  `hours` smallint(6) NOT NULL default '0',
  `timestamp` int(11) unsigned NOT NULL default '0',
  `nameid` smallint(5) unsigned NOT NULL default '0',
  `item_name` varchar(50) NOT NULL default '',
  `type` smallint(6) NOT NULL default '0',
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
  `unique_id` bigint(20) unsigned NOT NULL default '0',
  PRIMARY KEY  (`auction_id`)
) ENGINE=MyISAM;

--
-- Table structure for `db_roulette`
--

CREATE TABLE IF NOT EXISTS `db_roulette` (
  `index` int(11) NOT NULL default '0',
  `level` smallint(5) unsigned NOT NULL,
  `item_id` smallint(5) unsigned NOT NULL,
  `amount` smallint(5) unsigned NOT NULL DEFAULT '1',
  `flag` smallint(5) unsigned NOT NULL DEFAULT '1',
  PRIMARY KEY (`index`)
) ENGINE=MyISAM;

--
-- Table structure for table `bonus_script`
--

CREATE TABLE IF NOT EXISTS `bonus_script` (
  `char_id` INT(11) UNSIGNED NOT NULL,
  `script` TEXT NOT NULL,
  `tick` BIGINT(20) NOT NULL DEFAULT '0',
  `flag` SMALLINT(5) UNSIGNED NOT NULL DEFAULT '0',
  `type` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
  `icon` SMALLINT(3) NOT NULL DEFAULT '-1'
) ENGINE=InnoDB;

--
-- Table structure for table `buyingstore_items`
--

CREATE TABLE IF NOT EXISTS `buyingstore_items` (
  `buyingstore_id` int(10) unsigned NOT NULL,
  `index` smallint(5) unsigned NOT NULL,
  `item_id` int(10) unsigned NOT NULL,
  `amount` smallint(5) unsigned NOT NULL,
  `price` int(10) unsigned NOT NULL
) ENGINE=MyISAM;

--
-- Table structure for table `buyingstores`
--

CREATE TABLE IF NOT EXISTS `buyingstores` (
  `id` int(10) unsigned NOT NULL,
  `account_id` int(11) unsigned NOT NULL,
  `char_id` int(10) unsigned NOT NULL,
  `sex` enum('F','M') NOT NULL DEFAULT 'M',
  `map` varchar(20) NOT NULL,
  `x` smallint(5) unsigned NOT NULL,
  `y` smallint(5) unsigned NOT NULL,
  `title` varchar(80) NOT NULL,
  `limit` int(10) unsigned NOT NULL,
  `body_direction` CHAR( 1 ) NOT NULL DEFAULT '4',
  `head_direction` CHAR( 1 ) NOT NULL DEFAULT '0',
  `sit` CHAR( 1 ) NOT NULL DEFAULT '1',
  `autotrade` tinyint(4) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM;

--
-- Table structure for table `cart_inventory`
--

CREATE TABLE IF NOT EXISTS `cart_inventory` (
  `id` int(11) NOT NULL auto_increment,
  `char_id` int(11) NOT NULL default '0',
  `nameid` smallint(5) unsigned NOT NULL default '0',
  `amount` int(11) NOT NULL default '0',
  `equip` int(11) unsigned NOT NULL default '0',
  `identify` smallint(6) NOT NULL default '0',
  `refine` tinyint(3) unsigned NOT NULL default '0',
  `attribute` tinyint(4) NOT NULL default '0',
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
  `bound` tinyint(3) unsigned NOT NULL default '0',
  `unique_id` bigint(20) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `char_id` (`char_id`)
) ENGINE=MyISAM;

--
-- Table structure for table `char`
--

CREATE TABLE IF NOT EXISTS `char` (
  `char_id` int(11) unsigned NOT NULL auto_increment,
  `account_id` int(11) unsigned NOT NULL default '0',
  `char_num` tinyint(1) NOT NULL default '0',
  `name` varchar(30) NOT NULL DEFAULT '',
  `class` smallint(6) unsigned NOT NULL default '0',
  `base_level` smallint(6) unsigned NOT NULL default '1',
  `job_level` smallint(6) unsigned NOT NULL default '1',
  `base_exp` bigint(20) unsigned NOT NULL default '0',
  `job_exp` bigint(20) unsigned NOT NULL default '0',
  `zeny` int(11) unsigned NOT NULL default '0',
  `str` smallint(4) unsigned NOT NULL default '0',
  `agi` smallint(4) unsigned NOT NULL default '0',
  `vit` smallint(4) unsigned NOT NULL default '0',
  `int` smallint(4) unsigned NOT NULL default '0',
  `dex` smallint(4) unsigned NOT NULL default '0',
  `luk` smallint(4) unsigned NOT NULL default '0',
  `max_hp` int(11) unsigned NOT NULL default '0',
  `hp` int(11) unsigned NOT NULL default '0',
  `max_sp` int(11) unsigned NOT NULL default '0',
  `sp` int(11) unsigned NOT NULL default '0',
  `status_point` int(11) unsigned NOT NULL default '0',
  `skill_point` int(11) unsigned NOT NULL default '0',
  `option` int(11) NOT NULL default '0',
  `karma` tinyint(3) NOT NULL default '0',
  `manner` smallint(6) NOT NULL default '0',
  `party_id` int(11) unsigned NOT NULL default '0',
  `guild_id` int(11) unsigned NOT NULL default '0',
  `pet_id` int(11) unsigned NOT NULL default '0',
  `homun_id` int(11) unsigned NOT NULL default '0',
  `elemental_id` int(11) unsigned NOT NULL default '0',
  `hair` tinyint(4) unsigned NOT NULL default '0',
  `hair_color` smallint(5) unsigned NOT NULL default '0',
  `clothes_color` smallint(5) unsigned NOT NULL default '0',
  `body` smallint(5) unsigned NOT NULL default '0',
  `weapon` smallint(6) unsigned NOT NULL default '0',
  `shield` smallint(6) unsigned NOT NULL default '0',
  `head_top` smallint(6) unsigned NOT NULL default '0',
  `head_mid` smallint(6) unsigned NOT NULL default '0',
  `head_bottom` smallint(6) unsigned NOT NULL default '0',
  `robe` SMALLINT(6) UNSIGNED NOT NULL DEFAULT '0',
  `last_map` varchar(11) NOT NULL default '',
  `last_x` smallint(4) unsigned NOT NULL default '53',
  `last_y` smallint(4) unsigned NOT NULL default '111',
  `save_map` varchar(11) NOT NULL default '',
  `save_x` smallint(4) unsigned NOT NULL default '53',
  `save_y` smallint(4) unsigned NOT NULL default '111',
  `partner_id` int(11) unsigned NOT NULL default '0',
  `online` tinyint(2) NOT NULL default '0',
  `father` int(11) unsigned NOT NULL default '0',
  `mother` int(11) unsigned NOT NULL default '0',
  `child` int(11) unsigned NOT NULL default '0',
  `fame` int(11) unsigned NOT NULL default '0',
  `rename` SMALLINT(3) unsigned NOT NULL default '0',
  `delete_date` INT(11) UNSIGNED NOT NULL DEFAULT '0',
  `moves` int(11) unsigned NOT NULL DEFAULT '0',
  `unban_time` int(11) unsigned NOT NULL default '0',
  `font` tinyint(3) unsigned NOT NULL default '0',
  `uniqueitem_counter` int(11) unsigned NOT NULL default '0',
  `sex` ENUM('M','F','U') NOT NULL default 'U',
  `hotkey_rowshift` tinyint(3) unsigned NOT NULL default '0',
  `clan_id` int(11) unsigned NOT NULL default '0',
  `last_login` datetime DEFAULT NULL,
  `title_id` INT(11) unsigned NOT NULL default '0',
  `show_equip` tinyint(3) unsigned NOT NULL default '0',
  PRIMARY KEY  (`char_id`),
  UNIQUE KEY `name_key` (`name`),
  KEY `account_id` (`account_id`),
  KEY `party_id` (`party_id`),
  KEY `guild_id` (`guild_id`),
  KEY `online` (`online`)
) ENGINE=MyISAM AUTO_INCREMENT=150000; 

--
-- Table structure for table `char_reg_num`
--

CREATE TABLE IF NOT EXISTS `char_reg_num` (
  `char_id` int(11) unsigned NOT NULL default '0',
  `key` varchar(32) binary NOT NULL default '',
  `index` int(11) unsigned NOT NULL default '0',
  `value` int(11) NOT NULL default '0',
  PRIMARY KEY (`char_id`,`key`,`index`),
  KEY `char_id` (`char_id`)
) ENGINE=MyISAM;

--
-- Table structure for table `char_reg_str`
--

CREATE TABLE IF NOT EXISTS `char_reg_str` (
  `char_id` int(11) unsigned NOT NULL default '0',
  `key` varchar(32) binary NOT NULL default '',
  `index` int(11) unsigned NOT NULL default '0',
  `value` varchar(254) NOT NULL default '0',
  PRIMARY KEY (`char_id`,`key`,`index`),
  KEY `char_id` (`char_id`)
) ENGINE=MyISAM;

--
-- Table structure for table `charlog`
--

CREATE TABLE IF NOT EXISTS `charlog` (
  `time` datetime NOT NULL,
  `char_msg` varchar(255) NOT NULL default 'char select',
  `account_id` int(11) NOT NULL default '0',
  `char_num` tinyint(4) NOT NULL default '0',
  `name` varchar(23) NOT NULL default '',
  `str` int(11) unsigned NOT NULL default '0',
  `agi` int(11) unsigned NOT NULL default '0',
  `vit` int(11) unsigned NOT NULL default '0',
  `int` int(11) unsigned NOT NULL default '0',
  `dex` int(11) unsigned NOT NULL default '0',
  `luk` int(11) unsigned NOT NULL default '0',
  `hair` tinyint(4) NOT NULL default '0',
  `hair_color` int(11) NOT NULL default '0'
) ENGINE=MyISAM; 

--
-- Table structure for table `clan`
--
CREATE TABLE IF NOT EXISTS `clan` (
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

-- ----------------------------
-- Table structure for `clan_alliance`
-- ----------------------------
CREATE TABLE IF NOT EXISTS `clan_alliance` (
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

--
-- Table structure for table `elemental`
--

CREATE TABLE IF NOT EXISTS `elemental` (
  `ele_id` int(11) unsigned NOT NULL auto_increment,
  `char_id` int(11) NOT NULL,
  `class` mediumint(9) unsigned NOT NULL default '0',
  `mode` int(11) unsigned NOT NULL default '1',
  `hp` int(11) unsigned NOT NULL default '0',
  `sp` int(11) unsigned NOT NULL default '0',
  `max_hp` int(11) unsigned NOT NULL default '0',
  `max_sp` int(11) unsigned NOT NULL default '0',
  `atk1` MEDIUMINT(6) unsigned NOT NULL default '0',
  `atk2` MEDIUMINT(6) unsigned NOT NULL default '0',
  `matk` MEDIUMINT(6) unsigned NOT NULL default '0',
  `aspd` smallint(4) unsigned NOT NULL default '0',
  `def` smallint(4) unsigned NOT NULL default '0',
  `mdef` smallint(4) unsigned NOT NULL default '0',
  `flee` smallint(4) unsigned NOT NULL default '0',
  `hit` smallint(4) unsigned NOT NULL default '0',
  `life_time` bigint(20) NOT NULL default '0',
  PRIMARY KEY  (`ele_id`)
) ENGINE=MyISAM;

--
-- Table structure for table `friends`
--

CREATE TABLE IF NOT EXISTS `friends` (
  `char_id` int(11) NOT NULL default '0',
  `friend_account` int(11) NOT NULL default '0',
  `friend_id` int(11) NOT NULL default '0',
  KEY  `char_id` (`char_id`)
) ENGINE=MyISAM;

--
-- Table structure for table `global_acc_reg_num`
--

CREATE TABLE IF NOT EXISTS `global_acc_reg_num` (
  `account_id` int(11) unsigned NOT NULL default '0',
  `key` varchar(32) binary NOT NULL default '',
  `index` int(11) unsigned NOT NULL default '0',
  `value` int(11) NOT NULL default '0',
  PRIMARY KEY (`account_id`,`key`,`index`),
  KEY `account_id` (`account_id`)
) ENGINE=MyISAM;

--
-- Table structure for table `global_acc_reg_str`
--

CREATE TABLE IF NOT EXISTS `global_acc_reg_str` (
  `account_id` int(11) unsigned NOT NULL default '0',
  `key` varchar(32) binary NOT NULL default '',
  `index` int(11) unsigned NOT NULL default '0',
  `value` varchar(254) NOT NULL default '0',
  PRIMARY KEY (`account_id`,`key`,`index`),
  KEY `account_id` (`account_id`)
) ENGINE=MyISAM;

--
-- Table structure for table `guild`
--

CREATE TABLE IF NOT EXISTS `guild` (
  `guild_id` int(11) unsigned NOT NULL auto_increment,
  `name` varchar(24) NOT NULL default '',
  `char_id` int(11) unsigned NOT NULL default '0',
  `master` varchar(24) NOT NULL default '',
  `guild_lv` tinyint(6) unsigned NOT NULL default '0',
  `connect_member` tinyint(6) unsigned NOT NULL default '0',
  `max_member` tinyint(6) unsigned NOT NULL default '0',
  `average_lv` smallint(6) unsigned NOT NULL default '1',
  `exp` bigint(20) unsigned NOT NULL default '0',
  `next_exp` int(11) unsigned NOT NULL default '0',
  `skill_point` tinyint(11) unsigned NOT NULL default '0',
  `mes1` varchar(60) NOT NULL default '',
  `mes2` varchar(120) NOT NULL default '',
  `emblem_len` int(11) unsigned NOT NULL default '0',
  `emblem_id` int(11) unsigned NOT NULL default '0',
  `emblem_data` blob,
  `last_master_change` datetime,
  PRIMARY KEY  (`guild_id`,`char_id`),
  UNIQUE KEY `guild_id` (`guild_id`),
  KEY `char_id` (`char_id`)
) ENGINE=MyISAM;

--
-- Table structure for table `guild_alliance`
--

CREATE TABLE IF NOT EXISTS `guild_alliance` (
  `guild_id` int(11) unsigned NOT NULL default '0',
  `opposition` int(11) unsigned NOT NULL default '0',
  `alliance_id` int(11) unsigned NOT NULL default '0',
  `name` varchar(24) NOT NULL default '',
  PRIMARY KEY  (`guild_id`,`alliance_id`),
  KEY `alliance_id` (`alliance_id`)
) ENGINE=MyISAM;

--
-- Table structure for table `guild_castle`
--

CREATE TABLE IF NOT EXISTS `guild_castle` (
  `castle_id` int(11) unsigned NOT NULL default '0',
  `guild_id` int(11) unsigned NOT NULL default '0',
  `economy` int(11) unsigned NOT NULL default '0',
  `defense` int(11) unsigned NOT NULL default '0',
  `triggerE` int(11) unsigned NOT NULL default '0',
  `triggerD` int(11) unsigned NOT NULL default '0',
  `nextTime` int(11) unsigned NOT NULL default '0',
  `payTime` int(11) unsigned NOT NULL default '0',
  `createTime` int(11) unsigned NOT NULL default '0',
  `visibleC` int(11) unsigned NOT NULL default '0',
  `visibleG0` int(11) unsigned NOT NULL default '0',
  `visibleG1` int(11) unsigned NOT NULL default '0',
  `visibleG2` int(11) unsigned NOT NULL default '0',
  `visibleG3` int(11) unsigned NOT NULL default '0',
  `visibleG4` int(11) unsigned NOT NULL default '0',
  `visibleG5` int(11) unsigned NOT NULL default '0',
  `visibleG6` int(11) unsigned NOT NULL default '0',
  `visibleG7` int(11) unsigned NOT NULL default '0',
  PRIMARY KEY  (`castle_id`),
  KEY `guild_id` (`guild_id`)
) ENGINE=MyISAM;

--
-- Table structure for table `guild_expulsion`
--

CREATE TABLE IF NOT EXISTS `guild_expulsion` (
  `guild_id` int(11) unsigned NOT NULL default '0',
  `account_id` int(11) unsigned NOT NULL default '0',
  `name` varchar(24) NOT NULL default '',
  `mes` varchar(40) NOT NULL default '',
  PRIMARY KEY  (`guild_id`,`name`)
) ENGINE=MyISAM;

--
-- Table structure for table `guild_member`
--

CREATE TABLE IF NOT EXISTS `guild_member` (
  `guild_id` int(11) unsigned NOT NULL default '0',
  `account_id` int(11) unsigned NOT NULL default '0',
  `char_id` int(11) unsigned NOT NULL default '0',
  `hair` tinyint(6) unsigned NOT NULL default '0',
  `hair_color` smallint(6) unsigned NOT NULL default '0',
  `gender` tinyint(6) unsigned NOT NULL default '0',
  `class` smallint(6) unsigned NOT NULL default '0',
  `lv` smallint(6) unsigned NOT NULL default '0',
  `exp` bigint(20) unsigned NOT NULL default '0',
  `exp_payper` tinyint(11) unsigned NOT NULL default '0',
  `online` tinyint(4) unsigned NOT NULL default '0',
  `position` tinyint(6) unsigned NOT NULL default '0',
  `name` varchar(24) NOT NULL default '',
  PRIMARY KEY  (`guild_id`,`char_id`),
  KEY `char_id` (`char_id`)
) ENGINE=MyISAM;

--
-- Table structure for table `guild_position`
--

CREATE TABLE IF NOT EXISTS `guild_position` (
  `guild_id` int(9) unsigned NOT NULL default '0',
  `position` tinyint(6) unsigned NOT NULL default '0',
  `name` varchar(24) NOT NULL default '',
  `mode` smallint(11) unsigned NOT NULL default '0',
  `exp_mode` tinyint(11) unsigned NOT NULL default '0',
  PRIMARY KEY  (`guild_id`,`position`)
) ENGINE=MyISAM;

--
-- Table structure for table `guild_skill`
--

CREATE TABLE IF NOT EXISTS `guild_skill` (
  `guild_id` int(11) unsigned NOT NULL default '0',
  `id` smallint(11) unsigned NOT NULL default '0',
  `lv` tinyint(11) unsigned NOT NULL default '0',
  PRIMARY KEY  (`guild_id`,`id`)
) ENGINE=MyISAM;

--
-- Table structure for table `guild_storage`
--

CREATE TABLE IF NOT EXISTS `guild_storage` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `guild_id` int(11) unsigned NOT NULL default '0',
  `nameid` smallint(5) unsigned NOT NULL default '0',
  `amount` int(11) unsigned NOT NULL default '0',
  `equip` int(11) unsigned NOT NULL default '0',
  `identify` smallint(6) unsigned NOT NULL default '0',
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
  `bound` tinyint(3) unsigned NOT NULL default '0',
  `unique_id` bigint(20) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `guild_id` (`guild_id`)
) ENGINE=MyISAM;

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

--
-- Table structure for table `homunculus`
--

CREATE TABLE IF NOT EXISTS `homunculus` (
  `homun_id` int(11) NOT NULL auto_increment,
  `char_id` int(11) NOT NULL,
  `class` mediumint(9) unsigned NOT NULL default '0',
  `prev_class` mediumint(9) NOT NULL default '0',
  `name` varchar(24) NOT NULL default '',
  `level` smallint(4) NOT NULL default '0',
  `exp` bigint(20) unsigned NOT NULL default '0',
  `intimacy` int(12) NOT NULL default '0',
  `hunger` smallint(4) NOT NULL default '0',
  `str` smallint(4) unsigned NOT NULL default '0',
  `agi` smallint(4) unsigned NOT NULL default '0',
  `vit` smallint(4) unsigned NOT NULL default '0',
  `int` smallint(4) unsigned NOT NULL default '0',
  `dex` smallint(4) unsigned NOT NULL default '0',
  `luk` smallint(4) unsigned NOT NULL default '0',
  `hp` int(11) unsigned NOT NULL default '0',
  `max_hp` int(11) unsigned NOT NULL default '0',
  `sp` int(11) NOT NULL default '0',
  `max_sp` int(11) NOT NULL default '0',
  `skill_point` smallint(4) unsigned NOT NULL default '0',
  `alive` tinyint(2) NOT NULL default '1',
  `rename_flag` tinyint(2) NOT NULL default '0',
  `vaporize` tinyint(2) NOT NULL default '0',
  `autofeed` tinyint(2) NOT NULL default '0',
  PRIMARY KEY  (`homun_id`)
) ENGINE=MyISAM;

--
-- Table structure for table `hotkey`
--

CREATE TABLE IF NOT EXISTS `hotkey` (
	`char_id` INT(11) NOT NULL,
	`hotkey` TINYINT(2) unsigned NOT NULL,
	`type` TINYINT(1) unsigned NOT NULL default '0',
	`itemskill_id` INT(11) unsigned NOT NULL default '0',
	`skill_lvl` TINYINT(4) unsigned NOT NULL default '0',
	PRIMARY KEY (`char_id`,`hotkey`)
) ENGINE=MyISAM;

-- 
-- Table structure for table `interlog`
--

CREATE TABLE IF NOT EXISTS `interlog` (
  `time` datetime NOT NULL,
  `log` varchar(255) NOT NULL default ''
) ENGINE=MyISAM; 

--
-- Table structure for table `interreg`
--

CREATE TABLE IF NOT EXISTS `interreg` (
  `varname` varchar(11) NOT NULL,
  `value` varchar(20) NOT NULL,
   PRIMARY KEY (`varname`)
) ENGINE=InnoDB;

--
-- Table structure for table `inventory`
--

CREATE TABLE IF NOT EXISTS `inventory` (
  `id` int(11) unsigned NOT NULL auto_increment,
  `char_id` int(11) unsigned NOT NULL default '0',
  `nameid` smallint(5) unsigned NOT NULL default '0',
  `amount` int(11) unsigned NOT NULL default '0',
  `equip` int(11) unsigned NOT NULL default '0',
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
  `favorite` tinyint(3) unsigned NOT NULL default '0',
  `bound` tinyint(3) unsigned NOT NULL default '0',
  `unique_id` bigint(20) unsigned NOT NULL default '0',
  `equip_switch` int(11) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `char_id` (`char_id`)
) ENGINE=MyISAM;

--
-- Table structure for table `ipbanlist`
--

CREATE TABLE IF NOT EXISTS `ipbanlist` (
  `list` varchar(255) NOT NULL default '',
  `btime` datetime NOT NULL,
  `rtime` datetime NOT NULL,
  `reason` varchar(255) NOT NULL default '',
  KEY (`list`)
) ENGINE=MyISAM;

--
-- Table structure for table `login`
--

CREATE TABLE IF NOT EXISTS `login` (
  `account_id` int(11) unsigned NOT NULL auto_increment,
  `userid` varchar(23) NOT NULL default '',
  `user_pass` varchar(32) NOT NULL default '',
  `sex` enum('M','F','S') NOT NULL default 'M',
  `email` varchar(39) NOT NULL default '',
  `group_id` tinyint(3) NOT NULL default '0',
  `state` int(11) unsigned NOT NULL default '0',
  `unban_time` int(11) unsigned NOT NULL default '0',
  `expiration_time` int(11) unsigned NOT NULL default '0',
  `logincount` mediumint(9) unsigned NOT NULL default '0',
  `lastlogin` datetime,
  `last_ip` varchar(100) NOT NULL default '',
  `birthdate` DATE,
  `character_slots` tinyint(3) unsigned NOT NULL default '0',
  `pincode` varchar(4) NOT NULL DEFAULT '',
  `pincode_change` int(11) unsigned NOT NULL DEFAULT '0',
  `vip_time` int(11) unsigned NOT NULL default '0',
  `old_group` tinyint(3) NOT NULL default '0',
  PRIMARY KEY  (`account_id`),
  KEY `name` (`userid`)
) ENGINE=MyISAM AUTO_INCREMENT=2000000; 

-- added standard accounts for servers, VERY INSECURE!!!
-- inserted into the table called login which is above

INSERT INTO `login` (`account_id`, `userid`, `user_pass`, `sex`, `email`) VALUES ('1', 'n1', 'm1', 'S','athena@athena.com');

--
-- Table structure for table `mail`
--

CREATE TABLE IF NOT EXISTS `mail` (
  `id` bigint(20) unsigned NOT NULL auto_increment,
  `send_name` varchar(30) NOT NULL default '',
  `send_id` int(11) unsigned NOT NULL default '0',
  `dest_name` varchar(30) NOT NULL default '',
  `dest_id` int(11) unsigned NOT NULL default '0',
  `title` varchar(45) NOT NULL default '',
  `message` varchar(500) NOT NULL default '',
  `time` int(11) unsigned NOT NULL default '0',
  `status` tinyint(2) NOT NULL default '0',
  `zeny` int(11) unsigned NOT NULL default '0',
  `type` smallint(5) NOT NULL default '0',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM;

-- ----------------------------
-- Table structure for `mail_attachments`
-- ----------------------------

CREATE TABLE IF NOT EXISTS `mail_attachments` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `index` smallint(5) unsigned NOT NULL DEFAULT '0',
  `nameid` smallint(5) unsigned NOT NULL DEFAULT '0',
  `amount` int(11) unsigned NOT NULL DEFAULT '0',
  `refine` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `attribute` tinyint(4) unsigned NOT NULL DEFAULT '0',
  `identify` smallint(6) NOT NULL DEFAULT '0',
  `card0` smallint(5) unsigned NOT NULL DEFAULT '0',
  `card1` smallint(5) unsigned NOT NULL DEFAULT '0',
  `card2` smallint(5) unsigned NOT NULL DEFAULT '0',
  `card3` smallint(5) unsigned NOT NULL DEFAULT '0',
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
  `unique_id` bigint(20) unsigned NOT NULL DEFAULT '0',
  `bound` tinyint(1) unsigned NOT NULL DEFAULT '0',
    PRIMARY KEY (`id`,`index`)
) ENGINE=MyISAM;

--
-- Table structure for table `mapreg`
--

CREATE TABLE IF NOT EXISTS `mapreg` (
  `varname` varchar(32) binary NOT NULL,
  `index` int(11) unsigned NOT NULL default '0',
  `value` varchar(255) NOT NULL,
  PRIMARY KEY (`varname`,`index`)
) ENGINE=MyISAM;

--
-- Table `market` for market shop persistency
--

CREATE TABLE IF NOT EXISTS `market` (
  `name` varchar(50) NOT NULL DEFAULT '',
  `nameid` SMALLINT(5) UNSIGNED NOT NULL,
  `price` INT(11) UNSIGNED NOT NULL,
  `amount` SMALLINT(5) UNSIGNED NOT NULL,
  `flag` TINYINT(2) UNSIGNED NOT NULL DEFAULT '0',
  PRIMARY KEY  (`name`,`nameid`)
) ENGINE = MyISAM;

--
-- Table structure for table `memo`
--

CREATE TABLE IF NOT EXISTS `memo` (
  `memo_id` int(11) unsigned NOT NULL auto_increment,
  `char_id` int(11) unsigned NOT NULL default '0',
  `map` varchar(11) NOT NULL default '',
  `x` smallint(4) unsigned NOT NULL default '0',
  `y` smallint(4) unsigned NOT NULL default '0',
  PRIMARY KEY  (`memo_id`),
  KEY `char_id` (`char_id`)
) ENGINE=MyISAM;

--
-- Table structure for table `mercenary`
--

CREATE TABLE IF NOT EXISTS `mercenary` (
  `mer_id` int(11) unsigned NOT NULL auto_increment,
  `char_id` int(11) NOT NULL,
  `class` mediumint(9) unsigned NOT NULL default '0',
  `hp` int(11) unsigned NOT NULL default '0',
  `sp` int(11) unsigned NOT NULL default '0',
  `kill_counter` int(11) NOT NULL,
  `life_time` bigint(20) NOT NULL default '0',
  PRIMARY KEY  (`mer_id`)
) ENGINE=MyISAM;

--
-- Table structure for table `mercenary_owner`
--

CREATE TABLE IF NOT EXISTS `mercenary_owner` (
  `char_id` int(11) NOT NULL,
  `merc_id` int(11) NOT NULL default '0',
  `arch_calls` int(11) NOT NULL default '0',
  `arch_faith` int(11) NOT NULL default '0',
  `spear_calls` int(11) NOT NULL default '0',
  `spear_faith` int(11) NOT NULL default '0',
  `sword_calls` int(11) NOT NULL default '0',
  `sword_faith` int(11) NOT NULL default '0',
  PRIMARY KEY  (`char_id`)
) ENGINE=MyISAM;

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

--
-- Table structure for table `sc_data`
--

CREATE TABLE IF NOT EXISTS `sc_data` (
  `account_id` int(11) unsigned NOT NULL,
  `char_id` int(11) unsigned NOT NULL,
  `type` smallint(11) unsigned NOT NULL,
  `tick` bigint(20) NOT NULL,
  `val1` int(11) NOT NULL default '0',
  `val2` int(11) NOT NULL default '0',
  `val3` int(11) NOT NULL default '0',
  `val4` int(11) NOT NULL default '0',
  KEY (`account_id`),
  KEY (`char_id`)
) ENGINE=MyISAM;

--
-- Table structure for table `skillcooldown`
--

CREATE TABLE IF NOT EXISTS `skillcooldown` (
  `account_id` int(11) unsigned NOT NULL,
  `char_id` int(11) unsigned NOT NULL,
  `skill` smallint(11) unsigned NOT NULL DEFAULT '0',
  `tick` bigint(20) NOT NULL,
  KEY `account_id` (`account_id`),
  KEY `char_id` (`char_id`)
) ENGINE=MyISAM;

--
-- Table structure for table `party`
--

CREATE TABLE IF NOT EXISTS `party` (
  `party_id` int(11) unsigned NOT NULL auto_increment,
  `name` varchar(24) NOT NULL default '',
  `exp` tinyint(11) unsigned NOT NULL default '0',
  `item` tinyint(11) unsigned NOT NULL default '0',
  `leader_id` int(11) unsigned NOT NULL default '0',
  `leader_char` int(11) unsigned NOT NULL default '0',
  PRIMARY KEY  (`party_id`)
) ENGINE=MyISAM;

--
-- Table structure for table `pet`
--

CREATE TABLE IF NOT EXISTS `pet` (
  `pet_id` int(11) unsigned NOT NULL auto_increment,
  `class` mediumint(9) unsigned NOT NULL default '0',
  `name` varchar(24) NOT NULL default '',
  `account_id` int(11) unsigned NOT NULL default '0',
  `char_id` int(11) unsigned NOT NULL default '0',
  `level` smallint(4) unsigned NOT NULL default '0',
  `egg_id` smallint(5) unsigned NOT NULL default '0',
  `equip` mediumint(8) unsigned NOT NULL default '0',
  `intimate` smallint(9) unsigned NOT NULL default '0',
  `hungry` smallint(9) unsigned NOT NULL default '0',
  `rename_flag` tinyint(4) unsigned NOT NULL default '0',
  `incubate` int(11) unsigned NOT NULL default '0',
  PRIMARY KEY  (`pet_id`)
) ENGINE=MyISAM;

--
-- Table structure for table `quest`
--

CREATE TABLE IF NOT EXISTS `quest` (
  `char_id` int(11) unsigned NOT NULL default '0',
  `quest_id` int(10) unsigned NOT NULL,
  `state` enum('0','1','2') NOT NULL default '0',
  `time` int(11) unsigned NOT NULL default '0',
  `count1` mediumint(8) unsigned NOT NULL default '0',
  `count2` mediumint(8) unsigned NOT NULL default '0',
  `count3` mediumint(8) unsigned NOT NULL default '0',
  PRIMARY KEY  (`char_id`,`quest_id`)
) ENGINE=MyISAM;

--
-- Table structure for table `ragsrvinfo`
--

CREATE TABLE IF NOT EXISTS `ragsrvinfo` (
  `index` int(11) NOT NULL default '0',
  `name` varchar(255) NOT NULL default '',
  `exp` int(11) unsigned NOT NULL default '0',
  `jexp` int(11) unsigned NOT NULL default '0',
  `drop` int(11) unsigned NOT NULL default '0'
) ENGINE=MyISAM;

--
-- Table structure for table `skill`
--

CREATE TABLE IF NOT EXISTS `skill` (
  `char_id` int(11) unsigned NOT NULL default '0',
  `id` smallint(11) unsigned NOT NULL default '0',
  `lv` tinyint(4) unsigned NOT NULL default '0',
  `flag` TINYINT(1) UNSIGNED NOT NULL default 0,
  PRIMARY KEY  (`char_id`,`id`)
) ENGINE=MyISAM;

--
-- Table structure for table `skill_homunculus`
--

CREATE TABLE IF NOT EXISTS `skill_homunculus` (
  `homun_id` int(11) NOT NULL,
  `id` int(11) NOT NULL,
  `lv` smallint(6) NOT NULL,
  PRIMARY KEY  (`homun_id`,`id`)
) ENGINE=MyISAM;

--
-- Table structure for table `sstatus`
--

CREATE TABLE IF NOT EXISTS `sstatus` (
  `index` tinyint(4) unsigned NOT NULL default '0',
  `name` varchar(255) NOT NULL default '',
  `user` int(11) unsigned NOT NULL default '0'
) ENGINE=MyISAM;

--
-- Table structure for table `storage`
--

CREATE TABLE IF NOT EXISTS `storage` (
  `id` int(11) unsigned NOT NULL auto_increment,
  `account_id` int(11) unsigned NOT NULL default '0',
  `nameid` smallint(5) unsigned NOT NULL default '0',
  `amount` smallint(11) unsigned NOT NULL default '0',
  `equip` int(11) unsigned NOT NULL default '0',
  `identify` smallint(6) unsigned NOT NULL default '0',
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
  `bound` tinyint(3) unsigned NOT NULL default '0',
  `unique_id` bigint(20) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `account_id` (`account_id`)
) ENGINE=MyISAM;

--
-- Table structure for table `vending_items`
--

CREATE TABLE IF NOT EXISTS `vending_items` (
  `vending_id` int(10) unsigned NOT NULL,
  `index` smallint(5) unsigned NOT NULL,
  `cartinventory_id` int(10) unsigned NOT NULL,
  `amount` smallint(5) unsigned NOT NULL,
  `price` int(10) unsigned NOT NULL
) ENGINE=MyISAM;

--
-- Table structure for table `vendings`
--

CREATE TABLE IF NOT EXISTS `vendings` (
  `id` int(10) unsigned NOT NULL,
  `account_id` int(11) unsigned NOT NULL,
  `char_id` int(10) unsigned NOT NULL,
  `sex` enum('F','M') NOT NULL DEFAULT 'M',
  `map` varchar(20) NOT NULL,
  `x` smallint(5) unsigned NOT NULL,
  `y` smallint(5) unsigned NOT NULL,
  `title` varchar(80) NOT NULL,
  `body_direction` CHAR( 1 ) NOT NULL DEFAULT '4',
  `head_direction` CHAR( 1 ) NOT NULL DEFAULT '0',
  `sit` CHAR( 1 ) NOT NULL DEFAULT '1',
  `autotrade` tinyint(4) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM;

-- ---------------------------------------------------------------
--                  自定义数据库（GF头饰制作）                  --
-- ---------------------------------------------------------------

SET FOREIGN_KEY_CHECKS=0;
-- ----------------------------
-- Table structure for `item_lv_1`
-- ----------------------------
DROP TABLE IF EXISTS `item_lv_1`;
CREATE TABLE `item_lv_1` (
  `item_id` int(10) NOT NULL default '0'
) ENGINE=MyISAM;

-- ----------------------------
-- Records of item_lv_1
-- ----------------------------
INSERT INTO `item_lv_1` VALUES ('5336');
INSERT INTO `item_lv_1` VALUES ('5337');
INSERT INTO `item_lv_1` VALUES ('5339');
INSERT INTO `item_lv_1` VALUES ('5163');
INSERT INTO `item_lv_1` VALUES ('5166');
INSERT INTO `item_lv_1` VALUES ('5172');
INSERT INTO `item_lv_1` VALUES ('5178');
INSERT INTO `item_lv_1` VALUES ('5812');
INSERT INTO `item_lv_1` VALUES ('2246');
INSERT INTO `item_lv_1` VALUES ('2252');
INSERT INTO `item_lv_1` VALUES ('5066');
INSERT INTO `item_lv_1` VALUES ('5072');
INSERT INTO `item_lv_1` VALUES ('5157');
INSERT INTO `item_lv_1` VALUES ('5685');
INSERT INTO `item_lv_1` VALUES ('5831');
INSERT INTO `item_lv_1` VALUES ('5837');
INSERT INTO `item_lv_1` VALUES ('2206');
INSERT INTO `item_lv_1` VALUES ('2209');
INSERT INTO `item_lv_1` VALUES ('2213');
INSERT INTO `item_lv_1` VALUES ('2231');
INSERT INTO `item_lv_1` VALUES ('2233');
INSERT INTO `item_lv_1` VALUES ('2234');
INSERT INTO `item_lv_1` VALUES ('2235');
INSERT INTO `item_lv_1` VALUES ('2236');
INSERT INTO `item_lv_1` VALUES ('2242');
INSERT INTO `item_lv_1` VALUES ('2243');
INSERT INTO `item_lv_1` VALUES ('2244');
INSERT INTO `item_lv_1` VALUES ('2245');
INSERT INTO `item_lv_1` VALUES ('2247');
INSERT INTO `item_lv_1` VALUES ('2248');
INSERT INTO `item_lv_1` VALUES ('2249');
INSERT INTO `item_lv_1` VALUES ('2250');
INSERT INTO `item_lv_1` VALUES ('2251');
INSERT INTO `item_lv_1` VALUES ('2253');
INSERT INTO `item_lv_1` VALUES ('2267');
INSERT INTO `item_lv_1` VALUES ('2268');
INSERT INTO `item_lv_1` VALUES ('2269');
INSERT INTO `item_lv_1` VALUES ('2270');
INSERT INTO `item_lv_1` VALUES ('2272');
INSERT INTO `item_lv_1` VALUES ('2273');
INSERT INTO `item_lv_1` VALUES ('2274');
INSERT INTO `item_lv_1` VALUES ('2277');
INSERT INTO `item_lv_1` VALUES ('2287');
INSERT INTO `item_lv_1` VALUES ('2289');
INSERT INTO `item_lv_1` VALUES ('2291');
INSERT INTO `item_lv_1` VALUES ('2292');
INSERT INTO `item_lv_1` VALUES ('2296');
INSERT INTO `item_lv_1` VALUES ('2299');
INSERT INTO `item_lv_1` VALUES ('5004');
INSERT INTO `item_lv_1` VALUES ('5005');
INSERT INTO `item_lv_1` VALUES ('5009');
INSERT INTO `item_lv_1` VALUES ('5031');
INSERT INTO `item_lv_1` VALUES ('5046');
INSERT INTO `item_lv_1` VALUES ('5053');
INSERT INTO `item_lv_1` VALUES ('5080');
INSERT INTO `item_lv_1` VALUES ('5114');
INSERT INTO `item_lv_1` VALUES ('5116');
INSERT INTO `item_lv_1` VALUES ('5141');
INSERT INTO `item_lv_1` VALUES ('5175');
INSERT INTO `item_lv_1` VALUES ('5687');
INSERT INTO `item_lv_1` VALUES ('5816');
INSERT INTO `item_lv_1` VALUES ('5830');
INSERT INTO `item_lv_1` VALUES ('5838');
INSERT INTO `item_lv_1` VALUES ('2205');
INSERT INTO `item_lv_1` VALUES ('2210');
INSERT INTO `item_lv_1` VALUES ('2211');
INSERT INTO `item_lv_1` VALUES ('2212');
INSERT INTO `item_lv_1` VALUES ('2217');
INSERT INTO `item_lv_1` VALUES ('2219');
INSERT INTO `item_lv_1` VALUES ('2221');
INSERT INTO `item_lv_1` VALUES ('2225');
INSERT INTO `item_lv_1` VALUES ('2227');
INSERT INTO `item_lv_1` VALUES ('2229');
INSERT INTO `item_lv_1` VALUES ('2237');
INSERT INTO `item_lv_1` VALUES ('2239');
INSERT INTO `item_lv_1` VALUES ('2240');
INSERT INTO `item_lv_1` VALUES ('2257');
INSERT INTO `item_lv_1` VALUES ('2261');
INSERT INTO `item_lv_1` VALUES ('2262');
INSERT INTO `item_lv_1` VALUES ('2263');
INSERT INTO `item_lv_1` VALUES ('2271');
INSERT INTO `item_lv_1` VALUES ('2275');
INSERT INTO `item_lv_1` VALUES ('2276');
INSERT INTO `item_lv_1` VALUES ('2279');
INSERT INTO `item_lv_1` VALUES ('2281');
INSERT INTO `item_lv_1` VALUES ('2288');
INSERT INTO `item_lv_1` VALUES ('2290');
INSERT INTO `item_lv_1` VALUES ('2293');
INSERT INTO `item_lv_1` VALUES ('2294');
INSERT INTO `item_lv_1` VALUES ('2297');
INSERT INTO `item_lv_1` VALUES ('2298');
INSERT INTO `item_lv_1` VALUES ('5006');
INSERT INTO `item_lv_1` VALUES ('5008');
INSERT INTO `item_lv_1` VALUES ('5010');
INSERT INTO `item_lv_1` VALUES ('5011');
INSERT INTO `item_lv_1` VALUES ('5012');
INSERT INTO `item_lv_1` VALUES ('5014');
INSERT INTO `item_lv_1` VALUES ('5015');
INSERT INTO `item_lv_1` VALUES ('5024');
INSERT INTO `item_lv_1` VALUES ('5028');
INSERT INTO `item_lv_1` VALUES ('5033');
INSERT INTO `item_lv_1` VALUES ('5034');
INSERT INTO `item_lv_1` VALUES ('5035');
INSERT INTO `item_lv_1` VALUES ('5036');
INSERT INTO `item_lv_1` VALUES ('5037');
INSERT INTO `item_lv_1` VALUES ('5042');
INSERT INTO `item_lv_1` VALUES ('5043');
INSERT INTO `item_lv_1` VALUES ('5050');
INSERT INTO `item_lv_1` VALUES ('5052');
INSERT INTO `item_lv_1` VALUES ('5054');
INSERT INTO `item_lv_1` VALUES ('5055');
INSERT INTO `item_lv_1` VALUES ('5087');
INSERT INTO `item_lv_1` VALUES ('5088');
INSERT INTO `item_lv_1` VALUES ('5089');
INSERT INTO `item_lv_1` VALUES ('5092');
INSERT INTO `item_lv_1` VALUES ('5096');
INSERT INTO `item_lv_1` VALUES ('5118');
INSERT INTO `item_lv_1` VALUES ('5120');
INSERT INTO `item_lv_1` VALUES ('5134');
INSERT INTO `item_lv_1` VALUES ('5154');
INSERT INTO `item_lv_1` VALUES ('5155');
INSERT INTO `item_lv_1` VALUES ('5156');
INSERT INTO `item_lv_1` VALUES ('5375');
INSERT INTO `item_lv_1` VALUES ('5804');
INSERT INTO `item_lv_1` VALUES ('5806');
INSERT INTO `item_lv_1` VALUES ('5848');
INSERT INTO `item_lv_1` VALUES ('5849');
INSERT INTO `item_lv_1` VALUES ('5338');
INSERT INTO `item_lv_1` VALUES ('5346');
INSERT INTO `item_lv_1` VALUES ('2238');
INSERT INTO `item_lv_1` VALUES ('2241');

-- ----------------------------
-- Table structure for `item_lv_2`
-- ----------------------------
DROP TABLE IF EXISTS `item_lv_2`;
CREATE TABLE `item_lv_2` (
  `item_id` int(10) NOT NULL default '0'
) ENGINE=MyISAM;

-- ----------------------------
-- Records of item_lv_2
-- ----------------------------
INSERT INTO `item_lv_2` VALUES ('5104');
INSERT INTO `item_lv_2` VALUES ('5125');
INSERT INTO `item_lv_2` VALUES ('5159');
INSERT INTO `item_lv_2` VALUES ('5160');
INSERT INTO `item_lv_2` VALUES ('5162');
INSERT INTO `item_lv_2` VALUES ('5204');
INSERT INTO `item_lv_2` VALUES ('5315');
INSERT INTO `item_lv_2` VALUES ('5407');
INSERT INTO `item_lv_2` VALUES ('5448');
INSERT INTO `item_lv_2` VALUES ('5449');
INSERT INTO `item_lv_2` VALUES ('5847');
INSERT INTO `item_lv_2` VALUES ('5081');
INSERT INTO `item_lv_2` VALUES ('5098');
INSERT INTO `item_lv_2` VALUES ('5100');
INSERT INTO `item_lv_2` VALUES ('5131');
INSERT INTO `item_lv_2` VALUES ('5144');
INSERT INTO `item_lv_2` VALUES ('5173');
INSERT INTO `item_lv_2` VALUES ('5174');
INSERT INTO `item_lv_2` VALUES ('5177');
INSERT INTO `item_lv_2` VALUES ('5191');
INSERT INTO `item_lv_2` VALUES ('5192');
INSERT INTO `item_lv_2` VALUES ('5193');
INSERT INTO `item_lv_2` VALUES ('5194');
INSERT INTO `item_lv_2` VALUES ('5195');
INSERT INTO `item_lv_2` VALUES ('5196');
INSERT INTO `item_lv_2` VALUES ('5197');
INSERT INTO `item_lv_2` VALUES ('5243');
INSERT INTO `item_lv_2` VALUES ('5259');
INSERT INTO `item_lv_2` VALUES ('5399');
INSERT INTO `item_lv_2` VALUES ('5474');
INSERT INTO `item_lv_2` VALUES ('5478');
INSERT INTO `item_lv_2` VALUES ('5519');
INSERT INTO `item_lv_2` VALUES ('5615');
INSERT INTO `item_lv_2` VALUES ('5683');
INSERT INTO `item_lv_2` VALUES ('5684');
INSERT INTO `item_lv_2` VALUES ('5799');
INSERT INTO `item_lv_2` VALUES ('5802');
INSERT INTO `item_lv_2` VALUES ('2254');
INSERT INTO `item_lv_2` VALUES ('2255');
INSERT INTO `item_lv_2` VALUES ('5027');
INSERT INTO `item_lv_2` VALUES ('5045');
INSERT INTO `item_lv_2` VALUES ('5068');
INSERT INTO `item_lv_2` VALUES ('5093');
INSERT INTO `item_lv_2` VALUES ('5109');
INSERT INTO `item_lv_2` VALUES ('5122');
INSERT INTO `item_lv_2` VALUES ('5143');
INSERT INTO `item_lv_2` VALUES ('5148');
INSERT INTO `item_lv_2` VALUES ('5158');
INSERT INTO `item_lv_2` VALUES ('5161');
INSERT INTO `item_lv_2` VALUES ('5190');
INSERT INTO `item_lv_2` VALUES ('5302');
INSERT INTO `item_lv_2` VALUES ('5347');
INSERT INTO `item_lv_2` VALUES ('5348');
INSERT INTO `item_lv_2` VALUES ('5350');
INSERT INTO `item_lv_2` VALUES ('5351');
INSERT INTO `item_lv_2` VALUES ('5354');
INSERT INTO `item_lv_2` VALUES ('5355');
INSERT INTO `item_lv_2` VALUES ('5392');
INSERT INTO `item_lv_2` VALUES ('5414');
INSERT INTO `item_lv_2` VALUES ('5426');
INSERT INTO `item_lv_2` VALUES ('5427');
INSERT INTO `item_lv_2` VALUES ('5473');
INSERT INTO `item_lv_2` VALUES ('5530');
INSERT INTO `item_lv_2` VALUES ('5539');
INSERT INTO `item_lv_2` VALUES ('5578');
INSERT INTO `item_lv_2` VALUES ('5668');
INSERT INTO `item_lv_2` VALUES ('5682');
INSERT INTO `item_lv_2` VALUES ('5798');
INSERT INTO `item_lv_2` VALUES ('5811');
INSERT INTO `item_lv_2` VALUES ('5813');
INSERT INTO `item_lv_2` VALUES ('5820');
INSERT INTO `item_lv_2` VALUES ('5821');
INSERT INTO `item_lv_2` VALUES ('5833');
INSERT INTO `item_lv_2` VALUES ('5835');
INSERT INTO `item_lv_2` VALUES ('2214');
INSERT INTO `item_lv_2` VALUES ('2256');
INSERT INTO `item_lv_2` VALUES ('2258');
INSERT INTO `item_lv_2` VALUES ('2259');
INSERT INTO `item_lv_2` VALUES ('2260');
INSERT INTO `item_lv_2` VALUES ('2264');
INSERT INTO `item_lv_2` VALUES ('2265');
INSERT INTO `item_lv_2` VALUES ('2266');
INSERT INTO `item_lv_2` VALUES ('2280');
INSERT INTO `item_lv_2` VALUES ('2282');
INSERT INTO `item_lv_2` VALUES ('2283');
INSERT INTO `item_lv_2` VALUES ('2284');
INSERT INTO `item_lv_2` VALUES ('2285');
INSERT INTO `item_lv_2` VALUES ('2286');
INSERT INTO `item_lv_2` VALUES ('2295');
INSERT INTO `item_lv_2` VALUES ('5001');
INSERT INTO `item_lv_2` VALUES ('5002');
INSERT INTO `item_lv_2` VALUES ('5003');
INSERT INTO `item_lv_2` VALUES ('5007');
INSERT INTO `item_lv_2` VALUES ('5017');
INSERT INTO `item_lv_2` VALUES ('5018');
INSERT INTO `item_lv_2` VALUES ('5026');
INSERT INTO `item_lv_2` VALUES ('5029');
INSERT INTO `item_lv_2` VALUES ('5030');
INSERT INTO `item_lv_2` VALUES ('5062');
INSERT INTO `item_lv_2` VALUES ('5071');
INSERT INTO `item_lv_2` VALUES ('5073');
INSERT INTO `item_lv_2` VALUES ('5076');
INSERT INTO `item_lv_2` VALUES ('5084');
INSERT INTO `item_lv_2` VALUES ('5101');
INSERT INTO `item_lv_2` VALUES ('5113');
INSERT INTO `item_lv_2` VALUES ('5146');
INSERT INTO `item_lv_2` VALUES ('5167');
INSERT INTO `item_lv_2` VALUES ('5168');
INSERT INTO `item_lv_2` VALUES ('5180');
INSERT INTO `item_lv_2` VALUES ('5207');
INSERT INTO `item_lv_2` VALUES ('5300');
INSERT INTO `item_lv_2` VALUES ('5317');
INSERT INTO `item_lv_2` VALUES ('5371');
INSERT INTO `item_lv_2` VALUES ('5505');
INSERT INTO `item_lv_2` VALUES ('5523');
INSERT INTO `item_lv_2` VALUES ('5611');
INSERT INTO `item_lv_2` VALUES ('5686');
INSERT INTO `item_lv_2` VALUES ('5741');
INSERT INTO `item_lv_2` VALUES ('5797');
INSERT INTO `item_lv_2` VALUES ('5803');
INSERT INTO `item_lv_2` VALUES ('5814');
INSERT INTO `item_lv_2` VALUES ('5832');
INSERT INTO `item_lv_2` VALUES ('5836');
INSERT INTO `item_lv_2` VALUES ('5839');
INSERT INTO `item_lv_2` VALUES ('2207');
INSERT INTO `item_lv_2` VALUES ('2215');
INSERT INTO `item_lv_2` VALUES ('2223');
INSERT INTO `item_lv_2` VALUES ('2278');
INSERT INTO `item_lv_2` VALUES ('5023');
INSERT INTO `item_lv_2` VALUES ('5039');
INSERT INTO `item_lv_2` VALUES ('5047');
INSERT INTO `item_lv_2` VALUES ('5049');
INSERT INTO `item_lv_2` VALUES ('5056');
INSERT INTO `item_lv_2` VALUES ('5057');
INSERT INTO `item_lv_2` VALUES ('5059');
INSERT INTO `item_lv_2` VALUES ('5061');
INSERT INTO `item_lv_2` VALUES ('5063');
INSERT INTO `item_lv_2` VALUES ('5064');
INSERT INTO `item_lv_2` VALUES ('5077');
INSERT INTO `item_lv_2` VALUES ('5079');
INSERT INTO `item_lv_2` VALUES ('5082');
INSERT INTO `item_lv_2` VALUES ('5090');
INSERT INTO `item_lv_2` VALUES ('5189');
INSERT INTO `item_lv_2` VALUES ('5206');
INSERT INTO `item_lv_2` VALUES ('5209');
INSERT INTO `item_lv_2` VALUES ('5289');
INSERT INTO `item_lv_2` VALUES ('5370');
INSERT INTO `item_lv_2` VALUES ('5444');
INSERT INTO `item_lv_2` VALUES ('5450');
INSERT INTO `item_lv_2` VALUES ('5472');
INSERT INTO `item_lv_2` VALUES ('5499');
INSERT INTO `item_lv_2` VALUES ('5583');
INSERT INTO `item_lv_2` VALUES ('5792');
INSERT INTO `item_lv_2` VALUES ('5796');
INSERT INTO `item_lv_2` VALUES ('5807');
INSERT INTO `item_lv_2` VALUES ('5112');
INSERT INTO `item_lv_2` VALUES ('5265');
INSERT INTO `item_lv_2` VALUES ('5293');
INSERT INTO `item_lv_2` VALUES ('5834');

-- ----------------------------
-- Table structure for `item_lv_3`
-- ----------------------------
DROP TABLE IF EXISTS `item_lv_3`;
CREATE TABLE `item_lv_3` (
  `item_id` int(10) NOT NULL default '0'
) ENGINE=MyISAM;

-- ----------------------------
-- Records of item_lv_3
-- ----------------------------
INSERT INTO `item_lv_3` VALUES ('5097');
INSERT INTO `item_lv_3` VALUES ('5105');
INSERT INTO `item_lv_3` VALUES ('5124');
INSERT INTO `item_lv_3` VALUES ('5145');
INSERT INTO `item_lv_3` VALUES ('5151');
INSERT INTO `item_lv_3` VALUES ('5216');
INSERT INTO `item_lv_3` VALUES ('5238');
INSERT INTO `item_lv_3` VALUES ('5239');
INSERT INTO `item_lv_3` VALUES ('5240');
INSERT INTO `item_lv_3` VALUES ('5241');
INSERT INTO `item_lv_3` VALUES ('5312');
INSERT INTO `item_lv_3` VALUES ('5385');
INSERT INTO `item_lv_3` VALUES ('5435');
INSERT INTO `item_lv_3` VALUES ('5442');
INSERT INTO `item_lv_3` VALUES ('5491');
INSERT INTO `item_lv_3` VALUES ('5533');
INSERT INTO `item_lv_3` VALUES ('5566');
INSERT INTO `item_lv_3` VALUES ('5133');
INSERT INTO `item_lv_3` VALUES ('5169');
INSERT INTO `item_lv_3` VALUES ('5183');
INSERT INTO `item_lv_3` VALUES ('5217');
INSERT INTO `item_lv_3` VALUES ('5220');
INSERT INTO `item_lv_3` VALUES ('5222');
INSERT INTO `item_lv_3` VALUES ('5235');
INSERT INTO `item_lv_3` VALUES ('5236');
INSERT INTO `item_lv_3` VALUES ('5237');
INSERT INTO `item_lv_3` VALUES ('5260');
INSERT INTO `item_lv_3` VALUES ('5285');
INSERT INTO `item_lv_3` VALUES ('5437');
INSERT INTO `item_lv_3` VALUES ('5458');
INSERT INTO `item_lv_3` VALUES ('5461');
INSERT INTO `item_lv_3` VALUES ('5470');
INSERT INTO `item_lv_3` VALUES ('5475');
INSERT INTO `item_lv_3` VALUES ('5525');
INSERT INTO `item_lv_3` VALUES ('5526');
INSERT INTO `item_lv_3` VALUES ('5537');
INSERT INTO `item_lv_3` VALUES ('5538');
INSERT INTO `item_lv_3` VALUES ('5579');
INSERT INTO `item_lv_3` VALUES ('5763');
INSERT INTO `item_lv_3` VALUES ('5788');
INSERT INTO `item_lv_3` VALUES ('5815');
INSERT INTO `item_lv_3` VALUES ('5025');
INSERT INTO `item_lv_3` VALUES ('5074');
INSERT INTO `item_lv_3` VALUES ('5078');
INSERT INTO `item_lv_3` VALUES ('5102');
INSERT INTO `item_lv_3` VALUES ('5103');
INSERT INTO `item_lv_3` VALUES ('5108');
INSERT INTO `item_lv_3` VALUES ('5110');
INSERT INTO `item_lv_3` VALUES ('5164');
INSERT INTO `item_lv_3` VALUES ('5165');
INSERT INTO `item_lv_3` VALUES ('5176');
INSERT INTO `item_lv_3` VALUES ('5186');
INSERT INTO `item_lv_3` VALUES ('5202');
INSERT INTO `item_lv_3` VALUES ('5203');
INSERT INTO `item_lv_3` VALUES ('5218');
INSERT INTO `item_lv_3` VALUES ('5221');
INSERT INTO `item_lv_3` VALUES ('5252');
INSERT INTO `item_lv_3` VALUES ('5270');
INSERT INTO `item_lv_3` VALUES ('5272');
INSERT INTO `item_lv_3` VALUES ('5291');
INSERT INTO `item_lv_3` VALUES ('5296');
INSERT INTO `item_lv_3` VALUES ('5365');
INSERT INTO `item_lv_3` VALUES ('5383');
INSERT INTO `item_lv_3` VALUES ('5387');
INSERT INTO `item_lv_3` VALUES ('5397');
INSERT INTO `item_lv_3` VALUES ('5424');
INSERT INTO `item_lv_3` VALUES ('5425');
INSERT INTO `item_lv_3` VALUES ('5438');
INSERT INTO `item_lv_3` VALUES ('5439');
INSERT INTO `item_lv_3` VALUES ('5440');
INSERT INTO `item_lv_3` VALUES ('5441');
INSERT INTO `item_lv_3` VALUES ('5455');
INSERT INTO `item_lv_3` VALUES ('5465');
INSERT INTO `item_lv_3` VALUES ('5476');
INSERT INTO `item_lv_3` VALUES ('5496');
INSERT INTO `item_lv_3` VALUES ('5501');
INSERT INTO `item_lv_3` VALUES ('5573');
INSERT INTO `item_lv_3` VALUES ('5767');
INSERT INTO `item_lv_3` VALUES ('5016');
INSERT INTO `item_lv_3` VALUES ('5019');
INSERT INTO `item_lv_3` VALUES ('5032');
INSERT INTO `item_lv_3` VALUES ('5040');
INSERT INTO `item_lv_3` VALUES ('5060');
INSERT INTO `item_lv_3` VALUES ('5065');
INSERT INTO `item_lv_3` VALUES ('5067');
INSERT INTO `item_lv_3` VALUES ('5070');
INSERT INTO `item_lv_3` VALUES ('5075');
INSERT INTO `item_lv_3` VALUES ('5083');
INSERT INTO `item_lv_3` VALUES ('5085');
INSERT INTO `item_lv_3` VALUES ('5115');
INSERT INTO `item_lv_3` VALUES ('5117');
INSERT INTO `item_lv_3` VALUES ('5135');
INSERT INTO `item_lv_3` VALUES ('5139');
INSERT INTO `item_lv_3` VALUES ('5147');
INSERT INTO `item_lv_3` VALUES ('5258');
INSERT INTO `item_lv_3` VALUES ('5263');
INSERT INTO `item_lv_3` VALUES ('5299');
INSERT INTO `item_lv_3` VALUES ('5367');
INSERT INTO `item_lv_3` VALUES ('5402');
INSERT INTO `item_lv_3` VALUES ('5403');
INSERT INTO `item_lv_3` VALUES ('5413');
INSERT INTO `item_lv_3` VALUES ('5447');
INSERT INTO `item_lv_3` VALUES ('5667');
INSERT INTO `item_lv_3` VALUES ('5671');
INSERT INTO `item_lv_3` VALUES ('5742');
INSERT INTO `item_lv_3` VALUES ('2202');
INSERT INTO `item_lv_3` VALUES ('2204');
INSERT INTO `item_lv_3` VALUES ('5041');
INSERT INTO `item_lv_3` VALUES ('5048');
INSERT INTO `item_lv_3` VALUES ('5200');
INSERT INTO `item_lv_3` VALUES ('5316');
INSERT INTO `item_lv_3` VALUES ('5405');
INSERT INTO `item_lv_3` VALUES ('5422');
INSERT INTO `item_lv_3` VALUES ('5445');
INSERT INTO `item_lv_3` VALUES ('5483');
INSERT INTO `item_lv_3` VALUES ('5532');
INSERT INTO `item_lv_3` VALUES ('5556');
INSERT INTO `item_lv_3` VALUES ('5769');
INSERT INTO `item_lv_3` VALUES ('5850');
INSERT INTO `item_lv_3` VALUES ('5121');
INSERT INTO `item_lv_3` VALUES ('5123');
INSERT INTO `item_lv_3` VALUES ('5266');
INSERT INTO `item_lv_3` VALUES ('5398');
INSERT INTO `item_lv_3` VALUES ('5119');
INSERT INTO `item_lv_3` VALUES ('5268');
INSERT INTO `item_lv_3` VALUES ('5095');
INSERT INTO `item_lv_3` VALUES ('5591');

-- ----------------------------
-- Table structure for `item_lv_4`
-- ----------------------------
DROP TABLE IF EXISTS `item_lv_4`;
CREATE TABLE `item_lv_4` (
  `item_id` int(10) NOT NULL default '0'
) ENGINE=MyISAM;

-- ----------------------------
-- Records of item_lv_4
-- ----------------------------
INSERT INTO `item_lv_4` VALUES ('5094');
INSERT INTO `item_lv_4` VALUES ('5111');
INSERT INTO `item_lv_4` VALUES ('5150');
INSERT INTO `item_lv_4` VALUES ('5182');
INSERT INTO `item_lv_4` VALUES ('5227');
INSERT INTO `item_lv_4` VALUES ('5228');
INSERT INTO `item_lv_4` VALUES ('5229');
INSERT INTO `item_lv_4` VALUES ('5242');
INSERT INTO `item_lv_4` VALUES ('5261');
INSERT INTO `item_lv_4` VALUES ('5262');
INSERT INTO `item_lv_4` VALUES ('5284');
INSERT INTO `item_lv_4` VALUES ('5313');
INSERT INTO `item_lv_4` VALUES ('5409');
INSERT INTO `item_lv_4` VALUES ('5417');
INSERT INTO `item_lv_4` VALUES ('5430');
INSERT INTO `item_lv_4` VALUES ('5502');
INSERT INTO `item_lv_4` VALUES ('5506');
INSERT INTO `item_lv_4` VALUES ('5507');
INSERT INTO `item_lv_4` VALUES ('5557');
INSERT INTO `item_lv_4` VALUES ('5765');
INSERT INTO `item_lv_4` VALUES ('5852');
INSERT INTO `item_lv_4` VALUES ('5058');
INSERT INTO `item_lv_4` VALUES ('5138');
INSERT INTO `item_lv_4` VALUES ('5181');
INSERT INTO `item_lv_4` VALUES ('5188');
INSERT INTO `item_lv_4` VALUES ('5230');
INSERT INTO `item_lv_4` VALUES ('5231');
INSERT INTO `item_lv_4` VALUES ('5232');
INSERT INTO `item_lv_4` VALUES ('5233');
INSERT INTO `item_lv_4` VALUES ('5234');
INSERT INTO `item_lv_4` VALUES ('5253');
INSERT INTO `item_lv_4` VALUES ('5292');
INSERT INTO `item_lv_4` VALUES ('5404');
INSERT INTO `item_lv_4` VALUES ('5411');
INSERT INTO `item_lv_4` VALUES ('5429');
INSERT INTO `item_lv_4` VALUES ('5436');
INSERT INTO `item_lv_4` VALUES ('5531');
INSERT INTO `item_lv_4` VALUES ('5555');
INSERT INTO `item_lv_4` VALUES ('5655');
INSERT INTO `item_lv_4` VALUES ('5805');
INSERT INTO `item_lv_4` VALUES ('5038');
INSERT INTO `item_lv_4` VALUES ('5069');
INSERT INTO `item_lv_4` VALUES ('5086');
INSERT INTO `item_lv_4` VALUES ('5129');
INSERT INTO `item_lv_4` VALUES ('5142');
INSERT INTO `item_lv_4` VALUES ('5149');
INSERT INTO `item_lv_4` VALUES ('5184');
INSERT INTO `item_lv_4` VALUES ('5185');
INSERT INTO `item_lv_4` VALUES ('5205');
INSERT INTO `item_lv_4` VALUES ('5223');
INSERT INTO `item_lv_4` VALUES ('5288');
INSERT INTO `item_lv_4` VALUES ('5294');
INSERT INTO `item_lv_4` VALUES ('5303');
INSERT INTO `item_lv_4` VALUES ('5307');
INSERT INTO `item_lv_4` VALUES ('5332');
INSERT INTO `item_lv_4` VALUES ('5349');
INSERT INTO `item_lv_4` VALUES ('5401');
INSERT INTO `item_lv_4` VALUES ('5410');
INSERT INTO `item_lv_4` VALUES ('5416');
INSERT INTO `item_lv_4` VALUES ('5446');
INSERT INTO `item_lv_4` VALUES ('5548');
INSERT INTO `item_lv_4` VALUES ('5572');
INSERT INTO `item_lv_4` VALUES ('5574');
INSERT INTO `item_lv_4` VALUES ('5575');
INSERT INTO `item_lv_4` VALUES ('5586');
INSERT INTO `item_lv_4` VALUES ('5775');
INSERT INTO `item_lv_4` VALUES ('5827');
INSERT INTO `item_lv_4` VALUES ('5840');
INSERT INTO `item_lv_4` VALUES ('5020');
INSERT INTO `item_lv_4` VALUES ('5021');
INSERT INTO `item_lv_4` VALUES ('5091');
INSERT INTO `item_lv_4` VALUES ('5153');
INSERT INTO `item_lv_4` VALUES ('5179');
INSERT INTO `item_lv_4` VALUES ('5563');
INSERT INTO `item_lv_4` VALUES ('5380');
INSERT INTO `item_lv_4` VALUES ('5127');
INSERT INTO `item_lv_4` VALUES ('5128');
INSERT INTO `item_lv_4` VALUES ('5215');
INSERT INTO `item_lv_4` VALUES ('5267');
INSERT INTO `item_lv_4` VALUES ('5508');
INSERT INTO `item_lv_4` VALUES ('5509');
INSERT INTO `item_lv_4` VALUES ('5136');
INSERT INTO `item_lv_4` VALUES ('5254');
INSERT INTO `item_lv_4` VALUES ('5358');
INSERT INTO `item_lv_4` VALUES ('5601');
INSERT INTO `item_lv_4` VALUES ('5656');
INSERT INTO `item_lv_4` VALUES ('5673');
INSERT INTO `item_lv_4` VALUES ('5675');
INSERT INTO `item_lv_4` VALUES ('5679');
INSERT INTO `item_lv_4` VALUES ('5680');
INSERT INTO `item_lv_4` VALUES ('5737');
INSERT INTO `item_lv_4` VALUES ('5789');

-- ----------------------------
-- Table structure for `item_lv_5`
-- ----------------------------
DROP TABLE IF EXISTS `item_lv_5`;
CREATE TABLE `item_lv_5` (
  `item_id` int(10) NOT NULL default '0'
) ENGINE=MyISAM;

-- ----------------------------
-- Records of item_lv_5
-- ----------------------------
INSERT INTO `item_lv_5` VALUES ('5099');
INSERT INTO `item_lv_5` VALUES ('5126');
INSERT INTO `item_lv_5` VALUES ('5130');
INSERT INTO `item_lv_5` VALUES ('5137');
INSERT INTO `item_lv_5` VALUES ('5170');
INSERT INTO `item_lv_5` VALUES ('5212');
INSERT INTO `item_lv_5` VALUES ('5219');
INSERT INTO `item_lv_5` VALUES ('5226');
INSERT INTO `item_lv_5` VALUES ('5308');
INSERT INTO `item_lv_5` VALUES ('5324');
INSERT INTO `item_lv_5` VALUES ('5389');
INSERT INTO `item_lv_5` VALUES ('5415');
INSERT INTO `item_lv_5` VALUES ('5516');
INSERT INTO `item_lv_5` VALUES ('5517');
INSERT INTO `item_lv_5` VALUES ('5547');
INSERT INTO `item_lv_5` VALUES ('5610');
INSERT INTO `item_lv_5` VALUES ('5690');
INSERT INTO `item_lv_5` VALUES ('5856');
INSERT INTO `item_lv_5` VALUES ('5152');
INSERT INTO `item_lv_5` VALUES ('5187');
INSERT INTO `item_lv_5` VALUES ('5199');
INSERT INTO `item_lv_5` VALUES ('5208');
INSERT INTO `item_lv_5` VALUES ('5286');
INSERT INTO `item_lv_5` VALUES ('5384');
INSERT INTO `item_lv_5` VALUES ('5434');
INSERT INTO `item_lv_5` VALUES ('5500');
INSERT INTO `item_lv_5` VALUES ('5584');
INSERT INTO `item_lv_5` VALUES ('5140');
INSERT INTO `item_lv_5` VALUES ('5198');
INSERT INTO `item_lv_5` VALUES ('5255');
INSERT INTO `item_lv_5` VALUES ('5257');
INSERT INTO `item_lv_5` VALUES ('5277');
INSERT INTO `item_lv_5` VALUES ('5283');
INSERT INTO `item_lv_5` VALUES ('5287');
INSERT INTO `item_lv_5` VALUES ('5290');
INSERT INTO `item_lv_5` VALUES ('5304');
INSERT INTO `item_lv_5` VALUES ('5333');
INSERT INTO `item_lv_5` VALUES ('5335');
INSERT INTO `item_lv_5` VALUES ('5378');
INSERT INTO `item_lv_5` VALUES ('5381');
INSERT INTO `item_lv_5` VALUES ('5443');
INSERT INTO `item_lv_5` VALUES ('5480');
INSERT INTO `item_lv_5` VALUES ('5490');
INSERT INTO `item_lv_5` VALUES ('5524');
INSERT INTO `item_lv_5` VALUES ('5527');
INSERT INTO `item_lv_5` VALUES ('5528');
INSERT INTO `item_lv_5` VALUES ('5554');
INSERT INTO `item_lv_5` VALUES ('5658');
INSERT INTO `item_lv_5` VALUES ('5659');
INSERT INTO `item_lv_5` VALUES ('5660');
INSERT INTO `item_lv_5` VALUES ('5790');
INSERT INTO `item_lv_5` VALUES ('5824');
INSERT INTO `item_lv_5` VALUES ('5845');
INSERT INTO `item_lv_5` VALUES ('5846');
INSERT INTO `item_lv_5` VALUES ('5857');
INSERT INTO `item_lv_5` VALUES ('5858');
INSERT INTO `item_lv_5` VALUES ('5352');
INSERT INTO `item_lv_5` VALUES ('5859');
INSERT INTO `item_lv_5` VALUES ('5418');
INSERT INTO `item_lv_5` VALUES ('5428');
INSERT INTO `item_lv_5` VALUES ('5431');
INSERT INTO `item_lv_5` VALUES ('5462');
INSERT INTO `item_lv_5` VALUES ('5463');
INSERT INTO `item_lv_5` VALUES ('5466');
INSERT INTO `item_lv_5` VALUES ('5511');
INSERT INTO `item_lv_5` VALUES ('5822');
INSERT INTO `item_lv_5` VALUES ('5664');
INSERT INTO `item_lv_5` VALUES ('5201');
INSERT INTO `item_lv_5` VALUES ('5808');
INSERT INTO `item_lv_5` VALUES ('5851');
INSERT INTO `item_lv_5` VALUES ('5571');
INSERT INTO `item_lv_5` VALUES ('5670');
INSERT INTO `item_lv_5` VALUES ('5674');
INSERT INTO `item_lv_5` VALUES ('5761');
INSERT INTO `item_lv_5` VALUES ('5772');
INSERT INTO `item_lv_5` VALUES ('5773');
INSERT INTO `item_lv_5` VALUES ('5774');
INSERT INTO `item_lv_5` VALUES ('5778');
INSERT INTO `item_lv_5` VALUES ('5779');
INSERT INTO `item_lv_5` VALUES ('5783');
INSERT INTO `item_lv_5` VALUES ('5784');
INSERT INTO `item_lv_5` VALUES ('5785');

-- ----------------------------
-- Table structure for `item_lv_6`
-- ----------------------------
DROP TABLE IF EXISTS `item_lv_6`;
CREATE TABLE `item_lv_6` (
  `item_id` int(10) NOT NULL default '0'
) ENGINE=MyISAM;

-- ----------------------------
-- Records of item_lv_6
-- ----------------------------
INSERT INTO `item_lv_6` VALUES ('5373');
INSERT INTO `item_lv_6` VALUES ('5376');
INSERT INTO `item_lv_6` VALUES ('5467');
INSERT INTO `item_lv_6` VALUES ('5498');
INSERT INTO `item_lv_6` VALUES ('5545');
INSERT INTO `item_lv_6` VALUES ('5546');
INSERT INTO `item_lv_6` VALUES ('5585');
INSERT INTO `item_lv_6` VALUES ('5594');
INSERT INTO `item_lv_6` VALUES ('5786');
INSERT INTO `item_lv_6` VALUES ('5107');
INSERT INTO `item_lv_6` VALUES ('5211');
INSERT INTO `item_lv_6` VALUES ('5310');
INSERT INTO `item_lv_6` VALUES ('5311');
INSERT INTO `item_lv_6` VALUES ('5314');
INSERT INTO `item_lv_6` VALUES ('5357');
INSERT INTO `item_lv_6` VALUES ('5361');
INSERT INTO `item_lv_6` VALUES ('5362');
INSERT INTO `item_lv_6` VALUES ('5366');
INSERT INTO `item_lv_6` VALUES ('5382');
INSERT INTO `item_lv_6` VALUES ('5395');
INSERT INTO `item_lv_6` VALUES ('5497');
INSERT INTO `item_lv_6` VALUES ('5766');
INSERT INTO `item_lv_6` VALUES ('5776');
INSERT INTO `item_lv_6` VALUES ('5214');
INSERT INTO `item_lv_6` VALUES ('5597');
INSERT INTO `item_lv_6` VALUES ('5264');
INSERT INTO `item_lv_6` VALUES ('5271');
INSERT INTO `item_lv_6` VALUES ('5356');
INSERT INTO `item_lv_6` VALUES ('5421');
INSERT INTO `item_lv_6` VALUES ('5535');
INSERT INTO `item_lv_6` VALUES ('5665');
INSERT INTO `item_lv_6` VALUES ('5755');
INSERT INTO `item_lv_6` VALUES ('5759');
INSERT INTO `item_lv_6` VALUES ('5768');
INSERT INTO `item_lv_6` VALUES ('5841');
INSERT INTO `item_lv_6` VALUES ('5013');
INSERT INTO `item_lv_6` VALUES ('5433');
INSERT INTO `item_lv_6` VALUES ('5457');
INSERT INTO `item_lv_6` VALUES ('5503');
INSERT INTO `item_lv_6` VALUES ('5529');
INSERT INTO `item_lv_6` VALUES ('5810');
INSERT INTO `item_lv_6` VALUES ('5171');
INSERT INTO `item_lv_6` VALUES ('5432');
INSERT INTO `item_lv_6` VALUES ('5460');
INSERT INTO `item_lv_6` VALUES ('5464');
INSERT INTO `item_lv_6` VALUES ('5469');
INSERT INTO `item_lv_6` VALUES ('5567');
INSERT INTO `item_lv_6` VALUES ('5688');
INSERT INTO `item_lv_6` VALUES ('5809');
INSERT INTO `item_lv_6` VALUES ('5484');
INSERT INTO `item_lv_6` VALUES ('5588');
INSERT INTO `item_lv_6` VALUES ('5589');
INSERT INTO `item_lv_6` VALUES ('5661');
INSERT INTO `item_lv_6` VALUES ('5676');
INSERT INTO `item_lv_6` VALUES ('5677');
INSERT INTO `item_lv_6` VALUES ('5762');
INSERT INTO `item_lv_6` VALUES ('5780');
INSERT INTO `item_lv_6` VALUES ('5782');
INSERT INTO `item_lv_6` VALUES ('5868');

-- ----------------------------
-- Table structure for `item_lv_7`
-- ----------------------------
DROP TABLE IF EXISTS `item_lv_7`;
CREATE TABLE `item_lv_7` (
  `item_id` int(10) NOT NULL default '0'
) ENGINE=MyISAM;

-- ----------------------------
-- Records of item_lv_7
-- ----------------------------
INSERT INTO `item_lv_7` VALUES ('5210');
INSERT INTO `item_lv_7` VALUES ('5213');
INSERT INTO `item_lv_7` VALUES ('5269');
INSERT INTO `item_lv_7` VALUES ('5372');
INSERT INTO `item_lv_7` VALUES ('5379');
INSERT INTO `item_lv_7` VALUES ('5471');
INSERT INTO `item_lv_7` VALUES ('5482');
INSERT INTO `item_lv_7` VALUES ('5504');
INSERT INTO `item_lv_7` VALUES ('5568');
INSERT INTO `item_lv_7` VALUES ('5596');
INSERT INTO `item_lv_7` VALUES ('5609');
INSERT INTO `item_lv_7` VALUES ('5132');
INSERT INTO `item_lv_7` VALUES ('5225');
INSERT INTO `item_lv_7` VALUES ('5256');
INSERT INTO `item_lv_7` VALUES ('5278');
INSERT INTO `item_lv_7` VALUES ('5319');
INSERT INTO `item_lv_7` VALUES ('5363');
INSERT INTO `item_lv_7` VALUES ('5334');
INSERT INTO `item_lv_7` VALUES ('5396');
INSERT INTO `item_lv_7` VALUES ('5520');
INSERT INTO `item_lv_7` VALUES ('5549');
INSERT INTO `item_lv_7` VALUES ('5565');
INSERT INTO `item_lv_7` VALUES ('5569');
INSERT INTO `item_lv_7` VALUES ('5570');
INSERT INTO `item_lv_7` VALUES ('5581');
INSERT INTO `item_lv_7` VALUES ('5592');
INSERT INTO `item_lv_7` VALUES ('5654');
INSERT INTO `item_lv_7` VALUES ('5468');
INSERT INTO `item_lv_7` VALUES ('5495');
INSERT INTO `item_lv_7` VALUES ('5512');
INSERT INTO `item_lv_7` VALUES ('5513');
INSERT INTO `item_lv_7` VALUES ('5514');
INSERT INTO `item_lv_7` VALUES ('5515');
INSERT INTO `item_lv_7` VALUES ('5518');
INSERT INTO `item_lv_7` VALUES ('5550');
INSERT INTO `item_lv_7` VALUES ('5564');
INSERT INTO `item_lv_7` VALUES ('5590');
INSERT INTO `item_lv_7` VALUES ('5747');
INSERT INTO `item_lv_7` VALUES ('5748');
INSERT INTO `item_lv_7` VALUES ('5749');
INSERT INTO `item_lv_7` VALUES ('5750');
INSERT INTO `item_lv_7` VALUES ('5751');
INSERT INTO `item_lv_7` VALUES ('5752');
INSERT INTO `item_lv_7` VALUES ('5753');
INSERT INTO `item_lv_7` VALUES ('5754');
INSERT INTO `item_lv_7` VALUES ('5756');
INSERT INTO `item_lv_7` VALUES ('5757');
INSERT INTO `item_lv_7` VALUES ('5758');
INSERT INTO `item_lv_7` VALUES ('5760');
INSERT INTO `item_lv_7` VALUES ('5306');
INSERT INTO `item_lv_7` VALUES ('5353');
INSERT INTO `item_lv_7` VALUES ('5423');
INSERT INTO `item_lv_7` VALUES ('5593');
INSERT INTO `item_lv_7` VALUES ('5600');
INSERT INTO `item_lv_7` VALUES ('5740');
INSERT INTO `item_lv_7` VALUES ('5818');
INSERT INTO `item_lv_7` VALUES ('5819');
INSERT INTO `item_lv_7` VALUES ('5826');
INSERT INTO `item_lv_7` VALUES ('5551');
INSERT INTO `item_lv_7` VALUES ('5595');
INSERT INTO `item_lv_7` VALUES ('5598');
INSERT INTO `item_lv_7` VALUES ('5599');
INSERT INTO `item_lv_7` VALUES ('5662');
INSERT INTO `item_lv_7` VALUES ('5663');
INSERT INTO `item_lv_7` VALUES ('5738');
INSERT INTO `item_lv_7` VALUES ('5739');
INSERT INTO `item_lv_7` VALUES ('5744');
INSERT INTO `item_lv_7` VALUES ('5745');
INSERT INTO `item_lv_7` VALUES ('5764');
INSERT INTO `item_lv_7` VALUES ('5770');
INSERT INTO `item_lv_7` VALUES ('5869');
INSERT INTO `item_lv_7` VALUES ('5870');
INSERT INTO `item_lv_7` VALUES ('5871');

-- ----------------------------
-- Table structure for `item_lv_8`
-- ----------------------------
DROP TABLE IF EXISTS `item_lv_8`;
CREATE TABLE `item_lv_8` (
  `item_id` int(10) NOT NULL default '0'
) ENGINE=MyISAM;

-- ----------------------------
-- Records of item_lv_8
-- ----------------------------
INSERT INTO `item_lv_8` VALUES ('5022');
INSERT INTO `item_lv_8` VALUES ('5106');
INSERT INTO `item_lv_8` VALUES ('5605');
INSERT INTO `item_lv_8` VALUES ('5604');
INSERT INTO `item_lv_8` VALUES ('5451');
INSERT INTO `item_lv_8` VALUES ('5452');
INSERT INTO `item_lv_8` VALUES ('5453');
INSERT INTO `item_lv_8` VALUES ('5582');
INSERT INTO `item_lv_8` VALUES ('5603');
INSERT INTO `item_lv_8` VALUES ('5666');
INSERT INTO `item_lv_8` VALUES ('5746');

-- ----------------------------
-- Table structure for `item_tmp`
-- ----------------------------
DROP TABLE IF EXISTS `item_tmp`;
CREATE TABLE `item_tmp` (
  `item_id` int(10) NOT NULL default '0'
) ENGINE=MyISAM;

-- ----------------------------
-- Records of item_tmp
-- ----------------------------

-- ----------------------------
-- Table structure for `makeitem_lv_1`
-- ----------------------------
DROP TABLE IF EXISTS `makeitem_lv_1`;
CREATE TABLE `makeitem_lv_1` (
  `item_id` int(10) NOT NULL default '0'
) ENGINE=MyISAM;

-- ----------------------------
-- Records of makeitem_lv_1
-- ----------------------------
INSERT INTO `makeitem_lv_1` VALUES ('705');
INSERT INTO `makeitem_lv_1` VALUES ('709');
INSERT INTO `makeitem_lv_1` VALUES ('717');
INSERT INTO `makeitem_lv_1` VALUES ('725');
INSERT INTO `makeitem_lv_1` VALUES ('733');
INSERT INTO `makeitem_lv_1` VALUES ('741');
INSERT INTO `makeitem_lv_1` VALUES ('749');
INSERT INTO `makeitem_lv_1` VALUES ('901');
INSERT INTO `makeitem_lv_1` VALUES ('909');
INSERT INTO `makeitem_lv_1` VALUES ('917');
INSERT INTO `makeitem_lv_1` VALUES ('925');
INSERT INTO `makeitem_lv_1` VALUES ('935');
INSERT INTO `makeitem_lv_1` VALUES ('943');
INSERT INTO `makeitem_lv_1` VALUES ('951');
INSERT INTO `makeitem_lv_1` VALUES ('959');
INSERT INTO `makeitem_lv_1` VALUES ('967');
INSERT INTO `makeitem_lv_1` VALUES ('984');
INSERT INTO `makeitem_lv_1` VALUES ('992');
INSERT INTO `makeitem_lv_1` VALUES ('1000');
INSERT INTO `makeitem_lv_1` VALUES ('1008');
INSERT INTO `makeitem_lv_1` VALUES ('1016');
INSERT INTO `makeitem_lv_1` VALUES ('1024');
INSERT INTO `makeitem_lv_1` VALUES ('1032');
INSERT INTO `makeitem_lv_1` VALUES ('1040');
INSERT INTO `makeitem_lv_1` VALUES ('1048');
INSERT INTO `makeitem_lv_1` VALUES ('1056');
INSERT INTO `makeitem_lv_1` VALUES ('6010');
INSERT INTO `makeitem_lv_1` VALUES ('7004');
INSERT INTO `makeitem_lv_1` VALUES ('7012');
INSERT INTO `makeitem_lv_1` VALUES ('7068');
INSERT INTO `makeitem_lv_1` VALUES ('7100');
INSERT INTO `makeitem_lv_1` VALUES ('7116');
INSERT INTO `makeitem_lv_1` VALUES ('7124');
INSERT INTO `makeitem_lv_1` VALUES ('7140');
INSERT INTO `makeitem_lv_1` VALUES ('7156');
INSERT INTO `makeitem_lv_1` VALUES ('7164');
INSERT INTO `makeitem_lv_1` VALUES ('7172');
INSERT INTO `makeitem_lv_1` VALUES ('7188');
INSERT INTO `makeitem_lv_1` VALUES ('7196');
INSERT INTO `makeitem_lv_1` VALUES ('7212');
INSERT INTO `makeitem_lv_1` VALUES ('7220');

-- ----------------------------
-- Table structure for `makeitem_lv_2`
-- ----------------------------
DROP TABLE IF EXISTS `makeitem_lv_2`;
CREATE TABLE `makeitem_lv_2` (
  `item_id` int(10) NOT NULL default '0'
) ENGINE=MyISAM;

-- ----------------------------
-- Records of makeitem_lv_2
-- ----------------------------
INSERT INTO `makeitem_lv_2` VALUES ('702');
INSERT INTO `makeitem_lv_2` VALUES ('710');
INSERT INTO `makeitem_lv_2` VALUES ('718');
INSERT INTO `makeitem_lv_2` VALUES ('726');
INSERT INTO `makeitem_lv_2` VALUES ('734');
INSERT INTO `makeitem_lv_2` VALUES ('742');
INSERT INTO `makeitem_lv_2` VALUES ('902');
INSERT INTO `makeitem_lv_2` VALUES ('910');
INSERT INTO `makeitem_lv_2` VALUES ('918');
INSERT INTO `makeitem_lv_2` VALUES ('926');
INSERT INTO `makeitem_lv_2` VALUES ('936');
INSERT INTO `makeitem_lv_2` VALUES ('944');
INSERT INTO `makeitem_lv_2` VALUES ('952');
INSERT INTO `makeitem_lv_2` VALUES ('960');
INSERT INTO `makeitem_lv_2` VALUES ('985');
INSERT INTO `makeitem_lv_2` VALUES ('993');
INSERT INTO `makeitem_lv_2` VALUES ('1001');
INSERT INTO `makeitem_lv_2` VALUES ('1009');
INSERT INTO `makeitem_lv_2` VALUES ('1017');
INSERT INTO `makeitem_lv_2` VALUES ('1025');
INSERT INTO `makeitem_lv_2` VALUES ('1033');
INSERT INTO `makeitem_lv_2` VALUES ('1041');
INSERT INTO `makeitem_lv_2` VALUES ('1049');
INSERT INTO `makeitem_lv_2` VALUES ('1057');
INSERT INTO `makeitem_lv_2` VALUES ('6020');
INSERT INTO `makeitem_lv_2` VALUES ('7005');
INSERT INTO `makeitem_lv_2` VALUES ('7013');
INSERT INTO `makeitem_lv_2` VALUES ('7021');
INSERT INTO `makeitem_lv_2` VALUES ('7053');
INSERT INTO `makeitem_lv_2` VALUES ('7069');
INSERT INTO `makeitem_lv_2` VALUES ('7101');
INSERT INTO `makeitem_lv_2` VALUES ('7117');
INSERT INTO `makeitem_lv_2` VALUES ('7125');
INSERT INTO `makeitem_lv_2` VALUES ('7141');
INSERT INTO `makeitem_lv_2` VALUES ('7149');
INSERT INTO `makeitem_lv_2` VALUES ('7157');
INSERT INTO `makeitem_lv_2` VALUES ('7165');
INSERT INTO `makeitem_lv_2` VALUES ('7189');
INSERT INTO `makeitem_lv_2` VALUES ('7197');
INSERT INTO `makeitem_lv_2` VALUES ('7205');
INSERT INTO `makeitem_lv_2` VALUES ('7213');
INSERT INTO `makeitem_lv_2` VALUES ('7221');
INSERT INTO `makeitem_lv_2` VALUES ('705');
INSERT INTO `makeitem_lv_2` VALUES ('709');
INSERT INTO `makeitem_lv_2` VALUES ('717');
INSERT INTO `makeitem_lv_2` VALUES ('725');
INSERT INTO `makeitem_lv_2` VALUES ('733');
INSERT INTO `makeitem_lv_2` VALUES ('741');
INSERT INTO `makeitem_lv_2` VALUES ('749');
INSERT INTO `makeitem_lv_2` VALUES ('901');
INSERT INTO `makeitem_lv_2` VALUES ('909');
INSERT INTO `makeitem_lv_2` VALUES ('917');
INSERT INTO `makeitem_lv_2` VALUES ('925');
INSERT INTO `makeitem_lv_2` VALUES ('935');
INSERT INTO `makeitem_lv_2` VALUES ('943');
INSERT INTO `makeitem_lv_2` VALUES ('951');
INSERT INTO `makeitem_lv_2` VALUES ('959');
INSERT INTO `makeitem_lv_2` VALUES ('967');
INSERT INTO `makeitem_lv_2` VALUES ('984');
INSERT INTO `makeitem_lv_2` VALUES ('992');
INSERT INTO `makeitem_lv_2` VALUES ('1000');
INSERT INTO `makeitem_lv_2` VALUES ('1008');
INSERT INTO `makeitem_lv_2` VALUES ('1016');
INSERT INTO `makeitem_lv_2` VALUES ('1024');
INSERT INTO `makeitem_lv_2` VALUES ('1032');
INSERT INTO `makeitem_lv_2` VALUES ('1040');
INSERT INTO `makeitem_lv_2` VALUES ('1048');
INSERT INTO `makeitem_lv_2` VALUES ('1056');
INSERT INTO `makeitem_lv_2` VALUES ('6010');
INSERT INTO `makeitem_lv_2` VALUES ('7004');
INSERT INTO `makeitem_lv_2` VALUES ('7012');
INSERT INTO `makeitem_lv_2` VALUES ('7068');
INSERT INTO `makeitem_lv_2` VALUES ('7100');
INSERT INTO `makeitem_lv_2` VALUES ('7116');
INSERT INTO `makeitem_lv_2` VALUES ('7124');
INSERT INTO `makeitem_lv_2` VALUES ('7140');
INSERT INTO `makeitem_lv_2` VALUES ('7156');
INSERT INTO `makeitem_lv_2` VALUES ('7164');
INSERT INTO `makeitem_lv_2` VALUES ('7172');
INSERT INTO `makeitem_lv_2` VALUES ('7188');
INSERT INTO `makeitem_lv_2` VALUES ('7196');
INSERT INTO `makeitem_lv_2` VALUES ('7212');
INSERT INTO `makeitem_lv_2` VALUES ('7220');

-- ----------------------------
-- Table structure for `makeitem_lv_3`
-- ----------------------------
DROP TABLE IF EXISTS `makeitem_lv_3`;
CREATE TABLE `makeitem_lv_3` (
  `item_id` int(10) NOT NULL default '0'
) ENGINE=MyISAM;

-- ----------------------------
-- Records of makeitem_lv_3
-- ----------------------------
INSERT INTO `makeitem_lv_3` VALUES ('703');
INSERT INTO `makeitem_lv_3` VALUES ('711');
INSERT INTO `makeitem_lv_3` VALUES ('719');
INSERT INTO `makeitem_lv_3` VALUES ('727');
INSERT INTO `makeitem_lv_3` VALUES ('735');
INSERT INTO `makeitem_lv_3` VALUES ('743');
INSERT INTO `makeitem_lv_3` VALUES ('903');
INSERT INTO `makeitem_lv_3` VALUES ('911');
INSERT INTO `makeitem_lv_3` VALUES ('919');
INSERT INTO `makeitem_lv_3` VALUES ('928');
INSERT INTO `makeitem_lv_3` VALUES ('937');
INSERT INTO `makeitem_lv_3` VALUES ('945');
INSERT INTO `makeitem_lv_3` VALUES ('953');
INSERT INTO `makeitem_lv_3` VALUES ('961');
INSERT INTO `makeitem_lv_3` VALUES ('986');
INSERT INTO `makeitem_lv_3` VALUES ('994');
INSERT INTO `makeitem_lv_3` VALUES ('1002');
INSERT INTO `makeitem_lv_3` VALUES ('1010');
INSERT INTO `makeitem_lv_3` VALUES ('1018');
INSERT INTO `makeitem_lv_3` VALUES ('1026');
INSERT INTO `makeitem_lv_3` VALUES ('1034');
INSERT INTO `makeitem_lv_3` VALUES ('1042');
INSERT INTO `makeitem_lv_3` VALUES ('1050');
INSERT INTO `makeitem_lv_3` VALUES ('1058');
INSERT INTO `makeitem_lv_3` VALUES ('6021');
INSERT INTO `makeitem_lv_3` VALUES ('7006');
INSERT INTO `makeitem_lv_3` VALUES ('7014');
INSERT INTO `makeitem_lv_3` VALUES ('7030');
INSERT INTO `makeitem_lv_3` VALUES ('7038');
INSERT INTO `makeitem_lv_3` VALUES ('7054');
INSERT INTO `makeitem_lv_3` VALUES ('7070');
INSERT INTO `makeitem_lv_3` VALUES ('7110');
INSERT INTO `makeitem_lv_3` VALUES ('7118');
INSERT INTO `makeitem_lv_3` VALUES ('7126');
INSERT INTO `makeitem_lv_3` VALUES ('7150');
INSERT INTO `makeitem_lv_3` VALUES ('7158');
INSERT INTO `makeitem_lv_3` VALUES ('7166');
INSERT INTO `makeitem_lv_3` VALUES ('7182');
INSERT INTO `makeitem_lv_3` VALUES ('7190');
INSERT INTO `makeitem_lv_3` VALUES ('7198');
INSERT INTO `makeitem_lv_3` VALUES ('7206');
INSERT INTO `makeitem_lv_3` VALUES ('7214');
INSERT INTO `makeitem_lv_3` VALUES ('7222');
INSERT INTO `makeitem_lv_3` VALUES ('702');
INSERT INTO `makeitem_lv_3` VALUES ('710');
INSERT INTO `makeitem_lv_3` VALUES ('718');
INSERT INTO `makeitem_lv_3` VALUES ('726');
INSERT INTO `makeitem_lv_3` VALUES ('734');
INSERT INTO `makeitem_lv_3` VALUES ('742');
INSERT INTO `makeitem_lv_3` VALUES ('902');
INSERT INTO `makeitem_lv_3` VALUES ('910');
INSERT INTO `makeitem_lv_3` VALUES ('918');
INSERT INTO `makeitem_lv_3` VALUES ('926');
INSERT INTO `makeitem_lv_3` VALUES ('936');
INSERT INTO `makeitem_lv_3` VALUES ('944');
INSERT INTO `makeitem_lv_3` VALUES ('952');
INSERT INTO `makeitem_lv_3` VALUES ('960');
INSERT INTO `makeitem_lv_3` VALUES ('985');
INSERT INTO `makeitem_lv_3` VALUES ('993');
INSERT INTO `makeitem_lv_3` VALUES ('1001');
INSERT INTO `makeitem_lv_3` VALUES ('1009');
INSERT INTO `makeitem_lv_3` VALUES ('1017');
INSERT INTO `makeitem_lv_3` VALUES ('1025');
INSERT INTO `makeitem_lv_3` VALUES ('1033');
INSERT INTO `makeitem_lv_3` VALUES ('1041');
INSERT INTO `makeitem_lv_3` VALUES ('1049');
INSERT INTO `makeitem_lv_3` VALUES ('1057');
INSERT INTO `makeitem_lv_3` VALUES ('6020');
INSERT INTO `makeitem_lv_3` VALUES ('7005');
INSERT INTO `makeitem_lv_3` VALUES ('7013');
INSERT INTO `makeitem_lv_3` VALUES ('7021');
INSERT INTO `makeitem_lv_3` VALUES ('7053');
INSERT INTO `makeitem_lv_3` VALUES ('7069');
INSERT INTO `makeitem_lv_3` VALUES ('7101');
INSERT INTO `makeitem_lv_3` VALUES ('7117');
INSERT INTO `makeitem_lv_3` VALUES ('7125');
INSERT INTO `makeitem_lv_3` VALUES ('7141');
INSERT INTO `makeitem_lv_3` VALUES ('7149');
INSERT INTO `makeitem_lv_3` VALUES ('7157');
INSERT INTO `makeitem_lv_3` VALUES ('7165');
INSERT INTO `makeitem_lv_3` VALUES ('7189');
INSERT INTO `makeitem_lv_3` VALUES ('7197');
INSERT INTO `makeitem_lv_3` VALUES ('7205');
INSERT INTO `makeitem_lv_3` VALUES ('7213');
INSERT INTO `makeitem_lv_3` VALUES ('7221');
INSERT INTO `makeitem_lv_3` VALUES ('705');
INSERT INTO `makeitem_lv_3` VALUES ('709');
INSERT INTO `makeitem_lv_3` VALUES ('717');
INSERT INTO `makeitem_lv_3` VALUES ('725');
INSERT INTO `makeitem_lv_3` VALUES ('733');
INSERT INTO `makeitem_lv_3` VALUES ('741');
INSERT INTO `makeitem_lv_3` VALUES ('749');
INSERT INTO `makeitem_lv_3` VALUES ('901');
INSERT INTO `makeitem_lv_3` VALUES ('909');
INSERT INTO `makeitem_lv_3` VALUES ('917');
INSERT INTO `makeitem_lv_3` VALUES ('925');
INSERT INTO `makeitem_lv_3` VALUES ('935');
INSERT INTO `makeitem_lv_3` VALUES ('943');
INSERT INTO `makeitem_lv_3` VALUES ('951');
INSERT INTO `makeitem_lv_3` VALUES ('959');
INSERT INTO `makeitem_lv_3` VALUES ('967');
INSERT INTO `makeitem_lv_3` VALUES ('984');
INSERT INTO `makeitem_lv_3` VALUES ('992');
INSERT INTO `makeitem_lv_3` VALUES ('1000');
INSERT INTO `makeitem_lv_3` VALUES ('1008');
INSERT INTO `makeitem_lv_3` VALUES ('1016');
INSERT INTO `makeitem_lv_3` VALUES ('1024');
INSERT INTO `makeitem_lv_3` VALUES ('1032');
INSERT INTO `makeitem_lv_3` VALUES ('1040');
INSERT INTO `makeitem_lv_3` VALUES ('1048');
INSERT INTO `makeitem_lv_3` VALUES ('1056');
INSERT INTO `makeitem_lv_3` VALUES ('6010');
INSERT INTO `makeitem_lv_3` VALUES ('7004');
INSERT INTO `makeitem_lv_3` VALUES ('7012');
INSERT INTO `makeitem_lv_3` VALUES ('7068');
INSERT INTO `makeitem_lv_3` VALUES ('7100');
INSERT INTO `makeitem_lv_3` VALUES ('7116');
INSERT INTO `makeitem_lv_3` VALUES ('7124');
INSERT INTO `makeitem_lv_3` VALUES ('7140');
INSERT INTO `makeitem_lv_3` VALUES ('7156');
INSERT INTO `makeitem_lv_3` VALUES ('7164');
INSERT INTO `makeitem_lv_3` VALUES ('7172');
INSERT INTO `makeitem_lv_3` VALUES ('7188');
INSERT INTO `makeitem_lv_3` VALUES ('7196');
INSERT INTO `makeitem_lv_3` VALUES ('7212');
INSERT INTO `makeitem_lv_3` VALUES ('7220');

-- ----------------------------
-- Table structure for `makeitem_lv_4`
-- ----------------------------
DROP TABLE IF EXISTS `makeitem_lv_4`;
CREATE TABLE `makeitem_lv_4` (
  `item_id` int(10) NOT NULL default '0'
) ENGINE=MyISAM;

-- ----------------------------
-- Records of makeitem_lv_4
-- ----------------------------
INSERT INTO `makeitem_lv_4` VALUES ('704');
INSERT INTO `makeitem_lv_4` VALUES ('712');
INSERT INTO `makeitem_lv_4` VALUES ('720');
INSERT INTO `makeitem_lv_4` VALUES ('728');
INSERT INTO `makeitem_lv_4` VALUES ('736');
INSERT INTO `makeitem_lv_4` VALUES ('744');
INSERT INTO `makeitem_lv_4` VALUES ('752');
INSERT INTO `makeitem_lv_4` VALUES ('904');
INSERT INTO `makeitem_lv_4` VALUES ('912');
INSERT INTO `makeitem_lv_4` VALUES ('920');
INSERT INTO `makeitem_lv_4` VALUES ('929');
INSERT INTO `makeitem_lv_4` VALUES ('938');
INSERT INTO `makeitem_lv_4` VALUES ('946');
INSERT INTO `makeitem_lv_4` VALUES ('954');
INSERT INTO `makeitem_lv_4` VALUES ('962');
INSERT INTO `makeitem_lv_4` VALUES ('970');
INSERT INTO `makeitem_lv_4` VALUES ('987');
INSERT INTO `makeitem_lv_4` VALUES ('995');
INSERT INTO `makeitem_lv_4` VALUES ('1003');
INSERT INTO `makeitem_lv_4` VALUES ('1011');
INSERT INTO `makeitem_lv_4` VALUES ('1019');
INSERT INTO `makeitem_lv_4` VALUES ('1027');
INSERT INTO `makeitem_lv_4` VALUES ('1035');
INSERT INTO `makeitem_lv_4` VALUES ('1043');
INSERT INTO `makeitem_lv_4` VALUES ('1051');
INSERT INTO `makeitem_lv_4` VALUES ('1059');
INSERT INTO `makeitem_lv_4` VALUES ('7007');
INSERT INTO `makeitem_lv_4` VALUES ('7015');
INSERT INTO `makeitem_lv_4` VALUES ('7023');
INSERT INTO `makeitem_lv_4` VALUES ('7031');
INSERT INTO `makeitem_lv_4` VALUES ('7047');
INSERT INTO `makeitem_lv_4` VALUES ('7063');
INSERT INTO `makeitem_lv_4` VALUES ('7071');
INSERT INTO `makeitem_lv_4` VALUES ('7095');
INSERT INTO `makeitem_lv_4` VALUES ('7111');
INSERT INTO `makeitem_lv_4` VALUES ('7119');
INSERT INTO `makeitem_lv_4` VALUES ('7135');
INSERT INTO `makeitem_lv_4` VALUES ('7151');
INSERT INTO `makeitem_lv_4` VALUES ('7159');
INSERT INTO `makeitem_lv_4` VALUES ('7167');
INSERT INTO `makeitem_lv_4` VALUES ('7207');
INSERT INTO `makeitem_lv_4` VALUES ('7215');
INSERT INTO `makeitem_lv_4` VALUES ('7223');
INSERT INTO `makeitem_lv_4` VALUES ('703');
INSERT INTO `makeitem_lv_4` VALUES ('711');
INSERT INTO `makeitem_lv_4` VALUES ('719');
INSERT INTO `makeitem_lv_4` VALUES ('727');
INSERT INTO `makeitem_lv_4` VALUES ('735');
INSERT INTO `makeitem_lv_4` VALUES ('743');
INSERT INTO `makeitem_lv_4` VALUES ('903');
INSERT INTO `makeitem_lv_4` VALUES ('911');
INSERT INTO `makeitem_lv_4` VALUES ('919');
INSERT INTO `makeitem_lv_4` VALUES ('928');
INSERT INTO `makeitem_lv_4` VALUES ('937');
INSERT INTO `makeitem_lv_4` VALUES ('945');
INSERT INTO `makeitem_lv_4` VALUES ('953');
INSERT INTO `makeitem_lv_4` VALUES ('961');
INSERT INTO `makeitem_lv_4` VALUES ('986');
INSERT INTO `makeitem_lv_4` VALUES ('994');
INSERT INTO `makeitem_lv_4` VALUES ('1002');
INSERT INTO `makeitem_lv_4` VALUES ('1010');
INSERT INTO `makeitem_lv_4` VALUES ('1018');
INSERT INTO `makeitem_lv_4` VALUES ('1026');
INSERT INTO `makeitem_lv_4` VALUES ('1034');
INSERT INTO `makeitem_lv_4` VALUES ('1042');
INSERT INTO `makeitem_lv_4` VALUES ('1050');
INSERT INTO `makeitem_lv_4` VALUES ('1058');
INSERT INTO `makeitem_lv_4` VALUES ('6021');
INSERT INTO `makeitem_lv_4` VALUES ('7006');
INSERT INTO `makeitem_lv_4` VALUES ('7014');
INSERT INTO `makeitem_lv_4` VALUES ('7030');
INSERT INTO `makeitem_lv_4` VALUES ('7038');
INSERT INTO `makeitem_lv_4` VALUES ('7054');
INSERT INTO `makeitem_lv_4` VALUES ('7070');
INSERT INTO `makeitem_lv_4` VALUES ('7110');
INSERT INTO `makeitem_lv_4` VALUES ('7118');
INSERT INTO `makeitem_lv_4` VALUES ('7126');
INSERT INTO `makeitem_lv_4` VALUES ('7150');
INSERT INTO `makeitem_lv_4` VALUES ('7158');
INSERT INTO `makeitem_lv_4` VALUES ('7166');
INSERT INTO `makeitem_lv_4` VALUES ('7182');
INSERT INTO `makeitem_lv_4` VALUES ('7190');
INSERT INTO `makeitem_lv_4` VALUES ('7198');
INSERT INTO `makeitem_lv_4` VALUES ('7206');
INSERT INTO `makeitem_lv_4` VALUES ('7214');
INSERT INTO `makeitem_lv_4` VALUES ('7222');
INSERT INTO `makeitem_lv_4` VALUES ('702');
INSERT INTO `makeitem_lv_4` VALUES ('710');
INSERT INTO `makeitem_lv_4` VALUES ('718');
INSERT INTO `makeitem_lv_4` VALUES ('726');
INSERT INTO `makeitem_lv_4` VALUES ('734');
INSERT INTO `makeitem_lv_4` VALUES ('742');
INSERT INTO `makeitem_lv_4` VALUES ('902');
INSERT INTO `makeitem_lv_4` VALUES ('910');
INSERT INTO `makeitem_lv_4` VALUES ('918');
INSERT INTO `makeitem_lv_4` VALUES ('926');
INSERT INTO `makeitem_lv_4` VALUES ('936');
INSERT INTO `makeitem_lv_4` VALUES ('944');
INSERT INTO `makeitem_lv_4` VALUES ('952');
INSERT INTO `makeitem_lv_4` VALUES ('960');
INSERT INTO `makeitem_lv_4` VALUES ('985');
INSERT INTO `makeitem_lv_4` VALUES ('993');
INSERT INTO `makeitem_lv_4` VALUES ('1001');
INSERT INTO `makeitem_lv_4` VALUES ('1009');
INSERT INTO `makeitem_lv_4` VALUES ('1017');
INSERT INTO `makeitem_lv_4` VALUES ('1025');
INSERT INTO `makeitem_lv_4` VALUES ('1033');
INSERT INTO `makeitem_lv_4` VALUES ('1041');
INSERT INTO `makeitem_lv_4` VALUES ('1049');
INSERT INTO `makeitem_lv_4` VALUES ('1057');
INSERT INTO `makeitem_lv_4` VALUES ('6020');
INSERT INTO `makeitem_lv_4` VALUES ('7005');
INSERT INTO `makeitem_lv_4` VALUES ('7013');
INSERT INTO `makeitem_lv_4` VALUES ('7021');
INSERT INTO `makeitem_lv_4` VALUES ('7053');
INSERT INTO `makeitem_lv_4` VALUES ('7069');
INSERT INTO `makeitem_lv_4` VALUES ('7101');
INSERT INTO `makeitem_lv_4` VALUES ('7117');
INSERT INTO `makeitem_lv_4` VALUES ('7125');
INSERT INTO `makeitem_lv_4` VALUES ('7141');
INSERT INTO `makeitem_lv_4` VALUES ('7149');
INSERT INTO `makeitem_lv_4` VALUES ('7157');
INSERT INTO `makeitem_lv_4` VALUES ('7165');
INSERT INTO `makeitem_lv_4` VALUES ('7189');
INSERT INTO `makeitem_lv_4` VALUES ('7197');
INSERT INTO `makeitem_lv_4` VALUES ('7205');
INSERT INTO `makeitem_lv_4` VALUES ('7213');
INSERT INTO `makeitem_lv_4` VALUES ('7221');
INSERT INTO `makeitem_lv_4` VALUES ('705');
INSERT INTO `makeitem_lv_4` VALUES ('709');
INSERT INTO `makeitem_lv_4` VALUES ('717');
INSERT INTO `makeitem_lv_4` VALUES ('725');
INSERT INTO `makeitem_lv_4` VALUES ('733');
INSERT INTO `makeitem_lv_4` VALUES ('741');
INSERT INTO `makeitem_lv_4` VALUES ('749');
INSERT INTO `makeitem_lv_4` VALUES ('901');
INSERT INTO `makeitem_lv_4` VALUES ('909');
INSERT INTO `makeitem_lv_4` VALUES ('917');
INSERT INTO `makeitem_lv_4` VALUES ('925');
INSERT INTO `makeitem_lv_4` VALUES ('935');
INSERT INTO `makeitem_lv_4` VALUES ('943');
INSERT INTO `makeitem_lv_4` VALUES ('951');
INSERT INTO `makeitem_lv_4` VALUES ('959');
INSERT INTO `makeitem_lv_4` VALUES ('967');
INSERT INTO `makeitem_lv_4` VALUES ('984');
INSERT INTO `makeitem_lv_4` VALUES ('992');
INSERT INTO `makeitem_lv_4` VALUES ('1000');
INSERT INTO `makeitem_lv_4` VALUES ('1008');
INSERT INTO `makeitem_lv_4` VALUES ('1016');
INSERT INTO `makeitem_lv_4` VALUES ('1024');
INSERT INTO `makeitem_lv_4` VALUES ('1032');
INSERT INTO `makeitem_lv_4` VALUES ('1040');
INSERT INTO `makeitem_lv_4` VALUES ('1048');
INSERT INTO `makeitem_lv_4` VALUES ('1056');
INSERT INTO `makeitem_lv_4` VALUES ('6010');
INSERT INTO `makeitem_lv_4` VALUES ('7004');
INSERT INTO `makeitem_lv_4` VALUES ('7012');
INSERT INTO `makeitem_lv_4` VALUES ('7068');
INSERT INTO `makeitem_lv_4` VALUES ('7100');
INSERT INTO `makeitem_lv_4` VALUES ('7116');
INSERT INTO `makeitem_lv_4` VALUES ('7124');
INSERT INTO `makeitem_lv_4` VALUES ('7140');
INSERT INTO `makeitem_lv_4` VALUES ('7156');
INSERT INTO `makeitem_lv_4` VALUES ('7164');
INSERT INTO `makeitem_lv_4` VALUES ('7172');
INSERT INTO `makeitem_lv_4` VALUES ('7188');
INSERT INTO `makeitem_lv_4` VALUES ('7196');
INSERT INTO `makeitem_lv_4` VALUES ('7212');
INSERT INTO `makeitem_lv_4` VALUES ('7220');

-- ----------------------------
-- Table structure for `makeitem_lv_5`
-- ----------------------------
DROP TABLE IF EXISTS `makeitem_lv_5`;
CREATE TABLE `makeitem_lv_5` (
  `item_id` int(10) NOT NULL default '0'
) ENGINE=MyISAM;

-- ----------------------------
-- Records of makeitem_lv_5
-- ----------------------------
INSERT INTO `makeitem_lv_5` VALUES ('701');
INSERT INTO `makeitem_lv_5` VALUES ('713');
INSERT INTO `makeitem_lv_5` VALUES ('721');
INSERT INTO `makeitem_lv_5` VALUES ('729');
INSERT INTO `makeitem_lv_5` VALUES ('737');
INSERT INTO `makeitem_lv_5` VALUES ('745');
INSERT INTO `makeitem_lv_5` VALUES ('753');
INSERT INTO `makeitem_lv_5` VALUES ('905');
INSERT INTO `makeitem_lv_5` VALUES ('913');
INSERT INTO `makeitem_lv_5` VALUES ('921');
INSERT INTO `makeitem_lv_5` VALUES ('930');
INSERT INTO `makeitem_lv_5` VALUES ('939');
INSERT INTO `makeitem_lv_5` VALUES ('947');
INSERT INTO `makeitem_lv_5` VALUES ('955');
INSERT INTO `makeitem_lv_5` VALUES ('963');
INSERT INTO `makeitem_lv_5` VALUES ('971');
INSERT INTO `makeitem_lv_5` VALUES ('988');
INSERT INTO `makeitem_lv_5` VALUES ('996');
INSERT INTO `makeitem_lv_5` VALUES ('1004');
INSERT INTO `makeitem_lv_5` VALUES ('1012');
INSERT INTO `makeitem_lv_5` VALUES ('1020');
INSERT INTO `makeitem_lv_5` VALUES ('1028');
INSERT INTO `makeitem_lv_5` VALUES ('1036');
INSERT INTO `makeitem_lv_5` VALUES ('1044');
INSERT INTO `makeitem_lv_5` VALUES ('1052');
INSERT INTO `makeitem_lv_5` VALUES ('1060');
INSERT INTO `makeitem_lv_5` VALUES ('6023');
INSERT INTO `makeitem_lv_5` VALUES ('7016');
INSERT INTO `makeitem_lv_5` VALUES ('7032');
INSERT INTO `makeitem_lv_5` VALUES ('7048');
INSERT INTO `makeitem_lv_5` VALUES ('7064');
INSERT INTO `makeitem_lv_5` VALUES ('7072');
INSERT INTO `makeitem_lv_5` VALUES ('7096');
INSERT INTO `makeitem_lv_5` VALUES ('7104');
INSERT INTO `makeitem_lv_5` VALUES ('7112');
INSERT INTO `makeitem_lv_5` VALUES ('7120');
INSERT INTO `makeitem_lv_5` VALUES ('7136');
INSERT INTO `makeitem_lv_5` VALUES ('7152');
INSERT INTO `makeitem_lv_5` VALUES ('7168');
INSERT INTO `makeitem_lv_5` VALUES ('7192');
INSERT INTO `makeitem_lv_5` VALUES ('7200');
INSERT INTO `makeitem_lv_5` VALUES ('7208');
INSERT INTO `makeitem_lv_5` VALUES ('7216');
INSERT INTO `makeitem_lv_5` VALUES ('704');
INSERT INTO `makeitem_lv_5` VALUES ('712');
INSERT INTO `makeitem_lv_5` VALUES ('720');
INSERT INTO `makeitem_lv_5` VALUES ('728');
INSERT INTO `makeitem_lv_5` VALUES ('736');
INSERT INTO `makeitem_lv_5` VALUES ('744');
INSERT INTO `makeitem_lv_5` VALUES ('752');
INSERT INTO `makeitem_lv_5` VALUES ('904');
INSERT INTO `makeitem_lv_5` VALUES ('912');
INSERT INTO `makeitem_lv_5` VALUES ('920');
INSERT INTO `makeitem_lv_5` VALUES ('929');
INSERT INTO `makeitem_lv_5` VALUES ('938');
INSERT INTO `makeitem_lv_5` VALUES ('946');
INSERT INTO `makeitem_lv_5` VALUES ('954');
INSERT INTO `makeitem_lv_5` VALUES ('962');
INSERT INTO `makeitem_lv_5` VALUES ('970');
INSERT INTO `makeitem_lv_5` VALUES ('987');
INSERT INTO `makeitem_lv_5` VALUES ('995');
INSERT INTO `makeitem_lv_5` VALUES ('1003');
INSERT INTO `makeitem_lv_5` VALUES ('1011');
INSERT INTO `makeitem_lv_5` VALUES ('1019');
INSERT INTO `makeitem_lv_5` VALUES ('1027');
INSERT INTO `makeitem_lv_5` VALUES ('1035');
INSERT INTO `makeitem_lv_5` VALUES ('1043');
INSERT INTO `makeitem_lv_5` VALUES ('1051');
INSERT INTO `makeitem_lv_5` VALUES ('1059');
INSERT INTO `makeitem_lv_5` VALUES ('7007');
INSERT INTO `makeitem_lv_5` VALUES ('7015');
INSERT INTO `makeitem_lv_5` VALUES ('7023');
INSERT INTO `makeitem_lv_5` VALUES ('7031');
INSERT INTO `makeitem_lv_5` VALUES ('7047');
INSERT INTO `makeitem_lv_5` VALUES ('7063');
INSERT INTO `makeitem_lv_5` VALUES ('7071');
INSERT INTO `makeitem_lv_5` VALUES ('7095');
INSERT INTO `makeitem_lv_5` VALUES ('7111');
INSERT INTO `makeitem_lv_5` VALUES ('7119');
INSERT INTO `makeitem_lv_5` VALUES ('7135');
INSERT INTO `makeitem_lv_5` VALUES ('7151');
INSERT INTO `makeitem_lv_5` VALUES ('7159');
INSERT INTO `makeitem_lv_5` VALUES ('7167');
INSERT INTO `makeitem_lv_5` VALUES ('7207');
INSERT INTO `makeitem_lv_5` VALUES ('7215');
INSERT INTO `makeitem_lv_5` VALUES ('7223');
INSERT INTO `makeitem_lv_5` VALUES ('703');
INSERT INTO `makeitem_lv_5` VALUES ('711');
INSERT INTO `makeitem_lv_5` VALUES ('719');
INSERT INTO `makeitem_lv_5` VALUES ('727');
INSERT INTO `makeitem_lv_5` VALUES ('735');
INSERT INTO `makeitem_lv_5` VALUES ('743');
INSERT INTO `makeitem_lv_5` VALUES ('903');
INSERT INTO `makeitem_lv_5` VALUES ('911');
INSERT INTO `makeitem_lv_5` VALUES ('919');
INSERT INTO `makeitem_lv_5` VALUES ('928');
INSERT INTO `makeitem_lv_5` VALUES ('937');
INSERT INTO `makeitem_lv_5` VALUES ('945');
INSERT INTO `makeitem_lv_5` VALUES ('953');
INSERT INTO `makeitem_lv_5` VALUES ('961');
INSERT INTO `makeitem_lv_5` VALUES ('986');
INSERT INTO `makeitem_lv_5` VALUES ('994');
INSERT INTO `makeitem_lv_5` VALUES ('1002');
INSERT INTO `makeitem_lv_5` VALUES ('1010');
INSERT INTO `makeitem_lv_5` VALUES ('1018');
INSERT INTO `makeitem_lv_5` VALUES ('1026');
INSERT INTO `makeitem_lv_5` VALUES ('1034');
INSERT INTO `makeitem_lv_5` VALUES ('1042');
INSERT INTO `makeitem_lv_5` VALUES ('1050');
INSERT INTO `makeitem_lv_5` VALUES ('1058');
INSERT INTO `makeitem_lv_5` VALUES ('6021');
INSERT INTO `makeitem_lv_5` VALUES ('7006');
INSERT INTO `makeitem_lv_5` VALUES ('7014');
INSERT INTO `makeitem_lv_5` VALUES ('7030');
INSERT INTO `makeitem_lv_5` VALUES ('7038');
INSERT INTO `makeitem_lv_5` VALUES ('7054');
INSERT INTO `makeitem_lv_5` VALUES ('7070');
INSERT INTO `makeitem_lv_5` VALUES ('7110');
INSERT INTO `makeitem_lv_5` VALUES ('7118');
INSERT INTO `makeitem_lv_5` VALUES ('7126');
INSERT INTO `makeitem_lv_5` VALUES ('7150');
INSERT INTO `makeitem_lv_5` VALUES ('7158');
INSERT INTO `makeitem_lv_5` VALUES ('7166');
INSERT INTO `makeitem_lv_5` VALUES ('7182');
INSERT INTO `makeitem_lv_5` VALUES ('7190');
INSERT INTO `makeitem_lv_5` VALUES ('7198');
INSERT INTO `makeitem_lv_5` VALUES ('7206');
INSERT INTO `makeitem_lv_5` VALUES ('7214');
INSERT INTO `makeitem_lv_5` VALUES ('7222');
INSERT INTO `makeitem_lv_5` VALUES ('702');
INSERT INTO `makeitem_lv_5` VALUES ('710');
INSERT INTO `makeitem_lv_5` VALUES ('718');
INSERT INTO `makeitem_lv_5` VALUES ('726');
INSERT INTO `makeitem_lv_5` VALUES ('734');
INSERT INTO `makeitem_lv_5` VALUES ('742');
INSERT INTO `makeitem_lv_5` VALUES ('902');
INSERT INTO `makeitem_lv_5` VALUES ('910');
INSERT INTO `makeitem_lv_5` VALUES ('918');
INSERT INTO `makeitem_lv_5` VALUES ('926');
INSERT INTO `makeitem_lv_5` VALUES ('936');
INSERT INTO `makeitem_lv_5` VALUES ('944');
INSERT INTO `makeitem_lv_5` VALUES ('952');
INSERT INTO `makeitem_lv_5` VALUES ('960');
INSERT INTO `makeitem_lv_5` VALUES ('985');
INSERT INTO `makeitem_lv_5` VALUES ('993');
INSERT INTO `makeitem_lv_5` VALUES ('1001');
INSERT INTO `makeitem_lv_5` VALUES ('1009');
INSERT INTO `makeitem_lv_5` VALUES ('1017');
INSERT INTO `makeitem_lv_5` VALUES ('1025');
INSERT INTO `makeitem_lv_5` VALUES ('1033');
INSERT INTO `makeitem_lv_5` VALUES ('1041');
INSERT INTO `makeitem_lv_5` VALUES ('1049');
INSERT INTO `makeitem_lv_5` VALUES ('1057');
INSERT INTO `makeitem_lv_5` VALUES ('6020');
INSERT INTO `makeitem_lv_5` VALUES ('7005');
INSERT INTO `makeitem_lv_5` VALUES ('7013');
INSERT INTO `makeitem_lv_5` VALUES ('7021');
INSERT INTO `makeitem_lv_5` VALUES ('7053');
INSERT INTO `makeitem_lv_5` VALUES ('7069');
INSERT INTO `makeitem_lv_5` VALUES ('7101');
INSERT INTO `makeitem_lv_5` VALUES ('7117');
INSERT INTO `makeitem_lv_5` VALUES ('7125');
INSERT INTO `makeitem_lv_5` VALUES ('7141');
INSERT INTO `makeitem_lv_5` VALUES ('7149');
INSERT INTO `makeitem_lv_5` VALUES ('7157');
INSERT INTO `makeitem_lv_5` VALUES ('7165');
INSERT INTO `makeitem_lv_5` VALUES ('7189');
INSERT INTO `makeitem_lv_5` VALUES ('7197');
INSERT INTO `makeitem_lv_5` VALUES ('7205');
INSERT INTO `makeitem_lv_5` VALUES ('7213');
INSERT INTO `makeitem_lv_5` VALUES ('7221');
INSERT INTO `makeitem_lv_5` VALUES ('705');
INSERT INTO `makeitem_lv_5` VALUES ('709');
INSERT INTO `makeitem_lv_5` VALUES ('717');
INSERT INTO `makeitem_lv_5` VALUES ('725');
INSERT INTO `makeitem_lv_5` VALUES ('733');
INSERT INTO `makeitem_lv_5` VALUES ('741');
INSERT INTO `makeitem_lv_5` VALUES ('749');
INSERT INTO `makeitem_lv_5` VALUES ('901');
INSERT INTO `makeitem_lv_5` VALUES ('909');
INSERT INTO `makeitem_lv_5` VALUES ('917');
INSERT INTO `makeitem_lv_5` VALUES ('925');
INSERT INTO `makeitem_lv_5` VALUES ('935');
INSERT INTO `makeitem_lv_5` VALUES ('943');
INSERT INTO `makeitem_lv_5` VALUES ('951');
INSERT INTO `makeitem_lv_5` VALUES ('959');
INSERT INTO `makeitem_lv_5` VALUES ('967');
INSERT INTO `makeitem_lv_5` VALUES ('984');
INSERT INTO `makeitem_lv_5` VALUES ('992');
INSERT INTO `makeitem_lv_5` VALUES ('1000');
INSERT INTO `makeitem_lv_5` VALUES ('1008');
INSERT INTO `makeitem_lv_5` VALUES ('1016');
INSERT INTO `makeitem_lv_5` VALUES ('1024');
INSERT INTO `makeitem_lv_5` VALUES ('1032');
INSERT INTO `makeitem_lv_5` VALUES ('1040');
INSERT INTO `makeitem_lv_5` VALUES ('1048');
INSERT INTO `makeitem_lv_5` VALUES ('1056');
INSERT INTO `makeitem_lv_5` VALUES ('6010');
INSERT INTO `makeitem_lv_5` VALUES ('7004');
INSERT INTO `makeitem_lv_5` VALUES ('7012');
INSERT INTO `makeitem_lv_5` VALUES ('7068');
INSERT INTO `makeitem_lv_5` VALUES ('7100');
INSERT INTO `makeitem_lv_5` VALUES ('7116');
INSERT INTO `makeitem_lv_5` VALUES ('7124');
INSERT INTO `makeitem_lv_5` VALUES ('7140');
INSERT INTO `makeitem_lv_5` VALUES ('7156');
INSERT INTO `makeitem_lv_5` VALUES ('7164');
INSERT INTO `makeitem_lv_5` VALUES ('7172');
INSERT INTO `makeitem_lv_5` VALUES ('7188');
INSERT INTO `makeitem_lv_5` VALUES ('7196');
INSERT INTO `makeitem_lv_5` VALUES ('7212');
INSERT INTO `makeitem_lv_5` VALUES ('7220');

-- ----------------------------
-- Table structure for `makeitem_lv_6`
-- ----------------------------
DROP TABLE IF EXISTS `makeitem_lv_6`;
CREATE TABLE `makeitem_lv_6` (
  `item_id` int(10) NOT NULL default '0'
) ENGINE=MyISAM;

-- ----------------------------
-- Records of makeitem_lv_6
-- ----------------------------
INSERT INTO `makeitem_lv_6` VALUES ('706');
INSERT INTO `makeitem_lv_6` VALUES ('714');
INSERT INTO `makeitem_lv_6` VALUES ('722');
INSERT INTO `makeitem_lv_6` VALUES ('730');
INSERT INTO `makeitem_lv_6` VALUES ('738');
INSERT INTO `makeitem_lv_6` VALUES ('746');
INSERT INTO `makeitem_lv_6` VALUES ('754');
INSERT INTO `makeitem_lv_6` VALUES ('906');
INSERT INTO `makeitem_lv_6` VALUES ('914');
INSERT INTO `makeitem_lv_6` VALUES ('922');
INSERT INTO `makeitem_lv_6` VALUES ('931');
INSERT INTO `makeitem_lv_6` VALUES ('940');
INSERT INTO `makeitem_lv_6` VALUES ('948');
INSERT INTO `makeitem_lv_6` VALUES ('956');
INSERT INTO `makeitem_lv_6` VALUES ('964');
INSERT INTO `makeitem_lv_6` VALUES ('972');
INSERT INTO `makeitem_lv_6` VALUES ('989');
INSERT INTO `makeitem_lv_6` VALUES ('997');
INSERT INTO `makeitem_lv_6` VALUES ('1005');
INSERT INTO `makeitem_lv_6` VALUES ('1013');
INSERT INTO `makeitem_lv_6` VALUES ('1021');
INSERT INTO `makeitem_lv_6` VALUES ('1037');
INSERT INTO `makeitem_lv_6` VALUES ('1045');
INSERT INTO `makeitem_lv_6` VALUES ('1053');
INSERT INTO `makeitem_lv_6` VALUES ('1061');
INSERT INTO `makeitem_lv_6` VALUES ('7001');
INSERT INTO `makeitem_lv_6` VALUES ('7009');
INSERT INTO `makeitem_lv_6` VALUES ('7017');
INSERT INTO `makeitem_lv_6` VALUES ('7033');
INSERT INTO `makeitem_lv_6` VALUES ('7041');
INSERT INTO `makeitem_lv_6` VALUES ('7049');
INSERT INTO `makeitem_lv_6` VALUES ('7065');
INSERT INTO `makeitem_lv_6` VALUES ('7097');
INSERT INTO `makeitem_lv_6` VALUES ('7105');
INSERT INTO `makeitem_lv_6` VALUES ('7121');
INSERT INTO `makeitem_lv_6` VALUES ('7137');
INSERT INTO `makeitem_lv_6` VALUES ('7153');
INSERT INTO `makeitem_lv_6` VALUES ('7161');
INSERT INTO `makeitem_lv_6` VALUES ('7193');
INSERT INTO `makeitem_lv_6` VALUES ('7201');
INSERT INTO `makeitem_lv_6` VALUES ('7209');
INSERT INTO `makeitem_lv_6` VALUES ('7217');
INSERT INTO `makeitem_lv_6` VALUES ('7225');
INSERT INTO `makeitem_lv_6` VALUES ('701');
INSERT INTO `makeitem_lv_6` VALUES ('713');
INSERT INTO `makeitem_lv_6` VALUES ('721');
INSERT INTO `makeitem_lv_6` VALUES ('729');
INSERT INTO `makeitem_lv_6` VALUES ('737');
INSERT INTO `makeitem_lv_6` VALUES ('745');
INSERT INTO `makeitem_lv_6` VALUES ('753');
INSERT INTO `makeitem_lv_6` VALUES ('905');
INSERT INTO `makeitem_lv_6` VALUES ('913');
INSERT INTO `makeitem_lv_6` VALUES ('921');
INSERT INTO `makeitem_lv_6` VALUES ('930');
INSERT INTO `makeitem_lv_6` VALUES ('939');
INSERT INTO `makeitem_lv_6` VALUES ('947');
INSERT INTO `makeitem_lv_6` VALUES ('955');
INSERT INTO `makeitem_lv_6` VALUES ('963');
INSERT INTO `makeitem_lv_6` VALUES ('971');
INSERT INTO `makeitem_lv_6` VALUES ('988');
INSERT INTO `makeitem_lv_6` VALUES ('996');
INSERT INTO `makeitem_lv_6` VALUES ('1004');
INSERT INTO `makeitem_lv_6` VALUES ('1012');
INSERT INTO `makeitem_lv_6` VALUES ('1020');
INSERT INTO `makeitem_lv_6` VALUES ('1028');
INSERT INTO `makeitem_lv_6` VALUES ('1036');
INSERT INTO `makeitem_lv_6` VALUES ('1044');
INSERT INTO `makeitem_lv_6` VALUES ('1052');
INSERT INTO `makeitem_lv_6` VALUES ('1060');
INSERT INTO `makeitem_lv_6` VALUES ('6023');
INSERT INTO `makeitem_lv_6` VALUES ('7016');
INSERT INTO `makeitem_lv_6` VALUES ('7032');
INSERT INTO `makeitem_lv_6` VALUES ('7048');
INSERT INTO `makeitem_lv_6` VALUES ('7064');
INSERT INTO `makeitem_lv_6` VALUES ('7072');
INSERT INTO `makeitem_lv_6` VALUES ('7096');
INSERT INTO `makeitem_lv_6` VALUES ('7104');
INSERT INTO `makeitem_lv_6` VALUES ('7112');
INSERT INTO `makeitem_lv_6` VALUES ('7120');
INSERT INTO `makeitem_lv_6` VALUES ('7136');
INSERT INTO `makeitem_lv_6` VALUES ('7152');
INSERT INTO `makeitem_lv_6` VALUES ('7168');
INSERT INTO `makeitem_lv_6` VALUES ('7192');
INSERT INTO `makeitem_lv_6` VALUES ('7200');
INSERT INTO `makeitem_lv_6` VALUES ('7208');
INSERT INTO `makeitem_lv_6` VALUES ('7216');
INSERT INTO `makeitem_lv_6` VALUES ('704');
INSERT INTO `makeitem_lv_6` VALUES ('712');
INSERT INTO `makeitem_lv_6` VALUES ('720');
INSERT INTO `makeitem_lv_6` VALUES ('728');
INSERT INTO `makeitem_lv_6` VALUES ('736');
INSERT INTO `makeitem_lv_6` VALUES ('744');
INSERT INTO `makeitem_lv_6` VALUES ('752');
INSERT INTO `makeitem_lv_6` VALUES ('904');
INSERT INTO `makeitem_lv_6` VALUES ('912');
INSERT INTO `makeitem_lv_6` VALUES ('920');
INSERT INTO `makeitem_lv_6` VALUES ('929');
INSERT INTO `makeitem_lv_6` VALUES ('938');
INSERT INTO `makeitem_lv_6` VALUES ('946');
INSERT INTO `makeitem_lv_6` VALUES ('954');
INSERT INTO `makeitem_lv_6` VALUES ('962');
INSERT INTO `makeitem_lv_6` VALUES ('970');
INSERT INTO `makeitem_lv_6` VALUES ('987');
INSERT INTO `makeitem_lv_6` VALUES ('995');
INSERT INTO `makeitem_lv_6` VALUES ('1003');
INSERT INTO `makeitem_lv_6` VALUES ('1011');
INSERT INTO `makeitem_lv_6` VALUES ('1019');
INSERT INTO `makeitem_lv_6` VALUES ('1027');
INSERT INTO `makeitem_lv_6` VALUES ('1035');
INSERT INTO `makeitem_lv_6` VALUES ('1043');
INSERT INTO `makeitem_lv_6` VALUES ('1051');
INSERT INTO `makeitem_lv_6` VALUES ('1059');
INSERT INTO `makeitem_lv_6` VALUES ('7007');
INSERT INTO `makeitem_lv_6` VALUES ('7015');
INSERT INTO `makeitem_lv_6` VALUES ('7023');
INSERT INTO `makeitem_lv_6` VALUES ('7031');
INSERT INTO `makeitem_lv_6` VALUES ('7047');
INSERT INTO `makeitem_lv_6` VALUES ('7063');
INSERT INTO `makeitem_lv_6` VALUES ('7071');
INSERT INTO `makeitem_lv_6` VALUES ('7095');
INSERT INTO `makeitem_lv_6` VALUES ('7111');
INSERT INTO `makeitem_lv_6` VALUES ('7119');
INSERT INTO `makeitem_lv_6` VALUES ('7135');
INSERT INTO `makeitem_lv_6` VALUES ('7151');
INSERT INTO `makeitem_lv_6` VALUES ('7159');
INSERT INTO `makeitem_lv_6` VALUES ('7167');
INSERT INTO `makeitem_lv_6` VALUES ('7207');
INSERT INTO `makeitem_lv_6` VALUES ('7215');
INSERT INTO `makeitem_lv_6` VALUES ('7223');
INSERT INTO `makeitem_lv_6` VALUES ('703');
INSERT INTO `makeitem_lv_6` VALUES ('711');
INSERT INTO `makeitem_lv_6` VALUES ('719');
INSERT INTO `makeitem_lv_6` VALUES ('727');
INSERT INTO `makeitem_lv_6` VALUES ('735');
INSERT INTO `makeitem_lv_6` VALUES ('743');
INSERT INTO `makeitem_lv_6` VALUES ('903');
INSERT INTO `makeitem_lv_6` VALUES ('911');
INSERT INTO `makeitem_lv_6` VALUES ('919');
INSERT INTO `makeitem_lv_6` VALUES ('928');
INSERT INTO `makeitem_lv_6` VALUES ('937');
INSERT INTO `makeitem_lv_6` VALUES ('945');
INSERT INTO `makeitem_lv_6` VALUES ('953');
INSERT INTO `makeitem_lv_6` VALUES ('961');
INSERT INTO `makeitem_lv_6` VALUES ('986');
INSERT INTO `makeitem_lv_6` VALUES ('994');
INSERT INTO `makeitem_lv_6` VALUES ('1002');
INSERT INTO `makeitem_lv_6` VALUES ('1010');
INSERT INTO `makeitem_lv_6` VALUES ('1018');
INSERT INTO `makeitem_lv_6` VALUES ('1026');
INSERT INTO `makeitem_lv_6` VALUES ('1034');
INSERT INTO `makeitem_lv_6` VALUES ('1042');
INSERT INTO `makeitem_lv_6` VALUES ('1050');
INSERT INTO `makeitem_lv_6` VALUES ('1058');
INSERT INTO `makeitem_lv_6` VALUES ('6021');
INSERT INTO `makeitem_lv_6` VALUES ('7006');
INSERT INTO `makeitem_lv_6` VALUES ('7014');
INSERT INTO `makeitem_lv_6` VALUES ('7030');
INSERT INTO `makeitem_lv_6` VALUES ('7038');
INSERT INTO `makeitem_lv_6` VALUES ('7054');
INSERT INTO `makeitem_lv_6` VALUES ('7070');
INSERT INTO `makeitem_lv_6` VALUES ('7110');
INSERT INTO `makeitem_lv_6` VALUES ('7118');
INSERT INTO `makeitem_lv_6` VALUES ('7126');
INSERT INTO `makeitem_lv_6` VALUES ('7150');
INSERT INTO `makeitem_lv_6` VALUES ('7158');
INSERT INTO `makeitem_lv_6` VALUES ('7166');
INSERT INTO `makeitem_lv_6` VALUES ('7182');
INSERT INTO `makeitem_lv_6` VALUES ('7190');
INSERT INTO `makeitem_lv_6` VALUES ('7198');
INSERT INTO `makeitem_lv_6` VALUES ('7206');
INSERT INTO `makeitem_lv_6` VALUES ('7214');
INSERT INTO `makeitem_lv_6` VALUES ('7222');
INSERT INTO `makeitem_lv_6` VALUES ('702');
INSERT INTO `makeitem_lv_6` VALUES ('710');
INSERT INTO `makeitem_lv_6` VALUES ('718');
INSERT INTO `makeitem_lv_6` VALUES ('726');
INSERT INTO `makeitem_lv_6` VALUES ('734');
INSERT INTO `makeitem_lv_6` VALUES ('742');
INSERT INTO `makeitem_lv_6` VALUES ('902');
INSERT INTO `makeitem_lv_6` VALUES ('910');
INSERT INTO `makeitem_lv_6` VALUES ('918');
INSERT INTO `makeitem_lv_6` VALUES ('926');
INSERT INTO `makeitem_lv_6` VALUES ('936');
INSERT INTO `makeitem_lv_6` VALUES ('944');
INSERT INTO `makeitem_lv_6` VALUES ('952');
INSERT INTO `makeitem_lv_6` VALUES ('960');
INSERT INTO `makeitem_lv_6` VALUES ('985');
INSERT INTO `makeitem_lv_6` VALUES ('993');
INSERT INTO `makeitem_lv_6` VALUES ('1001');
INSERT INTO `makeitem_lv_6` VALUES ('1009');
INSERT INTO `makeitem_lv_6` VALUES ('1017');
INSERT INTO `makeitem_lv_6` VALUES ('1025');
INSERT INTO `makeitem_lv_6` VALUES ('1033');
INSERT INTO `makeitem_lv_6` VALUES ('1041');
INSERT INTO `makeitem_lv_6` VALUES ('1049');
INSERT INTO `makeitem_lv_6` VALUES ('1057');
INSERT INTO `makeitem_lv_6` VALUES ('6020');
INSERT INTO `makeitem_lv_6` VALUES ('7005');
INSERT INTO `makeitem_lv_6` VALUES ('7013');
INSERT INTO `makeitem_lv_6` VALUES ('7021');
INSERT INTO `makeitem_lv_6` VALUES ('7053');
INSERT INTO `makeitem_lv_6` VALUES ('7069');
INSERT INTO `makeitem_lv_6` VALUES ('7101');
INSERT INTO `makeitem_lv_6` VALUES ('7117');
INSERT INTO `makeitem_lv_6` VALUES ('7125');
INSERT INTO `makeitem_lv_6` VALUES ('7141');
INSERT INTO `makeitem_lv_6` VALUES ('7149');
INSERT INTO `makeitem_lv_6` VALUES ('7157');
INSERT INTO `makeitem_lv_6` VALUES ('7165');
INSERT INTO `makeitem_lv_6` VALUES ('7189');
INSERT INTO `makeitem_lv_6` VALUES ('7197');
INSERT INTO `makeitem_lv_6` VALUES ('7205');
INSERT INTO `makeitem_lv_6` VALUES ('7213');
INSERT INTO `makeitem_lv_6` VALUES ('7221');
INSERT INTO `makeitem_lv_6` VALUES ('705');
INSERT INTO `makeitem_lv_6` VALUES ('709');
INSERT INTO `makeitem_lv_6` VALUES ('717');
INSERT INTO `makeitem_lv_6` VALUES ('725');
INSERT INTO `makeitem_lv_6` VALUES ('733');
INSERT INTO `makeitem_lv_6` VALUES ('741');
INSERT INTO `makeitem_lv_6` VALUES ('749');
INSERT INTO `makeitem_lv_6` VALUES ('901');
INSERT INTO `makeitem_lv_6` VALUES ('909');
INSERT INTO `makeitem_lv_6` VALUES ('917');
INSERT INTO `makeitem_lv_6` VALUES ('925');
INSERT INTO `makeitem_lv_6` VALUES ('935');
INSERT INTO `makeitem_lv_6` VALUES ('943');
INSERT INTO `makeitem_lv_6` VALUES ('951');
INSERT INTO `makeitem_lv_6` VALUES ('959');
INSERT INTO `makeitem_lv_6` VALUES ('967');
INSERT INTO `makeitem_lv_6` VALUES ('984');
INSERT INTO `makeitem_lv_6` VALUES ('992');
INSERT INTO `makeitem_lv_6` VALUES ('1000');
INSERT INTO `makeitem_lv_6` VALUES ('1008');
INSERT INTO `makeitem_lv_6` VALUES ('1016');
INSERT INTO `makeitem_lv_6` VALUES ('1024');
INSERT INTO `makeitem_lv_6` VALUES ('1032');
INSERT INTO `makeitem_lv_6` VALUES ('1040');
INSERT INTO `makeitem_lv_6` VALUES ('1048');
INSERT INTO `makeitem_lv_6` VALUES ('1056');
INSERT INTO `makeitem_lv_6` VALUES ('6010');
INSERT INTO `makeitem_lv_6` VALUES ('7004');
INSERT INTO `makeitem_lv_6` VALUES ('7012');
INSERT INTO `makeitem_lv_6` VALUES ('7068');
INSERT INTO `makeitem_lv_6` VALUES ('7100');
INSERT INTO `makeitem_lv_6` VALUES ('7116');
INSERT INTO `makeitem_lv_6` VALUES ('7124');
INSERT INTO `makeitem_lv_6` VALUES ('7140');
INSERT INTO `makeitem_lv_6` VALUES ('7156');
INSERT INTO `makeitem_lv_6` VALUES ('7164');
INSERT INTO `makeitem_lv_6` VALUES ('7172');
INSERT INTO `makeitem_lv_6` VALUES ('7188');
INSERT INTO `makeitem_lv_6` VALUES ('7196');
INSERT INTO `makeitem_lv_6` VALUES ('7212');
INSERT INTO `makeitem_lv_6` VALUES ('7220');

-- ----------------------------
-- Table structure for `makeitem_lv_7`
-- ----------------------------
DROP TABLE IF EXISTS `makeitem_lv_7`;
CREATE TABLE `makeitem_lv_7` (
  `item_id` int(10) NOT NULL default '0'
) ENGINE=MyISAM;

-- ----------------------------
-- Records of makeitem_lv_7
-- ----------------------------
INSERT INTO `makeitem_lv_7` VALUES ('707');
INSERT INTO `makeitem_lv_7` VALUES ('715');
INSERT INTO `makeitem_lv_7` VALUES ('723');
INSERT INTO `makeitem_lv_7` VALUES ('731');
INSERT INTO `makeitem_lv_7` VALUES ('739');
INSERT INTO `makeitem_lv_7` VALUES ('747');
INSERT INTO `makeitem_lv_7` VALUES ('756');
INSERT INTO `makeitem_lv_7` VALUES ('907');
INSERT INTO `makeitem_lv_7` VALUES ('915');
INSERT INTO `makeitem_lv_7` VALUES ('923');
INSERT INTO `makeitem_lv_7` VALUES ('932');
INSERT INTO `makeitem_lv_7` VALUES ('941');
INSERT INTO `makeitem_lv_7` VALUES ('949');
INSERT INTO `makeitem_lv_7` VALUES ('957');
INSERT INTO `makeitem_lv_7` VALUES ('965');
INSERT INTO `makeitem_lv_7` VALUES ('990');
INSERT INTO `makeitem_lv_7` VALUES ('998');
INSERT INTO `makeitem_lv_7` VALUES ('1006');
INSERT INTO `makeitem_lv_7` VALUES ('1022');
INSERT INTO `makeitem_lv_7` VALUES ('1038');
INSERT INTO `makeitem_lv_7` VALUES ('1046');
INSERT INTO `makeitem_lv_7` VALUES ('1054');
INSERT INTO `makeitem_lv_7` VALUES ('1062');
INSERT INTO `makeitem_lv_7` VALUES ('7002');
INSERT INTO `makeitem_lv_7` VALUES ('7010');
INSERT INTO `makeitem_lv_7` VALUES ('7026');
INSERT INTO `makeitem_lv_7` VALUES ('7034');
INSERT INTO `makeitem_lv_7` VALUES ('7066');
INSERT INTO `makeitem_lv_7` VALUES ('7098');
INSERT INTO `makeitem_lv_7` VALUES ('7106');
INSERT INTO `makeitem_lv_7` VALUES ('7122');
INSERT INTO `makeitem_lv_7` VALUES ('7138');
INSERT INTO `makeitem_lv_7` VALUES ('7154');
INSERT INTO `makeitem_lv_7` VALUES ('7162');
INSERT INTO `makeitem_lv_7` VALUES ('7186');
INSERT INTO `makeitem_lv_7` VALUES ('7194');
INSERT INTO `makeitem_lv_7` VALUES ('7202');
INSERT INTO `makeitem_lv_7` VALUES ('7210');
INSERT INTO `makeitem_lv_7` VALUES ('7218');
INSERT INTO `makeitem_lv_7` VALUES ('7226');
INSERT INTO `makeitem_lv_7` VALUES ('706');
INSERT INTO `makeitem_lv_7` VALUES ('714');
INSERT INTO `makeitem_lv_7` VALUES ('722');
INSERT INTO `makeitem_lv_7` VALUES ('730');
INSERT INTO `makeitem_lv_7` VALUES ('738');
INSERT INTO `makeitem_lv_7` VALUES ('746');
INSERT INTO `makeitem_lv_7` VALUES ('754');
INSERT INTO `makeitem_lv_7` VALUES ('906');
INSERT INTO `makeitem_lv_7` VALUES ('914');
INSERT INTO `makeitem_lv_7` VALUES ('922');
INSERT INTO `makeitem_lv_7` VALUES ('931');
INSERT INTO `makeitem_lv_7` VALUES ('940');
INSERT INTO `makeitem_lv_7` VALUES ('948');
INSERT INTO `makeitem_lv_7` VALUES ('956');
INSERT INTO `makeitem_lv_7` VALUES ('964');
INSERT INTO `makeitem_lv_7` VALUES ('972');
INSERT INTO `makeitem_lv_7` VALUES ('989');
INSERT INTO `makeitem_lv_7` VALUES ('997');
INSERT INTO `makeitem_lv_7` VALUES ('1005');
INSERT INTO `makeitem_lv_7` VALUES ('1013');
INSERT INTO `makeitem_lv_7` VALUES ('1021');
INSERT INTO `makeitem_lv_7` VALUES ('1037');
INSERT INTO `makeitem_lv_7` VALUES ('1045');
INSERT INTO `makeitem_lv_7` VALUES ('1053');
INSERT INTO `makeitem_lv_7` VALUES ('1061');
INSERT INTO `makeitem_lv_7` VALUES ('7001');
INSERT INTO `makeitem_lv_7` VALUES ('7009');
INSERT INTO `makeitem_lv_7` VALUES ('7017');
INSERT INTO `makeitem_lv_7` VALUES ('7033');
INSERT INTO `makeitem_lv_7` VALUES ('7041');
INSERT INTO `makeitem_lv_7` VALUES ('7049');
INSERT INTO `makeitem_lv_7` VALUES ('7065');
INSERT INTO `makeitem_lv_7` VALUES ('7097');
INSERT INTO `makeitem_lv_7` VALUES ('7105');
INSERT INTO `makeitem_lv_7` VALUES ('7121');
INSERT INTO `makeitem_lv_7` VALUES ('7137');
INSERT INTO `makeitem_lv_7` VALUES ('7153');
INSERT INTO `makeitem_lv_7` VALUES ('7161');
INSERT INTO `makeitem_lv_7` VALUES ('7193');
INSERT INTO `makeitem_lv_7` VALUES ('7201');
INSERT INTO `makeitem_lv_7` VALUES ('7209');
INSERT INTO `makeitem_lv_7` VALUES ('7217');
INSERT INTO `makeitem_lv_7` VALUES ('7225');
INSERT INTO `makeitem_lv_7` VALUES ('701');
INSERT INTO `makeitem_lv_7` VALUES ('713');
INSERT INTO `makeitem_lv_7` VALUES ('721');
INSERT INTO `makeitem_lv_7` VALUES ('729');
INSERT INTO `makeitem_lv_7` VALUES ('737');
INSERT INTO `makeitem_lv_7` VALUES ('745');
INSERT INTO `makeitem_lv_7` VALUES ('753');
INSERT INTO `makeitem_lv_7` VALUES ('905');
INSERT INTO `makeitem_lv_7` VALUES ('913');
INSERT INTO `makeitem_lv_7` VALUES ('921');
INSERT INTO `makeitem_lv_7` VALUES ('930');
INSERT INTO `makeitem_lv_7` VALUES ('939');
INSERT INTO `makeitem_lv_7` VALUES ('947');
INSERT INTO `makeitem_lv_7` VALUES ('955');
INSERT INTO `makeitem_lv_7` VALUES ('963');
INSERT INTO `makeitem_lv_7` VALUES ('971');
INSERT INTO `makeitem_lv_7` VALUES ('988');
INSERT INTO `makeitem_lv_7` VALUES ('996');
INSERT INTO `makeitem_lv_7` VALUES ('1004');
INSERT INTO `makeitem_lv_7` VALUES ('1012');
INSERT INTO `makeitem_lv_7` VALUES ('1020');
INSERT INTO `makeitem_lv_7` VALUES ('1028');
INSERT INTO `makeitem_lv_7` VALUES ('1036');
INSERT INTO `makeitem_lv_7` VALUES ('1044');
INSERT INTO `makeitem_lv_7` VALUES ('1052');
INSERT INTO `makeitem_lv_7` VALUES ('1060');
INSERT INTO `makeitem_lv_7` VALUES ('6023');
INSERT INTO `makeitem_lv_7` VALUES ('7016');
INSERT INTO `makeitem_lv_7` VALUES ('7032');
INSERT INTO `makeitem_lv_7` VALUES ('7048');
INSERT INTO `makeitem_lv_7` VALUES ('7064');
INSERT INTO `makeitem_lv_7` VALUES ('7072');
INSERT INTO `makeitem_lv_7` VALUES ('7096');
INSERT INTO `makeitem_lv_7` VALUES ('7104');
INSERT INTO `makeitem_lv_7` VALUES ('7112');
INSERT INTO `makeitem_lv_7` VALUES ('7120');
INSERT INTO `makeitem_lv_7` VALUES ('7136');
INSERT INTO `makeitem_lv_7` VALUES ('7152');
INSERT INTO `makeitem_lv_7` VALUES ('7168');
INSERT INTO `makeitem_lv_7` VALUES ('7192');
INSERT INTO `makeitem_lv_7` VALUES ('7200');
INSERT INTO `makeitem_lv_7` VALUES ('7208');
INSERT INTO `makeitem_lv_7` VALUES ('7216');
INSERT INTO `makeitem_lv_7` VALUES ('704');
INSERT INTO `makeitem_lv_7` VALUES ('712');
INSERT INTO `makeitem_lv_7` VALUES ('720');
INSERT INTO `makeitem_lv_7` VALUES ('728');
INSERT INTO `makeitem_lv_7` VALUES ('736');
INSERT INTO `makeitem_lv_7` VALUES ('744');
INSERT INTO `makeitem_lv_7` VALUES ('752');
INSERT INTO `makeitem_lv_7` VALUES ('904');
INSERT INTO `makeitem_lv_7` VALUES ('912');
INSERT INTO `makeitem_lv_7` VALUES ('920');
INSERT INTO `makeitem_lv_7` VALUES ('929');
INSERT INTO `makeitem_lv_7` VALUES ('938');
INSERT INTO `makeitem_lv_7` VALUES ('946');
INSERT INTO `makeitem_lv_7` VALUES ('954');
INSERT INTO `makeitem_lv_7` VALUES ('962');
INSERT INTO `makeitem_lv_7` VALUES ('970');
INSERT INTO `makeitem_lv_7` VALUES ('987');
INSERT INTO `makeitem_lv_7` VALUES ('995');
INSERT INTO `makeitem_lv_7` VALUES ('1003');
INSERT INTO `makeitem_lv_7` VALUES ('1011');
INSERT INTO `makeitem_lv_7` VALUES ('1019');
INSERT INTO `makeitem_lv_7` VALUES ('1027');
INSERT INTO `makeitem_lv_7` VALUES ('1035');
INSERT INTO `makeitem_lv_7` VALUES ('1043');
INSERT INTO `makeitem_lv_7` VALUES ('1051');
INSERT INTO `makeitem_lv_7` VALUES ('1059');
INSERT INTO `makeitem_lv_7` VALUES ('7007');
INSERT INTO `makeitem_lv_7` VALUES ('7015');
INSERT INTO `makeitem_lv_7` VALUES ('7023');
INSERT INTO `makeitem_lv_7` VALUES ('7031');
INSERT INTO `makeitem_lv_7` VALUES ('7047');
INSERT INTO `makeitem_lv_7` VALUES ('7063');
INSERT INTO `makeitem_lv_7` VALUES ('7071');
INSERT INTO `makeitem_lv_7` VALUES ('7095');
INSERT INTO `makeitem_lv_7` VALUES ('7111');
INSERT INTO `makeitem_lv_7` VALUES ('7119');
INSERT INTO `makeitem_lv_7` VALUES ('7135');
INSERT INTO `makeitem_lv_7` VALUES ('7151');
INSERT INTO `makeitem_lv_7` VALUES ('7159');
INSERT INTO `makeitem_lv_7` VALUES ('7167');
INSERT INTO `makeitem_lv_7` VALUES ('7207');
INSERT INTO `makeitem_lv_7` VALUES ('7215');
INSERT INTO `makeitem_lv_7` VALUES ('7223');
INSERT INTO `makeitem_lv_7` VALUES ('703');
INSERT INTO `makeitem_lv_7` VALUES ('711');
INSERT INTO `makeitem_lv_7` VALUES ('719');
INSERT INTO `makeitem_lv_7` VALUES ('727');
INSERT INTO `makeitem_lv_7` VALUES ('735');
INSERT INTO `makeitem_lv_7` VALUES ('743');
INSERT INTO `makeitem_lv_7` VALUES ('903');
INSERT INTO `makeitem_lv_7` VALUES ('911');
INSERT INTO `makeitem_lv_7` VALUES ('919');
INSERT INTO `makeitem_lv_7` VALUES ('928');
INSERT INTO `makeitem_lv_7` VALUES ('937');
INSERT INTO `makeitem_lv_7` VALUES ('945');
INSERT INTO `makeitem_lv_7` VALUES ('953');
INSERT INTO `makeitem_lv_7` VALUES ('961');
INSERT INTO `makeitem_lv_7` VALUES ('986');
INSERT INTO `makeitem_lv_7` VALUES ('994');
INSERT INTO `makeitem_lv_7` VALUES ('1002');
INSERT INTO `makeitem_lv_7` VALUES ('1010');
INSERT INTO `makeitem_lv_7` VALUES ('1018');
INSERT INTO `makeitem_lv_7` VALUES ('1026');
INSERT INTO `makeitem_lv_7` VALUES ('1034');
INSERT INTO `makeitem_lv_7` VALUES ('1042');
INSERT INTO `makeitem_lv_7` VALUES ('1050');
INSERT INTO `makeitem_lv_7` VALUES ('1058');
INSERT INTO `makeitem_lv_7` VALUES ('6021');
INSERT INTO `makeitem_lv_7` VALUES ('7006');
INSERT INTO `makeitem_lv_7` VALUES ('7014');
INSERT INTO `makeitem_lv_7` VALUES ('7030');
INSERT INTO `makeitem_lv_7` VALUES ('7038');
INSERT INTO `makeitem_lv_7` VALUES ('7054');
INSERT INTO `makeitem_lv_7` VALUES ('7070');
INSERT INTO `makeitem_lv_7` VALUES ('7110');
INSERT INTO `makeitem_lv_7` VALUES ('7118');
INSERT INTO `makeitem_lv_7` VALUES ('7126');
INSERT INTO `makeitem_lv_7` VALUES ('7150');
INSERT INTO `makeitem_lv_7` VALUES ('7158');
INSERT INTO `makeitem_lv_7` VALUES ('7166');
INSERT INTO `makeitem_lv_7` VALUES ('7182');
INSERT INTO `makeitem_lv_7` VALUES ('7190');
INSERT INTO `makeitem_lv_7` VALUES ('7198');
INSERT INTO `makeitem_lv_7` VALUES ('7206');
INSERT INTO `makeitem_lv_7` VALUES ('7214');
INSERT INTO `makeitem_lv_7` VALUES ('7222');
INSERT INTO `makeitem_lv_7` VALUES ('702');
INSERT INTO `makeitem_lv_7` VALUES ('710');
INSERT INTO `makeitem_lv_7` VALUES ('718');
INSERT INTO `makeitem_lv_7` VALUES ('726');
INSERT INTO `makeitem_lv_7` VALUES ('734');
INSERT INTO `makeitem_lv_7` VALUES ('742');
INSERT INTO `makeitem_lv_7` VALUES ('902');
INSERT INTO `makeitem_lv_7` VALUES ('910');
INSERT INTO `makeitem_lv_7` VALUES ('918');
INSERT INTO `makeitem_lv_7` VALUES ('926');
INSERT INTO `makeitem_lv_7` VALUES ('936');
INSERT INTO `makeitem_lv_7` VALUES ('944');
INSERT INTO `makeitem_lv_7` VALUES ('952');
INSERT INTO `makeitem_lv_7` VALUES ('960');
INSERT INTO `makeitem_lv_7` VALUES ('985');
INSERT INTO `makeitem_lv_7` VALUES ('993');
INSERT INTO `makeitem_lv_7` VALUES ('1001');
INSERT INTO `makeitem_lv_7` VALUES ('1009');
INSERT INTO `makeitem_lv_7` VALUES ('1017');
INSERT INTO `makeitem_lv_7` VALUES ('1025');
INSERT INTO `makeitem_lv_7` VALUES ('1033');
INSERT INTO `makeitem_lv_7` VALUES ('1041');
INSERT INTO `makeitem_lv_7` VALUES ('1049');
INSERT INTO `makeitem_lv_7` VALUES ('1057');
INSERT INTO `makeitem_lv_7` VALUES ('6020');
INSERT INTO `makeitem_lv_7` VALUES ('7005');
INSERT INTO `makeitem_lv_7` VALUES ('7013');
INSERT INTO `makeitem_lv_7` VALUES ('7021');
INSERT INTO `makeitem_lv_7` VALUES ('7053');
INSERT INTO `makeitem_lv_7` VALUES ('7069');
INSERT INTO `makeitem_lv_7` VALUES ('7101');
INSERT INTO `makeitem_lv_7` VALUES ('7117');
INSERT INTO `makeitem_lv_7` VALUES ('7125');
INSERT INTO `makeitem_lv_7` VALUES ('7141');
INSERT INTO `makeitem_lv_7` VALUES ('7149');
INSERT INTO `makeitem_lv_7` VALUES ('7157');
INSERT INTO `makeitem_lv_7` VALUES ('7165');
INSERT INTO `makeitem_lv_7` VALUES ('7189');
INSERT INTO `makeitem_lv_7` VALUES ('7197');
INSERT INTO `makeitem_lv_7` VALUES ('7205');
INSERT INTO `makeitem_lv_7` VALUES ('7213');
INSERT INTO `makeitem_lv_7` VALUES ('7221');
INSERT INTO `makeitem_lv_7` VALUES ('705');
INSERT INTO `makeitem_lv_7` VALUES ('709');
INSERT INTO `makeitem_lv_7` VALUES ('717');
INSERT INTO `makeitem_lv_7` VALUES ('725');
INSERT INTO `makeitem_lv_7` VALUES ('733');
INSERT INTO `makeitem_lv_7` VALUES ('741');
INSERT INTO `makeitem_lv_7` VALUES ('749');
INSERT INTO `makeitem_lv_7` VALUES ('901');
INSERT INTO `makeitem_lv_7` VALUES ('909');
INSERT INTO `makeitem_lv_7` VALUES ('917');
INSERT INTO `makeitem_lv_7` VALUES ('925');
INSERT INTO `makeitem_lv_7` VALUES ('935');
INSERT INTO `makeitem_lv_7` VALUES ('943');
INSERT INTO `makeitem_lv_7` VALUES ('951');
INSERT INTO `makeitem_lv_7` VALUES ('959');
INSERT INTO `makeitem_lv_7` VALUES ('967');
INSERT INTO `makeitem_lv_7` VALUES ('984');
INSERT INTO `makeitem_lv_7` VALUES ('992');
INSERT INTO `makeitem_lv_7` VALUES ('1000');
INSERT INTO `makeitem_lv_7` VALUES ('1008');
INSERT INTO `makeitem_lv_7` VALUES ('1016');
INSERT INTO `makeitem_lv_7` VALUES ('1024');
INSERT INTO `makeitem_lv_7` VALUES ('1032');
INSERT INTO `makeitem_lv_7` VALUES ('1040');
INSERT INTO `makeitem_lv_7` VALUES ('1048');
INSERT INTO `makeitem_lv_7` VALUES ('1056');
INSERT INTO `makeitem_lv_7` VALUES ('6010');
INSERT INTO `makeitem_lv_7` VALUES ('7004');
INSERT INTO `makeitem_lv_7` VALUES ('7012');
INSERT INTO `makeitem_lv_7` VALUES ('7068');
INSERT INTO `makeitem_lv_7` VALUES ('7100');
INSERT INTO `makeitem_lv_7` VALUES ('7116');
INSERT INTO `makeitem_lv_7` VALUES ('7124');
INSERT INTO `makeitem_lv_7` VALUES ('7140');
INSERT INTO `makeitem_lv_7` VALUES ('7156');
INSERT INTO `makeitem_lv_7` VALUES ('7164');
INSERT INTO `makeitem_lv_7` VALUES ('7172');
INSERT INTO `makeitem_lv_7` VALUES ('7188');
INSERT INTO `makeitem_lv_7` VALUES ('7196');
INSERT INTO `makeitem_lv_7` VALUES ('7212');
INSERT INTO `makeitem_lv_7` VALUES ('7220');

-- ----------------------------
-- Table structure for `makeitem_lv_8`
-- ----------------------------
DROP TABLE IF EXISTS `makeitem_lv_8`;
CREATE TABLE `makeitem_lv_8` (
  `item_id` int(10) NOT NULL default '0'
) ENGINE=MyISAM;

-- ----------------------------
-- Records of makeitem_lv_8
-- ----------------------------
INSERT INTO `makeitem_lv_8` VALUES ('708');
INSERT INTO `makeitem_lv_8` VALUES ('716');
INSERT INTO `makeitem_lv_8` VALUES ('724');
INSERT INTO `makeitem_lv_8` VALUES ('732');
INSERT INTO `makeitem_lv_8` VALUES ('740');
INSERT INTO `makeitem_lv_8` VALUES ('748');
INSERT INTO `makeitem_lv_8` VALUES ('757');
INSERT INTO `makeitem_lv_8` VALUES ('908');
INSERT INTO `makeitem_lv_8` VALUES ('916');
INSERT INTO `makeitem_lv_8` VALUES ('924');
INSERT INTO `makeitem_lv_8` VALUES ('934');
INSERT INTO `makeitem_lv_8` VALUES ('942');
INSERT INTO `makeitem_lv_8` VALUES ('950');
INSERT INTO `makeitem_lv_8` VALUES ('958');
INSERT INTO `makeitem_lv_8` VALUES ('966');
INSERT INTO `makeitem_lv_8` VALUES ('991');
INSERT INTO `makeitem_lv_8` VALUES ('999');
INSERT INTO `makeitem_lv_8` VALUES ('1007');
INSERT INTO `makeitem_lv_8` VALUES ('1015');
INSERT INTO `makeitem_lv_8` VALUES ('1023');
INSERT INTO `makeitem_lv_8` VALUES ('1031');
INSERT INTO `makeitem_lv_8` VALUES ('1039');
INSERT INTO `makeitem_lv_8` VALUES ('1047');
INSERT INTO `makeitem_lv_8` VALUES ('1055');
INSERT INTO `makeitem_lv_8` VALUES ('1063');
INSERT INTO `makeitem_lv_8` VALUES ('7003');
INSERT INTO `makeitem_lv_8` VALUES ('7011');
INSERT INTO `makeitem_lv_8` VALUES ('7019');
INSERT INTO `makeitem_lv_8` VALUES ('7027');
INSERT INTO `makeitem_lv_8` VALUES ('7035');
INSERT INTO `makeitem_lv_8` VALUES ('7043');
INSERT INTO `makeitem_lv_8` VALUES ('7067');
INSERT INTO `makeitem_lv_8` VALUES ('7099');
INSERT INTO `makeitem_lv_8` VALUES ('7107');
INSERT INTO `makeitem_lv_8` VALUES ('7115');
INSERT INTO `makeitem_lv_8` VALUES ('7123');
INSERT INTO `makeitem_lv_8` VALUES ('7139');
INSERT INTO `makeitem_lv_8` VALUES ('7155');
INSERT INTO `makeitem_lv_8` VALUES ('7163');
INSERT INTO `makeitem_lv_8` VALUES ('7171');
INSERT INTO `makeitem_lv_8` VALUES ('7187');
INSERT INTO `makeitem_lv_8` VALUES ('7195');
INSERT INTO `makeitem_lv_8` VALUES ('7203');
INSERT INTO `makeitem_lv_8` VALUES ('7219');
INSERT INTO `makeitem_lv_8` VALUES ('707');
INSERT INTO `makeitem_lv_8` VALUES ('715');
INSERT INTO `makeitem_lv_8` VALUES ('723');
INSERT INTO `makeitem_lv_8` VALUES ('731');
INSERT INTO `makeitem_lv_8` VALUES ('739');
INSERT INTO `makeitem_lv_8` VALUES ('747');
INSERT INTO `makeitem_lv_8` VALUES ('756');
INSERT INTO `makeitem_lv_8` VALUES ('907');
INSERT INTO `makeitem_lv_8` VALUES ('915');
INSERT INTO `makeitem_lv_8` VALUES ('923');
INSERT INTO `makeitem_lv_8` VALUES ('932');
INSERT INTO `makeitem_lv_8` VALUES ('941');
INSERT INTO `makeitem_lv_8` VALUES ('949');
INSERT INTO `makeitem_lv_8` VALUES ('957');
INSERT INTO `makeitem_lv_8` VALUES ('965');
INSERT INTO `makeitem_lv_8` VALUES ('990');
INSERT INTO `makeitem_lv_8` VALUES ('998');
INSERT INTO `makeitem_lv_8` VALUES ('1006');
INSERT INTO `makeitem_lv_8` VALUES ('1022');
INSERT INTO `makeitem_lv_8` VALUES ('1038');
INSERT INTO `makeitem_lv_8` VALUES ('1046');
INSERT INTO `makeitem_lv_8` VALUES ('1054');
INSERT INTO `makeitem_lv_8` VALUES ('1062');
INSERT INTO `makeitem_lv_8` VALUES ('7002');
INSERT INTO `makeitem_lv_8` VALUES ('7010');
INSERT INTO `makeitem_lv_8` VALUES ('7026');
INSERT INTO `makeitem_lv_8` VALUES ('7034');
INSERT INTO `makeitem_lv_8` VALUES ('7066');
INSERT INTO `makeitem_lv_8` VALUES ('7098');
INSERT INTO `makeitem_lv_8` VALUES ('7106');
INSERT INTO `makeitem_lv_8` VALUES ('7122');
INSERT INTO `makeitem_lv_8` VALUES ('7138');
INSERT INTO `makeitem_lv_8` VALUES ('7154');
INSERT INTO `makeitem_lv_8` VALUES ('7162');
INSERT INTO `makeitem_lv_8` VALUES ('7186');
INSERT INTO `makeitem_lv_8` VALUES ('7194');
INSERT INTO `makeitem_lv_8` VALUES ('7202');
INSERT INTO `makeitem_lv_8` VALUES ('7210');
INSERT INTO `makeitem_lv_8` VALUES ('7218');
INSERT INTO `makeitem_lv_8` VALUES ('7226');
INSERT INTO `makeitem_lv_8` VALUES ('706');
INSERT INTO `makeitem_lv_8` VALUES ('714');
INSERT INTO `makeitem_lv_8` VALUES ('722');
INSERT INTO `makeitem_lv_8` VALUES ('730');
INSERT INTO `makeitem_lv_8` VALUES ('738');
INSERT INTO `makeitem_lv_8` VALUES ('746');
INSERT INTO `makeitem_lv_8` VALUES ('754');
INSERT INTO `makeitem_lv_8` VALUES ('906');
INSERT INTO `makeitem_lv_8` VALUES ('914');
INSERT INTO `makeitem_lv_8` VALUES ('922');
INSERT INTO `makeitem_lv_8` VALUES ('931');
INSERT INTO `makeitem_lv_8` VALUES ('940');
INSERT INTO `makeitem_lv_8` VALUES ('948');
INSERT INTO `makeitem_lv_8` VALUES ('956');
INSERT INTO `makeitem_lv_8` VALUES ('964');
INSERT INTO `makeitem_lv_8` VALUES ('972');
INSERT INTO `makeitem_lv_8` VALUES ('989');
INSERT INTO `makeitem_lv_8` VALUES ('997');
INSERT INTO `makeitem_lv_8` VALUES ('1005');
INSERT INTO `makeitem_lv_8` VALUES ('1013');
INSERT INTO `makeitem_lv_8` VALUES ('1021');
INSERT INTO `makeitem_lv_8` VALUES ('1037');
INSERT INTO `makeitem_lv_8` VALUES ('1045');
INSERT INTO `makeitem_lv_8` VALUES ('1053');
INSERT INTO `makeitem_lv_8` VALUES ('1061');
INSERT INTO `makeitem_lv_8` VALUES ('7001');
INSERT INTO `makeitem_lv_8` VALUES ('7009');
INSERT INTO `makeitem_lv_8` VALUES ('7017');
INSERT INTO `makeitem_lv_8` VALUES ('7033');
INSERT INTO `makeitem_lv_8` VALUES ('7041');
INSERT INTO `makeitem_lv_8` VALUES ('7049');
INSERT INTO `makeitem_lv_8` VALUES ('7065');
INSERT INTO `makeitem_lv_8` VALUES ('7097');
INSERT INTO `makeitem_lv_8` VALUES ('7105');
INSERT INTO `makeitem_lv_8` VALUES ('7121');
INSERT INTO `makeitem_lv_8` VALUES ('7137');
INSERT INTO `makeitem_lv_8` VALUES ('7153');
INSERT INTO `makeitem_lv_8` VALUES ('7161');
INSERT INTO `makeitem_lv_8` VALUES ('7193');
INSERT INTO `makeitem_lv_8` VALUES ('7201');
INSERT INTO `makeitem_lv_8` VALUES ('7209');
INSERT INTO `makeitem_lv_8` VALUES ('7217');
INSERT INTO `makeitem_lv_8` VALUES ('7225');
INSERT INTO `makeitem_lv_8` VALUES ('701');
INSERT INTO `makeitem_lv_8` VALUES ('713');
INSERT INTO `makeitem_lv_8` VALUES ('721');
INSERT INTO `makeitem_lv_8` VALUES ('729');
INSERT INTO `makeitem_lv_8` VALUES ('737');
INSERT INTO `makeitem_lv_8` VALUES ('745');
INSERT INTO `makeitem_lv_8` VALUES ('753');
INSERT INTO `makeitem_lv_8` VALUES ('905');
INSERT INTO `makeitem_lv_8` VALUES ('913');
INSERT INTO `makeitem_lv_8` VALUES ('921');
INSERT INTO `makeitem_lv_8` VALUES ('930');
INSERT INTO `makeitem_lv_8` VALUES ('939');
INSERT INTO `makeitem_lv_8` VALUES ('947');
INSERT INTO `makeitem_lv_8` VALUES ('955');
INSERT INTO `makeitem_lv_8` VALUES ('963');
INSERT INTO `makeitem_lv_8` VALUES ('971');
INSERT INTO `makeitem_lv_8` VALUES ('988');
INSERT INTO `makeitem_lv_8` VALUES ('996');
INSERT INTO `makeitem_lv_8` VALUES ('1004');
INSERT INTO `makeitem_lv_8` VALUES ('1012');
INSERT INTO `makeitem_lv_8` VALUES ('1020');
INSERT INTO `makeitem_lv_8` VALUES ('1028');
INSERT INTO `makeitem_lv_8` VALUES ('1036');
INSERT INTO `makeitem_lv_8` VALUES ('1044');
INSERT INTO `makeitem_lv_8` VALUES ('1052');
INSERT INTO `makeitem_lv_8` VALUES ('1060');
INSERT INTO `makeitem_lv_8` VALUES ('6023');
INSERT INTO `makeitem_lv_8` VALUES ('7016');
INSERT INTO `makeitem_lv_8` VALUES ('7032');
INSERT INTO `makeitem_lv_8` VALUES ('7048');
INSERT INTO `makeitem_lv_8` VALUES ('7064');
INSERT INTO `makeitem_lv_8` VALUES ('7072');
INSERT INTO `makeitem_lv_8` VALUES ('7096');
INSERT INTO `makeitem_lv_8` VALUES ('7104');
INSERT INTO `makeitem_lv_8` VALUES ('7112');
INSERT INTO `makeitem_lv_8` VALUES ('7120');
INSERT INTO `makeitem_lv_8` VALUES ('7136');
INSERT INTO `makeitem_lv_8` VALUES ('7152');
INSERT INTO `makeitem_lv_8` VALUES ('7168');
INSERT INTO `makeitem_lv_8` VALUES ('7192');
INSERT INTO `makeitem_lv_8` VALUES ('7200');
INSERT INTO `makeitem_lv_8` VALUES ('7208');
INSERT INTO `makeitem_lv_8` VALUES ('7216');
INSERT INTO `makeitem_lv_8` VALUES ('704');
INSERT INTO `makeitem_lv_8` VALUES ('712');
INSERT INTO `makeitem_lv_8` VALUES ('720');
INSERT INTO `makeitem_lv_8` VALUES ('728');
INSERT INTO `makeitem_lv_8` VALUES ('736');
INSERT INTO `makeitem_lv_8` VALUES ('744');
INSERT INTO `makeitem_lv_8` VALUES ('752');
INSERT INTO `makeitem_lv_8` VALUES ('904');
INSERT INTO `makeitem_lv_8` VALUES ('912');
INSERT INTO `makeitem_lv_8` VALUES ('920');
INSERT INTO `makeitem_lv_8` VALUES ('929');
INSERT INTO `makeitem_lv_8` VALUES ('938');
INSERT INTO `makeitem_lv_8` VALUES ('946');
INSERT INTO `makeitem_lv_8` VALUES ('954');
INSERT INTO `makeitem_lv_8` VALUES ('962');
INSERT INTO `makeitem_lv_8` VALUES ('970');
INSERT INTO `makeitem_lv_8` VALUES ('987');
INSERT INTO `makeitem_lv_8` VALUES ('995');
INSERT INTO `makeitem_lv_8` VALUES ('1003');
INSERT INTO `makeitem_lv_8` VALUES ('1011');
INSERT INTO `makeitem_lv_8` VALUES ('1019');
INSERT INTO `makeitem_lv_8` VALUES ('1027');
INSERT INTO `makeitem_lv_8` VALUES ('1035');
INSERT INTO `makeitem_lv_8` VALUES ('1043');
INSERT INTO `makeitem_lv_8` VALUES ('1051');
INSERT INTO `makeitem_lv_8` VALUES ('1059');
INSERT INTO `makeitem_lv_8` VALUES ('7007');
INSERT INTO `makeitem_lv_8` VALUES ('7015');
INSERT INTO `makeitem_lv_8` VALUES ('7023');
INSERT INTO `makeitem_lv_8` VALUES ('7031');
INSERT INTO `makeitem_lv_8` VALUES ('7047');
INSERT INTO `makeitem_lv_8` VALUES ('7063');
INSERT INTO `makeitem_lv_8` VALUES ('7071');
INSERT INTO `makeitem_lv_8` VALUES ('7095');
INSERT INTO `makeitem_lv_8` VALUES ('7111');
INSERT INTO `makeitem_lv_8` VALUES ('7119');
INSERT INTO `makeitem_lv_8` VALUES ('7135');
INSERT INTO `makeitem_lv_8` VALUES ('7151');
INSERT INTO `makeitem_lv_8` VALUES ('7159');
INSERT INTO `makeitem_lv_8` VALUES ('7167');
INSERT INTO `makeitem_lv_8` VALUES ('7207');
INSERT INTO `makeitem_lv_8` VALUES ('7215');
INSERT INTO `makeitem_lv_8` VALUES ('7223');
INSERT INTO `makeitem_lv_8` VALUES ('703');
INSERT INTO `makeitem_lv_8` VALUES ('711');
INSERT INTO `makeitem_lv_8` VALUES ('719');
INSERT INTO `makeitem_lv_8` VALUES ('727');
INSERT INTO `makeitem_lv_8` VALUES ('735');
INSERT INTO `makeitem_lv_8` VALUES ('743');
INSERT INTO `makeitem_lv_8` VALUES ('903');
INSERT INTO `makeitem_lv_8` VALUES ('911');
INSERT INTO `makeitem_lv_8` VALUES ('919');
INSERT INTO `makeitem_lv_8` VALUES ('928');
INSERT INTO `makeitem_lv_8` VALUES ('937');
INSERT INTO `makeitem_lv_8` VALUES ('945');
INSERT INTO `makeitem_lv_8` VALUES ('953');
INSERT INTO `makeitem_lv_8` VALUES ('961');
INSERT INTO `makeitem_lv_8` VALUES ('986');
INSERT INTO `makeitem_lv_8` VALUES ('994');
INSERT INTO `makeitem_lv_8` VALUES ('1002');
INSERT INTO `makeitem_lv_8` VALUES ('1010');
INSERT INTO `makeitem_lv_8` VALUES ('1018');
INSERT INTO `makeitem_lv_8` VALUES ('1026');
INSERT INTO `makeitem_lv_8` VALUES ('1034');
INSERT INTO `makeitem_lv_8` VALUES ('1042');
INSERT INTO `makeitem_lv_8` VALUES ('1050');
INSERT INTO `makeitem_lv_8` VALUES ('1058');
INSERT INTO `makeitem_lv_8` VALUES ('6021');
INSERT INTO `makeitem_lv_8` VALUES ('7006');
INSERT INTO `makeitem_lv_8` VALUES ('7014');
INSERT INTO `makeitem_lv_8` VALUES ('7030');
INSERT INTO `makeitem_lv_8` VALUES ('7038');
INSERT INTO `makeitem_lv_8` VALUES ('7054');
INSERT INTO `makeitem_lv_8` VALUES ('7070');
INSERT INTO `makeitem_lv_8` VALUES ('7110');
INSERT INTO `makeitem_lv_8` VALUES ('7118');
INSERT INTO `makeitem_lv_8` VALUES ('7126');
INSERT INTO `makeitem_lv_8` VALUES ('7150');
INSERT INTO `makeitem_lv_8` VALUES ('7158');
INSERT INTO `makeitem_lv_8` VALUES ('7166');
INSERT INTO `makeitem_lv_8` VALUES ('7182');
INSERT INTO `makeitem_lv_8` VALUES ('7190');
INSERT INTO `makeitem_lv_8` VALUES ('7198');
INSERT INTO `makeitem_lv_8` VALUES ('7206');
INSERT INTO `makeitem_lv_8` VALUES ('7214');
INSERT INTO `makeitem_lv_8` VALUES ('7222');
INSERT INTO `makeitem_lv_8` VALUES ('702');
INSERT INTO `makeitem_lv_8` VALUES ('710');
INSERT INTO `makeitem_lv_8` VALUES ('718');
INSERT INTO `makeitem_lv_8` VALUES ('726');
INSERT INTO `makeitem_lv_8` VALUES ('734');
INSERT INTO `makeitem_lv_8` VALUES ('742');
INSERT INTO `makeitem_lv_8` VALUES ('902');
INSERT INTO `makeitem_lv_8` VALUES ('910');
INSERT INTO `makeitem_lv_8` VALUES ('918');
INSERT INTO `makeitem_lv_8` VALUES ('926');
INSERT INTO `makeitem_lv_8` VALUES ('936');
INSERT INTO `makeitem_lv_8` VALUES ('944');
INSERT INTO `makeitem_lv_8` VALUES ('952');
INSERT INTO `makeitem_lv_8` VALUES ('960');
INSERT INTO `makeitem_lv_8` VALUES ('985');
INSERT INTO `makeitem_lv_8` VALUES ('993');
INSERT INTO `makeitem_lv_8` VALUES ('1001');
INSERT INTO `makeitem_lv_8` VALUES ('1009');
INSERT INTO `makeitem_lv_8` VALUES ('1017');
INSERT INTO `makeitem_lv_8` VALUES ('1025');
INSERT INTO `makeitem_lv_8` VALUES ('1033');
INSERT INTO `makeitem_lv_8` VALUES ('1041');
INSERT INTO `makeitem_lv_8` VALUES ('1049');
INSERT INTO `makeitem_lv_8` VALUES ('1057');
INSERT INTO `makeitem_lv_8` VALUES ('6020');
INSERT INTO `makeitem_lv_8` VALUES ('7005');
INSERT INTO `makeitem_lv_8` VALUES ('7013');
INSERT INTO `makeitem_lv_8` VALUES ('7021');
INSERT INTO `makeitem_lv_8` VALUES ('7053');
INSERT INTO `makeitem_lv_8` VALUES ('7069');
INSERT INTO `makeitem_lv_8` VALUES ('7101');
INSERT INTO `makeitem_lv_8` VALUES ('7117');
INSERT INTO `makeitem_lv_8` VALUES ('7125');
INSERT INTO `makeitem_lv_8` VALUES ('7141');
INSERT INTO `makeitem_lv_8` VALUES ('7149');
INSERT INTO `makeitem_lv_8` VALUES ('7157');
INSERT INTO `makeitem_lv_8` VALUES ('7165');
INSERT INTO `makeitem_lv_8` VALUES ('7189');
INSERT INTO `makeitem_lv_8` VALUES ('7197');
INSERT INTO `makeitem_lv_8` VALUES ('7205');
INSERT INTO `makeitem_lv_8` VALUES ('7213');
INSERT INTO `makeitem_lv_8` VALUES ('7221');
INSERT INTO `makeitem_lv_8` VALUES ('705');
INSERT INTO `makeitem_lv_8` VALUES ('709');
INSERT INTO `makeitem_lv_8` VALUES ('717');
INSERT INTO `makeitem_lv_8` VALUES ('725');
INSERT INTO `makeitem_lv_8` VALUES ('733');
INSERT INTO `makeitem_lv_8` VALUES ('741');
INSERT INTO `makeitem_lv_8` VALUES ('749');
INSERT INTO `makeitem_lv_8` VALUES ('901');
INSERT INTO `makeitem_lv_8` VALUES ('909');
INSERT INTO `makeitem_lv_8` VALUES ('917');
INSERT INTO `makeitem_lv_8` VALUES ('925');
INSERT INTO `makeitem_lv_8` VALUES ('935');
INSERT INTO `makeitem_lv_8` VALUES ('943');
INSERT INTO `makeitem_lv_8` VALUES ('951');
INSERT INTO `makeitem_lv_8` VALUES ('959');
INSERT INTO `makeitem_lv_8` VALUES ('967');
INSERT INTO `makeitem_lv_8` VALUES ('984');
INSERT INTO `makeitem_lv_8` VALUES ('992');
INSERT INTO `makeitem_lv_8` VALUES ('1000');
INSERT INTO `makeitem_lv_8` VALUES ('1008');
INSERT INTO `makeitem_lv_8` VALUES ('1016');
INSERT INTO `makeitem_lv_8` VALUES ('1024');
INSERT INTO `makeitem_lv_8` VALUES ('1032');
INSERT INTO `makeitem_lv_8` VALUES ('1040');
INSERT INTO `makeitem_lv_8` VALUES ('1048');
INSERT INTO `makeitem_lv_8` VALUES ('1056');
INSERT INTO `makeitem_lv_8` VALUES ('6010');
INSERT INTO `makeitem_lv_8` VALUES ('7004');
INSERT INTO `makeitem_lv_8` VALUES ('7012');
INSERT INTO `makeitem_lv_8` VALUES ('7068');
INSERT INTO `makeitem_lv_8` VALUES ('7100');
INSERT INTO `makeitem_lv_8` VALUES ('7116');
INSERT INTO `makeitem_lv_8` VALUES ('7124');
INSERT INTO `makeitem_lv_8` VALUES ('7140');
INSERT INTO `makeitem_lv_8` VALUES ('7156');
INSERT INTO `makeitem_lv_8` VALUES ('7164');
INSERT INTO `makeitem_lv_8` VALUES ('7172');
INSERT INTO `makeitem_lv_8` VALUES ('7188');
INSERT INTO `makeitem_lv_8` VALUES ('7196');
INSERT INTO `makeitem_lv_8` VALUES ('7212');
INSERT INTO `makeitem_lv_8` VALUES ('7220');

-- ----------------------------
-- Table structure for `makeitem_lv_boss`
-- ----------------------------
DROP TABLE IF EXISTS `makeitem_lv_boss`;
CREATE TABLE `makeitem_lv_boss` (
  `item_id` int(10) NOT NULL default '0'
) ENGINE=MyISAM;

-- ----------------------------
-- Records of makeitem_lv_boss
-- ----------------------------
INSERT INTO `makeitem_lv_BOSS` VALUES ('7211');
INSERT INTO `makeitem_lv_BOSS` VALUES ('1030');
INSERT INTO `makeitem_lv_BOSS` VALUES ('7018');
INSERT INTO `makeitem_lv_BOSS` VALUES ('7114');
INSERT INTO `makeitem_lv_BOSS` VALUES ('1029');
INSERT INTO `makeitem_lv_BOSS` VALUES ('7113');
INSERT INTO `makeitem_lv_BOSS` VALUES ('7169');
INSERT INTO `makeitem_lv_BOSS` VALUES ('7020');
INSERT INTO `makeitem_lv_BOSS` VALUES ('7036');
INSERT INTO `makeitem_lv_BOSS` VALUES ('7108');
INSERT INTO `makeitem_lv_BOSS` VALUES ('750');
INSERT INTO `makeitem_lv_BOSS` VALUES ('968');
INSERT INTO `makeitem_lv_BOSS` VALUES ('7109');
INSERT INTO `makeitem_lv_BOSS` VALUES ('751');
INSERT INTO `makeitem_lv_BOSS` VALUES ('969');
INSERT INTO `makeitem_lv_BOSS` VALUES ('7022');
INSERT INTO `makeitem_lv_BOSS` VALUES ('6022');
INSERT INTO `makeitem_lv_BOSS` VALUES ('7008');
INSERT INTO `makeitem_lv_BOSS` VALUES ('7024');
INSERT INTO `makeitem_lv_BOSS` VALUES ('1014');

-- ----------------------------
-- Table structure for `shoplog`
-- ----------------------------
DROP TABLE IF EXISTS `shoplog`;
CREATE TABLE `shoplog` (
  `charid` int(10) NOT NULL default '0',
  `makeitem` int(10) NOT NULL default '0',
  `zeny` int(15) NOT NULL default '0',
  `item1` int(10) NOT NULL default '0',
  `item2` int(10) NOT NULL,
  `item3` int(10) NOT NULL,
  `item4` int(10) NOT NULL,
  `item5` int(10) NOT NULL,
  `item6` int(10) NOT NULL,
  `item7` int(10) NOT NULL,
  `item8` int(10) NOT NULL,
  `itemnum1` int(10) NOT NULL,
  `itemnum2` int(10) NOT NULL,
  `itemnum3` int(10) NOT NULL,
  `itemnum4` int(10) NOT NULL default '0',
  `itemnum5` int(10) NOT NULL,
  `itemnum6` int(10) NOT NULL,
  `itemnum7` int(10) NOT NULL,
  `itemnum8` int(10) NOT NULL,
  PRIMARY KEY  (`charid`)
) ENGINE=MyISAM;

-- ----------------------------
-- Records of shoplog
-- ----------------------------
INSERT INTO `shoplog` VALUES ('150000', '5666', '7404864', '7108', '1029', '7020', '1050', '995', '0', '0', '0', '192', '224', '128', '144', '184', '0', '0', '0');

-- ----------------------------
-- Table structure for `shop_lv_1`
-- ----------------------------
DROP TABLE IF EXISTS `shop_lv_1`;
CREATE TABLE `shop_lv_1` (
  `makeitem` int(10) NOT NULL default '0',
  `zeny` int(10) NOT NULL default '0',
  `itemid1` int(10) NOT NULL default '0',
  `itemnum1` int(10) NOT NULL default '0',
  `itemid2` int(10) NOT NULL default '0',
  `itemnum2` int(10) NOT NULL default '0',
  `itemid3` int(10) NOT NULL default '0',
  `itemnum3` int(10) NOT NULL default '0',
  `itemid4` int(10) NOT NULL default '0',
  `itemnum4` int(10) NOT NULL default '0',
  `itemid5` int(10) NOT NULL default '0',
  `itemnum5` int(10) NOT NULL default '0',
  `itemid6` int(10) NOT NULL default '0',
  `itemnum6` int(10) NOT NULL default '0',
  `itemid7` int(10) NOT NULL default '0',
  `itemnum7` int(10) NOT NULL default '0',
  `itemid8` int(10) NOT NULL default '0',
  `itemnum8` int(10) NOT NULL default '0'
) ENGINE=MyISAM;

-- ----------------------------
-- Records of shop_lv_1
-- ----------------------------
INSERT INTO `shop_lv_1` VALUES ('2279', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0');
INSERT INTO `shop_lv_1` VALUES ('2239', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0');
INSERT INTO `shop_lv_1` VALUES ('5033', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0');
INSERT INTO `shop_lv_1` VALUES ('5089', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0');
INSERT INTO `shop_lv_1` VALUES ('2271', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0');
INSERT INTO `shop_lv_1` VALUES ('5014', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0');
INSERT INTO `shop_lv_1` VALUES ('5050', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0');
INSERT INTO `shop_lv_1` VALUES ('5005', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0');
INSERT INTO `shop_lv_1` VALUES ('5154', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0');
INSERT INTO `shop_lv_1` VALUES ('5053', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0');

-- ----------------------------
-- Table structure for `shop_lv_2`
-- ----------------------------
DROP TABLE IF EXISTS `shop_lv_2`;
CREATE TABLE `shop_lv_2` (
  `makeitem` int(10) NOT NULL default '0',
  `zeny` int(10) NOT NULL default '0',
  `itemid1` int(10) NOT NULL default '0',
  `itemnum1` int(10) NOT NULL default '0',
  `itemid2` int(10) NOT NULL default '0',
  `itemnum2` int(10) NOT NULL default '0',
  `itemid3` int(10) NOT NULL default '0',
  `itemnum3` int(10) NOT NULL default '0',
  `itemid4` int(10) NOT NULL default '0',
  `itemnum4` int(10) NOT NULL default '0',
  `itemid5` int(10) NOT NULL default '0',
  `itemnum5` int(10) NOT NULL default '0',
  `itemid6` int(10) NOT NULL default '0',
  `itemnum6` int(10) NOT NULL default '0',
  `itemid7` int(10) NOT NULL default '0',
  `itemnum7` int(10) NOT NULL default '0',
  `itemid8` int(10) NOT NULL default '0',
  `itemnum8` int(10) NOT NULL default '0'
) ENGINE=MyISAM;

-- ----------------------------
-- Records of shop_lv_2
-- ----------------------------
INSERT INTO `shop_lv_2` VALUES ('5392', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0');
INSERT INTO `shop_lv_2` VALUES ('2283', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0');
INSERT INTO `shop_lv_2` VALUES ('5811', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0');
INSERT INTO `shop_lv_2` VALUES ('5351', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0');
INSERT INTO `shop_lv_2` VALUES ('5399', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0');
INSERT INTO `shop_lv_2` VALUES ('2255', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0');
INSERT INTO `shop_lv_2` VALUES ('5056', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0');
INSERT INTO `shop_lv_2` VALUES ('5101', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0');
INSERT INTO `shop_lv_2` VALUES ('5813', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0');
INSERT INTO `shop_lv_2` VALUES ('5084', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0');

-- ----------------------------
-- Table structure for `shop_lv_3`
-- ----------------------------
DROP TABLE IF EXISTS `shop_lv_3`;
CREATE TABLE `shop_lv_3` (
  `makeitem` int(10) NOT NULL default '0',
  `zeny` int(10) NOT NULL default '0',
  `itemid1` int(10) NOT NULL default '0',
  `itemnum1` int(10) NOT NULL default '0',
  `itemid2` int(10) NOT NULL default '0',
  `itemnum2` int(10) NOT NULL default '0',
  `itemid3` int(10) NOT NULL default '0',
  `itemnum3` int(10) NOT NULL default '0',
  `itemid4` int(10) NOT NULL default '0',
  `itemnum4` int(10) NOT NULL default '0',
  `itemid5` int(10) NOT NULL default '0',
  `itemnum5` int(10) NOT NULL default '0',
  `itemid6` int(10) NOT NULL default '0',
  `itemnum6` int(10) NOT NULL default '0',
  `itemid7` int(10) NOT NULL default '0',
  `itemnum7` int(10) NOT NULL default '0',
  `itemid8` int(10) NOT NULL default '0',
  `itemnum8` int(10) NOT NULL default '0'
) ENGINE=MyISAM;

-- ----------------------------
-- Records of shop_lv_3
-- ----------------------------
INSERT INTO `shop_lv_3` VALUES ('5151', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0');
INSERT INTO `shop_lv_3` VALUES ('5402', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0');
INSERT INTO `shop_lv_3` VALUES ('5579', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0');
INSERT INTO `shop_lv_3` VALUES ('5095', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0');
INSERT INTO `shop_lv_3` VALUES ('2202', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0');
INSERT INTO `shop_lv_3` VALUES ('5147', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0');
INSERT INTO `shop_lv_3` VALUES ('5115', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0');
INSERT INTO `shop_lv_3` VALUES ('5268', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0');

-- ----------------------------
-- Table structure for `shop_lv_4`
-- ----------------------------
DROP TABLE IF EXISTS `shop_lv_4`;
CREATE TABLE `shop_lv_4` (
  `makeitem` int(10) NOT NULL default '0',
  `zeny` int(10) NOT NULL default '0',
  `itemid1` int(10) NOT NULL default '0',
  `itemnum1` int(10) NOT NULL default '0',
  `itemid2` int(10) NOT NULL default '0',
  `itemnum2` int(10) NOT NULL default '0',
  `itemid3` int(10) NOT NULL default '0',
  `itemnum3` int(10) NOT NULL default '0',
  `itemid4` int(10) NOT NULL default '0',
  `itemnum4` int(10) NOT NULL default '0',
  `itemid5` int(10) NOT NULL default '0',
  `itemnum5` int(10) NOT NULL default '0',
  `itemid6` int(10) NOT NULL default '0',
  `itemnum6` int(10) NOT NULL default '0',
  `itemid7` int(10) NOT NULL default '0',
  `itemnum7` int(10) NOT NULL default '0',
  `itemid8` int(10) NOT NULL default '0',
  `itemnum8` int(10) NOT NULL default '0'
) ENGINE=MyISAM;

-- ----------------------------
-- Records of shop_lv_4
-- ----------------------------
INSERT INTO `shop_lv_4` VALUES ('5086', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0');
INSERT INTO `shop_lv_4` VALUES ('5020', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0');
INSERT INTO `shop_lv_4` VALUES ('5411', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0');
INSERT INTO `shop_lv_4` VALUES ('5548', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0');
INSERT INTO `shop_lv_4` VALUES ('5737', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0');
INSERT INTO `shop_lv_4` VALUES ('5234', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0');
INSERT INTO `shop_lv_4` VALUES ('5205', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0');
INSERT INTO `shop_lv_4` VALUES ('5436', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0');
INSERT INTO `shop_lv_4` VALUES ('5307', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0');
INSERT INTO `shop_lv_4` VALUES ('5128', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0');

-- ----------------------------
-- Table structure for `shop_lv_5`
-- ----------------------------
DROP TABLE IF EXISTS `shop_lv_5`;
CREATE TABLE `shop_lv_5` (
  `makeitem` int(10) NOT NULL default '0',
  `zeny` int(10) NOT NULL default '0',
  `itemid1` int(10) NOT NULL default '0',
  `itemnum1` int(10) NOT NULL default '0',
  `itemid2` int(10) NOT NULL default '0',
  `itemnum2` int(10) NOT NULL default '0',
  `itemid3` int(10) NOT NULL default '0',
  `itemnum3` int(10) NOT NULL default '0',
  `itemid4` int(10) NOT NULL default '0',
  `itemnum4` int(10) NOT NULL default '0',
  `itemid5` int(10) NOT NULL default '0',
  `itemnum5` int(10) NOT NULL default '0',
  `itemid6` int(10) NOT NULL default '0',
  `itemnum6` int(10) NOT NULL default '0',
  `itemid7` int(10) NOT NULL default '0',
  `itemnum7` int(10) NOT NULL default '0',
  `itemid8` int(10) NOT NULL default '0',
  `itemnum8` int(10) NOT NULL default '0'
) ENGINE=MyISAM;

-- ----------------------------
-- Records of shop_lv_5
-- ----------------------------
INSERT INTO `shop_lv_5` VALUES ('5790', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0');
INSERT INTO `shop_lv_5` VALUES ('5389', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0');
INSERT INTO `shop_lv_5` VALUES ('5170', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0');
INSERT INTO `shop_lv_5` VALUES ('5584', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0');
INSERT INTO `shop_lv_5` VALUES ('5858', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0');
INSERT INTO `shop_lv_5` VALUES ('5198', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0');
INSERT INTO `shop_lv_5` VALUES ('5824', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0');
INSERT INTO `shop_lv_5` VALUES ('5859', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0');
INSERT INTO `shop_lv_5` VALUES ('5212', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0');
INSERT INTO `shop_lv_5` VALUES ('5199', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0');

-- ----------------------------
-- Table structure for `shop_lv_6`
-- ----------------------------
DROP TABLE IF EXISTS `shop_lv_6`;
CREATE TABLE `shop_lv_6` (
  `makeitem` int(10) NOT NULL default '0',
  `zeny` int(10) NOT NULL default '0',
  `itemid1` int(10) NOT NULL default '0',
  `itemnum1` int(10) NOT NULL default '0',
  `itemid2` int(10) NOT NULL default '0',
  `itemnum2` int(10) NOT NULL default '0',
  `itemid3` int(10) NOT NULL default '0',
  `itemnum3` int(10) NOT NULL default '0',
  `itemid4` int(10) NOT NULL default '0',
  `itemnum4` int(10) NOT NULL default '0',
  `itemid5` int(10) NOT NULL default '0',
  `itemnum5` int(10) NOT NULL default '0',
  `itemid6` int(10) NOT NULL default '0',
  `itemnum6` int(10) NOT NULL default '0',
  `itemid7` int(10) NOT NULL default '0',
  `itemnum7` int(10) NOT NULL default '0',
  `itemid8` int(10) NOT NULL default '0',
  `itemnum8` int(10) NOT NULL default '0'
) ENGINE=MyISAM;

-- ----------------------------
-- Records of shop_lv_6
-- ----------------------------
INSERT INTO `shop_lv_6` VALUES ('5841', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0');
INSERT INTO `shop_lv_6` VALUES ('5421', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0');
INSERT INTO `shop_lv_6` VALUES ('5503', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0');
INSERT INTO `shop_lv_6` VALUES ('5171', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0');
INSERT INTO `shop_lv_6` VALUES ('5460', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0');
INSERT INTO `shop_lv_6` VALUES ('5107', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0');
INSERT INTO `shop_lv_6` VALUES ('5497', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0');
INSERT INTO `shop_lv_6` VALUES ('5314', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0');
INSERT INTO `shop_lv_6` VALUES ('5766', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0');
INSERT INTO `shop_lv_6` VALUES ('5361', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0');

-- ----------------------------
-- Table structure for `shop_lv_7`
-- ----------------------------
DROP TABLE IF EXISTS `shop_lv_7`;
CREATE TABLE `shop_lv_7` (
  `makeitem` int(10) NOT NULL default '0',
  `zeny` int(10) NOT NULL default '0',
  `itemid1` int(10) NOT NULL default '0',
  `itemnum1` int(10) NOT NULL default '0',
  `itemid2` int(10) NOT NULL default '0',
  `itemnum2` int(10) NOT NULL default '0',
  `itemid3` int(10) NOT NULL default '0',
  `itemnum3` int(10) NOT NULL default '0',
  `itemid4` int(10) NOT NULL default '0',
  `itemnum4` int(10) NOT NULL default '0',
  `itemid5` int(10) NOT NULL default '0',
  `itemnum5` int(10) NOT NULL default '0',
  `itemid6` int(10) NOT NULL default '0',
  `itemnum6` int(10) NOT NULL default '0',
  `itemid7` int(10) NOT NULL default '0',
  `itemnum7` int(10) NOT NULL default '0',
  `itemid8` int(10) NOT NULL default '0',
  `itemnum8` int(10) NOT NULL default '0'
) ENGINE=MyISAM;

-- ----------------------------
-- Records of shop_lv_7
-- ----------------------------
INSERT INTO `shop_lv_7` VALUES ('5747', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0');
INSERT INTO `shop_lv_7` VALUES ('5753', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0');
INSERT INTO `shop_lv_7` VALUES ('5596', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0');
INSERT INTO `shop_lv_7` VALUES ('5549', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0');
INSERT INTO `shop_lv_7` VALUES ('5256', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0');
INSERT INTO `shop_lv_7` VALUES ('5568', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0');
INSERT INTO `shop_lv_7` VALUES ('5550', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0');
INSERT INTO `shop_lv_7` VALUES ('5757', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0');
INSERT INTO `shop_lv_7` VALUES ('5748', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0');
INSERT INTO `shop_lv_7` VALUES ('5353', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0');

-- ----------------------------
-- Table structure for `shop_lv_8`
-- ----------------------------
DROP TABLE IF EXISTS `shop_lv_8`;
CREATE TABLE `shop_lv_8` (
  `makeitem` int(10) NOT NULL default '0',
  `zeny` int(10) NOT NULL default '0',
  `itemid1` int(10) NOT NULL default '0',
  `itemnum1` int(10) NOT NULL default '0',
  `itemid2` int(10) NOT NULL default '0',
  `itemnum2` int(10) NOT NULL default '0',
  `itemid3` int(10) NOT NULL default '0',
  `itemnum3` int(10) NOT NULL default '0',
  `itemid4` int(10) NOT NULL default '0',
  `itemnum4` int(10) NOT NULL default '0',
  `itemid5` int(10) NOT NULL default '0',
  `itemnum5` int(10) NOT NULL default '0',
  `itemid6` int(10) NOT NULL default '0',
  `itemnum6` int(10) NOT NULL default '0',
  `itemid7` int(10) NOT NULL default '0',
  `itemnum7` int(10) NOT NULL default '0',
  `itemid8` int(10) NOT NULL default '0',
  `itemnum8` int(10) NOT NULL default '0'
) ENGINE=MyISAM;

-- ----------------------------
-- Records of shop_lv_8
-- ----------------------------
INSERT INTO `shop_lv_8` VALUES ('5453', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0');
INSERT INTO `shop_lv_8` VALUES ('5746', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0');
INSERT INTO `shop_lv_8` VALUES ('5605', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0');
INSERT INTO `shop_lv_8` VALUES ('5451', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0');
INSERT INTO `shop_lv_8` VALUES ('5582', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0');
INSERT INTO `shop_lv_8` VALUES ('5452', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0');
INSERT INTO `shop_lv_8` VALUES ('5022', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0');

-- ----------------------------
-- Table structure for `shop_tmp`
-- ----------------------------
DROP TABLE IF EXISTS `shop_tmp`;
CREATE TABLE `shop_tmp` (
  `itemid` int(10) NOT NULL default '0'
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
INSERT INTO `db_roulette`(`index`, `level`, `item_id`, `amount`, `flag` ) VALUES ( 5, 1, 12609, 1, 0 ); -- Old_Ore_Box
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
INSERT INTO `db_roulette`(`index`, `level`, `item_id`, `amount`, `flag` ) VALUES ( 33, 5, 12831, 1, 0 ); -- Potion_Box
INSERT INTO `db_roulette`(`index`, `level`, `item_id`, `amount`, `flag` ) VALUES ( 34, 5, 6235, 1, 0 ); -- Guarantee_Armor_6Up

INSERT INTO `db_roulette`(`index`, `level`, `item_id`, `amount`, `flag` ) VALUES ( 35, 6, 671, 1, 1 ); -- Gold_Coin
INSERT INTO `db_roulette`(`index`, `level`, `item_id`, `amount`, `flag` ) VALUES ( 36, 6, 12766, 1, 0 ); -- Reward_Job_BM25
INSERT INTO `db_roulette`(`index`, `level`, `item_id`, `amount`, `flag` ) VALUES ( 37, 6, 6234, 1, 0 ); -- Guarantee_Armor_7Up
INSERT INTO `db_roulette`(`index`, `level`, `item_id`, `amount`, `flag` ) VALUES ( 38, 6, 6233, 1, 0 ); -- Guarantee_Armor_8Up

INSERT INTO `db_roulette`(`index`, `level`, `item_id`, `amount`, `flag` ) VALUES ( 39, 7, 671, 1, 1 ); -- Gold_Coin
INSERT INTO `db_roulette`(`index`, `level`, `item_id`, `amount`, `flag` ) VALUES ( 40, 7, 6233, 1, 0 ); -- Guarantee_Armor_8Up
INSERT INTO `db_roulette`(`index`, `level`, `item_id`, `amount`, `flag` ) VALUES ( 41, 7, 6233, 1, 0 ); -- Guarantee_Armor_8Up	// KRO lists this twice

-- ----------------------------
-- upgrade_v8.13.1 for `vendings`
-- ----------------------------
ALTER TABLE `vendings` ADD COLUMN `extended_vending_item` smallint(5) unsigned NOT NULL DEFAULT '0' AFTER `sit`;

-- ----------------------------
-- upgrade_v8.14.0 for `bot_db`
-- ----------------------------
CREATE TABLE IF NOT EXISTS `bot_db` (
  `account_id` int(11) unsigned NOT NULL,
  `char_id` int(10) unsigned NOT NULL,
  `sex` enum('F','M') NOT NULL DEFAULT 'M',
  PRIMARY KEY (`account_id`)
) ENGINE=MyISAM;

-- ----------------------------
-- upgrade_v8.15.0 for `fame_rank`
-- ----------------------------
CREATE TABLE IF NOT EXISTS `fame_rank` (
 `char_id` int(10) unsigned NOT NULL default '1',
 `char_name` varchar(255) default 'Unknown',
 `class` int(10) unsigned default '0',
 `fame` tinyint(3) unsigned default '0',
 PRIMARY KEY (`char_id`)
 ) ENGINE=MyISAM;

-- ----------------------------
-- upgrade_v8.18.0 for `login`
-- ----------------------------
ALTER TABLE `login` ADD `server` int(10) NOT NULL default '0';
