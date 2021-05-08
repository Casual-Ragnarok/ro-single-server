// $Id: char.c,v 1.4 2004/09/15 00:25:29 running_pinata Exp $
// original : char2.c 2003/03/14 11:58:35 Rev.1.5
#define DUMP_UNKNOWN_PACKET	1

#include <sys/types.h>
#ifndef _WIN32
	#include <sys/socket.h>
	#include <sys/ioctl.h>
	#include <unistd.h>
	#include <signal.h>
	#include <fcntl.h>
	#include <arpa/inet.h>
	#include <netdb.h>
	#include <netinet/in.h>
	#include <sys/time.h>
#endif
#include <stdio.h>
#include <stdlib.h>
#include <string.h>

#include "core.h"
#include "socket.h"
#include "timer.h"
#include "db.h"
#include "mmo.h"
#include "version.h"
#include "lock.h"
#include "nullpo.h"
#include "malloc.h"
#include "char.h"

#include "inter.h"
#include "int_pet.h"
#include "int_guild.h"
#include "int_party.h"
#include "int_storage.h"

#ifdef MEMWATCH
#include "memwatch.h"
#endif

struct mmo_map_server server[MAX_MAP_SERVERS];
int server_fd[MAX_MAP_SERVERS];

int login_fd,char_fd;
char userid[24];
char passwd[24];
char server_name[20];
char login_ip_str[16];
int login_ip;
int login_port = 6900;
char char_ip_str[16];
int char_ip;
int char_port = 6121;
int char_maintenance;
int char_new;
char char_txt[1024];
char unknown_char_name[1024]="Unknown";
char char_log_filename[1024]="log/char.log";
char GM_account_filename[1024] = "conf/GM_account.txt";
char default_map_name[16]="prontera.gat";
int default_map_type=0;

#define CHAR_STATE_WAITAUTH 0
#define CHAR_STATE_AUTHOK 1
struct char_session_data{
  int state;
  int account_id,login_id1,login_id2,sex;
  int found_char[9];
};

#define AUTH_FIFO_SIZE 256
struct {
  int account_id,char_id,login_id1,login_id2,ip,tick,char_pos,delflag,sex;
} auth_fifo[AUTH_FIFO_SIZE];
int auth_fifo_pos=0;

int char_id_count=150000;
struct mmo_charstatus *char_dat;
int char_num,char_max;
int max_connect_user=0;
int autosave_interval=DEFAULT_AUTOSAVE_INTERVAL;
int start_zeny = 500;

// 初期位置（confファイルから再設定可能） 
struct point start_point={"new_1-1.gat",53,111};

static struct dbt *gm_account_db;
int isGM(int account_id)
{
	struct gm_account *p;
	p = numdb_search(gm_account_db,account_id);
	if( p == NULL)
		return 0;
	return p->level;
}
int read_gm_account()
{
	char line[8192];
	struct gm_account *p;
	FILE *fp;
	int c=0;

	gm_account_db = numdb_init();

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
//	printf("gm_account: %s read done (%d gm account ID)\n",gm_account_txt,c);
	return 0;
}

int mmo_char_tostr(char *str,struct mmo_charstatus *p)
{
	int i;
	char *str_p = str;
	
	nullpo_retr(-1,p);
	
	str_p += sprintf(str_p,"%d\t%d,%d\t%s\t%d,%d,%d\t%d,%d,%d\t%d,%d,%d,%d\t%d,%d,%d,%d,%d,%d\t%d,%d"
		"\t%d,%d,%d\t%d,%d,%d\t%d,%d,%d\t%d,%d,%d,%d,%d"
		"\t%s,%d,%d\t%s,%d,%d,%d\t",
		p->char_id,p->account_id,p->char_num,p->name, //
		p->class,p->base_level,p->job_level,
		p->base_exp,p->job_exp,p->zeny,
		p->hp,p->max_hp,p->sp,p->max_sp,
		p->str,p->agi,p->vit,p->int_,p->dex,p->luk,
		p->status_point,p->skill_point,
		p->option,p->karma,p->manner,	//
		p->party_id,p->guild_id,p->pet_id,
		p->hair,p->hair_color,p->clothes_color,
		p->weapon,p->shield,p->head_top,p->head_mid,p->head_bottom,
		p->last_point.map,p->last_point.x,p->last_point.y, //
		p->save_point.map,p->save_point.x,p->save_point.y,
		p->partner_id
	);
	for(i=0;i<10;i++)
		if(p->memo_point[i].map[0])
			str_p += sprintf(str_p,"%s,%d,%d",p->memo_point[i].map,p->memo_point[i].x,p->memo_point[i].y);
	
	*(str_p++)='\t';
	
	for(i=0;i<MAX_INVENTORY;i++)
		if(p->inventory[i].nameid){
			str_p += sprintf(str_p,"%d,%d,%d,%d,%d,%d,%d,%d,%d,%d,%d ",
			p->inventory[i].id,p->inventory[i].nameid,p->inventory[i].amount,p->inventory[i].equip,
			p->inventory[i].identify,p->inventory[i].refine,p->inventory[i].attribute,
			p->inventory[i].card[0],p->inventory[i].card[1],p->inventory[i].card[2],p->inventory[i].card[3]);
		}
	*(str_p++)='\t';
	
	for(i=0;i<MAX_CART;i++)
		if(p->cart[i].nameid){
			str_p += sprintf(str_p,"%d,%d,%d,%d,%d,%d,%d,%d,%d,%d,%d ",
			p->cart[i].id,p->cart[i].nameid,p->cart[i].amount,p->cart[i].equip,
			p->cart[i].identify,p->cart[i].refine,p->cart[i].attribute,
			p->cart[i].card[0],p->cart[i].card[1],p->cart[i].card[2],p->cart[i].card[3]);
		}
	*(str_p++)='\t';
	
	for(i=0;i<MAX_SKILL;i++)
		if(p->skill[i].id && p->skill[i].flag!=1)
			str_p += sprintf(str_p,"%d,%d ",p->skill[i].id,(p->skill[i].flag==0)?p->skill[i].lv:p->skill[i].flag-2);
	*(str_p++)='\t';
	
	for(i=0;i<p->global_reg_num;i++)
		str_p += sprintf(str_p,"%s,%d ",p->global_reg[i].str,p->global_reg[i].value);
	*(str_p++)='\t';
	*str_p='\0';
	return 0;
}

