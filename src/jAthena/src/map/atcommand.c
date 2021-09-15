//atcommand.c
#include <stdio.h>
#include <stdlib.h>
#include <string.h>
#include <ctype.h>
#include <math.h>
#include <time.h>

#include "socket.h"
#include "timer.h"
#include "nullpo.h"

#include "clif.h"
#include "chrif.h"
#include "intif.h"
#include "itemdb.h"
#include "map.h"
#include "pc.h"
#include "skill.h"
#include "mob.h"
#include "pet.h"
#include "battle.h"
#include "party.h"
#include "guild.h"
#include "atcommand.h"
#include "script.h"
#include "npc.h"
#include "status.h"
//#include "trade.h"
//#include "core.h"

#define OPTION_HIDE 0x40
#define STATE_BLIND 0x10

#ifdef MEMWATCH
#include "memwatch.h"
#endif

#ifdef _MSC_VER
	#define snprintf _snprintf
#endif

char msg_table[200][1024];	/* Server message */

#define ATCOMMAND_FUNC(x) int atcommand_ ## x (const int fd, struct map_session_data* sd, const char* command, const char* message)
//ATCOMMAND_FUNC(broadcast);
//ATCOMMAND_FUNC(localbroadcast);
ATCOMMAND_FUNC(rurap);
ATCOMMAND_FUNC(rura);
ATCOMMAND_FUNC(where);
ATCOMMAND_FUNC(jumpto);
ATCOMMAND_FUNC(jump);
ATCOMMAND_FUNC(who);
ATCOMMAND_FUNC(save);
ATCOMMAND_FUNC(load);
ATCOMMAND_FUNC(speed);
ATCOMMAND_FUNC(storage);
ATCOMMAND_FUNC(guildstorage);
ATCOMMAND_FUNC(option);
ATCOMMAND_FUNC(hide);
ATCOMMAND_FUNC(jobchange);
ATCOMMAND_FUNC(die);
ATCOMMAND_FUNC(kill);
ATCOMMAND_FUNC(alive);
ATCOMMAND_FUNC(kami);
ATCOMMAND_FUNC(heal);
ATCOMMAND_FUNC(item);
ATCOMMAND_FUNC(item2);
ATCOMMAND_FUNC(itemreset);
ATCOMMAND_FUNC(itemcheck);
ATCOMMAND_FUNC(baselevelup);
ATCOMMAND_FUNC(joblevelup);
ATCOMMAND_FUNC(help);
ATCOMMAND_FUNC(gm);
ATCOMMAND_FUNC(pvpoff);
ATCOMMAND_FUNC(pvpon);
ATCOMMAND_FUNC(gvgoff);
ATCOMMAND_FUNC(gvgon);
ATCOMMAND_FUNC(model);
ATCOMMAND_FUNC(go);
ATCOMMAND_FUNC(monster);
ATCOMMAND_FUNC(killmonster);
ATCOMMAND_FUNC(killmonster2);
ATCOMMAND_FUNC(refine);
ATCOMMAND_FUNC(produce);
ATCOMMAND_FUNC(memo);
ATCOMMAND_FUNC(gat);
ATCOMMAND_FUNC(packet);
ATCOMMAND_FUNC(statuspoint);
ATCOMMAND_FUNC(skillpoint);
ATCOMMAND_FUNC(zeny);
ATCOMMAND_FUNC(param);
ATCOMMAND_FUNC(guildlevelup);
ATCOMMAND_FUNC(makepet);
ATCOMMAND_FUNC(hatch);
ATCOMMAND_FUNC(petfriendly);
ATCOMMAND_FUNC(pethungry);
ATCOMMAND_FUNC(petrename);
ATCOMMAND_FUNC(charpetrename);
ATCOMMAND_FUNC(recall);
ATCOMMAND_FUNC(recallall);
ATCOMMAND_FUNC(recallguild);
ATCOMMAND_FUNC(recallparty);
ATCOMMAND_FUNC(character_job);
ATCOMMAND_FUNC(revive);
ATCOMMAND_FUNC(character_stats);
ATCOMMAND_FUNC(character_option);
ATCOMMAND_FUNC(character_save);
ATCOMMAND_FUNC(night);
ATCOMMAND_FUNC(day);
ATCOMMAND_FUNC(doom);
ATCOMMAND_FUNC(doommap);
ATCOMMAND_FUNC(raise);
ATCOMMAND_FUNC(raisemap);
ATCOMMAND_FUNC(character_baselevel);
ATCOMMAND_FUNC(character_joblevel);
ATCOMMAND_FUNC(kick);
ATCOMMAND_FUNC(kickall);
ATCOMMAND_FUNC(allskill);
ATCOMMAND_FUNC(questskill);
ATCOMMAND_FUNC(charquestskill);
ATCOMMAND_FUNC(lostskill);
ATCOMMAND_FUNC(charlostskill);
ATCOMMAND_FUNC(spiritball);
ATCOMMAND_FUNC(party);
ATCOMMAND_FUNC(guild);
ATCOMMAND_FUNC(agitstart);
ATCOMMAND_FUNC(agitend);
ATCOMMAND_FUNC(onlymes);
ATCOMMAND_FUNC(mapexit);
ATCOMMAND_FUNC(idsearch);
ATCOMMAND_FUNC(itemidentify);
ATCOMMAND_FUNC(shuffle);
ATCOMMAND_FUNC(maintenance);
ATCOMMAND_FUNC(misceffect);
ATCOMMAND_FUNC(summon);
ATCOMMAND_FUNC(whop);
ATCOMMAND_FUNC(reloaditemdb);
ATCOMMAND_FUNC(reloadmobdb);
ATCOMMAND_FUNC(reloadskilldb);
ATCOMMAND_FUNC(charskreset);
ATCOMMAND_FUNC(charstreset);
ATCOMMAND_FUNC(charreset);
ATCOMMAND_FUNC(charstpoint);
ATCOMMAND_FUNC(charskpoint);
ATCOMMAND_FUNC(charzeny);
ATCOMMAND_FUNC(mapinfo);
ATCOMMAND_FUNC(mobsearch);
ATCOMMAND_FUNC(cleanmap);
ATCOMMAND_FUNC(clock);
ATCOMMAND_FUNC(giveitem);
ATCOMMAND_FUNC(weather);

/*==========================================
 *AtCommandInfo atcommand_info[]構造体の定義
 *------------------------------------------
 */
static AtCommandInfo atcommand_info[] = {
	{ AtCommand_RuraP,					"@rura+",			0, atcommand_rurap },
	{ AtCommand_Rura,					"@rura",			0, atcommand_rura },
	{ AtCommand_Where,					"@where",			0, atcommand_where },
	{ AtCommand_JumpTo,					"@jumpto",			0, atcommand_jumpto },
	{ AtCommand_Jump,					"@jump",			0, atcommand_jump },
	{ AtCommand_Who,					"@who",				0, atcommand_who },
	{ AtCommand_Save,					"@save",			0, atcommand_save },
	{ AtCommand_Load,					"@load",			0, atcommand_load },
	{ AtCommand_Speed,					"@speed",			0, atcommand_speed },
	{ AtCommand_Storage,				"@storage",			0, atcommand_storage },
	{ AtCommand_GuildStorage,			"@gstorage",		0, atcommand_guildstorage },
	{ AtCommand_Option,					"@option",			0, atcommand_option },
	{ AtCommand_Hide,					"@hide",			0, atcommand_hide },
	{ AtCommand_JobChange,				"@jobchange",		0, atcommand_jobchange },
	{ AtCommand_Die,					"@die",				0, atcommand_die },
	{ AtCommand_Kill,					"@kill",			0, atcommand_kill },
	{ AtCommand_Alive,					"@alive",			0, atcommand_alive },
	{ AtCommand_Kami,					"@kami",			0, atcommand_kami },
	{ AtCommand_KamiB,					"@kamib",			0, atcommand_kami },
	{ AtCommand_Heal,					"@heal",			0, atcommand_heal },
	{ AtCommand_Item,					"@item",			0, atcommand_item },
	{ AtCommand_Item2,					"@item2",			0, atcommand_item2 },
	{ AtCommand_ItemReset,				"@itemreset",		0, atcommand_itemreset },
	{ AtCommand_ItemCheck,				"@itemcheck",		0, atcommand_itemcheck },
	{ AtCommand_BaseLevelUp,			"@lvup",			0, atcommand_baselevelup },
	{ AtCommand_JobLevelUp,				"@joblvup",			0, atcommand_joblevelup },
	{ AtCommand_H,						"@h",				0, atcommand_help },
	{ AtCommand_Help,					"@help",			0, atcommand_help },
	{ AtCommand_GM,						"@gm",				0, atcommand_gm },
	{ AtCommand_PvPOff,					"@pvpoff",			0, atcommand_pvpoff },
	{ AtCommand_PvPOn,					"@pvpon",			0, atcommand_pvpon },
	{ AtCommand_GvGOff,					"@gvgoff",			0, atcommand_gvgoff },
	{ AtCommand_GvGOn,					"@gvgon",			0, atcommand_gvgon },
	{ AtCommand_Model,					"@model",			0, atcommand_model },
	{ AtCommand_Go,						"@go",				0, atcommand_go },
	{ AtCommand_Monster,				"@monster",			0, atcommand_monster },
	{ AtCommand_KillMonster,			"@killmonster",		0, atcommand_killmonster },
	{ AtCommand_KillMonster2,			"@killmonster2",	0, atcommand_killmonster2 },
	{ AtCommand_Refine,					"@refine",			0, atcommand_refine },
	{ AtCommand_Produce,				"@produce",			0, atcommand_produce },
	{ AtCommand_Memo,					"@memo",			0, atcommand_memo },
	{ AtCommand_GAT,					"@gat",				0, atcommand_gat },
	{ AtCommand_Packet,					"@packet",			0, atcommand_packet },
	{ AtCommand_StatusPoint,			"@stpoint",			0, atcommand_statuspoint },
	{ AtCommand_SkillPoint,				"@skpoint",			0, atcommand_skillpoint },
	{ AtCommand_Zeny,					"@zeny",			0, atcommand_zeny },
//	{ AtCommand_Param,					"@param",			0, atcommand_param },
	{ AtCommand_Strength,				"@str",				0, atcommand_param },
	{ AtCommand_Agility,				"@agi",				0, atcommand_param },
	{ AtCommand_Vitality,				"@vit",				0, atcommand_param },
	{ AtCommand_Intelligence,			"@int",				0, atcommand_param },
	{ AtCommand_Dexterity,				"@dex",				0, atcommand_param },
	{ AtCommand_Luck,					"@luk",				0, atcommand_param },
	{ AtCommand_GuildLevelUp,			"@guildlvup",		0, atcommand_guildlevelup },
	{ AtCommand_MakePet,				"@makepet",			0, atcommand_makepet },
	{ AtCommand_Hatch,					"@hatch",			0, atcommand_hatch },
	{ AtCommand_PetFriendly,			"@petfriendly",		0, atcommand_petfriendly },
	{ AtCommand_PetHungry,				"@pethungry",		0, atcommand_pethungry },
	{ AtCommand_PetRename,				"@petrename",		0, atcommand_petrename },
	{ AtCommand_CharPetRename,			"@charpetrename",	0, atcommand_charpetrename },
	{ AtCommand_Recall,					"@recall",			0, atcommand_recall },
	{ AtCommand_Recallall,				"@recallall",		0, atcommand_recallall },
	{ AtCommand_RecallGuild,			"@recallguild",		0, atcommand_recallguild },
	{ AtCommand_RecallParty,			"@recallparty",		0, atcommand_recallparty },
	{ AtCommand_CharacterJob,			"@charjob",			0, atcommand_character_job },
	{ AtCommand_Revive,					"@revive",			0, atcommand_revive },
	{ AtCommand_CharacterStats,			"@charstats",		0, atcommand_character_stats },
	{ AtCommand_CharacterOption,		"@charoption",		0, atcommand_character_option },
	{ AtCommand_CharacterSave,			"@charsave",		0, atcommand_character_save },
	{ AtCommand_Night,					"@night",			0, atcommand_night },
	{ AtCommand_Day,					"@day",				0, atcommand_day },
	{ AtCommand_Doom,					"@doom",			0, atcommand_doom },
	{ AtCommand_DoomMap,				"@doommap",			0, atcommand_doommap },
	{ AtCommand_Raise,					"@raise",			0, atcommand_raise },
	{ AtCommand_RaiseMap,				"@raisemap",		0, atcommand_raisemap },
	{ AtCommand_CharacterBaseLevel,		"@charbaselvl",		0, atcommand_character_baselevel },
	{ AtCommand_CharacterJobLevel,		"@charjlvl",		0, atcommand_character_joblevel },
	{ AtCommand_Kick,					"@kick",			0, atcommand_kick },
	{ AtCommand_KickAll,				"@kickall",			0, atcommand_kickall },
	{ AtCommand_AllSkill,				"@allskill",		0, atcommand_allskill },
	{ AtCommand_AllSkill,				"@skillall",		0, atcommand_allskill },
	{ AtCommand_QuestSkill,				"@questskill",		0, atcommand_questskill },
	{ AtCommand_CharQuestSkill,			"@charquestskill",	0, atcommand_charquestskill },
	{ AtCommand_LostSkill,				"@lostskill",		0, atcommand_lostskill },
	{ AtCommand_CharLostSkill,			"@charlostskill",	0, atcommand_charlostskill },
	{ AtCommand_SpiritBall,				"@spiritball",		0, atcommand_spiritball },
	{ AtCommand_Party,					"@party",			0, atcommand_party },
	{ AtCommand_Guild,					"@guild",			0, atcommand_guild },
	{ AtCommand_AgitStart,				"@agitstart",		0, atcommand_agitstart },
	{ AtCommand_AgitEnd,				"@agitend",			0, atcommand_agitend },
	{ AtCommand_OnlyMes,				"@mes",				0, atcommand_onlymes },
	{ AtCommand_MapExit,				"@mapexit",			0, atcommand_mapexit },
	{ AtCommand_IDSearch,				"@idsearch",		0, atcommand_idsearch },
	{ AtCommand_ItemIdentify,			"@itemidentify",	0, atcommand_itemidentify },
	{ AtCommand_Shuffle,				"@shuffle",			0, atcommand_shuffle },
	{ AtCommand_Maintenance,			"@maintenance",		0, atcommand_maintenance },
	{ AtCommand_Misceffect,				"@misceffect",		0, atcommand_misceffect },
	{ AtCommand_Summon,					"@summon",			0, atcommand_summon },
	{ AtCommand_WhoP,					"@who+",			0, atcommand_whop },
	{ AtCommand_ReloadItemDB,			"@reloaditemdb",	0, atcommand_reloaditemdb }, // admin command
	{ AtCommand_ReloadMobDB,			"@reloadmobdb",		0, atcommand_reloadmobdb }, // admin command
	{ AtCommand_ReloadSkillDB,			"@reloadskilldb",	0, atcommand_reloadskilldb }, // admin command
	{ AtCommand_CharReset,				"@charreset",		0, atcommand_charreset },
	{ AtCommand_CharSkReset,			"@charskreset",		0, atcommand_charskreset },
	{ AtCommand_CharStReset,			"@charstreset",		0, atcommand_charstreset },
	{ AtCommand_CharSKPoint,			"@charskpoint",		0, atcommand_charskpoint },
	{ AtCommand_CharSTPoint,			"@charstpoint",		0, atcommand_charstpoint },
	{ AtCommand_CharZeny,				"@charzeny",		0, atcommand_charzeny },
	{ AtCommand_MapInfo,				"@mapinfo",			0, atcommand_mapinfo },
	{ AtCommand_MobSearch,				"@mobsearch",		0, atcommand_mobsearch },
	{ AtCommand_CleanMap,				"@cleanmap",		0, atcommand_cleanmap },
	{ AtCommand_Clock,					"@clock",			0, atcommand_clock },
	{ AtCommand_GiveItem,				"@giveitem",		0, atcommand_giveitem },
	{ AtCommand_Weather,				"@weather",			0, atcommand_weather },
	// add here
	{ AtCommand_MapMove,				"@mapmove",			0, NULL },
	{ AtCommand_Broadcast,				"@broadcast",		0, NULL },
	{ AtCommand_LocalBroadcast,			"@local_broadcast",	0, NULL },
	{ AtCommand_Unknown,				NULL,				0, NULL }
};

