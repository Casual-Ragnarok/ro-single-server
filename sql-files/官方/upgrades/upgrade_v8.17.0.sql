/*
---------------------------------------------
-    以下适用于全部v8.16.0用户 [Main]       -
---------------------------------------------
*/

-- ----------------------------
-- upgrade_20170830.sql
-- ----------------------------
ALTER TABLE `char`
	MODIFY `max_hp` int(11) unsigned NOT NULL default '0',
	MODIFY `hp` int(11) unsigned NOT NULL default '0',
	MODIFY `max_sp` int(11) unsigned NOT NULL default '0',
	MODIFY `sp` int(11) unsigned NOT NULL default '0';

ALTER TABLE `elemental`
	MODIFY `hp` int(11) unsigned NOT NULL default '0',
	MODIFY `sp` int(11) unsigned NOT NULL default '0',
	MODIFY `max_hp` int(11) unsigned NOT NULL default '0',
	MODIFY `max_sp` int(11) unsigned NOT NULL default '0';

ALTER TABLE `homunculus`
	MODIFY `hp` int(11) unsigned NOT NULL default '0',
	MODIFY `max_hp` int(11) unsigned NOT NULL default '0',
	MODIFY `sp` int(11) unsigned NOT NULL default '0',
	MODIFY `max_sp` int(11) unsigned NOT NULL default '0';

ALTER TABLE `mercenary`
	MODIFY `hp` int(11) unsigned NOT NULL default '0',
	MODIFY `sp` int(11) unsigned NOT NULL default '0';

-- ----------------------------
-- upgrade_20171001.sql
-- ----------------------------
ALTER TABLE `auction`
	CHANGE COLUMN `option_id0` `option_id0` SMALLINT(5) NOT NULL DEFAULT '0' AFTER `card3`,
	CHANGE COLUMN `option_val0` `option_val0` SMALLINT(5) NOT NULL DEFAULT '0' AFTER `option_id0`,
	CHANGE COLUMN `option_parm0` `option_parm0` TINYINT(3) NOT NULL DEFAULT '0' AFTER `option_val0`,
	CHANGE COLUMN `option_id1` `option_id1` SMALLINT(5) NOT NULL DEFAULT '0' AFTER `option_parm0`,
	CHANGE COLUMN `option_val1` `option_val1` SMALLINT(5) NOT NULL DEFAULT '0' AFTER `option_id1`,
	CHANGE COLUMN `option_parm1` `option_parm1` TINYINT(3) NOT NULL DEFAULT '0' AFTER `option_val1`,
	CHANGE COLUMN `option_id2` `option_id2` SMALLINT(5) NOT NULL DEFAULT '0' AFTER `option_parm1`,
	CHANGE COLUMN `option_val2` `option_val2` SMALLINT(5) NOT NULL DEFAULT '0' AFTER `option_id2`,
	CHANGE COLUMN `option_parm2` `option_parm2` TINYINT(3) NOT NULL DEFAULT '0' AFTER `option_val2`,
	CHANGE COLUMN `option_id3` `option_id3` SMALLINT(5) NOT NULL DEFAULT '0' AFTER `option_parm2`,
	CHANGE COLUMN `option_val3` `option_val3` SMALLINT(5) NOT NULL DEFAULT '0' AFTER `option_id3`,
	CHANGE COLUMN `option_parm3` `option_parm3` TINYINT(3) NOT NULL DEFAULT '0' AFTER `option_val3`,
	CHANGE COLUMN `option_id4` `option_id4` SMALLINT(5) NOT NULL DEFAULT '0' AFTER `option_parm3`,
	CHANGE COLUMN `option_val4` `option_val4` SMALLINT(5) NOT NULL DEFAULT '0' AFTER `option_id4`,
	CHANGE COLUMN `option_parm4` `option_parm4` TINYINT(3) NOT NULL DEFAULT '0' AFTER `option_val4`;

