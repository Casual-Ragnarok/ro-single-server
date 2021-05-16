<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: Application.php 25813 2011-11-22 10:07:48Z zhengqingpeng $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class Cloud_Service_Server_Application extends Cloud_Service_Server_Restful {

	protected static $_instance;

	public static function getInstance() {

		if (!(self::$_instance instanceof self)) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	public function onApplicationUpdate($appId, $appName, $version, $displayMethod, $displayOrder = null, $userPanelArea = null, $canvasTitle = null, $isFullscreen = null, $displayUserPanel = null, $additionalStatus = null) {

		$row = C::t('common_myapp')->fetch($appId);
		$result = true;
		if ($row['appname'] != $appName) {
			$fields = array('appname' => $appName);
			$result = C::t('home_userapp')->update_by_uid_appid(0, $appId, $fields);

			require_once libfile('function/cache');
			updatecache('userapp');
		}

		$displayMethod = ($displayMethod == 'iframe') ? 1 : 0;
		$this->refreshApplication($appId, $appName, $version, $userPanelArea, $canvasTitle, $isFullscreen, $displayUserPanel, $displayMethod, null, null, $displayOrder, $additionalStatus);
		return $result;
	}

	public function onApplicationRemove($appIds) {

		$result = C::t('home_userapp')->delete_by_uid_appid(0, $appIds);
		C::t('home_userappfield')->delete_by_uid_appid(0, $appIds);

		C::t('common_myapp')->delete($appIds);

		require_once libfile('function/cache');
		updatecache(array('userapp', 'myapp'));

		return $result;
	}

	public function onApplicationSetFlag($applications, $flag) {
		$flag = ($flag == 'disabled') ? -1 : ($flag == 'default' ? 1 : 0);
		$appIds = array();
		if ($applications && is_array($applications)) {
			foreach($applications as $application) {
				$this->refreshApplication($application['appId'], $application['appName'], null, null, null, null, null, null, null, $flag, null, null);
				$appIds[] = $application['appId'];
			}
		}

		if ($flag == -1) {

			C::t('home_feed')->delete_by_icon($appIds);

			C::t('home_userapp')->delete_by_uid_appid(0, $appIds);
			C::t('home_userappfield')->delete_by_uid_appid(0, $appIds);

			C::t('common_myinvite')->delete_by_appid($appIds);
			C::t('home_notification')->delete_by_type($appIds);
		}

		require_once libfile('function/cache');
		updatecache('userapp');

		$result = true;
		return $result;
	}

}