/*==========================================
 *get_atcommand_level @コマンドの必要レベルを取得
 *------------------------------------------
 */
int get_atcommand_level(const AtCommandType type)
{
	int i = 0;
	for (i = 0; atcommand_info[i].type != AtCommand_None; i++)
		if (atcommand_info[i].type == type)
			return atcommand_info[i].level;
	return 99;
}

/*==========================================
 *is_atcommand @コマンドに存在するかどうか確認する
 *------------------------------------------
 */
AtCommandType
is_atcommand(const int fd, struct map_session_data* sd, const char* message, int gmlvl)
{
	const char* str = message;
	int s_flag = 0;
	AtCommandInfo info;
	AtCommandType type;

	nullpo_retr(0, sd);

	if (!message || !*message)
		return AtCommand_None;
	memset(&info, 0, sizeof info);
	str += strlen(sd->status.name);
	while (*str && (isspace(*str) || (s_flag == 0 && *str == ':'))) {
		if (*str == ':')
			s_flag = 1;
		str++;
	}
	if (!*str)
		return AtCommand_None;
	type = atcommand((gmlvl > 0 )?gmlvl:pc_isGM(sd), str, &info);
//	type = atcommand(pc_isGM(sd), str, &info);
	if (type != AtCommand_None) {
		char command[25];
		char output[100];
		const char* p = str;
		memset(command, '\0', sizeof command);
		while (*p && !isspace(*p))
			p++;
		if (p - str > sizeof command) // too long
			return AtCommand_Unknown;
		strncpy(command, str,
			(p - str > sizeof command) ?  sizeof command : p - str);
		if (isspace(*p))
			p++;
		if (type == AtCommand_Unknown) {
			if(pc_isGM(sd)){
	 			snprintf(output, sizeof output, "%s is Unknown Command.", command);
	 			clif_displaymessage(fd, output);
	 		}else{
 				return AtCommand_None;
	 		}
		} else if(pc_isGM(sd)) {
			if (info.proc(fd, sd, command, p) != 0) {
				// 異常終了
 				snprintf(output, sizeof output, "%s failed.", command);
 				clif_displaymessage(fd, output);
			}
		} else {
			if (info.proc(fd, sd, command, p) != 0)
 				return AtCommand_None;
		}
		return info.type;
	}
	return AtCommand_None;
}

/*==========================================
 * 
 *------------------------------------------
 */
AtCommandType
atcommand(
	const int level, const char* message, struct AtCommandInfo* info)
{
	const char* p = message;
	if (!info)
		return AtCommand_None;
	if (!p || !*p) {
		fprintf(stderr, "at command message is empty\n");
		return AtCommand_None;
	}
	if (!*p || *p != '@')
		return AtCommand_None;
	
	if(p[0] == '@' && p[1] == '@')
		return AtCommand_None;

	memset(info, 0, sizeof(AtCommandInfo));
	{
		char command[101];
		int i = 0;
		sscanf(p, "%100s", command);
		command[100] = '\0';
		
		while (atcommand_info[i].type != AtCommand_Unknown) {
			if (strcmpi(command, atcommand_info[i].command) == 0 &&
				level >= atcommand_info[i].level)
				break;
			i++;
		}
		if (atcommand_info[i].type == AtCommand_Unknown || atcommand_info[i].proc == NULL )
			return AtCommand_Unknown;
		memcpy(info, &atcommand_info[i], sizeof atcommand_info[i]);
	}
	
	return info->type;
}

//struct Atcommand_Config atcommand_config;

/*==========================================
 * 
 *------------------------------------------
 */
static int atkillmonster_sub(struct block_list *bl,va_list ap)
{
	int flag = va_arg(ap,int);
	nullpo_retr(0, bl);
	
	if (flag)
		mob_damage(NULL,(struct mob_data *)bl,((struct mob_data *)bl)->hp,2);
	else
		mob_delete((struct mob_data *)bl);
	return 0;
}
/*==========================================
 * Mob search
 *------------------------------------------
 */
static int atmobsearch_sub(struct block_list *bl,va_list ap)
{
	int mob_id,fd;
	static int number=0;
	struct mob_data *md;
	char output[128];
	
	nullpo_retr(0, bl);
	
	if(!ap){
		number=0;
		return 0;
	}
	mob_id = va_arg(ap,int);
	fd = va_arg(ap,const int);
	
	md = (struct mob_data *)bl;
	
	if(md && fd && (mob_id==-1 || (md->class==mob_id))){
		snprintf(output, sizeof output, msg_table[94],
				++number,bl->x, bl->y,md->name);
		clif_displaymessage(fd, output);
	}
	return 0;
}
/*==========================================
 * cleanmap
 *------------------------------------------
 */
static int atcommand_cleanmap_sub(struct block_list *bl,va_list ap)
{
	struct flooritem_data *fitem;

	nullpo_retr(0, bl);

	fitem = (struct flooritem_data *)bl;
	if(fitem==NULL || fitem->bl.type!=BL_ITEM){
		if(battle_config.error_log)
			printf("map_clearflooritem_timer : error\n");
		return 1;
	}
	delete_timer(fitem->cleartimer,map_clearflooritem_timer);
	if(fitem->item_data.card[0] == (short)0xff00)
		intif_delete_petdata(*((long *)(&fitem->item_data.card[1])));
	clif_clearflooritem(fitem,0);
	map_delobject(fitem->bl.id);

	return 0;
}
/*==========================================
 * 
 *------------------------------------------
 */
/* Read Message Data */
int msg_config_read(const char *cfgName)
{
	int i,msg_number;
	char line[1024],w1[512],w2[1024];
	FILE *fp;

	fp=fopen(cfgName,"r");
	if (fp==NULL) {
		printf("file not found: %s\n",cfgName);
		return 1;
						}
	while(fgets(line,1020,fp)) {
		if (line[0] == '/' && line[1] == '/')
			continue;
		i=sscanf(line,"%d: %[^\r\n]",&msg_number,w2);
		if (i!=2) {
			if (sscanf(line,"%s: %[^\r\n]",w1,w2) !=2)
				continue;
			if (strcmpi(w1,"import") == 0) {
				msg_config_read(w2);
					}
			continue;
				}
		if (msg_number>=0&&msg_number<=200)
			strncpy(msg_table[msg_number],w2,1024);
		//printf("%d:%s\n",msg_number,msg);
			}
	fclose(fp);
	return 0;
}

/*
static AtCommandInfo* getAtCommandInfoByType(const AtCommandType type)
{
	int i = 0;
	for (i = 0; atcommand_info[i].type != AtCommand_None; i++)
		if (atcommand_info[i].type == type)
			return &atcommand_info[i];
	return NULL;
}
*/

/*==========================================
 * 
 *------------------------------------------
 */
static AtCommandInfo*
get_atcommandinfo_byname(const char* name)
{
	int i = 0;
	for (i = 0; atcommand_info[i].type != AtCommand_Unknown; i++)
		if (strcmpi(atcommand_info[i].command + 1, name) == 0)
			return &atcommand_info[i];
	return NULL;
}

/*==========================================
 * 
 *------------------------------------------
 */
int atcommand_config_read(const char *cfgName)
{
	int i;
	char line[1024],w1[1024],w2[1024];
	
	if (battle_config.atc_gmonly > 0) {
		AtCommandInfo* p = NULL;
		FILE* fp = fopen(cfgName,"r");
		if (fp == NULL) {
			printf("file not found: %s\n",cfgName);
			return 1;
		}
		while (fgets(line, 1020, fp)) {
			if (line[0] == '/' && line[1] == '/')
				continue;
			i=sscanf(line,"%1020[^:]:%1020s",w1,w2);
			if (i!=2)
				continue;
			p = get_atcommandinfo_byname(w1);
			if (p != NULL)
				p->level = atoi(w2);
			
			if (strcmpi(w1, "import") == 0)
				atcommand_config_read(w2);
			}
		fclose(fp);
		}

	return 0;
}

// @ command 処理関数群

// @rura+
/*==========================================
 * 
 *------------------------------------------
 */
int
atcommand_rurap(
	const int fd, struct map_session_data* sd,
	const char* command, const char* message)
{
	char map[100];
	char character[100];
	int x = 0, y = 0;
	struct map_session_data *pl_sd = NULL;
	
	nullpo_retr(-1, sd);

	if (!message || !*message)
		return -1;
	memset(character, '\0', sizeof character);
	if (sscanf(message, "%99s %d %d %99[^\n]", map, &x, &y, character) < 4)
		return -1;
	pl_sd = map_nick2sd(character);
	if (pl_sd == NULL) {
		clif_displaymessage(fd, msg_table[3]);
		return -1;
			}
	if (pc_isGM(sd) > pc_isGM(pl_sd)) {
		if (x >= 0 && x < 400 && y >= 0 && y < 400) {
			if (pc_setpos(pl_sd, map, x, y, 3) == 0) {
				clif_displaymessage(pl_sd->fd, msg_table[0]);
			} else {
				clif_displaymessage(fd, msg_table[1]);
		}
		} else {
			clif_displaymessage(fd,msg_table[2]);
			}
		}
	
	return 0;
}

// @rura
/*==========================================
 * 
 *------------------------------------------
 */
int
atcommand_rura(
	const int fd, struct map_session_data* sd,
	const char* command, const char* message)
{
	char map[100];
	int x = 0, y = 0;
	
	nullpo_retr(-1, sd);

	if (!message || !*message)
		return -1;
	
	if (sscanf(message, "%99s %d %d", map, &x, &y) < 1)
		return -1;
			if (x >= 0 && x < 400 && y >= 0 && y < 400) {
		clif_displaymessage(fd,
			(pc_setpos((struct map_session_data*)sd, map, x, y, 3) == 0) ?
				msg_table[0] : msg_table[1]);
	} else {
		clif_displaymessage(fd, msg_table[2]);
			}
	
	return 0;
}

/*==========================================
 * 
 *------------------------------------------
 */
int
atcommand_where(
	const int fd, struct map_session_data* sd,
	const char* command, const char* message)
{
	char character[100];
//	char output[200];
//	struct map_session_data *pl_sd = NULL;

	nullpo_retr(-1, sd);

	if (!message || !*message)
		return -1;
	memset(character, '\0', sizeof character);
	if (sscanf(message, "%99[^\n]", character) < 1)
		return -1;
	if(strncmp(sd->status.name,character,24)==0)
		return -1;

	intif_where(sd->status.account_id,character);
/*
	if ((pl_sd = map_nick2sd(character)) == NULL) {
		snprintf(output, sizeof output, "%s %d %d",
			sd->mapname, sd->bl.x, sd->bl.y);
		clif_displaymessage(fd, output);
		return -1;
	}
	snprintf(output, sizeof output, "%s %s %d %d",
		character, pl_sd->mapname, pl_sd->bl.x, pl_sd->bl.y);
	clif_displaymessage(fd, output);
*/	
	return 0;
}

/*==========================================
 * 
 *------------------------------------------
 */
int
atcommand_jumpto(
	const int fd, struct map_session_data* sd,
	const char* command, const char* message)
{
	char character[100];
//	struct map_session_data *pl_sd = NULL;
	
	nullpo_retr(-1, sd);

	if (!message || !*message)
		return -1;
	
	memset(character, '\0', sizeof character);
	if (sscanf(message, "%99[^\n]", character) < 1)
		return -1;
	if(strncmp(sd->status.name,character,24)==0)
		return -1;

	intif_jumpto(sd->status.account_id,character);
/*	if ((pl_sd = map_nick2sd(character)) != NULL) {
		char output[200];
		pc_setpos((struct map_session_data*)sd,
			pl_sd->mapname, pl_sd->bl.x, pl_sd->bl.y, 3);
		snprintf(output, sizeof output, msg_table[4], character);
		clif_displaymessage(fd, output);
	} else {
		clif_displaymessage(fd, msg_table[3]);
	}
*/
	return 0;
}

/*==========================================
 * 
 *------------------------------------------
 */
int
atcommand_jump(
	const int fd, struct map_session_data* sd,
	const char* command, const char* message)
{
	int x = 0, y = 0;
	
	nullpo_retr(-1, sd);

	if (sscanf(message, "%d %d", &x, &y) < 2)
		return -1;
	if (x >= 0 && x < 400 && y >= 0 && y < 400) {
		char output[200];
		pc_setpos((struct map_session_data*)sd, (char*)sd->mapname, x, y, 3);
		snprintf(output, sizeof output, msg_table[5], x, y);
		clif_displaymessage(fd, output);
	} else {
		clif_displaymessage(fd, msg_table[2]);
	}
	
	return 0;
}

/*==========================================
 * 
 *------------------------------------------
 */
int
atcommand_who(
	const int fd, struct map_session_data* sd,
	const char* command, const char* message)
{
	map_who(fd);
	return 0;
}
/*==========================================
 * 居場所付き検索を行う
 *------------------------------------------
 */
int
atcommand_whop(
	const int fd, struct map_session_data* sd,
	const char* command, const char* message)
{
	char output[200];
	struct map_session_data *pl_sd = NULL;
	int i = 0;

	nullpo_retr(-1, sd);

	for (i = 0; i < fd_max; i++) {
		if (session[i] && (pl_sd = session[i]->session_data) &&
			pl_sd->state.auth) {
			if( !(battle_config.hide_GM_session && pc_isGM(pl_sd)) ){
				snprintf(output, sizeof output, "%s %s %d %d",
					pl_sd->status.name, pl_sd->mapname, pl_sd->bl.x, pl_sd->bl.y);
				clif_displaymessage(fd, output);
			}
		}
	}
	return 0;
}

/*==========================================
 * 
 *------------------------------------------
 */
int
atcommand_save(
	const int fd, struct map_session_data* sd,
	const char* command, const char* message)
{
	nullpo_retr(-1, sd);

	pc_setsavepoint(sd, sd->mapname, sd->bl.x, sd->bl.y);
	if (sd->status.pet_id > 0 && sd->pd)
		intif_save_petdata(sd->status.account_id, &sd->pet);
			pc_makesavestatus(sd);
			chrif_save(sd);
			storage_storage_save(sd);
	clif_displaymessage(fd, msg_table[6]);
	
	return 0;
}

/*==========================================
 * 
 *------------------------------------------
 */
int
atcommand_load(
	const int fd, struct map_session_data* sd,
	const char* command, const char* message)
{
	nullpo_retr(-1, sd);

	pc_setpos(sd, sd->status.save_point.map,
		sd->status.save_point.x, sd->status.save_point.y, 0);
	clif_displaymessage(fd, msg_table[7]);
	
	return 0;
}

/*==========================================
 * 
 *------------------------------------------
 */
int
atcommand_speed(
	const int fd, struct map_session_data* sd,
	const char* command, const char* message)
{
	int speed = DEFAULT_WALK_SPEED;

	nullpo_retr(-1, sd);

	if (!message || !*message)
		return -1;
	speed = atoi(message);
	if (speed > MIN_WALK_SPEED && speed < MAX_WALK_SPEED) {
		sd->speed = speed;
				//sd->walktimer = x;
				//この文を追加 by れあ
		clif_updatestatus(sd, SP_SPEED);
		clif_displaymessage(fd, msg_table[8]);
		}
	
	return 0;
}

