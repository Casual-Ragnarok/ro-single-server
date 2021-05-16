<?php
/**
 * DZAPP Haodai Admin Control Panel -- Callback View
 *
 * @copyright (c) 2013 DZAPP. (http://www.dzapp.cn)
 * @author BranchZero <branchzero@gmail.com>
 */

include_once DISCUZ_ROOT.'./data/dzapp_haodai_config.php';
include_once DISCUZ_ROOT.'./source/plugin/dzapp_haodai/haodai.api.class.php';
@include_once DISCUZ_ROOT.'./data/sysdata/cache_dzapp_haodai_setting.php';
if(!defined('IN_DISCUZ') || !defined('IN_DISCUZ')) {
	exit('Access Denied');
}
$lang = array_merge($lang, $scriptlang['dzapp_haodai']);

if(!$_GET['want']){
	showtableheader($lang['callback_info'].' <a href="admin.php?action=plugins&operation=config&identifier=dzapp_haodai&pmod=admincp_callback&want=import">['.$lang['click_to_import'].']</a>');
	showtablerow('', array('class="td24"'), array($lang['appkey'], HD_AKEY));
	showtablerow('', array('class="td24"'), array($lang['appsecret'], HD_SKEY));
	showtablerow('', array('class="td24"'), array($lang['ref'], HD_REF));
	showtablerow('', array('class="td24"'), array($lang['callback_url'], HD_CALLBACK_URL));
	showtablerow('', array('class="td24"'), array($lang['host_url'], HD_API_HOST));
	if(!isset($hd_token)){
		$oauth = new HaoDaiOAuth(HD_AKEY, HD_SKEY);
		$auth_url = $oauth->getAuthorizeURL(HD_CALLBACK_URL);
		showtablerow('', array('class="td24"'), array($lang['callback_status'], '<font color="red">'.$lang['callback_expired'].'</font> <a href="'.$auth_url.'"><img src="source/plugin/dzapp_haodai/images/haodai_login.png" class="vmiddle"></a>'));
	}else{
		if(isset($hd_token['expires']) && TIMESTAMP > $hd_token['expires'] - 3600){
			$client = new HaoDaiClient(HD_AKEY, HD_SKEY);
			$client->set_debug(0);
			$result = $client->haodai_check_AccessToken();
			if($result['rs_code'] != '1000'){
				if($result['rs_code'] == '2100'){
					$new_hd_token = $client->oauth->getAccessToken('token', $hd_token);
					require_once libfile('function/cache');
					writetocache('dzapp_haodai_setting', getcachevars(array('hd_token' => $new_hd_token)));
					showtablerow('', array('class="td24"'), array($lang['callback_status'], '<font color="green">'.$lang['valid'].'</font>'));
				}else{
					$auth_url = $client->oauth->getAuthorizeURL(HD_CALLBACK_URL);
					showtablerow('', array('class="td24"'), array($lang['callback_status'], '<font color="red">'.$lang['callback_expired'].'</font> <a href="'.$auth_url.'"><img src="source/plugin/dzapp_haodai/images/haodai_login.png" class="vmiddle"></a>'));
				}
			}else{
				showtablerow('', array('class="td24"'), array($lang['callback_status'], '<font color="green">'.$lang['valid'].'</font>'));
			}
		}elseif(isset($hd_token['expires']) && TIMESTAMP < $hd_token['expires'] - 3600){
			showtablerow('', array('class="td24"'), array($lang['callback_status'], '<font color="green">'.$lang['valid'].'</font>'));
		}else{
			$oauth = new HaoDaiOAuth(HD_AKEY, HD_SKEY);
			$auth_url = $oauth->getAuthorizeURL(HD_CALLBACK_URL);
			showtablerow('', array('class="td24"'), array($lang['callback_status'], '<font color="red">'.$lang['callback_expired'].'</font> <a href="'.$auth_url.'"><img src="source/plugin/dzapp_haodai/images/haodai_login.png" class="vmiddle"></a>'));
		}
	}
	showtablefooter();
}elseif($_GET['want'] == 'import'){
	if(!submitcheck('ok')){
		showformheader('plugins&operation=config&identifier=dzapp_haodai&pmod=admincp_callback&want=import');
		showtableheader('import_callback');
		showsetting($lang['appkey'], 'AKEY', defined('HD_AKEY') ? HD_AKEY : '', 'text');
		showsetting($lang['appsecret'], 'SKEY', defined('HD_SKEY') ? HD_SKEY : '', 'text');
		showsetting($lang['ref'], 'REF', defined('HD_REF') ? HD_REF : '', 'text');
		showsetting($lang['callback_url'], 'CALLBACK_URL', defined('HD_CALLBACK_URL') ? HD_CALLBACK_URL : $_G['siteurl'].'plugin.php?id=dzapp_haodai:callback', 'text');
		showsetting($lang['host_url'], 'API_HOST', defined('HD_API_HOST') ? HD_API_HOST : 'http://api.haodai.com/', 'text');
		showsubmit('ok', "ok");
		showtablefooter();
		showformfooter();
	}else{
		$config = array();
		$config['HD_REF'] = $_GET['REF'];
		$config['HD_AKEY'] = $_GET['AKEY'];
		$config['HD_SKEY'] = $_GET['SKEY'];
		$config['HD_CALLBACK_URL'] = $_GET['CALLBACK_URL'];
		$config['HD_API_HOST'] = $_GET['API_HOST'];
		$config['HD_CITY'] = defined('HD_CITY') ? HD_CITY : '';
		$configfile = "<?php \r\n";
		foreach($config as $key => $value){
			$configfile .= "define('$key', '$value');\r\n";
		}
		$configfile .= "?>";
		$file = DISCUZ_ROOT."./data/dzapp_haodai_config.php";
		$fp = fopen($file, 'w');
		fwrite($fp, $configfile);
		fclose($fp);
		cpmsg('import_succeed', 'action=plugins&operation=config&identifier=dzapp_haodai&pmod=admincp_callback', 'succeed');
	}
}

?>