int mmo_char_fromstr(char *str,struct mmo_charstatus *p)
{
	int tmp_int[256];
	int set,next,len,i;
	
	nullpo_retr(0,p);
	
	// 1008以降の形式読み込み
	if( (set=sscanf(str,"%d\t%d,%d\t%[^\t]\t%d,%d,%d\t%d,%d,%d\t%d,%d,%d,%d\t%d,%d,%d,%d,%d,%d\t%d,%d"
		"\t%d,%d,%d\t%d,%d,%d\t%d,%d,%d\t%d,%d,%d,%d,%d"
		"\t%[^,],%d,%d\t%[^,],%d,%d,%d%n",
		&tmp_int[0],&tmp_int[1],&tmp_int[2],p->name, //
		&tmp_int[3],&tmp_int[4],&tmp_int[5],
		&tmp_int[6],&tmp_int[7],&tmp_int[8],
		&tmp_int[9],&tmp_int[10],&tmp_int[11],&tmp_int[12],
		&tmp_int[13],&tmp_int[14],&tmp_int[15],&tmp_int[16],&tmp_int[17],&tmp_int[18],
		&tmp_int[19],&tmp_int[20],
		&tmp_int[21],&tmp_int[22],&tmp_int[23], //
		&tmp_int[24],&tmp_int[25],&tmp_int[26],
		&tmp_int[27],&tmp_int[28],&tmp_int[29],
		&tmp_int[30],&tmp_int[31],&tmp_int[32],&tmp_int[33],&tmp_int[34],
		p->last_point.map,&tmp_int[35],&tmp_int[36], //
		p->save_point.map,&tmp_int[37],&tmp_int[38],&tmp_int[39],&next
		)
	)!=43 ){
		// 384以降1008以前の形式読み込み
		tmp_int[39]=0;
		if( (set=sscanf(str,"%d\t%d,%d\t%[^\t]\t%d,%d,%d\t%d,%d,%d\t%d,%d,%d,%d\t%d,%d,%d,%d,%d,%d\t%d,%d"
			"\t%d,%d,%d\t%d,%d,%d\t%d,%d,%d\t%d,%d,%d,%d,%d"
			"\t%[^,],%d,%d\t%[^,],%d,%d%n",
			&tmp_int[0],&tmp_int[1],&tmp_int[2],p->name, //
			&tmp_int[3],&tmp_int[4],&tmp_int[5],
			&tmp_int[6],&tmp_int[7],&tmp_int[8],
			&tmp_int[9],&tmp_int[10],&tmp_int[11],&tmp_int[12],
			&tmp_int[13],&tmp_int[14],&tmp_int[15],&tmp_int[16],&tmp_int[17],&tmp_int[18],
			&tmp_int[19],&tmp_int[20],
			&tmp_int[21],&tmp_int[22],&tmp_int[23], //
			&tmp_int[24],&tmp_int[25],&tmp_int[26],
			&tmp_int[27],&tmp_int[28],&tmp_int[29],
			&tmp_int[30],&tmp_int[31],&tmp_int[32],&tmp_int[33],&tmp_int[34],
			p->last_point.map,&tmp_int[35],&tmp_int[36], //
			p->save_point.map,&tmp_int[37],&tmp_int[38],&next
			)
		)!=42 ){
			// 384以前の形式の読み込み
			tmp_int[26]=0;
			set=sscanf(str,"%d\t%d,%d\t%[^\t]\t%d,%d,%d\t%d,%d,%d\t%d,%d,%d,%d\t%d,%d,%d,%d,%d,%d\t%d,%d"
				"\t%d,%d,%d\t%d,%d\t%d,%d,%d\t%d,%d,%d,%d,%d"
				"\t%[^,],%d,%d\t%[^,],%d,%d%n",
				&tmp_int[0],&tmp_int[1],&tmp_int[2],p->name, //
				&tmp_int[3],&tmp_int[4],&tmp_int[5],
				&tmp_int[6],&tmp_int[7],&tmp_int[8],
				&tmp_int[9],&tmp_int[10],&tmp_int[11],&tmp_int[12],
				&tmp_int[13],&tmp_int[14],&tmp_int[15],&tmp_int[16],&tmp_int[17],&tmp_int[18],
				&tmp_int[19],&tmp_int[20],
				&tmp_int[21],&tmp_int[22],&tmp_int[23], //
				&tmp_int[24],&tmp_int[25],//
				&tmp_int[27],&tmp_int[28],&tmp_int[29],
				&tmp_int[30],&tmp_int[31],&tmp_int[32],&tmp_int[33],&tmp_int[34],
				p->last_point.map,&tmp_int[35],&tmp_int[36], //
				p->save_point.map,&tmp_int[37],&tmp_int[38],&next
				);
			set+=2;
			printf("char: old char data ver.1\n");
		}else{// 383~1008Verでの読み込みに成功しているならsetを正常値へ
			set++;
			printf("char: old char data ver.2\n");
		}
	}
	p->char_id=tmp_int[0];
	p->account_id=tmp_int[1];
	p->char_num=tmp_int[2];
	p->class=tmp_int[3];
	p->base_level=tmp_int[4];
	p->job_level=tmp_int[5];
	p->base_exp=tmp_int[6];
	p->job_exp=tmp_int[7];
	p->zeny=tmp_int[8];
	p->hp=tmp_int[9];
	p->max_hp=tmp_int[10];
	p->sp=tmp_int[11];
	p->max_sp=tmp_int[12];
	p->str=tmp_int[13];
	p->agi=tmp_int[14];
	p->vit=tmp_int[15];
	p->int_=tmp_int[16];
	p->dex=tmp_int[17];
	p->luk=tmp_int[18];
	p->status_point=tmp_int[19];
	p->skill_point=tmp_int[20];
	p->option=tmp_int[21];
	p->karma=tmp_int[22];
	p->manner=tmp_int[23];
	p->party_id=tmp_int[24];
	p->guild_id=tmp_int[25];
	p->pet_id=tmp_int[26];
	p->hair=tmp_int[27];
	p->hair_color=tmp_int[28];
	p->clothes_color=tmp_int[29];
	p->weapon=tmp_int[30];
	p->shield=tmp_int[31];
	p->head_top=tmp_int[32];
	p->head_mid=tmp_int[33];
	p->head_bottom=tmp_int[34];
	p->last_point.x=tmp_int[35];
	p->last_point.y=tmp_int[36];
	p->save_point.x=tmp_int[37];
	p->save_point.y=tmp_int[38];
	p->partner_id=tmp_int[39];
	if(set!=43)
		return 0;
	if(str[next]=='\n' || str[next]=='\r')
		return 1;	// 新規データ
	next++;
	for(i=0;str[next] && str[next]!='\t';i++){
		set=sscanf(str+next,"%[^,],%d,%d%n",p->memo_point[i].map,&tmp_int[0],&tmp_int[1],&len);
		if(set!=3)
	    	return 0;
		p->memo_point[i].x=tmp_int[0];
		p->memo_point[i].y=tmp_int[1];
		next+=len;
		if(str[next]==' ')
			next++;
	}
	next++;
	for(i=0;str[next] && str[next]!='\t';i++){
		set=sscanf(str+next,"%d,%d,%d,%d,%d,%d,%d,%d,%d,%d,%d%n",
		&tmp_int[0],&tmp_int[1],&tmp_int[2],&tmp_int[3],
		&tmp_int[4],&tmp_int[5],&tmp_int[6],
		&tmp_int[7],&tmp_int[8],&tmp_int[9],&tmp_int[10],&len);
		if(set!=11)
			return 0;
		p->inventory[i].id=tmp_int[0];
		p->inventory[i].nameid=tmp_int[1];
		p->inventory[i].amount=tmp_int[2];
		p->inventory[i].equip=tmp_int[3];
		p->inventory[i].identify=tmp_int[4];
		p->inventory[i].refine=tmp_int[5];
		p->inventory[i].attribute=tmp_int[6];
		p->inventory[i].card[0]=tmp_int[7];
		p->inventory[i].card[1]=tmp_int[8];
		p->inventory[i].card[2]=tmp_int[9];
		p->inventory[i].card[3]=tmp_int[10];
		next+=len;
		if(str[next]==' ')
			next++;
	}
	next++;
	for(i=0;str[next] && str[next]!='\t';i++){
		set=sscanf(str+next,"%d,%d,%d,%d,%d,%d,%d,%d,%d,%d,%d%n",
		&tmp_int[0],&tmp_int[1],&tmp_int[2],&tmp_int[3],
		&tmp_int[4],&tmp_int[5],&tmp_int[6],
		&tmp_int[7],&tmp_int[8],&tmp_int[9],&tmp_int[10],&len);
		if(set!=11)
			return 0;
		p->cart[i].id=tmp_int[0];
		p->cart[i].nameid=tmp_int[1];
		p->cart[i].amount=tmp_int[2];
		p->cart[i].equip=tmp_int[3];
		p->cart[i].identify=tmp_int[4];
		p->cart[i].refine=tmp_int[5];
		p->cart[i].attribute=tmp_int[6];
		p->cart[i].card[0]=tmp_int[7];
		p->cart[i].card[1]=tmp_int[8];
		p->cart[i].card[2]=tmp_int[9];
		p->cart[i].card[3]=tmp_int[10];
		next+=len;
		if(str[next]==' ')
			next++;
	}
	next++;
	for(i=0;str[next] && str[next]!='\t';i++){
		set=sscanf(str+next,"%d,%d%n",
		&tmp_int[0],&tmp_int[1],&len);
		if(set!=2)
			return 0;
		p->skill[tmp_int[0]].id=tmp_int[0];
		p->skill[tmp_int[0]].lv=tmp_int[1];
		next+=len;
		if(str[next]==' ')
			next++;
	}
	next++;
	for(i=0;str[next] && str[next]!='\t' && str[next]!='\n' && str[next]!='\r';i++){ //global_reg実装以前のathena.txt互換のため一応'\n'チェック
		set=sscanf(str+next,"%[^,],%d%n",
		p->global_reg[i].str,&p->global_reg[i].value,&len);
		if(set!=2)
			return 0;
		next+=len;
		if(str[next]==' ')
			next++;
	}
	p->global_reg_num=i;
	p->mapip=0;
	p->mapport=0;
	
	return 1;
}

int mmo_char_init(void)
{
	char line[65536];
	int ret;
	FILE *fp;
	
	fp=fopen(char_txt,"r");
	char_dat=(struct mmo_charstatus *)aCalloc(256,sizeof(char_dat[0]));
	char_max=256;
	if(fp==NULL)
		return 0;
	while(fgets(line,65535,fp)){
		int i,j = -1;
		if( sscanf(line,"%d\t%%newid%%%n",&i,&j)==1 && j > 0 && (line[j]=='\n' || line[j]=='\r') ){
			if(char_id_count<i)
				char_id_count=i;
			continue;
		}
	
		if(char_num>=char_max){
			char_max+=256;
			char_dat=(struct mmo_charstatus *)aRealloc(char_dat,sizeof(char_dat[0])*char_max);
			memset(char_dat + (char_max - 256), '\0', 256 * sizeof(*char_dat));
    	}
		memset(&char_dat[char_num],0,sizeof(char_dat[0]));
		ret=mmo_char_fromstr(line,&char_dat[char_num]);
		if(ret){
			if(char_dat[char_num].char_id>=char_id_count)
				char_id_count=char_dat[char_num].char_id+1;
			char_num++;
		}
	}
	fclose(fp);
	return 0;
}

void mmo_char_sync(void)
{
	char line[65536];
	int i,lock;
	FILE *fp;
	
	fp=lock_fopen(char_txt,&lock);
	if(fp==NULL)
		return;
	for(i=0;i<char_num;i++){
		mmo_char_tostr(line,&char_dat[i]);
		fprintf(fp,"%s" RETCODE,line);
	}
	fprintf(fp,"%d\t%%newid%%" RETCODE,char_id_count);
	lock_fclose(fp,char_txt,&lock);
}
int mmo_char_sync_timer(int tid,unsigned int tick,int id,int data)
{
	mmo_char_sync();
	inter_save();
	return 0;
}

