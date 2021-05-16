var gjj_a = 100;
var gongjj_je = 100;
var gjj_b2 = 240;
var zuhe_b1 = 240;
var sydk_lv = 6.55;
var sydkmj_lv = 6.55;
var sydkmj_etflv = 7.926;
var zuhedk_lv = 6.55;
var gjjdk_lv = 4.50;
var zhgjj_ll = 4.50;
var mj_sf = 0.3;
var etfmj_sf = 0.6;
var gjjmj_sf = 0.3;
var gjjmj_etf_sf = 0.6;
var gjj_bmj = 240;
var etf_bmj = 240;
var gjj_gjj = 240;
var gjmj_gjj = 240;
var gjmjetf_gjj = 240;
var gjj_pmdj = true;
var gjj_mj = true;
var gjj_zj = true;
var gjj_dkje = true;
var gjj_je = true;
var dk_lilv = true;
var business_sum = true;
var gjj_eds_dkje = true;
var gong_mj = true;
var gong_pmdj = true;
var zuhesydk = true;
var zhgjjdk = true;
$(document).ready(function() {
    szyz();
    szyz_mj();
    szyz_pfdj();
    szyz_fwzj();
    szyz_gongjj();
    syyz_gjjmj();
    szyz_gjjpfdj();
    szyz_zhsy();
    szyz_zhgjj();
    kuanTab();
    $($("#tab_one").find("input[name='pattern_jisuan']")[0]).attr("checked", true);
    $($("#tab_tow").find("input[name='pattern_tow']")[0]).attr("checked", true);
    $($("#edfangshi").find("input[name='pattern_ed']")[0]).attr("checked", true);
    $($("#mjfangshi").find("input[name='pattern_mj']")[0]).attr("checked", true);
    $($("#gjjfangshi").find("input[name='gongdebx']")[0]).attr("checked", true);
    $($("#taofang").find("input[name='pattern_ytf']")[0]).attr("checked", true);
    $($("#gjjgfxz").find("input[name='ax-pattern']")[0]).attr("checked", true);
    $($("#gjjf_de").find("input[name='gjjf_de']")[0]).attr("checked", true);
    $($("#zuhede").find("input[name='zhdeb']")[0]).attr("checked", true);
    $($("#etfmjfangshi").find("input[name='pattern_mj']")[0]).attr("checked", true);
    $($("#gjjetf_fangshi").find("input[name='gjjetf_de']")[0]).attr("checked", true);
});
function kuanTab() {
    var Li = $('#nav_list li');
    Li.click(function() {
        var fgd = $(this).hasClass('fjg');
        var num = $(this).index();
        var thisCon = Li.parents('#content').children('.loan_con').eq(num);
        if (fgd == false) {
            Li.removeClass('fjg');
            $(this).addClass('fjg');
            $('.loan_con').hide();
            thisCon.show();
        };
    });
}

function dkjCK(obj) {
    var obj = $(obj);
    var xsuo = obj.parent().children('.loan_div').children('.loan_li_selectcon');
    var ads = xsuo.hasClass('none');
    if (ads == true) {
        xsuo.removeClass('none');
    } else {
        xsuo.addClass('none');
    }
}

