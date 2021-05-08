// $Id: map.c,v 1.3 2004/09/15 00:20:52 running_pinata Exp $
#include <stdio.h>
#include <stdlib.h>
#include <string.h>
#include <stdarg.h>
#ifndef _WIN32
	#include <netdb.h>
#endif

#include "core.h"
#include "timer.h"
#include "db.h"
#include "grfio.h"
#include "nullpo.h"
#include "malloc.h"
#include "map.h"
#include "chrif.h"
#include "clif.h"
#include "intif.h"
#include "npc.h"
#include "pc.h"
#include "mob.h"
#include "chat.h"
#include "itemdb.h"
#include "storage.h"
#include "skill.h"
#include "trade.h"
#include "party.h"
#include "battle.h"
#include "script.h"
#include "guild.h"
#include "pet.h"
#include "atcommand.h"
#include "status.h"

#ifdef MEMWATCH
#include "memwatch.h"
#endif

// 極力 staticでローカルに収める
static struct dbt * id_db;
static struct dbt * map_db;
static struct dbt * nick_db;
static struct dbt * charid_db;

static int users;
static struct block_list *object[MAX_FLOORITEM];
static int first_free_object_id,last_object_id;

#define block_free_max 1048576
static void *block_free[block_free_max];
static int block_free_count=0,block_free_lock=0;

#define BL_LIST_MAX 1048576
static struct block_list *bl_list[BL_LIST_MAX];
static int bl_list_count = 0;

struct map_data map[MAX_MAP_PER_SERVER];
int map_num=0;

int autosave_interval=DEFAULT_AUTOSAVE_INTERVAL;
int agit_flag=0;

extern int packet_parse_time;

struct charid2nick {
	char nick[24];
	int req_id;
	int account_id;
	unsigned long ip;
	unsigned int port;
};

// マップキャッシュ利用フラグ(map_athana.conf内のread_map_from_cacheで指定)
// 0:利用しない 1:非圧縮保存 2:圧縮保存
int map_read_flag=0;

char map_cache_file[256]="db/map.info"; // マップキャッシュファイル名
char motd_txt[256]="conf/motd.txt";
char help_txt[256]="conf/help.txt";

/*==========================================
 * 全map鯖総計での接続数設定
 * (char鯖から送られてくる)
 *------------------------------------------
 */
void map_setusers(int n)
{
	users=n;
}

/*==========================================
 * 全map鯖総計での接続数取得 (/wへの応答用)
 *------------------------------------------
 */
int map_getusers(void)
{
	return users;
}

//
// block削除の安全性確保処理
//

/*==========================================
 * blockをfreeするときfreeの変わりに呼ぶ
 * ロックされているときはバッファにためる
 *------------------------------------------
 */
int map_freeblock( void *bl )
{
	if(block_free_lock==0){
		free(bl);
		bl = NULL;
	}
	else{
		if( block_free_count>=block_free_max ) {
			if(battle_config.error_log)
				printf("map_freeblock: *WARNING* too many free block! %d %d\n",
			block_free_count,block_free_lock);
		}
		else
			block_free[block_free_count++]=bl;
	}
	return block_free_lock;
}
/*==========================================
 * blockのfreeを一時的に禁止する
 *------------------------------------------
 */
int map_freeblock_lock(void)
{
	return ++block_free_lock;
}
/*==========================================
 * blockのfreeのロックを解除する
 * このとき、ロックが完全になくなると
 * バッファにたまっていたblockを全部削除
 *------------------------------------------
 */
int map_freeblock_unlock(void)
{
	if( (--block_free_lock)==0 ){
		int i;
//		if(block_free_count>0) {
//			if(battle_config.error_log)
//				printf("map_freeblock_unlock: free %d object\n",block_free_count);
//		}
		for(i=0;i<block_free_count;i++){
			free(block_free[i]);
			block_free[i] = NULL;
		}
		block_free_count=0;
	}else if(block_free_lock<0){
		if(battle_config.error_log)
			printf("map_freeblock_unlock: lock count < 0 !\n");
	}
	return block_free_lock;
}


//
// block化処理
//
/*==========================================
 * map[]のblock_listから繋がっている場合に
 * bl->prevにbl_headのアドレスを入れておく
 *------------------------------------------
 */
static struct block_list bl_head;

/*==========================================
 * map[]のblock_listに追加
 * mobは数が多いので別リスト
 *
 * 既にlink済みかの確認が無い。危険かも
 *------------------------------------------
 */
int map_addblock(struct block_list *bl)
{
	int m,x,y;

	nullpo_retr(0, bl);

	if(bl->prev != NULL){
			if(battle_config.error_log)
				printf("map_addblock error : bl->prev!=NULL\n");
		return 0;
	}

	m=bl->m;
	x=bl->x;
	y=bl->y;
	if(m<0 || m>=map_num ||
	   x<0 || x>=map[m].xs ||
	   y<0 || y>=map[m].ys)
		return 1;

	if(bl->type==BL_MOB){
		bl->next = map[m].block_mob[x/BLOCK_SIZE+(y/BLOCK_SIZE)*map[m].bxs];
		bl->prev = &bl_head;
		if(bl->next) bl->next->prev = bl;
		map[m].block_mob[x/BLOCK_SIZE+(y/BLOCK_SIZE)*map[m].bxs] = bl;
		map[m].block_mob_count[x/BLOCK_SIZE+(y/BLOCK_SIZE)*map[m].bxs]++;
	} else {
		bl->next = map[m].block[x/BLOCK_SIZE+(y/BLOCK_SIZE)*map[m].bxs];
		bl->prev = &bl_head;
		if(bl->next) bl->next->prev = bl;
		map[m].block[x/BLOCK_SIZE+(y/BLOCK_SIZE)*map[m].bxs] = bl;
		map[m].block_count[x/BLOCK_SIZE+(y/BLOCK_SIZE)*map[m].bxs]++;
		if(bl->type==BL_PC)
			map[m].users++;
	}

	return 0;
}

/*==========================================
 * map[]のblock_listから外す
 * prevがNULLの場合listに繋がってない
 *------------------------------------------
 */
int map_delblock(struct block_list *bl)
{
	int b;
	nullpo_retr(0, bl);

	// 既にblocklistから抜けている
	if(bl->prev==NULL){
		if(bl->next!=NULL){
			// prevがNULLでnextがNULLでないのは有ってはならない
			if(battle_config.error_log)
				printf("map_delblock error : bl->next!=NULL\n");
		}
		return 0;
	}

	b = bl->x/BLOCK_SIZE+(bl->y/BLOCK_SIZE)*map[bl->m].bxs;

	if(bl->type==BL_PC)
		map[bl->m].users--;
	if(bl->next) bl->next->prev = bl->prev;
	if(bl->prev==&bl_head){
		// リストの頭なので、map[]のblock_listを更新する
		if(bl->type==BL_MOB){
			map[bl->m].block_mob[b] = bl->next;
			if((map[bl->m].block_mob_count[b]--) < 0)
				map[bl->m].block_mob_count[b] = 0;
		} else {
			map[bl->m].block[b] = bl->next;
			if((map[bl->m].block_count[b]--) < 0)
				map[bl->m].block_count[b] = 0;
		}
	} else {
		bl->prev->next = bl->next;
	}
	bl->next = NULL;
	bl->prev = NULL;

	return 0;
}

/*==========================================
 * 周囲のPC人数を数える (現在未使用)
 *------------------------------------------
 */
int map_countnearpc(int m,int x,int y)
{
	int bx,by,c=0;
	struct block_list *bl;

	if(map[m].users==0)
		return 0;
	for(by=y/BLOCK_SIZE-AREA_SIZE/BLOCK_SIZE-1;by<=y/BLOCK_SIZE+AREA_SIZE/BLOCK_SIZE+1;by++){
		if(by<0 || by>=map[m].bys)
			continue;
		for(bx=x/BLOCK_SIZE-AREA_SIZE/BLOCK_SIZE-1;bx<=x/BLOCK_SIZE+AREA_SIZE/BLOCK_SIZE+1;bx++){
			if(bx<0 || bx>=map[m].bxs)
				continue;
			bl = map[m].block[bx+by*map[m].bxs];
			for(;bl;bl=bl->next){
				if(bl->type==BL_PC)
					c++;
			}
		}
	}
	return c;
}

