@echo off

SET mypath="%~dp0"
echo %mypath:~0,-1%

SET NEWLINE=^& echo.

FIND /C /I "local.carbonphp.com" %WINDIR%\system32\drivers\etc\hosts
IF %ERRORLEVEL% NEQ 0 ECHO %NEWLINE%^127.0.0.1 local.carbonphp.com>>%WINDIR%\System32\drivers\etc\hosts

php -S local.carbonphp.com:80 index.php || php -S local.carbonphp.com:8080 index.php