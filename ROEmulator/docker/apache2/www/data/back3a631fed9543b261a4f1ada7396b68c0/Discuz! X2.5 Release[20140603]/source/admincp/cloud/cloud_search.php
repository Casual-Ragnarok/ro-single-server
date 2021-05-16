<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: cloud_search.php 29273 2012-03-31 07:58:50Z yexinhao $
 */
if(!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
	exit('Access Denied');
}

$op = $_GET['op'];
$anchor = isset($_GET['anchor']) ? $_GET['anchor'] : 'setting';

if (!$_G['inajax']) {
	cpheader();
}

if(preg_match('/^[a-z|A-Z|\d]+$/', $anchor)) {
	$utilService = Cloud::loadClass('Service_Util');

	$cp_version = $_G['setting']['my_search_data']['cp_version'];

	$params = array('link_url' => ADMINSCRIPT . '?action=cloud&operation=search', 'cp_version' => $cp_version, 'anchor' => $anchor);

	$signUrl = $utilService->generateSiteSignUrl($params);

	$utilService->redirect($cloudDomain . '/search/' . $anchor . '/?' . $signUrl);
}