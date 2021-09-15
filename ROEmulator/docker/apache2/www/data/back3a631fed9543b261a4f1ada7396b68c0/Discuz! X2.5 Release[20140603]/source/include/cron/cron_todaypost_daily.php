<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: cron_todaypost_daily.php 24471 2011-09-21 03:08:33Z zhengqingpeng $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

$yesterdayposts = intval(C::t('forum_forum')->fetch_sum_todaypost());

$historypost = C::t('common_setting')->fetch('historyposts');
$hpostarray = explode("\t", $historypost);
$_G['setting']['historyposts'] = $hpostarray[1] < $yesterdayposts ? "$yesterdayposts\t$yesterdayposts" : "$yesterdayposts\t$hpostarray[1]";

C::t('common_setting')->update('historyposts', $_G['setting']['historyposts']);
$date = date('Y-m-d', TIMESTAMP - 86400);

C::t('forum_statlog')->insert_stat_log($date);
C::t('forum_forum')->clear_todayposts();

savecache('historyposts', $_G['setting']['historyposts']);

?>