<?php

/**
 *		[Discuz!] (C)2001-2099 Comsenz Inc.
 *		This is NOT a freeware, use is subject to license terms
 *
 *		$Id: Disk.php 30715 2012-06-14 03:02:00Z songlixin $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class Cloud_Service_Disk {

	protected $_util;

	protected $_client;

	protected $_baseUrl;

	protected static $_instance;

	public static function getInstance() {
		global $_G;

		if (!(self::$_instance instanceof self)) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	public function __construct() {
		global $_G;

		$this->_util = Cloud::loadClass('Service_Util');
		$this->_client = Cloud::loadClass('Service_Client_Disk');
		$this->_baseUrl = $_G['siteurl'] . 'apptest.php?';
	}

	public function saveTask($attach, $openId) {
		global $_G;

		if (!$_G['uid']) {
			throw new Cloud_Exception('noLogin', '30001');
		}
		if (!$openId) {
			throw new Cloud_Exception('noopenId', '30002');
		}
		$verifyCode = md5($openId . $attach['aid'] . $_G['timestamp'] . $_G['uid']);
		$taskData = array(
			'aid' => $attach['aid'],
			'uid' => $_G['uid'],
			'openId' => $openId,
			'filename' => $attach['filename'],
			'verifycode' => $verifyCode,
			'status' => 0,
			'dateline' => $_G['timestamp'],
		);
		$taskId = C::t('#qqconnect#connect_disktask')->insert($taskData, true);

		$downParams = array(
			'taskid' => $taskId,
			'verifycode' => $verifyCode,
		);
		$downloadUrl = $this->_baseUrl . $this->_util->generateSiteSignUrl($downParams);
		$filePath = self::_getFullPath($attach['attachment']);
		if ($attach['filesize'] <= 10000000 && file_exists($filePath)) {
			$md5 = md5_file($filePath);
			$hash = hash_file('sha1', $filePath);
		}
		$cloudData = array(
			'sId' => $_G['setting']['my_siteid'],
			'openId' => $openId,
			'batchTasks' => array(
				array(
					'aId' => $attach['aid'],
					'fileName' => $attach['filename'],
					'downloadUrl' => $downloadUrl,
					'size' => filesize($filePath),
					'md5' => $md5,
					'hash' => $hash,
				),
			),
			'clientIp' => $_G['clientip'],
		);
		return $this->_client->sendTask($cloudData);
	}

	public function getAttachment() {
		global $_G;

		try {
			$taskData = $this->checkTask();
		} catch (Exception $e) {
			throw new Cloud_Exception($e);
		}
		$task = $taskData['task'];
		$attach = $taskData['attach'];
		$taskId = $task['taskid'];

		C::t('#qqconnect#connect_disktask')->update($taskId, array(
			'status' => 1,
			'downloadtime' => $_G['timestamp'],
		));
		$db = DB::object();
		$db->close();
		ob_end_clean();
		$attach['filename'] = '"'.(strtolower(CHARSET) == 'utf-8' && strexists($_SERVER['HTTP_USER_AGENT'], 'MSIE') ? urlencode($attach['filename']) : $attach['filename']).'"';
		dheader('Content-Type: application/octet-stream');
		dheader('Content-Length: ' . $attach['filesize']);
		dheader('Content-Disposition: attachment; filename='.$attach['filename']);

		self::_checkXSendFile($attach['attachment']);
		if ($attach['remote']) {
			self::_getRemoteFile($attach['attachment']);
		} else {
			self::_getLocalFile($attach['attachment']);
		}
		exit;
	}

	public function checkTask() {
		global $_G;
		$sId = $_G['setting']['my_siteid'];
		$ts = $_GET['ts'];
		$taskId = $_GET['taskid'];
		$verifyCode = $_GET['verifycode'];
		if ($sId != $_GET['s_id']) {
			throw new Cloud_Exception('sIdError', '30004');
		}
		if ($_G['timestamp'] - $ts > 86400) {
			throw new Cloud_Exception('downloadTimeOut', '30005');
		}
		$params = array(
			'taskid' => $taskId,
			'verifycode' => $verifyCode,
			's_id' => $_GET['s_id'],
			's_site_uid' => $_GET['s_site_uid'],
		);
		$sig = $_GET['sig'];
		$sKey = $_G['setting']['my_sitekey'];
		ksort($params);
		$str = $this->_util->httpBuildQuery($params, '', '&');
		if ($sig != md5(sprintf('%s|%s|%s', $str, $sKey, $ts))) {
			throw new Cloud_Exception('sigError', '30003');
		}

		$task = C::t('#qqconnect#connect_disktask')->fetch($taskId);
		if (!$task) {
			throw new Cloud_Exception('noTask', '30006');
		}
		if ($verifyCode != $task['verifycode']) {
			throw new Cloud_Exception('verifyError', '30009');
		}

		$attach = C::t('forum_attachment_n')->fetch('aid:' . $task['aid'], $task['aid']);
		if (!$attach) {
			throw new Cloud_Exception('noAttachment', '30007');
		}
		$return = array(
			'task' => $task,
			'attach' => $attach,
		);

		return $return;
	}

	public function clearDirtyData() {
		return C::t('#qqconnect#connect_disktask')->delete_by_status(1);
	}

	private static function _getFullPath($attachment) {
		global $_G;

		return $_G['setting']['attachdir'] . 'forum/' . $attachment;
	}

	private static function _getLocalFile($file) {
		$filename = self::_getFullPath($file);
		$readmod = getglobal('config/download/readmod');
		$readmod = $readmod > 0 && $readmod < 5 ? $readmod : 2;
		if($readmod == 1 || $readmod == 3 || $readmod == 4) {
			if($fp = @fopen($filename, 'rb')) {
				if(function_exists('fpassthru') && ($readmod == 3 || $readmod == 4)) {
					@fpassthru($fp);
				} else {
					echo @fread($fp, filesize($filename));
				}
			}
			@fclose($fp);
		} else {
			@readfile($filename);
		}
		@flush(); @ob_flush();
	}

	private static function _getRemoteFile($file) {
		global $_G;

		@set_time_limit(0);
		if(!@readfile($_G['setting']['ftp']['attachurl'] . 'forum/' . $file)) {
			$ftp = ftpcmd('object');
			$tmpfile = @tempnam($_G['setting']['attachdir'], '');
			if($ftp->ftp_get($tmpfile, 'forum/'.$file, FTP_BINARY)) {
				@readfile($tmpfile);
				@unlink($tmpfile);
			} else {
				@unlink($tmpfile);
				return FALSE;
			}
		}

		return TRUE;
	}

	private static function _checkXSendFile($file) {
		$filename = self::_getFullPath($file);
		$xsendfile = getglobal('config/download/xsendfile');
		if(!empty($xsendfile)) {
			$type = intval($xsendfile['type']);
			$cmd = '';
			switch ($type) {
				case 1:
					$cmd = 'X-Accel-Redirect';
					$url = $xsendfile['dir'] . $file;
					break;
				case 2:
					$cmd = $_SERVER['SERVER_SOFTWARE'] <'lighttpd/1.5' ? 'X-LIGHTTPD-send-file' : 'X-Sendfile';
					$url = $filename;
					break;
				case 3:
					$cmd = 'X-Sendfile';
					$url = $filename;
					break;
			}
			if($cmd) {
				dheader("$cmd: $url");
				exit();
			}
		}
	}
}