/*==========================================
 * セル上のPCとMOBの数を数える (グランドクロス用)
 *------------------------------------------
 */
int map_count_oncell(int m,int x,int y)
{
	int bx,by;
	struct block_list *bl;
	int i,c;
	int count = 0;

	if (x < 0 || y < 0 || (x >= map[m].xs) || (y >= map[m].ys))
		return 1;
	bx = x/BLOCK_SIZE;
	by = y/BLOCK_SIZE;

	bl = map[m].block[bx+by*map[m].bxs];
	c = map[m].block_count[bx+by*map[m].bxs];
	for(i=0;i<c && bl;i++,bl=bl->next){
		if(bl->x == x && bl->y == y && bl->type == BL_PC) count++;
	}
	bl = map[m].block_mob[bx+by*map[m].bxs];
	c = map[m].block_mob_count[bx+by*map[m].bxs];
	for(i=0;i<c && bl;i++,bl=bl->next){
		if(bl->x == x && bl->y == y) count++;
	}
	if(!count) count = 1;
	return count;
}
/*
 * セル上の最初に見つけたスキルユニットを返す
 */
struct skill_unit *map_find_skill_unit_oncell(int m,int x,int y,int skill_id)
{
	int bx,by;
	struct block_list *bl;
	int i,c;
	struct skill_unit *unit;

	if (x < 0 || y < 0 || (x >= map[m].xs) || (y >= map[m].ys))
		return NULL;
	bx = x/BLOCK_SIZE;
	by = y/BLOCK_SIZE;

	bl = map[m].block[bx+by*map[m].bxs];
	c = map[m].block_count[bx+by*map[m].bxs];
	for(i=0;i<c && bl;i++,bl=bl->next){
		if (bl->x != x || bl->y != y || bl->type != BL_SKILL)
			continue;
		unit = (struct skill_unit *) bl;
		if (unit->alive && unit->group->skill_id == skill_id)
			return unit;
	}
	return NULL;
}

/*==========================================
 * map m (x0,y0)-(x1,y1)内の全objに対して
 * funcを呼ぶ
 * type!=0 ならその種類のみ
 *------------------------------------------
 */
void map_foreachinarea(int (*func)(struct block_list*,va_list),int m,int x0,int y0,int x1,int y1,int type,...)
{
	int bx,by;
	struct block_list *bl;
	va_list ap;
	int blockcount=bl_list_count,i,c;

	va_start(ap,type);
	if (x0 < 0) x0 = 0;
	if (y0 < 0) y0 = 0;
	if (x1 >= map[m].xs) x1 = map[m].xs-1;
	if (y1 >= map[m].ys) y1 = map[m].ys-1;
	if (type == 0 || type != BL_MOB)
		for (by = y0 / BLOCK_SIZE; by <= y1 / BLOCK_SIZE; by++) {
			for(bx=x0/BLOCK_SIZE;bx<=x1/BLOCK_SIZE;bx++){
				bl = map[m].block[bx+by*map[m].bxs];
				c = map[m].block_count[bx+by*map[m].bxs];
				for(i=0;i<c && bl;i++,bl=bl->next){
					if(type && bl->type!=type)
						continue;
					if(bl->x>=x0 && bl->x<=x1 && bl->y>=y0 && bl->y<=y1 && bl_list_count<BL_LIST_MAX)
						bl_list[bl_list_count++]=bl;
				}
			}
		}
	if(type==0 || type==BL_MOB)
		for(by=y0/BLOCK_SIZE;by<=y1/BLOCK_SIZE;by++){
			for(bx=x0/BLOCK_SIZE;bx<=x1/BLOCK_SIZE;bx++){
				bl = map[m].block_mob[bx+by*map[m].bxs];
				c = map[m].block_mob_count[bx+by*map[m].bxs];
				for(i=0;i<c && bl;i++,bl=bl->next){
					if(bl->x>=x0 && bl->x<=x1 && bl->y>=y0 && bl->y<=y1 && bl_list_count<BL_LIST_MAX)
						bl_list[bl_list_count++]=bl;
				}
			}
		}

	if(bl_list_count>=BL_LIST_MAX) {
		if(battle_config.error_log)
			printf("map_foreachinarea: *WARNING* block count too many!\n");
	}

	map_freeblock_lock();	// メモリからの解放を禁止する

	for(i=blockcount;i<bl_list_count;i++)
		if(bl_list[i]->prev)	// 有効かどうかチェック
			func(bl_list[i],ap);

	map_freeblock_unlock();	// 解放を許可する

	va_end(ap);
	bl_list_count = blockcount;
}

/*==========================================
 * 矩形(x0,y0)-(x1,y1)が(dx,dy)移動した時の
 * 領域外になる領域(矩形かL字形)内のobjに
 * 対してfuncを呼ぶ
 *
 * dx,dyは-1,0,1のみとする（どんな値でもいいっぽい？）
 *------------------------------------------
 */
void map_foreachinmovearea(int (*func)(struct block_list*,va_list),int m,int x0,int y0,int x1,int y1,int dx,int dy,int type,...)
{
	int bx,by;
	struct block_list *bl;
	va_list ap;
	int blockcount=bl_list_count,i,c;

	va_start(ap,type);
	if(dx==0 || dy==0){
		// 矩形領域の場合
		if(dx==0){
			if(dy<0){
				y0=y1+dy+1;
			} else {
				y1=y0+dy-1;
			}
		} else if(dy==0){
			if(dx<0){
				x0=x1+dx+1;
			} else {
				x1=x0+dx-1;
			}
		}
		if(x0<0) x0=0;
		if(y0<0) y0=0;
		if(x1>=map[m].xs) x1=map[m].xs-1;
		if(y1>=map[m].ys) y1=map[m].ys-1;
		for(by=y0/BLOCK_SIZE;by<=y1/BLOCK_SIZE;by++){
			for(bx=x0/BLOCK_SIZE;bx<=x1/BLOCK_SIZE;bx++){
				bl = map[m].block[bx+by*map[m].bxs];
				c = map[m].block_count[bx+by*map[m].bxs];
				for(i=0;i<c && bl;i++,bl=bl->next){
					if(type && bl->type!=type)
						continue;
					if(bl->x>=x0 && bl->x<=x1 && bl->y>=y0 && bl->y<=y1 && bl_list_count<BL_LIST_MAX)
						bl_list[bl_list_count++]=bl;
				}
				bl = map[m].block_mob[bx+by*map[m].bxs];
				c = map[m].block_mob_count[bx+by*map[m].bxs];
				for(i=0;i<c && bl;i++,bl=bl->next){
					if(type && bl->type!=type)
						continue;
					if(bl->x>=x0 && bl->x<=x1 && bl->y>=y0 && bl->y<=y1 && bl_list_count<BL_LIST_MAX)
						bl_list[bl_list_count++]=bl;
				}
			}
		}
	}else{
		// L字領域の場合

		if(x0<0) x0=0;
		if(y0<0) y0=0;
		if(x1>=map[m].xs) x1=map[m].xs-1;
		if(y1>=map[m].ys) y1=map[m].ys-1;
		for(by=y0/BLOCK_SIZE;by<=y1/BLOCK_SIZE;by++){
			for(bx=x0/BLOCK_SIZE;bx<=x1/BLOCK_SIZE;bx++){
				bl = map[m].block[bx+by*map[m].bxs];
				c = map[m].block_count[bx+by*map[m].bxs];
				for(i=0;i<c && bl;i++,bl=bl->next){
					if(type && bl->type!=type)
						continue;
					if(!(bl->x>=x0 && bl->x<=x1 && bl->y>=y0 && bl->y<=y1))
						continue;
					if(((dx>0 && bl->x<x0+dx) || (dx<0 && bl->x>x1+dx) ||
						(dy>0 && bl->y<y0+dy) || (dy<0 && bl->y>y1+dy)) &&
						bl_list_count<BL_LIST_MAX)
							bl_list[bl_list_count++]=bl;
				}
				bl = map[m].block_mob[bx+by*map[m].bxs];
				c = map[m].block_mob_count[bx+by*map[m].bxs];
				for(i=0;i<c && bl;i++,bl=bl->next){
					if(type && bl->type!=type)
						continue;
					if(!(bl->x>=x0 && bl->x<=x1 && bl->y>=y0 && bl->y<=y1))
						continue;
					if(((dx>0 && bl->x<x0+dx) || (dx<0 && bl->x>x1+dx) ||
						(dy>0 && bl->y<y0+dy) || (dy<0 && bl->y>y1+dy)) &&
						bl_list_count<BL_LIST_MAX)
							bl_list[bl_list_count++]=bl;
				}
			}
		}

	}

	if(bl_list_count>=BL_LIST_MAX) {
		if(battle_config.error_log)
			printf("map_foreachinarea: *WARNING* block count too many!\n");
	}

	map_freeblock_lock();	// メモリからの解放を禁止する

	for(i=blockcount;i<bl_list_count;i++)
		if(bl_list[i]->prev)	// 有効かどうかチェック
			func(bl_list[i],ap);

	map_freeblock_unlock();	// 解放を許可する

	va_end(ap);
	bl_list_count = blockcount;
}

