<?php
/**
 * DZAPP Haodai Main View
 *
 * @copyright (c) 2013 DZAPP. (http://www.dzapp.cn)
 * @author BranchZero <branchzero@gmail.com>
 */

include_once DISCUZ_ROOT.'./data/dzapp_haodai_config.php';
include_once DISCUZ_ROOT.'./source/plugin/dzapp_haodai/haodai.api.class.php';
include_once DISCUZ_ROOT.'./source/plugin/dzapp_haodai/dzapp_haodai.func.php';
@include_once DISCUZ_ROOT.'./data/sysdata/cache_dzapp_haodai_setting.php';
@include_once DISCUZ_ROOT.'./data/sysdata/cache_dzapp_haodai_city.php';
require_once libfile('function/cache');
!defined('IN_DISCUZ') && exit('Access Denied');

$var = $_G['cache']['plugin']['dzapp_haodai'];
$dzapp_haodai_seo = dunserialize($_G['setting']['dzapp_haodai_seo']);
$action = $_GET['action'];
if(!isset($hd_token)){
	if($_G['adminid'] == 1){
		$oauth = new HaoDaiOAuth(HD_AKEY, HD_SKEY);
		$auth_url = $oauth->getAuthorizeURL(HD_CALLBACK_URL);
		header('Location:'.$auth_url);
	}else{
		showmessage('dzapp_haodai:callback_error_user');
	}
}else{
	if(isset($hd_token['expires']) && TIMESTAMP > $hd_token['expires'] - 3600){
		$client = new HaoDaiClient(HD_AKEY, HD_SKEY);
		$client->set_debug(0);
		$result = $client->haodai_check_AccessToken();
		if($result['rs_code'] != '1000'){
			if($result['rs_code'] == '2100'){
				$hd_token = $client->oauth->getAccessToken('token', $hd_token);
				writetocache('dzapp_haodai_setting', getcachevars(array('hd_token' => $hd_token)));
			}else{
				showmessage('dzapp_haodai:callback_error_user');
			}
		}
	}elseif(isset($hd_token['expires']) && TIMESTAMP < $hd_token['expires'] - 3600){
	}else{
		showmessage('dzapp_haodai:callback_error_user');
	}
}

if($_G['cookie']['HD_CITY'] && $zones[$_G['cookie']['HD_CITY']]){
	$city = $_G['cookie']['HD_CITY'];
}else{
	require_once libfile('function/misc');
	$location = convertip($_G['clientip']);
	$charset = strtoupper(CHARSET);
	foreach($zones as $key => $value){
		if($charset == 'UTF-8'){
			$value = substr($value, 0, strlen($value) - 3);
		}elseif($charset == 'GBK'){
			$value = substr($value, 0, strlen($value) - 2);
		}elseif($charset == 'BIG5'){
			$city = '';
			break;
		}
		if(stripos($location,$value) !== FALSE){
			$city = $key;
			break;
		}
	}
	if($city){
		dsetcookie('HD_CITY', $city);
	}else{
		$city = defined('HD_CITY') && (HD_CITY !== '') ? HD_CITY : showmessage('dzapp_haodai:pleasechoosecity', 'admin.php?action=plugins&operation=config&identifier=dzapp_haodai&pmod=admincp_city');
		dsetcookie('HD_CITY', HD_CITY);
	}
}

$client = new HaoDaiClient(HD_AKEY, HD_SKEY, $hd_token['access_token']);
$client->set_debug(0);

