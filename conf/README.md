# 关于 import 目录

## import 目录有什么用 ？

`import` 提供了一种方式去变更自定义配置，而不需要直接修改 `/conf` 目录下的主配置（同名配置将被覆盖），这样可以很大程度上避免 git 合并时引起的冲突。

下面提供一些配置示例：

------
### 登录服务器

假如希望关闭 `_M/F` 注册系统，采用 MD5 密码的注册方式，可以增加一个自定义配置文件 `conf/import/login_conf.txt` ，在其中写入配置项：

```
new_account: no
use_MD5_passwords: yes
```

> `new_account` 本是 `conf/login_athena.conf` 里面的一个同名配置项，它将被覆盖


------
### 角色服务器

假如希望更改服务器名称为 `RO仙境传说` ，可以增加一个自定义配置文件 `conf/import/char_conf.txt` ，在其中写入配置项：

```
server_name: RO仙境传说
```

> `server_name` 本是 `conf/char_athena.conf` 里面的一个同名配置项，它将被覆盖


------
### 地图服务器

假如希望隐藏所有错误信息，并且增加一些自定义地图，可以增加一个自定义配置文件 `conf/import/map_conf.txt` ，在其中写入配置项：

```
// Makes server output more silent by omitting certain types of messages:
// 16: Hide Error and SQL Error messages.
console_silent: 16
map: 1@toy
map: 1@valley
map: shops
```

> `console_silent` 本是 `conf/map_athena.conf` 里面的一个同名配置项，它将被覆盖


------
### Socket服务器

假如希望使用 mysql 数据库代替 txt 文件库，可以增加一个自定义配置文件 `conf/import/inter_conf.txt` ，在其中写入配置项：

```
use_sql_db: yes
```

> `use_sql_db` 本是 `conf/inter_athena.conf` 里面的一个同名配置项，它将被覆盖


------
### 日志设置

We want to log all items and all chat messages.

#### /conf/import/log_conf.txt

	log_filter: 1
	// Log CHAT (Global, Whisper, Party, Guild, Main chat, Clan) (Note 3)
	// log_chat: 63 = logs everything
	log_chat: 63


### Battle Configs
---
We want to change the way various mechanics work. For anything that would be configured in the `/conf/battle/` directory, it will go into `import/battle_conf.txt`. To help you find which configs came from where, it's generally a good idea to comment out the name of the file that specific collection of configs came from.

#### /conf/import/battle_conf.txt

	// guild.conf
	guild_exp_limit: 90

	// items.conf
	vending_over_max: no
	vending_tax: 100
	weapon_produce_rate: 200
	potion_produce_rate: 200
	produce_item_name_input: 0x03

	// misc.conf
	duel_time_interval: 2
	at_mapflag: yes
	at_monsterignore: yes
	cashshop_show_points: yes
	hide_fav_sell: yes
	// Whether or not mail box status is displayed upon login.
	// Default: 0
	// 0 = No
	// 1 = Yes
	// 2 = Yes, when there are unread mails
	mail_show_status: 2

	// monster.conf
	show_mob_info: 3

	// party.conf
	party_hp_mode: 1
	display_party_name: yes

	// pet.conf
	pet_rename: yes

	// player.conf
	max_aspd: 196
	max_third_aspd: 196
	max_extended_aspd: 196
	vip_disp_rate: no

	// status.conf
	debuff_on_logout: 3


We cannot stress enough how helpful this system is for everyone. The majority of git conflicts will simply go away if users make use of the `import/` system.
