/*
	[Discuz!] (C)2001-2099 Comsenz Inc.
	This is NOT a freeware, use is subject to license terms

	$Id: forum_viewthread.js 28794 2012-03-13 05:39:46Z zhangguosheng $
*/

var replyreload = '', attachimgST = new Array(), zoomgroup = new Array(), zoomgroupinit = new Array();

function attachimggroup(pid) {
	if(!zoomgroupinit[pid]) {
		for(i = 0;i < aimgcount[pid].length;i++) {
			zoomgroup['aimg_' + aimgcount[pid][i]] = pid;
		}
		zoomgroupinit[pid] = true;
	}
}

function attachimgshow(pid, onlyinpost) {
	onlyinpost = !onlyinpost ? false : onlyinpost;
	aimgs = aimgcount[pid];
	aimgcomplete = 0;
	loadingcount = 0;
	for(i = 0;i < aimgs.length;i++) {
		obj = $('aimg_' + aimgs[i]);
		if(!obj) {
			aimgcomplete++;
			continue;
		}
		if(onlyinpost && obj.getAttribute('inpost') || !onlyinpost) {
			if(!obj.status) {
				obj.status = 1;
				if(obj.getAttribute('file')) obj.src = obj.getAttribute('file');
				loadingcount++;
			} else if(obj.status == 1) {
				if(obj.complete) {
					obj.status = 2;
				} else {
					loadingcount++;
				}
			} else if(obj.status == 2) {
				aimgcomplete++;
				if(obj.getAttribute('thumbImg')) {
					thumbImg(obj);
				}
			}
			if(loadingcount >= 10) {
				break;
			}
		}
	}
	if(aimgcomplete < aimgs.length) {
		setTimeout(function () {
			attachimgshow(pid, onlyinpost);
		}, 100);
	}
}

function attachimglstshow(pid, islazy, fid, showexif) {
	var aimgs = aimgcount[pid];
	var s = '';
	if(fid) {
		s = ' onmouseover="showMenu({\'ctrlid\':this.id, \'pos\': \'12!\'});"';
	}
	if(typeof aimgcount == 'object' && $('imagelistthumb_' + pid)) {
		for(pid in aimgcount) {
			var imagelist = '';
			for(i = 0;i < aimgcount[pid].length;i++) {
				if(!$('aimg_' + aimgcount[pid][i]) || $('aimg_' + aimgcount[pid][i]).getAttribute('inpost') || parseInt(aimgcount[pid][i]) != aimgcount[pid][i]) {
					continue;
				}
				if(fid) {
					imagelist += '<div id="pattimg_' + aimgcount[pid][i] + '_menu" class="tip tip_4" style="display: none;"><div class="tip_horn"></div><div class="tip_c"><a href="forum.php?mod=ajax&action=setthreadcover&aid=' + aimgcount[pid][i] + '&fid=' + fid + '" class="xi2" onclick="showWindow(\'setcover' + aimgcount[pid][i] + '\', this.href)">设为封面</a></div></div>';
				}
				imagelist += '<div class="pattimg">' +
					'<a id="pattimg_' + aimgcount[pid][i] + '" class="pattimg_zoom" href="javascript:;"' + s + ' onclick="zoom($(\'aimg_' + aimgcount[pid][i] + '\'), attachimggetsrc(\'aimg_' + aimgcount[pid][i] + '\'), 0, 0, ' + (parseInt(showexif) ? 1 : 0) + ')" title="点击放大">点击放大</a>' +
					'<img ' + (islazy ? 'file' : 'src') + '="forum.php?mod=image&aid=' + aimgcount[pid][i] + '&size=100x100&key=' + imagelistkey + '&atid=' + tid + '" width="100" height="100" /></div>';
			}
			if($('imagelistthumb_' + pid)) {
				$('imagelistthumb_' + pid).innerHTML = imagelist;
			}
		}
	}
}

function attachimggetsrc(img) {
	return $(img).getAttribute('zoomfile') ? $(img).getAttribute('zoomfile') : $(img).getAttribute('file');
}

