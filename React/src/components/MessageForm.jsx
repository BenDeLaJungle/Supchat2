import React, { useState } from 'react';
import { apiFetch } from '../services/api';
import '../styles/color.css';
import '../styles/chat.css';
import { useSocket } from '../context/SocketContext';

const MessageForm = ({ channelId, userId, username, onMessageSent }) => {
  const [content, setContent] = useState('');
  const { socket, isReady } = useSocket();

  const handleSubmit = async (e) => {
    e.preventDefault();
    const trimmed = content.trim();
    if (!trimmed) return;

    if (!isReady || !socket) {
      alert("⛔ La connexion au chat n'est pas prête !");
      return;
    }

    try {
      const backendResponse = await apiFetch('messages', {
        method: 'POST',
        body: JSON.stringify({
          channel_id: channelId,
          user_id: userId,
          content: trimmed
        })
      });

      const message = {
        id: backendResponse.id,
        content: trimmed,
        timestamp: backendResponse.timestamp,
        author: backendResponse.user?.username || username || 'Inconnu',
        channel: channelId
      };

      console.log("🎯 Envoi via socket :", socket.id);
      socket.emit('message', message);
      console.log("📡 Message envoyé via socket :", message);

      setContent('');
    } catch (err) {
      console.error("💥 Erreur à l'envoi :", err.message);
    }
  };

  return (
    <form onSubmit={handleSubmit} className="message-form">
      <input
        type="text"
        value={content}
        onChange={(e) => setContent(e.target.value)}
        className="message-input"
        placeholder="Écris ton message..."
      />
      <button type="submit" className="message-button">
        Envoyer
      </button>
    </form>
  );
};

export default MessageForm;


 