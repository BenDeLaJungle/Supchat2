import React from 'react';
import { useNavigate } from 'react-router-dom';
import { logout } from '../services/auth';
import { useAuth } from '../context/AuthContext';
import '../styles/index.css'; // ou un fichier CSS plus ciblé si tu veux

export default function Logout() {
  const navigate = useNavigate();
  const { setUser } = useAuth();

  const handleLogout = () => {
    logout();      // vide le token
    setUser(null); // vide le contexte utilisateur
    navigate('/login');
  };

  return (
    <button onClick={handleLogout} className="logout-button">
      Se déconnecter
    </button>
  );
}
