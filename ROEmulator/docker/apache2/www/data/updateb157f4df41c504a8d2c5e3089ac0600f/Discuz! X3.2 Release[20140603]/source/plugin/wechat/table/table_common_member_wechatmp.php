<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: table_common_member_wechatmp.php 34506 2014-05-13 02:09:15Z nemohou $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class table_common_member_wechatmp extends discuz_table {

	public function __construct() {
		$this->_table = 'common_member_wechatmp';
		$this->_pk = 'uid';
		$this->_pre_cache_key = 'common_member_wechatmp_';

		parent::__construct();
	}

}