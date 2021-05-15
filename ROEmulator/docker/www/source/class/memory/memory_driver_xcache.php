<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: memory_driver_xcache.php 27449 2012-02-01 05:32:35Z zhangguosheng $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class memory_driver_xcache
{

	public function init($config) {

	}

	public function get($key) {
		return xcache_get($key);
	}

	public function set($key, $value, $ttl = 0) {
		return xcache_set($key, $value, $ttl);
	}

	public function rm($key) {
		return xcache_unset($key);
	}

	public function clear() {
		return xcache_clear_cache(XC_TYPE_VAR, 0);
	}

	public function inc($key, $step = 1) {
		return xcache_inc($key, $step);
	}

	public function dec($key, $step = 1) {
		return xcache_dec($key, $step);
	}

}