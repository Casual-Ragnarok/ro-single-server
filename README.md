# RO-Single-Server

> 仙境RO传说-单机版-服务端（支持联网开服）

------


## 关于网游单机化的历史背景
<details>
<summary>展开查看</summary>
<br/>

> 参考来源：《[还记得大明湖畔的RO么？一起来搭建自己的仙境传说](http://www.360doc.com/content/15/0713/07/7863900_484558332.shtml)》

所谓的网游单机版，就是把网络游戏服务器架设在自己的电脑上，通过客户端进行本地连接，让 C/S（client/server） 架构在一台计算机上完成，达到网游单机的效果。

RO 在网游中算是一个比较典型的存在，它的服务端只有逻辑代码，体积十分小（大约 200M 左右）。它的大部分的素材渲染都是集中在客户端实现，使得客户端相对庞大（到目前为止已经达到 3 ~ 4G ）。

因此，在架设单机的过程中，更多开发是集中在客户端部分，对官方原版的客户端进行素材扩充与渲染解析，这就是为什么我们玩私服时需要先下载一个韩服/台服/日服的客户端，然后还要下一个私服的客户端补丁覆盖到其中。

而在客户端补丁中，尤其重要的就是登陆器，它的作用是使得官方客户端的连接请求可以指向私服（或本地搭建的服务端），而不是官方服务器。

------

虽然不知道 RO 服务端的源码是否曾经泄露过，但是现在网上充斥着它的大量私服是不争的事实。

不过这些私服服务器，大多都是游戏 <b>模拟器</b>。

模拟器的概念相信很多人都不陌生了，比如在 PC 平台上通过模拟器玩 PS 平台的游戏、玩 GBA 的有游戏等等...

大多数网游的模拟器都是各游戏社区自己组织开发者，通过对游戏客户端进行逆向开发的，模拟服务端的响应行为。

因此不同的模拟器比官方服务器，根据其开发者的水平，会有各种不同程度的 BUG。

简而言之，模拟器就只是官方服务器的一个近似的镜像而已。

------

RO 的模拟器种类有很多，最主流的是 Athena（雅典娜） 系列。

Athena 也有很多系列分支，如曾经国人开发的 cAthena、 日本的 jAthena ，现在还勉强活着的 eAthena 等等...

<b>本单机服务器使用的正正就是 eAthena （下文简称 EA ）</b>。

[EA](https://github.com/eathena/eathena) 是在 Github 上的一个免费开源项目，所以使用 EA 做 RO 模拟器，只要不涉及商业利益就是合法的。 

> 注： EA 的源码是 C 语言写的，需编译使用。 但它的官方域名 eathena.ws 已过期并被挟持，就不要随便打开了

------

这里再扩展介绍一下 SeAthena （下文简称 SeA ）。

它是由 Inkfish 做的一个汉化版 EA ，现在 <b>大部分私服都是使用 SeA 做的</b>。

原因是 SeA 的收费版有很多不错的扩展功能（但免费版则有限制）。

所以如果想搭建自己的 RO 私服，EA 还是比较靠谱的，不仅免费而且方便 DIY 。

但是如果怕麻烦，使用 SeA 也是一个不错的选择，而且 [SeA 的论坛](http://www.4fro.cn/forum.php) 也是一个不错的学习地方。


</details>



## 关于此单机版

<details>
<summary>展开查看</summary>
<br/>

此 RO 单机版是在 [99Max](http://www.99max.me/) 对韩服官方的二次开发基础上，再次进行 <b>破解</b> 的。

之所以要破解，是因为 99Max 原本一直提倡都是做免费的 RO 单机，而且因为坚持与韩服 KRO 同步更新，算是做得不错的。

但是从 v8.11.0 版本（这是 99Max 的二次开发版本号，不是 RO 的版本号）开始，99Max 摒弃了以往的理念、违背了 EA 的协议，开起了淘宝店盈利，实在令人不齿。 作为 RO 的钻粉之一，那就不要怪我黑吃黑咯。

于是本人花了 ¥200 从 99Max 买了最新的 v8.19.0 的服务端和客户端，然后就有了这个破解版的 RO 单机。

------

顺带一提，破解原理其实很简单。

启动服务端后，不难发现在地图服务器 `map-server.exe` 运行的时候，会弹出一个激活码窗口。

对比 EA 的源码，很明显 99Max 对 `map-server.exe` 加了一个激活用的壳。

该激活码是比较经典的机器码注册方式，点击 `继续试用` 可以获得 30 天的试用期。

通过测试可以发现以下特征：

- 直接修改系统时间到 30 天后，重启服务器就会提示已过期
- 过期后删除服务端再重新解压，依然提示过期，说明记录试用期的时间点不在服务端的文件夹内
- 检查系统 `%temp%` 目录，点击试用前后并没有生成特别的文件（包括隐藏文件）
- 用 OD 稍微反汇编了一下 `map-server.exe` ，发现大量读写系统注册表的行为
- 社工了一下 99Max 的淘宝客服，她透露了不是联机校验，因为只会对硬盘、 CPU、 主板信息进行识别，所以重装系统不会导致激活失效

综上所述，不难判断 99Max 把试用期写到了系统注册表。

于是科学地监听了该进程对注册表的读写，发现每次点击 `继续试用` 的时候，注册表地址 `HKCU\Software\Classes\{49064D4F-D3C0-8818-C173-74BE82606519}` 就会被读写一次。

该注册表项的内容是加密的，虽然不知道加密算法，但是 <b>直接删除该注册表项即可重置试用期</b> 了，这样也省得脱壳了。

为了方便操作，我把此删除动作封装成 DOS 脚本，只要过期后执行一下（未过期也可执行），就可以永久试用了。

> 注：注册表地址 `HKCU` 是 `HKEY_CURRENT_USER` 的缩写

![](https://github.com/lyy289065406/ro-single-server/blob/master/img/00.png)


</details>


## 运行环境

　![](https://img.shields.io/badge/Platform-Windows%207%2f8%2f10%20x64-brightgreen.svg) ![](https://img.shields.io/badge/Platform-Windows%20Server%202003%2f2012%20x64-brightgreen.svg) 



## 版本说明 & 下载

- EA 服务端模拟器：v8.19.0 【[Github:免费已破解](https://github.com/lyy289065406/ro-single-server)】【[百度网盘(zx39):VMWare永久激活](https://pan.baidu.com/s/1M-W-bra6h16Bq7vqPI_Rng)】~【[99Max:收费](http://www.99max.me/thread-12926-1-1.html)】~
- 服务端环境修复包： DirectXRepair-v3.9 【[百度网盘(vs1m)](https://pan.baidu.com/s/1zoBXTi5rp7Yj1bhzMzo-oQ)】 (如果可以直接运行就不用装了)
- 韩服客户端（三转复兴后）：Ragnarok_KRO_20190306_Lite 【[百度网盘(dgui)](https://pan.baidu.com/s/1vrh-9wE29tfZvDiS10wkxw)】【[韩国官网](http://ro.gnjoy.com/pds/down/)】~【[99Max:收费](http://www.99max.me/thread-485-1-1.html)】~
- 客户端补丁（登陆器）：v4.3 【[百度网盘(rkk0)](https://pan.baidu.com/s/1qVFAwz55pdz-e_qTyjaXQg)】~【[99Max:收费](http://www.99max.me/thread-3674-1-1.html)】~

> 备：
<br/>　服务端历史版本更新内容: [view](https://github.com/lyy289065406/ro-single-server/tree/master/history/version-server.md)
<br/>　客户端历史版本更新内容: [view](https://github.com/lyy289065406/ro-single-server/tree/master/history/version-client.md)



## 安装教程

<details>
<summary>展开查看</summary>
<br/>

### 安装 & 启动服务端

- 服务端只能运行于 Windows 系统
- 安装 git，执行命令 `git clone https://github.com/lyy289065406/ro-single-server` 下载 EA 服务端
- 不懂 git 的同学可以直接点击本 [Github](https://github.com/lyy289065406/ro-single-server) 仓库的 【Clone and download】 ，解压后也是一样的
- 服务端可放到任意位置（<b>路径不要有中文</b>）
- 双击运行 `01-启动架设环境.bat` 并等待窗口关闭，会启动 mysql（用于存档） 与 Apache（用于注册、论坛等）
- 双击运行 `02-启动RO服务端.bat`，会依次自动启动：
<br/>　○ 角色服务器 `char-server.exe`
<br/>　○ 登陆服务器 `login-server.exe`
<br/>　○ 地图服务器 `map-server.exe` （此时会提示注册，点击 【继续试用】 按钮即可）

> 注：
<br/>　若启动过程中报错丢失 0xc000007b，安装环境修复包 DirectXRepair 即可
<br/>　服务端运行过程中不要关闭 `login-server.exe`、 `char-server.exe`、 `map-server.exe`
<br/>　因 mysql 内置在服务端，懂 git 的同学可以 Fork 这个仓库，再 Checkout 一个分支就可以用 github 远程备份存档了

![](https://github.com/lyy289065406/ro-single-server/blob/master/img/01.png)
![](https://github.com/lyy289065406/ro-single-server/blob/master/img/02.png)


### 停止服务端

- 手动关闭 `login-server.exe`、 `char-server.exe`、 `map-server.exe` 窗口
- 双击运行 `03-关闭架设环境.bat`
- 双击运行 `04-重置试用时间.bat` （可选，只要未过期都可以不执行）

> 注：重置试用时间并不会影响存档，存档是在 mysql 数据库中的


### 安装 & 启动客户端

- 客户端只能运行于 Windows 系统
- 下载韩服客户端（版本必须是 Ragnarok_KRO_20190306_Lite）
- 下载客户端补丁（版本必须是 v4.3）
- 解压客户端到任意位置（<b>路径不要有中文</b>）
- 解压客户端补丁到韩服客户端根目录，同名文件全覆盖
- 双击运行 `Setup_Plus.exe` 修改配置
- 双击运行 `99Max仙境传说_v4.3_Data.exe` 即可进入游戏


</details>



## 内置站点

- Discuz! X3.2 系统（含门户/论坛/空间）： [http://127.0.0.1:8096](http://127.0.0.1:8096)
- Discuz! 管理员后台（账密 admin/admin）： [http://127.0.0.1:8096/admin.php](http://127.0.0.1:8096/admin.php)
- 玩家注册页： [http://127.0.0.1:8096/ro](http://127.0.0.1:8096/ro) （同时支持 M/F 注册系统）

> 备： RO 的 M/F 注册系统
<br/>　M 表示 男
<br/>　F 表示 女
<br/>　玩家通过客户端正常启动游戏
<br/>　第一次登陆时，在帐号栏里填写 abc_M 或者 abc_F，其中 abc 就是要注册的帐号，密码栏填写要注册的密码
<br/>　第二次登陆时，把后面的 \_M/\_F 去掉，即可正常登陆



## 版本特色

- 三转职业最高 BASE 等级 175, 最高 JOB 等级 60
- 扩展职业最高 BASE 等级 160, 最高 JOB 等级 50
- 全新职业 [杜兰族]
- 无登录限制，无限在线人数
- 正式进入 2018 年新 UI 界面时代！
- 签到系统、一键换装、成就系统等等

<details>
<summary>展开查看</summary>
<br/>

![](https://github.com/lyy289065406/ro-single-server/blob/master/img/03.png)
![](https://github.com/lyy289065406/ro-single-server/blob/master/img/04.png)
![](https://github.com/lyy289065406/ro-single-server/blob/master/img/05.png)
![](https://github.com/lyy289065406/ro-single-server/blob/master/img/06.png)
![](https://github.com/lyy289065406/ro-single-server/blob/master/img/07.png)

</details>




## GM 使用教程

<details>
<summary>展开查看</summary>
<br/>

group_id设置为99即可，GM的权限查看在D:\99MaxEathena\conf\groups.conf内


游戏内已设置默认的 GM 账号密码 admin admin , 默认 GM 外观.
设置自己MySQL的帐号与密码，默认为root root,

游戏账号无需网页注册，进入登陆画面，输入账号_M或_F，即可自动注册
程序自带GM账号:admin 密码:admin 中的人物 最后防线



3. [问题]如何注册游戏账号
可使用注册页 http://127.0.0.1:8096/ro , 也可使用 M/F 注册, 百度可查(关键词: RO的MF注册)

![](https://github.com/lyy289065406/ro-single-server/blob/master/img/08.png)

</details>



## FAQ

<details>
<summary>展开查看</summary>
<br/>

### 0x01 搭建服务端会占用哪些端口？

RO 服务端启动后，会开启 5 个服务：

- 角色服务器 `char-server.exe`： 占用端口 6121
- 登陆服务器 `login-server.exe`： 占用端口 6900
- 地图服务器 `map-server.exe`： 占用端口 5121
- 存档数据库 `Mysql`： 占用端口 3306
- 配套Web站点 `Apache` ： 占用端口 8096 （较旧的版本占用的是 80 端口）

如果有时服务端启动失败，不妨检查一下这些端口是否被占用。

另外如果需要架设成联机服务器（或部署到 VMWare 等虚拟机），则至少对外开放 6900、 5121、 6121 这 3 个端口，客户端才能成功登陆。


### 0x02 怎样搭建联机服务器？

第一，我们在 99MaxEathena v8.7.0 端内添加联机IP：
★如果IP只是外网的，将 char_athena.conf 和 map_athena.conf 内
的 login_ip、char_ip、map_ip 全部修改为外网IP即可，一共有4处IP修改。

★如果IP分为内/外网的，那设置稍微复杂点，学会之后，其实也不难，呵呵...
除了以上4处IP修改以外，将 char_athena.conf、login_athena.conf 和 map_athena.conf 内
的 bind_ip 功能打开，并将其修改为内网IP即可，加上之前4处，一共有7处IP修改。

第二，我们在 99MaxRo_Patch_v3.0 补丁内添加联机IP：
将 clientinfo.xml 文件复制黏贴在客户端 Data 文件夹内，
修改其内容<address>127.0.0.1</address>为外网IP即可。

clientinfo.xml
```
<?xml version="1.0" encoding="gbk3212" ?>
<clientinfo>

        <servicetype>china</servicetype>
        <servertype>primary</servertype>
        <extendedslot>2</extendedslot>

        <connection>
                <display>单机测试 大陆 中国电信/网通</display>
                <desc></desc>
                <balloon></balloon>
                <address>127.0.0.1</address>
                <port>6900</port>
                <version>45</version>
                <langtype>3</langtype>
                <registrationweb>http://127.0.0.1/</registrationweb>
                <yellow>
                        <admin>2000000</admin>
                </yellow>
                <loading>
                        <image>loading00.jpg</image>
                        <image>loading01.jpg</image>
                        <image>loading02.jpg</image>
                        <image>loading03.jpg</image>
                        <image>loading04.jpg</image>
                        <image>loading05.jpg</image>
                        <image>loading06.jpg</image>
                        <image>loading07.jpg</image>
                        <image>loading08.jpg</image>
                </loading>
        </connection>

</clientinfo>
```





1. [问题]启动 map-server.exe 时出现 0xc000007b 的解决办法
http://www.99max.me/thread-21246-1-1.html

2. [问题]运行中出现计算机丢失 msvcr110.dll 和 vcruntime140.dll 的解决办法
http://www.99max.me/thread-26184-1-1.html



4. [问题]如何将新注册游戏账号设置GM权限
先安装以下地址的[工具]8, 再参考[教程]2, 数据库默认的账号密码为 root root

5. [问题]没有GM工具情况下, 如何正确使用GM指令
GM工具很早就不用了, 因为很长时间没更新, 新的GM指令已经不支持.
GM指令大全在服务端目录下的 help.txt 文件, 聊天栏内输入GM指令.
对自己使用 @ 符号, 比如 @cash 50000
对玩家使用 # 符号, 比如 #cash 玩家名字 50000

</details>



## 常用教程/工具

<details>
<summary>展开查看</summary>
<br/>

1.[教程]给新人如何使用 V8系 一键版 顺利进入游戏的教程
http://www.99max.me/thread-19115-1-1.html

2.[教程]99MaxEathena v8系 GM账号的设置
http://www.99max.me/thread-12928-1-1.html

3.[教程]99MaxEathena v8系 外网联机教程
http://www.99max.me/thread-16792-1-2.html

4.[原创]GM命令快捷菜单 可直接执行命令99Max专用版
http://www.99max.me/thread-14899-1-1.html

5.[工具]Yiko製造 - 99Max Eathena 文本数据编辑器 v1.1.3
http://www.99max.me/thread-18936-1-1.html

6.[教程]99MaxEathena v8系 添加自定义头饰教程
http://www.99max.me/thread-16795-1-1.html

7.[教程]99MaxEathena v8.10.0 版本 VIP系统 新增功能介绍
http://www.99max.me/thread-19005-1-1.html

8.[工具]Na.vicat110_mysql_cs 32位+64位+破解补丁
http://www.99max.me/thread-16484-1-1.html

9.[工具]给大家一个专业的文本编辑器 Notepad++ 6.6.9
http://www.99max.me/thread-18709-1-2.html

10.[分享]还有更多的教程及工具，请大家自行去查阅吧
http://www.99max.me/forum-18-1.html

</details>



## 【附】 目录 & 文件功能说明

- GM指令大全 - help.txt
- 角色指令大全 - charhelp.txt
- 修改经验倍率 - exp.conf
- 修改掉落倍率 - drops.conf
- 修改游戏名称 - char_athena.conf
- 修改物品属性 - item_db.txt
- 修改魔物属性 - mob_db.txt
- 修改在线商城 - item_cash_db.txt
- 修改交易限制 - item_trade.txt
- 修改宠物属性 - pet_db.txt

<details>
<summary>展开查看</summary>
<br/>

```
ro-single-server
|-- 01-启动架设环境.bat
|-- 02-启动RO服务端.bat
|-- 03-关闭假设环境.bat
|-- 04-重置试用时间.bat
|-- charserv.bat
|-- char-server.exe
|-- history
|-- img
|-- libmysql.dll
|-- login-server.exe
|-- logserv.bat
|-- mapserv.bat
|-- map-server.exe
|-- msvcr110.dll
|-- npc
|-- pcre8.dll
|-- zlib.dll
|-- serv.bat
|-- sql-files
|-- vcruntime140.dll
|-- conf
|   |-- atcommand_athena.conf
|   |-- battle
|   |   |-- battle.conf
|   |   |-- battleground.conf
|   |   |-- client.conf
|   |   |-- drops.conf
|   |   |-- exp.conf
|   |   |-- feature.conf
|   |   |-- gm.conf
|   |   |-- guild.conf
|   |   |-- homunc.conf
|   |   |-- items.conf
|   |   |-- misc.conf
|   |   |-- monster.conf
|   |   |-- party.conf
|   |   |-- pet.conf
|   |   |-- player.conf
|   |   |-- skill.conf
|   |   └-- status.conf
|   |-- battle_athena.conf
|   |-- channels.conf
|   |-- char_athena.conf
|   |-- charhelp.txt
|   |-- charhelp.txt.dump
|   |-- grf-files.txt
|   |-- groups.conf
|   |-- help.txt
|   |-- import
|   |   |-- battle_conf.txt
|   |   |-- char_conf.txt
|   |   |-- inter_conf.txt
|   |   |-- inter_server.yml
|   |   |-- log_conf.txt
|   |   |-- login_conf.txt
|   |   |-- map_conf.txt
|   |   |-- packet_conf.txt
|   |   └-- script_conf.txt
|   |-- inter_athena.conf
|   |-- inter_athena.conf.dump
|   |-- inter_server.yml
|   |-- log_athena.conf
|   |-- login_athena.conf
|   |-- map_athena.conf
|   |-- maps_athena.conf
|   |-- motd.txt
|   |-- msg_conf
|   |   |-- char_msg.conf
|   |   |-- import
|   |   |   |-- map_msg_chn_conf.txt
|   |   |   |-- map_msg_eng_conf.txt
|   |   |   |-- map_msg_frn_conf.txt
|   |   |   |-- map_msg_grm_conf.txt
|   |   |   |-- map_msg_idn_conf.txt
|   |   |   |-- map_msg_mal_conf.txt
|   |   |   |-- map_msg_por_conf.txt
|   |   |   |-- map_msg_rus_conf.txt
|   |   |   |-- map_msg_spn_conf.txt
|   |   |   └-- map_msg_tha_conf.txt
|   |   |-- login_msg.conf
|   |   |-- map_msg_chn.conf
|   |   |-- map_msg.conf
|   |   |-- map_msg_frn.conf
|   |   |-- map_msg_grm.conf
|   |   |-- map_msg_idn.conf
|   |   |-- map_msg_mal.conf
|   |   |-- map_msg_por.conf
|   |   |-- map_msg_rus.conf
|   |   |-- map_msg_spn.conf
|   |   |-- map_msg_tha.conf
|   |   └-- translation.conf
|   |-- packet_athena.conf
|   |-- readme.md
|   |-- script_athena.conf
|   |-- subnet_athena.conf
|   └-- valkyrie_sample.cfg
|-- db
|   |-- abra_db.txt
|   |-- castle_db.txt
|   |-- const.txt
|   |-- create_arrow_db.txt
|   |-- elemental_db.txt
|   |-- elemental_skill_db.txt
|   |-- GeoIP.dat
|   |-- guild_skill_tree.txt
|   |-- homun_skill_tree.txt
|   |-- import
|   |   |-- abra_db.txt
|   |   |-- achievement_db.yml
|   |   |-- attendance.yml
|   |   |-- attr_fix.txt
|   |   |-- castle_db.txt
|   |   |-- const.txt
|   |   |-- create_arrow_db.txt
|   |   |-- elemental_db.txt
|   |   |-- elemental_skill_db.txt
|   |   |-- exp_guild.txt
|   |   |-- exp_homun.txt
|   |   |-- guild_skill_tree.txt
|   |   |-- homunculus_db.txt
|   |   |-- homun_skill_tree.txt
|   |   |-- instance_db.txt
|   |   |-- item_avail.txt
|   |   |-- item_bluebox.txt
|   |   |-- item_buyingstore.txt
|   |   |-- item_cardalbum.txt
|   |   |-- item_cash_db.txt
|   |   |-- item_combo_db.txt
|   |   |-- item_db.txt
|   |   |-- item_delay.txt
|   |   |-- item_findingore.txt
|   |   |-- item_flag.txt
|   |   |-- item_giftbox.txt
|   |   |-- item_group_db.txt
|   |   |-- item_misc.txt
|   |   |-- item_noequip.txt
|   |   |-- item_nouse.txt
|   |   |-- item_package.txt
|   |   |-- item_randomopt_db.txt
|   |   |-- item_randomopt_group.txt
|   |   |-- item_stack.txt
|   |   |-- item_trade.txt
|   |   |-- item_violetbox.txt
|   |   |-- job_basehpsp_db.txt
|   |   |-- job_db1.txt
|   |   |-- job_db2.txt
|   |   |-- job_exp.txt
|   |   |-- job_noenter_map.txt
|   |   |-- job_param_db.txt
|   |   |-- level_penalty.txt
|   |   |-- magicmushroom_db.txt
|   |   |-- map_cache.dat
|   |   |-- map_index.txt
|   |   |-- mercenary_db.txt
|   |   |-- mercenary_skill_db.txt
|   |   |-- mob_avail.txt
|   |   |-- mob_boss.txt
|   |   |-- mob_branch.txt
|   |   |-- mob_chat_db.txt
|   |   |-- mob_classchange.txt
|   |   |-- mob_db.txt
|   |   |-- mob_drop.txt
|   |   |-- mob_item_ratio.txt
|   |   |-- mob_mission.txt
|   |   |-- mob_poring.txt
|   |   |-- mob_pouch.txt
|   |   |-- mob_race2_db.txt
|   |   |-- mob_random_db.txt
|   |   |-- mob_skill_db.txt
|   |   |-- pet_db.txt
|   |   |-- produce_db.txt
|   |   |-- quest_db.txt
|   |   |-- refine_db.yml
|   |   |-- size_fix.txt
|   |   |-- skill_cast_db.txt
|   |   |-- skill_castnodex_db.txt
|   |   |-- skill_changematerial_db.txt
|   |   |-- skill_copyable_db.txt
|   |   |-- skill_damage_db.txt
|   |   |-- skill_db.txt
|   |   |-- skill_improvise_db.txt
|   |   |-- skill_nocast_db.txt
|   |   |-- skill_nonearnpc_db.txt
|   |   |-- skill_require_db.txt
|   |   |-- skill_tree.txt
|   |   |-- skill_unit_db.txt
|   |   |-- spellbook_db.txt
|   |   |-- statpoint.txt
|   |   └-- status_disabled.txt
|   |-- item_auto_change.txt
|   |-- item_avail.txt
|   |-- item_drop_announce.txt
|   |-- item_findingore.txt
|   |-- item_nouse.txt
|   |-- item_vending.txt
|   |-- job_db2.txt
|   |-- magicmushroom_db.txt
|   |-- map_index.txt
|   |-- mercenary_db.txt
|   |-- mercenary_skill_db.txt
|   |-- mob_avail.txt
|   |-- mob_chat_db.txt
|   |-- mob_classchange.txt
|   |-- mob_item_ratio.txt
|   |-- mob_mission.txt
|   |-- mob_pouch.txt
|   |-- re
|   |   |-- achievement_db.yml
|   |   |-- attendance.yml
|   |   |-- attr_fix.txt
|   |   |-- exp_guild.txt
|   |   |-- exp_homun.txt
|   |   |-- homunculus_db.txt
|   |   |-- instance_db.txt
|   |   |-- item_bluebox.txt
|   |   |-- item_buyingstore.txt
|   |   |-- item_cardalbum.txt
|   |   |-- item_cash_db.txt
|   |   |-- item_combo_db.txt
|   |   |-- item_db.txt
|   |   |-- item_delay.txt
|   |   |-- item_flag.txt
|   |   |-- item_giftbox.txt
|   |   |-- item_group_db.txt
|   |   |-- item_misc.txt
|   |   |-- item_noequip.txt
|   |   |-- item_package.txt
|   |   |-- item_randomopt_db.txt
|   |   |-- item_randomopt_group.txt
|   |   |-- item_stack.txt
|   |   |-- item_trade.txt
|   |   |-- item_violetbox.txt
|   |   |-- job_basehpsp_db.txt
|   |   |-- job_db1.txt
|   |   |-- job_exp.txt
|   |   |-- job_noenter_map.txt
|   |   |-- job_param_db.txt
|   |   |-- level_penalty.txt
|   |   |-- map_cache.dat
|   |   |-- mob_boss.txt
|   |   |-- mob_branch.txt
|   |   |-- mob_db.txt
|   |   |-- mob_drop.txt
|   |   |-- mob_poring.txt
|   |   |-- mob_race2_db.txt
|   |   |-- mob_random_db.txt
|   |   |-- mob_skill_db.txt
|   |   |-- pet_db.txt
|   |   |-- produce_db.txt
|   |   |-- quest_db.txt
|   |   |-- refine_db.yml
|   |   |-- skill_cast_db.txt
|   |   |-- skill_castnodex_db.txt
|   |   |-- skill_db.txt
|   |   |-- skill_nocast_db.txt
|   |   |-- skill_require_db.txt
|   |   |-- skill_tree.txt
|   |   |-- skill_unit_db.txt
|   |   └-- statpoint.txt
|   |-- readme.md
|   |-- size_fix.txt
|   |-- skill_changematerial_db.txt
|   |-- skill_copyable_db.txt
|   |-- skill_damage_db.txt
|   |-- skill_improvise_db.txt
|   |-- skill_nonearnpc_db.txt
|   |-- spellbook_db.txt
|   └-- status_disabled.txt
|-- doc
|   |-- 99MaxEa_atcommands.txt
|   |-- 99MaxEa_bonus.txt
|   |-- 99MaxEa_events.txt
|   |-- 99MaxEa_mapflags.txt
|   |-- 99MaxEa_script_commands.txt
|   |-- 99MaxEa.txt
|   |-- achievements.txt
|   |-- atcommands.txt
|   |-- ea_job_system.txt
|   |-- effect_list.txt
|   |-- item_bonus.txt
|   |-- item_db.txt
|   |-- item_group.txt
|   |-- map_cache.txt
|   |-- mapflags.txt
|   |-- md5_hashcheck.txt
|   |-- mob_db_mode_list.txt
|   |-- mob_db.txt
|   |-- mob_skill_db_powerskill.txt
|   |-- model
|   |   |-- Model_Relation.mwb
|   |   |-- Model_Relation.png
|   |   └-- rathena.vpp
|   |-- packet_client.txt
|   |-- packet_interserv.txt
|   |-- packet_struct_notation.txt
|   |-- permissions.txt
|   |-- quest_variables.txt
|   |-- sample
|   |   |-- bank_test.txt
|   |   |-- basejob_baseclass_upper.txt
|   |   |-- checkoption.txt
|   |   |-- delitem2.txt
|   |   |-- getequipcardid.txt
|   |   |-- getequipid.txt
|   |   |-- getiteminfo.txt
|   |   |-- getmonsterinfo.txt
|   |   |-- gstorage_test.txt
|   |   |-- inarray.txt
|   |   |-- instancing.txt
|   |   |-- localized_npc.txt
|   |   |-- navigate.txt
|   |   |-- npc_dynamic_shop.txt
|   |   |-- npc_extend_shop.txt
|   |   |-- npc_live_dialogues.txt
|   |   |-- npc_shop_test.txt
|   |   |-- npc_test_array.txt
|   |   |-- npc_test_chat.txt
|   |   |-- npc_test_duplicate.txt
|   |   |-- npc_test_func.txt
|   |   |-- npc_test_getunits.txt
|   |   |-- npc_test_npctimer2.txt
|   |   |-- npc_test_npctimer.txt
|   |   |-- npc_test_pcre.txt
|   |   |-- npc_test_quest.txt
|   |   |-- npc_test_setitemx.txt
|   |   |-- npc_test_setmapflag.txt
|   |   |-- npc_test_skill.txt
|   |   |-- npc_test_time.txt
|   |   └-- randomopt.txt
|   |-- script_commands.txt
|   |-- skill_require_db.txt
|   |-- source_doc.txt
|   |-- status_change.txt
|   |-- whisper_sys.txt
|   └-- woe_time_explanation.txt
|-- gm-cmd
|   |-- GM命令快捷菜单.exe
|   |-- roa.ini
|   └-- ro.ini
|-- ROEmulator
|   |-- desktop.ini
|   |-- home
|   |   |-- admin
|   |   |   └-- program
|   |   |       |-- pskill.exe
|   |   |       |-- unidelay.exe
|   |   |       └-- uniserv.exe
|   |   └-- desktop.ini
|   |-- tmp
|   |-- usr
|   |   └-- local
|   |       |-- apache2
|   |       |-- mysql
|   |       └-- php
|   └-- www
└-- README.md

994 directories, 10054 files
```

</details>



------

## 版权声明

　[![Copyright (C) 2016-2020 By EXP](https://img.shields.io/badge/Copyright%20(C)-2016~2019%20By%20EXP-blue.svg)](http://exp-blog.com)　[![License: GPL v3](https://img.shields.io/badge/License-GPL%20v3-blue.svg)](https://www.gnu.org/licenses/gpl-3.0)
  

- Site: [http://exp-blog.com](http://exp-blog.com) 
- Mail: <a href="mailto:289065406@qq.com?subject=[EXP's Github]%20Your%20Question%20（请写下您的疑问）&amp;body=What%20can%20I%20help%20you?%20（需要我提供什么帮助吗？）">289065406@qq.com</a>


------
