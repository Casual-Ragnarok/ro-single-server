#ifndef _LOGIN_H_
#define _LOGIN_H_

#define MAX_SERVERS 30

#define LOGIN_CONF_NAME	"conf/login_athena.conf"

#define PASSWORDENC		3	// 暗号化パスワードに対応させるとき定義する
							// passwordencryptのときは1、
							// passwordencrypt2のときは2にする。
							// 3にすると両方に対応

#define START_ACCOUNT_NUM	  2000000
#define END_ACCOUNT_NUM		100000000

struct mmo_account {
	char* userid;
	char* passwd;
	int passwdenc;

	long account_id;
	long login_id1;
	long login_id2;
	long char_id;
	char lastlogin[24];
	int sex;
};

struct mmo_char_server {
	char name[20];
	long ip;
	short port;
	int users;
	int maintenance;
	int new;
};


#endif
