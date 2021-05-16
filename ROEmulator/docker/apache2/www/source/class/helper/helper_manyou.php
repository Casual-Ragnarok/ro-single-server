<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: helper_manyou.php 27449 2012-02-01 05:32:35Z zhangguosheng $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class helper_manyou {

	public static function manyoulog($logtype, $uids, $action, $fid = '') {
		global $_G;

		if($_G['setting']['my_app_status'] && $logtype == 'user') {
			$action = daddslashes($action);
			$values = array();
			$uids = is_array($uids) ? $uids : array($uids);
			foreach($uids as $uid) {
				$uid = intval($uid);
				C::t('common_member_log')->insert(array('uid' => $uid, 'action' => $action, 'dateline' => TIMESTAMP), false, true);
			}
		}
	}

	public static function getuserapp($panel = 0) {
		require_once libfile('function/manyou');
		manyou_getuserapp($panel);
		return true;
	}

	public static function getmyappiconpath($appid, $iconstatus=0) {
		if($iconstatus > 0) {
			return getglobal('setting/attachurl').'./'.'myapp/icon/'.$appid.'.jpg';
		}
		return 'http://appicon.manyou.com/icons/'.$appid;
	}

	public static function checkupdate() {
		global $_G;
		if($_G['setting']['my_app_status'] && empty($_G['setting']['my_closecheckupdate']) && $_G['group']['radminid'] == 1) {
			$sid = $_G['setting']['my_siteid'];
			$ts = $_G['timestamp'];
			$key = md5($sid.$ts.$_G['setting']['my_sitekey']);
			echo '<script type="text/javascript" src="http://notice.uchome.manyou.com/notice?sId='.$sid.'&ts='.$ts.'&key='.$key.'" charset="UTF-8"></script>';
		}
	}

}

?>