import React, { useEffect, useState } from 'react';
import { useNavigate } from 'react-router-dom';
import { apiFetch } from '../services/api';
import { logout } from '../services/auth';
import { useAuth } from '../context/AuthContext';
import Header from './Header';
import Card from '../pages/Card';
import SearchBar from './SearchBar';
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


  const handleLogout = () => {
    logout();
    setUser(null);
    navigate('/login');
  };


  return (
    <div>
      {user && (
        <>
          <Header />
          <div className='welcome-name'>
            Bonjour {user?.username}
          </div>

          <div className='card-container'>
            {[
              {
                title: "Messagerie",
                image: messenger,
                link: "/workspaces/1"
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
                link : "/workspaces"
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
                link: "/parametres"
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
