<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: google.php 34247 2013-11-22 02:41:52Z nemohou $
 */

@define('IN_API', true);
@define('CURSCRIPT', 'api');

require_once('../../source/class/class_core.php');
require_once('../../source/function/function_home.php');

$cachelist = array();
$discuz = C::app();

$discuz->cachelist = $cachelist;
$discuz->init_cron = false;
$discuz->init_setting = true;
$discuz->init_user = false;
$discuz->init_session = false;

$discuz->init();

$google = new GoogleAPI($discuz);
$google->run();

class GoogleAPI
{
	var $core;
	var $version = '2.0.0';
	function GoogleAPI(&$core) {
		$this->core = &$core;
	}

	function run() {
		$this->authcheck();
		$method = 'on_'.getgpc('a');
		if(method_exists($this, $method)) {
			$this->xmlheader();
			$this->$method();
			$this->xmlfooter();
		} else {
			$this->error('Unknow command');
		}
	}

	function authcheck() {
		$siteuniqueid = C::t('common_setting')->fetch('siteuniqueid');
		$auth = md5($siteuniqueid.'DISCUZ*COMSENZ*GOOGLE*API'.substr(time(), 0, 6));
		if($auth != getgpc('s')) {
			$this->error('Access error');
		}
	}

	function error($message) {
		$this->xmlheader();
		echo "<error>".$message."</error>";
		$this->xmlfooter();
	}

	function result($message = 'success') {
		$this->xmlheader();
		echo "<result>".$message."</result>";
		$this->xmlfooter();
	}

	function xmlheader() {
		static $isshowed;
		if(!$isshowed) {
			@header("Content-type: application/xml");
			echo "<?xml version=\"1.0\" encoding=\"".CHARSET."\"?>\n<document>\n";
			echo "<description>Discuz! API For Google Function</description>\n";
			echo "<version>{$this->version}</version>\n";
			$isshowed = true;
		}
		return true;
	}

	function xmlfooter($halt = true) {
		echo "\n</document>\n";
		$halt && exit();
	}

	function on_on() {
		C::t('common_setting')->update('google', 1);
		$this->result();
	}

	function on_off() {
		C::t('common_setting')->update('google', 0);
		$this->result();
	}

