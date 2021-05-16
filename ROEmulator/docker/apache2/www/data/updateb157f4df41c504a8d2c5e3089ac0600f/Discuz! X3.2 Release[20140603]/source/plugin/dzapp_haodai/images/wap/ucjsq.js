var gjj_eds_dkje = true;
var zhgjj_ll = true;
var zuhesydk = true;
var zuhedk_rate = true;
var car_price = true;
var car_rate = true;
var pop_list = true;
var gjjdk_lv = 4.50;

function MQxian(obj) {
    var QixianObj = $('[name="QX_type"]').val();
    $('#hid_month').val(QixianObj);
}
function LoanLV(obj) {
    var Loanlvobj = $('[name="lv_type"]').val();
    $('#zuhedk_rate').val(Loanlvobj);
}
function carxian(obj) {
    var QixianObj = $('[name="carQX_type"]').val();
    $('#car_month').val(QixianObj);
}
function carshoufu(obj) {
    var ShoufuObj = $('[name="carSF_type"]').val();
    $('#car_sf').val(ShoufuObj);
}
function carLoanLV(obj) {
    var Loanlvobj = $('[name="carlv_type"]').val();
    $('#car_rate').val(Loanlvobj);
}
function ClearData(obj) {
}
function AddData(obj) {
    if ($('#zuhesydk').val() == '')
        $('#zuhesydk').val('0');
}
function ClearD(obj) {
    $('#gjj_eds_dkje').attr('value', '');
}
function AddD(obj) {
    if ($('#gjj_eds_dkje').val() == '')
        $('#gjj_eds_dkje').val('0');
}
function backHome(obj) {
    $('.con_n').removeClass('none');
    $('.conresult').addClass('none');
    $('.uctype').removeClass('none');
    $('.head').addClass('none');
    $('.concarresult').addClass('none');
    $('#xf_prolist').addClass('none');
}