/*==========================================
 * 床アイテムやエフェクト用の一時obj割り当て
 * object[]への保存とid_db登録まで
 *
 * bl->idもこの中で設定して問題無い?
 *------------------------------------------
 */
int map_addobject(struct block_list *bl)
{
	int i;
	if( bl == NULL ){
		printf("map_addobject nullpo?\n");
		return 0;
	}
	if(first_free_object_id<2 || first_free_object_id>=MAX_FLOORITEM)
		first_free_object_id=2;
	for(i=first_free_object_id;i<MAX_FLOORITEM;i++)
		if(object[i]==NULL)
			break;
	if(i>=MAX_FLOORITEM){
		if(battle_config.error_log)
			printf("no free object id\n");
		return 0;
	}
	first_free_object_id=i;
	if(last_object_id<i)
		last_object_id=i;
	object[i]=bl;
	numdb_insert(id_db,i,bl);
	return i;
}

/*==========================================
 * 一時objectの解放
 *	map_delobjectのfreeしないバージョン
 *------------------------------------------
 */
int map_delobjectnofree(int id)
{
	if(object[id]==NULL)
		return 0;

	map_delblock(object[id]);
	numdb_erase(id_db,id);
//	map_freeblock(object[id]);
	object[id]=NULL;

	if(first_free_object_id>id)
		first_free_object_id=id;

	while(last_object_id>2 && object[last_object_id]==NULL)
		last_object_id--;

	return 0;
}

/*==========================================
 * 一時objectの解放
 * block_listからの削除、id_dbからの削除
 * object dataのfree、object[]へのNULL代入
 *
 * addとの対称性が無いのが気になる
 *------------------------------------------
 */
int map_delobject(int id)
{
	struct block_list *obj=object[id];

	if(obj==NULL)
		return 0;

	map_delobjectnofree(id);
	map_freeblock(obj);

	return 0;
}

/*==========================================
 * 全一時obj相手にfuncを呼ぶ
 *
 *------------------------------------------
 */
void map_foreachobject(int (*func)(struct block_list*,va_list),int type,...)
{
	int i;
	int blockcount=bl_list_count;
	va_list ap;

	va_start(ap,type);

	for(i=2;i<=last_object_id;i++){
		if(object[i]){
			if(type && object[i]->type!=type)
				continue;
			if(bl_list_count>=BL_LIST_MAX) {
				if(battle_config.error_log)
					printf("map_foreachobject: too many block !\n");
			}
			else
				bl_list[bl_list_count++]=object[i];
		}
	}

	map_freeblock_lock();

	for(i=blockcount;i<bl_list_count;i++)
		if( bl_list[i]->prev || bl_list[i]->next )
			func(bl_list[i],ap);

	map_freeblock_unlock();

	va_end(ap);
	bl_list_count = blockcount;
}

/*==========================================
 * 床アイテムを消す
 *
 * data==0の時はtimerで消えた時
 * data!=0の時は拾う等で消えた時として動作
 *
 * 後者は、map_clearflooritem(id)へ
 * map.h内で#defineしてある
 *------------------------------------------
 */
int map_clearflooritem_timer(int tid,unsigned int tick,int id,int data)
{
	struct flooritem_data *fitem;

	fitem = (struct flooritem_data *)object[id];
	if(fitem==NULL || fitem->bl.type!=BL_ITEM || (!data && fitem->cleartimer != tid)){
		if(battle_config.error_log)
			printf("map_clearflooritem_timer : error\n");
		return 1;
	}
	if(data)
		delete_timer(fitem->cleartimer,map_clearflooritem_timer);
	else if(fitem->item_data.card[0] == (short)0xff00)
		intif_delete_petdata(*((long *)(&fitem->item_data.card[1])));
	clif_clearflooritem(fitem,0);
	map_delobject(fitem->bl.id);

	return 0;
}

/*==========================================
 * (m,x,y)の周囲rangeマス内の空き(=侵入可能)cellの
 * 内から適当なマス目の座標をx+(y<<16)で返す
 *
 * 現状range=1でアイテムドロップ用途のみ
 *------------------------------------------
 */
int map_searchrandfreecell(int m,int x,int y,int range)
{
	int free_cell,i,j;

	for(free_cell=0,i=-range;i<=range;i++){
		if(i+y<0 || i+y>=map[m].ys)
			continue;
		for(j=-range;j<=range;j++){
			if(j+x<0 || j+x>=map[m].xs)
				continue;
			if(map_getcell(m,j+x,i+y,CELL_CHKNOPASS))
				continue;
			free_cell++;
		}
	}
	if(free_cell==0)
		return -1;
	free_cell=rand()%free_cell;
	for(i=-range;i<=range;i++){
		if(i+y<0 || i+y>=map[m].ys)
			continue;
		for(j=-range;j<=range;j++){
			if(j+x<0 || j+x>=map[m].xs)
				continue;
			if(map_getcell(m,j+x,i+y,CELL_CHKNOPASS))
				continue;
			if(free_cell==0){
				x+=j;
				y+=i;
				i=range+1;
				break;
			}
			free_cell--;
		}
	}

	return x+(y<<16);
}

/*==========================================
 * (m,x,y)を中心に3x3以内に床アイテム設置
 *
 * item_dataはamount以外をcopyする
 *------------------------------------------
 */
int map_addflooritem(struct item *item_data,int amount,int m,int x,int y,struct map_session_data *first_sd,
	struct map_session_data *second_sd,struct map_session_data *third_sd,int type)
{
	int xy,r;
	unsigned int tick;
	struct flooritem_data *fitem;

	nullpo_retr(0, item_data);

	if((xy=map_searchrandfreecell(m,x,y,1))<0)
		return 0;
	r=rand();

	fitem = (struct flooritem_data *)aCalloc(1,sizeof(*fitem));
	fitem->bl.type=BL_ITEM;
	fitem->bl.prev = fitem->bl.next = NULL;
	fitem->bl.m=m;
	fitem->bl.x=xy&0xffff;
	fitem->bl.y=(xy>>16)&0xffff;
	fitem->first_get_id = 0;
	fitem->first_get_tick = 0;
	fitem->second_get_id = 0;
	fitem->second_get_tick = 0;
	fitem->third_get_id = 0;
	fitem->third_get_tick = 0;

	fitem->bl.id = map_addobject(&fitem->bl);
	if(fitem->bl.id==0){
		free(fitem);
		return 0;
	}

