# RO 客户端 DATA 文件夹详解

> http://bbs.rohome.net/thread-881030-1-1.html

------

data\wav   为怪物叫声和技能效果音

palette\赣府  里面的.pal为发型染色，每个职业为0~8种不同的发型颜色。

palette\个  里面的.pal为职业装染色，每个职业为0~4种不同的职业装颜色。

sprite    里面的.spr和.act为游戏所需基本补丁不要动它。

sprite\book    里面的.spr和.act为游戏所需基本补丁不要动它。

sprite\homun    里面的.spr和.act为炼金术士人工生命体外观补丁。可以把自己喜欢的外观替换上去。（命名要一致才被数据读取）

sprite\npc    里面的.spr和.act为游戏NPC外观补丁，可以把自己喜欢的外观替换上去。（命名要一致才被数据读取）

sprite\阁胶磐  里面的.spr和.act为游戏怪物外观补丁，可以把自己喜欢的外观替换上去。（命名要一致才被数据读取）

sprite\规菩  里面有很多文件夹，里面的.spr和.act为人物举盾时外观补丁可以把喜欢的外观替换上去。（命名要一致才被数据读取）

sprite\酒捞袍  里面的.spr和.act分2种一种是技能（英文的）补丁一种是物品（乱码）补丁可以把喜欢的外观替换上去。（命名要一致才被数据读取）

sprite\厩技荤府  里面分别为巢（男）和咯（女）的头饰外观补丁可以把喜欢的外观替换上去。（命名要一致才被数据读取）
注：要男的显示外观放在巢文件夹里，要女的显示外观放在咯文件夹里。

sprite\捞蒲飘   里面的.spr和.act为游戏部分场景效果补丁，可以把自己喜欢的外观替换上去。（命名要一致才被数据读取）

sprite\牢埃练   为人物数据文件夹。职业外观、选择发型、武器装备动作全在这个文件夹内。

sprite\牢埃练\赣府烹   为男女发型外观补丁，可以把自己喜欢的外观替换上去。（命名要一致才被数据读取）

sprite\牢埃练\个烹   为男女职业装外观补丁可以把喜欢的外观替换上去。（命名要一致才被数据读取）

data\texture   里面分别为图片格式外观补丁，大多为MAP界面图、效果图、登入界面图和物品外观图和解释图（右击物品所见的图）。在这里只要介绍效果图、登入界面图和物品外观图和解释图位置在那个文件夹里：
texture\effect 为效果图目录。
texture\蜡历牢磐其捞胶 为登入界面图和物品外观图和解释图目录。可以按图的大小对应修改（修改工具推荐Photoshop）
texture\蜡历牢磐其捞胶\cardbmp 为卡片图目录。可以按图的大小对应修改（修改工具推荐Photoshop）
texture\蜡历牢磐其捞胶\collection 为解释图目录。可以按图的大小对应修改（修改工具推荐Photoshop）
texture\蜡历牢磐其捞胶\illust 为游戏NPC图目录。可以用Photoshop修改自己个性图。
texture\蜡历牢磐其捞胶\item 为物品外观图目录。可以按图的大小对应修改（修改工具推荐Photoshop）
texture\蜡历牢磐其捞胶\map 为地图目录。如果不是自己做新的地图不推荐修改。
texture\蜡历牢磐其捞胶文件夹里从loading00.jpg开始的图为进出地图的LOGO图。可以代替自己喜欢的LOGO界面。

在data文件夹里有很多.txt文件，分别都有各自的作用。
ba_frostjoke.txt为诗人剧本
dc_scream.txt为舞娘剧本
cardprefixnametable.txt为卡片插到装备上的解释
etcinfo.txt为个城市天气效果的设定

格式如下：

```
loadingscreen#
3#
指令#对应地图#天气情况#
weather#prontera.rsw#pokjuk#
weather#prontera.rsw#pokjuk#
weather#amatsu.rsw#sakura#
event#
1#
//file end

// 下雨效果
rain
// 下雪效果
snow
// 云雾效果
clouds
// 起雾效果
fog
// 烟火效果
pokjuk
// 樱花效果
sakura
```

