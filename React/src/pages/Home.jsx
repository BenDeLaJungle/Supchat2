import React, { useEffect, useState } from 'react';
import { useNavigate } from 'react-router-dom';
import { apiFetch } from '../services/api';
import { logout } from '../services/auth';
import { useAuth } from '../context/AuthContext';
import Header from '../components/ui/Header';
import Card from '../components/ui/Card';
import messenger from '../assets/messsage.png';
import files from '../assets/share.png';
import notif from '../assets/notif.png';
import workspaceIcon from '../assets/workspace.png';
import calendrier from '../assets/calendrier.png';
import adminspace from '../assets/adminspace.png';
import parametres from '../assets/settings.png';
import logo from '../assets/logo-supchat.png';
import '../styles/index.css';

export default function Home() {
  const [lastTwoWorkspaces, setDernierWorkspaces] = useState([]);
  const { user, setUser } = useAuth();
  const navigate = useNavigate();

  useEffect(() => {
    apiFetch('workspaces')
      .then((data) => {
        const sortedDesc = data.sort((a, b) => b.id - a.id);
        setDernierWorkspaces(sortedDesc.slice(0, 2));
      })
      .catch((err) => {
        console.error('Erreur lors de la récupération des workspaces', err);
      });
  }, []);

  const handleLogout = () => {
    logout();
    setUser(null);
    navigate('/login');
  };

  const ws1 = lastTwoWorkspaces[0];
  const ws2 = lastTwoWorkspaces[1];


  const cards = [
    { title: "Messagerie", image: messenger, link: "/workspaces/1" },
    { title: "Fichiers partagés", image: files },
    { title: "Notifications", image: notif },
    ...(ws1
      ? [{
          title: ws1.name,
          image: workspaceIcon,
          link: `/workspaces/${ws1.id}`
        }]
      : []
    ),
    ...(ws2
      ? [{
          title: ws2.name,
          image: workspaceIcon,
          link: `/workspaces/${ws2.id}`
        }]
      : []
    ),
    { title: "Tous les workspaces", image: workspaceIcon, link: "/workspaces" },
    { title: "Calendrier", image: calendrier, link: "/calendrier" },
    ...(user?.role === 'ROLE_ADMIN'
      ? [{ title: "Espace Admin", image: adminspace, link: "/AdminSpace" }]
      : [{ title: "Supchat", image: logo }]
    ),
    { title: "Paramètres", image: parametres, link: "/parametres" },
  ];

  return (
    <div>
      {user && (
        <>
          <Header onLogout={handleLogout} />

          <div className="welcome-name">
            Bonjour {user.username}
          </div>

          <div className="card-container">
            {cards.map((card) => (
              <Card key={card.title} {...card} />
            ))}
          </div>
        </>
      )}
    </div>
  );
}
