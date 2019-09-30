

var jq=jQuery.noConflict();



function moneyFocus(obj) {

    var obj = jq(obj);

    var val = obj.val();

    var reval = obj.attr('reval');

    jq('#money_u').show();

    obj.val(reval);


}

function moenySelect(obj) {

    var obj = jq(obj);

    var val = obj.html();

    var reval = obj.attr('reval');

    if (val == '其他') {

        jq('#money').attr('reval', '');

        jq('#money').val('');

        jq('#money').attr('class', 'sinp');

        jq('#money').focus();

    } else {

        jq('#money').attr('reval', reval);

        jq('#money').val(val);

        jq('#money').attr('class', 'sinp');

    }

    var money_u = jq('#money_u');

    var status = money_u.css('display');

    if (status == 'none') {

        jq('#money_u').show();

    } else {

        jq('#money_u').hide();

    }

}

function setList(dom) {

    if (jq(dom).attr('s') == '0') {

        closeList();

        jq(dom).attr('s', '1');

        jq(dom).css('display', 'block');

    } else {

        closeList();

    }

}

function closeList() {

    jq('.xiala').attr('s', 0);

    jq('.xiala').css('display', 'none');

}



function td_click(id) {

    var link = jq('#detail_' + id).attr('href');

    window.open(link);

}



function moneyKeyup(obj) {

    var obj = jq(obj);

    var val = obj.val();

    if (val == '' || val == '0') {

        return false;

    }

    var html = '<li><a onclick="moenySelect(this)" href="javascript:void(0);" k="money" reval="' + val + '">' + val + ' 万元</a></li>';

    if (val < 100) {

        var temp_val = val * 10;

        html += '<li><a onclick="moenySelect(this)" href="javascript:void(0);" k="money" reval="' + temp_val + '">' + temp_val + ' 万元</a></li>';

    }

    if (val < 10) {

        var temp_val = (val * 10 * 10);

        html += '<li><a onclick="moenySelect(this)" href="javascript:void(0);" k="money" reval="' + temp_val + '">' + temp_val + ' 万元</a></li>';

    }

    jq('#money_u').html(html);

}

function moneyBlur(obj) {

    var obj = jq(obj);   //#money


    var moeny_val = jq('#money').val();






    var sel = jq('#money_u li a');

    var val = moeny_val + ' 万元';

    jq('#money').attr('reval', moeny_val);

    jq('#money').val(val);

    jq('#money').attr('class', 'sinp');

}

function hideMoneyu() {

    jq('#money_u').hide();

    var dom = jq('#money_u');

    var status = dom.css('display');

    if (status == 'none') {

        dom.show();

    } else {

        dom.hide();

    }

}




function xdErr(isdom){

	var qxv=jq.trim(isdom.val());

	var qts=isdom.siblings(".xd_erro");

	if (qxv == '') {

        qts.css("display", "table");

        qts.text('不能为空');

        bool_month = false;

    } else if (qxv.length > 0) {

        if (!isNaN(qxv)) {

            qts.css("display", "none");

            isdom.css('color', 'grey');

            bool_month = true;

        } else {

            qts.css("display", "table");

            qts.css('color', 'red');

            isdom.css('color', 'red');

            qts.text('只限输入数字');

            bool_month = false;

        }

	}

}





function xdErrCK(isdom) {

    var ipt = isdom;

    var qts=isdom.siblings(".xd_erro");

    qts.css("display", "none");

    ipt.val('');

    ipt.css('color', '#333');

}









function HideShow(obj) {

    var obj = jq(obj);

    var dom = obj.find('.ceng');

    var status = dom.css('display');

    if (status == 'none') {

        dom.show();

    } else {

        dom.hide();

    }

}



function money_month_index(ourl) {

	var type_id = jq('#type_id').attr('reval');

	var money = jq('#money').attr('reval');

	var month = jq('#month').attr('reval');

	var fixed_url = '&money=' + money + '&month=' + month;

	var url = ourl + fixed_url;

	if (type_id == '1') {

		var rurl = url + '&xd_type=xiaofei';

	}else if(type_id == '2'){

		var rurl = url + '&xd_type=qiye';

	}else if(type_id == '3'){

		var rurl = url + '&xd_type=gouche';

	}else if(type_id == '4'){

		var rurl = url + '&xd_type=goufang';

	}

    window.location.href=rurl;

}