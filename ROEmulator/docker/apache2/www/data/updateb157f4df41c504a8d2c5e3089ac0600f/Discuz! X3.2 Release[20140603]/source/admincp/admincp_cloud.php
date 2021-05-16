<?php

/**
 *	  [Discuz!] (C)2001-2099 Comsenz Inc.
 *	  This is NOT a freeware, use is subject to license terms
 *
 *	  $Id: admincp_cloud.php 33808 2013-08-15 11:22:45Z nemohou $
 */

if(!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
	exit('Access Denied');
}

@set_time_limit(600);
cpheader();

if(empty($admincp) || !is_object($admincp)) {
	exit('Access Denied');
}

$adminscript = ADMINSCRIPT;

$cloudDomain = 'http://cp.discuz.qq.com';

$utilService = Cloud::loadClass('Service_Util');
$appService = Cloud::loadClass('Service_App');
$cloudClient = Cloud::loadClass('Service_Client_Cloud');

try {
	$cloudstatus = $appService->checkCloudStatus();
} catch (Cloud_Service_AppException $e) {
	if($operation == 'doctor' || $operation == 'siteinfo') {
	} else {
		cpmsg_error('cloud_status_error');
	}
}

$forceOpen = !empty($_GET['force_open']) ? true : false;

if(!$operation || $operation == 'open') {

	if($cloudstatus == 'cloud' && !$forceOpen) {
		cpmsg('cloud_turnto_applist', '', 'succeed', array(), '<p class="marginbot"><a href="###" onclick="top.location = \''.ADMINSCRIPT.'?frames=yes&action=cloud&operation=applist\'" class="lightlink">'.cplang('message_redirect').'</a></p><script type="text/JavaScript">setTimeout("top.location = \''.ADMINSCRIPT.'?frames=yes&action=cloud&operation=applist\'", 3000);</script>');
	} else {
		if ($_GET['getConfirmInfo']) {
			ajaxshowheader();
			ajaxshowfooter();
		}

		$step = max(1, intval($_GET['step']));
		$type = $cloudstatus == 'upgrade' ? 'upgrade' : 'open';

		if($step == 1) {

			$utilService->generateUniqueId();

			if($cloudstatus == 'upgrade' || ($cloudstatus == 'cloud' &&  $forceOpen)) {
				shownav('navcloud', 'menu_cloud_upgrade');
				$itemtitle = cplang('menu_cloud_upgrade');
			} else {
				shownav('navcloud', 'menu_cloud_open');
				$itemtitle = cplang('menu_cloud_open');
			}

			echo '
				<div class="itemtitle">
				<h3>'.$itemtitle.'</h3>
				<ul style="margin-right: 10px;" class="tab1"></ul>
				<ul class="stepstat" id="nav_steps"></ul>
				<ul class="tab1"></ul>
				</div>

				<div id="loading">
				<div id="loadinginner" style="display: block; padding: 100px 0; text-align: center; color: #999;">
				<img src="'.$_G['style']['imgdir'].'/loading.gif" alt="loading..." style="vertical-align: middle;" /> '.$lang['cloud_page_loading'].'
				</div>
				</div>
				<div style="display:none;" id="title"></div>';

			showformheader('', 'onsubmit="return submitForm();"');

			if($cloudstatus == 'upgrade' || ($cloudstatus == 'cloud' &&  $forceOpen)) {
				echo '<div style="margin-top:10px; color: red; padding-left: 10px;" id="manyou_update_tips"></div>';
			}

			showtableheader('', '', 'id="mainArea" style="display:none;"');

			echo '
				<tr><td id="" style="border:none;"><div id="msg" class="tipsblock"></div></td></tr>
				<tr><td style="border-top:none;"><br />
				<label><input onclick="if(this.checked) {$(\'submit_submit\').disabled=false; $(\'submit_submit\').style.color=\'#000\';} else {$(\'submit_submit\').disabled=true; $(\'submit_submit\').style.color=\'#aaa\';}" id="agreeProtocal" class="checkbox" type="checkbox" checked="checked" value="1" />' . cplang('cloud_agree_protocal') . '</label><a id="protocal_url" href="javascript:;" target="_blank">' . cplang('read_protocal') . '</a>
				</td>
				</tr>';

			showsubmit('submit', 'cloud_will_open', '', '<script type="text/javascript">$(\'submit_submit\').disabled = true; $(\'submit_submit\').style.color = \'#aaa\';</script><span id="cloud_doctor_site_test_result_div"><img src="' . $_G['style']['imgdir'] . '/loading.gif" class="vm"> '.cplang('cloud_waiting').'</span>');
			showtablefooter();
			showformfooter();

			echo '
				<div id="siteInfo" style="display:none;;">
				<h3 class="flb"><em>'.cplang('message_title').'</em><span><a href="javascript:;" class="flbc" onclick="hideWindow(\'open_cloud\');" title="'.cplang('close').'">'.cplang('close').'</a></span></h3>';

			showformheader('cloud&operation=open&step=2'.(($cloudstatus == 'cloud' && $forceOpen) ? '&force_open=1' : ''), '');

			echo '
				<div class="c">
				<div class="tplw">
				<p class="mbn tahfx">
				<strong>'.cplang('jump_to_cloud').'</strong><input type="hidden" id="cloud_api_ip" name="cloud_api_ip" value="" />
				</p>
				</div>
				</div>

				<div class="o pns"><button type="submit" class="pn pnc" id="btn_1"><span>'.cplang('continue').'</span></button></div>';

			showformfooter();
			echo "</div>";

			echo <<<EOT
<link rel="stylesheet" type="text/css" href="static/image/admincp/cloud/cloud.css" />
<script type="text/javascript" src="static/image/admincp/cloud/cloud.js"></script>
<script type="text/JavaScript">
var cloudStatus = "$cloudstatus";
var disallowfloat = 'siteInfo';
var cloudApiIp = '';
var dialogHtml = '';
var getMsg = false;

var millisec = 10 * 1000; //10√Î
var expirationText = '{$lang['cloud_time_out']}';
expirationTimeout = setTimeout("expiration()", millisec);
</script>
EOT;
			$introUrl = $cloudDomain.'/cloud/introduction';
			if($cloudstatus == 'upgrade') {
				$params = array('type' => 'upgrade');

				if ($_G['setting']['my_app_status']) {
					$params['apps']['manyou'] = array('status' => true);
				}

				if (isset($_G['setting']['my_search_status'])) {

					$params['apps']['search'] = array('status' => !empty($_G['setting']['my_search_status']) ? true : false);

					$oldSiteId = empty($_G['setting']['my_siteid_old'])?'':$_G['setting']['my_siteid_old'];
					$oldSitekeySign = empty($_G['setting']['my_sitekey_sign_old'])?'':$_G['setting']['my_sitekey_sign_old'];

					if($oldSiteId && $oldSiteId != $_G['setting']['my_siteid'] && $oldSitekeySign) {
						$params['apps']['search']['oldSiteId'] = $oldSiteId;
						$params['apps']['search']['searchSig'] = $oldSitekeySign;
					}

				}

				if (isset($_G['setting']['connect'])) {
					$params['apps']['connect'] = array('status' => !empty($_G['setting']['connect']['allow']) ? true : false);

					$oldSiteId = empty($_G['setting']['connectsiteid'])?'':$_G['setting']['connectsiteid'];
					$oldSitekey = empty($_G['setting']['connectsitekey'])?'':$_G['setting']['connectsitekey'];

					if($oldSiteId && $oldSiteId != $_G['setting']['my_siteid'] && $oldSitekey) {
						$params['apps']['connect']['oldSiteId'] = $oldSiteId;
						$params['apps']['connect']['connectSig'] = substr(md5(substr(md5($oldSiteId.'|'.$oldSitekey), 0, 16)), 16, 16);
					}
				}

				$params['ADTAG'] = 'CP.DISCUZ.INTRODUCTION';

				$signUrl = $utilService->generateSiteSignUrl($params);
				$introUrl .= '?'.$signUrl;
			}

			echo '<script type="text/JavaScript" charset="UTF-8" src="'.$introUrl.'"></script>';
			$doctorService = Cloud::loadClass('Service_Doctor');
			$doctorService->showCloudDoctorJS('open');

		} elseif($step == 2) {

			$statsUrl = $cloudDomain . '/cloud/stats/registerclick';
			echo '<script type="text/JavaScript" charset="UTF-8" src="'.$statsUrl.'"></script>';

			try {
				if($_G['setting']['my_siteid'] && $_G['setting']['my_sitekey']) {

					if($_G['setting']['my_app_status']) {
						$manyouClient = Cloud::loadClass('Service_Client_Manyou');
						$manyouClient->sync();
					}

					$cloudClient->upgradeManyou(trim($_GET['cloud_api_ip']));

				} else {
					$cloudClient->registerCloud(trim($_GET['cloud_api_ip']));
				}
			} catch (Cloud_Service_Client_RestfulException $exception) {
				switch ($exception->getCode()) {
				case 1:
					cpmsg('cloud_unknown_dns', '', 'error');
				case 2:
					$msgValues = array(
													'errorMessage' => $exception->getMessage(),
													'errorCode' => $exception->getCode()
												   );
					cpmsg('cloud_network_busy', '', 'error', $msgValues);
				default:
					$msgValues = array();
					$errorMessage = $exception->getMessage();
					$checkUrl = preg_match('/<a.+?>.+?<\/a>/i', $errorMessage, $results);
					if($checkUrl) {
						foreach($results as $key => $result) {
							$errorMessage = str_replace($result, '{replace_' . $key . '}', $errorMessage);
							$msgValues = array('replace_' . $key => $result);
						}
					}
					cpmsg($errorMessage, '', 'error', $msgValues);
				}
			}

			$params['ADTAG'] = 'CP.CLOUD.BIND.INDEX';
			$bindUrl = $cloudDomain . '/bind/index?' . $utilService->generateSiteSignUrl($params);
			echo '<script>top.location="' . $bindUrl . '";</script>';
		}
	}

} elseif($operation == 'applist') {

	if($cloudstatus != 'cloud') {
		cpmsg('cloud_open_first', '', 'succeed', array(), '<p class="marginbot"><a href="###" onclick="top.location = \''.ADMINSCRIPT.'?frames=yes&action=plugins\'" class="lightlink">'.cplang('message_redirect').'</a></p><script type="text/JavaScript">setTimeout("top.location = \''.ADMINSCRIPT.'?frames=yes&action=plugins\'", 3000);</script>');
	}

	$signParams = array('refer' => $_G['siteurl'], 'ADTAG' => 'CP.DISCUZ.APPLIST');
	$signUrl = $utilService->generateSiteSignUrl($signParams);
	$utilService->redirect($cloudDomain . '/cloud/appList/?' . $signUrl);

} elseif(in_array($operation, array('siteinfo', 'doctor'))) {

	require libfile("cloud/$operation", 'admincp');

} elseif(in_array($operation, array('manyou', 'connect', 'security', 'stats', 'search', 'smilies', 'qqgroup', 'union', 'storage'))) {
	if($cloudstatus != 'cloud') {
		cpmsg('cloud_open_first', '', 'succeed', array(), '<p class="marginbot"><a href="###" onclick="top.location = \''.ADMINSCRIPT.'?frames=yes&action=plugins\'" class="lightlink">'.cplang('message_redirect').'</a></p><script type="text/JavaScript">setTimeout("top.location = \''.ADMINSCRIPT.'?frames=yes&action=plugins\'", 3000);</script>');
	}

	if($operation != 'security') {
		$apps = $appService->getCloudApps();
		if(empty($apps) || empty($apps[$operation]) || $apps[$operation]['status'] == 'close') {
			cpmsg('cloud_application_close', 'action=plugins', 'error');
		}
		if($apps[$operation]['status'] == 'disable') {
			cpmsg('cloud_application_disable', 'action=plugins', 'error');
		}
	}

	require libfile("cloud/$operation", 'admincp');

} else {
	exit('Access Denied');
}