<?PHP
include('config.php');
echo "
<!DOCTYPE HTML PUBLIC '-//W3C//DTD HTML 4.0 Transitional//EN'>
<HTML><HEAD><TITLE>".$config_title." - 账号注册</TITLE>";
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
						<TD height=39><IMG height=27 src='images/wz1_regist.gif' width=209></TD>
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
			<center><FORM method='post' action='regist_act.php' name='NewAccount'>
				<TABLE cellSpacing=0 cellPadding=0 width=400 align=center border=0>
				<INPUT type='hidden' name='act' value='create'>
				<INPUT type='hidden' name='authnum_p' value='".$md5authnum."'>
				<TR valign=middle align='center'>
				<TD colspan='3'><FONT color=#FF0000>＊表示必填资料</FONT></TD>
				</TR>
				<TR vAlign=middle align=left>
				<TD><FONT color=#ff0000>*</FONT></TD>
				<TD><FONT color=#000066>账　　号：</FONT></TD>
				<TD><INPUT class=box id='id_N' maxLength=16 size=16 name=id_N> 4-15位以内，限用英文与数字</TD>
				</TR>
				<TR vAlign=middle align=left>
				<TD><FONT color=#ff0000>*</FONT></TD>
				<TD><FONT color=#000066>密　　码：</FONT></TD>
				<TD><INPUT class=box id='pwd_P' type=password maxLength=16 size=16 name=pwd_P> 4-16位以内，限用英文与数字</TD>
				</TR>
				<TR vAlign=middle align=left>
				<TD><FONT color=#ff0000>*</FONT></TD>
				<TD><FONT color=#000066>确认密码：</FONT></TD>
				<TD><INPUT class=box id='pwd1_P' type=password maxLength=16 size=16 name=pwd1_P> </TD>
				</TR>
				<TR vAlign=middle align=left>
				<TD><FONT color=#ff0000>*</FONT></TD>
				<TD><FONT color=#000066>性　　别：</FONT></TD>
				<TD><INPUT type=radio CHECKED value='M' name=sex> 男 <INPUT type=radio value='F' name=sex> 女</TD>
				</TR>
				<TR vAlign=middle align=left>
				<TD><FONT color=#ff0000>*</FONT></TD>
				<TD><FONT color=#000066>电子邮箱：</FONT></TD>
				<TD><INPUT class=box id='email_E' maxLength=30 size=30 name=email_E> </TD>
				</TR>
				<TR vAlign=middle align=left>
				<TD vAlign=middle><FONT color=#ff0000>*</FONT></TD>
				<TD vAlign=middle><FONT color=#000066>验证码：</FONT></TD>
				<TD vAlign=middle><INPUT class=box id='auth_num' maxLength=5 size=5 name=auth_num> <img src='authimg.php?authnum=".$authnum."'></TD>
				</TR>
				<TR vAlign=middle align=left>
				<TD></TD>
				<TD colSpan=2><FONT color=#333333>☆ 申请时请认真填写您的电子信箱。</FONT></TD>
				</TR>
				<TR vAlign=middle align=left>
				<TD>&nbsp;</TD>
				<TD>&nbsp;</TD>
				<TD vAlign=bottom>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; <INPUT type=image height=18 width=78 src='images/reg_yes.gif'>&nbsp;&nbsp;&nbsp; <IMG onclick=reset(); src='images/reg_no.gif' width=78 height=18></TD>
				</TR>
				<TR><TD height='20'></TD></TR>
				</TABLE>
			</FORM></center>
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