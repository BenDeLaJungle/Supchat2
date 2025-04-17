import React, { useState, useEffect } from 'react';
import { useAuth } from '../context/AuthContext';
import { apiFetch } from '../services/api';
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
    <div className="workspaces-page">
      <h1>🌐 Espaces de travail</h1>

      {loading && <p>Chargement...</p>}
      {error && <p style={{ color: 'red' }}>{error}</p>}

      {!loading && !error && (
        <div className="workspace-list">
          {workspaces.map(ws => (
            <div className="workspace-card" key={ws.id}>
              <h3>{ws.name}</h3>
              <p>{ws.description}</p>
              <p><strong>Type :</strong> {ws.public ? 'Public' : 'Privé'}</p>
              {!ws.members.includes(user.id) && (
                <button onClick={() => handleJoin(ws.id)}>Rejoindre</button>
              )}
              {ws.members.includes(user.id) && <p>✅ Membre</p>}
            </div>
          ))}
        </div>
      )}
    </div>
  );
}
