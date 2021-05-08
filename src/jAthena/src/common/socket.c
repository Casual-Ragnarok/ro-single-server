// $Id: socket.c,v 1.2 2004/09/15 00:17:17 running_pinata Exp $
// original : core.c 2003/02/26 18:03:12 Rev 1.7

#include <stdio.h>
#include <stdlib.h>
#include <sys/types.h>
#ifdef _WIN32
	#include <winsock.h>
	#pragma comment(lib,"ws2_32.lib")
#else
	#include <sys/socket.h>
	#include <netinet/in.h>
	#include <netinet/tcp.h>
	#include <unistd.h>
	#include <sys/time.h>
#endif
#include <fcntl.h>
#include <string.h>

#include "socket.h"
#include "malloc.h"

// socket.h でdefine されたcloseを置き換え
#undef close
#ifdef _WIN32
	#define close(id) do{ if(session[id]) closesocket(session[id]->socket); } while(0);
#endif

#ifdef MEMWATCH
#include "memwatch.h"
#endif

fd_set readfds;
int fd_max;

int rfifo_size = 65536;
int wfifo_size = 65536;

struct socket_data *session[FD_SETSIZE];

static int null_parse(int fd);
static int (*default_func_parse)(int) = null_parse;
static int (*default_func_destruct)(int) = NULL;

/*======================================
 *	CORE : Set function
 *--------------------------------------
 */
void set_defaultparse(int (*defaultparse)(int))
{
	default_func_parse = defaultparse;
}

void set_sock_destruct(int (*func_destruct)(int))
{
	default_func_destruct = func_destruct;
}

/*======================================
 *	CORE : Socket Sub Function
 *--------------------------------------
 */

static int recv_to_fifo(int fd)
{
	int len;

	//printf("recv_to_fifo : %d %d\n",fd,session[fd]->eof);
	if(session[fd]->eof)
		return -1;
#ifdef _WIN32
	len=recv(session[fd]->socket,session[fd]->rdata+session[fd]->rdata_size,RFIFOSPACE(fd),0);
#else
	len=recv(fd,session[fd]->rdata+session[fd]->rdata_size,RFIFOSPACE(fd),0);
#endif
	//{ int i; printf("recv %d : ",fd); for(i=0;i<len;i++){ printf("%02x ",RFIFOB(fd,session[fd]->rdata_size+i)); } printf("\n");}
	if(len>0){
		session[fd]->rdata_size+=len;
#ifdef _WIN32
	} else if(len == 0 || len == SOCKET_ERROR){
		printf("set eof :%d\n",fd);
		session[fd]->eof=1;
#else
	} else if(len<=0){
		printf("set eof :%d\n",fd);
		session[fd]->eof=1;
#endif
	}
	return 0;
}

static int send_from_fifo(int fd)
{
	int len;

	//printf("send_from_fifo : %d\n",fd);
	if(session[fd]->eof)
		return -1;
#ifdef _WIN32
	len=send(session[fd]->socket,session[fd]->wdata,session[fd]->wdata_size,0);
#else
	len=send(fd,session[fd]->wdata,session[fd]->wdata_size,0);
#endif
	//{ int i; printf("send %d : ",fd);  for(i=0;i<len;i++){ printf("%02x ",session[fd]->wdata[i]); } printf("\n");}
	if(len>0){
		if(len<session[fd]->wdata_size){
			memmove(session[fd]->wdata,session[fd]->wdata+len,session[fd]->wdata_size-len);
			session[fd]->wdata_size-=len;
		} else {
			session[fd]->wdata_size=0;
		}
#ifdef _WIN32
	} else if(len == 0 || len == SOCKET_ERROR) {
		printf("set eof :%d\n",fd);
		session[fd]->eof=1;
#else
	} else {
		printf("set eof :%d\n",fd);
		session[fd]->eof=1;
#endif
	}
	return 0;
}

static int null_parse(int fd)
{
	printf("null_parse : %d\n",fd);
	RFIFOSKIP(fd,RFIFOREST(fd));
	return 0;
}

