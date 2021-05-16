var xd_type_global = Jquery('#xd_type').val();
Jquery(document).ready(function() {
});
function placeholderBox() {
    var doc = document, inputs = doc.getElementsByTagName('input'), supportPlaceholder = 'placeholder'in doc.createElement('input'), placeholder = function(input) {
        var text = input.getAttribute('placeholder'), defaultValue = input.defaultValue;
        if (defaultValue == '') {
            input.value = text
        }
        Jquery(input).css('color', '#999');
        input.onfocus = function() {
            if (input.value === text) {
                this.value = ''
            }
            Jquery(this).css('color', '#333');
        };
        input.onblur = function() {
            if (input.value === '') {
                this.value = text
                Jquery(this).css('color', '#999');
            } else {
                Jquery(this).css('color', '#333');
            }
        }
    };
    if (!supportPlaceholder) {
        for (var i = 0, len = inputs.length; i < len; i++) {
            var input = inputs[i], text = input.getAttribute('placeholder');
            if (input.type === 'text' && text) {
                placeholder(input)
            }
        }
    }
}
function placeholderBoxText() {
    var doc = document, inputs = doc.getElementsByTagName('textarea'), supportPlaceholder = 'placeholder'in doc.createElement('textarea'), placeholder = function(input) {
        var text = input.getAttribute('placeholder'), defaultValue = input.defaultValue;
        if (defaultValue == '') {
            input.value = text
        }
        input.onfocus = function() {
            if (input.value === text) {
                this.value = ''
            }
            Jquery(this).css('color', '#333');
        };
        input.onblur = function() {
            if (input.value === '') {
                this.value = text
                Jquery(this).css('color', '#999');
            } else {
                Jquery(this).css('color', '#333');
            }
        }
    };
    for (var i = 0, len = inputs.length; i < len; i++) {
        var input = inputs[i], text = input.getAttribute('placeholder');
        placeholder(input);
    }
}
Jquery('.tboxb').click(function() {
    Jquery('.tishi').hide();
});
Jquery('.tbsb').click(function() {
    Jquery('.tishi2').hide();
});
function juzhong() {
    if (jQuery.browser.msie && (jQuery.browser.version == "6.0") && !jQuery.support.style) {
        Jquery('.tipbox').css({});
    } else {
        Jquery('.tipbox').css({
            top: ((Jquery(window).height() - Jquery('.tipbox').outerHeight()) / 2),
            left: ((Jquery(window).width() - Jquery(".tipbox").outerWidth()) / 2)
        });
    }
}
function applyNext(next_name) {
    bool = true;
    if (next_name == 'xiaofei_two') {
        var bool = checkOneForm();
        yesno();
        hidetip();
        if (bool == true) {
            Jquery('#one').hide();
            Jquery('#xiaofei_two').show();
        }
    } else if (next_name == 'two') {
        if (xd_type_global != 4) {
            var bool = checkOneForm();
        } else {
            var bool = xfCheckTwoForm();
        }
        if (bool == true) {
            if (Jquery('#xiaofei_two').length > 0) {
                Jquery('#xiaofei_two').hide();
                Jquery('.tboxtop').html('<div class="tboxa bac3">' +
                        '<span class="tbsp1 co3">1.填写个人职业信息</span>' +
                        '<span class = "tbsp2 co3" > 2.填写个人信用信息 </span>' +
                        '<span class = "tbsp3 cow" > 3.留下联系方式 </span>' +
                        '<span class = "tbsp4 co3" > 4.成功了！ </span></div>');
            }
            Jquery('#one').hide();
            Jquery('#two').show();
        }
    }
    else if (next_name == 'three') {
        var bool = checkTwoForm();
        if (bool == true) {
            Jquery('#two').hide();
            if (Jquery('#xiaofei_two').length > 0) {
                Jquery('.tboxtop').html('<div class="tboxa bac4"><span class="tbsp1 co3">1.填写个人职业信息</span>' +
                        '<span class = "tbsp2 co3" > 2.填写个人信用信息 </span>' +
                        '<span class = "tbsp3 co3" > 3.留下联系方式 </span>' +
                        '<span class = "tbsp4 cow" > 4.成功了！ </span></div>');
            }
            Jquery('#three').show();
            applySend();
        }
    }
    juzhong();
}
function checkOneForm() {
    var xd_type = Jquery('#xd_type').val();
    var bool = true;
    if (xd_type == 1) { //------------------------------------------- 企业 -------------------------------------------
        var qiye_type = Jquery('#qiye_type_inp_hidden').val();
        if (qiye_type == '') {
            var dom = Jquery('#qiye_typeTip').show();
            bool = false;
        }
        if (Jquery('#monthly').val() == '') {
            var dom = Jquery('#monthlyTip').show();
            bool = false;
        }
        var has_house = Jquery('input[name="has_house"]:checked').val();
        if (typeof (has_house) == 'undefined') {
            var dom = Jquery('#has_houseTip').show();
            bool = false;
        }
        var register = Jquery('input[name="register"]:checked').val();
        if (typeof (register) == 'undefined') {
            var dom = Jquery('#registerTip').show();
            bool = false;
        }
    } else if (xd_type == 2) { //------------------------------------------- 购车 -------------------------------------------
        var has_house = Jquery('input[name="has_house"]:checked').val();
        if (typeof (has_house) == 'undefined') {
            var dom = Jquery('#has_houseTip').show();
            bool = false;
        }
        var car_number = Jquery('input[name="car_number"]:checked').val();
        if (typeof (car_number) == 'undefined') {
            var dom = Jquery('#car_numberTip').show();
            bool = false;
        }
        var car_type = Jquery('input[name="car_type"]:checked').val();
        if (typeof (car_type) == 'undefined') {
            var dom = Jquery('#car_typeTip').show();
            bool = false;
        }
        var car_stage = Jquery('input[name="car_stage"]:checked').val();
        if (typeof (car_stage) == 'undefined') {
            var dom = Jquery('#car_stageTip').show();
            bool = false;
        }
        var car_use = Jquery('input[name="car_use"]:checked').val();
        if (typeof (car_use) == 'undefined') {
            var dom = Jquery('#car_useTip').show();
            bool = false;
        }
    } else if (xd_type == 3) { //------------------------------------------- 购房 -------------------------------------------
        var goufang_type = Jquery('#goufang_type_inp_hidden').val();
        if (goufang_type == '') {
            var dom = Jquery('#goufang_typeTip').show();
            bool = false;
        }
        var salary = Jquery('#salary_inp_hidden').val();
        var wage = Jquery.trim(Jquery('#salary_inp').val());
        if (salary == '') {
            var dom = Jquery('#salaryTip').show();
            bool = false;
        } else {
            if (!isNaN(salary)) {
                if (salary > 1000000) {
                    var dom = Jquery('#salaryTip').show();
                    Jquery('#salaryTip').text('限100万内');
                    bool = false;
                } else {
                    var dom = Jquery('#salaryTip').hide();
                    bool = true;
                }
            } else {
                var dom = Jquery('#salaryTip').show();
                Jquery('#salaryTip').text('输入纯数字');
                bool = false;
            }
        }
        var first_house = Jquery('input[name="first_house"]:checked').val();
        if (typeof (first_house) == 'undefined') {
            var dom = Jquery('#first_houseTip').show();
            bool = false;
        }
        var id_name = 'second_hand_house';
        var second_hand_house = Jquery('input[name="' + id_name + '"]:checked').val();
        if (typeof (second_hand_house) == 'undefined') {
            var dom = Jquery('#' + id_name + 'Tip').show();
            bool = false;
        }
    } else if (xd_type == 4) { //------------------------------------------- 消费 -------------------------------------------
        bool = xfCheckOneForm(bool);
    }
    return bool;
}

