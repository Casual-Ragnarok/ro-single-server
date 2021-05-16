
var cd_a = 20;
var cd_b2 = 36;
var cd_c = 5.4;
var car_price = true;
var car_nll = true;
var pop_list = true;
var gjj_a = 20;
var gjj_b2 = 36;
var gjj_c = 5.4;
var gjj_d = 10000;
var gjj_e = 200;
var gjj_b6 = 1;
var gjj_price = true;
var gjj_nll = true;
var gjj_list = true;
var gjj_pmdj = true;
var gjj_mj = true;
var zhdk_b2 = 36;
var zhdk_a = 20;
var zhdk_b = 20;
var zhdk_c = 20;
var zhdk_d = 20;
var zhdk_gjjprice = true;
var jq = jQuery.noConflict();
var zhdk_gjjll = true;
var zhdk_sdprice = true;
var zhdk_sdll = true;
var efd_mj = true;
var efd_zj = true;
var esfd_a = 0;
var esfd_b = 0;
jq(document).ready(function() {
    fdj();
    cdyz1();
    cdyz2();
    cdyz3();
    cdjs();
    jq('#jsstyle_0').attr('checked', true)
    gjjyz1();
    gjjyz2();
    gjjyz3();
    gjjyz4();
    gjjyz5();
    gjjyz6();
    gjjjs();
    zhdkyz1();
    zhdkyz2();
    zhdkyz3();
    zhdkyz4();
    zhdkyz5();
    zhdkjs();
    jq('#jsfwxz_0').attr('checked', true)
    jq('#jshas5_0').attr('checked', true)
    esfdyz1();
    esfdyz2();
    esfdjs();
});

function fdj() {
    var fdjt01 = jq('#jsstyle_0');
    var fdjt02 = jq('#jsstyle_1');
    var fdjt01_con = fdjt01.parent().parent().parent().children('.fdj_01');
    var fdjt02_con = fdjt02.parent().parent().parent().children('.fdj_02');
    fdjt01.click(function() {
        fdjt01_con.removeClass('none');
        fdjt02_con.addClass('none');

        jq('.js_total').text('0');
        jq('.js_time').text('0');
        jq('#js_Lmy').text('0');
        jq('#js_Lzlx').text('0');
        jq('#js_Lbxhj').text('0');
        jq('#js_Rsy').text('0');
        jq('#js_Rdj').text('0');
        jq('#js_Rzlx').text('0');
        jq('#js_Rbxhj').text('0');
    });
    fdjt02.click(function() {
        fdjt01_con.addClass('none');
        fdjt02_con.removeClass('none');

        jq('.js_total').text('0');
        jq('.js_time').text('0');
        jq('#js_Lmy').text('0');
        jq('#js_Lzlx').text('0');
        jq('#js_Lbxhj').text('0');
        jq('#js_Rsy').text('0');
        jq('#js_Rdj').text('0');
        jq('#js_Rzlx').text('0');
        jq('#js_Rbxhj').text('0');
    });
}

