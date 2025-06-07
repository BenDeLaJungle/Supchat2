import React, { useEffect, useState } from 'react';
import { useParams, Link } from 'react-router-dom';
import { apiFetch } from '../services/api';
import AdminHeader from '../components/ui/Adminheader';
import '../styles/WorkspaceDetail.css'; 

export default function WorkspaceDetail() {
  const { workspaceId } = useParams();
  const [workspaceName, setWorkspaceName] = useState('');
  const [channels, setChannels] = useState([]);
  const [members, setMembers] = useState([]);
  const [users, setUsers] = useState([]);
  const [inviteLink, setInviteLink] = useState(null);
  const [newChannelName, setNewChannelName] = useState('');
  const [newChannelStatus, setNewChannelStatus] = useState('1');
  const [newChannelMinRole, setNewChannelMinRole] = useState('1');
  const [newMemberId, setNewMemberId] = useState('');
  const [newRoleId, setNewRoleId] = useState('1');
  const [currentUserRole, setCurrentUserRole] = useState(1);

useEffect(() => {
  const fetchData = async () => {
    const user = await apiFetch('user');
    const workspaceData = await apiFetch(`workspaces/${workspaceId}`);
    const allChannels = await apiFetch(`workspaces/${workspaceId}/channels`);
    const membersData = await apiFetch(`workspaces/${workspaceId}/members`);
    const currentMember = membersData.find(m => m.user_id === user.id);
    const usersData = await apiFetch('users');

    setWorkspaceName(workspaceData.name);
    setMembers(membersData);
    setUsers(usersData);

    // Si l'utilisateur est le créateur du workspace, il est admin par défaut
    if (user.id === workspaceData.creator.id) {
      setCurrentUserRole(3);
    } else {
      setCurrentUserRole(currentMember ? currentMember.role_id : 1);
    }

    const userRole = (user.id === workspaceData.creator_id)
      ? 3
      : (currentMember?.role_id ?? 1);

    const filteredChannels = allChannels.filter(
      ch => ch.status === true || (ch.minRole ?? 1) <= userRole
    );
    setChannels(filteredChannels);
  };

  fetchData();
}, [workspaceId]);


  const handleCreateChannel = async () => {
    if (!newChannelName.trim()) return;

    await apiFetch('channels', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        name: newChannelName,
        status: newChannelStatus === "1",
        workspace_id: workspaceId,
        min_role: parseInt(newChannelMinRole, 10)
      })
    });

    const updatedChannels = await apiFetch(`workspaces/${workspaceId}/channels`);
    const user = await apiFetch('user');
    const currentMember = members.find(m => m.user_id === user.id);
    const visibleChannels = updatedChannels.filter(
      ch => ch.status === true || (ch.minRole ?? 1) <= (currentMember?.role_id ?? 1)
    );

    setChannels(visibleChannels);
    setNewChannelName('');
    setNewChannelStatus('1');
    setNewChannelMinRole('1');
  };

  const handleAddMember = async () => {
    if (!newMemberId) return;
    const roleIdToAssign = currentUserRole === 2 ? 1 : parseInt(newRoleId, 10);
    await apiFetch(`workspaces/${workspaceId}/members`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        user_id: parseInt(newMemberId, 10),
        role_id: roleIdToAssign
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

        <h3 className="section-title">Canaux</h3>
        <div className="section-container">
          <div className="list-container channels-list">
            {channels.map(channel => (
              <Link
                to={`channels/${channel.id}`}
                key={channel.id}
                className="channel-link-button"
              >
                {channel.name}
              </Link>
            ))}
          </div>

          {currentUserRole !== 1 && (
            <div className="workspace-card">
              <input
                type="text"
                value={newChannelName}
                onChange={(e) => setNewChannelName(e.target.value)}
                placeholder="Nom du nouveau canal"
                className="input-regular"
              />
              <select
                value={newChannelStatus}
                onChange={(e) => setNewChannelStatus(e.target.value)}
                className="select-regular"
              >
                <option value="1">Public</option>
                <option value="2">Privé</option>
              </select>
              {newChannelStatus === "2" && (
                <select
                  value={newChannelMinRole}
                  onChange={(e) => setNewChannelMinRole(e.target.value)}
                  className="select-regular"
                >
                  <option value="1">Accessible à tous les membres</option>
                  <option value="2">Accessible aux modérateurs et admins</option>
                  <option value="3">Accessible uniquement aux admins</option>
                </select>
              )}
              <button
                onClick={handleCreateChannel}
                className="button-primary"
              >
                Créer un canal
              </button>
            </div>
          )}
        </div>

        <h3 className="section-title">Membres du workspace</h3>
        <div className="section-container">
          <div className="list-container members-list">
            {members.map(member => (
              <div key={member.id} className="member-row">
                <span className="member-label">
                  {member.user_name} ({getRoleLabel(member.role_id)})
                </span>
                {currentUserRole !== 1 && (
                  <button
                    onClick={() => handleDeleteMember(member.id)}
                    className="button-danger-sm"
                  >
                    Supprimer
                  </button>
                )}
              </div>
            ))}
          </div>

          {currentUserRole !== 1 && (
            <div className="workspace-card">
              <select
                value={newMemberId}
                onChange={(e) => setNewMemberId(e.target.value)}
                className="select-regular"
              >
                <option value="">Sélectionner un utilisateur</option>
                {users.map(user => (
                  <option key={user.id} value={user.id}>
                    {user.username}
                  </option>
                ))}
              </select>

              {currentUserRole === 3 && (
                <select
                  value={newRoleId}
                  onChange={(e) => setNewRoleId(e.target.value)}
                  className="select-regular"
                >
                  <option value="1">Membre</option>
                  <option value="2">Modérateur</option>
                  <option value="3">Admin</option>
                </select>
              )}

              <button
                onClick={handleAddMember}
                className="button-primary"
              >
                Ajouter le membre
              </button>
            </div>
          )}
        </div>

        <h3 className="section-title">Invitation</h3>
        <div className="invite-section">
          {(currentUserRole === 3 || currentUserRole === 2) && (
            <button
              onClick={handleGenerateInviteLink}
              className="button-invite"
            >
              Générer un lien d'invitation
            </button>
          )}
          {inviteLink && (
            <div className="invite-link-container">
              <span className="invite-link-text">{inviteLink}</span>
              <button
                onClick={handleCopyToClipboard}
                className="button-secondary-sm"
              >
                Copier le lien
              </button>
            </div>
          )}
        </div>
      </div>
    </>
  );
}

