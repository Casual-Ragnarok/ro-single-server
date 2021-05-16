<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms'
 *
 *      $Id: admincp_plugins.php 32011 2012-10-31 02:20:10Z monkey $
 */

if(!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
	exit('Access Denied');
}

cpheader();

if(!empty($_GET['identifier']) && !empty($_GET['pmod'])) {
	$operation = 'config';
}

if($operation != 'config' && !$admincp->isfounder) {
	cpmsg('noaccess_isfounder', '', 'error');
}

$pluginid = !empty($_GET['pluginid']) ? intval($_GET['pluginid']) : 0;
$anchor = !empty($_GET['anchor']) ? $_GET['anchor'] : '';
$isplugindeveloper = isset($_G['config']['plugindeveloper']) && $_G['config']['plugindeveloper'] > 0;
if(!empty($_GET['dir']) && !ispluginkey($_GET['dir'])) {
	unset($_GET['dir']);
}

require_once libfile('function/plugin');

if(!$operation) {

	if(!submitcheck('submit')) {

		loadcache('plugin');
		shownav('plugin');
		showsubmenu('nav_plugins', array(
			array(array('menu' => 'plugins_list', 'submenu' => array(
				array('plugins_list', 'plugins', empty($_GET['system'])),
				array('plugins_system', 'plugins&system=1', !empty($_GET['system']))
			)), 1),
			$isplugindeveloper ? array('plugins_add', 'plugins&operation=add', 0) : array(),
			array('cloudaddons_plugin_link', 'cloudaddons'),
		), '<a href="'.ADMINSCRIPT.'?action=plugins&operation=upgradecheck" class="bold" style="float:right;padding-right:40px;">'.$lang['plugins_validator'].'</a>');
		showformheader('plugins');
		showtableheader();
		$outputsubmit = false;
		$plugins = $addonids = array();
		$plugins = C::t('common_plugin')->fetch_all_data();
		if(empty($_G['cookie']['addoncheck_plugin'])) {
			foreach($plugins as $plugin) {
				$addonids[$plugin['pluginid']] = $plugin['identifier'].'.plugin';
			}
			$checkresult = dunserialize(cloudaddons_upgradecheck($addonids));
			savecache('addoncheck_plugin', $checkresult);
			dsetcookie('addoncheck_plugin', 1, 3600);
		} else {
			loadcache('addoncheck_plugin');
			$checkresult = $_G['cache']['addoncheck_plugin'];
		}
		$splitavailable = array();
		foreach($plugins as $plugin) {
			$addonid = $plugin['identifier'].'.plugin';
			$updateinfo = '';
			list(, $newver) = explode(':', $checkresult[$addonid]);
			if($newver) {
				$updateinfo = '<a href="'.ADMINSCRIPT.'?action=cloudaddons&id='.$addonid.'" title="'.$lang['plugins_online_update'].'"><font color="red">'.$lang['plugins_find_newversion'].' '.$newver.'</font></a>';
			}
			$plugins[] = $plugin['identifier'];
			$hookexists = FALSE;
			$plugin['modules'] = dunserialize($plugin['modules']);
			if((empty($_GET['system']) && $plugin['modules']['system'] && !$updateinfo || !empty($_GET['system']) && !$plugin['modules']['system'])) {
				continue;
			}
			$submenuitem = array();
			if(is_array($plugin['modules'])) {
				foreach($plugin['modules'] as $k => $module) {
					if($module['type'] == 11) {
						$hookorder = $module['displayorder'];
						$hookexists = $k;
					}
					if($module['type'] == 3) {
						$submenuitem[] = '<a href="'.ADMINSCRIPT.'?action=plugins&operation=config&do='.$plugin['pluginid'].'&identifier='.$plugin['identifier'].'&pmod='.$module['name'].'">'.$module['menu'].'</a>';
					}
				}
			}
			$outputsubmit = $hookexists !== FALSE && $plugin['available'] || $outputsubmit;
			$hl = !empty($_GET['hl']) && $_GET['hl'] == $plugin['pluginid'];
			$intro = $title = '';
			$order = !$updateinfo ? intval($plugin['modules']['system']) + 1 : 0;
			if($plugin['available']) {
				if(empty($splitavailable[0])) {
					$title = '<tr><th colspan="15" class="partition">'.cplang('plugins_list_available').'</th></tr>';
					$splitavailable[0] = 1;
				}
			} else {
				if(empty($splitavailable[1])) {
					$title = '<tr><th colspan="15" class="partition">'.cplang('plugins_list_unavailable').'</th></tr>';
					$splitavailable[1] = 1;
				}
			}
			$pluginlist[$order][$plugin['pluginid']] = $title.showtablerow('class="hover'.($hl ? ' hl' : '').'"', array('valign="top" style="width:45px"', ($plugin['available'] ? 'class="bold"' : 'class="light"').' valign="top" style="width:200px"', 'valign="bottom"', 'align="right" valign="bottom" style="width:160px"'), array(
				'<img src="'.cloudaddons_pluginlogo_url($plugin['identifier']).'" onerror="this.src=\'static/image/admincp/plugin_logo.png\';this.onerror=null" width="40" height="40" align="left" />',
				dhtmlspecialchars($plugin['name']).' '.dhtmlspecialchars($plugin['version']).'<br /><span class="sml">'.$plugin['identifier'].'</span><br />'.$updateinfo,
				($plugin['description'] ? $plugin['description'].'<br /><br />' : '').
					($plugin['copyright'] ? '<span class="light">'.cplang('author').': '.dhtmlspecialchars($plugin['copyright']).'</span>' : '').'<div class="psetting">'.
						'<a href="'.ADMINSCRIPT.'?action=cloudaddons&id='.$plugin['identifier'].'.plugin" target="_blank" title="'.$lang['cloudaddons_linkto'].'">'.$lang['view'].'</a>'.
						($plugin['modules']['extra']['intro'] ? '<a href="javascript:;" onclick="display(\'intro_'.$plugin['pluginid'].'\')">'.cplang('plugins_home').'</a>' : '').
						(isset($_G['cache']['plugin'][$plugin['identifier']]) ? '<a href="'.ADMINSCRIPT.'?action=plugins&operation=config&do='.$plugin['pluginid'].'">'.$lang['config'].'</a>' : '').
						implode('', $submenuitem).'</div>',
				($hookexists !== FALSE && $plugin['available'] ? $lang['display_order'].": <input class=\"txt num\" type=\"text\" id=\"displayorder_$plugin[pluginid]\" name=\"displayordernew[$plugin[pluginid]][$hookexists]\" value=\"$hookorder\" /><br /><br />" : '').
				($plugin['modules']['system'] != 2 ? (!$plugin['available'] ? "<a href=\"".ADMINSCRIPT."?action=plugins&operation=enable&pluginid=$plugin[pluginid]\" class=\"bold\">$lang[enable]</a>&nbsp;&nbsp;" : "<a href=\"".ADMINSCRIPT."?action=plugins&operation=disable&pluginid=$plugin[pluginid]\">$lang[closed]</a>&nbsp;&nbsp;") : '').
					"<a href=\"".ADMINSCRIPT."?action=plugins&operation=upgrade&pluginid=$plugin[pluginid]\">$lang[plugins_config_upgrade]</a>&nbsp;&nbsp;".
					(!$plugin['modules']['system'] ? "<a href=\"".ADMINSCRIPT."?action=plugins&operation=delete&pluginid=$plugin[pluginid]\">$lang[plugins_config_uninstall]</a>&nbsp;&nbsp;" : '').
					($isplugindeveloper && !$plugin['modules']['system'] ? "<a href=\"".ADMINSCRIPT."?action=plugins&operation=edit&pluginid=$plugin[pluginid]\">$lang[plugins_editlink]</a>&nbsp;&nbsp;" : ''),
			), true).($plugin['modules']['extra']['intro'] ? showtablerow('class="noborder" id="intro_'.$plugin['pluginid'].'" style="display:none"', array('', 'colspan="3"'), array('', $plugin['modules']['extra']['intro']), true) : '');
		}
		ksort($pluginlist);
		$pluginlist = (array)$pluginlist[0] + (array)$pluginlist[1] + (array)$pluginlist[2] + (array)$pluginlist[3];
		if(!empty($_GET['hl']) && isset($pluginlist[$_GET['hl']])) {
			$hl = $pluginlist[$_GET['hl']];
			unset($pluginlist[$_GET['hl']]);
			array_unshift($pluginlist, $hl);
		}
		echo implode('', $pluginlist);

		if(empty($_GET['system'])) {
			$plugindir = DISCUZ_ROOT.'./source/plugin';
			$pluginsdir = dir($plugindir);
			$newplugins = array();
			showtableheader();
			$newlist = '';
			while($entry = $pluginsdir->read()) {
				if(!in_array($entry, array('.', '..')) && is_dir($plugindir.'/'.$entry) && !in_array($entry, $plugins)) {
					$entrydir = DISCUZ_ROOT.'./source/plugin/'.$entry;
					$d = dir($entrydir);
					$filemtime = filemtime($entrydir);
					$entrytitle = $entry;
					$entryversion = $entrycopyright = $importtxt = '';
					$extra = currentlang();
					$extra = $extra ? '_'.$extra : '';
					if(file_exists($entrydir.'/discuz_plugin_'.$entry.$extra.'.xml')) {
						$importtxt = @implode('', file($entrydir.'/discuz_plugin_'.$entry.$extra.'.xml'));
					} elseif(file_exists($entrydir.'/discuz_plugin_'.$entry.'.xml')) {
						$importtxt = @implode('', file($entrydir.'/discuz_plugin_'.$entry.'.xml'));
					}
					if($importtxt) {
						$pluginarray = getimportdata('Discuz! Plugin', 0, 1);
						if(!empty($pluginarray['plugin']['name'])) {
							$entrytitle = dhtmlspecialchars($pluginarray['plugin']['name']);
							$entryversion = dhtmlspecialchars($pluginarray['plugin']['version']);
							$entrycopyright = dhtmlspecialchars($pluginarray['plugin']['copyright']);
						}
						$file = $entrydir.'/'.$f;
						$newlist .= showtablerow('class="hover"', array('style="width:45px"', 'class="light" valign="top" style="width:200px"', 'valign="bottom"', 'align="right" valign="bottom" style="width:160px"'), array(
							'<img src="'.cloudaddons_pluginlogo_url($entry).'" onerror="this.src=\'static/image/admincp/plugin_logo.png\';this.onerror=null" width="40" height="40" align="left" style="margin-right:5px" />',
							$entrytitle.' '.$entryversion.($filemtime > TIMESTAMP - 86400 ? ' <font color="red">New!</font>' : '').'</span><br /><span class="sml light">'.$entry.'</span>',
							'<span class="light">'.cplang('author').': '.$entrycopyright.'</span>'.
							'<div class="psetting light"><a href="'.ADMINSCRIPT.'?action=cloudaddons&id='.$entry.'.plugin" target="_blank" title="'.$lang['cloudaddons_linkto'].'">'.$lang['view'].'</a></div>',
							'<a href="'.ADMINSCRIPT.'?action=plugins&operation=import&dir='.$entry.'" class="bold">'.$lang['plugins_config_install'].'</a>'
						), true);
					}
				}
			}
			if($newlist) {
				showtitle('plugins_list_new');
				echo $newlist;
			}
		}

		if($outputsubmit) {
			showsubmit('submit', 'submit', '', '<a href="'.ADMINSCRIPT.'?action=cloudaddons">'.cplang('cloudaddons_plugin_link').'</a>');
		} else {
			showsubmit('', '', '', '<a href="'.ADMINSCRIPT.'?action=cloudaddons">'.cplang('cloudaddons_plugin_link').'</a>');
		}
		showtablefooter();
		showformfooter();

	} else {

		foreach(C::t('common_plugin')->fetch_all_data(1) as $plugin) {
			if(!empty($_GET['displayordernew'][$plugin['pluginid']])) {
				$plugin['modules'] = dunserialize($plugin['modules']);
				$k = array_keys($_GET['displayordernew'][$plugin['pluginid']]);
				$v = array_values($_GET['displayordernew'][$plugin['pluginid']]);
				$plugin['modules'][$k[0]]['displayorder'] = $v[0];
				C::t('common_plugin')->update($plugin['pluginid'], array('modules' => serialize($plugin['modules'])));
			}
		}

		updatecache(array('plugin', 'setting', 'styles'));
		cleartemplatecache();

		cpmsg('plugins_edit_succeed', 'action=plugins', 'succeed');

	}

} elseif($operation == 'enable' || $operation == 'disable') {

	$conflictplugins = '';
	if($operation == 'enable') {
		$plugin = C::t('common_plugin')->fetch($_GET['pluginid']);
		if(!$plugin) {
			cpmsg('plugin_not_found', '', 'error');
		}

		require_once libfile('cache/setting', 'function');
		list(,, $hookscript) = get_cachedata_setting_plugin($plugin['identifier']);
		$exists = array();
		foreach($hookscript as $script => $modules) {
			foreach($modules as $module => $data) {
				foreach(array('funcs' => '', 'outputfuncs' => '_output', 'messagefuncs' => '_message') as $functype => $funcname) {
					foreach($data[$functype] as $k => $funcs) {
						$pluginids = array();
						foreach($funcs as $func) {
							$pluginids[$func[0]] = $func[0];
						}
						if(in_array($plugin['identifier'], $pluginids) && count($pluginids) > 1) {
							unset($pluginids[$plugin['identifier']]);
							foreach($pluginids as $pluginid) {
								$exists[$pluginid][$k.$funcname] = $k.$funcname;
							}
						}
					}
				}
			}
		}
		if($exists) {
			$plugins = array();
			foreach(C::t('common_plugin')->fetch_all_by_identifier(array_keys($exists)) as $plugin) {
				$plugins[] = '<b>'.$plugin['name'].'</b>:'.
					'&nbsp;<a href="javascript:;" onclick="display(\'conflict_'.$plugin['identifier'].'\')">'.cplang('plugins_conflict_view').'</a>'.
					'&nbsp;<a href="'.cloudaddons_pluginlogo_url($plugin['identifier']).'" target="_blank">'.cplang('plugins_conflict_info').'</a>'.
					'<span id="conflict_'.$plugin['identifier'].'" style="display:none"><br />'.implode(',', $exists[$plugin['identifier']]).'</span>';
			}
			$conflictplugins = '<div align="left" style="margin: auto 100px; border: 1px solid #DEEEFA;padding: 4px;line-height: 25px;">'.implode('<br />', $plugins).'</div>';
		}
	}
	$available = $operation == 'enable' ? 1 : 0;
	C::t('common_plugin')->update($_GET['pluginid'], array('available' => $available));
	updatecache(array('plugin', 'setting', 'styles'));
	cleartemplatecache();
	updatemenu('plugin');
	if($operation == 'enable') {
		if(!$conflictplugins) {
			cpmsg('plugins_enable_succeed', 'action=plugins', 'succeed');
		} else {
			cpmsg('plugins_conflict', 'action=plugins', 'succeed', array('plugins' => $conflictplugins));
		}
	} else {
		cpmsg('plugins_disable_succeed', 'action=plugins', 'succeed');
	}
	cpmsg('plugins_'.$operation.'_succeed', 'action=plugins', 'succeed');

} elseif($operation == 'export' && $pluginid) {

	if(!$isplugindeveloper) {
		cpmsg('undefined_action', '', 'error');
	}

	$plugin = C::t('common_plugin')->fetch($pluginid);
	if(!$plugin) {
		cpheader();
		cpmsg('plugin_not_found', '', 'error');
	}

	unset($plugin['pluginid']);

	$pluginarray = array();
	$pluginarray['plugin'] = $plugin;
	$pluginarray['version'] = strip_tags($_G['setting']['version']);

	foreach(C::t('common_pluginvar')->fetch_all_by_pluginid($pluginid) as $var) {
		unset($var['pluginvarid'], $var['pluginid']);
		$pluginarray['var'][] = $var;
	}
	$modules = dunserialize($pluginarray['plugin']['modules']);
	if($modules['extra']['langexists'] && file_exists($file = DISCUZ_ROOT.'./data/plugindata/'.$pluginarray['plugin']['identifier'].'.lang.php')) {
		include $file;
		if(!empty($scriptlang[$pluginarray['plugin']['identifier']])) {
			$pluginarray['language']['scriptlang'] = $scriptlang[$pluginarray['plugin']['identifier']];
		}
		if(!empty($templatelang[$pluginarray['plugin']['identifier']])) {
			$pluginarray['language']['templatelang'] = $templatelang[$pluginarray['plugin']['identifier']];
		}
		if(!empty($installlang[$pluginarray['plugin']['identifier']])) {
			$pluginarray['language']['installlang'] = $installlang[$pluginarray['plugin']['identifier']];
		}
	}
	unset($modules['extra']);
	$pluginarray['plugin']['modules'] = serialize($modules);
	$plugindir = DISCUZ_ROOT.'./source/plugin/'.$pluginarray['plugin']['directory'];
	if(file_exists($plugindir.'/install.php')) {
		$pluginarray['installfile'] = 'install.php';
	}
	if(file_exists($plugindir.'/uninstall.php')) {
		$pluginarray['uninstallfile'] = 'uninstall.php';
	}
	if(file_exists($plugindir.'/upgrade.php')) {
		$pluginarray['upgradefile'] = 'upgrade.php';
	}
	if(file_exists($plugindir.'/check.php')) {
		$pluginarray['checkfile'] = 'check.php';
	}

	exportdata('Discuz! Plugin', $plugin['identifier'], $pluginarray);

} elseif($operation == 'import') {

	if(submitcheck('importsubmit') || isset($_GET['dir'])) {
		cloudaddons_validator($_GET['dir'].'.plugin');

		if(!isset($_GET['dir'])) {
			$pluginarray = getimportdata('Discuz! Plugin');
		} elseif(!isset($_GET['installtype'])) {
			$pdir = DISCUZ_ROOT.'./source/plugin/'.$_GET['dir'];
			$d = dir($pdir);
			$xmls = '';
			$count = 0;
			$noextra = false;
			$currentlang = currentlang();
			while($f = $d->read()) {
				if(preg_match('/^discuz\_plugin_'.$_GET['dir'].'(\_\w+)?\.xml$/', $f, $a)) {
					$extratxt = $extra = substr($a[1], 1);
					if($extra) {
						if($currentlang && $currentlang == $extra) {
							dheader('location: '.ADMINSCRIPT.'?action=plugins&operation=import&dir='.$_GET['dir'].'&installtype='.rawurlencode($extra));
						}
					} else {
						$noextra = true;
					}
					$url = ADMINSCRIPT.'?action=plugins&operation=import&dir='.$_GET['dir'].'&installtype='.rawurlencode($extra);
					$xmls .= '&nbsp;<input type="button" class="btn" onclick="location.href=\''.$url.'\'" value="'.($extra ? $extratxt : $lang['plugins_import_default']).'">&nbsp;';
					$count++;
				}
			}
			if($count == 1 && $noextra) {
				dheader('location: '.ADMINSCRIPT.'?action=plugins&operation=import&dir='.$_GET['dir'].'&installtype=');
			}
			$xmls .= '<br /><br /><input class="btn" onclick="location.href=\''.ADMINSCRIPT.'?action=plugins\'" type="button" value="'.$lang['cancel'].'"/>';
			echo '<div class="infobox"><h4 class="infotitle2">'.$lang['plugins_import_installtype_1'].' '.$_GET['dir'].' '.$lang['plugins_import_installtype_2'].' '.$count.' '.$lang['plugins_import_installtype_3'].'</h4>'.$xmls.'</div>';
			exit;
		} else {
			$installtype = $_GET['installtype'];
			$dir = $_GET['dir'];
			$license = $_GET['license'];
			$extra = $installtype ? '_'.$installtype : '';
			$importfile = DISCUZ_ROOT.'./source/plugin/'.$dir.'/discuz_plugin_'.$dir.$extra.'.xml';
			$importtxt = @implode('', file($importfile));
			$pluginarray = getimportdata('Discuz! Plugin');
			if(empty($license) && $pluginarray['license']) {
				require_once libfile('function/discuzcode');
				$pluginarray['license'] = discuzcode(strip_tags($pluginarray['license']), 1, 0);
				echo '<div class="infobox"><h4 class="infotitle2">'.$pluginarray['plugin']['name'].' '.$pluginarray['plugin']['version'].' '.$lang['plugins_import_license'].'</h4><div style="text-align:left;line-height:25px;">'.$pluginarray['license'].'</div><br /><br /><center>'.
					'<button onclick="location.href=\''.ADMINSCRIPT.'?action=plugins&operation=import&dir='.$dir.'&installtype='.$installtype.'&license=yes\'">'.$lang['plugins_import_agree'].'</button>&nbsp;&nbsp;'.
					'<button onclick="location.href=\''.ADMINSCRIPT.'?action=plugins\'">'.$lang['plugins_import_pass'].'</button></center></div>';
				exit;
			}
		}

		if(!ispluginkey($pluginarray['plugin']['identifier'])) {
			cpmsg('plugins_edit_identifier_invalid', 'action=plugins', 'error');
		}
		if(is_array($pluginarray['vars'])) {
			foreach($pluginarray['vars'] as $config) {
				if(!ispluginkey($config['variable'])) {
					cpmsg('plugins_import_var_invalid', 'action=plugins', 'error');
				}
			}
		}

		$plugin = C::t('common_plugin')->fetch_by_identifier($pluginarray['plugin']['identifier']);
		if($plugin) {
			cpmsg('plugins_import_identifier_duplicated', 'action=plugins', 'error', array('plugin_name' => $plugin['name']));
		}

		if(!empty($pluginarray['checkfile']) && preg_match('/^[\w\.]+$/', $pluginarray['checkfile'])) {
			$filename = DISCUZ_ROOT.'./source/plugin/'.$_GET['dir'].'/'.$pluginarray['checkfile'];
			if(file_exists($filename)) {
				loadcache('pluginlanguage_install');
				$installlang = $pluginarray['language']['installlang'];
				@include $filename;
			}
		}

		if(empty($_GET['ignoreversion']) && !versioncompatible($pluginarray['version'])) {
			if(isset($dir)) {
				cpmsg('plugins_import_version_invalid_confirm', 'action=plugins&operation=import&ignoreversion=yes&dir='.$dir.'&installtype='.$installtype.'&license='.$license, 'form', array('cur_version' => $pluginarray['version'], 'set_version' => $_G['setting']['version']), '', true, ADMINSCRIPT.'?action=plugins');
			} else {
				cpmsg('plugins_import_version_invalid', 'action=plugins', 'error', array('cur_version' => $pluginarray['version'], 'set_version' => $_G['setting']['version']));
			}
		}

		$pluginid = plugininstall($pluginarray, $installtype);

		updatemenu('plugin');

		if(!empty($dir) && !empty($pluginarray['installfile']) && preg_match('/^[\w\.]+$/', $pluginarray['installfile'])) {
			dheader('location: '.ADMINSCRIPT.'?action=plugins&operation=plugininstall&dir='.$dir.'&installtype='.$installtype.'&pluginid='.$pluginid);
		}

		if(!empty($dir)) {
			cpmsg('plugins_install_succeed', 'action=plugins&hl='.$pluginid, 'succeed');
		} else {
			cpmsg('plugins_import_succeed', 'action=plugins&hl='.$pluginid, 'succeed');
		}

	}

} elseif($operation == 'plugininstall' || $operation == 'pluginuninstall' || $operation == 'pluginupgrade') {

	$finish = FALSE;
	$dir = $_GET['dir'];
	$installtype = str_replace('/', '', $_GET['installtype']);
	$extra = $installtype ? '_'.$installtype : '';
	$xmlfile = 'discuz_plugin_'.$dir.$extra.'.xml';
	$importfile = DISCUZ_ROOT.'./source/plugin/'.$dir.'/'.$xmlfile;
	if(!file_exists($importfile)) {
		cpmsg('plugin_file_error', '', 'error');
	}
	$importtxt = @implode('', file($importfile));
	$pluginarray = getimportdata('Discuz! Plugin');
	if($operation == 'plugininstall') {
		$filename = $pluginarray['installfile'];
	} elseif($operation == 'pluginuninstall') {
		$filename = $pluginarray['uninstallfile'];
	} else {
		$filename = $pluginarray['upgradefile'];
		$toversion = $pluginarray['plugin']['version'];
	}
	loadcache('pluginlanguage_install');
	$installlang = $_G['cache']['pluginlanguage_install'][$dir];

	if(!empty($filename) && preg_match('/^[\w\.]+$/', $filename)) {
		$filename = DISCUZ_ROOT.'./source/plugin/'.$dir.'/'.$filename;
		if(file_exists($filename)) {
			@include_once $filename;
		} else {
			$finish = TRUE;
		}
	} else {
		$finish = TRUE;
	}

	if($finish) {
		updatecache('setting');
		updatemenu('plugin');
		if($operation == 'plugininstall') {
			cpmsg('plugins_install_succeed', 'action=plugins&hl='.$_GET['pluginid'], 'succeed');
		} if($operation == 'pluginuninstall') {
			loadcache('pluginlanguage_install', 1);
			if(!empty($_G['cache']['pluginlanguage_install']) && isset($_G['cache']['pluginlanguage_install'][$identifier])) {
				unset($_G['cache']['pluginlanguage_install'][$identifier]);
				savecache('pluginlanguage_install', $_G['cache']['pluginlanguage_install']);
			}
			cloudaddons_uninstall($dir.'.plugin', DISCUZ_ROOT.'./source/plugin/'.$dir);
			cpmsg('plugins_delete_succeed', "action=plugins", 'succeed');
		} else {
			cpmsg('plugins_upgrade_succeed', "action=plugins", 'succeed', array('toversion' => $toversion));
		}
	}

} elseif($operation == 'upgrade') {

	$plugin = C::t('common_plugin')->fetch($pluginid);
	$modules = dunserialize($plugin['modules']);
	$dir = substr($plugin['directory'], 0, -1);

	if(!$_GET['confirmed']) {

		$file = DISCUZ_ROOT.'./source/plugin/'.$dir.'/discuz_plugin_'.$dir.($modules['extra']['installtype'] ? '_'.$modules['extra']['installtype'] : '').'.xml';
		$upgrade = false;
		if(file_exists($file)) {
			$importtxt = @implode('', file($file));
			$pluginarray = getimportdata('Discuz! Plugin');
			$newver = !empty($pluginarray['plugin']['version']) ? $pluginarray['plugin']['version'] : 0;
			$upgrade = $newver > $plugin['version'] ? true : false;
		}
		$entrydir = DISCUZ_ROOT.'./source/plugin/'.$dir;
		$upgradestr = '';
		if(file_exists($entrydir)) {
			$d = dir($entrydir);
			while($f = $d->read()) {
				if(preg_match('/^discuz\_plugin\_'.$plugin['identifier'].'(\_\w+)?\.xml$/', $f, $a)) {
					$extratxt = $extra = substr($a[1], 1);
					if(preg_match('/^SC\_GBK$/i', $extra)) {
						$extratxt = '&#31616;&#20307;&#20013;&#25991;&#29256;';
					} elseif(preg_match('/^SC\_UTF8$/i', $extra)) {
						$extratxt = '&#31616;&#20307;&#20013;&#25991;&#85;&#84;&#70;&#56;&#29256;';
					} elseif(preg_match('/^TC\_BIG5$/i', $extra)) {
						$extratxt = '&#32321;&#39636;&#20013;&#25991;&#29256;';
					} elseif(preg_match('/^TC\_UTF8$/i', $extra)) {
						$extratxt = '&#32321;&#39636;&#20013;&#25991;&#85;&#84;&#70;&#56;&#29256;';
					}
					if($modules['extra']['installtype'] == $extratxt) {
						continue;
					}
					$importtxt = @implode('', file($entrydir.'/'.$f));
					$pluginarray = getimportdata('Discuz! Plugin');
					$newverother = !empty($pluginarray['plugin']['version']) ? $pluginarray['plugin']['version'] : 0;
					$upgradestr .= $newverother > $plugin['version'] ? '<input class="btn" onclick="location.href=\''.ADMINSCRIPT.'?action=plugins&operation=upgrade&pluginid='.$pluginid.'&confirmed=yes&installtype='.rawurlencode($extra).'\'" type="button" value="'.($extra ? $extratxt : $lang['plugins_import_default']).' '.$newverother.'" />&nbsp;&nbsp;&nbsp;' : '';
				}
			}
		}
		if(!empty($pluginarray['checkfile']) && preg_match('/^[\w\.]+$/', $pluginarray['checkfile'])) {
			$filename = DISCUZ_ROOT.'./source/plugin/'.$plugin['identifier'].'/'.$pluginarray['checkfile'];
			if(file_exists($filename)) {
				loadcache('pluginlanguage_install');
				$installlang = $_G['cache']['pluginlanguage_install'][$plugin['identifier']];
				@include $filename;
			}
		}

		if($upgrade) {

			cpmsg('plugins_config_upgrade_confirm', 'action=plugins&operation=upgrade&pluginid='.$pluginid.'&confirm=yes', 'form', array('pluginname' => $plugin['name'], 'version' => $plugin['version'], 'toversion' => $newver));

		} elseif($upgradestr) {

			echo '<h3>'.cplang('discuz_message').'</h3><div class="infobox"><h4 class="marginbot normal">'.cplang('plugins_config_upgrade_other', array('pluginname' => $plugin['name'], 'version' => $plugin['version'])).'</h4><br /><p class="margintop">'.$upgradestr.
				'<input class="btn" onclick="location.href=\''.ADMINSCRIPT.'?action=plugins\'" type="button" value="'.$lang['cancel'].'"/></div></div>';

		} else {

			$addonid = $plugin['identifier'].'.plugin';
			$checkresult = dunserialize(cloudaddons_upgradecheck(array($addonid)));

			list($return, $newver) = explode(':', $checkresult[$addonid]);

			cloudaddons_installlog($pluginarray['plugin']['identifier'].'.plugin');
			dsetcookie('addoncheck_plugin', '', -1);

			if($newver) {
				cpmsg('plugins_config_upgrade_new', '', 'succeed', array('newver' => $newver, 'addonid' => $addonid));
			} else {
				cpmsg('plugins_config_upgrade_missed', 'action=plugins', 'succeed');
			}

		}

	} else {

		$installtype = !isset($_GET['installtype']) ? $modules['extra']['installtype'] : (preg_match('/^\w+$/', $_GET['installtype']) ? $_GET['installtype'] : '');
		$importfile = DISCUZ_ROOT.'./source/plugin/'.$dir.'/discuz_plugin_'.$dir.($installtype ? '_'.$installtype : '').'.xml';
		if(!file_exists($importfile)) {
			cpmsg('plugin_file_error', '', 'error');
		}

		cloudaddons_validator($dir.'.plugin');

		$importtxt = @implode('', file($importfile));
		$pluginarray = getimportdata('Discuz! Plugin');

		if(!ispluginkey($pluginarray['plugin']['identifier']) || $pluginarray['plugin']['identifier'] != $plugin['identifier']) {
			cpmsg('plugins_edit_identifier_invalid', '', 'error');
		}
		if(is_array($pluginarray['vars'])) {
			foreach($pluginarray['vars'] as $config) {
				if(!ispluginkey($config['variable'])) {
					cpmsg('plugins_upgrade_var_invalid', '', 'error');
				}
			}
		}

		if(!empty($pluginarray['checkfile']) && preg_match('/^[\w\.]+$/', $pluginarray['checkfile'])) {
			if(!empty($pluginarray['language'])) {
				$installlang[$pluginarray['plugin']['identifier']] = $pluginarray['language']['installlang'];
			}
			$filename = DISCUZ_ROOT.'./source/plugin/'.$plugin['directory'].$pluginarray['checkfile'];
			if(file_exists($filename)) {
				loadcache('pluginlanguage_install');
				$installlang = $_G['cache']['pluginlanguage_install'][$plugin['identifier']];
				@include $filename;
			}
		}

		pluginupgrade($pluginarray, $installtype);

		if(!empty($plugin['directory']) && !empty($pluginarray['upgradefile']) && preg_match('/^[\w\.]+$/', $pluginarray['upgradefile'])) {
			dheader('location: '.ADMINSCRIPT.'?action=plugins&operation=pluginupgrade&dir='.$dir.'&installtype='.$modules['extra']['installtype'].'&fromversion='.$plugin['version']);
		}
		$toversion = $pluginarray['plugin']['version'];

		cpmsg('plugins_upgrade_succeed', "action=plugins", 'succeed', array('toversion' => $toversion));

	}

} elseif($operation == 'config') {

	if(empty($pluginid) && !empty($do)) {
		$pluginid = $do;
	}
	if($_GET['identifier']) {
		$plugin = C::t('common_plugin')->fetch_by_identifier($_GET['identifier']);
	} else {
		$plugin = C::t('common_plugin')->fetch($pluginid);
	}
	if(!$plugin) {
		cpmsg('plugin_not_found', '', 'error');
	} else {
		$pluginid = $plugin['pluginid'];
	}

	cloudaddons_validator($plugin['identifier'].'.plugin');

	$plugin['modules'] = dunserialize($plugin['modules']);

	$pluginvars = array();
	foreach(C::t('common_pluginvar')->fetch_all_by_pluginid($pluginid) as $var) {
		if(strexists($var['type'], '_')) {
			continue;
		}
		$pluginvars[$var['variable']] = $var;
	}

	if($pluginvars) {
		$submenuitem[] = array('config', "plugins&operation=config&do=$pluginid", !$_GET['pmod']);
	}
	if(is_array($plugin['modules'])) {
		foreach($plugin['modules'] as $module) {
			if($module['type'] == 3) {
				if(!$pluginvars && empty($_GET['pmod'])) {
					$_GET['pmod'] = $module['name'];
				}
				$submenuitem[] = array($module['menu'], "plugins&operation=config&do=$pluginid&identifier=$plugin[identifier]&pmod=$module[name]", $_GET['pmod'] == $module['name'], !$_GET['pmod'] ? 1 : 0);
			}
		}
	}

	if(empty($_GET['pmod'])) {

		if(!submitcheck('editsubmit')) {
			$operation = '';
			shownav('plugin', $plugin['name']);
			showsubmenuanchors($plugin['name'].' '.$plugin['version'].(!$plugin['available'] ? ' ('.$lang['plugins_unavailable'].')' : ''), $submenuitem);

			if($pluginvars) {
				showformheader("plugins&operation=config&do=$pluginid");
				showtableheader();
				showtitle($lang['plugins_config']);

				$extra = array();
				foreach($pluginvars as $var) {
					if(strexists($var['type'], '_')) {
						continue;
					}
					$var['variable'] = 'varsnew['.$var['variable'].']';
					if($var['type'] == 'number') {
						$var['type'] = 'text';
					} elseif($var['type'] == 'select') {
						$var['type'] = "<select name=\"$var[variable]\">\n";
						foreach(explode("\n", $var['extra']) as $key => $option) {
							$option = trim($option);
							if(strpos($option, '=') === FALSE) {
								$key = $option;
							} else {
								$item = explode('=', $option);
								$key = trim($item[0]);
								$option = trim($item[1]);
							}
							$var['type'] .= "<option value=\"".dhtmlspecialchars($key)."\" ".($var['value'] == $key ? 'selected' : '').">$option</option>\n";
						}
						$var['type'] .= "</select>\n";
						$var['variable'] = $var['value'] = '';
					} elseif($var['type'] == 'selects') {
						$var['value'] = dunserialize($var['value']);
						$var['value'] = is_array($var['value']) ? $var['value'] : array($var['value']);
						$var['type'] = "<select name=\"$var[variable][]\" multiple=\"multiple\" size=\"10\">\n";
						foreach(explode("\n", $var['extra']) as $key => $option) {
							$option = trim($option);
							if(strpos($option, '=') === FALSE) {
								$key = $option;
							} else {
								$item = explode('=', $option);
								$key = trim($item[0]);
								$option = trim($item[1]);
							}
							$var['type'] .= "<option value=\"".dhtmlspecialchars($key)."\" ".(in_array($key, $var['value']) ? 'selected' : '').">$option</option>\n";
						}
						$var['type'] .= "</select>\n";
						$var['variable'] = $var['value'] = '';
					} elseif($var['type'] == 'date') {
						$var['type'] = 'calendar';
						$extra['date'] = '<script type="text/javascript" src="static/js/calendar.js"></script>';
					} elseif($var['type'] == 'datetime') {
						$var['type'] = 'calendar';
						$var['extra'] = 1;
						$extra['date'] = '<script type="text/javascript" src="static/js/calendar.js"></script>';
					} elseif($var['type'] == 'forum') {
						require_once libfile('function/forumlist');
						$var['type'] = '<select name="'.$var['variable'].'"><option value="">'.cplang('plugins_empty').'</option>'.forumselect(FALSE, 0, $var['value'], TRUE).'</select>';
						$var['variable'] = $var['value'] = '';
					} elseif($var['type'] == 'forums') {
						$var['description'] = ($var['description'] ? (isset($lang[$var['description']]) ? $lang[$var['description']] : $var['description'])."\n" : '').$lang['plugins_edit_vars_multiselect_comment']."\n".$var['comment'];
						$var['value'] = dunserialize($var['value']);
						$var['value'] = is_array($var['value']) ? $var['value'] : array();
						require_once libfile('function/forumlist');
						$var['type'] = '<select name="'.$var['variable'].'[]" size="10" multiple="multiple"><option value="">'.cplang('plugins_empty').'</option>'.forumselect(FALSE, 0, 0, TRUE).'</select>';
						foreach($var['value'] as $v) {
							$var['type'] = str_replace('<option value="'.$v.'">', '<option value="'.$v.'" selected>', $var['type']);
						}
						$var['variable'] = $var['value'] = '';
					} elseif(substr($var['type'], 0, 5) == 'group') {
						if($var['type'] == 'groups') {
							$var['description'] = ($var['description'] ? (isset($lang[$var['description']]) ? $lang[$var['description']] : $var['description'])."\n" : '').$lang['plugins_edit_vars_multiselect_comment']."\n".$var['comment'];
							$var['value'] = dunserialize($var['value']);
							$var['type'] = '<select name="'.$var['variable'].'[]" size="10" multiple="multiple"><option value=""'.(@in_array('', $var['value']) ? ' selected' : '').'>'.cplang('plugins_empty').'</option>';
						} else {
							$var['type'] = '<select name="'.$var['variable'].'"><option value="">'.cplang('plugins_empty').'</option>';
						}
						$var['value'] = is_array($var['value']) ? $var['value'] : array($var['value']);

						$query = C::t('common_usergroup')->range_orderby_credit();
						$groupselect = array();
						foreach($query as $group) {
							$group['type'] = $group['type'] == 'special' && $group['radminid'] ? 'specialadmin' : $group['type'];
							$groupselect[$group['type']] .= '<option value="'.$group['groupid'].'"'.(@in_array($group['groupid'], $var['value']) ? ' selected' : '').'>'.$group['grouptitle'].'</option>';
						}
						$var['type'] .= '<optgroup label="'.$lang['usergroups_member'].'">'.$groupselect['member'].'</optgroup>'.
							($groupselect['special'] ? '<optgroup label="'.$lang['usergroups_special'].'">'.$groupselect['special'].'</optgroup>' : '').
							($groupselect['specialadmin'] ? '<optgroup label="'.$lang['usergroups_specialadmin'].'">'.$groupselect['specialadmin'].'</optgroup>' : '').
							'<optgroup label="'.$lang['usergroups_system'].'">'.$groupselect['system'].'</optgroup></select>';
						$var['variable'] = $var['value'] = '';
					} elseif($var['type'] == 'extcredit') {
						$var['type'] = '<select name="'.$var['variable'].'"><option value="">'.cplang('plugins_empty').'</option>';
						foreach($_G['setting']['extcredits'] as $id => $credit) {
							$var['type'] .= '<option value="'.$id.'"'.($var['value'] == $id ? ' selected' : '').'>'.$credit['title'].'</option>';
						}
						$var['type'] .= '</select>';
						$var['variable'] = $var['value'] = '';
					}

					showsetting(isset($lang[$var['title']]) ? $lang[$var['title']] : dhtmlspecialchars($var['title']), $var['variable'], $var['value'], $var['type'], '', 0, isset($lang[$var['description']]) ? $lang[$var['description']] : nl2br(dhtmlspecialchars($var['description'])), dhtmlspecialchars($var['extra']), '', true);
				}
				showsubmit('editsubmit');
				showtablefooter();
				showformfooter();
				echo implode('', $extra);
			}

		} else {

			if(is_array($_GET['varsnew'])) {
				foreach($_GET['varsnew'] as $variable => $value) {
					if(isset($pluginvars[$variable])) {
						if($pluginvars[$variable]['type'] == 'number') {
							$value = (float)$value;
						} elseif(in_array($pluginvars[$variable]['type'], array('forums', 'groups', 'selects'))) {
							$value = serialize($value);
						}
						$value = (string)$value;
						C::t('common_pluginvar')->update_by_variable($pluginid, $variable, array('value' => $value));
					}
				}
			}

			updatecache(array('plugin', 'setting', 'styles'));
			cleartemplatecache();
			cpmsg('plugins_setting_succeed', 'action=plugins&operation=config&do='.$pluginid.'&anchor='.$anchor, 'succeed');

		}

	} else {

		$scriptlang[$plugin['identifier']] = lang('plugin/'.$plugin['identifier']);
		$modfile = '';
		if(is_array($plugin['modules'])) {
			foreach($plugin['modules'] as $module) {
				if($module['type'] == 3 && $module['name'] == $_GET['pmod']) {
					$plugin['directory'] .= (!empty($plugin['directory']) && substr($plugin['directory'], -1) != '/') ? '/' : '';
					$modfile = './source/plugin/'.$plugin['directory'].$module['name'].'.inc.php';
					break;
				}
			}
		}

		if($modfile) {
			shownav('plugin', $plugin['name']);
			showsubmenu($plugin['name'].' '.$plugin['version'].(!$plugin['available'] ? ' ('.$lang['plugins_unavailable'] : ''), $submenuitem);
			if(!@include(DISCUZ_ROOT.$modfile)) {
				cpmsg('plugins_setting_module_nonexistence', '', 'error', array('modfile' => $modfile));
			} else {
				exit();
			}
		} else {
			cpmsg('plugin_file_error', '', 'error');
		}

	}

} elseif($operation == 'add') {

	if(!$isplugindeveloper) {
		cpmsg('undefined_action', '', 'error');
	}

	if(!submitcheck('addsubmit')) {
		shownav('plugin');
		showsubmenu('nav_plugins', array(
			array('plugins_list', 'plugins', 0),
			array('plugins_add', 'plugins&operation=add', 1),
			array('cloudaddons_plugin_link', 'cloudaddons'),
		));
		showtips('plugins_add_tips');

		showformheader("plugins&operation=add", '', 'configform');
		showtableheader();
		showsetting('plugins_edit_name', 'namenew', '', 'text');
		showsetting('plugins_edit_version', 'versionnew', '', 'text');
		showsetting('plugins_edit_copyright', 'copyrightnew', '', 'text');
		showsetting('plugins_edit_identifier', 'identifiernew', '', 'text');
		showsubmit('addsubmit');
		showtablefooter();
		showformfooter();
	} else {
		$namenew	= dhtmlspecialchars(trim($_GET['namenew']));
		$versionnew	= strip_tags(trim($_GET['versionnew']));
		$identifiernew	= trim($_GET['identifiernew']);
		$copyrightnew	= dhtmlspecialchars($_GET['copyrightnew']);

		if(!$namenew) {
			cpmsg('plugins_edit_name_invalid', '', 'error');
		} else {
			if(!ispluginkey($identifiernew) || C::t('common_plugin')->fetch_by_identifier($identifiernew)) {
				cpmsg('plugins_edit_identifier_invalid', '', 'error');
			}
		}
		$data = array(
			'name' => $namenew,
			'version' => $versionnew,
			'identifier' => $identifiernew,
			'directory' => $identifiernew.'/',
			'available' => 0,
			'copyright' => $copyrightnew,
		);
		$pluginid = C::t('common_plugin')->insert($data, true);
		updatecache(array('plugin', 'setting', 'styles'));
		cleartemplatecache();
		cpmsg('plugins_add_succeed', "action=plugins&operation=edit&pluginid=$pluginid", 'succeed');
	}

} elseif($operation == 'edit') {

	if(!$isplugindeveloper) {
		cpmsg('undefined_action', '', 'error');
	}

	if(empty($pluginid) ) {
		$pluginlist = '<select name="pluginid">';
		foreach(C::t('common_plugin')->fetch_all_data() as $plugin) {
			$pluginlist .= '<option value="'.$plugin['pluginid'].'">'.$plugin['name'].'</option>';
		}
		$pluginlist .= '</select>';
		cpmsg('plugins_nonexistence', 'action=plugins&operation=edit'.(!empty($highlight) ? "&highlight=$highlight" : ''), 'form', $pluginlist);
	} else {
		$condition = !empty($uid) ? "uid='$uid'" : "username='$username'";
	}

	$plugin = C::t('common_plugin')->fetch($pluginid);
	if(!$plugin) {
		cpmsg('plugin_not_found', '', 'error');
	}

	$plugin['modules'] = dunserialize($plugin['modules']);

	if($plugin['modules']['system']) {
		cpmsg('plugin_donot_edit', '', 'error');
	}

	if(!submitcheck('editsubmit')) {

		$adminidselect = array($plugin['adminid'] => 'selected');

		shownav('plugin');
		$anchor = in_array($_GET['anchor'], array('config', 'modules', 'vars')) ? $_GET['anchor'] : 'config';
		showsubmenuanchors($lang['plugins_edit'].' - '.$plugin['name'].($plugin['available'] ? cplang('plugins_edit_available') : ''), array(
			array('plugins_list', 'plugins', 0, 1),
			array('config', 'config', $anchor == 'config'),
			array('plugins_config_module', 'modules', $anchor == 'modules'),
			array('plugins_config_vars', 'vars', $anchor == 'vars'),
			array('export', 'plugins&operation=export&pluginid='.$plugin['pluginid'], 0, 1),
		));
		showtips('plugins_edit_tips');

		showtagheader('div', 'config', $anchor == 'config');
		showformheader("plugins&operation=edit&type=common&pluginid=$pluginid", '', 'configform');
		showtableheader();
		showsetting('plugins_edit_name', 'namenew', $plugin['name'], 'text');
		showsetting('plugins_edit_version', 'versionnew', $plugin['version'], 'text');
		if(!$plugin['copyright']) {
			showsetting('plugins_edit_copyright', 'copyrightnew', $plugin['copyright'], 'text');
		}
		showsetting('plugins_edit_identifier', 'identifiernew', $plugin['identifier'], 'text');
		showsetting('plugins_edit_directory', 'directorynew', $plugin['directory'], 'text');
		showsetting('plugins_edit_description', 'descriptionnew', $plugin['description'], 'textarea');
		showsetting('plugins_edit_langexists', 'langexists', $plugin['modules']['extra']['langexists'], 'radio');
		showsubmit('editsubmit');
		showtablefooter();
		showformfooter();
		showtagfooter('div');

		showtagheader('div', 'modules', $anchor == 'modules');
		showformheader("plugins&operation=edit&type=modules&pluginid=$pluginid", '', 'modulesform');
		showtableheader('plugins_edit_modules');
		showsubtitle(array('', 'plugins_edit_modules_type', 'plugins_edit_modules_name', 'plugins_edit_modules_menu', 'plugins_edit_modules_menu_url', 'plugins_edit_modules_adminid', 'display_order'));

		$moduleids = array();
		if(is_array($plugin['modules'])) {
			foreach($plugin['modules'] as $moduleid => $module) {
				if($moduleid === 'extra' || $moduleid === 'system') {
					continue;
				}
				$adminidselect = array($module['adminid'] => 'selected');
				$includecheck = empty($val['include']) ? $lang['no'] : $lang['yes'];

				$typeselect = '<optgroup label="'.cplang('plugins_edit_modules_type_g1').'">'.
					'<option h="1100100" e="inc" value="1"'.($module['type'] == 1 ? ' selected="selected"' : '').'>'.cplang('plugins_edit_modules_type_1').'</option>'.
					'<option h="1111" e="inc" value="5"'.($module['type'] == 5 ? ' selected="selected"' : '').'>'.cplang('plugins_edit_modules_type_5').'</option>'.
					'<option h="1100100" e="inc" value="27"'.($module['type'] == 27 ? ' selected="selected"' : '').'>'.cplang('plugins_edit_modules_type_27').'</option>'.
					'<option h="1100100" e="inc" value="23"'.($module['type'] == 23 ? ' selected="selected"' : '').'>'.cplang('plugins_edit_modules_type_23').'</option>'.
					'<option h="1100110" e="inc" value="25"'.($module['type'] == 25 ? ' selected="selected"' : '').'>'.cplang('plugins_edit_modules_type_25').'</option>'.
					'<option h="1100111" e="inc" value="24"'.($module['type'] == 24 ? ' selected="selected"' : '').'>'.cplang('plugins_edit_modules_type_24').'</option>'.
					'</optgroup>'.
					'<optgroup label="'.cplang('plugins_edit_modules_type_g3').'">'.
					'<option h="1111" e="inc" value="7"'.($module['type'] == 7 ? ' selected="selected"' : '').'>'.cplang('plugins_edit_modules_type_7').'</option>'.
					'<option h="1111" e="inc" value="17"'.($module['type'] == 17 ? ' selected="selected"' : '').'>'.cplang('plugins_edit_modules_type_17').'</option>'.
					'<option h="1111" e="inc" value="19"'.($module['type'] == 19 ? ' selected="selected"' : '').'>'.cplang('plugins_edit_modules_type_19').'</option>'.
					'<option h="1001" e="inc" value="14"'.($module['type'] == 14 ? ' selected="selected"' : '').'>'.cplang('plugins_edit_modules_type_14').'</option>'.
					'<option h="1111" e="inc" value="26"'.($module['type'] == 26 ? ' selected="selected"' : '').'>'.cplang('plugins_edit_modules_type_26').'</option>'.
					'<option h="1111" e="inc" value="21"'.($module['type'] == 21 ? ' selected="selected"' : '').'>'.cplang('plugins_edit_modules_type_21').'</option>'.
					'<option h="1001" e="inc" value="15"'.($module['type'] == 15 ? ' selected="selected"' : '').'>'.cplang('plugins_edit_modules_type_15').'</option>'.
					'<option h="1001" e="inc" value="16"'.($module['type'] == 16 ? ' selected="selected"' : '').'>'.cplang('plugins_edit_modules_type_16').'</option>'.
					'<option h="1001" e="inc" value="3"'.($module['type'] == 3 ? ' selected="selected"' : '').'>'.cplang('plugins_edit_modules_type_3').'</option>'.
					'</optgroup>'.
					'<optgroup label="'.cplang('plugins_edit_modules_type_g2').'">'.
					'<option h="0011" e="class" value="11"'.($module['type'] == 11 ? ' selected="selected"' : '').'>'.cplang('plugins_edit_modules_type_11').'</option>'.
					'<option h="0011" e="class" value="28"'.($module['type'] == 28 ? ' selected="selected"' : '').'>'.cplang('plugins_edit_modules_type_28').'</option>'.
					'<option h="0001" e="class" value="12"'.($module['type'] == 12 ? ' selected="selected"' : '').'>'.cplang('plugins_edit_modules_type_12').'</option>'.
					'</optgroup>';
				showtablerow('', array('class="td25"', 'class="td28"'), array(
					"<input class=\"checkbox\" type=\"checkbox\" name=\"delete[$moduleid]\">",
					"<select id=\"s_$moduleid\" onchange=\"shide(this, '$moduleid')\" name=\"typenew[$moduleid]\">$typeselect</select>".
						' <a href="javascript:;" onclick="window.open(\''.ADMINSCRIPT.'?action=plugins&mod=attachment&operation=sample&pluginid='.$pluginid.'&frame=no&typeid=\'+$(\'s_'.$moduleid.'\').value+\'&module=\'+$(\'en_'.$moduleid.'\').value+\'&fn=\'+$(\'e_'.$moduleid.'\').innerHTML)">'.cplang('plugins_module_sample').'</a>',
					"<input type=\"text\" class=\"txt\" size=\"15\" id=\"en_$moduleid\" name=\"namenew[$moduleid]\" value=\"$module[name]\"><span id=\"e_$moduleid\"></span>",
					"<span id=\"m_$moduleid\"><input type=\"text\" class=\"txt\" size=\"15\" name=\"menunew[$moduleid]\" value=\"$module[menu]\"></span>",
					"<span id=\"u_$moduleid\"><input type=\"text\" class=\"txt\" size=\"15\" id=\"url_$moduleid\" onchange=\"shide($('s_$moduleid'), '$moduleid')\" name=\"urlnew[$moduleid]\" value=\"".dhtmlspecialchars($module['url'])."\"></span>",
					"<span id=\"a_$moduleid\"><select name=\"adminidnew[$moduleid]\">\n".
					"<option value=\"0\" $adminidselect[0]>$lang[usergroups_system_0]</option>\n".
					"<option value=\"1\" $adminidselect[1]>$lang[usergroups_system_1]</option>\n".
					"<option value=\"2\" $adminidselect[2]>$lang[usergroups_system_2]</option>\n".
					"<option value=\"3\" $adminidselect[3]>$lang[usergroups_system_3]</option>\n".
					"</select></span>",
					"<span id=\"o_$moduleid\"><input type=\"text\" class=\"txt\" style=\"width:50px\" name=\"ordernew[$moduleid]\" value=\"$module[displayorder]\"></span>"
				));
				showtagheader('tbody', 'n_'.$moduleid);
				showtablerow('', array('', 'colspan="6"'), array(
				   '',
				   '&nbsp;&nbsp;&nbsp;<span id="nt_'.$moduleid.'">'.$lang['plugins_edit_modules_navtitle'].':<input type="text" class="txt" size="15" name="navtitlenew['.$moduleid.']" value="'.$module['navtitle'].'"></span>
					<span id="ni_'.$moduleid.'">'.$lang['plugins_edit_modules_navicon'].':<input type="text" class="txt" name="naviconnew['.$moduleid.']" value="'.$module['navicon'].'"></span>
					<span id="nsn_'.$moduleid.'">'.$lang['plugins_edit_modules_navsubname'].':<input type="text" class="txt" name="navsubnamenew['.$moduleid.']" value="'.$module['navsubname'].'"></span>
					<span id="nsu_'.$moduleid.'">'.$lang['plugins_edit_modules_navsuburl'].':<input type="text" class="txt" name="navsuburlnew['.$moduleid.']" value="'.$module['navsuburl'].'"></span>
					',
				));
				showtagfooter('tbody');

				$moduleids[] = $moduleid;
			}
		}
		showtablerow('', array('class="td25"', 'class="td28"'), array(
			cplang('add_new'),
			'<select id="s_n" onchange="shide(this, \'n\')" name="newtype">'.
				'<optgroup label="'.cplang('plugins_edit_modules_type_g1').'">'.
				'<option h="1100100" e="inc" value="1">'.cplang('plugins_edit_modules_type_1').'</option>'.
				'<option h="1111" e="inc" value="5">'.cplang('plugins_edit_modules_type_5').'</option>'.
				'<option h="1100100" e="inc" value="27">'.cplang('plugins_edit_modules_type_27').'</option>'.
				'<option h="1100100" e="inc" value="23">'.cplang('plugins_edit_modules_type_23').'</option>'.
				'<option h="1100110" e="inc" value="25">'.cplang('plugins_edit_modules_type_25').'</option>'.
				'<option h="1100111" e="inc" value="24">'.cplang('plugins_edit_modules_type_24').'</option>'.
				'</optgroup>'.
				'<optgroup label="'.cplang('plugins_edit_modules_type_g3').'">'.
				'<option h="1111" e="inc" value="7">'.cplang('plugins_edit_modules_type_7').'</option>'.
				'<option h="1111" e="inc" value="17">'.cplang('plugins_edit_modules_type_17').'</option>'.
				'<option h="1111" e="inc" value="19">'.cplang('plugins_edit_modules_type_19').'</option>'.
				'<option h="1001" e="inc" value="14">'.cplang('plugins_edit_modules_type_14').'</option>'.
				'<option h="1001" e="inc" value="26">'.cplang('plugins_edit_modules_type_26').'</option>'.
				'<option h="1001" e="inc" value="21">'.cplang('plugins_edit_modules_type_21').'</option>'.
				'<option h="1001" e="inc" value="15">'.cplang('plugins_edit_modules_type_15').'</option>'.
				'<option h="1001" e="inc" value="16">'.cplang('plugins_edit_modules_type_16').'</option>'.
				'<option h="1001" e="inc" value="3">'.cplang('plugins_edit_modules_type_3').'</option>'.
				'</optgroup>'.
				'<optgroup label="'.cplang('plugins_edit_modules_type_g2').'">'.
				'<option h="0011" e="class" value="11">'.cplang('plugins_edit_modules_type_11').'</option>'.
				'<option h="0011" e="class" value="28">'.cplang('plugins_edit_modules_type_28').'</option>'.
				'<option h="0001" e="class" value="12">'.cplang('plugins_edit_modules_type_12').'</option>'.
				'</optgroup>'.
			'</select>',
			'<input type="text" class="txt" size="15" name="newname"><span id="e_n"></span>',
			'<span id="m_n"><input type="text" class="txt" size="15" name="newmenu"></span>',
			'<span id="u_n"><input type="text" class="txt" size="15" id="url_n" onchange="shide($(\'s_n\'), \'n\')" name="newurl"></span>',
			'<span id="a_n"><select name="newadminid">'.
			'<option value="0" selected>'.cplang('usergroups_system_0').'</option>'.
			'<option value="1">'.cplang('usergroups_system_1').'</option>'.
			'<option value="2">'.cplang('usergroups_system_2').'</option>'.
			'<option value="3">'.cplang('usergroups_system_3').'</option>'.
			'</select></span>',
			'<span id="o_n"><input type="text" class="txt" style="width:50px"  name="neworder"></span>',
		));
		showtagheader('tbody', 'n_n');
		showtablerow('', array('', 'colspan="6"'), array(
		   '',
		   '&nbsp;&nbsp;&nbsp;<span id="nt_n">'.$lang['plugins_edit_modules_navtitle'].':<input type="text" class="txt" name="newnavtitle"></span>
			<span id="ni_n">'.$lang['plugins_edit_modules_navicon'].':<input type="text" class="txt" name="newnavicon"></span>
			<span id="nsn_n">'.$lang['plugins_edit_modules_navsubname'].':<input type="text" class="txt" name="newnavsubname"></span>
			<span id="nsu_n">'.$lang['plugins_edit_modules_navsuburl'].':<input type="text" class="txt" name="newnavsuburl"></span>
			',
		));
		showtagfooter('tbody');
		showsubmit('editsubmit', 'submit', 'del');
		showtablefooter();
		showformfooter();
		showtagfooter('div');
		$shideinit = '';
		foreach($moduleids as $moduleid) {
			$shideinit .= 'shide($("s_'.$moduleid.'"), \''.$moduleid.'\');';
		}
		echo '<script type="text/JavaScript">
			function shide(obj, id) {
				v = obj.options[obj.selectedIndex].getAttribute("h");
				$("m_" + id).style.display = v.substr(0,1) == "1" ? "" : "none";
				$("u_" + id).style.display = v.substr(1,1) == "1" ? "" : "none";
				$("a_" + id).style.display = v.substr(2,1) == "1" ? "" : "none";
				$("o_" + id).style.display = v.substr(3,1) == "1" ? "" : "none";
				if(v.substr(4,1)) {
					$("n_" + id).style.display = v.substr(4,1) == "1" ? "" : "none";
					$("nt_" + id).style.display = v.substr(4,1) == "1" ? "" : "none";
					$("ni_" + id).style.display = v.substr(5,1) == "1" ? "" : "none";
					$("nsn_" + id).style.display = v.substr(6,1) == "1" ? "" : "none";
					$("nsu_" + id).style.display = v.substr(6,1) == "1" ? "" : "none";
				} else {
					$("n_" + id).style.display = "none";
				}
				e = obj.options[obj.selectedIndex].getAttribute("e");
				$("e_" + id).innerHTML = e && ($("url_" + id).value == \'\' || $("u_" + id).style.display == "none") ? "." + e + ".php" : "";
			}
			shide($("s_n"), "n");'.$shideinit.'
		</script>';

		showtagheader('div', 'vars', $anchor == 'vars');
		showformheader("plugins&operation=edit&type=vars&pluginid=$pluginid", '', 'varsform');
		showtableheader('plugins_edit_vars');
		showsubtitle(array('', 'display_order', 'plugins_vars_title', 'plugins_vars_variable', 'plugins_vars_type', ''));
		foreach(C::t('common_pluginvar')->fetch_all_by_pluginid($plugin['pluginid']) as $var) {
			$var['type'] = $lang['plugins_edit_vars_type_'. $var['type']];
			$var['title'] .= isset($lang[$var['title']]) ? '<br />'.$lang[$var['title']] : '';
			showtablerow('', array('class="td25"', 'class="td28"'), array(
				"<input class=\"checkbox\" type=\"checkbox\" name=\"delete[]\" value=\"$var[pluginvarid]\">",
				"<input type=\"text\" class=\"txt\" size=\"2\" name=\"displayordernew[$var[pluginvarid]]\" value=\"$var[displayorder]\">",
				$var['title'],
				$var['variable'],
				$var['type'],
				"<a href=\"".ADMINSCRIPT."?action=plugins&operation=vars&pluginid=$plugin[pluginid]&pluginvarid=$var[pluginvarid]\" class=\"act\">$lang[detail]</a>"
			));
		}
		showtablerow('', array('class="td25"', 'class="td28"'), array(
			cplang('add_new'),
			'<input type="text" class="txt" size="2" name="newdisplayorder" value="0">',
			'<input type="text" class="txt" size="15" name="newtitle">',
			'<input type="text" class="txt" size="15" name="newvariable">',
			'<select name="newtype">
				<option value="number">'.cplang('plugins_edit_vars_type_number').'</option>
				<option value="text" selected>'.cplang('plugins_edit_vars_type_text').'</option>
				<option value="textarea">'.cplang('plugins_edit_vars_type_textarea').'</option>
				<option value="radio">'.cplang('plugins_edit_vars_type_radio').'</option>
				<option value="select">'.cplang('plugins_edit_vars_type_select').'</option>
				<option value="selects">'.cplang('plugins_edit_vars_type_selects').'</option>
				<option value="color">'.cplang('plugins_edit_vars_type_color').'</option>
				<option value="date">'.cplang('plugins_edit_vars_type_date').'</option>
				<option value="datetime">'.cplang('plugins_edit_vars_type_datetime').'</option>
				<option value="forum">'.cplang('plugins_edit_vars_type_forum').'</option>
				<option value="forums">'.cplang('plugins_edit_vars_type_forums').'</option>
				<option value="group">'.cplang('plugins_edit_vars_type_group').'</option>
				<option value="groups">'.cplang('plugins_edit_vars_type_groups').'</option>
				<option value="extcredit">'.cplang('plugins_edit_vars_type_extcredit').'</option>
				<option value="forum_text">'.cplang('plugins_edit_vars_type_forum_text').'</option>
				<option value="forum_textarea">'.cplang('plugins_edit_vars_type_forum_textarea').'</option>
				<option value="forum_radio">'.cplang('plugins_edit_vars_type_forum_radio').'</option>
				<option value="forum_select">'.cplang('plugins_edit_vars_type_forum_select').'</option>
				<option value="group_text">'.cplang('plugins_edit_vars_type_group_text').'</option>
				<option value="group_textarea">'.cplang('plugins_edit_vars_type_group_textarea').'</option>
				<option value="group_radio">'.cplang('plugins_edit_vars_type_group_radio').'</option>
				<option value="group_select">'.cplang('plugins_edit_vars_type_group_select').'</option>
			</seletc>',
			''
		));
		showsubmit('editsubmit', 'submit', 'del');
		showtablefooter();
		showformfooter();
		showtagfooter('div');

	} else {

		$type = $_GET['type'];
		$anchor = $_GET['anchor'];
		if($type == 'common') {

			$namenew	= dhtmlspecialchars(trim($_GET['namenew']));
			$versionnew	= strip_tags(trim($_GET['versionnew']));
			$directorynew	= dhtmlspecialchars($_GET['directorynew']);
			$identifiernew	= trim($_GET['identifiernew']);
			$descriptionnew	= dhtmlspecialchars($_GET['descriptionnew']);
			$copyrightnew	= $plugin['copyright'] ? addslashes($plugin['copyright']) : dhtmlspecialchars($_GET['copyrightnew']);
			$adminidnew	= ($_GET['adminidnew'] > 0 && $_GET['adminidnew'] <= 3) ? $_GET['adminidnew'] : 1;

			if(!$namenew) {
				cpmsg('plugins_edit_name_invalid', '', 'error');
			} elseif(!isplugindir($directorynew)) {
				cpmsg('plugins_edit_directory_invalid', '', 'error');
			} elseif($identifiernew != $plugin['identifier']) {
				$plugin = C::t('common_plugin')->fetch_by_identifier($identifiernew);
				if($plugin || !ispluginkey($identifiernew)) {
					cpmsg('plugins_edit_identifier_invalid', '', 'error');
				}
			}
			if($_GET['langexists'] && !file_exists($langfile = DISCUZ_ROOT.'./data/plugindata/'.$identifiernew.'.lang.php')) {
				cpmsg('plugins_edit_language_invalid', '', 'error', array('langfile' => $langfile));
			}
			$plugin['modules']['extra']['langexists'] = $_GET['langexists'];
			C::t('common_plugin')->update($pluginid, array(
			    'adminid' => $adminidnew,
			    'version' => $versionnew,
			    'name' => $namenew,
			    'modules' => serialize($plugin['modules']),
			    'identifier' => $identifiernew,
			    'description' => $descriptionnew,
			    'directory' => $directorynew,
			    'copyright' => $copyrightnew
			));

		} elseif($type == 'modules') {

			$modulesnew = array();
			$newname = trim($_GET['newname']);
			$updatenav = false;
			if(is_array($plugin['modules'])) {
				foreach($plugin['modules'] as $moduleid => $module) {
					if(!isset($_GET['delete'][$moduleid])) {
						if($moduleid === 'extra' || $moduleid === 'system') {
							continue;
						}
						$modulesnew[] = array(
							'name'		=> $_GET['namenew'][$moduleid],
							'menu'		=> $_GET['menunew'][$moduleid],
							'url'		=> $_GET['urlnew'][$moduleid],
							'type'		=> $_GET['typenew'][$moduleid],
							'adminid'	=> ($_GET['adminidnew'][$moduleid] >= 0 && $_GET['adminidnew'][$moduleid] <= 3) ? $_GET['adminidnew'][$moduleid] : $module['adminid'],
							'displayorder'	=> intval($_GET['ordernew'][$moduleid]),
							'navtitle'	=> $_GET['navtitlenew'][$moduleid],
							'navicon'	=> $_GET['naviconnew'][$moduleid],
							'navsubname'	=> $_GET['navsubnamenew'][$moduleid],
							'navsuburl'	=> $_GET['navsuburlnew'][$moduleid],
						);
						if(in_array($_GET['typenew'][$moduleid], array(1,23,24,25))) {
							$updatenav = true;
						}
					} elseif(in_array($_GET['typenew'][$moduleid], array(1,23,24,25))) {
						$updatenav = true;
					}
				}
			}

			if($updatenav) {
				C::t('common_nav')->delete_by_type_identifier(3, $plugin['identifier']);
			}

			$modulenew = array();
			if(!empty($_GET['newname'])) {
				$modulesnew[] = array(
					'name'		=> $_GET['newname'],
					'menu'		=> $_GET['newmenu'],
					'url'		=> $_GET['newurl'],
					'type'		=> $_GET['newtype'],
					'adminid'	=> $_GET['newadminid'],
					'displayorder'	=> intval($_GET['neworder']),
					'navtitle'	=> $_GET['newnavtitle'],
					'navicon'	=> $_GET['newnavicon'],
					'navsubname'	=> $_GET['newnavsubname'],
					'navsuburl'	=> $_GET['newnavsuburl'],
				);
			}

			usort($modulesnew, 'modulecmp');

			$namesarray = array();
			foreach($modulesnew as $key => $module) {
				$namekey = in_array($module['type'], array(11, 12)) ? 1 : 0;
				if(!ispluginkey($module['name'])) {
					cpmsg('plugins_edit_modules_name_invalid', '', 'error');
				} elseif(@in_array($module['name'], $namesarray[$namekey])) {
					cpmsg('plugins_edit_modules_duplicated', '', 'error');
				}
				$namesarray[$namekey][] = $module['name'];

				$module['menu'] = trim($module['menu']);
				$module['url'] = trim($module['url']);
				$module['adminid'] = $module['adminid'] >= 0 && $module['adminid'] <= 3 ? $module['adminid'] : 1 ;

				$modulesnew[$key] = $module;
			}
			if(!empty($plugin['modules']['extra'])) {
				$modulesnew['extra'] = $plugin['modules']['extra'];
			}

			if(!empty($plugin['modules']['system'])) {
				$modulesnew['system'] = $plugin['modules']['system'];
			}

			C::t('common_plugin')->update($pluginid, array('modules' => serialize($modulesnew)));

		} elseif($type == 'vars') {

			if($_GET['delete']) {
				C::t('common_pluginvar')->delete($_GET['delete']);
			}

			if(is_array($_GET['displayordernew'])) {
				foreach($_GET['displayordernew'] as $id => $displayorder) {
					C::t('common_pluginvar')->update($id, array('displayorder' => $displayorder));
				}
			}

			$newtitle = dhtmlspecialchars(trim($_GET['newtitle']));
			$newvariable = trim($_GET['newvariable']);
			if($newtitle && $newvariable) {
				if(strlen($newvariable) > 40 || !ispluginkey($newvariable) || C::t('common_pluginvar')->check_variable($pluginid, $newvariable)) {
					cpmsg('plugins_edit_var_invalid', '', 'error');
				}
				$data = array(
					'pluginid' => $pluginid,
					'displayorder' => $_GET['newdisplayorder'],
					'title' => $newtitle,
					'variable' => $newvariable,
					'type' => $_GET['newtype'],
				);
				C::t('common_pluginvar')->insert($data);
			}

		}

		updatecache(array('plugin', 'setting', 'styles'));
		cleartemplatecache();
		updatemenu('plugin');
		cpmsg('plugins_edit_succeed', "action=plugins&operation=edit&pluginid=$pluginid&anchor=$anchor", 'succeed');

	}

} elseif($operation == 'delete') {

	$plugin = C::t('common_plugin')->fetch($pluginid);
	$dir = substr($plugin['directory'], 0, -1);
	$modules = dunserialize($plugin['modules']);
	if($modules['system']) {
		cpmsg('plugins_delete_error');
	}

	if(!$_GET['confirmed']) {

		$installtype = $modules['extra']['installtype'];
		$importfile = DISCUZ_ROOT.'./source/plugin/'.$dir.'/discuz_plugin_'.$dir.($installtype ? '_'.$installtype : '').'.xml';
		if(!file_exists($importfile)) {
			cpmsg('plugin_file_error', '', 'error');
		}

		$importtxt = @implode('', file($importfile));
		$pluginarray = getimportdata('Discuz! Plugin');

		if(!empty($pluginarray['checkfile']) && preg_match('/^[\w\.]+$/', $pluginarray['checkfile'])) {
			$filename = DISCUZ_ROOT.'./source/plugin/'.$plugin['identifier'].'/'.$pluginarray['checkfile'];
			if(file_exists($filename)) {
				loadcache('pluginlanguage_install');
				$installlang = $_G['cache']['pluginlanguage_install'][$plugin['identifier']];
				@include $filename;
			}
		}

		cpmsg('plugins_delete_confirm', 'action=plugins&operation=delete&pluginid='.$pluginid.'&confirmed=yes', 'form', array('pluginname' => $plugin['name'], 'toversion' => $plugin['version']));

	} else {

		dsetcookie('uninstallreason', $_GET['uninstallreason'] ? '|'.implode('|', $_GET['uninstallreason']).'|' : '');

		$identifier = $plugin['identifier'];
		C::t('common_plugin')->delete($pluginid);
		C::t('common_pluginvar')->delete_by_pluginid($pluginid);
		C::t('common_nav')->delete_by_type_identifier(3, $identifier);

		foreach(array('script', 'template') as $type) {
			loadcache('pluginlanguage_'.$type, 1);
			if(isset($_G['cache']['pluginlanguage_'.$type][$identifier])) {
				unset($_G['cache']['pluginlanguage_'.$type][$identifier]);
				savecache('pluginlanguage_'.$type, $_G['cache']['pluginlanguage_'.$type]);
			}
		}

		updatecache(array('plugin', 'setting', 'styles'));
		cleartemplatecache();
		updatemenu('plugin');

		if($dir) {
			$file = DISCUZ_ROOT.'./source/plugin/'.$dir.'/discuz_plugin_'.$dir.($modules['extra']['installtype'] ? '_'.$modules['extra']['installtype'] : '').'.xml';
			if(file_exists($file)) {
				$importtxt = @implode('', file($file));
				$pluginarray = getimportdata('Discuz! Plugin');
				if(!empty($pluginarray['uninstallfile']) && preg_match('/^[\w\.]+$/', $pluginarray['uninstallfile'])) {
					dheader('location: '.ADMINSCRIPT.'?action=plugins&operation=pluginuninstall&dir='.$dir.'&installtype='.$modules['extra']['installtype']);
				}
			}
		}

		loadcache('pluginlanguage_install', 1);
		if(!empty($_G['cache']['pluginlanguage_install']) && isset($_G['cache']['pluginlanguage_install'][$identifier])) {
			unset($_G['cache']['pluginlanguage_install'][$identifier]);
			savecache('pluginlanguage_install', $_G['cache']['pluginlanguage_install']);
		}

		cloudaddons_uninstall($dir.'.plugin', DISCUZ_ROOT.'./source/plugin/'.$dir);
		cpmsg('plugins_delete_succeed', "action=plugins", 'succeed');
	}

} elseif($operation == 'vars') {

	$pluginvarid = $_GET['pluginvarid'];
	$pluginvar = C::t('common_plugin')->fetch_by_pluginvarid($pluginid, $pluginvarid);
	if(!$pluginvar) {
		cpmsg('pluginvar_not_found', '', 'error');
	}

	if(!submitcheck('varsubmit')) {
		shownav('plugin');
		showsubmenu($lang['plugins_edit'].' - '.$pluginvar['name'], array(
			array('plugins_list', 'plugins', 0),
			array('config', 'plugins&operation=edit&pluginid='.$pluginid.'&anchor=config', 0),
			array('plugins_config_module', 'plugins&operation=edit&pluginid='.$pluginid.'&anchor=modules', 0),
			array('plugins_config_vars', 'plugins&operation=edit&pluginid='.$pluginid.'&anchor=vars', 1),
			array('export', 'plugins&operation=export&pluginid='.$pluginid, 0),
		));

		$typeselect = '<select name="typenew" onchange="if(this.value.indexOf(\'select\') != -1) $(\'extra\').style.display=\'\'; else $(\'extra\').style.display=\'none\';">';
		foreach(array('number', 'text', 'radio', 'textarea', 'select', 'selects', 'color', 'date', 'datetime', 'forum', 'forums', 'group', 'groups', 'extcredit',
				'forum_text', 'forum_textarea', 'forum_radio', 'forum_select', 'group_text', 'group_textarea', 'group_radio', 'group_select') as $type) {
			$typeselect .= '<option value="'.$type.'" '.($pluginvar['type'] == $type ? 'selected' : '').'>'.$lang['plugins_edit_vars_type_'.$type].'</option>';
		}
		$typeselect .= '</select>';

		showformheader("plugins&operation=vars&pluginid=$pluginid&pluginvarid=$pluginvarid");
		showtableheader();
		showtitle($lang['plugins_edit_vars'].' - '.$pluginvar['title']);
		showsetting('plugins_edit_vars_title', 'titlenew', $pluginvar['title'], 'text');
		showsetting('plugins_edit_vars_description', 'descriptionnew', $pluginvar['description'], 'textarea');
		showsetting('plugins_edit_vars_type', '', '', $typeselect);
		showsetting('plugins_edit_vars_variable', 'variablenew', $pluginvar['variable'], 'text');
		showtagheader('tbody', 'extra', $pluginvar['type'] == 'select' || $pluginvar['type'] == 'selects');
		showsetting('plugins_edit_vars_extra', 'extranew',  $pluginvar['extra'], 'textarea');
		showtagfooter('tbody');
		showsubmit('varsubmit');
		showtablefooter();
		showformfooter();

	} else {

		$titlenew	= cutstr(trim($_GET['titlenew']), 25);
		$descriptionnew	= cutstr(trim($_GET['descriptionnew']), 255);
		$variablenew	= trim($_GET['variablenew']);
		$extranew	= trim($_GET['extranew']);

		if(!$titlenew) {
			cpmsg('plugins_edit_var_title_invalid', '', 'error');
		} elseif($variablenew != $pluginvar['variable']) {
			if(!$variablenew || strlen($variablenew) > 40 || !ispluginkey($variablenew) || C::t('common_pluginvar')->check_variable($pluginid, $variablenew)) {
				cpmsg('plugins_edit_vars_invalid', '', 'error');
			}
		}

		C::t('common_pluginvar')->update_by_pluginvarid($pluginid, $pluginvarid, array(
		    'title' => $titlenew,
		    'description' => $descriptionnew,
		    'type' => $_GET['typenew'],
		    'variable' => $variablenew,
		    'extra' => $extranew
		));

		updatecache(array('plugin', 'setting', 'styles'));
		cleartemplatecache();
		cpmsg('plugins_edit_vars_succeed', "action=plugins&operation=edit&pluginid=$pluginid&anchor=vars", 'succeed');
	}

} elseif($operation == 'upgradecheck') {
	if(empty($_GET['identifier'])) {
		$pluginarray = C::t('common_plugin')->fetch_all_data();
	} else {
		$plugin = C::t('common_plugin')->fetch_by_identifier($_GET['identifier']);
		$pluginarray = $plugin ? array($plugin) : array();
	}
	$plugins = $errarray = $newarray = $nowarray = array();
	if(!$pluginarray) {
		cpmsg('plugin_not_found', '', 'error');
	} else {
		$addonids = array();
		foreach($pluginarray as $row) {
			if(ispluginkey($row['identifier'])) {
				$addonids[] = $row['identifier'].'.plugin';
			}
		}
		$checkresult = dunserialize(cloudaddons_upgradecheck($addonids));
		savecache('addoncheck_plugin', $checkresult);
		foreach($pluginarray as $row) {
			$addonid = $row['identifier'].'.plugin';
			if(isset($checkresult[$addonid])) {
				list($return, $newver) = explode(':', $checkresult[$addonid]);
				$result[$row['identifier']]['result'] = $return;
				if($newver) {
					$result[$row['identifier']]['newver'] = $newver;
				}
			}
			$plugins[$row['identifier']] = $row['name'].' '.$row['version'];
			$modules = dunserialize($row['modules']);

			$file = DISCUZ_ROOT.'./source/plugin/'.$row['identifier'].'/discuz_plugin_'.$row['identifier'].($modules['extra']['installtype'] ? '_'.$modules['extra']['installtype'] : '').'.xml';
			$upgrade = false;
			if(file_exists($file)) {
				$importtxt = @implode('', file($file));
				$pluginarray = getimportdata('Discuz! Plugin', 0, 1);
				$newver = !empty($pluginarray['plugin']['version']) ? $pluginarray['plugin']['version'] : 0;
				if($newver > $row['version']) {
					$upgrade = true;
					$nowarray[] = '<a href="'.ADMINSCRIPT.'?action=plugins&operation=upgrade&pluginid='.$row['pluginid'].'">'.$plugins[$row['identifier']].' -> '.$newver.'</a>';
				}
			}
			if(!$upgrade) {
				$entrydir = DISCUZ_ROOT.'./source/plugin/'.$row['identifier'];
				$upgradestr = '';
				if(file_exists($entrydir)) {
					$d = dir($entrydir);
					while($f = $d->read()) {
						if(preg_match('/^discuz\_plugin\_'.$row['identifier'].'(\_\w+)?\.xml$/', $f, $a)) {
							$extratxt = $extra = substr($a[1], 1);
							if(preg_match('/^SC\_GBK$/i', $extra)) {
								$extratxt = '&#31616;&#20307;&#20013;&#25991;&#29256;';
							} elseif(preg_match('/^SC\_UTF8$/i', $extra)) {
								$extratxt = '&#31616;&#20307;&#20013;&#25991;&#85;&#84;&#70;&#56;&#29256;';
							} elseif(preg_match('/^TC\_BIG5$/i', $extra)) {
								$extratxt = '&#32321;&#39636;&#20013;&#25991;&#29256;';
							} elseif(preg_match('/^TC\_UTF8$/i', $extra)) {
								$extratxt = '&#32321;&#39636;&#20013;&#25991;&#85;&#84;&#70;&#56;&#29256;';
							}
							if($modules['extra']['installtype'] == $extratxt) {
								continue;
							}
							$importtxt = @implode('', file($entrydir.'/'.$f));
							$pluginarray = getimportdata('Discuz! Plugin', 0, 1);
							$newverother = !empty($pluginarray['plugin']['version']) ? $pluginarray['plugin']['version'] : 0;
							if($newverother > $row['version']) {
								$nowarray[] = '<a href="'.ADMINSCRIPT.'?action=plugins&operation=upgrade&pluginid='.$row['pluginid'].'&confirmed=yes&installtype='.rawurlencode($extra).'">'.$plugins[$row['identifier']].' -> '.$newverother.($extra ? ' ('.$extratxt.')' : '').'</a>';
							}
						}
					}
				}
			}
		}
	}
	foreach($result as $id => $row) {
		if($row['result'] == 0) {
			$errarray[] = '<a href="'.ADMINSCRIPT.'?action=cloudaddons&id='.$id.'.plugin" target="_blank">'.$plugins[$id].'</a>';
		} elseif($row['result'] == 2) {
			$newarray[] = '<a href="'.ADMINSCRIPT.'?action=cloudaddons&id='.$id.'.plugin" target="_blank">'.$plugins[$id].($row['newver'] ? ' -> '.$row['newver'] : '').'</a>';
		}
	}
	if(!$nowarray && !$newarray && !$errarray) {
		cpmsg('plugins_validator_noupdate', '', 'error');
	} else {
		shownav('plugin');
		showsubmenu('nav_plugins', array(
			array('plugins_list', 'plugins', 0),
			$isplugindeveloper ? array('plugins_add', 'plugins&operation=add', 0) : array(),
			array('cloudaddons_plugin_link', 'cloudaddons'),
		), '<a href="'.ADMINSCRIPT.'?action=plugins&operation=upgradecheck" class="bold" style="float:right;padding-right:40px;">'.$lang['plugins_validator'].'</a>');
		showtableheader();
		if($nowarray) {
			showtitle('plugins_validator_nowupgrade');
			foreach($nowarray as $row) {
				showtablerow('class="hover"', array(), array($row));
			}
		}
		if($newarray) {
			showtitle('plugins_validator_newversion');
			foreach($newarray as $row) {
				showtablerow('class="hover"', array(), array($row));
			}
		}
		if($errarray) {
			showtitle('plugins_validator_error');
			foreach($errarray as $row) {
				showtablerow('class="hover"', array(), array($row));
			}
		}
		showtablefooter();
	}
} elseif($operation == 'sample') {
	$plugin = C::t('common_plugin')->fetch($pluginid);
	if(!$plugin) {
		cpmsg('plugin_not_found', '', 'error');
	}
	$code = moduleample($_GET['typeid'], $_GET['module'], $plugin);
	if(!$code) {
		cpmsg('NO_OPERATION');
	}
	dheader('Content-Disposition: attachment; filename='.$_GET['module'].$_GET['fn']);
	dheader('Content-Type: application/octet-stream');
	ob_end_clean();
	echo $code;
	define('FOOTERDISABLED' , 1);
	exit();
}

function moduleample($typeid, $module, $plugin) {
	$samples = array(
		1 => "<?php


if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

?>",
		2 => "<?php


if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}


?>",
		3 => "<?php


if(!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
	exit('Access Denied');
}

?>",
		4 => "<?php


if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class plugin_{PLUGINID} {

}

?>",
		5 => "<?php


if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class mobileplugin_{PLUGINID} {

}

?>",
		6 => "<?php


if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class threadplugin_{PLUGINID} {
	var \$name = '';
	var \$iconfile = '';
	var \$buttontext = '';
}

?>");
	$types = array(1 => 1, 5 => 1, 27 => 1, 23 => 1, 25 => 1, 24 => 1, 7 => 2, 17 => 2, 19 => 2, 14 => 2, 26 => 2, 21 => 2, 15 => 2, 16 => 2, 3 => 3, 11 => 4, 28 => 5, 12 => 6);

	$code = $samples[$types[$typeid]];
	$code = str_replace(
		array(
			'{DATE}',
			'{PLUGINID}',
			'{MODULE}',
			'{MODULENAME}',
			'{COPYRIGHT}',
		),
		array(
			dgmdate(TIMESTAMP, 'Y'),
			$plugin['identifier'],
			$module,
			cplang('plugins_edit_modules_type_'.$typeid),
			$plugin['copyright'],
		), $code);
	return $code;
}

function versioncompatible($versions) {
	global $_G;
	list($currentversion) = explode(' ', trim(strip_tags($_G['setting']['version'])));
	$versions = strip_tags($versions);
	foreach(explode(',', $versions) as $version) {
		list($version) = explode(' ', trim($version));
		if($version && $currentversion === $version) {
			return true;
		}
	}
	return false;
}

?>