/*======================================
 *	CORE : Socket Function
 *--------------------------------------
 */

static int connect_client(int listen_fd)
{
	int fd;
	struct sockaddr_in client_address;
	int len;
	int result;
	int yes = 1; // reuse fix
#ifdef _WIN32
	SOCKET socket;
#endif
	//printf("connect_client : %d\n",listen_fd);

	len=sizeof(client_address);

#ifdef _WIN32
	result = 1;
	fd=accept(session[listen_fd]->socket,(struct sockaddr*)&client_address,&len);
	ioctlsocket(fd,FIONBIO,&result);
#else
	fd=accept(listen_fd,(struct sockaddr*)&client_address,&len);
	result = fcntl(fd,F_SETFL,O_NONBLOCK);
#endif

//	setsockopt(fd,SOL_SOCKET,SO_REUSEADDR,NULL,0);
	setsockopt(fd,SOL_SOCKET,SO_REUSEADDR,(char *)&yes,sizeof yes); // reuse fix
#ifdef SO_REUSEPORT
//	setsockopt(fd,SOL_SOCKET,SO_REUSEPORT,NULL,0);
	setsockopt(fd,SOL_SOCKET,SO_REUSEPORT,(char *)&yes,sizeof yes); //reuse fix
#endif
//	setsockopt(fd,IPPROTO_TCP,TCP_NODELAY,NULL,0);
	setsockopt(fd,IPPROTO_TCP,TCP_NODELAY,(char *)&yes,sizeof yes); // reuse fix

	if(fd==-1){
		perror("accept");
	} else {
		FD_SET(fd,&readfds);
	}
#ifdef _WIN32
	result = 1;
	ioctlsocket(fd,FIONBIO,&result);

	socket = fd;
	fd=2;
	while(session[fd] != NULL && fd<fd_max) {
		fd++;
	}
#else
	result = fcntl(fd, F_SETFL, O_NONBLOCK);
#endif

	session[fd] = (struct socket_data *)aCalloc(1,sizeof(*session[fd]));
	session[fd]->rdata       = (unsigned char *)aCalloc(1,rfifo_size);
	session[fd]->wdata       = (unsigned char *)aCalloc(1,wfifo_size);
	session[fd]->max_rdata   = rfifo_size;
	session[fd]->max_wdata   = wfifo_size;
	session[fd]->func_recv   = recv_to_fifo;
	session[fd]->func_send   = send_from_fifo;
	session[fd]->func_parse  = default_func_parse;
	session[fd]->client_addr = client_address;
#if _WIN32
	session[fd]->socket      = socket;
#endif
	session[fd]->func_destruct = default_func_destruct;
	if(fd_max<=fd) fd_max=fd+1;

  //printf("new_session : %d %d\n",fd,session[fd]->eof);
  return fd;
}

int make_listen_port(int port)
{
	struct sockaddr_in server_address;
	int fd;
	int result;
	int yes = 1; // reuse fix
#ifdef _WIN32
	SOCKET sock;
#endif

	fd = socket( AF_INET, SOCK_STREAM, 0 );
#ifdef _WIN32
	result = 1;
	ioctlsocket(fd,FIONBIO,&result);
#else
	result = fcntl(fd,F_SETFL,O_NONBLOCK);
#endif

#ifndef _WIN32
//	setsockopt(fd,SOL_SOCKET,SO_REUSEADDR,NULL,0);
	setsockopt(fd,SOL_SOCKET,SO_REUSEADDR,(char *)&yes,sizeof yes); // reuse fix
#ifdef SO_REUSEPORT
//	setsockopt(fd,SOL_SOCKET,SO_REUSEPORT,NULL,0);
	setsockopt(fd,SOL_SOCKET,SO_REUSEPORT,(char *)&yes,sizeof yes); //reuse fix
#endif
//	setsockopt(fd,IPPROTO_TCP,TCP_NODELAY,NULL,0);
	setsockopt(fd,IPPROTO_TCP,TCP_NODELAY,(char *)&yes,sizeof yes); // reuse fix
#endif

	server_address.sin_family      = AF_INET;
	server_address.sin_addr.s_addr = htonl( INADDR_ANY );
	server_address.sin_port        = htons((unsigned short)port);

	result = bind(fd, (struct sockaddr*)&server_address, sizeof(server_address));
	if( result == -1 ) {
		perror("bind");
		exit(1);
	}
	result = listen( fd, 5 );
	if( result == -1 ) { /* error */
		perror("listen");
		exit(1);
	}

	FD_SET(fd, &readfds );

#ifdef _WIN32
	sock = fd;
	fd=2;
	while(session[fd] != NULL && fd<fd_max) {
		fd++;
	}
#endif

	session[fd] = (struct socket_data *)aCalloc(1,sizeof(*session[fd]));
	session[fd]->func_recv = connect_client;
#ifdef _WIN32
	session[fd]->socket = sock;
#endif
	if(fd_max<=fd) fd_max=fd+1;

	return fd;
}

