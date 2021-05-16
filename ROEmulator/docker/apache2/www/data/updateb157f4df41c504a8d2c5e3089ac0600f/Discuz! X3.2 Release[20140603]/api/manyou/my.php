<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: my.php 34170 2013-10-28 02:58:29Z nemohou $
 */

define('IN_API', true);
define('CURSCRIPT', 'api');
define('DISABLEDEFENSE', true);
define('DISABLEXSSCHECK', true);
require_once('../../source/class/class_core.php');
require_once('../../source/function/function_home.php');

$cachelist = array();
$discuz = C::app();

$discuz->cachelist = $cachelist;
$discuz->init_cron = false;
$discuz->init_setting = true;
$discuz->init_user = false;
$discuz->init_session = false;

$discuz->init();

$siteId = $_G['setting']['my_siteid'];
$siteKey = $_G['setting']['my_sitekey'];
$timezone = $_G['setting']['timeoffset'];
$language = $_SC['language'] ? $_SC['language'] : 'zh_CN';
$version = $_G['setting']['version'];
$myAppStatus = $_G['setting']['my_app_status'];
$mySearchStatus = $_G['setting']['my_search_status'];

$my = Cloud::loadClass('Service_Server_My', array($siteId, $siteKey, $timezone, $version, CHARSET, $language, $myAppStatus, $mySearchStatus));
$my->run();