import React, { useEffect, useState } from 'react';
import { useParams, Link } from 'react-router-dom';
import { apiFetch } from '../services/api';
import AdminHeader from './Adminheader';

export default function WorkspaceDetail() {
  const { workspaceId } = useParams();
  const [workspaceName, setWorkspaceName] = useState('');
  const [channels, setChannels] = useState([]);
  const [members, setMembers] = useState([]);
  const [users, setUsers] = useState([]);
  const [inviteLink, setInviteLink] = useState(null);

  // Pour le formulaire de nouveau canal
  const [newChannelName, setNewChannelName] = useState('');
  const [newChannelStatus, setNewChannelStatus] = useState('1');

  // **Pour le formulaire de nouveau membre**
  const [newMemberId, setNewMemberId] = useState('');
  const [newRoleId, setNewRoleId] = useState('1'); // 1 = Membre, 2 = Admin

  useEffect(() => {
    // Charger tous les éléments au chargement ou quand workspaceId change
    apiFetch(`workspaces/${workspaceId}/channels`).then(setChannels);
    apiFetch(`workspaces/${workspaceId}`).then(data => setWorkspaceName(data.name));
    apiFetch(`workspaces/${workspaceId}/members`).then(setMembers);
    apiFetch('users').then(setUsers);
  }, [workspaceId]);

  const handleCreateChannel = async () => {
    if (!newChannelName.trim()) return;
    await apiFetch('channels', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        name: newChannelName,
        status: newChannelStatus === "1",
        workspace_id: parseInt(workspaceId, 10),
      }),
    });
    setNewChannelName('');
    setNewChannelStatus('1');
    setChannels(await apiFetch(`workspaces/${workspaceId}/channels`));
  };

  const handleAddMember = async () => {
    if (!newMemberId) return;
    try {
      await apiFetch(`workspaces/${workspaceId}/members`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          user_id: parseInt(newMemberId, 10),
          role_id: parseInt(newRoleId, 10),
        }),
      });
      setNewMemberId('');
      setNewRoleId('1');
      setMembers(await apiFetch(`workspaces/${workspaceId}/members`));
    } catch (e) {
      alert(e.message);
    }
  };

  const handleDeleteMember = async memberId => {
    await apiFetch(`workspaces/${workspaceId}/members/${memberId}`, { method: 'DELETE' });
    setMembers(await apiFetch(`workspaces/${workspaceId}/members`));
  };

  const handleGenerateInviteLink = async () => {
    const data = await apiFetch(`workspaces/${workspaceId}/generate-invite`);
    setInviteLink(data.invite_link);
  };

  const getRoleLabel = roleId => {
    switch (roleId) {
      case 1: return 'Membre';
      case 2: return 'Admin';
      default: return 'Inconnu';
    }
  };

  return (
    <>
      <AdminHeader />

      <div className="workspace-detail-page">
        <h2 className="workspace-detail-title">{workspaceName}</h2>

        {/* --- Canaux --- */}
        <h3>Canaux</h3>
        <ul className="channel-list">
          {channels.map(ch => (
            <li key={ch.id}>
              <Link to={`/channels/${ch.id}`}>{ch.name}</Link>
            </li>
          ))}
        </ul>
        <div className="work-channel-create-form">
          <input
            type="text"
            value={newChannelName}
            onChange={e => setNewChannelName(e.target.value)}
            placeholder="Nom du nouveau canal"
          />
          <select
            value={newChannelStatus}
            onChange={e => setNewChannelStatus(e.target.value)}
          >
            <option value="1">Public</option>
            <option value="0">Privé</option>
          </select>
          <button onClick={handleCreateChannel}>Créer un canal</button>
        </div>

        {/* --- Membres --- */}
        <div className="workspace-members-section">
          <h3>Membres du workspace</h3>
          {members.map(member => (
            <div key={member.id} className="workspace-member-row">
              <span>
                {member.user_name} ({getRoleLabel(member.role_id)})
              </span>
              <button onClick={() => handleDeleteMember(member.id)}>Supprimer</button>
            </div>
          ))}

          {/* Formulaire d'ajout */}
          <div className="work-channel-create-form">
            <select
              value={newMemberId}
              onChange={e => setNewMemberId(e.target.value)}
            >
              <option value="">Sélectionner un utilisateur</option>
              {users.map(u => (
                <option key={u.id} value={u.id}>
                  {u.username}
                </option>
              ))}
            </select>

            {/* **Nouveau select pour le rôle** */}
            <select
              value={newRoleId}
              onChange={e => setNewRoleId(e.target.value)}
            >
              <option value="1">Membre</option>
              <option value="2">Admin</option>
            </select>

            <button onClick={handleAddMember}>Ajouter le membre</button>
          </div>
        </div>

        {/* --- Invitation --- */}
        <div className="generate-invite-section">
          <button onClick={handleGenerateInviteLink}>
            Générer un lien d'invitation
          </button>
          {inviteLink && (
            <>
              <p>{inviteLink}</p>
              <button onClick={() => {
                navigator.clipboard.writeText(inviteLink);
                alert('Lien copié');
              }}>
                Copier le lien
              </button>
            </>
          )}
        </div>
      </div>
    </>
  );
}
