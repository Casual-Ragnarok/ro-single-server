#ifndef _INT_STORAGE_H_
#define _INT_STORAGE_H_

int inter_storage_init();
int inter_storage_save();
int inter_guild_storage_save();
int inter_storage_delete(int account_id);
int inter_guild_storage_delete(int guild_id);

int inter_storage_parse_frommap(int fd);

void do_final_int_storage(void);

extern char storage_txt[1024];
extern char guild_storage_txt[1024];

#endif
