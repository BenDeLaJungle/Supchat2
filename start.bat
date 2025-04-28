@echo off
echo 🌟 Lancement des serveurs...

:: Aller à la racine du projet (le dossier de ce script)
cd /d %~dp0

echo 🐳 Lancement de Mercure via Docker...
docker compose -f MercureHub/docker-compose.yml up -d mercure

:: Symfony (lance le serveur uniquement)
start "Symfony" cmd /k "cd /d %~dp0Symfony && start-symfony-commands.bat"

:: Petite pause de politesse 
timeout /t 2 >nul

:: React
start "React" cmd /k "cd /d %~dp0React && npm run dev"

pause