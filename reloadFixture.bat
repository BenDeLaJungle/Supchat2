@echo off
echo Reload des fixtures...

cd /d %~dp0

start "Symfony" cmd /k "cd /d %~dp0Symfony && reload-symfony-commands.bat"