@echo off
echo Lancement des serveurs...

cd /d %~dp0

start "Symfony" cmd /k "cd /d %~dp0Symfony && start-symfony-commands.bat"

timeout /t 2 >nul

start "React" cmd /k "cd /d %~dp0React && npm run dev"

timeout /t 2 >nul

start "WebSocket" cmd /k "cd /d %~dp0Node && node server.js"

echo Tous les serveurs ont été lancés !
