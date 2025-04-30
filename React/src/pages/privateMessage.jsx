import React, { useState, useCallback } from 'react';
import MessageList from '../components/MessageList';
import MessageForm from '../components/MessageForm';
import WebSocketHandler from '../components/WebSocketHandler';
import { useAuth } from '../context/AuthContext';
import '../styles/message.css';

const PrivateMessage = () => {
  const { user } = useAuth();
  const channelId = 1;
  const [messages, setMessages] = useState([]);

  const handleNewMessage = useCallback((msg) => {
    setMessages((prev) => {
      if (prev.some((m) => m.id === msg.id)) return prev;
      return [...prev, msg];
    });
  }, []);

  const handleMessagesFetched = (newMessages) => {
    const ids = new Set(messages.map((m) => m.id));
    const unique = newMessages.filter((m) => !ids.has(m.id));
    setMessages((prev) => [...unique, ...prev]);
  };

  return (
    <div className="privateMessage p-4 bg-gray-50 rounded shadow">
      <h3 className="text-lg font-semibold mb-4">
        💬 Test de messagerie sur le canal #{channelId}
      </h3>

      <WebSocketHandler channelId={channelId} onMessage={handleNewMessage} />

      <MessageList
        channelId={channelId}
        messages={messages}
        onMessagesFetched={handleMessagesFetched}
      />

      <MessageForm
        channelId={channelId}
        userId={user?.id}
        username={user?.username}
        onMessageSent={handleNewMessage}
      />
    </div>
  );
};

export default PrivateMessage;
 