	function on_gtt() {
		global $_G;
		$tids = explode(',', getgpc('t'));
		$msg = getgpc('msg') ? true : false;
		$att = getgpc('att') ? true : false;
		$posts = getgpc('post') ? explode(',', getgpc('post')) : array();
		if($posts) {
			$posts[0] = intval($posts[0]);
			$posts[1] = intval($posts[1]);
			$posts = implode(',', $posts);
		}
		$xmlcontent .= "<threadsdata>\n";
		if(is_array($tids) && !empty($tids)) {
			$ftid = $threadlist = $postlist = $attachlist = $pattachlist = array();
			foreach ($tids as $tid) {
				if(is_numeric($tid)) {
					$ftid[] = $tid;
				}
			}
			if($ftid) {
				$threads = C::t('forum_thread')->fetch_all_by_tid($ftid);
				foreach($threads as $thread) {
					$thread['message'] = '';
					if($msg) {
						if($thread['posttableid']) {
							$tablenamelist['forum_post_'.intval($thread['posttableid'])][] = $thread['tid'];
						} else {
							$tablenamelist['forum_post'][] = $thread['tid'];
						}
					}
					$threadlist[$thread['tid']] = $thread;
				}
				if($msg) {
					foreach($tablenamelist AS $tablename => $tids) {
						$pquery = DB::query("SELECT tid, message, pid FROM ".DB::table($tablename)." WHERE tid IN (".dimplode($tids).") AND first=1", 'SILENT');
						while($pquery && $post = DB::fetch($pquery)) {
							$threadlist[$post['tid']]['message'] = dhtmlspecialchars($post['message']);
							if($att) {
								$_tid = (string)$post['tid'];
								$attachtablename = 'forum_attachment_'.intval($_tid{strlen($_tid)-1});
								$aquery = DB::query("SELECT dateline, filename, filesize, attachment, remote, description, readperm, price, isimage, width FROM ".DB::table($attachtablename)." WHERE pid='$post[pid]'");
								$attachs = '';
								while($aquery && $attach = DB::fetch($aquery)) {
									$attach['url'] = (($attach['remote'] ? $_G['setting']['ftp']['attachurl'] : $_G['setting']['attachurl']).'forum/').$attach['attachment'];
									unset($attach['attachment'], $attach['remote']);
									$attachs .= '<attach>';
									foreach($attach as $_k => $_v) {
										$attachs .= '<'.$_k.'>'.$_v.'</'.$_k.'>';
									}
									$attachs .= '</attach>';
									$attachlist[$_tid] = $attachs;
								}
							}
						}
						if($posts) {
							$pquery = DB::query("SELECT tid, pid, authorid, message FROM ".DB::table($tablename)." WHERE tid IN (".dimplode($tids).") AND first=0 LIMIT $posts", 'SILENT');
							while($pquery && $post = DB::fetch($pquery)) {
								if($att) {
									$_tid = (string)$post['tid'];
									$attachtablename = 'forum_attachment_'.intval($_tid{strlen($_tid)-1});
									$aquery = DB::query("SELECT dateline, filename, filesize, attachment, remote, description, readperm, price, isimage, width FROM ".DB::table($attachtablename)." WHERE pid='$post[pid]'", 'SILENT');
									$attachs = '';
									while($aquery && $attach = DB::fetch($aquery)) {
										$attach['url'] = (($attach['remote'] ? $_G['setting']['ftp']['attachurl'] : $_G['setting']['attachurl']).'forum/').$attach['attachment'];
										unset($attach['attachment'], $attach['remote']);
										$attachs .= '<attach>';
										foreach($attach as $_k => $_v) {
											$attachs .= '<'.$_k.'>'.$_v.'</'.$_k.'>';
										}
										$attachs .= '</attach>';
									}
								}
								$postlist[$post['tid']] .= "<post>\n".
									"	<pid>".$post['pid']."</pid>\n".
									"	<authorid>".$post['authorid']."</authorid>\n".
									"	<message>".dhtmlspecialchars($post['message'])."</message>\n".
									($attachs ? "		<attachments>$attachs</attachments>\n" : '').
									"</post>\n";
							}
						}
					}
					unset($tablenamelist);
				}

				foreach($threadlist AS $tid => $thread) {
					$xmlcontent .=
					"	<thread>\n".
					"		<tid>$thread[tid]</tid>\n".
					"		<fid>$thread[fid]</fid>\n".
					"		<authorid>$thread[authorid]</authorid>\n".
					"		<subject>$thread[subject]</subject>\n".
					"		<views>$thread[views]</views>\n".
					"		<replies>$thread[replies]</replies>\n".
					"		<special>$thread[replies]</special>\n".
					"		<posttableid>$thread[posttableid]</posttableid>\n".
					"		<dateline>$thread[dateline]</dateline>\n".
					"		<lastpost>$thread[lastpost]</lastpost>\n".
					($msg ? "		<message>$thread[message]</message>\n" : '').
					($attachlist[$tid] ? "		<attachments>$attachlist[$tid]</attachments>\n" : '').
					"	</thread>\n".
					($postlist[$tid] ? "		<posts>$postlist[$tid]</posts>\n" : '');
				}
			}

		}
		$xmlcontent .= "</threadsdata>";
		echo $xmlcontent;
	}

	function on_gts() {
		$xmlcontent = '';
		$threads = C::t('forum_thread')->count();

		$posts = 0;
		loadcache('posttableids');
		if($_G['cache']['posttableids']) {
			foreach($_G['cache']['posttableids'] AS $tableid) {
				$posts += DB::result_first("SELECT COUNT(*) FROM ".DB::table(getposttable($tableid))." LIMIT 1");
			}
		}
		$members = C::t('common_member')->count();
		$settings = C::t('common_setting')->fetch_all(array('bbname', 'historyposts'));
		$bbname = $settings['bbname'];
		$yesterdayposts = $settings['historyposts'];
		if(!empty($yesterdayposts)) {
			$yesterdayposts = explode("\t", $yesterdayposts);
			$yestoday = intval($yesterdayposts[0]);
			$mostpost = intval($yesterdayposts[1]);
		} else {
			$yestoday = $mostpost = 0;
		}

		$xmlcontent .= "<sitedata>\n".
		"	<bbname>".dhtmlspecialchars($bbname)."</bbname>\n".
		"	<threads>$threads</threads>\n".
		"	<posts>$posts</posts>\n".
		"	<members>$members</members>\n".
		"	<yesterdayposts>$yestoday</yesterdayposts>\n".
		"	<mostposts>$mostpost</mostposts>\n".
		"</sitedata>\n";
		echo $xmlcontent;

		echo "<forumdata>\n";
		$query = C::t('forum_forum')->fetch_all_forum_ignore_access();
		foreach($query as $forum) {
			echo "	<$forum[type]>\n".
			"		<fid>$forum[fid]</fid>\n".
			"		<fup>$forum[fup]</fup>\n".
			"		<name>".dhtmlspecialchars($forum['name'])."</name>\n".
			"		<description>".dhtmlspecialchars($forum['description'])."</description>\n".
			"		<threads>$forum[threads]</threads>\n".
			"		<posts>$forum[posts]</posts>\n".
			"		<todayposts>$forum[todayposts]</todayposts>\n".
			"	</$forum[type]>\n";
		}

		echo "</forumdata>";

	}

}

?>