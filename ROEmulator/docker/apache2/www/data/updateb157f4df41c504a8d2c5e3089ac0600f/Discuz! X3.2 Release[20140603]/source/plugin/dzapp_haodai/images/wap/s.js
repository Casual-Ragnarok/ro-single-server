/**
 * ¸ß¼¶ËÑË÷
 */

function conditionSel(obj) {
    var obj = $(obj);
    var showTit = obj.parents('.moreSearchTC_Con_li').children('span.HomeSelect_span');
    var $selectObj = obj.children('option:selected');
    var selectTit = $selectObj.text();
    showTit.text(selectTit);

}
$(document).ready(function() {
    var $selectList = $(".mHSearch_select_show");

    $.each($selectList, function(index, domEle) {
        conditionSel(domEle);
    });

});