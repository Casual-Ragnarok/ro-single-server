var xltime = 40;
var JGPmoney = false;
var JGPmonth = false;
var jq = jQuery.noConflict();
jq(document).ready(function(){
	smallstar();
	wenhao();
    jq(document).click(function (event) {
	  var yon = jq('#xlk_ul').hasClass('xiala_no');
	  if(yon == true){
		 jq('#xlk_ul').hide(100);
	  }
	});
});
function iptxlCK(obj){
    var obj = jq(obj);
	var xlcon = obj.parent().children('.wydk_xlk').children('.wydk_xiala');
	var bas = xlcon.hasClass('xiala_no');
	if(bas==true){
	  xlcon.slideDown(xltime);
	  xlcon.removeClass('xiala_no');
	} else{
	  xlcon.slideUp(xltime);
	  xlcon.addClass('xiala_no');
	}
}
function JGPmoneySelect(tab){
    jq('#xuazn').unbind();
	var tab = jq(tab);
	var Li_a = jq('#xlk_ul li a');
	var this_reval = tab.attr('reval');
	jq('#xuazn').val(this_reval);
	var sey_reval = jq('#xuazn').attr('reval');
	jq('#xuazn').attr('reval', this_reval);
	jq('#xlk_ul').slideUp(xltime);
	jq('#xlk_ul').addClass('xiala_no');
	Li_a.removeClass('mo');
	tab.addClass('mo');
    var ts = tab.parent().parent().parent().parent().children('.Tosearch_ts');
    ts.hide(200);
}
function JGPmonthSelect(bba){
	var bba = jq(bba);
	var Li_a = jq('#qixan_ul li a');
	var this_s = bba.attr('s');
	jq('#qixan').val(this_s);
	var sey_reval = jq('#qixan').attr('reval');
	var This_reval = bba.attr('reval');
	jq('#qixan').attr('reval', This_reval);
	var yon = bba.hasClass('dw_month');
	if(yon == true){
	  jq('#time_dw').removeClass('nian');
	  jq('#time_dw').addClass('geyue');
	}else{
	  jq('#time_dw').removeClass('geyue');
	  jq('#time_dw').addClass('nian');
	}
	jq('#qixan_ul').slideUp(xltime);
	Li_a.removeClass('mo');
	bba.addClass('mo');
	jq('#qixan_ul').addClass('xiala_no');
    var ts = bba.parent().parent().parent().parent().children('.Tosearch_ts');
    ts.hide(200);
}
function JGPageMoneyBlu(){
    JGPageMoneyYz();
}
function JGPageTimeBlu(){
	JGPageTimeYz();
}
function leixCK(bad){
    var bad = jq(bad);
	var xleix = bad.parent().children('.jglx_xl').children('.jglx_xiala');
	var vds = xleix.hasClass('xiala_no');
	var allul = jq('.jglx_xiala');
	if(vds==true){
	  allul.slideUp(xltime);
	  xleix.slideDown(xltime);
	  xleix.removeClass('xiala_no');
	} else{
	  allul.slideUp(xltime);
	  xleix.slideUp(xltime);
	  xleix.addClass('xiala_no');
	}
}
function wordSelect(dfh){
	var dfh = jq(dfh);
	var Li_a = jq('#jglx_ul li a');
	jq('#jglx_ul').slideUp(xltime);
	jq('#jglx_ul').addClass('xiala_no');
	var sey_word = jq('#leix_type').html();
	var This_word = dfh.html();
	jq('#leix_type').html(This_word);
	var sey_reval = jq('#leix_type').attr('reval');
	var This_reval = dfh.attr('reval');
	jq('#leix_type').attr('reval', This_reval);
	Li_a.removeClass('mo');
	dfh.addClass('mo');
}
function textSelect(frg){
	var frg = jq(frg);
	var Li_a = jq('#jglxb_ul li a');
	jq('#jglxb_ul').slideUp(xltime);
	jq('#jglxb_ul').addClass('xiala_no');
	var sey_word = jq('#leixb_type').html();
	var This_word = frg.html();
	jq('#leixb_type').html(This_word);
	var sey_reval = jq('#leixb_type').attr('reval');
	var This_reval = frg.attr('reval');
	jq('#leixb_type').attr('reval', This_reval);
	Li_a.removeClass('mo');
	frg.addClass('mo');
}
function smallstar(){
    var small_span = jq(".stars_small");
	var small_tas = jq(".small_tc");
	small_tas. hide();
    small_span.mouseover(function(){
    var nowstars = jq(this);
    var small_tas = nowstars.parent().children('span.small_tc');
	small_tas.toggle(130);
  });
   small_span.mouseout(function(){
    var nowstars = jq(this);
    var small_tas = nowstars.parent().children('span.small_tc');
	small_tas.toggle(130);
  });
}
function wenhao(){
	var wen_hao = jq(".wenhao");
	var ceng_none = jq(".ceng");
	wen_hao.mouseover(function(){
	var nowstars = jq(this);
	var ceng_none = nowstars.parent().children('.ceng');
	ceng_none.toggle();
});
    wen_hao.mouseout(function(){
    var nowstars = jq(this);
    var ceng_none = nowstars.parent().children('.ceng');
	ceng_none.toggle();
  });
}
function JGPageFoc(obj){
  var obj = jq(obj);
  var ts = obj.parent().parent().parent().children('.Tosearch_ts');
  ts.hide(200);
}
function JGPageMoneyYz(){
  var obj = jq('#xuazn');
  var val = jq.trim(obj.val());
  var ts = obj.parent().parent().parent().children('.Tosearch_ts');
  if(val.length == 0){
	  ts.show(300);
	  ts.text('亲，金额别忘了填');
	  JGPmoney = false;
  }else {
        if (!isNaN(val)) {
            JGPmoney = true;
            var money = jq(this).attr('reval', val);
        } else {
	        ts.show(300);
            ts.text('只填纯数字哟');
            JGPmoney = false;
        }
    }
}
function JGPageTimeYz(){
  var obj = jq('#qixan');
  var val = jq.trim(obj.val());
  var ts = obj.parent().parent().parent().children('.Tosearch_ts');
  if(val.length == 0){
	  ts.show(300);
	  ts.text('亲，金额别忘了填');
	  JGPmonth = false;
  }else {
        if (!isNaN(val)) {
            JGPmonth = true;
            var money = jq(this).attr('reval', val);
        } else {
	        ts.show(300);
            ts.text('只填纯数字哟');
            JGPmonth = false;
        }
    }
}
function JGPageKeyup(obj){
  var obj = jq(obj);
  var val = jq.trim(obj.val());
  var xlcon = obj.parent().parent().parent().children('.wydk_xlk').children('.wydk_xiala');
  xlcon.slideUp(xltime);
  xlcon.addClass('xiala_no');
  var ts = obj.parent().parent().parent().children('.Tosearch_ts');
  ts.hide(500);
}
function PageSearch(jump){
	JGPageMoneyYz();
	JGPageTimeYz();
	if (JGPmoney == true && JGPmonth == true) {
		var type_id = 4;
		var money = jq('#xuazn').val();
		var time_dw = jq('#time_dw').hasClass('geyue');
		if(time_dw == true){
        	var month = jq('#qixan').val();
		}else {
         	var month_x = jq('#qixan').val();
		  	var month = month_x * 12;
		}
        var first_pay = jq('#wydk_FirstPay').attr('reval');
        if (type_id == '2') {
            fixed_url = '&money=' + money + '&month=' + month + '&first_pay=' + first_pay;
        } else {
            fixed_url = '&money=' + money + '&month=' + month;
        }
        var url = jump + fixed_url;
        window.open(url);
        return false;
	}
}