idnum2itemdesctable.txt 为物品解释
idnum2itemdisplaynametable.txt 为物品名字
idnum2itemresnametable.txt 为物品连接根据

如：一个红色药水组成。
在idnum2itemdisplaynametable.txt里修改他的名字：`501#红色药水#`

在idnum2itemdesctable.txt里修改他的解释：

```
501#
将红色的药草捣碎制成的体力恢复剂，^000088约可恢复45点HP。^000000
^ffffff_^000000
重量 : ^7777777^000000
#
```

在idnum2itemresnametable.txt里修改他的连接根据：`501#弧埃器记#`

相对的修改有:
Data\sprite\酒捞袍 里要加个弧埃器记.spr和弧埃器记.act补丁
Data\texture\蜡历牢磐其捞胶\collection 里要加个弧埃器记.bmp的解释图
Data\texture\蜡历牢磐其捞胶\item 里要加个弧埃器记.bmp的物品图

ndoorrswtable.txt为室内平视角限制
manner.txt为禁语限制
mapnametable.txt为地图名称（也就是打/where所见的信息）
mp3nametable.txt为地图背景音乐管理
msgstringtable.txt为游戏基本信息
num2cardillustnametable.txt为卡片连接根据

> 相对的连接为Data\texture\蜡历牢磐其捞胶\cardbmp里的图

num2itemdesctable.txt为未鉴定物品解释
num2itemdisplaynametable.txt为未鉴定物品名字
num2itemresnametable.txt为未鉴定物品连接根据
skilldesctable.txt为技能解释
skillnametable.txt为技能名字

在data文件夹里有很多.xml文件，分别都有各自的作用。
monstertalktable.xml为怪物的对话
pettalktable.xml为宠物的对话
sclientinfo.xml为服务器登入设置

DATA文件夹里的乱码含义（人物篇）
注：其中韩文部分必须在安装韩文显示之后才能正常显示
简体中文 韩文 繁体乱码 简体乱码
人物的设定文件在：\data\sprite\牢埃练\个烹
男：\data\sprite\牢埃练\个烹\巢
女：\data\sprite\牢埃练\个烹\咯

替换方法：
找到你需要替换的外观的文件（含.act和.spr各一个），复制到对应的性别目录中，然后改变文件名为你需要替换的职业

需要寻找的文件和需要替换的文件名字可以通过ctrl+f在下表查找，另如果看到的文件名都是钢管纹，就需要用转换器转换成乱码才行。

列表：
一转的部分：

檬焊磊_巢        初心者        檬焊磊_咯        初心者
八荤_巢        剑士        八荤_咯        剑士
档迪_巢        盗贼        档迪_咯        盗贼
惑牢_巢        商人        惑牢_咯        商人
泵荐_巢        弓箭手        泵荐_咯        弓箭手
付过荤_巢        法师        付过荤_咯        法师
己流磊_巢        服侍        己流磊_咯        服侍

二转的部分：

扁荤_巢        骑士        扁荤_咯        骑士
其内其内_扁荤_巢        骑鸟的骑士        其内其内_扁荤_咯        骑鸟的骑士
橇府胶飘_巢        牧师        橇府胶飘_咯        牧师
困历靛_巢        巫师        困历靛_咯        巫师
清磐_巢        猎人        清磐_咯        猎人
绢技脚_巢        刺客        绢技脚_咯        刺客
力枚傍_巢        铁匠        力枚傍_咯        铁匠
肺弊_巢        流氓        肺弊_咯        流氓
官靛_巢        诗人               
公锐_咯        舞娘
公锐_咯_官瘤        舞娘-领养
公锐官瘤_咯        舞娘-领养
根农_巢        武僧        根农_咯        武僧
楷陛贱荤_巢        炼金术师        楷陛贱荤_咯        炼金术师
技捞瘤_巢        贤者        技捞瘤_咯        贤者
农风技捞歹_巢        十字军        农风技捞歹_咯        十字军
脚其内农风技捞歹_巢        骑鸟的十字军        脚其内农风技捞歹_咯        骑鸟的十字军
备其内农风技捞歹_巢        骑鸟的十字军-领养        备其内农风技捞歹_咯        骑鸟的十字军-领养

