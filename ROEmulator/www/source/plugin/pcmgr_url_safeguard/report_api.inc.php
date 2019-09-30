<?php
	$server = 'http://openapi.guanjia.qq.com/fcgi-bin/rpt?host=' . $_SERVER["HTTP_HOST"] . '&';
	$server .= http_build_query($_GET);
	$ret = file_get_contents($server);
?>