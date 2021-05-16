<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: install.php 33766 2013-08-12 02:38:48Z nemohou $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

$sql = <<<EOF

CREATE TABLE IF NOT EXISTS `pre_security_evilpost` (
  `pid` int(10) unsigned NOT NULL COMMENT '帖子ID',
  `tid` mediumint(8) unsigned NOT NULL DEFAULT '0' COMMENT '主题ID',
  `type` tinyint(1) NOT NULL DEFAULT '0' COMMENT '帖子类型',
  `evilcount` int(10) NOT NULL DEFAULT '0' COMMENT '恶意次数',
  `eviltype` mediumint(8) unsigned NOT NULL DEFAULT '0' COMMENT '恶意类型',
  `createtime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `operateresult` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '操作结果：1 通过 2 删除 3 忽略',
  `isreported` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否已经上报',
  `censorword` char(50) NOT NULL,
  PRIMARY KEY (`pid`),
  KEY `type` (`tid`,`type`),
  KEY `operateresult` (`operateresult`,`createtime`)
) ENGINE=MyISAM;

CREATE TABLE IF NOT EXISTS `pre_security_eviluser` (
  `uid` int(10) unsigned NOT NULL COMMENT '用户ID',
  `evilcount` int(10) NOT NULL DEFAULT '0' COMMENT '恶意次数',
  `eviltype` mediumint(8) unsigned NOT NULL DEFAULT '0' COMMENT '恶意类型',
  `createtime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `operateresult` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '操作结果：1 恢复 2 删除 3 忽略',
  `isreported` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否已经上报',
  PRIMARY KEY (`uid`),
  KEY `operateresult` (`operateresult`,`createtime`)
) ENGINE=MyISAM;

CREATE TABLE IF NOT EXISTS `pre_security_failedlog` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '主键',
  `reporttype` char(20) NOT NULL COMMENT '上报类型',
  `tid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'TID',
  `pid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'PID',
  `uid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'UID',
  `failcount` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '计数',
  `createtime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '失败时间',
  `posttime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '发帖时间/上次发帖时间',
  `delreason` char(255) NOT NULL COMMENT '处理原因',
  `scheduletime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '计划重试时间',
  `lastfailtime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '上次失败时间',
  `extra1` int(10) unsigned NOT NULL COMMENT '整型的扩展字段',
  `extra2` char(255) NOT NULL DEFAULT '0' COMMENT '字符类型的扩展字段',
  PRIMARY KEY (`id`),
  KEY `pid` (`pid`),
  KEY `uid` (`uid`)
) ENGINE=MyISAM;

EOF;

runquery($sql);
$cronId_security_daily = $cronId_security_lastpost = 0;
if(file_exists(DISCUZ_ROOT . './source/include/cron/cron_security_cleanup_lastpost.php') || file_exists(DISCUZ_ROOT . './source/include/cron/cron_security_daily.php')) {
	$count = C::t('common_cron')->count();
	$oldData = C::t('common_cron')->range(0, $count);
	foreach ($oldData as $value) {
		if ($value['filename'] == 'cron_security_daily.php') {
			$cronId_security_daily = $value['cronid'];
		}elseif ($value['filename'] == 'cron_security_cleanup_lastpost.php') {
			$cronId_security_lastpost = $value['cronid'];
		}
	}
}
include DISCUZ_ROOT . 'source/language/lang_admincp_cloud.php';
if(file_exists(DISCUZ_ROOT . './source/include/cron/cron_security_cleanup_lastpost.php') && empty($cronId_security_lastpost)) {
	$data = array(
		'available' => 1,
		'type' => 'system',
		'name' => $extend_lang['security_cron_lastpost'],
		'filename' => 'cron_security_cleanup_lastpost.php',
		'weekday' => -1,
		'day' => -1,
		'hour' => 7,
		'minute' => 0,
	);
	C::t('common_cron')->insert($data, true, false, false);
}
if (file_exists(DISCUZ_ROOT . './source/include/cron/cron_security_daily.php')) {
	if (empty($cronId_security_daily)) {
		$data = array(
			'available' => 1,
			'type' => 'system',
			'name' => $extend_lang['security_cron_daily'],
			'filename' => 'cron_security_daily.php',
			'weekday' => -1,
			'day' => -1,
			'hour' => 2,
			'minute' => 0,
		);
		$cronId_security_daily = C::t('common_cron')->insert($data, true, false, false);
	} else {
		C::t('common_cron')->update($cronId_security_daily, array(
			'available' => 1,
			'weekday' => -1,
			'day' => -1,
			'hour' => 2,
			'minute' => 0,
		));
	}
	updatecache('setting');
	discuz_cron::run($cronId_security_daily);
}
$updateData = array(
	'security_usergroups_white_list' => serialize(array(1, 2, 3)),
	'security_safelogin' => 1,
);

C::t('common_setting')->update_batch($updateData);
updatecache('setting');

$finish = true;