转生二转的部分：

肺靛唱捞飘_巢        骑士领主        肺靛唱捞飘_咯        骑士领主
肺靛其内_巢        骑鸟的骑士领主        肺靛其内_咯        骑鸟的骑士领主
窍捞橇府_巢        神官        窍捞橇府_咯        神官
窍捞困历靛_巢        超魔        窍捞困历靛_咯        超魔
胶唱捞欺_巢        神射手        胶唱捞欺_咯        神射手
绢截脚农肺胶_巢        十字刺客        绢截脚农肺胶_咯        十字刺客
拳捞飘胶固胶_巢        神工匠        拳捞飘胶固胶_咯        神工匠
胶配目_巢        神行太保        胶配目_咯        神行太保
努扼款_巢        艺人               
笼矫_咯        吉普赛
猫乔柯_巢        武术宗师        猫乔柯_咯        武术宗师
农府俊捞磐_巢        创造者        农府俊捞磐_咯        创造者
橇肺其辑_巢        智者        橇肺其辑_咯        智者
迫扼凋_巢        圣殿骑士        迫扼凋_咯        圣殿骑士
其内迫扼凋_巢        骑鸟的圣殿骑士        其内迫扼凋_咯        骑鸟的圣殿骑士

转生二转零时档的部分：

己流磊_h_巢        转生服侍-零时        己流磊_h_咯        转生服侍-零时
扁荤_h_巢        转生骑士-零时        扁荤_h_咯        转生骑士-零时
其内其内_扁荤_h_巢        骑鸟的转生骑士-零时        其内其内_扁荤_h_咯        骑鸟的转生骑士-零时
橇府胶飘_h_巢        转生牧师-零时        橇府胶飘_h_咯        转生牧师-零时
困历靛_h_巢        转生巫师-零时        困历靛_h_咯        转生巫师-零时
清磐_h_巢        转生猎人-零时        清磐_h_咯        转生猎人-零时
绢技脚_h_巢        转生刺客-零时        绢技脚_h_咯        转生刺客-零时
力枚傍_h_巢        转生铁匠-零时        力枚傍_h_咯        转生铁匠-零时
肺弊_h_巢        转生流氓-零时        肺弊_h_咯        转生流氓-零时
官靛_h_巢        转生诗人-零时               
公锐_h_咯        转生舞娘-零时
根农_h_巢        转生武僧-零时        根农_h_咯        转生武僧-零时
楷陛贱荤_h_巢        转生炼金术师-零时        楷陛贱荤_h_咯        转生炼金术师-零时
技捞瘤_h_巢        转生贤者-零时        技捞瘤_h_咯        转生贤者-零时
农风技捞歹_h_巢        转生十字军-零时        农风技捞歹_h_咯        转生十字军-零时
脚其内农风技捞歹_h_巢        骑鸟的转生十字军-零时        脚其内农风技捞歹_h_咯        骑鸟的转生十字军-零时

三转的部分：

