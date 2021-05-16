<?php

/**
 *		[Discuz! X] (C)2001-2099 Comsenz Inc.
 *		This is NOT a freeware, use is subject to license terms
 *
 *		$Id: security.class.php 33945 2013-09-05 01:48:02Z nemohou $
 */


if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class plugin_security {
	const DEBUG = 0;
	protected static $postReportAction = array('post_newthread_succeed', 'post_edit_succeed', 'post_reply_succeed',
							'post_newthread_mod_succeed', 'post_newthread_mod_succeed', 'post_reply_mod_succeed',
							'edit_reply_mod_succeed', 'edit_newthread_mod_succeed');
	protected static $userReportAction = array('login_succeed', 'register_succeed', 'location_login_succeed_mobile',
							'location_login_succeed', 'register_succeed_location', 'register_email_verify',
							'register_manual_verify', 'login_succeed_inactive_member');
	protected static $hookMoudle = array('post', 'logging', 'register');
	protected static $isAdminGroup = 0;
	protected static $cloudAppService;
	protected static $securityService;
	protected static $securityStatus;

	public function __construct() {
		self::$cloudAppService = Cloud::loadClass('Service_App');
		self::$securityStatus = self::$cloudAppService->getCloudAppStatus('security');
		self::$securityService = Cloud::loadClass('Service_Security');
	}

	public function common() {
		global $_G;
		if (self::$securityStatus != TRUE) {
			return false;
		}
		if ($_G['uid']) {
			$lastCookieReportTime = $this->_decodeReportTime($_G['cookie']['security_cookiereport']);
			if ($lastCookieReportTime < strtotime('today')) {
				$this->_reportLoginUser(array('uid' => $_G['uid']));
			}
		}

		if ($_G['adminid'] > 0) {
			self::$isAdminGroup = 1;
		}

		return true;
	}

	public function global_footer() {
		global $_G, $_GET;
		if (self::$securityStatus != TRUE) {
			return false;
		}

		$ajaxReportScript = '';
		$formhash = formhash();
		if($_G['member']['allowadmincp'] == 1) {
			$processName = 'securityOperate';
			if (self::$isAdminGroup && !discuz_process::islocked($processName, 30)) {
				$ajaxReportScript .= <<<EOF
					<script type='text/javascript'>
					var url = SITEURL + '/plugin.php?id=security:sitemaster';
					var x = new Ajax();
					x.post(url, 'formhash=$formhash', function(s){});
					</script>
EOF;
			}
			$processName = 'securityNotice';
			if (self::$isAdminGroup && !discuz_process::islocked($processName, 30)) {
				$ajaxReportScript .= <<<EOF
					<div class="focus plugin" id="evil_notice"></div>
					<script type='text/javascript'>
					var url = SITEURL + '/plugin.php?id=security:evilnotice&formhash=$formhash';
					ajaxget(url, 'evil_notice', '');
					</script>
EOF;
			}
		}

		$processName = 'securityRetry';
		$time = 10;
		if (!discuz_process::islocked($processName, $time)) {
			if (C::t('#security#security_failedlog')->count()) {
				$ajaxRetryScript = <<<EOF
					<script type='text/javascript'>
					var urlRetry = SITEURL + '/plugin.php?id=security:job';
					var ajaxRetry = new Ajax();
					ajaxRetry.post(urlRetry, 'formhash=$formhash', function(s){});
					</script>
EOF;
			}
		}

		return $ajaxReportScript . $ajaxRetryScript;
	}

	function global_footerlink() {
		return '&nbsp;<a href="http://discuz.qq.com/service/security" target="_blank" title="'.lang('plugin/security', 'title').'"><img src="static/image/common/security.png"></a>';
	}

	public function deletepost($param) {
		global $_G, $_POST;
		if (self::$securityStatus != TRUE) {
			return false;
		}

		$step = $param['step'];
		$param = $param['param'];
		$ids = $param[0];
		$idType = $param[1];
		$recycle = $param[4];

		if ($step == 'check' && $idType == 'pid') {
			self::$securityService->updatePostOperate($ids, 'delete');
			if ($_POST['module'] == 'security' && $_POST['method'] == 'setEvilPost') {
				return true;
			}
			self::$securityService->logDeletePost($ids, $_POST['reason']);
		}

		return true;
	}

	public function deletethread($param) {
		global $_G, $_POST;
		if (self::$securityStatus != TRUE) {
			return false;
		}

		$step = $param['step'];
		$param = $param['param'];
		$ids = $param[0];

		if ($step == 'check') {
			self::$securityService->updateThreadOperate($ids, 'delete');
			if ($_POST['module'] == 'security' && $_POST['method'] == 'setEvilPost') {
				return true;
			}
			self::$securityService->logDeleteThread($ids, $_POST['reason']);
		}

		return true;
	}

	public function savebanlog($param) {
		global $_G, $_POST;
		if (self::$securityStatus != TRUE) {
			return false;
		}
		$param = $param['param'];
		$username = $param[0];
		$oldGid = $param[1];
		$newGid = $param[2];
		$reason = $param[4];
		if ($_POST['formhash'] && $newGid >= 4 && $newGid < 10) {
			self::$securityService->logBannedMember($username, $reason);
		} else {
			self::$securityService->updateMemberRecover($username);
		}
	}

	public function undeletethreads($param) {
		$tids = $param['param'][0];
		if ($tids && is_array($tids)) {
			self::$securityService->updateThreadOperate($tids, 'recover');
		}
	}

	public function recyclebinpostundelete ($param) {
		$pids = $param['param'][0];
		if ($pids && is_array($pids)) {
			self::$securityService->updatePostOperate($pids, 'recover');
		}
	}

	public function deletemember($param) {
		$uids = $param['param'][0];
		$step = $param['step'];
		if ($step == 'check' && $uids && is_array($uids)) {
			self::$securityService->updateMemberOperate($uids, 'delete');
		}
	}

	protected function _decodeReportTime($time) {
		if (!$time) {
			return 0;
		}
		return authcode($time);
	}

	protected function _encodeReportTime($time) {
		if (!$time) {
			return 0;
		}
		return authcode($time, 'ENCODE');
	}

	protected function _reportRegisterUser($param) {
		global $_G;
		if (!$param['uid'] && !$_G['uid']) {
			return false;
		} else {
			$param['uid'] = $_G['uid'];
		}
		$this->secLog('USERREG-UID', $param['uid']);

		self::$securityService->reportRegister($param['uid']);
		$this->_retryReport();
	}

	protected function _reportLoginUser($param) {
		global $_G;

		if (!$param['uid'] && !$_G['uid']) {
			return false;
		} else {
			$param['uid'] = $_G['uid'];
		}
		$this->secLog('USERLOG-UID', $param['uid']);
		self::$securityService->reportLogin($param['uid']);
		$this->_retryReport();
		$cookieTime = 43200;
		dsetcookie('security_cookiereport', $this->_encodeReportTime($_G['timestamp']), $cookieTime, 1);
		return true;
	}

	protected function _reportMobileLoginUser($param) {
		if (!$param['username']) {
			return false;
		}
		$username = $param['username'];
		$result = C::t('common_member')->fetch_by_username($username);
		return $this->_reportLoginUser($result);
	}

	protected function _reportNewThread($param) {
		global $_G;
		if (!$param['pid'] || !$param['tid']) {
			return false;
		}
		$this->secLog('NEWTHREAD-TID', $param['tid']);
		$tid = $param['tid'];
		$pid = $param['pid'];

		self::$securityService->reportPost('new', $tid, $pid, $extra, $param['isFollow']);
		$this->_retryReport();
		return true;
	}

	protected function _reportNewPost($param) {
		global $_G;
		if (!$param['pid'] || !$param['tid']) {
			return false;
		}
		$this->secLog('NEWPOST-PID', $param['pid']);
		$tid = $param['tid'];
		$pid = $param['pid'];

		self::$securityService->reportPost('new', $tid, $pid, $extra, $param['isFollow']);
		$this->_retryReport();
		return true;
	}

	protected function _reportEditPost($param) {
		global $_G;
		if (!$param['pid'] || !$param['tid']) {
			return false;
		}
		$this->secLog('EDITPOST-PID', $param['pid']);
		$tid = $param['tid'];
		$pid = $param['pid'];

		self::$securityService->reportPost('edit', $tid, $pid, $extra, $param['isFollow']);
		$this->_retryReport();
		return true;
	}

	protected function _retryReport() {
		return self::$securityService->retryReportData();
	}

	public function secLog($type, $data) {
		global $_G;
		if (self::$securityStatus != TRUE) {
			return false;
		}

		if (!self::DEBUG) {
			return false;
		}
	}

	public function getMergeAction() {
		return array_merge(self::$postReportAction, self::$userReportAction);
	}
}

