import React, { useState, useRef, useEffect } from 'react';
import { apiFetch } from '../services/api';
import { useSocket } from '../context/SocketContext';
import '../styles/color.css';
import '../styles/chat.css';

const Message = ({ id, author, content, timestamp, currentUserId, canEditGlobal, onDelete, channelId }) => {
  const { socket, isReady } = useSocket();

  const [menuOpen, setMenuOpen] = useState(false);
  const [isEditing, setIsEditing] = useState(false);
  const [editedContent, setEditedContent] = useState(content);
  const [localContent, setLocalContent] = useState(content);
  const [error, setError] = useState(null);
  const menuRef = useRef(null);

  const canEditThisMessage = (author.id === currentUserId) || canEditGlobal;

  useEffect(() => {
    const handleClickOutside = (event) => {
      if (menuRef.current && !menuRef.current.contains(event.target)) {
        setMenuOpen(false);
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

      if (onDelete) onDelete(id);
    } catch (err) {
      console.error("Erreur suppression :", err.message);
      alert("Erreur lors de la suppression : " + err.message);
    }
  };

  const handleEditClick = () => {
    setMenuOpen(false);
    setIsEditing(true);
  };

  return (
    <div className="message">
      <div className="message-inner">
        <div className="message-author">{author.username}</div>

        {isEditing ? (
          <div className="message-edit-form">
            <textarea
              className="message-edit-input"
              value={editedContent}
              onChange={(e) => setEditedContent(e.target.value)}
            />
            <div className="edit-buttons">
              <button onClick={handleEditSubmit}>Enregistrer</button>
              <button onClick={handleEditCancel}>Annuler</button>
              {error && <div className="edit-error">{error}</div>}
            </div>
          </div>
        ) : (
          <div className="message-content">{localContent}</div>
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



 