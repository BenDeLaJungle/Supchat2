@echo off
echo 📁 Dans le dossier Symfony

:: 🔥 Drop schéma
php bin\console doctrine:schema:drop --force --full-database
IF %ERRORLEVEL% NEQ 0 goto error

:: 🧱 Migrations
php bin\console doctrine:migrations:migrate --no-interaction
IF %ERRORLEVEL% NEQ 0 goto error

:: 🌱 Fixtures
php bin\console doctrine:fixtures:load --no-interaction
IF %ERRORLEVEL% NEQ 0 goto error

echo ✅ Fixtures reloadées avec succès ! Fermeture de la fenêtre...
timeout /t 2 >nul
exit

:error
echo ❌ Une erreur est survenue ! Vérifiez les commandes ci-dessus.
pause