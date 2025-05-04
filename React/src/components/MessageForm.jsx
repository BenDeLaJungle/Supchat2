import React, { useState } from 'react';
import { apiFetch } from '../services/api';
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
      // On enregistre d’abord dans la BDD (REST)
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

      // Ensuite on notifie en WebSocket
      console.log("🎯 Envoi via socket :", socket.id);
      socket.emit('message', message);
      console.log("📡 Message envoyé via socket :", message);

      setContent('');
    } catch (err) {
      console.error("💥 Erreur à l'envoi :", err.message);
    }
  };

  return (
    <form onSubmit={handleSubmit} className="mt-4 flex gap-2">
      <input
        type="text"
        value={content}
        onChange={(e) => setContent(e.target.value)}
        className="flex-1 border rounded px-3 py-2 text-black bg-white"
        placeholder="Écris ton message..."
      />
      <button
        type="submit"
        className="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded transition-colors"
      >
        Envoyer
      </button>
    </form>
  );
};

export default MessageForm;

 