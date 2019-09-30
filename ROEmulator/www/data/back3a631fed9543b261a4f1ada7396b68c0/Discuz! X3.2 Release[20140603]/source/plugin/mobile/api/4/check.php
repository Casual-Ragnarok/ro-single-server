<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: check.php 34525 2014-05-15 05:40:42Z nemohou $
 */

if(!defined('IN_MOBILE_API')) {
	exit('Access Denied');
}

require './source/class/class_core.php';

$file = 'data/sysdata/cache_mobile.php';
if(!file_exists($file)) {
	$discuz = C::app();
	$discuz->init_session = true;
	$discuz->init_cron = false;
	$discuz->init_misc = false;
	$discuz->init_mobile = false;
	$discuz->init();
	require_once DISCUZ_ROOT.'./source/plugin/mobile/cache/cache_mobile.php';
	require_once libfile('function/cache');
	build_cache_plugin_mobile();
}

include $file;

mobile_core::make_cors($_SERVER['REQUEST_METHOD'], REQUEST_METHOD_DOMAIN);

echo $mobilecheck;

?>