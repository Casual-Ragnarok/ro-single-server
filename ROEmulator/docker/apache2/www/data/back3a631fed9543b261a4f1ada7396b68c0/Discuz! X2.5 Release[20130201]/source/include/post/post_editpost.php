<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: post_editpost.php 32176 2012-11-23 03:23:39Z liulanbo $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}
$orig = C::t('forum_post')->fetch('tid:'.$_G['tid'], $pid, false);
$isfirstpost = $orig['first'] ? 1 : 0;

if($isfirstpost && (($special == 1 && !$_G['group']['allowpostpoll']) || ($special == 2 && !$_G['group']['allowposttrade']) || ($special == 3 && !$_G['group']['allowpostreward']) || ($special == 4 && !$_G['group']['allowpostactivity']) || ($special == 5 && !$_G['group']['allowpostdebate']))) {
	showmessage('group_nopermission', NULL, array('grouptitle' => $_G['group']['grouptitle']), array('login' => 1));
}
if($orig && $orig['fid'] == $_G['fid'] && $orig['tid'] == $_G['tid']) {
	$user = getuserbyuid($orig['authorid']);
	$orig['adminid'] = $user['adminid'];
} else {
	$orig = array();
}

if($_G['setting']['magicstatus']) {
	$magiclog = C::t('forum_threadmod')->fetch_by_tid_magicid($_G['tid'], 10);
	$magicid = $magiclog['magicid'];
	$_G['group']['allowanonymous'] = $_G['group']['allowanonymous'] || $magicid ? 1 : $_G['group']['allowanonymous'];
}

$isorigauthor = $_G['uid'] && $_G['uid'] == $orig['authorid'];
$isanonymous = ($_G['group']['allowanonymous'] || $orig['anonymous']) && getgpc('isanonymous') ? 1 : 0;
$audit = $orig['invisible'] == -2 || $thread['displayorder'] == -2 ? $_GET['audit'] : 0;

if(empty($orig)) {
	showmessage('post_nonexistence');
} elseif((!$_G['forum']['ismoderator'] || !$_G['group']['alloweditpost'] || (in_array($orig['adminid'], array(1, 2, 3)) && $_G['adminid'] > $orig['adminid'])) && !(($_G['forum']['alloweditpost'] || $orig['invisible'] == -3)&& $isorigauthor)) {
	showmessage('post_edit_nopermission', NULL);
} elseif($isorigauthor && !$_G['forum']['ismoderator'] && $orig['invisible'] != -3) {
	$alloweditpost_status = getstatus($_G['setting']['alloweditpost'], $special + 1);
	if(!$alloweditpost_status && $_G['group']['edittimelimit'] && TIMESTAMP - $orig['dateline'] > $_G['group']['edittimelimit'] * 60) {
		showmessage('post_edit_timelimit', NULL, array('edittimelimit' => $_G['group']['edittimelimit']));
	}
}

$thread['pricedisplay'] = $thread['price'] == -1 ? 0 : $thread['price'];

if($special == 5) {
	$debate = array_merge($thread, daddslashes(C::t('forum_debate')->fetch($_G['tid'])));
	$firststand = C::t('forum_debatepost')->get_firststand($_G['tid'], $_G['uid']);

	if(!$isfirstpost && $debate['endtime'] && $debate['endtime'] < TIMESTAMP && !$_G['forum']['ismoderator']) {
		showmessage('debate_end');
	}
	if($isfirstpost && $debate['umpirepoint'] && !$_G['forum']['ismoderator']) {
		showmessage('debate_umpire_comment_invalid');
	}
}

$rushreply = getstatus($thread['status'], 3);


if($isfirstpost && $isorigauthor && $_G['group']['allowreplycredit']) {
	if($replycredit_rule = C::t('forum_replycredit')->fetch($_G['tid'])) {
		if($thread['replycredit']) {
			$replycredit_rule['lasttimes'] = $thread['replycredit'] / $replycredit_rule['extcredits'];
		}
		$replycredit_rule['extcreditstype'] = $replycredit_rule['extcreditstype'] ? $replycredit_rule['extcreditstype'] : $_G['setting']['creditstransextra'][10];
	}
}

