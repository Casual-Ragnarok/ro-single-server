@echo off
echo.
rem ## Save return path
pushd %~dp0

rem ## Check to see if already stopped
if NOT exist ROEmulator\usr\local\apache2\logs\httpd.pid goto :ALREADYKILLED

rem ## It exists is it running
SET /P pid=<ROEmulator\usr\local\apache2\logs\httpd.pid
netstat -anop tcp | FIND /I " %pid%" >NUL
IF ERRORLEVEL 1 goto :NOTRUNNING
IF ERRORLEVEL 0 goto :RUNNING

:NOTRUNNING
rem ## Not shutdown using server_stop.bat hence delete files
del ROEmulator\usr\local\apache2\logs\httpd.pid
del ROEmulator\usr\local\mysql\data\mysql_mini.pid 
        
goto :ALREADYKILLED

rem ## It is running hence shut server down
:RUNNING
rem ## Get drive letter
SET /P Disk=<ROEmulator\usr\local\apache2\logs\drive.txt

rem ## Remove pid file server was closed
del ROEmulator\usr\local\mysql\data\mysql_mini.pid

rem ## Kill MySQL server
ROEmulator\usr\local\mysql\bin\mysqladmin.exe --port=3306 --user=root --password=root shutdown

rem ## Kill Apache process and all it's sub-processes
SET killstring= -t "%pid%"
ROEmulator\home\admin\program\pskill.exe  Apache_16.exe c

echo  MySQL  服务器已经关闭
echo  Apache 服务器已经关闭

rem ## Remove pid file
del ROEmulator\usr\local\apache2\logs\httpd.pid

rem ## Remove disk file
del ROEmulator\usr\local\apache2\logs\drive.txt

rem ## Kill drive
subst %Disk%: /D

goto :END

:ALREADYKILLED
echo  MySQL  服务器已经关闭
echo  Apache 服务器已经关闭
ping 127.0.0.1 -n 3 >nul
:END
echo.

:pause

rem ## Return to caller
popd