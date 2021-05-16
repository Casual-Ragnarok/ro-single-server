<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: cloud_doctor.php 33991 2013-09-16 07:25:00Z nemohou $
 */
if(!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
	exit('Access Denied');
}

@set_time_limit(0);

$op = trim($_GET['op']);

if(submitcheck('setidkeysubmit')) {
	if (!isfounder()) {
		cpmsg('action_noaccess', '', 'error');
	}

	$siteId = intval(trim($_GET['my_siteid']));
	if($siteId && strcmp($_GET['my_siteid'], $siteId) !== 0) {
		cpmsg('cloud_idkeysetting_siteid_failure', '', 'error');
	}

	$_GET['my_sitekey'] = trim($_GET['my_sitekey']);
	if(empty($_GET['my_sitekey'])) {
		$siteKey = '';
	} elseif(strpos($_GET['my_sitekey'], '***')) {
		$siteKey = false;
	} elseif(preg_match('/^[0-9a-f]{32}$/', $_GET['my_sitekey'])) {
		$siteKey = $_GET['my_sitekey'];
	} else {
		cpmsg('cloud_idkeysetting_sitekey_failure', '', 'error');
	}

	$settings = array();
	if($siteKey !== false) {
		$settings['my_sitekey'] = $siteKey;
	}
	if($_G['setting']['my_siteid'] != $siteId) {
		$settings['my_siteid'] = $siteId;
	}
	if($_G['setting']['cloud_status'] != $_GET['cloud_status']) {
		$settings['cloud_status'] = intval(trim($_GET['cloud_status']));;
	}
	if($settings) {
		C::t('common_setting')->update_batch($settings);
		updatecache('setting');
	}

	$locationUrl = ADMINSCRIPT.'?frames=yes&action=cloud&operation=doctor';

	cpmsg('cloud_idkeysetting_success', '', 'succeed', array(), '<p class="marginbot"><a href="###" onclick="top.location = \''.$locationUrl.'\'" class="lightlink">'.cplang('message_redirect').'</a></p><script type="text/JavaScript">setTimeout("top.location = \''.$locationUrl.'\'", 3000);</script>');

} elseif($op == 'apitest') {

	$doctorService =Cloud::loadClass('Service_Doctor');
	$APIType = intval($_GET['api_type']);
	$APIIP = trim($_GET['api_ip']);

	$startTime = microtime(true);
	$testStatus = $doctorService->testAPI($APIType, $APIIP, $_G['setting']);
	$endTime = microtime(true);

	$otherTips = '';
	if($APIIP) {
		if ($_GET['api_description']) {
			$otherTips = diconv(trim($_GET['api_description']), 'UTF-8');
		}
	} else {
		if($APIType == 1) {
			$otherTips = '<a href="javascript:;" onClick="display(\'cloud_tbody_api_test\')">'.$lang['cloud_doctor_api_test_other'].'</a>';
		} elseif($APIType == 2) {
			$otherTips = '<a href="javascript:;" onClick="display(\'cloud_tbody_manyou_test\')">'.$lang['cloud_doctor_manyou_test_other'].'</a>';
		} elseif($APIType == 3) {
			$otherTips = '<a href="javascript:;" onClick="display(\'cloud_tbody_qzone_test\')">'.$lang['cloud_doctor_qzone_test_other'].'</a>';
		}
	}

	ajaxshowheader();
	if($testStatus) {
		printf($lang['cloud_doctor_api_test_success'], $lang['cloud_doctor_result_success'], $APIIP, $endTime - $startTime, $otherTips);
	} else {
		printf($lang['cloud_doctor_api_test_failure'], $lang['cloud_doctor_result_failure'], $APIIP, $otherTips);
	}
	ajaxshowfooter();

} elseif($op == 'setidkey') {

	ajaxshowheader();
	echo '
		<h3 class="flb" id="fctrl_showblock" style="cursor: move;">
			<em id="return_showblock" fwin="showblock">'.$lang['cloud_doctor_setidkey'].'</em>
			<span><a title="'.$lang['close'].'" onclick="hideWindow(\'cloudApiIpWin\');return false;" class="flbc" href="javascript:;">'.$lang['close'].'</a></span>
		</h3>
		';
	echo '<div style="margin: 0 10px; width: 700px;">';
	showtips('cloud_doctor_setidkey_tips');
	showformheader('cloud');
	showhiddenfields(array('operation' => $operation));
	showhiddenfields(array('op' => $op));
	showtableheader();
	showsetting('cloud_site_id', 'my_siteid', $_G['setting']['my_siteid'], 'text');
	showsetting('cloud_site_key', 'my_sitekey', preg_replace('/(\w{2})\w*(\w{2})/', '\\1****\\2', $_G['setting']['my_sitekey']), 'text');
	showsetting('cloud_site_status', array('cloud_status', array(array('0', $lang['cloud_doctor_status_0']), array('1', $lang['cloud_doctor_status_1']), array('2', $lang['cloud_doctor_status_2']))), $_G['setting']['cloud_status'], 'select');
	showsubmit('setidkeysubmit');
	showtablefooter();
	showformfooter();
	echo '</div>';
	ajaxshowfooter();

} else {



	$appService = Cloud::loadClass('Service_App');
	$doctorService = Cloud::loadClass('Service_Doctor');
	require_once DISCUZ_ROOT.'./source/discuz_version.php';

	shownav('tools', 'menu_cloud_doctor');
	showsubmenu('menu_cloud_doctor');
	showtips('cloud_doctor_tips');
	echo '<script type="text/javascript">var disallowfloat = "";</script>';

	showtableheader();
	showformheader('cloud');
	showhiddenfields(array('operation' => 'siteinfo'));
	showtagheader('tbody', '', true);
	showtitle('cloud_doctor_title_status');
	showtablerow('', array('class="td24"'), array(
		'<strong>'.cplang('cloud_site_url').'</strong>',
		$_G['siteurl'].
		(isfounder() ? ' &nbsp; <input type="submit" class="btn" id="submit_syncsubmit" name="syncsubmit" value="'.$lang['cloud_sync'].'" />&nbsp; ' : '')
	));
	showtablerow('', array('class="td24"'), array(
		'<strong>'.cplang('cloud_site_id').'</strong>',
		$_G['setting']['my_siteid']
	));
	showtablerow('', array('class="td24"'), array(
		'<strong>'.cplang('cloud_site_key').'</strong>',
		preg_replace('/(\w{2})\w*(\w{2})/', '\\1****\\2', $_G['setting']['my_sitekey']).' '.$lang['cloud_site_key_safetips']
	));

	showtablerow('', array('class="td24"'), array(
		'<strong>'.cplang('cloud_site_status').'</strong>',
		(isfounder() ? $doctorService->showCloudStatus($_G['setting']['cloud_status']).' <a href="javascript:;" onClick="showWindow(\'cloudApiIpWin\', \''.ADMINSCRIPT.'?action=cloud&operation=doctor&op=setidkey\'); return false;">'.$lang['cloud_doctor_modify_siteidkey'].'</a>' : $doctorService->showCloudStatus($_G['setting']['cloud_status'])).
		(isfounder() ? ' &nbsp; <input type="submit" class="btn" id="submit_resetsubmit" name="resetsubmit" value="'.$lang['cloud_resetkey'].'" />' : '')
	));
	showtablerow('', array('class="td24"'), array(
		'<strong>'.cplang('setting_basic_bbclosed').'</strong>',
		$_G['setting']['bbclosed'] ? $lang['cloud_doctor_close_yes'] : $lang['no']
	));
	showtablerow('', array('class="td24"'), array(
		'<strong>'.cplang('cloud_site_version').'</strong>',
		DISCUZ_VERSION.' '.DISCUZ_RELEASE
	));
	if(isfounder()) {
		showtablerow('', array('class="td24"'), array(
			'<strong>'.cplang('cloud_change_info').'</strong>',
			'<a href="'.$doctorService->changeQQUrl().'" target="_blank">'.cplang('cloud_change_qq').'</a>',
		));
	}
	showtagfooter('tbody');
	showformfooter();

	showtagheader('tbody', '', true);
	showtitle('cloud_doctor_title_result');
	showtablerow('', array('class="td24"'), array(
		'<strong>'.cplang('cloud_timecheck').'</strong>',
		'<span id="cloud_time_check">' . cplang('cloud_doctor_time_check', array('imgdir' => $_G['style']['imgdir'])) .'</span>',
	));
	showtablerow('', array('class="td24"'), array(
		'<strong>'.cplang('cloud_doctor_gethostbyname_function').'</strong>',
		function_exists('gethostbyname') ? $lang['cloud_doctor_result_success'].' '.$lang['available'] : $lang['cloud_doctor_result_failure'].$lang['cloud_doctor_function_disable']
	));

	showtablerow('', array('class="td24"'), array(
		'<strong>'.cplang('cloud_doctor_dns_api').'</strong>',
		$doctorService->checkDNSResult(1, $_G['setting'])
	));
	showtablerow('', array('class="td24"'), array(
		'<strong>'.cplang('cloud_doctor_dns_api_test').'</strong>',
		$doctorService->showTestJS(1)
	));
	showtagfooter('tbody');

	showtagheader('tbody', 'cloud_tbody_api_test', false);
	showtagfooter('tbody');

	showtagheader('tbody', '', true);
	showtablerow('', array('class="td24"'), array(
		'<strong>'.cplang('cloud_doctor_dns_manyou').'</strong>',
		$doctorService->checkDNSResult(2, $_G['setting'])
	));
	showtablerow('', array('class="td24"'), array(
		'<strong>'.cplang('cloud_doctor_dns_manyou_test').'</strong>',
		$doctorService->showTestJS(2)
	));
	showtagfooter('tbody');

	showtagheader('tbody', 'cloud_tbody_manyou_test', false);
	showtagfooter('tbody');

	showtagheader('tbody', '', true);
	showtablerow('', array('class="td24"'), array(
		'<strong>'.cplang('cloud_doctor_dns_qzone').'</strong>',
		$doctorService->checkDNSResult(3, $_G['setting'])
	));
	showtablerow('', array('class="td24"'), array(
		'<strong>'.cplang('cloud_doctor_dns_qzone_test').'</strong>',
		$doctorService->showTestJS(3)
	));
	showtagfooter('tbody');

	showtagheader('tbody', 'cloud_tbody_qzone_test', false);
	showtagfooter('tbody');

	showtagheader('tbody', 'cloud_tbody_site_test', true);
	showtablerow('', array('class="td24"'), array(
		'<strong>'.cplang('cloud_doctor_site_test').'</strong>',
		cplang('cloud_doctor_site_test_result', array('imgdir' => $_G['style']['imgdir']))
	));
	showtagfooter('tbody');

	showtagheader('tbody', '', true);
	showtitle('cloud_doctor_title_plugin');
	$doctorService->showPlugins();
	showtagfooter('tbody');

	if($appService->getCloudAppStatus('connect')) {
		if ($op == 'fixGuest') {
			$doctorService->fixGuestGroup(cplang('connect_guest_group_name'));
		}

		showtagheader('tbody', '', true);
		showtitle('cloud_doctor_title_connect');
		showtablerow('', array('class="td24"'), array(
			'<strong>'.cplang('cloud_doctor_connect_app_id').'</strong>',
			!empty($_G['setting']['connectappid']) ? $_G['setting']['connectappid'] : $lang['cloud_doctor_connect_reopen']
		));
		showtablerow('', array('class="td24"'), array(
			'<strong>'.cplang('cloud_doctor_connect_app_key').'</strong>',
			!empty($_G['setting']['connectappkey']) ? preg_replace('/(\w{2})\w*(\w{2})/', '\\1****\\2', $_G['setting']['connectappkey']).' '.$lang['cloud_site_key_safetips'] : $lang['cloud_doctor_connect_reopen']
		));

		$guestGroupStr = cplang('cloud_doctor_result_success') .' '. cplang('cloud_doctor_normal');
		if (!$doctorService->checkGuestGroup()) {
			$guestGroupStr = cplang('cloud_doctor_result_failure') . ' ' . cplang('cloud_doctor_connect_fix');
		}
		showtablerow('', array('class="td24"'), array(
			'<strong>'.cplang('cloud_doctor_connect_guestgroup').'</strong>',
			$guestGroupStr,
		));
		showtagfooter('tbody');
	}

	showtablefooter();
	$doctorService->showCloudDoctorJS();
}