
// ステータス計算、状態異常処理
#include <time.h>
#include <ctype.h>
#include <stdio.h>
#include <stdlib.h>
#include <memory.h>

#include "pc.h"
#include "map.h"
#include "pet.h"
#include "mob.h"
#include "clif.h"
#include "timer.h"
#include "skill.h"
#include "itemdb.h"
#include "battle.h"
#include "status.h"
#include "nullpo.h"
#include "script.h"

static int max_weight_base[MAX_PC_CLASS];
static int hp_coefficient[MAX_PC_CLASS];
static int hp_coefficient2[MAX_PC_CLASS];
static int hp_sigma_val[MAX_PC_CLASS][MAX_LEVEL];
static int sp_coefficient[MAX_PC_CLASS];
static int aspd_base[MAX_PC_CLASS][20];
static int refinebonus[5][3];	// 精錬ボーナステーブル(refine_db.txt)
static int percentrefinery[5][10];	// 精錬成功率(refine_db.txt)
static int atkmods[3][20];	// 武器ATKサイズ修正(size_fix.txt)
static char job_bonus[3][MAX_PC_CLASS][MAX_LEVEL];

/*==========================================
 * 精錬ボーナス
 *------------------------------------------
 */
int status_getrefinebonus(int lv,int type)
{
	if(lv >= 0 && lv < 5 && type >= 0 && type < 3)
		return refinebonus[lv][type];
	return 0;
}

/*==========================================
 * 精錬成功率
 *------------------------------------------
 */
int status_percentrefinery(struct map_session_data *sd,struct item *item)
{
	int percent;

	nullpo_retr(0, item);
	percent=percentrefinery[itemdb_wlv(item->nameid)][(int)item->refine];

	percent += pc_checkskill(sd,BS_WEAPONRESEARCH);	// 武器研究スキル所持

	// 確率の有効範囲チェック
	if( percent > 100 ){
		percent = 100;
	}
	if( percent < 0 ){
		percent = 0;
	}

	return percent;
}

/*==========================================
 * パラメータ計算
 * first==0の時、計算対象のパラメータが呼び出し前から
 * 変 化した場合自動でsendするが、
 * 能動的に変化させたパラメータは自前でsendするように
 *------------------------------------------
 */

