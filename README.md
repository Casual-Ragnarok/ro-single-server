# RO-Single-Server

> 仙境RO传说-单机版-服务端（支持联网开服）

------

## 关于此仓库（非开发者可不看）

<details>
<summary>展开查看</summary>

- master: 主分支，当前最新版服务端的原始档
- tag: 基线，历史版本服务端的原始档
- staging: 测试环境分支，用于本地调试服务器修改的内容，其他人可以删掉
- produce: 生产环境分支，含我的服务器的远程存档，其他人可以删掉

> 配套客户端仓库：[https://github.com/lyy289065406/ro-single-client](https://github.com/lyy289065406/ro-single-client)


</details>


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

> 注：
<br/>　注册表地址 `HKCU` 是 `HKEY_CURRENT_USER` 的缩写
<br/>　由于没有从根本上破解，每隔 1 小时服务端会弹出一次激活框，可以不管，也可以点击【继续试用】，不影响游戏

![](https://github.com/lyy289065406/ro-single-server/blob/master/img/00.png)


</details>


## 运行环境

　![](https://img.shields.io/badge/Platform-Windows%207%2f8%2f10%20x64-brightgreen.svg) ![](https://img.shields.io/badge/Platform-Windows%20Server%202003%2f2012%20x64-brightgreen.svg) 



## 版本说明 & 下载

- EA 服务端模拟器：v8.19.0 【[Github:免费已破解](https://github.com/lyy289065406/ro-single-server)】【[百度网盘(zx39):VMWare永久激活](https://pan.baidu.com/s/1M-W-bra6h16Bq7vqPI_Rng)】~【[99Max:收费](http://www.99max.me/thread-12926-1-1.html)】~
- 服务端环境修复包： DirectXRepair-v3.9 【[百度网盘(vs1m)](https://pan.baidu.com/s/1zoBXTi5rp7Yj1bhzMzo-oQ)】 (如果可以直接运行就不用装了)
- 韩服客户端（三转复兴后）：Ragnarok_KRO_20190306_Lite 【[百度网盘(dgui)](https://pan.baidu.com/s/1vrh-9wE29tfZvDiS10wkxw)】【[韩国官网](http://ro.gnjoy.com/pds/down/)】~【[99Max:收费](http://www.99max.me/thread-485-1-1.html)】~
- 客户端补丁（登陆器）：v4.3 【[Github](https://github.com/lyy289065406/ro-single-client)】【[百度网盘(iav5)](https://pan.baidu.com/s/1F3XLwqDDwebvUNIYIKPlXQ&shfl=sharepset)】~【[99Max:收费](http://www.99max.me/thread-3674-1-1.html)】~
 
> 备注：
<br/>　服务端脚本更新记录（游戏内公示）: [view](https://github.com/lyy289065406/ro-single-server/blob/master/npc/re/%E5%8A%9F%E8%83%BD%E8%84%9A%E6%9C%AC/update.txt)
<br/>　服务端历史版本更新内容: [view](https://github.com/lyy289065406/ro-single-server/tree/master/history/version-server.md)
<br/>　客户端历史版本更新内容: [view](https://github.com/lyy289065406/ro-single-server/tree/master/history/version-client.md)


## 版本特色

- 三转职业最高 BASE 等级 175, 最高 JOB 等级 70
- 扩展职业最高 BASE 等级 160, 最高 JOB 等级 50
- 全新种族 [杜兰族] （即喵族，原本 RO 只能控制人族）
- 无登录限制，无限在线人数
- 正式进入 2018 年新 UI 界面时代！
- 签到系统、一键换装、成就系统等等

<details>
<summary>展开查看更多</summary>
<br/>

![](https://github.com/lyy289065406/ro-single-server/blob/master/img/03.png)
![](https://github.com/lyy289065406/ro-single-server/blob/master/img/04.png)
![](https://github.com/lyy289065406/ro-single-server/blob/master/img/05.png)
![](https://github.com/lyy289065406/ro-single-server/blob/master/img/06.png)
![](https://github.com/lyy289065406/ro-single-server/blob/master/img/07.png)
![](https://github.com/lyy289065406/ro-single-server/blob/master/img/09.png)
![](https://github.com/lyy289065406/ro-single-server/blob/master/img/10.png)

</details>


## 安装教程

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

<details>
<summary>展开查看更多</summary>
<br/>

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
- 覆盖客户端补丁到韩服客户端根目录
- 双击运行 `RO仙境传说_Setup_Plus.exe` 修改配置
- 双击运行 `RO仙境传说_v4.3_Data.exe` 即可进入游戏


</details>



## 内置站点

- Discuz! X3.2 系统（含门户/论坛/空间）： [http://127.0.0.1:8096](http://127.0.0.1:8096)
- Discuz! 管理员后台（账密 admin/admin）： [http://127.0.0.1:8096/admin.php](http://127.0.0.1:8096/admin.php)
- 玩家注册页： [http://127.0.0.1:8096/ro](http://127.0.0.1:8096/ro) （同时支持 M/F 注册系统）

<details>
<summary>展开查看更多</summary>
<br/>

> 备： RO 的 M/F 注册系统
<br/>　M 表示 男
<br/>　F 表示 女
<br/>　玩家通过客户端正常启动游戏
<br/>　第一次登陆时，在帐号栏里填写 abc_M 或者 abc_F，其中 abc 就是要注册的帐号，密码栏填写要注册的密码
<br/>　第二次登陆时，把后面的 \_M/\_F 去掉，即可正常登陆


![](https://github.com/lyy289065406/ro-single-server/blob/master/img/13.png)
![](https://github.com/lyy289065406/ro-single-server/blob/master/img/12.png)

</details>


## GM 使用教程

### 默认 GM 账号

- 账号： admin
- 密码： admin
- 名称： GM01
- 外观： GM 默认外观

<details>
<summary>展开查看更多</summary>

### 服务器数据库

- 类型： mysql
- IP： 127.0.0.1
- 端口： 3306
- 账号： root
- 密码： root
- 库名： ragnarok
- 账号管理表： login
- 角色管理表： char

> 注：
<br/>　mysql 的 root 账号只允许本地连接
<br/>　若需要通过联网访问，需增加新的 mysql 账号并授权（具体方法自行搜索）

### 添加 GM 账号

- 注册普通账号
- 使用任意工具（如 Navicat【[百度网盘(5z29)](https://pan.baidu.com/s/19AfofEPg37YoootVpgMeDg)】）登录数据库
- 打开账号管理表 login ，找到刚刚注册的普通账号
- 修改 group_id 列为 99 即可赋予其 GM 权限

> 注：group_id 表示 GM 等级，各个等级权限详见 [conf/groups.conf](https://github.com/lyy289065406/ro-single-server/blob/master/conf/groups.conf) 文件

![](https://github.com/lyy289065406/ro-single-server/blob/master/img/14.png)


### GM 常用配置

- GM 指令辅助工具： [`tools/GM命令菜单/GM命令快捷菜单.exe`](https://github.com/lyy289065406/ro-single-server/blob/staging/tools/GM%E5%91%BD%E4%BB%A4%E8%8F%9C%E5%8D%95/GM%E5%91%BD%E4%BB%A4%E5%BF%AB%E6%8D%B7%E8%8F%9C%E5%8D%95.exe)
- GM 指令大全： [`conf/help.txt`](https://github.com/lyy289065406/ro-single-server/blob/master/conf/help.txt)
- 角色指令大全：  [`conf/charhelp.txt`](https://github.com/lyy289065406/ro-single-server/blob/master/conf/charhelp.txt)
- 修改服务器参数：  [`conf/char_athena.conf`](https://github.com/lyy289065406/ro-single-server/blob/master/conf/char_athena.conf)
- 修改经验倍率：  [`conf/battle/exp.conf`](https://github.com/lyy289065406/ro-single-server/blob/master/conf/battle/exp.conf)
- 修改掉落倍率：  [`conf/battle/drops.conf`](https://github.com/lyy289065406/ro-single-server/blob/master/conf/battle/drops.conf)
- 修改物品属性：  [`db/re/item_db.txt`](https://github.com/lyy289065406/ro-single-server/blob/master/db/re/item_db.txt)
- 修改魔物属性：  [`db/re/mob_db.txt`](https://github.com/lyy289065406/ro-single-server/blob/master/db/re/mob_db.txt)
- 修改在线商城：  [`db/re/item_cash_db.txt`](https://github.com/lyy289065406/ro-single-server/blob/master/db/re/item_cash_db.txt)
- 修改交易限制：  [`db/re/item_trade.txt`](https://github.com/lyy289065406/ro-single-server/blob/master/db/re/item_trade.txt)
- 修改宠物属性：  [`db/re/pet_db.txt`](https://github.com/lyy289065406/ro-single-server/blob/master/db/re/pet_db.txt)

> 注：
<br/>　"GM指令辅助工具" 因为很长时间没更新, 新的 GM 指令已经不支持了，建议直接查看 [`conf/help.txt`](https://github.com/lyy289065406/ro-single-server/blob/master/conf/help.txt)
<br/>　若对自己使用 GM 指令，则使用 `@` 符号, 如 `@cash 50000`
<br/>　若对玩家使用 GM 指令，则使用 `#` 符号, 如 `#cash 玩家名字 50000`
<br/>　GM 指令大全在服务端目录下的 help.txt 文件, 聊天栏内输入

![](https://github.com/lyy289065406/ro-single-server/blob/master/img/08.png)
![](https://github.com/lyy289065406/ro-single-server/blob/master/img/11.png)


</details>


## FAQ

<details>
<summary>展开查看</summary>

### 0x01 运行服务端报错：计算机丢失 `msvcr110.dll` 和 `vcruntime140.dll`

.NET 版本过旧或缺失必要的 VC++ 运行库导致，由于服务端根目录下已经有这两个文件，一般不会出现这个问题。

可以尝试把服务端根目录下的 `msvcr110.dll` 和 `vcruntime140.dll` 文件复制到 `C:\Windows\System32` 目录。

若还是不行则需要修复 .NET【[百度网盘(m2e4)](https://pan.baidu.com/s/1Sics3B5rGCUZl-47Tv5n7A)】


------
### 0x02 运行服务端报错： `0xc000007b`

缺失 DirectX 或版本过旧导致，

修复包： DirectXRepair-v3.9 【[百度网盘(vs1m)](https://pan.baidu.com/s/1zoBXTi5rp7Yj1bhzMzo-oQ)】 (如果可以直接运行就不用装了)


------
### 0x03 服务端每小时提示弹一次激活框

由于没有从根本上破解（脱壳），确实每隔 1 小时服务端会弹出一次激活框。

其实可以不管，也可以点击【继续试用】，顺便执行脚本 `04-重置试用时间.bat`，不影响游戏。

> 注：找时间我会把整个激活逻辑删掉


### 0x04 游戏中存在乱码

例如【导航】、【Tips Box】等。

翻译组的锅，没有完全翻译（客户端的 `data_ch.grf` 内置了语言包，配置文件为 `data.ini`），不过并不影响游戏。

所谓的乱码其实是没有翻译的韩文，主要因为我们在中文系统上用韩服客户端就会出现这种情况。

------
### 0x05 搭建服务端会占用哪些端口？

RO 服务端启动后，会开启 5 个服务：

- 角色服务器 `char-server.exe`： 占用端口 6121
- 登陆服务器 `login-server.exe`： 占用端口 6900
- 地图服务器 `map-server.exe`： 占用端口 5121
- 存档数据库 `Mysql`： 占用端口 3306
- 配套Web站点 `Apache` ： 占用端口 8096 （较旧的版本占用的是 80 端口）

如果有时服务端启动失败，不妨检查一下这些端口是否被占用。

另外如果需要架设成联机服务器（或部署到 VMWare 等虚拟机），则至少对外开放 6900、 5121、 6121 这 3 个端口，客户端才能成功登陆。

> 注：若搭建联机服务器，需确保防火墙策略已放行上述的 5 个端口

------
### 0x06 怎样搭建联机服务器？

假设服务器 IP 如下：

- 本地回环地址： 127.0.0.1
- 局域网地址： 192.168.1.2
- 公网地址： 9.8.7.6

首先需要知道 <b>服务端</b> 和 <b>客户端</b> 在哪里配置 IP 的。

在 <b>单机</b> 情况下，服务端配置是这样的：

- 登录服务器配置文件： [conf/login_athena.conf](https://github.com/lyy289065406/ro-single-server/blob/master/conf/login_athena.conf)
<br/>　○ bind_ip: 127.0.0.1 （默认被注释）
- 角色服务器配置文件： [conf/char_athena.conf](https://github.com/lyy289065406/ro-single-server/blob/master/conf/char_athena.conf)
<br/>　○ char_ip: 127.0.0.1
<br/>　○ login_ip: 127.0.0.1
<br/>　○ bind_ip: 127.0.0.1 （默认被注释）
- 地图服务器配置文件： [conf/map_athena.conf](https://github.com/lyy289065406/ro-single-server/blob/master/conf/map_athena.conf)
<br/>　○ map_ip: 127.0.0.1
<br/>　○ char_ip: 127.0.0.1
<br/>　○ bind_ip: 127.0.0.1 （默认被注释）

------

根据联机所架设的网络不同（共 3 种架设方式），配置方法也不同。

若<b>仅需 局域网 联机</b>，服务端配置修改为（共修改 4 处）：

- 登录服务器配置文件： [conf/login_athena.conf](https://github.com/lyy289065406/ro-single-server/blob/master/conf/login_athena.conf)
<br/>　○ bind_ip: 127.0.0.1 （保持被注释）
- 角色服务器配置文件： [conf/char_athena.conf](https://github.com/lyy289065406/ro-single-server/blob/master/conf/char_athena.conf)
<br/>　○ char_ip: 192.168.1.2
<br/>　○ login_ip: 192.168.1.2
<br/>　○ bind_ip: 127.0.0.1 （保持被注释）
- 地图服务器配置文件： [conf/map_athena.conf](https://github.com/lyy289065406/ro-single-server/blob/master/conf/map_athena.conf)
<br/>　○ map_ip: 192.168.1.2
<br/>　○ char_ip: 192.168.1.2
<br/>　○ bind_ip: 127.0.0.1 （保持被注释）

> 注：局域网下，子网掩码配置文件 [conf/subnet_athena.conf](https://github.com/lyy289065406/ro-single-server/blob/master/conf/login_athena.conf) 可能会影响客户端登录。该文件通过子网掩码计算客户端所配置的服务端 IP ，只要结果和服务器的真实局域网 IP 一致就允许服务端登录（换言之允许客户端配置服务端的 IP 段）。

------

若<b>仅需 公网 联机</b>，服务端配置修改为（共修改 4 处）：

- 登录服务器配置文件： [conf/login_athena.conf](https://github.com/lyy289065406/ro-single-server/blob/master/conf/login_athena.conf)
<br/>　○ bind_ip: 127.0.0.1 （保持被注释）
- 角色服务器配置文件： [conf/char_athena.conf](https://github.com/lyy289065406/ro-single-server/blob/master/conf/char_athena.conf)
<br/>　○ char_ip: 9.8.7.6
<br/>　○ login_ip: 9.8.7.6
<br/>　○ bind_ip: 127.0.0.1 （保持被注释）
- 地图服务器配置文件： [conf/map_athena.conf](https://github.com/lyy289065406/ro-single-server/blob/master/conf/map_athena.conf)
<br/>　○ map_ip: 9.8.7.6
<br/>　○ char_ip: 9.8.7.6
<br/>　○ bind_ip: 127.0.0.1 （保持被注释）

------

若<b>同时需 局域网+公网 联机</b>，服务端配置修改为（共修改 7 处）：

- 登录服务器配置文件： [conf/login_athena.conf](https://github.com/lyy289065406/ro-single-server/blob/master/conf/login_athena.conf)
<br/>　○ bind_ip: 192.168.1.2
- 角色服务器配置文件： [conf/char_athena.conf](https://github.com/lyy289065406/ro-single-server/blob/master/conf/char_athena.conf)
<br/>　○ char_ip: 9.8.7.6
<br/>　○ login_ip: 9.8.7.6
<br/>　○ bind_ip: 192.168.1.2
- 地图服务器配置文件： [conf/map_athena.conf](https://github.com/lyy289065406/ro-single-server/blob/master/conf/map_athena.conf)
<br/>　○ map_ip: 9.8.7.6
<br/>　○ char_ip: 9.8.7.6
<br/>　○ bind_ip: 192.168.1.2


------

而对于 <b>客户端</b> 配置则简单得多。

客户端默认情况下是不存在 IP 配置文件的，在安装登录器补丁后，需要手动添加一个文件 `data/sclientinfo.xml`。

根据客户端要走 <b>局域网</b> 还是 <b>公网</b> 接入服务端，对应修改 `<address>` 的值即可。

完整的 `data/sclientinfo.xml` 文件内容如下:
```
<?xml version="1.0" encoding="gbk3212" ?>
<clientinfo>

        <servicetype>china</servicetype>
        <servertype>primary</servertype>
        <extendedslot>2</extendedslot>

        <connection>
                <display>单机服务器 电信/网通双线</display>
                <desc></desc>
                <balloon></balloon>
                <address>127.0.0.1</address>
                <port>6900</port>
                <version>45</version>
                <langtype>3</langtype>
                <registrationweb>http://127.0.0.1/ro</registrationweb>
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



</details>



### 0x99 更多资料

> [传送门](https://github.com/lyy289065406/ro-single-server/tree/master/tools/RO%E8%B5%84%E6%96%99)



## 【附】 目录 & 文件功能说明


<details>
<summary>展开查看</summary>
<br/>

> 注：
<br/>　RO 服务端除了使用 mysql 作为动态数据库之外，还会使用 txt 和 conf 作为静态数据库
<br/>　历史原因，大部分文件的编码都是以 GBK 为主，修改文件时不要随便改变编码，避免引起不必要的异常
<br/>　`※` 表示常用配置

```
ro-single-server
|-- 01-启动架设环境.bat  ......................  [启动 mysql 存档数据库和 Apache 门户网站]
|-- 02-启动RO服务端.bat  ......................  [启动 RO 服务端（含登录、角色、地图服务器）]
|-- 03-关闭架设环境.bat  ......................  [停止 mysql 存档数据库和 Apache 门户网站]
|-- 04-重置试用时间.bat  ......................  [服务端试用期破解脚本]
|-- serv.bat  ...............................  [调用脚本 logserv.bat、 charserv.bat、 mapserv.bat 的前置脚本]
|-- logserv.bat  ............................  [启用登录服务器 login-server.exe 的脚本]
|-- login-server.exe  .......................  [登录服务器]
|-- charserv.bat  ...........................  [启动角色服务器 char-server.exe 的脚本]
|-- char-server.exe  ........................  [角色服务器]
|-- mapserv.bat  ............................  [启动地图服务器 login-server.exe 的脚本]
|-- map-server.exe  .........................  [地图服务器]
|-- libmysql.dll  ...........................  [连接 mysql 模块的库文件]
|-- pcre8.dll  ..............................  [perl 正则表达式模块的库文件]
|-- zlib.dll  ...............................  [解压模块的库文件]
|-- msvcr110.dll  ...........................  [.NET 库文件之一]
|-- vcruntime140.dll  .......................  [.NET 库文件之一]
|-- sql-files  ..............................  [mysql 建库脚本]
|-- history  ................................  [历史版本的更新内容说明]
|-- img  ....................................  [README 文档插图]
|-- tools  ..................................  [辅助工具（用于旧版，基本已失效）]
|   |-- GM命令菜单  ..........................  [GM 常用命令查询菜单）]
|   └-- GRF解压工具  .........................  [用于解压客户端 *.grf 文件（密码已失效）]
|-- ROEmulator  .............................  [RO 服务端模拟器工作目录（启动架设环境后会映射到一个空闲盘符）]
|   |-- home  ...............................  [admin HOME 目录（内置启动 mysql 和 Apache 的组件）]
|   |-- tmp  ................................  [缓存模拟器内各个应用临时文件的临时]
|   |-- usr
|   |   └-- local
|   |       |-- apache2  ....................  [Apache 模块，用于支持 Discuz! 门户网站]
|   |       |-- php  ........................  [php 模块，用于支持 Discuz! 门户网站]
|   |       └-- mysql  ......................  [mysql 存档数据库]
|   └-- www  ................................  [Discuz! 门户网站前端源码]
|-- conf  ...................................  [RO 服务端配置目录（在 re 文件夹内表示"复兴后"）]
|   |-- help.txt  ...........................  [※ GM 命令配置文件]
|   |-- charhelp.txt  .......................  [※ 角色命令配置文件]
|   |-- atcommand_athena.conf ...............  [命令别名配置文件]
|   |-- char_athena.conf  ...................  [※ 角色服务器配置文件（可修改服务器参数）]
|   |-- login_athena.conf  ..................  [※ 登录服务器配置文件]
|   |-- map_athena.conf  ....................  [※ 地图服务器配置文件]
|   |-- maps_athena.conf  ...................  [地图名称数据库]
|   |-- groups.conf  ........................  [※ 各个玩家角色组的权限配置文件（GM 权限配置）]
|   |-- script_athena.conf ..................  [脚本配置文件]
|   |-- subnet_athena.conf ..................  [子网掩码配置文件（在局域网下架设服务器需关注）]
|   |-- packet_athena.conf ..................  [Socket 配置文件]
|   |-- log_athena.conf .....................  [日志配置文件]
|   |-- inter_athena.conf  ..................  [※ 数据库配置文件]
|   |-- inter_server.yml
|   |-- grf-files.txt  ......................  [GRF 文件默认位置]
|   |-- motd.txt
|   |-- valkyrie_sample.cfg
|   |-- channels.conf
|   |-- battle_athena.conf  .................  [通过 import 导入汇总了所有与战斗相关的配置文件]
|   |-- battle  .............................  [战斗相关配置]
|   |   |-- battle.conf  ....................  [※ 有关一般战斗的配置]
|   |   |-- battleground.conf  ..............  [战役/战场配置]
|   |   |-- client.conf .....................  [客户端效果的配置]
|   |   |-- drops.conf  .....................  [※ 物品掉落几率配置]
|   |   |-- exp.conf  .......................  [※ 经验倍率/经验处罚率、人物状态、人物最高等级的配置]
|   |   |-- feature.conf  ...................  [功能控制（开/关）配置]
|   |   |-- gm.conf  ........................  [GM 等级、GM 命令和相关防止恶意攻击的配置]
|   |   |-- guild.conf  .....................  [公会和 GVG 配置]
|   |   |-- homunc.conf  ....................  [人工生命体配置]
|   |   |-- items.conf  .....................  [物品效果和物品验证的配置]
|   |   |-- status.conf  ....................  [状态配置]
|   |   |-- monster.conf  ...................  [魔物配置]
|   |   |-- party.conf  .....................  [组队配置]
|   |   |-- pet.conf  .......................  [宠物配置]
|   |   |-- player.conf  ....................  [※ 人物效果配置]
|   |   |-- skill.conf  .....................  [技能配置]
|   |   └-- misc.conf  ......................  [环境配置（不属于上面分类里的设置，如 PVP、昼夜、禁言、日志等）]
|   |-- msg_conf  ...........................  [各种事件、地图、任务、系统等消息的配置]
|   |-- import  .............................  [自定义配置目录]
|   └-- README.md  ..........................  [RO 服务端配置目录说明]
|-- db  .....................................  [RO 服务端文本数据库（在 re 文件夹内表示"复兴后"）]
|   |-- abra_db.txt  ........................  [贤者随机技能发动数据库]
|   |-- castle_db.txt  ......................  [公会城堡数据库]
|   |-- const.txt  ..........................  [常量表]
|   |-- create_arrow_db.txt  ................  [制作箭技能数据库]
|   |-- elemental_db.txt  ...................  [元素精灵数据库]
|   |-- elemental_skill_db.txt  .............  [元素精灵技能数据库]
|   |-- GeoIP.dat  ..........................  [IP 地理位置数据库]
|   |-- guild_skill_tree.txt  ...............  [公会技能树数据库]
|   |-- homun_skill_tree.txt  ...............  [人工生命体技能树数据库]
|   |-- item_auto_change.txt  ...............  [自动转换武器属性的物品库]
|   |-- item_avail.txt  .....................  [※ 物品外观替换库（用于客户端因某些物品缺失外观导致的报错）]
|   |-- item_drop_announce.txt  .............  [物品掉落全服公告]
|   |-- item_findingore.txt  ................  [获得寻找的矿石数据库]
|   |-- item_nouse.txt  .....................  [物品使用限制数据库]
|   |-- item_vending.txt  ...................  [自动售货机物品库]
|   |-- job_db2.txt  ........................  [Job 升级奖励库]
|   |-- magicmushroom_db.txt  ...............  [狂笑之毒数据库]
|   |-- map_index.txt  ......................  [地图索引库]
|   |-- mercenary_db.txt  ...................  [雇佣兵资料库]
|   |-- mercenary_skill_db.txt  .............  [雇佣兵技能库]
|   |-- mob_avail.txt  ......................  [魔物外观替换库]
|   |-- mob_chat_db.txt  ....................  [魔物对话数据库]
|   |-- mob_classchange.txt  ................  [魔物召唤数据库]
|   |-- mob_item_ratio.txt  .................  [魔物进阶掉率设置]
|   |-- mob_mission.txt  ....................  [跆拳道任务召唤怪物数据库]
|   |-- mob_pouch.txt  ......................  [红色炸弹能召唤出来的魔物数据库]
|   |-- status_disabled.txt  ................  [状态改变限制数据库]
|   |-- size_fix.txt  .......................  [体型大小对武器伤害的修正数据库]
|   |-- skill_changematerial_db.txt  ........  [基因技能: "素材变化" 转换成品资料数据库]
|   |-- skill_copyable_db.txt  ..............  [技能: 威吓/抄袭/重现 技能库]
|   |-- skill_damage_db.txt  ................  [技能: 伤害调整数据库]
|   |-- skill_improvise_db.txt  .............  [技能: 随机发动魔法数据库]
|   |-- skill_nonearnpc_db.txt  .............  [技能: 距离 NPC 数据库]
|   |-- spellbook_db.txt  ...................  [阅读魔法书保存点数数据库]
|   |-- import  .............................  [自定义数据库目录]
|   |-- re  .................................  [复兴后的数据库]
|   |   |-- achievement_db.yml
|   |   |-- attendance.yml
|   |   |-- attr_fix.txt  ...................  [属性伤害调整]
|   |   |-- exp_guild.txt  ..................  [公会等级经验值库]
|   |   |-- exp_homun.txt  ..................  [人工生命体等级经验值库]
|   |   |-- homunculus_db.txt  ..............  [人工生命体资料库]
|   |   |-- instance_db.txt  ................  [副本数据库]
|   |   |-- item_bluebox.txt  ...............  [蓝箱开出物品资料库]
|   |   |-- item_buyingstore.txt  ...........  [采购系统物品限定数据库]
|   |   |-- item_cardalbum.txt  .............  [老旧收集册能开启到的物品数据库]
|   |   |-- item_cash_db.txt  ...............  [在线商城]
|   |   |-- item_combo_db.txt  ..............  [套装产生额外效果数据库]
|   |   |-- item_db.txt  ....................  [※ 物品数据库（编号、名称、价格、重量、使用效果等）]
|   |   |-- item_delay.txt  .................  [物品使用延迟数据库]
|   |   |-- item_flag.txt
|   |   |-- item_giftbox.txt  ...............  [开启礼物盒所获得的物品数据库]
|   |   |-- item_group_db.txt
|   |   |-- item_misc.txt  ..................  [开启神秘红色箱子所获得的物品数据库]
|   |   |-- item_noequip.txt  ...............  [装备/物品/卡片 限制库]
|   |   |-- item_package.txt  ...............  [物品封包数据库]
|   |   |-- item_randomopt_db.txt
|   |   |-- item_randomopt_group.txt
|   |   |-- item_stack.txt  .................  [物品叠加量限制数据库]
|   |   |-- item_trade.txt  .................  [物品交易限制数据库]
|   |   |-- item_violetbox.txt  .............  [开启老紫箱所获得的物品数据库]
|   |   |-- job_basehpsp_db.txt .............  [Base 每级基础 HP/SP 数据库]
|   |   |-- job_db1.txt  ....................  [Job 每级增益数据库]
|   |   |-- job_exp.txt  ....................  [※ Base/Job 上限值以及升级所需经验]
|   |   |-- job_noenter_map.txt
|   |   |-- job_param_db.txt
|   |   |-- level_penalty.txt  ..............  [经验和掉落率修改数据库]
|   |   |-- map_cache.dat  ..................  [地图缓存库]
|   |   |-- mob_boss.txt  ...................  [魔物 BOSS 数据库]
|   |   |-- mob_branch.txt  .................  [Dead Branch Summonable 魔物资料库]
|   |   |-- mob_db.txt  .....................  [魔物属性]
|   |   |-- mob_drop.txt  ...................  [魔物掉落数据库]
|   |   |-- mob_poring.txt  .................  [Poring Box Summonable 魔物资料库]
|   |   |-- mob_race2_db.txt  ...............  [怪物族群数据库]
|   |   |-- mob_random_db.txt
|   |   |-- mob_skill_db.txt  ...............  [魔物技能数据库]
|   |   |-- pet_db.txt  .....................  [宠物属性]
|   |   |-- produce_db.txt  .................  [物品制造数据库]
|   |   |-- quest_db.txt  ...................  [制作任务数据库]
|   |   |-- refine_db.yml  ..................  [精炼数据库]
|   |   |-- skill_cast_db.txt  ..............  [※ 技能时间数据库（咏唱、固定咏唱、 CD、 副作用持续时间等）]
|   |   |-- skill_castnodex_db.txt  .........  [影响技能时间数据库]
|   |   |-- skill_db.txt  ...................  [技能数据库]
|   |   |-- skill_nocast_db.txt  ............  [技能限制数据库]
|   |   |-- skill_require_db.txt  ...........  [前置技能数据库]
|   |   |-- skill_tree.txt  .................  [技能树]
|   |   |-- skill_unit_db.txt
|   |   └-- statpoint.txt  ..................  [※ Base 每级素质点（需开启 exp.conf 的 use_statpoint_table）]
|   └-- README.md  ..........................  [RO 服务端文本数据库说明]
|-- npc  ....................................  [NPC 脚本库（在 re 文件夹内表示"复兴后", .99Max 后缀为已加密）]
|   |-- 任务系统
|   |   └-- seals  ..........................  [※ 复兴前四神器任务]
|   |       |-- god_global.txt  .............  [四神器任务人数 & 进度调整脚本]
|   |       |-- seal_status.txt  ............  [四神器任务进度面板]
|   |       |-- god_weapon_creation.txt  ....  [六神器创建脚本（含复兴后的新神器）]
|   |       |-- brisingamen_seal.txt  .......  [女神颈链解封任务]
|   |       |-- megingjard_seal.txt  ........  [雷神腰带解封任务]
|   |       |-- sleipnir_seal.txt  ..........  [史雷普尼尔之靴解封任务]
|   |       └-- mjolnir_seal.txt  ...........  [雷神之锤解封任务]
|   |-- re  .................................  [复兴后的脚本库]
|   |   |-- script_main.conf  ...............  [※ NPC 配置脚本的总入口点]
|   |   |-- 任务系统.conf  ...................  [子系统脚本的入口点]
|   |   |-- 传送点.conf  .....................  [※ 子系统脚本的入口点]
|   |   |-- 副本系统.conf  ...................  [子系统脚本的入口点]
|   |   |-- 功能脚本.conf  ...................  [子系统脚本的入口点]
|   |   |-- 卡普拉人员.conf  .................  [子系统脚本的入口点]
|   |   |-- 商店系统.conf  ...................  [子系统脚本的入口点]
|   |   |-- 地图属性.conf  ...................  [子系统脚本的入口点]
|   |   |-- 城镇人员.conf  ...................  [子系统脚本的入口点]
|   |   |-- 导游人员.conf  ...................  [子系统脚本的入口点]
|   |   |-- 攻城战.conf  .....................  [子系统脚本的入口点]
|   |   |-- 攻城战TE.conf  ...................  [NPC 子系统脚本的入口点]
|   |   |-- 职业就职.conf  ...................  [NPC 子系统脚本的入口点]
|   |   |-- 飞空艇.conf  .....................  [NPC 子系统脚本的入口点]
|   |   |-- 魔物刷新.conf  ...................  [NPC 子系统脚本的入口点]
|   |   |-- 任务系统
|   |   |-- 传送点
|   |   |   └-- custom_warp.txt  ............  [※ 自定义传送阵]
|   |   |-- 副本系统
|   |   |-- 功能脚本
|   |   |   |-- dismount_card.txt  ..........  [※ 安全拆卡 NPC]
|   |   |   |-- onekey_master.txt  ..........  [※ 一键大师]
|   |   |   └-- update.txt  .................  [※ 升级内容公示面板]
|   |   |-- 卡普拉人员
|   |   |-- 商店系统
|   |   |   └-- custom_shop.txt  ............  [※ 自定义商店 NPC]
|   |   |-- 地图属性
|   |   |-- 城镇人员
|   |   |-- 导游人员
|   |   |-- 攻城战
|   |   |-- 攻城战TE
|   |   |-- 职业就职
|   |   |-- 飞空艇
|   |   └-- 魔物刷新
|-- doc  ....................................  [NPC 的脚本说明文档（RO 一切脚本均是 NPC）]
|   └-- script_commands.txt  ................  [※ 脚本函数目录（编写脚本时经常需要查阅）]
└-- README.md  ..............................  [RO 服务端说明]

994 directories, 10054 files
```

</details>


------

## 版权声明

　[![Copyright (C) 2016-2020 By EXP](https://img.shields.io/badge/Copyright%20(C)-2016~2019%20By%20EXP-blue.svg)](http://exp-blog.com)　[![License: GPL v3](https://img.shields.io/badge/License-GPL%20v3-blue.svg)](https://www.gnu.org/licenses/gpl-3.0)
  

- Site: [http://exp-blog.com](http://exp-blog.com) 
- Mail: <a href="mailto:289065406@qq.com?subject=[EXP's Github]%20Your%20Question%20（请写下您的疑问）&amp;body=What%20can%20I%20help%20you?%20（需要我提供什么帮助吗？）">289065406@qq.com</a>


------
