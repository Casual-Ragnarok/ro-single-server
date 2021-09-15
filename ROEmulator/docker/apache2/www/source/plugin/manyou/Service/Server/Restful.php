<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: Restful.php 29263 2012-03-31 05:45:08Z yexinhao $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

define('MY_FRIEND_NUM_LIMIT', 2000);

Cloud::loadFile('Service_Server_RestfulException');
Cloud::loadFile('Service_Server_Response');
Cloud::loadFile('Service_Server_ErrorResponse');

abstract class Cloud_Service_Server_Restful {

	protected static $_instance;

	public static function getInstance() {

		if (!(self::$_instance instanceof self)) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	protected function _myAddslashes($string) {
		if(is_array($string)) {
			foreach($string as $key => $val) {
				$string[$key] = $this->_myAddslashes($val);
			}
		} else {
			$string = ($string === null) ? null : addslashes($string);
		}
		return $string;
	}

	protected function _myStripslashes($string) {
		if(is_array($string)) {
			foreach($string as $key => $val) {
				$string[$key] = $this->_myStripslashes($val);
			}
		} else {
			$string = ($string === null) ? null : stripslashes($string);
		}
		return $string;
	}

	public function onUsersGetInfo($uIds, $fields = array(), $isExtra = false) {
		return new Cloud_Service_Server_ErrorResponse('2', 'Method not implemented.');
	}

	public function onUsersGetFriendInfo($uId, $num = MY_FRIEND_NUM_LIMIT, $isExtra = false) {
		return new Cloud_Service_Server_ErrorResponse('2', 'Method not implemented.');
	}

	public function onUsersGetExtraInfo($uIds) {
		return new Cloud_Service_Server_ErrorResponse('2', 'Method not implemented.');
	}

	public function onUsersGetFormHash($uId, $userAgent) {
		return new Cloud_Service_Server_ErrorResponse('2', 'Method not implemented.');
	}

	public function onFriendsGet($uIds, $friendNum = MY_FRIEND_NUM_LIMIT) {
		return new Cloud_Service_Server_ErrorResponse('2', 'Method not implemented.');
	}

	public function onFriendsAreFriends($uId1, $uId2) {
		return new Cloud_Service_Server_ErrorResponse('2', 'Method not implemented.');
	}

	public function onUserApplicationAdd($uId, $appId, $appName, $privacy, $allowSideNav, $allowFeed, $allowProfileLink,  $defaultBoxType, $defaultMYML, $defaultProfileLink, $version, $displayMethod, $displayOrder = null, $userPanelArea = null, $canvasTitle = null,  $isFullscreen = null , $displayUserPanel = null, $additionalStatus = null) {
		return new Cloud_Service_Server_ErrorResponse('2', 'Method not implemented.');
	}

	public function onUserApplicationRemove($uId, $appIds) {
		return new Cloud_Service_Server_ErrorResponse('2', 'Method not implemented.');
	}

	public function onUserApplicationUpdate($uId, $appIds, $appName, $privacy, $allowSideNav, $allowFeed, $allowProfileLink, $version, $displayMethod, $displayOrder = null, $userPanelArea = null, $canvasTitle = null,  $isFullscreen = null, $displayUserPanel = null) {
		return new Cloud_Service_Server_ErrorResponse('2', 'Method not implemented.');
	}

	public function onUserApplicationGetInstalled($uId) {
		return new Cloud_Service_Server_ErrorResponse('2', 'Method not implemented.');
	}

	public function onUserApplicationGet($uId, $appIds) {
		return new Cloud_Service_Server_ErrorResponse('2', 'Method not implemented.');
	}

	public function onSiteGetUpdatedUsers($num) {
		return new Cloud_Service_Server_ErrorResponse('2', 'Method not implemented.');
	}

	public function onSiteGetUpdatedFriends($num) {
		return new Cloud_Service_Server_ErrorResponse('2', 'Method not implemented.');
	}

	public function onSiteGetAllUsers($from, $num, $friendNum = MY_FRIEND_NUM_LIMIT) {
		return new Cloud_Service_Server_ErrorResponse('2', 'Method not implemented.');
	}

	public function onSiteGetStat($beginDate = null, $num = null, $orderType = 'ASC') {
		return new Cloud_Service_Server_ErrorResponse('2', 'Method not implemented.');
	}

	public function onFeedPublishTemplatizedAction($uId, $appId, $titleTemplate, $titleData, $bodyTemplate, $bodyData, $bodyGeneral = '', $image1 = '', $image1Link = '', $image2 = '', $image2Link = '', $image3 = '', $image3Link = '', $image4 = '', $image4Link = '', $targetIds = '', $privacy = '', $hashTemplate = '', $hashData = '', $specialAppid=0) {
		return new Cloud_Service_Server_ErrorResponse('2', 'Method not implemented.');
	}

	public function onNotificationsSend($uId, $recipientIds, $appId, $notification) {
		return new Cloud_Service_Server_ErrorResponse('2', 'Method not implemented.');
	}

	public function onNotificationsGet($uId) {
		return new Cloud_Service_Server_ErrorResponse('2', 'Method not implemented.');
	}

	public function onApplicationUpdate($appId, $appName, $version, $displayMethod, $displayOrder = null, $userPanelArea = null, $canvasTitle = null,  $isFullscreen = null, $displayUserPanel = null, $additionalStatus = null) {
		return new Cloud_Service_Server_ErrorResponse('2', 'Method not implemented.');
	}

	public function onApplicationRemove($appIds) {
		return new Cloud_Service_Server_ErrorResponse('2', 'Method not implemented.');
	}

	public function onApplicationSetFlag($applications, $flag) {
		return new Cloud_Service_Server_ErrorResponse('2', 'Method not implemented.');
	}

	public function onCreditGet($uId) {
		return new Cloud_Service_Server_ErrorResponse('2', 'Method not implemented.');
	}

	public function onRequestSend($uId, $recipientIds, $appId, $requestName, $myml, $type) {
		return new Cloud_Service_Server_ErrorResponse('2', 'Method not implemented.');
	}

	public function onVideoAuthSetAuthStatus($uId, $status) {
		return new Cloud_Service_Server_ErrorResponse('2', 'Method not implemented.');
	}

	public function onVideoAuthAuth($uId, $picData, $picExt = 'jpg', $isReward = false) {
		return new Cloud_Service_Server_ErrorResponse('2', 'Method not implemented.');
	}

	public function onSearchGetUserGroupPermissions($userGroupIds) {
		return new Cloud_Service_Server_ErrorResponse('2', 'Method not implemented.');
	}

	public function onSearchGetUpdatedPosts($num, $lastPostIds = array()) {
		return new Cloud_Service_Server_ErrorResponse('2', 'Method not implemented.');
	}

	public function onSearchRemovePostLogs($pIds) {
		return new Cloud_Service_Server_ErrorResponse('2', 'Method not implemented.');
	}

	public function onSearchGetPosts($pIds) {
		return new Cloud_Service_Server_ErrorResponse('2', 'Method not implemented.');
	}

	public function onSearchGetNewPosts($num, $fromPostId = 0) {
		return new Cloud_Service_Server_ErrorResponse('2', 'Method not implemented.');
	}

	public function onSearchGetAllPosts($num, $pId = 0, $orderType = 'ASC') {
		return new Cloud_Service_Server_ErrorResponse('2', 'Method not implemented.');
	}

	public function onSearchRecyclePosts($pIds) {
		return new Cloud_Service_Server_ErrorResponse('2', 'Method not implemented.');
	}

	public function onSearchGetUpdatedThreads($num, $lastThreadIds = array(), $lastForumIds = array(), $lastUserIds = array()) {
		return new Cloud_Service_Server_ErrorResponse('2', 'Method not implemented.');
	}

	public function onSearchRemoveThreadLogs($lastThreadIds = array(), $lastForumIds = array(), $lastUserIds = array()) {
		return new Cloud_Service_Server_ErrorResponse('2', 'Method not implemented.');
	}

	public function onSearchGetThreads($tIds) {
		return new Cloud_Service_Server_ErrorResponse('2', 'Method not implemented.');
	}

	public function onSearchRecycleThreads($tIds) {
		return new Cloud_Service_Server_ErrorResponse('2', 'Method not implemented.');
	}

	public function onSearchGetNewThreads($num, $tId = 0) {
		return new Cloud_Service_Server_ErrorResponse('2', 'Method not implemented.');
	}

	public function onSearchGetAllThreads($num, $tId = 0, $orderType = 'ASC') {
		return new Cloud_Service_Server_ErrorResponse('2', 'Method not implemented.');
	}

	public function onSearchGetForums($fIds = array()) {
		return new Cloud_Service_Server_ErrorResponse('2', 'Method not implemented.');
	}

	public function onSearchSetConfig($data = array()) {
		return new Cloud_Service_Server_ErrorResponse('2', 'Method not implemented.');
	}

	public function onSearchGetConfig($data = array()) {
		return new Cloud_Service_Server_ErrorResponse('2', 'Method not implemented.');
	}

	public function onSearchSetHotWords($hotWords = array()) {
		return new Cloud_Service_Server_ErrorResponse('2', 'Method not implemented.');
	}


	public function onCommonGetNav($type = '') {
		return new Cloud_Service_Server_ErrorResponse('2', 'Method not implemented.');
	}

	public function onCloudGetApps($appName = '') {
		return new Cloud_Service_Server_ErrorResponse('2', 'Method not implemented.');
	}

	public function onCloudSetApp($app) {
		return new Cloud_Service_Server_ErrorResponse('2', 'Method not implemented.');
	}

	public function onCloudOpenCloud() {
		return new Cloud_Service_Server_ErrorResponse('2', 'Method not implemented.');
	}

	public function onCloudGetStats() {
		return new Cloud_Service_Server_ErrorResponse('2', 'Method not implemented.');
	}

	public function onConnectSetConfig($data = array()) {
		return new Cloud_Service_Server_ErrorResponse('2', 'Method not implemented.');
	}

	public function onUnionAddAdvs($advs = array()) {
		return new Cloud_Service_Server_ErrorResponse('2', 'Method not implemented.');
	}


	protected function _convertPrivacy($privacy, $u2m = false) {
		$privacys = array(0=>'public', 1=>'friends', 2=>'someFriends', 3=>'me', 4=>'passwd');
		$privacys = ($u2m) ? $privacys : array_flip($privacys);
		return $privacys[$privacy];
	}

	protected function _spaceInfo2Extra($rows) {
		$privacy = dunserialize($rows['privacy']);
		$profilePrivacy = $privacy['profile'];

		$res = array();
		$map = array(
					 'graduateschool' => array('edu', 'school', true),
					 'company' => array('work', 'company', true),
					 'lookingfor' => array('trainwith', 'value'),
					 'interest' => array('interest', 'value'),
					 'bio' => array('intro', 'value')
					 );

		foreach ($map as $dzKey => $myKeys) {
			if ($rows[$dzKey]) {
				$data = array('privacy' => $this->_convertPrivacy($profilePrivacy[$dzKey], true), $myKeys[1] => $rows[$dzKey]);
				if ($myKeys[2]) {
					$res[$myKeys[0]][] = $data;
				} else {
					$res[$myKeys[0]] = $data;
				}
			}
		}

		return $res;
	}

	protected function _friends2friends($friends , $num, $isOnlyReturnId = false, $isFriendIdKey = false) {
		$i = 1;
		$res = array();
		foreach($friends as $friend) {
			if($num && $i > $num) {
				break;
			}
			if ($isOnlyReturnId) {
				$row = $friend['fuid'];
			} else {
				$row = array('uId' => $friend['fuid'],
						'handle' => $friend['fusername']
						);
			}
			if ($isFriendIdKey) {
				$res[$friend['fuid']] = $row;
			} else {
				$res[] = $row;
			}
			$i++;
		}
		return $res;
	}

	protected function _space2user($space) {
		global $_G;

		if(!$space) {
			return array();
		}
		$founders = empty($_G['config']['admincp']['founder'])?array():explode(',', $_G['config']['admincp']['founder']);
		$adminLevel = 'none';
		if($space['groupid'] == 1 && $space['adminid'] == 1) {
			$adminLevel = 'manager';
			if($founders
			 && (in_array($space['uid'], $founders)
			 || (!is_numeric($space['username']) && in_array($space['username'], $founders)))) {
				$adminLevel = 'founder';
			}
		}

		$privacy = dunserialize($space['privacy']);
		if (!$privacy) {
			$privacy = array();
		}

		$profilePrivacy = array();
		$map = array('affectivestatus' => 'relationshipStatus',
					 'birthday' => 'birthday',
					 'bloodtype' => 'bloodType',
					 'birthcity' => 'birthPlace',
					 'residecity' => 'residePlace',
					 'mobile' => 'mobile',
					 'qq' => 'qq',
					 'msn' => 'msn');
		$privacys = dunserialize($space['privacy']);
		foreach ($map as $dzKey => $myKey) {
			$profilePrivacy[$myKey] = $this->_convertPrivacy($privacys['profile'][$dzKey], true);
		}

		$user = array(
			'uId'		=> $space['uid'],
			'handle'	=> $space['username'],
			'action'	=> $space['action'],
			'realName'	=> $space['realname'],
			'realNameChecked' => $space['realname'] ? true : false,
			'gender'	=> $space['gender'] == 1 ? 'male' : ($space['gender'] == 2 ? 'female' : 'unknown'),
			'email'		=> $space['email'],
			'qq'		=> $space['qq'],
			'msn'		=> $space['msn'],
			'birthday'	=> sprintf('%04d-%02d-%02d', $space['birthyear'], $space['birthmonth'], $space['birthday']),
			'bloodType'	=> empty($space['bloodtype']) ? 'unknown' : $space['bloodtype'],
			'relationshipStatus' => $space['affectivestatus'],
			'birthProvince' => $space['birthprovince'],
			'birthCity'	=> $space['birthcity'],
			'resideProvince' => $space['resideprovince'],
			'resideCity'	=> $space['residecity'],
			'viewNum'	=> $space['views'],
			'friendNum'	=> $space['friends'],
			'feedfriend'	=> $space['feedfriend'],
			'myStatus'	=> $space['spacenote'],
			'lastActivity' => $space['lastactivity'],
			'created'	=> $space['regdate'],
			'credit'	=> $space['credits'],
			'isUploadAvatar'	=> $space['avatarstatus'] ? true : false,
			'adminLevel'		=> $adminLevel,

			'homepagePrivacy'	=> $this->_convertPrivacy($privacy['view']['index'], true),
			'profilePrivacyList'	=> $profilePrivacy,
			'friendListPrivacy'	=> $this->_convertPrivacy($privacy['view']['friend'], true)
			);
		return $user;
	}

	protected function _getFriends($uId, $num = null) {
		global $_G;

		$fquery = C::t('home_friend')->fetch_all_by_uid($uId, 0, $num);
		$friends = array();
		foreach($fquery as $friend) {
			$friends[] = $friend['fuid'];
		}
		return $friends;
	}


	public function refreshApplication($appId, $appName, $version, $userPanelArea, $canvasTitle, $isFullscreen, $displayUserPanel, $displayMethod, $narrow, $flag, $displayOrder, $additionalStatus) {
		global $_G;

		$fields = array();
		if ($appName !== null && strlen($appName)>1) {
			$fields['appname'] = $appName;
		}
		if ($version !== null) {
			$fields['version'] = $version;
			$fields['iconstatus'] = 0;
			$fields['icondowntime'] = 0;
		}
		if ($displayMethod !== null) {
			$fields['displaymethod'] = $displayMethod;
		}
		if ($narrow !== null) {
			$fields['narrow'] = $narrow;
		}
		if ($flag !== null) {
			$fields['flag'] = $flag;
		}
		if ($displayOrder !== null) {
			$fields['displayorder'] = $displayOrder;
		}
		if ($userPanelArea !== null) {
			$fields['userpanelarea'] = $userPanelArea;
		}
		if ($canvasTitle !== null) {
			$fields['canvastitle'] = $canvasTitle;
		}
		if ($isFullscreen !== null) {
			$fields['fullscreen'] = $isFullscreen;
		}
		if ($displayUserPanel !== null) {
			$fields['displayuserpanel'] = $displayUserPanel;
		}
		if ($additionalStatus !== null) {
			$fields['appstatus'] = $additionalStatus == 'new' ? 1 : ($additionalStatus == 'none' ? 0 : 2);
		}

		$result = false;
		if($application = C::t('common_myapp')->fetch($appId)) {
			$needUpdate = false;
			foreach ($fields as $key => $value) {
				if ($value != $application[$key]) {
					$needUpdate = true;
					break;
				}
			}
			if ($needUpdate) {
				C::t('common_myapp')->update($appId, $fields);
			}
			$result = true;
		} else {
			$fields['appid'] = $appId;
			$result = C::t('common_myapp')->insert($fields, true);
			$result = true;
		}
		require_once libfile('function/cache');
		updatecache(array('myapp', 'userapp'));

		return $result;
	}

	public function getUsers($uIds, $spaces = array(), $isReturnSpaceField = true, $isExtra = true, $isReturnFriends = false, $friendNum = 500, $isOnlyReturnFriendId = false, $isFriendIdKey = false) {
		if (!$uIds) {
			return array();
		}

		if (!is_array($uIds)) {
			$uIds = (array)$uIds;
		}

		if (!$spaces) {
			$spaces = C::t('common_member')->fetch_all($uIds);
		}

		$totalFriendsNum = 0;
		foreach(C::t('common_member_count')->fetch_all($uIds) as $uid => $row) {
			$spaces[$uid] = array_merge($spaces[$uid], $row);
			$totalFriendsNum += $row['friends'];
		}

		foreach(C::t('common_member_status')->fetch_all($uIds) as $uid => $row) {
			$spaces[$uid] = array_merge($spaces[$uid], $row);
		}

		$spaceFields = array();
		if ($isReturnSpaceField) {
			$spaceFields = C::t('common_member_profile')->fetch_all($uIds);
		}

		foreach(C::t('common_member_field_home')->fetch_all($uIds) as $uid => $row) {
			$spaces[$uid] = array_merge($spaces[$uid], $row);
			$spaceFields[$uid] = array_merge($spaceFields[$uid], $row);
		}

		$friends = array();
		if ($isReturnFriends) {
			if ($totalFriendsNum <= 10000) {
				$query = C::t('home_friend')->fetch_all_by_uid($uIds);
				foreach($query as $row) {
					$friends[$row['uid']][] = $row;
				}
			} else {
				foreach ($uIds as $uId) {
					$query = C::t('home_friend')->fetch_all_by_uid($uId, 0 , $friendNum);
					foreach($query as $row) {
						$friends[$uId][] = $row;
					}
				}
			}
		}

		$users = array();
		foreach($uIds as $uId) {
			$space = $spaces[$uId];
			if ($isReturnSpaceField) {
				$space = array_merge($spaceFields[$uId], $space);
			}
			$user = $this->_space2user($space);
			if (!$user) {
				continue;
			}

			if ($isExtra) {
				$user['extra'] = $this->_spaceInfo2Extra($spaceFields[$uId]);
			}

			if ($isReturnFriends) {
				$user['friends'] = $this->_friends2friends($friends[$uId], $friendNum, $isOnlyReturnFriendId, $isFriendIdKey);
			}
			$users[] = $user;
		}
		return $users;
	}

	public function getExtraByUsers($uIds) {
		if (!$uIds) {
			return array();
		}

		if (!is_array($uIds)) {
			$uIds = (array)$uIds;
		}

		$spaceFields = array();
		$spaceFields = C::t('common_member_profile')->fetch_all($uIds);

		foreach(C::t('common_member_field_home')->fetch_all($uIds) as $uid => $row) {
			$spaceFields[$uid] = array_merge($spaceFields[$uid], $row);
		}

		$users = array();
		foreach($uIds as $uId) {
			$user = array('uId' => $uId,
					'extra' => $this->_spaceInfo2Extra($spaceFields[$uId]));
			$users[] = $user;
		}

		return $users;
	}

	function getUserSpace($uId) {
		global $_G;

		$space = getuserbyuid($uId);
		if (!$space['uid']) {
			return false;
		}

		$_G['uid'] = $space['uid'];
		$_G['username'] = $space['username'];

		return true;
	}

}