import React, { useEffect, useState } from 'react';
import { useNavigate } from 'react-router-dom';
import { apiFetch } from '../services/api';
import { logout } from '../services/auth';
import { useAuth } from '../context/AuthContext';
import Header from './Header';
import Card from '../pages/Card';
import '../styles/index.css';
import messenger from '../assets/messsage.png';
import files from '../assets/share.png';
import notif from '../assets/notif.png';
import workspace from '../assets/workspace.png';
import calendrier from '../assets/calendrier.png';
import appel from '../assets/appel.png';
import parametres from '../assets/settings.png';





export default function Home() {
  const [users, setUsers] = useState([]);
  const [error, setError] = useState(null);
  const [loading, setLoading] = useState(true);
  const [deleteId, setDeleteId] = useState('');
  const [deleteMessage, setDeleteMessage] = useState('');
  const { user, setUser } = useAuth();
  const navigate = useNavigate();

  useEffect(() => {
    if (!user) {
      navigate('/login');
    } else if (user?.role === 'ROLE_ADMIN') {
      apiFetch('api/admin/users')
        .then(data => {
          setUsers(data);
          setLoading(false);
        })
        .catch(err => {
          console.error("Erreur API 😭", err);
          setError("Impossible de récupérer les utilisateurs.");
          setLoading(false);
        });
    } else {
      setError("Accès non autorisé : réservé aux administrateurs.");
      setLoading(false);
    }
  }, [user]);

  const handleLogout = () => {
    logout();
    setUser(null);
    navigate('/login');
  };

  const handleDeleteUserById = async () => {
    if (!deleteId) return alert("Veuillez entrer un ID.");
    if (!window.confirm(`❗ Supprimer l'utilisateur ID ${deleteId} ?`)) return;

    try {
      const response = await apiFetch(`api/admin/user/${deleteId}`, { method: 'DELETE' });
      setDeleteMessage(response.message || "Utilisateur supprimé !");
      // Mise à jour de la liste
      setUsers(prev => prev.filter(u => u.id !== parseInt(deleteId)));
      setDeleteId('');
    } catch (err) {
      console.error("Erreur suppression :", err);
      setDeleteMessage("Erreur lors de la suppression.");
    }
  };

  return (
    <div>
      {user && (
        <>
          {/* HEADER AVEC LOGO + BARRE DE RECHERCHE + LOGOUT */}
          <Header />
  
          {/* SECTION DES CARTES */}
          <div className="card-container">
            {[
              {
                title: "Messagerie",
                description: "Consultez vos conversations",
                image: messenger,
              },
              {
                title: "Fichiers partagés",
                description: "Accédez à vos fichiers partagés",
                image: files,
              },
              {
                title: "Notifications",
                description: "Consultez vos alertes",
                image: notif,
              },
              {
                title: "Workspace 1",
                description: "Rejoignez votre espace de travail",
                image: workspace,
              },
              {
                title: "Workspace 2",
                description: "Rejoignez votre espace de travail",
                image: workspace,
              },
              {
                title: "Tous les workspaces",
                description: "Rejoignez vos espaces de travail",
                image: workspace,
              },
              {
                title: "Calendrier",
                description: "Accédez à votre calendrier",
                image: calendrier,
              },
              {
                title: "Appel",
                description: "Contactez vos collègues",
                image: appel,
              },
              {
                title: "Paramètres",
                description: "Accédez aux réglages de votre compte",
                image: parametres,
              },
            ].map((card) => (
              <Card key={card.title} {...card} />
            ))}
          </div>
        </>
      )}
    </div>
  );  
}
