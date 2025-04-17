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
import parametres from '../assets/settings.png';
import adminspace from '../assets/adminspace.png';
import logo from '../assets/logo-supchat.png';





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
          <Header />

          <div className="welcome-name">
            Bonjour {user?.username}
          </div>

          <div className="card-container">
            {[
              {
                title: "Messagerie",
                image: messenger,
              },
              {
                title: "Fichiers partagés",
                image: files,
              },
              {
                title: "Notifications",
                image: notif,
              },
              {
                title: "Workspace 1",
                image: workspace,
              },
              {
                title: "Workspace 2",
                image: workspace,
              },
              {
                title: "Tous les workspaces",
                image: workspace,
              },
              {
                title: "Calendrier",
                image: calendrier,
                link: "/calendrier"
              },
              user?.role === 'ROLE_ADMIN'
                ? { title: "Espace Admin", image: adminspace, link: "/AdminSpace"}
                : { title: "logo-user", image: logo },
              {
                title: "Paramètres",
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
