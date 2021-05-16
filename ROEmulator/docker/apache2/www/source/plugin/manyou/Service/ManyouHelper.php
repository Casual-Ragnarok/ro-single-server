<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: ManyouHelper.php 25512 2011-11-14 02:31:42Z yexinhao $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class Cloud_Service_ManyouHelper {

	protected static $_instance;

	public static function getInstance() {

		if (!(self::$_instance instanceof self)) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	public function __construct() {

	}

	public function getMethodCode($module, $method) {
		$methods = array(
				'Search.getUserGroupPermissions' => 10,
				'Search.getUpdatedPosts' => 11,
				'Search.removePostLogs' => 12,
				'Search.getPosts' => 13,
				'Search.getNewPosts' => 14,
				'Search.getAllPosts' => 15,
				'Search.removePosts' => 16,
				'Search.getUpdatedThreads' => 17,
				'Search.removeThreadLogs' => 18,
				'Search.getThreads' => '1a',
				'Search.getNewThreads' => '1b',
				'Search.getAllThreads' => '1c',
				'Search.getForums' => '1d',
				'Search.recycleThreads' => '1e',
				'Search.recycleThreads' => '1f',
				'Search.setConfig' => '20',
				'Search.getConfig' => '21',
				'Search.setHotWords' => '22',

				'Cloud.getApps' => '30',
				'Cloud.setApp' => '31',
				'Cloud.openCloud' => '32',
				'Cloud.getStatus' => '33',
				'Connect.setConfig' => '34',
				'Union.addAdvs' => '35',

				'Common.setConfig' => '40',
				'Common.getNav' => '41',
				'Site.getUpdatedUsers' => '42',
				'Site.getUpdatedFriends' => '43',
				'Site.getAllUsers' => '44',
				'Site.getStat' => '45',

				'Users.getInfo' => '50',
				'Users.getFriendInfo' => '51',
				'Users.getExtraInfo' => '52',
				'Friends.get' => '53',
				'Friends.areFriends' => '54',
				'Application.update' => '55',
				'Application.remove' => '56',
				'Application.setFlag' => '57',
				'UserApplication.add' => '58',
				'UserApplication.remove' => '5a',
				'UserApplication.update' => '5b',
				'UserApplication.getInstalled' => '5c',
				'UserApplication.get' => '5d',
				'Feed.publishTemplatizedAction' => '5e',
				'Notifications.send' => '5f',
				'Notifications.get' => '60',
				'Profile.setMYML' => '61',
				'Profile.setActionLink' => '62',
				'Request.send' => '63',
				'NewsFeed.get' => '64',
				'VideoAuth.setAuthStatus' => '65',
				'VideoAuth.auth' => '66',
				'Users.getFormHash' => '67',

				'Credit.get' => '70',
				'Credit.update' => '71',
				'MiniBlog.post' => '72',
				'MiniBlog.get' => '73',
				'Photo.createAlbum' => '74',
				'Photo.updateAlbum' => '75',
				'Photo.removeAlbum' => '76',
				'Photo.getAlbums' => '77',
				'Photo.upload' => '78',
				'Photo.get' => '7a',
				'Photo.update' => '7b',
				'Photo.remove' => '7c',
				'ImbotMsn.setBindStatus' => '7d',
				);

		return $methods[$module . '.' . $method];
	}


}