function lotypeSelect(obj) {
    var obj = $(obj);
    var hjm = obj.parent().parent('.loan_li_selectcon');
    hjm.addClass('none');
    var sey_word = $('#loan_type01').html();
    var This_word = obj.html();
    $('#loan_type01').html(This_word);
    var sey_reval = $('#loan_type01').attr('reval');
    var This_reval = obj.attr('reval');
    $('#loan_type01').attr('reval', This_reval);
}
function ynewSelect() {
    $('#lv_pop_list').click(function() {
        var js_yon = $('#lv_list_options').hasClass('none');
        if (js_yon == true) {
            $('#lv_list_options').removeClass('none');
        } else {
            $('#lv_list_options').addClass('none');
        }

    });
}
function zhdkll(zhlv) {
    var bthis = $(zhlv);
    var js_reval = bthis.attr('reval');
    var jsshow = $('#loan_type07');
    var jsli = $('#zhdkll_list li a');
    var jsul = $('#zhdkll_list');
    var jsshow_reval = jsshow.attr('reval');
    var jscontent = $('#zuhedk_rate');
    jscontent.val(js_reval);
    jscontent = js_reval;
    jsshow_reval = js_reval;
    zuhedk_lv = jsshow_reval;
    jsshow.text(bthis.text());
    jsli.removeClass('mo');
    bthis.addClass('mo');
    jsul.addClass('none');
    jsshow.attr('reval', zuhedk_lv);
    return zuhedk_lv;
}
function sydk(sylv) {
    var bthis = $(sylv);
    var js_reval = bthis.attr('reval');
    var jsshow = $('#loan_type02');
    var jsli = $('#lv_list_options li a');
    var jsul = $('#lv_list_options');
    var jsshow_reval = jsshow.attr('reval');
    var jscontent = $('#business_rate');
    jscontent.val(js_reval);
    jscontent = js_reval;
    jsshow_reval = js_reval;
    sydk_lv = jsshow_reval;
    jsshow.text(bthis.text());
    jsli.removeClass('mo');
    bthis.addClass('mo');
    jsul.addClass('none');
    jsshow.attr('reval', sydk_lv);
    return sydk_lv;
}
function sydkmj(sylv) {
    var bthis = $(sylv);
    var js_reval = bthis.attr('reval');
    var jsshow = $('#loan_type05');
    var jsli = $('#sydkmj_list li a');
    var jsul = $('#sydkmj_list');
    var jsshow_reval = jsshow.attr('reval');
    var jscontent = $('#business_rate_mj');
    jscontent.val(js_reval);
    jscontent = js_reval;
    jsshow_reval = js_reval;
    sydkmj_lv = jsshow_reval;
    jsshow.text(bthis.text());
    jsli.removeClass('mo');
    bthis.addClass('mo');
    jsul.addClass('none');
    jsshow.attr('reval', sydkmj_lv);
    return sydkmj_lv;
}

function sydkmjetf(sylv) {
    var bthis = $(sylv);
    var js_reval = bthis.attr('reval');
    var jsshow = $('#loan_type55');
    var jsli = $('#sydkmj_etflist li a');
    var jsul = $('#sydkmj_etflist');
    var jsshow_reval = jsshow.attr('reval');
    var jscontent = $('#business_rate_etfmj');
    jscontent.val(js_reval);
    jscontent = js_reval;
    jsshow_reval = js_reval;
    sydkmj_etflv = jsshow_reval;
    jsshow.text(bthis.text());
    jsli.removeClass('mo');
    bthis.addClass('mo');
    jsul.addClass('none');
    jsshow.attr('reval', sydkmj_etflv);
    return sydkmj_etflv;
}
function sydk_mjsf(mjsf) {
    var bthis = $(mjsf);
    var js_reval = bthis.attr('reval');
    var jsshow = $('#loan_type03');
    var jsli = $('#lv_list_sf li a');
    var jsul = $('#lv_list_sf');
    var jsshow_reval = jsshow.attr('reval');
    jsshow_reval = js_reval;
    mj_sf = jsshow_reval;
    jsshow.text(bthis.text());
    jsli.removeClass('mo');
    bthis.addClass('mo');
    jsul.addClass('none');
    jsshow.attr('reval', mj_sf);
    return mj_sf;
}