/*==========================================
 * 
 *------------------------------------------
 */
int
atcommand_storage(
	const int fd, struct map_session_data* sd,
	const char* command, const char* message)
{
			storage_storageopen(sd);
	return 0;
}

/*==========================================
 * 
 *------------------------------------------
 */
int
atcommand_guildstorage(
	const int fd, struct map_session_data* sd,
	const char* command, const char* message)
{
	nullpo_retr(-1, sd);

	if (sd->status.guild_id > 0)
				storage_guild_storageopen(sd);
	return 0;
}

/*==========================================
 * 
 *------------------------------------------
 */
int
atcommand_option(
	const int fd, struct map_session_data* sd,
	const char* command, const char* message)
{
	int param1 = 0, param2 = 0, param3 = 0;
	
	nullpo_retr(-1, sd);

	if (!message || !*message)
		return -1;
	
	if (sscanf(message, "%d %d %d", &param1, &param2, &param3) < 1)
		return -1;
	sd->opt1 = param1;
	sd->opt2 = param2;
	if (!(sd->status.option & CART_MASK) && param3 & CART_MASK) {
				clif_cart_itemlist(sd);
				clif_cart_equiplist(sd);
		clif_updatestatus(sd, SP_CARTINFO);
			}
	sd->status.option = param3;
			clif_changeoption(&sd->bl);
			clif_displaymessage(fd,msg_table[9]);
	
	return 0;
}

/*==========================================
 * 
 *------------------------------------------
 */
int
atcommand_hide(
	const int fd, struct map_session_data* sd,
	const char* command, const char* message)
{
	nullpo_retr(-1, sd);

	if (sd->status.option & OPTION_HIDE) {
		sd->status.option &= ~OPTION_HIDE;
		clif_displaymessage(fd, msg_table[10]);
	} else {
		sd->status.option |= OPTION_HIDE;
		clif_displaymessage(fd, msg_table[11]);
			}
			clif_changeoption(&sd->bl);
	
	return 0;
}

/*==========================================
 * 転職する upperを指定すると転生や養子にもなれる
 *------------------------------------------
 */
int
atcommand_jobchange(
	const int fd, struct map_session_data* sd,
	const char* command, const char* message)
{
	int job = 0, upper = -1;
	if (!message || !*message)
		return -1;
	if (sscanf(message, "%d %d", &job, &upper) < 1)
		return -1;

	if ((job >= 0 && job < MAX_PC_CLASS)) {
		if(pc_jobchange(sd, job, upper) == 0)
			clif_displaymessage(fd, msg_table[12]);
	}
	
	return 0;
}

/*==========================================
 * 
 *------------------------------------------
 */
int
atcommand_die(
	const int fd, struct map_session_data* sd,
	const char* command, const char* message)
{
	nullpo_retr(-1, sd);

	pc_damage(NULL, sd, sd->status.hp + 1);
	clif_displaymessage(fd, msg_table[13]);
	
	return 0;
}

/*==========================================
 * 
 *------------------------------------------
 */
int
atcommand_kill(
	const int fd, struct map_session_data* sd,
	const char* command, const char* message)
{
	char character[100];
	struct map_session_data *pl_sd = NULL;
	
	if (!message || !*message)
		return -1;
	memset(character, '\0', sizeof character);
	sscanf(message, "%99[^\n]", character);
	if ((pl_sd = map_nick2sd(character)) != NULL) {
		if (pc_isGM(sd) > pc_isGM(pl_sd)) {
			pc_damage(NULL, pl_sd, pl_sd->status.hp + 1);
			clif_displaymessage(fd, msg_table[14]);
			}
	} else {
		clif_displaymessage(fd, msg_table[15]);
		}
	
	return 0;
}

/*==========================================
 * 
 *------------------------------------------
 */
int
atcommand_alive(
	const int fd, struct map_session_data* sd,
	const char* command, const char* message)
{
	nullpo_retr(-1, sd);

	sd->status.hp = sd->status.max_hp;
	sd->status.sp = sd->status.max_sp;
			pc_setstand(sd);
	if (battle_config.pc_invincible_time > 0)
		pc_setinvincibletimer(sd, battle_config.pc_invincible_time);
	clif_updatestatus(sd, SP_HP);
	clif_updatestatus(sd, SP_SP);
	clif_resurrection(&sd->bl, 1);
	clif_displaymessage(fd, msg_table[16]);
	
	return 0;
}

/*==========================================
 * 
 *------------------------------------------
 */
int
atcommand_kami(
	const int fd, struct map_session_data* sd,
	const char* command, const char* message)
{
	char output[200];
	
	if (!message || !*message)
		return -1;
	sscanf(message, "%199[^\n]", output);
	intif_GMmessage(output,
		strlen(output) + 1,
		(*(command + 5) == 'b') ? 0x10 : 0);
	
	return 0;
}

/*==========================================
 *叫ぶ
 *------------------------------------------
 */
int atcommand_onlymes(
	const int fd, struct map_session_data* sd,
	const char* command, const char* message)
{
	char temp0[200],temp1[200];

	sscanf(message, "%199[^#\n]", temp0);
	strcpy(temp1,"[叫ぶ]");
	strcat(temp1,sd->status.name);
	strcat(temp1," :");
	strcat(temp1,temp0);
	
	clif_onlymessage(sd,temp1,strlen(temp1)+1);

	return 0;
}

/*==========================================
 * 
 *------------------------------------------
 */
int
atcommand_heal(
	const int fd, struct map_session_data* sd,
	const char* command, const char* message)
{
	int hp = 0, sp = 0;
	
	nullpo_retr(-1, sd);

	if (!message || !*message) {
	} else if (sscanf(message, "%d %d", &hp, &sp) < 1)
		return -1;
	if (hp <= 0 && sp <= 0) {
		hp = sd->status.max_hp-sd->status.hp;
		sp = sd->status.max_sp-sd->status.sp;
	} else {
		if (sd->status.hp + hp > sd->status.max_hp) {
			hp = sd->status.max_hp - sd->status.hp;
		}
		if (sd->status.sp + sp > sd->status.max_sp) {
			sp = sd->status.max_sp - sd->status.sp;
		}
		}
	clif_heal(fd, SP_HP, (hp > 0x7fff) ? 0x7fff : hp);
	clif_heal(fd, SP_SP, (sp > 0x7fff) ? 0x7fff : sp);
	pc_heal(sd, hp, sp);
	clif_displaymessage(fd, msg_table[17]);
	
	return 0;
}

/*==========================================
 * 
 *------------------------------------------
 */
int
atcommand_item(
	const int fd, struct map_session_data* sd,
	const char* command, const char* message)
{
	char item_name[100];
	int number = 0, item_id = 0, flag = 0;
			struct item item_tmp;
			struct item_data *item_data;

	if (!message || !*message)
		return -1;

	if (sscanf(message, "%99s %d", item_name, &number) < 1)
		return -1;
	if (number <= 0)
		number = 1;
	
	if ((item_id = atoi(item_name)) > 0) {
		if (battle_config.item_check) {
			item_id =
				(((item_data = itemdb_exists(item_id)) &&
				 itemdb_available(item_id)) ? item_id : 0);
		} else {
			item_data = itemdb_search(item_id);
				}
	} else if ((item_data = itemdb_searchname(item_name)) != NULL) {
		item_id = (!battle_config.item_check ||
			itemdb_available(item_data->nameid)) ? item_data->nameid : 0;
	}
	
	if (item_id > 0) {
		int loop = 1, get_count = number,i;
		if (item_data->type == 4 || item_data->type == 5 ||
			item_data->type == 7 || item_data->type == 8) {
			loop = number;
			get_count = 1;
		}
		for (i = 0; i < loop; i++) {
			memset(&item_tmp, 0, sizeof(item_tmp));
			item_tmp.nameid = item_id;
			item_tmp.identify = 1;
			if ((flag = pc_additem((struct map_session_data*)sd,
					&item_tmp, get_count)))
				clif_additem((struct map_session_data*)sd, 0, 0, flag);
				}
		clif_displaymessage(fd, msg_table[18]);
			} else {
		clif_displaymessage(fd, msg_table[19]);
			}
	
	return 0;
}

/*==========================================
 * 
 *------------------------------------------
 */
int
atcommand_item2(
	const int fd, struct map_session_data* sd,
	const char* command, const char* message)
{
			struct item item_tmp;
			struct item_data *item_data;
	char item_name[100];
	int item_id = 0, number = 0;
	int identify = 0, refine = 0, attr = 0;
	int c1 = 0, c2 = 0, c3 = 0, c4 = 0;
	int flag = 0;
	
	if (sscanf(message, "%99s %d %d %d %d %d %d %d %d", item_name, &number,
		&identify, &refine, &attr, &c1, &c2, &c3, &c4) >= 9) {
		if (number <= 0)
			number = 1;
		
		if ((item_id = atoi(item_name)) > 0) {
			if (battle_config.item_check) {
				item_id =
					(((item_data = itemdb_exists(item_id)) &&
					  itemdb_available(item_id)) ? item_id : 0);
			} else {
				item_data = itemdb_search(item_id);
					}
		} else if ((item_data = itemdb_searchname(item_name)) != NULL) {
			item_id =
				(!battle_config.item_check ||
				 itemdb_available(item_data->nameid)) ? item_data->nameid : 0;
		}
		
		if (item_id > 0) {
			int loop = 1, get_count = number, i = 0;
			
			if (item_data->type == 4 || item_data->type == 5 ||
				item_data->type == 7 || item_data->type == 8) {
				loop = number;
				get_count = 1;
				if (item_data->type == 7) {
					identify = 1;
					refine = 0;
				}
				if (item_data->type == 8)
					refine = 0;
				if (refine > 10)
					refine = 10;
				} else {
				identify = 1;
				refine = attr = 0;
				}
			for (i = 0; i < loop; i++) {
				memset(&item_tmp, 0, sizeof(item_tmp));
				item_tmp.nameid = item_id;
				item_tmp.identify = identify;
				item_tmp.refine = refine;
				item_tmp.attribute = attr;
				item_tmp.card[0] = c1;
				item_tmp.card[1] = c2;
				item_tmp.card[2] = c3;
				item_tmp.card[3] = c4;
				if ((flag = pc_additem(sd, &item_tmp, get_count)))
					clif_additem(sd, 0, 0, flag);
			}
			clif_displaymessage(fd, msg_table[18]);
		} else {
			clif_displaymessage(fd, msg_table[19]);
		}
	} else {
		return -1;
			}
	return 0;
}

/*==========================================
 * 
 *------------------------------------------
 */
int
atcommand_itemreset(
	const int fd, struct map_session_data* sd,
	const char* command, const char* message)
{
	int i = 0;

	nullpo_retr(-1, sd);

	for (i = 0; i < MAX_INVENTORY; i++) {
		if (sd->status.inventory[i].amount &&
			sd->status.inventory[i].equip == 0)
			pc_delitem(sd, i, sd->status.inventory[i].amount, 0);
		}
	clif_displaymessage(fd, msg_table[20]);
	
	return 0;
}

/*==========================================
 * 
 *------------------------------------------
 */
int
atcommand_itemcheck(
	const int fd, struct map_session_data* sd,
	const char* command, const char* message)
{
			pc_checkitem(sd);
	
	return 0;
}

/*==========================================
 * 
 *------------------------------------------
 */
int
atcommand_baselevelup(
	const int fd, struct map_session_data* sd,
	const char* command, const char* message)
{
	int level = 0, i = 0;

	nullpo_retr(-1, sd);

	if (!message || !*message)
		return -1;
	
	level = atoi(message);
	if (level >= 1) {
		for (i = 1; i <= level; i++)
				sd->status.status_point += (sd->status.base_level + i + 14) / 5 ;
		sd->status.base_level += level;
		clif_updatestatus(sd, SP_BASELEVEL);
		clif_updatestatus(sd, SP_NEXTBASEEXP);
		clif_updatestatus(sd, SP_STATUSPOINT);
		status_calc_pc(sd, 0);
		pc_heal(sd, sd->status.max_hp, sd->status.max_sp);
		clif_misceffect(&sd->bl, 0);
		clif_displaymessage(fd, msg_table[21]);
	} else if (level < 0 && sd->status.base_level + level > 0) {
		sd->status.base_level += level;
		clif_updatestatus(sd, SP_BASELEVEL);
		clif_updatestatus(sd, SP_NEXTBASEEXP);
		status_calc_pc(sd, 0);
		clif_displaymessage(fd, msg_table[22]);
							}
	
	return 0;
}

/*==========================================
 * 
 *------------------------------------------
 */
int
atcommand_joblevelup(
	const int fd, struct map_session_data* sd,
	const char* command, const char* message)
{
	int up_level = 50, level = 0;
	//転生や養子の場合の元の職業を算出する
	struct pc_base_job s_class;

	nullpo_retr(-1, sd);

	s_class = pc_calc_base_job(sd->status.class);

	if (!message || !*message)
		return -1;
	level = atoi(message);
	if (s_class.job == 0)
		up_level -= 40;
	if ((s_class.job == 23) || (s_class.upper == 1 && s_class.type == 2)) //スパノビと転生二次職はJobレベルの最高が70
		up_level += 20;
	if (sd->status.job_level == up_level && level > 0) {
		clif_displaymessage(fd, msg_table[23]);
	} else if (level >= 1) {
		if (sd->status.job_level + level > up_level)
			level = up_level - sd->status.job_level;
		sd->status.job_level += level;
		clif_updatestatus(sd, SP_JOBLEVEL);
		clif_updatestatus(sd, SP_NEXTJOBEXP);
		sd->status.skill_point += level;
		clif_updatestatus(sd, SP_SKILLPOINT);
		status_calc_pc(sd, 0);
		clif_misceffect(&sd->bl, 1);
		clif_displaymessage(fd, msg_table[24]);
	} else if (level < 0 && sd->status.job_level + level > 0) {
		sd->status.job_level += level;
		clif_updatestatus(sd, SP_JOBLEVEL);
		clif_updatestatus(sd, SP_NEXTJOBEXP);
		status_calc_pc(sd, 0);
		clif_displaymessage(fd, msg_table[25]);
						}
	
	return 0;
}

/*==========================================
 * 
 *------------------------------------------
 */
int
atcommand_help(
	const int fd, struct map_session_data* sd,
	const char* command, const char* message)
{
	char buf[BUFSIZ];
	int display = 1;

	FILE* fp = fopen(help_txt, "r");
	if (fp != NULL) {
		int i = 0;
		clif_displaymessage(fd, msg_table[26]);
		while (fgets(buf, sizeof buf - 20, fp) != NULL) {
			for (i = 0; buf[i] != '\0'; i++) {
				if (buf[i] == '\r' || buf[i] == '\n') {
					buf[i] = '\0';
				}
			}
			if (message && *message && buf[0] == '@') {
				if (strstr(buf, message))
					display = 1;
				else
					display = 0;
			}
			if (display)
				clif_displaymessage(fd, buf);
		}
		fclose(fp);
	} else
		clif_displaymessage(fd, msg_table[27]);
	
	return 0;
}

/*==========================================
 * 
 *------------------------------------------
 */