$(document).ready(function() {
    $('#zuhe_btn').click(function() {
        var shangye_money = $('#zuhesydk').val();
        var gjj_money = $('#gjj_eds_dkje').val();
        if (shangye_money == 0 && gjj_money == 0) {
            alert('请输入贷款金额');
            return false;
        }

        $('.conresult').removeClass('none');
        $('.con_n').addClass('none');
        $('.head').removeClass('none');
        $('.uctype').addClass('none');
        var js_total = $('.js_total');
        var js_time = $('.js_time');
        var js_Lmy = $('#js_Lmy');
        var js_Rsy = $('#js_Rsy');
        var js_Rdj = $('#js_Rdj');
        var js_Lzlx = $('#js_Lzlx');
        var js_Rzlx = $('#js_Rzlx');
        var js_Lbxhj = $('#js_Lbxhj');
        var js_Rbxhj = $('#js_Rbxhj');

        if ($('#zuhedk_rate').val() == '' || isNaN($('#zuhedk_rate').val())) {
            $('#zuhedk_rate').val(zuhedk_lv);
        }
        var r3 = parseFloat($('#zuhesydk').val());
        var r4 = parseFloat($('#zuhedk_rate').val());
        if ($('#zhgjj_ll').val() == '' || isNaN($('#zhgjj_ll').val())) {
            $('#zhgjj_ll').val(zhgjj_ll);
        }
        var r0 = parseFloat($('#hid_month').val());
        var r1 = parseFloat($('#gjj_eds_dkje').val());
        var r2 = parseFloat($('#zhgjj_ll').val());
        if (zuhesydk == true && gjj_eds_dkje == true && zhgjj_ll == true && zuhedk_rate == true) {
            var js_total_01 = r1 * 10000 / 10000;
            js_time = r0 * 10000 / 10000;
            var yll_01 = ((r2 * 0.01) / 12) * 10000 / 10000;
            var t_01 = Math.pow((1 + yll_01), js_time);
            var js_Lmy_01 = (js_total_01 * yll_01 * (t_01 / (t_01 - 1))) * 10000 / 10000;

            var js_Lzlx_00_01 = (js_time * js_Lmy_01) - js_total_01;
            var js_Lzlx_01 = js_Lzlx_00_01 * 10000 / 10000;

            var js_Lbxhj_01 = js_Lzlx_01 + js_total_01;
            var js_mbj_01 = js_total_01 / js_time;
            var js_Rsy_00_01 = js_mbj_01 + (js_total_01 * yll_01);
            var js_Rsy_01 = js_Rsy_00_01 * 10000 / 10000;
            var js_Rdj_00_01 = (js_mbj_01 + (js_total_01 - js_mbj_01) * yll_01) - (js_mbj_01 + (js_total_01 - js_mbj_01 * 2) * yll_01);
            var js_Rdj_01 = js_Rdj_00_01 * 10000 / 10000;
            var js_Rzlx_00_01 = js_total_01 * yll_01 * (js_time + 1) / 2;
            var js_Rzlx_01 = js_Rzlx_00_01 * 10000 / 10000;
            var js_Rbxhj_01 = js_Rzlx_01 + js_total_01;
            var js_total_02 = r3 * 10000 / 10000;
            var yll_02 = ((r4 * 0.01) / 12) * 10000 / 10000;
            var t_02 = Math.pow((1 + yll_02), js_time);//alert(t_02);
            var js_Lmy_02 = (js_total_02 * yll_02 * (t_02 / (t_02 - 1)) * 10000) / 10000;
            var js_Lzlx_00_02 = (js_time * js_Lmy_02) - js_total_02;
            var js_Lzlx_02 = js_Lzlx_00_02 * 10000 / 10000;

            var js_Lbxhj_02 = js_Lzlx_02 + js_total_02;
            var js_mbj_02 = js_total_02 / js_time;
            var js_Rsy_00_02 = js_mbj_02 + (js_total_02 * yll_02);
            var js_Rsy_02 = js_Rsy_00_02 * 10000 / 10000;
            var js_Rdj_00_02 = (js_mbj_02 + (js_total_02 - js_mbj_02) * yll_02) - (js_mbj_02 + (js_total_02 - js_mbj_02 * 2) * yll_02);
            var js_Rdj_02 = js_Rdj_00_02 * 10000 / 10000;
            var js_Rzlx_00_02 = js_total_02 * yll_02 * (js_time + 1) / 2;
            var js_Rzlx_02 = js_Rzlx_00_02 * 10000 / 10000;
            var js_Rbxhj_02 = js_Rzlx_02 + js_total_02;

            js_total = (js_total_01 + js_total_02) * 10000;
            js_Lmy = ((js_Lmy_01 + js_Lmy_02) * 10000).toFixed(2);
            js_Lzlx = ((js_Lzlx_01 + js_Lzlx_02) * 10000).toFixed(2);
            js_Lbxhj = ((js_Lbxhj_01 + js_Lbxhj_02) * 10000).toFixed(2);
            js_Rsy = ((js_Rsy_01 + js_Rsy_02) * 10000).toFixed(2);
            js_Rdj = (((js_Rdj_01 + js_Rdj_02) * 10000 / 10000) * 10000).toFixed(2);
            js_Rzlx = ((js_Rzlx_01 + js_Rzlx_02) * 10000).toFixed(2);
            js_Rbxhj = ((js_Rbxhj_01 + js_Rbxhj_02) * 10000).toFixed(2);

            $('.js_total').text(js_total);
            $('.js_time').text(js_time);
            $('#js_Lmy').text(js_Lmy);
            $('#js_Lzlx').text(js_Lzlx);
            $('#js_Lbxhj').text(js_Lbxhj);
            $('#js_Rsy').text(js_Rsy);
            $('#js_Rdj').text(js_Rdj);
            $('#js_Rzlx').text(js_Rzlx);
            $('#js_Rbxhj').text(js_Rbxhj);
        }
    });
    $("#gjjin_btn").click(function() {
        var gjj_money = $('#gjj_eds_dkje').val();
        if (gjj_money == 0) {
            alert('请输入贷款金额');
            return false;
        }

        $('.conresult').removeClass('none');
        $('.con_n').addClass('none');
        $('.head').removeClass('none');
        $('.uctype').addClass('none');
        var js_total = $('.js_total');
        var js_time = $('.js_time');
        var js_Lmy = $('#js_Lmy');
        var js_Rsy = $('#js_Rsy');
        var js_Rdj = $('#js_Rdj');
        var js_Lzlx = $('#js_Lzlx');
        var js_Rzlx = $('#js_Rzlx');
        var js_Lbxhj = $('#js_Lbxhj');
        var js_Rbxhj = $('#js_Rbxhj');
        if ($('#zuhedk_rate').val() == '' || isNaN($('#zuhedk_rate').val())) {
            $('#zuhedk_rate').val(gjjdk_lv);
        }
        var r1 = parseFloat($('#gjj_eds_dkje').val());
        var r2 = parseFloat($('#hid_month').val());
        var r3 = parseFloat($('#zuhedk_rate').val());
        if (zuhesydk == true && gjj_eds_dkje == true && zhgjj_ll == true && zuhedk_rate == true) {
            js_total = (Math.round(r1 * 10000) / 10000) * 10000;
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
            var js_Lmys = js_Lmy.toFixed(2);
            var js_Lzlxs = js_Lzlx.toFixed(2);
            var js_Lbxhjs = js_Lbxhj.toFixed(2);
            $('.js_total').text(js_total);
            $('.js_time').text(js_time);
            $('#js_Lmy').text(js_Lmys);
            $('#js_Lzlx').text(js_Lzlxs);
            $('#js_Lbxhj').text(js_Lbxhjs);
            var js_Rsys = js_Rsy.toFixed(2);
            var js_Rdjs = js_Rdj.toFixed(2);
            $('#js_Rsy').text(js_Rsys);
            $('#js_Rdj').text(js_Rdjs);
            $('#js_Rzlx').text(js_Rzlx);
            $('#js_Rbxhj').text(js_Rbxhj);
        }
    });//over

    $('#car_btn').click(function() {
        $('.concarresult').removeClass('none');
        $('.con_n').addClass('none');
        $('.head').removeClass('none');
        $('.uctype').addClass('none');
        if (car_price == true && car_rate == true && pop_list == true) {
            var cd_yg = $('#topYg');
            var cd_time = $('#topM');
            var cd_price = $('#topPrice');
            var cd_all = $('#topTotal');
            if ($('#car_rate').val() == '' || isNaN($('#car_rate').val())) {
                $('#car_rate').val(car_lv);
            }
            var r1 = parseFloat($('#car_price').val());
            var r3 = parseFloat($('#car_rate').val());
            var r2 = parseFloat($('#car_month').val());
            cd_sf = Math.round((r1 * (0.3)) * 10000) / 10000;
            cd_price = Math.round(r1 * 10000) / 10000;
            cd_time = r2;
            var cd_bj = cd_price - cd_sf;
            var yll = (r3 * 0.01) / 12;
            var t = Math.pow((1 + yll), cd_time);
            cd_yg = Math.round((cd_bj * yll * (t / (t - 1))) * 10000) / 10000;
            cd_all = Math.round((cd_sf + cd_yg * cd_time) * 10000) / 10000;
            cd_more = Math.round((cd_all - cd_price) * 10000) / 10000;
            $('#topYg').text(cd_yg * 10000);
            $('#topM').text(cd_time);
            $('#topPrice').text(cd_price * 10000);
            $('#topTotal').text(cd_all * 10000);
        }
    });


});

