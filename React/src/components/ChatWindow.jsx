import React, { useState, useCallback } from 'react';
import MessageList from '../components/MessageList';
import MessageForm from '../components/MessageForm';
import WebSocketHandler from '../components/WebSocketHandler';
import { useAuth } from '../context/AuthContext';
import '../styles/color.css';
import '../styles/chat.css';

const ChatWindow = ({ channelId = 1 }) => {
  const { user } = useAuth();
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
    <div className="chat-window">
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

export default ChatWindow;
