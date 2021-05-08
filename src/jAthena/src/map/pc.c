#include <stdio.h>
#include <stdlib.h>
#include <string.h>
#include <ctype.h>

#include "timer.h"
#include "db.h"
#include "nullpo.h"
#include "malloc.h"
#include "map.h"
#include "chrif.h"
#include "clif.h"
#include "intif.h"
#include "pc.h"
#include "npc.h"
#include "mob.h"
#include "pet.h"
#include "itemdb.h"
#include "script.h"
#include "battle.h"
#include "skill.h"
#include "party.h"
#include "guild.h"
#include "chat.h"
#include "trade.h"
#include "storage.h"
#include "vending.h"
#include "status.h"

#ifdef MEMWATCH
#include "memwatch.h"
#endif

#ifdef _MSC_VER
	#define snprintf _snprintf
#endif

#define PVP_CALCRANK_INTERVAL 1000	// PVP順位計算の間隔

static int exp_table[14][MAX_LEVEL];

static int dirx[8]={0,-1,-1,-1,0,1,1,1};
static int diry[8]={1,1,0,-1,-1,-1,0,1};

static unsigned int equip_pos[11]={0x0080,0x0008,0x0040,0x0004,0x0001,0x0200,0x0100,0x0010,0x0020,0x0002,0x8000};

static char GM_account_filename[1024] = "conf/GM_account.txt";
static struct dbt *gm_account_db;

static struct {
	int id;
	int max;
	struct {
		short id,lv;
	} need[6];
} skill_tree[3][MAX_PC_CLASS][100];

void pc_set_gm_account_fname(char *str)
{
	strncpy(GM_account_filename,str,1024);
}

int pc_isGM(struct map_session_data *sd)
{
	struct gm_account *p;

	nullpo_retr(0, sd);

	if( (p = numdb_search(gm_account_db,sd->status.account_id)) == NULL )
		return 0;
	return p->level;
}
int pc_numisGM(int account_id)
{
	struct gm_account *p;

	if( (p = numdb_search(gm_account_db,account_id)) == NULL )
		return 0;
	return p->level;
}
/*==========================================
 * PCが終了できるかどうかの判定
 *------------------------------------------
 */
int pc_isquitable(struct map_session_data *sd)
{
	unsigned int tick=gettick();
	struct skill_unit_group* sg;
	int c=0;

	nullpo_retr(0, sd);

	c=pc_counttargeted(sd,NULL,0);

	if(!pc_isdead(sd) && (sd->opt1 || sd->opt2))
		return 1;
	if(sd->skilltimer != -1)
		return 1;
	if(DIFF_TICK(tick , sd->canact_tick) < 0)
		return 1;
	if(sd->sc_data && sd->sc_data[SC_DANCING].timer!=-1 && sd->sc_data[SC_DANCING].val4 && (sg=(struct skill_unit_group *)sd->sc_data[SC_DANCING].val2) && sg->src_id == sd->bl.id)
		return 1;
	if(c > 0)
		return 1;

	return 0;
}

static int distance(int x0,int y0,int x1,int y1)
{
	int dx,dy;

	dx=abs(x0-x1);
	dy=abs(y0-y1);
	return dx>dy ? dx : dy;
}

static int pc_invincible_timer(int tid,unsigned int tick,int id,int data)
{
	struct map_session_data *sd;

	if( (sd=(struct map_session_data *)map_id2sd(id)) == NULL || sd->bl.type!=BL_PC )
		return 1;

	if(sd->invincible_timer != tid){
		if(battle_config.error_log)
			printf("invincible_timer %d != %d\n",sd->invincible_timer,tid);
		return 0;
	}
	sd->invincible_timer=-1;

	return 0;
}

int pc_setinvincibletimer(struct map_session_data *sd,int val)
{
	nullpo_retr(0, sd);

	if(sd->invincible_timer != -1)
		delete_timer(sd->invincible_timer,pc_invincible_timer);
	sd->invincible_timer = add_timer(gettick()+val,pc_invincible_timer,sd->bl.id,0);
	return 0;
}

int pc_delinvincibletimer(struct map_session_data *sd)
{
	nullpo_retr(0, sd);

	if(sd->invincible_timer != -1) {
		delete_timer(sd->invincible_timer,pc_invincible_timer);
		sd->invincible_timer = -1;
	}
	return 0;
}

static int pc_spiritball_timer(int tid,unsigned int tick,int id,int data)
{
	struct map_session_data *sd;
	int i;

	if( (sd=(struct map_session_data *)map_id2sd(id)) == NULL || sd->bl.type!=BL_PC )
		return 1;

	if(sd->spirit_timer[0] != tid){
		if(battle_config.error_log)
			printf("spirit_timer %d != %d\n",sd->spirit_timer[0],tid);
		return 0;
	}
	sd->spirit_timer[0]=-1;
	for(i=1;i<sd->spiritball;i++) {
		sd->spirit_timer[i-1] = sd->spirit_timer[i];
		sd->spirit_timer[i] = -1;
	}
	sd->spiritball--;
	if(sd->spiritball < 0)
		sd->spiritball = 0;
	clif_spiritball(sd);

	return 0;
}

int pc_addspiritball(struct map_session_data *sd,int interval,int max)
{
	int i;

	nullpo_retr(0, sd);

	if(max > MAX_SKILL_LEVEL)
		max = MAX_SKILL_LEVEL;
	if(sd->spiritball < 0)
		sd->spiritball = 0;

	if(sd->spiritball >= max) {
		if(sd->spirit_timer[0] != -1) {
			delete_timer(sd->spirit_timer[0],pc_spiritball_timer);
			sd->spirit_timer[0] = -1;
		}
		for(i=1;i<max;i++) {
			sd->spirit_timer[i-1] = sd->spirit_timer[i];
			sd->spirit_timer[i] = -1;
		}
	}
	else
		sd->spiritball++;

	sd->spirit_timer[sd->spiritball-1] = add_timer(gettick()+interval,pc_spiritball_timer,sd->bl.id,0);
	clif_spiritball(sd);

	return 0;
}


int pc_delspiritball(struct map_session_data *sd,int count,int type)
{
	int i;

	nullpo_retr(0, sd);

	if(sd->spiritball <= 0) {
		sd->spiritball = 0;
		return 0;
	}

	if(count > sd->spiritball)
		count = sd->spiritball;
	sd->spiritball -= count;
	if(count > MAX_SKILL_LEVEL)
		count = MAX_SKILL_LEVEL;

	for(i=0;i<count;i++) {
		if(sd->spirit_timer[i] != -1) {
			delete_timer(sd->spirit_timer[i],pc_spiritball_timer);
			sd->spirit_timer[i] = -1;
		}
	}
	for(i=count;i<MAX_SKILL_LEVEL;i++) {
		sd->spirit_timer[i-count] = sd->spirit_timer[i];
		sd->spirit_timer[i] = -1;
	}

	if(!type)
		clif_spiritball(sd);

	return 0;
}

int pc_setrestartvalue(struct map_session_data *sd,int type)
{
	//転生や養子の場合の元の職業を算出する
	struct pc_base_job s_class;

	nullpo_retr(0, sd);

	s_class = pc_calc_base_job(sd->status.class);

	//-----------------------
	// 死亡した
	if(sd->special_state.restart_full_recover) {	// オシリスカード
		sd->status.hp=sd->status.max_hp;
		sd->status.sp=sd->status.max_sp;
	}
	else {
		if(s_class.job == 0 && battle_config.restart_hp_rate < 50) { //ノビは半分回復
			sd->status.hp=(sd->status.max_hp)/2;
		}
		else {
			if(battle_config.restart_hp_rate <= 0)
				sd->status.hp = 1;
			else {
				sd->status.hp = sd->status.max_hp * battle_config.restart_hp_rate /100;
				if(sd->status.hp <= 0)
					sd->status.hp = 1;
			}
		}
		if(battle_config.restart_sp_rate > 0) {
			int sp = sd->status.max_sp * battle_config.restart_sp_rate /100;
			if(sd->status.sp < sp)
				sd->status.sp = sp;
		}
	}
	if(type&1)
		clif_updatestatus(sd,SP_HP);
	if(type&1)
		clif_updatestatus(sd,SP_SP);

	if(type&2) {
		if(!(battle_config.death_penalty_type&1) ) { //デスペナ
			if((s_class.job != 0) && !map[sd->bl.m].flag.nopenalty && !map[sd->bl.m].flag.gvg){
				if(battle_config.death_penalty_type&2 && battle_config.death_penalty_base > 0)
					sd->status.base_exp -= (int)((double)pc_nextbaseexp(sd) * (double)battle_config.death_penalty_base/10000.);
				else if(battle_config.death_penalty_base > 0) {
					if(pc_nextbaseexp(sd) > 0)
						sd->status.base_exp -= (int)((double)sd->status.base_exp * (double)battle_config.death_penalty_base/10000.);
				}
				if(sd->status.base_exp < 0)
					sd->status.base_exp = 0;
				if(type&1)
					clif_updatestatus(sd,SP_BASEEXP);

				if(battle_config.death_penalty_type&2 && battle_config.death_penalty_job > 0)
					sd->status.job_exp -= (int)((double)pc_nextjobexp(sd) * (double)battle_config.death_penalty_job/10000.);
				else if(battle_config.death_penalty_job > 0) {
					if(pc_nextjobexp(sd) > 0)
						sd->status.job_exp -= (int)((double)sd->status.job_exp * (double)battle_config.death_penalty_job/10000.);
				}
				if(sd->status.job_exp < 0)
					sd->status.job_exp = 0;
				if(type&1)
					clif_updatestatus(sd,SP_JOBEXP);
			}
		}
		if(battle_config.zeny_penalty > 0&&!map[sd->bl.m].flag.nozenypenalty) {
			int zeny = (int)((double)sd->status.zeny * (double)battle_config.zeny_penalty / 10000.);
			if(zeny < 1) zeny = 1;
			sd->status.zeny -= zeny;
			if(sd->status.zeny < 0) sd->status.zeny = 0;
			if(type&1)
				clif_updatestatus(sd,SP_ZENY);
		}
	}

	return 0;
}

/*==========================================
 * 自分をロックしているMOBの数を数える(foreachclient)
 *------------------------------------------
 */
static int pc_counttargeted_sub(struct block_list *bl,va_list ap)
{
	int id,*c,target_lv;
	struct block_list *src;

	nullpo_retr(0, bl);
	nullpo_retr(0, ap);

	id=va_arg(ap,int);

	nullpo_retr(0, c=va_arg(ap,int *));

	src=va_arg(ap,struct block_list *);
	target_lv=va_arg(ap,int);
	if(id == bl->id || (src && id == src->id)) return 0;
	if(bl->type == BL_PC) {
		struct map_session_data *sd=(struct map_session_data *)bl;
		if( sd && sd->attacktarget == id && sd->attacktimer != -1 && sd->attacktarget_lv >= target_lv)
			(*c)++;
	}
	else if(bl->type == BL_MOB) {
		struct mob_data *md = (struct mob_data *)bl;
//		if(md && md->target_id == id && md->timer != -1 && md->state.state == MS_ATTACK && md->target_lv >= target_lv)
		if(md && md->target_id == id && md->target_lv >= target_lv)
			(*c)++;
		//printf("md->target_lv:%d, target_lv:%d\n",((struct mob_data *)bl)->target_lv,target_lv);
	}
	return 0;
}

int pc_counttargeted(struct map_session_data *sd,struct block_list *src,int target_lv)
{
	int c=0;
	map_foreachinarea(pc_counttargeted_sub, sd->bl.m,
		sd->bl.x-AREA_SIZE,sd->bl.y-AREA_SIZE,
		sd->bl.x+AREA_SIZE,sd->bl.y+AREA_SIZE,0,sd->bl.id,&c,src,target_lv);
	return c;
}

/*==========================================
 * ローカルプロトタイプ宣言 (必要な物のみ)
 *------------------------------------------
 */
static int pc_walktoxy_sub(struct map_session_data *);


/*==========================================
 * saveに必要なステータス修正を行なう
 *------------------------------------------
 */
int pc_makesavestatus(struct map_session_data *sd)
{
	nullpo_retr(0, sd);

	// 服の色は色々弊害が多いので保存対象にはしない
	if(!battle_config.save_clothcolor)
		sd->status.clothes_color=0;

	// 死亡状態だったのでhpを1、位置をセーブ場所に変更
	if(pc_isdead(sd)){
		pc_setrestartvalue(sd,0);
		memcpy(&sd->status.last_point,&sd->status.save_point,sizeof(sd->status.last_point));
	} else {
		memcpy(sd->status.last_point.map,sd->mapname,24);
		sd->status.last_point.x = sd->bl.x;
		sd->status.last_point.y = sd->bl.y;
	}

	// セーブ禁止マップだったので指定位置に移動
	if(map[sd->bl.m].flag.nosave){
		struct map_data *m=&map[sd->bl.m];
		if(strcmp(m->save.map,"SavePoint")==0)
			memcpy(&sd->status.last_point,&sd->status.save_point,sizeof(sd->status.last_point));
		else
			memcpy(&sd->status.last_point,&m->save,sizeof(sd->status.last_point));
	}

	//マナーポイントがプラスだった場合0に
	if(sd->status.manner > 0)
		sd->status.manner = 0;
	return 0;
}


/*==========================================
 * 接続時の初期化
 *------------------------------------------
 */
int pc_setnewpc(struct map_session_data *sd,int account_id,int char_id,int login_id1,int client_tick,int sex,int fd)
{
	nullpo_retr(0, sd);

	sd->bl.id        = account_id;
	sd->char_id      = char_id;
	sd->login_id1    = login_id1;
	sd->client_tick  = client_tick;
	sd->sex          = sex;
	sd->state.auth   = 0;
	sd->bl.type      = BL_PC;
	sd->canact_tick = sd->canmove_tick = gettick();
	sd->state.waitingdisconnect=0;

	return 0;
}


int pc_equippoint(struct map_session_data *sd,int n)
{
	int ep = 0;
	//転生や養子の場合の元の職業を算出する
	struct pc_base_job s_class;

	nullpo_retr(0, sd);

	s_class = pc_calc_base_job(sd->status.class);

	if(sd->inventory_data[n]) {
		ep = sd->inventory_data[n]->equip;
		if(sd->inventory_data[n]->look == 1 || sd->inventory_data[n]->look == 2 || sd->inventory_data[n]->look == 6) {
			if(ep == 2 && (pc_checkskill(sd,AS_LEFT) > 0 || s_class.job == 12))
				return 34;
		}
	}
	return ep;
}

int pc_setinventorydata(struct map_session_data *sd)
{
	int i,id;

	nullpo_retr(0, sd);

	for(i=0;i<MAX_INVENTORY;i++) {
		id = sd->status.inventory[i].nameid;
		sd->inventory_data[i] = itemdb_search(id);
	}
	return 0;
}

int pc_calcweapontype(struct map_session_data *sd)
{
	nullpo_retr(0, sd);

	if(sd->weapontype1 != 0 &&	sd->weapontype2 == 0)
		sd->status.weapon = sd->weapontype1;
	if(sd->weapontype1 == 0 &&	sd->weapontype2 != 0)// 左手武器 Only
		sd->status.weapon = sd->weapontype2;
	else if(sd->weapontype1 == 1 && sd->weapontype2 == 1)// 双短剣
		sd->status.weapon = 0x11;
	else if(sd->weapontype1 == 2 && sd->weapontype2 == 2)// 双単手剣
		sd->status.weapon = 0x12;
	else if(sd->weapontype1 == 6 && sd->weapontype2 == 6)// 双単手斧
		sd->status.weapon = 0x13;
	else if( (sd->weapontype1 == 1 && sd->weapontype2 == 2) ||
		(sd->weapontype1 == 2 && sd->weapontype2 == 1) ) // 短剣 - 単手剣
		sd->status.weapon = 0x14;
	else if( (sd->weapontype1 == 1 && sd->weapontype2 == 6) ||
		(sd->weapontype1 == 6 && sd->weapontype2 == 1) ) // 短剣 - 斧
		sd->status.weapon = 0x15;
	else if( (sd->weapontype1 == 2 && sd->weapontype2 == 6) ||
		(sd->weapontype1 == 6 && sd->weapontype2 == 2) ) // 単手剣 - 斧
		sd->status.weapon = 0x16;
	else
		sd->status.weapon = sd->weapontype1;

	return 0;
}

int pc_setequipindex(struct map_session_data *sd)
{
	int i,j;

	nullpo_retr(0, sd);

	for(i=0;i<11;i++)
		sd->equip_index[i] = -1;

	for(i=0;i<MAX_INVENTORY;i++) {
		if(sd->status.inventory[i].nameid <= 0)
			continue;
		if(sd->status.inventory[i].equip) {
			for(j=0;j<11;j++)
				if(sd->status.inventory[i].equip & equip_pos[j])
					sd->equip_index[j] = i;
			if(sd->status.inventory[i].equip & 0x0002) {
				if(sd->inventory_data[i])
					sd->weapontype1 = sd->inventory_data[i]->look;
				else
					sd->weapontype1 = 0;
			}
			if(sd->status.inventory[i].equip & 0x0020) {
				if(sd->inventory_data[i]) {
					if(sd->inventory_data[i]->type == 4) {
						if(sd->status.inventory[i].equip == 0x0020)
							sd->weapontype2 = sd->inventory_data[i]->look;
						else
							sd->weapontype2 = 0;
					}
					else
						sd->weapontype2 = 0;
				}
				else
					sd->weapontype2 = 0;
			}
		}
	}
	pc_calcweapontype(sd);

	return 0;
}

int pc_isequip(struct map_session_data *sd,int n)
{
	struct item_data *item;
	struct status_change *sc_data;
	//転生や養子の場合の元の職業を算出する
	struct pc_base_job s_class;

	nullpo_retr(0, sd);

	item = sd->inventory_data[n];
	sc_data = status_get_sc_data(&sd->bl);
	s_class = pc_calc_base_job(sd->status.class);

 	if( battle_config.gm_allequip>0 && pc_isGM(sd)>=battle_config.gm_allequip )
		return 1;

	if(item == NULL)
		return 0;
	if(item->sex != 2 && sd->status.sex != item->sex)
		return 0;
	if(item->elv > 0 && sd->status.base_level < item->elv)
		return 0;
	if(((1<<s_class.job)&item->class) == 0)
		return 0;
	if(map[sd->bl.m].flag.pvp && (item->flag.no_equip==1 || item->flag.no_equip==3))
		return 0;
	if(map[sd->bl.m].flag.gvg && (item->flag.no_equip==2 || item->flag.no_equip==3))
		return 0;
	if(item->equip & 0x0002 && sc_data && sc_data[SC_STRIPWEAPON].timer != -1)
		return 0;
	if(item->equip & 0x0020 && sc_data && sc_data[SC_STRIPSHIELD].timer != -1)
		return 0;
	if(item->equip & 0x0010 && sc_data && sc_data[SC_STRIPARMOR].timer != -1)
		return 0;
	if(item->equip & 0x0100 && sc_data && sc_data[SC_STRIPHELM].timer != -1)
		return 0;
	return 1;
}

/*==========================================
 * session idに問題無し
 * char鯖から送られてきたステータスを設定
 *------------------------------------------
 */
int pc_authok(int id,struct mmo_charstatus *st)
{
	struct map_session_data *sd;
	struct party *p;
	struct guild *g;
	int i;
	unsigned long tick = gettick();

	sd = map_id2sd(id);
	if(sd==NULL)
		return 1;
	if(sd->new_fd){
		// 2重login状態だったので、両方落す
		clif_authfail_fd(sd->fd,2);	// same id
		clif_authfail_fd(sd->new_fd,2);	// same id
		return 1;
	}
	memcpy(&sd->status,st,sizeof(*st));

	if(sd->status.sex != sd->sex){
		clif_authfail_fd(sd->fd,0);
		return 1;
	}

	memset(&sd->state,0,sizeof(sd->state));
	// 基本的な初期化
	sd->state.connect_new = 1;
	sd->bl.prev = sd->bl.next = NULL;
	sd->weapontype1 = sd->weapontype2 = 0;
	sd->view_class = sd->status.class;
	sd->speed = DEFAULT_WALK_SPEED;
	sd->state.dead_sit=0;
	sd->dir=0;
	sd->head_dir=0;
	sd->state.auth=1;
	sd->walktimer=-1;
	sd->attacktimer=-1;
	sd->skilltimer=-1;
	sd->skillitem=-1;
	sd->skillitemlv=-1;
	sd->invincible_timer=-1;

	sd->deal_locked =0;
	sd->trade_partner=0;

	sd->inchealhptick = 0;
	sd->inchealsptick = 0;
	sd->hp_sub = 0;
	sd->sp_sub = 0;
	sd->inchealspirithptick = 0;
	sd->inchealspiritsptick = 0;
	sd->canact_tick = tick;
	sd->canmove_tick = tick;
	sd->attackabletime = tick;

	sd->doridori_counter = 0;

	sd->spiritball = 0;
	sd->wis_all = 0;

	for(i=0;i<MAX_SKILL_LEVEL;i++)
		sd->spirit_timer[i] = -1;
	for(i=0;i<MAX_SKILLTIMERSKILL;i++)
		sd->skilltimerskill[i].timer = -1;

	memset(&sd->dev,0,sizeof(struct square));
	for(i=0;i<5;i++){
		sd->dev.val1[i] = 0;
		sd->dev.val2[i] = 0;
	}

	// アカウント変数の送信要求
	intif_request_accountreg(sd);

	// アイテムチェック
	pc_setinventorydata(sd);
	pc_checkitem(sd);

	// pet
	sd->petDB = NULL;
	sd->pd = NULL;
	sd->pet_hungry_timer = -1;
	memset(&sd->pet,0,sizeof(struct s_pet));

	// ステータス異常の初期化
	for(i=0;i<MAX_STATUSCHANGE;i++) {
		sd->sc_data[i].timer=-1;
		sd->sc_data[i].val1 = sd->sc_data[i].val2 = sd->sc_data[i].val3 = sd->sc_data[i].val4 = 0;
	}
	sd->sc_count=0;
	sd->status.option&=OPTION_MASK;

	// スキルユニット関係の初期化
	memset(sd->skillunit,0,sizeof(sd->skillunit));
	memset(sd->skillunittick,0,sizeof(sd->skillunittick));

	// パーティー関係の初期化
	sd->party_sended=0;
	sd->party_invite=0;
	sd->party_x=-1;
	sd->party_y=-1;
	sd->party_hp=-1;

	// ギルド関係の初期化
	sd->guild_sended=0;
	sd->guild_invite=0;
	sd->guild_alliance=0;

	// イベント関係の初期化
	memset(sd->eventqueue,0,sizeof(sd->eventqueue));
	for(i=0;i<MAX_EVENTTIMER;i++)
		sd->eventtimer[i] = -1;

	// 位置の設定
	pc_setpos(sd,sd->status.last_point.map ,
		sd->status.last_point.x , sd->status.last_point.y, 0);

	// pet
	if(sd->status.pet_id > 0)
		intif_request_petdata(sd->status.account_id,sd->status.char_id,sd->status.pet_id);

	// パーティ、ギルドデータの要求
	if( sd->status.party_id>0 && (p=party_search(sd->status.party_id))==NULL)
		party_request_info(sd->status.party_id);
	if( sd->status.guild_id>0 && (g=guild_search(sd->status.guild_id))==NULL)
		guild_request_info(sd->status.guild_id);

	// pvpの設定
	sd->pvp_rank=0;
	sd->pvp_point=0;
	sd->pvp_timer=-1;

	// 通知
	clif_authok(sd);
	map_addnickdb(sd);
//	if( map_charid2nick(sd->status.char_id)==NULL )
		map_addchariddb(sd->status.char_id,sd->status.name,sd->status.account_id,sd->status.mapip,sd->status.mapport);

	//スパノビ用死にカウンターのスクリプト変数からの読み出しとsdへのセット
	sd->die_counter = pc_readglobalreg(sd,"PC_DIE_COUNTER");

	// ステータス初期計算など
	status_calc_pc(sd,1);

	// Message of the Dayの送信
	{
		char buf[256];
		FILE *fp;
		if(	(fp = fopen(motd_txt, "r"))!=NULL){
			while (fgets(buf, 250, fp) != NULL){
				int i;
				for( i=0; buf[i]; i++){
					if( buf[i]=='\r' || buf[i]=='\n'){
						buf[i]=0;
						break;
					}
				}
				clif_displaymessage(sd->fd,buf);
			}
			fclose(fp);
		}
	}

	return 0;
}