	tick = gettick();
	if(first_sd) {
		fitem->first_get_id = first_sd->bl.id;
		if(type)
			fitem->first_get_tick = tick + battle_config.mvp_item_first_get_time;
		else
			fitem->first_get_tick = tick + battle_config.item_first_get_time;
	}
	if(second_sd) {
		fitem->second_get_id = second_sd->bl.id;
		if(type)
			fitem->second_get_tick = tick + battle_config.mvp_item_first_get_time + battle_config.mvp_item_second_get_time;
		else
			fitem->second_get_tick = tick + battle_config.item_first_get_time + battle_config.item_second_get_time;
	}
	if(third_sd) {
		fitem->third_get_id = third_sd->bl.id;
		if(type)
			fitem->third_get_tick = tick + battle_config.mvp_item_first_get_time + battle_config.mvp_item_second_get_time + battle_config.mvp_item_third_get_time;
		else
			fitem->third_get_tick = tick + battle_config.item_first_get_time + battle_config.item_second_get_time + battle_config.item_third_get_time;
	}

	memcpy(&fitem->item_data,item_data,sizeof(*item_data));
	fitem->item_data.amount=amount;
	fitem->subx=(r&3)*3+3;
	fitem->suby=((r>>2)&3)*3+3;
	fitem->cleartimer=add_timer(gettick()+battle_config.flooritem_lifetime,map_clearflooritem_timer,fitem->bl.id,0);

	map_addblock(&fitem->bl);
	clif_dropflooritem(fitem);

	return fitem->bl.id;
}

/*==========================================
 * charid_dbへ追加(返信待ちがあれば返信)
 *------------------------------------------
 */
void map_addchariddb(int charid, char *name, int account_id, unsigned long ip, int port)
{
	struct charid2nick *p;
	int req=0;
	p=numdb_search(charid_db,charid);
	if(p==NULL){	// データベースにない
		p = (struct charid2nick *)aCalloc(1,sizeof(struct charid2nick));
		p->req_id=0;
	}else
		numdb_erase(charid_db,charid);

	req=p->req_id;
	memcpy(p->nick,name,24);
	p->account_id=account_id;
	p->ip=ip;
	p->port=port;
	p->req_id=0;
	numdb_insert(charid_db,charid,p);
	if(req){	// 返信待ちがあれば返信
		struct map_session_data *sd = map_id2sd(req);
		if(sd!=NULL)
			clif_solved_charname(sd,charid);
	}
	//printf("map add chariddb:%s\n",p->nick);
	return;
}
/*==========================================
 * charid_dbから削除
 *------------------------------------------
 */
void map_delchariddb(int charid)
{
	struct charid2nick *p;
	p=numdb_search(charid_db,charid);
	if(p){	// データベースにあった
		p->ip=0;	//実際に削除すると武器の名前とか取れなくなるのでmap-serverのIPとPortだけ削除
		p->port=0;
//		printf("map delete chariddb:%s\n",p->nick);
	}//else
//		printf("map delete chariddb:notfound %d\n",charid);

	return;
}
/*==========================================
 * charid_dbへ追加（返信要求のみ）
 *------------------------------------------
 */
int map_reqchariddb(struct map_session_data * sd,int charid)
{
	struct charid2nick *p;

	nullpo_retr(0, sd);

	p=numdb_search(charid_db,charid);
	if(p!=NULL)	// データベースにすでにある
		return 0;
	p = (struct charid2nick *)aCalloc(1,sizeof(struct charid2nick));
	p->req_id=sd->bl.id;
	numdb_insert(charid_db,charid,p);
	return 0;
}

/*==========================================
 * id_dbへblを追加
 *------------------------------------------
 */
void map_addiddb(struct block_list *bl)
{
	nullpo_retv(bl);

	numdb_insert(id_db,bl->id,bl);
}

/*==========================================
 * id_dbからblを削除
 *------------------------------------------
 */
void map_deliddb(struct block_list *bl)
{
	nullpo_retv(bl);

	numdb_erase(id_db,bl->id);
}

/*==========================================
 * nick_dbへsdを追加
 *------------------------------------------
 */
void map_addnickdb(struct map_session_data *sd)
{
	nullpo_retv(sd);

	strdb_insert(nick_db,sd->status.name,sd);
}

/*==========================================
 * PCのquit処理 map.c内分
 *
 * quit処理の主体が違うような気もしてきた
 *------------------------------------------
 */
int map_quit(struct map_session_data *sd)
{
	int i;

	nullpo_retr(0, sd);

	if(sd->chatID)	// チャットから出る
		chat_leavechat(sd);

	if(sd->trade_partner)	// 取引を中断する
		trade_tradecancel(sd);

	if(sd->party_invite>0)	// パーティ勧誘を拒否する
		party_reply_invite(sd,sd->party_invite_account,0);

	if(sd->guild_invite>0)	// ギルド勧誘を拒否する
		guild_reply_invite(sd,sd->guild_invite,0);
	if(sd->guild_alliance>0)	// ギルド同盟勧誘を拒否する
		guild_reply_reqalliance(sd,sd->guild_alliance_account,0);

	party_send_logout(sd);	// パーティのログアウトメッセージ送信

	guild_send_memberinfoshort(sd,0);	// ギルドのログアウトメッセージ送信

	pc_cleareventtimer(sd);	// イベントタイマを破棄する

	if(sd->state.storage_flag)
		storage_guild_storage_quit(sd,0);
	else
		storage_storage_quit(sd);	// 倉庫を開いてるなら保存する

	skill_castcancel(&sd->bl,0);	// 詠唱を中断する
	skill_stop_dancing(&sd->bl,1);// ダンス/演奏中断

	if(sd->sc_data && sd->sc_data[SC_BERSERK].timer!=-1) //バーサーク中の終了はHPを100に
		sd->status.hp = 100;

	if(sd->sc_data && sd->sc_data[SC_WEDDING].timer!=-1) //結婚中はリログしても1時間は継続
		pc_setglobalreg(sd,"PC_WEDDING_TIME",sd->sc_data[SC_WEDDING].val2);
	else
		pc_setglobalreg(sd,"PC_WEDDING_TIME",0);

	skill_status_change_clear(&sd->bl,1);	// ステータス異常を解除する
	skill_clear_unitgroup(&sd->bl);	// スキルユニットグループの削除
	skill_cleartimerskill(&sd->bl);
	pc_stop_walking(sd,0);
	pc_stopattack(sd);
	pc_delinvincibletimer(sd);
	pc_delspiritball(sd,sd->spiritball,1);
	skill_gangsterparadise(sd,0);

	status_calc_pc(sd,4);

	clif_clearchar_area(&sd->bl,2);

	if(sd->status.pet_id && sd->pd) {
		pet_lootitem_drop(sd->pd,sd);
		pet_remove_map(sd);
		if(sd->pet.intimate <= 0) {
			intif_delete_petdata(sd->status.pet_id);
			sd->status.pet_id = 0;
			sd->pd = NULL;
			sd->petDB = NULL;
		}
		else
			intif_save_petdata(sd->status.account_id,&sd->pet);
	}

	if(pc_isdead(sd))
		pc_setrestartvalue(sd,2);
	pc_makesavestatus(sd);
	//クローンスキルで覚えたスキルは消す
	for(i=0;i<MAX_SKILL;i++){
		if(sd->status.skill[i].flag == 13){
			sd->status.skill[i].id=0;
			sd->status.skill[i].lv=0;
			sd->status.skill[i].flag=0;
		}
	}
	chrif_save(sd);
	storage_storage_save(sd);
	storage_delete(sd->status.account_id);

	if( sd->npc_stackbuf && sd->npc_stackbuf != NULL)
		free( sd->npc_stackbuf );

	map_delblock(&sd->bl);

	numdb_erase(id_db,sd->bl.id);
	strdb_erase(nick_db,sd->status.name);
	numdb_erase(charid_db,sd->status.char_id);
//printf("map quit:%s\n",sd->status.name);

	return 0;
}

/*==========================================
 * id番号のPCを探す。居なければNULL
 *------------------------------------------
 */
