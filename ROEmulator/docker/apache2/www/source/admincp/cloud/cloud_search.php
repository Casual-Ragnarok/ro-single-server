<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: cloud_search.php 33387 2013-06-05 03:21:26Z jeffjzhang $
 */
if(!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
	exit('Access Denied');
}

$op = $_GET['op'];
$anchor = isset($_GET['anchor']) ? $_GET['anchor'] : 'setting';

$current = array($anchor => 1);

if (!$_G['inajax']) {
	cpheader();
	shownav('navcloud', 'menu_cloud_search');
	showsubmenu('menu_cloud_search', array(
		array(array('menu' => 'search_menu_settingsearch', 'submenu' => array(
			array('search_menu_basicsetting', 'cloud&operation=search&anchor=setting', $current['setting']),
			array('search_menu_modulesetting', 'cloud&operation=search&anchor=modulesetting', $current['modulesetting']),
		)), in_array($anchor, array('setting', 'modulesetting'))),
		array('Iwenwen', 'cloud&operation=search&anchor=iwenwen', $current['iwenwen']),
	));
}

if($anchor == 'modulesetting') {
	if(!submitcheck('modulesetting')) {
		showtips('search_modulesetting_tips');
		showformheader('cloud&operation=search&anchor=modulesetting');
		showtableheader();
		showsetting('search_setting_allow_thread_related', 'cloudsearch_relatedthread', isset($_G['setting']['my_search_data']['allow_thread_related']) ? $_G['setting']['my_search_data']['allow_thread_related'] : 1, 'radio');
		showsetting('search_setting_allow_recommend_related', 'cloudsearch_relatedrecommend', isset($_G['setting']['my_search_data']['allow_recommend_related']) ? $_G['setting']['my_search_data']['allow_recommend_related'] : 1, 'radio');
		showsubmit('modulesetting');
		showtablefooter();
		showformfooter();
	} else {
		$_G['setting']['my_search_data']['allow_thread_related'] = dintval($_POST['cloudsearch_relatedthread']);
		$_G['setting']['my_search_data']['allow_recommend_related'] = dintval($_POST['cloudsearch_relatedrecommend']);
		$updateData = array(
			'my_search_data' => $_G['setting']['my_search_data']
		);
		C::t('common_setting')->update_batch($updateData);
		updatecache('setting');
		cpmsg('setting_update_succeed', 'action=cloud&operation=search&anchor='.$anchor, 'succeed');
	}

} elseif(preg_match('/^[a-z|A-Z|\d]+$/', $anchor)) {
	$utilService = Cloud::loadClass('Service_Util');

	$cp_version = $_G['setting']['my_search_data']['cp_version'];

	$params = array('link_url' => ADMINSCRIPT . '?action=cloud&operation=search', 'cp_version' => $cp_version, 'anchor' => $anchor, 'm_setting' => 1);

	$signUrl = $utilService->generateSiteSignUrl($params);

	$utilService->redirect($cloudDomain . '/search/' . $anchor . '/?' . $signUrl);
}