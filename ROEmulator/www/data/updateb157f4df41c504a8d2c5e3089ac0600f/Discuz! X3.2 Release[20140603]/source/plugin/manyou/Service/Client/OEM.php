<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: OEM.php 33992 2013-09-17 01:01:52Z nemohou $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

Cloud::loadFile('Service_Client_Restful');

class Cloud_Service_Client_OEM extends Cloud_Service_Client_Restful {

	protected static $_instance;

	public static function getInstance($debug = false) {

		if (!(self::$_instance instanceof self)) {
			self::$_instance = new self($debug);
		}

		return self::$_instance;
	}

	public function __construct($debug = false) {

		return parent::__construct($debug);
	}

	public function checkApp() {
		if(!$this->_sId) {
			return array();
		}
		global $_G;
		if($_G['uid']) {
			$connectUser = C::t('#qqconnect#common_member_connect')->fetch($_G['uid']);
			$openid = $connectUser['conopenid'];
		} else {
			$openid = '';
		}
		return $this->_callMethod('oem.checkApp', array('sId' => $this->_sId, 'uId' => $_G['uid'], 'openId' => $openid));
	}

	public function getDownloadUrl() {
		if(!$this->_sId) {
			return '';
		}
		global $_G;
		loadcache('mobileoem_data');
		if(!$_G['cache']['mobileoem_data']['downloadPath']) {
			return '';
		}
		if($_G['uid']) {
			$connectUser = C::t('#qqconnect#common_member_connect')->fetch($_G['uid']);
			$openid = $connectUser['conopenid'];
		} else {
			$openid = '';
		}
		$utilService = Cloud::loadClass('Service_Util');
		return $_G['cache']['mobileoem_data']['downloadPath'].'?'.$utilService->generateSiteSignUrl(array('sId' => $this->_sId, 'uId' => $_G['uid'], 'openId' => $openid));
	}

}