int status_calc_pc(struct map_session_data* sd,int first)
{
	int b_speed,b_max_hp,b_max_sp,b_hp,b_sp,b_weight,b_max_weight,b_paramb[6],b_parame[6],b_hit,b_flee;
	int b_aspd,b_watk,b_def,b_watk2,b_def2,b_flee2,b_critical,b_attackrange,b_matk1,b_matk2,b_mdef,b_mdef2,b_class;
	int b_base_atk;
	struct skill b_skill[MAX_SKILL];
	int i,bl,index;
	int skill,aspd_rate,wele,wele_,def_ele,refinedef=0;
	int pele=0,pdef_ele=0;
	int str,dstr,dex;
	struct pc_base_job s_class;

	nullpo_retr(0, sd);

	//転生や養子の場合の元の職業を算出する
	s_class = pc_calc_base_job(sd->status.class);

	b_speed = sd->speed;
	b_max_hp = sd->status.max_hp;
	b_max_sp = sd->status.max_sp;
	b_hp = sd->status.hp;
	b_sp = sd->status.sp;
	b_weight = sd->weight;
	b_max_weight = sd->max_weight;
	memcpy(b_paramb,&sd->paramb,sizeof(b_paramb));
	memcpy(b_parame,&sd->paramc,sizeof(b_parame));
	memcpy(b_skill,&sd->status.skill,sizeof(b_skill));
	b_hit = sd->hit;
	b_flee = sd->flee;
	b_aspd = sd->aspd;
	b_watk = sd->watk;
	b_def = sd->def;
	b_watk2 = sd->watk2;
	b_def2 = sd->def2;
	b_flee2 = sd->flee2;
	b_critical = sd->critical;
	b_attackrange = sd->attackrange;
	b_matk1 = sd->matk1;
	b_matk2 = sd->matk2;
	b_mdef = sd->mdef;
	b_mdef2 = sd->mdef2;
	b_class = sd->view_class;
	sd->view_class = sd->status.class;
	b_base_atk = sd->base_atk;

	pc_calc_skilltree(sd);	// スキルツリーの計算

	sd->max_weight = max_weight_base[s_class.job]+sd->status.str*300;
	if(pc_checkskill(sd,KN_RIDING)==1) // ライディングスキル所持
		sd->max_weight +=battle_config.riding_weight; // Weight+α(初期設定は0)
	if( (skill=pc_checkskill(sd,MC_INCCARRY))>0 )	// 所持量増加
		sd->max_weight += skill*1000;

	if(first&1) {
		sd->weight=0;
		for(i=0;i<MAX_INVENTORY;i++){
			if(sd->status.inventory[i].nameid==0 || sd->inventory_data[i] == NULL)
				continue;
			sd->weight += sd->inventory_data[i]->weight*sd->status.inventory[i].amount;
		}
		sd->cart_max_weight=battle_config.max_cart_weight;
		sd->cart_weight=0;
		sd->cart_max_num=MAX_CART;
		sd->cart_num=0;
		for(i=0;i<MAX_CART;i++){
			if(sd->status.cart[i].nameid==0)
				continue;
			sd->cart_weight+=itemdb_weight(sd->status.cart[i].nameid)*sd->status.cart[i].amount;
			sd->cart_num++;
		}
	}

	memset(sd->paramb,0,sizeof(sd->paramb));
	memset(sd->parame,0,sizeof(sd->parame));
	sd->hit = 0;
	sd->flee = 0;
	sd->flee2 = 0;
	sd->critical = 0;
	sd->aspd = 0;
	sd->watk = 0;
	sd->def = 0;
	sd->mdef = 0;
	sd->watk2 = 0;
	sd->def2 = 0;
	sd->mdef2 = 0;
	sd->status.max_hp = 0;
	sd->status.max_sp = 0;
	sd->attackrange = 0;
	sd->attackrange_ = 0;
	sd->atk_ele = 0;
	sd->def_ele = 0;
	sd->star =0;
	sd->overrefine =0;
	sd->matk1 =0;
	sd->matk2 =0;
	sd->speed = DEFAULT_WALK_SPEED ;
	sd->hprate=battle_config.hp_rate;
	sd->sprate=battle_config.sp_rate;
	sd->castrate=100;
	sd->dsprate=100;
	sd->base_atk=0;
	sd->arrow_atk=0;
	sd->arrow_ele=0;
	sd->arrow_hit=0;
	sd->arrow_range=0;
	sd->nhealhp=sd->nhealsp=sd->nshealhp=sd->nshealsp=sd->nsshealhp=sd->nsshealsp=0;
	memset(sd->addele,0,sizeof(sd->addele));
	memset(sd->addrace,0,sizeof(sd->addrace));
	memset(sd->addsize,0,sizeof(sd->addsize));
	memset(sd->addele_,0,sizeof(sd->addele_));
	memset(sd->addrace_,0,sizeof(sd->addrace_));
	memset(sd->addsize_,0,sizeof(sd->addsize_));
	memset(sd->subele,0,sizeof(sd->subele));
	memset(sd->subrace,0,sizeof(sd->subrace));
	memset(sd->addeff,0,sizeof(sd->addeff));
	memset(sd->addeff2,0,sizeof(sd->addeff2));
	memset(sd->reseff,0,sizeof(sd->reseff));
	memset(&sd->special_state,0,sizeof(sd->special_state));
	memset(sd->weapon_coma_ele,0,sizeof(sd->weapon_coma_ele));
	memset(sd->weapon_coma_race,0,sizeof(sd->weapon_coma_race));
	memset(sd->weapon_atk,0,sizeof(sd->weapon_atk));
	memset(sd->weapon_atk_rate,0,sizeof(sd->weapon_atk_rate));

	sd->watk_ = 0;			//二刀流用(仮)
	sd->watk_2 = 0;
	sd->atk_ele_ = 0;
	sd->star_ = 0;
	sd->overrefine_ = 0;

	sd->aspd_rate = 100;
	sd->speed_rate = 100;
	sd->hprecov_rate = 100;
	sd->sprecov_rate = 100;
	sd->critical_def = 0;
	sd->double_rate = 0;
	sd->near_attack_def_rate = sd->long_attack_def_rate = 0;
	sd->atk_rate = sd->matk_rate = 100;
	sd->ignore_def_ele = sd->ignore_def_race = 0;
	sd->ignore_def_ele_ = sd->ignore_def_race_ = 0;
	sd->ignore_mdef_ele = sd->ignore_mdef_race = 0;
	sd->arrow_cri = 0;
	sd->magic_def_rate = sd->misc_def_rate = 0;
	memset(sd->arrow_addele,0,sizeof(sd->arrow_addele));
	memset(sd->arrow_addrace,0,sizeof(sd->arrow_addrace));
	memset(sd->arrow_addsize,0,sizeof(sd->arrow_addsize));
	memset(sd->arrow_addeff,0,sizeof(sd->arrow_addeff));
	memset(sd->arrow_addeff2,0,sizeof(sd->arrow_addeff2));
	memset(sd->magic_addele,0,sizeof(sd->magic_addele));
	memset(sd->magic_addrace,0,sizeof(sd->magic_addrace));
	memset(sd->magic_subrace,0,sizeof(sd->magic_subrace));
	sd->perfect_hit = 0;
	sd->critical_rate = sd->hit_rate = sd->flee_rate = sd->flee2_rate = 100;
	sd->def_rate = sd->def2_rate = sd->mdef_rate = sd->mdef2_rate = 100;
	sd->def_ratio_atk_ele = sd->def_ratio_atk_ele_ = 0;
	sd->def_ratio_atk_race = sd->def_ratio_atk_race_ = 0;
	sd->get_zeny_num = 0;
	sd->add_damage_class_count = sd->add_damage_class_count_ = sd->add_magic_damage_class_count = 0;
	sd->add_def_class_count = sd->add_mdef_class_count = 0;
	sd->monster_drop_item_count = 0;
	memset(sd->add_damage_classrate,0,sizeof(sd->add_damage_classrate));
	memset(sd->add_damage_classrate_,0,sizeof(sd->add_damage_classrate_));
	memset(sd->add_magic_damage_classrate,0,sizeof(sd->add_magic_damage_classrate));
	memset(sd->add_def_classrate,0,sizeof(sd->add_def_classrate));
	memset(sd->add_mdef_classrate,0,sizeof(sd->add_mdef_classrate));
	memset(sd->monster_drop_race,0,sizeof(sd->monster_drop_race));
	memset(sd->monster_drop_itemrate,0,sizeof(sd->monster_drop_itemrate));
	sd->speed_add_rate = sd->aspd_add_rate = 100;
	sd->double_add_rate = sd->perfect_hit_add = sd->get_zeny_add_num = 0;
	sd->splash_range = sd->splash_add_range = 0;
	sd->autospell_id = sd->autospell_lv = sd->autospell_rate = 0;
	sd->hp_drain_rate = sd->hp_drain_per = sd->sp_drain_rate = sd->sp_drain_per = 0;
	sd->hp_drain_rate_ = sd->hp_drain_per_ = sd->sp_drain_rate_ = sd->sp_drain_per_ = 0;
	sd->hp_drain_value = sd->hp_drain_value_ = sd->sp_drain_value = sd->sp_drain_value_ = 0;
	sd->short_weapon_damage_return = sd->long_weapon_damage_return = 0;
	sd->break_weapon_rate = sd->break_armor_rate = 0;
	sd->add_steal_rate = 0;
	sd->unbreakable_equip = 0;

	for(i=0;i<10;i++) {
		index = sd->equip_index[i];
		if(index < 0)
			continue;
		if(i == 9 && sd->equip_index[8] == index)
			continue;
		if(i == 5 && sd->equip_index[4] == index)
			continue;
		if(i == 6 && (sd->equip_index[5] == index || sd->equip_index[4] == index))
			continue;
		
		if(sd->inventory_data[index]) {
			if(sd->inventory_data[index]->type == 4) {
				if(sd->status.inventory[index].card[0]!=0x00ff && sd->status.inventory[index].card[0]!=0x00fe && sd->status.inventory[index].card[0]!=(short)0xff00) {
					int j;
					for(j=0;j<sd->inventory_data[index]->slot;j++){	// カード
						int c=sd->status.inventory[index].card[j];
						if(c>0){
							if(i == 8 && sd->status.inventory[index].equip == 0x20)
								sd->state.lr_flag = 1;
							run_script(itemdb_equipscript(c),0,sd->bl.id,0);
							sd->state.lr_flag = 0;
						}
					}
				}
			}
			else if(sd->inventory_data[index]->type==5){ // 防具
				if(sd->status.inventory[index].card[0]!=0x00ff && sd->status.inventory[index].card[0]!=0x00fe && sd->status.inventory[index].card[0]!=(short)0xff00) {
					int j;
					for(j=0;j<sd->inventory_data[index]->slot;j++){	// カード
						int c=sd->status.inventory[index].card[j];
						if(c>0)
							run_script(itemdb_equipscript(c),0,sd->bl.id,0);
					}
				}
			}
		}
	}
	wele = sd->atk_ele;
	wele_ = sd->atk_ele_;
	def_ele = sd->def_ele;
	if(battle_config.pet_status_support) {
		if(sd->status.pet_id > 0 && sd->petDB && sd->pet.intimate > 0)
			run_script(sd->petDB->script,0,sd->bl.id,0);
		pele = sd->atk_ele;
		pdef_ele = sd->def_ele;
		sd->atk_ele = sd->def_ele = 0;
	}
	memcpy(sd->paramcard,sd->parame,sizeof(sd->paramcard));

	// 装備品によるステータス変化はここで実行
	for(i=0;i<10;i++) {
		index = sd->equip_index[i];
		if(index < 0)
			continue;
		if(i == 9 && sd->equip_index[8] == index)
			continue;
		if(i == 5 && sd->equip_index[4] == index)
			continue;
		if(i == 6 && (sd->equip_index[5] == index || sd->equip_index[4] == index))
			continue;
		if(sd->inventory_data[index]) {
			sd->def += sd->inventory_data[index]->def;
			if(sd->inventory_data[index]->type == 4) {
				int r,wlv = sd->inventory_data[index]->wlv;
				if(i == 8 && sd->status.inventory[index].equip == 0x20) {
					//二刀流用データ入力
					sd->watk_ += sd->inventory_data[index]->atk;
					sd->watk_2 = (r=sd->status.inventory[index].refine)*	// 精錬攻撃力
						refinebonus[wlv][0];
					if( (r-=refinebonus[wlv][2])>0 )	// 過剰精錬ボーナス
						sd->overrefine_ = r*refinebonus[wlv][1];

					if(sd->status.inventory[index].card[0]==0x00ff){	// 製造武器
						sd->star_ = (sd->status.inventory[index].card[1]>>8);	// 星のかけら
						wele_= (sd->status.inventory[index].card[1]&0x0f);	// 属 性
					}
					sd->attackrange_ += sd->inventory_data[index]->range;
					sd->state.lr_flag = 1;
					run_script(sd->inventory_data[index]->equip_script,0,sd->bl.id,0);
					sd->state.lr_flag = 0;
				}
				else {	//二刀流武器以外
					sd->watk += sd->inventory_data[index]->atk;
					sd->watk2 += (r=sd->status.inventory[index].refine)*	// 精錬攻撃力
						refinebonus[wlv][0];
					if( (r-=refinebonus[wlv][2])>0 )	// 過剰精錬ボーナス
						sd->overrefine += r*refinebonus[wlv][1];

					if(sd->status.inventory[index].card[0]==0x00ff){	// 製造武器
						sd->star += (sd->status.inventory[index].card[1]>>8);	// 星のかけら
						wele = (sd->status.inventory[index].card[1]&0x0f);	// 属 性
					}
					sd->attackrange += sd->inventory_data[index]->range;
					run_script(sd->inventory_data[index]->equip_script,0,sd->bl.id,0);
				}
			}
			else if(sd->inventory_data[index]->type == 5) {
				sd->watk += sd->inventory_data[index]->atk;
				refinedef += sd->status.inventory[index].refine*refinebonus[0][0];
				run_script(sd->inventory_data[index]->equip_script,0,sd->bl.id,0);
			}
		}
	}

	if(sd->equip_index[10] >= 0){ // 矢
		index = sd->equip_index[10];
		if(sd->inventory_data[index]){		//まだ属性が入っていない
			sd->state.lr_flag = 2;
			run_script(sd->inventory_data[index]->equip_script,0,sd->bl.id,0);
			sd->state.lr_flag = 0;
			sd->arrow_atk += sd->inventory_data[index]->atk;
		}
	}
	sd->def += (refinedef+50)/100;

	if(sd->attackrange < 1) sd->attackrange = 1;
	if(sd->attackrange_ < 1) sd->attackrange_ = 1;
	if(sd->attackrange < sd->attackrange_)
		sd->attackrange = sd->attackrange_;
	if(sd->status.weapon == 11)
		sd->attackrange += sd->arrow_range;
	if(wele > 0)
		sd->atk_ele = wele;
	if(wele_ > 0)
		sd->atk_ele_ = wele_;
	if(def_ele > 0)
		sd->def_ele = def_ele;
	if(battle_config.pet_status_support) {
		if(pele > 0 && !sd->atk_ele)
			sd->atk_ele = pele;
		if(pdef_ele > 0 && !sd->def_ele)
			sd->def_ele = pdef_ele;
	}
	sd->double_rate += sd->double_add_rate;
	sd->perfect_hit += sd->perfect_hit_add;
	sd->get_zeny_num += sd->get_zeny_add_num;
	sd->splash_range += sd->splash_add_range;
	if(sd->speed_add_rate != 100)
		sd->speed_rate += sd->speed_add_rate - 100;
	if(sd->aspd_add_rate != 100)
		sd->aspd_rate += sd->aspd_add_rate - 100;

	// 武器ATKサイズ補正 (右手)
	sd->atkmods[0] = atkmods[0][sd->weapontype1];
	sd->atkmods[1] = atkmods[1][sd->weapontype1];
	sd->atkmods[2] = atkmods[2][sd->weapontype1];
	//武器ATKサイズ補正 (左手)
	sd->atkmods_[0] = atkmods[0][sd->weapontype2];
	sd->atkmods_[1] = atkmods[1][sd->weapontype2];
	sd->atkmods_[2] = atkmods[2][sd->weapontype2];

	// jobボーナス分
	for(i=0;i<sd->status.job_level && i<MAX_LEVEL;i++){
		if(job_bonus[s_class.upper][s_class.job][i])
			sd->paramb[job_bonus[s_class.upper][s_class.job][i]-1]++;
	}

	if( (skill=pc_checkskill(sd,AC_OWL))>0 )	// ふくろうの目
		sd->paramb[4] += skill;

	// ステータス変化による基本パラメータ補正
	if(sd->sc_count){
		if(sd->sc_data[SC_CONCENTRATE].timer!=-1 && sd->sc_data[SC_QUAGMIRE].timer == -1){	// 集中力向上
			sd->paramb[1]+= (sd->status.agi+sd->paramb[1]+sd->parame[1]-sd->paramcard[1])*(2+sd->sc_data[SC_CONCENTRATE].val1)/100;
			sd->paramb[4]+= (sd->status.dex+sd->paramb[4]+sd->parame[4]-sd->paramcard[4])*(2+sd->sc_data[SC_CONCENTRATE].val1)/100;
		}
		if(sd->sc_data[SC_INCREASEAGI].timer!=-1 && sd->sc_data[SC_QUAGMIRE].timer == -1 && sd->sc_data[SC_DONTFORGETME].timer == -1){	// 速度増加
			sd->paramb[1]+= 2+sd->sc_data[SC_INCREASEAGI].val1;
			sd->speed -= sd->speed *25/100;
		}
		if(sd->sc_data[SC_DECREASEAGI].timer!=-1)	// 速度減少(agiはbattle.cで)
			sd->speed = sd->speed *125/100;
		if(sd->sc_data[SC_BLESSING].timer!=-1){	// ブレッシング
			sd->paramb[0]+= sd->sc_data[SC_BLESSING].val1;
			sd->paramb[3]+= sd->sc_data[SC_BLESSING].val1;
			sd->paramb[4]+= sd->sc_data[SC_BLESSING].val1;
		}
		if(sd->sc_data[SC_GLORIA].timer!=-1)	// グロリア
			sd->paramb[5]+= 30;
		if(sd->sc_data[SC_LOUD].timer!=-1 && sd->sc_data[SC_QUAGMIRE].timer == -1)	// ラウドボイス
			sd->paramb[0]+= 4;
		if(sd->sc_data[SC_QUAGMIRE].timer!=-1){	// クァグマイア
			sd->speed = sd->speed*3/2;
			sd->paramb[1]-=(sd->status.agi+sd->paramb[1]+sd->parame[1])/2;
			sd->paramb[4]-=(sd->status.dex+sd->paramb[4]+sd->parame[4])/2;
		}
		if(sd->sc_data[SC_TRUESIGHT].timer!=-1){	// トゥルーサイト
			sd->paramb[0]+= 5;
			sd->paramb[1]+= 5;
			sd->paramb[2]+= 5;
			sd->paramb[3]+= 5;
			sd->paramb[4]+= 5;
			sd->paramb[5]+= 5;
		}
	}

	//1度も死んでないJob70スパノビに+10
	if(s_class.job == 23 && sd->die_counter == 0 && sd->status.job_level >= 70){
		sd->paramb[0]+= 15;
		sd->paramb[1]+= 15;
		sd->paramb[2]+= 15;
		sd->paramb[3]+= 15;
		sd->paramb[4]+= 15;
		sd->paramb[5]+= 15;
	}
	sd->paramc[0]=sd->status.str+sd->paramb[0]+sd->parame[0];
	sd->paramc[1]=sd->status.agi+sd->paramb[1]+sd->parame[1];
	sd->paramc[2]=sd->status.vit+sd->paramb[2]+sd->parame[2];
	sd->paramc[3]=sd->status.int_+sd->paramb[3]+sd->parame[3];
	sd->paramc[4]=sd->status.dex+sd->paramb[4]+sd->parame[4];
	sd->paramc[5]=sd->status.luk+sd->paramb[5]+sd->parame[5];
	for(i=0;i<6;i++)
		if(sd->paramc[i] < 0) sd->paramc[i] = 0;

	if(sd->status.weapon == 11 || sd->status.weapon == 13 || sd->status.weapon == 14) {
		str = sd->paramc[4];
		dex = sd->paramc[0];
	}
	else {
		str = sd->paramc[0];
		dex = sd->paramc[4];
	}
	dstr = str/10;
	sd->base_atk += str + dstr*dstr + dex/5 + sd->paramc[5]/5;
	sd->matk1 += sd->paramc[3]+(sd->paramc[3]/5)*(sd->paramc[3]/5);
	sd->matk2 += sd->paramc[3]+(sd->paramc[3]/7)*(sd->paramc[3]/7);
	if(sd->matk1 < sd->matk2) {
		int temp = sd->matk2;
		sd->matk2 = sd->matk1;
		sd->matk1 = temp;
	}
	sd->hit += sd->paramc[4] + sd->status.base_level;
	sd->flee += sd->paramc[1] + sd->status.base_level;
	sd->def2 += sd->paramc[2];
	sd->mdef2 += sd->paramc[3];
	sd->flee2 += sd->paramc[5]+10;
	sd->critical += (sd->paramc[5]*3)+10;

	if(sd->base_atk < 1)
		sd->base_atk = 1;
	if(sd->critical_rate != 100)
		sd->critical = (sd->critical*sd->critical_rate)/100;
	if(sd->critical < 10) sd->critical = 10;
	if(sd->hit_rate != 100)
		sd->hit = (sd->hit*sd->hit_rate)/100;
	if(sd->hit < 1) sd->hit = 1;
	if(sd->flee_rate != 100)
		sd->flee = (sd->flee*sd->flee_rate)/100;
	if(sd->flee < 1) sd->flee = 1;
	if(sd->flee2_rate != 100)
		sd->flee2 = (sd->flee2*sd->flee2_rate)/100;
	if(sd->flee2 < 10) sd->flee2 = 10;
	if(sd->def_rate != 100)
		sd->def = (sd->def*sd->def_rate)/100;
	if(sd->def < 0) sd->def = 0;
	if(sd->def2_rate != 100)
		sd->def2 = (sd->def2*sd->def2_rate)/100;
	if(sd->def2 < 1) sd->def2 = 1;
	if(sd->mdef_rate != 100)
		sd->mdef = (sd->mdef*sd->mdef_rate)/100;
	if(sd->mdef < 0) sd->mdef = 0;
	if(sd->mdef2_rate != 100)
		sd->mdef2 = (sd->mdef2*sd->mdef2_rate)/100;
	if(sd->mdef2 < 1) sd->mdef2 = 1;

	// 二刀流 ASPD 修正
	if (sd->status.weapon <= 16)
		sd->aspd += aspd_base[s_class.job][sd->status.weapon]-(sd->paramc[1]*4+sd->paramc[4])*aspd_base[s_class.job][sd->status.weapon]/1000;
	else
		sd->aspd += (
			(aspd_base[s_class.job][sd->weapontype1]-(sd->paramc[1]*4+sd->paramc[4])*aspd_base[s_class.job][sd->weapontype1]/1000) +
			(aspd_base[s_class.job][sd->weapontype2]-(sd->paramc[1]*4+sd->paramc[4])*aspd_base[s_class.job][sd->weapontype2]/1000)
			) * 140 / 200;

	aspd_rate = sd->aspd_rate;

	//攻撃速度増加

	if( (skill=pc_checkskill(sd,AC_VULTURE))>0){	// ワシの目
		sd->hit += skill;
		if(sd->status.weapon == 11)
			sd->attackrange += skill;
	}

	if( (skill=pc_checkskill(sd,BS_WEAPONRESEARCH))>0)	// 武器研究の命中率増加
		sd->hit += skill*2;
	if(sd->status.option&2 && (skill = pc_checkskill(sd,RG_TUNNELDRIVE))>0 )	// トンネルドライブ	// トンネルドライブ
		sd->speed += (short)(1.2*DEFAULT_WALK_SPEED - skill*9);
	if (pc_iscarton(sd) && (skill=pc_checkskill(sd,MC_PUSHCART))>0)	// カートによる速度低下
		sd->speed += (short)((10-skill) * (DEFAULT_WALK_SPEED * 0.1));
	else if (pc_isriding(sd))	// ペコペコ乗りによる速度増加
		sd->speed -= (short)(0.25 * DEFAULT_WALK_SPEED);
	if(sd->sc_count){
		if(sd->sc_data[SC_WINDWALK].timer!=-1) 	//ウィンドウォーク時はLv*2%減算
			sd->speed -= sd->speed *(sd->sc_data[SC_WINDWALK].val1*2)/100;
		if(sd->sc_data[SC_CARTBOOST].timer!=-1)	// カートブースト
			sd->speed -= (DEFAULT_WALK_SPEED * 20)/100;
		if(sd->sc_data[SC_BERSERK].timer!=-1)	//バーサーク中はIAと同じぐらい速い？
			sd->speed -= sd->speed *25/100;
		if(sd->sc_data[SC_WEDDING].timer!=-1)	//結婚中は歩くのが遅い
			sd->speed = 2*DEFAULT_WALK_SPEED;
	}

	if((skill=pc_checkskill(sd,CR_TRUST))>0) { // フェイス
		sd->status.max_hp += skill*200;
		sd->subele[6] += skill*5;
	}
	if((skill=pc_checkskill(sd,BS_SKINTEMPER))>0)
		sd->subele[3] += skill*5;

	bl=sd->status.base_level;

	//最大HP計算
	//転生2次職の場合最大HP25%UP
	if(b_class >= PC_CLASS_BASE2 && b_class < PC_CLASS_BASE3 && s_class.job > 6 && s_class.job < 23)
		sd->status.max_hp += ((3500 + bl*hp_coefficient2[s_class.job] + hp_sigma_val[s_class.job][(bl > 0)? bl-1:0])/100 * (100 + sd->paramc[2])/100 + (sd->parame[2] - sd->paramcard[2])) * 125/100;
		else sd->status.max_hp += (3500 + bl*hp_coefficient2[s_class.job] + hp_sigma_val[s_class.job][(bl > 0)? bl-1:0])/100 * (100 + sd->paramc[2])/100 + (sd->parame[2] - sd->paramcard[2]);

	if(sd->hprate!=100)
		sd->status.max_hp = sd->status.max_hp*sd->hprate/100;

	if(sd->sc_data && sd->sc_data[SC_BERSERK].timer!=-1){	// バーサーク
		sd->status.max_hp = sd->status.max_hp * 3;
	}
	if(s_class.job == 23 && sd->status.base_level >= 99){
		sd->status.max_hp = sd->status.max_hp + 2000;
	}

	if(sd->status.max_hp < 0 || sd->status.max_hp > battle_config.max_hp)
		sd->status.max_hp = battle_config.max_hp;

	// 最大SP計算
	//転生2次職の場合最大SP25%UP
	if(b_class >= PC_CLASS_BASE2 && b_class < PC_CLASS_BASE3 && s_class.job > 6 && s_class.job < 23)
		sd->status.max_sp += (((sp_coefficient[s_class.job] * bl) + 1000)/100 * (100 + sd->paramc[3])/100 + (sd->parame[3] - sd->paramcard[3])) * 125/100;
		else sd->status.max_sp += ((sp_coefficient[s_class.job] * bl) + 1000)/100 * (100 + sd->paramc[3])/100 + (sd->parame[3] - sd->paramcard[3]);

	if(sd->sprate!=100)
		sd->status.max_sp = sd->status.max_sp*sd->sprate/100;

	if((skill=pc_checkskill(sd,HP_MEDITATIO))>0) // メディテイティオ 
		sd->status.max_sp += sd->status.max_sp*skill/100;
	if((skill=pc_checkskill(sd,HW_SOULDRAIN))>0) /* ソウルドレイン */
		sd->status.max_sp += sd->status.max_sp*2*skill/100;

	if(sd->status.max_sp < 0 || sd->status.max_sp > battle_config.max_sp)
		sd->status.max_sp = battle_config.max_sp;

	//自然回復HP
	sd->nhealhp = 1 + (sd->paramc[2]/5) + (sd->status.max_hp/200);
	if((skill=pc_checkskill(sd,SM_RECOVERY)) > 0) {	/* HP回復力向上 */
		sd->nshealhp = skill*5 + (sd->status.max_hp*skill/500);
		if(sd->nshealhp > 0x7fff) sd->nshealhp = 0x7fff;
	}
	//自然回復SP
	sd->nhealsp = 1 + (sd->paramc[3]/6) + (sd->status.max_sp/100);
	if(sd->paramc[3] >= 120)
		sd->nhealsp += ((sd->paramc[3]-120)>>1) + 4;
	if((skill=pc_checkskill(sd,MG_SRECOVERY)) > 0) { /* SP回復力向上 */
		sd->nshealsp = skill*3 + (sd->status.max_sp*skill/500);
		if(sd->nshealsp > 0x7fff) sd->nshealsp = 0x7fff;
	}
	
	if((skill = pc_checkskill(sd,MO_SPIRITSRECOVERY)) > 0) {
		sd->nsshealhp = skill*4 + (sd->status.max_hp*skill/500);
		sd->nsshealsp = skill*2 + (sd->status.max_sp*skill/500);
		if(sd->nsshealhp > 0x7fff) sd->nsshealhp = 0x7fff;
		if(sd->nsshealsp > 0x7fff) sd->nsshealsp = 0x7fff;
	}
	if(sd->hprecov_rate != 100) {
		sd->nhealhp = sd->nhealhp*sd->hprecov_rate/100;
		if(sd->nhealhp < 1) sd->nhealhp = 1;
	}
	if(sd->sprecov_rate != 100) {
		sd->nhealsp = sd->nhealsp*sd->sprecov_rate/100;
		if(sd->nhealsp < 1) sd->nhealsp = 1;
	}
	if((skill=pc_checkskill(sd,HP_MEDITATIO)) > 0) { // メディテイティオはSPRではなく自然回復にかかる
		sd->nhealsp += 3*skill*(sd->status.max_sp)/100;
		if(sd->nhealsp > 0x7fff) sd->nhealsp = 0x7fff;
	}

	// 種族耐性（これでいいの？ ディバインプロテクションと同じ処理がいるかも）
	if( (skill=pc_checkskill(sd,SA_DRAGONOLOGY))>0 ){	// ドラゴノロジー
		skill = skill*4;
		sd->addrace[9]+=skill;
		sd->addrace_[9]+=skill;
		sd->subrace[9]+=skill;
	}

	//Flee上昇
	if( (skill=pc_checkskill(sd,TF_MISS))>0 )	// 回避率増加
		sd->flee += skill*3;
	if( (skill=pc_checkskill(sd,MO_DODGE))>0 )	// 見切り
		sd->flee += (skill*3)>>1;

	// スキルやステータス異常による残りのパラメータ補正
	if(sd->sc_count){
		// ATK/DEF変化形
		if(sd->sc_data[SC_ANGELUS].timer!=-1)	// エンジェラス
			sd->def2 = sd->def2*(110+5*sd->sc_data[SC_ANGELUS].val1)/100;
		if(sd->sc_data[SC_IMPOSITIO].timer!=-1)	{// インポシティオマヌス
			sd->watk += sd->sc_data[SC_IMPOSITIO].val1*5;
			index = sd->equip_index[8];
			if(index >= 0 && sd->inventory_data[index] && sd->inventory_data[index]->type == 4)
				sd->watk_ += sd->sc_data[SC_IMPOSITIO].val1*5;
		}
		if(sd->sc_data[SC_PROVOKE].timer!=-1){	// プロボック
			sd->def2 = sd->def2*(100-6*sd->sc_data[SC_PROVOKE].val1)/100;
			sd->base_atk = sd->base_atk*(100+2*sd->sc_data[SC_PROVOKE].val1)/100;
			sd->watk = sd->watk*(100+2*sd->sc_data[SC_PROVOKE].val1)/100;
			index = sd->equip_index[8];
			if(index >= 0 && sd->inventory_data[index] && sd->inventory_data[index]->type == 4)
				sd->watk_ = sd->watk_*(100+2*sd->sc_data[SC_PROVOKE].val1)/100;
		}
		if(sd->sc_data[SC_POISON].timer!=-1)	// 毒状態
			sd->def2 = sd->def2*75/100;
		if(sd->sc_data[SC_DRUMBATTLE].timer!=-1){	// 戦太鼓の響き
			sd->watk += sd->sc_data[SC_DRUMBATTLE].val2;
			sd->def  += sd->sc_data[SC_DRUMBATTLE].val3;
			index = sd->equip_index[8];
			if(index >= 0 && sd->inventory_data[index] && sd->inventory_data[index]->type == 4)
				sd->watk_ += sd->sc_data[SC_DRUMBATTLE].val2;
		}
		if(sd->sc_data[SC_NIBELUNGEN].timer!=-1) {	// ニーベルングの指輪
			index = sd->equip_index[9];
			if(index >= 0 && sd->inventory_data[index] && sd->inventory_data[index]->wlv >= 4)
				sd->watk += sd->sc_data[SC_NIBELUNGEN].val2;
			index = sd->equip_index[8];
			if(index >= 0 && sd->inventory_data[index] && sd->inventory_data[index]->wlv >= 4)
				sd->watk_ += sd->sc_data[SC_NIBELUNGEN].val2;
		}

		if(sd->sc_data[SC_VOLCANO].timer!=-1 && sd->def_ele==3){	// ボルケーノ
			sd->watk += sd->sc_data[SC_VIOLENTGALE].val3;
		}

		if(sd->sc_data[SC_SIGNUMCRUCIS].timer!=-1)
			sd->def = sd->def * (100 - sd->sc_data[SC_SIGNUMCRUCIS].val2)/100;
		if(sd->sc_data[SC_ETERNALCHAOS].timer!=-1)	// エターナルカオス
			sd->def=0;

		if(sd->sc_data[SC_CONCENTRATION].timer!=-1){ //コンセントレーション
			sd->watk = sd->watk * (100 + 5*sd->sc_data[SC_CONCENTRATION].val1)/100;
			index = sd->equip_index[8];
			if(index >= 0 && sd->inventory_data[index] && sd->inventory_data[index]->type == 4)
				sd->watk_ = sd->watk * (100 + 5*sd->sc_data[SC_CONCENTRATION].val1)/100;
			sd->def = sd->def * (100 - 5*sd->sc_data[SC_CONCENTRATION].val1)/100;
		}

		if(sd->sc_data[SC_INCATK].timer!=-1)	//item 682用
			sd->watk += sd->sc_data[SC_INCATK].val1;
		if(sd->sc_data[SC_INCMATK].timer!=-1){	//item 683用
			sd->matk1 += sd->sc_data[SC_INCMATK].val1;
			sd->matk2 += sd->sc_data[SC_INCMATK].val1;
		}

		// ASPD/移動速度変化系
		if(sd->sc_data[SC_TWOHANDQUICKEN].timer != -1 && sd->sc_data[SC_QUAGMIRE].timer == -1 && sd->sc_data[SC_DONTFORGETME].timer == -1)	// 2HQ
			aspd_rate -= 30;
		if(sd->sc_data[SC_ADRENALINE].timer != -1 && sd->sc_data[SC_TWOHANDQUICKEN].timer == -1 &&
			sd->sc_data[SC_QUAGMIRE].timer == -1 && sd->sc_data[SC_DONTFORGETME].timer == -1) {	// アドレナリンラッシュ
			if(sd->sc_data[SC_ADRENALINE].val2 || !battle_config.party_skill_penaly)
				aspd_rate -= 30;
			else
				aspd_rate -= 25;
		}
		if(sd->sc_data[SC_SPEARSQUICKEN].timer != -1 && sd->sc_data[SC_ADRENALINE].timer == -1 &&
			sd->sc_data[SC_TWOHANDQUICKEN].timer == -1 && sd->sc_data[SC_QUAGMIRE].timer == -1 && sd->sc_data[SC_DONTFORGETME].timer == -1)	// スピアクィッケン
			aspd_rate -= sd->sc_data[SC_SPEARSQUICKEN].val2;
		if(sd->sc_data[SC_ASSNCROS].timer!=-1 && // 夕陽のアサシンクロス
			sd->sc_data[SC_TWOHANDQUICKEN].timer==-1 && sd->sc_data[SC_ADRENALINE].timer==-1 && sd->sc_data[SC_SPEARSQUICKEN].timer==-1 &&
			sd->sc_data[SC_DONTFORGETME].timer == -1)
				aspd_rate -= 5+sd->sc_data[SC_ASSNCROS].val1+sd->sc_data[SC_ASSNCROS].val2+sd->sc_data[SC_ASSNCROS].val3;
		if(sd->sc_data[SC_DONTFORGETME].timer!=-1){		// 私を忘れないで
			aspd_rate += sd->sc_data[SC_DONTFORGETME].val1*3 + sd->sc_data[SC_DONTFORGETME].val2 + (sd->sc_data[SC_DONTFORGETME].val3>>16);
			sd->speed= sd->speed*(100+sd->sc_data[SC_DONTFORGETME].val1*2 + sd->sc_data[SC_DONTFORGETME].val2 + (sd->sc_data[SC_DONTFORGETME].val3&0xffff))/100;
		}
		if(	sd->sc_data[i=SC_SPEEDPOTION2].timer!=-1 ||
			sd->sc_data[i=SC_SPEEDPOTION1].timer!=-1 ||
			sd->sc_data[i=SC_SPEEDPOTION0].timer!=-1)	// 増 速ポーション
			aspd_rate -= sd->sc_data[i].val2;

		// HIT/FLEE変化系
		if(sd->sc_data[SC_WHISTLE].timer!=-1){  // 口笛
			sd->flee += sd->flee * (sd->sc_data[SC_WHISTLE].val1
					+sd->sc_data[SC_WHISTLE].val2+(sd->sc_data[SC_WHISTLE].val3>>16))/100;
			sd->flee2+= (sd->sc_data[SC_WHISTLE].val1+sd->sc_data[SC_WHISTLE].val2+(sd->sc_data[SC_WHISTLE].val3&0xffff)) * 10;
		}
		if(sd->sc_data[SC_HUMMING].timer!=-1)  // ハミング
			sd->hit += (sd->sc_data[SC_HUMMING].val1*2+sd->sc_data[SC_HUMMING].val2
					+sd->sc_data[SC_HUMMING].val3) * sd->hit/100;
		if(sd->sc_data[SC_VIOLENTGALE].timer!=-1 && sd->def_ele==4){	// バイオレントゲイル
			sd->flee += sd->flee*sd->sc_data[SC_VIOLENTGALE].val3/100;
		}
		if(sd->sc_data[SC_BLIND].timer!=-1){	// 暗黒
			sd->hit -= sd->hit*25/100;
			sd->flee -= sd->flee*25/100;
		}
		if(sd->sc_data[SC_WINDWALK].timer!=-1) // ウィンドウォーク
			sd->flee += sd->flee*(sd->sc_data[SC_WINDWALK].val2)/100;
		if(sd->sc_data[SC_SPIDERWEB].timer!=-1) //スパイダーウェブ
			sd->flee -= sd->flee*50/100;
		if(sd->sc_data[SC_TRUESIGHT].timer!=-1) //トゥルーサイト
			sd->hit += 3*(sd->sc_data[SC_TRUESIGHT].val1);
		if(sd->sc_data[SC_CONCENTRATION].timer!=-1) //コンセントレーション
			sd->hit += (sd->hit*(10*(sd->sc_data[SC_CONCENTRATION].val1)))/100;

		// 耐性
		if(sd->sc_data[SC_SIEGFRIED].timer!=-1){  // 不死身のジークフリード
			sd->subele[1] += sd->sc_data[SC_SIEGFRIED].val2;	// 水
			sd->subele[3] += sd->sc_data[SC_SIEGFRIED].val2;	// 火
		}
		if(sd->sc_data[SC_PROVIDENCE].timer!=-1){	// プロヴィデンス
			sd->subele[6] += sd->sc_data[SC_PROVIDENCE].val2;	// 対 聖属性
			sd->subrace[6] += sd->sc_data[SC_PROVIDENCE].val2;	// 対 悪魔
		}

		// その他
		if(sd->sc_data[SC_APPLEIDUN].timer!=-1){	// イドゥンの林檎
			sd->status.max_hp += ((5+sd->sc_data[SC_APPLEIDUN].val1*2+((sd->sc_data[SC_APPLEIDUN].val2+1)>>1)
						+sd->sc_data[SC_APPLEIDUN].val3/10) * sd->status.max_hp)/100;
			if(sd->status.max_hp < 0 || sd->status.max_hp > battle_config.max_hp)
				sd->status.max_hp = battle_config.max_hp;
		}
		if(sd->sc_data[SC_DELUGE].timer!=-1 && sd->def_ele==1){	// デリュージ
			sd->status.max_hp += sd->status.max_hp*sd->sc_data[SC_DELUGE].val3/100;
			if(sd->status.max_hp < 0 || sd->status.max_hp > battle_config.max_hp)
				sd->status.max_hp = battle_config.max_hp;
		}
		if(sd->sc_data[SC_SERVICE4U].timer!=-1) {	// サービスフォーユー
			sd->status.max_sp += sd->status.max_sp*(10+sd->sc_data[SC_SERVICE4U].val1+sd->sc_data[SC_SERVICE4U].val2
						+sd->sc_data[SC_SERVICE4U].val3)/100;
			if(sd->status.max_sp < 0 || sd->status.max_sp > battle_config.max_sp)
				sd->status.max_sp = battle_config.max_sp;
			sd->dsprate-=(10+sd->sc_data[SC_SERVICE4U].val1*3+sd->sc_data[SC_SERVICE4U].val2
					+sd->sc_data[SC_SERVICE4U].val3);
			if(sd->dsprate<0)sd->dsprate=0;
		}

		if(sd->sc_data[SC_FORTUNE].timer!=-1)	// 幸運のキス
			sd->critical += (10+sd->sc_data[SC_FORTUNE].val1+sd->sc_data[SC_FORTUNE].val2
						+sd->sc_data[SC_FORTUNE].val3)*10;

		if(sd->sc_data[SC_EXPLOSIONSPIRITS].timer!=-1){	// 爆裂波動
			if(s_class.job==23)
				sd->critical += sd->sc_data[SC_EXPLOSIONSPIRITS].val1*100;
			else
				sd->critical += sd->sc_data[SC_EXPLOSIONSPIRITS].val2;
		}

		if(sd->sc_data[SC_STEELBODY].timer!=-1){	// 金剛
			sd->def = 90;
			sd->mdef = 90;
			aspd_rate += 25;
			sd->speed = (sd->speed * 125) / 100;
		}
		if(sd->sc_data[SC_DEFENDER].timer != -1) {
			sd->aspd += (550 - sd->sc_data[SC_DEFENDER].val1*50);
			sd->speed = (sd->speed * (155 - sd->sc_data[SC_DEFENDER].val1*5)) / 100;
		}
		if(sd->sc_data[SC_ENCPOISON].timer != -1)
			sd->addeff[4] += sd->sc_data[SC_ENCPOISON].val2;

		if( sd->sc_data[SC_DANCING].timer!=-1 )		// 演奏/ダンス使用中
			sd->speed*=4;
		if(sd->sc_data[SC_CURSE].timer!=-1)
			sd->speed += 450;

		if(sd->sc_data[SC_TRUESIGHT].timer!=-1) //トゥルーサイト
			sd->critical += sd->critical*(sd->sc_data[SC_TRUESIGHT].val1)/100;

/*		if(sd->sc_data[SC_VOLCANO].timer!=-1)	// エンチャントポイズン(属性はbattle.cで)
			sd->addeff[2]+=sd->sc_data[SC_VOLCANO].val2;//% of granting
		if(sd->sc_data[SC_DELUGE].timer!=-1)	// エンチャントポイズン(属性はbattle.cで)
			sd->addeff[0]+=sd->sc_data[SC_DELUGE].val2;//% of granting
		*/
	}

	if(sd->speed_rate != 100)
		sd->speed = sd->speed*sd->speed_rate/100;
	if(sd->speed < 1) sd->speed = 1;
	if(aspd_rate != 100)
		sd->aspd = sd->aspd*aspd_rate/100;
	if(pc_isriding(sd))							// 騎兵修練
		sd->aspd = sd->aspd*(100 + 10*(5 - pc_checkskill(sd,KN_CAVALIERMASTERY)))/ 100;
	if(sd->aspd < battle_config.max_aspd) sd->aspd = battle_config.max_aspd;
	sd->amotion = sd->aspd;
	sd->dmotion = 800-sd->paramc[1]*4;
	if(sd->dmotion<400)
		sd->dmotion = 400;
	if(sd->skilltimer != -1 && (skill = pc_checkskill(sd,SA_FREECAST)) > 0) {
		sd->prev_speed = sd->speed;
		sd->speed = sd->speed*(175 - skill*5)/100;
	}

	if(sd->status.hp>sd->status.max_hp)
		sd->status.hp=sd->status.max_hp;
	if(sd->status.sp>sd->status.max_sp)
		sd->status.sp=sd->status.max_sp;

	if(first&4)
		return 0;
	if(first&3) {
		clif_updatestatus(sd,SP_SPEED);
		clif_updatestatus(sd,SP_MAXHP);
		clif_updatestatus(sd,SP_MAXSP);
		if(first&1) {
			clif_updatestatus(sd,SP_HP);
			clif_updatestatus(sd,SP_SP);
		}
		return 0;
	}

	if(b_class != sd->view_class) {
		clif_changelook(&sd->bl,LOOK_BASE,sd->view_class);
#if PACKETVER < 4
		clif_changelook(&sd->bl,LOOK_WEAPON,sd->status.weapon);
		clif_changelook(&sd->bl,LOOK_SHIELD,sd->status.shield);
#else
		clif_changelook(&sd->bl,LOOK_WEAPON,0);
#endif
	}

	if( memcmp(b_skill,sd->status.skill,sizeof(sd->status.skill)) || b_attackrange != sd->attackrange)
		clif_skillinfoblock(sd);	// スキル送信

	if(b_speed != sd->speed)
		clif_updatestatus(sd,SP_SPEED);
	if(b_weight != sd->weight)
		clif_updatestatus(sd,SP_WEIGHT);
	if(b_max_weight != sd->max_weight) {
		clif_updatestatus(sd,SP_MAXWEIGHT);
		pc_checkweighticon(sd);
	}
	for(i=0;i<6;i++)
		if(b_paramb[i] + b_parame[i] != sd->paramb[i] + sd->parame[i])
			clif_updatestatus(sd,SP_STR+i);
	if(b_hit != sd->hit)
		clif_updatestatus(sd,SP_HIT);
	if(b_flee != sd->flee)
		clif_updatestatus(sd,SP_FLEE1);
	if(b_aspd != sd->aspd)
		clif_updatestatus(sd,SP_ASPD);
	if(b_watk != sd->watk || b_base_atk != sd->base_atk)
		clif_updatestatus(sd,SP_ATK1);
	if(b_def != sd->def)
		clif_updatestatus(sd,SP_DEF1);
	if(b_watk2 != sd->watk2)
		clif_updatestatus(sd,SP_ATK2);
	if(b_def2 != sd->def2)
		clif_updatestatus(sd,SP_DEF2);
	if(b_flee2 != sd->flee2)
		clif_updatestatus(sd,SP_FLEE2);
	if(b_critical != sd->critical)
		clif_updatestatus(sd,SP_CRITICAL);
	if(b_matk1 != sd->matk1)
		clif_updatestatus(sd,SP_MATK1);
	if(b_matk2 != sd->matk2)
		clif_updatestatus(sd,SP_MATK2);
	if(b_mdef != sd->mdef)
		clif_updatestatus(sd,SP_MDEF1);
	if(b_mdef2 != sd->mdef2)
		clif_updatestatus(sd,SP_MDEF2);
	if(b_attackrange != sd->attackrange)
		clif_updatestatus(sd,SP_ATTACKRANGE);
	if(b_max_hp != sd->status.max_hp)
		clif_updatestatus(sd,SP_MAXHP);
	if(b_max_sp != sd->status.max_sp)
		clif_updatestatus(sd,SP_MAXSP);
	if(b_hp != sd->status.hp)
		clif_updatestatus(sd,SP_HP);
	if(b_sp != sd->status.sp)
		clif_updatestatus(sd,SP_SP);

/*	if(before.cart_num != before.cart_num || before.cart_max_num != before.cart_max_num ||
		before.cart_weight != before.cart_weight || before.cart_max_weight != before.cart_max_weight )
		clif_updatestatus(sd,SP_CARTINFO);*/

	if(sd->status.hp<sd->status.max_hp>>2 && pc_checkskill(sd,SM_AUTOBERSERK)>0 &&
		(sd->sc_data[SC_PROVOKE].timer==-1 || sd->sc_data[SC_PROVOKE].val2==0 ) && !pc_isdead(sd))
		// オートバーサーク発動
		status_change_start(&sd->bl,SC_PROVOKE,10,1,0,0,0,0);

	return 0;
}

