<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: class_cloud.php 28702 2012-03-08 06:43:58Z yexinhao $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class Cloud {

	static private $_loaded = array();

	public static function loadClass($className, $params = null) {

		if (strpos($className, 'Cloud_') !== 0) {
			$className = 'Cloud_' . $className;
		}

		self::loadFile($className);

		$instance = call_user_func_array(array($className, 'getInstance'), (array)$params);

		return $instance;
	}

	public static function loadFile($className) {

		$items = explode('_', $className);
		if ($items[0] == 'Cloud') {
			unset($items[0]);
		}

		$loadKey = implode('_', $items);
		if (isset(self::$_loaded[$loadKey])) {
			return true;
		}

		$file = DISCUZ_ROOT . '/api/manyou/' . implode('/', $items) . '.php';

		if (!is_file($file)) {
			throw new Cloud_Exception('Cloud file not exists!', 50001);
		}

		include $file;
		self::$_loaded[$loadKey] = true;

		return true;
	}

}

class Cloud_Exception extends Exception {

	public function __construct($message, $code = 0) {
		if ($message instanceof Exception) {
			parent::__construct($message->getMessage(), intval($message->getCode()));
		} else {
			parent::__construct($message, intval($code));
		}
	}

}
?>