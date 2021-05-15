<?php
/**
 *		[Discuz!] (C)2001-2099 Comsenz Inc.
 *		This is NOT a freeware, use is subject to license terms
 *
 *		$Id: sitemaster.inc.php 29265 2012-03-31 06:03:26Z yexinhao $
 */

if (!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

if ($_G['adminid'] <= 0) {
	exit('Access Denied');
}

if ($_POST['formhash'] != formhash()) {
    exit('Access Denied');
}

$securityService = Cloud::loadClass('Service_Security');
$securityClient = Cloud::loadClass('Service_Client_Security');

$typeArray = array('1' => 'post', '2' => 'member');
$operateType = 'member';
$operateData = $securityService->getOperateData($operateType);
if (count($operateData) == 0) {
	$operateType = 'post';
	$operateData = $securityService->getOperateData($operateType);
}
if (count($operateData) == 0 || !is_array($operateData)) {
    exit;
}

$operateThreadData = array();
$operatePostData = array();
if ($operateType == 'post') {
	foreach ($operateData as $tempData) {
		if ($tempData['operateType'] == 'thread') {
			$operateThreadData[] = $tempData;
		} else {
			$operatePostData[] = $tempData;
		}
	}
	if (count($operateThreadData)) {
		$res = $securityClient->securityReportOperation('thread', $operateThreadData);
	} elseif(count($operatePostData)) {
		$res = $securityClient->securityReportOperation('post', $operatePostData);
	}
} elseif(count($operateData)) {
	$res = $securityClient->securityReportOperation($operateType, $operateData);
}
$securityService->markasreported($operateType, $operateData);