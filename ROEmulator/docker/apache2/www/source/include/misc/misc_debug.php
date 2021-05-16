<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: misc_debug.php 25889 2011-11-24 09:52:20Z monkey $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

if(DISCUZ_DEBUG && ckfounder($_G['uid'])) {
	print<<<EOF
	<style>
	.tclass, .tclass2 {
	text-align:left;width:900px;border:0;border-collapse:collapse;margin-bottom:5px;table-layout: fixed; word-wrap: break-word;background:#FFF;}
	.tclass table, .tclass2 table {width:100%;border:0;table-layout: fixed; word-wrap: break-word;}
	.tclass table td, .tclass2 table td {border-bottom:0;border-right:0;border-color: #ADADAD;}
	.tclass th, .tclass2 th {border:1px solid #000;background:#CCC;padding: 2px;font-family: Courier New, Arial;font-size: 11px;}
	.tclass td, .tclass2 td {border:1px solid #000;background:#FFFCCC;padding: 2px;font-family: Courier New, Arial;font-size: 11px;}
	.tclass2 th {background:#D5EAEA;}
	.tclass2 td {background:#FFFFFF;}
	.firsttr td {border-top:0;}
	.firsttd {border-left:none !important;}
	.bold {font-weight:bold;}
	</style>
	<div id="uchome_debug" style="display:;">
EOF;
	$class = 'tclass2';
	if(empty($_G['debug_query'])) $_G['debug_query'] = array();
	foreach ($_G['debug_query'] as $dkey => $debug) {
	($class == 'tclass')?$class = 'tclass2':$class = 'tclass';
	echo '<table cellspacing="0" class="'.$class.'"><tr><th rowspan="2" width="20">'.($dkey+1).'</th><td width="60">'.$debug['time'].' ms</td><td class="bold">'. dhtmlspecialchars($debug['sql']).'</td></tr>';
	if(!empty($debug['info'])) {
		echo '<tr><td>Info</th><td>'.$debug['info'].'</td></tr>';
	}
	if(!empty($debug['explain'])) {
		echo '<tr><td>Explain</td><td><table cellspacing="0"><tr class="firsttr"><td width="5%" class="firsttd">id</td><td width="10%">select_type</td><td width="12%">table</td><td width="5%">type</td><td width="20%">possible_keys</td><td width="10%">key</td><td width="8%">key_len</td><td width="5%">ref</td><td width="5%">rows</td><td width="20%">Extra</td></tr><tr>';
		foreach ($debug['explain'] as $ekey => $explain) {
		($ekey == 'id')?$tdclass = ' class="firsttd"':$tdclass='';
		if(empty($explain)) $explain = '-';
		echo '<td'.$tdclass.'>'.$explain.'</td>';
		}
		echo '</tr></table></td></tr>';
	}
	echo '</table>';
	}
	if($values = $_COOKIE) {
	($class == 'tclass')?$class = 'tclass2':$class = 'tclass';
	$i = 1;
	echo '<table class="'.$class.'">';
	foreach ($values as $ckey => $cookie) {
		echo '<tr><th width="20">'.$i.'</th><td width="250">$_COOKIE[\''.$ckey.'\']</td><td>'.$cookie.'</td></tr>';
		$i++;
	}
	echo '</table>';
	}
	if($files = get_included_files()) {
	($class == 'tclass')?$class = 'tclass2':$class = 'tclass';
	echo '<table class="'.$class.'">';
	foreach ($files as $fkey => $file) {
		echo '<tr><th width="20">'.($fkey+1).'</th><td>'.$file.'</td></tr>';
	}
	echo '</table>';
	}
	if($values = $_SERVER) {
	($class == 'tclass')?$class = 'tclass2':$class = 'tclass';
	$i = 1;
	echo '<table class="'.$class.'">';
	foreach ($values as $ckey => $cookie) {
		echo '<tr><th width="20">'.$i.'</th><td width="250">$_SERVER[\''.$ckey.'\']</td><td>'.$cookie.'</td></tr>';
		$i++;
	}
	echo '</table>';
	}
	echo '</div>';
}

?>