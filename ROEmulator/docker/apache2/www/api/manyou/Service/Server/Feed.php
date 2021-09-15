<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: Feed.php 25512 2011-11-14 02:31:42Z yexinhao $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class Cloud_Service_Server_Feed extends Cloud_Service_Server_Restful {

	protected static $_instance;

	public static function getInstance() {

		if (!(self::$_instance instanceof self)) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	public function onFeedPublishTemplatizedAction($uId, $appId, $titleTemplate, $titleData, $bodyTemplate, $bodyData, $bodyGeneral = '', $image1 = '', $image1Link = '', $image2 = '', $image2Link = '', $image3 = '', $image3Link = '', $image4 = '', $image4Link = '', $targetIds = '', $privacy = '', $hashTemplate = '', $hashData = '', $specialAppid=0) {
		$res = $this->getUserSpace($uId);
		if (!$res) {
			return new Cloud_Service_Server_ErrorResponse('1', "User($uId) Not Exists");
		}

		$friend = ($privacy == 'public') ? 0 : ($privacy == 'friends' ? 1 : 2);

		$images = array($image1, $image2, $image3, $image4);
		$image_links = array($image1Link, $image2Link, $image3Link, $image4Link);

		require_once libfile('function/feed');
		$result = feed_add($appId, $titleTemplate, $titleData, $bodyTemplate, $bodyData, $bodyGeneral, $images, $image_links, $targetIds, $friend, $specialAppid, 1);

		return $result;
	}

}