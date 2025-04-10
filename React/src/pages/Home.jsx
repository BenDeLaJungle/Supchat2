import React, { useEffect, useState } from 'react';
import { apiFetch } from '../services/api';
import { useAuth } from '../context/AuthContext';

export default function Home() {
  const [users, setUsers] = useState([]);
  const [error, setError] = useState(null);
  const { user } = useAuth();

  useEffect(() => {
    if (user?.role === 'ROLE_ADMIN') {
      apiFetch('/admin/users')
        .then(data => setUsers(data))
        .catch(err => {
          console.error("Erreur API 😭", err);
          setError("Impossible de récupérer les utilisateurs");
        });
    } else {
      setError("Accès non autorisé : réservé aux administrateurs.");
    }
  }, [user]);

  return (
    <div>
      <h1>🏠 Bienvenue sur la page d’accueil</h1>

      {user && <h2>Bonjour, {user.userName} !</h2>}

      <h2>👥 Liste des utilisateurs :</h2>

      {error && <p style={{ color: 'red' }}>{error}</p>}

      <ul>
        {users.map(user => (
          <li key={user.id}>
            {user.username} ({user.email})
          </li>
        ))}
      </ul>
    </div>
  );
}