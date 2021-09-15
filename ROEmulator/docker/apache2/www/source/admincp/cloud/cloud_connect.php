<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: cloud_connect.php 33756 2013-08-10 06:32:48Z nemohou $
 */

if(!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
	exit('Access Denied');
}

$utilService = Cloud::loadClass('Service_Util');
$connectService = Cloud::loadClass('Service_Connect');

$op = $_GET['op'];

$signUrl = $utilService->generateSiteSignUrl();

$_GET['anchor'] = in_array($_GET['anchor'], array('setting', 'service')) ? $_GET['anchor'] : 'setting';
$current = array($_GET['anchor'] => 1);

$connectnav = array();

$connectnav[0] = array('connect_menu_setting', 'cloud&operation=connect&anchor=setting', $current['setting']);
$connectnav[1] = array('connect_menu_service', 'cloud&operation=connect&anchor=service', $current['service']);

if (!$_G['inajax']) {
	cpheader();
}

if($_GET['anchor'] == 'service') {
	$utilService->redirect($cloudDomain.'/connect/service/?' . $signUrl);

} elseif ($_GET['anchor'] == 'setting') {

	$setting = C::t('common_setting')->fetch_all(array('extcredits', 'connect', 'connectsiteid', 'connectsitekey', 'regconnect'));
	$setting['connect'] = (array)dunserialize($setting['connect']);

	if(!submitcheck('connectsubmit')) {

		shownav('navcloud', 'menu_setting_qqconnect');

		include_once libfile('function/forumlist');
		$forumselect = array();
			$forumselect['t'] = '<select name="connectnew[t][fids][]" multiple="multiple" size="10">'.forumselect(FALSE, 0, 0, TRUE).'</select>';
			if($setting['connect']['t']['fids']) {
				foreach($setting['connect']['t']['fids'] as $v) {
					$forumselect['t'] = str_replace('<option value="'.$v.'">', '<option value="'.$v.'" selected>', $forumselect['t']);
				}
			}

		$connectrewardcredits = $connectgroup = $connectguestgroup = '';
		$setting['extcredits'] = dunserialize($setting['extcredits']);
		for($i = 0; $i <= 8; $i++) {
			if($setting['extcredits'][$i]['available']) {
				$extcredit = 'extcredits'.$i.' ('.$setting['extcredits'][$i]['title'].')';
				$connectrewardcredits .= '<option value="'.$i.'" '.($i == intval($setting['connect']['register_rewardcredit']) ? 'selected' : '').'>'.($i ? $extcredit : $lang['none']).'</option>';
			}
		}

		$groups = C::t('common_usergroup')->fetch_all_by_type('special');
		foreach($groups as $group) {
			$connectgroup .= "<option value=\"$group[groupid]\" ".($group['groupid'] == $setting['connect']['register_groupid'] ? 'selected' : '').">$group[grouptitle]</option>\n";
			$connectguestgroup .= "<option value=\"$group[groupid]\" ".($group['groupid'] == $setting['connect']['guest_groupid'] ? 'selected' : '').">$group[grouptitle]</option>\n";
		}

		showformheader('cloud&operation=connect');
		showtableheader();
		showsetting('connect_setting_allow', 'connectnew[allow]', $setting['connect']['allow'], 'radio', 0, 1);
		showsetting('setting_access_guest_connect_group', '', '', '<select name="connectnew[guest_groupid]"><option value="0">'.$lang['usergroups_system_0'].'</option>'.$connectguestgroup.'</select>');
		showsetting('setting_access_register_connect_birthday', 'connectnew[register_birthday]', $setting['connect']['register_birthday'], 'radio');
		showsetting('setting_access_register_connect_gender', 'connectnew[register_gender]', $setting['connect']['register_gender'], 'radio');
		showsetting('setting_access_register_connect_uinlimit', 'connectnew[register_uinlimit]', $setting['connect']['register_uinlimit'], 'text');
		showsetting('setting_access_register_connect_credit', '', '', '<select name="connectnew[register_rewardcredit]">'.$connectrewardcredits.'</select>');
		showsetting('setting_access_register_connect_addcredit', 'connectnew[register_addcredit]', $setting['connect']['register_addcredit'], 'text');
		showsetting('setting_access_register_connect_group', '', '', '<select name="connectnew[register_groupid]"><option value="0">'.$lang['usergroups_system_0'].'</option>'.$connectgroup.'</select>');
		showsetting('setting_access_register_connect_regverify', 'connectnew[register_regverify]', $setting['connect']['register_regverify'], 'radio');
		showsetting('setting_access_register_connect_invite', 'connectnew[register_invite]', $setting['connect']['register_invite'], 'radio');
		showsetting('setting_access_register_connect_newbiespan', 'connectnew[newbiespan]', $setting['connect']['newbiespan'], 'text');
		showtagfooter('tbody');
		showsubmenu('menu_cloud_connect', $connectnav);
		showsetting('connect_setting_t_allow', 'connectnew[t][allow]', $setting['connect']['t']['allow'], 'radio', 0, 1);
		showsetting('connect_setting_t_fids', '', '', $forumselect['t']);
		showsetting('connect_setting_t_group', 'connectnew[t][group]', $setting['connect']['t']['group'], 'radio');
		showsetting('connect_setting_t_reply', 'connectnew[t][reply]', $setting['connect']['t']['reply'], 'radio');
		showsetting('connect_setting_t_reply_showauthor', 'connectnew[t][reply_showauthor]', $setting['connect']['t']['reply_showauthor'], 'radio');
		showtagfooter('tbody');
		showsetting('connect_setting_like_allow', 'connectnew[like_allow]', $setting['connect']['like_allow'], 'radio', 0, 1);
		showsetting('connect_setting_like_url', 'connectnew[like_qq]', $setting['connect']['like_qq'], 'text');
		showtagfooter('tbody');
		showsetting('connect_setting_turl_allow', 'connectnew[turl_allow]', $setting['connect']['turl_allow'], 'radio', 0, 1);
		showsetting('connect_setting_turl_qq', 'connectnew[turl_qq]', $setting['connect']['turl_qq'], 'text');
		showtagfooter('tbody');
		showsetting('connect_setting_qshare_allow', 'connectnew[qshare_allow]', $setting['connect']['qshare_allow'], 'radio', 0, 1);
		showtagfooter('tbody');
		showsetting('connect_setting_weibo_appkey', 'connectnew[qshare_appkey]', $setting['connect']['qshare_appkey'], 'text');
		showtagfooter('tbody');
		showsubmit('connectsubmit');
		showtablefooter();
		showformfooter();

	} else {

		if($_GET['connectnew']['turl_qq'] && !is_numeric($_GET['connectnew']['turl_qq'])) {
			cpmsg('connect_setting_turl_qq_failed', '', 'error');
		}

		if($_GET['connectnew']['like_url']) {
			$url = parse_url($_GET['connectnew']['like_url']);
			if(!preg_match('/\.qq\.com$/i', $url['host'])) {
				cpmsg('connect_like_url_error', '', 'error');
			}
		}
		if($_GET['connectnew']['like_allow'] && $_GET['connectnew']['like_url'] === '') {
			cpmsg('connect_like_url_miss', '', 'error');
		}
		$_GET['connectnew'] = array_merge($setting['connect'], $_GET['connectnew']);
		$_GET['connectnew']['like_url'] = $_GET['connectnew']['like_qq'] ? 'http://open.qzone.qq.com/like?url=http%3A%2F%2Fuser.qzone.qq.com%2F'.$_GET['connectnew']['like_qq'].'&width=100&height=21&type=button_num' : '';
		$_GET['connectnew']['turl_code'] = '';
		$connectnew = serialize($_GET['connectnew']);
		$regconnectnew = !$setting['connect']['allow'] && $_GET['connectnew']['allow'] ? 1 : $setting['regconnect'];
		C::t('common_setting')->update_batch(array('regconnect' => $regconnectnew, 'connect' => $connectnew));

		$connectClient = Cloud::loadClass('Service_Client_Connect');
		try {
			$res = $connectClient->sync($_GET['connectnew']['like_qq'], $_GET['connectnew']['turl_qq']);
		} catch (Cloud_Service_Client_RestfulException $e) {
			$res = array('status' => false, 'msg' => cplang('qqgroup_msg_remote_exception', array('errmsg' => $e->getMessage(), 'errno' => $e->getCode())));
		}
		if(!is_array($res)) {
			$res = array('status' => false, 'msg' => 'qqgroup_msg_remote_error');
		}
		if($res['mblogCode']) {
			$_GET['connectnew']['turl_code'] = $res['mblogCode'];
			$connectnew = serialize($_GET['connectnew']);
			C::t('common_setting')->update('connect', $connectnew);
		}

		updatecache(array('setting', 'fields_register', 'fields_connect_register'));
		cpmsg('connect_update_succeed', 'action=cloud&operation=connect', 'succeed');

	}
}