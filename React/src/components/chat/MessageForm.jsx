import React, { useState, useRef } from 'react';
import EmojiPicker from 'emoji-picker-react';
import { apiFetch } from '../../services/api';
import '../../styles/color.css';
import '../../styles/chat.css';
import { useSocket } from '../../context/SocketContext';

const MessageForm = ({ channelId, userId, username, onMessageSent }) => {
  const [content, setContent] = useState('');
  const [file, setFile] = useState(null);
  const [showEmojiPicker, setShowEmojiPicker] = useState(false);
  const fileInputRef = useRef(null); 
  const { socket, isReady } = useSocket();

  const extractMentionsAndChannels = (text) => {
    const mentionRegex = /@([a-zA-Z0-9_-]+)/g;
    const channelRegex = /#([a-zA-Z0-9_-]+)/g;
    const mentions = [];
    const channels = [];
    let match;

    while ((match = mentionRegex.exec(text)) !== null) mentions.push(match[1]);
    while ((match = channelRegex.exec(text)) !== null) channels.push(match[1]);

    return { mentions, channels };
  };

  const handleSubmit = async (e) => {
    e.preventDefault();
    const trimmed = content.trim();

    if (!trimmed && !file) return;

    if (!isReady || !socket) {
      alert("La connexion au chat n'est pas prête !");
      return;
    }

    try {
      const backendResponse = await apiFetch('messages', {
        method: 'POST',
        body: JSON.stringify({
          channel_id: channelId,
          user_id: userId,
          content: trimmed || (file ? " " : "")
        })
      });

      const messageId = backendResponse.id;
      const { mentions, channels } = extractMentionsAndChannels(trimmed);
      const uniqueMentions = [...new Set(mentions)];

      const tasks = [];

      for (const username of uniqueMentions) {
        tasks.push(
          apiFetch(`users/by-username/${username}`)
            .then(user => {
              if (user?.id) {
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
            }).catch(err => console.warn(`Mention "${username}" ignorée :`, err.message))
        );
      }

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

      if (file && messageId) {
        const formData = new FormData();
        formData.append('file', file);
        formData.append('message_id', messageId);
        const token = localStorage.getItem('jwt');

        if (!token) {
          console.warn("Token JWT manquant !");
          return;
        }

        tasks.push(
          fetch('https://localhost:8000/api/files/upload', {
            method: 'POST',
            body: formData,
            headers: {
              Authorization: `Bearer ${token}`
            }
          })
          .then(res => {
            if (!res.ok) throw new Error('Erreur upload fichier');
            return res.json();
          })
          .catch(err => console.warn("Erreur fichier :", err.message))
        );
      }

      await Promise.all(tasks);

      const message = {
        id: messageId,
        content: trimmed,
        timestamp: backendResponse.timestamp,
        author: backendResponse.author || { id: userId, username: username || 'Inconnu' },
        channel: channelId
      };

      socket.emit('message', message);
      setContent('');
      setFile(null);
      setShowEmojiPicker(false);
      if (fileInputRef.current) fileInputRef.current.value = ''; 
    } catch (err) {
      console.error("Erreur à l'envoi :", err.message);
    }
  };

  const onEmojiClick = (emojiData) => {
    setContent(prev => prev + emojiData.emoji);
  };

    return (
    <form onSubmit={handleSubmit} className="message-form">
      {/* Champ texte */}
      <input
        type="text"
        className="message-input"
        placeholder="Écris ton message…"
        value={content}
        onChange={e => setContent(e.target.value)
        }
      />

      {/* Bouton fichier via label */}
      <label htmlFor="file-upload" className="file-button" title="Joindre un fichier">
        📎
      </label>
      <input
        id="file-upload"
        type="file"
        ref={fileInputRef}
        className="file-input"
        onChange={e => setFile(e.target.files[0])}
        accept=".pdf,.jpg,.jpeg,.png,.doc,.docx,.zip"
      />
      {file && (
        <div className="file-preview">
          {file.name}
        </div>
      )}

      {/* Bouton emoji */}
      <button
        type="button"
        className="emoji-toggle-button"
        onClick={() => setShowEmojiPicker(v => !v)}
        title="Ajouter un emoji"
      >
        😊
      </button>
      {showEmojiPicker && (
        <div className="emoji-picker-container">
          <EmojiPicker onEmojiClick={(_, data) => setContent(c => c + data.emoji)} />
        </div>
      )}

      {/* Bouton Envoyer */}
      <button type="submit" className="message-button-send">
        Envoyer
      </button>
    </form>
  );
};

export default MessageForm;