<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: UserApplication.php 33052 2013-04-12 09:39:49Z zhengqingpeng $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class Cloud_Service_Server_UserApplication extends Cloud_Service_Server_Restful {

	protected static $_instance;

	public static function getInstance() {

		if (!(self::$_instance instanceof self)) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	public function onUserApplicationAdd($uId, $appId, $appName, $privacy, $allowSideNav, $allowFeed, $allowProfileLink, $defaultBoxType, $defaultMYML, $defaultProfileLink, $version, $displayMethod, $displayOrder = null, $userPanelArea = null, $canvasTitle = null, $isFullscreen = null , $displayUserPanel = null, $additionalStatus = null) {
		global $_G;

		$res = $this->getUserSpace($uId);
		if (!$res) {
			return new Cloud_Service_Server_ErrorResponse('1', "User($uId) Not Exists");
		}

		$row = C::t('home_userapp')->fetch_by_uid_appid($uId, $appId);

		if ($row['appid']) {
			$errCode = '170';
			$errMessage = 'Application has been already added';
			return new Cloud_Service_Server_ErrorResponse($errCode, $errMessage);
		}

		switch($privacy) {
			case 'public':
				$privacy = 0;
				break;
			case 'friends':
				$privacy = 1;
				break;
			case 'me':
				$privacy = 3;
				break;
			case 'none':
				$privacy = 5;
				break;
			default:
				$privacy = 0;
		}

		$narrow = ($defaultBoxType == 'narrow') ? 1 : 0;

		$setarr = array('uid' => $uId,
				'appid' => $appId,
				'appname' => $appName,
				'privacy' => $privacy,
				'allowsidenav' => $allowSideNav,
				'allowfeed' => $allowFeed,
				'allowprofilelink' => $allowProfileLink,
				'narrow' => $narrow
				);
		if ($displayOrder !== null) {
			$setarr['displayorder'] = $displayOrder;
		}
		$maxMenuOrder = C::t('home_userapp')->fetch_max_menuorder_by_uid($uId);
		$setarr['menuorder'] = ++$maxMenuOrder;

		C::t('home_userapp')->insert($setarr);

		$fields = array('uid' => $uId,
				'appid' => $appId,
				'profilelink' => $defaultProfileLink,
				'myml' => $defaultMYML
				);
		$result = C::t('home_userappfield')->insert($fields, true);

		updatecreditbyaction('installapp', $uId, array(), $appId);

		require_once libfile('function/cache');
		updatecache('userapp');

		C::t('common_member_status')->update($uId, array('lastactivity' => TIMESTAMP), 'UNBUFFERED');

		$displayMethod = ($displayMethod == 'iframe') ? 1 : 0;
		$this->refreshApplication($appId, $appName, $version, $userPanelArea, $canvasTitle, $isFullscreen, $displayUserPanel, $displayMethod, $narrow, null, null, $additionalStatus);

		return 1;
	}

	public function onUserApplicationRemove($uId, $appIds) {

		$result = C::t('home_userapp')->delete_by_uid_appid($uId, $appIds);
		C::t('home_userappfield')->delete_by_uid_appid($uId, $appIds);

		updatecreditbyaction('installapp', $uId, array(), $appId, -1);

		require_once libfile('function/cache');
		updatecache('userapp');

		return $result;
	}

	public function onUserApplicationUpdate($uId, $appIds, $appName, $privacy, $allowSideNav, $allowFeed, $allowProfileLink, $version, $displayMethod, $displayOrder = null, $userPanelArea = null, $canvasTitle = null, $isFullscreen = null, $displayUserPanel = null) {
		switch($privacy) {
			case 'public':
				$privacy = 0;
				break;
			case 'friends':
				$privacy = 1;
				break;
			case 'me':
				$privacy = 3;
				break;
			case 'none':
				$privacy = 5;
				break;
			default:
				$privacy = 0;
		}

		$setarr = array(
			'appname'	=> $appName,
			'privacy'	=> $privacy,
			'allowsidenav'	=> $allowSideNav,
			'allowfeed'		=> $allowFeed,
			'allowprofilelink'	=> $allowProfileLink
		);
		if ($displayOrder !== null) {
			$setarr['displayorder'] = $displayOrder;
			$setarr['menuorder'] = $displayOrder;
		}

		$result = C::t('home_userapp')->update_by_uid_appid($uId, $appIds, $setarr);

		$displayMethod = ($displayMethod == 'iframe') ? 1 : 0;
		if (is_array($appIds)) {
			foreach($appIds as $appId) {
				$this->refreshApplication($appId, $appName, $version, $userPanelArea, $canvasTitle, $isFullscreen, $displayUserPanel, $displayMethod, null, null, null, null);
			}
		}

		return $result;
	}

	public function onUserApplicationGetInstalled($uId) {

		$result = array();
		foreach(C::t('home_userapp')->fetch_all_by_uid_appid($uId) as $userApp) {
			$result[] = $userApp['appid'];
		}
		return $result;
	}

	public function onUserApplicationGet($uId, $appIds) {

		$result = array();
		foreach(C::t('home_userapp')->fetch_all_by_uid_appid($uId, $appIds) as $userApp) {
			switch($userApp['privacy']) {
				case 0:
					$privacy = 'public';
					break;
				case 1:
					$privacy = 'friends';
					break;
				case 3:
					$privacy = 'me';
					break;
				case 5:
					$privacy = 'none';
					break;
				default:
					$privacy = 'public';
			}
			$result[] = array(
						'appId'		=> $userApp['appid'],
						'privacy'	=> $privacy,
						'allowSideNav'		=> $userApp['allowsidenav'],
						'allowFeed'			=> $userApp['allowfeed'],
						'allowProfileLink'	=> $userApp['allowprofilelink'],
						'displayOrder'		=> $userApp['displayorder']
						);
		}
		return $result;
	}

}