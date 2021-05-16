<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: cloud_union.php 29273 2012-03-31 07:58:50Z yexinhao $
 */
if(!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
	exit('Access Denied');
}

if(!$_G['inajax']) {
	cpheader();
	shownav('navcloud', 'cloud_stats');
}

$unionDomain = 'http://union.discuz.qq.com';
$utilService = Cloud::loadClass('Service_Util');
$signUrl = $utilService->generateSiteSignUrl();

$utilService->redirect($unionDomain . '/site/application/?' . $signUrl);