烽唱捞飘_巢        卢恩骑士        烽唱捞飘_咯        卢恩骑士
烽唱捞飘悔鹅_巢        骑龙的卢恩骑士-绿        烽唱捞飘悔鹅_咯        骑龙的卢恩骑士-绿
烽唱捞飘悔鹅2_巢        骑龙的卢恩骑士-黑        烽唱捞飘悔鹅2_咯        骑龙的卢恩骑士-黑
烽唱捞飘悔鹅3_巢        骑龙的卢恩骑士-白        烽唱捞飘悔鹅3_咯        骑龙的卢恩骑士-白
烽唱捞飘悔鹅4_巢        骑龙的卢恩骑士-蓝        烽唱捞飘悔鹅4_咯        骑龙的卢恩骑士-蓝
烽唱捞飘悔鹅5_巢        骑龙的卢恩骑士-红        烽唱捞飘悔鹅5_咯        骑龙的卢恩骑士-红
酒农厚俭_巢        大主教        酒农厚俭_咯        大主教
况废_巢        大法师        况废_咯        大法师
饭牢廉_巢        游侠        饭牢廉_咯        游侠
饭牢廉戳措_巢        骑狼的游侠        饭牢廉戳措_咯        骑狼的游侠
辨肺凭农肺胶_巢        十字切割者        辨肺凭农肺胶_咯        十字切割者
固纳葱_巢        机匠        固纳葱_咯        机匠
付档扁绢_巢        坐魔装甲的机匠        付档扁绢_咯        坐魔装甲的机匠
溅档快眉捞辑_巢        逐影        溅档快眉捞辑_咯        逐影
刮胶飘凡_巢        宫廷乐师               
盔歹矾_咯        漫游舞者
酱扼_巢        修罗        酱扼_咯        修罗
力匙腐_巢        基因学者        力匙腐_咯        基因学者
家辑矾_巢        元素使        家辑矾_咯        元素使
啊靛_巢        皇家卫士        啊靛_咯        皇家卫士
弊府迄啊靛_巢        骑狮鹫的皇家卫士        弊府迄啊靛_咯        骑狮鹫的皇家卫士

特殊一二转的部分：

酱欺畴厚胶_巢        超初        酱欺畴厚胶_咯        超初
怕鼻家斥_巢        跆拳道        怕鼻家斥_咯        跆拳道
鼻己_巢        拳圣        鼻己_咯        拳圣
鼻己蓝钦_巢        融合状态的拳圣        鼻己蓝钦_咯        融合状态的拳圣
家匡傅目_巢        灵媒        家匡傅目_咯        灵媒
囱磊_巢        忍者        囱磊_咯        忍者
扒呈_巢        枪手        扒呈_咯        枪手

佣兵的部分：

八侩捍        剑士佣兵               
劝侩捍        弓箭-佣兵
芒侩捍        枪佣兵               

其他的部分：

搬去_巢        新郎        搬去_咯        新娘
咯抚_巢        夏日装        咯抚_咯        夏日装
魂鸥_巢        圣诞装        魂鸥_咯        圣诞装
款康磊_巢        GM外观1        款康磊_咯        GM外观1
款康磊2_巢        GM外观2-高达        款康磊2_咯        GM外观2-高达
盼矫档_巢        燕尾服               
傀爹        婚纱


========== 武器 ==========
短剑     단검       钦匐     窜八
剑     검       匐       八
弓     활            劝
斧头     도끼       紫郭     档尝
枪     창       璽       芒
钝器     클럽       贗毁     努反
魔杖     롯드       煜萄     吩靛
书     책       畴       氓
拳刃     카타르_카타르   苹颤脑_苹颤脑   墨鸥福_墨鸥福
吉他     악기       学晦     厩扁
鞭子     채찍       瓣鎰     盲嘛
拳套     (不明)     (不明)     (不明)



为了方便大家自己修改
放出我自己收集的部分补丁效果列表：

尸人不透明效果补丁
%RoRoot%\data\sprite\阁胶磐

怒雷陨石完全免疫无震动
%RoRoot%\data\texture\怒雷陨石免疫\
lord.str
meteor1.str
meteor2.str
meteor3.str
meteor4.str
----------------------------------------------
小地图强化
%RoRoot%\data\texture\蜡历牢磐其捞胶\map
----------------------------------------------
效果简化文件清单
%RoRoot%\data\texture\effect\

暴风雪
stormgust.str
smoke.tga
plazma_a.bmp
plazma_b.bmp
plazma_c.bmp
puyan.bmp
shockwave_c.bmp
shockwave_d.bmp
shockwave_e.bmp
twirl_soft.bmp
snow_a.bmp
snow_b.bmp

怒雷强击
lord.str
lord__soft
plazma_a.bmp

泥沼弱化
bubble_a.bmp
bubble_b.bmp
bubble_d.bmp

黑暗免疫
white02.bmp
fullb.tga

烟雾去除
fog1.tga
fog2.tga
fog3.tga

塔罗牌文字解释
tarot01.tga~tarot14.tga

音速投掷简化
sonicblow.str

阿修罗霸凰拳
asum.str
asura1.tga~asura16.tga

