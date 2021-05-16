<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: cloud_stats.php 29282 2012-03-31 09:26:14Z zhouxiaobo $
 */
if(!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
	exit('Access Denied');
}

$_GET['anchor'] = in_array($_GET['anchor'], array('base', 'summary')) ? $_GET['anchor'] : 'summary';
$current = array($_GET['anchor'] => 1);

$statsnav = array();
$statsnav[0] = array('cloud_stats_summary', 'cloud&operation=stats&anchor=summary', $current['summary']);
$statsnav[1] = array('cloud_stats_setting', 'cloud&operation=stats&anchor=base', $current['base']);

if(!$_G['inajax']) {
	cpheader();
}

if($_GET['anchor'] == 'base') {

	if(!submitcheck('settingsubmit')) {

		shownav('navcloud', 'cloud_stats');
		showsubmenu('cloud_stats', $statsnav);

		showtips('cloud_stats_tips');
		showformheader('cloud&edit=yes');
		showhiddenfields(array('operation' => $operation));
		showtableheader();

		$myicon = C::t('common_setting')->fetch('cloud_staticon');
		if ($myicon === false || in_array($myicon, array(5, 6, 7, 8))) {
			$myicon = 1;
		}

		$checkicon[$myicon] = ' checked';
		$icons = '<table style="margin-bottom: 3px; margin-top:3px; width:350px;"><tr><td>';
		for($i=1;$i<=11;$i++) {
			if ($i < 5) {
				$icons .= '<input class="radio" type="radio" id="stat_icon_'.$i.'" name="settingnew[cloud_staticon]" value="'.$i.'"'.$checkicon[$i].' /><label for="stat_icon_'.$i.'">&nbsp;<img src="http://tcss.qq.com/icon/toss_1'.$i.'.gif" /></label>&nbsp;&nbsp;';
				if ($i % 4 == 0) {
					$icons .= '</td></tr><tr><td>';
				}
			} elseif ($i < 9) {
				continue;
			} elseif ($i < 11) {
				$icons .= '<input class="radio" type="radio" id="stat_icon_'.$i.'" name="settingnew[cloud_staticon]" value="'.$i.'"'.$checkicon[$i].' /><label for="stat_icon_'.$i.'">&nbsp;'.$lang['cloud_stats_icon_word'.$i].'</label>&nbsp;&nbsp;';
			} else {
				$icons .= '</td></tr><tr><td><input class="radio" type="radio" id="stat_icon_'.$i.'" name="settingnew[cloud_staticon]" value="0"'.$checkicon[0].' /><label for="stat_icon_'.$i.'">&nbsp;'.$lang['cloud_stats_icon_none'].'</label></td></tr>';
			}
		}
		$icons .= '</table>';
		showsetting('cloud_stats_icon_set', '', '', $icons);

		showsubmit('settingsubmit', 'submit');
		showtablefooter();
		showformfooter();

	} else {

		$settingnew = $_GET['settingnew'];
		$settingnew['cloud_staticon'] = intval($settingnew['cloud_staticon']);

		C::t('common_setting')->update('cloud_staticon', $settingnew['cloud_staticon']);
		updatecache('setting');

		cpmsg('setting_update_succeed', 'action=cloud&operation='.$operation.(!empty($_GET['anchor']) ? '&anchor='.$_GET['anchor'] : ''), 'succeed');
	}

} elseif($_GET['anchor'] == 'summary') {

	shownav('navcloud', 'cloud_stats');
	showsubmenu('cloud_stats', $statsnav);

	$statsDomain = 'http://ta.qq.com';
	$utilService = Cloud::loadClass('Service_Util');
	$signUrl = $utilService->generateSiteSignUrl(array('v' => 2));

	$utilService->redirect($statsDomain . '/statsSummary/?' . $signUrl);
}