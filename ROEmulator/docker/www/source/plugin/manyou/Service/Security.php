<?php

/**
 *		[Discuz!] (C)2001-2099 Comsenz Inc.
 *		This is NOT a freeware, use is subject to license terms
 *
 *		$Id: Security.php 30564 2012-06-04 05:38:45Z songlixin $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class Cloud_Service_Security {
	protected static $debug = 0;
	protected static $_secClient;
	protected static $_secStatus;
	protected static $postAction = array('newThread', 'newPost', 'editPost', 'editThread');
	protected static $userAction = array('register', 'login');
	protected static $delPostAction = array('delThread', 'delPost');
	protected static $delUserAction = array('banUser');
	protected static $retryLimit = 8;
	protected static $specialType = array('1' => 'poll', '2' => 'trade', '3' => 'reward', '4' => 'activity', '5' => 'debate');
	protected static $_instance;

	public static function getInstance() {

		if (!(self::$_instance instanceof self)) {
			self::$_instance = new self();
			$cloudAppService = Cloud::loadClass('Service_App');
			self::$_secStatus = $cloudAppService->getCloudAppStatus('security');
			self::$_instance->_setClient();
		}

		return self::$_instance;
	}

	private function _getUA() {
		return $_SERVER['HTTP_USER_AGENT'];
	}

	private function _setClient() {
		if (!self::$_secStatus) {
			return false;
		}

		self::$_secClient = Cloud::loadClass('Service_Client_Security');
	}

	public function reportRegister($uid) {
		global $_G;
		if (!self::$_secStatus) {
			return false;
		}
		$startTime = microtime(true);

		$uid = dintval($uid);
		$member = C::t('common_member')->fetch($uid);
		if (!is_array($member)) {
			return true;
		}
		$openId = $this->_getOpenId($uid);

		$secReportCodeStatus = ($_G['setting']['seccodestatus'] & 1) ? 1 : 2;

		$batchData = array();
		$batchData[] = array(
			'siteUid' => $member['uid'],
			'username' => $member['username'],
			'email' => $member['email'],
			'openId' => $openId,
			'registerTime' => $_G['timestamp'],
			'clientIp' => $_G['clientip'],
			'remoteIp' => $_SERVER['REMOTE_ADDR'],
			'hasVerifyCode' => $secReportCodeStatus,
			'regResult' => 1,
			'userAgent' => $this->_getUA(),
			'extra' => $extra
		);
		$result = false;
		try {
			$result = self::$_secClient->securityReportUserRegister($batchData);
		} catch (Cloud_Service_Client_RestfulException $e) {
			$ids = array($uid);
			$this->logFailed('register', $ids);
		} catch (Exception $e) {
		}
		$this->benchMarkLog($startTime, $member['uid'], $batchData, 'register');

		return $result;
	}


	public function reportLogin($uid) {
		global $_G;
		if (!self::$_secStatus) {
			return false;
		}
		$startTime = microtime(true);

		$uid = dintval($uid);
		$member = C::t('common_member')->fetch($uid, 0 ,1);
		if (!is_array($member)) {
			return true;
		}
		$memberField = C::t('common_member_field_forum')->fetch($uid, 0, 1);
		$memberStatus = C::t('common_member_status')->fetch($uid, 0, 1);
		$memberCount = C::t('common_member_count')->fetch($uid, 0, 1);
		$memberVerify = C::t('common_member_verify')->fetch($uid);

		$openId = $this->_getOpenId($uid);

		$userBitMap['isAdmin'] = $member['adminid'] ? 1 : 2;
		$userBitMap['hasMedal'] = $memberField['medals'] ? 1 : 2;
		$userBitMap['hasAvatar'] = $member['avatarstatus'] ? 1 : 2;
		$userBitMap['hasVerify'] = (count($memberVerify)) ? 1 : 2;

		$username = $member['username'];
		$email = $member['email'];
		$emailStatus = $member['emailstatus'];
		$sUrl = $_G['siteurl'];
		$credits = $member['credits'];
		$regIp = $memberStatus['regip'];
		$regDate = $member['regdate'];
		$friends = $memberCount['friends'];
		$onlineTime = $memberCount['oltime']*3600;
		$threads = $memberCount['threads'];
		$posts = $memberCount['posts'];
		$signature = $memberField['sightml'];

		if (!$regIp) {
			$regIp = 'N/A';
		}

		if ($_G['clientip'] == NULL) {
			$_G['clientip'] = 'N/A';
		}

		$batchData = array();
		$batchData[] = array(
			'siteUid' => $uid,
			'openId' => $openId,
			'loginTime' => TIMESTAMP,
			'clientIp' => $_G['clientip'],
			'remoteIp' => $_SERVER['REMOTE_ADDR'],
			'username' => $username,
			'email' => $email,
			'emailStatus' => $emailStatus,
			'sUrl' => $sUrl,
			'credits' => $credits,
			'registerIp' => $regIp,
			'registerTime' => $regDate,
			'friends' => $friends,
			'onlineTime' => $onlineTime,
			'threads' => $threads,
			'posts' => $posts,
			'signature' => $signature,
			'userBitMap' => $userBitMap,
			'userAgent' => $this->_getUA(),
			'extra' => $extra
		);

		$result = false;
		try {
			$result = self::$_secClient->securityReportUserLogin($batchData);
		} catch (Cloud_Service_Client_RestfulException $e) {
			$ids = array($uid);
			$this->logFailed('login', $ids);
		} catch (Exception $e) {

		}
		$this->benchMarkLog($startTime, $uid, $batchData, 'login');

		return $result;
	}


	public function reportPost($type, $tid, $pid, $extra = NULL, $isFollow = 0) {
		global $_G;
		$utilService = Cloud::loadClass('Service_Util');
		if (!self::$_secStatus) {
			return false;
		}
		$startTime = microtime(true);

		$tid = dintval($tid);
		$pid = dintval($pid);
		$url = $_G['siteurl'] . "forum.php?mod=redirect&goto=findpost&ptid=$tid&pid=$pid";

		include_once libfile('function/forum');
		$thread = get_thread_by_tid($tid);
		if (!is_array($thread)) {
			return true;
		}
		$post = get_post_by_pid($pid);
		$member = C::t('common_member')->fetch($post['authorid'], 0, 1);
		$memberField = C::t('common_member_field_forum')->fetch($post['authorid'], 0, 1);
		$memberStatus = C::t('common_member_status')->fetch($post['authorid'], 0, 1);
		$memberVerify = C::t('common_member_verify')->fetch($post['authorid']);

		if ($post['first'] == 1) {
			$type = $type . 'Thread';
		} else {
			$type = $type . 'Post';
		}

		$id = 'pid:' . $pid;
		$aids = C::t('forum_attachment')->fetch_all_by_id('pid', $pid);
		$aids = array_keys($aids);

		$postAttachs = C::t('forum_attachment_n')->fetch_all($id, $aids);

		if (!$post['first']) {
			$firstPost = C::t('forum_post')->fetch_all_by_tid($thread['posttableid'], $tid, 1, '', 0, 0, 1);
			$firstPost = array_pop($firstPost);

			$id = 'pid:' . $firstPost['pid'];
			$aids = C::t('forum_attachment')->fetch_all_by_id('pid', $firstPost['pid']);
			$aids = array_keys($aids);
			$threadAttachs = C::t('forum_attachment_n')->fetch_all($id, $aids);
		} else {
			$firstPost = $post;
			$firstPostAttachs = $postAttachs;
		}

		$views = dintval($thread['views']);
		$replies = dintval($thread['replies']);
		$favourites = dintval($thread['favtimes']);
		$supports = dintval($thread['recommend_add']);
		$opposes = dintval($thread['recommend_sub']);
		$shares = dintval($thread['sharetimes']);

		$openId = $this->_getOpenId($_G['uid']);

		if (!$thread || !$post) {
			return true;
		}

		$fid = $thread["fid"];

		if ($post['first']) {
			$threadShield = ($post['status'] & 1) ? 1 : 2;
			$threadWarning = ($post['status'] & 2) ? 1 : 2;
		} else {
			$threadShield = ($firstPost['status'] & 1) ? 1 : 2;
			$threadWarning = ($firstPost['status'] & 2) ? 1 : 2;
		}

		$contentBitMap = array(
			'onTop' => $thread['displayorder'] ? 1:2,
			'hide' => (strpos($post['message'], '[hide')) ? 1:2,
			'digest' => $thread['digest'] ? 1 : 2,
			'highLight' => $thread['highlight'] ? 1:2,
			'special' => $thread['special'] ? 1:2,
			'threadAttach' => 2,
			'threadAttachFlash' => 2,
			'threadAttachPic' => 2,
			'threadAttachVideo' => 2,
			'threadAttachAudio' => 2,
			'threadShield' => $threadShield,
			'threadWarning' => $threadWarning,
			'postAttach' => 2,
			'postAttachFlash' => 2,
			'postAttachPic' => 2,
			'postAttachVideo' => 2,
			'postAttachAudio' => 2,
			'postShield' => ($post['status'] & 1) ? 1 : 2,
			'postWarning' => ($post['status'] & 2) ? 1 : 2,
			'isAdmin' => $member['adminid'] ? 1 : 2,
			'isRush' => getstatus($thread['status'], 3) ? 1 : 2,
			'hasReadPerm' => $thread['readperm'] ? 1 : 2,
			'hasStamp' => ($thread['stamp'] >= 0) ? 1 : 2,
			'hasIcon' => ($thread['icon'] >= 0) ? 1 : 2,
			'isPushed' => $thread['pushedaid'] ? 1 : 2,
			'hasCover' => $thread['cover'] ? 1 : 2,
			'hasReward' => $thread['replycredit'] ? 1 : 2,
			'isFollow' => $isFollow ? 1 : 2,
			'threadStatus' => $thread['status'],
			'postStatus' => $post['status'],
		);

		if ($post['first']) {
			$contentBitMap['isMobile'] = $utilService->isMobile($thread['status']) ? 1 : 2;
			if ($contentBitMap['isMobile'] == 1) {
				$contentBitMap['isMobileSound'] = $utilService->mobileHasSound($thread['status']) ? 1 : 2;
				$contentBitMap['isMobilePhoto'] = $utilService->mobileHasPhoto($thread['status']) ? 1 : 2;
				$contentBitMap['isMobileGPS'] = $utilService->mobileHasGPS($thread['status']) ? 1 : 2;
			}
		} else {
			$contentBitMap['isMobile'] = getstatus($post['status'], 4) ? 1 : 2;
		}


		$userBitMap['isAdmin'] = $member['adminid'] ? 1 : 2;
		$userBitMap['hasMedal'] = $memberField['medals'] ? 1 : 2;
		$userBitMap['hasAvatar'] = $member['avatarstatus'] ? 1 : 2;
		$userBitMap['hasVerify'] = (count($memberVerify)) ? 1 : 2;

		$videoExt = array('.rm', '.flv', '.mkv', '.rmvb', '.avi', '.wmv', '.mp4', '.mpeg', '.mpg');
		$audioExt = array('.wav', '.mid', '.mp3', '.m3u', '.wma', '.asf', '.asx');
		if ($firstPostAttachs) {
			foreach($firstPostAttachs as $attach) {
				$fileExt = substr($attach['filename'], strrpos($attach['filename'], '.'));
				if ($fileExt == '.bmp' || $fileExt == '.jpg' || $fileExt == '.jpeg' || $fileExt == '.gif' || $fileExt == '.png') {
					$contentBitMap['threadAttachPic'] = 1;
				}
				if ($fileExt == '.swf' || $fileExt == '.fla') {
					$contentBitMap['threadAttachFlash'] = 1;
				}
				if (in_array($fileExt, $videoExt)) {
					$contentBitMap['threadAttachVideo'] = 1;
				}
				if (in_array($fileExt, $audioExt)) {
					$contentBitMap['threadAttachAudio'] = 1;
				}
			}

			$contentBitMap['threadAttach'] = 1;
		}

		if ($postAttachs) {
			foreach($postAttachs as $attach) {

				$fileExt = substr($attach['filename'], strrpos($attach['filename'], '.'));
				if ($fileExt == '.bmp' || $fileExt == '.jpg' || $fileExt == '.jpeg' || $fileExt == '.gif' || $fileExt == '.png') {
					$contentBitMap['postAttachPic'] = 1;
				}
				if ($fileExt == '.swf' || $fileExt == '.fla') {
					$contentBitMap['postAttachFlash'] = 1;
				}
				if (in_array($fileExt, $videoExt)) {
					$contentBitMap['postAttachVideo'] = 1;
				}
				if (in_array($fileExt, $audioExt)) {
					$contentBitMap['postAttachAudio'] = 1;
				}
			}

			$contentBitMap['postAttach'] = 1;
		}

		if ($thread['authorid'] == $_G['uid']) {
			$threadEmail = $_G['member']['email'];
		} else {
			$threadMember = C::t('common_member')->fetch($thread['authorid'], 0, 1);
			$threadEmail = $threadMember['email'];
		}

		if ($post['authorid'] == $_G['uid']) {
			$postEmail = $_G['member']['email'];
		} else {
			$postEmail = $member['email'];
		}

		if ($thread['special']) {
			if (array_key_exists($thread['special'], $this->specialType)) {
				$threadSpecial = self::$specialType[$thread['special']];
			} else {
				$threadSpecial = 'other';
			}
		}
		$threadSort = 2;
		if ($thread['sortid']) {
			$threadSort = 1;
			if ($post['first']) {
				$sortMessage = $this->_convertSortInfo($thread['sortid'], $thread['tid']);
			}
		}
		$contentBitMap['threadSort'] = $threadSort;
		if ($_GET['action'] == 'newtrade') {
			$type = 'newThread';
			$pid = $firstPost['pid'];
		}

		$batchData[] = array(
						'tId' => $tid,
						'pId' => $pid,
						'threadUid' => dintval($thread['authorid']),
						'threadUsername' => $thread['author'],
						'threadEmail' => $threadEmail,
						'postUid' => dintval($post['authorid']),
						'postUsername' => $post['author'],
						'postEmail' => $postEmail,
						'openId' => $openId,
						'fId' => dintval($fid),
						'threadUrl' => $url,
						'operateTime' => $_G['timestamp'],
						'clientIp' => $_G['clientip'],
						'remoteIp' => $_SERVER['REMOTE_ADDR'],
						'views' => $views,
						'replies' => $replies,
						'favourites' => $favourites,
						'supports' => $supports,
						'opposes' => $opposes,
						'shares' => $shares,
						'title' => $post['subject'],
						'content' => $post['message'],
						'sortMessage' => $sortMessage,
						'attachList' => $postAttachs,
						'reportType' => $type,
						'contentBitMap' => $contentBitMap,
						'userBitMap' => $userBitMap,
						'extra' => $extra,
						'specialType' => $threadSpecial,
						'signature' => $memberField['sightml'],
						'userAgent' => $this->_getUA(),
					);

		$result = false;
		try {
			$result = self::$_secClient->securityReportPost($batchData);
		} catch (Cloud_Service_Client_RestfulException $e) {
			$ids = array($tid, $pid);
			$this->logFailed($type, $ids);
		} catch (Exception $e) {

		}
		$this->benchMarkLog($startTime, $pid, $batchData, $type);

		return $result;
	}

	private function _convertSortInfo($sortId, $tid) {
		global $_G;
		$returnStr = array();
		require_once libfile('function/threadsort');
		$sortInfo = threadsortshow($sortId, $tid);
		foreach ($sortInfo['optionlist'] as $value) {
			if ($value['type'] != 'select') {
				$returnStr[] = $value['title'] . ':' . $value['value'];
			}
		}
		if (count($returnStr)) {
			return implode('<br/>', $returnStr);
		}
		return false;
	}

	public function reportDelPost($logId) {
		if (!self::$_secStatus) {
			return false;
		}

		if (!$logId) {
			return true;
		}
		$log = C::t('#security#security_failedlog')->fetch($logId);
		if ($log['pid'] == 0) {
			return true;
		}

		$extraData = dunserialize($log['extra2']);
		$batchData[] = array(
			'tid' => $log['tid'],
			'pid' => $log['pid'],
			'uid' => $log['uid'],
			'delType' => $log['reporttype'],
			'findEvilTime' => $log['createtime'],
			'postTime' => $log['posttime'],
			'reason' => $log['delreason'],
			'fid' => $extraData['fid'],
			'clientIp' => $extraData['clientIp'],
			'openId' => $extraData['openId'],
		);

		$result = false;
		try {
			$result = self::$_secClient->securityReportDelPost($batchData);
		} catch (Cloud_Service_Client_RestfulException $e) {
			$ids = array($log['tid'], $log['pid']);
			$this->logFailed('delPost', $ids);
		} catch (Exception $e) {

		}
		return $result;
	}

	public function reportBanUser($logId) {
		if (!self::$_secStatus) {
			return false;
		}

		if (!$logId) {
			return true;
		}

		$log = C::t('#security#security_failedlog')->fetch($logId);
		if ($log['uid'] == 0) {
			return true;
		}

		$extraData = dunserialize($log['extra2']);
		$batchData[] = array(
			'uid' => $log['uid'],
			'findEvilTime' => $log['createtime'],
			'postTime' => $log['posttime'],
			'reason' => $log['delreason'],
			'clientIp' => $extraData['clientIp'],
			'openId' => $extraData['openId'],
		);

		$result = false;
		try {
			$result = self::$_secClient->securityReportBanUser($batchData);
		} catch (Cloud_Service_Client_RestfulException $e) {
			$ids = array($log['uid']);
			$this->logFailed('banUser', $ids);
		} catch (Exception $e) {

		}
		return $result;
	}


	public function retryReportData($num = 1) {
		global $_G;
		if (!self::$_secStatus) {
			return false;
		}
		C::t('#security#security_failedlog')->deleteDirtyLog();

		$num = dintval($num) ? dintval($num) : 1;
		$result = 0;
		$clearIds = array();
		$retryData = C::t('#security#security_failedlog')->range(0, $num, 'ASC');

		foreach ($retryData as $data) {
			if (!$data['uid'] && !$data['tid'] && !$data['pid']) {
				continue;
			}
			if ($data['scheduletime'] > $_G['timestamp']) {
				continue;
			}

			$reportType = $data['reporttype'];
			$uid = $data['uid'];
			$tid = $data['tid'];
			$pid = $data['pid'];
			$failcount = $data['failcount'];
			if ($failcount >= self::$retryLimit) {
				$clearIds[] = $data['id'];
			} else {
				switch ($reportType) {
				case 'newThread':
				case 'newPost':
					$result = $this->reportPost('new', $tid, $pid);
					break;
				case 'editPost':
				case 'editThread':
					$result = $this->reportPost('edit', $tid, $pid);
					break;
				case 'register':
					$result = $this->reportRegister($uid);
					break;
				case 'login':
					$result = $this->reportLogin($uid);
					break;
				case 'delThread':
				case 'delPost':
					$result = $this->reportDelPost($data['id']);
					break;
				case 'banUser':
					$result = $this->reportBanUser($data['id']);
					break;
				default:break;
				}
				if ($result) {
					$clearIds[] = $data['id'];
				}
			}
		}
		$this->_clearFailed($clearIds);
	}

	private function _clearFailed($ids) {
		if (!is_array($ids)) {
			$ids = array($ids);
		}
		if (count($ids) < 1) {
			return false;
		}

		C::t('#security#security_failedlog')->delete($ids);
	}

	public function writeLog($id, $type) {

		if (!self::$debug) {
			return false;
		}

		return true;
	}

	function logFailed($reportType, $ids, $reason = '') {
		global $_G;
		if (!self::$_secStatus) {
			return false;
		}
		$this->_checkAndClearFailNum();

		if (!is_array($ids)) {
			$ids = array($ids);
		}
		$postTime = 0;
		if (in_array($reportType, self::$postAction) || in_array($reportType, self::$delPostAction)) {
			$tid = dintval($ids[0]) ? dintval($ids[0]) : dintval($ids['tid']);
			$pid = dintval($ids[1]) ? dintval($ids[1]) : dintval($ids['pid']);
			$uid = dintval($ids['uid']);
			if ($pid == 0) {
				return false;
			}
			if (in_array($reportType, self::$delPostAction)) {
				require_once libfile('function/forum');
				$postInfo = get_post_by_pid($pid);
				$postTime = $postInfo['dateline'];
				$fid = $postInfo['fid'];
				$uid = $postInfo['authorid'];
				$clientIp = $postInfo['useip'];
				$openId = $this->_getOpenId($uid);
			}
			$oldData = C::t('#security#security_failedlog')->fetch_by_pid($pid);
		} elseif (in_array($reportType, self::$userAction) || in_array($reportType, self::$delUserAction)) {
			$tid = 0;
			$pid = 0;
			$uid = dintval($ids[0]) ? dintval($ids[0]) : dintval($ids['uid']);
			if ($uid == 0) {
				return false;
			}
			if (in_array($reportType, self::$delUserAction)) {
				$memberStatus = C::t('common_member_status')->fetch($uid, 0, 1);
				$postTime = $memberStatus['lastpost'];
				$clientIp = $this->_getMemberIp($uid);
				$openId = $this->_getOpenId($uid);
			}
			$oldData = C::t('#security#security_failedlog')->fetch_by_uid($uid);
		} else {
			return false;
		}
		$extraData = array(
			'fid' => $fid,
			'clientIp' => $clientIp,
			'openId' => $openId,
		);

		if (is_array($oldData) && $oldData['id']) {
			$data = $oldData;
			$data['reporttype'] = $reportType;
			$data['lastfailtime'] = $_G['timestamp'];
			$data['scheduletime'] = $_G['timestamp'] + 300;
			$data['failcount']++;
		} else {
			$data = array(
						'reporttype' => $reportType,
						'tid' => $tid,
						'pid' => $pid,
						'uid' => $uid,
						'failcount' => 1,
						'createtime' => $_G['timestamp'],
						'posttime' => $postTime,
						'delreason' => $reason,
						'scheduletime' => $_G['timestamp'] + 60,
						'lastfailtime' => $_G['timestamp'],
					);
		}
		if ($extraData['fid'] || $extraData['clientIp'] || $extraData['openId']) {
			$data['extra2'] = serialize($extraData);
		}

		if (!$data['uid'] && !$data['tid'] && !$data['pid']) {
			return false;
		}
		C::t('#security#security_failedlog')->insert($data, 0, 1);
	}

	private function _getOpenId($uid) {
		$member = C::t('common_member')->fetch($uid, 0, 1);
		if ($member['conisbind']) {
			$connectInfo = C::t('#qqconnect#common_member_connect')->fetch($uid);
			$openId = $connectInfo['conopenid'];
		} else {
			$openId = '';
		}
		return $openId;
	}

	private function _getMemberIp($uid) {
		if (empty($uid)) {
			return false;
		}
		$member = C::t('common_member_status')->fetch($uid, 0, 1);
		if ($member['lastip']) {
			return $member['lastip'];
		} else {
			return $member['regip'];
		}
	}

	private function _checkAndClearFailNum($maxNum = '50000') {
		$num = C::t('#security#security_failedlog')->count();
		if ($num >= $maxNum) {
			C::t('#security#security_failedlog')->truncate();
		}
		return true;
	}

	public function logDeletePost($pids, $reason = 'Admin Delete') {
		if (!is_array($pids)) {
			$pids = array($pids);
		}
		$logData = array();
		require_once libfile('function/forum');

		foreach ($pids as $pid) {
			$postInfo = get_post_by_pid($pid);
			if ($postInfo['invisible'] != '-5') {
				$logData[] = array(
					'tid' => $postInfo['tid'],
					'pid' => $postInfo['pid'],
					'fid' => $postInfo['fid'],
					'uid' => $postInfo['authorid'],
					'clientIp' => $postInfo['useip'],
					'openId' => $this->_getOpenId($postInfo['authorid']),
				);
			}
		}

		if (count($logData)) {
			foreach ($logData as $data) {
				$this->logFailed('delPost', $data, $reason);
			}
		}
	}


	public function logBannedMember($username, $reason = 'Admin Banned') {
		if (!$username || !self::$_secStatus) {
			return false;
		}
		$uid = C::t('common_member')->fetch_uid_by_username($username);
		if ($uid) {
			$data = array(
				'uid' => $uid,
				'openId' => $this->_getOpenId($uid),
				'clientIp' => $this->_getMemberIp($uid),
			);
			$this->logFailed('banUser', $data, $reason);
		}
	}

	public function logDeleteThread($tids, $reason = 'Admin Delete') {
		global $_G;
		if (!is_array($tids)) {
			$tids = array($tids);
		}

		$postids = array();
		$logData = array();
		loadcache(array('threadtableids', 'posttableids'));
		$threadtableids = !empty($_G['cache']['threadtableids']) ? $_G['cache']['threadtableids'] : array();
		$posttableids = !empty($_G['cache']['posttableids']) ? $_G['cache']['posttableids'] : array();
		$threadtableids = array_unique(array_merge(array('0'), $threadtableids));
		$posttableids = array_unique(array_merge(array('0'), $threadtableids));

		foreach($threadtableids as $tableid) {
			$threads = C::t('forum_thread')->fetch_all_by_tid($tids, 0, 0, $tableid);
			if (count($threads)) {
				foreach ($threads as $tid => $thread) {
					if ($thread['displayorder'] == '-1') {
						unset($threads[$tid]);
					}
				}
				$postids[$tableid] = array_keys($threads);
			}
		}

		foreach ($posttableids as $postTableId) {
			if (count($postids[$postTableId])) {
				$posts = C::t('forum_post')->fetch_all_by_tid($postTableId, $tids, 1, '', 0, 0, 1);
				foreach ($posts as $data) {
					$logData[] = array(
						'tid' => $data['tid'],
						'pid' => $data['pid'],
						'fid' => $data['fid'],
						'uid' => $data['authorid'],
						'clientIp' => $data['useip'],
						'openId' => $this->_getOpenId($data['authorid']),
					);
				}
			}
		}

		if (count($logData)) {
			foreach ($logData as $data) {
				$this->logFailed('delThread', $data, $reason);
			}
		}
	}

	public function getOperateData($type, $limit = 20) {
		if (!self::$_secStatus) {
			return false;
		}

		$allowType = array('post', 'user', 'member');
		$operateData = array();
		$operateResultData = array();
		if ($type == 'member') {
			$type = 'user';
		}
		if (!in_array($type, $allowType)) {
			return false;
		}
		$tableName = '#security#security_evil' . $type;

		$operateData = C::t($tableName)->fetch_all_report($limit);

		foreach($operateData as $tempData) {
			$operateResult = $tempData['operateresult'] == 1 ? 'recover' : 'delete';
			if ($type == 'post') {
				require_once libfile('function/forum');
				$detailData = get_post_by_pid($tempData['pid']);

				$detailData['pid'] = $tempData['pid'];
				$detailData['tid'] = $tempData['tid'];
				$detailData['uid'] = $id = $tempData['pid'];
			} elseif ($type == 'user') {
				$detailData = C::t('common_member')->fetch($tempData['uid'], 0, 1);
				$detailData['uid'] = $id = $tempData['uid'];
			}
			if ($type == 'post') {
				$operateType = $detailData['first'] ? 'thread' : 'post';
			} elseif ($type == 'user') {
				$operateType = 'member';
			}
			$data = array(
				'tid' => $detailData['tid'] ? $detailData['tid'] : 0,
				'pid' => $detailData['pid'] ? $detailData['pid'] : 0,
				'fid' => $detailData['fid'] ? $detailData['fid'] : 0,
				'operateType' => $operateType,
				'operate' => $operateResult,
				'operateId' => $id,
				'uid' => $detailData['authorid'] ? $detailData['authorid'] : $detailData['uid'],
			);
			$data['openId'] = $this->_getOpenId($data['uid']);
			$data['clientIp'] = $detailData['useip'] ? $detailData['useip'] : $this->_getMemberIp($data['uid']);
			$operateResultData[] = $data;
		}
		return $operateResultData;
	}

	public function markasreported($operateType, $operateData) {
		if (!self::$_secStatus) {
			return false;
		}

		foreach ($operateData as $data) {
			$operateId[] = $data['operateId'];
		}
		if (!is_array($operateId) || !count($operateId)) {
			return false;
		}

		if ($operateType == 'member') {
			C::t('#security#security_eviluser')->update($operateId, array('isreported' => 1));
		} elseif(in_array($operateType, array('thread', 'post'))) {
			C::t('#security#security_evilpost')->update($operateId, array('isreported' => 1));
		}
		return true;
	}

	public function updatePostOperate($ids, $operate) {
		if (!self::$_secStatus) {
			return false;
		}

		$result = $this->_getOperateResult($operate);
		C::t('#security#security_evilpost')->update($ids, array('operateresult' => $result));
		return true;
	}

	public function updateThreadOperate($ids, $operate) {
		if (!self::$_secStatus) {
			return false;
		}

		$result = $this->_getOperateResult($operate);
		C::t('#security#security_evilpost')->update_by_tid($ids, array('operateresult' => $result));
		return true;
	}

	public function updateMemberOperate($ids, $operate) {
		if (!self::$_secStatus) {
			return false;
		}

		$result = $this->_getOperateResult($operate);
		C::t('#security#security_eviluser')->update($ids, array('operateresult' => $result));
		return true;
	}

	public function updateMemberRecover($username) {
		if (!$username || !self::$_secStatus) {
			return false;
		}

		$uid = C::t('common_member')->fetch_uid_by_username($username);
		$this->updateMemberOperate(array($uid), 'recover');
		return true;
	}

	private function _getOperateResult($str) {
		$mapArray = array(
			'recover' => 1,
			'delete' => 2,
		);

		return $mapArray[$str];
	}

	private function benchMarkLog($startTime, $id, $data, $type) {
		return true;
		$util = Cloud::loadClass('Service_Util');
		$endTime = microtime(true);
		$dataSize = strlen($util->httpBuildQuery($data));
		$content = array(
			date('Y-m-d H:i:s', $startTime),
			$endTime - $startTime,
			$type,
			$id,
			$dataSize,
		);
		$content = join(',', $content) . "\n";
	}
}