struct map_session_data * map_id2sd(int id)
{
	struct block_list *bl;

	bl=numdb_search(id_db,id);
	if(bl && bl->type==BL_PC)
		return (struct map_session_data*)bl;
	return NULL;
}

/*==========================================
 * char_id番号の名前を探す
 *------------------------------------------
 */
char * map_charid2nick(int id)
{
	struct charid2nick *p=numdb_search(charid_db,id);
	if(p==NULL)
		return NULL;
	if(p->req_id!=0)
		return NULL;
	return p->nick;
}

/*==========================================
 * 名前がnickのPCを探す。居なければNULL
 *------------------------------------------
 */
struct map_session_data * map_nick2sd(char *nick)
{
	if(nick == NULL)
		return NULL;
	return strdb_search(nick_db,nick);
}

/*==========================================
 * id番号の物を探す
 * 一時objectの場合は配列を引くのみ
 *------------------------------------------
 */
struct block_list * map_id2bl(int id)
{
	struct block_list *bl;
	if(id<sizeof(object)/sizeof(object[0]))
		bl = object[id];
	else
		bl = numdb_search(id_db,id);

	return bl;
}

/*==========================================
 * id_db内の全てにfuncを実行
 *------------------------------------------
 */
int map_foreachiddb(int (*func)(void*,void*,va_list),...)
{
	va_list ap;

	va_start(ap,func);
	numdb_foreach(id_db,func,ap);
	va_end(ap);
	return 0;
}

/*==========================================
 * map.npcへ追加 (warp等の領域持ちのみ)
 *------------------------------------------
 */
int map_addnpc(int m,struct npc_data *nd)
{
	int i;
	if(m<0 || m>=map_num)
		return -1;
	for(i=0;i<map[m].npc_num && i<MAX_NPC_PER_MAP;i++)
		if(map[m].npc[i]==NULL)
			break;
	if(i==MAX_NPC_PER_MAP){
		if(battle_config.error_log)
			printf("too many NPCs in one map %s\n",map[m].name);
		return -1;
	}
	if(i==map[m].npc_num){
		map[m].npc_num++;
	}

	nullpo_retr(0, nd);

	map[m].npc[i]=nd;
	nd->n = i;
	numdb_insert(id_db,nd->bl.id,nd);

	return i;
}

/*==========================================
 * map名からmap番号へ変換
 *------------------------------------------
 */
int map_mapname2mapid(char *name)
{
	struct map_data *md;

	md=strdb_search(map_db,name);
	if(md==NULL || md->gat==NULL)
		return -1;
	return md->m;
}

/*==========================================
 * 他鯖map名からip,port変換
 *------------------------------------------
 */
int map_mapname2ipport(char *name,int *ip,int *port)
{
	struct map_data_other_server *mdos;

	mdos=strdb_search(map_db,name);
	if(mdos==NULL || mdos->gat)
		return -1;
	*ip=mdos->ip;
	*port=mdos->port;
	return 0;
}

/*==========================================
 *
 *------------------------------------------
 */
int map_check_dir(int s_dir,int t_dir)
{
	if(s_dir == t_dir)
		return 0;
	switch(s_dir) {
		case 0:
			if(t_dir == 7 || t_dir == 1 || t_dir == 0)
				return 0;
			break;
		case 1:
			if(t_dir == 0 || t_dir == 2 || t_dir == 1)
				return 0;
			break;
		case 2:
			if(t_dir == 1 || t_dir == 3 || t_dir == 2)
				return 0;
			break;
		case 3:
			if(t_dir == 2 || t_dir == 4 || t_dir == 3)
				return 0;
			break;
		case 4:
			if(t_dir == 3 || t_dir == 5 || t_dir == 4)
				return 0;
			break;
		case 5:
			if(t_dir == 4 || t_dir == 6 || t_dir == 5)
				return 0;
			break;
		case 6:
			if(t_dir == 5 || t_dir == 7 || t_dir == 6)
				return 0;
			break;
		case 7:
			if(t_dir == 6 || t_dir == 0 || t_dir == 7)
				return 0;
			break;
	}
	return 1;
}

/*==========================================
 * 彼我の方向を計算
 *------------------------------------------
 */
int map_calc_dir( struct block_list *src,int x,int y)
{
	int dir=0;
	int dx,dy;

	nullpo_retr(0, src);

	dx=x-src->x;
	dy=y-src->y;
	if( dx==0 && dy==0 ){	// 彼我の場所一致
		dir=0;	// 上
	}else if( dx>=0 && dy>=0 ){	// 方向的に右上
		dir=7;						// 右上
		if( dx*3-1<dy ) dir=0;		// 上
		if( dx>dy*3 ) dir=6;		// 右
	}else if( dx>=0 && dy<=0 ){	// 方向的に右下
		dir=5;						// 右下
		if( dx*3-1<-dy ) dir=4;		// 下
		if( dx>-dy*3 ) dir=6;		// 右
	}else if( dx<=0 && dy<=0 ){ // 方向的に左下
		dir=3;						// 左下
		if( dx*3+1>dy ) dir=4;		// 下
		if( dx<dy*3 ) dir=2;		// 左
	}else{						// 方向的に左上
		dir=1;						// 左上
		if( -dx*3-1<dy ) dir=0;		// 上
		if( -dx>dy*3 ) dir=2;		// 左
	}
	return dir;
}

// gat系
/*==========================================
 * (m,x,y)の状態を調べる
 *------------------------------------------
 */
int map_getcell(int m,int x,int y,cell_t cellchk)
{
	return (m < 0 || m > MAX_MAP_PER_SERVER) ? 0 : map_getcellp(&map[m],x,y,cellchk);
}

int map_getcellp(struct map_data* m,int x,int y,cell_t cellchk)
{
	int j;
	nullpo_ret(m);

	if(x<0 || x>=m->xs-1 || y<0 || y>=m->ys-1)
	{
		if(cellchk==CELL_CHKNOPASS) return 1;
		return 0;
	}
	j=x+y*m->xs;

	switch(cellchk)
	{
		case CELL_CHKPASS:
			return (m->gat[j] != 1 && m->gat[j] != 5);
		case CELL_CHKNOPASS:
			return (m->gat[j] == 1 || m->gat[j] == 5);
		case CELL_CHKWALL:
			return (m->gat[j] == 1);
		case CELL_CHKNPC:
			return (m->gat[j]&0x80);
		case CELL_CHKWATER:
			return (m->gat[j] == 3);
		case CELL_CHKGROUND:
			return (m->gat[j] == 5);
		case CELL_GETTYPE:
			return m->gat[j];
		default:
			return 0;
	}	
}

/*==========================================
 * (m,x,y)の状態を設定する
 *------------------------------------------
 */
void map_setcell(int m,int x,int y,int cell)
{
	int j;
	if(x<0 || x>=map[m].xs || y<0 || y>=map[m].ys)
		return;
	j=x+y*map[m].xs;

	if (cell == CELL_SETNPC)
		map[m].gat[j] |= 0x80;
	else
		map[m].gat[j] = cell;
}

/*==========================================
 * 他鯖管理のマップをdbに追加
 *------------------------------------------
 */
int map_setipport(char *name,unsigned long ip,int port)
{
	struct map_data *md;
	struct map_data_other_server *mdos;

	md=strdb_search(map_db,name);
	if(md==NULL){ // not exist -> add new data
		mdos=(struct map_data_other_server *)aCalloc(1,sizeof(struct map_data_other_server));
		memcpy(mdos->name,name,24);
		mdos->gat  = NULL;
		mdos->ip   = ip;
		mdos->port = port;
		strdb_insert(map_db,mdos->name,mdos);
	} else {
		if(md->gat){ // local -> check data
			if(ip!=clif_getip() || port!=clif_getport()){
				printf("from char server : %s -> %08lx:%d\n",name,ip,port);
				return 1;
			}
		} else { // update
			mdos=(struct map_data_other_server *)md;
			mdos->ip   = ip;
			mdos->port = port;
		}
	}
	return 0;
}
/*==========================================
 * 他鯖管理のマップをdbから削除
 *------------------------------------------
 */
