<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: table_mobile_wechat_masssend.php 35024 2014-10-14 07:43:43Z nemohou $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class table_mobile_wechat_masssend extends discuz_table {

	public function __construct() {
		$this->_table = 'mobile_wechat_masssend';
		$this->_pk = 'id';

		parent::__construct();
	}

}