function attachimglst(pid, op, islazy) {
	if(!op) {
		$('imagelist_' + pid).style.display = 'none';
		$('imagelistthumb_' + pid).style.display = '';
	} else {
		$('imagelistthumb_' + pid).style.display = 'none';
		$('imagelist_' + pid).style.display = '';
		if(islazy) {
			o = new lazyload();
			o.showImage();
		} else {
			attachimgshow(pid);
		}
	}
	doane();
}

function attachimginfo(obj, infoobj, show, event) {
	objinfo = fetchOffset(obj);
	if(show) {
		$(infoobj).style.left = objinfo['left'] + 'px';
		$(infoobj).style.top = obj.offsetHeight < 40 ? (objinfo['top'] + obj.offsetHeight) + 'px' : objinfo['top'] + 'px';
		$(infoobj).style.display = '';
	} else {
		if(BROWSER.ie) {
			$(infoobj).style.display = 'none';
			return;
		} else {
			var mousex = document.body.scrollLeft + event.clientX;
			var mousey = document.documentElement.scrollTop + event.clientY;
			if(mousex < objinfo['left'] || mousex > objinfo['left'] + objinfo['width'] || mousey < objinfo['top'] || mousey > objinfo['top'] + objinfo['height']) {
				$(infoobj).style.display = 'none';
			}
		}
	}
}

function signature(obj) {
	if(obj.style.maxHeightIE != '') {
		var height = (obj.scrollHeight > parseInt(obj.style.maxHeightIE)) ? obj.style.maxHeightIE : obj.scrollHeight + 'px';
		if(obj.innerHTML.indexOf('<IMG ') == -1) {
			obj.style.maxHeightIE = '';
		}
		return height;
	}
}

function tagshow(event) {
	var obj = BROWSER.ie ? event.srcElement : event.target;
	ajaxmenu(obj, 0, 1, 2);
}

function parsetag(pid) {
	if(!$('postmessage_'+pid) || $('postmessage_'+pid).innerHTML.match(/<script[^\>]*?>/i)) {
		return;
	}
	var havetag = false;
	var tagfindarray = new Array();
	var str = $('postmessage_'+pid).innerHTML.replace(/(^|>)([^<]+)(?=<|$)/ig, function($1, $2, $3, $4) {
		for(i in tagarray) {
			if(tagarray[i] && $3.indexOf(tagarray[i]) != -1) {
				havetag = true;
				$3 = $3.replace(tagarray[i], '<h_ ' + i + '>');
				tmp = $3.replace(/&[a-z]*?<h_ \d+>[a-z]*?;/ig, '');
				if(tmp != $3) {
					$3 = tmp;
				} else {
					tagfindarray[i] = tagarray[i];
					tagarray[i] = '';
				}
			}
		}
		return $2 + $3;
		});
		if(havetag) {
		$('postmessage_'+pid).innerHTML = str.replace(/<h_ (\d+)>/ig, function($1, $2) {
			return '<span href=\"forum.php?mod=tag&name=' + tagencarray[$2] + '\" onclick=\"tagshow(event)\" class=\"t_tag\">' + tagfindarray[$2] + '</span>';
	    	});
	}
}

function setanswer(pid, from){
	if(confirm('您确认要把该回复选为“最佳答案”吗？')){
		if(BROWSER.ie) {
			doane(event);
		}
		$('modactions').action='forum.php?mod=misc&action=bestanswer&tid=' + tid + '&pid=' + pid + '&from=' + from + '&bestanswersubmit=yes';
		$('modactions').submit();
	}
}

var authort;
function showauthor(ctrlObj, menuid) {
	authort = setTimeout(function () {
		showMenu({'menuid':menuid});
		if($(menuid + '_ma').innerHTML == '') $(menuid + '_ma').innerHTML = ctrlObj.innerHTML;
	}, 500);
	if(!ctrlObj.onmouseout) {
		ctrlObj.onmouseout = function() {
			clearTimeout(authort);
		}
	}
}

