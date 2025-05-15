import React, { useEffect, useState } from 'react';
import { useParams, Link } from 'react-router-dom';
import { apiFetch } from '../services/api';
import AdminHeader from './Adminheader';

export default function WorkspaceDetail() {
  const { workspaceId } = useParams();
  const [channels, setChannels] = useState([]);
  const [workspaceName, setWorkspaceName] = useState('');

  const [newChannelName, setNewChannelName] = useState('');
  const [newChannelStatus, setNewChannelStatus] = useState('1');

  useEffect(() => {
    const fetchChannels = async () => {
      const data = await apiFetch(`workspaces/${workspaceId}/channels`);
      setChannels(data);
    };
    fetchChannels();
  }, [workspaceId]);

  useEffect(() => {
    const fetchWorkspaceDetails = async () => {
      const data = await apiFetch(`api/workspaces/${workspaceId}`);
      setWorkspaceName(data.name);
    };
    fetchWorkspaceDetails();
  }, [workspaceId]);

  const handleCreateChannel = async () => {
    if (!newChannelName.trim()) return;

    try {
      const isPublic = newChannelStatus === "1";

      await apiFetch('api/channels', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          name: newChannelName,
          status: isPublic,
          workspace_id: workspaceId
        })
      });

      // Affiche la liste des channels
      const data = await apiFetch(`workspaces/${workspaceId}/channels`);
      setChannels(data);

      setNewChannelName('');
      setNewChannelStatus('1');
    } catch (error) {
      console.error("Erreur lors de la création du canal :", error.message);
    }
  };

  return (
    <>
      <AdminHeader />
      <div className="workspace-detail-page">
        <h2 className="workspace-detail-title">{workspaceName}</h2>

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
      </div>
    </>
  );
}