int map_eraseipport(char *name,unsigned long ip,int port)
{
	struct map_data *md;
	struct map_data_other_server *mdos;
//	unsigned char *p=(unsigned char *)&ip;

	md=strdb_search(map_db,name);
	if(md){
		if(md->gat) // local -> check data
			return 0;
		else{
			mdos=(struct map_data_other_server *)md;
			if(mdos->ip==ip && mdos->port == port){
				strdb_erase(map_db,name);
//				if(battle_config.etc_log)
//					printf("erase map %s %d.%d.%d.%d:%d\n",name,p[0],p[1],p[2],p[3],port);
			}
		}
	}
	return 0;
}

// 初期化周り
/*==========================================
 * 水場高さ設定
 *------------------------------------------
 */
static struct {
	char mapname[24];
	int waterheight;
} *waterlist=NULL;

#define NO_WATER 1000000

static int map_waterheight(char *mapname)
{
	if(waterlist){
		int i;
		for(i=0;waterlist[i].mapname[0] && i < MAX_MAP_PER_SERVER;i++)
			if(strcmp(waterlist[i].mapname,mapname)==0)
				return waterlist[i].waterheight;
	}
	return NO_WATER;
}

static void map_readwater(char *watertxt)
{
	char line[1024],w1[1024];
	FILE *fp;
	int n=0;

	fp=fopen(watertxt,"r");
	if(fp==NULL){
		printf("file not found: %s\n",watertxt);
		return;
	}
	if(waterlist==NULL)
		waterlist=aCalloc(MAX_MAP_PER_SERVER,sizeof(*waterlist));
	while(fgets(line,1020,fp) && n < MAX_MAP_PER_SERVER){
		int wh,count;
		if(line[0] == '/' && line[1] == '/')
			continue;
		if((count=sscanf(line,"%s%d",w1,&wh)) < 1){
			continue;
		}
		strncpy(waterlist[n].mapname,w1,24);
		if(count >= 2)
			waterlist[n].waterheight = wh;
		else
			waterlist[n].waterheight = 3;
		n++;
	}
	fclose(fp);
}

/*==========================================
* マップキャッシュに追加する
*===========================================*/

// マップキャッシュの最大値
#define MAX_MAP_CACHE 768

//各マップごとの最小限情報を入れるもの、READ_FROM_BITMAP用
struct map_cache_info {
	char fn[32];//ファイル名
	int xs,ys; //幅と高さ
	int water_height;
	int pos;  // データが入れてある場所
	int compressed;     // zilb通せるようにする為の予約
	int compressed_len; // zilb通せるようにする為の予約
}; // 56 byte

struct {
	struct map_cache_head {
		int sizeof_header;
		int sizeof_map;
		// 上の２つ改変不可
		int nmaps; // マップの個数
		int filesize;
	} head;
	struct map_cache_info *map;
	FILE *fp;
	int dirty;
} map_cache;

static int map_cache_open(char *fn);
static void map_cache_close(void);
static int map_cache_read(struct map_data *m);
static int map_cache_write(struct map_data *m);

static int map_cache_open(char *fn)
{
	atexit(map_cache_close);
	if(map_cache.fp) {
		map_cache_close();
	}
	map_cache.fp = fopen(fn,"r+b");
	if(map_cache.fp) {
		fread(&map_cache.head,1,sizeof(struct map_cache_head),map_cache.fp);
		fseek(map_cache.fp,0,SEEK_END);
		if(
			map_cache.head.sizeof_header == sizeof(struct map_cache_head) &&
			map_cache.head.sizeof_map    == sizeof(struct map_cache_info) &&
			map_cache.head.nmaps         == MAX_MAP_CACHE &&
			map_cache.head.filesize      == ftell(map_cache.fp)
		) {
			// キャッシュ読み込み成功
			map_cache.map = aMalloc(sizeof(struct map_cache_info) * map_cache.head.nmaps);
			fseek(map_cache.fp,sizeof(struct map_cache_head),SEEK_SET);
			fread(map_cache.map,sizeof(struct map_cache_info),map_cache.head.nmaps,map_cache.fp);
			return 1;
		}
		fclose(map_cache.fp);
	}
	// 読み込みに失敗したので新規に作成する
	map_cache.fp = fopen(fn,"wb");
	if(map_cache.fp) {
		memset(&map_cache.head,0,sizeof(struct map_cache_head));
		map_cache.map   = aCalloc(sizeof(struct map_cache_info),MAX_MAP_CACHE);
		map_cache.head.nmaps         = MAX_MAP_CACHE;
		map_cache.head.sizeof_header = sizeof(struct map_cache_head);
		map_cache.head.sizeof_map    = sizeof(struct map_cache_info);

		map_cache.head.filesize  = sizeof(struct map_cache_head);
		map_cache.head.filesize += sizeof(struct map_cache_info) * map_cache.head.nmaps;

		map_cache.dirty = 1;
		return 1;
	}
	return 0;
}

static void map_cache_close(void)
{
	if(!map_cache.fp) { return; }
	if(map_cache.dirty) {
		fseek(map_cache.fp,0,SEEK_SET);
		fwrite(&map_cache.head,1,sizeof(struct map_cache_head),map_cache.fp);
		fwrite(map_cache.map,map_cache.head.nmaps,sizeof(struct map_cache_info),map_cache.fp);
	}
	fclose(map_cache.fp);
	free(map_cache.map);
	map_cache.fp = NULL;
	return;
}

int map_cache_read(struct map_data *m)
{
	int i;
	if(!map_cache.fp) { return 0; }
	for(i = 0;i < map_cache.head.nmaps ; i++) {
		if(!strcmp(m->name,map_cache.map[i].fn)) {
			if(map_cache.map[i].water_height != map_waterheight(m->name)) {
				// 水場の高さが違うので読み直し
				return 0;
			} else if(map_cache.map[i].compressed == 0) {
				// 非圧縮ファイル
				int size = map_cache.map[i].xs * map_cache.map[i].ys;
				m->xs = map_cache.map[i].xs;
				m->ys = map_cache.map[i].ys;
				m->gat = (unsigned char *)aCalloc(m->xs * m->ys,sizeof(unsigned char));
				fseek(map_cache.fp,map_cache.map[i].pos,SEEK_SET);
				if(fread(m->gat,1,size,map_cache.fp) == size) {
					// 成功
					return 1;
				} else {
					// なぜかファイル後半が欠けてるので読み直し
					m->xs = 0; m->ys = 0; m->gat = NULL; free(m->gat);
					return 0;
				}
			} else if(map_cache.map[i].compressed == 1) {
				// 圧縮フラグ=1 : zlib
				unsigned char *buf;
				unsigned long dest_len;
				int size_compress = map_cache.map[i].compressed_len;
				m->xs = map_cache.map[i].xs;
				m->ys = map_cache.map[i].ys;
				m->gat = (unsigned char *)aMalloc(m->xs * m->ys * sizeof(unsigned char));
				buf = (unsigned char*)aMalloc(size_compress);
				fseek(map_cache.fp,map_cache.map[i].pos,SEEK_SET);
				if(fread(buf,1,size_compress,map_cache.fp) != size_compress) {
					// なぜかファイル後半が欠けてるので読み直し
					printf("fread error\n");
					m->xs = 0; m->ys = 0; m->gat = NULL;
					free(m->gat); free(buf);
					return 0;
				}
				dest_len = m->xs * m->ys;
				decode_zip(m->gat,&dest_len,buf,size_compress);
				if(dest_len != map_cache.map[i].xs * map_cache.map[i].ys) {
					// 正常に解凍が出来てない
					m->xs = 0; m->ys = 0; m->gat = NULL;
					free(m->gat); free(buf);
					return 0;
				}
				free(buf);
				return 1;
			}
		}
	}
	return 0;
}

