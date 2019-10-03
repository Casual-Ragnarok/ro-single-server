# 关于 import 目录

## import 目录有什么用 ？

`import` 提供了一种方式去变更自定义数据，而不需要直接修改 `/db` 目录下的原数据（同名数据将被覆盖），这样可以很大程度上避免 git 合并时引起的冲突。

下面提供一些配置示例：

------
### 成就系统

假如希望自定义成就系统，并且这些成就可以通过 NPC 或 GM 提供给玩家，可以增加一个自定义数据文件 `db/import/achievement_db.yml` ，在其中写入类似格式的内容：

```
Achievements:
  - ID: 280000
    Group: "AG_GOAL_ACHIEVE"
    Name: "Emperio"
    Reward:
      TitleID: 1035
    Score: 50
  - ID: 280001
    Group: "AG_GOAL_ACHIEVE"
    Name: "Staff"
    Reward:
      TitleID: 1036
    Score: 50
```

------
### 房屋实体

假如希望在地图上增加自定义的房屋实体，可以增加一个自定义数据文件 `db/import/instance_db.txt` ，在其中写入类似格式的内容：

```
// ID,Name,LimitTime,IdleTimeOut,EnterMap,EnterX,EnterY,Map2,Map3,...,Map255
35,Home,3600,900,1@home,24,6,2@home,3@home
```

------
### 魔物别名

假如希望为魔物自定义别名你，可以增加一个自定义数据文件 `db/import/mob_avail.txt` ，在其中写入类似格式的内容：

```
// Structure of Database:
// MobID,SpriteID{,Equipment}
3850,0
```

------
### 自定义地图

假如希望增加自定义地图，需要先在 `db/import/map_cache.dat` 写入自定义地图数据，然后把地图名称添加到自定义索引表 `db/import/map_index.txt`， 在其中写入如下内容，使得地图服务器可以自动加载地图：

```
1@home	1250
2@home
3@home
ev_has
shops
prt_pvp
```

------
### 物品交易限制

假如希望特定的物品不能被交易、出售、掉落、存放等，可以增加一个自定义数据文件 `db/import/item_trade.txt` ，在其中写入类似格式的内容：

```
// Legend for 'TradeMask' field (bitmask):
// 1   - item can't be dropped
// 2   - item can't be traded (nor vended)
// 4   - wedded partner can override restriction 2
// 8   - item can't be sold to npcs
// 16  - item can't be placed in the cart
// 32  - item can't be placed in the storage
// 64  - item can't be placed in the guild storage
// 128 - item can't be attached to mail
// 256 - item can't be auctioned
// Full outright value = 511
34000,511,100	// Old Green Box
34001,511,100	// House Keys
34002,511,100	// Reputation Journal
```

------
### 自定义任务

假如希望增加自定义任务，可以增加一个自定义数据文件 `db/import/quest_db.txt` ，在其中写入类似格式的内容：

```
// Quest ID,Time Limit,Target1,Val1,Target2,Val2,Target3,Val3,MobID1,NameID1,Rate1,MobID2,NameID2,Rate2,MobID3,NameID3,Rate3,Quest Title
89001,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,"Reputation Quest"
89002,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,"Reputation Quest"
```


