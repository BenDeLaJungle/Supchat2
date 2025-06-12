import React, { useState, useEffect, useCallback } from 'react';
import MessageList from './MessageList';
import MessageForm from './MessageForm';
import WebSocketHandler from '../socket/WebSocketHandler';
import { useAuth } from '../../context/AuthContext';
import '../../styles/color.css';
import '../../styles/chat.css';
import { apiFetch } from '../../services/api';
import AdminHeader from '../ui/Adminheader';

const ChatWindow = ({ channelId = 1 }) => {
  const { user } = useAuth();
  const [messages, setMessages] = useState([]);
  const [channelName, setChannelName] = useState('');
  const [privileges, setPrivileges] = useState(null);

  // Récupération des privilèges
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
        setPrivileges({
          isAdmin: false,
          canModerate: false,
          canManage: false
        });
      }
    };

    loadPrivileges();
  }, [channelId, user]);

  // Récupération du nom du canal
  useEffect(() => {
    const fetchChannelDetails = async () => {
      try {
        const data = await apiFetch(`channels/${channelId}`);
        setChannelName(data.name);
      } catch (error) {
        console.error("Erreur lors du chargement du nom du canal :", error.message);
      }
    };

    fetchChannelDetails();
  }, [channelId]);

  const handleMessagesFetched = (newMessages) => {
    const ids = new Set(messages.map((m) => m.id));
    const unique = newMessages.filter((m) => !ids.has(m.id));
    setMessages((prev) => [...unique, ...prev]);
  };

  const canEdit = !!(privileges?.isAdmin || privileges?.canModerate);

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

  //Pendant que ça charge
  if (!privileges) {
    return (
      <>
        
        <h2 style={{ textAlign: 'center', margin: '1rem 0' }}>Chargement des privilèges...</h2>
      </>
    );
  }

  return (
    <>
      
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
    </>
  );
};

export default ChatWindow;