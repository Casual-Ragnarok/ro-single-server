<?PHP
include('config.php');
if (!empty($_POST['act'])) {
	$id = $_POST['id'];
	$oldpass = $_POST['oldpass'];
	$pass = $_POST['pwd'];
	$pass2 = $_POST['pwd1'];
	$email = $_POST['email'];

	$RegPD = 0;
	if (md5($_POST['md5lock']) != $_POST['md5lockx']) {
		$TempString1="<BR><font color='#ff0000'>验证码错误</font>";
		$TempString2="<BR><BR><A href='user_repass.php'>返回</a>";
		$RegPD = 1;
	}
	if (!isAlNum($id)) {
		$TempString1="<font color='#ff0000'>你输入的用户名不是数字或英文</font>";
		$TempString2="<BR><BR><A href='user_repass.php'>返回</a>";
		$RegPD = 1;
	}
	if (strlen($id) <4 and $RegPD != 1) {
		$TempString1="<font color='#ff0000'>你输入的用户名少于4个字符</font>";
		$TempString2="<BR><BR><A href='user_repass.php'>返回</a>";
		$RegPD = 1;
	}
	if (strlen($oldpass) <6 or strlen($pass) <6 or strlen($pass2) <6 and $RegPD != 1) {
		$TempString1="<font color='#ff0000'>你输入的密码少于6个字符</font>";
		$TempString2="<BR><BR><A href='user_repass.php'>返回</a>";
		$RegPD = 1;
	}
	if (!ismail($email) and $RegPD != 1) {
		$TempString1="<font color='#ff0000'>你的EMAIL格式错误</font>";
		$TempString2="<BR><BR><A href='user_repass.php'>返回</a>";
		$RegPD = 1;
	}
	if ($pass != $pass2 and $RegPD != 1) {
		$TempString1="<font color='#ff0000'>你输入的两次新密码输入不一样</font>";
		$TempString2="<BR><BR><A href='user_repass.php'>返回</a>";
		$RegPD = 1;	
	}
	if ($RegPD != 1) {
		$query = "select * from login where username='$id'";
		$result = mysql_query($query);
		while ($r=mysql_fetch_array($result)) {
		$username = $r['username'];
		$password = $r['password'];
		$user_email = $r['email'];
		}

		if ($id == $username and $oldpass == $password and $email == $user_email) {
		$query = "UpDate login Set password='$pass' Where username='$id'";
		$result = mysql_query($query);
			if ($result) {
				$TempString1="<font color='#ff0000'>修改成功，请记妥善保管您的密码</font>";
				$TempString2="<BR><BR><A href='index.php'target='_top'>返回</a>";
			} Else {
				$TempString1="<font color='#ff0000'>修改失败,请重新填写资料</font>";
				$TempString2="<BR><BR><A href='user_repass.php'>返回</a>";
			}
		} Else {
				$TempString1="<font color='#ff0000'>用户名、密码或EMAIL错误</font>";
				$TempString2="<BR><BR><A href='user_repass.php'>返回</a>";
		}
	}
} Else {
	$TempString1="<font color='#ff0000'>错误访问,5秒后返回.</font>";
	$TempString2="<META http-equiv=refresh content='5; url=user_repass.php'>";
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
