<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: bestanswer.php 35024 2014-10-14 07:43:43Z nemohou $
 */
if (!defined('IN_MOBILE_API')) {
	exit('Access Denied');
}

$_GET['mod'] = 'misc';
$_GET['action'] = 'bestanswer';
$_GET['bestanswersubmit'] = 'yes';
include_once 'forum.php';

class mobile_api {

	function common() {
	}

	function output() {
		$variable = array();
		mobile_core::result(mobile_core::variable($variable));
	}

}

?>