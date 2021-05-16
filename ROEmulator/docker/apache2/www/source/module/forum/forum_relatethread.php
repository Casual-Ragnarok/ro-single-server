<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: forum_relatethread.php 25791 2011-11-22 07:10:59Z zhengqingpeng $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

if(!$_G['setting']['qihoo']['relate']['bbsnum']) {
	exit;
}

$_SERVER = empty($_SERVER) ? $HTTP_SERVER_VARS : $_SERVER;
$_GET = empty($_GET) ? $HTTP_GET_VARS : $_GET;

$site = $_SERVER['HTTP_HOST'];
$subjectenc = rawurlencode($_GET['subjectenc']);
$tags = explode(' ',trim($_GET['tagsenc']));
$tid = intval($_GET['tid']);

if($_GET['verifykey'] <> md5($_G['authkey'].$tid.$subjectenc.CHARSET.$site)) {
	exit();
}

$tshow = !$_G['setting']['qihoo']['relate']['position'] ? 'mid' : 'bot';
$intnum = intval($_G['setting']['qihoo']['relate']['bbsnum']);
$extnum = intval($_G['setting']['qihoo']['relate']['webnum']);
$exttype = $_G['setting']['qihoo']['relate']['type'];
$up = intval($_GET['qihoo_up']);
$data = @implode('', file("http://related.code.qihoo.com/related.html?title=$subjectenc&ics=".CHARSET."&ocs=".CHARSET."&site=$site&sort=pdate&tshow=$tshow&intnum=$intnum&extnum=$extnum&exttype=$exttype&up=$up"));

if($data) {
	$timestamp = time();
	$chs = '';

	if(PHP_VERSION > '5' && CHARSET != 'utf-8') {
		require_once libfile('class/chinese');
		$chs = new Chinese('utf-8', CHARSET);
	}

	$parser = xml_parser_create();
	xml_parser_set_option($parser, XML_OPTION_CASE_FOLDING, 0);
	xml_parser_set_option($parser, XML_OPTION_SKIP_WHITE, 1);
	xml_parse_into_struct($parser, $data, $values, $index);
	xml_parser_free($parser);

	$xmldata = array('chanl', 'fid', 'title', 'tid', 'author', 'pdate', 'rdate', 'rnum', 'vnum', 'insite');
	$relatedthreadlist = $keywords = array();
	$nextuptime = 0;
	foreach($index as $tag => $valuearray) {
		if(in_array($tag, $xmldata)) {
			foreach($valuearray as $key => $value) {
				if($values[$index['title'][$key]]['value']) {
					$relatedthreadlist[$key][$tag] = !empty($chs) ? $chs->convert(trim($values[$value]['value'])) : trim($values[$value]['value']);
					$relatedthreadlist[$key]['fid'] = !$values[$index['fid'][$key]]['value'] ? preg_replace("/(.+?)\/forum\-(\d+)\-(\d+)\.html/", "\\2", trim($values[$index['curl'][$key]]['value'])) : trim($values[$index['fid'][$key]]['value']);
					$relatedthreadlist[$key]['tid'] = !$values[$index['tid'][$key]]['value'] ? preg_replace("/(.+?)\/thread\-(\d+)\-(\d+)-(\d+)\.html/", "\\2", trim($values[$index['surl'][$key]]['value'])) : trim($values[$index['tid'][$key]]['value']);
				}
			}
		} elseif(in_array($tag, array('kw', 'ekw'))) {
			$type = $tag == 'kw' ? 'general' : 'trade';
			foreach($valuearray as $value) {
				$keywords[$type][] = !empty($chs) ? $chs->convert(trim($values[$value]['value'])) : trim($values[$value]['value']);
			}
		} elseif($tag == 'nextuptime') {
			$nextuptime = $values[$index['nextuptime'][0]]['value'];
		} elseif($tag == 'keep' && intval($values[$index['keep'][0]]['value'])) {
			exit;
		}
	}

	$generalnew = array();
	if($keywords['general']) {
		$searchkeywords = rawurlencode(implode(' ', $keywords['general']));
		foreach($keywords['general'] as $keyword) {
			$generalnew[] = $keyword;
			if(!in_array($keyword, $tags)) {
				$relatedkeywords .= '<a href="forum.php?mod=search&srchtype=qihoo&amp;srchtxt='.rawurlencode($keyword).'&amp;searchsubmit=yes" target="_blank"><strong><font color="red">'.$keyword.'</font></strong></a>&nbsp;';
			}
		}
	}
	$keywords['general'] = $generalnew;

	$threadlist = array();
	if($relatedthreadlist) {
		foreach($relatedthreadlist as $key => $relatedthread) {
			if($relatedthread['insite'] == 1) {
				$threadlist['bbsthread'][] = $relatedthread;
			} elseif($_G['setting']['qihoo']['relate']['webnum']) {
				if(empty($_G['setting']['qihoo']['relate']['banurl']) || !preg_match($_G['setting']['qihoo']['relate']['banurl'], $relatedthread['tid'])) {
					$threadlist['webthread'][] = $relatedthread;
				}
			}
		}
		$threadlist['bbsthread'] = $threadlist['bbsthread'] ? array_slice($threadlist['bbsthread'], 0, $_G['setting']['qihoo']['relate']['bbsnum']) : array();
		$threadlist['webthread'] = $threadlist['webthread'] ? array_slice($threadlist['webthread'], 0, $_G['setting']['qihoo']['relate']['bbsnum'] - count($threadlist['bbsthread'])) : array();
		$relatedthreadlist = array_merge($threadlist['bbsthread'], $threadlist['webthread']);
	}

	$keywords['general'] = $keywords['general'][0] ? implode("\t", $keywords['general']) : '';
	$keywords['trade'] = $keywords['trade'][0] ? implode("\t", $keywords['trade']) : '';
	$relatedthreads = $relatedthreadlist ? addslashes(serialize($relatedthreadlist)) : '';
	$expiration = $nextuptime ? $nextuptime : $timestamp + 86400;

	$data = array('tid' => $tid, 'type' => 'general', 'expiration' => $expiration, 'keywords' => $keywords[general], 'relatedthreads' => $relatedthreads);
	C::t('forum_relatedthread')->insert($data, 0, 1);
	if($keywords['trade']) {
		$data['type'] = 'trade';
		$data['keywords'] = $keywords['trade'];
		C::t('forum_relatedthread')->insert($data, 0, 1);
	}
}

?>