int make_new_char(int fd,unsigned char *dat)
{
	int i;
	struct char_session_data *sd;
	FILE *logfp;
	
	for(i=0;i<24 && dat[i];i++){
		if(dat[i]<0x20 || dat[i]==0x7f)
			return -1;
	}
	
	if(dat[24]+dat[25]+dat[26]+dat[27]+dat[28]+dat[29]>5*6 ||
		 dat[30]>=9 ||
		 dat[33]==0 || dat[33]>=20 ||
		 dat[31]>=9){
		logfp=fopen(char_log_filename,"a");
		if(logfp){
			fprintf(logfp,"make new char error %d-%d %s %d,%d,%d,%d,%d,%d %d,%d" RETCODE,
				fd,dat[30],dat,dat[24],dat[25],dat[26],dat[27],dat[28],dat[29],dat[33],dat[31]);
			fclose(logfp);
		}
		return -1;
	}
	logfp=fopen(char_log_filename,"a");
	if(logfp){
		fprintf(logfp,"make new char %d-%d %s" RETCODE,fd,dat[30],dat);
		fclose(logfp);
	}
	sd=session[fd]->session_data;
	
	for(i=0;i<char_num;i++){
		if(strcmp(char_dat[i].name,dat)==0 || (char_dat[i].account_id==sd->account_id && char_dat[i].char_num==dat[30]))
			break;
	}
	if(i!=char_num)
		return -1;
	if(char_num>=char_max){
		char_max+=256;
		char_dat=(struct mmo_charstatus *)aRealloc(char_dat,sizeof(char_dat[0])*char_max);
		memset(char_dat + (char_max - 256), '\0', 256 * sizeof(*char_dat));
	}
	memset(&char_dat[i],0,sizeof(char_dat[0]));
	
	char_dat[i].char_id=char_id_count++;
	char_dat[i].account_id=sd->account_id;
	char_dat[i].char_num=dat[30];
	strncpy(char_dat[i].name,dat,24);
	char_dat[i].class=0;
	char_dat[i].base_level=1;
	char_dat[i].job_level=1;
	char_dat[i].base_exp=0;
	char_dat[i].job_exp=0;
	char_dat[i].zeny=start_zeny;
	char_dat[i].str=dat[24];
	char_dat[i].agi=dat[25];
	char_dat[i].vit=dat[26];
	char_dat[i].int_=dat[27];
	char_dat[i].dex=dat[28];
	char_dat[i].luk=dat[29];
	char_dat[i].max_hp=40 * (100 + char_dat[i].vit)/100;
	char_dat[i].max_sp=11 * (100 + char_dat[i].int_)/100;;
	char_dat[i].hp=char_dat[i].max_hp;
	char_dat[i].sp=char_dat[i].max_sp;
	char_dat[i].status_point=0;
	char_dat[i].skill_point=0;
	char_dat[i].option=0;
	char_dat[i].karma=0;
	char_dat[i].manner=0;
	char_dat[i].party_id=0;
	char_dat[i].guild_id=0;
	char_dat[i].hair=dat[33];
	char_dat[i].hair_color=dat[31];
	char_dat[i].clothes_color=0;
	char_dat[i].inventory[0].nameid = 1201; /* Knife */
	char_dat[i].inventory[0].amount = 1;
	char_dat[i].inventory[0].equip = 0x02;
	char_dat[i].inventory[0].identify = 1;
	char_dat[i].inventory[1].nameid = 2301; /* Cotton Shirt */
	char_dat[i].inventory[1].amount = 1;
	char_dat[i].inventory[1].equip = 0x10;
	char_dat[i].inventory[1].identify = 1;
	char_dat[i].weapon = 1;
	char_dat[i].shield=0;
	char_dat[i].head_top=0;
	char_dat[i].head_mid=0;
	char_dat[i].head_bottom=0;
	memcpy(&char_dat[i].last_point,&start_point,sizeof(start_point));
	memcpy(&char_dat[i].save_point,&start_point,sizeof(start_point));
/*strcpy(char_dat[i].last_point.map,"new_1-1.gat");
	char_dat[i].last_point.x=start_po;
	char_dat[i].last_point.y=111;
	strcpy(char_dat[i].save_point.map,"new_1-1.gat");
	char_dat[i].save_point.x=53;
	char_dat[i].save_point.y=111;*/
	char_num++;
	
	mmo_char_sync();
	return i;
}

int count_users(void)
{
	if(login_fd>0 && session[login_fd]){
		int i,users;
		for(i=0,users=0;i<MAX_MAP_SERVERS;i++)
			if(server_fd[i]>0)
				users+=server[i].users;
		return users;
	}
  return 0;
}

int mmo_char_send006b(int fd,struct char_session_data *sd)
{
	int i,j,found_num;
#ifdef NEW_006b
	int offset=24;
#else
	int offset=4;
#endif

	sd->state=CHAR_STATE_AUTHOK;
	for(i=found_num=0;i<char_num;i++){
		if(char_dat[i].account_id==sd->account_id){
			sd->found_char[found_num]=i;
			found_num++;
			if(found_num==9)
				break;
		}
	}
	for(i=found_num;i<9;i++)
		sd->found_char[i]=-1;

	memset(WFIFOP(fd,0),0,offset+found_num*106);
	WFIFOW(fd,0)=0x6b;
	WFIFOW(fd,2)=offset+found_num*106;

	for( i = 0; i < found_num; i++ ) {
		j=sd->found_char[i];
		
		memset(WFIFOP(fd,offset+(i*106)),0x00,106);
		
		WFIFOL(fd,offset+(i*106)) = char_dat[j].char_id;
		WFIFOL(fd,offset+(i*106)+4) = char_dat[j].base_exp;
		WFIFOL(fd,offset+(i*106)+8) = char_dat[j].zeny;
		WFIFOL(fd,offset+(i*106)+12) = char_dat[j].job_exp;
		WFIFOL(fd,offset+(i*106)+16) = char_dat[j].job_level;
		
		WFIFOL(fd,offset+(i*106)+20) = 0;
		WFIFOL(fd,offset+(i*106)+24) = 0;
		WFIFOL(fd,offset+(i*106)+28) = char_dat[j].option;
		
		WFIFOL(fd,offset+(i*106)+32) = char_dat[j].karma;
		WFIFOL(fd,offset+(i*106)+36) = char_dat[j].manner;
		
		WFIFOW(fd,offset+(i*106)+40) = char_dat[j].status_point;
		WFIFOW(fd,offset+(i*106)+42) = (char_dat[j].hp > 0x7fff)? 0x7fff:char_dat[j].hp;
		WFIFOW(fd,offset+(i*106)+44) = (char_dat[j].max_hp > 0x7fff)? 0x7fff:char_dat[j].max_hp;
		WFIFOW(fd,offset+(i*106)+46) = (char_dat[j].sp > 0x7fff)? 0x7fff:char_dat[j].sp;
		WFIFOW(fd,offset+(i*106)+48) = (char_dat[j].max_sp > 0x7fff)? 0x7fff:char_dat[j].max_sp;
		WFIFOW(fd,offset+(i*106)+50) = DEFAULT_WALK_SPEED; // char_dat[j].speed;
		WFIFOW(fd,offset+(i*106)+52) = char_dat[j].class;
		WFIFOW(fd,offset+(i*106)+54) = char_dat[j].hair;
		WFIFOW(fd,offset+(i*106)+56) = char_dat[j].weapon;
		WFIFOW(fd,offset+(i*106)+58) = char_dat[j].base_level;
		WFIFOW(fd,offset+(i*106)+60) = char_dat[j].skill_point;
		WFIFOW(fd,offset+(i*106)+62) = char_dat[j].head_bottom;
		WFIFOW(fd,offset+(i*106)+64) = char_dat[j].shield;
		WFIFOW(fd,offset+(i*106)+66) = char_dat[j].head_top;
		WFIFOW(fd,offset+(i*106)+68) = char_dat[j].head_mid;
		WFIFOW(fd,offset+(i*106)+70) = char_dat[j].hair_color;
		WFIFOW(fd,offset+(i*106)+72) = char_dat[j].clothes_color;
		
		memcpy( WFIFOP(fd,offset+(i*106)+74), char_dat[j].name, 24 );
		
		WFIFOB(fd,offset+(i*106)+98) = (char_dat[j].str > 255)? 255:char_dat[j].str;
		WFIFOB(fd,offset+(i*106)+99) = (char_dat[j].agi > 255)? 255:char_dat[j].agi;
		WFIFOB(fd,offset+(i*106)+100) = (char_dat[j].vit > 255)? 255:char_dat[j].vit;
		WFIFOB(fd,offset+(i*106)+101) = (char_dat[j].int_ > 255)? 255:char_dat[j].int_;
		WFIFOB(fd,offset+(i*106)+102) = (char_dat[j].dex > 255)? 255:char_dat[j].dex;
		WFIFOB(fd,offset+(i*106)+103) = (char_dat[j].luk > 255)? 255:char_dat[j].luk;
		WFIFOB(fd,offset+(i*106)+104) = char_dat[j].char_num;
	}

	WFIFOSET(fd,WFIFOW(fd,2));
	return 0;
}

int set_account_reg2(int acc,int num,struct global_reg *reg)
{
	int i,c;
	for(i=0,c=0;i<char_num;i++){
		if(char_dat[i].account_id==acc){
			memcpy(char_dat[i].account_reg2,reg,sizeof(char_dat[i].account_reg2));
			char_dat[i].account_reg2_num=num;
			c++;
		}
	}
	return c;
}

// 離婚(char削除時に使用)
int char_divorce(struct mmo_charstatus *cs){
	if(cs == NULL)
		return 0;

	if(cs->partner_id > 0){
		int i,j;
		for(i=0;i<char_num;i++){
			if(char_dat[i].char_id == cs->partner_id && char_dat[i].partner_id == cs->char_id){
				cs->partner_id=0;
				char_dat[i].partner_id=0;
				for(j=0;j<MAX_INVENTORY;j++){
					if(char_dat[i].inventory[j].nameid == WEDDING_RING_M || char_dat[i].inventory[j].nameid == WEDDING_RING_F){
						memset(&char_dat[i].inventory[j],0,sizeof(char_dat[i].inventory[0]));
					}
				}
				return 0;
			}
		}
	}
	return 0;
}

// キャラ削除に伴うデータ削除
static int char_delete(struct mmo_charstatus *cs,const char *mail)
{
	int j;
	char buf[64];
	
	nullpo_retr(-1,cs);
	
	printf("char_delete: %s\n",cs->name);
	// キャラが接続しているかもしれないのでmapに切断要求
	WBUFW(buf,0)=0x2b19;
	WBUFL(buf,2)=cs->account_id;
	mapif_sendall(buf,6);
	
	// ペット削除
	if(cs->pet_id)
		inter_pet_delete(cs->pet_id);
	for(j=0;j<MAX_INVENTORY;j++)
		if(cs->inventory[j].card[0] == (short)0xff00)
			inter_pet_delete(*((long *)(&cs->inventory[j].card[2])));
	for(j=0;j<MAX_CART;j++)
		if(cs->cart[j].card[0] == (short)0xff00)
			inter_pet_delete(*((long *)(&cs->cart[j].card[2])));
	// ギルド脱退
	if(cs->guild_id)
		inter_guild_leave(cs->guild_id,cs->account_id,cs->char_id);
	// パーティー脱退
	if(cs->party_id)
		inter_party_leave(cs->party_id,cs->account_id);
	// 離婚
	if(cs->partner_id){
		// 離婚情報をmapに通知
		WBUFW(buf,0)=0x2b12;
		WBUFL(buf,2)=cs->char_id;
		WBUFL(buf,6)=cs->partner_id;
		mapif_sendall(buf,10);
		// 離婚
		char_divorce(cs);
	}

	return 0;
}

