@rem original version from eAthena, thanks. 

@echo off
if "%1" == "boot" goto boot
set __bin__=
if exist "bin\login-server.exe" set __bin__=bin\
start win32_start boot %__bin__%login-server.exe
start win32_start boot %__bin__%char-server.exe
start win32_start boot %__bin__%map-server.exe
goto end

:boot
if not exist %2 goto end
echo Athena 自動再起動スクリプト for WIN32
echo.
echo %2 の異常終了を監視中です。
echo サーバーを終了するには、最初にこのウィンドウを閉じてください。
start /wait %2
cls
echo %2 が終了しました。再起動します。
echo. | date /T
echo. | time /T
goto boot

:end
echo %2
