<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: cloud_siteinfo.php 33808 2013-08-15 11:22:45Z nemohou $
 */
if(!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
	exit('Access Denied');
}

if(submitcheck('syncsubmit')) {

	if($cloudstatus != 'cloud') {
		cpmsg('cloud_open_first', '', 'succeed', array(), '<p class="marginbot"><a href="###" onclick="top.location = \''.ADMINSCRIPT.'?frames=yes&action=plugins\'" class="lightlink">'.cplang('message_redirect').'</a></p><script type="text/JavaScript">setTimeout("top.location = \''.ADMINSCRIPT.'?frames=yes&action=plugins\'", 3000);</script>');
	}

	try {
		$cloudClient = & Cloud::loadClass('Service_Client_Cloud', array(true));

		if ($_G['setting']['my_app_status']) {
			$manyouClient = Cloud::loadClass('Service_Client_Manyou');
			$manyouClient->sync();
		}

		$res = $cloudClient->sync();
	} catch (Cloud_Service_Client_RestfulException $e) {
		cpmsg('cloud_sync_failure', '', 'error', array('errorCode' => $e->getCode(), 'errorMessage' => $e->getMessage()));
	}

	cpmsg('cloud_sync_success', '', 'succeed', array(), '<p class="marginbot"><a href="###" onclick="top.location = \''.ADMINSCRIPT.'?frames=yes&action=cloud&operation=doctor\'" class="lightlink">'.cplang('message_redirect').'</a></p><script type="text/JavaScript">setTimeout("top.location = \''.ADMINSCRIPT.'?frames=yes&action=cloud&operation=doctor\'", 3000);</script>');

} elseif(submitcheck('resetsubmit')) {

	if (!isfounder()) {
		cpmsg('action_noaccess', '', 'error');
	}

	if($cloudstatus != 'cloud') {
		cpmsg('cloud_open_first', '', 'succeed', array(), '<p class="marginbot"><a href="###" onclick="top.location = \''.ADMINSCRIPT.'?frames=yes&action=plugins\'" class="lightlink">'.cplang('message_redirect').'</a></p><script type="text/JavaScript">setTimeout("top.location = \''.ADMINSCRIPT.'?frames=yes&action=plugins\'", 3000);</script>');
	}

	try {
		$cloudClient = Cloud::loadClass('Service_Client_Cloud');
		$res = $cloudClient->resetKey();
	} catch (Cloud_Service_Client_RestfulException $e) {
		cpmsg($e->getMessage(), '', 'error');
	}

	$sId = intval($res['sId']);
	$sKey = trim($res['sKey']);

	C::t('common_setting')->update_batch(array('my_siteid' => $sId, 'my_sitekey' => $sKey));
	updatecache('setting');

	cpmsg('cloud_reset_success', '', 'succeed', array(), '<p class="marginbot"><a href="###" onclick="top.location = \''.ADMINSCRIPT.'?frames=yes&action=cloud&operation=doctor\'" class="lightlink">'.cplang('message_redirect').'</a></p><script type="text/JavaScript">setTimeout("top.location = \''.ADMINSCRIPT.'?frames=yes&action=cloud&operation=doctor\'", 3000);</script>');

} elseif(submitcheck('ipsubmit')) {

	$_POST['cloud_api_ip'] = trim($_POST['cloud_api_ip']);
	$_POST['my_ip'] = trim($_POST['my_ip']);
	$_POST['connect_api_ip'] = trim($_POST['connect_api_ip']);

	if($_G['setting']['cloud_api_ip'] != $_POST['cloud_api_ip'] || $_G['setting']['my_ip'] != $_POST['my_ip'] || $_G['setting']['connect_api_ip'] != $_POST['connect_api_ip']) {
		C::t('common_setting')->update_batch(array('cloud_api_ip' => $_POST['cloud_api_ip'], 'my_ip' => $_POST['my_ip'], 'connect_api_ip' => $_POST['connect_api_ip']));
		updatecache('setting');
	}

	$locationUrl = ADMINSCRIPT.'?frames=yes&action=cloud&operation=doctor';

	cpmsg('cloud_ipsetting_success', '', 'succeed', array(), '<p class="marginbot"><a href="###" onclick="top.location = \''.$locationUrl.'\'" class="lightlink">'.cplang('message_redirect').'</a></p><script type="text/JavaScript">setTimeout("top.location = \''.$locationUrl.'\'", 3000);</script>');

} elseif ($_GET['anchor'] == 'cloud_ip') {
	ajaxshowheader();
	echo '
		<h3 class="flb" id="fctrl_showblock" style="cursor: move;">
			<em id="return_showblock" fwin="showblock">'.$lang['cloud_api_ip_btn'].'</em>
			<span><a title="'.$lang['close'].'" onclick="hideWindow(\'cloudApiIpWin\');return false;" class="flbc" href="javascript:;">'.$lang['close'].'</a></span>
		</h3>
		';
	echo '<div style="margin: 0 10px; width: 700px;">';
	showformheader('cloud');
	showhiddenfields(array('operation' => $operation));
	if($_GET['callback']) {
		showhiddenfields(array('callback' => $_GET['callback']));
	}
	showtableheader();
	showsetting('cloud_api_ip', 'cloud_api_ip', $_G['setting']['cloud_api_ip'], 'text');
	showsetting('cloud_manyou_ip', 'my_ip', $_G['setting']['my_ip'], 'text');
	showsetting('cloud_connect_api_ip', 'connect_api_ip', $_G['setting']['connect_api_ip'], 'text');
	showsubmit('ipsubmit');
	showtablefooter();
	showformfooter();
	echo '</div>';
	ajaxshowfooter();
} else {
	shownav('navcloud', 'menu_cloud_siteinfo');
	showsubmenu('menu_cloud_siteinfo');
	showtips('cloud_siteinfo_tips');
	echo '<script type="text/javascript">var disallowfloat = "";</script>';
	showformheader('cloud');
	showhiddenfields(array('operation' => $operation));
	showtableheader();
	showtitle('menu_cloud_siteinfo');
	showtablerow('', array('class="td24"'), array(
		'<strong>'.cplang('cloud_site_name').'</strong>',
		$_G['setting']['bbname']
	));
	showtablerow('', array('class="td24"'), array(
		'<strong>'.cplang('cloud_site_url').'</strong>',
		$_G['siteurl']
	));
	showtablerow('', array('class="td24"'), array(
		'<strong>'.cplang('cloud_site_id').'</strong>',
		$_G['setting']['my_siteid']
	));

	$resetSubmitButton = isfounder() ? '<input type="submit" class="btn" id="submit_resetsubmit" name="resetsubmit" value="'.$lang['cloud_resetkey'].'" />&nbsp; ' : '';
	showsubmit('syncsubmit', 'cloud_sync', '',$resetSubmitButton.'<input type="button" class="btn" onClick="showWindow(\'cloudApiIpWin\', \''.ADMINSCRIPT.'?action=cloud&operation=siteinfo&anchor=cloud_ip\'); return false;" value="'.$lang['cloud_api_ip_btn'].'" />');
	showtablefooter();
	showformfooter();
}