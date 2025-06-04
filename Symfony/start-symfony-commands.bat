@echo off
echo Lancement du serveur Symfony en HTTPS...

:: Stoppe les serveurs Symfony précédents s'il y en a
symfony server:stop >nul 2>&1

:: Démarre Symfony en HTTPS sur le port 8000
symfony server:start --port=8000 --daemon

IF %ERRORLEVEL% NEQ 0 (
    echo  ECHEC lancement serveur Symfony
) ELSE (
    echo  Symfony est lancé sur https://127.0.0.1:8000
    echo  Documentation API : https://127.0.0.1:8000/api/doc
)

pause