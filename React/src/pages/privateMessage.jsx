import React, { useState } from 'react';
import MessageList from '../components/MessageList';
import MessageForm from '../components/MessageForm';
import WebSocketHandler from '../components/WebSocketHandler';
import { useAuth } from '../context/AuthContext';
import '../styles/message.css';

const PrivateMessage = () => {
  const { user } = useAuth();
  const channelId = 1;
  const [messages, setMessages] = useState([]);

  const handleIncomingMessage = (msg) => {
    setMessages((prev) => {
      if (prev.some((m) => m.id === msg.id)) return prev;
      return [...prev, msg];
    });
  };

  const handleMessagesFetched = (newMessages) => {
    setMessages((prev) => {
      const ids = new Set(prev.map((m) => m.id));
      return [...newMessages.filter((m) => !ids.has(m.id)), ...prev];
    });
  };

  return (
    <div className="privateMessage p-4 bg-gray-50 rounded shadow">
      <h3 className="text-lg font-semibold mb-4">
        💬 Test de messagerie sur le canal #{channelId}
      </h3>

      {/* WebSocket temps réel */}
      <WebSocketHandler channelId={channelId} onMessage={handleIncomingMessage} />

      {/* Affichage des messages */}
      <MessageList
        channelId={channelId}
        messages={messages}
        onMessagesFetched={handleMessagesFetched}
      />

      {/* Formulaire d'envoi */}
      <MessageForm
        channelId={channelId}
        userId={user?.id}
        username={user?.username}
        onMessageSent={handleIncomingMessage}
      />
    </div>
  );
};

export default PrivateMessage;
 