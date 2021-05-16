<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: memory_driver_apc.php 27635 2012-02-08 06:38:31Z zhangguosheng $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class memory_driver_apc
{

	public function init($config) {

	}

	public function get($key) {
		return apc_fetch($key);
	}

	public function getMulti($keys) {
		return apc_fetch($keys);
	}

	public function set($key, $value, $ttl = 0) {
		return apc_store($key, $value, $ttl);
	}

	public function rm($key) {
		return apc_delete($key);
	}

	public function clear() {
		return apc_clear_cache('user');
	}

	public function inc($key, $step = 1) {
		return apc_inc($key, $step) !== false ? apc_fetch($key) : false;
	}

	public function dec($key, $step = 1) {
		return apc_dec($key, $step) !== false ? apc_fetch($key) : false;
	}

}