ALTER TABLE `cart_inventory`
	CHANGE COLUMN `option_id0` `option_id0` SMALLINT(5) NOT NULL DEFAULT '0' AFTER `card3`,
	CHANGE COLUMN `option_val0` `option_val0` SMALLINT(5) NOT NULL DEFAULT '0' AFTER `option_id0`,
	CHANGE COLUMN `option_parm0` `option_parm0` TINYINT(3) NOT NULL DEFAULT '0' AFTER `option_val0`,
	CHANGE COLUMN `option_id1` `option_id1` SMALLINT(5) NOT NULL DEFAULT '0' AFTER `option_parm0`,
	CHANGE COLUMN `option_val1` `option_val1` SMALLINT(5) NOT NULL DEFAULT '0' AFTER `option_id1`,
	CHANGE COLUMN `option_parm1` `option_parm1` TINYINT(3) NOT NULL DEFAULT '0' AFTER `option_val1`,
	CHANGE COLUMN `option_id2` `option_id2` SMALLINT(5) NOT NULL DEFAULT '0' AFTER `option_parm1`,
	CHANGE COLUMN `option_val2` `option_val2` SMALLINT(5) NOT NULL DEFAULT '0' AFTER `option_id2`,
	CHANGE COLUMN `option_parm2` `option_parm2` TINYINT(3) NOT NULL DEFAULT '0' AFTER `option_val2`,
	CHANGE COLUMN `option_id3` `option_id3` SMALLINT(5) NOT NULL DEFAULT '0' AFTER `option_parm2`,
	CHANGE COLUMN `option_val3` `option_val3` SMALLINT(5) NOT NULL DEFAULT '0' AFTER `option_id3`,
	CHANGE COLUMN `option_parm3` `option_parm3` TINYINT(3) NOT NULL DEFAULT '0' AFTER `option_val3`,
	CHANGE COLUMN `option_id4` `option_id4` SMALLINT(5) NOT NULL DEFAULT '0' AFTER `option_parm3`,
	CHANGE COLUMN `option_val4` `option_val4` SMALLINT(5) NOT NULL DEFAULT '0' AFTER `option_id4`,
	CHANGE COLUMN `option_parm4` `option_parm4` TINYINT(3) NOT NULL DEFAULT '0' AFTER `option_val4`;

ALTER TABLE `guild_storage`
	CHANGE COLUMN `option_id0` `option_id0` SMALLINT(5) NOT NULL DEFAULT '0' AFTER `card3`,
	CHANGE COLUMN `option_val0` `option_val0` SMALLINT(5) NOT NULL DEFAULT '0' AFTER `option_id0`,
	CHANGE COLUMN `option_parm0` `option_parm0` TINYINT(3) NOT NULL DEFAULT '0' AFTER `option_val0`,
	CHANGE COLUMN `option_id1` `option_id1` SMALLINT(5) NOT NULL DEFAULT '0' AFTER `option_parm0`,
	CHANGE COLUMN `option_val1` `option_val1` SMALLINT(5) NOT NULL DEFAULT '0' AFTER `option_id1`,
	CHANGE COLUMN `option_parm1` `option_parm1` TINYINT(3) NOT NULL DEFAULT '0' AFTER `option_val1`,
	CHANGE COLUMN `option_id2` `option_id2` SMALLINT(5) NOT NULL DEFAULT '0' AFTER `option_parm1`,
	CHANGE COLUMN `option_val2` `option_val2` SMALLINT(5) NOT NULL DEFAULT '0' AFTER `option_id2`,
	CHANGE COLUMN `option_parm2` `option_parm2` TINYINT(3) NOT NULL DEFAULT '0' AFTER `option_val2`,
	CHANGE COLUMN `option_id3` `option_id3` SMALLINT(5) NOT NULL DEFAULT '0' AFTER `option_parm2`,
	CHANGE COLUMN `option_val3` `option_val3` SMALLINT(5) NOT NULL DEFAULT '0' AFTER `option_id3`,
	CHANGE COLUMN `option_parm3` `option_parm3` TINYINT(3) NOT NULL DEFAULT '0' AFTER `option_val3`,
	CHANGE COLUMN `option_id4` `option_id4` SMALLINT(5) NOT NULL DEFAULT '0' AFTER `option_parm3`,
	CHANGE COLUMN `option_val4` `option_val4` SMALLINT(5) NOT NULL DEFAULT '0' AFTER `option_id4`,
	CHANGE COLUMN `option_parm4` `option_parm4` TINYINT(3) NOT NULL DEFAULT '0' AFTER `option_val4`;

