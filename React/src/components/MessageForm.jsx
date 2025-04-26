import React, { useState } from 'react';
import { apiFetch } from '../services/api';

const MessageForm = ({ channelId, userId, onMessageSent }) => {
  const [content, setContent] = useState('');

  const handleSubmit = async (e) => {
    e.preventDefault();
    if (!content.trim()) return;

    try {
      await apiFetch('messages', {
        method: 'POST',
        body: JSON.stringify({
          channel_id: channelId,
          user_id: userId,
          content: content.trim()
        })
      });

      setContent('');
      onMessageSent();
    } catch (err) {
      console.error("Erreur lors de l'envoi du message :", err.message);
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

