<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: optimizer_cloudsecurity.php 33488 2013-06-24 01:48:20Z jeffjzhang $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class optimizer_cloudsecurity {

	public function __construct() {

	}

	public function check() {
		$apps = C::t('common_setting')->fetch('cloud_apps', true);
		$security = $apps['security'];
		if(!isset($security['status']) || $security['status'] == 'close') {
			$return = array('status' => 1, 'type' =>'header', 'lang' => lang('optimizer', 'optimizer_cloudsecurity_need'));
		} else {
			$securitysetting = C::t('common_setting')->fetch('security_usergroups_white_list');
			$securitysafelogin = C::t('common_setting')->fetch('security_safelogin');
			if($securitysetting != serialize(array(1, 2, 3)) || $securitysafelogin != 1) {
				$return = array('status' => 1, 'type' =>'header', 'lang' => lang('optimizer', 'optimizer_cloudsecurity_setting_need'));
			} else {
				$return = array('status' => 0, 'type' =>'none', 'lang' => lang('optimizer', 'optimizer_cloudsecurity_no_need'));
			}
		}
		return $return;
	}

	public function optimizer() {
		$adminfile = defined(ADMINSCRIPT) ? ADMINSCRIPT : 'admin.php';
		dheader('Location: '.$_G['siteurl'].$adminfile.'?action=cloud&operation=applist');
	}
}

?>