<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: table_home_notification.php 28262 2012-02-27 02:29:28Z liulanbo $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class table_home_notification extends discuz_table
{
	public function __construct() {

		$this->_table = 'home_notification';
		$this->_pk    = 'id';

		parent::__construct();
	}

	public function delete_clear($new, $days) {
		$days = TIMESTAMP - intval($days) * 86400;
		DB::query("DELETE FROM %t WHERE new=%d AND dateline<%d", array($this->_table, $new, $days));
	}
	public function delete_by_type($type) {
		if(!$type) {
			return;
		}
		return DB::delete($this->_table, DB::field('type', $type));
	}

	public function optimize() {
		DB::query("OPTIMIZE TABLE %t", array($this->_table), true);
	}

	public function fetch_by_fromid_uid($id, $idtype, $uid) {
		return DB::fetch_first("SELECT * FROM %t WHERE from_id=%d AND from_idtype=%s AND uid=%d", array($this->_table, $id, $idtype, $uid));
	}

	public function delete_by_id_uid($id, $uid) {
		DB::query("DELETE FROM %t WHERE id=%d AND uid=%d", array($this->_table, $id, $uid));
	}

	public function delete_by_uid($uid) {
		DB::query("DELETE FROM %t WHERE uid IN (%n) OR authorid IN (%n)", array($this->_table, $uid, $uid));
	}

	public function fetch_all_by_authorid_fromid($authorid, $fromid, $type) {
		return DB::fetch_all("SELECT * FROM %t WHERE authorid=%d AND from_id=%d AND type=%s", array($this->_table, $authorid, $fromid, $type));
	}

	public function ignore($uid, $new = true, $from_num = true) {
		$uid = intval($uid);
		$update = array();
		if($new) {
			$update['new'] = 0;
		}
		if($from_num) {
			$update['from_num'] = 0;
		}
		if($update) {
			DB::update($this->_table, $update, array('uid' => $uid, 'new' => 1));
		}
	}

	public function count_by_uid($uid, $new, $type = '') {
		$new = intval($new);
		$type = $type ? ' AND '.DB::field('type', $type) : '';
		$new = ' AND '.DB::field('new', $new);
		return DB::result_first("SELECT COUNT(*) FROM %t WHERE uid=%d %i %i", array($this->_table, $uid, $type, $new));
	}

	public function fetch_all_by_uid($uid, $new, $type, $start, $perpage) {
		$new = intval($new);
		$type = $type ? ' AND '.DB::field('type', $type) : '';
		$new = ' AND '.DB::field('new', $new);
		return DB::fetch_all("SELECT * FROM %t WHERE uid=%d %i %i ORDER BY new DESC, dateline DESC %i", array($this->_table, $uid, $type, $new, DB::limit($start, $perpage)));
	}
}

?>