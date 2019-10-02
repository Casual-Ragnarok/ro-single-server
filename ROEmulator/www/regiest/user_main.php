<?PHP
include('config.php');
require "inc/header.inc";
/*判断显示页面 Begin*/
if ($_GET['action'] == "regist") {
	$MsgString="<iframe height='350' width='512' name='main' marginWidth='0' marginHeight='0' src='user_regist.php' frameBorder='0' scrolling='auto' bordercolor=''></iframe>";
	$MsgPicture="title_regist.jpg";
}
elseif ($_GET['action'] == "repass") {
	$MsgString="<iframe height='350' width='512' name='main' marginWidth='0' marginHeight='0' src='user_repass.php' frameBorder='0' scrolling='auto' bordercolor=''></iframe>";
	$MsgPicture="title_repass.jpg";
}
elseif ($_GET['action'] == "recall") {
	$MsgString="<iframe height='350' width='512' name='main' marginWidth='0' marginHeight='0' src='user_recall.php' frameBorder='0' scrolling='auto' bordercolor=''></iframe>";
	$MsgPicture="title_recall.jpg";
}
elseif ($_GET['action'] == "paylist") {
	$MsgString="<iframe height='350' width='512' name='main' marginWidth='0' marginHeight='0' src='user_paylist.php' frameBorder='0' scrolling='auto' bordercolor=''></iframe>";
	$MsgPicture="title_paylist.jpg";
}
elseif ($_GET['action'] == "download") {
	$MsgString="<iframe height='350' width='512' name='main' marginWidth='0' marginHeight='0' src='user_download.php' frameBorder='0' scrolling='auto' bordercolor=''></iframe>";
	$MsgPicture="title_download.jpg";
}
elseif ($_GET['action'] == "forum") {
	$MsgString="<META http-equiv=refresh content='0; url=".$config_bbs_url."'>";
	$MsgPicture="title_sys.jpg";
}
else {
	$MsgString="<font color='#FF0000'><b>错误的访问参数！</b></font>";
	$MsgPicture="title_sys.jpg";
}
/*判断显示页面 End*/
echo "
<!-- Top Begin -->
<table width='1002' border='0' cellpadding='0' cellspacing='0' align='center'>
	<tr>
		<td width='1002' height='277' cellSpacing='0' cellPadding='0' valign='top' align='center' background='images/top_logo_2.jpg' border='0'>
		<object classid='clsid:D27CDB6E-AE6D-11cf-96B8-444553540000' codebase='http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=6,0,29,0' width='1002' height='120'>
			<param name='movie' value='images/menu_intor.swf'>
			<param name='quality' value='high'>
			<param name='wmode' value='transparent'>
			<embed src='images/menu_intor.swf' width='1002' height='120' quality='high' pluginspage='http://www.macromedia.com/go/getflashplayer' type='application/x-shockwave-flash' wmode='transparent'></embed>
		</object>
		</td>
	</tr>
</table>
<!-- Top End -->
<!-- Main Begin -->
<table width='1002' border='0' align='center' cellPadding='0' cellSpacing='0' bgcolor='#FEE072'>
	<tr>
		<td vAlign='top' width='231' background='images/intor_20.jpg' height='382'>
			<table cellSpacing='0' cellPadding='0' width='100%' border='0'>
			<tr>
				<td><img src='images/menu.jpg' width='231' height='382' border='0' usemap='#menu'></td>
			</tr>
			</table>
		</td>
		<td width='623' rowSpan='2' vAlign='top'>
			<table cellSpacing='0' cellPadding='0' width='100%' border='0'>
			<tr>
				<td><IMG height='30' src='images/".$MsgPicture."' width='623'></td>
			</tr>
			</table>
			<table width='100%' cellSpacing='0' cellPadding='0' border='0'>
			<tr>
				<td width='36' background='images/intor_16.jpg'></td>
				<td vAlign='top' width='551' bgColor='#FFFCF3'>
					<table class='span20' cellSpacing='0' cellPadding='0' width='93%' align='center' border='0'>
					<tr>
						<td vAlign='middle' height='350'>
						<P>".$MsgString."</P>
						</td>
					</tr>
					</table>
				</td>
				<td vAlign='top' width='36' background='images/intor_18.jpg'></td>
			</tr>
			</table>
			<table width='100%' cellSpacing=0 cellPadding=0 border=0>
			<tr>
				<td height='40' background='images/intor_22.jpg'></td>
			</tr>
			</table>
		</td>
		<td vAlign='top' width='148' rowSpan='2'><IMG height='160' src='images/intor_15.jpg' width='148'></td>
	</tr>
	<tr>
		<td vAlign='bottom' background='images/intor_20.jpg'><IMG height='119' src='images/intor_21.jpg' width='231'></td>
	</tr>
</table>
<!-- Main End -->
";
require "inc/footer.inc";
echo "
<map name='menu'>
	<area shape='rect' coords='68,53,160,79' href='user_main.php?action=regist' target='_top'>
	<area shape='rect' coords='68,87,160,113' href='user_main.php?action=repass' target='_top'>
	<area shape='rect' coords='68,124,159,150' href='user_main.php?action=recall' target='_top'>
	<area shape='rect' coords='68,160,159,186' href='user_main.php?action=paylist' target='_top'>
	<area shape='rect' coords='68,198,158,224' href='user_main.php?action=download' target='_top'>
	<area shape='rect' coords='68,235,158,261' href='".$config_bbs_url."' target='_blank'>
</map>
</body>
</html>";
?>