/*==========================================
 * session idに問題ありなので後始末
 *------------------------------------------
 */
int pc_authfail(int id)
{
	struct map_session_data *sd;

	sd = map_id2sd(id);
	if(sd==NULL)
		return 1;
	if(sd->new_fd){
		// 2重login状態だったので、新しい接続のみ落す
		clif_authfail_fd(sd->new_fd,0);

		sd->new_fd=0;
		return 0;
	}
	clif_authfail_fd(sd->fd,0);
	return 0;
}


static int pc_calc_skillpoint(struct map_session_data* sd)
{
	int  i,skill,skill_point=0;

	nullpo_retr(0, sd);

	for(i=1;i<MAX_SKILL;i++){
		if( (skill = pc_checkskill(sd,i)) > 0) {
			if(!(skill_get_inf2(i)&0x01) || battle_config.quest_skill_learn) {
				if(!sd->status.skill[i].flag)
					skill_point += skill;
				else if(sd->status.skill[i].flag > 2 && sd->status.skill[i].flag != 13) {
					skill_point += (sd->status.skill[i].flag - 2);
				}
			}
		}
	}

	return skill_point;
}

/*==========================================
 * 覚えられるスキルの計算
 *------------------------------------------
 */
int pc_calc_skilltree(struct map_session_data *sd)
{
	int i,id=0,flag;
	int c=0, s=0;
	//転生や養子の場合の元の職業を算出する
	struct pc_base_job s_class;

	nullpo_retr(0, sd);

	s_class = pc_calc_base_job(sd->status.class);
	c = s_class.job;
	s = (s_class.upper==1) ? 1 : 0 ; //転生以外は通常のスキル？

	if(battle_config.skillup_limit && c >= 0 && c < 23) {
		int skill_point = pc_calc_skillpoint(sd);
		if(skill_point < 9)
			c = 0;
		else if(sd->status.skill_point >= sd->status.job_level && skill_point < 58 && c > 6) {
			switch(c) {
				case 7:
				case 14:
					c = 1;
					break;
				case 8:
				case 15:
					c = 4;
					break;
				case 9:
				case 16:
					c = 2;
					break;
				case 10:
				case 18:
					c = 5;
					break;
				case 11:
				case 19:
				case 20:
					c = 3;
					break;
				case 12:
				case 17:
					c = 6;
					break;
			}
		}
	}

	for(i=0;i<MAX_SKILL;i++){
		if (sd->status.skill[i].flag != 13) sd->status.skill[i].id=0;
		if (sd->status.skill[i].flag && sd->status.skill[i].flag != 13){	// cardスキルなら、
			sd->status.skill[i].lv=(sd->status.skill[i].flag==1)?0:sd->status.skill[i].flag-2;	// 本当のlvに
			sd->status.skill[i].flag=0;	// flagは0にしておく
		}
	}

	if (battle_config.gm_allskill > 0 && pc_isGM(sd) >= battle_config.gm_allskill){
		// 全てのスキル
		for(i=1;i<158;i++)
			sd->status.skill[i].id=i;
		for(i=210;i<291;i++)
			sd->status.skill[i].id=i;
		if(battle_config.gm_allskill_addabra){ // アブラカタブラ専用スキル
			for(i=291;i<304;i++)
				sd->status.skill[i].id=i;
		}
		for(i=304;i<337;i++)
			if(i!=331)
				sd->status.skill[i].id=i;
		if(battle_config.enable_upper_class){ //confで無効でなければ読み込む
			for(i=355;i<MAX_SKILL;i++)
				sd->status.skill[i].id=i;
		}
	}else{
		// 通常の計算
		do{
			flag=0;
			for(i=0;(id=skill_tree[s][c][i].id)>0;i++){
				int j,f=1;
				if(!battle_config.skillfree) {
					for(j=0;j<5;j++) {
						if( skill_tree[s][c][i].need[j].id &&
							pc_checkskill(sd,skill_tree[s][c][i].need[j].id) < skill_tree[s][c][i].need[j].lv)
							f=0;
					}
				}
				if(f && sd->status.skill[id].id==0 ){
					sd->status.skill[id].id=id;
					flag=1;
				}
			}
		}while(flag);
	}
//	if(battle_config.etc_log)
//		printf("calc skill_tree\n");
	return 0;
}

/*==========================================
 * 重量アイコンの確認
 *------------------------------------------
 */
int pc_checkweighticon(struct map_session_data *sd)
{
	int flag=0;

	nullpo_retr(0, sd);

	if(sd->weight*2 >= sd->max_weight)
		flag=1;
	if(sd->weight*10 >= sd->max_weight*9)
		flag=2;

	if(flag==1){
		if(sd->sc_data[SC_WEIGHT50].timer==-1)
			status_change_start(&sd->bl,SC_WEIGHT50,0,0,0,0,0,0);
	}else{
		status_change_end(&sd->bl,SC_WEIGHT50,-1);
	}
	if(flag==2){
		if(sd->sc_data[SC_WEIGHT90].timer==-1)
			status_change_start(&sd->bl,SC_WEIGHT90,0,0,0,0,0,0);
	}else{
		status_change_end(&sd->bl,SC_WEIGHT90,-1);
	}
	return 0;
}

/*==========================================
 * 装 備品による能力等のボーナス設定
 *------------------------------------------
 */
int pc_bonus(struct map_session_data *sd,int type,int val)
{
	nullpo_retr(0, sd);

	switch(type){
	case SP_STR:
	case SP_AGI:
	case SP_VIT:
	case SP_INT:
	case SP_DEX:
	case SP_LUK:
		if(sd->state.lr_flag != 2)
			sd->parame[type-SP_STR]+=val;
		break;
	case SP_ATK1:
		if(!sd->state.lr_flag)
			sd->watk+=val;
		else if(sd->state.lr_flag == 1)
			sd->watk_+=val;
		break;
	case SP_ATK2:
		if(!sd->state.lr_flag)
			sd->watk2+=val;
		else if(sd->state.lr_flag == 1)
			sd->watk_2+=val;
		break;
	case SP_BASE_ATK:
		if(sd->state.lr_flag != 2)
			sd->base_atk+=val;
		break;
	case SP_MATK1:
		if(sd->state.lr_flag != 2)
			sd->matk1 += val;
		break;
	case SP_MATK2:
		if(sd->state.lr_flag != 2)
			sd->matk2 += val;
		break;
	case SP_MATK:
		if(sd->state.lr_flag != 2) {
			sd->matk1 += val;
			sd->matk2 += val;
		}
		break;
	case SP_DEF1:
		if(sd->state.lr_flag != 2)
			sd->def+=val;
		break;
	case SP_MDEF1:
		if(sd->state.lr_flag != 2)
			sd->mdef+=val;
		break;
	case SP_MDEF2:
		if(sd->state.lr_flag != 2)
			sd->mdef+=val;
		break;
	case SP_HIT:
		if(sd->state.lr_flag != 2)
			sd->hit+=val;
		else
			sd->arrow_hit+=val;
		break;
	case SP_FLEE1:
		if(sd->state.lr_flag != 2)
			sd->flee+=val;
		break;
	case SP_FLEE2:
		if(sd->state.lr_flag != 2)
			sd->flee2+=val*10;
		break;
	case SP_CRITICAL:
		if(sd->state.lr_flag != 2)
			sd->critical+=val*10;
		else
			sd->arrow_cri += val*10;
		break;
	case SP_ATKELE:
		if(!sd->state.lr_flag)
			sd->atk_ele=val;
		else if(sd->state.lr_flag == 1)
			sd->atk_ele_=val;
		else if(sd->state.lr_flag == 2)
			sd->arrow_ele=val;
		break;
	case SP_DEFELE:
		if(sd->state.lr_flag != 2)
			sd->def_ele=val;
		break;
	case SP_MAXHP:
		if(sd->state.lr_flag != 2)
			sd->status.max_hp+=val;
		break;
	case SP_MAXSP:
		if(sd->state.lr_flag != 2)
			sd->status.max_sp+=val;
		break;
	case SP_CASTRATE:
		if(sd->state.lr_flag != 2)
			sd->castrate+=val;
		break;
	case SP_MAXHPRATE:
		if(sd->state.lr_flag != 2)
			sd->hprate+=val;
		break;
	case SP_MAXSPRATE:
		if(sd->state.lr_flag != 2)
			sd->sprate+=val;
		break;
	case SP_SPRATE:
		if(sd->state.lr_flag != 2)
			sd->dsprate+=val;
		break;
	case SP_ATTACKRANGE:
		if(!sd->state.lr_flag)
			sd->attackrange += val;
		else if(sd->state.lr_flag == 1)
			sd->attackrange_ += val;
		else if(sd->state.lr_flag == 2)
			sd->arrow_range += val;
		break;
	case SP_ADD_SPEED:
		if(sd->state.lr_flag != 2)
			sd->speed -= val;
		break;
	case SP_SPEED_RATE:
		if(sd->state.lr_flag != 2) {
			if(sd->speed_rate > 100-val)
				sd->speed_rate = 100-val;
		}
		break;
	case SP_SPEED_ADDRATE:
		if(sd->state.lr_flag != 2)
			sd->speed_add_rate = sd->speed_add_rate * (100-val)/100;
		break;
	case SP_ASPD:
		if(sd->state.lr_flag != 2)
			sd->aspd -= val*10;
		break;
	case SP_ASPD_RATE:
		if(sd->state.lr_flag != 2) {
			if(sd->aspd_rate > 100-val)
				sd->aspd_rate = 100-val;
		}
		break;
	case SP_ASPD_ADDRATE:
		if(sd->state.lr_flag != 2)
			sd->aspd_add_rate = sd->aspd_add_rate * (100-val)/100;
		break;
	case SP_HP_RECOV_RATE:
		if(sd->state.lr_flag != 2)
			sd->hprecov_rate += val;
		break;
	case SP_SP_RECOV_RATE:
		if(sd->state.lr_flag != 2)
			sd->sprecov_rate += val;
		break;
	case SP_CRITICAL_DEF:
		if(sd->state.lr_flag != 2)
			sd->critical_def += val;
		break;
	case SP_NEAR_ATK_DEF:
		if(sd->state.lr_flag != 2)
			sd->near_attack_def_rate += val;
		break;
	case SP_LONG_ATK_DEF:
		if(sd->state.lr_flag != 2)
			sd->long_attack_def_rate += val;
		break;
	case SP_DOUBLE_RATE:
		if(sd->state.lr_flag == 0 && sd->double_rate < val)
			sd->double_rate = val;
		break;
	case SP_DOUBLE_ADD_RATE:
		if(sd->state.lr_flag == 0)
			sd->double_add_rate += val;
		break;
	case SP_MATK_RATE:
		if(sd->state.lr_flag != 2)
			sd->matk_rate += val;
		break;
	case SP_IGNORE_DEF_ELE:
		if(!sd->state.lr_flag)
			sd->ignore_def_ele |= 1<<val;
		else if(sd->state.lr_flag == 1)
			sd->ignore_def_ele_ |= 1<<val;
		break;
	case SP_IGNORE_DEF_RACE:
		if(!sd->state.lr_flag)
			sd->ignore_def_race |= 1<<val;
		else if(sd->state.lr_flag == 1)
			sd->ignore_def_race_ |= 1<<val;
		break;
	case SP_ATK_RATE:
		if(sd->state.lr_flag != 2)
			sd->atk_rate += val;
		break;
	case SP_MAGIC_ATK_DEF:
		if(sd->state.lr_flag != 2)
			sd->magic_def_rate += val;
		break;
	case SP_MISC_ATK_DEF:
		if(sd->state.lr_flag != 2)
			sd->misc_def_rate += val;
		break;
	case SP_IGNORE_MDEF_ELE:
		if(sd->state.lr_flag != 2)
			sd->ignore_mdef_ele |= 1<<val;
		break;
	case SP_IGNORE_MDEF_RACE:
		if(sd->state.lr_flag != 2)
			sd->ignore_mdef_race |= 1<<val;
		break;
	case SP_PERFECT_HIT_RATE:
		if(sd->state.lr_flag != 2 && sd->perfect_hit < val)
			sd->perfect_hit = val;
		break;
	case SP_PERFECT_HIT_ADD_RATE:
		if(sd->state.lr_flag != 2)
			sd->perfect_hit_add += val;
		break;
	case SP_CRITICAL_RATE:
		if(sd->state.lr_flag != 2)
			sd->critical_rate+=val;
		break;
	case SP_GET_ZENY_NUM:
		if(sd->state.lr_flag != 2 && sd->get_zeny_num < val)
			sd->get_zeny_num = val;
		break;
	case SP_ADD_GET_ZENY_NUM:
		if(sd->state.lr_flag != 2)
			sd->get_zeny_add_num += val;
		break;
	case SP_DEF_RATIO_ATK_ELE:
		if(!sd->state.lr_flag)
			sd->def_ratio_atk_ele |= 1<<val;
		else if(sd->state.lr_flag == 1)
			sd->def_ratio_atk_ele_ |= 1<<val;
		break;
	case SP_DEF_RATIO_ATK_RACE:
		if(!sd->state.lr_flag)
			sd->def_ratio_atk_race |= 1<<val;
		else if(sd->state.lr_flag == 1)
			sd->def_ratio_atk_race_ |= 1<<val;
		break;
	case SP_HIT_RATE:
		if(sd->state.lr_flag != 2)
			sd->hit_rate += val;
		break;
	case SP_FLEE_RATE:
		if(sd->state.lr_flag != 2)
			sd->flee_rate += val;
		break;
	case SP_FLEE2_RATE:
		if(sd->state.lr_flag != 2)
			sd->flee2_rate += val;
		break;
	case SP_DEF_RATE:
		if(sd->state.lr_flag != 2)
			sd->def_rate += val;
		break;
	case SP_DEF2_RATE:
		if(sd->state.lr_flag != 2)
			sd->def2_rate += val;
		break;
	case SP_MDEF_RATE:
		if(sd->state.lr_flag != 2)
			sd->mdef_rate += val;
		break;
	case SP_MDEF2_RATE:
		if(sd->state.lr_flag != 2)
			sd->mdef2_rate += val;
		break;
	case SP_RESTART_FULL_RECORVER:
		if(sd->state.lr_flag != 2)
			sd->special_state.restart_full_recover = 1;
		break;
	case SP_NO_CASTCANCEL:
		if(sd->state.lr_flag != 2)
			sd->special_state.no_castcancel = 1;
		break;
	case SP_NO_CASTCANCEL2:
		if(sd->state.lr_flag != 2)
			sd->special_state.no_castcancel2 = 1;
		break;
	case SP_NO_SIZEFIX:
		if(sd->state.lr_flag != 2)
			sd->special_state.no_sizefix = 1;
		break;
	case SP_NO_MAGIC_DAMAGE:
		if(sd->state.lr_flag != 2)
			sd->special_state.no_magic_damage = 1;
		break;
	case SP_NO_WEAPON_DAMAGE:
		if(sd->state.lr_flag != 2)
			sd->special_state.no_weapon_damage = 1;
		break;
	case SP_NO_GEMSTONE:
		if(sd->state.lr_flag != 2)
			sd->special_state.no_gemstone = 1;
		break;
	case SP_INFINITE_ENDURE:
		if(sd->state.lr_flag != 2)
			sd->special_state.infinite_endure = 1;
		break;
	case SP_UNBREAKABLE_WEAPON:
		if(sd->state.lr_flag != 2)
			sd->unbreakable_equip |= EQP_WEAPON;
		break;
	case SP_UNBREAKABLE_ARMOR:
		if(sd->state.lr_flag != 2)
			sd->unbreakable_equip |= EQP_ARMOR;
		break;
	case SP_UNBREAKABLE_HELM:
		if(sd->state.lr_flag != 2)
			sd->unbreakable_equip |= EQP_HELM;
		break;
	case SP_UNBREAKABLE_SHIELD:
		if(sd->state.lr_flag != 2)
			sd->unbreakable_equip |= EQP_SHIELD;
		break;
	case SP_SPLASH_RANGE:
		if(sd->state.lr_flag != 2 && sd->splash_range < val)
			sd->splash_range = val;
		break;
	case SP_SPLASH_ADD_RANGE:
		if(sd->state.lr_flag != 2)
			sd->splash_add_range += val;
		break;
	case SP_SHORT_WEAPON_DAMAGE_RETURN:
		if(sd->state.lr_flag != 2)
			sd->short_weapon_damage_return += val;
		break;
	case SP_LONG_WEAPON_DAMAGE_RETURN:
		if(sd->state.lr_flag != 2)
			sd->long_weapon_damage_return += val;
		break;
	case SP_BREAK_WEAPON_RATE:
		if(sd->state.lr_flag != 2)
			sd->break_weapon_rate += val;
		break;
	case SP_BREAK_ARMOR_RATE:
		if(sd->state.lr_flag != 2)
			sd->break_armor_rate += val;
		break;
	case SP_ADD_STEAL_RATE:
		if(sd->state.lr_flag != 2)
			sd->add_steal_rate += val;
		break;

	default:
		if(battle_config.error_log)
			printf("pc_bonus: unknown type %d %d !\n",type,val);
		break;
	}

	return 0;
}

/*==========================================
 * 装 備品による能力等のボーナス設定
 *------------------------------------------
 */
int pc_bonus2(struct map_session_data *sd,int type,int type2,int val)
{
	int i;

	nullpo_retr(0, sd);

	switch(type){
	case SP_ADDELE:
		if(!sd->state.lr_flag)
			sd->addele[type2]+=val;
		else if(sd->state.lr_flag == 1)
			sd->addele_[type2]+=val;
		else if(sd->state.lr_flag == 2)
			sd->arrow_addele[type2]+=val;
		break;
	case SP_ADDRACE:
		if(!sd->state.lr_flag)
			sd->addrace[type2]+=val;
		else if(sd->state.lr_flag == 1)
			sd->addrace_[type2]+=val;
		else if(sd->state.lr_flag == 2)
			sd->arrow_addrace[type2]+=val;
		break;
	case SP_ADDSIZE:
		if(!sd->state.lr_flag)
			sd->addsize[type2]+=val;
		else if(sd->state.lr_flag == 1)
			sd->addsize_[type2]+=val;
		else if(sd->state.lr_flag == 2)
			sd->arrow_addsize[type2]+=val;
		break;
	case SP_SUBELE:
		if(sd->state.lr_flag != 2)
			sd->subele[type2]+=val;
		break;
	case SP_SUBRACE:
		if(sd->state.lr_flag != 2)
			sd->subrace[type2]+=val;
		break;
	case SP_ADDEFF:
		if(sd->state.lr_flag != 2)
			sd->addeff[type2]+=val;
		else
			sd->arrow_addeff[type2]+=val;
		break;
	case SP_ADDEFF2:
		if(sd->state.lr_flag != 2)
			sd->addeff2[type2]+=val;
		else
			sd->arrow_addeff2[type2]+=val;
		break;
	case SP_RESEFF:
		if(sd->state.lr_flag != 2)
			sd->reseff[type2]+=val;
		break;
	case SP_MAGIC_ADDELE:
		if(sd->state.lr_flag != 2)
			sd->magic_addele[type2]+=val;
		break;
	case SP_MAGIC_ADDRACE:
		if(sd->state.lr_flag != 2)
			sd->magic_addrace[type2]+=val;
		break;
	case SP_MAGIC_SUBRACE:
		if(sd->state.lr_flag != 2)
			sd->magic_subrace[type2]+=val;
		break;
	case SP_ADD_DAMAGE_CLASS:
		if(!sd->state.lr_flag) {
			for(i=0;i<sd->add_damage_class_count;i++) {
				if(sd->add_damage_classid[i] == type2) {
					sd->add_damage_classrate[i] += val;
					break;
				}
			}
			if(i >= sd->add_damage_class_count && sd->add_damage_class_count < 10) {
				sd->add_damage_classid[sd->add_damage_class_count] = type2;
				sd->add_damage_classrate[sd->add_damage_class_count] += val;
				sd->add_damage_class_count++;
			}
		}
		else if(sd->state.lr_flag == 1) {
			for(i=0;i<sd->add_damage_class_count_;i++) {
				if(sd->add_damage_classid_[i] == type2) {
					sd->add_damage_classrate_[i] += val;
					break;
				}
			}
			if(i >= sd->add_damage_class_count_ && sd->add_damage_class_count_ < 10) {
				sd->add_damage_classid_[sd->add_damage_class_count_] = type2;
				sd->add_damage_classrate_[sd->add_damage_class_count_] += val;
				sd->add_damage_class_count_++;
			}
		}
		break;
	case SP_ADD_MAGIC_DAMAGE_CLASS:
		if(sd->state.lr_flag != 2) {
			for(i=0;i<sd->add_magic_damage_class_count;i++) {
				if(sd->add_magic_damage_classid[i] == type2) {
					sd->add_magic_damage_classrate[i] += val;
					break;
				}
			}
			if(i >= sd->add_magic_damage_class_count && sd->add_magic_damage_class_count < 10) {
				sd->add_magic_damage_classid[sd->add_magic_damage_class_count] = type2;
				sd->add_magic_damage_classrate[sd->add_magic_damage_class_count] += val;
				sd->add_magic_damage_class_count++;
			}
		}
		break;
	case SP_ADD_DEF_CLASS:
		if(sd->state.lr_flag != 2) {
			for(i=0;i<sd->add_def_class_count;i++) {
				if(sd->add_def_classid[i] == type2) {
					sd->add_def_classrate[i] += val;
					break;
				}
			}
			if(i >= sd->add_def_class_count && sd->add_def_class_count < 10) {
				sd->add_def_classid[sd->add_def_class_count] = type2;
				sd->add_def_classrate[sd->add_def_class_count] += val;
				sd->add_def_class_count++;
			}
		}
		break;
	case SP_ADD_MDEF_CLASS:
		if(sd->state.lr_flag != 2) {
			for(i=0;i<sd->add_mdef_class_count;i++) {
				if(sd->add_mdef_classid[i] == type2) {
					sd->add_mdef_classrate[i] += val;
					break;
				}
			}
			if(i >= sd->add_mdef_class_count && sd->add_mdef_class_count < 10) {
				sd->add_mdef_classid[sd->add_mdef_class_count] = type2;
				sd->add_mdef_classrate[sd->add_mdef_class_count] += val;
				sd->add_mdef_class_count++;
			}
		}
		break;
	case SP_HP_DRAIN_RATE:
		if(!sd->state.lr_flag) {
			sd->hp_drain_rate += type2;
			sd->hp_drain_per += val;
		}
		else if(sd->state.lr_flag == 1) {
			sd->hp_drain_rate_ += type2;
			sd->hp_drain_per_ += val;
		}
		break;
	case SP_HP_DRAIN_VALUE:
		if(!sd->state.lr_flag) {
			sd->hp_drain_rate += type2;
			sd->hp_drain_value += val;
		}
		else if(sd->state.lr_flag == 1) {
			sd->hp_drain_rate_ += type2;
			sd->hp_drain_value_ += val;
		}
		break;
	case SP_SP_DRAIN_RATE:
		if(!sd->state.lr_flag) {
			sd->sp_drain_rate += type2;
			sd->sp_drain_per += val;
		}
		else if(sd->state.lr_flag == 1) {
			sd->sp_drain_rate_ += type2;
			sd->sp_drain_per_ += val;
		}
		break;
	case SP_SP_DRAIN_VALUE:
		if(!sd->state.lr_flag) {
			sd->sp_drain_rate += type2;
			sd->sp_drain_value += val;
		}
		else if(sd->state.lr_flag == 1) {
			sd->sp_drain_rate_ += type2;
			sd->sp_drain_value_ += val;
		}
		break;
	case SP_WEAPON_COMA_ELE:
		if(sd->state.lr_flag != 2)
			sd->weapon_coma_ele[type2] += val;
		break;
	case SP_WEAPON_COMA_RACE:
		if(sd->state.lr_flag != 2)
			sd->weapon_coma_race[type2] += val;
		break;
	case SP_WEAPON_ATK:
		if(sd->state.lr_flag != 2)
			sd->weapon_atk[type2]+=val;
		break;
	case SP_WEAPON_ATK_RATE:
		if(sd->state.lr_flag != 2)
			sd->weapon_atk_rate[type2]+=val;
		break;
	default:
		if(battle_config.error_log)
			printf("pc_bonus2: unknown type %d %d %d!\n",type,type2,val);
		break;
	}

	return 0;
}

