all: map-server

COMMON_OBJ = ../common/core.o ../common/socket.o ../common/timer.o ../common/grfio.o ../common/db.o ../common/lock.o ../common/nullpo.o ../common/malloc.o
LIBS = -lz -lm

map-server: map.o chrif.o clif.o pc.o npc.o chat.o path.o itemdb.o mob.o script.o storage.o skill.o atcommand.o battle.o intif.o trade.o party.o vending.o guild.o pet.o status.o $(COMMON_OBJ)
	$(CC) -o ../../bin/$@ $> $(LIBS)

map.o: map.c map.h chrif.h clif.h npc.h pc.h mob.h chat.h skill.h itemdb.h storage.h party.h pet.h atcommand.h ../common/core.h ../common/timer.h ../common/db.h ../common/grfio.h ../common/mmo.h
chrif.o: chrif.c map.h battle.h chrif.h clif.h intif.h pc.h npc.h ../common/socket.h ../common/timer.h ../common/mmo.h
clif.o: clif.c map.h chrif.h clif.h mob.h intif.h pc.h npc.h itemdb.h chat.h script.h storage.h party.h guild.h atcommand.h pet.h atcommand.h ../common/socket.h ../common/timer.h ../common/mmo.h ../common/version.h
pc.o: pc.c map.h clif.h intif.h pc.h npc.h mob.h itemdb.h battle.h skill.h script.h party.h guild.h pet.h trade.h storage.h chat.h vending.h ../common/timer.h ../common/mmo.h ../common/db.h
npc.o: npc.c map.h npc.h clif.h pc.h script.h mob.h itemdb.h battle.h ../common/db.h ../common/timer.h ../common/mmo.h
chat.o: chat.c map.h clif.h pc.h chat.h ../common/db.h ../common/mmo.h
path.o: path.c map.h battle.h ../common/mmo.h
itemdb.o: itemdb.c map.h battle.h itemdb.h ../common/db.h ../common/grfio.h ../common/mmo.h
mob.o: mob.c map.h clif.h intif.h pc.h mob.h skill.h battle.h npc.h itemdb.h ../common/timer.h ../common/socket.h ../common/mmo.h
script.o: script.c itemdb.h map.h pc.h mob.h clif.h intif.h npc.h script.h storage.h skill.h pet.h battle.h ../common/timer.h ../common/socket.h ../common/db.h ../common/mmo.h ../common/lock.h
storage.o: storage.c itemdb.h pc.h clif.h intif.h storage.h guild.h ../common/mmo.h ../common/db.h
skill.o: skill.c skill.h map.h clif.h pc.h mob.h battle.h itemdb.h script.h ../common/timer.h ../common/mmo.h
atcommand.o: atcommand.c atcommand.h itemdb.h pc.h map.h skill.h clif.h mob.h intif.h battle.h storage.h guild.h pet.h ../common/socket.h ../common/timer.h ../common/mmo.h
battle.o: battle.c battle.h skill.h map.h mob.h pc.h pet.h guild.h ../common/timer.h ../common/mmo.h
intif.o: intif.c intif.h chrif.h clif.h party.h guild.h storage.h map.h battle.h pet.h ../common/socket.h ../common/mmo.h
trade.o: trade.c trade.h clif.h itemdb.h map.h pc.h npc.h ../common/mmo.h
party.o: party.c party.h clif.h intif.h pc.h map.h battle.h ../common/db.h ../common/socket.h ../common/timer.h ../common/mmo.h
vending.o: vending.c vending.h clif.h itemdb.h map.h pc.h ../common/mmo.h
guild.o: guild.c guild.h storage.h battle.h clif.h intif.h pc.h npc.h map.h ../common/db.h ../common/socket.h ../common/timer.h ../common/mmo.h
pet.o:pet.c pet.h map.h clif.h chrif.h intif.h pc.h itemdb.h battle.h mob.h npc.h script.h ../common/db.h ../common/socket.h ../common/timer.h ../common/mmo.h
status.o: status.c status.h

clean:
	rm -f *.o
	rm -f GNUmakefile

ifeq ($(findstring CYGWIN,$(shell uname)), CYGWIN)
		rm -f ../../bin/map-server.exe
else
		rm -f ../../bin/map-server
endif
