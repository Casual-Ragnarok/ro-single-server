/*
Navicat MySQL Data Transfer

Source Server         : ro
Source Server Version : 50560
Source Host           : localhost:3306
Source Database       : ragnarok_log

Target Server Type    : MYSQL
Target Server Version : 50560
File Encoding         : 65001

Date: 2022-05-21 02:19:53
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for atcommandlog
-- ----------------------------
DROP TABLE IF EXISTS `atcommandlog`;
CREATE TABLE `atcommandlog` (
  `atcommand_id` mediumint(9) unsigned NOT NULL AUTO_INCREMENT,
  `atcommand_date` datetime NOT NULL,
  `account_id` int(11) unsigned NOT NULL DEFAULT '0',
  `char_id` int(11) unsigned NOT NULL DEFAULT '0',
  `char_name` varchar(25) NOT NULL DEFAULT '',
  `map` varchar(11) NOT NULL DEFAULT '',
  `command` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`atcommand_id`),
  KEY `account_id` (`account_id`),
  KEY `char_id` (`char_id`)
) ENGINE=MyISAM AUTO_INCREMENT=13 DEFAULT CHARSET=gbk;

-- ----------------------------
-- Records of atcommandlog
-- ----------------------------
INSERT INTO `atcommandlog` VALUES ('1', '2019-04-10 21:33:15', '2000000', '150000', '最后防线', 'new_1-1', '@kami 欢迎管理员 - [最后防线] 登录仙境传说!');
INSERT INTO `atcommandlog` VALUES ('2', '2021-05-15 06:29:12', '2000000', '150000', 'GM01', 'new_1-1', '@kami 欢迎管理员 - [GM01] 登录仙境传说!');
INSERT INTO `atcommandlog` VALUES ('3', '2021-05-15 06:29:25', '2000000', '150000', 'GM01', 'morocc', '@go 1');
INSERT INTO `atcommandlog` VALUES ('4', '2021-05-15 06:29:36', '2000000', '150000', 'GM01', 'prontera', '@go 0');
INSERT INTO `atcommandlog` VALUES ('5', '2021-05-15 06:29:40', '2000000', '150000', 'GM01', 'prontera', '@speed 1');
INSERT INTO `atcommandlog` VALUES ('6', '2021-05-15 14:31:59', '2000000', '150000', 'GM01', 'new_1-1', '@kami 欢迎管理员 - [GM01] 登录仙境传说!');
INSERT INTO `atcommandlog` VALUES ('7', '2021-05-15 14:32:06', '2000000', '150000', 'GM01', 'prontera', '@go 0');
INSERT INTO `atcommandlog` VALUES ('8', '2021-05-15 14:32:11', '2000000', '150000', 'GM01', 'prontera', '@speed 1');
INSERT INTO `atcommandlog` VALUES ('9', '2021-05-15 06:34:43', '2000000', '150000', 'GM01', 'prt_fild08', '@kami 欢迎管理员 - [GM01] 登录仙境传说!');
INSERT INTO `atcommandlog` VALUES ('10', '2021-05-20 15:44:09', '2000000', '150000', 'GM01', 'new_1-1', '@kami 欢迎管理员 - [GM01] 登录仙境传说!');
INSERT INTO `atcommandlog` VALUES ('11', '2021-05-23 04:16:44', '2000000', '150000', 'GM01', 'new_1-1', '@kami 欢迎管理员 - [GM01] 登录仙境传说!');
INSERT INTO `atcommandlog` VALUES ('12', '2022-05-20 18:06:10', '2000000', '150000', 'GM01', 'new_1-1', '@kami 欢迎管理员 - [GM01] 登录仙境传说!');

-- ----------------------------
-- Table structure for branchlog
-- ----------------------------
DROP TABLE IF EXISTS `branchlog`;
CREATE TABLE `branchlog` (
  `branch_id` mediumint(9) unsigned NOT NULL AUTO_INCREMENT,
  `branch_date` datetime NOT NULL,
  `account_id` int(11) NOT NULL DEFAULT '0',
  `char_id` int(11) NOT NULL DEFAULT '0',
  `char_name` varchar(25) NOT NULL DEFAULT '',
  `map` varchar(11) NOT NULL DEFAULT '',
  PRIMARY KEY (`branch_id`),
  KEY `account_id` (`account_id`),
  KEY `char_id` (`char_id`)
) ENGINE=MyISAM DEFAULT CHARSET=gbk;

-- ----------------------------
-- Records of branchlog
-- ----------------------------

-- ----------------------------
-- Table structure for cashlog
-- ----------------------------
DROP TABLE IF EXISTS `cashlog`;
CREATE TABLE `cashlog` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `time` datetime NOT NULL,
  `char_id` int(11) NOT NULL DEFAULT '0',
  `type` enum('T','V','P','M','S','N','D','C','A','E','I','B','$') NOT NULL DEFAULT 'S',
  `cash_type` enum('O','K','C') NOT NULL DEFAULT 'O',
  `amount` int(11) NOT NULL DEFAULT '0',
  `map` varchar(11) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `type` (`type`)
) ENGINE=MyISAM DEFAULT CHARSET=gbk;

-- ----------------------------
-- Records of cashlog
-- ----------------------------

-- ----------------------------
-- Table structure for chatlog
-- ----------------------------
DROP TABLE IF EXISTS `chatlog`;
CREATE TABLE `chatlog` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `time` datetime NOT NULL,
  `type` enum('O','W','P','G','M','C') NOT NULL DEFAULT 'O',
  `type_id` int(11) NOT NULL DEFAULT '0',
  `src_charid` int(11) NOT NULL DEFAULT '0',
  `src_accountid` int(11) NOT NULL DEFAULT '0',
  `src_map` varchar(11) NOT NULL DEFAULT '',
  `src_map_x` smallint(4) NOT NULL DEFAULT '0',
  `src_map_y` smallint(4) NOT NULL DEFAULT '0',
  `dst_charname` varchar(25) NOT NULL DEFAULT '',
  `message` varchar(150) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `src_accountid` (`src_accountid`),
  KEY `src_charid` (`src_charid`)
) ENGINE=MyISAM DEFAULT CHARSET=gbk;

-- ----------------------------
-- Records of chatlog
-- ----------------------------

-- ----------------------------
-- Table structure for feedinglog
-- ----------------------------
DROP TABLE IF EXISTS `feedinglog`;
CREATE TABLE `feedinglog` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `time` datetime NOT NULL,
  `char_id` int(11) NOT NULL,
  `target_id` int(11) NOT NULL,
  `target_class` smallint(11) NOT NULL,
  `type` enum('P','H','O') NOT NULL,
  `intimacy` int(11) unsigned NOT NULL,
  `item_id` smallint(5) unsigned NOT NULL,
  `map` varchar(11) NOT NULL,
  `x` smallint(5) unsigned NOT NULL,
  `y` smallint(5) unsigned NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=gbk;

-- ----------------------------
-- Records of feedinglog
-- ----------------------------

-- ----------------------------
-- Table structure for loginlog
-- ----------------------------
DROP TABLE IF EXISTS `loginlog`;
CREATE TABLE `loginlog` (
  `time` datetime NOT NULL,
  `ip` varchar(15) NOT NULL DEFAULT '',
  `user` varchar(23) NOT NULL DEFAULT '',
  `rcode` tinyint(4) NOT NULL DEFAULT '0',
  `log` varchar(255) NOT NULL DEFAULT '',
  KEY `ip` (`ip`)
) ENGINE=MyISAM DEFAULT CHARSET=gbk;

-- ----------------------------
-- Records of loginlog
-- ----------------------------
INSERT INTO `loginlog` VALUES ('2019-04-10 21:30:43', '0.0.0.0', 'login server', '100', 'login server started');
INSERT INTO `loginlog` VALUES ('2019-04-10 21:30:45', '127.0.0.1', 'n1', '100', 'charserver - 99Max???????? @127.0.0.1:6121');
INSERT INTO `loginlog` VALUES ('2019-04-10 21:32:04', '0.0.0.0', 'login server', '100', 'login server started');
INSERT INTO `loginlog` VALUES ('2019-04-10 21:32:06', '127.0.0.1', 'n1', '100', 'charserver - 99Max???????? @127.0.0.1:6121');
INSERT INTO `loginlog` VALUES ('2019-04-10 21:32:54', '127.0.0.1', 'admin', '100', 'login ok');
INSERT INTO `loginlog` VALUES ('2019-04-10 21:33:59', '0.0.0.0', 'login server', '100', 'login server shutdown');
INSERT INTO `loginlog` VALUES ('2021-05-15 06:28:51', '0.0.0.0', 'login server', '100', 'login server started');
INSERT INTO `loginlog` VALUES ('2021-05-15 06:28:52', '127.0.0.1', 'n1', '100', 'charserver - RO???????? @127.0.0.1:6121');
INSERT INTO `loginlog` VALUES ('2021-05-15 06:29:10', '127.0.0.1', 'admin', '100', 'login ok');
INSERT INTO `loginlog` VALUES ('2021-05-15 14:30:44', '0.0.0.0', 'login server', '100', 'login server started');
INSERT INTO `loginlog` VALUES ('2021-05-15 14:30:45', '127.0.0.1', 'n1', '100', 'charserver - RO???????? @127.0.0.1:6121');
INSERT INTO `loginlog` VALUES ('2021-05-15 14:31:56', '127.0.0.1', 'admin', '100', 'login ok');
INSERT INTO `loginlog` VALUES ('2021-05-15 06:34:13', '0.0.0.0', 'login server', '100', 'login server started');
INSERT INTO `loginlog` VALUES ('2021-05-15 06:34:14', '127.0.0.1', 'n1', '100', 'charserver - RO???????? @127.0.0.1:6121');
INSERT INTO `loginlog` VALUES ('2021-05-15 06:34:41', '127.0.0.1', 'admin', '100', 'login ok');
INSERT INTO `loginlog` VALUES ('2021-05-15 06:35:07', '127.0.0.1', 'admin', '100', 'login ok');
INSERT INTO `loginlog` VALUES ('2021-05-20 15:43:33', '0.0.0.0', 'login server', '100', 'login server started');
INSERT INTO `loginlog` VALUES ('2021-05-20 15:43:35', '127.0.0.1', 'n1', '100', 'charserver - RO???????? @127.0.0.1:6121');
INSERT INTO `loginlog` VALUES ('2021-05-20 15:44:03', '127.0.0.1', 'admin', '100', 'login ok');
INSERT INTO `loginlog` VALUES ('2021-05-23 04:16:19', '0.0.0.0', 'login server', '100', 'login server started');
INSERT INTO `loginlog` VALUES ('2021-05-23 04:16:20', '127.0.0.1', 'n1', '100', 'charserver - RO???????? @127.0.0.1:6121');
INSERT INTO `loginlog` VALUES ('2021-05-23 04:16:40', '127.0.0.1', 'admin', '100', 'login ok');
INSERT INTO `loginlog` VALUES ('2022-05-20 18:05:33', '0.0.0.0', 'login server', '100', 'login server started');
INSERT INTO `loginlog` VALUES ('2022-05-20 18:05:34', '127.0.0.1', 'n1', '100', 'charserver - RO???????? @127.0.0.1:6121');
INSERT INTO `loginlog` VALUES ('2022-05-20 18:06:06', '127.0.0.1', 'admin', '100', 'login ok');

-- ----------------------------
-- Table structure for mvplog
-- ----------------------------
DROP TABLE IF EXISTS `mvplog`;
CREATE TABLE `mvplog` (
  `mvp_id` mediumint(9) unsigned NOT NULL AUTO_INCREMENT,
  `mvp_date` datetime NOT NULL,
  `kill_char_id` int(11) NOT NULL DEFAULT '0',
  `monster_id` smallint(6) NOT NULL DEFAULT '0',
  `prize` smallint(5) unsigned NOT NULL DEFAULT '0',
  `mvpexp` bigint(20) unsigned NOT NULL DEFAULT '0',
  `map` varchar(11) NOT NULL DEFAULT '',
  PRIMARY KEY (`mvp_id`)
) ENGINE=MyISAM DEFAULT CHARSET=gbk;

-- ----------------------------
-- Records of mvplog
-- ----------------------------

-- ----------------------------
-- Table structure for npclog
-- ----------------------------
DROP TABLE IF EXISTS `npclog`;
CREATE TABLE `npclog` (
  `npc_id` mediumint(9) unsigned NOT NULL AUTO_INCREMENT,
  `npc_date` datetime NOT NULL,
  `account_id` int(11) unsigned NOT NULL DEFAULT '0',
  `char_id` int(11) unsigned NOT NULL DEFAULT '0',
  `char_name` varchar(25) NOT NULL DEFAULT '',
  `map` varchar(11) NOT NULL DEFAULT '',
  `mes` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`npc_id`),
  KEY `account_id` (`account_id`),
  KEY `char_id` (`char_id`)
) ENGINE=MyISAM DEFAULT CHARSET=gbk;

-- ----------------------------
-- Records of npclog
-- ----------------------------

-- ----------------------------
-- Table structure for picklog
-- ----------------------------
DROP TABLE IF EXISTS `picklog`;
CREATE TABLE `picklog` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `time` datetime NOT NULL,
  `char_id` int(11) NOT NULL DEFAULT '0',
  `type` enum('M','P','L','T','V','S','N','C','A','R','G','E','B','O','I','X','D','U','$','F','Y','Z','Q','H') NOT NULL DEFAULT 'P',
  `nameid` smallint(5) unsigned NOT NULL DEFAULT '0',
  `amount` int(11) NOT NULL DEFAULT '1',
  `refine` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `card0` smallint(5) unsigned NOT NULL DEFAULT '0',
  `card1` smallint(5) unsigned NOT NULL DEFAULT '0',
  `card2` smallint(5) unsigned NOT NULL DEFAULT '0',
  `card3` smallint(5) unsigned NOT NULL DEFAULT '0',
  `option_id0` smallint(5) NOT NULL DEFAULT '0',
  `option_val0` smallint(5) NOT NULL DEFAULT '0',
  `option_parm0` tinyint(3) NOT NULL DEFAULT '0',
  `option_id1` smallint(5) NOT NULL DEFAULT '0',
  `option_val1` smallint(5) NOT NULL DEFAULT '0',
  `option_parm1` tinyint(3) NOT NULL DEFAULT '0',
  `option_id2` smallint(5) NOT NULL DEFAULT '0',
  `option_val2` smallint(5) NOT NULL DEFAULT '0',
  `option_parm2` tinyint(3) NOT NULL DEFAULT '0',
  `option_id3` smallint(5) NOT NULL DEFAULT '0',
  `option_val3` smallint(5) NOT NULL DEFAULT '0',
  `option_parm3` tinyint(3) NOT NULL DEFAULT '0',
  `option_id4` smallint(5) NOT NULL DEFAULT '0',
  `option_val4` smallint(5) NOT NULL DEFAULT '0',
  `option_parm4` tinyint(3) NOT NULL DEFAULT '0',
  `unique_id` bigint(20) unsigned NOT NULL DEFAULT '0',
  `map` varchar(11) NOT NULL DEFAULT '',
  `bound` tinyint(1) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `type` (`type`)
) ENGINE=MyISAM DEFAULT CHARSET=gbk;

-- ----------------------------
-- Records of picklog
-- ----------------------------

-- ----------------------------
-- Table structure for zenylog
-- ----------------------------
DROP TABLE IF EXISTS `zenylog`;
CREATE TABLE `zenylog` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `time` datetime NOT NULL,
  `char_id` int(11) NOT NULL DEFAULT '0',
  `src_id` int(11) NOT NULL DEFAULT '0',
  `type` enum('T','V','P','M','S','N','D','C','A','E','I','B','K') NOT NULL DEFAULT 'S',
  `amount` int(11) NOT NULL DEFAULT '0',
  `map` varchar(11) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `type` (`type`)
) ENGINE=MyISAM DEFAULT CHARSET=gbk;

-- ----------------------------
-- Records of zenylog
-- ----------------------------