int pc_bonus3(struct map_session_data *sd,int type,int type2,int type3,int val)
{
	int i;
	switch(type){
	case SP_ADD_MONSTER_DROP_ITEM:
		if(sd->state.lr_flag != 2) {
			for(i=0;i<sd->monster_drop_item_count;i++) {
				if(sd->monster_drop_itemid[i] == type2) {
					sd->monster_drop_race[i] |= 1<<type3;
					if(sd->monster_drop_itemrate[i] < val)
						sd->monster_drop_itemrate[i] = val;
					break;
				}
			}
			if(i >= sd->monster_drop_item_count && sd->monster_drop_item_count < 10) {
				sd->monster_drop_itemid[sd->monster_drop_item_count] = type2;
				sd->monster_drop_race[sd->monster_drop_item_count] |= 1<<type3;
				sd->monster_drop_itemrate[sd->monster_drop_item_count] = val;
				sd->monster_drop_item_count++;
			}
		}
		break;
	case SP_AUTOSPELL:
		if(sd->state.lr_flag != 2){
			sd->autospell_id = type2;
			sd->autospell_lv = type3;
			sd->autospell_rate = val;
		}
		break;
	default:
		if(battle_config.error_log)
			printf("pc_bonus3: unknown type %d %d %d %d!\n",type,type2,type3,val);
		break;
	}

	return 0;
}

/*==========================================
 * スクリプトによるスキル所得
 *------------------------------------------
 */
int pc_skill(struct map_session_data *sd,int id,int level,int flag)
{
	nullpo_retr(0, sd);

	if(level>MAX_SKILL_LEVEL){
		if(battle_config.error_log)
			printf("support card skill only!\n");
		return 0;
	}
	if(!flag && (sd->status.skill[id].id == id || level == 0)){	// クエスト所得ならここで条件を確認して送信する
		sd->status.skill[id].lv=level;
		status_calc_pc(sd,0);
		clif_skillinfoblock(sd);
	}
	else if(sd->status.skill[id].lv < level){	// 覚えられるがlvが小さいなら
		if(sd->status.skill[id].id==id)
			sd->status.skill[id].flag=sd->status.skill[id].lv+2;	// lvを記憶
		else {
			sd->status.skill[id].id=id;
			sd->status.skill[id].flag=1;	// cardスキルとする
		}
		sd->status.skill[id].lv=level;
	}

	return 0;
}

/*==========================================
 * カード挿入
 *------------------------------------------
 */
int pc_insert_card(struct map_session_data *sd,int idx_card,int idx_equip)
{
	nullpo_retr(0, sd);

	if(idx_card >= 0 && idx_card < MAX_INVENTORY && idx_equip >= 0 && idx_equip < MAX_INVENTORY && sd->inventory_data[idx_card]) {
		int i;
		int nameid=sd->status.inventory[idx_equip].nameid;
		int cardid=sd->status.inventory[idx_card].nameid;
		int ep=sd->inventory_data[idx_card]->equip;

		if( nameid <= 0 || sd->inventory_data[idx_equip] == NULL ||
			(sd->inventory_data[idx_equip]->type!=4 && sd->inventory_data[idx_equip]->type!=5)||	// 装 備じゃない
			( sd->status.inventory[idx_equip].identify==0 ) ||		// 未鑑定
			( sd->status.inventory[idx_equip].card[0]==0x00ff) ||		// 製造武器
			( sd->status.inventory[idx_equip].card[0]==0x00fe) ||
			( (sd->inventory_data[idx_equip]->equip&ep)==0 ) ||					// 装 備個所違い
			( sd->inventory_data[idx_equip]->type==4 && ep==32) ||			// 両 手武器と盾カード
			( sd->status.inventory[idx_equip].card[0]==(short)0xff00) || sd->status.inventory[idx_equip].equip){

			clif_insert_card(sd,idx_equip,idx_card,1);
			return 0;
		}
		for(i=0;i<sd->inventory_data[idx_equip]->slot;i++){
			if( sd->status.inventory[idx_equip].card[i] == 0){
			// 空きスロットがあったので差し込む
				sd->status.inventory[idx_equip].card[i]=cardid;

			// カードは減らす
				clif_insert_card(sd,idx_equip,idx_card,0);
				pc_delitem(sd,idx_card,1,1);
				return 0;
			}
		}
	}
	else
		clif_insert_card(sd,idx_equip,idx_card,1);

	return 0;
}

//
// アイテム物
//


/*==========================================
 * スキルによる買い値修正
 *------------------------------------------
 */
int pc_modifybuyvalue(struct map_session_data *sd,int orig_value)
{
	int skill,val = orig_value,rate1 = 0,rate2 = 0;
	if((skill=pc_checkskill(sd,MC_DISCOUNT))>0)	// ディスカウント
		rate1 = 5+skill*2-((skill==10)? 1:0);
	if((skill=pc_checkskill(sd,RG_COMPULSION))>0)	// コムパルションディスカウント
		rate2 = 5+skill*4;
	if(rate1 < rate2) rate1 = rate2;
	if(rate1)
		val = (int)((double)orig_value*(double)(100-rate1)/100.);
	if(val < 0) val = 0;
	if(orig_value > 0 && val < 1) val = 1;

	return val;
}


/*==========================================
 * スキルによる売り値修正
 *------------------------------------------
 */
int pc_modifysellvalue(struct map_session_data *sd,int orig_value)
{
	int skill,val = orig_value,rate = 0;
	if((skill=pc_checkskill(sd,MC_OVERCHARGE))>0)	// オーバーチャージ
		rate = 5+skill*2-((skill==10)? 1:0);
	if(rate)
		val = (int)((double)orig_value*(double)(100+rate)/100.);
	if(val < 0) val = 0;
	if(orig_value > 0 && val < 1) val = 1;

	return val;
}


/*==========================================
 * アイテムを買った時に、新しいアイテム欄を使うか、
 * 3万個制限にかかるか確認
 *------------------------------------------
 */
int pc_checkadditem(struct map_session_data *sd,int nameid,int amount)
{
	int i;

	nullpo_retr(0, sd);

	if(itemdb_isequip(nameid))
		return ADDITEM_NEW;

	for(i=0;i<MAX_INVENTORY;i++){
		if(sd->status.inventory[i].nameid==nameid){
			if(sd->status.inventory[i].amount+amount > MAX_AMOUNT)
				return ADDITEM_OVERAMOUNT;
			return ADDITEM_EXIST;
		}
	}

	if(amount > MAX_AMOUNT)
		return ADDITEM_OVERAMOUNT;
	return ADDITEM_NEW;
}


/*==========================================
 * 空きアイテム欄の個数
 *------------------------------------------
 */
int pc_inventoryblank(struct map_session_data *sd)
{
	int i,b;

	nullpo_retr(0, sd);

	for(i=0,b=0;i<MAX_INVENTORY;i++){
		if(sd->status.inventory[i].nameid==0)
			b++;
	}

	return b;
}


/*==========================================
 * お金を払う
 *------------------------------------------
 */
int pc_payzeny(struct map_session_data *sd,int zeny)
{
	double z;

	nullpo_retr(0, sd);

	z = (double)sd->status.zeny;
	if(sd->status.zeny<zeny || z - (double)zeny > MAX_ZENY)
		return 1;
	sd->status.zeny-=zeny;
	clif_updatestatus(sd,SP_ZENY);

	return 0;
}


/*==========================================
 * お金を得る
 *------------------------------------------
 */
int pc_getzeny(struct map_session_data *sd,int zeny)
{
	double z;

	nullpo_retr(0, sd);

	z = (double)sd->status.zeny;
	if(z + (double)zeny > MAX_ZENY) {
		zeny = 0;
		sd->status.zeny = MAX_ZENY;
	}
	sd->status.zeny+=zeny;
	clif_updatestatus(sd,SP_ZENY);

	return 0;
}


/*==========================================
 * アイテムを探して、インデックスを返す
 *------------------------------------------
 */
int pc_search_inventory(struct map_session_data *sd,int item_id)
{
	int i;

	nullpo_retr(-1, sd);

	for(i=0;i<MAX_INVENTORY;i++) {
		if(sd->status.inventory[i].nameid == item_id &&
		 (sd->status.inventory[i].amount > 0 || item_id == 0))
			return i;
	}

	return -1;
}


/*==========================================
 * アイテム追加。個数のみitem構造体の数字を無視
 *------------------------------------------
 */
int pc_additem(struct map_session_data *sd,struct item *item_data,int amount)
{
	struct item_data *data;
	int i,w;

	nullpo_retr(1, sd);
	nullpo_retr(1, item_data);

	if(item_data->nameid <= 0 || amount <= 0)
		return 1;
	data = itemdb_search(item_data->nameid);
	if((w = data->weight*amount) + sd->weight > sd->max_weight)
		return 2;

	i = MAX_INVENTORY;

	if(!itemdb_isequip2(data)){
		// 装 備品ではないので、既所有品なら個数のみ変化させる
		for(i=0;i<MAX_INVENTORY;i++)
		if(sd->status.inventory[i].nameid == item_data->nameid &&
			sd->status.inventory[i].card[0] == item_data->card[0] && sd->status.inventory[i].card[1] == item_data->card[1] &&
			sd->status.inventory[i].card[2] == item_data->card[2] && sd->status.inventory[i].card[3] == item_data->card[3]) {
			if(sd->status.inventory[i].amount+amount > MAX_AMOUNT)
				return 5;
			sd->status.inventory[i].amount+=amount;
			clif_additem(sd,i,amount,0);
			break;
		}
	}
	if(i >= MAX_INVENTORY){
		// 装 備品か未所有品だったので空き欄へ追加
		i = pc_search_inventory(sd,0);
		if(i >= 0) {
			memcpy(&sd->status.inventory[i],item_data,sizeof(sd->status.inventory[0]));
			sd->status.inventory[i].amount=amount;
			sd->inventory_data[i]=data;
			clif_additem(sd,i,amount,0);
		}
		else return 4;
	}
	sd->weight += w;
	clif_updatestatus(sd,SP_WEIGHT);

	return 0;
}


/*==========================================
 * アイテムを減らす
 *------------------------------------------
 */
int pc_delitem(struct map_session_data *sd,int n,int amount,int type)
{
	nullpo_retr(1, sd);

	if(sd->status.inventory[n].nameid==0 || amount <= 0 || sd->status.inventory[n].amount<amount || sd->inventory_data[n] == NULL)
		return 1;

	sd->status.inventory[n].amount -= amount;
	sd->weight -= sd->inventory_data[n]->weight*amount ;
	if(sd->status.inventory[n].amount<=0){
		if(sd->status.inventory[n].equip)
			pc_unequipitem(sd,n,0);
		memset(&sd->status.inventory[n],0,sizeof(sd->status.inventory[0]));
		sd->inventory_data[n] = NULL;
	}
	if(!(type&1))
		clif_delitem(sd,n,amount);
	if(!(type&2))
		clif_updatestatus(sd,SP_WEIGHT);

	return 0;
}


/*==========================================
 * アイテムを落す
 *------------------------------------------
 */
int pc_dropitem(struct map_session_data *sd,int n,int amount)
{
	nullpo_retr(1, sd);

	if (n < 0 || n >= MAX_INVENTORY)
		return 1;

	if (amount <= 0)
		return 1;

	if(sd->status.inventory[n].nameid <=0 || sd->status.inventory[n].amount < amount)
		return 1;

	if(itemdb_isdropable(sd->status.inventory[n].nameid) == 0)
		return 1;
	if(pc_candrop(sd,sd->status.inventory[n].nameid))
		return 1;

	map_addflooritem(&sd->status.inventory[n],amount,sd->bl.m,sd->bl.x,sd->bl.y,NULL,NULL,NULL,0);
	pc_delitem(sd,n,amount,0);

	return 0;
}


/*==========================================
 * アイテムを拾う
 *------------------------------------------
 */
int pc_takeitem(struct map_session_data *sd,struct flooritem_data *fitem)
{
	int flag;
	unsigned int tick = gettick();
	struct map_session_data *first_sd = NULL,*second_sd = NULL,*third_sd = NULL;

	nullpo_retr(0, sd);
	nullpo_retr(0, fitem);

	if(fitem->first_get_id > 0) {
		first_sd = map_id2sd(fitem->first_get_id);
		if(tick < fitem->first_get_tick) {
			if(fitem->first_get_id != sd->bl.id && !(first_sd && first_sd->status.party_id == sd->status.party_id)) {
				clif_additem(sd,0,0,6);
				return 0;
			}
		}
		else if(fitem->second_get_id > 0) {
			second_sd = map_id2sd(fitem->second_get_id);
			if(tick < fitem->second_get_tick) {
				if(fitem->first_get_id != sd->bl.id && fitem->second_get_id != sd->bl.id &&
					!(first_sd && first_sd->status.party_id == sd->status.party_id) && !(second_sd && second_sd->status.party_id == sd->status.party_id)) {
					clif_additem(sd,0,0,6);
					return 0;
				}
			}
			else if(fitem->third_get_id > 0) {
				third_sd = map_id2sd(fitem->third_get_id);
				if(tick < fitem->third_get_tick) {
					if(fitem->first_get_id != sd->bl.id && fitem->second_get_id != sd->bl.id && fitem->third_get_id != sd->bl.id &&
						!(first_sd && first_sd->status.party_id == sd->status.party_id) && !(second_sd && second_sd->status.party_id == sd->status.party_id) &&
						!(third_sd && third_sd->status.party_id == sd->status.party_id)) {
						clif_additem(sd,0,0,6);
						return 0;
					}
				}
			}
		}
	}
	if((flag = pc_additem(sd,&fitem->item_data,fitem->item_data.amount)))
		// 重量overで取得失敗
		clif_additem(sd,0,0,flag);
	else {
		/* 取得成功 */
		if(sd->attacktimer != -1)
			pc_stopattack(sd);
		clif_takeitem(&sd->bl,&fitem->bl);
		map_clearflooritem(fitem->bl.id);
	}
	return 0;
}

int pc_isUseitem(struct map_session_data *sd,int n)
{
	struct item_data *item;
	int nameid;
	struct pc_base_job s_class;

	nullpo_retr(0, sd);

	item = sd->inventory_data[n];
	nameid = sd->status.inventory[n].nameid;

	//転生や養子の場合の元の職業を算出する
	s_class = pc_calc_base_job(sd->status.class);
	if(item == NULL)
		return 0;
	if(item->type != 0 && item->type != 2)
		return 0;
	if((nameid == 605) && map[sd->bl.m].flag.gvg)
		return 0;
	if(nameid == 601 && (map[sd->bl.m].flag.noteleport || map[sd->bl.m].flag.gvg)) {
		clif_skill_teleportmessage(sd,0);
		return 0;
	}
	if(nameid == 602 && map[sd->bl.m].flag.noreturn)
		return 0;
	if(nameid == 604 && (map[sd->bl.m].flag.nobranch || map[sd->bl.m].flag.gvg))
		return 0;
	if(item->sex != 2 && sd->status.sex != item->sex)
		return 0;
	if(item->elv > 0 && sd->status.base_level < item->elv)
		return 0;
	if(((1<<s_class.job)&item->class) == 0)
		return 0;
	return 1;
}

/*==========================================
 * アイテムを使う
 *------------------------------------------
 */
int pc_useitem(struct map_session_data *sd,int n)
{
	int nameid,amount;

	nullpo_retr(1, sd);

	if(n >=0 && n < MAX_INVENTORY) {
		nameid = sd->status.inventory[n].nameid;
		amount = sd->status.inventory[n].amount;
		if(sd->status.inventory[n].nameid <= 0 ||
			sd->status.inventory[n].amount <= 0 ||
			!pc_isUseitem(sd,n) ) {
			clif_useitemack(sd,n,0,0);
			return 1;
		}
		if(sd->inventory_data[n])
			run_script(sd->inventory_data[n]->use_script,0,sd->bl.id,0);

		amount = sd->status.inventory[n].amount;
		clif_useitemack(sd,n,amount-1,1);
		pc_delitem(sd,n,1,1);
	}

	return 0;
}


/*==========================================
 * カートアイテム追加。個数のみitem構造体の数字を無視
 *------------------------------------------
 */
int pc_cart_additem(struct map_session_data *sd,struct item *item_data,int amount)
{
	struct item_data *data;
	int i,w;

	nullpo_retr(1, sd);
	nullpo_retr(1, item_data);

	if(item_data->nameid <= 0 || amount <= 0)
		return 1;
	data = itemdb_search(item_data->nameid);

	if((w=data->weight*amount) + sd->cart_weight > sd->cart_max_weight)
		return 1;

	i=MAX_CART;
	if(!itemdb_isequip2(data)){
		// 装 備品ではないので、既所有品なら個数のみ変化させる
		for(i=0;i<MAX_CART;i++){
			if(sd->status.cart[i].nameid==item_data->nameid &&
				sd->status.cart[i].card[0] == item_data->card[0] && sd->status.cart[i].card[1] == item_data->card[1] &&
				sd->status.cart[i].card[2] == item_data->card[2] && sd->status.cart[i].card[3] == item_data->card[3]){
				if(sd->status.cart[i].amount+amount > MAX_AMOUNT)
					return 1;
				sd->status.cart[i].amount+=amount;
				clif_cart_additem(sd,i,amount,0);
				break;
			}
		}
	}
	if(i >= MAX_CART){
		// 装 備品か未所有品だったので空き欄へ追加
		for(i=0;i<MAX_CART;i++){
			if(sd->status.cart[i].nameid==0){
				memcpy(&sd->status.cart[i],item_data,sizeof(sd->status.cart[0]));
				sd->status.cart[i].amount=amount;
				sd->cart_num++;
				clif_cart_additem(sd,i,amount,0);
				break;
			}
		}
		if(i >= MAX_CART)
			return 1;
	}
	sd->cart_weight += w;
	clif_updatestatus(sd,SP_CARTINFO);

	return 0;
}


/*==========================================
 * カートアイテムを減らす
 *------------------------------------------
 */
int pc_cart_delitem(struct map_session_data *sd,int n,int amount,int type)
{
	nullpo_retr(1, sd);

	if(sd->status.cart[n].nameid==0 ||
	   sd->status.cart[n].amount<amount)
		return 1;

	sd->status.cart[n].amount -= amount;
	sd->cart_weight -= itemdb_weight(sd->status.cart[n].nameid)*amount ;
	if(sd->status.cart[n].amount <= 0){
		memset(&sd->status.cart[n],0,sizeof(sd->status.cart[0]));
		sd->cart_num--;
	}
	if(!type) {
		clif_cart_delitem(sd,n,amount);
		clif_updatestatus(sd,SP_CARTINFO);
	}

	return 0;
}


/*==========================================
 * カートへアイテム移動
 *------------------------------------------
 */
int pc_putitemtocart(struct map_session_data *sd,int idx,int amount)
{
	struct item *item_data;

	nullpo_retr(0, sd);
	nullpo_retr(0, item_data=&sd->status.inventory[idx]);

	if(itemdb_isdropable(sd->status.inventory[idx].nameid) == 0)
		return 1;
	if(pc_candrop(sd,sd->status.inventory[idx].nameid)==1)
		return 1;

	if( item_data->nameid==0 || item_data->amount<amount || sd->vender_id )
		return 1;
	if(pc_cart_additem(sd,item_data,amount)==0)
		return pc_delitem(sd,idx,amount,0);

	return 1;
}

/*==========================================
 * カート内のアイテム数確認(個数の差分を返す)
 *------------------------------------------
 */
int pc_cartitem_amount(struct map_session_data *sd,int idx,int amount)
{
	struct item *item_data;

	nullpo_retr(-1, sd);
	nullpo_retr(-1, item_data=&sd->status.cart[idx]);

	if( item_data->nameid==0 || !item_data->amount)
		return -1;
	return item_data->amount-amount;
}
/*==========================================
 * カートからアイテム移動
 *------------------------------------------
 */

int pc_getitemfromcart(struct map_session_data *sd,int idx,int amount)
{
	struct item *item_data;
	int flag;

	nullpo_retr(0, sd);
	nullpo_retr(0, item_data=&sd->status.cart[idx]);


	if( item_data->nameid==0 || item_data->amount<amount || sd->vender_id )
		return 1;
	if((flag = pc_additem(sd,item_data,amount)) == 0)
		return pc_cart_delitem(sd,idx,amount,0);

	clif_additem(sd,0,0,flag);
	return 1;
}


/*==========================================
 * アイテム鑑定
 *------------------------------------------
 */
int pc_item_identify(struct map_session_data *sd,int idx)
{
	int flag=1;

	nullpo_retr(0, sd);

	if(idx >= 0 && idx < MAX_INVENTORY) {
		if(sd->status.inventory[idx].nameid > 0 && sd->status.inventory[idx].identify == 0 ){
			flag=0;
			sd->status.inventory[idx].identify=1;
		}
		clif_item_identified(sd,idx,flag);
	}
	else
		clif_item_identified(sd,idx,flag);

	return !flag;
}

/*==========================================
 * スティル品公開
 *------------------------------------------
 */
int pc_show_steal(struct block_list *bl,va_list ap)
{
	struct map_session_data *sd;
	int itemid;
	int type;

	struct item_data *item=NULL;
	char output[100];

	nullpo_retr(0, bl);
	nullpo_retr(0, ap);
	nullpo_retr(0, sd=va_arg(ap,struct map_session_data *));

	itemid=va_arg(ap,int);
	type=va_arg(ap,int);

	if(!type){
		if((item=itemdb_exists(itemid))==NULL)
			sprintf(output,"%sがUnknown_Itemをスティールしました",sd->status.name);
		else
			sprintf(output,"%sが%sをスティールしました",sd->status.name,item->jname);
		clif_displaymessage( ((struct map_session_data *)bl)->fd, output);
	}else{
		sprintf(output,"%sは重量オーバーによりアイテムを取得できませんでした",sd->status.name);
		clif_displaymessage( ((struct map_session_data *)bl)->fd, output);
	}

	return 0;
}
/*==========================================
 *
 *------------------------------------------
 */
