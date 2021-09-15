/*
	[Discuz!] (C)2001-2009 Comsenz Inc.
	This is NOT a freeware, use is subject to license terms

	$Id: soso_smilies.js 34306 2014-01-17 04:31:33Z nemohou $
*/

var sosojs = document.createElement('script');
	sosojs.type = 'text/javascript';
	sosojs.charset = "utf-8";
	sosojs.src = 'http://pic.sogou.com/discuz/sosoexp_platform.js';
	var sosolo = document.getElementsByTagName('script')[0];
	sosolo.parentNode.insertBefore(sosojs, sosolo);

function bbcode2html_sososmilies(sososmilieid, getsrc) {
	var imgsrc = '';
	sososmilieid = String(sososmilieid);

	if(sososmilieid.indexOf('_') >= 0) {
		if (sososmilieid.indexOf('_') == 0) {
			sososmilieid = sososmilieid.substr(1);
		}
		var imgid = 'soso__' + sososmilieid;
		var realsmilieid = sososmilieid.substr(0, sososmilieid.length-2);
		var serverid = sososmilieid.substr(sososmilieid.length-1);
		imgsrc = "http://imgstore0"+serverid+".cdn.sogou.com/app/a/100520032/"+realsmilieid;
	} else {
		var imgid = 'soso_' + sososmilieid;
		imgsrc = "http://imgstore01.cdn.sogou.com/app/a/100520032/"+sososmilieid;
	}
	if(!isUndefined(getsrc)) {
		return imgsrc;
	}
	return '<img src="'+imgsrc+'" smilieid="'+imgid+'" border="0" alt="" />';
}

function html2bbcode_sososmilies(htmlsmilies) {
	if(htmlsmilies) {
		htmlsmilies = htmlsmilies.replace(/<img[^>]+smilieid=(["']?)soso_(\w+)(\1)[^>]*>/ig, function($1, $2, $3) { return sososmileycode($3);});
	}
	return htmlsmilies;
}

function sososmileycode(sososmilieid) {
	if(sososmilieid) {
		return "{:soso_"+sososmilieid+":}";
	}
}

function sososmiliesurl2id(sosourl) {
	var sososmilieid = '';
	if(sosourl && sosourl.length > 30 && sosourl.indexOf('http://imgstore0') == 0) {
		var type = sosourl.substr(sosourl.lastIndexOf('/') + 1, 1);
		if (type == 'e') {
            		sososmilieid = sosourl.substr(sosourl.lastIndexOf('/') + 1);
		} else {
        		var serverid = sosourl.substr(16, 1);
			var realsmilieid = sosourl.substr(sosourl.lastIndexOf('/') + 1);
			sososmilieid = '_'+realsmilieid+'_'+serverid;
		}
		return sososmilieid;
	}
}

function insertsosoSmiley(sosourl) {
	var sososmilieid = sososmiliesurl2id(sosourl);
	if(sososmilieid) {
		var code = sososmileycode(sososmilieid);
		var src = bbcode2html_sososmilies(sososmilieid, true);
		checkFocus();
		if(wysiwyg && allowsmilies && (!$('smileyoff') || $('smileyoff').checked == false)) {
			insertText(bbcode2html_sososmilies(sososmilieid), false);
		} else {
			code += ' ';
			insertText(code, strlen(code), 0);
		}
		hideMenu();
	}
}

function insertfastpostSmiley(sosourl, textareaid) {
	var sososmilieid = sososmiliesurl2id(sosourl);
	if(sososmilieid) {
		var code = sososmileycode(sososmilieid);
		seditor_insertunit(textareaid, code);
	}
}

var TimeCounter = 0;
function SOSO_EXP_CHECK(textareaid) {
	TimeCounter++;
	if(typeof editorid!='undefined' && textareaid == 'newthread') {
		var eExpBtn = $(editorid + '_sml'),
			eEditBox = $(editorid + '_textarea');
			eExpBtn.setAttribute('init', 1);
			fFillEditBox = function(editbox, url) {
				insertsosoSmiley(url);
			};
	} else if(in_array(textareaid, ['post', 'fastpost', 'pm', 'send', 'reply', 'sightml'])) {
		var eExpBtn = $(textareaid+"sml"),
			eEditBox = $(textareaid+"message"),
			fFillEditBox = function(editbox, url) {
				insertfastpostSmiley(url, textareaid);
			};
	} else {
		return false;
	}
	if(typeof SOSO_EXP != "undefined" && typeof SOSO_EXP.Register == "function" && eExpBtn && eEditBox) {
		var pos = 'bottom';
		if(in_array(textareaid, ['fastpost', 'pm', 'reply'])) {
			pos = 'top';
		}
		eExpBtn.onclick = function() { return null; };
		SOSO_EXP.Register(60001, 'discuz', eExpBtn, pos, eEditBox, fFillEditBox);
		if(typeof editdoc != "undefined" && editdoc && editdoc.body) {
			editdoc.body.onclick = extrafunc_soso_showmenu;
			document.body.onclick = extrafunc_soso_showmenu;
		}
		return true;
	} else if(TimeCounter<15) {
		setTimeout(function () { SOSO_EXP_CHECK(textareaid) ; }, 2000);
		return false;
	} else if(typeof SOSO_EXP == "undefined" || typeof SOSO_EXP.Register != "function") {
		return false;
	} else {
		return false;
	}
}

if(typeof EXTRAFUNC['bbcode2html'] != "undefined") {
	EXTRAFUNC['bbcode2html']['soso'] = 'extrafunc_soso_bbcode2html';
	EXTRAFUNC['html2bbcode']['soso'] = 'extrafunc_soso_html2bbcode';
	if(typeof editdoc != "undefined") {
		EXTRAFUNC['showmenu']['soso'] = 'extrafunc_soso_showmenu';
	}
}

function extrafunc_soso_showmenu() {
	SOSO_EXP.Platform.hideBox();
}

function extrafunc_soso_bbcode2html() {
	if(!fetchCheckbox('smileyoff') && allowsmilies) {
		EXTRASTR = EXTRASTR.replace(/\{\:soso_(\w+)\:\}/ig, function($1, $2) { return bbcode2html_sososmilies($2);});
	}
	return EXTRASTR;
}

function extrafunc_soso_html2bbcode() {
	if((allowhtml && fetchCheckbox('htmlon')) || (!fetchCheckbox('bbcodeoff') && allowbbcode)) {
		EXTRASTR = html2bbcode_sososmilies(EXTRASTR);
	}
	return EXTRASTR;
}