function cdyz1(cd_a) {
    var cipt = jq('#car_price')
    var cd_a = cipt.val();
    cipt.blur(function() {
        var Cprice_val = cipt.val();
        var Tprice_val = jq.trim(Cprice_val);
        var error_ts = jq('.car_price_error');
        if (Tprice_val == "") {
            error_ts.removeClass('none');
            error_ts.text('不能为空');
            car_price = false;
        } else if (Tprice_val.length > 0) {
            if (!isNaN(Tprice_val)) {
                error_ts.addClass('none');
                cipt.addClass('success_ts');
                car_price = true;
            } else {
                error_ts.removeClass('none');
                error_ts.text('少年，这不是一个数字');
                cipt.addClass('error_ts');
                car_price = false;
            }
        }
    });
    cipt.focus(function() {
        cipt.removeClass('success_ts');
        cipt.removeClass('error_ts');
    });
    return cd_a;
}
function cdyz2() {
    var ctime = jq('#cd_pop_list')
    var clist = jq('#cd_list_options');
    ctime.click(function() {
        var cd_yon = clist.hasClass('none');
        if (cd_yon == true) {
            clist.removeClass('none');
        } else {
            clist.addClass('none');
        }
    });
}
function cdmonth(bb) {
    var bthis = jq(bb);
    var cd_reval = bthis.attr('reval');
    var cdshow = jq('#car_dktime');
    var cdli = jq('#cd_list_options li a');
    var cdul = jq('#cd_list_options');
    var cdshow_reval = cdshow.attr('reval');
    cdshow_reval = cd_reval;
    cd_b2 = cdshow_reval;
    cdshow.text(bthis.text());
    cdli.removeClass('mo');
    bthis.addClass('mo');
    cdul.addClass('none');
    return cd_b2;
}
function cdyz3(cd_c) {
    var cnll = jq('#car_nll');
    var error_ts = jq('.car_nll_error');
    var cd_c = cnll.val();
    cnll.blur(function() {
        var Cnll_val = cnll.val();
        var Tnll_val = jq.trim(Cnll_val);
        if (Tnll_val == '') {
            error_ts.removeClass('none');
            error_ts.text('不能为空');
            car_nll = false;
        } else if (Tnll_val.length > 0) {
            if (!isNaN(Tnll_val)) {
                cnll.addClass('success_ts');
                error_ts.addClass('none');
                car_nll = true;
            } else {
                error_ts.removeClass('none');
                error_ts.text('少年，这不是一个年利率的数字');
                cnll.addClass('error_ts');
                car_nll = false;
            }
        }
    });
    cnll.focus(function() {
        cnll.removeClass('success_ts');
        cnll.removeClass('error_ts');
    });
    return cd_c;
}
function cdjs(cd_a, cd_c) {
    var cbtn = jq('#cd_jsbtn');
    cbtn.click(function() {
        if (car_price == true && car_nll == true && pop_list == true) {
            var cd_sf = jq('#topSf');
            var cd_yg = jq('#topYg');
            var cd_time = jq('#topM');
            var cd_price = jq('#topPrice');
            var cd_all = jq('#topTotal');
            var cd_more = jq('#topMore');

            var r1 = cdyz1(cd_a);
            var r2 = cd_b2;
            var r3 = cdyz3(cd_c);

            cd_sf = Math.round((r1 * (0.3)) * 10000) / 10000;
            cd_price = Math.round(r1 * 10000) / 10000;
            cd_time = r2;
            var cd_bj = cd_price - cd_sf;
            var yll = (r3 * 0.01) / 12;
            var t = Math.pow((1 + yll), cd_time);
            cd_yg = Math.round((cd_bj * yll * (t / (t - 1))) * 10000) / 10000;
            cd_all = Math.round((cd_sf + cd_yg * cd_time) * 10000) / 10000;
            cd_more = Math.round((cd_all - cd_price) * 10000) / 10000;
            jq('#topSf').text(cd_sf);
            jq('#topYg').text(cd_yg * 10000);
            jq('#topM').text(cd_time);
            jq('#topPrice').text(cd_price);
            jq('#topTotal').text(cd_all);
            jq('#topMore').text(cd_more);

        } else {
        }
        ;

    });
}


