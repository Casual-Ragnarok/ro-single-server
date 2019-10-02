<?PHP
include('config.php');
require "inc/user_header.inc";
echo "
<TABLE cellSpacing=0 cellPadding=0 width=500 align=center border=0>
	<TR>
		<TD width=400 rowspan='3' vAlign=top>
			<!--过程部分-->";
			$authnum=rand(1000,9999);
			$md5authnum=md5($authnum);
			echo "	<center>
			<form action='user_repass_act.php' method='post' name='pwdForm' id='pwdForm'>
			<TABLE cellSpacing=0 cellPadding=0 width=400 align=center border=0>
			<INPUT type='hidden' name='act' value='change'>
			<INPUT type='hidden' name='md5lockx' value='".$md5authnum."'>
			<TR>
			<TD colspan='3'><FONT color=#FF0000>＊表示必填资料</FONT></TD>
			</TR>
			<TR>
			<TD align=left><FONT color=#ff0000>*</FONT>&nbsp;</TD>
			<TD align=left><font color='#000066'>帐　　号：</font></TD>
			<TD align=left><INPUT class=box id='id' maxLength=16 size=20 name='id'></TD>
			</TR>
			<TR>
			<TD align=left><FONT color=#ff0000>*</FONT>&nbsp;</TD>
			<TD align=left><font color='#000066'>旧 密 码：</font></TD>
			<TD align=left><INPUT class=box id='oldpass' type=password maxLength=16 size=20 name='oldpass'></TD>
			</TR>
			<TR>
			<TD align=left><FONT color=#ff0000>*</FONT>&nbsp;</TD>
			<TD align=left><font color='#000066'>新 密 码：</font></TD>
			<TD align=left><INPUT class=box id='pwd' type=password maxLength=16 size=20 name='pwd'></TD>
			</TR>
			<tr>
			<TD align=left><FONT color=#ff0000>*</FONT>&nbsp;</TD>
			<TD align=left><FONT color=#000066>确认密码：</FONT></TD>
			<TD align=left><INPUT class=box id='pwd1' type=password maxLength=16 size=20 name='pwd1'></TD>
			</TR>
			<tr>
			<TD align=left><FONT color=#ff0000>*</FONT>&nbsp;</TD>
			<TD align=left><FONT color=#000066>电子邮箱：</FONT></TD>
			<TD align=left><INPUT class=box id='email' maxLength=30 size=30 name='email'></TD>
			</TR>
			<TR>
			<TD align=left><FONT color=#ff0000>*</FONT></TD>
			<TD align=left><FONT color=#000066>验证码：</FONT></TD>
			<TD align=left><INPUT class=box id='md5lock' maxLength=5 size=5 name=md5lock> <img src=\"authimg.php?authnum=".$authnum."\"></TD>
			</TR>
			<tr>
			<td height=10></td>
			</tr>
			<TR>
			<TD colspan='3' align=center><INPUT type=image src='images/btn_yes.gif' width='110' height='50'>&nbsp;&nbsp;&nbsp; <IMG onclick=reset(); src='images/btn_no.gif' width='110' height='50' border='0'></TD>
			</TR>
			</TABLE>
			</FORM>
			</center>
			<!--过程部分-->
		</TD>
	</TR>
</TABLE>";
require "inc/user_footer.inc";
?>