<?php
/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: Favorite.php 29721 2012-04-26 07:01:08Z zhengqingpeng $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

Cloud::loadFile('Service_Client_Restful');

class Cloud_Service_Client_Favorite extends Cloud_Service_Client_Restful {

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

	public function add($siteUid, $pkId, $id, $idType, $title, $description, $dateline) {
		$_params = array(
				'openid' => $this->getUserOpenId($siteUid),
				'sSiteUid' => $siteUid,
				'pkId' => $pkId,
				'fromId' => $id,
				'fromIdType' => $idType,
				'title' => $title,
				'description' => $description,
				'dateline' => $dateline,
				'deviceToken' => $this->getUserDeviceToken($siteUid)
			);
		return $this->_callMethod('connect.discuz.favorite.add', $_params);
	}

	public function remove($siteUid, $pkIds, $dateline) {
		$_params = array(
				'openid' => $this->getUserOpenId($siteUid),
				'sSiteUid' => $siteUid,
				'pkIds' => $pkIds,
				'dateline' => $dateline
			);
		return $this->_callMethod('connect.discuz.favorite.remove', $_params);
	}
	protected function _callMethod($method, $args) {
		try {
			return parent::_callMethod($method, $args);
		} catch (Exception $e) {

		}
	}

}