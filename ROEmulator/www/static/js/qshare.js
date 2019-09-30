/*
	[Discuz!] (C)2001-2009 Comsenz Inc.
	This is NOT a freeware, use is subject to license terms

	$Id$
*/

var _share_tencent_weibo = function() {
    var share_btn = function(_arr) {
        if (_arr[0]) {
            return _arr[0];
        }
        else {
            var o = document.createElement("a"),
            _ostyle = "width:92px;height:22px;background:url(http://open.t.qq.com/apps/qshare/images/icon.gif) no-repeat #f00;position:absolute;display:none;";
            o.setAttribute("style", _ostyle);
            o.style.cssText = _ostyle;
            o.setAttribute("href", "javascript:;");
            document.body.insertBefore(o, document.body.childNodes[0]);
            return o;
        }
    } (arguments);
    var share_area = function(_arr) {
        if (_arr[1]) {
            if ((typeof _arr[1] == "object" && _arr[1].length) || (_arr[1].constructor == Array)) {
                return _arr[1];
            } else {
                return [_arr[1]];
            }
        }
        else {
            return [document.body];
        }
    } (arguments);
    var current_area = share_area[0];
    var _site = arguments[2] ? arguments[2] : "";
    var _appkey = encodeURI(arguments[3] ? arguments[3] : "");
    var _web = {
        "name": arguments[4] || "",
        "href": location.href,
        "hash": location.hash
    };
    var _pic = function(area) {
        var _imgarr = area.getElementsByTagName("img");
        var _srcarr = [];
        for (var i = 0; i < _imgarr.length; i++) {
            _srcarr.push(_imgarr[i].src);
        }
        return _srcarr.join("|");
    };
    var _u = 'http://v.t.qq.com/share/share.php?url=$url$&appkey=' + _appkey + '&site=' + _site + '&title=$title$&pic=$pic$';
    var _select = function() {
        return (document.selection ? document.selection.createRange().text: document.getSelection()).toString().replace(/[\s\n]+/g, " ");
    };
    if ( !! window.find) {
        HTMLElement.prototype.contains = function(B) {
            return this.compareDocumentPosition(B) - 19 > 0
        }
    }
    String.prototype.elength = function() {
        return this.replace(/[^\u0000-\u00ff]/g, "aa").length;
    }
    document.onmouseup = function(e) {
        e = e || window.event;
        var o = e.target || e.srcElement;
        for (var i = 0; i < share_area.length; i++) {
            if (share_area[i].contains(o) || share_area[i] == o) {
                var _e = {
                    "x": e.clientX,
                    "y": e.clientY
                };
                var _o = {
                    "w": share_btn.clientWidth,
                    "h": share_btn.clientHeight
                };
                var _d = window.pageYOffset || (document.documentElement || document.body).scrollTop || 0;
                var x = (_e.x - _o.w < 0) ? _e.x + _o.w: _e.x - _o.w,
                y = (_e.y - _o.h < 0) ? _e.y + _d - _o.h: _e.y + _d - _o.h + ( - [1, ] ? 10 : 0);
                if (_select() && _select().length >= 10) {
                    with(share_btn.style) {
                        display = "inline-block";
                        left = (x - 5) + "px";
                        top = y + "px";
                        position = "absolute";
                        zIndex = "999999";
                    }
                    current_area = share_area[i];
                    break;
                } else {
                    share_btn.style.display = "none";
                }

            } else {
                share_btn.style.display = "none";
            }
        }
    };
    share_btn.onclick = function() {
        var _str = _select();
        var _strmaxlen = 280 - ("\u6211\u6765\u81EA\u4E8E\u817E\u8BAF\u5FAE\u535A\u5F00\u653E\u5E73\u53F0" + " " + _web.name).elength();
        var _resultstr = "";
        if (_str.elength() > _strmaxlen) {
            _strmaxlen = _strmaxlen - 3;
            for (var i = _strmaxlen >> 1; i <= _strmaxlen; i++) {
                if ((_str.slice(0, i)).elength() > _strmaxlen) {
                    break;
                }
                else {
                    _resultstr = _str.slice(0, i);
                }
            }
            _resultstr += "...";
        } else {
            _resultstr = _str;
        }
        if (_str) {
            var url = _u.replace("$title$", encodeURIComponent(_resultstr + " " + _web.name)).replace("$pic$", _pic(current_area));
            url = url.replace("$url$", encodeURIComponent(_web.href.replace(_web.hash, "") + "#" + (current_area["name"] || current_area["id"] || "")));
            if (!- [1, ]) {
                url = url.substr(0, 2048);
            }
            window.open(url, 'null', 'width=700,height=680,top=0,left=0,toolbar=no,menubar=no,scrollbars=no,location=yes,resizable=no,status=no');
        }
    };
};