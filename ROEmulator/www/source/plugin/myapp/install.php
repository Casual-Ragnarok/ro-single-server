<?php
/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: install.php 25889 2011-11-24 09:52:20Z monkey $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

$sql = <<<EOF

DROP TABLE IF EXISTS pre_home_userapp_plying;
CREATE TABLE pre_home_userapp_plying (
  `uid` mediumint(8) unsigned NOT NULL default '0',
  `appid` text NOT NULL,
  PRIMARY KEY (`uid`)
)ENGINE=MyISAM;

EOF;

runquery($sql);

$finish = TRUE;
?>