function gjjyz1(gjj_a) {
    var cipt = jq('#gjj_price')
    var gjj_a = cipt.val();
    cipt.blur(function() {
        var Cprice_val = cipt.val();
        var Tprice_val = jq.trim(Cprice_val);
        var error_ts = jq(this).parent().parent().children('.js_error');
        if (Tprice_val == "") {
            error_ts.removeClass('none');
            error_ts.text('不能为空');
            gjj_price = false;
        } else if (Tprice_val.length > 0) {
            if (!isNaN(Tprice_val)) {
                error_ts.addClass('none');
                cipt.addClass('success_ts');
                gjj_price = true;
            } else {
                error_ts.removeClass('none');
                error_ts.text('少年，这不是一个数字');
                cipt.addClass('error_ts');
                gjj_price = false;
            }
        }
    });
    cipt.focus(function() {
        cipt.removeClass('success_ts');
        cipt.removeClass('error_ts');
    });
    return gjj_a;
}
function gjjyz2() {
    var ctime = jq('#gjj_pop_list')
    var clist = jq('#gjj_list_options');
    ctime.click(function() {
        var js_yon = clist.hasClass('none');
        if (js_yon == true) {
            clist.removeClass('none');
        } else {
            clist.addClass('none');
        }
    });
}
function gjjmonth(gjjbb) {
    var bthis = jq(gjjbb);
    var js_reval = bthis.attr('reval');
    var jsshow = jq('#gjj_dktime');
    var jsli = jq('#gjj_list_options li a');
    var jsul = jq('#gjj_list_options');
    var jsshow_reval = jsshow.attr('reval');
    jsshow_reval = js_reval;
    gjj_b2 = jsshow_reval;
    jsshow.text(bthis.text());
    jsli.removeClass('mo');
    bthis.addClass('mo');
    jsul.addClass('none');
    return gjj_b2;
}
function gjjyz3(gjj_c) {
    var cnll = jq('#gjj_nll');
    var gjj_c = cnll.val();
    cnll.blur(function() {
        var error_ts = jq(this).parent().parent().children('.js_error');
        var Cnll_val = cnll.val();
        var Tnll_val = jq.trim(Cnll_val);
        if (Tnll_val == '') {
            error_ts.removeClass('none');
            error_ts.text('不能为空');
            gjj_nll = false;
        } else if (Tnll_val.length > 0) {
            if (!isNaN(Tnll_val)) {
                cnll.addClass('success_ts');
                error_ts.addClass('none');
                gjj_nll = true;
            } else {
                error_ts.removeClass('none');
                error_ts.text('少年，这不是一个年利率的数字');
                cnll.addClass('error_ts');
                gjj_nll = false;
            }
        }
    });
    cnll.focus(function() {
        cnll.removeClass('success_ts');
        cnll.removeClass('error_ts');
    });
    return gjj_c;
}
function gjjyz4(gjj_d) {
    var cipt = jq('#gjj_pmdj')
    var gjj_d = cipt.val();
    cipt.blur(function() {
        var Cprice_val = cipt.val();
        var Tprice_val = jq.trim(Cprice_val);
        var error_ts = jq(this).parent().parent().children('.js_error');
        if (Tprice_val == "") {
            error_ts.removeClass('none');
            error_ts.text('不能为空');
            gjj_pmdj = false;
        } else if (Tprice_val.length > 0) {
            if (!isNaN(Tprice_val)) {
                error_ts.addClass('none');
                cipt.addClass('success_ts');
                gjj_pmdj = true;
            } else {
                error_ts.removeClass('none');
                error_ts.text('少年，这不是一个数字');
                cipt.addClass('error_ts');
                gjj_pmdj = false;
            }
        }
    });
    cipt.focus(function() {
        cipt.removeClass('success_ts');
        cipt.removeClass('error_ts');
    });
    return gjj_d;
}
function gjjyz5(gjj_e) {
    var cipt = jq('#gjj_mj')
    var gjj_e = cipt.val();
    cipt.blur(function() {
        var Cprice_val = cipt.val();
        var Tprice_val = jq.trim(Cprice_val);
        var error_ts = jq(this).parent().parent().children('.js_error');
        if (Tprice_val == "") {
            error_ts.removeClass('none');
            error_ts.text('不能为空');
            gjj_mj = false;
        } else if (Tprice_val.length > 0) {
            if (!isNaN(Tprice_val)) {
                error_ts.addClass('none');
                cipt.addClass('success_ts');
                gjj_mj = true;
            } else {
                error_ts.removeClass('none');
                error_ts.text('少年，这不是一个数字');
                cipt.addClass('error_ts');
                gjj_mj = false;
            }
        }
    });
    cipt.focus(function() {
        cipt.removeClass('success_ts');
        cipt.removeClass('error_ts');
    });
    return gjj_e;
}
function gjjyz6() {
    var ctime = jq('#gjj_nature_list')
    var clist = jq('#gjj_nature_options');
    ctime.click(function() {
        var js_yon = clist.hasClass('none');
        if (js_yon == true) {
            clist.removeClass('none');
        } else {
            clist.addClass('none');
        }
    });
}
function gjjnature(gjjb6) {
    var bthis = jq(gjjb6);
    var js_reval = bthis.attr('reval');
    var jsshow = jq('#gjj_nature_show');
    var jsli = jq('#gjj_nature_options li a');
    var jsul = jq('#gjj_nature_options');
    var jsshow_reval = jsshow.attr('reval');
    jsshow_reval = js_reval;
    gjj_b6 = jsshow_reval;
    jsshow.text(bthis.text());
    jsli.removeClass('mo');
    bthis.addClass('mo');
    jsul.addClass('none');
    return gjj_b6;
}
function gjjjs(gjj_a, gjj_c, gjj_d, gjj_e) {
    var gjjtn = jq('#gjj_jsbtn');
    gjjtn.click(function() {
        var js_total = jq('.js_total');
        var js_time = jq('.js_time');
        var js_Lmy = jq('#js_Lmy');
        var js_Rsy = jq('#js_Rsy');
        var js_Rdj = jq('#js_Rdj');
        var js_Lzlx = jq('#js_Lzlx');
        var js_Rzlx = jq('#js_Rzlx');
        var js_Lbxhj = jq('#js_Lbxhj');
        var js_Rbxhj = jq('#js_Rbxhj');

        var r1 = gjjyz1(gjj_a);
        var r2 = gjj_b2;
        var r3 = gjjyz3(gjj_c);
        var r4 = gjjyz4(gjj_d);
        var r5 = gjjyz5(gjj_e) * 0.0001;
        var r6 = gjj_b6;
        if (jq('#jsstyle_0').attr('checked') == 'checked') {
            if (gjj_price == true && gjj_nll == true && gjj_list == true) {
                js_total = Math.round(r1 * 10000) / 10000;
                js_time = Math.round(r2 * 10000) / 10000;
                var yll = Math.round(((r3 * 0.01) / 12) * 10000) / 10000;
                var t = Math.pow((1 + yll), js_time);
                js_Lmy = Math.round((js_total * yll * (t / (t - 1))) * 10000) / 10000;
                var js_Lzlx_00 = (js_time * js_Lmy) - js_total;
                js_Lzlx = Math.round(js_Lzlx_00 * 10000) / 10000;
                js_Lbxhj = js_Lzlx + Math.round(js_total);
                var js_mbj = js_total / js_time;
                var js_Rsy_00 = js_mbj + (js_total * yll);
                js_Rsy = Math.round(js_Rsy_00 * 10000) / 10000;
                var js_Rdj_00 = (js_mbj + (js_total - js_mbj) * yll) - (js_mbj + (js_total - js_mbj * 2) * yll);
                js_Rdj = Math.round(js_Rdj_00 * 10000) / 10000;
                var js_Rzlx_00 = js_total * yll * (js_time + 1) / 2;
                js_Rzlx = Math.round(js_Rzlx_00 * 10000) / 10000;
                js_Rbxhj = js_Rzlx + Math.round(js_total);

                jq('.js_total').text(js_total);
                jq('.js_time').text(js_time);
                jq('#js_Lmy').text(js_Lmy);
                jq('#js_Lzlx').text(js_Lzlx);
                jq('#js_Lbxhj').text(js_Lbxhj);
                jq('#js_Rsy').text(js_Rsy);
                jq('#js_Rdj').text(js_Rdj);
                jq('#js_Rzlx').text(js_Rzlx);
                jq('#js_Rbxhj').text(js_Rbxhj);
            }
        }
        if (jq('#jsstyle_1').attr('checked') == 'checked') {
            if (gjj_pmdj == true && gjj_mj == true && gjj_nll == true) {
                var js_fjzj = Math.round(r4 * r5 * 10000) / 10000;
                var bx = 0;
                if (r6 == 1) {
                    bx = 0.7; //alert('1111');
                } else if (r6 == 2) {
                    bx = 0.4; //alert('2222');
                }
                var dz = Math.round(js_fjzj * bx * 10000) / 10000;
                js_total = Math.round(dz * 10000) / 10000;
                js_time = Math.round(r2 * 10000) / 10000;
                var yll = Math.round(((r3 * 0.01) / 12) * 10000) / 10000;
                var t = Math.pow((1 + yll), js_time);
                js_Lmy = Math.round((js_total * yll * (t / (t - 1))) * 10000) / 10000;
                var js_Lzlx_00 = (js_time * js_Lmy) - js_total;
                js_Lzlx = Math.round(js_Lzlx_00 * 10000) / 10000;
                js_Lbxhj = js_Lzlx + Math.round(js_total);
                var js_mbj = js_total / js_time;
                var js_Rsy_00 = js_mbj + (js_total * yll);
                js_Rsy = Math.round(js_Rsy_00 * 10000) / 10000;
                var js_Rdj_00 = (js_mbj + (js_total - js_mbj) * yll) - (js_mbj + (js_total - js_mbj * 2) * yll);
                js_Rdj = Math.round(js_Rdj_00 * 10000) / 10000;
                var js_Rzlx_00 = js_total * yll * (js_time + 1) / 2;
                js_Rzlx = Math.round(js_Rzlx_00 * 10000) / 10000;
                js_Rbxhj = js_Rzlx + Math.round(js_total);

                jq('.js_total').text(js_total);
                jq('.js_time').text(js_time);
                jq('#js_Lmy').text(js_Lmy);
                jq('#js_Lzlx').text(js_Lzlx);
                jq('#js_Lbxhj').text(js_Lbxhj);
                jq('#js_Rsy').text(js_Rsy);
                jq('#js_Rdj').text(js_Rdj);
                jq('#js_Rzlx').text(js_Rzlx);
                jq('#js_Rbxhj').text(js_Rbxhj);
            }
        }
    });
}