int
atcommand_gm(
	const int fd, struct map_session_data* sd,
	const char* command, const char* message)
{
	char password[100];
	
	nullpo_retr(-1, sd);

	if (!message || !*message)
		return -1;
	
	sscanf(message, "%99[^\n]", password);
	if (sd->status.party_id)
		clif_displaymessage(fd, msg_table[28]);
	else if (sd->status.guild_id)
		clif_displaymessage(fd, msg_table[29]);
	else {
		if (sd->status.pet_id > 0 && sd->pd)
			intif_save_petdata(sd->status.account_id, &sd->pet);
				pc_makesavestatus(sd);
				chrif_save(sd);
				storage_storage_save(sd);
		clif_displaymessage(fd, msg_table[30]);
		chrif_changegm(sd->status.account_id, password, strlen(password) + 1);
		}

	return 0;
}

/*==========================================
 * 
 *------------------------------------------
 */
int
atcommand_pvpoff(
	const int fd, struct map_session_data* sd,
	const char* command, const char* message)
{
	struct map_session_data *pl_sd = NULL;
	int i = 0;
	
	nullpo_retr(-1, sd);

	if (map[sd->bl.m].flag.pvp) {
				map[sd->bl.m].flag.pvp = 0;
		clif_send0199(sd->bl.m, 0);
		for (i = 0; i < fd_max; i++) {	//人数分ループ
			if (session[i] && (pl_sd = session[i]->session_data) &&
				pl_sd->state.auth) {
				if (sd->bl.m == pl_sd->bl.m) {
					clif_pvpset(pl_sd, 0, 0, 2);
					if (pl_sd->pvp_timer != -1) {
						delete_timer(pl_sd->pvp_timer, pc_calc_pvprank_timer);
								pl_sd->pvp_timer = -1;
							}
						}
					}
				}
		clif_displaymessage(fd, msg_table[31]);
		}

	return 0;
}

/*==========================================
 * 
 *------------------------------------------
 */
int
atcommand_pvpon(
	const int fd, struct map_session_data* sd,
	const char* command, const char* message)
{
	struct map_session_data *pl_sd = NULL;
	int i = 0;
	
	nullpo_retr(-1, sd);

	if (!map[sd->bl.m].flag.pvp) {
				map[sd->bl.m].flag.pvp = 1;
		clif_send0199(sd->bl.m, 1);
		for (i = 0; i < fd_max; i++) {
			if (session[i] && (pl_sd = session[i]->session_data) &&
				pl_sd->state.auth) {
				if (sd->bl.m == pl_sd->bl.m && pl_sd->pvp_timer == -1) {
					pl_sd->pvp_timer = add_timer(gettick() + 200,
						pc_calc_pvprank_timer, pl_sd->bl.id, 0);
					pl_sd->pvp_rank = 0;
					pl_sd->pvp_lastusers = 0;
					pl_sd->pvp_point = 5;
						}
					}
				}
		clif_displaymessage(fd, msg_table[32]);
		}

	return 0;
}

/*==========================================
 * 
 *------------------------------------------
 */
int
atcommand_gvgoff(
	const int fd, struct map_session_data* sd,
	const char* command, const char* message)
{
	nullpo_retr(-1, sd);

	if (map[sd->bl.m].flag.gvg) {
				map[sd->bl.m].flag.gvg = 0;
		clif_send0199(sd->bl.m, 0);
		clif_displaymessage(fd, msg_table[33]);
		}
	return 0;
}

/*==========================================
 * 
 *------------------------------------------
 */
int
atcommand_gvgon(
	const int fd, struct map_session_data* sd,
	const char* command, const char* message)
{
	nullpo_retr(-1, sd);

	if (!map[sd->bl.m].flag.gvg) {
				map[sd->bl.m].flag.gvg = 1;
		clif_send0199(sd->bl.m, 3);
		clif_displaymessage(fd, msg_table[34]);
			}
	return 0;
}

/*==========================================
 * 
 *------------------------------------------
 */
int
atcommand_model(
	const int fd, struct map_session_data* sd,
	const char* command, const char* message)
{
	int hair_style = 0, hair_color = 0, cloth_color = 0;

	nullpo_retr(-1, sd);

	if (!message || !*message)
		return -1;
	if (sscanf(message, "%d %d %d", &hair_style, &hair_color, &cloth_color) < 1)
		return -1;
	if (hair_style >= MIN_HAIR_STYLE && hair_style < MAX_HAIR_STYLE &&
		hair_color >= MIN_HAIR_COLOR && hair_color < MAX_HAIR_COLOR &&
		cloth_color >= MIN_CLOTH_COLOR && cloth_color <= MAX_CLOTH_COLOR) {
				//服の色変更
		if (cloth_color != 0 &&
			sd->status.sex == 1 &&
			(sd->status.class == 12 ||  sd->status.class == 17)) {
					//服の色未実装職の判定
					clif_displaymessage(fd,msg_table[35]);
				} else {
			pc_changelook(sd, LOOK_HAIR, hair_style);
			pc_changelook(sd, LOOK_HAIR_COLOR, hair_color);
			pc_changelook(sd, LOOK_CLOTHES_COLOR, cloth_color);
			clif_displaymessage(fd, msg_table[36]);
				}
			} else {
		clif_displaymessage(fd, msg_table[37]);
		}

	return 0;
}

/*==========================================
 * 
 *------------------------------------------
 */
int
atcommand_go(
	const int fd, struct map_session_data* sd,
	const char* command, const char* message)
{
	int town = 0;
			struct { char map[16]; int x,y; } data[] = {
				{	"prontera.gat",	156, 191	},	//	0=プロンテラ
				{	"morocc.gat",	156,  93	},	//	1=モロク
				{	"geffen.gat",	119,  59	},	//	2=ゲフェン
				{	"payon.gat",	 89, 122	},	//	3=フェイヨン
				{	"alberta.gat",	192, 147	},	//	4=アルベルタ
				{	"izlude.gat",	128, 114	},	//	5=イズルード
				{	"aldebaran.gat",140, 131	},	//	6=アルデバラン
				{	"xmas.gat",		147, 134	},	//	7=ルティエ
				{	"comodo.gat",	209, 143	},	//	8=コモド
				{	"yuno.gat",		157,  51	},	//	9=ジュノー
				{	"amatsu.gat",	198,  84	},	//	10=アマツ
				{	"gonryun.gat",	160, 120	},	//	11=ゴンリュン
				{	"umbala.gat",	 89, 157	},	//	12=ウンバラ
				{	"niflheim.gat",	202, 177	},	//	13=ニブルヘルム
				{	"louyang.gat",	217,  40	},	//	14=洛陽
			};
	
	nullpo_retr(-1, sd);

	if (!message || !*message)
		return -1;
	
	town = atoi(message);
	if (town >= 0 && town < sizeof(data) / sizeof(data[0])) {
		pc_setpos((struct map_session_data*)sd,
			data[town].map, data[town].x, data[town].y, 3);
			} else {
		clif_displaymessage(fd, msg_table[38]);
		}
	return 0;
}

/*==========================================
 * 
 *------------------------------------------
 */
int
atcommand_monster(
	const int fd, struct map_session_data* sd,
	const char* command, const char* message)
{
	char name[100];
	char monster[100];
	int mob_id = 0;
	int number = 0;
	int x = 0;
	int y = 0;
	int count = 0;
	int i = 0;
	
	nullpo_retr(-1, sd);

	if (!message || !*message)
		return -1;
	if (sscanf(message, "%99s %99s %d %d %d", name, monster,
		&number, &x, &y) < 3)
		return -1;
	
	if ((mob_id = atoi(monster)) == 0)
		mob_id = mobdb_searchname(monster);
	if (number <= 0)
		number = 1;
	if (battle_config.etc_log)
		printf("%s monster=%s name=%s id=%d count=%d (%d,%d)\n",
			command, monster, name, mob_id, number, x, y);
	
	for (i = 0; i < number; i++) {
		int mx = 0, my = 0;
		if (x <= 0)
			mx = sd->bl.x + (rand() % 10 - 5);
		else
			mx = x;
		if (y <= 0)
			my = sd->bl.y + (rand() % 10 - 5);
		else
			my = y;
		count +=
			(mob_once_spawn((struct map_session_data*)sd,
				"this", mx, my, name, mob_id, 1, "") != 0) ?
			1 : 0;
				}
	if (count != 0) {
					clif_displaymessage(fd,msg_table[39]);
	} else {
					clif_displaymessage(fd,msg_table[40]);
				}
	
	return 0;
}

/*==========================================
 * 
 *------------------------------------------
 */
void
atcommand_killmonster_sub(
	const int fd, struct map_session_data* sd, const char* message,
	const int drop)
{
	int map_id = 0;
	
	nullpo_retv(sd);

	if (!message || !*message) {
		map_id = sd->bl.m;
	} else {
		char map_name[100];
		sscanf(message, "%99s", map_name);
		if (strstr(map_name, ".gat") == NULL && strlen(map_name) < 16) {
			strcat(map_name, ".gat");
			}
		if ((map_id = map_mapname2mapid(map_name)) < 0)
			map_id = sd->bl.m;
	}
	map_foreachinarea(atkillmonster_sub, map_id, 0, 0,
		map[map_id].xs, map[map_id].ys, BL_MOB, drop);
}

/*==========================================
 * 
 *------------------------------------------
 */
int
atcommand_killmonster(
	const int fd, struct map_session_data* sd,
	const char* command, const char* message)
{
	atcommand_killmonster_sub(fd, sd, message, 1);
	
	return 0;
}

/*==========================================
 * 
 *------------------------------------------
 */
int
atcommand_killmonster2(
	const int fd, struct map_session_data* sd,
	const char* command, const char* message)
{
	atcommand_killmonster_sub(fd, sd, message, 0);
	
	return 0;
}

/*==========================================
 * 
 *------------------------------------------
 */
int
atcommand_refine(
	const int fd, struct map_session_data* sd,
	const char* command, const char* message)
{
	int i = 0, position = 0, refine = 0, current_position = 0;
	struct map_session_data* p;
	
	nullpo_retr(-1, sd);

	p = (struct map_session_data*)sd;

	if (!message || !*message)
		return -1;
	
	if (sscanf(message, "%d %d", &position, &refine) >= 2) {
		for (i = 0; i < MAX_INVENTORY; i++) {
			if (sd->status.inventory[i].nameid &&	// 該当個所の装備を精錬する
			    (sd->status.inventory[i].equip & position ||
				(sd->status.inventory[i].equip && !position))) {
				if (refine <= 0 || refine > 10)
					refine = 1;
				if (sd->status.inventory[i].refine < 10) {
					p->status.inventory[i].refine += refine;
					if (sd->status.inventory[i].refine > 10)
						p->status.inventory[i].refine = 10;
					current_position = sd->status.inventory[i].equip;
					pc_unequipitem((struct map_session_data*)sd, i, 0);
					clif_refine(fd, (struct map_session_data*)sd,
						0, i, sd->status.inventory[i].refine);
					clif_delitem((struct map_session_data*)sd, i, 1);
					clif_additem((struct map_session_data*)sd, i, 1, 0);
					pc_equipitem((struct map_session_data*)sd, i,
						current_position);
					clif_misceffect((struct block_list*)&sd->bl, 3);
					}
				}
			}
		}
	
	return 0;
}

/*==========================================
 * 
 *------------------------------------------
 */
int
atcommand_produce(
	const int fd, struct map_session_data* sd,
	const char* command, const char* message)
{
	char item_name[100];
	int item_id = 0, attribute = 0, star = 0;
	int flag = 0;

	nullpo_retr(-1, sd);

	if (!message || !*message)
		return -1;
	
	if (sscanf(message, "%99s %d %d", item_name, &attribute, &star) > 0) {
		if ((item_id = atoi(item_name)) == 0) {
			struct item_data *item_data = itemdb_searchname(item_name);
			if (item_data)
				item_id = item_data->nameid;
		}
		if (itemdb_exists(item_id) &&
			(item_id <= 500 || item_id > 1099) &&
			(item_id < 4001 || item_id > 4148) &&
			(item_id < 7001 || item_id > 10019) &&
			itemdb_isequip(item_id)) {
					struct item tmp_item;
			if (attribute < MIN_ATTRIBUTE || attribute > MAX_ATTRIBUTE)
				attribute = ATTRIBUTE_NORMAL;
			if (star < MIN_STAR || star > MAX_STAR)
				star = 0;
			memset(&tmp_item, 0, sizeof tmp_item);
			tmp_item.nameid = item_id;
			tmp_item.amount = 1;
			tmp_item.identify = 1;
			tmp_item.card[0] = 0x00ff;
			tmp_item.card[1] = ((star * 5) << 8) + attribute;
			*((unsigned long *)(&tmp_item.card[2])) = sd->char_id;
			clif_produceeffect(sd, 0, item_id); // 製造エフェクトパケット
			clif_misceffect(&sd->bl, 3); // 他人にも成功を通知
			if ((flag = pc_additem(sd, &tmp_item, 1)))
				clif_additem(sd, 0, 0, flag);
		} else {
			if (battle_config.error_log)
				printf("@produce NOT WEAPON [%d]\n", item_id);
		}
		}

	return 0;
}

/*==========================================
 * 
 *------------------------------------------
 */
int
atcommand_memo(
	const int fd, struct map_session_data* sd,
	const char* command, const char* message)
{
	int position = 0;
	
	if (!message || !*message)
		return -1;
	
	position = atoi(message);
	if (position < MIN_PORTAL_MEMO || position > MAX_PORTAL_MEMO)
		position = MIN_PORTAL_MEMO;
	pc_memo(sd, position+MIN_PORTAL_MEMO);
	return 0;
}

/*==========================================
 * 
 *------------------------------------------
 */
int
atcommand_gat(
	const int fd, struct map_session_data* sd,
	const char* command, const char* message)
{
	char output[BUFSIZ];
	int y = 0;

	nullpo_retr(-1, sd);

	for (y = 2; y >= -2; y--) {
		snprintf(output, sizeof output,
			"%s (x= %d, y= %d) %02X %02X %02X %02X %02X",
			map[sd->bl.m].name, sd->bl.x - 2, sd->bl.y + y,
 			map_getcell(sd->bl.m, sd->bl.x - 2, sd->bl.y + y, CELL_GETTYPE),
 			map_getcell(sd->bl.m, sd->bl.x - 1, sd->bl.y + y, CELL_GETTYPE),
 			map_getcell(sd->bl.m, sd->bl.x,     sd->bl.y + y, CELL_GETTYPE),
 			map_getcell(sd->bl.m, sd->bl.x + 1, sd->bl.y + y, CELL_GETTYPE),
 			map_getcell(sd->bl.m, sd->bl.x + 2, sd->bl.y + y, CELL_GETTYPE));
		clif_displaymessage(fd, output);
			}
	
	return 0;
}

/*==========================================
 * 
 *------------------------------------------
 */
int
atcommand_packet(
	const int fd, struct map_session_data* sd,
	const char* command, const char* message)
{
	int x = 0, y = 0;
	
	nullpo_retr(-1, sd);

	if (!message || !*message)
		return -1;
	
	if (sscanf(message,"%d %d", &x, &y) < 2)
			return 1;
	clif_status_change(&sd->bl, x, y);
	return 0;
}

/*==========================================
 * 
 *------------------------------------------
 */
int
atcommand_statuspoint(
	const int fd, struct map_session_data* sd,
	const char* command, const char* message)
{
	int point = 0;
	
	nullpo_retr(-1, sd);

	if (!message || !*message)
		return -1;
	point = atoi(message);
	if (point > 0 || sd->status.status_point + point >= 0) {
		sd->status.status_point += point;
		clif_updatestatus(sd, SP_STATUSPOINT);
	} else {
		clif_displaymessage(fd, msg_table[41]);
		}

	return 0;
}

/*==========================================
 * 
 *------------------------------------------
 */
