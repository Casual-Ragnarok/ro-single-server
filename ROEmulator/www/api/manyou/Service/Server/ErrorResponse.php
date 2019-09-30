<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: ErrorResponse.php 25510 2011-11-14 02:22:26Z yexinhao $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class Cloud_Service_Server_ErrorResponse {

	public $errorCode = 0;
	public $errorMessage = '';

	public function __construct($errorCode, $errorMessage) {
		$this->errorCode = $errorCode;
		$this->errorMessage = $errorMessage;
	}

	function getCode() {
		return $this->errorCode;
	}

	function getMessage() {
		return $this->errorMessage;
	}

	function getResult() {
		return null;
	}

}