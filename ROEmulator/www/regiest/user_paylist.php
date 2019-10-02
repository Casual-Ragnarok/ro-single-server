<?PHP
include('config.php');
require "inc/user_header.inc";
echo "<table cellspacing=0 cellpadding=0 width='500' border='0'>
	<TR bgcolor='F4F4F4'>
		<TD width='10%' height='20' align='center' class=f_one><B>排名</B></TD>
		<TD width='20%' height='20' align='left' class=f_one>&nbsp;&nbsp;<B>角色名称</B></TD>
		<TD width='14%' height='20' align='center' class=f_one><B>职业</B></TD>
		<TD width='13%' height='20' align='center' class=f_one><B>BASE LV</B></TD>
		<TD width='13%' height='20' align='center' class=f_one><B>JOB LV</B></TD>
		<TD width='20%' height='20' align='right' class=f_one><B>所在工会</B>&nbsp;&nbsp;</TD>
	</TR>";
		$query="select * from `char` where account_id>'$config_list_gmid' order by '$config_list_order' desc limit 0,$config_list_num";
		$result = mysql_query($query);
		$i = "1";$bg = "#f4f4f4";
		while ($r = mysql_fetch_array($result)) {
			if ($bg == "#ffffff") {
				$bg = "#f4f4f4";
			}elseif ($bg == "#f4f4f4") {
				$bg = "#ffffff";
			}
		switch ($r['class']) {
			case 0:$class = "初心者";break;case 1:$class = "剑士";break;case 2:$class = "法師";break;
			case 3:$class = "弓箭手";break;case 4:$class = "服士";break;case 5:$class = "商人";break;
			case 6:$class = "盗贼";break;case 7:$class = "骑士";break;case 8:$class = "牧师";break;
			case 9:$class = "巫师";break;case 10:$class = "铁匠";break;case 11:$class = "猎人";break;
			case 12:$class = "刺客";break;case 13:$class = "骑士(鸟)";break;case 14:$class = "十字军";break;
			case 15:$class = "武僧";break;case 16:$class = "贤者";break;case 17:$class = "流氓";break;
			case 18:$class = "炼金术士";break;case 19:$class = "诗人";break;case 20:$class = "舞者";break;
			case 21:$class = "十字军(鸟)";break;case 23:$class = "超级初心者";break;case 4001:$class = "进阶初学者";break;
			case 4002:$class = "进阶剑士";break;case 4003:$class = "进阶法師";break;case 4004:$class = "进阶弓箭手";break;
			case 4005:$class = "进阶服士";break;case 4006:$class = "进阶商人";break;case 4007:$class = "进阶盗贼";break;
			case 4008:$class = "骑士领主(鸟)";break;case 4009:$class = "神官";break;case 4010:$class = "超魔导士";break;
			case 4011:$class = "银匠";break;case 4012:$class = "神射手";break;case 4013:$class = "十字刺客";break;
			case 4014:$class = "骑士领主";break;case 4015:$class = "圣殿十字军";break;case 4016:$class = "武术宗师";break;
			case 4017:$class = "智者";break;case 4018:$class = "神行太保";break;case 4019:$class = "创造者";break;
			case 4020:$class = "搞笑艺人";break;case 4021:$class = "冷艳舞姬";break;case 4022:$class = "圣殿十字军(鸟)";break;
			case 4023:$class = "初心者宝宝";break;case 4024:$class = "剑士宝宝";break;case 4025:$class = "法师宝宝";break;
			case 4026:$class = "弓箭手宝宝";break;case 4027:$class = "服士宝宝";break;case 4028:$class = "商人宝宝";break;
			case 4029:$class = "盗贼宝宝";break;case 4030:$class = "骑士宝宝";break;case 4031:$class = "牧师宝宝";break;
			case 4032:$class = "巫师宝宝";break;case 4033:$class = "铁匠宝宝";break;case 4034:$class = "猎人宝宝";break;
			case 4035:$class = "刺客宝宝";break;case 4036:$class = "骑士宝宝(鸟)";break;case 4037:$class = "十字军宝宝";break;
			case 4038:$class = "武僧宝宝";break;case 4039:$class = "贤者宝宝";break;case 4040:$class = "流氓宝宝";break;
			case 4041:$class = "炼金术士宝宝";break;case 4042:$class = "诗人宝宝";break;case 4043:$class = "舞娘宝宝";break;
			case 4044:$class = "十字军宝宝(鸟)";break;case 4045:$class = "超级初心者宝宝";break;
		}
		$gid = $r['guild_id'];
		$query2="select * from guild where guild_id = '$gid' limit 1";
		$result2 = mysql_query($query2);
		$r2 = mysql_fetch_array($result2);
		printf("<tr bgcolor=$bg>
		    <TD height='20' align='center' class=f_one>%s</TD>
		    <TD height='20' align='left' class=f_one>&nbsp;&nbsp;%s</TD>
		    <TD height='20' align='center' class=f_one>%s</TD>
		    <TD height='20' align='center' class=f_one>%s</TD>
		    <TD height='20' align='center' class=f_one>%s</TD>
		    <TD height='20' align='center' class=f_one>%s</TD>
		</tr>",$i++,$r['name'],$class,$r['base_level'],$r['job_level'],$r2['name']);}
echo "</TABLE>";
require "inc/user_footer.inc";
?>