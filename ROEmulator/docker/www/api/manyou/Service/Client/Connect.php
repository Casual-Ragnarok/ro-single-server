<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: Connect.php 25946 2011-11-28 01:34:23Z zhouxiaobo $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

Cloud::loadFile('Service_Client_Restful');

class Cloud_Service_Client_Connect extends Cloud_Service_Client_Restful {

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

	public function sync($qzoneLikeQQ, $mblogQQ) {

		return $this->_callMethod('connect.sync', array('qzoneLikeQQ' => $qzoneLikeQQ, 'mblogFollowQQ' => $mblogQQ));
	}

}