@echo off
echo 🚀 Lancement du serveur Symfony...

symfony server:start --no-tls --dir=public || echo ❌ ECHEC lancement serveur

echo ✅ Symfony est lancé ! Appuyez sur une touche pour fermer cette fenêtre.
echo documentation sur http://localhost:8000/api/doc
pause
