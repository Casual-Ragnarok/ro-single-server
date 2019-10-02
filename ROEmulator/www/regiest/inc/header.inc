<?PHP
echo "<!-- Header Begin -->
<!DOCTYPE HTML PUBLIC '-//W3C//Dtd HTML 4.0 transitional//EN'>
<html>
<head>
<title>".$config_title."</title>
<link href='images/main.css' type=text/css rel=stylesheet>
<meta http-equiv='Content-Type' content='text/html; charset=gb2312' />
<STYLE type=text/css>
BODY {
	BACKGROUND-IMAGE: url(images/homeditu.jpg)
}
.style1 {
	COLOR: #ffffff
}
.style2 {
	FONT-SIZE: 16px
}
.style3 {
	COLOR: #ff0000
}
</STYLE>
<script src='images/AC_RunActiveContent.js' type='text/javascript'></script>
<script language='javascript'>
var popshowInterVal=1000;  //显示时间间隔;
var pophidInterVal=1000; //隐藏时间;
var flashWidth=480;		//显示宽度;
var flashHeight=400;   //显示高度;
var popsrc='images/pop.swf';  //泡泡显示flash
function popwin(obj_form)
{
window.open('','popvote','toolbar=no,location=no,directories=no,status=no,menubar=no,scrollbars=no,resizable=no,copyhistory=yes,width=420,height=200,');
obj_form.target='popvote';
return true;
}
</script>
<script language='JavaScript' type='text/javascript' src='images/flash.js'></script>
<meta content='MSHTML 6.00.2900.3020' name=generator>
</head>
<body bgColor=#ffffff leftMargin=0 topMargin=0 marginheight='0' marginwidth='0'>
<div id='popdiv' style='POSITION: absolute; Z-INDEX: 999;' ><script language='JavaScript' type='text/javascript'>writeFlashHTML2('_swf='+popsrc, '_width='+flashWidth, '_height='+flashHeight,'_wmode=transparent')</script></div>
<!-- Header End -->";
?>