死塔小地图
tha_t03.bmp
tha_t04.bmp
tha_t05.bmp
tha_t06.bmp
tha_t07.bmp
tha_t08.bmp
tha_t09.bmp
tha_t10.bmp
tha_t11.bmp
tha_t12.bmp
tha_scene01.bmp
thana_boss.bmp
thana_step.bmp

和尚气弹
thunder_center.bmp
----------------------------------------------



卡片放大补丁
\data\sprite\酒捞袍\捞抚绝绰墨靛.act（不必备）
\data\sprite\酒捞袍\捞抚绝绰墨靛.spr


生体研究所魔物染色补丁
\data\sprite\阁胶磐\armaia.act
\data\sprite\阁胶磐\eremes.act
\data\sprite\阁胶磐\erend.act
\data\sprite\阁胶磐\harword.act
\data\sprite\阁胶磐\katrinn.act
\data\sprite\阁胶磐\kavac.act
\data\sprite\阁胶磐\magaleta.act
\data\sprite\阁胶磐\rawrel.act
\data\sprite\阁胶磐\seyren.act
\data\sprite\阁胶磐\shecil.act
\data\sprite\阁胶磐\whikebain.act
\data\sprite\阁胶磐\ygnizem.act


天使赐福简化补丁
\data\sprite\捞蒲飘\particle1.spr
\data\sprite\捞蒲飘\particle3.spr
\data\sprite\捞蒲飘\particle4.spr
\data\sprite\捞蒲飘\particle5.spr
\data\sprite\捞蒲飘\particle6.spr
\data\sprite\捞蒲飘\particle7.spr
\data\sprite\捞蒲飘\status-sleep.spr
\data\sprite\捞蒲飘\绵汗.spr


暴风雪效果简化补丁
\data\texture\effect\plazma_a.bmp
\data\texture\effect\plazma_b.bmp
\data\texture\effect\plazma_c.bmp
\data\texture\effect\puyan.bmp
\data\texture\effect\sg___empty.bmp
\data\texture\effect\shockwave_c.bmp
\data\texture\effect\shockwave_d.bmp
\data\texture\effect\shockwave_e.bmp
\data\texture\effect\snow_a.bmp
\data\texture\effect\snow_b.bmp
\data\texture\effect\storm_god.bmp
\data\texture\effect\twirl_soft.bmp


怒雷效果简化补丁(有地面震动)
\data\texture\effect\lord.str
\data\texture\effect\lordy.bmp
\data\texture\effect\lord_a.bmp
\data\texture\effect\lord_b.bmp
\data\texture\effect\lord_empty.bmp
\data\texture\effect\lord_p_a.bmp
\data\texture\effect\lord_p_b.bmp
\data\texture\effect\lord_p_c.bmp
\data\texture\effect\lord_wave_a.bmp
\data\texture\effect\lord_wave_b.bmp
\data\texture\effect\lord_wave_c.bmp
\data\texture\effect\lord_wave_d.bmp
\data\texture\effect\lord_wave_e.bmp
\data\texture\effect\lord__god.bmp
\data\texture\effect\lord__soft.bmp
\data\texture\effect\sg___empty.bmp
\data\texture\effect\lord_blast_e.inbmp.bmp
\data\texture\effect\lord_blast_e_small.bmp
\data\texture\effect\lord_blast_f.bmp
\data\texture\effect\lord_blast_stick_b.bmp
\data\texture\effect\lord_stick_a.bmp
\data\texture\effect\lord_blast_1_small.bmp
\data\texture\effect\lord_blast_a.bmp
\data\texture\effect\lord_blast_b.bmp
\data\texture\effect\lord_blast_c.bmp
\data\texture\effect\lord_blast_d.bmp
\data\texture\effect\lord_blast_e.bmp
\data\texture\effect\lord_stick_h.bmp
\data\texture\effect\lord_stick_g.bmp
\data\texture\effect\lord_stick_f.bmp
\data\texture\effect\lord_stick_e.bmp
\data\texture\effect\lord_stick_c.bmp
\data\texture\effect\lord_stick_b.bmp
\data\texture\effect\lord_stick_a.bmp
\data\texture\effect\lord_stick_plazma_a.bmp
\data\texture\effect\lord_stick_plazma_b.bmp
\data\texture\effect\lord_stick_plazma_c.bmp


