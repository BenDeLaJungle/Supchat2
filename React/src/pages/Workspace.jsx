import React, { useState, useEffect } from 'react';
import { useAuth } from '../context/AuthContext';
import { apiFetch } from '../services/api';
import Header from './Header';
import '../styles/index.css';
import '../styles/workspaces.css';

export default function Workspaces() {
  const { user } = useAuth();
  const [workspaces, setWorkspaces] = useState([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);

  useEffect(() => {
    const fetchWorkspaces = async () => {
      try {
        const data = await apiFetch('api/workspaces');
        setWorkspaces(data);
      } catch (err) {
        console.error('Erreur récupération workspaces', err);
        setError("Impossible de charger les espaces de travail.");
      } finally {
        setLoading(false);
      }
    };
    fetchWorkspaces();
  }, []);

  const handleJoin = async (workspaceId) => {
    try {
      await apiFetch(`api/workspaces/${workspaceId}/join`, { method: 'POST' });
      alert('Vous avez rejoint le workspace !');
    } catch (err) {
      alert("Erreur lors de l'adhésion au workspace.");
    }
  };

  return (
    <div>
      <Header />
      <div className="welcome-name">🌐 Espaces de travail – Bienvenue {user?.username}</div>

      {loading && <p className="workspace-center">Chargement...</p>}
      {error && <p className="workspace-center" style={{ color: 'red' }}>{error}</p>}

      {!loading && !error && (
        <div className="workspace-table-container">
          <table className="workspace-table">
            <thead>
              <tr>
                <th>Nom</th>
                <th>Description</th>
                <th>Type</th>
                <th>Statut</th>
              </tr>
            </thead>
            <tbody>
              {workspaces.map(ws => (
                <tr key={ws.id}>
                  <td>{ws.name}</td>
                  <td>{ws.description}</td>
                  <td>{ws.public ? 'Public' : 'Privé'}</td>
                  <td>
                    {ws.members.includes(user.id) ? (
                      '✅ Membre'
                    ) : (
                      <button onClick={() => handleJoin(ws.id)}>Rejoindre</button>
                    )}
                  </td>
                </tr>
              ))}
            </tbody>
          </table>
        </div>
      )}
    </div>
  );
}
