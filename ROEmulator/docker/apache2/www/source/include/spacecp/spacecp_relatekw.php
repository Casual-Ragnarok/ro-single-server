<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: spacecp_relatekw.php 6752 2010-03-25 08:47:54Z cnteacher $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

if(!$_G['setting']['headercharset']) {
	@header('Content-Type: text/html; charset='.CHARSET);
}
$_G['inajax'] = 1;

$subjectenc = rawurlencode(strip_tags($_GET['subjectenc']));
$messageenc = rawurlencode(strip_tags(preg_replace("/\[.+?\]/U", '', $_GET['messageenc'])));
$data = @implode('', file("http://keyword.discuz.com/related_kw.html?title=$subjectenc&content=$messageenc&ics={$_G[charset]}&ocs={$_G[charset]}"));

if($data) {
	$parser = xml_parser_create();
	xml_parser_set_option($parser, XML_OPTION_CASE_FOLDING, 0);
	xml_parser_set_option($parser, XML_OPTION_SKIP_WHITE, 1);
	xml_parse_into_struct($parser, $data, $values, $index);
	xml_parser_free($parser);

	$kws = array();

	foreach($values as $valuearray) {
		if($valuearray['tag'] == 'kw' || $valuearray['tag'] == 'ekw') {
			if(PHP_VERSION > '5' && CHARSET != 'utf-8') {
				$kws[] = diconv(trim($valuearray['value']), 'utf-8');
			} else {
				$kws[] = trim($valuearray['value']);
			}
		}
	}

	$return = '';
	if($kws) {
		foreach($kws as $kw) {
			$kw = dhtmlspecialchars($kw);
			$return .= $kw.' ';
		}
		$return = trim($return);
	}

	showmessage($return, '', array(), array('msgtype' => 3, 'handle' => false));
} else {
	showmessage(' ', '', array(), array('msgtype' => 3, 'handle' => false));
}

?>