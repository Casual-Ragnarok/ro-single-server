var jq = jQuery.noConflict();

jq(document).ready(function() {

    wydk();


});

function wydk() {

    var selectbtn = jq('.wydk_ipt');

    selectbtn.mouseover(function() {

        var selectcon = jq(this).parent().parent().children('.wydk_div').children('ul.wydk_li_selectcon');

        var yon = selectcon.hasClass('none');

        if (yon == true) {

            selectcon.removeClass('none');

        } else {

            selectcon.addClass('none');

        }

    });

    selectbtn.mouseout(function() {

        var selectcon = jq(this).parent().parent().children('.wydk_div').children('ul.wydk_li_selectcon');

        selectcon.addClass('none');

    });

    var select_con = jq('.wydk_div');

    select_con.mouseover(function() {

        var selectcon = jq(this).children('ul.wydk_li_selectcon');

        selectcon.removeClass('none');

    });

    select_con.mouseout(function() {

        var selectcon = jq(this).children('ul.wydk_li_selectcon');

        selectcon.addClass('none');

    });

}




function dktypeSelect(obj) {

    var obj = jq(obj);

    var Li = jq('#LTtabTit li');

    var yon_s = obj.hasClass('show_tabtit');

    var obj_reval = obj.attr('reval');

    if (yon_s == false) {

        Li.removeClass('show_tabtit');

        obj.addClass('show_tabtit');

    }

    if (obj_reval == 2) {

        jq('#CarFirstPay').removeClass('none');



    } else {

        jq('#CarFirstPay').addClass('none');



    }



    var select_name = obj.attr('name');

    if (select_name == 'type_qiye') {//-------------------------------------------------------企业贷款------------------------------------


        var Smoney = jq('#wydk_money');

        Smoney.attr('reval', 50);

        Smoney.text('50万');

        var Hmoney_ul = jq('#wydk_money_qiye').html();

        jq('#wydk_money_ul').html(Hmoney_ul);



        var Stime = jq('#wydk_month');

        Stime.attr('reval', 12);

        Stime.text('1年');

        var Hmonth_ul = jq('#wydk_month_qiye').html();

        jq('#wydk_month_ul').html(Hmonth_ul);



    } else if (select_name == 'type_gouche') {//-------------------------------------------------------购车贷款------------------------------------

        var Smoney = jq('#wydk_money');

        var Hmoney_ul = jq('#wydk_money_ul');

        Smoney.attr('reval', 15);

        Smoney.text('15万');

        var Hmoney_ul = jq('#wydk_money_gouche').html();

        jq('#wydk_money_ul').html(Hmoney_ul);



        var Stime = jq('#wydk_month');

        var Htime_ul = jq('#wydk_month_ul');

        Stime.attr('reval', 24);

        Stime.text('2年');

        var Hmonth_ul = jq('#wydk_month_gouche').html();

        jq('#wydk_month_ul').html(Hmonth_ul);



    } else if (select_name == 'type_goufang') {//-------------------------------------------------------购房贷款------------------------------------

        var Smoney = jq('#wydk_money');

        var Hmoney_ul = jq('#wydk_money_ul');

        Smoney.attr('reval', 100);

        Smoney.text('100万');

        var Hmoney_ul = jq('#wydk_money_goufang').html();

        jq('#wydk_money_ul').html(Hmoney_ul);



        var Stime = jq('#wydk_month');

        var Htime_ul = jq('#wydk_month_ul');

        Stime.attr('reval', 240);

        Stime.text('20年');

        var Hmonth_ul = jq('#wydk_month_goufang').html();

        jq('#wydk_month_ul').html(Hmonth_ul);



    } else if (select_name == 'type_xiaofei') {//-------------------------------------------------------消费贷款 or 不限------------------------------------

        var Smoney = jq('#wydk_money');

        var Hmoney_ul = jq('#wydk_money_ul');

        Smoney.attr('reval', 10);

        Smoney.text('10万');

        var Hmoney_ul = jq('#wydk_money_xiaofei').html();

        jq('#wydk_money_ul').html(Hmoney_ul);



        var Stime = jq('#wydk_month');

        var Htime_ul = jq('#wydk_month_ul');

        Stime.attr('reval', 12);

        Stime.text('1年');

        var Hmonth_ul = jq('#wydk_month_xiaofei').html();

        jq('#wydk_month_ul').html(Hmonth_ul);

    }

}

function FirstPaySelect(obj) {

    var obj = jq(obj);

    var val = obj.html();

    var reval = obj.attr('reval');

    jq('#wydk_FirstPay').attr('reval', reval);

    jq('#wydk_FirstPay').text(val);

    var select_con = jq('#wydk_FirstPay_ul');

    select_con.children('li').children('a').removeClass('mo');

    obj.addClass('mo');

    select_con.addClass('none');

}

function moneySelect(obj) { //-------贷款金额-------

    var obj = jq(obj);

    var val = obj.html();

    var reval = obj.attr('reval');

    jq('#wydk_money').attr('reval', reval);

    jq('#wydk_money').text(val);

    var select_con = jq('#wydk_money_ul');

    select_con.children('li').children('a').removeClass('mo');

    jq(this).addClass('mo');

    select_con.addClass('none');

}

function monthSelect(obj) { //-------贷款期限-------

    var obj = jq(obj);

    var val = obj.html();

    var reval = obj.attr('reval');

    jq('#wydk_month').attr('reval', reval);

    jq('#wydk_month').text(val);

    var select_con = jq('#wydk_month_ul');

    select_con.children('li').children('a').removeClass('mo');

    jq(this).addClass('mo');

    select_con.addClass('none');

}

function wydk_search(ourl) {

    var type_id = jq('#LTtabTit li.show_tabtit').attr('reval');

    var money = jq('#wydk_money').attr('reval');

    var month = jq('#wydk_month').attr('reval');

    var fixed_url = '&money=' + money + '&month=' + month;

    if (type_id == '2') {

        var first_pay = jq('#wydk_FirstPay').attr('reval');

        fixed_url = '&money=' + money + '&month=' + month + '&first_pay=' + first_pay;

    }

    var url = ourl + fixed_url;

	if (type_id == '4') {

		var rurl = url + '&xd_type=xiaofei';

	}else if(type_id == '1'){

		var rurl = url + '&xd_type=qiye';

	}else if(type_id == '2'){

		var rurl = url + '&xd_type=gouche';

	}else if(type_id == '3'){

		var rurl = url + '&xd_type=goufang';

	}

    window.open(rurl);

    return false;

}