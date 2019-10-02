<?PHP
include('config.php');
if (!empty($_POST['select'])){
		$charname=$_POST['select'];
		$query = "SELECT `last_map` FROM `char` WHERE name='".$charname."';";
		$result = mysql_query($query) or die("执行指令失败!");
		$data = mysql_fetch_row($result);
	if ($data[0]==$config_not_use){
		$TempString1="<FONT color=#0000FF>操作失败</FONT><br>";
		$TempString2="<FONT color=#0000FF>禁止传送的地图</FONT><br>";
	}else{
		$query = "SELECT `char_id`,`save_map`,`save_x`,`save_y` FROM `char` WHERE name='".$charname."';";
		$result = mysql_query($query) or die("执行指令失败!");
		$data = mysql_fetch_row($result);
		$charid=$data[0];$savemap=$data[1];$savex=$data[2];
		$savey=$data[3];
		if ($_POST['recall']==on){
			$query="UPDATE `char` SET `last_map`='".$savemap."',`last_x`='".$savex."',`last_y`='".$savey."' WHERE `name`='".$charname."';";
			$result = mysql_query($query) or die("执行指令失败!");
			$TempString1="<br><FONT color=#0000FF>已经将&nbsp;".$charname."&nbsp;传回储存点了</FONT>";
		}
		if ($_POST['divest']==on){
			$query="UPDATE `char` SET `head_top`='0',`head_mid`='0',`head_bottom`='0' WHERE `name`='".$charname."';";
			$result = mysql_query($query) or die("执行指令失败!");
			$query="UPDATE `inventory` SET `equip`=0 WHERE `char_id`='".$charid."';";
			$result = mysql_query($query) or die("执行指令失败!");
			$TempString2="<br><FONT color=#0000FF>已经将&nbsp;".$charname."&nbsp;身上的所有装备卸下了</FONT>";
		}
	}
} Else {
	$TempString1="<font color='#ff0000'>错误访问,5秒后返回.</font>";
	$TempString2="<META http-equiv=refresh content='5; url=user_recall.php'>";
}
require "inc/user_header.inc";
echo "
<TABLE cellSpacing=0 cellPadding=0 width=500 align=center border=0>
	<TR>
		<TD width=400 rowspan='3' vAlign=top>
			<!--过程部分-->
			<center>
				<TABLE cellSpacing=0 cellPadding=0 width=400 align=center border=0>
				<TR valign=middle align='center'>
				<TD height=30>&nbsp;</TD>
				</TR>
				<TR>
				<TD><center>".$TempString1."".$TempString2."<BR><BR><A href='index.php'target='_top'>返回</a></center></TD>
				</TR>
				<TR>
				<TD height=30>&nbsp;</TD>
				</TR>
				</TABLE>
			</center>
			<!--过程部分-->
		</TD>
	</TR>
</TABLE>";
require "inc/user_footer.inc";
?>