int make_connection(long ip,int port)
{
	struct sockaddr_in server_address;
	int fd;
	int result;
	int yes = 1; // reuse fix
#ifdef _WIN32
	SOCKET sock;
#endif

	fd = socket( AF_INET, SOCK_STREAM, 0 );

	//	setsockopt(fd,SOL_SOCKET,SO_REUSEADDR,NULL,0);
	setsockopt(fd,SOL_SOCKET,SO_REUSEADDR,(char *)&yes,sizeof yes); // reuse fix
#ifdef SO_REUSEPORT
//	setsockopt(fd,SOL_SOCKET,SO_REUSEPORT,NULL,0);
	setsockopt(fd,SOL_SOCKET,SO_REUSEPORT,(char *)&yes,sizeof yes); //reuse fix
#endif
//	setsockopt(fd,IPPROTO_TCP,TCP_NODELAY,NULL,0);
	setsockopt(fd,IPPROTO_TCP,TCP_NODELAY,(char *)&yes,sizeof yes); // reuse fix

	server_address.sin_family = AF_INET;
	server_address.sin_addr.s_addr = ip;
	server_address.sin_port = htons((unsigned short)port);

#ifdef _WIN32
	result = 1;
	ioctlsocket(fd,FIONBIO,&result);
#else
	result = fcntl(fd, F_SETFL, O_NONBLOCK);
#endif
	result = connect(fd, (struct sockaddr *)(&server_address),sizeof(struct sockaddr_in));
	FD_SET(fd,&readfds);

#ifdef _WIN32
	sock = fd;
	fd=2;
	while(session[fd] != NULL && fd<fd_max) {
		fd++;
	}
#endif

	session[fd] = (struct socket_data *)aCalloc(1,sizeof(*session[fd]));

	session[fd]->rdata      = (unsigned char *)aCalloc(1,rfifo_size);
	session[fd]->wdata      = (unsigned char *)aCalloc(1,wfifo_size);
	session[fd]->max_rdata  = rfifo_size;
	session[fd]->max_wdata  = wfifo_size;
	session[fd]->func_recv  = recv_to_fifo;
	session[fd]->func_send  = send_from_fifo;
	session[fd]->func_parse = default_func_parse;
#if _WIN32
	session[fd]->socket = sock;
#endif
	session[fd]->func_destruct = default_func_destruct;
	if(fd_max<=fd) fd_max=fd+1;

	return fd;
}

int delete_session(int fd)
{
	if(fd<0 || fd>=FD_SETSIZE)
		return -1;
#ifdef _WIN32
	FD_CLR(session[fd]->socket,&readfds);
#else
	FD_CLR((unsigned int)fd,&readfds);
#endif
	if(session[fd]){
		// ２重呼び出しの防止
		if(session[fd]->flag_destruct) {
			return 0;
		}
		session[fd]->flag_destruct = 1;
		// デストラクタを呼び出す
		if(session[fd]->func_destruct) {
			session[fd]->func_destruct(fd);
		}
		close(fd);
		if(session[fd]->rdata)
			free(session[fd]->rdata);
		if(session[fd]->wdata)
			free(session[fd]->wdata);
		if(session[fd]->session_data)
			free(session[fd]->session_data);
		free(session[fd]);
	}
	session[fd]=NULL;
	//printf("delete_session:%d\n",fd);
	return 0;
}