static int map_cache_write(struct map_data *m)
{
	int i;
	unsigned long len_new , len_old;
	char *write_buf;
	if(!map_cache.fp) { return 0; }
	for(i = 0;i < map_cache.head.nmaps ; i++) {
		if(!strcmp(m->name,map_cache.map[i].fn)) {
			// 同じエントリーがあれば上書き
			if(map_cache.map[i].compressed == 0) {
				len_old = map_cache.map[i].xs * map_cache.map[i].ys;
			} else if(map_cache.map[i].compressed == 1) {
				len_old = map_cache.map[i].compressed_len;
			} else {
				// サポートされてない形式なので長さ０
				len_old = 0;
			}
			if(map_read_flag == 2) {
				// 圧縮保存
				// さすがに２倍に膨れる事はないという事で
				write_buf = aMalloc(m->xs * m->ys * 2);
				len_new = m->xs * m->ys * 2;
				encode_zip(write_buf,&len_new,m->gat,m->xs * m->ys);
				map_cache.map[i].compressed     = 1;
				map_cache.map[i].compressed_len = len_new;
			} else {
				len_new = m->xs * m->ys;
				write_buf = m->gat;
				map_cache.map[i].compressed     = 0;
				map_cache.map[i].compressed_len = 0;	
			}
			if(len_new <= len_old) {
				// サイズが同じか小さくなったので場所は変わらない
				fseek(map_cache.fp,map_cache.map[i].pos,SEEK_SET);
				fwrite(write_buf,1,len_new,map_cache.fp);
			} else {
				// 新しい場所に登録
				fseek(map_cache.fp,map_cache.head.filesize,SEEK_SET);
				fwrite(write_buf,1,len_new,map_cache.fp);
				map_cache.map[i].pos = map_cache.head.filesize;
				map_cache.head.filesize += len_new;
			}
			map_cache.map[i].xs  = m->xs;
			map_cache.map[i].ys  = m->ys;
			map_cache.map[i].water_height = map_waterheight(m->name);
			map_cache.dirty = 1;
			if(map_read_flag == 2) {
				free(write_buf);
			}
			return 0;
		}
	}
	// 同じエントリが無ければ書き込める場所を探す
	for(i = 0;i < map_cache.head.nmaps ; i++) {
		if(map_cache.map[i].fn[0] == 0) {
			// 新しい場所に登録
			if(map_read_flag == 2) {
				write_buf = aMalloc(m->xs * m->ys * 2);
				len_new = m->xs * m->ys * 2;
				encode_zip(write_buf,&len_new,m->gat,m->xs * m->ys);
				map_cache.map[i].compressed     = 1;
				map_cache.map[i].compressed_len = len_new;
			} else {
				len_new = m->xs * m->ys;
				write_buf = m->gat;
				map_cache.map[i].compressed     = 0;
				map_cache.map[i].compressed_len = 0;
			}
			strncpy(map_cache.map[i].fn,m->name,sizeof(map_cache.map[0].fn));
			fseek(map_cache.fp,map_cache.head.filesize,SEEK_SET);
			fwrite(write_buf,1,len_new,map_cache.fp);
			map_cache.map[i].pos = map_cache.head.filesize;
			map_cache.map[i].xs  = m->xs;
			map_cache.map[i].ys  = m->ys;
			map_cache.map[i].water_height = map_waterheight(m->name);
			map_cache.head.filesize += len_new;
			map_cache.dirty = 1;
			if(map_read_flag == 2) {
				free(write_buf);
			}
			return 0;
		}
	}
	// 書き込めなかった
	return 1;
}

/*==========================================
 * マップ1枚読み込み
 * ===================================================*/
static int map_readmap(int m,char *fn,int *map_cache)
{
	unsigned char *gat;
	size_t size;

	printf("\rmap reading %s[%d/%d] %-20s  ",(map_read_flag ? "with cache " : ""),m,map_num,fn);
	fflush(stdout);

	if(map_cache_read(&map[m])) {
		// キャッシュから読み込めた
		(*map_cache)++;
	} else {
		int s;
		int wh;
		int x,y,xs,ys;
		struct gat_1cell {float high[4]; int type;} *p;
		// read & convert fn
		gat=grfio_read(fn);
		if(gat==NULL) {
			// さすがにマップが読めないのはまずいので終了する
			printf("Can't load map %s\n",fn);
			exit(1);
		}

		xs=map[m].xs=*(int*)(gat+6);
		ys=map[m].ys=*(int*)(gat+10);
		map[m].gat = (unsigned char *)aCalloc(s = map[m].xs * map[m].ys,sizeof(unsigned char));
		wh=map_waterheight(map[m].name);
		for(y=0;y<ys;y++){
			p=(struct gat_1cell*)(gat+y*xs*20+14);
			for(x=0;x<xs;x++){
				if(wh!=NO_WATER && p->type==0){
					// 水場判定
					map[m].gat[x+y*xs]=(p->high[0]>wh || p->high[1]>wh || p->high[2]>wh || p->high[3]>wh) ? 3 : 0;
				} else {
					map[m].gat[x+y*xs]=p->type;
				}
				p++;
			}
		}
		map_cache_write(&map[m]);
		free(gat);
	}
	map[m].m=m;
	map[m].npc_num=0;
	map[m].users=0;
	memset(&map[m].flag,0,sizeof(map[m].flag));
	map[m].bxs=(map[m].xs+BLOCK_SIZE-1)/BLOCK_SIZE;
	map[m].bys=(map[m].ys+BLOCK_SIZE-1)/BLOCK_SIZE;
	size = map[m].bxs * map[m].bys * sizeof(struct block_list*);
	map[m].block = (struct block_list **)aCalloc(1,size);
	map[m].block_mob = (struct block_list **)aCalloc(1,size);
	size = map[m].bxs*map[m].bys*sizeof(int);
	map[m].block_count = (int *)aCalloc(1,size);
	map[m].block_mob_count=(int *)aCalloc(1,size);
	strdb_insert(map_db,map[m].name,&map[m]);

//	printf("%s read done\n",fn);

	return 0;
}

/*==========================================
 * 全てのmapデータを読み込む
 *------------------------------------------
 */
int map_readallmap(void)
{
	int i;
	char fn[256];
	FILE *fp=NULL;
	int map_cache = 0;

	// マップキャッシュを開く
	if(map_read_flag) {
		map_cache_open(map_cache_file);
	}

	/*
	// 先に全部のマップの存在を確認
	for(i=0;i<map_num;i++){
		if(strstr(map[i].name,".gat")==NULL)
			continue;
		sprintf(fn,"data\\%s",map[i].name);
		grfio_size(fn);
	}*/
	for(i=0;i<map_num;i++){
		if(strstr(map[i].name,".gat")==NULL)
			continue;
		sprintf(fn,"data\\%s",map[i].name);
		map_readmap(i,fn,&map_cache);
	}

	free(waterlist);
	printf("\rmap read done. (%d map , %d map(s) in cache) %24s\n",map_num,map_cache,"");

	// マップキャッシュを閉じる
	map_cache_close();

	if(fp!=NULL)	fclose(fp);

	return 0;
}
/*==========================================
 * 読み込むmapを追加する
 *------------------------------------------
 */
int map_addmap(char *mapname)
{
	if( strcmpi(mapname,"clear")==0 ){
		map_num=0;
		return 0;
	}

	if(map_num>=MAX_MAP_PER_SERVER-1){
		printf("too many map\n");
		return 1;
	}
	memcpy(map[map_num].name,mapname,24);
	map_num++;
	return 0;
}
/*==========================================
 * 読み込むmapを削除する
 *------------------------------------------
 */
