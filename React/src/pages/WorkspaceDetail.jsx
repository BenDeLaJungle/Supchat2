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
  const [newChannelName, setNewChannelName] = useState('');
  const [newChannelStatus, setNewChannelStatus] = useState('1');
  const [newMemberId, setNewMemberId] = useState('');
  const [newRoleId, setNewRoleId] = useState('1');

  useEffect(() => {
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
        workspace_id: workspaceId
      })
    });
    const updatedChannels = await apiFetch(`workspaces/${workspaceId}/channels`);
    setChannels(updatedChannels);
    setNewChannelName('');
    setNewChannelStatus('1');
  };

  const handleAddMember = async () => {
    if (!newMemberId) return;
    await apiFetch(`workspaces/${workspaceId}/members`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        user_id: parseInt(newMemberId),
        role_id: parseInt(newRoleId)
      })
    });
    const updatedMembers = await apiFetch(`workspaces/${workspaceId}/members`);
    setMembers(updatedMembers);
    setNewMemberId('');
    setNewRoleId('1');
  };

  const handleDeleteMember = async (memberId) => {
    await apiFetch(`workspaces/${workspaceId}/members/${memberId}`, { method: 'DELETE' });
    const updatedMembers = await apiFetch(`workspaces/${workspaceId}/members`);
    setMembers(updatedMembers);
  };

  const getRoleLabel = (roleId) => {
    switch (roleId) {
      case 1: return 'Membre';
      case 2: return 'Modérateur';
      case 3: return 'Admin';
      default: return 'Inconnu';
    }
  };

  const handleGenerateInviteLink = async () => {
    const response = await apiFetch(`workspaces/${workspaceId}/generate-invite`);
    setInviteLink(response.invite_link);
  };

  const handleCopyToClipboard = () => {
    if (inviteLink) {
      navigator.clipboard.writeText(inviteLink);
      alert('Lien copié dans le presse-papiers');
    }
  };

  return (
    <>
      <AdminHeader />
      <div className="workspace-detail-page">
        <h2 className="workspace-detail-title">{workspaceName}</h2>

        <h3>Canaux</h3>
        <ul className="channel-list">
          {channels.map(channel => (
            <li key={channel.id} className="channel-list-item">
              <Link to={`/channels/${channel.id}`} className="channel-list-link">
                {channel.name}
              </Link>
            </li>
          ))}
        </ul>

        <div className="work-channel-create-form">
          <input
            type="text"
            value={newChannelName}
            onChange={(e) => setNewChannelName(e.target.value)}
            placeholder="Nom du nouveau canal"
            className="work-channel-input"
          />
          <select
            value={newChannelStatus}
            onChange={(e) => setNewChannelStatus(e.target.value)}
            className="work-channel-select"
          >
            <option value="1">Public</option>
            <option value="2">Privé</option>
          </select>
          <button onClick={handleCreateChannel} className="work-channel-create-button">
            Créer un canal
          </button>
        </div>

        <div className="workspace-members-section">
          <h3>Membres du workspace</h3>
          <div className="workspace-member-list">
            {members.map(member => (
              <div key={member.id} className="workspace-member-row">
                <span>{member.user_name} ({getRoleLabel(member.role_id)})</span>
                <button
                  onClick={() => handleDeleteMember(member.id)}
                  className="workspace-member-delete-btn"
                >
                  Supprimer
                </button>
              </div>
            ))}
          </div>

          <div>
            <select
              value={newMemberId}
              onChange={(e) => setNewMemberId(e.target.value)}
              className="work-channel-select"
            >
              <option value="">Sélectionner un utilisateur</option>
              {users.map(user => (
                <option key={user.id} value={user.id}>{user.username}</option>
              ))}
            </select>

            <select
              value={newRoleId}
              onChange={(e) => setNewRoleId(e.target.value)}
              className="work-channel-select"
            >
              <option value="1">Membre</option>
              <option value="2">Modérateur</option>
              <option value="3">Admin</option>
            </select>

            <button onClick={handleAddMember} className="work-channel-create-button">
              Ajouter le membre
            </button>
          </div>
        </div>

        <div className="generate-invite-section">
          <button onClick={handleGenerateInviteLink} className="work-channel-create-button">
            Générer un lien d'invitation
          </button>

          {inviteLink && (
            <div style={{ marginTop: '10px' }}>
              <p>{inviteLink}</p>
              <button onClick={handleCopyToClipboard} className="work-channel-create-button">
                Copier le lien
              </button>
            </div>
          )}
        </div>
      </div>
    </>
  );
}
