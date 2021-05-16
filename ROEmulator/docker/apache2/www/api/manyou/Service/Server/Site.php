<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: Site.php 29263 2012-03-31 05:45:08Z yexinhao $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class Cloud_Service_Server_Site extends Cloud_Service_Server_Restful {

	protected static $_instance;

	public static function getInstance() {

		if (!(self::$_instance instanceof self)) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	public function onSiteGetAllUsers($from, $userNum, $friendNum = 500, $isExtra) {

		$result = array();
		if ($userNum <= 0) {
			$totalNum = C::t('common_member')->count();
			$maxUId = C::t('common_member')->max_uid();
			$result['totalNum'] = $totalNum;
			$result['maxUId'] = $maxUId;
		} else {
			$spaces = C::t('common_member')->range_by_uid($from, $userNum);
			$uIds = array_keys($spaces);
			$users = $this->getUsers($uIds, $spaces, true, $isExtra, true, $friendNum, true);
			$result['users'] = $users;
		}

		return $result;
	}

	public function onSiteGetUpdatedUsers($num) {
		$totalNum = C::t('common_member_log')->count();

		$users = array();
		if ($totalNum) {
			$deletedUsers = $userLogs = $uIds = array();
			$undeletedUserIds = array();
			foreach(C::t('common_member_log')->fetch_all_range($num) as $row) {
				$uIds[] = $row['uid'];
				if ($row['action'] == 'delete') {
					$deletedUsers[] = array('uId' => $row['uid'],
								'action' => $row['action'],
								);
				} else {
					$undeletedUserIds[] = $row['uid'];
				}
				$userLogs[$row['uid']] = $row;
			}

			$updatedUsers = $this->getUsers($undeletedUserIds, false, true, true, false);

			foreach($updatedUsers as $k => $v) {
				$updatedUsers[$k]['action'] = $userLogs[$v['uId']]['action'];
				$updatedUsers[$k]['updateType'] = 'all';
			}

			$users = array_merge($updatedUsers, $deletedUsers);

			if ($uIds) {
				C::t('common_member_log')->delete($uIds);
			}
		}

		$result = array('totalNum'	=> $totalNum, 'users' => $users);
		return $result;
	}

	public function onSiteGetUpdatedFriends($num) {
		$friends = array();
		$totalNum = C::t('home_friendlog')->count();
		if ($totalNum) {
			foreach(C::t('home_friendlog')->fetch_all_order_by_dateline(0, $num) as $friend) {
				$friends[] = array('uId' => $friend['uid'],
							'uId2' => $friend['fuid'],
							'action' => $friend['action']
							);
				C::t('home_friendlog')->delete_by_uid_fuid($friend['uid'], $friend['fuid']);
			}
		}

		return array('totalNum' => $totalNum, 'friends' => $friends);
	}

	public function onSiteGetStat($beginDate = null, $num = null, $orderType = 'ASC') {
		$result = array();
		$fields = array('login' => 'loginUserNum',
				'doing' => 'doingNum',
				'blog' => 'blogNum',
				'pic' => 'photoNum',
				'poll' => 'pollNum',
				'event' => 'eventNum',
				'share' => 'shareNum',
				'thread' => 'threadNum',
				'docomment' => 'doingCommentNum',
				'blogcomment' => 'blogCommentNum',
				'piccomment' => 'photoCommentNum',
				'pollcomment' => 'pollCommentNum',
				'eventcomment' => 'eventCommentNum',
				'sharecomment' => 'shareCommentNum',
				'pollvote' => 'pollUserNum',
				'eventjoin' => 'eventUserNum',
				'post' => 'postNum',
				'wall' => 'wallNum',
				'poke' => 'pokeNum',
				'click' => 'clickNum',
				);
		foreach(C::t('common_stat')->fetch_all_by_daytime($beginDate, 0, $num, $orderType) as $row) {
			$stat = array('date' => $row['daytime']);
			foreach($row as $k => $v) {
				if (array_key_exists($k, $fields)) {
					$stat[$fields[$k]] = $v;
				}
			}
			$result[] = $stat;
		}
		return $result;
	}

}