import React, { useState, useEffect, useCallback } from 'react';
import { useLocation, useNavigate } from 'react-router-dom';
import { apiFetch } from '../services/api';
import AdminHeader from '../components/ui/Adminheader';
import ChatWindow from '../components/chat/ChatWindow';
import { useAuth } from '../context/AuthContext';
import '../styles/Index.css';

const workspaceId = 1;

export default function Messaging() {
  const { user } = useAuth();
  const navigate = useNavigate();
  const location = useLocation();
  const searchParams = new URLSearchParams(location.search);
  const selectedChannelIdFromUrl = parseInt(searchParams.get("channel"), 10);

  const [channels, setChannels] = useState([]);
  const [users, setUsers] = useState([]);
  const [selectedChannelId, setSelectedChannelId] = useState(selectedChannelIdFromUrl || null);
  const [selectedParticipant, setSelectedParticipant] = useState('');
  const [error, setError] = useState(null);
  const [showCreate, setShowCreate] = useState(false);

  const loadAll = useCallback(async () => {
    try {
      const [channelDataRaw, userData] = await Promise.all([
        apiFetch(`workspaces/${workspaceId}/channels`),
        apiFetch('users')
      ]);

      const channelData = Array.isArray(channelDataRaw) ? Object.values(channelDataRaw) : channelDataRaw;
      setChannels(channelData.filter(ch => ch.status === false));
      setUsers(userData.filter(u => u.id !== user.id));
      setError(null);
    } catch (err) {
      console.error('Erreur chargement:', err.message);
      setError("Impossible de charger les données.");
    }
  }, [user?.id]);

  useEffect(() => {
    if (user?.id) loadAll();
  }, [user?.id, loadAll]);

  useEffect(() => {
    if (selectedChannelIdFromUrl && !isNaN(selectedChannelIdFromUrl)) {
      setSelectedChannelId(selectedChannelIdFromUrl);
    }
  }, [selectedChannelIdFromUrl]);

  const handleCreateChannel = async () => {
    const targetId = parseInt(selectedParticipant, 10);
    if (!targetId || isNaN(targetId)) {
      alert("Veuillez sélectionner un utilisateur.");
      return;
    }

    const participants = [user.id, targetId];
    try {
      await apiFetch('channels/simple', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          workspace_id: workspaceId,
          status: false,
          participants
        })
      });

      setSelectedParticipant('');
      setShowCreate(false);
      await loadAll();
    } catch (err) {
      console.error("Erreur création canal :", err.message);
      setError("Erreur lors de la création du canal.");
    }
  };

  const getChannelDisplayName = (name) => {
    if (!name.startsWith("priv_")) return name;
    const ids = name.replace("priv_", "").split("_").map(n => parseInt(n, 10));
    const names = ids.map(id => {
      if (id === user.id) return "Moi";
      const found = users.find(u => u.id === id);
      return found ? found.username : `User ${id}`;
    });
    return names.join(" ↔ ");
  };

  const handleChannelClick = (channelId) => {
    setSelectedChannelId(channelId);
    navigate(`/messaging?channel=${channelId}`);
  };

  return (
    <>
      <AdminHeader />
      <div className="messaging-container">
        <div className="messaging-sidebar">
          <h2 className="welcome-title">Conversations privées</h2>
          <button className="start-conv-btn" onClick={() => setShowCreate(!showCreate)}>
            ➕ Nouvelle conversation
          </button>

          {showCreate && (
            <div className="new-conversation-bar">
              <select
                value={selectedParticipant}
                onChange={(e) => setSelectedParticipant(e.target.value)}
                className="select-regular"
              >
                <option value="">-- Choisir un utilisateur --</option>
                {users.map(u => (
                  <option key={u.id} value={u.id}>
                    {u.username}
                  </option>
                ))}
              </select>
              <button className="send-btn" onClick={handleCreateChannel}>Créer</button>
            </div>
          )}

          {error && <div className="message-error">{error}</div>}

          <div className="conversation-list">
            {channels.map(channel => (
              <button
                key={channel.id}
                className="conversation-button"
                onClick={() => handleChannelClick(channel.id)}
              >
                {getChannelDisplayName(channel.name)}
              </button>
            ))}
          </div>
        </div>

        <div className="messaging-main">
          {selectedChannelId ? (
            <ChatWindow channelId={selectedChannelId} />
          ) : (
            <p className="no-conv-msg">Sélectionnez un canal pour voir les messages.</p>
          )}
        </div>
      </div>
    </>
  );
}

 