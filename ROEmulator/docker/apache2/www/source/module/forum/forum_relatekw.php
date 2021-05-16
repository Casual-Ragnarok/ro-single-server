<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: forum_relatekw.php 29236 2012-03-30 05:34:47Z chenmengshu $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

if($tid = @intval($_GET['tid'])) {
	$data = C::t('forum_post')->fetch_threadpost_by_tid_invisible($tid);
	$subject = $data['subject'];
	$message = cutstr($data['message'], 500, '');
	$pid = $data['pid'];
} else {
	$subject = $_GET['subjectenc'];
	$message = $_GET['messageenc'];
}

$subjectenc = rawurlencode(strip_tags($subject));
$messageenc = rawurlencode(strip_tags(preg_replace("/\[.+?\]/U", '', $message)));
$data = @implode('', file("http://keyword.discuz.com/related_kw.html?ics=".CHARSET."&ocs=".CHARSET."&title=$subjectenc&content=$messageenc"));

if($data) {

	if(PHP_VERSION > '5' && CHARSET != 'utf-8') {
		require_once libfile('class/chinese');
		$chs = new Chinese('utf-8', CHARSET);
	}

	$parser = xml_parser_create();
	xml_parser_set_option($parser, XML_OPTION_CASE_FOLDING, 0);
	xml_parser_set_option($parser, XML_OPTION_SKIP_WHITE, 1);
	xml_parse_into_struct($parser, $data, $values, $index);
	xml_parser_free($parser);

	$kws = array();

	foreach($values as $valuearray) {
		if($valuearray['tag'] == 'kw' || $valuearray['tag'] == 'ekw') {
			$kws[] = !empty($chs) ? $chs->convert(trim($valuearray['value'])) : trim($valuearray['value']);
		}
	}

	$return = '';
	if($kws) {
		foreach($kws as $kw) {
			$kw = dhtmlspecialchars($kw);
			$return .= $kw.',';
		}
		$return = dhtmlspecialchars($return);
	}
	$return = substr($return, 0, strlen($return)-1);

	if(!$tid) {
		$_G['inajax'] = 1;
		include template('forum/relatekw');
	} elseif($kws) {
		loadcache('censor');
		C::t('forum_post')->update('tid:'.$_G['tid'], $pid, array(
			'tags' => implode(',', $kws),
		));
	}
}

?>