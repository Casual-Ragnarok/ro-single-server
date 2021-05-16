<?php

/**
 *      By:cr180 QQ:250997329
 *		 Date: 2013年12月9日 02:53:41
 *		这个脚本比较简单 只是读取了插件已缓存好的数据 帖子列表页 帖子页插入了推荐信息
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}
class plugin_dzapp_haodai {
	function common() {
		global $_G;
		include_once DISCUZ_ROOT.'./data/dzapp_haodai_config.php';
		$_G['cache']['plugin']['dzapp_haodai']['API_citynames_1231'] = HD_CITY;
		$_G['cache']['plugin']['dzapp_haodai']['forumlisthot'] = unserialize($_G['cache']['plugin']['dzapp_haodai']['forumlisthot']);

		return;
	}
}
class plugin_dzapp_haodai_forum extends plugin_dzapp_haodai {
	function forumdisplay_top_output() {
		global $_G;

		if(!in_array($_G['fid'],$_G['cache']['plugin']['dzapp_haodai']['forumlisthot'])) return;
		if(file_exists(DISCUZ_ROOT.'./data/sysdata/cache_dzapp_haodai_filter_goufang.php')){
			include_once DISCUZ_ROOT.'./data/sysdata/cache_dzapp_haodai_filter_goufang.php';
			$goufang = $filter['filter'];
		}
		if(file_exists(DISCUZ_ROOT.'./data/sysdata/cache_dzapp_haodai_filter_gouche.php')){
			include_once DISCUZ_ROOT.'./data/sysdata/cache_dzapp_haodai_filter_gouche.php';
			$gouche = $filter['filter'];
		}
		if(file_exists(DISCUZ_ROOT.'./data/sysdata/cache_dzapp_haodai_filter_xiaofei.php')){
			include_once DISCUZ_ROOT.'./data/sysdata/cache_dzapp_haodai_filter_xiaofei.php';
			$xiaofei = $filter['filter'];
		}
		if(file_exists(DISCUZ_ROOT.'./data/sysdata/cache_dzapp_haodai_dkgl_'.$_G['cache']['plugin']['dzapp_haodai']['API_citynames_1231'].'.php')){
			include_once DISCUZ_ROOT.'./data/sysdata/cache_dzapp_haodai_dkgl_'.$_G['cache']['plugin']['dzapp_haodai']['API_citynames_1231'].'.php';
			$dkgl_article = $dkgl_article['items'];
		}
		if(!file_exists(DISCUZ_ROOT.'./data/sysdata/cache_dzapp_haodai_filter_goufang.php') && !file_exists(DISCUZ_ROOT.'./data/sysdata/cache_dzapp_haodai_filter_gouche.php') && !file_exists(DISCUZ_ROOT.'./data/sysdata/cache_dzapp_haodai_filter_xiaofei.php')){
			return '<div style="display:none">'.
			'<iframe width="0" height="0" src="plugin.php?id=dzapp_haodai"></iframe>'.
			'<iframe width="0" height="0" src="plugin.php?id=dzapp_haodai&action=search&xd_type=goufang"></iframe>'.
			'<iframe width="0" height="0" src="plugin.php?id=dzapp_haodai&action=search&xd_type=gouche"></iframe>'.
			'<iframe width="0" height="0" src="plugin.php?id=dzapp_haodai&action=search&xd_type=xiaofei"></iframe>'.
			'</div>';
		}

		$haodaistyle = 1;
		include_once template('dzapp_haodai:hook_forum');
		return $haodai_html;
	}
	function viewthread_postsightmlafter_output() {
		global $_G,$postlist;
		$return = array();
		if(!in_array($_G['fid'],$_G['cache']['plugin']['dzapp_haodai']['forumlisthot'])) return;
		if(file_exists(DISCUZ_ROOT.'./data/sysdata/cache_dzapp_haodai_jyfx_'.$_G['cache']['plugin']['dzapp_haodai']['API_citynames_1231'].'.php')){
			include_once DISCUZ_ROOT.'./data/sysdata/cache_dzapp_haodai_jyfx_'.$_G['cache']['plugin']['dzapp_haodai']['API_citynames_1231'].'.php';
			$jyfx_article = $jyfx_article['items'];
		}
		if(file_exists(DISCUZ_ROOT.'./data/sysdata/cache_dzapp_haodai_dkgl_'.$_G['cache']['plugin']['dzapp_haodai']['API_citynames_1231'].'.php')){
			include_once DISCUZ_ROOT.'./data/sysdata/cache_dzapp_haodai_dkgl_'.$_G['cache']['plugin']['dzapp_haodai']['API_citynames_1231'].'.php';
			$dkgl_article = $dkgl_article['items'];
		}
		if(file_exists(DISCUZ_ROOT.'./data/sysdata/cache_dzapp_haodai_dkzx_'.$_G['cache']['plugin']['dzapp_haodai']['API_citynames_1231'].'.php')){
			include_once DISCUZ_ROOT.'./data/sysdata/cache_dzapp_haodai_dkzx_'.$_G['cache']['plugin']['dzapp_haodai']['API_citynames_1231'].'.php';
			$dkzx_article = $dkzx_article['items'];
		}
		$haodai_article = array_merge($jyfx_article,$dkgl_article,$dkzx_article);
		if(!$haodai_article) return;
		shuffle($haodai_article);
		$haodaistyle = 2;
		include_once template('dzapp_haodai:hook_forum');

		foreach($postlist as $key => $post){
			if($post['first']){
				$return[] = $haodai_html;
			}else{
				$return[] = '';
			}
		}
		return $return;
	}

}
?>