if(!submitcheck('editsubmit')) {

	$thread['hiddenreplies'] = getstatus($thread['status'], 2);


	$postinfo = C::t('forum_post')->fetch('tid:'.$_G['tid'], $pid);
	if($postinfo['fid'] != $_G['fid'] || $postinfo['tid'] != $_G['tid']) {
		$postinfo = array();
	}

	$usesigcheck = $postinfo['usesig'] ? 'checked="checked"' : '';
	$urloffcheck = $postinfo['parseurloff'] ? 'checked="checked"' : '';
	$smileyoffcheck = $postinfo['smileyoff'] == 1 ? 'checked="checked"' : '';
	$codeoffcheck = $postinfo['bbcodeoff'] == 1 ? 'checked="checked"' : '';
	$tagoffcheck = $postinfo['htmlon'] & 2 ? 'checked="checked"' : '';
	$htmloncheck = $postinfo['htmlon'] & 1 ? 'checked="checked"' : '';
	if($htmloncheck) {
		$editor['editormode'] = 0;
		$editor['allowswitcheditor'] = 0;
	}
	$showthreadsorts = ($thread['sortid'] || !empty($sortid)) && $isfirstpost;
	$sortid = empty($sortid) ? $thread['sortid'] : $sortid;

	$poll = $temppoll = '';
	if($isfirstpost) {
		if($postinfo['tags']) {
			$tagarray_all = $array_temp = $threadtag_array = array();
			$tagarray_all = explode("\t", $postinfo['tags']);
			if($tagarray_all) {
				foreach($tagarray_all as $var) {
					if($var) {
						$array_temp = explode(',', $var);
						$threadtag_array[] = $array_temp['1'];
					}
				}
			}
			$postinfo['tag'] = implode(',', $threadtag_array);
		}
		$allownoticeauthor = getstatus($thread['status'], 6);

		if($rushreply) {
			$postinfo['rush'] = C::t('forum_threadrush')->fetch($_G['tid']);
			if($postinfo['rush']['creditlimit'] == -996) {
				$postinfo['rush']['creditlimit'] = '';
			}
			$postinfo['rush']['stopfloor'] = $postinfo['rush']['stopfloor'] ? $postinfo['rush']['stopfloor'] : '';
			$postinfo['rush']['starttimefrom'] = $postinfo['rush']['starttimefrom'] ? dgmdate($postinfo['rush']['starttimefrom'], 'Y-m-d H:i') : '';
			$postinfo['rush']['starttimeto'] = $postinfo['rush']['starttimeto'] ? dgmdate($postinfo['rush']['starttimeto'], 'Y-m-d H:i') : '';
		}

		if($special == 127) {
			$sppos = strpos($postinfo['message'], chr(0).chr(0).chr(0));
			$specialextra = substr($postinfo['message'], $sppos + 3);
			if($specialextra && array_key_exists($specialextra, $_G['setting']['threadplugins']) && in_array($specialextra, $_G['forum']['threadplugin']) && in_array($specialextra, $_G['group']['allowthreadplugin'])) {
				$postinfo['message'] = substr($postinfo['message'], 0, $sppos);
			} else {
				showmessage('post_edit_nopermission_threadplign');
				$special = 0;
				$specialextra = '';
			}
		}
		$thread['freecharge'] = $_G['setting']['maxchargespan'] && TIMESTAMP - $thread['dateline'] >= $_G['setting']['maxchargespan'] * 3600 ? 1 : 0;
		$freechargehours = !$thread['freecharge'] ? $_G['setting']['maxchargespan'] - intval((TIMESTAMP - $thread['dateline']) / 3600) : 0;
		if($thread['special'] == 1 && ($_G['group']['alloweditpoll'] || $thread['authorid'] == $_G['uid'])) {
			$pollinfo = C::t('forum_poll')->fetch($_G['tid']);
			$query = C::t('forum_polloption')->fetch_all_by_tid($_G['tid']);
			foreach($query as $temppoll) {
				$poll['multiple'] = $pollinfo['multiple'];
				$poll['visible'] = $pollinfo['visible'];
				$poll['maxchoices'] = $pollinfo['maxchoices'];
				$poll['expiration'] = $pollinfo['expiration'];
				$poll['overt'] = $pollinfo['overt'];
				$poll['polloptionid'][] = $temppoll['polloptionid'];
				$poll['displayorder'][] = $temppoll['displayorder'];
				$poll['polloption'][] = $temppoll['polloption'];
			}
		} elseif($thread['special'] == 3) {
			$rewardprice = $thread['price'];
		} elseif($thread['special'] == 4) {
			$activitytypelist = $_G['setting']['activitytype'] ? explode("\n", trim($_G['setting']['activitytype'])) : '';
			$activity = C::t('forum_activity')->fetch($_G['tid']);
			$activity['starttimefrom'] = dgmdate($activity['starttimefrom'], 'Y-m-d H:i');
			$activity['starttimeto'] = $activity['starttimeto'] ? dgmdate($activity['starttimeto'], 'Y-m-d H:i') : '';
			$activity['expiration'] = $activity['expiration'] ? dgmdate($activity['expiration'], 'Y-m-d H:i') : '';
			$activity['ufield'] = $activity['ufield'] ? dunserialize($activity['ufield']) : array();
			if($activity['ufield']['extfield']) {
				$activity['ufield']['extfield'] = implode("\n", $activity['ufield']['extfield']);
			}
		} elseif($thread['special'] == 5 ) {
			$debate['endtime'] = $debate['endtime'] ? dgmdate($debate['endtime'], 'Y-m-d H:i') : '';
		}
		if ($_G['group']['allowsetpublishdate']) {
			loadcache('cronpublish');
			$cron_publish_ids = dunserialize(getglobal('cache/cronpublish'));
			if (in_array($_G['tid'], $cron_publish_ids)) {
				$cronpublish = 1;
				$cronpublishdate = dgmdate($thread['dateline'], "dt");
			}
		}
	}

	if($thread['special'] == 2 && ($thread['authorid'] == $_G['uid'] && $_G['group']['allowposttrade'] || $_G['group']['allowedittrade'])) {
		$trade = C::t('forum_trade')->fetch_goods(0, $pid);
		if($trade) {
			$trade['expiration'] = $trade['expiration'] ? date('Y-m-d', $trade['expiration']) : '';
			$trade['costprice'] = $trade['costprice'] > 0 ? $trade['costprice'] : '';
			$trade['message'] = dhtmlspecialchars($trade['message']);
			$expiration_7days = date('Y-m-d', TIMESTAMP + 86400 * 7);
			$expiration_14days = date('Y-m-d', TIMESTAMP + 86400 * 14);
			$expiration_month = date('Y-m-d', mktime(0, 0, 0, date('m')+1, date('d'), date('Y')));
			$expiration_3months = date('Y-m-d', mktime(0, 0, 0, date('m')+3, date('d'), date('Y')));
			$expiration_halfyear = date('Y-m-d', mktime(0, 0, 0, date('m')+6, date('d'), date('Y')));
			$expiration_year = date('Y-m-d', mktime(0, 0, 0, date('m'), date('d'), date('Y')+1));
		} else {
			$special = 0;
			$trade = array();
		}
	}

	if($isfirstpost && $specialextra) {
		@include_once DISCUZ_ROOT.'./source/plugin/'.$_G['setting']['threadplugins'][$specialextra]['module'].'.class.php';
		$classname = 'threadplugin_'.$specialextra;
		if(class_exists($classname) && method_exists($threadpluginclass = new $classname, 'editpost')) {
			$threadplughtml = $threadpluginclass->editpost($_G['fid'], $_G['tid']);
		}
	}

	$postinfo['subject'] = str_replace('"', '&quot;', $postinfo['subject']);
	$postinfo['message'] = dhtmlspecialchars($postinfo['message']);
	$language = lang('forum/misc');
	$postinfo['message'] = preg_replace($postinfo['htmlon'] ? $language['post_edithtml_regexp'] : (!$_G['forum']['allowbbcode'] || $postinfo['bbcodeoff'] ? $language['post_editnobbcode_regexp'] : $language['post_edit_regexp']), '', $postinfo['message']);

	if($special == 5) {
		$standselected = array($firststand => 'selected="selected"');
	}

	if($_G['group']['allowpostattach'] || $_G['group']['allowpostimage']) {
		$attachlist = getattach($pid);
		$attachs = $attachlist['attachs'];
		$imgattachs = $attachlist['imgattachs'];
		unset($attachlist);
		$attachfind = $attachreplace = array();
		if(!empty($attachs['used'])) {
			foreach($attachs['used'] as $attach) {
				if($attach['isimage']) {
					$attachfind[] = "/\[attach\]$attach[aid]\[\/attach\]/i";
					$attachreplace[] = '[attachimg]'.$attach['aid'].'[/attachimg]';
				}
			}
		}
		if(!empty($imgattachs['used'])) {
			foreach($imgattachs['used'] as $attach) {
				$attachfind[] = "/\[attach\]$attach[aid]\[\/attach\]/i";
				$attachreplace[] = '[attachimg]'.$attach['aid'].'[/attachimg]';
			}
		}
		$attachfind && $postinfo['message'] = preg_replace($attachfind, $attachreplace, $postinfo['message']);
	}
	if($special == 2 && $trade['aid'] && !empty($imgattachs['used']) && is_array($imgattachs['used'])) {
		foreach($imgattachs['used'] as $k => $tradeattach) {
			if($tradeattach['aid'] == $trade['aid']) {
				unset($imgattachs['used'][$k]);
				break;
			}
		}
	}
	if($special == 4 && $activity['aid'] && !empty($imgattachs['used']) && is_array($imgattachs['used'])) {
		foreach($imgattachs['used'] as $k => $activityattach) {
			if($activityattach['aid'] == $activity['aid']) {
				unset($imgattachs['used'][$k]);
				break;
			}
		}
	}

	if($sortid) {
		require_once libfile('post/threadsorts', 'include');
		foreach($_G['forum_optionlist'] as $option) {
			if($option['type'] == 'image') {
				foreach($imgattachs['used'] as $k => $sortattach) {
					if($sortattach['aid'] == $option['value']['aid']) {
						unset($imgattachs['used'][$k]);
						break;
					}
				}
			}
		}
	}

	$imgattachs['unused'] = !$sortid ? $imgattachs['unused'] : '';

	include template('forum/post');

} else {

	$redirecturl = "forum.php?mod=viewthread&tid=$_G[tid]&page=$_GET[page]&extra=$extra".($vid && $isfirstpost ? "&vid=$vid" : '')."#pid$pid";

	if(empty($_GET['delete'])) {

		if($post_invalid = checkpost($subject, $message, $isfirstpost && ($special || $sortid))) {
			showmessage($post_invalid, '', array('minpostsize' => $_G['setting']['minpostsize'], 'maxpostsize' => $_G['setting']['maxpostsize']));
		}
		$threadupdatearr = array();
		if(!$isorigauthor && !$_G['group']['allowanonymous']) {
			if($orig['anonymous'] && !$isanonymous) {
				$isanonymous = 0;
				$threadupdatearr['author'] = $orig['author'];
				$anonymousadd = 0;
			} else {
				$isanonymous = $orig['anonymous'];
				$anonymousadd = '';
			}
		} else {
			$threadupdatearr['author'] = $isanonymous ? '' : $orig['author'];
			$anonymousadd = $isanonymous;
		}

		if($isfirstpost) {

			if(trim($subject) == '' && $thread['special'] != 2) {
				showmessage('post_sm_isnull');
			}

			if(!$sortid && !$thread['special'] && trim($message) == '') {
				showmessage('post_sm_isnull');
			}

			$typeid = isset($_G['forum']['threadtypes']['types'][$typeid]) ? $typeid : 0;
			if(!$_G['forum']['ismoderator'] && !empty($_G['forum']['threadtypes']['moderators'][$thread['typeid']])) {
				$typeid = $thread['typeid'];
			}
			$sortid = isset($_G['forum']['threadsorts']['types'][$sortid]) ? $sortid : 0;
			$typeexpiration = intval($_GET['typeexpiration']);

			if(!$typeid && $_G['forum']['threadtypes']['required'] && !$thread['special']) {
				showmessage('post_type_isnull');
			}

			$publishdate = null;
			if ($_G['group']['allowsetpublishdate'] && $thread['displayorder'] == -4) {
				loadcache('cronpublish');
				$cron_publish_ids = dunserialize($_G['cache']['cronpublish']);
				if (!$_GET['cronpublish'] && in_array($_G['tid'], $cron_publish_ids)) {
					unset($cron_publish_ids[$_G['tid']]);
					$cron_publish_ids = serialize($cron_publish_ids);
					savecache('cronpublish', $cron_publish_ids);
				} elseif ($_GET['cronpublish'] && $_GET['cronpublishdate']) {
					$threadupdatearr['dateline'] = $publishdate = strtotime($_GET['cronpublishdate']);
					$_GET['save'] = 1;
					if (!in_array($_G['tid'], $cron_publish_ids)) {
						$cron_publish_ids[$_G['tid']] = $_G['tid'];
						$cron_publish_ids = serialize($cron_publish_ids);
						savecache('cronpublish', $cron_publish_ids);
					}
				}
			}



			$readperm = $_G['group']['allowsetreadperm'] ? intval($readperm) : ($isorigauthor ? 0 : 'ignore');
			if($thread['special'] == 3) {
				$price = $isorigauthor ? ($thread['price'] > 0 && $thread['price'] != $_GET['rewardprice'] ? $_GET['rewardprice'] : 0) : $thread['price'];
			} else {
				$price = intval($_GET['price']);
				$price = $thread['price'] < 0 && !$thread['special']
					?($isorigauthor || !$price ? -1 : $price)
					:($_G['group']['maxprice'] ? ($price <= $_G['group']['maxprice'] ? ($price > 0 ? $price : 0) : $_G['group']['maxprice']) : ($isorigauthor ? $price : $thread['price']));

				if($price > 0 && floor($price * (1 - $_G['setting']['creditstax'])) == 0) {
					showmessage('post_net_price_iszero');
				}
			}

			if($thread['special'] == 1 && ($_G['group']['alloweditpoll'] || $isorigauthor) && !empty($_GET['polls'])) {
				$pollarray = '';
				foreach($_GET['polloption'] as $key => $val) {
					if(trim($val) === '') {
						unset($_GET['polloption'][$key]);
					}
				}
				$pollarray['options'] = $_GET['polloption'];
				if($pollarray['options']) {
					if(count($pollarray['options']) > $_G['setting']['maxpolloptions']) {
						showmessage('post_poll_option_toomany', '', array('maxpolloptions' => $_G['setting']['maxpolloptions']));
					}
					foreach($pollarray['options'] as $key => $value) {
						$pollarray['options'][$key] = censor($pollarray['options'][$key]);
						if(!trim($value)) {
							C::t('forum_polloption')->delete_safe_tid($_G['tid'], $key);
							unset($pollarray['options'][$key]);
						}
					}
					$threadupdatearr['special'] = 1;
					foreach($_GET['displayorder'] as $key => $value) {
						if(preg_match("/^-?\d*$/", $value)) {
							$pollarray['displayorder'][$key] = $value;
						}
					}
					$curpolloption = count($pollarray['options']);
					$pollarray['maxchoices'] = empty($_GET['maxchoices']) ? 0 : ($_GET['maxchoices'] > $curpolloption ? $curpolloption : $_GET['maxchoices']);
					$pollarray['multiple'] = empty($_GET['maxchoices']) || $_GET['maxchoices'] == 1 ? 0 : 1;
					$pollarray['visible'] = empty($_GET['visibilitypoll']);
					$pollarray['expiration'] = $_GET['expiration'];
					$pollarray['overt'] = !empty($_GET['overt']);
					foreach($_GET['polloptionid'] as $key => $value) {
						if(!preg_match("/^\d*$/", $value)) {
							showmessage('submit_invalid');
						}
					}
					$expiration = intval($_GET['expiration']);
					if($close) {
						$pollarray['expiration'] = TIMESTAMP;
					} elseif($expiration) {
						if(empty($pollarray['expiration'])) {
							$pollarray['expiration'] = 0;
						} else {
							$pollarray['expiration'] = TIMESTAMP + 86400 * $expiration;
						}
					}
					$optid = '';
					$query = C::t('forum_polloption')->fetch_all_by_tid($_G['tid']);
					foreach($query as $tempoptid) {
						$optid[] = $tempoptid['polloptionid'];
					}
					foreach($pollarray['options'] as $key => $value) {
						$value = dhtmlspecialchars(trim($value));
						if(in_array($_GET['polloptionid'][$key], $optid)) {
							if($_G['group']['alloweditpoll']) {
								C::t('forum_polloption')->update_safe_tid($_GET['polloptionid'][$key], $_G['tid'], $pollarray['displayorder'][$key], $value);
							} else {
								C::t('forum_polloption')->update_safe_tid($_GET['polloptionid'][$key], $_G['tid'], $pollarray['displayorder'][$key]);
							}
						} else {
							C::t('forum_polloption')->insert(array('tid' => $_G['tid'], 'displayorder' => $pollarray['displayorder'][$key], 'polloption' => $value));
						}
					}
					$polloptionpreview = '';
					$query = C::t('forum_polloption')->fetch_all_by_tid($_G['tid'], 1, 2);
					foreach($query as $option) {
						$polloptvalue = preg_replace("/\[url=(https?){1}:\/\/([^\[\"']+?)\](.+?)\[\/url\]/i", "<a href=\"\\1://\\2\" target=\"_blank\">\\3</a>", $option['polloption']);
						$polloptionpreview .= $polloptvalue."\t";
					}

					$polloptionpreview = daddslashes($polloptionpreview);

					$data = array('multiple' => $pollarray['multiple'], 'visible' => $pollarray['visible'], 'maxchoices' => $pollarray['maxchoices'], 'expiration' => $pollarray['expiration'], 'overt' => $pollarray['overt'], 'pollpreview' => $polloptionpreview);
					C::t('forum_poll')->update($_G['tid'], $data);
				} else {
					$threadupdatearr['special'] = 0;
					C::t('forum_poll')->delete($_G['tid']);
					C::t('forum_polloption')->delete_safe_tid($_G['tid']);
				}

			} elseif($thread['special'] == 3 && $isorigauthor) {

				$rewardprice = intval($_GET['rewardprice']);
				if($thread['price'] > 0 && $thread['price'] != $_GET['rewardprice']) {
					if($rewardprice <= 0){
						showmessage('reward_credits_invalid');
					}
					$addprice = ceil(($rewardprice - $thread['price']) + ($rewardprice - $thread['price']) * $_G['setting']['creditstax']);
					if($rewardprice < $thread['price']) {
						showmessage('reward_credits_fall');
					} elseif($rewardprice < $_G['group']['minrewardprice'] || ($_G['group']['maxrewardprice'] > 0 && $rewardprice > $_G['group']['maxrewardprice'])) {
						showmessage('reward_credits_between', '', array('minrewardprice' => $_G['group']['minrewardprice'], 'maxrewardprice' => $_G['group']['maxrewardprice']));
					} elseif($addprice > getuserprofile('extcredits'.$_G['setting']['creditstransextra'][2])) {
						showmessage('reward_credits_shortage');
					}
					$realprice = ceil($thread['price'] + $thread['price'] * $_G['setting']['creditstax']);

					updatemembercount($thread['authorid'], array($_G['setting']['creditstransextra'][2] => -$addprice));
					C::t('common_credit_log')->update_by_uid_operation_relatedid($thread['authorid'], 'RTC', $_G['tid'], array('extcredits'.$_G['setting']['creditstransextra'][2] => $realprice));
				}

				if(!$_G['forum']['ismoderator']) {
					if($thread['replies'] > 1) {
						$subject = addslashes($thread['subject']);
					}
				}

				$price = $rewardprice;

			} elseif($thread['special'] == 4 && $_G['group']['allowpostactivity']) {

				$activitytime = intval($_GET['activitytime']);
				if(empty($_GET['starttimefrom'][$activitytime])) {
					showmessage('activity_fromtime_please');
				} elseif(strtotime($_GET['starttimefrom'][$activitytime]) === -1 || @strtotime($_GET['starttimefrom'][$activitytime]) === FALSE) {
					showmessage('activity_fromtime_error');
				} elseif($activitytime && ((@strtotime($_GET['starttimefrom']) > @strtotime($_GET['starttimeto']) || !$_GET['starttimeto']))) {
					showmessage('activity_fromtime_error');
				} elseif(!trim($_GET['activityclass'])) {
					showmessage('activity_sort_please');
				} elseif(!trim($_GET['activityplace'])) {
					showmessage('activity_address_please');
				} elseif(trim($_GET['activityexpiration']) && (@strtotime($_GET['activityexpiration']) === -1 || @strtotime($_GET['activityexpiration']) === FALSE)) {
					showmessage('activity_totime_error');
				}

				$activity = array();
				$activity['class'] = censor(dhtmlspecialchars(trim($_GET['activityclass'])));
				$activity['starttimefrom'] = @strtotime($_GET['starttimefrom'][$activitytime]);
				$activity['starttimeto'] = $activitytime ? @strtotime($_GET['starttimeto']) : 0;
				$activity['place'] = censor(dhtmlspecialchars(trim($_GET['activityplace'])));
				$activity['cost'] = intval($_GET['cost']);
				$activity['gender'] = intval($_GET['gender']);
				$activity['number'] = intval($_GET['activitynumber']);
				if($_GET['activityexpiration']) {
					$activity['expiration'] = @strtotime($_GET['activityexpiration']);
				} else {
					$activity['expiration'] = 0;
				}
				$extfield = $_GET['extfield'];
				$extfield = explode("\n", $_GET['extfield']);
				foreach($extfield as $key => $value) {
					$extfield[$key] = censor(trim($value));
					if($extfield[$key] === '' || is_numeric($extfield[$key])) {
						unset($extfield[$key]);
					}
				}
				$extfield = array_unique($extfield);
				if(count($extfield) > $_G['setting']['activityextnum']) {
					showmessage('post_activity_extfield_toomany', '', array('maxextfield' => $_G['setting']['activityextnum']));
				}
				$activity['ufield'] = array('userfield' => $_GET['userfield'], 'extfield' => $extfield);
				$activity['ufield'] = serialize($activity['ufield']);
				if(intval($_GET['activitycredit']) > 0) {
					$activity['credit'] = intval($_GET['activitycredit']);
				}
				$data = array('cost' => $activity['cost'], 'starttimefrom' => $activity['starttimefrom'], 'starttimeto' => $activity['starttimeto'], 'place' => $activity['place'], 'class' => $activity['class'], 'gender' => $activity['gender'], 'number' => $activity['number'], 'expiration' => $activity['expiration'], 'ufield' => $activity['ufield'], 'credit' => $activity['credit']);
				C::t('forum_activity')->update($_G['tid'], $data);

			} elseif($thread['special'] == 5 && $_G['group']['allowpostdebate']) {

				if(empty($_GET['affirmpoint']) || empty($_GET['negapoint'])) {
					showmessage('debate_position_nofound');
				} elseif(!empty($_GET['endtime']) && (!($endtime = @strtotime($_GET['endtime'])) || $endtime < TIMESTAMP)) {
					showmessage('debate_endtime_invalid');
				} elseif(!empty($_GET['umpire'])) {
					if(!C::t('common_member')->fetch_uid_by_username($_GET['umpire'])) {
						$_GET['umpire'] = dhtmlspecialchars($_GET['umpire']);
						showmessage('debate_umpire_invalid');
					}
				}
				$affirmpoint = censor(dhtmlspecialchars($_GET['affirmpoint']));
				$negapoint = censor(dhtmlspecialchars($_GET['negapoint']));
				C::t('forum_debate')->update($_G['tid'], array('affirmpoint' => $affirmpoint, 'negapoint' => $negapoint, 'endtime' => $endtime, 'umpire' => $_GET['umpire']));

			} elseif($specialextra) {

				@include_once DISCUZ_ROOT.'./source/plugin/'.$_G['setting']['threadplugins'][$specialextra]['module'].'.class.php';
				$classname = 'threadplugin_'.$specialextra;
				if(class_exists($classname) && method_exists($threadpluginclass = new $classname, 'editpost_submit')) {
					$threadpluginclass->editpost_submit($_G['fid'], $_G['tid']);
				}

			}

			$_G['forum_optiondata'] = array();
			if($_G['forum']['threadsorts']['types'][$sortid] && $_G['forum_checkoption']) {
				$_G['forum_optiondata'] = threadsort_validator($_GET['typeoption'], $pid);
			}

			$threadimageaid = 0;
			$threadimage = array();

			if($_G['forum']['threadsorts']['types'][$sortid] && $_G['forum_optiondata'] && is_array($_G['forum_optiondata'])) {
				$sql = $separator = $filedname = $valuelist = '';
				foreach($_G['forum_optiondata'] as $optionid => $value) {
					$value = censor($value);
					if($_G['forum_optionlist'][$optionid]['type'] == 'image') {
						$identifier = $_G['forum_optionlist'][$optionid]['identifier'];
						$newsortaid = intval($_GET['typeoption'][$identifier]['aid']);
						if($newsortaid && $_GET['oldsortaid'][$identifier] && $newsortaid != $_GET['oldsortaid'][$identifier]) {
							$attach = C::t('forum_attachment_n')->fetch('tid:'.$_G['tid'], $_GET['oldsortaid'][$identifier]);
							C::t('forum_attachment')->delete($_GET['oldsortaid'][$identifier]);
							C::t('forum_attachment_n')->delete('tid:'.$_G['tid'], $_GET['oldsortaid'][$identifier]);
							dunlink($attach);
							$threadimageaid = $newsortaid;
							convertunusedattach($newsortaid, $_G['tid'], $pid);
						}
					}
					if($_G['forum_optionlist'][$optionid]['unchangeable']) {
						continue;
					}
					if(($_G['forum_optionlist'][$optionid]['search'] || in_array($_G['forum_optionlist'][$optionid]['type'], array('radio', 'select', 'number'))) && $value) {
						$filedname .= $separator.$_G['forum_optionlist'][$optionid]['identifier'];
						$valuelist .= $separator."'$value'";
						$sql .= $separator.$_G['forum_optionlist'][$optionid]['identifier']."='$value'";
						$separator = ' ,';
					}
					C::t('forum_typeoptionvar')->update_by_tid($_G['tid'], array('value' => $value, 'sortid' => $sortid), false, false, $optionid);
				}

				if($typeexpiration) {
					C::t('forum_typeoptionvar')->update_by_tid($_G['tid'], array('expiration' => (TIMESTAMP + $typeexpiration)), false, false, null, $sortid);
				}

				if($sql || ($filedname && $valuelist)) {
					if(C::t('forum_optionvalue')->fetch_all_tid($sortid, "WHERE tid='$_G[tid]'")) {
						if($sql) {
							C::t('forum_optionvalue')->update($sortid, $_G['tid'], $_G['fid'], $sql);
						}
					} elseif($filedname && $valuelist) {
						C::t('forum_optionvalue')->insert($sortid, "($filedname, tid, fid) VALUES ($valuelist, '$_G[tid]', '$_G[fid]')");
					}
				}
			}

			$thread['status'] = setstatus(4, $_GET['ordertype'], $thread['status']);

			$thread['status'] = setstatus(2, $_GET['hiddenreplies'], $thread['status']);

			$thread['status'] = setstatus(6, $_GET['allownoticeauthor'] ? 1 : 0, $thread['status']);

			$displayorder = empty($_GET['save']) ? ($thread['displayorder'] == -4 ? -4 : $thread['displayorder']) : -4;

			if($isorigauthor && $_G['group']['allowreplycredit']) {
				$_POST['replycredit_extcredits'] = intval($_POST['replycredit_extcredits']);
				$_POST['replycredit_times'] = intval($_POST['replycredit_times']);
				$_POST['replycredit_membertimes'] = intval($_POST['replycredit_membertimes']) > 0 ? intval($_POST['replycredit_membertimes']) : 1;
				$_POST['replycredit_random'] = intval($_POST['replycredit_random']) < 0 || intval($_POST['replycredit_random']) > 99 ? 0 : intval($_POST['replycredit_random']) ;
				if($_POST['replycredit_extcredits'] > 0 && $_POST['replycredit_times'] > 0) {
					$replycredit = $_POST['replycredit_extcredits'] * $_POST['replycredit_times'];
					$replycredit_diff =  $replycredit - $thread['replycredit'];
					if($replycredit_diff > 0) {
						$replycredit_diff = ceil($replycredit_diff + ($replycredit_diff * $_G['setting']['creditstax']));
						if(!$replycredit_rule) {
							$replycredit_rule = array();
							if($_G['setting']['creditstransextra']['10']) {
								$replycredit_rule['extcreditstype'] = $_G['setting']['creditstransextra']['10'];
							}
						}

						if($replycredit_diff > getuserprofile('extcredits'.$replycredit_rule['extcreditstype'])) {
							showmessage('post_edit_thread_replaycredit_nocredit');
						}
					}

					if($replycredit_diff) {
						updatemembercount($_G['uid'], array($replycredit_rule['extcreditstype'] => ($replycredit_diff > 0 ? -$replycredit_diff : abs($replycredit_diff))), 1, ($replycredit_diff > 0 ? 'RCT' : 'RCB'), $_G['tid']);
					}
				} elseif(($_POST['replycredit_extcredits'] == 0 || $_POST['replycredit_times'] == 0) && $thread['replycredit'] > 0) {
					$replycredit = 0;
					C::t('forum_replycredit')->delete($_G['tid']);
					updatemembercount($thread['authorid'], array($replycredit_rule['extcreditstype'] => $thread['replycredit']), 1, 'RCB', $_G['tid']);
					$threadupdatearr['replycredit'] = 0;
				} else {
					$replycredit = $thread['replycredit'];
				}
				if($replycredit) {
					$threadupdatearr['replycredit'] = $replycredit;
					$replydata = array(
							'tid' => $_G['tid'],
							'extcredits' => $_POST['replycredit_extcredits'],
							'extcreditstype' => $replycredit_rule['extcreditstype'],
							'times' => $_POST['replycredit_times'],
							'membertimes' => $_POST['replycredit_membertimes'],
							'random' => $_POST['replycredit_random']
						);
					C::t('forum_replycredit')->insert($replydata, false, true);
				}
			}

			if($rushreply) {
				$_GET['rushreplyfrom'] = strtotime($_GET['rushreplyfrom']);
				$_GET['rushreplyto'] = strtotime($_GET['rushreplyto']);
				$_GET['rewardfloor'] = trim($_GET['rewardfloor']);
				$_GET['stopfloor'] = intval($_GET['stopfloor']);
				$_GET['creditlimit'] = $_GET['creditlimit'] == '' ? '-996' : intval($_GET['creditlimit']);
				if($_GET['rushreplyfrom'] > $_GET['rushreplyto'] && !empty($_GET['rushreplyto'])) {
					showmessage('post_rushreply_timewrong');
				}
				$maxposition = C::t('forum_post')->fetch_maxposition_by_tid($thread['posttableid'], $_G['tid']);
				if($thread['closed'] == 1 && ((!$_GET['rushreplyfrom'] && !$_GET['rushreplyto']) || ($_GET['rushreplyfrom'] < $_G['timestamp'] && $_GET['rushreplyto'] > $_G['timestamp']) || (!$_GET['rushreplyfrom'] && $_GET['rushreplyto'] > $_G['timestamp']) || ($_GET['stopfloor'] && $_GET['stopfloor'] > $maxposition) )) {
					$threadupdatearr['closed'] = 0;
				} elseif($thread['closed'] == 0 && (($_GET['rushreplyfrom'] && $_GET['rushreplyfrom'] > $_G['timestamp']) || ($_GET['rushreplyto'] && $_GET['rushreplyto'] && $_GET['rushreplyto'] < $_G['timestamp']) || ($_GET['stopfloor'] && $_GET['stopfloor'] <= $maxposition) )) {
					$threadupdatearr['closed'] = 1;
				}
				if(!empty($_GET['rewardfloor']) && !empty($_GET['stopfloor'])) {
					$floors = explode(',', $_GET['rewardfloor']);
					if(!empty($floors)) {
						foreach($floors AS $key => $floor) {
							if(strpos($floor, '*') === false) {
								if(intval($floor) == 0) {
									unset($floors[$key]);
								} elseif($floor > $_GET['stopfloor']) {
									unset($floors[$key]);
								}
							}
						}
					}
					$_GET['rewardfloor'] = implode(',', $floors);
				}
				$rushdata = array('stopfloor' => $_GET['stopfloor'], 'starttimefrom' => $_GET['rushreplyfrom'], 'starttimeto' => $_GET['rushreplyto'], 'rewardfloor' => $_GET['rewardfloor'], 'creditlimit' => $_GET['creditlimit']);
				C::t('forum_threadrush')->update($_G['tid'], $rushdata);
			}
			$threadupdatearr['typeid'] = $typeid;
			$threadupdatearr['sortid'] = $sortid;
			$threadupdatearr['subject'] = $subject;
			if($readperm !== 'ignore') {
				$threadupdatearr['readperm'] = $readperm;
			}
			$threadupdatearr['price'] = $price;
			$threadupdatearr['status'] = $thread['status'];
			if($_G['forum_auditstatuson'] && $audit == 1) {
				$threadupdatearr['displayorder'] = 0;
				$threadupdatearr['moderated'] = 1;
			} else {
				$threadupdatearr['displayorder'] = $displayorder;
			}
			C::t('forum_thread')->update($_G['tid'], $threadupdatearr, true);

			if($_G['tid'] > 1) {
				if($_G['thread']['closed'] > 1) {
					C::t('forum_thread')->update($_G['thread']['closed'], array('subject' => $subject), true);
				} elseif(empty($_G['thread']['isgroup'])) {
					$threadclosed = C::t('forum_threadclosed')->fetch($_G['tid']);
					if($threadclosed['redirect']) {
						C::t('forum_thread')->update($threadclosed['redirect'], array('subject' => $subject), true);
					}
				}
			}
			$class_tag = new tag();
			$tagstr = $class_tag->update_field($_GET['tags'], $_G['tid'], 'tid', $_G['thread']);

		} else {

			if($subject == '' && $message == '' && $thread['special'] != 2) {
				showmessage('post_sm_isnull');
			}

		}

		$htmlon = $_G['group']['allowhtml'] && !empty($_GET['htmlon']) ? 1 : 0;

		if($_G['setting']['editedby'] && (TIMESTAMP - $orig['dateline']) > 60 && $_G['adminid'] != 1) {
			$editor = $isanonymous && $isorigauthor ? lang('forum/misc', 'anonymous') : $_G['username'];
			$edittime = dgmdate(TIMESTAMP);
			$message = lang('forum/misc', $htmlon ? 'post_edithtml' : (!$_G['forum']['allowbbcode'] || $_GET['bbcodeoff'] ? 'post_editnobbcode' : 'post_edit'), array('editor' => $editor, 'edittime' => $edittime)) . $message;
		}

		if($_G['group']['allowat']) {
			$atlist = $atlist_tmp = $ateduids = array();
			$atnum = $maxselect = 0;
			foreach(C::t('home_notification')->fetch_all_by_authorid_fromid($_G['uid'], $_G['tid'], 'at') as $row) {
				$atnum ++;
				$ateduids[$row[uid]] = $row['uid'];
			}
			$maxselect = $_G['group']['allowat'] - $atnum;
			preg_match_all("/@([^\r\n]*?)\s/i", $message.' ', $atlist_tmp);
			$atlist_tmp = array_slice(array_unique($atlist_tmp[1]), 0, $_G['group']['allowat']);
			if($maxselect > 0 && !empty($atlist_tmp)) {
				if(empty($_G['setting']['at_anyone'])) {
					foreach(C::t('home_follow')->fetch_all_by_uid_fusername($_G['uid'], $atlist_tmp) as $row) {
						if(!in_array($row['followuid'], $ateduids)) {
							$atlist[$row[followuid]] = $row['fusername'];
						}
						if(count($atlist) == $maxselect) {
							break;
						}
					}
					if(count($atlist) < $maxselect) {
						$query = C::t('home_friend')->fetch_all_by_uid_username($_G['uid'], $atlist_tmp);
						foreach($query as $row) {
							if(!in_array($row['followuid'], $ateduids)) {
								$atlist[$row[fuid]] = $row['fusername'];
							}
						}
					}
				} else {
					foreach(C::t('common_member')->fetch_all_by_username($atlist_tmp) as $row) {
						if(!in_array($row['uid'], $ateduids)) {
							$atlist[$row[uid]] = $row['username'];
						}
						if(count($atlist) == $maxselect) {
							break;
						}
					}
				}
				if($atlist) {
					foreach($atlist as $atuid => $atusername) {
						$atsearch[] = "/@".str_replace('/', '\/', preg_quote($atusername))." /i";
						$atreplace[] = "[url=home.php?mod=space&uid=$atuid]@{$atusername}[/url] ";
					}
					$message = preg_replace($atsearch, $atreplace, $message.' ', 1);
				}
			}
		}
		$bbcodeoff = checkbbcodes($message, !empty($_GET['bbcodeoff']));
		$smileyoff = checksmilies($message, !empty($_GET['smileyoff']));
		$tagoff = $isfirstpost ? !empty($tagoff) : 0;
		$attachupdate = !empty($_GET['delattachop']) || ($_G['group']['allowpostattach'] || $_G['group']['allowpostimage']) && ($_GET['attachnew'] || $special == 2 && $_GET['tradeaid'] || $special == 4 && $_GET['activityaid'] || $isfirstpost && $sortid);

		if($attachupdate) {
			updateattach($thread['displayorder'] == -4 || $_G['forum_auditstatuson'], $_G['tid'], $pid, $_GET['attachnew'], $_GET['attachupdate'], $orig['authorid']);
		}

		if($special == 2 && $_G['group']['allowposttrade']) {

			if($trade = C::t('forum_trade')->fetch_goods($_G['tid'], $pid)) {
				$seller = empty($_GET['paymethod']) && $_GET['seller'] ? censor(dhtmlspecialchars(trim($_GET['seller']))) : '';
				$item_name = censor(dhtmlspecialchars(trim($_GET['item_name'])));
				$item_price = floatval($_GET['item_price']);
				$item_credit = intval($_GET['item_credit']);
				$item_locus = censor(dhtmlspecialchars(trim($_GET['item_locus'])));
				$item_number = intval($_GET['item_number']);
				$item_quality = intval($_GET['item_quality']);
				$item_transport = intval($_GET['item_transport']);
				$postage_mail = intval($_GET['postage_mail']);
				$postage_express = intval(trim($_GET['postage_express']));
				$postage_ems = intval($_GET['postage_ems']);
				$item_type = intval($_GET['item_type']);
				$item_costprice = floatval($_GET['item_costprice']);

				if(!trim($item_name)) {
					showmessage('trade_please_name');
				} elseif($_G['group']['maxtradeprice'] && $item_price > 0 && ($_G['group']['mintradeprice'] > $item_price || $_G['group']['maxtradeprice'] < $item_price)) {
					showmessage('trade_price_between', '', array('mintradeprice' => $_G['group']['mintradeprice'], 'maxtradeprice' => $_G['group']['maxtradeprice']));
				} elseif($_G['group']['maxtradeprice'] && $item_credit > 0 && ($_G['group']['mintradeprice'] > $item_credit || $_G['group']['maxtradeprice'] < $item_credit)) {
					showmessage('trade_credit_between', '', array('mintradeprice' => $_G['group']['mintradeprice'], 'maxtradeprice' => $_G['group']['maxtradeprice']));
				} elseif(!$_G['group']['maxtradeprice'] && $item_price > 0 && $_G['group']['mintradeprice'] > $item_price) {
					showmessage('trade_price_more_than', '', array('mintradeprice' => $_G['group']['mintradeprice']));
				} elseif(!$_G['group']['maxtradeprice'] && $item_credit > 0 && $_G['group']['mintradeprice'] > $item_credit) {
					showmessage('trade_credit_more_than', '', array('mintradeprice' => $_G['group']['mintradeprice']));
				} elseif($item_price <= 0 && $item_credit <= 0) {
					showmessage('trade_pricecredit_need');
				} elseif($item_number < 1) {
					showmessage('tread_please_number');
				}

				if($trade['aid'] && $_GET['tradeaid'] && $trade['aid'] != $_GET['tradeaid']) {
					$attach = C::t('forum_attachment_n')->fetch('tid:'.$_G['tid'], $trade['aid']);
					C::t('forum_attachment')->delete($trade['aid']);
					C::t('forum_attachment_n')->delete('tid:'.$_G['tid'], $trade['aid']);
					dunlink($attach);
					$threadimageaid = $_GET['tradeaid'];
					convertunusedattach($_GET['tradeaid'], $_G['tid'], $pid);
				}

				$expiration = $_GET['item_expiration'] ? @strtotime($_GET['item_expiration']) : 0;
				$closed = $expiration > 0 && @strtotime($_GET['item_expiration']) < TIMESTAMP ? 1 : $closed;

				switch($_GET['transport']) {
					case 'seller':$item_transport = 1;break;
					case 'buyer':$item_transport = 2;break;
					case 'virtual':$item_transport = 3;break;
					case 'logistics':$item_transport = 4;break;
				}
				if(!$item_price || $item_price <= 0) {
					$item_price = $postage_mail = $postage_express = $postage_ems = '';
				}

				$data = array('aid' => $_GET['tradeaid'], 'account' => $seller, 'tenpayaccount' => $_GET['tenpay_account'], 'subject' => $item_name, 'price' => $item_price, 'amount' => $item_number, 'quality' => $item_quality, 'locus' => $item_locus, 'transport' => $item_transport, 'ordinaryfee' => $postage_mail, 'expressfee' => $postage_express, 'emsfee' => $postage_ems, 'itemtype' => $item_type, 'expiration' => $expiration, 'closed' => $closed, 'costprice' => $item_costprice, 'credit' => $item_credit, 'costcredit' => $_GET['item_costcredit']);
				C::t('forum_trade')->update($_G['tid'], $pid, $data);
				if(!empty($_GET['infloat'])) {
					$viewpid = C::t('forum_post')->fetch_threadpost_by_tid_invisible($_G['tid']);
					$viewpid = $viewpid['pid'];
					$redirecturl = "forum.php?mod=viewthread&tid=$_G[tid]&viewpid=$viewpid#pid$viewpid";
				} else {
					$redirecturl = "forum.php?mod=viewthread&do=tradeinfo&tid=$_G[tid]&pid=$pid";
				}
			}

		}

		if($special == 4 && $isfirstpost && $_G['group']['allowpostactivity']) {
			$activity = C::t('forum_activity')->fetch($_G['tid']);
			$activityaid = $activity['aid'];
			if($activityaid && $activityaid != $_GET['activityaid']) {
				$attach = C::t('forum_attachment_n')->fetch('tid:'.$_G['tid'], $activityaid);
				C::t('forum_attachment')->delete($activityaid);
				C::t('forum_attachment_n')->delete('tid:'.$_G['tid'], $activityaid);
				dunlink($attach);
			}
			if($_GET['activityaid']) {
				$threadimageaid = $_GET['activityaid'];
				convertunusedattach($_GET['activityaid'], $_G['tid'], $pid);
				C::t('forum_activity')->update($_G['tid'], array('aid' => $_GET['activityaid']));
			}
		}

		if($isfirstpost && $attachupdate) {
			if(!$threadimageaid) {
				$threadimage = C::t('forum_attachment_n')->fetch_max_image('tid:'.$_G['tid'], 'pid', $pid);
				$threadimageaid = $threadimage['aid'];
			}

			if($_G['forum']['picstyle']) {
				if(empty($thread['cover'])) {
					setthreadcover($pid, 0, $threadimageaid);
				} else {
					setthreadcover($pid, $_G['tid'], 0, 1);
				}
			}

			if($threadimageaid) {
				if(!$threadimage) {
					$threadimage = C::t('forum_attachment_n')->fetch_max_image('tid:'.$_G['tid'], 'tid', $_G['tid']);
				}
				C::t('forum_threadimage')->delete_by_tid($_G['tid']);
				C::t('forum_threadimage')->insert(array(
					'tid' => $_G['tid'],
					'attachment' => $threadimage['attachment'],
					'remote' => $threadimage['remote'],
				));
			}
		}

		$feed = array();
		if($special == 127) {
			$message .= chr(0).chr(0).chr(0).$specialextra;
		}

		if($_G['forum_auditstatuson'] && $audit == 1) {
			C::t('forum_post')->update($thread['posttableid'], $pid, array('status' => 4), false, false, null, -2, null, 0);
			updatepostcredits('+', $orig['authorid'], ($isfirstpost ? 'post' : 'reply'), $_G['fid']);
			updatemodworks('MOD', 1);
			updatemodlog($_G['tid'], 'MOD');
		}

		$displayorder = $pinvisible = 0;
		if($isfirstpost) {
			$displayorder = $modnewthreads ? -2 : $thread['displayorder'];
			$pinvisible = $modnewthreads ? -2 : (empty($_GET['save']) ? 0 : -3);
		} else {
			$pinvisible = $modnewreplies ? -2 : ($thread['displayorder'] == -4 ? -3 : 0);
		}

		$message = preg_replace('/\[attachimg\](\d+)\[\/attachimg\]/is', '[attach]\1[/attach]', $message);
		$parseurloff = !empty($_GET['parseurloff']);
		$setarr = array(
			'message' => $message,
			'usesig' => $_GET['usesig'],
			'htmlon' => $htmlon,
			'bbcodeoff' => $bbcodeoff,
			'parseurloff' => $parseurloff,
			'smileyoff' => $smileyoff,
			'subject' => $subject,
			'tags' => $tagstr
		);
		if($anonymousadd !== '') {
			$setarr['anonymous'] = $anonymousadd;
		}
		if($publishdate) {
			$setarr['dateline'] = $publishdate;
		}
		if($_G['forum_auditstatuson'] && $audit == 1) {
			$setarr['invisible'] = 0;
		} else {
			$setarr['invisible'] = $pinvisible;
		}
		C::t('forum_post')->update('tid:'.$_G['tid'], $pid, $setarr);
		if($_G['group']['allowat'] && $atlist) {
			foreach($atlist as $atuid => $atusername) {
				notification_add($atuid, 'at', 'at_message', array('from_id' => $_G['tid'], 'from_idtype' => 'at', 'buyerid' => $_G['uid'], 'buyer' => $_G['username'], 'tid' => $_G['tid'], 'subject' => $thread['subject'], 'pid' => $pid, 'message' => messagecutstr($message, 150)));
			}
			set_atlist_cookie(array_keys($atlist));
		}
		$_G['forum']['lastpost'] = explode("\t", $_G['forum']['lastpost']);

		if($orig['dateline'] == $_G['forum']['lastpost'][2] && ($orig['author'] == $_G['forum']['lastpost'][3] || ($_G['forum']['lastpost'][3] == '' && $orig['anonymous']))) {
			$lastpost = "$_G[tid]\t".($isfirstpost ? $subject : $thread['subject'])."\t$orig[dateline]\t".($isanonymous ? '' : $orig['author']);
			C::t('forum_forum')->update($_G['fid'], array('lastpost' => $lastpost));

		}

		if(!$_G['forum_auditstatuson'] || $audit != 1) {
			if($isfirstpost && $modnewthreads) {
				C::t('forum_thread')->update($_G['tid'], array('displayorder' => -2));
				manage_addnotify('verifythread');
			} elseif(!$isfirstpost && $modnewreplies) {
				C::t('forum_thread')->increase($_G['tid'], array('replies' => -1));
				manage_addnotify('verifypost');
			}
			if($modnewreplies || $modnewthreads) {
				C::t('forum_forum')->update($_G['fid'], array('modworks' => '1'));
			}
		}
		if($isfirstpost) {
			require_once libfile('function/discuzcode');
			require_once libfile('function/followcode');
			$feed = C::t('forum_threadpreview')->fetch($_G['tid']);
			if($feed) {
				C::t('forum_threadpreview')->update($_G['tid'], array('content' => followcode($message, $_G['tid'], $pid, 1000)));
			}
		}
		if($thread['lastpost'] == $orig['dateline'] && ((!$orig['anonymous'] && $thread['lastposter'] == $orig['author']) || ($orig['anonymous'] && $thread['lastposter'] == '')) && $orig['anonymous'] != $isanonymous) {
			C::t('forum_thread')->update($_G['tid'], array('lastposter' => $isanonymous ? '' : $orig['author']), true);
		}

		if(!$isorigauthor) {
			updatemodworks('EDT', 1);
			require_once libfile('function/misc');
			modlog($thread, 'EDT');
		}

		if($isfirstpost && $thread['displayorder'] == -4 && empty($_GET['save'])) {
			threadpubsave($thread['tid']);
		}

	} else {

		if(!$_G['setting']['editperdel']) {
			showmessage('post_edit_thread_ban_del', NULL);
		}

		if($isfirstpost && $thread['replies'] > 0) {
			showmessage(($thread['special'] == 3 ? 'post_edit_reward_already_reply' : 'post_edit_thread_already_reply'), NULL);
		}

		if($thread['special'] == 3) {
			if($thread['price'] < 0 && ($thread['dateline'] + 1 == $orig['dateline'])) {
				showmessage('post_edit_reward_nopermission', NULL);
			}
		}

		if($rushreply) {
			showmessage('post_edit_delete_rushreply_nopermission', NULL);
		}

		if($thread['displayorder'] >= 0) {
			updatepostcredits('-', $orig['authorid'], ($isfirstpost ? 'post' : 'reply'), $_G['fid']);
		}

		if($thread['special'] == 3 && $isfirstpost) {
			updatemembercount($orig['authorid'], array($_G['setting']['creditstransextra'][2] => $thread['price']));
			C::t('common_credit_log')->delete_by_uid_operation_relatedid($thread['authorid'], 'RTC', $_G['tid']);
		}

		if($thread['replycredit'] && $isfirstpost && !$isanonymous) {
			updatemembercount($orig['authorid'], array($replycredit_rule['extcreditstype'] => $thread['replycredit']), true, 'RCB', $_G['tid']);
			C::t('forum_replycredit')->delete($_G['tid']);
		} elseif (!$isfirstpost && !$isanonymous) {
			$postreplycredit = C::t('forum_post')->fetch('tid:'.$_G['tid'], $pid);
			$postreplycredit = $postreplycredit['replycredit'];
			if($postreplycredit) {
				C::t('forum_post')->update('tid:'.$_G['tid'], $pid, array('replycredit' => 0));
				updatemembercount($orig['authorid'], array($replycredit_rule['extcreditstype'] => '-'.$postreplycredit));
			}
		}


		$thread_attachment = $post_attachment = 0;
		foreach(C::t('forum_attachment_n')->fetch_all_by_id('tid:'.$_G['tid'], 'tid', $_G['tid']) as $attach) {
			if($attach['pid'] == $pid) {
				if($thread['displayorder'] >= 0) {
					$post_attachment++;
				}
				dunlink($attach);
			} else {
				$thread_attachment = 1;
			}
		}

		if($post_attachment) {
			C::t('forum_attachment')->delete_by_id('pid', $pid);
			DB::query("DELETE FROM ".DB::table(getattachtablebytid($_G['tid']))." WHERE pid='$pid'", 'UNBUFFEREED');
			updatecreditbyaction('postattach', $orig['authorid'], array(),  '', -$post_attachment);
		}

		C::t('forum_post')->delete('tid:'.$_G['tid'], $pid);
		C::t('forum_postcomment')->delete_by_rpid($pid);
		if($thread['special'] == 2) {
			C::t('forum_trade')->delete_by_id_idtype($pid, 'pid');
		}
		$forumcounter = array();
		if($isfirstpost) {
			$forumcounter['threads'] = $forumcounter['posts'] = -1;
			$tablearray = array('forum_relatedthread', 'forum_debate', 'forum_debatepost', 'forum_polloption', 'forum_poll');
			foreach ($tablearray as $table) {
				DB::query("DELETE FROM ".DB::table($table)." WHERE tid='$_G[tid]'", 'UNBUFFERED');
			}
			C::t('forum_thread')->delete_by_tid($_G['tid']);
			C::t('common_moderate')->delete($_G['tid'], 'tid');
			C::t('forum_threadmod')->delete_by_tid($_G['tid']);
			C::t('forum_typeoptionvar')->delete_by_tid($_G['tid']);
			if($_G['setting']['globalstick'] && in_array($thread['displayorder'], array(2, 3))) {
				require_once libfile('function/cache');
				updatecache('globalstick');
			}
		} else {
			$forumcounter['posts'] = -1;
			$lastpost = C::t('forum_post')->fetch_visiblepost_by_tid('tid:'.$_G['tid'], $_G['tid'], 0, 1);
			$lastpost['author'] = !$lastpost['anonymous'] ? addslashes($lastpost['author']) : '';
			$updatefieldarr = array(
				'replies' => -1,
				'attachment' => array($thread_attachment),
				'lastposter' => array($lastpost['author']),
				'lastpost' => array($lastpost['dateline'])
			);
			C::t('forum_thread')->increase($_G['tid'], $updatefieldarr);
		}

		$_G['forum']['lastpost'] = explode("\t", $_G['forum']['lastpost']);
		if($orig['dateline'] == $_G['forum']['lastpost'][2] && ($orig['author'] == $_G['forum']['lastpost'][3] || ($_G['forum']['lastpost'][3] == '' && $orig['anonymous']))) {
			$lastthread = C::t('forum_thread')->fetch_by_fid_displayorder($_G['fid']);
			C::t('forum_forum')->update($_G['fid'], array('lastpost' => "$lastthread[tid]\t$lastthread[subject]\t$lastthread[lastpost]\t$lastthread[lastposter]"));
		}
		C::t('forum_forum')->update_forum_counter($_G['fid'], $forumcounter['threads'], $forumcounter['posts']);

	}

	if($specialextra) {

		@include_once DISCUZ_ROOT.'./source/plugin/'.$_G['setting']['threadplugins'][$specialextra]['module'].'.class.php';
		$classname = 'threadplugin_'.$specialextra;
		if(class_exists($classname) && method_exists($threadpluginclass = new $classname, 'editpost_submit_end')) {
			$threadpluginclass->editpost_submit_end($_G['fid'], $_G['tid']);
		}

	}

	if($_G['forum']['threadcaches']) {
		deletethreadcaches($_G['tid']);
	}

	$param = array('fid' => $_G['fid'], 'tid' => $_G['tid'], 'pid' => $pid);

	dsetcookie('clearUserdata', 'forum');

	if($_G['forum_auditstatuson']) {
		if($audit == 1) {
			updatemoderate($isfirstpost ? 'tid' : 'pid', $isfirstpost ? $_G['tid'] : $pid, '2');
			showmessage('auditstatuson_succeed', $redirecturl, $param);
		} else {
			updatemoderate($isfirstpost ? 'tid' : 'pid', $isfirstpost ? $_G['tid'] : $pid);
			showmessage('audit_edit_succeed', '', $param, array('alert' => 'right'));
		}
	} else {
		if(!empty($_GET['delete']) && $isfirstpost) {
			showmessage('post_edit_delete_succeed', "forum.php?mod=forumdisplay&fid=$_G[fid]", $param);
		} elseif(!empty($_GET['delete'])) {
			showmessage('post_edit_delete_succeed', "forum.php?mod=viewthread&tid=$_G[tid]&page=$_GET[page]&extra=$extra".($vid && $isfirstpost ? "&vid=$vid" : ''), $param);
		} else {
			if($isfirstpost && $modnewthreads) {
				C::t('forum_post')->update($thread['posttableid'], $pid, array('status' => 4), false, false, null, -2, null, 0);
				updatemoderate('tid', $_G['tid']);
				showmessage('edit_newthread_mod_succeed', $redirecturl, $param);
			} elseif(!$isfirstpost && $modnewreplies) {
				C::t('forum_post')->update($thread['posttableid'], $pid, array('status' => 4), false, false, null, -2, null, 0);
				updatemoderate('pid', $pid);
				showmessage('edit_reply_mod_succeed', "forum.php?mod=forumdisplay&fid=$_G[fid]", $param);
			} else {
				showmessage('post_edit_succeed', $redirecturl, $param);
			}
		}
	}

}

?>