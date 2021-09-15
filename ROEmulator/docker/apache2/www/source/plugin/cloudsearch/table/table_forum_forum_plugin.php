<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: table_forum_forum_plugin.php 29366 2012-04-09 03:00:26Z zhouxiaobo $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class table_forum_forum_plugin extends discuz_table {

	public function __construct() {

		$this->_table = 'forum_forum';
		$this->_pk    = 'fid';

		parent::__construct();
	}

	public function fetch_all_forum_by_formula_for_plugin() {
		$statusql = 'f.status<>\'3\'';
		return DB::fetch_all("SELECT f.*, ff.*, a.uid FROM ".DB::table($this->_table)." f LEFT JOIN ".DB::table('forum_forumfield')." ff ON ff.fid=f.fid LEFT JOIN ".DB::table('forum_access')." a ON a.fid=f.fid AND a.allowview>'0' WHERE $statusql AND formulaperm<>'' ORDER BY f.type, f.displayorder");
	}
}