/*==========================================
 * 対象のClassを返す(汎用)
 * 戻りは整数で0以上
 *------------------------------------------
 */
int status_get_class(struct block_list *bl)
{
	nullpo_retr(0, bl);
	if(bl->type==BL_MOB && (struct mob_data *)bl)
		return ((struct mob_data *)bl)->class;
	else if(bl->type==BL_PC && (struct map_session_data *)bl)
		return ((struct map_session_data *)bl)->status.class;
	else if(bl->type==BL_PET && (struct pet_data *)bl)
		return ((struct pet_data *)bl)->class;
	else
		return 0;
}
/*==========================================
 * 対象の方向を返す(汎用)
 * 戻りは整数で0以上
 *------------------------------------------
 */
int status_get_dir(struct block_list *bl)
{
	nullpo_retr(0, bl);
	if(bl->type==BL_MOB && (struct mob_data *)bl)
		return ((struct mob_data *)bl)->dir;
	else if(bl->type==BL_PC && (struct map_session_data *)bl)
		return ((struct map_session_data *)bl)->dir;
	else if(bl->type==BL_PET && (struct pet_data *)bl)
		return ((struct pet_data *)bl)->dir;
	else
		return 0;
}
/*==========================================
 * 対象のレベルを返す(汎用)
 * 戻りは整数で0以上
 *------------------------------------------
 */
int status_get_lv(struct block_list *bl)
{
	nullpo_retr(0, bl);
	if(bl->type==BL_MOB && (struct mob_data *)bl)
		return mob_db[((struct mob_data *)bl)->class].lv;
	else if(bl->type==BL_PC && (struct map_session_data *)bl)
		return ((struct map_session_data *)bl)->status.base_level;
	else if(bl->type==BL_PET && (struct pet_data *)bl)
		return ((struct pet_data *)bl)->msd->pet.level;
	else
		return 0;
}

/*==========================================
 * 対象の射程を返す(汎用)
 * 戻りは整数で0以上
 *------------------------------------------
 */
int status_get_range(struct block_list *bl)
{
	nullpo_retr(0, bl);
	if(bl->type==BL_MOB && (struct mob_data *)bl)
		return mob_db[((struct mob_data *)bl)->class].range;
	else if(bl->type==BL_PC && (struct map_session_data *)bl)
		return ((struct map_session_data *)bl)->attackrange;
	else if(bl->type==BL_PET && (struct pet_data *)bl)
		return mob_db[((struct pet_data *)bl)->class].range;
	else
		return 0;
}
/*==========================================
 * 対象のHPを返す(汎用)
 * 戻りは整数で0以上
 *------------------------------------------
 */
int status_get_hp(struct block_list *bl)
{
	nullpo_retr(1, bl);
	if(bl->type==BL_MOB && (struct mob_data *)bl)
		return ((struct mob_data *)bl)->hp;
	else if(bl->type==BL_PC && (struct map_session_data *)bl)
		return ((struct map_session_data *)bl)->status.hp;
	else
		return 1;
}
/*==========================================
 * 対象のMHPを返す(汎用)
 * 戻りは整数で0以上
 *------------------------------------------
 */
int status_get_max_hp(struct block_list *bl)
{
	nullpo_retr(1, bl);
	if(bl->type==BL_PC && ((struct map_session_data *)bl))
		return ((struct map_session_data *)bl)->status.max_hp;
	else {
		struct status_change *sc_data=status_get_sc_data(bl);
		int max_hp=1;
		if(bl->type==BL_MOB && ((struct mob_data*)bl)) {
			max_hp = mob_db[((struct mob_data*)bl)->class].max_hp;
			if(mob_db[((struct mob_data*)bl)->class].mexp > 0) {
				if(battle_config.mvp_hp_rate != 100)
					max_hp = (max_hp * battle_config.mvp_hp_rate)/100;
			}
			else {
				if(battle_config.monster_hp_rate != 100)
					max_hp = (max_hp * battle_config.monster_hp_rate)/100;
			}
		}
		else if(bl->type==BL_PET && ((struct pet_data*)bl)) {
			max_hp = mob_db[((struct pet_data*)bl)->class].max_hp;
			if(mob_db[((struct pet_data*)bl)->class].mexp > 0) {
				if(battle_config.mvp_hp_rate != 100)
					max_hp = (max_hp * battle_config.mvp_hp_rate)/100;
			}
			else {
				if(battle_config.monster_hp_rate != 100)
					max_hp = (max_hp * battle_config.monster_hp_rate)/100;
			}
		}
		if(sc_data) {
			if(sc_data[SC_APPLEIDUN].timer!=-1)
				max_hp += ((5+sc_data[SC_APPLEIDUN].val1*2+((sc_data[SC_APPLEIDUN].val2+1)>>1)
						+sc_data[SC_APPLEIDUN].val3/10) * max_hp)/100;
		}
		if(max_hp < 1) max_hp = 1;
		return max_hp;
	}
	return 1;
}
/*==========================================
 * 対象のStrを返す(汎用)
 * 戻りは整数で0以上
 *------------------------------------------
 */
int status_get_str(struct block_list *bl)
{
	int str=0;
	struct status_change *sc_data;
	
	nullpo_retr(0, bl);
	sc_data=status_get_sc_data(bl);
	if(bl->type==BL_MOB && ((struct mob_data *)bl))
		str = mob_db[((struct mob_data *)bl)->class].str;
	else if(bl->type==BL_PC && ((struct map_session_data *)bl))
		return ((struct map_session_data *)bl)->paramc[0];
	else if(bl->type==BL_PET && ((struct pet_data *)bl))
		str = mob_db[((struct pet_data *)bl)->class].str;

	if(sc_data) {
		if(sc_data[SC_LOUD].timer!=-1 && sc_data[SC_QUAGMIRE].timer == -1 && bl->type != BL_PC)
			str += 4;
		if( sc_data[SC_BLESSING].timer != -1 && bl->type != BL_PC){	// ブレッシング
			int race=status_get_race(bl);
			if(battle_check_undead(race,status_get_elem_type(bl)) || race==6 )	str >>= 1;	// 悪 魔/不死
			else str += sc_data[SC_BLESSING].val1;	// その他
		}
		if(sc_data[SC_TRUESIGHT].timer!=-1 && bl->type != BL_PC)	// トゥルーサイト
			str += 5;
	}
	if(str < 0) str = 0;
	return str;
}
/*==========================================
 * 対象のAgiを返す(汎用)
 * 戻りは整数で0以上
 *------------------------------------------
 */

int status_get_agi(struct block_list *bl)
{
	int agi=0;
	struct status_change *sc_data;

	nullpo_retr(0, bl);
	sc_data=status_get_sc_data(bl);
	if(bl->type==BL_MOB && (struct mob_data *)bl)
		agi=mob_db[((struct mob_data *)bl)->class].agi;
	else if(bl->type==BL_PC && (struct map_session_data *)bl)
		agi=((struct map_session_data *)bl)->paramc[1];
	else if(bl->type==BL_PET && (struct pet_data *)bl)
		agi=mob_db[((struct pet_data *)bl)->class].agi;

	if(sc_data) {
		if( sc_data[SC_INCREASEAGI].timer!=-1 && sc_data[SC_QUAGMIRE].timer == -1 && sc_data[SC_DONTFORGETME].timer == -1 &&
			bl->type != BL_PC)	// 速度増加(PCはpc.cで)
			agi += 2+sc_data[SC_INCREASEAGI].val1;

		if(sc_data[SC_CONCENTRATE].timer!=-1 && sc_data[SC_QUAGMIRE].timer == -1 && bl->type != BL_PC)
			agi += agi*(2+sc_data[SC_CONCENTRATE].val1)/100;

		if(sc_data[SC_DECREASEAGI].timer!=-1)	// 速度減少
			agi -= 2+sc_data[SC_DECREASEAGI].val1;

		if(sc_data[SC_QUAGMIRE].timer!=-1 )	// クァグマイア
			agi >>= 1;
		if(sc_data[SC_TRUESIGHT].timer!=-1 && bl->type != BL_PC)	// トゥルーサイト
			agi += 5;
	}
	if(agi < 0) agi = 0;
	return agi;
}
/*==========================================
 * 対象のVitを返す(汎用)
 * 戻りは整数で0以上
 *------------------------------------------
 */
int status_get_vit(struct block_list *bl)
{
	int vit=0;
	struct status_change *sc_data;

	nullpo_retr(0, bl);
	sc_data=status_get_sc_data(bl);
	if(bl->type==BL_MOB && (struct mob_data *)bl)
		vit=mob_db[((struct mob_data *)bl)->class].vit;
	else if(bl->type==BL_PC && (struct map_session_data *)bl)
		vit=((struct map_session_data *)bl)->paramc[2];
	else if(bl->type==BL_PET && (struct pet_data *)bl)
		vit=mob_db[((struct pet_data *)bl)->class].vit;
	if(sc_data) {
		if(sc_data[SC_STRIPARMOR].timer != -1 && bl->type!=BL_PC)
			vit = vit*60/100;
		if(sc_data[SC_TRUESIGHT].timer!=-1 && bl->type != BL_PC)	// トゥルーサイト
			vit += 5;
	}

	if(vit < 0) vit = 0;
	return vit;
}
/*==========================================
 * 対象のIntを返す(汎用)
 * 戻りは整数で0以上
 *------------------------------------------
 */