ALTER TABLE `auction`
	CHANGE COLUMN `option_id0` `option_id0` SMALLINT(5) NOT NULL DEFAULT '0' AFTER `card3`,
	CHANGE COLUMN `option_val0` `option_val0` SMALLINT(5) NOT NULL DEFAULT '0' AFTER `option_id0`,
	CHANGE COLUMN `option_parm0` `option_parm0` TINYINT(3) NOT NULL DEFAULT '0' AFTER `option_val0`,
	CHANGE COLUMN `option_id1` `option_id1` SMALLINT(5) NOT NULL DEFAULT '0' AFTER `option_parm0`,
	CHANGE COLUMN `option_val1` `option_val1` SMALLINT(5) NOT NULL DEFAULT '0' AFTER `option_id1`,
	CHANGE COLUMN `option_parm1` `option_parm1` TINYINT(3) NOT NULL DEFAULT '0' AFTER `option_val1`,
	CHANGE COLUMN `option_id2` `option_id2` SMALLINT(5) NOT NULL DEFAULT '0' AFTER `option_parm1`,
	CHANGE COLUMN `option_val2` `option_val2` SMALLINT(5) NOT NULL DEFAULT '0' AFTER `option_id2`,
	CHANGE COLUMN `option_parm2` `option_parm2` TINYINT(3) NOT NULL DEFAULT '0' AFTER `option_val2`,
	CHANGE COLUMN `option_id3` `option_id3` SMALLINT(5) NOT NULL DEFAULT '0' AFTER `option_parm2`,
	CHANGE COLUMN `option_val3` `option_val3` SMALLINT(5) NOT NULL DEFAULT '0' AFTER `option_id3`,
	CHANGE COLUMN `option_parm3` `option_parm3` TINYINT(3) NOT NULL DEFAULT '0' AFTER `option_val3`,
	CHANGE COLUMN `option_id4` `option_id4` SMALLINT(5) NOT NULL DEFAULT '0' AFTER `option_parm3`,
	CHANGE COLUMN `option_val4` `option_val4` SMALLINT(5) NOT NULL DEFAULT '0' AFTER `option_id4`,
	CHANGE COLUMN `option_parm4` `option_parm4` TINYINT(3) NOT NULL DEFAULT '0' AFTER `option_val4`;

ALTER TABLE `inventory`
	CHANGE COLUMN `option_id0` `option_id0` SMALLINT(5) NOT NULL DEFAULT '0' AFTER `card3`,
	CHANGE COLUMN `option_val0` `option_val0` SMALLINT(5) NOT NULL DEFAULT '0' AFTER `option_id0`,
	CHANGE COLUMN `option_parm0` `option_parm0` TINYINT(3) NOT NULL DEFAULT '0' AFTER `option_val0`,
	CHANGE COLUMN `option_id1` `option_id1` SMALLINT(5) NOT NULL DEFAULT '0' AFTER `option_parm0`,
	CHANGE COLUMN `option_val1` `option_val1` SMALLINT(5) NOT NULL DEFAULT '0' AFTER `option_id1`,
	CHANGE COLUMN `option_parm1` `option_parm1` TINYINT(3) NOT NULL DEFAULT '0' AFTER `option_val1`,
	CHANGE COLUMN `option_id2` `option_id2` SMALLINT(5) NOT NULL DEFAULT '0' AFTER `option_parm1`,
	CHANGE COLUMN `option_val2` `option_val2` SMALLINT(5) NOT NULL DEFAULT '0' AFTER `option_id2`,
	CHANGE COLUMN `option_parm2` `option_parm2` TINYINT(3) NOT NULL DEFAULT '0' AFTER `option_val2`,
	CHANGE COLUMN `option_id3` `option_id3` SMALLINT(5) NOT NULL DEFAULT '0' AFTER `option_parm2`,
	CHANGE COLUMN `option_val3` `option_val3` SMALLINT(5) NOT NULL DEFAULT '0' AFTER `option_id3`,
	CHANGE COLUMN `option_parm3` `option_parm3` TINYINT(3) NOT NULL DEFAULT '0' AFTER `option_val3`,
	CHANGE COLUMN `option_id4` `option_id4` SMALLINT(5) NOT NULL DEFAULT '0' AFTER `option_parm3`,
	CHANGE COLUMN `option_val4` `option_val4` SMALLINT(5) NOT NULL DEFAULT '0' AFTER `option_id4`,
	CHANGE COLUMN `option_parm4` `option_parm4` TINYINT(3) NOT NULL DEFAULT '0' AFTER `option_val4`;
	
