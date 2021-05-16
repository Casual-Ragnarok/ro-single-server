/*
	[Discuz!] (C)2001-2009 Comsenz Inc.
	This is NOT a freeware, use is subject to license terms

	$Id: soso_smilies.js 28110 2012-02-22 08:54:16Z songlixin $
*/

var sosojs = document.createElement('script');
	sosojs.type = 'text/javascript';
	sosojs.charset = "utf-8";
	sosojs.src = 'http://bq.soso.com/js/sosoexp_platform.js';
var sosolo = document.getElementsByTagName('script')[0];
	sosolo.parentNode.insertBefore(sosojs, sosolo);

function bbcode2html_sososmilies(sososmilieid, getsrc) {
	var imgsrc = '';
	sososmilieid = String(sososmilieid);
	var imgid = 'soso_' + sososmilieid;

	if(sososmilieid.indexOf('_') == 0) {
		var realsmilieid = sososmilieid.substr(0, sososmilieid.length-2);
		var serverid = sososmilieid.substr(sososmilieid.length-1);
		imgsrc = "http://piccache"+serverid+".soso.com/face/"+realsmilieid;
	} else {
		imgsrc = "http://cache.soso.com/img/img/"+sososmilieid+".gif";
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
	if(sosourl && sosourl.length > 30) {
		var idindex = sosourl.lastIndexOf('/');
		if(sosourl.indexOf('http://piccache') == 0) {
			var serverid = sosourl.substr(15,1);
			var realsmilieid = sosourl.substr(idindex+1);
			sososmilieid = realsmilieid+'_'+serverid;
		} else if(sosourl.indexOf('http://cache.soso.com') == 0) {
			sososmilieid = sosourl.substring(idindex+1, sosourl.length-4);
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