int status_get_int(struct block_list *bl)
{
	int int_=0;
	struct status_change *sc_data;

	nullpo_retr(0, bl);
	sc_data=status_get_sc_data(bl);
	if(bl->type==BL_MOB && (struct mob_data *)bl)
		int_=mob_db[((struct mob_data *)bl)->class].int_;
	else if(bl->type==BL_PC && (struct map_session_data *)bl)
		int_=((struct map_session_data *)bl)->paramc[3];
	else if(bl->type==BL_PET && (struct pet_data *)bl)
		int_=mob_db[((struct pet_data *)bl)->class].int_;

	if(sc_data) {
		if( sc_data[SC_BLESSING].timer != -1 && bl->type != BL_PC){	// ブレッシング
			int race=status_get_race(bl);
			if(battle_check_undead(race,status_get_elem_type(bl)) || race==6 )	int_ >>= 1;	// 悪 魔/不死
			else int_ += sc_data[SC_BLESSING].val1;	// その他
		}
		if( sc_data[SC_STRIPHELM].timer != -1 && bl->type != BL_PC)
			int_ = int_*90/100;
		if(sc_data[SC_TRUESIGHT].timer!=-1 && bl->type != BL_PC)	// トゥルーサイト
			int_ += 5;
	}
	if(int_ < 0) int_ = 0;
	return int_;
}
/*==========================================
 * 対象のDexを返す(汎用)
 * 戻りは整数で0以上
 *------------------------------------------
 */
int status_get_dex(struct block_list *bl)
{
	int dex=0;
	struct status_change *sc_data;

	nullpo_retr(0, bl);
	sc_data=status_get_sc_data(bl);
	if(bl->type==BL_MOB && (struct mob_data *)bl)
		dex=mob_db[((struct mob_data *)bl)->class].dex;
	else if(bl->type==BL_PC && (struct map_session_data *)bl)
		dex=((struct map_session_data *)bl)->paramc[4];
	else if(bl->type==BL_PET && (struct pet_data *)bl)
		dex=mob_db[((struct pet_data *)bl)->class].dex;

	if(sc_data) {
		if(sc_data[SC_CONCENTRATE].timer!=-1 && sc_data[SC_QUAGMIRE].timer == -1 && bl->type != BL_PC)
			dex += dex*(2+sc_data[SC_CONCENTRATE].val1)/100;

		if( sc_data[SC_BLESSING].timer != -1 && bl->type != BL_PC){	// ブレッシング
			int race=status_get_race(bl);
			if(battle_check_undead(race,status_get_elem_type(bl)) || race==6 )	dex >>= 1;	// 悪 魔/不死
			else dex += sc_data[SC_BLESSING].val1;	// その他
		}

		if(sc_data[SC_QUAGMIRE].timer!=-1 )	// クァグマイア
			dex >>= 1;
		if(sc_data[SC_TRUESIGHT].timer!=-1 && bl->type != BL_PC)	// トゥルーサイト
			dex += 5;
	}
	if(dex < 0) dex = 0;
	return dex;
}
/*==========================================
 * 対象のLukを返す(汎用)
 * 戻りは整数で0以上
 *------------------------------------------
 */
int status_get_luk(struct block_list *bl)
{
	int luk=0;
	struct status_change *sc_data;

	nullpo_retr(0, bl);
	sc_data=status_get_sc_data(bl);
	if(bl->type==BL_MOB && (struct mob_data *)bl)
		luk=mob_db[((struct mob_data *)bl)->class].luk;
	else if(bl->type==BL_PC && (struct map_session_data *)bl)
		luk=((struct map_session_data *)bl)->paramc[5];
	else if(bl->type==BL_PET && (struct pet_data *)bl)
		luk=mob_db[((struct pet_data *)bl)->class].luk;

	if(sc_data) {
		if(sc_data[SC_GLORIA].timer!=-1 && bl->type != BL_PC)	// グロリア(PCはpc.cで)
			luk += 30;
		if(sc_data[SC_CURSE].timer!=-1 )		// 呪い
			luk=0;
		if(sc_data[SC_TRUESIGHT].timer!=-1 && bl->type != BL_PC)	// トゥルーサイト
			luk += 5;
	}
	if(luk < 0) luk = 0;
	return luk;
}

/*==========================================
 * 対象のFleeを返す(汎用)
 * 戻りは整数で1以上
 *------------------------------------------
 */
int status_get_flee(struct block_list *bl)
{
	int flee=1;
	struct status_change *sc_data;

	nullpo_retr(1, bl);
	sc_data=status_get_sc_data(bl);
	if(bl->type==BL_PC && (struct map_session_data *)bl)
		flee=((struct map_session_data *)bl)->flee;
	else
		flee=status_get_agi(bl) + status_get_lv(bl);

	if(sc_data) {
		if(sc_data[SC_WHISTLE].timer!=-1 && bl->type != BL_PC)
			flee += flee*(sc_data[SC_WHISTLE].val1+sc_data[SC_WHISTLE].val2
					+(sc_data[SC_WHISTLE].val3>>16))/100;
		if(sc_data[SC_BLIND].timer!=-1 && bl->type != BL_PC)
			flee -= flee*25/100;
		if(sc_data[SC_WINDWALK].timer!=-1 && bl->type != BL_PC) // ウィンドウォーク
			flee += flee*(sc_data[SC_WINDWALK].val2)/100;
		if(sc_data[SC_SPIDERWEB].timer!=-1 && bl->type != BL_PC) //スパイダーウェブ
			flee -= flee*50/100;
	}
	if(flee < 1) flee = 1;
	return flee;
}
/*==========================================
 * 対象のHitを返す(汎用)
 * 戻りは整数で1以上
 *------------------------------------------
 */
int status_get_hit(struct block_list *bl)
{
	int hit=1;
	struct status_change *sc_data;

	nullpo_retr(1, bl);
	sc_data=status_get_sc_data(bl);
	if(bl->type==BL_PC && (struct map_session_data *)bl)
		hit=((struct map_session_data *)bl)->hit;
	else
		hit=status_get_dex(bl) + status_get_lv(bl);

	if(sc_data) {
		if(sc_data[SC_HUMMING].timer!=-1 && bl->type != BL_PC)	// 
			hit += hit*(sc_data[SC_HUMMING].val1*2+sc_data[SC_HUMMING].val2
					+sc_data[SC_HUMMING].val3)/100;
		if(sc_data[SC_BLIND].timer!=-1 && bl->type != BL_PC)		// 呪い
			hit -= hit*25/100;
		if(sc_data[SC_TRUESIGHT].timer!=-1 && bl->type != BL_PC)		// トゥルーサイト
			hit += 3*(sc_data[SC_TRUESIGHT].val1);
		if(sc_data[SC_CONCENTRATION].timer!=-1 && bl->type != BL_PC) //コンセントレーション
			hit += (hit*(10*(sc_data[SC_CONCENTRATION].val1)))/100;
	}
	if(hit < 1) hit = 1;
	return hit;
}
/*==========================================
 * 対象の完全回避を返す(汎用)
 * 戻りは整数で1以上
 *------------------------------------------
 */
int status_get_flee2(struct block_list *bl)
{
	int flee2=1;
	struct status_change *sc_data;

	nullpo_retr(1, bl);
	sc_data=status_get_sc_data(bl);
	if(bl->type==BL_PC && (struct map_session_data *)bl){
		flee2 = status_get_luk(bl) + 10;
		flee2 += ((struct map_session_data *)bl)->flee2 - (((struct map_session_data *)bl)->paramc[5] + 10);
	}
	else
		flee2=status_get_luk(bl)+1;

	if(sc_data) {
		if(sc_data[SC_WHISTLE].timer!=-1 && bl->type != BL_PC)
			flee2 += (sc_data[SC_WHISTLE].val1+sc_data[SC_WHISTLE].val2
					+(sc_data[SC_WHISTLE].val3&0xffff))*10;
	}
	if(flee2 < 1) flee2 = 1;
	return flee2;
}
/*==========================================
 * 対象のクリティカルを返す(汎用)
 * 戻りは整数で1以上
 *------------------------------------------
 */
int status_get_critical(struct block_list *bl)
{
	int critical=1;
	struct status_change *sc_data;

	nullpo_retr(1, bl);
	sc_data=status_get_sc_data(bl);
	if(bl->type==BL_PC && (struct map_session_data *)bl){
		critical = status_get_luk(bl)*3 + 10;
		critical += ((struct map_session_data *)bl)->critical - ((((struct map_session_data *)bl)->paramc[5]*3) + 10);
	}
	else
		critical=status_get_luk(bl)*3 + 1;

	if(sc_data) {
		if(sc_data[SC_FORTUNE].timer!=-1 && bl->type != BL_PC)
			critical += (10+sc_data[SC_FORTUNE].val1+sc_data[SC_FORTUNE].val2
					+sc_data[SC_FORTUNE].val3)*10;
		if(sc_data[SC_EXPLOSIONSPIRITS].timer!=-1 && bl->type != BL_PC)
			critical += sc_data[SC_EXPLOSIONSPIRITS].val2;
		if(sc_data[SC_TRUESIGHT].timer!=-1 && bl->type != BL_PC) //トゥルーサイト
			critical += critical*sc_data[SC_TRUESIGHT].val1/100;
	}
	if(critical < 1) critical = 1;
	return critical;
}
/*==========================================
 * base_atkの取得
 * 戻りは整数で1以上
 *------------------------------------------
 */
int status_get_baseatk(struct block_list *bl)
{
	struct status_change *sc_data;
	int batk=1;

	nullpo_retr(1, bl);
	sc_data=status_get_sc_data(bl);
	if(bl->type==BL_PC && (struct map_session_data *)bl){
		batk = ((struct map_session_data *)bl)->base_atk; //設定されているbase_atk
		batk += ((struct map_session_data *)bl)->weapon_atk[((struct map_session_data *)bl)->status.weapon];
	}else { //それ以外なら
		int str,dstr;
		str = status_get_str(bl); //STR
		dstr = str/10;
		batk = dstr*dstr + str; //base_atkを計算する
	}
	if(sc_data) { //状態異常あり
		if(sc_data[SC_PROVOKE].timer!=-1 && bl->type != BL_PC) //PCでプロボック(SM_PROVOKE)状態
			batk = batk*(100+2*sc_data[SC_PROVOKE].val1)/100; //base_atk増加
		if(sc_data[SC_CURSE].timer!=-1 ) //呪われていたら
			batk -= batk*25/100; //base_atkが25%減少
		if(sc_data[SC_CONCENTRATION].timer!=-1 && bl->type != BL_PC) //コンセントレーション
			batk += batk*(5*sc_data[SC_CONCENTRATION].val1)/100;
	}
	if(batk < 1) batk = 1; //base_atkは最低でも1
	return batk;
}
/*==========================================
 * 対象のAtkを返す(汎用)
 * 戻りは整数で0以上
 *------------------------------------------
 */
int status_get_atk(struct block_list *bl)
{
	struct status_change *sc_data;
	int atk=0;

	nullpo_retr(0, bl);
	sc_data=status_get_sc_data(bl);
	if(bl->type==BL_PC && (struct map_session_data *)bl)
		atk = ((struct map_session_data*)bl)->watk;
	else if(bl->type==BL_MOB && (struct mob_data *)bl)
		atk = mob_db[((struct mob_data*)bl)->class].atk1;
	else if(bl->type==BL_PET && (struct pet_data *)bl)
		atk = mob_db[((struct pet_data*)bl)->class].atk1;

	if(sc_data) {
		if(sc_data[SC_PROVOKE].timer!=-1 && bl->type != BL_PC)
			atk = atk*(100+2*sc_data[SC_PROVOKE].val1)/100;
		if(sc_data[SC_CURSE].timer!=-1 )
			atk -= atk*25/100;
		if(sc_data[SC_CONCENTRATION].timer!=-1 && bl->type != BL_PC) //コンセントレーション
			atk += atk*(5*sc_data[SC_CONCENTRATION].val1)/100;
	}
	if(atk < 0) atk = 0;
	return atk;
}
/*==========================================
 * 対象の左手Atkを返す(汎用)
 * 戻りは整数で0以上
 *------------------------------------------
 */
int status_get_atk_(struct block_list *bl)
{
	nullpo_retr(0, bl);
	if(bl->type==BL_PC && (struct map_session_data *)bl){
		int atk=((struct map_session_data*)bl)->watk_;

		if(((struct map_session_data *)bl)->sc_data[SC_CURSE].timer!=-1 )
			atk -= atk*25/100;
		return atk;
	}
	else
		return 0;
}
/*==========================================
 * 対象のAtk2を返す(汎用)
 * 戻りは整数で0以上
 *------------------------------------------
 */
int status_get_atk2(struct block_list *bl)
{
	nullpo_retr(0, bl);
	if(bl->type==BL_PC && (struct map_session_data *)bl)
		return ((struct map_session_data*)bl)->watk2;
	else {
		struct status_change *sc_data=status_get_sc_data(bl);
		int atk2=0;
		if(bl->type==BL_MOB && (struct mob_data *)bl)
			atk2 = mob_db[((struct mob_data*)bl)->class].atk2;
		else if(bl->type==BL_PET && (struct pet_data *)bl)
			atk2 = mob_db[((struct pet_data*)bl)->class].atk2;
		if(sc_data) {
			if( sc_data[SC_IMPOSITIO].timer!=-1)
				atk2 += sc_data[SC_IMPOSITIO].val1*5;
			if( sc_data[SC_PROVOKE].timer!=-1 )
				atk2 = atk2*(100+2*sc_data[SC_PROVOKE].val1)/100;
			if( sc_data[SC_CURSE].timer!=-1 )
				atk2 -= atk2*25/100;
			if(sc_data[SC_DRUMBATTLE].timer!=-1)
				atk2 += sc_data[SC_DRUMBATTLE].val2;
			if(sc_data[SC_NIBELUNGEN].timer!=-1 && (status_get_element(bl)/10) >= 8 )
				atk2 += sc_data[SC_NIBELUNGEN].val2;
			if(sc_data[SC_STRIPWEAPON].timer!=-1)
				atk2 -= atk2*90/100;
			if(sc_data[SC_CONCENTRATION].timer!=-1) //コンセントレーション
				atk2 += atk2*(5*sc_data[SC_CONCENTRATION].val1)/100;
		}
		if(atk2 < 0) atk2 = 0;
		return atk2;
	}
	return 0;
}
/*==========================================
 * 対象の左手Atk2を返す(汎用)
 * 戻りは整数で0以上
 *------------------------------------------
 */
int status_get_atk_2(struct block_list *bl)
{
	nullpo_retr(0, bl);
	if(bl->type==BL_PC && (struct map_session_data *)bl)
		return ((struct map_session_data*)bl)->watk_2;
	else
		return 0;
}
/*==========================================
 * 対象のMAtk1を返す(汎用)
 * 戻りは整数で0以上
 *------------------------------------------
 */
int status_get_matk1(struct block_list *bl)
{
	nullpo_retr(0, bl);
	if(bl->type==BL_MOB){
		int matk,int_=status_get_int(bl);
		matk = int_+(int_/5)*(int_/5);
		return matk;
	}
	else if(bl->type==BL_PC && (struct map_session_data *)bl)
		return ((struct map_session_data *)bl)->matk1;
	else if(bl->type==BL_PET){
		int matk,int_=status_get_int(bl);
		matk = int_+(int_/5)*(int_/5);
		return matk;
	}
	else
		return 0;
}
/*==========================================
 * 対象のMAtk2を返す(汎用)
 * 戻りは整数で0以上
 *------------------------------------------
 */
int status_get_matk2(struct block_list *bl)
{
	nullpo_retr(0, bl);
	if(bl->type==BL_MOB){
		int matk,int_=status_get_int(bl);
		matk = int_+(int_/7)*(int_/7);
		return matk;
	}
	else if(bl->type==BL_PC && (struct map_session_data *)bl)
		return ((struct map_session_data *)bl)->matk2;
	else if(bl->type==BL_PET){
		int matk,int_=status_get_int(bl);
		matk = int_+(int_/7)*(int_/7);
		return matk;
	}
	else
		return 0;
}
/*==========================================
 * 対象のDefを返す(汎用)
 * 戻りは整数で0以上
 *------------------------------------------
 */
int status_get_def(struct block_list *bl)
{
	struct status_change *sc_data;
	int def=0,skilltimer=-1,skillid=0;

	nullpo_retr(0, bl);
	sc_data=status_get_sc_data(bl);
	if(bl->type==BL_PC && (struct map_session_data *)bl){
		def = ((struct map_session_data *)bl)->def;
		skilltimer = ((struct map_session_data *)bl)->skilltimer;
		skillid = ((struct map_session_data *)bl)->skillid;
	}
	else if(bl->type==BL_MOB && (struct mob_data *)bl) {
		def = mob_db[((struct mob_data *)bl)->class].def;
		skilltimer = ((struct mob_data *)bl)->skilltimer;
		skillid = ((struct mob_data *)bl)->skillid;
	}
	else if(bl->type==BL_PET && (struct pet_data *)bl)
		def = mob_db[((struct pet_data *)bl)->class].def;

	if(def < 1000000) {
		if(sc_data) {
			//キーピング時はDEF100
			if( sc_data[SC_KEEPING].timer!=-1)
				def = 100;
			//プロボック時は減算
			if( sc_data[SC_PROVOKE].timer!=-1 && bl->type != BL_PC)
				def = (def*(100 - 6*sc_data[SC_PROVOKE].val1)+50)/100;
			//戦太鼓の響き時は加算
			if( sc_data[SC_DRUMBATTLE].timer!=-1 && bl->type != BL_PC)
				def += sc_data[SC_DRUMBATTLE].val3;
			//毒にかかっている時は減算
			if(sc_data[SC_POISON].timer!=-1 && bl->type != BL_PC)
				def = def*75/100;
			//ストリップシールド時は減算
			if(sc_data[SC_STRIPSHIELD].timer!=-1 && bl->type != BL_PC)
				def = def*85/100;
			//シグナムクルシス時は減算
			if(sc_data[SC_SIGNUMCRUCIS].timer!=-1 && bl->type != BL_PC)
				def = def * (100 - sc_data[SC_SIGNUMCRUCIS].val2)/100;
			//永遠の混沌時はDEF0になる
			if(sc_data[SC_ETERNALCHAOS].timer!=-1 && bl->type != BL_PC)
				def = 0;
			//凍結、石化時は右シフト
			if(sc_data[SC_FREEZE].timer != -1 || (sc_data[SC_STONE].timer != -1 && sc_data[SC_STONE].val2 == 0))
				def >>= 1;
			//コンセントレーション時は減算
			if( sc_data[SC_CONCENTRATION].timer!=-1 && bl->type != BL_PC)
				def = (def*(100 - 5*sc_data[SC_CONCENTRATION].val1))/100;
		}
		//詠唱中は詠唱時減算率に基づいて減算
		if(skilltimer != -1) {
			int def_rate = skill_get_castdef(skillid);
			if(def_rate != 0)
				def = (def * (100 - def_rate))/100;
		}
	}
	if(def < 0) def = 0;
	return def;
}
/*==========================================
 * 対象のMDefを返す(汎用)
 * 戻りは整数で0以上
 *------------------------------------------
 */
int status_get_mdef(struct block_list *bl)
{
	struct status_change *sc_data;
	int mdef=0;

	nullpo_retr(0, bl);
	sc_data=status_get_sc_data(bl);
	if(bl->type==BL_PC && (struct map_session_data *)bl)
		mdef = ((struct map_session_data *)bl)->mdef;
	else if(bl->type==BL_MOB && (struct mob_data *)bl)
		mdef = mob_db[((struct mob_data *)bl)->class].mdef;
	else if(bl->type==BL_PET && (struct pet_data *)bl)
		mdef = mob_db[((struct pet_data *)bl)->class].mdef;

	if(mdef < 1000000) {
		if(sc_data) {
			//バリアー状態時はMDEF100
			if(sc_data[SC_BARRIER].timer != -1)
				mdef = 100;
			//凍結、石化時は1.25倍
			if(sc_data[SC_FREEZE].timer != -1 || (sc_data[SC_STONE].timer != -1 && sc_data[SC_STONE].val2 == 0))
				mdef = mdef*125/100;
		}
	}
	if(mdef < 0) mdef = 0;
	return mdef;
}
/*==========================================
 * 対象のDef2を返す(汎用)
 * 戻りは整数で1以上
 *------------------------------------------
 */