function checkTwoForm() {
    var bool = true;
    var nickname = Jquery('#nickname').val();
    var nknet = Jquery('#nicknameTip');
    var mobile = Jquery('#mobile').val();
    if (/[\s><,._\。\[\]\{\}\?\/\+\=\|\'\\\":;\~\!\@\#\*\Jquery\%\^\&`\uff00-\uffff)(]+/.test(nickname) && nickname.length > 0) {
        nknet.show();
        nknet.text('输入只限中英文');
        bool = false;
    } else if (nickname == '') {
        nknet.show();
        bool = false;
    } else if (nickname.length == 0) {
        nknet.show();
        nknet.text('请填写您的称呼');
        bool = false;
    }
    if (mobile == '' || mobile == '用于接收信贷员联系方式') {
        Jquery('#mobileTip').show();
        bool = false;
    } else {
        if (checkMobile(mobile) == false) {
            Jquery('#mobileTip').show();
            bool = false;
        }
    }
    var email = Jquery('#email').val();
    var def_email = Jquery('#email').attr('defval');
    if (email != def_email) {
        if (checkEmail(email) == false) {
            Jquery('#emailTip').show();
            bool = false;
        }
    }
    return bool;
}

function inpTwoFocus(obj) {
    Jquery(obj).css('color', '#666666');
}
function inpTwoBlur(obj) {
    Jquery(obj).css('color', '#CCCCCC');
}
function applySend() {
    var sendData = Jquery('#applyForm').serialize();
    var xd_id = Jquery('#xd_id').val();
    var xd_type = Jquery('#xd_type').val();
    var zone_id = Jquery('#zone_id').val();
    var remark = remarkApply(xd_type);
    var bank_id = Jquery('#bank_id').val();
    var money = Jquery('#money_detail').val();
    var month = Jquery('#month_detail').val();
    var source_host = Jquery('#source_host').val();
    var ref = Jquery('#ref').val();
    sendData += ('&url=' + document.location.href + '&xd_id=' + xd_id + '&xd_type=' + xd_type + '&zone_id=' + zone_id +
            '&remark=' + remark + '&bank_id=' + bank_id + '&money=' + money + '&month=' + month + '&source_host=' + source_host +
            '&ref=' + ref);
    Jquery.post('/xindai/applySend', sendData, function(data) {
        if (data != false) {
            iu_id = data;
        } else {

        }
    });
}
function sendApplydetails() {
    var details = Jquery.trim(Jquery('#applydetails').val());
    if (details == '' || iu_id == null) {
        return false;
    }
    var xd_type = Jquery('#xd_type').val();
    var sendData = ('id=' + iu_id + '&details=' + details + '&xd_type=' + xd_type);
    Jquery.post('/xindai/applyDetails', sendData, function(data) {
    });
}
function remarkApply(xd_type) {
    var split_str = '<br />';
    var remark = '';
    if (xd_type == 1) { //------------------------------------------- 企业 -------------------------------------------
        var qiye_type = Jquery('#qiye_type_inp_hidden').val();
        var monthly = '银行卡走账月收入:' + Jquery('#monthly').val();
        var has_house = Jquery('input[name="has_house"]:checked').val();
        var register = Jquery('input[name="register"]:checked').val();
        remark = (qiye_type + split_str + monthly + split_str + has_house + split_str + register);
    } else if (xd_type == 2) { //------------------------------------------- 购车 -------------------------------------------
        var has_house = Jquery('input[name="has_house"]:checked').val();
        var car_number = Jquery('input[name="car_number"]:checked').val();
        var car_type = Jquery('input[name="car_type"]:checked').val();
        var car_stage = Jquery('input[name="car_stage"]:checked').val();
        var car_use = Jquery('input[name="car_use"]:checked').val();
        remark = (has_house + split_str + car_number + split_str + car_type + split_str + car_stage + split_str + car_use);
    } else if (xd_type == 3) { //------------------------------------------- 购房 -------------------------------------------
        var goufang_type = Jquery('#goufang_type_inp_hidden').val();
        var salary = Jquery('#salary_inp_hidden').attr('val') + ':' + Jquery('#salary_inp_hidden').val();
        var first_house = Jquery('input[name="first_house"]:checked').val();
        var second_hand_house = Jquery('input[name="second_hand_house"]:checked').val();
        remark = (goufang_type + split_str + salary + split_str + first_house + split_str + second_hand_house);
    } else if (xd_type == 4) { //------------------------------------------- 消费 -------------------------------------------
        var salary = Jquery('#salary_inp_hidden').attr('val') + ':' + Jquery('#salary_inp_hidden').val();
        var salary_type = Jquery('input[name="salary_type"]:checked').val();
        var qiye_type = Jquery('#qiye_type_inp_hidden').val();
        var year_born = Jquery('#year_born').attr('val') + ':' + Jquery('#year_born').val();
        var job_year = Jquery.trim(Jquery('input[name="job_year"]').val());
        var job_year_placeholder = Jquery('input[name="job_year"]').attr('placeholder') + '年';   //IE6下提示语
        if (job_year_placeholder == job_year) {
            job_year = '';
        }
        var job_month = Jquery.trim(Jquery('input[name="job_month"]').val());
        var job_month_placeholder = Jquery('input[name="job_month"]').attr('placeholder');   //IE6下提示语
        if (job_month_placeholder == job_month) {
            job_month = '';
        }
        var job_year_month = '';
        if ((job_year + job_month) != '') {
            var str = '';
            if (job_year != '') {
                str = job_year + '年 ';
            }
            if (job_month != '') {
                str += job_month + '月';
            }
            job_year_month = split_str + '您的工作时间是:' + str;
        }
        var has_blue_card = isHas(Jquery('input[name="has_blue_card"]:checked').val());
        if (has_blue_card != '') {
            has_blue_card = split_str + '您是否有信用卡:' + has_blue_card + split_str;
        }
        var count_blue_card = Jquery('input[name="count_blue_card"]').val();
        var count_blue_card_placeholder = Jquery('input[name="count_blue_card"]').attr('placeholder');   //IE6下提示语
        if (count_blue_card == count_blue_card_placeholder) {
            count_blue_card = '';
        }
        if (count_blue_card != '') {
            count_blue_card = '您有几张信用卡:' + count_blue_card + split_str;
        }
        var money_blue_card = Jquery('input[name="money_blue_card"]').val();
        var money_blue_card_placeholder = Jquery('input[name="money_blue_card"]').attr('placeholder');   //IE6下提示语
        if (money_blue_card == money_blue_card_placeholder) {
            money_blue_card = '';
        }
        if (money_blue_card != '') {
            money_blue_card = '额度总额是多少:' + money_blue_card + split_str;
        }
        var has_debt_card = isHas(Jquery('input[name="has_debt_card"]:checked').val());
        if (has_debt_card != '') {
            has_debt_card = '是否有负债（信用卡）:' + has_debt_card + split_str;
        }
        var money_debt_card = Jquery('input[name="money_debt_card"]').val();
        var money_debt_card_placeholder = Jquery('input[name="money_debt_card"]').attr('placeholder');   //IE6下提示语
        if (money_debt_card == money_debt_card_placeholder) {
            money_debt_card = '';
        }
        if (money_debt_card != '') {
            money_debt_card = '负债多少（信用卡）:' + money_debt_card + split_str;
        }
        var has_succ_reply = isHas(Jquery('input[name="has_succ_reply"]:checked').val());
        if (has_succ_reply != '') {
            has_succ_reply = '您之前是否成功申请贷款:' + has_succ_reply + split_str;
        }
        var has_debt_loan = isHas(Jquery('input[name="has_debt_loan"]:checked').val());
        if (has_debt_loan != '') {
            has_debt_loan = '是否有负债（贷款）:' + has_debt_loan + split_str;
        }
        var money_debt_loan = Jquery('input[name="money_debt_loan"]').val();
        var money_debt_loan_placeholder = Jquery('input[name="money_debt_loan"]').attr('placeholder');   //IE6下提示语
        if (money_debt_loan == money_debt_loan_placeholder) {
            money_debt_loan = '';
        }
        if (money_debt_loan != '') {
            money_debt_loan = '负债多少（贷款）:' + money_debt_loan + split_str;
        }
        var xiaofei_two = job_year_month + has_blue_card + count_blue_card + money_blue_card + has_debt_card + money_debt_card +
                has_succ_reply + has_debt_loan + money_debt_loan;

        remark = (salary + split_str + salary_type + split_str + year_born + split_str + qiye_type + xiaofei_two);
    }
    return remark;
}

function xiaofei_two() {

}

function isHas(val) {
    var res = '';
    if (val == 1) {
        res = '有';
    } else if (val == 2) {
        res = '没有';
    }
    return res;
}

function checkBlur(id_name, val_id_name, default_val) {
    var val = Jquery('#' + val_id_name).val();
    setTimeout(function() {
        if (val != default_val) {
            Jquery('#' + id_name + 'Tip').hide();
        } else {
            Jquery('#' + id_name + 'Tip').show();
        }
    }, 300);
}

function checkFocus(id_name, val_id_name, default_val) {
    var val = Jquery('#' + val_id_name).val();
    if (val != default_val) {
        Jquery('#' + id_name + 'Tip').hide();
    } else {
        Jquery('#' + id_name + 'Tip').show();
    }
}


function checkTip(id_name) {
    var dom = Jquery('#' + id_name + 'Tip');
    var status = dom.css('display');
    if (status == 'none') {
        dom.show();
    } else {
        dom.hide();
    }
}

function inpFocus(id_name) {
    var dom = Jquery('#' + id_name + '_ul');
    var status = dom.css('display');
    if (status == 'none') {
        dom.show();
    } else {
        dom.hide();
    }
}
function inpSelect(id_name, obj) {
    var dom = Jquery('#' + id_name + '_inp');
    var val = Jquery(obj).html();
    var type_name = dom.attr('val');
    dom.val(val);
    var relval = type_name + ':' + val;
    dom.attr('relval', relval);
    Jquery('#' + id_name + '_inp_hidden').val(relval);
}
function yearSelect(obj) {
    var obj = Jquery(obj);
    var val = obj.html();
    var reval = obj.attr('reval');
    if (val == '其他') {
        Jquery('#year_born').attr('reval', '');
        Jquery('#year_born').val('');
        Jquery('#year_born').attr('class', 'sinp iptsr');
        Jquery('#year_born').focus();
    } else {
        Jquery('#year_born').attr('reval', reval);
        Jquery('#year_born').val(reval);
        Jquery('#year_born').attr('class', 'sinp iptsr');
    }
    var year_u = Jquery('#year_u');
    var status = year_u.css('display');
    if (status == 'none') {
        Jquery('#year_u').show();
    } else {
        Jquery('#year_u').hide();
    }
}
function hideYearu() {
    var dom = Jquery('#year_u');
    var status = dom.css('display');
    if (status == 'none') {
        Jquery('#year_u').show();
    } else {
        Jquery('#year_u').hide();
    }
}
function yearKeyup(obj) {
    Jquery('#year_u').show();
    var obj = Jquery(obj);
    var val = obj.val();
    var arr = isNaN(val);

    obj.val(val.replace(/[^\d]/g, ""));
    if (arr == true) {
        Jquery('#year_u').hide();
        return false;
    }

    if (val == '' || val == '0') {
        Jquery('#year_u').hide();
        return false;
    }
    if (val.length >= 4) {
        obj.val(val.substring(0, 4));
        Jquery('#year_u').hide();
    }
    var html = '<li><a onclick="yearSelect(this)" href="javascript:void(0);" k="year" reval="' + val + '">' + val + ' </a></li>';
    if (val < 100) {
        var temp_val = val * 10;
        html += '<li><a onclick="yearSelect(this)" href="javascript:void(0);" k="year" reval="' + temp_val + '">' + temp_val + ' </a></li>';
    }
    if (val < 10) {
        var temp_val = (val * 10 * 10);
        html += '<li><a onclick="yearSelect(this)" href="javascript:void(0);" k="year" reval="' + temp_val + '">' + temp_val + ' </a></li>';
    }
    Jquery('#year_u').html(html);
}

function inpVal(default_str, obj) {
    var obj = Jquery(obj);
    var val = obj.val();
    if (val == default_str) {
        obj.val('');
    } else if (val == '') {
        obj.val(default_str);
        obj.css('color', '#CCCCCC');
    }
}

function salarytypeSel(type) {
    var salary_show = '每月打入银行卡的工资';
    if (type == 'two') {
        salary_show = '每月领取现金';
    }
    Jquery('#salary_show').html(salary_show);
    Jquery('#salary_inp_hidden').attr('val', salary_show);
}
Jquery(document).ready(function() {
    Jquery('.iptsr').focus(function() {
        Jquery(this).placeholder('');
    });
    Jquery('#applydetails').bind('click', srfun)
            .bind('keyup', srfun);
});
var srfun = function() {
    var num = Jquery('#applydetails').val().length;
    if (num > 280) {
        var yewnum = Jquery('#applydetails').val().substr(0, 280)
        Jquery('#applydetails').val(yewnum);
    }
    var num_yu = 280 - num;
    if (num_yu == -1) {
        num_yu = 0;
    }
    Jquery('#shownum').html(num_yu / 2);
}