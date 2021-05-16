function a(x,y){
	l = Jquery('#main').offset().left;
	w = Jquery('#main').width();
	Jquery('#tbox').css('left',(l + w + x) + 'px');
	Jquery('#tbox').css('bottom',y + 'px');
}
function b(){
	h = Jquery(window).height();
	t = Jquery(document).scrollTop();
	if(t > h){
		Jquery('#gotop').fadeIn('slow');
	}else{
		Jquery('#gotop').fadeOut('slow');
	}
}
Jquery(document).ready(function(e) {
	a(10,10);//#tbox的div距浏览器底部和页面内容区域右侧的距离
	b();
	Jquery('#gotop').click(function(){
		Jquery(document).scrollTop(0);
	})
});
Jquery(window).resize(function(){
	a(10,10);//#tbox的div距浏览器底部和页面内容区域右侧的距离
});

Jquery(window).scroll(function(e){
	b();
})