<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: update.php 33990 2013-09-13 10:20:02Z nemohou $
 */

include_once('../source/class/class_core.php');
include_once('../source/function/function_core.php');

@set_time_limit(0);

$cachelist = array();
$discuz = C::app();

$discuz->cachelist = $cachelist;
$discuz->init_cron = false;
$discuz->init_setting = true;
$discuz->init_user = false;
$discuz->init_session = false;
$discuz->init_misc = false;

$discuz->init();
$config = array(
	'dbcharset' => $_G['config']['db']['1']['dbcharset'],
	'charset' => $_G['config']['output']['charset'],
	'tablepre' => $_G['config']['db']['1']['tablepre']
);
$theurl = 'update.php';

$_G['siteurl'] = preg_replace('/\/install\/$/i', '/', $_G['siteurl']);

if($_GET['from']) {
	if(md5($_GET['from'].$_G['config']['security']['authkey']) != $_GET['frommd5']) {
		$refererarr = parse_url(dreferer());
		list($dbreturnurl, $dbreturnurlmd5) = explode("\t", authcode($_GET['from']));
		if(md5($dbreturnurl) == $dbreturnurlmd5) {
			$dbreturnurlarr = parse_url($dbreturnurl);

		} else {
			$dbreturnurlarr = parse_url($_GET['from']);
		}
		parse_str($dbreturnurlarr['query'], $dbreturnurlparamarr);
		$operation = $dbreturnurlparamarr['operation'];
		$version = $dbreturnurlparamarr['version'];
		$release = $dbreturnurlparamarr['release'];
		if(!$operation || !$version || !$release) {
			show_msg('请求的参数不正确');
		}
		$time = $_G['timestamp'];
		dheader('Location: '.$_G['siteurl'].basename($refererarr['path']).'?action=upgrade&operation='.$operation.'&version='.$version.'&release='.$release.'&ungetfrom='.$time.'&ungetfrommd5='.md5($time.$_G['config']['security']['authkey']));
	}
}

$lockfile = DISCUZ_ROOT.'./data/update.lock';
if(file_exists($lockfile) && !$_GET['from']) {
	show_msg('请您先登录服务器ftp，手工删除 ./data/update.lock 文件，再次运行本文件进行升级。');
}

$devmode = file_exists(DISCUZ_ROOT.'./install/data/install_dev.sql');
$sqlfile = DISCUZ_ROOT.($devmode ? './install/data/install_dev.sql' : './install/data/install.sql');

if(!file_exists($sqlfile)) {
	show_msg('SQL文件 '.$sqlfile.' 不存在');
}
$first_to_2_5 = !C::t('common_setting')->skey_exists('strongpw');
$first_to_3_0 = !C::t('common_setting')->skey_exists('antitheft');
if($_POST['delsubmit']) {
	if(!empty($_POST['deltables'])) {
		foreach ($_POST['deltables'] as $tname => $value) {
			DB::query("DROP TABLE `".DB::table($tname)."`");
		}
	}
	if(!empty($_POST['delcols'])) {
		foreach ($_POST['delcols'] as $tname => $cols) {
			foreach ($cols as $col => $indexs) {
				if($col == 'PRIMARY') {
					DB::query("ALTER TABLE ".DB::table($tname)." DROP PRIMARY KEY", 'SILENT');
				} elseif($col == 'KEY' || $col == 'UNIQUE') {
					foreach ($indexs as $index => $value) {
						DB::query("ALTER TABLE ".DB::table($tname)." DROP INDEX `$index`", 'SILENT');
					}
				} else {
					DB::query("ALTER TABLE ".DB::table($tname)." DROP `$col`");
				}
			}
		}
	}

	show_msg('删除表和字段操作完成了', $theurl.'?step=style');
}

function waitingdb($curstep, $sqlarray) {
	global $theurl;
	foreach($sqlarray as $key => $sql) {
		$sqlurl .= '&sql[]='.md5($sql);
		$sendsql .= '<img width="1" height="1" src="'.$theurl.'?step='.$curstep.'&waitingdb=1&sqlid='.$key.'">';
	}
	show_msg("优化数据表", $theurl.'?step=waitingdb&nextstep='.$curstep.$sqlurl.'&sendsql='.base64_encode($sendsql), 5000, 1);
}
if(empty($_GET['step'])) $_GET['step'] = 'start';