int
atcommand_skillpoint(
	const int fd, struct map_session_data* sd,
	const char* command, const char* message)
{
	int point = 0;

	nullpo_retr(-1, sd);

	if (!message || !*message)
		return -1;
	point = atoi(message);
	if (point > 0 || sd->status.skill_point + point >= 0) {
		sd->status.skill_point += point;
		clif_updatestatus(sd, SP_SKILLPOINT);
	} else {
		clif_displaymessage(fd, msg_table[41]);
		}

	return 0;
}

/*==========================================
 * 
 *------------------------------------------
 */
int
atcommand_zeny(
	const int fd, struct map_session_data* sd,
	const char* command, const char* message)
{
	int zeny = 0;
	
	nullpo_retr(-1, sd);

	if (!message || !*message)
		return -1;
	zeny = atoi(message);
	if (zeny > 0 || sd->status.zeny + zeny >= 0) {
		if (sd->status.zeny + zeny > MAX_ZENY)
			zeny = MAX_ZENY - sd->status.zeny;
		sd->status.zeny += zeny;
		clif_updatestatus(sd, SP_ZENY);
	} else {
		clif_displaymessage(fd, msg_table[41]);
		}
	return 0;
}

/*==========================================
 * 
 *------------------------------------------
 */
int
atcommand_param(
	const int fd, struct map_session_data* sd,
	const char* command, const char* message)
{
	int i = 0, value = 0, index = -1, new_value = 0;
	
	const char* param[] = {
		"@str", "@agi", "@vit", "@int", "@dex", "@luk", NULL
	};
	short* status[6];
	status[0] = &sd->status.str;
	status[1] = &sd->status.agi;
	status[2] = &sd->status.vit;
	status[3] = &sd->status.int_;
	status[4] = &sd->status.dex;
	status[5] = &sd->status.luk;
	
	if (!message || !*message)
		return -1;
	value = atoi(message);
	
	for (i = 0; param[i] != NULL; i++) {
		if (strcmpi(command, param[i]) == 0) {
			index = i;
			break;
		}
	}
	if (index < 0 || index > MAX_STATUS_TYPE)
		return -1;
	
	new_value = (int)(*status[index]) + value;
	if (new_value < 1)
		value = 1 - *status[index];
	if (new_value > battle_config.max_parameter)
		value = battle_config.max_parameter - *status[index];
	*status[index] += value;
	
	clif_updatestatus(sd, SP_STR + index);
	clif_updatestatus(sd, SP_USTR + index);
	status_calc_pc(sd, 0);
	clif_displaymessage(fd, msg_table[42]);
	
	return 0;
}

/*==========================================
 * 
 *------------------------------------------
 */
int
atcommand_guildlevelup(
	const int fd, struct map_session_data* sd,
	const char* command, const char* message)
{
	int level = 0;
	struct guild *guild_info = NULL;
	
	nullpo_retr(-1, sd);

	if (!message || !*message)
		return -1;
	level = atoi(message);
	if (sd->status.guild_id <= 0 ||
		(guild_info = guild_search(sd->status.guild_id)) == NULL) {
		clif_displaymessage(fd, msg_table[43]);
		return 0;
	}
	if (strcmp(sd->status.name, guild_info->master) != 0) {
		clif_displaymessage(fd, msg_table[44]);
		return 0;
		}

	if (guild_info->guild_lv + level >= 1 &&
		guild_info->guild_lv + level <= MAX_GUILDLEVEL)
		intif_guild_change_basicinfo(guild_info->guild_id,
			GBI_GUILDLV, &level, 2);
	else
		clif_displaymessage(fd, msg_table[45]);
	
	return 0;
}

/*==========================================
 * 
 *------------------------------------------
 */
int
atcommand_makepet(
	const int fd, struct map_session_data* sd,
	const char* command, const char* message)
{
	int id = 0, pet_id = 0;

	nullpo_retr(-1, sd);

	if (!message || !*message)
		return -1;
			
	id = atoi(message);
	pet_id = search_petDB_index(id, PET_CLASS);
	if (pet_id < 0)
		pet_id = search_petDB_index(id, PET_EGG);
	if (pet_id >= 0) {
		sd->catch_target_class = pet_db[pet_id].class;
		intif_create_pet(
			sd->status.account_id, sd->status.char_id,
			pet_db[pet_id].class, mob_db[pet_db[pet_id].class].lv,
			pet_db[pet_id].EggID, 0, pet_db[pet_id].intimate,
			100, 0, 1, pet_db[pet_id].jname);
	} else {
		return -1;
		}

	return 0;
}
/*==========================================
 *
 *------------------------------------------
 */
int atcommand_hatch(
	const int fd, struct map_session_data* sd,
	const char* command, const char* message)
{
	if (sd->status.pet_id <= 0)
		clif_sendegg(sd);
	else {
		clif_displaymessage(fd, "atcomand_hatch");
		return -1;
	}

	return 0;
}
/*==========================================
 * 
 *------------------------------------------
 */
int
atcommand_petfriendly(
	const int fd, struct map_session_data* sd,
	const char* command, const char* message)
{
	int friendly = 0;

	nullpo_retr(-1, sd);

	if (!message || !*message)
		return -1;

	friendly = atoi(message);
	if (sd->status.pet_id > 0 && sd->pd) {
		if (friendly >= 0 && friendly <= 1000) {
					int t = sd->pet.intimate;
			sd->pet.intimate = friendly;
					clif_send_petstatus(sd);
			if (battle_config.pet_status_support) {
				if ((sd->pet.intimate > 0 && t <= 0) ||
					(sd->pet.intimate <= 0 && t > 0)) {
					if (sd->bl.prev != NULL)
						status_calc_pc(sd, 0);
							else
						status_calc_pc(sd, 2);
					}
				}
		} else {
			return -1;
			}
		}

	return 0;
}

/*==========================================
 * 
 *------------------------------------------
 */
int
atcommand_pethungry(
	const int fd, struct map_session_data* sd,
	const char* command, const char* message)
{
	int hungry = 0;
	
	nullpo_retr(-1, sd);

	if (!message || !*message)
		return -1;
	
	hungry = atoi(message);
	if (sd->status.pet_id > 0 && sd->pd) {
		if (hungry >= 0 && hungry <= 100) {
			sd->pet.hungry = hungry;
					clif_send_petstatus(sd);
		} else {
			return -1;
				}
			}

	return 0;
}

/*==========================================
 * 
 *------------------------------------------
 */
int
atcommand_petrename(
	const int fd, struct map_session_data* sd,
	const char* command, const char* message)
{
	nullpo_retr(-1, sd);

	if (sd->status.pet_id > 0 && sd->pd) {
				sd->pet.rename_flag = 0;
		intif_save_petdata(sd->status.account_id, &sd->pet);
				clif_send_petstatus(sd);
			}

	return 0;
}
int atcommand_charpetrename(
	const int fd, struct map_session_data* sd,
	const char* command, const char* message)
{
	char character[100];
	struct map_session_data *pl_sd;

	memset(character, '\0', sizeof(character));

	if (!message || !*message || sscanf(message, "%99[^\n]", character) < 1) {
		clif_displaymessage(fd, "Please, enter a player name (usage: @charpetrename <char name>).");
		return -1;
	}

	if ((pl_sd = map_nick2sd(character)) != NULL) {
		if (pl_sd->status.pet_id > 0 && pl_sd->pd) {
			if (pl_sd->pet.rename_flag != 0) {
				pl_sd->pet.rename_flag = 0;
				intif_save_petdata(pl_sd->status.account_id, &pl_sd->pet);
				clif_send_petstatus(pl_sd);
				clif_displaymessage(fd, "atcommand charpetrename_This player can now rename his/her pet");
			} else {
				clif_displaymessage(fd, "atcommand charpetrename_This player can already rename his/her pet.");
				return -1;
			}
		} else {
			clif_displaymessage(fd, "atcommand charpetrename_Sorry, but this player has no pet.");
			return -1;
		}
	} else {
		clif_displaymessage(fd, "atcommand charpetrename_Character not found.");
		return -1;
	}

	return 0;
}
/*==========================================
 * 
 *------------------------------------------
 */
int
atcommand_recall(
	const int fd, struct map_session_data* sd,
	const char* command, const char* message)
{
	char character[100];
//	struct map_session_data *pl_sd = NULL;
	
	nullpo_retr(-1, sd);

	if (!message || !*message)
		return -1;
	
	memset(character, '\0', sizeof character);
	if(sscanf(message, "%99[^\n]", character) < 1)
		return -1;
	if(strncmp(sd->status.name,character,24)==0)
		return -1;

	intif_charmovereq(sd,character,1);

/*
	if ((pl_sd = map_nick2sd(character)) != NULL) {
		if (pc_isGM(sd) > pc_isGM(pl_sd)) {
			char output[200];
					pc_setpos(pl_sd, sd->mapname, sd->bl.x, sd->bl.y, 2);
			snprintf(output, sizeof output, msg_table[46], character);
			clif_displaymessage(fd, output);
				}
	} else {
		clif_displaymessage(fd, msg_table[47]);
	}
*/
	return 0;
}
/*==========================================
 * recallを接続者全員にかける
 *------------------------------------------
 */
int atcommand_recallall(
	const int fd, struct map_session_data* sd,
	const char* command, const char* message)
{
	struct map_session_data *pl_sd;
	int i;
	char output[200];

	memset(output, '\0', sizeof(output));

	for (i = 0; i < fd_max; i++)
		if (session[i] && (pl_sd = session[i]->session_data) && pl_sd->state.auth
			&& sd->status.account_id != pl_sd->status.account_id
				&& pc_isGM(sd) >= pc_isGM(pl_sd) && pl_sd)
					pc_setpos(pl_sd, sd->mapname, sd->bl.x, sd->bl.y, 2);
//					intif_charmovereq(sd,pl_sd->status.name,1);
	clif_displaymessage(fd, msg_table[105]);

	return 0;
}
/*==========================================
 * Recall online characters of a guild to your location
 *------------------------------------------
 */
int atcommand_recallguild(
	const int fd, struct map_session_data* sd,
	const char* command, const char* message)
{
	struct map_session_data *pl_sd;
	int i;
	char guild_name[100];
	char output[200];
	struct guild *g;

	memset(guild_name, '\0', sizeof(guild_name));
	memset(output, '\0', sizeof(output));

	if (!message || !*message || sscanf(message, "%99[^\n]", guild_name) < 1)
		return -1;

	if ((g = guild_searchname(guild_name)) != NULL ||
	    (g = guild_search(atoi(message))) != NULL) {
		for (i = 0; i < fd_max; i++)
			if (session[i] && (pl_sd = session[i]->session_data) && pl_sd->state.auth
			    && sd->status.account_id != pl_sd->status.account_id
			    && pl_sd->status.guild_id == g->guild_id)
					pc_setpos(pl_sd, sd->mapname, sd->bl.x, sd->bl.y, 2);
		sprintf(output, msg_table[106], g->name);
		clif_displaymessage(fd, output);
	} else {
		clif_displaymessage(fd, msg_table[107]);
		return -1;
	}

	return 0;
}

/*==========================================
 * Recall online characters of a party to your location
 *------------------------------------------
 */
int atcommand_recallparty(
	const int fd, struct map_session_data* sd,
	const char* command, const char* message)
{
	int i;
	struct map_session_data *pl_sd;
	char party_name[100];
	char output[200];
	struct party *p;

	memset(party_name, '\0', sizeof(party_name));
	memset(output, '\0', sizeof(output));

	if (!message || !*message || sscanf(message, "%99[^\n]", party_name) < 1)
		return -1;

	if ((p = party_searchname(party_name)) != NULL ||
	    (p = party_search(atoi(message))) != NULL) {
		for (i = 0; i < fd_max; i++)
			if (session[i] && (pl_sd = session[i]->session_data) && pl_sd->state.auth
				&& sd->status.account_id != pl_sd->status.account_id
				&& pl_sd->status.party_id == p->party_id)
					pc_setpos(pl_sd, sd->mapname, sd->bl.x, sd->bl.y, 2);
		sprintf(output, msg_table[108], p->name);
		clif_displaymessage(fd, output);
	} else {
		clif_displaymessage(fd, msg_table[109]);
		return -1;
	}

	return 0;
}
/*==========================================
 * 対象キャラクターを転職させる upper指定で転生や養子も可能
 *------------------------------------------
 */
int
atcommand_character_job(
	const int fd, struct map_session_data* sd,
	const char* command, const char* message)
{
	char character[100];
	struct map_session_data* pl_sd = NULL;
	int job = 0, upper = -1;
	
	if (!message || !*message)
		return -1;
	
	memset(character, '\0', sizeof character);
	if (sscanf(message, "%d %d %99[^\n]", &job, &upper, character) < 3){ //upper指定してある
		upper = -1;
		if (sscanf(message, "%d %99[^\n]", &job, character) < 2) //upper指定してない上に何か足りない
			return -1;
	}
	if ((pl_sd = map_nick2sd(character)) != NULL) {
		if (pc_isGM(sd) > pc_isGM(pl_sd)) {
			if ((job >= 0 && job < MAX_PC_CLASS)) {
				pc_jobchange(pl_sd, job, upper);
				clif_displaymessage(fd, msg_table[48]);
			} else {
				clif_displaymessage(fd, msg_table[49]);
				}
			}
	} else {
		clif_displaymessage(fd, msg_table[50]);
	}
	
	return 0;
}

/*==========================================
 * 
 *------------------------------------------
 */
int
atcommand_revive(
	const int fd, struct map_session_data* sd,
	const char* command, const char* message)
{
	char character[100];
	struct map_session_data *pl_sd = NULL;
	
	if (!message || !*message)
		return -1;
	memset(character, '\0', sizeof character);
	sscanf(message, "%99[^\n]", character);
	if ((pl_sd = map_nick2sd(character)) != NULL) {
		pl_sd->status.hp = pl_sd->status.max_hp;
				pc_setstand(pl_sd);
		if (battle_config.pc_invincible_time > 0)
			pc_setinvincibletimer(sd, battle_config.pc_invincible_time);
		clif_updatestatus(pl_sd, SP_HP);
		clif_updatestatus(pl_sd, SP_SP);
		clif_resurrection(&pl_sd->bl, 1);
		clif_displaymessage(fd, msg_table[51]);
	} else {
		clif_displaymessage(fd, msg_table[52]);
			}
	
	return 0;
}

/*==========================================
 * 
 *------------------------------------------
 */
int
atcommand_character_stats(
	const int fd, struct map_session_data* sd,
	const char* command, const char* message)
{
	char character[100];
	struct map_session_data *pl_sd = NULL;
	
	nullpo_retr(-1, sd);

	if (!message || !*message)
		return -1;
	memset(character, '\0', sizeof character);
	sscanf(message, "%99[^\n]", character);
	if ((pl_sd = map_nick2sd(character)) != NULL) {
		char output[200];
		int i = 0;
		struct {
			const char* format;
			int value;
		} output_table[14];
		
		output_table[0].format  = "Base Level - %d";	output_table[0].value = pl_sd->status.base_level;
		output_table[1].format  = "Job Level - %d";		output_table[1].value = pl_sd->status.job_level;
		output_table[2].format  = "Hp - %d";			output_table[2].value = pl_sd->status.hp;
		output_table[3].format  = "MaxHp - %d";			output_table[3].value = pl_sd->status.max_hp;
		output_table[4].format  = "Sp - %d";			output_table[4].value = pl_sd->status.sp;
		output_table[5].format  = "MaxSp - %d";			output_table[5].value = pl_sd->status.max_sp;
		output_table[6].format  = "Str - %d";			output_table[6].value = pl_sd->status.str;
		output_table[7].format  = "Agi - %d";			output_table[7].value = pl_sd->status.agi;
		output_table[8].format  = "Vit - %d";			output_table[8].value = pl_sd->status.vit;
		output_table[9].format  = "Int - %d";			output_table[9].value = pl_sd->status.int_;
		output_table[10].format = "Dex - %d";			output_table[10].value = pl_sd->status.dex;
		output_table[11].format = "Luk - %d";			output_table[11].value = pl_sd->status.luk;
		output_table[12].format = "Zeny - %d";			output_table[12].value = pl_sd->status.zeny;
		output_table[13].format =  NULL;				output_table[13].value = 0;

		snprintf(output, sizeof output, msg_table[53], pl_sd->status.name);
		clif_displaymessage(fd, output);
		for (i = 0; output_table[i].format != NULL; i++) {
			snprintf(output, sizeof output,
				output_table[i].format, output_table[i].value);
			clif_displaymessage(fd, output);
		}
	} else {
				clif_displaymessage(fd,msg_table[54]);
			}
	
	return 0;
}

