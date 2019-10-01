<?PHP
include('config.php');
require "inc/user_header.inc";
echo "
<TABLE cellSpacing=0 cellPadding=0 width=500 align=center border=0>
	<TR>
		<TD width=400 rowspan='3' vAlign=top>
				<!--过程部分-->";
				$authnum2=rand(1000,9999);
				$md5authnum2=md5($authnum2);
				echo "	<center>
				<form action='user_recall_act.php' method=post name=form1>
				<INPUT type='hidden' name='md5lockx' value='".$md5authnum2."'>
				<table align='center' cellpadding='0' cellspacing='0' border='0' width='400'>
				<tr>
				<td height=10></td>
				</tr>
				<tr>
				<TD align=left><FONT color=#ff0000>*</FONT>&nbsp;</TD>
				<td align=left><font color='#000066'>帐　　号：</font></td>
				<td align=left><input name='id' type='text' class='box' id='id' size='20' maxlength='16'></td>
				</tr>
				<tr>
				<TD align=left><FONT color=#ff0000>*</FONT>&nbsp;</TD>
				<td align=left><font color='#000066'>密　　码：</font></td>
				<td align=left><input name='passwd' type='password' class='box' id='passwd' size='20' maxlength='16'></td>
				</tr>
				<TR>
				<TD align=left><FONT color=#ff0000>*</FONT>&nbsp;</TD>
				<TD align=left><FONT color=#000066>验 证 码：</FONT></TD>
				<TD align=left><INPUT class=box id='md5lock' maxLength=5 size=5 name=md5lock> <img src=\"authimg.php?authnum=".$authnum2."\"></TD>
				</TR>
				<tr>
				<td height=10></td>
				</tr>
				<TR>
				<TD colspan='3' align=center><INPUT type=image src='images/btn_yes.gif' width='110' height='50'>&nbsp;&nbsp;&nbsp; <IMG onclick=reset(); src='images/btn_no.gif' width='110' height='50' border='0'></TD>
				</TR>
				</table>
				</form>
				</center>
				<!--过程部分-->

		</TD>
	</TR>
</TABLE>";
require "inc/user_footer.inc";
?>