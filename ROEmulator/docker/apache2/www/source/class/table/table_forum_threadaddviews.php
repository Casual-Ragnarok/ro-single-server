<?php
/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: table_forum_threadaddviews.php 27449 2012-02-01 05:32:35Z zhangguosheng $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}
class table_forum_threadaddviews extends discuz_table {

	public function __construct() {
		$this->_table = 'forum_threadaddviews';
		$this->_pk    = 'tid';
		parent::__construct();
	}

	public function update_by_tid($tid) {
		return DB::query('UPDATE %t SET `addviews`=`addviews`+1 WHERE tid=%d', array($this->_table, $tid));
	}
	public function fetch_all_order_by_tid($start = 0, $limit = 0) {
		return DB::fetch_all('SELECT * FROM %t ORDER BY tid'.DB::limit($start, $limit), array($this->_table), $this->_pk);
	}
}

?>