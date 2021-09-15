<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: SearchHelper.php 33013 2013-04-08 03:31:33Z liulanbo $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class Cloud_Service_SearchHelper {

	protected static $_instance;

	public static function getInstance() {

		if (!(self::$_instance instanceof self)) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	public function __construct() {

	}

	public function getTables($table) {
		if(!in_array($table, array('post', 'thread'))) {
			return false;
		}
		$infos = getglobal($table.'table_info');
		if ($infos) {
			$tables = array();
			foreach($infos as $id => $row) {
				$tables[$id] = $id;
			}
		} else {
			$tables = array(0);
		}
		return $tables;
	}

	private function _convertForum($row) {
		$result = array();
		$map = array(
					'fid'	=> 'fId',
					'fup'	=> 'pId',
					'name'	=> 'fName',
					'type'	=> 'type',
					'displayorder'	=> 'displayOrder',
					);
		foreach($row as $k => $v) {
			if (array_key_exists($k, $map)) {
				$result[$map[$k]] = $v;
				continue;
			}

			if ($k == 'status') {
				$isGroup = false;
				switch ($v) {
					case '0' :
						$displayStatus = 'hidden';
						break;
					case '1' :
						$displayStatus = 'normal';
						break;
					case '2' :
						$displayStatus = 'some';
						break;
					case '3' :
						$displayStatus = 'normal';
						$isGroup = true;
						break;
					default :
						$displayStatus = 'unknown';
				}
				$result['displayStatus'] = $displayStatus;
				$result['isGroup'] = $isGroup;
			}
		}
		$formula = dunserialize($row['formulaperm']);
		$result['isFormula'] = $formula[1] ? TRUE : FALSE;
		$result['sign'] = md5(serialize($result));
		return $result;
	}

	public function getForums($fIds = array()) {

		if($fIds) {
			$forums = C::t('forum_forum')->fetch_all_info_by_fids($fIds);
		} else {
			$forums = C::t('forum_forum')->fetch_all_info_by_fids(0, 0, 0, 0, 0, 0, 1);
		}

		$result = array();
		$result['totalNum'] = count($forums);

		foreach($forums as $forum) {
			$result['data'][$forum['fid']] = self::_convertForum($forum);
		}

		if (!$fIds) {
			$result['sign'] = md5(serialize($result['data']));
		}
		return $result;
	}

	public function getUserGroupPermissions($userGroupIds) {

		global $_G;
		$userGroups = array();
		if ($_G['setting']['verify']['enabled']) {
			foreach ($userGroupIds as $groupId) {
				if (dintval($groupId) > 65500) {
					$vGid = $groupId - 65500;
					if ($_G['setting']['verify'][$vGid]['available']) {
						$key = array_search($groupId, $userGroupIds);
						unset($userGroupIds[$key]);
						$userGroups['v' . $vGid] = array();
					}
				}
			}
		}

		$fields = array(
						'groupid' => 'userGroupId',
						'grouptitle' => 'userGroupName',
						'readaccess'	=> 'readPermission',
						'allowvisit'	=> 'allowVisit',
						'allowsearch'	=> 'searchLevel',
						);
		$userGroup= C::t('common_usergroup')->fetch_all((array)$userGroupIds);
		$userGroupField = C::t('common_usergroup_field')->fetch_all_fields((array)$userGroupIds, array('readaccess', 'allowsearch'));
		foreach(array_merge($userGroup, $userGroupField) as $row) {
			foreach($row as $k => $v) {
				if (array_key_exists($k, $fields)) {
					if ($k == 'allowsearch') {
						$userGroups[$row['groupid']]['allowSearchAlbum'] = ($v & 8) ? true : false;
						$userGroups[$row['groupid']]['allowSearchBlog'] = ($v & 4) ? true : false;
						$userGroups[$row['groupid']]['allowSearchForum'] = ($v & 2) ? true : false;
						$userGroups[$row['groupid']]['allowSearchPortal'] = ($v & 1) ? true : false;
						$userGroups[$row['groupid']]['allowFulltextSearch'] = ($v & 32) ? true : false;
					} else {
						$userGroups[$row['groupid']][$fields[$k]] = $v;
					}
				}
				$userGroups[$row['groupid']]['forbidForumIds'] = array();
				$userGroups[$row['groupid']]['allowForumIds'] = array();
				$userGroups[$row['groupid']]['specifyAllowForumIds'] = array();
			}
		}

		$fIds = array();
		foreach(C::t('forum_forum')->fetch_all_by_status('1') as $row) {
			$fIds[$row['fid']] = $row['fid'];
		}

		$fieldForums = array();
		foreach(C::t('forum_forumfield')->fetch_all_by_fid($fIds) as $row) {
			$fieldForums[$row['fid']] = $row;
		}

		foreach($fIds as $fId) {
			$row = $fieldForums[$fId];
			$allowViewGroupIds = array();
			if ($row['viewperm']) {
				$allowViewGroupIds = explode("\t", $row['viewperm']);
			}
			foreach($userGroups as $gid => $_v) {
				if ($row['password']) {
					$userGroups[$gid]['forbidForumIds'][] = $fId;
					continue;
				}
				$perm = dunserialize($row['formulaperm']);
				if(is_array($perm)) {
					$spviewperm = explode("\t", $row['spviewperm']);
					if (in_array($gid, $spviewperm)) {
						$userGroups[$gid]['allowForumIds'][] = $fId;
						$userGroups[$gid]['specifyAllowForumIds'][] = $fId;
						continue;
					}
					if ($perm[0] || $perm[1] || $perm['users']) {
						$userGroups[$gid]['forbidForumIds'][] = $fId;
						continue;
					}
				}
				if (!$allowViewGroupIds) {
					$userGroups[$gid]['allowForumIds'][] = $fId;
				} elseif (!in_array($gid, $allowViewGroupIds)) {
					$userGroups[$gid]['forbidForumIds'][] = $fId;
				} elseif (in_array($gid, $allowViewGroupIds)) {
					$userGroups[$gid]['allowForumIds'][] = $fId;
					$userGroups[$gid]['specifyAllowForumIds'][] = $fId;
				}
			}
		}

		foreach ($userGroups as $groupId => $v) {
			if (substr($groupId, 0, 1) == 'v') {
				$verifyKey = 65500 + dintval(substr($groupId, 1));
				$v['userGroupId'] = $verifyKey;
				$userGroups[$verifyKey] = $v;
				unset($userGroups[$groupId]);
			}
		}

		foreach($userGroups as $k => $v) {
			ksort($v);
			$userGroups[$k]['sign'] = md5(serialize($v));
		}
		return $userGroups;
	}

	public function getGuestPerm($gfIds = array()) {
		$perm = self::getUserGroupPermissions(array(7));
		$guestPerm = $perm[7];
		if ($gfIds) {
			foreach(C::t('forum_forumfield')->fetch_all_by_fid($gfIds) as $row) {
				if ($row['gviewperm'] == 1) {
					$guestPerm['allowForumIds'][] = $row['fid'];
				} else {
					$guestPerm['forbidForumIds'][] = $row['fid'];
				}
			}

		}
		return $guestPerm;
	}

	public function convertThread($row) {
		$result = array();
		$map = array(
					'tid'	=> 'tId',
					'fid'	=> 'fId',
					'authorid'	=> 'authorId',
					'author'	=> 'authorName',
					'special'	=> 'specialType',
					'price'	=> 'price',
					'subject'	=> 'subject',
					'readperm'	=> 'readPermission',
					'lastposter'	=> 'lastPoster',
					'views'	=> 'viewNum',
					'replies'	=> 'replyNum',
					'displayorder'	=> 'stickLevel',
					'highlight'	=> 'isHighlight',
					'digest'	=> 'digestLevel',
					'rate'	=> 'rate',
					'attachment'	=> 'isAttached',
					'moderated'	=> 'isModerated',
					'closed'	=> 'isClosed',
					'supe_pushstatus'	=> 'supeSitePushStatus',
					'recommends'	=> 'recommendTimes',
					'recommend_add'	=> 'recommendSupportTimes',
					'recommend_sub'	=> 'recommendOpposeTimes',
					'heats'		=> 'heats',
					'pid'		=> 'pId',
					'isgroup' => 'isGroup',
					'posttableid' => 'postTableId',
					'favtimes'  => 'favoriteTimes',
					'sharetimes'=> 'shareTimes',
					'icon'  => 'icon',
					);
		$map2 = array(
					'dateline'	=> 'createdTime',
					'lastpost'	=> 'lastPostedTime',
					);
		foreach($row as $k => $v) {
			if (array_key_exists($k, $map)) {
				if ($k == 'special') {
					switch($v) {
						case 1:
							$v = 'poll';
							break;
						case 2:
							$v = 'trade';
							break;
						case 3:
							$v = 'reward';
							break;
						case 4:
							$v = 'activity';
							break;
						case 5:
							$v = 'debate';
							break;
						case 127:
							$v = 'plugin';
							break;
						default:
							$v = 'normal';
					}
				}

				if ($k == 'displayorder') {
					if ($v >= 0) {
						$result['displayStatus'] = 'normal';
					} elseif ($v == -1) {
						$result['displayStatus'] = 'recycled';
					} elseif ($v == -2) {
						$result['displayStatus'] = 'unapproved';
					} elseif ($v == -3) {
						$result['displayStatus'] = 'ignored';
					} elseif ($v == -4) {
						$result['displayStatus'] = 'draft';
					} else {
						$result['displayStatus'] = 'unknown';
					}

					switch($v) {
						case 1:
							$v = 'board';
							break;
						case 2:
							$v = 'group';
							break;
						case 3:
							$v = 'global';
							break;
						case 0:
						default:
							$v = 'none';
					}
				}

				if (in_array($k, array('highlight', 'moderated', 'closed', 'isgroup'))) {
					$v = $v ? true : false;
				}
				$result[$map[$k]] = $v;
			} elseif (array_key_exists($k, $map2)) {
				$result[$map2[$k]] = dgmdate($v, 'Y-m-d H:i:s', 8);
			}
		}
		return $result;
	}

	public function preGetThreads($tableid, $tIds) {
		$result = array();
		if($tIds) {
			foreach(C::t('forum_thread')->fetch_all_by_tid($tIds, 0, 0, $tableid) as $thread) {
				$result[$thread['tid']] = self::convertThread($thread);
			}
		}
		return $result;
	}

	public function getThreadPosts($tIds) {
		$result = array();
		foreach($tIds as $postTableId => $_tIds) {
			foreach(C::t('forum_post')->fetch_all_by_tid($postTableId, $_tIds, true, '', 0, 0, 1) as $post) {
				$result[$post['tid']] = self::convertPost($post);
			}
		}
		return $result;
	}

	public function getThreads($tIds, $isReturnPostId = true) {
		global $_G;
		$tables = array();
		$infos = $_G['setting']['threadtable_info'];
		if ($infos) {
			foreach($infos as $id => $row) {
				$tables[] = $id;
			}
		} else {
			$tables = array('forum_thread');
		}

		$tableNum = count($tables);
		$res = $data = $_tableInfo = array();
		for($i = 0; $i < $tableNum; $i++) {
			$_threads = self::preGetThreads($tables[$i], $tIds);
			if ($_threads) {
				if (!$data) {
					$data = $_threads;
				} else {
					$data = $data +  $_threads;
				}
				if (count($data) == count($tIds)) {
					break;
				}
			}
		}

		if ($isReturnPostId) {
			$threadIds = array();
			foreach($data as $tId => $thread) {
				$postTableId = $thread['postTableId'];
				$threadIds[$postTableId][] = $tId;
			}

			$threadPosts = self::getThreadPosts($threadIds);
			foreach($data as $tId => $thread) {
				$data[$tId]['pId'] = $threadPosts[$tId]['pId'];
			}
		}
		return $data;
	}

	public function convertPost($row) {
		$result = array();
		$map = array('pid' => 'pId',
						'tid'	=> 'tId',
						'fid'	=> 'fId',
						'authorid'	=> 'authorId',
						'author'	=> 'authorName',
						'useip'	=> 'authorIp',
						'anonymous'	=> 'isAnonymous',
						'subject'	=> 'subject',
						'message'	=> 'content',
						'invisible'	=> 'displayStatus',
						'htmlon'	=> 'isHtml',
						'attachment'	=> 'isAttached',
						'rate'	=> 'rate',
						'ratetimes'	=> 'rateTimes',
						'dateline'	=> 'createdTime',
						'first'		=> 'isThread',
					   );
		$map2 = array(
					  'bbcodeoff'	=> 'isBbcode',
					  'smileyoff'	=> 'isSmiley',
					  'parseurloff'	=> 'isParseUrl',
					 );
		foreach($row as $k => $v) {
			if (array_key_exists($k, $map)) {
				if ($k == 'invisible') {
					switch($v) {
						case 0:
							$v = 'normal';
							break;
						case -1:
							$v = 'recycled';
							break;
						case -2:
							$v = 'unapproved';
							break;
						case -3:
							$v = 'ignored';
							break;
						case -4:
							$v = 'draft';
							break;
						default:
							$v = 'unkonwn';
					}
				}
				if ($k == 'dateline') {
					$result[$map[$k]] = dgmdate($v, 'Y-m-d H:i:s', 8);
					continue;
				}

				if (in_array($k, array('htmlon', 'attachment', 'first', 'anonymous'))) {
					$v = $v ? true : false;
				}

				$result[$map[$k]] = $v;
			} elseif (array_key_exists($k, $map2)) {
				$result[$map2[$k]] = $v ? false : true;
			}
		}
		$result['isWarned'] = $result['isBanned'] = false;
		if ($row['status'] & 1) {
			$result['isBanned'] = true;
		}
		if ($row['status'] & 2) {
			$result['isWarned'] = true;
		}
		$attachInfo = array();
		if ($result['isAttached']) {
			$attachIndex = C::t('forum_attachment')->fetch_all_by_id('pid', $row['pid']);
			$attachment = C::t('forum_attachment_n')->fetch_all_by_id('pid:'.$row['pid'], 'pid', $row['pid'], 'aid');
			$attachMap = array(
				'aid' => 'aId',
				'tid' => 'tId',
				'pid' => 'pId',
				'uid' => 'uId',
				'dateline' => 'uploadedTime',
				'filename' => 'fileName',
				'filesize' => 'fileSize',
				'attachment' => 'filePath',
				'remote' => 'isRemote',
				'description' => 'description',
				'readperm' => 'readPerm',
				'price' => 'price',
				'isimage' => 'isImage',
				'width' => 'width',
				'thumb' => 'isThumb',
				'picid' => 'picId',
			);
			foreach ($attachment as $k => $v) {
				$attachTemp = array();
				foreach ($v as $key => $val) {
					if ($key == 'dateline') {
						$attachTemp[$attachMap[$key]] = dgmdate($val, 'Y-m-d H:i:s', 8);
						continue;
					}
					$attachTemp[$attachMap[$key]] = $val;
				}
				$attachInfo[$k] = $attachTemp;
				$attachInfo[$k]['downloadTimes'] = $attachIndex[$k]['downloads'];
			}
		}

		$result['attachInfo'] = $attachInfo;
		return $result;
	}

	public function convertNav($row) {
		$map = array(	'id' => 'id',
						'name' => 'name',
						'title' => 'title',
						'url' => 'url',
						'type' => 'provider',
						'navtype' => 'navType',
						'available' => 'available',
						'displayorder' => 'displayOrder',
						'target' => 'linkTarget',
						'highlight' => 'highlight',
						'level' => 'userGroupLevel',
						'subtype' => 'subLayout',
						'subcols' => 'subColNum',
						'subname' => 'subName',
						'suburl' => 'subUrl',
					   );

		foreach($row as $k => $v) {
			if (array_key_exists($k, $map)) {
				if (in_array($k, array('available'))) {
					$v = $v > 0 ? true : false;
				}
				if ($k == 'subtype') {
					if ($v == 1) {
						$v = 'parallel';
					} else {
						$v = 'menu';
					}
				}
				if ($k == 'type') {
					switch($v) {
						case '1':
							$v = 'user';
							break;
						case '0':
						default:
							$v = 'system';
							break;
					}
				}
				if ($k == 'navtype') {
					switch($v) {
						case 1:
							$v = 'footer';
							break;
						case 2:
							$v = 'space';
							break;
						case 3:
							$v = 'my';
							break;
						case 0:
							$v = 'header';
							break;
					}
				}
				$result[$map[$k]] = $v;
			}
		}
		return $result;
	}

	public function convertPoll($row) {
		$map = array('polloptionid' => 'id',
				'tid' => null,
				'votes' => 'votes',
				'displayorder' => 'displayOrder',
				'polloption' => 'label',
				'voterids' => 'voterIds',
				);
		$result = array();
		foreach($row as $k => $v) {
			$field = $map[$k];
			if ($field !== null) {
				$result[$field] = $v;
			}
		}
		return $result;
	}

	public function getPollInfo($tIds) {
		$result = array();
		foreach(C::t('forum_polloption')->fetch_all_by_tid($tIds) as $row) {
			$result[$row['tid']][$row['polloptionid']] = self::convertPoll($row);
		}
		return $result;

	}

	public function getThreadSort($tIds) {
		global $_G;

		$optionvar = array();
		foreach(C::t('forum_typeoptionvar')->fetch_all_by_tid_optionid($tIds) as $row) {
			if(!isset($_G['cache']['threadsort_option_'.$row['sortid']])) {
				loadcache(array('threadsort_option_'.$row['sortid']));
			}
			$title = $_G['cache']['threadsort_option_'.$row['sortid']][$row['optionid']]['title'];
			$type = $_G['cache']['threadsort_option_'.$row['sortid']][$row['optionid']]['type'];
			if($title && !in_array($type, array('image'))) {
				$optionvar[$row['tid']][$title] = $row['value'];
			}
		}
		return $optionvar;
	}



	public function allowSearchForum() {
		C::t('common_usergroup_field')->update_allowsearch();
		require_once libfile('function/cache');
		updatecache('usergroups');
	}

	public function myThreadLog($opt, $data) {
		global $_G;

		$cloudAppService = Cloud::loadClass('Service_App');
		if(!$cloudAppService->getCloudAppStatus('search')) return;
		$data['action'] = $opt;
		$data['dateline'] = time();
		C::t('forum_threadlog')->insert($data, false, true);
	}

	public function myPostLog($opt, $data) {
		global $_G;

		$cloudAppService = Cloud::loadClass('Service_App');
		if(!$cloudAppService->getCloudAppStatus('search')) return;
		$data['action'] = $opt;
		$data['dateline'] = time();
		C::t('forum_postlog')->insert($data, false, true);
	}

	public function getRelatedThreadsTao($keyword, $page, $tpp, $excludeForumIds = '', $cache = false) {
		global $_G;

		$sId = $_G['setting']['my_siteid'];
		$result = array();
		if($sId) {
			if($cache === true) {
				$kname = 'search_recommend_thread_'.$keyword.'_'.$page.'_'.$excludeForumIds;
				loadcache($kname);
			}
			if(isset($_G['cache'][$kname]['ts']) && (TIMESTAMP - $_G['cache'][$kname]['ts'] <= 21600)) {
				$result = $_G['cache'][$kname]['result'];
			} else {

				$apiUrl = 'http://api.discuz.qq.com/search/discuz/tao?';
				$params = array(
					'sId' => $sId,
					'q' => $keyword,
					'tpp' => $tpp,
					'excludeForumIds' => $excludeForumIds,
					'page' => $page ? $page : 1,
					'clientIp' => $_G['clientip']
				);

				$utilService = Cloud::loadClass('Service_Util');
				$response = dfsockopen($apiUrl.$utilService->generateSiteSignUrl($params), 0, '', '', false, $_G['setting']['cloud_api_ip']);
				require_once libfile('class/xml');
				$result = (array) xml2array($response);

				if($cache === true && isset($result['status']) && $result['status'] == 0) {
					save_syscache($kname, array('ts' => TIMESTAMP, 'result' => $result));
				}
				if($result['status'] != 0) {
					$result = null;
				}
			}
		}

		return $result;
	}

	public function getRelatedThreads($fId, $cache = false) {
		global $_G;

		$sId = $_G['setting']['my_siteid'];
		$result = array();
		if($sId) {
			if($cache === true) {
				$kname = 'search_recommend_fidthread_'.$fId;
				loadcache($kname);
			}
			if(isset($_G['cache'][$kname]['ts']) && (TIMESTAMP - $_G['cache'][$kname]['ts'] <= 21600)) {
				$result = $_G['cache'][$kname]['result'];
			} else {

				$apiUrl = 'http://api.discuz.qq.com/search/discuz/forumRelated?';
				$params = array(
					'sId' => $sId,
					'fId' => $fId,
					'clientIp' => $_G['clientip']
				);

				$utilService = Cloud::loadClass('Service_Util');
				$response = dfsockopen($apiUrl.$utilService->generateSiteSignUrl($params), 0, '', '', false, $_G['setting']['cloud_api_ip']);
				require_once libfile('class/xml');
				$result = (array) xml2array($response);

				if($cache === true && isset($result['status']) && $result['status'] == 0) {
					save_syscache($kname, array('ts' => TIMESTAMP, 'result' => $result));
				}
				if($result['status'] != 0) {
					$result = null;
				}
			}
		}

		return $result;
	}


	public function getRecWords($needNum = 14, $format = 'num', $fid = 0) {
		global $_G;

		$sId = $_G['setting']['my_siteid'];
		$data = array();

		if($sId) {
			$fid = $fid ? $fid : 0;
			$kname = 'search_recommend_words_' . $fid;
			loadcache($kname);

			$cacheLife = isset($_G['setting']['my_search_data']['recwords_lifetime']) ? intval($_G['setting']['my_search_data']['recwords_lifetime']) : 21600;
			$cloudSettingTime = isset($_G['setting']['my_search_data']['set_forbidden_recwords_time']) ? intval($_G['setting']['my_search_data']['set_forbidden_recwords_time']) : 0;
			$cacheSettingTime = isset($_G['cache'][$kname]['setting_ts']) ? intval($_G['cache'][$kname]['setting_ts']) : 0;

			if((!$cloudSettingTime || $cloudSettingTime == $cacheSettingTime) && isset($_G['cache'][$kname]['ts']) && (TIMESTAMP - $_G['cache'][$kname]['ts'] <= $cacheLife)) {
				$data = $_G['cache'][$kname]['result'];
			} else {
				$apiUrl = 'http://api.discuz.qq.com/search/recwords/get';
				$params = array(
					's_id' => $sId,
					'f_id' => $fid,
					'need_random' => false,
					'need_num' => $needNum,
					'version' => $format == 'num' ? 1 : 2, // 1：返回数字下标的结果集、2：返回关联数组形式的结果集
					'close' => 1,
				);

				$utilService = Cloud::loadClass('Service_Util');
				$response = dfsockopen($apiUrl, 0, $utilService->generateSiteSignUrl($params), '', false, $_G['setting']['cloud_api_ip']);
				$result = (array) unserialize($response);

				if(isset($result['status']) && $result['status'] === 0) {
					$data = $result['result'];

					if($cloudSettingTime) {
						save_syscache($kname, array('ts' => TIMESTAMP, 'setting_ts' => $cloudSettingTime, 'result' => $data));
					} else {
						save_syscache($kname, array('ts' => TIMESTAMP, 'result' => $data));
					}

				}
			}
		}

		return $data;
	}

	public function makeSearchSignUrl() {
		global $_G;

		$url = '';
		$params = array();
		$mySearchData = $_G['setting']['my_search_data'];
		$mySiteId = $_G['setting']['my_siteid'];
		$mySiteKey = $_G['setting']['my_sitekey'];

		$cloudAppService = Cloud::loadClass('Service_App');
		if($mySearchData['status'] && $cloudAppService->getCloudAppStatus('search') && $mySiteId) {
			$myExtGroupIds = array();
			$_extGroupIds = explode("\t", $_G['member']['extgroupids']);

			foreach($_extGroupIds as $v) {
				if($v) {
					$myExtGroupIds[] = $v;
				}
			}

			$myExtGroupIdsStr = implode(',', $myExtGroupIds);
			$params = array(
				'sId' => $mySiteId,
				'ts' => time(),
				'cuId' => $_G['uid'],
				'cuName' => $_G['username'],
				'gId' => intval($_G['groupid']),
				'agId' => intval($_G['adminid']),
				'egIds' => $myExtGroupIdsStr,
				'fmSign' => '',
			);

			$groupIds = array($params['gId']);
			if($params['agId']) {
				$groupIds[] = $params['agId'];
			}

			if($myExtGroupIds) {
				$groupIds = array_merge($groupIds, $myExtGroupIds);
			}

			$groupIds = array_unique($groupIds);
			foreach($groupIds as $v) {
				$key = 'ugSign' . $v;
				$params[$key] = '';
			}

			$extraParams = array();
			if (isset($_G['setting']['verify']['enabled']) && $_G['setting']['verify']['enabled']) {
				$verifyGroups = C::t('common_member_verify')->fetch($_G['uid']);
				$extraParams['ext_vgIds'] = 0;
				foreach ($verifyGroups as $k => $v) {
					if ($k != 'uid') {
						$position = dintval(substr($k, strlen('verify')));
						$extraParams['ext_vgIds'] = setstatus($position, dintval($v), $extraParams['ext_vgIds']);
					}
				}
			}

			if ($_G['cookie']['ffids' . $_G['uid']]) {
				$ext_ffids = str_replace('D', ',', authcode($_G['cookie']['ffids' . $_G['uid']], 'DECODE'));
				$extraParams['ext_ffids'] = $ext_ffids;
			}

			if (!empty($extraParams)) {
				ksort($extraParams);
				$params = array_merge($params, $extraParams);
			}

			$params['sign'] = md5(implode('|', $params) . '|' . $mySiteKey);

			if ($cloudAppService->getCloudAppStatus('connect') && $_G['member']['conopenid']) {
				$connectService = Cloud::loadClass('Service_Connect');
				$connectService->connectMergeMember();
				$params['openid'] = $_G['member']['conopenid'];
			}

			$params['charset'] = $_G['charset'];
			if($mySearchData['domain']) {
				$domain = $mySearchData['domain'];
			} else {
				$domain = 'search.discuz.qq.com';
			}

			$url = 'http://' . $domain . '/f/discuz';
		}
		return !empty($url) ? array('url' => $url, 'params' => $params) : array();
	}
}