import React, { useEffect, useState } from 'react';
import { apiFetch } from '../services/api';
import { Link } from 'react-router-dom';
import AdminHeader from './Adminheader';

export default function WorkspaceList() {
  const [workspaces, setWorkspaces] = useState([]);
  const [newWorkspaceName, setNewWorkspaceName] = useState('');
  const [newWorkspaceStatus, setNewWorkspaceStatus] = useState('1');

  // Fonction pour récupérer les workspaces
  const fetchWorkspaces = async () => {
    const data = await apiFetch('api/workspaces');
    setWorkspaces(data);
  };

  useEffect(() => {
    fetchWorkspaces();
  }, []);

  // Fonction pour créer un workspace
  const handleCreateWorkspace = async () => {
    if (!newWorkspaceName.trim()) return;

    try {
      const isPublic = newWorkspaceStatus === "1";

      await apiFetch('api/workspaces', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          name: newWorkspaceName,
          status: isPublic
        })
      });

      fetchWorkspaces();

      setNewWorkspaceName('');
      setNewWorkspaceStatus('1');
    } catch (error) {
      console.error("Erreur lors de la création du workspace :", error.message);
    }
  };

  return (
    <>
      <AdminHeader />
      <div className="workspace-page">
        <h2 className="workspace-title">Mes workspaces</h2>

        <div className="workspace-card-container">
          {workspaces.map(ws => (
            <Link to={`/workspaces/${ws.id}`} key={ws.id} className="workspace-link">
              <div className="workspace-card">
                <h2>{ws.name}</h2>
                 <p>{ws.status === true ? 'Public' : 'Privé'}</p>
              </div>
            </Link>
          ))}
        </div>

        <div className="work-channel-create-form">
          <input
            type="text"
            value={newWorkspaceName}
            onChange={(e) => setNewWorkspaceName(e.target.value)}
            placeholder="Nom du nouveau workspace"
            className="work-channel-input"
          />
          <select
            value={newWorkspaceStatus}
            onChange={(e) => setNewWorkspaceStatus(e.target.value)}
            className="work-channel-select"
          >
            <option value="1">Public</option>
            <option value="2">Privé</option>
          </select>
          <button onClick={handleCreateWorkspace} className="work-channel-create-button">
            Créer un workspace
          </button>
        </div>
      </div>
    </>
  );
}
