<?php

/**
 *		[Discuz!] (C)2001-2099 Comsenz Inc.
 *		This is NOT a freeware, use is subject to license terms
 *
 *		$Id: Mobile.php 29713 2012-04-26 01:51:38Z yexinhao $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

Cloud::loadFile('Service_Server_Restful');

class Cloud_Service_Server_Mobile extends Cloud_Service_Server_Restful {

	protected static $_instance;

	public static function getInstance() {

		if (!(self::$_instance instanceof self)) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	public function onMobileLoginToken($deviceToken, $uid) {

		return C::t("#mobile#common_devicetoken")->loginToken($deviceToken, $uid);
	}

	public function onMobileLogoutToken($deviceToken, $uid) {

		return C::t("#mobile#common_devicetoken")->logoutToken($deviceToken);
	}

	public function onMobileClearToken($deviceToken) {

		return C::t("#mobile#common_devicetoken")->clearToken($deviceToken);
	}
}