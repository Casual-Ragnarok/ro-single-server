<?PHP
include('config.php');
if (!empty($_POST['act'])) {
	$id = $_POST["id_N"];
	$pass1 = $_POST["pwd_P"];
	$pass2 = $_POST["pwd1_P"];
	$email = $_POST["email_E"];
	$sex = $_POST["sex"];
	
	$RegPD = 0;
	if (md5($_POST['md5lock']) != $_POST['md5lockx']) {
		$TempString1="<BR><font color='#FF0000'>验证码错误</font>";
		$TempString2="<BR><BR><a href='user_regist.php'>回上一页</a>";
		$RegPD = 1;
	}
	if (!isAlNum($id)) {
		$TempString1="<BR><font color='#FF0000'>请使用数字或英文作用户名</font>";
		$TempString2="<BR><BR><A href='user_regist.php'>返回</a>";
		$RegPD = 1;
	}
	if (strlen($id) <4 and $RegPD != 1) {
		$TempString1="<BR><font color='#FF0000'>用户名不得少于4个字符</font>";
		$TempString2="<BR><BR><A href='user_regist.php'>返回</a>";
		$RegPD = 1;
	}
	if (strlen($pass1) <6 or strlen($pass2) <6 and $RegPD != 1) {
		$TempString1="<BR><font color='#FF0000'>你输入的密码少于6个字符</font>";
		$TempString2="<BR><BR><A href='user_regist.php'>返回</a>";
		$RegPD = 1;
	}
	if (!ismail($email) and $RegPD != 1) {
		$TempString1="<BR><font color='#FF0000'>你的EMAIL格式错误</font>";
		$TempString2="<BR><BR><A href='user_regist.php'>返回</a>";
		$RegPD = 1;
	}
	if ($pass1 != $pass2 and $RegPD != 1) {
		$TempString1="<BR><font color='#FF0000'>两次密码输入不符</font>";
		$TempString2="<BR><BR><A href='user_regist.php'>返回</a>";
		$RegPD = 1;
	}
	if ($RegPD != 1) {
		$query="select username from login where username='$id'";
		$check = mysql_query($query,$connect);
		$total_count = mysql_affected_rows();
		if($total_count>=1) {
		$TempString1="<BR><font color='#FF0000'>你注册的用户名已存在</font>";
		$TempString2="<BR><BR><A href='user_regist.php'>返回</a>";
		$RegPD = 1;
		}
		if ($RegPD != 1) {
		$query="insert into login (account_id, userid, user_pass, sex, email,level) values ('$accountno','$id','$pass1','$sex','$email','0')";
		$result = mysql_query($query);
			if ($result) {
				$TempString1="<BR><font color='#FF0000'>帐号注册成功，开始您的仙境之旅吧！</font>";
				$TempString2="<BR><BR><A href='index.php'target='_top'>返回</a>";
			}else{
				$TempString1="<BR><font color='#FF0000'>注册失败,请重新填写资料</font>";
				$TempString2="<BR><BR><A href='user_regist.php'>返回</a>";
			}
		}
	}
} Else {
	$TempString1="<font color='#FF0000'>错误访问,5秒后返回.</font>";
	$TempString2="<META http-equiv=refresh content='5; url=user_regist.php'>";
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