function zhdkyz1() {
    var ctime = jq('#zhdk_pop_list')
    var clist = jq('#zhdk_list_options');
    ctime.click(function() {
        var js_yon = clist.hasClass('none');
        if (js_yon == true) {
            clist.removeClass('none');
        } else {
            clist.addClass('none');
        }
    });
}
function zhdkmonth(zhdkbb) {
    var bthis = jq(zhdkbb);
    var js_reval = bthis.attr('reval');
    var jsshow = jq('#zhdk_dktime');
    var jsli = jq('#zhdk_list_options li a');
    var jsul = jq('#zhdk_list_options');
    var jsshow_reval = jsshow.attr('reval');
    jsshow_reval = js_reval;
    zhdk_b2 = jsshow_reval;
    jsshow.text(bthis.text());
    jsli.removeClass('mo');
    bthis.addClass('mo');
    jsul.addClass('none');
    return zhdk_b2;
}
function zhdkyz2(zhdk_a) {
    var cipt = jq('#zhdk_gjjprice')
    var zhdk_a = cipt.val();
    cipt.blur(function() {
        var Cprice_val = cipt.val();
        var Tprice_val = jq.trim(Cprice_val);
        var error_ts = jq(this).parent().parent().children('.js_error');
        if (Tprice_val == "") {
            error_ts.removeClass('none');
            error_ts.text('不能为空');
            zhdk_gjjprice = false;
        } else if (Tprice_val.length > 0) {
            if (!isNaN(Tprice_val)) {
                error_ts.addClass('none');
                cipt.addClass('success_ts');
                zhdk_gjjprice = true;
            } else {
                error_ts.removeClass('none');
                error_ts.text('少年，这不是一个数字');
                cipt.addClass('error_ts');
                zhdk_gjjprice = false;
            }
        }
    });
    cipt.focus(function() {
        cipt.removeClass('success_ts');
        cipt.removeClass('error_ts');
    });
    return zhdk_a;
}
function zhdkyz3(zhdk_b) {
    var cipt = jq('#zhdk_gjjll')
    var zhdk_b = cipt.val();
    cipt.blur(function() {
        var Cprice_val = cipt.val();
        var Tprice_val = jq.trim(Cprice_val);
        var error_ts = jq(this).parent().parent().children('.js_error');
        if (Tprice_val == "") {
            error_ts.removeClass('none');
            error_ts.text('不能为空');
            zhdk_gjjll = false;
        } else if (Tprice_val.length > 0) {
            if (!isNaN(Tprice_val)) {
                error_ts.addClass('none');
                cipt.addClass('success_ts');
                zhdk_gjjll = true;
            } else {
                error_ts.removeClass('none');
                error_ts.text('少年，这不是一个数字');
                cipt.addClass('error_ts');
                zhdk_gjjll = false;
            }
        }
    });
    cipt.focus(function() {
        cipt.removeClass('success_ts');
        cipt.removeClass('error_ts');
    });
    return zhdk_b;
}
function zhdkyz4(zhdk_c) {
    var cipt = jq('#zhdk_sdprice')
    var zhdk_c = cipt.val();
    cipt.blur(function() {
        var Cprice_val = cipt.val();
        var Tprice_val = jq.trim(Cprice_val);
        var error_ts = jq(this).parent().parent().children('.js_error');
        if (Tprice_val == "") {
            error_ts.removeClass('none');
            error_ts.text('不能为空');
            zhdk_sdprice = false;
        } else if (Tprice_val.length > 0) {
            if (!isNaN(Tprice_val)) {
                error_ts.addClass('none');
                cipt.addClass('success_ts');
                zhdk_sdprice = true;
            } else {
                error_ts.removeClass('none');
                error_ts.text('少年，这不是一个数字');
                cipt.addClass('error_ts');
                zhdk_sdprice = false;
            }
        }
    });
    cipt.focus(function() {
        cipt.removeClass('success_ts');
        cipt.removeClass('error_ts');
    });
    return zhdk_c;
}
function zhdkyz5(zhdk_d) {
    var cipt = jq('#zhdk_sdll')
    var zhdk_d = cipt.val();
    cipt.blur(function() {
        var Cprice_val = cipt.val();
        var Tprice_val = jq.trim(Cprice_val);
        var error_ts = jq(this).parent().parent().children('.js_error');
        if (Tprice_val == "") {
            error_ts.removeClass('none');
            error_ts.text('不能为空');
            zhdk_sdll = false;
        } else if (Tprice_val.length > 0) {
            if (!isNaN(Tprice_val)) {
                error_ts.addClass('none');
                cipt.addClass('success_ts');
                zhdk_sdll = true;
            } else {
                error_ts.removeClass('none');
                error_ts.text('少年，这不是一个数字');
                cipt.addClass('error_ts');
                zhdk_sdll = false;
            }
        }
    });
    cipt.focus(function() {
        cipt.removeClass('success_ts');
        cipt.removeClass('error_ts');
    });
    return zhdk_d;
}
function zhdkjs(zhdk_a, zhdk_b, zhdk_c, zhdk_d) {
    var zhdktn = jq('#zhdk_jsbtn');
    zhdktn.click(function() {
        var js_total = jq('.js_total');
        var js_time = jq('.js_time');
        var js_Lmy = jq('#js_Lmy');
        var js_Rsy = jq('#js_Rsy');
        var js_Rdj = jq('#js_Rdj');
        var js_Lzlx = jq('#js_Lzlx');
        var js_Rzlx = jq('#js_Rzlx');
        var js_Lbxhj = jq('#js_Lbxhj');
        var js_Rbxhj = jq('#js_Rbxhj');

        var r0 = zhdk_b2;
        var r1 = zhdkyz2(zhdk_a);
        var r2 = zhdkyz3(zhdk_b);
        var r3 = zhdkyz4(zhdk_c);
        var r4 = zhdkyz5(zhdk_d);

        if (zhdk_gjjprice == true && zhdk_gjjll == true && zhdk_sdprice == true && zhdk_sdll == true) {

            var js_total_01 = Math.round(r1 * 10000) / 10000;
            js_time = Math.round(r0 * 10000) / 10000;
            var yll_01 = Math.round(((r2 * 0.01) / 12) * 10000) / 10000;
            var t_01 = Math.pow((1 + yll_01), js_time);
            var js_Lmy_01 = Math.round((js_total_01 * yll_01 * (t_01 / (t_01 - 1))) * 10000) / 10000;
            var js_Lzlx_00_01 = (js_time * js_Lmy_01) - js_total_01;
            var js_Lzlx_01 = Math.round(js_Lzlx_00_01 * 10000) / 10000;
            var js_Lbxhj_01 = js_Lzlx_01 + Math.round(js_total_01);
            var js_mbj_01 = js_total_01 / js_time;
            var js_Rsy_00_01 = js_mbj_01 + (js_total_01 * yll_01);
            var js_Rsy_01 = Math.round(js_Rsy_00_01 * 10000) / 10000;
            var js_Rdj_00_01 = (js_mbj_01 + (js_total_01 - js_mbj_01) * yll_01) - (js_mbj_01 + (js_total_01 - js_mbj_01 * 2) * yll_01);
            var js_Rdj_01 = Math.round(js_Rdj_00_01 * 10000) / 10000;
            var js_Rzlx_00_01 = js_total_01 * yll_01 * (js_time + 1) / 2;
            var js_Rzlx_01 = Math.round(js_Rzlx_00_01 * 10000) / 10000;
            var js_Rbxhj_01 = js_Rzlx_01 + Math.round(js_total_01);



            var js_total_02 = Math.round(r3 * 10000) / 10000;
            var yll_02 = Math.round(((r4 * 0.01) / 12) * 10000) / 10000;
            var t_02 = Math.pow((1 + yll_02), js_time);
            var js_Lmy_02 = Math.round((js_total_01 * yll_02 * (t_02 / (t_02 - 1))) * 10000) / 10000;
            var js_Lzlx_00_02 = (js_time * js_Lmy_02) - js_total_02;
            var js_Lzlx_02 = Math.round(js_Lzlx_00_02 * 10000) / 10000;
            var js_Lbxhj_02 = js_Lzlx_02 + Math.round(js_total_02);
            var js_mbj_02 = js_total_02 / js_time;
            var js_Rsy_00_02 = js_mbj_02 + (js_total_02 * yll_02);
            var js_Rsy_02 = Math.round(js_Rsy_00_02 * 10000) / 10000;
            var js_Rdj_00_02 = (js_mbj_02 + (js_total_02 - js_mbj_02) * yll_02) - (js_mbj_02 + (js_total_02 - js_mbj_02 * 2) * yll_02);
            var js_Rdj_02 = Math.round(js_Rdj_00_02 * 10000) / 10000;
            var js_Rzlx_00_02 = js_total_02 * yll_02 * (js_time + 1) / 2;
            var js_Rzlx_02 = Math.round(js_Rzlx_00_02 * 10000) / 10000;
            var js_Rbxhj_02 = js_Rzlx_02 + Math.round(js_total_02);


            js_total = js_total_01 + js_total_02;
            js_Lmy = js_Lmy_01 + js_Lmy_02;
            js_Lzlx = js_Lzlx_01 + js_Lzlx_02;
            js_Lbxhj = js_Lbxhj_01 + js_Lbxhj_02;
            js_Rsy = js_Rsy_01 + js_Rsy_02;
            js_Rdj = (js_Rdj_01 + js_Rdj_02) * 10000 / 10000;
            js_Rzlx = js_Rzlx_01 + js_Rzlx_02;
            js_Rbxhj = js_Rbxhj_01 + js_Rbxhj_02;

            jq('.js_total').text(js_total);
            jq('.js_time').text(js_time);
            jq('#js_Lmy').text(js_Lmy);
            jq('#js_Lzlx').text(js_Lzlx);
            jq('#js_Lbxhj').text(js_Lbxhj);
            jq('#js_Rsy').text(js_Rsy);
            jq('#js_Rdj').text(js_Rdj);
            jq('#js_Rzlx').text(js_Rzlx);
            jq('#js_Rbxhj').text(js_Rbxhj);
        }
    });
}