/*==========================================
 * 
 *------------------------------------------
 */
int
atcommand_character_option(
	const int fd, struct map_session_data* sd,
	const char* command, const char* message)
{
	char character[100];
	int opt1 = 0, opt2 = 0, opt3 = 0;
	struct map_session_data* pl_sd = NULL;
	
	if (!message || !*message)
		return -1;
	memset(character, '\0', sizeof character);
	if (sscanf(message, "%d %d %d %99[^\n]",
			&opt1, &opt2, &opt3, character) < 4)
		return -1;
	if ((pl_sd = map_nick2sd(character)) != NULL) {
		if (pc_isGM(sd) > pc_isGM(pl_sd)) {
			pl_sd->opt1 = opt1;
			pl_sd->opt2 = opt2;
			pl_sd->status.option = opt3;
					clif_changeoption(&pl_sd->bl);
			clif_displaymessage(fd, msg_table[55]);
			}
	} else {
		clif_displaymessage(fd, msg_table[56]);
				}
	
	return 0;
}

/*==========================================
 * 
 *------------------------------------------
 */
int
atcommand_character_save(
	const int fd, struct map_session_data* sd,
	const char* command, const char* message)
{
	char map_name[100];
	char character[100];
	struct map_session_data* pl_sd = NULL;
	int x = 0, y = 0;
	
	if (!message || !*message)
		return -1;
	memset(character, '\0', sizeof character);
	if (sscanf(message, "%99s %d %d %99[^\n]", map_name, &x, &y, character) < 4)
		return -1;
	if ((pl_sd = map_nick2sd(character)) != NULL) {
		if (pc_isGM(sd) > pc_isGM(pl_sd)) {
			pc_setsavepoint(pl_sd, map_name, x, y);
			clif_displaymessage(fd, msg_table[57]);
			}
	} else {
		clif_displaymessage(fd, msg_table[58]);
		}
	
	return 0;
}

/*==========================================
 * 
 *------------------------------------------
 */
int
atcommand_night(
	const int fd, struct map_session_data* sd,
	const char* command, const char* message)
{
	struct map_session_data *pl_sd = NULL;
	int i = 0;
	for(i = 0; i < fd_max; i++) {
		if (session[i] && (pl_sd = session[i]->session_data) &&
			pl_sd->state.auth) {
			pl_sd->opt2 |= STATE_BLIND;
					clif_changeoption(&pl_sd->bl);
			clif_displaymessage(pl_sd->fd, msg_table[59]);
			}
		}
	
	return 0;
}

/*==========================================
 * 
 *------------------------------------------
 */
int
atcommand_day(
	const int fd, struct map_session_data* sd,
	const char* command, const char* message)
{
	struct map_session_data *pl_sd = NULL;
	int i = 0;
	for(i = 0; i < fd_max; i++) {
		if (session[i] && (pl_sd = session[i]->session_data) &&
			pl_sd->state.auth) {
			pl_sd->opt2 &= ~STATE_BLIND;
					clif_changeoption(&pl_sd->bl);
			clif_displaymessage(pl_sd->fd, msg_table[60]);
				}
			}
	
	return 0;
}

/*==========================================
 * 
 *------------------------------------------
 */
int
atcommand_doom(
	const int fd, struct map_session_data* sd,
	const char* command, const char* message)
{
	struct map_session_data *pl_sd = NULL;
	int i = 0;
	for(i = 0; i < fd_max; i++) {
		if (session[i] && (pl_sd = session[i]->session_data) &&
			pl_sd->state.auth) {
			pl_sd = session[i]->session_data;
			if (pc_isGM(sd) > pc_isGM(pl_sd)) {
				pc_damage(NULL, pl_sd, pl_sd->status.hp + 1);
				clif_displaymessage(pl_sd->fd, msg_table[61]);
		}
			}
		}
	
	return 0;
}

/*==========================================
 * 
 *------------------------------------------
 */
int
atcommand_doommap(
	const int fd, struct map_session_data* sd,
	const char* command, const char* message)
{
	struct map_session_data *pl_sd = NULL;
	int i = 0;

	nullpo_retr(-1, sd);

	for (i = 0; i < fd_max; i++) {
		if (session[i] && (pl_sd = session[i]->session_data) &&
			pl_sd->state.auth && sd->bl.m == pl_sd->bl.m &&
			pc_isGM(sd) > pc_isGM(pl_sd)) {
					pc_damage(NULL, pl_sd, pl_sd->status.hp + 1);
					clif_displaymessage(pl_sd->fd, msg_table[61]);
	}
				}
	clif_displaymessage(fd, msg_table[62]);
	
	return 0;
}

/*==========================================
 * 
 *------------------------------------------
 */
static void
atcommand_raise_sub(struct map_session_data* sd)
{
	if (sd && sd->state.auth && pc_isdead(sd)) {
		sd->status.hp = sd->status.max_hp;
		sd->status.sp = sd->status.max_sp;
		pc_setstand(sd);
		clif_updatestatus(sd, SP_HP);
		clif_updatestatus(sd, SP_SP);
		clif_resurrection(&sd->bl, 1);
		clif_displaymessage(sd->fd, msg_table[63]);
			}
}

/*==========================================
 * 
 *------------------------------------------
 */
int
atcommand_raise(
	const int fd, struct map_session_data* sd,
	const char* command, const char* message)
{
	int i = 0;
	for (i = 0; i < fd_max; i++) {
		if (session[i])
			atcommand_raise_sub(session[i]->session_data);
		}
	clif_displaymessage(fd, msg_table[64]);
	
	return 0;
}

/*==========================================
 * 
 *------------------------------------------
 */
int
atcommand_raisemap(
	const int fd, struct map_session_data* sd,
	const char* command, const char* message)
{
	struct map_session_data *pl_sd = NULL;
	int i = 0;

	nullpo_retr(-1, sd);

	for (i = 0; i < fd_max; i++) {
		if (session[i] && (pl_sd = session[i]->session_data) &&
			pl_sd->state.auth && pc_isdead(pl_sd) && sd->bl.m == pl_sd->bl.m)
				atcommand_raise_sub(pl_sd);
	}
	clif_displaymessage(fd, msg_table[64]);
	
	return 0;
}

/*==========================================
 * atcommand_character_baselevel @charbaselvlで対象キャラのレベルを上げる
 *------------------------------------------
*/
int
atcommand_character_baselevel(
	const int fd, struct map_session_data* sd,
	const char* command, const char* message)
{
	struct map_session_data *pl_sd = NULL;
	char character[100];
	int level = 0, i = 0;
	
	if (!message || !*message) //messageが空ならエラーを返して終了
		return -1; //エラーを返して終了
	memset(character, '\0', sizeof character);
	if (sscanf(message, "%d %99[^\n]", &level, character) < 2) //messageにLevelとキャラ名が無ければ
		return -1; //エラーを返して終了
	if ((pl_sd = map_nick2sd(character)) != NULL) { //該当名のキャラが存在する
		if (pc_isGM(sd) > pc_isGM(pl_sd)) { //対象キャラのGMレベルが自分より小さい
			if (level >= 1) { //上げるレベルが１より大きい
				for (i = 1; i <= level; i++) //入力されたレベル回ステータスポイントを追加する
					pl_sd->status.status_point += (pl_sd->status.base_level + i + 14) / 5 ;
				pl_sd->status.base_level += level; //対象キャラのベースレベルを上げる
				clif_updatestatus(pl_sd, SP_BASELEVEL); //クライアントに上げたベースレベルを送る
				clif_updatestatus(pl_sd, SP_NEXTBASEEXP); //クライアントに次のベースレベルアップまでの必要経験値を送る
				clif_updatestatus(pl_sd, SP_STATUSPOINT); //クライアントにステータスポイントを送る
				status_calc_pc(pl_sd, 0); //ステータスを計算しなおす
				pc_heal(pl_sd, pl_sd->status.max_hp, pl_sd->status.max_sp); //HPとSPを完全回復させる
				clif_misceffect(&pl_sd->bl, 0); //ベースレベルアップエフェクトの送信
				clif_displaymessage(fd, msg_table[65]); //レベルを上げたメッセージを表示する
			} else if (level < 0 && pl_sd->status.base_level + level > 0) { //対象キャラのレベルがマイナスで変化結果が0以上なら
				pl_sd->status.base_level += level; //対象キャラのレベルを下げる
				clif_updatestatus(pl_sd, SP_BASELEVEL); //クライアントに下げたベースレベルを送る
				clif_updatestatus(pl_sd, SP_NEXTBASEEXP); //クライアントに次のベースレベルアップまでの必要経験値を送る
				status_calc_pc(pl_sd, 0); //ステータスを計算しなおす
				clif_displaymessage(fd, msg_table[66]); //レベルを下げたメッセージを表示する
			}
		}
	}
	return 0; //正常終了
}

/*==========================================
 * atcommand_character_joblevel @charjoblvlで対象キャラのJobレベルを上げる
 *------------------------------------------
 */
int
atcommand_character_joblevel(
	const int fd, struct map_session_data* sd,
	const char* command, const char* message)
{
	struct map_session_data *pl_sd = NULL;
	char character[100];
	int max_level = 50, level = 0;
	//転生や養子の場合の元の職業を算出する
	struct pc_base_job pl_s_class;
	
	if (!message || !*message)
		return -1;
	memset(character, '\0', sizeof character);
	if (sscanf(message, "%d %99[^\n]", &level, character) < 2)
		return -1;
	if ((pl_sd = map_nick2sd(character)) != NULL) {
		pl_s_class = pc_calc_base_job(pl_sd->status.class);
		if (pc_isGM(sd) > pc_isGM(pl_sd)) {
			if (pl_s_class.job == 0)
				max_level -= 40;
			if ((pl_s_class.job == 23) || (pl_s_class.upper == 1 && pl_s_class.type == 2)) //スパノビと転生職はJobレベルの最高が70
				max_level += 20;
			if (pl_sd->status.job_level == max_level && level > 0) {
				clif_displaymessage(fd, msg_table[67]);
			} else if (level >= 1) {
				if (pl_sd->status.job_level + level > max_level)
					level = max_level - pl_sd->status.job_level;
				pl_sd->status.job_level += level;
				clif_updatestatus(pl_sd, SP_JOBLEVEL);
				clif_updatestatus(pl_sd, SP_NEXTJOBEXP);
				pl_sd->status.skill_point += level;
				clif_updatestatus(pl_sd, SP_SKILLPOINT);
				status_calc_pc(pl_sd, 0);
				clif_misceffect(&pl_sd->bl, 1);
				clif_displaymessage(fd, msg_table[68]);
			} else if (level < 0 && sd->status.job_level + level > 0) {
				pl_sd->status.job_level += level;
				clif_updatestatus(pl_sd, SP_JOBLEVEL);
				clif_updatestatus(pl_sd, SP_NEXTJOBEXP);
				status_calc_pc(pl_sd, 0);
				clif_displaymessage(fd, msg_table[69]);
				}
			}
		}

	return 0;
}

/*==========================================
 * 
 *------------------------------------------
 */
int
atcommand_kick(
	const int fd, struct map_session_data* sd,
	const char* command, const char* message)
{
	struct map_session_data *pl_sd = NULL;
	char character[100];
	
	if (!message || !*message)
		return -1;
	memset(character, '\0', sizeof character);
	if (sscanf(message, "%99[^\n]", character) < 1)
		return -1;
	if ((pl_sd = map_nick2sd(character)) == NULL)
		return -1;
	if (pc_isGM(sd) > pc_isGM(pl_sd))
		clif_GM_kick(sd, pl_sd, 1);
			else
		clif_GM_kickack(sd, 0);
	return 0;
}

/*==========================================
 * 
 *------------------------------------------
 */
int
atcommand_kickall(
	const int fd, struct map_session_data* sd,
	const char* command, const char* message)
{
	struct map_session_data *pl_sd = NULL;
	int i = 0;

	nullpo_retr(-1, sd);

	for (i = 0; i < fd_max; i++) {
		if (session[i] &&
			(pl_sd = session[i]->session_data) && pl_sd->state.auth) {
			if (sd->status.account_id != pl_sd->status.account_id)
				clif_GM_kick(sd, pl_sd, 0);
			}
		}
	clif_GM_kick(sd, sd, 0);
	
	return 0;
}

/*==========================================
 * 
 *------------------------------------------
 */
int
atcommand_allskill(
	const int fd, struct map_session_data* sd,
	const char* command, const char* message)
{
			pc_allskillup(sd);
	clif_displaymessage(fd, msg_table[76]);
	return 0;
}

/*==========================================
 * 
 *------------------------------------------
 */
int
atcommand_questskill(
	const int fd, struct map_session_data* sd,
	const char* command, const char* message)
{
	int skill_id = 0;
	if (!message || !*message)
		return -1;
	skill_id = atoi(message);
	if (skill_get_inf2(skill_id) & 0x01) {
		pc_skill(sd, skill_id, 1, 0);
		clif_displaymessage(fd, msg_table[70]);
	}
	return 0;
}
/*==========================================
 *
 *------------------------------------------
 */
int atcommand_charquestskill(
	const int fd, struct map_session_data* sd,
	const char* command, const char* message)
{
	char character[100];
	char output[100];
	struct map_session_data *pl_sd;
	int skill_id = 0;

	memset(character, '\0', sizeof(character));

	if (!message || !*message || sscanf(message, "%d %99[^\n]", &skill_id, character) < 2 || skill_id < 0)
		return -1;

	if (skill_id >= 0 && skill_id < MAX_SKILL_DB) {
		if(skill_get_inf2(skill_id) & 0x01
		   && (pl_sd = map_nick2sd(character)) != NULL
		   && pc_checkskill(pl_sd, skill_id) == 0){
				pc_skill(pl_sd, skill_id, 1, 0);
				sprintf(output,msg_table[110], pl_sd->status.name);
				clif_displaymessage(fd, output);
		} else {
			clif_displaymessage(fd, msg_table[3]);
			return -1;
		}
	}
	else
		return -1;
	return 0;
}
/*==========================================
 * 
 *------------------------------------------
 */
int
atcommand_lostskill(
	const int fd, struct map_session_data* sd,
	const char* command, const char* message)
{
	int skill_id = 0;

	nullpo_retr(-1, sd);

	if (!message || !*message)
		return -1;
	skill_id = atoi(message);
	if (skill_id > 0 && skill_id < MAX_SKILL &&
		pc_checkskill(sd, skill_id) == 0)
		return -1;
	sd->status.skill[skill_id].lv = 0;
	sd->status.skill[skill_id].flag = 0;
				clif_skillinfoblock(sd);
	clif_displaymessage(fd, msg_table[71]);

	return 0;
}
/*==========================================
 *
 *------------------------------------------
 */