class plugin_security_forum extends plugin_security {

	public function post_security(){
		return true;
	}

	public function post_report_message($param) {
		global $_G, $extra, $redirecturl;
		if (self::$securityStatus != TRUE) {
			return false;
		}

		$param['message'] = $param['param'][0];
		$param['values'] = $param['param'][2];
		if (in_array($param['message'], self::$postReportAction)) {
			switch ($param['message']) {
				case 'post_newthread_succeed':
				case 'post_newthread_mod_succeed':
					$this->_reportNewThread($param['values']);
					break;
				case 'post_edit_succeed':
				case 'edit_reply_mod_succeed':
				case 'edit_newthread_mod_succeed':
					$this->_reportEditPost($param['values']);
					break;
				case 'post_reply_succeed':
				case 'post_reply_mod_succeed':
					$this->_reportNewPost($param['values']);
				default:break;
			}
		}
	}
}

class plugin_security_group extends plugin_security_forum {}
class plugin_security_home extends plugin_security_forum {

	public function spacecp_follow_report_message($param) {
		global $_G, $extra, $redirecturl;
		if (self::$securityStatus != TRUE) {
			return false;
		}

		$param['message'] = $param['param'][0];
		$param['values'] = $param['param'][2];
		$param['values']['isFollow'] = 1;
		if (in_array($param['message'], self::$postReportAction)) {
			switch ($param['message']) {
				case 'post_newthread_succeed':
				case 'post_newthread_mod_succeed':
					$this->_reportNewThread($param['values']);
					break;
				case 'post_edit_succeed':
				case 'edit_reply_mod_succeed':
				case 'edit_newthread_mod_succeed':
					$this->_reportEditPost($param['values']);
					break;
				case 'post_reply_succeed':
				case 'post_reply_mod_succeed':
					$this->_reportNewPost($param['values']);
				default:break;
			}
		}
	}
}