int status_get_def2(struct block_list *bl)
{
	struct status_change *sc_data;
	int def2=1;

	nullpo_retr(1, bl);
	sc_data=status_get_sc_data(bl);
	if(bl->type==BL_PC)
		def2 = ((struct map_session_data *)bl)->def2;
	else if(bl->type==BL_MOB)
		def2 = mob_db[((struct mob_data *)bl)->class].vit;
	else if(bl->type==BL_PET)
		def2 = mob_db[((struct pet_data *)bl)->class].vit;

	if(sc_data) {
		if( sc_data[SC_ANGELUS].timer!=-1 && bl->type != BL_PC)
			def2 = def2*(110+5*sc_data[SC_ANGELUS].val1)/100;
		if( sc_data[SC_PROVOKE].timer!=-1 && bl->type != BL_PC)
			def2 = (def2*(100 - 6*sc_data[SC_PROVOKE].val1)+50)/100;
		if(sc_data[SC_POISON].timer!=-1 && bl->type != BL_PC)
			def2 = def2*75/100;
		//コンセントレーション時は減算
		if( sc_data[SC_CONCENTRATION].timer!=-1 && bl->type != BL_PC)
			def2 = def2*(100 - 5*sc_data[SC_CONCENTRATION].val1)/100;
	}
	if(def2 < 1) def2 = 1;
	return def2;
}
/*==========================================
 * 対象のMDef2を返す(汎用)
 * 戻りは整数で0以上
 *------------------------------------------
 */
int status_get_mdef2(struct block_list *bl)
{
	nullpo_retr(0, bl);
	if(bl->type==BL_MOB && (struct mob_data *)bl)
		return mob_db[((struct mob_data *)bl)->class].int_ + (mob_db[((struct mob_data *)bl)->class].vit>>1);
	else if(bl->type==BL_PC && (struct map_session_data *)bl)
		return ((struct map_session_data *)bl)->mdef2 + (((struct map_session_data *)bl)->paramc[2]>>1);
	else if(bl->type==BL_PET && (struct pet_data *)bl)
		return mob_db[((struct pet_data *)bl)->class].int_ + (mob_db[((struct pet_data *)bl)->class].vit>>1);
	else
		return 0;
}
/*==========================================
 * 対象のSpeed(移動速度)を返す(汎用)
 * 戻りは整数で1以上
 * Speedは小さいほうが移動速度が速い
 *------------------------------------------
 */
int status_get_speed(struct block_list *bl)
{
	nullpo_retr(1000, bl);
	if(bl->type==BL_PC && (struct map_session_data *)bl)
		return ((struct map_session_data *)bl)->speed;
	else {
		struct status_change *sc_data=status_get_sc_data(bl);
		int speed = 1000;
		if(bl->type==BL_MOB && (struct mob_data *)bl)
//			speed = mob_db[((struct mob_data *)bl)->class].speed;
			speed = ((struct mob_data *)bl)->speed;
		else if(bl->type==BL_PET && (struct pet_data *)bl)
			speed = ((struct pet_data *)bl)->msd->petDB->speed;

		if(sc_data) {
			//速度増加時は25%減算
			if(sc_data[SC_INCREASEAGI].timer!=-1 && sc_data[SC_DONTFORGETME].timer == -1)
				speed -= speed*25/100;
			//速度減少時は25%加算
			if(sc_data[SC_DECREASEAGI].timer!=-1)
				speed = speed*125/100;
			//クァグマイア時は50%加算
			if(sc_data[SC_QUAGMIRE].timer!=-1)
				speed = speed*3/2;
			//私を忘れないで…時は加算
			if(sc_data[SC_DONTFORGETME].timer!=-1)
				speed = speed*(100+sc_data[SC_DONTFORGETME].val1*2 + sc_data[SC_DONTFORGETME].val2 + (sc_data[SC_DONTFORGETME].val3&0xffff))/100;
			//金剛時は25%加算
			if(sc_data[SC_STEELBODY].timer!=-1)
				speed = speed*125/100;
			//ディフェンダー時は加算
			if(sc_data[SC_DEFENDER].timer!=-1)
				speed = (speed * (155 - sc_data[SC_DEFENDER].val1*5)) / 100;
			//踊り状態は4倍遅い
			if(sc_data[SC_DANCING].timer!=-1 )
				speed*=4;
			//呪い時は450加算
			if(sc_data[SC_CURSE].timer!=-1)
				speed = speed + 450;
			//ウィンドウォーク時はLv*2%減算
			if(sc_data[SC_WINDWALK].timer!=-1)
				speed -= (speed*(sc_data[SC_WINDWALK].val1*2))/100;
		}
		if(speed < 1) speed = 1;
		return speed;
	}

	return 1000;
}
/*==========================================
 * 対象のaDelay(攻撃時ディレイ)を返す(汎用)
 * aDelayは小さいほうが攻撃速度が速い
 *------------------------------------------
 */
int status_get_adelay(struct block_list *bl)
{
	nullpo_retr(4000, bl);
	if(bl->type==BL_PC && (struct map_session_data *)bl)
		return (((struct map_session_data *)bl)->aspd<<1);
	else {
		struct status_change *sc_data=status_get_sc_data(bl);
		int adelay=4000,aspd_rate = 100,i;
		if(bl->type==BL_MOB && (struct mob_data *)bl)
			adelay = mob_db[((struct mob_data *)bl)->class].adelay;
		else if(bl->type==BL_PET && (struct pet_data *)bl)
			adelay = mob_db[((struct pet_data *)bl)->class].adelay;

		if(sc_data) {
			//ツーハンドクイッケン使用時でクァグマイアでも私を忘れないで…でもない時は3割減算
			if(sc_data[SC_TWOHANDQUICKEN].timer != -1 && sc_data[SC_QUAGMIRE].timer == -1 && sc_data[SC_DONTFORGETME].timer == -1)	// 2HQ
				aspd_rate -= 30;
			//アドレナリンラッシュ使用時でツーハンドクイッケンでもクァグマイアでも私を忘れないで…でもない時は
			if(sc_data[SC_ADRENALINE].timer != -1 && sc_data[SC_TWOHANDQUICKEN].timer == -1 &&
				sc_data[SC_QUAGMIRE].timer == -1 && sc_data[SC_DONTFORGETME].timer == -1) {	// アドレナリンラッシュ
				//使用者とパーティメンバーで格差が出る設定でなければ3割減算
				if(sc_data[SC_ADRENALINE].val2 || !battle_config.party_skill_penaly)
					aspd_rate -= 30;
				//そうでなければ2.5割減算
				else
					aspd_rate -= 25;
			}
			//スピアクィッケン時は減算
			if(sc_data[SC_SPEARSQUICKEN].timer != -1 && sc_data[SC_ADRENALINE].timer == -1 &&
				sc_data[SC_TWOHANDQUICKEN].timer == -1 && sc_data[SC_QUAGMIRE].timer == -1 && sc_data[SC_DONTFORGETME].timer == -1)	// スピアクィッケン
				aspd_rate -= sc_data[SC_SPEARSQUICKEN].val2;
			//夕日のアサシンクロス時は減算
			if(sc_data[SC_ASSNCROS].timer!=-1 && // 夕陽のアサシンクロス
				sc_data[SC_TWOHANDQUICKEN].timer==-1 && sc_data[SC_ADRENALINE].timer==-1 && sc_data[SC_SPEARSQUICKEN].timer==-1 &&
				sc_data[SC_DONTFORGETME].timer == -1)
				aspd_rate -= 5+sc_data[SC_ASSNCROS].val1+sc_data[SC_ASSNCROS].val2+sc_data[SC_ASSNCROS].val3;
			//私を忘れないで…時は加算
			if(sc_data[SC_DONTFORGETME].timer!=-1)		// 私を忘れないで
				aspd_rate += sc_data[SC_DONTFORGETME].val1*3 + sc_data[SC_DONTFORGETME].val2 + (sc_data[SC_DONTFORGETME].val3>>16);
			//金剛時25%加算
			if(sc_data[SC_STEELBODY].timer!=-1)	// 金剛
				aspd_rate += 25;
			//増速ポーション使用時は減算
			if(	sc_data[i=SC_SPEEDPOTION2].timer!=-1 || sc_data[i=SC_SPEEDPOTION1].timer!=-1 || sc_data[i=SC_SPEEDPOTION0].timer!=-1)
				aspd_rate -= sc_data[i].val2;
			//ディフェンダー時は加算
			if(sc_data[SC_DEFENDER].timer != -1)
				adelay += (1100 - sc_data[SC_DEFENDER].val1*100);
		}
		if(aspd_rate != 100)
			adelay = adelay*aspd_rate/100;
		if(adelay < battle_config.monster_max_aspd<<1) adelay = battle_config.monster_max_aspd<<1;
		return adelay;
	}
	return 4000;
}
int status_get_amotion(struct block_list *bl)
{
	nullpo_retr(2000, bl);
	if(bl->type==BL_PC && (struct map_session_data *)bl)
		return ((struct map_session_data *)bl)->amotion;
	else {
		struct status_change *sc_data=status_get_sc_data(bl);
		int amotion=2000,aspd_rate = 100,i;
		if(bl->type==BL_MOB && (struct mob_data *)bl)
			amotion = mob_db[((struct mob_data *)bl)->class].amotion;
		else if(bl->type==BL_PET && (struct pet_data *)bl)
			amotion = mob_db[((struct pet_data *)bl)->class].amotion;

		if(sc_data) {
			if(sc_data[SC_TWOHANDQUICKEN].timer != -1 && sc_data[SC_QUAGMIRE].timer == -1 && sc_data[SC_DONTFORGETME].timer == -1)	// 2HQ
				aspd_rate -= 30;
			if(sc_data[SC_ADRENALINE].timer != -1 && sc_data[SC_TWOHANDQUICKEN].timer == -1 &&
				sc_data[SC_QUAGMIRE].timer == -1 && sc_data[SC_DONTFORGETME].timer == -1) {	// アドレナリンラッシュ
				if(sc_data[SC_ADRENALINE].val2 || !battle_config.party_skill_penaly)
					aspd_rate -= 30;
				else
					aspd_rate -= 25;
			}
			if(sc_data[SC_SPEARSQUICKEN].timer != -1 && sc_data[SC_ADRENALINE].timer == -1 &&
				sc_data[SC_TWOHANDQUICKEN].timer == -1 && sc_data[SC_QUAGMIRE].timer == -1 && sc_data[SC_DONTFORGETME].timer == -1)	// スピアクィッケン
				aspd_rate -= sc_data[SC_SPEARSQUICKEN].val2;
			if(sc_data[SC_ASSNCROS].timer!=-1 && // 夕陽のアサシンクロス
				sc_data[SC_TWOHANDQUICKEN].timer==-1 && sc_data[SC_ADRENALINE].timer==-1 && sc_data[SC_SPEARSQUICKEN].timer==-1 &&
				sc_data[SC_DONTFORGETME].timer == -1)
				aspd_rate -= 5+sc_data[SC_ASSNCROS].val1+sc_data[SC_ASSNCROS].val2+sc_data[SC_ASSNCROS].val3;
			if(sc_data[SC_DONTFORGETME].timer!=-1)		// 私を忘れないで
				aspd_rate += sc_data[SC_DONTFORGETME].val1*3 + sc_data[SC_DONTFORGETME].val2 + (sc_data[SC_DONTFORGETME].val3>>16);
			if(sc_data[SC_STEELBODY].timer!=-1)	// 金剛
				aspd_rate += 25;
			if(	sc_data[i=SC_SPEEDPOTION2].timer!=-1 || sc_data[i=SC_SPEEDPOTION1].timer!=-1 || sc_data[i=SC_SPEEDPOTION0].timer!=-1)
				aspd_rate -= sc_data[i].val2;
			if(sc_data[SC_DEFENDER].timer != -1)
				amotion += (550 - sc_data[SC_DEFENDER].val1*50);
		}
		if(aspd_rate != 100)
			amotion = amotion*aspd_rate/100;
		if(amotion < battle_config.monster_max_aspd) amotion = battle_config.monster_max_aspd;
		return amotion;
	}
	return 2000;
}
int status_get_dmotion(struct block_list *bl)
{
	int ret;
	struct status_change *sc_data;

	nullpo_retr(0, bl);
	sc_data = status_get_sc_data(bl);
	if(bl->type==BL_MOB && (struct mob_data *)bl){
		ret=mob_db[((struct mob_data *)bl)->class].dmotion;
		if(battle_config.monster_damage_delay_rate != 100)
			ret = ret*battle_config.monster_damage_delay_rate/100;
	}
	else if(bl->type==BL_PC && (struct map_session_data *)bl){
		ret=((struct map_session_data *)bl)->dmotion;
		if(battle_config.pc_damage_delay_rate != 100)
			ret = ret*battle_config.pc_damage_delay_rate/100;
	}
	else if(bl->type==BL_PET && (struct pet_data *)bl)
		ret=mob_db[((struct pet_data *)bl)->class].dmotion;
	else
		return 2000;

	if((sc_data && sc_data[SC_ENDURE].timer!=-1) ||
		(bl->type == BL_PC && ((struct map_session_data *)bl)->special_state.infinite_endure))
		ret=0;

	return ret;
}
int status_get_element(struct block_list *bl)
{
	int ret = 20;
	struct status_change *sc_data;

	nullpo_retr(ret, bl);
	sc_data = status_get_sc_data(bl);
	if(bl->type==BL_MOB && (struct mob_data *)bl)	// 10の位＝Lv*2、１の位＝属性
		ret=((struct mob_data *)bl)->def_ele;
	else if(bl->type==BL_PC && (struct map_session_data *)bl)
		ret=20+((struct map_session_data *)bl)->def_ele;	// 防御属性Lv1
	else if(bl->type==BL_PET && (struct pet_data *)bl)
		ret = mob_db[((struct pet_data *)bl)->class].element;

	if(sc_data) {
		if( sc_data[SC_BENEDICTIO].timer!=-1 )	// 聖体降福
			ret=26;
		if( sc_data[SC_FREEZE].timer!=-1 )	// 凍結
			ret=21;
		if( sc_data[SC_STONE].timer!=-1 && sc_data[SC_STONE].val2==0)
			ret=22;
	}

	return ret;
}

int status_get_attack_element(struct block_list *bl)
{
	int ret = 0;
	struct status_change *sc_data=status_get_sc_data(bl);

	nullpo_retr(0, bl);
	if(bl->type==BL_MOB && (struct mob_data *)bl)
		ret=0;
	else if(bl->type==BL_PC && (struct map_session_data *)bl)
		ret=((struct map_session_data *)bl)->atk_ele;
	else if(bl->type==BL_PET && (struct pet_data *)bl)
		ret=0;

	if(sc_data) {
		if( sc_data[SC_FROSTWEAPON].timer!=-1)	// フロストウェポン
			ret=1;
		if( sc_data[SC_SEISMICWEAPON].timer!=-1)	// サイズミックウェポン
			ret=2;
		if( sc_data[SC_FLAMELAUNCHER].timer!=-1)	// フレームランチャー
			ret=3;
		if( sc_data[SC_LIGHTNINGLOADER].timer!=-1)	// ライトニングローダー
			ret=4;
		if( sc_data[SC_ENCPOISON].timer!=-1)	// エンチャントポイズン
			ret=5;
		if( sc_data[SC_ASPERSIO].timer!=-1)		// アスペルシオ
			ret=6;
	}

	return ret;
}
int status_get_attack_element2(struct block_list *bl)
{
	nullpo_retr(0, bl);
	if(bl->type==BL_PC && (struct map_session_data *)bl) {
		int ret = ((struct map_session_data *)bl)->atk_ele_;
		struct status_change *sc_data = ((struct map_session_data *)bl)->sc_data;

		if(sc_data) {
			if( sc_data[SC_FROSTWEAPON].timer!=-1)	// フロストウェポン
				ret=1;
			if( sc_data[SC_SEISMICWEAPON].timer!=-1)	// サイズミックウェポン
				ret=2;
			if( sc_data[SC_FLAMELAUNCHER].timer!=-1)	// フレームランチャー
				ret=3;
			if( sc_data[SC_LIGHTNINGLOADER].timer!=-1)	// ライトニングローダー
				ret=4;
			if( sc_data[SC_ENCPOISON].timer!=-1)	// エンチャントポイズン
				ret=5;
			if( sc_data[SC_ASPERSIO].timer!=-1)		// アスペルシオ
				ret=6;
		}
		return ret;
	}
	return 0;
}
int status_get_party_id(struct block_list *bl)
{
	nullpo_retr(0, bl);
	if(bl->type==BL_PC && (struct map_session_data *)bl)
		return ((struct map_session_data *)bl)->status.party_id;
	else if(bl->type==BL_MOB && (struct mob_data *)bl){
		struct mob_data *md=(struct mob_data *)bl;
		if( md->master_id>0 )
				return -md->master_id;
			return -md->bl.id;
	}
	else if(bl->type==BL_SKILL && (struct skill_unit *)bl)
		return ((struct skill_unit *)bl)->group->party_id;
	else
	return 0;
}
int status_get_guild_id(struct block_list *bl)
{
	nullpo_retr(0, bl);
	if(bl->type==BL_PC && (struct map_session_data *)bl)
		return ((struct map_session_data *)bl)->status.guild_id;
	else if(bl->type==BL_MOB && (struct mob_data *)bl)
		return ((struct mob_data *)bl)->class;
	else if(bl->type==BL_SKILL && (struct skill_unit *)bl)
		return ((struct skill_unit *)bl)->group->guild_id;
	else
		return 0;
}
int status_get_race(struct block_list *bl)
{
	nullpo_retr(0, bl);
	if(bl->type==BL_MOB && (struct mob_data *)bl)
		return mob_db[((struct mob_data *)bl)->class].race;
	else if(bl->type==BL_PC && (struct map_session_data *)bl)
		return 7;
	else if(bl->type==BL_PET && (struct pet_data *)bl)
		return mob_db[((struct pet_data *)bl)->class].race;
	else
		return 0;
}
int status_get_size(struct block_list *bl)
{
	nullpo_retr(1, bl);
	if(bl->type==BL_MOB && (struct mob_data *)bl)
		return mob_db[((struct mob_data *)bl)->class].size;
	else if(bl->type==BL_PC && (struct map_session_data *)bl)
		return 1;
	else if(bl->type==BL_PET && (struct pet_data *)bl)
		return mob_db[((struct pet_data *)bl)->class].size;
	else
		return 1;
}
int status_get_mode(struct block_list *bl)
{
	nullpo_retr(0x01, bl);
	if(bl->type==BL_MOB && (struct mob_data *)bl)
		return mob_db[((struct mob_data *)bl)->class].mode;
	else if(bl->type==BL_PET && (struct pet_data *)bl)
		return mob_db[((struct pet_data *)bl)->class].mode;
	else
		return 0x01;	// とりあえず動くということで1
}

int status_get_mexp(struct block_list *bl)
{
	nullpo_retr(0, bl);
	if(bl->type==BL_MOB && (struct mob_data *)bl)
		return mob_db[((struct mob_data *)bl)->class].mexp;
	else if(bl->type==BL_PET && (struct pet_data *)bl)
		return mob_db[((struct pet_data *)bl)->class].mexp;
	else
		return 0;
}