// authfifoの比較
int cmp_authfifo(int i,int account_id,int login_id1,int login_id2,int ip)
{
	if(	auth_fifo[i].account_id==account_id &&
		auth_fifo[i].login_id1==login_id1 )
		return 1;
#ifdef CMP_AUTHFIFO_LOGIN2
//	printf("cmp_authfifo: id2 check %d %x %x = %08x %08x %08x\n",i,auth_fifo[i].login_id2,login_id2,
//		auth_fifo[i].account_id,auth_fifo[i].login_id1,auth_fifo[i].login_id2);
	if( auth_fifo[i].login_id2==login_id2 && login_id2>0)
		return 1;
#endif
#ifdef CMP_AUTHFIFO_IP
//	printf("cmp_authfifo: ip check %d %x %x = %08x %08x %08x\n",i,auth_fifo[i].ip,ip,
//		auth_fifo[i].account_id,auth_fifo[i].login_id1,auth_fifo[i].login_id2);
	if(auth_fifo[i].ip==ip && ip!=0 && ip!=-1)
		return 1;
#endif
	return 0;
}

// ソケットのデストラクタ
int parse_login_disconnect(int fd) {
	if(fd==login_fd)
		login_fd=-1;
	return 0;
}

int parse_tologin(int fd)
{
	int i,fdc;
	struct char_session_data *sd;

	printf("parse_tologin : %d %d %d\n",fd,RFIFOREST(fd),RFIFOW(fd,0));
	sd=session[fd]->session_data;
	while(RFIFOREST(fd)>=2){
		switch(RFIFOW(fd,0)){
		case 0x2711:
			if(RFIFOREST(fd)<3)
				return 0;
			if(RFIFOB(fd,2)){
				printf("connect login server error : %d\n",RFIFOB(fd,2));
				exit(1);
			}
			RFIFOSKIP(fd,3);
			break;
		case 0x2713:
			if(RFIFOREST(fd)<15)
				return 0;
			for(i=0;i<fd_max;i++){
				if(session[i] && (sd=session[i]->session_data)){
					if(sd->account_id==RFIFOL(fd,2))
						break;
				}
			}
			fdc=i;
			if(fdc==fd_max){
				RFIFOSKIP(fd,15);
				break;
			}
			printf("parse_tologin 2713 : %d\n",RFIFOB(fd,6));
			if(RFIFOB(fd,6)!=0){
				WFIFOW(fdc,0)=0x6c;
				WFIFOB(fdc,2)=0x42;
				WFIFOSET(fdc,3);
				RFIFOSKIP(fd,15);
				break;
			}
			sd->account_id=RFIFOL(fd,7);
			sd->login_id1=RFIFOL(fd,11);
	
			if(char_maintenance && isGM(sd->account_id)==0){
				close(fd);
				session[fd]->eof=1;
				return 0;
			}
			if(max_connect_user > 0) {
				if(count_users() < max_connect_user  || isGM(sd->account_id)>0)
					mmo_char_send006b(fdc,sd);
				else {
					WFIFOW(fdc,0)=0x6c;
					WFIFOW(fdc,2)=0;
					WFIFOSET(fdc,3);
				}
			}
			else
				mmo_char_send006b(fdc,sd);
			
			RFIFOSKIP(fd,15);
			break;
		
		case 0x2721: 	// gm reply
			{
				int oldacc,newacc;
				unsigned char buf[64];
				if(RFIFOREST(fd)<10)
					return 0;
				oldacc=RFIFOL(fd,2);
				newacc=RFIFOL(fd,6);
				RFIFOSKIP(fd,10);
				if(newacc>0){
					for(i=0;i<char_num;i++){
						if(char_dat[i].account_id==oldacc)
							char_dat[i].account_id=newacc;
					}
				}
				WBUFW(buf,0)=0x2b0b;
				WBUFL(buf,2)=oldacc;
				WBUFL(buf,6)=newacc;
				mapif_sendall(buf,10);
	//			printf("char -> map\n");
			}break;
	
		case 0x2723:	// changesex reply
			{
				int acc,sex,i/*,h,j*/;
				unsigned char buf[64];
				if(RFIFOREST(fd)<7)
					return 0;
			
				acc=RFIFOL(fd,2);
				sex=RFIFOB(fd,6);
				RFIFOSKIP(fd,7);
				if(acc>0){
					for(i=0;i<char_num;i++){
						if(char_dat[i].account_id==acc){
							char_dat[i].sex=sex;
							auth_fifo[i].sex=sex;
							if(char_dat[i].class==19 || char_dat[i].class==20){
								char_dat[i].class=(sex)?19:20;//雷鳥は職も変更
								/*for(h=1901;h<1964;h++){//雷鳥専用装備を装備していた場合は外す
									if(char_dat[i].inventory[h].equip)
										char_dat[i].inventory[h].equip=0;
								}
								for(j=0;j<MAX_SKILL;j++){//スキルはリセット
									if(char_dat[i].skill[j].id>0 && !char_dat[i].skill[j].flag){
										char_dat[i].skill_point += char_dat[i].skill[j].lv;
										char_dat[i].skill[j].lv = 0;
									}
								}*/
							}
						}
					}
				}
				WBUFW(buf,0)=0x2b0d;
				WBUFL(buf,2)=acc;
				WBUFB(buf,6)=sex;
		
				mapif_sendall(buf,7);
	//			printf("char -> map\n");
			}break;
	
		// account_reg2変更通知
		case 0x2729:
			{
				struct global_reg reg[ACCOUNT_REG2_NUM];
				unsigned char buf[4096];
				int j,p,acc;
				if(RFIFOREST(fd)<4)
					return 0;
				if(RFIFOREST(fd)<RFIFOW(fd,2))
					return 0;
				acc=RFIFOL(fd,4);
				for(p=8,j=0;p<RFIFOW(fd,2) && j<ACCOUNT_REG2_NUM;p+=36,j++){
					memcpy(reg[j].str,RFIFOP(fd,p),32);
					reg[j].value=RFIFOL(fd,p+32);
				}
				set_account_reg2(acc,j,reg);
				// 同垢ログインを禁止していれば送る必要は無い
				memcpy(buf,RFIFOP(fd,0),RFIFOW(fd,2));
				WBUFW(buf,0)=0x2b11;
				mapif_sendall(buf,WBUFW(buf,2));
				RFIFOSKIP(fd,RFIFOW(fd,2));
	//			printf("char: save_account_reg_reply\n");
			} break;
	
		// アカウント削除通知
		case 0x272a:
			{
				// 該当キャラクターの削除
				int i;
				for(i=0;i<char_max;i++){
					if(char_dat[i].account_id==RFIFOL(fd,2)){
						char_delete(&char_dat[i],NULL);
						if( i<char_num-1 )
							memcpy(&char_dat[i],&char_dat[char_num-1],sizeof(char_dat[0]));
						char_num--;
						continue;
					}
				}
				// 倉庫の削除
				inter_storage_delete(RFIFOL(fd,2));
				RFIFOSKIP(fd,6);
			} break;
		//charメンテナンス状態変更応答
		case 0x272c:
			{
				unsigned char buf[10];
				if(RFIFOREST(fd)<3)
					return 0;
				WBUFW(buf,0)=0x2b15;
				WBUFB(buf,2)=RFIFOB(fd,2);
				mapif_sendall(buf,3);
				
				RFIFOSKIP(fd,3);
			}
			break;
		default:
			close(fd);
			session[fd]->eof=1;
			return 0;
		}
	}
	RFIFOFLUSH(fd);
	return 0;
}
int char_erasemap(int fd,int id)
{
	unsigned char buf[16384];
	int i,j;
	
	WBUFW(buf,0)=0x2b16;
	WBUFL(buf,4)=server[id].ip;
	WBUFW(buf,8)=server[id].port;
	for(i=0,j=0;i<MAX_MAP_PER_SERVER;i++){
		if(server[id].map[i][0]>0){
			memcpy(WBUFP(buf,12+(j++)*16),server[id].map[i],16);
//			printf("char: map erase: %d %d %s\n",id,i,server[id].map[i]);
		}
	}
	WBUFW(buf,10)=j;
	WBUFW(buf,2)=j*16+12;
	mapif_sendallwos(fd,buf,WBUFW(buf,2));

	printf("char: map erase: %d (%d maps)\n",id,j);

	for(j=0;j<MAX_MAP_PER_SERVER;j++)
		server[id].map[j][0]=0;

	return 0;
}

int parse_map_disconnect(int fd) {
	int i,id;
	for(id=0;id<MAX_MAP_SERVERS;id++)
		if(server_fd[id]==fd)
			break;
	for(i=0;i<MAX_MAP_SERVERS;i++)
		if(server_fd[i]==fd)
			server_fd[i]=-1;
	char_erasemap(fd,id);
	//map-serverが切れたときに残っていたキャラの切断を他map-serverに通知
	for(i=0;i<char_num;i++){
		unsigned char buf[10];
		
		if(char_dat[i].mapip == server[id].ip && char_dat[i].mapport == server[id].port){
			//printf("char: mapdisconnect %s %d %d %d %d\n",char_dat[i].name,(int)char_dat[i].mapip,(int)server[id].ip,char_dat[i].mapport,server[id].port);
			char_dat[i].mapip=0;
			char_dat[i].mapport=0;
			WBUFW(buf,0)=0x2b17;
			WBUFL(buf,2)=char_dat[i].char_id;
			mapif_sendallwos(fd,buf,6);
		}
	}
	close(fd);
	return 0;
}