function esfdyz1(esfd_a) {
    var cipt = jq('#efd_mj')
    var esfd_a = cipt.val();
    cipt.blur(function() {
        var Cprice_val = cipt.val();
        var Tprice_val = jq.trim(Cprice_val);
        var error_ts = jq(this).parent().parent().children('.js_error');
        if (Tprice_val == "") {
            error_ts.removeClass('none');
            error_ts.text('不能为空');
            efd_mj = false;
        } else if (Tprice_val.length > 0) {
            if (!isNaN(Tprice_val)) {
                error_ts.addClass('none');
                cipt.addClass('success_ts');
                efd_mj = true;
            } else {
                error_ts.removeClass('none');
                error_ts.text('少年，这不是一个数字');
                cipt.addClass('error_ts');
                efd_mj = false;
            }
        }
    });
    cipt.focus(function() {
        cipt.removeClass('success_ts');
        cipt.removeClass('error_ts');
    });
    return esfd_a;
}

function esfdyz2(esfd_b) {
    var cipt = jq('#efd_zj')
    var esfd_b = cipt.val();
    cipt.blur(function() {
        var Cprice_val = cipt.val();
        var Tprice_val = jq.trim(Cprice_val);
        var error_ts = jq(this).parent().parent().children('.js_error');
        if (Tprice_val == "") {
            error_ts.removeClass('none');
            error_ts.text('不能为空');
            efd_zj = false;
        } else if (Tprice_val.length > 0) {
            if (!isNaN(Tprice_val)) {
                error_ts.addClass('none');
                cipt.addClass('success_ts');
                efd_zj = true;
            } else {
                error_ts.removeClass('none');
                error_ts.text('少年，这不是一个数字');
                cipt.addClass('error_ts');
                efd_zj = false;
            }
        }
    });
    cipt.focus(function() {
        cipt.removeClass('success_ts');
        cipt.removeClass('error_ts');
    });
    return esfd_b;
}

