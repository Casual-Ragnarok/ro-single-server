<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: cloud_manyou.php 29273 2012-03-31 07:58:50Z yexinhao $
 */
if(!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
	exit('Access Denied');
}

cpheader();

if(!submitcheck('settingsubmit')) {
	shownav('navcloud', 'setting_manyou');

	$_GET['anchor'] = in_array($_GET['anchor'], array('base', 'manage')) ? $_GET['anchor'] : 'manage';
	$current = array($_GET['anchor'] => 1);

	$manyounav = array();

	if($_G['setting']['my_app_status']) {
		$manyounav[0] = array('setting_manyou_manage', 'cloud&operation=manyou&anchor=manage', $current['manage']);
	}
	$manyounav[1] = array('setting_manyou_base', 'cloud&operation=manyou&anchor=base', $current['base']);

	showsubmenu('setting_manyou', $manyounav);

	showformheader('cloud&edit=yes');
	showhiddenfields(array('operation' => $operation));

	if($_GET['anchor'] == 'base') {

		showtips('setting_manyou_tips');

		showtableheader('', 'nobottom', 'id="base"');
		$actives = $checkarr = array();
		$actives[$_G['setting']['my_app_status']] = ' class="checked"';
		$checkarr[$_G['setting']['my_app_status']] = ' checked';

		$str = <<<EOF
		<ul onmouseover="altStyle(this);">
			<li$actives[1]><input type="radio" onclick="hiddenShareInfo(0, 1);$('hidden_setting_manyou_base_status').style.display = '';" $checkarr[1] value="1" name="settingnew[my_app_status]" class="radio">&nbsp;$lang[yes]</li>
			<li$actives[0]><input type="radio" onclick="hiddenShareInfo(0, 0);$('hidden_setting_manyou_base_status').style.display = 'none';" $checkarr[0] value="0" name="settingnew[my_app_status]" class="radio">&nbsp;$lang[no]</li>
		</ul>
EOF;
		showsetting('setting_manyou_base_status', 'settingnew[my_app_status]', $_G['setting']['my_app_status'], $str, '', 1);
		showsetting('setting_manyou_base_close_prompt', 'settingnew[my_closecheckupdate]', $_G['setting']['my_closecheckupdate'], 'radio');
		showsetting('setting_manyou_base_open_app_prompt', 'settingnew[my_openappprompt]', $_G['setting']['my_openappprompt'], 'radio');
		showtagfooter('tbody');

		$appstate = !empty($_G['setting']['my_app_status']) ? 1 : 0;
		$actives = $checkarr = array();

		echo <<<EOF
		<script type="text/javascript">

			var appState = $appstate;

			function hiddenShareInfo(type, state) {
				appState = state ? 1 : 0;
				$('shareinfo').style.display = appState ? '' : 'none';
			}
		</script>
EOF;

		showtagheader('tbody', 'shareinfo', $_G['setting']['my_app_status']);
		showsetting('setting_manyou_base_ip', 'settingnew[my_ip]', $_G['setting']['my_ip'], 'text');
		showtagfooter('tbody');
		showtablefooter();

	} elseif($_G['setting']['my_app_status']) {


		$uchUrl = $_G['siteurl'].'/'.ADMINSCRIPT.'?action=cloud&operation=manyou&anchor=' . $_GET['anchor'];

		if(empty($_GET['my_suffix'])) {
			$_GET['my_suffix'] = '/appadmin/list';
		}
		$my_prefix = 'http://uchome.manyou.com';
		$my_suffix = urlencode($_GET['my_suffix']);
		$tmp_suffix = $_GET['my_suffix']?urldecode($_GET['my_suffix']):'/appadmin/list';
		$myUrl = $my_prefix.$tmp_suffix;

		$timestamp = time();
		$hash = md5($_G['setting']['my_siteid'].'|'.$_G['uid'].'|'.$_G['setting']['my_sitekey'].'|'.$timestamp);

		$delimiter = strrpos($myUrl, '?') ? '&' : '?';

		$url = $myUrl.$delimiter.'s_id='.$_G['setting']['my_siteid'].'&uch_id='.$_G['uid'].'&uch_url='.urlencode($uchUrl).'&my_suffix='.$my_suffix.'&timestamp='.$timestamp.'&my_sign='.$hash;

		print <<<EOF
		<script type="text/javascript" src="http://static.manyou.com/scripts/my_iframe.js"></script>
		<script language="javascript">
		var prefixURL = "$my_prefix";
		var suffixURL = "$my_suffix";
		var queryString = '';
		var url = "{$url}";
		var oldHash = null;
		var timer = null;
		var server = new MyXD.Server("ifm0");
		server.registHandler('iframeHasLoaded');
		server.registHandler('setTitle');
		server.start();
		function iframeHasLoaded(ifm_id) {
			MyXD.Util.showIframe(ifm_id);
			document.getElementById('loading').style.display = 'none';
		}
		function setTitle(x) {
			document.title = x;
		}
		</script>

		<div id="loading" style="display:block; padding:100px 0 100px 0;text-align:center;color:#999999;font-size:12px;">
		<img src="static/image/common/loading.gif" alt="loading..." align="absmiddle" />  {$lang['loading']}...
		</div>
		<div style="margin-top:8px;">
			<iframe id="ifm0" frameborder="0" width="810px" scrolling="no" height="810px" style="position:absolute; top:-5000px; left:-5000px;" src="{$url}"></iframe>
		</div>
		</body></html>
EOF;
		exit();

	} else {
		cpmsg('my_app_status_off', 'action=cloud&operation=manyou&anchor=base', 'error');
	}

	showsubmit('settingsubmit', 'submit');
	showtablefooter();
	showformfooter();

} else {

	$settingnew = $_GET['settingnew'];

	$settings = array();
	foreach($settingnew as $key => $val) {
		if($_G['setting'][$key] != $val) {
			$$key = $val;

			$settings[$key] = $val;
		}
	}

	if($settings) {
		C::t('common_setting')->update_batch($settings);
	}

	$appName = 'manyou';
	$status = $settingnew['my_app_status'] ? 'normal' : 'pause';

	$appService = Cloud::loadClass('Service_App');
	$appService->setCloudAppStatus($appName, $status);

	cpmsg('setting_update_succeed', 'action=cloud&operation='.$operation.(!empty($_GET['anchor']) ? '&anchor='.$_GET['anchor'] : ''), 'succeed');
}