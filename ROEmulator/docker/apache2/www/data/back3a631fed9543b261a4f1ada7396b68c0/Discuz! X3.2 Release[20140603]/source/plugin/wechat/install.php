<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: install.php 34563 2014-05-30 02:29:27Z nemohou $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

$sql = <<<EOF

CREATE TABLE IF NOT EXISTS pre_common_member_wechat (
  `uid` mediumint(8) unsigned NOT NULL,
  `openid` char(32) NOT NULL default '',
  `status` tinyint(1) NOT NULL DEFAULT 0,
  `isregister` tinyint(1) unsigned NOT NULL default '0',
  PRIMARY KEY (`uid`),
  UNIQUE KEY `openid` (`openid`)
) ENGINE=MYISAM;

CREATE TABLE IF NOT EXISTS pre_mobile_wechat_authcode (
  `sid` char(6) NOT NULL,
  `code` int(10) unsigned NOT NULL,
  `uid` mediumint(8) unsigned NOT NULL,
  `status` tinyint(1) NOT NULL DEFAULT 0,
  `createtime` int(10) unsigned NOT NULL,
  PRIMARY KEY (`sid`),
  UNIQUE KEY `code` (`code`),
  KEY `createtime` (`createtime`)
) ENGINE=MEMORY;

CREATE TABLE IF NOT EXISTS pre_common_member_wechatmp (
  `uid` mediumint(8) unsigned NOT NULL,
  `openid` char(32) NOT NULL default '',
  `status` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`uid`)
) ENGINE=MYISAM;

CREATE TABLE IF NOT EXISTS pre_mobile_wsq_threadlist (
  `skey` int(10) unsigned NOT NULL,
  `svalue` text NOT NULL,
  PRIMARY KEY (`skey`)
) ENGINE=MyISAM;

EOF;

runquery($sql);

if(!$_G['setting']['mobilewechat']) {
	$setting = array (
		'wechat_mtype' => '0',
		'wechat_qrtype' => '3',
		'wechat_token' => random(16),
		'wechat_allowregister' => '1',
		'wechat_allowfastregister' => '1',
		'wechat_disableregrule' => '1',
		'wechat_float_qrcode' => '1',
		'wechat_confirmtype' => '0',
		'wechat_user' => 'wx_',
		'wechat_newusergroupid' => $_G['setting']['newusergroupid'],
	);

	$settings = array('mobilewechat' => serialize($setting));
	C::t('common_setting')->update_batch($settings);
}

$finish = TRUE;

?>