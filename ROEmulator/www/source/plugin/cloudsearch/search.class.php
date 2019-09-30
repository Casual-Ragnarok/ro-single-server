<?php

/**
 *      [Discuz! X] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: search.class.php 33387 2013-06-05 03:21:26Z jeffjzhang $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}


class plugin_cloudsearch {

	protected $allow = FALSE;

	protected $allow_hot_topic = FALSE;
	protected $allow_thread_related = FALSE;
	protected $allow_forum_recommend = FALSE;
	protected $allow_forum_related = FALSE;
	protected $allow_collection_related = FALSE;
	protected $allow_search_suggest = FALSE;

	public function plugin_cloudsearch() {
		global $_G, $searchparams;

		$cloudAppService = Cloud::loadClass('Service_App');
		$this->allow = $cloudAppService->getCloudAppStatus('search');
		if($this->allow) {
			$this->allow_hot_topic = $_G['setting']['my_search_data']['allow_hot_topic'];
			$this->allow_thread_related = isset($_G['setting']['my_search_data']['allow_thread_related']) ? $_G['setting']['my_search_data']['allow_thread_related'] : 1;
			$this->allow_recommend_related = isset($_G['setting']['my_search_data']['allow_recommend_related']) ? $_G['setting']['my_search_data']['allow_recommend_related'] : 1;
			$this->allow_forum_recommend = $_G['setting']['my_search_data']['allow_forum_recommend'];
			$this->allow_forum_related = $_G['setting']['my_search_data']['allow_forum_related'];
			$this->allow_collection_related = $_G['setting']['my_search_data']['allow_collection_related'];
			$this->allow_search_suggest = $_G['setting']['my_search_data']['allow_search_suggest'];
			$this->allow_thread_related_bottom = $_G['setting']['my_search_data']['allow_thread_related_bottom'];
			include_once template('cloudsearch:module');

			if (!$searchparams) {
				$searchHelper = Cloud::loadClass('Cloud_Service_SearchHelper');
				$searchparams = $searchHelper->makeSearchSignUrl();
			}
		}
	}

	public function common() {
		if(!$this->allow) {
			return;
		}

		if ($_GET['mod'] == 'redirect' && $_GET['goto'] == 'findpost' && $_GET['ptid'] && $_GET['pid']) {
			$post = get_post_by_pid($_GET['pid']);
			if (empty($post)) {
				$searchHelper = Cloud::loadClass('Cloud_Service_SearchHelper');
				$searchHelper->myPostLog('redelete', array('pid' => $_GET['pid']));
			}
		}
	}

	public function global_footer() {
		if(!$this->allow) {
			return;
		}

		$res = '';
		if(($this->allow_thread_related && $GLOBALS['page'] == 1 || $this->allow_thread_related_bottom) && CURSCRIPT == 'forum' && CURMODULE == 'viewthread') {
			$res = tpl_cloudsearch_global_footer_related();
		}

		if($this->allow_recommend_related && CURSCRIPT == 'forum' && (CURMODULE == 'viewthread' || CURMODULE == 'forumdisplay')) {
			if($this->_is_from_search_engine()) {
				$res .= tpl_cloudsearch_global_footer_mini();
			}
		}

		if ($this->allow_search_suggest) {
			$params = array(
			                'callback' => 'cloudsearch_suggest_callback'
			               );
			$util = Cloud::loadClass('Cloud_Service_Util');
			$queryString = $util->generateSiteSignUrl($params, true);
			$res .= tpl_cloudsearch_global_footer_suggest($queryString);
		}
		$res .= tpl_cloudsearch_global_footer_formula_output();

		return $res;
	}

	private function _is_from_search_engine() {

		$regex = "((http|https)\:\/\/)?";
		$regex .= "([a-z]*.)+?(ask.com|yahoo.com|bing.com|baidu.com|soso.com|google.com|google.cn|gougou.com|sogou.com|yahoo.cn|youdao.com|google.com.hk|360.cn|360sou.com|so.com)(.[a-z]{2,3})?\/";
		if(preg_match("/^$regex/i", $_SERVER['HTTP_REFERER'])) {
			return true;
		}

		return false;
	}

	public function topicadmin_message($params) {
		if(!$this->allow || !$params) {
			return;
		}

		$param = $params['param'];
		if($param[0] == 'admin_succeed') {
			$searchHelper = Cloud::loadClass('Cloud_Service_SearchHelper');
			$action = $_GET['action'];

			switch($action) {
				case 'moderate':
					global $operations;
					$moderate = empty($_GET['moderate']) ? array() : $_GET['moderate'];

					foreach($moderate as $tid) {
						if(!$tid = max(0, intval($tid))) {
							continue;
						}

						foreach($operations as $operation) {
							if(in_array($operation, array('stick', 'highlight', 'digest', 'bump', 'down', 'delete', 'move', 'close', 'open'))) {

								if($operation == 'stick') {
									$my_opt = $_GET['sticklevel'] ? 'sticky' : 'update';
								} elseif($operation == 'digest') {
									$my_opt = $_GET['digestlevel'] ? 'digest' : 'update';
								} elseif($operation == 'highlight') {
									$my_opt = $_GET['highlight_color'] ? 'highlight' : 'update';
								} else {
									$my_opt = $operation;
								}

								$data = array('tid' => $tid);
								if($my_opt == 'move' && $_GET['moveto']) {
									global $toforum;
									$data['otherid'] = $toforum['fid'];
								}

								$searchHelper->myThreadLog($my_opt, $data);
							}
						}
					}
					break;

				case 'banpost':
					global $posts;

					$banned = intval($_GET['banned']);
					foreach($posts as $post) {
						if ($banned) {
							$searchHelper->myPostLog('ban', array('pid' => $post['pid'], 'uid' => $post['authorid']));
						} else {
							$searchHelper->myPostLog('unban', array('pid' => $post['pid'], 'uid' => $post['authorid']));
						}
					}
					break;

				case 'merge':
					global $_G, $thread;
					$othertid = intval($_GET['othertid']);

					if ($thread) {
						$searchHelper->myThreadLog('merge', array('tid' => $othertid, 'otherid' => $_G['tid'], 'fid' => $thread['fid']));
					}
					break;

				case 'split':
					global $_G, $pids;

					$searchHelper->myThreadLog('split', array('tid' => $_G['tid']));

					foreach((array)explode(',', $pids) as $pid) {
						$searchHelper->myPostLog('split', array('pid' => $pid));
					}
					break;

				case 'warn':
					global $warned, $posts;

					foreach((array)$posts as $k => $post) {
						if($warned && !($post['status'] & 2)) {
							$searchHelper->myPostLog('warn', array('pid' => $post['pid'], 'uid' => $post['authorid']));
						} elseif(!$warned && ($post['status'] & 2)) {
							$searchHelper->myPostLog('unwarn', array('pid' => $post['pid'], 'uid' => $post['authorid']));
						}
					}
					break;

				default:
					break;
			}
		}
	}

	public function modcp_message($params) {
		if(!$this->allow || !$params) {
			return;
		}

		$param = $params['param'];
		if(in_array($param[0], array('modcp_member_ban_succeed', 'modcp_mod_succeed', 'modcp_recyclebin_restore_succeed', 'modcp_recyclebin_delete_succeed'))) {
			$searchHelper = Cloud::loadClass('Cloud_Service_SearchHelper');
			$action = $_GET['action'];

			switch($action) {
				case 'member':
					global $groupidnew, $member;

					$my_opt = in_array($groupidnew, array(4, 5)) ? 'banuser' : 'unbanuser';
					$searchHelper->myThreadLog($my_opt, array('uid' => $member['uid']));
					break;

				case 'moderate':
					global $op, $postlist;

					if($op == 'replies') {
						global $postlist;

						foreach((array)$postlist as $post) {
							$searchHelper->myPostLog('validate', array('pid' => $post['pid']));
						}
					} else {
						global $moderation;

						foreach((array)$moderation['validate'] as $tid) {
							$searchHelper->myThreadLog('validate', array('tid' => $tid));
						}
					}
					break;

				case 'recyclebin':
					if(!empty($_GET['moderate'])) {
						global $_G;

						foreach(C::t('forum_thread')->fetch_all_by_tid_displayorder($_GET['moderate'], -1, '=', $_G['fid']) as $tid) {
							if($op == 'restore') {
								$searchHelper->myThreadLog('restore', array('tid' => $tid['tid']));
							} elseif($op == 'delete' && $_G['group']['allowclearrecycle']) {
								$searchHelper->myPostLog('delete', array('tid' => $tid['tid']));
							}
						}
					}
					break;

				default:
					break;
			}
		}
	}

	public function viewthread_message($params) {
		if(!$this->allow || !$params) {
			return;
		}

		$param = $params['param'];
		if ($param[0] == 'thread_nonexistence' && $_GET['tid']) {
			$searchHelper = Cloud::loadClass('Cloud_Service_SearchHelper');
			$searchHelper->myThreadLog('redelete', array('tid' => $_GET['tid']));
		}
	}

	public function space_message($params) {
		if(!$this->allow || !$params) {
			return;
		}

		$param = $params['param'];
		if ($param[0] == 'thread_delete_succeed') {
			global $moderate;

			$searchHelper = Cloud::loadClass('Cloud_Service_SearchHelper');
			foreach((array)$moderate as $tid) {
				$searchHelper->myThreadLog('delete', array('tid' => $tid));
			}
		}
	}

	public function post_message($params) {
		if(!$this->allow || !$params) {
			return;
		}

		global $_G, $isfirstpost, $pid, $modnewthreads, $pinvisible;
		$param = $params['param'];
		$searchHelper = Cloud::loadClass('Cloud_Service_SearchHelper');
		if($param[0] == 'post_edit_delete_succeed' && !empty($_GET['delete']) && $isfirstpost) {
			$searchHelper->myThreadLog('delete', array('tid' => $_G['tid']));
		} elseif($param[0] == 'post_edit_delete_succeed' && !empty($_GET['delete'])) {
			$searchHelper->myPostLog('delete', array('pid' => $pid));
		} elseif($param[0] == 'post_edit_succeed' && !$modnewreplies && $pinvisible != -3) {
			$searchHelper->myPostLog('update', array('pid' => $pid));
		}
	}

	public function deletemember($params) {
		$uids = $params['param'][0];
		$step = $params['step'];

		if($step == 'delete' && is_array($uids)) {
			$searchHelper = Cloud::loadClass('Cloud_Service_SearchHelper');
			foreach($uids as $uid) {
				$searchHelper->myThreadLog('deluser', array('uid' => $uid));
			}
		}
	}

	public function deletepost($params) {
		$pids = $params['param'][0];
		$idtype = $params['param'][1];
		$step = $params['step'];

		if($step == 'delete' && $idtype == 'pid' && is_array($pids)) {
			$searchHelper = Cloud::loadClass('Cloud_Service_SearchHelper');
			foreach($pids as $pid) {
				$searchHelper->myPostLog('delete', array('pid' => $pid));
			}
		}
	}

	public function deletethread($params) {
		$tids = $params['param'][0];
		$step = $params['step'];

		if($step == 'delete' && is_array($tids)) {
			$searchHelper = Cloud::loadClass('Cloud_Service_SearchHelper');
			foreach($tids as $tid) {
		        $searchHelper->myThreadLog('delete', array('tid' => $tid));
	        }
		}
	}

	public function undeletethreads($params) {
		$tids = $params['param'][0];

		if(is_array($tids)) {
			$searchHelper = Cloud::loadClass('Cloud_Service_SearchHelper');
			foreach($tids as $tid) {
				$searchHelper->myThreadLog('restore', array('tid' => $tid));
			}
		}
	}

	public function recyclebinpostundelete($params) {
		$pids = $params['param'][0];

		if(is_array($pids)) {
			$searchHelper = Cloud::loadClass('Cloud_Service_SearchHelper');
			foreach($pids as $pid) {
				$searchHelper->myPostLog('restore', array('pid' => $pid));
			}
		}
	}

	public function threadpubsave($params) {
		global $thread;
		$step = $params['step'];
		$posts = $params['posts'];

		if($step == 'save') {
			$searchHelper = Cloud::loadClass('Cloud_Service_SearchHelper');
			if ($thread['tid']) {
				$searchHelper->myThreadLog('update', array('tid' => $thread['tid']));
			}

			if($thread['replies'] && is_array($posts)) {
				foreach($posts as $post) {
					$searchHelper->myPostLog('update', array('pid' => $post['pid']));
				}
			}
		}
	}
}

class plugin_cloudsearch_member extends plugin_cloudsearch {

}

class plugin_cloudsearch_forum extends plugin_cloudsearch {

	public function index_catlist_top_output() {
		if(!$this->allow || !$this->allow_hot_topic) {
			return;
		}

		global $searchparams;

		$searchHelper = Cloud::loadClass('Service_SearchHelper');
		$recwords = $searchHelper->getRecWords(14, 'assoc');
		$srchotquery = '';
		if(!empty($searchparams['params'])) {
			foreach($searchparams['params'] as $key => $value) {
				$srchotquery .= '&' . $key . '=' . rawurlencode($value);
			}
		}
		return tpl_cloudsearch_index_top($recwords, $searchparams, $srchotquery);
	}

	public function viewthread_postbottom_output() {
		if(!$this->allow_thread_related) {
			return;
		}
		global $_G;
		$return = array();
		if($GLOBALS['page'] == 1 && $_G['forum_firstpid'] && $GLOBALS['postlist'][$_G['forum_firstpid']]['invisible'] == 0) {
			$return[] = tpl_cloudsearch_viewthread_postbottom_output();
			return $return;
		}
	}

	public function collection_viewoptions_output() {
		if(!$this->allow) {
			return;
		}
		global $_G;

		if($GLOBALS['permission'] || $GLOBALS['isteamworkers']) {
			return tpl_cloudsearch_collection_viewoptions_output();
		}
	}

	public function collection_relatedop_output() {
		if(!$this->allow || $GLOBALS['op'] != 'related') {
			return;
		}
		global $_G;

		if(!$GLOBALS['permission'] && !$GLOBALS['isteamworkers']) {
			showmessage('undefined_action', NULL);
		}
		$_GET['keyword'] = trim($_GET['keyword']);
		$gKeyword = $_GET['keyword'] ? $_GET['keyword'] : $_G['collection']['name'];

		$tids = array();

		$searchHelper = Cloud::loadClass('Service_SearchHelper');
		$cloudData = $searchHelper->getRelatedThreadsTao($gKeyword, $_G['page'], $_G['tpp']);

		if($cloudData['result']['data']) {
			foreach ($cloudData['result']['ad']['content'] as $sAdv) {
				$threadlist[] = array('icon' => (string)$cloudData['result']['ad']['icon']) + $sAdv;
			}
			foreach ($cloudData['result']['data'] as $sPost) {
				$threadlist[] = $sPost;
			}

			loadcache('forums');
			foreach($threadlist as $curtid=>&$curvalue) {
				$curvalue['pForumName'] = $_G['cache']['forums'][$curvalue['pForumId']]['name'];
				$curvalue['istoday'] = strtotime($curvalue['pPosted']) > $todaytime ? 1 : 0;
				$curvalue['dateline'] = $curvalue['pPosted'];
			}
			$multipage = multi($cloudData['result']['total'], $_G['tpp'], $_G['page'], "forum.php?mod=collection&action=view&op=related&ctid={$_G['collection']['ctid']}&keyword=".urlencode($_GET['keyword']));
		}
		return tpl_cloudsearch_collection_relatedop_output($threadlist, $multipage);
	}

	public function collection_threadlistbottom_output() {
		if(!$this->allow_collection_related || !$GLOBALS['ctid'] || $GLOBALS['action'] != 'view' || $GLOBALS['op']) {
			return;
		}
		global $_G;

		return tpl_cloudsearch_relate_threadlist_output(urlencode($_G['collection']['name'].' '.implode(' ', $_G['collection']['arraykeyword'])));
	}

	public function forumdisplay_threadlist_bottom_output() {
		global $_G;

		if(!$this->allow_forum_related|| $_G['page'] > 1) {
			return;
		}

		return tpl_cloudsearch_relate_threadlist_output(urlencode($_G['forum']['name']));
	}

	public function forumdisplay_middle_output() {
		if(!$this->allow || !$this->allow_forum_recommend) {
			return;
		}

		global $_G, $searchparams;
		$result = '';
		if ($_G['fid']) {
			$searchHelper = Cloud::loadClass('Service_SearchHelper');
			$recwords = $searchHelper->getRecWords(14, 'assoc', $_G['fid']);
			$srchotquery = '';
			if(!empty($searchparams['params'])) {
				foreach($searchparams['params'] as $key => $value) {
					$srchotquery .= '&' . $key . '=' . rawurlencode($value);
				}
			}
			$result = tpl_cloudsearch_index_top($recwords, $searchparams, $srchotquery, 'hotopic_fm');
		}

		return $result;
	}

	public function viewthread_middle_output() {

		if (!$this->allow || !$this->allow_thread_related_bottom) {
			return;
		}

		global $_G;
		if ($_G['page'] == 1) {
			return;
		}

		return tpl_cloudsearch_viewthread_modaction_output();
	}

	public function index_forum_extra_output() {

		return array();
	}

	public function forumdisplay_subforum_extra_output() {

		return array();
	}

	private function _get_forum_hotspot($forumlist) {
		if (!$this->allow || !$this->allow_forum_recommend) {
			return;
		}

		global $_G, $searchparams;

		if (!is_array($forumlist) || count($forumlist) == 0 || !$searchparams) {
			return;
		}

		$srchotquery = '';
		if(!empty($searchparams['params'])) {
			foreach($searchparams['params'] as $key => $value) {
				$srchotquery .= '&' . $key . '=' . rawurlencode($value);
			}
		}

		$return = $cachenames = $fids = array();
		foreach ($forumlist as $fid => $forum) {
			$cachenames[] = 'search_recommend_words_' . $forum['fid'];
			$fids[] = $forum['fid'];
		}

		loadcache($cachenames);
		foreach ($fids as $v) {
			$forum_recwords = $_G['cache']['search_recommend_words_' . $v]['result'];
			if (!$forum_recwords) {
				continue;
			}

			$forum_recwords = array_slice($forum_recwords, 0, 3);

			foreach($forum_recwords as $key => $word) {
				$forum_recwords[$key]['url'] = $searchparams['url'] . '?' . $srchotquery . '&q=' . urlencode($word['word']) . '&source=word.hotopic_if.'.($key + 1).'&keywordType=recommend&hwfId=' . $v;
			}
			$return[$v] = tpl_index_forum_extra_output($forum_recwords);
		}

		return $return;
	}

}

class plugin_cloudsearch_group extends plugin_cloudsearch {

}

class plugin_cloudsearch_home extends plugin_cloudsearch {

}