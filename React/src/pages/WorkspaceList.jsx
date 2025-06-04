import React, { useEffect, useState } from 'react';
import { apiFetch } from '../services/api';
import { Link } from 'react-router-dom';
import AdminHeader from '../components/ui/Adminheader';
import '../styles/workspacelist.css';

export default function WorkspaceList() {
  const [workspaces, setWorkspaces] = useState([]);
  const [newWorkspaceName, setNewWorkspaceName] = useState('');
  const [newWorkspaceStatus, setNewWorkspaceStatus] = useState('1');

  // pour gérer l'invitation
  const [inviteLink, setInviteLink] = useState('');

  // charge la liste des workspaces dont je suis membre
  const fetchWorkspaces = async () => {
    try {
      const data = await apiFetch('workspaces');
      setWorkspaces(data);
    } catch (err) {
      console.error('Erreur lors de la récupération des workspaces :', err);
    }
  };

  useEffect(() => {
    fetchWorkspaces();
  }, []);

  // création de workspace
  const handleCreateWorkspace = async () => {
    if (!newWorkspaceName.trim()) return;
    try {
      const isPublic = newWorkspaceStatus === "1";
      await apiFetch('workspaces', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          name: newWorkspaceName,
          status: isPublic
        })
      });
      setNewWorkspaceName('');
      setNewWorkspaceStatus('1');
      fetchWorkspaces();
    } catch (error) {
      console.error("Erreur lors de la création du workspace :", error.message);
    }
  };

  // rejoindre via un lien d'invitation
  const handleJoinWithLink = async () => {
    if (!inviteLink.trim()) {
      alert('Veuillez coller un lien ou un token d\'invitation.');
      return;
    }

    // on extrait juste le token à la fin de l'URL ou on suppose que l'utilisateur a collé directement le token
    const token = inviteLink.includes('/')
      ? inviteLink.trim().split('/').pop()
      : inviteLink.trim();

    try {
      await apiFetch(`workspaces/invite/${token}`, { method: 'POST' });
      setInviteLink('');
      fetchWorkspaces();
      alert('Vous avez rejoint le workspace avec succès !');
    } catch (err) {
      console.error('Erreur lors de la jonction au workspace :', err);
      alert(`Impossible de rejoindre : ${err.message}`);
    }
  };

  return (
    <>
      <AdminHeader />
      <div className="workspace-page">
        <h2 className="workspace-title">Mes workspaces</h2>

        <div className="workspace-card-container">
          {workspaces.map(ws => (
            <Link
              to={`/workspaces/${ws.id}`}
              key={ws.id}
              className="workspace-link"
              onClick={() => localStorage.setItem('lastWorkspaceId', ws.id)}
            >
              <div className="workspace-card">
                <h2>{ws.name}</h2>
                <p>{ws.status === true ? 'Public' : 'Privé'}</p>
              </div>
            </Link>
          ))}
        </div>

        {/* création de workspace */}
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

        {/* rejoindre via un lien d'invitation */}
        <div className="work-channel-create-form" style={{ marginTop: '2rem' }}>
          <input
            type="text"
            value={inviteLink}
            onChange={e => setInviteLink(e.target.value)}
            placeholder="Coller le lien d'invitation ou le token"
            className="work-channel-input"
          />
          <button onClick={handleJoinWithLink} className="work-channel-create-button">
            Rejoindre un workspace
          </button>
        </div>
      </div>
    </>
  );
}
