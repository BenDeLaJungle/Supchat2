import React, { useEffect, useState } from 'react';
import { apiFetch } from '../services/api';
import { Link } from 'react-router-dom';
import AdminHeader from './Adminheader';

export default function WorkspaceList() {
  const [workspaces, setWorkspaces] = useState([]);
  const [newWorkspaceName, setNewWorkspaceName] = useState('');
  const [newWorkspaceStatus, setNewWorkspaceStatus] = useState('public');

  useEffect(() => {
    const fetchWorkspaces = async () => {
      const data = await apiFetch('workspaces');
      setWorkspaces(data);
    };
    fetchWorkspaces();
  }, []);

  const handleCreateWorkspace = async () => {
    if (!newWorkspaceName.trim()) return;

    try {
      const data = await apiFetch('workspaces', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          name: newWorkspaceName,
          status: newWorkspaceStatus
        })
      });
      setWorkspaces([...workspaces, data]);
      setNewWorkspaceName('');
      setNewWorkspaceStatus('public');
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
                <p>{ws.status === 'private' ? 'Privé' : 'Public'}</p>
              </div>
            </Link>
          ))}
        </div>

        <div className="workspace-create-form">
          <input
            type="text"
            value={newWorkspaceName}
            onChange={(e) => setNewWorkspaceName(e.target.value)}
            placeholder="Nom du nouveau workspace"
            className="workspace-input"
          />
          <select
            value={newWorkspaceStatus}
            onChange={(e) => setNewWorkspaceStatus(e.target.value)}
            className="workspace-select"
          >
            <option value="public">Public</option>
            <option value="private">Privé</option>
          </select>
          <button onClick={handleCreateWorkspace} className="workspace-create-button">
            Créer un workspace
          </button>
        </div>
      </div>
    </>
  );
}

  