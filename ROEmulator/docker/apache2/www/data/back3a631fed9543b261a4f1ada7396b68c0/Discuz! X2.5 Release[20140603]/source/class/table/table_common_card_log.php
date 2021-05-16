<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: table_common_card_log.php 27449 2012-02-01 05:32:35Z zhangguosheng $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class table_common_card_log extends discuz_table
{
	public function __construct() {

		$this->_table = 'common_card_log';
		$this->_pk    = 'id';

		parent::__construct();
	}

	public function fetch_by_operation($operation) {
		return DB::fetch_first('SELECT * FROM %t WHERE operation=%d ORDER BY dateline DESC LIMIT 1', array($this->_table, $operation));
	}

	public function fetch_all_by_operation($operation, $start = 0, $limit = 0) {
		return DB::fetch_all('SELECT * FROM %t WHERE operation=%d ORDER BY dateline DESC '.DB::limit($start, $limit), array($this->_table, $operation));
	}

	public function count_by_operation($operation) {
		return DB::result_first('SELECT COUNT(*) FROM %t WHERE operation=%d', array($this->_table, $operation));
	}
}

?>