function productAjax(city, type) {
    if (type == '4') {
        var money = $('#xiaofei_money').val();
        var month = $('#xiaofei_month').val();
        var sendData = ('city=' + city + '&type_id=' + type + '&money=' + money + '&month=' + month);
    }
    else if (type == '1') {
        var money = $('#qiye_money').val();
        var month = $('#qiye_month').val();
        var sendData = ('city=' + city + '&type_id=' + type + '&money=' + money + '&month=' + month);
    }
    else if (type == '2') {
        var money = $('#car_money').val();
        var month = $('#car_month').val();
        var shoufu = $('#car_shoufu').val();
        var sendData = ('city=' + city + '&type_id=' + type + '&money=' + money + '&month=' + month + '&shoufu=' + shoufu);
    }
    else if (type == '3') {
        var money = $('#goufang_money').val();
        var month = $('#goufang_month').val();
        var sendData = ('city=' + city + '&type_id=' + type + '&money=' + money + '&month=' + month);
    }
    $.post('/jisuanqi/productAjax', sendData, function(data) {
        if (data['res'] == true) {
            $('.con_n').addClass('none');
            $('.head').removeClass('none');
            $('.uctype').addClass('none');
            $('#xf_prolist').html(data['list']);
            $('#xf_prolist').removeClass('none');
        }
    });
}