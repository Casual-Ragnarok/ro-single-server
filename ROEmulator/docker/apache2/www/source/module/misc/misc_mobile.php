<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: misc_mobile.php 25221 2011-10-31 09:24:20Z liulanbo $
 */
if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}
if($mod == 'mobile' && defined('IN_MOBILE')) {
	if($_G['setting']['domain']['app']['mobile']) {
		dheader("Location:http://".$_G['setting']['domain']['app']['mobile']);
	} else {
		dheader("Location:".$_G['siteurl'].'forum.php?mobile=yes');
	}
} elseif(!$_G['setting']['mobile']['allowmobile']) {
	dheader("Location:".($_G['setting']['domain']['app']['default'] ? "http://".$_G['setting']['domain']['app']['default'] : $_G['siteurl']));
}
include DISCUZ_ROOT.'./source/language/mobile/lang_template.php';
$_G['lang'] = array_merge($_G['lang'], $lang);
$navtitle = $_G['lang']['misc_mobile_title'];
if($_GET['view'] == true) {
	include libfile('forum/forum_index_mobile', 'module');
	include libfile('function/forumlist');
	loadcache('userstats');

	$query = C::t('forum_forum')->fetch_all_forum(1);
	foreach($query as $forum) {
		if($forum['type'] != 'group') {
			$threads += $forum['threads'];
			$posts += $forum['posts'];
			$todayposts += $forum['todayposts'];

			if($forum['type'] == 'forum' && isset($catlist[$forum['fup']])) {
				if(forum($forum)) {
					$catlist[$forum['fup']]['forums'][] = $forum['fid'];
					$forum['orderid'] = $catlist[$forum['fup']]['forumscount']++;
					$forum['subforums'] = '';
					$forumlist[$forum['fid']] = $forum;
				}

			} elseif(isset($forumlist[$forum['fup']])) {
				$forumlist[$forum['fup']]['threads'] += $forum['threads'];
				$forumlist[$forum['fup']]['posts'] += $forum['posts'];
				$forumlist[$forum['fup']]['todayposts'] += $forum['todayposts'];
			}

		} else {
			$forum['forumscount'] 	= 0;
			$catlist[$forum['fid']] = $forum;
		}
	}
	ob_start();
	include template('mobile/forum/discuz');
} else {
	include template('mobile/common/preview');
}
function output_preview() {
	$content = ob_get_contents();
	ob_end_clean();
	ob_start();
	$content = preg_replace("/\<a href=\"(.*?)\"[\s]?\>(.*?)\<\/a\>/e", "replace_href('\\2', '\\1')", $content);
	echo $content;
	exit;
}
function replace_href($html_str, $other1, $other2) {
	$string = "<span class='lkcss'>".stripslashes($html_str)."</span>";
	return $string;
}
?>