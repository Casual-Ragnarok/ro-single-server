<?php

/**
 *		[Discuz!] (C)2001-2099 Comsenz Inc.
 *		This is NOT a freeware, use is subject to license terms
 *
 *		$Id: xf_storage.class.php 29353 2012-04-06 03:00:07Z liudongdong $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class plugin_xf_storage {

	protected $value = array();
	protected $appStatus = '';
	protected $jsCode = '';

	public function plugin_xf_storage() {
		global $_G;
		$cloudAppService = Cloud::loadClass('Service_App');
		$this->appStatus = $cloudAppService->getCloudAppStatus('storage');
	}

	public function common(){
		global $_G;
		if (!$this->appStatus) {
			return false;
		}
		if(CURMODULE == 'post' && CURSCRIPT == 'forum' && $_G['uid']){
			$_G['config']['output']['iecompatible'] = '7';
		}

		return true;
	}

	public function global_footer(){
		if (!$this->appStatus) {
		   return false;
		}
		include template('xf_storage:css');

		return $return;
	}

	public function _output($aid, $sha, $filename) {
		include_once libfile('function/attachment');
		$storageService = Cloud::loadClass('Service_Storage');

		$qqdlUrl = $storageService->makeQQdlUrl($sha, $filename);
		$aidencode = packaids(array('aid' => $aid));
		include template('xf_storage:link');

		return $return;
	}

	public function _jsOutput($aid, $return) {
		$spanId = 'attach_' . $aid;
		$return = str_replace(array("\r\n", '\'', '<p class="xg2">', '</p>', "\n"), array('', '\\\''), $return);
		include template('xf_storage:jscode');

		return $jscode;
	}

}

class plugin_xf_storage_forum extends plugin_xf_storage {

	public function post_attach_btn_extra() {
		global $_G;
		if (!$this->appStatus) {
			return false;
		}
		include template('xf_storage:editor');

		return $return;
	}

	public function post_attach_tab_extra() {
		global $_G;
		if (!$this->appStatus) {
			return false;
		}
		$editorid = 'e';
		include template('xf_storage:ftnupload');

		return $return;
	}

	public function viewthread_attach_extra_output() {
		global $postlist, $_G;
		$return = array();
		foreach ($postlist as $pid => $post) {
			foreach ($post['attachments'] as $aid => $attachment) {
				if (strpos($attachment['attachment'], 'storage:') !== false) {
					$sha1 = substr($attachment['attachment'], -40);
					$return[$aid] = $this->_output($aid, $sha1, $attachment['filename']);
					if (in_array($aid, $_G['forum_attachtags'][$pid])) {
						$postlist[$pid]['message'] .= $this->_jsOutput($aid, $return[$aid]);
						unset($return[$aid]);
					}
					if ($attachment['isimage']) {
						unset($return[$aid]);
					}
				}
			}
		}
		return $return;
	}
}