int parse_frommap(int fd)
{
	int i,j;
	int id;
	
	for(id=0;id<MAX_MAP_SERVERS;id++)
		if(server_fd[id]==fd)
			break;
	if(id==MAX_MAP_SERVERS)
		session[fd]->eof=1;
	//printf("parse_frommap : %d %d %d\n",fd,RFIFOREST(fd),RFIFOW(fd,0));
	while(RFIFOREST(fd)>=2){
		switch(RFIFOW(fd,0)){
		// マップサーバーから担当マップ名を受信
		case 0x2afa:
			if(RFIFOREST(fd)<4 || RFIFOREST(fd)<RFIFOW(fd,2))
				return 0;
			for(i=4,j=0;i<RFIFOW(fd,2);i+=16,j++){
				memcpy(server[id].map[j],RFIFOP(fd,i),16);
			//	printf("set map %d.%d : %s\n",id,j,server[id].map[j]);
			}
			i=server[id].ip;
			{
				unsigned char *p=(unsigned char *)&i;
				printf("set map %d from %d.%d.%d.%d:%d (%d maps)\n",
					id,p[0],p[1],p[2],p[3],server[id].port,j);
			}
			server[id].map[j][0]=0;
			RFIFOSKIP(fd,RFIFOW(fd,2));
			WFIFOW(fd,0)=0x2afb;
			WFIFOW(fd,2)=0;
			WFIFOSET(fd,3);
			{	// 他のマップサーバーに担当マップ情報を送信
				unsigned char buf[16384];
				int x;
				WBUFW(buf,0)=0x2b04;
				WBUFW(buf,2)=j*16+12;
				WBUFL(buf,4)=server[id].ip;
				WBUFW(buf,8)=server[id].port;
				WBUFW(buf,10)=i;
				for(i=0;i<j;i++){
					memcpy(WBUFP(buf,12+i*16),server[id].map[i],16);
				}
				mapif_sendallwos(fd,buf,WBUFW(buf,2));
				// 他のマップサーバーの担当マップを送信
				for(x=0;x<MAX_MAP_SERVERS;x++){
					if(server_fd[x]>=0 && x!=id){
						WFIFOW(fd,0)=0x2b04;
						WFIFOL(fd,4)=server[x].ip;
						WFIFOW(fd,8)=server[x].port;
						for(i=0,j=0;i<MAX_MAP_PER_SERVER;i++){
							if(server[x].map[i][0]>0)
								memcpy(WFIFOP(fd,12+(j++)*16),server[x].map[i],16);
						}
						if(j>0){
							WFIFOW(fd,10)=j;
							WFIFOW(fd,2)=j*16+12;
							WFIFOSET(fd,WFIFOW(fd,2));
						}
					}
				}
			}
			break;
		// 認証要求
		case 0x2afc:
			if(RFIFOREST(fd)<24)
				return 0;
			printf("auth_fifo search %08x %08x %08x %08x %08x\n",
				RFIFOL(fd,2),RFIFOL(fd,6),RFIFOL(fd,10),RFIFOL(fd,14),RFIFOL(fd,18));
			for(i=0;i<AUTH_FIFO_SIZE;i++){
				if( cmp_authfifo(i,RFIFOL(fd,2),RFIFOL(fd,10),RFIFOL(fd,14),RFIFOL(fd,18)) &&
					auth_fifo[i].char_id==RFIFOL(fd,6) &&
					!auth_fifo[i].delflag){
					auth_fifo[i].delflag=1;
					break;
				}
			}
			if(i==AUTH_FIFO_SIZE){
//				char_dat[auth_fifo[i].char_pos].mapip=0;
//				char_dat[auth_fifo[i].char_pos].mapport=0;
				WFIFOW(fd,0)=0x2afe;
				WFIFOW(fd,2)=RFIFOL(fd,2);
				WFIFOB(fd,6)=0;
				WFIFOSET(fd,7);
				printf("auth_fifo search error!\n");
			} else {
				unsigned char buf[60];
				
				char_dat[auth_fifo[i].char_pos].mapip=server[id].ip;
				char_dat[auth_fifo[i].char_pos].mapport=server[id].port;
				
				WFIFOW(fd,0)=0x2afd;
				WFIFOW(fd,2)=12+sizeof(char_dat[0]);
				WFIFOL(fd,4)=RFIFOL(fd,2);
			//	WFIFOL(fd,8)=RFIFOL(fd,6);
				WFIFOL(fd,8)=auth_fifo[i].login_id2;
				char_dat[auth_fifo[i].char_pos].sex=auth_fifo[i].sex;
				memcpy(WFIFOP(fd,12),&char_dat[auth_fifo[i].char_pos],sizeof(char_dat[0]));
				WFIFOSET(fd,WFIFOW(fd,2));
				
				//このmap-server以外にログインしたことを通知する
				WBUFW(buf,0)=0x2b09;
				WBUFL(buf,2)=char_dat[auth_fifo[i].char_pos].char_id;
				memcpy(WBUFP(buf,6),char_dat[auth_fifo[i].char_pos].name,24);
				WBUFL(buf,30)=char_dat[auth_fifo[i].char_pos].account_id;
				WBUFL(buf,34)=char_dat[auth_fifo[i].char_pos].mapip;
				WBUFW(buf,38)=char_dat[auth_fifo[i].char_pos].mapport;
				mapif_sendallwos(fd,buf,40);
			}
			RFIFOSKIP(fd,24);
			break;
		// MAPサーバー上のユーザー数受信
		case 0x2aff:
			if(RFIFOREST(fd)<6)
				return 0;
			server[id].users=RFIFOL(fd,2);
			RFIFOSKIP(fd,6);
			break;
		// キャラデータ保存
		case 0x2b01:
			if(RFIFOREST(fd)<4 || RFIFOREST(fd)<RFIFOW(fd,2))
				return 0;
			for(i=0;i<char_num;i++){
				if(char_dat[i].account_id==RFIFOL(fd,4) &&
					char_dat[i].char_id==RFIFOL(fd,8))
					break;
			}
			if(i!=char_num){
				memcpy(&char_dat[i],RFIFOP(fd,12),sizeof(char_dat[0]));
			}
			RFIFOSKIP(fd,RFIFOW(fd,2));
			break;
		// キャラセレ要求
		case 0x2b02:
			if(RFIFOREST(fd)<18)
				return 0;

			if(auth_fifo_pos>=AUTH_FIFO_SIZE){
				auth_fifo_pos=0;
			}
			printf("auth_fifo set 0x2b02 %d - %08x %08x %08x %08x\n",
				auth_fifo_pos,RFIFOL(fd,2),RFIFOL(fd,6),RFIFOL(fd,10),RFIFOL(fd,14));
			auth_fifo[auth_fifo_pos].account_id=RFIFOL(fd,2);
			auth_fifo[auth_fifo_pos].char_id=0;
			auth_fifo[auth_fifo_pos].login_id1=RFIFOL(fd,6);
			auth_fifo[auth_fifo_pos].login_id2=RFIFOL(fd,10);
			auth_fifo[auth_fifo_pos].delflag=2;
			auth_fifo[auth_fifo_pos].char_pos=0;
			auth_fifo[auth_fifo_pos].tick=gettick();
			auth_fifo[auth_fifo_pos].ip=RFIFOL(fd,14);
			auth_fifo_pos++;
			
			WFIFOW(fd,0)=0x2b03;
			WFIFOL(fd,2)=RFIFOL(fd,2);
			WFIFOB(fd,6)=0;
			WFIFOSET(fd,7);
			
			RFIFOSKIP(fd,18);
			
			break;
		// マップサーバー間移動要求
		case 0x2b05:
			if(RFIFOREST(fd)<41)
				return 0;

			if(auth_fifo_pos>=AUTH_FIFO_SIZE){
				auth_fifo_pos=0;
			}
			memcpy(WFIFOP(fd,2),RFIFOP(fd,2),38);
			WFIFOW(fd,0)=0x2b06;

			printf("auth_fifo set 0x2b05 %d - %08x %08x\n",auth_fifo_pos,RFIFOL(fd,2),RFIFOL(fd,6));
			auth_fifo[auth_fifo_pos].account_id=RFIFOL(fd,2);
			auth_fifo[auth_fifo_pos].char_id=RFIFOL(fd,10);
			auth_fifo[auth_fifo_pos].login_id1=RFIFOL(fd,6);
			auth_fifo[auth_fifo_pos].delflag=0;
			auth_fifo[auth_fifo_pos].sex=RFIFOB(fd,40);
			auth_fifo[auth_fifo_pos].tick=gettick();
			auth_fifo[auth_fifo_pos].ip=session[fd]->client_addr.sin_addr.s_addr;
			{
				int i=0;
				for(i=0;i<char_num;i++){
					if(	char_dat[i].account_id==RFIFOL(fd,2) &&
						char_dat[i].char_id==RFIFOL(fd,10) )
					break;
				}
				if(i==char_num){
					WFIFOW(fd,6)=1;
					WFIFOSET(fd,40);
					RFIFOSKIP(fd,41);
					break;
				}
				auth_fifo[auth_fifo_pos].char_pos=i;
			}
			auth_fifo_pos++;
			
			WFIFOL(fd,6)=0;
			WFIFOSET(fd,40);
			RFIFOSKIP(fd,41);
			
			break;
			
		// キャラ名検索
		case 0x2b08:
			if(RFIFOREST(fd)<6)
				return 0;
			for(i=0;i<char_num;i++){
				if(char_dat[i].char_id==RFIFOL(fd,2))
					break;
			}
			WFIFOW(fd,0)=0x2b09;
			WFIFOL(fd,2)=RFIFOL(fd,2);
			if(i!=char_num){
				memcpy(WFIFOP(fd,6),char_dat[i].name,24);
				WFIFOL(fd,30)=char_dat[i].account_id;
				WFIFOL(fd,34)=char_dat[i].mapip;
				WFIFOW(fd,38)=char_dat[i].mapport;
			}else{
				memcpy(WFIFOP(fd,6),unknown_char_name,24);
				WFIFOL(fd,30)=0;
				WFIFOL(fd,34)=0;
				WFIFOW(fd,38)=0;
			}
			WFIFOSET(fd,40);
			
			RFIFOSKIP(fd,6);
			break;
		
		// GMになりたーい
		case 0x2b0a:
			if(RFIFOREST(fd)<4)
				return 0;
			if(RFIFOREST(fd)<RFIFOW(fd,2))
				return 0;
			memcpy(WFIFOP(login_fd,2),RFIFOP(fd,2),RFIFOW(fd,2)-2);
			WFIFOW(login_fd,0)=0x2720;
			WFIFOSET(login_fd,RFIFOW(fd,2));
//			printf("char : change gm -> login %d %s %d\n",RFIFOL(fd,4),RFIFOP(fd,8),RFIFOW(fd,2));
			RFIFOSKIP(fd,RFIFOW(fd,2));
			break;

		//性別変換要求
		case 0x2b0c:
			if(RFIFOREST(fd)<4)
				return 0;
			if(RFIFOREST(fd)<RFIFOW(fd,2)){
				printf("char : changesex -> length error : %d : %d\n",RFIFOREST(fd),RFIFOW(fd,2));
				return 0;}
			WFIFOW(login_fd,0)=0x2722;
			WFIFOW(login_fd,2)=RFIFOW(fd,2);
			WFIFOL(login_fd,4)=RFIFOL(fd,4);
			WFIFOB(login_fd,8)=RFIFOB(fd,8);
			WFIFOSET(login_fd,RFIFOW(fd,2));
//			printf("char : change sex -> login %d %d %d \n",RFIFOL(fd,4),RFIFOB(fd,8),RFIFOW(fd,2));
			RFIFOSKIP(fd,RFIFOW(fd,2));
			break;

		// account_reg保存要求
		case 0x2b10: {
			struct global_reg reg[ACCOUNT_REG2_NUM];
			int j,p,acc;
			if(RFIFOREST(fd)<4)
				return 0;
			if(RFIFOREST(fd)<RFIFOW(fd,2))
				return 0;
			acc=RFIFOL(fd,4);
			for(p=8,j=0;p<RFIFOW(fd,2) && j<ACCOUNT_REG2_NUM;p+=36,j++){
				memcpy(reg[j].str,RFIFOP(fd,p),32);
				reg[j].value=RFIFOL(fd,p+32);
			}
			set_account_reg2(acc,j,reg);
			// loginサーバーへ送る
			memcpy(WFIFOP(login_fd,0),RFIFOP(fd,0),RFIFOW(fd,2));
			WFIFOW(login_fd,0)=0x2728;
			WFIFOSET(login_fd,WFIFOW(login_fd,2));
			// ワールドへの同垢ログインがなければmapサーバーに送る必要はない
			//memcpy(buf,RFIFOP(fd,0),RFIFOW(fd,2));
			//WBUFW(buf,0)=0x2b11;
			//mapif_sendall(buf,WBUFW(buf,2));
			RFIFOSKIP(fd,RFIFOW(fd,2));
//			printf("char: save_account_reg (from map)\n");
			break;
		}
		//mapサーバ 有効化
		case 0x2b13:
			if(RFIFOREST(fd)<3)
				return 0;
			server[id].active=RFIFOB(fd,2);
			printf("char: map_server_active: %d %d\n",id,server[id].active);
			RFIFOSKIP(fd,3);
			break;
		//charサーバ メンテナンス状態に
		case 0x2b14:
			if(RFIFOREST(fd)<3)
				return 0;
			char_maintenance=RFIFOB(fd,2);
			printf("char: maintenance: %d\n",char_maintenance);
			//loginに通知
			WFIFOW(login_fd,0)=0x272b;
			WFIFOB(login_fd,2)=char_maintenance;
			WFIFOSET(login_fd,3);
			
			RFIFOSKIP(fd,3);
			break;
		//キャラクター切断を他mapに通知
		case 0x2b18:
			{
				unsigned char buf[10];
				
				if(RFIFOREST(fd)<10)
					return 0;
				
				for(i=0;i<char_num;i++){
					if(char_dat[i].account_id==RFIFOL(fd,2) &&
						char_dat[i].char_id==RFIFOL(fd,6))
						break;
				}
				if(i!=char_num){
					char_dat[i].mapip=0;
					char_dat[i].mapport=0;
					WBUFW(buf,0)=0x2b17;
					WBUFL(buf,2)=RFIFOL(fd,6);
					mapif_sendallwos(fd,buf,6);
				}
				
				RFIFOSKIP(fd,10);
			}
			break;

		default:
			// inter server処理に渡す
			{
				int r=inter_parse_frommap(fd);
				if( r==1 )	break;		// 処理できた
				if( r==2 )	return 0;	// パケット長が足りない
			}
			
			// inter server処理でもない場合は切断
			printf("char: unknown packet %x! (from login)\n",RFIFOW(fd,0));
			close(fd);
			session[fd]->eof=1;	
			return 0;
		}
	}
	return 0;
}
/*==========================================
 * mapが含まれているmap-serverを探す
 *------------------------------------------
 */
