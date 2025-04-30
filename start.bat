@echo off
echo 🌟 Lancement des serveurs...

:: Aller à la racine du projet (là où est ce script)
cd /d %~dp0

:: Symfony (PHP Backend)
start "Symfony" cmd /k "cd /d %~dp0Symfony && start-symfony-commands.bat"

:: Petite pause de politesse
timeout /t 2 >nul

:: React (Frontend)
start "React" cmd /k "cd /d %~dp0React && npm run dev"

:: Petite pause de politesse encore 🩷
timeout /t 2 >nul

:: Node.js (WebSocket Server)
start "WebSocket" cmd /k "cd /d %~dp0Node && node server.js"

:: Fin
echo 🚀 Tous les serveurs ont été lancés !