ALTER TABLE `mail_attachments`
	CHANGE COLUMN `option_id0` `option_id0` SMALLINT(5) NOT NULL DEFAULT '0' AFTER `card3`,
	CHANGE COLUMN `option_val0` `option_val0` SMALLINT(5) NOT NULL DEFAULT '0' AFTER `option_id0`,
	CHANGE COLUMN `option_parm0` `option_parm0` TINYINT(3) NOT NULL DEFAULT '0' AFTER `option_val0`,
	CHANGE COLUMN `option_id1` `option_id1` SMALLINT(5) NOT NULL DEFAULT '0' AFTER `option_parm0`,
	CHANGE COLUMN `option_val1` `option_val1` SMALLINT(5) NOT NULL DEFAULT '0' AFTER `option_id1`,
	CHANGE COLUMN `option_parm1` `option_parm1` TINYINT(3) NOT NULL DEFAULT '0' AFTER `option_val1`,
	CHANGE COLUMN `option_id2` `option_id2` SMALLINT(5) NOT NULL DEFAULT '0' AFTER `option_parm1`,
	CHANGE COLUMN `option_val2` `option_val2` SMALLINT(5) NOT NULL DEFAULT '0' AFTER `option_id2`,
	CHANGE COLUMN `option_parm2` `option_parm2` TINYINT(3) NOT NULL DEFAULT '0' AFTER `option_val2`,
	CHANGE COLUMN `option_id3` `option_id3` SMALLINT(5) NOT NULL DEFAULT '0' AFTER `option_parm2`,
	CHANGE COLUMN `option_val3` `option_val3` SMALLINT(5) NOT NULL DEFAULT '0' AFTER `option_id3`,
	CHANGE COLUMN `option_parm3` `option_parm3` TINYINT(3) NOT NULL DEFAULT '0' AFTER `option_val3`,
	CHANGE COLUMN `option_id4` `option_id4` SMALLINT(5) NOT NULL DEFAULT '0' AFTER `option_parm3`,
	CHANGE COLUMN `option_val4` `option_val4` SMALLINT(5) NOT NULL DEFAULT '0' AFTER `option_id4`,
	CHANGE COLUMN `option_parm4` `option_parm4` TINYINT(3) NOT NULL DEFAULT '0' AFTER `option_val4`;

