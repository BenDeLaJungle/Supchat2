import React, { useState } from 'react';
import { apiFetch } from '../../services/api';
import '../../styles/color.css';
import '../../styles/chat.css';
import { useSocket } from '../../context/SocketContext';

const MessageForm = ({ channelId, userId, username, onMessageSent }) => {
  const [content, setContent] = useState('');
  const { socket, isReady } = useSocket();

  const extractMentionsAndChannels = (text) => {
    const mentionRegex = /@([a-zA-Z0-9_-]+)/g;
    const channelRegex = /#([a-zA-Z0-9_-]+)/g;

    const mentions = [];
    const channels = [];

    let match;
    while ((match = mentionRegex.exec(text)) !== null) {
      mentions.push(match[1]);
    }

    while ((match = channelRegex.exec(text)) !== null) {
      channels.push(match[1]);
    }

    return { mentions, channels };
  };

  const handleSubmit = async (e) => {
    e.preventDefault();
    const trimmed = content.trim();
    if (!trimmed) return;

    if (!isReady || !socket) {
      alert("La connexion au chat n'est pas prête !");
      return;
    }

    const { mentions, channels } = extractMentionsAndChannels(trimmed);
    const uniqueMentions = [...new Set(mentions)];

    try {
      //Créer le message
      const backendResponse = await apiFetch('messages', {
        method: 'POST',
        body: JSON.stringify({
          channel_id: channelId,
          user_id: userId,
          content: trimmed
        })
      });

      const messageId = backendResponse.id;

      //Créer mentions et hashtags
      const tasks = [];

      // @mentions
      for (const username of uniqueMentions) {
        tasks.push(
          apiFetch(`users/by-username/${username}`)
            .then(user => {
              if (user && user.id) {
                return apiFetch('mention/add', {
                  method: 'POST',
                  body: JSON.stringify({
                    userId: user.id,
                    messageId: messageId
                  })
                }).then(() => {
                  return apiFetch('notifications/create', {
                    method: 'POST',
                    body: JSON.stringify({
                      userId: user.id,
                      messageId: messageId
                    })
                  });
                });
              }
            })
            .catch(err => console.warn(`Mention "${username}" ignorée :`, err.message))
        );
      }

      // #hashtags
      if (channels.length > 0) {
        tasks.push(
          apiFetch('hashtags', {
            method: 'POST',
            body: JSON.stringify({
              message_id: messageId,
              channels: channels
            })
          }).catch(err => console.warn("Erreur hashtags :", err.message))
        );
      }

      await Promise.all(tasks);

      //Envoi via WebSocket
      const message = {
        id: messageId,
        content: trimmed,
        timestamp: backendResponse.timestamp,
        author: backendResponse.author || { id: userId, username: username || 'Inconnu' },
        channel: channelId
      };

      socket.emit('message', message);
      setContent('');
    } catch (err) {
      console.error("Erreur à l'envoi :", err.message);
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


 