if($_GET['step'] == 'start') {
	include_once('../config/config_ucenter.php');
	include_once('../uc_client/client.php');
	$version = uc_check_version();
	$version = $version['db'];
	if(!$devmode && !C::t('common_setting')->fetch('bbclosed')) {
		C::t('common_setting')->update('bbclosed', 1);
		require_once libfile('function/cache');
		updatecache('setting');
		show_msg('您的站点未关闭，正在关闭，请稍后...', $theurl.'?step=start', 5000);
	}
	if(version_compare($version, '1.5.2') <= 0) {
		show_msg('请先升级 UCenter 到 1.6.0 以上版本。<br>如果使用为Discuz! X自带UCenter，请先下载 UCenter 1.6.0, 在 utilities 目录下找到对应的升级程序，复制或上传到 Discuz! X 的 uc_server 目录下，运行该程序进行升级');
	} else {
		show_msg('说明：<br>本升级程序会参照最新的SQL文件，对数据库进行同步升级。<br>
			请确保当前目录下 ./data/install.sql 文件为最新版本。<br><br>
			升级完成后会关闭所有插件以确保正常运行，请站长逐个开启每一个插件检测是否兼容新版本。<br><br>
			<a href="'.$theurl.'?step=prepare'.($_GET['from'] ? '&from='.rawurlencode($_GET['from']).'&frommd5='.rawurlencode($_GET['frommd5']) : '').'">准备完毕，升级开始</a>');
	}
} elseif ($_GET['step'] == 'waitingdb') {
	$query = DB::fetch_all("SHOW FULL PROCESSLIST");
	foreach($query as $row) {
		if(in_array(md5($row['Info']), $_GET['sql'])) {
			$list .= '[时长]:'.$row['Time'].'秒 [状态]:<b>'.$row['State'].'</b>[信息]:'.$row['Info'].'<br><br>';
		}
	}
	if(empty($list) && empty($_GET['sendsql'])) {
		$msg = '准备进入下一步操作，请稍后...';
		$notice = '';
		$url = "?step=$_GET[nextstep]";
		$time = 5;
	} else {
		$msg = '正在升级数据，请稍后...';
		$notice = '<br><br><b>以下是正在执行的数据库升级语句:</b><br>'.$list.base64_decode($_GET['sendsql']);
		$sqlurl = implode('&sql[]=', $_GET['sql']);
		$url = "?step=waitingdb&nextstep=$_GET[nextstep]&sql[]=".$sqlurl;
		$time = 20;
	}
	show_msg($msg, $theurl.$url, $time*1000, 0, $notice);
} elseif ($_GET['step'] == 'prepare') {
	if(!C::t('common_setting')->skey_exists('group_recommend')) {
		C::t('forum_groupinvite')->truncate();
	}
	if(DB::fetch_first("SHOW COLUMNS FROM ".DB::table('forum_activityapply')." LIKE 'contact'")) {
		$query = DB::query("UPDATE ".DB::table('forum_activityapply')." SET message=CONCAT_WS(' 联系方式:', message, contact) WHERE contact<>''");
		DB::query("ALTER TABLE ".DB::table('forum_activityapply')." DROP contact");
	}
	if($row = DB::fetch_first("SHOW COLUMNS FROM ".DB::table('forum_postcomment')." LIKE 'authorid'")) {
		if(strstr($row['Type'], 'unsigned')) {
			DB::query("ALTER TABLE ".DB::table('forum_postcomment')." CHANGE authorid authorid mediumint(8) NOT NULL default '0'");
			DB::query("UPDATE ".DB::table('forum_postcomment')." SET authorid='-1' WHERE authorid='0'");
		}
	}
	if(!$row = DB::fetch_first("SHOW COLUMNS FROM ".DB::table('common_failedlogin')." LIKE 'username'")) {
		DB::query("TRUNCATE ".DB::table('common_failedlogin'));
		DB::query("ALTER TABLE ".DB::table('common_failedlogin')." ADD username char(32) NOT NULL default '' AFTER ip");
		DB::query("ALTER TABLE ".DB::table('common_failedlogin')." DROP PRIMARY KEY");
		DB::query("ALTER TABLE ".DB::table('common_failedlogin')." ADD PRIMARY KEY ipusername (ip,username)");
	}
	if(!$row = DB::fetch_first("SHOW COLUMNS FROM ".DB::table('forum_forumfield')." LIKE 'seodescription'")) {
		DB::query("ALTER TABLE ".DB::table('forum_forumfield')." ADD seodescription text NOT NULL default '' COMMENT '版块seo描述' AFTER keywords");
		DB::query("UPDATE ".DB::table('forum_forumfield')." SET seodescription=description WHERE membernum='0'");
	}
	if(DB::fetch_first("SHOW TABLES LIKE '".DB::table('common_tagitem')."'")) {
		$noexist_itemkey = true;
		$query = DB::query("SHOW INDEX FROM ".DB::table('common_tagitem'));
		while($row = DB::fetch($query)) {
			if($row['Key_name'] == 'item') {
				$noexist_itemkey = false;
				break;
			}
		}
		if($noexist_itemkey) {
			$query = DB::query("SELECT *, count(idtype) AS rcount FROM ".DB::table('common_tagitem')." GROUP BY tagid,itemid,idtype ORDER BY rcount DESC");
			while($row = DB::fetch($query)) {
				if($row['rcount'] > 1) {
					DB::query("DELETE FROM ".DB::table('common_tagitem')." WHERE tagid='$row[tagid]' AND itemid='$row[itemid]' AND idtype='$row[idtype]' LIMIT ".($row['rcount'] - 1));
				} else {
					break;
				}
			}
		}
	}
	$posttables = get_special_tables_array('forum_post');
	$posttables[] = 'forum_post';
	foreach($posttables as $post_tablename) {
		if(!DB::fetch_first("SHOW COLUMNS FROM ".DB::table($post_tablename)." LIKE 'position'")) {
			$sql[] = "ALTER TABLE ".DB::table($post_tablename)." ORDER BY tid ASC, first DESC, pid ASC";
			$replycreditsql = '';
			if(DB::fetch_first("SHOW COLUMNS FROM ".DB::table($post_tablename)." LIKE 'replycredit'")) {
				$replycreditsql = "CHANGE `replycredit` `replycredit` int(10) NOT NULL default '0',";
			}
			$sql[] = "ALTER TABLE ".DB::table($post_tablename)." CHANGE `pid` `pid` INT(10) UNSIGNED NOT NULL,$replycreditsql CHANGE `status` `status` int(10) NOT NULL default '0', ADD UNIQUE KEY pid (pid), DROP PRIMARY KEY, ADD `position` INT(8) UNSIGNED NOT NULL AUTO_INCREMENT, ADD PRIMARY KEY(`tid`, `position`), DROP INDEX authorid, ADD INDEX authorid (authorid,invisible)";
			if(!$_GET['waitingdb']) {
				waitingdb('prepare', $sql);
			} else {
				if($sql[$_GET[sqlid]]) {
					DB::query($sql[$_GET[sqlid]], array(), false, true);
				}
			}
			exit();
		}
	}

	$diydata = array('common_diy_data' => 0, 'common_template_block' => 0);
	foreach(DB::fetch_all('SHOW KEYS FROM '.DB::table('common_diy_data')) as $_key) {
		if($_key['Key_name'] == 'PRIMARY') {
			$diydata['common_diy_data']++;
		}
	}
	foreach(DB::fetch_all('SHOW KEYS FROM '.DB::table('common_template_block')) as $_key) {
		if($_key['Key_name'] == 'PRIMARY') {
			$diydata['common_template_block']++;
		}
	}
	if($diydata['common_diy_data'] == 1) {
		DB::query('ALTER TABLE '.DB::table('common_diy_data').' DROP PRIMARY KEY');
	}
	if($diydata['common_template_block'] == 2) {
		DB::query('ALTER TABLE '.DB::table('common_template_block').' DROP PRIMARY KEY');
	}

	show_msg('准备完毕，进入下一步数据库结构升级', $theurl.'?step=sql');
} elseif ($_GET['step'] == 'sql') {

	$sql = implode('', file($sqlfile));
	preg_match_all("/CREATE\s+TABLE.+?pre\_(.+?)\s*\((.+?)\)\s*(ENGINE|TYPE)\s*=\s*(\w+)/is", $sql, $matches);
	$newtables = empty($matches[1])?array():$matches[1];
	$newsqls = empty($matches[0])?array():$matches[0];
	if(empty($newtables) || empty($newsqls)) {
		show_msg('SQL文件内容为空，请确认');
	}

	$i = empty($_GET['i'])?0:intval($_GET['i']);
	$count_i = count($newtables);
	if($i>=$count_i) {
		show_msg('数据库结构升级完毕，进入下一步数据升级操作', $theurl.'?step=data');
	}
	$newtable = $newtables[$i];

	$specid = intval($_GET['specid']);
	if($specid && in_array($newtable, array('forum_post', 'forum_thread'))) {
		$spectable = $newtable;
		$newtable = get_special_table_by_num($newtable, $specid);
	}

	$newcols = getcolumn($newsqls[$i]);

	if(!$query = DB::query("SHOW CREATE TABLE ".DB::table($newtable), 'SILENT')) {
		preg_match("/(CREATE TABLE .+?)\s*(ENGINE|TYPE)\s*=\s*(\w+)/is", $newsqls[$i], $maths);

		$maths[3] = strtoupper($maths[3]);
		if($maths[3] == 'MEMORY' || $maths[3] == 'HEAP') {
			$type = helper_dbtool::dbversion() > '4.1' ? " ENGINE=MEMORY".(empty($config['dbcharset'])?'':" DEFAULT CHARSET=$config[dbcharset]" ): " TYPE=HEAP";
		} else {
			$type = helper_dbtool::dbversion() > '4.1' ? " ENGINE=MYISAM".(empty($config['dbcharset'])?'':" DEFAULT CHARSET=$config[dbcharset]" ): " TYPE=MYISAM";
		}
		$usql = $maths[1].$type;

		$usql = str_replace("CREATE TABLE IF NOT EXISTS pre_", 'CREATE TABLE IF NOT EXISTS '.$config['tablepre'], $usql);
		$usql = str_replace("CREATE TABLE pre_", 'CREATE TABLE '.$config['tablepre'], $usql);

		if(!DB::query($usql, 'SILENT')) {
			show_msg('添加表 '.DB::table($newtable).' 出错,请手工执行以下SQL语句后,再重新运行本升级程序:<br><br>'.dhtmlspecialchars($usql));
		} else {
			$msg = '添加表 '.DB::table($newtable).' 完成';
		}
	} else {
		$value = DB::fetch($query);
		$oldcols = getcolumn($value['Create Table']);

		$updates = array();
		$allfileds =array_keys($newcols);
		foreach ($newcols as $key => $value) {
			if($key == 'PRIMARY') {
				if($value != $oldcols[$key]) {
					if(!empty($oldcols[$key])) {
						$usql = "RENAME TABLE ".DB::table($newtable)." TO ".DB::table($newtable.'_bak');
						if(!DB::query($usql, 'SILENT')) {
							show_msg('升级表 '.DB::table($newtable).' 出错,请手工执行以下升级语句后,再重新运行本升级程序:<br><br><b>升级SQL语句</b>:<div style=\"position:absolute;font-size:11px;font-family:verdana,arial;background:#EBEBEB;padding:0.5em;\">'.dhtmlspecialchars($usql)."</div><br><b>Error</b>: ".DB::error()."<br><b>Errno.</b>: ".DB::errno());
						} else {
							$msg = '表改名 '.DB::table($newtable).' 完成！';
							show_msg($msg, $theurl.'?step=sql&i='.$_GET['i']);
						}
					}
					$updates[] = "ADD PRIMARY KEY $value";
				}
			} elseif ($key == 'KEY') {
				foreach ($value as $subkey => $subvalue) {
					if(!empty($oldcols['KEY'][$subkey])) {
						if($subvalue != $oldcols['KEY'][$subkey]) {
							$updates[] = "DROP INDEX `$subkey`";
							$updates[] = "ADD INDEX `$subkey` $subvalue";
						}
					} else {
						$updates[] = "ADD INDEX `$subkey` $subvalue";
					}
				}
			} elseif ($key == 'UNIQUE') {
				foreach ($value as $subkey => $subvalue) {
					if(!empty($oldcols['UNIQUE'][$subkey])) {
						if($subvalue != $oldcols['UNIQUE'][$subkey]) {
							$updates[] = "DROP INDEX `$subkey`";
							$updates[] = "ADD UNIQUE INDEX `$subkey` $subvalue";
						}
					} else {
						$usql = "ALTER TABLE  ".DB::table($newtable)." DROP INDEX `$subkey`";
						DB::query($usql, 'SILENT');
						$updates[] = "ADD UNIQUE INDEX `$subkey` $subvalue";
					}
				}
			} else {
				if(!empty($oldcols[$key])) {
					if(strtolower($value) != strtolower($oldcols[$key])) {
						$updates[] = "CHANGE `$key` `$key` $value";
					}
				} else {
					$i = array_search($key, $allfileds);
					$fieldposition = $i > 0 ? 'AFTER `'.$allfileds[$i-1].'`' : 'FIRST';
					$updates[] = "ADD `$key` $value $fieldposition";
				}
			}
		}

		if(!empty($updates)) {
			$usql = "ALTER TABLE ".DB::table($newtable)." ".implode(', ', $updates);
			if(!DB::query($usql, 'SILENT')) {
				show_msg('升级表 '.DB::table($newtable).' 出错,请手工执行以下升级语句后,再重新运行本升级程序:<br><br><b>升级SQL语句</b>:<div style=\"position:absolute;font-size:11px;font-family:verdana,arial;background:#EBEBEB;padding:0.5em;\">'.dhtmlspecialchars($usql)."</div><br><b>Error</b>: ".DB::error()."<br><b>Errno.</b>: ".DB::errno());
			} else {
				$msg = '升级表 '.DB::table($newtable).' 完成！';
			}
		} else {
			$msg = '检查表 '.DB::table($newtable).' 完成，不需升级，跳过';
		}
	}

	if($specid) {
		$newtable = $spectable;
	}

	if(get_special_table_by_num($newtable, $specid+1)) {
		$next = $theurl . '?step=sql&i='.($_GET['i']).'&specid='.($specid + 1);
	} else {
		$next = $theurl.'?step=sql&i='.($_GET['i']+1);
	}
	show_msg("[ $i / $count_i ] ".$msg, $next);

} elseif ($_GET['step'] == 'data') {
	if(empty($_GET['op']) || $_GET['op'] == 'realname') {

		$nextop = 'profile';

		$p = 1000;
		$i = !empty($_GET['i']) ? intval($_GET['i']) : 0;
		$n = 0;
		if($i==0) {
			$value = DB::fetch_first('SELECT * FROM '.DB::table('common_member_profile_setting')." WHERE fieldid = 'realname'");
			if(!empty($value)) {
				show_msg("实名功能升级完毕", "$theurl?step=data&op=$nextop");
			}
			DB::query("INSERT INTO ".DB::table('common_member_profile_setting')." VALUES ('realname', '1', '0', '1', '真实姓名', '', '0', '0', '0', '0', '1', 'text', '0', '', '', '0', '0')");
		}
		$t = DB::result_first('SELECT uid FROM '.DB::table('common_member')." ORDER BY uid DESC LIMIT 1");
		$names = $uids = array();
		$query = DB::query('SELECT * FROM '.DB::table('common_member')." WHERE uid>'$i' AND realname != '' LIMIT $p");
		while($value=DB::fetch($query)) {
			$n = intval($value['uid']);
			$value['uid'] = intval($value['uid']);
			$value['realname'] = $value['realname'];
			C::t('common_member')->update($value['uid'], array('realname'=>''));
			C::t('common_member_profile')->update($value['uid'], array('realname'=>$value['realname']));
			$names[$value['uid']] = $value['realname'];
		}

		if($n>0) {
			show_msg("实名功能升级中[$n/$t]", "$theurl?step=data&op=realname&i=$n");
		} else {
			show_msg("实名功能升级完毕", "$theurl?step=data&op=$nextop");
		}

	} elseif($_GET['op'] == 'profile') {
		$nextop = 'setting';
		$value = DB::result_first('SELECT count(*) FROM '.DB::table('common_member_profile_setting')." WHERE fieldid = 'birthdist'");
		if(!$value) {
			DB::query("INSERT INTO ".DB::table('common_member_profile_setting')." VALUES ('birthdist', 1, 0, 0, '出生县', '出生行政区/县', 0, 0, 0, 0, 0, 0, 0, 'select', 0, '', '')");
			DB::query("INSERT INTO ".DB::table('common_member_profile_setting')." VALUES ('birthcommunity', 1, 0, 0, '出生小区', '', 0, 0, 0, 0, 0, 0, 0, 'select', 0, '', '')");
			DB::query("UPDATE ".DB::table('common_member_profile_setting')." SET title='出生地' WHERE fieldid = 'birthcity'");
			DB::query("UPDATE ".DB::table('common_member_profile_setting')." SET title='居住地' WHERE fieldid = 'residecity'");
		}
		$count = DB::result_first("SELECT COUNT(*) FROM ".DB::table('common_district')." WHERE `level`='1' AND `usetype`>'0'");
		if(!$count) {
			DB::query("UPDATE ".DB::table('common_district')." SET `usetype`='3' WHERE `level` = '1'");
		}
		$profile = DB::fetch_first('SELECT * FROM '.DB::table('common_member_profile_setting')." WHERE fieldid = 'birthday'");
		if($profile['title'] == '出生日期') {
			DB::query("UPDATE ".DB::table('common_member_profile_setting')." SET title='生日' WHERE fieldid = 'birthday'");
			DB::query("UPDATE ".DB::table('common_member_profile_setting')." SET title='证件类型' WHERE fieldid = 'idcardtype'");
			DB::query("UPDATE ".DB::table('common_member_profile_setting')." SET title='支付宝' WHERE fieldid = 'alipay'");
			DB::query("UPDATE ".DB::table('common_member_profile_setting')." SET title='ICQ' WHERE fieldid = 'icq'");
			DB::query("UPDATE ".DB::table('common_member_profile_setting')." SET title='QQ' WHERE fieldid = 'qq'");
			DB::query("UPDATE ".DB::table('common_member_profile_setting')." SET title='MSN' WHERE fieldid = 'msn'");
			DB::query("UPDATE ".DB::table('common_member_profile_setting')." SET title='阿里旺旺' WHERE fieldid = 'taobao'");
		}
		show_msg("用户栏目升级完毕", "$theurl?step=data&op=$nextop");
	} elseif($_GET['op'] == 'setting') {
		$nextop = 'admingroup';
		$settings = $newsettings = array();

		$settings = C::t('common_setting')->fetch_all();

		if(!isset($settings['relatetime'])) {
			$newsettings['relatetime'] = 60;
		}

		if(!isset($settings['portalstatus'])) {
			$newsettings['portalstatus'] = 1;
		}

		if(!isset($settings['homestatus'])) {
			$newsettings['homestatus'] = 1;
		}

		if(isset($settings['thumbsource']) && !$settings['sourcewidth'] && !$settings['sourceheight']) {
			$newsettings['sourcewidth'] = $setting['thumbwidth'];
			$newsettings['sourceheight'] = $setting['thumbheight'];
		}

		if(empty($settings['my_siteid']) && !empty($settings['connectsiteid'])) {
			$newsettings['my_siteid'] = $settings['connectsiteid'];
			C::t('common_setting')->delete('connectsiteid');
		}

		if(empty($settings['my_sitekey']) && !empty($settings['connectsitekey'])) {
			$newsettings['my_sitekey'] = $settings['connectsitekey'];
			C::t('common_setting')->delete('connectsitekey');
		}

		$newsettings['adminnotifytypes'] = 'verifythread,verifypost,verifyuser,verifyblog,verifydoing,verifypic,verifyshare,verifycommontes,verifyrecycle,verifyrecyclepost,verifyarticle,verifyacommont,verifymedal,verify_1,verify_2,verify_3,verify_4,verify_5,verify_6,verify_7';

		if(!isset($settings['allowwidthauto'])) {
			$newsettings['allowwidthauto'] = 1;
			$newsettings['switchwidthauto'] = 1;
		}
		if(!$settings['activitypp']) {
			$newsettings['activitypp'] = 8;
		}
		if(!$settings['followretainday']) {
			$newsettings['followretainday'] = 7;
		}
		if(!isset($settings['allowpostcomment'])) {
			$newsettings['allowpostcomment'] = array('1');
		}

		if(!isset($settings['styleid1'])) {
			$newsettings['styleid1'] = 1;
		}

		if(!isset($settings['styleid2'])) {
			$newsettings['styleid2'] = 1;
		}

		if(!isset($settings['styleid3'])) {
			$newsettings['styleid3'] = 1;
		}

		if($settings['heatthread']) {
			$settings['heatthread'] = dunserialize($settings['heatthread']);
			if(empty($settings['heatthread']['type'])) {
				$settings['heatthread']['type'] = 1;
				$settings['heatthread']['period'] = 15;
			}
			if(empty($settings['heatthread']['guidelimit'])) {
				$settings['heatthread']['guidelimit'] = 3;
			}
			$newsettings['heatthread'] = $settings['heatthread'];
		}

		if($settings['seotitle'] && dunserialize($settings['seotitle']) === FALSE) {
			$rownew = array('forum' => $settings['seotitle']);
			$newsettings['seotitle'] = $rownew;
		}
		if($settings['seokeywords'] && dunserialize($settings['seokeywords']) === FALSE) {
			$rownew = array('forum' => $settings['seokeywords']);
			$newsettings['seokeywords'] = $rownew;
		}
		if($settings['seodescription'] && dunserialize($settings['seodescription']) === FALSE) {
			$rownew = array('forum' => $settings['seodescription']);
			$newsettings['seodescription'] = $rownew;
		}
		if($settings['watermarkminheight'] && dunserialize($settings['watermarkminheight']) === FALSE) {
			$rownew = array('portal' => $settings['watermarkminheight'], 'forum' => $settings['watermarkminheight'], 'album' => $settings['watermarkminheight']);
			$newsettings['watermarkminheight'] = $rownew;
		}
		if($settings['watermarkminwidth'] && dunserialize($settings['watermarkminwidth']) === FALSE) {
			$rownew = array('portal' => $settings['watermarkminwidth'], 'forum' => $settings['watermarkminwidth'], 'album' => $settings['watermarkminwidth']);
			$newsettings['watermarkminwidth'] = $rownew;
		}
		if($settings['watermarkquality'] && dunserialize($settings['watermarkquality']) === FALSE) {
			$rownew = array('portal' => $settings['watermarkquality'], 'forum' => $settings['watermarkquality'], 'album' => $settings['watermarkquality']);
			$newsettings['watermarkquality'] = $rownew;
		}
		if($settings['watermarkstatus'] && dunserialize($settings['watermarkstatus']) === FALSE) {
			$rownew = array('portal' => $settings['watermarkstatus'], 'forum' => $settings['watermarkstatus'], 'album' => $settings['watermarkstatus']);
			$newsettings['watermarkstatus'] = $rownew;
		}
		if($settings['watermarktrans'] && dunserialize($settings['watermarktrans']) === FALSE) {
			$rownew = array('portal' => $settings['watermarktrans'], 'forum' => $settings['watermarktrans'], 'album' => $settings['watermarktrans']);
			$newsettings['watermarktrans'] = $rownew;
		}
		if($settings['watermarktype'] && dunserialize($settings['watermarktype']) === FALSE) {
			$watermarktype_map = array(
				0 => 'gif',
				1 => 'png',
				2 => 'text',
			);
			$rownew = array('portal' => $watermarktype_map[$settings['watermarktype']], 'forum' => $watermarktype_map[$settings['watermarktype']], 'album' => $watermarktype_map[$settings['watermarktype']]);
			$newsettings['watermarktype'] = $rownew;
		}
		if($settings['watermarktext'] && dunserialize($settings['watermarktext']) === FALSE) {
			$rownew = array();
			$watermarktext = (array)dunserialize($settings['watermarktext']);
			foreach($watermarktext as $data_k => $data_v) {
				$rownew[$data_k]['portal'] = $data_v;
				$rownew[$data_k]['forum'] = $data_v;
				$rownew[$data_k]['album'] = $data_v;
			}
			$newsettings['watermarktext'] = $rownew;
		}
		if(!$settings['mobile']) {
			$newsettings['mobile'] = array('allowmobile' => 0, 'mobilepreview' => 1);
		}
		if(!$settings['card']) {
			$newsettings['card'] = array('open' => 0);
		}
		$newsettings['group_allowfeed'] = '1';
		if(empty($settings['relatenum'])) {
			$newsettings['relatenum'] = '10';
		}
		if(!isset($settings['profilegroup'])) {
			$profilegroupnew = array(
				'base' =>
				array (
				  'available' => 1,
				  'displayorder' => 0,
				  'title' => '基本资料',
				  'field' =>
				  array (
					'realname' => 'realname',
					'gender' => 'gender',
					'birthday' => 'birthday',
					'birthcity' => 'birthcity',
					'residecity' => 'residecity',
					'residedist' => 'residedist',
					'affectivestatus' => 'affectivestatus',
					'lookingfor' => 'lookingfor',
					'bloodtype' => 'bloodtype',
					'field1' => 'field1',
					'field2' => 'field2',
					'field3' => 'field3',
					'field4' => 'field4',
					'field5' => 'field5',
					'field6' => 'field6',
					'field7' => 'field7',
					'field8' => 'field8',
				  ),
				),
				'contact' =>
				array (
				  'title' => '联系方式',
				  'available' => '1',
				  'displayorder' => '1',
				  'field' =>
				  array (
					'telephone' => 'telephone',
					'mobile' => 'mobile',
					'icq' => 'icq',
					'qq' => 'qq',
					'yahoo' => 'yahoo',
					'msn' => 'msn',
					'taobao' => 'taobao',
				  ),
				),
				'edu' =>
				array (
				  'available' => 1,
				  'displayorder' => 2,
				  'title' => '教育情况',
				  'field' =>
				  array (
					'graduateschool' => 'graduateschool',
					'education' => 'education',
				  ),
				),
				'work' =>
				array (
				  'available' => 1,
				  'displayorder' => 3,
				  'title' => '工作情况',
				  'field' =>
				  array (
					'occupation' => 'occupation',
					'company' => 'company',
					'position' => 'position',
					'revenue' => 'revenue',
				  ),
				),
				'info' =>
				array (
				  'title' => '个人信息',
				  'available' => '1',
				  'displayorder' => '4',
				  'field' =>
				  array (
					'idcardtype' => 'idcardtype',
					'idcard' => 'idcard',
					'address' => 'address',
					'zipcode' => 'zipcode',
					'site' => 'site',
					'bio' => 'bio',
					'interest' => 'interest',
					'sightml' => 'sightml',
					'customstatus' => 'customstatus',
					'timeoffset' => 'timeoffset',
				  ),
				),
			);
			$newsettings['profilegroup'] = $profilegroupnew;
		}
		if(!isset($settings['ranklist'])) {
			$newsettings['ranklist'] = array(
				'status' => '0',
				'cache_time' => '1',
				'index_select' => 'thisweek',
				'member' => array('available' => '0', 'cache_time' => '5', 'show_num' => '20',),
				'thread' => array('available' => '0', 'cache_time' => '5', 'show_num' => '20',),
				'blog' => array('available' => '0', 'cache_time' => '5', 'show_num' => '20',),
				'poll' => array('available' => '0', 'cache_time' => '5', 'show_num' => '20',),
				'activity' => array('available' => '0', 'cache_time' => '5', 'show_num' => '20',),
				'picture' => array('available' => '0', 'cache_time' => '5', 'show_num' => '20',),
				'forum' => array('available' => '0', 'cache_time' => '5', 'show_num' => '20',),
				'group' => array('available' => '0', 'cache_time' => '5', 'show_num' => '20',),
			);
		}
		if(!isset($settings['ipregctrltime'])) {
			$newsettings['ipregctrltime'] = '72';
		}
		DB::query("REPLACE INTO ".DB::table('common_setting')." VALUES ('regname', 'register')");
		$newsettings['regname'] = 'register';
		if(empty($settings['reglinkname'])) {
			$newsettings['reglinkname'] = '注册';
		}

		if(empty($settings['domain'])) {
			$newsettings['domain'] = array(
				'defaultindex' => 'forum.php',
				'holddomain' => 'www|*blog*|*space*',
				'list' => array(),
				'app' => array('portal' => '', 'forum' => '', 'group' => '', 'home' => '', 'default' => '',),
				'root' => array('home' => '', 'group' => '', 'forum' => '', 'topic' => '', 'channel' => '',),
			);
		}
		if(empty($settings['group_recommend'])) {
			if($settings['newbiespan'] > 0) {
				$newsettings['newbiespan'] = round($settings['newbiespan'] * 60);
			}
			DB::query("UPDATE ".DB::table('common_member_field_forum')." SET attentiongroup=''");

			$query = DB::query("SELECT f.fid, f.name, ff.description, ff.icon FROM ".DB::table('forum_forum')." f LEFT JOIN ".DB::table('forum_forumfield')." ff USING(fid) WHERE f.status='3' AND f.type='sub' ORDER BY f.commoncredits desc LIMIT 8");
			while($row = DB::fetch($query)) {
				$row['name'] = addslashes($row['name']);
				$settings['attachurl'] .= substr($settings['attachurl'], -1, 1) != '/' ? '/' : '';
				if($row['icon']) {
					$row['icon'] = $settings['attachurl'].'group/'.$row['icon'];
				} else {
					$row['icon'] = 'static/image/common/groupicon.gif';
				}
				$row['description'] = addslashes($row['description']);
				$group_recommend[$row[fid]] = $row;
			}
			$newsettings['group_recommend'] = $group_recommend;
		}

		if(!DB::result_first("SELECT COUNT(*) FROM ".DB::table('common_magic')." WHERE credit>'0'")) {
			$creditstranssi = explode(',', $settings['creditstrans']);
			$creditstran = $creditstranssi[3] ? $creditstranssi[3] : $creditstranssi[0];
			DB::update('common_magic', array('credit' => $creditstran));
		}
		if(!isset($settings['allowviewuserthread'])) {
			$allowviewuserthread = array('allow'=>'1','fids'=>array());
			$query = DB::query('SELECT ff.fid,ff.viewperm FROM '.DB::table('forum_forum').' f LEFT JOIN '.DB::table('forum_forumfield')." ff ON f.fid = ff.fid WHERE f.status='1' AND f.type IN ('forum','sub')");
			while($value = DB::fetch($query)) {
				$arr = !empty($value['viewperm']) ? explode("\t", $value['viewperm']) : array();
				if(empty($value['viewperm']) || in_array('7', $arr) ||  in_array($settings['newusergroupid'], $arr) ) {
					$allowviewuserthread['fids'][] = $value['fid'];
				}
			}
			$newsettings['allowviewuserthread'] = $allowviewuserthread;
		}
		if(!isset($settings['focus'])) {
			$focusnew = array('title' => '站长推荐', 'cookie' => 1);
			$newsettings['focus'] = $focusnew;
		} else {
			$focus = dunserialize($settings['focus']);
			if(!isset($focus['cookie'])) {
				$focus['cookie'] = 1;
				$newsettings['focus'] = $focus;
			}
		}
		if(!isset($settings['onlyacceptfriendpm'])) {
			$onlyacceptfriendpmnew = '0';
			$newsettings['onlyacceptfriendpm'] = $onlyacceptfriendpmnew;
		}
		if(!isset($settings['pmreportuser'])) {
			$pmreportusernew = '1';
			$newsettings['pmreportuser'] = $pmreportusernew;
		}
		if(!isset($settings['chatpmrefreshtime'])) {
			$chatpmrefreshtimenew = '8';
			$newsettings['chatpmrefreshtime'] = $chatpmrefreshtimenew;
		}
		if(!isset($settings['preventrefresh'])) {
			$preventrefreshnew = '1';
			$newsettings['preventrefresh'] = $preventrefreshnew;
		}
		if(!isset($settings['targetblank'])) {
			$targetblanknew = '0';
			$newsettings['targetblank'] = $targetblanknew;
		}
		if(!isset($settings['article_tags'])) {
			$article_tagsnew = array(1 => '原创', 2 => '热点', 3 => '组图', 4 => '爆料', 5 => '头条', 6 => '幻灯', 7 => '滚动', 8 => '推荐');
			$newsettings['article_tags'] = $article_tagsnew;
		}
		if(empty($settings['anonymoustext'])) {
			$newsettings['anonymoustext'] = '匿名';
		}
		if(!$word_type_count = DB::result_first("SELECT count(*) FROM ".DB::table('common_word_type')."")) {
			DB::query("INSERT INTO ".DB::table('common_word_type')." VALUES('1', '政治'),('2', '广告')");
		}
		if(!isset($settings['userreasons'])) {
			$newsettings['userreasons'] = '很给力!\r\n神马都是浮云\r\n赞一个!\r\n山寨\r\n淡定';
		}
		if(!$forum_typevar_search = C::t('forum_typevar')->count_by_search(2)) {
			C::t('forum_typevar')->update_by_search(1, array('search' => 3));
		}
		if(($seccodecheck = $settings['seccodestatus'])) {
			if(!($seccodecheck & 16)) {
				$seccodecheck = setstatus(5, 1, $seccodecheck);
				$newsettings['seccodestatus'] = $seccodecheck;
			}
		}
		$seccodedata = dunserialize($setting['seccodedata']);
		if(!$seccodedata['rule']) {
			$seccodestatuss = sprintf('%05b', $seccodecheck);
			$seccodedata['rule']['register']['allow'] = $seccodestatuss{4};
			$seccodedata['rule']['login']['allow'] = $seccodestatuss{3};
			$seccodedata['rule']['post']['allow'] = $seccodestatuss{2};
			$seccodedata['rule']['password']['allow'] = $seccodestatuss{1};
			$seccodedata['rule']['card']['allow'] = $seccodestatuss{0};
			$newsettings['seccodedata'] = serialize($seccodedata);
		}
		if(!isset($settings['collectionteamworkernum'])) {
			$newsettings['collectionteamworkernum'] = '3';
		}
		if(!isset($settings['collectionnum'])) {
			$newsettings['collectionnum'] = '5';
		}
		if(!$settings['numbercard']) {
			$newsettings['numbercard'] = 'a:1:{s:3:"row";a:3:{i:1;s:7:"threads";i:2;s:5:"posts";i:3;s:7:"credits";}}';
		}
		if(!isset($settings['threadguestlite'])) {
			$newsettings['threadguestlite'] = '1';
		}

		$settings['search'] = dunserialize($settings['search']);
		if(!is_array($settings['search']['collection'])) {
			$newsettings['search'] = $settings['search'];
			$newsettings['search']['collection']['status'] = '1';
			$newsettings['search']['collection']['searchctrl'] = '10';
			$newsettings['search']['collection']['maxspm'] = '10';
			$newsettings['search']['collection']['maxsearchresults'] = '500';
			DB::query("UPDATE ".DB::table('common_usergroup_field')." SET allowsearch = allowsearch | 64 WHERE groupid<'4' OR groupid>'9'");
		}

		if(empty($settings['lazyload'])) {
			$newsettings['lazyload'] = 0;
		}
		if(empty($settings['guide'])) {
			$newsettings['guide'] = array('hotdt' => 604800, 'digestdt' => 604800);
		}

		$settings['memory'] = isset($settings['memory']) ? dunserialize($settings['memory']) : array();
		if(empty($settings['memory']) || isset($settings['memory']['diyblock']['enable'])) {
			$memory = array();
			if(isset($settings['memory']['diyblock']['enable'])) {
				foreach($settings['memory'] as $k => $v) {
					if(!empty($v['enable'])) {
						$memory['memory'][$k] = $v['ttl'];
					}
				}
			}
			unset($memory['forum_post']);
			$newsettings['memory'] = array_merge(array('common_member' => 0,'common_member_count' => 0,'common_member_status' => 0,'common_member_profile' => 0,
											'common_member_field_home' => 0,'common_member_field_forum' => 0,'common_member_verify' => 0,
											'forum_thread' => 172800, 'forum_thread_forumdisplay' => 300, 'forum_collectionrelated' => 0, 'forum_postcache' => 300,
											'forum_collection' => 300,'home_follow' => 86400, 'forumindex' => 30, 'diyblock' => 300, 'diyblockoutput' => 30), $memory);
		}

		if(!isset($settings['blockmaxaggregationitem'])) {
			$newsettings['blockmaxaggregationitem'] = 20000;
		}

		if(!isset($settings['showfollowcollection'])) {
			$newsettings['showfollowcollection'] = 8;
		}

		if(!isset($settings['antitheft'])) {
			$newsettings['antitheft'] = array('allow' => 0, 'max' => 200);
		}

		if(!isset($settings['repliesrank'])) {
			$newsettings['repliesrank'] = 1;
			$newsettings['threadblacklist'] = 1;
			$newsettings['threadhotreplies'] = 3;
			$newsettings['threadfilternum'] = 10;
			$newsettings['hidefilteredpost'] = 1;
			$newsettings['nofilteredpost'] = 0;
			$newsettings['filterednovote'] = 1;
		}

		$group_userperm = dunserialize($settings['group_userperm']);
		if(!isset($$group_userperm['allowlivethread'])) {
			$group_userperm['allowlivethread'] = '1';
			$newsettings['group_userperm'] = serialize($group_userperm);
		}

		if(!isset($settings['darkroom'])) {
			$newsettings['darkroom'] = '1';
		}

		if(!isset($settings['showfjump'])) {
			$newsettings['showfjump'] = 1;
		}
		if(!isset($settings['grid'])) {
			$newsettings['grid'] = 'a:8:{s:8:"showgrid";s:1:"0";s:8:"gridtype";s:1:"0";s:8:"textleng";s:2:"30";s:4:"fids";a:1:{i:0;i:0;}s:9:"highlight";s:1:"1";s:11:"targetblank";s:1:"1";s:8:"showtips";s:1:"1";s:9:"cachelife";s:3:"600";}';
		}

		if(!empty($newsettings)) {
			C::t('common_setting')->update_batch($newsettings);
		}

		if(!DB::result_first("SELECT allowreplycredit FROM ".DB::table('common_usergroup_field')." WHERE groupid = 1")) {
			DB::query("UPDATE ".DB::table('common_usergroup_field')." SET allowreplycredit = '1' WHERE groupid = 1");
		}
		show_msg("配置项升级完成", "$theurl?step=data&op=$nextop");
	} elseif($_GET['op'] == 'admingroup') {
		$nextop = 'updatethreadtype';
		if(!DB::result_first("SELECT allowclearrecycle FROM ".DB::table('common_admingroup')." WHERE allowclearrecycle='1'")) {
			DB::query('UPDATE '.DB::table('common_admingroup')." SET allowclearrecycle='1' WHERE admingid='1' OR admingid='2'");
		}
		DB::query('UPDATE '.DB::table('common_admingroup')." SET allowmanagetag='1' WHERE admingid IN ('1', '2', '3')");
		DB::query('UPDATE '.DB::table('common_admingroup')." SET allowlivethread='1' WHERE admingid IN ('1', '2', '3')");
		DB::query('UPDATE '.DB::table('common_usergroup_field')." SET allowposttag='1' WHERE groupid=1");
		if(DB::result_first("SELECT cpgroupid FROM ".DB::table('common_admincp_group')." WHERE cpgroupid='3'")) {
			if(!DB::result_first("SELECT cpgroupid FROM ".DB::table('common_admincp_perm')." WHERE cpgroupid='3' AND perm='threads_group'")) {
				DB::query("INSERT INTO ".DB::table('common_admincp_perm')." VALUES ('3', 'threads_group')");
				DB::query("INSERT INTO ".DB::table('common_admincp_perm')." VALUES ('3', 'prune_group')");
				DB::query("INSERT INTO ".DB::table('common_admincp_perm')." VALUES ('3', 'attach_group')");
				DB::query("ALTER TABLE ".DB::table('common_admingroup')." DROP `disablepostctrl`");
				DB::query("UPDATE ".DB::table('common_usergroup_field')." SET allowgroupdirectpost='3'");
				DB::query("UPDATE ".DB::table('common_usergroup_field')." SET allowgroupposturl='3' WHERE groupid='1'");
			}
		}
		if(DB::result_first("SELECT cpgroupid FROM ".DB::table('common_admincp_group')." WHERE cpgroupid='1'")) {
			if(!DB::result_first("SELECT cpgroupid FROM ".DB::table('common_admincp_perm')." WHERE cpgroupid='1' AND perm='postcomment'")) {
				DB::query("INSERT INTO ".DB::table('common_admincp_perm')." VALUES ('1', 'postcomment')");
			}
		}
		if(!DB::result_first("SELECT allowbanvisituser FROM ".DB::table('common_admingroup')." WHERE allowbanvisituser='1'")) {
			DB::query('UPDATE '.DB::table('common_admingroup')." SET allowbanvisituser='1' WHERE admingid='1' OR admingid='2'");
		}
		if($first_to_2_5) {
			DB::query('UPDATE '.DB::table('common_admingroup')." SET allowmanagecollection='1' WHERE admingid='1' OR admingid='2'");
		}
		DB::query('UPDATE '.DB::table('common_admingroup')." SET allowmakehtml='1' WHERE admingid=1");
		show_msg("管理组设置升级完成", "$theurl?step=data&op=$nextop");
	} elseif($_GET['op'] == 'updatethreadtype') {
		$nextop = 'updatecron';
		$selectoption = array();

		$query = DB::query("SELECT * FROM ".DB::table('forum_typeoption')." WHERE type='select'");
		while($typeoptionarr = DB::fetch($query)) {
			$selectoption[] = $typeoptionarr['identifier'];
		}

		$query = C::t('forum_threadtype')->range();
		foreach($query as $threadtypearr) {
			if(DB::num_rows(DB::query("SHOW TABLES LIKE '".DB::table('forum_optionvalue')."$threadtypearr[typeid]'")) != 1) {
				continue;
			}
			$varnames = array();
			$queryoptionvalue = DB::query("SELECT * FROM ".DB::table('forum_optionvalue')."$threadtypearr[typeid] LIMIT 1");
			if($optionvaluearr = DB::fetch($queryoptionvalue)) {
				foreach($optionvaluearr as $key => $value) {
					if(in_array($key, $selectoption)) {
						$varnames[] = 'CHANGE `'.$key.'` `'.$key.'` VARCHAR(50) NOT NULL';
					}
				}
			}
			if(!empty($varnames)) {
				DB::query("ALTER TABLE ".DB::table('forum_optionvalue')."$threadtypearr[typeid] ".implode(',', $varnames));
			}
		}
		show_msg("分类信息升级完成", "$theurl?step=data&op=$nextop");
	} elseif($_GET['op'] == 'updatecron') {
		$nextop = 'updatemagic';
		if(!DB::result_first("SELECT filename FROM ".DB::table('common_cron')." WHERE filename='cron_cleanfeed.php'")) {
			DB::query("INSERT INTO ".DB::table('common_cron')." VALUES ('', '1','system','清理过期动态','cron_cleanfeed.php','1269746634','1269792000','-1','-1','0','0')");
		}

		if(!DB::result_first("SELECT filename FROM ".DB::table('common_cron')." WHERE filename='cron_checkpatch_daily.php'")) {
			DB::query("INSERT INTO ".DB::table('common_cron')." VALUES ('', '1','system','每日获取安全补丁','cron_checkpatch_daily.php','1269746639','1269792000','-1','-1','2','22')");
		}

		if(!DB::result_first("SELECT filename FROM ".DB::table('common_cron')." WHERE filename='cron_publish_halfhourly.php'")) {
			DB::query("INSERT INTO ".DB::table('common_cron')." VALUES ('', '1','system','定时发布主题','cron_publish_halfhourly.php','1269746639','1269792000','-1','-1','-1','0	30')");
		}

		if(!DB::result_first("SELECT filename FROM ".DB::table('common_cron')." WHERE filename='cron_follow_daily.php'")) {
			DB::query("INSERT INTO ".DB::table('common_cron')." VALUES ('','1','system','每周广播归档','cron_follow_daily.php','1269746639','1269792000','-1','-1','02','0')");
		}
		if(!DB::result_first("SELECT filename FROM ".DB::table('common_cron')." WHERE filename='cron_todayviews_daily.php'")) {
			DB::query("INSERT INTO ".DB::table('common_cron')." VALUES ('','1','system','更新每日查看数','cron_todayviews_daily.php','1321500558','1321556400','-1','-1','3','0	5	10	15	20	25	30	35	40	45	50	55')");
		}
		if(!DB::result_first("SELECT filename FROM ".DB::table('common_cron')." WHERE filename='cron_member_optimize_daily.php'")) {
			DB::query("INSERT INTO ".DB::table('common_cron')." VALUES ('','0','system','每日用户表优化','cron_member_optimize_daily.php','1321500558','1321556400','-1','-1','2','0	5	10	15	20	25	30	35	40	45	50	55')");
		}
		if(DB::result_first("SELECT COUNT(*) FROM ".DB::table('common_cron')." WHERE filename='cron_birthday_daily.php'")) {
			DB::query("DELETE FROM ".DB::table('common_cron')." WHERE filename='cron_birthday_daily.php'");
		}

		if(!DB::result_first("SELECT filename FROM ".DB::table('common_cron')." WHERE filename='cron_todayheats_daily.php'")) {
			DB::query("INSERT INTO ".DB::table('common_cron')." VALUES ('','1','system','统计今日热帖','cron_todayheats_daily.php','1269746623','1269792000','-1','-1','0','0')");
		}

		show_msg("计划任务升级完成", "$theurl?step=data&op=$nextop");
	} elseif($_GET['op'] == 'updatemagic') {
		$nextop = 'updatereport';
		if(DB::result_first("SELECT name FROM ".DB::table('common_magic')." WHERE identifier='highlight'")) {
			DB::query("UPDATE ".DB::table('common_magic')." SET name='变色卡', description='可以将帖子或日志的标题高亮，变更颜色' WHERE identifier='highlight'");
		}
		if(DB::result_first("SELECT name FROM ".DB::table('common_magic')." WHERE identifier='namepost'")) {
			DB::query("UPDATE ".DB::table('common_magic')." SET name='显身卡', description='可以查看一次匿名用户的真实身份。' WHERE identifier='namepost'");
		}
		if(DB::result_first("SELECT name FROM ".DB::table('common_magic')." WHERE identifier='anonymouspost'")) {
			DB::query("UPDATE ".DB::table('common_magic')." SET name='匿名卡', description='在指定的地方，让自己的名字显示为匿名。' WHERE identifier='anonymouspost'");
		}

		show_msg("道具升级完成", "$theurl?step=data&op=$nextop");
	} elseif($_GET['op'] == 'updatereport') {
		$nextop = 'myappcount';
		if(!C::t('common_setting')->skey_exists('report_reward')) {
			$report_uids = array();
			$founders = $_G['config']['admincp']['founder'] !== '' ? explode(',', str_replace(' ', '', addslashes($_G['config']['admincp']['founder']))) : array();
			if($founders) {
				$founderexists = true;
				$fuid = $fuser = array();
				foreach($founders as $founder) {
					if(is_numeric($founder)) {
						$fuid[] = $founder;
					} else {
						$fuser[] = $founder;
					}
				}
				$query = DB::query("SELECT uid, username FROM ".DB::table('common_member')." WHERE ".($fuid ? "uid IN (".dimplode($fuid).")" : '0')." OR ".($fuser ? "username IN (".dimplode($fuser).")" : '0'));
				while($founder = DB::fetch($query)) {
					$report_uids[] = $founder['uid'];
				}
			}
			$query = DB::query("SELECT uid FROM ".DB::table('common_admincp_perm')." ap LEFT JOIN ".DB::table('common_admincp_member')." am ON am.cpgroupid=ap.cpgroupid where perm='report'");
			while($user = DB::fetch($query)) {
				if(empty($users[$user[uid]])) {
					$report_uids[] = $user['uid'];
				}
			}
			if($report_uids) {
				$report_receive = array('adminuser' => $report_uids, 'supmoderator' => array());
				C::t('common_setting')->update('report_receive', $report_receive);
			}
			$report_reward = array();
			$report_reward['min'] = '-3';
			$report_reward['max'] = '3';
			C::t('common_setting')->update('report_reward', $report_receive);
		}

		show_msg("举报升级完成", "$theurl?step=data&op=$nextop");
	} elseif($_GET['op'] == 'myappcount') {

		$nextop = 'nav';
		$needupgrade = DB::query("SELECT COUNT(*) FROM ".DB::table('common_myapp_count'), 'SILENT');
		if($needupgrade) {
			DB::query("DROP TABLE `".DB::table('common_myapp_count')."`");
			DB::query("DROP TABLE `".DB::table('home_userapp_stat')."`");
		}
		show_msg("漫游应用统计升级完成", "$theurl?step=data&op=$nextop");

	} elseif($_GET['op'] == 'nav') {

		$nextop = 'forumstatus';
		$parentids = $navs = $subnavs = array();
		$query = DB::query("SELECT * FROM ".DB::table('common_nav')." WHERE navtype='0' AND parentid<>'0'");
		while($nav = DB::fetch($query)) {
			$parentids[$nav['parentid']] = $nav['parentid'];
		}

		$query = DB::query("SELECT * FROM ".DB::table('common_nav')." WHERE type='0'");
		while($nav = DB::fetch($query)) {
			if($nav['identifier'] == 5 && $nav['navtype'] == 0 && $nav['available'] == -1) {
				$nav['available'] = 1;
			}
			$navs[] = $nav;
			if($parentids[$nav['id']]) {
				$subnavs[$nav['id']] = $nav['id'];
			}
		}


		$navid = DB::result_first("SELECT id FROM ".DB::table('common_nav')." WHERE navtype=0 AND type=0 AND identifier=12");
		DB::delete('common_nav', "type='0'");
		DB::delete('common_nav', "name='{hr}'");
		DB::delete('common_nav', "name='{userpanelarea1}'");
		DB::delete('common_nav', "name='{userpanelarea2}'");
		$sql = implode('', file(DISCUZ_ROOT.'./install/data/install_data.sql'));
		preg_match("/\[update\_nav\](.+?)\[\/update\_nav\]/is", $sql, $a);
		runquery($a[1]);
		foreach($navs as $nav) {
			if($nav['identifier']) {
				if($nav['identifier'] == 4) {
					$homestatus = C::t('common_setting')->fetch('homestatus');
					$nav['available'] = $homestatus ? $nav['available'] : -1;
					if(!$navid) {
						DB::update('common_nav', array('available' => $homestatus ? 0 : -1),
								"navtype IN(0, 2, 3) AND type=0 AND identifier IN('feed', 'blog', 'album', 'share', 'doing', 'wall', '12', '13', '14', '15')");
						if($homestatus) {
							DB::update('common_nav', array('available' => 1),
									"navtype=2 AND type=0 AND identifier IN('feed', 'blog', 'album', 'share', 'doing', 'wall')");
						}
						DB::query("REPLACE INTO ".DB::table('common_setting')." VALUES ('homestyle', '1'),('homepagestyle', '1'),('feedstatus', '$homestatus'),('blogstatus', '$homestatus'),('doingstatus', '$homestatus'),('albumstatus', '$homestatus'),('sharestatus', '$homestatus'),('wallstatus', '$homestatus')");
					}
				}
				DB::update('common_nav', array('name' => $nav['name'], 'available' => $nav['available'], 'displayorder' => $nav['displayorder']),
					"navtype='$nav[navtype]' AND identifier='$nav[identifier]'");

				if($subnavs[$nav['id']]) {
					$parentid = DB::result_first("SELECT id FROM ".DB::table('common_nav')." WHERE navtype='$nav[navtype]' AND identifier='$nav[identifier]'");
					DB::update('common_nav', array('parentid' => $parentid), "parentid='$nav[id]'");
				}

			}
		}

		show_msg("导航数据升级完成", "$theurl?step=data&op=$nextop");

	} elseif($_GET['op'] == 'forumstatus') {

		$nextop = 'usergroup';
		$query = DB::query("SELECT fid FROM ".DB::table('forum_forum')." WHERE status='2'");
		if(DB::num_rows($query)) {
			while($row = DB::fetch($query)) {
				$fids[] = $row['fid'];
			}
			DB::update('forum_forum', array('status' => 1), "status='2'");
		}

		show_msg("版块状态升级完毕", "$theurl?step=data&op=$nextop");

	} elseif($_GET['op'] == 'usergroup') {
		$nextop = 'creditrule';
		DB::update('common_usergroup', array('allowvisit' => 2), "groupid='1'");
		DB::update('common_usergroup_field', array('allowbegincode' => 1), "groupid='1'");
		if(DB::result_first("SELECT COUNT(*) FROM ".DB::table('forum_forum')." WHERE allowmediacode>'0'") &&
			!DB::result_first("SELECT COUNT(*) FROM ".DB::table('common_usergroup_field')." WHERE allowmediacode>'0'")) {
			DB::update('common_usergroup_field', array('allowmediacode' => 1), "groupid<'4' OR groupid>'9'");
		}
		show_msg("用户升级完毕", "$theurl?step=data&op=$nextop");
	} elseif($_GET['op'] == 'creditrule') {
		$nextop = 'bbcode';
		$delrule = array('register', 'realname', 'invitefriend', 'report', 'uploadimage', 'editrealname', 'editrealemail', 'delavatar');
		$count = DB::result(DB::query("SELECT COUNT(*) FROM ".DB::table('common_credit_rule')." WHERE action IN(".dimplode($delrule).")"),0);
		if($count) {
			DB::query("DELETE FROM ".DB::table('common_credit_rule')." WHERE action IN(".dimplode($delrule).")");
		}
		DB::update('common_credit_rule', array('rulename' => '每天登录'), "rulename='每天登陆'");
		$count = DB::result_first("SELECT COUNT(*) FROM ".DB::table('common_credit_rule')." WHERE action='portalcomment'");
		if(!$count) {
			DB::query("INSERT INTO ".DB::table('common_credit_rule')." (`rulename`, `action`, `cycletype`, `cycletime`, `rewardnum`, `norepeat`, `extcredits1`, `extcredits2`, `extcredits3`, `extcredits4`, `extcredits5`, `extcredits6`, `extcredits7`, `extcredits8`, `fids`) VALUES ('文章评论','portalcomment','1','0','40','1','0','1','0','0','0','0','0','0','')");
		}
		$count = DB::result_first("SELECT COUNT(*) FROM ".DB::table('common_credit_rule')." WHERE action='followedcollection'");
		if(!$count) {
			DB::query("INSERT INTO ".DB::table('common_credit_rule')." (`rulename`, `action`, `cycletype`, `cycletime`, `rewardnum`, `norepeat`, `extcredits1`, `extcredits2`, `extcredits3`, `extcredits4`, `extcredits5`, `extcredits6`, `extcredits7`, `extcredits8`, `fids`) VALUES ('淘专辑被订阅','followedcollection','1','0','3','0','0','1','0','0','0','0','0','0','')");
		}

		show_msg("积分规则升级完毕", "$theurl?step=data&op=$nextop");
	} elseif($_GET['op'] == 'bbcode') {
		$nextop = 'stamp';
		$allowcusbbcodes = array();
		$query = DB::query("SELECT * FROM ".DB::table('common_usergroup_field'));
		while($row = DB::fetch($query)) {
			if($row['allowcusbbcode']) {
				$allowcusbbcodes[] = $row['groupid'];
			}
		}
		if($allowcusbbcodes) {
			DB::query("UPDATE ".DB::table('forum_bbcode')." SET perm='".implode("\t", $allowcusbbcodes)."' WHERE perm=''");
		}
		show_msg("自定义代码权限升级完毕", "$theurl?step=data&op=$nextop");
	} elseif($_GET['op'] == 'stamp') {
		$nextop = 'block_item';
		$stampnew = DB::result_first("SELECT COUNT(*) FROM ".DB::table('forum_thread')." WHERE stamp>'0'");
		if(!$stampnew) {
			$query = DB::query("SELECT t.tid, tm.stamp FROM ".DB::table('forum_thread')." t
				INNER JOIN ".DB::table('forum_threadmod')." tm ON t.tid=tm.tid AND tm.action='SPA'
				WHERE t.status|16=t.status");
			while($row = DB::fetch($query)) {
				DB::query("UPDATE ".DB::table('forum_thread')." SET stamp='$row[stamp]' WHERE tid='$row[tid]'", 'UNBUFFERED');
			}
		}
		if(!DB::result_first("SELECT COUNT(*) FROM ".DB::table('common_smiley')." WHERE url='010.gif'")) {
			DB::query("REPLACE INTO ".DB::table('common_smiley')." (typeid, displayorder, type, code, url) VALUES ('4','19','stamp','编辑采用','010.gif')");
		}
		if(!DB::result_first("SELECT COUNT(*) FROM ".DB::table('common_smiley')." WHERE url='010.small.gif'")) {
			DB::query("REPLACE INTO ".DB::table('common_smiley')." (typeid, displayorder, type, code, url) VALUES ('0','18','stamplist','编辑采用','010.small.gif')");
		}
		if(!DB::result_first("SELECT COUNT(*) FROM ".DB::table('common_smiley')." WHERE url='011.small.gif'")) {
			DB::query("REPLACE INTO ".DB::table('common_smiley')." (typeid, displayorder, type, code, url) VALUES ('0','20','stamplist','新人帖','011.small.gif')");
			$setnewbie = true;
		}
		require_once libfile('function/cache');
		updatecache('stamps');
		updatecache('stamptypeid');
		if($setnewbie) {
			$id = DB::result_first("SELECT displayorder FROM ".DB::table('common_smiley')." WHERE url='011.small.gif'");
			DB::query("REPLACE INTO ".DB::table('common_setting')." VALUES ('newbie', '$id')");
		}
		show_msg("鉴定图章升级完毕", "$theurl?step=data&op=$nextop");
	} elseif($_GET['op'] == 'block_item') {
		$nextop = 'block_permission';
		$bids = $items = $blocks = array();
		$query = DB::query("SELECT itemid, bid, pic, picflag, thumbpath FROM ".DB::table('common_block_item')." WHERE makethumb='1'");
		while($row = DB::fetch($query)) {
			if(empty($row['thumbpath'])) {
				$bids[$row['bid']] = $row['bid'];
				$items[] = $row;
			}
		}
		if($bids) {
			$query = DB::query("SELECT bid, picwidth, picheight FROM ".DB::table('common_block')." WHERE bid IN(".dimplode($bids).")");
			while($value = DB::fetch($query)) {
				$blocks[$value['bid']] = $value;
			}
			foreach($items as $item) {
				$block = $blocks[$item['bid']];
				$hash = md5($item['pic'].'-'.$item['picflag'].':'.$block['picwidth'].'|'.$block['picheight']);
				$thumbpath = 'block/'.substr($hash, 0, 2).'/'.$hash.'.jpg';
				DB::update('common_block_item', array('thumbpath' => $thumbpath), "itemid='$item[itemid]'");
			}
		}
		show_msg("模块缩略图权限升级完毕", "$theurl?step=data&op=$nextop");

	} elseif($_GET['op'] == 'block_permission') {
		$nextop = 'portalcategory_permission';
		if(!C::t('common_setting')->skey_exists('group_recommend')) {
			DB::query("UPDATE ".DB::table('common_block_permission')." SET allowmanage=allowsetting,allowrecommend=allowdata");
		}
		if(!DB::result_first('SELECT inheritedtplname FROM '.DB::table('common_template_permission')." WHERE inheritedtplname != '' LIMIT 1")) {
			$query = DB::query('SELECT * FROM '.DB::table('common_template_permission')." WHERE inheritedtplname = ''");
			$templatearr = array();
			while($value = DB::fetch($query)) {
				$templatearr[$value['targettplname']][] = $value;
			}
			if(!empty($templatearr)) {
				require_once libfile('class/blockpermission');
				$tplpermissions = new template_permission();
				foreach($templatearr as $tplname => $users) {
					$tplpermissions->add_users($tplname, $users);
				}
			}
		}
		show_msg("模块权限升级完毕", "$theurl?step=data&op=$nextop");
	} elseif($_GET['op'] == 'portalcategory_permission') {
		$nextop = 'portal_comment';
		if(!DB::result_first('SELECT inheritedcatid FROM '.DB::table('portal_category_permission')." WHERE inheritedcatid > '0' LIMIT 1")) {
			$query = DB::query('SELECT * FROM '.DB::table('portal_category_permission')." WHERE inheritedcatid = '0'");
			$catearr = array();
			while($value = DB::fetch($query)) {
				$catearr[$value['catid']][] = $value;
			}
			if(!empty($catearr)) {
				require_once libfile('class/portalcategory');
				$categorypermissions = new portal_category();
				foreach($catearr as $catid => $users) {
					$categorypermissions->add_users_perm($catid, $users);
				}
			}
		}
		show_msg("门户频道权限升级完毕", "$theurl?step=data&op=$nextop");
	} elseif($_GET['op'] == 'portal_comment') {
		$nextop = 'portal_article_cover_img';
		$one = DB::fetch_first('SELECT * FROM '.DB::table('portal_comment')." WHERE id=0 AND idtype='' LIMIT 1");
		if($one && isset($one['aid'])) {
			DB::query("UPDATE ".DB::table('portal_comment')." SET id=aid,idtype='aid' WHERE aid>0");
		}
		show_msg("文章评论升级完毕", "$theurl?step=data&op=$nextop");

	} elseif($_GET['op'] == 'portal_article_cover_img') {
		$nextop = 'block_style';
		$pic = DB::result_first('SELECT pic FROM '.DB::table('portal_article_title')." WHERE LENGTH(pic)>6 LIMIT 1");
		if($pic && is_numeric(substr($pic, 0, strpos($pic,'/')))) {
			DB::query("UPDATE ".DB::table('portal_article_title')." SET pic=CONCAT('portal/',pic) WHERE LENGTH(pic)>6");
		}
		show_msg("文章封面图升级完毕", "$theurl?step=data&op=$nextop");

	} elseif($_GET['op'] == 'block_style') {
		$nextop = 'block_script';
		$sql = implode('', file(DISCUZ_ROOT.'./install/data/install_data.sql'));
		preg_match("/\[block\_style\](.+?)\[\/block\_style\]/is", $sql, $a);
		unset($sql);
		preg_match_all("/\[key\:(.+?)\](.+?)\[\/key\]/is", $a[1], $aa);
		$data = array();
		if(!empty($aa[1])) {
			foreach($aa[1] as $key => $value){
				$data[$value] = $aa[2][$key];
			}
			$hashs = array_keys($data);
			$query = DB::query('SELECT hash FROM '.DB::table('common_block_style')." WHERE hash IN (".dimplode($hashs).")");
			while($value = DB::fetch($query)) {
				unset($data[$value['hash']]);
			}
			if(!empty($data)) {
				$sql = implode("\r\n", $data);
				runquery($sql);
			}
			DB::query("UPDATE ".DB::table('common_block_style')." SET name = replace(`name`, 'X1.5', '内置')");
			DB::query("UPDATE ".DB::table('common_block_style')." SET name = replace(`name`, 'X2.0', '内置')");
		}
		show_msg("模块模板升级完毕", "$theurl?step=data&op=$nextop");
	} elseif($_GET['op'] == 'block_script') {
		$nextop = 'common_usergroup_field';
		include_once libfile('function/block');
		$blocks = $styles = $styleids = array();
		$query = DB::query('SELECT * FROM '.DB::table('common_block')." WHERE blockclass='forum_attachment' OR blockclass='group_attachment'");
		while($value = DB::fetch($query)) {
			$blocks[$value['bid']] = $value;
			if(empty($value['blockstyle'])) {
				$styleids[$value['styleid']] = $value['styleid'];
			}
		}

		if($styleids) {
			$query = DB::query('SELECT * FROM '.DB::table('common_block_style')." WHERE styleid IN (".dimplode($styleids).")");
			while($value = DB::fetch($query)) {
				$value['template'] = dunserialize($value['template']);
				$styles[$value['styleid']] = $value;
			}
		}
		foreach($blocks as $bid => $block) {
			unset($block['bid']);
			if(empty($block['blockstyle'])) {
				$block['blockstyle'] = $styles[$block['styleid']];
			} else {
				$block['blockstyle'] = dunserialize($block['blockstyle']);
			}
			$block = block_conver_to_thread($block);
			DB::update('common_block', $block, array('bid'=>$bid));

			DB::query('DELETE FROM '.DB::table('common_block_style')." WHERE blockclass='forum_attachment' OR blockclass='group_attachment'");
			$_G['block'][$bid] = $block;
			block_updatecache($bid, true);
		}

		show_msg("模块脚本升级完毕", "$theurl?step=data&op=$nextop");
	} elseif($_GET['op'] == 'common_usergroup_field') {
		$nextop = 'group_index';
		if(!C::t('common_setting')->skey_exists('group_recommend')) {
			DB::query("UPDATE ".DB::table('common_usergroup_field')."
				SET allowcommentarticle=allowcomment,allowblogmod=allowblog,allowdoingmod=allowdoing,allowuploadmod=allowupload,allowsharemod=allowshare,allowdownlocalimg=allowpostarticle");
		}
		$queryraterange = DB::query("SELECT groupid, raterange FROM ".DB::table('common_usergroup_field'));
		while($usergroupfield = DB::fetch($queryraterange)) {
			if($usergroupfield['raterange']) {
				$raterangearray = array();
				foreach(explode("\n", $usergroupfield['raterange']) as $range) {
					$range = explode("\t", $range);
					if(count($range) == 4) {
						$raterangearray[$range[0]] = implode("\t", array($range[0], 'isself' => 0, 'min' => $range[1], 'max' => $range[2], 'mrpd' => $range[3]));
					}
				}
				if(!empty($raterangearray)) {
					DB::query("UPDATE ".DB::table('common_usergroup_field')." SET raterange='".implode("\n", $raterangearray)."' WHERE groupid='".$usergroupfield['groupid']."'");
				}
			}
		}

		if($first_to_2_5) {
			DB::query('UPDATE '.DB::table('common_usergroup_field')." SET allowat='50' WHERE groupid=1");
			DB::query('UPDATE '.DB::table('common_usergroup_field')." SET allowcreatecollection='5',allowcommentcollection='1',allowfollowcollection='30' WHERE groupid<'4' OR groupid>'9'");
		}

		show_msg("用户组权限升级完毕", "$theurl?step=data&op=$nextop");

	} elseif($_GET['op'] == 'group_index') {
		$nextop = 'domain';
		if(!C::t('common_setting')->skey_exists('group_recommend')) {
			$arr = array(
				0 => array('importfile'=>'./data/group_index.xml','primaltplname'=>'group/index', 'targettplname'=>'group/index'),
			);
			foreach ($arr as $v) {
				import_diy($v['importfile'], $v['primaltplname'], $v['targettplname']);
			}
		}
		show_msg("群组首页升级完毕", "$theurl?step=data&op=$nextop");

	} elseif($_GET['op'] == 'domain') {
		$nextop = 'pm';
		$newsettings = array();
		if(!empty($_G['config']['app']['domain'])) {
			$update = 0;
			foreach($_G['config']['app']['domain'] as $key => $value) {
				if($value && !$_G['setting']['domain']['app'][$key]) {
					$update = 1;
				}
			}
			if($update) {
				$domain = array(
					'defaultindex' => !empty($_G['config']['app']['default']) ? $_G['config']['app']['default'].'.php' : '',
					'app' => $_G['config']['app']['domain'],
				);
				$newsettings['domain'] = $domain;
			}
		}
		if(!empty($_G['config']['app']['default']) && !$_G['setting']['defaultindex']) {
			$newsettings['defaultindex'] = $_G['config']['app']['default'].'.php';
		}
		if(!empty($_G['config']['home']['holddomain']) && !$_G['setting']['holddomain']) {
			$holddomain = implode('|', explode(',', $_G['config']['home']['holddomain']));
			$newsettings['holddomain'] = $holddomain;
		}
		if(!empty($_G['config']['home']['allowdomain']) && !$_G['setting']['allowspacedomain']) {
			$newsettings['allowspacedomain'] = 1;
		}

		if(!DB::result_first("SELECT domain FROM ".DB::table('common_domain')." WHERE idtype='home'")) {
			$domainroot = $_G['config']['home']['domainroot'] ? $_G['config']['home']['domainroot'] : '';
			DB::query("INSERT INTO ".DB::table('common_domain')." (domain, domainroot, id, idtype) SELECT domain, '$domainroot', uid, 'home' FROM ".DB::table('common_member_field_home')." WHERE domain<>''");
		}
		if(!empty($newsettings)) {
			C::t('common_setting')->update_batch($newsettings);
		}
		show_msg("域名设置升级完毕", "$theurl?step=data&op=$nextop");

	} elseif($_GET['op'] == 'pm') {
		$nextop = 'allowgetimage';
			DB::query("UPDATE ".DB::table('common_member')." SET newpm='0', newprompt='0'");
		show_msg("新短消息状态重置完毕", "$theurl?step=data&op=$nextop");
	} elseif($_GET['op'] == 'allowgetimage') {
		$nextop = 'verify';
		if(!DB::result_first("SELECT COUNT(*) FROM ".DB::table('common_usergroup_field')." WHERE allowgetimage='1'")) {
			$query = DB::query("SELECT groupid, allowgetattach FROM ".DB::table('common_usergroup_field'));
			while($row = DB::fetch($query)) {
				DB::query('UPDATE '.DB::table('common_usergroup_field')." SET allowgetimage='".intval($row['allowgetattach'])."' WHERE groupid='$row[groupid]'");
			}
			$query = DB::query("SELECT uid, allowgetattach FROM ".DB::table('forum_access'));
			while($row = DB::fetch($query)) {
				DB::query('UPDATE '.DB::table('forum_access')." SET allowgetimage='".intval($row['allowgetattach'])."' WHERE uid='$row[uid]'");
			}
		}
		show_msg("查看图片权限升级完毕", "$theurl?step=data&op=$nextop");

	} elseif($_GET['op'] == 'verify') {
		$nextop = 'threadimage';
		$settings = $verifys = array();

		$settings = C::t('common_setting')->fetch_all(array('verify', 'realname', 'videophoto', 'video_allowviewspace'));
		$verifys = (array)dunserialize($settings['verify']);
		$updateverify = $_GET['updateverify'] ? true : false;
		if(!isset($verifys[6])) {
			$verifys[6] = array(
					'title' => '实名认证',
					'available' => $settings['realname'],
					'showicon' => 0,
					'viewrealname' => 0,
					'field' => array('realname' => realname),
					'icon' => ''
				);
			$verifys[7] = array(
					'title' => '视频认证',
					'available' => $settings['videophoto'],
					'showicon' => 0,
					'viewvideophoto' => $settings['video_allowviewspace'],
					'icon' => ''
				);
			if($verifys['enabled'] && ($settings['realname'] || $settings['videophoto'])) {
				$verifys['enabled'] = 1;
			}
			C::t('common_setting')->update('verify', $verifys);
			$updateverify = true;
		}
		if($updateverify) {
			$p = 1000;
			$i = !empty($_GET['i']) ? intval($_GET['i']) : 0;
			$n = 0;
			$t = DB::result_first("SELECT COUNT(*) FROM ".DB::table('common_member_profile')." WHERE realname != ''");
			if($t) {
				$query = DB::query('SELECT mp.realname, m.* FROM '.DB::table('common_member_profile')." mp LEFT JOIN ".DB::table('common_member')." m  USING(uid) WHERE mp.uid>'$i' AND mp.realname != '' LIMIT $p");
				while($value=DB::fetch($query)) {
					$n = intval($value['uid']);
					$havauser = DB::result_first("SELECT COUNT(*) FROM ".DB::table('common_member_verify')." WHERE uid='$n'");
					$data = array(
							'verify6' => '1',
							'verify7' => $value['videophotostatus'] ? 1 : 0,
						);
					if($havauser) {
						DB::update('common_member_verify', $data, array('uid' => $n));
					} else {
						$data['uid'] = $n;
						DB::insert('common_member_verify', $data);
					}

				}
				if($n) {
					show_msg("实名认证升级中[$n/$t]", "$theurl?step=data&op=verify&i=$n&updateverify=true");
				}
			}
		}
		show_msg("认证数据升级完毕", "$theurl?step=data&op=$nextop");
	} elseif($_GET['op'] == 'forumattach') {
		$nextop = 'forumstatlog';
		$limit = 10000;
		$start = !empty($_GET['start']) ? $_GET['start'] : 0;
		$needupgrade = DB::query("SELECT COUNT(*) FROM ".DB::table('forum_attachmentfield'), 'SILENT');
		$count = DB::result_first("SELECT COUNT(*) FROM ".DB::table('forum_attachment'));
		if($needupgrade && $count) {
			if(!$start) {
				for($i = 0;$i < 10;$i++) {
					DB::query("TRUNCATE ".DB::table('forum_attachment_'.$i));
				}
			}
			$query = DB::query("SELECT a.*,af.description FROM ".DB::table('forum_attachment')." a
				LEFT JOIN ".DB::table('forum_attachmentfield')." af USING(aid)
				ORDER BY aid LIMIT $start, $limit");
			if(DB::num_rows($query)) {
				while($row = DB::fetch($query)) {
					$tid = (string)$row['tid'];
					$tableid = $tid{strlen($tid)-1};
					DB::update('forum_attachment', array('tableid' => $tableid), array('aid' => $row['aid']));
					DB::insert('forum_attachment_'.$tableid, array(
						'aid' => $row['aid'],
						'tid' => $row['tid'],
						'pid' => $row['pid'],
						'uid' => $row['uid'],
						'dateline' => $row['dateline'],
						'filename' => $row['filename'],
						'filesize' => $row['filesize'],
						'attachment' => $row['attachment'],
						'remote' => $row['remote'],
						'description' => $row['description'],
						'readperm' => $row['readperm'],
						'price' => $row['price'],
						'isimage' => $row['isimage'],
						'width' => $row['width'],
						'thumb' => $row['thumb'],
						'picid' => $row['picid'],
					));
				}
				$start += $limit;
				show_msg("论坛附件表升级中 ... $start/$count", "$theurl?step=data&op=forumattach&start=$start");
			}
			DB::query("DROP TABLE `".DB::table('forum_attachmentfield')."`");
			$dropsql = array();
			$dropfields = array('width', 'dateline', 'readperm', 'price', 'filename', 'filetype', 'filesize', 'attachment', 'isimage', 'thumb', 'remote', 'picid');
			$query = DB::query('SHOW COLUMNS FROM `'.DB::table('forum_attachment').'`');
			while($field = DB::fetch($query)) {
				if(in_array($field['Field'], $dropfields, true)) {
					$dropsql[] = 'DROP `'.$field['Field'].'`';
				}
			}
			if($dropsql) {
				DB::query("ALTER TABLE ".DB::table('forum_attachment').' '.implode(', ', $dropsql));
			}
		}
		show_msg("论坛附件表升级完毕", "$theurl?step=data&op=$nextop");
	} elseif($_GET['op'] == 'forumstatlog') {
		$nextop = 'moderate';
		DB::query('DELETE FROM '.DB::table('forum_statlog')." WHERE logdate='0000-00-00'");
		show_msg("论坛版块统计数据升级完毕", "$theurl?step=data&op=$nextop");
	} elseif($_GET['op'] == 'threadimage') {
		$nextop = 'forumattach';
		$defaultmonth = 10;
		$limit = 1000;
		$start = !empty($_GET['start']) ? $_GET['start'] : 0;
		$needupgraded = DB::query("SELECT COUNT(*) FROM ".DB::table('forum_attachmentfield'), 'SILENT');
		if($needupgraded) {
			$cachefile = DISCUZ_ROOT.'./data/threadimage.cache';
			if(!file_exists($cachefile)) {
				$dateline = time() - 86400 * $defaultmonth * 30;
				$query = DB::query("SELECT tid from ".DB::table('forum_thread')." WHERE dateline>'$dateline' AND attachment='2' AND posttableid='0'");
				$data = array();
				while($row = DB::fetch($query)) {
					$data[] = $row['tid'];
				}
				if($data && @$fp = fopen($cachefile, 'w')) {
					fwrite($fp, implode('|', $data));
					fclose($fp);
				} else {
					show_msg("主题图片表无法处理，跳过", "$theurl?step=data&op=$nextop");
				}
			} else {
				$data = @file($cachefile);
				if(!$data) {
					show_msg("主题图片表无法处理，跳过", "$theurl?step=data&op=$nextop");
				}
				$data = explode('|', $data[0]);
			}
			$tids = array_slice($data, $start, $limit);
			if(!$tids) {
				@unlink($cachefile);
				show_msg("主题图片表处理完毕", "$theurl?step=data&op=$nextop");
			}
			$insertsql = array();
			foreach(C::t('forum_post')->fetch_all_by_tid(0, $tids, false, '', 0, 0, 1) as $row) {
				$threadimage = DB::fetch_first("SELECT attachment, remote FROM ".DB::table(getattachtablebytid($row['tid']))." WHERE pid='$row[pid]' AND isimage IN ('1', '-1') ORDER BY width DESC LIMIT 1");
				if($threadimage['attachment']) {
					$threadimage = daddslashes($threadimage);
					$insertsql[$row['tid']] = "('$row[tid]', '$threadimage[attachment]', '$threadimage[remote]')";
				}
			}
			if($insertsql) {
				DB::query("INSERT INTO ".DB::table('forum_threadimage')." (`tid`, `attachment`, `remote`) VALUES ".implode(',', $insertsql));
			}
			$start += $limit;
			show_msg("主题图片表处理中 ... $start ", "$theurl?step=data&op=threadimage&start=$start");
		} else {
			show_msg("主题图片表无法处理，跳过", "$theurl?step=data&op=$nextop");
		}
	} elseif($_GET['op'] == 'moderate') {

		$nextop = 'moderate_update';
		if(DB::fetch_first("SHOW TABLES LIKE '".DB::table('common_moderate')."'")) {
			$modcount = DB::result_first("SELECT COUNT(*) FROM ".DB::table('common_moderate'), array(), true);
		} else {
			$modcount = false;
		}
		if(!$modcount) {
			$query = DB::query("SELECT tid FROM ".DB::table('forum_thread')." WHERE displayorder='-2'");
			while($row = DB::fetch($query)) {
				updatemoderate('tid', $row['tid']);
			}
			loadcache('posttable_info');
			$posttables = array();
			if(!empty($_G['cache']['posttable_info']) && is_array($_G['cache']['posttable_info'])) {
				foreach($_G['cache']['posttable_info'] as $posttableid => $data) {
					$posttables[] = $posttableid;
				}
			} else {
				$posttables[] = 0;
			}
			foreach($posttables as $postableid) {
				$query = DB::query("SELECT pid FROM ".DB::table(getposttable($postableid))." WHERE invisible='-2' AND first='0'");
				while($row = DB::fetch($query)) {
					updatemoderate('pid', $row['pid']);
				}
			}

			$query = DB::query("SELECT blogid FROM ".DB::table('home_blog')." WHERE status='1'");
			while($row = DB::fetch($query)) {
				updatemoderate('blogid', $row['blogid']);
			}
			$query = DB::query("SELECT doid FROM ".DB::table('home_doing')." WHERE status='1'");
			while($row = DB::fetch($query)) {
				updatemoderate('doid', $row['doid']);
			}
			$query = DB::query("SELECT picid FROM ".DB::table('home_pic')." WHERE status='1'");
			while($row = DB::fetch($query)) {
				updatemoderate('picid', $row['picid']);
			}
			$query = DB::query("SELECT sid FROM ".DB::table('home_share')." WHERE status='1'");
			while($row = DB::fetch($query)) {
				updatemoderate('sid', $row['sid']);
			}
			$query = DB::query("SELECT idtype, cid FROM ".DB::table('home_comment')." WHERE status='1'");
			while($row = DB::fetch($query)) {
				updatemoderate($row['idtype'].'_cid', $row['cid']);
			}
			$query = DB::query("SELECT aid FROM ".DB::table('portal_article_title')." WHERE status='1'");
			while($row = DB::fetch($query)) {
				updatemoderate('aid', $row['aid']);
			}
			$query = DB::query("SELECT cid FROM ".DB::table('portal_comment')." WHERE idtype='aid' AND status='1'");
			while($row = DB::fetch($query)) {
				updatemoderate('aid_cid', $row['cid']);
			}
			$query = DB::query("SELECT cid FROM ".DB::table('portal_comment')." WHERE idtype='topic' AND status='1'");
			while($row = DB::fetch($query)) {
				updatemoderate('topicid_cid', $row['cid']);
			}
		}
		show_msg("审核数据升级完毕", "$theurl?step=data&op=$nextop");

	} elseif($_GET['op'] == 'moderate_update') {
		$nextop = 'founder';

		if($first_to_2_5 && DB::fetch_first("SHOW TABLES LIKE '".DB::table('common_moderate')."'")) {
			$tables = array(
				'tid' => 'forum_thread_moderate',
				'pid' => 'forum_post_moderate',
				'blogid' => 'home_blog_moderate',
				'picid' => 'home_pic_moderate',
				'doid' => 'home_doing_moderate',
				'sid' => 'home_share_moderate',
				'aid' => 'portal_article_moderate',
				'aid_cid' => 'portal_article_comment_moderate',
				'topicid_cid' => 'portal_comment_moderate',
				'uid_cid' => 'home_comment_moderate',
				'blogid_cid' => 'home_comment_moderate',
				'sid_cid' => 'home_comment_moderate',
				'picid_cid' => 'home_comment_moderate',
			);

			$query = DB::query("SELECT * FROM ".DB::table('common_moderate'));
			while($row = DB::fetch($query)) {
				if(isset($tables[$row['idtype']])) {
					$row = daddslashes($row);
					$table = $tables[$row['idtype']];
					if($table != 'home_comment_moderate') {
						unset($row['idtype']);
					}
					DB::insert($table, $row, false, true);
				}
			}
		}
		show_msg("审核数据转换完毕", "$theurl?step=data&op=$nextop");

	} elseif($_GET['op'] == 'founder') {

		$nextop = 'threadprofile';
		$founders = explode(',', str_replace(' ', '', $_G['config']['admincp']['founder']));
		if($founders) {
			foreach($founders as $founder) {
				if(is_numeric($founder)) {
					$fuid[] = $founder;
				} else {
					$fuser[] = $founder;
				}
			}
			$query = DB::query("SELECT uid FROM ".DB::table('common_member')." WHERE ".($fuid ? "uid IN (".dimplode($fuid).")" : '0')." OR ".($fuser ? "username IN (".dimplode($fuser).")" : '0'));
			$founders = array();
			while($founder = DB::fetch($query)) {
				$founders[] = $founder['uid'];
			}
			if($founders) {
				C::t('common_member')->update_admincp_manage($founders);
			}
		}

		show_msg("创始人数据升级完毕", "$theurl?step=data&op=$nextop");

	} elseif($_GET['op'] == 'threadprofile') {

		$nextop = 'plugin';
		if(!DB::result_first("SELECT COUNT(*) FROM ".DB::table("forum_threadprofile")." WHERE global=1")) {
			DB::query("INSERT INTO ".DB::table("forum_threadprofile")." (`id`, `name`, `template`, `global`) VALUES
				  (1, '默认方案', 'a:2:{s:4:\"left\";s:399:\"{numbercard}\r\n{groupicon}<p>{*}</p>{/groupicon}\r\n{authortitle}<p><em>{*}</em></p>{/authortitle}\r\n{customstatus}<p class=\"xg1\">{*}</p>{/customstatus}\r\n{star}<p>{*}</p>{/star}\r\n{upgradeprogress}<p>{*}</p>{/upgradeprogress}\r\n<dl class=\"pil cl\">\r\n\t<dt>{baseinfo=credits,1}</dt><dd>{baseinfo=credits,0}</dd>\r\n</dl>\r\n{medal}<p class=\"md_ctrl\">{*}</p>{/medal}\r\n<dl class=\"pil cl\">{baseinfo=field_qq,0}</dl>\";s:3:\"top\";s:82:\"<dl class=\"cl\">\r\n<dt>{baseinfo=credits,1}</dt><dd>{baseinfo=credits,0}</dd>\r\n</dl>\";}', 1);");
			DB::query("REPLACE INTO ".DB::table("forum_bbcode")." VALUES ('2','2','qq','bb_qq.gif','<a href=\"http://wpa.qq.com/msgrd?V=3&Uin={1}&amp;Site=[Discuz!]&amp;from=discuz&amp;Menu=yes\" target=\"_blank\"><img src=\"static/image/common/qq_big.gif\" border=\"0\"></a>','[qq]688888[/qq]','显示 QQ 在线状态，点这个图标可以和他（她）聊天','1','请输入 QQ 号码:<a href=\"\" class=\"xi2\" onclick=\"this.href=\'http://wp.qq.com/set.html?from=discuz&uin=\'+$(\'e_cst1_qq_param_1\').value\" target=\"_blank\" style=\"float:right;\">设置QQ在线状态&nbsp;&nbsp;</a>','1','21','1	2	3	10	11	12	13	14	15	16	17	18	19');");
		}

		show_msg("布局方案设置升级完毕", "$theurl?step=data&op=$nextop");

	} elseif($_GET['op'] == 'plugin') {

		$nextop = 'notification';

		loadcache('pluginlanguage_script');
		loadcache('pluginlanguage_template');
		loadcache('pluginlanguage_install');
		if(!$_G['cache']['pluginlanguage_script'] && !$_G['cache']['pluginlanguage_template'] && !$_G['cache']['pluginlanguage_install']) {
			$query = DB::query("SELECT identifier, pluginid, modules FROM ".DB::table('common_plugin'));
			while($plugin = DB::fetch($query)) {
				$plugin['modules'] = dunserialize($plugin['modules']);
				if(!empty($plugin['modules']['extra']['langexists'])) {
					@include DISCUZ_ROOT.'./data/plugindata/'.$plugin['identifier'].'.lang.php';
					if(!empty($scriptlang)) {
						$_G['cache']['pluginlanguage_script'][$plugin['identifier']] = $scriptlang[$plugin['identifier']];
					}
					if(!empty($templatelang)) {
						$_G['cache']['pluginlanguage_template'][$plugin['identifier']] = $templatelang[$plugin['identifier']];
					}
					if(!empty($installlang)) {
						$_G['cache']['pluginlanguage_install'][$plugin['identifier']] = $installlang[$plugin['identifier']];
					}
				}
			}
			savecache('pluginlanguage_script', $_G['cache']['pluginlanguage_script']);
			savecache('pluginlanguage_template', $_G['cache']['pluginlanguage_template']);
			savecache('pluginlanguage_install', $_G['cache']['pluginlanguage_install']);
		}

		show_msg("插件语言包数据升级完毕", "$theurl?step=data&op=$nextop");
	} elseif($_GET['op'] == 'notification') {
		$nextop = 'medal';
		if(!DB::result_first("SELECT id FROM ".DB::table('home_notification')." WHERE category>0")) {
			$_G['notice_structure']['follow'] = array('follow');
			$_G['notice_structure']['follower'] = array('follower');
			foreach($_G['notice_structure'] as $key => $val) {
				switch ($key) {
					case 'mypost' : $category = 1; break;
					case 'interactive' : $category = 2; break;
					case 'system' : $category = 3; break;
					case 'manage' : $category = 4; break;
					case 'follow' : $category = 5; break;
					case 'follower' : $category = 6; break;
					default :  $category = 0;
				}
				if($category) {
					DB::query("UPDATE ".DB::table('home_notification')." SET category=$category WHERE type IN(".dimplode($val).")");
				}
			}
			DB::query("UPDATE ".DB::table('home_notification')." SET category=2,type='comment' WHERE type IN('piccomment','blogcomment','sharecomment','doing')");
			DB::query("UPDATE ".DB::table('home_notification')." SET category=3,type='click' WHERE type IN('clickblog','clickarticle','clickpic')");
		}
		show_msg("提醒数据升级完毕", "$theurl?step=data&op=$nextop");
	} elseif($_GET['op'] == 'medal') {

		$nextop = 'closeswitch';

		if(!DB::result_first("SELECT COUNT(*) FROM ".DB::table('common_member_medal'))) {

			$query = DB::query("SELECT uid, medals FROM ".DB::table('common_member_field_forum')." WHERE medals != ''");
			while($member = DB::fetch($query)) {
				$medals = explode("\t", $member['medals']);
				foreach($medals as $medalid) {
					$medalid = intval($medalid);
					DB::insert('common_member_medal', array(
					    'uid' => $member['uid'],
					    'medalid' => $medalid
					), 0, 1);
				}
			}
		}
		show_msg("用户勋章数据升级完毕", "$theurl?step=data&op=$nextop");
	} elseif($_GET['op'] == 'closeswitch') {
		$nextop = 'end';
		if($first_to_2_5) {
			$newsettings = array();
			$newsettings['strongpw'] = 0;
			$newsettings['pwlength'] = 0;
			C::t('common_setting')->update_batch($newsettings);
		}
		show_msg("数据升级结束", "$theurl?step=data&op=$nextop");
	} else {

		$deletevar = array('app', 'home');//config中需要删除的项目
		$default_config = $_config = array();
		$default_configfile = DISCUZ_ROOT.'./config/config_global_default.php';
		if(!file_exists($default_configfile)) {
			exit('config_global_default.php was lost, please reupload this  file.');
		} else {
			include $default_configfile;
			$default_config = $_config;
		}
		$configfile = DISCUZ_ROOT.'./config/config_global.php';
		include $configfile;
		DB::query("UPDATE ".DB::table('common_plugin')." SET available='0' WHERE modules NOT LIKE '%s:6:\"system\";i:2;%'");
		if(save_config_file($configfile, $_config, $default_config, $deletevar)) {
			show_msg("数据处理完成", "$theurl?step=delete");
		} else {
			show_msg('"config/config_global.php" 文件已更新，由于 "config/" 目录不可写入，我们已将更新的文件保存到 "data/" 目录下，请通过 FTP 软件将其转移到 "config/" 目录下覆盖源文件。<br /><br /><a href="'.$theurl.'?step=delete">当您完成上述操作后点击这里继续</a>');
		}
	}

}elseif ($_GET['step'] == 'delete') {

	if(!$devmode) {
		show_msg("数据删除不处理，进入下一步", "$theurl?step=style");
	}

	$oldtables = array();
	$query = DB::query("SHOW TABLES LIKE '$config[tablepre]%'");
	while ($value = DB::fetch($query)) {
		$values = array_values($value);
		$oldtables[] = $values[0];
	}

	$sql = implode('', file($sqlfile));
	preg_match_all("/CREATE\s+TABLE.+?pre\_(.+?)\s+\((.+?)\)\s*(ENGINE|TYPE)\s*\=/is", $sql, $matches);
	$newtables = empty($matches[1])?array():$matches[1];

	$connecttables = array('common_member_connect', 'common_uin_black', 'connect_feedlog', 'connect_memberbindlog', 'connect_tlog', 'connect_tthreadlog', 'common_connect_guest', 'connect_disktask');

	$newsqls = empty($matches[0])?array():$matches[0];

	$deltables = array();
	$delcolumns = array();

	foreach ($oldtables as $tname) {
		$tname = substr($tname, strlen($config['tablepre']));
		if(in_array($tname, $newtables)) {
			$query = DB::query("SHOW CREATE TABLE ".DB::table($tname));
			$cvalue = DB::fetch($query);
			$oldcolumns = getcolumn($cvalue['Create Table']);

			$i = array_search($tname, $newtables);
			$newcolumns = getcolumn($newsqls[$i]);

			foreach ($oldcolumns as $colname => $colstruct) {
				if($colname == 'UNIQUE' || $colname == 'KEY') {
					foreach ($colstruct as $key_index => $key_value) {
						if(empty($newcolumns[$colname][$key_index])) {
							$delcolumns[$tname][$colname][$key_index] = $key_value;
						}
					}
				} else {
					if(empty($newcolumns[$colname])) {
						$delcolumns[$tname][] = $colname;
					}
				}
			}
		} else {
			if(!strexists($tname, 'uc_') && !strexists($tname, 'ucenter_') && !preg_match('/forum_(thread|post)_(\d+)$/i', $tname) && !in_array($tname, $connecttables)) {
				$deltables[] = $tname;
			}
		}
	}

	show_header();
	echo '<form method="post" autocomplete="off" action="'.$theurl.'?step=delete'.($_GET['from'] ? '&from='.rawurlencode($_GET['from']).'&frommd5='.rawurlencode($_GET['frommd5']) : '').'">';

	$deltablehtml = '';
	if($deltables) {
		$deltablehtml .= '<table>';
		foreach ($deltables as $tablename) {
			$deltablehtml .= "<tr><td><input type=\"checkbox\" name=\"deltables[$tablename]\" value=\"1\"></td><td>{$config['tablepre']}$tablename</td></tr>";
		}
		$deltablehtml .= '</table>';
		echo "<p>以下 <strong>数据表</strong> 与标准数据库相比是多余的:<br>您可以根据需要自行决定是否删除</p>$deltablehtml";
	}

	$delcolumnhtml = '';
	if($delcolumns) {
		$delcolumnhtml .= '<table>';
		foreach ($delcolumns as $tablename => $cols) {
			foreach ($cols as $coltype => $col) {
				if (is_array($col)) {
					foreach ($col as $index => $indexvalue) {
						$delcolumnhtml .= "<tr><td><input type=\"checkbox\" name=\"delcols[$tablename][$coltype][$index]\" value=\"1\"></td><td>{$config['tablepre']}$tablename</td><td>索引($coltype) $index $indexvalue</td></tr>";
					}
				} else {
					$delcolumnhtml .= "<tr><td><input type=\"checkbox\" name=\"delcols[$tablename][$col]\" value=\"1\"></td><td>{$config['tablepre']}$tablename</td><td>字段 $col</td></tr>";
				}
			}
		}
		$delcolumnhtml .= '</table>';

		echo "<p>以下 <strong>字段</strong> 与标准数据库相比是多余的:<br>您可以根据需要自行决定是否删除</p>$delcolumnhtml";
	}

	if(empty($deltables) && empty($delcolumns)) {
		echo "<p>与标准数据库相比，没有需要删除的数据表和字段</p><a href=\"$theurl?step=style".($_GET['from'] ? '&from='.rawurlencode($_GET['from']).'&frommd5='.rawurlencode($_GET['frommd5']) : '')."\">请点击进入下一步</a></p>";
	} else {
		echo "<p><input type=\"submit\" name=\"delsubmit\" value=\"提交删除\"></p><p>您也可以忽略多余的表和字段<br><a href=\"$theurl?step=style".($_GET['from'] ? '&from='.rawurlencode($_GET['from']).'&frommd5='.rawurlencode($_GET['frommd5']) : '')."\">直接进入下一步</a></p>";
	}
	echo '</form>';

	show_footer();
	exit();

} elseif ($_GET['step'] == 'style') {
	if(empty($_GET['confirm'])) {
		show_msg("请确认是否要恢复默认风格？<br /><br /><a href=\"$theurl?step=style&confirm=yes".($_GET['from'] ? '&from='.rawurlencode($_GET['from']).'&frommd5='.rawurlencode($_GET['frommd5']) : '')."\">[ 是 ]</a>&nbsp;&nbsp;<a href=\"$theurl?step=cache".($_GET['from'] ? '&from='.rawurlencode($_GET['from']).'&frommd5='.rawurlencode($_GET['frommd5']) : '')."\">[ 否 ]</a>", '');
	}

	define('IN_ADMINCP', true);
	require_once libfile('function/admincp');
	require_once libfile('function/importdata');
	$dir = DB::result_first("SELECT t.directory FROM ".DB::table('common_style')." s LEFT JOIN ".DB::table('common_template')." t ON t.templateid=s.templateid WHERE s.styleid='1'");
	import_styles(1, $dir, 1, 0, 0);
	C::t('common_setting')->update('styleid', 1);

	show_msg("默认风格已恢复，进入下一步", "$theurl?step=cache");

} elseif ($_GET['step'] == 'cache') {
	$appService = Cloud::loadClass('Service_App');
	try {
		$cloudstatus = $appService->checkCloudStatus();
	} catch (Exception $e) {
	}
	$result = false;
	if($cloudstatus == 'cloud' && !$appService->getCloudAppStatus('search')) {
		try{
			$cloudAppService = Cloud::loadClass('Service_Client_Cloud');
			$result = $cloudAppService->appOpen();
		} catch(Exception $e) {
		}
	}


	if($result == true) {
		$opensoso = '<br><br>友情提示：<br>为更好的降低论坛搜索时的数据压力，本次升级已经帮本站开通纵横搜索服务。<br>你可以在 <a href=\\\'../admin.php?frames=yes&action=cloud&operation=search\\\' target=\\\'_blank\\\'>站点后台-&gt;应用-&gt;纵横搜索 进行管理</a>。';
	}
	if(!$devmode && @$fp = fopen($lockfile, 'w')) {
		fwrite($fp, ' ');
		fclose($fp);
	}

	dir_clear(ROOT_PATH.'./data/template');
	dir_clear(ROOT_PATH.'./data/cache');
	dir_clear(ROOT_PATH.'./data/threadcache');
	dir_clear(ROOT_PATH.'./uc_client/data');
	dir_clear(ROOT_PATH.'./uc_client/data/cache');
	savecache('setting', '');

	if($_GET['from']) {
		show_msg('<span id="finalmsg">缓存更新中，请稍候 ...</span><iframe src="../misc.php?mod=initsys" style="display:none;" onload="window.location.href=\''.$_GET['from'].'\'"></iframe>');
	} else {
		show_msg('<span id="finalmsg">缓存更新中，请稍候 ...</span><iframe src="../misc.php?mod=initsys" style="display:none;" onload="document.getElementById(\'finalmsg\').innerHTML = \'恭喜，数据库结构升级完成！为了数据安全，请删除本文件。'.$opensoso.'\'"></iframe>');
	}

}

function has_another_special_table($tablename, $key) {
	if(!$key) {
		return $tablename;
	}

	$tables_array = get_special_tables_array($tablename);

	if($key > count($tables_array)) {
		return FALSE;
	} else {
		return TRUE;
	}
}

function get_special_tables_array($tablename) {
	$tablename = DB::table($tablename);
	$tablename = str_replace('_', '\_', $tablename);
	$query = DB::query("SHOW TABLES LIKE '{$tablename}\_%'");
	$dbo = DB::object();
	$tables_array = array();
	while($row = $dbo->fetch_array($query, $dbo->drivertype == 'mysqli' ? MYSQLI_NUM : MYSQL_NUM)) {
		if(preg_match("/^{$tablename}_(\\d+)$/i", $row[0])) {
			$prefix_len = strlen($dbo->tablepre);
			$row[0] = substr($row[0], $prefix_len);
			$tables_array[] = $row[0];
		}
	}
	return $tables_array;
}

function get_special_table_by_num($tablename, $num) {
	$tables_array = get_special_tables_array($tablename);

	$num --;
	return isset($tables_array[$num]) ? $tables_array[$num] : FALSE;
}

function getcolumn($creatsql) {

	$creatsql = preg_replace("/ COMMENT '.*?'/i", '', $creatsql);
	preg_match("/\((.+)\)\s*(ENGINE|TYPE)\s*\=/is", $creatsql, $matchs);

	$cols = explode("\n", $matchs[1]);
	$newcols = array();
	foreach ($cols as $value) {
		$value = trim($value);
		if(empty($value)) continue;
		$value = remakesql($value);
		if(substr($value, -1) == ',') $value = substr($value, 0, -1);

		$vs = explode(' ', $value);
		$cname = $vs[0];

		if($cname == 'KEY' || $cname == 'INDEX' || $cname == 'UNIQUE') {

			$name_length = strlen($cname);
			if($cname == 'UNIQUE') $name_length = $name_length + 4;

			$subvalue = trim(substr($value, $name_length));
			$subvs = explode(' ', $subvalue);
			$subcname = $subvs[0];
			$newcols[$cname][$subcname] = trim(substr($value, ($name_length+2+strlen($subcname))));

		}  elseif($cname == 'PRIMARY') {

			$newcols[$cname] = trim(substr($value, 11));

		}  else {

			$newcols[$cname] = trim(substr($value, strlen($cname)));
		}
	}
	return $newcols;
}

function remakesql($value) {
	$value = trim(preg_replace("/\s+/", ' ', $value));
	$value = str_replace(array('`',', ', ' ,', '( ' ,' )', 'mediumtext'), array('', ',', ',','(',')','text'), $value);
	return $value;
}

function show_msg($message, $url_forward='', $time = 1, $noexit = 0, $notice = '') {

	if($url_forward) {
		$url_forward = $_GET['from'] ? $url_forward.'&from='.rawurlencode($_GET['from']).'&frommd5='.rawurlencode($_GET['frommd5']) : $url_forward;
		$message = "<a href=\"$url_forward\">$message (跳转中...)</a><br>$notice<script>setTimeout(\"window.location.href ='$url_forward';\", $time);</script>";
	}

	show_header();
	print<<<END
	<table>
	<tr><td>$message</td></tr>
	</table>
END;
	show_footer();
	!$noexit && exit();
}


function show_header() {
	global $config;

	$nowarr = array($_GET['step'] => ' class="current"');
	if(in_array($_GET['step'], array('waitingdb','prepare'))) {
		$nowarr = array('sql' => ' class="current"');
	}
	print<<<END
	<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
	<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
	<meta http-equiv="Content-Type" content="text/html; charset=$config[charset]" />
	<title> 数据库升级程序 </title>
	<style type="text/css">
	* {font-size:12px; font-family: Verdana, Arial, Helvetica, sans-serif; line-height: 1.5em; word-break: break-all; }
	body { text-align:center; margin: 0; padding: 0; background: #F5FBFF; }
	.bodydiv { margin: 40px auto 0; width:720px; text-align:left; border: solid #86B9D6; border-width: 5px 1px 1px; background: #FFF; }
	h1 { font-size: 18px; margin: 1px 0 0; line-height: 50px; height: 50px; background: #E8F7FC; color: #5086A5; padding-left: 10px; }
	#menu {width: 100%; margin: 10px auto; text-align: center; }
	#menu td { height: 30px; line-height: 30px; color: #999; border-bottom: 3px solid #EEE; }
	.current { font-weight: bold; color: #090 !important; border-bottom-color: #F90 !important; }
	input { border: 1px solid #B2C9D3; padding: 5px; background: #F5FCFF; }
	#footer { font-size: 10px; line-height: 40px; background: #E8F7FC; text-align: center; height: 38px; overflow: hidden; color: #5086A5; margin-top: 20px; }
	</style>
	</head>
	<body>
	<div class="bodydiv">
	<h1>数据库升级工具</h1>
	<div style="width:90%;margin:0 auto;">
	<table id="menu">
	<tr>
	<td{$nowarr[start]}>升级开始</td>
	<td{$nowarr[sql]}>数据库结构添加与更新</td>
	<td{$nowarr[data]}>数据更新</td>
	<td{$nowarr[delete]}>数据库结构删除</td>
	<td{$nowarr[cache]}>升级完成</td>
	</tr>
	</table>
	<br>
END;
}

function show_footer() {
	print<<<END
	</div>
	<div id="footer">&copy; Comsenz Inc. 2001-2013 http://www.comsenz.com</div>
	</div>
	<br>
	</body>
	</html>
END;
}

function runquery($sql) {
	global $_G;
	$tablepre = $_G['config']['db'][1]['tablepre'];
	$dbcharset = $_G['config']['db'][1]['dbcharset'];

	$sql = str_replace("\r", "\n", str_replace(array(' {tablepre}', ' cdb_', ' `cdb_', ' pre_', ' `pre_'), array(' '.$tablepre, ' '.$tablepre, ' `'.$tablepre, ' '.$tablepre, ' `'.$tablepre), $sql));
	$ret = array();
	$num = 0;
	foreach(explode(";\n", trim($sql)) as $query) {
		$queries = explode("\n", trim($query));
		foreach($queries as $query) {
			$ret[$num] .= $query[0] == '#' || $query[0].$query[1] == '--' ? '' : $query;
		}
		$num++;
	}
	unset($sql);

	foreach($ret as $query) {
		$query = trim($query);
		if($query) {

			if(substr($query, 0, 12) == 'CREATE TABLE') {
				$name = preg_replace("/CREATE TABLE ([a-z0-9_]+) .*/is", "\\1", $query);
				DB::query(create_table($query, $dbcharset));

			} else {
				DB::query($query);
			}

		}
	}
}


function import_diy($importfile, $primaltplname, $targettplname) {
	global $_G;

	$css = $html = '';
	$arr = array();

	$content = file_get_contents(realpath($importfile));
	if (empty($content)) return $arr;
	require_once DISCUZ_ROOT.'./source/class/class_xml.php';
	$diycontent = xml2array($content);

	if ($diycontent) {

		foreach ($diycontent['layoutdata'] as $key => $value) {
			if (!empty($value)) getframeblock($value);
		}
		$newframe = array();
		foreach ($_G['curtplframe'] as $value) {
			$newframe[] = $value['type'].random(6);
		}

		$mapping = array();
		if (!empty($diycontent['blockdata'])) {
			$mapping = block_import($diycontent['blockdata']);
			unset($diycontent['blockdata']);
		}

		$oldbids = $newbids = array();
		if (!empty($mapping)) {
			foreach($mapping as $obid=>$nbid) {
				$oldbids[] = 'portal_block_'.$obid;
				$newbids[] = 'portal_block_'.$nbid;
			}
		}

		require_once DISCUZ_ROOT.'./source/class/class_xml.php';
		$xml = array2xml($diycontent['layoutdata'],true);
		$xml = str_replace($oldbids, $newbids, $xml);
		$xml = str_replace((array)array_keys($_G['curtplframe']), $newframe, $xml);
		$diycontent['layoutdata'] = xml2array($xml);

		$css = str_replace($oldbids, $newbids, $diycontent['spacecss']);
		$css = str_replace((array)array_keys($_G['curtplframe']), $newframe, $css);

		$arr['spacecss'] = $css;
		$arr['layoutdata'] = $diycontent['layoutdata'];
		$arr['style'] = $diycontent['style'];
		save_diy_data($primaltplname, $targettplname, $arr, true);
	}
	return $arr;
}

function save_config_file($filename, $config, $default, $deletevar) {
	$config = setdefault($config, $default, $deletevar);
	$date = gmdate("Y-m-d H:i:s", time() + 3600 * 8);
	$content = <<<EOT
<?php


\$_config = array();

EOT;
	$content .= getvars(array('_config' => $config));
	$content .= "\r\n// ".str_pad('  THE END  ', 50, '-', STR_PAD_BOTH)." //\r\n\r\n?>";
	if(!is_writable($filename) || !($len = file_put_contents($filename, $content))) {
		file_put_contents(DISCUZ_ROOT.'./data/config_global.php', $content);
		return 0;
	}
	return 1;
}

function setdefault($var, $default, $deletevar) {
	foreach ($default as $k => $v) {
		if(!isset($var[$k])) {
			$var[$k] = $default[$k];
		} elseif(is_array($v)) {
			$var[$k] = setdefault($var[$k], $default[$k]);
		}
	}
	foreach ($deletevar as $k) {
		unset($var[$k]);
	}
	return $var;
}

function getvars($data, $type = 'VAR') {
	$evaluate = '';
	foreach($data as $key => $val) {
		if(!preg_match("/^[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*$/", $key)) {
			continue;
		}
		if(is_array($val)) {
			$evaluate .= buildarray($val, 0, "\${$key}")."\r\n";
		} else {
			$val = addcslashes($val, '\'\\');
			$evaluate .= $type == 'VAR' ? "\$$key = '$val';\n" : "define('".strtoupper($key)."', '$val');\n";
		}
	}
	return $evaluate;
}

function buildarray($array, $level = 0, $pre = '$_config') {
	static $ks;
	if($level == 0) {
		$ks = array();
		$return = '';
	}

	foreach ($array as $key => $val) {
		if($level == 0) {
			$newline = str_pad('  CONFIG '.strtoupper($key).'  ', 70, '-', STR_PAD_BOTH);
			$return .= "\r\n// $newline //\r\n";
			if($key == 'admincp') {
				$newline = str_pad(' Founders: $_config[\'admincp\'][\'founder\'] = \'1,2,3\'; ', 70, '-', STR_PAD_BOTH);
				$return .= "// $newline //\r\n";
			}
		}

		$ks[$level] = $ks[$level - 1]."['$key']";
		if(is_array($val)) {
			$ks[$level] = $ks[$level - 1]."['$key']";
			$return .= buildarray($val, $level + 1, $pre);
		} else {
			$val =  is_string($val) || strlen($val) > 12 || !preg_match("/^\-?[1-9]\d*$/", $val) ? '\''.addcslashes($val, '\'\\').'\'' : $val;
			$return .= $pre.$ks[$level - 1]."['$key']"." = $val;\r\n";
		}
	}
	return $return;
}

function dir_clear($dir) {
	global $lang;
	if($directory = @dir($dir)) {
		while($entry = $directory->read()) {
			$filename = $dir.'/'.$entry;
			if(is_file($filename)) {
				@unlink($filename);
			}
		}
		$directory->close();
		@touch($dir.'/index.htm');
	}
}

function block_conver_to_thread($block){
	if($block['blockclass'] == 'forum_attachment') {
		$block['blockclass'] = 'forum_thread';
		$block['script'] = 'thread';
	} else if($block['blockclass'] == 'group_attachment') {
		$block['blockclass'] = 'group_thread';
		$block['script'] = 'groupthread';
	}
	$block['param'] = is_array($block['param']) ? $block['param'] : (array)dunserialize($block['param']);
	unset($block['param']['threadmethod']);
	$block['param']['special'] = array(0);
	$block['param']['picrequired'] = 1;
	$block['param'] = serialize($block['param']);
	$block['styleid'] = 0;
	$block['blockstyle'] = block_style_conver_to_thread($block['blockstyle'], $block['blockclass']);
	return $block;
}

function block_style_conver_to_thread($style, $blockclass) {
	$template = block_build_template($style['template']);
	$search = array('threadurl', 'threadsubject', 'threadsummary', 'filesize', 'downloads');
	$replace = array('url', 'title', 'summary', '');
	$template = str_replace($search, $replace, $template);
	$arr = array(
		'name' => '',
		'blockclass' => $blockclass,
	);
	block_parse_template($template, $arr);
	$arr['fields'] = dunserialize($arr['fields']);
	$arr['template'] = dunserialize($arr['template']);
	$arr = serialize($arr);
	return $arr;
}

function create_table($sql, $dbcharset) {
	$type = strtoupper(preg_replace("/^\s*CREATE TABLE\s+.+\s+\(.+?\).*(ENGINE|TYPE)\s*=\s*([a-z]+?).*$/isU", "\\2", $sql));
	$type = in_array($type, array('MYISAM', 'HEAP', 'MEMORY')) ? $type : 'MYISAM';
	return preg_replace("/^\s*(CREATE TABLE\s+.+\s+\(.+?\)).*$/isU", "\\1", $sql).
	(helper_dbtool::dbversion() > '4.1' ? " ENGINE=$type DEFAULT CHARSET=".$dbcharset : " TYPE=$type");
}

?>