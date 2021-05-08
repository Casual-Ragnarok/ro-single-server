all: core.o socket.o timer.o grfio.o db.o lock.o nullpo.o malloc.o

core.o: core.c core.h
socket.o: socket.c socket.h
timer.o: timer.c timer.h
grfio.o: grfio.c grfio.h
db.o: db.c db.h
lock.o: lock.h
nullpo.o: nullpo.h
malloc.o: malloc.h

clean:
	rm -f *.o
	rm -f GNUmakefile
