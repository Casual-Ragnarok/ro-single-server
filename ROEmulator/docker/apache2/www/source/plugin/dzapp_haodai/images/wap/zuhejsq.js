$(document).ready(function() {
    $('#sydk_jsbtn').click(function() {
        if ($('#business_rate').val() == '' || isNaN($('#business_rate').val())) {
            $('#business_rate').val(sydk_lv);
        }
        var year_lilv = parseFloat($('#business_rate').val());
        var money = parseFloat($('#business_sum').val());
        var month = parseFloat($('#loan_type01').attr('reval'));
        var debx_or_debj = $('input:radio[name="pattern_ed"]:checked').val();
        if (debx_or_debj == 'debx') {
            debxRun(year_lilv, money, month, 'sydk');
        } else {
            debjRun(year_lilv, money, month, 'sydk');
        }
    });
    $('#sydk_mj_jsbtn').click(function() {
        if ($('#business_rate_mj').val() == '' || isNaN($('#business_rate_mj').val())) {
            $('#business_rate_mj').val(sydkmj_lv);
        }
        var year_lilv = parseFloat($('#business_rate_mj').val());
        var money = parseFloat($('#business_dkje').html());
        var month = parseFloat($('#loan_type04').attr('reval'));
        var debx_or_debj = $('input:radio[name="pattern_mj"]:checked').val();
        if (debx_or_debj == 'debx') {
            debxRun(year_lilv, money, month, 'sydk');
        } else {
            debjRun(year_lilv, money, month, 'sydk');
        }
    });
    $('#gjj_btn').click(function() {
        if ($('#gjjdk_lv').val() == '' || isNaN($('#gjjdk_lv').val())) {
            $('#gjjdk_lv').val(gjjdk_lv);
        }
        var year_lilv = parseFloat($('#gjjdk_lv').val());
        var money = parseFloat($('#gjj_eds_dkje').val());
        var month = parseFloat($('#loan_type08').attr('reval'));
        var debx_or_debj = $('input:radio[name="gongdebx"]:checked').val();
        if (debx_or_debj == 'debx') {
            debxRun(year_lilv, money, month, 'gjjdk');
        } else {
            debjRun(year_lilv, money, month, 'gjjdk');
        }
    });
    $('#zuhe_btn').click(function() {
        if ($('#zuhedk_rate').val() == '' || isNaN($('#zuhedk_rate').val())) {
            $('#zuhedk_rate').val(zuhedk_lv);
        }
        var sy_year_lilv = parseFloat($('#zuhedk_rate').val());
        var sy_money = parseFloat($('#zuhesydk').val());
        if ($('#zhgjj_ll').val() == '' || isNaN($('#zhgjj_ll').val())) {
            $('#zhgjj_ll').val(zhgjj_ll);
        }
        var gjj_year_lilv = parseFloat($('#zhgjj_ll').val());
        var gjj_money = parseFloat($('#gjj_eds_dkje').val());
        var money = parseFloat(sy_money + gjj_money);
        var month = parseFloat($('#hid_month').val());
        var year_lilv = (sy_money / money * sy_year_lilv) + (gjj_money / money * gjj_year_lilv);
        year_lilv = parseFloat(year_lilv).toFixed(5);
        var debx_or_debj = $('input:radio[name="zhdeb"]:checked').val();
        if (debx_or_debj == 'debx') {
            debxRun(year_lilv, money, month, 'zhdk');
        } else {
            debjRun(year_lilv, money, month, 'zhdk');
        }
    });
});

function debxRun(year_lilv, money, month, tab) {
    var resArr = mydebx(year_lilv, money, month);
    var listArr = resArr['list_res'];
    var simpArr = resArr['simp_res'];
    $('#sydk_yg_text_' + tab).html('&#27599;&#26376;&#26376;&#20379;');
    setResdom(simpArr, listArr, tab);
}

function debjRun(year_lilv, money, month, tab) {
    var resArr = mydebj(year_lilv, money, month);
    var listArr = resArr['list_res'];
    var simpArr = resArr['simp_res'];
    $('#sydk_yg_text_' + tab).html('&#26368;&#39640;&#26376;&#20379;');
    setResdom(simpArr, listArr, tab);
}

function mydebx(year_lilv, money, month) {
    money = (money * 10000);
    var year = month / 12;
    var year_1 = (parseInt(year / 5));
    var year_2 = (parseInt(year / 5));
    var active = year_lilv * 10 / 12 * 0.001;
    var t1 = Math.pow(1 + active, month);
    var t2 = t1 - 1;
    var tmp = t1 / t2;
    var monthratio = active * tmp;
    var monthBack = (money * monthratio).toFixed(2);
    year_lilv = year_lilv * 0.01;
    var yue_lilv = ((year_lilv / 12));
    var objArray = new Array();
    var ljch_bj = 0;
    var pre_sybj = 0;
    var i = 1;
    for (i = 1; i <= month; i++) {
        objArray[i - 1] = new Array();
        objArray[i - 1]['qc'] = i;
        objArray[i - 1]['chbx'] = monthBack;
        if (i == 1) {
            pre_sybj = money;
        } else {
            pre_sybj = objArray[i - 2]['sybj'];
        }
        objArray[i - 1]['chlx'] = (pre_sybj * yue_lilv).toFixed(2);
        var chbj = (objArray[i - 1]['chbx'] - objArray[i - 1]['chlx']);
        objArray[i - 1]['chbj'] = chbj.toFixed(2);
        ljch_bj += chbj;
        var sybj = (money - ljch_bj);
        objArray[i - 1]['sybj'] = sybj.toFixed(2);
        if (sybj <= 1) {
            objArray[i - 1]['sybj'] = 0.00;
        }
    }
    var yg = monthBack;
    var ljhkze = monthBack * month;
    var lxze = ljhkze - money;
    var yxxdy = monthBack * 2;
    var resArray = new Array();
    resArray['simp_res'] = new Array();
    resArray['list_res'] = new Array();
    resArray['simp_res']['yg'] = parseFloat(yg).toFixed(0);
    resArray['simp_res']['ljhkze'] = parseFloat(ljhkze).toFixed(0);
    resArray['simp_res']['lxze'] = parseFloat(lxze).toFixed(0);
    resArray['simp_res']['yxxdy'] = parseFloat(yxxdy).toFixed(0);
    resArray['list_res'] = objArray;
    return resArray;
}

