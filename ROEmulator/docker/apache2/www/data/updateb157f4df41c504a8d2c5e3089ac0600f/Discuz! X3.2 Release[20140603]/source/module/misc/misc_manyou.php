<?php
/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: misc_manyou.php 33253 2013-05-10 01:29:32Z andyzheng $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}
if($_GET['action'] == 'inviteCode') {
	$my_register_url = 'http://api.manyou.com/uchome.php';
	$response = dfsockopen($my_register_url, 0, 'action=inviteCode&app=search');
	showmessage($response, '', array(), array('msgtype' => 3, 'handle' => false));
}elseif($_GET['action'] == 'menu') {
	$list = array();
	$menu = C::t('common_setting')->fetch('appmenu');
	$renew = false;
	if($menu) {
		$list = unserialize($menu);
	}
	$today = strtotime(dgmdate(TIMESTAMP, 'Y-m-d'));
	if(!isset($list['dateline']) || $list['dateline'] < $today) {
		$userApp = Cloud::loadClass('Service_Client_Manyou');
		$list = $userApp->getMenuApps();
	}
	$usedList = array();
	if(!empty($_G['cookie']['usedapp']) && $_G['uid']) {
		$usedInfo = explode('|', $_G['cookie']['usedapp']);
		if($usedInfo[0] == $_G['uid']) {
			$appids = explode(',', $usedInfo[1]);
			if($appids) {
				$usedList = C::t('home_userapp')->fetch_all_by_uid_appid($_G['uid'], $appids);
				$usedAppId = array();
				foreach($usedList as $key => $app) {
					if(isset($list[1][$app['appid']])) {
						unset($usedList[$key]);
						continue;
					}
					$usedAppId[$app['appid']] = $app['appid'];
					$app['pic'] = 'http://appicon.manyou.com/logos/'.$app['appid'];
					$app['url'] = 'userapp.php?mod=app&id='.$app['appid'];
					$app['name'] = $app['appname'];
					$usedList[$key] = $app;
				}
				$usedNum = count($usedAppId);
				if($usedNum < 6 && isset($list[2]) && $list[2]) {
					$addNum = 6 - $usedNum;
					foreach($list[2] as $app) {
						if(!$addNum) {
							break;
						}elseif(!isset($usedAppId[$app['appid']])) {
							$usedList[] = $app;
							$addNum--;
						}
					}
				}
			}
		}
	}
	if(empty($usedList) && isset($list[2]) && $list[2]) {
		foreach($list[2] as $app) {
			if(count($usedList) >= 6) {
				break;
			}
			$usedList[] = $app;
		}
	}
	include template("userapp/userapp_misc");
}