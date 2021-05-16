<?php
/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: upgrade.php 33387 2013-06-05 03:21:26Z jeffjzhang $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

if ($_GET['fromversion'] < 1.04) {
	$my_search_data = array(
		'status' => 0,
		'allow_hot_topic' => 1,
		'allow_thread_related' => 1,
		'allow_recommend_related' => 1,
		'allow_thread_related_bottom' => 0,
		'allow_forum_recommend' => 1,
		'allow_forum_related' => 0,
		'allow_collection_related' => 1,
		'allow_search_suggest' => 0,
		'cp_version' => 1,
		'recwords_lifetime' => 21600,
	);

	if (is_array($_G['setting']['my_search_data'])) {
		$my_search_data = array_merge($my_search_data, $_G['setting']['my_search_data']);
	}

	$insertData = serialize($my_search_data);

	$sql = <<<EOF
REPLACE INTO pre_common_setting VALUES ('my_search_data', '{$insertData}');
EOF;
	runquery($sql);
}

$finish = TRUE;