function sydk_etfjsf(mjsf) {
    var bthis = $(mjsf);
    var js_reval = bthis.attr('reval');
    var jsshow = $('#loan_type22');
    var jsli = $('#etf_list_sf li a');
    var jsul = $('#etf_list_sf');
    var jsshow_reval = jsshow.attr('reval');
    jsshow_reval = js_reval;
    etfmj_sf = jsshow_reval;
    jsshow.text(bthis.text());
    jsli.removeClass('mo');
    bthis.addClass('mo');
    jsul.addClass('none');
    jsshow.attr('reval', etfmj_sf);
    return etfmj_sf;
}
function sydk_gjjmjsf(mjsf) {
    var bthis = $(mjsf);
    var js_reval = bthis.attr('reval');
    var jsshow = $('#funds_type');
    var jsli = $('#funds_type_list li a');
    var jsul = $('#funds_type_list');
    var jsshow_reval = jsshow.attr('reval');
    jsshow_reval = js_reval;
    gjjmj_sf = jsshow_reval;
    jsshow.text(bthis.text());
    jsli.removeClass('mo');
    bthis.addClass('mo');
    jsul.addClass('none');
    jsshow.attr('reval', gjjmj_sf);
    return gjjmj_sf;
}

function gjjdk_etf(mjsf) {
    var bthis = $(mjsf);
    var js_reval = bthis.attr('reval');
    var jsshow = $('#funds_etf_type');
    var jsli = $('#funds_etf_type_list li a');
    var jsul = $('#funds_etf_type_list');
    var jsshow_reval = jsshow.attr('reval');
    jsshow_reval = js_reval;
    gjjmj_etf_sf = jsshow_reval;
    jsshow.text(bthis.text());
    jsli.removeClass('mo');
    bthis.addClass('mo');
    jsul.addClass('none');
    return gjjmj_etf_sf;
}

function shoufueSelect(obj) {
    var obj = $(obj);
    var hjm = obj.parent().parent('.loan_li_selectcon');
    hjm.addClass('none');
    var sey_word = $('#loan_type03').html();
    var This_word = obj.html();
    $('#loan_type03').html(This_word);
    var sey_reval = $('#loan_type03').attr('reval');
    var This_reval = obj.attr('reval');
}
function qitimeSelect(obj) {
    var obj = $(obj);
    var hjm = obj.parent().parent('.loan_li_selectcon');
    hjm.addClass('none');
    var sey_word = $('#loan_type04').html();
    var This_word = obj.html();
    $('#loan_type04').html(This_word);
    var sey_reval = $('#loan_type04').attr('reval');
    var This_reval = obj.attr('reval');
    $('#loan_type04').attr('reval', This_reval);
}
function yearlSelect(obj) {
    var obj = $(obj);
    var hjm = obj.parent().parent('.loan_li_selectcon');
    hjm.addClass('none');
    var sey_word = $('#loan_type05').html();
    var This_word = obj.html();
    $('#loan_type05').html(This_word);
    var sey_reval = $('#loan_type05').attr('reval');
    var This_reval = obj.attr('reval');
    $('#loan_type05').attr('reval', This_reval);
}
function symonth(obj) {
    var obj = $(obj);
    var hjm = obj.parent().parent('.loan_li_selectcon');
    hjm.addClass('none');
    var sey_word = $('#loan_type08').html();
    var This_word = obj.html();
    $('#loan_type08').html(This_word);
    var sey_reval = $('#loan_type08').attr('reval');
    var This_reval = obj.attr('reval');
    $('#loan_type08').attr('reval', This_reval);
}
function daikSelect(obj) {
    var obj = $(obj);
    var hjm = obj.parent().parent('.loan_li_selectcon');
    hjm.addClass('none');
    var sey_word = $('#loan_type09').html();
    var This_word = obj.html();
    $('#loan_type09').html(This_word);
    var sey_reval = $('#loan_type09').attr('reval');
    var This_reval = obj.attr('reval');
    $('#loan_type09').attr('reval', This_reval);
}
function zuhedkeSelect(obj) {
    var obj = $(obj);
    var hjm = obj.parent().parent('.loan_li_selectcon');
    hjm.addClass('none');
    var sey_word = $('#loan_type06').html();
    var This_word = obj.html();
    $('#loan_type06').html(This_word);
    var sey_reval = $('#loan_type06').attr('reval');
    var This_reval = obj.attr('reval');
    $('#loan_type06').attr('reval', This_reval);
}
function zuheyearSelect(obj) {
    var obj = $(obj);
    var hjm = obj.parent().parent('.loan_li_selectcon');
    hjm.addClass('none');
    var sey_word = $('#loan_type07').html();
    var This_word = obj.html();
    $('#loan_type07').html(This_word);
    var sey_reval = $('#loan_type07').attr('reval');
    var This_reval = obj.attr('reval');
    $('#loan_type07').attr('reval', This_reval);
}
function fundsSelect(obj) {
    var obj = $(obj);
    var hjm = obj.parent().parent('.loan_li_selectcon');
    hjm.addClass('none');
    var sey_word = $('#funds_type').html();
    var This_word = obj.html();
    $('#funds_type').html(This_word);
    var sey_reval = $('#funds_type').attr('reval');
    var This_reval = obj.attr('reval');
    $('#funds_type').attr('reval', This_reval);
}
function fundsdkSelect(obj) {
    var obj = $(obj);
    var hjm = obj.parent().parent('.loan_li_selectcon');
    hjm.addClass('none');
    var sey_word = $('#loan_funds').html();
    var This_word = obj.html();
    $('#loan_funds').html(This_word);
    var sey_reval = $('#loan_funds').attr('reval');
    var This_reval = obj.attr('reval');
    $('#loan_funds').attr('reval', This_reval);
}

