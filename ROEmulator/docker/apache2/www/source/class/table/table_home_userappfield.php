<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: table_home_userappfield.php 27449 2012-02-01 05:32:35Z zhangguosheng $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class table_home_userappfield extends discuz_table
{
	public function __construct() {

		$this->_table = 'home_userappfield';
		$this->_pk    = '';

		parent::__construct();
	}

	public function delete_by_uid_appid($uid = 0, $appid = 0) {
		$parameter = array($this->_table);
		$wherearr = array();
		if($uid) {
			$parameter[] = $uid;
			$wherearr[] = is_array($uid) ? 'uid IN(%n)' : 'uid=%d';
		}
		if($appid) {
			$parameter[] = $appid;
			$wherearr[] = is_array($appid) ? 'appid IN(%n)' : 'appid=%d';
		}
		$wheresql = !empty($wherearr) && is_array($wherearr) ? ' WHERE '.implode(' AND ', $wherearr) : '';

		return DB::query("DELETE FROM %t $wheresql", $parameter);
	}

	public function delete_by_uid($uids) {
		return DB::delete($this->_table, DB::field('uid', $uids));
	}

}

?>