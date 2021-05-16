<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: table_security_evilpost.php 29265 2012-03-31 06:03:26Z yexinhao $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class table_security_evilpost extends discuz_table {

	public function __construct() {
		$this->_table = 'security_evilpost';
		$this->_pk = 'pid';

		parent::__construct();
	}

	public function fetch_all_report($limit = 20) {
		return DB::fetch_all("SELECT * FROM %t WHERE isreported = 0 AND operateresult > 0 LIMIT %d", array($this->_table, $limit));
	}

	public function range_by_operateresult($operateresult, $start, $limit) {

		return DB::fetch_all('SELECT * FROM %t WHERE ' . DB::field('operateresult', $operateresult) . ' ' . DB::limit($start, $limit), array($this->_table), $this->_pk);
	}

	public function count_by_type($type) {
		$type = $type ? 1 : 0;
		$count = (int) DB::result_first("SELECT count(*) FROM %t WHERE type = %d", array($this->_table, $type));
		return $count;
	}

	public function fetch_range_by_type($type, $start, $perPage = '20', $orderBy = 'createtime') {
		$type = $type ? 1 : 0;
		$orderSql = " ORDER BY $orderBy DESC ";
		$limitSql = DB::limit($start, $perPage);
		$return = DB::fetch_all("SELECT * FROM %t WHERE type = %d %i %i", array($this->_table, $type, $orderSql, $limitSql));
		return $return;
	}

	public function update_result($operateResult, $pids) {
		$operateResult = $operateResult ? 1 : 0;
		if (!isset($pids)) {
			return false;
		}

		$updateData = array('operateresult' => $operateResult);
		$updateCondition = DB::field('pid', $pids);
		return DB::update($this->_table, $updateData, $updateCondition);
	}

	public function update_reported($isReported, $pids) {
		$isReported = $isReported ? 1 : 0;
		if (!isset($pids)) {
			return false;
		}

		$updateData = array('isreported' => $isReported);
		$updateCondition = DB::field('pid', $pids);
		return DB::update($this->_table, $updateData, $updateCondition);
	}

	public function update_by_pid_type($data, $pid, $type) {
		if(!empty($data) && is_array($data)) {
			return DB::update($this->_table, $data, array(DB::field($this->_pk, $pid), DB::field('type', $type)));
		}
		return 0;
	}

	public function update_by_tid($ids, $data) {
		if(!empty($data) && is_array($data)) {
			return DB::update($this->_table, $data, DB::field('tid', $ids));
		}
		return 0;
	}

	public function count_by_recyclebine($fid = 0, $isgroup = 0, $author = array(), $username = array(), $pstarttime = 0, $pendtime = 0, $mstarttime = 0, $mendtime = 0, $keywords = '') {
		$sql = $this->recyclebine_where($fid, $isgroup, $author, $username, $pstarttime, $pendtime, $mstarttime, $mendtime, $keywords);
		return DB::result_first('SELECT COUNT(*) FROM '.DB::table('forum_thread').' t INNER JOIN '.DB::table('security_evilpost').' s ON s.tid = t.tid LEFT JOIN '.DB::table('forum_threadmod').' tm ON tm.tid=t.tid '.$sql[0], $sql[1]);
	}

	private function recyclebine_where($fid = 0, $isgroup = 0, $authors = array(), $username = array(), $pstarttime = 0, $pendtime = 0, $mstarttime = 0, $mendtime = 0, $keywords = '') {
		$parameter = array();
		$wherearr = array('t.displayorder=-1', 'tm.action=\'DEL\'', 's.type=1');
		if($fid) {
			$parameter[] = $fid;
			$wherearr[] = is_array($fid) ? 't.fid IN(%n)' : 't.fid=%d';
		}
		if($isgroup) {
			$wherearr[] = 't.isgroup=1';
		}
		if(!empty($authors)) {
			$parameter[] = $authors;
			$wherearr[] = is_array($authors) ? 't.author IN(%n)' : 't.author=%s';
		}
		if(!empty($username)) {
			$parameter[] = $username;
			$wherearr[] = is_array($username) ? 'tm.username IN(%n)' : 'tm.username=%s';
		}
		if($pstarttime) {
			$parameter[] = $pstarttime;
			$wherearr[] = 't.dateline>=%d';
		}
		if($pendtime) {
			$parameter[] = $pendtime;
			$wherearr[] = 't.dateline<%d';
		}
		if($mstarttime) {
			$parameter[] = $mstarttime;
			$wherearr[] = 'tm.dateline>=%d';
		}
		if($mendtime) {
			$parameter[] = $mendtime;
			$wherearr[] = 'tm.dateline<%d';
		}
		if($keywords) {
			$keysql = array();
			foreach(explode(',', str_replace(' ', '', $keywords)) as $keyword) {
				$parameter[] = '%'.$keywords.'%';
				$keysql[] = "t.subject LIKE %s";
			}
			if($keysql) {
				$wherearr[] = '('.implode(' OR ', $keysql).')';
			}
		}
		$wheresql = !empty($wherearr) && is_array($wherearr) ? ' WHERE '.implode(' AND ', $wherearr) : '';
		return array($wheresql, $parameter);
	}

	public function fetch_all_by_recyclebine($fid = 0, $isgroup = 0, $author = array(), $username = array(), $pstarttime = 0, $pendtime = 0, $mstarttime = 0, $mendtime = 0, $keywords = '', $start = 0, $limit = 0) {
		$sql = $this->recyclebine_where($fid, $isgroup, $author, $username, $pstarttime, $pendtime, $mstarttime, $mendtime, $keywords);
		return DB::fetch_all('SELECT f.name AS forumname, f.allowsmilies, f.allowhtml, f.allowbbcode, f.allowimgcode,
				t.tid, t.fid, t.authorid, t.author, t.subject, t.views, t.replies, t.dateline, t.posttableid,
				tm.uid AS moduid, tm.username AS modusername, tm.dateline AS moddateline, tm.action AS modaction, tm.reason
				FROM '.DB::table('forum_thread').' t INNER JOIN '.DB::table('security_evilpost').' s ON s.tid = t.tid LEFT JOIN '.DB::table('forum_threadmod').' tm ON tm.tid=t.tid
				LEFT JOIN '.DB::table('forum_forum').' f ON f.fid=t.fid '.$sql[0].' ORDER BY t.dateline DESC '.DB::limit($start, $limit), $sql[1]);
	}

	public function count_by_search($tableid, $tid = null, $keywords = null, $invisible =null, $fid = null, $authorid = null, $author = null, $starttime = null, $endtime = null, $useip = null, $first = null) {
		$sql = '';
		$sql .= $tid ? ' AND '.DB::field('tid', $invisible) : '';
		$sql .= $invisible !== null ? ' AND '.DB::field('invisible', $invisible) : '';
		$sql .= $first !== null ? ' AND '.DB::field('first', $first) : '';
		$sql .= $fid ? ' AND '.DB::field('fid', $fid) : '';
		$sql .= $authorid !== null ? ' AND '.DB::field('authorid', $authorid) : '';
		$sql .= $author ? ' AND '.DB::field('author', $author) : '';
		$sql .= $starttime ? ' AND '.DB::field('dateline', $starttime, '>=') : '';
		$sql .= $endtime ? ' AND '.DB::field('dateline', $endtime, '<') : '';
		$sql .= $useip ? ' AND '.DB::field('useip', $useip, 'like') : '';
		$sql .= ' AND s.type = 0';
		if(trim($keywords)) {
			$sqlkeywords = $or = '';
			foreach(explode(',', str_replace(' ', '', $keywords)) as $keyword) {
				$sqlkeywords .= " $or message LIKE '%$keyword%'";
				$or = 'OR';
			}
			$sql .= " AND ($sqlkeywords)";
		}
		if($sql) {
			return DB::result_first('SELECT COUNT(*) FROM %t t INNER JOIN '.DB::table('security_evilpost').' s ON s.pid = t.pid WHERE 1 %i', array(C::t('forum_post')->get_tablename($tableid), $sql));
		} else {
			return 0;
		}
	}

	public function fetch_all_by_search($tableid, $tid = null, $keywords = null, $invisible = null, $fid = null, $authorid = null, $author = null, $starttime = null, $endtime = null, $useip = null, $first = null, $start = null, $limit = null) {
		$sql = '';
		$sql .= $tid ? ' AND '.DB::field('tid', $tid) : '';
		$sql .= $invisible !== null ? ' AND '.DB::field('invisible', $invisible) : '';
		$sql .= $first !== null ? ' AND '.DB::field('first', $first) : '';
		$sql .= $fid ? ' AND '.DB::field('fid', $fid) : '';
		$sql .= $authorid !== null ? ' AND '.DB::field('authorid', $authorid) : '';
		$sql .= $author ? ' AND '.DB::field('author', $author) : '';
		$sql .= $starttime ? ' AND '.DB::field('dateline', $starttime, '>=') : '';
		$sql .= $endtime ? ' AND '.DB::field('dateline', $endtime, '<') : '';
		$sql .= $useip ? ' AND '.DB::field('useip', $useip, 'like') : '';
		$sql .= ' AND s.type = 0';
		if(trim($keywords)) {
			$sqlkeywords = $or = '';
			foreach(explode(',', str_replace(' ', '', $keywords)) as $keyword) {
				$sqlkeywords .= " $or message LIKE '%$keyword%'";
				$or = 'OR';
			}
			$sql .= " AND ($sqlkeywords)";
		}
		if($sql) {
			return DB::fetch_all('SELECT * FROM %t t INNER JOIN '.DB::table('security_evilpost').' s ON s.pid = t.pid WHERE 1 %i ORDER BY dateline DESC %i', array(C::t('forum_post')->get_tablename($tableid), $sql, DB::limit($start, $limit)));
		} else {
			return array();
		}
	}
}