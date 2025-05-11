import React, { useState, useEffect, useCallback } from 'react';
import MessageList from '../components/MessageList';
import MessageForm from '../components/MessageForm';
import WebSocketHandler from '../components/WebSocketHandler';
import { useAuth } from '../context/AuthContext';
import '../styles/color.css';
import '../styles/chat.css';
import { apiFetch } from '../services/api';

const ChatWindow = ({ channelId = 1 }) => {
  const { user } = useAuth();
  const [messages, setMessages] = useState([]);
  const [privileges, setPrivileges] = useState({
    isAdmin: false,
    canModerate: false,
    canManage: false
  });

  // Fetch des privilèges au chargement
  useEffect(() => {
    if (!user?.id) return;

    const loadPrivileges = async () => {
      try {
        const data = await apiFetch(`channels/${channelId}/privilege`, {
          method: 'POST',
          body: JSON.stringify({ user_id: user.id })
        });

        setPrivileges({
          isAdmin: data.is_admin,
          canModerate: data.can_moderate,
          canManage: data.can_manage
        });
      } catch (error) {
        console.error("Erreur en récupérant les privilèges :", error.message);
      }
    };

    loadPrivileges();
  }, [channelId, user]);

  const handleMessagesFetched = (newMessages) => {
    const ids = new Set(messages.map((m) => m.id));
    const unique = newMessages.filter((m) => !ids.has(m.id));
    setMessages((prev) => [...unique, ...prev]);
  };

  // Vérifie si peut editer
  const canEdit = privileges.isAdmin || privileges.canModerate;

  const handleIncomingMessage = useCallback((incoming) => {
    setMessages((prev) => {
      const exists = prev.find((m) => m.id === incoming.id);

      if (incoming.deleted) {
        return prev.map((m) =>
          m.id === incoming.id
            ? { ...m, content: '[Ce message a été supprimé]', deleted: true }
            : m
        );
      }

      if (!exists) {
        return [...prev, incoming];
      }

      return prev.map((m) =>
        m.id === incoming.id ? { ...m, ...incoming } : m
      );
    });
  }, []);



  return (
    <div className="chat-window">
      <WebSocketHandler channelId={channelId} onMessage={handleIncomingMessage} />

      <MessageList
        channelId={channelId}
        messages={messages}
        onMessagesFetched={handleMessagesFetched}
        canEdit={canEdit}
        userId={user?.id}
      />

      <MessageForm
        channelId={channelId}
        userId={user?.id}
        username={user?.username}
        onMessageSent={handleIncomingMessage}
      />
    </div>
  );
};

export default ChatWindow;

