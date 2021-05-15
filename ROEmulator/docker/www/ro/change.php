<?PHP
include('config.php');
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
<!-- 过程部分_开始 -->";
				$authnum=rand(10000,99999);
				$md5authnum=md5($authnum);
				echo "
				<center>
				<font color=#ff0000><b>密码修改</b></font>
				<form action='change_act.php' method='post' name='pwdForm' id='pwdForm'>
				<TABLE cellSpacing=0 cellPadding=0 width=400 align=center border=0>
				<INPUT type='hidden' name='act' value='change'>
				<INPUT type='hidden' name='authnum_p' value='".$md5authnum."'>
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
				<TD align=left><INPUT class=box id='auth_num' maxLength=5 size=5 name=auth_num> <img src='authimg.php?authnum=".$authnum."'></TD>
				</TR>
				<tr>
				<td height=10></td>
				</tr>
				<TR>
				<TD colspan='3' align=center><INPUT type=image height=18 width=78 src='images/reg_yes.gif'>&nbsp;&nbsp;&nbsp; <IMG onclick=reset(); src='images/reg_no.gif' width=78 height=18></TD>
				</TR>
				</TABLE>
				</FORM>
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