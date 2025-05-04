import React, { useState } from 'react';
import { apiFetch } from '../services/api';

const MessageForm = ({ channelId, userId, username, onMessageSent }) => {
  const [content, setContent] = useState('');

  const handleSubmit = async (e) => {
    e.preventDefault();
    const trimmed = content.trim();
    if (!trimmed) return;

    try {
      // Envoi au backend
      const backendResponse = await apiFetch('messages', {
        method: 'POST',
        body: JSON.stringify({
          channel_id: channelId,
          user_id: userId,
          content: trimmed
        })
      });

      fetch("http://localhost:3001/broadcast", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({
          id: backendResponse.id,
          content: trimmed,
          timestamp: backendResponse.timestamp,
          author: backendResponse.user?.username || username || 'Inconnu',
          channel: channelId
        })
      }).then(() => {
        console.log("📡 Broadcast envoyé au WebSocket server !");
      });

      setContent('');

      const newMessage = {
        id: backendResponse.id,
        content: trimmed,
        timestamp: backendResponse.timestamp,
        author: backendResponse.user?.username || username || 'Inconnu'
      };

      console.log("📝 Message envoyé (API):", newMessage);

      // Propagation au parent
      onMessageSent?.(newMessage);

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

 