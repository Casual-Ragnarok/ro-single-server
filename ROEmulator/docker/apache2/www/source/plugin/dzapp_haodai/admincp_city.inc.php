<?php
/**
 * DZAPP Haodai Admin Control Panel -- City List
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
loadcache('plugin');
$lang = array_merge($lang, $scriptlang['dzapp_haodai']);
$var = $_G['cache']['plugin']['dzapp_haodai'];

if(isset($hd_token)){
	if(!submitcheck('submit')){
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
				cpmsg('callback_error_admin', 'action=plugins&operation=config&identifier=dzapp_haodai&pmod=admincp_callback', 'error');
			}
		}
		$catselect = "<select name=\"CITY\">";
		foreach($zones as $key => $value){
			$catselect .= "<option value=\"$key\" ".($key == HD_CITY ? 'selected="selected"' : NULL).">$value</option>\n";
		}
		$catselect .= '</select>';
		showformheader('plugins&operation=config&identifier=dzapp_haodai&pmod=admincp_city');
		showtableheader($lang['choosecity']);
		showsetting($lang['localcity'], '', '', $catselect);
		showtablefooter();
		showsubmit('submit', 'submit');
		showformfooter();
	}else{
		$config = array();
		$config['HD_REF'] = HD_REF;
		$config['HD_AKEY'] = HD_AKEY;
		$config['HD_SKEY'] = HD_SKEY;
		$config['HD_CALLBACK_URL'] = HD_CALLBACK_URL;
		$config['HD_API_HOST'] = HD_API_HOST;
		$config['HD_CITY'] = $_GET['CITY'];
		$configfile = "<?php \r\n";
		foreach($config as $key => $value){
			$configfile .= "define('$key', '$value');\r\n";
		}
		$configfile .= "?>";
		$file = DISCUZ_ROOT."./data/dzapp_haodai_config.php";
		$fp = fopen($file, 'w');
		fwrite($fp, $configfile);
		fclose($fp);
		cpmsg('setting_succeed', 'action=plugins&operation=config&identifier=dzapp_haodai&pmod=admincp_city', 'succeed');
	}
}else{
	cpmsg('callback_error_admin', 'action=plugins&operation=config&identifier=dzapp_haodai&pmod=admincp_callback', 'error');
}

?>