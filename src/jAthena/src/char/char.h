#ifndef _CHAR_H_
#define _CHAR_H_

#define MAX_MAP_SERVERS 30

#define CHAR_CONF_NAME	"conf/char_athena.conf"

#define DEFAULT_AUTOSAVE_INTERVAL 300*1000

struct mmo_map_server{
	long ip;
	short port;
	int users;
	char map[MAX_MAP_PER_SERVER][16];
	short active;
};

int mapif_sendall(unsigned char *buf,unsigned int len);
int mapif_sendallwos(int fd,unsigned char *buf,unsigned int len);
int mapif_send(int fd,unsigned char *buf,unsigned int len);

extern int autosave_interval;

#endif
