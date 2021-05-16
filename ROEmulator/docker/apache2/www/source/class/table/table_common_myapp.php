<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: table_common_myapp.php 27906 2012-02-16 08:15:08Z zhengqingpeng $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class table_common_myapp extends discuz_table
{
	public function __construct() {

		$this->_table = 'common_myapp';
		$this->_pk    = 'appid';

		parent::__construct();
	}
	public function fetch_all_by_flag($flag, $glue = '=', $sort = 'ASC') {
		$glue = helper_util::check_glue($glue);
		return DB::fetch_all("SELECT * FROM %t WHERE flag{$glue}%d ORDER BY ".DB::order('displayorder', $sort), array($this->_table, $flag), $this->_pk);
	}

}

?>