function dkmoney(obj) {
    czjsq();
    gjj_czjs();
    var obj = $(obj);
    var mo = obj.parents().children('.dkmoney');
    var me = obj.parents().children('.area');
    me.hide();
    mo.show();
}
function area(obj) {
    czjsq();
    gjj_czjs();
    var obj = $(obj);
    var mo = obj.parents().children('.dkmoney');
    var me = obj.parents().children('.area');
    mo.hide();
    me.show();
}

function czjsq() {
    $('#business_sum').val('100');
    $('#ks_year_1').text(0);
    $('#ks_year_2').text(0);
    $('#ks_year').text(0);
    $('#ks_zlx_1').text(0);
    $('#ks_zlx_2').text(0);
    $('#ks_zlx').text(0);
    $('#sy_lxze').text(0);
    $('#sy_myyg').text(0);
    $('#sy_ljhk').text(0);
    $('#sy_yxxdy').text(0);
    $('#business_mj').val('');
    $('#business_pmdj').val('');
    $('#mj_fwzj').text(0);
    $('#business_dkje').text(0);
}

function gjj_czjs() {
    $('#gjj_eds_dkje').val('100');
    $('#gjj_year_1').text(0);
    $('#gjj_year_2').text(0);
    $('#gjj_year').text(0);
    $('#gjj_zlx_1').text(0);
    $('#gjj_zlx_2').text(0);
    $('#gjj_zlx').text(0);
    $('#gjj_lxze').text(0);
    $('#gjj_myyg').text(0);
    $('#gjj_ljhk').text(0);
    $('#gjj_yxxdy').text(0);
    $('#business_gjjmj').val('');
    $('#business_gjjpfdj').val('');
    $('#gjjmj_fwzj').text(0);
    $('#gjj_dkje').text(0);

}

