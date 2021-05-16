<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: table_security_failedlog.php 28862 2012-03-15 08:30:54Z songlixin $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class table_security_failedlog extends discuz_table {

	public function __construct() {
		$this->_table = 'security_failedlog';
		$this->_pk = 'id';

		parent::__construct();
	}

	public function deleteDirtyLog() {
		DB::delete($this->_table, 'pid = 0 AND tid = 0 AND uid = 0 OR lastfailtime = 0 OR failcount >= 10');
		return true;
	}

	public function fetch_by_pid($pid) {

		return DB::fetch_first('SELECT * FROM %t WHERE ' . DB::field('pid', $pid) . ' ' . DB::limit(0, 1), array($this->_table), $this->_pk);
	}

	public function fetch_by_uid($uid) {

		return DB::fetch_first('SELECT * FROM %t WHERE ' . DB::field('uid', $uid) . ' ' . DB::limit(0, 1), array($this->_table), $this->_pk);
	}

	public function range_by_pid($pid, $start = 0, $limit = 5) {

		return DB::fetch_all('SELECT * FROM %t WHERE ' . DB::field('pid', $pid) . ' ' . DB::limit($start, $limit), array($this->_table), $this->_pk);
	}

	public function range_by_uid($uid, $start = 0, $limit = 5) {

		return DB::fetch_all('SELECT * FROM %t WHERE ' . DB::field('uid', $uid) . ' ' . DB::limit($start, $limit), array($this->_table), $this->_pk);
	}

}