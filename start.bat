@echo off
echo Lancement des serveurs...

:: racine du projet
cd /d %~dp0

:: Symfony (PHP Backend)
start "Symfony" cmd /k "cd /d %~dp0Symfony && start-symfony-commands.bat"


timeout /t 2 >nul

:: React (Frontend)
start "React" cmd /k "cd /d %~dp0React && npm run dev"


timeout /t 2 >nul

:: Node.js (WebSocket Server)
start "WebSocket" cmd /k "cd /d %~dp0Node && node server.js"


echo Tous les serveurs ont été lancés !