int search_mapserver(char *map, struct mmo_charstatus *cd)
{
	int i,j,k;
	printf("search_mapserver %s\n",map);
	for(i=0;i<MAX_MAP_SERVERS;i++){
		if(server_fd[i]<0)
			continue;
		for(j=0;server[i].map[j][0];j++){
			//printf("%s : %s = %d\n",server[i].map[j],map,strcmp(server[i].map[j],map));
			if((k=strcmp(server[i].map[j],map))==0){
				printf("search_mapserver success %s -> %d\n",map,i);
				return i;
			}
			//printf("%s : %s = %d\n",server[i].map[j],map,k);
		}
	}
	if(cd){
		for(i=0;i<MAX_MAP_SERVERS;i++){
			if(server_fd[i]<0)
				continue;
			for(j=0;server[i].map[j][0];j++){
				memcpy(cd->last_point.map,server[i].map[j],16);
				printf("search_mapserver success? %s -> %d\n",map,i);
				return i;
			}
		}
	}
	printf("search_mapserver failed\n");
	return -1;
}

// char_mapifの初期化処理（現在はinter_mapif初期化のみ）
static int char_mapif_init(int fd)
{
	return inter_mapif_init(fd);
}

int parse_char_disconnect(int fd) {
	if(fd==login_fd)
		login_fd=-1;
	return 0;
}

