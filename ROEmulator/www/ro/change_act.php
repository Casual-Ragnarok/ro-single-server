<?PHP
include('config.php');
if (!empty($_POST['act'])) {
	$id = $_POST['id'];
	$oldpass = $_POST['oldpass'];
	$pass = $_POST['pwd'];
	$pass2 = $_POST['pwd1'];
	$email = $_POST['email'];

	$RegPD = 0;
	if (md5($_POST['auth_num']) != $_POST['authnum_p']) {
		$TempString1="<BR><font color='#ff0000'>验证码错误</font>";
		$TempString2="<BR><BR><FONT color=#0000FF><A href='change.php'>返回</a></FONT>";
		$RegPD = 1;
	}
	if (!isAlNum($id)) {
		$TempString1="<font color='#ff0000'>你输入的用户名不是数字或英文</font>";
		$TempString2="<BR><BR><FONT color=#0000FF><A href='change.php'>返回</a></FONT>";
		$RegPD = 1;
	}
	if (strlen($id) <4 and $RegPD != 1) {
		$TempString1="<font color='#ff0000'>你输入的用户名少于4个字符</font>";
		$TempString2="<BR><BR><FONT color=#0000FF><A href='change.php'>返回</a></FONT>";
		$RegPD = 1;
	}
	if (strlen($oldpass) <6 or strlen($pass) <6 or strlen($pass2) <6 and $RegPD != 1) {
		$TempString1="<font color='#ff0000'>你输入的密码少于6个字符</font>";
		$TempString2="<BR><BR><FONT color=#0000FF><A href='change.php'>返回</a></FONT>";
		$RegPD = 1;
	}
	if (!ismail($email) and $RegPD != 1) {
		$TempString1="<font color='#ff0000'>你的EMAIL格式错误</font>";
		$TempString2="<BR><BR><FONT color=#0000FF><A href='change.php'>返回</a></FONT>";
		$RegPD = 1;
	}
	if ($pass != $pass2 and $RegPD != 1) {
		$TempString1="<font color='#ff0000'>你输入的两次新密码输入不一样</font>";
		$TempString2="<BR><BR><FONT color=#0000FF><A href='change.php'>返回</a></FONT>";
		$RegPD = 1;	
	}
	if ($RegPD != 1) {
		$query = "select * from login where userid='$id'";
		$result = mysql_query($query);
		while ($r=mysql_fetch_array($result)) {
		$userid = $r['userid'];
		$user_pass = $r['user_pass'];
		$user_email = $r['email'];
		}

		if ($id == $userid and $oldpass == $user_pass and $email == $user_email) {
		$query = "UpDate login Set user_pass='$pass' Where userid='$id'";
		$result = mysql_query($query);
			if ($result) {
				$TempString1="<font color='#ff0000'>修改成功，请记妥善保管您的密码</font>";
			} Else {
				$TempString1="<font color='#ff0000'>修改失败,请重新填写资料</font>";
				$TempString2="<BR><BR><FONT color=#0000FF><A href='change.php'>返回</a></FONT>";
			}
		} Else {
				$TempString1="<font color='#ff0000'>用户名、密码或EMAIL错误</font>";
				$TempString2="<BR><BR><FONT color=#0000FF><A href='change.php'>返回</a></FONT>";
		}
	}
} Else {
	$TempString1="<font color='#ff0000'>错误访问,5秒后返回.</font>";
	$TempString2="<META http-equiv=refresh content='5; url=cpass.php'>";
}
echo "
<!DOCTYPE HTML PUBLIC '-//W3C//DTD HTML 4.0 Transitional//EN'>
<HTML><HEAD><TITLE>".$config_title." - 密码修改</TITLE>";
require "header.inc";
echo "
<TABLE style='POSITION: relative' cellSpacing=0 cellPadding=0 width=1004 align=center border=0>
<TBODY>
<TR>
<TD width=144 background=images/left_bg.jpg></TD>
<TD bgColor=#c5c5c5>
	<TABLE cellSpacing=0 cellPadding=0 width=716 border=0>
	<TBODY>
	<TR>
	<TD>
		<TABLE cellSpacing=0 cellPadding=0 width=716 border=0>
		<TBODY>
		<TR>
		<TD width=13><IMG height=12 src='images/table01_01.gif' width=13></TD>
		<TD width='100%' background=images/table01_bg1.gif height=12></TD>
		<TD width=13><IMG height=12 src='images/table01_02.gif' width=13></TD>
		</TR>
		</TBODY>
		</TABLE>
	</TD>
	</TR>
	<TR>
	<TD>
		<TABLE cellSpacing=0 cellPadding=0 width=716 border=0>
		<TBODY>
		<TR>
		<TD width=13 background=images/table01_bg2.gif>&nbsp;</TD>
		<TD bgColor=#ffefef>
			<TABLE cellSpacing=0 cellPadding=0 width=694 border=0>
			<TBODY>
			<TR>
			<TD height=94>
				<TABLE cellSpacing=0 cellPadding=0 width=694 border=0>
				<TBODY>
				<TR>
				<TD width=42><IMG height=94 src='images/table03_01.gif' width=42></TD>
				<TD vAlign=top background=images/table03_bg1.gif>
					<TABLE cellSpacing=0 cellPadding=0 width='100%' border=0>
					<TBODY>
					<TR>
					<TD width='76%'>
						<TABLE cellSpacing=0 cellPadding=0 width='100%' border=0>
						<TBODY>
						<TR>
						<TD height=55>
							<TABLE cellSpacing=0 cellPadding=0 width='84%' align=right border=0>
							<TBODY>
							<TR>
							<TD width=17><IMG height=13 src='images/heart.gif' width=17></TD>
							<TD class=txt><DIV align=center>".$config_game_Vname."</DIV></TD>
							<TD width=17><IMG height=13 src='images/heart.gif' width=17></TD>
							</TR>
							</TBODY>
							</TABLE>
						</TD>
						</TR>
						<TR>
						<TD height=39><IMG height=27 src='images/wz1_change.gif' width=209></TD>
						</TR>
						</TBODY>
						</TABLE>
					</TD>
					<TD vAlign=top width='22%'><IMG height=52 src='images/heart2.gif' width=64></TD>
					</TR>
					</TBODY>
					</TABLE>
				</TD>
				<TD width=43><IMG height=94 src='images/table03_02.gif' width=43></TD>
				</TR>
				</TBODY>
				</TABLE>
			</TD>
			</TR>	
			<TR>
			<TD height=181>
				<TABLE cellSpacing=0 cellPadding=0 width='100%' border=0>
				<TBODY>
				<TR>
				<TD>
					<TABLE cellSpacing=0 cellPadding=0 width=678 align=center border=0>
					<TBODY>
					<TR>
					<TD width=2><IMG height=2 src='images/table04_01.gif' width=2></TD>
					<TD background=images/table04_bg1.gif height=2></TD>
					<TD width=2><IMG height=2 src='images/table04_02.gif' width=2></TD>
					</TR>
					<TR>
					<TD width=2 background=images/table04_bg2.gif></TD>
					<TD bgColor=#ffffff>
