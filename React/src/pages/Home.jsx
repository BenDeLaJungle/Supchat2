import React, { useEffect, useState } from 'react';
import { useNavigate } from 'react-router-dom';
import { apiFetch } from '../services/api';
import { logout } from '../services/auth';
import { useAuth } from '../context/AuthContext';

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
      <h1>🏠 Bienvenue sur la page d’accueil</h1>

      {user && (
        <>
          <h2>Bonjour, {user.username} !</h2>
          <button onClick={handleLogout} style={{ marginBottom: '1rem' }}>
            🚪 Se déconnecter
          </button>
          <p>📧 Email : {user.email}</p>
          <p>🛡️ Rôle : {user.role}</p>
          <p>📌 Statut : {user.status}</p>
          <p>🎨 Thème : {user.theme ? 'Sombre' : 'Clair'}</p>
		  <p>ID : {user.id }</p>
          <hr />
        </>
      )}

      {loading && <p>Chargement des utilisateurs...</p>}

      {error && <p style={{ color: 'red' }}>{error}</p>}

      {!loading && !error && (
        <>
          <h2>👥 Liste des utilisateurs :</h2>
          <ul>
            {users.map(user => (
              <li key={user.id}>
                {user.username} ({user.email}) - {user.role} - {user.status} - ID:{user.id}
              </li>
            ))}
          </ul>

          <hr />
          <h3>🗑️ Supprimer un utilisateur par ID</h3>
          <input
            type="number"
            placeholder="ID utilisateur"
            value={deleteId}
            onChange={(e) => setDeleteId(e.target.value)}
            style={{ marginRight: '1rem' }}
          />
          <button onClick={handleDeleteUserById}>
            Supprimer
          </button>
          {deleteMessage && <p>{deleteMessage}</p>}
        </>
      )}
    </div>
  );
}
