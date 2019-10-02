<?PHP
echo "<!-- Footer Begin -->
<table height='164' cellSpacing='0' cellPadding='0' width='1002' align='center' background='images/home_57.jpg' border='0'>
	<tr>
	<td vAlign=top>
		<table class='span35' cellSpacing='0' cellPadding='0' width='100%' border='0'>
		<tr>
			<td width='17%'></td>
			<td width='68%'>
				<DIV class='bottom' align='center'>
				∞Ê»®À˘”–&copy;2009 ".$config_title." <BR>
				Copyright&copy;2009 ".$config_title2." Corporation. All rights reserved.<BR>
				</DIV>
			</td>
			<td width='15%'></td>
			</tr>
		</table>
	</td>
	</tr>
</table>
<script language='javascript'>
var popshow;
popshow=false;
function hidpop()
{
	document.getElementById('popdiv').style.display='none';
	popshow=false;
}
function showpop()
{
   	var doc_width = 602, doc_height =600;
	xp= Math.random()*(doc_width-50);
	yp= Math.random()*doc_height;
	document.getElementById('popdiv').style.display='block';
	document.getElementById('popdiv').style.top=xp;
	document.getElementById('popdiv').style.left=yp;
	popshow=true;
}
function showpopt()
{
	if(popshow)
	{
	}
	else
	{
		showpop();
	}
}
window.setInterval('showpopt()',popshowInterVal);
window.setInterval('hidpop();',pophidInterVal);
</script>
<!-- Footer End -->";
?>