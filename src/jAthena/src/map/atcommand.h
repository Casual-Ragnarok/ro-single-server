//atcommand.h
#ifndef _ATCOMMAND_H_
#define _ATCOMMAND_H_

enum AtCommandType {
	AtCommand_None = -1,
	AtCommand_Broadcast = 0,
	AtCommand_LocalBroadcast,
	AtCommand_MapMove,
	AtCommand_ResetState,
	AtCommand_RuraP,
	AtCommand_Rura,
	AtCommand_Where,
	AtCommand_JumpTo,
	AtCommand_Jump,
	AtCommand_Who,
	AtCommand_Save,
	AtCommand_Load,
	AtCommand_Speed,
	AtCommand_Storage,
	AtCommand_GuildStorage,
	AtCommand_Option,
	AtCommand_Hide,
	AtCommand_JobChange,
	AtCommand_Die,
	AtCommand_Kill,
	AtCommand_Alive,
	AtCommand_Kami,
	AtCommand_KamiB,
	AtCommand_Heal,
	AtCommand_Item,
	AtCommand_Item2,
	AtCommand_ItemReset,
	AtCommand_ItemCheck,
	AtCommand_BaseLevelUp,
	AtCommand_JobLevelUp,
	AtCommand_H,
	AtCommand_Help,
	AtCommand_GM,
	AtCommand_PvPOff,
	AtCommand_PvPOn,
	AtCommand_GvGOff,
	AtCommand_GvGOn,
	AtCommand_Model,
	AtCommand_Go,
	AtCommand_Monster,
	AtCommand_KillMonster,
	AtCommand_KillMonster2,
	AtCommand_Refine,
	AtCommand_Produce,
	AtCommand_Memo,
	AtCommand_GAT,
	AtCommand_Packet,
	AtCommand_StatusPoint,
	AtCommand_SkillPoint,
	AtCommand_Zeny,
	AtCommand_Param,
	AtCommand_Strength,
	AtCommand_Agility,
	AtCommand_Vitality,
	AtCommand_Intelligence,
	AtCommand_Dexterity,
	AtCommand_Luck,
	AtCommand_GuildLevelUp,
	AtCommand_MakePet,
	AtCommand_Hatch,
	AtCommand_PetFriendly,
	AtCommand_PetHungry,
	AtCommand_PetRename,
	AtCommand_CharPetRename,
	AtCommand_Recall,
	AtCommand_Recallall,
	AtCommand_RecallGuild,
	AtCommand_RecallParty,
	AtCommand_CharacterJob,
	AtCommand_Revive,
	AtCommand_CharacterStats,
	AtCommand_CharacterOption,
	AtCommand_CharacterSave,
	AtCommand_CharacterLoad,
	AtCommand_Night,
	AtCommand_Day,
	AtCommand_Doom,
	AtCommand_DoomMap,
	AtCommand_Raise,
	AtCommand_RaiseMap,
	AtCommand_CharacterBaseLevel,
	AtCommand_CharacterJobLevel,
	AtCommand_Kick,
	AtCommand_KickAll,
	AtCommand_AllSkill,
	AtCommand_QuestSkill,
	AtCommand_CharQuestSkill,
	AtCommand_LostSkill,
	AtCommand_CharLostSkill,
	AtCommand_SpiritBall,
	AtCommand_Party,
	AtCommand_Guild,
	AtCommand_AgitStart,
	AtCommand_AgitEnd,
	AtCommand_OnlyMes,
	AtCommand_MapExit,
	AtCommand_IDSearch,
	AtCommand_ItemIdentify,
	AtCommand_Shuffle,
	AtCommand_Maintenance,
	AtCommand_Misceffect,
	AtCommand_Summon,
	AtCommand_WhoP,
	AtCommand_ReloadItemDB,
	AtCommand_ReloadMobDB,
	AtCommand_ReloadSkillDB,
	AtCommand_CharSkReset,
	AtCommand_CharStReset,
	AtCommand_CharReset,
	AtCommand_CharSKPoint,
	AtCommand_CharSTPoint, 
	AtCommand_CharZeny,
	AtCommand_MapInfo,
	AtCommand_MobSearch,
	AtCommand_CleanMap,
	AtCommand_Clock,
	AtCommand_GiveItem,
	AtCommand_Weather,
	AtCommand_Unknown,
	AtCommand_MAX
};

typedef enum AtCommandType AtCommandType;

typedef struct AtCommandInfo {
	AtCommandType type;
	const char* command;
	int level;
	int (*proc)(const int, struct map_session_data*, 
		const char* command, const char* message);
} AtCommandInfo;

AtCommandType
is_atcommand(const int fd, struct map_session_data* sd, const char* message, int gmlvl);

AtCommandType atcommand(
	const int level, const char* message, AtCommandInfo* info);
int get_atcommand_level(const AtCommandType type);

#define ATCOMMAND_CONF_FILENAME	"conf/atcommand_athena.conf"
#define MSG_CONF_NAME	"conf/msg_athena.conf"
int atcommand_config_read(const char *cfgName);
int msg_config_read(const char *cfgName);
extern char msg_table[200][1024];

#endif