if(defined('IN_MOBILE')){
	if(!@include_once DISCUZ_ROOT.'./data/sysdata/cache_dzapp_haodai_filter_mobile.php'){
		$filter_raw = $filterm = $filtermn = array();
		$rawtypes = array('gouche', 'goufang', 'qiye', 'xiaofei');
		foreach($rawtypes as $value){
			$filter_raw[$value] = get_xindai_filter($value);
			foreach($filter_raw[$value]['filter'] as $value1){
				foreach($value1['options'] as $value2){
					$filterm[$value][$value1['key']][$value2['val']] = array('name' => $value2['name'], 'val' => $value2['val']);
				}
				$filtermn[$value][$value1['key']] = $value1['name'];
			}
		}
		unset($filter_raw);
		writetocache('dzapp_haodai_filter_mobile', getcachevars(array('filterm' => $filterm, 'filtermn' => $filtermn)));
	}
}

if($action == ''){
	if(!@include_once DISCUZ_ROOT.'./data/sysdata/cache_dzapp_haodai_xindai_ad_'.$city.'.php' || TIMESTAMP - filemtime(DISCUZ_ROOT.'./data/sysdata/cache_dzapp_haodai_xindai_ad_'.$city.'.php') > $var['refreshtime']){
		$xindai_ad = get_xindai_ad();
		$xindai_ad_type = array();
		$xindai_ad_detail = array();
		foreach($xindai_ad['items'] as $key => $value){
			$xindai_ad_type[] = $value['type_name'];
			foreach($value['list'] as $value2){
				$value2['apply'] = cutstr(strip_tags($value2['apply']), 90);
				$xindai_ad_detail[$key][] = $value2;
			}
		}
		writetocache('dzapp_haodai_xindai_ad_'.$city, getcachevars(array('xindai_ad_type' => $xindai_ad_type,'xindai_ad_detail' => $xindai_ad_detail)));
	}

	if(!@include_once DISCUZ_ROOT.'./data/sysdata/cache_dzapp_haodai_dkgl_'.$city.'.php' || TIMESTAMP - filemtime(DISCUZ_ROOT.'./data/sysdata/cache_dzapp_haodai_dkgl_'.$city.'.php') > $var['refreshtime']){
		$dkgl_article = get_article_dkgl_list();
		writetocache('dzapp_haodai_dkgl_'.$city, getcachevars(array('dkgl_article' => $dkgl_article)));
	}
	if(!@include_once DISCUZ_ROOT.'./data/sysdata/cache_dzapp_haodai_dkzx_'.$city.'.php' || TIMESTAMP - filemtime(DISCUZ_ROOT.'./data/sysdata/cache_dzapp_haodai_dkzx_'.$city.'.php') > $var['refreshtime']){
		$dkzx_article = get_article_dkzx_list();
		writetocache('dzapp_haodai_dkzx_'.$city, getcachevars(array('dkzx_article' => $dkzx_article)));
	}
	if(!@include_once DISCUZ_ROOT.'./data/sysdata/cache_dzapp_haodai_cjwt_'.$city.'.php' || TIMESTAMP - filemtime(DISCUZ_ROOT.'./data/sysdata/cache_dzapp_haodai_cjwt_'.$city.'.php') > $var['refreshtime']){
		$cjwt_article = get_article_cjwt_list();
		writetocache('dzapp_haodai_cjwt_'.$city, getcachevars(array('cjwt_article' => $cjwt_article)));
	}
	$seodata = array('bbname' => $_G['setting']['bbname']);
	list($navtitle, $metadescription, $metakeywords) = get_seosetting('', $seodata, $dzapp_haodai_seo['main']);
	include template('dzapp_haodai:main_'.$var['mode']);
}elseif($action == 'search'){
	$_GET['xd_type'] = $_GET['xd_type'] ? $_GET['xd_type'] : 'xiaofei';
	if(!in_array($_GET['xd_type'],array('xiaofei','goufang','qiye','gouche'))) showmessage('dzapp_haodai:xd_type_fail');
	if(!@include_once DISCUZ_ROOT.'./data/sysdata/cache_dzapp_haodai_filter_'.$_GET['xd_type'].'.php'){
		$filter = get_xindai_filter();
		writetocache('dzapp_haodai_filter_'.$_GET['xd_type'], getcachevars(array('filter' => $filter)));
	}
	$money = $_GET['money'] ? intval($_GET['money']) : 10;
	$month = $_GET['month'] ? intval($_GET['month']) : 12;
	$param = is_array($_GET['param']) ? $_GET['param'] : array();
	$data = array();
	$page = max(1, intval($_GET['page']));
	unset($_GET['page']);
	if($_GET['remove']){
		unset($_GET[$_GET['remove']]);
		unset($_GET['remove']);
	}
	if($_GET['allremove']){
		$data = array();
		$newget = array('id'=>$_GET['id'], 'action'=>$_GET['action'], 'xd_type'=>$_GET['xd_type'], 'money'=>$_GET['money'], 'month'=>$_GET['month']);
		$_GET = $newget;
	}
	$chosen = array();
	foreach($filter['filter'] as $key => $value){
		$value['get'] = $_GET;
		if($value['get'][$value['key']]){
			$chosen[] = array('name'=>$value['name'], 'key'=>$value['key']);
			$data[$value['key']] = $value['get'][$value['key']];
			unset($value['get'][$value['key']]);
		}
		$filter['filter'][$key]['link'] = 'plugin.php?'.url_implode($value['get']);
	}
	if($_GET['grade']) $data['grade'] = $_GET['grade'];
	if($_GET['total_interest']) $data['total_interest'] = $_GET['total_interest'];
	if($_GET['month_repay']) $data['month_repay'] = $_GET['month_repay'];
	$result = get_xindai_list($money, $month, $data, $page);
	$num = $result['count'];
	$items = array();
	foreach($result['items'] as $value){
		$value['year_rate'] = round($value['year_rate'], 2);
		$value['more'] = count(explode(',',$value['profession'])) > 3 ? 1 : 0;
		$value['professions'] = reset(array_chunk(explode(',',$value['profession']),3));
		$items[] = $value;
	}
	$theurl = 'plugin.php?'.url_implode($_GET);
	$sort_get = $_GET;
	unset($sort_get['grade']);
	unset($sort_get['total_interest']);
	unset($sort_get['month_repay']);
	$theurl_without_sort = 'plugin.php?'.url_implode($sort_get);
	$start_limit = ($page - 1) * 10;
	$multipage = multi($num, 10, $page, $theurl);
	$maxpage = ceil($num / 10);
	$seodata = array('bbname' => $_G['setting']['bbname'], 'xd_type' => lang('plugin/dzapp_haodai','xd_type_'.$_GET['xd_type']));
	list($navtitle, $metadescription, $metakeywords) = get_seosetting('', $seodata, $dzapp_haodai_seo['search']);
	include template('dzapp_haodai:search');
}elseif($action == 'news'){
	if(!$_GET['aid']) showmessage('dzapp_haodai:param_wrong');
	$id = intval($_GET['aid']);
	$result = get_article_detail($id);
	if(!@include_once DISCUZ_ROOT.'./data/sysdata/cache_dzapp_haodai_dkzx_'.$city.'.php' || TIMESTAMP - filemtime(DISCUZ_ROOT.'./data/sysdata/cache_dzapp_haodai_dkzx_'.$city.'.php') > $var['refreshtime']){
		$dkzx_article = get_article_dkzx_list();
		writetocache('dzapp_haodai_dkzx_'.$city, getcachevars(array('dkzx_article' => $dkzx_article)));
	}
	$seodata = array('bbname' => $_G['setting']['bbname'], 'news_title' => $result['title']);
	list($navtitle, $metadescription, $metakeywords) = get_seosetting('', $seodata, $dzapp_haodai_seo['news']);
	include template('dzapp_haodai:news');
}elseif($action == 'view'){
	$_GET['xd_type'] = $_GET['xd_type'] ? $_GET['xd_type'] : 'xiaofei';
	if(!in_array($_GET['xd_type'],array('xiaofei','goufang','qiye','gouche'))) showmessage('dzapp_haodai:xd_type_fail');
	if(!$_GET['xd_id']) showmessage('dzapp_haodai:param_wrong');
	$xd_id = intval($_GET['xd_id']);
	$money = $_GET['money'] ? intval($_GET['money']) : 1;
	$month = $_GET['month'] ? intval($_GET['month']) : 12;
	$get_related = get_xindai_recommend($money, $month);
	$get_related = $get_related['items'];
	$xd = get_xindai_detail($xd_id, $money, $month);
	$xd['limit_min'] = round($xd['limit_min']);
	$xd['limit_max'] = round($xd['limit_max']);
	$xd['apply'] = htmlspecialchars_decode($xd['apply']);
	$xd['stuff'] = htmlspecialchars_decode($xd['stuff']);
	$xd['content'] = htmlspecialchars_decode($xd['content']);
	if(!@include_once DISCUZ_ROOT.'./data/sysdata/cache_dzapp_haodai_hot_recommend.php' || TIMESTAMP - filemtime(DISCUZ_ROOT.'./data/sysdata/cache_dzapp_haodai_hot_recommend.php') > $var['refreshtime']){
		$hot_recommend = get_hot_recommend();
		writetocache('dzapp_haodai_hot_recommend', getcachevars(array('hot_recommend' => $hot_recommend)));
	}
	$seodata = array('bbname' => $_G['setting']['bbname'], 'xd_type' => lang('plugin/dzapp_haodai','xd_type_'.$_GET['xd_type']), 'bank_name' => $xd['bank_name'], 'xd_name' => $xd['name']);
	list($navtitle, $metadescription, $metakeywords) = get_seosetting('', $seodata, $dzapp_haodai_seo['view']);
	include template('dzapp_haodai:view');
}elseif($action == 'apply'){
	$_GET['xd_type'] = $_GET['xd_type'] ? $_GET['xd_type'] : 'xiaofei';
	if(!in_array($_GET['xd_type'],array('xiaofei','goufang','qiye','gouche'))) showmessage('dzapp_haodai:xd_type_fail');
	if(!$_GET['xd_id']) showmessage('dzapp_haodai:param_wrong');
	$xd_id = intval($_GET['xd_id']);
	$money = $_GET['money'] ? intval($_GET['money']) : 1;
	$month = $_GET['month'] ? intval($_GET['month']) : 12;
	$xd = get_xindai_detail($xd_id, $money, $month);
	if(!submitcheck('applysubmit')) {
		$seodata = array('bbname' => $_G['setting']['bbname']);
		list($navtitle, $metadescription, $metakeywords) = get_seosetting('', $seodata, $dzapp_haodai_seo['apply']);
		include template('dzapp_haodai:apply');
	}else{
		if($_GET['xd_type'] == 'xiaofei'){
			if(!$_GET['company_type'] || !$_GET['year_born'] || !$_GET['salary_type'] || !$_GET['salary'] || !$_GET['has_creditcard'] || !$_GET['has_debt'] || !$_GET['has_succ_apply'] || !$_GET['has_debt_loan'] || !$_GET['nickname'] || !$_GET['mobile']) showmessage('dzapp_haodai:apply_incomplete');
			if($_GET['has_creditcard'] == lang('plugin/dzapp_haodai','have') && (!$_GET['creditcard_num'] || !$_GET['creditcard_money'])) showmessage('dzapp_haodai:apply_incomplete');
			if($_GET['has_debt'] == lang('plugin/dzapp_haodai','have') && !$_GET['debt_money']) showmessage('dzapp_haodai:apply_incomplete');
			if($_GET['has_debt_loan'] == lang('plugin/dzapp_haodai','have') && !$_GET['debt_loan_money']) showmessage('dzapp_haodai:apply_incomplete');
			$remark = lang('plugin/dzapp_haodai','remark_company_type').$_GET['company_type'].lang('plugin/dzapp_haodai','remark_birth').$_GET['year_born'].lang('plugin/dzapp_haodai','remark_salary_type').$_GET['salary_type'].lang('plugin/dzapp_haodai','remark_month_income').$_GET['salary'].lang('plugin/dzapp_haodai','remark_worktime').$_GET['job_year'].lang('plugin/dzapp_haodai','remark_year').$_GET['job_month'].lang('plugin/dzapp_haodai','remark_month').($_GET['has_creditcard'] == lang('plugin/dzapp_haodai','have') ? lang('plugin/dzapp_haodai','remark_has_card').$_GET['creditcard_num'].lang('plugin/dzapp_haodai','remark_card_num').$_GET['creditcard_money'].lang('plugin/dzapp_haodai','remark_yuan') : '').($_GET['has_debt'] == lang('plugin/dzapp_haodai','have') ? lang('plugin/dzapp_haodai','remark_has_debt').$_GET['debt_money'].lang('plugin/dzapp_haodai','remark_yuan') : '').lang('plugin/dzapp_haodai','remark_succeed_applied').$_GET['has_succ_apply'].($_GET['has_debt_loan'] == lang('plugin/dzapp_haodai','have') ? lang('plugin/dzapp_haodai','remark_has_debt_num').$_GET['debt_loan_money'].lang('plugin/dzapp_haodai','remark_yuan') : '');
		}elseif($_GET['xd_type'] == 'goufang'){
			if(!$_GET['house_type'] || !$_GET['monthly'] || !$_GET['hukou'] || !$_GET['has_secondhandhouse'] || !$_GET['nickname'] || !$_GET['mobile']) showmessage('dzapp_haodai:apply_incomplete');
			$remark = lang('plugin/dzapp_haodai','remark_house_type').$_GET['house_type'].lang('plugin/dzapp_haodai','remark_month_income').$_GET['monthly'].lang('plugin/dzapp_haodai','remark_second_hand').$_GET['has_secondhandhouse'].lang('plugin/dzapp_haodai','remark_live_in').$_GET['hukou'];
		}elseif($_GET['xd_type'] == 'gouche'){
			if(!$_GET['has_house'] || !$_GET['car_step'] || !$_GET['monthly'] || !$_GET['nickname'] || !$_GET['mobile']) showmessage('dzapp_haodai:apply_incomplete');
			$remark = lang('plugin/dzapp_haodai','remark_has_house').$_GET['has_house'].lang('plugin/dzapp_haodai','remark_car_no').$_GET['car_step'].lang('plugin/dzapp_haodai','remark_card_bank_num').$_GET['monthly'];
		}elseif($_GET['xd_type'] == 'qiye'){
			if(!$_GET['company_type'] || !$_GET['monthly'] || !$_GET['has_house'] || !$_GET['business_time'] || !$_GET['nickname'] || !$_GET['mobile']) showmessage('dzapp_haodai:apply_incomplete');
			$remark = lang('plugin/dzapp_haodai','remark_company_type').$_GET['company_type'].lang('plugin/dzapp_haodai','remark_card_bank_num').$_GET['monthly'].lang('plugin/dzapp_haodai','remark_qiye_has_house').$_GET['has_house'].lang('plugin/dzapp_haodai','bt').$_GET['business_time'];
		}
		$remark = diconv($remark, CHARSET, 'UTF-8');
		$_GET['nickname'] = diconv($_GET['nickname'], CHARSET, 'UTF-8');
		if($_GET['details']){
			if(strlen($_GET['details']) > 280) showmessage('dzapp_haodai:apply_reach_limit');
			$_GET['details'] = diconv($_GET['details'], CHARSET, 'UTF-8');
		}
		$data = array();
		if($_GET['email']) $data['email'] = $_GET['email'];
		if($_GET['details']) $data['details'] = $_GET['details'];
		$data['xd_id'] = $xd_id;
		$data['xd_type'] = $_GET['xd_type'];
		$data['bank_id'] = $xd['bank_id'];
		$data['month'] = $month;
		$data['remark'] = $remark;
		$result = arrayCoding($client->send_xindai_apply($city, $_GET['nickname'], $money, $_GET['mobile'], $data), 'UTF-8', CHARSET);
		if($result['rs_code'] != '1000') showmessage(lang('plugin/dzapp_haodai','apply_fail').$result['rs_msg']);
		if($result['id'] && $_GET['details']){
			$result2 = arrayCoding($client->send_xindai_apply_details($result['id'], $_GET['details'], $_GET['xd_type']), 'UTF-8', CHARSET);
			if($result2['rs_code'] != '1000') showmessage(lang('plugin/dzapp_haodai','apply_fail').$result2['rs_msg']);
		}
		showmessage('dzapp_haodai:apply_succeed','plugin.php?id=dzapp_haodai');
	}
}elseif($action == 'list'){
	$page = max(1, intval($_GET['page']));
	if($_GET['type'] == 'dkgl'){
		$result = get_article_dkgl_list($page);
		$maxpage = ceil($result['count'] / 10);
	}elseif($_GET['type'] == 'dkzx'){
		$result = get_article_dkzx_list($page);
		$maxpage = ceil($result['count'] / 10);
	}elseif($_GET['type'] == 'cjwt'){
		$result = get_article_cjwt_list($page);
		$maxpage = ceil($result['count'] / 10);
	}else{
		showmessage('dzapp_haodai:param_wrong');
	}
	if(defined('IN_MOBILE')){
		$pageinfo = '<div class="Pbottom"><div class="Pbottom_nei auto w85 no">';
		if($page != 1){
			$pageinfo .= '<a href="'.rewrite_list($_GET['type'], $page - 1).'" class="NextPage fl"><span class="BackPage_box Page_box auto"><span class="BackPage_box_icon Page_box_icon iconbox touming"></span><span class="Page_box_word fr">'.lang('plugin/dzapp_haodai','page_2').'</span><div class="clear"></div></span></a>';
		}else{
			$pageinfo .= '<span class="no fl"><span class="BackPage_box Page_box auto"><span class="BackPage_box_icon Page_box_icon iconbox touming"></span><span class="Page_box_word fr">'.lang('plugin/dzapp_haodai','page_2').'</span><div class="clear"></div></span></span>';
		}
		if($page < 3){
			$startpage = 1;
		}elseif($maxpage - $page < 2){
			$startpage = $maxpage - 4;
		}else{
			$startpage = $page - 2;
		}
		$pageinfo .= '<span class="page_number fl"><select class="number_con" name="jumpMenu" id="jumpMenu"  onchange="location.href=this.value;">';
		for($i=$startpage;$i <= $startpage + 4; $i++){
			$pageinfo .= '<option'.(($i == $page) ? ' selected' : '').' value="'.rewrite_list($_GET['type'], $i).'">'.$i.'</option>';
		}
		$pageinfo .= '</select></span>';
		if($page != $maxpage){
			$pageinfo .= '<a href="'.rewrite_list($_GET['type'], $page + 1).'" class="NextPage fl"><span class="NextPage_box Page_box auto"><span class="Page_box_word fl">'.lang('plugin/dzapp_haodai','page_3').'</span><span class="NextPage_box_icon Page_box_icon iconbox"></span><div class="clear"></div></span></a>';
		}else{
			$pageinfo .= '<span class="no fl"><span class="NextPage_box Page_box auto"><span class="Page_box_word fl">'.lang('plugin/dzapp_haodai','page_3').'</span><span class="NextPage_box_icon Page_box_icon iconbox"></span><div class="clear"></div></span></span>';
		}
		$pageinfo .= '<div class="clear"></div></div></div>';
	}else{
		if($page != 1){
			$pageinfo = '<a href="'.rewrite_list($_GET['type'], 1).'" class="nextpage">'.lang('plugin/dzapp_haodai','page_1').'</a><a href="'.rewrite_list($_GET['type'], $page - 1).'" class="nextpage">'.lang('plugin/dzapp_haodai','page_2').'</a>';
		}else{
			$pageinfo = '';
		}
		if($page < 3){
			$startpage = 1;
		}elseif($maxpage - $page < 2){
			$startpage = $maxpage - 4;
		}else{
			$startpage = $page - 2;
		}
		for($i=$startpage;$i <= $startpage + 4; $i++){
			if($i == $page){
				$pageinfo .= '<span class="pageon">'.$page.'</span>';
			}else{
				$pageinfo .= '<a href="'.rewrite_list($_GET['type'], $i).'" class="pageoff">'.$i.'</a>';
			}
		}
		if($page != $maxpage){
			$pageinfo .= '<a href="'.rewrite_list($_GET['type'], $page + 1).'" class="nextpage">'.lang('plugin/dzapp_haodai','page_3').'</a><a href="'.rewrite_list($_GET['type'], $maxpage).'" class="nextpage">'.lang('plugin/dzapp_haodai','page_4').'</a>';
		}
	}
	if(empty($result['items'])) showmessage('dzapp_haodai:param_wrong');
	$articles = $result['items'];
	include template('dzapp_haodai:list');
}elseif($action == 'calc'){
	if(!@include_once DISCUZ_ROOT.'./data/sysdata/cache_dzapp_haodai_dkgl_'.$city.'.php' || TIMESTAMP - filemtime(DISCUZ_ROOT.'./data/sysdata/cache_dzapp_haodai_dkgl_'.$city.'.php') > $var['refreshtime']){
		$dkgl_article = get_article_dkgl_list();
		writetocache('dzapp_haodai_dkgl_'.$city, getcachevars(array('dkgl_article' => $dkgl_article)));
	}
	$type = $_GET['type'];
	$seodata = array('bbname' => $_G['setting']['bbname'], 'calc_type' => lang('plugin/dzapp_haodai','calc_'.$type));
	list($navtitle, $metadescription, $metakeywords) = get_seosetting('', $seodata, $dzapp_haodai_seo['calc']);
	if(defined('IN_MOBILE') && $type == 'fd'){
		$script_more = '
					var $btnss = $(\'.btn-b\');
                    var $tables = $btnss.siblings(\'.int_table\');
                    $btnss.click(
                            function() {
                            if ($tables.hasClass(\'hide-table\')) {
                            $tables.removeClass(\'hide-table\');
                                    $(this).html(\''.lang('plugin/dzapp_haodai','hide-table').'\');
                            } else
                            {
                            $tables.addClass(\'hide-table\');
                                    $(this).html(\''.lang('plugin/dzapp_haodai','check-more').'\');
                            }
                            });';
	}
	include template('dzapp_haodai:calc_'.$type);
}elseif($action == 'fastloan'){
	if(submitcheck('applysubmit')){
		if(!$_GET['mobile'] || !$_GET['nickname'] || !$_GET['money']) showmessage('dzapp_haodai:apply_incomplete');
		$_GET['nickname'] = diconv($_GET['nickname'], CHARSET, 'UTF-8');
		$_GET['money'] = intval($_GET['money']);
		if(!is_numeric($_GET['mobile'])) showmessage('dzapp_haodai:mobile_be_int');
		$result = arrayCoding($client->send_xindai_apply($city, $_GET['nickname'], $_GET['money'], $_GET['mobile']), 'UTF-8', CHARSET);
		if($result['rs_code'] != '1000') showmessage(lang('plugin/dzapp_haodai','apply_fail').$result['rs_msg']);
		showmessage('dzapp_haodai:apply_succeed','plugin.php?id=dzapp_haodai');
	}else{
		$navtitle = lang('plugin/dzapp_haodai','fastloan');
		include template('dzapp_haodai:fastloan');
	}

}

?>