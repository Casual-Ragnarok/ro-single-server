function PageRotate180(obj){
    obj = $(obj);
    var xl3icon = obj.children('span.Conditions_xl');
    var yes180 = xl3icon.hasClass('Conditions_xl180');
    if (yes180 == false) {
        xl3icon.addClass('Conditions_xl180');
    } else {
        xl3icon.removeClass('Conditions_xl180');
    }
}