int atcommand_charlostskill(
	const int fd, struct map_session_data* sd,
	const char* command, const char* message)
{
	char character[100];
	char output[100];
	struct map_session_data *pl_sd;
	int skill_id = 0;

	memset(character, '\0', sizeof(character));

	if (!message || !*message || sscanf(message, "%d %99[^\n]", &skill_id, character) < 2 || skill_id < 0)
		return -1;

	if (skill_id >= 0 && skill_id < MAX_SKILL) {
		if (skill_get_inf2(skill_id) & 0x01
			&& (pl_sd = map_nick2sd(character)) != NULL
			&& pc_checkskill(pl_sd, skill_id) > 0) {
				pl_sd->status.skill[skill_id].lv = 0;
				pl_sd->status.skill[skill_id].flag = 0;
				clif_skillinfoblock(pl_sd);
				sprintf(output,msg_table[111],pl_sd->status.name);
				clif_displaymessage(fd, output);
		} else {
			clif_displaymessage(fd, msg_table[3]);
			return -1;
		}
	} else
		return -1;
	return 0;
}
/*==========================================
 * 
 *------------------------------------------
 */
int
atcommand_spiritball(
	const int fd, struct map_session_data* sd,
	const char* command, const char* message)
{
	int number = 0;

	nullpo_retr(-1, sd);

	if (!message || !*message)
		return -1;
	number = atoi(message);
	if (number >= 0 && number <= 0x7FFF) {
		if (sd->spiritball > 0)
			pc_delspiritball(sd, sd->spiritball, 1);
		sd->spiritball = number;
		clif_spiritball(sd);
	}

	return 0;
}

/*==========================================
 * 
 *------------------------------------------
 */
int
atcommand_party(
	const int fd, struct map_session_data* sd,
	const char* command, const char* message)
{
	char party[100];
	if (!message || !*message)
		return -1;
	if (sscanf(message, "%99[^\n]", party))
		party_create(sd, party);
	return 0;
}

/*==========================================
 * 
 *------------------------------------------
 */
int
atcommand_guild(
	const int fd, struct map_session_data* sd,
	const char* command, const char* message)
{
	char guild[100];
	int prev = 0;
	if (!message || !*message)
		return -1;
	if (sscanf(message, "%99[^\n]", guild) == 0)
		return -1;

	prev = battle_config.guild_emperium_check;
	battle_config.guild_emperium_check = 0;
	guild_create(sd, guild);
	battle_config.guild_emperium_check = prev;
	return 0;
}
	
/*==========================================
 * 
 *------------------------------------------
 */
int
atcommand_agitstart(
	const int fd, struct map_session_data* sd,
	const char* command, const char* message)
{
	if (agit_flag == 1) {
		clif_displaymessage(fd, msg_table[73]);
			return 1;
		}
	agit_flag = 1;
	guild_agit_start();
	clif_displaymessage(fd, msg_table[72]);
	return 0;
}

/*==========================================
 * 
 *------------------------------------------
 */
int
atcommand_agitend(
	const int fd, struct map_session_data* sd,
	const char* command, const char* message)
{
	if (agit_flag == 0) {
		clif_displaymessage(fd, msg_table[75]);
		return 1;
	}
	agit_flag = 0;
	guild_agit_end();
	clif_displaymessage(fd, msg_table[74]);
	return 0;
}
/*==========================================
 *itemDBのリロード
 *------------------------------------------
 */
int atcommand_reloaditemdb(
	const int fd, struct map_session_data* sd,
	const char* command, const char* message)
{
	do_init_itemdb();
	clif_displaymessage(fd, msg_table[89]);

	return 0;
}

/*==========================================
 *MOBDBのリロード
 *------------------------------------------
 */
int atcommand_reloadmobdb(
	const int fd, struct map_session_data* sd,
	const char* command, const char* message)
{
	mob_reload();
	clif_displaymessage(fd, msg_table[90]);

	return 0;
}

/*==========================================
 *スキルDBのリロード
 *------------------------------------------
 */
int atcommand_reloadskilldb(
	const int fd, struct map_session_data* sd,
	const char* command, const char* message)
{
	skill_reload();
	clif_displaymessage(fd, msg_table[91]); //

	return 0;
}

/*==========================================
 * @mapexitでマップサーバーを終了させる
 *------------------------------------------
 */
int
atcommand_mapexit(
	const int fd, struct map_session_data* sd,
	const char* command, const char* message)
{
	struct map_session_data *pl_sd = NULL;
	int i = 0;

	nullpo_retr(-1, sd);

	for (i = 0; i < fd_max; i++) {
		if (session[i] &&
			(pl_sd = session[i]->session_data) && pl_sd->state.auth) {
			if (sd->status.account_id != pl_sd->status.account_id)
				clif_GM_kick(sd, pl_sd, 0);
			}
		}
	clif_GM_kick(sd, sd, 0);
	
	exit(1);
}

int
atcommand_idsearch(
	const int fd, struct map_session_data* sd,
	const char* command, const char* message)
{
	char item_name[100];
	char output[100];
	unsigned int i,j,k,len,flag,match=0;
	struct item_data *item=NULL;
	if (sscanf(message, "%99s", item_name) < 0)
		return -1;
	len=strlen(item_name);
	snprintf(output,sizeof output,msg_table[77],item_name);
	clif_displaymessage(fd,output);
	for(i=0;i<20000;i++){
		if((item=itemdb_exists(i))==NULL)
			continue;
		//文字列関数はよくわからないので自前で文字列検索_|￣|○for使いすぎ
		for(j=0;j<strlen(item->jname);j++){
			if(item->jname[j]==item_name[0]){
				//1文字目が合ってるなら2文字目以降を検索。
				flag=1;
				for(k=1;k<len;k++){
					if(item->jname[j+k]==item_name[k])
						flag++;
				}
				if(flag==len){
					match++;
					snprintf(output, sizeof output,msg_table[78],item->jname,item->nameid);
					clif_displaymessage(fd,output);
				}
			}
		}
	}
	snprintf(output, sizeof output,msg_table[79],match);
	clif_displaymessage(fd,output);
	return 0;
}
/*==========================================
 * 
 *------------------------------------------
 */
int
atcommand_itemidentify(
	const int fd, struct map_session_data* sd,
	const char* command, const char* message)
{
	int i = 0;

	nullpo_retr(-1, sd);

	for (i = 0; i < MAX_INVENTORY; i++) {
		if (sd->status.inventory[i].amount &&
			sd->status.inventory[i].identify == 0)
			pc_item_identify(sd, i);
		}
	clif_displaymessage(fd, msg_table[80]);
	
	return 0;
}
static int atshuffle_sub(struct block_list *bl,va_list ap)
{

	nullpo_retr(0, bl);

	mob_warp((struct mob_data *)bl,bl->m,-1,-1,3);

	return 0;
}

int
atcommand_shuffle(
	const int fd, struct map_session_data* sd,
	const char* command, const char* message)
{
	struct map_session_data *pl_sd = NULL;
	int i = 0,mode = 0;

	nullpo_retr(-1, sd);

	if (sscanf(message, "%d", &mode) < 0)
		return -1;

	//PCのシャッフル
	if(!mode){
		for (i = 0; i < fd_max; i++) {
			if (session[i] && (pl_sd = session[i]->session_data) &&
				pl_sd->state.auth && sd->bl.m == pl_sd->bl.m &&
				pc_isGM(sd) > pc_isGM(pl_sd))
						pc_randomwarp(pl_sd,3);
		}
	//MOBのシャッフル
	}else if(mode == 1){
		map_foreachinarea(atshuffle_sub, sd->bl.m, 0, 0,
			map[sd->bl.m].xs, map[sd->bl.m].ys, BL_MOB);

	}else {
		return -1;
	}
	clif_displaymessage(fd, msg_table[81]);
	return 0;
}

int
atcommand_maintenance(
	const int fd, struct map_session_data* sd,
	const char* command, const char* message)
{
	int maintenance = 0;

	nullpo_retr(-1, sd);

	if (sscanf(message, "%d", &maintenance) < 0)
		return -1;

	chrif_maintenance(maintenance);

	return 0;
}
int
atcommand_misceffect(
	const int fd, struct map_session_data* sd,
	const char* command, const char* message)
{
	int effno = 0;

	nullpo_retr(-1, sd);

	if (sscanf(message, "%d", &effno) < 0)
		return -1;

	clif_misceffect2(&sd->bl,effno);

	return 0;
}
/*==========================================
 * 
 *------------------------------------------
 */
int
atcommand_summon(
	const int fd, struct map_session_data* sd,
	const char* command, const char* message)
{
	char name[100];
	int mob_id = 0;
	int x = 0;
	int y = 0;
	int id = 0;
	struct mob_data *md;
	unsigned int tick=gettick();
	
	nullpo_retr(-1, sd);

	if (!message || !*message)
		return -1;
	if (sscanf(message, "%99s", name) < 1)
		return -1;
	
	if ((mob_id = atoi(name)) == 0)
		mob_id = mobdb_searchname(name);
	if(mob_id == 0)
		return -1;

	x = sd->bl.x + (rand() % 10 - 5);
	y = sd->bl.y + (rand() % 10 - 5);

	id = mob_once_spawn(sd,"this", x, y, "--ja--", mob_id, 1, "");
	if((md=(struct mob_data *)map_id2bl(id))){
		md->master_id=sd->bl.id;
		md->state.special_mob_ai=1;
		md->mode=mob_db[md->class].mode|0x04;
		md->deletetimer=add_timer(tick+60000,mob_timer_delete,id,0);
		clif_misceffect2(&md->bl,344);
	}
	clif_skill_poseffect(&sd->bl,AM_CALLHOMUN,1,x,y,tick);

	return 0;
}
/*==========================================
 * Character Skill Reset
 *------------------------------------------
 */
int atcommand_charskreset(
	const int fd, struct map_session_data* sd,
	const char* command, const char* message)
{
	char character[100];
	char output[200];
	struct map_session_data *pl_sd;

	memset(character, '\0', sizeof(character));
	memset(output, '\0', sizeof(output));

	if (!message || !*message || sscanf(message, "%99[^\n]", character) < 1)
		return -1;

	if ((pl_sd = map_nick2sd(character)) != NULL) {
		if (pc_isGM(sd) >= pc_isGM(pl_sd)) {
			pc_resetskill(pl_sd);
			sprintf(output, msg_table[99], character);
			clif_displaymessage(fd, output);
		} else
			return -1;
	} else {
		clif_displaymessage(fd, msg_table[3]);
		return -1;
	}

	return 0;
}

/*==========================================
 * Character Stat Reset
 *------------------------------------------
 */
int atcommand_charstreset(
	const int fd, struct map_session_data* sd,
	const char* command, const char* message)
{
	char character[100];
	char output[200];
	struct map_session_data *pl_sd;

	memset(character, '\0', sizeof(character));
	memset(output, '\0', sizeof(output));

	if (!message || !*message || sscanf(message, "%99[^\n]", character) < 1)
		return -1;

	if ((pl_sd = map_nick2sd(character)) != NULL) {
		if (pc_isGM(sd) >= pc_isGM(pl_sd)) {
			pc_resetstate(pl_sd);
			sprintf(output, msg_table[100], character);
			clif_displaymessage(fd, output);
		} else
			return -1;
	} else {
		clif_displaymessage(fd, msg_table[3]);
		return -1;
	}

	return 0;
}

/*==========================================
 * Character Reset
 *------------------------------------------
 */
int atcommand_charreset(
	const int fd, struct map_session_data* sd,
	const char* command, const char* message)
{
	char character[100];
	char output[200];
	struct map_session_data *pl_sd;

	memset(character, '\0', sizeof(character));
	memset(output, '\0', sizeof(output));

	if (!message || !*message || sscanf(message, "%99[^\n]", character) < 1)
		return -1;

	if ((pl_sd = map_nick2sd(character)) != NULL) {
		if (pc_isGM(sd) >= pc_isGM(pl_sd)) {
			pc_resetstate(pl_sd);
			pc_resetskill(pl_sd);
			sprintf(output, msg_table[101], character);
			clif_displaymessage(fd, output);
		} else
			return -1;
	} else {
		clif_displaymessage(fd, msg_table[3]);
		return -1;
	}

	return 0;
}
/*==========================================
 * Character Status Point (rewritten by [Yor])
 *------------------------------------------
 */
int atcommand_charstpoint(
	const int fd, struct map_session_data* sd,
	const char* command, const char* message)
{
	struct map_session_data *pl_sd;
	char character[100];
	char output[100];
	int new_status_point;
	int point = 0;

	memset(character, '\0', sizeof(character));

	if (!message || !*message || sscanf(message, "%d %99[^\n]", &point, character) < 2 || point == 0)
		return -1;

	if ((pl_sd = map_nick2sd(character)) != NULL) {
		new_status_point = (int)pl_sd->status.status_point + point;
		if (point > 0 && (point > 0x7FFF || new_status_point > 0x7FFF)) // fix positiv overflow
			new_status_point = 0x7FFF;
		else if (point < 0 && (point < -0x7FFF || new_status_point < 0)) // fix negativ overflow
			new_status_point = 0;
		if (new_status_point != (int)pl_sd->status.status_point) {
			pl_sd->status.status_point = new_status_point;
			clif_updatestatus(pl_sd, SP_STATUSPOINT);
			sprintf(output, msg_table[102], character);
			clif_displaymessage(fd, output);
		} else {
			return -1;
		}
	} else {
		clif_displaymessage(fd, msg_table[3]);
		return -1;
	}

	return 0;
}
/*==========================================
 * Character Skill Point (Rewritten by [Yor])
 *------------------------------------------
 */
int atcommand_charskpoint(
	const int fd, struct map_session_data* sd,
	const char* command, const char* message)
{
	struct map_session_data *pl_sd;
	char character[100];
	char output[100];
	int new_skill_point;
	int point = 0;

	memset(character, '\0', sizeof(character));

	if (!message || !*message || sscanf(message, "%d %99[^\n]", &point, character) < 2 || point == 0)
		return -1;

	if ((pl_sd = map_nick2sd(character)) != NULL) {
		new_skill_point = (int)pl_sd->status.skill_point + point;
		if (point > 0 && (point > 0x7FFF || new_skill_point > 0x7FFF)) // fix positiv overflow
			new_skill_point = 0x7FFF;
		else if (point < 0 && (point < -0x7FFF || new_skill_point < 0)) // fix negativ overflow
			new_skill_point = 0;
		if (new_skill_point != (int)pl_sd->status.skill_point) {
			pl_sd->status.skill_point = new_skill_point;
			clif_updatestatus(pl_sd, SP_SKILLPOINT);
			sprintf(output, msg_table[103], character);
			clif_displaymessage(fd, output);
		} else {
			return -1;
		}
	} else {
		clif_displaymessage(fd, msg_table[3]);
		return -1;
	}

	return 0;
}
/*==========================================
 * Character Zeny Point (Rewritten by [Yor])
 *------------------------------------------
 */
int atcommand_charzeny(
	const int fd, struct map_session_data* sd,
	const char* command, const char* message)
{
	struct map_session_data *pl_sd;
	char character[100];
	char output[100];
	int zeny = 0, new_zeny;

	memset(character, '\0', sizeof(character));

	if (!message || !*message || sscanf(message, "%d %99[^\n]", &zeny, character) < 2 || zeny == 0)
		return -1;

	if ((pl_sd = map_nick2sd(character)) != NULL) {
		new_zeny = pl_sd->status.zeny + zeny;
		if (zeny > 0 && (zeny > MAX_ZENY || new_zeny > MAX_ZENY)) // fix positiv overflow
			new_zeny = MAX_ZENY;
		else if (zeny < 0 && (zeny < -MAX_ZENY || new_zeny < 0)) // fix negativ overflow
			new_zeny = 0;
		if (new_zeny != pl_sd->status.zeny) {
			pl_sd->status.zeny = new_zeny;
			clif_updatestatus(pl_sd, SP_ZENY);
			sprintf(output, msg_table[104], character);
			clif_displaymessage(fd, output);
		} else {
			return -1;
		}
	} else {
		clif_displaymessage(fd, msg_table[3]);
		return -1;
	}

	return 0;
}

