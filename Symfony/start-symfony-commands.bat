@echo off
echo 📁 Dans le dossier Symfony
:: 🔥 Drop schéma (toutes les tables)
php bin\console doctrine:schema:drop --force --full-database

:: 🧱 Recréer via migrations
php bin\console doctrine:migrations:migrate --no-interaction

:: 🌱 Charger les fixtures
php bin\console doctrine:fixtures:load --no-interaction

echo 🚀 Lancement du serveur Symfony...
symfony server:start --no-tls --dir=public || echo ❌ ECHEC lancement serveur

echo ✅ Tout est fini ! Tapez une touche pour fermer cette fenêtre.
pause
