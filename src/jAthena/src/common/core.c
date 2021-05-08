// $Id: core.c,v 1.1.1.1 2004/06/24 19:29:20 running_pinata Exp $
// original : core.c 2003/02/26 18:03:12 Rev 1.7

#include <stdio.h>
#include <stdlib.h>
#ifndef _WIN32
	#include <unistd.h>
	#include <signal.h>
#endif

#include "core.h"
#include "socket.h"
#include "timer.h"

#ifdef MEMWATCH
#include "memwatch.h"
#endif

static void (*term_func)(void)=NULL;

int packet_parse_time = 0;

/*======================================
 *	CORE : Set function
 *--------------------------------------
 */
void set_termfunc(void (*termfunc)(void))
{
	term_func = termfunc;
}

/*======================================
 *	CORE : Signal Sub Function
 *--------------------------------------
 */

#ifdef _WIN32
void sig_proc(void) {
	if(term_func)
		term_func();
}
#else
static void sig_proc(int sn)
{
	int i;
	switch(sn){
	case SIGINT:
	case SIGTERM:
		if(term_func)
			term_func();
		for(i=0;i<fd_max;i++){
			if(!session[i])
				continue;
			close(i);
		}
		exit(0);
		break;
	}
}
#endif

/*======================================
 *	CORE : MAINROUTINE
 *--------------------------------------
 */

int main(int argc,char **argv)
{
	int next;

	do_socket();
#ifdef _WIN32
	atexit(sig_proc);
#else
	signal(SIGPIPE,SIG_IGN);
	signal(SIGTERM,sig_proc);
	signal(SIGINT,sig_proc);
#endif
	do_init(argc,argv);
	if (packet_parse_time > 0) {
		add_timer_func_list(parsepacket_timer,"parsepacket_timer");
		add_timer_interval(gettick()+packet_parse_time,parsepacket_timer,0,0,packet_parse_time);

		while(1){
			next=do_timer(gettick_nocache());
			do_sendrecv(next);
		}
	} else {
		while(1){
			next=do_timer(gettick_nocache());
			do_sendrecv(next);
			do_parsepacket();
		}
	}
	return 0;
}

#ifdef _WIN32
int strcasecmp(const char *s1, const char *s2) {
	while(*s1 || *s2) {
		if((*s1 | 0x20) != (*s2 | 0x20)) {
			return ((*s1 | 0x20) > (*s2 | 0x20) ? 1 : -1);
		}
		s1++; s2++;
	}
	return 0;
}
#endif
