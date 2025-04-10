@echo off
echo 🌟 Reload des fixtures...

:: Aller à la racine du projet (le dossier de ce script)
cd /d %~dp0

:: Symfony avec reset complet
start "Symfony" cmd /k "cd /d %~dp0Symfony && reload-symfony-commands.bat"