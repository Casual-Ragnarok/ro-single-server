Jquery(document).ready(function() {
});
function xfCheckOneForm(bool) {
    var id_name = 'salary';
    var salary = Jquery('#salary_inp_hidden').val();
    var wage = Jquery.trim(Jquery('#salary_inp').val());
    if (salary == '') {
        var dom = Jquery('#' + id_name + 'Tip').show();
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
    var id_name = 'qiye_type';
    var qiye_type = Jquery('#qiye_type_inp_hidden').val();
    if (qiye_type == '') {
        var dom = Jquery('#' + id_name + 'Tip').show();
        bool = false;
    }
    var id_name = 'salary_type';
    var salary_type = Jquery('input[name="' + id_name + '"]:checked').val();
    if (typeof (salary_type) == 'undefined') {
        var dom = Jquery('#' + id_name + 'Tip').show();
        bool = false;
    }
    var id_name = 'year_born';
    var year_born = Jquery('#year_born_inp').val();
    alert(year_born);
    if (year_born == '') {
        Jquery('#' + id_name + 'Tip').html('请回答问题');
        Jquery('#' + id_name + 'Tip').show();
        bool = false;
    } else {
        if (year_born < 1900) {
            Jquery('#' + id_name + 'Tip').html('您有这么老吗');
            var dom = Jquery('#' + id_name + 'Tip').show();
            bool = false;
        }
    }

    var jobyear = Jquery.trim(Jquery('#work_year').val());        //-------------------您的工作时间 - 年 start ---------------------------
    var job_year_placeholder = Jquery('input[name="job_year"]').attr('placeholder');   //IE6下提示语
    var id_name = 'jobtime';
    if (job_year_placeholder == jobyear) {
        jobyear = '';
    }
    if (jobyear == '' && jobmonth == '') {
        var dom = Jquery('#' + id_name + 'Tip').show();
        bool = false
    } else {
        if (!isNaN(jobyear)) {
            if ((jobyear < 0) || (jobyear > 100)) {
                var dom = Jquery('.jobtime').show();
                Jquery('.jobtime').text('0<年数<45');
                bool = false;
            }
        } else {
            var dom = Jquery('.jobtime').show();
            Jquery('.jobtime').text('输入纯数字');
            bool = false;
        }
    }//-------------------您的工作时间 - 年 over  ---------------------------
    var jobmonth = Jquery.trim(Jquery('#work_month').val());  //-------------------您的工作时间 - 月 start  ---------------
    var job_month_placeholder = Jquery('input[name="job_month"]').attr('placeholder');   //IE6下提示语

    if (job_month_placeholder == jobmonth) {
        jobmonth = '';
    }
    if (jobyear == '' && jobmonth == '') {
        var dom = Jquery('#' + id_name + 'Tip').show();
        bool = false;
    } else {
        if (!isNaN(jobmonth)) {
            if ((jobmonth < 0) || (jobmonth > 11)) {
                var dom = Jquery('.jobtime').show();
                Jquery('.jobtime').text('0<=月数<12');
                bool = false;
            }
        } else {
            var dom = Jquery('.jobtime').show();
            Jquery('.jobtime').text('输入纯数字');
            bool = false;
        }
    }     //------------------- 您的工作时间 - 月 over ---------------------------
    return bool;
}
function xfCheckTwoForm(bool) {
    var bool = true;
    var has_blue_card = isHas(Jquery('input[name="has_blue_card"]:checked').val());
    if (has_blue_card == '') {
        bool = false;
        Jquery('#has_blue_card_tip').show();
    }
    if (has_blue_card == '有') {
        var count_blue_card = Jquery('input[name="count_blue_card"]').val();             //1.1您有几张信用卡
        if (count_blue_card == '') {
            bool = false;
            Jquery('#count_blue_card_tip').show();
        } else {
            if (isNaN(count_blue_card)) {
                bool = false;
                Jquery('#count_blue_card_tip').html('请输入纯数字');
                Jquery('#count_blue_card_tip').show();
            } else {
                if (count_blue_card < 0) {
                    bool = false;
                    Jquery('#count_blue_card_tip').html('不能为负数');
                    Jquery('#count_blue_card_tip').show();
                } else if (count_blue_card > 10000) {
                    bool = false;
                    Jquery('#count_blue_card_tip').html('最多10000张');
                    Jquery('#count_blue_card_tip').show();
                } else {
                    Jquery('#count_blue_card_tip').hide();
                }
            }
        }
        var money_blue_card = Jquery('input[name="money_blue_card"]').val();            //1.2额度总额是多少
        if (money_blue_card == '') {
            bool = false;
            Jquery('#money_blue_card_tip').show();
        } else {
            if (isNaN(money_blue_card)) {
                bool = false;
                Jquery('#money_blue_card_tip').html('请输入纯数字');
                Jquery('#money_blue_card_tip').show();
            } else {
                if (money_blue_card < 0) {
                    bool = false;
                    Jquery('#money_blue_card_tip').html('不能为负数');
                    Jquery('#money_blue_card_tip').show();
                } else if (money_blue_card > 8000000) {
                    bool = false;
                    Jquery('#money_blue_card_tip').html('800万内');
                    Jquery('#money_blue_card_tip').show();
                } else {
                    Jquery('#money_blue_card_tip').hide();
                }
            }
        }
    } else {
        Jquery('input[name="count_blue_card"]').val('');
        Jquery('input[name="money_blue_card"]').val('');
    }

    var has_debt_card = isHas(Jquery('input[name="has_debt_card"]:checked').val());
    if (has_debt_card == '') {
        bool = false;
        Jquery('#has_debt_card_tip').show();
    }
    if (has_debt_card == '有') {
        var money_debt_card = Jquery('input[name="money_debt_card"]').val();             //2.1负债多少
        if (money_debt_card == '') {
            bool = false;
            Jquery('#money_debt_card_tip').show();
        } else {
            if (isNaN(money_debt_card)) {
                bool = false;
                Jquery('#money_debt_card_tip').html('请输入纯数字');
                Jquery('#money_debt_card_tip').show();
            } else {
                if (money_debt_card < 0) {
                    bool = false;
                    Jquery('#money_debt_card_tip').html('不能为负数');
                    Jquery('#money_debt_card_tip').show();
                } else if (money_debt_card > 8000000) {
                    bool = false;
                    Jquery('#money_debt_card_tip').html('800万内');
                    Jquery('#money_debt_card_tip').show();
                } else {
                    Jquery('#money_debt_card_tip').hide();
                }
            }
        }
    }
    else {
        Jquery('input[name="money_debt_card"]').val('');
    }
    var has_succ_reply = isHas(Jquery('input[name="has_succ_reply"]:checked').val());
    if (has_succ_reply == '') {
        bool = false;
        Jquery('#has_succ_reply_tip').show();
    } else {
        Jquery('#has_succ_reply_tip').hide();
    }
    var has_debt_loan = isHas(Jquery('input[name="has_debt_loan"]:checked').val());
    if (has_debt_loan == '') {
        bool = false;
        Jquery('#has_debt_loan_tip').show();
    }
    if (has_debt_loan == '有') {
        var money_debt_loan = Jquery('input[name="money_debt_loan"]').val();             //4.1负债多少
        if (money_debt_loan == '') {
            bool = false;
            Jquery('#money_debt_loan_tip').show();
        } else {
            if (isNaN(money_debt_loan)) {
                bool = false;
                Jquery('#money_debt_loan_tip').html('请输入纯数字');
                Jquery('#money_debt_loan_tip').show();
            } else {
                if (money_debt_loan < 0) {
                    bool = false;
                    Jquery('#money_debt_loan_tip').html('不能为负数');
                    Jquery('#money_debt_loan_tip').show();
                } else if (money_debt_loan > 8000000) {
                    bool = false;
                    Jquery('#money_debt_loan_tip').html('800万内');
                    Jquery('#money_debt_loan_tip').show();
                } else {
                    Jquery('#money_debt_loan_tip').hide();
                }
            }
        }
    }
    else {
        Jquery('input[name="money_debt_loan"]').val('');
    }
    return bool;
}


Jquery(document).ready(function() {
    yesno();
    hidetip();
});
function yesno() {
    var tab_yes = Jquery('.ipttab_yes');
    var tab_no = Jquery('.ipttab_no');
    tab_yes.click(function() {
        var tab_con = Jquery(this).parent().parent().parent().parent().children('.xinpu');
        Jquery(this).parent().parent().children('.tishi').hide();
        tab_con.show();
        xf_juzhong();
    });
    tab_no.click(function() {
        var tab_con = Jquery(this).parent().parent().parent().parent().children('.xinpu');
        Jquery(this).parent().parent().children('.tishi').hide();
        tab_con.hide();
        xf_juzhong();
    });
    var tab_yes2 = Jquery('.ipttab_yes2');
    var tab_no2 = Jquery('.ipttab_no2');
    tab_yes2.click(function() {
        Jquery(this).parent().parent().children('.tishi').hide();
        xf_juzhong();
    });
    tab_no2.click(function() {
        Jquery(this).parent().parent().children('.tishi').hide();
        xf_juzhong();
    });
}
function xf_juzhong() {
    if (jQuery.browser.msie && (jQuery.browser.version == "6.0") && !jQuery.support.style) {
        Jquery('.tipbox').css({});
    } else {
        Jquery('.tipbox').css({
            top: ((Jquery(window).height() - Jquery('.tipbox').outerHeight()) / 2),
            left: (Jquery(window).width() - Jquery(".tipbox").outerWidth()) / 2
        });
    }
}

function hidetip() {
    var tip = Jquery('.tishi');
    var ipt = Jquery('.sinp');
    ipt.click(function() {
        Jquery(this).parent().parent().children('.tishi').hide();
    });
}