/*==========================================
 * @mapinfo <map name> [0-3] by MC_Cameri
 * => Shows information about the map [map name]
 * 0 = no additional information
 * 1 = Show users in that map and their location
 * 2 = Shows NPCs in that map
 * 3 = Shows the shops/chats in that map (not implemented)
 *------------------------------------------
 */
int atcommand_mapinfo(
	const int fd, struct map_session_data* sd,
	const char* command, const char* message)
{
	struct map_session_data *pl_sd;
	struct npc_data *nd = NULL;
	struct chat_data *cd = NULL;
	char output[200], map_name[100];
	char direction[12];
	int m_id, i, chat_num, list = 0;

	memset(output, '\0', sizeof(output));
	memset(map_name, '\0', sizeof(map_name));
	memset(direction, '\0', sizeof(direction));

	sscanf(message, "%d %99[^\n]", &list, map_name);

	if (list < 0 || list > 3)
		return -1;

	if (map_name[0] == '\0')
		strcpy(map_name, sd->mapname);
	if (strstr(map_name, ".gat") == NULL && strstr(map_name, ".afm") == NULL && strlen(map_name) < 13) // 16 - 4 (.gat)
		strcat(map_name, ".gat");

	if ((m_id = map_mapname2mapid(map_name)) < 0)
		return -1;

	clif_displaymessage(fd, "------ Map Info ------");
	sprintf(output, "Map Name: %s", map_name);
	clif_displaymessage(fd, output);
	sprintf(output, "Players In Map: %d", map[m_id].users);
	clif_displaymessage(fd, output);
	sprintf(output, "NPCs In Map: %d", map[m_id].npc_num);
	clif_displaymessage(fd, output);

	for (i=chat_num=0; i<fd_max; i++)
		if (session[i] && (pl_sd = session[i]->session_data)
			 && pl_sd->state.auth && (cd = (struct chat_data*)map_id2bl(pl_sd->chatID)))
				chat_num++;

	sprintf(output, "Chats In Map: %d", chat_num);
	clif_displaymessage(fd, output);
	clif_displaymessage(fd, "------ Map Flags ------");
	sprintf(output, "Player vs Player: %s | No Guild: %s | No Party: %s",
		(map[m_id].flag.pvp) ? "True" : "False",
		(map[m_id].flag.pvp_noguild) ? "True" : "False",
		(map[m_id].flag.pvp_noparty) ? "True" : "False");
	clif_displaymessage(fd, output);
	sprintf(output, "Guild vs Guild: %s | No Party: %s",
		(map[m_id].flag.gvg) ? "True" : "False",
		(map[m_id].flag.gvg_noparty) ? "True" : "False");
	clif_displaymessage(fd, output);
	sprintf(output, "No Dead Branch: %s",
		(map[m_id].flag.nobranch) ? "True" : "False");
	clif_displaymessage(fd, output);
	sprintf(output, "No Memo: %s",
		(map[m_id].flag.nomemo) ? "True" : "False");
	clif_displaymessage(fd, output);
	sprintf(output, "No Penalty: %s",
		(map[m_id].flag.nopenalty) ? "True" : "False");
	clif_displaymessage(fd, output);
	sprintf(output, "No Return: %s",
		(map[m_id].flag.noreturn) ? "True" : "False");
	clif_displaymessage(fd, output);
	sprintf(output, "No Save: %s",
		(map[m_id].flag.nosave) ? "True" : "False");
	clif_displaymessage(fd, output);
	sprintf(output, "No Teleport: %s",
		(map[m_id].flag.noteleport) ? "True" : "False");
	clif_displaymessage(fd, output);
	sprintf(output, "No Monster Teleport: %s",
		(map[m_id].flag.monster_noteleport) ? "True" : "False");
	clif_displaymessage(fd, output);
	sprintf(output, "No Zeny Penalty: %s",
		(map[m_id].flag.nozenypenalty) ? "True" : "False");
	clif_displaymessage(fd, output);

	switch (list) {
		case 0:
			// Do nothing. It's list 0, no additional display.
			break;
		case 1:
			clif_displaymessage(fd, "----- Players in Map -----");
			for (i = 0; i < fd_max; i++) {
				if (session[i] && (pl_sd = session[i]->session_data) && pl_sd->state.auth && strcmp(pl_sd->mapname, map_name) == 0) {
					sprintf(output, "Player '%s' (session #%d) | Location: %d,%d",
						pl_sd->status.name, i, pl_sd->bl.x, pl_sd->bl.y);
					clif_displaymessage(fd, output);
				}
			}
			break;
		case 2:
			clif_displaymessage(fd, "----- NPCs in Map -----");
			for (i = 0; i < map[m_id].npc_num;) {
				nd = map[m_id].npc[i];
				switch(nd->dir) {
				case 0:
					strcpy(direction, "North");
					break;
				case 1:
					strcpy(direction, "North West");
					break;
				case 2:
					strcpy(direction, "West");
					break;
				case 3:
					strcpy(direction, "South West");
					break;
				case 4:
					strcpy(direction, "South");
					break;
				case 5:
					strcpy(direction, "South East");
					break;
				case 6:
					strcpy(direction, "East");
					break;
				case 7:
					strcpy(direction, "North East");
					break;
				case 9:
					strcpy(direction, "North");
					break;
				default:
					strcpy(direction, "Unknown");
					break;
				}
				sprintf(output, "NPC %d: %s | Direction: %s | Sprite: %d | Location: %d %d",
				        ++i, nd->name, direction, nd->class, nd->bl.x, nd->bl.y);
				clif_displaymessage(fd, output);
			}
			break;
		case 3:
			clif_displaymessage(fd, "----- Chats in Map -----");
			for (i = 0; i < fd_max; i++) {
				if (session[i] && (pl_sd = session[i]->session_data) && pl_sd->state.auth &&
					(cd = (struct chat_data*)map_id2bl(pl_sd->chatID)) &&
					strcmp(pl_sd->mapname, map_name) == 0 &&
					cd->usersd[0] == pl_sd) {
					sprintf(output, "Chat %d: %s | Player: %s | Location: %d %d",
							i, cd->title, pl_sd->status.name, cd->bl.x, cd->bl.y);
					clif_displaymessage(fd, output);
					sprintf(output, "   Users: %d/%d | Password: %s | Public: %s",
							cd->users, cd->limit, cd->pass, (cd->pub) ? "Yes" : "No");
					clif_displaymessage(fd, output);
				}
			}
			break;
		default: // normally impossible to arrive here
			clif_displaymessage(fd, "Please, enter at least a valid list number (usage: @mapinfo <0-3> [map]).");
			return -1;
			break;
	}
	return 0;
}
/*==========================================
 * 	MOB Search
 *------------------------------------------
 */
int
atcommand_mobsearch(
	const int fd, struct map_session_data* sd,
	const char* command, const char* message)
{
	char mob_name[100];
	char output[100];
	int mob_id,map_id = 0;

	nullpo_retr(-1, sd);

	if (sscanf(message, "%99s", mob_name) < 0)
		return -1;

	if ((mob_id = atoi(mob_name)) == 0)
		 mob_id = mobdb_searchname(mob_name);
	if(mob_id !=-1 && (mob_id <= 1000 || mob_id >= 2000)){
		snprintf(output, sizeof output, msg_table[93],mob_name);
		clif_displaymessage(fd, output);
		return 0;
	}
	if(mob_id == atoi(mob_name) && mob_db[mob_id].jname)
				strcpy(mob_name,mob_db[mob_id].jname);	// --ja--
//				strcpy(mob_name,mob_db[mob_id].name);	// --en--

	map_id = sd->bl.m;

	snprintf(output, sizeof output, msg_table[92],
		mob_name, sd->mapname);
	clif_displaymessage(fd, output);

	map_foreachinarea(atmobsearch_sub, map_id, 0, 0,
		map[map_id].xs, map[map_id].ys, BL_MOB, mob_id, fd);

	atmobsearch_sub(&sd->bl,0);		// 番号リセット

	return 0;
}
/*==========================================
 * ドロップアイテムの掃除
 *------------------------------------------
 */
int
atcommand_cleanmap(
	const int fd, struct map_session_data* sd,
	const char* command, const char* message)
{
	int i=0;
	map_foreachinarea(atcommand_cleanmap_sub,sd->bl.m,
					  sd->bl.x-AREA_SIZE*2,sd->bl.y-AREA_SIZE*2,
					  sd->bl.x+AREA_SIZE*2,sd->bl.y+AREA_SIZE*2,
					  BL_ITEM,sd,&i);
	clif_displaymessage(fd, msg_table[95]);
	return 0;
}
/*==========================================
 * Clock
 *------------------------------------------
 */
int
atcommand_clock(
	const int fd, struct map_session_data* sd,
	const char* command, const char* message)
{
	char output[48];
	struct tm *tm;
	time_t t;
	t = time(NULL);
	tm = localtime(&t);
	
	sprintf(output, msg_table[96],tm->tm_hour,tm->tm_min,tm->tm_sec);
	
	clif_displaymessage(fd, output);
	return 0;
}
/*==========================================
 * Give Item
 * @giveitem (item_id or item_name) amount charname
 *------------------------------------------
 */
static void 
atcommand_giveitem_sub(struct map_session_data *sd,struct item_data *item_data,int number)
{
	int flag = 0;
	int loop = 1, get_count = number,i;
	struct item item_tmp;

	if(sd && item_data){
		if (item_data->type == 4 || item_data->type == 5 ||
			item_data->type == 7 || item_data->type == 8) {
			loop = number;
			get_count = 1;
		}
		for (i = 0; i < loop; i++) {
			memset(&item_tmp, 0, sizeof(item_tmp));
			item_tmp.nameid = item_data->nameid;
			item_tmp.identify = 1;
			if ((flag = pc_additem((struct map_session_data*)sd,
					&item_tmp, get_count)))
				clif_additem((struct map_session_data*)sd, 0, 0, flag);
		}
	}
}
int
atcommand_giveitem(
	const int fd, struct map_session_data* sd,
	const char* command, const char* message)
{
	struct map_session_data *pl_sd = NULL;
	struct item_data *item_data;
	char item_name[100];
	char character[100];
	char output[100];
	int number,i,item_id;

	if (!message || !*message)
		return -1;

	if (sscanf(message, "%99s %d %99[^\n]", item_name, &number, character) < 3)
		return -1;

	if (number <= 0)
		number = 1;

	if ((item_id = atoi(item_name)) > 0) {
		if (battle_config.item_check) {
			item_id =
				(((item_data = itemdb_exists(item_id)) &&
				 itemdb_available(item_id)) ? item_id : 0);
		} else {
			item_data = itemdb_search(item_id);
		}
	} else if ((item_data = itemdb_searchname(item_name)) != NULL) {
		item_id = (!battle_config.item_check ||
			itemdb_available(item_data->nameid)) ? item_data->nameid : 0;
	}
	if(item_id == 0)
		return -1;

	if ((pl_sd = map_nick2sd(character)) != NULL) { //該当名のキャラが存在する
		atcommand_giveitem_sub(pl_sd,item_data,number);
		snprintf(output, sizeof output ,msg_table[97],item_name,number);
		clif_displaymessage(pl_sd->fd, output);
		snprintf(output, sizeof output ,msg_table[98],pl_sd->status.name,item_name,number);
		clif_displaymessage(fd, output);
	}
	else if(strcmp(character,"ALL")==0){			// 名前がALLなら、接続者全員へ
		for (i = 0; i < fd_max; i++) {
			if (session[i] && (pl_sd = session[i]->session_data)){
				atcommand_giveitem_sub(pl_sd,item_data,number);
				snprintf(output, sizeof output ,msg_table[97],item_name,number);
				clif_displaymessage(pl_sd->fd, output);
			}
		}
		snprintf(output, sizeof output ,msg_table[98],"全員",item_name,number);
		clif_displaymessage(fd, output);
	}
	return 0;
}
/*==========================================
 * Weather control
 * 発動後に効果を戻す(消す)方法が分からない・・・
 *------------------------------------------
 */
int
atcommand_weather(
	const int fd, struct map_session_data* sd,
	const char* command, const char* message)
{
	struct map_session_data *psd=NULL;
	int i,type,map_id,effno=0;
	char weather[100],map_name[100],output[100];

	if (sscanf(message, "%99s %99s", weather , map_name) < 1)
		return -1;

	if (map_name[0] == '\0')
		strcpy(map_name, sd->mapname);
	else if (strstr(map_name, ".gat") == NULL && strlen(map_name) < 13)
		strcat(map_name, ".gat");

	if ((map_id = map_mapname2mapid(map_name)) < 0)
		map_id = sd->bl.m;

	if(!atoi(weather)){
		if(!strcmp(weather,"day"))
			type=0;
		else if(!strcmp(weather,"0"))
			type=0;
		else if(!strcmp(weather,"rain"))
			type=1;
		else if(!strcmp(weather,"snow"))
			type=2;
		else if(!strcmp(weather,"sakura"))
			type=3;
		else if(!strcmp(weather,"fog"))
			type=4;
		else if(!strcmp(weather,"leaves"))
			type=5;
		else
			return -1;
	}
	else
		type=atoi(weather);

	if(type<0 || type>5)
		return -1;

	switch(type){
		case 0:
			if(map[map_id].flag.rain==1)
				effno=410;
			map[map_id].flag.rain=0;
			map[map_id].flag.snow=0;
			map[map_id].flag.sakura=0;
			map[map_id].flag.fog=0;
			map[map_id].flag.leaves=0;
			snprintf(output, sizeof output ,msg_table[112]);
			clif_displaymessage(fd, output);
			break;
		case 1:
			if(!map[map_id].flag.rain){
				effno=161;
				map[map_id].flag.rain=1;
				snprintf(output, sizeof output ,msg_table[84]);
				clif_displaymessage(fd, output);
			}else{
				map[map_id].flag.rain=0;
			}
			break;
		case 2:
			if(!map[map_id].flag.snow){
				effno=162;
				map[map_id].flag.snow=1;
				snprintf(output, sizeof output ,msg_table[85]);
				clif_displaymessage(fd, output);
			}else{
				map[map_id].flag.snow=0;
			}
			break;
		case 3:
			if(!map[map_id].flag.sakura){
				effno=163;
				map[map_id].flag.sakura=1;
				snprintf(output, sizeof output ,msg_table[86]);
				clif_displaymessage(fd, output);
			}else{
				map[map_id].flag.sakura=0;
			}
			break;
		case 4:
			if(!map[map_id].flag.fog){
				effno=233;
				map[map_id].flag.fog=1;
				snprintf(output, sizeof output ,msg_table[87]);
				clif_displaymessage(fd, output);
			}else{
				map[map_id].flag.fog=0;
			}
			break;
		case 5:
			if(!map[map_id].flag.leaves){
				effno=333;
				map[map_id].flag.leaves=1;
				snprintf(output, sizeof output ,msg_table[88]);
				clif_displaymessage(fd, output);
			}else{
				map[map_id].flag.leaves=0;
			}
			break;
		default:
			break;
	}
	// 指定マップ内に既に居るキャラは即時に天候変化
	for(i=0; effno && i < fd_max; i++) {
		if (session[i] && (psd = session[i]->session_data) != NULL && psd->state.auth){
			if(strcmp(map_name,"all.gat") && !strcmp(map_name,psd->mapname))
				clif_misceffect3(&psd->bl,effno);
		}
	}
	return 0;
}