// StatusChange系の所得
struct status_change *status_get_sc_data(struct block_list *bl)
{
	nullpo_retr(NULL, bl);
	if(bl->type==BL_MOB && (struct mob_data *)bl)
		return ((struct mob_data*)bl)->sc_data;
	else if(bl->type==BL_PC && (struct map_session_data *)bl)
		return ((struct map_session_data*)bl)->sc_data;
	return NULL;
}
short *status_get_sc_count(struct block_list *bl)
{
	nullpo_retr(NULL, bl);
	if(bl->type==BL_MOB && (struct mob_data *)bl)
		return &((struct mob_data*)bl)->sc_count;
	else if(bl->type==BL_PC && (struct map_session_data *)bl)
		return &((struct map_session_data*)bl)->sc_count;
	return NULL;
}
short *status_get_opt1(struct block_list *bl)
{
	nullpo_retr(0, bl);
	if(bl->type==BL_MOB && (struct mob_data *)bl)
		return &((struct mob_data*)bl)->opt1;
	else if(bl->type==BL_PC && (struct map_session_data *)bl)
		return &((struct map_session_data*)bl)->opt1;
	else if(bl->type==BL_NPC && (struct npc_data *)bl)
		return &((struct npc_data*)bl)->opt1;
	return 0;
}
short *status_get_opt2(struct block_list *bl)
{
	nullpo_retr(0, bl);
	if(bl->type==BL_MOB && (struct mob_data *)bl)
		return &((struct mob_data*)bl)->opt2;
	else if(bl->type==BL_PC && (struct map_session_data *)bl)
		return &((struct map_session_data*)bl)->opt2;
	else if(bl->type==BL_NPC && (struct npc_data *)bl)
		return &((struct npc_data*)bl)->opt2;
	return 0;
}
short *status_get_opt3(struct block_list *bl)
{
	nullpo_retr(0, bl);
	if(bl->type==BL_MOB && (struct mob_data *)bl)
		return &((struct mob_data*)bl)->opt3;
	else if(bl->type==BL_PC && (struct map_session_data *)bl)
		return &((struct map_session_data*)bl)->opt3;
	else if(bl->type==BL_NPC && (struct npc_data *)bl)
		return &((struct npc_data*)bl)->opt3;
	return 0;
}
short *status_get_option(struct block_list *bl)
{
	nullpo_retr(0, bl);
	if(bl->type==BL_MOB && (struct mob_data *)bl)
		return &((struct mob_data*)bl)->option;
	else if(bl->type==BL_PC && (struct map_session_data *)bl)
		return &((struct map_session_data*)bl)->status.option;
	else if(bl->type==BL_NPC && (struct npc_data *)bl)
		return &((struct npc_data*)bl)->option;
	return 0;
}

/*==========================================
 * ステータス異常開始
 *------------------------------------------
 */
int status_change_start(struct block_list *bl,int type,int val1,int val2,int val3,int val4,int tick,int flag)
{
	struct map_session_data *sd = NULL;
	struct status_change* sc_data;
	short *sc_count, *option, *opt1, *opt2, *opt3;
	int opt_flag = 0, calc_flag = 0, updateflag = 0, race, mode, elem, undead_flag;
	int scdef=0;

	nullpo_retr(0, bl);
	if(bl->type == BL_SKILL)
		return 0;
	nullpo_retr(0, sc_data=status_get_sc_data(bl));
	nullpo_retr(0, sc_count=status_get_sc_count(bl));
	nullpo_retr(0, option=status_get_option(bl));
	nullpo_retr(0, opt1=status_get_opt1(bl));
	nullpo_retr(0, opt2=status_get_opt2(bl));
	nullpo_retr(0, opt3=status_get_opt3(bl));


	race=status_get_race(bl);
	mode=status_get_mode(bl);
	elem=status_get_elem_type(bl);
	undead_flag=battle_check_undead(race,elem);


	if(type == SC_AETERNA && (sc_data[SC_STONE].timer != -1 || sc_data[SC_FREEZE].timer != -1) )
		return 0;

	switch(type){
		case SC_STONE:
		case SC_FREEZE:
			scdef=3+status_get_mdef(bl)+status_get_luk(bl)/3;
			break;
		case SC_STAN:
		case SC_SILENCE:
		case SC_POISON:
		case SC_DPOISON:
			scdef=3+status_get_vit(bl)+status_get_luk(bl)/3;
			break;
		case SC_SLEEP:
		case SC_BLIND:
			scdef=3+status_get_int(bl)+status_get_luk(bl)/3;
			break;
		case SC_CURSE:
			scdef=3+status_get_luk(bl);
			break;

//		case SC_CONFUSION:
		default:
			scdef=0;
	}
	if(scdef>=100)
		return 0;
	if(bl->type==BL_PC){
		sd=(struct map_session_data *)bl;
		if( sd && type == SC_ADRENALINE && !(skill_get_weapontype(BS_ADRENALINE)&(1<<sd->status.weapon)))
			return 0;

		if(SC_STONE<=type && type<=SC_BLIND){	/* カードによる耐性 */
			if( sd && sd->reseff[type-SC_STONE] > 0 && rand()%10000<sd->reseff[type-SC_STONE]){
				if(battle_config.battle_log)
					printf("PC %d skill_sc_start: cardによる異常耐性発動\n",sd->bl.id);
				return 0;
			}
		}
	}
	else if(bl->type == BL_MOB) {
	}
	else {
		if(battle_config.error_log)
			printf("status_change_start: neither MOB nor PC !\n");
		return 0;
	}

	if(type==SC_FREEZE && undead_flag && !(flag&1))
		return 0;

	if((type == SC_ADRENALINE || type == SC_WEAPONPERFECTION || type == SC_OVERTHRUST) &&
		sc_data[type].timer != -1 && sc_data[type].val2 && !val2)
		return 0;

	if(mode & 0x20 && (type==SC_STONE || type==SC_FREEZE ||
		type==SC_STAN || type==SC_SLEEP || type==SC_SILENCE || type==SC_QUAGMIRE || type == SC_DECREASEAGI || type == SC_SIGNUMCRUCIS || type == SC_PROVOKE ||
		(type == SC_BLESSING && (undead_flag || race == 6))) && !(flag&1)){
		/* ボスには効かない(ただしカードによる効果は適用される) */
		return 0;
	}
	if(type==SC_FREEZE || type==SC_STAN || type==SC_SLEEP)
		battle_stopwalking(bl,1);

	if(sc_data[type].timer != -1){	/* すでに同じ異常になっている場合タイマ解除 */
		if(sc_data[type].val1 > val1 && type != SC_COMBO && type != SC_DANCING && type != SC_DEVOTION &&
			type != SC_SPEEDPOTION0 && type != SC_SPEEDPOTION1 && type != SC_SPEEDPOTION2)
			return 0;
		if ((type >=SC_STAN && type <= SC_BLIND) || type == SC_DPOISON)
			return 0;/* 継ぎ足しができない状態異常である時は状態異常を行わない */
		if(type == SC_GRAFFITI){	//異常中にもう一度状態異常になった時に解除してから再度かかる
			status_change_end(bl,type,-1);
		}else{
			(*sc_count)--;
			delete_timer(sc_data[type].timer, status_change_timer);
			sc_data[type].timer = -1;
		}
	}

	switch(type){	/* 異常の種類ごとの処理 */
		case SC_PROVOKE:			/* プロボック */
			calc_flag = 1;
			if(tick <= 0) tick = 1000;	/* (オートバーサーク) */
			break;
		case SC_ENDURE:				/* インデュア */
			if(tick <= 0) tick = 1000 * 60;
			break;
		case SC_CONCENTRATE:		/* 集中力向上 */
			calc_flag = 1;
			break;
		case SC_BLESSING:			/* ブレッシング */
			{
				if(bl->type == BL_PC || (!undead_flag && race != 6)) {
					if(sc_data[SC_CURSE].timer!=-1 )
						status_change_end(bl,SC_CURSE,-1);
					if(sc_data[SC_STONE].timer!=-1 && sc_data[SC_STONE].val2 == 0)
						status_change_end(bl,SC_STONE,-1);
				}
				calc_flag = 1;
			}
			break;
		case SC_ANGELUS:			/* アンゼルス */
			calc_flag = 1;
			break;
		case SC_INCREASEAGI:		/* 速度上昇 */
			calc_flag = 1;
			if(sc_data[SC_DECREASEAGI].timer!=-1 )
				status_change_end(bl,SC_DECREASEAGI,-1);
			if(sc_data[SC_WINDWALK].timer!=-1 )	/* ウインドウォーク */
				status_change_end(bl,SC_WINDWALK,-1);
			break;
		case SC_DECREASEAGI:		/* 速度減少 */
			calc_flag = 1;
			if(sc_data[SC_INCREASEAGI].timer!=-1 )
				status_change_end(bl,SC_INCREASEAGI,-1);
			break;
		case SC_SIGNUMCRUCIS:		/* シグナムクルシス */
			calc_flag = 1;
//			val2 = 14 + val1;
			val2 = 10 + val1*2;
			tick = 600*1000;
			clif_emotion(bl,4);
			break;
		case SC_SLOWPOISON:
			if (sc_data[SC_POISON].timer == -1 && sc_data[SC_DPOISON].timer == -1)
				return 0;
			break;
		case SC_TWOHANDQUICKEN:		/* 2HQ */
			*opt3 |= 1;
			calc_flag = 1;
			break;
		case SC_ADRENALINE:			/* アドレナリンラッシュ */
			calc_flag = 1;
			break;
		case SC_WEAPONPERFECTION:	/* ウェポンパーフェクション */
			if(battle_config.party_skill_penaly && !val2) tick /= 5;
			break;
		case SC_OVERTHRUST:			/* オーバースラスト */
			*opt3 |= 2;
			if(battle_config.party_skill_penaly && !val2) tick /= 10;
			break;
		case SC_MAXIMIZEPOWER:		/* マキシマイズパワー(SPが1減る時間,val2にも) */
			if(bl->type == BL_PC)
				val2 = tick;
			else
				tick = 5000*val1;
			break;
		case SC_ENCPOISON:			/* エンチャントポイズン */
			calc_flag = 1;
			val2=(((val1 - 1) / 2) + 3)*100;	/* 毒付与確率 */
			skill_encchant_eremental_end(bl,SC_ENCPOISON);
			break;
		case SC_EDP:			/* エンチャントデッドリーポイズン */
		{
			struct map_session_data *sd = (struct map_session_data *)bl;
			clif_displaymessage(sd->fd, " 武器に猛毒属性が付与されました");
			val2 = val1 + 2;			/* 猛毒付与確率(%) */
			//  calc_flagは必要ない
			break;
		}
		case SC_POISONREACT:	/* ポイズンリアクト */
			break;
		case SC_IMPOSITIO:			/* インポシティオマヌス */
			calc_flag = 1;
			break;
		case SC_ASPERSIO:			/* アスペルシオ */
			skill_encchant_eremental_end(bl,SC_ASPERSIO);
			break;
		case SC_SUFFRAGIUM:			/* サフラギム */
		case SC_BENEDICTIO:			/* 聖体 */
		case SC_MAGNIFICAT:			/* マグニフィカート */
		case SC_AETERNA:			/* エーテルナ */
			break;
		case SC_ENERGYCOAT:			/* エナジーコート */
			*opt3 |= 4;
			break;
		case SC_MAGICROD:
			val2 = val1*20;
			break;
		case SC_KYRIE:				/* キリエエレイソン */
			/* アスムが掛かっていたら解除して */
			if(sc_data[SC_ASSUMPTIO].timer!=-1)
			status_change_end(bl,SC_ASSUMPTIO,-1);
		
			/* キリエを掛ける */
			val2 = status_get_max_hp(bl) * (val1 * 2 + 10) / 100;/* 耐久度 */
			val3 = (val1 / 2 + 5);	/* 回数 */
			break;
		case SC_GLORIA:				/* グロリア */
			calc_flag = 1;
			break;
		case SC_LOUD:				/* ラウドボイス */
			calc_flag = 1;
			break;
		case SC_TRICKDEAD:			/* 死んだふり */
			break;
		case SC_QUAGMIRE:			/* クァグマイア */
			calc_flag = 1;
			if(sc_data[SC_CONCENTRATE].timer!=-1 )	/* 集中力向上解除 */
				status_change_end(bl,SC_CONCENTRATE,-1);
			if(sc_data[SC_INCREASEAGI].timer!=-1 )	/* 速度上昇解除 */
				status_change_end(bl,SC_INCREASEAGI,-1);
			if(sc_data[SC_TWOHANDQUICKEN].timer!=-1 )
				status_change_end(bl,SC_TWOHANDQUICKEN,-1);
			if(sc_data[SC_SPEARSQUICKEN].timer!=-1 )
				status_change_end(bl,SC_SPEARSQUICKEN,-1);
			if(sc_data[SC_ADRENALINE].timer!=-1 )
				status_change_end(bl,SC_ADRENALINE,-1);
			if(sc_data[SC_LOUD].timer!=-1 )
				status_change_end(bl,SC_LOUD,-1);
			if(sc_data[SC_TRUESIGHT].timer!=-1 )	/* トゥルーサイト */
				status_change_end(bl,SC_TRUESIGHT,-1);
			if(sc_data[SC_WINDWALK].timer!=-1 )	/* ウインドウォーク */
				status_change_end(bl,SC_WINDWALK,-1);
			if(sc_data[SC_CARTBOOST].timer!=-1 )	/* カートブースト */
				status_change_end(bl,SC_CARTBOOST,-1);
			break;
		case SC_MAGICPOWER:			/* 魔法力増幅 */
			val2 = 1;				// 一度だけ増幅
			break;
		case SC_SACRIFICE:			/* サクリファイス */
			val2 = 5;				// 5回の攻撃で有効
			break;
		case SC_FLAMELAUNCHER:		/* フレームランチャー */
			skill_encchant_eremental_end(bl,SC_FLAMELAUNCHER);
			break;
		case SC_FROSTWEAPON:		/* フロストウェポン */
			skill_encchant_eremental_end(bl,SC_FROSTWEAPON);
			break;
		case SC_LIGHTNINGLOADER:	/* ライトニングローダー */
			skill_encchant_eremental_end(bl,SC_LIGHTNINGLOADER);
			break;
		case SC_SEISMICWEAPON:		/* サイズミックウェポン */
			skill_encchant_eremental_end(bl,SC_SEISMICWEAPON);
			break;
		case SC_DEVOTION:			/* ディボーション */
			calc_flag = 1;
			break;
		case SC_PROVIDENCE:			/* プロヴィデンス */
			calc_flag = 1;
			val2=val1*5;
			break;
		case SC_REFLECTSHIELD:
			val2=10+val1*3;
			break;
		case SC_STRIPWEAPON:
		case SC_STRIPSHIELD:
		case SC_STRIPARMOR:
		case SC_STRIPHELM:
		case SC_CP_WEAPON:
		case SC_CP_SHIELD:
		case SC_CP_ARMOR:
		case SC_CP_HELM:
			break;

		case SC_AUTOSPELL:			/* オートスペル */
			val4 = 5 + val1*2;
			break;

		case SC_VOLCANO:
			calc_flag = 1;
			val3 = val1*10;
			val4 = val1>=5?20: (val1==4?19: (val1==3?17: ( val1==2?14:10 ) ) );
			break;
		case SC_DELUGE:
			calc_flag = 1;
			val3 = val1>=5?15: (val1==4?14: (val1==3?12: ( val1==2?9:5 ) ) );
			val4 = val1>=5?20: (val1==4?19: (val1==3?17: ( val1==2?14:10 ) ) );
			break;
		case SC_VIOLENTGALE:
			calc_flag = 1;
			val3 = val1*3;
			val4 = val1>=5?20: (val1==4?19: (val1==3?17: ( val1==2?14:10 ) ) );
			break;

		case SC_SPEARSQUICKEN:		/* スピアクイッケン */
			calc_flag = 1;
			val2 = 20+val1;
			*opt3 |= 1;
			break;
		case SC_COMBO:
			break;
		case SC_BLADESTOP_WAIT:		/* 白刃取り(待ち) */
			break;
		case SC_BLADESTOP:		/* 白刃取り */
			if(val2==2) clif_bladestop((struct block_list *)val3,(struct block_list *)val4,1);
			*opt3 |= 32;
			break;

		case SC_LULLABY:			/* 子守唄 */
			val2 = 11;
			break;
		case SC_RICHMANKIM:
			break;
		case SC_ETERNALCHAOS:		/* エターナルカオス */
			calc_flag = 1;
			break;
		case SC_DRUMBATTLE:			/* 戦太鼓の響き */
			calc_flag = 1;
			val2 = (val1+1)*25;
			val3 = (val1+1)*2;
			break;
		case SC_NIBELUNGEN:			/* ニーベルングの指輪 */
			calc_flag = 1;
			val2 = (val1+2)*50;
			break;
		case SC_ROKISWEIL:			/* ロキの叫び */
			break;
		case SC_INTOABYSS:			/* 深淵の中に */
			break;
		case SC_SIEGFRIED:			/* 不死身のジークフリード */
			calc_flag = 1;
			val2 = 5 + val1*15;
			break;
		case SC_DISSONANCE:			/* 不協和音 */
			val2 = 10;
			break;
		case SC_WHISTLE:			/* 口笛 */
			calc_flag = 1;
			break;
		case SC_ASSNCROS:			/* 夕陽のアサシンクロス */
			calc_flag = 1;
			break;
		case SC_POEMBRAGI:			/* ブラギの詩 */
			break;
		case SC_APPLEIDUN:			/* イドゥンの林檎 */
			calc_flag = 1;
			break;
		case SC_UGLYDANCE:			/* 自分勝手なダンス */
			val2 = 10;
			break;
		case SC_HUMMING:			/* ハミング */
			calc_flag = 1;
			break;
		case SC_DONTFORGETME:		/* 私を忘れないで */
			calc_flag = 1;
			if(sc_data[SC_INCREASEAGI].timer!=-1 )	/* 速度上昇解除 */
				status_change_end(bl,SC_INCREASEAGI,-1);
			if(sc_data[SC_TWOHANDQUICKEN].timer!=-1 )
				status_change_end(bl,SC_TWOHANDQUICKEN,-1);
			if(sc_data[SC_SPEARSQUICKEN].timer!=-1 )
				status_change_end(bl,SC_SPEARSQUICKEN,-1);
			if(sc_data[SC_ADRENALINE].timer!=-1 )
				status_change_end(bl,SC_ADRENALINE,-1);
			if(sc_data[SC_ASSNCROS].timer!=-1 )
				status_change_end(bl,SC_ASSNCROS,-1);
			if(sc_data[SC_TRUESIGHT].timer!=-1 )	/* トゥルーサイト */
				status_change_end(bl,SC_TRUESIGHT,-1);
			if(sc_data[SC_WINDWALK].timer!=-1 )	/* ウインドウォーク */
				status_change_end(bl,SC_WINDWALK,-1);
			if(sc_data[SC_CARTBOOST].timer!=-1 )	/* カートブースト */
				status_change_end(bl,SC_CARTBOOST,-1);
			break;
		case SC_FORTUNE:			/* 幸運のキス */
			calc_flag = 1;
			break;
		case SC_SERVICE4U:			/* サービスフォーユー */
			calc_flag = 1;
			break;
		case SC_DANCING:			/* ダンス/演奏中 */
			calc_flag = 1;
			val3= tick / 1000;
			tick = 1000;
			break;

		case SC_EXPLOSIONSPIRITS:	// 爆裂波動
			calc_flag = 1;
			val2 = 75 + 25*val1;
			*opt3 |= 8;
			break;
		case SC_STEELBODY:			// 金剛
			calc_flag = 1;
			*opt3 |= 16;
			break;
		case SC_EXTREMITYFIST:		/* 阿修羅覇凰拳 */
			break;
		case SC_AUTOCOUNTER:
			val3 = val4 = 0;
			break;

		case SC_SPEEDPOTION0:		/* 増速ポーション */
		case SC_SPEEDPOTION1:
		case SC_SPEEDPOTION2:
			calc_flag = 1;
			tick = 1000 * tick;
			val2 = 5*(2+type-SC_SPEEDPOTION0);
			break;

		case SC_INCATK:		//item 682用
		case SC_INCMATK:	//item 683用
			calc_flag = 1;
			tick = 1000 * tick;
			break;
		case SC_WEDDING:	//結婚用(結婚衣裳になって歩くのが遅いとか)
			{
				time_t timer;
	
				calc_flag = 1;
				tick = 10000;
				if(!val2)
					val2 = time(&timer);
			}
			break;
		case SC_NOCHAT:	//チャット禁止状態
			{
				time_t timer;
				tick = 60000;
				if(!val2)
					val2 = time(&timer);
				updateflag = SP_MANNER;
			}
			break;
		case SC_SELFDESTRUCTION: //自爆
			clif_skillcasting(bl,bl->id, bl->id,0,0,331,skill_get_time(val2,val1));
			val3 = tick / 1000;
			tick = 1000;
			break;

		/* option1 */
		case SC_STONE:				/* 石化 */
			if(!(flag&2)) {
				int sc_def = status_get_mdef(bl)*200;
				tick = tick - sc_def;
			}
			val3 = tick/1000;
			if(val3 < 1) val3 = 1;
			tick = 5000;
			val2 = 1;
			break;
		case SC_SLEEP:				/* 睡眠 */
			if(!(flag&2)) {
//				int sc_def = 100 - (status_get_int(bl) + status_get_luk(bl)/3);
//				tick = tick * sc_def / 100;
//				if(tick < 1000) tick = 1000;
				tick = 30000;//睡眠はステータス耐性に関わらず30秒
			}
			break;
		case SC_FREEZE:				/* 凍結 */
			if(!(flag&2)) {
				int sc_def = 100 - status_get_mdef(bl);
				tick = tick * sc_def / 100;
			}
			break;
		case SC_STAN:				/* スタン（val2にミリ秒セット） */
			if(!(flag&2)) {
				int sc_def = 100 - (status_get_vit(bl) + status_get_luk(bl)/3);
				tick = tick * sc_def / 100;
			}
			break;

		/* option2 */
		case SC_DPOISON:			/* 猛毒 */
		{
			int mhp = status_get_max_hp(bl);
			int hp = status_get_hp(bl);
			// MHPの1/4以下にはならない
			if (hp > mhp>>2) {
				if(bl->type == BL_PC) {
					int diff = mhp*10/100;
					if (hp - diff < mhp>>2)
						hp = hp - (mhp>>2);
					pc_heal((struct map_session_data *)bl, -hp, 0);
				} else if(bl->type == BL_MOB) {
					struct mob_data *md = (struct mob_data *)bl;
					hp -= mhp*15/100;
					if (hp > mhp>>2)
						md->hp = hp;
					else
						md->hp = mhp>>2;
				}
			}
		}	// fall through
		case SC_POISON:				/* 毒 */
			calc_flag = 1;
			if(!(flag&2)) {
				int sc_def = 100 - (status_get_vit(bl) + status_get_luk(bl)/5);
				tick = tick * sc_def / 100;
			}
			val3 = tick/1000;
			if(val3 < 1) val3 = 1;
			tick = 1000;
			break;
		case SC_SILENCE:			/* 沈黙（レックスデビーナ） */
			if(!(flag&2)) {
				int sc_def = 100 - status_get_vit(bl);
				tick = tick * sc_def / 100;
			}
			break;
		case SC_BLIND:				/* 暗黒 */
			calc_flag = 1;
			if(!(flag&2)) {
				int sc_def = status_get_lv(bl)/10 + status_get_int(bl)/15;
				tick = 30000 - sc_def;
			}
			break;
		case SC_CURSE:
			calc_flag = 1;
			if(!(flag&2)) {
				int sc_def = 100 - status_get_vit(bl);
				tick = tick * sc_def / 100;
			}
			break;

		/* option */
		case SC_HIDING:		/* ハイディング */
			calc_flag = 1;
			if(bl->type == BL_PC) {
				val2 = tick / 1000;		/* 持続時間 */
				tick = 1000;
			}
			break;
		case SC_CLOAKING:		/* クローキング */
			if(bl->type == BL_PC)
				val2 = tick;
			else
				tick = 5000*val1;
			break;
		case SC_SIGHT:			/* サイト/ルアフ */
		case SC_RUWACH:
			val2 = tick/250;
			tick = 10;
			break;

		/* セーフティウォール、ニューマ */
		case SC_SAFETYWALL:	case SC_PNEUMA:
			tick=((struct skill_unit *)val2)->group->limit;
			break;

		/* アンクル */
		case SC_ANKLE:
			break;

		/* ウォーターボール */
		case SC_WATERBALL:
			tick=150;
			if(val1>5) //レベルが5以上の場合は25発に制限(1発目はすでに打ってるので-1)
				val3=5*5-1;
			else
				val3= (val1|1)*(val1|1)-1;
			break;

		/* スキルじゃない/時間に関係しない */
		case SC_RIDING:
			calc_flag = 1;
			tick = 600*1000;
			break;
		case SC_FALCON:	case SC_WEIGHT50:	case SC_WEIGHT90:
			tick=600*1000;
			break;

		case SC_AUTOGUARD:
			{
				int i,t;
				for(i=val2=0;i<val1;i++) {
					t = 5-(i>>1);
					val2 += (t < 0)? 1:t;
				}
			}
			break;

		case SC_DEFENDER:
			calc_flag = 1;
			val2 = 5 + val1*15;
			break;

		case SC_KEEPING:
		case SC_BARRIER:
		case SC_HALLUCINATION:
			break;
		case SC_CONCENTRATION:	/* コンセントレーション */
			*opt3 |= 1;
			calc_flag = 1;
			break;
		case SC_TENSIONRELAX:	/* テンションリラックス */
			calc_flag = 1;
			if(bl->type == BL_PC) {
				tick = 10000;
			}
			break;
		case SC_AURABLADE:		/* オーラブレード */
		case SC_PARRYING:		/* パリイング */
		case SC_HEADCRUSH:		/* ヘッドクラッシュ */
		case SC_JOINTBEAT:		/* ジョイントビート */
		case SC_MELTDOWN:		/* メルトダウン */

			//とりあえず手抜き
			break;
		case SC_WINDWALK:		/* ウインドウォーク */
			calc_flag = 1;
			val2 = (val1 / 2); //Flee上昇率
			break;
		case SC_BERSERK:		/* バーサーク */
			if(sd){
				sd->status.sp = 0;
				clif_updatestatus(sd,SP_SP);
				clif_status_change(bl,SC_INCREASEAGI,1);	/* アイコン表示 */
			}
			*opt3 |= 128;
			tick = 1000;
			calc_flag = 1;
			break;
		case SC_ASSUMPTIO:		/* アスムプティオ */
			/* キリエが掛かっていたら解除して */
			if(sc_data[SC_KYRIE].timer!=-1)
				status_change_end(bl,SC_KYRIE,-1);

			/* アスムのフラグを立てる */
			*opt3 |= 2048;
			break;
		case SC_MARIONETTE:		/* マリオネットコントロール */
			*opt3 |= 1024;
			break;

		case SC_CARTBOOST:		/* カートブースト */
		case SC_TRUESIGHT:		/* トゥルーサイト */
		case SC_SPIDERWEB:		/* スパイダーウェッブ */
			calc_flag = 1;
			break;
		case SC_REJECTSWORD:	/* リジェクトソード */
			val2 = 3; //3回攻撃を跳ね返す
			break;
		case SC_MEMORIZE:		/* メモライズ */
			val2 = 3; //3回詠唱を1/3にする
			break;
		case SC_GRAFFITI:		/* グラフィティ */
			{
				struct skill_unit_group *sg = skill_unitsetting(bl,RG_GRAFFITI,val1,val2,val3,0);
				if(sg)
					val4 = (int)sg;
			}
			break;
		case SC_SPLASHER:		/* ベナムスプラッシャー */
			break;
		default:
			if(battle_config.error_log)
				printf("UnknownStatusChange [%d]\n", type);
			return 0;
	}

	if(bl->type==BL_PC && type<SC_SENDMAX)
		clif_status_change(bl,type,1);	/* アイコン表示 */

	/* optionの変更 */
	switch(type){
		case SC_STONE:
		case SC_FREEZE:
		case SC_STAN:
		case SC_SLEEP:
			battle_stopattack(bl);	/* 攻撃停止 */
			skill_stop_dancing(bl,0);	/* 演奏/ダンスの中断 */
			{	/* 同時に掛からないステータス異常を解除 */
				int i;
				for(i = SC_STONE; i <= SC_SLEEP; i++){
					if(sc_data[i].timer != -1){
						(*sc_count)--;
						delete_timer(sc_data[i].timer, status_change_timer);
						sc_data[i].timer = -1;
					}
				}
			}
			if(type == SC_STONE)
				*opt1 = 6;
			else
				*opt1 = type - SC_STONE + 1;
			opt_flag = 1;
			break;
		case SC_POISON:
		case SC_CURSE:
		case SC_SILENCE:
		case SC_BLIND:
			*opt2 |= 1<<(type-SC_POISON);
			opt_flag = 1;
			break;
		case SC_DPOISON:	// 暫定で毒のエフェクトを使用
			*opt2 |= 1;
			opt_flag = 1;
			break;
		case SC_SIGNUMCRUCIS:
			*opt2 |= 0x40;
			opt_flag = 1;
			break;
		case SC_HIDING:
		case SC_CLOAKING:
			battle_stopattack(bl);	/* 攻撃停止 */
			*option |= ((type==SC_HIDING)?2:4);
			opt_flag =1 ;
			break;
		case SC_SIGHT:
			*option |= 1;
			opt_flag = 1;
			break;
		case SC_RUWACH:
			*option |= 8192;
			opt_flag = 1;
			break;
		case SC_WEDDING:
			*option |= 4096;
			opt_flag = 1;
	}

	if(opt_flag)	/* optionの変更 */
		clif_changeoption(bl);

	(*sc_count)++;	/* ステータス異常の数 */

	sc_data[type].val1 = val1;
	sc_data[type].val2 = val2;
	sc_data[type].val3 = val3;
	sc_data[type].val4 = val4;
	/* タイマー設定 */
	sc_data[type].timer = add_timer(
		gettick() + tick, status_change_timer, bl->id, type);

	if(bl->type==BL_PC && calc_flag)
		status_calc_pc(sd,0);	/* ステータス再計算 */

	if(bl->type==BL_PC && updateflag)
		clif_updatestatus(sd,updateflag);	/* ステータスをクライアントに送る */

	return 0;
}
/*==========================================
 * ステータス異常全解除
 *------------------------------------------
 */
