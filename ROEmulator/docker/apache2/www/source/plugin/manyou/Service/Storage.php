<?php

/**
 *		[Discuz!] (C)2001-2099 Comsenz Inc.
 *		This is NOT a freeware, use is subject to license terms
 *
 *		$Id: Storage.php 29263 2012-03-31 05:45:08Z yexinhao $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class Cloud_Service_Storage {
	protected static $debug = 0;
	protected static $_appStatus;
	protected static $_siteId;
	protected static $_encKey;
	protected static $_util;

	protected static $_instance;

	public static function getInstance() {
		global $_G;

		if (!(self::$_instance instanceof self)) {
			self::$_instance = new self();
			$cloudAppService = Cloud::loadClass('Service_App');
			self::$_appStatus = $cloudAppService->getCloudAppStatus('storage');
			self::$_siteId = $_G['setting']['my_siteid'];
			self::$_encKey = $_G['setting']['xf_storage_enc_key'];
			self::$_util = Cloud::loadClass('Service_Util');
		}

		return self::$_instance;
	}

	public function ftnFormhash($specialadd = '') {
		global $_G;

		return substr(md5(substr($_G['timestamp'], 0, -4) . $_G['username'] . $_G['uid'] . $_G['authkey'] . self::$_encKey . $specialadd), 8, 8);
	}

	public function makeFtnSig($formhash) {
		global $_G;

		$openId = $this->getOpenId($_G['uid']);
		$signGetx = array(
					's_id' => self::$_siteId,
					's_site_uid' => $_G['uid'],
					'ts' => $_G['timestamp'],
					'discuz_form_hash' => $formhash,
					'site_url' => $_G['siteurl'],
					'discuz_openid' => $openId,
				);
		ksort($signGetx);

		return self::$_util->hashHmac('sha1', self::$_util->httpBuildQuery($signGetx, '', '&'), self::$_encKey);
	}

	public function makeIframeUrl($formhash) {
		global $_G;

		$openId = $this->getOpenId($_G['uid']);
		$ftnGetx = array(
					's_id' => self::$_siteId,
					's_site_uid' => $_G['uid'],
					'ts' => $_G['timestamp'],
					'discuz_form_hash' => $formhash,
					'site_url' => $_G['siteurl'],
					'discuz_openid' => $openId,
				);
		$url = "http://cp.discuz.qq.com/storage/FTN?" . self::$_util->httpBuildQuery($ftnGetx, '', '&');
		$url = $url.'&sign=' . $this->makeFtnSig($formhash);

		return $url;
	}

	public function makeQQdlUrl($sha, $filename) {
		global $_G;

		$filename = trim($filename);
		$filename = urlencode(diconv($filename,CHARSET,'UTF-8'));
		$url = $_G['siteurl'] . $filename . '?&&txf_fid=' . $sha . '&siteid=' . self::$_siteId;

		return 'qqdl://'.base64_encode($url);
	}

	public function makeDownloadurl($sha1, $filesize, $filename) {
		global $_G;

		$filename = trim($filename,' "'); // Discuz! 默认的filename两侧会加上 双引号
		$filename = diconv($filename,CHARSET,'UTF-8');
		$filename = $this->str2hex($filename);

		$filename = strtolower($filename[1]);
		$post = 'http://dz.xf.qq.com/ftn.php?v=1&&';

		$k = self::$_util->hashHmac('sha1', sprintf('%s|%s|%s', $sha1, $_G['timestamp'], self::$_siteId), self::$_encKey);

		$parm = array(
			'site_id' => self::$_siteId,
			't' => $_G['timestamp'],
			'sha1' => $sha1,
			'filesize' => $filesize,
			'filename' => $filename,
			'k' => $k,
			'ip' => $_G['clientip']
		);

		return $post . self::$_util->httpBuildQuery($parm, '', '&&');
	}


	private function _joinParm($parm = array(),$joiner = '&'){
		$andflag = '';
		$return = '';
		foreach($parm as $key => $value){
			$value = urlencode($value);
			$return .= $andflag.$key.'='.$value;
			$andflag = $joiner;
		}
		return $return;
	}

	public function str2hex($str){
		$length = strlen($str)*2;
		return unpack('H' . $length, $str);
	}

	public function getOpenId($uid) {
		$member = C::t('common_member')->fetch($uid);
		if ($member['conisbind']) {
			$connectInfo = C::t('#qqconnect#common_member_connect')->fetch($uid);
			$openId = $connectInfo['conopenid'];
		} else {
			$openId = '';
		}
		return $openId;
	}

	public function checkAttachment($attach, $redirect = true) {
		if (strpos($attach['attachment'], 'storage:') !== false) {
			C::t('forum_attachment')->update_download($attach['aid']);
			$sha1 = substr($attach['attachment'], -40);
			$downloadUrl = $this->makeDownloadurl($sha1, $attach['filesize'], $attach['filename']);
			if ($redirect) {
				dheader('location:' . $downloadUrl);
				exit;
			} else {
				return $downloadUrl;
			}
		}
	}

	public function getAttachmentByPids($pids) {
		global $_G;
		if (!is_array($pids)) {
			$pids = explode(',', $pids);
		}
		include_once libfile('function/post');
		$attachment = array();

		foreach ($pids as $pid) {
			$data = getattach($pid);
			$attachment[] = $data['used'];
		}

		return $attachment;
	}
}