function zuhe_cjjs() {
    $('#zuhesydk').val('');
    $('#zhgjjdk').val('');
}
function sydklv() {
    $('#lv_pop_list').click(function() {
        var js_yon = $('#lv_list_options').hasClass('none');
        if (js_yon == true) {
            $('#lv_list_options').removeClass('none');
        } else {
            $('#lv_list_options').addClass('none');
        }
    });
}
function sy_month(gjjbb) {
    var bthis = $(gjjbb);
    var js_reval = bthis.attr('reval');
    var jsshow = $('#loan_type01');
    var jsli = $('#sydk_options li a');
    var jsul = $('#sydk_options');
    var jsshow_reval = jsshow.attr('reval');
    jsshow_reval = js_reval;
    gjj_b2 = jsshow_reval;
    jsshow.text(bthis.text());
    jsli.removeClass('mo');
    bthis.addClass('mo');
    jsul.addClass('none');
    jsshow.attr('reval', gjj_b2);
    return gjj_b2;
}
function symj_month(gjjbb) {
    var bthis = $(gjjbb);
    var js_reval = bthis.attr('reval');
    var jsshow = $('#loan_type04');
    var jsli = $('#symj_qx_list li a');
    var jsul = $('#symj_qx_list');
    var jsshow_reval = jsshow.attr('reval');
    jsshow_reval = js_reval;
    gjj_bmj = jsshow_reval;
    jsshow.text(bthis.text());
    jsli.removeClass('mo');
    bthis.addClass('mo');
    jsul.addClass('none');
    jsshow.attr('reval', gjj_bmj);
    return gjj_bmj;
}
function symjetf_month(gjjbb) {
    var bthis = $(gjjbb);
    var js_reval = bthis.attr('reval');
    var jsshow = $('#loan_type44');
    var jsli = $('#etf_qx_list li a');
    var jsul = $('#etf_qx_list');
    var jsshow_reval = jsshow.attr('reval');
    jsshow_reval = js_reval;
    etf_bmj = jsshow_reval;
    jsshow.text(bthis.text());
    jsli.removeClass('mo');
    bthis.addClass('mo');
    jsul.addClass('none');
    jsshow.attr('reval', etf_bmj);
    return etf_bmj;
}

function gjjdk_month(gjjbb) {
    var bthis = $(gjjbb);
    var js_reval = bthis.attr('reval');
    var jsshow = $('#loan_type08');
    var jsli = $('#gjjdk_list li a');
    var jsul = $('#gjjdk_list');
    var jsshow_reval = jsshow.attr('reval');
    jsshow_reval = js_reval;
    gjj_gjj = jsshow_reval;
    jsshow.text(bthis.text());
    jsli.removeClass('mo');
    bthis.addClass('mo');
    jsul.addClass('none');
    jsshow.attr('reval', gjj_gjj);
    return gjj_gjj;
}
function gjjmjdk_month(gjjbb) {
    var bthis = $(gjjbb);
    var js_reval = bthis.attr('reval');
    var jsshow = $('#loan_type09');
    var jsli = $('#gjjmjdk_list li a');
    var jsul = $('#gjjmjdk_list');
    var jsshow_reval = jsshow.attr('reval');
    jsshow_reval = js_reval;
    gjmj_gjj = jsshow_reval;
    jsshow.text(bthis.text());
    jsli.removeClass('mo');
    bthis.addClass('mo');
    jsul.addClass('none');
    jsshow.attr('reval', gjmj_gjj);
    return gjmj_gjj;
}
function gjjmjetf_month(gjjbb) {
    var bthis = $(gjjbb);
    var js_reval = bthis.attr('reval');
    var jsshow = $('#loan_type99');
    var jsli = $('#gjjmjetf_list li a');
    var jsul = $('#gjjmjetf_list');
    var jsshow_reval = jsshow.attr('reval');
    jsshow_reval = js_reval;
    gjmjetf_gjj = jsshow_reval;
    jsshow.text(bthis.text());
    jsli.removeClass('mo');
    bthis.addClass('mo');
    jsul.addClass('none');
    jsshow.attr('reval', gjmjetf_gjj);
    return gjmjetf_gjj;
}
function zh_month(gjjbb) {
    var bthis = $(gjjbb);
    var js_reval = bthis.attr('reval');
    var jsshow = $('#loan_type06');
    var jsli = $('#zuhe_year_list li a');
    var jsul = $('#zuhe_year_list');
    var jsshow_reval = jsshow.attr('reval');
    jsshow_reval = js_reval;
    zuhe_b1 = jsshow_reval;
    jsshow.text(bthis.text());
    jsli.removeClass('mo');
    bthis.addClass('mo');
    jsul.addClass('none');
    jsshow.attr('reval', zuhe_b1);
    return zuhe_b1;
}
function szyz(gjj_a) {
    var cipt = $('#business_sum');
    var gjj_a = cipt.val();
    cipt.blur(function() {
        var Cprice_val = cipt.val();
        var Tprice_val = $.trim(Cprice_val);
        var error_ts = $('.js_error');
        if (Tprice_val == "") {
            error_ts.removeClass('none');
            error_ts.text('金额不能为空');
            business_sum = false;
        } else if (Tprice_val.length > 0) {
            if (!isNaN(Tprice_val)) {
                error_ts.addClass('none');
                cipt.addClass('success_ts');
                business_sum = true;
            } else {
                error_ts.removeClass('none');
                error_ts.text('请输入数字');
                cipt.addClass('error_ts');
                business_sum = false;
            }
        }
    });
    cipt.focus(function() {
        cipt.removeClass('success_ts');
        cipt.removeClass('error_ts');
    });
    return gjj_a;
}

