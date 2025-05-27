import React from 'react';
import { useNavigate } from 'react-router-dom';
import { logout } from '../services/auth';
import { useAuth } from '../context/AuthContext';
import '../styles/index.css';

export default function Logout() {
  const navigate = useNavigate();
  const { setUser } = useAuth();

  const handleLogout = () => {
    logout();    
    setUser(null);
    navigate('/login');
  };

  return (
    <button onClick={handleLogout} className="logout-button">
      Se déconnecter
    </button>
  );
}
