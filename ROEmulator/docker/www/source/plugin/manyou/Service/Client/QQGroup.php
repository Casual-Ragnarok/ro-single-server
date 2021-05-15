<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: QQGroup.php 26146 2011-12-03 08:53:31Z yexinhao $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

Cloud::loadFile('Service_Client_Restful');

class Cloud_Service_Client_QQGroup extends Cloud_Service_Client_Restful {

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

	public function miniportal($topic, $normal, $gIds = array()) {

		return $this->_callMethod('qqgroup.miniportal', array('topic' => $topic, 'normal' => $normal, 'gIds' => $gIds));
	}

	public function feed($thread, $gIds = array()) {

		return $this->_callMethod('qqgroup.feed', array('thread' => $thread, 'gIds' => $gIds));
	}

}