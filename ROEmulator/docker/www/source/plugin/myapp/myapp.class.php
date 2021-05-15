<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: myapp.class.php 29265 2012-03-31 06:03:26Z yexinhao $
 */

class plugin_myapp{
	function plugin_myapp() {
		global $_G;

		$this->title = $_G['cache']['plugin']['myapp']['showtitle'];
		$this->num = intval($_G['cache']['plugin']['myapp']['shownum']);
		$this->myapp = intval($_G['cache']['plugin']['myapp']['showmyapp']);
	}
}

class plugin_myapp_forum extends plugin_myapp {

	function viewthread_sidebottom_output() {
		global $_G, $postlist;

		if(IS_ROBOT) {
			return array();
		}

		if(!$_G['forum_firstpid']) {
			return array();
		}
		loadcache('myapp');
		$thisApp = '';
		$poster = reset($postlist);
		$userapp = C::t('home_userapp_plying')->fetch($poster['authorid']);
		if(!empty($userapp['appid'])) {
			$applist = explode(',', $userapp['appid']);
			$i = 0;
			foreach($applist as $appid) {
				if(!empty($_G['cache']['myapp'][$appid])) {
					if($i < $this->num) {
						$thisApp .= '<a href="userapp.php?mod=app&id='.$appid.'&fromtid='.$_G['tid'].'"'.(isset($_G['cache']['myapp'][$appid]) ? ' title="'.$_G['cache']['myapp'][$appid]['appname'].'"' : '').' target="_blank">'.
						'<img class="authicn vm" src="http://appicon.manyou.com/logos/'.$appid.'" style="width:40px;height:40px;margin-left:0px;"/></a>';
						$i++;
					} else {
						break;
					}
				}
			}
		}

		$thisApp = $thisApp ? '<p>'.$this->title.'</p><p class="avt" style="margin-left:10px;">'.$thisApp.'</p>' : '';
		return array($thisApp);
	}

	function viewthread_postsightmlafter_output() {
		global $_G, $postlist;
		if(IS_ROBOT || !$this->myapp) {
			return array();
		}
		$myappdiv = $myapp = $uids = array();
		foreach($postlist as $post) {
			$uids[$post['authorid']] = $post['authorid'];
		}
		require_once libfile('function/feed');
		foreach(C::t('common_member')->fetch_all($uids) as $uid) {
			$list = array();
			foreach(C::t('home_feed_app')->fetch_all_by_uid_icon($uid['uid'], '', 0, 1) as $feed) {
				$list[$feed['icon']][] = mkfeed($feed);
			}
			$myapp[$uid['uid']] = $this->getmyapplist($list);
		}
		foreach($postlist as $post) {
			$myappdiv[] = $myapp[$post['authorid']];
		}
		return $myappdiv;
	}

	function getmyapplist($list) {
		if(!$list) {
			return '';
		}
		$myapp = '<div class="xld xlda mtm">';
		foreach($list as $appicon => $values) {
			$myapp .= '<dl class="bbda cl"><dd class="m avt">';
			$myapp .= '<a href="userapp.php?mod=app&id='.$appicon.'"><img src="http://appicon.manyou.com/logos/'.$appicon.'" alt="" /></a>';
			$myapp .= '</dd><dd class="cl"><ul class="el">';
			foreach($values as $value) {
				$myapp .= '<li class="cl"><a class="t" href="userapp.php?icon='.$value[icon].'" title="'.lang('home/template', 'just_look_dynamic').'">';
				$myapp .= '<img width="16" height="16" '.($_G[cache][myapp][$value[icon]][icon] ? 'src="'.$_G[cache][myapp][$value[icon]][icon].'" onerror="this.onerror=null;this.src=\'http://appicon.manyou.com/icons/'.$value[icon].'\'"' : 'src="http://appicon.manyou.com/icons/'.$value[icon].'"').' />';
				$myapp .= '</a>'.$value[title_template].'<span class="xg1">'.dgmdate($value[dateline], 'n-j H:i').'</span><div class="ec">';
				if($value['image_1']) {
					$myapp .= '<a href="'.$value[image_1_link].'"'.$value[target].'><img src="'.$value[image_1].'" class="tn" /></a>';
				}
				if($value['image_2']) {
					$myapp .= '<a href="'.$value[image_2_link].'"'.$value[target].'><img src="'.$value[image_2].'" class="tn" /></a>';
				}
				if($value['image_3']) {
					$myapp .= '<a href="'.$value[image_3_link].'"'.$value[target].'><img src="'.$value[image_3].'" class="tn" /></a>';
				}
				if($value['image_4']) {
					$myapp .= '<a href="'.$value[image_4_link].'"'.$value[target].'><img src="'.$value[image_4].'" class="tn" /></a>';
				}
				if($value['body_template']) {
					$myapp .= '<div class="detail"'.($value['image_3'] ? 'style="clear: both; zoom: 1;"' : '').'>';
					$myapp .= $value[body_template].'</div>';
				}
				if($value['body_general']) {
					$myapp .= '<div class="quote"><blockquote>'.$value[body_general].'</blockquote></div>';
				}
				$myapp .= '</div></li>';
			}
			$myapp .= '</ul></dd></dl>';
		}
		return $myapp;
	}

}

class plugin_myapp_userapp extends plugin_myapp {
	function userapp_update() {
		global $_G;

		if(!empty($_GET['id']) && is_numeric($_GET['id'])) {
			$applist = array();
			$userapp = C::t('home_userapp_plying')->fetch($_G['uid']);
			if(!empty($userapp['appid'])) {
				$applist = explode(',', $userapp['appid']);
				if(!empty($applist)) {
					$applist = array_diff($applist, array(''));
					$key = array_search($_GET['id'], $applist);
					if($key !== false) {
						unset($applist[$key]);
					}
					array_unshift($applist, $_GET['id']);
					while(count($applist) > $this->num) {
						array_pop($applist);
					}
				}
			}
			if(empty($applist)) {
				$applist = array($_GET['id']);
			}
			if(!empty($applist)) {
				$appstr = implode(',', $applist);
				C::t('home_userapp_plying')->insert(array('uid' => $_G['uid'], 'appid' => daddslashes($appstr)), false, true);
			}
		}
	}
}