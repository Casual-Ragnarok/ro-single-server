<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: Response.php 25510 2011-11-14 02:22:26Z yexinhao $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class Cloud_Service_Server_Response {

	public $result;
	public $mode;

	public function __construct($res, $mode = null) {
		$this->result = $res;
		$this->mode = $mode;
	}

	public function getResult() {
		return $this->result;
	}

	public function getMode() {
		return $this->mode;
	}

}