function szyz_mj(gjj_e) {
    var cipt = $('#business_mj')
    var gjj_e = cipt.val();
    cipt.blur(function() {
        var Cprice_val = cipt.val();
        var Tprice_val = $.trim(Cprice_val);
        var error_ts = $('.jsmj_error');
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
                error_ts.text('请输入数字');
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
function syyz_gjjmj(gongjj_mj) {
    var cipt = $('#business_gjjmj')
    var gongjj_mj = cipt.val();
    cipt.blur(function() {
        var Cprice_val = cipt.val();
        var Tprice_val = $.trim(Cprice_val);
        var error_ts = $('.gongmj_error');
        if (Tprice_val == "") {
            error_ts.removeClass('none');
            error_ts.text('不能为空');
            gong_mj = false;
        } else if (Tprice_val.length > 0) {
            if (!isNaN(Tprice_val)) {
                error_ts.addClass('none');
                cipt.addClass('success_ts');
                gong_mj = true;
            } else {
                error_ts.removeClass('none');
                error_ts.text('请输入数字');
                cipt.addClass('error_ts');
                gong_mj = false;
            }
        }
    });
    cipt.focus(function() {
        cipt.removeClass('success_ts');
        cipt.removeClass('error_ts');
    });
    return gongjj_mj;
}
function szyz_pfdj(gjj_d) {
    var cipt = $('#business_pmdj')
    var gjj_d = cipt.val();
    cipt.blur(function() {
        var Cprice_val = cipt.val();
        var Tprice_val = $.trim(Cprice_val);
        var error_ts = $('.jspfdj_error');
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
                error_ts.text('请输入数字');
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

function szyz_gjjpfdj(gjj_pfdj) {
    var cipt = $('#business_gjjpfdj')
    var gjj_pfdj = cipt.val();
    cipt.blur(function() {
        var Cprice_val = cipt.val();
        var Tprice_val = $.trim(Cprice_val);
        var error_ts = $('.gongpfdj_error');
        if (Tprice_val == "") {
            error_ts.removeClass('none');
            error_ts.text('不能为空');
            gong_pmdj = false;
        } else if (Tprice_val.length > 0) {
            if (!isNaN(Tprice_val)) {
                error_ts.addClass('none');
                cipt.addClass('success_ts');
                gong_pmdj = true;
            } else {
                error_ts.removeClass('none');
                error_ts.text('请输入数字');
                cipt.addClass('error_ts');
                gong_pmdj = false;
            }
        }
    });
    cipt.focus(function() {
        cipt.removeClass('success_ts');
        cipt.removeClass('error_ts');
    });
    return gjj_pfdj;
}
function szyz_fwzj(gjj_z) {
    var cipt = $('#business_fwzj')
    var gjj_z = cipt.val();
    cipt.blur(function() {
        var Cprice_val = cipt.val();
        var Tprice_val = $.trim(Cprice_val);
        var error_ts = $('.jsfwzj_error');
        if (Tprice_val == "") {
            error_ts.removeClass('none');
            error_ts.text('不能为空');
            gjj_zj = false;
        } else if (Tprice_val.length > 0) {
            if (!isNaN(Tprice_val)) {
                error_ts.addClass('none');
                cipt.addClass('success_ts');
                gjj_zj = true;
            } else {
                error_ts.removeClass('none');
                error_ts.text('请输入数字');
                cipt.addClass('error_ts');
                gjj_zj = false;
            }
        }
    });
    cipt.focus(function() {
        cipt.removeClass('success_ts');
        cipt.removeClass('error_ts');
    });
    return gjj_z;
}

function szyz_gongjj(gongjj_je) {
    var cipt = $('#gjj_eds_dkje')
    var gongjj_je = cipt.val();
    cipt.blur(function() {
        var Cprice_val = cipt.val();
        var Tprice_val = $.trim(Cprice_val);
        var error_ts = $('.gongjj_error');
        if (Tprice_val == "") {
            error_ts.removeClass('none');
            error_ts.text('不能为空');
            gjj_eds_dkje = false;
        } else if (Tprice_val.length > 0) {
            if (!isNaN(Tprice_val)) {
                error_ts.addClass('none');
                cipt.addClass('success_ts');
                gjj_eds_dkje = true;
            } else {
                error_ts.removeClass('none');
                error_ts.text('请输入数字');
                cipt.addClass('error_ts');
                gjj_eds_dkje = false;
            }
        }
    });
    cipt.focus(function() {
        cipt.removeClass('success_ts');
        cipt.removeClass('error_ts');
    });
    return gongjj_je;
}

function szyz_zhsy(zhsy_a) {
    var cipt = $('#zuhesydk');
    var zhsy_a = cipt.val();
    cipt.blur(function() {
        var Cprice_val = cipt.val();
        var Tprice_val = $.trim(Cprice_val);
        var error_ts = $('.zuhesydk_error');
        if (Tprice_val == "") {
            error_ts.removeClass('none');
            error_ts.text('金额不能为空');
            zuhesydk = false;
        } else if (Tprice_val.length > 0) {
            if (!isNaN(Tprice_val)) {
                error_ts.addClass('none');
                cipt.addClass('success_ts');
                zuhesydk = true;
            } else {
                error_ts.removeClass('none');
                error_ts.text('请输入数字');
                cipt.addClass('error_ts');
                zuhesydk = false;
            }
        }
    });
    cipt.focus(function() {
        cipt.removeClass('success_ts');
        cipt.removeClass('error_ts');
    });
    return zhsy_a;
}
function szyz_zhgjj(zhsy_gjj) {
    var cipt = $('#zhgjjdk');
    var zhsy_gjj = cipt.val();
    cipt.blur(function() {
        var Cprice_val = cipt.val();
        var Tprice_val = $.trim(Cprice_val);
        var error_ts = $('.zuhegjjdk_error');
        if (Tprice_val == "") {
            error_ts.removeClass('none');
            error_ts.text('金额不能为空');
            zhgjjdk = false;
        } else if (Tprice_val.length > 0) {
            if (!isNaN(Tprice_val)) {
                error_ts.addClass('none');
                cipt.addClass('success_ts');
                zhgjjdk = true;
            } else {
                error_ts.removeClass('none');
                error_ts.text('请输入数字');
                cipt.addClass('error_ts');
                zhgjjdk = false;
            }
        }
    });
    cipt.focus(function() {
        cipt.removeClass('success_ts');
        cipt.removeClass('error_ts');
    });
    return zhsy_gjj;
}

function yixztf(obj) {
    var obj = $(obj);
    var mo = obj.parents('.area').children('.yi_taofang');
    var me = obj.parents('.area').children('.er_taofang');
    me.hide();
    mo.show();

}
function erxztf(obj) {
    var obj = $(obj);
    var mo = obj.parents('.area').children('.yi_taofang');
    var me = obj.parents('.area').children('.er_taofang');
    mo.hide();
    me.show();
}