# RO-Single-Server

> 仙境RO传说-单机版-服务端（支持联网开服）

------


## 关于网游单机化的历史背景
<details>
<summary>展开查看</summary>

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

> 注： EA 的源码可以看一下，使用 C 编写，需要编译使用。 但是它的官方域名 eathena.ws 已过期并被挟持，就不要随便打开了

------

这里再扩展介绍一下 SeAthena （下文简称 SeA ）。

它是由 Inkfish 做的一个汉化版 EA ，现在 <b>大部分私服都是使用 SeA 做的</b>。

原因是 SeA 的收费版有很多不错的扩展功能（但免费版则有限制）。

所以如果想搭建自己的 RO 私服，EA 还是比较靠谱的，不仅免费而且方便 DIY 。

但是如果怕麻烦，使用 SeA 也是一个不错的选择，而且 [SeA 的论坛](http://www.4fro.cn/forum.php) 也是一个不错的学习地方。


</details>


## 运行环境

　![](https://img.shields.io/badge/Platform-Windows%207%2f8%2f10%20x64-brightgreen.svg) 



## 简介



## 破解说明

## 素材版本

8.19.0
4.3
KRO


## 使用教程


------

## 版权声明

　[![Copyright (C) 2016-2020 By EXP](https://img.shields.io/badge/Copyright%20(C)-2016~2019%20By%20EXP-blue.svg)](http://exp-blog.com)　[![License: GPL v3](https://img.shields.io/badge/License-GPL%20v3-blue.svg)](https://www.gnu.org/licenses/gpl-3.0)
  

- Site: [http://exp-blog.com](http://exp-blog.com) 
- Mail: <a href="mailto:289065406@qq.com?subject=[EXP's Github]%20Your%20Question%20（请写下您的疑问）&amp;body=What%20can%20I%20help%20you?%20（需要我提供什么帮助吗？）">289065406@qq.com</a>


------
