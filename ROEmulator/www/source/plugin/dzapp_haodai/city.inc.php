<?php

/**
 * DZAPP Haodai City Switcher
 *
 * @copyright (c) 2013-2014 DZAPP. (http://www.dzapp.cn)
 * @author BranchZero <branchzero@gmail.com>
 */

include_once DISCUZ_ROOT.'./data/dzapp_haodai_config.php';
include_once DISCUZ_ROOT.'./source/plugin/dzapp_haodai/haodai.api.class.php';
include_once DISCUZ_ROOT.'./source/plugin/dzapp_haodai/dzapp_haodai.func.php';
@include_once DISCUZ_ROOT.'./data/sysdata/cache_dzapp_haodai_setting.php';
if(!defined('IN_DISCUZ') || !defined('IN_DISCUZ')) {
	exit('Access Denied');
}

if(!@include_once DISCUZ_ROOT.'./data/sysdata/cache_dzapp_haodai_city.php' || TIMESTAMP - filemtime(DISCUZ_ROOT.'./data/sysdata/cache_dzapp_haodai_city.php') > $var['refreshtime']){
	$client = new HaoDaiClient(HD_AKEY, HD_SKEY, $hd_token['access_token']);
	$client->set_debug(0);
	$result = $client->get_xindai_zones();
	$zonelist = $result['items'];
	if(count($zonelist) > 0){
		$zones = array();
		foreach($zonelist as $value){
			if($value['s_EN'] == 'www') continue;
			$value['zone_name'] = diconv($value['zone_name'], 'UTF-8', CHARSET);
			$value['Province'] = diconv($value['Province'], 'UTF-8', CHARSET);
			$value['area'] = diconv($value['area'], 'UTF-8', CHARSET);
			$zones[$value['s_EN']] = $value['zone_name'];
			$zonesort['provinces'][$value['Province']][$value['s_EN']] = $value['zone_name'];
			$zonesort['letter_raw'][$value['letter']][$value['s_EN']] = $value['zone_name'];
			$zonesort['area'][$value['area']][$value['s_EN']] = $value['zone_name'];
		}
		foreach(range('A', 'Z') as $letter) {
			if(!empty($zonesort['letter_raw'][$letter])) $zonesort['letter'][$letter] = $zonesort['letter_raw'][$letter];
		}
		require_once libfile('function/cache');
		writetocache('dzapp_haodai_city', getcachevars(array('zones' => $zones)));
		writetocache('dzapp_haodai_city_sort', getcachevars(array('zonesort' => $zonesort)));
	}else{
		showmessage('dzapp_haodai:callback_error_admin');
	}
}else{
	@include_once DISCUZ_ROOT.'./data/sysdata/cache_dzapp_haodai_city_sort.php';
}

if(!$_GET['city']){
	include template('dzapp_haodai:city');
}else{
	if(!$_GET['city'] || $_GET['formhash'] != FORMHASH || !$zones[$_GET['city']]) showmessage('dzapp_haodai:city_choose_wrong');
	dsetcookie('HD_CITY', $_GET['city']);
	showmessage('dzapp_haodai:city_choose_ok', 'plugin.php?id=dzapp_haodai');
}

?>