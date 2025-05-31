import React, { useState, useRef, useEffect } from 'react';
import { apiFetch } from '../../services/api';
import { useSocket } from '../../context/SocketContext';
import EmojiPicker from 'emoji-picker-react';
import '../../styles/color.css';
import '../../styles/chat.css';
import { useNavigate } from 'react-router-dom';

const Message = ({ id, author, content, timestamp, currentUserId, canEditGlobal, onDelete, channelId,hashtags = [],onChannelSelected}) => {
  const { socket, isReady } = useSocket();
  const navigate = useNavigate();
  const [menuOpen, setMenuOpen] = useState(false);
  const [isEditing, setIsEditing] = useState(false);
  const [editedContent, setEditedContent] = useState(content);
  const [localContent, setLocalContent] = useState(content);
  const [error, setError] = useState(null);
  const [showEmojiPicker, setShowEmojiPicker] = useState(false);
  const menuRef = useRef(null);

  const canEditThisMessage = (author.id === currentUserId) || canEditGlobal;

  useEffect(() => {
    const handleClickOutside = (event) => {
      if (menuRef.current && !menuRef.current.contains(event.target)) {
        setMenuOpen(false);
        setShowEmojiPicker(false);
      }
    };
    document.addEventListener('mousedown', handleClickOutside);
    return () => document.removeEventListener('mousedown', handleClickOutside);
  }, []);

  const broadcastMessage = (data) => {
    if (isReady && socket) {
      socket.emit('message', data);
    } else {
      console.warn("Socket pas prêt, impossible de broadcaster.");
    }
  };

  const parseContent = (text) => {
    const hashtagRegex = /#([a-zA-Z0-9_-]+)/g;
    const parts = [];
    let lastIndex = 0;
    let match;

    while ((match = hashtagRegex.exec(text)) !== null) {
      const before = text.slice(lastIndex, match.index);
      const tag = match[1];

      if (before) parts.push(before);

      const found = hashtags.find(h => h.tag === tag);

      if (found) {
        parts.push(
          <span
            key={match.index}
            className="hashtag-link"
            onClick={() => {
              console.log("Navigating to channel:", found.channel.id);
              navigate(`/channels/${found.channel.id}`);
            }}
            style={{ color: 'var(--primary)', cursor: 'pointer' }}
          >
            #{tag}
          </span>
        );
      } else {
        parts.push(`#${tag}`);
      }

      lastIndex = hashtagRegex.lastIndex;
    }

    const after = text.slice(lastIndex);
    if (after) parts.push(after);

    return parts;
  };


  const handleEditSubmit = async () => {
    try {
      await apiFetch(`messages/${id}`, {
        method: 'PUT',
        body: JSON.stringify({ content: editedContent }),
      });

      const updated = {
        id,
        content: editedContent,
        timestamp,
        author,
        channel: channelId
      };

      setLocalContent(editedContent);
      setIsEditing(false);
      broadcastMessage(updated);
      setError(null);
    } catch (err) {
      console.error("Erreur édition :", err.message);
      setError(err.message);
    }
  };

  const handleEditCancel = () => {
    setIsEditing(false);
    setEditedContent(localContent);
    setError(null);
  };

  const handleDeleteClick = async () => {
    setMenuOpen(false);
    if (!window.confirm("Tu es sûr de vouloir supprimer ce message ?")) return;

    try {
      await apiFetch(`messages/${id}`, {
        method: 'DELETE',
      });

      broadcastMessage({
        id,
        deleted: true,
        channel: channelId
      });
    } catch (err) {
      console.error("Erreur suppression :", err.message);
      alert("Erreur lors de la suppression : " + err.message);
    }
  };

  const handleEditClick = () => {
    setMenuOpen(false);
    setIsEditing(true);
  };

  const onEmojiClick = (emojiData) => {
    setEditedContent(prev => prev + emojiData.emoji);
  };

  return (
    <div className="message">
      <div className="message-inner">
        <div className="message-author">{author.username}</div>

        {isEditing ? (
          <div className="message-edit-form">
            <div>
              <textarea
                className="message-edit-input"
                value={editedContent}
                onChange={(e) => setEditedContent(e.target.value)}
              />
              <div className="emoji-picker-container">
                <button onClick={() => setShowEmojiPicker(!showEmojiPicker)}>
                  😊
                </button>
                {showEmojiPicker && (
                  <div style={{ position: 'absolute', zIndex: 10 }}>
                    <EmojiPicker onEmojiClick={onEmojiClick} />
                  </div>
                )}
              </div>
            </div>
            <div className="edit-buttons">
              <button onClick={handleEditSubmit}>Enregistrer</button>
              <button onClick={handleEditCancel}>Annuler</button>
              {error && <div className="edit-error">{error}</div>}
            </div>
          </div>
        ) : (
          <div className="message-content">{parseContent(localContent)}</div>
        )}

        <div className="message-timestamp">{new Date(timestamp).toLocaleString()}</div>
      </div>

      {canEditThisMessage && !isEditing && (
        <div className="message-actions" ref={menuRef}>
          <button className="message-button" onClick={() => setMenuOpen(!menuOpen)}>
            ...
          </button>

          {menuOpen && (
            <div className="message-menu">
              <button className="message-menu-item" onClick={handleEditClick}>
                Modifier
              </button>
              <button className="message-menu-item" onClick={handleDeleteClick}>
                Supprimer
              </button>
            </div>
          )}
        </div>
      )}
    </div>
  );
};

export default Message;