int parse_char(int fd)
{
	int i,ch,j;
	unsigned short cmd;
	struct char_session_data *sd;

	if(login_fd<0)
		session[fd]->eof=1;
	sd=session[fd]->session_data;
	while(RFIFOREST(fd)>=2){
		cmd = RFIFOW(fd,0);
		// crc32のスキップ用
		if(	sd==NULL			&&	// 未ログインor管理パケット
			RFIFOREST(fd)>=4	&&	// 最低バイト数制限 ＆ 0x7530,0x7532管理パケ除去
			RFIFOREST(fd)<=21	&&	// 最大バイト数制限 ＆ サーバーログイン除去
			cmd!=0x20b	&&	// md5通知パケット除去
			(RFIFOREST(fd)<6 || RFIFOW(fd,4)==0x65)	){	// 次に何かパケットが来てるなら、接続でないとだめ
			RFIFOSKIP(fd,4);
			cmd = RFIFOW(fd,0);
			printf("parse_char : %d crc32 skipped\n",fd);
			if(RFIFOREST(fd)==0)
				return 0;
		}

		if(cmd<30000 && cmd!=0x187)
			printf("parse_char : %d %d %d\n",fd,RFIFOREST(fd),cmd);

		// 不正パケットの処理
		if (sd == NULL && cmd != 0x65 && cmd != 0x20b && cmd != 0x187 &&
					 cmd != 0x2af8 && cmd != 0x7530 && cmd != 0x7532)
			cmd = 0xffff;	// パケットダンプを表示させる
		
		switch(cmd){
		case 0x20b:		//20040622暗号化ragexe対応
			if(RFIFOREST(fd)<19)
				return 0;
			RFIFOSKIP(fd,19);
			break;
		case 0x65:	// 接続要求
			if(RFIFOREST(fd)<17)
				return 0;
			if(sd==NULL)
				sd=session[fd]->session_data=(struct char_session_data *)aCalloc(1,sizeof(*sd));
			sd->account_id=RFIFOL(fd,2);
			sd->login_id1=RFIFOL(fd,6);
			sd->login_id2=RFIFOL(fd,10);
			sd->sex=RFIFOB(fd,16);
			sd->state=CHAR_STATE_WAITAUTH;

			WFIFOL(fd,0)=RFIFOL(fd,2);
			WFIFOSET(fd,4);

			for(i=0;i<AUTH_FIFO_SIZE;i++){
				if(cmp_authfifo(i,sd->account_id,sd->login_id1,sd->login_id2,session[fd]->client_addr.sin_addr.s_addr) &&
				   auth_fifo[i].delflag==2){
					auth_fifo[i].delflag=1;
					sd->account_id=auth_fifo[i].account_id;
					sd->login_id1=auth_fifo[i].login_id1;
					sd->login_id2=auth_fifo[i].login_id2;
					break;
				}
			}
			if(i==AUTH_FIFO_SIZE){
				WFIFOW(login_fd,0)=0x2712;
				WFIFOL(login_fd,2)=sd->account_id;
				WFIFOL(login_fd,6)=sd->login_id1;
				WFIFOL(login_fd,10)=sd->login_id2;
				WFIFOB(login_fd,14)=sd->sex;
				WFIFOL(login_fd,15)=session[fd]->client_addr.sin_addr.s_addr;
				WFIFOL(login_fd,19)=sd->account_id;
				WFIFOSET(login_fd,23);
			} else {
				if(char_maintenance && isGM(sd->account_id)==0){
					close(fd);
					session[fd]->eof=1;
					return 0;
				}
				if(max_connect_user > 0) {
					if(count_users() < max_connect_user  || isGM(sd->account_id)>0)
						mmo_char_send006b(fd,sd);
					else {
						WFIFOW(fd,0)=0x6c;
						WFIFOW(fd,2)=0;
						WFIFOSET(fd,3);
					}
				}
				else
					mmo_char_send006b(fd,sd);
			}

			RFIFOSKIP(fd,17);
			break;
		case 0x66:	// キャラ選択
			if(RFIFOREST(fd)<3)
				return 0;
			for(ch=0;ch<9;ch++)
				if(sd->found_char[ch]>=0 && char_dat[sd->found_char[ch]].char_num==RFIFOB(fd,2))
					break;
			if(ch!=9){
				FILE *logfp;
				logfp=fopen(char_log_filename,"a");
				if(logfp){
					fprintf(logfp,"char select %d-%d %s" RETCODE,sd->account_id,RFIFOB(fd,2),char_dat[sd->found_char[ch]].name);
					fclose(logfp);
				}

				i=search_mapserver(char_dat[sd->found_char[ch]].last_point.map,NULL);
				if(i<0){
					if(default_map_type&1){
						memcpy(char_dat[sd->found_char[ch]].last_point.map,default_map_name,16);
						i=search_mapserver(char_dat[sd->found_char[ch]].last_point.map,NULL);
					}
					if(default_map_type&2){
						if(i<0)
							i=search_mapserver(char_dat[sd->found_char[ch]].last_point.map,&char_dat[sd->found_char[ch]]);
					}
				}
				if(i<0 || server[i].active==0){
					WFIFOW(fd,0)=0x6c;
					WFIFOW(fd,2)=0;
					WFIFOSET(fd,3);
					RFIFOSKIP(fd,3);
					break;
				}
				// ２重ログイン撃退（違うマップサーバの場合）
				// 同じマップサーバの場合は、マップサーバー内で処理される
				for(j = 0;j<9;j++) {
					if(sd->found_char[j]>=0 &&
						(char_dat[sd->found_char[j]].mapip || char_dat[sd->found_char[j]].mapport) &&
						(char_dat[sd->found_char[j]].mapip != server[i].ip || char_dat[sd->found_char[j]].mapport != server[i].port)
					) {
						// ２重ログイン検出
						// mapに切断要求
						char buf[256];
						WBUFW(buf,0)=0x2b1a;
						WBUFL(buf,2)=sd->account_id;
						mapif_sendall(buf,6);

						// 接続失敗送信
						WFIFOW(fd,0)=0x6c;
						WFIFOW(fd,2)=0;
						WFIFOSET(fd,3);
						RFIFOSKIP(fd,3);
						break;
					}
				}
				if(j != 9) break;
				
				WFIFOW(fd,0)=0x71;
				WFIFOL(fd,2)=char_dat[sd->found_char[ch]].char_id;
				memcpy(WFIFOP(fd,6),char_dat[sd->found_char[ch]].last_point.map,16);
				WFIFOL(fd,22)=server[i].ip;
				WFIFOW(fd,26)=server[i].port;
				WFIFOSET(fd,28);

				if(auth_fifo_pos>=AUTH_FIFO_SIZE){
					auth_fifo_pos=0;
				}
				printf("auth_fifo set 0x66 %d - %08x %08x %08x %08x\n",
					auth_fifo_pos,sd->account_id,char_dat[sd->found_char[ch]].char_id,sd->login_id1,sd->login_id2);
				auth_fifo[auth_fifo_pos].account_id=sd->account_id;
				auth_fifo[auth_fifo_pos].char_id=char_dat[sd->found_char[ch]].char_id;
				auth_fifo[auth_fifo_pos].login_id1=sd->login_id1;
				auth_fifo[auth_fifo_pos].login_id2=sd->login_id2;
				auth_fifo[auth_fifo_pos].delflag=0;
				auth_fifo[auth_fifo_pos].char_pos=sd->found_char[ch];
				auth_fifo[auth_fifo_pos].sex=sd->sex;
				auth_fifo_pos++;
			}
			RFIFOSKIP(fd,3);
			break;
		case 0x67:	// 作成
			if(RFIFOREST(fd)<37)
				return 0;
			i=make_new_char(fd,RFIFOP(fd,2));
			if(i<0){
				WFIFOW(fd,0)=0x6e;
				WFIFOB(fd,2)=0x00;
				WFIFOSET(fd,3);
				RFIFOSKIP(fd,37);
				break;
			}

			WFIFOW(fd,0)=0x6d;
			memset(WFIFOP(fd,2),0x00,106);

			WFIFOL(fd,2) = char_dat[i].char_id;
			WFIFOL(fd,2+4) = char_dat[i].base_exp;
			WFIFOL(fd,2+8) = char_dat[i].zeny;
			WFIFOL(fd,2+12) = char_dat[i].job_exp;
			WFIFOL(fd,2+16) = char_dat[i].job_level;

			WFIFOL(fd,2+28) = char_dat[i].karma;
			WFIFOL(fd,2+32) = char_dat[i].manner;

			WFIFOW(fd,2+40) = 0x30;
			WFIFOW(fd,2+42) = (char_dat[i].hp > 0x7fff)? 0x7fff:char_dat[i].hp;
			WFIFOW(fd,2+44) = (char_dat[i].max_hp > 0x7fff)? 0x7fff:char_dat[i].max_hp;
			WFIFOW(fd,2+46) = (char_dat[i].sp > 0x7fff)? 0x7fff:char_dat[i].sp;
			WFIFOW(fd,2+48) = (char_dat[i].max_sp > 0x7fff)? 0x7fff:char_dat[i].max_sp;
			WFIFOW(fd,2+50) = DEFAULT_WALK_SPEED; // char_dat[i].speed;
			WFIFOW(fd,2+52) = char_dat[i].class;
			WFIFOW(fd,2+54) = char_dat[i].hair;

			WFIFOW(fd,2+58) = char_dat[i].base_level;
			WFIFOW(fd,2+60) = char_dat[i].skill_point;

			WFIFOW(fd,2+64) = char_dat[i].shield;
			WFIFOW(fd,2+66) = char_dat[i].head_top;
			WFIFOW(fd,2+68) = char_dat[i].head_mid;
			WFIFOW(fd,2+70) = char_dat[i].hair_color;

			memcpy( WFIFOP(fd,2+74), char_dat[i].name, 24 );

			WFIFOB(fd,2+98) = (char_dat[i].str > 255)? 255:char_dat[i].str;
			WFIFOB(fd,2+99) = (char_dat[i].agi > 255)? 255:char_dat[i].agi;
			WFIFOB(fd,2+100) = (char_dat[i].vit > 255)? 255:char_dat[i].vit;
			WFIFOB(fd,2+101) = (char_dat[i].int_ > 255)? 255:char_dat[i].int_;
			WFIFOB(fd,2+102) = (char_dat[i].dex > 255)? 255:char_dat[i].dex;
			WFIFOB(fd,2+103) = (char_dat[i].luk > 255)? 255:char_dat[i].luk;
			WFIFOB(fd,2+104) = char_dat[i].char_num;

			WFIFOSET(fd,108);
			RFIFOSKIP(fd,37);
			for(ch=0;ch<9;ch++) {
				if(sd->found_char[ch]==-1) {
					sd->found_char[ch]=i;
					break;
				}
			}
		case 0x68:	// 削除
			if(RFIFOREST(fd)<46)
				return 0;
			for(i=0;i<9;i++){
				struct mmo_charstatus *cs=NULL;
				if( (cs=&char_dat[sd->found_char[i]])->char_id==RFIFOL(fd,2)){
					char_delete(cs,NULL);	// 削除のための処理
					if(sd->found_char[i]!=char_num-1){
						memcpy(cs,&char_dat[char_num-1],sizeof(char_dat[0]));
					}
					char_num--;
					for(ch=i;ch<9-1;ch++)
						sd->found_char[ch]=sd->found_char[ch+1];
					sd->found_char[8]=-1;
					break;
				}
			}
			if( i==9 ){
				WFIFOW(fd,0)=0x70;
				WFIFOB(fd,2)=0;
				WFIFOSET(fd,3);
			} else {
				WFIFOW(fd,0)=0x6f;
				WFIFOSET(fd,2);
			}
			RFIFOSKIP(fd,46);
			break;
		case 0x2af8:	// マップサーバーログイン
			if(RFIFOREST(fd)<60)
				return 0;
			WFIFOW(fd,0)=0x2af9;
			for(i=0;i<MAX_MAP_SERVERS;i++){
				if(server_fd[i]<0)
					break;
			}
			if(i==MAX_MAP_SERVERS || strcmp(RFIFOP(fd,2),userid) || strcmp(RFIFOP(fd,26),passwd)){
				WFIFOB(fd,2)=3;
				WFIFOSET(fd,3);
				RFIFOSKIP(fd,60);
			} else {
				WFIFOB(fd,2)=0;
				session[fd]->func_parse=parse_frommap;
				session[fd]->func_destruct = parse_map_disconnect;
				server_fd[i]=fd;
				server[i].ip=RFIFOL(fd,54);
				server[i].port=RFIFOW(fd,58);
				server[i].users=0;
				memset(server[i].map,0,sizeof(server[i].map));
				WFIFOSET(fd,3);
				for(i=0;i<char_num;i++){
				//他マップにログインしているキャラクター情報を送信する
					if(char_dat[i].mapip != 0 && char_dat[i].mapport !=0){
						WFIFOW(fd,0)=0x2b09;
						WFIFOL(fd,2)=char_dat[i].char_id;
						memcpy(WFIFOP(fd,6),char_dat[i].name,24);
						WFIFOL(fd,30)=char_dat[i].account_id;
						WFIFOL(fd,34)=char_dat[i].mapip;
						WFIFOW(fd,38)=char_dat[i].mapport;
						WFIFOSET(fd,40);
					}
				}
				RFIFOSKIP(fd,60);
				realloc_fifo(fd,FIFOSIZE_SERVERLINK,FIFOSIZE_SERVERLINK);	
				char_mapif_init(fd);
				return 0;
			}
			break;
		case 0x187:	// Alive信号？
			if (RFIFOREST(fd) < 6) {
				return 0;
			}
			WFIFOW(fd,0)=0x187;
			WFIFOL(fd,2)=sd->account_id;
			WFIFOSET(fd,6);
			RFIFOSKIP(fd, 6);
			break;
		
		case 0x7530:	// Athena情報所得
			WFIFOW(fd,0)=0x7531;
			WFIFOB(fd,2)=ATHENA_MAJOR_VERSION;
			WFIFOB(fd,3)=ATHENA_MINOR_VERSION;
			WFIFOB(fd,4)=ATHENA_REVISION;
			WFIFOB(fd,5)=ATHENA_RELEASE_FLAG;
			WFIFOB(fd,6)=ATHENA_OFFICIAL_FLAG;
			WFIFOB(fd,7)=ATHENA_SERVER_INTER | ATHENA_SERVER_CHAR;
			WFIFOW(fd,8)=ATHENA_MOD_VERSION;
			WFIFOSET(fd,10);
			RFIFOSKIP(fd,2);
			return 0;
		case 0x7532:	// 接続の切断(defaultと処理は一緒だが明示的にするため)
			close(fd);
			session[fd]->eof=1;
			return 0;

		default:
#ifdef DUMP_UNKNOWN_PACKET
			{
				int i;
				printf("---- 00-01-02-03-04-05-06-07-08-09-0A-0B-0C-0D-0E-0F");
				for(i=0;i<RFIFOREST(fd);i++){
					if((i&15)==0)
						printf("\n%04X ",i);
					printf("%02X ",RFIFOB(fd,i));
				}
				printf("\n");
			}
#endif
			close(fd);
			session[fd]->eof=1;
			return 0;
		}
	}
	RFIFOFLUSH(fd);
	return 0;
}

