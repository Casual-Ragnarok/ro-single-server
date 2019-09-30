<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: Security.php 29263 2012-03-31 05:45:08Z yexinhao $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

Cloud::loadFile('Service_Client_Restful');

class Cloud_Service_Client_Security extends Cloud_Service_Client_Restful {

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

	function securityReportUserRegister($batchData) {

		return $this->_callMethod('security.user.register', array('sId' => $this->_sId, 'data' => $batchData));
	}

	function securityReportUserLogin($batchData) {

		return $this->_callMethod('security.user.login', array('sId' => $this->_sId, 'data' => $batchData));
	}

	function securityReportBanUser($batchData) {

		return $this->_callMethod('security.user.ban', array('sId' => $this->_sId, 'data' => $batchData));
	}

	function securityReportPost($batchData) {

		return $this->_callMethod('security.post.handlePost', array('sId' => $this->_sId, 'data' => $batchData));
	}

	function securityReportDelPost($batchData) {

		return $this->_callMethod('security.post.del', array('sId' => $this->_sId, 'data' => $batchData));
	}

	function securityReportOperation($operateType, $results, $operateTime = TIMESTAMP, $extra = array()) {

		$data = array(
					  'sId' => $this->_sId,
					  'sSiteUid' => $this->siteUid,
					  'operateType' => $operateType,
					  'operateTime' => $operateTime,
					  'results' => $results,
					  'extra' => $extra
				  );
		return $this->_callMethod('security.sitemaster.handleOperation', $data);
	}

}