int realloc_fifo(int fd,int rfifo_size,int wfifo_size)
{
	struct socket_data *s=session[fd];
	if( s->max_rdata != rfifo_size && s->rdata_size < rfifo_size){
	
		s->rdata      = (unsigned char *)aRealloc(s->rdata, rfifo_size);
		s->max_rdata  = rfifo_size;
	}
	if( s->max_wdata != wfifo_size && s->wdata_size < wfifo_size){
		s->wdata      = (unsigned char *)aRealloc(s->wdata, wfifo_size);
		s->max_wdata  = wfifo_size;
	}
	return 0;
}

int WFIFOSET(int fd,int len)
{
	struct socket_data *s=session[fd];
	if( s->wdata_size+len+16384 > s->max_wdata ){
		realloc_fifo(fd,s->max_rdata, s->max_wdata <<1 );
		printf("socket: %d wdata expanded to %d bytes.\n",fd, s->max_wdata);
	}
	s->wdata_size=(s->wdata_size+(len)+2048 < s->max_wdata) ?
		 s->wdata_size+len : (printf("socket: %d wdata lost !!\n",fd),s->wdata_size);
	return 0;
}

int do_sendrecv(int next)
{
	fd_set rfd,wfd;
	struct timeval timeout;
	int ret,i;

	rfd=readfds;
	FD_ZERO(&wfd);
	for(i=0;i<fd_max;i++){
#ifndef _WIN32
		if(!session[i] && FD_ISSET(i,&readfds)){
			printf("force clr fds %d\n",i);
			FD_CLR((unsigned int)i,&readfds);
			continue;
		}
#endif
		if(!session[i])
			continue;
		if(session[i]->wdata_size)
#ifdef _WIN32
			FD_SET(session[i]->socket,&wfd);
#else
			FD_SET(i,&wfd);
#endif
	}
	timeout.tv_sec  = next/1000;
	timeout.tv_usec = next%1000*1000;
	ret = select(fd_max,&rfd,&wfd,NULL,&timeout);
	if(ret<=0) {
		return 0;
	}
	for(i=0;i<fd_max;i++){
		if(!session[i])
			continue;
#ifdef _WIN32
		if(FD_ISSET(session[i]->socket,&wfd)){
#else
		if(FD_ISSET(i,&wfd)){
#endif
			//printf("write:%d\n",i);
			if(session[i]->func_send)
				session[i]->func_send(i);
		}
#ifdef _WIN32
		if(FD_ISSET(session[i]->socket,&rfd)){
#else
		if(FD_ISSET(i,&rfd)){
#endif
			//printf("read:%d\n",i);
			if(session[i]->func_recv)
				session[i]->func_recv(i);
		}
	}
	return 0;
}

int do_parsepacket(void)
{
	int i;
	for(i=0;i<fd_max;i++){
		if(!session[i])
			continue;
		if(session[i]->eof) {
			delete_session(i);
		} else {
			if(session[i]->func_parse && session[i]->rdata_size)
				session[i]->func_parse(i);
			if(!session[i]->eof) {
				RFIFOFLUSH(i);
			}
		}
	}
	return 0;
}

int parsepacket_timer(int tid, unsigned int tick, int id, int data) {
	do_parsepacket();
	return 0;
}

void do_socket(void)
{
	FD_ZERO(&readfds);
#ifdef _WIN32
	{
		WSADATA  Data;
		if(WSAStartup(MAKEWORD(1,1),&Data) != 0) {
			MessageBox(NULL,"Winsock Dll Load Error","socket.c",MB_OK);
			exit(1);
		}
	}
#endif
}
