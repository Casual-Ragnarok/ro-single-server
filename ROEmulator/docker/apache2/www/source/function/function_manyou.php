<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: function_manyou.php 28663 2012-03-07 05:50:37Z zhangguosheng $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}


function manyou_getuserapp($panel = 0) {
	global $_G;

	$myapps = $panelapp = $_G['my_menu'] = $userapplist = $_G['my_panelapp'] = array();
	$showcount = $_G['my_menu_more'] = 0;

	if($_G['uid'] && $_G['setting']['my_app_status']) {
		space_merge($_G['member'], 'field_home');
		if($_G['member']['menunum'] < 3) $_G['member']['menunum'] = 10;
		$userapps = C::t('home_userapp')->fetch_all_by_uid_appid($_G['uid'], 0, 'menuorder');
		$appids = array();
		foreach($userapps as $app) {
			$appids[$app['appid']] = $app['appid'];
		}
		if(!empty($appids)) {
			$myapps = C::t('common_myapp')->fetch_all($appids);
		}
		foreach($userapps as $value) {
			$value['iconstatus'] = $myapps[$value['appid']]['iconstatus'];
			$value['userpanelarea'] = $myapps[$value['appid']]['userpanelarea'];
			$value['appstatus'] = $myapps[$value['appid']]['appstatus'];

			$value['icon'] = getmyappiconpath($value['appid'], $value['iconstatus']);
			if($value['iconstatus']=='0' && empty($_G['myapp_icon_downloaded'])) {
				$_G['myapp_icon_downloaded'] = '1';
				downloadmyappicon($value['appid']);
			}
			if($value['allowsidenav'] && !empty($value['appname'])) {

				$_G['my_userapp'][$value['appid']] = $value;
				if($panel) {
					$userapplist[$value['appid']] = $value;
					if($value['userpanelarea'] && $value['userpanelarea'] < 3) {
						$panelapp[$value['appid']] = $value;
						$_G['my_panelapp'][$value['userpanelarea']][$value['appid']] = $value;
					}
				} else {
					if(!isset($_G['cache']['userapp'][$value['appid']])) {
						if($_G['member']['menunum'] > 100 || $showcount < $_G['member']['menunum']) {
							$_G['my_menu'][] = $value;
							$showcount++;
						} else {
							$_G['my_menu_more'] = 1;
						}
					}
				}
			} elseif (!$value['allowsidenav']) {
				if(isset($_G['cache']['userapp'][$value['appid']])) {
					unset($_G['cache']['userapp'][$value['appid']]);
				}
			}

		}
		if(!empty($userapplist)) {
			foreach($panelapp as $appid => $value) {
				if(isset($_G['cache']['userapp'][$value['appid']])) {
					unset($_G['cache']['userapp'][$appid]);
				}
			}
			foreach($userapplist as $appid => $value) {
				if(!isset($_G['cache']['userapp'][$value['appid']]) && !isset($panelapp[$value['appid']])) {
					if($_G['member']['menunum'] > 100 || $showcount < $_G['member']['menunum']) {
						$_G['my_menu'][] = $value;
						$showcount++;
					} else {
						$_G['my_menu_more'] = 1;
						break;
					}
				}
			}
		}
	}
}

function downloadmyappicon($appid) {
	$iconpath = getglobal('setting/attachdir').'./'.'myapp/icon/'.$appid.'.jpg';
	if(!is_dir(dirname($iconpath))) {
		dmkdir(dirname($iconpath));
	}
	C::t('common_myapp')->update($appid, array('iconstatus'=>'-1'));
	$ctx = stream_context_create(array('http' => array('timeout' => 10)));
	$icondata = file_get_contents(getmyappiconpath($appid, 0), false, $ctx);
	if($icondata) {
		file_put_contents($iconpath, $icondata);
		C::t('common_myapp')->update($appid, array('iconstatus'=>'1', 'icondowntime'=>TIMESTAMP));
	}
}