黑暗效果消除补丁
\data\texture\effect\fullb.tga
\data\texture\effect\white02.bmp


阿修罗霸凰拳简化补丁
\data\texture\effect\asura1.tga
\data\texture\effect\asura2.tga
\data\texture\effect\asura3.tga
\data\texture\effect\asura4.tga
\data\texture\effect\asura5.tga
\data\texture\effect\asura6.tga
\data\texture\effect\asura11.tga
\data\texture\effect\asura12.tga
\data\texture\effect\asura13.tga
\data\texture\effect\asura14.tga
\data\texture\effect\asura15.tga
\data\texture\effect\asura16.tga


钢铁都市除尘补丁
\data\texture\effect\fog1.tga
\data\texture\effect\fog2.tga
\data\texture\effect\fog3.tga


灵媒的大蓝字去除
\data\texture\effect\soul_i.tga
\data\texture\effect\soul_k.tga
\data\texture\effect\soul_l.tga
\data\texture\effect\soul_n.tga
\data\texture\effect\soul_o.tga
\data\texture\effect\soul_s.tga
\data\texture\effect\soul_u.tga


魔法阵简化补丁
\data\texture\effect\magic_target.tga


魔法锁定效果
\data\texture\effect\lockon128.tga


99级光环补丁
\data\texture\effect\auraring.bmp
\data\texture\effect\freezing_circle.bmp（2转）
\data\texture\effect\whitelight.tga（3转）


塔罗纸牌补丁
\data\texture\effect\tarot01.tga
\data\texture\effect\tarot02.tga
\data\texture\effect\tarot03.tga
\data\texture\effect\tarot04.tga
\data\texture\effect\tarot05.tga
\data\texture\effect\tarot06.tga
\data\texture\effect\tarot07.tga
\data\texture\effect\tarot08.tga
\data\texture\effect\tarot09.tga
\data\texture\effect\tarot10.tga
\data\texture\effect\tarot11.tga
\data\texture\effect\tarot12.tga
\data\texture\effect\tarot13.tga
\data\texture\effect\tarot14.tga


蛛网视觉效果补强
\data\texture\effect\spiderweb.tga


光之障壁视觉效果补强
\data\texture\effect\freeze_1_small.bmp
\data\texture\effect\freeze_a.bmp
\data\texture\effect\freeze_a_small.bmp
\data\texture\effect\freeze_ice_part.bmp


地属性领域
\data\texture\effect\aaa copy


火焰阵简化
\data\sprite\捞蒲飘\拳堪柳.spr










data\sprite\捞蒲飘
                   emotion.ACT   韩国新表情（小站MAKIYO大大发过）
                            emotion.spr 韩国新表情
                   status-sleep.spr 睡眠状态ZZZ改为红色
data\texture\郴何家前
                   arch.BMP    下水道去顶棚（SD区特别有用）
                            BAK_arch.BMP   下水道去顶棚（SD区特别有用）
                   thor_column02.BMP  火山石头柱子去顶
data\texture\蜡历牢磐其捞胶\basic_interface
                            ro_shop.BMP 缩小游戏窗口右下角商城图标
data\texture\蜡历牢磐其捞胶\login_interface
                           warning.BMP 登陆画面
                  win_select.BMP 人物选择画面
data\wav\effect   游戏中各种技能的效果声音，包括舞蹈等

data\texture\effect 各种游戏技能效果
蛛网效果增强
spiderweb.tga
黑暗效果弱化
white02.bmp
fullb.tga
暗影效果补强
foot_l_b.tga
foot_r_b.tga
AB爆气效果修改
super1.bmp~super5.bmp
大地之击效果关闭
crashearth.str
泥沼技能效果改变为贴在地上的绿色小泡泡
lord_blast_2_small.bmp
quagmire.str
99级人物光环效果修改
freezing_circle.bmp
pikapika2.bmp
ring_blue.tga
whitelight.tga