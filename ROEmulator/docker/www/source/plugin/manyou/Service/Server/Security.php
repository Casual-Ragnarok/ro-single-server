<?php

/**
 *		[Discuz!] (C)2001-2099 Comsenz Inc.
 *		This is NOT a freeware, use is subject to license terms
 *
 *		$Id: Security.php 33923 2013-09-03 02:59:43Z nemohou $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

Cloud::loadFile('Service_Server_Restful');

class Cloud_Service_Server_Security extends Cloud_Service_Server_Restful {

	protected static $_instance;

	public static function getInstance() {

		if (!(self::$_instance instanceof self)) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	public function onSecuritySetEvilPost($data) {

		$results = array();
		foreach ($data as $evilPost) {

			$results[] = $this->_handleEvilPost($evilPost['tid'], $evilPost['pid'], $evilPost['evilType'], $evilPost['evilLevel']);
		}

		return $results;
	}

	public function onSecuritySetEvilUser($data, $days = 1) {
		$results = array();

		foreach ($data as $evilUser) {
			$results[] = $this->_handleEvilUser($evilUser['uid'], $evilUser['evilType'], $evilUser['evilLevel'], $days);
		}
		return $results;
	}

	protected function _handleEvilPost($tid, $pid, $evilType, $evilLevel = 1) {

		include_once DISCUZ_ROOT.'./source/language/lang_admincp_cloud.php';

		$securityService = Cloud::loadClass('Service_Security');
		$securityService->writeLog($pid, 'pid');

		$evilPost = C::t('#security#security_evilpost')->fetch($pid);

		if (count($evilPost)) {
			return true;
		} else {
			require_once libfile('function/delete');
			require_once libfile('function/forum');
			require_once libfile('function/post');

			$data = array('pid' => $pid, 'tid' => $tid, 'evilcount' => 1, 'eviltype' => $evilType, 'createtime' => TIMESTAMP);
			$post = get_post_by_pid($pid);

			if (is_array($post) && count($post) > 0) {
				if ($tid != $post['tid']) {
					return false;
				}

				$thread = get_thread_by_tid($tid);

				if ($post['first']) {
					$data['type'] = 1;
					if ($this->_checkThreadIgnore($tid)) {
						return false;
					}
					C::t('#security#security_evilpost')->insert($data, false, true);
					$this->_updateEvilCount('thread');
					deletethread(array($tid), true, true, true);
					updatemodlog($tid, 'DEL', 0, 1, $extend_lang['security_modreason']);
				} else {
					$data['type'] = 0;
					if ($this->_checkPostIgnore($pid, $post)) {
						return false;
					}
					C::t('#security#security_evilpost')->insert($data, false, true);
					$this->_updateEvilCount('post');

					deletepost(array($pid), 'pid', true, false, true);
				}
				if(!empty($post['authorid'])) {
					$data = array('uid' => $post['authorid'], 'createtime' => TIMESTAMP);
					C::t('#security#security_eviluser')->insert($data, false, true);
				}
			} else {
				$data['operateresult'] = 2;
				C::t('#security#security_evilpost')->insert($data, false, true);
			}
			if($evilLevel >= 5) {
				$user = C::t('common_member')->fetch($post['authorid'], 0, 1);
				$this->_handleBandUser($user, 1);
			}
		}

		return true;
	}

	protected function _handleBandUser($user, $days = 1) {
		$uid = $user['uid'];
		if($this->_checkUserIgnore($uid)) {
			return false;
		}
		require_once libfile('function/forum');
		$setarr = array('groupid' => 4);
		if($days) {
			$days = !empty($days) ? TIMESTAMP + $days * 86400 : 0;
			$days = $days > TIMESTAMP ? $days : 0;
			if($days) {
				$user['groupterms']['main'] = array('time' => $days, 'adminid' => $user['adminid'], 'groupid' => $user['groupid']);
				$user['groupterms']['ext'][4] = $days;
				C::t('common_member_field_forum')->update($uid, array('groupterms' => serialize($user['groupterms'])));
				$setarr['groupexpiry'] = groupexpiry($user['groupterms']);
			} else {
				$setarr['groupexpiry'] = 0;
			}
		}

		require_once libfile('function/misc');
		return C::t('common_member')->update($uid, $setarr);
	}

	protected function _handleEvilUser($uid, $evilType, $evilLevel = 1, $days = 1) {
		global $_G;

		include_once DISCUZ_ROOT.'./source/language/lang_admincp_cloud.php';

		$securityService = Cloud::loadClass('Service_Security');
		$securityService->writeLog($uid, 'uid');

		if($this->_checkUserIgnore($uid)) {
			return false;
		}
		$user = C::t('common_member')->fetch($uid, 0, 1);

		if(is_array($user)) {
			$update = $this->_handleBandUser($user, $days);
			if ($update) {
				$_G['member']['username'] = 'SYSTEM';
				savebanlog($user['username'], $user['groupid'], 4, 0, $extend_lang['security_modreason']);
			}
		}

		$evilUser = C::t('#security#security_eviluser')->fetch($uid);

		if (count($evilUser)) {
			return true;
		} else {
			$data = array('uid' => $uid, 'evilcount' => 1, 'eviltype' => $evilType, 'createtime' => TIMESTAMP);

			C::t('#security#security_eviluser')->insert($data, false, true);
			$this->_updateEvilCount('member');
		}

		return true;
	}

	protected function _checkThreadIgnore($tid) {

		if (!intval($tid)) {
			return true;
		}
		require_once libfile('function/forum');
		$checkFiled = array('highlight', 'displayorder', 'digest');
		$thread = get_thread_by_tid($tid);
		$checkResult = false;
		$checkResult = $this->_checkBoardIgnore($thread['fid']);
		$checkResult = $checkResult ? true : $this->_checkUserIgnore($thread['authorid']);
		foreach ($checkFiled as $field) {
			if ($thread[$field] > 0) {
				$checkResult = true;
			};
		}

		return $checkResult;
	}

	protected function _updateEvilCount($type) {

		if (empty($type)) {
			return false;
		}

		$settingKey = 'cloud_security_stats_' . $type;
		$count = intval(C::t('common_setting')->fetch($settingKey));
		C::t('common_setting')->update($settingKey, $count + 1);

	}

	protected function _checkPostIgnore($pid, $post) {
		if (!intval($pid)) {
			return true;
		}
		$checkResult = false;
		$checkResult = $this->_checkBoardIgnore($post['fid']);
		$checkResult = $checkResult ? true : $this->_checkUserIgnore($post['authorid']);

		$postStick = C::t('forum_poststick')->count_by_pid($pid);
		if ($checkResult || $postStick) {
			$checkResult = true;
		}

		return $checkResult;
	}

	protected function _checkBoardIgnore($fid) {
		global $_G;
		$checkResult = false;

		$whiteList = $_G['setting']['security_forums_white_list'];
		$whiteList = is_array($whiteList) ? $whiteList : array();
		if (in_array($fid, $whiteList)) {
			$checkResult = true;
		}
		return $checkResult;
	}

	protected function _checkUserIgnore($uid) {
		global $_G;
		if (!intval($uid)) {
			return true;
		}
		$whiteList = $_G['setting']['security_usergroups_white_list'];
		$whiteList = is_array($whiteList) ? $whiteList : array();

		$memberInfo = C::t('common_member')->fetch($uid, 0, 1);
		$checkResult = false;
		if (in_array($memberInfo['groupid'], $whiteList)) {
			$checkResult = true;
		}

		return $checkResult;
	}
}