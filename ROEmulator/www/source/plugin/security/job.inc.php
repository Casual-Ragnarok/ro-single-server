<?php
/**
 *		[Discuz!] (C)2001-2099 Comsenz Inc.
 *		This is NOT a freeware, use is subject to license terms
 *
 *		$Id: job.inc.php 29265 2012-03-31 06:03:26Z yexinhao $
 */

if (!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

if ($_POST['formhash'] != formhash()) {
	exit('Access Denied');
}

$securityService = Cloud::loadClass('Service_Security');
$securityService->retryReportData('3');