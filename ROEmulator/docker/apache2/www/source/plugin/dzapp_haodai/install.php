<?php
/**
 * DZAPP Haodai Install Process
 *
 * @copyright (c) 2013 DZAPP. (http://www.dzapp.cn)
 * @author BranchZero <branchzero@gmail.com>
 * @LastModTime 2013/11/22 15:44
 */


if(!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) exit('Access Denied!');
$request_url = str_replace('&step='.$_GET['step'],'',$_SERVER['QUERY_STRING']);
$form_url = str_replace('action=','',$request_url);
showsubmenusteps($installlang['title'], array(
	array($installlang['choose'], $_GET['step']==''),
	array($installlang['reg'], $_GET['step']=='install'),
	array($installlang['succeed'], $_GET['step']=='ok')
));
switch($_GET['step']){
	default:
		C::t('common_plugin')->update($_GET['pluginid'], array('available' => '1'));
		updatecache(array('plugin', 'setting', 'styles'));
		if(stripos($_G['siteurl'], "http://localhost") !== FALSE || stripos($_G['siteurl'], "http://127.0.0.1") !== FALSE) cpmsg($installlang['local_ban'], '', 'error');
		cpmsg($installlang['ifreg'], "{$request_url}&step=install&modetype=1", 'form', array(), '', TRUE, $_G['siteurl']."admin.php?{$request_url}&step=install&modetype=2");
	case 'install':
		if(extension_loaded('curl')){
			if($_GET['modetype'] == '1'){
				if(!submitcheck('submit')){
					showtips($installlang['hd_tip1']);
					showformheader("{$form_url}&step=install&modetype=1");
					showtableheader($installlang['reg_header']);
					showsetting($installlang['email'], 'email', $_G['setting']['adminemail'], 'text', '', '', $installlang['must_fill'].$installlang['email_summary']);
					showsetting($installlang['password'], 'passwd', '', 'password', '', '', $installlang['must_fill'].$installlang['password_summary']);
					showsetting($installlang['repassword'], 'repasswd', '', 'password', '', '', $installlang['must_fill']);
					showsetting($installlang['nickname'], 'nickname', '', 'text', '', '', $installlang['must_fill']);
					showsetting($installlang['contact_name'], 'realname', '', 'text', '', '', $installlang['must_fill'].$installlang['contact_name_summary']);
					showsetting($installlang['contact_tel'], 'tel', '', 'text', '', '', $installlang['must_fill'].$installlang['contact_tel_summary']);
					showsetting('QQ', 'qq', '', 'text', '', '', $installlang['must_fill'].$installlang['qq_summary']);
					showsubmit('submit', 'submit');
					showtablefooter();
					showformfooter();
				}else{
					if(!$_GET['email'] || !$_GET['passwd'] || !$_GET['repasswd'] || !$_GET['nickname'] || !$_GET['realname'] || !$_GET['tel'] || !$_GET['qq']) cpmsg($installlang['not_fill'], '', 'error');
					if($_GET['passwd'] !== $_GET['repasswd']) cpmsg($installlang['psw_not_match'], '', 'error');
					define('HD_API_HOST', 'http://api.haodai.com/');
					include_once DISCUZ_ROOT.'./source/plugin/dzapp_haodai/haodai.api.class.php';
					$client = new HaoDaiClient('1000002', 'sLXuof1JgsqxssYSkJVXqci4MNHftaxB');
					$client->set_debug(0);
					$config = array();
					$data = array(
						'email' => diconv($_GET['email'], CHARSET, 'UTF-8'),
						'tel' => $_GET['tel'],
						'nickname' => diconv($_GET['nickname'], CHARSET, 'UTF-8'),
						'passwd' => diconv($_GET['passwd'], CHARSET, 'UTF-8'),
						'realname' => diconv($_GET['realname'], CHARSET, 'UTF-8'),
						'qq' => $_GET['qq'],
						'domain' => $_G['siteurl'],
						'sitename' => diconv($_G['setting']['sitename'], CHARSET, 'UTF-8')
					);
					$result = $client->register_union_account($data);
					if($result['rs_code'] != '1000'){
						cpmsg($installlang['errmsg']."<br>".diconv($result['rs_msg'], 'UTF-8', CHARSET), '', 'error');
					}
					$config['HD_REF'] = $result['hd_ref'];
					define('HD_REF', $config['HD_REF']);
					$client = new HaoDaiClient('1000002', 'sLXuof1JgsqxssYSkJVXqci4MNHftaxB');
					$client->set_debug(0);
					$data = array(
						'app_name' => diconv($_G['setting']['sitename'].' '.dgmdate($_G['timestamp'], 'Y-m-d-H:i'), CHARSET, 'UTF-8'),
						'site_url' => $_G['siteurl'],
						'desc' => diconv($_G['setting']['sitename'].$installlang['hd_desc'], CHARSET, 'UTF-8'),
						'callback_url' => $_G['siteurl'].'plugin.php?id=dzapp_haodai:callback',
					);
					$result = $client->haodai_app_register($data);
					if($result['rs_code'] != '1000'){
						cpmsg($installlang['errmsg']."<br>".diconv($result['rs_msg'], 'UTF-8', CHARSET), '', 'error');
					}else{
						$config['HD_AKEY'] = $result['hd_akey'];
						$config['HD_SKEY'] = $result['hd_skey'];
						$config['HD_CALLBACK_URL'] = $result['hd_callback_url'];
						$config['HD_API_HOST'] = 'http://api.haodai.com/';
						$configfile = "<?php \r\n";
						foreach($config as $key => $value){
							$configfile .= "define('$key', '$value');\r\n";
						}
						$configfile .= "?>";
						$file = DISCUZ_ROOT."./data/dzapp_haodai_config.php";
						$fp = fopen($file, 'w');
						fwrite($fp, $configfile);
						fclose($fp);
						cpmsg($installlang['install_succeed'], "{$request_url}&step=ok", 'loading', '');
					}
				}
			}elseif($_GET['modetype'] == '2'){
				if(!submitcheck('submit')){
					showtips($installlang['hd_tip2']);
					showformheader("{$form_url}&step=install&modetype=2");
					showtableheader($installlang['reg_header2']);
					showsetting($installlang['ref'], 'ref', '', 'text', '', '', $installlang['must_fill'].$installlang['ref_summary']);
					showsubmit('submit', 'submit');
					showtablefooter();
					showformfooter();
				}else{
					if(!$_GET['ref']) cpmsg($installlang['not_fill'], '', 'error');
					define('HD_API_HOST', 'http://api.haodai.com/');
					define('HD_REF', $_GET['ref']);
					include_once DISCUZ_ROOT.'./source/plugin/dzapp_haodai/haodai.api.class.php';
					$client = new HaoDaiClient('1000002', 'sLXuof1JgsqxssYSkJVXqci4MNHftaxB');
					$client->set_debug(0);
					$config = array();
					$config['HD_REF'] = $_GET['ref'];
					$data = array(
						'app_name' => diconv($_G['setting']['sitename'].' '.dgmdate($_G['timestamp'], 'Y-m-d-H:i'), CHARSET, 'UTF-8'),
						'site_url' => $_G['siteurl'],
						'desc' => diconv($_G['setting']['sitename'].$installlang['hd_desc'], CHARSET, 'UTF-8'),
						'callback_url' => $_G['siteurl'].'plugin.php?id=dzapp_haodai:callback',
					);
					$result = $client->haodai_app_register($data);
					if($result['rs_code'] != '1000'){
						cpmsg($installlang['errmsg']."<br>".diconv($result['rs_msg'], 'UTF-8', CHARSET), '', 'error');
					}else{
						$config['HD_AKEY'] = $result['hd_akey'];
						$config['HD_SKEY'] = $result['hd_skey'];
						$config['HD_CALLBACK_URL'] = $result['hd_callback_url'];
						$config['HD_API_HOST'] = 'http://api.haodai.com/';
						$config['HD_CITY'] = '';
						$configfile = "<?php \r\n";
						foreach($config as $key => $value){
							$configfile .= "define('$key', '$value');\r\n";
						}
						$configfile .= "?>";
						$file = DISCUZ_ROOT."./data/dzapp_haodai_config.php";
						$fp = fopen($file, 'w');
						fwrite($fp, $configfile);
						fclose($fp);
						cpmsg($installlang['install_succeed'], "{$request_url}&step=ok", 'loading', '');
					}
				}
			}
		}else{
			cpmsg($installlang['curl_unsupported'], '', 'error');
		}
		break;
	case 'ok':
		$finish = TRUE;
		break;
}
?>