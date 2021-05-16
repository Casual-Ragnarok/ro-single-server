<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: VideoAuth.php 28833 2012-03-14 08:42:59Z yexinhao $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class Cloud_Service_Server_VideoAuth extends Cloud_Service_Server_Restful {

	protected static $_instance;

	public static function getInstance() {

		if (!(self::$_instance instanceof self)) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	public function onVideoAuthSetAuthStatus($uId, $status) {
		if ($status == 'approved') {
			$status = 1;
			updatecreditbyaction('videophoto', $uId);
		} else if($status == 'refused') {
			$status = 0;
		} else {
			$errCode = '200';
			$errMessage = 'Error arguments';
			return new Cloud_Service_Server_ErrorResponse($errCode, $errMessage);
		}

		$result = C::t('common_member')->update($uId, array('videophotostatus' => $status));
		$memberVerify = C::t('common_member_verify')->fetch($uId);
		if(!$memberVerify) {
			C::t('common_member_verify')->insert(array('uid' => $uId, 'verify7' => $status));
		} else {
			C::t('common_member_verify')->update($uId, array('verify7' => $status));
		}
		return $result;
	}

	public function onVideoAuthAuth($uId, $picData, $picExt = 'jpg', $isReward = false) {
		global $_G;
		$res = $this->getUserSpace($uId);
		if (!$res) {
			return new Cloud_Service_Server_ErrorResponse('1', "User($uId) Not Exists");
		}
		$allowPicType = array('jpg','jpeg','gif','png');
		if(in_array($picExt, $allowPicType)) {
			$pic = base64_decode($picData);
			if (!$pic || strlen($pic) == strlen($picData)) {
				$errCode = '200';
				$errMessage = 'Error argument';
				return new Cloud_Service_Server_ErrorResponse($errCode, $errMessage);
			}

			$secret = md5($_G['timestamp']."\t".$_G['uid']);
			$picDir = DISCUZ_ROOT . './data/avatar/' . substr($secret, 0, 1);
			if (!is_dir($picDir)) {
				if (!mkdir($picDir, 0777)) {
					$errCode = '300';
					$errMessage = 'Cannot create directory';
					return new Cloud_Service_Server_ErrorResponse($errCode, $errMessage);
				}
			}

			$picDir .= '/' . substr($secret, 1, 1);
			if (!is_dir($picDir)) {
				if (!@mkdir($picDir, 0777)) {
					$errCode = '300';
					$errMessage = 'Cannot create directory';
					return new Cloud_Service_Server_ErrorResponse($errCode, $errMessage);
				}
			}

			$picPath = $picDir . '/' . $secret . '.' . $picExt;
			$fp = @fopen($picPath, 'wb');
			if ($fp) {
				if (fwrite($fp, $pic) !== FALSE) {
					fclose($fp);

					$upload = new discuz_upload();
					if(!$upload->get_image_info($picPath)) {
						@unlink($picPath);
					} else {
						C::t('common_member')->update($uId, array('videophotostatus' => 1));
						$memberVerify = C::t('common_member_verify')->fetch($uId);
						if(!$memberVerify) {
							C::t('common_member_verify')->insert(array('uid' => $uId, 'verify7' => 1));
						} else {
							C::t('common_member_verify')->update($uId, array('verify7' => 1));
						}
						$fields = array('videophoto' => $secret);
						$result = C::t('common_member_field_home')->update($uId, $fields);

						if ($isReward) {
							updatecreditbyaction('videophoto', $uId);
						}
						return $result;
					}
				}
				fclose($fp);
			}
		}
		$errCode = '300';
		$errMessage = 'Video Auth Error';
		return new Cloud_Service_Server_ErrorResponse($errCode, $errMessage);
	}

}