// 全てのMAPサーバーにデータ送信（送信したmap鯖の数を返す）
int mapif_sendall(unsigned char *buf,unsigned int len)
{
	int i,c;
	for(i=0,c=0;i<MAX_MAP_SERVERS;i++){
		int fd;
		if((fd=server_fd[i])>0){
			memcpy(WFIFOP(fd,0),buf,len);
			WFIFOSET(fd,len);
			c++;
		}
	}
	return c;
}
// 自分以外の全てのMAPサーバーにデータ送信（送信したmap鯖の数を返す）
int mapif_sendallwos(int sfd,unsigned char *buf,unsigned int len)
{
	int i,c;
	for(i=0,c=0;i<MAX_MAP_SERVERS;i++){
		int fd;
		if((fd=server_fd[i])>0 && fd!=sfd){
			memcpy(WFIFOP(fd,0),buf,len);
			WFIFOSET(fd,len);
			c++;
		}
	}
	return c;
}
// MAPサーバーにデータ送信（map鯖生存確認有り）
int mapif_send(int fd,unsigned char *buf,unsigned int len)
{
	int i;
	for(i=0;i<MAX_MAP_SERVERS;i++){
		if((fd==server_fd[i])>0){
			memcpy(WFIFOP(fd,0),buf,len);
			WFIFOSET(fd,len);
			return 1;
		}
	}
	return 0;
}

int send_users_tologin(int tid,unsigned int tick,int id,int data)
{
	if(login_fd>0 && session[login_fd]){
		int i,users;
		for(i=0,users=0;i<MAX_MAP_SERVERS;i++)
			if(server_fd[i]>0)
				users+=server[i].users;
		
		WFIFOW(login_fd,0)=0x2714;
		WFIFOL(login_fd,2)=users;
		WFIFOSET(login_fd,6);
		
		for(i=0;i<MAX_MAP_SERVERS;i++){
			int fd;
			if((fd=server_fd[i])>0){
				WFIFOW(fd,0)=0x2b00;
				WFIFOL(fd,2)=users;
				WFIFOSET(fd,6);
			}
		}
	}
	return 0;
}

int check_connect_login_server(int tid,unsigned int tick,int id,int data)
{
	if(login_fd<=0 || session[login_fd]==NULL){
		login_fd=make_connection(login_ip,login_port);
		session[login_fd]->func_parse=parse_tologin;
		session[login_fd]->func_destruct = parse_login_disconnect;
		realloc_fifo(login_fd,FIFOSIZE_SERVERLINK,FIFOSIZE_SERVERLINK);	
		WFIFOW(login_fd,0)=0x2710;
		memcpy(WFIFOP(login_fd,2),userid,24);
		memcpy(WFIFOP(login_fd,26),passwd,24);
		WFIFOL(login_fd,50)=0;
		WFIFOL(login_fd,54)=char_ip;
		WFIFOW(login_fd,58)=char_port;
		memcpy(WFIFOP(login_fd,60),server_name,20);
		WFIFOW(login_fd,80)=char_maintenance;
		WFIFOW(login_fd,82)=char_new;
		WFIFOSET(login_fd,84);
	}
	return 0;
}

int char_config_read(const char *cfgName)
{
	struct hostent *h=NULL;
	char line[1024],w1[1024],w2[1024];
	int i;
	FILE *fp=fopen(cfgName,"r");
	if(fp==NULL){
		printf("file not found: %s\n",cfgName);
		exit(1);
	}

	while(fgets(line,1020,fp)){
		if(line[0] == '/' && line[1] == '/')
			continue;

		i=sscanf(line,"%[^:]: %[^\r\n]",w1,w2);
		if(i!=2)
			continue;
		if(strcmpi(w1,"userid")==0){
			memcpy(userid,w2,24);
		}
		else if(strcmpi(w1,"passwd")==0){
			memcpy(passwd,w2,24);
		}
		else if(strcmpi(w1,"server_name")==0){
			memcpy(server_name,w2,16);
		}
		else if(strcmpi(w1,"login_ip")==0){
			h = gethostbyname (w2);
			if(h != NULL) { 
				printf("Login server IP address : %s -> %d.%d.%d.%d\n",w2,(unsigned char)h->h_addr[0],(unsigned char)h->h_addr[1],(unsigned char)h->h_addr[2],(unsigned char)h->h_addr[3]);
				sprintf(login_ip_str,"%d.%d.%d.%d",(unsigned char)h->h_addr[0],(unsigned char)h->h_addr[1],(unsigned char)h->h_addr[2],(unsigned char)h->h_addr[3]);
			}
			else
				memcpy(login_ip_str,w2,16);
		}
		else if(strcmpi(w1,"login_port")==0){
			login_port=atoi(w2);
		}
		else if(strcmpi(w1,"char_ip")==0){
			h = gethostbyname (w2);
			if(h != NULL) { 
				printf("Character server IP address : %s -> %d.%d.%d.%d\n",w2,(unsigned char)h->h_addr[0],(unsigned char)h->h_addr[1],(unsigned char)h->h_addr[2],(unsigned char)h->h_addr[3]);
				sprintf(char_ip_str,"%d.%d.%d.%d",(unsigned char)h->h_addr[0],(unsigned char)h->h_addr[1],(unsigned char)h->h_addr[2],(unsigned char)h->h_addr[3]);
			}
			else
				memcpy(char_ip_str,w2,16);
		}
		else if(strcmpi(w1,"char_port")==0){
			char_port=atoi(w2);
		}
		else if(strcmpi(w1,"char_maintenance")==0){
			char_maintenance=atoi(w2);
		}
		else if(strcmpi(w1,"char_new")==0){
			char_new=atoi(w2);
		}
		else if(strcmpi(w1,"char_txt")==0){
			strncpy(char_txt,w2,1024);
		}
		else if(strcmpi(w1,"max_connect_user")==0){
			max_connect_user=atoi(w2);
		}
		else if(strcmpi(w1,"autosave_time")==0){
			autosave_interval=atoi(w2)*1000;
			if(autosave_interval <= 0)
				autosave_interval = DEFAULT_AUTOSAVE_INTERVAL;
		}
		else if(strcmpi(w1,"start_point")==0){
			char map[32];
			int x,y;
			if( sscanf(w2,"%[^,],%d,%d",map,&x,&y)<3 )
				continue;
			memcpy(start_point.map,map,16);
			start_point.x=x;
			start_point.y=y;
		}
		else if(strcmpi(w1,"start_zeny")==0){
			start_zeny=atoi(w2);
			if(start_zeny < 0) start_zeny = 0;
		}
		else if(strcmpi(w1,"unknown_char_name")==0){
			strncpy(unknown_char_name,w2,1024);
			unknown_char_name[24] = 0;
		}
		else if(strcmpi(w1,"char_log_filename")==0){
			strncpy(char_log_filename,w2,1024);
		}
		else if(strcmpi(w1,"default_map_type")==0){
			default_map_type=atoi(w2);
		}
		else if(strcmpi(w1,"default_map_name")==0){
			strncpy(default_map_name,w2,16);
		}
		else if(strcmpi(w1,"import")==0){
			char_config_read(w2);
		}
	}
	fclose(fp);

	return 0;
}

static int gm_account_db_final(void *key,void *data,va_list ap)
{
	struct gm_account *p=data;

	free(p);

	return 0;
}
void do_final(void)
{
	int i;

	mmo_char_sync();
	inter_save();
	free(char_dat);
	do_final_inter();
	do_final_int_guild();
	do_final_int_party();
	do_final_int_pet();
	do_final_int_storage();
	if(gm_account_db)
		numdb_final(gm_account_db,gm_account_db_final);

	exit_dbn();

	delete_session(login_fd);
	delete_session(char_fd);
	for(i=0;i<MAX_MAP_SERVERS;i++){
		if(server_fd[i]>0)
			delete_session(server_fd[i]);
	}
	do_final_timer();
}

int do_init(int argc,char **argv)
{
	int i;

	char_config_read((argc<2)? CHAR_CONF_NAME:argv[1]);

	login_ip=inet_addr(login_ip_str);
	char_ip=inet_addr(char_ip_str);

	for(i=0;i<MAX_MAP_SERVERS;i++){
		server_fd[i]=-1;
		server[i].active=0;
	}

	mmo_char_init();
	read_gm_account();
	inter_init((argc>2)?argv[2]:inter_cfgName);	// inter server 初期化

//	set_termfunc(mmo_char_sync);
	set_termfunc(do_final);
	set_defaultparse(parse_char);
	set_sock_destruct(parse_char_disconnect);

	char_fd = make_listen_port(char_port);

	add_timer_func_list(check_connect_login_server,"check_connect_login_server");
	add_timer_func_list(send_users_tologin,"send_users_tologin");
	add_timer_func_list(mmo_char_sync_timer,"mmo_char_sync_timer");

	i=add_timer_interval(gettick()+1000,check_connect_login_server,0,0,10*1000);
	i=add_timer_interval(gettick()+1000,send_users_tologin,0,0,5*1000);
	i=add_timer_interval(gettick()+autosave_interval,mmo_char_sync_timer,0,0,autosave_interval);

	return 0;
}