function esfdjs(esfd_a, esfd_b) {
    var efdbtn = jq('#efd_jsbtn');
    efdbtn.click(function() {
        var js_hj01 = jq('#js_hj01');
        var js_qs01 = jq('#js_qs01');
        var js_yys01 = jq('#js_yys01');
        var js_cjs01 = jq('#js_cjs01');
        var js_jyfjs01 = jq('#js_jyfjs01');
        var js_gs01 = jq('#js_gs01');
        var js_yhs01 = jq('#js_yhs01');

        var r1 = esfdyz1(esfd_a);
        var r2 = esfdyz2(esfd_b);

        if (efd_mj == true && efd_zj == true) {
            if (jq('#jsfwxz_0').attr('checked') == 'checked') {
                if (r1 < 90) {
                    js_qs01 = Math.round(r2 * 0.01 * 10000 * 10000) / 10000;
                }
                else {
                    js_qs01 = Math.round(r2 * 0.015 * 10000 * 10000) / 10000;
                }
            } else if (jq('#jsfwxz_1').attr('checked') == 'checked') {
                js_qs01 = Math.round(r2 * 0.03 * 10000 * 10000) / 10000;
            }
            if (jq('#jshas5_0').attr('checked') == 'checked') {
                js_yys01 = 0;
                js_cjs01 = 0;
                js_jyfjs01 = 0;
                js_gs01 = 0;
                js_yhs01 = 0;
            }
            if (jq('#jshas5_1').attr('checked') == 'checked') {
                js_yys01 = Math.round(r2 * 0.0555 * 10000 * 10000) / 10000;
                js_cjs01 = Math.round(js_yys01 * 0.07 * 100000) / 100000;
                js_jyfjs01 = Math.round(js_yys01 * 0.03 * 100000) / 100000;
                js_gs01 = Math.round(r2 * 0.01 * 10000 * 10000) / 10000;
                js_yhs01 = 0;
            }
            js_hj01 = js_qs01 + js_yys01 + js_cjs01 + js_jyfjs01 + js_gs01;
            jq('#js_hj01').text(js_hj01);
            jq('#js_qs01').text(js_qs01);
            jq('#js_yys01').text(js_yys01);
            jq('#js_cjs01').text(js_cjs01);
            jq('#js_jyfjs01').text(js_jyfjs01);
            jq('#js_gs01').text(js_gs01);
            jq('#js_yhs01').text(js_yhs01);
        }

    });
}