class plugin_security_member extends plugin_security {

	public function logging_report_message($param) {
		global $_G;

		if (self::$securityStatus != TRUE) {
			return false;
		}

		$param['message'] = $param['param'][0];
		$param['values'] = $param['param'][2];
		if (in_array($param['message'], self::$userReportAction)) {
			if (!$param['values']['uid']) {
				$this->_reportLoginUser($param['values']);
			} else {
				$this->_reportMobileLoginUser($param['values']);
			}
		}
	}

	public function register_report_message($param) {
		if (self::$securityStatus != TRUE) {
			return false;
		}

		$param['message'] = $param['param'][0];
		$param['values'] = $param['param'][2];

		if (in_array($param['message'], self::$userReportAction)) {
			$this->_reportRegisterUser($param['values']);
		}
	}
	public function connect_report_message($param) {
		global $_G;
		if (self::$securityStatus != TRUE) {
			return false;
		}

		$param['message'] = $param['param'][0];
		$param['values'] = $param['param'][2];
		if (($_POST['regsubmit'] || $_POST['loginsubmit']) && $_POST['formhash']) {
			if ($_POST['loginsubmit']) {
				$this->_reportLoginUser($_G['member']);
			} else {
				$this->_reportRegisterUser($param['values']);
			}
		}
	}
}

class mobileplugin_security extends plugin_security {}

class mobileplugin_security_forum extends plugin_security_forum {}

class mobileplugin_security_member extends plugin_security_member {}

class plugin_security_connect extends plugin_security_member {

	public function login_report_message($param) {
		if (self::$securityStatus != TRUE) {
			return false;
		}

		$param['message'] = $param['param'][0];
		$param['values'] = $param['param'][2];
		if (in_array($param['message'], self::$userReportAction)) {
			switch ($param['message']) {
				case login_succeed:
				case location_login_succeed:
				case location_login_succeed_mobile:
					$this->_reportMobileLoginUser($param['values']);
				default:break;
			}
		}
	}
}