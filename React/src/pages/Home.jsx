import React, { useEffect, useState } from 'react';
import api from '../services/axios';

export default function Home() {
  const [users, setUsers] = useState([]);
  const [error, setError] = useState(null);

  useEffect(() => {
    api.get('/admin/users')
      .then(res => setUsers(res.data))
      .catch(err => {
        console.error("Erreur API 😭", err);
        setError("Impossible de récupérer les utilisateurs");
      });
  }, []);

  return (
    <div>
      <h1>🏠 Bienvenue sur la page d’accueil</h1>
      <h2>👥 Liste des utilisateurs :</h2>
      
      {error && <p style={{ color: 'red' }}>{error}</p>}

      <ul>
        {users.map(user => (
          <li key={user.id}>
            {user.firstName} {user.lastName} ({user.userName})
          </li>
        ))}
      </ul>
    </div>
  );
}
  