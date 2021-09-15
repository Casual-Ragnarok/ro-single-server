all: char-server

COMMON_OBJ = ../common/core.o ../common/socket.o ../common/timer.o ../common/db.o ../common/lock.o ../common/nullpo.o ../common/malloc.o
COMMON_H = ../common/core.h ../common/socket.h ../common/timer.h ../common/mmo.h ../common/db.h ../common/lock.h ../common/nullpo.h ../common/malloc.h
char-server: char.o inter.o int_party.o int_guild.o int_storage.o int_pet.o $(COMMON_OBJ)
	$(CC) -o ../../bin/$@ $>

char.o: char.c char.h inter.h int_pet.h $(COMMON_H) ../common/version.h
inter.o: inter.c inter.h int_party.h int_guild.h int_storage.h int_pet.h char.h $(COMMON_H)
int_party.o: int_party.c int_party.h inter.h char.h $(COMMON_H)
int_guild.o: int_guild.c int_guild.h int_storage.h inter.h char.h $(COMMON_H)
int_storage.o: int_storage.c int_storage.h int_guild.h inter.h char.h $(COMMON_H)
int_pet.o: int_pet.c int_pet.h inter.h char.h $(COMMON_H)
clean:
	rm -f *.o
	rm -f GNUmakefile

ifeq ($(findstring CYGWIN,$(shell uname)), CYGWIN)
		rm -f ../../bin/char-server.exe
else
		rm -f ../../bin/char-server
endif
