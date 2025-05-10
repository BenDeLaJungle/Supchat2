import React, { useState, useEffect } from 'react';
import { apiFetch } from '../services/api';
import AdminHeader from './Adminheader';
import MessageList from '../components/MessageList';
import MessageForm from '../components/MessageForm';
import { useAuth } from '../context/AuthContext';
import { Link } from 'react-router-dom';
import '../styles/index.css';

export default function Messaging() {
  const { user } = useAuth();
  const [channels, setChannels] = useState([]);
  const [selectedChannelId, setSelectedChannelId] = useState(null);
  const [error, setError] = useState(null);
  const [newChannelName, setNewChannelName] = useState('');
  const [newChannelVisible, setNewChannelVisible] = useState(false);

  const fetchChannels = async () => {
    try {
      const workspaceId = 1; // à adapter si tu gères plusieurs workspaces
      const data = await apiFetch(`workspaces/${workspaceId}/channels`);
      const privateChannels = data.filter(ch => ch.status === false);
      setChannels(privateChannels);
      setError(null);
    } catch (err) {
      console.error("Erreur de chargement des canaux :", err.message);
      setError("Impossible de charger les canaux.");
    }
  };

  const handleCreateChannel = async () => {
    if (!newChannelName.trim()) return;
    try {
      await apiFetch('channels', {
        method: 'POST',
        body: JSON.stringify({
          name: newChannelName,
          workspace_id: 1, // à adapter dynamiquement
          status: false
        })
      });
      setNewChannelName('');
      setNewChannelVisible(false);
      fetchChannels();
    } catch (err) {
      console.error("Erreur lors de la création du canal :", err.message);
      setError("Erreur lors de la création du canal.");
    }
  };

  useEffect(() => {
    fetchChannels();
  }, []);

  return (
    <>
      <AdminHeader />
      <div className="messaging-container">
        <div className="messaging-sidebar">
          <h2 className="welcome-title">Conversations privées</h2>
          <button className="start-conv-btn" onClick={() => setNewChannelVisible(!newChannelVisible)}>
            ➕ Nouvelle conversation privée
          </button>

          {newChannelVisible && (
            <div className="new-conversation-bar">
              <input
                type="text"
                className="recipient-input"
                placeholder="Nom du canal"
                value={newChannelName}
                onChange={(e) => setNewChannelName(e.target.value)}
              />
              <button className="send-btn" onClick={handleCreateChannel}>Créer</button>
            </div>
          )}

          {error && <div className="message-error">{error}</div>}

          <div className="conversation-list">
            {channels.map(channel => (
              <div key={channel.id}>
                <button
                  type="button"
                  className="conversation-button"
                  onClick={(e) => {
                    e.preventDefault();
                    setSelectedChannelId(channel.id);
                  }}
                >
                  {channel.name}
                </button>
              </div>
            ))}
          </div>

          <Link to="/test-messages" className="acces-test">
            🔗 Accéder à la messagerie test
          </Link>
        </div>

        <div className="messaging-main">
          {selectedChannelId ? (
            <>
              <MessageList channelId={selectedChannelId} />
              <MessageForm
                channelId={selectedChannelId}
                userId={user?.id}
                onMessageSent={() => fetchChannels()}
              />
            </>
          ) : (
            <p className="no-conv-msg">Sélectionnez un canal pour voir les messages.</p>
          )}
        </div>
      </div>
    </>
  );
}