int skill_status_change_clear(struct block_list *bl,int type)
{
	struct status_change* sc_data;
	short *sc_count, *option, *opt1, *opt2, *opt3;
	int i;

	nullpo_retr(0, bl);
	nullpo_retr(0, sc_data=status_get_sc_data(bl));
	nullpo_retr(0, sc_count=status_get_sc_count(bl));
	nullpo_retr(0, option=status_get_option(bl));
	nullpo_retr(0, opt1=status_get_opt1(bl));
	nullpo_retr(0, opt2=status_get_opt2(bl));
	nullpo_retr(0, opt3=status_get_opt3(bl));

	if(*sc_count == 0)
		return 0;
	for(i = 0; i < MAX_STATUSCHANGE; i++){
		if(sc_data[i].timer != -1){	/* 異常があるならタイマーを削除する */
/*
			delete_timer(sc_data[i].timer, status_change_timer);
			sc_data[i].timer = -1;

			if(!type && i<SC_SENDMAX)
				clif_status_change(bl,i,0);
*/

			status_change_end(bl,i,-1);
		}
	}
	*sc_count = 0;
	*opt1 = 0;
	*opt2 = 0;
	*opt3 = 0;
	*option &= OPTION_MASK;

	if(!type || type&2)
		clif_changeoption(bl);

	return 0;
}

/*==========================================
 * ステータス異常終了
 *------------------------------------------
 */
int status_change_end( struct block_list* bl , int type,int tid )
{
	struct status_change* sc_data;
	int opt_flag=0, calc_flag = 0;
	short *sc_count, *option, *opt1, *opt2, *opt3;

	nullpo_retr(0, bl);
	if(bl->type!=BL_PC && bl->type!=BL_MOB) {
		if(battle_config.error_log)
			printf("status_change_end: neither MOB nor PC !\n");
		return 0;
	}
	nullpo_retr(0, sc_data=status_get_sc_data(bl));
	nullpo_retr(0, sc_count=status_get_sc_count(bl));
	nullpo_retr(0, option=status_get_option(bl));
	nullpo_retr(0, opt1=status_get_opt1(bl));
	nullpo_retr(0, opt2=status_get_opt2(bl));
	nullpo_retr(0, opt3=status_get_opt3(bl));

	if((*sc_count)>0 && sc_data[type].timer!=-1 &&
		(sc_data[type].timer==tid || tid==-1) ){

		if(tid==-1)	/* タイマから呼ばれていないならタイマ削除をする */
			delete_timer(sc_data[type].timer,status_change_timer);

		/* 該当の異常を正常に戻す */
		sc_data[type].timer=-1;
		(*sc_count)--;

		switch(type){	/* 異常の種類ごとの処理 */
			case SC_PROVOKE:			/* プロボック */
			case SC_CONCENTRATE:		/* 集中力向上 */
			case SC_BLESSING:			/* ブレッシング */
			case SC_ANGELUS:			/* アンゼルス */
			case SC_INCREASEAGI:		/* 速度上昇 */
			case SC_DECREASEAGI:		/* 速度減少 */
			case SC_SIGNUMCRUCIS:		/* シグナムクルシス */
			case SC_HIDING:
			case SC_TWOHANDQUICKEN:		/* 2HQ */
			case SC_ADRENALINE:			/* アドレナリンラッシュ */
			case SC_ENCPOISON:			/* エンチャントポイズン */
			case SC_IMPOSITIO:			/* インポシティオマヌス */
			case SC_GLORIA:				/* グロリア */
			case SC_LOUD:				/* ラウドボイス */
			case SC_QUAGMIRE:			/* クァグマイア */
			case SC_PROVIDENCE:			/* プロヴィデンス */
			case SC_SPEARSQUICKEN:		/* スピアクイッケン */
			case SC_VOLCANO:
			case SC_DELUGE:
			case SC_VIOLENTGALE:
			case SC_ETERNALCHAOS:		/* エターナルカオス */
			case SC_DRUMBATTLE:			/* 戦太鼓の響き */
			case SC_NIBELUNGEN:			/* ニーベルングの指輪 */
			case SC_SIEGFRIED:			/* 不死身のジークフリード */
			case SC_WHISTLE:			/* 口笛 */
			case SC_ASSNCROS:			/* 夕陽のアサシンクロス */
			case SC_HUMMING:			/* ハミング */
			case SC_DONTFORGETME:		/* 私を忘れないで */
			case SC_FORTUNE:			/* 幸運のキス */
			case SC_SERVICE4U:			/* サービスフォーユー */
			case SC_EXPLOSIONSPIRITS:	// 爆裂波動
			case SC_STEELBODY:			// 金剛
			case SC_DEFENDER:
			case SC_SPEEDPOTION0:		/* 増速ポーション */
			case SC_SPEEDPOTION1:
			case SC_SPEEDPOTION2:
			case SC_APPLEIDUN:			/* イドゥンの林檎 */
			case SC_RIDING:
			case SC_BLADESTOP_WAIT:
			case SC_AURABLADE:			/* オーラブレード */
			case SC_PARRYING:			/* パリイング */
			case SC_CONCENTRATION:		/* コンセントレーション */
			case SC_TENSIONRELAX:		/* テンションリラックス */
			case SC_ASSUMPTIO:			/* アシャンプティオ */
			case SC_WINDWALK:		/* ウインドウォーク */
			case SC_TRUESIGHT:		/* トゥルーサイト */
			case SC_SPIDERWEB:		/* スパイダーウェッブ */
			case SC_INCATK:		//item 682用
			case SC_INCMATK:	//item 683用
			case SC_WEDDING:	//結婚用(結婚衣裳になって歩くのが遅いとか)
				calc_flag = 1;
				break;
			case SC_EDP:		// エフェクトが実装されたら削除する
			{
				struct map_session_data *sd = (struct map_session_data *)bl;
				clif_displaymessage(sd->fd, " 猛毒属性が解除されました");
				break;
			}
			case SC_BERSERK:			/* バーサーク */
				calc_flag = 1;
				clif_status_change(bl,SC_INCREASEAGI,0);	/* アイコン消去 */
				break;
			case SC_DEVOTION:		/* ディボーション */
				{
					struct map_session_data *md = map_id2sd(sc_data[type].val1);
					sc_data[type].val1=sc_data[type].val2=0;
					skill_devotion(md,bl->id);
					calc_flag = 1;
				}
				break;
			case SC_BLADESTOP:
				{
					struct status_change *t_sc_data = status_get_sc_data((struct block_list *)sc_data[type].val4);
					//片方が切れたので相手の白刃状態が切れてないのなら解除
					if(t_sc_data && t_sc_data[SC_BLADESTOP].timer!=-1)
						status_change_end((struct block_list *)sc_data[type].val4,SC_BLADESTOP,-1);

					if(sc_data[type].val2==2)
						clif_bladestop((struct block_list *)sc_data[type].val3,(struct block_list *)sc_data[type].val4,0);
				}
				break;
			case SC_DANCING:
				{
					struct map_session_data *dsd;
					struct status_change *d_sc_data;
					if(sc_data[type].val4 && (dsd=map_id2sd(sc_data[type].val4))){
						d_sc_data = dsd->sc_data;
						//合奏で相手がいる場合相手のval4を0にする
						if(d_sc_data && d_sc_data[type].timer!=-1)
							d_sc_data[type].val4=0;
					}
				}
				calc_flag = 1;
				break;
			case SC_GRAFFITI:
				{
					struct skill_unit_group *sg=(struct skill_unit_group *)sc_data[type].val4;	//val4がグラフィティのgroup_id
					if(sg)
						skill_delunitgroup(sg);
				}
				break;
			case SC_NOCHAT:	//チャット禁止状態
				{
					struct map_session_data *sd;
					if(bl->type == BL_PC && (sd=(struct map_session_data *)bl)){
//						sd->status.manner = 0;
						clif_updatestatus(sd,SP_MANNER);
					}
				}
				break;
			case SC_SPLASHER:		/* ベナムスプラッシャー */
				{
					struct block_list *src=map_id2bl(sc_data[type].val3);
					if(src && tid!=-1){
						//自分にダメージ＆周囲3*3にダメージ
						skill_castend_damage_id(src, bl,sc_data[type].val2,sc_data[type].val1,gettick(),0 );
					}
				}
				break;
			case SC_SELFDESTRUCTION:		/* 自爆 */
				{
					//自分のダメージは0にして
					struct mob_data *md;
					if(bl->type == BL_MOB && (md=(struct mob_data*)bl))
						skill_castend_damage_id(bl, bl,sc_data[type].val2,sc_data[type].val1,gettick(),0 );
				}
				break;

		/* option1 */
			case SC_FREEZE:
				sc_data[type].val3 = 0;
				break;

		/* option2 */
			case SC_POISON:				/* 毒 */
			case SC_BLIND:				/* 暗黒 */
			case SC_CURSE:
				calc_flag = 1;
				break;
		}

		if(bl->type==BL_PC && type<SC_SENDMAX)
			clif_status_change(bl,type,0);	/* アイコン消去 */

		switch(type){	/* 正常に戻るときなにか処理が必要 */
		case SC_STONE:
		case SC_FREEZE:
		case SC_STAN:
		case SC_SLEEP:
			*opt1 = 0;
			opt_flag = 1;
			break;

		case SC_POISON:
			if (sc_data[SC_DPOISON].timer != -1)	//
				break;						// DPOISON用のオプション
			*opt2 &= ~1;					// が専用に用意された場合には 
			opt_flag = 1;					// ここは削除する 
			break;							//
		case SC_CURSE:
		case SC_SILENCE:
		case SC_BLIND:
			*opt2 &= ~(1<<(type-SC_POISON));
			opt_flag = 1;
			break;
		case SC_DPOISON:
			if (sc_data[SC_POISON].timer != -1)	// DPOISON用のオプションが	
				break;							// 用意されたら削除
			*opt2 &= ~1;	// 毒状態解除
			opt_flag = 1;
			break;
		case SC_SIGNUMCRUCIS:
			*opt2 &= ~0x40;
			opt_flag = 1;
			break;

		case SC_HIDING:
		case SC_CLOAKING:
			*option &= ~((type==SC_HIDING)?2:4);
			opt_flag = 1 ;
			break;

		case SC_SIGHT:
			*option &= ~1;
			opt_flag = 1;
			break;
		case SC_WEDDING:	//結婚用(結婚衣裳になって歩くのが遅いとか)
			*option &= ~4096;
			opt_flag = 1;
			break;
		case SC_RUWACH:
			*option &= ~8192;
			opt_flag = 1;
			break;

		//opt3
		case SC_TWOHANDQUICKEN:		/* 2HQ */
		case SC_SPEARSQUICKEN:		/* スピアクイッケン */
		case SC_CONCENTRATION:		/* コンセントレーション */
			*opt3 &= ~1;
			break;
		case SC_OVERTHRUST:			/* オーバースラスト */
			*opt3 &= ~2;
			break;
		case SC_ENERGYCOAT:			/* エナジーコート */
			*opt3 &= ~4;
			break;
		case SC_EXPLOSIONSPIRITS:	// 爆裂波動
			*opt3 &= ~8;
			break;
		case SC_STEELBODY:			// 金剛
			*opt3 &= ~16;
			break;
		case SC_BLADESTOP:		/* 白刃取り */
			*opt3 &= ~32;
			break;
		case SC_BERSERK:		/* バーサーク */
			*opt3 &= ~128;
			break;
		case SC_MARIONETTE:		/* マリオネットコントロール */
			*opt3 &= ~1024;
			break;
		case SC_ASSUMPTIO:		/* アスムプティオ */
			*opt3 &= ~2048;
			break;
		}

		if(opt_flag)	/* optionの変更を伝える */
			clif_changeoption(bl);

		if(bl->type==BL_PC && calc_flag)
			status_calc_pc((struct map_session_data *)bl,0);	/* ステータス再計算 */
	}

	return 0;
}


