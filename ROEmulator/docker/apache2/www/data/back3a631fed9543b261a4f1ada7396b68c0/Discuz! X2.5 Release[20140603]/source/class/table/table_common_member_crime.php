<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: table_common_member_crime.php 28893 2012-03-19 02:14:36Z svn_project_zhangjie $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class table_common_member_crime extends discuz_table
{
	public function __construct() {

		$this->_table = 'common_member_crime';
		$this->_pk    = 'cid';

		parent::__construct();
	}

	public function fetch_all_by_uid($uid) {
		return $uid ? DB::fetch_all('SELECT * FROM %t WHERE uid=%d ORDER BY dateline DESC', array($this->_table, $uid), $this->_pk) : array();
	}

	public function count_by_uid_action($uid, $action) {
		return $uid ? DB::result_first('SELECT COUNT(*) FROM %t WHERE uid=%d AND action=%d', array($this->_table, $uid, $action)) : 0;
	}

	public function count_by_where($where) {
		return $where ? DB::result_first('SELECT COUNT(*) FROM %t %i ', array($this->_table, $where)) : $this->count();
	}

	public function fetch_all_by_where($where, $start = 0, $limit = 0) {
		return DB::fetch_all('SELECT * FROM %t %i ORDER BY dateline DESC '.DB::limit($start, $limit), array($this->_table, $where));
	}
}

?>