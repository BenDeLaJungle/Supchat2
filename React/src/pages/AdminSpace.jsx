import React, { useEffect, useState } from 'react';
import { apiFetch } from '../services/api';
import { useAuth } from '../context/AuthContext';
import Header from './Header';
import '../styles/admin.css';

export default function Admin() {
  const [users, setUsers] = useState([]);
  const [deleteId, setDeleteId] = useState('');
  const [deleteMessage, setDeleteMessage] = useState('');
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);
  const { user } = useAuth();

  // 🆕 Fait défiler vers le haut dès que la page est montée
  useEffect(() => {
    window.scrollTo(0, 0);
  }, []);

  useEffect(() => {
    if (user?.role === 'ROLE_ADMIN') {
      apiFetch('api/admin/users')
        .then((data) => {
          setUsers(data);
          setLoading(false);
        })
        .catch((err) => {
          setError("Erreur lors du chargement des utilisateurs.");
          setLoading(false);
        });
    } else {
      setError("Accès non autorisé.");
      setLoading(false);
    }
  }, [user]);

  const handleDeleteUserById = async () => {
    if (!deleteId) return alert("Veuillez entrer un ID.");
    if (!window.confirm(`❗ Supprimer l'utilisateur ID ${deleteId} ?`)) return;

    try {
      const response = await apiFetch(`api/admin/user/${deleteId}`, {
        method: 'DELETE'
      });
      setDeleteMessage(response.message || "Utilisateur supprimé !");
      setUsers(prev => prev.filter(u => u.id !== parseInt(deleteId)));
      setDeleteId('');
    } catch (err) {
      console.error("Erreur suppression :", err);
      setDeleteMessage("Erreur lors de la suppression.");
    }
  };

  return (
    <>
      <Header />
      <div className="admin-wrapper">
        {loading && <p>Chargement des utilisateurs...</p>}
        {error && <p className="error">{error}</p>}

        {!loading && !error && (
          <div className="admin-space">
            <h2>👥 Liste des utilisateurs :</h2>
            <ul className="user-liste">
              {users.map(user => (
                <li key={user.id}>
                  {user.username} ({user.email}) - {user.role} - {user.status} - ID: {user.id}
                </li>
              ))}
            </ul>

            <hr />
            <h3>🗑️ Supprimer un utilisateur par ID</h3>
            <div className="suppr-section">
              <input
                type="number"
                placeholder="ID utilisateur"
                value={deleteId}
                onChange={(e) => setDeleteId(e.target.value)}
              />
              <button onClick={handleDeleteUserById}>Supprimer</button>
            </div>
            {deleteMessage && <p>{deleteMessage}</p>}
          </div>
        )}
      </div>
    </>
  );
}