function fastpostappendreply() {
	if($('fastpostrefresh') != null) {
		setcookie('fastpostrefresh', $('fastpostrefresh').checked ? 1 : 0, 2592000);
		if($('fastpostrefresh').checked) {
			location.href = 'forum.php?mod=redirect&tid='+tid+'&goto=lastpost&random=' + Math.random() + '#lastpost';
			return;
		}
	}
	newpos = fetchOffset($('post_new'));
	document.documentElement.scrollTop = newpos['top'];
	$('post_new').style.display = '';
	$('post_new').id = '';
	div = document.createElement('div');
	div.id = 'post_new';
	div.style.display = 'none';
	div.className = '';
	$('postlistreply').appendChild(div);
	$('fastpostsubmit').disabled = false;
	if($('fastpostmessage')) {
		$('fastpostmessage').value = '';
	} else {
		editdoc.body.innerHTML = BROWSER.firefox ? '<br />' : '';
	}
	if($('secanswer3')) {
		$('checksecanswer3').innerHTML = '<img src="' + STATICURL + 'image/common/none.gif" width="17" height="17">';
		$('secanswer3').value = '';
		secclick3['secanswer3'] = 0;
	}
	if($('seccodeverify3')) {
		$('checkseccodeverify3').innerHTML = '<img src="' + STATICURL + 'image/common/none.gif" width="17" height="17">';
		$('seccodeverify3').value = '';
		secclick3['seccodeverify3'] = 0;
	}
	showCreditPrompt();
}

function succeedhandle_fastpost(locationhref, message, param) {
	var pid = param['pid'];
	var tid = param['tid'];
	var from = param['from'];
	if(pid) {
		ajaxget('forum.php?mod=viewthread&tid=' + tid + '&viewpid=' + pid + '&from=' + from, 'post_new', 'ajaxwaitid', '', null, 'fastpostappendreply()');
		if(replyreload) {
			var reloadpids = replyreload.split(',');
			for(i = 1;i < reloadpids.length;i++) {
				ajaxget('forum.php?mod=viewthread&tid=' + tid + '&viewpid=' + reloadpids[i] + '&from=' + from, 'post_' + reloadpids[i]);
			}
		}
		$('fastpostreturn').className = '';
	} else {
		if(!message) {
			message = '本版回帖需要审核，您的帖子将在通过审核后显示';
		}
		$('post_new').style.display = $('fastpostmessage').value = $('fastpostreturn').className = '';
		$('fastpostreturn').innerHTML = message;
	}
	if(param['sechash']) {
		updatesecqaa(param['sechash']);
		updateseccode(param['sechash']);
	}
	if($('attach_tblheader')) {
		$('attach_tblheader').style.display = 'none';
	}
	if($('attachlist')) {
		$('attachlist').innerHTML = '';
	}
}

function errorhandle_fastpost() {
	$('fastpostsubmit').disabled = false;
}

function succeedhandle_comment(locationhref, message, param) {
	ajaxget('forum.php?mod=misc&action=commentmore&tid=' + param['tid'] + '&pid=' + param['pid'], 'comment_' + param['pid']);
	hideWindow('comment');
	showCreditPrompt();
}

function succeedhandle_postappend(locationhref, message, param) {
	ajaxget('forum.php?mod=viewthread&tid=' + param['tid'] + '&viewpid=' + param['pid'], 'post_' + param['pid']);
	hideWindow('postappend');
}

function recommendupdate(n) {
	if(getcookie('recommend')) {
		var objv = n > 0 ? $('recommendv_add') : $('recommendv_subtract');
		objv.innerHTML = parseInt(objv.innerHTML) + 1;
		setTimeout(function () {
			$('recommentc').innerHTML = parseInt($('recommentc').innerHTML) + n;
			$('recommentv').style.display = 'none';
		}, 1000);
		setcookie('recommend', '');
	}
}

function favoriteupdate() {
	var obj = $('favoritenumber');
	obj.innerHTML = parseInt(obj.innerHTML) + 1;
}
function relayupdate() {
	var obj = $('relaynumber');
	obj.innerHTML = parseInt(obj.innerHTML) + 1;
}

