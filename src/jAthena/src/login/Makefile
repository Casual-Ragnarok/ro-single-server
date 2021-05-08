all: login-server

COMMON_OBJ = ../common/core.o ../common/socket.o ../common/timer.o ../common/db.o ../common/lock.o ../common/malloc.o
COMMON_H = ../common/core.h ../common/socket.h ../common/timer.h ../common/mmo.h ../common/version.h ../common/db.h ../common/lock.h ../common/malloc.h

login-server: login.o md5calc.o $(COMMON_OBJ)
	$(CC) -o ../../bin/$@ login.o md5calc.o $(COMMON_OBJ)
login.o: login.c login.h md5calc.h $(COMMON_H)
md5calc.o: md5calc.c md5calc.h

clean:
	rm -f *.o
	rm -f GNUmakefile

ifeq ($(findstring CYGWIN,$(shell uname)), CYGWIN)
		rm -f ../../bin/login-server.exe
else
		rm -f ../../bin/login-server
endif
