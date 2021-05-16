<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: Cloud.php 29177 2012-03-28 05:56:17Z yexinhao $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class Cloud_Service_Server_Cloud extends Cloud_Service_Server_Restful {

	protected static $_instance;

	public static function getInstance() {

		if (!(self::$_instance instanceof self)) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	public function onCloudGetApps($appName = '') {

		$appService = Cloud::loadClass('Service_App');
		$utilService = Cloud::loadClass('Service_Util');
		$apps = $appService->getCloudApps(false);

		if ($appName) {
			$apps = array($appName => $apps[$appName]);
		}

		$apps['apiVersion'] = $utilService->getApiVersion();

		$apps['siteInfo'] = $this->_getBaseInfo();

		return $apps;
	}

	public function onCloudSetApps($apps) {

		if (!is_array($apps)) {
			return false;
		}

		$appService = Cloud::loadClass('Service_App');
		$utilService = Cloud::loadClass('Service_Util');

		$res = array();
		$res['apiVersion'] = $utilService->getApiVersion();

		foreach ($apps as $appName => $status) {
			$res[$appName] = $appService->setCloudAppStatus($appName, $status, false, false);
		}

		try {
			require_once libfile('function/cache');
			updatecache(array('plugin', 'setting', 'styles'));
			cleartemplatecache();
		} catch (Exception $e) {
		}

		$res['siteInfo'] = $this->_getBaseInfo();

		return $res;
	}

	public function onCloudOpenCloud() {

		$appService = Cloud::loadClass('Service_App');
		$utilService = Cloud::loadClass('Service_Util');

		$this->_openCloud();

		$res = array();
		$res['status'] = true;
		$res['siteInfo'] = $this->_getBaseInfo();

		return $res;
	}

	protected function _openCloud() {

		require_once libfile('function/cache');

		$result = C::t('common_setting')->update('cloud_status', 1);

		try {
			C::t('common_setting')->delete(array('connectsiteid', 'connectsitekey', 'my_siteid_old', 'my_sitekey_sign_old'));
		} catch (Exception $e) {
		}

		updatecache('setting');
		return true;
	}

	protected function _getBaseInfo() {
		global $_G;
		$info = array();

		loadcache(array('userstats', 'historyposts'));
		$indexData = memory('get', 'forum_index_page_1');
		if(is_array($indexData) && $indexData) {
			$indexData = array();
			$info['threads'] = $indexData['threads'] ? $indexData['threads'] : 0;
			$info['todayPosts'] = $indexData['todayposts'] ? $indexData['todayposts'] : 0;
			$info['allPosts'] = $indexData['posts'] ? $indexData['posts'] : 0;
		} else {
			$threads = $posts = $todayposts = 0;
			$query = C::t('forum_forum')->fetch_all_by_status(1, 0);
			foreach($query as $forum) {
				if($forum['type'] != 'group') {
					$threads += $forum['threads'];
					$posts += $forum['posts'];
					$todayposts += $forum['todayposts'];
				}
			}
			$info['threads'] = $threads ? $threads : 0;
			$info['allPosts'] = $posts ? $posts : 0;
			$info['todayPosts'] = $todayposts ? $todayposts : 0;
		}

		$info['members'] = $_G['cache']['userstats']['totalmembers'] ? intval($_G['cache']['userstats']['totalmembers']) : 0;
		$postdata = $_G['cache']['historyposts'] ? explode("\t", $_G['cache']['historyposts']) : array(0,0);
		$info['yesterdayPosts'] = intval($postdata[0]);

		return $info;
	}

	public function onCloudGetStats() {
		global $_G;

		$info = array();

		$tableprelen = strlen(C::t('common_setting')->get_tablepre());
		$table = array(
			'forum_thread' => 'threads',
			'forum_post' => 'allPosts',
			'common_member' => 'members'
		);
		foreach(C::t('common_setting')->fetch_all_table_status() as $row) {
			$tablename = substr($row['Name'], $tableprelen);
			if(!isset($table[$tablename])) {
				continue;
			}
			$info[$table[$tablename]] = $row['Rows'];
		}

		loadcache(array('historyposts'));
		$postdata = $_G['cache']['historyposts'] ? explode("\t", $_G['cache']['historyposts']) : array(0,0);
		$info['yesterdayPosts'] = intval($postdata[0]);

		$postnum = 0;
		$postrow = 0;
		$avg_posts = C::t('common_stat')->fetch_post_avg();

		$info['avgPosts'] = intval($avg_posts);
		$info['statsCode'] = $_G['setting']['statcode'];

		return $info;
	}

}