<!-- 过程部分_开始 -->
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
<!-- 过程部分_结束 -->
					</TD>
					<TD width=2 background=images/table04_bg3.gif></TD>
					</TR>
					</TBODY>
					</TABLE>
				</TD>
				</TR>
				<TR>
				<TD>
					<TABLE cellSpacing=0 cellPadding=0 width=678 align=center border=0>
					<TBODY>
					<TR>
					<TD width=2><IMG height=3 src='images/table04_03.gif' width=2></TD>
					<TD width='100%' background=images/table04_bg4.gif height=3></TD>
					<TD width=2><IMG height=3 src='images/table04_04.gif' width=2></TD>
					</TR>
					</TBODY>
					</TABLE>
				</TD>
				</TR>
				</TBODY>
				</TABLE>
			</TD>
			</TR>
			</TBODY>
			</TABLE>
		</TD>
		<TD width=13 background=images/table01_bg3.gif>&nbsp;</TD>
		</TR>
		</TBODY>
		</TABLE>
	</TD>
	</TR>
	<TR>
	<TD>
		<TABLE cellSpacing=0 cellPadding=0 width=716 border=0>
		<TBODY>
		<TR>
		<TD width=167><IMG height=49 src='images/table01_03.gif' width=167></TD>
		<TD background=images/table01_bg4.gif>&nbsp;</TD>
		<TD width=13><IMG height=49 src='images/table01_04.gif' width=13></TD>
		</TR>
		</TBODY>
		</TABLE>
	</TD>
	</TR>
	</TBODY>
	</TABLE>
	<TABLE cellSpacing=0 cellPadding=0 width=715 align=center border=0>
	<TBODY>
	<TR>
	<TD vAlign=bottom><IMG height=55 src='images/table02_01.gif' width=76 border=0></TD>
	<TD vAlign=bottom><A href='".$config_bbs_url."' target=_blank><IMG height=55 src='images/table02_02.gif' width=128 border=0></A></TD>
	<TD vAlign=bottom><A href='".$config_bbs_url."' target=_blank><IMG height=55 src='images/table02_03.gif' width=126 border=0></A></TD>
	<TD vAlign=bottom><IMG height=55 src='images/table02_04.jpg' width=231></TD>
	<TD vAlign=bottom><IMG height=74 src='images/table02_05.gif' width=154></TD>
	</TR>
	</TBODY>
	</TABLE>
</TD>
<TD vAlign=top width=144 background=images/right_bg.jpg></TD>
</TR>
</TBODY>
</TABLE>";
require "footer.inc";
?>