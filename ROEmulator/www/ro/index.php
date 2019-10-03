<?PHP
include('config.php');
echo "
<!DOCTYPE HTML PUBLIC '-//W3C//DTD HTML 4.0 Transitional//EN'>
<HTML><HEAD><TITLE>".$config_title."</TITLE>";
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
	<TD bgColor=#ffffff>
		<TABLE cellSpacing=0 cellPadding=0 width=694 border=0>
		<TBODY>
		<TR>
		<TD colspan='2'>
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
						<TD class=txt><DIV align=center>".$config_patch_Vname."</DIV></TD>
						<TD width=17><IMG height=13 src='images/heart.gif' width=17></TD>
						</TR>
						</TBODY>
						</TABLE>
					</TD>
					</TR>
					<TR>
					<TD height=39><IMG height=27 src='images/wz1.gif' width=209></TD>
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
		<TD colspan='2'>
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
					<TABLE cellSpacing=4 cellPadding=0 width='100%' border=0>
					<TBODY>
					<TR>
					<TD colSpan=4 height=30><DIV align=center><SPAN class=txt><IMG height=46 src='images/c_http1.gif' width=228 border=0></SPAN></DIV></TD>
					</TR>
					<TR>
					<TD width='14%' height=30><DIV align=center><SPAN class=txt><A href='".$config_cilent_url1."' target=_blank>百度网盘(dgui)</A></SPAN></DIV></TD>
					<TD width='14%'><DIV align=center><SPAN class=txt><A href='".$config_cilent_url2."' target=_blank>99Max(收费)</A></SPAN></DIV></TD>
					<TD width='14%'><DIV align=center><SPAN class=txt><A href='".$config_cilent_url3."' target=_blank>韩国官网</A></SPAN></DIV></TD>
					<TD width='14%'><DIV align=center><SPAN class=txt><A href='".$config_cilent_url4."' target=_blank>本地下载</A></SPAN></DIV></TD>
					</TR>
					</TBODY>
					</TABLE>
					<TABLE cellSpacing=4 cellPadding=0 width='100%' border=0>
					<TBODY>
					<TR>
					<TD height=30 colspan='4'><DIV class=txt align=center><IMG src='images/c_http2.gif' width=228 height=46 border=0></DIV></TD>
					</TR>
					<TR>
					<TD width='14%' height=30><DIV class=txt align=center><A href='".$config_patch_url1."' target=_blank>百度网盘(rkk0)</A></DIV></TD>
					<TD width='14%'><DIV align=center><SPAN class=txt><A href='".$config_patch_url2."' target=_blank>99Max(收费)</A></SPAN></DIV></TD>
					<TD width='14%'><DIV align=center><SPAN class=txt><A href='".$config_patch_url3."' target=_blank>本地下载一</A></SPAN></DIV></TD>
					<TD width='14%'><DIV align=center><SPAN class=txt><A href='".$config_patch_url4."' target=_blank>本地下载二</A></SPAN></DIV></TD>
					</TR>
					</TBODY>
					</TABLE>
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
		<TR>
		<TD width='8' height='14'>&nbsp;</TD>
		<TD width='686'>&nbsp;</TD>
		</TR>
		<TR>
		<TD height='71'>&nbsp;</TD>
		<TD valign='top'>
			<TABLE cellSpacing=0 cellPadding=0 width=678 align=center border=0>
			<TBODY>
			<TR>
			<TD width='678'>
				<TABLE style='BORDER-RIGHT: #949494 1px solid; BORDER-TOP: #949494 1px solid; BORDER-LEFT: #949494 1px solid; BORDER-BOTTOM: #949494 0px solid' cellSpacing=0 cellPadding=0 width=678 align=center border=0>
				<TBODY>
				<TR>
				<TD height=40>
					<TABLE cellSpacing=0 cellPadding=0 width=674 align=center border=0>
					<TBODY>
					<TR>
					<TD width=160><IMG height=38 src='images/table05_04.gif' width=160></TD>
					<TD background=images/table05_bg2.gif>&nbsp;</TD>
					</TR>
					</TBODY>
					</TABLE>
				</TD>
				</TR>
				</TBODY>
				</TABLE>
			</TD>
			<td width='8'></td>
			</TR>
			<TR>
			<TD>
				<TABLE cellSpacing=0 cellPadding=0 width=678 align=center border=0>
				<TBODY>
				<TR>
				<TD width=2><IMG height=3 src='images/table04_03.gif' width=2></TD>
				<TD width='100%' background=images/table05_bg1.gif height=3></TD>
				<TD width=2><IMG height=3 src='images/table04_04.gif' width=2></TD>
				</TR>
				</TBODY>
				</TABLE>
			</TD>
			<td></td>
			</TR>
				<TR>
				<TD>
					<TABLE style='BORDER-RIGHT: #949494 1px solid; BORDER-TOP: #949494 0px solid; BORDER-LEFT: #949494 1px solid; BORDER-BOTTOM: #949494 0px solid' cellSpacing=0 cellPadding=0 width=678 align=center border=0>
					<TBODY>
					<TR>
					<TD bgColor=#ffffff>
						<TABLE cellSpacing=5 cellPadding=0 width='90%' align=center border=0>
						<TBODY>
						<TR>
						<TD width='632' height='14' align='left' valign='top'>".$config_bbs_msgscript."</TD>
						</TR>
						</TBODY>
						</TABLE>
					</TD>
					</TR>
					</TBODY>
					</TABLE>
				</TD>
				<td></td>
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
				<td></td>
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
</TBODY>";
require "footer.inc";
?>