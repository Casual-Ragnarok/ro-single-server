<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: search_my.php 31728 2012-09-25 09:03:42Z zhouxiaobo $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

define('NOROBOT', TRUE);

if (!$_G['setting']['my_siteid'] || !$_G['setting']['my_search_status']) {
	dheader('Location: index.php');
}

$appService = Cloud::loadClass('Service_App');

if ($appService->getCloudAppStatus('connect')) {
	$connectService = Cloud::loadClass('Cloud_Service_Connect');
	$connectService->connectMergeMember();
}

$searchHelper = Cloud::loadClass('Cloud_Service_SearchHelper');
$myForums = $searchHelper->getForums();

$myExtGroupIds = array();
$_extGroupIds = explode("\t", $_G['member']['extgroupids']);
foreach($_extGroupIds as $v) {
	if ($v) {
		$myExtGroupIds[] = $v;
	}
}
$myExtGroupIdsStr = implode(',', $myExtGroupIds);

$params = array(
				'cuName' => $_G['username'],
				'gId' => $_G['groupid'],
				'agId' => $_G['adminid'],
				'egIds' => $myExtGroupIdsStr,
				'fmSign' => substr($myForums['sign'], -4),
			   );

$groupIds = explode(',', $_G['groupid']);
if ($_G['adminid']) {
	$groupIds[] = $_G['adminid'];
}
if ($myExtGroupIds) {
	$groupIds = array_merge($groupIds, $myExtGroupIds);
}

$groupIds = array_unique($groupIds);
$userGroups = $searchHelper->getUserGroupPermissions($groupIds);
foreach($groupIds as $k => $v) {
	$value =  substr($userGroups[$v]['sign'], -4);
	if ($value) {
		$params['ugSign' . $v] = $value;
	}
}
$params['charset'] = $_G['charset'];
if ($_G['member']['conopenid']) {
	$params['openid'] = $_G['member']['conopenid'];
}

$extra = array('q', 'fId', 'author', 'scope', 'source', 'module', 'isAdv');
foreach($extra as $v) {
	if ($_GET[$v]) {
		$params[$v] = $_GET[$v];
	}
}
$mySearchData = $_G['setting']['my_search_data'];
if ($mySearchData['domain']) {
	$domain = $mySearchData['domain'];
} else {
	$domain = 'search.discuz.qq.com';
}

$utilService = Cloud::loadClass('Cloud_Service_Util');
$url = 'http://' . $domain . '/f/discuz?' . $utilService->generateSiteSignUrl($params, true);

dheader('Location: ' . $url);

?>