function shareupdate() {
	var obj = $('sharenumber');
	obj.innerHTML = parseInt(obj.innerHTML) + 1;
}

function switchrecommendv() {
	display('recommendv');
	display('recommendav');
}

function appendreply() {
	newpos = fetchOffset($('post_new'));
	document.documentElement.scrollTop = newpos['top'];
	$('post_new').style.display = '';
	$('post_new').id = '';
	div = document.createElement('div');
	div.id = 'post_new';
	div.style.display = 'none';
	div.className = '';
	$('postlistreply').appendChild(div);
	if($('postform')) {
		$('postform').replysubmit.disabled = false;
	}
	showCreditPrompt();
}

function poll_checkbox(obj) {
	if(obj.checked) {
		p++;
		for (var i = 0; i < $('poll').elements.length; i++) {
			var e = $('poll').elements[i];
			if(p == max_obj) {
				if(e.name.match('pollanswers') && !e.checked) {
					e.disabled = true;
				}
			}
		}
	} else {
		p--;
		for (var i = 0; i < $('poll').elements.length; i++) {
			var e = $('poll').elements[i];
			if(e.name.match('pollanswers') && e.disabled) {
				e.disabled = false;
			}
		}
	}
	$('pollsubmit').disabled = p <= max_obj && p > 0 ? false : true;
}

function itemdisable(i) {
	if($('itemt_' + i).className == 'z') {
		$('itemt_' + i).className = 'z xg1';
		$('itemc_' + i).value = '';
		itemset(i);
	} else {
		$('itemt_' + i).className = 'z';
		$('itemc_' + i).value = $('itemc_' + i).value > 0 ? $('itemc_' + i).value : 0;
	}
}
function itemop(i, v) {
	var h = v > 0 ? '-' + (v * 16) + 'px' : '0';
	$('item_' + i).style.backgroundPosition = '10px ' + h;
}
function itemclk(i, v) {
	$('itemc_' + i).value = v;
	$('itemt_' + i).className = 'z';
}
function itemset(i) {
	var v = $('itemc_' + i).value;
	var h = v > 0 ? '-' + (v * 16) + 'px' : '0';
	$('item_' + i).style.backgroundPosition = '10px ' + h;
}

function checkmgcmn(id) {
	if($('mgc_' + id) && !$('mgc_' + id + '_menu').getElementsByTagName('li').length) {
		$('mgc_' + id).innerHTML = '';
		$('mgc_' + id).style.display = 'none';
	}
}

function toggleRatelogCollapse(tarId, ctrlObj) {
	if($(tarId).className == 'rate') {
		$(tarId).className = 'rate rate_collapse';
		setcookie('ratecollapse', 1, 2592000);
		ctrlObj.innerHTML = '展开';
	} else {
		$(tarId).className = 'rate';
		setcookie('ratecollapse', 0, -2592000);
		ctrlObj.innerHTML = '收起';
	}
}

function copyThreadUrl(obj) {
	setCopy($('thread_subject').innerHTML.replace(/&amp;/g, '&') + '\n' + obj.href + '\n', '帖子地址已经复制到剪贴板');
	return false;
}

function replyNotice() {
	var newurl = 'forum.php?mod=misc&action=replynotice&tid=' + tid + '&op=';
	var replynotice = $('replynotice');
	var status = replynotice.getAttribute("status");
	if(status == 1) {
		replynotice.href = newurl + 'receive';
		replynotice.innerHTML = '接收回复通知';
		replynotice.setAttribute("status", 0);
	} else {
		replynotice.href = newurl + 'ignore';
		replynotice.innerHTML = '取消回复通知';
		replynotice.setAttribute("status", 1);
	}
}

var connect_share_loaded = 0;
function connect_share(connect_share_url, connect_uin) {
	if(parseInt(discuz_uid) <= 0) {
		return true;
	} else {
		if(connect_uin) {
			setTimeout(function () {
				if(!connect_share_loaded) {
					showDialog('分享服务连接失败，请稍后再试。', 'notice');
					$('append_parent').removeChild($('connect_load_js'));
				}
			}, 5000);
			connect_load(connect_share_url);
		} else {
			showDialog($('connect_share_unbind').innerHTML, 'info', '请先绑定QQ账号');
		}
		return false;
	}
}