ALTER TABLE `storage`
	CHANGE COLUMN `option_id0` `option_id0` SMALLINT(5) NOT NULL DEFAULT '0' AFTER `card3`,
	CHANGE COLUMN `option_val0` `option_val0` SMALLINT(5) NOT NULL DEFAULT '0' AFTER `option_id0`,
	CHANGE COLUMN `option_parm0` `option_parm0` TINYINT(3) NOT NULL DEFAULT '0' AFTER `option_val0`,
	CHANGE COLUMN `option_id1` `option_id1` SMALLINT(5) NOT NULL DEFAULT '0' AFTER `option_parm0`,
	CHANGE COLUMN `option_val1` `option_val1` SMALLINT(5) NOT NULL DEFAULT '0' AFTER `option_id1`,
	CHANGE COLUMN `option_parm1` `option_parm1` TINYINT(3) NOT NULL DEFAULT '0' AFTER `option_val1`,
	CHANGE COLUMN `option_id2` `option_id2` SMALLINT(5) NOT NULL DEFAULT '0' AFTER `option_parm1`,
	CHANGE COLUMN `option_val2` `option_val2` SMALLINT(5) NOT NULL DEFAULT '0' AFTER `option_id2`,
	CHANGE COLUMN `option_parm2` `option_parm2` TINYINT(3) NOT NULL DEFAULT '0' AFTER `option_val2`,
	CHANGE COLUMN `option_id3` `option_id3` SMALLINT(5) NOT NULL DEFAULT '0' AFTER `option_parm2`,
	CHANGE COLUMN `option_val3` `option_val3` SMALLINT(5) NOT NULL DEFAULT '0' AFTER `option_id3`,
	CHANGE COLUMN `option_parm3` `option_parm3` TINYINT(3) NOT NULL DEFAULT '0' AFTER `option_val3`,
	CHANGE COLUMN `option_id4` `option_id4` SMALLINT(5) NOT NULL DEFAULT '0' AFTER `option_parm3`,
	CHANGE COLUMN `option_val4` `option_val4` SMALLINT(5) NOT NULL DEFAULT '0' AFTER `option_id4`,
	CHANGE COLUMN `option_parm4` `option_parm4` TINYINT(3) NOT NULL DEFAULT '0' AFTER `option_val4`;

-- ----------------------------
-- upgrade_20171019.sql
-- ----------------------------
INSERT INTO `skill` (`char_id`, `id`, `lv`, `flag`)
  SELECT `char_id`, 5065, 1, 0 FROM `char` WHERE `father` > 0 OR `mother` > 0;

/*
---------------------------------------------
-    以下适用于全部v8.16.0用户 [Logs]       -
---------------------------------------------
*/

-- ----------------------------
-- upgrade_20170830.sql
-- ----------------------------
ALTER TABLE `picklog`
	CHANGE COLUMN `option_id0` `option_id0` SMALLINT(5) NOT NULL DEFAULT '0' AFTER `card3`,
	CHANGE COLUMN `option_val0` `option_val0` SMALLINT(5) NOT NULL DEFAULT '0' AFTER `option_id0`,
	CHANGE COLUMN `option_parm0` `option_parm0` TINYINT(3) NOT NULL DEFAULT '0' AFTER `option_val0`,
	CHANGE COLUMN `option_id1` `option_id1` SMALLINT(5) NOT NULL DEFAULT '0' AFTER `option_parm0`,
	CHANGE COLUMN `option_val1` `option_val1` SMALLINT(5) NOT NULL DEFAULT '0' AFTER `option_id1`,
	CHANGE COLUMN `option_parm1` `option_parm1` TINYINT(3) NOT NULL DEFAULT '0' AFTER `option_val1`,
	CHANGE COLUMN `option_id2` `option_id2` SMALLINT(5) NOT NULL DEFAULT '0' AFTER `option_parm1`,
	CHANGE COLUMN `option_val2` `option_val2` SMALLINT(5) NOT NULL DEFAULT '0' AFTER `option_id2`,
	CHANGE COLUMN `option_parm2` `option_parm2` TINYINT(3) NOT NULL DEFAULT '0' AFTER `option_val2`,
	CHANGE COLUMN `option_id3` `option_id3` SMALLINT(5) NOT NULL DEFAULT '0' AFTER `option_parm2`,
	CHANGE COLUMN `option_val3` `option_val3` SMALLINT(5) NOT NULL DEFAULT '0' AFTER `option_id3`,
	CHANGE COLUMN `option_parm3` `option_parm3` TINYINT(3) NOT NULL DEFAULT '0' AFTER `option_val3`,
	CHANGE COLUMN `option_id4` `option_id4` SMALLINT(5) NOT NULL DEFAULT '0' AFTER `option_parm3`,
	CHANGE COLUMN `option_val4` `option_val4` SMALLINT(5) NOT NULL DEFAULT '0' AFTER `option_id4`,
	CHANGE COLUMN `option_parm4` `option_parm4` TINYINT(3) NOT NULL DEFAULT '0' AFTER `option_val4`;

/*
---------------------------------------------
-  以下适用于将DB使用SQL数据库者，非者勿用  -
---------------------------------------------
*/