int pc_steal_item(struct map_session_data *sd,struct block_list *bl)
{
	if(sd != NULL && bl != NULL && bl->type == BL_MOB) {
		int i,skill,rate,itemid,flag;
		struct mob_data *md=(struct mob_data *)bl;
		if( md && !md->state.steal_flag && mob_db[md->class].mexp <= 0 && !(mob_db[md->class].mode&0x20) &&
			md->sc_data && md->sc_data[SC_STONE].timer == -1 && md->sc_data[SC_FREEZE].timer == -1) {
			skill = sd->paramc[4] - mob_db[md->class].dex + pc_checkskill(sd,TF_STEAL) * 3 + 10;
			if(0 < skill) {
				for(i=0;i<8;i++) {
					itemid = mob_db[md->class].dropitem[i].nameid;
					if(itemid > 0 && itemdb_type(itemid) != 6) {
						rate = (mob_db[md->class].dropitem[i].p /battle_config.item_rate * 100 * skill)/100;
						rate += sd->add_steal_rate;
						if(rand()%10000 < rate) {
							struct item tmp_item;
							memset(&tmp_item,0,sizeof(tmp_item));
							tmp_item.nameid = itemid;
							tmp_item.amount = 1;
							tmp_item.identify = 1;
							flag = pc_additem(sd,&tmp_item,1);
							if(battle_config.show_steal_in_same_party)
								party_foreachsamemap(pc_show_steal,sd,1,sd,tmp_item.nameid,0);
							if(flag){
								if(battle_config.show_steal_in_same_party)
									party_foreachsamemap(pc_show_steal,sd,1,sd,tmp_item.nameid,1);
								clif_additem(sd,0,0,flag);
							}
							md->state.steal_flag = 1;
							return 1;
						}
					}
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
int pc_steal_coin(struct map_session_data *sd,struct block_list *bl)
{
	if(sd != NULL && bl != NULL && bl->type == BL_MOB) {
		int rate,skill;
		struct mob_data *md=(struct mob_data *)bl;
		if(md && !md->state.steal_coin_flag && md->sc_data && md->sc_data[SC_STONE].timer == -1 && md->sc_data[SC_FREEZE].timer == -1) {
			skill = pc_checkskill(sd,RG_STEALCOIN)*10;
			rate = skill + (sd->status.base_level - mob_db[md->class].lv)*3 + sd->paramc[4]*2 + sd->paramc[5]*2;
			if(rand()%1000 < rate) {
				pc_getzeny(sd,mob_db[md->class].lv*10 + rand()%100);
				md->state.steal_coin_flag = 1;
				return 1;
			}
		}
	}

	return 0;
}
//
//
//
/*==========================================
 * PCの位置設定
 *------------------------------------------
 */
int pc_setpos(struct map_session_data *sd,char *mapname_org,int x,int y,int clrtype)
{
	char mapname[24];
	int m;

	nullpo_retr(0, sd);

	if(sd->chatID)	// チャットから出る
		chat_leavechat(sd);
	if(sd->trade_partner)	// 取引を中断する
		trade_tradecancel(sd);
	if(sd->state.storage_flag)
		storage_guild_storage_quit(sd,0);
	else
		storage_storage_quit(sd);	// 倉庫を開いてるなら保存する

	if(sd->party_invite>0)	// パーティ勧誘を拒否する
		party_reply_invite(sd,sd->party_invite_account,0);
	if(sd->guild_invite>0)	// ギルド勧誘を拒否する
		guild_reply_invite(sd,sd->guild_invite,0);
	if(sd->guild_alliance>0)	// ギルド同盟勧誘を拒否する
		guild_reply_reqalliance(sd,sd->guild_alliance_account,0);

	skill_castcancel(&sd->bl,0);	// 詠唱中断
	pc_stop_walking(sd,0);		// 歩行中断
	pc_stopattack(sd);			// 攻撃中断

	if(sd->status.pet_id > 0 && sd->pd && sd->pet.intimate > 0) {
		pet_stopattack(sd->pd);
		pet_changestate(sd->pd,MS_IDLE,0);
	}
	if(sd->sc_data[SC_BLADESTOP].timer!=-1)
		status_change_end(&sd->bl,SC_BLADESTOP,-1);

	memcpy(mapname,mapname_org,24);
	mapname[16]=0;
	if(strstr(mapname,".gat")==NULL && strlen(mapname)<16){
		strcat(mapname,".gat");
	}
	m=map_mapname2mapid(mapname);
	if(m<0){
		if(sd->mapname[0]){
			int ip,port;
			if(map_mapname2ipport(mapname,&ip,&port)==0){
				skill_stop_dancing(&sd->bl,1);
				skill_unit_out_all(&sd->bl,gettick(),1);
				clif_clearchar_area(&sd->bl,clrtype&0xffff);
				skill_gangsterparadise(sd,0);
				map_delblock(&sd->bl);
				if(sd->status.pet_id > 0 && sd->pd) {
					if(sd->pd->bl.m != m && sd->pet.intimate <= 0) {
						pet_remove_map(sd);
						intif_delete_petdata(sd->status.pet_id);
						sd->status.pet_id = 0;
						sd->pd = NULL;
						sd->petDB = NULL;
						if(battle_config.pet_status_support)
							status_calc_pc(sd,2);
					}
					else if(sd->pet.intimate > 0) {
						pet_stopattack(sd->pd);
						pet_changestate(sd->pd,MS_IDLE,0);
						clif_clearchar_area(&sd->pd->bl,clrtype&0xffff);
						map_delblock(&sd->pd->bl);
					}
				}
				memcpy(sd->mapname,mapname,24);
				sd->bl.x=x;
				sd->bl.y=y;
				sd->state.waitingdisconnect=1;
				pc_makesavestatus(sd);
				if(sd->status.pet_id > 0 && sd->pd)
					intif_save_petdata(sd->status.account_id,&sd->pet);
				chrif_save(sd);
				storage_storage_save(sd);
				storage_delete(sd->status.account_id);
				chrif_changemapserver(sd,mapname,x,y,ip,port);
				return 0;
			}
		}
#if 0
		clif_authfail_fd(sd->fd,0);	// cancel
		clif_setwaitclose(sd->fd);
#endif
		return 1;
	}

	if(x <0 || x >= map[m].xs || y <0 || y >= map[m].ys)
		x=y=0;
	if((x==0 && y==0) || map_getcell(m,x,y,CELL_CHKNOPASS)){
		if(x||y) {
			if(battle_config.error_log)
				printf("stacked (%d,%d)\n",x,y);
		}
		do {
			x=rand()%(map[m].xs-2)+1;
			y=rand()%(map[m].ys-2)+1;
		} while(map_getcell(m,x,y,CELL_CHKNOPASS));
	}

	if(sd->mapname[0] && sd->bl.prev != NULL){
		skill_unit_out_all(&sd->bl,gettick(),1);
		clif_clearchar_area(&sd->bl,clrtype&0xffff);
		skill_gangsterparadise(sd,0);
		map_delblock(&sd->bl);
		// pet
		if(sd->status.pet_id > 0 && sd->pd) {
			if(sd->pd->bl.m != m && sd->pet.intimate <= 0) {
				pet_remove_map(sd);
				intif_delete_petdata(sd->status.pet_id);
				sd->status.pet_id = 0;
				sd->pd = NULL;
				sd->petDB = NULL;
				if(battle_config.pet_status_support)
					status_calc_pc(sd,2);
				pc_makesavestatus(sd);
				chrif_save(sd);
				storage_storage_save(sd);
			}
			else if(sd->pet.intimate > 0) {
				pet_stopattack(sd->pd);
				pet_changestate(sd->pd,MS_IDLE,0);
				clif_clearchar_area(&sd->pd->bl,clrtype&0xffff);
				map_delblock(&sd->pd->bl);
			}
		}
		clif_changemap(sd,mapname,x,y);
	}

	memcpy(sd->mapname,mapname,24);
	sd->bl.m = m;
	sd->to_x = x;
	sd->to_y = y;

	skill_stop_dancing(&sd->bl, 2); //移動先にユニットを移動するかどうかの判断もする

	sd->bl.x =  x;
	sd->bl.y =  y;

	if(sd->status.pet_id > 0 && sd->pd && sd->pet.intimate > 0) {
		sd->pd->bl.m = m;
		sd->pd->bl.x = sd->pd->to_x = x;
		sd->pd->bl.y = sd->pd->to_y = y;
		sd->pd->dir = sd->dir;
	}

//	map_addblock(&sd->bl);	/// ブロック登録とspawnは
//	clif_spawnpc(sd);		// clif_parse_LoadEndAckで行う

	return 0;
}


/*==========================================
 * PCのランダムワープ
 *------------------------------------------
 */
int pc_randomwarp(struct map_session_data *sd,int type)
{
	int x,y,i=0;
	int m;

	nullpo_retr(0, sd);

	m=sd->bl.m;

	if(map[sd->bl.m].flag.noteleport)	// テレポート禁止
		return 0;

	do{
		x=rand()%(map[m].xs-2)+1;
		y=rand()%(map[m].ys-2)+1;
	}while( map_getcell(m,x,y,CELL_CHKNOPASS)&& (i++)<1000 );

	if(i<1000)
		pc_setpos(sd,map[m].name,x,y,type);

	return 0;
}


/*==========================================
 * 現在位置のメモ
 *------------------------------------------
 */
int pc_memo(struct map_session_data *sd,int i)
{
	int skill;
	int j;

	nullpo_retr(0, sd);

	skill=pc_checkskill(sd,AL_WARP);

	if(i>=MIN_PORTAL_MEMO)
		i-=MIN_PORTAL_MEMO;
	else if(map[sd->bl.m].flag.nomemo){
		clif_skill_teleportmessage(sd,1);
		return 0;
	}

	if(skill < 1) {
		clif_skill_memo(sd,2);
	}

	if(skill<2 || i<-1 || i>2){
		clif_skill_memo(sd,1);
		return 0;
	}

	for(j=0;j<3;j++){
		if(strcmp(sd->status.memo_point[j].map,map[sd->bl.m].name)==0){
			i=j;
			break;
		}
	}

	if(i==-1){
		for(i=skill-3;i>=0;i--){
			memcpy(&sd->status.memo_point[i+1],&sd->status.memo_point[i],
				sizeof(struct point));
		}
		i=0;
	}
	memcpy(sd->status.memo_point[i].map,map[sd->bl.m].name,24);
	sd->status.memo_point[i].x=sd->bl.x;
	sd->status.memo_point[i].y=sd->bl.y;

	clif_skill_memo(sd,0);

	return 1;
}

/*==========================================
 *
 *------------------------------------------
 */
int pc_can_reach(struct map_session_data *sd,int x,int y)
{
	struct walkpath_data wpd;

	nullpo_retr(0, sd);

	if( sd->bl.x==x && sd->bl.y==y )	// 同じマス
		return 1;

	// 障害物判定
	wpd.path_len=0;
	wpd.path_pos=0;
	wpd.path_half=0;
	return (path_search(&wpd,sd->bl.m,sd->bl.x,sd->bl.y,x,y,0)!=-1)?1:0;
}

//
// 歩 行物
//
/*==========================================
 * 次の1歩にかかる時間を計算
 *------------------------------------------
 */
static int calc_next_walk_step(struct map_session_data *sd)
{
	nullpo_retr(0, sd);

	if(sd->walkpath.path_pos>=sd->walkpath.path_len)
		return -1;
	if(sd->walkpath.path[sd->walkpath.path_pos]&1)
		return sd->speed*14/10;

	return sd->speed;
}


/*==========================================
 * 半歩進む(timer関数)
 *------------------------------------------
 */
static int pc_walk(int tid,unsigned int tick,int id,int data)
{
	struct map_session_data *sd;
	int i;
	int moveblock;
	int x,y,dx,dy;

	sd=map_id2sd(id);
	if(sd==NULL)
		return 0;

	if(sd->walktimer != tid){
		if(battle_config.error_log)
			printf("pc_walk %d != %d\n",sd->walktimer,tid);
		return 0;
	}
	sd->walktimer=-1;
	if(sd->walkpath.path_pos>=sd->walkpath.path_len || sd->walkpath.path_pos!=data)
		return 0;

	//歩いたので息吹のタイマーを初期化
	sd->inchealspirithptick = 0;
	sd->inchealspiritsptick = 0;

	sd->walkpath.path_half ^= 1;
	if(sd->walkpath.path_half==0){ // マス目中心へ到着
		sd->walkpath.path_pos++;
		if(sd->state.change_walk_target){
			pc_walktoxy_sub(sd);
			return 0;
		}
	} else { // マス目境界へ到着
		if(sd->walkpath.path[sd->walkpath.path_pos]>=8)
			return 1;

		x = sd->bl.x;
		y = sd->bl.y;
		if(map_getcell(sd->bl.m,x,y,CELL_CHKNOPASS))
		{
			pc_stop_walking(sd,1);
			return 0;
		}
		sd->dir=sd->head_dir=sd->walkpath.path[sd->walkpath.path_pos];
		dx = dirx[(int)sd->dir];
		dy = diry[(int)sd->dir];
		if(map_getcell(sd->bl.m,x,y,CELL_CHKNOPASS))
		{
			pc_walktoxy_sub(sd);
			return 0;
		}

		moveblock = ( x/BLOCK_SIZE != (x+dx)/BLOCK_SIZE || y/BLOCK_SIZE != (y+dy)/BLOCK_SIZE);

		sd->walktimer = 1;
		map_foreachinmovearea(clif_pcoutsight,sd->bl.m,x-AREA_SIZE,y-AREA_SIZE,x+AREA_SIZE,y+AREA_SIZE,dx,dy,0,sd);

		x += dx;
		y += dy;

		if(moveblock) map_delblock(&sd->bl);
		sd->bl.x = x;
		sd->bl.y = y;
		if(moveblock) map_addblock(&sd->bl);

		if(sd->sc_data[SC_DANCING].timer!=-1)
			skill_unit_move_unit_group((struct skill_unit_group *)sd->sc_data[SC_DANCING].val2,sd->bl.m,dx,dy);

		map_foreachinmovearea(clif_pcinsight,sd->bl.m,x-AREA_SIZE,y-AREA_SIZE,x+AREA_SIZE,y+AREA_SIZE,-dx,-dy,0,sd);
		sd->walktimer = -1;

		if(sd->status.party_id>0){	// パーティのＨＰ情報通知検査
			struct party *p=party_search(sd->status.party_id);
			if(p!=NULL){
				int p_flag=0;
				map_foreachinmovearea(party_send_hp_check,sd->bl.m,x-AREA_SIZE,y-AREA_SIZE,x+AREA_SIZE,y+AREA_SIZE,-dx,-dy,BL_PC,sd->status.party_id,&p_flag);
				if(p_flag)
					sd->party_hp=-1;
			}
		}
		if(sd->status.option&4)	// クローキングの消滅検査
			skill_check_cloaking(&sd->bl);
		/* ディボーション検査 */
		for(i=0;i<5;i++)
			if(sd->dev.val1[i]){
				skill_devotion3(&sd->bl,sd->dev.val1[i]);
				break;
			}
		/* 被ディボーション検査 */
		if( sd->sc_data && sd->sc_data[SC_DEVOTION].val1){
				skill_devotion2(&sd->bl,sd->sc_data[SC_DEVOTION].val1);
		}

		skill_unit_move(&sd->bl,tick,1);	// スキルユニットの検査

		if(map_getcell(sd->bl.m,x,y,CELL_CHKNPC))
			npc_touch_areanpc(sd,sd->bl.m,x,y);
		else
			sd->areanpc_id=0;
	}
	if((i=calc_next_walk_step(sd))>0) {
		i = i>>1;
		if(i < 1 && sd->walkpath.path_half == 0)
			i = 1;
		sd->walktimer=add_timer(tick+i,pc_walk,id,sd->walkpath.path_pos);
	}
	if(battle_config.disp_hpmeter)
		clif_hpmeter(sd);

	return 0;
}


/*==========================================
 * 移動可能か確認して、可能なら歩行開始
 *------------------------------------------
 */
static int pc_walktoxy_sub(struct map_session_data *sd)
{
	struct walkpath_data wpd;
	int i;

	nullpo_retr(1, sd);

	if(path_search(&wpd,sd->bl.m,sd->bl.x,sd->bl.y,sd->to_x,sd->to_y,0))
		return 1;
	memcpy(&sd->walkpath,&wpd,sizeof(wpd));

	clif_walkok(sd);
	sd->state.change_walk_target=0;

	if((i=calc_next_walk_step(sd))>0){
		i = i>>2;
		sd->walktimer=add_timer(gettick()+i,pc_walk,sd->bl.id,0);
	}
	clif_movechar(sd);

	return 0;
}


/*==========================================
 * pc歩 行要求
 *------------------------------------------
 */
int pc_walktoxy(struct map_session_data *sd,int x,int y)
{

	nullpo_retr(0, sd);

	sd->to_x=x;
	sd->to_y=y;

	if(sd->walktimer != -1 && sd->state.change_walk_target==0){
		// 現在歩いている最中の目的地変更なのでマス目の中心に来た時に
		// timer関数からpc_walktoxy_subを呼ぶようにする
		sd->state.change_walk_target=1;
	} else {
		pc_walktoxy_sub(sd);
	}

	return 0;
}

/*==========================================
 * 歩 行停止
 *------------------------------------------
 */
int pc_stop_walking(struct map_session_data *sd,int type)
{
	nullpo_retr(0, sd);

	if(sd->walktimer != -1) {
		delete_timer(sd->walktimer,pc_walk);
		sd->walktimer=-1;
	}
	sd->walkpath.path_len=0;
	sd->to_x = sd->bl.x;
	sd->to_y = sd->bl.y;
	if(type&0x01)
		clif_fixpos(&sd->bl);
	if(type&0x02 && battle_config.pc_damage_delay) {
		unsigned int tick = gettick();
		int delay = status_get_dmotion(&sd->bl);
		if(sd->canmove_tick < tick)
			sd->canmove_tick = tick + delay;
	}

	return 0;
}


/*==========================================
 *
 *------------------------------------------
 */
int pc_movepos(struct map_session_data *sd,int dst_x,int dst_y)
{
	int moveblock;
	int dx,dy,dist;

	struct walkpath_data wpd;

	nullpo_retr(0, sd);

	if(path_search(&wpd,sd->bl.m,sd->bl.x,sd->bl.y,dst_x,dst_y,0))
		return 1;

	sd->dir = sd->head_dir = map_calc_dir(&sd->bl, dst_x,dst_y);

	dx = dst_x - sd->bl.x;
	dy = dst_y - sd->bl.y;
	dist = distance(sd->bl.x,sd->bl.y,dst_x,dst_y);

	moveblock = ( sd->bl.x/BLOCK_SIZE != dst_x/BLOCK_SIZE || sd->bl.y/BLOCK_SIZE != dst_y/BLOCK_SIZE);

	map_foreachinmovearea(clif_pcoutsight,sd->bl.m,sd->bl.x-AREA_SIZE,sd->bl.y-AREA_SIZE,sd->bl.x+AREA_SIZE,sd->bl.y+AREA_SIZE,dx,dy,0,sd);

	if(moveblock) map_delblock(&sd->bl);
	sd->bl.x = dst_x;
	sd->bl.y = dst_y;
	if(moveblock) map_addblock(&sd->bl);

	map_foreachinmovearea(clif_pcinsight,sd->bl.m,sd->bl.x-AREA_SIZE,sd->bl.y-AREA_SIZE,sd->bl.x+AREA_SIZE,sd->bl.y+AREA_SIZE,-dx,-dy,0,sd);

	if(sd->status.party_id>0){	// パーティのＨＰ情報通知検査
		struct party *p=party_search(sd->status.party_id);
		if(p!=NULL){
			int flag=0;
			map_foreachinmovearea(party_send_hp_check,sd->bl.m,sd->bl.x-AREA_SIZE,sd->bl.y-AREA_SIZE,sd->bl.x+AREA_SIZE,sd->bl.y+AREA_SIZE,-dx,-dy,BL_PC,sd->status.party_id,&flag);
			if(flag)
				sd->party_hp=-1;
		}
	}

	if(sd->status.option&4)	// クローキングの消滅検査
		skill_check_cloaking(&sd->bl);

	skill_unit_move(&sd->bl,gettick(),dist+7);	// スキルユニットの検査

	if(map_getcell(sd->bl.m,sd->bl.x,sd->bl.y,CELL_CHKNPC))
		npc_touch_areanpc(sd,sd->bl.m,sd->bl.x,sd->bl.y);
	else
		sd->areanpc_id=0;

	return 0;
}

//
// 武器戦闘
//
/*==========================================
 * スキルの検索 所有していた場合Lvが返る
 *------------------------------------------
 */
int pc_checkskill(struct map_session_data *sd,int skill_id)
{
	if(sd == NULL) return 0;
	if( skill_id>=10000 ){
		struct guild *g;
		if( sd->status.guild_id>0 && (g=guild_search(sd->status.guild_id))!=NULL)
			return guild_checkskill(g,skill_id);
		return 0;
	}

	if(sd->status.skill[skill_id].id == skill_id)
		return (sd->status.skill[skill_id].lv);

	return 0;
}

/*==========================================
 * 武器変更によるスキルの継続チェック
 * 引数：
 *   struct map_session_data *sd	セッションデータ
 *   int nameid						装備品ID
 * 返り値：
 *   0		変更なし
 *   -1		スキルを解除
 *------------------------------------------
 */
int pc_checkallowskill(struct map_session_data *sd)
{
	nullpo_retr(0, sd);

	if( sd->sc_data == NULL )
		return 0;

	if(!(skill_get_weapontype(KN_TWOHANDQUICKEN)&(1<<sd->status.weapon)) && sd->sc_data[SC_TWOHANDQUICKEN].timer!=-1) {	// 2HQ
		status_change_end(&sd->bl,SC_TWOHANDQUICKEN,-1);	// 2HQを解除
		return -1;
	}
	if(!(skill_get_weapontype(LK_AURABLADE)&(1<<sd->status.weapon)) && sd->sc_data[SC_AURABLADE].timer!=-1) {	/* オーラブレード */
		status_change_end(&sd->bl,SC_AURABLADE,-1);	/* オーラブレードを解除 */
		return -1;
	}
	if(!(skill_get_weapontype(LK_PARRYING)&(1<<sd->status.weapon)) && sd->sc_data[SC_PARRYING].timer!=-1) {	/* パリイング */
		status_change_end(&sd->bl,SC_PARRYING,-1);	/* パリイングを解除 */
		return -1;
	}
	if(!(skill_get_weapontype(LK_CONCENTRATION)&(1<<sd->status.weapon)) && sd->sc_data[SC_CONCENTRATION].timer!=-1) {	/* コンセントレーション */
		status_change_end(&sd->bl,SC_CONCENTRATION,-1);	/* コンセントレーションを解除 */
		return -1;
	}
	if(!(skill_get_weapontype(CR_SPEARQUICKEN)&(1<<sd->status.weapon)) && sd->sc_data[SC_SPEARSQUICKEN].timer!=-1){	// スピアクィッケン
		status_change_end(&sd->bl,SC_SPEARSQUICKEN,-1);	// スピアクイッケンを解除
		return -1;
	}
	if(!(skill_get_weapontype(BS_ADRENALINE)&(1<<sd->status.weapon)) && sd->sc_data[SC_ADRENALINE].timer!=-1){	// アドレナリンラッシュ
		status_change_end(&sd->bl,SC_ADRENALINE,-1);	// アドレナリンラッシュを解除
		return -1;
	}

	if(sd->status.shield <= 0) {
		if(sd->sc_data[SC_AUTOGUARD].timer!=-1){	// オートガード
			status_change_end(&sd->bl,SC_AUTOGUARD,-1);
			return -1;
		}
		if(sd->sc_data[SC_DEFENDER].timer!=-1){	// ディフェンダー
			status_change_end(&sd->bl,SC_DEFENDER,-1);
			return -1;
		}
		if(sd->sc_data[SC_REFLECTSHIELD].timer!=-1){ //リフレクトシールド
			status_change_end(&sd->bl,SC_REFLECTSHIELD,-1);
			return -1;
		}
	}

	return 0;
}


/*==========================================
 * 装 備品のチェック
 *------------------------------------------
 */
int pc_checkequip(struct map_session_data *sd,int pos)
{
	int i;

	nullpo_retr(-1, sd);

	for(i=0;i<11;i++){
		if(pos & equip_pos[i])
			return sd->equip_index[i];
	}

	return -1;
}

/*==========================================
 * 転生職や養子職の元の職業を返す
 *------------------------------------------
 */
struct pc_base_job pc_calc_base_job(int b_class)
{
	struct pc_base_job bj;
	//転生や養子の場合の元の職業を算出する
	if(b_class < MAX_PC_CLASS){ //通常
		bj.job = b_class;
		bj.upper = 0;
	}else if(b_class >= PC_CLASS_BASE2 && b_class < PC_CLASS_BASE3){ //転生職
		bj.job = b_class - PC_CLASS_BASE2;
		bj.upper = 1;
	}else if(b_class == 23 + PC_CLASS_BASE3 -1){ //養子スパノビ
		bj.job = b_class - (PC_CLASS_BASE3 - 1);
		bj.upper = 2;
	}else{ //養子スパノビ以外の養子
		bj.job = b_class - PC_CLASS_BASE3;
		bj.upper = 2;
	}

	if(battle_config.enable_upper_class==0){ //confで無効になっていたらupper=0
		bj.upper = 0;
	}

	if(bj.job == 0){
		bj.type = 0;
	}else if(bj.job < 7){
		bj.type = 1;
	}else{
		bj.type = 2;
	}
	
	return bj;
}


/*==========================================
 * PCの攻撃 (timer関数)
 *------------------------------------------
 */
int pc_attack_timer(int tid,unsigned int tick,int id,int data)
{
	struct map_session_data *sd;
	struct block_list *bl;
	struct status_change *sc_data;
	short *opt;
	int dist,skill,range;

	sd=map_id2sd(id);
	if(sd == NULL)
		return 0;
	if(sd->attacktimer != tid){
		if(battle_config.error_log)
			printf("pc_attack_timer %d != %d\n",sd->attacktimer,tid);
		return 0;
	}
	sd->attacktimer=-1;

	if(sd->bl.prev == NULL)
		return 0;

	bl=map_id2bl(sd->attacktarget);
	if(bl==NULL || bl->prev == NULL)
		return 0;

	if(bl->type == BL_PC && pc_isdead((struct map_session_data *)bl))
		return 0;

	// 同じmapでないなら攻撃しない
	// PCが死んでても攻撃しない
	if(sd->bl.m != bl->m || pc_isdead(sd))
		return 0;

	if( sd->opt1>0 || sd->status.option&2)	// 異常などで攻撃できない
		return 0;

	if(sd->sc_data[SC_AUTOCOUNTER].timer != -1)
		return 0;
	if(sd->sc_data[SC_BLADESTOP].timer != -1)
		return 0;

	if((opt = status_get_option(bl)) != NULL && *opt&0x46)
		return 0;
	if((sc_data = status_get_sc_data(bl)) != NULL && sc_data[SC_TRICKDEAD].timer != -1)
		return 0;

	if(sd->skilltimer != -1 && pc_checkskill(sd,SA_FREECAST) <= 0)
		return 0;

	if(!battle_config.sdelay_attack_enable && pc_checkskill(sd,SA_FREECAST) <= 0) {
		if(DIFF_TICK(tick , sd->canact_tick) < 0) {
			clif_skill_fail(sd,1,4,0);
			return 0;
		}
	}

	dist = distance(sd->bl.x,sd->bl.y,bl->x,bl->y);
	range = sd->attackrange;
	if(sd->status.weapon != 11) range++;
	if( dist > range ){	// 届 かないので移動
		if(pc_can_reach(sd,bl->x,bl->y))
			clif_movetoattack(sd,bl);
		return 0;
	}

	if(dist <= range && !battle_check_range(&sd->bl,bl,range) ) {
		if(pc_can_reach(sd,bl->x,bl->y) && sd->canmove_tick < tick && sd->sc_data[SC_ANKLE].timer == -1)
			pc_walktoxy(sd,bl->x,bl->y);
		sd->attackabletime = tick + (sd->aspd<<1);
	}
	else {
		if(battle_config.pc_attack_direction_change)
			sd->dir=sd->head_dir=map_calc_dir(&sd->bl, bl->x,bl->y );	// 向き設定

		if(sd->walktimer != -1)
			pc_stop_walking(sd,1);

		if(sd->sc_data[SC_COMBO].timer == -1) {
			map_freeblock_lock();
			pc_stop_walking(sd,0);
			sd->attacktarget_lv = battle_weapon_attack(&sd->bl,bl,tick,0);
			if(!(battle_config.pc_cloak_check_type&2) && sd->sc_data[SC_CLOAKING].timer != -1)
				status_change_end(&sd->bl,SC_CLOAKING,-1);
			if(sd->status.pet_id > 0 && sd->pd && sd->petDB && battle_config.pet_attack_support)
				pet_target_check(sd,bl,0);
			map_freeblock_unlock();
			if(sd->skilltimer != -1 && (skill = pc_checkskill(sd,SA_FREECAST)) > 0 ) // フリーキャスト
				sd->attackabletime = tick + ((sd->aspd<<1)*(150 - skill*5)/100);
			else
				sd->attackabletime = tick + (sd->aspd<<1);
		}
		else if(sd->attackabletime <= tick) {
			if(sd->skilltimer != -1 && (skill = pc_checkskill(sd,SA_FREECAST)) > 0 ) // フリーキャスト
				sd->attackabletime = tick + ((sd->aspd<<1)*(150 - skill*5)/100);
			else
				sd->attackabletime = tick + (sd->aspd<<1);
		}
		if(sd->attackabletime <= tick) sd->attackabletime = tick + (battle_config.max_aspd<<1);
	}

	if(sd->state.attack_continue) {
		sd->attacktimer=add_timer(sd->attackabletime,pc_attack_timer,sd->bl.id,0);
	}

	return 0;
}


/*==========================================
 * 攻撃要求
 * typeが1なら継続攻撃
 *------------------------------------------
 */
int pc_attack(struct map_session_data *sd,int target_id,int type)
{
	struct block_list *bl;
	int d;

	nullpo_retr(0, sd);

	bl=map_id2bl(target_id);
	if(bl==NULL)
		return 1;
	if(!battle_check_target(&sd->bl,bl,BCT_ENEMY))
		return 1;
	if(sd->attacktimer != -1)
		pc_stopattack(sd);
	sd->attacktarget=target_id;
	sd->state.attack_continue=type;

	d=DIFF_TICK(sd->attackabletime,gettick());
	if(d>0 && d<2000){	// 攻撃delay中
		sd->attacktimer=add_timer(sd->attackabletime,pc_attack_timer,sd->bl.id,0);
	} else {
		// 本来timer関数なので引数を合わせる
		pc_attack_timer(-1,gettick(),sd->bl.id,0);
	}

	return 0;
}


/*==========================================
 * 継続攻撃停止
 *------------------------------------------
 */
int pc_stopattack(struct map_session_data *sd)
{
	nullpo_retr(0, sd);

	if(sd->attacktimer != -1) {
		delete_timer(sd->attacktimer,pc_attack_timer);
		sd->attacktimer=-1;
	}
	sd->attacktarget=0;
	sd->state.attack_continue=0;

	return 0;
}


int pc_checkbaselevelup(struct map_session_data *sd)
{
	int next = pc_nextbaseexp(sd);

	nullpo_retr(0, sd);

	if(sd->status.base_exp >= next && next > 0){
		struct pc_base_job 	s_class = pc_calc_base_job(sd->status.class);

		// base側レベルアップ処理
		sd->status.base_exp -= next;

		sd->status.base_level ++;
		sd->status.status_point += (sd->status.base_level+14) / 5 ;
		clif_updatestatus(sd,SP_STATUSPOINT);
		clif_updatestatus(sd,SP_BASELEVEL);
		clif_updatestatus(sd,SP_NEXTBASEEXP);
		status_calc_pc(sd,0);
		pc_heal(sd,sd->status.max_hp,sd->status.max_sp);

		//スパノビはキリエ、イムポ、マニピ、グロ、サフラがかかる
		if(s_class.job == 23){
			status_change_start(&sd->bl,SkillStatusChangeTable[PR_KYRIE],10,0,0,0,skill_get_time(PR_KYRIE,10),0 );
			status_change_start(&sd->bl,SkillStatusChangeTable[PR_IMPOSITIO],5,0,0,0,skill_get_time(PR_IMPOSITIO,5),0 );
			status_change_start(&sd->bl,SkillStatusChangeTable[PR_MAGNIFICAT],5,0,0,0,skill_get_time(PR_MAGNIFICAT,5),0 );
			status_change_start(&sd->bl,SkillStatusChangeTable[PR_GLORIA],5,0,0,0,skill_get_time(PR_GLORIA,5),0 );
			status_change_start(&sd->bl,SkillStatusChangeTable[PR_SUFFRAGIUM],3,0,0,0,skill_get_time(PR_SUFFRAGIUM,3),0 );
		}

		clif_misceffect(&sd->bl,0);
		//レベルアップしたのでパーティー情報を更新する
		//(公平範囲チェック)
		party_send_movemap(sd);
		return 1;
	}

	return 0;
}


int pc_checkjoblevelup(struct map_session_data *sd)
{
	int next = pc_nextjobexp(sd);

	nullpo_retr(0, sd);

	if(sd->status.job_exp >= next && next > 0){
		// job側レベルアップ処理
		sd->status.job_exp -= next;
		sd->status.job_level ++;
		clif_updatestatus(sd,SP_JOBLEVEL);
		clif_updatestatus(sd,SP_NEXTJOBEXP);
		sd->status.skill_point ++;
		clif_updatestatus(sd,SP_SKILLPOINT);
		status_calc_pc(sd,0);

		clif_misceffect(&sd->bl,1);
		return 1;
	}

	return 0;
}


/*==========================================
 * 経験値取得
 *------------------------------------------
 */
int pc_gainexp(struct map_session_data *sd,int base_exp,int job_exp)
{
	char output[128];
	nullpo_retr(0, sd);

	if(sd->bl.prev == NULL || pc_isdead(sd))
		return 0;

	if(sd->sc_data[SC_RICHMANKIM].timer != -1) {
		base_exp += base_exp*(25 + sd->sc_data[SC_RICHMANKIM].val1*25)/100;
		job_exp += job_exp*(25 + sd->sc_data[SC_RICHMANKIM].val1*25)/100;
	}

	if(sd->status.guild_id>0){	// ギルドに上納
		base_exp-=guild_payexp(sd,base_exp);
		if(base_exp < 0)
			base_exp = 0;
	}

	sd->status.base_exp += base_exp;
	if(sd->status.base_exp < 0)
		sd->status.base_exp = 0;
	while(pc_checkbaselevelup(sd)) ;

	clif_updatestatus(sd,SP_BASEEXP);

	sd->status.job_exp += job_exp;
	if(sd->status.job_exp < 0)
		sd->status.job_exp = 0;
	while(pc_checkjoblevelup(sd)) ;

	clif_updatestatus(sd,SP_JOBEXP);
	
	if(battle_config.disp_experience){
		snprintf(output, sizeof output,
			"Base:%d Job:%d の経験値を獲得",base_exp,job_exp);
		clif_disp_onlyself(sd,output,strlen(output));
	}

	return 0;
}

/*==========================================
 * base level側必要経験値計算
 *------------------------------------------
 */
int pc_nextbaseexp(struct map_session_data *sd)
{
	int i;

	nullpo_retr(0, sd);

	if(sd->status.base_level>=MAX_LEVEL || sd->status.base_level<=0)
		return 0;

	if(sd->status.class==0) i=0;
	else if(sd->status.class<=6) i=1;
	else if(sd->status.class<=22) i=2;
	else if(sd->status.class==23) i=3;
	else if(sd->status.class==PC_CLASS_BASE2) i=4;
	else if(sd->status.class<=PC_CLASS_BASE2 + 6) i=5;
	else i=6;

	return exp_table[i][sd->status.base_level-1];
}

/*==========================================
 * job level側必要経験値計算
 *------------------------------------------
 */
int pc_nextjobexp(struct map_session_data *sd)
{
	int i;

	nullpo_retr(0, sd);

	if(sd->status.job_level>=MAX_LEVEL || sd->status.job_level<=0)
		return 0;

	if(sd->status.class==0) i=7;
	else if(sd->status.class<=6) i=8;
	else if(sd->status.class<=22) i=9;
	else if(sd->status.class==23) i=10;
	else if(sd->status.class==PC_CLASS_BASE2) i=11;
	else if(sd->status.class<=PC_CLASS_BASE2 + 6) i=12;
	else i=13;

	return exp_table[i][sd->status.job_level-1];
}

/*==========================================
 * 必要ステータスポイント計算
 *------------------------------------------
 */
int pc_need_status_point(struct map_session_data *sd,int type)
{
	int val;

	nullpo_retr(-1, sd);

	if(type<SP_STR || type>SP_LUK)
		return -1;
	val =
		type==SP_STR ? sd->status.str :
		type==SP_AGI ? sd->status.agi :
		type==SP_VIT ? sd->status.vit :
		type==SP_INT ? sd->status.int_:
		type==SP_DEX ? sd->status.dex : sd->status.luk;

	return (val+9)/10+1;
}


/*==========================================
 * 能力値成長
 *------------------------------------------
 */
int pc_statusup(struct map_session_data *sd,int type)
{
	int need,val;

	nullpo_retr(0, sd);

	need=pc_need_status_point(sd,type);
	if(type<SP_STR || type>SP_LUK || need<0 || need>sd->status.status_point){
		clif_statusupack(sd,type,0,0);
		return 1;
	}
	switch(type){
	case SP_STR:
		if(sd->status.str >= battle_config.max_parameter) {
			clif_statusupack(sd,type,0,0);
			return 1;
		}
		val= ++sd->status.str;
		break;
	case SP_AGI:
		if(sd->status.agi >= battle_config.max_parameter) {
			clif_statusupack(sd,type,0,0);
			return 1;
		}
		val= ++sd->status.agi;
		break;
	case SP_VIT:
		if(sd->status.vit >= battle_config.max_parameter) {
			clif_statusupack(sd,type,0,0);
			return 1;
		}
		val= ++sd->status.vit;
		break;
	case SP_INT:
		if(sd->status.int_ >= battle_config.max_parameter) {
			clif_statusupack(sd,type,0,0);
			return 1;
		}
		val= ++sd->status.int_;
		break;
	case SP_DEX:
		if(sd->status.dex >= battle_config.max_parameter) {
			clif_statusupack(sd,type,0,0);
			return 1;
		}
		val= ++sd->status.dex;
		break;
	case SP_LUK:
		if(sd->status.luk >= battle_config.max_parameter) {
			clif_statusupack(sd,type,0,0);
			return 1;
		}
		val= ++sd->status.luk;
		break;
	}
	sd->status.status_point-=need;
	if(need!=pc_need_status_point(sd,type)){
		clif_updatestatus(sd,type-SP_STR+SP_USTR);
	}
	clif_updatestatus(sd,SP_STATUSPOINT);
	clif_updatestatus(sd,type);
	status_calc_pc(sd,0);
	clif_statusupack(sd,type,1,val);

	return 0;
}

/*==========================================
 * 能力値成長
 *------------------------------------------
 */
int pc_statusup2(struct map_session_data *sd,int type,int val)
{
	nullpo_retr(0, sd);

	if(type<SP_STR || type>SP_LUK){
		clif_statusupack(sd,type,0,0);
		return 1;
	}
	switch(type){
	case SP_STR:
		if(sd->status.str + val >= battle_config.max_parameter)
			val = battle_config.max_parameter;
		else if(sd->status.str + val < 1)
			val = 1;
		else
			val += sd->status.str;
		sd->status.str = val;
		break;
	case SP_AGI:
		if(sd->status.agi + val >= battle_config.max_parameter)
			val = battle_config.max_parameter;
		else if(sd->status.agi + val < 1)
			val = 1;
		else
			val += sd->status.agi;
		sd->status.agi = val;
		break;
	case SP_VIT:
		if(sd->status.vit + val >= battle_config.max_parameter)
			val = battle_config.max_parameter;
		else if(sd->status.vit + val < 1)
			val = 1;
		else
			val += sd->status.vit;
		sd->status.vit = val;
		break;
	case SP_INT:
		if(sd->status.int_ + val >= battle_config.max_parameter)
			val = battle_config.max_parameter;
		else if(sd->status.int_ + val < 1)
			val = 1;
		else
			val += sd->status.int_;
		sd->status.int_ = val;
		break;
	case SP_DEX:
		if(sd->status.dex + val >= battle_config.max_parameter)
			val = battle_config.max_parameter;
		else if(sd->status.dex + val < 1)
			val = 1;
		else
			val += sd->status.dex;
		sd->status.dex = val;
		break;
	case SP_LUK:
		if(sd->status.luk + val >= battle_config.max_parameter)
			val = battle_config.max_parameter;
		else if(sd->status.luk + val < 1)
			val = 1;
		else
			val = sd->status.luk + val;
		sd->status.luk = val;
		break;
	}
	clif_updatestatus(sd,type-SP_STR+SP_USTR);
	clif_updatestatus(sd,type);
	status_calc_pc(sd,0);
	clif_statusupack(sd,type,1,val);

	return 0;
}

/*==========================================
 * スキルポイント割り振り
 *------------------------------------------
 */
int pc_skillup(struct map_session_data *sd,int skill_num)
{
	nullpo_retr(0, sd);

	if( skill_num>=10000 ){
		guild_skillup(sd,skill_num,0);
		return 0;
	}

	if( sd->status.skill_point>0 &&
		sd->status.skill[skill_num].id!=0 &&
		sd->status.skill[skill_num].lv < skill_get_max(skill_num) )
	{
		sd->status.skill[skill_num].lv++;
		sd->status.skill_point--;
		status_calc_pc(sd,0);
		clif_skillup(sd,skill_num);
		clif_updatestatus(sd,SP_SKILLPOINT);
		clif_skillinfoblock(sd);
	}

	return 0;
}

/*==========================================
 * /allskill
 *------------------------------------------
 */
int pc_allskillup(struct map_session_data *sd)
{
	int i,id;
	int c=0, s=0;
	//転生や養子の場合の元の職業を算出する
	struct pc_base_job s_class;

	nullpo_retr(0, sd);

	s_class = pc_calc_base_job(sd->status.class);
	c = s_class.job;
	s = (s_class.upper==1) ? 1 : 0 ; //転生以外は通常のスキル？

	for(i=0;i<MAX_SKILL;i++){
		sd->status.skill[i].id=0;
		if (sd->status.skill[i].flag && sd->status.skill[i].flag != 13){	// cardスキルなら、
			sd->status.skill[i].lv=(sd->status.skill[i].flag==1)?0:sd->status.skill[i].flag-2;	// 本当のlvに
			sd->status.skill[i].flag=0;	// flagは0にしておく
		}
	}

	if (battle_config.gm_allskill > 0 && pc_isGM(sd) >= battle_config.gm_allskill){
		// 全てのスキル
		for(i=1;i<158;i++)
			sd->status.skill[i].lv=skill_get_max(i);
		for(i=210;i<291;i++)
			sd->status.skill[i].lv=skill_get_max(i);
		if(battle_config.gm_allskill_addabra) {
			for(i=291;i<304;i++)
				sd->status.skill[i].lv=skill_get_max(i);
		}
		for(i=304;i<MAX_SKILL;i++)
			sd->status.skill[i].lv=skill_get_max(i);
	}
	else {
		for(i=0;(id=skill_tree[s][c][i].id)>0;i++){
			if(sd->status.skill[id].id==0 && (!(skill_get_inf2(id)&0x01) || battle_config.quest_skill_learn) )
				sd->status.skill[id].lv=skill_get_max(id);
		}
	}
	status_calc_pc(sd,0);

	return 0;
}

/*==========================================
 * /resetstate
 *------------------------------------------
 */
int pc_resetstate(struct map_session_data* sd)
{
	#define sumsp(a) ((a)*((a-2)/10+2) - 5*((a-2)/10)*((a-2)/10) - 6*((a-2)/10) -2)
	int add=0;

	nullpo_retr(0, sd);

	add += sumsp(sd->status.str);
	add += sumsp(sd->status.agi);
	add += sumsp(sd->status.vit);
	add += sumsp(sd->status.int_);
	add += sumsp(sd->status.dex);
	add += sumsp(sd->status.luk);
	sd->status.status_point+=add;

	clif_updatestatus(sd,SP_STATUSPOINT);

	sd->status.str=1;
	sd->status.agi=1;
	sd->status.vit=1;
	sd->status.int_=1;
	sd->status.dex=1;
	sd->status.luk=1;

	clif_updatestatus(sd,SP_STR);
	clif_updatestatus(sd,SP_AGI);
	clif_updatestatus(sd,SP_VIT);
	clif_updatestatus(sd,SP_INT);
	clif_updatestatus(sd,SP_DEX);
	clif_updatestatus(sd,SP_LUK);

	status_calc_pc(sd,0);

	return 0;
}


/*==========================================
 * /resetskill
 *------------------------------------------
 */
int pc_resetskill(struct map_session_data* sd)
{
	int  i,skill;

	nullpo_retr(0, sd);

	for(i=1;i<MAX_SKILL;i++){
		if( (skill = pc_checkskill(sd,i)) > 0) {
			if(!(skill_get_inf2(i)&0x01) || battle_config.quest_skill_learn) {
				if(!sd->status.skill[i].flag)
					sd->status.skill_point += skill;
				else if(sd->status.skill[i].flag > 2 && sd->status.skill[i].flag != 13) {
					sd->status.skill_point += (sd->status.skill[i].flag - 2);
				}
				sd->status.skill[i].lv = 0;
			}
			else if(battle_config.quest_skill_reset)
				sd->status.skill[i].lv = 0;
			sd->status.skill[i].flag = 0;
		}
		else
			sd->status.skill[i].lv = 0;
	}
	clif_updatestatus(sd,SP_SKILLPOINT);
	clif_skillinfoblock(sd);
	status_calc_pc(sd,0);

	return 0;
}


/*==========================================
 * pcにダメージを与える
 *------------------------------------------
 */
int pc_damage(struct block_list *src,struct map_session_data *sd,int damage)
{
	int i=0,j=0;
	struct pc_base_job s_class;

	nullpo_retr(0, sd);

	//転生や養子の場合の元の職業を算出する
	s_class = pc_calc_base_job(sd->status.class);
	// 既に死んでいたら無効
	if(pc_isdead(sd))
		return 0;
	// 座ってたら立ち上がる
	if(pc_issit(sd)) {
		pc_setstand(sd);
		skill_gangsterparadise(sd,0);
	}

	// 歩 いていたら足を止める
	if(sd->sc_data[SC_ENDURE].timer == -1 && !sd->special_state.infinite_endure)
		pc_stop_walking(sd,3);
	// 演奏/ダンスの中断
	if(damage > sd->status.max_hp>>2)
		skill_stop_dancing(&sd->bl,0);

	sd->status.hp-=damage;
	if(sd->status.pet_id > 0 && sd->pd && sd->petDB && battle_config.pet_damage_support)
		pet_target_check(sd,src,1);

	if(sd->status.option&2)
		status_change_end(&sd->bl, SC_HIDING, -1);
	if(sd->status.option&4)
		status_change_end(&sd->bl, SC_CLOAKING, -1);

	if(sd->status.hp>0){
		// まだ生きているならHP更新
		clif_updatestatus(sd,SP_HP);

		if(sd->status.hp<sd->status.max_hp>>2 && pc_checkskill(sd,SM_AUTOBERSERK)>0 &&
			(sd->sc_data[SC_PROVOKE].timer==-1 || sd->sc_data[SC_PROVOKE].val2==0 ))
			// オートバーサーク発動
			status_change_start(&sd->bl,SC_PROVOKE,10,1,0,0,0,0);

		return 0;
	}
	//スパノビがExp99%でHPが0になるとHPが回復して金剛状態になる
	if(s_class.job == 23 && pc_nextbaseexp(sd) && 100*sd->status.base_exp/pc_nextbaseexp(sd)>=99 && sd->sc_data && sd->sc_data[SC_STEELBODY].timer==-1){
		clif_skill_nodamage(&sd->bl,&sd->bl,MO_STEELBODY,5,1);
		status_change_start(&sd->bl,SkillStatusChangeTable[MO_STEELBODY],5,0,0,0,skill_get_time(MO_STEELBODY,5),0 );
		sd->status.hp = sd->status.max_hp;
		clif_updatestatus(sd,SP_HP);
		return 0;
	}
	sd->status.hp = 0;
	pc_setdead(sd);
	if(sd->vender_id)
		vending_closevending(sd);

	if(sd->status.pet_id > 0 && sd->pd) {
		if(sd->petDB) {
			sd->pet.intimate -= sd->petDB->die;
			if(sd->pet.intimate < 0)
				sd->pet.intimate = 0;
			clif_send_petdata(sd,1,sd->pet.intimate);
		}
	}

	pc_stop_walking(sd,0);
	skill_castcancel(&sd->bl,0);	// 詠唱の中止
	clif_clearchar_area(&sd->bl,1);
	skill_unit_out_all(&sd->bl,gettick(),1);
	if(sd->sc_data[SC_BLADESTOP].timer!=-1)//白刃は事前に解除
		status_change_end(&sd->bl,SC_BLADESTOP,-1);
	pc_setglobalreg(sd,"PC_DIE_COUNTER",++sd->die_counter); //死にカウンター書き込み
	skill_status_change_clear(&sd->bl,0);	// ステータス異常を解除する
	clif_updatestatus(sd,SP_HP);
	status_calc_pc(sd,0);

	if(battle_config.bone_drop==2
		|| (battle_config.bone_drop==1 && map[sd->bl.m].flag.pvp)){	// ドクロドロップ
		struct item item_tmp;
		memset(&item_tmp,0,sizeof(item_tmp));
		item_tmp.nameid=7005;
		item_tmp.identify=1;
		item_tmp.card[0]=0x00fe;
		item_tmp.card[1]=0;
		*((unsigned long *)(&item_tmp.card[2]))=sd->char_id;	/* キャラID */
		map_addflooritem(&item_tmp,1,sd->bl.m,sd->bl.x,sd->bl.y,NULL,NULL,NULL,0);
	}

	for(i=0;i<5;i++)
		if(sd->dev.val1[i]){
			status_change_end(&map_id2sd(sd->dev.val1[i])->bl,SC_DEVOTION,-1);
			sd->dev.val1[i] = sd->dev.val2[i]=0;
		}

	if(battle_config.death_penalty_type&1) {
		if(s_class.job != 0 && !map[sd->bl.m].flag.nopenalty && !map[sd->bl.m].flag.gvg){
			if(battle_config.death_penalty_type&2 && battle_config.death_penalty_base > 0)
				sd->status.base_exp -= (int)((double)pc_nextbaseexp(sd) * (double)battle_config.death_penalty_base/10000.);
			else if(battle_config.death_penalty_base > 0) {
				if(pc_nextbaseexp(sd) > 0)
					sd->status.base_exp -= (int)((double)sd->status.base_exp * (double)battle_config.death_penalty_base/10000.);
			}
			if(sd->status.base_exp < 0)
				sd->status.base_exp = 0;
			clif_updatestatus(sd,SP_BASEEXP);

			if(battle_config.death_penalty_type&2 && battle_config.death_penalty_job > 0)
				sd->status.job_exp -= (int)((double)pc_nextjobexp(sd) * (double)battle_config.death_penalty_job/10000.);
			else if(battle_config.death_penalty_job > 0) {
				if(pc_nextjobexp(sd) > 0)
					sd->status.job_exp -= (int)((double)sd->status.job_exp * (double)battle_config.death_penalty_job/10000.);
			}
			if(sd->status.job_exp < 0)
				sd->status.job_exp = 0;
			clif_updatestatus(sd,SP_JOBEXP);
		}
	}

	// pvp
	if( map[sd->bl.m].flag.pvp){
		//ランキング計算
		if(!map[sd->bl.m].flag.pvp_nocalcrank){
			sd->pvp_point-=5;
			if(src && src->type==BL_PC )
				((struct map_session_data *)src)->pvp_point++;
		}
		//ナイトメアモードアイテムドロップ
		if(map[sd->bl.m].flag.pvp_nightmaredrop){
			for(j=0;j<MAX_DROP_PER_MAP;j++){
				int id = map[sd->bl.m].drop_list[j].drop_id;
				int type = map[sd->bl.m].drop_list[j].drop_type;
				int per = map[sd->bl.m].drop_list[j].drop_per;
				if(id == 0)
					continue;
				if(id == -1){//ランダムドロップ
					int eq_num=0,eq_n[MAX_INVENTORY];
					memset(eq_n,0,sizeof(eq_n));
					//先ず装備しているアイテム数をカウント
					for(i=0;i<MAX_INVENTORY;i++){
						int k;
						if( (type == 1 && !sd->status.inventory[i].equip)
							|| (type == 2 && sd->status.inventory[i].equip)
							||  type == 3){
							//InventoryIndexを格納
							for(k=0;k<MAX_INVENTORY;k++){
								if(eq_n[k] <= 0){
									eq_n[k]=i;
									break;
								}
							}
							eq_num++;
						}
					}
					if(eq_num > 0){
						int n = eq_n[rand()%eq_num];//該当アイテムの中からランダム
						if(rand()%10000 < per){
							if(sd->status.inventory[n].equip)
								pc_unequipitem(sd,n,0);
							pc_dropitem(sd,n,1);
						}
					}
				}
				else if(id > 0){
					for(i=0;i<MAX_INVENTORY;i++){
						if(sd->status.inventory[i].nameid == id//ItemIDが一致していて
							&& rand()%10000 < per//ドロップ率判定もOKで
							&& ((type == 1 && !sd->status.inventory[i].equip)//タイプ判定もOKならドロップ
								|| (type == 2 && sd->status.inventory[i].equip)
								|| type == 3)
							){
							if(sd->status.inventory[i].equip)
								pc_unequipitem(sd,i,0);
							pc_dropitem(sd,i,1);
							break;
						}
					}
				}
			}
			pc_setdead(sd);
		}
		// 強制送還
		if( sd->pvp_point < 0 ){
			sd->pvp_point=0;
			pc_setstand(sd);
			pc_setrestartvalue(sd,3);
			pc_setpos(sd,sd->status.save_point.map,sd->status.save_point.x,sd->status.save_point.y,0);
		}
	}
	//GvG
	if(map[sd->bl.m].flag.gvg){
		pc_setstand(sd);
		pc_setrestartvalue(sd,3);
		pc_setpos(sd,sd->status.save_point.map,sd->status.save_point.x,sd->status.save_point.y,0);
	}

	return 0;
}


//
// script関 連
//
/*==========================================
 * script用PCステータス読み出し
 *------------------------------------------
 */
int pc_readparam(struct map_session_data *sd,int type)
{
	int val=0;
	struct pc_base_job s_class;
	
	nullpo_retr(0, sd);

	s_class = pc_calc_base_job(sd->status.class);

	switch(type){
	case SP_SKILLPOINT:
		val= sd->status.skill_point;
		break;
	case SP_STATUSPOINT:
		val= sd->status.status_point;
		break;
	case SP_ZENY:
		val= sd->status.zeny;
		break;
	case SP_BASELEVEL:
		val= sd->status.base_level;
		break;
	case SP_JOBLEVEL:
		val= sd->status.job_level;
		break;
	case SP_CLASS:
		val= s_class.job;
		break;
	case SP_UPPER:
		val= s_class.upper;
		break;
	case SP_SEX:
		val= sd->sex;
		break;
	case SP_WEIGHT:
		val= sd->weight;
		break;
	case SP_MAXWEIGHT:
		val= sd->max_weight;
		break;
	case SP_BASEEXP:
		val= sd->status.base_exp;
		break;
	case SP_JOBEXP:
		val= sd->status.job_exp;
		break;
	case SP_NEXTBASEEXP:
		val= pc_nextbaseexp(sd);
		break;
	case SP_NEXTJOBEXP:
		val= pc_nextjobexp(sd);
		break;
	case SP_HP:
		val= sd->status.hp;
		break;
	case SP_MAXHP:
		val= sd->status.max_hp;
		break;
	case SP_SP:
		val= sd->status.sp;
		break;
	case SP_MAXSP:
		val= sd->status.max_sp;
		break;
	case SP_PARTNER:
		val= sd->status.partner_id;
		break;
	case SP_CART:
		val= sd->status.option&CART_MASK;
		break;
	}

	return val;
}


/*==========================================
 * script用PCステータス設定
 *------------------------------------------
 */
int pc_setparam(struct map_session_data *sd,int type,int val)
{
	int i = 0,up_level = 50;
	struct pc_base_job s_class;

	nullpo_retr(0, sd);

	s_class = pc_calc_base_job(sd->status.class);

	switch(type){
	case SP_BASELEVEL:
		if (val > sd->status.base_level) {
			for (i = 1; i <= (val - sd->status.base_level); i++)
				sd->status.status_point += (sd->status.base_level + i + 14) / 5 ;
		}
		sd->status.base_level = val;
		sd->status.base_exp = 0;
		clif_updatestatus(sd, SP_BASELEVEL);
		clif_updatestatus(sd, SP_NEXTBASEEXP);
		clif_updatestatus(sd, SP_STATUSPOINT);
		clif_updatestatus(sd, SP_BASEEXP);
		status_calc_pc(sd, 0);
		pc_heal(sd, sd->status.max_hp, sd->status.max_sp);
		break;
	case SP_JOBLEVEL:
		if (s_class.job == 0)
			up_level -= 40;
		if ((s_class.job == 23) || (s_class.upper == 1 && s_class.type == 2))
			up_level += 20;
		if (val >= sd->status.job_level) {
			if (val > up_level)val = up_level;
			sd->status.skill_point += (val-sd->status.job_level);
		sd->status.job_level = val;
			sd->status.job_exp = 0;
			clif_updatestatus(sd, SP_JOBLEVEL);
			clif_updatestatus(sd, SP_NEXTJOBEXP);
			clif_updatestatus(sd, SP_JOBEXP);
			clif_updatestatus(sd, SP_SKILLPOINT);
			status_calc_pc(sd, 0);
			clif_misceffect(&sd->bl, 1);
		} else {
			sd->status.job_level = val;
			sd->status.job_exp = 0;
			clif_updatestatus(sd, SP_JOBLEVEL);
			clif_updatestatus(sd, SP_NEXTJOBEXP);
			clif_updatestatus(sd, SP_JOBEXP);
			status_calc_pc(sd, 0);
		}
		clif_updatestatus(sd,type);
		break;
	case SP_SKILLPOINT:
		sd->status.skill_point = val;
		break;
	case SP_STATUSPOINT:
		sd->status.status_point = val;
		break;
	case SP_ZENY:
		sd->status.zeny = val;
		break;
	case SP_BASEEXP:
		if(pc_nextbaseexp(sd) > 0) {
			sd->status.base_exp = val;
			if(sd->status.base_exp < 0)
				sd->status.base_exp=0;
			pc_checkbaselevelup(sd);
		}
		break;
	case SP_JOBEXP:
		if(pc_nextjobexp(sd) > 0) {
			sd->status.job_exp = val;
			if(sd->status.job_exp < 0)
				sd->status.job_exp=0;
			pc_checkjoblevelup(sd);
		}
		break;
	}
	clif_updatestatus(sd,type);

	return 0;
}


/*==========================================
 * HP/SP回復
 *------------------------------------------
 */
int pc_heal(struct map_session_data *sd,int hp,int sp)
{
//	if(battle_config.battle_log)
//		printf("heal %d %d\n",hp,sp);

	nullpo_retr(0, sd);

	if(pc_checkoverhp(sd)) {
		if(hp > 0)
			hp = 0;
	}
	if(pc_checkoversp(sd)) {
		if(sp > 0)
			sp = 0;
	}

	// バーサーク中は回復させない
	if(sd->sc_data && sd->sc_data[SC_BERSERK].timer!=-1) {
		if (sp > 0)
			sp = 0;
		if (hp > 0)
			hp = 0;
	}

	if(hp+sd->status.hp>sd->status.max_hp)
		hp=sd->status.max_hp-sd->status.hp;
	if(sp+sd->status.sp>sd->status.max_sp)
		sp=sd->status.max_sp-sd->status.sp;
	sd->status.hp+=hp;
	if(sd->status.hp <= 0) {
		sd->status.hp = 0;
		pc_damage(NULL,sd,1);
		hp = 0;
	}
	sd->status.sp+=sp;
	if(sd->status.sp <= 0)
		sd->status.sp = 0;
	if(hp)
		clif_updatestatus(sd,SP_HP);
	if(sp)
		clif_updatestatus(sd,SP_SP);

	return hp + sp;
}


/*==========================================
 * HP/SP回復
 *------------------------------------------
 */
int pc_itemheal(struct map_session_data *sd,int hp,int sp)
{
	int bonus;
//	if(battle_config.battle_log)
//		printf("heal %d %d\n",hp,sp);

	nullpo_retr(0, sd);

	if(sd->state.potionpitcher_flag) {
		sd->potion_hp = hp;
		sd->potion_sp = sp;
		return 0;
	}

	if(pc_checkoverhp(sd)) {
		if(hp > 0)
			hp = 0;
	}
	if(pc_checkoversp(sd)) {
		if(sp > 0)
			sp = 0;
	}
	if(hp > 0) {
		bonus = (sd->paramc[2]<<1) + 100 + pc_checkskill(sd,SM_RECOVERY)*10;
		if(bonus != 100)
			hp = hp * bonus / 100;
		bonus = 100 + pc_checkskill(sd,AM_LEARNINGPOTION)*5;
		if(bonus != 100)
			hp = hp * bonus / 100;
	}
	if(sp > 0) {
		bonus = (sd->paramc[3]<<1) + 100 + pc_checkskill(sd,MG_SRECOVERY)*10;
		if(bonus != 100)
			sp = sp * bonus / 100;
		bonus = 100 + pc_checkskill(sd,AM_LEARNINGPOTION)*5;
		if(bonus != 100)
			sp = sp * bonus / 100;
	}
	if(hp+sd->status.hp>sd->status.max_hp)
		hp=sd->status.max_hp-sd->status.hp;
	if(sp+sd->status.sp>sd->status.max_sp)
		sp=sd->status.max_sp-sd->status.sp;
	sd->status.hp+=hp;
	if(sd->status.hp <= 0) {
		sd->status.hp = 0;
		pc_damage(NULL,sd,1);
		hp = 0;
	}
	sd->status.sp+=sp;
	if(sd->status.sp <= 0)
		sd->status.sp = 0;
	if(hp)
		clif_updatestatus(sd,SP_HP);
	if(sp)
		clif_updatestatus(sd,SP_SP);

	return 0;
}


/*==========================================
 * HP/SP回復
 *------------------------------------------
 */
int pc_percentheal(struct map_session_data *sd,int hp,int sp)
{
	nullpo_retr(0, sd);

	if(sd->state.potionpitcher_flag) {
		sd->potion_per_hp = hp;
		sd->potion_per_sp = sp;
		return 0;
	}

	if(pc_checkoverhp(sd)) {
		if(hp > 0)
			hp = 0;
	}
	if(pc_checkoversp(sd)) {
		if(sp > 0)
			sp = 0;
	}
	if(hp) {
		if(hp >= 100) {
			sd->status.hp = sd->status.max_hp;
		}
		else if(hp <= -100) {
			sd->status.hp = 0;
			pc_damage(NULL,sd,1);
		}
		else {
			sd->status.hp += sd->status.max_hp*hp/100;
			if(sd->status.hp > sd->status.max_hp)
				sd->status.hp = sd->status.max_hp;
			if(sd->status.hp <= 0) {
				sd->status.hp = 0;
				pc_damage(NULL,sd,1);
				hp = 0;
			}
		}
	}
	if(sp) {
		if(sp >= 100) {
			sd->status.sp = sd->status.max_sp;
		}
		else if(sp <= -100) {
			sd->status.sp = 0;
		}
		else {
			sd->status.sp += sd->status.max_sp*sp/100;
			if(sd->status.sp > sd->status.max_sp)
				sd->status.sp = sd->status.max_sp;
			if(sd->status.sp < 0)
				sd->status.sp = 0;
		}
	}
	if(hp)
		clif_updatestatus(sd,SP_HP);
	if(sp)
		clif_updatestatus(sd,SP_SP);

	return 0;
}


/*==========================================
 * 職変更
 * 引数	job 職業 0～23
 *		upper 通常 0, 転生 1, 養子 2, そのまま -1
 *------------------------------------------
 */
int pc_jobchange(struct map_session_data *sd,int job, int upper)
{
	int i;
	int b_class = 0;
	struct pc_base_job s_class;

	nullpo_retr(0, sd);

	//転生や養子の場合の元の職業を算出する
	s_class = pc_calc_base_job(sd->status.class);

	if(battle_config.enable_upper_class==0){ //confで無効になっていたらupper=0
		upper = 0;
	}
	
	if(upper < 0) //現在転生かどうかを判断する
		upper = s_class.upper;

	if(upper == 0){ //通常職ならjobそのまんま
		b_class = job;
	}else if(upper == 1){
		if(job == 23){ //転生にスパノビは存在しないのでお断り
			return 1;
		}else{
			b_class = job + PC_CLASS_BASE2;
		}
	}else if(upper == 2){ //養子に結婚はないけどどうせ次で蹴られるからいいや
		b_class = (job==23)?(job + (PC_CLASS_BASE3 - 1)):(job + PC_CLASS_BASE3);
	}else{
		return 1;
	}

	if((sd->status.sex == 0 && job == 19) ||
	   (sd->status.sex == 1 && job == 20) ||
	   job ==22 || sd->status.class == b_class) //♀はバードになれない、♂はダンサーになれない、結婚衣裳もお断り
		return 1;


	sd->status.class = sd->view_class = b_class;
	clif_changelook(&sd->bl,LOOK_BASE,sd->view_class);
	if(sd->status.clothes_color > 0)
		clif_changelook(&sd->bl,LOOK_CLOTHES_COLOR,sd->status.clothes_color);
	if(sd->status.manner < 0)
		clif_changestatus(&sd->bl,SP_MANNER,sd->status.manner);

	sd->status.job_level=1;
	sd->status.job_exp=0;
	clif_updatestatus(sd,SP_JOBLEVEL);
	clif_updatestatus(sd,SP_JOBEXP);
	clif_updatestatus(sd,SP_NEXTJOBEXP);

	for(i=0;i<11;i++) {
		if(sd->equip_index[i] >= 0)
			if(!pc_isequip(sd,sd->equip_index[i]))
				pc_unequipitem(sd,sd->equip_index[i],1);	// 装備外し
	}
	status_calc_pc(sd,0);
	pc_checkallowskill(sd);
	pc_equiplookall(sd);
	clif_equiplist(sd);

	return 0;
}


/*==========================================
 * 見た目変更
 *------------------------------------------
 */
int pc_equiplookall(struct map_session_data *sd)
{
	nullpo_retr(0, sd);

#if PACKETVER < 4
	clif_changelook(&sd->bl,LOOK_WEAPON,sd->status.weapon);
	clif_changelook(&sd->bl,LOOK_SHIELD,sd->status.shield);
#else
	clif_changelook(&sd->bl,LOOK_WEAPON,0);
	clif_changelook(&sd->bl,LOOK_SHOES,0);
#endif
	clif_changelook(&sd->bl,LOOK_HEAD_BOTTOM,sd->status.head_bottom);
	clif_changelook(&sd->bl,LOOK_HEAD_TOP,sd->status.head_top);
	clif_changelook(&sd->bl,LOOK_HEAD_MID,sd->status.head_mid);

	return 0;
}


/*==========================================
 * 見た目変更
 *------------------------------------------
 */
int pc_changelook(struct map_session_data *sd,int type,int val)
{
	nullpo_retr(0, sd);

	switch(type){
	case LOOK_HAIR:
		sd->status.hair=val;
		break;
	case LOOK_WEAPON:
		sd->status.weapon=val;
		break;
	case LOOK_HEAD_BOTTOM:
		sd->status.head_bottom=val;
		break;
	case LOOK_HEAD_TOP:
		sd->status.head_top=val;
		break;
	case LOOK_HEAD_MID:
		sd->status.head_mid=val;
		break;
	case LOOK_HAIR_COLOR:
		sd->status.hair_color=val;
		break;
	case LOOK_CLOTHES_COLOR:
		sd->status.clothes_color=val;
		break;
	case LOOK_SHIELD:
		sd->status.shield=val;
		break;
	case LOOK_SHOES:
		break;
	}
	clif_changelook(&sd->bl,type,val);

	return 0;
}


/*==========================================
 * 付属品(鷹,ペコ,カート)設定
 *------------------------------------------
 */
int pc_setoption(struct map_session_data *sd,int type)
{
	nullpo_retr(0, sd);

	sd->status.option=type;
	clif_changeoption(&sd->bl);
	status_calc_pc(sd,0);

	return 0;
}


/*==========================================
 * カート設定
 *------------------------------------------
 */
int pc_setcart(struct map_session_data *sd,int type)
{
	int cart[6]={0x0000,0x0008,0x0080,0x0100,0x0200,0x0400};

	nullpo_retr(0, sd);

	if(pc_checkskill(sd,MC_PUSHCART)>0){ // プッシュカートスキル所持
		if(!pc_iscarton(sd)){ // カートを付けていない
			pc_setoption(sd,cart[type]);
			clif_cart_itemlist(sd);
			clif_cart_equiplist(sd);
			clif_updatestatus(sd,SP_CARTINFO);
			clif_status_change(&sd->bl,0x0c,0);
		}
		else{
			pc_setoption(sd,cart[type]);
		}
	}

	return 0;
}


/*==========================================
 * 鷹設定
 *------------------------------------------
 */
int pc_setfalcon(struct map_session_data *sd)
{
	if(pc_checkskill(sd,HT_FALCON)>0){	// ファルコンマスタリースキル所持
		pc_setoption(sd,0x0010);
	}

	return 0;
}


/*==========================================
 * ペコペコ設定
 *------------------------------------------
 */
int pc_setriding(struct map_session_data *sd)
{
	if(pc_checkskill(sd,KN_RIDING)>0){ // ライディングスキル所持
		pc_setoption(sd,0x0020);
	}

	return 0;
}
/*==========================================
 * アイテムドロップ可不可判定
 *------------------------------------------
 */
int pc_candrop(struct map_session_data *sd,int item_id)
{
	if(pc_isGM(sd) && pc_isGM(sd) < battle_config.gm_can_drop_lv)
		return 1;
	return 0;
}

/*==========================================
 * script用変数の値を読む
 *------------------------------------------
 */
int pc_readreg(struct map_session_data *sd,int reg)
{
	int i;

	nullpo_retr(0, sd);

	for(i=0;i<sd->reg_num;i++)
		if(sd->reg[i].index==reg)
			return sd->reg[i].data;

	return 0;
}
/*==========================================
 * script用変数の値を設定
 *------------------------------------------
 */
int pc_setreg(struct map_session_data *sd,int reg,int val)
{
	int i;

	nullpo_retr(0, sd);

	for (i = 0; i < sd->reg_num; i++) {
		if (sd->reg[i].index == reg){
			sd->reg[i].data = val;
			return 0;
		}
	}
	sd->reg_num++;
	sd->reg = (struct script_reg *)aRealloc(sd->reg, sizeof(*(sd->reg)) * sd->reg_num);
/*	memset(sd->reg + (sd->reg_num - 1) * sizeof(*(sd->reg)), 0,
		sizeof(*(sd->reg)));
*/
	sd->reg[i].index = reg;
	sd->reg[i].data = val;

	return 0;
}


/*==========================================
 * script用文字列変数の値を読む
 *------------------------------------------
 */
char *pc_readregstr(struct map_session_data *sd,int reg)
{
	int i;

	nullpo_retr(0, sd);

	for(i=0;i<sd->regstr_num;i++)
		if(sd->regstr[i].index==reg)
			return sd->regstr[i].data;

	return NULL;
}
/*==========================================
 * script用文字列変数の値を設定
 *------------------------------------------
 */
int pc_setregstr(struct map_session_data *sd,int reg,char *str)
{
	int i;

	nullpo_retr(0, sd);

	if(strlen(str)+1 >= sizeof(sd->regstr[0].data)){
		printf("pc_setregstr: string too long !\n");
		return 0;
	}

	for(i=0;i<sd->regstr_num;i++)
		if(sd->regstr[i].index==reg){
			strncpy(sd->regstr[i].data,str,256);
			return 0;
		}
	sd->regstr_num++;
	sd->regstr = (struct script_regstr *)aRealloc(sd->regstr, sizeof(sd->regstr[0]) * sd->regstr_num);
/*	memset(sd->reg + (sd->reg_num - 1) * sizeof(*(sd->reg)), 0,
		sizeof(*(sd->reg)));
*/
	sd->regstr[i].index=reg;
	strncpy(sd->regstr[i].data,str,256);

	return 0;
}

/*==========================================
 * script用グローバル変数の値を読む
 *------------------------------------------
 */
int pc_readglobalreg(struct map_session_data *sd,char *reg)
{
	int i;

	nullpo_retr(0, sd);

	for(i=0;i<sd->status.global_reg_num;i++){
		if(strcmp(sd->status.global_reg[i].str,reg)==0)
			return sd->status.global_reg[i].value;
	}

	return 0;
}


/*==========================================
 * script用グローバル変数の値を設定
 *------------------------------------------
 */
int pc_setglobalreg(struct map_session_data *sd,char *reg,int val)
{
	int i;

	nullpo_retr(0, sd);

	//PC_DIE_COUNTERがスクリプトなどで変更された時の処理
	if(strcmp(reg,"PC_DIE_COUNTER") == 0 && sd->die_counter != val){
		sd->die_counter = val;
		status_calc_pc(sd,0);
	}
	if(val==0){
		for(i=0;i<sd->status.global_reg_num;i++){
			if(strcmp(sd->status.global_reg[i].str,reg)==0){
				sd->status.global_reg[i]=sd->status.global_reg[sd->status.global_reg_num-1];
				sd->status.global_reg_num--;
				break;
			}
		}
		return 0;
	}
	for(i=0;i<sd->status.global_reg_num;i++){
		if(strcmp(sd->status.global_reg[i].str,reg)==0){
			sd->status.global_reg[i].value=val;
			return 0;
		}
	}
	if(sd->status.global_reg_num<GLOBAL_REG_NUM){
		strncpy(sd->status.global_reg[i].str,reg,32);
		sd->status.global_reg[i].value=val;
		sd->status.global_reg_num++;
		return 0;
	}
	if(battle_config.error_log)
		printf("pc_setglobalreg : couldn't set %s (GLOBAL_REG_NUM = %d)\n", reg, GLOBAL_REG_NUM);

	return 1;
}

/*==========================================
 * script用アカウント変数の値を読む
 *------------------------------------------
 */
int pc_readaccountreg(struct map_session_data *sd,char *reg)
{
	int i;

	nullpo_retr(0, sd);

	for(i=0;i<sd->status.account_reg_num;i++){
		if(strcmp(sd->status.account_reg[i].str,reg)==0)
			return sd->status.account_reg[i].value;
	}

	return 0;
}
/*==========================================
 * script用アカウント変数の値を設定
 *------------------------------------------
 */
int pc_setaccountreg(struct map_session_data *sd,char *reg,int val)
{
	int i;

	nullpo_retr(0, sd);

	if(val==0){
		for(i=0;i<sd->status.account_reg_num;i++){
			if(strcmp(sd->status.account_reg[i].str,reg)==0){
				sd->status.account_reg[i]=sd->status.account_reg[sd->status.account_reg_num-1];
				sd->status.account_reg_num--;
				break;
			}
		}
		intif_saveaccountreg(sd);
		return 0;
	}
	for(i=0;i<sd->status.account_reg_num;i++){
		if(strcmp(sd->status.account_reg[i].str,reg)==0){
			sd->status.account_reg[i].value=val;
			intif_saveaccountreg(sd);
			return 0;
		}
	}
	if(sd->status.account_reg_num<ACCOUNT_REG_NUM){
		strncpy(sd->status.account_reg[i].str,reg,32);
		sd->status.account_reg[i].value=val;
		sd->status.account_reg_num++;
		intif_saveaccountreg(sd);
		return 0;
	}
	if(battle_config.error_log)
		printf("pc_setaccountreg : couldn't set %s (ACCOUNT_REG_NUM = %d)\n", reg, ACCOUNT_REG_NUM);

	return 1;
}
/*==========================================
 * script用アカウント変数2の値を読む
 *------------------------------------------
 */
int pc_readaccountreg2(struct map_session_data *sd,char *reg)
{
	int i;

	nullpo_retr(0, sd);

	for(i=0;i<sd->status.account_reg2_num;i++){
		if(strcmp(sd->status.account_reg2[i].str,reg)==0)
			return sd->status.account_reg2[i].value;
	}

	return 0;
}
/*==========================================
 * script用アカウント変数2の値を設定
 *------------------------------------------
 */
int pc_setaccountreg2(struct map_session_data *sd,char *reg,int val)
{
	int i;

	nullpo_retr(1, sd);

	if(val==0){
		for(i=0;i<sd->status.account_reg2_num;i++){
			if(strcmp(sd->status.account_reg2[i].str,reg)==0){
				sd->status.account_reg2[i]=sd->status.account_reg2[sd->status.account_reg2_num-1];
				sd->status.account_reg2_num--;
				break;
			}
		}
		chrif_saveaccountreg2(sd);
		return 0;
	}
	for(i=0;i<sd->status.account_reg2_num;i++){
		if(strcmp(sd->status.account_reg2[i].str,reg)==0){
			sd->status.account_reg2[i].value=val;
			chrif_saveaccountreg2(sd);
			return 0;
		}
	}
	if(sd->status.account_reg2_num<ACCOUNT_REG2_NUM){
		strncpy(sd->status.account_reg2[i].str,reg,32);
		sd->status.account_reg2[i].value=val;
		sd->status.account_reg2_num++;
		chrif_saveaccountreg2(sd);
		return 0;
	}
	if(battle_config.error_log)
		printf("pc_setaccountreg2 : couldn't set %s (ACCOUNT_REG2_NUM = %d)\n", reg, ACCOUNT_REG2_NUM);

	return 1;
}

/*==========================================
 * イベントタイマー処理
 *------------------------------------------
 */
int pc_eventtimer(int tid,unsigned int tick,int id,int data)
{
	struct map_session_data *sd=map_id2sd(id);
	int i;
	if(sd==NULL)
		return 0;

	for(i=0;i<MAX_EVENTTIMER;i++){
		if( sd->eventtimer[i]==tid ){
			sd->eventtimer[i]=-1;
			npc_event(sd,(const char *)data);
			break;
		}
	}
	free((void *)data);
	if(i==MAX_EVENTTIMER) {
		if(battle_config.error_log)
			printf("pc_eventtimer: no such event timer\n");
	}

	return 0;
}


/*==========================================
 * イベントタイマー追加
 *------------------------------------------
 */
int pc_addeventtimer(struct map_session_data *sd,int tick,const char *name)
{
	int i;

	nullpo_retr(0, sd);

	for(i=0;i<MAX_EVENTTIMER;i++)
		if( sd->eventtimer[i]==-1 )
			break;
	if(i<MAX_EVENTTIMER){
		char *evname=(char *)aCalloc(24,sizeof(char));
		memcpy(evname,name,24);
		sd->eventtimer[i]=add_timer(gettick()+tick,
			pc_eventtimer,sd->bl.id,(int)evname);
	}else {
		if(battle_config.error_log)
			printf("pc_addtimer: event timer is full !\n");
	}

	return 0;
}


/*==========================================
 * イベントタイマー削除
 *------------------------------------------
 */
int pc_deleventtimer(struct map_session_data *sd,const char *name)
{
	int i;

	nullpo_retr(0, sd);

	for(i=0;i<MAX_EVENTTIMER;i++)
		if( sd->eventtimer[i]!=-1 && strcmp(
			(char *)(get_timer(sd->eventtimer[i])->data), name)==0 ){
				delete_timer(sd->eventtimer[i],pc_eventtimer);
				sd->eventtimer[i]=-1;
				break;
		}

	return 0;
}


/*==========================================
 * イベントタイマーカウント値追加
 *------------------------------------------
 */
int pc_addeventtimercount(struct map_session_data *sd,const char *name,int tick)
{
	int i;

	nullpo_retr(0, sd);

	for(i=0;i<MAX_EVENTTIMER;i++)
		if( sd->eventtimer[i]!=-1 && strcmp(
			(char *)(get_timer(sd->eventtimer[i])->data), name)==0 ){
				addtick_timer(sd->eventtimer[i],tick);
				break;
		}

	return 0;
}


/*==========================================
 * イベントタイマー全削除
 *------------------------------------------
 */
int pc_cleareventtimer(struct map_session_data *sd)
{
	int i;

	nullpo_retr(0, sd);

	for(i=0;i<MAX_EVENTTIMER;i++)
		if( sd->eventtimer[i]!=-1 ){
			delete_timer(sd->eventtimer[i],pc_eventtimer);
			sd->eventtimer[i]=-1;
		}

	return 0;
}

//
// 装 備物
//
/*==========================================
 * アイテムを装備する
 *------------------------------------------
 */
int pc_equipitem(struct map_session_data *sd,int n,int pos)
{
	int i,nameid;
	struct item_data *id;
	//転生や養子の場合の元の職業を算出する
	struct pc_base_job s_class;

	nullpo_retr(0, sd);

	s_class = pc_calc_base_job(sd->status.class);

	nameid = sd->status.inventory[n].nameid;
	id = sd->inventory_data[n];
	pos = pc_equippoint(sd,n);
	if(battle_config.battle_log)
		printf("equip %d(%d) %x:%x\n",nameid,n,id->equip,pos);
	if(!pc_isequip(sd,n) || !pos) {
		clif_equipitemack(sd,n,0,0);	// fail
		return 0;
	}
	if(pos==0x88){ // アクセサリ用例外処理
		int epor=0;
		if(sd->equip_index[0] >= 0)
			epor |= sd->status.inventory[sd->equip_index[0]].equip;
		if(sd->equip_index[1] >= 0)
			epor |= sd->status.inventory[sd->equip_index[1]].equip;
		epor &= 0x88;
		pos = epor == 0x08 ? 0x80 : 0x08;
	}

	// 二刀流処理
	if ((pos==0x22) // 一応、装備要求箇所が二刀流武器かチェックする
	 &&	(id->equip==2)	// 単 手武器
	 &&	(pc_checkskill(sd, AS_LEFT) > 0 || s_class.job == 12) ) // 左手修錬有
	{
		int tpos=0;
		if(sd->equip_index[8] >= 0)
			tpos |= sd->status.inventory[sd->equip_index[8]].equip;
		if(sd->equip_index[9] >= 0)
			tpos |= sd->status.inventory[sd->equip_index[9]].equip;
		tpos &= 0x02;
		pos = tpos == 0x02 ? 0x20 : 0x02;
	}

	for(i=0;i<11;i++) {
		if(sd->equip_index[i] >= 0 && sd->status.inventory[sd->equip_index[i]].equip&pos) {
			pc_unequipitem(sd,sd->equip_index[i],1);
		}
	}
	// 弓矢装備
	if(pos==0x8000){
		clif_arrowequip(sd,n);
		clif_arrow_fail(sd,3);	// 3=矢が装備できました
	}
	else
		clif_equipitemack(sd,n,pos,1);

	for(i=0;i<11;i++) {
		if(pos & equip_pos[i])
			sd->equip_index[i] = n;
	}
	sd->status.inventory[n].equip=pos;

	if(sd->status.inventory[n].equip & 0x0002) {
		if(sd->inventory_data[n])
			sd->weapontype1 = sd->inventory_data[n]->look;
		else
			sd->weapontype1 = 0;
		pc_calcweapontype(sd);
		clif_changelook(&sd->bl,LOOK_WEAPON,sd->status.weapon);
	}
	if(sd->status.inventory[n].equip & 0x0020) {
		if(sd->inventory_data[n]) {
			if(sd->inventory_data[n]->type == 4) {
				sd->status.shield = 0;
				if(sd->status.inventory[n].equip == 0x0020)
					sd->weapontype2 = sd->inventory_data[n]->look;
				else
					sd->weapontype2 = 0;
			}
			else if(sd->inventory_data[n]->type == 5) {
				sd->status.shield = sd->inventory_data[n]->look;
				sd->weapontype2 = 0;
			}
		}
		else
			sd->status.shield = sd->weapontype2 = 0;
		pc_calcweapontype(sd);
		clif_changelook(&sd->bl,LOOK_SHIELD,sd->status.shield);
	}
	if(sd->status.inventory[n].equip & 0x0001) {
		if(sd->inventory_data[n])
			sd->status.head_bottom = sd->inventory_data[n]->look;
		else
			sd->status.head_bottom = 0;
		clif_changelook(&sd->bl,LOOK_HEAD_BOTTOM,sd->status.head_bottom);
	}
	if(sd->status.inventory[n].equip & 0x0100) {
		if(sd->inventory_data[n])
			sd->status.head_top = sd->inventory_data[n]->look;
		else
			sd->status.head_top = 0;
		clif_changelook(&sd->bl,LOOK_HEAD_TOP,sd->status.head_top);
	}
	if(sd->status.inventory[n].equip & 0x0200) {
		if(sd->inventory_data[n])
			sd->status.head_mid = sd->inventory_data[n]->look;
		else
			sd->status.head_mid = 0;
		clif_changelook(&sd->bl,LOOK_HEAD_MID,sd->status.head_mid);
	}
	if(sd->status.inventory[n].equip & 0x0040)
		clif_changelook(&sd->bl,LOOK_SHOES,0);

	pc_checkallowskill(sd);	// 装備品でスキルか解除されるかチェック
	status_calc_pc(sd,0);

	if(sd->sc_data[SC_SIGNUMCRUCIS].timer != -1 && !battle_check_undead(7,sd->def_ele))
		status_change_end(&sd->bl,SC_SIGNUMCRUCIS,-1);
	if(sd->sc_data[SC_DANCING].timer!=-1 && (sd->status.weapon != 13 && sd->status.weapon !=14))
		skill_stop_dancing(&sd->bl,0);

	return 0;
}

/*==========================================
 * 装 備した物を外す
 *------------------------------------------
 */
int pc_unequipitem(struct map_session_data *sd,int n,int type)
{
	nullpo_retr(0, sd);

	if(battle_config.battle_log)
		printf("unequip %d %x:%x\n",n,pc_equippoint(sd,n),sd->status.inventory[n].equip);
	if(sd->status.inventory[n].equip){
		int i;
		for(i=0;i<11;i++) {
			if(sd->status.inventory[n].equip & equip_pos[i])
				sd->equip_index[i] = -1;
		}
		if(sd->status.inventory[n].equip & 0x0002) {
			sd->weapontype1 = 0;
			sd->status.weapon = sd->weapontype2;
			pc_calcweapontype(sd);
			clif_changelook(&sd->bl,LOOK_WEAPON,sd->status.weapon);
		}
		if(sd->status.inventory[n].equip & 0x0020) {
			sd->status.shield = sd->weapontype2 = 0;
			pc_calcweapontype(sd);
			clif_changelook(&sd->bl,LOOK_SHIELD,sd->status.shield);
		}
		if(sd->status.inventory[n].equip & 0x0001) {
			sd->status.head_bottom = 0;
			clif_changelook(&sd->bl,LOOK_HEAD_BOTTOM,sd->status.head_bottom);
		}
		if(sd->status.inventory[n].equip & 0x0100) {
			sd->status.head_top = 0;
			clif_changelook(&sd->bl,LOOK_HEAD_TOP,sd->status.head_top);
		}
		if(sd->status.inventory[n].equip & 0x0200) {
			sd->status.head_mid = 0;
			clif_changelook(&sd->bl,LOOK_HEAD_MID,sd->status.head_mid);
		}
		if(sd->status.inventory[n].equip & 0x0040)
			clif_changelook(&sd->bl,LOOK_SHOES,0);

		clif_unequipitemack(sd,n,sd->status.inventory[n].equip,1);
		sd->status.inventory[n].equip=0;
		if(!type)
			pc_checkallowskill(sd);
		if(sd->weapontype1 == 0 && sd->weapontype2 == 0)
			skill_encchant_eremental_end(&sd->bl,-1);  //武器持ち誓えは無条件で属性付与解除
	} else {
		clif_unequipitemack(sd,n,0,0);
	}
	if(!type) {
		status_calc_pc(sd,0);
		if(sd->sc_data[SC_SIGNUMCRUCIS].timer != -1 && !battle_check_undead(7,sd->def_ele))
			status_change_end(&sd->bl,SC_SIGNUMCRUCIS,-1);
	}

	return 0;
}

/*==========================================
 * アイテムのindex番号を詰めたり
 * 装 備品の装備可能チェックを行なう
 *------------------------------------------
 */
int pc_checkitem(struct map_session_data *sd)
{
	int i,j,k,id,calc_flag = 0;
	struct item_data *it=NULL;

	nullpo_retr(0, sd);

	// 所持品空き詰め
	for(i=j=0;i<MAX_INVENTORY;i++){
		if( (id=sd->status.inventory[i].nameid)==0)
			continue;
		if( battle_config.item_check && !itemdb_available(id) ){
			if(battle_config.error_log)
				printf("illeagal item id %d in %d[%s] inventory.\n",id,sd->bl.id,sd->status.name);
			pc_delitem(sd,i,sd->status.inventory[i].amount,3);
			continue;
		}
		if(i>j){
			memcpy(&sd->status.inventory[j],&sd->status.inventory[i],sizeof(struct item));
			sd->inventory_data[j] = sd->inventory_data[i];
		}
		j++;
	}
	if(j < MAX_INVENTORY)
		memset(&sd->status.inventory[j],0,sizeof(struct item)*(MAX_INVENTORY-j));
	for(k=j;k<MAX_INVENTORY;k++)
		sd->inventory_data[k] = NULL;

	// カート内空き詰め
	for(i=j=0;i<MAX_CART;i++){
		if( (id=sd->status.cart[i].nameid)==0 )
			continue;
		if( battle_config.item_check &&  !itemdb_available(id) ){
			if(battle_config.error_log)
				printf("illeagal item id %d in %d[%s] cart.\n",id,sd->bl.id,sd->status.name);
			pc_cart_delitem(sd,i,sd->status.cart[i].amount,1);
			continue;
		}
		if(i>j){
			memcpy(&sd->status.cart[j],&sd->status.cart[i],sizeof(struct item));
		}
		j++;
	}
	if(j < MAX_CART)
		memset(&sd->status.cart[j],0,sizeof(struct item)*(MAX_CART-j));

	// 装 備位置チェック

	for(i=0;i<MAX_INVENTORY;i++){

		it=sd->inventory_data[i];

		if(sd->status.inventory[i].nameid==0)
			continue;
		if(sd->status.inventory[i].equip & ~pc_equippoint(sd,i)) {
			sd->status.inventory[i].equip=0;
			calc_flag = 1;
		}
		//装備制限チェック
		if(sd->status.inventory[i].equip && map[sd->bl.m].flag.pvp && (it->flag.no_equip==1 || it->flag.no_equip==3)){//PvP制限
			sd->status.inventory[i].equip=0;
			calc_flag = 1;
		}else if(sd->status.inventory[i].equip && map[sd->bl.m].flag.gvg && (it->flag.no_equip==2 || it->flag.no_equip==3)){//GvG制限
			sd->status.inventory[i].equip=0;
			calc_flag = 1;
		}
	}

	pc_setequipindex(sd);
	if(calc_flag)
		status_calc_pc(sd,2);

	return 0;
}


int pc_checkoverhp(struct map_session_data *sd)
{
	nullpo_retr(0, sd);

	if(sd->status.hp == sd->status.max_hp)
		return 1;
	if(sd->status.hp > sd->status.max_hp) {
		sd->status.hp = sd->status.max_hp;
		clif_updatestatus(sd,SP_HP);
		return 2;
	}

	return 0;
}

int pc_checkoversp(struct map_session_data *sd)
{
	nullpo_retr(0, sd);

	if(sd->status.sp == sd->status.max_sp)
		return 1;
	if(sd->status.sp > sd->status.max_sp) {
		sd->status.sp = sd->status.max_sp;
		clif_updatestatus(sd,SP_SP);
		return 2;
	}

	return 0;
}


/*==========================================
 * PVP順位計算用(foreachinarea)
 *------------------------------------------
 */
int pc_calc_pvprank_sub(struct block_list *bl,va_list ap)
{
	struct map_session_data *sd1,*sd2=NULL;

	nullpo_retr(0, bl);
	nullpo_retr(0, ap);
	nullpo_retr(0, sd1=(struct map_session_data *)bl);
	nullpo_retr(0, sd2=va_arg(ap,struct map_session_data *));

	if( sd1->pvp_point > sd2->pvp_point )
		sd2->pvp_rank++;
	return 0;
}
/*==========================================
 * PVP順位計算
 *------------------------------------------
 */
int pc_calc_pvprank(struct map_session_data *sd)
{
	int old;
	struct map_data *m;

	nullpo_retr(0, sd);
	nullpo_retr(0, m=&map[sd->bl.m]);
	
	old=sd->pvp_rank;

	if( !(m->flag.pvp) )
		return 0;
	sd->pvp_rank=1;
	map_foreachinarea(pc_calc_pvprank_sub,sd->bl.m,0,0,m->xs,m->ys,BL_PC,sd);
	if(old!=sd->pvp_rank || sd->pvp_lastusers!=m->users)
		clif_pvpset(sd,sd->pvp_rank,sd->pvp_lastusers=m->users,0);
	return sd->pvp_rank;
}
/*==========================================
 * PVP順位計算(timer)
 *------------------------------------------
 */
int pc_calc_pvprank_timer(int tid,unsigned int tick,int id,int data)
{
	struct map_session_data *sd=map_id2sd(id);
	if(sd==NULL)
		return 0;
	sd->pvp_timer=-1;
	if( pc_calc_pvprank(sd)>0 )
		sd->pvp_timer=add_timer(
			gettick()+PVP_CALCRANK_INTERVAL,
			pc_calc_pvprank_timer,id,data);
	return 0;
}

/*==========================================
 * sdは結婚しているか(既婚の場合は相方のchar_idを返す)
 *------------------------------------------
 */
int pc_ismarried(struct map_session_data *sd)
{
	if(sd == NULL)
		return -1;
	if(sd->status.partner_id > 0)
		return sd->status.partner_id;
	else
		return 0;
}
/*==========================================
 * sdがdstsdと結婚(dstsd→sdの結婚処理も同時に行う)
 *------------------------------------------
 */
int pc_marriage(struct map_session_data *sd,struct map_session_data *dstsd)
{
	if(sd == NULL || dstsd == NULL || sd->status.partner_id > 0 || dstsd->status.partner_id > 0)
		return -1;
	sd->status.partner_id=dstsd->status.char_id;
	dstsd->status.partner_id=sd->status.char_id;
	return 0;
}


/*==========================================
 * sdが離婚(相手はsd->status.partner_idに依る)(相手も同時に離婚・結婚指輪自動剥奪)
 *------------------------------------------
 */
int pc_divorce(struct map_session_data *sd)
{
	struct map_session_data *p_sd=NULL;
	if(sd == NULL || !pc_ismarried(sd))
		return -1;

	if( (p_sd=map_nick2sd(map_charid2nick(sd->status.partner_id))) !=NULL){
		int i;
		if(p_sd->status.partner_id != sd->status.char_id || sd->status.partner_id != p_sd->status.char_id){
			printf("pc_divorce: Illegal partner_id sd=%d p_sd=%d\n",sd->status.partner_id,p_sd->status.partner_id);
			return -1;
		}
		sd->status.partner_id=0;
		p_sd->status.partner_id=0;
		for(i=0;i<MAX_INVENTORY;i++)
			if(sd->status.inventory[i].nameid == WEDDING_RING_M || sd->status.inventory[i].nameid == WEDDING_RING_F)
				pc_delitem(sd,i,1,0);
		for(i=0;i<MAX_INVENTORY;i++)
			if(p_sd->status.inventory[i].nameid == WEDDING_RING_M || p_sd->status.inventory[i].nameid == WEDDING_RING_F)
				pc_delitem(p_sd,i,1,0);

	}else{
		printf("pc_divorce: p_sd nullpo\n");
		return -1;
	}
	return 0;
}

/*==========================================
 * sdの相方のmap_session_dataを返す
 *------------------------------------------
 */
struct map_session_data *pc_get_partner(struct map_session_data *sd)
{
	struct map_session_data *p_sd = NULL;
	char *nick;
	if(sd == NULL || !pc_ismarried(sd))
		return NULL;

	nick=map_charid2nick(sd->status.partner_id);

	if (nick==NULL)
		return NULL;

	if((p_sd=map_nick2sd(nick)) == NULL )
		return NULL;

	return p_sd;
}

//装備破壊
int pc_break_equip(struct map_session_data *sd, unsigned short where)
{
	int i;
	int sc;

	if(sd == NULL)
		return -1;
	if(sd->unbreakable_equip & where)
		return 0;
	switch (where) {
		case EQP_WEAPON:
			sc = SC_CP_WEAPON;
			break;
		case EQP_ARMOR:
			sc = SC_CP_ARMOR;
			break;
		case EQP_SHIELD:
			sc = SC_CP_SHIELD;
			break;
		case EQP_HELM:
			sc = SC_CP_HELM;
			break;
		default:
			return 0;
	}
	if( sd->sc_data && sd->sc_data[sc].timer != -1 )
		return 0;

	for (i=0;i<MAX_INVENTORY;i++) {
		if (sd->status.inventory[i].equip & where) {
			sd->status.inventory[i].attribute = 1;
			pc_unequipitem(sd,i,0);
			break;
		}
	}
	clif_itemlist(sd);
	clif_equiplist(sd);
	return 0;
}

//
// 自然回復物
//
/*==========================================
 * SP回復量計算
 *------------------------------------------
 */
static int natural_heal_tick,natural_heal_prev_tick,natural_heal_diff_tick;
static int pc_spheal(struct map_session_data *sd)
{
	int a;

	nullpo_retr(0, sd);

	a = natural_heal_diff_tick;
	if(pc_issit(sd)) a += a;
	if( sd->sc_data[SC_MAGNIFICAT].timer!=-1 )	// マグニフィカート
		a += a;

	return a;
}

/*==========================================
 * HP回復量計算
 *------------------------------------------
 */
static int pc_hpheal(struct map_session_data *sd)
{
	int a;

	nullpo_retr(0, sd);

	a = natural_heal_diff_tick;
	if(pc_issit(sd)) a += a;

	return a;
}

static int pc_natural_heal_hp(struct map_session_data *sd)
{
	int bhp;
	int inc_num,bonus,skill,hp_flag;

	nullpo_retr(0, sd);

	if(pc_checkoverhp(sd)) {
		sd->hp_sub = sd->inchealhptick = 0;
		return 0;
	}

	bhp=sd->status.hp;
	hp_flag = (pc_checkskill(sd,SM_MOVINGRECOVERY) > 0 && sd->walktimer != -1);

	if(sd->walktimer == -1) {
		inc_num = pc_hpheal(sd);
		if( sd->sc_data[SC_TENSIONRELAX].timer!=-1 ){	// テンションリラックス
			sd->hp_sub += 2*inc_num;
			sd->inchealhptick += 3*natural_heal_diff_tick;
		}else{
			sd->hp_sub += inc_num;
			sd->inchealhptick += natural_heal_diff_tick;
		}
	}
	else if(hp_flag) {
		inc_num = pc_hpheal(sd);
		sd->hp_sub += inc_num;
		sd->inchealhptick = 0;
	}
	else {
		sd->hp_sub = sd->inchealhptick = 0;
		return 0;
	}

	if(sd->hp_sub >= battle_config.natural_healhp_interval) {
		bonus = sd->nhealhp;
		if(hp_flag) {
			bonus >>= 2;
			if(bonus <= 0) bonus = 1;
		}
		while(sd->hp_sub >= battle_config.natural_healhp_interval) {
			sd->hp_sub -= battle_config.natural_healhp_interval;
			if(sd->status.hp + bonus <= sd->status.max_hp)
				sd->status.hp += bonus;
			else {
				sd->status.hp = sd->status.max_hp;
				sd->hp_sub = sd->inchealhptick = 0;
			}
		}
	}
	if(bhp!=sd->status.hp)
		clif_updatestatus(sd,SP_HP);

	if(sd->nshealhp > 0) {
		if(sd->inchealhptick >= battle_config.natural_heal_skill_interval && sd->status.hp < sd->status.max_hp) {
			bonus = sd->nshealhp;
			while(sd->inchealhptick >= battle_config.natural_heal_skill_interval) {
				sd->inchealhptick -= battle_config.natural_heal_skill_interval;
				if(sd->status.hp + bonus <= sd->status.max_hp)
					sd->status.hp += bonus;
				else {
					bonus = sd->status.max_hp - sd->status.hp;
					sd->status.hp = sd->status.max_hp;
					sd->hp_sub = sd->inchealhptick = 0;
				}
				clif_heal(sd->fd,SP_HP,bonus);
			}
		}
	}
	else sd->inchealhptick = 0;

	return 0;

	if(sd->sc_data[SC_APPLEIDUN].timer!=-1) { // Apple of Idun
		if(sd->inchealhptick >= 6000 && sd->status.hp < sd->status.max_hp) {
			bonus = skill*20;
			while(sd->inchealhptick >= 6000) {
				sd->inchealhptick -= 6000;
				if(sd->status.hp + bonus <= sd->status.max_hp)
					sd->status.hp += bonus;
				else {
					bonus = sd->status.max_hp - sd->status.hp;
					sd->status.hp = sd->status.max_hp;
					sd->hp_sub = sd->inchealhptick = 0;
				}
				clif_heal(sd->fd,SP_HP,bonus);
			}
		}
	}
	else sd->inchealhptick = 0;

	return 0;
}

static int pc_natural_heal_sp(struct map_session_data *sd)
{
	int bsp;
	int inc_num,bonus;
	struct pc_base_job s_class;

	nullpo_retr(0, sd);

	if(pc_checkoversp(sd)) {
		sd->sp_sub = sd->inchealsptick = 0;
		return 0;
	}

	bsp=sd->status.sp;

	s_class = pc_calc_base_job(sd->status.class);
	inc_num = pc_spheal(sd);
	if(s_class.job == 23 || sd->sc_data[SC_EXPLOSIONSPIRITS].timer == -1)
		sd->sp_sub += inc_num;
	if(sd->walktimer == -1)
		sd->inchealsptick += natural_heal_diff_tick;
	else sd->inchealsptick = 0;

	if(sd->sp_sub >= battle_config.natural_healsp_interval){
		bonus = sd->nhealsp;;
		while(sd->sp_sub >= battle_config.natural_healsp_interval){
			sd->sp_sub -= battle_config.natural_healsp_interval;
			if(sd->status.sp + bonus <= sd->status.max_sp)
				sd->status.sp += bonus;
			else {
				sd->status.sp = sd->status.max_sp;
				sd->sp_sub = sd->inchealsptick = 0;
			}
		}
	}

	if(bsp != sd->status.sp)
		clif_updatestatus(sd,SP_SP);

	if(sd->nshealsp > 0) {
		if(sd->inchealsptick >= battle_config.natural_heal_skill_interval && sd->status.sp < sd->status.max_sp) {
			if(sd->doridori_counter && s_class.job == 23)
				bonus = sd->nshealsp*2;
			else
				bonus = sd->nshealsp;
			sd->doridori_counter = 0;
			while(sd->inchealsptick >= battle_config.natural_heal_skill_interval) {
				sd->inchealsptick -= battle_config.natural_heal_skill_interval;
				if(sd->status.sp + bonus <= sd->status.max_sp)
					sd->status.sp += bonus;
				else {
					bonus = sd->status.max_sp - sd->status.sp;
					sd->status.sp = sd->status.max_sp;
					sd->sp_sub = sd->inchealsptick = 0;
				}
				clif_heal(sd->fd,SP_SP,bonus);
			}
		}
	}
	else sd->inchealsptick = 0;

	return 0;
}

static int pc_spirit_heal_hp(struct map_session_data *sd,int level)
{
	int bonus_hp,interval = battle_config.natural_heal_skill_interval;

	nullpo_retr(0, sd);

	if(pc_checkoverhp(sd)) {
		sd->inchealspirithptick = 0;
		return 0;
	}

	sd->inchealspirithptick += natural_heal_diff_tick;

	if(sd->weight*100/sd->max_weight >= battle_config.natural_heal_weight_rate)
		interval += interval;

	if(sd->inchealspirithptick >= interval) {
		bonus_hp = sd->nsshealhp;
		while(sd->inchealspirithptick >= interval) {
			if(pc_issit(sd)) {
				sd->inchealspirithptick -= interval;
				if(sd->status.hp < sd->status.max_hp) {
					if(sd->status.hp + bonus_hp <= sd->status.max_hp)
						sd->status.hp += bonus_hp;
					else {
						bonus_hp = sd->status.max_hp - sd->status.hp;
						sd->status.hp = sd->status.max_hp;
					}
					clif_heal(sd->fd,SP_HP,bonus_hp);
					sd->inchealspirithptick = 0;
				}
			}else{
				sd->inchealspirithptick -= natural_heal_diff_tick;
				break;
			}
		}
	}

	return 0;
}
static int pc_spirit_heal_sp(struct map_session_data *sd,int level)
{
	int bonus_sp,interval = battle_config.natural_heal_skill_interval;

	nullpo_retr(0, sd);

	if(pc_checkoversp(sd)) {
		sd->inchealspiritsptick = 0;
		return 0;
	}

	sd->inchealspiritsptick += natural_heal_diff_tick;

	if(sd->weight*100/sd->max_weight >= battle_config.natural_heal_weight_rate)
		interval += interval;

	if(sd->inchealspiritsptick >= interval) {
		bonus_sp = sd->nsshealsp;
		while(sd->inchealspiritsptick >= interval) {
			if(pc_issit(sd)) {
				sd->inchealspiritsptick -= interval;
				if(sd->status.sp < sd->status.max_sp) {
					if(sd->status.sp + bonus_sp <= sd->status.max_sp)
						sd->status.sp += bonus_sp;
					else {
						bonus_sp = sd->status.max_sp - sd->status.sp;
						sd->status.sp = sd->status.max_sp;
					}
					clif_heal(sd->fd,SP_SP,bonus_sp);
					sd->inchealspiritsptick = 0;
				}
			}else{
				sd->inchealspiritsptick -= natural_heal_diff_tick;
				break;
			}
		}
	}

	return 0;
}

/*==========================================
 * HP/SP 自然回復 各クライアント
 *------------------------------------------
 */

static int pc_natural_heal_sub(struct map_session_data *sd,va_list ap)
{
	int skill;

	nullpo_retr(0, sd);

	if( (battle_config.natural_heal_weight_rate > 100 || sd->weight*100/sd->max_weight < battle_config.natural_heal_weight_rate) &&
		!pc_isdead(sd) && 
		!pc_ishiding(sd) && 
		sd->sc_data[SC_POISON].timer == -1
	  ){
		pc_natural_heal_hp(sd);
		if( sd->sc_data && sd->sc_data[SC_EXTREMITYFIST].timer == -1 &&	//阿修羅状態ではSPが回復しない
			sd->sc_data[SC_DANCING].timer == -1 &&	//ダンス状態ではSPが回復しない
			sd->sc_data[SC_BERSERK].timer == -1 	//バーサーク状態ではSPが回復しない
		) 
			pc_natural_heal_sp(sd);
	}
	else {
		sd->hp_sub = sd->inchealhptick = 0;
		sd->sp_sub = sd->inchealsptick = 0;
	}
	if((skill = pc_checkskill(sd,MO_SPIRITSRECOVERY)) > 0 && !pc_ishiding(sd) && sd->sc_data[SC_POISON].timer == -1){
		pc_spirit_heal_hp(sd,skill);
		pc_spirit_heal_sp(sd,skill);
	}
	else {
		sd->inchealspirithptick = 0;
		sd->inchealspiritsptick = 0;
	}
	return 0;
}

/*==========================================
 * HP/SP自然回復 (interval timer関数)
 *------------------------------------------
 */
int pc_natural_heal(int tid,unsigned int tick,int id,int data)
{
	natural_heal_tick = tick;
	natural_heal_diff_tick = DIFF_TICK(natural_heal_tick,natural_heal_prev_tick);
	clif_foreachclient(pc_natural_heal_sub);

	natural_heal_prev_tick = tick;
	return 0;
}

/*==========================================
 * セーブポイントの保存
 *------------------------------------------
 */
int pc_setsavepoint(struct map_session_data *sd,char *mapname,int x,int y)
{
	nullpo_retr(0, sd);

	strncpy(sd->status.save_point.map,mapname,24);
	sd->status.save_point.x = x;
	sd->status.save_point.y = y;

	return 0;
}


/*==========================================
 * 自動セーブ 各クライアント
 *------------------------------------------
 */
static int last_save_fd,save_flag;
static int pc_autosave_sub(struct map_session_data *sd,va_list ap)
{
	nullpo_retr(0, sd);

	if(save_flag==0 && sd->fd>last_save_fd){
//		if(battle_config.save_log)
//			printf("autosave %d\n",sd->fd);
		// pet
		if(sd->status.pet_id > 0 && sd->pd)
			intif_save_petdata(sd->status.account_id,&sd->pet);
		pc_makesavestatus(sd);
		chrif_save(sd);
		storage_storage_save(sd);
		save_flag=1;
		last_save_fd = sd->fd;
	}

	return 0;
}


/*==========================================
 * 自動セーブ (timer関数)
 *------------------------------------------
 */
int pc_autosave(int tid,unsigned int tick,int id,int data)
{
	int interval;

	save_flag=0;
	clif_foreachclient(pc_autosave_sub);
	if(save_flag==0)
		last_save_fd=0;

	interval = autosave_interval/(clif_countusers()+1);
	if(interval <= 0)
		interval = 1;
	add_timer(gettick()+interval,pc_autosave,0,0);

	return 0;
}


int pc_read_gm_account()
{
	char line[8192];
	struct gm_account *p;
	FILE *fp;
	int c=0;

	gm_account_db=numdb_init();

	if( (fp=fopen(GM_account_filename,"r"))==NULL )
		return 1;
	while(fgets(line,sizeof(line),fp)){
		if(line[0] == '/' && line[1] == '/')
			continue;
		p=(struct gm_account *)aCalloc(1,sizeof(struct gm_account));
		if(sscanf(line,"%d %d",&p->account_id,&p->level) != 2 || p->level <= 0) {
			printf("gm_account: broken data [%s] line %d\n",GM_account_filename,c);
		}
		else {
			if(p->level > 99)
				p->level = 99;
			numdb_insert(gm_account_db,p->account_id,p);
		}
		c++;
	}
	fclose(fp);
	printf("read %s done (%d gm account ID)\n",GM_account_filename,c);

	return 0;
}

void pc_setstand(struct map_session_data *sd){
	nullpo_retv(sd);

	if(sd->sc_data && sd->sc_data[SC_TENSIONRELAX].timer!=-1)
		status_change_end(&sd->bl,SC_TENSIONRELAX,-1);

	sd->state.dead_sit = 0;
}

//
// 初期化物
//
/*==========================================
 * 設定ファイル読み込む
 * exp.txt 必要経験値
 * job_db1.txt 重量,hp,sp,攻撃速度 => status.c　に移動
 * job_db2.txt job能力値ボーナス
 * skill_tree.txt 各職毎のスキルツリー
 * attr_fix.txt 属性修正テーブル
 * size_fix.txt サイズ補正テーブル
 * refine_db.txt 精錬データテーブル
 *------------------------------------------
 */
int pc_readdb(void)
{
	int i,j,k,upper=0;
	FILE *fp;
	char line[1024],*p;

	// 必要経験値読み込み

	fp=fopen("db/exp.txt","r");
	if(fp==NULL){
		printf("can't read db/exp.txt\n");
		return 1;
	}
	i=0;
	while(fgets(line,1020,fp)){
		int bn,b1,b2,b3,b4,b5,b6,jn,j1,j2,j3,j4,j5,j6;
		if(line[0]=='/' && line[1]=='/')
			continue;
		if(sscanf(line,"%d,%d,%d,%d,%d,%d,%d,%d,%d,%d,%d,%d,%d,%d",&bn,&b1,&b2,&b3,&b4,&b5,&b6,&jn,&j1,&j2,&j3,&j4,&j5,&j6)!=14)
			continue;
		exp_table[0][i]=bn;
		exp_table[1][i]=b1;
		exp_table[2][i]=b2;
		exp_table[3][i]=b3;
		exp_table[4][i]=b4;
		exp_table[5][i]=b5;
		exp_table[6][i]=b6;
		exp_table[7][i]=jn;
		exp_table[8][i]=j1;
		exp_table[9][i]=j2;
		exp_table[10][i]=j3;
		exp_table[11][i]=j4;
		exp_table[12][i]=j5;
		exp_table[13][i]=j6;
		i++;
		if(i >= MAX_LEVEL)
			break;
	}
	fclose(fp);
	printf("read db/exp.txt done\n");

	// スキルツリー
	memset(skill_tree,0,sizeof(skill_tree));
	fp=fopen("db/skill_tree.txt","r");
	if(fp==NULL){
		printf("can't read db/skill_tree.txt\n");
		return 1;
	}
	while(fgets(line,1020,fp)){
		char *split[50];
		if(line[0]=='/' && line[1]=='/')
			continue;
		for(j=0,p=line;j<14 && p;j++){
			split[j]=p;
			p=strchr(p,',');
			if(p) *p++=0;
		}
		if(j<14)
			continue;
		upper=atoi(split[0]);
		if(upper>0 && battle_config.enable_upper_class==0){ //confで無効になっていたら
			continue;
		}
		i=atoi(split[1]);
		for(j=0;skill_tree[upper][i][j].id;j++);
		skill_tree[upper][i][j].id=atoi(split[2]);
		skill_tree[upper][i][j].max=atoi(split[3]);
		for(k=0;k<5;k++){
			skill_tree[upper][i][j].need[k].id=atoi(split[k*2+4]);
			skill_tree[upper][i][j].need[k].lv=atoi(split[k*2+5]);
		}
	}
	fclose(fp);
	printf("read db/skill_tree.txt done\n");

	// 属性修正テーブル
	for(i=0;i<4;i++)
		for(j=0;j<10;j++)
			for(k=0;k<10;k++)
				attr_fix_table[i][j][k]=100;
	fp=fopen("db/attr_fix.txt","r");
	if(fp==NULL){
		printf("can't read db/attr_fix.txt\n");
		return 1;
	}
	while(fgets(line,1020,fp)){
		char *split[10];
		int lv,n;
		if(line[0]=='/' && line[1]=='/')
			continue;
		for(j=0,p=line;j<3 && p;j++){
			split[j]=p;
			p=strchr(p,',');
			if(p) *p++=0;
		}
		lv=atoi(split[0]);
		n=atoi(split[1]);
//		printf("%d %d\n",lv,n);

		for(i=0;i<n;){
			if( !fgets(line,1024,fp) )
				break;
			if(line[0]=='/' && line[1]=='/')
				continue;

			for(j=0,p=line;j<n && p;j++){
				while(*p==32 && *p>0)
					p++;
				attr_fix_table[lv-1][i][j]=atoi(p);
				if(battle_config.attr_recover == 0 && attr_fix_table[lv-1][i][j] < 0)
					attr_fix_table[lv-1][i][j] = 0;
				p=strchr(p,',');
				if(p) *p++=0;
			}

			i++;
		}
	}
	printf("read db/attr_fix.txt done\n");

	return 0;
}

/*==========================================
 * 終了
 *------------------------------------------
 */
static int gm_account_db_final(void *key,void *data,va_list ap)
{
	struct gm_account *p=data;

	free(p);

	return 0;
}
int do_final_pc(void)
{
	if(gm_account_db)
		numdb_final(gm_account_db,gm_account_db_final);
	return 0;
}
/*==========================================
 * pc関係初期化
 *------------------------------------------
 */
int do_init_pc(void)
{
	pc_readdb();
	pc_read_gm_account();

	add_timer_func_list(pc_walk,"pc_walk");
	add_timer_func_list(pc_attack_timer,"pc_attack_timer");
	add_timer_func_list(pc_natural_heal,"pc_natural_heal");
	add_timer_func_list(pc_invincible_timer,"pc_invincible_timer");
	add_timer_func_list(pc_eventtimer,"pc_eventtimer");
	add_timer_func_list(pc_calc_pvprank_timer,"pc_calc_pvprank_timer");
	add_timer_func_list(pc_autosave,"pc_autosave");
	add_timer_func_list(pc_spiritball_timer,"pc_spiritball_timer");
	add_timer_interval((natural_heal_prev_tick=gettick()+NATURAL_HEAL_INTERVAL),pc_natural_heal,0,0,NATURAL_HEAL_INTERVAL);
	add_timer(gettick()+autosave_interval,pc_autosave,0,0);

	return 0;
}
