<?PHP
include('config.php');
require "inc/user_header.inc";
	$RePD = 0;
	if (md5($_POST['md5lock']) != $_POST['md5lockx']) {
		$TempString1="<BR><font color='#FF0000'>验证码错误</font>";
		$TempString2="<BR><BR><a href='user_regist.php'>回上一页</a>";
		$RegPD = 1;
	}
	if (empty($_POST['id'])) {
		$TempString1="<font color='#ff0000'>未输入帐号</font>";
		$TempString2="<a href=user_recall.php>回上一页</a>";
		$RegPD = 1;
	}
	if (empty($_POST['passwd']) and $RegPD != 1) {
		$TempString1="<font color='#ff0000'>未输入密码</font>";
		$TempString2="<a href=user_recall.php>回上一页</a>";
		$RegPD = 1;
	}
	if (empty($_POST['id']) or empty($_POST['passwd']) and $RegPD != 1){
		$TempString1="<font color='#ff0000'>错误 #013</font>";
		$TempString2="<a href=user_recall.php>回上一页</a>";
		$RegPD = 1;
	}
if ($RegPD != 1) {
	$query = "SELECT account_id,password FROM login WHERE username='".$_POST['id']."'";
	$result = mysql_query($query) or die("执行指令失败!");
	$data = mysql_fetch_row($result);
	$account_id=$data[0];
	$password=$data[1];
	if($_POST['passwd']!=$password){
		$TempString1="<font color='#ff0000'>帐号或密码错误!</font>";
		$TempString2="<a href=user_recall.php>回上一页</a>";
	}else{
	echo "
	<TABLE cellSpacing=0 cellPadding=0 width=500 align=center border=0>
		<TR>
			<TD width=400 rowspan='3' vAlign=top>
				<center>
				<form name=form1 method=post action=user_recall_msg.php>
				<table width=400 align='center'>
				<tr>
				<td width='100%'><div align='center'>请选择人物：<br></td>
				</tr>
				<tr>
				<td width='242'><div align='center'><br><select name=select >";
				$query = "SELECT `name` FROM `char` WHERE account_id=".$account_id.";";
				$result = mysql_query($query) or die("执行指令失败!");
				$row = mysql_num_rows($result);
				for($i=0;$i<$row;$i++){
				$data = mysql_fetch_row($result);
				echo "\t<option value=".$data[0].">".$data[0]."</option>\n";
				}
				echo "
				</select></div></td></tr><tr><td><br>
				<div align='center'><input type='checkbox' class='box' name='recall'>&nbsp;&nbsp;将人物传回储存点</div><br><br>	
				<div align='center'><input type='checkbox' class='box' name='divest'>&nbsp;&nbsp;卸下身上的所有装备</div></td></tr>
				</table><br><div align='center'><input type=submit class='box' name=Submit value=&nbsp;确&nbsp;定&nbsp;></div></form>
				</center>
			</TD>
		</TR>
	</TABLE>";
	require "inc/user_footer.inc";
	exit;
	}
}
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
				<TD height=60><center>".$TempString1."".$TempString2."</center></TD>
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
