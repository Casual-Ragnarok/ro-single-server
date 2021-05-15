<?php
function get_xindai_ad(){
	global $client,$city;
	for($i=1;$i<=3;$i++){
		$xindai_ad = arrayCoding($client->get_xindai_ad($city), 'UTF-8', CHARSET);
		if($xindai_ad['rs_code'] == '1000') break;
	}
	if($i == 4) showmessage('dzapp_haodai:callback_error_user');
	return $xindai_ad;
}
function get_article_dkgl_list($page = 1){
	global $client,$city;
	for($i=1;$i<=3;$i++){
		$dkgl_article = arrayCoding($client->get_article_dkgl_list($city, FALSE, $page, 10), 'UTF-8', CHARSET);
		if($dkgl_article['rs_code'] == '1000') break;
	}
	if($i == 4) showmessage('dzapp_haodai:callback_error_user');
	return $dkgl_article;
}
function get_article_dkzx_list($page = 1){
	global $client,$city;
	for($i=1;$i<=3;$i++){
		$dkzx_article = arrayCoding($client->get_article_dkzx_list($city, FALSE, $page, 10), 'UTF-8', CHARSET);
		if($dkzx_article['rs_code'] == '1000') break;
	}
	if($i == 4) showmessage('dzapp_haodai:callback_error_user');
	return $dkzx_article;
}
function get_article_jyfx_list($page = 1){
	global $client,$city;
	for($i=1;$i<=3;$i++){
		$jyfx_article = arrayCoding($client->get_article_jyfx_list($city, FALSE, $page, 10), 'UTF-8', CHARSET);
		if($jyfx_article['rs_code'] == '1000') break;
	}
	if($i == 4) showmessage('dzapp_haodai:callback_error_user');
	return $jyfx_article;
}
function get_article_cjwt_list($page = 1){
	global $client,$city;
	for($i=1;$i<=3;$i++){
		$cjwt_article = arrayCoding($client->get_article_cjwt_list($city, FALSE, $page, 10), 'UTF-8', CHARSET);
		if($cjwt_article['rs_code'] == '1000') break;
	}
	if($i == 4) showmessage('dzapp_haodai:callback_error_user');
	return $cjwt_article;
}
function get_hot_recommend(){
	global $client,$city;
	for($i=1;$i<=3;$i++){
		$hot_recommend = arrayCoding($client->get_hot_recommend(), 'UTF-8', CHARSET);
		if($hot_recommend['rs_code'] == '1000') break;
	}
	if($i == 4) showmessage('dzapp_haodai:callback_error_user');
	return $hot_recommend;
}
function get_xindai_filter($type = ''){
	global $client,$_GET;
	$type = !empty($type) ? $type : $_GET['xd_type'];
	for($i=1;$i<=3;$i++){
		$filter = arrayCoding($client->get_xindai_filter($type), 'UTF-8', CHARSET);
		if($filter['rs_code'] == '1000') break;
	}
	if($i == 4) showmessage('dzapp_haodai:callback_error_user');
	return $filter;
}
function get_xindai_list($money, $month, $data, $page){
	global $client,$_GET,$city;
	for($i=1;$i<=3;$i++){
		$result = arrayCoding($client->get_xindai_list($city, $_GET['xd_type'], $money, $month, $data, $page, 10), 'UTF-8', CHARSET);
		if($result['rs_code'] == '1000') break;
	}
	if($i == 4) showmessage('dzapp_haodai:callback_error_user');
	return $result;
}
function get_xindai_detail($xd_id, $money, $month){
	global $client,$city;
	for($i=1;$i<=3;$i++){
		$xd = arrayCoding($client->get_xindai_detail($city, $xd_id, $money, $month), 'UTF-8', CHARSET);
		if($xd['rs_code'] == '1000') break;
	}
	if($i == 4) showmessage('dzapp_haodai:callback_error_user');
	return $xd;
}
function get_article_detail($id){
	global $client,$_GET,$city;
	for($i=1;$i<=3;$i++){
		$result = arrayCoding($client->get_article_detail($id), 'UTF-8', CHARSET);
		if($result['rs_code'] == '1000') break;
	}
	if($i == 4) showmessage('dzapp_haodai:callback_error_user');
	return $result;
}
function arrayCoding($array, $inCharset, $outCharset) {
    if(is_array($array)){
        $arr1 = array();
        foreach($array as $key => $value){
            $key = iconv($inCharset, $outCharset, $key);
            $arr1[$key] = arrayCoding($value, $inCharset, $outCharset);
        }
    }else{
        $arr1 = iconv($inCharset, $outCharset, $array);
    }
    return $arr1;
}
function url_implode($gets) {
	$arr = array();
	foreach ($gets as $key => $value) {
		if($value) {
			$arr[] = $key.'='.urlencode($value);
		}
	}
	return implode('&', $arr);
}
function rewrite_index() {
	global $_G;
	if(!$_G['cache']['plugin']['dzapp_haodai']['rewrite']) return 'plugin.php?id=dzapp_haodai';
	return 'haodai.html';
}
function rewrite_list($type, $page) {
	global $_G;
	if(!$_G['cache']['plugin']['dzapp_haodai']['rewrite']) return 'plugin.php?id=dzapp_haodai&action=list&type='.$type.'&page='.$page;
	return 'haodai-list-'.$type.'-'.$page.'.html';
}
function rewrite_news($aid) {
	global $_G;
	if(!$_G['cache']['plugin']['dzapp_haodai']['rewrite']) return 'plugin.php?id=dzapp_haodai&action=news&aid='.$aid;
	return 'haodai-news-'.$aid.'.html';
}
function rewrite_calc($type) {
	global $_G;
	if(!$_G['cache']['plugin']['dzapp_haodai']['rewrite']) return 'plugin.php?id=dzapp_haodai&action=calc&type='.$type;
	return 'haodai-calculator-'.$type.'.html';
}
function rewrite_view_apply($type, $xd_id, $xd_type, $month, $money) {
	global $_G;
	if(!$_G['cache']['plugin']['dzapp_haodai']['rewrite']) return 'plugin.php?id=dzapp_haodai&action='.$type.'&xd_id='.$xd_id.'&xd_type='.$xd_type.'&month='.$month.'&money='.$money;
	return 'haodai-'.$type.'-'.$xd_id.'-'.$xd_type.'-'.$month.'-'.$money.'.html';
}
function get_xindai_recommend($money, $month){
	global $client,$_GET,$city;
	for($i=1;$i<=3;$i++){
		$result = arrayCoding($client->get_xindai_list($city, $_GET['xd_type'], $money, $month, array(), 1, 5), 'UTF-8', CHARSET);
		if($result['rs_code'] == '1000') break;
	}
	if($i == 4) showmessage('dzapp_haodai:callback_error_user');
	return $result;
}
?>