<?php

/**
 *		[Discuz!] (C)2001-2099 Comsenz Inc.
 *		This is NOT a freeware, use is subject to license terms
 *
 *		$Id: Mobile.php 34063 2013-09-26 05:37:52Z nemohou $
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

	public function onMobileModule() {
		global $_G;
		if(!$_G['setting']['plugins']['available']) {
			return '';
		}
		require_once libfile('function/admincp');
		loadcache('pluginlanguage_script', 1);
		$return = array();
		foreach($_G['setting']['plugins']['available'] as $pluginid) {
			$row = array();
			$modulefile = DISCUZ_ROOT.'./source/plugin/'.$pluginid.'/discuz_mobile_'.$pluginid.'.xml';
			if(file_exists($modulefile)) {
				$_GET['importtxt'] = @implode('', file($modulefile));
				$pluginarray = getimportdata('Discuz! Mobile', 0, 1);
				if($pluginarray) {
					foreach($pluginarray as $name => $value) {
						$row[] = array(
							'name' => isset($_G['cache']['pluginlanguage_script'][$pluginid][$name]) ? $_G['cache']['pluginlanguage_script'][$pluginid][$name] : $name,
							'logo' => $value['logo'],
							'url' => preg_match('/^http:\/\//', $value['url']) ? $value['url'] : $_G['siteurl'].$value['url'],
						);
					}
				}
			}
			$return[$pluginid] = $row;
		}
		return $return;
	}

}