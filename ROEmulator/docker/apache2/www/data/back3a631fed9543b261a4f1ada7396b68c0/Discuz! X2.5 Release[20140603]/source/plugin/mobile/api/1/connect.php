<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: connect.php 29074 2012-03-26 05:56:06Z monkey $
 */

if(!defined('IN_MOBILE_API')) {
	exit('Access Denied');
}

$_GET['mod'] = 'connect';
include_once 'member.php';

class mobile_api {

	function common() {
	}

	function output() {
		if(!empty($_POST)) {
			mobile_core::result(mobile_core::variable());
		} else {
			global $_G;
			$bbrulehash = $_G['setting']['bbrules'] ? substr(md5(FORMHASH), 0, 8) : '';
			$isconnect = $_G['qc']['connect_app_id'] && $_G['qc']['connect_openid'];
			include template('mobile:register');
			exit;
		}
	}

}

?>