function  mydebj(year_lilv, money, month) {
    money = money * 10000;
    var year = month / 12;
    var year_1 = (parseInt(year / 5));
    var year_2 = (parseInt(year / 5));
    var active = year_lilv * 10 / 12 * 0.001;
    var objArray = new Array();
    var interestM = 0;
    var interestTotal = 0;
    var chbj = money / month;
    for (var i = 1; i <= month; i++) {
        var t1 = (money - money * (i - 1) / month) * active;
        interestM = money / month + t1;
        objArray[i - 1] = new Array();
        objArray[i - 1]['qc'] = i;
        objArray[i - 1]['chbx'] = (interestM).toFixed(2);
        objArray[i - 1]['chlx'] = (interestM - chbj).toFixed(2);
        objArray[i - 1]['chbj'] = (chbj).toFixed(2);
        objArray[i - 1]['sybj'] = (money - (chbj * i)).toFixed(2);
        if (objArray[i - 1]['sybj'] <= 1) {
            objArray[i - 1]['sybj'] = 0.00;
        }
        interestTotal = interestTotal + interestM;
    }
    interestTotal = (Math.round(interestTotal * 100)) / 100;
    var yg = objArray[0]['chbx'];
    var ljhkze = interestTotal;
    var lxze = (ljhkze - money);
    lxze = lxze.toFixed(0);
    var yxxdy = (parseFloat(objArray[0]['chbx']) + parseFloat(objArray[month - 1]['chbx']));
    yxxdy = yxxdy.toFixed(0);
    var resArray = new Array();
    resArray['simp_res'] = new Array();
    resArray['list_res'] = new Array();
    resArray['simp_res']['yg'] = parseFloat(yg).toFixed(0);
    resArray['simp_res']['ljhkze'] = parseFloat(ljhkze).toFixed(0);
    resArray['simp_res']['lxze'] = parseFloat(lxze).toFixed(0);
    resArray['simp_res']['yxxdy'] = parseFloat(yxxdy).toFixed(0);
    resArray['list_res'] = objArray;
    return resArray;
}

function setResdom(simpArr, listArr, tab) {
    $("#" + tab + "_lxze").html(simpArr['lxze']).hide().fadeIn("slow");
    $("#" + tab + "_myyg").html(simpArr['yg']).hide().fadeIn("slow");
    $("#" + tab + "_ljhk").html(simpArr['ljhkze']).hide().fadeIn("slow");
    $("#" + tab + "_yxxdy").html(simpArr['yxxdy']).hide().fadeIn("slow");
    var table_trs = '';
    for (var i = 0; i < listArr.length; i++) {
        table_trs += "<tr><td>" + listArr[i]['qc'] + "</td><td>" + listArr[i]['chbx'] + "</td><td>" + listArr[i]['chlx'] +
                "</td><td>" + listArr[i]['chbj'] + "</td><td>" + listArr[i]['sybj'] + "</td></tr>";
    }
    $("#hd_tbody_" + tab).html('');
    $("#hd_tbody_" + tab).html(table_trs).hide();
    $("#hd_tbody_" + tab).html(table_trs).fadeIn("slow");
    $("#hd_tbody_sydk tr:odd").css('background-color', '#dce9f1');
    $("#hd_tbody_gjjdk tr:odd").css('background-color', '#dce9f1');
    $("#hd_tbody_zhdk tr:odd").css('background-color', '#dce9f1');
}
function MQxian(obj) {
    var QixianObj = $('[class="SelectCon"]').val();
    $('#hid_month').val(QixianObj);
}
function LoanLV(obj) {
    var Loanlvobj = $('[name="lv_type"]').val();
    $('#zuhedk_rate').val(Loanlvobj);
}
function ClearData(obj) {
    $('#zuhesydk').attr('value', '');
}
function AddData(obj) {
    if ($('#zuhesydk').val() == '')
        $('#zuhesydk').val('100');
}
function ClearD(obj) {
    $('#gjj_eds_dkje').attr('value', '');
}
function AddD(obj) {
    if ($('#gjj_eds_dkje').val() == '')
        $('#gjj_eds_dkje').val('50');
}
function jisyinc(obj) {
    var obj = $(obj);
    var moe = obj.parents('.prod_box').children('.mat02');
    var moed = obj.parents('.prod_box').children('.mat01');
    moe.show();
    moed.hide();
    $('.backbtn').removeClass('none');
    $('.backbtnn').removeClass('none');
}
function backHome(obj) {
    var obj = $(obj);
    var moe = $('.prod_box').children('.mat02');
    var moed = $('.prod_box').children('.mat01');
    moed.show();
    moe.hide();
    $('.backbtn').addClass('none');
    $('.backbtnn').addClass('none');
    $('.Ptop').removeClass('none');
}