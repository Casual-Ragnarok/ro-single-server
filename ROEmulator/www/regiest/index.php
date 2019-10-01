<html>
<head>
<style type="text/css">
<!--
.top_bar	{
	clear: both;
	height: 29px;
	line-height: 29px;
	text-align: center;
	background-color: transparent;
	background-image: url(images/top_bg_1.gif);
	background-repeat: repeat;
}
.top_bar a	{padding: 0 5px; color: #3399FF;}
.STYLE36 {
	font-family: "宋体";
	font-size: 12px;
}
-->
</style>
</head>
<BODY text=#009999 leftmargin="0" topmargin="0" rightmargin="0" onLoad="flevInitPersistentLayer('Layer16',0,'880','','','100','','',10)"  onUnload="leave()" onselectstart="return false">
<div class="top_bar"><!--{/if}-->
  <span class="STYLE36"><a href="http://www.loveqm.com/index.php">动漫游戏签名网</a> <a href="http://www.loveqm.com/forum-12-1.html">制作签名</a> <a href="http://www.loveqm.com/register.php">加入会员</a></span>&nbsp;<a href="http://www.loveqm.com/home/" class="STYLE1">我的空间</a></div>
<?PHP
include('config.php');
require "inc/header.inc";
echo "
<!-- Top Begin -->
<table width='1002' border='0' cellpadding='0' cellspacing='0' align='center'>
	<tr>
		<td width='1002' height='267' valign='top' background='images/top_logo.jpg'>
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
<!-- line_1 Begin -->
<table width='1002' border='0' cellpadding='0' cellspacing='0' align='center'>
	<tr>
	<!-- list_1 Begin -->
		<td width='212' valign='top' background='images/homebg1.jpg' align='center'>
			<table width='100%' border='0' cellpadding='0' cellspacing='0'>
				<tr>
				<td width='212' height='196' valign='top'>
				<!-- 服务器状态-Begin -->";
				error_reporting(0);
				$IP = array("#1" => "".$config_loginip.":".$config_loginport."","#2" => "".$config_charip.":".$config_charport."","#3" => "".$config_mapip.":".$config_mapport."",);
				while(list($ServerName,$Host)=each($IP)) {
				list($IPAddress,$Port)=explode(":",$Host);
					if($fp=fsockopen($IPAddress,$Port,$ERROR_NO,$ERROR_STR,(float)0.5)) {
						$Server_info=$Server_info+1;
					fclose($fp);
					}
				}
				if ($Server_info != 3) {
				echo "
				<img src='images/home_09s.jpg' width='212' height='196' align='center'>";
				} else {
				echo "
				<img src='images/home_09.jpg' width='212' height='196' align='center'>";
				}
				echo "
				<!-- 服务器状态-End -->
				</td>
				</tr>
			</table>
			<table width='100%' border='0' cellpadding='0' cellspacing='0'>
				<tr>
				<td valign='top'><a href='user_main.php?action=paylist'><img src='images/home_15.jpg' width='212' height='68' border='0'></a></td>
				</tr>
				<tr>
				<tr>
				<td><img src='images/home_23.jpg'></td>
				</tr>
				<td width='212' valign='top' background='images/home_24.jpg'>
					<table width='150' border='0' cellpadding='0' cellspacing='0' align='center'>
						<tr>
						<td>
						<!-- 游戏排行-Begin -->
						<table cellspacing=0 cellpadding=0 width='150' border='0'>
							<tr>
							<td width='120' height='20' align='center'><B>玩家名称</B></td>
							<td width='30' align='center'><B>等级</B></td>
							</tr>";
							$query="select * from `char` where account_id>'$config_list_gmid' order by '$config_index_list_order' desc limit 0,$config_index_list_num";
							$result = mysql_query($query);
							while ($r = mysql_fetch_array($result)) {
							printf("
							<tr>
							<TD height='20' align='center'>%s</TD>
							<TD align='center'>%s</TD>
							</tr>",$r['name'],$r['base_level']);
							}
						echo "
						</table>
						<!-- 游戏排行-End -->
						</td>
						</tr>
					</table>
				</td>
				</tr>
				<tr>
				<td height='10' background='images/home_30.jpg'></td>
				</tr>
				<tr>
				<td height='23' background='images/home_31.jpg'></td>
				</tr>
			</table>
		</td>
	<!-- list_1 End -->
	<!-- list_2 Begin -->
		<td width='595' valign='top' bgcolor='#FEDC00' background='images/homebg2.jpg'>
			<table width='100%' border='0' cellpadding='0' cellspacing='0'>
				<tr>
				<td width='21' height='194' valign='top' background='images/home_10.jpg'></td>
				<td width='562' valign='top' background='images/home_10_1.jpg'>
				<object classid='clsid:D27CDB6E-AE6D-11cf-96B8-444553540000' codebase='http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=7,0,19,0' width='562' height='194'>
					<param name='movie' value='images/adflash.swf'>
					<param name='quality' value='high'>
					<embed src='images/adflash.swf' quality='high' pluginspage='http://www.macromedia.com/go/getflashplayer' type='application/x-shockwave-flash' width='562' height='194'></embed>
				</object>
				</td>
				<td width='12' background='images/home_12.jpg'></td>
				</tr>
			</table>
			<table width='100%' border='0' cellpadding='0' cellspacing='0'>
				<tr>
				<td width='595' height='62' valign='top' background='images/home_14.jpg'></td>
				</tr>
			</table>
			<table width='100%' border='0' cellpadding='0' cellspacing='0'>
				<tr>
				<td width='26' valign='top' background='images/home_17.jpg'></td>
				<td vAlign='top' width='546'>
					<table width='100%' border='0' cellpadding='0' cellspacing='0'>
						<tr>
						<td><img src='images/home_18.jpg' width='546' height='52' border='0' usemap='#Map'></td>
						</tr>
						<tr>
						<td width='90%' valign='top' bgcolor='#03BBEF' class='style1'><b>".$config_bbs_gdscript."</b></td>
						</tr>
						<tr>
						<td><img src='images/home_34.jpg' width='546' height='45' border='0' usemap='#Map2'></td>
						</tr>
						<tr>
						<td width='90%' valign='top' bgcolor='#03BBEF' class='style1'><b>".$config_bbs_msgscript."</b></td>
						</tr>
					</table>
				</td>
				<td width='23' background='images/home_21.jpg'></td>
				</tr>
				<tr>
				<td height='16' colspan='3' background='images/home_39.jpg'></td>
				</tr>
			</table>
		</td>
	<!-- list_2 End -->
	<!-- list_3 Begin -->
		<td width='195' valign='top' bgcolor='#FEDC00'>
			<table width='100%' border='0' cellpadding='0' cellspacing='0'>
				<tr>
				<td width='195' height='234' valign='top'><img src='images/home_13.jpg' border='0' usemap='#Map13'></td>
				</tr>
			</table>
			<table width='100%' border='0' cellpadding='0' cellspacing='0'>
				<tr>
				<td width='195' height='85' background='images/home_16.jpg'></td>
				</tr>
				<tr>
				<td valign='top'>
					<table width='100%' border='0' cellpadding='0' cellspacing='0'>
						<tr>
						<td width='14' background='images/home_27.jpg'></TD>
						<td width='166' bgColor='#d3f651' align='center'>
						<a href='".$config_bbs_url."' target='_blank'><img src='images/ad_01.jpg' width='155' height='260' border='0'></a>
						</td>
						<td width='15' background='images/home_29.jpg'></TD>
					</table>
				</td>
				</tr>
				<tr>
				<td width='195' height='16' background='images/home_40.jpg'></td>
				</tr>
			</table>
		</td>
	<!-- list_3 End -->
	</tr>
</table>
<!-- line_1 End -->
<!-- line_2 Begin -->
<table width='1002' border='0' cellpadding='0' cellspacing='0' align='center'>
	<tr>
	<td height='110' background='images/banner1002_bg.jpg'>
	<object classid='clsid:D27CDB6E-AE6D-11cf-96B8-444553540000' codebase='http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=6,0,29,0' width='1002' height='110'>
		<param name='movie' value='images/1002x110.swf'>
		<param name='quality' value='high'>
		<param name='wmode' value='transparent'>
		<embed src='images/1002x110.swf' width='1002' height='110' quality='high' pluginspage='http://www.macromedia.com/go/getflashplayer' type='application/x-shockwave-flash' wmode='transparent'></embed>
	</object>
	</td>
	</tr>
</table>
<!-- line_2 End -->
<!-- line_3 Begin -->
<table width='1002' border='0' align='center' cellpadding='0' cellspacing='0'>
	<tr>
		<td width='502' valign='top'>
		<table width='100%' height='15' border='0' cellpadding='0' cellspacing='0' background='images/home_42.jpg'>
			<tr>
				<td></td>
			</tr>
		</table>
		<table width='100%' height='175' border='0' cellpadding='0' cellspacing='0'>
			<tr>
				<td height='49' background='images/home_44.jpg'>&nbsp;</td>
				<td valign='top' bgcolor='#FEFCEF'>
				<table width='100%' border='0' cellspacing='0' cellpadding='0'>
					<tr>
						<td height='49'><img src='images/home_45.jpg' width='469' height='49'></td>
					</tr>
				</table>
				</td>
				<td background='images/home_46.jpg'>&nbsp;</td>
			</tr>
			<tr>
				<td width='19' background='images/home_44.jpg'>&nbsp;</td>
				<td width='469' valign='middle' bgcolor='#FEFCEF'>
				<table width='100%' border='0' cellpadding='0' cellspacing='0'>
					<tr>
						<td><div align='center'><a href='other/flash01.zip' target='_blank'>
						<img src='other/flash01.jpg' width='216' height='125' border='0'>
						</a></div></td>
						<td><div align='center'><a href='other/flash02.zip' target='_blank'>
						<img src='other/flash02.jpg' width='216' height='125' border='0'>
						</a></div></td>
					</tr>
				</table>
				</td>
				<td width='14' background='images/home_46.jpg'>&nbsp;</td>
			</tr>
		</table>
		<table width='100%' height='18' border='0' cellpadding='0' cellspacing='0' background='images/home_55.jpg'>
			<tr>
				<td></td>
			</tr>
		</table>
		</td>
		<td valign='top'>
		<table width='100%' height='15' border='0' cellpadding='0' cellspacing='0' background='images/home_43.jpg'>
			<tr>
				<td></td>
			</tr>
		</table>
		<table width='100%' height='175' border='0' cellpadding='0' cellspacing='0'>
			<tr>
				<td width='15' background='images/home_47.jpg'>&nbsp;</td>
				<td width='469' valign='top' bgcolor='#FEFCEF'>
				<table width='100%' border='0' cellspacing='0' cellpadding='0'>
					<tr>
						<td><img src='images/home_48.jpg' width='469' height='49'></td>
					</tr>
				</table>
				<table width='100%' border='0' cellpadding='0' cellspacing='0'>
					<tr>
						<td><div align='center'><a href='other/pic01.jpg' target='_blank'>
						<img src='other/pic01s.jpg' width='216' height='125' border='0'>
						</a></div></td>
						<td><div align='center'><a href='other/pic02.jpg' target='_blank'>
						<img src='other/pic02s.jpg' width='216' height='125' border='0'>
						</a></div></td>
					</tr>
				</table>
				</td>
				<td width='16' background='images/home_49.jpg'>&nbsp;</td>
			</tr>
		</table>
		<table width='100%' height='18' border='0' cellpadding='0' cellspacing='0' background='images/home_56.jpg'>
			<tr>
				<td></td>
			</tr>
		</table>
		</td>
	</tr>
</table>
<!-- line_3 End -->
<!-- Main End -->
";
require "inc/footer.inc";
echo "
<map name='Map13'>
	<area shape='rect' coords='35,13,160,45' href='user_main.php?action=regist'>
	<area shape='rect' coords='35,55,160,86' href='user_main.php?action=repass'>
	<area shape='rect' coords='35,95,160,126' href='user_main.php?action=recall'>
	<area shape='rect' coords='35,135,160,168' href='user_main.php?action=download'>
</map>
<map name='Map'>
	<area shape='rect' coords='492,18,553,36' href='".$config_bbs_gdurl."' target='_blank'>
</map>
<map name='Map2'>
	<area shape='rect' coords='490,14,554,33' href='".$config_bbs_msgurl."' target='_blank'>
</map>
</body>
</html>";
?>
</body>
</html>
