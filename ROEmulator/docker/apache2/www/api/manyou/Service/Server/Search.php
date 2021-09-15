<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: Search.php 32355 2013-01-06 03:11:36Z chenmengshu $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

Cloud::loadFile('Service_SearchHelper');
class Cloud_Service_Server_Search extends Cloud_Service_Server_Restful {

	protected static $_instance;

	public static function getInstance() {

		if (!(self::$_instance instanceof self)) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	public function onSearchGetUserGroupPermissions($userGroupIds) {
		if (!$userGroupIds) {
			return array();
		}
		$result = Cloud_Service_SearchHelper::getUserGroupPermissions($userGroupIds);
		return $result;
	}

	public function onSearchGetUpdatedPosts($num, $lastPostIds = array()) {

		if ($lastPostIds) {
			C::t('forum_postlog')->delete_by_pid($lastPostIds);
		}
		$result = array();
		$totalNum = C::t('forum_postlog')->count();
		if (!$totalNum) {
			return $result;
		}
		$result['totalNum'] = $totalNum;
		$pIds = $deletePosts = $updatePostIds = array();
		$unDeletePosts = array();
		$posts = array();
		foreach(C::t('forum_postlog')->fetch_all_order_by_dateline($num) as $post) {
			$pIds[] = $post['pid'];
			if ($post['action'] == 'delete') {
				$deletePosts[$post['pid']] = array(
						'pId' => $post['pid'],
						'action' => $post['action'],
						'updated' => dgmdate($post['dateline'], 'Y-m-d H:i:s', 8),
					);
			} else {
				$unDeletePosts[$post['pid']] = array(
						'pId' => $post['pid'],
						'action' => $post['action'],
						'updated' => dgmdate($post['dateline'], 'Y-m-d H:i:s', 8),
					);
			}
		}
		if($pIds) {
			if($unDeletePosts) {
				$gfIds = array(); // groupForumIds
				$posts = $this->_getPosts(array_keys($unDeletePosts));
				foreach($unDeletePosts as $pId => $updatePost) {
					if($posts[$pId]) {
						$unDeletePosts[$pId] = array_merge($updatePost, $posts[$pId]);
					} else {
						$unDeletePosts[$pId]['pId'] = 0;
					}
					if($posts[$pId]['isGroup']) {
						$gfIds[$posts[$pId]['fId']] = $posts[$pId]['fId'];
					}
				}
			}
		}
		$result['data'] = $deletePosts + $unDeletePosts;
		$result['ids']['post'] = $pIds;
		return $result;
	}

	public function onSearchRemovePostLogs($pIds) {
		if (!$pIds) {
			return false;
		}
		C::t('forum_postlog')->delete_by_pid($pIds);
		return true;
	}

	protected function _preGetPosts($tableid, $pIds) {
		$result = array();
		foreach(C::t('forum_post')->fetch_all_by_pid($tableid, $pIds) as $post) {
			$result[$post['pid']] = Cloud_Service_SearchHelper::convertPost($post);
		}
		return $result;
	}

	protected function _getPosts($pIds) {
		$posts = array();
		foreach(Cloud_Service_SearchHelper::getTables('post') as $tableid) {
			$_posts = $this->_preGetPosts($tableid, $pIds);
			if ($_posts) {
				if (!$posts) {
					$posts = $_posts;
				} else {
					$posts = $posts + $_posts;
				}
				if (count($posts) == count($pIds)) {
					break;
				}
			}
		}

		if ($posts) {
			foreach($posts as $pId => $post) {
				$tIds[$post['pId']] = $post['tId'];
			}

			if ($tIds) {
				$gfIds = $vtIds = $stIds = array(); // poll
				$threads = Cloud_Service_SearchHelper::getThreads($tIds);
				foreach($posts as $pId => $post) {
					$tId = $tIds[$pId];
					$posts[$pId]['isGroup'] = $threads[$tId]['isGroup'];
					if ($threads[$tId]['isGroup']) {
						$gfIds[$threads[$tId]['fId']] = $threads[$tId]['fId'];
					}
					if ($post['isThread']) {
						$stIds[$pId] = $tId;
						$posts[$pId]['threadInfo'] = $threads[$tId];
					}
					if ($threads[$tId]['specialType'] == 'poll') {
						$vtIds[$pId] = $tId;
					}
				}
				if($stIds) {
					$sorts = Cloud_Service_SearchHelper::getThreadSort($stIds);
					foreach($stIds as $pId => $tId) {
						$posts[$pId]['category'] = $sorts[$tId];
					}
				}
				if ($vtIds) {
					$polls = Cloud_Service_SearchHelper::getPollInfo($vtIds);
					foreach($vtIds as $pId => $tId) {
						$posts[$pId]['threadInfo']['pollInfo'] = $polls[$tId];
					}
				}
				$guestPerm = Cloud_Service_SearchHelper::getGuestPerm($gfIds);
				foreach($posts as $pId => $post) {
					if (in_array($post['fId'], $guestPerm['allowForumIds'])) {
						$posts[$pId]['isPublic'] = true;
					} else {
						$posts[$pId]['isPublic'] = false;
					}
					if ($post['isThread']) {
						$posts[$pId]['threadInfo']['isPublic'] = $posts[$pId]['isPublic'];
					}
				}
			}
		}

		return $posts;
	}

	public function onSearchGetPosts($pIds) {
		$authors = array();
		$posts = $this->_getPosts($pIds);
		if ($posts) {
			foreach($posts as $post) {
				$authors[$post['authorId']][] = $post['pId'];
			}

			$authorids = array_keys($authors);
			if ($authorids) {
				$banuids= $uids = array();
				foreach(C::t('common_member')->fetch_all($authorids) as $uid => $author) {
					$uids[$uid] = $uid;
					if ($author['groupid'] == 4 || $author['groupid'] == 5) {
						$banuids[] = $author['uid'];
					}
				}

				$deluids = array_diff($authorids, $uids);
				foreach($deluids as $deluid) {
					if (!$deluid) {
						continue;
					}
					foreach($authors[$deluid] as $pid) {
						$posts[$pid]['authorStatus'] = 'delete';
					}
				}
				foreach($banuids as $banuid) {
					foreach($authors[$banuid] as $pid) {
						$posts[$pid]['authorStatus'] = 'ban';
					}
				}
			}
		}
		return $posts;
	}

	protected function _getNewPosts($tableid, $num, $fromPostId = 0) {

		$result = array();
		if (dintval($num)) {
			foreach(C::t('forum_post')->fetch_all_new_post_by_pid($fromPostId, '', $num, $tableid) as $post) {
				$result['maxPid'] = $post['pid'];
				$result['data'][$post['pid']] = Cloud_Service_SearchHelper::convertPost($post);
			}
		}

		return $result;
	}

	public function onSearchGetNewPosts($num, $fromPostId = 0) {
		$res = $data = array();
		$maxPid = 0;
		foreach(Cloud_Service_SearchHelper::getTables('post') as $tableid) {
			$_posts = $this->_getNewPosts($tableid, $num, $fromPostId);
			if ($_posts['data']) {
				if (!$data) {
					$data = $_posts['data'];
				} else {
					$data = $data + $_posts['data'];
				}
			}
			if ($maxPid < $_posts['maxPid']) {
				$maxPid = $_posts['maxPid'];
			}
		}

		$_postNum = 0;
		if ($maxPid) {
			ksort($data);
			foreach($data as $k => $v) {
				$_postNum++;
				$res['data'][$k] = $v;
				$res['maxPid'] = $k;
				if ($_postNum == $num) {
					break;
				}
			}
			if (!$res['maxPid']) {
				$res['maxPid'] = $maxPid;
			}
		}

		if ($res['data']) {
			$tIds = $autors = array();
			foreach($res['data'] as $pId => $post) {
				$authors[$post['authorId']][] = $post['pId'];
				$tIds[$pId] = $post['tId'];
			}

			if ($tIds) {
				$threads = Cloud_Service_SearchHelper::getThreads($tIds);
				$stIds = array();
				foreach ($tIds as $pId => $tId) {
					$res['data'][$pId]['isGroup'] = $threads[$tId]['isGroup'];
					if ($res['data'][$pId]['isThread']) {
						$stIds[$pId] = $tId;
						$res['data'][$pId]['threadInfo'] = $threads[$tId];
					}
				}
				if($stIds) {
					$sorts = Cloud_Service_SearchHelper::getThreadSort($stIds);
					foreach($stIds as $pId => $tId) {
						$res['data'][$pId]['category'] = $sorts[$tId];
					}
				}
			}

			$authorids = array_keys($authors);
			if ($authorids) {
				$banuids= $uids = array();
				foreach(C::t('common_member')->fetch_all($authorids) as $uid => $author) {
					$uids[$uid] = $uid;
					if ($author['groupid'] == 4 || $author['groupid'] == 5) {
						$banuids[] = $author['uid'];
					}
				}
				$deluids = array_diff($authorids, $uids);
				foreach($deluids as $deluid) {
					if (!$deluid) {
						continue;
					}
					foreach($authors[$deluid] as $pid) {
						$res['data'][$pid]['authorStatus'] = 'delete';
					}
				}
				foreach($banuids as $banuid) {
					foreach($authors[$banuid] as $pid) {
						$res['data'][$pid]['authorStatus'] = 'ban';
					}
				}
			}
		}

		return $res;
	}

	public function onSearchGetAllPosts($num, $pId = 0, $orderType = 'ASC') {
		$res = $data = $_tableInfo = array();
		$maxPid = $minPid = 0;
		$orderType = strtoupper($orderType);
		foreach(Cloud_Service_SearchHelper::getTables('post') as $tableid) {
			$_posts = $this->_getAllPosts($tableid, $num, $pId, $orderType);
			if ($_posts['data']) {
				if (!$data) {
					$data = $_posts['data'];
				} else {
					$data = $data + $_posts['data'];
				}
			}
			if ($orderType == 'DESC') {
				if (!$minPid) {
					$minPid = $_posts['minPid'];
				}
				if ($minPid > $_posts['minPid']) {
					$minPid = $_posts['minPid'];
				}
				$_tableInfo['minPids'][] = array('current_index' => $i,
												 'minPid' => $_posts['minPid'],
												);
			} else {
				if ($maxPid < $_posts['maxPid']) {
					$maxPid = $_posts['maxPid'];
				}
				$_tableInfo['maxPids'][] = array('current_index' => $i,
												 'maxPid' => $_posts['maxPid'],
												);
			}
		}
		$_postNum = 0;
		if ($orderType == 'DESC') {
			if ($minPid) {
				krsort($data);
				foreach($data as $k => $v) {
					$_postNum++;
					$res['minPid'] = $k;
					$res['data'][$k] = $v;
					if ($_postNum == $num) {
						break;
					}
				}
				if (!$res['minPid']) {
					$res['minPid'] = $minPid;
				}
			}
		} else {
			if ($maxPid) {
				ksort($data);
				foreach($data as $k => $v) {
					$_postNum++;
					$res['data'][$k] = $v;
					$res['maxPid'] = $k;
					if ($_postNum == $num) {
						break;
					}
				}
				if (!$res['maxPid']) {
					$res['maxPid'] = $maxPid;
				}
			}
		}

		if ($res['data']) {
			$_tableInfo['tables'] = $tables;

			$tIds = $authors = $forums = array();
			foreach($res['data'] as $pId => $post) {
				$authors[$post['authorId']][] = $post['pId'];
				$tIds[$post['pId']] = $post['tId'];
			}

			if ($tIds) {
				$vtIds = $gfIds = $stIds = array();
				$threads = Cloud_Service_SearchHelper::getThreads($tIds);
				foreach($tIds as $_pId => $tId) {
					$res['data'][$_pId]['isGroup'] = $threads[$tId]['isGroup'];
					$myPost = $res['data'][$_pId];

					if ($myPost['isGroup']) {
						$gfIds[$myPost['fId']] = $myPost['fId'];
					}

					if ($myPost['isThread']) {
						$stIds[$_pId] = $tId;
						$res['data'][$_pId]['threadInfo'] = $threads[$tId];
						if ($threads[$tId]['specialType'] == 'poll') {
							$vtIds[$_pId] = $tId;
						}
					}
				}

				if($stIds) {
					$sorts = Cloud_Service_SearchHelper::getThreadSort($stIds);
					foreach($stIds as $pId => $tId) {
						$res['data'][$pId]['category'] = $sorts[$tId];
					}
				}

				if ($vtIds) {
					$polls = Cloud_Service_SearchHelper::getPollInfo($vtIds);
					foreach($vtIds as $pId => $tId) {
						$res['data'][$pId]['threadInfo']['pollInfo'] = $polls[$tId];
					}
				}

				$guestPerm = Cloud_Service_SearchHelper::getGuestPerm($gfIds);
				foreach($res['data'] as $key => $row) {
					if (in_array($row['fId'], $guestPerm['allowForumIds'])) {
						$res['data'][$key]['isPublic'] = true;
					} else {
						$res['data'][$key]['isPublic'] = false;
					}
					if ($row['isThread']) {
						$res['data'][$key]['threadInfo']['isPublic'] = $res['data'][$key]['isPublic'];
					}
				}
			}

			$authorids = array_keys($authors);
			if ($authorids) {
				$banuids= $uids = array();
				foreach(C::t('common_member')->fetch_all($authorids) as $uid => $author) {
					$uids[$uid] = $uid;
					if ($author['groupid'] == 4 || $author['groupid'] == 5) {
						$banuids[] = $author['uid'];
					}
				}
				$deluids = array_diff($authorids, $uids);
				foreach($deluids as $deluid) {
					if (!$deluid) {
						continue;
					}
					foreach($authors[$deluid] as $pid) {
						$res['data'][$pid]['authorStatus'] = 'delete';
					}
				}
				foreach($banuids as $banuid) {
					foreach($authors[$banuid] as $pid) {
						$res['data'][$pid]['authorStatus'] = 'ban';
					}
				}
			}

		}
		return $res;
	}

	protected function _getAllPosts($tableid, $num, $pId = 0, $orderType = 'ASC') {
		$result = array();
		if (dintval($num)) {
			if (strtoupper($orderType) == 'DESC') {
				$glue = '<';
				$key = 'minPid';
			} else {
				$orderType = 'ASC';
				$glue = '>';
				$key = 'maxPid';
			}
			$tIds = $authors = array();
			foreach(C::t('forum_post')->fetch_all_new_post_by_pid($pId, 0, $num, $tableid, $glue, $orderType) as $post) {
				$result[$key] = $post['pid'];
				$result['data'][$post['pid']] = Cloud_Service_SearchHelper::convertPost($post);
			}
		}

		return $result;
	}

	protected function _removeThreads($tIds, $isRecycle = false) {
		$tableThreads = array();
		foreach(Cloud_Service_SearchHelper::getTables('thread') as $tableid) {
			$_threads = Cloud_Service_SearchHelper::preGetThreads($tableid, $tIds);
			$tableThreads[$tableid] = $_threads;
		}

		foreach($tableThreads as $tableid => $threads) {
			$_tids = $_threadIds = array();
			foreach($threads as $thread) {
				$_tids[] = $thread['tId'];
				$postTable = $thread['postTableId'] ? $thread['postTableId'] : 0;
				$_threadIds[$postTable][] = $thread['tId'];
			}

			if($_tids) {
				if($isRecycle) {
					C::t('forum_thread')->update($_tids, array('displayorder' => -1), false, false, $tableid);
					continue;
				}

				C::t('forum_thread')->delete_by_tid($_tids, false, $tableid);
				foreach($_threadIds as $postTable => $_tIds) {
					if ($_tIds) {
						C::t('forum_post')->delete_by_tid($postTable, $_tIds);
					}
				}
			}
		}
		return true;
	}


	public function onSearchRecyclePosts($pIds) {
		$posts = array();
		foreach(Cloud_Service_SearchHelper::getTables('post') as $tableid) {
			$_posts = $this->_preGetPosts($tableid, $pIds);
			$posts[$tableid] = $_posts;
		}
		foreach($posts as $id => $rows) {
			$tids = $pids = array();
			foreach($rows as $row) {
				if ($row['isThread']) {
					$tids[] = $row['tId'];
				} else {
					$pids[] = $row['pId'];
				}
			}
			if ($pids) {
				C::t('forum_post')->update($id, $pids, array('invisible' => -1));
			}

			if ($tids) {
				$this->_removeThreads($tids, true);
			}
		}
		return true;
	}

	public function onSearchGetUpdatedThreads($num, $lastThreadIds = array(), $lastForumIds = array(), $lastUserIds = array()) {

		$this->onSearchRemoveThreadLogs($lastThreadIds, $lastForumIds, $lastUserIds);
		$result = array();
		$totalNum = C::t('forum_threadlog')->count();
		if (!$totalNum) {
			return $result;
		}
		$result['totalNum'] = $totalNum;

		$tIds = $deleteThreads = $updateThreadIds = $otherLogs = $ids = array();
		$unDeleteThreads = array();
		$threads = array();
		$otherActions = array('mergeforum', 'banuser', 'unbanuser', 'deluser', 'delforum');
		foreach(C::t('forum_threadlog')->fetch_all_order_by_dateline($num) as $thread) {
			$tIds[] = $thread['tid'];
			if (in_array($thread['action'], array('delete', 'redelete'))) {
				$ids['thread'][] = $thread['tid'];
				$deleteThreads[$thread['tid']] = array('tId' => $thread['tid'],
													   'action' => $thread['action'],
													   'updated' => dgmdate($thread['dateline'], 'Y-m-d H:i:s', 8),
													  );
			} elseif (in_array($thread['action'], array('banuser', 'unbanuser', 'deluser'))) {
				$ids['user'][] = $thread['uid'];
				$expiry = 0;
				if ($thread['expiry']) {
					$expiry = dgmdate($thread['expiry'], 'Y-m-d H:i:s', 8);
				}
				$otherLogs[] = array('uId' => $thread['uid'],
									 'isDeletePost' => $thread['otherid'],
									 'action' => $thread['action'],
									 'expiry' => $expiry,
									 'updated' => dgmdate($thread['dateline'], 'Y-m-d H:i:s', 8),
									);
			} elseif (in_array($thread['action'], array('mergeforum', 'delforum'))) {
				$ids['forum'][] = $thread['fid'];
				$otherLogs[] = array('fId' => $thread['fid'],
									 'otherId' => $thread['otherid'],
									 'action' => $thread['action'],
									 'updated' => dgmdate($thread['dateline'], 'Y-m-d H:i:s', 8),
									);
			} elseif (in_array($thread['action'], array('merge'))) {
				$ids['thread'][] = $thread['tid'];
				$otherLogs[] = array('tId' => $thread['tid'],
									 'fId' => $thread['fId'],
									 'otherId' => $thread['otherid'],
									 'action' => $thread['action'],
									 'updated' => dgmdate($thread['dateline'], 'Y-m-d H:i:s', 8),
									);
			} else {
				$ids['thread'][] = $thread['tid'];
				$unDeleteThreads[$thread['tid']] = array('tId' => $thread['tid'],
									'action'  => $thread['action'],
									'otherId' => $thread['otherid'],
									'updated' => dgmdate($thread['dateline'], 'Y-m-d H:i:s', 8),
									);
			}
		}

		if ($tIds) {
			if ($unDeleteThreads) {
				$vtIds = $gfIds = array(); // poll, isPublic
				$threads = Cloud_Service_SearchHelper::getThreads(array_keys($unDeleteThreads));
				foreach($unDeleteThreads as $tId => $updateThread) {
					$vtIds[] = $tId;
					if ($threads[$tId]) {
						$unDeleteThreads[$tId] = array_merge($threads[$tId], $updateThread);
					} else {
						$unDeleteThreads[$tId]['tId'] = 0;
					}
					if ($threads[$tId]['isGroup']) {
						$gfIds[$threads[$tId]['fId']] = $threads[$tId]['fId'];
					}
				}
				$polls = Cloud_Service_SearchHelper::getPollInfo($vtIds);
				foreach($polls as $tId => $poll) {
					$unDeleteThreads[$tId]['pollInfo'] = $poll;
				}
				$guestPerm = Cloud_Service_SearchHelper::getGuestPerm($gfIds);
				foreach($unDeleteThreads as $tId => $row) {
					if (in_array($row['fId'], $guestPerm['allowForumIds'])) {
						$unDeleteThreads[$tId]['isPublic'] = true;
					} else {
						$unDeleteThreads[$tId]['isPublic'] = false;
					}
				}
			}
		}
		$result['data'] = $deleteThreads + $unDeleteThreads + $otherLogs;
		$result['ids'] = $ids;
		return $result;
	}

	public function onSearchRemoveThreadLogs($lastThreadIds = array(), $lastForumIds = array(), $lastUserIds = array()) {
		if($lastThreadIds) {
			C::t('forum_threadlog')->delete_by_tid_fid_uid($lastThreadIds);
		}
		if($lastForumIds) {
			C::t('forum_threadlog')->delete_by_tid_fid_uid(0, $lastForumIds);
		}
		if($lastUserIds) {
			C::t('forum_threadlog')->delete_by_tid_fid_uid(0, array(), $lastUserIds);
		}
		return true;
	}

	protected function _getThread($tId) {
		$result = Cloud_Service_SearchHelper::getThreads(array($tId));
		return $result[$tId];
	}

	public function onSearchGetThreads($tIds) {
		$authors = $authorids = array();

		$result = Cloud_Service_SearchHelper::getThreads($tIds);
		if ($result) {
			$vtIds = $gfIds = array();
			foreach($result as $key => $thread) {
				$authors[$thread['authorId']][] = $thread['tId'];
				if ($thread['specialType'] == 'poll') {
					$vtIds[] = $thread['tId'];
				}
				if ($thread['isGroup'] ) {
					$gfIds[$thread['fId']] = $thread['fId'];
				}
			}
			$guestPerm = Cloud_Service_SearchHelper::getGuestPerm($gfIds);
			foreach($result as $key => $row) {
				if (in_array($row['fId'], $guestPerm['allowForumIds'])) {
					$result[$key]['isPublic'] = true;
				} else {
					$result[$key]['isPublic'] = false;
				}
			}
		}

		if ($vtIds) { // vote
			$polls = Cloud_Service_SearchHelper::getPollInfo($vtIds);
			foreach($polls as $tId => $poll) {
				$result[$tId]['pollInfo'] = $poll;
			}
		}

		$authorids = array_keys($authors);
		if ($authorids) {
			$banuids= $uids = array();
			foreach(C::t('common_member')->fetch_all($authorids) as $uid => $author) {
				$uids[$uid] = $uid;
				if ($author['groupid'] == 4 || $author['groupid'] == 5) {
					$banuids[] = $author['uid'];
				}
			}
			$deluids = array_diff($authorids, $uids);
			foreach($deluids as $deluid) {
				if (!$deluid) {
					continue;
				}
				foreach($authors[$deluid] as $tid) {
					$result[$tid]['authorStatus'] = 'delete';
				}
			}
			foreach($banuids as $banuid) {
				foreach($authors[$banuid] as $tid) {
					$result[$tid]['authorStatus'] = 'ban';
				}
			}
		}
		return $result;
	}

	protected function _getNewThreads($tableid, $num, $fromThreadId = 0) {
		$result = array();
		foreach(C::t('forum_thread')->fetch_all_new_thread_by_tid($fromThreadId, 0, $num, $tableid) as $thread) {
			$result['maxTid'] = $thread['tid'];
			$result['data'][$thread['tid']] = Cloud_Service_SearchHelper::convertThread($thread);
		}

		return $result;
	}

	public function onSearchGetNewThreads($num, $tId = 0) {
		$res = $data = $_tableInfo = array();
		$maxTid = 0;
		foreach(Cloud_Service_SearchHelper::getTables('thread') as $tableid) {
			$_threads = $this->_getNewThreads($tableid, $num, $tId);
			if ($_threads['data']) {
				if (!$data) {
					$data = $_threads['data'];
				} else {
					$data = $data + $_threads['data'];
				}
			}
			if ($maxTid < $_threads['maxTid']) {
				$maxTid = $_threads['maxTid'];
			}
			$_tableInfo['maxTids'][] = array('current_index' => $i,
											 'maxTid' => $_threads['maxTid'],
											);
		}
		$_threadNum = 0;
		if ($maxTid) {
			ksort($data);
			foreach($data as $k => $v) {
				$_threadNum++;
				$res['maxTid'] = $k;
				$res['data'][$k] = $v;
				if ($_threadNum == $num) {
					break;
				}
			}
			if (!$res['maxTid']) {
				$res['maxTid'] = $maxTid;
			}
		}

		if ($res['data']) {
			$_tableInfo['tables'] = $tables;

			$postThreadIds = $authors = array();
			foreach($res['data'] as $tId => $thread) {
				$authors[$thread['authorId']][] = $thread['tId'];
				$postThreadIds[$thread['postTableId']][] = $thread['tId'];
			}


			$threadPosts = Cloud_Service_SearchHelper::getThreadPosts($postThreadIds);
			foreach($res['data'] as $tId => $v) {
				$res['data'][$tId]['pId'] = $threadPosts[$tId]['pId'];
			}

			$authorids = array_keys($authors);
			if ($authorids) {
				$banuids= $uids = array();
				foreach(C::t('common_member')->fetch_all($authorids) as $uid => $author) {
					$uids[$uid] = $uid;
					if ($author['groupid'] == 4 || $author['groupid'] == 5) {
						$banuids[] = $author['uid'];
					}
				}
				$deluids = array_diff($authorids, $uids);
				foreach($deluids as $deluid) {
					if (!$deluid) {
						continue;
					}
					foreach($authors[$deluid] as $tid) {
						$res['data'][$tid]['authorStatus'] = 'delete';
					}
				}
				foreach($banuids as $banuid) {
					foreach($authors[$banuid] as $tid) {
						$res['data'][$tid]['authorStatus'] = 'ban';
					}
				}
			}
		}
		return $res;
	}

	protected function _getAllThreads($tableid, $num, $tid = 0, $orderType = 'ASC') {

		$result = array();

		$orderType = strtoupper($orderType) == 'DESC' ? 'DESC' : 'ASC';
		$glue = ($orderType == 'DESC') ? '<' : '>';
		$key = ($orderType == 'DESC') ? 'minTid' : 'maxTid';
		$tIds = $vtIds = array();
		foreach(C::t('forum_thread')->fetch_all_new_thread_by_tid($tid, 0, $num, $tableid, $glue, $orderType) as $thread) {
			$result[$key] = $thread['tid'];
			$result['data'][$thread['tid']] = Cloud_Service_SearchHelper::convertThread($thread);
			if ($result['data'][$thread['tid']]['specialType'] == 'poll') {
				$vtIds[] = $thread['tid'];
			}
		}
		if(!empty($vtIds)) {
			$polls = Cloud_Service_SearchHelper::getPollInfo($vtIds);
			foreach($polls as $tId => $poll) {
				$result['data'][$tId]['pollInfo'] = $poll;
			}
		}

		return $result;
	}

	public function onSearchGetAllThreads($num, $tId = 0, $orderType = 'ASC') {
		$orderType = strtoupper($orderType);
		$res = $data = $_tableInfo = array();
		$minTid = $maxTid = 0;
		foreach(Cloud_Service_SearchHelper::getTables('thread') as $tableid) {
			$_threads = $this->_getAllThreads($tableid, $num, $tId, $orderType);
			if ($_threads['data']) {
				if (!$data) {
					$data = $_threads['data'];
				} else {
					$data = $data + $_threads['data'];
				}
			}
			if ($orderType == 'DESC') {
				if (!$minTid) {
					$minTid = $_threads['minTid'];
				}
				if ($minTid > $_threads['minTid']) {
					$minTid = $_threads['minTid'];
				}
				$_tableInfo['minTids'][] = array('current_index' => $i,
												 'minTid' => $_threads['minTid'],
												);
			} else {
				if ($maxTid < $_threads['maxTid']) {
					$maxTid = $_threads['maxTid'];
				}
				$_tableInfo['maxTids'][] = array('current_index' => $i,
												 'maxTid' => $_threads['maxTid'],
												);
			}
		}
		$_threadNum = 0;
		if ($orderType == 'DESC') {
			if ($minTid) {
				krsort($data);
				foreach($data as $k => $v) {
					$_threadNum++;
					$res['minTid'] = $k;
					$res['data'][$k] = $v;
					if ($_threadNum == $num) {
						break;
					}
				}
				if (!$res['minTid']) {
					$res['minTid'] = $minTid;
				}
			}
		} else {
			if ($maxTid) {
				ksort($data);
				foreach($data as $k => $v) {
					$_threadNum++;
					$res['data'][$k] = $v;
					$res['maxTid'] = $k;
					if ($_threadNum == $num) {
						break;
					}
				}
				if (!$res['maxTid']) {
					$res['maxTid'] = $maxTid;
				}
			}
		}

		if ($res['data']) {
			$_tableInfo['tables'] = $tables;

			$_tIds = array();
			$authors = $gfIds = array();
			foreach($res['data'] as $tId => $thread) {
				$_tIds[$thread['postTableId']][] = $tId;
				$authors[$thread['authorId']][] = $thread['tId'];
				if ($thread['isGroup']) {
					$gfIds[$thread['fId']] = $thread['fId'];
				}
			}

			if ($_tIds) {
				$guestPerm = Cloud_Service_SearchHelper::getGuestPerm($gfIds); // GuestPerm
				$threadPosts = Cloud_Service_SearchHelper::getThreadPosts($_tIds);
				foreach($res['data'] as $tId => $v) {
					$res['data'][$tId]['pId'] = $threadPosts[$tId]['pId'];
					if (in_array($v['fId'], $guestPerm['allowForumIds'])) {
						$res['data'][$tId]['isPublic'] = true;
					} else {
						$res['data'][$tId]['isPublic'] = false;
					}
				}
			}

			$authorids = array_keys($authors);
			if ($authorids) {
				$banuids= $uids = array();
				foreach(C::t('common_member')->fetch_all($authorids) as $uid => $author) {
					$uids[$uid] = $uid;
					if ($author['groupid'] == 4 || $author['groupid'] == 5) {
						$banuids[] = $author['uid'];
					}
				}
				$deluids = array_diff($authorids, $uids);
				foreach($deluids as $deluid) {
					if (!$deluid) {
						continue;
					}
					foreach($authors[$deluid] as $tid) {
						$res['data'][$tid]['authorStatus'] = 'delete';
					}
				}
				foreach($banuids as $banuid) {
					foreach($authors[$banuid] as $tid) {
						$res['data'][$tid]['authorStatus'] = 'ban';
					}
				}
			}
		}
		return $res;
	}

	public function onSearchGetForums($fIds = array()) {
		return Cloud_Service_SearchHelper::getForums($fIds);
	}

	public function onSearchSetConfig($data) {
		global $_G;
		$searchData = $_G['setting']['my_search_data'];
		if (!is_array($searchData)) {
			$searchData = array();
		}

		$settings = array();
		foreach($data as $k => $v) {
			if (substr($k, 0, strlen('hotWordChangedFId_')) == 'hotWordChangedFId_') {
				$hotWordChangedFId = dintval(substr($k, strlen('hotWordChangedFId_')));
				C::t('common_syscache')->delete('search_recommend_words_' . $hotWordChangedFId);
				continue;
			}
			if ($k == 'showDiscuzSearch' && $v) {
				$status = $v == 1 ? 1 : 0;
				$searchSetting = C::t('common_setting')->fetch('search', true);
				$searchSetting['forum']['status'] = $status;
				$settings['search'] = $searchSetting;
				continue;
			}
			$searchData[$k] = $v;
		}
		$settings['my_search_data'] = $searchData;

		C::t('common_setting')->update_batch($settings);
		require_once DISCUZ_ROOT . './source/function/function_cache.php';
		updatecache('setting');

		return true;
	}

	public function onSearchGetConfig($keys) {
		global $_G;
		$maps = array(
					'hotWords' => 'srchhotkeywords',
					'maxThreadPostId' => 'NON-SETTING',
					'rewrite' => 'rewrite',
					'domain' => 'domain',
					'mySearchData' => 'my_search_data',
					);
		$confs = array();
		foreach($keys as $key) {
			if ($fieldName = $maps[$key]) {
				if ($key == 'maxThreadPostId') {
					$confs[$key] = $this->_getMaxDataItem();
					continue;
				}

				if ($key == 'domain') {
					$conf = array();
					if ($_G['setting']['domain']) {
						if ($_G['setting']['domain']['list']) {
							foreach($_G['setting']['domain']['list'] as $k => $v) {
								$conf['subDomain'][$k]['id'] = $v['id'];
								$conf['subDomain'][$k]['type'] = $v['idtype'];
							}
						}
						$conf['moduleDomain'] = $_G['setting']['domain']['app'];
					}
					$confs[$key] = $conf;
					continue;
				}

				if ($key == 'rewrite') {
					$conf = array();
					if ($_G['setting']['rewritestatus'] && $_G['setting']['rewriterule']) {
						$conf['compatible'] = $_G['setting']['rewritecompatible'] ? true : false;
						foreach($_G['setting']['rewriterule'] as $mod => $rule) {
							$conf['modules'][$mod]['rule'] = $rule;
							if (in_array($mod, $_G['setting']['rewritestatus'])) {
								$conf['modules'][$mod]['status'] = true;
							} else {
								$conf['modules'][$mod]['status'] = false;
							}
						}
					}
					$confs[$key] = $conf;
					continue;
				}

				$confs[$key] = $_G['setting'][$fieldName];
			}
		}
		return $confs;
	}

	public function onSearchSetHotWords($data, $method = 'append', $limit = 0) {
		global $_G;

		$srchhotkeywords = array();
		if ($_G['setting']['srchhotkeywords']) {
			$srchhotkeywords = $_G['setting']['srchhotkeywords'];
		}
		$newHotWords = array();
		foreach($data as $k => $v) {
			$newHotWords[] = $v;
		}

		switch ($method) {
			case 'overwrite':
				$hotWords = $newHotWords;
				break;
			case 'prepend':
				$hotWords = array_merge($newHotWords, $srchhotkeywords);
				break;
			case 'append':
				$hotWords = array_merge($srchhotkeywords, $newHotWords);
				break;
		}

		if ($limit) {
			$hotWords = array_slice($hotWords, 0, $limit);
		}
		$hotWords = array_unique($hotWords);

		$hotWords = implode("\n", $hotWords);

		C::t('common_setting')->update('srchhotkeywords', $hotWords);
		require_once DISCUZ_ROOT . './source/function/function_cache.php';
		updatecache('setting');
		return true;
	}

	public function _getMaxDataItem() {

		$threadTableInfo = C::t('forum_thread')->gettablestatus();
		$maxTId = $threadTableInfo['Auto_increment'] - 1;

		$maxPId = C::t('forum_post_tableid')->fetch_max_id();
		$maxPId = intval($maxPId);

		return array('maxThreadId' => $maxTId, 'maxPostId' => $maxPId);
	}
}