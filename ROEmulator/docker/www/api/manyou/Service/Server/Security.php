<?php

/**
 *		[Discuz!] (C)2001-2099 Comsenz Inc.
 *		This is NOT a freeware, use is subject to license terms
 *
 *		$Id: Security.php 31428 2012-08-28 02:35:36Z songlixin $
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


	public function onSecuritySetEvilUser($data) {
		$results = array();

		foreach ($data as $evilUser) {
			$results[] = $this->_handleEvilUser($evilUser['uid'], $evilUser['evilType'], $evilUser['evilLevel']);
		}
		return $results;
	}

	protected function _handleEvilPost($tid, $pid, $evilType, $evilLevel = 1) {

		include_once DISCUZ_ROOT.'./source/language/lang_admincp_cloud.php';

		$securityService = Cloud::loadClass('Service_Security');
		$securityService->writeLog($pid, 'pid');

		$evilPost = C::t('#security#security_evilpost')->fetch($pid);

		if (count($evilPost)) {
			$data = $evilPost;
			$data['evilcount'] = $evilPost['evilcount'] + 1;
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

			} else {
				$data['operateresult'] = 2;
				C::t('#security#security_evilpost')->insert($data, false, true);
			}
		}

		return true;
	}

	protected function _handleEvilUser($uid, $evilType, $evilLevel = 1) {
		global $_G;

		include_once DISCUZ_ROOT.'./source/language/lang_admincp_cloud.php';

		$securityService = Cloud::loadClass('Service_Security');
		$securityService->writeLog($uid, 'uid');

		$evilUser = C::t('#security#security_eviluser')->fetch($uid);

		if (count($evilUser)) {
			$data = $evilUser;
			$data['evilcount'] = $evilUser['evilcount'] + 1;
		} else {

			if ($this->_checkUserIgnore($uid)) {
				return false;
			}
			$data = array('uid' => $uid, 'evilcount' => 1, 'eviltype' => $evilType, 'createtime' => TIMESTAMP);
			$user = C::t('common_member')->fetch($uid, 0, 1);
			C::t('#security#security_eviluser')->insert($data, false, true);
			$this->_updateEvilCount('member');

			if (is_array($user)) {
				require_once libfile('function/misc');
				$update = C::t('common_member')->update($uid, array('groupid' => 4));
				if ($update) {
					$_G['member']['username'] = 'SYSTEM';
					savebanlog($user['username'], $user['groupid'], 4, 0, $extend_lang['security_modreason']);
				}
			} else {
				$data['operateresult'] = 2;
				C::t('#security#security_eviluser')->insert($data, false, true);
			}
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

		$whiteList = dunserialize($_G['setting']['security_forums_white_list']);
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
		$whiteList = dunserialize($_G['setting']['security_usergroups_white_list']);
		$whiteList = is_array($whiteList) ? $whiteList : array();

		$memberInfo = C::t('common_member')->fetch($uid, 0, 1);
		$checkResult = false;
		if (in_array($memberInfo['groupid'], $whiteList)) {
			$checkResult = true;
		}

		return $checkResult;
	}
}