<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: spacecp_space.php 25510 2011-11-14 02:22:26Z yexinhao $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

if(submitcheck('delappsubmit')) {

	$setarr = array();
	if($_POST['type'] == 'profilelink') {
		$setarr = array('allowprofilelink' => 0);
	} else {
		$setarr = array('privacy' => 5);
	}
	$appid = intval($_POST['appid']);
	C::t('home_userapp')->update_by_uid_appid($_G['uid'], $appid, $setarr);
	my_userapp_update($_G['uid'], $appid, $setarr['privacy'], $setarr['allowprofilelink']);
	showmessage('do_success', dreferer());
}

if($_GET['op'] == 'delete') {
	$delid = $_GET['type'] == 'profilelink'? 'profilelink_'.$_GET['appid'] : $_GET['appid'];
}
$actives = array($ac => ' class="active"');

include_once template("home/spacecp_space");


function my_userapp_update($uId, $appId, $privacy = null, $allowProfileLink = null) {
	global $my_register_url, $_G;

	$mySiteId = $_G['setting']['my_siteid'];
	$mySiteKey = $_G['setting']['my_sitekey'];
	if (!$_G['setting']['my_app_status']) {
		$res = array('errCode' =>  121,
		'errMessage' => 'Manyou Service Disabled',
		'result'	=> ''
		);
		return $res;
	}

	$data = array();

	if ($privacy !== null) {
		switch($privacy) {
			case 1:
				$data['privacy'] = 'friends';
				break;
			case 3:
				$data['privacy'] = 'me';
				break;
			case 5:
				$data['privacy'] = 'none';
				break;
			case 0:
			default:
				$data['privacy'] = 'public';
		}
	}

	if ($allowProfileLink !== null) {
		$data['allowProfileLink'] = $allowProfileLink ? true : false;
	}

	if (!$data) {
		return array('errCode' => 5, 'errMessage' => 'Post Data Cann\'t Be Empty!');
	}

	$data = serialize($data);
	$key = "$mySiteId|$mySiteKey|$uId|$appId|$data";
	$key = md5($key);
	$data = urlencode($data);

	$postString = sprintf('action=%s&key=%s&mySiteId=%d&uId=%d&appId=%d&data=%s', 'userappUpdate', $key, $mySiteId, $uId, $appId, $data);

	loaducenter();
	$url = 'http://api.manyou.com/uchome.php';
	$response = uc_fopen2($url, 0, $postString, '', false, $_G['setting']['my_ip']);
	$res = unserialize($response);
	if (!$response) {
		$res['errCode'] = 111;
		$res['errMessage'] = 'Empty Response';
		$res['result'] = $response;
	} elseif(!$res) {
		$res['errCode'] = 110;
		$res['errMessage'] = 'Error Response';
		$res['result'] = $response;
	}

	return $res;

}

?>