/*==========================================
 * ステータス異常終了タイマー
 *------------------------------------------
 */
int status_change_timer(int tid, unsigned int tick, int id, int data)
{
	int type=data;
	struct block_list *bl;
	struct map_session_data *sd=NULL;
	struct status_change *sc_data;
	//short *sc_count; //使ってない？

	if( (bl=map_id2bl(id)) == NULL )
		return 0; //該当IDがすでに消滅しているというのはいかにもありそうなのでスルーしてみる
	nullpo_retr(0, sc_data=status_get_sc_data(bl));

	if(bl->type==BL_PC)
		sd=(struct map_session_data *)bl;

	//sc_count=status_get_sc_count(bl); //使ってない？

	if(sc_data[type].timer != tid) {
		if(battle_config.error_log)
			printf("status_change_timer %d != %d\n",tid,sc_data[type].timer);
		return 0;
	}


	switch(type){	/* 特殊な処理になる場合 */
	case SC_MAXIMIZEPOWER:	/* マキシマイズパワー */
	case SC_CLOAKING:		/* クローキング */
		if(sd){
			if( sd->status.sp > 0 ){	/* SP切れるまで持続 */
				sd->status.sp--;
				clif_updatestatus(sd,SP_SP);
				sc_data[type].timer=add_timer(	/* タイマー再設定 */
					sc_data[type].val2+tick, status_change_timer,
					bl->id, data);
				return 0;
			}
		}
		break;

	case SC_HIDING:		/* ハイディング */
		if(sd){		/* SPがあって、時間制限の間は持続 */
			if( sd->status.sp > 0 && (--sc_data[type].val2)>0 ){
				if(sc_data[type].val2 % (sc_data[type].val1+3) ==0 ){
					sd->status.sp--;
					clif_updatestatus(sd,SP_SP);
				}
				sc_data[type].timer=add_timer(	/* タイマー再設定 */
					1000+tick, status_change_timer,
					bl->id, data);
				return 0;
			}
		}
		break;

	case SC_SIGHT:	/* サイト */
	case SC_RUWACH:	/* ルアフ */
		{
			//const int range=10; 修正前は両方10x10だったのです
			// スキル修正: サイトの範囲=7x7 ルアフの範囲=5x5
			int range = 5;
			if ( type == SC_SIGHT ) range = 7;

			map_foreachinarea( status_change_timer_sub,
				bl->m, bl->x-range, bl->y-range, bl->x+range,bl->y+range,0,
				bl,type,tick);

			if( (--sc_data[type].val2)>0 ){
				sc_data[type].timer=add_timer(	/* タイマー再設定 */
					250+tick, status_change_timer,
					bl->id, data);
				return 0;
			}
		}
		break;

	case SC_SIGNUMCRUCIS:		/* シグナムクルシス */
		{
			int race = status_get_race(bl);
			if(race == 6 || battle_check_undead(race,status_get_elem_type(bl))) {
				sc_data[type].timer=add_timer(1000*600+tick,status_change_timer, bl->id, data );
				return 0;
			}
		}
		break;

	case SC_PROVOKE:	/* プロボック/オートバーサーク */
		if(sc_data[type].val2!=0){	/* オートバーサーク（１秒ごとにHPチェック） */
			if(sd && sd->status.hp>sd->status.max_hp>>2)	/* 停止 */
				break;
			sc_data[type].timer=add_timer( 1000+tick,status_change_timer, bl->id, data );
			return 0;
		}
		break;

	case SC_WATERBALL:	/* ウォーターボール */
		{
			struct block_list *target=map_id2bl(sc_data[type].val2);
			if(target==NULL || target->prev==NULL)
				break;
			skill_attack(BF_MAGIC,bl,bl,target,WZ_WATERBALL,sc_data[type].val1,tick,0);
			if((--sc_data[type].val3)>0) {
				sc_data[type].timer=add_timer( 150+tick,status_change_timer, bl->id, data );
				return 0;
			}
		}
		break;

	case SC_DISSONANCE:	/* 不協和音 */
		if( (--sc_data[type].val2)>0){
			struct skill_unit *unit=
				(struct skill_unit *)sc_data[type].val4;
			struct block_list *src;

			if(!unit || !unit->group)
				break;
			src=map_id2bl(unit->group->src_id);
			if(!src)
				break;
			skill_attack(BF_MISC,src,&unit->bl,bl,unit->group->skill_id,sc_data[type].val1,tick,0);
			sc_data[type].timer=add_timer(skill_get_time2(unit->group->skill_id,unit->group->skill_lv)+tick,
				status_change_timer, bl->id, data );
			return 0;
		}
		break;

	case SC_LULLABY:	/* 子守唄 */
		if( (--sc_data[type].val2)>0){
			struct skill_unit *unit=
				(struct skill_unit *)sc_data[type].val4;
			if(!unit || !unit->group || unit->group->src_id==bl->id)
				break;
			skill_additional_effect(bl,bl,unit->group->skill_id,sc_data[type].val1,BF_LONG|BF_SKILL|BF_MISC,tick);
			sc_data[type].timer=add_timer(skill_get_time(unit->group->skill_id,unit->group->skill_lv)/10+tick,
				status_change_timer, bl->id, data );
			return 0;
		}
		break;

	case SC_STONE:
		if(sc_data[type].val2 != 0) {
			short *opt1 = status_get_opt1(bl);
			sc_data[type].val2 = 0;
			sc_data[type].val4 = 0;
			battle_stopwalking(bl,1);
			if(opt1) {
				*opt1 = 1;
				clif_changeoption(bl);
			}
			sc_data[type].timer=add_timer(1000+tick,status_change_timer, bl->id, data );
			return 0;
		}
		else if( (--sc_data[type].val3) > 0) {
			int hp = status_get_max_hp(bl);
			if((++sc_data[type].val4)%5 == 0 && status_get_hp(bl) > hp>>2) {
				hp = hp/100;
				if(hp < 1) hp = 1;
				if(bl->type == BL_PC)
					pc_heal((struct map_session_data *)bl,-hp,0);
				else if(bl->type == BL_MOB){
					struct mob_data *md;
					if((md=((struct mob_data *)bl)) == NULL)
						break;
					md->hp -= hp;
				}
			}
			sc_data[type].timer=add_timer(1000+tick,status_change_timer, bl->id, data );
			return 0;
		}
		break;
	case SC_POISON:
		if (sc_data[SC_SLOWPOISON].timer == -1 && (--sc_data[type].val3) > 0) {
			int hp = status_get_max_hp(bl);
			if (status_get_hp(bl) > hp>>2) {
				if(bl->type == BL_PC) {
					hp = 3 + hp*3/200;
					pc_heal((struct map_session_data *)bl,-hp,0);
				} else if (bl->type == BL_MOB) {
					struct mob_data *md;
					if ((md=((struct mob_data *)bl)) == NULL)
						break;
					hp = 3 + hp/200;
					md->hp -= hp;
				}
			}
		}
		if (sc_data[type].val3 > 0)
			sc_data[type].timer=add_timer(1000+tick,status_change_timer, bl->id, data );
		break;
	case SC_DPOISON:
		if (sc_data[SC_SLOWPOISON].timer == -1 && (--sc_data[type].val3) > 0) {
			int hp = status_get_max_hp(bl);
			if (status_get_hp(bl) > hp>>2) {
				if(bl->type == BL_PC) {
					hp = 3 + hp/50;
					pc_heal((struct map_session_data *)bl, -hp, 0);
				} else if (bl->type == BL_MOB) {
					struct mob_data *md;
					if ((md=((struct mob_data *)bl)) == NULL)
						break;
					hp = 3 + hp/100;
					md->hp -= hp;
				}
			}
		}
		if (sc_data[type].val3 > 0)
			sc_data[type].timer=add_timer(1000+tick,status_change_timer, bl->id, data );
		break;
	case SC_TENSIONRELAX:	/* テンションリラックス */
		if(sd){		/* SPがあって、HPが満タンでなければ継続 */
			if( sd->status.sp > 12 && sd->status.max_hp > sd->status.hp ){
				if(sc_data[type].val2 % (sc_data[type].val1+3) ==0 ){
					sd->status.sp -= 12;
					clif_updatestatus(sd,SP_SP);
				}
				sc_data[type].timer=add_timer(	/* タイマー再設定 */
					10000+tick, status_change_timer,
					bl->id, data);
				return 0;
			}
			if(sd->status.max_hp <= sd->status.hp)
				status_change_end(&sd->bl,SC_TENSIONRELAX,-1);
		}
		break;

	/* 時間切れ無し？？ */
	case SC_AETERNA:
	case SC_TRICKDEAD:
	case SC_RIDING:
	case SC_FALCON:
	case SC_WEIGHT50:
	case SC_WEIGHT90:
	case SC_MAGICPOWER:		/* 魔法力増幅 */
	case SC_REJECTSWORD:	/* リジェクトソード */
	case SC_MEMORIZE:		/* メモライズ */
	case SC_SACRIFICE:		/* サクリファイス */
		sc_data[type].timer=add_timer( 1000*600+tick,status_change_timer, bl->id, data );
		return 0;

	case SC_DANCING: //ダンススキルの時間SP消費
		{
			int s=0;
			if(sd){
				if(sd->status.sp > 0 && (--sc_data[type].val3)>0){
					switch(sc_data[type].val1){
					case BD_RICHMANKIM:				/* ニヨルドの宴 3秒にSP1 */
					case BD_DRUMBATTLEFIELD:		/* 戦太鼓の響き 3秒にSP1 */
					case BD_RINGNIBELUNGEN:			/* ニーベルングの指輪 3秒にSP1 */
					case BD_SIEGFRIED:				/* 不死身のジークフリード 3秒にSP1 */
					case BA_DISSONANCE:				/* 不協和音 3秒でSP1 */
					case BA_ASSASSINCROSS:			/* 夕陽のアサシンクロス 3秒でSP1 */
					case DC_UGLYDANCE:				/* 自分勝手なダンス 3秒でSP1 */
						s=3;
						break;
					case BD_LULLABY:				/* 子守歌 4秒にSP1 */
					case BD_ETERNALCHAOS:			/* 永遠の混沌 4秒にSP1 */
					case BD_ROKISWEIL:				/* ロキの叫び 4秒にSP1 */
					case DC_FORTUNEKISS:			/* 幸運のキス 4秒でSP1 */
						s=4;
						break;
					case BD_INTOABYSS:				/* 深淵の中に 5秒にSP1 */
					case BA_WHISTLE:				/* 口笛 5秒でSP1 */
					case DC_HUMMING:				/* ハミング 5秒でSP1 */
					case BA_POEMBRAGI:				/* ブラギの詩 5秒でSP1 */
					case DC_SERVICEFORYOU:			/* サービスフォーユー 5秒でSP1 */
						s=5;
						break;
					case BA_APPLEIDUN:				/* イドゥンの林檎 6秒でSP1 */
						s=6;
						break;
					case DC_DONTFORGETME:			/* 私を忘れないで… 10秒でSP1 */
					case CG_MOONLIT:				/* 月明りの泉に落ちる花びら 10秒でSP1？ */
						s=10;
						break;
					}
					if(s && ((sc_data[type].val3 % s) == 0)){
						sd->status.sp--;
						clif_updatestatus(sd,SP_SP);
					}
					sc_data[type].timer=add_timer(	/* タイマー再設定 */
						1000+tick, status_change_timer,
						bl->id, data);
					return 0;
				}
			}
		}
		break;
	case SC_BERSERK:		/* バーサーク */
		if(sd){		/* HPが100以上なら継続 */
			if( (sd->status.hp - sd->status.hp/100) > 100 ){
				sd->status.hp -= sd->status.hp/100;
				clif_updatestatus(sd,SP_HP);
				sc_data[type].timer=add_timer(	/* タイマー再設定 */
					10000+tick, status_change_timer,
					bl->id, data);
				return 0;
			}
		}
		break;
	case SC_WEDDING:	//結婚用(結婚衣裳になって歩くのが遅いとか)
		if(sd){
			time_t timer;
			if(time(&timer) < ((sc_data[type].val2) + 3600)){	//1時間たっていないので継続
				sc_data[type].timer=add_timer(	/* タイマー再設定 */
					10000+tick, status_change_timer,
					bl->id, data);
				return 0;
			}
		}
		break;
	case SC_NOCHAT:	//チャット禁止状態
		if(sd){
			time_t timer;
			if((++sd->status.manner) && time(&timer) < ((sc_data[type].val2) + 60*(0-sd->status.manner))){	//開始からstatus.manner分経ってないので継続
				clif_updatestatus(sd,SP_MANNER);
				sc_data[type].timer=add_timer(	/* タイマー再設定(60秒) */
					60000+tick, status_change_timer,
					bl->id, data);
				return 0;
			}
		}
		break;
	case SC_SELFDESTRUCTION:		/* 自爆 */
		if(--sc_data[type].val3>0){
			struct mob_data *md;
			if(bl->type==BL_MOB && (md=(struct mob_data *)bl) && md->speed > 250){
				md->speed -= 250;
				md->next_walktime=tick;
			}
			sc_data[type].timer=add_timer(	/* タイマー再設定 */
				1000+tick, status_change_timer,
				bl->id, data);
				return 0;
		}
		break;
	}

	return status_change_end( bl,type,tid );
}

/*==========================================
 * ステータス異常タイマー範囲処理
 *------------------------------------------
 */
int status_change_timer_sub(struct block_list *bl, va_list ap )
{
	struct block_list *src;
	int type;
	unsigned int tick;

	nullpo_retr(0, bl);
	nullpo_retr(0, ap);
	nullpo_retr(0, src=va_arg(ap,struct block_list*));
	type=va_arg(ap,int);
	tick=va_arg(ap,unsigned int);

	if(bl->type!=BL_PC && bl->type!=BL_MOB)
		return 0;

	switch( type ){
	case SC_SIGHT:	/* サイト */
	case SC_CONCENTRATE:
		if( (*status_get_option(bl))&6 ){
			status_change_end( bl, SC_HIDING, -1);
			status_change_end( bl, SC_CLOAKING, -1);
		}
		break;
	case SC_RUWACH:	/* ルアフ */
		if( (*status_get_option(bl))&6 ){
			status_change_end( bl, SC_HIDING, -1);
			status_change_end( bl, SC_CLOAKING, -1);
			if(battle_check_target( src,bl, BCT_ENEMY ) > 0) {
				struct status_change *sc_data = status_get_sc_data(bl);
				skill_attack(BF_MAGIC,src,src,bl,AL_RUWACH,sc_data[type].val1,tick,0);
			}
		}
		break;
	}
	return 0;
}


static int status_calc_sigma(void)
{
	int i,j,k;

	for(i=0;i<MAX_PC_CLASS;i++) {
		memset(hp_sigma_val[i],0,sizeof(hp_sigma_val[i]));
		for(k=0,j=2;j<=MAX_LEVEL;j++) {
			k += hp_coefficient[i]*j + 50;
			k -= k%100;
			hp_sigma_val[i][j-1] = k;
		}
	}
	return 0;
}

int status_readdb(void) {
	int i,j,k;
	FILE *fp;
	char line[1024],*p;

	// JOB補正数値１
	fp=fopen("db/job_db1.txt","r");
	if(fp==NULL){
		printf("can't read db/job_db1.txt\n");
		return 1;
	}
	i=0;
	while(fgets(line,1020,fp)){
		char *split[50];
		if(line[0]=='/' && line[1]=='/')
			continue;
		for(j=0,p=line;j<21 && p;j++){
			split[j]=p;
			p=strchr(p,',');
			if(p) *p++=0;
		}
		if(j<21)
			continue;
		max_weight_base[i]=atoi(split[0]);
		hp_coefficient[i]=atoi(split[1]);
		hp_coefficient2[i]=atoi(split[2]);
		sp_coefficient[i]=atoi(split[3]);
		for(j=0;j<17;j++)
			aspd_base[i][j]=atoi(split[j+4]);
		i++;
		if(i==MAX_PC_CLASS)
			break;
	}
	fclose(fp);
	printf("read db/job_db1.txt done\n");

	// JOBボーナス
	fp=fopen("db/job_db2.txt","r");
	if(fp==NULL){
		printf("can't read db/job_db2.txt\n");
		return 1;
	}
	i=0;
	while(fgets(line,1020,fp)){
		if(line[0]=='/' && line[1]=='/')
			continue;
		for(j=0,p=line;j<MAX_LEVEL && p;j++){
			if(sscanf(p,"%d",&k)==0)
				break;
			job_bonus[0][i][j]=k;
			job_bonus[2][i][j]=k; //養子職のボーナスは分からないので仮
			p=strchr(p,',');
			if(p) p++;
		}
		i++;
		if(i==MAX_PC_CLASS)
			break;
	}
	fclose(fp);
	printf("read db/job_db2.txt done\n");

	// JOBボーナス2 転生職用
	fp=fopen("db/job_db2-2.txt","r");
	if(fp==NULL){
		printf("can't read db/job_db2-2.txt\n");
		return 1;
	}
	i=0;
	while(fgets(line,1020,fp)){
		if(line[0]=='/' && line[1]=='/')
			continue;
		for(j=0,p=line;j<MAX_LEVEL && p;j++){
			if(sscanf(p,"%d",&k)==0)
				break;
			job_bonus[1][i][j]=k;
			p=strchr(p,',');
			if(p) p++;
		}
		i++;
		if(i==MAX_PC_CLASS)
			break;
	}
	fclose(fp);
	printf("read db/job_db2-2.txt done\n");

	// 精錬データテーブル
	for(i=0;i<5;i++){
		for(j=0;j<10;j++)
			percentrefinery[i][j]=100;
		refinebonus[i][0]=0;
		refinebonus[i][1]=0;
		refinebonus[i][2]=10;
	}
	fp=fopen("db/refine_db.txt","r");
	if(fp==NULL){
		printf("can't read db/refine_db.txt\n");
		return 1;
	}
	i=0;
	while(fgets(line,1020,fp)){
		char *split[16];
		if(line[0]=='/' && line[1]=='/')
			continue;
		if(atoi(line)<=0)
			continue;
		memset(split,0,sizeof(split));
		for(j=0,p=line;j<16 && p;j++){
			split[j]=p;
			p=strchr(p,',');
			if(p) *p++=0;
		}
		refinebonus[i][0]=atoi(split[0]);	// 精錬ボーナス
		refinebonus[i][1]=atoi(split[1]);	// 過剰精錬ボーナス
		refinebonus[i][2]=atoi(split[2]);	// 安全精錬限界
		for(j=0;j<10 && split[j];j++)
			percentrefinery[i][j]=atoi(split[j+3]);
		i++;
	}
	printf("read db/refine_db.txt done\n");

	// サイズ補正テーブル
	for(i=0;i<3;i++)
		for(j=0;j<20;j++)
			atkmods[i][j]=100;
	fp=fopen("db/size_fix.txt","r");
	if(fp==NULL){
		printf("can't read db/size_fix.txt\n");
		return 1;
	}
	i=0;
	while(fgets(line,1020,fp)){
		char *split[20];
		if(line[0]=='/' && line[1]=='/')
			continue;
		if(atoi(line)<=0)
			continue;
		memset(split,0,sizeof(split));
		for(j=0,p=line;j<20 && p;j++){
			split[j]=p;
			p=strchr(p,',');
			if(p) *p++=0;
		}
		for(j=0;j<20 && split[j];j++)
			atkmods[i][j]=atoi(split[j]);
		i++;
	}
	printf("read db/size_fix.txt done\n");

	return 0;
}

/*==========================================
 * スキル関係初期化処理
 *------------------------------------------
 */
int do_init_status(void)
{
	add_timer_func_list(status_change_timer,"status_change_timer");
	status_readdb();
	status_calc_sigma();
	return 0;
}