int map_delmap(char *mapname)
{
	int i;

	if( strcmpi(mapname,"all")==0 ){
		map_num=0;
		return 0;
	}

	for(i=0;i<map_num;i++){
		if(strcmp(map[i].name,mapname)==0){
			memmove(map+i,map+i+1,sizeof(map[0])*(map_num-i-1));
			map_num--;
		}
	}
	return 0;
}
/*==========================================
 * @whoのDB版
 *------------------------------------------
 */
 int map_who_sub(void *key,void *data,va_list ap)
{
	struct charid2nick *p;
	int fd;

	nullpo_retr(-1, ap);
	nullpo_retr(-1, data);
	nullpo_retr(-1, p=(struct charid2nick *)data);

//printf("who: %s %d %d\n",p->nick,(int)p->ip,p->port);

	fd=va_arg(ap,int);

	if( p->ip != 0 && 
		p->port != 0 &&
		!(battle_config.hide_GM_session && pc_numisGM(p->account_id))
	)
		clif_displaymessage(fd, p->nick);

	return 0;
}
int map_who(int fd){
	numdb_foreach( charid_db, map_who_sub, fd );
	return 0;
}
/*==========================================
 * 設定ファイルを読み込む
 *------------------------------------------
 */
int map_config_read(char *cfgName)
{
	int i;
	char line[1024],w1[1024],w2[1024];
	FILE *fp;
	struct hostent *h=NULL;

	fp=fopen(cfgName,"r");
	if(fp==NULL){
		printf("file not found: %s\n",cfgName);
		return 1;
	}
	while(fgets(line,1020,fp)){
		if(line[0] == '/' && line[1] == '/')
			continue;
		i=sscanf(line,"%[^:]: %[^\r\n]",w1,w2);
		if(i!=2)
			continue;
		if(strcmpi(w1,"userid")==0){
			chrif_setuserid(w2);
		} else if(strcmpi(w1,"passwd")==0){
			chrif_setpasswd(w2);
		} else if(strcmpi(w1,"char_ip")==0){
			h = gethostbyname (w2);
			if(h != NULL) {
				printf("Character sever IP address : %s -> %d.%d.%d.%d\n",w2,(unsigned char)h->h_addr[0],(unsigned char)h->h_addr[1],(unsigned char)h->h_addr[2],(unsigned char)h->h_addr[3]);
				sprintf(w2,"%d.%d.%d.%d",(unsigned char)h->h_addr[0],(unsigned char)h->h_addr[1],(unsigned char)h->h_addr[2],(unsigned char)h->h_addr[3]);
			}
			chrif_setip(w2);
		} else if(strcmpi(w1,"char_port")==0){
			chrif_setport(atoi(w2));
		} else if(strcmpi(w1,"map_ip")==0){
			h = gethostbyname (w2);
			if(h != NULL) {
				printf("Map server IP address : %s -> %d.%d.%d.%d\n",w2,(unsigned char)h->h_addr[0],(unsigned char)h->h_addr[1],(unsigned char)h->h_addr[2],(unsigned char)h->h_addr[3]);
				sprintf(w2,"%d.%d.%d.%d",(unsigned char)h->h_addr[0],(unsigned char)h->h_addr[1],(unsigned char)h->h_addr[2],(unsigned char)h->h_addr[3]);
			}
			clif_setip(w2);
		} else if(strcmpi(w1,"map_port")==0){
			clif_setport(atoi(w2));
		} else if(strcmpi(w1,"water_height")==0){
			map_readwater(w2);
		} else if(strcmpi(w1,"gm_account_filename")==0){
			pc_set_gm_account_fname(w2);
		} else if(strcmpi(w1,"map")==0){
			map_addmap(w2);
		} else if(strcmpi(w1,"delmap")==0){
			map_delmap(w2);
		} else if(strcmpi(w1,"npc")==0){
			npc_addsrcfile(w2);
		} else if(strcmpi(w1,"delnpc")==0){
			npc_delsrcfile(w2);
		} else if(strcmpi(w1,"data_grf")==0){
			grfio_setdatafile(w2);
		} else if(strcmpi(w1,"sdata_grf")==0){
			grfio_setsdatafile(w2);
		} else if(strcmpi(w1,"adata_grf")==0){
			grfio_setadatafile(w2);
		} else if(strcmpi(w1,"packet_parse_time")==0){
			packet_parse_time=atoi(w2);
			if(packet_parse_time <= 0)
				packet_parse_time = 0;
		} else if(strcmpi(w1,"autosave_time")==0){
			autosave_interval=atoi(w2)*1000;
			if(autosave_interval <= 0)
				autosave_interval = DEFAULT_AUTOSAVE_INTERVAL;
		} else if(strcmpi(w1,"motd_txt")==0){
			strncpy(motd_txt,w2,256);
		} else if(strcmpi(w1,"help_txt")==0){
			strncpy(help_txt,w2,256);
		} else if(strcmpi(w1,"mapreg_txt")==0){
			strncpy(mapreg_txt,w2,256);
		}else if(strcmpi(w1,"read_map_from_cache")==0){
			map_read_flag=atoi(w2);
		}else if(strcmpi(w1,"map_cache_file")==0){
			strncpy(map_cache_file,w2,255);
		}else if(strcmpi(w1,"import")==0){
			map_config_read(w2);
		}
	}
	fclose(fp);

	return 0;
}

/*==========================================
 * map鯖終了時処理
 *------------------------------------------
 */
static int id_db_final(void *key,void *data,va_list ap)
{
	return 0;
}
static int map_db_final(void *key,void *data,va_list ap)
{
//	char *name;

//	nullpo_retr(0, name=data);

//	free(name);

	return 0;
}
static int nick_db_final(void *key,void *data,va_list ap)
{
	char *nick;

	nullpo_retr(0, nick=data);

	free(nick);

	return 0;
}
static int charid_db_final(void *key,void *data,va_list ap)
{
	struct charid2nick *p;

	nullpo_retr(0, p=data);

	free(p);

	return 0;
}
void do_final(void)
{
	int i;

	chrif_mapactive(0); //マップサーバー停止中

	do_final_npc();
	do_final_script();
	do_final_itemdb();
	do_final_storage();
	do_final_guild();
	do_final_clif();
	do_final_chrif();
	do_final_pc();
	do_final_party();

	for(i=0;i<=map_num;i++){
		if(map[i].gat) {	
			free(map[i].gat);
			map[i].gat=NULL;
		}
		if(map[i].block) free(map[i].block);
		if(map[i].block_mob) free(map[i].block_mob);
		if(map[i].block_count) free(map[i].block_count);
		if(map[i].block_mob_count) free(map[i].block_mob_count);
	}

	if(map_db)
		strdb_final(map_db,map_db_final);
	if(nick_db)
		strdb_final(nick_db,nick_db_final);
	if(charid_db)
		numdb_final(charid_db,charid_db_final);
	if(id_db)
		numdb_final(id_db,id_db_final);
	exit_dbn();

	do_final_timer();
}

/*==========================================
 * map鯖初期化の大元
 *------------------------------------------
 */
int do_init(int argc,char *argv[])
{
	srand(gettick());

	if(map_config_read((argc<2)? MAP_CONF_NAME:argv[1]))
		exit(1);
	battle_config_read((argc>2)? argv[2]:BATTLE_CONF_FILENAME);
	atcommand_config_read((argc>3)? argv[3]:ATCOMMAND_CONF_FILENAME);
	script_config_read((argc>4)? argv[4]:SCRIPT_CONF_NAME);
	msg_config_read((argc>5)? argv[5]:MSG_CONF_NAME);

	atexit(do_final);

	id_db = numdb_init();
	map_db = strdb_init(16);
	nick_db = strdb_init(24);
	charid_db = numdb_init();

	grfio_init((argc>6)? argv[6]:GRF_PATH_FILENAME);
	map_readallmap();

	add_timer_func_list(map_clearflooritem_timer,"map_clearflooritem_timer");

	do_init_chrif();
	do_init_clif();
	do_init_itemdb();
	do_init_mob();	// npcの初期化時内でmob_spawnして、mob_dbを参照するのでinit_npcより先
	do_init_script();
	do_init_npc();
	do_init_pc();
	do_init_party();
	do_init_guild();
	do_init_storage();
	do_init_skill();
	do_init_pet();
	do_init_status();
	npc_event_do_oninit();	// npcのOnInitイベント実行

	return 0;
}