function connect_load(src) {
	var e = document.createElement('script');
	e.type = "text/javascript";
	e.id = 'connect_load_js';
	e.src = src + '&_r=' + Math.random();
	e.async = true;
	$('append_parent').appendChild(e);
}

function connect_show_dialog(title, html, type) {
	var type = type ? type : 'info';
	showDialog(html, type, title, null, 0);
}

function connect_get_thread() {
	connect_thread_info.subject = $('connect_thread_title').value;
	if ($('postmessage_' + connect_thread_info.post_id)) {
		connect_thread_info.html_content = preg_replace(["'"], ['%27'], encodeURIComponent(preg_replace(['本帖最后由 .*? 于 .*? 编辑','&nbsp;','<em onclick="copycode\\(\\$\\(\'code0\'\\)\\);">复制代码</em>'], ['',' ', ''], $('postmessage_' + connect_thread_info.post_id).innerHTML)));
	}
	return connect_thread_info;
}

function lazyload(className) {
	var obj = this;
	lazyload.className = className;
	this.getOffset = function (el, isLeft) {
		var  retValue  = 0 ;
		while  (el != null ) {
			retValue  +=  el["offset" + (isLeft ? "Left" : "Top" )];
			el = el.offsetParent;
		}
		return  retValue;
	};
	this.initImages = function (ele) {
		lazyload.imgs = [];
		var eles = lazyload.className ? $C(lazyload.className, ele) : [document.body];
		for (var i = 0; i < eles.length; i++) {
			var imgs = eles[i].getElementsByTagName('IMG');
			for(var j = 0; j < imgs.length; j++) {
				if(imgs[j].getAttribute('file') && !imgs[j].getAttribute('lazyloaded')) {
					if(this.getOffset(imgs[j]) > document.documentElement.clientHeight) {
						lazyload.imgs.push(imgs[j]);
					} else {
						imgs[j].setAttribute('src', imgs[j].getAttribute('file'));
						imgs[j].setAttribute('lazyloaded', 'true');
					}
				}
			}
		}
	};
	this.showImage = function() {
		this.initImages();
		if(!lazyload.imgs.length) return false;
		var imgs = [];
		var scrollTop = Math.max(document.documentElement.scrollTop , document.body.scrollTop);
		for (var i=0; i<lazyload.imgs.length; i++) {
			var img = lazyload.imgs[i];
			var offsetTop = this.getOffset(img);
			if (!img.getAttribute('lazyloaded') && offsetTop > document.documentElement.clientHeight && (offsetTop  - scrollTop < document.documentElement.clientHeight)) {
				var dom = document.createElement('div');
				var width = img.getAttribute('width') ? img.getAttribute('width') : 100;
				var height = img.getAttribute('height') ? img.getAttribute('height') : 100;
				dom.innerHTML = '<div style="width: '+width+'px; height: '+height+'px;background: url('+IMGDIR + '/loading.gif) no-repeat center center;"></div>';
				img.parentNode.insertBefore(dom.childNodes[0], img);
				img.onload = function () {if(!this.getAttribute('_load')) {this.setAttribute('_load', 1);this.style.width = this.style.height = '';this.parentNode.removeChild(this.previousSibling);}};
				img.style.width = img.style.height = '1px';
				img.setAttribute('src', img.getAttribute('file') ? img.getAttribute('file') : img.getAttribute('src'));
				img.setAttribute('lazyloaded', true);
			} else {
				imgs.push(img);
			}
		}
		lazyload.imgs = imgs;
		return true;
	};
	this.showImage();
	_attachEvent(window, 'scroll', function(){obj.showImage();});
}
function update_collection(){
	sum = 1;
    $('collectionnumber').innerText = parseInt($('collectionnumber').innerText)+sum;
}