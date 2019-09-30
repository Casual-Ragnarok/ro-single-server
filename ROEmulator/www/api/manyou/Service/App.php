<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: App.php 29177 2012-03-28 05:56:17Z yexinhao $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

Cloud::loadFile('Service_AppException');

class Cloud_Service_App {

	protected static $_instance;

	public static function getInstance() {

		if (!(self::$_instance instanceof self)) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	public function __construct() {

	}

	public function checkCloudStatus() {
		global $_G;

		$res = false;

		$cloudStatus = $_G['setting']['cloud_status'];
		$sId = $_G['setting']['my_siteid'];
		$sKey = $_G['setting']['my_sitekey'];

		if($sId && $sKey) {
			switch($cloudStatus) {
			case 1:
				$res = 'cloud';
				break;
			case 2:
				$res = 'unconfirmed';
				break;
			default:
				$res = 'upgrade';
			}
		} elseif(!$cloudStatus && !$sId && !$sKey) {
			$res = 'register';
		} else {
			throw new Cloud_Service_AppException('Cloud status error!', 52101);
		}

		return $res;
	}

	public function getCloudApps($cache = true) {

		$apps = array();

		if($cache) {
			global $_G;
			$apps = $_G['setting']['cloud_apps'];
		} else {
			$apps = C::t('common_setting')->fetch('cloud_apps', true);
		}

		if (!$apps) {
			$apps = array();
		}
		if (!is_array($apps)) {
			$apps = dunserialize($apps);
		}

		unset($apps[0]);

		return $apps;
	}

	public function getCloudAppStatus($appName, $cache = true) {

		$res = false;

		$apps = $this->getCloudApps($cache);
		if ($apps && $apps[$appName]) {
			$res = ($apps[$appName]['status'] == 'normal');
		}

		return $res;
	}

	public function setCloudAppStatus($appName, $status, $cache = true, $updateCache = true) {

		$method = '_' . strtolower($appName) . 'StatusCallback';
		if(!method_exists($this, $method)) {
			throw new Cloud_Service_AppException('Cloud callback: ' . $method . ' not exists!', 51001);
		}

		try {
			$callbackRes = $this->$method($appName, $status);
		} catch (Exception $e) {
			throw new Cloud_Service_AppException($e);
		}
		if (!$callbackRes) {
			throw new Cloud_Service_AppException('Cloud callback: ' . $method . ' error!', 51003);
		}

		$apps = $this->getCloudApps($cache);

		$app = array('name' => $appName, 'status' => $status);
		$apps[$appName] = $app;

		C::t('common_setting')->update('cloud_apps', $apps);

		if ($updateCache) {
			require_once libfile('function/cache');
			updatecache(array('plugin', 'setting', 'styles'));
			cleartemplatecache();
		}

		return true;
	}

	private function _manyouStatusCallback($appName, $status) {

		$available = 0;
		if($status == 'normal') {
			$available = 1;
		}
		return C::t('common_setting')->update('my_app_status', $available);
	}

	private function _connectStatusCallback($appName, $status) {

		$available = 0;
		if($status == 'normal') {
			$available = 1;
		}

		$setting = C::t('common_setting')->fetch('connect', true);
		if(!$setting) {
			$setting = array();
		}
		$setting['allow'] = $available;

		C::t('common_setting')->update('connect', $setting);

		$this->setPluginAvailable('qqconnect', $available);

		return true;
	}

	private function _securityStatusCallback($appName, $status) {

		$available = 0;
		if($status == 'normal') {
			$available = 1;
		}

		$this->setPluginAvailable('security', $available);

		return true;
	}

	private function _storageStatusCallback($appName, $status) {

		$available = 0;
		if($status == 'normal') {
			$available = 1;
		}

		$this->setPluginAvailable('xf_storage', $available);

		return true;
	}

	private function _statsStatusCallback($appName, $status) {

		$available = 0;
		if($status == 'normal') {
			$available = 1;
		}

		$this->setPluginAvailable('cloudstat', $available);

		return true;
	}

	private function _searchStatusCallback($appName, $status) {

		$available = 0;
		if($status == 'normal') {
			$available = 1;
		}

		C::t('common_setting')->update('my_search_status', $available);

		if($available) {
			Cloud::loadFile('Service_SearchHelper');
			Cloud_Service_SearchHelper::allowSearchForum();
		}

		$this->setPluginAvailable('cloudsearch', $available);

		return true;
	}

	private function _smiliesStatusCallback($appName, $status) {

		$available = 0;
		if($status == 'normal') {
			$available = 1;
		}

		$this->setPluginAvailable('soso_smilies', $available);

		return true;
	}

	private function _qqgroupStatusCallback($appName, $status) {

		$available = 0;
		if($status == 'normal') {
			$available = 1;
		}

		$this->setPluginAvailable('qqgroup', $available);

		return true;
	}

	private function _unionStatusCallback($appName, $status) {

		return true;
	}

	function setPluginAvailable($identifier, $available) {

		$available = intval($available);

		$plugin = C::t('common_plugin')->fetch_by_identifier($identifier);

		if(!$plugin || !$plugin['pluginid']) {
			throw new Cloud_Service_AppException('Cloud plugin: ' . $identifier . ' not exists!', 51108);
		}

		C::t('common_plugin')->update($plugin['pluginid'], array('available' => $available));

		return true;
	}

}